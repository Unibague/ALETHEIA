<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyServiceAreaRequest;
use App\Models\AssessmentPeriod;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceAreaRequest;
use App\Http\Requests\UpdateServiceAreaRequest;
use Illuminate\Http\JsonResponse;
use Ospina\CurlCobain\CurlCobain;

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
}
