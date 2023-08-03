<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendSecondAssessmentReminderTeacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secondReminderTeacher:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        //Correo Jefes

        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'finish')->first()->days_in_advance;

        $emailDate = Carbon::parse($bossesDates->bed)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

        if($todayDate === $emailDate){

            $bossesFromUnits = DB::table('unity_assessments as ua')->where('role', '=', 'jefe')
                ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id', 'u.email', 'u.name'])->distinct()
                ->join('users as u', 'u.id', '=', 'ua.evaluator_id')->get()->toArray();

            /*        dd($bossesFromUnits);*/

            foreach ($bossesFromUnits as $boss) {

                $bossTeachersToEvaluate = DB::table('unity_assessments as ua')->select(['u.name as evaluated_teacher_name'])
                    ->where('role', '=', 'jefe')
                    ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('ua.evaluator_id', '=', $boss->id)
                    ->where('ua.pending', '=', 1)
                    ->join('users as u', 'u.id', '=', 'ua.evaluated_id')
                    ->orderBy('evaluated_teacher_name', 'asc')->get()->toArray();

                if (count($bossTeachersToEvaluate) == 0) {

                    continue;

                }

                $bossTeachersToEvaluate = array_unique(array_column($bossTeachersToEvaluate, 'evaluated_teacher_name'));

                /*            dd($bossTeachersToEvaluate);*/

                $data = [
                    'role' => 'Jefe de Evaluación 360°',
                    'name' => $boss->name,
                    'teachers_to_evaluate' => $bossTeachersToEvaluate,
                    'start_date' => $bossesDates->bsd,
                    'end_date' => $bossesDates->bed,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\SecondReminderMailable($data);

                Mail::bcc([$boss->email])->send($email);

            }


            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }
        /*Correo para jefes*/



        /*Correo para autoevaluación*/
        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'finish')->first()->days_in_advance;

        $emailDate = Carbon::parse($autoAssessmentsDates->sed)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

        if($todayDate === $emailDate){

            $autoAssessmentsFromUnits = DB::table('unity_assessments')->where('role', '=', 'autoevaluación')
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id', 'u.email', 'u.name'])
                ->where('unity_assessments.pending', '=', 1)
                ->join('users as u', 'u.id', '=', 'unity_assessments.evaluator_id')->get()->toArray();

            foreach ($autoAssessmentsFromUnits as $teacher) {

                $data = [
                    'role' => 'Autoevaluación',
                    'name' => $teacher->name,
                    'start_date' => $autoAssessmentsDates->ssd,
                    'end_date' => $autoAssessmentsDates->sed,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\SecondReminderMailable($data);

                Mail::bcc([$teacher->email])->send($email);

            }


            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }
        /*Correo para autoevaluación*/





        /*Correo para pares*/

        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'start')->first()->days_in_advance;

        $emailDate = Carbon::parse($peersDates->ced)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');


        if($todayDate === $emailDate){

            $peersFromUnits = DB::table('unity_assessments as ua')->where('role', '=', 'par')
                ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)->select(['u.id', 'u.email', 'u.name'])->distinct()
                ->join('users as u', 'u.id', '=', 'ua.evaluator_id')->get()->toArray();

            /*        dd($bossesFromUnits);*/

            foreach ($peersFromUnits as $peer) {

                $peerTeachersToEvaluate = DB::table('unity_assessments as ua')->select(['u.name as evaluated_teacher_name'])
                    ->where('role', '=', 'par')
                    ->where('ua.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('ua.evaluator_id', '=', $peer->id)
                    ->where('ua.pending', '=', 1)
                    ->join('users as u', 'u.id', '=', 'ua.evaluated_id')
                    ->orderBy('evaluated_teacher_name', 'asc')->get()->toArray();

                if (count($peerTeachersToEvaluate) === 0) {
                    continue;
                }

                $peerTeachersToEvaluate = array_unique(array_column($peerTeachersToEvaluate, 'evaluated_teacher_name'));

                /*            dd($bossTeachersToEvaluate);*/

                $data = [
                    'role' => 'Par de Evaluación 360°',
                    'name' => $peer->name,
                    'teachers_to_evaluate' => $peerTeachersToEvaluate,
                    'start_date' => $peersDates->csd,
                    'end_date' => $peersDates->ced,
                    'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                ];


                $email = new \App\Mail\SecondReminderMailable($data);

                Mail::bcc([$peer->email])->send($email);

            }

            $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

        }

        /*Correo para pares*/


        return 0;
    }
}
