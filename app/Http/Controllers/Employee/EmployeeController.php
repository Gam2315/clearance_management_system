<?php

namespace App\Http\Controllers\Employee;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
     public function employeeDashboard()
    {
       
        $user = Auth::user();
        $employeeDepartmentId = $user->department_id;
        $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments
        $isRestricted = in_array($employeeDepartmentId, $restrictedDepartments);

        // Initialize department counts
        $departmentCounts = [];

        if ($isRestricted) {
            // For restricted departments, only count their own students
            $totalStudents = Student::where('department_id', $employeeDepartmentId)->count();

            // Get the department name
            $department = Department::find($employeeDepartmentId);
            if ($department) {
                $departmentCounts[] = [
                    'id' => $department->id,
                    'name' => $department->department_name,
                    'count' => $totalStudents
                ];
            }
        } else {
            // For non-restricted departments, count students in all academic departments
            $departments = Department::whereIn('id', [1, 2, 3, 4])->get();

            foreach ($departments as $department) {
                $count = Student::where('department_id', $department->id)->count();
                $departmentCounts[] = [
                    'id' => $department->id,
                    'name' => $department->department_name,
                    'count' => $count
                ];
            }

            // Calculate total students across all departments
            $totalStudents = Student::count();
        }

        // Get other data needed for the dashboard
        $clearances = Clearance::all();
        $departments = Department::all();
        $courses = Course::all();

        return view('employee.dashboard', compact(
            'departments',
            'courses',
            'clearances',
            'totalStudents',
            'departmentCounts',
            'isRestricted'
        ));
    }

     public function account_setting()
    {
        // Fetch the logged-in user's information
        $id = Auth::user()->id;
        $user = User::find($id);
        $departments = Department::all();

        return view('employee.account-settings.settings', compact('user', 'departments'));
    }

    public function EmployeeLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

}
