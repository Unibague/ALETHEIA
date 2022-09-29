<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnityRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'id' => 'required|Integer',
            'name' => 'required|String',
            'code' => 'required|String',
            'is_custom' => 'required|Boolean',
            'assessment_period_id' => 'required|Integer',

        ];
    }
}
