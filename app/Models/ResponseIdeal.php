<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ResponseIdeal
 *
 * @property int $id
 * @property string $teaching_ladder
 * @property int $form_questions_id
 * @property mixed $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FormQuestion|null $formQuestion
 * @method static \Database\Factories\ResponseIdealFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereFormQuestionsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereTeachingLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResponseIdeal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResponseIdeal extends Model
{
    use HasFactory;
    public function formQuestion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FormQuestion::class);
    }


    public static function saveResponseIdeals($teachingLadder,$competences): void{


        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        DB::table('response_ideals')->updateOrInsert
        (['assessment_period_id'  =>   $activeAssessmentPeriodId, 'teaching_ladder' => $teachingLadder],
            ['response' => $competences,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()]
        );


    }




    public function getResponseIdeals($teaching_ladder): JsonResponse{


        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $competences = DB::table('response_ideals')->where('teaching_ladder', $teaching_ladder)
            ->where('assessment_period_id', $activeAssessmentPeriodId)->select('response')->first();

        if($competences == null){

            $competences = DB::table('response_ideals')->where('teaching_ladder', $teaching_ladder)
                ->where('assessment_period_id', $activeAssessmentPeriodId)->select('response')->get();

            return response()->json($competences);


        }

        $competences = DB::table('response_ideals')->where('teaching_ladder', $teaching_ladder)
            ->where('assessment_period_id', $activeAssessmentPeriodId)->select('response')->first()->response;

        return response()->json(json_decode($competences));


    }



}
