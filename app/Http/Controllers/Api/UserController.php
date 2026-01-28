<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Delete a user (soft delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Check if user is trying to delete themselves or has admin privileges
            if (auth()->id() !== $user->id && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'Unauthorized to delete this user',
                    'status' => false
                ], 403);
            }

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully',
                'status' => true,
                'data' => [
                    'id' => $user->id,
                    'deleted_at' => $user->deleted_at
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
                'status' => false
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting user: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

}
