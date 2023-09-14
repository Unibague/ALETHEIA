<?php

namespace App\Console\Commands;

use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckForDueAcademicPeriodsBFinishAssessment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academic_periods:check_due_b_finish';

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

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $anticipationDays = DB::table('assessment_reminder')->select(['days_in_advance'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('send_reminder_before', '=', 'finish')->first()->days_in_advance;
        $emailDate = Carbon::now()->addDays($anticipationDays)->toDateString();

        $dueAcademicPeriods = DB::table('academic_periods')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('students_end_date', '=', $emailDate )->get();

        if(count($dueAcademicPeriods) > 0){

            foreach ($dueAcademicPeriods as $academicPeriod) {

                $students = DB::table('group_user as gu')->select(['gu.user_id as id', 'u.name', 'u.email'])
                    ->join('users as u', 'u.id', '=', 'gu.user_id')
                    ->where('gu.academic_period_id', '=', $academicPeriod->id)
                    ->where('gu.has_answer', '=', 0)->distinct()->get();

                if(count($students) == 0){
                    continue;
                }

                foreach ($students as $student) {

                    $studentTeachersLeftToEvaluate = [];

                    $studentTeachers = DB::table('group_user as gu')->select(['gu.user_id', 'u.name as teacher_name', 'g.name as group_name'])
                        ->join('groups as g', 'gu.group_id', '=', 'g.group_id')
                        ->join('users as u', 'g.teacher_id', '=', 'u.id')
                        ->where('gu.academic_period_id', '=', $academicPeriod->id)->where('gu.user_id', '=', $student->id)
                        ->where('gu.has_answer', '=', 0)->get();

                    if (count($studentTeachers) == 0){
                        //Si no hay docentes pues no se agrega a la lista de correspondencia
                        continue;
                    }

                    foreach ($studentTeachers as $studentTeacher) {

                        if ($studentTeacher->group_name == 'ADULTOS--EXAMEN DE CLASIFICACION' || $studentTeacher->group_name == 'NI?OS--EXAMEN DE CLASIFICACION'
                            || $studentTeacher->group_name == 'EXAMEN DE SUFICIENCIA') {

                            continue;

                        }
                        $teacherInfo = (object)['teacher_name' => $studentTeacher->teacher_name,
                            'group_name' => $studentTeacher->group_name];
                        $studentTeachersLeftToEvaluate [] = $teacherInfo;

                    }

                    if(count($studentTeachersLeftToEvaluate) == 0){
                        continue;
                    }

                    $parameters = json_encode(['name' => $student->name, 'role' => 'Estudiante',
                        'teachers_to_evaluate' => $studentTeachersLeftToEvaluate,
                        'start_date' => $academicPeriod->students_start_date, 'end_date' => $academicPeriod->students_end_date]);

                    DB::table('assessment_reminder_users')->updateOrInsert(['email' => $student->email,
                        'academic_period_id' => $academicPeriod->id,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'before_start_or_finish_assessment' => 'Finish'],
                        ['email_parameters' => $parameters, 'status' => 'Not Started']);
                }
            }
        }
        return 0;
    }
}
