<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{config('app.name')}}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/favicon.png')}}">

    <!-- page css -->
    <link href="{{asset('assets/vendors/datatables/dataTables.bootstrap.min.css')}}" rel="stylesheet">
    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

</head>

<body>
    <div class="app">
        <div class="layout">
            <!-- Header START -->
            <div class="header">
                <div class="logo logo-dark">
                    <a href="{{route('dean.dashboard')}}">
                        <img src="{{asset('assets/images/logo/spup.png')}}" alt="Logo">
                        <img class="logo-fold" src="{{asset('assets/images/logo/spup-fold.png')}}" alt="Logo">
                    </a>
                </div>
                <div class="logo logo-white">
                    <a href="{{route('dean.dashboard')}}">
                        <img src="{{asset('assets/images/logo/spup.png')}}" alt="Logo">
                        <img class="logo-fold" src="{{asset('assets/images/logo/spup-fold.png')}}" alt="Logo">
                    </a>
                </div>
                <div class="nav-wrap">
                    <ul class="nav-left">
                        <li class="desktop-toggle">
                            <a href="javascript:void(0);">
                                <i class="anticon"></i>
                            </a>
                        </li>
                        <li class="mobile-toggle">
                            <a href="javascript:void(0);">
                                <i class="anticon"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#search-drawer">
                                <i class="anticon anticon-search"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav-right">
                     
                        @php
                        $user = Auth::user();
                        
                        $role = $user->role ?? 'No Role'; // Ensure role retrieval is correct

                        // Fetch user profile information
                        $firstname = $user->firstname ?? '';
                        $middlename = $user->middlename ?? '';
                        $lastname = $user->lastname ?? '';
                        @endphp

                        <li class="dropdown dropdown-animated scale-left">
                            <div class="pointer" data-toggle="dropdown">
                                <div class="avatar avatar-image m-h-10 m-r-15">
                                    <img src="{{ asset($user->picture ?? 'assets/images/avatars/profile-picture.png') }}" alt="User Avatar">
                                </div>
                            </div>
                            <div class="p-b-15 p-t-20 dropdown-menu pop-profile">
                                <div class="p-h-20 p-b-15 m-b-10 border-bottom">
                                    <div class="d-flex m-r-50">
                                        <div class="avatar avatar-lg avatar-image">
                                            <img src="{{ asset($user->picture ?? 'assets/images/avatars/profile-picture.png') }}" alt="User Avatar">
                                        </div>
                                        <div class="m-l-10">
                                            <p class="m-b-0 text-dark font-weight-semibold">{{ $firstname }} {{
                                                $middlename }} {{ $lastname }}</p>
                                            <p class="m-b-0 opacity-07">{{ $role }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <a href="{{ route('dean.account_settings') }}"
                                    class="dropdown-item d-block p-h-15 p-v-10">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="anticon opacity-04 font-size-16 anticon-lock"></i>
                                            <span class="m-l-10">Account Setting</span>
                                        </div>
                                        <i class="anticon font-size-10 anticon-right"></i>
                                    </div>
                                </a>
                                
                                <!-- Logout -->
                                <form id="logout-form" action="{{ route('dean.logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                                <a href="javascript:void(0);"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="dropdown-item d-block p-h-15 p-v-10">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="anticon opacity-04 font-size-16 anticon-logout"></i>
                                            <span class="m-l-10">Logout</span>
                                        </div>
                                        <i class="anticon font-size-10 anticon-right"></i>
                                    </div>
                                </a>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
            <!-- Header END -->

            <!-- Side Nav START -->
            @include('dean.layout.sidebar')
            <!-- Side Nav END -->

            <!-- Page Container START -->
            <div class="page-container">
                <!-- Content Wrapper START -->
                <div class="main-content">
                    @yield('content')
                </div>
                <!-- Content Wrapper END -->

                @include('dean.layout.footer')
            </div>
            <!-- Page Container END -->

        </div>
    </div>

    @include('dean.layout.scripts')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
