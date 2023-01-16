<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UnityAssessment
 *
 * @property int $id
 * @property int $evaluated_id
 * @property int $evaluator_id
 * @property string $role
 * @property int $pending
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $evaluated
 * @property-read \App\Models\User $evaluator
 * @property-read \App\Models\FormAnswers|null $formAnswer
 * @property-read \App\Models\Unit $unity
 * @method static \Database\Factories\UnityAssessmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereEvaluatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereEvaluatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment wherePending($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Unit $unit
 */
class UnityAssessment extends Model
{
    use HasFactory;
    public function evaluated(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'evaluated_id');
    }
    public function evaluator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'evaluator_id');
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    public function formAnswer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FormAnswers::class);
    }
}
