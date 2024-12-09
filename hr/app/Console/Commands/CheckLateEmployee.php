<?php

namespace App\Console\Commands;

use App\Notifications\LateArrivalNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\HumanResource\Entities\Attendance;
use Modules\HumanResource\Entities\Employee;

class CheckLateEmployee extends Command
{
    protected $signature = 'attendance:notify-late';
    protected $description = 'Notify employees who are late by more than 30 minutes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Current time
        $now = Carbon::now();

        // Today's date
        $today = $now->toDateString();

        // Fetch all active employees
        $employees = Employee::with(['attendance_time', 'user'])->where('is_active', true)->get();

        foreach ($employees as $employee) {
            // Get scheduled start time for today
            $scheduledStartTime = Carbon::parse($employee->attendance_time?->start_time)
                                        ->setDate($now->year, $now->month, $now->day);

            // Calculate the threshold time (start_time + 30 minutes)
            $thresholdTime = $scheduledStartTime->copy()->addMinutes(30);

            // Check if current time is past the threshold
            if ($now->greaterThanOrEqualTo($thresholdTime)) {
                // Fetch today's attendance record
                $attendance = Attendance::where('employee_id', $employee->id)
                                        ->whereDate('time', $today)
                                        ->first();

                // If attendance is null or clock-in time is after threshold
                if (!$attendance || Carbon::parse($attendance->in_time)->greaterThan($thresholdTime)) {
                    // Calculate late minutes
                    $lateMinutes = $attendance
                                   ? Carbon::parse($attendance->in_time)->diffInMinutes($scheduledStartTime, false)
                                   : $now->diffInMinutes($scheduledStartTime, false);

                    // Ensure lateMinutes is positive
                    $lateMinutes = $lateMinutes > 0 ? $lateMinutes : 0;

                    // Send notification if not already sent
                    // To prevent duplicate notifications, you might need to track sent notifications
                    // For simplicity, we'll assume one notification per day

                    $employee->user->notify(new LateArrivalNotification($employee, $lateMinutes, $today));

                    // Optionally, log the notification or mark as sent in another way
                    $this->info('Late notification sent to ' . $employee->full_name);
                }
            }
        }

        $this->info('Late employee notifications have been processed.');
    }
}
