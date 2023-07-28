<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestCronjobSendReminderMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Recordatorio: Comienzo de Evaluación docente - Jefe de docente";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('reminder');
    }
}
