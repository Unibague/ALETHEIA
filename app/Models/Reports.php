<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class Reports extends Model
{
    use HasFactory;


    public static function downloadPDF($teacherRolesAssessments){




    }

    public function engineering(): Response
    {
        $token = csrf_token();

        return Inertia::render('Reports/Ingenieria', ['token' => $token]);
    }




}
