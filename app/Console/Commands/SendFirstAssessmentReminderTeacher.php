<?php

namespace App\Console\Commands;

use App\Helpers\AtlanteProvider;
use App\Mail\FirstReminderMailable;
use App\Mail\SendReminderMailable;
use App\Mail\TestCronjobSendReminderMailable;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendFirstAssessmentReminderTeacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firstReminderTeacher:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para enviar el recordatorio de la evaluación docente a los ESTUDIANTES de la siguiente manera: un día antes de iniciar la evaluación docente,
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

        $todayDate = new \DateTime("today");

        $todayDate = $todayDate->format('d/m/Y');

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        set_time_limit(10000);

        /*Correo para jefes*/

        $bossesDates = DB::table('assessment_periods as asp')->select('boss_start_date as bsd', 'boss_end_date as bed')
            ->where('asp.active', '=', $activeAssessmentPeriodId)->first();

        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'start')->first()->days_in_advance;

        $emailDate = Carbon::parse($bossesDates->bsd)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

        if($todayDate === $emailDate){

            $bossesFromUnits = DB::table('unity_assessments as ua')->where('role', '=', 'jefe')
                ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id','u.email', 'u.name'])->distinct()
                ->join('users as u', 'u.id',  '=', 'ua.evaluator_id')->get()->toArray();

            /*        dd($bossesFromUnits);*/

            foreach ($bossesFromUnits as $boss){

                $bossTeachersToEvaluate = DB::table('unity_assessments as ua')->select(['u.name as evaluated_teacher_name'])
                    ->where('role', '=', 'jefe')
                    ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('ua.evaluator_id', '=', $boss->id)
                    ->join('users as u', 'u.id',  '=', 'ua.evaluated_id')
                    ->orderBy('evaluated_teacher_name', 'asc')->get()->toArray();


                $bossTeachersToEvaluate = array_unique(array_column($bossTeachersToEvaluate, 'evaluated_teacher_name'));

                /*            dd($bossTeachersToEvaluate);*/

                $data = [
                    'role'=>'Jefe de Evaluación 360°',
                    'name'=> $boss->name,
                    'teachers_to_evaluate'=> $bossTeachersToEvaluate,
                    'start_date' => $bossesDates->bsd,
                    'end_date' => $bossesDates->bed,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\FirstReminderMailable($data);

                Mail::bcc([$boss->email])->send($email);

            }


            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }


        /*Correo para autoevaluación*/

        $autoAssessmentsDates = DB::table('assessment_periods as asp')->select('self_start_date as ssd', 'self_end_date as sed')
            ->where('asp.active', '=', $activeAssessmentPeriodId)->first();

        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'start')->first()->days_in_advance;

        $emailDate = Carbon::parse($autoAssessmentsDates->ssd)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

        if($todayDate === $emailDate){

            $autoAssessmentsFromUnits = DB::table('unity_assessments')->where('role', '=', 'autoevaluación')
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id','u.email', 'u.name'])
                ->join('users as u', 'u.id',  '=', 'unity_assessments.evaluator_id')->get()->toArray();


            foreach ($autoAssessmentsFromUnits as $teacher){

                $data = [
                    'role'=>'Autoevaluación',
                    'name'=> $teacher->name,
                    'start_date' => $autoAssessmentsDates->ssd,
                    'end_date' => $autoAssessmentsDates->sed,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\FirstReminderMailable($data);

                Mail::bcc([$teacher->email])->send($email);

            }



            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }



        /*Correo para autoevaluación*/



        /*Correo para pares*/

        $peersDates = DB::table('assessment_periods as asp')->select('colleague_start_date as csd', 'colleague_end_date as ced')
            ->where('asp.active', '=', $activeAssessmentPeriodId)->first();

        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'start')->first()->days_in_advance;

        $emailDate = Carbon::parse($peersDates->csd)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

        if($todayDate === $emailDate){

            $peersFromUnits = DB::table('unity_assessments as ua')->where('role', '=', 'par')
                ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id','u.email', 'u.name'])->distinct()
                ->join('users as u', 'u.id',  '=', 'ua.evaluator_id')->get()->toArray();

            /*        dd($bossesFromUnits);*/

            foreach ($peersFromUnits as $peer){

                $peerTeachersToEvaluate = DB::table('unity_assessments as ua')->select(['u.name as evaluated_teacher_name'])
                    ->where('role', '=', 'par')
                    ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('ua.evaluator_id', '=', $peer->id)
                    ->join('users as u', 'u.id',  '=', 'ua.evaluated_id')
                    ->orderBy('evaluated_teacher_name', 'asc')->get()->toArray();


                $peerTeachersToEvaluate = array_unique(array_column($peerTeachersToEvaluate, 'evaluated_teacher_name'));

                /*            dd($bossTeachersToEvaluate);*/

                $data = [
                    'role'=>'Par de Evaluación 360°',
                    'name'=> $peer->name,
                    'teachers_to_evaluate'=> $peerTeachersToEvaluate,
                    'start_date' => $peersDates->csd,
                    'end_date' => $peersDates->ced,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\FirstReminderMailable($data);

                Mail::bcc([$peer->email])->send($email);

            }


            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }

        return 0;

    }
}
