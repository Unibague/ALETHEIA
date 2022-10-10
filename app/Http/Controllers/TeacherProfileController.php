<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTeacherStatusRequest;
use App\Models\AssessmentPeriod;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 *
 */
class TeacherProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $actualAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod();
        return response()->json(TeacherProfile::where('assessment_period_id', '=', $actualAssessmentPeriod->id)->with('user')->get());
    }


    /**
     * @param ChangeTeacherStatusRequest $request
     * @param TeacherProfile $teacher
     * @return JsonResponse
     */
    public function changeStatus(ChangeTeacherStatusRequest $request, TeacherProfile $teacher): JsonResponse
    {
        $status = $request->input('status');
        $teacher->status = $status;
        $teacher->save();
        if ($status === 'suspendido') {
            return response()->json(['message' => 'El profesor se ha sido suspendido. Este dejará de sincronizarse y no podrá participar de la evaluación 360']);
        }
        return response()->json(['message' => "El estado del profesor ha sido cambiado a $status."]);
    }

    public function sync()
    {
        $url = 'http://integra.unibague.edu.co/teachers';
        $curl = new CurlCobain($url);
        $curl->setQueryParamsAsArray([
            'year' => '23',
            'api_token' => env('MIDDLEWARE_API_TOKEN')
        ]);
        $curl->setQueryParam('year', '23');
        $request = $curl->makeRequest();
        try {
            $teachers = json_decode($request, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        //Iterate over received data and create the academic period
        foreach ($teachers as $teacher) {
            $user = User::where('email', $teacher['email']);
            TeacherProfile::updateOrCreate(
                [
                    'identification_number' => $teacher->identification_number
                ],
                [
                    'description' => $teacher->description,
                    'class_start_date' => $teacher->class_start_date,
                    'class_end_date' => $teacher->class_end_date,
                ]);
        }
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);

    }
}
