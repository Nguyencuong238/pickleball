<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'description' => $this->description,
            'metadata' => $this->metadata,
            'is_positive' => $this->isPositive(),
            'formatted_points' => $this->getFormattedPoints(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
