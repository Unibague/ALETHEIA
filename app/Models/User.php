<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormAnswers[] $formAnswers
 * @property-read int|null $form_answers_count
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $teacherGroups
 * @property-read int|null $teacher_groups_count
 * @property-read \App\Models\TeacherProfile|null $teacherProfile
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Unit[] $unities
 * @property-read int|null $unities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UnityAssessment[] $unityAssessments
 * @property-read int|null $unity_assessments_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Unit[] $units
 * @property-read int|null $units_count
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public static function updateOrCreateFromArrayAndGetUsers(array $data): array
    {
        $now = Carbon::now()->toDateTimeString();
        $upsertData = [];
        $emails = [];
        foreach ($data as $user) {
            $emails[] = $user['email'];
            $upsertData[] = ['email' => $user['email'], 'name' => $user['name'], 'password' => 'automatic_generate_password', 'created_at' => $now, 'updated_at' => $now];
        }
        //Chunk in 1000 in order to make a safer query
        foreach (array_chunk($upsertData, 1000) as $sqlData) {
            DB::table('users')->upsert($sqlData, 'email', null);
        }


        $uniqueEmails = array_unique($emails);
        $users = DB::table('users')->whereIn('email', $uniqueEmails)->select('id', 'email')->get()->toArray();
        $roleUpsertData = [];
        $roleId = Role::where('name', '=', 'estudiante')->firstOrFail()->id;

        foreach ($users as $user) {
            $roleUpsertData[] = ['user_id' => $user->id, 'role_id' => $roleId];
        }
        foreach (array_chunk($roleUpsertData, 1000) as $sqlData) {
            DB::table('role_user')->upsert($sqlData, ['user_id'], ['role_id']);
        }

        return $users;
    }

    public function role()
    {
        $user = $this;
        $actualRole = session('role');
        //Check if is still valid
        $userRoles = $user->roles;
        foreach ($userRoles as $role) {
            if ($actualRole === $role->id) {
                return $role;
            }
        }
        return (object)[
            'name' => 'no role',
            'customId' => 0
        ];
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->BelongsToMany(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        try {
            $roleNumber = Role::getRoleNumber($roleName);
        } catch (\RuntimeException $e) {
            return false;
        }
        return $this->role()->customId >= $roleNumber;
    }

    public function hasOneRole(): bool
    {
        return count($this->roles) === 1;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('administrador');

    }

    public function isStudent(): bool
    {
        return $this->hasRole('estudiante');
    }

    public function teacherProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    //TODO: Terminar
    public function unityAssessments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnityAssessment::class);
    }

    public function units(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class);
    }

    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $now = Carbon::now();
        $date = $now->toDateString();
        return $this->belongsToMany(Group::class,'group_user','user_id','group_id','id','group_id')->with(['teacher', 'academicPeriod'])
            ->wherePivotIn('academic_period_id', AcademicPeriod::getCurrentAcademicPeriodIds())
            ->join('academic_periods as ap','ap.id','=','group_user.academic_period_id')
            ->where('ap.students_start_date','<=',$date)
            ->where('ap.students_end_date','>=',$date)
            ->withPivot('has_answer');
    }

    public function teacherGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id', 'id');
    }
}
