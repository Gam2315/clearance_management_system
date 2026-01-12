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
        var selectedProgram = "{{ $student->courses ?? '' }}"; // Fetch stored program_id

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
    $(document).ready(function () {
    $('#dsn_id').on('change', function () {
        var dsn_id = $(this).val();
        var selectedPosition= "{{ $student->designation ?? '' }}"; // Fetch stored program_id

        if (department_id) {
            $.ajax({
                url: "{{ route('get.data', '') }}/" + dsn_id,
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    $('#position_id').html('<option selected disabled>Loading Position...</option>');
                },
                success: function (data) {
                    $('#position_id').empty().append('<option selected disabled>Choose Position</option>');

                    if (data.length > 0) {
                        $.each(data, function (key, value) {
                            var selected = (value.id == selectedPosition) ? 'selected' : '';
                            $('#position_id').append('<option value="' + value.id + '" ' + selected + '>' + value.position_title + '</option>');
                        });
                    } else {
                        $('#position_id').append('<option selected disabled>No position available</option>');
                    }
                },
                error: function () {
                    $('#position_id').html('<option selected disabled>Error loading position</option>');
                }
            });
        } else {
            $('#position_id').html('<option selected disabled>Choose Position</option>');
        }
    });

    // Trigger change event to populate the existing program when editing
    var oldDesignation = "{{ old('designation_id', $user->designation_id ?? '') }}";
    if (oldDesignation) {
        $('#dsn_id').val(oldDesignation).trigger('change');
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