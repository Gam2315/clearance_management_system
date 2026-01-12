@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Create New Officer User Account</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.users.list-of-users') }}">User Account</a>
                    <span class="breadcrumb-item active">Create New Officer User Account</span>
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
                    <form method="POST" action="{{route('admin.users.store_new_officer_user')}}" enctype="multipart/form-data">
                        @csrf
                         <div class="form-group row">
                            <label for="firstname" class="col-sm-2 col-form-label">First Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                    placeholder="Enter First Name" value="{{ old('firstname') }}">
                            </div>
                            @error('firstname')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="middlename" class="col-sm-2 col-form-label">Middle Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="middlename" name="middlename"
                                    placeholder="Enter Middle Name" value="{{ old('middlename') }}">
                            </div>
                            @error('middlename')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="lastname" class="col-sm-2 col-form-label">Last Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                    placeholder="Enter Last Name" value="{{ old('lastname') }}">
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
                                    <option value="Jr." {{ old('suffix_name') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                    <option value="Sr." {{ old('suffix_name') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                    <option value="I" {{ old('suffix_name') == 'I' ? 'selected' : '' }}>I</option>
                                    <option value="II" {{ old('suffix_name') == 'II' ? 'selected' : '' }}>II</option>
                                    <option value="III" {{ old('suffix_name') == 'III' ? 'selected' : '' }}>III</option>
                                    <option value="IV" {{ old('suffix_name') == 'IV' ? 'selected' : '' }}>IV</option>
                                    <option value="V" {{ old('suffix_name') == 'V' ? 'selected' : '' }}>V</option>
                                </select>
                            </div>
                            @error('suffix_name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <label for="student_id" class="col-sm-2 col-form-label">Student ID</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="student_id" name="student_id"
                                    placeholder="Enter Student ID" value="{{ old('student_id') }}">
                            </div>
                            @error('student_id')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <label for="department_id" class="col-sm-2 col-form-label">Department</label>
                            <div class="col-sm-5">
                                <select id="department_id" name="department_id" class="form-control">
                                    <option selected disabled>Choose Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department') == $department->id ? 'selected' : '' }}>
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
                            <label for="program" class="col-sm-2 col-form-label">Program</label>
                            <div class="col-sm-5">
                                <select id="program" name="program" class="form-control">
                                    <option selected disabled>Choose Program</option>
                                </select>
                            </div>
                            @error('program')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                         <div class="form-group row">
                            <label for="dsn_id" class="col-sm-2 col-form-label">Designation</label>
                            <div class="col-sm-5">
                                <select id="dsn_id" name="dsn_id" class="form-control">
                                    <option selected >Choose Desgination</option>
                                    @foreach($designation as $designations)
                                        <option value="{{ $designations->id }}" {{ old('dsn_id') == $designations->id ? 'selected' : '' }}>
                                            {{ $designations->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('dsn_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                         </div>

                         <div class="form-group row">
                            <label for="position_id" class="col-sm-2 col-form-label">Position</label>
                            <div class="col-sm-5">
                                <select id="position_id" name="position_id" class="form-control">
                                    <option selected >Choose Position</option>
                                  
                                </select>
                            </div>
                            @error('position_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                         <div class="form-group row">
                            <label for="role" class="col-sm-2 col-form-label">Role</label>
                            <div class="col-sm-2">
                                <select id="role" name="role" class="form-control">
                                    <option value="" {{ old('role') == '' ? 'selected' : '' }}>Select Role</option>
                                    <option value="officer" {{ old('role') == 'officer' ? 'selected' : '' }}>PSG OFFICER</option>
                                </select>
                            </div>
                            @error('role')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        
                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('admin.users.list-of-users') }}" role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Save New User Account</button>
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