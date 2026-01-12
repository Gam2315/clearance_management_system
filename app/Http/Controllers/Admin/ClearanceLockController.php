<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClearanceLockController extends Controller
{
    /**
     * Display locked clearances
     */
    public function index(Request $request)
    {
        $query = Clearance::with(['student.user', 'student.department', 'academicYear', 'locker'])
                          ->where('is_locked', true);

        // Filter by academic year if specified
        if ($request->filled('academic_year')) {
            $query->where('academic_id', $request->academic_year);
        }

        // Filter by department if specified
        if ($request->filled('department')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        $lockedClearances = $query->orderBy('locked_at', 'desc')->paginate(20);
        $academicYears = AcademicYear::orderBy('created_at', 'desc')->get();
        $departments = \App\Models\Department::whereIn('id', [1, 2, 3, 4])->get();

        return view('admin.clearance.locked-clearances', compact(
            'lockedClearances', 
            'academicYears', 
            'departments'
        ));
    }

    /**
     * Show unlock form for specific clearance
     */
    public function show($id)
    {
        $clearance = Clearance::with([
            'student.user', 
            'student.department', 
            'academicYear', 
            'locker',
            'statuses.department'
        ])->findOrFail($id);

        if (!$clearance->is_locked) {
            return redirect()->route('admin.clearance.locked-clearances')
                           ->with('error', 'This clearance is not locked.');
        }

        return view('admin.clearance.unlock-clearance', compact('clearance'));
    }

    /**
     * Unlock a specific clearance
     */
    public function unlock(Request $request, $id)
    {
        $request->validate([
            'unlock_reason' => 'required|string|max:500',
            'confirm_unlock' => 'required|accepted'
        ]);

        $clearance = Clearance::findOrFail($id);

        if (!$clearance->is_locked) {
            return redirect()->back()->with('error', 'This clearance is not locked.');
        }

        // Check if user has permission to unlock
        $user = Auth::user();
        $canUnlock = false;

        if ($clearance->can_unlock_roles) {
            $canUnlock = in_array($user->role, $clearance->can_unlock_roles);
        } else {
            // Default: only admin can unlock
            $canUnlock = $user->role === 'admin';
        }

        if (!$canUnlock) {
            return redirect()->back()->with('error', 
                'You do not have permission to unlock this clearance.');
        }

        DB::transaction(function() use ($clearance, $request, $user) {
            // Unlock the clearance
            $clearance->update([
                'is_locked' => false,
                'lock_reason' => null,
                'locked_at' => null,
                'locked_by' => null,
                'can_unlock_roles' => null
            ]);

            // Update student record
            $student = $clearance->student;
            $lockedYears = $student->locked_academic_years ? 
                          json_decode($student->locked_academic_years, true) : [];

            // Remove this academic year from locked years
            $lockedYears = array_filter($lockedYears, function($year) use ($clearance) {
                return $year['academic_year_id'] != $clearance->academic_id;
            });

            // If no more locked years, update has_locked_clearance
            $hasLockedClearance = !empty($lockedYears);

            $student->update([
                'has_locked_clearance' => $hasLockedClearance,
                'locked_academic_years' => empty($lockedYears) ? null : json_encode(array_values($lockedYears))
            ]);

            // Log the unlock action
            activity()
                ->performedOn($clearance)
                ->causedBy($user)
                ->withProperties([
                    'unlock_reason' => $request->unlock_reason,
                    'student_number' => $student->student_number,
                    'academic_year' => $clearance->academicYear->academic_year ?? 'N/A'
                ])
                ->log('Clearance unlocked');
        });

        return redirect()->route('admin.clearance.locked-clearances')
                       ->with('success', 
                           "Clearance unlocked successfully for student {$clearance->student->student_number}.");
    }

    /**
     * Bulk unlock clearances
     */
    public function bulkUnlock(Request $request)
    {
        $request->validate([
            'clearance_ids' => 'required|array',
            'clearance_ids.*' => 'exists:clearances,id',
            'bulk_unlock_reason' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        $unlockedCount = 0;

        DB::transaction(function() use ($request, $user, &$unlockedCount) {
            foreach ($request->clearance_ids as $clearanceId) {
                $clearance = Clearance::find($clearanceId);
                
                if ($clearance && $clearance->is_locked) {
                    // Check permission
                    $canUnlock = false;
                    if ($clearance->can_unlock_roles) {
                        $canUnlock = in_array($user->role, $clearance->can_unlock_roles);
                    } else {
                        $canUnlock = $user->role === 'admin';
                    }

                    if ($canUnlock) {
                        // Unlock the clearance
                        $clearance->update([
                            'is_locked' => false,
                            'lock_reason' => null,
                            'locked_at' => null,
                            'locked_by' => null,
                            'can_unlock_roles' => null
                        ]);

                        // Update student record
                        $student = $clearance->student;
                        $lockedYears = $student->locked_academic_years ? 
                                      json_decode($student->locked_academic_years, true) : [];

                        $lockedYears = array_filter($lockedYears, function($year) use ($clearance) {
                            return $year['academic_year_id'] != $clearance->academic_id;
                        });

                        $hasLockedClearance = !empty($lockedYears);

                        $student->update([
                            'has_locked_clearance' => $hasLockedClearance,
                            'locked_academic_years' => empty($lockedYears) ? null : json_encode(array_values($lockedYears))
                        ]);

                        $unlockedCount++;

                        // Log the action
                        activity()
                            ->performedOn($clearance)
                            ->causedBy($user)
                            ->withProperties([
                                'unlock_reason' => $request->bulk_unlock_reason,
                                'bulk_operation' => true,
                                'student_number' => $student->student_number
                            ])
                            ->log('Clearance bulk unlocked');
                    }
                }
            }
        });

        return redirect()->back()->with('success', 
            "Successfully unlocked {$unlockedCount} clearances.");
    }

    /**
     * Get locked clearances statistics
     */
    public function statistics()
    {
        $stats = [
            'total_locked' => Clearance::where('is_locked', true)->count(),
            'by_department' => Clearance::where('is_locked', true)
                                      ->join('students', 'clearances.student_id', '=', 'students.id')
                                      ->join('departments', 'students.department_id', '=', 'departments.id')
                                      ->selectRaw('departments.department_name, COUNT(*) as count')
                                      ->groupBy('departments.department_name')
                                      ->get(),
            'by_academic_year' => Clearance::where('is_locked', true)
                                          ->join('academic_years', 'clearances.academic_id', '=', 'academic_years.id')
                                          ->selectRaw('academic_years.academic_year, academic_years.semester, COUNT(*) as count')
                                          ->groupBy('academic_years.academic_year', 'academic_years.semester')
                                          ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Lock all incomplete clearances for current academic year
     */
    public function lockCurrentYear(Request $request)
    {
        $request->validate([
            'confirm' => 'required|boolean'
        ]);

        if (!$request->confirm) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmation required'
            ], 400);
        }

        try {
            // Get current academic year
            $currentAcademicYear = \App\Models\AcademicYear::where('status', 'active')->first();

            if (!$currentAcademicYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active academic year found'
                ], 400);
            }

            // Find students with incomplete clearances
            $studentsToLock = \App\Models\Student::whereHas('clearances', function($query) use ($currentAcademicYear) {
                $query->where('academic_id', $currentAcademicYear->id)
                      ->where('overall_status', '!=', 'cleared')
                      ->where('is_locked', false);
            })->with(['clearances' => function($query) use ($currentAcademicYear) {
                $query->where('academic_id', $currentAcademicYear->id);
            }])->get();

            if ($studentsToLock->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No incomplete clearances found to lock',
                    'locked_count' => 0
                ]);
            }

            $lockedCount = 0;
            $systemUser = Auth::user();

            DB::transaction(function() use ($studentsToLock, $currentAcademicYear, $systemUser, &$lockedCount) {
                foreach ($studentsToLock as $student) {
                    $clearance = $student->clearances->first();

                    if ($clearance && !$clearance->is_locked) {
                        // Lock the clearance
                        $clearance->update([
                            'is_locked' => true,
                            'lock_reason' => "Incomplete clearance from Academic Year {$currentAcademicYear->academic_year} - {$currentAcademicYear->semester}. Student failed to complete clearance requirements before the deadline.",
                            'locked_at' => now(),
                            'locked_by' => $systemUser->id,
                            'can_unlock_roles' => ['admin', 'registrar']
                        ]);

                        // Update student record
                        $lockedYears = $student->locked_academic_years ?
                                      json_decode($student->locked_academic_years, true) : [];
                        $lockedYears[] = [
                            'academic_year_id' => $currentAcademicYear->id,
                            'academic_year' => $currentAcademicYear->academic_year,
                            'semester' => $currentAcademicYear->semester,
                            'locked_at' => now()->toISOString()
                        ];

                        $student->update([
                            'has_locked_clearance' => true,
                            'locked_academic_years' => json_encode($lockedYears)
                        ]);

                        $lockedCount++;
                    }
                }
            });

            // Log the action
            activity()
                ->causedBy($systemUser)
                ->withProperties([
                    'academic_year' => $currentAcademicYear->academic_year,
                    'locked_count' => $lockedCount
                ])
                ->log('Bulk locked incomplete clearances');

            return response()->json([
                'success' => true,
                'message' => "Successfully locked {$lockedCount} incomplete clearances",
                'locked_count' => $lockedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while locking clearances: ' . $e->getMessage()
            ], 500);
        }
    }
}
