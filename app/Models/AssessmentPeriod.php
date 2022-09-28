<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AssessmentPeriod
 *
 * @property int $id
 * @property string $name
 * @property string $self_start_date
 * @property string $self_end_date
 * @property string $boss_start_date
 * @property string $boss_end_date
 * @property string $colleague_start_date
 * @property string $colleague_end_date
 * @property int $done_by_none
 * @property int $done_by_auxiliary
 * @property int $done_by_assistant
 * @property int $done_by_associated
 * @property int $head_teacher
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AcademicPeriod[] $academicPeriods
 * @property-read int|null $academic_periods_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ServiceArea[] $serviceArea
 * @property-read int|null $service_area_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TeacherProfile[] $teacher_profiles
 * @property-read int|null $teacher_profiles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Unity[] $unities
 * @property-read int|null $unities_count
 * @method static \Database\Factories\AssessmentPeriodFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereBossEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereBossStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereColleagueEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereColleagueStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereDoneByAssistant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereDoneByAssociated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereDoneByAuxiliary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereDoneByNone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereHeadTeacher($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereSelfEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereSelfStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssessmentPeriod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AssessmentPeriod extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function academicPeriods(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AcademicPeriod::class);
    }

    public function teacher_profiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function unities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unity::class);
    }

    public function serviceArea(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user', 'assessment_period_id', 'group_id');
    }

    public static function getActiveAssessmentPeriod()
    {
        return self::where('active', '=', 1)->firstOrFail();
    }
}
