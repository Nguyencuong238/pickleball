<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'name',
        'description',
        'price',
        'sessions_count',
        'discount_percent',
        'is_group',
        'max_group_size',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'sessions_count' => 'integer',
        'discount_percent' => 'integer',
        'is_group' => 'boolean',
        'max_group_size' => 'integer',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
