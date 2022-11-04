<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Form
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $degree
 * @property int|null $assessment_period_id
 * @property int|null $unit_id
 * @property int|null $academic_period_id
 * @property string $unity_role
 * @property string|null $teaching_ladder
 * @property int|null $service_area_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicPeriod|null $academicPeriod
 * @property-read \App\Models\AssessmentPeriod|null $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @property-read \App\Models\FormQuestion|null $formQuestions
 * @property-read \App\Models\ServiceArea|null $serviceArea
 * @property-read \App\Models\Unit|null $unity
 * @method static \Database\Factories\FormFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereAcademicPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereServiceAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereTeachingLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUnityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUnityRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Form extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function academicPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function serviceArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function formQuestions(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FormQuestion::class);
    }

    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }

    public static function createStudentForm($request)
    {
        return self::UpdateOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'degree' => $request->input('degree'),
                'academic_period_id' => $request->input('academic_period_id'),
                'service_areas' => json_encode($request->input('service_areas')),
            ]);
    }

    public static function createOthersForm($request)
    {
        return self::UpdateOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'assessment_period_id' => $request->input('assessment_period')['id'],
                'unit_role' => $request->input('unit_role'),
                'teaching_ladder' => $request->input('teaching_ladder'),
                'units' => json_encode($request->input('units')),
            ]);
    }


}
