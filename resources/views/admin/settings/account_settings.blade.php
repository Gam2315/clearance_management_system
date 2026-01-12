@extends('admin.layout.header')

@section('main-content')
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
        @include('alert.alert-message')

        <div class="container">
            <div class="tab-content m-t-15">
                <div class="tab-pane fade show active" id="tab-account">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Basic Infomation</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.update.profile')}}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('POST')
                                <div class="media align-items-center">
                                    <div class="avatar avatar-image  m-h-10 m-r-15" style="height: 80px; width: 80px">
                                        <img src="{{ asset($user->profile_photo ?? 'assets/images/avatars/thumb-3.jpg') }}"
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
                                        <input type="file" name="picture" id="picture" class="form-control">
                                    </div>
                                </div>
                                <hr class="m-v-25">

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-semibold" for="username">User Name:</label>
                                        <input type="text" class="form-control" name="username" id="username"
                                            placeholder="User Name" value="{{ $user->name }}">
                                        @error('username')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="font-weight-semibold" for="email">Email:</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="email" value="{{ $user->email }}">
                                        @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="font-weight-semibold" for="firstname">First Name:</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname"
                                            placeholder="First Name" value="{{ $user->userProfile->firstname ?? '' }}">
                                        @error('firstname')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="font-weight-semibold" for="middlename">Middle Name</label>
                                        <input type="text" class="form-control" name="middlename" id="middlename"
                                            value="{{ $user->userProfile->middlename ?? '' }}"
                                            placeholder="Middle Name">
                                        @error('middlename')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="font-weight-semibold" for="lastname">Last Name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname"
                                            value="{{ $user->userProfile->lastname ?? '' }}" placeholder="Last Name">
                                        @error('lastname')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary btn-tone m-r-5">Update
                                            Account</button>
                                        <a class="btn btn-danger btn-tone m-r-5"
                                            href="{{route('admin.student.list-of-student')}}" role="button">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.change.password') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="oldPassword">Old Password:</label>
                                        <input type="password" class="form-control" id="oldPassword" name="old_password"
                                            placeholder="Old Password" required>
                                            @error('oldPassword')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="newPassword">New Password:</label>
                                        <input type="password" class="form-control" id="newPassword" name="new_password"
                                            placeholder="New Password" required>
                                            @error('new_password')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="font-weight-semibold" for="confirmPassword">Confirm
                                            Password:</label>
                                        <input type="password" class="form-control" id="confirmPassword"
                                            name="new_password_confirmation" placeholder="Confirm Password" required>
                                            @error('confirm_password')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-primary m-t-30">Change Password</button>
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
    @include('admin.layout.footer')
    <!-- Footer END -->

</div>
@endsection