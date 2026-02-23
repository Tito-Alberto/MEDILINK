<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePharmacyApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $pharmacy = $user?->pharmacy;

        if (! $pharmacy) {
            return redirect()
                ->route('pharmacy.status')
                ->with('status', 'Cadastre sua farmacia para gerir produtos.');
        }

        if ($pharmacy->status !== 'approved') {
            return redirect()
                ->route('pharmacy.status')
                ->with('status', 'Sua farmacia ainda nao foi aprovada.');
        }

        return $next($request);
    }
}
