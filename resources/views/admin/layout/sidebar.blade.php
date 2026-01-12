<!-- Side Nav START -->
<div class="side-nav">
    <div class="side-nav-inner">
        <ul class="side-nav-menu scrollable">
            <li class="nav-item dropdown">
                <a href="{{route('admin.dashboard')}}">
                    <span class="icon-holder">
                        <i class="anticon anticon-dashboard"></i>
                    </span>
                    <span class="title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="dropdown-toggle" href="javascript:void(0);">
                    <span class="icon-holder">
                        <i class="anticon anticon-team"></i>
                    </span>
                    <span class="title">Student</span>
                    <span class="arrow">
                        <i class="arrow-icon"></i>
                    </span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{route('admin.students.list-of-students')}}">List of Student</a>
                    </li>
                    <li>
                        <a href="{{route('admin.student.add_new_student')}}">Add New Student</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="dropdown-toggle" href="javascript:void(0);">
                    <span class="icon-holder">
                        <i class="anticon anticon-file-done"></i>
                    </span>
                    <span class="title">Clearance</span>
                    <span class="arrow">
                        <i class="arrow-icon"></i>
                    </span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{route('admin.clearance.list')}}">List of Clearance</a>
                    </li>
                    <li>
                        <a href="{{route('admin.clearance.locked-clearances')}}">
                            <i class="fas fa-lock text-danger"></i> Locked Clearances
                        </a>
                    </li>

                </ul>
            </li>





            <li class="nav-item dropdown">
                <a class="dropdown-toggle" href="javascript:void(0);">
                    <span class="icon-holder">
                        <i class="anticon anticon-file-protect"></i>
                    </span>
                    <span class="title">Reports</span>
                    <span class="arrow">
                        <i class="arrow-icon"></i>
                    </span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{route('admin.reports.dean-osa')}}">View Reports</a>
                    </li>
                    <li>
                        <a href="{{route('admin.reports.previous-years')}}">
                            <i class="anticon anticon-history"></i> Previous Years Records
                        </a>
                    </li>

                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="dropdown-toggle" href="javascript:void(0);">
                    <span class="icon-holder">
                        <i class="anticon anticon-setting"></i>
                    </span>
                    <span class="title">Settings</span>
                    <span class="arrow">
                        <i class="arrow-icon"></i>
                    </span>
                </a>
                <ul class="dropdown-menu">

                    <li class="nav-item dropdown">
                        <a href="javascript:void(0);">
                            <span>User Accounts</span>
                            <span class="arrow">
                                <i class="arrow-icon"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{route('admin.users.list-of-users')}}">List of Users Account</a>
                            </li>
                            <li>
                                <a href="{{route('admin.users.create_new_user')}}">Create New Users Account</a>
                            </li>

                            <li>
                                <a href="{{route('admin.users.list_officer_user_account')}}">List of Officer Account</a>
                            </li>

                              <li>
                                <a href="{{route('admin.users.create_new_officer_user')}}">Create New Officer Users Account</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0);">
                            <span>Department</span>
                            <span class="arrow">
                                <i class="arrow-icon"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{route('admin.department.list-of-department')}}">List of Department</a>
                            </li>
                            <li>
                                <a href="{{route('admin.department.add_new_department')}}">Add New Department</a>
                            </li>
                        </ul>
                    </li>



                    <li class="nav-item dropdown">
                        <a href="javascript:void(0);">
                            <span>Academic Year</span>
                            <span class="arrow">
                                <i class="arrow-icon"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{route('admin.academic_year.list_of_academic_year')}}">List of Academic
                                    Year</a>
                            </li>
                            <li>
                                <a href="{{route('admin.academic_year.add_new_academic_year')}}">Add New Academic
                                    Year</a>
                            </li>
                        </ul>
                    </li>


                    <li class="nav-item dropdown">
                        <a href="javascript:void(0);">
                            <span>Activity Logs</span>
                            <span class="arrow">
                                <i class="arrow-icon"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{route('admin.activity-logs.index')}}">View Activity Logs</a>
                            </li>

                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!-- Side Nav END -->