@extends('officer.layout.header')

@section('main-content')

@php
    $user = Auth::user();
    $isUniwide = $user->designation_id == 5;
    $pageTitle = $isUniwide ? 'UNIWIDE Students' : 'Governor Students';
    $studentType = $isUniwide ? 'UNIWIDE' : 'Governor';
@endphp

<style>
    /* Fix table styling for better visibility */
    #student-table {
        background-color: white !important;
    }

    #student-table tbody tr {
        display: table-row !important;
        visibility: visible !important;
        background-color: white !important;
    }

    #student-table td {
        border: 1px solid #dee2e6 !important;
        padding: 12px !important;
        background-color: white !important;
        color: #495057 !important;
        vertical-align: middle !important;
    }

    #student-table thead th {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        padding: 12px !important;
        color: #495057 !important;
        font-weight: 600 !important;
    }

    /* Fix text colors */
    #student-table .text-primary {
        color: #007bff !important;
    }

    #student-table .text-muted {
        color: #6c757d !important;
    }

    #student-table .badge-success {
        background-color: #28a745 !important;
        color: white !important;
    }

    #student-table .badge-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
</style>

<script>
    // Define variables for JavaScript filtering
    var isRestricted = false; // Officers are not restricted by department for UNIWIDE students
    var employeeDepartmentId = null; // No department restriction for officers viewing UNIWIDE students
</script>
<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">{{ $pageTitle }}</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('officer.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('officer.student.list-of-students') }}">Student</a>
                    <span class="breadcrumb-item active">{{ $pageTitle }}</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('officer.alert.alert_message')
        @include('officer.alert.alert_danger')
        <div class="card">
            <div class="card-body">
               
            
            

                <!-- Mobile-optimized filters -->
              
                
                

                <h4 class="mb-3">{{ $studentType }} Student Personal Information</h4>



                <!-- Mobile-optimized table -->
                <div class="mt-4">
                    <div class="table-responsive">
                        <table id="student-table" class="table table-hover table-striped">
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
                            @php
                                // Debug each student row
                                try {
                                    $studentNumber = $student->student_number ?? 'N/A';
                                    $firstName = $student->user->firstname ?? 'N/A';
                                    $lastName = $student->user->lastname ?? 'N/A';
                                    $middleName = $student->user->middlename ?? '';
                                    $suffixName = $student->user->suffix_name ?? '';
                                    $departmentName = $student->department->department_name ?? 'No Department';
                                    $courseName = $student->courses->course_name ?? 'No Program';
                                    $year = $student->year ?? 'N/A';
                                    $academicYear = $student->AY->academic_year ?? 'N/A';
                                    $semester = $student->AY->semester ?? 'N/A';
                                    $isArchived = $student->is_archived ?? false;
                                } catch (Exception $e) {
                                    echo "Error processing student: " . $e->getMessage();
                                    continue;
                                }
                            @endphp
                            <tr>
                                <!-- Student Info Column (Mobile-first) -->
                                <td>
                                    <div class="student-info-mobile">
                                        <div class="font-weight-bold text-primary">{{ $studentNumber }}</div>
                                        <div class="font-weight-semibold mt-1">
                                            {{ $lastName }}, {{ $firstName }} {{ $middleName }} {{ $suffixName }}
                                        </div>
                                        <div class="d-md-none mt-2">
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-apartment mr-1"></i>{{ $departmentName }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="anticon anticon-book mr-1"></i>{{ $courseName }} - {{ $year }} Year
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge badge-{{ $isArchived ? 'secondary' : 'success' }}">
                                                    {{ $isArchived ? 'Archived' : 'Active' }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="anticon anticon-calendar mr-1"></i>{{ $academicYear }} - {{ $semester }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Department (Hidden on mobile) -->
                                <td data-dept-id="{{ $student->department_id }}" class="d-none d-md-table-cell">
                                    {{ $departmentName }}
                                </td>

                                <!-- Program (Hidden on mobile and small tablets) -->
                                <td data-program-id="{{ $student->course_id }}" class="d-none d-lg-table-cell">
                                    <div>{{ $courseName }}</div>
                                    <small class="text-muted">{{ $year }} Year</small>
                                </td>

                                <!-- Status (Hidden on mobile) -->
                                <td class="d-none d-md-table-cell">
                                    <span class="badge badge-{{ $isArchived ? 'secondary' : 'success' }}">
                                        {{ $isArchived ? 'Archived' : 'Active' }}
                                    </span>
                                </td>

                                <!-- Academic Year (Hidden on mobile and small tablets) -->
                                <td class="d-none d-lg-table-cell">
                                    {{ $academicYear }} - {{ $semester }}
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
    @include('officer.layout.footer')

</div>
<!-- Page Container END -->

<script>
    // Initialize DataTable for UNIWIDE students
    $(document).ready(function() {
        // Wait a moment for the page to fully load
        setTimeout(function() {
            // Destroy any existing DataTable instance
            if ($.fn.DataTable.isDataTable('#student-table')) {
                $('#student-table').DataTable().destroy();
            }

            // Initialize DataTable with proper configuration
            $('#student-table').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [], // No initial sorting
                columnDefs: [
                    { orderable: true, targets: '_all' }
                ],
                language: {
                    emptyTable: "No {{ strtolower($studentType) }} students found",
                    info: "Showing _START_ to _END_ of _TOTAL_ {{ strtolower($studentType) }} students",
                    infoEmpty: "Showing 0 to 0 of 0 {{ strtolower($studentType) }} students"
                }
            });

            console.log("{{ $studentType }} students table initialized successfully");
        }, 100);
    });
</script>

@endsection