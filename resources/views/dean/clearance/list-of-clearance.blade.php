@extends('dean.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Clearance Management</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="">Clearance</a>
                    <span class="breadcrumb-item active">Clearance Status</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('dean.alert.alert_message')
        @include('dean.alert.alert_danger')
        <!-- Enhanced clearance list -->
        <div class="card">
        <br>
            <div class="card-header bg-transparent border-0 pb-0">
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-3 mb-md-0">
                      
                        <p class="text-muted mb-0">Monitor and track clearance progress for all students</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                            <i class="anticon anticon-reload mr-2"></i>Refresh
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="anticon anticon-printer mr-2"></i>Print Report
                        </button>
                    </div>
                </div>
            </div>
           <div class="card-body pt-3">
                <!-- Summary Statistics -->
                @php
                    $totalClearances = count($clearances);
                    $fullyCleared = $clearances->filter(function($clearance) {
                        return $clearance->isFullyCleared();
                    })->count();
                    $locked = $clearances->filter(function($clearance) {
                        return $clearance->is_locked;
                    })->count();
                    $inProgress = $clearances->filter(function($clearance) {
                        return !$clearance->isFullyCleared() && !$clearance->is_locked && $clearance->getCompletionPercentage() > 0;
                    })->count();
                    $pending = $totalClearances - $fullyCleared - $locked - $inProgress;
                @endphp

                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="text-center">
                            <h5 class="mb-1 font-weight-bold text-primary">{{ $totalClearances }}</h5>
                            <small class="text-muted">Total Students</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="text-center">
                            <h5 class="mb-1 font-weight-bold text-success">{{ $fullyCleared }}</h5>
                            <small class="text-muted">Fully Cleared</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="text-center">
                            <h5 class="mb-1 font-weight-bold text-warning">{{ $inProgress }}</h5>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="text-center">
                            <h5 class="mb-1 font-weight-bold text-danger">{{ $locked }}</h5>
                            <small class="text-muted">Locked</small>
                        </div>
                    </div>
                </div>

                <!-- Enhanced table -->
                <div class="table-responsive">
                    <table id="data-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-nowrap">Student Information</th>
                                <th class="d-none d-md-table-cell text-nowrap">Department</th>
                                <th class="d-none d-lg-table-cell text-nowrap">Academic Year</th>
                                <th class="text-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clearances as $clearance)
                            <tr>
                                <!-- Student Information (Mobile-first) -->
                                <td>
                                    <div class="student-info-mobile">
                                        <div class="font-weight-bold text-primary">
                                            {{ $clearance->student->student_number ?? 'N/A' }}
                                        </div>
                                        <div class="font-weight-semibold mt-1">
                                            {{ $clearance->student->user->lastname }} {{ $clearance->student->user->firstname }} {{ $clearance->student->user->middlename }}
                                        </div>
                                        <div class="d-md-none mt-2">
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-apartment mr-1"></i>{{ $clearance->department->department_name }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-calendar mr-1"></i>{{ $clearance->academicYear->academic_year }}
                                            </small>
                                            <div class="mt-2">
                                                @php
                                                    $isFullyCleared = $clearance->isFullyCleared();
                                                    $completionPercentage = $clearance->getCompletionPercentage();

                                                    if ($clearance->is_locked) {
                                                        $statusClass = 'badge-danger';
                                                        $statusIcon = 'anticon-lock';
                                                        $statusText = 'Locked';
                                                    } elseif ($isFullyCleared) {
                                                        $statusClass = 'badge-success';
                                                        $statusIcon = 'anticon-check';
                                                        $statusText = 'Cleared';
                                                    } elseif ($completionPercentage > 0) {
                                                        $statusClass = 'badge-warning';
                                                        $statusIcon = 'anticon-clock-circle';
                                                        $statusText = 'In Progress';
                                                    } else {
                                                        $statusClass = 'badge-secondary';
                                                        $statusIcon = 'anticon-minus-circle';
                                                        $statusText = 'Pending';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    <i class="{{ $statusIcon }} mr-1"></i>{{ $statusText }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Department (Hidden on mobile) -->
                                <td class="d-none d-md-table-cell">
                                    {{ $clearance->department->department_name }}
                                </td>

                                <!-- Academic Year (Hidden on mobile and small tablets) -->
                                <td class="d-none d-lg-table-cell">
                                    {{ $clearance->academicYear->academic_year }}
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        // Check if clearance is fully cleared
                                        $isFullyCleared = $clearance->isFullyCleared();

                                        // Get completion percentage
                                        $completionPercentage = $clearance->getCompletionPercentage();

                                        // Determine status
                                        if ($clearance->is_locked) {
                                            $statusClass = 'badge-danger';
                                            $statusIcon = 'anticon-lock';
                                            $statusText = 'Locked';
                                        } elseif ($isFullyCleared) {
                                            $statusClass = 'badge-success';
                                            $statusIcon = 'anticon-check';
                                            $statusText = 'Cleared';
                                        } elseif ($completionPercentage > 0) {
                                            $statusClass = 'badge-warning';
                                            $statusIcon = 'anticon-clock-circle';
                                            $statusText = 'In Progress';
                                        } else {
                                            $statusClass = 'badge-secondary';
                                            $statusIcon = 'anticon-minus-circle';
                                            $statusText = 'Pending';
                                        }
                                    @endphp

                                    <span class="badge {{ $statusClass }}">
                                        <i class="{{ $statusIcon }} mr-1"></i>{{ $statusText }}
                                    </span>

                                    @if($clearance->is_locked)
                                        <div class="mt-1">
                                            <small class="text-muted">{{ $clearance->lock_reason }}</small>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="d-none d-lg-table-footer-group">
                            <tr>
                                <th>Student Information</th>
                                <th class="d-none d-md-table-cell">Department</th>
                                <th class="d-none d-lg-table-cell">Academic Year</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- Content Wrapper END -->
        @include('dean.layout.footer')

    </div>
    <!-- Page Container END -->



    @endsection