<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
 * @property int $unit_id
 * @method static \Illuminate\Database\Eloquent\Builder|TeacherProfile whereUnitId($value)
 */
class TeacherProfile extends Model
{
    protected $table = 'v2_teacher_profiles';
    protected $guarded = [];
    use HasFactory;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_identifier','identifier', );
    }

    /**
     * @param array $teachers
     */
    public static function createOrUpdateFromArray(array $teachers): void
    {
        $finalTeachers = $teachers;
        $teacherRoleId = Role::getTeacherRoleId();
        $failedToSyncTeachersCounter = 0;
        $failedToSyncTeachersArray = [];
        $failedToSyncTeachersNames = [];

        $serialized = array_map('serialize', $finalTeachers);
        $unique = array_unique($serialized);
        $finalTeachers = array_intersect_key($finalTeachers, $unique);

        //Iterate over received data and create the academic period
        $assessment_period_id = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $assessmentPeriodAsString = (string)$assessment_period_id;

        $errorMessage = '';

        foreach ($finalTeachers as $teacher) {
            $user = User::firstOrCreate(['email' => $teacher['email']], ['name' => $teacher['name'],
                'password' => Hash::make($teacher['identification_number'] . $teacher['email'])]);

            if ($teacher['unit'] !== "") {
                $unitIdentifier = $teacher['unit'] . '-' . $assessmentPeriodAsString;
            }

            if ($teacher['unit'] == "" && $teacher['employee_type'] == 'DTC'){
                $failedToSyncTeachersArray[] = $teacher;
                $failedToSyncTeachersCounter++;
                continue;
            }

            try {
                self::updateOrCreate(
                    [
                        'identification_number' => $teacher['identification_number'],
                        'unit_identifier' => $unitIdentifier === '' ? null : $unitIdentifier,
                    ],
                    [
                        'user_id' => $user->id,
                        'position' => $teacher['position'] === '' ? null : $teacher['position'],
                        'teaching_ladder' => $teacher['teaching_ladder'] === '' ? null : $teacher['teaching_ladder'],
                        'employee_type' => $teacher['employee_type'] === '' ? null : $teacher['employee_type'],
                        'status' => 'activo',
                        'assessment_period_id' => $assessment_period_id
                    ]);

                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id,
                        'role_id' => $teacherRoleId]
                );


            } catch (\Exception $e) {
                $errorMessage .= nl2br("Ha ocurrido el siguiente error mirando al docente $teacher[name] : {$e->getMessage()}");
            }

            if($teacher['employee_type'] == 'DTC'){
                self::assignTeacherToUnit($user->id, $unitIdentifier);
            }

        }

        if($failedToSyncTeachersCounter) {

            foreach ($failedToSyncTeachersArray as $failedToSyncTeacher){

                $failedToSyncTeachersNames[] = $failedToSyncTeacher['name'];
            }
            throw new \RuntimeException("Docentes cargados, pero ocurrió un problema sincronizando a los siguientes docentes TC: " . implode(",", $failedToSyncTeachersNames));
        }

        if ($errorMessage !== '') {
            throw new \RuntimeException($errorMessage);
        }

    }


    public static function assignTeacherToUnit($userId, $unitIdentifier): void
    {

        $roleId = Role::getTeacherRoleId();
        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        DB::table('v2_unit_user')->updateOrInsert(
            ['user_id' => $userId, 'role_id' => $roleId],
            ['unit_identifier' => $unitIdentifier]
        );

        $user = DB::table('unity_assessments')->where('evaluated_id', $userId)
            ->where('evaluator_id', $userId)->where('assessment_period_id', '=', $activeAssessmentPeriod)->first();

        if (!$user) {
            DB::table('unity_assessments')->updateOrInsert(
                ['evaluated_id' => $userId, 'evaluator_id' => $userId, 'role' => 'autoevaluación'],
                ['pending' => 1, 'unit_identifier' => $unitIdentifier,
                    'assessment_period_id' => $activeAssessmentPeriod,
                    'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                    'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
        }

    }


    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

}

