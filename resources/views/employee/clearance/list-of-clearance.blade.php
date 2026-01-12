@extends('employee.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Clearance</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="">Clearance</a>
                    <span class="breadcrumb-item active">List of Clearance</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
      
        <div class="card">
            <div class="card-body">
              
                <h4>Clearance Management</h4>
                <div class="m-t-25">
                    <div class="table-responsive table-responsive-custom">
                        <table id="data-table" class="table table-hover table-white">
                            <thead class="thead-white">
                            <tr>
                                <th>Student Number</th>
                                <th>Student Name</th>
                                <th>Department</th>
                                <th>Academic Year</th>
                              
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clearances as $clearance)
                            <tr>
                                 <td>{{ $clearance->student->student_number }}</td>
                                <td>{{ $clearance->student->user->lastname .' '. $clearance->student->user->firstname .' '. $clearance->student->user->middlename }}</td>
                                  <td>{{ $clearance->department->department_name }}</td>
                                <td>{{ $clearance->academicYear->academic_year }}</td>

                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Student Number</th>
                                <th>Student Name</th>
                                <th>Department</th>
                                <th>Academic Year</th>
                            </tr>
                        </tfoot>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('employee.layout.footer')

</div>
<!-- Page Container END -->

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables for clearance page
        $('#data-table').DataTable();
    });
</script>
@endsection

