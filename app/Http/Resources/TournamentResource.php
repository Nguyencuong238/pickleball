<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('banner');
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'registration_deadline' => $this->registration_deadline,
            'location' => $this->location,
            'organizer' => $this->organizer,
            'organizer_email' => $this->organizer_email,
            'organizer_hotline' => $this->organizer_hotline,
            'price' => $this->price,
            'prizes' => $this->prizes,
            'max_participants' => $this->max_participants,
            'participants_count' => $this->athletes()->count(),
            'image_url' => $media ? $media->getUrl() : null,
            'status' => $this->start_date > now() ? 'upcoming' : ($this->end_date < now() ? 'completed' : 'ongoing'),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
