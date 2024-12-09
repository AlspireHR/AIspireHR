<li class="{{ request()->is('employee.leave.application.form') ? 'mm-active' : '' }}">
    <a class="has-arrow material-ripple" href="#">
        <i class="fa fa-plane"></i>
        <span>{{ localize('request_leave') }}</span>
    </a>
    <ul class="nav-second-level attendances mm-show">
        <li class="attendances.create mm-active ">
            <a class="dropdown-item"
                href="{{ route('employee.leave.application.form') }}">{{ localize('request_leave') }}</a>
        </li>
        <li class="attendances.missingAttendance mm-active">
            <a class="dropdown-item"
                href="{{ route('employee.leave.list') }}">{{ localize('leave_summary') }}</a>
        </li>
    </ul>
</li>


<li class="attendances mm-active">
    <a class="has-arrow material-ripple" href="#">
        <i class="fa fa-user"></i>
        <span> {{ localize('attendance') }}</span>
    </a>
    <ul class="nav-second-level attendances mm-show">
               
                <li class="attendances.missingAttendance mm-active">
                    <a class="dropdown-item"
                        href="{{ route('employee.attendance.history') }}">{{ localize('attendance_summary') }}</a>
                </li>
    </ul>
</li>

