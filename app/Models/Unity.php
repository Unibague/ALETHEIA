<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Unity
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $is_custom
 * @property int $assessment_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UnityAssessment[] $unityAssessment
 * @property-read int|null $unity_assessment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\UnityFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unity query()
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Unity extends Model
{
    use HasFactory;
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
    return $this->hasMany(Form::class);
}
    public function unityAssessment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnityAssessment::class);
    }
    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

}
