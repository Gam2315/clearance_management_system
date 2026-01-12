@extends('admin.layout.header')

@section('main-content')

<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Student Clearance</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">
                        <i class="anticon anticon-home m-r-5"></i>Home
                    </a>
                    <span class="breadcrumb-item active">Student Clearance</span>
                </nav>
            </div>
        </div>

        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')

        <div class="row">
            <!-- Clearance Status -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @if($clearance)
                        {{-- Show lock warning if clearance is locked --}}
                        @if($clearance->is_locked)
                        <div class="alert alert-danger mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lock fa-2x mr-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">🔒 Clearance Locked</h5>
                                    <p class="mb-1"><strong>Reason:</strong> {{ $clearance->lock_reason }}</p>
                                    <p class="mb-1"><strong>Locked on:</strong> {{ $clearance->locked_at ?
                                        $clearance->locked_at->format('M d, Y g:i A') : 'N/A' }}</p>
                                    <small class="text-muted">Please contact the Registrar's Office to resolve this
                                        issue.</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="clearance-overview mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Academic Year:</strong> {{ $clearance->academicYear->academic_year ??
                                        'N/A' }}</p>
                                    <p><strong>Semester:</strong> {{ $clearance->academicYear->semester ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Overall Status:</strong>
                                        @if($clearance->is_locked)
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-lock mr-1"></i>LOCKED
                                        </span>
                                        @else
                                        <span
                                            class="badge badge-{{ $clearance->overall_status == 'cleared' ? 'success' : 'warning' }} badge-lg">
                                            {{ ucfirst($clearance->overall_status ?? 'Pending') }}
                                        </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Department Clearance Status -->
                        <div class="clearance-status-grid">
                            <h5 class="mb-4 text-uppercase font-weight-bold">STUDENT CLEARANCE</h5>

                            @if($student)
                            <div class="student-header-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Student Number:</strong> {{ $student->student_number }}</p>
                                        <p><strong>Name:</strong> {{ $student->user->firstname }} {{
                                            $student->user->lastname }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Course:</strong> {{ $student->courses->course_name ?? 'N/A' }}</p>
                                        <p><strong>Year Level:</strong> {{ $student->year }}</p>
                                    </div>
                                </div>
                                <hr class="my-3">
                            </div>
                            @endif

                            @if($clearance->statuses && $clearance->statuses->count() > 0)
                            <div class="row">
                                @php
                                // Get required departments for this student
                                $requiredDepartments = $student->getRequiredDepartments();

                                // Get existing statuses keyed by department_id
                                $existingStatuses = $clearance->statuses->keyBy('department_id');

                                // Map department IDs to display names
                                $departmentMapping = [
                                1 => 'SITE DEAN', // School of Information Technology Education
                                2 => 'SASTE DEAN', // School of Arts, Sciences and Teacher Education
                                3 => 'SNAHS DEAN', // School of Nursing and Allied Health Sciences
                                4 => 'SBAHM DEAN', // School of Business, Accountancy, and Hospitality Management
                                5 => 'BAO',
                                8 => 'CLINIC',
                                17 => 'SPUP UNIWIDE',
                                10 => 'LIBRARY',
                                6 => 'OSA',
                                9 => 'RESEARCH',
                                18 => 'UNIWIDE',
                                13 => 'CHRISTIAN FORMATION',
                                12 => 'COLLEGE SCIENCE LAB',
                                11 => 'UNIVERSITY REGISTRAR',
                                14 => 'BOUTIQUE',
                                7 => 'GUIDANCE',
                                16 => 'ENGINEERING',
                                15 => 'COMPUTER',
                                ];

                                // Group statuses by display name
                                $groupedStatuses = [];

                                // Process all required departments to show comprehensive view
                                foreach ($requiredDepartments as $deptId) {
                                    // Skip department 17 (UNIWIDE) for UNIWIDE students since they have dedicated President section
                                    if ($deptId == 18 && $student->is_uniwide == 1) {
                                        continue;
                                    }

                                    // Get all statuses for this department (there might be multiple)
                                    $departmentStatuses = $clearance->statuses->where('department_id', $deptId);

                                    // Find the dean/employee status (NOT adviser status)
                                    $deanEmployeeStatus = null;
                                    foreach ($departmentStatuses as $status) {
                                        // Only look for dean or employee roles, skip adviser roles
                                        if (in_array($status->approver_role, ['dean', 'employee'])) {
                                            $deanEmployeeStatus = $status;
                                            break; // Use the first dean/employee status found
                                        }
                                    }

                                    // For academic departments, group them as "SCHOOL DEAN"
                                    $displayName = $departmentMapping[$deptId] ?? 'UNKNOWN';
                                    if (in_array($deptId, [1, 2, 3, 4])) {
                                        $displayName = 'SCHOOL DEAN';
                                    }

                                    // Always add the department, even if no dean/employee status exists (shows as PENDING)
                                    if (!isset($groupedStatuses[$displayName])) {
                                        $groupedStatuses[$displayName] = $deanEmployeeStatus; // Can be null for pending
                                    } else {
                                        // Prioritize dean role over employee role for the same display name
                                        $currentStatus = $groupedStatuses[$displayName];
                                        $currentRole = $currentStatus ? $currentStatus->approver_role : null;
                                        $newRole = $deanEmployeeStatus ? $deanEmployeeStatus->approver_role : null;

                                        // Priority: dean > employee
                                        if ($newRole === 'dean' || ($newRole === 'employee' && $currentRole !== 'dean')) {
                                            $groupedStatuses[$displayName] = $deanEmployeeStatus;
                                        }
                                    }
                                }





                                // Split into two columns
                                $allDepartments = array_keys($groupedStatuses);
                                $statusChunks = array_chunk($groupedStatuses, ceil(count($groupedStatuses) / 2), true);
                                @endphp

                                <div class="col-md-6">
                                    @foreach($statusChunks[0] as $deptName => $status)
                                    @php
                                    $statusText = $status ? strtoupper($status->status) : 'PENDING';
                                    $statusColor = $status && $status->status == 'cleared' ? 'text-success' :
                                    'text-warning';
                                    @endphp
                                    <div class="clearance-item mb-2">
                                        <span class="department-name">{{ $deptName }}:</span>
                                        <span class="status {{ $statusColor }} font-weight-bold">{{ $statusText
                                            }}</span>
                                        @if($deptName == 'BAO' && $status && $status->status == 'cleared')
                                        <span class="text-muted">OR NO {{ $status->or_number ??
                                            $status->clearance_number ?? '________________' }}</span>
                                        @elseif($deptName == 'BAO')
                                        <span class="text-muted">OR NO _________________</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>

                                @if(isset($statusChunks[1]))
                                <div class="col-md-6">
                                    @foreach($statusChunks[1] as $deptName => $status)
                                    @php
                                    $statusText = $status ? strtoupper($status->status) : 'PENDING';
                                    $statusColor = $status && $status->status == 'cleared' ? 'text-success' :
                                    'text-warning';
                                    @endphp
                                    <div class="clearance-item mb-2">
                                        <span class="department-name">{{ $deptName }}:</span>
                                        <span class="status {{ $statusColor }} font-weight-bold">{{ $statusText
                                            }}</span>
                                        @if($deptName == 'BAO' && $status && $status->status == 'cleared')
                                        <span class="text-muted">OR NO {{ $status->or_number ??
                                            $status->clearance_number ?? '________________' }}</span>
                                        @elseif($deptName == 'BAO')
                                        <span class="text-muted">OR NO _________________</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Show PSG and Governor/UNIWIDE sections based on student type --}}
                                <div class="col-md-12 mt-3">

                                    @php
                                    $isUniwide = $student->is_uniwide == 1;

                                    // Find adviser status
                                    $adviserStatusText = 'PENDING';
                                    $adviserStatusColor = 'text-warning';

                                    $adviserStatus = $clearance->statuses->first(function ($status) {
                                        return $status->approver_role === 'adviser';
                                    });

                                    if ($adviserStatus && $adviserStatus->status === 'cleared') {
                                        $adviserStatusText = 'CLEARED';
                                        $adviserStatusColor = 'text-success';
                                    }
                                    @endphp


                                    {{-- Show PSG row only for non-uniwide students --}}
                                    @if(!$isUniwide)
                                    <div class="clearance-item mb-2">
                                        <span class="department-name">PSG - {{ $student->department->department_code ?? 'N/A' }}:</span>
                                        <span class="{{ $adviserStatusColor }}">
                                            ADVISER: {{ $adviserStatusText }}
                                        </span>
                                    </div>
                                    @endif

                                    {{-- Governor/President row --}}
                                    @php
                                    $govPresStatusText = 'PENDING';
                                    $govPresStatusColor = 'text-warning';

                                    // Define position IDs to match
                                    $governorPositionIds = [1, 2, 3, 4]; // IDs for all governors
                                    $presidentPositionId = 5; // ID for president

                                    $govPresStatus = $clearance->statuses->first(function ($status) use ($isUniwide,
                                    $governorPositionIds, $presidentPositionId) {
                                        if (!$status->approved_by) return false;

                                        $approver = \App\Models\User::find($status->approved_by);
                                        if (!$approver) return false;

                                        $positionId = $approver->position_id;

                                        return $isUniwide
                                            ? $positionId == $presidentPositionId
                                            : in_array($positionId, $governorPositionIds);
                                    });

                                    if ($govPresStatus && $govPresStatus->status === 'cleared') {
                                        $govPresStatusText = 'CLEARED';
                                        $govPresStatusColor = 'text-success';
                                    }
                                    @endphp

                                    <div class="clearance-item mb-2">
                                        <span class="department-name">
                                            {{ $isUniwide ? 'PRESIDENT UNIWIDE' : 'GOVERNOR' }}:
                                        </span>
                                        <span class="status {{ $govPresStatusColor }} font-weight-bold">
                                            {{ $govPresStatusText }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <p class="text-muted">No clearance status records found.</p>
                            </div>
                            @endif
                        </div>

                        @if($clearance->overall_status == 'cleared')
                        <div class="alert alert-success mt-3">
                            <i class="anticon anticon-check-circle"></i>
                            <strong>Congratulations!</strong> Your clearance has been completed for this academic year.
                        </div>

                        @else
                        <div class="alert alert-info mt-3">
                            <i class="anticon anticon-info-circle"></i>
                            <strong>Note:</strong> Please ensure all requirements are submitted to the respective
                            departments to complete your clearance.
                        </div>
                        @endif

                        @else
                        <div class="text-center py-5">
                            <i class="anticon anticon-file-text display-4 text-muted"></i>
                            <h5 class="mt-3">No Clearance Record Found</h5>
                            <p class="text-muted">No clearance record has been created for you in the current academic
                                year.</p>
                            <p class="text-muted">Please contact the registrar's office for assistance.</p>
                        </div>
                        @endif






                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->

@endsection