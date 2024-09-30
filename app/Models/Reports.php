<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class Reports extends Model
{
    use HasFactory;

    public static function mapGroupsResultsToFrontEndStructure($groupsResults){

        $headers =
            [
            ['text' => 'Docente', 'value' => 'teacher_name'],
            ['text' => 'Área de servicio', 'value' => 'service_area_name'],
            ['text' => 'Asignatura', 'value' => 'group_name'],
            ['text' => 'Número de Grupo', 'value' => 'group_number'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($groupsResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'service_area_name' => $result->service_area_name,
                'service_area_code' => $result->service_area_code,
                'assessment_period_id' => $result->assessment_period_id,
                'group_name' => $result->group_name,
                'group_number' => $result->group_number,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];


        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }


    public static function mapServiceAreasResultsToFrontEndStructure($serviceAreasResults){

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
                ['text' => 'Área de servicio', 'value' => 'service_area_name'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($serviceAreasResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'assessment_period_id' => $result->assessment_period_id,
                'service_area_name' => $result->service_area_name,
                'service_area_code' => $result->service_area_code,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];

        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }


    public static function mapFinalTeachingResultsToFrontEndStructure($finalTeachingResults){

        $headers =
            [
                ['text' => 'Docente', 'value' => 'teacher_name'],
            ];

        $attributeNames = [];
        $items = [];

        foreach ($finalTeachingResults as $result) {
            $item = [
                'teacher_name' => $result->teacher_name,
                'teacher_id' => $result->teacher_id,
                'assessment_period_id' => $result->assessment_period_id,
                'reviewers' => $result->reviewers,
                'total_students' => $result->total_students,
                'overall_average' => $result->overall_average,
                'open_ended_answers' => $result->open_ended_answers
            ];

            foreach ($result->competences_average as $competence) {
                foreach ($competence->attributes as $attribute) {
                    $attributeName = $attribute->name;
                    if (!in_array($attributeName, $attributeNames)) {
                        $attributeNames[] = $attributeName;
                        $headers[] = ['text' => $attributeName, 'value' => $attributeName];
                    }
                    $item[$attributeName] = $attribute->overall_average;
                }
            }
            $items[] = $item;
        }

        $headers [] = ['text' => 'Promedio General', 'value' => 'overall_average'];
        $headers [] = ['text' => 'Evaluadores', 'value' => 'reviewers'];
        $headers [] = ['text' => 'Total Estudiantes', 'value' => 'total_students'];
        $headers [] = ['text' => 'Gráfico', 'value' => 'graph'];
        $headers [] = ['text' => 'Comentarios', 'value' => 'open_ended_answers'];

        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }

}
