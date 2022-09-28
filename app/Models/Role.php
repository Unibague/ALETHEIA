<?php

namespace App\Models;

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
    protected $guarded = [];
    use HasFactory;


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->BelongsToMany(User::class);

    }

    public static function getRoleNumber(string $roleName): int
    {
        $selectedRole = self::where('name', '=', $roleName)->first();
        if ($selectedRole === null) {
            throw new \RuntimeException('El rol buscado no existe');
        }
        return $selectedRole->customId;
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
                'role_id' => $roleId
            ]
        );
    }


}
