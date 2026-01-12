@extends('admin.layout.header')

@section('main-content')
<style>
    /* Custom styles for better table readability */
    .table-responsive-custom {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table-white {
        background-color: white;
        margin-bottom: 0;
    }

    .table-white th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        font-size: 0.85em;
        padding: 12px 8px;
        vertical-align: middle;
    }

    .table-white td {
        padding: 12px 8px;
        vertical-align: middle;
        border-color: #e9ecef;
    }

    .table-white tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Badge improvements */
    .badge {
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
        margin: 2px;
        white-space: nowrap;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }

    .badge-info {
        background-color: #17a2b8;
        color: white;
    }

    /* Read-only styling */
    .read-only-notice {
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 20px;
        color: #0066cc;
    }

    .read-only-notice i {
        margin-right: 8px;
    }

    /* Department status cell styling */
    .department-status-cell {
        max-width: 300px;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.8;
        padding: 8px !important;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .table-responsive-custom {
            font-size: 0.85em;
        }

        .badge {
            font-size: 0.7em;
            padding: 2px 6px;
        }
    }

    .academic-year-info {
        background-color: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid #007bff;
    }

    .loading-spinner {
        text-align: center;
        padding: 40px;
    }

    .no-data-message {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
</style>

<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Previous Academic Years Clearance Records</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item">
                        <i class="anticon anticon-home m-r-5"></i>Home
                    </a>
                    <a class="breadcrumb-item" href="{{route('admin.reports.dean-osa')}}">Reports</a>
                    <span class="breadcrumb-item active">Previous Years Records</span>
                </nav>
            </div>
        </div>

        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')

        <div class="card">
            <div class="card-body">
                <!-- Read-only Notice -->
                <div class="read-only-notice">
                    <i class="anticon anticon-info-circle"></i>
                    <strong>Read-Only Records:</strong> These are historical clearance records from previous academic years. 
                    Data is displayed for reference only and cannot be modified.
                </div>

                <!-- Filter Form -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form id="filter_form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Previous Academic Year <span class="text-danger">*</span></label>
                                        <select id="academic_id" class="form-control" required>
                                            <option value="">Select Previous Academic Year</option>
                                            @foreach($academicYears as $ay)
                                                <option value="{{ $ay->id }}">
                                                    {{ $ay->academic_year }} - {{ $ay->semester }}
                                                    <span class="badge badge-secondary">Inactive</span>
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($academicYears->isEmpty())
                                            <small class="text-muted">No previous academic years found.</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department (Optional)</label>
                                        <select id="department_id" class="form-control">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Academic Year Info Display -->
                <div id="academic_year_info" class="academic-year-info" style="display: none;">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-1">
                                <i class="anticon anticon-calendar mr-2"></i>
                                <span id="selected_academic_year"></span>
                            </h5>
                            <p class="mb-0 text-muted">Historical clearance records for this academic period</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-secondary">
                                <i class="anticon anticon-lock mr-1"></i>
                                Read-Only
                            </span>
                            <div class="mt-2">
                                <small class="text-muted">Total Records: <span id="total_records">0</span></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div id="data_section" style="display: none;">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Clearance Records</h5>
                            <div>
                                <button type="button" id="export_data" class="btn btn-info">
                                    <i class="anticon anticon-download mr-1"></i>Export Data
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-bordered table-white" id="records_table">
                                <thead class="thead-white">
                                    <tr>
                                        <th style="width: 120px;">Student Number</th>
                                        <th style="width: 180px;">Name</th>
                                        <th style="width: 150px;">Department</th>
                                        <th style="width: 150px;">Program</th>
                                        <th style="width: 80px;">Year</th>
                                        <th style="width: 100px;">Overall Status</th>
                                        <th style="width: 100px;">Locked</th>
                                        <th style="width: 300px;">Department Statuses</th>
                                        <th style="width: 120px;">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody id="records_tbody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loading_state" class="loading-spinner" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading clearance records...</p>
                </div>

                <!-- No Data State -->
                <div id="no_data_state" class="no-data-message" style="display: none;">
                    <i class="anticon anticon-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                    <h5 class="mt-3">No Records Found</h5>
                    <p>No clearance records found for the selected criteria.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->
    @include('admin.layout.footer')
</div>
<!-- Page Container END -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load data when academic year or department changes
    document.getElementById('academic_id').addEventListener('change', loadData);
    document.getElementById('department_id').addEventListener('change', loadData);

    function loadData() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;

        // Hide all sections first
        hideAllSections();

        if (!academicId) {
            return;
        }

        showLoadingState();
        
        fetch('{{ route("admin.reports.previous-years.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                academic_id: academicId,
                department_id: departmentId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingState();
            
            if (data.success) {
                displayAcademicYearInfo(data.academic_year_info, data.total_records);
                displayData(data.data);
            } else {
                showError(data.error || 'Failed to load data');
            }
        })
        .catch(error => {
            hideLoadingState();
            console.error('Error loading data:', error);
            showError('An error occurred while loading data. Please try again.');
        });
    }

    function hideAllSections() {
        document.getElementById('academic_year_info').style.display = 'none';
        document.getElementById('data_section').style.display = 'none';
        document.getElementById('no_data_state').style.display = 'none';
    }

    function showLoadingState() {
        document.getElementById('loading_state').style.display = 'block';
    }

    function hideLoadingState() {
        document.getElementById('loading_state').style.display = 'none';
    }

    function displayAcademicYearInfo(academicYearInfo, totalRecords) {
        document.getElementById('selected_academic_year').textContent = 
            academicYearInfo.academic_year + ' - ' + academicYearInfo.semester;
        document.getElementById('total_records').textContent = totalRecords;
        document.getElementById('academic_year_info').style.display = 'block';
    }

    function displayData(records) {
        const tbody = document.getElementById('records_tbody');
        
        if (records.length === 0) {
            document.getElementById('no_data_state').style.display = 'block';
            return;
        }

        let html = '';
        records.forEach(record => {
            // Build department statuses badges
            let departmentStatusesHtml = '';
            for (const [deptName, status] of Object.entries(record.department_statuses)) {
                const badgeClass = status === 'cleared' ? 'badge-success' : 
                                 status === 'pending' ? 'badge-warning' : 'badge-secondary';
                departmentStatusesHtml += `<span class="badge ${badgeClass}">${deptName}: ${status}</span> `;
            }

            // Overall status badge
            const overallStatusBadge = record.overall_status === 'cleared' ? 'badge-success' : 
                                     record.overall_status === 'pending' ? 'badge-warning' : 'badge-secondary';

            // Lock status
            const lockStatus = record.is_locked ? 
                `<span class="badge badge-danger" title="${record.lock_reason || 'Locked'}">
                    <i class="anticon anticon-lock mr-1"></i>Yes
                </span>` : 
                `<span class="badge badge-success">No</span>`;

            html += `
                <tr>
                    <td>${record.student_number}</td>
                    <td>${record.student_name}</td>
                    <td>${record.department}</td>
                    <td>${record.course}</td>
                    <td>${record.year}</td>
                    <td><span class="badge ${overallStatusBadge}">${record.overall_status}</span></td>
                    <td>${lockStatus}</td>
                    <td class="department-status-cell">${departmentStatusesHtml}</td>
                    <td>${record.updated_at}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        document.getElementById('data_section').style.display = 'block';
    }

    function showError(message) {
        const tbody = document.getElementById('records_tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-danger">
                    <i class="anticon anticon-exclamation-circle mr-2"></i>
                    ${message}
                </td>
            </tr>
        `;
        document.getElementById('data_section').style.display = 'block';
    }

    // Export functionality (placeholder)
    document.getElementById('export_data').addEventListener('click', function() {
        alert('Export functionality will be implemented in a future update.');
    });
});
</script>
@endsection
