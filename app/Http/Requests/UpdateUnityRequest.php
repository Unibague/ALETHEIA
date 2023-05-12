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

        if($user->hasRole('administrador') || $user->hasRole('administrador de unidad')){

            return true;
        }

        return false;

/*        $user = auth()->user();
        return $user->hasRole('administrador');*/
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|String',
        ];
    }
}
