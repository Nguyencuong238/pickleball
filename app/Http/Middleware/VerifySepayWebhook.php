<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySepayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        // Fail-closed: reject if not configured
        $apiKey = config('gems.sepay.api_key');
        if (empty($apiKey)) {
            return response()->json(['message' => 'Webhook not configured'], 503);
        }

        // IP whitelist check
        $allowedIps = array_filter(
            explode(',', config('gems.sepay.allowed_ips', ''))
        );
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // API key check (always required)
        $authHeader = $request->header('Authorization', '');
        if ($authHeader !== "Apikey {$apiKey}") {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
