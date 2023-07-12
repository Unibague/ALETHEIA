<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyServiceAreaRequest;
use App\Models\AssessmentPeriod;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceAreaRequest;
use App\Http\Requests\UpdateServiceAreaRequest;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
    public function index(): JsonResponse
    {
        return response()->json(ServiceArea::getCurrentServiceAreas());
    }



    public function edit($serviceArea): JsonResponse
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $serviceArea = DB::table('service_areas')->where('code', '=', $serviceArea)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get();

        return Inertia::render('ServiceAreas/ManageServiceArea', ['serviceArea' => $serviceArea]);
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


    public function getServiceAreasResults(): JsonResponse{

        return response()->json(ServiceArea::getServiceAreasResults());

    }

    public function getServiceAreasResultsPerGroup(): JsonResponse{

        return response()->json(ServiceArea::getServiceAreasResultsPerGroup());

    }

    public function getServiceAreasTeachersWithResults(): JsonResponse{

        return response()->json(ServiceArea::getServiceAreasTeachersWithResults());

    }


}
