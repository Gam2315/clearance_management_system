<?php

namespace App\Http\Controllers\Employee;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\ClearanceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class EmpClearanceController extends Controller
{
   

    public function update(Request $request, $id)
    {
        $status = ClearanceStatus::findOrFail($id);
        $clearance = $status->clearance;

        // Check if clearance is accessible
        if (!$clearance->isAccessible()) {
            $reason = $clearance->is_locked ? 'Clearance is locked' : 'Student has blocking violations or incomplete previous semester';
            return back()->with('error', "Cannot update clearance status. Reason: {$reason}");
        }

        $status->status = $request->input('status');
        $status->remarks = $request->input('remarks');
        $status->approved_by = $request->input('approved_by');
        $status->department_id = $request->input('department_id');

        // Set submission timestamp if status is being cleared
        if ($request->input('status') === 'cleared') {
            $status->submitted_at = now();
            $status->can_submit = false;
        }

        $status->save();

        return back()->with('success', 'Clearance status updated.');
    }

    public function submitClearance($id)
    {
        $clearance = Clearance::findOrFail($id);
        $student = $clearance->student;

        // Check if student can proceed with clearance
        if (!$student->canProceedWithClearance()) {
            return back()->with('error', 'Student cannot proceed with clearance due to violations or graduation status.');
        }

        // Check if clearance is accessible
        if (!$clearance->isAccessible()) {
            return back()->with('error', 'Clearance is not accessible at this time.');
        }

        // Mark all pending statuses as submitted
        $clearance->statuses()->where('status', 'pending')->update([
            'submitted_at' => now(),
            'can_submit' => false,
        ]);

        return back()->with('success', 'Clearance submitted successfully.');
    }

    public function cancelClearance($id)
    {
        $clearance = Clearance::findOrFail($id);

        // Mark all statuses as cancelled
        $clearance->statuses()->update([
            'is_cancelled' => true,
            'cancelled_at' => now(),
            'can_submit' => true,
        ]);

        return back()->with('success', 'Clearance cancelled. Student can resubmit.');
    }

    public function lockClearance(Request $request, $id)
    {
        $request->validate([
            'lock_reason' => 'required|string',
        ]);

        $clearance = Clearance::findOrFail($id);
        $clearance->lock($request->lock_reason, auth()->id());

        return back()->with('success', 'Clearance locked successfully.');
    }

    public function unlockClearance($id)
    {
        $clearance = Clearance::findOrFail($id);
        $clearance->unlock();

        return back()->with('success', 'Clearance unlocked successfully.');
    }


    public function showTap()
    {
        return view('clearance-tap.index');
    }

    public function index()
    {

        return view('employee.clearance-tap.index');
    }

    public function automaticClearanceInterface()
    {
        return view('employee.clearance.automatic-clearance');
    }

    public function detectStudentForClearance()
    {
        try {
            // First, clean up old taps (older than 2 minutes)
            DB::table('nfc_taps')
                ->where('created_at', '<', Carbon::now()->subMinutes(2))
                ->delete();

            // Get the latest NFC tap from the database
            $latestTap = DB::table('nfc_taps')->latest('created_at')->first();

            if (!$latestTap) {
                return response()->json([
                    'found' => false,
                    'message' => 'No RFID card detected. Please tap your ID card.',
                    'status' => 'waiting',
                    'debug' => [
                        'total_taps' => 0,
                        'current_time' => Carbon::now()->toDateTimeString()
                    ]
                ]);
            }

            // Check if this tap is recent (within last 15 seconds for stricter detection)
            $tapTime = Carbon::parse($latestTap->created_at);
            $now = Carbon::now();
            $secondsDiff = $now->diffInSeconds($tapTime);

            if ($secondsDiff > 15) {
                // Delete expired tap to prevent showing stale data
                DB::table('nfc_taps')->where('id', $latestTap->id)->delete();

                return response()->json([
                    'found' => false,
                    'message' => 'RFID tap expired. Please tap your ID card again.',
                    'status' => 'expired',
                    'debug' => [
                        'seconds_since_tap' => $secondsDiff,
                        'tap_time' => $tapTime->toDateTimeString(),
                        'current_time' => $now->toDateTimeString()
                    ]
                ]);
            }

            // Normalize UID to uppercase for consistent comparison
            $normalizedUid = strtoupper($latestTap->uid);

            // Find student by NFC UID with case-insensitive search
            $student = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->whereRaw('UPPER(nfc_uid) = ?', [$normalizedUid])
                ->first();

            // Debug information
            $debugInfo = [
                'tap_uid' => $latestTap->uid,
                'normalized_uid' => $normalizedUid,
                'tap_time' => $tapTime,
                'current_time' => $now,
                'diff_seconds' => $now->diffInSeconds($tapTime),
                'student_found' => $student ? true : false,
                'total_students_with_nfc' => Student::whereNotNull('nfc_uid')->count(),
                'all_nfc_uids' => Student::whereNotNull('nfc_uid')->pluck('nfc_uid')->toArray()
            ];

            if (!$student) {
                return response()->json([
                    'found' => false,
                    'message' => 'RFID card detected but not linked to any student. Please link your card first.',
                    'status' => 'unlinked',
                    'uid' => $latestTap->uid,
                    'debug' => $debugInfo
                ]);
            }

            // Get clearance for active academic year
            $activeAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

            if ($activeAcademicYear) {
                $clearance = $student->clearances()
                    ->where('academic_id', $activeAcademicYear->id)
                    ->first();
            } else {
                $clearance = $student->clearances()->latest()->first();
            }

            if (!$clearance) {
                return response()->json([
                    'found' => false,
                    'message' => 'Student found but has no clearance record.',
                    'status' => 'no_clearance',
                    'student' => [
                        'name' => $student->user->firstname . ' ' . $student->user->lastname,
                        'student_number' => $student->student_number
                    ]
                ]);
            }

            // Check if student can proceed with clearance (temporarily disabled for first-time users)
            // This will be re-enabled once all students are properly initialized
            // if (!$student->canProceedWithClearance()) {
            //     $blockingReason = $this->getBlockingReason($student);
            //     return response()->json([
            //         'found' => true,
            //         'status' => 'blocked',
            //         'message' => $blockingReason,
            //         'student' => [
            //             'id' => $student->id,
            //             'student_number' => $student->student_number,
            //             'name' => $student->user->firstname . ' ' . $student->user->middlename . ' ' . $student->user->lastname,
            //             'department' => $student->department->department_name ?? 'No Department',
            //             'program' => $student->courses->course_name ?? 'No Program',
            //             'year' => $student->year,
            //             'has_violations' => $student->has_violations,
            //             'is_graduated' => $student->is_graduated,
            //         ]
            //     ]);
            // }

            // Check if clearance is accessible (temporarily disabled for first-time users)
            // This will be re-enabled once all students are properly initialized
            // if (!$clearance->isAccessible()) {
            //     return response()->json([
            //         'found' => true,
            //         'status' => 'blocked',
            //         'message' => 'Clearance is locked: ' . ($clearance->lock_reason ?? 'No reason provided'),
            //         'student' => [
            //             'id' => $student->id,
            //             'student_number' => $student->student_number,
            //             'name' => $student->user->firstname . ' ' . $student->user->middlename . ' ' . $student->user->lastname,
            //             'department' => $student->department->department_name ?? 'No Department',
            //             'program' => $student->courses->course_name ?? 'No Program',
            //             'year' => $student->year,
            //         ]
            //     ]);
            // }

            // Check if employee can clear this student
            $currentUser = auth()->user();
            $canClear = $this->canEmployeeClearStudent($currentUser, $student);

            // Get existing status for this department
            $existingStatus = $clearance->statuses()
                ->where('department_id', $currentUser->department_id)
                ->first();

            // Mark this tap as used by deleting it to prevent reuse
            DB::table('nfc_taps')->where('id', $latestTap->id)->delete();

            return response()->json([
                'found' => true,
                'status' => 'detected',
                'can_clear' => $canClear,
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'name' => $student->user->firstname . ' ' . $student->user->middlename . ' ' . $student->user->lastname,
                    'department' => $student->department->department_name ?? 'No Department',
                    'program' => $student->courses->course_name ?? 'No Program',
                    'year' => $student->year,
                    'has_violations' => $student->has_violations,
                    'is_graduated' => $student->is_graduated,
                ],
                'clearance' => [
                    'id' => $clearance->id,
                    'is_locked' => $clearance->is_locked,
                    'lock_reason' => $clearance->lock_reason,
                ],
                'existing_status' => $existingStatus ? [
                    'status' => $existingStatus->status,
                    'or_number' => $existingStatus->or_number,
                    'approved_by' => $existingStatus->approver->name ?? 'Unknown',
                    'created_at' => $existingStatus->created_at->format('M d, Y h:i A')
                ] : null,
                'debug' => [
                    'tap_consumed' => true,
                    'tap_time' => $tapTime->toDateTimeString(),
                    'seconds_since_tap' => $secondsDiff
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'message' => 'Error detecting RFID: ' . $e->getMessage(),
                'status' => 'error'
            ]);
        }
    }

    private function canEmployeeClearStudent($employee, $student)
    {
        // Define restricted departments
        $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS, SBAHM

        // If employee is from a restricted department, they can only clear students from their own department
        if (in_array($employee->department_id, $restrictedDepartments)) {
            return $employee->department_id === $student->department_id;
        }

        // Other departments (like Library, BAO) can clear all students
        return true;
    }

    /**
     * Get the specific reason why a student is blocked from clearance
     */
    private function getBlockingReason($student)
    {
        if ($student->is_graduated) {
            return 'Student has already graduated and cannot proceed with clearance.';
        }

        if ($student->hasBlockingViolations()) {
            return 'Student has active violations that block clearance processing.';
        }

        if (!$student->hasPreviousYearClearanceCompleted()) {
            return 'Student did not complete clearance for the previous academic year and is blocked from current year clearance.';
        }

        return 'Student is blocked from clearance for unknown reasons.';
    }

    public function EmplistClearance(Request $request)
    {
        $user = Auth::user();
        $employeeDepartmentId = $user->department_id;

        // Get active academic year
        $activeAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

        // Define which departments are restricted to only see their own students
        $restrictedDepartments = [1,2, 3, 4]; // SITE, SASTE, SNAHS departments

        // Build base query
        $query = Clearance::with('academicYear', 'student', 'department')
                          ->where('is_archived', false);

        // Filter by active academic year if available
        if ($activeAcademicYear) {
            $query->where('academic_id', $activeAcademicYear->id);
        }

        // If employee is from a restricted department, only show their students
        if (in_array($employeeDepartmentId, $restrictedDepartments)) {
            $query->where('department_id', $employeeDepartmentId);
        }

        $clearances = $query->get();

        // Fetch those departments for displaying or other uses
        $departments = Department::all();

         return view('employee.clearance.list-of-clearance', compact('clearances', 'departments'));
    }
              
       
    


 

























    /**
     * Process automatic clearance from RFID detection
     */
    public function processAutomaticClearance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'clearance_id' => 'required|exists:clearances,id',
            'status' => 'required|in:cleared,pending',
            'or_number' => 'nullable|string',
        ]);

        try {
            $student = Student::findOrFail($request->student_id);
            $clearance = Clearance::findOrFail($request->clearance_id);
            $currentUser = auth()->user();

            // Check if employee can clear this student
            if (!$this->canEmployeeClearStudent($currentUser, $student)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to clear students from this department.'
                ], 403);
            }

            // Check if clearance is accessible (temporarily disabled for first-time users)
            // This will be re-enabled once all students are properly initialized
            // if (!$clearance->isAccessible()) {
            //     $reason = 'Clearance is not accessible.';
            //     if ($clearance->is_locked) {
            //         $reason = 'Clearance is locked: ' . ($clearance->lock_reason ?? 'No reason provided');
            //     } elseif (!$student->hasPreviousYearClearanceCompleted()) {
            //         $reason = 'Student did not complete clearance for the previous academic year.';
            //     }

            //     return response()->json([
            //         'success' => false,
            //         'message' => $reason
            //     ], 400);
            // }

            // Check if student can proceed with clearance (temporarily disabled for first-time users)
            // This will be re-enabled once all students are properly initialized
            // if (!$student->canProceedWithClearance()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => $this->getBlockingReason($student)
            //     ], 400);
            // }



            // Validate OR number for BAO department (ID 5)
            if ($currentUser->department_id == 5 && $request->status === 'cleared' && empty($request->or_number)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OR number is required for BAO clearance.'
                ], 400);
            }

            // Check if status already exists for this department
            $existingStatus = $clearance->statuses()
                ->where('department_id', $currentUser->department_id)
                ->first();

            if ($existingStatus && $existingStatus->status === 'cleared' && $request->status === 'cleared') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student has already been cleared by this department.'
                ], 400);
            }

            // Create or update clearance status
            if ($existingStatus) {
                $existingStatus->update([
                    'status' => $request->status,
                    'approved_by' => $currentUser->id,
                    'or_number' => $request->or_number,
                    'cleared_at' => $request->status === 'cleared' ? now() : null,
                    'submitted_at' => $request->status === 'cleared' ? now() : null,
                    'cancelled_at' => $request->status === 'pending' ? now() : null,
                ]);
            } else {
                ClearanceStatus::create([
                    'clearance_id' => $request->clearance_id,
                    'student_id' => $request->student_id,
                    'department_id' => $currentUser->department_id,
                    'approved_by' => $currentUser->id,
                    'status' => $request->status,
                    'or_number' => $request->or_number,
                    'cleared_at' => $request->status === 'cleared' ? now() : null,
                    'submitted_at' => $request->status === 'cleared' ? now() : null,
                    'cancelled_at' => $request->status === 'pending' ? now() : null,
                ]);
            }

            // Update student's clearance history if cleared
            if ($request->status === 'cleared') {
                $student->updateClearanceHistory(
                    $clearance->academic_id,
                    $clearance->semester ?? '1st',
                    true
                );
            }

            $statusText = $request->status === 'cleared' ? 'cleared' : 'marked as pending';

            return response()->json([
                'success' => true,
                'message' => "Student has been {$statusText} successfully!",
                'status' => $request->status,
                'completion_percentage' => $clearance->getCompletionPercentage()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing clearance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for processing student by student number
     */
    public function showProcessStudentForm()
    {
        // Redirect to the clearance tap form if accessed via GET
        return redirect()->route('employee.clearance.clearance-tap-id')
            ->with('info', 'Please enter a student ID to process clearance. This page only accepts form submissions.');
    }

    /**
     * Process student by student number for manual clearance
     */
    public function processStudentByNumber(Request $request)
    {
        // Debug logging
        Log::info('processStudentByNumber called', [
            'method' => $request->method(),
            'url' => $request->url(),
            'input' => $request->all(),
            'user_id' => auth()->id(),
            'user_department' => auth()->user()->department_id ?? 'none'
        ]);

        $request->validate([
            'student_number' => 'required|string'
        ]);

        // Check if there's a recent NFC tap that belongs to a different student
        $latestTap = DB::table('nfc_taps')->latest('created_at')->first();
        if ($latestTap) {
            $tapTime = Carbon::parse($latestTap->created_at);
            $now = Carbon::now();

            // If tap is recent (within last 30 seconds)
            if ($now->diffInSeconds($tapTime) <= 30) {
                $normalizedUid = strtoupper($latestTap->uid);
                $nfcStudent = Student::whereRaw('UPPER(nfc_uid) = ?', [$normalizedUid])->first();

                // If NFC card belongs to a different student than the one entered
                if ($nfcStudent && $nfcStudent->student_number !== $request->student_number) {
                    return back()->with('error', 'This NFC card is already linked to another student (' . $nfcStudent->student_number . '). Please use the correct student ID or remove the NFC card.');
                }
            }
        }

        // Find student by student number
        $student = Student::with(['user', 'department', 'clearances.statuses'])
            ->where('student_number', $request->student_number)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student not found. Please check the student number.');
        }

        // Get clearance for active academic year
        $activeAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

        if ($activeAcademicYear) {
            $clearance = $student->clearances()
                ->where('academic_id', $activeAcademicYear->id)
                ->first();
        } else {
            $clearance = $student->clearances()->latest()->first();
        }

        if (!$clearance) {
            return back()->with('error', 'No clearance record found for this student.');
        }

        // Check if clearance is locked
        if ($clearance->is_locked) {
            return back()->with('error', 'This student\'s clearance is locked: ' . $clearance->lock_reason);
        }

        // Check if student can proceed with clearance (skip for now to allow first-time users)
        // This validation will be re-enabled once all students are properly initialized
        // if (!$student->canProceedWithClearance()) {
        //     $reason = $this->getBlockingReason($student);
        //     return back()->with('error', $reason);
        // }

        // Check if clearance is accessible (temporarily disabled for first-time users)
        // This will be re-enabled once all students are properly initialized
        // if (!$clearance->isAccessible()) {
        //     return back()->with('error', 'This student\'s clearance is not accessible at this time.');
        // }

        // Check if current user can clear this student (same department)
        $currentUser = auth()->user();
        $canClear = $this->canEmployeeClearStudent($currentUser, $student);

        // Get existing status for this department
        $existingStatus = ClearanceStatus::with('approver')
            ->where('clearance_id', $clearance->id)
            ->where('department_id', $currentUser->department_id)
            ->first();

        // Redirect to student clearance processing page
        return view('employee.clearance.student-clearance', compact(
            'student',
            'clearance',
            'existingStatus',
            'canClear'
        ));
    }
}