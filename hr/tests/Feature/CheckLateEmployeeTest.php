<?php

namespace Tests\Feature;

use App\Console\Commands\CheckLateEmployee;
use App\Models\User;
use App\Notifications\LateArrivalNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\HumanResource\Entities\Attendance;
use Modules\HumanResource\Entities\Employee;
use Tests\TestCase;

class CheckLateEmployeeTest extends TestCase
{
   

    /** @test */
    public function it_notifies_late_employees_correctly()
    {
        // Prevent actual notifications from being sent
        Notification::fake();

        // Fetch the existing user
        $user = User::where('email', 'dhiaaqahtan@gmail.com')->first();
        $this->assertNotNull($user, 'User with the specified email does not exist.');

        // Fetch the associated employee
        $employee = Employee::where('email', 'dhiaaqahtan@gmail.com')->first();
        $this->assertNotNull($employee, 'Employee with the specified email does not exist.');

        // Define the scheduled start time as a variable
        $start_time = '09:00:00';



        // Dynamically set the current time to 09:35:00
        $testNow = Carbon::parse($start_time)
                        ->setDate(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day)
                        ->addMinutes(35); // 09:35 AM
        Carbon::setTestNow($testNow);

        // Run the command
        $this->artisan('attendance:notify-late')
             ->expectsOutput('Late notification sent to ' . $employee->full_name)
             ->expectsOutput('Late employee notifications have been processed.')
             ->assertExitCode(0);

        // Assert that the notification was sent
        Notification::assertSentTo(
            [$user],
            LateArrivalNotification::class,
            function ($notification, $channels) use ($employee) {
                return $notification->getEmployee()->id === $employee->id &&
                       $notification->getLateMinutes() === 35;
            }
        );

        // Clear the test now
        Carbon::setTestNow();
    }
}
