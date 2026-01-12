@extends('admin.layout.header')

@section('main-content')
<!-- Page Container START -->
<div class="page-container simple-aesthetic">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <!-- Simple Header -->
        <div class="page-header">
            <h2 class="header-title">Dashboard</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="#" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                </nav>
            </div>
        </div>

        <!-- Simple Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card simple-stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-simple">
                                <i class="anticon anticon-user"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="mb-0">{{ number_format($totalStudents) }}</h3>
                                <p class="text-muted mb-0">Total Students</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card simple-stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-simple">
                                <i class="anticon anticon-apartment"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="mb-0">{{ number_format($totalDepartment) }}</h3>
                                <p class="text-muted mb-0">Departments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card simple-stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-simple">
                                <i class="anticon anticon-book"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="mb-0">{{ number_format($totalCourse) }}</h3>
                                <p class="text-muted mb-0">Total Courses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card simple-stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-simple">
                                <i class="anticon anticon-team"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="mb-0">{{ number_format($totalUsers) }}</h3>
                                <p class="text-muted mb-0">System Users</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clearance Progress Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="anticon anticon-file-done mr-2"></i>
                            Clearance Progress Overview
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Progress Statistics -->
                            <div class="col-lg-8">
                                <div class="clearance-progress-stats">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="progress-stat-item completed">
                                                <div class="progress-stat-icon">
                                                    <i class="anticon anticon-check-circle"></i>
                                                </div>
                                                <div class="progress-stat-content">
                                                    <h3>{{ $completedClearances ?? 0 }}</h3>
                                                    <p>Completed</p>
                                                    <small>Fully cleared students</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="progress-stat-item pending">
                                                <div class="progress-stat-icon">
                                                    <i class="anticon anticon-clock-circle"></i>
                                                </div>
                                                <div class="progress-stat-content">
                                                    <h3>{{ $pendingClearances ?? 0 }}</h3>
                                                    <p>In Progress</p>
                                                    <small>Partial clearances</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="progress-stat-item blocked">
                                                <div class="progress-stat-icon">
                                                    <i class="anticon anticon-exclamation-circle"></i>
                                                </div>
                                                <div class="progress-stat-content">
                                                    <h3>{{ $blockedClearances ?? 0 }}</h3>
                                                    <p>Blocked</p>
                                                    <small>Requiring attention</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="overall-progress mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="progress-label">Overall Completion Rate</span>
                                        <span class="progress-percentage">{{ isset($totalClearances) && $totalClearances > 0 ? round(($completedClearances / $totalClearances) * 100, 1) : 0 }}%</span>
                                    </div>
                                    <div class="progress-bar-simple">
                                        <div class="progress-fill" style="width: {{ isset($totalClearances) && $totalClearances > 0 ? round(($completedClearances / $totalClearances) * 100, 1) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="col-lg-4">
                                <div class="clearance-quick-actions">
                                    <h5 class="mb-3">Quick Actions</h5>
                                    <div class="quick-action-list">
                                        <a href="{{ route('admin.clearance.list') }}" class="quick-action-btn">
                                            <i class="anticon anticon-file-done"></i>
                                            <span>Manage Clearances</span>
                                            <i class="anticon anticon-arrow-right"></i>
                                        </a>
                                        <a href="{{route('admin.reports.dean-osa')}}" class="quick-action-btn">
                                            <i class="anticon anticon-bar-chart"></i>
                                            <span>Generate Reports</span>
                                            <i class="anticon anticon-arrow-right"></i>
                                        </a>
                                        <a href="#" class="quick-action-btn">
                                            <i class="anticon anticon-download"></i>
                                            <span>Export Data</span>
                                            <i class="anticon anticon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clearance Report by Department -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="anticon anticon-bar-chart mr-2"></i>
                            Clearance Report by Department
                        </h4>
                        <small class="text-muted">Student clearance completion percentage by department</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($departments->take(6) as $department)
                            @php
                                $totalStudentsInDept = $department->students ? $department->students->count() : 0;
                                $completedStudents = $department->cleared_count ?? 0;
                                $completionPercentage = $department->clearance_completion ?? 0;
                            @endphp
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="department-clearance-card">
                                    <div class="dept-clearance-header">
                                        <div class="dept-info">
                                            <h6>{{ $department->department_name ?? 'Unknown Department' }}</h6>
                                            <p class="text-muted mb-0">{{ $totalStudentsInDept }} students</p>
                                        </div>
                                        <div class="completion-badge">
                                            <span class="percentage-text">{{ $completionPercentage }}%</span>
                                        </div>
                                    </div>

                                    <div class="clearance-breakdown">
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Completed</span>
                                            <span class="breakdown-value completed">{{ $completedStudents }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Pending</span>
                                            <span class="breakdown-value pending">{{ $totalStudentsInDept - $completedStudents }}</span>
                                        </div>
                                    </div>

                                    <div class="dept-progress-bar">
                                        <div class="progress-track">
                                            <div class="progress-fill-dept" style="width: {{ $completionPercentage }}%"></div>
                                        </div>
                                        <div class="progress-labels">
                                            <span class="progress-start">0%</span>
                                            <span class="progress-end">100%</span>
                                        </div>
                                    </div>

                                    <div class="dept-actions">
                                        <a href="#" class="btn-view-details">
                                            <i class="anticon anticon-eye"></i>
                                            View Details
                                        </a>
                                        <a href="#" class="btn-manage">
                                            <i class="anticon anticon-setting"></i>
                                            Manage
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4">
                                <i class="anticon anticon-bar-chart" style="font-size: 3rem; color: #ddd;"></i>
                                <p class="text-muted mt-2">No department data available</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Summary Statistics -->
                        <div class="clearance-summary mt-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-item">
                                        <div class="summary-icon overall">
                                            <i class="anticon anticon-pie-chart"></i>
                                        </div>
                                        <div class="summary-content">
                                            <h4>{{ isset($totalClearances) && $totalClearances > 0 ? round(($completedClearances / $totalClearances) * 100, 1) : 0 }}%</h4>
                                            <p>Overall Completion</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-item">
                                        <div class="summary-icon high">
                                            <i class="anticon anticon-arrow-up"></i>
                                        </div>
                                        <div class="summary-content">
                                            <h4>{{ $departments->where('students', '!=', null)->count() }}</h4>
                                            <p>Active Departments</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-item">
                                        <div class="summary-icon medium">
                                            <i class="anticon anticon-clock-circle"></i>
                                        </div>
                                        <div class="summary-content">
                                            <h4>{{ $pendingClearances ?? 0 }}</h4>
                                            <p>Pending Clearances</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-item">
                                        <div class="summary-icon low">
                                            <i class="anticon anticon-exclamation-circle"></i>
                                        </div>
                                        <div class="summary-content">
                                            <h4>{{ $blockedClearances ?? 0 }}</h4>
                                            <p>Needs Attention</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clearance Report by Academic Year -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="anticon anticon-calendar mr-2"></i>
                            Student Clearance Report by Academic Year
                        </h4>
                        <small class="text-muted">Clearance completion status across different academic years</small>
                    </div>
                    <div class="card-body">
                        @if(isset($academicYearReports) && $academicYearReports->count() > 0)
                        <div class="academic-year-reports">
                            @foreach($academicYearReports as $yearReport)
                            <div class="academic-year-section mb-4">
                                <div class="year-header">
                                    <div class="year-info">
                                        <h5 class="year-title">
                                            {{ $yearReport->year_name }}
                                            @if($yearReport->status === 'active')
                                                <span class="badge badge-success ml-2">Active</span>
                                            @else
                                                <span class="badge badge-secondary ml-2">{{ ucfirst($yearReport->status) }}</span>
                                            @endif
                                        </h5>
                                        <p class="year-period text-muted">{{ $yearReport->start_date }} - {{ $yearReport->end_date }}</p>
                                    </div>
                                    <div class="year-summary">
                                        <div class="summary-stats">
                                            <div class="stat-item-small">
                                                <span class="stat-number">{{ $yearReport->total_students }}</span>
                                                <span class="stat-label">Total Students</span>
                                            </div>
                                            <div class="stat-item-small">
                                                <span class="stat-number completed-color">{{ $yearReport->completed_clearances }}</span>
                                                <span class="stat-label">Completed</span>
                                            </div>
                                            <div class="stat-item-small">
                                                <span class="stat-number pending-color">{{ $yearReport->pending_clearances }}</span>
                                                <span class="stat-label">Pending</span>
                                            </div>
                                            <div class="stat-item-small">
                                                <span class="stat-number blocked-color">{{ $yearReport->blocked_clearances }}</span>
                                                <span class="stat-label">Blocked</span>
                                            </div>
                                        </div>
                                        <div class="completion-rate-large">
                                            <span class="rate-number">{{ $yearReport->completion_percentage }}%</span>
                                            <span class="rate-label">Completion Rate</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="year-progress-section">
                                    
                                    <!-- Individual Progress Bars for Each Status -->
                                    <div class="individual-progress-bars">
                                        <div class="status-progress-item">
                                            <div class="status-progress-header">
                                                <span class="legend-color completed"></span>
                                                <span class="legend-text">Completed ({{ $yearReport->completed_percentage }}%)</span>
                                            </div>
                                            <div class="status-progress-bar">
                                                <div class="status-progress-fill completed" style="width: {{ $yearReport->completed_percentage }}%"></div>
                                            </div>
                                        </div>

                                        <div class="status-progress-item">
                                            <div class="status-progress-header">
                                                <span class="legend-color pending"></span>
                                                <span class="legend-text">Pending ({{ $yearReport->pending_percentage }}%)</span>
                                            </div>
                                            <div class="status-progress-bar">
                                                <div class="status-progress-fill pending" style="width: {{ $yearReport->pending_percentage }}%"></div>
                                            </div>
                                        </div>

                                        <div class="status-progress-item">
                                            <div class="status-progress-header">
                                                <span class="legend-color blocked"></span>
                                                <span class="legend-text">Blocked ({{ $yearReport->blocked_percentage }}%)</span>
                                            </div>
                                            <div class="status-progress-bar">
                                                <div class="status-progress-fill blocked" style="width: {{ $yearReport->blocked_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department Breakdown for this Academic Year -->
                                <div class="year-departments mt-3">
                                    <h6 class="mb-3">Department Breakdown</h6>
                                    <div class="row">
                                        @foreach($yearReport->department_stats as $deptStat)
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="dept-year-card">
                                                <div class="dept-year-header">
                                                    <h6>{{ $deptStat->department_name }}</h6>
                                                    <span class="dept-completion-badge">{{ $deptStat->completion_rate }}%</span>
                                                </div>
                                                <div class="dept-year-stats">
                                                    <div class="dept-stat-row">
                                                        <span class="dept-stat-label">Students:</span>
                                                        <span class="dept-stat-value">{{ $deptStat->total_students }}</span>
                                                    </div>
                                                    <div class="dept-stat-row">
                                                        <span class="dept-stat-label">Cleared:</span>
                                                        <span class="dept-stat-value completed-color">{{ $deptStat->cleared_students }}</span>
                                                    </div>
                                                    <div class="dept-stat-row">
                                                        <span class="dept-stat-label">Pending:</span>
                                                        <span class="dept-stat-value pending-color">{{ $deptStat->pending_students }}</span>
                                                    </div>
                                                    <div class="dept-stat-row">
                                                        <span class="dept-stat-label">Blocked:</span>
                                                        <span class="dept-stat-value blocked-color">{{ $deptStat->blocked_students }}</span>
                                                    </div>
                                                </div>
                                                <div class="dept-year-progress">
                                                    <!-- Individual Progress Bars for Each Department Status -->
                                                    <div class="dept-individual-progress-bars">
                                                        <div class="dept-status-progress-item">
                                                            <div class="dept-status-progress-header">
                                                                <span class="dept-legend-color completed"></span>
                                                                <span class="dept-legend-text">Cleared ({{ $deptStat->cleared_percentage }}%)</span>
                                                            </div>
                                                            <div class="dept-status-progress-bar">
                                                                <div class="dept-status-progress-fill completed" style="width: {{ $deptStat->cleared_percentage }}%"></div>
                                                            </div>
                                                        </div>

                                                        <div class="dept-status-progress-item">
                                                            <div class="dept-status-progress-header">
                                                                <span class="dept-legend-color pending"></span>
                                                                <span class="dept-legend-text">Pending ({{ $deptStat->pending_percentage }}%)</span>
                                                            </div>
                                                            <div class="dept-status-progress-bar">
                                                                <div class="dept-status-progress-fill pending" style="width: {{ $deptStat->pending_percentage }}%"></div>
                                                            </div>
                                                        </div>

                                                        <div class="dept-status-progress-item">
                                                            <div class="dept-status-progress-header">
                                                                <span class="dept-legend-color blocked"></span>
                                                                <span class="dept-legend-text">Blocked ({{ $deptStat->blocked_percentage }}%)</span>
                                                            </div>
                                                            <div class="dept-status-progress-bar">
                                                                <div class="dept-status-progress-fill blocked" style="width: {{ $deptStat->blocked_percentage }}%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="anticon anticon-calendar" style="font-size: 4rem; color: #ddd;"></i>
                            <h5 class="mt-3 text-muted">No Academic Year Data Available</h5>
                            <p class="text-muted">Academic year clearance reports will appear here once data is available.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Simple Department Overview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Department Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($departments->take(6) as $department)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="simple-dept-card">
                                    <div class="dept-info">
                                        <h6>{{ $department->department_name ?? 'Unknown Department' }}</h6>
                                        <div class="dept-stats">
                                            <span class="badge badge-primary">{{ $department->courses ? $department->courses->count() : 0 }} courses</span>
                                            <span class="badge badge-secondary">{{ $department->students ? $department->students->count() : 0 }} students</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No departments found</p>
                            </div>
                            @endforelse
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

<style>
/* Simple Aesthetic Dashboard Styles */
.simple-aesthetic {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    min-height: 100vh;
}

/* Simple Statistics Cards */
.simple-stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    margin-bottom: 1.5rem;
}

.simple-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-icon-simple {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 1.5rem;
}

.simple-stat-card h3 {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
}

.simple-stat-card p {
    font-size: 0.9rem;
    color: #7f8c8d;
}

/* Simple Department Cards */
.simple-dept-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.simple-dept-card:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(102, 126, 234, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.dept-info h6 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.dept-stats {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.dept-stats .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
}

/* Clearance Progress Styles */
.clearance-progress-stats {
    margin-bottom: 1rem;
}

.progress-stat-item {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.progress-stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.progress-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem auto;
    font-size: 1.5rem;
    color: white;
}

.progress-stat-item.completed .progress-stat-icon {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

.progress-stat-item.pending .progress-stat-icon {
    background: linear-gradient(135deg, #f39c12, #e67e22);
}

.progress-stat-item.blocked .progress-stat-icon {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.progress-stat-content h3 {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.progress-stat-content p {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #2c3e50;
}

.progress-stat-content small {
    color: #7f8c8d;
    font-size: 0.8rem;
}

.overall-progress {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 10px;
    padding: 1.5rem;
}

.progress-label {
    font-weight: 600;
    color: #2c3e50;
}

.progress-percentage {
    font-weight: 700;
    font-size: 1.25rem;
    color: #27ae60;
}

.progress-bar-simple {
    height: 12px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 6px;
    transition: all 0.8s ease;
}

/* Quick Actions in Clearance Section */
.clearance-quick-actions {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 10px;
    padding: 1.5rem;
    height: 100%;
}

.clearance-quick-actions h5 {
    color: #2c3e50;
    font-weight: 600;
}

.quick-action-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.quick-action-btn:hover {
    background: rgba(102, 126, 234, 0.1);
    border-color: rgba(102, 126, 234, 0.2);
    transform: translateX(4px);
    text-decoration: none;
    color: #667eea;
}

.quick-action-btn i:first-child {
    color: #667eea;
    font-size: 1.25rem;
}

.quick-action-btn span {
    flex: 1;
    font-weight: 500;
}

.quick-action-btn i:last-child {
    color: #7f8c8d;
    transition: all 0.3s ease;
}

.quick-action-btn:hover i:last-child {
    transform: translateX(4px);
    color: #667eea;
}

/* Department Clearance Report Styles */
.department-clearance-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.department-clearance-card:hover {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(102, 126, 234, 0.2);
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.dept-clearance-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.dept-clearance-header .dept-info h6 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.completion-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.clearance-breakdown {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    background: rgba(248, 249, 250, 0.5);
    border-radius: 8px;
    padding: 1rem;
}

.breakdown-item {
    text-align: center;
    flex: 1;
}

.breakdown-label {
    display: block;
    font-size: 0.8rem;
    color: #7f8c8d;
    margin-bottom: 0.25rem;
}

.breakdown-value {
    display: block;
    font-size: 1.25rem;
    font-weight: 600;
}

.breakdown-value.completed {
    color: #27ae60;
}

.breakdown-value.pending {
    color: #f39c12;
}

.dept-progress-bar {
    margin-bottom: 1rem;
}

.progress-track {
    height: 8px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill-dept {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 4px;
    transition: all 0.8s ease;
}

.progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #7f8c8d;
}

.dept-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-view-details,
.btn-manage {
    flex: 1;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-view-details {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.btn-view-details:hover {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
    text-decoration: none;
}

.btn-manage {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
    border: 1px solid rgba(39, 174, 96, 0.2);
}

.btn-manage:hover {
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
    text-decoration: none;
}

/* Summary Statistics */
.clearance-summary {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 12px;
    padding: 1.5rem;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.summary-item:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
}

.summary-icon.overall {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.summary-icon.high {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

.summary-icon.medium {
    background: linear-gradient(135deg, #f39c12, #e67e22);
}

.summary-icon.low {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.summary-content h4 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.summary-content p {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin: 0;
}

/* Card Enhancements */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}

.card-header {
    background: rgba(248, 249, 250, 0.5);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px 12px 0 0 !important;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .simple-stat-card h3 {
        font-size: 1.5rem;
    }

    .stat-icon-simple {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }

    .simple-dept-card {
        padding: 1rem;
    }

    .progress-stat-item {
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .progress-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }

    .progress-stat-content h3 {
        font-size: 1.5rem;
    }

    .overall-progress {
        padding: 1rem;
    }

    .clearance-quick-actions {
        padding: 1rem;
        margin-top: 1rem;
    }

    .quick-action-btn {
        padding: 0.75rem;
    }

    .department-clearance-card {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .dept-clearance-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .completion-badge {
        align-self: flex-end;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }

    .clearance-breakdown {
        padding: 0.75rem;
    }

    .breakdown-value {
        font-size: 1rem;
    }

    .dept-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn-view-details,
    .btn-manage {
        padding: 0.6rem 1rem;
        font-size: 0.75rem;
    }

    .clearance-summary {
        padding: 1rem;
    }

    .summary-item {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .summary-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .summary-content h4 {
        font-size: 1.25rem;
    }
}

/* Academic Year Report Styles */
.academic-year-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
}

.year-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.year-info .year-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.year-period {
    margin: 5px 0 0 0;
    font-size: 0.9rem;
}

.year-summary {
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
}

.summary-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-item-small {
    text-align: center;
}

.stat-item-small .stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1;
}

.stat-item-small .stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 2px;
}

.completion-rate-large {
    text-align: center;
    padding: 10px 20px;
    background: white;
    border-radius: 8px;
    border: 2px solid #28a745;
}

.completion-rate-large .rate-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #28a745;
    line-height: 1;
}

.completion-rate-large .rate-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 2px;
}

.year-progress-section {
    margin-top: 15px;
}

.progress-bar-academic {
    height: 12px;
    background: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
}

.progress-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.progress-segment.completed {
    background: #28a745;
}

.progress-segment.pending {
    background: #ffc107;
}

.progress-segment.blocked {
    background: #dc3545;
}

.progress-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.legend-color.completed {
    background: #28a745;
}

.legend-color.pending {
    background: #ffc107;
}

.legend-color.blocked {
    background: #dc3545;
}

.legend-text {
    font-size: 0.85rem;
    color: #6c757d;
}

.year-departments h6 {
    color: #495057;
    font-weight: 600;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
}

.dept-year-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    height: 100%;
}

.dept-year-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.dept-year-header h6 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
}

.dept-completion-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.dept-year-stats {
    margin-bottom: 12px;
}

.dept-stat-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.dept-stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.dept-stat-value {
    font-size: 0.85rem;
    font-weight: 600;
}

/* Department Progress Bar Styles */
.dept-progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    margin-bottom: 8px;
}

.dept-progress-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.dept-progress-segment.completed {
    background: #28a745;
}

.dept-progress-segment.pending {
    background: #ffc107;
}

.dept-progress-segment.blocked {
    background: #dc3545;
}

.dept-progress-legend {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
}

.dept-legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.dept-legend-color {
    width: 8px;
    height: 8px;
    border-radius: 2px;
}

.dept-legend-color.completed {
    background: #28a745;
}

.dept-legend-color.pending {
    background: #ffc107;
}

.dept-legend-color.blocked {
    background: #dc3545;
}

.dept-legend-text {
    font-weight: 600;
    color: #495057;
}

/* Individual Status Progress Bars */
.individual-progress-bars {
    margin-top: 15px;
}

.status-progress-item {
    margin-bottom: 12px;
}

.status-progress-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.status-progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.status-progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.status-progress-fill.completed {
    background: #28a745;
}

.status-progress-fill.pending {
    background: #ffc107;
}

.status-progress-fill.blocked {
    background: #dc3545;
}

/* Department Individual Status Progress Bars */
.dept-individual-progress-bars {
    margin-top: 10px;
}

.dept-status-progress-item {
    margin-bottom: 8px;
}

.dept-status-progress-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 3px;
}

.dept-status-progress-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.dept-status-progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.dept-status-progress-fill.completed {
    background: #28a745;
}

.dept-status-progress-fill.pending {
    background: #ffc107;
}

.dept-status-progress-fill.blocked {
    background: #dc3545;
}

/* Keep old styles for backward compatibility */
.mini-progress-bar {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}

.mini-progress-fill {
    height: 100%;
    background: #28a745;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.completed-color {
    color: #28a745 !important;
}

.pending-color {
    color: #ffc107 !important;
}

.blocked-color {
    color: #dc3545 !important;
}

@media (max-width: 768px) {
    .year-header {
        flex-direction: column;
        gap: 15px;
    }

    .year-summary {
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .summary-stats {
        justify-content: space-around;
        width: 100%;
    }
}
</style>

@endsection