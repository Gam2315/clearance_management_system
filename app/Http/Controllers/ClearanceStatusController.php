<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClearanceStatus;
use App\Models\Student;
use App\Models\Clearance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClearanceStatusController extends Controller
{
    public function Employeestore(Request $request)
    {


        // Validate the request
        $validationRules = [
            'clearance_id' => 'required|exists:clearances,id',
            'student_id' => 'required|exists:students,id',
            'department_id' => 'required|exists:departments,id',
            'approved_by' => 'required|exists:users,id',
            'approver_role' => 'required|in:employee',
            'status' => 'required|in:cleared,pending',
        ];

        // Add OR number validation for BAO department (ID 14)
        if ($request->department_id == 5) {
            $validationRules['or_number'] = 'required|string';
        } else {
            $validationRules['or_number'] = 'nullable|string';
        }

        $request->validate($validationRules);

        // Check if clearance is locked
        $clearance = Clearance::find($request->clearance_id);
        if ($clearance && $clearance->is_locked) {
            return redirect()->back()->with(
                'error',
                'This clearance is locked and cannot be processed. Reason: ' . $clearance->lock_reason .
                    '. Please contact the administrator to unlock it.'
            );
        }

        // Check if a status already exists for this clearance and department
        $existingStatus = ClearanceStatus::where('clearance_id', $request->clearance_id)
            ->where('department_id', $request->department_id)
            ->first();

        // If student is already cleared, don't allow changing to pending
        if ($existingStatus && $existingStatus->status === 'cleared') {
            return redirect()->back()
                ->with('error', "Student has already been cleared");
        }

        // If status doesn't exist or is different, update/create it
        if ($existingStatus) {
            // Update existing status
            $existingStatus->status = $request->status;
            $existingStatus->approved_by = $request->approved_by;

            if ($request->filled('or_number')) {
                $existingStatus->or_number = $request->or_number;
            }

            $existingStatus->save();
        } else {
            // Create new status
            ClearanceStatus::create([
                'clearance_id' => $request->clearance_id,
                'student_id' => $request->student_id,
                'department_id' => $request->department_id,
                'approved_by' => $request->approved_by,
                'approver_role' => $request->approver_role,
                'status' => $request->status,
                'or_number' => $request->or_number,
            ]);
        }

        // Clear cache to ensure updated status is displayed
        Cache::forget('student_clearance_' . $request->student_id);
        $statusText = $request->status === 'cleared' ? 'cleared' : 'marked as pending';

        return redirect()->route('employee.clearance.clearance-tap-id')->with('success', "Student has been {$statusText}!");
    }

    public function Adviserstore(Request $request)
    {
        // Validate the request
        $validationRules = [
            'clearance_id' => 'required|exists:clearances,id',
            'student_id' => 'required|exists:students,id',
            'department_id' => 'required|exists:departments,id',
            'approved_by' => 'required|exists:users,id',
            'approver_role' => 'required|in:adviser',
            'status' => 'required|in:cleared,pending',
        ];

        if ($request->department_id == 5) {
            $validationRules['or_number'] = 'required|string';
        } else {
            $validationRules['or_number'] = 'nullable|string';
        }

        $request->validate($validationRules);

        // Check if clearance is locked
        $clearance = Clearance::find($request->clearance_id);
        if ($clearance && $clearance->is_locked) {
            return redirect()->back()->with(
                'error',
                'This clearance is locked and cannot be processed. Reason: ' . $clearance->lock_reason .
                    '. Please contact the administrator to unlock it.'
            );
        }

        // ❌ Skip fetching old status
        // But still block if a cleared record exists from another adviser
        $alreadySubmitted = ClearanceStatus::where('clearance_id', $request->clearance_id)
            ->where('student_id', $request->student_id)
            ->where('department_id', $request->department_id)
            ->where('approver_role', $request->approver_role)
            ->where('approved_by', auth()->id())
            ->first();

        if ($alreadySubmitted) {
            return redirect()->back()
                ->with('error', 'You have already submitted a clearance status for this student.');
        }



        // ✅ Always insert a new status record
        ClearanceStatus::create([
            'clearance_id' => $request->clearance_id,
            'student_id' => $request->student_id,
            'department_id' => $request->department_id,
            'approved_by' => $request->approved_by,
            'approver_role' => $request->approver_role,
            'status' => $request->status,
            'or_number' => $request->or_number,
        ]);

        Cache::forget('student_clearance_' . $request->student_id);

        $statusText = $request->status === 'cleared' ? 'cleared' : 'marked as pending';
        return redirect()->route('adviser.clearance.clearance-tap-id')
            ->with('success', "Student has been {$statusText}!");
    }


    public function Deanstore(Request $request)
    {
        // Validate the request
        $validationRules = [
            'clearance_id' => 'required|exists:clearances,id',
            'student_id' => 'required|exists:students,id',
            'department_id' => 'required|exists:departments,id',
            'approved_by' => 'required|exists:users,id',
            'approver_role' => 'required|in:dean',
            'status' => 'required|in:cleared,pending',
        ];

        // Add OR number validation for BAO department (ID 5)
        if ($request->department_id == 5) {
            $validationRules['or_number'] = 'required|string';
        } else {
            $validationRules['or_number'] = 'nullable|string';
        }

        $request->validate($validationRules);

        // Check if clearance is locked
        $clearance = Clearance::find($request->clearance_id);
        if ($clearance && $clearance->is_locked) {
            return redirect()->back()->with(
                'error',
                'This clearance is locked and cannot be processed. Reason: ' . $clearance->lock_reason .
                    '. Please contact the administrator to unlock it.'
            );
        }

        // Check if student is already cleared for this department
        $alreadySubmitted = ClearanceStatus::where('clearance_id', $request->clearance_id)
            ->where('student_id', $request->student_id)
            ->where('department_id', $request->department_id)
            ->where('approver_role', $request->approver_role)
            ->where('approved_by', auth()->id())
            ->first();

        if ($alreadySubmitted) {
            return redirect()->back()
                ->with('error', 'You have already submitted a clearance status for this student.');
        }

        // Create new status only (no update)
        ClearanceStatus::create([
            'clearance_id' => $request->clearance_id,
            'student_id' => $request->student_id,
            'department_id' => $request->department_id,
            'approved_by' => $request->approved_by,
            'approver_role' => $request->approver_role,
            'status' => $request->status,
            'or_number' => $request->or_number,
        ]);

        // Clear cache
        Cache::forget('student_clearance_' . $request->student_id);

        $statusText = $request->status === 'cleared' ? 'cleared' : 'marked as pending';
        return redirect()->back()
            ->with('success', "Student has been {$statusText}!");
    }



    public function Officerstore(Request $request)
    {
        // Validate the request
        $validationRules = [
            'clearance_id' => 'required|exists:clearances,id',
            'student_id' => 'required|exists:students,id',
            'department_id' => 'required|exists:departments,id',
            'approved_by' => 'required|exists:users,id',
            'approver_role' => 'required|in:officer',
            'status' => 'required|in:cleared,pending',
        ];

        if ($request->department_id == 5) {
            $validationRules['or_number'] = 'required|string';
        } else {
            $validationRules['or_number'] = 'nullable|string';
        }

        $request->validate($validationRules);

        // Check if clearance is locked
        $clearance = Clearance::find($request->clearance_id);
        if ($clearance && $clearance->is_locked) {
            return redirect()->back()->with(
                'error',
                'This clearance is locked and cannot be processed. Reason: ' . $clearance->lock_reason .
                    '. Please contact the administrator to unlock it.'
            );
        }

        $alreadySubmitted = ClearanceStatus::where('clearance_id', $request->clearance_id)
            ->where('student_id', $request->student_id)
            ->where('department_id', $request->department_id)
            ->where('approver_role', $request->approver_role)
            ->where('approved_by', auth()->id())
            ->first();

        if ($alreadySubmitted) {
            return redirect()->back()
                ->with('error', 'You have already submitted a clearance status for this student.');
        }


        // ✅ Always insert a new status record
        ClearanceStatus::create([
            'clearance_id' => $request->clearance_id,
            'student_id' => $request->student_id,
            'department_id' => $request->department_id,
            'approved_by' => $request->approved_by,
            'approver_role' => $request->approver_role,
            'status' => $request->status,
            'or_number' => $request->or_number,
        ]);

        Cache::forget('student_clearance_' . $request->student_id);

        $statusText = $request->status === 'cleared' ? 'cleared' : 'marked as pending';
        return redirect()->route('officer.clearance.clearance-tap-id')
            ->with('success', "Student has been {$statusText}!");
    }

    /**
     * Get the specific reason why a student is blocked from clearance
     */
    private function getBlockingReason($student)
    {
        if ($student->is_graduated) {
            return 'Student has already graduated and cannot proceed with clearance.';
        }

        if ($student->hasBlockingViolations()) {
            return 'Student has active violations that block clearance processing.';
        }

        if (!$student->hasPreviousYearClearanceCompleted()) {
            return 'Student did not complete clearance for the previous academic year and is blocked from current year clearance.';
        }

        return 'Student is blocked from clearance for unknown reasons.';
    }
}
