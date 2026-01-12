@extends('officer.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">


    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header no-gutters has-tab">
            <h2 class="font-weight-normal">Setting</h2>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab-account">Account</a>
                </li>

            </ul>
        </div>
        @include('officer.alert.alert_message')
        @include('officer.alert.alert_danger')
        <div class="container">
            <div class="tab-content m-t-15">
                <div class="tab-pane fade show active" id="tab-account">
                    <div class="card">
                        <div class="card-header">

                            <h4 class="card-title">Basic Personal Infomation</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{route('officer.update_setting', $user->id)}}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="media align-items-center">
                                    <div class="avatar avatar-image  m-h-10 m-r-15" style="height: 80px; width: 80px">
                                        <img id="showImage"
                                            src="{{(!empty($user->picture)) ? url('assets/images/upload/admin-images/'. $user->picture): url('assets/images/avatars/profile-picture.png')}}"
                                            alt="">
                                    </div>
                                    <div class="m-l-20 m-r-20">
                                        <h5 class="m-b-5 font-size-18">Change Avatar</h5>
                                        <p class="opacity-07 font-size-13 m-b-0">
                                            Recommended Dimensions: <br>
                                            120x120 Max fil size: 5MB
                                        </p>
                                    </div>
                                    <div>
                                        <input type="file" class="btn btn-info btn-tone m-r-5" name="picture"
                                            id="image">


                                    </div>
                                </div>
                                <hr class="m-v-25">

                                <div class="form-group row">
                                    <label for="student_id" class="col-sm-2 col-form-label">Employee ID</label>
                                    <div class="col-sm-2">
                                        <input  class="form-control" readonly
                                        placeholder="Employee ID" value="{{$user->employee_id}}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="firstname" class="col-sm-2 col-form-label">First Name</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name" value="{{$user->firstname}}">
                                    </div>
                                    @error('firstname')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                </div>


                                <div class="form-group row">
                                    <label for="middlename" class="col-sm-2 col-form-label">Middle Name</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="middlename" name="middlename" 
                                        placeholder="Middle Name" value="{{$user->middlename}}">
                                    </div>
                                    @error('middlename')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                </div>


                                <div class="form-group row">
                                    <label for="lastname" class="col-sm-2 col-form-label">Last Name</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name" value="{{$user->lastname}}">
                                    </div>
                                    @error('lastname')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                </div>

                                <div class="form-group row">
                                    <label for="suffix_name" class="col-sm-2 col-form-label">Suffix Name</label>
                                    <div class="col-sm-3">
                                        <select id="suffix_name" name="suffix_name" class="form-control">
                                            <option selected disabled>Suffix Name</option>
                                            <option value="Jr." {{ old('suffix_name', $user->suffix_name ?? '') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                            <option value="Sr." {{ old('suffix_name', $user->suffix_name ?? '') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                            <option value="I" {{ old('suffix_name', $user->suffix_name ?? '') == 'I' ? 'selected' : '' }}>I</option>
                                            <option value="II" {{ old('suffix_name', $user->suffix_name ?? '') == 'II' ? 'selected' : '' }}>II</option>
                                            <option value="III" {{ old('suffix_name', $user->suffix_name ?? '') == 'III' ? 'selected' : '' }}>III</option>
                                            <option value="IV" {{ old('suffix_name', $user->suffix_name ?? '') == 'IV' ? 'selected' : '' }}>IV</option>
                                            <option value="V" {{ old('suffix_name', $user->suffix_name ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        </select>
                                    </div>
                                    @error('suffix_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-success btn-tone m-r-5">Update
                                        Information</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.update_password', $user->id) }}"
                                enctype="multipart/form-data">

                                @csrf
                                @method('PUT')
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="oldPassword">Old Password:</label>
                                        <input type="password" class="form-control" id="oldPassword" name="oldPassword"
                                            placeholder="Old Password" required>
                                        @error('oldPassword')
                                        <span class="badge badge-pill badge-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="newPassword">New Password:</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword"
                                            placeholder="New Password" required>
                                        @error('newPassword')
                                        <span class="badge badge-pill badge-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="confirmPassword">Confirm
                                            Password:</label>
                                        <input type="password" class="form-control" id="confirmPassword"
                                            name="newPassword_confirmation" placeholder="Confirm Password" required>
                                        @error('confirmPassword')
                                        <span class="badge badge-pill badge-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-success btn-tone mt-4">Change
                                            Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    <!-- Footer START -->
    @include('officer.layout.footer')
    <!-- Footer END -->

</div>
<!-- Page Container END -->

@endsection