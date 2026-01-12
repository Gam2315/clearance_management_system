<!-- Core Vendors JS -->
<script src="{{asset('assets/js/vendors.min.js')}}"></script>

<!-- page js -->
<script src="{{asset('assets/vendors/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/vendors/datatables/dataTables.bootstrap.min.js')}}"></script>
<script src="{{asset('assets/js/pages/datatables.js')}}"></script>
<!-- Core JS -->
<script src="{{asset('assets/js/app.min.js')}}"></script>


<script>
    $('#data-table').DataTable();
</script>

<script src="{{asset('assets/js/jquery-3.6.0.min.js')}}"></script>
<script>
    $(document).ready(function () {
    $('#department_id').on('change', function () {
        var department_id = $(this).val();
        var selectedProgram = "{{ $student->program ?? '' }}"; // Fetch stored program_id

        if (department_id) {
            $.ajax({
                url: "{{ route('get.courses', '') }}/" + department_id,
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    $('#program').html('<option selected disabled>Loading Program...</option>');
                },
                success: function (data) {
                    $('#program').empty().append('<option selected disabled>Choose Program</option>');

                    if (data.length > 0) {
                        $.each(data, function (key, value) {
                            var selected = (value.id == selectedProgram) ? 'selected' : '';
                            $('#program').append('<option value="' + value.id + '" ' + selected + '>' + value.course_name + '</option>');
                        });
                    } else {
                        $('#program').append('<option selected disabled>No programs available</option>');
                    }
                },
                error: function () {
                    $('#program').html('<option selected disabled>Error loading programs</option>');
                }
            });
        } else {
            $('#program').html('<option selected disabled>Choose Program</option>');
        }
    });

    // Trigger change event to populate the existing program when editing
    var oldDepartment = "{{ old('department_id', $student->department_id ?? '') }}";
    if (oldDepartment) {
        $('#department_id').val(oldDepartment).trigger('change');
    }
});

</script>

<script>
    $(document).ready(function() {
    $('select[name="department_id"]').change(function() {
        let departmentId = $(this).val();
        $.ajax({
            url: '/fetch-programs/' + departmentId,
            type: 'GET',
            success: function(response) {
                $('#data-table tbody').empty(); // Clear existing data
                $.each(response.programs, function(index, program) {
                    $('#data-table tbody').append(`
                        <tr>
                            <td>${program.department.department_name}</td>
                            <td>${program.course_code}</td>
                            <td>${program.course_name}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="/program/edit-program-information/${program.id}" class="btn btn-warning btn-tone m-r-5">
                                        <i class="anticon anticon-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal" data-target="#deleteModal-${program.id}">
                                        <i class="anticon anticon-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            }
        });
    });
});

</script>

<script type="text/javascript">
$(document).ready(function(){
    $('#image').change(function(e){
        var reader = new FileReader();
        reader.onload = function(e){
            $('#showImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    });
})

</script>



<script>
    $(document).ready(function() {
        // Setup CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // When department selection changes
        $('#filter-department').on('change', function() {
            var departmentId = $(this).val();
            var programSelect = $('#filter-program');
            
            if (departmentId) {
                // Enable and load programs for the selected department
                $.ajax({
                    url: "{{ route('get.courses', '') }}/" + departmentId,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        programSelect.html('<option selected disabled>Loading...</option>');
                        programSelect.prop('disabled', true);
                    },
                    success: function(data) {
                        programSelect.empty().append('<option value="">All Programs</option>');
                        
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                programSelect.append('<option value="' + value.id + '">' + value.course_name + '</option>');
                            });
                            programSelect.prop('disabled', false);
                        } else {
                            programSelect.append('<option disabled>No programs available</option>');
                        }
                    },
                    error: function() {
                        programSelect.html('<option selected disabled>Error loading programs</option>');
                    }
                });
            } else {
                // Reset program dropdown if no department selected
                programSelect.html('<option selected>Select Program</option>');
                programSelect.prop('disabled', true);
            }
            
            // Filter the table based on department
            filterStudents();
        });
        
        // When program selection changes
        $('#filter-program').on('change', function() {
            filterStudents();
        });
        
        // Function to filter students table
        function filterStudents() {
            var departmentId = $('#filter-department').val();
            var programId = $('#filter-program').val();
            
            $('#data-table tbody tr').each(function() {
                var row = $(this);
                var showRow = true;
                
                // Get department name from the selected option
                var selectedDepartmentName = departmentId ? 
                    $('#filter-department option:selected').text().trim() : '';
                
                // Get program name from the selected option
                var selectedProgramName = programId ? 
                    $('#filter-program option:selected').text().trim() : '';
                
                // Get values from the current row
                var rowDepartment = row.find('td:nth-child(3)').text().trim();
                var rowProgram = row.find('td:nth-child(4)').text().trim();
                
                // Apply department filter
                if (departmentId && rowDepartment !== selectedDepartmentName) {
                    showRow = false;
                }
                
                // Apply program filter
                if (programId && rowProgram !== selectedProgramName) {
                    showRow = false;
                }
                
                // Show or hide the row
                row.toggle(showRow);
            });
        }
    });
</script>





<script>
    $(document).ready(function() {
        // Check if we're in a restricted department
        @php
            $user = Auth::user();
            $employeeDepartmentId = $user->department_id;
            $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments
            $isRestricted = in_array($employeeDepartmentId, $restrictedDepartments);
        @endphp
        
        var isRestricted = {{ $isRestricted ? 'true' : 'false' }};
        
        // Check which page we're on by looking at the URL
        var currentUrl = window.location.pathname;
        var isStudentPage = currentUrl.includes('student');
        var isClearancePage = currentUrl.includes('clearance');
        
        // Only set up department change handler for non-restricted departments
        if (!isRestricted) {
            $('#filter-department').on('change', function() {
                var departmentId = $(this).val();
                if (departmentId) {
                    $.ajax({
                        url: '/get-courses/' + departmentId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#filter-program').empty().prop('disabled', false);
                            $('#filter-program').append('<option value="">Select Program</option>');
                            $.each(data, function(key, value) {
                                $('#filter-program').append('<option value="' + value.id + '">' + value.course_name + '</option>');
                            });
                            
                            // Only filter students on the student page
                            if (isStudentPage) {
                                filterStudents();
                            }
                        }
                    });
                } else {
                    $('#filter-program').empty().prop('disabled', true);
                    $('#filter-program').append('<option value="">Select Program</option>');
                    
                    // Only filter students on the student page
                    if (isStudentPage) {
                        filterStudents();
                    }
                }
            });
        }
        
        // Program change handler works for everyone
        $('#filter-program').on('change', function() {
            // Only filter students on the student page
            if (isStudentPage) {
                filterStudents();
            }
        });
        
        // Function to filter students based on selections
        function filterStudents() {
            // Only run this function on the student page
            if (!isStudentPage) return;
            
            var departmentId = isRestricted ? {{ $employeeDepartmentId }} : $('#filter-department').val();
            var programId = $('#filter-program').val();
            
            console.log("Filtering with department:", departmentId, "program:", programId);
            
            // Show all rows first
            $('#data-table tbody tr').show();
            
            // Then filter by department if selected
            if (departmentId) {
                $('#data-table tbody tr').each(function() {
                    var row = $(this);
                    var rowDeptId = row.find('td:eq(2)').attr('data-dept-id');
                    
                    if (rowDeptId != departmentId) {
                        row.hide();
                    }
                });
            }
            
            // Then filter by program if selected
            if (programId) {
                $('#data-table tbody tr:visible').each(function() {
                    var row = $(this);
                    var rowProgramId = row.find('td:eq(3)').attr('data-program-id');
                    
                    if (rowProgramId != programId) {
                        row.hide();
                    }
                });
            }
            
            // Show a message if no results
            if ($('#data-table tbody tr:visible').length === 0) {
                if ($('#no-results-row').length === 0) {
                    $('#data-table tbody').append('<tr id="no-results-row"><td colspan="7" class="text-center">No students match the selected filters</td></tr>');
                }
            } else {
                $('#no-results-row').remove();
            }
        }
        
        // Initial filtering (for restricted departments)
        if (isRestricted && isStudentPage) {
            filterStudents();
        }
        
        // Initialize DataTables for both pages with responsive configuration
        if ($('#data-table').length > 0 && !$.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: -1 },
                    { responsivePriority: 10000, targets: '_all' }
                ]
            });
        }

        // Add dashboard card hover effects
        $('.dashboard-card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    });
</script>


<script>
    $(document).ready(function() {
        // Check if we're in a restricted department
        @php
            $user = Auth::user();
            $employeeDepartmentId = $user->department_id;
            $restrictedDepartments = [1, 2, 3, 4]; // SITE, SASTE, SNAHS departments
            $isRestricted = in_array($employeeDepartmentId, $restrictedDepartments);
        @endphp
        
        var isRestricted = {{ $isRestricted ? 'true' : 'false' }};
        
        // Check which page we're on by looking at the URL
        var currentUrl = window.location.pathname;
        var isStudentPage = currentUrl.includes('student');
        var isClearancePage = currentUrl.includes('clearance');
        
        // Only set up department change handler for non-restricted departments
        if (!isRestricted) {
            $('#filter-department').on('change', function() {
                var departmentId = $(this).val();
                if (departmentId) {
                    $.ajax({
                        url: '/get-courses/' + departmentId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#filter-program').empty().prop('disabled', false);
                            $('#filter-program').append('<option value="">Select Program</option>');
                            $.each(data, function(key, value) {
                                $('#filter-program').append('<option value="' + value.id + '">' + value.course_name + '</option>');
                            });
                            
                            // Only filter students on the student page
                            if (isStudentPage) {
                                filterStudents();
                            }
                        }
                    });
                } else {
                    $('#filter-program').empty().prop('disabled', true);
                    $('#filter-program').append('<option value="">Select Program</option>');
                    
                    // Only filter students on the student page
                    if (isStudentPage) {
                        filterStudents();
                    }
                }
            });
        }
        
        // Program change handler works for everyone
        $('#filter-program').on('change', function() {
            // Only filter students on the student page
            if (isStudentPage) {
                filterStudents();
            }
        });
        
        // Function to filter students based on selections
        function filterStudents() {
            // Only run this function on the student page
            if (!isStudentPage) return;
            
            var departmentId = isRestricted ? {{ $employeeDepartmentId }} : $('#filter-department').val();
            var programId = $('#filter-program').val();
            
            console.log("Filtering with department:", departmentId, "program:", programId);
            
            // Show all rows first
            $('#data-table tbody tr').show();
            
            // Then filter by department if selected
            if (departmentId) {
                $('#data-table tbody tr').each(function() {
                    var row = $(this);
                    var rowDeptId = row.find('td:eq(1)').attr('data-dept-id');

                    if (rowDeptId != departmentId) {
                        row.hide();
                    }
                });
            }

            // Then filter by program if selected
            if (programId) {
                $('#data-table tbody tr:visible').each(function() {
                    var row = $(this);
                    var rowProgramId = row.find('td:eq(2)').attr('data-program-id');

                    if (rowProgramId != programId) {
                        row.hide();
                    }
                });
            }

            
            // Show a message if no results
            if ($('#data-table tbody tr:visible').length === 0) {
                if ($('#no-results-row').length === 0) {
                    $('#data-table tbody').append('<tr id="no-results-row"><td colspan="7" class="text-center">No students match the selected filters</td></tr>');
                }
            } else {
                $('#no-results-row').remove();
            }
        }
        
        // Initial filtering (for restricted departments)
        if (isRestricted && isStudentPage) {
            filterStudents();
        }
        
        // Initialize DataTables for both pages with responsive configuration
        if ($('#data-table').length > 0 && !$.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: -1 },
                    { responsivePriority: 10000, targets: '_all' }
                ]
            });
        }

        // Add dashboard card hover effects
        $('.dashboard-card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    });
</script>