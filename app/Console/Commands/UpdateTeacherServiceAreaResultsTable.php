<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTeacherServiceAreaResultsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:teacher-service-area-results-update';

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

        return 0;
    }
}
