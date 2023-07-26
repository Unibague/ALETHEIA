<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ResponseIdeal;
use App\Http\Requests\StoreResponseIdealRequest;
use App\Http\Requests\UpdateResponseIdealRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class ResponseIdealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreResponseIdealRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreResponseIdealRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function show(ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function edit(ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateResponseIdealRequest  $request
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateResponseIdealRequest $request, ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResponseIdeal $responseIdeal)
    {
        //
    }


    public function indexUnitResponseIdeals($unitId){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;


        $unit = Unit::where('identifier','=', $unitId)->where('assessment_period_id', '=', $activeAssessmentPeriodId)->first();

        return Inertia::render('ResponseIdeals/IndexUnit', ['unit' => $unit]);

    }


    public function editUnitTeachingLadderResponseIdeals($unitId, $teachingLadder){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;


        $unit = Unit::where('identifier','=', $unitId)->where('assessment_period_id', '=', $activeAssessmentPeriodId)->first();

        return Inertia::render('ResponseIdeals/IndexUnit', ['unit' => $unit]);

    }



    public function getUnitTeachingLadderResponseIdeals($unitId, $teachingLadder){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $responseIdeals = DB::table('response_ideals')->select(['response'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('teaching_ladder', '=', $teachingLadder)->where('unit_identifier', '=', $unitId)->first()->response;

        return response()->json(json_decode($responseIdeals));

    }


    public function delete(Request $request): JsonResponse{

        try {

            $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
            $teachingLadder = strtolower($request->input('teachingLadder'));
            $unitIdentifier = $request->input('unit');

            DB::table('response_ideals')->where('unit_identifier' , '=',  $unitIdentifier)
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->where('teaching_ladder', '=', $teachingLadder)->delete();

        } catch (JsonException $e) {

            return response()->json(['message' => 'Ha ocurrido un error guardando la información']);
        }

        return response()->json(['message' => 'Ideales de respuesta borrados exitosamente']);

    }


    public function upsertData(Request $request): JsonResponse{

        try {

            $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
            $teachingLadder = strtolower($request->input('teachingLadder'));
            $unitIdentifier = $request->input('unit');
            $competences = json_encode($request->input('competences'));
            $confirmation = $request->input('confirmation');


/*            dd($teachingLadder, $unitIdentifier, $confirmation);*/

            if ($confirmation){

                DB::table('response_ideals')->updateOrInsert
                (['unit_identifier' => $unitIdentifier,'assessment_period_id'  =>   $activeAssessmentPeriodId, 'teaching_ladder' => $teachingLadder],
                    ['response' => $competences,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()]);


                return response()->json(['message' => 'Ideales de respuesta guardados exitosamente']);

            }

/*            dd($teachingLadder, $unitIdentifier, $competences);*/

            $existingTeachingLadder =  DB::table('response_ideals')->where('unit_identifier' , '=',  $unitIdentifier)
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->where('teaching_ladder', '=', $teachingLadder)->first();

            if($existingTeachingLadder){

                return response()->json(['message' => 'Ya existe un ideal de respuesta asociado a este escalafón en esta unidad, ¿deseas sobreescribirlo?:'] , 500);

            }

            DB::table('response_ideals')->updateOrInsert
            (['unit_identifier' => $unitIdentifier,'assessment_period_id'  =>   $activeAssessmentPeriodId, 'teaching_ladder' => $teachingLadder],
                ['response' => $competences,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()]
            );


        } catch (JsonException $e) {

            return response()->json(['message' => 'Ha ocurrido un error guardando la información']);
        }

        return response()->json(['message' => 'Ideales de respuesta guardados exitosamente']);

    }


    public function getAllCompetences(): JsonResponse
    {

        return ResponseIdeal::getAllResponseIdeals();
    }


    public function getTeacherResponseIdeals(Request $request): JsonResponse
    {
        $teachingLadder = $request->input('teachingLadder');
        $unitIdentifier = $request->input('unitIdentifier');

        return ResponseIdeal::getTeacherResponseIdeals($teachingLadder, $unitIdentifier);

    }


    public function getUnitResponseIdeals($unitId): JsonResponse
    {
        return ResponseIdeal::getUnitResponseIdeals($unitId);
    }

}
