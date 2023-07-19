<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateReportResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para ejecutar los queries necesarios en el proceso de actualizar los resultados de las tablas de los reportes de las evaluaciones docentes';

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

        set_time_limit(300);

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $teachers = DB::table('form_answers as fa')->select(['fa.teacher_id'])->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->where('f.type', '=', 'estudiantes')
            ->where('f.creation_assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();

        $uniqueTeachers = array_column($teachers, 'teacher_id');

        $uniqueTeachers = array_unique($uniqueTeachers);

        foreach ($uniqueTeachers as $uniqueTeacher) {

            $groupsFromTeacher = DB::table('form_answers as fa')->select(['fa.group_id'])
                ->join('forms as f', 'fa.form_id', '=', 'f.id')->join('groups', 'fa.group_id', '=', 'groups.group_id')
                ->where('f.type', '=', 'estudiantes')->where('fa.teacher_id', '=', $uniqueTeacher)->get()->toArray();

            $uniqueGroupsId = array_column($groupsFromTeacher, 'group_id');

            $uniqueGroupsId = array_unique($uniqueGroupsId);

            foreach ($uniqueGroupsId as $uniqueGroupId) {

                $final_first_competence_average = 0;
                $final_second_competence_average = 0;
                $final_third_competence_average = 0;
                $final_fourth_competence_average = 0;
                $final_fifth_competence_average = 0;
                $final_sixth_competence_average = 0;


                $totalStudentsEnrolledOnGroup = DB::table('group_user')->where('group_id', '=', $uniqueGroupId)->get()->count();

                $serviceAreaCodeFromGroup = DB::table('form_answers as fa')->select(['groups.service_area_code'])
                    ->join('forms as f', 'fa.form_id', '=', 'f.id')->join('groups', 'fa.group_id', '=', 'groups.group_id')
                    ->where('f.type', '=', 'estudiantes')->where('fa.teacher_id', '=', $uniqueTeacher)
                    ->where('fa.group_id', '=', $uniqueGroupId)->first()->service_area_code;

                $hourTypeFromGroup = DB::table('form_answers as fa')->select(['groups.hour_type'])
                    ->join('forms as f', 'fa.form_id', '=', 'f.id')->join('groups', 'fa.group_id', '=', 'groups.group_id')
                    ->where('f.type', '=', 'estudiantes')->where('fa.teacher_id', '=', $uniqueTeacher)
                    ->where('fa.group_id', '=', $uniqueGroupId)->first()->hour_type;

                $answersFromGroup = DB::table('form_answers as fa')->select(['fa.teacher_id', 'fa.group_id', 'fa.first_competence_average', 'fa.second_competence_average',
                    'fa.third_competence_average', 'fa.fourth_competence_average', 'fa.fifth_competence_average', 'fa.sixth_competence_average', 'groups.service_area_code',
                    'groups.hour_type'])
                    ->join('forms as f', 'fa.form_id', '=', 'f.id')->join('groups', 'fa.group_id', '=', 'groups.group_id')
                    ->where('f.type', '=', 'estudiantes')->where('fa.teacher_id', '=', $uniqueTeacher)
                    ->where('fa.group_id', '=', $uniqueGroupId)->get()->toArray();


                $studentsAmount = count($answersFromGroup);


                foreach ($answersFromGroup as $key => $answerFromGroup) {

                    $final_first_competence_average += $answersFromGroup[$key]->first_competence_average;
                    $final_second_competence_average += $answerFromGroup->second_competence_average;
                    $final_third_competence_average += $answerFromGroup->third_competence_average;
                    $final_fourth_competence_average += $answerFromGroup->fourth_competence_average;
                    $final_fifth_competence_average += $answerFromGroup->fifth_competence_average;
                    $final_sixth_competence_average += $answerFromGroup->sixth_competence_average;

                }


                $final_first_competence_average /= $studentsAmount;
                $final_second_competence_average /= $studentsAmount;
                $final_third_competence_average /= $studentsAmount;
                $final_fourth_competence_average /= $studentsAmount;
                $final_fifth_competence_average /= $studentsAmount;
                $final_sixth_competence_average /= $studentsAmount;


                $final_first_competence_average = number_format($final_first_competence_average, 1);
                $final_second_competence_average = number_format($final_second_competence_average, 1);
                $final_third_competence_average = number_format($final_third_competence_average, 1);
                $final_fourth_competence_average = number_format($final_fourth_competence_average, 1);
                $final_fifth_competence_average = number_format($final_fifth_competence_average, 1);
                $final_sixth_competence_average = number_format($final_sixth_competence_average, 1);


                DB::table('group_results')->updateOrInsert(['teacher_id' => $uniqueTeacher, 'group_id' => $uniqueGroupId,
                    'assessment_period_id' => $activeAssessmentPeriodId],
                    ['hour_type' => $hourTypeFromGroup, 'service_area_code' => $serviceAreaCodeFromGroup, 'first_final_competence_average' => $final_first_competence_average, 'second_final_competence_average' => $final_second_competence_average,
                        'third_final_competence_average' => $final_third_competence_average, 'fourth_final_competence_average' => $final_fourth_competence_average,
                        'fifth_final_competence_average' => $final_fifth_competence_average, 'sixth_final_competence_average' => $final_sixth_competence_average,
                        'students_amount_reviewers' => $studentsAmount, 'students_amount_on_group' => $totalStudentsEnrolledOnGroup, 'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
            }

        }


        $finalResultsFromTeachersOnGroups = DB::table('group_results as gr')->select(['gr.teacher_id'])->where('hour_type', '=', 'normal')->get()->toArray();

        $uniqueTeachers = array_column($finalResultsFromTeachersOnGroups, 'teacher_id');

        $uniqueTeachers = array_unique($uniqueTeachers);

        foreach ($uniqueTeachers as $uniqueTeacher){

            $finalResultsFromTeacherOnGroups = DB::table('group_results as gr')->where('teacher_id', '=', $uniqueTeacher)
                ->where('hour_type', '=', 'normal')->get();

            $groupsAmount = count($finalResultsFromTeacherOnGroups);

            $aggregateTotalStudentsReviewersOnGroups = 0;
            $aggregateTotalStudentsEnrolledOnGroups = 0;

            $final_first_aggregate_competence_average = 0;
            $final_second_aggregate_competence_average = 0;
            $final_third_aggregate_competence_average = 0;
            $final_fourth_aggregate_competence_average = 0;
            $final_fifth_aggregate_competence_average = 0;
            $final_sixth_aggregate_competence_average = 0;
            /*

                        if ($uniqueTeacher == 181){

                            dd($finalResultsFromTeacherOnGroups);

                        }*/


            foreach ($finalResultsFromTeacherOnGroups as $key=>$finalResultsFromTeacherOnGroup){

                $aggregateTotalStudentsReviewersOnGroups += $finalResultsFromTeacherOnGroups[$key]->students_amount_reviewers;
                $aggregateTotalStudentsEnrolledOnGroups += $finalResultsFromTeacherOnGroups[$key]->students_amount_on_group;

                $final_first_aggregate_competence_average += $finalResultsFromTeacherOnGroup->first_final_competence_average;
                $final_second_aggregate_competence_average += $finalResultsFromTeacherOnGroup->second_final_competence_average;
                $final_third_aggregate_competence_average += $finalResultsFromTeacherOnGroup->third_final_competence_average;
                $final_fourth_aggregate_competence_average +=$finalResultsFromTeacherOnGroup->fourth_final_competence_average;
                $final_fifth_aggregate_competence_average += $finalResultsFromTeacherOnGroup->fifth_final_competence_average;
                $final_sixth_aggregate_competence_average += $finalResultsFromTeacherOnGroup->sixth_final_competence_average;

            }


            /*    if ($uniqueTeacher == 181){

                    dd($final_first_aggregate_competence_average,$final_second_aggregate_competence_average);
                    dd($aggregateTotalStudentsReviewersOnGroups);

                }*/


            $final_first_aggregate_competence_average /= $groupsAmount;
            $final_second_aggregate_competence_average /= $groupsAmount;
            $final_third_aggregate_competence_average /= $groupsAmount;
            $final_fourth_aggregate_competence_average /= $groupsAmount;
            $final_fifth_aggregate_competence_average /= $groupsAmount;
            $final_sixth_aggregate_competence_average /= $groupsAmount;

            $final_first_aggregate_competence_average = number_format($final_first_aggregate_competence_average, 1);
            $final_second_aggregate_competence_average = number_format($final_second_aggregate_competence_average, 1);
            $final_third_aggregate_competence_average = number_format($final_third_aggregate_competence_average, 1);
            $final_fourth_aggregate_competence_average = number_format($final_fourth_aggregate_competence_average, 1);
            $final_fifth_aggregate_competence_average = number_format($final_fifth_aggregate_competence_average, 1);
            $final_sixth_aggregate_competence_average = number_format($final_sixth_aggregate_competence_average, 1);

            DB::table('teachers_students_perspectives')->updateOrInsert(['teacher_id' => $uniqueTeacher,'assessment_period_id' => $activeAssessmentPeriodId],
                ['first_final_aggregate_competence_average' => $final_first_aggregate_competence_average,
                    'second_final_aggregate_competence_average' => $final_second_aggregate_competence_average,
                    'third_final_aggregate_competence_average' => $final_third_aggregate_competence_average,
                    'fourth_final_aggregate_competence_average' => $final_fourth_aggregate_competence_average,
                    'fifth_final_aggregate_competence_average' => $final_fifth_aggregate_competence_average,
                    'sixth_final_aggregate_competence_average' => $final_sixth_aggregate_competence_average,
                    'groups_amount' => $groupsAmount,
                    'aggregate_students_amount_reviewers' => $aggregateTotalStudentsReviewersOnGroups,
                    'aggregate_students_amount_on_360_groups' => $aggregateTotalStudentsEnrolledOnGroups,
                    'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                    'updated_at' => Carbon::now('GMT-5')->toDateTimeString() ]);

        }

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $teachers = DB::table('group_results')->select(['teacher_id'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();

        $uniqueTeachers = array_column($teachers, 'teacher_id');

        $uniqueTeachersId = array_unique($uniqueTeachers);


        foreach ($uniqueTeachersId as $uniqueTeacherId){

            $serviceAreaCodesFromTeacher = DB::table('group_results')->select(['service_area_code'])->where('teacher_id', '=', $uniqueTeacherId)->get()->toArray();

            $uniqueServiceAreaCodes = array_column($serviceAreaCodesFromTeacher, 'service_area_code');

            $uniqueServiceAreaCodes = array_unique($uniqueServiceAreaCodes);


            foreach ($uniqueServiceAreaCodes as $uniqueServiceAreaCode){

                $groupsFromServiceAreaCode = DB::table('group_results')->where('service_area_code', '=', $uniqueServiceAreaCode)
                    ->where('teacher_id', '=', $uniqueTeacherId)->get();

                $groupsAmountFromServiceAreaCode = count($groupsFromServiceAreaCode);

                $aggregateTotalStudentsReviewersOnServiceArea = 0;
                $aggregateTotalStudentsEnrolledOnServiceArea = 0;


                $final_first_aggregate_competence_average = 0;
                $final_second_aggregate_competence_average = 0;
                $final_third_aggregate_competence_average = 0;
                $final_fourth_aggregate_competence_average = 0;
                $final_fifth_aggregate_competence_average = 0;
                $final_sixth_aggregate_competence_average = 0;


                foreach ($groupsFromServiceAreaCode as $key=>$groupFromServiceAreaCode){

                    $aggregateTotalStudentsReviewersOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_reviewers;
                    $aggregateTotalStudentsEnrolledOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_on_group;

                    $final_first_aggregate_competence_average += $groupFromServiceAreaCode->first_final_competence_average;
                    $final_second_aggregate_competence_average += $groupFromServiceAreaCode->second_final_competence_average;
                    $final_third_aggregate_competence_average += $groupFromServiceAreaCode->third_final_competence_average;
                    $final_fourth_aggregate_competence_average +=$groupFromServiceAreaCode->fourth_final_competence_average;
                    $final_fifth_aggregate_competence_average += $groupFromServiceAreaCode->fifth_final_competence_average;
                    $final_sixth_aggregate_competence_average += $groupFromServiceAreaCode->sixth_final_competence_average;

                }


                $final_first_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
                $final_second_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
                $final_third_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
                $final_fourth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
                $final_fifth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
                $final_sixth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;

                $final_first_aggregate_competence_average = number_format($final_first_aggregate_competence_average, 1);
                $final_second_aggregate_competence_average = number_format($final_second_aggregate_competence_average, 1);
                $final_third_aggregate_competence_average = number_format($final_third_aggregate_competence_average, 1);
                $final_fourth_aggregate_competence_average = number_format($final_fourth_aggregate_competence_average, 1);
                $final_fifth_aggregate_competence_average = number_format($final_fifth_aggregate_competence_average, 1);
                $final_sixth_aggregate_competence_average = number_format($final_sixth_aggregate_competence_average, 1);


                DB::table('teachers_service_areas_results')->updateOrInsert(['teacher_id' => $uniqueTeacherId, 'service_area_code' =>$uniqueServiceAreaCode,'assessment_period_id' => $activeAssessmentPeriodId],
                    ['first_final_aggregate_competence_average' => $final_first_aggregate_competence_average,
                        'second_final_aggregate_competence_average' => $final_second_aggregate_competence_average,
                        'third_final_aggregate_competence_average' => $final_third_aggregate_competence_average,
                        'fourth_final_aggregate_competence_average' => $final_fourth_aggregate_competence_average,
                        'fifth_final_aggregate_competence_average' => $final_fifth_aggregate_competence_average,
                        'sixth_final_aggregate_competence_average' => $final_sixth_aggregate_competence_average,
                        'aggregate_students_amount_reviewers' => $aggregateTotalStudentsReviewersOnServiceArea,
                        'aggregate_students_amount_on_service_area' => $aggregateTotalStudentsEnrolledOnServiceArea,
                        'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                        'updated_at' => Carbon::now('GMT-5')->toDateTimeString() ]);


            }

        }

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $teacherRoleId = Role::getTeacherRoleId();

        $teachersFrom360 = DB::table('teachers_students_perspectives as tsp')->select(['teacher_id'])
            ->join('v2_unit_user','tsp.teacher_id', '=', 'v2_unit_user.user_id')
            ->where('v2_unit_user.role_id', '=', $teacherRoleId)->get()->toArray();


        $uniqueTeachers = array_column($teachersFrom360, 'teacher_id');

        $uniqueTeachersId = array_unique($uniqueTeachers);

        foreach ($uniqueTeachersId as $uniqueTeacherId){

            $allAssessments = [];

            $peerPercentage = 0.15;
            $autoPercentage = 0.15;
            $bossPercentage = 0.35;
            $studentsPercentage = 0.35;

            $firstCompetenceTotal= 0;
            $secondCompetenceTotal= 0;
            $thirdCompetenceTotal= 0;
            $fourthCompetenceTotal= 0;
            $fifthCompetenceTotal= 0;
            $sixthCompetenceTotal= 0;


            $peerBossAutoAssessmentAnswers = DB::table('form_answers as fa')
                ->select(['t.name', 'f.unit_role', 'fa.first_competence_average','fa.second_competence_average','fa.third_competence_average',
                    'fa.fourth_competence_average','fa.fifth_competence_average','fa.sixth_competence_average','t.id as teacherId', 'v2_unit_user.unit_identifier',
                    'v2_units.name as unitName','fa.submitted_at'])
                ->join('forms as f', 'fa.form_id', '=', 'f.id')
                ->join('users as t', 'fa.teacher_id', '=', 't.id')
                ->join('teachers_students_perspectives as tsp', 'tsp.teacher_id','=','t.id')
                ->join('v2_unit_user','tsp.teacher_id', '=', 'v2_unit_user.user_id')
                ->join('v2_units', 'v2_unit_user.unit_identifier','=', 'v2_units.identifier')
                ->where('f.type','=','otros')
                ->where('v2_unit_user.role_id', '=', $teacherRoleId)
                ->where('tsp.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('t.id', '=', $uniqueTeacherId)
                ->get();


            /*        if($uniqueTeacherId == 92){

                        dd(count($peerBossAutoAssessmentAnswers));
                    }*/

            if(count($peerBossAutoAssessmentAnswers) == 0){

                continue;

            }


            $studentsAnswers = DB::table('teachers_students_perspectives as tsp')
                ->select(['tsp.first_final_aggregate_competence_average as first_competence_average',
                    'tsp.second_final_aggregate_competence_average as second_competence_average',
                    'tsp.third_final_aggregate_competence_average as third_competence_average',
                    'tsp.fourth_final_aggregate_competence_average as fourth_competence_average',
                    'tsp.fifth_final_aggregate_competence_average as fifth_competence_average',
                    'tsp.sixth_final_aggregate_competence_average as sixth_competence_average'])
                ->where('teacher_id', '=', $uniqueTeacherId)->get()->first();

            $studentsAnswers->unit_role = "estudiante";

            $peerBossAutoAssessmentAnswers [] = $studentsAnswers;

            $allAssessments = $peerBossAutoAssessmentAnswers;

            foreach ($allAssessments as $assessment){

                if($assessment->unit_role === "par"){
                    $firstCompetenceTotal += $assessment->first_competence_average*$peerPercentage;
                    $secondCompetenceTotal += $assessment->second_competence_average*$peerPercentage;
                    $thirdCompetenceTotal += $assessment->third_competence_average*$peerPercentage;
                    $fourthCompetenceTotal += $assessment->fourth_competence_average*$peerPercentage;
                    $fifthCompetenceTotal += $assessment->fifth_competence_average*$peerPercentage;
                    $sixthCompetenceTotal += $assessment->sixth_competence_average*$peerPercentage;
                }


                if($assessment->unit_role === "jefe"){
                    $firstCompetenceTotal += $assessment->first_competence_average*$bossPercentage;
                    $secondCompetenceTotal += $assessment->second_competence_average*$bossPercentage;
                    $thirdCompetenceTotal += $assessment->third_competence_average*$bossPercentage;
                    $fourthCompetenceTotal += $assessment->fourth_competence_average*$bossPercentage;
                    $fifthCompetenceTotal += $assessment->fifth_competence_average*$bossPercentage;
                    $sixthCompetenceTotal += $assessment->sixth_competence_average*$bossPercentage;
                }

                if($assessment->unit_role === "estudiante"){

                    $firstCompetenceTotal += $assessment->first_competence_average*$studentsPercentage;
                    $secondCompetenceTotal += $assessment->second_competence_average*$studentsPercentage;
                    $thirdCompetenceTotal += $assessment->third_competence_average*$studentsPercentage;
                    $fourthCompetenceTotal += $assessment->fourth_competence_average*$studentsPercentage;
                    $fifthCompetenceTotal += $assessment->fifth_competence_average*$studentsPercentage;
                    $sixthCompetenceTotal += $assessment->sixth_competence_average*$studentsPercentage;
                }

                if($assessment->unit_role === "autoevaluación"){

                    $firstCompetenceTotal += $assessment->first_competence_average*$autoPercentage;
                    $secondCompetenceTotal += $assessment->second_competence_average*$autoPercentage;
                    $thirdCompetenceTotal += $assessment->third_competence_average*$autoPercentage;
                    $fourthCompetenceTotal += $assessment->fourth_competence_average*$autoPercentage;
                    $fifthCompetenceTotal += $assessment->fifth_competence_average*$autoPercentage;
                    $sixthCompetenceTotal += $assessment->sixth_competence_average*$autoPercentage;
                }

            }


            /* if($uniqueTeacherId == 226){
                 dd($firstCompetenceTotal,$secondCompetenceTotal,$thirdCompetenceTotal,$fourthCompetenceTotal,$fifthCompetenceTotal,$sixthCompetenceTotal);

             }*/



            $firstCompetenceTotal = number_format($firstCompetenceTotal, 1);
            $secondCompetenceTotal = number_format($secondCompetenceTotal, 1);
            $thirdCompetenceTotal = number_format($thirdCompetenceTotal, 1);
            $fourthCompetenceTotal = number_format($fourthCompetenceTotal, 1);
            $fifthCompetenceTotal = number_format($fifthCompetenceTotal, 1);
            $sixthCompetenceTotal = number_format($sixthCompetenceTotal, 1);


            DB::table('teachers_360_final_average')->updateOrInsert(['teacher_id' => $uniqueTeacherId,
                'assessment_period_id' => $activeAssessmentPeriodId], ['first_final_aggregate_competence_average' => $firstCompetenceTotal,
                'second_final_aggregate_competence_average' => $secondCompetenceTotal,
                'third_final_aggregate_competence_average' => $thirdCompetenceTotal,
                'fourth_final_aggregate_competence_average' => $fourthCompetenceTotal,
                'fifth_final_aggregate_competence_average' => $fifthCompetenceTotal,
                'sixth_final_aggregate_competence_average' => $sixthCompetenceTotal]);

            /*       dd($firstCompetenceTotal,$secondCompetenceTotal,$thirdCompetenceTotal,$fourthCompetenceTotal,$fifthCompetenceTotal,$sixthCompetenceTotal);*/


            /*        dd($allAssessments);*/
        }


        return 0;

    }
}
