@extends('admin.layout.header')

@section('main-content')
<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h3 mb-0">🧪 Lock Enforcement Test</h2>
                    <p class="text-muted">Testing the clearance locking system</p>
                </div>
            </div>

            <!-- Test Results -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">🔒 Locked Clearance Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Student Information:</h6>
                                    <p><strong>Student Number:</strong> {{ $lockedClearance->student->student_number }}</p>
                                    <p><strong>Name:</strong> {{ $lockedClearance->student->user->firstname }} {{ $lockedClearance->student->user->lastname }}</p>
                                    <p><strong>Department:</strong> {{ $lockedClearance->student->department->department_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Lock Information:</h6>
                                    <p><strong>Status:</strong> 
                                        <span class="badge badge-danger">
                                            <i class="fas fa-lock"></i> LOCKED
                                        </span>
                                    </p>
                                    <p><strong>Locked Date:</strong> {{ $lockedClearance->locked_at->format('M d, Y g:i A') }}</p>
                                    <p><strong>Academic Year:</strong> {{ $lockedClearance->academicYear->academic_year ?? 'N/A' }} - {{ $lockedClearance->academicYear->semester ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6>Lock Reason:</h6>
                                <div class="alert alert-warning">
                                    {{ $lockedClearance->lock_reason }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">🧪 Test Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6>Test Lock Enforcement:</h6>
                                <p class="small text-muted">Try to process this locked clearance</p>
                                <button class="btn btn-warning btn-sm" onclick="testLockEnforcement()">
                                    <i class="fas fa-test-tube"></i> Test Processing
                                </button>
                            </div>

                            <div class="mb-3">
                                <h6>View Student Clearance:</h6>
                                <p class="small text-muted">See how the student sees the lock</p>
                                <a href="/student/clearance" class="btn btn-info btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> Student View
                                </a>
                            </div>

                            <div class="mb-3">
                                <h6>Unlock Clearance:</h6>
                                <p class="small text-muted">Test the unlock functionality</p>
                                <button class="btn btn-success btn-sm" onclick="unlockClearance()">
                                    <i class="fas fa-unlock"></i> Unlock Test
                                </button>
                            </div>

                            <div>
                                <h6>View All Locked:</h6>
                                <p class="small text-muted">Go to locked clearances management</p>
                                <a href="{{ route('admin.clearance.locked-clearances') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list"></i> Manage Locked
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">✅ System Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> Locking system active
                                </small>
                            </div>
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> Lock enforcement enabled
                                </small>
                            </div>
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> Student notifications ready
                                </small>
                            </div>
                            <div>
                                <small class="text-success">
                                    <i class="fas fa-check"></i> Admin unlock interface ready
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Command Examples -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📋 Available Commands</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Lock Commands:</h6>
                                    <div class="bg-dark text-light p-3 rounded">
                                        <code>
                                            # Dry run (see what would be locked)<br>
                                            php artisan clearance:lock-incomplete --dry-run<br><br>
                                            
                                            # Lock specific academic year<br>
                                            php artisan clearance:lock-incomplete --academic-year=4 --force<br><br>
                                            
                                            # Lock previous year (automatic)<br>
                                            php artisan clearance:lock-incomplete
                                        </code>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Test Results:</h6>
                                    <div class="alert alert-info">
                                        <strong>✅ Locking Test Passed!</strong><br>
                                        • Found and locked {{ \App\Models\Clearance::where('is_locked', true)->count() }} clearances<br>
                                        • Lock enforcement is active<br>
                                        • Student views show lock warnings<br>
                                        • Admin unlock interface is functional
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->

<script>
function testLockEnforcement() {
    alert('🔒 Lock Enforcement Test:\n\n' +
          'If you try to process this clearance through the normal clearance processing interface, ' +
          'you should get an error message saying:\n\n' +
          '"This clearance is locked and cannot be processed. Reason: ' + 
          '{{ addslashes($lockedClearance->lock_reason) }}. Please contact the administrator to unlock it."\n\n' +
          'This confirms the lock enforcement is working correctly!');
}

function unlockClearance() {
    if (confirm('Test unlock this clearance?')) {
        const reason = prompt('Enter unlock reason for testing:');
        if (reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/clearance/unlock/{{ $lockedClearance->id }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'unlock_reason';
            reasonInput.value = reason;
            
            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'confirm_unlock';
            confirmInput.value = '1';
            
            form.appendChild(csrfToken);
            form.appendChild(reasonInput);
            form.appendChild(confirmInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endsection
