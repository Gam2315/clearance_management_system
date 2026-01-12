<!-- Side Nav START -->
<div class="side-nav">
    <div class="side-nav-inner">
        <ul class="side-nav-menu scrollable">
            <li class="nav-item dropdown">
                <a href="{{route('employee.dashboard')}}">
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
                        <a href="{{route('employee.student.list-of-students')}}">List of Student</a>
                    </li>
                </ul>
            </li>

            @php
                $user = Auth::user();
                $isOSAEmployee = $user->department_id == 6; // OSA department ID
            @endphp

            @if($isOSAEmployee)
            <li class="nav-item dropdown">
                <a href="{{route('employee.reports.dean-osa')}}">
                    <span class="icon-holder">
                        <i class="anticon anticon-file-text"></i>
                    </span>
                    <span class="title">Reports</span>
                    <span class="badge badge-success" style="font-size: 0.7em; margin-left: 5px;">OSA</span>
                </a>
            </li>
            @endif

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
                        <a href="{{route('employee.clearance.clearance-tap-id')}}">

                            <span class="title">Clearance Tapping</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!-- Side Nav END -->