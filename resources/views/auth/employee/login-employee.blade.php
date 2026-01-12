<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SPUP | Login</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/favicon.png')}}">

    <!-- page css -->
    
    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">

</head>


<body>
    <div class="app">
        <div class="container-fluid">
            <div class="d-flex full-height p-v-20 flex-column justify-content-between">
                <div class="d-none d-md-flex p-h-40">
                    <img src="{{asset('assets/images/logo/spup-fold.png')}}" alt="">
                </div>
                <div class="container">
                    <div class="row align-items-center">
                        
                        <div class="m-l-auto col-md-5">
                            @include('auth.alert.alert_danger')
                            @include('auth.alert.alert_message')
                            <div class="card">
                                <div class="card-body">
                                    <h2 class="m-t-20">Employee Login</h2>
                                    <p class="m-b-30">To access the Employee panel, please login using your employee credentials.</p>
                                    <form method="POST" action="{{ route('auth.employee.login') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="login">Employee ID:</label>
                                            <div class="input-affix">
                                                <i class="prefix-icon anticon anticon-user"></i>
                                                <input type="text" name="login" class="form-control" id="login" required
                                                    autofocus autocomplete="username" placeholder="Employee ID">
                                            </div>
                                             @error('login')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold" for="password">Password:</label>

                                            <div class="input-affix m-b-10">
                                                <i class="prefix-icon anticon anticon-lock"></i>
                                                <input type="password" name="password" required
                                                    autocomplete="current-password" class="form-control"
                                                    placeholder="Password">
                                            </div>
                                             @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                       <div class="form-group row">
                                            <div class="col-sm-10">
                                                <button type="submit" class="btn btn-primary btn-tone m-r-5">Log In</button>
                                                <a class="btn btn-danger btn-tone m-r-5" href="{{url('/')}}" role="button">Back</a>
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
    <script src="assets/js/vendors.min.js"></script>

    <!-- page js -->

    <!-- Core JS -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>