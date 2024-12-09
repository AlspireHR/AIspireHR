<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Setting\Entities\Application;
use App\Models\User;
use App\Models\Appsetting;
use Modules\HumanResource\Entities\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\HumanResource\Entities\Attendance;
use Modules\HumanResource\Entities\ApplyLeave;
use Modules\HumanResource\Entities\WeekHoliday;
use Modules\HumanResource\Entities\SalaryGenerate;
use Modules\HumanResource\Entities\LeaveType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FrontEmployeeController extends Controller
{

    /**
     * Show the dashboard.
     */
    public function index()
    {
        $status = "Ok";

        $attendanceSummary = [
            'total_present' => $this->getTotalPresentDays(Auth::user()->employee->id),
            'total_hours' => $this->getTotalHours(Auth::user()->employee->id),
        ];

        // Fetch Leave Summary
        $leaveSummary = [
            'taken_leave' => $this->getTakenLeave(Auth::user()->employee->id),
            'remaining_leave' => $this->getRemainingLeave(Auth::user()->employee->id),
        ];



        return view('employee.dashboard', compact('status', 'attendanceSummary', 'leaveSummary'));
    }

    /**
     * Example method to get total present days.
     */
    protected function getTotalPresentDays($employeeId)
    {
        // Implement logic to calculate total present days
        return Attendance::where('employee_id', $employeeId)->count();
    }

    /**
     * Example method to get total hours.
     */
    protected function getTotalHours($employeeId)
    {
        // Implement logic to calculate total hours
        $totalHours = Attendance::where('employee_id', $employeeId)->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(MAX(time), MIN(time)))'));
        return gmdate("H:i:s", $totalHours);
    }

    /**
     * Example method to get taken leave.
     */
    protected function getTakenLeave($employeeId)
    {
        return ApplyLeave::where('employee_id', $employeeId)->sum('total_approved_day');
    }

    /**
     * Example method to get remaining leave.
     */
    protected function getRemainingLeave($employeeId)
    {
        $totalLeave = LeaveType::sum('leave_days'); // Adjust based on your logic
        $takenLeave = $this->getTakenLeave($employeeId);
        return $totalLeave - $takenLeave;
    }

    /**
     * Example method to get loan amount due.
     */



    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('employee.auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = $request->input('token_id');

        if (Auth::attempt($credentials)) {
            // Authentication passed
            $user = Auth::user();

            // Update token if necessary
            if ($token) {
                $user->token_id = $token;
                $user->save();
            }

            return redirect()->route('employee.dashboard')->with('success', __('successfully_logged_in'));
        }

        return back()->withErrors([
            'email' => __('no_data_found'),
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('employee.login')->with('success', __('successfully_logged_out'));
    }

    /**
     * Show password recovery form.
     */
    public function showPasswordRecoveryForm()
    {
        return view('employee.auth.password_recovery');
    }

    /**
     * Handle password recovery submission.
     */
    public function passwordRecovery(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'email' => 'required|email|max:100',
            ]);

            $user = User::where('email', $request->input('email'))->first();

            $ptoken = Str::random(60); // Generate a secure token

            if ($user) {
                $user->password_reset_token = $ptoken;
                $user->save();

                // Send recovery email
                $this->sendRecoveryEmail($user->email, $ptoken);

                return back()->with('success', __('successfully_send_email'));
            } else {
                return back()->withErrors(['email' => __('email_not_found')]);
            }
        } catch (ValidationException $e) {
            // Validation failed
            return back()->withErrors(['email' => __('email_format_was_not_right')]);
        }
    }

    /**
     * Send password recovery email.
     */
    protected function sendRecoveryEmail($email, $ptoken)
    {
        $url = route('employee.recovery.form', ['token_id' => $ptoken]);
        $msg = "Click on this URL to reset your password: $url";
        mail($email, "Password Recovery", wordwrap($msg, 100));
    }

    /**
     * Show the password recovery form with token.
     */
    public function recoveryForm($token_id)
    {
        $user = User::where('password_reset_token', $token_id)->first();

        if ($user) {
            return view('employee.auth.recovery_form', ['token' => $token_id, 'title' => __('recovery_form')]);
        }

        return redirect()->route('employee.login')->withErrors(['token' => __('invalid_or_expired_token')]);
    }

    /**
     * Handle password recovery submission.
     */
    public function recoverySubmit(Request $request, $token_id)
    {
        try {
            // Validate the request data
            $request->validate([
                'password' => 'required|min:8|confirmed', // Ensure password confirmation
            ]);

            $user = User::where('password_reset_token', $token_id)->first();

            if ($user) {
                // Update the user's password
                $user->password = Hash::make($request->password);
                $user->password_reset_token = null; // Invalidate the token
                $user->save();

                return redirect()->route('employee.login')->with('success', __('password_updated_successfully'));
            } else {
                return redirect()->route('employee.recovery.form', $token_id)->withErrors(['token' => __('user_not_found')]);
            }
        } catch (ValidationException $e) {
            // Validation failed
            return redirect()->route('employee.recovery.form', $token_id)->withErrors(['password' => __('password_must_be_at_least_8_characters')]);
        }
    }

    /**
     * Return language data (could be used in a settings page).
     */
    public function language()
    {
        $languages = [
            'login' => __('login'),
            'add_attendance' => __('add_attendance'),
        ];
        return view('employee.settings.language', compact('languages'));
    }

    /**
     * Show web settings.
     */
    public function webSetting()
    {
        $settings = Application::first();

        if ($settings) {
            $settings->logo = asset('storage/' . $settings->logo);
            $settings->favicon = asset('storage/' . $settings->favicon);

            return view('employee.settings.web', [
                'status' => 'Ok',
                'attendance_url' => route('employee.attendance.add'),
                'base_url' => url('/'),
                'logo_url' => $settings->logo,
                'settings' => $settings,
            ]);
        }

        return view('employee.settings.web')->withErrors(['settings_not_found' => __('settings_not_found')]);
    }

    /**
     * Show the attendance form.
     */
    public function showAttendanceForm()
    {
        $latitude = Appsetting::pluck('latitude')->first();
        $longitude = Appsetting::pluck('longitude')->first();
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $barcodeData = "{$latitude},{$longitude},{$currentDateTime}";

        $qrCode = QrCode::size(200)->generate($barcodeData);
        return view('employee.attendance.form', compact('latitude', 'longitude', 'barcodeData', 'qrCode'));
    }

    /**
     * Handle adding attendance.
     */
    public function addAttendance(Request $request)
    {

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'datetime' => 'required|date',
        ]);

        $ulatitude       = $request->get('latitude');
        $ulongitude      = $request->get('longitude');
        $employee_id     = Auth::user()->employee->id;
        $time            = $request->get('datetime');
        $userid          = Auth::id();
        $checklatitude = Appsetting::where('latitude', $ulatitude)
            ->where('longitude', $ulongitude)
            ->count();

        $userInfo = User::select('*')->where('id', $userid)->first();
        $user_data = $this->userData($userInfo->email);
        $user_data->firstname = $user_data->first_name;
        $user_data->lastname = $user_data->last_name;

        $settingdata = Appsetting::first();

        $lat1 = $settingdata->latitude;
        $lon1 = $settingdata->longitude;
        $lat2 = $ulatitude;
        $lon2 = $ulongitude;
        $theta = $lon1 - $lon2;

        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles= $dist * 60 * 1.1515;
        $unit = 'K';
        $metre   = ($miles*1.609344)*1000;

        $distance =  number_format($metre,1);

        $attendance_history = [
            'employee_id' => $employee_id,
            'state'  => 1,
            'id'     => 0,
            'time'   => $time,
        ];

        if($settingdata->acceptablerange > $distance){

            if(Attendance::create($attendance_history)){
                $json['response'] = [
                    'status'     => 'ok',
                    'range'     => $distance,
                    'message'    => 'Successfully Saved',
                ];

                $icon='';
                $fields3 = array(
                    'to'=> $user_data->token_id,
                    'data'=>array(
                        'title'=>"Attendance",
                        'body'=>"Dear ".$user_data->firstname.' '.$user_data->lastname." Your Attendance Successfully Saved",
                        'image'=>$icon,
                        'media_type'=>"image",
                        "action"=> "2",
                    ),
                    'notification'=>array(
                        'sound'=>"default",
                        'title'=>"Attendance",
                        'body'=>"Dear ".$user_data->firstname.' '.$user_data->lastname." Your Attendance Successfully Saved",
                        'image'=>$icon,
                    )
                );

                $post_data3 = json_encode($fields3);
                $url = "https://fcm.googleapis.com/fcm/send";
                $ch3  = curl_init($url);
                curl_setopt($ch3, CURLOPT_FAILONERROR, TRUE);
                curl_setopt($ch3, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch3, CURLOPT_POSTFIELDS, $post_data3);
                curl_setopt($ch3, CURLOPT_HTTPHEADER, array($settingdata->googleapi_authkey,
                    'Content-Type: application/json')
                );
                $result3 = curl_exec($ch3);
                curl_close($ch3);
            }
            else {
                $json['response'] = [
                    'status'     => 'error',
                    'range'      => $distance,
                    'lat'        => $lat1,
                    'dfrange'    => $settingdata->acceptablerange,
                    'message'    =>  localize('please_try_again'),

                ];
            }
        }else{
                $json['response'] = [
                'status'     => 'error',
                    'range'    => $distance,
                'message'    => localize('out_of_range'),

            ];
        }

        echo json_encode($json,JSON_UNESCAPED_UNICODE);

        $ulatitude = $request->latitude;
        $ulongitude = $request->longitude;
        $time = $request->datetime;
        $employee_id = Auth::user()->employee->id;
        $userid = Auth::id();

        $userInfo = User::find($userid);
        $user_data = $this->userData($userInfo->email);
        $user_data->firstname = $user_data->first_name;
        $user_data->lastname = $user_data->last_name;

        $settingdata = Appsetting::first();

        // Calculate distance
        $distance = $this->calculateDistance($settingdata->latitude, $settingdata->longitude, $ulatitude, $ulongitude);

        $attendance_history = [
            'employee_id' => $employee_id,
            'state' => 1,
            'time' => $time,
        ];

        if ($settingdata->acceptablerange > $distance) {
            if (Attendance::create($attendance_history)) {
                return back()->with('success', __('attendance_successfully_saved'));
            } else {
                return back()->withErrors(['attendance' => __('please_try_again')]);
            }
        } else {
            return back()->withErrors(['distance' => __('out_of_range')]);
        }
    }

    /**
     * Calculate distance between two coordinates in meters.
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $metre = ($miles * 1.609344) * 1000;

        return number_format($metre, 1);
    }

    /**
     * Handle attendance history view.
     */
    public function attendanceHistory(Request $request)
    {
        $employee_id = Auth::user()->employee->id; // Assuming User has an Employee relation

        $attendances = $this->getAttendanceHistory($employee_id);

        return view('employee.attendance.history', compact('attendances'));
    }

    /**
     * Retrieve attendance history.
     */
    protected function getAttendanceHistory($employeeId)
    {

        return Attendance::where('employee_id', $employeeId)->orderBy('time', 'desc')->get();
    }

    /**
     * Show leave application form.
     */
    public function showLeaveApplicationForm()
    {
        $user_info = Auth::user();
        $leaveTypes = LeaveType::all();
        return view('employee.leave.application', compact('leaveTypes','user_info'));
    }

    public function leaveApplication(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'type_id' => 'required|exists:leave_types,id',
            'reason' => 'required|string',
        ]);

        $employee_id = Auth::user()->employee->id;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $leave_type = $request->type_id;
        $reason = $request->reason;

        // Calculate total days including both start and end date
        $apply_day = Carbon::parse($from_date)->diffInDays(Carbon::parse($to_date)) + 1;

        // Calculate total approved leave taken
        $taken_leave = ApplyLeave::where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type)
            ->sum('total_approved_day');

        $total_leave = LeaveType::where('id', $leave_type)->value('leave_days');

        if ($taken_leave + $apply_day > $total_leave) {
            return back()->withErrors(['leave' => __('you_already_enjoyed_all_leaves')]);
        }

        // Create leave application
        $leaveApplication = ApplyLeave::create([
            'uuid' => (string) Str::uuid(),
            'employee_id' => $employee_id,
            'leave_type_id' => $leave_type,
            'leave_apply_start_date' => $from_date,
            'leave_apply_end_date' => $to_date,
            'total_apply_day' => $apply_day,
            'reason' => $reason,
            'leave_apply_date' => Carbon::now()->format('Y-m-d'),
        ]);

        if ($leaveApplication) {

            return back()->with('success', __('leave_request_successfully_submitted'));
        }

        return back()->withErrors(['leave' => __('please_try_again')]);
    }

    /**
     * Handle leave list view.
     */
    public function leaveList(Request $request)
    {
        $employee_id = Auth::user()->employee->id;
        $leaves = ApplyLeave::where('employee_id', $employee_id)->orderBy('leave_apply_date', 'desc')->get();

        return view('employee.leave.list', compact('leaves'));
    }

    /**
     * Handle other functionalities similarly...
     */

    // ... (Other methods like attendanceDatewise, leaveTypeList, etc.)

    /**
     * Fetch user data related to Employee.
     */
    public function userData($email)
    {
        return Employee::select('employees.*', 'departments.department_name', 'users.profile_image as profile_pic', 'users.token_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('users', 'users.email', '=', 'employees.email')
            ->where('employees.email', $email)
            ->where('users.user_type_id', 2)
            ->first();
    }
}
