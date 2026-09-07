<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserLoginController extends Controller
{
    // 1. Menampilkan halaman login buatan kamu
    public function tampilFormLogin()
    {
        if (Auth::check()) {
            return redirect('/dashbord');
        }
        return view('login_masuk.login1');
    }

    public function prosesLogin(Request $request)
    {
        // 1. Validasi (Biarkan Laravel yang handle exception-nya otomatis)
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ], [
            'login.required' => 'Username atau Email wajib diisi!',
            'login.max' => 'Username atau Email terlalu panjang!',
            'password.required' => 'Password wajib diisi!',
            'password.max' => 'Password terlalu panjang!',
        ]);

        // Rate limiting: max 3 attempts per login+IP, decay in 3 minutes (180 seconds)
        $rateKey = 'login-attempts|'.Str::lower($request->input('login')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            // Audit Log: lockout event
            Log::warning("Login lockout triggered.", [
                'login' => $request->input('login'),
                'ip' => $request->ip(),
                'lockout_time_seconds' => $seconds,
                'user_agent' => $request->userAgent()
            ]);

            return back()->withErrors([
                'login' => 'Terlalu banyak percobaan login salah. Akun Anda dikunci sementara.',
            ])->with('lockout_time', $seconds)->onlyInput('login');
        }

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $kredensial = [
            $fieldType => $request->login,
            'password' => $request->password,
            'status' => 'Aktif',
        ];

        // 2. Bungkus proses autentikasi & update dengan try-catch
        try {
            if (Auth::attempt($kredensial, $request->remember)) {
                $request->session()->regenerate();
                // Clear rate limit on successful login
                RateLimiter::clear($rateKey);

                $user = auth()->user();

                // Store current session ID in cache to restrict multiple logins
                \Illuminate\Support\Facades\Cache::forever('user-session-' . $user->id, $request->session()->getId());

                // Update login terakhir
                try {
                    $user->update(['login_terakhir' => now()]);
                } catch (\Exception $e) {
                    Log::warning("Gagal mengupdate login_terakhir untuk user ID: " . $user->id . ". Error: " . $e->getMessage());
                }

                // ===== AUTO-SET PT & CABANG AKTIF =====
                $activeBranch = $user->branches()
                                     ->with('company')
                                     ->where('branches.status', 'Active')
                                     ->orderByRaw('user_branches.is_default DESC')
                                     ->first();

                if ($activeBranch) {
                    session([
                        'active_branch_id'    => $activeBranch->id,
                        'active_branch_name'  => $activeBranch->nama_cabang,
                        'active_branch_kode'  => $activeBranch->kode_cabang,
                        'active_company_id'   => $activeBranch->company_id,
                        'active_company_name' => $activeBranch->company?->nama_perusahaan ?? $activeBranch->nama_perusahaan,
                    ]);
                } else {
                    $activeCompany = $user->companies()->orderByRaw('user_companies.is_default DESC')->first();
                    if (!$activeCompany && $user->hasRole('Super Admin')) {
                        $activeCompany = \App\Models\Company::where('status', 'Active')->first();
                    }
                    if ($activeCompany) {
                        session([
                            'active_branch_id'    => null,
                            'active_branch_name'  => null,
                            'active_branch_kode'  => null,
                            'active_company_id'   => $activeCompany->id,
                            'active_company_name' => $activeCompany->nama_perusahaan,
                        ]);
                    }
                }
                // =======================================

                // Database Log: Successful Login
                \App\Models\UserActivityLog::create([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'activity' => 'Login',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'Success',
                    'status_message' => 'Berhasil'
                ]);

                // Audit Log: successful login
                Log::info("User login successful.", [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'active_branch' => $activeBranch ? $activeBranch->nama_cabang : 'None',
                    'user_agent' => $request->userAgent()
                ]);

                return redirect()->intended('/dashbord');
            }

            // Increment rate limiter on failure
            RateLimiter::hit($rateKey, 180);
            $remaining = RateLimiter::retriesLeft($rateKey, 3);

            // Fetch user to classify specific failure reason
            $userObj = \App\Models\User::where($fieldType, $request->login)->first();
            $statusMsg = 'Password Salah';
            $userId = null;
            $username = $request->login;

            if (!$userObj) {
                $statusMsg = 'User Tidak Ditemukan';
            } elseif ($userObj->status !== 'Aktif') {
                $statusMsg = 'Akun Nonaktif';
                $userId = $userObj->id;
                $username = $userObj->username;
            } else {
                $userId = $userObj->id;
                $username = $userObj->username;
            }

            // Database Log: Failed Login
            \App\Models\UserActivityLog::create([
                'user_id' => $userId,
                'username' => $username,
                'activity' => 'Login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'Failed',
                'status_message' => $statusMsg
            ]);

            // Audit Log: failed login attempt
            Log::warning("Failed login attempt - " . $statusMsg, [
                'login' => $request->input('login'),
                'ip' => $request->ip(),
                'remaining_attempts' => $remaining,
                'user_agent' => $request->userAgent()
            ]);

            if ($remaining <= 0) {
                $seconds = RateLimiter::availableIn($rateKey);
                // Audit Log: lockout event (when hitting zero)
                Log::warning("Login lockout triggered.", [
                    'login' => $request->input('login'),
                    'ip' => $request->ip(),
                    'lockout_time_seconds' => $seconds,
                    'user_agent' => $request->userAgent()
                ]);
                return back()->withErrors([
                    'login' => 'Batas percobaan login habis. Akun Anda dikunci sementara.',
                ])->with('lockout_time', $seconds)->onlyInput('login');
            }

            // Jika kredensial salah
            return back()->withErrors([
                'login' => 'Email/Username atau password salah. Sisa percobaan: ' . $remaining,
            ])->onlyInput('login');

        } catch (\Exception $e) {
            // Jika ada error sistem (db down, kolom hilang, dll), log error-nya secara detail
            Log::error('Gagal memproses login akibat kesalahan sistem: '.$e->getMessage(), [
                'login' => $request->input('login'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString()
            ]);

            // Abort ke internal server error (500) untuk menampilkan custom errors/500.blade.php
            abort(500, 'Terjadi kesalahan sistem saat memproses login Anda.');
        }
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        $userId = Auth::id();
        $username = Auth::check() ? Auth::user()->username : 'Guest';

        if (Auth::check()) {
            // Database Log: Logout
            \App\Models\UserActivityLog::create([
                'user_id' => $userId,
                'username' => $username,
                'activity' => 'Logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'Success',
                'status_message' => 'Berhasil'
            ]);

            \Illuminate\Support\Facades\Cache::forget('user-session-' . $userId);
            \Illuminate\Support\Facades\Cache::forget('user-last-seen-' . $userId);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Audit Log: successful logout
        Log::info("User logged out.", [
            'user_id' => $userId,
            'username' => $username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('login', $request->has('expired') ? ['expired' => 1] : []);
    }
}
