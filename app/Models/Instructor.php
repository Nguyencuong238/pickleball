<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'experience', 'ward', 'province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
