<?php

namespace App\Models;

use App\Helpers\AtlanteProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicPeriod $academicPeriod
 * @property-read ServiceArea $serviceArea
 * @property-read User|null $teacher
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\GroupFactory factory(...$parameters)
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereAcademicPeriodId($value)
 * @method static Builder|Group whereClassCode($value)
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereDegree($value)
 * @method static Builder|Group whereGroup($value)
 * @method static Builder|Group whereHourType($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 * @method static Builder|Group whereServiceAreaId($value)
 * @method static Builder|Group whereTeacherId($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $group_id
 * @property-read Collection|FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @method static Builder|Group whereGroupId($value)
 */
class Group extends Model
{

    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @param array|null $academicPeriodsIds
     * @return Group[]|Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public static function withoutTeacher(array $academicPeriodsIds = null)
    {
        if ($academicPeriodsIds === null) {
            $academicPeriodsIds = AcademicPeriod::getCurrentAcademicPeriodIds();
        }
        return self::whereIn('academic_period_id', $academicPeriodsIds)
            ->where('teacher_id', '=', null)
            ->with(['academicPeriod', 'serviceArea', 'teacher'])
            ->get();
    }

    public function enrolls(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id', 'group_id', 'id')
            ->withPivot(['has_answer']);
    }

    /**
     * @param array $groups
     * @param array $possibleAcademicPeriods
     * @return void
     */
    public static function createOrUpdateFromArray(array $groups, array $possibleAcademicPeriods): void
    {
        $academicPeriods = AcademicPeriod::whereIn('name', $possibleAcademicPeriods)->get()->toArray();
        $academicPeriodNameAndId = array_reduce($academicPeriods, static function ($result, $academicPeriod) {
            $result[$academicPeriod['name']] = $academicPeriod['id'];
            return $result;
        }, []);

        $possibleTeachers = array_unique(array_column($groups, 'teacher_email'));

        $teachers = User::whereIn('email', $possibleTeachers)->get()->toArray();

        $teacherAreaNameAndId = array_reduce($teachers, static function ($result, $teacher) {
            $result[$teacher['email']] = $teacher['id'];
            return $result;
        }, []);

        $upsertData = [];

        foreach ($groups as $group) {
            $upsertData[] = [
                'group_id' => (int)$group['group_id'],
                'academic_period_id' => $academicPeriodNameAndId[$group['academic_period_name']],
                'group' => $group['group_code'],
                'name' => $group['name'],
                'class_code' => $group['class_code'],
                'degree' => strtolower($group['degree_code']),
                'service_area_code' => $group['service_area_code'],
                'teacher_id' => $teacherAreaNameAndId[$group['teacher_email']] ?? null,
                'hour_type' => $group['hour_type'] === '' ? 'normal' : $group['hour_type'],
            ];
        }

        /*$justGroupsIds = array_column($upsertData,'group_id');
        dd(array_diff_assoc($justGroupsIds, array_unique($justGroupsIds)));*/
        self::upsert($upsertData, ['group_id'],
            ['academic_period_id', 'name', 'class_code', 'degree', 'service_area_code', 'teacher_id', 'hour_type']);
    }


    public static function purifyGroups($user): void
    {

            $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

            $email = $user->email;
            $userId = $user->id;

            foreach ($academicPeriods as $academicPeriod) {

                $enrolls = AtlanteProvider::get('enrolls', [
                    'periods' => $academicPeriod->name,
                    'email' => $email
                ], true);


                if (count($enrolls) == 0) {

                    continue;

                }

                $groupsId = [];

                foreach ($enrolls as $enroll) {

                    if ($enroll['pago'] === 'SI' && $enroll['estado'] === "Matriculada") {

                        $groupsId [] = (int)$enroll['group_id'];

                    }

                }

                $existingGroups = DB::table('group_user')
                    ->where('user_id', $userId)->where('academic_period_id', $academicPeriod->id)
                    ->select('group_id', 'academic_period_id')->get()->toArray();


                if (count($existingGroups) > 0) {

                    foreach ($existingGroups as $existingGroup) {

                        if (!in_array($existingGroup->group_id, $groupsId, false)) {

                            if ($existingGroup->academic_period_id == $academicPeriod->id) {


                                DB::table('group_user')
                                    ->where('group_id', '=', $existingGroup->group_id)
                                    ->where('user_id', '=', $userId)
                                    ->where('academic_period_id', '=', $existingGroup->academic_period_id)
                                    ->delete();

                            }

                        }

                    }

                }

            }

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function academicPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_code', 'code');
    }


}
