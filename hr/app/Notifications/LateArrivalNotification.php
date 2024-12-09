<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\HumanResource\Entities\Employee;

class LateArrivalNotification extends Notification
{
    

    protected $employee;
    protected $lateMinutes;
    protected $date;



    public function __construct(User $employee, int $lateMinutes, string $date)
    {
        $this->employee = $employee;
        $this->lateMinutes = $lateMinutes;
        $this->date = $date;
    }

    /**
     * Get the employee.
     *
     * @return Employee
     */




    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // Add other channels if needed (e.g., 'database', 'sms')
    }


    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Late Arrival Notification')
                    ->greeting('Hello ' . $this->employee->full_name . ',')
                    ->line('You were marked as late on ' . \Carbon\Carbon::parse($this->date)->format('F j, Y') . '.')
                    ->line('You were ' . $this->lateMinutes . ' minutes late.')
                    ->line('Please ensure to adhere to the scheduled work timings.')
                    ->action('View Attendance', url('/attendance'))
                    ->line('Thank you for your attention.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
