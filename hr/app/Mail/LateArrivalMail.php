<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LateArrivalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $lateMinutes;
    public $date;

    /**
     * Create a new message instance.
     *
     * @param $employee
     * @param int $lateMinutes
     * @param string $date
     */
    public function __construct($employee, int $lateMinutes, string $date)
    {
        $this->employee = $employee;
        $this->lateMinutes = $lateMinutes;
        $this->date = $date;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Late Arrival Notification',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.late_arrival',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
