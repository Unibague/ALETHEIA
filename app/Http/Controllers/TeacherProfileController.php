<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeTeacherStatusRequest;
use App\Models\AssessmentPeriod;
use App\Models\TeacherProfile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Ospina\CurlCobain\CurlCobain;

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
        return response()->json(TeacherProfile::where('assessment_period_id', '=', $actualAssessmentPeriod->id)->with('user')->get()->sortBy('user.name')->values()->all());
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
        $endpoint = 'teachers';

        try {
            $teachers = $this->makeRequest($endpoint);

        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        //Iterate over received data and create the academic period
        $assessment_period_id = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        foreach ($teachers as $teacher) {
            try {
                $user = User::firstOrCreate(['email' => $teacher->email], ['name' => $teacher->name, 'password' => Hash::make($teacher->identification_number . $teacher->email)]);
                //$unit = Unit::where('code', '=', $teacher->unit)->firstOrFail();
                $unit = (object)['id' => 1];
            } catch (ModelNotFoundException $e) {
                continue;
            }

            TeacherProfile::updateOrCreate(
                [
                    'identification_number' => $teacher->identification_number,
                    'user_id' => $user->id
                ],
                [
                    'unit_id' => $unit->id,
                    'position' => $teacher->position,
                    //'teaching_ladder' => $teacher->teaching_ladder,
                    'teaching_ladder' => 'ninguno',
                    'employee_type' => $teacher->employee_type,
                    'status' => 'activo',
                    'assessment_period_id' => $assessment_period_id
                ]);
        }
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);

    }
}
