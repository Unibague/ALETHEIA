<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyServiceAreaRequest;
use App\Models\AssessmentPeriod;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceAreaRequest;
use App\Http\Requests\UpdateServiceAreaRequest;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Js;
use Inertia\Inertia;
use Ospina\CurlCobain\CurlCobain;
use Psy\Util\Json;


class ServiceAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(ServiceArea::getCurrentServiceAreas($assessmentPeriodId));
    }

    public function edit($serviceArea)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $serviceArea = DB::table('service_areas')->where('code', '=', $serviceArea)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->first();
        return Inertia::render('ServiceAreas/ManageServiceArea', ['serviceAreaCode' => $serviceArea]);
    }


    public function sync(): JsonResponse
    {
        try {
            $serviceAreas = AtlanteProvider::get('serviceAreas');
        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()]);
        }
        ServiceArea::createOrUpdateFromArray($serviceAreas);
        return response()->json(['message' => 'Las áreas de servicio se han sincronizado exitosamente']);
    }


    public function getServiceAreasResults(Request $request): JsonResponse{
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(ServiceArea::getServiceAreasResults($assessmentPeriodId));
    }

    public function getServiceAreasResultsPerGroup(Request $request): JsonResponse{
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(ServiceArea::getServiceAreasResultsPerGroup($assessmentPeriodId));
    }

    public function getServiceAreasTeachersWithResults(Request $request): JsonResponse{
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(ServiceArea::getServiceAreasTeachers($assessmentPeriodId));
        return response()->json(ServiceArea::getServiceAreasTeachersWithResults($assessmentPeriodId));
    }


    public function getServiceAreaAdmins($serviceAreaCode): JsonResponse{
        return response()->json(ServiceArea::getServiceAreaAdmins($serviceAreaCode));
    }

    public function getServiceAreasAdmins(\Request $request): JsonResponse{
        return response()->json(ServiceArea::getUsersAssignedToServiceAreas());
    }

    public function assignServiceAreaAdmin(\Illuminate\Http\Request $request): JsonResponse{
        $serviceAreaCode = $request->input('serviceAreaCode');
        $userId = $request->input('userId');
        return (ServiceArea::assignServiceAreaAdmin($serviceAreaCode, $userId));
    }

    public function deleteServiceAreaAdmin(\Illuminate\Http\Request $request): JsonResponse{
        $serviceAreaCode = $request->input('serviceAreaCode');
        $userId = $request->input('userId');
        return (ServiceArea::deleteServiceAreaAdmin($serviceAreaCode, $userId));
    }


    public function assignServiceAreas(Request $request){

        $userId = $request->get('userId');
        $serviceAreas = $request->input('serviceAreas');

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        try{
            DB::table('service_area_user')->where('user_id', $userId)
                ->where('assessment_period_id','=',$activeAssessmentPeriodId)->delete();

            foreach ($serviceAreas as $serviceArea) {
                DB::table('service_area_user')->insert([
                    'user_id' => $userId,
                    'service_area_code' => $serviceArea,
                    'role_id' => 10, // Asigna un rol si es necesario
                    'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            }

            return response()->json(['message' => "Áreas de servicio actualizadas correctamente"]);

        } catch (QueryException $exception){
            return response()->json(['message' => $exception->getMessage()], 400);
        }

    }

    public function deleteServiceAreasFromUser(Request $request){
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $userId = $request->get('userId');
        DB::table('service_area_user')->where('user_id', $userId)
            ->where('assessment_period_id','=',$activeAssessmentPeriodId)->delete();
        return response()->json(['message' => "Áreas de servicio desligadas del usuario correctamente"]);
    }
}
