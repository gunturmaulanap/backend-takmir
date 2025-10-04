<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get CORS configuration
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $allowedMethods = config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']);
        $allowedHeaders = config('cors.allowed_headers', ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With', 'X-CSRF-TOKEN']);
        $supportsCredentials = config('cors.supports_credentials', false);
        $maxAge = config('cors.max_age', 86400);

        // Convert arrays to strings
        $allowedOriginsString = is_array($allowedOrigins) ? implode(', ', $allowedOrigins) : $allowedOrigins;
        $allowedMethodsString = is_array($allowedMethods) ? implode(', ', $allowedMethods) : $allowedMethods;
        $allowedHeadersString = is_array($allowedHeaders) ? implode(', ', $allowedHeaders) : $allowedHeaders;

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200, [
                'Access-Control-Allow-Origin' => $allowedOriginsString,
                'Access-Control-Allow-Methods' => $allowedMethodsString,
                'Access-Control-Allow-Headers' => $allowedHeadersString,
                'Access-Control-Allow-Credentials' => $supportsCredentials ? 'true' : 'false',
                'Access-Control-Max-Age' => (string) $maxAge
            ]);
        }

        $response = $next($request);

        // Add CORS headers to the response
        $response->headers->set('Access-Control-Allow-Origin', $allowedOriginsString);
        $response->headers->set('Access-Control-Allow-Methods', $allowedMethodsString);
        $response->headers->set('Access-Control-Allow-Headers', $allowedHeadersString);
        $response->headers->set('Access-Control-Allow-Credentials', $supportsCredentials ? 'true' : 'false');

        return $response;
    }
}
