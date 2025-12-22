<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StadiumResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'maps_address' => $this->maps_address,
            'maps_link' => $this->maps_link,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'image' => $this->getFirstMedia('banner') ? $this->getFirstMediaUrl('banner') : '',
            'opening_hours' => $this->opening_hours,
            'amenities' => $this->amenities,
            'utilities' => $this->utilities,
            'regulations' => $this->regulations,
            'court_surface' => $this->court_surface,
            'rating' => $this->rating,
            'rating_count' => $this->rating_count,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_premium' => $this->is_premium,
            'is_verified' => $this->is_verified,
            'verified' => $this->verified,
            'province_id' => $this->province_id,
            'user_id' => $this->user_id,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            
            // Relationships
            'province' => new ProvinceResource($this->whenLoaded('province')),
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
