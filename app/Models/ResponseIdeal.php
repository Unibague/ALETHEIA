<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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


    public static function saveResponseIdeals($competences): void{


        $activeAssessmentPeriodsId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        DB::table('response_ideals')->upsert([
            'assessment_period_id' =>   $activeAssessmentPeriodsId,
            'teaching_ladder' => 'asociado',
            'response' => $competences,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ], ['assessment_period_id', 'teaching_ladder']);


    }




}
