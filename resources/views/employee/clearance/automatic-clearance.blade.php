@extends('employee.layout.header')

@section('main-content')

<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Automatic Student Clearance</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('employee.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="">Clearance</a>
                    <span class="breadcrumb-item active">Automatic Clearance</span>
                </nav>
            </div>
        </div>
        
        <!-- Content goes Here -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-6">
                                <h4 class="mb-2 mb-md-0">🔧 Automatic RFID Student Clearance</h4>
                            </div>
                            <div class="col-12 col-md-3 text-md-center">
                                <button type="button" id="clear_nfc_btn" class="btn btn-sm btn-outline-danger">
                                    <i class="anticon anticon-delete"></i> Clear NFC Cache
                                </button>
                            </div>
                            <div class="col-12 col-md-3 text-md-right">
                                <span id="rfid_status" class="badge badge-success badge-lg">Scanning...</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <!-- RFID Scanner Status -->
                        <div class="text-center mb-4">
                            <div id="rfid_scanner_icon" class="mb-3">
                                <i class="anticon anticon-wifi" style="font-size: 64px; color: #28a745; animation: pulse 1.5s infinite;"></i>
                            </div>
                            <div class="alert alert-info">
                                <h5><i class="anticon anticon-info-circle"></i> RFID Scanner Active</h5>
                                <p class="mb-0">Students can tap their RFID cards for instant clearance processing</p>
                            </div>
                        </div>

                        <!-- Student Detection Result -->
                        <div id="student_detection_result" class="mb-4" style="display: none;">
                            <!-- Student information will be displayed here -->
                        </div>

                        <!-- Clearance Processing Form -->
                        <div id="clearance_form_container" style="display: none;">
                            <form id="clearance_form" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" id="form_student_id">
                                <input type="hidden" name="clearance_id" id="form_clearance_id">

                                <!-- OR Number for BAO Department -->
                                @if(auth()->user()->department_id == 14)
                                <div class="form-group mb-3">
                                    <label for="or_number"><strong>BAO:</strong> OR Number</label>
                                    <input type="text" name="or_number" id="or_number" class="form-control"
                                        placeholder="Enter OR Number" required>
                                </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                                        <button type="button" id="clear_btn" class="btn btn-success btn-lg btn-block">
                                            <i class="anticon anticon-check-circle"></i> Clear Student
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <button type="button" id="pending_btn" class="btn btn-warning btn-lg btn-block">
                                            <i class="anticon anticon-clock-circle"></i> Mark as Pending
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Instructions -->
                        <div id="instructions" class="text-center text-muted">
                            <p><i class="anticon anticon-info-circle"></i> Waiting for student to tap RFID card...</p>
                            <small>Students should tap their ID cards on the RFID reader to begin clearance processing</small>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content Wrapper END -->

    @include('employee.layout.footer')

</div>
<!-- Page Container END -->

<style>
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.student-card {
    border: 2px solid #28a745;
    border-radius: 10px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rfidScanningActive = false;
    let rfidScanInterval = null;
    let currentStudent = null;

    // Auto-start RFID scanning when page loads
    startRfidScanning();

    // Clear NFC cache button
    document.getElementById('clear_nfc_btn').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;

        button.innerHTML = '<i class="anticon anticon-loading"></i> Clearing...';
        button.disabled = true;

        fetch('{{ route("employee.clearance.clear-nfc-taps") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear the display
                document.getElementById('student_detection_result').style.display = 'none';
                document.getElementById('clearance_form_container').style.display = 'none';
                document.getElementById('instructions').style.display = 'block';

                // Show success message briefly
                const instructions = document.getElementById('instructions');
                const originalInstructions = instructions.innerHTML;
                instructions.innerHTML = '<p class="text-success"><i class="anticon anticon-check-circle"></i> NFC cache cleared successfully!</p>';

                setTimeout(() => {
                    instructions.innerHTML = originalInstructions;
                }, 2000);
            }

            button.innerHTML = originalText;
            button.disabled = false;
        })
        .catch(error => {
            console.error('Error clearing NFC cache:', error);
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });

    function startRfidScanning() {
        rfidScanningActive = true;
        document.getElementById('rfid_status').textContent = 'Scanning...';
        document.getElementById('rfid_status').className = 'badge badge-success';
        
        // Start polling for RFID taps every 2 seconds
        rfidScanInterval = setInterval(checkForRfidTap, 2000);
    }

    function checkForRfidTap() {
        if (!rfidScanningActive) return;

        fetch('{{ route("employee.clearance.detect-student") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('student_detection_result');
            const formContainer = document.getElementById('clearance_form_container');
            const instructions = document.getElementById('instructions');
            
            if (data.found && data.status === 'detected') {
                currentStudent = data.student;
                
                // Hide instructions
                instructions.style.display = 'none';
                
                // Show student information
                resultDiv.innerHTML = generateStudentCard(data);
                resultDiv.style.display = 'block';
                
                // Show clearance form if employee can clear this student
                if (data.can_clear) {
                    document.getElementById('form_student_id').value = data.student.id;
                    document.getElementById('form_clearance_id').value = data.clearance.id;
                    
                    // Pre-fill OR number if exists
                    if (data.existing_status && data.existing_status.or_number) {
                        const orInput = document.getElementById('or_number');
                        if (orInput) {
                            orInput.value = data.existing_status.or_number;
                        }
                    }
                    
                    formContainer.style.display = 'block';
                } else {
                    formContainer.style.display = 'none';
                }
                
                // Update status
                document.getElementById('rfid_status').textContent = 'Student Detected';
                document.getElementById('rfid_status').className = 'badge badge-primary';
                
            } else {
                // No student detected or error
                if (data.status === 'blocked') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h6><i class="anticon anticon-stop"></i> Student Blocked</h6>
                            <p>${data.message}</p>
                            <div class="student-info mt-3">
                                <p><strong>Student:</strong> ${data.student.name}</p>
                                <p><strong>Student ID:</strong> ${data.student.student_number}</p>
                                <p><strong>Department:</strong> ${data.student.department}</p>
                                <p><strong>Program:</strong> ${data.student.program} | ${data.student.year} Year</p>
                                ${data.student.has_violations ? '<p class="text-danger"><i class="anticon anticon-warning"></i> Has Active Violations</p>' : ''}
                                ${data.student.is_graduated ? '<p class="text-info"><i class="anticon anticon-trophy"></i> Graduated Student</p>' : ''}
                            </div>
                            <small class="text-muted">Contact the registrar or admin to resolve this issue.</small>
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                } else if (data.status === 'unlinked') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h6><i class="anticon anticon-exclamation-triangle"></i> RFID Card Not Linked</h6>
                            <p>${data.message}</p>
                            <p><strong>UID:</strong> ${data.uid}</p>
                            <a href="{{ route('employee.clearance.clearance-tap-id') }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="anticon anticon-link"></i> Link RFID Card
                            </a>
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                } else if (data.status === 'no_clearance') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <h6><i class="anticon anticon-info-circle"></i> No Clearance Record</h6>
                            <p>${data.message}</p>
                            <p><strong>Student:</strong> ${data.student.name} (${data.student.student_number})</p>
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                } else {
                    // Waiting or expired
                    resultDiv.style.display = 'none';
                    instructions.style.display = 'block';
                }
                
                formContainer.style.display = 'none';
                
                // Update status
                document.getElementById('rfid_status').textContent = 'Scanning...';
                document.getElementById('rfid_status').className = 'badge badge-success';
            }
        })
        .catch(error => {
            console.error('RFID Detection Error:', error);
        });
    }

    function generateStudentCard(data) {
        const student = data.student;
        const clearance = data.clearance;
        const existingStatus = data.existing_status;
        const canClear = data.can_clear;
        
        let statusBadge = '';
        if (existingStatus) {
            const badgeClass = existingStatus.status === 'cleared' ? 'badge-success' : 'badge-warning';
            statusBadge = `<span class="badge ${badgeClass} status-badge">${existingStatus.status.toUpperCase()}</span>`;
        } else {
            statusBadge = '<span class="badge badge-secondary status-badge">NO STATUS</span>';
        }
        
        let clearanceInfo = '';
        if (clearance.is_locked) {
            clearanceInfo = `
                <div class="alert alert-danger">
                    <i class="anticon anticon-lock"></i> <strong>Clearance Locked</strong>
                    <br>Reason: ${clearance.lock_reason}
                </div>
            `;
        }
        
        let accessInfo = '';
        if (!canClear) {
            accessInfo = `
                <div class="alert alert-warning">
                    <i class="anticon anticon-warning"></i> You cannot clear this student (different department)
                </div>
            `;
        }
        
        return `
            <div class="student-card p-3 p-md-4">
                <!-- Mobile-optimized student info -->
                <div class="text-center mb-3">
                    <h4 class="mb-2"><i class="anticon anticon-user text-primary"></i> ${student.name}</h4>
                    ${statusBadge}
                </div>

                <div class="student-details">
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Student ID:</strong> ${student.student_number}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Department:</strong> ${student.department}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Program:</strong> ${student.program} | ${student.year} Year
                        </div>
                    </div>

                    ${student.has_violations ? '<div class="row mb-2"><div class="col-12"><span class="text-danger"><i class="anticon anticon-warning"></i> Has Violations</span></div></div>' : ''}
                    ${student.is_graduated ? '<div class="row mb-2"><div class="col-12"><span class="text-info"><i class="anticon anticon-trophy"></i> Graduated</span></div></div>' : ''}

                    ${existingStatus ? `<div class="row mb-2"><div class="col-12"><small class="text-muted">Approved by: ${existingStatus.approved_by}<br>${existingStatus.created_at}</small></div></div>` : ''}
                    ${existingStatus && existingStatus.or_number ? `<div class="row mb-2"><div class="col-12"><strong>OR Number:</strong> ${existingStatus.or_number}</div></div>` : ''}
                </div>

                ${clearanceInfo}
                ${accessInfo}
            </div>
        `;
    }

    // Button click handlers
    document.getElementById('clear_btn').addEventListener('click', function() {
        processClearance('cleared', this);
    });

    document.getElementById('pending_btn').addEventListener('click', function() {
        processClearance('pending', this);
    });

    function processClearance(status, button) {
        const form = document.getElementById('clearance_form');
        const formData = new FormData(form);
        formData.append('status', status);

        // Show loading state
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="anticon anticon-loading"></i> Processing...';
        button.disabled = true;

        fetch('{{ route("employee.clearance.process-automatic") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const resultDiv = document.getElementById('student_detection_result');
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="anticon anticon-check-circle"></i> Success!</h6>
                        <p>${data.message}</p>
                        ${data.completion_percentage ? `<p><strong>Clearance Progress:</strong> ${data.completion_percentage}%</p>` : ''}
                    </div>
                `;

                // Hide form
                document.getElementById('clearance_form_container').style.display = 'none';

                // Reset after 3 seconds to allow for new student
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                // Show error message
                const resultDiv = document.getElementById('student_detection_result');
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="anticon anticon-exclamation-triangle"></i> Error</h6>
                        <p>${data.message}</p>
                    </div>
                `;

                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            // Show error message
            const resultDiv = document.getElementById('student_detection_result');
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="anticon anticon-exclamation-triangle"></i> Error</h6>
                    <p>Failed to process clearance. Please try again.</p>
                </div>
            `;

            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
});
</script>

@endsection
