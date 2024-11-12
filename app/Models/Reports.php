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

    public static function mapGroupsResultsToFrontEndStructure($groupsResults, $areLegacyResults)
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

            if($result->competences_average != null ){
                foreach ($result->competences_average as $competence) {

                    if(!$areLegacyResults){
                        foreach ($competence->attributes as $attribute) {
                            $attributeName = $attribute->name;
                            if (!in_array($attributeName, $attributeNames)) {
                                $attributeNames[] = $attributeName;
                                $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                            }
                            $item[$attributeName] = $attribute->overall_average;
                        }
                    }

                    else{
                        $attributeName = $competence->name;
                        if (!in_array($attributeName, $attributeNames)) {
                            $attributeNames[] = $attributeName;
                            $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                        }
                        $item[$attributeName] = $competence->overall_average;
                    }
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


    public static function mapServiceAreasResultsToFrontEndStructure($serviceAreasResults, $areLegacyResults)
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

            if($result->competences_average != null ){
                foreach ($result->competences_average as $competence) {

                    if(!$areLegacyResults){
                        foreach ($competence->attributes as $attribute) {
                            $attributeName = $attribute->name;
                            if (!in_array($attributeName, $attributeNames)) {
                                $attributeNames[] = $attributeName;
                                $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                            }
                            $item[$attributeName] = $attribute->overall_average;
                        }
                    }

                    else{
                        $attributeName = $competence->name;
                        if (!in_array($attributeName, $attributeNames)) {
                            $attributeNames[] = $attributeName;
                            $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                        }
                        $item[$attributeName] = $competence->overall_average;
                    }
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

    public static function updateGroupResults($academicPeriod){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
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


    public static function updateTeacherServiceAreaResults(){

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $teacherIds = DB::table('group_results as gr')
            ->select(['gr.teacher_id'])
            ->where('gr.assessment_period_id', '=', 6)
            ->orderBy('id','desc')
            ->pluck('gr.teacher_id')
            ->unique();

        foreach ($teacherIds as $teacherId) {
            $serviceAreasIds = DB::table('group_results as gr')
                ->select(['gr.service_area_code'])
                ->where('teacher_id', '=', $teacherId)
                ->where('gr.assessment_period_id', '=', 6)
                ->pluck('gr.service_area_code')
                ->unique();

            foreach ($serviceAreasIds as $serviceAreaId) {
                $hourTypes = ['normal', 'cátedra'];
                $results = [];

                foreach ($hourTypes as $hourType) {
                    $studentsWithAnswer = 0;
                    $studentsEnrolled = 0;
                    $openEndedAnswers = [];
                    $competencesData = [];
                    $overallAverage = 0;
                    $groupCount = 0;

                    $groups = DB::table('group_results as gr')
                        ->where('gr.teacher_id', '=', $teacherId)
                        ->where('gr.service_area_code', '=', $serviceAreaId)
                        ->where('gr.assessment_period_id', '=', 6)
                        ->where('gr.hour_type', '=', $hourType)
                        ->join('groups as g', 'g.group_id', '=', 'gr.group_id')
                        ->get();

                    foreach ($groups as $group) {
                        $groupCount++;
                        $competencesAverage = json_decode($group->competences_average, true);
                        $overallAverage += $group->overall_average;

                        foreach ($competencesAverage as $competence) {
                            $competenceId = $competence['id'];
                            if (!isset($competencesData[$competenceId])) {
                                $competencesData[$competenceId] = [
                                    'id' => $competenceId,
                                    'name' => $competence['name'],
                                    'attributes' => [],
                                    'overall_sum' => 0,
                                    'overall_count' => 0,
                                ];
                            }

                            $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                            $competencesData[$competenceId]['overall_count']++;

                            foreach ($competence['attributes'] as $attributeValue) {
                                $attributeName = $attributeValue['name'];
                                if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                    $competencesData[$competenceId]['attributes'][$attributeName] = [
                                        'name' => $attributeName,
                                        'sum' => 0,
                                        'count' => 0,
                                    ];
                                }
                                if (isset($attributeValue['overall_average'])) {
                                    $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attributeValue['overall_average'];
                                    $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                                }
                            }
                        }

                        $studentsWithAnswer += $group->students_amount_reviewers;
                        $studentsEnrolled += $group->students_amount_on_group;
                        $openEndedAnswers[] = [
                            'group_name' => $group->name . " | Grupo: " . $group->group,
                            'questions' => json_decode($group->open_ended_answers, true),
                        ];
                    }

                    if ($groupCount > 0) {
                        $finalCompetencesData = [];
                        $overallAverage = $overallAverage / $groupCount;

                        foreach ($competencesData as $competence) {
                            $finalCompetence = [
                                'id' => $competence['id'],
                                'name' => $competence['name'],
                                'attributes' => [],
                                'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                            ];

                            foreach ($competence['attributes'] as $attributeName => $attributeData) {
                                if ($attributeData['count'] > 0) {
                                    $finalCompetence['attributes'][] = [
                                        'name' => $attributeName,
                                        'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                                    ];
                                }
                            }
                            $finalCompetencesData[] = $finalCompetence;
                        }

                        $results[$hourType] = [
                            'competences_average' => $finalCompetencesData,
                            'open_ended_answers' => $openEndedAnswers,
                            'overall_average' => round($overallAverage, 2),
                            'aggregate_students_amount_reviewers' => $studentsWithAnswer,
                            'aggregate_students_amount_on_service_area' => $studentsEnrolled,
                        ];
                    }
                }

                // Calculate total (average of normal and cátedra)
                if (!empty($results)) {
                    $totalResult = [
                        'competences_average' => [],
                        'open_ended_answers' => [],
                        'overall_average' => 0,
                        'aggregate_students_amount_reviewers' => 0,
                        'aggregate_students_amount_on_service_area' => 0,
                    ];

                    $hourTypeCount = count($results);
                    foreach ($results as $hourTypeResult) {
                        $totalResult['overall_average'] += $hourTypeResult['overall_average'];
                        $totalResult['aggregate_students_amount_reviewers'] += $hourTypeResult['aggregate_students_amount_reviewers'];
                        $totalResult['aggregate_students_amount_on_service_area'] += $hourTypeResult['aggregate_students_amount_on_service_area'];
                        $totalResult['open_ended_answers'] = array_merge($totalResult['open_ended_answers'], $hourTypeResult['open_ended_answers']);

                        foreach ($hourTypeResult['competences_average'] as $competence) {
                            $competenceId = $competence['id'];
                            if (!isset($totalResult['competences_average'][$competenceId])) {
                                $totalResult['competences_average'][$competenceId] = [
                                    'id' => $competenceId,
                                    'name' => $competence['name'],
                                    'attributes' => [],
                                    'overall_average' => 0,
                                ];
                            }
                            $totalResult['competences_average'][$competenceId]['overall_average'] += $competence['overall_average'];

                            foreach ($competence['attributes'] as $attribute) {
                                $attributeName = $attribute['name'];
                                if (!isset($totalResult['competences_average'][$competenceId]['attributes'][$attributeName])) {
                                    $totalResult['competences_average'][$competenceId]['attributes'][$attributeName] = [
                                        'name' => $attributeName,
                                        'overall_average' => 0,
                                    ];
                                }
                                $totalResult['competences_average'][$competenceId]['attributes'][$attributeName]['overall_average'] += $attribute['overall_average'];
                            }
                        }
                    }

                    // Calculate averages for total
                    $totalResult['overall_average'] = round($totalResult['overall_average'] / $hourTypeCount, 2);
                    foreach ($totalResult['competences_average'] as &$competence) {
                        $competence['overall_average'] = round($competence['overall_average'] / $hourTypeCount, 2);
                        foreach ($competence['attributes'] as &$attribute) {
                            $attribute['overall_average'] = round($attribute['overall_average'] / $hourTypeCount, 2);
                        }
                        $competence['attributes'] = array_values($competence['attributes']);
                    }
                    $totalResult['competences_average'] = array_values($totalResult['competences_average']);

                    $results['total'] = $totalResult;
                }

                // Upsert results for each hour type and total
                foreach ($results as $hourType => $result) {
                    DB::table('teachers_service_areas_results')->updateOrInsert(
                        [
                            'teacher_id' => $teacherId,
                            'service_area_code' => $serviceAreaId,
                            'assessment_period_id' => $activeAssessmentPeriodId,
                            'hour_type' => $hourType
                        ],
                        [
                            'competences_average' => json_encode($result['competences_average'], JSON_UNESCAPED_UNICODE),
                            'open_ended_answers' => json_encode($result['open_ended_answers'], JSON_UNESCAPED_UNICODE),
                            'overall_average' => $result['overall_average'],
                            'aggregate_students_amount_reviewers' => $result['aggregate_students_amount_reviewers'],
                            'aggregate_students_amount_on_service_area' => $result['aggregate_students_amount_on_service_area'],
                            'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                            'updated_at' => Carbon::now('GMT-5')->toDateTimeString()
                        ]
                    );
                }
            }
        }
    }


    public static function updateTeacherStudentPerspectiveResultsTable(){

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $teacherIds = DB::table('teachers_service_areas_results as tsar')
            ->select(['tsar.teacher_id'])
            ->where('tsar.assessment_period_id', '=', 6)
            ->pluck('tsar.teacher_id')
            ->unique();

        $hourTypes = ['normal', 'cátedra', 'total'];

        foreach ($teacherIds as $teacherId) {
            foreach ($hourTypes as $hourType) {
                $studentsWithAnswer = 0;
                $studentsEnrolled = 0;
                $competencesData = [];
                $overallAverage = 0;
                $openEndedAnswers = [];
                $groupsAmount = 0;

                // Get all the results from the teacher for the current hour type
                $serviceAreas = DB::table('teachers_service_areas_results as tsar')
                    ->where('tsar.teacher_id', '=', $teacherId)
                    ->where('tsar.assessment_period_id', '=', 6)
                    ->where('tsar.hour_type', '=', $hourType)
                    ->where('sa.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->join('service_areas as sa', 'sa.code', '=', 'tsar.service_area_code')
                    ->get();

                $serviceAreaCount = $serviceAreas->count();

                foreach ($serviceAreas as $serviceArea) {
                    $openEndedAnswers[] = [
                        'service_area_name' => $serviceArea->name,
                        'groups' => json_decode($serviceArea->open_ended_answers, true)
                    ];

                    $competencesAverage = json_decode($serviceArea->competences_average, true);
                    $overallAverage += $serviceArea->overall_average;
                    $studentsWithAnswer += $serviceArea->aggregate_students_amount_reviewers;
                    $studentsEnrolled += $serviceArea->aggregate_students_amount_on_service_area;

                    // Count the number of groups
                    $groupsAmount += count(json_decode($serviceArea->open_ended_answers, true));

                    foreach ($competencesAverage as $competence) {
                        $competenceId = $competence['id'];
                        if (!isset($competencesData[$competenceId])) {
                            $competencesData[$competenceId] = [
                                'id' => $competenceId,
                                'name' => $competence['name'],
                                'attributes' => [],
                                'overall_sum' => 0,
                                'overall_count' => 0,
                            ];
                        }

                        $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                        $competencesData[$competenceId]['overall_count']++;

                        foreach ($competence['attributes'] as $attributeValue) {
                            $attributeName = $attributeValue['name'];
                            if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                $competencesData[$competenceId]['attributes'][$attributeName] = [
                                    'name' => $attributeName,
                                    'sum' => 0,
                                    'count' => 0,
                                ];
                            }
                            if (isset($attributeValue['overall_average'])) {
                                $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attributeValue['overall_average'];
                                $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                            }
                        }
                    }
                }

                // Calculate final averages
                $finalCompetencesData = [];
                $overallAverage = $serviceAreaCount > 0 ? $overallAverage / $serviceAreaCount : 0;

                foreach ($competencesData as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'attributes' => [],
                        'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                    ];

                    foreach ($competence['attributes'] as $attributeName => $attributeData) {
                        if ($attributeData['count'] > 0) {
                            $finalCompetence['attributes'][] = [
                                'name' => $attributeName,
                                'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                            ];
                        }
                    }
                    $finalCompetencesData[] = $finalCompetence;
                }

                // Only insert if there's data for this hour type
                if ($serviceAreaCount > 0) {
                    DB::table('teachers_students_perspectives')->updateOrInsert(
                        [
                            'teacher_id' => $teacherId,
                            'assessment_period_id' => $activeAssessmentPeriodId,
                            'hour_type' => $hourType
                        ],
                        [
                            'groups_amount' => $groupsAmount,
                            'competences_average' => json_encode($finalCompetencesData, JSON_UNESCAPED_UNICODE),
                            'overall_average' => round($overallAverage, 2),
                            'open_ended_answers' => json_encode($openEndedAnswers, JSON_UNESCAPED_UNICODE),
                            'aggregate_students_amount_reviewers' => $studentsWithAnswer,
                            'aggregate_students_amount_on_360_groups' => $studentsEnrolled,
                            'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                            'updated_at' => Carbon::now('GMT-5')->toDateTimeString()
                        ]
                    );
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
