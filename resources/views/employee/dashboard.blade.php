@extends('employee.layout.header')

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
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card dashboard-card employee-dashboard">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="m-b-0 text-muted">Total Students</p>
                                        <h2 class="m-b-0">{{ $totalStudents }}</h2>
                                    </div>
                                    <div class="text-primary">
                                        <i class="anticon anticon-team font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="anticon anticon-team font-size-48 text-info"></i>
                                </div>
                                <h5 class="card-title">Students</h5>
                                <p class="card-text text-muted">Manage student records</p>
                                <a href="{{route('employee.student.list-of-students')}}" class="btn btn-info btn-sm w-100">
                                    <i class="anticon anticon-eye mr-2"></i>View Students
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="anticon anticon-setting font-size-48 text-warning"></i>
                                </div>
                                <h5 class="card-title">Settings</h5>
                                <p class="card-text text-muted">System configuration</p>
                                <a href="{{route('employee.clearance.clearance-tap-id')}}" class="btn btn-warning btn-sm w-100">
                                    <i class="anticon anticon-scan mr-2"></i>RFID Tapping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Students per Department</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-hover table-white">
                                <thead class="thead-white">
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-center">Number of Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departmentCounts as $dept)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="anticon anticon-apartment text-primary mr-2"></i>
                                                <span class="font-weight-semibold">{{ $dept['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary badge-pill">{{ $dept['count'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <!-- Content Wrapper END -->

    @include('employee.layout.footer')

</div>
<!-- Page Container END -->





























































@endsection