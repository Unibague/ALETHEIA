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
            'degree' =>  [  Rule::in(['pregrado','posgrado'])],
            'assessment_period_id' => 'Integer',
            'unit_id' => 'Integer',
            'academic_period_id' => 'Integer',
            'unity_role' =>  [  Rule::in(['jefe','par','autoevaluación'])],
            'teaching_ladder' => [  Rule::in(['ninguno','auxiliar','asistente','asociado','titular'])],
            'service_area_id' => 'Integer',

        ];
    }
}
