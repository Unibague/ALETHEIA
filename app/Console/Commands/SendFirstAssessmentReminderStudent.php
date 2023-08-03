<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendFirstAssessmentReminderStudent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firstReminderStudent:send';

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

        $todayDate = new \DateTime("today");

        $todayDate = $todayDate->format('d/m/Y');

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        set_time_limit(10000);

        //Primer Correo para estudiantes

        foreach ($academicPeriods as $academicPeriod) {

            $studentsDates = DB::table('academic_periods as acp')->select('students_start_date as ssd', 'students_end_date as sed')
                ->where('acp.assessment_period_id', '=', $activeAssessmentPeriodId)->where('acp.id', '=', $academicPeriod->id)->first();
            /*        dd($students);*/

            $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('send_reminder_before', '=', 'start')->first()->days_in_advance;

            $emailDate = Carbon::parse($studentsDates->ssd)->toDate()->modify("-" . $anticipationDays . "days")->format('d/m/Y');

            /*                dd($studentsDates->ssd, $emailDate);*/

            if ($todayDate == $emailDate) {

                $students = DB::table('reminder_before_start_users as r')->select(['u.id as user_id', 'u.name', 'u.email'])->where('r.academic_period_id', '=', $academicPeriod->id)
                    ->join('users as u', 'u.id', '=', 'r.user_id')
                    ->where('r.assessment_period_id', '=', $activeAssessmentPeriodId)->where('r.status', '=', 'Not Started')->take(100)->get()->toArray();

                if (count($students) == 0) {

                    $students = DB::table('reminder_before_start_users as r')->select(['u.id as user_id', 'u.name'])->where('r.academic_period_id', '=', $academicPeriod->id)
                        ->join('users as u', 'u.id', '=', 'r.user_id')
                        ->where('r.assessment_period_id', '=', $activeAssessmentPeriodId)->where('r.status', '=', 'In Progress')->take(100)->get()->toArray();

                    if (count($students) == 0) {
                        continue;
                    }

                    $referenceToOriginalStudents = $students;

                }

                else {

                    $referenceToOriginalStudents = $students;

                    $selectedStudentsIds = array_unique(array_column($students, 'user_id'));

                    //Set users with status In Progress
                    DB::table('reminder_before_start_users')->where('academic_period_id', '=', $academicPeriod->id)
                        ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->whereIn('user_id', $selectedStudentsIds)->update(['status' => 'In Progress']);

                }


                foreach ($referenceToOriginalStudents as $student) {

                    $studentTeachersToEvaluate = [];

                    $studentTeachers = DB::table('group_user as gu')->select(['gu.user_id', 'u.name as teacher_name', 'g.name as group_name'])
                        ->join('groups as g', 'gu.group_id', '=', 'g.group_id')
                        ->join('users as u', 'g.teacher_id', '=', 'u.id')
                        ->where('gu.academic_period_id', '=', $academicPeriod->id)->where('user_id', '=', $student->user_id)
                        ->get();

                    /*                dd($studentTeachers);*/

                    foreach ($studentTeachers as $studentTeacher) {

                        $teacherInfo = (object)['teacher_name' => $studentTeacher->teacher_name,
                            'group_name' => $studentTeacher->group_name];

                        $studentTeachersToEvaluate [] = $teacherInfo;

                    }

                    $data = [
                        'role' => 'Estudiante',
                        'name' => $student->name,
                        'teachers_to_evaluate' => $studentTeachersToEvaluate,
                        'start_date' => $studentsDates->ssd,
                        'end_date' => $studentsDates->sed,
                        'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
                    ];

                    $email = new \App\Mail\FirstReminderMailable($data);

                    Mail::bcc(['benitorodriguez141@gmail.com'])->send($email);

                    DB::table('reminder_before_start_users')->where('academic_period_id', '=', $academicPeriod->id)
                        ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->where('user_id', '=', $student->user_id)
                        ->update(['status' => 'Done']);

                }

                $confirmationEmail = new \App\Mail\ConfirmationFinishSend();

                Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);

            }

        }

        //Primer Correo para estudiantes


        return 0;
    }
}
