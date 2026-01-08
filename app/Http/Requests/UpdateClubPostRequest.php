<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by policy in controller
    }

    public function rules(): array
    {
        $imageConfig = config('club_posts.images');
        $videoConfig = config('club_posts.videos');

        return [
            'content' => 'required|string|max:' . config('club_posts.content.max_length'),
            'visibility' => 'required|in:public,members_only',
            'media_type' => 'nullable|in:images,video,youtube',
            'keep_media_ids' => 'nullable|array',
            'keep_media_ids.*' => 'integer|exists:club_post_media,id',
            'images' => 'nullable|array|max:' . $imageConfig['max_count'],
            'images.*' => 'image|mimes:' . implode(',', $imageConfig['allowed_mimes']) . '|max:' . $imageConfig['max_size'],
            'video' => 'nullable|file|mimes:' . implode(',', $videoConfig['allowed_mimes']) . '|max:' . $videoConfig['max_size'],
            'youtube_url' => ['nullable', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/'],
        ];
    }
}
