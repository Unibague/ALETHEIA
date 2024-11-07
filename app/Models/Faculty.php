<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $guarded = [];



    public function serviceAreas()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        return $this->belongsToMany(ServiceArea::class, 'faculty_service_area', 'faculty_id', 'service_area_code', 'id', 'code')
            ->wherePivot('assessment_period_id', $activeAssessmentPeriodId) // Filter based on pivot column
            ->where('service_areas.assessment_period_id', $activeAssessmentPeriodId); // Extra filtering to account for composite PK
    }

//    public function serviceAreas()
//    {
//        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
//        return $this->belongsToMany(ServiceArea::class, 'faculty_service_area', 'faculty_id', 'service_area_code', 'id', 'code')
//            ->where('service_areas.assessment_period_id', $activeAssessmentPeriodId);
//    }


}
