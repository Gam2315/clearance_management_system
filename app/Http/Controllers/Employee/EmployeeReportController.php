<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeReportController extends Controller
{
    public function __construct()
    {
        // Middleware to ensure only OSA employees can access reports
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            // Check if user is employee and belongs to OSA department (ID: 14)
            if ($user->role !== 'employee' || $user->department_id != 6) {
                abort(403, 'Access denied. Reports are only available for OSA employees.');
            }
            
            return $next($request);
        });
    }

    public function employeeReports()
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

        Log::info('Employee OSA Reports Debug Info', $debugInfo);

        return view('employee.reports.dean-osa-reports', compact('departments', 'academicYears', 'debugInfo'));
    }

    public function getStudentsNotCleared(Request $request)
    {
        Log::info('Employee getStudentsNotCleared called', $request->all());

        try {
            // Check if user is authenticated and has employee role
            if (!auth()->check()) {
                Log::error('User not authenticated');
                return response()->json(['error' => 'Not authenticated', 'students' => []], 401);
            }

            if (auth()->user()->role !== 'employee') {
                Log::error('User not employee: ' . auth()->user()->role);
                return response()->json(['error' => 'Not authorized', 'students' => []], 403);
            }

            // Additional check for OSA department
            if (auth()->user()->department_id != 6) {
                Log::error('Employee not from OSA department: ' . auth()->user()->department_id);
                return response()->json(['error' => 'Access denied. Reports only for OSA employees.', 'students' => []], 403);
            }

            // More lenient validation for debugging
            $validated = $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
                'semester' => 'nullable|string',
            ]);

            Log::info('Employee validation passed', $validated);

            // Check if academic year exists
            $academicYear = AcademicYear::find($request->academic_id);
            if (!$academicYear) {
                Log::error('Academic year not found: ' . $request->academic_id);
                return response()->json(['error' => 'Academic year not found', 'students' => []], 404);
            }

            Log::info('Academic year found', [
                'id' => $academicYear->id,
                'year' => $academicYear->academic_year,
                'semester' => $academicYear->semester
            ]);

            // Get students
            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id);

            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $students = $query->get();
            Log::info('Total students found: ' . $students->count());

            $notClearedStudents = [];

            foreach ($students as $student) {
                $clearance = $student->clearances()
                    ->where('academic_id', $request->academic_id)
                    ->first();

                Log::info('Processing student: ' . $student->student_number, [
                    'has_clearance' => $clearance ? 'yes' : 'no',
                    'clearance_id' => $clearance ? $clearance->id : null
                ]);

                // Check if student is fully cleared
                if (!$clearance || !$clearance->isFullyCleared()) {
                    // Get pending departments using the same logic as other controllers
                    $pendingDepartments = $this->getPendingDepartments($student, $clearance);

                    $notClearedStudents[] = [
                        'student_number' => $student->student_number,
                        'name' => ($student->user->lastname ?? '') . ', ' . 
                                 ($student->user->firstname ?? '') . ' ' . 
                                 ($student->user->middlename ?? ''),
                        'department' => $student->department->department_name ?? 'N/A',
                        'program' => $student->courses->course_name ?? 'N/A',
                        'year' => $student->year ?? 'N/A',
                        'has_violations' => $student->has_violations,
                        'clearance_locked' => $clearance ? $clearance->is_locked : false,
                        'pending_departments' => $pendingDepartments
                    ];
                }
            }

            Log::info('Students not cleared count: ' . count($notClearedStudents));

            return response()->json([
                'students' => $notClearedStudents,
                'total_students' => $students->count(),
                'not_cleared_count' => count($notClearedStudents),
                'debug_info' => [
                    'academic_year' => $academicYear->academic_year,
                    'department_filter' => $request->department_id ? 'Applied' : 'None',
                    'user_role' => auth()->user()->role,
                    'user_department' => auth()->user()->department_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getStudentsNotCleared: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'students' => []
            ], 500);
        }
    }

    public function getStudentsCleared(Request $request)
    {
        try {
            // Check OSA department access
            if (auth()->user()->department_id != 14) {
                return response()->json(['error' => 'Access denied. Reports only for OSA employees.', 'students' => []], 403);
            }

            $request->validate([
                'academic_id' => 'required|integer',
                'department_id' => 'nullable|integer',
            ]);

            $query = Student::with(['user', 'department', 'courses', 'clearances.statuses'])
                ->where('is_archived', false)
                ->where('academic_id', $request->academic_id);

            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $students = $query->get();
            $clearedStudents = [];

            foreach ($students as $student) {
                $clearance = $student->clearances()
                    ->where('academic_id', $request->academic_id)
                    ->first();

                if ($clearance && $clearance->isFullyCleared()) {
                    $clearedStudents[] = [
                        'student_number' => $student->student_number,
                        'name' => ($student->user->lastname ?? '') . ', ' . 
                                 ($student->user->firstname ?? '') . ' ' . 
                                 ($student->user->middlename ?? ''),
                        'department' => $student->department->department_name ?? 'N/A',
                        'program' => $student->courses->course_name ?? 'N/A',
                        'year' => $student->year ?? 'N/A',
                        'cleared_date' => $clearance->updated_at ? $clearance->updated_at->format('M d, Y') : 'N/A'
                    ];
                }
            }

            return response()->json([
                'students' => $clearedStudents,
                'total_students' => $students->count(),
                'cleared_count' => count($clearedStudents)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getStudentsCleared: ' . $e->getMessage());

            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'students' => []
            ], 500);
        }
    }

    public function printNotClearedList(Request $request)
    {
        // Check OSA department access
        if (auth()->user()->department_id != 14) {
            abort(403, 'Access denied. Reports are only available for OSA employees.');
        }

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
        $notClearedStudents = [];

        foreach ($students as $student) {
            $clearance = $student->clearances()
                ->where('academic_id', $request->academic_id)
                ->first();

            if ($clearance && !$clearance->isFullyCleared()) {
                $notClearedStudents[] = $student;
            }
        }

        return view('employee.reports.print-not-cleared', compact('notClearedStudents', 'academicYear', 'department'));
    }

    public function printClearedList(Request $request)
    {
        // Check OSA department access
        if (auth()->user()->department_id != 14) {
            abort(403, 'Access denied. Reports are only available for OSA employees.');
        }

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
        $clearedStudents = [];

        foreach ($students as $student) {
            $clearance = $student->clearances()
                ->where('academic_id', $request->academic_id)
                ->first();

            if ($clearance && $clearance->isFullyCleared()) {
                $clearedStudents[] = $student;
            }
        }

        return view('employee.reports.print-cleared', compact('clearedStudents', 'academicYear', 'department'));
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
}
