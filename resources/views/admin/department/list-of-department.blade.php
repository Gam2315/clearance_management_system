@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Department</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.department.list-of-department') }}">Department</a>
                    <span class="breadcrumb-item active">List of Department</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Department Information</h4>
                <div class="m-t-25">
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Department Code</th>
                                <th>Department Name</th>
                                <th>Department Head</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $department)
                            <tr>
                                <td>{{ $department->department_code }}</td>
                                <td>{{ $department->department_name}}</td>
                                <td>{{ $department->department_head }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{route('admin.department.edit_information_department', $department->id)}}"
                                            class="btn btn-warning btn-tone m-r-5"><i
                                                class="anticon anticon-edit"></i></a>

                                        <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal"
                                            data-target="#deleteModal-{{ $department->id }}">
                                            <i class="anticon anticon-delete"></i>
                                        </button>
                                    </div>

                                </td>

                                <div class="modal fade" id="deleteModal-{{ $department->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel-{{ $department->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $department->id }}">Delete
                                                    Department</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="anticon anticon-close"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.department.delete_information_department', $department->id)}}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this department?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning btn-tone m-r-5"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-tone m-r-5">Delete
                                                        Department</button>
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
                                <th>Department Code</th>
                                <th>Department Name</th>
                                <th>Department Head</th>
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