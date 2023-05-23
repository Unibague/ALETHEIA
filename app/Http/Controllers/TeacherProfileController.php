<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\ChangeTeacherStatusRequest;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\TeacherProfile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Ospina\CurlCobain\CurlCobain;
use SebastianBergmann\LinesOfCode\RuntimeException;

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
        return response()->json(TeacherProfile::where('assessment_period_id', '=', $actualAssessmentPeriod->id)
            ->with('user')->get()->sortBy('user.name')->values()->all());
    }


    public function viewTeacherAssessments(): \Inertia\Response
    {

        $user = auth()->user();

        return Inertia::render('Teachers/Assessments',
            ['user' => $user]);

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


    public function sync(): JsonResponse
    {
        try {

            $academicPeriodsSeparatedByComas = AcademicPeriod::getCurrentAcademicPeriodsByCommas();
            $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod();
            $suitableTeachingLadders = $activeAssessmentPeriod->getSuitableTeachingLadders();
            $teachers = AtlanteProvider::get('teachers', [
                'periods' => $academicPeriodsSeparatedByComas,
            ], true);


            $finalTeachers = [];
            foreach ($teachers as $teacher){

               //Traerse profesores que tengan escalafon activo para evaluacion y los que sean DTC
                if(in_array($teacher['teaching_ladder'],$suitableTeachingLadders, false)
                   && $teacher['email'] != ""){

                    $finalTeachers [] = $teacher;

                }
            }

    /*        foreach ($teachers as $teacher){

                //Traerse profesores que tengan escalafon activo para evaluacion y los que sean DTC
                if(in_array($teacher['teaching_ladder'],$suitableTeachingLadders, false)
                    && $teacher['employee_type'] == 'DTC'&& $teacher['email'] != ""){

                    $finalTeachers [] = $teacher;

                }
            }*/

        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()], 400);
        }
        try {
            TeacherProfile::createOrUpdateFromArray($finalTeachers);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'Los docentes se han sincronizado exitosamente']);
    }


    public function getSuitableList(): JsonResponse

    {
        $actualAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod();
        return response()->json(TeacherProfile::where('assessment_period_id', '=', $actualAssessmentPeriod->id)
            ->whereIn('employee_type', ['DTC', 'ESI'])->with('user')->get()->sortBy('user.name')->values()->all());
    }




}
