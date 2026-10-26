 <header class="top-navbar">
     <style>
         .company-header-badge {
             background-color: #f1f5f9;
             border: 1px solid #e2e8f0;
         }
         .company-header-text {
             color: #1e293b;
         }

         /* ===== DARK MODE NAVBAR & BRANCH SWITCHER OVERRIDES ===== */
         [data-bs-theme="dark"] #branchDropdownMenu,
         html[data-theme="dark"] #branchDropdownMenu,
         .dark-mode #branchDropdownMenu {
             background-color: #0f172a !important;
             border: 1px solid #334155 !important;
             box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
         }

         [data-bs-theme="dark"] .company-header-badge,
         html[data-theme="dark"] .company-header-badge,
         .dark-mode .company-header-badge {
             background-color: #1e293b !important;
             border-color: #334155 !important;
         }

         [data-bs-theme="dark"] .company-header-text,
         html[data-theme="dark"] .company-header-text,
         .dark-mode .company-header-text {
             color: #60a5fa !important;
         }

         [data-bs-theme="dark"] #branchDropdownBtn,
         html[data-theme="dark"] #branchDropdownBtn,
         .dark-mode #branchDropdownBtn {
             background: linear-gradient(135deg, #1e293b, #0f172a) !important;
             border-color: #334155 !important;
             color: #f8fafc !important;
         }

         [data-bs-theme="dark"] #branchDropdownBtn .text-muted,
         html[data-theme="dark"] #branchDropdownBtn .text-muted,
         .dark-mode #branchDropdownBtn .text-muted {
             color: #94a3b8 !important;
         }

         [data-bs-theme="dark"] #branchDropdownMenu .dropdown-item,
         html[data-theme="dark"] #branchDropdownMenu .dropdown-item,
         .dark-mode #branchDropdownMenu .dropdown-item {
             color: #cbd5e1 !important;
         }

         [data-bs-theme="dark"] #branchDropdownMenu .dropdown-item:hover,
         html[data-theme="dark"] #branchDropdownMenu .dropdown-item:hover,
         .dark-mode #branchDropdownMenu .dropdown-item:hover {
             background-color: #1e293b !important;
             color: #ffffff !important;
         }

         [data-bs-theme="dark"] #branchDropdownMenu .dropdown-item.active,
         html[data-theme="dark"] #branchDropdownMenu .dropdown-item.active,
         .dark-mode #branchDropdownMenu .dropdown-item.active {
             background-color: rgba(37, 99, 235, 0.25) !important;
             color: #60a5fa !important;
         }

         [data-bs-theme="dark"] #branchDropdownMenu .text-muted,
         html[data-theme="dark"] #branchDropdownMenu .text-muted,
         .dark-mode #branchDropdownMenu .text-muted {
             color: #94a3b8 !important;
         }
     </style>
     <div class="d-flex align-items-center">
         <button class="btn-icon me-3" id="sidebarToggle">
             <i class="bi bi-list fs-4"></i>
         </button>

         <!-- Search Box -->
         <div class="d-none d-md-flex align-items-center navbar-search rounded-pill px-3 py-1">
             <i class="bi bi-search navbar-search-icon"></i>
             <input type="text" class="navbar-search-input border-0 bg-transparent shadow-none w-100 ms-2"
                 placeholder="{{ __('messages.search_placeholder') }}" style="min-width: 250px;"
                 autocomplete="off">
         </div>
     </div>

     <div class="d-flex align-items-center gap-2">

         {{-- ===== BRANCH SWITCHER ===== --}}
          @auth
          @php
              $user = auth()->user();
              $assignedBranches  = $user->branches()->where('branches.status', 'Active')->get();
              $assignedCompanies = $user->companies()->where('companies.status', 'Active')->get();
              $userBranchCount   = $assignedBranches->count();
              $userCompanyCount  = $assignedCompanies->count();
              $userOptionCount   = $userBranchCount + $userCompanyCount;

              $activeBranchId    = session('active_branch_id');
              $activeBranchName  = session('active_branch_name');
              $activeBranchKode  = session('active_branch_kode');
              $activeCompanyId   = session('active_company_id');
              $activeCompanyName = session('active_company_name');

              if (!$activeCompanyId && !$activeCompanyName) {
                  $defaultComp = $assignedCompanies->where('pivot.is_default', 1)->first() ?? $assignedCompanies->first();
                  if (!$defaultComp && $user->hasRole('Super Admin')) {
                      $defaultComp = \App\Models\Company::where('status', 'Active')->first();
                  }
                  if ($defaultComp) {
                      session([
                          'active_company_id'   => $defaultComp->id,
                          'active_company_name' => $defaultComp->nama_perusahaan,
                      ]);
                      $activeCompanyId   = $defaultComp->id;
                      $activeCompanyName = $defaultComp->nama_perusahaan;
                  }
              }
          @endphp

          @if($activeCompanyName)
          <div class="dropdown" id="branchSwitcherDropdown">
              <button
                  class="btn btn-sm d-flex align-items-center gap-2 rounded-pill px-3 py-2 border"
                  style="background: linear-gradient(135deg, #e0eafc, #cfdef3); border-color: #a8c3e8 !important; color: #1e3a5f;"
                  {!! $userOptionCount > 1 ? 'data-bs-toggle="dropdown"' : '' !!}
                  title="{{ $activeCompanyName && $activeBranchName ? ($activeCompanyName . ' - ' . $activeBranchName) : ($activeCompanyName ?? $activeBranchName) }}"
                  id="branchDropdownBtn">
                  <i class="bi bi-building-fill" style="color: #2563eb;"></i>
                  <div class="d-none d-sm-flex flex-column text-start" style="line-height: 1.1;">
                      @if($activeBranchName)
                          <span class="text-muted fw-bold" style="font-size: 0.65rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                              {{ $activeCompanyName }}
                          </span>
                          <span class="fw-semibold" style="font-size: 0.78rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                              {{ $activeBranchName }}
                          </span>
                      @else
                          <span class="fw-semibold" style="font-size: 0.8rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                              {{ $activeCompanyName }}
                          </span>
                      @endif
                  </div>
                  @if($activeBranchKode)
                      <span class="badge rounded-pill" style="background-color: #2563eb; font-size: 0.65rem;">{{ $activeBranchKode }}</span>
                  @else
                      <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.65rem;">Pusat</span>
                  @endif

                  @if($userOptionCount > 1)
                  <i class="bi bi-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                  @else
                  <i class="bi bi-lock-fill ms-1" style="font-size: 0.65rem; opacity: 0.5;"></i>
                  @endif
              </button>

              @if($userOptionCount > 1)
              <div class="dropdown-menu shadow border-0 mt-2 p-2" style="min-width: 260px; max-height: 380px; overflow-y: auto; border-radius: 14px;" id="branchDropdownMenu">
                  <div class="px-2 pb-2 mb-1 border-bottom">
                      <p class="text-muted fw-semibold mb-0" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">
                          <i class="bi bi-arrow-left-right me-1"></i> Pilih PT & Cabang
                      </p>
                  </div>
                  <div id="branchList">
                      <!-- Diisi oleh JavaScript -->
                      <div class="text-center py-2">
                          <span class="spinner-border spinner-border-sm text-primary"></span>
                      </div>
                  </div>
              </div>
              @endif
          </div>
          @else
          <span class="badge bg-warning text-dark rounded-pill px-3 py-2 d-none d-sm-inline">
              <i class="bi bi-exclamation-triangle me-1"></i> Belum ada PT / Cabang
          </span>
          @endif
          @endauth
         {{-- ===== END BRANCH SWITCHER ===== --}}

         <div class="vr mx-1 text-muted d-none d-sm-block"></div>



         <!-- Theme Toggle -->
         <button class="btn-icon" id="themeToggle" data-bs-toggle="tooltip" title="Toggle Dark Mode">
             <i class="bi bi-moon fs-5"></i>
         </button>

         <!-- Fullscreen Toggle -->
         <button class="btn-icon d-none d-sm-inline-flex" id="fullscreenToggle" data-bs-toggle="tooltip"
             title="Fullscreen">
             <i class="bi bi-arrows-fullscreen fs-6"></i>
         </button>

          <!-- Notifications -->
          @php
              $notifications = [];

              if (auth()->check()) {
                  $user = auth()->user();
                  $activeBranchId = session('active_branch_id');
                  $activeCompanyId = session('active_company_id');

                  // 1. Low Stock Alert (Staff Gudang, Inventori & Super Admin)
                  if ($user->can('lihat-barang') || $user->can('lihat-dashboard-inventori') || $user->can('lihat-stok-gudang') || $user->can('lihat-dashboard') || $user->hasRole(['Super Admin', 'Warehouse', 'Supervisor Warehouse', 'Admin', 'Owner', 'Director'])) {
                      try {
                          $gudangQuery = \App\Models\MasterGudang::query()->where('status', 'Active');
                          if (!empty($activeBranchId)) {
                              $gudangQuery->where('cabang_id', $activeBranchId);
                          } else {
                              $gudangQuery->whereNull('cabang_id');
                              if (!empty($activeCompanyId)) {
                                  $gudangQuery->where('company_id', $activeCompanyId);
                              }
                          }
                          $gudangIds = $gudangQuery->pluck('id')->toArray();

                          if (!empty($gudangIds)) {
                              $lowStockCount = \App\Models\StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                                  ->whereIn('stok_gudangs.gudang_id', $gudangIds)
                                  ->where(function($q) {
                                      $q->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
                                        ->where('master_barang.minimum_stok', '>', 0);
                                  })
                                  ->whereNull('master_barang.deleted_at')
                                  ->count();

                              if ($lowStockCount > 0) {
                                  $notifications[] = [
                                      'type' => 'danger',
                                      'icon' => 'bi-exclamation-triangle-fill',
                                      'title' => 'Stok Menipis (' . $lowStockCount . ' SKU)',
                                      'message' => 'Terdapat ' . $lowStockCount . ' item barang mendekati / di bawah batas buffer minimum.',
                                      'link' => route('stock-overview.index')
                                  ];
                              }
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 2. Goods Receipt Pending (Warehouse & Super Admin)
                  if ($user->can('lihat-goods-receipt') || $user->can('lihat-penerimaan-barang') || $user->can('lihat-dashboard-inventori') || $user->hasRole(['Super Admin', 'Warehouse', 'Supervisor Warehouse', 'Admin'])) {
                      try {
                          $pendingGr = \Illuminate\Support\Facades\DB::table('goods_receipts')
                              ->whereNull('deleted_at')
                              ->whereIn('status', ['Draft', 'Pending'])
                              ->count();

                          if ($pendingGr > 0) {
                              $notifications[] = [
                                  'type' => 'info',
                                  'icon' => 'bi-box-arrow-in-down',
                                  'title' => 'Penerimaan Barang (GR)',
                                  'message' => $pendingGr . ' dokumen penerimaan barang belum difinalisasi.',
                                  'link' => route('gr.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 3. Goods Issue Pending (Warehouse & Super Admin)
                  if ($user->can('lihat-goods-issue') || $user->can('lihat-pengeluaran-barang') || $user->can('lihat-dashboard-inventori') || $user->hasRole(['Super Admin', 'Warehouse', 'Supervisor Warehouse', 'Admin'])) {
                      try {
                          $pendingGi = \Illuminate\Support\Facades\DB::table('goods_issues')
                              ->whereNull('deleted_at')
                              ->whereIn('status', ['Draft', 'Pending'])
                              ->count();

                          if ($pendingGi > 0) {
                              $notifications[] = [
                                  'type' => 'warning',
                                  'icon' => 'bi-box-arrow-up-right',
                                  'title' => 'Pengeluaran Barang (GI)',
                                  'message' => $pendingGi . ' dokumen pengeluaran barang menunggu konfirmasi.',
                                  'link' => route('gi.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 4. Warehouse Transfers Pending (Warehouse & Super Admin)
                  if ($user->can('lihat-transfer') || $user->can('lihat-transfer-gudang') || $user->can('lihat-dashboard-inventori') || $user->hasRole(['Super Admin', 'Warehouse', 'Supervisor Warehouse', 'Admin'])) {
                      try {
                          $pendingTransfers = \Illuminate\Support\Facades\DB::table('warehouse_transfers')
                              ->whereNull('deleted_at')
                              ->whereIn('status', ['Pending', 'Draft'])
                              ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId))
                              ->count();

                          if ($pendingTransfers > 0) {
                              $notifications[] = [
                                  'type' => 'primary',
                                  'icon' => 'bi-arrow-left-right',
                                  'title' => 'Transfer Gudang Aktif',
                                  'message' => $pendingTransfers . ' transfer antar gudang menunggu proses tindak lanjut.',
                                  'link' => route('transfer.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 5. Stock Opname Active (Warehouse & Super Admin)
                  if ($user->can('lihat-stock-opname') || $user->can('lihat-dashboard-inventori') || $user->hasRole(['Super Admin', 'Warehouse', 'Supervisor Warehouse', 'Admin'])) {
                      try {
                          $pendingOpname = \Illuminate\Support\Facades\DB::table('stock_opnames')
                              ->whereNull('deleted_at')
                              ->whereIn('status', ['Draft', 'Pending'])
                              ->count();

                          if ($pendingOpname > 0) {
                              $notifications[] = [
                                  'type' => 'info',
                                  'icon' => 'bi-clipboard-check-fill',
                                  'title' => 'Stock Opname Berjalan',
                                  'message' => $pendingOpname . ' sesi stock opname aktif membutuhkan audit fisik.',
                                  'link' => route('opname.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 6. PR Pending Review (Accounting, Buyer, Purchasing & Super Admin)
                  if ($user->can('lihat-pr') || $user->can('lihat-dashboard-purchasing') || $user->hasRole(['Super Admin', 'Purchasing', 'Admin', 'Director', 'Owner'])) {
                      try {
                          $pendingPrCount = \Illuminate\Support\Facades\DB::table('purchase_requisitions')
                              ->whereNull('deleted_at')
                              ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'))
                              ->where('status', 'Pending')
                              ->count();

                          if ($pendingPrCount > 0) {
                              $notifications[] = [
                                  'type' => 'warning',
                                  'icon' => 'bi-file-earmark-text-fill',
                                  'title' => 'Pengajuan PR Pending',
                                  'message' => $pendingPrCount . ' pengajuan PR menunggu persetujuan approval.',
                                  'link' => route('pr.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 7. Purchase Order Pending / Draft (Purchasing & Super Admin)
                  if ($user->can('lihat-po') || $user->can('lihat-dashboard-purchasing') || $user->hasRole(['Super Admin', 'Purchasing', 'Admin', 'Director', 'Owner'])) {
                      try {
                          $pendingPoCount = \Illuminate\Support\Facades\DB::table('purchase_orders')
                              ->whereNull('deleted_at')
                              ->whereIn('status', ['Pending', 'Draft'])
                              ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId))
                              ->count();

                          if ($pendingPoCount > 0) {
                              $notifications[] = [
                                  'type' => 'primary',
                                  'icon' => 'bi-cart-check-fill',
                                  'title' => 'Purchase Order (PO)',
                                  'message' => $pendingPoCount . ' PO belum dikonfirmasi / release.',
                                  'link' => route('po.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 8. Sales / Tender Alert (Sales & Super Admin)
                  if ($user->can('lihat-tender') || $user->hasRole(['Super Admin', 'Sales', 'Admin'])) {
                      try {
                          $pendingTenders = \Illuminate\Support\Facades\DB::table('tenders')
                              ->whereNull('deleted_at')
                              ->where('status', 'Pending')
                              ->count();

                          if ($pendingTenders > 0) {
                              $notifications[] = [
                                  'type' => 'info',
                                  'icon' => 'bi-briefcase-fill',
                                  'title' => 'Penawaran Tender Aktif',
                                  'message' => $pendingTenders . ' tender aktif membutuhkan tindak lanjut.',
                                  'link' => route('tender.index')
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }

                  // 9. Security Warning: Failed Login Attempts (IT Admin & Super Admin)
                  if ($user->can('lihat-hak-akses') || $user->hasRole(['Super Admin', 'Admin'])) {
                      try {
                          $failedCount = \App\Models\UserActivityLog::where('activity', 'Login')
                              ->where('status', 'Failed')
                              ->where('created_at', '>=', now()->subHours(24))
                              ->count();

                          if ($failedCount > 0) {
                              $notifications[] = [
                                  'type' => 'danger',
                                  'icon' => 'bi-shield-exclamation',
                                  'title' => 'Percobaan Login Gagal',
                                  'message' => $failedCount . ' kali login gagal dalam 24 jam terakhir.',
                                  'link' => '/activity-logs'
                              ];
                          }
                      } catch (\Throwable $e) {}
                  }
              }
          @endphp

          <div class="dropdown">
              <button class="btn-icon position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Pusat Notifikasi">
                  <i class="bi bi-bell fs-5"></i>
                  @if(count($notifications) > 0)
                      <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                  @endif
              </button>
              <div class="dropdown-menu dropdown-menu-end shadow border-0"
                  style="width: 340px; border-radius: 14px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, rgba(226, 232, 240, 0.8)) !important;">
                  <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-color, #e2e8f0) !important;">
                      <h6 class="mb-0 fw-bold" style="color: var(--text-main, #1e293b); font-size: 0.92rem;">
                          <i class="bi bi-bell me-1 text-primary"></i> Notifikasi
                      </h6>
                      @if(count($notifications) > 0)
                          <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;">{{ count($notifications) }} Perlu Tindakan</span>
                      @else
                          <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.7rem; border: 1px solid rgba(16, 185, 129, 0.25);">Terkendali</span>
                      @endif
                  </div>
                  <div class="p-2" style="max-height: 340px; overflow-y: auto;">
                      @forelse($notifications as $notif)
                          @php
                              $typeColorMap = [
                                  'primary' => ['bg' => 'rgba(37, 99, 235, 0.15)', 'color' => '#2563eb'],
                                  'danger'  => ['bg' => 'rgba(239, 68, 68, 0.15)', 'color' => '#ef4444'],
                                  'warning' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'color' => '#f59e0b'],
                                  'info'    => ['bg' => 'rgba(14, 165, 233, 0.15)', 'color' => '#0ea5e9'],
                                  'success' => ['bg' => 'rgba(16, 185, 129, 0.15)', 'color' => '#10b981'],
                              ];
                              $style = $typeColorMap[$notif['type']] ?? $typeColorMap['primary'];
                          @endphp
                          <a href="{{ $notif['link'] }}" class="dropdown-item d-flex align-items-start p-2.5 rounded-3 mb-1 text-wrap" style="transition: all 0.2s ease;">
                              <div class="icon-box me-3 d-flex align-items-center justify-content-center" style="width:36px; height:36px; min-width: 36px; border-radius: 10px; background: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                  <i class="bi {{ $notif['icon'] }} fs-6"></i>
                              </div>
                              <div style="min-width: 0; flex: 1;">
                                  <p class="mb-0 fw-semibold text-truncate" style="color: var(--text-main, #1e293b); font-size: 0.82rem;">{{ $notif['title'] }}</p>
                                  <small class="text-wrap d-block" style="color: var(--text-muted, #64748b); font-size: 0.75rem; line-height: 1.35; margin-top: 2px;">{{ $notif['message'] }}</small>
                              </div>
                          </a>
                      @empty
                          <div class="text-center py-4 px-3">
                              <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-circle" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                                  <i class="bi bi-shield-check fs-4"></i>
                              </div>
                              <p class="mb-1 fw-semibold" style="color: var(--text-main, #1e293b); font-size: 0.85rem;">Sistem Terkendali</p>
                              <small class="d-block" style="color: var(--text-muted, #64748b); font-size: 0.75rem; line-height: 1.4;">
                                  Tidak ada antrean tugas mendesak atau peringatan stok yang memerlukan tindakan Anda saat ini.
                              </small>
                              <div class="mt-3 pt-2 border-top text-center" style="border-color: var(--border-color, #e2e8f0) !important; font-size: 0.72rem; color: var(--text-muted, #94a3b8);">
                                  <i class="bi bi-person-badge me-1 text-primary"></i> Sesi: <strong>{{ auth()->user()->getRoleNames()->first() ?? 'Staff' }}</strong> &bull; {{ session('active_branch_name') ?? session('active_company_name') ?? 'Pusat' }}
                              </div>
                          </div>
                      @endforelse
                  </div>
                  @if(count($notifications) > 0)
                      @can('lihat-log-aktivitas-user')
                      <div class="p-2 border-top text-center" style="border-color: var(--border-color, #e2e8f0) !important;">
                          <a href="/activity-logs" class="text-primary fw-medium text-decoration-none" style="font-size: 0.75rem;">
                              <i class="bi bi-clock-history me-1"></i> Lihat Riwayat Log Aktivitas
                          </a>
                      </div>
                      @endcan
                  @endif
              </div>
          </div>

         <div class="vr mx-2 text-muted"></div>

         <!-- User Profile -->
         <div class="dropdown">
             <button class="btn border-0 bg-transparent d-flex align-items-center p-0" data-bs-toggle="dropdown">
                 @auth
                 <img src="{{ auth()->user()->foto ? asset(auth()->user()->foto) : (auth()->user()->jenis_kelamin == 'P' ? asset('gambar/perempuan.jpg') : asset('gambar/laki.jpg')) }}"
                     alt="User" class="rounded-circle border border-2" width="36" height="36" style="object-fit: cover;">
                 <div class="d-none d-md-block text-start ms-2">
                     <h6 class="mb-0 fw-semibold fs-7" style="color: var(--text-main);">{{ auth()->user()->inisial ?: (auth()->user()->nama_lengkap ?? 'User') }}</h6>
                     <small class="text-muted" style="font-size: 0.7rem;">{{ auth()->user()->getRoleNames()->first() ?? 'No Role' }}</small>
                 </div>
                 @else
                 <img src="{{ asset('gambar/laki.jpg') }}" alt="User"
                     class="rounded-circle" width="36" height="36">
                 @endauth
                 <i class="bi bi-chevron-down ms-2 fs-7 text-muted d-none d-md-block"></i>
             </button>
             <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                 style="border-radius: var(--border-radius-md);">
                 <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                 <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="bi bi-gear me-2"></i> Account Settings</a>
                 </li>
                 <li>
                     <hr class="dropdown-divider">
                 </li>
                 <li>
                     <form method="POST" action="{{ route('logout') }}">
                         @csrf
                         <button type="submit" class="dropdown-item py-2 text-danger">
                             <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                         </button>
                     </form>
                 </li>
             </ul>
         </div>



     </div>

     {{-- ===== BRANCH SWITCHER JAVASCRIPT ===== --}}
     @push('scripts')
     <script>
     (function() {
         function escapeHtml(str) {
             if (!str) return '';
             return String(str)
                 .replace(/&/g, '&amp;')
                 .replace(/</g, '&lt;')
                 .replace(/>/g, '&gt;')
                 .replace(/"/g, '&quot;')
                 .replace(/'/g, '&#039;');
         }

         const dropdownMenu = document.getElementById('branchDropdownMenu');
         const branchList   = document.getElementById('branchList');
         const dropdownBtn  = document.getElementById('branchDropdownBtn');

         if (!dropdownMenu || !dropdownBtn) return;

         // Load daftar PT & cabang saat dropdown dibuka
         dropdownBtn.addEventListener('show.bs.dropdown', function() {
             branchList.innerHTML = `
                 <div class="text-center py-2">
                     <span class="spinner-border spinner-border-sm text-primary"></span>
                 </div>`;

             fetch('/branch/list', {
                 headers: { 'X-Requested-With': 'XMLHttpRequest' }
             })
             .then(r => r.json())
             .then(data => {
                 if (!data.success || !data.companies || !data.companies.length) {
                     branchList.innerHTML = `<div class="text-muted text-center py-2" style="font-size:0.8rem;">Tidak ada PT/Cabang tersedia.</div>`;
                     return;
                 }

                 let html = '';
                 data.companies.forEach((company, cIdx) => {
                     const isPusatActive = (company.id == data.active_company_id && !data.active_branch_id);

                     if (cIdx > 0) {
                         html += `<div class="dropdown-divider my-2"></div>`;
                     }

                     // Header PT
                     html += `
                         <div class="px-2.5 py-1.5 rounded-2 mb-1 company-header-badge">
                             <div class="fw-bold company-header-text" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                 <i class="bi bi-building me-1 text-primary"></i> ${escapeHtml(company.nama_perusahaan)}
                             </div>
                         </div>`;

                     // Pilihan PT Pusat Level
                     html += `
                         <button class="dropdown-item rounded-3 py-1.5 px-3 mb-1 d-flex align-items-center gap-2 entity-switch-btn ${isPusatActive ? 'active fw-bold' : ''}"
                                 data-company-id="${company.id}"
                                 data-branch-id=""
                                 ${isPusatActive ? 'disabled' : ''}>
                             <i class="bi bi-diagram-3${isPusatActive ? '-fill' : ''} text-primary" style="font-size: 0.85rem;"></i>
                             <div class="text-start flex-grow-1">
                                 <div class="fw-semibold" style="font-size: 0.8rem;">Pusat (${escapeHtml(company.kode_perusahaan || 'PT')})</div>
                                 <div class="text-muted" style="font-size: 0.68rem;">Akses Tingkat Pusat / PT Induk</div>
                             </div>
                             ${isPusatActive ? '<i class="bi bi-check-circle-fill text-success ms-auto"></i>' : ''}
                         </button>`;

                     // Pilihan Cabang-Cabang di bawah PT ini
                     if (company.branches && company.branches.length) {
                         company.branches.forEach(branch => {
                             const isBranchActive = (company.id == data.active_company_id && branch.id == data.active_branch_id);
                             html += `
                                 <button class="dropdown-item rounded-3 py-1.5 px-3 mb-1 d-flex align-items-center gap-2 entity-switch-btn ms-2 ${isBranchActive ? 'active fw-bold' : ''}"
                                         data-company-id="${company.id}"
                                         data-branch-id="${branch.id}"
                                         ${isBranchActive ? 'disabled' : ''} style="width: calc(100% - 0.5rem);">
                                     <i class="bi bi-geo-alt${isBranchActive ? '-fill' : ''} text-info" style="font-size: 0.85rem;"></i>
                                     <div class="text-start flex-grow-1">
                                         <div class="fw-semibold" style="font-size: 0.8rem;">${escapeHtml(branch.nama_cabang)}</div>
                                         <div class="text-muted" style="font-size: 0.68rem;">Cabang • ${escapeHtml(branch.kode_cabang)}</div>
                                     </div>
                                     ${isBranchActive ? '<i class="bi bi-check-circle-fill text-success ms-auto"></i>' : ''}
                                 </button>`;
                         });
                     }
                 });

                 branchList.innerHTML = html;

                 // Event listener untuk setiap tombol switcher
                 document.querySelectorAll('.entity-switch-btn').forEach(btn => {
                     btn.addEventListener('click', function() {
                         const companyId = this.dataset.companyId;
                         const branchId  = this.dataset.branchId || null;
                         switchEntity(companyId, branchId);
                     });
                 });
             })
             .catch((err) => {
                 console.error(err);
                 branchList.innerHTML = `<div class="text-danger text-center py-2" style="font-size:0.8rem;">Gagal memuat daftar.</div>`;
             });
         }, { once: false });

         function switchEntity(companyId, branchId) {
             const bsDropdown = bootstrap.Dropdown.getInstance(dropdownBtn);
             if (bsDropdown) bsDropdown.hide();

             fetch('/branch/switch', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                     'X-Requested-With': 'XMLHttpRequest',
                 },
                 body: JSON.stringify({ company_id: companyId, branch_id: branchId }),
             })
             .then(r => r.json())
             .then(data => {
                 if (data.success) {
                     Swal.fire({
                         icon: 'success',
                         title: 'Akses Diubah!',
                         html: data.message,
                         timer: 1500,
                         timerProgressBar: true,
                         showConfirmButton: false,
                         position: 'center',
                     }).then(() => {
                         window.location.reload();
                     });
                 } else {
                     Swal.fire({
                         icon: 'error',
                         title: 'Gagal!',
                         text: data.message,
                         confirmButtonText: 'OK',
                     });
                 }
             })
             .catch(() => {
                 Swal.fire({
                     icon: 'error',
                     title: 'Error Jaringan',
                     text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
                     confirmButtonText: 'OK',
                 });
             });
         }
     })();
     </script>
     @endpush
     {{-- ===== END BRANCH SWITCHER JAVASCRIPT ===== --}}

 </header>
