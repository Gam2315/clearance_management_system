<?php

namespace App\Http\Controllers\Dean;

use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\Clearance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DeanController extends Controller
{
    public function DeanDashboard(){
        $user = Auth::user();
        $departmentId = $user->department_id;

        // Get statistics for dean's department
        $totalStudents = Student::where('department_id', $departmentId)->count();
        $totalEmployees = User::where('department_id', $departmentId)
                             ->where('role', 'employee')
                             ->count();
        $totalAdvisers = User::where('department_id', $departmentId)
                            ->where('role', 'adviser')
                            ->count();

        // Get clearance statistics
        $activeClearances = Clearance::whereHas('student', function($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })->where('is_archived', 0)->count();

        $completedClearances = Clearance::whereHas('student', function($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })->whereHas('statuses', function($query) use ($departmentId) {
            $query->where('department_id', $departmentId)->where('status', 'cleared');
        })->count();

        return view('dean.dashboard', compact(
            'totalStudents',
            'totalEmployees',
            'totalAdvisers',
            'activeClearances',
            'completedClearances'
        ));
    }
     public function account_setting()
    {
        // Fetch the logged-in user's information
        $id = Auth::user()->id;
        $user = User::find($id);
        $departments = Department::all();

        return view('dean.account-settings.settings', compact('user', 'departments'));
    }

    public function DeanLogout(Request $request)
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

    // User Management Functions for Dean
    public function manageUsers()
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        // Get users from dean's department (employees and advisers)
        $users = User::where('department_id', $departmentId)
                    ->whereIn('role', ['employee', 'adviser'])
                    ->with('department')
                    ->get();

        $departments = Department::all();

        return view('dean.users.manage-users', compact('users', 'departments'));
    }

    public function createUser()
    {
        $user = Auth::user();
        $departments = Department::where('id', $user->department_id)->get();

        return view('dean.users.create-user', compact('departments'));
    }

    public function storeUser(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'employee_id' => 'required|string|unique:users,employee_id',
            'role' => 'required|in:employee,adviser',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $newUser = new User();
        $newUser->name = strtolower($request->firstname . '.' . $request->lastname);
        $newUser->firstname = $request->firstname;
        $newUser->middlename = $request->middlename;
        $newUser->lastname = $request->lastname;
        $newUser->suffix_name = $request->suffix_name;
        $newUser->employee_id = $request->employee_id;
        $newUser->password = Hash::make('spup@2024');
        $newUser->password_changed_at = null; // Force password change on first login
        $newUser->role = $request->role;
        $newUser->status = 'active';
        $newUser->department_id = $user->department_id; // Assign to dean's department

        // Handle image upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/images/upload/users/'), $filename);
            $newUser->picture = $filename;
        }

        $newUser->save();

        return redirect()->route('dean.manage-users')->with('success', 'User created successfully.');
    }

    public function editUser($id)
    {
        $user = Auth::user();
        $editUser = User::where('id', $id)
                       ->where('department_id', $user->department_id)
                       ->whereIn('role', ['employee', 'adviser'])
                       ->firstOrFail();

        $departments = Department::where('id', $user->department_id)->get();

        return view('dean.users.edit-user', compact('editUser', 'departments'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = Auth::user();
        $editUser = User::where('id', $id)
                       ->where('department_id', $user->department_id)
                       ->whereIn('role', ['employee', 'adviser'])
                       ->firstOrFail();

        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'employee_id' => 'required|string|unique:users,employee_id,' . $id,
            'role' => 'required|in:employee,adviser',
            'status' => 'required|in:active,inactive',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $editUser->name = strtolower($request->firstname . '.' . $request->lastname);
        $editUser->firstname = $request->firstname;
        $editUser->middlename = $request->middlename;
        $editUser->lastname = $request->lastname;
        $editUser->suffix_name = $request->suffix_name;
        $editUser->employee_id = $request->employee_id;
        $editUser->role = $request->role;
        $editUser->status = $request->status;

        // Handle image upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/images/upload/users/'), $filename);

            // Delete old picture if exists
            if (!empty($editUser->picture) && file_exists(public_path('assets/images/upload/users/' . $editUser->picture))) {
                unlink(public_path('assets/images/upload/users/' . $editUser->picture));
            }

            $editUser->picture = $filename;
        }

        $editUser->save();

        return redirect()->route('dean.manage-users')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = Auth::user();
        $deleteUser = User::where('id', $id)
                         ->where('department_id', $user->department_id)
                         ->whereIn('role', ['employee', 'adviser'])
                         ->firstOrFail();

        // Delete user picture if exists
        if (!empty($deleteUser->picture) && file_exists(public_path('assets/images/upload/users/' . $deleteUser->picture))) {
            unlink(public_path('assets/images/upload/users/' . $deleteUser->picture));
        }

        $deleteUser->delete();

        return redirect()->route('dean.manage-users')->with('success', 'User deleted successfully.');
    }
}
