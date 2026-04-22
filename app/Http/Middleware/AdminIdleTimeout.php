<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return $next($request);
        }

        $timeoutMinutes = (int) env('ADMIN_IDLE_TIMEOUT', 15);
        $lastActivity = $request->session()->get('admin_last_activity');

        if ($lastActivity && (time() - $lastActivity) > ($timeoutMinutes * 60)) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['session' => 'Sesi admin berakhir karena tidak ada aktivitas. Silakan login lagi.']);
        }

        $request->session()->put('admin_last_activity', time());

        return $next($request);
    }
}
