@extends('dean.layout.header')

@section('main-content')


 <!-- Page Container START -->
 <div class="page-container">
                
    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Dashboard</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="#" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                   
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted m-b-5">Total Students</p>
                                <h2 class="m-b-0">{{ $totalStudents ?? 0 }}</h2>
                            </div>
                            <div class="avatar avatar-icon avatar-lg avatar-blue">
                                <i class="anticon anticon-team"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted m-b-5">Department Employees</p>
                                <h2 class="m-b-0">{{ $totalEmployees ?? 0 }}</h2>
                            </div>
                            <div class="avatar avatar-icon avatar-lg avatar-green">
                                <i class="anticon anticon-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted m-b-5">Department Advisers</p>
                                <h2 class="m-b-0">{{ $totalAdvisers ?? 0 }}</h2>
                            </div>
                            <div class="avatar avatar-icon avatar-lg avatar-gold">
                                <i class="anticon anticon-solution"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted m-b-5">Active Clearances</p>
                                <h2 class="m-b-0">{{ $activeClearances ?? 0 }}</h2>
                            </div>
                            <div class="avatar avatar-icon avatar-lg avatar-cyan">
                                <i class="anticon anticon-file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dean Management Tools</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 m-b-30">
                                <a href="{{ route('dean.manage-users') }}" class="btn btn-primary btn-block btn-lg">
                                    <i class="anticon anticon-team m-r-5"></i>
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 m-b-30">
                                <a href="{{ route('dean.student.list-of-students') }}" class="btn btn-info btn-block btn-lg">
                                    <i class="anticon anticon-user m-r-5"></i>
                                    View Students
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 m-b-30">
                                <a href="{{ route('dean.clearance.list-of-clearance') }}" class="btn btn-success btn-block btn-lg">
                                    <i class="anticon anticon-file-text m-r-5"></i>
                                    Clearances
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 m-b-30">
                                <a href="{{ route('dean.reports.dean-osa') }}" class="btn btn-warning btn-block btn-lg">
                                    <i class="anticon anticon-bar-chart m-r-5"></i>
                                    Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Department Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center m-b-15">
                                    <div class="avatar avatar-icon avatar-sm avatar-blue m-r-15">
                                        <i class="anticon anticon-check-circle"></i>
                                    </div>
                                    <div>
                                        <p class="m-b-0 text-dark font-weight-semibold">Completed Clearances</p>
                                        <p class="m-b-0 text-muted font-size-13">{{ $completedClearances ?? 0 }} students cleared</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center m-b-15">
                                    <div class="avatar avatar-icon avatar-sm avatar-gold m-r-15">
                                        <i class="anticon anticon-clock-circle"></i>
                                    </div>
                                    <div>
                                        <p class="m-b-0 text-dark font-weight-semibold">Pending Clearances</p>
                                        <p class="m-b-0 text-muted font-size-13">{{ ($activeClearances ?? 0) - ($completedClearances ?? 0) }} students pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content Wrapper END -->

    @include('dean.layout.footer')

</div>
<!-- Page Container END -->





























































@endsection