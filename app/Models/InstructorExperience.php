<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'organization',
        'description',
        'start_year',
        'end_year',
        'is_current',
        'sort_order',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'sort_order' => 'integer',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
