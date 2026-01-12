<?php

namespace App\Http\Controllers\Adviser;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\ClearanceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdviserController extends Controller
{
    public function AdviserDashboard()
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

        return view('adviser.dashboard', compact(
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

        return view('adviser.account-settings.settings', compact('user', 'departments'));
    }

    public function AdviserLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    // PSG Adviser specific functions
    public function psgAdviserDashboard()
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        // Get PSG adviser specific statistics
        $totalStudents = Student::where('department_id', $departmentId)->count();

        // Get students requiring PSG adviser clearance
        $studentsRequiringClearance = Student::where('department_id', $departmentId)
            ->whereHas('clearances', function($query) use ($departmentId) {
                $query->where('is_archived', 0)
                      ->whereDoesntHave('statuses', function($subQuery) use ($departmentId) {
                          $subQuery->where('department_id', $departmentId)
                                   ->where('status', 'cleared');
                      });
            })->count();

        // Get recent clearances signed by this PSG adviser
        $recentClearances = ClearanceStatus::where('department_id', $departmentId)
                                         ->where('approved_by', $user->id)
                                         ->where('status', 'cleared')
                                         ->with(['clearance.student.user'])
                                         ->orderBy('created_at', 'desc')
                                         ->limit(10)
                                         ->get();

        return view('adviser.psg-dashboard', compact(
            'totalStudents',
            'studentsRequiringClearance',
            'recentClearances'
        ));
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
            $file->move(public_path('assets/images/upload/adviser-images/'), $filename);

            // Delete old picture if exists
            if (!empty($user->picture) && file_exists(public_path('assets/images/upload/adviser-images/' . $user->picture))) {
                unlink(public_path('assets/images/upload/adviser-images/' . $user->picture));
            }

            $user->picture = $filename;
        }

        $user->save();

        return redirect()->back()->with('success', 'User information updated successfully.');
    }
}
