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
                <h2 class="h3 mb-0">🔒 Locked Clearances Management</h2>
                <div>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#bulkUnlockModal">
                        <i class="fas fa-unlock"></i> Bulk Unlock
                    </button>
                    <button type="button" class="btn btn-danger" onclick="lockCurrentYear()">
                        <i class="fas fa-lock"></i> Lock Current Year
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $lockedClearances->total() }}</h4>
                            <p class="mb-0">Total Locked</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-lock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $lockedClearances->where('locked_at', '>=', now()->subDays(7))->count() }}</h4>
                            <p class="mb-0">Locked This Week</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.clearance.locked-clearances') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="academic_year">Academic Year</label>
                        <select name="academic_year" id="academic_year" class="form-control">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year') == $year->id ? 'selected' : '' }}>
                                    {{ $year->academic_year }} - {{ $year->semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="department">Department</label>
                        <select name="department" id="department" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.clearance.locked-clearances') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Locked Clearances Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Locked Clearances</h5>
        </div>
        <div class="card-body">
            @if($lockedClearances->count() > 0)
                <form id="bulkUnlockForm">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Student Number</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Academic Year</th>
                                    <th>Lock Reason</th>
                                    <th>Locked Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lockedClearances as $clearance)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="clearance_ids[]" value="{{ $clearance->id }}" class="clearance-checkbox">
                                        </td>
                                        <td>{{ $clearance->student->student_number }}</td>
                                        <td>{{ $clearance->student->user->firstname }} {{ $clearance->student->user->lastname }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $clearance->student->department->department_code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $clearance->academicYear->academic_year ?? 'N/A' }} - {{ $clearance->academicYear->semester ?? 'N/A' }}</td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($clearance->lock_reason, 50) }}</small>
                                        </td>
                                        <td>{{ $clearance->locked_at ? $clearance->locked_at->format('M d, Y g:i A') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('admin.clearance.show-locked', $clearance->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="unlockSingle({{ $clearance->id }})">
                                                <i class="fas fa-unlock"></i> Unlock
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $lockedClearances->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-unlock fa-3x text-muted mb-3"></i>
                    <h5>No Locked Clearances Found</h5>
                    <p class="text-muted">All clearances are currently unlocked or no clearances match your filter criteria.</p>
                </div>
            @endif
        </div>
    </div>
        </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->

<!-- Bulk Unlock Modal -->
<div class="modal fade" id="bulkUnlockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Unlock Clearances</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.clearance.bulk-unlock') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_unlock_reason">Reason for Unlocking</label>
                        <textarea name="bulk_unlock_reason" id="bulk_unlock_reason" class="form-control" rows="3" required 
                                  placeholder="Enter the reason for unlocking these clearances..."></textarea>
                    </div>
                    <div id="selectedClearances"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-unlock"></i> Unlock Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.clearance-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Bulk unlock functionality
function openBulkUnlock() {
    const selected = document.querySelectorAll('.clearance-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select at least one clearance to unlock.');
        return;
    }

    const selectedContainer = document.getElementById('selectedClearances');
    selectedContainer.innerHTML = '';
    
    selected.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'clearance_ids[]';
        input.value = checkbox.value;
        selectedContainer.appendChild(input);
    });

    $('#bulkUnlockModal').modal('show');
}

// Single unlock
function unlockSingle(clearanceId) {
    if (confirm('Are you sure you want to unlock this clearance?')) {
        const reason = prompt('Please enter the reason for unlocking:');
        if (reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/clearance/unlock/${clearanceId}`;
            
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

// Lock current year
function lockCurrentYear() {
    if (confirm('Are you sure you want to lock all incomplete clearances for the current academic year?\n\nThis will:\n- Lock all students with incomplete clearances\n- Prevent them from accessing clearance until unlocked\n- Send notifications to affected students\n\nThis action cannot be undone easily.')) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locking...';
        button.disabled = true;

        // Make AJAX request to trigger locking
        fetch('/admin/clearance/lock-current-year', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                confirm: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Successfully locked ${data.locked_count} clearances.`);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error occurred while locking clearances.');
            console.error('Error:', error);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Open bulk unlock modal when bulk unlock button is clicked
document.querySelector('[data-target="#bulkUnlockModal"]').addEventListener('click', openBulkUnlock);
</script>
@endsection
