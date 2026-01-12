<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPUP | Student Information</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/favicon.png')}}">

    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .student-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .student-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .header-section {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 16px 16px 0 0;
            padding: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .back-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .back-btn:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-1px);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 1rem;
        }

        .main-title {
            color: #1e293b;
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin: 0;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #008000;
        }

        .info-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
        }

        .status-card {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .status-card.alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }

        .status-card.alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .status-card.alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }

        .form-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .submit-btn {
            background: #10b981;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .copyright-text {
            color: #64748b;
            font-size: 0.85rem;
            text-align: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .student-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="container-fluid p-0 d-flex align-items-center justify-content-center min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10 col-lg-8 col-xl-7">

                        <!-- Main Card -->
                        <div class="student-container">

                            <!-- Header Section -->
                            <div class="header-section">
                                <a class="back-btn" href="{{route('officer.clearance.clearance-tap-id')}}">
                                    <i class="anticon anticon-caret-left"></i>
                                    Back
                                </a>

                                <!-- Success/Error Messages -->
                                @include('officer.alert.alert_message')
                                @include('officer.alert.alert_danger')

                                <!-- Logo Section -->
                                <div class="logo-section">
                                    <img class="img-fluid" alt="SPUP Logo" src="{{ asset('assets/images/logo/spup-fold.png') }}" width="80">
                                </div>

                                <!-- Main Title -->
                                <h2 class="main-title">Student Information</h2>
                            </div>

                            <!-- Content Section -->
                            <div class="p-4">
                                @if ($clearance)
                                <!-- Student Information Grid -->
                                <div class="student-info-grid">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="anticon anticon-user"></i>
                                            Student Name
                                        </div>
                                        <div class="info-value">
                                            {{ $student->user->firstname }} {{ $student->user->middlename }} {{ $student->user->lastname }}
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                           <i class="anticon anticon-idcard"></i>
                                            Student ID
                                        </div>
                                        <div class="info-value">
                                            {{ $student->student_number }}
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="anticon anticon-profile"></i>
                                            Course & Year
                                        </div>
                                        <div class="info-value">
                                            {{ $student->courses->course_name }} | {{ $student->year }} Year
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-building me-1"></i>
                                            Department
                                        </div>
                                        <div class="info-value">
                                            {{ $student->department->department_name }}
                                        </div>
                                    </div>
                                </div>
                                @php
                                // Define the restricted departments where the warning should be shown (e.g., SITE,
                                $restrictedDepartments = ['SITE', 'SASTE', 'SNAHS', 'SBAHM'];
                                $userDepartmentCode = auth()->user()->department->department_code ?? '';
                                $userDesignationId = auth()->user()->designation_id ?? 0;
                                $isStudentUniwide = $student->is_uniwide ?? 0;

                                // Check if officer can clear this student
                                $canClearStudent = true;
                                $restrictionMessage = '';

                                if ($userDesignationId == 5) {
                                    // UNIWIDE Officer (designation_id = 5) can only clear UNIWIDE students
                                    if ($isStudentUniwide != 1) {
                                        $canClearStudent = false;
                                        $restrictionMessage = 'You can only clear UNIWIDE students as a UNIWIDE officer.';
                                    }
                                } else {
                                    // Governor Officers (designation_id = 1,2,3,4) can only clear non-UNIWIDE students from their department
                                    if ($isStudentUniwide == 1) {
                                        $canClearStudent = false;
                                        $restrictionMessage = 'UNIWIDE students can only be cleared by UNIWIDE officers.';
                                    } elseif (in_array($userDepartmentCode, $restrictedDepartments) &&
                                             auth()->user()->department_id !== $student->department_id) {
                                        $canClearStudent = false;
                                        $restrictionMessage = 'You cannot clear this student as they belong to a different department.';
                                    }
                                }
                                @endphp

                                @if(!$canClearStudent)
                                <!-- Show restriction message -->
                                <div class="status-card alert alert-warning">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>{{ $restrictionMessage }}</span>
                                    </div>
                                </div>
                                @else
                                <!-- If the student belongs to the same department as the logged-in user or is from a different department but not in the restricted list -->
                                <form method="POST" action="{{ route('officer.clearance-status.store') }}" id="clearanceForm">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="clearance_id" value="{{ $clearance->id }}">
                                    <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                                    <input type="hidden" name="approved_by" value="{{ auth()->user()->id }}">
                                    <input type="hidden" name="approver_role" value="{{ auth()->user()->role }}">

                                    @php
                                    $isBaoUser = auth()->user()->department &&
                                    auth()->user()->department->department_code === 'BAO';

                                    // Check if there's an existing status - prioritize controller-passed variable
                                    if (!isset($existingStatus)) {
                                        $existingStatus = null;
                                        if ($clearance && $clearance->statuses) {
                                            foreach ($clearance->statuses as $status) {
                                                if (
                                                    $status->department_id == auth()->user()->department_id &&
                                                    $status->approved_by == auth()->user()->id
                                                ) {
                                                    $existingStatus = $status;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    // Get OR number from existing status
                                    $existingOrNumber = $existingStatus ? $existingStatus->or_number : '';
                                    @endphp

                                    <!-- Current Status Display -->
                                    @if($existingStatus)
                                    <div class="status-card {{ $existingStatus->status == 'cleared' ? 'alert-success' : 'alert-warning' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-{{ $existingStatus->status == 'cleared' ? 'check-circle' : 'clock' }} me-2"></i>
                                            <div>
                                                <div class="fw-bold">Current Status: {{ ucfirst($existingStatus->status) }}</div>
                                                @if($isBaoUser && $existingOrNumber)
                                                <small>OR Number: {{ $existingOrNumber }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="status-card alert-info">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <span>No status set yet</span>
                                        </div>
                                    </div>
                                    @endif

                                    @if($existingStatus && $existingStatus->status == 'cleared')
                                    <!-- Already Cleared Message -->
                                    <div class="status-card alert-success">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span>This student has already been cleared by you.</span>
                                        </div>
                                    </div>
                                    @else
                                    <!-- Form Section -->
                                    <div class="form-section">
                                        @if($isBaoUser)
                                        <div class="mb-3">
                                            <label for="or_number" class="form-label">
                                                <i class="fas fa-receipt me-1"></i>
                                                BAO OR Number
                                            </label>
                                            <input type="text" name="or_number" id="or_number" class="form-input"
                                                placeholder="Enter OR Number" value="{{ $existingOrNumber }}">
                                        </div>
                                        @endif

                                        <button type="submit" name="status" value="cleared" class="submit-btn">
                                            <i class="fas fa-check"></i>
                                            Clear Student
                                        </button>
                                    </div>
                                    @endif
                                </form>
                                @endif

                                @else
                                <!-- No Clearance Record -->
                                <div class="status-card alert-warning">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>This student has no clearance record yet.</span>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>

                        <!-- Copyright -->
                        <div class="copyright-text">
                            <p class="mb-0">
                                Copyright © {{now()->year }} - {{now()->addYear()->year}} St. Paul University Philippines. All Rights Reserved.
                            </p>
                        </div>

                    </div>
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
        function submitForm(status) {
            document.getElementById('status-field').value = status;
            document.getElementById('clearanceForm').submit();
        }
    </script>
    <script>
        function toggleRemarks() {
            document.getElementById('remarks-section').style.display = 'block';
        }
    </script>



</body>

</html>