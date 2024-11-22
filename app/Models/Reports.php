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

    public static function getGroupResultsByTeacher($assessmentPeriodId, $teacherId){

        $areLegacyResults = false;

        $groupResults = DB::table('group_results as gr')
            ->select(['u.name as teacher_name', 'u.id as teacher_id',
                'gr.assessment_period_id', 'gr.hour_type',
                'g.name as group_name', 'g.group as group_number', 'sa.name as service_area_name', 'sa.code as service_area_code',
                'gr.competences_average', 'gr.open_ended_answers',
                'gr.overall_average', 'gr.students_amount_reviewers as reviewers', 'gr.students_amount_on_group as total_students'])
            ->join('users as u', 'gr.teacher_id', '=', 'u.id')
            ->join('groups as g', 'gr.group_id', '=', 'g.group_id')
            ->join('service_areas as sa', 'gr.service_area_code', '=', 'sa.code')
            ->where('gr.assessment_period_id', '=', $assessmentPeriodId)
            ->where('gr.teacher_id', '=', $teacherId)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->get();

        // Manually decode the JSON columns
        $groupResults = $groupResults->map(function ($result) {
            $result->competences_average = json_decode($result->competences_average);
            $result->open_ended_answers = json_decode($result->open_ended_answers);
            return $result;
        });

        if($assessmentPeriodId < 6){
            $areLegacyResults = true;
        }

        return Reports::mapGroupsResultsToFrontEndStructure($groupResults, $areLegacyResults);

    }


    public static function mapGroupsResultsToFrontEndStructure($groupsResults, $areLegacyResults)
    {

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
                ['text' => 'Área de servicio', 'value' => 'service_area_name'],
                ['text' => 'Asignatura', 'value' => 'group_name'],
                ['text' => '# Grupo', 'value' => 'group_number'],
                ['text' => 'Tipo hora', 'value' => 'hour_type']
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
                'hour_type' => $result->hour_type,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            if ($result->competences_average != null) {
                foreach ($result->competences_average as $competence) {

                    if (!$areLegacyResults) {
                        foreach ($competence->attributes as $attribute) {
                            $attributeName = $attribute->name;
                            if (!in_array($attributeName, $attributeNames)) {
                                $attributeNames[] = $attributeName;
                                $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                            }
                            $item[$attributeName] = $attribute->overall_average;
                        }
                    } else {
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
                ['text' => 'Tipo hora', 'value' => 'hour_type']
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
                'hour_type' => $result->hour_type,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            if ($result->competences_average != null) {
                foreach ($result->competences_average as $competence) {

                    if (!$areLegacyResults) {
                        foreach ($competence->attributes as $attribute) {
                            $attributeName = $attribute->name;
                            if (!in_array($attributeName, $attributeNames)) {
                                $attributeNames[] = $attributeName;
                                $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                            }
                            $item[$attributeName] = $attribute->overall_average;
                        }
                    } else {
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
                ['text' => 'Tipo hora', 'value' => 'hour_type']
            ];

        $attributeNames = [];
        $items = [];

        foreach ($finalTeachingResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'hour_type' => $result->hour_type,
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

    public static function mapFacultiesResultsToFrontEndStructure($facultiesResults)
    {

        $headers = [
            ['text' => 'Facultad', 'value' => 'faculty_name'],
            ['text' => 'Tipo hora', 'value' => 'hour_type']
        ];
        $attributeNames = [];
        $items = [];

        foreach ($facultiesResults as $result) {
            $item = [
                'faculty_name' => $result->faculty_name,
                'hour_type'=> $result->hour_type,
                'faculty_id' => $result->faculty_id,
                'assessment_period_id' => $result->assessment_period_id,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
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

    public static function updateGroupResults($academicPeriod)
    {

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        //Get the ID's from the teachers that had answers on the current academic period
        $teacherIds = self::getEvaluatedTeachersFromAcademicPeriod($academicPeriod->id, $activeAssessmentPeriodId);

        if (count($teacherIds) > 0) {
            foreach ($teacherIds as $teacherId) {
                $groups = DB::table('groups as g')->where('g.teacher_id', '=', $teacherId)
                    ->join('academic_periods as ap', 'g.academic_period_id', '=', 'ap.id')
                    ->where('g.academic_period_id','=',$academicPeriod->id)
                    ->where('ap.assessment_period_id', '=', $activeAssessmentPeriodId)->get();

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
                            $competencesData[$competenceKey]['totalAnswers']++;

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

                            if ($competenceName !== 'Satisfacción') {
                                $overallAverage += $competenceAverage;
                                $competencesPresent++;
                            }
                        }
                    }

                    // Calculate the final overall average
                    if ($competencesPresent > 0) {
                        $overallAverage /= $competencesPresent;
                    }

                    $teacherProfile = DB::table('v2_teacher_profiles')->where('user_id','=',$teacherId)->where('assessment_period_id','=',$group->assessment_period_id)->first();

                    if(!$teacherProfile){
                        continue;
                    }

                    $hourType = 'normal';

                    if($teacherProfile->employee_type !== 'DTC'){
                        $hourType = 'cátedra';
                    }

                    DB::table('group_results')->updateOrInsert
                    (
                        [
                            'teacher_id' => $teacherId,
                            'group_id' => $group->group_id,
                            'assessment_period_id' => $group->assessment_period_id
                        ],
                        [
                            'hour_type' => $hourType,
                            'service_area_code' => $group->service_area_code,
                            'students_amount_reviewers' => $studentsWithAnswer,
                            'students_amount_on_group' => $studentsEnrolled,
                            'competences_average' => json_encode($competencesAverage, JSON_UNESCAPED_UNICODE),
                            'overall_average' => round($overallAverage, 2),
                            'open_ended_answers' => json_encode($groupedOpenEndedAnswers, JSON_UNESCAPED_UNICODE),
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );
                }
            }
        }

    }


    public static function updateTeacherServiceAreaResults()
    {

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $teacherIds = DB::table('group_results as gr')
            ->select(['gr.teacher_id'])
            ->where('gr.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->orderBy('id', 'desc')
            ->pluck('gr.teacher_id')
            ->unique();

        foreach ($teacherIds as $teacherId) {
            $serviceAreasIds = DB::table('group_results as gr')
                ->select(['gr.service_area_code'])
                ->where('teacher_id', '=', $teacherId)
                ->where('gr.assessment_period_id', '=', $activeAssessmentPeriodId)
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
                        ->where('gr.assessment_period_id', '=', $activeAssessmentPeriodId)
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
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );
                }
            }
        }
    }


    public static function updateTeacherStudentPerspectiveResultsTable()
    {

        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $teacherIds = DB::table('teachers_service_areas_results as tsar')
            ->select(['tsar.teacher_id'])
            ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->pluck('tsar.teacher_id')
            ->unique();

        $hourTypes = ['normal', 'cátedra'];

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
                    ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
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
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );
                }
            }
        }
    }


    public static function updateServiceAreaResults()
    {
        $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;

        // Retrieve unique service area codes for the active assessment period
        $serviceAreaCodes = DB::table('teachers_service_areas_results as tsar')
            ->select('tsar.service_area_code')
            ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->pluck('tsar.service_area_code')
            ->unique();

        // Define hour types to loop through
        $hourTypes = ['normal', 'cátedra'];

        // Loop through each service area code
        foreach ($serviceAreaCodes as $serviceAreaCode) {
            $totalData = [
                'studentsWithAnswer' => 0,
                'studentsEnrolled' => 0,
                'overallAverage' => 0,
                'competencesData' => [],
                'openEndedAnswers' => [],
                'resultsCount' => 0
            ];

            // Process each hour type
            foreach ($hourTypes as $hourType) {
                $studentsWithAnswer = 0;
                $studentsEnrolled = 0;
                $competencesData = [];
                $overallAverage = 0;
                $openEndedAnswers = [];

                // Get all results for the current service area code and hour type
                $results = DB::table('teachers_service_areas_results as tsar')
                    ->where('tsar.service_area_code', '=', $serviceAreaCode)
                    ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('tsar.hour_type', '=', $hourType)
                    ->join('users', 'tsar.teacher_id', '=', 'users.id')
                    ->get();

                $resultsCount = $results->count();

                foreach ($results as $result) {
                    $openEndedAnswers[] = [
                        'answers' => json_decode($result->open_ended_answers, true),
                        'teacher_name' => $result->name,
                    ];

                    $competencesAverage = json_decode($result->competences_average, true);
                    $overallAverage += $result->overall_average;
                    $studentsWithAnswer += $result->aggregate_students_amount_reviewers;
                    $studentsEnrolled += $result->aggregate_students_amount_on_service_area;

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

                // Calculate the final averages for competences
                $finalCompetencesData = [];
                $overallAverage = $resultsCount > 0 ? $overallAverage / $resultsCount : 0;

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

                // Insert or update the service area result for current hour type
                if ($resultsCount > 0) {
                    DB::table('service_area_results')->updateOrInsert(
                        [
                            'service_area_code' => $serviceAreaCode,
                            'assessment_period_id' => $activeAssessmentPeriodId,
                            'hour_type' => $hourType,
                        ],
                        [
                            'competences_average' => json_encode($finalCompetencesData, JSON_UNESCAPED_UNICODE),
                            'overall_average' => round($overallAverage, 2),
                            'open_ended_answers' => json_encode($openEndedAnswers, JSON_UNESCAPED_UNICODE),
                            'students_reviewers' => $studentsWithAnswer,
                            'students_enrolled' => $studentsEnrolled,
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );

                    // Accumulate data for total calculation
                    $totalData['studentsWithAnswer'] += $studentsWithAnswer;
                    $totalData['studentsEnrolled'] += $studentsEnrolled;
                    $totalData['overallAverage'] += $overallAverage;
                    $totalData['resultsCount']++;

                    // Merge competences data
                    foreach ($finalCompetencesData as $competence) {
                        $competenceId = $competence['id'];
                        if (!isset($totalData['competencesData'][$competenceId])) {
                            $totalData['competencesData'][$competenceId] = [
                                'id' => $competence['id'],
                                'name' => $competence['name'],
                                'attributes' => [],
                                'overall_sum' => 0,
                                'count' => 0
                            ];
                        }

                        $totalData['competencesData'][$competenceId]['overall_sum'] += $competence['overall_average'];
                        $totalData['competencesData'][$competenceId]['count']++;

                        foreach ($competence['attributes'] as $attribute) {
                            $attributeName = $attribute['name'];
                            if (!isset($totalData['competencesData'][$competenceId]['attributes'][$attributeName])) {
                                $totalData['competencesData'][$competenceId]['attributes'][$attributeName] = [
                                    'name' => $attributeName,
                                    'sum' => 0,
                                    'count' => 0
                                ];
                            }
                            $totalData['competencesData'][$competenceId]['attributes'][$attributeName]['sum'] += $attribute['overall_average'];
                            $totalData['competencesData'][$competenceId]['attributes'][$attributeName]['count']++;
                        }
                    }

                    $totalData['openEndedAnswers'] = array_merge($totalData['openEndedAnswers'], $openEndedAnswers);
                }
            }

            // Calculate and insert total results if we have data from any hour type
            if ($totalData['resultsCount'] > 0) {
                // Calculate final averages for total
                $finalTotalCompetencesData = [];
                foreach ($totalData['competencesData'] as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'attributes' => [],
                        'overall_average' => round($competence['overall_sum'] / $competence['count'], 2)
                    ];

                    foreach ($competence['attributes'] as $attributeName => $attributeData) {
                        if ($attributeData['count'] > 0) {
                            $finalCompetence['attributes'][] = [
                                'name' => $attributeName,
                                'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2)
                            ];
                        }
                    }
                    $finalTotalCompetencesData[] = $finalCompetence;
                }

                // Insert or update the total results
                DB::table('service_area_results')->updateOrInsert(
                    [
                        'service_area_code' => $serviceAreaCode,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'hour_type' => 'total',
                    ],
                    [
                        'competences_average' => json_encode($finalTotalCompetencesData, JSON_UNESCAPED_UNICODE),
                        'overall_average' => round($totalData['overallAverage'] / $totalData['resultsCount'], 2),
                        'open_ended_answers' => json_encode($totalData['openEndedAnswers'], JSON_UNESCAPED_UNICODE),
                        'students_reviewers' => $totalData['studentsWithAnswer'],
                        'students_enrolled' => $totalData['studentsEnrolled'],
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }
        }
    }


    public static function updateFacultyResults()
    {
        // Obtain the active assessment period ID
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        // Retrieve all faculties with their service areas for the active assessment period
        $faculties = \App\Models\Faculty::with('serviceAreas')->get();

        // Define the hour types to iterate over
        $hourTypes = ['normal', 'cátedra'];

        // Loop through each faculty
        foreach ($faculties as $faculty) {
            $totalData = [
                'studentsWithAnswer' => 0,
                'studentsEnrolled' => 0,
                'competencesData' => [],
                'overallAverage' => 0,
                'resultsCount' => 0
            ];

            foreach ($hourTypes as $hourType) {
                $studentsWithAnswer = 0;
                $studentsEnrolled = 0;
                $competencesData = [];
                $overallAverage = 0;
                $hasData = false;

                // Loop through each service area in the faculty
                foreach ($faculty->serviceAreas as $serviceArea) {
                    // Retrieve service area results for the specific hour type
                    $serviceAreaResults = DB::table('service_area_results')
                        ->where('service_area_code', $serviceArea->code)
                        ->where('assessment_period_id', $activeAssessmentPeriodId)
                        ->where('hour_type', $hourType)
                        ->get();

                    // If there are no results, skip this iteration
                    if ($serviceAreaResults->isEmpty()) {
                        continue;
                    }

                    $hasData = true;

                    // Aggregate the results
                    foreach ($serviceAreaResults as $result) {
                        $overallAverage += $result->overall_average;
                        $studentsWithAnswer += $result->students_reviewers;
                        $studentsEnrolled += $result->students_enrolled;

                        // Decode the competences average data
                        $competencesAverage = json_decode($result->competences_average, true);
                        foreach ($competencesAverage as $competence) {
                            $competenceId = $competence['id'];

                            // Initialize competence data if not already set
                            if (!isset($competencesData[$competenceId])) {
                                $competencesData[$competenceId] = [
                                    'id' => $competenceId,
                                    'name' => $competence['name'],
                                    'overall_sum' => 0,
                                    'overall_count' => 0,
                                    'attributes' => []
                                ];
                            }

                            // Aggregate overall competence averages
                            $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                            $competencesData[$competenceId]['overall_count']++;

                            // Loop through each attribute in the competence
                            foreach ($competence['attributes'] as $attribute) {
                                $attributeName = $attribute['name'];

                                // Initialize attribute data if not already set
                                if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                    $competencesData[$competenceId]['attributes'][$attributeName] = [
                                        'name' => $attributeName,
                                        'sum' => 0,
                                        'count' => 0
                                    ];
                                }

                                // Aggregate attribute average
                                if (isset($attribute['overall_average'])) {
                                    $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attribute['overall_average'];
                                    $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                                }
                            }
                        }
                    }
                }

                // Calculate final averages for each competence and its attributes
                $finalCompetencesData = [];
                $overallAverage = $faculty->serviceAreas->count() > 0 ? $overallAverage / $faculty->serviceAreas->count() : 0;

                foreach ($competencesData as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                        'attributes' => []
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

                if (!empty($finalCompetencesData)) {
                    // Insert or update the results for current hour type
                    DB::table('faculty_results')->updateOrInsert(
                        [
                            'faculty_id' => $faculty->id,
                            'assessment_period_id' => $activeAssessmentPeriodId,
                            'hour_type' => $hourType,
                        ],
                        [
                            'overall_average' => round($overallAverage, 2),
                            'students_enrolled' => $studentsEnrolled,
                            'students_reviewers' => $studentsWithAnswer,
                            'competences_average' => json_encode($finalCompetencesData, JSON_UNESCAPED_UNICODE),
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );

                    // Accumulate data for total calculation
                    if ($hasData) {
                        $totalData['studentsWithAnswer'] += $studentsWithAnswer;
                        $totalData['studentsEnrolled'] += $studentsEnrolled;
                        $totalData['overallAverage'] += $overallAverage;
                        $totalData['resultsCount']++;

                        // Merge competences data
                        foreach ($finalCompetencesData as $competence) {
                            $competenceId = $competence['id'];
                            if (!isset($totalData['competencesData'][$competenceId])) {
                                $totalData['competencesData'][$competenceId] = [
                                    'id' => $competence['id'],
                                    'name' => $competence['name'],
                                    'overall_sum' => 0,
                                    'count' => 0,
                                    'attributes' => []
                                ];
                            }

                            $totalData['competencesData'][$competenceId]['overall_sum'] += $competence['overall_average'];
                            $totalData['competencesData'][$competenceId]['count']++;

                            foreach ($competence['attributes'] as $attribute) {
                                $attributeName = $attribute['name'];
                                if (!isset($totalData['competencesData'][$competenceId]['attributes'][$attributeName])) {
                                    $totalData['competencesData'][$competenceId]['attributes'][$attributeName] = [
                                        'name' => $attributeName,
                                        'sum' => 0,
                                        'count' => 0
                                    ];
                                }
                                $totalData['competencesData'][$competenceId]['attributes'][$attributeName]['sum'] += $attribute['overall_average'];
                                $totalData['competencesData'][$competenceId]['attributes'][$attributeName]['count']++;
                            }
                        }
                    }
                }
            }

            // Calculate and insert total results if we have data from any hour type
            if ($totalData['resultsCount'] > 0) {
                // Calculate final averages for total
                $finalTotalCompetencesData = [];
                foreach ($totalData['competencesData'] as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'attributes' => [],
                        'overall_average' => round($competence['overall_sum'] / $competence['count'], 2)
                    ];

                    foreach ($competence['attributes'] as $attributeName => $attributeData) {
                        if ($attributeData['count'] > 0) {
                            $finalCompetence['attributes'][] = [
                                'name' => $attributeName,
                                'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2)
                            ];
                        }
                    }
                    $finalTotalCompetencesData[] = $finalCompetence;
                }

                // Insert or update the total results
                DB::table('faculty_results')->updateOrInsert(
                    [
                        'faculty_id' => $faculty->id,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'hour_type' => 'total',
                    ],
                    [
                        'competences_average' => json_encode($finalTotalCompetencesData, JSON_UNESCAPED_UNICODE),
                        'overall_average' => round($totalData['overallAverage'] / $totalData['resultsCount'], 2),
                        'students_reviewers' => $totalData['studentsWithAnswer'],
                        'students_enrolled' => $totalData['studentsEnrolled'],
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
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
