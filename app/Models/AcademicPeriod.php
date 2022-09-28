<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AcademicPeriod
 *
 * @property int $id
 * @property string $name
 * @property string $class_start_date
 * @property string $class_end_date
 * @property string $students_start_date
 * @property string $students_end_date
 * @property int $assessment_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @method static \Database\Factories\AcademicPeriodFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereClassEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereClassStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereStudentsEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereStudentsStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcademicPeriod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AcademicPeriod extends Model
{

    protected $guarded = [];
    use HasFactory;

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public static function createFakePeriods(): void
    {
        $academicPeriods = ['2022A', '2022B'];

        $allPeriods = self::all()->count();
        if ($allPeriods === 0) {
            foreach ($academicPeriods as $academicPeriod) {
                (new \Database\Seeders\AcademicPeriodSeeder)->run($academicPeriod);
            }
        }
    }

}
