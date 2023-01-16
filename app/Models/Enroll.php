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

    public static function createOrUpdateFromArray(array $enrolls, int $academicPeriodId): int
    {
        //Create users
        $emails = User::updateOrCreateFromArrayAndGetEmails($enrolls);

        $users = DB::table('users')->whereIn('email', $emails)->select('id', 'email')->get()->toArray();
        $userEmailAndId = array_reduce($users, static function ($result, $user) {
            $result[$user->email] = $user->id;
            return $result;
        }, []);

        $upsertData = [];
        $now = Carbon::now()->toDateTimeString();
        foreach ($enrolls as $enroll) {
            $upsertData[] = ['user_id' => $userEmailAndId[$enroll["email"]], 'group_id' => $enroll['group_id'], 'has_answer' => 0, 'academic_period_id' => $academicPeriodId, 'created_at' => $now, 'updated_at' => $now];
        }

        $total = 0;
        foreach (array_chunk($upsertData, 1000) as $sqlData) {
            $total += DB::table('group_user')->insertOrIgnore($sqlData);
        }
        return $total;
    }


}
