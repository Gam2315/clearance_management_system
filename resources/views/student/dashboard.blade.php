@extends('student.layout.header')

@section('main-content')


 <!-- Page Container START -->
 <div class="page-container">
                
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header no-gutters">
            <div class="d-md-flex align-items-md-center justify-content-between">
                <div class="media m-v-10 align-items-center">
                    <div class="avatar avatar-image avatar-lg">
                        <img src="{{ asset($user->picture ?? 'assets/images/avatars/profile-picture.png') }}"
                            alt="User Avatar">
                    </div>
                    <div class="media-body m-l-15">
                        <h4 class="m-b-0">Welcome back, {{ $user->firstname .' '. $user->lastname }}!</h4>
                        <span class="text-gray">{{ ucfirst($user->role) }}</span>
                        @if($student)
                            <br><small class="text-muted">Student Number: {{ $student->student_number }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Content goes Here -->
        <div class="row">
            <!-- Student Information Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Student Information</h4>
                    </div>
                    <div class="card-body">
                        @if($student)
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>Student Number:</strong><br>{{ $student->student_number }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>Year Level:</strong><br>{{ $student->year }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>Department:</strong><br>{{ $student->department->department_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>Course:</strong><br>{{ $student->courses->course_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No student record found.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Clearance Status Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Clearance Status</h4>
                    </div>
                    <div class="card-body">
                        @if($clearance)
                            <div class="clearance-status">
                                <p><strong>Overall Status:</strong>
                                    <span class="badge badge-{{ $clearance->overall_status == 'cleared' ? 'success' : 'warning' }}">
                                        {{ ucfirst($clearance->overall_status) }}
                                    </span>
                                </p>
                                <p><strong>Academic Year:</strong> {{ $clearance->academicYear->academic_year ?? 'N/A' }}</p>
                                <p><strong>Semester:</strong> {{ $clearance->academicYear->semester ?? 'N/A' }}</p>

                                <div class="mt-3">
                                    <a href="{{ route('student.clearance') }}" class="btn btn-primary">
                                        <i class="anticon anticon-file-text"></i> View Full Clearance
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No clearance record found for this academic year.</p>
                            <div class="mt-3">
                                <a href="{{ route('student.clearance') }}" class="btn btn-primary">
                                    <i class="anticon anticon-file-text"></i> View Clearance
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content Wrapper END -->

    @include('student.layout.footer')

</div>
<!-- Page Container END -->

@endsection