<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserActivityLogController extends Controller
{
    /**
     * Display full Activity Logs view (Paginated)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 🔒 Authorization check: Super Admin or has 'lihat-log-aktivitas-user' permission
        if (!$user->hasRole('Super Admin') && !$user->can('lihat-log-aktivitas-user')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Audit Log Aktivitas Pengguna.');
        }

        $appName = config('app.name', 'ERP Pastac');

        $logs = UserActivityLog::with('user:id,nama_lengkap,email')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        foreach ($logs as $log) {
            $log->parsed_device = self::parseUserAgent($log->user_agent);
        }

        return view('user.activity_logs', compact('logs', 'appName'));
    }

    /**
     * AJAX Endpoint: User Activity Logs for Dashboard Widget
     */
    public function getActivityLogsAjax(Request $request)
    {
        $user = auth()->user();

        // 🔒 Authorization check
        if (!$user->hasRole('Super Admin') && !$user->can('lihat-log-aktivitas-user')) {
            return response()->json([], 403);
        }

        $logs = Cache::remember('dashboard_activity_logs', 60, function () {
            return UserActivityLog::with('user:id,nama_lengkap')
                ->select(['id', 'user_id', 'username', 'activity', 'ip_address', 'user_agent', 'status', 'status_message', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'waktu'          => $log->created_at->format('d/m/Y H:i'),
                        'user'           => $log->user ? $log->user->nama_lengkap : ($log->username ?: 'Guest'),
                        'aktivitas'      => $log->activity,
                        'ip_address'     => $log->ip_address,
                        'device'         => UserActivityLogController::parseUserAgent($log->user_agent),
                        'status'         => $log->status,
                        'status_message' => $log->status_message,
                    ];
                });
        });

        return response()->json($logs);
    }

    /**
     * AJAX Endpoint: Active Users Monitor for Dashboard Widget
     */
    public function getActiveUsersAjax(Request $request)
    {
        $user = auth()->user();

        // 🔒 Authorization check
        if (!$user->hasRole('Super Admin') && !$user->can('lihat-monitor-aktif')) {
            return response()->json([], 403);
        }

        $users = Cache::remember('dashboard_active_users_list', 30, function () {
            return User::with(['position:id,nama_jabatan'])
                ->where('status', 'Aktif')
                ->select(['id', 'nama_lengkap', 'foto', 'login_terakhir', 'karyawan_id'])
                ->get()
                ->map(function ($u) {
                    $lastSeen = Cache::get('user-last-seen-' . $u->id);
                    $isOnline = $lastSeen && $lastSeen->gt(now()->subMinutes(5));
                    return [
                        'id'             => $u->id,
                        'nama_lengkap'   => $u->nama_lengkap,
                        'foto'           => $u->foto ? asset('storage/' . $u->foto) : null,
                        'jabatan'        => $u->position->nama_jabatan ?? 'Staff',
                        'is_online'      => $isOnline,
                        'last_seen_text' => $lastSeen
                            ? $lastSeen->diffForHumans()
                            : ($u->login_terakhir ? Carbon::parse($u->login_terakhir)->diffForHumans() : 'Belum aktif'),
                        'last_seen_title' => $lastSeen
                            ? $lastSeen->format('d M Y H:i:s')
                            : ($u->login_terakhir ? Carbon::parse($u->login_terakhir)->format('d M Y H:i:s') : 'Belum pernah aktif'),
                    ];
                })
                ->sortByDesc('is_online')
                ->values();
        });

        return response()->json($users);
    }

    /**
     * Helper: Parse User Agent String into Browser and OS
     */
    public static function parseUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown Device';
        }

        $os = 'Unknown';
        $browser = 'Unknown';

        // Detect OS
        if (preg_match('/windows|win32/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        }

        // Detect Browser
        if (preg_match('/edge|edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return "$browser ($os)";
    }
}
