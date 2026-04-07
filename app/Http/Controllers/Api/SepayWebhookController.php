<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SepayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    public function handle(Request $request, SepayService $sepayService): JsonResponse
    {
        try {
            $sepayService->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('SePay webhook error: ' . $e->getMessage(), [
                'payload' => $request->all(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
