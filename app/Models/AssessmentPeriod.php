<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentPeriod extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function academicPeriods(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AcademicPeriod::class);
    }

    public function teacher_profiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function unities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unity::class);
    }

    public function serviceArea(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user', 'assessment_period_id', 'group_id');
    }
}
