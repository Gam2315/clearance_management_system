@extends('employee.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Students</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('employee.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('employee.student.list-of-students') }}">Student</a>
                    <span class="breadcrumb-item active">List of Students</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('employee.alert.alert_message')
        @include('employee.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                @php
                    $user = Auth::user();
                    $employeeDepartmentId = $user->department_id;
                    $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments
                    $isRestricted = in_array($employeeDepartmentId, $restrictedDepartments);
                @endphp

                <!-- Mobile-optimized filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="row">
                            @if(!$isRestricted)
                            <div class="col-12 col-md-6 mb-3">
                                <label for="filter-department" class="form-label">Select Department</label>
                                <select id="filter-department" class="form-control custom-select">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-12 {{ $isRestricted ? 'col-md-12' : 'col-md-6' }} mb-3">
                                <label for="filter-program" class="form-label">Select Program</label>
                                <select id="filter-program" class="form-control custom-select" {{ $isRestricted ? '' : 'disabled' }}>
                                    <option value="">Select Program</option>
                                    @if($isRestricted)
                                        @foreach($programs->where('department_id', $employeeDepartmentId) as $program)
                                            <option value="{{ $program->id }}">{{ $program->course_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3">Student Personal Information</h4>
                <!-- Mobile-optimized table -->
                <div class="mt-4">
                    <div class="table-responsive">
                        <table id="data-table" class="table table-hover table-white">
                            <thead class="thead-white">
                                <tr>
                                    <th class="text-nowrap">Student Info</th>
                                    <th class="d-none d-md-table-cell text-nowrap">Department</th>
                                    <th class="d-none d-lg-table-cell text-nowrap">Program</th>
                                    <th class="d-none d-md-table-cell text-nowrap">Status</th>
                                    <th class="d-none d-lg-table-cell text-nowrap">Academic Year</th>
                                </tr>
                            </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <!-- Student Info Column (Mobile-first) -->
                                <td>
                                    <div class="student-info-mobile">
                                        <div class="font-weight-bold text-primary">{{ $student->student_number }}</div>
                                        <div class="font-weight-semibold mt-1">
                                            {{ $student->user->lastname }}, {{ $student->user->firstname }} {{ $student->user->middlename }} {{ $student->user->suffix_name }}
                                        </div>
                                        <div class="d-md-none mt-2">
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-apartment mr-1"></i>{{ $student->department->department_name ?? 'No Department' }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-book mr-1"></i>{{ $student->courses->course_name ?? 'No Program' }} - {{ $student->year }} Year
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge badge-{{ $student->status == 'Active' ? 'success' : 'secondary' }}">
                                                    {{ $student->status }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="anticon anticon-calendar mr-1"></i>{{ $student->AY->academic_year }} - {{ $student->AY->semester }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Department (Hidden on mobile) -->
                                <td data-dept-id="{{ $student->department_id }}" class="d-none d-md-table-cell">
                                    {{ $student->department->department_name ?? 'No Department' }}
                                </td>

                                <!-- Program (Hidden on mobile and small tablets) -->
                                <td data-program-id="{{ $student->course_id }}" class="d-none d-lg-table-cell">
                                    <div>{{ $student->courses->course_name ?? 'No Program' }}</div>
                                    <small class="text-muted">{{ $student->year }} Year</small>
                                </td>

                                <!-- Status (Hidden on mobile) -->
                                <td class="d-none d-md-table-cell">
                                    <span class="badge badge-{{ $student->status == 'Active' ? 'success' : 'secondary' }}">
                                        {{ $student->status }}
                                    </span>
                                </td>

                                <!-- Academic Year (Hidden on mobile and small tablets) -->
                                <td class="d-none d-lg-table-cell">
                                    {{ $student->AY->academic_year }} - {{ $student->AY->semester }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="d-none d-lg-table-footer-group">
                            <tr>
                                <th>Student Info</th>
                                <th class="d-none d-md-table-cell">Department</th>
                                <th class="d-none d-lg-table-cell">Program</th>
                                <th class="d-none d-md-table-cell">Status</th>
                                <th class="d-none d-lg-table-cell">Academic Year</th>
                            </tr>
                        </tfoot>

                    </table>

                </div>

            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('employee.layout.footer')
  
</div>
<!-- Page Container END -->

@endsection


