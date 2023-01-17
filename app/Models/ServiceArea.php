<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ServiceArea
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $assessment_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @method static \Database\Factories\ServiceAreaFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceArea extends Model
{
    protected $guarded = [];
    use HasFactory;

    public static function getCurrentServiceAreas(){
        return self::where('assessment_period_id','=', AssessmentPeriod::getActiveAssessmentPeriod()->id)->orderBy('name', 'asc')->get();
    }

    public static function createOrUpdateFromArray(array $serviceAreas): void
    {
        $upsertData = [];
        $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        foreach ($serviceAreas as $serviceArea) {
            $upsertData[] = [
                'code' => $serviceArea->code,
                'name' => $serviceArea->name,
                'assessment_period_id' => $assessmentPeriodId,
            ];
        }
        self::upsert($upsertData, ['code', 'assessment_period_id'], ['name', 'assessment_period_id','updated_at']);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

}
