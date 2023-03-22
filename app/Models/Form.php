<?php

namespace App\Models;

use Database\Factories\FormFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicPeriod|null $academicPeriod
 * @property-read AssessmentPeriod|null $assessmentPeriod
 * @property-read Collection|FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @property-read FormQuestion|null $formQuestions
 * @property-read ServiceArea|null $serviceArea
 * @property-read Unit|null $unity
 * @method static FormFactory factory(...$parameters)
 * @method static Builder|Form newModelQuery()
 * @method static Builder|Form newQuery()
 * @method static Builder|Form query()
 * @method static Builder|Form whereAcademicPeriodId($value)
 * @method static Builder|Form whereAssessmentPeriodId($value)
 * @method static Builder|Form whereCreatedAt($value)
 * @method static Builder|Form whereDegree($value)
 * @method static Builder|Form whereId($value)
 * @method static Builder|Form whereName($value)
 * @method static Builder|Form whereServiceAreaId($value)
 * @method static Builder|Form whereTeachingLadder($value)
 * @method static Builder|Form whereType($value)
 * @method static Builder|Form whereUnityId($value)
 * @method static Builder|Form whereUnityRole($value)
 * @method static Builder|Form whereUpdatedAt($value)
 * @mixin Eloquent
 * @property mixed|null $units
 * @property string|null $unit_role
 * @property mixed|null $service_areas
 * @property-read Unit|null $unit
 * @method static Builder|Form whereServiceAreas($value)
 * @method static Builder|Form whereUnitRole($value)
 * @method static Builder|Form whereUnits($value)
 */
class Form extends Model
{
    protected $guarded = [];
    use HasFactory;

    public static function withoutQuestions(int $assessmentPeriodId = null)
    {
        if ($assessmentPeriodId === null) {
            $assessmentPeriodId = (int)AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        return self::where('creation_assessment_period_id', '=', $assessmentPeriodId)
            ->where('questions', '=', null)
            ->with(['academicPeriod', 'assessmentPeriod'])
            ->get();
    }

    public static function getCurrentForms()
    {
        $assessmentPeriodId = (int)AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return self::where('creation_assessment_period_id', '=', $assessmentPeriodId)
            ->with(['academicPeriod', 'assessmentPeriod'])->get();
    }

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
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'degree' => $request->input('degree'),
                'academic_period_id' => $request->input('academic_period_id'),
                'service_areas' => json_encode($request->input('service_areas')),
                'creation_assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
            ]);
    }

    public static function createOthersForm($request)
    {
        return self::UpdateOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'assessment_period_id' => $request->input('assessment_period_id'),
                'unit_role' => $request->input('unit_role'),
                'teaching_ladder' => $request->input('teaching_ladder'),
                'units' => json_encode($request->input('units')),
                'creation_assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
            ]);
    }
}
