<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        return $user->hasRole('administrador');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'String',
            'type' => ['required', Rule::in([null, 'estudiantes', 'otros'])],
            'degree' => [Rule::in([null, 'pregrado', 'posgrado','cursos'])],
            'academic_period_id' => 'Integer',
            'assessment_period_id' => 'Integer',
            'units' => 'Array|min:1',
            'unit_role' => [Rule::in([null, 'jefe', 'par', 'autoevaluación'])],
            'teaching_ladder' => [Rule::in([null, 'ninguno', 'auxiliar', 'asistente', 'asociado', 'titular'])],
            'service_areas' => 'Array|min:1',

        ];
    }
}
