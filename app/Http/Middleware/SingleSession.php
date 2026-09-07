<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Track user activity (last seen) in Cache for 7 days
            Cache::put('user-last-seen-' . $user->id, now(), now()->addDays(7));

            // Security Check: Force logout if user account is deactivated
            if ($user->status !== 'Aktif') {
                // Audit Log: access attempt by deactivated user
                Log::warning("Deactivated user attempt to access route.", [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->fullUrl()
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['error' => 'user_deactivated'], 403);
                }

                return redirect()->route('login')->withErrors([
                    'login' => 'Akun Anda telah dinonaktifkan oleh administrator.'
                ]);
            }

            $userId = $user->id;
            $currentSessionId = $request->session()->getId();
            $cachedSessionId = Cache::get('user-session-' . $userId);

            // If there is a recorded session in Cache, and it doesn't match the current one
            if ($cachedSessionId && $currentSessionId !== $cachedSessionId) {
                // Audit Log: concurrent session invalidation
                Log::warning("Concurrent session invalidated. Logging out current session.", [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'invalidated_session_id' => $currentSessionId,
                    'active_session_id' => $cachedSessionId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // Logout the user on this device
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect back to login with error message or return JSON if AJAX
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['error' => 'session_invalidated'], 401);
                }

                return redirect()->route('login')->withErrors([
                    'login' => 'Akun Anda telah login di perangkat/browser lain. Sesi ini telah dikeluarkan otomatis.'
                ]);
            }

            // If there's no session recorded in Cache yet, set the current one
            if (!$cachedSessionId) {
                Cache::forever('user-session-' . $userId, $currentSessionId);
            }
        }

        return $next($request);
    }
}
