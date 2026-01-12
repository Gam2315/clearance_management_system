<?php

namespace App\Http\Controllers\Officer;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OfficerController extends Controller
{
    public function OfficerDashboard()
    {
        $user = Auth::user();

        // Check officer's designation to determine which students to show
        // designation_id = 5 (UNIWIDE) → Show UNIWIDE students (is_uniwide = 1)
        // designation_id = 1,2,3,4 (SW_SITE, SW_SASTE, SW_SNAHS, SW_SBAHM) → Show non-UNIWIDE students (is_uniwide = 0)

        $departmentCounts = [];

        if ($user->designation_id == 5) {
            // UNIWIDE Officer - Show only UNIWIDE students from all departments
            $departments = Department::whereIn('id', [1, 2, 3, 4])->get();

            foreach ($departments as $department) {
                $count = Student::where('department_id', $department->id)
                                ->where('is_uniwide', 1)
                                ->where('is_archived', false)
                                ->count();

                $departmentCounts[] = [
                    'id' => $department->id,
                    'name' => $department->department_name,
                    'count' => $count
                ];
            }

            // Total UNIWIDE students across all departments
            $totalStudents = Student::where('is_uniwide', 1)
                                   ->where('is_archived', false)
                                   ->count();
        } else {
            // Governor (Department-specific Officer) - Show only non-UNIWIDE students from their department
            $totalStudents = Student::where('department_id', $user->department_id)
                                   ->where('is_uniwide', 0)
                                   ->where('is_archived', false)
                                   ->count();

            // Get the department name for the single department count
            $department = Department::find($user->department_id);
            if ($department) {
                $departmentCounts[] = [
                    'id' => $department->id,
                    'name' => $department->department_name,
                    'count' => $totalStudents
                ];
            }
        }

        // Load related data for view
        $clearances = Clearance::all();
        $departments = Department::all();
        $courses = Course::all();

        return view('officer.dashboard', compact(
            'departments',
            'courses',
            'clearances',
            'totalStudents',
            'departmentCounts'
        ));
    }

    

    public function OfficerLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function account_setting()
    {
        $user = Auth::user();
        return view('officer.account-settings.settings', compact('user'));
    }

    public function updateSetting(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'suffix_name' => 'nullable|string|max:255',
        ]);

        $user = User::find(Auth::id());
        $user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'middlename' => $request->middlename,
            'suffix_name' => $request->suffix_name,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::find(Auth::id());

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect!');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}
