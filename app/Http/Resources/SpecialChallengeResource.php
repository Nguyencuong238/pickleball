<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecialChallengeResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'points' => $this->points,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'is_ongoing' => $this->isOngoing(),
            'is_upcoming' => $this->isUpcoming(),
            'has_ended' => $this->hasEnded(),
            'status_label' => $this->getStatusLabel(),
            'participant_count' => $this->getParticipantCount(),
            'max_participants' => $this->max_participants,
            'remaining_slots' => $this->getRemainingSlots(),
            'has_reached_limit' => $this->hasReachedLimit(),
        ];
    }
}
