<?php

namespace App\Http\Controllers\Students;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Designation;

class StudentsController extends Controller
{


    public function showInfo($id)
    {
        $student = Student::with('user', 'department')->findOrFail($id);

        $clearance = Clearance::with('student', 'academicYear')
            ->where('student_id', $id)
            ->first(); // Use firstOrFail() if you're sure every student has clearance



        return view('employee.student.student-info', compact('student', 'clearance'));
    }

    public function DeanShowInfo($id)
    {
        $student = Student::with('user', 'department')->findOrFail($id);

        // Get clearance for active academic year
        $activeAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

        if ($activeAcademicYear) {
            $clearance = Clearance::with(['student', 'academicYear', 'statuses.department', 'statuses.approver'])
                ->where('student_id', $id)
                ->where('academic_id', $activeAcademicYear->id)
                ->first();
        } else {
            $clearance = Clearance::with(['student', 'academicYear', 'statuses.department', 'statuses.approver'])
                ->where('student_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return view('dean.student.student-info', compact('student', 'clearance'));
    }

    public function AdviserShowInfo($id)
    {
        $student = Student::with('user', 'department')->findOrFail($id);

        $clearance = Clearance::with(['student', 'academicYear', 'statuses.department', 'statuses.approver'])
            ->where('student_id', $id)
            ->first(); // Use firstOrFail() if you're sure every student has clearance



        return view('adviser.student.student-info', compact('student', 'clearance'));
    }

    public function addStudent(Request $request)
    {
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();

        $academic_year = AcademicYear::all();
        return view('admin.student.add_new_student', compact('departments', 'academic_year'));
    }

    public function storeStudent(Request $request)
    {
        $validatedData = $request->validate([
            'firstname' => 'required',
            'middlename' => 'required',
            'lastname' => 'required',
            'suffix_name' => 'nullable|string',
            'student_id' => 'required|unique:students,student_number',
            'department_id' => 'required',
            'program' => 'required',
            'year' => 'required|in:1st,2nd,3rd,4th,5th',
            'academic_id' => 'required',
        ]);

        try {
            // Check if a user with the same name already exists
            $existingUser = User::where('firstname', $validatedData['firstname'])
                ->where('lastname', $validatedData['lastname'])
                ->where('middlename', $validatedData['middlename'])
                ->first();

            // Check if this is an existing student with locked clearance
            if ($existingUser) {
                $existingStudent = Student::where('users_id', $existingUser->id)->first();
                if ($existingStudent && $existingStudent->has_locked_clearance) {
                    return redirect()->back()->with(
                        'error',
                        'Cannot create new clearance. This student has locked clearance from a previous academic year. Please contact the registrar to resolve this issue first.'
                    );
                }
            }

            if ($existingUser) {
                // Check if this user already has a student record
                $existingStudent = Student::where('users_id', $existingUser->id)->first();

                if ($existingStudent) {
                    return redirect()->back()->with('error', 'Student with this name already exists in the system.');
                }

                // Use existing user but create new student record
                $user = $existingUser;
            } else {
                // Create new user
                $user = User::create([
                    'name' => "{$validatedData['firstname']} {$validatedData['lastname']}",
                    'firstname' => $validatedData['firstname'],
                    'lastname' => $validatedData['lastname'],
                    'middlename' => $validatedData['middlename'],
                    'suffix_name' => $validatedData['suffix_name'],
                    'password' => Hash::make('spup@2024'), // Default password
                    'password_changed_at' => null, // Force password change on first login
                    'status' => 'active',
                    'role' => 'student',
                ]);
            }

            // Create student record linked to user
            $student = Student::create([
                'users_id' => $user->id,
                'student_number' => $validatedData['student_id'],
                'course_id' => $validatedData['program'],
                'year' => $validatedData['year'],
                'department_id' => $validatedData['department_id'],
                'academic_id' => $validatedData['academic_id'],
                'is_uniwide' => $request->has('is_uniwide') ? true : false,
            ]);

            // Create clearance record
            $clearance = Clearance::create([
                'student_id' => $student->id,
                'academic_id' => $validatedData['academic_id'],
                'department_id' => $validatedData['department_id'],
                'overall_status' => 'pending',
                'previous_semester_completed' => true,
            ]);

            // Get required departments based on student's course and department
            $requiredDepartments = $student->getRequiredDepartments();

            // Create clearance status for each required department - ALL SET TO PENDING
            foreach ($requiredDepartments as $departmentId) {
                $department = \App\Models\Department::find($departmentId);
                if (!$department) continue;

                \App\Models\ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'department_id' => $departmentId,
                    'status' => 'pending',
                    'cleared_at' => null,
                    'remarks' => 'Awaiting clearance verification.',
                    'approved_by' => null,
                    'student_id' => $student->id,
                ]);
            }

            return redirect()->back()->with('success', 'Student added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add student: ' . $e->getMessage());
        }
    }

    public function getStudent(Request $request)
    {
        $students = Student::with('user', 'department', 'courses')
            ->where('is_archived', false)
            ->get();
        $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        $programs = Course::all();
        return view('admin.student.list-of-students', compact('students', 'departments', 'programs'));
    }

    public function editStudent($id)
    {
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get();
        $courses = Course::all(); // Fetch all courses
        $academic_year = AcademicYear::all();

        $student = Student::with('department', 'courses', 'user', 'AY')->findOrFail($id);

        return view('admin.student.edit_information_student', compact('student', 'departments', 'courses', 'academic_year'));
    }

    public function updateStudent(Request $request, $id)
    {
        // Validate the input fields
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'suffix_name' => 'nullable',
            'student_id' => 'unique:students,student_number,' . $id, // Ensure student_number is unique except for the current student
            'department_id' => 'required|exists:departments,id',
            'program' => 'required',
            'year' => 'required',
            'status' => 'required', // Default status
            'academic_id' => 'required',
        ]);

        // Find the student with the related user
        $student = Student::with('user')->findOrFail($id);
        $user = $student->user; // Get the associated user

        // Update user details in the users table
        $user->update([
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'suffix_name' => $request->suffix_name,
        ]);

        // Update student details in the students table
        $student->update([
            'student_number' => $request->student_id,
            'department_id' => $request->department_id,
            'program' => $request->program, // Ensure program_id is the correct column name
            'year' => $request->year,
            'status' => $request->status, // Default status
            'academic_id' => $request->academic_id,
            'is_uniwide' => $request->has('is_uniwide') ? true : false,
        ]);

        // Redirect back with a success message
        return redirect()->route('student.edit_information_student', $student->id)->with('success', 'Student information updated successfully!');
    }

    public function deleteStudent($id)
    {
        $student = Student::with('user')->findOrFail($id); // Find student with related user

        // Optional: Delete the associated user if needed
        if ($student->user) {
            $student->user->delete(); // Deletes the user record
        }

        $student->delete(); // Delete student record

        return redirect()->route('admin.students.list-of-students')->with('error', 'Student deleted successfully!');
    }


    public function showStudent($id)
    {
        $departments = Department::all();
        $courses = Course::all(); // Fetch all courses

        $student = Student::with('department', 'courses', 'user')->findOrFail($id);
        $clearance = Clearance::with(['statuses.department'])
            ->where('student_id', $id)
            ->first();

        return view('admin.student.student-profile', compact('student', 'departments', 'courses', 'clearance'));
    }


    public function EmployeeStudent(Request $request)
    {
        // Show only UNIWIDE students (is_uniwide = 1) for all employees
        $students = Student::with(['user', 'department', 'courses', 'AY'])
            ->where('is_archived', false)
            ->get();

        $programs = Course::all();
        $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        return view('employee.student.list-of-students', compact('students', 'departments', 'programs'));
    }



    public function DeanStudent(Request $request)
    {

        $user = Auth::user();
        $employeeDepartmentId = $user->department_id;

        // Define which departments are restricted to only see their own students
        $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments

        // If employee is from a restricted department, only show their students
        if (in_array($employeeDepartmentId, $restrictedDepartments)) {
            $students = Student::where('department_id', $employeeDepartmentId)
                ->where('is_archived', false)
                ->with('AY', 'courses', 'department', 'user')
                ->get();
        } else {
            // For other departments (like Library, SBAHM), show all students
            $students = Student::with('user', 'AY', 'department', 'courses')
                ->where('is_archived', false)
                ->get();
            $programs = Course::all();
            $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        }
        // Fetch those departments for displaying or other uses

        $programs = Course::all();
        $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        return view('dean.student.list-of-students', compact('students', 'departments', 'programs'));
    }

    public function AdviserStudent(Request $request)
    {

        $user = Auth::user();
        $employeeDepartmentId = $user->department_id;

        // Define which departments are restricted to only see their own students
        $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments

        // If employee is from a restricted department, only show their students
        if (in_array($employeeDepartmentId, $restrictedDepartments)) {
            $students = Student::where('department_id', $employeeDepartmentId)
                ->where('is_archived', false)
                ->with('AY', 'courses', 'department', 'user')
                ->get();
        } else {
            // For other departments (like Library, SBAHM), show all students
            $students = Student::with('user', 'AY', 'department', 'courses')
                ->where('is_archived', false)
                ->get();
            $programs = Course::all();
            $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        }
        // Fetch those departments for displaying or other uses

        $programs = Course::all();
        $departments = Department::whereIn('id', [1, 2, 3, 4])->with('programs')->get();
        return view('adviser.student.list-of-students', compact('students', 'departments', 'programs'));
    }
    public function OfficerStudent(Request $request)
    {
        $user = Auth::user();

        // Check officer's designation to determine which students to show
        // designation_id = 5 (UNIWIDE) → Show UNIWIDE students (is_uniwide = 1)
        // designation_id = 1,2,3,4 (SW_SITE, SW_SASTE, SW_SNAHS, SW_SBAHM) → Show non-UNIWIDE students (is_uniwide = 0)

        if ($user->designation_id == 5) {
            // UNIWIDE Officer - Show only UNIWIDE students
            $students = Student::with(['user', 'department', 'courses', 'AY'])
                ->where('is_uniwide', 1)
                ->where('is_archived', false)
                ->get();
        } else {
            // Governor (Department-specific Officer) - Show only non-UNIWIDE students from their department
            $students = Student::with(['user', 'department', 'courses', 'AY'])
                ->where('is_uniwide', 0)
                ->where('department_id', $user->department_id)
                ->where('is_archived', false)
                ->get();
        }

        return view('officer.student.list-of-students', compact('students'));
    }
    //if  the user is uniwide either designation or position get the list of students on is_uniwide = 1

    
    





    public function OfficerShowInfo($id)
    {
        $student = Student::with('department', 'courses', 'user')->findOrFail($id);
        $clearance = Clearance::with(['statuses.department'])
            ->where('student_id', $id)
            ->first();

        return view('officer.student.student-info', compact('student', 'clearance'));
    }

    public function StudentDashboard()
    {
        $user = Auth::user();
        $student = $user->student;

        // Get student's clearance information
        $clearance = null;
        if ($student) {
            $clearance = \App\Models\Clearance::with(['statuses.department'])
                ->where('student_id', $student->id)
                ->first();
        }

        return view('student.dashboard', compact('user', 'student', 'clearance'));
    }
    public function account_setting()
    {
        // Fetch the logged-in user's information
        $id = Auth::user()->id;
        $user = User::find($id);

        return view('student.account-settings.settings', compact('user'));
    }

    public function StudentLogout(Request $request)
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
            $file->move(public_path('assets/images/upload/users/'), $filename);

            // Delete old picture if exists
            if (!empty($user->picture) && file_exists(public_path('assets/images/upload/users/' . $user->picture))) {
                unlink(public_path('assets/images/upload/users/' . $user->picture));
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

    public function clearance()
    {
        $student = Auth::user()->student;
        // $student = Student::with('user', 'department')->findOrFail($id);

        // Get the active academic year
        $activeAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

        if (!$activeAcademicYear) {
            // If no active academic year, get the most recent clearance
            $clearance = Clearance::with(['statuses.department', 'academicYear'])
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->first();
        } else {
            // Get clearance for the active academic year
            $clearance = Clearance::with(['statuses.department', 'academicYear'])
                ->where('student_id', $student->id)
                ->where('academic_id', $activeAcademicYear->id)
                ->first();

            // If no clearance for active academic year, get the most recent one
            if (!$clearance) {
                $clearance = Clearance::with(['statuses.department', 'academicYear'])
                    ->where('student_id', $student->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        return view('student.clearance.view-clearance', compact('student', 'clearance'));
    }
}
