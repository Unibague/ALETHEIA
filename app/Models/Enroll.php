<?php

namespace App\Models;

use App\Helpers\AtlanteProvider;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


/**
 * App\Models\Enroll
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Enroll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enroll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enroll query()
 * @mixin Eloquent
 */
class Enroll extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'group_user';


    public static function getActiveEnrolls(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $activeAcademicPeriods = AcademicPeriod::getCurrentAcademicPeriodIds();
        return self::whereIn('academic_period_id', $activeAcademicPeriods)->with(['user', 'group', 'academicPeriod', 'group.teacher'])->paginate(500);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }


    public static function checkExistingGroups(): void{
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();
        $academicPeriodsIds = [];
        foreach ($academicPeriods as $academicPeriod){
            $academicPeriodsIds [] = $academicPeriod['id'];
        }
        $groups = AtlanteProvider::get('enrolls', [
            'periods' =>  AcademicPeriod::getCurrentAcademicPeriodsByCommas()
        ], true);

        $nonExistingGroupsFromEndpoint = [];
        $existingGroupsFromEndpointIds = [];
        foreach ($groups as $group){

            $existingGroupsFromEndpointIds [] = $group['group_id'];
        }
        $existingGroupsInDB = DB::table('group_user')->whereIn('academic_period_id',$academicPeriodsIds)
            ->select('group_id')->get()->toArray();

        $existingGroupsInDB = array_column($existingGroupsInDB, 'group_id');
        foreach ($existingGroupsInDB as $existingGroupInDB){

            if(!in_array($existingGroupInDB, $existingGroupsFromEndpointIds, false)){

                $nonExistingGroupsFromEndpoint [] = $existingGroupInDB;
            }
        }
        dd(array_unique($nonExistingGroupsFromEndpoint));

    }


    public static function deleteThoseDuplicatedGroups(array $enrolls, int $academicPeriodId): void
    {

        $users = User::updateOrCreateFromArrayAndGetUsers($enrolls);

        $userEmailAndId = array_reduce($users, static function ($result, $user) {
            $result[$user->email] = $user->id;
            return $result;
        }, []);

        foreach ($enrolls as $enroll) {

            $groupsId = [];

            if ($enroll['pago'] === 'SI' && $enroll['estado'] === "Matriculada") {

                $groupsId [] = (int)$enroll['group_id'];


            } else {

                continue;

            }

            $userId = $userEmailAndId[$enroll["email"]];

            $existingGroups = DB::table('group_user')
                ->where('user_id', $userId)->where('academic_period_id', $academicPeriodId)
                ->select('group_id', 'academic_period_id')->get()->toArray();

            if (count($existingGroups)>0){

                foreach ($existingGroups as $existingGroup){

                    if(!in_array($existingGroup->group_id, $groupsId, false)){
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


    public static function createOrUpdateFromArray(array $enrolls, int $academicPeriodId): void
    {
        //Create users
        $users = User::updateOrCreateFromArrayAndGetUsers($enrolls);

        $userEmailAndId = array_reduce($users, static function ($result, $user) {
            $result[$user->email] = $user->id;
            return $result;
        }, []);

        $upsertData = [];
        $deleteData = [];

        foreach ($enrolls as $enroll) {

            if ($enroll['pago'] === 'SI' && $enroll['estado'] === "Matriculada") {
                $upsertData[] = [
                    'user_id' => $userEmailAndId[$enroll["email"]],
                    'group_id' => $enroll['group_id'],
                    'has_answer' => 0,
                    'academic_period_id' => $academicPeriodId
                ];


            } else {
                $deleteData[] = [
                    'user_id' => $userEmailAndId[$enroll["email"]],
                    'group_id' => $enroll['group_id'],
                    'academic_period_id' => $academicPeriodId
                ];
            }

        }
       /*     foreach ($upsertData as $item) {
                try {
                    self::updateOrCreate([
                        'group_id' => $item['group_id'],
                        'user_id' => $item['user_id'],
                        'academic_period_id' => $item['academic_period_id']
                    ], [
                        'has_answer' => 0
                    ]);
                } catch (\Exception $e) {
                    dd('Encontre este error :' . $e->getMessage());
                }
            }*/
            try {
                foreach (array_chunk($upsertData, 1000) as $sqlData) {
                    self::upsert($sqlData, ['group_id', 'user_id', 'academic_period_id'], ['updated_at']);
                }
            } catch (\Exception $exception) {
                $message = 'No se ha podido migrar toda la carga académica, asegurese de haber sincronizado previamente los cursos, en caso de que se mantenga el error, por favor comunicarse con desarrolladorg3@gmail.com';
                throw new \RuntimeException($message);
            }

            //Delete the other registers
            foreach ($deleteData as $deleteItem) {
                DB::table('group_user')
                    ->where('group_id', '=', $deleteItem['group_id'])
                    ->where('user_id', '=', $deleteItem['user_id'])
                    ->where('academic_period_id', '=', $deleteItem['academic_period_id'])
                    ->delete();
            }
        }


}
