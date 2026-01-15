<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTaskResource extends JsonResource
{
    private bool $canEarn = true;
    private ?string $reason = null;

    /**
     * Set eligibility info for task
     */
    public function setEligibility(bool $canEarn, ?string $reason): self
    {
        $this->canEarn = $canEarn;
        $this->reason = $reason;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'points' => $this->points,
            'role' => $this->role,
            'category' => $this->category,
            'frequency' => $this->frequency,
            'requires_approval' => $this->requires_approval,
            'proof_type' => $this->proof_type,
            'is_active' => $this->is_active,
            'can_earn' => $this->canEarn,
            'ineligibility_reason' => $this->reason,
        ];
    }
}
