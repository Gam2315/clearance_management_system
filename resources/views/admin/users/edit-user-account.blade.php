@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Edit User Account</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.users.list-of-users') }}">User Account</a>
                    <span class="breadcrumb-item active">Edit User Account</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>User Account Information</h4>
                <div class="m-t-25">
                    <form method="POST" action="{{route('admin.users.update_user_account', $user->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="firstname" class="col-sm-2 col-form-label">First Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                    placeholder="Enter First Name" value="{{ old('firstname', $user->firstname ) }}">
                            </div>
                            @error('firstname')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="middlename" class="col-sm-2 col-form-label">Middle Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="middlename" name="middlename"
                                    placeholder="Enter Middle Name" value="{{ old('middlename', $user->middlename ) }}">
                            </div>
                            @error('middlename')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="lastname" class="col-sm-2 col-form-label">Last Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                    placeholder="Enter Last Name" value="{{ old('lastname',  $user->lastname ) }}">
                            </div>
                            @error('lastname')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="suffix_name" class="col-sm-2 col-form-label">Suffix Name</label>
                            <div class="col-sm-2">
                                <select id="suffix_name" name="suffix_name" class="form-control">
                                    <option value="">Suffix Name</option>
                                    <option value="Jr." {{ old('suffix_name', $user->suffix_name ?? '') == 'Jr.' ?
                                        'selected' : '' }}>Jr.</option>
                                    <option value="Sr." {{ old('suffix_name',$user->suffix_name ?? '') == 'Sr.' ?
                                        'selected' : '' }}>Sr.</option>
                                    <option value="I" {{ old('suffix_name', $user->suffix_name ?? '') == 'I' ?
                                        'selected' : '' }}>I</option>
                                    <option value="II" {{ old('suffix_name', $user->suffix_name ?? '') == 'II' ?
                                        'selected' : '' }}>II</option>
                                    <option value="III" {{ old('suffix_name', $user->suffix_name ?? '') == 'III' ?
                                        'selected' : '' }}>III</option>
                                    <option value="IV" {{ old('suffix_name', $user->suffix_name ?? '') == 'IV' ?
                                        'selected' : '' }}>IV</option>
                                    <option value="V" {{ old('suffix_name', $user->suffix_name ?? '') == 'V' ?
                                        'selected' : '' }}>V</option>

                                </select>
                            </div>
                            @error('suffix_name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <label for="employee_id" class="col-sm-2 col-form-label">Employee ID</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="employee_id" name="employee_id"
                                    placeholder="Enter Employee ID"
                                    value="{{ old('employee_id', $user->employee_id) }}">
                            </div>
                            @error('employee_id')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <label for="department_id" class="col-sm-2 col-form-label">Department</label>
                            <div class="col-sm-5">
                                <select id="department_id" name="department_id" class="form-control">
                                    <option selected disabled>Choose Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $user->
                                        department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('department_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <label for="status" class="col-sm-2 col-form-label">Role</label>
                            <div class="col-sm-2">
                                <select id="role" name="role" class="form-control">
                                    <option value="" {{ old('role')=='' ? 'selected' : '' }}>Select Role</option>

                                    <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : ''
                                        }}>Administrator</option>
                                    <option value="employee" {{ old('role', $user->role ?? '') == 'employee' ?
                                        'selected' : '' }}>Employee</option>
                                        <option value="dean" {{ old('role', $user->role ?? '') == 'dean' ?
                                        'selected' : '' }}>Department Head</option>
                                        <option value="adviser" {{ old('role', $user->role ?? '') == 'adviser' ?
                                        'selected' : '' }}>Schoolwide Adviser</option>
                                    <option value="officer" {{ old('role', $user->role ?? '') == 'officer' ?
                                        'selected' : '' }}>PSG Officer</option>

                                </select>
                            </div>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('admin.users.list-of-users') }}"
                                    role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Save New User
                                    Account</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->


@endsection