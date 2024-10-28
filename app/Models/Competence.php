<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $casts = [
        'attributes' => 'array', // Automatically cast attributes as array (JSON)
    ];

    use HasFactory;



}
