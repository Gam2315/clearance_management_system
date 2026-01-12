@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">


    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Student Profile</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="#" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.students.list-of-students') }}">List of Student</a>
                    <span class="breadcrumb-item active">Student Profile</span>
                </nav>
            </div>
        </div>
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <div class="d-md-flex align-items-center">
                                <div class="text-center text-sm-left ">
                                    <div class="avatar avatar-image" style="width: 150px; height:150px">
                                        <img src="assets/images/avatars/thumb-3.jpg" alt="">
                                    </div>
                                </div>
                                <div class="text-center text-sm-left m-v-15 p-l-30">
                                    <h2 class="m-b-5">{{ $student->user->lastname . ', ' . $student->user->firstname . '
                                        ' . $student->user->middlename . ' ' . $student->user->suffix_name}}</h2>
                                    <p class="text-opacity font-size-13">{{ $student->courses->course_name .' | ' .
                                        $student->year }} Year</p>
                                    <p class="text-dark m-b-20">{{ $student->department->department_name}} </p>
                                    <a class="btn btn-danger btn-tone m-r-5"
                                        href="{{ route('admin.students.list-of-students') }}" role="button">Back</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="row">
                                <div class="d-md-block d-none border-left col-1"></div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5>STUDENT CLEARANCE</h5>
                            <hr>
                            @php
                            $statusMap = []; // ✅ Correct initialization

                            if ($clearance && $clearance->statuses) {
                            foreach ($clearance->statuses as $status) {
                            $deptName = strtoupper($status->department->department_code);
                            // Show actual status regardless of approver role
                            if ($status->status === 'cleared') {
                            $statusMap[$deptName] = 'Cleared';
                            } else {
                            $statusMap[$deptName] = 'Pending';
                            }
                            }
                            }
                            @endphp
                            <table id="data-table" class="table">
                                <tr>
                                    <td class="border border-black p-1 w-1/3">School Dean : <strong>{{
                                            $statusMap[$student->department->department_code ?? ''] ?? 'Pending'
                                            }}</strong></td>

                                    <td class="border border-black p-1 w-1/3">Laboratory (if applicable):</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-1 w-1/3">OSA: <strong>{{ $statusMap['OSA'] ??
                                            'Pending'
                                            }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">FOOD LABS: <strong>{{ $statusMap['FOOD
                                            LABS'] ??
                                            'Pending' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-1 w-1/3">GUIDANCE: <strong>{{
                                            $statusMap['GUIDANCE'] ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">COMPUTER: <strong>{{
                                            $statusMap['COMPUTER'] ??
                                            'Pending' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-1 w-1/3">LIBRARY: <strong>{{ $statusMap['LIBRARY']
                                            ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">ENGINEERING: <strong>{{
                                            $statusMap['ENGINEERING']
                                            ?? 'Pending' }}</strong></td>
                                </tr>

                                <tr>
                                    <td class="border border-black p-1 w-1/3">BOUTIQUE: <strong>{{
                                            $statusMap['BOUTIQUE'] ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">COLLEGE SCIENCE LAB: <strong>{{
                                            $statusMap['COLLEGE SCIENCE LAB'] ?? 'Pending' }}</strong></td>
                                </tr>

                                <tr>
                                    <td class="border border-black p-1 w-1/3">CHRISTIAN FORMATION: <strong>{{
                                            $statusMap['CHRISTIAN FORMATION'] ?? 'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">UNIVERSITY REGISTRAR: <strong>{{
                                            $statusMap['UNIVERSITY REGISTRAR'] ?? 'Pending' }}</strong></td>
                                </tr>

                                <tr>
                                    <td class="border border-black p-1 w-1/3">CLINIC: <strong>{{ $statusMap['CLINIC'] ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">PSG-{{
                                        $student->department->department_code ?? 'N/A' }} :
                                        ADVISER: <span class="underline"><strong>{{
                                                strtoupper($statusMap[strtoupper($student->department->department_code
                                                ?? '')] ??
                                                'Pending')
                                                }}</strong></span></td>
                                </tr>

                                <tr>
                                    <td class="border border-black p-1 w-1/3">ALUMNI: <strong>{{ $statusMap['ALUMNI'] ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">PRESIDENT(FOR UNIWIDE): <strong>{{
                                            $statusMap['PRESIDENT(FOR UNIWIDE)'] ?? 'Pending' }}</strong>/Governor:
                                        <span class="underline">_________</span>
                                    </td>
                                </tr>


                                <tr>
                                    <td class="border border-black p-1 w-1/3">RESEARCH: <strong>{{
                                            $statusMap['RESEARCH'] ??
                                            'Pending' }}</strong></td>
                                    <td class="border border-black p-1 w-1/3">BAO: <span
                                            class="underline">________________</span> OR NO <span
                                            class="underline">________________</span></td>
                                </tr>
                            </table>
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
<!-- Page Container END -->




@endsection