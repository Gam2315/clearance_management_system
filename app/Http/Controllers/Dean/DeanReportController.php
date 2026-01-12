<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeanReportController extends Controller
{
    public function index()
    {
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $academicYears = AcademicYear::all();
        
        return view('admin.reports.clearance-reports', compact('departments', 'academicYears'));
    }

    public function deanReports()
    {
        // Get dean's department only
        $deanDepartmentId = auth()->user()->department_id;
        $departments = Department::where('id', $deanDepartmentId)->get();
        $academicYears = AcademicYear::all();

        // Debug information - restrict to dean's department
        $debugInfo = [
            'departments_count' => $departments->count(),
            'academic_years_count' => $academicYears->count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('is_archived', false)->count(),
            'dean_department_students' => Student::where('is_archived', false)
                ->where('department_id', $deanDepartmentId)->count(),
            'total_clearances' => Clearance::count(),
            'dean_department_id' => $deanDepartmentId,
        ];

        Log::info('Dean OSA Reports Debug Info', $debugInfo);

        return view('dean.reports.dean-osa-reports', compact('departments', 'academicYears', 'debugInfo'));
    }

    public function automaticClearanceInterface()
    {
        return view('admin.reports.automatic-clearance');
    }

    public function debugReports(Request $request)
    {
        try {
            $data = [
                'request_data' => $request->all(),
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'role' => auth()->user()->role,
                    'name' => auth()->user()->name
                ] : null,
                'students_count' => Student::count(),
                'academic_years_count' => AcademicYear::count(),
                'departments_count' => Department::count(),
                'clearances_count' => Clearance::count(),
                'sample_student' => Student::with(['user', 'department', 'courses'])->first(),
                'sample_academic_year' => AcademicYear::first(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function getStudentsNotCleared(Request $request)
    {
        // Log everything for debugging
        Log::info('=== DEAN REPORTS DEBUG START ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Request URL: ' . $request->url());
        Log::info('Request headers: ', $request->headers->all());
        Log::info('Request body: ', $request->all());
        Log::info('User: ', auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'role' => auth()->user()->role,
            'department_id' => auth()->user()->department_id
        ] : 'Not authenticated');

        try {
            // Check if user is authenticated and has dean role
            if (!auth()->check()) {
                Log::error('User not authenticated');
                return response()->json(['error' => 'Not authenticated', 'students' => []], 401);
            }

            if (auth()->user()->role !== 'dean') {
                Log::error('User not dean: ' . auth()->user()->role);
                return response()->json(['error' => 'Not authorized', 'students' => []], 403);
            }

            // Get dean's department
            $deanDepartmentId = auth()->user()->department_id;
            if (!$deanDepartmentId) {
                Log::error('Dean has no department assigned');
                return response()->json(['error' => 'Dean has no department assigned', 'students' => []], 403);
            }

            // More lenient validation for debugging
            $validated = $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
                'semester' => 'nullable|string',
            ]);

            Log::info('Validation passed', $validated);

            // Check if academic year exists
            $academicYear = AcademicYear::find($request->academic_id);
            Log::info('Academic year check', ['found' => $academicYear ? [
                'id' => $academicYear->id,
                'academic_year' => $academicYear->academic_year,
                'semester' => $academicYear->semester
            ] : null]);

            if (!$academicYear) {
                $availableYears = AcademicYear::all(['id', 'academic_year', 'semester']);
                Log::info('Available academic years', $availableYears->toArray());

                return response()->json([
                    'error' => 'Academic year not found',
                    'academic_id' => $request->academic_id,
                    'available_academic_years' => $availableYears,
                    'students' => []
                ], 404);
            }

            // Get students - restrict to dean's department only
            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id)
                ->where('department_id', $deanDepartmentId); // Only students from dean's department

            // Note: Ignore department_id from request since dean can only see their own department
            // if ($request->department_id) {
            //     $query->where('department_id', $request->department_id);
            // }

            $students = $query->get();
            Log::info('Students query result', [
                'count' => $students->count(),
                'academic_id_requested' => $request->academic_id,
                'department_id_requested' => $request->department_id
            ]);

            // If no students found, provide helpful debugging info
            if ($students->count() === 0) {
                $allStudentsCount = Student::where('is_archived', false)->count();
                $studentsInOtherAcademicYears = Student::where('is_archived', false)
                    ->where('academic_id', '!=', $request->academic_id)
                    ->get(['academic_id', 'student_number'])
                    ->groupBy('academic_id');

                Log::info('No students found - Debug info', [
                    'total_active_students' => $allStudentsCount,
                    'students_in_other_academic_years' => $studentsInOtherAcademicYears->map(function($students, $academicId) {
                        return [
                            'academic_id' => $academicId,
                            'count' => $students->count(),
                            'academic_year_info' => AcademicYear::find($academicId)?->only(['academic_year', 'semester'])
                        ];
                    })
                ]);
            }

            // Sample student for debugging
            if ($students->count() > 0) {
                $sampleStudent = $students->first();
                Log::info('Sample student', [
                    'id' => $sampleStudent->id,
                    'student_number' => $sampleStudent->student_number,
                    'year' => $sampleStudent->year,
                    'department_id' => $sampleStudent->department_id,
                    'academic_id' => $sampleStudent->academic_id
                ]);
            }

            $notClearedStudents = collect();

            foreach ($students as $student) {
                $clearance = $student->clearances()
                    ->where('academic_id', $request->academic_id)
                    ->when($request->semester, function ($q) use ($request) {
                        return $q->where('semester', $request->semester);
                    })
                    ->first();

                if (!$clearance || !$clearance->isFullyCleared()) {
                    $notClearedStudents->push($student);
                }
            }

            Log::info('Not cleared students count: ' . $notClearedStudents->count());

            // If no students found, provide helpful suggestions
            $suggestions = [];
            if ($students->count() === 0) {
                $allAcademicYears = AcademicYear::all(['id', 'academic_year', 'semester']);
                $studentsPerAcademicYear = [];

                foreach ($allAcademicYears as $ay) {
                    $count = Student::where('academic_id', $ay->id)->where('is_archived', false)->count();
                    if ($count > 0) {
                        $studentsPerAcademicYear[] = [
                            'academic_id' => $ay->id,
                            'academic_year' => $ay->academic_year,
                            'semester' => $ay->semester,
                            'student_count' => $count
                        ];
                    }
                }

                if (!empty($studentsPerAcademicYear)) {
                    $suggestions[] = 'Try selecting a different academic year. Students found in: ' .
                        implode(', ', array_map(function($ay) {
                            return $ay['academic_year'] . ' - ' . $ay['semester'] . ' (' . $ay['student_count'] . ' students)';
                        }, $studentsPerAcademicYear));
                }
            }

            $result = [
                'students' => $notClearedStudents->map(function ($student) {
                    $clearance = $student->clearances()->latest()->first();
                    return [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'name' => ($student->user ? $student->user->firstname . ' ' . $student->user->lastname : 'N/A'),
                        'department' => ($student->department ? $student->department->department_name : 'N/A'),
                        'program' => ($student->courses ? $student->courses->course_name : 'N/A'),
                        'year' => $student->year,
                        'has_violations' => $student->has_violations,
                        'clearance_locked' => $clearance ? $clearance->is_locked : false,
                        'pending_departments' => $this->getPendingDepartments($student, $clearance),
                    ];
                })->values(),
                'total_students' => $students->count(),
                'not_cleared_count' => $notClearedStudents->count(),
                'suggestions' => $suggestions,
                'debug_info' => [
                    'academic_year' => [
                        'id' => $academicYear->id,
                        'academic_year' => $academicYear->academic_year,
                        'semester' => $academicYear->semester
                    ],
                    'request_data' => $request->all(),
                    'user_role' => auth()->user()->role
                ]
            ];

            Log::info('Final result', $result);
            Log::info('=== REPORTS DEBUG END ===');

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Exception in getStudentsNotCleared: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to fetch students',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'students' => []
            ], 500);
        }
    }

    public function getStudentsCleared(Request $request)
    {
        try {
            // Check if user is authenticated and has dean role
            if (!auth()->check() || auth()->user()->role !== 'dean') {
                return response()->json(['error' => 'Not authorized', 'students' => []], 403);
            }

            // Get dean's department
            $deanDepartmentId = auth()->user()->department_id;
            if (!$deanDepartmentId) {
                return response()->json(['error' => 'Dean has no department assigned', 'students' => []], 403);
            }

            // More lenient validation for debugging
            $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
                'semester' => 'nullable|string',
            ]);

            Log::info('Getting students cleared for dean department', [
                'academic_id' => $request->academic_id,
                'dean_department_id' => $deanDepartmentId,
                'semester' => $request->semester
            ]);

            // Restrict to dean's department only
            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id)
                ->where('department_id', $deanDepartmentId); // Only students from dean's department

            $students = $query->get();
            Log::info('Found students count for cleared: ' . $students->count());

            $clearedStudents = $students->filter(function ($student) use ($request) {
                $clearance = $student->clearances()
                    ->where('academic_id', $request->academic_id)
                    ->when($request->semester, function ($q) use ($request) {
                        return $q->where('semester', $request->semester);
                    })
                    ->first();

                return $clearance && $clearance->isFullyCleared();
            });

            Log::info('Cleared students count: ' . $clearedStudents->count());

            return response()->json([
                'students' => $clearedStudents->map(function ($student) {
                    $clearance = $student->clearances()->latest()->first();
                    return [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'name' => ($student->user ? $student->user->firstname . ' ' . $student->user->lastname : 'N/A'),
                        'department' => ($student->department ? $student->department->department_name : 'N/A'),
                        'program' => ($student->courses ? $student->courses->course_name : 'N/A'),
                        'year' => $student->year,
                        'cleared_date' => $clearance ? $clearance->updated_at->format('Y-m-d') : null,
                    ];
                })->values(),
                'total_students' => $students->count(),
                'cleared_count' => $clearedStudents->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getStudentsCleared: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch cleared students',
                'message' => $e->getMessage(),
                'students' => []
            ], 500);
        }
    }

    public function printNotClearedList(Request $request)
    {
        // Check if user is authenticated and has dean role
        if (!auth()->check() || auth()->user()->role !== 'dean') {
            abort(403, 'Not authorized');
        }

        // Get dean's department
        $deanDepartmentId = auth()->user()->department_id;
        if (!$deanDepartmentId) {
            abort(403, 'Dean has no department assigned');
        }

        $request->validate([
            'academic_id' => 'required|exists:academic_years,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $academicYear = AcademicYear::find($request->academic_id);
        $department = Department::find($deanDepartmentId); // Use dean's department

        // Restrict to dean's department only
        $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
            ->where('is_archived', false)
            ->where('academic_id', $request->academic_id)
            ->where('department_id', $deanDepartmentId); // Only students from dean's department

        $students = $query->get();

        $notClearedStudents = $students->filter(function ($student) use ($request) {
            $clearance = $student->clearances()
                ->where('academic_id', $request->academic_id)
                ->first();

            if (!$clearance) {
                return true;
            }

            return !$clearance->isFullyCleared();
        });

        return view('dean.reports.print-not-cleared', compact('notClearedStudents', 'academicYear', 'department'));
    }

    public function printClearedList(Request $request)
    {
        // Check if user is authenticated and has dean role
        if (!auth()->check() || auth()->user()->role !== 'dean') {
            abort(403, 'Not authorized');
        }

        // Get dean's department
        $deanDepartmentId = auth()->user()->department_id;
        if (!$deanDepartmentId) {
            abort(403, 'Dean has no department assigned');
        }

        $request->validate([
            'academic_id' => 'required|exists:academic_years,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $academicYear = AcademicYear::find($request->academic_id);
        $department = Department::find($deanDepartmentId); // Use dean's department

        // Restrict to dean's department only
        $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
            ->where('is_archived', false)
            ->where('academic_id', $request->academic_id)
            ->where('department_id', $deanDepartmentId); // Only students from dean's department

        $students = $query->get();

        $clearedStudents = $students->filter(function ($student) use ($request) {
            $clearance = $student->clearances()
                ->where('academic_id', $request->academic_id)
                ->first();

            return $clearance && $clearance->isFullyCleared();
        });

        return view('dean.reports.print-cleared', compact('clearedStudents', 'academicYear', 'department'));
    }

 

    private function getPendingDepartments($student, $clearance)
    {
        if (!$clearance) {
            return ['All departments'];
        }

        // Use the clearance model's getPendingDepartments method which properly
        // compares required departments with cleared departments
        $pendingDepartmentNames = $clearance->getPendingDepartments();

        // If no pending departments found, student is fully cleared
        if (empty($pendingDepartmentNames)) {
            return [];
        }

        return $pendingDepartmentNames;
    }


    public function detectStudentForClearance(Request $request)
    {
        try {
            // Get the latest NFC tap from the database
            $latestTap = DB::table('nfc_taps')->latest('created_at')->first();

            if (!$latestTap) {
                return response()->json([
                    'found' => false,
                    'message' => 'No RFID card detected. Please tap your ID card.',
                    'status' => 'waiting'
                ]);
            }

            // Check if this tap is recent (within last 30 seconds)
            $tapTime = Carbon::parse($latestTap->created_at);
            $now = Carbon::now();

            if ($now->diffInSeconds($tapTime) > 30) {
                return response()->json([
                    'found' => false,
                    'message' => 'Please tap your ID card again.',
                    'status' => 'expired'
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

            // Check if employee can clear this student
            $currentUser = auth()->user();
            $canClear = $this->canEmployeeClearStudent($currentUser, $student);

            // Get existing status for this department
            $existingStatus = $clearance->statuses()
                ->where('department_id', $currentUser->department_id)
                ->first();

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
                ] : null
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

    public function getViolationReport(Request $request)
    {
        try {
            $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
            ]);

            // This would need to be implemented based on your violation model structure
            // For now, returning empty array
            return response()->json([
                'violations' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getViolationReport: ' . $e->getMessage());

            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'violations' => []
            ], 500);
        }
    }

    public function getGraduatedStudents(Request $request)
    {
        try {
            $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
            ]);

            // This would need to be implemented based on your graduated students model structure
            // For now, returning empty array
            return response()->json([
                'students' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getGraduatedStudents: ' . $e->getMessage());

            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'students' => []
            ], 500);
        }
    }
}