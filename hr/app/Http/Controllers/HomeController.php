<?php

namespace App\Http\Controllers;

use App\Mail\LateArrivalMail;
use App\Models\User;
use App\Notifications\LateArrivalNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\HumanResource\Entities\ApplyLeave;
use Modules\HumanResource\Entities\Department;
use Modules\HumanResource\Entities\Employee;
use Modules\HumanResource\Entities\Holiday;
use Modules\HumanResource\Entities\PointSettings;
use Modules\HumanResource\Entities\WeekHoliday;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:read_dashboard')->only('index');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

     public function testSimpleEmail()
{
    $employee = Employee::find(5); // Get the employee (this is just an example)

    if (!$employee) {
        return response()->json('Employee not found', 404);
    }

    $emailContent = "You were marked as late on " . now()->toDateString();

    Mail::raw($emailContent, function ($message) use ($employee) {
        $message->to('dhiaaqahtan@gmail.com')
                ->subject('Test Email');
    });

    return response()->json('The email has been sent');
}
     public function testemail()
    {
        $employee = Employee::find(5);

        // dd($employee->email);
        if (!$employee) {
            return response()->json('Employee not found', 404);
        }

        $lateMinutes = 10; // Integer for minutes
        $now = Carbon::now();
        $today = $now->toDateString();

        // Send the email using the Mail facade
        Mail::to($employee->email)->send(new LateArrivalMail($employee, $lateMinutes, $today));

        return response()->json('The email has been sent');
    }

    public function index()
    {
        $total_employee   = Employee::where('is_active', 1)->count();
        $present_employee = $this->count_attend_employee();
        $today_leave      = $this->leave_employee()->leave_total;

        $departments = Department::with([
            'employees.attendances:id,employee_id,time',
            'employees.leave:id,employee_id,leave_apply_start_date,leave_apply_end_date,leave_approved_start_date,leave_approved_end_date,is_approved_by_manager,is_approved',
        ])->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'department_name']);

        // department wise employee attendances
        $departmentWiseAttendanceReport = $this->attendanceDepartmentWise(0);

        // position wise candidate count

        // leave appropriated employee
        $leaveEmployees = ApplyLeave::with([
            'employee:id,employee_id,first_name,middle_name,last_name,email,position_id,department_id',
            'employee.position:id,position_name'
        ])
            ->latest()
            ->take(4)
            ->get();

        return view('backend.layouts.home', compact([
            'present_employee',
            'today_leave',
            'total_employee',
            'departmentWiseAttendanceReport',
            'leaveEmployees',
        ]));
    }

    public function staffHome()
    {
        return redirect()->route('myProfile');
    }

    public function empProfile()
    {
        return view('auth.profile-info');
    }


    // myProfile
    public function myProfile()
    {
        $userId = Auth::id();

        $userInfo = Auth::user();
        if ($userInfo->user_type_id == 1) {
            return redirect()->route('empProfile');
        }
        $employee_info = Employee::where('user_id', $userId)
            ->first();

        $point_settings     = PointSettings::first();
        $total_employee     = Employee::where('is_active', 1)->count();
        $present_empl       = $this->count_attend_employee_current_year($employee_info?->id);
        $late_attend        = $this->count_late_attend_current_year($employee_info?->id, $point_settings);
        $total_working_days = $this->count_total_working_days($employee_info?->id);
        $total_absent_days  = $total_working_days - $present_empl;
        $absent_days = 0;
        if ($total_absent_days > 0) {
            $absent_days = $total_absent_days;
        }
        $leave_applications = $this->get_leave_applications($employee_info?->id);
        $current_year_holidays = $this->get_holidays_current_year();




        $user_info = Auth::user();

        return view(
            'employee.emp-dashboard',
            compact(
                'userInfo',
                'total_employee',
                'present_empl',
                'late_attend',
                'absent_days',
                'leave_applications',
                'user_info',
                'current_year_holidays',
            )
        );
    }

    public function get_holidays_current_year()
    {
        $year = date('Y');

        // leave applications
        $holidays = Holiday::select('*')
            ->whereYear('start_date', $year)
            ->orderBy('start_date', 'desc')
            ->get();

        $resp_array = [];
        foreach ($holidays as $holiday) {
            $child_array = [];
            // $dates = [];
            // $dates[] = $holiday->start_date;
            // $dates[] = $holiday->end_date;
            $child_array = [$holiday->start_date, $holiday->end_date];

            // Parse the date using Carbon
            $carbonDate = Carbon::parse($holiday->start_date);
            // Get the month name
            $monthName = $carbonDate->format('F');

            if (isset($resp_array[$monthName])) {
                $resp_array[$monthName][count($resp_array[$monthName])] = $child_array;
            } else {
                $resp_array[$monthName][0] = $child_array;
            }
        }

        return $resp_array;
    }




    public function get_leave_applications($employee_id)
    {
        $year = date('Y');

        // leave applications
        $leaveApplications = ApplyLeave::with([
            'employee:id,employee_id,first_name,middle_name,last_name,email,position_id,department_id',
            'employee.position:id,position_name'
        ])
            ->where('employee_id', $employee_id)
            ->latest()
            ->take(3)
            ->get();

        return $leaveApplications;
    }

    public function count_total_working_days($employee_id)
    {
        $year = date('Y');
        return $this->totalWorkingDays($year, $employee_id);
    }

    /*
    | Numbers of days worked by any employee for particular branch
    */
    public function totalWorkingDays($year, $employee_id)
    {
        $betweendate = $this->betweenDate();

        //Calculate monthly working days for the employee
        $all_weekends = WeekHoliday::first();
        $weekends_days = explode(',', @$all_weekends->dayname);

        $weekends = array();
        $validDays = ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

        foreach ($weekends_days as $value) {
            if (in_array(ucwords($value), $validDays)) {
                $weekends[] = ucwords($value);
            }
        }

        $holidaysWithSum = DB::table('holidays')
            ->select('holidays.*', DB::raw('SUM(total_day) as total_day_sum'))
            ->where('holidays.start_date', '>=', $betweendate['start_date'])
            ->where('holidays.end_date', '<=', $betweendate['end_date'])
            ->whereNull('holidays.deleted_at')
            ->first();

        $totalHolidays = (int)$holidaysWithSum->total_day_sum;

        //Now calculate Monthly Working Days
        $workdays = array();

        $days_and_weekends_count = $this->getFridaysAndSaturdays(
            $betweendate['start_date'],
            $betweendate['end_date'],
            $weekends
        );

        //total leave calculation start
        $getLeaveInfo =  DB::table('apply_leaves')
            ->select('apply_leaves.*', DB::raw('SUM(total_approved_day) as total_day_sum'))
            ->where('apply_leaves.leave_approved_start_date', '>=', $betweendate['start_date'])
            ->where('apply_leaves.leave_approved_end_date', '<=', $betweendate['end_date'])
            ->where('apply_leaves.is_approved_by_manager', 1)
            ->where('apply_leaves.is_approved', 1)
            ->where('apply_leaves.employee_id', $employee_id)
            ->whereNull('apply_leaves.deleted_at')
            ->first();

        $totalLeaveDays = (int)$getLeaveInfo->total_day_sum;

        // total leave calculation start

        $totalWeekends = $days_and_weekends_count['totalWeekends'];
        $totalDays = $days_and_weekends_count['totalDays'];

        $workdays = $totalDays - $totalWeekends - $totalHolidays - $totalLeaveDays;

        if ($workdays > 0) {
            return $workdays;
        }
        return 0;
    }

    public function getFridaysAndSaturdays($startDate, $endDate, $weekends)
    {

        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        if ($weekends && count($weekends) > 0) {

            $result = [];


            $num_days = array();
            foreach ($weekends  as $day) {
                $num_days[] = date('N', strtotime($day));
            }

            $totalDays = 0;
            for ($currentDate = $startTimestamp; $currentDate <= $endTimestamp; $currentDate += 86400) {

                $totalDays++;
                $currentDayOfWeek = date('N', $currentDate);

                in_array($currentDayOfWeek, $num_days);

                // Check if the current day is Friday (5) or Saturday (6)
                if (in_array($currentDayOfWeek, $num_days)) {
                    $result[] = date('Y-m-d', $currentDate);
                }
            }
        } else {
            $totalDays = 0;
            $result = [];
        }

        $response = array('totalDays' => $totalDays, 'totalWeekends' => count($result));
        return $response;
    }

    public function betweenDate($salaryDate = '')
    {

        $startDate = Carbon::now()->firstOfYear();
        $endDate   = Carbon::now()->format('Y-m-d');
        $betweenDate = array('start_date' => $startDate, 'end_date' => $endDate);

        return $betweenDate;
    }

    public function count_late_attend_current_year($employee_id, $point_settings)
    {
        if ($point_settings == null) {
            return 0;
        } else {

            $definedTime = $point_settings->attendance_end . ':00';
            $year = date('Y');

            $result = DB::table('attendances')
                ->select(
                    DB::raw('DATE(time) as date'),
                    'employee_id',
                    DB::raw('MIN(time) as first_time'),
                    DB::raw('COUNT(*) as attendance_count')
                )
                ->where('employee_id', $employee_id)
                ->whereYear('time', $year)
                ->whereNull('deleted_at')
                ->groupBy(DB::raw('DATE(time)'), 'employee_id')
                ->havingRaw('TIME(MIN(time)) > ?', [$definedTime])
                ->get();

            // Count the number of distinct attendance records (days with attendance)
            return $result->count();
        }
    }

    public function count_attend_employee_current_year($employee_id)
    {
        $year = date('Y');

        $result = DB::table('attendances')
            ->select(
                DB::raw('DATE(time) as date'),
                'employee_id',
                DB::raw('MIN(time) as first_time'),
                DB::raw('COUNT(*) as attendance_count')
            )
            ->where('employee_id', $employee_id)
            ->whereYear('time', $year)
            ->whereNull('deleted_at')
            ->groupBy(DB::raw('DATE(time)'))
            ->get();

        return $result->count();
    }

    // editMyProfile
    public function editMyProfile()
    {
        return view('auth.edit-profile');
    }

    public function count_attend_employee()
    {
        $date = date('Y-m-d');
        $result = DB::table('attendances')
            ->select(DB::raw('COUNT(*)'))
            ->whereDate('time', $date)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->groupBy('employee_id')
            ->get();

        $count = $result->count();

        return $count;
    }

    public function leave_employee()
    {
        $date  = date('Y-m-d');

        $result = DB::table('apply_leaves')
            ->select(DB::raw('COUNT(employee_id) as leave_total'))
            ->whereDate('leave_approved_start_date', '<=', $date)
            ->whereDate('leave_approved_end_date', '>=', $date)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->get();

        if ($result->isNotEmpty()) {
            return $result->first();
        } else {
            return null;
        }
    }

    // Get individual employee dashboard box data
    public function get_employee_box_data()
    {
        $data_boxes = array();

        // Get the authenticated user ID
        $userId = Auth::id();
        $employee_info = Employee::where('is_active', 1)
            ->where('user_id', $userId)
            ->first();

        $employee_id = $employee_info->id;

        $currentDate = Carbon::now();
        $dateY = $currentDate->format('Y');
        $dateM = $currentDate->format('m');

        // Total points of employee
        $dataTotal = DB::table('reward_points')
            ->where('employee_id', $employee_id)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->sum('total');

        $dataTotal = $dataTotal ?? 0;

        $data_boxes['total'] = $dataTotal;

        // Attendance point current year
        $dataAttendence = DB::table('point_attendances')
            ->where('employee_id', $employee_id)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->whereYear('created_at', $dateY)
            ->sum('point');

        $dataAttendence = $dataAttendence ?? 0;

        $data_boxes['attendence'] = $dataAttendence;

        // Collaborative point current year
        $dataCollaborative = DB::table('point_collaboratives')
            ->where('point_shared_with', $employee_id)
            ->whereYear('point_date', $dateY)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->sum('point');

        $dataCollaborative = $dataCollaborative ?? 0;

        $data_boxes['collaborative'] = $dataCollaborative;

        //Management point current year
        $dataManagement = DB::table('point_management')
            ->where('employee_id', $employee_id)
            ->whereYear('created_at', $dateY)
            ->whereNull('deleted_at') // Check if deleted_at is NULL
            ->sum('point');

        $dataManagement = $dataManagement ?? 0;

        $data_boxes['management'] = $dataManagement;

        return $data_boxes;
    }

    public function attendanceDepartmentWise($type)
    {
        $type = request()->get('type') ?? $type;
        switch ($type) {
            case 0:
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;

            case 1:
                $fromDate = Carbon::now()->startOfWeek();
                $toDate = Carbon::now()->endOfWeek();
                break;

            case 2:
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;

            case 3:
                $fromDate = Carbon::now()->startOfYear();
                $toDate = Carbon::now()->endOfYear();
                break;

            default:
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;
        }

        $departments = Department::with([
            'employees.attendances:id,employee_id,time',
            'employees.leave:id,employee_id,leave_apply_start_date,leave_apply_end_date,leave_approved_start_date,leave_approved_end_date,is_approved_by_manager,is_approved',
        ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'department_name']);

        $departmentWiseAttendanceReport = [];
        foreach ($departments as $department) {
            $totalEmployees = $department->employees->count();
            $presentCount = 0;
            $leaveCount = 0;
            foreach ($department->employees as $employee) {
                $presentCount += $employee->attendances()
                    ->selectRaw('DATE(time) as date')
                    ->whereBetween('time', [$fromDate, $toDate])
                    ->groupBy('date')
                    ->get()
                    ->count();
                $leaveCount = $employee->leave()
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('leave_apply_start_date', [$fromDate, $toDate])
                            ->orWhereBetween('leave_apply_end_date', [$fromDate, $toDate])
                            ->orWhere(function ($query) use ($fromDate, $toDate) {
                                $query->where('leave_apply_start_date', '<=', $fromDate)
                                    ->where('leave_apply_end_date', '>=', $toDate);
                            });
                    })
                    ->count();
            }
            $absentCount = $totalEmployees - $presentCount - $leaveCount;
            if ($totalEmployees > 0) {
                if ($absentCount < 0) {
                    $absentCount = 0;
                }
                $presentPercentage = round(($presentCount / $totalEmployees) * 100, 2);
                $absentPercentage = round(($absentCount / $totalEmployees) * 100, 2);
                $leavePercentage = round(($leaveCount / $totalEmployees) * 100, 2);
            } else {
                $presentPercentage = 0;
                $absentPercentage = 0;
                $leavePercentage = 0;
            }

            $departmentWiseAttendanceReport[] = [
                'department' => $department->department_name,
                'present' => $presentPercentage,
                'absent' => $absentPercentage,
                'leave' => $leavePercentage,
            ];
        }

        return $departmentWiseAttendanceReport;
    }


}
