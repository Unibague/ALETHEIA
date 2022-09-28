<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TeacherProfile
 *
 * @property int $id
 * @property int $assessment_period_id
 * @property string $identification_number
 * @property int $user_id
 * @property string $unity
 * @property string $position
 * @property string $teaching_ladder
 * @property string $employee_type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\TeacherProfileFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereEmployeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereIdentificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereTeachingLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereUnity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereUserId($value)
 * @mixin \Eloquent
 */
class TeacherProfile extends Model
{
    use HasFactory;
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

}

