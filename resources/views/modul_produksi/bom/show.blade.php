<x-layouts title="Detail BOM — {{ $bom->kode_bom }}">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/bom_produksi.css') }}?v={{ time() }}">
<style>
/* ─── BOM SHOW PAGE CUSTOM STYLES ───────────────────────── */
.bom-show-header {
    background: var(--card-bg);
    border-bottom: 1px solid var(--border-color);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

/* Breadcrumb touch-friendly scroll */
.bom-breadcrumb-scroll {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 2px;
}
.bom-breadcrumb-scroll::-webkit-scrollbar {
    display: none;
}

/* Action buttons styling */
.bom-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 9999px;
    padding: 0 14px;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.bom-action-btn-icon {
    width: 36px;
    min-width: 36px;
    padding: 0;
}

@media (max-width: 767.98px) {
    .bom-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        width: 100%;
        margin-top: 12px;
    }
    .bom-header-actions .btn-back {
        flex: 0 0 auto;
    }
    .bom-header-actions .btn-primary-action {
        flex: 1 1 auto;
    }
    .bom-header-actions .btn-secondary-action {
        flex: 1 1 calc(50% - 28px);
    }
    .bom-header-actions .btn-icon-action {
        flex: 0 0 36px;
    }
}

.bom-show-nav-tabs {
    border-bottom: none;
    margin-bottom: -1px;
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    gap: 4px;
}
.bom-show-nav-tabs::-webkit-scrollbar {
    display: none;
}
.bom-show-nav-tabs .nav-link {
    color: var(--text-muted);
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    padding: 10px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
    background: transparent;
    white-space: nowrap;
}
.bom-show-nav-tabs .nav-link:hover {
    color: var(--text-main);
}
.bom-show-nav-tabs .nav-link.active {
    color: var(--accent-color) !important;
    border-bottom: 3px solid var(--accent-color) !important;
    background: transparent !important;
}
.bom-show-nav-tabs .nav-link i { font-size: 14px; }

/* Sidebar cards */
.sidebar-card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    background: var(--card-bg);
    margin-bottom: 1rem;
}
.sidebar-card-header {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.sidebar-card-body { padding: 14px; }
.sidebar-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 5px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 13px;
    gap: 8px;
}
.sidebar-info-row:last-child { border-bottom: none; }
.sidebar-info-label { color: var(--text-muted); font-size: 11.5px; min-width: 90px; }
.sidebar-info-value { font-weight: 600; color: var(--text-main); text-align: right; }

/* Komponen tabel */
.bom-comp-table thead th {
    background: var(--table-header-bg) !important;
    color: var(--table-header-text) !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .4px !important;
    padding: 9px 10px !important;
    white-space: nowrap;
}
.bom-comp-table tbody td {
    padding: 8px 10px !important;
    vertical-align: middle;
    font-size: 13px;
    color: var(--text-main);
    border-color: var(--border-color) !important;
}
.bom-comp-table tbody tr:hover { background: var(--table-hover-bg) !important; }

/* Kalkulator section */
.kalkulator-section {
    background: var(--bg-light);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    padding: 20px;
    margin-bottom: 1rem;
}

/* Tree BOM */
.bom-tree ul { list-style: none; padding-left: 22px; margin: 0; }
.bom-tree li { position: relative; padding: 3px 0; }
.bom-tree li::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 11px;
    width: 12px;
    height: 1px;
    background: var(--border-color);
}
.bom-tree li::after {
    content: '';
    position: absolute;
    left: -15px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: var(--border-color);
}
.bom-tree > ul > li::after { top: 11px; }
.bom-tree-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    transition: background .15s;
}
.bom-tree-item:hover { background: var(--table-hover-bg); }
.bom-tree-root {
    background: linear-gradient(135deg,rgba(2,132,199,.1),rgba(16,185,129,.08));
    border: 1px solid rgba(2,132,199,.25);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
}
</style>
@endpush

<div class="container-fluid p-0">

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- HEADER SECTION                                                --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="bom-show-header px-3 px-md-4 pt-3 pt-md-4 pb-0">

        {{-- Title Row --}}
        <div class="row align-items-start pb-3">
            <div class="col-12 col-md-7">
                {{-- Badges Row --}}
                <div class="d-flex align-items-center gap-1.5 gap-md-2 mb-2 flex-wrap">
                    {{-- Status Badge --}}
                    @if($bom->status === 'Draft')
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-pencil-square me-1"></i>Draft</span>
                    @elseif($bom->status === 'Pending')
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-clock-history me-1"></i>Pending Approval</span>
                    @elseif($bom->status === 'Approved' || $bom->status === 'Active')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>
                    @elseif($bom->status === 'Rejected')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>
                    @elseif($bom->status === 'Completed' || $bom->status === 'Selesai')
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-check-all me-1"></i>Selesai</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;"><i class="bi bi-dash-circle me-1"></i>{{ $bom->status }}</span>
                    @endif

                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;">
                        <i class="bi bi-tag me-1"></i>{{ $bom->tipe_bom }}
                    </span>
                    @if($bom->tender)
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size:11px;" title="{{ $bom->tender->nama_tender }}">
                            <i class="bi bi-file-earmark-text me-1"></i>Tender: {{ $bom->tender->nama_tender }}
                        </span>
                    @endif
                </div>

                {{-- Judul Utama BOM --}}
                <h1 class="fw-bold text-theme-main mb-2 fs-4 fs-md-3" style="line-height: 1.25;">{{ $bom->nama_bom }}</h1>

                {{-- Baris Detail: Kode BOM & Barang Jadi --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2 text-theme-muted" style="font-size: 12px;">
                    <div class="d-inline-flex align-items-center gap-1.5 bg-body-tertiary px-2.5 py-0.5 rounded-pill border border-secondary border-opacity-15">
                        <i class="bi bi-upc-scan text-primary"></i>
                        <span class="font-monospace text-theme-main fw-bold">{{ $bom->kode_bom }}</span>
                    </div>
                    <span class="opacity-30 d-none d-sm-inline">|</span>
                    <div class="d-inline-flex align-items-center gap-1.5 text-truncate" style="max-width: 100%;">
                        <i class="bi bi-box-seam text-info"></i>
                        @if($bom->barangJadi)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace px-2 py-0.5 rounded">{{ $bom->barangJadi->kode_barang }}</span>
                            <span class="text-theme-main fw-semibold text-truncate">{{ $bom->barangJadi->nama_barang }}</span>
                        @else
                            <span class="text-theme-muted">-</span>
                        @endif
                    </div>
                </div>

                {{-- Creator, Updater, Approver & Timestamp --}}
                <div class="d-flex flex-wrap align-items-center gap-2 text-theme-muted" style="font-size: 11.5px;">
                    <span><i class="bi bi-person me-1"></i>Disusun oleh <strong class="text-theme-main">{{ $bom->creator ? $bom->creator->nama_lengkap : 'System' }}</strong></span>
                    @if($bom->updater && $bom->updated_at && $bom->updated_at->gt($bom->created_at))
                        <span class="opacity-40">·</span>
                        <span><i class="bi bi-pencil-fill text-warning me-1"></i>Diedit oleh <strong class="text-warning">{{ $bom->updater->nama_lengkap }}</strong> <span class="text-theme-muted">({{ $bom->updated_at->format('d/m/Y H:i') }})</span></span>
                    @endif
                    @if($bom->approver)
                        <span class="opacity-40">·</span>
                        <span><i class="bi bi-patch-check-fill text-success me-1"></i>Disetujui oleh <strong class="text-success">{{ $bom->approver->nama_lengkap }}</strong> @if($bom->approved_at) <span class="text-theme-muted">({{ $bom->approved_at->format('d/m/Y H:i') }})</span> @endif</span>
                    @endif
                    <span class="opacity-40">·</span>
                    <span><i class="bi bi-calendar3 me-1"></i>{{ $bom->created_at ? $bom->created_at->format('d/m/Y H:i') : '-' }}</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="col-12 col-md-5 mt-2 mt-md-0">
                <div class="bom-header-actions justify-content-start justify-content-md-end align-items-center">
                    <a href="{{ route('produksi.bom.index') }}" class="btn btn-outline-secondary bom-action-btn btn-back">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>

                    {{-- Tombol Ajukan (Saat status Draft atau Rejected) --}}
                    @if(in_array($bom->status, ['Draft', 'Rejected']) && ($isCreator || $isSupervisor))
                        <button type="button" class="btn btn-primary bom-action-btn btn-primary-action shadow-sm" onclick="sendPendingBom({{ $bom->id }})">
                            <i class="bi bi-send-fill me-1"></i> Ajukan ke Pending
                        </button>
                    @endif

                    {{-- Tombol Approval (Hanya saat status Pending untuk User yang memiliki hak akses approve-bom / Super Admin / Owner) --}}
                    @if($bom->status === 'Pending' && ($isSupervisor || auth()->user()->can('approve-bom')))
                        <button type="button" class="btn btn-success bom-action-btn btn-primary-action shadow-sm" onclick="approveBom({{ $bom->id }})">
                            <i class="bi bi-check-circle-fill me-1"></i> Setujui
                        </button>
                        <button type="button" class="btn btn-danger bom-action-btn btn-secondary-action shadow-sm" onclick="rejectBom({{ $bom->id }})">
                            <i class="bi bi-x-circle-fill me-1"></i> Tolak
                        </button>
                    @endif

                    {{-- Tombol Tandai Selesai (Saat status Approved / Active) --}}
                    @if(in_array($bom->status, ['Approved', 'Active']) && ($isSupervisor || auth()->user()->can('approve-bom')))
                        <button type="button" class="btn btn-info text-white bom-action-btn btn-primary-action shadow-sm" onclick="completeBom({{ $bom->id }})">
                            <i class="bi bi-check-all me-1"></i> Selesai
                        </button>
                    @endif

                    {{-- Tombol Aktifkan Kembali (Saat status Completed / Selesai / Inactive - Khusus Super Admin / Owner) --}}
                    @if(in_array($bom->status, ['Completed', 'Selesai', 'Inactive']) && $isSupervisor)
                        <button type="button" class="btn btn-success bom-action-btn btn-primary-action shadow-sm" onclick="reactivateBom({{ $bom->id }})">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Aktifkan
                        </button>
                    @endif

                    {{-- Tombol Edit BOM --}}
                    @if($canEdit)
                        <button type="button" class="btn btn-warning text-white bom-action-btn btn-secondary-action shadow-sm" onclick="editBom({{ $bom->id }})" style="background-color: #d97706; border-color: #d97706;">
                            <i class="bi bi-pencil me-1"></i> Edit BOM
                        </button>
                    @endif

                    {{-- Tombol Cetak PDF BOM --}}
                    <a href="{{ route('produksi.bom.print', $bom->id) }}" target="_blank" class="btn btn-outline-info bom-action-btn btn-secondary-action">
                        <i class="bi bi-printer me-1"></i> Cetak
                    </a>

                    {{-- Tombol Hapus BOM --}}
                    @if($canDelete)
                        <button type="button" class="btn btn-outline-danger bom-action-btn bom-action-btn-icon btn-icon-action" onclick="deleteBom({{ $bom->id }})" title="Hapus Resep BOM">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Navigation (Touch-scrollable on mobile) --}}
        <ul class="nav bom-show-nav-tabs no-scrollbar" id="bomShowTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-komponen" type="button" role="tab">
                    <i class="bi bi-boxes me-1"></i> Komponen BOM
                    <span class="badge bg-info bg-opacity-25 text-info ms-1.5 rounded-pill" style="font-size:10px;">{{ $bom->items->count() }}</span>
                </button>
            </li>
            @if($canViewHpp)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kalkulator" type="button" role="tab">
                        <i class="bi bi-calculator me-1"></i> Kalkulator Produksi
                    </button>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> Informasi Lainnya
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Riwayat BOM
                </button>
            </li>
        </ul>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT + SIDEBAR                                        --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="row g-0">

        {{-- ── MAIN CONTENT (col-8) ── --}}
        <div class="col-12 col-xl-8 p-3 p-md-4">
            <div class="tab-content" id="bomShowTabContent">

                {{-- ╔══════════════════════════════════╗
                     ║  TAB 1: KOMPONEN BOM             ║
                     ╚══════════════════════════════════╝ --}}
                <div class="tab-pane fade show active" id="tab-komponen" role="tabpanel">

                    {{-- Sub-header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-theme-main mb-0">
                                <i class="bi bi-list-check me-2 text-primary"></i>
                                Daftar Komponen
                            </h6>
                            <small class="text-theme-muted">
                                Komponen bahan baku yang dibutuhkan untuk memproduksi
                                <strong>{{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</strong>
                                {{ $bom->barangJadi ? $bom->barangJadi->nama_barang : '' }}.
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-xs py-2.5 px-3 mb-3 d-flex align-items-center justify-content-between rounded-3" style="background-color: rgba(13, 202, 240, 0.1); border-left: 4px solid #0dcaf0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calculator-fill text-info fs-5"></i>
                            <div class="small">
                                <span class="fw-bold text-info">Rumus Kalkulasi Resep BOM:</span> 
                                <span class="text-theme-main"><strong>Qty Bruto (Qty + Scrap)</strong> = Qty per Pcs × (1 + Scrap / 100)</span> &nbsp;|&nbsp; 
                                <span class="text-theme-main"><strong>Subtotal HPP</strong> = Qty Bruto × Estimasi HPP</span>
                            </div>
                        </div>
                        <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-gear-fill me-1"></i>Otomatis</span>
                    </div>

                    {{-- TABEL KOMPONEN --}}
                    <div class="table-responsive rounded-3 border mb-4">
                        <table class="table bom-comp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:38px;">No</th>
                                    <th style="width:105px;">Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th style="width:160px;">Spesifikasi</th>
                                    <th class="text-center" style="width:80px;">Satuan</th>
                                    <th class="text-end" style="width:110px;">Qty per 1 Pcs</th>
                                    <th class="text-center" style="width:100px;">Scrap (%)</th>
                                    <th class="text-end" style="width:120px;">Qty + Scrap</th>
                                    <th class="text-end" style="width:150px;">Estimasi HPP</th>
                                    <th class="text-end" style="min-width:180px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @forelse($bom->items as $idx => $item)
                                    @php
                                        $grossQty = $item->jumlah_kebutuhan * (1 + ($item->waste_percent / 100));
                                        $itemSubtotal = $grossQty * $item->estimasi_harga_satuan;
                                        $subtotal += $itemSubtotal;
                                    @endphp
                                    <tr>
                                        <td class="text-center text-theme-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 font-monospace" style="font-size:11px;">
                                                {{ $item->barangMentah ? $item->barangMentah->kode_barang : '-' }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-theme-main">{{ $item->barangMentah ? $item->barangMentah->nama_barang : '-' }}</td>
                                        <td class="text-theme-muted small">{{ $item->catatan ?: '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-theme-main border-0">
                                                {{ $item->satuan ? $item->satuan->nama_satuan : '-' }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace fw-semibold" style="white-space:nowrap;">
                                            {{ rtrim(rtrim(number_format($item->jumlah_kebutuhan, 4, ',', '.'), '0'), ',') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $wasteVal = (float)$item->waste_percent;
                                                $wasteFmt = ($wasteVal == (int)$wasteVal) ? (int)$wasteVal : rtrim(rtrim(number_format($wasteVal, 2, ',', '.'), '0'), ',');
                                            @endphp
                                            @if($wasteVal > 0)
                                                <span class="badge px-2.5 py-1 rounded-pill fw-extrabold" style="background-color: #f59e0b !important; color: #0f172a !important; border: 1px solid #fbbf24; font-size: 11px;">{{ $wasteFmt }}%</span>
                                            @else
                                                <span class="text-theme-muted" style="font-size: 11px;">0%</span>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace text-warning fw-semibold" style="white-space:nowrap;">
                                            {{ rtrim(rtrim(number_format($grossQty, 4, ',', '.'), '0'), ',') }}
                                        </td>
                                        <td class="text-end font-monospace text-theme-muted" style="white-space:nowrap;">
                                            @if($canViewHpp)
                                                Rp {{ number_format($item->estimasi_harga_satuan, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-primary" style="white-space:nowrap; min-width:180px;">
                                            @if($canViewHpp)
                                                Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-theme-muted">
                                            <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                                            Belum ada komponen bahan baku.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="9" class="text-end text-theme-muted pe-3" style="white-space:nowrap;">Subtotal Bahan Baku:</td>
                                    <td class="text-end font-monospace text-primary fw-bold pe-3" style="white-space:nowrap; min-width:180px;">
                                        @if($canViewHpp)
                                            Rp {{ number_format($subtotalBahan, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-end text-theme-muted pe-3" style="white-space:nowrap;">Biaya Tenaga Kerja / CMT:</td>
                                    <td class="text-end font-monospace text-theme-main fw-bold pe-3" style="white-space:nowrap; min-width:180px;">
                                        @if($canViewHpp)
                                            Rp {{ number_format($bom->biaya_tenaga_kerja, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-end text-theme-muted pe-3" style="white-space:nowrap;">Biaya Overhead Produksi:</td>
                                    <td class="text-end font-monospace text-theme-main fw-bold pe-3" style="white-space:nowrap; min-width:180px;">
                                        @if($canViewHpp)
                                            Rp {{ number_format($bom->biaya_overhead, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                <tr style="border-top: 2px solid #10b981 !important; background: rgba(16,185,129,0.08);">
                                    <td colspan="9" class="text-end text-success fs-6 fw-bold pe-3" style="white-space:nowrap;">TOTAL HPP PER RESEP:</td>
                                    <td class="text-end font-monospace text-success fw-bold fs-5 pe-3" style="white-space:nowrap; min-width:180px;">
                                        @if($canViewHpp)
                                            Rp {{ number_format($totalHpp, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- HPP Per Unit Banner --}}
                    @if($canViewHpp)
                        <div class="rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2"
                             style="background: linear-gradient(135deg,rgba(16,185,129,.08),rgba(2,132,199,.06)); border: 1px dashed #10b981;">
                            <div>
                                <div class="fw-bold text-theme-main" style="font-size:13px;">
                                    <i class="bi bi-tag-fill text-success me-2"></i>Estimasi HPP Per Unit
                                </div>
                                <small class="text-theme-muted">Total HPP ÷ {{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success font-monospace" style="font-size:1.5rem;">
                                    Rp {{ number_format($hppPerUnit, 0, ',', '.') }}
                                </div>
                                <small class="text-theme-muted">per {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" onclick="pindahKeTabKalkulator()" style="background:#d97706; border-color:#d97706;">
                                    <i class="bi bi-calculator me-1"></i> Hitung untuk Banyak Unit
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ╔══════════════════════════════════╗
                     ║  TAB 2: KALKULATOR PRODUKSI      ║
                     ╚══════════════════════════════════╝ --}}
                <div class="tab-pane fade" id="tab-kalkulator" role="tabpanel">

                    {{-- INPUT SECTION --}}
                    <div class="kalkulator-section mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:rgba(245,158,11,.15);border:1px solid #d97706;">
                                        <i class="bi bi-calculator-fill text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-theme-main" style="font-size:14px;">Kalkulator Biaya Produksi</div>
                                        <small class="text-theme-muted" style="font-size:11px;">Input qty → lihat kebutuhan bahan &amp; total biaya</small>
                                    </div>
                                </div>
                                <label class="fw-semibold text-warning small mb-2 d-block">Rencana Jumlah Produksi</label>
                                <div class="input-group input-group-lg">
                                    <button type="button" class="btn btn-outline-warning" onclick="adjustKalQty(-10)"><i class="bi bi-dash-lg"></i></button>
                                    <input type="number" id="kalJumlahProduksi" class="form-control text-center fw-bold fs-4"
                                           min="1" step="1" placeholder="100"
                                           style="border-color:#d97706;color:var(--text-main);background:var(--input-bg);border-left:none;border-right:none;"
                                           oninput="hitungKalkulator()">
                                    <button type="button" class="btn btn-outline-warning" onclick="adjustKalQty(10)"><i class="bi bi-plus-lg"></i></button>
                                    <span class="input-group-text fw-bold" style="background:var(--readonly-bg);border-color:#d97706;color:var(--text-main);">{{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</span>
                                </div>
                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    @foreach([10, 50, 100, 500, 1000] as $q)
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setKalQty({{ $q }})">{{ number_format($q, 0, ',', '.') }}</button>
                                    @endforeach
                                </div>
                                <small class="text-theme-muted mt-2 d-block" style="font-size:10px;">
                                    1 resep BOM = <strong class="text-theme-main">{{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</strong>
                                </small>
                            </div>
                            <div class="col-12 col-md-7">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="rounded-3 p-3 text-center" style="background:var(--card-bg);border:1px solid var(--border-color);">
                                            <div style="font-size:10px;color:#d97706;font-weight:700;text-transform:uppercase;">Total Produksi</div>
                                            <div class="fw-bold text-theme-main font-monospace" style="font-size:1.5rem;letter-spacing:-1px;" id="kpiKalQty">-</div>
                                            <div class="text-theme-muted" style="font-size:10px;">{{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="rounded-3 p-3 text-center" style="background:var(--card-bg);border:1px solid var(--border-color);">
                                            <div style="font-size:10px;color:#059669;font-weight:700;text-transform:uppercase;">HPP Per Unit</div>
                                            <div class="fw-bold text-success font-monospace" style="font-size:1rem;margin-top:4px;" id="kpiKalHppUnit">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="rounded-3 p-3" style="background:linear-gradient(135deg,rgba(245,158,11,.15),rgba(239,68,68,.05));border:1px solid #d97706;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div style="font-size:10px;color:#d97706;font-weight:700;text-transform:uppercase;">ESTIMASI TOTAL BIAYA</div>
                                                <div class="fw-bold text-warning font-monospace" style="font-size:1.4rem;letter-spacing:-1px;" id="kpiKalGrandTotal">Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PREVIEW KEBUTUHAN (muncul setelah input) --}}
                    <div id="previewSection" class="d-none">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-theme-main mb-0">
                                <i class="bi bi-card-list me-2 text-warning"></i>
                                Preview Kebutuhan Material
                                <span class="badge bg-warning text-dark ms-2 rounded-pill" id="previewLabel" style="font-size:10px;">untuk 0 unit</span>
                            </h6>
                        </div>

                        <div class="table-responsive rounded-3 border mb-4">
                            <table class="table bom-comp-table align-middle mb-0" style="font-size:13px;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:38px;">No</th>
                                        <th style="width:105px;">Kode</th>
                                        <th>Nama Bahan Baku</th>
                                        <th class="text-center" style="width:110px;">Qty/Unit</th>
                                        <th class="text-center text-warning" style="width:130px;">Total Qty</th>
                                        <th class="text-center" style="width:75px;">Satuan</th>
                                        <th class="text-end" style="width:130px;">HPP/Satuan</th>
                                        <th class="text-end text-warning" style="width:150px;">Total Biaya</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPreview"></tbody>
                            </table>
                        </div>

                        {{-- RINCIAN BIAYA --}}
                        <div class="row g-3">
                            <div class="col-12 col-md-7">
                                <div class="rounded-3 border overflow-hidden">
                                    <div class="px-3 py-2 fw-bold text-theme-main small" style="background:var(--table-header-bg);font-size:11px;letter-spacing:.5px;border-bottom:1px solid var(--border-color);">
                                        <i class="bi bi-receipt me-2 text-primary"></i>RINCIAN BREAKDOWN BIAYA
                                    </div>
                                    <table class="table table-sm mb-0" style="font-size:13px;">
                                        <tbody>
                                            <tr>
                                                <td class="ps-3 text-theme-muted"><i class="bi bi-boxes me-1 text-primary"></i>Total Bahan Baku</td>
                                                <td class="text-end pe-3 fw-bold text-primary font-monospace" id="rincianBahan">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 text-theme-muted">
                                                    <i class="bi bi-person-workspace me-1 text-info"></i>Biaya Tenaga Kerja
                                                    <small class="text-muted d-block" id="rincianTkDesc" style="font-size:10px;"></small>
                                                </td>
                                                <td class="text-end pe-3 fw-bold text-info font-monospace" id="rincianTk">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 text-theme-muted">
                                                    <i class="bi bi-gear me-1 text-secondary"></i>Biaya Overhead
                                                    <small class="text-muted d-block" id="rincianOhDesc" style="font-size:10px;"></small>
                                                </td>
                                                <td class="text-end pe-3 fw-bold text-secondary font-monospace" id="rincianOh">Rp 0</td>
                                            </tr>
                                            <tr style="background:rgba(16,185,129,.06);border-top:2px solid #10b981 !important;">
                                                <td class="ps-3 fw-bold text-success fs-6"><i class="bi bi-check-circle-fill me-1"></i>TOTAL HPP PRODUKSI</td>
                                                <td class="text-end pe-3 fw-bold text-success font-monospace fs-5" id="rincianTotal">Rp 0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="rounded-3 h-100 d-flex flex-column justify-content-center p-4 text-center"
                                     style="background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(5,150,105,.06));border:2px solid #10b981;">
                                     <div class="d-inline-flex align-items-center justify-content-center text-white fw-extrabold rounded-circle mx-auto mb-2 shadow-sm" style="width:48px; height:48px; font-size:1.3rem; font-family: 'Segoe UI', monospace; background: rgba(52, 211, 153, 0.25); border: 2px solid #34d399; color: #ffffff !important; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">Rp</div>
                                    <div class="text-theme-muted mt-1" style="font-size:10px;text-transform:uppercase;font-weight:700;">Estimasi Total Biaya Produksi</div>
                                    <div class="fw-bold text-success font-monospace mt-1" style="font-size:1.7rem;letter-spacing:-1px;" id="summaryTotal">Rp 0</div>
                                    <div class="text-success mt-1" style="font-size:11px;" id="summaryDesc">untuk 0 unit</div>
                                    <hr style="border-color:var(--border-color);margin:10px 0;">
                                    <div class="d-flex justify-content-between"><span class="text-theme-muted" style="font-size:10px;">HPP Per Unit:</span><span class="fw-bold text-success font-monospace" style="font-size:13px;" id="summaryHppUnit">Rp 0</span></div>
                                    <div class="d-flex justify-content-between mt-1"><span class="text-theme-muted" style="font-size:10px;">Porsi Bahan:</span><span class="fw-bold text-theme-main font-monospace" style="font-size:11px;" id="summaryPorsiBahan">0%</span></div>
                                    <div class="d-flex justify-content-between mt-1"><span class="text-theme-muted" style="font-size:10px;">Porsi TK+OH:</span><span class="fw-bold text-theme-main font-monospace" style="font-size:11px;" id="summaryPorsiTkOh">0%</span></div>
                                </div>
                            </div>
                        </div>

                        {{-- PROGRESS BAR KOMPOSISI --}}
                        <div class="rounded-3 border p-3 mt-3" style="background:var(--bg-light);">
                            <div class="fw-semibold small text-theme-muted mb-2"><i class="bi bi-bar-chart-fill me-1 text-warning"></i>Komposisi Biaya Produksi</div>
                            @foreach([['Bahan Baku','primary','barBahan','barPorsiBahan'],['Tenaga Kerja','info','barTk','barPorsiTk'],['Overhead','secondary','barOh','barPorsiOh']] as $bar)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-theme-muted"><i class="bi bi-circle-fill me-1 text-{{ $bar[1] }}" style="font-size:8px;"></i>{{ $bar[0] }}</span>
                                    <span class="small fw-bold text-{{ $bar[1] }} font-monospace" id="{{ $bar[3] }}">0%</span>
                                </div>
                                <div class="progress rounded-pill" style="height:9px;background:rgba(0,0,0,.05);">
                                    <div class="progress-bar bg-{{ $bar[1] }} rounded-pill" id="{{ $bar[2] }}" style="width:0%;transition:width .5s ease;"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Empty state --}}
                    <div id="kalEmptyState" class="text-center py-5">
                        <i class="bi bi-calculator text-warning opacity-25" style="font-size:4rem;"></i>
                        <div class="fw-bold text-theme-muted mt-3">Masukkan Jumlah Produksi</div>
                        <small class="text-muted">Ketik atau pilih jumlah unit yang ingin diproduksi di atas.</small>
                    </div>
                </div>

                {{-- ╔══════════════════════════════════╗
                     ║  TAB 3: INFORMASI LAINNYA        ║
                     ╚══════════════════════════════════╝ --}}
                <div class="tab-pane fade" id="tab-info" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="sidebar-card">
                                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(2,132,199,.1),transparent);color:var(--text-main);">
                                    <i class="bi bi-info-circle text-primary"></i> Detail Resep BOM
                                </div>
                                <div class="sidebar-card-body">
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Kode BOM</span><span class="sidebar-info-value font-monospace text-primary">{{ $bom->kode_bom }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Nama Resep</span><span class="sidebar-info-value">{{ $bom->nama_bom }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Tipe Produksi</span><span class="sidebar-info-value">{{ $bom->tipe_bom }}</span></div>
                                    <div class="sidebar-info-row">
                                        <span class="sidebar-info-label">Tender / Project</span>
                                        <span class="sidebar-info-value">
                                            @if($bom->tender)
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill">
                                                    <i class="bi bi-briefcase me-1"></i>[{{ $bom->tender->nomor_kontrak ?: 'TENDER' }}] {{ $bom->tender->nama_tender }}
                                                </span>
                                            @else
                                                <span class="text-theme-muted"><i class="bi bi-dash-circle me-1"></i>Standar (Tanpa Tender)</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Target Hasil</span><span class="sidebar-info-value">{{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : '-' }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Status</span><span class="sidebar-info-value">{{ $bom->status }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Disusun Oleh</span><span class="sidebar-info-value">{{ $bom->creator ? $bom->creator->nama_lengkap : 'System' }}</span></div>
                                    @if($bom->updater && $bom->updated_at && $bom->updated_at->gt($bom->created_at))
                                        <div class="sidebar-info-row"><span class="sidebar-info-label">Diedit Oleh</span><span class="sidebar-info-value text-warning fw-bold">{{ $bom->updater->nama_lengkap }}</span></div>
                                    @endif
                                    @if($bom->approver)
                                        <div class="sidebar-info-row"><span class="sidebar-info-label">Disetujui Oleh</span><span class="sidebar-info-value text-success fw-bold">{{ $bom->approver->nama_lengkap }}</span></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="sidebar-card">
                                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(16,185,129,.1),transparent);color:var(--text-main);">
                                    <i class="bi bi-cash-stack text-success"></i> Ringkasan Biaya
                                </div>
                                <div class="sidebar-card-body">
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Subtotal Bahan</span><span class="sidebar-info-value font-monospace text-primary">Rp {{ number_format($subtotalBahan, 0, ',', '.') }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Biaya TK/CMT</span><span class="sidebar-info-value font-monospace">Rp {{ number_format($bom->biaya_tenaga_kerja, 0, ',', '.') }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Biaya Overhead</span><span class="sidebar-info-value font-monospace">Rp {{ number_format($bom->biaya_overhead, 0, ',', '.') }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">Total HPP Resep</span><span class="sidebar-info-value font-monospace text-success fw-bold">Rp {{ number_format($totalHpp, 0, ',', '.') }}</span></div>
                                    <div class="sidebar-info-row"><span class="sidebar-info-label">HPP Per Unit</span><span class="sidebar-info-value font-monospace text-success fw-bold" style="font-size:15px;">Rp {{ number_format($hppPerUnit, 0, ',', '.') }}</span></div>
                                </div>
                            </div>
                        </div>
                        @if($bom->keterangan)
                        <div class="col-12">
                            <div class="sidebar-card">
                                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(245,158,11,.1),transparent);color:var(--text-main);">
                                    <i class="bi bi-chat-text text-warning"></i> Catatan / Keterangan
                                </div>
                                <div class="sidebar-card-body">
                                    <p class="text-theme-main mb-0" style="line-height:1.6;">{{ $bom->keterangan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ╔══════════════════════════════════╗
                     ║  TAB 4: RIWAYAT BOM              ║
                     ╚══════════════════════════════════╝ --}}
                {{-- ╔══════════════════════════════════╗
                     ║  TAB 4: RIWAYAT BOM              ║
                     ╚══════════════════════════════════╝ --}}
                <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                    <div class="rounded-3 border overflow-hidden">
                        <div class="px-4 py-2.5 fw-bold small text-theme-main" style="background:var(--table-header-bg);border-bottom:1px solid var(--border-color);font-size:11px;letter-spacing:.5px;">
                            <i class="bi bi-clock-history me-2 text-primary"></i>RIWAYAT AKTIVITAS & PERUBAHAN BOM
                        </div>
                        <div class="p-4 position-relative">
                            
                            {{-- 1. BOM Dibuat --}}
                            <div class="d-flex gap-3 mb-4 position-relative">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                                     style="width:38px;height:38px;background:rgba(37,99,235,.12);border:1.5px solid #2563eb;z-index:2;">
                                    <i class="bi bi-plus-circle-fill text-primary" style="font-size:16px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-theme-main fs-6">BOM Dibuat</span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">Pembuatan Awal</span>
                                    </div>
                                    <div class="text-theme-muted small mt-0.5">Disusun oleh: <strong class="text-theme-main">{{ $bom->creator ? $bom->creator->nama_lengkap : 'System' }}</strong></div>
                                    <div class="text-theme-muted small mt-1"><i class="bi bi-calendar3 me-1"></i>{{ $bom->created_at ? $bom->created_at->format('d F Y, H:i') : '-' }} WIB</div>
                                </div>
                            </div>

                            @php
                                $hasApproved = !empty($bom->approved_at) || in_array($bom->status, ['Approved', 'Active', 'Completed', 'Selesai']);
                                $isUpdatedAfterCreated = $bom->updater && $bom->updated_at && $bom->updated_at->gt($bom->created_at);
                                
                                // Deteksi apakah ada edit terpisah dari approval
                                $hasSeparateEdit = false;
                                if ($isUpdatedAfterCreated) {
                                    if (!$hasApproved) {
                                        $hasSeparateEdit = true;
                                    } elseif ($bom->approved_at && abs($bom->updated_at->diffInSeconds($bom->approved_at)) > 5) {
                                        $hasSeparateEdit = true;
                                    } elseif (!$bom->approved_at && $bom->updated_by != $bom->approved_by) {
                                        $hasSeparateEdit = true;
                                    }
                                }
                            @endphp

                            {{-- 2. BOM Diperbarui (Diedit) --}}
                            @if($hasSeparateEdit)
                            <div class="d-flex gap-3 mb-4 position-relative">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                                     style="width:38px;height:38px;background:rgba(245,158,11,.12);border:1.5px solid #d97706;z-index:2;">
                                    <i class="bi bi-pencil-fill text-warning" style="font-size:15px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-theme-main fs-6">BOM Diperbarui (Diedit)</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">Revisi Resep</span>
                                    </div>
                                    <div class="text-theme-muted small mt-0.5">Diedit terakhir oleh: <strong class="text-theme-main">{{ $bom->updater ? $bom->updater->nama_lengkap : '-' }}</strong></div>
                                    <div class="text-theme-muted small mt-1"><i class="bi bi-clock-history me-1"></i>{{ $bom->updated_at->format('d F Y, H:i') }} WIB</div>
                                </div>
                            </div>
                            @endif

                            {{-- 3. BOM Disetujui (Approved) --}}
                            @if($hasApproved)
                            <div class="d-flex gap-3 {{ in_array($bom->status, ['Completed', 'Selesai']) ? 'mb-4' : '' }} position-relative">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                                     style="width:38px;height:38px;background:rgba(16,185,129,.15);border:1.5px solid #10b981;z-index:2;">
                                    <i class="bi bi-patch-check-fill text-success" style="font-size:17px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-theme-main fs-6">BOM Disetujui</span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">Disetujui</span>
                                    </div>
                                    <div class="text-theme-muted small mt-0.5">
                                        Disetujui oleh: <strong class="text-success">{{ $bom->approver ? $bom->approver->nama_lengkap : ($bom->updater ? $bom->updater->nama_lengkap : 'Supervisor') }}</strong>
                                    </div>
                                    <div class="text-theme-muted small mt-1">
                                        <i class="bi bi-calendar-check me-1"></i>{{ $bom->approved_at ? $bom->approved_at->format('d F Y, H:i') : ($bom->updated_at ? $bom->updated_at->format('d F Y, H:i') : '-') }} WIB
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- 4. BOM Ditolak (Rejected) --}}
                            @if($bom->status === 'Rejected')
                            <div class="d-flex gap-3 position-relative">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                                     style="width:38px;height:38px;background:rgba(239,68,68,.12);border:1.5px solid #ef4444;z-index:2;">
                                    <i class="bi bi-x-circle-fill text-danger" style="font-size:16px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-theme-main fs-6">BOM Ditolak</span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">Ditolak</span>
                                    </div>
                                    <div class="text-theme-muted small mt-0.5">Ditolak oleh: <strong class="text-danger">{{ $bom->updater ? $bom->updater->nama_lengkap : 'Supervisor' }}</strong></div>
                                    <div class="text-theme-muted small mt-1"><i class="bi bi-calendar-x me-1"></i>{{ $bom->updated_at ? $bom->updated_at->format('d F Y, H:i') : '-' }} WIB</div>
                                </div>
                            </div>
                            @endif

                            {{-- 5. BOM Ditandai Selesai --}}
                            @if(in_array($bom->status, ['Completed', 'Selesai']))
                            <div class="d-flex gap-3 position-relative">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                                     style="width:38px;height:38px;background:rgba(6,182,212,.12);border:1.5px solid #06b6d4;z-index:2;">
                                    <i class="bi bi-check-all text-info" style="font-size:18px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-theme-main fs-6">BOM Ditandai Selesai</span>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">Selesai Produksi</span>
                                    </div>
                                    <div class="text-theme-muted small mt-0.5">Ditandai oleh: <strong class="text-theme-main">{{ $bom->updater ? $bom->updater->nama_lengkap : 'System' }}</strong></div>
                                    <div class="text-theme-muted small mt-1"><i class="bi bi-calendar3 me-1"></i>{{ $bom->updated_at ? $bom->updated_at->format('d F Y, H:i') : '-' }} WIB</div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>{{-- end tab-content --}}
        </div>{{-- end main col --}}

        {{-- ── SIDEBAR (col-4) ── --}}
        <div class="col-12 col-xl-4 p-3 p-md-4 border-start border-md-top-0" style="border-color:var(--border-color)!important;">

            {{-- Card: Ringkasan BOM --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(2,132,199,.12),transparent);color:var(--text-main);">
                    <i class="bi bi-clipboard-data text-primary"></i> Ringkasan BOM
                </div>
                <div class="sidebar-card-body">
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Total Komponen</span><span class="sidebar-info-value text-primary">{{ $bom->items->count() }} Jenis Bahan</span></div>
                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">Target Hasil</span>
                        <span class="sidebar-info-value font-monospace fw-bold text-primary">
                            {{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : '-' }}
                        </span>
                    </div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Satuan BOM</span><span class="sidebar-info-value">{{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : '-' }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Tipe Produksi</span><span class="sidebar-info-value">{{ $bom->tipe_bom }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Metode Kalkulasi</span><span class="sidebar-info-value">Fixed Quantity</span></div>
                    <hr style="border-color:var(--border-color);margin:8px 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="sidebar-info-label">HPP Per Unit</span>
                        <span class="fw-bold text-success font-monospace" style="font-size:15px;">Rp {{ number_format($hppPerUnit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Card: Foto Produk --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(168,85,247,.12),transparent);color:var(--text-main);">
                    <i class="bi bi-image text-purple" style="color:#a855f7;"></i> Gambar Produk Jadi
                </div>
                <div class="sidebar-card-body p-2">
                    @php
                        $fotosArr = collect();
                        if ($bom->barangJadi) {
                            if ($bom->barangJadi->primaryFoto && $bom->barangJadi->primaryFoto->foto_path) {
                                $fotosArr->push($bom->barangJadi->primaryFoto->foto_path);
                            }
                            if ($bom->barangJadi->fotos && $bom->barangJadi->fotos->count() > 0) {
                                foreach ($bom->barangJadi->fotos as $f) {
                                    if ($f->foto_path && !$fotosArr->contains($f->foto_path)) {
                                        $fotosArr->push($f->foto_path);
                                    }
                                }
                            }
                        }
                    @endphp
                    @if($fotosArr->count() > 0)
                        <div id="fotoCarousel" class="carousel slide rounded-3 overflow-hidden" style="background:var(--bg-light);" data-bs-ride="false">
                            <div class="carousel-inner">
                                @foreach($fotosArr as $i => $path)
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <img src="{{ asset('storage/' . $path) }}"
                                         class="d-block w-100" alt="Foto produk"
                                         style="height:180px;object-fit:contain;padding:8px;">
                                </div>
                                @endforeach
                            </div>
                            @if($fotosArr->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#fotoCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#fotoCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                            <div class="text-center py-1">
                                <small class="text-theme-muted" style="font-size:10px;">{{ $fotosArr->count() }} foto</small>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center py-4 rounded-3" style="background:var(--bg-light);min-height:140px;">
                            <i class="bi bi-image text-theme-muted" style="font-size:2.5rem;opacity:.3;"></i>
                            <small class="text-theme-muted mt-2" style="font-size:11px;">Tidak ada foto produk</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Informasi Produksi --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(16,185,129,.12),transparent);color:var(--text-main);">
                    <i class="bi bi-gear text-success"></i> Informasi Produksi
                </div>
                <div class="sidebar-card-body">
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Lot Produksi</span><span class="sidebar-info-value">{{ rtrim(rtrim(number_format($bom->jumlah_hasil, 4, ',', '.'), '0'), ',') }} {{ $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : '' }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Proyek / Tender</span><span class="sidebar-info-value text-warning fw-semibold">{{ $bom->tender ? $bom->tender->nama_tender : '-' }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Biaya TK/CMT</span><span class="sidebar-info-value font-monospace">Rp {{ number_format($bom->biaya_tenaga_kerja, 0, ',', '.') }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Biaya Overhead</span><span class="sidebar-info-value font-monospace">Rp {{ number_format($bom->biaya_overhead, 0, ',', '.') }}</span></div>
                    <div class="sidebar-info-row"><span class="sidebar-info-label">Metode Costing</span><span class="sidebar-info-value">Standard Cost</span></div>
                </div>
            </div>

            {{-- Card: Tree Struktur BOM --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header" style="background:linear-gradient(135deg,rgba(245,158,11,.12),transparent);color:var(--text-main);">
                    <i class="bi bi-diagram-3 text-warning"></i> Tree Struktur BOM
                </div>
                <div class="sidebar-card-body">
                    <div class="bom-tree">
                        <div class="bom-tree-root">
                            <i class="bi bi-box-seam text-info me-1"></i>
                            <strong class="text-primary">{{ $bom->barangJadi ? $bom->barangJadi->kode_barang : '-' }}</strong>
                            <span class="text-theme-muted ms-1" style="font-size:11px;">{{ $bom->barangJadi ? $bom->barangJadi->nama_barang : '-' }}</span>
                            <span class="badge bg-success rounded-pill ms-auto" style="font-size:9px;">1 Pcs</span>
                        </div>
                        <ul>
                            @foreach($bom->items as $item)
                            <li>
                                <div class="bom-tree-item">
                                    <i class="bi bi-circle-fill text-primary" style="font-size:6px;flex-shrink:0;"></i>
                                    <span class="text-primary fw-bold" style="font-size:11px;white-space:nowrap;">{{ $item->barangMentah ? $item->barangMentah->kode_barang : '-' }}</span>
                                    <span class="text-theme-main" style="font-size:11px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $item->barangMentah ? $item->barangMentah->nama_barang : '-' }}">
                                        {{ $item->barangMentah ? $item->barangMentah->nama_barang : '-' }}
                                    </span>
                                    <span class="badge bg-warning text-dark rounded-pill ms-1 flex-shrink-0" style="font-size:9px;">
                                        {{ rtrim(rtrim(number_format($item->jumlah_kebutuhan, 4, ',', '.'), '0'), ',') }}
                                        {{ $item->satuan ? $item->satuan->nama_satuan : '' }}
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

        </div>{{-- end sidebar --}}
    </div>{{-- end row --}}
</div>

{{-- Edit modal (re-use existing) --}}
@include('modul_produksi.bom._modal_edit')
@include('modul_produksi.bom._modal_lookup_barang')

@push('scripts')
<script>
// Global safe HTML escaping
if (typeof window.escapeHtmlBom !== 'function') {
    window.escapeHtmlBom = function(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
}

@php
    $itemsSafeList = $bom->items->map(function($item) {
        return [
            'kode'   => $item->barangMentah ? $item->barangMentah->kode_barang : '-',
            'nama'   => $item->barangMentah ? $item->barangMentah->nama_barang : '-',
            'satuan' => $item->satuan ? $item->satuan->nama_satuan : ($item->barangMentah && $item->barangMentah->satuan ? $item->barangMentah->satuan->nama_satuan : '-'),
            'qty'    => (float)$item->jumlah_kebutuhan,
            'waste'  => (float)$item->waste_percent,
            'hpp'    => (float)$item->estimasi_harga_satuan,
        ];
    })->values()->all();
@endphp

// ═══════════════════════════════════════════════════════════════════
// DATA BOM (dari PHP ke JS via JSON aman)
// ═══════════════════════════════════════════════════════════════════
const _bomData = {
    id            : {{ $bom->id }},
    jumlah_hasil  : {{ max((float)$bom->jumlah_hasil, 0.0001) }},
    biaya_tk      : {{ (float)$bom->biaya_tenaga_kerja }},
    biaya_oh      : {{ (float)$bom->biaya_overhead }},
    satuan        : {!! json_encode($bom->satuanHasil ? $bom->satuanHasil->nama_satuan : 'Unit') !!},
    items         : {!! json_encode($itemsSafeList) !!}
};

// ═══════════════════════════════════════════════════════════════════
// FORMATTER HELPERS
// ═══════════════════════════════════════════════════════════════════
function unformatNumberID(val) {
    if (val === null || val === undefined || val === '') return 0;
    if (typeof val === 'number') return val;
    let str = val.toString().trim();
    if (str === '') return 0;
    if (str.includes(',')) {
        str = str.replace(/\./g, '').replace(',', '.');
    } else if (/^\d{1,3}(\.\d{3})+$/.test(str)) {
        str = str.replace(/\./g, '');
    }
    let num = parseFloat(str);
    return isNaN(num) ? 0 : num;
}

function formatRibuanID(num, maxDecimals = 2) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    let n = parseFloat(num);
    if (isNaN(n)) return '0';

    let str = n.toFixed(maxDecimals);
    let parts = str.split('.');
    let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    if (parts.length > 1 && parseInt(parts[1], 10) > 0) {
        let decPart = parts[1].replace(/0+$/, '');
        return decPart.length > 0 ? (intPart + ',' + decPart) : intPart;
    }
    return intPart;
}

function numberFormatID(val, maxDecimals = 2) {
    let num = (typeof val === 'number') ? val : (unformatNumberID(val) || parseFloat(val) || 0);
    return formatRibuanID(num, maxDecimals);
}

function cleanNumberInputValue(val) {
    if (val === null || val === undefined || val === '') return '';
    if (typeof val === 'number') return val;
    let str = val.toString().trim();
    if (str === '') return '';
    if (str.includes(',')) {
        str = str.replace(/\./g, '').replace(',', '.');
    }
    let num = parseFloat(str);
    return isNaN(num) ? '' : num;
}

// ═══════════════════════════════════════════════════════════════════
// KALKULATOR HELPERS
// ═══════════════════════════════════════════════════════════════════
function fmtRp(val) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val));
}
function fmtNum(val, dec = 4) {
    if (val === null || val === undefined || isNaN(parseFloat(val))) return '0';
    let num = parseFloat(val);
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: dec }).format(num);
}

function setKalQty(v)       { document.getElementById('kalJumlahProduksi').value = v; hitungKalkulator(); }
function adjustKalQty(d)    { const cur = parseFloat(document.getElementById('kalJumlahProduksi').value)||0; setKalQty(Math.max(1, cur+d)); }

// ═══════════════════════════════════════════════════════════════════
// HITUNG KALKULATOR
// ═══════════════════════════════════════════════════════════════════
function hitungKalkulator() {
    const qty = parseFloat(document.getElementById('kalJumlahProduksi').value) || 0;

    if (qty <= 0) {
        document.getElementById('kpiKalQty').textContent = '-';
        document.getElementById('kpiKalHppUnit').textContent = '-';
        document.getElementById('kpiKalGrandTotal').textContent = 'Rp 0';
        document.getElementById('previewSection').classList.add('d-none');
        document.getElementById('kalEmptyState').classList.remove('d-none');
        return;
    }

    const faktor   = qty / _bomData.jumlah_hasil;
    let   totalBahan = 0;
    let   rowsHtml   = '';

    _bomData.items.forEach((item, idx) => {
        const grossQtyPerResep = item.qty * (1 + item.waste / 100);
        const qtyPerUnit       = grossQtyPerResep / _bomData.jumlah_hasil;
        const totalQty         = qtyPerUnit * qty;
        const totalBiaya       = totalQty * item.hpp;
        totalBahan            += totalBiaya;

        rowsHtml += `
            <tr>
                <td class="text-center text-muted" style="padding:7px 8px;">${idx+1}</td>
                <td style="padding:7px 8px;"><span class="font-monospace fw-bold text-primary" style="font-size:12px;">${item.kode}</span></td>
                <td style="padding:7px 8px;" class="fw-semibold text-theme-main">${item.nama}${item.waste > 0 ? ` <span class="badge rounded-pill ms-1 fw-extrabold shadow-2xs" style="font-size:9.5px; background-color:#f59e0b !important; color:#0f172a !important; border:1px solid #fbbf24;">${item.waste}% waste</span>` : ''}</td>
                <td class="text-center font-monospace" style="padding:7px 8px;font-size:12.5px;">
                    <span class="text-theme-muted">${fmtNum(qtyPerUnit)}</span> <strong class="text-theme-main">${item.satuan}</strong>
                </td>
                <td class="text-center font-monospace" style="padding:7px 8px;">
                    <div class="fw-bold" style="font-size:13.5px; color:#d97706;">${fmtNum(totalQty)}</div>
                    <div class="small fw-bold text-theme-muted" style="font-size:11.5px;">${item.satuan}</div>
                </td>
                <td class="text-center fw-bold text-theme-main" style="padding:7px 8px;font-size:12.5px;">
                    ${item.satuan}
                </td>
                <td class="text-end font-monospace text-theme-muted" style="padding:7px 8px;font-size:12px;">${fmtRp(item.hpp)}</td>
                <td class="text-end font-monospace fw-bold" style="padding:7px 8px;color:#d97706;font-size:13px;">${fmtRp(totalBiaya)}</td>
            </tr>`;
    });

    // Footer tabel
    rowsHtml += `
        <tr style="background:rgba(245,158,11,.07);border-top:2px solid #d97706;">
            <td colspan="7" class="text-end fw-bold text-warning pe-3" style="padding:8px;">TOTAL BAHAN (${fmtNum(qty,0)} ${_bomData.satuan}):</td>
            <td class="text-end font-monospace fw-bold text-warning pe-3" style="padding:8px;">${fmtRp(totalBahan)}</td>
        </tr>`;

    document.getElementById('tbodyPreview').innerHTML = rowsHtml;

    const simTk    = _bomData.biaya_tk * faktor;
    const simOh    = _bomData.biaya_oh * faktor;
    const grand    = totalBahan + simTk + simOh;
    const hppUnit  = qty > 0 ? grand / qty : 0;

    const pBahan   = grand > 0 ? totalBahan / grand * 100 : 0;
    const pTk      = grand > 0 ? simTk / grand * 100 : 0;
    const pOh      = grand > 0 ? simOh / grand * 100 : 0;
    const batchFmt = parseFloat(faktor.toFixed(4));

    // KPI header
    document.getElementById('kpiKalQty').textContent = fmtNum(qty, 0);
    document.getElementById('kpiKalHppUnit').textContent = fmtRp(hppUnit);
    document.getElementById('kpiKalGrandTotal').textContent = fmtRp(grand);

    // Rincian biaya
    document.getElementById('rincianBahan').textContent = fmtRp(totalBahan);
    document.getElementById('rincianTk').textContent    = fmtRp(simTk);
    document.getElementById('rincianTkDesc').textContent = `(${batchFmt} batch × Rp ${new Intl.NumberFormat('id-ID').format(Math.round(_bomData.biaya_tk))} per batch)`;
    document.getElementById('rincianOh').textContent    = fmtRp(simOh);
    document.getElementById('rincianOhDesc').textContent = `(${batchFmt} batch × Rp ${new Intl.NumberFormat('id-ID').format(Math.round(_bomData.biaya_oh))} per batch)`;
    document.getElementById('rincianTotal').textContent  = fmtRp(grand);

    // Summary card
    document.getElementById('summaryTotal').textContent    = fmtRp(grand);
    document.getElementById('summaryDesc').textContent     = `untuk ${fmtNum(qty,0)} ${_bomData.satuan}`;
    document.getElementById('summaryHppUnit').textContent  = fmtRp(hppUnit) + ' / ' + _bomData.satuan;
    document.getElementById('summaryPorsiBahan').textContent = pBahan.toFixed(1) + '%';
    document.getElementById('summaryPorsiTkOh').textContent  = (pTk + pOh).toFixed(1) + '%';

    // Label preview
    document.getElementById('previewLabel').textContent = `untuk ${fmtNum(qty,0)} ${_bomData.satuan}`;

    // Progress bars
    ['barBahan','barTk','barOh'].forEach((id, i) => {
        const pct = [pBahan, pTk, pOh][i];
        document.getElementById(id).style.width = pct + '%';
        document.getElementById(['barPorsiBahan','barPorsiTk','barPorsiOh'][i]).textContent = pct.toFixed(1) + '%';
    });

    document.getElementById('kalEmptyState').classList.add('d-none');
    document.getElementById('previewSection').classList.remove('d-none');
}

// Print shortcut
function printBom(id) {
    window.open(`/produksi/bom/${id}/print`, '_blank');
}

// ═══════════════════════════════════════════════════════════════════
// WORKFLOW ACTIONS: SEND PENDING, APPROVE, REJECT
// ═══════════════════════════════════════════════════════════════════

function sendPendingBom(id) {
    Swal.fire({
        title: 'Ajukan Resep BOM?',
        text: 'Resep BOM akan diajukan ke status Pending Approval agar dapat ditinjau dan disetujui.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Ajukan (Pending)',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mengirim pengajuan BOM ke atasan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}/send-pending`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diajukan!',
                        text: res.message || 'Resep BOM berhasil masuk antrean Pending Approval.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal Mengajukan', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                }
            });
        }
    });
}

function approveBom(id) {
    Swal.fire({
        title: 'Setujui Resep BOM ini?',
        text: 'Resep BOM yang disetujui (Approved) akan aktif dan siap digunakan untuk proses SPK & Produksi.',
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Setujui (Approve)',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Approval...',
                text: 'Menyimpan persetujuan BOM...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}/approve`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disetujui!',
                        text: res.message || 'Resep BOM berhasil disetujui.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal Menyetujui', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui BOM.', 'error');
                }
            });
        }
    });
}

function rejectBom(id) {
    Swal.fire({
        title: 'Tolak Resep BOM?',
        input: 'textarea',
        inputLabel: 'Alasan Penolakan (Catatan)',
        inputPlaceholder: 'Tuliskan alasan penolakan atau revisi bahan baku...',
        inputAttributes: {
            'aria-label': 'Alasan penolakan'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Tolak (Reject)',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return 'Harap masukkan alasan penolakan!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Penolakan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}/reject`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    alasan: result.value
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'info',
                        title: 'BOM Ditolak',
                        text: res.message || 'Resep BOM telah ditolak.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal Menolak', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }
    });
}

function completeBom(id) {
    Swal.fire({
        title: 'Tandai Selesai BOM ini?',
        text: 'Resep BOM yang diselesaikan akan ditandai berstatus Selesai.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-all me-1"></i> Ya, Tandai Selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Menyimpan status Selesai...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}/complete`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message || 'Resep BOM telah ditandai sebagai Selesai.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }
    });
}

function reactivateBom(id) {
    Swal.fire({
        title: 'Aktifkan Kembali BOM ini?',
        text: 'Resep BOM yang diaktifkan kembali akan berstatus Approved (Aktif).',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Aktifkan Kembali',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mengaktifkan kembali BOM...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}/reactivate`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Aktif!',
                        text: res.message || 'Resep BOM telah diaktifkan kembali.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }
    });
}

function deleteBom(id) {
    Swal.fire({
        title: 'Hapus Resep BOM ini?',
        text: 'Resep BOM yang dihapus tidak dapat dipulihkan kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/produksi/bom/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: res.message || 'Resep BOM telah dihapus.',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("produksi.bom.index") }}';
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menghapus BOM.', 'error');
                }
            });
        }
    });
}

// Switch to kalkulator tab helper
function pindahKeTabKalkulator() {
    const tabBtn = document.querySelector('button[data-bs-target="#tab-kalkulator"]');
    if (tabBtn) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
        tab.show();
        setTimeout(() => {
            const inp = document.getElementById('kalJumlahProduksi');
            if (inp) inp.focus();
        }, 200);
    }
}
</script>
@endpush
</x-layouts>
