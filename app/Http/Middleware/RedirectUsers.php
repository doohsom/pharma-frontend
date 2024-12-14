<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (\Auth::guard($guard)->check()) {
                $role = \Auth::user()->role;
                return redirect($this->getDashboardRoute($role));
            }
        }

        return $next($request);
    }

    private function getDashboardRoute($role)
    {
        switch ($role) {
            case 'Supplier':
                return '/supplier/dashboard';
            case 'PharmaCo':
                return '/manufacturer/dashboard';
            case 'Distributor':
                return '/distributor/dashboard';
            case 'Pharmacy':
                return '/pharmacy/dashboard';
            case 'Patient':
                return '/patient/dashboard';
            default:
                return '/dashboard';
        }
    }
}
