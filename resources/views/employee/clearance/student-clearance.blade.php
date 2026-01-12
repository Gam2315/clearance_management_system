<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPUP | Student Clearance</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/favicon.png')}}">

    <!-- page css -->

    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">

</head>

<body>
    <div class="app">
        <div class="container-fluid p-h-0 p-v-20 bg full-height d-flex">
            <div class="d-flex flex-column justify-content-between w-100">
                <div class="container d-flex h-100">

                    <div class="row align-items-center w-100">
                        <div class="col-md-7 col-lg-5 m-h-auto">
                            @include('employee.alert.alert_message')
                            @include('employee.alert.alert_danger')

                            <!-- Debug information -->
                            @if(config('app.debug'))
                            <div class="alert alert-info">
                                <strong>Debug Info:</strong><br>
                                Student ID: {{ $student->id ?? 'N/A' }}<br>
                                Clearance ID: {{ $clearance->id ?? 'N/A' }}<br>
                                User Dept ID: {{ auth()->user()->department_id ?? 'N/A' }}<br>
                                User ID: {{ auth()->user()->id ?? 'N/A' }}<br>
                                Existing Status: {{ $existingStatus->status ?? 'None' }}
                            </div>
                            @endif
                            <div class="card shadow-lg">

                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <img class="img-fluid me-0" alt="SPUP Logo"
                                            src="{{ asset('assets/images/logo/spup-fold.png') }}" width="100">
                                        <img class="img-fluid" src="" width="90" style="margin-left: -25px">
                                    </div>

                                    @if ($clearance)
                                    <h2>Student Information</h2>
                                    <div class="student-info mb-4">
                                        <p><strong>Student Name:</strong> {{ $student->user->lastname }}, {{
                                            $student->user->firstname }} {{ $student->user->middlename }}</p>
                                        <p><strong>Student ID:</strong> {{ $student->student_number }}</p>
                                        <p><strong>Course & Year:</strong> {{ $student->courses->course_name }} | {{
                                            $student->year }} Year</p>
                                        <p><strong>Department:</strong> {{ $student->department->department_name }}</p>
                                    </div>

                                    @php
                                    // Define restricted departments that can only clear their own students
                                    $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS, SBAHM
                                    $currentUserDeptId = auth()->user()->department_id;

                                    // Check if user can clear this student
                                    $canClearStudent = true;
                                    if (in_array($currentUserDeptId, $restrictedDepartments)) {
                                        // Restricted departments can only clear students from their own department
                                        $canClearStudent = ($currentUserDeptId === $student->department_id);
                                    }
                                    // Other departments (like OSA, Library, BAO) can clear all students
                                    @endphp

                                    @if($canClearStudent)
                                        <form method="POST" action="{{ route('employee.clearance-status.store') }}" id="clearanceForm">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <input type="hidden" name="clearance_id" value="{{ $clearance->id }}">
                                            <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                                            <input type="hidden" name="approved_by" value="{{ auth()->user()->id }}">

                                            @php
                                            $isBaoUser = auth()->user()->department &&
                                            auth()->user()->department->department_code === 'BAO';

                                            // Check if there's an existing status
                                            $existingOrNumber = '';

                                            if ($existingStatus) {
                                                $existingOrNumber = $existingStatus->or_number;
                                            }
                                            @endphp

                                            <div class="current-status mb-3">
                                                @if($existingStatus)
                                                <div
                                                    class="alert {{ $existingStatus->status == 'cleared' ? 'alert-success' : 'alert-warning' }}">
                                                    Current status: <strong>{{ ucfirst($existingStatus->status) }}</strong>
                                                    @if($isBaoUser && $existingOrNumber)
                                                    <br>OR Number: <strong>{{ $existingOrNumber }}</strong>
                                                    @endif
                                                </div>
                                                @else
                                                <div class="alert alert-info">
                                                    No status set yet
                                                </div>
                                                @endif
                                            </div>

                                            @if($isBaoUser)
                                            <div class="form-group mb-3">
                                                <label for="or_number"><strong>BAO:</strong> OR Number</label>
                                                <input type="text" name="or_number" id="or_number" class="form-control"
                                                    placeholder="Enter OR Number" value="{{ $existingOrNumber }}">
                                            </div>
                                            @endif

                                            <div class="d-flex">
                                                @if($existingStatus && $existingStatus->status == 'cleared')
                                                    <div class="alert alert-info">
                                                        <i class="anticon anticon-check-circle"></i>
                                                        <span class="m-l-10">This student has already been cleared by your department.</span>
                                                    </div>
                                                @else
                                                    <button type="submit" name="status" value="cleared"
                                                        class="btn btn-success btn-tone m-r-5">Clear Student</button>
                                                @endif
                                            </div>
                                        </form>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="anticon anticon-warning"></i>
                                            <span class="m-l-10">You cannot clear this student as they belong to a different department.</span>
                                        </div>
                                    @endif

                                    @else
                                    <div class="alert alert-warning">
                                        <i class="anticon anticon-warning"></i>
                                        <span class="m-l-10">This student has no clearance record yet.</span>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-none d-md-flex p-h-40 justify-content-between">
                    <p class="m-b-0">Copyright © {{now()->year }} - {{now()->addYear()->year}} St. Paul University
                        Philippines. All Rights Reserved.</p>

                </div>
            </div>
        </div>
    </div>


    <!-- Core Vendors JS -->
    <script src="{{asset('assets/js/vendors.min.js')}}"></script>

    <!-- page js -->

    <!-- Core JS -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('clearanceForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Debug: Log form data
                    const formData = new FormData(form);
                    console.log('Form submission data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }

                    const submitBtn = e.submitter;
                    if (submitBtn) {
                        console.log('Submit button value:', submitBtn.value);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

                        // Re-enable after 5 seconds in case of error
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = submitBtn.value === 'cleared' ? 'Clear Student' : 'Re-Clear Student';
                        }, 5000);
                    }
                });
            }
        });
    </script>

</body>

</html>
