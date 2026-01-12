<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{

     public function getCourses(Request $request, $department_id)
    {
        // Fetch courses based on department_id
        $courses = Course::where('department_id', $department_id)->get(['id', 'course_name']); // Adjust column names

        // Return as JSON response
        return response()->json($courses);
    }
     public function getProgram(Request $request)
    {
        $program = Course::with('department')->get();
        $departments = Department::all();

        return view('admin.program.list-of-program', compact('program', 'departments'));
    }

    public function fetchPrograms($departmentId)
    {
        $programs = Course::where('department_id', $departmentId)->with('department')->get();
        return response()->json(['programs' => $programs]);
    }

    public function addProgram(Request $request)
    {
        $program = Course::with('department')->get();
        $departments = Department::all();


        return view('admin.program.add-new-program', compact('program', 'departments'));
    }

    public function storeProgram(Request $request)
    {
        $request->validate([
            'program_name' => 'required',
            'department_id' => 'required',
            'program_code' => 'required',
        ]);


        Course::create([
            'course_name' => $request->program_name,
            'department_id' => $request->department_id,
            'course_code' => $request->program_code,
        ]);
        return redirect()->route('program.list-of-program')->with('success', 'Program added successfully.');
       
    }
    public function editProgram(Request $request, $id)
    {
        $program = Course::with('department')->findOrFail($id);
        $departments = Department::all();
        return view('admin.program.edit-program-information', compact('program', 'departments'));
    }
    public function updateProgram(Request $request, $id)
    {
        $request->validate([
            'program_name' => 'required',
            'department_id' => 'required',
            'program_code' => 'required',
        ]);
        $program = Course::findOrFail($id);
        $program->update([
            'course_name' => $request->program_name,
            'department_id' => $request->department_id,
            'course_code' => $request->program_code,
        ]);
        return redirect()->route('program.edit_information_program', $program->id)->with('success', 'Program updated successfully.');
    }

    public function deleteProgram(Request $request, $id)
    {
        $program = Course::findOrFail($id);
        $program->delete();
        return redirect()->route('program.list-of-program')->with('error', 'Program deleted successfully.');
    }
}
