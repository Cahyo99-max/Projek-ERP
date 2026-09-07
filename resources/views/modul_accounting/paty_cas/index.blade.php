<x-layouts>
    <x-slot:title>Kas Kecil (Petty Cash) - {{ $activeEntityName }}</x-slot:title>

    <div class="container-fluid py-2 px-2 px-md-4 pc-container">
        
        <!-- =========================================================================
             1. HEADER TOOLBAR & ENTITAS ACTIVE PILL
             ========================================================================= -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="bi bi-house-door"></i> Dashboard</a></li>
                        <li class="breadcrumb-item text-muted">Akuntansi &amp; Keuangan</li>
                        <li class="breadcrumb-item active fw-bold text-success" aria-current="page">Kas Kecil (Petty Cash)</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0 text-body-emphasis fs-5 fs-md-4">Kas Kecil (Petty Cash)</h4>
                        <p class="text-muted small mb-0 d-none d-sm-block">Pencatatan pengeluaran harian, pengisian, dan klaim terintegrasi COA &amp; Tender.</p>
                    </div>
                </div>
            </div>

            <!-- Entitas Status Badge & Quick Action Header -->
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-between justify-content-md-end">
                <div class="badge bg-body border shadow-sm rounded-pill px-3 py-2 text-body d-flex align-items-center gap-2">
                    <span class="p-1 rounded-circle bg-success d-inline-block"></span>
                    <span class="fw-bold small">{{ $activeEntityName }}</span>
                    <span class="badge {{ $isCabang ? 'bg-info-subtle text-info border-info-subtle' : 'bg-primary-subtle text-primary border-primary-subtle' }} border rounded-pill" style="font-size: 10px;">
                        {{ $isCabang ? 'Cabang' : 'Pusat' }}
                    </span>
                </div>

                <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-none d-md-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
                    <i class="bi bi-plus-circle fs-6"></i>
                    <span>Tambah Transaksi</span>
                </button>
            </div>
        </div>

        <!-- =========================================================================
             2. DIGITAL WALLET CARD (HERO BENTO CARD SALDO KAS KECIL)
             ========================================================================= -->
        <div class="row g-3 mb-3">
            <!-- Main Hero Wallet Card (Gradient Fintech Look) -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 text-white rounded-4 shadow-sm position-relative overflow-hidden pc-wallet-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #064e3b 100%); min-height: 185px;">
                    <!-- Decorative Background Shapes -->
                    <div class="position-absolute end-0 top-0 opacity-10 pe-none" style="transform: translate(20%, -20%);">
                        <i class="bi bi-wallet2" style="font-size: 240px;"></i>
                    </div>

                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between position-relative z-1">
                        <!-- Top Row: Card Title & Eye Toggle -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white bg-opacity-20 backdrop-blur rounded-pill px-2.5 py-1 text-white small font-monospace">
                                    <i class="bi bi-shield-check text-warning me-1"></i> Saldo Kas Kecil
                                </span>
                                <span class="small text-white-50 font-monospace d-none d-sm-inline">
                                    ({{ $activeEntityName }})
                                </span>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none" id="btnToggleBalance" title="Sembunyikan/Tampilkan Saldo">
                                <i class="bi bi-eye fs-5" id="iconEyeBalance"></i>
                            </button>
                        </div>

                        <!-- Middle Row: Single Focused Balance Display -->
                        <div class="my-2">
                            <div class="text-white-50 small mb-0.5 font-monospace">Saldo Berjalan Saat Ini</div>
                            <h2 class="fw-bold font-monospace mb-0 text-white pc-balance-val fs-2 fs-md-1" data-nominal="{{ $saldoKasKecil }}">
                                Rp {{ number_format($saldoKasKecil, 0, ',', '.') }}
                            </h2>
                        </div>

                        <!-- Bottom Row: Quick Action Pill Buttons (Mobile Ready) -->
                        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top border-white border-opacity-10">
                            <button type="button" class="btn btn-sm btn-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center btn-quick-action" data-type="Pengeluaran">
                                <i class="bi bi-dash-circle"></i>
                                <span>+ Kas Keluar</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center btn-quick-action" data-type="Pemasukan" style="background-color: #059669; border-color: #059669;">
                                <i class="bi bi-plus-circle"></i>
                                <span>+ Pengisian</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center btn-quick-action" data-type="Klaim">
                                <i class="bi bi-receipt"></i>
                                <span>+ Klaim Bon</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat Metric Cards (Bulan Ini) -->
            <div class="col-12 col-xl-5">
                <div class="row g-2 g-md-3 h-100">
                    <!-- Pengeluaran Bulan Ini -->
                    <div class="col-6 col-xl-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small fw-semibold mb-0.5">Pengeluaran Bulan Ini</div>
                                    <h5 class="fw-bold font-monospace mb-0 text-danger fs-6 fs-md-5">- Rp {{ number_format($thisMonthOut, 0, ',', '.') }}</h5>
                                    <div class="small text-muted mt-0.5 font-monospace" style="font-size: 11px;"><i class="bi bi-arrow-down-right text-danger"></i> Biaya Operasional</div>
                                </div>
                                <div class="p-2.5 rounded-3 bg-danger-subtle text-danger d-inline-flex">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengisian / Inflow Bulan Ini -->
                    <div class="col-6 col-xl-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small fw-semibold mb-0.5">Pengisian Bulan Ini</div>
                                    <h5 class="fw-bold font-monospace mb-0 text-success fs-6 fs-md-5">+ Rp {{ number_format($thisMonthIn, 0, ',', '.') }}</h5>
                                    <div class="small text-muted mt-0.5 font-monospace" style="font-size: 11px;"><i class="bi bi-arrow-up-left text-success"></i> Top Up Kas Kecil</div>
                                </div>
                                <div class="p-2.5 rounded-3 bg-success-subtle text-success d-inline-flex">
                                    <i class="bi bi-piggy-bank fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             3. FILTER BAR & SEARCH
             ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 bg-body">
            <div class="card-body p-2.5 p-md-3">
                <form action="{{ route('accounting.petty-cash.index') }}" method="GET" id="formFilterPettyCash">
                    
                    <div class="row g-2 align-items-center">
                        <!-- Search Box -->
                        <div class="col-12 col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Cari no transaksi, pengaju, catatan..." value="{{ $search }}">
                                @if($search)
                                    <a href="{{ route('accounting.petty-cash.index', request()->except('search')) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
                                @endif
                                <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
                            </div>
                        </div>

                        <!-- Tender Filter Dropdown -->
                        <div class="col-6 col-md-3">
                            <select name="tender_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                                <option value="">-- Filter Tender --</option>
                                @foreach($tenders as $t)
                                    <option value="{{ $t->id }}" {{ $tenderFilter == $t->id ? 'selected' : '' }}>
                                        🎯 {{ $t->nomor_kontrak }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Type Chips -->
                        <div class="col-6 col-md-4 d-flex flex-wrap align-items-center justify-content-end gap-1.5">
                            <a href="{{ route('accounting.petty-cash.index', array_merge(request()->except('tipe', 'page'), ['tipe' => 'all'])) }}" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $tipeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}" style="font-size: 12px;">
                                Semua
                            </a>
                            <a href="{{ route('accounting.petty-cash.index', array_merge(request()->except('tipe', 'page'), ['tipe' => 'Pengeluaran'])) }}" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $tipeFilter === 'Pengeluaran' ? 'btn-danger' : 'btn-outline-danger' }}" style="font-size: 12px;">
                                🔴 Keluar
                            </a>
                            <a href="{{ route('accounting.petty-cash.index', array_merge(request()->except('tipe', 'page'), ['tipe' => 'Pemasukan'])) }}" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $tipeFilter === 'Pemasukan' ? 'btn-success' : 'btn-outline-success' }}" style="font-size: 12px;">
                                🟢 Pengisian
                            </a>
                            <a href="{{ route('accounting.petty-cash.index', array_merge(request()->except('tipe', 'page'), ['tipe' => 'Klaim'])) }}" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $tipeFilter === 'Klaim' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}" style="font-size: 12px;">
                                🟡 Klaim
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" data-bs-toggle="collapse" data-bs-target="#collapseDateFilter" title="Filter Tanggal">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Collapsible Date Filter -->
                    <div class="collapse mt-2 pt-2 border-top {{ request('start_date') && (request('start_date') != now()->startOfMonth()->toDateString()) ? 'show' : '' }}" id="collapseDateFilter">
                        <div class="row g-2 align-items-center">
                            <div class="col-6 col-md-3">
                                <label class="small text-muted" style="font-size: 11px;">Dari Tanggal:</label>
                                <input type="date" name="start_date" class="form-control form-control-sm rounded-3" value="{{ $startDate }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="small text-muted" style="font-size: 11px;">Sampai Tanggal:</label>
                                <input type="date" name="end_date" class="form-control form-control-sm rounded-3" value="{{ $endDate }}">
                            </div>
                            <div class="col-12 col-md-2 mt-auto">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">Terapkan Tanggal</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <!-- =========================================================================
             4. MOBILE TRANSACTION CARDS FEED (OPTIMIZED FOR HP)
             ========================================================================= -->
        <div class="d-block d-lg-none mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="small fw-bold text-muted text-uppercase">Daftar Transaksi ({{ $transactions->total() }})</span>
                <span class="small text-muted">Hal {{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</span>
            </div>

            @forelse($transactions as $trx)
                @php
                    $isExpense = ($trx->tipe === 'Pengeluaran' || $trx->tipe === 'Klaim');
                    $badgeTipeClass = $trx->tipe === 'Pengeluaran' ? 'bg-danger-subtle text-danger border-danger-subtle' : ($trx->tipe === 'Pemasukan' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle');
                    
                    // Category Icon
                    $catLower = strtolower($trx->kategori);
                    $iconClass = 'bi-wallet2';
                    $iconBg = 'bg-primary-subtle text-primary';
                    if (str_contains($catLower, 'bbm') || str_contains($catLower, 'bensin') || str_contains($catLower, 'transport')) {
                        $iconClass = 'bi-fuel-pump';
                        $iconBg = 'bg-danger-subtle text-danger';
                    } elseif (str_contains($catLower, 'atk') || str_contains($catLower, 'kantor')) {
                        $iconClass = 'bi-paperclip';
                        $iconBg = 'bg-info-subtle text-info';
                    } elseif (str_contains($catLower, 'konsumsi') || str_contains($catLower, 'makan')) {
                        $iconClass = 'bi-cup-hot';
                        $iconBg = 'bg-warning-subtle text-warning';
                    } elseif (str_contains($catLower, 'kurir') || str_contains($catLower, 'ongkir')) {
                        $iconClass = 'bi-box-seam';
                        $iconBg = 'bg-secondary-subtle text-secondary';
                    } elseif (str_contains($catLower, 'pengisian')) {
                        $iconClass = 'bi-piggy-bank';
                        $iconBg = 'bg-success-subtle text-success';
                    }
                @endphp
                <div class="card border-0 shadow-sm rounded-4 mb-2.5 bg-body pc-trx-mobile-card" data-id="{{ $trx->id }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <!-- Left: Icon & Info -->
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="p-2.5 rounded-4 {{ $iconBg }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bi {{ $iconClass }} fs-5"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="fw-bold text-body-emphasis small">{{ $trx->kategori }}</span>
                                        <span class="badge {{ $badgeTipeClass }} border rounded-pill px-2 py-0.5 font-monospace" style="font-size: 10px;">{{ $trx->tipe }}</span>
                                    </div>
                                    <p class="small text-muted mb-0 font-monospace" style="font-size: 11px;">
                                        {{ date('d M Y', strtotime($trx->tanggal)) }} &bull; {{ $trx->nomor_transaksi }}
                                    </p>
                                    @if($trx->keterangan)
                                        <p class="small text-body-secondary mb-1 text-truncate" style="max-width: 200px; font-size: 11px;">
                                            {{ $trx->keterangan }}
                                        </p>
                                    @endif

                                    <!-- Tag Tender (Jika ada) -->
                                    @if($trx->tender)
                                        <div class="mt-1">
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill font-monospace" style="font-size: 10px;">
                                                🎯 {{ $trx->tender->nomor_kontrak }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Nominal & Action -->
                            <div class="text-end flex-shrink-0">
                                <span class="fw-bold font-monospace d-block {{ $isExpense ? 'text-danger' : 'text-success' }} fs-6">
                                    {{ $isExpense ? '-' : '+' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                </span>

                                @if($trx->lampiran_nota)
                                    <a href="{{ asset('storage/' . $trx->lampiran_nota) }}" target="_blank" class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 text-decoration-none mt-1 d-inline-block" onclick="event.stopPropagation();">
                                        <i class="bi bi-paperclip"></i> Nota
                                    </a>
                                @endif

                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-muted btn-detail-pc" data-id="{{ $trx->id }}" style="font-size: 11px;">
                                        Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-body">
                    <i class="bi bi-inbox fs-1 text-muted opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-muted mb-1">Belum Ada Transaksi Kas Kecil</h6>
                    <p class="small text-muted mb-3">Tidak ditemukan catatan kas kecil untuk {{ $activeEntityName }}.</p>
                    <div>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-1.5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash">
                            <i class="bi bi-plus-circle me-1"></i> Buat Transaksi Baru
                        </button>
                    </div>
                </div>
            @endforelse

            <!-- Mobile Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <!-- =========================================================================
             5. DESKTOP & TABLET DATA TABLE
             ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 d-none d-lg-block bg-body">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text text-success"></i> Riwayat Pembukuan Kas Kecil &bull; {{ $activeEntityName }}
                </h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill font-monospace px-3 py-1">Total: {{ $transactions->total() }} Data</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="desktopTablePettyCash">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Kategori &amp; Keterangan</th>
                                <th>Akun Kas (COA)</th>
                                <th>Lawan COA (Beban)</th>
                                <th>Tender/Proyek</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Bukti</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                @php
                                    $isExpense = ($trx->tipe === 'Pengeluaran' || $trx->tipe === 'Klaim');
                                @endphp
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-body-emphasis">
                                        {{ $trx->nomor_transaksi }}
                                        <div class="small fw-normal text-muted">{{ $trx->nama_pengaju ?: 'Admin' }}</div>
                                    </td>
                                    <td class="font-monospace text-muted small">
                                        {{ date('d/m/Y', strtotime($trx->tanggal)) }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-body-emphasis small">{{ $trx->kategori }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 220px;">{{ $trx->keterangan ?: '-' }}</div>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 11px;">
                                            {{ $trx->coaKas->code ?? '-' }}
                                        </span>
                                        <div class="text-truncate" style="max-width: 140px; font-size: 11px;">{{ $trx->coaKas->name ?? '-' }}</div>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 11px;">
                                            {{ $trx->lawanCoa->code ?? '-' }}
                                        </span>
                                        <div class="text-truncate" style="max-width: 140px; font-size: 11px;">{{ $trx->lawanCoa->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if($trx->tender)
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill font-monospace px-2 py-1" style="font-size: 11px;">
                                                🎯 {{ $trx->tender->nomor_kontrak }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace fw-bold {{ $isExpense ? 'text-danger' : 'text-success' }}">
                                        {{ $isExpense ? '-' : '+' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($trx->lampiran_nota)
                                            <a href="{{ asset('storage/' . $trx->lampiran_nota) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle p-1.5 lh-1" title="Lihat Struk">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info btn-detail-pc" data-id="' . $trx->id . '" title="Detail Voucher" onclick="loadPettyCashDetail({{ $trx->id }})"><i class="bi bi-eye"></i></button>
                                            <button type="button" class="btn btn-outline-warning btn-edit-pc" data-id="{{ $trx->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="btn btn-outline-danger btn-delete-pc" data-id="{{ $trx->id }}" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                        Tidak ada data kas kecil untuk {{ $activeEntityName }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Desktop Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="small text-muted">Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi</span>
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>

    <!-- FLOATING ACTION BUTTON (FAB) -->
    <button type="button" class="btn btn-success rounded-circle shadow-lg d-flex d-md-none align-items-center justify-content-center pc-fab-btn" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" title="Input Kas Kecil">
        <i class="bi bi-plus-lg fs-4"></i>
    </button>

    <!-- PUSH MODALS -->
    @push('modals')
        @include('modul_accounting.paty_cas._modal_input')
        @include('modul_accounting.paty_cas._modal_edit')
        @include('modul_accounting.paty_cas._modal_detail')
    @endpush

    <!-- PUSH CUSTOM STYLES & SCRIPTS -->
    @push('styles')
    <style>
        .pc-container {
            max-width: 1400px;
        }
        .pc-wallet-hero {
            box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.3), 0 8px 10px -6px rgba(6, 78, 59, 0.2);
        }
        .backdrop-blur {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .pc-trx-mobile-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }
        .pc-trx-mobile-card:active {
            transform: scale(0.98);
        }
        .pc-fab-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 58px;
            height: 58px;
            z-index: 1040;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: none;
        }
        .pc-type-btn {
            transition: all 0.2s ease;
        }
        @media (max-width: 576px) {
            .pc-container {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Toggle Show / Hide Balance
            let isBalanceHidden = false;
            $('#btnToggleBalance').on('click', function() {
                isBalanceHidden = !isBalanceHidden;
                $('.pc-balance-val').each(function() {
                    const rawNominal = $(this).data('nominal') || 0;
                    if (isBalanceHidden) {
                        $(this).text('Rp ••••••••');
                        $('#iconEyeBalance').removeClass('bi-eye').addClass('bi-eye-slash');
                    } else {
                        $(this).text('Rp ' + new Intl.NumberFormat('id-ID').format(rawNominal));
                        $('#iconEyeBalance').removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                });
            });

            // 2. Quick Action Buttons on Wallet Header
            $('.btn-quick-action').on('click', function() {
                const targetType = $(this).data('type');
                if (targetType === 'Pengeluaran') {
                    $('#tipe_pengeluaran').prop('checked', true).trigger('change');
                } else if (targetType === 'Pemasukan') {
                    $('#tipe_pemasukan').prop('checked', true).trigger('change');
                } else if (targetType === 'Klaim') {
                    $('#tipe_klaim').prop('checked', true).trigger('change');
                }
                $('#modalInputPettyCash').modal('show');
            });

            // 3. Format Rupiah Input Helper
            function formatRupiah(value) {
                const numberString = value.replace(/[^,\d]/g, '').toString();
                const split = numberString.split(',');
                const sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    const separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            }

            $('#input_nominal, #edit_input_nominal').on('input', function() {
                const formatted = formatRupiah($(this).val());
                $(this).val(formatted);
            });

            // 4. Quick Nominal Chips (+20k, +50k, etc.)
            $('.btn-quick-nominal').on('click', function() {
                const addAmount = parseInt($(this).data('amount')) || 0;
                let currentVal = parseInt($('#input_nominal').val().replace(/[^0-9]/g, '')) || 0;
                currentVal += addAmount;
                $('#input_nominal').val(formatRupiah(currentVal.toString())).trigger('input');
            });

            $('.btn-clear-nominal').on('click', function() {
                $('#input_nominal').val('').trigger('input');
            });

            // 5. Category Chips Click Handler
            $('.btn-cat-chip').on('click', function() {
                const cat = $(this).data('cat');
                $('#input_kategori').val(cat);
                if (cat === 'Pengisian Kas Kecil') {
                    $('#tipe_pemasukan').prop('checked', true).trigger('change');
                }
            });

            // 6. Dynamic COA Label Adjuster when Tipe changes
            $('input[name="tipe"]').on('change', function() {
                const tipe = $(this).val();
                if (tipe === 'Pemasukan') {
                    $('#labelCoaKas').html('Akun Kas Kecil (Penerima) <span class="text-danger">*</span>');
                    $('#labelLawanCoa').html('Akun Sumber Dana (Kas Besar / Bank) <span class="text-danger">*</span>');
                } else {
                    $('#labelCoaKas').html('Akun Kas Kecil (Sumber) <span class="text-danger">*</span>');
                    $('#labelLawanCoa').html('Akun Biaya / Beban (Lawan COA) <span class="text-danger">*</span>');
                }
            });

            // 7. Image Receipt Live Preview
            $('#input_lampiran_nota').on('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        $('#imgReceiptPreview').attr('src', evt.target.result);
                        $('#previewReceiptContainer').removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#previewReceiptContainer').addClass('d-none');
                }
            });

            $('#btnRemoveReceiptPreview').on('click', function() {
                $('#input_lampiran_nota').val('');
                $('#previewReceiptContainer').addClass('d-none');
            });

            // 8. Submit Form Input Petty Cash
            $('#formInputPettyCash').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = $('#btnSubmitPettyCash');

                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                $.ajax({
                    url: "{{ route('accounting.petty-cash.store') }}",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i><span>Simpan &amp; Jurnal</span>');
                        if (response.success) {
                            $('#modalInputPettyCash').modal('hide');
                            $('#formInputPettyCash')[0].reset();
                            $('#previewReceiptContainer').addClass('d-none');
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: response.message,
                                confirmButtonColor: '#059669',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i><span>Simpan &amp; Jurnal</span>');
                        let errMsg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan!',
                            text: errMsg,
                            confirmButtonColor: '#e11d48'
                        });
                    }
                });
            });

            // 9. Click Mobile Card or Table Row to Open Detail
            $('.pc-trx-mobile-card').on('click', function() {
                const id = $(this).data('id');
                loadPettyCashDetail(id);
            });

            $(document).on('click', '.btn-detail-pc', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                loadPettyCashDetail(id);
            });

            // Load Detail Function
            window.loadPettyCashDetail = function(id) {
                $('#modalDetailPettyCash').modal('show');
                $('#detail_nomor_header').text('Memuat...');
                $('#detail_nominal').text('Rp ...');

                $.ajax({
                    url: `/accounting/petty-cash/${id}`,
                    method: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            const d = res.data;
                            const isExpense = (d.tipe === 'Pengeluaran' || d.tipe === 'Klaim');

                            $('#detail_nomor_header').text(d.nomor_transaksi);
                            $('#detail_nomor').text(d.nomor_transaksi);
                            $('#detail_company_name').text(d.branch ? d.branch.nama_cabang : (d.company ? d.company.nama_perusahaan : 'PT Pusat'));
                            
                            if (d.tender) {
                                $('#detail_tender_container').removeClass('d-none');
                                $('#detail_tender_title').text(d.tender.nomor_kontrak + ' - ' + d.tender.nama_tender);
                            } else {
                                $('#detail_tender_container').addClass('d-none');
                            }

                            // Nominal
                            $('#detail_tipe_label').text('TOTAL ' + d.tipe.toUpperCase());
                            $('#detail_nominal').removeClass('text-danger text-success')
                                .addClass(isExpense ? 'text-danger' : 'text-success')
                                .text((isExpense ? '- ' : '+ ') + 'Rp ' + new Intl.NumberFormat('id-ID').format(d.nominal));
                            
                            $('#detail_tanggal').text('Tanggal: ' + (d.tanggal ? new Date(d.tanggal).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-'));
                            $('#detail_kategori').text(d.kategori);
                            $('#detail_pengaju').text(d.nama_pengaju || '-');
                            $('#detail_creator').text(d.creator ? (d.creator.nama_lengkap || d.creator.username) : 'System');
                            $('#detail_keterangan').text(d.keterangan || 'Tidak ada catatan tambahan.');

                            // COA Breakdown
                            $('#detail_coa_kas').text((d.coa_kas ? d.coa_kas.code + ' - ' + d.coa_kas.name : '-'));
                            $('#detail_lawan_coa').text((d.lawan_coa ? d.lawan_coa.code + ' - ' + d.lawan_coa.name : '-'));
                            
                            // Jurnal breakdown
                            if (d.jurnal) {
                                $('#detail_nomor_jurnal').text(d.jurnal.nomor_jurnal);
                                let tbodyHtml = '';
                                if (d.jurnal.details && d.jurnal.details.length > 0) {
                                    d.jurnal.details.forEach(function(item) {
                                        tbodyHtml += `
                                            <tr>
                                                <td><span class="font-monospace fw-bold">${item.account ? item.account.code : ''}</span> ${item.account ? item.account.name : ''}</td>
                                                <td class="text-end font-monospace">${item.debit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.debit) : '-'}</td>
                                                <td class="text-end font-monospace">${item.kredit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.kredit) : '-'}</td>
                                            </tr>
                                        `;
                                    });
                                }
                                $('#detail_jurnal_tbody').html(tbodyHtml);
                            } else {
                                $('#detail_nomor_jurnal').text('Tidak ada jurnal');
                                $('#detail_jurnal_tbody').html('<tr><td colspan="3" class="text-center text-muted">Jurnal otomatis belum tercatat.</td></tr>');
                            }

                            // Receipt Photo
                            if (res.lampiran_url) {
                                $('#detail_receipt_photo_container').removeClass('d-none');
                                $('#detail_receipt_img').attr('src', res.lampiran_url);
                                $('#detail_receipt_download_btn').attr('href', res.lampiran_url);
                            } else {
                                $('#detail_receipt_photo_container').addClass('d-none');
                            }

                            $('#btnEditFromDetail').attr('data-id', d.id);
                            $('#btnDeleteFromDetail').attr('data-id', d.id);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memuat detail transaksi.', 'error');
                    }
                });
            };

            // 10. Edit Handler
            $(document).on('click', '.btn-edit-pc, #btnEditFromDetail', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id') || $(this).data('id');
                $('#modalDetailPettyCash').modal('hide');

                $.ajax({
                    url: `/accounting/petty-cash/${id}/edit`,
                    method: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            const d = res.data;
                            $('#edit_pc_id').val(d.id);
                            $('#edit_pc_nomor_display').text(d.nomor_transaksi);
                            
                            if (d.tipe === 'Pengeluaran') $('#edit_tipe_pengeluaran').prop('checked', true);
                            else if (d.tipe === 'Pemasukan') $('#edit_tipe_pemasukan').prop('checked', true);
                            else if (d.tipe === 'Klaim') $('#edit_tipe_klaim').prop('checked', true);

                            $('#edit_input_nominal').val(formatRupiah(parseInt(d.nominal).toString()));
                            $('#edit_input_tanggal').val(d.tanggal ? d.tanggal.substring(0, 10) : '');
                            $('#edit_input_nama_pengaju').val(d.nama_pengaju || '');
                            $('#edit_input_kategori').val(d.kategori);
                            $('#edit_input_coa_kas_id').val(d.coa_kas_id);
                            $('#edit_input_lawan_coa_id').val(d.lawan_coa_id);
                            $('#edit_input_tender_id').val(d.tender_id || '');
                            $('#edit_input_keterangan').val(d.keterangan || '');

                            if (res.lampiran_url) {
                                $('#edit_existingReceiptContainer').removeClass('d-none');
                                $('#edit_linkExistingReceipt').attr('href', res.lampiran_url);
                            } else {
                                $('#edit_existingReceiptContainer').addClass('d-none');
                            }

                            $('#modalEditPettyCash').modal('show');
                        }
                    }
                });
            });

            // Submit Edit Form
            $('#formEditPettyCash').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit_pc_id').val();
                const formData = new FormData(this);
                const submitBtn = $('#btnUpdatePettyCash');

                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...');

                $.ajax({
                    url: `/accounting/petty-cash/${id}`,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg fs-5"></i><span>Update Transaksi</span>');
                        if (res.success) {
                            $('#modalEditPettyCash').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: res.message,
                                confirmButtonColor: '#059669',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg fs-5"></i><span>Update Transaksi</span>');
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal update transaksi.', 'error');
                    }
                });
            });

            // 11. Delete Handler
            $(document).on('click', '.btn-delete-pc, #btnDeleteFromDetail', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id') || $(this).data('id');
                
                Swal.fire({
                    title: 'Hapus Transaksi Kas Kecil?',
                    text: 'Transaksi dan jurnal akuntansi terkait akan dihapus secara permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/accounting/petty-cash/${id}`,
                            method: 'DELETE',
                            success: function(res) {
                                $('#modalDetailPettyCash').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    html: res.message,
                                    confirmButtonColor: '#059669',
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus.', 'error');
                            }
                        });
                    }
                });
            });

            // 12. Print Voucher Function
            window.printPettyCashVoucher = function() {
                const printContents = document.getElementById('receiptPrintArea').innerHTML;
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Voucher Kas Kecil</title>');
                printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
                printWindow.document.write('</head><body class="p-4">');
                printWindow.document.write(printContents);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            };
        });
    </script>
    @endpush
</x-layouts>
