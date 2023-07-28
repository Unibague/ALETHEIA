<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecondReminderMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $subject = "Recordatorio: Finalización de Evaluación docente";
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('secondEmailReminder')->with('data', $this->data);
    }
}
