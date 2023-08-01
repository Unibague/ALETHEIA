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

        $todayDate = new \DateTime("today");

        $todayDate = $todayDate->format('d/m/Y');


        if ($todayDate == "01/08/2023"){

            $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
            $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

            set_time_limit(10000);

            /*    foreach ($academicPeriods as $academicPeriod) {*/

            $students = DB::table('reminder_before_start_users as r')->select(['u.id as user_id', 'u.name'])->where('r.academic_period_id', '=', 1)
                ->join('users as u', 'u.id', '=', 'r.user_id')
                ->where('r.assessment_period_id', '=', $activeAssessmentPeriodId)->where('r.status', '=', 'Not Started')->take(100)->get()->toArray();

            $studentsDates = DB::table('academic_periods as acp')->select('students_start_date as ssd', 'students_end_date as sed')
                ->where('acp.assessment_period_id', '=', $activeAssessmentPeriodId)->where('acp.id', '=', 1)->first();

            $referenceToOriginalStudents = $students;

            $selectedStudentsIds = array_unique(array_column($students, 'user_id'));

            //Set users with status In Progress
            DB::table('reminder_before_start_users')->where('academic_period_id', '=', 1)
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->whereIn('user_id', $selectedStudentsIds)->update(['status' => 'In Progress']);

            /*        dd($students);*/

            foreach ($referenceToOriginalStudents as $student){

                $studentTeachersToEvaluate = [];

                $studentTeachers = DB::table('group_user as gu')->select(['gu.user_id', 'u.name as teacher_name', 'g.name as group_name'])
                    ->join('groups as g', 'gu.group_id', '=', 'g.group_id')
                    ->join('users as u', 'g.teacher_id', '=', 'u.id')
                    ->where('gu.academic_period_id', '=', 1)->where('user_id', '=', $student->user_id)
                    ->get();

                /*                dd($studentTeachers);*/

                foreach ($studentTeachers as $studentTeacher) {

                    if ($studentTeacher->group_name == 'ADULTOS--EXAMEN DE CLASIFICACION' || $studentTeacher->group_name == 'NI?OS--EXAMEN DE CLASIFICACION'){

                        continue;

                    }

                    $teacherInfo = (object)['teacher_name' => $studentTeacher->teacher_name,
                        'group_name' => $studentTeacher->group_name];

                    $studentTeachersToEvaluate [] = $teacherInfo;

                }

                if(count($studentTeachersToEvaluate) == 0){


                    continue;

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

                DB::table('reminder_before_start_users')->where('academic_period_id', '=', 1)
                    ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->where('user_id', '=', $student->user_id)
                    ->update(['status' => 'Done']);

            }

        }


 /*       $data = [
            'role'=>'Estudiante',
            'name'=> 'fkwkfwekfkwefe',
            'teachers_to_evaluate' => [],
            'start_date' => 'kwekfwkefkwe',
            'end_date' => 'rglereglreglreglre',
            'assessment_period_name' => 'fwekfkwefkwfkwekfwekfek'
        ];

        $email = new FirstReminderMailable($data);

/*        Mail::bcc(['juan.gonzalez10@unibague.edu.co'])->send($email);*/

/*        $groups = AtlanteProvider::get('enrolls', [
            'periods' =>  AcademicPeriod::getCurrentAcademicPeriodsByCommas()
        ], true);*/


/*        $studentsDates = DB::table('academic_periods as acp')->select('students_start_date as ssd', 'students_end_date as sed')->first();

        $firstEmailDate = Carbon::parse($studentsDates->ssd)->toDate()->modify('-1 day')->format('d/m/Y');

        $secondEmailDate = Carbon::parse($studentsDates->ssd)->toDate()->modify('-2 days')->format('d/m/Y');*/

        return 0;
    }
}
