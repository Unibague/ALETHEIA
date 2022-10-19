<?php

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreFormRequest extends FormRequest
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
            'name' => 'required|String',
            'type' => ['required', Rule::in([null, 'estudiantes', 'otros'])],
            'degree' => [Rule::in([null, 'pregrado', 'posgrado']), 'required_if:type,estudiante'],
            'academic_period_id' => 'required_if:type,estudiantes|Integer',
            'assessment_period_id' => 'required_if:type,otros|Integer',
            'unit_id' => 'required_if:type,otros|Integer',
            'unit_role' => [Rule::in([null, 'jefe', 'par', 'autoevaluación']), 'required_if:type,otros'],
            'teaching_ladder' => [Rule::in([null, 'ninguno', 'auxiliar', 'asistente', 'asociado', 'titular']), 'required_if:type,otros'],
            'service_area_id' => 'required_if:type,estudiantes|Integer',
        ];
    }
}
