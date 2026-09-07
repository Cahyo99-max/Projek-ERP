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
   

    public function handle($request, Closure $next, ...$roles)
    {
        // 1. Cek apakah user sudah login, jika belum lempar ke halaman login (jangan langsung 403 biar gak bingung)
        if (! auth()->check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu.');
        }

        // 2. Proteksi Keamanan: Cek apakah data relasi role-nya ada di database
        if (! auth()->user()->role) {
            auth()->logout();

            return redirect()->route('login')->with('warning', 'Akun Anda tidak memiliki hak akses.');
        }

        // 3. Ambil nama role (diubah ke huruf kecil semua biar aman dari typo kapital)
        $userRole = strtolower(auth()->user()->role->name);

        // 4. Cek apakah role user ada di dalam daftar role yang diizinkan
        // array_map digunakan agar semua parameter role yang dikirim di route juga jadi huruf kecil
        if (! in_array($userRole, array_map('strtolower', $roles))) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
