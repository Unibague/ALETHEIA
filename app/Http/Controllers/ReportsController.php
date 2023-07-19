<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\Reports;
use App\Models\ServiceArea;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{

    public function index()
    {
        return Inertia::render('Reports/CompleteServiceAreasResults');
    }

    public function show360Assessment(){

        $user = auth()->user();

        $units = [];

        if ($user->role()->name == "Resultados Evaluación"){

            //Ingenierías
            //Meisel y William
            if($user->id == 240 || $user->id == 270){
                $units = ['025-1', '014-1','028-1', '018-1', '022-1', '105-1','104-1'];
            }

            //Luisa Gallo
            if($user->id == 159){
                $units = ['025-1', '014-1'];
            }

            //Carolina Saavedra
            if($user->id == 144){
                $units = ['018-1', '022-1'];
            }

            //Del Rio
            if($user->id == 31){
                $units = ['028-1'];
            }
//Ingenierías



//Facultad Ciencias Naturales
            //Nyckyertt
            if($user->id == 183){
                $units = ['083-1', '007-1'];
            }

            //Bilma
            if($user->id == 155){
                $units = ['083-1'];
            }
//Facultad Ciencias Naturales



//Facultad Ciencias Económicas

            //Paola Henoe
            if($user->id == 192){
                $units = ['103-1', '106-1', '021-1', '027-1', '013-1' ,'017-1', '092-1', '024-1'];
            }

            //Alejandro Gutiérrez

            if($user->id == 215){
                $units = ['092-1', '024-1', '013-1', '017-1', '103-1'];
            }

            //Paula Lorena Rodríguez

            if($user->id == 154){
                $units = ['021-1', '027-1', '106-1'];
            }

//Facultad Ciencias Económicas


//Facultad Derecho

            //Alexander Cruz
            if($user->id == 122957){
                $units = ['008-1', '012-1'];
            }

            //Cesar Barrera
            if($user->id == 98){
                $units = ['012-1'];
            }


//Facultad Derecho


//Facultad Humanidades

            //Daniel Lopera
            if($user->id == 218){
                $units = ['011-1', '110-1', '111-1'];
            }

            //Sandra Abella
            if($user->id == 164){
                $units = ['011-1', '110-1', '111-1'];
            }

            //Torrente
            if($user->id == 46){
                $units = ['110-1'];
            }

            //Tatiana Ávila
            if($user->id == 40){
                $units = ['111-1'];
            }

//Proyectos Especiales

            //Hernan Lopez-Garay
            if($user->id == 263077){
                $units = ['087-1'];
            }
//Proyectos Especiales



//Centro de Idiomas

            //Fredy
            if($user->id == 122927){
                $units = ['036-1'];
            }

            //Maria Beatriz
            if($user->id == 121316){
                $units = ['036-1'];
            }

//Centro de Idiomas

            return Inertia::render('Reports/Complete360AssessmentResults', ['propsUnits' => $units]);

        }

        else{

            return Inertia::render('Reports/Complete360AssessmentResults');

        }

    }

    public function showServiceAreasAssessment(){

        $user = auth()->user();

        if($user->role()->name == "Jefe de Área de Servicio") {

            $userId = auth()->user()->id;

            $serviceAreasArray = [];

            $serviceAreas = DB::table('service_area_user')->where('user_id', '=', $userId)->get();

            if(count($serviceAreas) > 0){

                foreach ($serviceAreas as $serviceArea){

                    $serviceAreasArray [] = $serviceArea->service_area_code;

                }

                return Inertia::render('Reports/CompleteServiceAreasResults', ['propsServiceAreas' => $serviceAreasArray]);
            }
        }

        return Inertia::render('Reports/CompleteServiceAreasResults');

    }


    public function downloadPDF($chartInfo, $teacherResults){

        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('active', '=', 1)->first()->name;
        $teacherResults = json_decode($teacherResults);
        $chart = json_decode($chartInfo);
        $labels = $chart->data->labels;
        $teacherName = strtolower($teacherResults[0]->name);
        $chart = urlencode(json_encode($chart));

        /*        dd($chart);*/
/*       $pdf = Pdf::loadView('report', compact( 'assessmentPeriodName', 'chart', 'teacherResults'));*//*
        return Pdf::loadView('report', compact( 'assessmentPeriodName', 'chart'))->stream('Reporte cronos.pdf');*/
        return view('report', compact( 'assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));

    }


    public function downloadServiceAreaPDF($chartInfo, $teacherResults){

        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('active', '=', 1)->first()->name;
        $teacherResults = json_decode($teacherResults);

  /*      dd($teacherResults);*/

        $chart = json_decode($chartInfo);
        $labels = $chart->data->labels;

        $teacherName = strtolower($teacherResults[0]->name);
        $chart = urlencode(json_encode($chart));

        if(isset($teacherResults[0]->group_id)){
            /*dd($teacherResults);*/
            return view('reportServiceAreaGroups', compact( 'assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));

        }
        return view('reportServiceArea', compact( 'assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));
    }







}
