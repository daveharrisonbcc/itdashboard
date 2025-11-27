<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Allow preflight OPTIONS requests to pass through
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204);
        }

        $apiKey = $request->header('X-API-KEY');
        $validKey = env('SHAREPOINT_API_KEY');

        if ($apiKey !== $validKey) {
            // Add CORS headers to the 401 response
            return response()->json(['message' => 'Unauthorized'], 401)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-API-KEY, Authorization');
        }

        return $next($request);
    }
}
