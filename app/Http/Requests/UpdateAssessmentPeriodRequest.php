<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentPeriodRequest extends FormRequest
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
            'self_start_date' => 'required|Date',
            'self_end_date' => 'required|Date',
            'boss_start_date' => 'required|Date',
            'boss_end_date' => 'required|Date',
            'done_by_name' => 'required|Boolean',
            'done_by_auxiliary' => 'required|Boolean',
            'done_by_assistant' => 'required|Boolean',
            'done_by_associated' => 'required|Boolean',
            'done_by_head_teacher' => 'required|Boolean',
            'active' => 'required|Boolean',
            'colleague_start_date' => 'required|Date',
            'colleague_end_date' => 'required|Date',
        ];
    }
}
