<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
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
            'court_name' => $this->court_name,
            'court_number' => $this->court_number,
            'court_type' => $this->court_type,
            'surface_type' => $this->surface_type,
            'status' => $this->status,
            'description' => $this->description,
            'amenities' => $this->amenities,
            'capacity' => $this->capacity,
            'size' => $this->size,
            'is_active' => $this->is_active,
            'daily_matches' => $this->daily_matches,
            'rental_price' => $this->rental_price,
            'stadium_id' => $this->stadium_id,
            'tournament_id' => $this->tournament_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
