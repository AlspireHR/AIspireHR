<?php

namespace App\Http\Controllers;

use \DB;
use App\Conversations\LeaveApplicationConversation;
use App\Http\Middleware\BotManMiddleware;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\HumanResource\Entities\Employee;

class BotManController extends Controller
{
    public function handle(Request $request)
    {
        $botman = app('botman');

        // Apply the custom middleware
        $botman->middleware->received(new BotManMiddleware());

        $botman->hears('{message}', function (BotMan $bot, $message) {
            $this->botResponses($bot, $message);
        });

        $botman->listen();
    }

    protected function botResponses(BotMan $bot, $message)
    {
        // Retrieve the user_id from BotMan's user storage
        $userId = auth()->user()->id;
        Log::info("Bot received message: {$message} from user ID: {$userId}");
        if (!$userId) {
            $bot->reply("Please log in to use this feature.");
            return;
        }

        $lowerMessage = strtolower($message);

        if (strpos($lowerMessage, 'my details') !== false) {
            $this->showUserDetails($bot, $userId);
        }elseif (strpos($lowerMessage, 'company policies') !== false) {
            $this->showCompanyPolicies($bot);
        } elseif (strpos($lowerMessage, 'leave balance') !== false) {
            $this->showLeaveBalance($bot, $userId);
        } elseif (strpos($lowerMessage, 'attendance') !== false) {
            $this->showAttendance($bot, $userId);
        } elseif (strpos($lowerMessage, 'apply for leave') !== false) {
            $bot->startConversation(new LeaveApplicationConversation());
        } elseif (strpos($lowerMessage, 'help') !== false) {
            $this->showHelp($bot);
        }elseif (strpos($lowerMessage, 'working hours') !== false) {
            $bot->reply("Our working hours are from 9 AM to 6 PM, Monday to Friday.");
        }
        else {
            $bot->reply("I'm sorry, I didn't understand that. You can ask me about leave balance, attendance, or company policies.");
        }
    }
    protected function showCompanyPolicies($bot)
    {
        $policies = "Here are some of the key company policies:\n";
        $policies .= "- Work hours: 9 AM to 6 PM\n";
        $policies .= "- Leave application: Submit at least 3 days in advance\n";
        $policies .= "- Dress code: Business casual\n";
        $policies .= "For more details, refer to the employee handbook.";

        $bot->reply($policies);
    }


    protected function showUserDetails($bot, $userId)
    {
        $employee = Employee::where('user_id', $userId)->first();
        Log::info("Bot received message: {$userId} from user ID: {$userId}");
        if ($employee) {
            $department = $employee->department ? $employee->department->department_name : 'N/A';
            $designation = $employee->designation ? $employee->designation->designation_name : 'N/A';

            $message = "Here are your details:\n";
            $message .= "Name: {$employee->first_name} {$employee->last_name}\n";
            $message .= "Department: {$department}\n";
            $message .= "Designation: {$designation}";

            $bot->reply($message);
        } else {
            $bot->reply("Sorry, I couldn't find your details.");
        }
    }

    protected function showLeaveBalance($bot, $userId)
    {
        // Fetch leave balance from the database
        $leaveBalance = DB::table('apply_leaves')
            ->where('employee_id', $userId)
            ->sum();
            Log::info("Bot received message: {$leaveBalance} from user ID: {$userId}");
        $bot->reply("Your current leave balance is: {$leaveBalance} days.");
    }

    protected function showAttendance($bot, $userId)
    {
        // Fetch attendance records from your database
        $attendance = DB::table('attendances') // Adjust table name as per your schema
            ->where('employee_id', $userId)
            ->orderBy('time', 'desc')
            ->limit(5)
            ->get();
            Log::info("Bot received message: {$attendance} from user ID: {$userId}");
        if ($attendance->isEmpty()) {
            $bot->reply("You have no attendance records.");
        } else {
            $response = "Your recent attendance records:\n";
            foreach ($attendance as $record) {
                $status = $record->status; // e.g., Present, Absent
                $date = $record->date;
                $response .= "{$date}: {$status}\n";
            }
            $bot->reply($response);
        }
    }
    protected function showHelp($bot)
    {
        $question = \BotMan\BotMan\Messages\Outgoing\Question::create("I can assist you with the following commands:")
            ->addButtons([
                \BotMan\BotMan\Messages\Outgoing\Actions\Button::create('My Details')->value('my details'),
                \BotMan\BotMan\Messages\Outgoing\Actions\Button::create('Leave Balance')->value('leave balance'),
                \BotMan\BotMan\Messages\Outgoing\Actions\Button::create('Attendance')->value('attendance'),
                \BotMan\BotMan\Messages\Outgoing\Actions\Button::create('Apply for Leave')->value('apply for leave'),
                \BotMan\BotMan\Messages\Outgoing\Actions\Button::create('Help')->value('help'),
            ]);

        // Capture the controller instance


        $bot->ask($question, function (Answer $answer) use ($bot) {
            $this->botResponses($bot, $answer->getValue());
        })->bindTo($this);

    }



}
