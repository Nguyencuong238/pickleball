<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointSubmissionResource extends JsonResource
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
            'uuid' => $this->uuid,
            'task' => [
                'code' => $this->pointTask->code,
                'name' => $this->pointTask->name,
                'points' => $this->pointTask->points,
            ],
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'proof_data' => $this->sanitizeProofData($this->proof_data),
            'points_awarded' => $this->points_awarded,
            'admin_notes' => $this->admin_notes,
            'submitted_at' => $this->created_at->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
    }

    /**
     * Sanitize proof data for API response (escape URLs)
     *
     * @param array<string, mixed>|null $proofData
     * @return array<string, mixed>|null
     */
    private function sanitizeProofData(?array $proofData): ?array
    {
        if ($proofData === null) {
            return null;
        }

        // Sanitize URL if present
        if (isset($proofData['url'])) {
            $proofData['url'] = filter_var($proofData['url'], FILTER_SANITIZE_URL);
        }

        return $proofData;
    }
}
