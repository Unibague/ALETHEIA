<?php

namespace App\Console\Commands;

use App\Mail\SendReminderMailable;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendAssessmentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para enviar el recordatorio de la evaluación docente de la siguiente manera: un día antes de iniciar la evaluación docente,
    se envía correo con las fechas de inicio y cierre de la evlauación. Y 2 días antes de acabar el periodo de evaluación se envía otra notificación indicando que la
    evaluación está pronta a terminar y los profesores que aún le falta por evaluar';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $email = new SendReminderMailable();
        Mail::bcc(['juan.gonzalez10@unibague.edu.co'])->send($email);


        $todayDate = new \DateTime("today");

        $todayDate = $todayDate->format('d/m/Y');

/*        $studentsDates = DB::table('academic_periods as acp')->select('students_start_date as ssd', 'students_end_date as sed')->first();

        $firstEmailDate = Carbon::parse($studentsDates->ssd)->toDate()->modify('-1 day')->format('d/m/Y');

        $secondEmailDate = Carbon::parse($studentsDates->ssd)->toDate()->modify('-2 days')->format('d/m/Y');*/


        if($todayDate === "26/07/2023"){

            Mail::bcc(['juan.gonzalez10@unibague.edu.co'])->send($email);

        }

        return 0;
    }
}