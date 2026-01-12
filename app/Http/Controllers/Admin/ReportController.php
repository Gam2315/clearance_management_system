<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;
use App\Models\Violation;
use App\Models\GraduatedStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $academicYears = AcademicYear::all();
        
        return view('admin.reports.clearance-reports', compact('departments', 'academicYears'));
    }

    public function deanOsaReports()
    {
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $academicYears = AcademicYear::all();

        // Debug information
        $debugInfo = [
            'departments_count' => $departments->count(),
            'academic_years_count' => $academicYears->count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('is_archived', false)->count(),
            'total_clearances' => Clearance::count(),
        ];

        Log::info('Dean OSA Reports Debug Info', $debugInfo);

        return view('admin.reports.dean-osa-reports', compact('departments', 'academicYears', 'debugInfo'));
    }

    public function automaticClearanceInterface()
    {
        return view('admin.reports.automatic-clearance');
    }

    public function previousYearsReports()
    {
       $departments = Department::whereIn('department_code', ['SITE', 'SASTE', 'SNAHS', 'SBAHM'])
                         ->whereIn('id', [1, 2, 3, 4])
                         ->get();

        // Get only inactive academic years (previous years)
        $academicYears = AcademicYear::where('status', 'inactive')
            ->orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();



        return view('admin.reports.previous-years-reports', compact('departments', 'academicYears'));
    }

    public function getPreviousYearsData(Request $request)
    {
        try {
            $academicId = $request->input('academic_id');
            $departmentId = $request->input('department_id');

            if (!$academicId) {
                return response()->json(['error' => 'Academic year is required'], 400);
            }

            // Verify that the academic year is inactive (previous year)
            $academicYear = AcademicYear::where('id', $academicId)
                ->where('status', 'inactive')
                ->first();

            if (!$academicYear) {
                return response()->json(['error' => 'Invalid academic year or academic year is still active'], 400);
            }

            // Base query for clearances in the selected academic year
            $query = Clearance::with(['student.user', 'student.department', 'student.courses', 'academicYear'])
                ->where('academic_id', $academicId);

            // Filter by department if specified
            if ($departmentId) {
                $query->whereHas('student', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            $clearances = $query->get();

            $data = [];
            foreach ($clearances as $clearance) {
                $student = $clearance->student;
                if (!$student || !$student->user) continue;

                // Get all clearance statuses for this clearance
                $statuses = $clearance->statuses()->with('department')->get();

                // Group statuses by department
                $departmentStatuses = [];
                foreach ($statuses as $status) {
                    if ($status->department) {
                        $departmentStatuses[$status->department->department_code] = $status->status;
                    }
                }

                $data[] = [
                    'id' => $clearance->id,
                    'student_number' => $student->student_number,
                    'student_name' => $student->user->name,
                    'department' => $student->department ? $student->department->department_code : 'N/A',
                    'course' => $student->courses ? $student->courses->course_name : 'N/A',
                    'year' => $student->year,
                    'overall_status' => $clearance->overall_status,
                    'is_locked' => $clearance->is_locked,
                    'lock_reason' => $clearance->lock_reason,
                    'academic_year' => $academicYear->academic_year . ' - ' . $academicYear->semester,
                    'department_statuses' => $departmentStatuses,
                    'created_at' => $clearance->created_at ? $clearance->created_at->format('M d, Y') : 'N/A',
                    'updated_at' => $clearance->updated_at ? $clearance->updated_at->format('M d, Y') : 'N/A'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'academic_year_info' => [
                    'academic_year' => $academicYear->academic_year,
                    'semester' => $academicYear->semester,
                    'status' => $academicYear->status
                ],
                'total_records' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching data'], 500);
        }
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
        Log::info('=== REPORTS DEBUG START ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Request URL: ' . $request->url());
        Log::info('Request headers: ', $request->headers->all());
        Log::info('Request body: ', $request->all());
        Log::info('User: ', auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'role' => auth()->user()->role
        ] : 'Not authenticated');

        try {
            // Check if user is authenticated and has admin role
            if (!auth()->check()) {
                Log::error('User not authenticated');
                return response()->json(['error' => 'Not authenticated', 'students' => []], 401);
            }

            if (auth()->user()->role !== 'admin') {
                Log::error('User not admin: ' . auth()->user()->role);
                return response()->json(['error' => 'Not authorized', 'students' => []], 403);
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

            // Get students
            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id);

            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $students = $query->get();
            Log::info('Students query result', ['count' => $students->count()]);

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
            // More lenient validation for debugging
            $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
                'semester' => 'nullable|string',
            ]);

            Log::info('Getting students cleared', [
                'academic_id' => $request->academic_id,
                'department_id' => $request->department_id,
                'semester' => $request->semester
            ]);

            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id);

            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

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
        $request->validate([
            'academic_id' => 'required|exists:academic_years,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $academicYear = AcademicYear::find($request->academic_id);
        $department = $request->department_id ? Department::find($request->department_id) : null;

        $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
            ->where('is_archived', false)
            ->where('academic_id', $request->academic_id);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

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

        return view('admin.reports.print-not-cleared', compact('notClearedStudents', 'academicYear', 'department'));
    }

    public function printClearedList(Request $request)
    {
        $request->validate([
            'academic_id' => 'required|exists:academic_years,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $academicYear = AcademicYear::find($request->academic_id);
        $department = $request->department_id ? Department::find($request->department_id) : null;

        $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
            ->where('is_archived', false)
            ->where('academic_id', $request->academic_id);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $students = $query->get();

        $clearedStudents = $students->filter(function ($student) use ($request) {
            $clearance = $student->clearances()
                ->where('academic_id', $request->academic_id)
                ->first();

            return $clearance && $clearance->isFullyCleared();
        });

        return view('admin.reports.print-cleared', compact('clearedStudents', 'academicYear', 'department'));
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
}