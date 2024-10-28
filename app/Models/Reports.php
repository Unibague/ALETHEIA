<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class Reports extends Model
{
    use HasFactory;

    public static function mapGroupsResultsToFrontEndStructure($groupsResults)
    {

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
                ['text' => 'Área de servicio', 'value' => 'service_area_name'],
                ['text' => 'Asignatura', 'value' => 'group_name'],
                ['text' => 'Número de Grupo', 'value' => 'group_number'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($groupsResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'service_area_name' => $result->service_area_name,
                'service_area_code' => $result->service_area_code,
                'assessment_period_id' => $result->assessment_period_id,
                'group_name' => $result->group_name,
                'group_number' => $result->group_number,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];


        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }


    public static function mapServiceAreasResultsToFrontEndStructure($serviceAreasResults)
    {

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
                ['text' => 'Área de servicio', 'value' => 'service_area_name'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($serviceAreasResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'assessment_period_id' => $result->assessment_period_id,
                'service_area_name' => $result->service_area_name,
                'service_area_code' => $result->service_area_code,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];

        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }


    public static function mapFinalTeachingResultsToFrontEndStructure($finalTeachingResults)
    {

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($finalTeachingResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'assessment_period_id' => $result->assessment_period_id,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];

        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }


    public static function getEvaluatedTeachersFromAcademicPeriod($academicPeriodId, $assessmentPeriodId): \Illuminate\Support\Collection
    {
        return DB::table('form_answers as fa')->select(['fa.teacher_id'])
            ->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->where('f.type', '=', 'estudiantes')
            ->where('fa.assessment_period_id', '=', $assessmentPeriodId)
            ->join('academic_periods as ap', 'fa.assessment_period_id', '=', 'ap.assessment_period_id')
            ->where('ap.id', '=', $academicPeriodId)
            ->pluck('fa.teacher_id')->unique();
    }

    public static function updateGroupResults(){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        foreach ($academicPeriods as $academicPeriod) {
            if (!AcademicPeriod::isStudentsAssessmentDateFinished($academicPeriod)) {
                continue;
            }

            //Get the ID's from the teachers that had answers on the current academic period
            $teacherIds = self::getEvaluatedTeachersFromAcademicPeriod($academicPeriod->id, $activeAssessmentPeriodId);

            if (count($teacherIds) > 0) {
                foreach ($teacherIds as $teacherId) {
                    $groups = DB::table('groups as g')->where('g.teacher_id', '=', $teacherId)
                        ->join('academic_periods as ap', 'g.academic_period_id', '=', 'ap.id')
                        ->where('ap.assessment_period_id','=',6)->get();

                    //Now that we have the groups info for the teacher, we can proceed and do the calculations
                    foreach ($groups as $group) {

                        $answersFromGroup = self::getGroupAnswers($group);
                        $studentsWithAnswer = count($answersFromGroup);
                        $studentsEnrolled = DB::table('group_user')
                            ->where('group_id', '=', $group->group_id)
                            ->count();

                        if ($answersFromGroup->isEmpty() || $group->assessment_period_id == null) {
                            continue;
                        }

                        $openEndedAnswers = [];
                        $competencesAverage = [];
                        $competencesData = [];

                        foreach ($answersFromGroup as $answerFromGroup) {

                            $userOpenEndedAnswers = json_decode($answerFromGroup->open_ended_answers, JSON_THROW_ON_ERROR);
                            $openEndedAnswers [] = $userOpenEndedAnswers;
                            $answerCompetences = json_decode($answerFromGroup->competences_average);

                            foreach ($answerCompetences as $competence) {

                                $competenceKey = $competence->name;
                                if (!isset($competencesData[$competence->name])) {
                                    $competencesData[$competence->name] = [
                                        'id' => $competence->id,
                                        'totalScore' => 0,
                                        'totalAnswers' => 0,
                                        'attributes' => []
                                    ];
                                }

                                $score = floatval($competence->average);
                                $competencesData[$competenceKey]['totalScore'] += $score;
                                $competencesData[$competenceKey]['totalAnswers'] ++;

                                if (isset($competence->attributes) && is_array($competence->attributes)) {
                                    foreach ($competence->attributes as $attribute) {
                                        $attributeKey = $attribute->name;
                                        if (!isset($competencesData[$competenceKey]['attributes'][$attributeKey])) {
                                            $competencesData[$competenceKey]['attributes'][$attributeKey] = [
                                                'totalScore' => 0,
                                                'totalAnswers' => 0
                                            ];
                                        }
                                        $attributeScore = floatval($attribute->average);
                                        $competencesData[$competenceKey]['attributes'][$attributeKey]['totalScore'] += $attributeScore;
                                        $competencesData[$competenceKey]['attributes'][$attributeKey]['totalAnswers']++;
                                    }
                                }
                            }


                        }

                        $groupedOpenEndedAnswers = \App\Models\FormAnswers::groupOpenEndedAnswers($openEndedAnswers);

                        $overallAverage = 0;
                        $competencesPresent = 0;
                        foreach ($competencesData as $competenceName => $competence) {

                            $attributesAverage = [];
                            if ($competence['totalAnswers'] > 0) {
                                $competenceAverage = round($competence['totalScore'] / ($competence['totalAnswers']), 2);

                                foreach ($competence['attributes'] as $attributeName => $attribute) {
                                    if ($attribute['totalAnswers'] > 0) {
                                        $attributeAverage = round($attribute['totalScore'] / $attribute['totalAnswers'], 2);
                                        $attributesAverage [] = [
                                            'name' => $attributeName,
                                            'overall_average' => $attributeAverage
                                        ];
                                    }
                                }

                                $competencesAverage[] = [
                                    'id' => $competence['id'],
                                    'name' => $competenceName,
                                    'overall_average' => $competenceAverage,
                                    'attributes' => $attributesAverage
                                ];

                                if($competenceName !== 'Satisfacción'){
                                    $overallAverage += $competenceAverage;
                                    $competencesPresent++;
                                }
                            }
                        }

                        // Calculate the final overall average
                        if ($competencesPresent > 0) {
                            $overallAverage /= $competencesPresent;
                        }

                        DB::table('group_results')->updateOrInsert
                        (
                            [
                                'teacher_id' => $teacherId,
                                'group_id' => $group->group_id,
                                'assessment_period_id' => $group->assessment_period_id
                            ],
                            [
                                'hour_type' => $group->hour_type,
                                'service_area_code' => $group->service_area_code,
                                'students_amount_reviewers' => $studentsWithAnswer,
                                'students_amount_on_group' => $studentsEnrolled,
                                'competences_average' => json_encode($competencesAverage, JSON_UNESCAPED_UNICODE),
                                'overall_average' => round($overallAverage, 2),
                                'open_ended_answers' => json_encode($groupedOpenEndedAnswers, JSON_UNESCAPED_UNICODE),
                                'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                                'updated_at' => Carbon::now('GMT-5')->toDateTimeString()
                            ]
                        );
                    }
                }
            }
        }
    }


    public static function getGroupAnswers($group): \Illuminate\Support\Collection
    {
        return DB::table('form_answers as fa')
            ->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->join('groups', 'fa.group_id', '=', 'groups.group_id')
            ->where('f.type', '=', 'estudiantes')
            ->where('fa.group_id', '=', $group->group_id)->get();
    }




}
