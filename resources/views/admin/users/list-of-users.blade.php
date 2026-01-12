@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of User Account</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.users.list-of-users') }}">Users</a>
                    <span class="breadcrumb-item active">List of User Account</span>
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
                           
                        </div>
                    </div>

                </div>
                <h4>User Personal Information</h4>
                <div class="m-t-25">
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->employee_id }}</td>
                                <td>{{ $user->lastname . ', ' . $user->firstname . ' ' .
                                    $user->middlename . ' ' . $user->suffix_name }}</td>

                                <td>{{ $user->department->department_name ?? 'No Department' }}</td>
                                <td>{{ $user->role }}</td>
                                <td>{{ $user->status }}</td>
                                <td>
                                    <div class="btn-group">

                                        <a href="{{route('admin.users.edit_user_account', $user->id)}}"
                                            class="btn btn-warning btn-tone m-r-5"><i
                                                class="anticon anticon-edit"></i></a>

                                        <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal"
                                            data-target="#deleteModal-{{ $user->id }}">
                                            <i class="anticon anticon-delete"></i>
                                        </button>
                                    </div>

                                </td>

                                <div class="modal fade" id="deleteModal-{{ $user->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel-{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $user->id }}">Delete
                                                    User Account</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="anticon anticon-close"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.users.delete_user_account', $user->id)}}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this user account?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning btn-tone m-r-5"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-tone m-r-5">Delete
                                                        User Account</button>
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
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
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