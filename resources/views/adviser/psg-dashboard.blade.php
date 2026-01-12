@extends('adviser.layout.layout')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0">PSG Adviser Dashboard</h2>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt"></i> {{ date('F d, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStudents }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Clearances
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $studentsRequiringClearance }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Cleared Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recentClearances->where('created_at', '>=', today())->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Cleared
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recentClearances->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('adviser.clearance.clearance-tap-id') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-id-card fa-2x mb-2"></i><br>
                                Clearance Tap ID
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('adviser.student.list-of-students') }}" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-list fa-2x mb-2"></i><br>
                                View Students
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('adviser.account_settings') }}" class="btn btn-secondary btn-lg w-100">
                                <i class="fas fa-cog fa-2x mb-2"></i><br>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Clearances -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Clearances Signed</h6>
                    <span class="badge badge-primary">{{ $recentClearances->count() }} Total</span>
                </div>
                <div class="card-body">
                    @if($recentClearances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student Number</th>
                                        <th>Date Cleared</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentClearances->take(10) as $clearance)
                                    <tr>
                                        <td>
                                            {{ $clearance->clearance->student->user->firstname }} 
                                            {{ $clearance->clearance->student->user->lastname }}
                                        </td>
                                        <td>{{ $clearance->clearance->student->student_number }}</td>
                                        <td>{{ $clearance->created_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <span class="badge badge-success">Cleared</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No clearances signed yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
</style>
@endsection
