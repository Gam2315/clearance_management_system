<?php

namespace App\Http\Controllers\Adviser;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\ClearanceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdviserClearanceController extends Controller
{
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

            // Get latest clearance
            $clearance = $student->clearances()->latest()->first();

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

            // Check if student can proceed with clearance (includes previous year check)
            if (!$student->canProceedWithClearance()) {
                $blockingReason = $this->getBlockingReason($student);
                return response()->json([
                    'found' => true,
                    'status' => 'blocked',
                    'message' => $blockingReason,
                    'student' => [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'name' => $student->user->firstname . ' ' . $student->user->middlename . ' ' . $student->user->lastname,
                        'department' => $student->department->department_name ?? 'No Department',
                        'program' => $student->courses->course_name ?? 'No Program',
                        'year' => $student->year,
                        'has_violations' => $student->has_violations,
                        'is_graduated' => $student->is_graduated,
                    ]
                ]);
            }

            // Check if clearance is accessible
            if (!$clearance->isAccessible()) {
                return response()->json([
                    'found' => true,
                    'status' => 'blocked',
                    'message' => 'Clearance is locked: ' . ($clearance->lock_reason ?? 'No reason provided'),
                    'student' => [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'name' => $student->user->firstname . ' ' . $student->user->middlename . ' ' . $student->user->lastname,
                        'department' => $student->department->department_name ?? 'No Department',
                        'program' => $student->courses->course_name ?? 'No Program',
                        'year' => $student->year,
                    ]
                ]);
            }

            // Get current user's department to check if they can clear this student
            $currentUser = Auth::user();
            $userDepartment = $currentUser->department_id;

            // Check if user can clear this student (same department or admin)
            $canClear = $userDepartment == $student->department_id || $currentUser->role === 'admin';

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
                'pending_departments' => $this->getPendingDepartments($student, $clearance),
                'debug' => [
                    'tap_consumed' => true,
                    'tap_time' => $tapTime->toDateTimeString(),
                    'seconds_since_tap' => $secondsDiff
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'message' => 'Error occurred during detection: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    private function getBlockingReason($student)
    {
        if ($student->has_violations) {
            return 'Student has unresolved violations and cannot proceed with clearance.';
        }

        if ($student->is_graduated) {
            return 'Student has already graduated and cannot proceed with clearance.';
        }

        return 'Student cannot proceed with clearance for unknown reasons.';
    }

    private function getPendingDepartments($student, $clearance)
    {
        if (!$clearance) {
            return ['All departments'];
        }

        $allDepartments = Department::whereIn('id', [1, 2, 3, 4])->pluck('department_name', 'id');
        $clearedDepartments = $clearance->statuses()
            ->where('status', 'cleared')
            ->pluck('department_id')
            ->toArray();

        $pendingDepartments = [];
        foreach ($allDepartments as $id => $name) {
            if (!in_array($id, $clearedDepartments)) {
                $pendingDepartments[] = $name;
            }
        }

        return empty($pendingDepartments) ? ['All departments cleared'] : $pendingDepartments;
    }

    /**
     * Show the form for processing student by student number
     */
    public function showProcessStudentForm()
    {
        // Redirect to the clearance tap form if accessed via GET
        return redirect()->route('adviser.clearance.clearance-tap-id')
            ->with('info', 'Please enter a student ID to process clearance. This page only accepts form submissions.');
    }

    /**
     * Process student by student number for manual clearance
     */
    public function processStudentByNumber(Request $request)
    {
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

        // Get the latest clearance for this student
        $clearance = Clearance::with(['statuses.department', 'statuses.approver'])
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$clearance) {
            return back()->with('error', 'No clearance record found for this student.');
        }

        // Check if clearance is locked
        if ($clearance->is_locked) {
            return back()->with('error', 'This student\'s clearance is locked: ' . $clearance->lock_reason);
        }

        // Check if current user can clear this student (same department)
        $currentUser = auth()->user();
        $canClear = $this->canAdviserClearStudent($currentUser, $student);

        // Get existing status for this department
        $existingStatus = ClearanceStatus::with('approver')
            ->where('clearance_id', $clearance->id)
            ->where('department_id', $currentUser->department_id)
            ->first();

        // Redirect to student clearance processing page
        return view('adviser.student.student-info', compact(
            'student',
            'clearance',
            'existingStatus',
            'canClear'
        ));
    }

    private function canAdviserClearStudent($adviser, $student)
    {
        // Define restricted departments
        $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS, SBAHM

        // If adviser is from a restricted department, they can only clear students from their own department
        if (in_array($adviser->department_id, $restrictedDepartments)) {
            return $adviser->department_id === $student->department_id;
        }

        // Other departments (like Library, BAO) can clear all students
        return true;
    }
}
