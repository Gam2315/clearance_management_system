<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
class DepartmentController extends Controller
{
     public function getDepartment(Request $request)
    {
        $departments = Department::all();
        return view('admin.department.list-of-department', compact('departments'));
    }

    public function addDepartment(Request $request)
    {
         $departments = Department::all();
        return view('admin.department.add_new_department', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
    
        $request->validate([
            'department_name' => 'required|string|max:255',
            'department_code' => 'required|string|max:255',
            'department_head'=> 'required|string|max:255',
        ]);

        Department::create([
            'department_name' => $request->department_name,
            'department_code' => $request->department_code,
            'department_head' => $request->department_head,
        ]);

        return redirect()->route('admin.department.list-of-department')->with('success', 'Department added successfully.');
    }

    public function editDepartment($id)
    {
        $department = Department::findOrFail($id);
        $departments = Department::all();
        return view('admin.department.edit_department_information', compact('department', 'departments'));
    }

    public function updateDepartment(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'department_code' => 'required|string|max:255',
            'department_head'=> 'required|string|max:255',
        ]);
        $department = Department::findOrFail($id);
        $department->update([
            'department_name'=> $request->department_name,
            'department_code'=> $request->department_code,
            'department_head'=> $request->department_head,
        ]);
        return redirect()->route('admin.department.edit_information_department', $department->id)->with('success', 'Department updated successfully.');

    }

    public function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return redirect()->route('admin.department.list-of-department')->with('error', 'Department deleted successfully.');
    }
}
