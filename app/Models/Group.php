<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property int $academic_period_id
 * @property string $class_code
 * @property string $group
 * @property string $degree
 * @property int $service_area_id
 * @property int|null $teacher_id
 * @property string $hour_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicPeriod $academicPeriod
 * @property-read \App\Models\ServiceArea $serviceArea
 * @property-read \App\Models\User|null $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\GroupFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereAcademicPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereClassCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereHourType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereServiceAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $group_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereGroupId($value)
 */
class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function createOrUpdateFromArray(array $groups, array $possibleAcademicPeriods): object
    {
        $academicPeriods = AcademicPeriod::whereIn('name', $possibleAcademicPeriods)->get()->toArray();
        $academicPeriodNameAndId = array_reduce($academicPeriods, static function ($result, $academicPeriod) {
            $result[$academicPeriod['name']] = $academicPeriod['id'];
            return $result;
        }, []);

        $possibleServiceAreas = array_unique(array_column($groups, 'service_area_code'));
        $serviceAreas = ServiceArea::whereIn('code', $possibleServiceAreas)->get()->toArray();
        $serviceAreaNameAndId = array_reduce($serviceAreas, static function ($result, $academicPeriod) {
            $result[$academicPeriod['code']] = $academicPeriod['id'];
            return $result;
        }, []);

        $possibleTeachers = array_unique(array_column($groups, 'teacher_email'));
        $teachers = User::whereIn('email', $possibleTeachers)->get()->toArray();
        $teacherAreaNameAndId = array_reduce($teachers, static function ($result, $teacher) {
            $result[$teacher['email']] = $teacher['id'];
            return $result;
        }, []);
        $errors = [];

        foreach ($groups as $group) {
            try {
                self::updateOrCreate(
                    [
                        'group_id' => (int)$group['group_id']
                    ],
                    [
                        'group' => $group['group_code'],
                        'name' => $group['name'],
                        'academic_period_id' => $academicPeriodNameAndId[$group['academic_period_name']],
                        'class_code' => $group['class_code'],
                        'degree' => strtolower($group['degree_code']),
                        'service_area_id' => $serviceAreaNameAndId[$group['service_area_code']],
                        'teacher_id' => $teacherAreaNameAndId[$group['teacher_email']],
                        'hour_type' => $group['hour_type'],
                    ]);
            } catch (\Exception $e) {
                $errors[] = ['group' => $group, 'error' => $e->getMessage()];
            }
        }
        $hasError = count($errors) > 0;
        return (object)['hasError' => $hasError, 'errors' => $errors];
    }

    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function serviceArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }


}
