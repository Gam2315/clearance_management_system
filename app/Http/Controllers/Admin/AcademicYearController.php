<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Clearance;
use Illuminate\Support\Facades\Artisan;
class AcademicYearController extends Controller
{
    public function addAY()
    {
        $departments = Department::all();
        return view('admin.academic_year.add-new-academic-year', compact('departments'));
    }
    public function storeAY(Request $request)
    {
        $validatedData = $request->validate([
            'academic_year' => 'required',
            'semester' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        // Create a new academic year record
        $academicYear = AcademicYear::create($validatedData);

        // If the new academic year is created as active, automatically assign students and create clearances
        if ($validatedData['status'] === 'active') {
            try {
                // Set all other academic years to inactive
                AcademicYear::where('id', '!=', $academicYear->id)->update(['status' => 'inactive']);

                // Update all active students to the new academic year
                $studentsUpdated = Student::where('is_archived', false)
                    ->update(['academic_id' => $academicYear->id]);

                // Create clearances for all students in the new academic year
                Artisan::call('clearance:create-for-new-year', [
                    '--academic-year' => $academicYear->id,
                    '--force' => true
                ]);

                $message = "Academic Year added successfully and activated";

                return redirect()->back()->with('success', $message);
            } catch (\Exception $e) {
                $message = "Academic Year added successfully, but there was an issue with automatic assignment: " . $e->getMessage();
                return redirect()->back()->with('warning', $message);
            }
        }

        return redirect()->back()->with('success', 'Academic Year added successfully.');
    }

    public function getAY()
    {
        $academic_years = AcademicYear::all();
        $departments = Department::all();
        return view('admin.academic_year.list-of-academic-year', compact('academic_years', 'departments'));
    }

    public function editAY($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
         $departments = Department::all();
        return view('admin.academic_year.edit-academic-year', compact('academic_year', 'departments'));
    }

    public function updateAY(Request $request, $id)
    {
        $validatedData = $request->validate([
            'academic_year' => 'required',
            'semester' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        // Get the current academic year record to check for status changes
        $academicYear = AcademicYear::findOrFail($id);
        $oldStatus = $academicYear->status;
        $newStatus = $validatedData['status'];

        // Update the academic year record
        $academicYear->update($validatedData);

        // Handle academic year status changes
        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'inactive') {
                // When setting to inactive, archive only clearances - preserve student data
                $clearancesUpdated = Clearance::where('academic_id', $id)
                    ->where('is_archived', false)
                    ->update(['is_archived' => true]);

                $message = "Academic Year updated successfully. {$clearancesUpdated} clearances have been archived. All student data has been preserved.";
            } elseif ($newStatus === 'active' && $oldStatus === 'inactive') {
                // Unarchive clearances when reactivating academic year
                $clearancesUpdated = Clearance::where('academic_id', $id)
                    ->where('is_archived', true)
                    ->update(['is_archived' => false]);

                $message = "Academic Year updated successfully. {$clearancesUpdated} clearances have been unarchived.";
            } elseif ($newStatus === 'active' && $oldStatus !== 'active') {
                // When setting an academic year to active, create clearances for all students
                try {
                    // First, set all other academic years to inactive
                    AcademicYear::where('id', '!=', $id)->update(['status' => 'inactive']);

                    // Update all active students to the new academic year
                    $studentsUpdated = Student::where('is_archived', false)
                        ->update(['academic_id' => $id]);

                    // Run the command to create clearances for the new academic year
                    Artisan::call('clearance:create-for-new-year', [
                        '--academic-year' => $id,
                        '--force' => true
                    ]);

                    $output = Artisan::output();
                    $message = "Academic Year activated successfully. {$studentsUpdated} students updated to new academic year. Clearances have been created for all students. " . strip_tags($output);
                } catch (\Exception $e) {
                    $message = "Academic Year updated successfully, but there was an issue creating clearances: " . $e->getMessage();
                }
            } else {
                $message = 'Academic Year updated successfully.';
            }
        } else {
            $message = 'Academic Year updated successfully.';
        }

        return redirect()->route('admin.academic_year.list_of_academic_year')->with('success', $message);
    }
    public function deleteAY($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
        $academic_year->delete();

        return redirect()->back()->with('error', 'Academic Year deleted successfully.');
    }
}
