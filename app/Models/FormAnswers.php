<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormAnswers extends Model
{
    use HasFactory;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //TODO: Terminar
    public function group()
    {
        //
    }

    public function groupUser()
    {
        DB::table('group_user')->select('*')->join('form_answers', 'form_answers.group_user_id', '=', 'group_user.id')
            ->where('form_answers.group_user_id', '=', $this->group_user_id)->get();
    }

    public function unityAssessment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnityAssessment::class);
    }

    public function form(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Form  ::class);
    }
}
