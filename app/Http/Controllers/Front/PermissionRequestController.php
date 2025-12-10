<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:home_yard,referee'
        ]);

        $user = auth()->user();

        // Check if user already has a pending request
        $existingRequest = PermissionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'Bạn đã có yêu cầu cấp quyền đang chờ xét duyệt.'
            ], 422);
        }

        // Create new permission request
        $permissionRequest = PermissionRequest::create([
            'user_id' => $user->id,
            'permissions' => $validated['permissions'],
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Yêu cầu cấp quyền đã được gửi thành công.',
            'request' => $permissionRequest
        ], 201);
    }
}
