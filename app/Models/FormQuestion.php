<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FormQuestion
 *
 * @property int $id
 * @property int $form_id
 * @property mixed $questions
 * @property mixed $answer_options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Form $form
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ResponseIdeal[] $responseIdeal
 * @property-read int|null $response_ideal_count
 * @method static \Database\Factories\FormQuestionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereAnswerOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FormQuestion extends Model
{
    protected $guarded = [];
    use HasFactory;
    public function form(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
    public function responseIdeal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResponseIdeal::class);
    }
    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }
}
