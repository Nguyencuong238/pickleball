<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    /**
     * Allowed MIME types for media upload (server-defined whitelist)
     */
    private const ALLOWED_IMAGE_MIMES = 'jpg,jpeg,png,gif,webp';
    private const ALLOWED_VIDEO_MIMES = 'mp4,mov,webm';
    private const MAX_FILE_SIZE = 10240; // 10MB

    /**
     * Upload media to temp collection
     */
    public function uploadMedia(Request $request)
    {
        // Determine media type from request (images or videos only)
        $mediaType = $request->input('type', 'image');

        // Use server-defined whitelist - never trust client input for validation rules
        $allowedMimes = match ($mediaType) {
            'video' => self::ALLOWED_VIDEO_MIMES,
            default => self::ALLOWED_IMAGE_MIMES,
        };

        $request->validate([
            'media' => 'required|array|max:10',
            'media.*' => 'required|file|mimes:' . $allowedMimes . '|max:' . self::MAX_FILE_SIZE,
            'type' => 'nullable|in:image,video',
        ]);

        try {
            $tempoModel = \App\Models\Tempo::firstOrCreate(
                ['session_id' => session()->getId()]
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
