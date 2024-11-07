<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;

/**
 * App\Models\FormAnswers
 *
 * @property int $id
 * @property int $user_id
 * @property int $form_questions_id
 * @property mixed $questions
 * @property mixed $answers
 * @property string $submitted_at
 * @property int $group_user_id
 * @property int|null $unity_assessment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Form|null $form
 * @property-read \App\Models\UnityAssessment|null $unityAssessment
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\FormAnswersFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereFormQuestionsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereGroupUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereUnityAssessmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereUserId($value)
 * @mixin \Eloquent
 * @property int $form_id
 * @property int|null $group_id
 * @property int|null $teacher_id
 * @property float|null $first_competence_average
 * @property float|null $second_competence_average
 * @property float|null $third_competence_average
 * @property float|null $fourth_competence_average
 * @property float|null $fifth_competence_average
 * @property float|null $sixth_competence_average
 * @property-read \App\Models\Group|null $group
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereFifthCompetenceAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereFirstCompetenceAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereFourthCompetenceAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereSecondCompetenceAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereSixthCompetenceAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswers whereThirdCompetenceAverage($value)
 */
class FormAnswers extends Model
{
    use HasFactory;

    protected $table = 'form_answers';
    protected $guarded = [];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentFormAnswers(): array
    {
        $activeAssessmentPeriodsId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $answers = DB::table('form_answers as fa')
            ->select(['fa.id', 'fa.submitted_at', 'u.name as studentName', 't.name as teacherName', 'g.name as groupName',
                'fa.competences_average', 'fa.overall_average'])
            ->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->join('users as u', 'fa.user_id', '=', 'u.id')
            ->join('users as t', 'fa.teacher_id', '=', 't.id')
            ->join('groups as g', 'fa.group_id', '=', 'g.group_id')
            ->where('f.creation_assessment_period_id', '=', $activeAssessmentPeriodsId)
            ->orderBy('fa.updated_at', 'desc')
            ->get();

        $headers = [
            ['text' => 'Estudiante', 'value' => 'studentName'],
            ['text' => 'Grupo', 'value' => 'groupName'],
            ['text' => 'Profesor', 'value' => 'teacherName'],
                    ];

        $attributeNames = [];

        foreach ($answers as $answer) {
            foreach (json_decode($answer->competences_average) as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $answer->$attributeName = $attribute->average;
                }
            }
        }

        $headers [] = ['text' => 'Fecha de envío', 'value' => 'submitted_at'];
        $headers[] = ['text' => 'Acciones', 'value' => 'actions', 'sortable' => false];

        return ['headers' => $headers, 'answers' => $answers];
    }

    public static function getCurrentTeacherFormAnswers(int $assessmentPeriodId = null): \Illuminate\Support\Collection
    {
        $teacherRoleId = Role::getTeacherRoleId();
        /*      'tsp.first_final_aggregate_competence_average',
                      'tsp.second_final_aggregate_competence_average', 'tsp.third_final_aggregate_competence_average', 'tsp.fourth_final_aggregate_competence_average',
                      'tsp.fifth_final_aggregate_competence_average', 'tsp.sixth_final_aggregate_competence_average'*/

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('form_answers as fa')
            ->select(['t.name', 'f.unit_role', 'fa.first_competence_average', 'fa.second_competence_average', 'fa.third_competence_average',
                'fa.fourth_competence_average', 'fa.fifth_competence_average', 'fa.sixth_competence_average', 't.id as teacherId', 'v2_unit_user.unit_identifier',
                'v2_units.name as unitName', 'fa.submitted_at'])
            ->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->join('users as t', 'fa.teacher_id', '=', 't.id')
            ->join('teachers_students_perspectives as tsp', 'tsp.teacher_id', '=', 't.id')
            ->join('v2_unit_user', 't.id', '=', 'v2_unit_user.user_id')
            ->join('v2_units', 'v2_unit_user.unit_identifier', '=', 'v2_units.identifier')
            ->where('f.creation_assessment_period_id', '=', $assessmentPeriodId)
            ->where('f.type', '=', 'otros')
            ->where('v2_units.assessment_period_id', '=', $assessmentPeriodId)
            ->where('v2_unit_user.role_id', '=', $teacherRoleId)
            ->where('tsp.assessment_period_id', '=', $assessmentPeriodId)->orderBy('t.name', 'ASC')
            ->get();

    }

    public static function getCurrentTeacherFormAnswersFromStudents(int $assessmentPeriodId = null): \Illuminate\Support\Collection
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $teacherRoleId = Role::getTeacherRoleId();

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('teachers_students_perspectives as tsp')->select(['tsp.first_final_aggregate_competence_average as first_competence_average',
            'tsp.second_final_aggregate_competence_average as second_competence_average',
            'tsp.third_final_aggregate_competence_average as third_competence_average',
            'tsp.fourth_final_aggregate_competence_average as fourth_competence_average',
            'tsp.fifth_final_aggregate_competence_average as fifth_competence_average',
            'tsp.sixth_final_aggregate_competence_average as sixth_competence_average', 't.name as name', 't.id as teacherId', 'v2_unit_user.unit_identifier',
            'v2_units.name as unitName',
            'tsp.updated_at as submitted_at', 'tsp.aggregate_students_amount_reviewers', 'tsp.aggregate_students_amount_on_360_groups'])
            ->join('users as t', 'tsp.teacher_id', '=', 't.id')
            ->join('v2_unit_user', 't.id', '=', 'v2_unit_user.user_id')
            ->join('v2_units', 'v2_unit_user.unit_identifier', '=', 'v2_units.identifier')
            ->where('v2_unit_user.role_id', '=', $teacherRoleId)
            ->where('v2_units.assessment_period_id', '=', $assessmentPeriodId)
            ->where('tsp.assessment_period_id', '=', $assessmentPeriodId)
            ->get();
    }

    public static function getFinalGradesFromTeachers(int $assessmentPeriodId = null): \Illuminate\Support\Collection
    {
        $teacherRoleId = Role::getTeacherRoleId();
        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('teachers_360_final_average as t360')->select(['t360.first_final_aggregate_competence_average as first_competence_average',
            't360.second_final_aggregate_competence_average as second_competence_average', 't360.third_final_aggregate_competence_average as third_competence_average',
            't360.fourth_final_aggregate_competence_average as fourth_competence_average', 't360.fifth_final_aggregate_competence_average as fifth_competence_average',
            't360.sixth_final_aggregate_competence_average as sixth_competence_average', 't360.involved_actors',
            't360.total_actors', 't.name as name', 't.id as teacherId', 'v2_unit_user.unit_identifier',
            'v2_units.name as unitName', 'tsp.updated_at as submitted_at'])
            ->join('teachers_students_perspectives as tsp', 'tsp.teacher_id', '=', 't360.teacher_id')
            ->join('users as t', 'tsp.teacher_id', '=', 't.id')
            ->join('v2_unit_user', 't.id', '=', 'v2_unit_user.user_id')
            ->join('v2_units', 'v2_unit_user.unit_identifier', '=', 'v2_units.identifier')
            ->where('v2_unit_user.role_id', '=', $teacherRoleId)
            ->where('tsp.assessment_period_id', '=', $assessmentPeriodId)
            ->where('v2_units.assessment_period_id', '=', $assessmentPeriodId)
            ->where('t360.assessment_period_id', '=', $assessmentPeriodId)->get();
    }


    public static function getOpenAnswersFromStudentsView($teacherId, $serviceArea, int $assessmentPeriodId = null)
    {
        $openAnswersFromStudents = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
//        dd($normalHourType);
        if ($serviceArea !== null) {
            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea)->get();
        } else {
            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'g.name', 'g.group', 'g.group_id'])->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')
                ->join('groups as g', 'g.group_id', '=', 'fa.group_id')->where('forms.type', '=', 'estudiantes')
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->get();
        }

        foreach ($answersFromStudents as $answerFromStudent) {
            $openAnswerFromStudent = $answerFromStudent->answers;
            $allAnswers = json_decode($openAnswerFromStudent);
            foreach ($allAnswers as $singleAnswer) {
//                && $singleAnswer->answer !== "." && $singleAnswer->answer !== "No" && $singleAnswer->answer !== "Ninguna"
                if ($singleAnswer->type == 'abierta' && !in_array($singleAnswer->answer, $openAnswersFromStudents, true)) {
                    $openAnswersFromStudents [] = (object)[
                        'question' => $singleAnswer->name,
                        'answer' => $singleAnswer->answer,
                        'group_name' => $answerFromStudent->name,
                        'group_number' => $answerFromStudent->group,
                    ];
                }
            }
        }

        return $openAnswersFromStudents;
    }

    public static function getOpenAnswersFromStudentsFromGroupView($teacherId, $serviceArea, $groupId, int $assessmentPeriodId = null)
    {
        $openAnswersFromStudents = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        if ($serviceArea !== null) {
            $answersFromStudents = DB::table('form_answers as fa')->select(['answers'])->where('fa.teacher_id', '=', $teacherId)
                ->where('fa.group_id', '=', $groupId)->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea)->get();
        } else {
            $answersFromStudents = DB::table('form_answers as fa')->select(['answers'])->where('fa.teacher_id', '=', $teacherId)
                ->where('fa.teacher_id', '=', $teacherId)->where('fa.group_id', '=', $groupId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->get();
        }

        foreach ($answersFromStudents as $answerFromStudent) {
            $answerFromStudent = $answerFromStudent->answers;
            $allAnswers = json_decode($answerFromStudent);
            foreach ($allAnswers as $singleAnswer) {
                if (isset($singleAnswer->type)) {
                    if ($singleAnswer->type == 'abierta' && !in_array($singleAnswer->answer, $openAnswersFromStudents, true)) {
                        $openAnswersFromStudents [] = (object)[
                            'question' => $singleAnswer->name,
                            'answer' => $singleAnswer->answer];
                    }
                }
            }
        }
        return $openAnswersFromStudents;
    }


    public static function getOpenAnswersFromColleagues($teacherId, int $assessmentPeriodId = null)
    {

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        $answersFromColleagues = DB::table('form_answers as fa')->select(['fa.answers', 'forms.unit_role', 'users.name as colleague_name'])
            ->where('fa.teacher_id', '=', $teacherId)
            ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'otros')
            ->join('users', 'users.id', '=', 'fa.user_id')
            ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->get();


        return self::organizeColleaguesAnswers($answersFromColleagues, $teacherId, $assessmentPeriodId);
    }

    public static function getOpenAnswersFromStudents($teacherId, $serviceArea, int $assessmentPeriodId = null)
    {
        $openAnswersFromStudents = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
//        dd($normalHourType);
        if ($serviceArea !== null) {
            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea)->get();
        }

        foreach ($answersFromStudents as $answerFromStudent) {
            $openAnswerFromStudent = $answerFromStudent->answers;
            $allAnswers = json_decode($openAnswerFromStudent);
            foreach ($allAnswers as $singleAnswer) {
//                && $singleAnswer->answer !== "." && $singleAnswer->answer !== "No" && $singleAnswer->answer !== "Ninguna"
                if ($singleAnswer->type == 'abierta' && !in_array($singleAnswer->answer, $openAnswersFromStudents, true)) {
                    $openAnswersFromStudents [] = (object)[
                        'question' => $singleAnswer->name,
                        'answer' => $singleAnswer->answer,
                        'group_name' => $answerFromStudent->name,
                        'group_number' => $answerFromStudent->group,
                    ];
                }
            }
        }

        return $openAnswersFromStudents;
    }

    public static function getOpenAnswersFromStudentsFromGroup($teacherId, $serviceArea, $groupId, int $assessmentPeriodId = null)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $openAnswersFromStudents = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        if ($serviceArea !== null) {
            $answersFromStudents = DB::table('form_answers as fa')->select(['answers'])->where('fa.teacher_id', '=', $teacherId)
                ->where('fa.group_id', '=', $groupId)->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea)->get();
        } else {
            $answersFromStudents = DB::table('form_answers as fa')->select(['answers'])->where('fa.teacher_id', '=', $teacherId)
                ->where('fa.teacher_id', '=', $teacherId)->where('fa.group_id', '=', $groupId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->get();
        }

        foreach ($answersFromStudents as $answerFromStudent) {
            $answerFromStudent = $answerFromStudent->answers;
            $allAnswers = json_decode($answerFromStudent);
            foreach ($allAnswers as $singleAnswer) {
                if (isset($singleAnswer->type)) {
                    if ($singleAnswer->type == 'abierta' && !in_array($singleAnswer->answer, $openAnswersFromStudents, true)) {
                        $openAnswersFromStudents [] = (object)[
                            'question' => $singleAnswer->name,
                            'answer' => $singleAnswer->answer];
                    }
                }
            }
        }
        return $openAnswersFromStudents;
    }


    public static function getOpenAnswersFromStudents360Report($teacherId, int $assessmentPeriodId = null)
    {
        $finalDataFromTeacher = [];
        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        //Get all the answers
        $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'g.name', 'g.group', 'g.group_id'])
            ->where('fa.teacher_id', '=', $teacherId)
            ->where('forms.type', '=', 'estudiantes')
            ->where('fa.assessment_period_id', '=', $assessmentPeriodId)
            ->join('forms', 'fa.form_id', '=', 'forms.id')
            ->join('groups as g', 'g.group_id', '=', 'fa.group_id')->get()->toArray();

        return self::organizeStudentsAnswers($answersFromStudents, $teacherId, $assessmentPeriodId);
    }


    public static function getOpenAnswersFromStudentsServiceAreasReport($teacherId, $serviceAreas, int $assessmentPeriodId = null)
    {
        $finalDataFromTeacher = [];
        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $finalOpenQuestions = [];

        foreach ($serviceAreas as $serviceArea) {
            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
                ->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();
            foreach ($answersFromStudents as $answerFromStudent) {
                //Decode the answers json object and take
                $questionsFromStudent = json_decode($answerFromStudent->answers);
                foreach ($questionsFromStudent as $questionFromStudent) {
                    if ($questionFromStudent->type === "abierta" && !in_array($questionFromStudent->name, $finalOpenQuestions, true)) {
                        $finalOpenQuestions [] = $questionFromStudent->name;
                    }
                }
            }
        }

        foreach ($finalOpenQuestions as $openQuestion) {
            $finalQuestionData = new \stdClass();
            $finalDataFromTeacherOnServiceAreas = [];
            foreach ($serviceAreas as $serviceArea) {
                $finalDataFromServiceArea = [];
                $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
                    ->where('fa.teacher_id', '=', $teacherId)
                    ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                    ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                    ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                    ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                    ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();

                $groupsData = self::organizeStudentsAnswersServiceArea($answersFromStudents->toArray(), $teacherId, $assessmentPeriodId, $openQuestion);

                $finalDataFromServiceArea = (object)[
                    'service_area_name' => $serviceArea->name,
                    'groups' => $groupsData
                ];

                if ($groupsData) {
                    $finalDataFromTeacherOnServiceAreas [] = $finalDataFromServiceArea;
                }
            }

            $finalQuestionData->question_name = $openQuestion;
            $finalQuestionData->service_areas = $finalDataFromTeacherOnServiceAreas;
            $finalDataFromTeacher [] = $finalQuestionData;
        }

//        dd($finalDataFromTeacher);

        return $finalDataFromTeacher;
    }


    public static function getOpenAnswersFromStudentsSingleServiceAreaReport($teacherId, $serviceArea, int $assessmentPeriodId = null)
    {
        $finalDataFromTeacher = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $serviceArea = DB::table('service_areas')->where('code', '=', $serviceArea)
            ->where('assessment_period_id', '=', $assessmentPeriodId)->get()->first();

        $finalOpenQuestions = [];

        $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
            ->where('fa.teacher_id', '=', $teacherId)
            ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
            ->join('groups', 'groups.group_id', '=', 'fa.group_id')
            ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();

        foreach ($answersFromStudents as $answerFromStudent) {
            //Decode the answers json object and take
            $questionsFromStudent = json_decode($answerFromStudent->answers);
            foreach ($questionsFromStudent as $questionFromStudent) {
                if ($questionFromStudent->type === "abierta" && !in_array($questionFromStudent->name, $finalOpenQuestions, true)) {
                    $finalOpenQuestions [] = $questionFromStudent->name;
                }
            }
        }

        foreach ($finalOpenQuestions as $openQuestion) {
            $finalQuestionData = new \stdClass();

            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
                ->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();

            $groupsData = self::organizeStudentsAnswersServiceArea($answersFromStudents->toArray(), $teacherId, $assessmentPeriodId, $openQuestion);
            $finalDataFromServiceArea = (object)[
                'question_name' => $openQuestion,
                'service_area_name' => $serviceArea->name,
                'groups' => $groupsData
            ];

            if ($groupsData) {
                $finalDataFromTeacher [] = $finalDataFromServiceArea;
            }
        }

        return $finalDataFromTeacher;
    }


    public static function getOpenAnswersFromStudentsGroupsReport($teacherId, $serviceArea, $groupId, int $assessmentPeriodId = null)
    {

        $finalDataFromTeacher = [];

        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $serviceArea = DB::table('service_areas')->where('code', '=', $serviceArea)
            ->where('assessment_period_id', '=', $assessmentPeriodId)->get()->first();

        $finalOpenQuestions = [];

        $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
            ->where('fa.teacher_id', '=', $teacherId)
            ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
            ->join('groups', 'groups.group_id', '=', 'fa.group_id')
            ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
            ->where('groups.group_id', '=', $groupId)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();

        foreach ($answersFromStudents as $answerFromStudent) {
            //Decode the answers json object and take
            $questionsFromStudent = json_decode($answerFromStudent->answers);
            foreach ($questionsFromStudent as $questionFromStudent) {
                if ($questionFromStudent->type === "abierta" && !in_array($questionFromStudent->name, $finalOpenQuestions, true)) {
                    $finalOpenQuestions [] = $questionFromStudent->name;
                }
            }
        }

        foreach ($finalOpenQuestions as $openQuestion) {
            $finalQuestionData = new \stdClass();

            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'groups.name', 'groups.group', 'groups.group_id'])
                ->where('fa.teacher_id', '=', $teacherId)
                ->join('forms', 'fa.form_id', '=', 'forms.id')->where('forms.type', '=', 'estudiantes')
                ->join('groups', 'groups.group_id', '=', 'fa.group_id')
                ->join('service_areas as sa', 'groups.service_area_code', '=', 'sa.code')
                ->where('groups.group_id', '=', $groupId)
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)->where('sa.code', '=', $serviceArea->code)->get();

            $groupData = self::organizeStudentsAnswersServiceArea($answersFromStudents->toArray(), $teacherId, $assessmentPeriodId, $openQuestion);

            if ($groupData) {
                $finalDataFromGroup = (object)[
                    'question_name' => $openQuestion,
                    'service_area_name' => $serviceArea->name,
                    'groups' => $groupData
                ];

                $finalDataFromTeacher [] = $finalDataFromGroup;
            }
        }

        return $finalDataFromTeacher;

    }


    public static function organizeStudentsAnswers($answersFromStudents, $teacherId, $assessmentPeriodId)
    {

        $finalDataAnswers = [];
        $finalAnswers = [];

        //First let's take all the unique open questions from the forms filled by students
        $finalOpenQuestions = [];
        foreach ($answersFromStudents as $answerFromStudent) {
            //Decode the answers json object and take
            $questionsFromStudent = json_decode($answerFromStudent->answers);
            foreach ($questionsFromStudent as $questionFromStudent) {
                if ($questionFromStudent->type === "abierta" && !in_array($questionFromStudent->name, $finalOpenQuestions, true)) {
                    $finalOpenQuestions [] = $questionFromStudent->name;
                }
            }
        }

        $groupsId = array_unique(array_column($answersFromStudents, 'group_id'));
        $groups = DB::table('groups as g')->whereIn('g.group_id', $groupsId)->get();
//        dd($groups);

        foreach ($finalOpenQuestions as $openQuestion) {
            foreach ($groups as $group) {
                $finalAnswersFromGroup = new \stdClass();
                $openAnswersFromStudents = [];
                $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'g.name', 'g.group', 'g.group_id'])
                    ->where('fa.teacher_id', '=', $teacherId)
                    ->where('fa.assessment_period_id', '=', $assessmentPeriodId)
                    ->where('g.group_id', '=', $group->group_id)
                    ->join('forms', 'fa.form_id', '=', 'forms.id')
                    ->join('groups as g', 'g.group_id', '=', 'fa.group_id')->where('forms.type', '=', 'estudiantes')->get();

                foreach ($answersFromStudents as $answersFromStudent) {
                    $decodedAnswersFromStudent = json_decode($answersFromStudent->answers);
                    foreach ($decodedAnswersFromStudent as $decodedAnswerFromStudent) {
                        if ($decodedAnswerFromStudent->type === 'abierta' && $decodedAnswerFromStudent->name === $openQuestion
                            && !in_array($decodedAnswerFromStudent->answer, $openAnswersFromStudents, true)
                        ) {
                            $openAnswersFromStudents [] = $decodedAnswerFromStudent->answer;
                        }
                    }
                }
                $finalAnswersFromGroup->group_name = $group->name;
                $finalAnswersFromGroup->group_number = $group->group;
                $finalAnswersFromGroup->answers = $openAnswersFromStudents;
                $finalAnswers [] = $finalAnswersFromGroup;
            }

            $finalAnswersFromOpenQuestion = new \stdClass();
            $finalAnswersFromOpenQuestion->question_name = $openQuestion;
            $finalAnswersFromOpenQuestion->groups = $finalAnswers;
            $finalDataAnswers [] = $finalAnswersFromOpenQuestion;

        }

//       dd($finalDataAnswers);

        return $finalDataAnswers;
    }

    public static function organizeStudentsAnswersServiceArea($answersFromStudents, $teacherId, $assessmentPeriodId, $openQuestion)
    {

        $groupsId = array_unique(array_column($answersFromStudents, 'group_id'));
        $groups = DB::table('groups as g')->whereIn('g.group_id', $groupsId)->get();
//        dd($groups);
        $finalAnswers = [];

        foreach ($groups as $group) {
            $finalAnswersFromGroup = new \stdClass();
            $openAnswersFromStudents = [];
            $answersFromStudents = DB::table('form_answers as fa')->select(['fa.answers', 'g.name', 'g.group', 'g.group_id'])
                ->where('fa.teacher_id', '=', $teacherId)
                ->where('fa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('g.group_id', '=', $group->group_id)
                ->join('forms', 'fa.form_id', '=', 'forms.id')
                ->join('groups as g', 'g.group_id', '=', 'fa.group_id')->where('forms.type', '=', 'estudiantes')->get();

            foreach ($answersFromStudents as $answersFromStudent) {
                $decodedAnswersFromStudent = json_decode($answersFromStudent->answers);
//                dd($decodedAnswersFromStudent);
                foreach ($decodedAnswersFromStudent as $decodedAnswerFromStudent) {
                    if ($decodedAnswerFromStudent->type === 'abierta' && $decodedAnswerFromStudent->name === $openQuestion &&
                        !in_array($decodedAnswerFromStudent->answer, $openAnswersFromStudents, true)) {
                        $openAnswersFromStudents [] = $decodedAnswerFromStudent->answer;
                    }
                }
            }
            $finalAnswersFromGroup->group_name = $group->name;
            $finalAnswersFromGroup->group_number = $group->group;
            $finalAnswersFromGroup->answers = $openAnswersFromStudents;
            $finalAnswers [] = $finalAnswersFromGroup;
        }

        return $finalAnswers;
    }


    public static function organizeColleaguesAnswers($answersFromColleagues, $teacherId, $assessmentPeriodId)
    {

        $finalDataAnswers = [];

        //First let's take all the unique open questions from the forms filled by teachers
        $finalOpenQuestions = [];
        foreach ($answersFromColleagues as $answerFromColleague) {
            //Decode the answers json object and take
            $questionsFromColleague = json_decode($answerFromColleague->answers);
            foreach ($questionsFromColleague as $questionFromColleague) {
                if ($questionFromColleague->type === "abierta" && !in_array($questionFromColleague->name, $finalOpenQuestions, true)) {
                    $finalOpenQuestions [] = $questionFromColleague->name;
                }
            }
        }

        foreach ($finalOpenQuestions as $openQuestion) {
            $finalQuestionAnswers = new \stdClass();
            $finalAnswers = [];
            foreach ($answersFromColleagues as $answersFromColleague) {
//                    dd($answersFromColleague);
                $openAnswersFromColleague = [];
                $finalAnswersFromColleague = new \stdClass();
                $decodedAnswersFromColleague = json_decode($answersFromColleague->answers);
                foreach ($decodedAnswersFromColleague as $decodedAnswerFromColleague) {
                    if ($decodedAnswerFromColleague->type === 'abierta' && $decodedAnswerFromColleague->name === $openQuestion
                        && !in_array($decodedAnswerFromColleague->answer, $openAnswersFromColleague, true)) {
                        $openAnswersFromColleague [] = $decodedAnswerFromColleague->answer;
                    }
                }

                if (count($openAnswersFromColleague) > 0) {
                    $finalAnswersFromColleague->answers = $openAnswersFromColleague;
                    $finalAnswersFromColleague->unit_role = $answersFromColleague->unit_role;
                    $finalAnswersFromColleague->name = $answersFromColleague->colleague_name;
                    $finalAnswers [] = $finalAnswersFromColleague;
                }
            }

            $finalQuestionAnswers->answers = $finalAnswers;
            $finalQuestionAnswers->question_name = $openQuestion;
            $finalDataAnswers [] = $finalQuestionAnswers;

        }

        return $finalDataAnswers;
    }


    public static function calculateCompetenceAverage($competenceAnswers)
    {
        if (empty($competenceAnswers)) {
            return 0;
        }

        $sum = array_sum($competenceAnswers);
        return $sum / count($competenceAnswers);
    }

    /**
     * @throws \JsonException
     */
    public static function createStudentFormFromRequest(Request $request, Form $form): void
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $competencesAverage = self::getCompetencesAverage(json_decode(json_encode($request->input('answers'), JSON_THROW_ON_ERROR),
            false, 512, JSON_THROW_ON_ERROR));
        $openEndedAnswers = self::getOpenEndedAnswersFromFormAnswer($request->input('answers'));

        self::firstOrCreate([
            'user_id' => auth()->user()->id,
            'group_id' => $request->input('groupId'),
            'teacher_id' => $request->input('teacherId'),
            'assessment_period_id' => $activeAssessmentPeriodId
        ],
            ['form_id' => $form->id,
                'answers' => json_encode($request->input('answers')),
                'submitted_at' => Carbon::now()->toDateTimeString(),
                'competences_average' => json_encode($competencesAverage['competences']),
                'open_ended_answers' => json_encode($openEndedAnswers),
                'overall_average' => $competencesAverage['overall_average'],
            ]);

        //Let's check if user already answered the test
        $alreadyAnswered = DB::table('group_user')
            ->where('group_id', '=', $request->input('groupId'))
            ->where('user_id', '=', auth()->user()->id)->where('has_answer', '=', 1)->first();

        if ($alreadyAnswered) {
            return;
        }
        self::updateResponseStatusToAnswered($request->input('groupId'), auth()->user()->id);
        //Check if user already answered every test
        Group::allGroupsAnswered();
    }

    public static function updateResponseStatusToAnswered($groupId, $userId): void
    {
        DB::table('group_user')
            ->where('group_id', '=', $groupId)
            ->where('user_id', '=', $userId)->update(['has_answer' => 1]);
    }

    public static function createTeacherFormFromRequest(Request $request, Form $form): void
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $competencesAverage = self::getCompetencesAverage(json_decode(json_encode($request->input('answers'), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR));

        self::create([
            'user_id' => auth()->user()->id,
            'form_id' => $form->id,
            'answers' => json_encode($request->input('answers')),
            'submitted_at' => Carbon::now()->toDateTimeString(),
            'group_id' => null,
            'teacher_id' => $request->input('teacherId'),
            'competences_average' => $competencesAverage,
            'assessment_period_id' => $activeAssessmentPeriodId,
        ]);

        self::updateTeacherResponseStatusToAnswered(auth()->user()->id, $request->input('role'), $request->input('teacherId'));
    }


    public static function getCompetencesAverage($answers): array
    {
        $competences = self::getCompetencesFromFormAnswer($answers);
        $averages = self::getAveragesFromCompetences($competences);
        return self::calculateOverallAverage($averages);
    }

    public static function getOpenEndedAnswersFromFormAnswer($formAnswers): array
    {
        $openAnswers = [];
        foreach ($formAnswers as $answer) {

            if(!array_key_exists('type', $answer)){
                $answer['type'] = 'multiple';
            }

            if ($answer['type'] !== 'abierta') {
                continue;
            }

            if (!array_key_exists('commentType', $answer)) {
                $answer['commentType'] = 'General';
            }

            $openAnswers[] = [
                'question' => $answer['name'],
                'commentType' => $answer['commentType'],
                'answer' => $answer['answer']
            ];
        }
        return $openAnswers;
    }


    public static function groupOpenEndedAnswers($openEndedAnswers) {

        $groupedAnswers = [];
        foreach ($openEndedAnswers as $answerSet) {
            foreach ($answerSet as $answer) {
                $question = $answer['question'];
                $commentType = $answer['commentType'];
                $answerText = $answer['answer'];

                if (!isset($groupedAnswers[$question])) {
                    $groupedAnswers[$question] = [
                        'question' => $question,
                        'commentType' => []
                    ];
                }

                if (!isset($groupedAnswers[$question]['commentType'][$commentType])) {
                    $groupedAnswers[$question]['commentType'][$commentType] = [
                        'type' => $commentType,
                        'answers' => []
                    ];
                }

                $groupedAnswers[$question]['commentType'][$commentType]['answers'][] = $answerText;
            }
        }

        // Convert associative arrays to indexed arrays
        foreach ($groupedAnswers as &$question) {
            $question['commentType'] = array_values($question['commentType']);
        }
        return array_values($groupedAnswers);
    }

    private
    static function getAveragesFromCompetences($competences): array
    {
        $averages = [];

        foreach ($competences as $competence) {
            $competenceData = [
                'id' => $competence['id'],
                'name' => $competence['name'],
                'average' => round($competence['accumulatedValue'] / $competence['totalAnswers'], 2),
                'attributes' => []
            ];

            foreach ($competence['attributes'] as $attributeName => $attribute) {
                $competenceData['attributes'][] = [
                    'name' => $attributeName,
                    'average' => round($attribute['accumulatedValue'] / $attribute['totalAnswers'], 2)
                ];
            }

            $averages[] = $competenceData;
        }

        return $averages;
    }

    private static function getCompetencesFromFormAnswer($formAnswers): array
    {
        $competences = [];
        try {
            foreach ($formAnswers as $answer) {

                if ($answer->type === 'abierta') {
                    continue;
                }

                $competence = $answer->competence;
                $attribute = $competence->attribute;

                if (!isset($competences[$competence->name])) {
                    $competences[$competence->name] = [
                        'id' => $competence->id,
                        'name' => $competence->name,
                        'totalAnswers' => 0,
                        'accumulatedValue' => 0,
                        'attributes' => []
                    ];
                }

                if (!isset($competences[$competence->name]['attributes'][$attribute])) {
                    $competences[$competence->name]['attributes'][$attribute] = [
                        'name' => $attribute,
                        'totalAnswers' => 0,
                        'accumulatedValue' => 0,
                    ];
                }

                // Update competence level stats
                $competences[$competence->name]['totalAnswers']++;
                $competences[$competence->name]['accumulatedValue'] += (double)$answer->answer;

                // Update attribute level stats
                $competences[$competence->name]['attributes'][$attribute]['totalAnswers']++;
                $competences[$competence->name]['attributes'][$attribute]['accumulatedValue'] += (double)$answer->answer;
            }
        } catch (\Exception $exception) {
            throw new \RuntimeException('Debes contestar todas las preguntas para poder enviar el formulario');
        }
        return $competences;
    }


    private static function calculateOverallAverage($averages): array
    {
        // Filter out competences where name is "Satisfacción"
        $filteredAverages = array_filter($averages, function ($competence) {
            return $competence['name'] !== 'Satisfacción';
        });

        // Calculate sum and count only for the filtered competences
        $sum = array_sum(array_column($filteredAverages, 'average'));
        $count = count($filteredAverages);
        $overallAverage = $count > 0 ? round($sum / $count, 2) : 0;

        return [
            'competences' => $averages, // Return the original competences
            'overall_average' => $overallAverage
        ];
    }
    public
    static function updateTeacherResponseStatusToAnswered($userId, $role, $evaluatedId): void
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        if ($role == 'autoevaluación') {

            UnityAssessment::where('evaluated_id', $userId)->where('role', $role)
                ->where('evaluator_id', $userId)
                ->where('assessment_period_id', $activeAssessmentPeriodId)->update([
                    'pending' => 0
                ]);
        }

        if ($role == "jefe" || $role == "par") {

            UnityAssessment::where('evaluated_id', $evaluatedId)->where('role', $role)
                ->where('evaluator_id', $userId)
                ->where('assessment_period_id', $activeAssessmentPeriodId)->update([
                    'pending' => 0
                ]);

        }
    }


    public
    function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public
    function groupUser()
    {
        DB::table('group_user')->select('*')->join('form_answers', 'form_answers.group_user_id', '=', 'group_user.id')
            ->where('form_answers.group_user_id', '=', $this->group_user_id)->get();
    }

    public
    function unityAssessment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnityAssessment::class);
    }

    public
    function form(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Form  ::class);
    }
}
