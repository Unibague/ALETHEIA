<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormAnswers;
use App\Models\Test;
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
        return response()->json(['messages' => 'Formlario diligenciado exitosamente. Seras redirigido a la página de inicio']);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $testId
     * @return Application|ResponseFactory|\Illuminate\Http\Response|Response
     */
    public function startTest(Request $request, int $testId)
    {
        $data = json_decode($request->input('data')); //parse data
        if ($data->pivot->has_answer === 1) {
            return response('Ya has contestado esta evaluación', 401);
        }
        $test = Form::findOrFail($testId);
        return Inertia::render('Tests/Show', ['test' => $test, 'group' => ['id' => $data->group_id, 'name' => $data->name], 'teacher' => $data->teacher]);
    }

}
