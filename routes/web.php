<?php

use App\Http\Controllers\Activity;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NfcController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\Dean\DeanController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ClearanceController;
use App\Http\Controllers\Adviser\AdviserController;
use App\Http\Controllers\ClearanceStatusController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Employee\EmployeeController;

use App\Http\Controllers\Students\StudentsController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Dean\DeanClearanceController;
use App\Http\Controllers\Dean\DeanReportController;
use App\Http\Controllers\Employee\EmpClearanceController;
use App\Http\Controllers\Adviser\AdviserClearanceController;
use App\Http\Controllers\GetController;
use App\Http\Controllers\Officer\OfficerController;
use App\Http\Controllers\Auth\PasswordChangeController;
use Illuminate\Support\Facades\DB;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Password Change Routes (must be accessible without middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'showChangeForm'])->name('password.change.form');
    Route::post('/change-password', [PasswordChangeController::class, 'updatePassword'])->name('password.change.update');
});





Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
Route::middleware('auth', 'password.change', 'role:admin')->group(function () {
   
    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::post('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
    Route::get('/admin/account-settings/settings', [AdminController::class, 'account_setting'])->name('admin.account_settings');
    Route::put('/admin/account-settings/setting/update/{id}', [AdminController::class,'updateSetting'])->name('admin.update_setting');
    Route::put('/admin/account-settings/setting/update-password/{id}', [AdminController::class,'updatePassword'])->name('admin.update_password');
   
    Route::get('/users/list-of-users-account', [UserController::class, 'getUsers'])->name('admin.users.list-of-users');
    Route::get('/users/create-new-user-account', [UserController::class, 'addUser'])->name('admin.users.create_new_user');
    Route::post('/users/store-new-user-account', [UserController::class, 'storeUser'])->name('admin.users.store_new_user');
    Route::get('/users/edit-user-account/{id}', [UserController::class, 'editUser'])->name('admin.users.edit_user_account');
    Route::put('/users/update-user-account/{id}', [UserController::class, 'updateUser'])->name('admin.users.update_user_account');
    Route::delete('/users/delete-user-account/{id}', [UserController::class, 'deleteUser'])->name('admin.users.delete_user_account');


       Route::get('/users/create-new-officer-user-account', [UserController::class, 'addOfficerUser'])->name('admin.users.create_new_officer_user');
    Route::post('/users/store-new-officer-user-account', [UserController::class, 'storeOfficerUser'])->name('admin.users.store_new_officer_user');
    Route::get('/users/officer/list-officer-user-account', [UserController::class, 'getofficer'])->name('admin.users.list_officer_user_account');
    Route::get('/users/officer/edit-officer-user-account/{id}', [UserController::class, 'editOfficerUser'])->name('admin.users.edit_officer_user_account');
    Route::put('/users/update-officer-user-account/{id}', [UserController::class, 'updateOfficerUser'])->name('admin.users.update_officer_user_account');
    Route::delete('/users/delete-officer-user-account/{id}', [UserController::class, 'deleteOfficerUser'])->name('admin.users.delete_officer_user_account');
  
  
  
    Route::get('/students/list-of-students', [StudentController::class, 'getStudent'])->name('admin.students.list-of-students');
    Route::get('/student/add-new-student', [StudentController::class, 'addStudent'])->name('admin.student.add_new_student');
    Route::post('/student/store-new-student', [StudentController::class, 'storeStudent'])->name('admin.student.store_new_student');
    Route::get('/student/edit-student-information/{id}', [StudentController::class, 'editStudent'])->name('admin.student.edit_information_student');
    Route::put('/student/update-student-information/{id}', [StudentController::class, 'updateStudent'])->name('admin.student.update_information_student');
    Route::delete('/student/delete-student-information/{id}', [StudentController::class, 'deleteStudent'])->name('admin.student.delete_information_student');
    Route::get('/student/student-profile/{id}', [StudentController::class, 'showStudent'])->name('admin.student.student-profile');

    // Clearance Lock Management
    Route::get('/admin/clearance/locked', [\App\Http\Controllers\Admin\ClearanceLockController::class, 'index'])->name('admin.clearance.locked-clearances');
    Route::get('/admin/clearance/locked/{id}', [\App\Http\Controllers\Admin\ClearanceLockController::class, 'show'])->name('admin.clearance.show-locked');
    Route::post('/admin/clearance/unlock/{id}', [\App\Http\Controllers\Admin\ClearanceLockController::class, 'unlock'])->name('admin.clearance.unlock');
    Route::post('/admin/clearance/bulk-unlock', [\App\Http\Controllers\Admin\ClearanceLockController::class, 'bulkUnlock'])->name('admin.clearance.bulk-unlock');
    Route::post('/admin/clearance/lock-current-year', [\App\Http\Controllers\Admin\ClearanceLockController::class, 'lockCurrentYear'])->name('admin.clearance.lock-current-year');



    Route::get('/department/list-of-department', [DepartmentController::class, 'getDepartment'])->name('admin.department.list-of-department');
    Route::get('/department/add-new-department', [DepartmentController::class, 'addDepartment'])->name('admin.department.add_new_department');
    Route::post('/department/store-new-department', [DepartmentController::class, 'storeDepartment'])->name('admin.department.store_new_department');
    Route::get('/department/edit-department-information/{id}', [DepartmentController::class, 'editDepartment'])->name('admin.department.edit_information_department');
    Route::put('/department/update-department-information/{id}', [DepartmentController::class, 'updateDepartment'])->name('admin.department.update_information_department');
    Route::delete('/department/delete-department-information/{id}', [DepartmentController::class, 'deleteDepartment'])->name('admin.department.delete_information_department');

    Route::get('/fetch-programs/{departmentId}', [CourseController::class, 'fetchPrograms']);
    
    Route::get('/program/list-of-program', [CourseController::class, 'getProgram'])->name('admin.program.list-of-program');
    Route::get('/program/add-new-program', [CourseController::class, 'addProgram'])->name('admin.program.add_new_program');
    Route::post('/program/store-new-program', [CourseController::class, 'storeProgram'])->name('admin.program.store_new_program');
    Route::get('/program/edit-program-information/{id}', [CourseController::class, 'editProgram'])->name('admin.program.edit_information_program');
    Route::put('/program/update-program-information/{id}', [CourseController::class, 'updateProgram'])->name('admin.program.update_information_program');
    Route::delete('/program/delete-program-information/{id}', [CourseController::class, 'deleteProgram'])->name('admin.program.delete_information_program');
   
    Route::get('/admin/clearance/list-of-clearance', [ClearanceController::class, 'getClearance'])->name('admin.clearance.list');
    Route::put('/clearance/update/{id}', [ClearanceController::class, 'update'])->name('clearance.update');
    Route::post('/clearance/submit/{id}', [ClearanceController::class, 'submitClearance'])->name('clearance.submit');
    Route::post('/clearance/cancel/{id}', [ClearanceController::class, 'cancelClearance'])->name('clearance.cancel');
    Route::post('/clearance/lock/{id}', [ClearanceController::class, 'lockClearance'])->name('clearance.lock');
    Route::post('/clearance/unlock/{id}', [ClearanceController::class, 'unlockClearance'])->name('clearance.unlock');

    Route::get('/academic-year/add-new-academic-year', [AcademicYearController::class, 'addAY'])->name('admin.academic_year.add_new_academic_year');
    Route::post('/academic-year/store-new-academic-year', [AcademicYearController::class, 'storeAY'])->name('admin.academic_year.store_new_academic_year');
    Route::get('/academic-year/list-of-academic-year', [AcademicYearController::class, 'getAY'])->name('admin.academic_year.list_of_academic_year');
    Route::get('/academic-year/edit/edit-academic-year-information/{id}', [AcademicYearController::class, 'editAY'])->name('admin.academic_year.edit_information_academic_year');
    Route::put('/academic-year/update-academic-year-information/{id}', [AcademicYearController::class, 'updateAY'])->name('admin.academic_year.update_information_academic_year');
    Route::delete('/academic-year/delete-academic-year-information/{id}', [AcademicYearController::class, 'deleteAY'])->name('admin.academic_year.delete_information_academic_year');
    Route::get('/activity-logs', [Activity::class, 'index'])->name('admin.activity-logs.index');
     // Reports Routes
    Route::get('/admin/reports', [ReportController::class, 'deanOsaReports'])->name('admin.reports.dean-osa');
    Route::post('/admin/reports/students-not-cleared', [ReportController::class, 'getStudentsNotCleared'])->name('admin.reports.students-not-cleared');
    Route::post('/admin/reports/students-cleared', [ReportController::class, 'getStudentsCleared'])->name('admin.reports.students-cleared');
    Route::get('/admin/reports/print-not-cleared', [ReportController::class, 'printNotClearedList'])->name('admin.reports.print-not-cleared');
    Route::get('/admin/reports/print-cleared', [ReportController::class, 'printClearedList'])->name('admin.reports.print-cleared');
    Route::post('/admin/reports/violations', [ReportController::class, 'getViolationReport'])->name('admin.reports.violations');
    Route::post('/admin/reports/graduated-students', [ReportController::class, 'getGraduatedStudents'])->name('admin.reports.graduated-students');
    Route::get('/admin/reports/automatic-clearance', [ReportController::class, 'automaticClearanceInterface'])->name('admin.reports.automatic-clearance');
    Route::get('/admin/reports/previous-years', [ReportController::class, 'previousYearsReports'])->name('admin.reports.previous-years');
    Route::post('/admin/reports/previous-years/data', [ReportController::class, 'getPreviousYearsData'])->name('admin.reports.previous-years.data');

    Route::get('/admin/reports/detect-student', [ReportController::class, 'detectStudentForClearance'])->name('admin.reports.detect-student');

});
Route::get('/get-courses/{department_id}', [CourseController::class, 'getCourses'])->name('get.courses');
Route::get('/get-data/{dsn_id}', [GetController::class, 'getData'])->name('get.data');
Route::middleware('auth', 'password.change', 'role:employee')->group(function () {
   Route::get('/employee/dashboard', [EmployeeController::class, 'EmployeeDashboard'])->name('employee.dashboard');
   Route::post('/employee/logout', [EmployeeController::class, 'EmployeeLogout'])->name('employee.logout');
   Route::get('/employee/account-settings/settings', [EmployeeController::class, 'account_setting'])->name('employee.account_settings');
    Route::put('/employee/account-settings/setting/update/{id}', [EmployeeController::class,'updateSetting'])->name('employee.update_setting');
    Route::put('/employee/account-settings/setting/update-password/{id}', [EmployeeController::class,'updatePassword'])->name('employee.update_password');

    Route::get('/employee/clearance/clearance-tap-id', [NfcController::class, 'showLinkForm'])->name('employee.clearance.clearance-tap-id');
    Route::get('/employee/student/{student}', [StudentsController::class, 'showInfo'])->name('employee.student.student-info');

    Route::post('/employee/nfc-link', [NfcController::class, 'EmployeelinkToStudent'])->name('employee.link.nfc');
    Route::post('/employee/clearance-status/store', [ClearanceStatusController::class, 'Employeestore'])->name('employee.clearance-status.store');
    

  
    
   


    Route::get('/employee/student/view/list-of-students', [StudentsController::class, 'EmployeeStudent'])->name('employee.student.list-of-students');

    Route::get('/employee/clearance/list-of-clearance', [EmpClearanceController::class, 'EmplistClearance'])->name('employee.clearance.list-of-clearance');
    Route::get('/employee/clearance/automatic', [EmpClearanceController::class, 'automaticClearanceInterface'])->name('employee.clearance.automatic');
    Route::get('/employee/clearance/detect-student', [EmpClearanceController::class, 'detectStudentForClearance'])->name('employee.clearance.detect-student');
    Route::post('/employee/clearance/process-automatic', [EmpClearanceController::class, 'processAutomaticClearance'])->name('employee.clearance.process-automatic');



    // Employee Reports Routes (OSA Only)
    Route::get('/employee/reports', [App\Http\Controllers\Employee\EmployeeReportController::class, 'employeeReports'])->name('employee.reports.dean-osa');
    Route::post('/employee/reports/students-not-cleared', [App\Http\Controllers\Employee\EmployeeReportController::class, 'getStudentsNotCleared'])->name('employee.reports.students-not-cleared');
    Route::post('/employee/reports/students-cleared', [App\Http\Controllers\Employee\EmployeeReportController::class, 'getStudentsCleared'])->name('employee.reports.students-cleared');
    Route::get('/employee/reports/print-not-cleared', [App\Http\Controllers\Employee\EmployeeReportController::class, 'printNotClearedList'])->name('employee.reports.print-not-cleared');
    Route::get('/employee/reports/print-cleared', [App\Http\Controllers\Employee\EmployeeReportController::class, 'printClearedList'])->name('employee.reports.print-cleared');

    // Handle both GET and POST for process-student route
    Route::get('/employee/clearance/process-student', [EmpClearanceController::class, 'showProcessStudentForm'])->name('employee.clearance.process-student.form');
    Route::post('/employee/clearance/process-student', [EmpClearanceController::class, 'processStudentByNumber'])->name('employee.clearance.process-student');

    // Clear NFC taps for testing
    Route::post('/employee/clearance/clear-nfc-taps', function() {
        DB::table('nfc_taps')->delete();
        return response()->json(['success' => true, 'message' => 'All NFC taps cleared']);
    })->name('employee.clearance.clear-nfc-taps');

    Route::post('/adviser/clearance/clear-nfc-taps', function() {
        DB::table('nfc_taps')->delete();
        return response()->json(['success' => true, 'message' => 'All NFC taps cleared']);
    })->name('adviser.clearance.clear-nfc-taps');

    Route::post('/dean/clearance/clear-nfc-taps', function() {
        DB::table('nfc_taps')->delete();
        return response()->json(['success' => true, 'message' => 'All NFC taps cleared']);
    })->name('dean.clearance.clear-nfc-taps');

    Route::post('/admin/clearance/clear-nfc-taps', function() {
        DB::table('nfc_taps')->delete();
        return response()->json(['success' => true, 'message' => 'All NFC taps cleared']);
    })->name('admin.clearance.clear-nfc-taps');

    // Test clearance route
    Route::get('/employee/test-clearance', function() {
        $student = \App\Models\Student::with('clearances')->first();
        $clearance = $student ? $student->clearances()->latest()->first() : null;

        if (!$student || !$clearance) {
            return 'No test data available';
        }

        return [
            'student' => $student->student_number,
            'clearance_id' => $clearance->id,
            'accessible' => $clearance->isAccessible(),
            'can_proceed' => $student->canProceedWithClearance(),
            'user_dept' => auth()->user()->department_id,
        ];
    });

    // Fix student clearance history
    Route::get('/employee/fix-student-clearance/{student_number}', function($student_number) {
        $student = \App\Models\Student::where('student_number', $student_number)->first();

        if (!$student) {
            return 'Student not found';
        }

        // Mark first year clearance as completed for testing
        $student->update(['first_year_clearance_completed' => true]);

        return [
            'message' => 'Student clearance history updated',
            'student' => $student->student_number,
            'first_year_completed' => $student->first_year_clearance_completed,
            'can_proceed' => $student->canProceedWithClearance(),
        ];
    });

    // Check student clearance status
    Route::get('/employee/check-student-clearance/{student_number}', function($student_number) {
        $student = \App\Models\Student::with(['clearances'])->where('student_number', $student_number)->first();

        if (!$student) {
            return 'Student not found';
        }

        $clearance = $student->clearances()->latest()->first();

        return [
            'student' => $student->student_number,
            'year' => $student->year,
            'first_year_completed' => $student->first_year_clearance_completed,
            'has_previous_year_completed' => $student->hasPreviousYearClearanceCompleted(),
            'can_proceed' => $student->canProceedWithClearance(),
            'clearance_accessible' => $clearance ? $clearance->isAccessible() : 'No clearance',
            'clearance_locked' => $clearance ? $clearance->is_locked : 'No clearance',
            'clearance_previous_semester' => $clearance ? $clearance->previous_semester_completed : 'No clearance',
        ];
    });

    // Initialize all students for first-time system use
    Route::get('/employee/initialize-all-students', function() {
        $studentsUpdated = 0;
        $clearancesUpdated = 0;

        // Update all students who don't have first_year_clearance_completed set
        $students = \App\Models\Student::whereNull('first_year_clearance_completed')->get();
        foreach ($students as $student) {
            $student->update(['first_year_clearance_completed' => true]);
            $studentsUpdated++;
        }

        // Update all clearances that don't have previous_semester_completed set
        $clearances = \App\Models\Clearance::whereNull('previous_semester_completed')->get();
        foreach ($clearances as $clearance) {
            $clearance->update(['previous_semester_completed' => true]);
            $clearancesUpdated++;
        }

        return [
            'message' => 'All students and clearances initialized for first-time system use',
            'students_updated' => $studentsUpdated,
            'clearances_updated' => $clearancesUpdated,
        ];
    });

    // Debug route to test clearance status creation
    Route::get('/employee/test-clearance-status/{student_number}', function($student_number) {
        $student = \App\Models\Student::where('student_number', $student_number)->first();
        if (!$student) {
            return 'Student not found';
        }

        $clearance = $student->clearances()->latest()->first();
        if (!$clearance) {
            return 'No clearance found';
        }

        // Try to create a test clearance status
        $status = \App\Models\ClearanceStatus::create([
            'clearance_id' => $clearance->id,
            'student_id' => $student->id,
            'department_id' => auth()->user()->department_id ?? 1,
            'approved_by' => auth()->user()->id ?? 1,
            'status' => 'cleared',
            'cleared_at' => now(),
        ]);

        return [
            'message' => 'Test clearance status created',
            'status' => $status->toArray(),
            'student' => $student->student_number,
            'clearance_id' => $clearance->id,
        ];
    });
});

Route::middleware('auth', 'password.change', 'role:student')->group(function () {
    Route::get('/student/dashboard', [StudentsController::class, 'StudentDashboard'])->name('student.dashboard');
    Route::post('/student/logout', [StudentsController::class, 'StudentLogout'])->name('student.logout');
    Route::get('/student/account-settings/settings', [StudentsController::class, 'account_setting'])->name('student.account_settings');
    Route::put('/student/account-settings/setting/update/{id}', [StudentsController::class,'updateSetting'])->name('student.update_setting');
    Route::put('/student/account-settings/setting/update-password/{id}', [StudentsController::class,'updatePassword'])->name('student.update_password');
    Route::get('/student/clearance', [StudentsController::class, 'clearance'])->name('student.clearance');
});

Route::middleware('auth', 'password.change', 'role:dean')->group(function () {
    Route::get('/dean/dashboard', [DeanController::class, 'DeanDashboard'])->name('dean.dashboard');
    Route::post('/dean/logout', [DeanController::class, 'DeanLogout'])->name('dean.logout');
    Route::get('/dean/account-settings/settings', [DeanController::class, 'account_setting'])->name('dean.account_settings');
    Route::put('/dean/account-settings/setting/update/{id}', [DeanController::class,'updateSetting'])->name('dean.update_setting');
    Route::put('/dean/account-settings/setting/update-password/{id}', [DeanController::class,'updatePassword'])->name('dean.update_password');
    //Route::get('/student/view/list-of-students', [StudentController::class, 'EmployeeStudent'])->name('employee.student.list-of-students');

    Route::get('/dean/clearance/clearance-tap-id', [NfcController::class, 'DeanshowLinkForm'])->name('dean.clearance.clearance-tap-id');
    Route::get('/dean/student/{student}', [StudentsController::class, 'DeanShowInfo'])->name('dean.student.student-info');
    //Route::post('/nfc/save-tap', [NfcController::class, 'storeTap']);
    //Route::post('/nfc/store-uid', [NfcController::class, 'storeUid'])->name('nfc.store-uid');

    Route::post('/dean/nfc-link', [NfcController::class, 'DeanlinkToStudent'])->name('dean.link.nfc');
    Route::post('/dean/clearance-status/store', [ClearanceStatusController::class, 'Deanstore'])->name('dean.clearance-status.store');
    
    Route::get('/student/view-student/list-of-students', [StudentsController::class, 'DeanStudent'])->name('dean.student.list-of-students');

    Route::get('/clearance/list-of-clearance', [DeanClearanceController::class, 'DeanlistClearance'])->name('dean.clearance.list-of-clearance');
    Route::get('/clearance/automatic', [DeanClearanceController::class, 'automaticClearanceInterface'])->name('dean.clearance.automatic');
    Route::get('/clearance/detect-student', [DeanClearanceController::class, 'detectStudentForClearance'])->name('dean.clearance.detect-student');
    Route::post('/clearance/process-automatic', [DeanClearanceController::class, 'processAutomaticClearance'])->name('dean.clearance.process-automatic');

    Route::get('/dean/reports', [DeanReportController::class, 'deanReports'])->name('dean.reports.dean-osa');
    Route::post('/dean/reports/students-not-cleared', [DeanReportController::class, 'getStudentsNotCleared'])->name('dean.reports.students-not-cleared');
    Route::post('/dean/reports/students-cleared', [DeanReportController::class, 'getStudentsCleared'])->name('dean.reports.students-cleared');
    Route::get('/dean/reports/print-not-cleared', [DeanReportController::class, 'printNotClearedList'])->name('dean.reports.print-not-cleared');
    Route::get('/dean/reports/print-cleared', [DeanReportController::class, 'printClearedList'])->name('dean.reports.print-cleared');
    Route::post('/dean/reports/violations', [DeanReportController::class, 'getViolationReport'])->name('dean.reports.violations');
    Route::post('/dean/reports/graduated-students', [DeanReportController::class, 'getGraduatedStudents'])->name('dean.reports.graduated-students');

    // Dean User Management Routes
    Route::get('/dean/users/manage', [DeanController::class, 'manageUsers'])->name('dean.manage-users');
    Route::get('/dean/users/create', [DeanController::class, 'createUser'])->name('dean.create-user');
    Route::post('/dean/users/store', [DeanController::class, 'storeUser'])->name('dean.store-user');
    Route::get('/dean/users/edit/{id}', [DeanController::class, 'editUser'])->name('dean.edit-user');
    Route::put('/dean/users/update/{id}', [DeanController::class, 'updateUser'])->name('dean.update-user');
    Route::delete('/dean/users/delete/{id}', [DeanController::class, 'deleteUser'])->name('dean.delete-user');

});
Route::middleware('auth', 'password.change', 'role:adviser')->group(function () {
    Route::get('/adviser/dashboard', [AdviserController::class, 'AdviserDashboard'])->name('adviser.dashboard');
    Route::post('/adviser/logout', [AdviserController::class, 'AdviserLogout'])->name('adviser.logout');
    Route::get('/adviser/account-settings/settings', [AdviserController::class, 'account_setting'])->name('adviser.account_settings');
    Route::put('/adviser/account-settings/setting/update/{id}', [AdviserController::class,'updateSetting'])->name('adviser.update_setting');
    Route::put('/adviser/account-settings/setting/update-password/{id}', [AdviserController::class,'updatePassword'])->name('adviser.update_password');

    Route::get('/student/view/list-of-students', [StudentsController::class, 'AdviserStudent'])->name('adviser.student.list-of-students');
    Route::get('/adviser/clearance/clearance-tap-id', [NfcController::class, 'AdvisershowLinkForm'])->name('adviser.clearance.clearance-tap-id');
    Route::get('/adviser/student/{student}', [StudentsController::class, 'AdviserShowInfo'])->name('adviser.student.student-info');
   

    Route::post('/adviser/nfc-link', [NfcController::class, 'AdviserlinkToStudent'])->name('adviser.link.nfc');
    Route::post('/adviser/clearance-status/store', [ClearanceStatusController::class, 'Adviserstore'])->name('adviser.clearance-status.store');
    Route::get('/clearance/detect-student', [AdviserClearanceController::class, 'detectStudentForClearance'])->name('adviser.clearance.detect-student');

    // Handle both GET and POST for adviser process-student route
    Route::get('/adviser/clearance/process-student', [AdviserClearanceController::class, 'showProcessStudentForm'])->name('adviser.clearance.process-student.form');
    Route::post('/adviser/clearance/process-student', [AdviserClearanceController::class, 'processStudentByNumber'])->name('adviser.clearance.process-student');

    // PSG Adviser specific routes
    Route::get('/adviser/psg-dashboard', [AdviserController::class, 'psgAdviserDashboard'])->name('adviser.psg-dashboard');

});
Route::middleware('auth', 'password.change', 'role:officer')->group(function () {
    Route::get('/officer/dashboard', [OfficerController::class, 'OfficerDashboard'])->name('officer.dashboard');
    Route::post('/officer/logout', [OfficerController::class, 'OfficerLogout'])->name('officer.logout');
    Route::get('/officer/account-settings/settings', [OfficerController::class, 'account_setting'])->name('officer.account_settings');
    Route::put('/officer/account-settings/setting/update/{id}', [OfficerController::class,'updateSetting'])->name('officer.update_setting');
    Route::put('/officer/account-settings/setting/update-password/{id}', [OfficerController::class,'updatePassword'])->name('officer.update_password');

    Route::get('/officer/student/list-of-students', [StudentsController::class, 'OfficerStudent'])->name('officer.student.list-of-students');
    Route::get('/officer/clearance/clearance-tap-id', [NfcController::class, 'OfficershowLinkForm'])->name('officer.clearance.clearance-tap-id');
    Route::post('/officer/clearance/link-to-student', [NfcController::class, 'OfficerlinkToStudent'])->name('officer.clearance.link-to-student');
    Route::get('/officer/student/{student}', [StudentsController::class, 'OfficerShowInfo'])->name('officer.student.student-info');
   

    Route::post('/officer/nfc-link', [NfcController::class, 'OfficerlinkToStudent'])->name('officer.link.nfc');
    Route::post('/officer/clearance-status/store', [ClearanceStatusController::class, 'Officerstore'])->name('officer.clearance-status.store');
    Route::get('/clearance/detect-student', [AdviserClearanceController::class, 'detectStudentForClearance'])->name('adviser.clearance.detect-student');

    // Handle both GET and POST for adviser process-student route
    Route::get('/adviser/clearance/process-student', [AdviserClearanceController::class, 'showProcessStudentForm'])->name('adviser.clearance.process-student.form');
    Route::post('/adviser/clearance/process-student', [AdviserClearanceController::class, 'processStudentByNumber'])->name('adviser.clearance.process-student');

    // PSG Adviser specific routes
    Route::get('/adviser/psg-dashboard', [AdviserController::class, 'psgAdviserDashboard'])->name('adviser.psg-dashboard');

});

Route::get('/auth/student/login', [AuthController::class, 'StudentForm'])->name('auth.student.login-student');
Route::get('/auth/adviser/login', [AuthController::class, 'AdviserForm'])->name('auth.adviser.login-adviser');
Route::get('/auth/admin/login', [AuthController::class, 'AdminForm'])->name('auth.admin.login-admin');
Route::get('/auth/dean/login', [AuthController::class, 'DeanForm'])->name('auth.dean.login-dean');
Route::get('/auth/officer/login', [AuthController::class, 'OfficerForm'])->name('auth.officer.login-officer');
Route::get('/auth/employee/login', [AuthController::class, 'EmployeeForm'])->name('auth.employee.login-employee');




Route::post('/auth/adviser/login/store', [AuthController::class, 'loginAll'])->name('auth.adviser.login');
Route::post('/auth/student/login/store', [AuthController::class, 'loginAll'])->name('auth.student.login');
Route::post('/auth/admin/login/store', [AuthController::class, 'loginAll'])->name('auth.admin.login');
Route::post('/auth/dean/login/store', [AuthController::class, 'loginAll'])->name('auth.dean.login');
Route::post('/auth/officer/login/store', [AuthController::class, 'loginAll'])->name('auth.officer.login');
Route::post('/auth/employee/login/store', [AuthController::class, 'loginAll'])->name('auth.employee.login');

Route::get('/auth/student/registration', [AuthController::class, 'register'])->name('auth.student.register');
Route::get('/auth/adviser/registration', [AuthController::class, 'registered'])->name('auth.adviser.register');
Route::post('/auth/student/store', [AuthController::class, 'storeStudent'])->name('auth.student.store');


Route::post('/auth/login/store', [AuthController::class, 'loginAll'])->name('auth.all.login');
// Secure file serving route
Route::get('/secure-file/{directory}/{filename}', [SecureFileController::class, 'serve'])
    ->name('secure-file')
    ->middleware('auth');






Route::post('/auth/adviser/store', [AuthController::class, 'storeAdviser'])->name('auth.adviser.store');

