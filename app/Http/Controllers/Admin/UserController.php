<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Position;
use App\Models\Department;
use App\Models\Designation;
use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class UserController extends Controller
{
    private SecureFileUploadService $fileUploadService;

    public function __construct(SecureFileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }
    
    public function getUsers()
    {
       $users = User::whereIn('role', ['employee', 'dean', 'adviser'])->with('department')->get();

        $departments = Department::all();
        return view('admin.users.list-of-users', compact('users', 'departments'));

    }

    public function getOfficer()
    {
       $users = User::whereIn('role', ['officer'])
       ->whereIn('designation_id', [1, 2, 3, 4, 5])
       ->whereIn('position_id', [1, 2, 3, 4, 5])
       ->with('department')->get();

        $departments = Department::all();
        return view('admin.users.list-of-officer-user-account', compact('users', 'departments'));

    }

     public function AddUser()
    {
        $users = User::where('role', ['employee', 'dean','adviser'])->with('department')->get();
        $departments = Department::all();
        return view('admin.users.create-new-user-account', compact('users', 'departments'));

    }

      public function AddOfficerUser()
    {
        $users = User::where('role', 'officer')->with('department')->get();
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $designation = Designation::all();
        $position = Position::all();
        return view('admin.users.create-new-officer-user-account', compact('users', 'departments', 'designation','position'));

    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'employee_id' => 'required|unique:users,employee_id',
            'role' => 'required|in:employee,admin,dean,adviser,officer',
            'department_id' => 'required',
        ]);

        $user = new User();
        $user->name = strtolower($request->firstname . '.' . $request->lastname);
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->suffix_name = $request->suffix_name;
        $user->employee_id = $request->employee_id;

        // Use default password
        $temporaryPassword = 'spup@2025';
        $user->password = Hash::make($temporaryPassword);
        $user->password_changed_at = null; // Force password change on first login

        $user->role = $request->role;
        $user->status = 'inactive'; // Require activation
        $user->department_id = $request->department_id;

        // Handle image upload securely
        if ($request->hasFile('picture')) {
            try {
                $filename = $this->fileUploadService->uploadProfilePicture(
                    $request->file('picture'),
                    'users'
                );
                $user->picture = $filename;
            } catch (\Exception $e) {
                return back()->withErrors(['picture' => $e->getMessage()]);
            }
        }

        $user->save();

        // User account created successfully - show the default password
        $successMessage = "User Account created successfully! Default Password: <strong>spup@2025</strong> (User must change this on first login)";

        return redirect()->back()->with('success', $successMessage);
    }


     public function storeOfficerUser(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'student_id' => 'required|unique:users,employee_id',
            'role' => 'required|in:officer',
            'department_id' => 'required',
            'dsn_id'=> 'required',
            'position_id'=> 'required',
            'program' => 'required',
        ]);

        $user = new User();
        $user->name = strtolower($request->firstname . '.' . $request->lastname);
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->suffix_name = $request->suffix_name;
        $user->employee_id = $request->student_id;

        // Use default password
        $temporaryPassword = 'spup@2025';
        $user->password = Hash::make($temporaryPassword);
        $user->password_changed_at = null; // Force password change on first login

        $user->role = $request->role;
        $user->status = 'inactive'; // Require activation
        $user->department_id = $request->department_id;
        $user->designation_id = $request->dsn_id;
        $user->position_id = $request->position_id;
         $user->course_id = $request->program;


       
           

        // Handle image upload securely
        if ($request->hasFile('picture')) {
            try {
                $filename = $this->fileUploadService->uploadProfilePicture(
                    $request->file('picture'),
                    'users'
                );
                $user->picture = $filename;
            } catch (\Exception $e) {
                return back()->withErrors(['picture' => $e->getMessage()]);
            }
        }

        $user->save();

        // User account created successfully - show the temporary password
        $successMessage = "User Account created successfully! Temporary Password: <strong>{$temporaryPassword}</strong> (User must change this on first login)";

        return redirect()->back()->with('success', $successMessage);
    }
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::all();
        return view('admin.users.edit-user-account', compact('user', 'departments'));
    }

    public function editOfficerUser($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $designation = Designation::all();
        $position = Position::all();
        $course = Course::all();
        return view('admin.users.edit-officer-user-account', compact('user', 'departments', 'designation', 'position', 'course'));
    }


    public function updateOfficerUser(Request $request, $id)
    {


        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'employee_id' => 'required|unique:users,employee_id,' . $id,
            'role' => 'required|in:employee,admin,dean,adviser,officer',
            'department_id' => 'required',
            'dsn_id'=> 'required',
            'position_id'=> 'required',
            'program' => 'required',
        ]);

        $user = User::findOrFail($id);
        $user->name = strtolower($request->firstname . '.' . $request->lastname);
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->suffix_name = $request->suffix_name;
        $user->employee_id = $request->employee_id;
        $user->role = $request->role;
        $user->status = 'active'; // Default status
        $user->department_id = $request->department_id;
        $user->designation_id = $request->dsn_id;
        $user->position_id = $request->position_id;
        $user->course_id = $request->program;

        // Handle image upload
       
        $user->save();

        return redirect()->back()->with('success', 'User Account updated successfully.');

    }
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'employee_id' => 'required|unique:users,employee_id,' . $id,
            'role' => 'required|in:employee,admin,dean,adviser,officer',
            'department_id' => 'required',
        ]);

        $user = User::findOrFail($id);
        $user->name = strtolower($request->firstname . '.' . $request->lastname);
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->suffix_name = $request->suffix_name;
        $user->employee_id = $request->employee_id;
        $user->role = $request->role;
        $user->status = 'active'; // Default status
        $user->department_id = $request->department_id;

        // Handle image upload
       
        $user->save();

        return redirect()->back()->with('success', 'User Account updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('error', 'User Account deleted successfully.');
    }
}
