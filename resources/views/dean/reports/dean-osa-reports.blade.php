@extends('dean.layout.header')

@section('main-content')
<style>
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
                                            <label>Department</label>
                                            <select id="department_id" class="form-control" disabled>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}" selected>{{ $dept->department_name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">You can only view students from your department</small>
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
                                                <th>Student Number</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Program</th>
                                                <th>Year</th>
                                                <th>Has Violations</th>
                                                <th>Clearance Locked</th>
                                                <th>Pending Departments</th>
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
                                                <th>Student Number</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Program</th>
                                                <th>Year</th>
                                                <th>Cleared Date</th>
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
    @include('dean.layout.footer')

</div>
<!-- Page Container END -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load reports when academic year changes (department is fixed for dean)
    function loadReportsAutomatically() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value; // Dean's department is always selected

        console.log('Auto-loading reports with:', { academicId, departmentId });

        if (!academicId) {
            // Clear all tables if no academic year selected
            clearAllTables();
            return;
        }

        if (!departmentId) {
            console.error('Department not found. Please contact administrator.');
            clearAllTables();
            return;
        }

        loadNotClearedStudents(academicId, departmentId);
        loadClearedStudents(academicId, departmentId);
        loadViolationsReport(academicId, departmentId);
        loadGraduatedStudents(academicId, departmentId);
    }

    // Function to clear all report tables
    function clearAllTables() {
        const tables = ['#not_cleared_table tbody', '#cleared_table tbody', '#violations_table tbody', '#graduated_table tbody'];
        tables.forEach(selector => {
            const tbody = document.querySelector(selector);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Please select an academic year to load reports</td></tr>';
            }
        });
    }

    // Add event listener to academic year dropdown (department is fixed for dean)
    document.getElementById('academic_id').addEventListener('change', loadReportsAutomatically);

    // Initialize with empty tables
    clearAllTables();



    // Print buttons
    document.getElementById('print_not_cleared').addEventListener('click', function() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;
        
        if (!academicId) {
            alert('Please select an academic year first');
            return;
        }

        const url = `{{ route('dean.reports.print-not-cleared') }}?academic_id=${academicId}&department_id=${departmentId}`;
        window.open(url, '_blank');
    });

    document.getElementById('print_cleared').addEventListener('click', function() {
        const academicId = document.getElementById('academic_id').value;
        const departmentId = document.getElementById('department_id').value;
        
        if (!academicId) {
            alert('Please select an academic year first');
            return;
        }

        const url = `{{ route('dean.reports.print-cleared') }}?academic_id=${academicId}&department_id=${departmentId}`;
        window.open(url, '_blank');
    });

    function loadNotClearedStudents(academicId, departmentId) {
        // Show loading state
        const tbody = document.querySelector('#not_cleared_table tbody');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Loading...</td></tr>';

        fetch('{{ route("dean.reports.students-not-cleared") }}', {
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
                            // Determine badge color based on department type
                            let badgeClass = 'badge-secondary';
                            if (dept.includes('SCHOOL OF')) {
                                badgeClass = 'badge-primary';
                            } else if (dept.includes('BUSINESS AFFAIRS') || dept.includes('CLINIC') ||
                                     dept.includes('LIBRARY') || dept.includes('STUDENT AFFAIRS') ||
                                     dept.includes('RESEARCH') || dept.includes('FOODLAB')) {
                                badgeClass = 'badge-info';
                            }

                            // Shorten department names for better display
                            let shortName = dept;
                            if (dept.includes('BUSINESS AFFAIRS')) shortName = 'BAO';
                            else if (dept.includes('STUDENT AFFAIRS')) shortName = 'OSA';
                            else if (dept.includes('FOODLAB')) shortName = 'FOODLAB';
                            else if (dept.includes('LIBRARY')) shortName = 'LIBRARY';
                            else if (dept.includes('RESEARCH')) shortName = 'RESEARCH';
                            else if (dept.includes('CLINIC')) shortName = 'CLINIC';
                            else if (dept.includes('SCHOOL OF INFORMATION TECHNOLOGY')) shortName = 'SITE';
                            else if (dept.includes('SCHOOL OF ARTS')) shortName = 'SASTE';
                            else if (dept.includes('SCHOOL OF NURSING')) shortName = 'SNAHS';
                            else if (dept.includes('SCHOOL OF BUSINESS')) shortName = 'SBAHM';
                            else if (dept.includes('REGISTRAR')) shortName = 'REGISTRAR';
                            else if (dept.includes('GUIDANCE')) shortName = 'GUIDANCE';
                            else if (dept.includes('BOUTIQUE')) shortName = 'BOUTIQUE';
                            else if (dept.includes('CASHIER')) shortName = 'CASHIER';
                            else if (dept.includes('COMPUTER')) shortName = 'COMPUTER';
                            else if (dept.includes('ENGINEERING')) shortName = 'ENGINEERING';
                            else if (dept.includes('SCIENCE')) shortName = 'SCIENCE LAB';
                            else if (dept.includes('SPUP')) shortName = 'SPUP';

                            return `<span class="badge ${badgeClass}" style="font-size: 0.75em; margin: 2px 3px 2px 0; display: inline-block;">${shortName}</span>`;
                        }).join('');
                    } else {
                        pendingDepartmentsHtml = '<span class="text-muted">All cleared</span>';
                    }

                    const row = `
                        <tr>
                            <td>${student.student_number || 'N/A'}</td>
                            <td>${student.name || 'N/A'}</td>
                            <td>${student.department || 'N/A'}</td>
                            <td>${student.program || 'N/A'}</td>
                            <td>${student.year || 'N/A'}</td>
                            <td><span class="badge badge-${student.has_violations ? 'danger' : 'success'}">${student.has_violations ? 'Yes' : 'No'}</span></td>
                            <td><span class="badge badge-${student.clearance_locked ? 'danger' : 'success'}">${student.clearance_locked ? 'Yes' : 'No'}</span></td>
                            <td class="pending-departments-cell">${pendingDepartmentsHtml}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                let message = 'No students found who haven\'t cleared';
                if (data.suggestions && data.suggestions.length > 0) {
                    message += '<br><small class="text-info">' + data.suggestions.join('<br>') + '</small>';
                }
                if (data.total_students === 0) {
                    message = 'No students found for the selected academic year and filters';
                    if (data.suggestions && data.suggestions.length > 0) {
                        message += '<br><small class="text-info">' + data.suggestions.join('<br>') + '</small>';
                    }
                }
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">${message}</td></tr>`;
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

        fetch('{{ route("dean.reports.students-cleared") }}', {
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
                            <td>${student.student_number || 'N/A'}</td>
                            <td>${student.name || 'N/A'}</td>
                            <td>${student.department || 'N/A'}</td>
                            <td>${student.program || 'N/A'}</td>
                            <td>${student.year || 'N/A'}</td>
                            <td>${student.cleared_date || 'N/A'}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                let message = 'No students found who have cleared';
                if (data.total_students === 0) {
                    message = 'No students found for the selected academic year and filters';
                }
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">${message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error loading cleared students:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        });
    }

    function loadViolationsReport(academicId, departmentId) {
        fetch('{{ route("dean.reports.violations") }}', {
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
        fetch('{{ route("dean.reports.graduated-students") }}', {
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
