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
                                <!-- Success/Error Messages -->
                                @include('student.alert.alert_message')
                                @include('student.alert.alert_danger')

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
                                <!-- Student Clearance Status Display -->
                                <div class="status-card alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span>This is a read-only view of student information.</span>
                                    </div>
                                </div>
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

    <!-- Core JS -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>

</body>
</html>
                                                    Current status: <strong>{{ ucfirst($existingStatus->status) }}</strong>
                                                    @if($isBaoUser && $existingOrNumber)
                                                    <br>OR Number: <strong>{{ $existingOrNumber }}</strong>
                                                    @endif
                                                @endif
                                            </div>
                                            @else
                                            <div class="alert alert-info">
                                                @if($isPsgUser)
                                                    PSG: <strong>Pending</strong> : ADVISER: ___________
                                                @else
                                                    No status set yet
                                                @endif
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
                                                    class="btn btn-success btn-tone m-r-5">Submit</button>
                                            @endif
                                        </div>
                                    </form>
                                    @endif

                                    @if(!$clearance)
                                    <div class="alert alert-warning">
                                        <i class="anticon anticon-warning"></i>
                                        <span class="m-l-10">This student has no clearance record yet.</span>
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