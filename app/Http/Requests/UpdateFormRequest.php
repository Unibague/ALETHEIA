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
            'name' => 'required|String',
            'type' => 'required|Enum',
            'degree' =>  [  Rule::in(['pregrado','posgrado']),'requiered_if:type,estudiante'],
            'assessment_period_id' => 'required_if:type,estudiante|Integer',
            'unit_id' => 'required_if:type,otros|Integer',
            'academic_period_id' => 'required_if:type,otros|Integer',
            'unity_role' =>  [  Rule::in(['jefe','par','autoevaluación']),'requiered_if:type,otros'],
            'teaching_ladder' => [  Rule::in(['ninguno','auxiliar','asistente','asociado','titular']),'requiered_if:type,otros'],
            'service_area_id' => 'required_if:type,estudiante|Integer',

        ];
    }
}
