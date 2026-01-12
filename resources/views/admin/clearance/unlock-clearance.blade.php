@extends('admin.layout.header')

@section('main-content')
<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Alert Messages -->
            @include('admin.alert.alert_message')
            @include('admin.alert.alert_danger')

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h3 mb-0">🔓 Unlock Clearance</h2>
                            <p class="text-muted">Review and unlock student clearance</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.clearance.locked-clearances') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Locked Clearances
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">👤 Student Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Student Number:</strong></p>
                                    <p class="text-primary">{{ $clearance->student->student_number }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Name:</strong></p>
                                    <p>{{ $clearance->student->user->firstname }} {{ $clearance->student->user->lastname }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Department:</strong></p>
                                    <p>{{ $clearance->student->department->department_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Year Level:</strong></p>
                                    <p>{{ $clearance->student->year ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">🔒 Lock Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p><strong>Academic Year:</strong></p>
                                <p>{{ $clearance->academicYear->academic_year ?? 'N/A' }} - {{ $clearance->academicYear->semester ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <p><strong>Locked Date:</strong></p>
                                <p>{{ $clearance->locked_at ? $clearance->locked_at->format('M d, Y g:i A') : 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <p><strong>Locked By:</strong></p>
                                <p>{{ $clearance->locker->firstname ?? 'System' }} {{ $clearance->locker->lastname ?? '' }}</p>
                            </div>
                            <div>
                                <p><strong>Lock Reason:</strong></p>
                                <div class="alert alert-warning">
                                    {{ $clearance->lock_reason }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clearance Status -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📋 Clearance Status Overview</h5>
                        </div>
                        <div class="card-body">
                            @if($clearance->statuses && $clearance->statuses->count() > 0)
                                <div class="row">
                                    @foreach($clearance->statuses as $status)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-left-{{ $status->status === 'cleared' ? 'success' : 'warning' }}">
                                                <div class="card-body py-2">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1">{{ $status->department->department_name ?? 'N/A' }}</h6>
                                                            <small class="text-muted">{{ $status->department->department_code ?? 'N/A' }}</small>
                                                        </div>
                                                        <div>
                                                            <span class="badge badge-{{ $status->status === 'cleared' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($status->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-3">
                                    @php
                                        $totalDepartments = $clearance->statuses->count();
                                        $clearedDepartments = $clearance->statuses->where('status', 'cleared')->count();
                                        $completionPercentage = $totalDepartments > 0 ? round(($clearedDepartments / $totalDepartments) * 100, 2) : 0;
                                    @endphp
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $completionPercentage == 100 ? 'success' : 'warning' }}" 
                                             style="width: {{ $completionPercentage }}%">
                                            {{ $completionPercentage }}% Complete ({{ $clearedDepartments }}/{{ $totalDepartments }})
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-muted">No clearance status records found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unlock Form -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">🔓 Unlock Clearance</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.clearance.unlock', $clearance->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="unlock_reason">Reason for Unlocking <span class="text-danger">*</span></label>
                                    <textarea name="unlock_reason" id="unlock_reason" class="form-control @error('unlock_reason') is-invalid @enderror" rows="4" required
                                              placeholder="Enter the detailed reason for unlocking this clearance...">{{ old('unlock_reason') }}</textarea>
                                    @error('unlock_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        This reason will be logged for audit purposes. Be specific about why the unlock is being granted.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="confirm_unlock" name="confirm_unlock" required>
                                        <label class="custom-control-label" for="confirm_unlock">
                                            I confirm that I want to unlock this clearance and understand the implications
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-unlock"></i> Unlock Clearance
                                    </button>
                                    <a href="{{ route('admin.clearance.locked-clearances') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">⚠️ Important Notes</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Before Unlocking:</h6>
                                <ul class="mb-0">
                                    <li>Verify student has resolved the issues</li>
                                    <li>Check if requirements are now complete</li>
                                    <li>Ensure proper documentation exists</li>
                                    <li>Consider if penalty fees are required</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <h6 class="alert-heading">After Unlocking:</h6>
                                <ul class="mb-0">
                                    <li>Student can proceed with clearance</li>
                                    <li>All departments can process clearance</li>
                                    <li>Action will be logged in audit trail</li>
                                    <li>Student will be notified of unlock</li>
                                </ul>
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
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const reason = document.getElementById('unlock_reason').value.trim();
    const confirm = document.getElementById('confirm_unlock').checked;
    
    if (!reason || reason.length < 10) {
        e.preventDefault();
        alert('Please provide a detailed reason for unlocking (at least 10 characters).');
        return false;
    }
    
    if (!confirm) {
        e.preventDefault();
        alert('Please confirm that you want to unlock this clearance.');
        return false;
    }
    
    if (!window.confirm('Are you sure you want to unlock this clearance? This action will be logged.')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endsection
