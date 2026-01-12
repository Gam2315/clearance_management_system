<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
class AdminController extends Controller
{
    public function AdminDashboard()
    {
        $totalStudents = Student::count();
        $totalDepartment = Department::count();
        $totalCourse = Course::count();
        $totalUsers = User::whereIn('role', ['dean', 'employee', 'adviser'])->count();

        // Get active academic year
        $activeAcademicYear = AcademicYear::where('status', 'active')->first();

        // Load departments with their relationships and clearance data
        $departments = Department::whereIn('id', [1, 2, 3, 4])
                                ->with(['courses', 'students'])
                                ->get();

        // Add clearance statistics to each department
        $departments = $departments->map(function ($department) use ($activeAcademicYear) {
            if ($activeAcademicYear) {
                // Get students in this department
                $studentsInDept = $department->students;
                $totalStudentsInDept = $studentsInDept->count();

                if ($totalStudentsInDept > 0) {
                    // Count cleared students in this department
                    $clearedStudents = Clearance::where('department_id', $department->id)
                                               ->where('academic_id', $activeAcademicYear->id)
                                               ->where('overall_status', 'cleared')
                                               ->count();

                    // Calculate completion percentage
                    $department->clearance_completion = round(($clearedStudents / $totalStudentsInDept) * 100, 1);
                    $department->cleared_count = $clearedStudents;
                    $department->pending_count = $totalStudentsInDept - $clearedStudents;
                } else {
                    $department->clearance_completion = 0;
                    $department->cleared_count = 0;
                    $department->pending_count = 0;
                }
            } else {
                $department->clearance_completion = 0;
                $department->cleared_count = 0;
                $department->pending_count = 0;
            }

            return $department;
        });

        // Overall clearance statistics
        $totalClearances = 0;
        $completedClearances = 0;
        $pendingClearances = 0;
        $blockedClearances = 0;

        if ($activeAcademicYear) {
            $totalClearances = Clearance::where('academic_id', $activeAcademicYear->id)->count();
            $completedClearances = Clearance::where('academic_id', $activeAcademicYear->id)
                                           ->where('overall_status', 'cleared')
                                           ->count();
            $pendingClearances = Clearance::where('academic_id', $activeAcademicYear->id)
                                         ->where('overall_status', 'pending')
                                         ->count();
            $blockedClearances = Clearance::where('academic_id', $activeAcademicYear->id)
                                         ->where('is_locked', true)
                                         ->count();
        }

        $courses = Course::all(); // Fetch all courses

        // Academic Year Reports
        $academicYearReports = $this->getAcademicYearReports();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalDepartment',
            'totalCourse',
            'totalUsers',
            'departments',
            'courses',
            'totalClearances',
            'completedClearances',
            'pendingClearances',
            'blockedClearances',
            'activeAcademicYear',
            'academicYearReports'
        ));
    }

    private function getAcademicYearReports()
    {
        // Only show active academic year
        $academicYears = AcademicYear::where('status', 'active')->orderBy('created_at', 'desc')->get();

        $reports = $academicYears->map(function ($academicYear) {
            // Get all clearances for this academic year
            $clearances = Clearance::where('academic_id', $academicYear->id)->get();
            $totalStudents = $clearances->count();

            if ($totalStudents == 0) {
                return (object) [
                    'id' => $academicYear->id,
                    'year_name' => $academicYear->academic_year . ' - ' . $academicYear->semester,
                    'start_date' => $academicYear->academic_year,
                    'end_date' => $academicYear->semester,
                    'status' => $academicYear->status,
                    'total_students' => 0,
                    'completed_clearances' => 0,
                    'pending_clearances' => 0,
                    'blocked_clearances' => 0,
                    'completion_percentage' => 0,
                    'completed_percentage' => 0,
                    'pending_percentage' => 0,
                    'blocked_percentage' => 0,
                    'department_stats' => collect([])
                ];
            }

            $completedClearances = $clearances->where('overall_status', 'cleared')->count();
            $pendingClearances = $clearances->where('overall_status', 'pending')->count();
            $blockedClearances = $clearances->where('is_locked', true)->count();

            $completionPercentage = round(($completedClearances / $totalStudents) * 100, 1);
            $completedPercentage = round(($completedClearances / $totalStudents) * 100, 1);
            $pendingPercentage = round(($pendingClearances / $totalStudents) * 100, 1);
            $blockedPercentage = round(($blockedClearances / $totalStudents) * 100, 1);

            // Get department statistics for this academic year
            $departmentStats = Department::with(['clearances' => function($query) use ($academicYear) {
                $query->where('academic_id', $academicYear->id);
            }])->get()->map(function ($department) {
                $clearances = $department->clearances;
                $totalStudents = $clearances->count();

                if ($totalStudents == 0) {
                    return (object) [
                        'department_name' => $department->department_name,
                        'total_students' => 0,
                        'cleared_students' => 0,
                        'pending_students' => 0,
                        'blocked_students' => 0,
                        'completion_rate' => 0,
                        'cleared_percentage' => 0,
                        'pending_percentage' => 0,
                        'blocked_percentage' => 0
                    ];
                }

                $clearedStudents = $clearances->where('overall_status', 'cleared')->count();
                $pendingStudents = $clearances->where('overall_status', 'pending')->count();
                $blockedStudents = $clearances->where('is_locked', true)->count();
                $completionRate = round(($clearedStudents / $totalStudents) * 100, 1);

                // Calculate percentages for progress bar
                $clearedPercentage = round(($clearedStudents / $totalStudents) * 100, 1);
                $pendingPercentage = round(($pendingStudents / $totalStudents) * 100, 1);
                $blockedPercentage = round(($blockedStudents / $totalStudents) * 100, 1);

                return (object) [
                    'department_name' => $department->department_name,
                    'total_students' => $totalStudents,
                    'cleared_students' => $clearedStudents,
                    'pending_students' => $pendingStudents,
                    'blocked_students' => $blockedStudents,
                    'completion_rate' => $completionRate,
                    'cleared_percentage' => $clearedPercentage,
                    'pending_percentage' => $pendingPercentage,
                    'blocked_percentage' => $blockedPercentage
                ];
            })->filter(function ($stat) {
                return $stat->total_students > 0; // Only include departments with students
            });

            return (object) [
                'id' => $academicYear->id,
                'year_name' => $academicYear->academic_year . ' - ' . $academicYear->semester,
                'start_date' => $academicYear->academic_year,
                'end_date' => $academicYear->semester,
                'status' => $academicYear->status,
                'total_students' => $totalStudents,
                'completed_clearances' => $completedClearances,
                'pending_clearances' => $pendingClearances,
                'blocked_clearances' => $blockedClearances,
                'completion_percentage' => $completionPercentage,
                'completed_percentage' => $completedPercentage,
                'pending_percentage' => $pendingPercentage,
                'blocked_percentage' => $blockedPercentage,
                'department_stats' => $departmentStats
            ];
        });

        return $reports;
    }

    public function account_setting()
    {
        // Fetch the logged-in user's information
        $id = Auth::user()->id;
        $user = User::find($id);
        $departments = Department::all();

        return view('admin.account-settings.settings', compact('user', 'departments'));
    }

    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function updateSetting(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // max 5MB
        ]);

        $user = User::findOrFail($id);

        // Update basic fields
     
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->suffix_name = $request->suffix_name;

        // Handle image upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/images/upload/admin-images/'), $filename);

            // Delete old picture if exists
            if (!empty($user->picture) && file_exists(public_path('assets/images/upload/admin-images/' . $user->picture))) {
                unlink(public_path('assets/images/upload/admin-images/' . $user->picture));
            }

            $user->picture = $filename;
        }

        $user->save();

        return redirect()->back()->with('success', 'User information updated successfully.');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/|min:8|confirmed',
        ]);
        
        $user = User::findOrFail($id);
        
        // Check if old password matches
        if (!Hash::check($request->oldPassword, $user->password)) {
            return back()->withErrors(['oldPassword' => 'Old password is incorrect.']);
        }
        
        // Update new password
        $user->password = Hash::make($request->newPassword);
        $user->save();
        
        return back()->with('success', 'Password updated successfully!');
        
    }


}
