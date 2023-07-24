<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property int $customId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCustomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    public $timestamps = true;
    protected $guarded = [];
    use HasFactory;


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->BelongsToMany(User::class);

    }

    public static function getTeacherRoleId(): int
    {
        $id = DB::table('roles')
            ->select('id')->where('name','docente')->pluck('id');

        $id = $id[0];

        return $id;

    }


    public static function getServiceAreaAdminRoleId(): int
    {
        $id = DB::table('roles')
            ->select('id')->where('name','Jefe de Área de Servicio')->first()->id;

        return $id;

    }

    public static function getStaffMemberRoleId(): int
    {
        $staffMemberRole = DB::table('roles')->select('id')->where('name','funcionario')->first();

        return $staffMemberRole->id;

    }


    public static function getUnitAdminRoleId(): int{

         $unitAdminRole = DB::table('roles')->select('id')->where('name','administrador de unidad')->first();

         return $unitAdminRole->id;

    }

    public static function getUnitBossRoleId(): int{

        $unitBossRole = DB::table('roles')->select('id')->where('name','jefe de profesor')->first();

        return $unitBossRole->id;

    }

    public static function getRoleIdByName(string $roleName): int
    {

        return DB::table('roles')->select('id')->where('name',$roleName)->first()->id;

    }

    public static function getRoleNumber(string $roleName): int
    {
        $selectedRole = self::where('name', '=', $roleName)->first();
        if ($selectedRole === null) {
            throw new \RuntimeException('El rol buscado no existe');
        }
        return $selectedRole->customId;
    }


    public static function getRoleNameByCustomId(int $roleCustomId): string
    {
        $roleName = self::where('customId', '=', $roleCustomId)->first();
        if ($roleName === null) {
            throw new \RuntimeException('El rol buscado no existe');
        }
        return $roleName->name;
    }



    public static function assignRoleToEmail(string $email, $roleName): void
    {
        $user = User::where('email', '=', $email)->first();
        if (!$user) {
            throw new \RuntimeException('El usuario indicado no existe');
        }
        $selectedRole = self::where('name', '=', $roleName)->first();
        if (!$selectedRole) {
            throw new \RuntimeException('El rol indicado no existe');
        }
        self::assignRole($user->id, $selectedRole->id);
    }

    public static function assignRole(int $userId, int $roleId): void
    {
        DB::table('role_user')->insert(
            [
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                'updated_at' => Carbon::now('GMT-5')->toDateTimeString(),
            ]
        );
    }


}
