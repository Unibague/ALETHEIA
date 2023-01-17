<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Unit extends Model
{
    protected $guarded = [];
    use HasFactory;

    public static function getCurrentUnits(){
        return self::where('assessment_period_id','=', AssessmentPeriod::getActiveAssessmentPeriod()->id)->with('users')->get();
    }

    public static function createOrUpdateFromArray(array $units): void
    {
        $upsertData = [];
        $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        foreach ($units as $unit) {
            $upsertData[] = [
                'code' => $unit->code,
                'name' => $unit->name,
                'is_custom' => 0,
                'assessment_period_id' => $assessmentPeriodId
            ];
        }
        DB::table('units')->upsert($upsertData, ['code', 'assessment_period_id'], ['name', 'is_custom', 'assessment_period_id']);
    }


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class,'unit_user','unit_code','user_id');
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
