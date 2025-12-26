<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stadium = null;
        if ($this->court && $this->court->stadium) {
            $stadium = new StadiumBookingResource($this->court->stadium);
        }

        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'booking_date' => $this->booking_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration_hours' => $this->duration_hours,
            'hourly_rate' => $this->hourly_rate,
            'total_price' => $this->total_price,
            'service_fee' => $this->service_fee,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'court' => new CourtResource($this->whenLoaded('court')),
            'stadium' => $stadium,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
