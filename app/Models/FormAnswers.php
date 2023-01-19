<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    protected $guarded = [];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @throws \JsonException
     */
    public static function createStudentFormFromRequest(Request $request, Form $form): void
    {
        $competencesAverage = self::getCompetencesAverage(json_decode(json_encode($request->input('answers'), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR));
        self::create([
            'user_id' => auth()->user()->id,
            'form_id' => $form->id,
            'answers' => json_encode($request->input('answers')),
            'submitted_at' => Carbon::now()->toDateTimeString(),
            'group_id' => $request->input('groupId'),
            'teacher_id' => $request->input('teacherId'),
            'first_competence_average' => $competencesAverage['C1'],
            'second_competence_average' => $competencesAverage['C2'],
            'third_competence_average' => $competencesAverage['C3'],
            'fourth_competence_average' => $competencesAverage['C4'],
            'fifth_competence_average' => $competencesAverage['C5'],
            'sixth_competence_average' => $competencesAverage['C6'],
        ]);

        self::updateResponseStatusToAnswered($request->input('groupId'), auth()->user()->id);
    }

    public static function getCompetencesAverage($answers)
    {
        $competences = self::getCompetencesFromFormAnswer($answers);
        return self::getAveragesFromCompetences($competences);
    }

    private static function getAveragesFromCompetences($competences)
    {
        $averages = [];
        foreach ($competences as $competence => $attributes) {
            $averages[$competence] = $attributes['accumulatedValue'] / $attributes['totalAnswers'];
        }
        return $averages;
    }

    private static function getCompetencesFromFormAnswer($formAnswers): array
    {
        $competences = [];
        foreach ($formAnswers as $answer) {
            if (isset($competences[$answer->competence]) === true) {
                $competences[$answer->competence]['totalAnswers']++;
            } else {
                $competences[$answer->competence]['totalAnswers'] = 1;
            }

            // the competence already exist at this point
            if (isset($competences[$answer->competence]['accumulatedValue']) === true) {
                $competences[$answer->competence]['accumulatedValue'] += (double)$answer->answer;
            } else {
                $competences[$answer->competence]['accumulatedValue'] = (double)$answer->answer;
            }
        }
        return $competences;
    }

    public static function updateResponseStatusToAnswered($groupId, $userId): void
    {
        DB::table('group_user')
            ->where('group_id', '=', $groupId)
            ->where('user_id', '=', $userId)
            ->update(
                [
                    'has_answer' => 1
                ]
            );

    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function groupUser()
    {
        DB::table('group_user')->select('*')->join('form_answers', 'form_answers.group_user_id', '=', 'group_user.id')
            ->where('form_answers.group_user_id', '=', $this->group_user_id)->get();
    }

    public function unityAssessment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnityAssessment::class);
    }

    public function form(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Form  ::class);
    }
}
