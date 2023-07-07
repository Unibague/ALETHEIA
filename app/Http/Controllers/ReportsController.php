<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\Reports;
use App\Models\ServiceArea;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{

    public function index()
    {
        return Inertia::render('Reports/Index');
    }


    public function downloadPDF(Request $request){

        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('active', '=', 1)->first()->name;
        $chart = urlencode(json_encode($request->input('myChart')));
        $pdf = Pdf::loadView('report', compact( 'assessmentPeriodName', 'chart'));
        return view('report', compact( 'assessmentPeriodName', 'chart'));
    }


    /*      */

}
