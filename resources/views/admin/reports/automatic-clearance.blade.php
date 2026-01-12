@extends('admin.layout.header')

@section('main-content')
<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <meta name="csrf-token" content="{{ csrf_token() }}">
<div class="page-header">
    <h2 class="header-title">Automatic RFID Clearance</h2>
    <div class="header-sub-title">
        <nav class="breadcrumb breadcrumb-dash">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
            <a class="breadcrumb-item" href="{{ route('admin.reports.dean-osa') }}">Reports</a>
            <span class="breadcrumb-item active">Automatic RFID Clearance</span>
        </nav>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="anticon anticon-scan"></i> 
                        RFID Clearance Processing
                    </h4>
                </div>
                
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-4">
                        <img class="img-fluid me-0" alt="SPUP Logo" src="{{ asset('assets/images/logo/spup-fold.png') }}" width="100">
                        <img class="img-fluid" src="" width="90" style="margin-left: -25px">
                    </div>

                    <h2 class="m-b-1">TAP STUDENT ID CARD</h2>
                    <p class="text-muted">Students tap their RFID cards for automatic clearance processing</p>

                    <!-- RFID Status -->
                    <div class="text-center mb-4">
                        <span id="rfid_status" class="badge badge-success">Scanning for students...</span>
                        <br><br>
                        <button type="button" id="clear_nfc_btn" class="btn btn-sm btn-outline-danger">
                            <i class="anticon anticon-delete"></i> Clear NFC Cache
                        </button>
                    </div>

                    <!-- Instructions -->
                    <div id="instructions" class="alert alert-info">
                        <h6><i class="anticon anticon-info-circle"></i> Instructions</h6>
                        <p>Ask the student to tap their RFID card on the reader. The system will automatically detect and display their clearance information.</p>
                    </div>

                    <!-- Student Detection Result -->
                    <div id="student_detection_result" class="mb-3" style="display: none;">
                        <!-- Student clearance information will be shown here -->
                    </div>

                    <!-- Clearance Form Container -->
                    <div id="clearance_form_container" style="display: none;">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Process Clearance</h5>
                            </div>
                            <div class="card-body">
                                <form id="clearance_form">
                                    @csrf
                                    <input type="hidden" id="student_id" name="student_id">
                                    <input type="hidden" id="clearance_id" name="clearance_id">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="cleared">Clear</option>
                                                    <option value="pending">Pending</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="or_number">OR Number (Optional)</label>
                                                <input type="text" class="form-control" id="or_number" name="or_number" placeholder="Enter OR number">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="remarks">Remarks (Optional)</label>
                                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks"></textarea>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="anticon anticon-check"></i> Process Clearance
                                        </button>
                                        <button type="button" id="cancel_clearance" class="btn btn-secondary btn-lg ml-2">
                                            <i class="anticon anticon-close"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rfidScanningActive = false;
    let rfidScanInterval = null;
    let currentStudent = null;

    // Auto-start RFID scanning when page loads
    startRfidScanning();

    function startRfidScanning() {
        rfidScanningActive = true;
        document.getElementById('rfid_status').textContent = 'Scanning...';
        document.getElementById('rfid_status').className = 'badge badge-success';
        
        // Start polling for RFID taps every 2 seconds
        rfidScanInterval = setInterval(checkForRfidTap, 2000);
    }

    function checkForRfidTap() {
        if (!rfidScanningActive) return;

        fetch('{{ route("admin.reports.detect-student") }}', {
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
                    document.getElementById('student_id').value = data.student.id;
                    document.getElementById('clearance_id').value = data.clearance.id;
                    formContainer.style.display = 'block';
                } else {
                    formContainer.style.display = 'none';
                }
                
            } else {
                // No student detected or error
                if (data.status === 'unlinked') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h6><i class="anticon anticon-exclamation-triangle"></i> RFID Card Not Linked</h6>
                            <p>${data.message}</p>
                            <p><strong>UID:</strong> ${data.uid}</p>
                            <a href="" target="_blank" class="btn btn-primary btn-sm">
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
                    // Waiting or expired - show "NO NFC card detected" warning
                    resultDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h6><i class="anticon anticon-exclamation-triangle"></i> NO NFC card detected</h6>
                            <p>${data.message || 'Please tap your ID card on the reader.'}</p>
                            <small class="text-muted">Make sure your card is properly positioned on the RFID reader.</small>
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                    instructions.style.display = 'none';
                }

                formContainer.style.display = 'none';

                // Update status
                document.getElementById('rfid_status').textContent = 'NO NFC card detected';
                document.getElementById('rfid_status').className = 'badge badge-warning';
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
        
        let statusBadge = '';
        let statusInfo = '';
        
        if (existingStatus) {
            const badgeClass = existingStatus.status === 'cleared' ? 'badge-success' : 'badge-warning';
            statusBadge = `<span class="badge ${badgeClass}">${existingStatus.status.toUpperCase()}</span>`;
            statusInfo = `
                <div class="mt-2">
                    <small class="text-muted">
                        Processed by: ${existingStatus.approved_by}<br>
                        Date: ${existingStatus.created_at}
                        ${existingStatus.or_number ? `<br>OR Number: ${existingStatus.or_number}` : ''}
                    </small>
                </div>
            `;
        }
        
        let alertClass = 'alert-success';
        let alertIcon = 'anticon-check-circle';
        let alertTitle = 'Student Detected';
        
        if (clearance.is_locked) {
            alertClass = 'alert-danger';
            alertIcon = 'anticon-lock';
            alertTitle = 'Clearance Locked';
        } else if (student.has_violations) {
            alertClass = 'alert-warning';
            alertIcon = 'anticon-exclamation-triangle';
            alertTitle = 'Student Has Violations';
        }
        
        return `
            <div class="alert ${alertClass}">
                <h6><i class="anticon ${alertIcon}"></i> ${alertTitle}</h6>
                <div class="row text-left">
                    <div class="col-md-6">
                        <p><strong>Student Number:</strong> ${student.student_number}</p>
                        <p><strong>Name:</strong> ${student.name}</p>
                        <p><strong>Department:</strong> ${student.department}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Program:</strong> ${student.program}</p>
                        <p><strong>Year:</strong> ${student.year}</p>
                        <p><strong>Status:</strong> ${statusBadge || '<span class="badge badge-secondary">Not Processed</span>'}</p>
                    </div>
                </div>
                ${statusInfo}
                ${clearance.is_locked ? `<div class="mt-2"><small class="text-danger">Reason: ${clearance.lock_reason}</small></div>` : ''}
                ${student.has_violations ? '<div class="mt-2"><small class="text-warning">⚠️ This student has violations that may affect clearance.</small></div>' : ''}
                ${!data.can_clear ? '<div class="mt-2"><small class="text-info">ℹ️ You are not authorized to clear students from this department.</small></div>' : ''}
            </div>
        `;
    }

    // Handle clearance form submission
    document.getElementById('clearance_form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="anticon anticon-loading"></i> Processing...';
        
        fetch('', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Clearance status updated successfully!');
                // Reset form and hide it
                document.getElementById('clearance_form').reset();
                document.getElementById('clearance_form_container').style.display = 'none';
                document.getElementById('student_detection_result').style.display = 'none';
                document.getElementById('instructions').style.display = 'block';
                currentStudent = null;
            } else {
                alert('Error: ' + (data.message || 'Failed to update clearance status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the clearance');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="anticon anticon-check"></i> Process Clearance';
        });
    });

    // Handle cancel button
    document.getElementById('cancel_clearance').addEventListener('click', function() {
        document.getElementById('clearance_form').reset();
        document.getElementById('clearance_form_container').style.display = 'none';
        document.getElementById('student_detection_result').style.display = 'none';
        document.getElementById('instructions').style.display = 'block';
        currentStudent = null;
    });

    // Clear NFC cache button
    document.getElementById('clear_nfc_btn').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;

        button.innerHTML = '<i class="anticon anticon-loading"></i> Clearing...';
        button.disabled = true;

        fetch('/admin/clearance/clear-nfc-taps', {
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
                const resultDiv = document.getElementById('student_detection_result');
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="anticon anticon-check-circle"></i> NFC cache cleared successfully!
                    </div>
                `;
                resultDiv.style.display = 'block';

                setTimeout(() => {
                    resultDiv.style.display = 'none';
                    document.getElementById('instructions').style.display = 'block';
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
});
</script>
    </div>
    <!-- Content Wrapper END -->
</div>
<!-- Page Container END -->
@endsection
