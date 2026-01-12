<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPUP | RFID READER</title>
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

        .clearance-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .clearance-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .logo-section {
            text-align: center;
            padding: 2rem 0 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .main-title {
            color: #1e293b;
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin: 1.5rem 0;
        }

        .nfc-status-card {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .nfc-status-card.alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .nfc-status-card.alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }

        .nfc-status-card.alert-danger {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
        }

        .nfc-status-icon {
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .form-section {
            padding: 1rem 0;
        }

        .horizontal-form {
            display: flex;
            gap: 1rem;
            align-items: end;
        }

        .input-group {
            flex: 1;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .student-input {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #ffffff;
            width: 100%;
        }

        .student-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .submit-btn {
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .submit-btn:hover:not(:disabled) {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .copyright-text {
            color: #64748b;
            font-size: 0.85rem;
            text-align: center;
            margin-top: 2rem;
        }

        .is-valid {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }

        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        @media (max-width: 768px) {
            .horizontal-form {
                flex-direction: column;
                gap: 1rem;
            }

            .submit-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="container-fluid p-0 d-flex align-items-center justify-content-center min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10 col-lg-8 col-xl-6">

                        <!-- Success/Error Messages -->
                        @include('adviser.alert.alert_message')
                        @include('adviser.alert.alert_danger')

                        <!-- Main Card -->
                        <div class="clearance-container">
                            <div class="p-4">

                                <!-- Logo Section -->
                                <div class="logo-section">
                                    <img class="img-fluid" alt="SPUP Logo" src="{{ asset('assets/images/logo/spup-fold.png') }}" width="80">
                                </div>

                                <!-- Main Title -->
                                <h2 class="main-title">Student ID Verification</h2>

                                <!-- NFC Status Indicator -->
                                <div class="nfc-status-card alert alert-info" id="nfc_status">
                                    <div class="d-flex align-items-center">
                                        <i class="anticon anticon-wifi"></i>
                                        <span id="nfc_status_text">Checking for NFC card...</span>
                                    </div>
                                </div>

                                <!-- Form Section -->
                                <div class="form-section">
                                    <form method="POST" action="{{ route('adviser.link.nfc') }}" id="student_form">
                                        @csrf
                                        <div class="horizontal-form">
                                            <div class="input-group">
                                                <label class="form-label">Student ID Number</label>
                                                <input class="student-input"
                                                       type="text"
                                                       name="student_number"
                                                       required
                                                       placeholder="Enter Student ID"
                                                       value="{{ old('student_number') }}"
                                                       id="student_number_input"
                                                       autocomplete="off">
                                            </div>
                                            <button type="submit" class="submit-btn" id="submit_btn" disabled>
                                                Submit
                                            </button>
                                        </div>
                                    </form>
                                </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('student_form');
            const submitBtn = document.getElementById('submit_btn');
            const studentInput = document.getElementById('student_number_input');
            const nfcStatus = document.getElementById('nfc_status');
            const nfcStatusText = document.getElementById('nfc_status_text');

            let nfcCardDetected = false;
            let checkingInterval;

            // Function to check for NFC card
            function checkNfcCard() {
                fetch('/api/nfc-check', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.nfc_detected) {
                        nfcCardDetected = true;
                        nfcStatus.className = 'nfc-status-card alert alert-success';
                        nfcStatusText.textContent = 'NFC card detected! Ready to proceed.';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit';
                        clearInterval(checkingInterval);
                    } else {
                        nfcCardDetected = false;
                        nfcStatus.className = 'nfc-status-card alert alert-warning';
                        nfcStatusText.textContent = 'Please tap your NFC ID card on the reader.';
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Waiting for NFC Card';
                    }
                })
                .catch(error => {
                    console.error('Error checking NFC:', error);
                    nfcStatus.className = 'nfc-status-card alert alert-danger';
                    nfcStatusText.textContent = 'Unable to connect to NFC reader.';
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Connection Error';
                });
            }

            // Start checking for NFC card every 2 seconds
            checkingInterval = setInterval(checkNfcCard, 2000);
            checkNfcCard(); // Initial check

            // Add form submission handler
            form.addEventListener('submit', function(e) {
                if (!nfcCardDetected) {
                    e.preventDefault();
                    alert('Please tap your NFC card first before submitting!');
                    return false;
                }

                // Prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

                // Re-enable after 5 seconds in case of error
                setTimeout(() => {
                    if (!nfcCardDetected) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Waiting for NFC Card';
                    }
                }, 5000);
            });

            // Focus on input when page loads
            studentInput.focus();

            // Add input formatting and validation
            studentInput.addEventListener('input', function(e) {
                // Remove any non-alphanumeric characters
                this.value = this.value.replace(/[^a-zA-Z0-9-]/g, '');

                // Convert to uppercase
                this.value = this.value.toUpperCase();

                // Add visual feedback for valid input
                if (this.value.length >= 6) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-valid');
                    if (this.value.length > 0) {
                        this.classList.add('is-invalid');
                    }
                }
            });

            // Allow Enter key to submit
            studentInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (nfcCardDetected && this.value.length >= 6) {
                        form.submit();
                    } else if (!nfcCardDetected) {
                        alert('Please tap your NFC card first!');
                    } else {
                        alert('Please enter a valid Student ID!');
                    }
                }
            });
        });
    </script>
    
  

</body>

</html>
