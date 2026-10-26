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

        $search = $request->query('search');
        $activityFilter = $request->query('activity');
        $statusFilter = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = UserActivityLog::with('user:id,nama_lengkap,email')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($activityFilter) {
            $query->where('activity', $activityFilter);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $logs = $query->paginate($perPage)->withQueryString();

        foreach ($logs as $log) {
            $details = self::parseUserAgentDetails($log->user_agent);
            $log->parsed_device = $details['formatted'];
            $log->device_browser = $details['browser'];
            $log->device_os = $details['os'];
        }

        // Summary Stats
        $totalActivities = UserActivityLog::count();
        $totalSuccessLogin = UserActivityLog::where('activity', 'Login')->where('status', 'Success')->count();
        $totalFailedLogin = UserActivityLog::where('activity', 'Login')->where('status', 'Failed')->count();

        return view('user.activity_logs', compact(
            'logs',
            'appName',
            'search',
            'activityFilter',
            'statusFilter',
            'perPage',
            'totalActivities',
            'totalSuccessLogin',
            'totalFailedLogin'
        ));
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
     * Helper: Parse User Agent String into Detailed Browser and OS
     */
    public static function parseUserAgentDetails(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return ['browser' => 'Unknown', 'os' => 'Unknown', 'formatted' => 'Unknown Device'];
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

        return [
            'browser'   => $browser,
            'os'        => $os,
            'formatted' => "$browser ($os)",
        ];
    }

    /**
     * Helper: Parse User Agent String into Browser and OS
     */
    public static function parseUserAgent(?string $userAgent): string
    {
        return self::parseUserAgentDetails($userAgent)['formatted'];
    }

    /**
     * Delete / Prune Activity Logs permanently
     */
    public function prune(Request $request)
    {
        $user = auth()->user();

        // 🔒 Authorization check: Super Admin or has 'hapus-log-aktivitas-user' permission
        if (!$user->hasRole('Super Admin') && !$user->can('hapus-log-aktivitas-user')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus / membersihkan Audit Log Aktivitas Pengguna.');
        }

        $request->validate([
            'prune_mode' => 'required|in:30_days,60_days,90_days,custom_range,all',
            'start_date' => 'nullable|required_if:prune_mode,custom_range|date',
            'end_date'   => 'nullable|required_if:prune_mode,custom_range|date|after_or_equal:start_date',
        ], [
            'prune_mode.required' => 'Pilih mode pembersihan log.',
            'start_date.required_if' => 'Tanggal mulai wajib diisi untuk rentang tanggal.',
            'end_date.required_if' => 'Tanggal akhir wajib diisi untuk rentang tanggal.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $mode = $request->input('prune_mode');
        $deletedCount = 0;

        switch ($mode) {
            case '30_days':
                $cutoff = Carbon::now()->subDays(30);
                $deletedCount = UserActivityLog::where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log aktivitas yang lebih lama dari 30 hari.";
                break;

            case '60_days':
                $cutoff = Carbon::now()->subDays(60);
                $deletedCount = UserActivityLog::where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log aktivitas yang lebih lama dari 60 hari.";
                break;

            case '90_days':
                $cutoff = Carbon::now()->subDays(90);
                $deletedCount = UserActivityLog::where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log aktivitas yang lebih lama dari 90 hari.";
                break;

            case 'custom_range':
                $start = Carbon::parse($request->input('start_date'))->startOfDay();
                $end = Carbon::parse($request->input('end_date'))->endOfDay();
                $deletedCount = UserActivityLog::whereBetween('created_at', [$start, $end])->delete();
                $message = "Berhasil menghapus {$deletedCount} log aktivitas dari tanggal " . $start->format('d/m/Y') . " sampai " . $end->format('d/m/Y') . ".";
                break;

            case 'all':
                $deletedCount = UserActivityLog::count();
                UserActivityLog::truncate();
                $message = "Berhasil mengosongkan seluruh data log aktivitas ({$deletedCount} rekaman).";
                break;

            default:
                return redirect()->route('activity-logs.index')->with('error', 'Pilihan mode pembersihan log tidak valid.');
        }

        // Flush Dashboard cache
        Cache::forget('dashboard_activity_logs');

        return redirect()->route('activity-logs.index')->with('success', $message);
    }
}
