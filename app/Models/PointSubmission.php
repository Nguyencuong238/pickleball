<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PointSubmission extends Model
{
    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'uuid',
        'user_id',
        'point_task_id',
        'status',
        'proof_data',
        'admin_id',
        'admin_notes',
        'reviewed_at',
        'points_awarded',
    ];

    protected $casts = [
        'proof_data' => 'array',
        'reviewed_at' => 'datetime',
        'points_awarded' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Use UUID for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user who submitted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated point task
     */
    public function pointTask(): BelongsTo
    {
        return $this->belongsTo(PointTask::class);
    }

    /**
     * Get the admin who reviewed
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if submission is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if submission is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if submission is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Dang cho duyet',
            self::STATUS_APPROVED => 'Da duyet',
            self::STATUS_REJECTED => 'Tu choi',
            default => $this->status,
        };
    }

    /**
     * Scope for pending submissions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected submissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
