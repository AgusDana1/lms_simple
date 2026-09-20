<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRole = $user->role?->slug;

        if (! $userRole || ! in_array($userRole, $roles, true)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
