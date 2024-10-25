<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTeacherStudentPerspectiveResultsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:teacher-student-perspective-results-update';

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
        return 0;
    }
}
