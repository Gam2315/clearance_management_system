<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NfcController extends Controller
{

    public function showLinkForm()
    {

        return view('employee.clearance-tap.index');
    }

    public function DeanshowLinkForm()
    {

        return view('dean.clearance-tap.index');
    }

     public function AdvisershowLinkForm()
    {

        return view('adviser.clearance-tap.index');
    }

     public function OfficershowLinkForm()
    {

        return view('officer.clearance-tap.index');
    }


    public function storeUid(Request $request)
    {
        $uid = strtoupper($request->input('uid'));

        if (!$uid) {
            return response()->json(['message' => 'UID missing'], 400);
        }

        // Clear old
        DB::table('nfc_taps')->delete();

        // Insert new
        DB::table('nfc_taps')->insert([
            'uid' => $uid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'UID stored', 'uid' => $uid]);
    }

    public function EmployeelinkToStudent(Request $request)
    {
        $request->validate([
            'student_number' => 'required'
        ]);

        // Step 1: Get the latest tapped UID or use provided UID
        $uid = $request->input('uid');
        if (!$uid) {
            // Check for recent NFC tap (within last 30 seconds)
            $uidRecord = DB::table('nfc_taps')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->latest('created_at')
                ->first();

            if (!$uidRecord) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'No NFC card detected. Please tap your ID.'], 400);
                }
                return back()->with('error', 'No NFC card detected. Please tap your ID.');
            }
            $uid = $uidRecord->uid;
        }

        // Step 2: Find the student by student number
        $student = Student::where('student_number', $request->student_number)->first();

        if (!$student) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Student not found. Please check the student number.'], 400);
            }
            return back()->with('error', 'Student not found. Please check the student number.');
        }

        // Step 3: Check if this UID is already linked to another student
        $existingStudentWithUID = Student::where('nfc_uid', $uid)->first();

        if ($existingStudentWithUID && $existingStudentWithUID->id !== $student->id) {
            $errorMessage = 'This NFC card is already linked to another student: ' . $existingStudentWithUID->user->firstname . ' ' . $existingStudentWithUID->user->lastname . ' (' . $existingStudentWithUID->student_number . ')';
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $errorMessage,
                    'existing_student' => [
                        'student_number' => $existingStudentWithUID->student_number,
                        'name' => $existingStudentWithUID->user->firstname . ' ' . $existingStudentWithUID->user->lastname
                    ]
                ], 400);
            }
            return back()->with('error', $errorMessage);
        }

        // Step 4: Check if this student already has an NFC UID
        if ($student->nfc_uid && strtoupper($student->nfc_uid) !== strtoupper($uid)) {
            // Student already has a different NFC card linked - show error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This student is already linked to another NFC card. Cannot link new card.',
                    'existing_nfc_uid' => $student->nfc_uid,
                    'attempted_uid' => $uid
                ], 400);
            }
            return back()->with('error', 'This student is already linked to another NFC card. Cannot link new card.');
        }

        // Step 5: Check if student already has this exact UID linked
        if ($student->nfc_uid && strtoupper($student->nfc_uid) === strtoupper($uid)) {
            // Student already has this exact NFC card linked - proceed to student info page
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student already has this NFC card linked. Redirecting to student info.',
                    'redirect' => route('employee.student.student-info', ['student' => $student->id]),
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->firstname . ' ' . $student->user->lastname,
                        'student_number' => $student->student_number,
                        'nfc_uid' => $student->nfc_uid
                    ]
                ]);
            }
            return redirect()->route('employee.student.student-info', ['student' => $student->id])
                ->with('info', 'Student already has this NFC card linked.');
        }

        // Step 6: All good, link the card (normalize UID to uppercase)
        $student->nfc_uid = strtoupper($uid);
        $student->save();

        // Step 6: Update the tap timestamp so the system can immediately detect the linked card
        DB::table('nfc_taps')->where('uid', $uid)->update([
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'NFC card successfully linked to student!',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->firstname . ' ' . $student->user->lastname,
                    'student_number' => $student->student_number
                ]
            ]);
        }

        return redirect()->route('employee.student.student-info', ['student' => $student->id])
            ->with('success', 'NFC card successfully linked to student!');
    }


     public function DeanlinkToStudent(Request $request)
    {
        $request->validate([
            'student_number' => 'required'
        ]);

        // Step 1: Get the latest tapped UID or use provided UID
        $uid = $request->input('uid');
        if (!$uid) {
            // Check for recent NFC tap (within last 30 seconds)
            $uidRecord = DB::table('nfc_taps')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->latest('created_at')
                ->first();

            if (!$uidRecord) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'No NFC card detected. Please tap your ID.'], 400);
                }
                return back()->with('error', 'No NFC card detected. Please tap your ID.');
            }
            $uid = $uidRecord->uid;
        }

        // Step 2: Find the student by student number
        $student = Student::where('student_number', $request->student_number)->first();

        if (!$student) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Student not found. Please check the student number.'], 400);
            }
            return back()->with('error', 'Student not found. Please check the student number.');
        }

        // Step 3: Check if this UID is already linked to another student
        $existingStudentWithUID = Student::where('nfc_uid', $uid)->first();

        if ($existingStudentWithUID && $existingStudentWithUID->id !== $student->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This NFC card is already linked to another student.'], 400);
            }
            return back()->with('error', 'This NFC card is already linked to another student.');
        }

        // Step 4: Check if this student already has an NFC UID
        if ($student->nfc_uid && strtoupper($student->nfc_uid) !== strtoupper($uid)) {
            // Student already has a different NFC card linked - show error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This student is already linked to another NFC card. Cannot link new card.',
                    'existing_nfc_uid' => $student->nfc_uid,
                    'attempted_uid' => $uid
                ], 400);
            }
            return back()->with('error', 'This student is already linked to another NFC card. Cannot link new card.');
        }

        // Step 5: Check if student already has this exact UID linked
        if ($student->nfc_uid && strtoupper($student->nfc_uid) === strtoupper($uid)) {
            // Student already has this exact NFC card linked - proceed to student info page
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student already has this NFC card linked. Redirecting to student info.',
                    'redirect' => route('dean.student.student-info', ['student' => $student->id]),
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->firstname . ' ' . $student->user->lastname,
                        'student_number' => $student->student_number,
                        'nfc_uid' => $student->nfc_uid
                    ]
                ]);
            }
            return redirect()->route('dean.student.student-info', ['student' => $student->id])
                ->with('info', 'Student already has this NFC card linked.');
        }

        // Step 6: All good, link the card (normalize UID to uppercase)
        $student->nfc_uid = strtoupper($uid);
        $student->save();

        // Step 6: Update the tap timestamp so the system can immediately detect the linked card
        DB::table('nfc_taps')->where('uid', $uid)->update([
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'NFC card successfully linked to student!',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->firstname . ' ' . $student->user->lastname,
                    'student_number' => $student->student_number
                ]
            ]);
        }

        return redirect()->route('dean.student.student-info', ['student' => $student->id])
            ->with('success', 'NFC card successfully linked to student!');
    }


    public function AdviserlinkToStudent(Request $request)
    {
        $request->validate([
            'student_number' => 'required'
        ]);

        // Step 1: Get the latest tapped UID or use provided UID
        $uid = $request->input('uid');
        if (!$uid) {
            // Check for recent NFC tap (within last 30 seconds)
            $uidRecord = DB::table('nfc_taps')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->latest('created_at')
                ->first();

            if (!$uidRecord) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'No NFC card detected. Please tap your ID.'], 400);
                }
                return back()->with('error', 'No NFC card detected. Please tap your ID.');
            }
            $uid = $uidRecord->uid;
        }

        // Step 2: Find the student by student number
        $student = Student::where('student_number', $request->student_number)->first();

        if (!$student) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Student not found. Please check the student number.'], 400);
            }
            return back()->with('error', 'Student not found. Please check the student number.');
        }

        // Step 3: Check if this UID is already linked to another student
        $existingStudentWithUID = Student::where('nfc_uid', $uid)->first();

        if ($existingStudentWithUID && $existingStudentWithUID->id !== $student->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This NFC card is already linked to another student.'], 400);
            }
            return back()->with('error', 'This NFC card is already linked to another student.');
        }

        // Step 4: Check if this student already has an NFC UID
        if ($student->nfc_uid && strtoupper($student->nfc_uid) !== strtoupper($uid)) {
            // Student already has a different NFC card linked - show error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This student is already linked to another NFC card. Cannot link new card.',
                    'existing_nfc_uid' => $student->nfc_uid,
                    'attempted_uid' => $uid
                ], 400);
            }
            return back()->with('error', 'This student is already linked to another NFC card. Cannot link new card.');
        }

        // Step 5: Check if student already has this exact UID linked
        if ($student->nfc_uid && strtoupper($student->nfc_uid) === strtoupper($uid)) {
            // Student already has this exact NFC card linked - proceed to student info page
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student already has this NFC card linked. Redirecting to student info.',
                    'redirect' => route('adviser.student.student-info', ['student' => $student->id]),
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->firstname . ' ' . $student->user->lastname,
                        'student_number' => $student->student_number,
                        'nfc_uid' => $student->nfc_uid
                    ]
                ]);
            }
            return redirect()->route('adviser.student.student-info', ['student' => $student->id])
                ->with('info', 'Student already has this NFC card linked.');
        }

        // Step 6: All good, link the card (normalize UID to uppercase)
        $student->nfc_uid = strtoupper($uid);
        $student->save();

        // Step 6: Update the tap timestamp so the system can immediately detect the linked card
        DB::table('nfc_taps')->where('uid', $uid)->update([
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'NFC card successfully linked to student!',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->firstname . ' ' . $student->user->lastname,
                    'student_number' => $student->student_number
                ]
            ]);
        }

        return redirect()->route('adviser.student.student-info', ['student' => $student->id])
            ->with('success', 'NFC card successfully linked to student!');
    }

    public function OfficerlinkToStudent(Request $request)
    {
        $request->validate([
            'student_number' => 'required'
        ]);

        // Step 1: Get the latest tapped UID or use provided UID
        $uid = $request->input('uid');
        if (!$uid) {
            // Check for recent NFC tap (within last 30 seconds)
            $uidRecord = DB::table('nfc_taps')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->latest('created_at')
                ->first();

            if (!$uidRecord) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'No NFC card detected. Please tap your ID.'], 400);
                }
                return back()->with('error', 'No NFC card detected. Please tap your ID.');
            }
            $uid = $uidRecord->uid;
        }

        // Step 2: Find the student by student number
        $student = Student::where('student_number', $request->student_number)->first();

        if (!$student) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Student not found. Please check the student number.'], 400);
            }
            return back()->with('error', 'Student not found. Please check the student number.');
        }

        // Step 3: Check if this UID is already linked to another student
        $existingStudentWithUID = Student::where('nfc_uid', $uid)->first();

        if ($existingStudentWithUID && $existingStudentWithUID->id !== $student->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This NFC card is already linked to another student.'], 400);
            }
            return back()->with('error', 'This NFC card is already linked to another student.');
        }

        // Step 4: Check if this student already has an NFC UID
        if ($student->nfc_uid && strtoupper($student->nfc_uid) !== strtoupper($uid)) {
            // Student already has a different NFC card linked - show error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This student is already linked to another NFC card. Cannot link new card.',
                    'existing_nfc_uid' => $student->nfc_uid,
                    'attempted_uid' => $uid
                ], 400);
            }
            return back()->with('error', 'This student is already linked to another NFC card. Cannot link new card.');
        }

        // Step 5: Check if student already has this exact UID linked
        if ($student->nfc_uid && strtoupper($student->nfc_uid) === strtoupper($uid)) {
            // Student already has this exact NFC card linked - proceed to student info page
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student already has this NFC card linked. Redirecting to student info.',
                    'redirect' => route('officer.student.student-info', ['student' => $student->id]),
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->firstname . ' ' . $student->user->lastname,
                        'student_number' => $student->student_number,
                        'nfc_uid' => $student->nfc_uid
                    ]
                ]);
            }
            return redirect()->route('officer.student.student-info', ['student' => $student->id])
                ->with('info', 'Student already has this NFC card linked.');
        }

        // Step 6: All good, link the card (normalize UID to uppercase)
        $student->nfc_uid = strtoupper($uid);
        $student->save();

        // Step 6: Update the tap timestamp so the system can immediately detect the linked card
        DB::table('nfc_taps')->where('uid', $uid)->update([
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'NFC card successfully linked to student!',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->firstname . ' ' . $student->user->lastname,
                    'student_number' => $student->student_number
                ]
            ]);
        }

        return redirect()->route('officer.student.student-info', ['student' => $student->id])
            ->with('success', 'NFC card successfully linked to student!');
    }

    /**
     * Store NFC tap data from external source (like Python script)
     */
    public function storeTap(Request $request)
    {
        try {
            $uid = $request->input('uid');

            if (!$uid) {
                return response()->json([
                    'success' => false,
                    'message' => 'UID is required'
                ], 400);
            }

            // Normalize UID to uppercase
            $uid = strtoupper($uid);

            // Always clear old taps and store the new tap first
            DB::table('nfc_taps')->delete();
            DB::table('nfc_taps')->insert([
                'uid' => $uid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('NFC tap stored', ['uid' => $uid]);

            // Check if this UID is already linked to a student (after storing)
            $existingStudent = Student::where('nfc_uid', $uid)->first();
            if ($existingStudent) {
                return response()->json([
                    'success' => true,
                    'message' => 'NFC card detected - already linked to student',
                    'student_info' => [
                        'student_number' => $existingStudent->student_number,
                        'name' => $existingStudent->user->firstname . ' ' . $existingStudent->user->lastname
                    ],
                    'uid' => $uid,
                    'already_linked' => true,
                    'timestamp' => now()->toISOString()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'NFC tap stored successfully - ready for linking',
                'uid' => $uid,
                'already_linked' => false,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing NFC tap', [
                'error' => $e->getMessage(),
                'uid' => $request->input('uid')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error storing NFC tap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if an NFC card has been detected recently
     */
    public function checkNfcCard()
    {
        try {
            // Check if there's a recent NFC tap (within last 30 seconds)
            $recentTap = DB::table('nfc_taps')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->latest('created_at')
                ->first();

            if ($recentTap) {
                return response()->json([
                    'nfc_detected' => true,
                    'uid' => $recentTap->uid,
                    'timestamp' => $recentTap->created_at
                ]);
            } else {
                return response()->json([
                    'nfc_detected' => false,
                    'message' => 'No NFC card detected recently'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking NFC card', ['error' => $e->getMessage()]);

            return response()->json([
                'nfc_detected' => false,
                'error' => 'Error checking NFC status'
            ], 500);
        }
    }
}
