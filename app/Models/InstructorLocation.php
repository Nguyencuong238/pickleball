<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'district',
        'city',
        'venues',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
