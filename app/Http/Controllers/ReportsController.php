<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\FormAnswers;
use App\Models\Reports;
use App\Models\ServiceArea;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{

    public function index()
    {
        return Inertia::render('Reports/CompleteServiceAreasResults');
    }

    public function downloadTeachingPDF(Request $request)
    {
        $data = $request->all();
        $assessment = $data["assessment"];
        $headers = $data["headers"];
        $overallAverageChart = $data['overallAverageChart'];
        $satisfactionChart = $data['satisfactionChart'];
        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('id', '=', $assessment['assessment_period_id'])->first()->name;
        $reportType = $data["reportType"];

        $pdf = Pdf::loadView('teachingReport', [
            'assessment' => $assessment,
            'teacherName' => $assessment["teacher_name"],
            'openEndedAnswers' => $assessment["open_ended_answers"],
            'headers' => $headers,
            'overallAverageChart' => $overallAverageChart,
            'satisfactionChart' => $satisfactionChart,
            'assessmentPeriodName' => $assessmentPeriodName,
            'reportType' => $reportType,
        ])->setPaper('a4', 'landscape'); // Here you set the paper size and orientation to landscape.

        return $pdf->download('aletheia_reporte_docencia_' . Carbon::now()->toDateTimeString() . '.pdf');
    }


    public function downloadFacultyPDF(Request $request)
    {
        $data = $request->all();
        $faculty = $data["faculty"];
        $headers = $data["headers"];
        $overallAverageChart = $data['overallAverageChart'];
        $satisfactionChart = $data['satisfactionChart'];
        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('id', '=', $faculty['assessment_period_id'])->first()->name;

        dd($headers);

        $pdf = Pdf::loadView('facultyReport', [
            'faculty' => $faculty,
            'facultyName' => $faculty["faculty_name"],
            'headers' => $headers,
            'overallAverageChart' => $overallAverageChart,
            'satisfactionChart' => $satisfactionChart,
            'assessmentPeriodName' => $assessmentPeriodName,
        ])->setPaper('a4', 'landscape'); // Here you set the paper size and orientation to landscape.

        return $pdf->download('aletheia_reporte_docencia_' . Carbon::now()->toDateTimeString() . '.pdf');
    }


    public function getGroupResults(Request $request)
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        $areLegacyResults = false;
        $serviceAreas = $request->input('serviceAreas');
        $serviceAreasId = [];

        foreach ($serviceAreas as $serviceArea) {
            $serviceAreasId[] = $serviceArea['code'];
        }

        $groupResults = DB::table('group_results as gr')
            ->select(['u.name as teacher_name', 'u.id as teacher_id',
                'gr.assessment_period_id', 'gr.hour_type',
                'g.name as group_name', 'g.group as group_number', 'sa.name as service_area_name', 'sa.code as service_area_code',
                'gr.competences_average', 'gr.open_ended_answers',
                'gr.overall_average', 'gr.students_amount_reviewers as reviewers', 'gr.students_amount_on_group as total_students'])
            ->join('users as u', 'gr.teacher_id', '=', 'u.id')
            ->join('groups as g', 'gr.group_id', '=', 'g.group_id')
            ->join('service_areas as sa', 'gr.service_area_code', '=', 'sa.code')
            ->where('gr.assessment_period_id', '=', $assessmentPeriodId)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->whereIn('sa.code', $serviceAreasId)
            ->get();

        // Manually decode the JSON columns
        $groupResults = $groupResults->map(function ($result) {
            $result->competences_average = json_decode($result->competences_average);
            $result->open_ended_answers = json_decode($result->open_ended_answers);
            return $result;
        });

        if($assessmentPeriodId < 6){
            $areLegacyResults = true;
        }

        $groupResults = Reports::mapGroupsResultsToFrontEndStructure($groupResults, $areLegacyResults);



        return response()->json($groupResults);
    }

    public function getServiceAreaResults(Request $request)
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        $areLegacyResults = false;


        $serviceAreas = $request->input('serviceAreas');
        $serviceAreasId = [];

        foreach ($serviceAreas as $serviceArea) {
            $serviceAreasId[] = $serviceArea['code'];
        }

        $serviceAreaResults = DB::table('teachers_service_areas_results as tsar')
            ->select(['u.name as teacher_name', 'sa.name as service_area_name', 'sa.code as service_area_code',
                'tsar.assessment_period_id', 'tsar.hour_type',
                'tsar.competences_average', 'tsar.open_ended_answers',
                'tsar.overall_average', 'tsar.aggregate_students_amount_reviewers as reviewers',
                'tsar.aggregate_students_amount_on_service_area as total_students',
                'u.id as teacher_id'])
            ->join('users as u', 'tsar.teacher_id', '=', 'u.id')
            ->join('service_areas as sa', 'tsar.service_area_code', '=', 'sa.code')
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->where('tsar.assessment_period_id', '=', $assessmentPeriodId)
            ->whereIn('sa.code', $serviceAreasId)
            ->get();

        // Manually decode the JSON columns
        $serviceAreaResults = $serviceAreaResults->map(function ($result) {
            $result->competences_average = json_decode($result->competences_average);
            $result->open_ended_answers = json_decode($result->open_ended_answers);
            return $result;
        });

        if($assessmentPeriodId < 6){
            $areLegacyResults = true;
        }

        $serviceAreaResults = Reports::mapServiceAreasResultsToFrontEndStructure($serviceAreaResults,  $areLegacyResults);

        return response()->json($serviceAreaResults);
    }


    public function getFinalTeachingResults(Request $request)
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        $finalTeachingResults = DB::table('teachers_students_perspectives as tsp')
            ->select(['u.name as teacher_name', 'tsp.hour_type',
                'tsp.assessment_period_id', 'tsp.open_ended_answers',
                'tsp.competences_average', 'tsp.overall_average', 'tsp.aggregate_students_amount_reviewers as reviewers',
                'tsp.aggregate_students_amount_on_360_groups as total_students',
                'u.id as teacher_id'])
            ->join('users as u', 'tsp.teacher_id', '=', 'u.id')
            ->where('tsp.assessment_period_id', '=', $assessmentPeriodId)
            ->get();

        // Manually decode the JSON columns
        $finalTeachingResults = $finalTeachingResults->map(function ($result) {
            $result->competences_average = json_decode($result->competences_average);
            $result->open_ended_answers = json_decode($result->open_ended_answers);
            return $result;
        });

        $finalTeachingResults = Reports::mapFinalTeachingResultsToFrontEndStructure($finalTeachingResults);

        return response()->json($finalTeachingResults);
    }

    public function getFacultyResults(Request $request){

        $assessmentPeriodId = $request->input('assessmentPeriodId');
        $areLegacyResults = false;

        $facultiesResults = DB::table('faculty_results as fr')
            ->select(['f.name as faculty_name','f.id as faculty_id',
                'fr.assessment_period_id',
                'fr.competences_average',
                'fr.hour_type',
                'fr.overall_average', 'fr.students_reviewers as reviewers',
                'fr.students_enrolled as total_students'])
            ->join('faculties as f', 'fr.faculty_id', '=', 'f.id')
            ->where('fr.assessment_period_id', '=', $assessmentPeriodId)
            ->get();

        // Manually decode the JSON columns
        $facultiesResults = $facultiesResults->map(function ($result) {
            $result->competences_average = json_decode($result->competences_average);
            return $result;
        });

        if($assessmentPeriodId < 6){
            $areLegacyResults = true;
        }

        $facultiesResults = Reports::mapFacultiesResultsToFrontEndStructure($facultiesResults,  $areLegacyResults);

        return response()->json($facultiesResults);
    }

    public function indexServiceAreaResults()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $user = auth()->user();
        $token = csrf_token();

        if ($user->role()->name === "administrador") {
            return Inertia::render('Reports/ServiceAreas', ['token' => $token]);
        }

        if ($user->role()->name == "Jefe de Área de Servicio") {
            $userId = auth()->user()->id;
            $serviceAreas = DB::table('service_area_user')->where('user_id', '=', $userId)
                ->where('service_area_user.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('service_areas.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->join('service_areas', 'service_area_user.service_area_code', '=', 'service_areas.code')->get();

            if (count($serviceAreas) > 0) {
                return Inertia::render('Reports/ServiceAreas', ['serviceAreasFromProps' => $serviceAreas]);
            }
            return Inertia::render('Reports/ServiceAreas', ['serviceAreas' => []]);
        }
    }

    public function indexLegacyGroupResults()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $user = auth()->user();
        $token = csrf_token();

        if ($user->role()->name === "administrador") {
            return Inertia::render('Reports/LegacyReports', ['token' => $token]);
        }

        if ($user->role()->name == "Jefe de Área de Servicio") {
            $userId = auth()->user()->id;
            $serviceAreas = DB::table('service_area_user')->where('user_id', '=', $userId)
                ->where('service_area_user.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('service_areas.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->join('service_areas', 'service_area_user.service_area_code', '=', 'service_areas.code')->get();

            if (count($serviceAreas) > 0) {
                return Inertia::render('Reports/LegacyReports', ['serviceAreasFromProps' => $serviceAreas]);
            }
            return Inertia::render('Reports/LegacyReports', ['serviceAreas' => []]);
        }
    }


    public function downloadPDF($chartInfo, $teacherResults)
    {

        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('active', '=', 1)->first()->name;
        $teacherResults = json_decode($teacherResults);
        $chart = json_decode($chartInfo);
        $labels = $chart->data->labels;
        $teacherName = strtolower($teacherResults[0]->name);
        $chart = urlencode(json_encode($chart));

        return view('report', compact('assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));
    }


//    public function downloadServiceAreaPDF($chartInfo, $teacherResults){
//
//        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('active', '=', 1)->first()->name;
//        $teacherResults = json_decode($teacherResults);
//
//  /*      dd($teacherResults);*/
//
//        $chart = json_decode($chartInfo);
//        $labels = $chart->data->labels;
//
//        $teacherName = strtolower($teacherResults[0]->name);
//        $chart = urlencode(json_encode($chart));
//
//        if(isset($teacherResults[0]->group_id)){
//            /*dd($teacherResults);*/
//            return view('reportServiceAreaGroups', compact( 'assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));
//
//        }
//        return view('reportServiceArea', compact( 'assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName'));
//    }


    public function download360Report(Request $request)
    {

        $assessmentPeriodId = $request->input('assessmentPeriodId');
        if ($assessmentPeriodId == null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('id', '=', $assessmentPeriodId)->first()->name;
        $teacherId = $request->input('teacherId');
        $teacherResults = $request->input('teacherResults');
        $teacherResults = json_decode($teacherResults);

        //Now let's retrieve the group results from teacher to show after the graph on the report
        $groupsResults = DB::table('group_results as gr')->select(['g.name as group_name', 'g.group as group_number',
            'gr.first_final_competence_average as first_competence_average',
            'gr.second_final_competence_average as second_competence_average',
            'gr.third_final_competence_average as third_competence_average',
            'gr.fourth_final_competence_average as fourth_competence_average',
            'gr.fifth_final_competence_average as fifth_competence_average',
            'gr.sixth_final_competence_average as sixth_competence_average',
            'students_amount_reviewers', 'students_amount_on_group'])->where('gr.teacher_id', '=', $teacherId)
            ->join('groups as g', 'g.group_id', '=', 'gr.group_id')
            ->where('gr.assessment_period_id', '=', $assessmentPeriodId)->orderBy('g.name', 'DESC')->get();

        //Now retrieve the open answers from the groups
        $openAnswersFromStudents = FormAnswers::getOpenAnswersFromStudents360Report($teacherId, $assessmentPeriodId);

//        dd($openAnswersFromStudents);
        //Now retrieve the open answers from the colleagues, boss and autoAssessment
        $openAnswersFromTeachers = FormAnswers::getOpenAnswersFromColleagues($teacherId, $assessmentPeriodId);


        $chartInfo = $request->input('chart');
        $chart = json_decode($chartInfo);
        $chart->options->scales->yAxes = [];
        $ticks = (object)['ticks' => (object)['min' => 0, 'stepSize' => 1]];
        $chart->options->scales->yAxes [] = $ticks;
        $labels = $chart->data->labels;
        $teacherName = strtolower($teacherResults[0]->name);
        $chart = urlencode(json_encode($chart));
        $timestamp = Carbon::now();

        return view('report360', compact('assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName', 'timestamp',
            'groupsResults', 'openAnswersFromStudents', 'openAnswersFromTeachers'));
    }


    public function downloadServiceAreasReport(Request $request)
    {

        $assessmentPeriodId = $request->input('assessmentPeriodId');
        if ($assessmentPeriodId == null) {
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('id', '=', $assessmentPeriodId)->first()->name;
        $teacherId = $request->input('teacherId');
        $teacherResults = $request->input('teacherResults');
        $teacherResults = json_decode($teacherResults);
        $teacherName = strtolower($teacherResults[0]->name);
        $chartInfo = $request->input('chart');
        $chart = json_decode($chartInfo);
        $chart->options->scales->yAxes = [];
        $ticks = (object)['ticks' => (object)['min' => 0, 'stepSize' => 1]];
        $chart->options->scales->yAxes [] = $ticks;

        $labels = $chart->data->labels;
        $chart = urlencode(json_encode($chart));
        $timestamp = Carbon::now();

        $serviceAreasCodes = array_unique(array_column($teacherResults, 'service_area_code'));
        $serviceAreas = DB::table('service_areas as sa')->whereIn('sa.code', $serviceAreasCodes)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)->get();

        //Si el reporte es por grupo individual
        if (isset($teacherResults[0]->group_id)) {

            $serviceAreasCodes = array_unique(array_column($teacherResults, 'service_area_code'));
            $serviceAreas = DB::table('service_areas as sa')->whereIn('sa.code', $serviceAreasCodes)
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)->get();

            $serviceAreasGroups = [];

            foreach ($serviceAreas as $serviceArea) {
                $groupsResults = DB::table('group_results as gr')->select(['g.name as group_name', 'g.group as group_number',
                    'gr.first_final_competence_average as first_competence_average',
                    'gr.second_final_competence_average as second_competence_average',
                    'gr.third_final_competence_average as third_competence_average',
                    'gr.fourth_final_competence_average as fourth_competence_average',
                    'gr.fifth_final_competence_average as fifth_competence_average',
                    'gr.sixth_final_competence_average as sixth_competence_average',
                    'students_amount_reviewers', 'students_amount_on_group', 'sa.name'])->where('gr.teacher_id', '=', $teacherId)
                    ->join('groups as g', 'g.group_id', '=', 'gr.group_id')
                    ->join('service_areas as sa', 'g.service_area_code', '=', 'sa.code')
                    ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                    ->where('gr.assessment_period_id', '=', $assessmentPeriodId)->where('g.service_area_code', '=', $serviceArea->code)
                    ->orderBy('g.name', 'DESC')->get();
                $serviceAreasGroups [] = $groupsResults;
            }

            //Now retrieve the open answers from the groups
            $openAnswersFromStudents = FormAnswers::getOpenAnswersFromStudentsServiceAreasReport($teacherId, $serviceAreas, $assessmentPeriodId);


            return view('reportServiceAreaGroups', compact('assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName',
                'timestamp', 'serviceAreasGroups', 'openAnswersFromStudents'));
        }

        //Si el reporte es por área(s) de servicio
        //Now let's retrieve the group results from teacher on every serviceArea
        $serviceAreasCodes = array_unique(array_column($teacherResults, 'service_area_code'));
        $serviceAreas = DB::table('service_areas as sa')->whereIn('sa.code', $serviceAreasCodes)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)->get();

        $serviceAreasGroups = [];

        foreach ($serviceAreas as $serviceArea) {
            $groupsResults = DB::table('group_results as gr')->select(['g.name as group_name', 'g.group as group_number',
                'gr.first_final_competence_average as first_competence_average',
                'gr.second_final_competence_average as second_competence_average',
                'gr.third_final_competence_average as third_competence_average',
                'gr.fourth_final_competence_average as fourth_competence_average',
                'gr.fifth_final_competence_average as fifth_competence_average',
                'gr.sixth_final_competence_average as sixth_competence_average',
                'students_amount_reviewers', 'students_amount_on_group', 'sa.name'])->where('gr.teacher_id', '=', $teacherId)
                ->join('groups as g', 'g.group_id', '=', 'gr.group_id')
                ->join('service_areas as sa', 'g.service_area_code', '=', 'sa.code')
                ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
                ->where('gr.assessment_period_id', '=', $assessmentPeriodId)->where('g.service_area_code', '=', $serviceArea->code)
                ->orderBy('g.name', 'DESC')->get();
            $serviceAreasGroups [] = $groupsResults;
        }

        //Now retrieve the open answers from the groups
        $openAnswersFromStudents = FormAnswers::getOpenAnswersFromStudentsServiceAreasReport($teacherId, $serviceAreas, $assessmentPeriodId);


        return view('reportServiceArea', compact('assessmentPeriodName', 'chart', 'teacherResults', 'labels', 'teacherName',
            'timestamp', 'serviceAreasGroups', 'openAnswersFromStudents'));
    }


    public function getReminders()
    {

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $reminders = DB::table('assessment_reminder')->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get();
        return response()->json($reminders);

    }

    public function updateReminder(Request $request)
    {

        $reminderToEdit = $request->input('reminderToEdit');
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        DB::table('assessment_reminder')->updateOrInsert(
            ['assessment_period_id' => $activeAssessmentPeriodId,
                'send_reminder_before' => $reminderToEdit['send_reminder_before']],
            ['reminder_name' => $reminderToEdit['reminder_name'],
                'roles' => $reminderToEdit['roles'],
                'days_in_advance' => $reminderToEdit['days_in_advance']]);

        return response()->json(['message' => 'Recordatorio actualizado correctamente']);
    }

}
