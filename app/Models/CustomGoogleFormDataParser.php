<?php

namespace App\Models;

use Google\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomGoogleFormDataParser extends Model
{

    protected $categories = [
        'Diseño de experiencias y contextos ampliados de aprendizaje' => [0, 1],
        'Espacios amables para el aprendizaje' => [2, 3],
        'Medición y aseguramiento del aprendizaje' => [4, 5]
    ];

    // Response value mapping from your original code
    protected $responseValues = [
        'Totalmente en desacuerdo' => 1,
        'En desacuerdo' => 2,
        'Neutral' => 3,
        'De acuerdo' => 4,
        'Totalmente de acuerdo' => 5,
        'Nada satisfecho(a)' => 1,
        'Poco satisfecho(a)' => 2,
        'Normal' => 3,
        'Satisfecho(a)' => 4,
        'Muy Satisfecho(a)' => 5
    ];


    protected $teachers = [
        'T1' => 'adriana.covaleda@unibague.edu.co',
        'T2' => 'fredy.camacho@unibague.edu.co',
        'T3' => 'sandra.obando@unibague.edu.co',
        'T4' => 'sandra.munoz@unibague.edu.co',
        'T5' => 'mauricio.gomez@unibague.edu.co',
        'T6' => 'santiago.sanchez@unibague.edu.co'
    ];


    use HasFactory;

    protected function getTeachersByClassCode($classCode)
    {
        $baseTeachers = [
            'T1' => 'adriana.covaleda@unibague.edu.co',
            'T2' => 'fredy.camacho@unibague.edu.co',
            'T3' => 'sandra.obando@unibague.edu.co',
            'T4' => 'sandra.munoz@unibague.edu.co',
            'T5' => 'mauricio.gomez@unibague.edu.co',
            'T6' => 'santiago.sanchez@unibague.edu.co',
        ];

        if ($classCode === '51A49') {
            $baseTeachers = [
                'T1' => 'adriana.covaleda@unibague.edu.co',
                'T2' => 'fredy.camacho@unibague.edu.co',
                'T3' => 'sandra.obando@unibague.edu.co',
                'T4' => 'sandra.munoz@unibague.edu.co',
                'T5' => 'mauricio.gomez@unibague.edu.co',
                'T6' => 'monica.cardenas@unibague.edu.co',
                'T7' => 'santiago.sanchez@unibague.edu.co',
            ];
        }

        return $baseTeachers;
    }


    public function transformGoogleFormData(\Illuminate\Support\Collection $rawData, $classCode)
    {

        $transformedData = collect();

        // Dynamically set the teachers based on the class_code
        $teachers = $this->getTeachersByClassCode($classCode);


        $rawData->each(function ($submission) use (&$transformedData, $teachers) {
            $transformedSubmission = [
                'timestamp' => $submission['Timestamp'],
                'student_email' => $submission['Email address'],
                'evaluations' => []
            ];

            foreach ($teachers as $teacherIndex => $teacherEmail) {

                $teacherResponses = $this->extractTeacherResponses($submission->toArray(), $teacherIndex);

                if (!empty($teacherResponses)) {
                    $evaluation = $this->calculateTeacherEvaluation($teacherResponses);
                    $transformedSubmission['evaluations'][$teacherEmail] = $evaluation;
                }
            }
            $transformedData->push($transformedSubmission);
        });

        return $transformedData;
    }


    private function extractTeacherResponses($submission, string $teacherIndex): array
    {

        $teacherResponses = [];

        // Find columns for this teacher
        foreach ($submission as $key => $value) {
            // Simple string comparison instead of regex
            if (strpos($key, "[{$teacherIndex}_") === 0 || strpos($key, "{$teacherIndex}_") === 0) {
                // Extract the number after the teacher index
                preg_match("/\d+$/", $key, $matches);
                if ($matches) {
                    $questionNumber = (int)$matches[0];
                    $teacherResponses[$questionNumber - 1] = $value;
                }
            }
        }

        // Sort by question number to ensure correct order
        ksort($teacherResponses);

        return $teacherResponses;
    }


    private function calculateTeacherEvaluation(array $responses): array
    {
        $evaluation = [];

        // Calculate averages for each category
        foreach ($this->categories as $categoryName => $indices) {
            $evaluation[] = [
                'category' => $categoryName,
                'average' => $this->calculateCategoryAverage($responses, $indices)
            ];
        }

        // Add satisfaction (assuming it's the 7th item)
        $evaluation[] = [
            'category' => 'Satisfacción',
            'value' => $this->responseValues[$responses[6]] ?? null
        ];

        // Add comments (assuming it's the 8th item)
        $evaluation[] = [
            'category' => 'Comentarios',
            'value' => $responses[7] ?? null
        ];

        return $evaluation;
    }

    /**
     * Calculate average for a specific category
     */
    private function calculateCategoryAverage(array $responses, array $indices): float
    {
        $sum = 0;
        foreach ($indices as $index) {
            $response = $responses[$index] ?? 0;
            $sum += $this->responseValues[$response] ?? 0;
        }
        return round($sum / count($indices), 2);
    }

    public function processTransformedAnswers($transformedData)
    {
        $teachersAnalysis = [];

        foreach ($transformedData as $data) {
            foreach ($data['evaluations'] as $teacherEmail => $categories) {
                // Initialize the structure for this teacher if not already present
                if (!isset($teachersAnalysis[$teacherEmail])) {
                    $teachersAnalysis[$teacherEmail] = [
                        'categories' => [
                            'Docencia' => [
                                'id' => 1,
                                'name' => 'Docencia',
                                'attributes' => [
                                    ['name' => 'Diseño de experiencias y contextos ampliados de aprendizaje', 'overall_average' => 0],
                                    ['name' => 'Espacios amables para el aprendizaje', 'overall_average' => 0],
                                    ['name' => 'Medición y aseguramiento del aprendizaje', 'overall_average' => 0],
                                ],
                                'overall_average' => 0,
                            ],
                            'Satisfacción' => [
                                'id' => 2,
                                'name' => 'Satisfacción',
                                'attributes' => [
                                    ['name' => 'Satisfacción', 'overall_average' => 0],
                                ],
                                'overall_average' => 0,
                            ],
                        ],
                        'open_ended_answers' => [
                            'question' => '¿Tiene algún comentario constructivo, que permita el mejoramiento en el desempeño del Asesor/Coordinador?',
                            'commentType' => [
                                [
                                    'type' => 'Constructivo',
                                    'answers' => [],
                                ],
                            ],
                        ],
                    ];
                }

                // Temporary sums and counts for overall averages
                $docenciaSums = [
                    'Diseño de experiencias y contextos ampliados de aprendizaje' => 0,
                    'Espacios amables para el aprendizaje' => 0,
                    'Medición y aseguramiento del aprendizaje' => 0,
                ];
                $docenciaCounts = [
                    'Diseño de experiencias y contextos ampliados de aprendizaje' => 0,
                    'Espacios amables para el aprendizaje' => 0,
                    'Medición y aseguramiento del aprendizaje' => 0,
                ];
                $satisfactionSum = 0;
                $satisfactionCount = 0;

                // Iterate over the categories for the teacher
                foreach ($categories as $category) {
                    $categoryName = $category['category'];

                    if ($categoryName === 'Comentarios') {
                        // Collect comments for open-ended answers
                        $teachersAnalysis[$teacherEmail]['open_ended_answers']['commentType'][0]['answers'][] = $category['value'];
                    } elseif (isset($docenciaSums[$categoryName])) {
                        // Add Docencia category averages
                        $docenciaSums[$categoryName] += $category['average'];
                        $docenciaCounts[$categoryName]++;
                    } elseif ($categoryName === 'Satisfacción') {
                        // Add Satisfacción averages
                        $satisfactionSum += $category['value'];
                        $satisfactionCount++;
                    }
                }

                // Calculate Docencia averages
                foreach ($teachersAnalysis[$teacherEmail]['categories']['Docencia']['attributes'] as &$attribute) {
                    $attribute['overall_average'] = $docenciaCounts[$attribute['name']] > 0
                        ? round($docenciaSums[$attribute['name']] / $docenciaCounts[$attribute['name']], 2)
                        : 0;
                }

                // Calculate Satisfacción averages
                if ($satisfactionCount > 0) {
                    $teachersAnalysis[$teacherEmail]['categories']['Satisfacción']['attributes'][0]['overall_average'] = round($satisfactionSum / $satisfactionCount, 2);
                }
            }
        }

        // Calculate overall averages for Docencia and Satisfacción
        foreach ($teachersAnalysis as &$teacherData) {
            $docenciaTotal = array_sum(array_column($teacherData['categories']['Docencia']['attributes'], 'overall_average'));
            $teacherData['categories']['Docencia']['overall_average'] = round($docenciaTotal / count($teacherData['categories']['Docencia']['attributes']), 2);

            $satisfactionOverall = $teacherData['categories']['Satisfacción']['attributes'][0]['overall_average'];
            $teacherData['categories']['Satisfacción']['overall_average'] = $satisfactionOverall;
        }

        return $teachersAnalysis;
    }






}
