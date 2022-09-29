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
     * Update the specified resource in storage.
     *
     * @param UpdateAcademicPeriodRequest $request
     * @param AcademicPeriod $academicPeriod
     * @return JsonResponse
     */
    public function update(UpdateAcademicPeriodRequest $request, AcademicPeriod $academicPeriod): JsonResponse
    {
        $academicPeriod->update($request->all());
        return response()->json(['message' => 'El periodo ha sido actualizado exitosamente']);
    }
}
