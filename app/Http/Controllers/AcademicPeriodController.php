<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Http\Requests\StoreAcademicPeriodRequest;
use App\Http\Requests\UpdateAcademicPeriodRequest;
use Database\Seeders\AcademicPeriodSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AcademicPeriodController extends Controller
{

    public function sync()
    {
        //TODO:Erase this, temporary create fake periods
        AcademicPeriod::createFakePeriods();

        //TODO: Make request and store in $academicPeriods
        $academicPeriods = [
            (object)[
                'name' => '2022A',
                'class_start_date' => '2022-02-01',
                'class_end_date' => '2022-02-01',
            ],
            (object)[
                'name' => '2022B',
                'class_start_date' => '2022-02-01',
                'class_end_date' => '2022-02-01',
            ],
        ];

        //Iterate over received data and create the academic period
        foreach ($academicPeriods as $academicPeriod) {
            AcademicPeriod::updateOrCreate(
                [
                    'name' => $academicPeriod->name
                ],
                [
                    'class_start_date' => $academicPeriod->class_start_date,
                    'class_end_date' => $academicPeriod->class_end_date,
                ]);
        }
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(AcademicPeriod::with('assessmentPeriod')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreAcademicPeriodRequest $request
     * @return Response
     */
    public function store(StoreAcademicPeriodRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AcademicPeriod $academicPeriod
     * @return Response
     */
    public function show(AcademicPeriod $academicPeriod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AcademicPeriod $academicPeriod
     * @return Response
     */
    public function edit(AcademicPeriod $academicPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateAcademicPeriodRequest $request
     * @param \App\Models\AcademicPeriod $academicPeriod
     * @return Response
     */
    public function update(UpdateAcademicPeriodRequest $request, AcademicPeriod $academicPeriod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\AcademicPeriod $academicPeriod
     * @return Response
     */
    public function destroy(AcademicPeriod $academicPeriod)
    {
        //
    }
}
