@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Program</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('student.list-of-students') }}">Program</a>
                    <span class="breadcrumb-item active">List of Program</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Program Information</h4>
                <div class="row m-b-30">
                    <div class="col-lg-8">
                        <div class="d-md-flex">
                            <div class="m-b-10">
                                <select class="custom-select" name="department_id" style="min-width: 180px;">
                                    <option selected disabled>Choose Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="m-t-25">
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Program Code</th>
                                <th>Program Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($program as $programs)
                            <tr>
                                <td>{{ $programs->department->department_name }}</td>
                                <td>{{ $programs->course_code}}</td>
                                <td>{{ $programs->course_name }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{route('program.edit_information_program', $programs->id)}}"
                                            class="btn btn-warning btn-tone m-r-5"><i
                                                class="anticon anticon-edit"></i></a>

                                        <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal"
                                            data-target="#deleteModal-{{ $programs->id }}">
                                            <i class="anticon anticon-delete"></i>
                                        </button>
                                    </div>

                                </td>

                                <div class="modal fade" id="deleteModal-{{ $programs->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel-{{ $programs->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $programs->id }}">Delete
                                                    Program</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="anticon anticon-close"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('program.delete_information_program', $programs->id)}}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this program?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning btn-tone m-r-5"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-tone m-r-5">Delete
                                                        Program</button>
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
                                <th>Department</th>
                                <th>Program Code</th>
                                <th>Program Name</th>
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