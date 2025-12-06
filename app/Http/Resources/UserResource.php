<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'role_type' => $this->role_type,
            'status' => $this->status,
            'elo_rating' => $this->elo_rating,
            'elo_rank' => $this->elo_rank,
            'total_ocr_matches' => $this->total_ocr_matches,
            'ocr_wins' => $this->ocr_wins,
            'ocr_losses' => $this->ocr_losses,
            'challenge_score' => $this->challenge_score,
            'community_score' => $this->community_score,
            'total_oprs' => $this->total_oprs,
            'opr_level' => $this->opr_level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
