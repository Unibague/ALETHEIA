<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unity extends Model
{
    use HasFactory;
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
    return $this->hasMany(Form::class);
}
    public function unityAssessment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnityAssessment::class);
    }
    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

}
