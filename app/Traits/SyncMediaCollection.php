<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;

trait SyncMediaCollection
{
    /**
     * Sync media collection for this model
     * - Move media from Tempo to model collection
     * - Delete old media not in the new list
     * 
     * @param string $fieldName Form field name (e.g., 'gallery', 'banner')
     * @param string $collectionName Media collection name
     * @param \Illuminate\Http\Request $request Request instance
     */
    public function syncMediaCollection($fieldName, $collectionName, $request)
    {
        $mediaIds = $request->input($fieldName);
        
        if (empty($mediaIds)) {
            $this->deleteAllMediaInCollection($collectionName);
            return;
        }

        $newMediaIds = $this->parseMediaIds($mediaIds);
        
        // Delete media not in the new list
        $this->deleteOldMedia($collectionName, $newMediaIds);
        
        // Move newly uploaded media from Tempo
        $this->moveTempoMedia($newMediaIds, $collectionName);
        
        // Clean up empty Tempo model
        $this->cleanupTempoModel();
    }

    /**
     * Parse and filter media IDs from comma-separated string
     */
    private function parseMediaIds($mediaIds): array
    {
        return array_filter(
            array_map('intval', explode(',', $mediaIds)),
            fn($id) => $id > 0
        );
    }

    /**
     * Delete media not in the new list
     */
    private function deleteOldMedia($collectionName, array $newMediaIds): void
    {
        $this->getMedia($collectionName)
            ->reject(fn($media) => in_array($media->id, $newMediaIds))
            ->each(fn($media) => $this->safeDeleteMedia($media));
    }

    /**
     * Move newly uploaded media from Tempo model
     */
    private function moveTempoMedia(array $newMediaIds, $collectionName): void
    {
        $tempoMediaIds = $this->getTempoMediaIds();
        
        if (empty($tempoMediaIds)) {
            return;
        }

        // Find newly uploaded media (intersection of new IDs and Tempo IDs)
        $mediaToMove = array_intersect($newMediaIds, $tempoMediaIds);
        
        foreach ($mediaToMove as $mediaId) {
            $this->safelyMoveMedia($mediaId, $collectionName);
        }
    }

    /**
     * Get all media IDs from Tempo model
     */
    private function getTempoMediaIds(): array
    {
        $tempoModel = \App\Models\Tempo::where('session_id', session()->getId())->first();
        
        return $tempoModel 
            ? $tempoModel->getMedia('default')->pluck('id')->toArray()
            : [];
    }

    /**
     * Safely move a media to this model
     */
    private function safelyMoveMedia($mediaId, $collectionName): void
    {
        $media = Media::find($mediaId);
        
        if ($media && $media->model_type === 'App\Models\Tempo') {
            $this->safeDeleteMedia($media, fn() => $media->move($this, $collectionName), "move");
        }
    }

    /**
     * Safely delete a media with error handling
     */
    private function safeDeleteMedia($media, $callback = null, $action = "delete"): void
    {
        try {
            if ($callback) {
                $callback();
            } else {
                $media->delete();
            }
        } catch (\Exception $e) {
            Log::error("Failed to {$action} media {$media->id}: " . $e->getMessage());
        }
    }

    /**
     * Delete all media in a collection
     */
    private function deleteAllMediaInCollection($collectionName): void
    {
        $this->getMedia($collectionName)->each(fn($media) => $this->safeDeleteMedia($media));
    }

    /**
     * Clean up Tempo model if empty
     */
    private function cleanupTempoModel(): void
    {
        $tempoModel = \App\Models\Tempo::where('session_id', session()->getId())->first();
        
        if ($tempoModel && $tempoModel->getMedia('default')->isEmpty()) {
            $tempoModel->delete();
        }
    }

    /**
     * Sync multiple media collections at once
     * 
     * @param array $collections Array of [fieldName => collectionName]
     *                           Example: ['gallery' => 'gallery', 'banner' => 'banner']
     * @param \Illuminate\Http\Request $request Request instance
     */
    public function syncMultipleMediaCollections($collections = [], $request = null)
    {
        $request = $request ?? request();

        foreach ($collections as $fieldName => $collectionName) {
            if ($request->has($fieldName)) {
                $this->syncMediaCollection($fieldName, $collectionName, $request);
            }
        }
    }
}
