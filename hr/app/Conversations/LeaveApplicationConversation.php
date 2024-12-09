<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Incoming\Answer;
use DB;
use Illuminate\Support\Str;

class LeaveApplicationConversation extends Conversation
{
    protected $leaveType;
    protected $startDate;
    protected $endDate;

    public function run()
    {
        $this->askLeaveType();
    }

    protected function askLeaveType()
    {
        $question = Question::create('What type of leave would you like to apply for?')
            ->addButtons([
                Button::create('Annual Leave')->value('4'),
                Button::create('Sick Leave')->value('3'),
                Button::create('Casual Leave')->value('1'),
            ]);

        $this->ask($question, function (Answer $answer) {
            $this->leaveType = $answer->getValue();
            $this->askStartDate();
        });
    }

    protected function askStartDate()
    {
        $this->ask('Please enter the start date (YYYY-MM-DD):', function (Answer $answer) {
            $this->startDate = $answer->getText();
            $this->askEndDate();
        });
    }

    protected function askEndDate()
    {
        $this->ask('Please enter the end date (YYYY-MM-DD):', function (Answer $answer) {
            $this->endDate = $answer->getText();
            $this->confirmApplication();
        });
    }

    protected function confirmApplication()
    {
        // Assuming BotMan has user storage with 'user_id'
        $userId = auth()->user()->id;

        if (!$userId) {
            $this->say("Error: User not authenticated.");
            return;
        }

        // Save the leave application to the database
        DB::table('apply_leaves')->insert([
            'uuid' => Str::uuid(),
            'employee_id' => $userId,
            'leave_type_id' => $this->leaveType,
            'leave_apply_start_date' => $this->startDate,
            'leave_apply_end_date' => $this->endDate,
            'is_approved' => '0',
            'leave_apply_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->say('Your leave application has been submitted successfully.');
    }
}
