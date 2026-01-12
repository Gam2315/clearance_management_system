@extends('admin.layout.header')

@section('main-content')
<!-- Page Container START -->
<div class="page-container">
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Students</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.students.list-of-students') }}">Student</a>
                    <span class="breadcrumb-item active">List of Students</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <div class="row m-b-30">
                    <div class="col-lg-8">
                        <div class="d-md-flex">

                            <div class="form-group col-md-10">
                                <label for="inputState">Select Department</label>
                                <select id="filter-department" class="custom-select" style="min-width: 180px;">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="inputState">Select Program</label>
                                <select id="filter-program" class="custom-select" style="min-width: 180px;" disabled>
                                    <option selected>Select Program</option>

                                </select>
                            </div>

                        </div>
                    </div>

                </div>
                <h4>Student Personal Information</h4>
                <div class="m-t-25">
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Student Number</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Academic Year</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>{{ $student->student_number }}</td>
                                <td>{{ $student->user->lastname . ', ' . $student->user->firstname . ' ' .
                                    $student->user->middlename . ' ' . $student->user->suffix_name }}</td>

                                <td>{{ $student->department->department_name ?? 'No Department' }}</td>
                                <td>{{ $student->courses->course_name }}</td>
                                <td>{{ $student->year }} Year</td>
                                <td>{{ $student->AY->academic_year }} -{{ $student->AY->semester }} </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{route('admin.student.student-profile',$student->id)}}"
                                            class="btn btn-success btn-tone m-r-5"><i
                                                class="anticon anticon-eye"></i></a>
                                        <a href="{{route('admin.student.edit_information_student', $student->id)}}"
                                            class="btn btn-warning btn-tone m-r-5"><i
                                                class="anticon anticon-edit"></i></a>

                                        <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal"
                                            data-target="#deleteModal-{{ $student->id }}">
                                            <i class="anticon anticon-delete"></i>
                                        </button>
                                    </div>
                                </td>

                                <div class="modal fade" id="deleteModal-{{ $student->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel-{{ $student->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $student->id }}">Delete
                                                    Student</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="anticon anticon-close"></i>
                                                </button>
                                            </div>
                                            <form
                                                action="{{route('admin.student.delete_information_student', $student->id)}}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this student?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning btn-tone m-r-5"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-tone m-r-5">Delete
                                                        Student</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Student Number</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Academic Year</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->
    @include('admin.layout.footer')

</div>
<!-- Page Container END -->

@endsection