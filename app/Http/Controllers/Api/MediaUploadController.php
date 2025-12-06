<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    /**
     * Get identifier for current user/session
     * Works for both Web and API
     */
    private function getSessionIdentifier(Request $request)
    {
        // If authenticated via JWT (API)
        if (auth('api')->check()) {
            return 'user_' . auth('api')->id();
        }
        
        // If authenticated via session (Web)
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        // Guest user - use session ID
        return session()->getId();
    }

    /**
     * Upload media to temp collection
     */
    public function uploadMedia(Request $request)
    {
        if ($request->rules) {
            $mediaRules = 'mimes:' . $request->rules . '|max:2048';
        } else {
            $mediaRules = 'max:2048';
        }

        $request->validate([
            'media' => 'required|array',
            'media.*' => $mediaRules,
        ]);

        try {
            $sessionId = $this->getSessionIdentifier($request);
            
            $tempoModel = \App\Models\Tempo::firstOrCreate(
                ['session_id' => $sessionId]
            );

            $uploadedMedia = [];
            foreach ($request->file('media') as $file) {
                $media = $tempoModel->addMedia($file)
                    ->toMediaCollection('default');

                $uploadedMedia[] = [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ];
            }

            return response()->json([
                'success' => true,
                'media' => $uploadedMedia,
                'message' => 'Media uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete media
     */
    public function deleteMedia($mediaId)
    {
        try {
            // Delete from media library
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
            if ($media) {
                $media->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
