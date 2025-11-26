<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddNgrokHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add ngrok-skip-browser-warning header to bypass ngrok warning page
        if (str_contains(config('app.url'), 'ngrok')) {
            $response->headers->set('ngrok-skip-browser-warning', 'true');
        }
        
        return $response;
    }
}
