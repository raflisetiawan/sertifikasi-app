<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentCallbackMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip authentication for payment callbacks
        if ($request->is('api/payments/callback') || $request->is('api/payments/status/update')) {
            return $next($request);
        }

        return $next($request);
    }
}
