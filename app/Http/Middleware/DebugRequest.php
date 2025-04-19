<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::debug('Request method: ' . $request->method());
        Log::debug('Request path: ' . $request->path());
        Log::debug('Request all: ' . json_encode($request->all(), JSON_PRETTY_PRINT));

        $response = $next($request);

        Log::debug('Response status: ' . $response->status());
        
        return $response;
    }
}