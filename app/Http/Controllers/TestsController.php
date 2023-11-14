<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestPreviewRequest;
use App\Models\Form;
use App\Models\FormAnswers;
use App\Models\Test;
use App\Models\UnityAssessment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Test::getUserTests());
    }


    public function indexTeacherAutoTest()
    {
        $userId = auth()->user()->id;
        $teacher = json_decode(UnityAssessment::getAutoAssessmentFromTeacher($userId));
        return response()->json(Test::getUserTests($teacher, 'autoevaluación'));
    }



    public function indexTeacherPeerTests()
    {
        $userId = auth()->user()->id;
        $peers = json_decode(UnityAssessment::getPeerAssessmentsFromTeacher($userId));
        return response()->json(Test::getUserTests($peers, 'par'));
    }


    public function indexTeacherBossTests(Request $request)
    {
        $userId = auth()->user()->id;
        $subordinates = json_decode(UnityAssessment::getBossAssessmentsFromTeacher($userId));
        return response()->json(Test::getUserTests($subordinates, 'jefe'));
    }



    public function indexView(): Response
    {
        $token = csrf_token();
        return Inertia::render('Tests/Index', ['token' => $token]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function store(Request $request): JsonResponse
    {
        $form = Form::findOrFail($request->input('form_id'));

        if ($form->type === 'estudiantes') {
            FormAnswers::createStudentFormFromRequest($request, $form);
        }

        if ($form->type === 'otros'){
            FormAnswers::createTeacherFormFromRequest($request, $form);
        }

        return response()->json(['messages' => 'Formulario diligenciado exitosamente. Serás redirigido a la página de inicio']);
    }

    /**
     * Student request for attempting to start the test.
     *
     * @param Request $request
     * @param int $testId
     * @return Application|ResponseFactory|\Illuminate\Http\Response|Response
     */
    public function startTest(Request $request, int $testId)
    {
        $data = json_decode($request->input('data')); //parse data
        $test = Form::findOrFail($testId);
        if ($data->test->type == "estudiantes") {
            if ($data->pivot->has_answer === 1) {
                return response('Ya has contestado esta evaluación', 401);
            }
            return Inertia::render('Tests/Show', ['test' => $test, 'group' => ['id' => $data->group_id, 'name' => $data->name], 'teacher' => $data->teacher, 'canSend' => true]);
        }

        if ($data->test->type == "otros") {

            if ($data->pending === 0) {
                return response('Ya has contestado esta evaluación', 401);
            }
            return Inertia::render('Tests/ShowTeacherTest', ['test' => $test, 'teacher' => $data, 'canSend' => true]);
        }
    }

    public function preview(TestPreviewRequest $request, int $testId): Response
    {
        $test = Form::findOrFail($testId);
        return Inertia::render('Tests/Show', ['test' => $test, 'group' => ['id' => 'No. grupo', 'name' => 'Nombre del docente'], 'teacher' => (object)[], 'canSend' => false]);
    }

}
