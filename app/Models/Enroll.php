<?php

namespace App\Models;

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

    public static function createOrUpdateFromArray(array $enrolls, int $academicPeriodId): void
    {
        //Create users
        $emails = User::updateOrCreateFromArrayAndGetEmails($enrolls);

        $users = DB::table('users')->whereIn('email', $emails)->select('id', 'email')->get()->toArray();
        $userEmailAndId = array_reduce($users, static function ($result, $user) {
            $result[$user->email] = $user->id;
            return $result;
        }, []);
        $upsertData = [];
        foreach ($enrolls as $enroll) {
            $upsertData[] = [
                'user_id' => $userEmailAndId[$enroll["email"]],
                'group_id' => $enroll['group_id'],
                'has_answer' => 0,
                'academic_period_id' => $academicPeriodId
            ];
        }

        /* foreach ($upsertData as $item) {
             try {
                 self::updateOrCreate([
                     'group_id' => $item['group_id'],
                     'user_id' => $item['user_id'],
                     'academic_period_id' => $item['academic_period_id']
                 ],[
                     'has_answer' => 0
                 ]);
             } catch (\Exception $e) {
                 dd('Encontre este error :'.$e->getMessage());
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


    }


}
