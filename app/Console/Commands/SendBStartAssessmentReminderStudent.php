<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendBStartAssessmentReminderStudent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder_student:send_b_start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para enviar el recordatorio de la evaluación docente a los DOCENTES de la siguiente manera: un día antes de iniciar la evaluación docente,
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

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        set_time_limit(10000);

        $emailsToSent = DB::table('assessment_reminder_users')->where('status', '=', 'Not Started')
            ->where('before_start_or_finish_assessment', '=', 'Start')
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->take(100)->get();

        if (count($emailsToSent) == 0) {

            $emailsToSent = DB::table('assessment_reminder_users')->where('status', '=', 'In Progress')
                ->where('before_start_or_finish_assessment', '=', 'Start')
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->take(100)->get();
        }

        foreach ($emailsToSent as $student){

            $emailParameters = json_decode($student->email_parameters);
            DB::table('assessment_reminder_users')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('id', '=', $student->id)->update(['status' => 'In Progress']);

            $data = [
                'role' => $emailParameters->role,
                'name' => $emailParameters->name,
                'teachers_to_evaluate' => $emailParameters->teachers_to_evaluate,
                'start_date' => $emailParameters->start_date,
                'end_date' => $emailParameters->end_date,
                'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
            ];

            $email = new \App\Mail\FirstReminderMailable($data);
            Mail::bcc([$student->email])->send($email);
            DB::table('assessment_reminder_users')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('id', '=', $student->id)->update(['status' => 'Done']);
        }


            $issue = 'Lote de Estudiantes previos a empezar evaluación docente';
            $confirmationEmail = new \App\Mail\ConfirmationFinishSend($issue);
            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);


        return 0;
    }
}
