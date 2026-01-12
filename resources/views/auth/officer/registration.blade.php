<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SPUP | Registration</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/spup-favicon.ico')}}">

    <!-- page css -->

    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">

</head>

<body>
    <div class="app">
        <div class="container-fluid">
            <div class="d-flex full-height p-v-20 flex-column justify-content-between">
                <div class="d-none d-md-flex p-h-40">
                    <img src="{{asset('assets/images/logo/spup.png')}}" alt="">
                </div>
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-6 d-none d-md-block">
                            <img class="img-fluid" src="" alt="">
                        </div>
                        <div class="m-l-auto col-md-5">
                            @include('alert.alert-message')
                            <div class="card">
                                <div class="card-body">
                                    <h2 class="m-t-20">Sign In</h2>
                                    <p class="m-b-30">Personal Information</p>
                                    <form method="POST" action="{{ route('auth.adviser.store') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="username">Username:</label>
                                            <input type="text" required name="username" class="form-control"
                                                id="username" placeholder="Enter Username">
                                            @error('username')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="firstname">First Name:</label>
                                            <input type="text" required name="firstname" class="form-control"
                                                id="firstname" placeholder="Enter First Name">
                                            @error('firstname')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="middlename">Middle Name:</label>
                                            <input type="text" required name="middlename" class="form-control"
                                                id="middlename" placeholder="Enter Middle Name">
                                            @error('middlename')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="lastname">Last Name:</label>
                                            <input type="text" required name="lastname" class="form-control"
                                                id="lastname" placeholder="Enter Last Name">
                                            @error('lastname')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="lastname">Suffix:</label>
                                            <select name="suffix" required id="suffix" class="form-control">
                                                <option selected disabled>Choose Suffix</option>
                                                <option value="Jr.">Jr.</option>
                                                <option value="Sr.">Sr.</option>
                                                <option value="I">I</option>
                                                <option value="II">II</option>
                                                <option value="III">III</option>
                                                <option value="IV">IV</option>
                                                <option value="V">V</option>
                                            </select>
                                            @error('suffix')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="email">Email (Optional):</label>
                                            <input type="text" name="email" class="form-control" id="email"
                                                placeholder="Enter Email">
                                            @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="employee_id">Employee ID:</label>
                                            <input type="text" required name="employee_id" class="form-control"
                                                id="employee_id" placeholder="Enter Employee ID">
                                            @error('employee_id')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                       
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="password">Password:</label>
                                            <input type="password" class="form-control" required
                                                autocomplete="new-password" name="password" id="password"
                                                placeholder="Enter Password">
                                            @error('password')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="confirmPassword">Confirm
                                                Password:</label>
                                            <input type="password" class="form-control" required
                                                autocomplete="new-password" name="password_confirmation"
                                                id="password_confirmation" placeholder="Enter Confirm Password">
                                            @error('password_confirmation')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <div class="d-flex align-items-center justify-content-between p-t-15">
                                                <div class="checkbox">

                                                    <label for="checkbox"><span>I have already the <a
                                                                href="{{route('auth.student.login-student')}}">account?</a></span></label>
                                                </div>
                                                <button class="btn btn-primary">Register As Adivser</button>


                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-none d-md-flex  p-h-40 justify-content-between">
                    <span class="">Copyright © {{now()->year }} - {{now()->addYear()->year}} School of Information
                        Technology & Engineering</span>
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a class="text-dark text-link" href="">Legal</a>
                        </li>
                        <li class="list-inline-item">
                            <a class="text-dark text-link" href="">Privacy</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <!-- Core Vendors JS -->
    <script src="{{asset('assets/js/vendors.min.js')}}"></script>

    <!-- page js -->

    <!-- Core JS -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>

</body>

</html>