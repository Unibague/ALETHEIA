<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\UnityAssessment
 *
 * @property int $id
 * @property int $evaluated_id
 * @property int $evaluator_id
 * @property string $role
 * @property int $pending
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $evaluated
 * @property-read \App\Models\User $evaluator
 * @property-read \App\Models\FormAnswers|null $formAnswer
 * @property-read \App\Models\Unit $unity
 * @method static \Database\Factories\UnityAssessmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereEvaluatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereEvaluatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment wherePending($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnityAssessment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Unit $unit
 */
class UnityAssessment extends Model
{
    use HasFactory;

    protected $guarded = [];


    public static function assignRolesToTeacher($beingAssignedUserId, $assignedToUserId, $role,$unitIdentifier): void
    {

        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        //Si el usuario que desea asignarle es el mismo, pues lo saca de una vez
        if ($beingAssignedUserId == $assignedToUserId) {
            throw new \RuntimeException('El docente no puede ser su propio par/jefe');
        }

        $peerRole = Role::getRoleIdByName('par');

        /*Si ya hay un par o jefe asignado, se encarga de que no se pueda colocar a esa misma persona como jefe o par*/
/*        $record = DB::table('unity_assessments')->where('evaluated_id', $beingAssignedUserId)
            ->where('evaluator_id', $assignedToUserId)->exists();

        if ($record) {

            throw new \RuntimeException('Ese docente ya es par/jefe del respectivo docente');
        }*/

        //Si ya hay una persona asignada a este rol de par para este docente, entonces vamos a validar al profesor que se cambiará
        //para saber si lo quitamos de la tabla de v2_unit_user

        $existingPeer = self::where('evaluated_id', $beingAssignedUserId)
            ->where('role', $role)->where('unit_identifier', $unitIdentifier)
            ->where('assessment_period_id', $activeAssessmentPeriod)->first();

        if ($existingPeer) {

            $existingPeer = $existingPeer->evaluator_id;

            $remainingAssignments = self::where('evaluator_id', $existingPeer)
                ->where('unit_identifier', $unitIdentifier)->get();


            if ($remainingAssignments->count() <= 1) {

                DB::table('v2_unit_user')->where('user_id', $existingPeer)
                    ->where('unit_identifier', $unitIdentifier)->where('role_id', $peerRole)->delete();

            }
        }


            if ($role == 'par') {

                DB::table('v2_unit_user')->updateOrInsert(['user_id' => $assignedToUserId,
                    'unit_identifier' => $unitIdentifier, 'role_id' => $peerRole]);

            }

            self::updateOrCreate(
                ['evaluated_id' => $beingAssignedUserId,
                    'role' => $role],
                ['evaluator_id' => $assignedToUserId,
                    'pending' => true,
                    'unit_identifier' => $unitIdentifier,
                    'assessment_period_id' => $activeAssessmentPeriod,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()]);
            /*

                    //Si ya hay una persona asignada a este rol de  para este docente, entonces vamos a validar al profesor que se cambiará
                    //para saber si lo quitamos de la tabla de v2_unit_user y de la tabla role_user

                    $roleId = Role::getRoleIdByName('jefe de profesor');

                    $existingBoss = self::where('evaluated_id', $beingAssignedUserId)
                        ->where('role', $role)->where('unit_identifier', $unitIdentifier)
                        ->where('assessment_period_id', $activeAssessmentPeriod)->first();

                    if($existingBoss){

                        $existingBoss = $existingBoss->evaluator_id;

                        $remainingAssignments = self::where('evaluator_id', $existingBoss)
                            ->where('unit_identifier', $unitIdentifier)->get();


                        if ($remainingAssignments->count() <= 1){

                            DB::table('v2_unit_user')->where('user_id', $existingBoss)
                                ->where('unit_identifier', $unitIdentifier)->where('role_id', $roleId)->delete();

                        }


                        //Aqui verificamos si el usuario al que fue asignado, tiene otras asignaciones adicionales...
                        $user = DB::table('unity_assessments')->where('evaluator_id', $existingBoss)
                            ->where('role', $role)->get();


                        //Si se llega a dar que esa asignación que borramos era la última para el correspondiente rol, entonces procedemos a
                        //borrarle ese rol en la tabla role_user
                        if($user->count() <= 1){

                            DB::table('role_user')->where('user_id', $existingBoss)
                                ->where('role_id', $roleId)->delete();

                        }

                    }


                    $teacherWithRole = DB::table('role_user')
                        ->where('user_id', $assignedToUserId)->where('role_id', $roleId)->first();

                    if (!$teacherWithRole) {

                        DB::table('role_user')->updateOrInsert(['user_id' => $assignedToUserId, 'role_id' => $roleId]);

                    }


                    //Le damos el role de jefe a la persona en la unidad
                    DB::table('v2_unit_user')->updateOrInsert(['user_id' => $assignedToUserId,
                                'unit_identifier' => $unitIdentifier, 'role_id' => $roleId]);


                    self::updateOrCreate(
                        ['evaluated_id' => $beingAssignedUserId,
                            'role' => $role],
                        [ 'evaluator_id' => $assignedToUserId,
                            'pending' => true,
                            'unit_identifier' => $unitIdentifier,
                            'assessment_period_id' => $activeAssessmentPeriod]);
            */

    }

    public static function removeAssignment($beingAssignedUserId, $assignedToUserId, $role, $unitIdentifier): void{

        //Aqui simplemente buscamos el registro asociado a esa asignación y lo borramos
         DB::table('unity_assessments')->where('evaluated_id', $beingAssignedUserId)
            ->where('evaluator_id', $assignedToUserId)->where('role', $role)->delete();

        $peerRole = Role::getRoleIdByName('par');

        //Aqui verificamos si ese par tiene otras asignaciones restantes dentro de esa unidad
        $remainingAssignments = self::where('evaluator_id', $assignedToUserId)
            ->where('unit_identifier', $unitIdentifier)->get();

        if ($remainingAssignments->count() == 0){

            DB::table('v2_unit_user')->where('user_id', $assignedToUserId)
                ->where('unit_identifier', $unitIdentifier)->where('role_id', $peerRole)->delete();

        }

/*
        //Aqui verificamos si el usuario al que fue asignado, tiene otras asignaciones adicionales...
        $user = self::where('evaluator_id', $assignedToUserId)
            ->where('role', $role)->get();


         //Si se llega a dar que esa asignación que borramos era la última para el correspondiente rol, entonces procedemos a
        //borrarle ese rol en la tabla role_user
        if($user->count() == 0){

            DB::table('role_user')->where('user_id', $assignedToUserId)
                ->where('role_id', $roleId)->delete();
        }*/


    }



    public static function getAllAssignments(){

           return self::get();
    }


    public static function getAutoAssessmentFromTeacher($userId){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $now = Carbon::now();
        $date = $now->toDateString();

        return self::where('unity_assessments.evaluated_id', $userId)->where('role', 'autoevaluación')
            ->where('unity_assessments.assessment_period_id', $activeAssessmentPeriodId)
            ->join('assessment_periods', 'assessment_periods.id','unity_assessments.assessment_period_id')
            ->join('v2_teacher_profiles','v2_teacher_profiles.user_id','unity_assessments.evaluated_id')
            ->join('users','users.id','v2_teacher_profiles.user_id')
            ->where('v2_teacher_profiles.status', '=', 'activo')
            ->where('v2_teacher_profiles.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('assessment_periods.self_start_date', '<=', $date)
            ->where('assessment_periods.self_end_date', '>=', $date)
            ->select(
                ['unity_assessments.evaluated_id as id',
                    'unity_assessments.role',
                    'unity_assessments.unit_identifier',
                    'unity_assessments.pending',
                    'v2_teacher_profiles.teaching_ladder',
                    'users.name',
                    'assessment_periods.self_start_date',
                    'assessment_periods.self_end_date'
                    ])->get();

    }

    public static function getPeerAssessmentsFromTeacher($userId){

        $now = Carbon::now();
        $date = $now->toDateString();

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return self::where('unity_assessments.evaluator_id', $userId)->where('role', 'par')
            ->where('unity_assessments.assessment_period_id', $activeAssessmentPeriodId)
            ->join('assessment_periods', 'assessment_periods.id','unity_assessments.assessment_period_id')
            ->join('users','users.id', 'unity_assessments.evaluated_id')
            ->join('v2_teacher_profiles','v2_teacher_profiles.user_id','users.id')
            ->where('v2_teacher_profiles.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('v2_teacher_profiles.status', '=', 'activo')
            ->where('assessment_periods.colleague_start_date', '<=', $date)
            ->where('assessment_periods.colleague_end_date', '>=', $date)
            ->select(['users.name', 'users.id','unity_assessments.unit_identifier', 'v2_teacher_profiles.teaching_ladder',
                'unity_assessments.pending',
                'assessment_periods.colleague_start_date',
                'assessment_periods.colleague_end_date'])->get();
    }


    public static function getBossAssessmentsFromTeacher($userId){

        $now = Carbon::now();
        $date = $now->toDateString();

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return self::where('unity_assessments.evaluator_id', $userId)->where('role', 'jefe')
            ->where('unity_assessments.assessment_period_id', $activeAssessmentPeriodId)
            ->join('assessment_periods', 'assessment_periods.id','unity_assessments.assessment_period_id')
            ->join('users','users.id', 'unity_assessments.evaluated_id')
            ->join('v2_teacher_profiles','v2_teacher_profiles.user_id','users.id')
            ->where('v2_teacher_profiles.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('v2_teacher_profiles.status', '=', 'activo')
            ->where('assessment_periods.boss_start_date', '<=', $date)
            ->where('assessment_periods.boss_end_date', '>=', $date)
            ->select(['users.name', 'users.id','unity_assessments.unit_identifier', 'v2_teacher_profiles.teaching_ladder',
                'unity_assessments.pending',
                'assessment_periods.boss_start_date',
                'assessment_periods.boss_end_date'])->get();
    }



    public static function getUnitAssignments($unitTeachersId){

        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return DB::table('unity_assessments as ua')
            ->whereIn('ua.evaluated_id', $unitTeachersId)->where('ua.assessment_period_id', '=', $activeAssessmentPeriod)
            ->join('users','users.id','=', 'ua.evaluator_id')->get();

    }


    public function evaluated(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'evaluated_id');
    }
    public function evaluator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'evaluator_id');
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    public function formAnswer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FormAnswers::class);
    }
}
