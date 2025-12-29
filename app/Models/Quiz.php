<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'category',
        'difficulty',
        'is_active',
    ];

    protected $casts = [
        'options' => 'json',
        'is_active' => 'boolean',
        'difficulty' => 'integer',
    ];
}
