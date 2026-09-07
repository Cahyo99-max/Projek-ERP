<x-layouts title="Log Aktivitas User - ">

    <!-- Breadcrumb & Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Log Aktivitas User</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none text-muted">{{ $appName }}</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Pengaturan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log Aktivitas</li>
                </ol>
            </nav>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="/dashbord" class="btn btn-primary shadow-sm"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-journal-text text-primary fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size: 0.9rem;">Total Aktivitas</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $logs->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-shield-check text-success fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size: 0.9rem;">Login Berhasil</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ \App\Models\UserActivityLog::where('activity', 'Login')->where('status', 'Success')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #ffd3b6 0%, #ffaaa5 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-shield-exclamation text-danger fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size: 0.9rem;">Login Gagal</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ \App\Models\UserActivityLog::where('activity', 'Login')->where('status', 'Failed')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card p-4 border-0 rounded-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary fs-4"></i>
                <h5 class="fw-bold mb-0">Riwayat Autentikasi Pengguna</h5>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Waktu</th>
                        <th class="py-3">User</th>
                        <th class="py-3">Aktivitas</th>
                        <th class="py-3">IP Address</th>
                        <th class="py-3">Device / Browser</th>
                        <th class="py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-7" style="width: 32px; height: 32px;">
                                        {{ mb_strtoupper(mb_substr($log->user ? $log->user->nama_lengkap : ($log->username ?: 'G'), 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $log->user ? $log->user->nama_lengkap : ($log->username ?: 'Guest') }}</div>
                                        @if($log->user)
                                            <div class="text-muted fs-8">{{ $log->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->activity === 'Login')
                                    <span class="badge bg-primary-soft rounded-pill px-2.5 py-1 fw-medium"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
                                @else
                                    <span class="badge bg-secondary-soft rounded-pill px-2.5 py-1 fw-medium"><i class="bi bi-box-arrow-right me-1"></i> Logout</span>
                                @endif
                            </td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>
                                <span class="fs-7 text-muted" title="{{ $log->user_agent }}">
                                    {{ $log->parsed_device }}
                                </span>
                            </td>
                            <td>
                                @if($log->status === 'Success')
                                    <span class="badge bg-success-soft rounded-pill px-3 py-1 fw-semibold">
                                        🟢 Berhasil
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft rounded-pill px-3 py-1 fw-semibold" title="{{ $log->status_message }}">
                                        🔴 {{ $log->status_message ?: 'Gagal' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-2 d-block mb-3"></i>
                                Belum ada log aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Pagination Links -->
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <span class="fs-8 text-muted">
                Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data
            </span>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>

</x-layouts>
