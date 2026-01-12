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

    .badge-primary {
        background-color: #007bff;
        color: white;
    }

    .badge-info {
        background-color: #17a2b8;
        color: white;
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

    /* Pending departments cell styling */
    .pending-departments-cell {
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
</style>
<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Reports</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <span class="breadcrumb-item active">Reports</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form id="filter_form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Academic Year</label>
                                            <select id="academic_id" class="form-control" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $ay)
                                                    <option value="{{ $ay->id }}">{{ $ay->academic_year }}</option>
                                                @endforeach
                                            </select>
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
                    <!-- Report Tabs -->
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="not-cleared-tab" data-toggle="tab" href="#not-cleared" role="tab">
                                Students Not Cleared
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="cleared-tab" data-toggle="tab" href="#cleared" role="tab">
                                Students Cleared
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="reportTabsContent">
                        <!-- Students Not Cleared -->
                        <div class="tab-pane fade show active" id="not-cleared" role="tabpanel">
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Students Who Haven't Cleared</h5>
                                    <button type="button" id="print_not_cleared" class="btn btn-success">Print List</button>
                                </div>
                                <div class="table-responsive table-responsive-custom">
                                    <table class="table table-bordered table-white" id="not_cleared_table">
                                        <thead class="thead-white">
                                            <tr>
                                                <th style="width: 120px;">Student Number</th>
                                                <th style="width: 180px;">Name</th>
                                                <th style="width: 200px;">Department</th>
                                                <th style="width: 150px;">Program</th>
                                                <th style="width: 80px;">Year</th>
                                                <th style="width: 100px;">Has Violations</th>
                                                <th style="width: 120px;">Clearance Locked</th>
                                                <th style="width: 300px;">Pending Departments</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Students Cleared -->
                        <div class="tab-pane fade" id="cleared" role="tabpanel">
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Students Who Have Cleared</h5>
                                    <button type="button" id="print_cleared" class="btn btn-success">Print List</button>
                                </div>
                                <div class="table-responsive table-responsive-custom">
                                    <table class="table table-bordered table-white" id="cleared_table">
                                        <thead class="thead-white">
                                            <tr>
                                                <th style="width: 120px;">Student Number</th>
                                                <th style="width: 180px;">Name</th>
                                                <th style="width: 200px;">Department</th>
                                                <th style="width: 150px;">Program</th>
                                                <th style="width: 80px;">Year</th>
                                                <th style="width: 150px;">Cleared Date</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load reports when academic year or department changes
    function loadReportsAutomatically() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;

        if (!academicId) {
            // Clear all tables if no academic year selected
            clearAllTables();
            return;
        }

        loadNotClearedStudents(academicId, departmentId);
        loadClearedStudents(academicId, departmentId);
    }

    function clearAllTables() {
        document.querySelector('#not_cleared_table tbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Please select an academic year to load reports</td></tr>';
        document.querySelector('#cleared_table tbody').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Please select an academic year to load reports</td></tr>';
    }

    // Add event listeners to dropdowns
    document.getElementById('academic_id').addEventListener('change', loadReportsAutomatically);
    document.getElementById('department_id').addEventListener('change', loadReportsAutomatically);

    // Initialize with empty tables
    clearAllTables();



    // Add event listeners for both test buttons
    document.getElementById('test_api').addEventListener('click', runAPITest);
    document.getElementById('debug_test_api').addEventListener('click', runAPITest);

    // Print buttons
    document.getElementById('print_not_cleared').addEventListener('click', function() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;
        
        if (!academicId) {
            alert('Please select an academic year first');
            return;
        }

        const url = `{{ route('admin.reports.print-not-cleared') }}?academic_id=${academicId}&department_id=${departmentId}`;
        window.open(url, '_blank');
    });

    document.getElementById('print_cleared').addEventListener('click', function() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;
        
        if (!academicId) {
            alert('Please select an academic year first');
            return;
        }

        const url = `{{ route('admin.reports.print-cleared') }}?academic_id=${academicId}&department_id=${departmentId}`;
        window.open(url, '_blank');
    });

    function loadNotClearedStudents(academicId, departmentId) {
        // Show loading state
        const tbody = document.querySelector('#not_cleared_table tbody');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Loading...</td></tr>';

        fetch('{{ route("admin.reports.students-not-cleared") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                academic_id: academicId,
                department_id: departmentId || null
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response body:', text);
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Students not cleared response:', data);
            tbody.innerHTML = '';

            if (data.students && data.students.length > 0) {
                data.students.forEach(student => {
                    // Format pending departments as badges
                    let pendingDepartmentsHtml = '';
                    if (student.pending_departments && student.pending_departments.length > 0) {
                        pendingDepartmentsHtml = student.pending_departments.map(dept => {
                            // All pending departments should be yellow/warning since they haven't cleared the student yet
                            let badgeClass = 'badge badge-warning';

                            // Shorten department names for better display
                            let shortName = dept;
                            shortName = shortName.replace('SCHOOL OF INFORMATION TECHNOLOGY AND ENGINEERING', 'SITE');
                            shortName = shortName.replace('SCHOOL OF ARTS, SCIENCES AND TEACHER EDUCATION', 'SASTE');
                            shortName = shortName.replace('SCHOOL OF NURSING AND ALLIED HEALTH SCIENCES', 'SNAHS');
                            shortName = shortName.replace('SCHOOL OF BUSINESS ADMINISTRATION AND HOSPITALITY MANAGEMENT', 'SBAHM');
                            shortName = shortName.replace('BUSINESS AFFAIRS OFFICE', 'BAO');
                            shortName = shortName.replace('OFFICE OF STUDENT AFFAIRS', 'OSA');

                            return `<span class="badge ${badgeClass} mr-1 mb-1" style="font-size: 0.75em;">${shortName}</span>`;
                        }).join('');
                    } else {
                        pendingDepartmentsHtml = '<span class="text-muted">All cleared</span>';
                    }

                    const row = `
                        <tr>
                            <td style="font-weight: 500;">${student.student_number || 'N/A'}</td>
                            <td style="font-weight: 500;">${student.name || 'N/A'}</td>
                            <td><small>${student.department || 'N/A'}</small></td>
                            <td><small>${student.program || 'N/A'}</small></td>
                            <td class="text-center">${student.year || 'N/A'}</td>
                            <td class="text-center">
                                <span class="badge ${student.has_violations ? 'badge-warning' : 'badge-success'}">${student.has_violations ? 'Yes' : 'No'}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge ${student.clearance_locked ? 'badge-danger' : 'badge-success'}">${student.clearance_locked ? 'Yes' : 'No'}</span>
                            </td>
                            <td style="line-height: 1.6;">${pendingDepartmentsHtml}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No students found who haven\'t cleared</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading students not cleared:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function loadClearedStudents(academicId, departmentId) {
        // Show loading state
        const tbody = document.querySelector('#cleared_table tbody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

        fetch('{{ route("admin.reports.students-cleared") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                academic_id: academicId,
                department_id: departmentId || null
            })
        })
        .then(response => {
            console.log('Cleared response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Cleared error response body:', text);
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Students cleared response:', data);
            tbody.innerHTML = '';

            if (data.students && data.students.length > 0) {
                data.students.forEach(student => {
                    const row = `
                        <tr>
                            <td style="font-weight: 500;">${student.student_number || 'N/A'}</td>
                            <td style="font-weight: 500;">${student.name || 'N/A'}</td>
                            <td><small>${student.department || 'N/A'}</small></td>
                            <td><small>${student.program || 'N/A'}</small></td>
                            <td class="text-center">${student.year || 'N/A'}</td>
                            <td class="text-center">
                                <span class="badge badge-success">${student.cleared_date || 'N/A'}</span>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No students found who have cleared</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading cleared students:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function loadViolationsReport(academicId, departmentId) {
        fetch('{{ route("admin.reports.violations") }}', {
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
            const tbody = document.querySelector('#violations_table tbody');
            tbody.innerHTML = '';
            
            data.violations.forEach(violation => {
                const row = `
                    <tr>
                        <td>${violation.student_number}</td>
                        <td>${violation.student_name}</td>
                        <td>${violation.violation_type}</td>
                        <td>${violation.severity}</td>
                        <td>${violation.status}</td>
                        <td>${violation.violation_date}</td>
                        <td>${violation.blocks_clearance ? 'Yes' : 'No'}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        })
        .catch(error => console.error('Error:', error));
    }

    function loadGraduatedStudents(academicId, departmentId) {
        fetch('{{ route("admin.reports.graduated-students") }}', {
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
            const tbody = document.querySelector('#graduated_table tbody');
            tbody.innerHTML = '';
            
            data.students.forEach(student => {
                const row = `
                    <tr>
                        <td>${student.student_number}</td>
                        <td>${student.name}</td>
                        <td>${student.department}</td>
                        <td>${student.graduation_date}</td>
                        <td>${student.clearance_completed ? 'Yes' : 'No'}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>
@endsection
