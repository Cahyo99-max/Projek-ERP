<x-layouts title="Lembar Kontrol Pengerjaan Produksi SPK - Modul Produksi">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<style>
/* ─── CINEMATIC SPK DETAIL PAGE ─── */

/* Page background */
body { background: #060d1a !important; }

/* ── HERO HEADER CARD ── */
.spk-hero-card {
    background: linear-gradient(135deg, #0c1a2e 0%, #0f2040 50%, #0a1628 100%);
    border: 1px solid rgba(56, 189, 248, 0.2);
    border-radius: 22px;
    padding: 28px 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(56,189,248,0.1);
}

.spk-hero-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #38bdf8 30%, #818cf8 60%, #38bdf8 80%, transparent 100%);
}

.spk-hero-card::after {
    content: '';
    position: absolute;
    top: -80px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(56,189,248,0.1) 0%, transparent 65%);
    pointer-events: none;
}

.spk-hero-deco-left {
    position: absolute;
    bottom: -40px; left: -30px;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    pointer-events: none;
}

/* SPK Number Badge */
.spk-number-badge {
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.45);
    color: #38bdf8;
    font-family: var(--bs-font-monospace);
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    padding: 5px 14px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Progress bar */
.spk-progress-track {
    background: rgba(255,255,255,0.06);
    border-radius: 50px;
    height: 10px;
    overflow: hidden;
}

.spk-progress-fill {
    background: linear-gradient(90deg, #0ea5e9, #38bdf8, #818cf8);
    background-size: 200% 100%;
    border-radius: 50px;
    height: 100%;
    animation: progressShimmer 2.5s linear infinite;
    box-shadow: 0 0 12px rgba(56,189,248,0.45);
    transition: width 1s ease;
}

.spk-progress-fill.complete {
    background: linear-gradient(90deg, #059669, #10b981, #34d399);
    box-shadow: 0 0 12px rgba(16,185,129,0.45);
}

@keyframes progressShimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ── ACTION BUTTONS ── */
.btn-back-pill {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.75);
    border-radius: 50px;
    padding: 7px 18px;
    font-size: 12.5px;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
}

.btn-back-pill:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.3);
    color: #ffffff;
    text-decoration: none;
}

.btn-print-pill {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    border: none;
    color: #ffffff;
    border-radius: 50px;
    padding: 7px 18px;
    font-size: 12.5px;
    font-weight: 700;
    transition: all 0.22s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
    box-shadow: 0 4px 18px rgba(37,99,235,0.45);
}

.btn-print-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(37,99,235,0.6);
    color: #ffffff;
    text-decoration: none;
}

/* ── TAHAPAN CARDS ── */
.tahapan-card {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(71, 85, 105, 0.4);
    border-radius: 18px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.tahapan-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: rgba(100,116,139,0.4);
    transition: all 0.28s ease;
}

.tahapan-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.45);
}

.tahapan-card.status-progress {
    background: rgba(14, 165, 233, 0.05);
    border-color: rgba(56, 189, 248, 0.3);
}

.tahapan-card.status-progress::before {
    background: linear-gradient(180deg, #0ea5e9, #38bdf8);
    box-shadow: 0 0 14px rgba(56,189,248,0.7);
}

.tahapan-card.status-selesai {
    background: rgba(16, 185, 129, 0.05);
    border-color: rgba(52, 211, 153, 0.3);
}

.tahapan-card.status-selesai::before {
    background: linear-gradient(180deg, #059669, #10b981);
    box-shadow: 0 0 14px rgba(16,185,129,0.7);
}

.tahapan-card.status-menunggu {
    opacity: 0.82;
}

/* Step number bubble */
.step-bubble {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 15px;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.step-bubble.waiting {
    background: rgba(100, 116, 139, 0.2);
    border: 1px solid rgba(100, 116, 139, 0.35);
    color: #64748b;
}

.step-bubble.progress {
    background: rgba(14, 165, 233, 0.2);
    border: 1px solid rgba(56, 189, 248, 0.45);
    color: #38bdf8;
    box-shadow: 0 0 16px rgba(56,189,248,0.3);
}

.step-bubble.done {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(52, 211, 153, 0.45);
    color: #34d399;
    box-shadow: 0 0 16px rgba(16,185,129,0.3);
}

/* Stat mini chips inside tahapan card */
.stat-chip {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 10px 14px;
    flex: 1 1 0;
    min-width: 0;
}

.stat-chips-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.stat-chip .chip-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(148, 163, 184, 0.7);
    display: block;
    margin-bottom: 2px;
}

.stat-chip .chip-val {
    font-size: 14px;
    font-weight: 800;
    font-family: var(--bs-font-monospace);
    color: #f1f5f9;
}

.stat-chip.chip-target   { border-color: rgba(100,116,139,0.3); }
.stat-chip.chip-selesai  { border-color: rgba(56,189,248,0.3); }
.stat-chip.chip-reject   { border-color: rgba(239,68,68,0.3); }
.stat-chip.chip-operator { border-color: rgba(99,102,241,0.3); }

/* Action buttons inside card */
.btn-tahapan {
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 12.5px;
    font-weight: 700;
    border: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-tahapan.start {
    background: rgba(14, 165, 233, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.45);
    color: #38bdf8;
}

.btn-tahapan.start:hover {
    background: #0ea5e9;
    color: #ffffff;
    box-shadow: 0 0 18px rgba(14,165,233,0.5);
}

.btn-tahapan.input {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(37,99,235,0.4);
}

.btn-tahapan.input:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(37,99,235,0.6);
    color: #ffffff;
}

.btn-tahapan.history {
    background: rgba(100, 116, 139, 0.12);
    border: 1px solid rgba(100, 116, 139, 0.3);
    color: rgba(148,163,184,0.9);
}

.btn-tahapan.history:hover {
    background: rgba(100, 116, 139, 0.25);
    color: #f1f5f9;
}

.btn-tahapan.selesai {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(5,150,105,0.4);
}

.btn-tahapan.selesai:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(16,185,129,0.6);
    color: #ffffff;
}

/* ── COMPLETE SPK FOOTER CARD ── */
.complete-spk-card {
    background: linear-gradient(135deg, rgba(5,150,105,0.12) 0%, rgba(16,185,129,0.06) 100%);
    border: 1px solid rgba(52,211,153,0.25);
    border-radius: 18px;
    padding: 22px 26px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 40px rgba(16,185,129,0.08);
}

.complete-spk-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
}

.btn-complete-spk {
    background: linear-gradient(135deg, #059669, #10b981);
    border: none;
    color: #ffffff;
    border-radius: 12px;
    padding: 11px 28px;
    font-weight: 800;
    font-size: 13.5px;
    letter-spacing: 0.2px;
    transition: all 0.22s ease;
    box-shadow: 0 0 24px rgba(16,185,129,0.45);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-complete-spk:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 40px rgba(16,185,129,0.7);
    color: #ffffff;
}

/* Status badges */
.badge-cinematic {
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-draft     { background:rgba(100,116,139,0.2); color:#94a3b8; border:1px solid rgba(100,116,139,0.3); }
.badge-pending   { background:rgba(245,158,11,0.2);  color:#fbbf24; border:1px solid rgba(245,158,11,0.35); }
.badge-approved  { background:rgba(16,185,129,0.2);  color:#34d399; border:1px solid rgba(16,185,129,0.35); }
.badge-progress  { background:rgba(14,165,233,0.2);  color:#38bdf8; border:1px solid rgba(56,189,248,0.4); }
.badge-completed { background:rgba(16,185,129,0.2);  color:#34d399; border:1px solid rgba(16,185,129,0.4); }
.badge-cancelled { background:rgba(239,68,68,0.2);   color:#f87171; border:1px solid rgba(239,68,68,0.35); }
.badge-menunggu  { background:rgba(100,116,139,0.15); color:#64748b; border:1px solid rgba(100,116,139,0.25); }

@media (max-width: 575.98px) {
    .spk-hero-card { padding: 18px 16px; border-radius: 16px; }
    .tahapan-card  { padding: 16px 14px; border-radius: 14px; }
    .stat-chip     { padding: 7px 10px; }
}

/* ═══════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES — applies when NOT in dark theme
   ═══════════════════════════════════════════════════════ */
html:not([data-bs-theme="dark"]) body {
    background: #f1f5f9 !important;
}

/* Hero card */
html:not([data-bs-theme="dark"]) .spk-hero-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faff 50%, #eef4ff 100%);
    border: 1px solid rgba(37, 99, 235, 0.15);
    box-shadow: 0 8px 32px rgba(37,99,235,0.1), 0 0 0 1px rgba(37,99,235,0.08);
}

html:not([data-bs-theme="dark"]) .spk-hero-card::before {
    background: linear-gradient(90deg, transparent 0%, #2563eb 30%, #818cf8 60%, #2563eb 80%, transparent 100%);
    opacity: 0.4;
}

html:not([data-bs-theme="dark"]) .spk-hero-card::after {
    background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 65%);
}

html:not([data-bs-theme="dark"]) .spk-hero-deco-left {
    background: radial-gradient(circle, rgba(99,102,241,0.05) 0%, transparent 70%);
}

/* Progress track */
html:not([data-bs-theme="dark"]) .spk-progress-track {
    background: rgba(37, 99, 235, 0.08);
}

/* Breadcrumb & title text */
html:not([data-bs-theme="dark"]) .breadcrumb-item a {
    color: #64748b !important;
}

html:not([data-bs-theme="dark"]) h4.fw-bold {
    color: #0f172a !important;
}

/* Back button */
html:not([data-bs-theme="dark"]) .btn-back-pill {
    background: rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(15, 23, 42, 0.14);
    color: #334155;
}

html:not([data-bs-theme="dark"]) .btn-back-pill:hover {
    background: rgba(15, 23, 42, 0.12);
    border-color: rgba(15, 23, 42, 0.25);
    color: #0f172a;
}

/* Tahapan cards */
html:not([data-bs-theme="dark"]) .tahapan-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}

html:not([data-bs-theme="dark"]) .tahapan-card::before {
    background: #cbd5e1;
}

html:not([data-bs-theme="dark"]) .tahapan-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,0.12);
}

html:not([data-bs-theme="dark"]) .tahapan-card.status-progress {
    background: #f0f9ff;
    border-color: rgba(14, 165, 233, 0.3);
}

html:not([data-bs-theme="dark"]) .tahapan-card.status-selesai {
    background: #f0fdf9;
    border-color: rgba(16, 185, 129, 0.3);
}

html:not([data-bs-theme="dark"]) .tahapan-card .fw-bold.text-white,
html:not([data-bs-theme="dark"]) .tahapan-card h5.text-white {
    color: #0f172a !important;
}

/* Step bubbles */
html:not([data-bs-theme="dark"]) .step-bubble.waiting {
    background: rgba(100, 116, 139, 0.12);
    border: 1px solid rgba(100, 116, 139, 0.25);
    color: #475569;
}

/* Stat chips */
html:not([data-bs-theme="dark"]) .stat-chip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

html:not([data-bs-theme="dark"]) .stat-chip.chip-target   { border-color: #cbd5e1; }
html:not([data-bs-theme="dark"]) .stat-chip.chip-selesai  { border-color: rgba(14,165,233,0.35); background: #f0f9ff; }
html:not([data-bs-theme="dark"]) .stat-chip.chip-reject   { border-color: rgba(239,68,68,0.25); }
html:not([data-bs-theme="dark"]) .stat-chip.chip-operator { border-color: rgba(99,102,241,0.25); background: #faf5ff; }

html:not([data-bs-theme="dark"]) .stat-chip .chip-label {
    color: #94a3b8;
}

html:not([data-bs-theme="dark"]) .stat-chip .chip-val {
    color: #1e293b;
}

/* History button */
html:not([data-bs-theme="dark"]) .btn-tahapan.history {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
}

html:not([data-bs-theme="dark"]) .btn-tahapan.history:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* Complete footer card */
html:not([data-bs-theme="dark"]) .complete-spk-card {
    background: linear-gradient(135deg, rgba(5,150,105,0.06) 0%, rgba(16,185,129,0.03) 100%);
    border: 1px solid rgba(52, 211, 153, 0.3);
    box-shadow: 0 4px 20px rgba(16,185,129,0.08);
}

html:not([data-bs-theme="dark"]) .complete-spk-card small {
    color: #64748b !important;
}

/* Section header text */
html:not([data-bs-theme="dark"]) .text-white {
    color: #0f172a !important;
}

html:not([data-bs-theme="dark"]) .text-white-50 {
    color: #64748b !important;
}

html:not([data-bs-theme="dark"]) small[style*="color:rgba(148"] {
    color: #94a3b8 !important;
}

/* SPK number badge stays cyan in both modes */
html:not([data-bs-theme="dark"]) .spk-number-badge {
    background: rgba(37, 99, 235, 0.08);
    border: 1px solid rgba(37, 99, 235, 0.3);
    color: #2563eb;
}

/* Tahapan name heading in light mode */
html:not([data-bs-theme="dark"]) .tahapan-card h5 {
    color: #0f172a !important;
}

/* Overall progress label */
html:not([data-bs-theme="dark"]) .d-flex span[style*="color:rgba(148"] {
    color: #475569 !important;
}

/* PIC chip */
html:not([data-bs-theme="dark"]) div[style*="rgba(99,102,241,0.12)"] {
    background: rgba(99,102,241,0.08) !important;
    border: 1px solid rgba(99,102,241,0.2) !important;
}

/* Empty state */
html:not([data-bs-theme="dark"]) div[style*="background:rgba(15,23,42"] {
    background: rgba(241,245,249,0.8) !important;
    border-color: #e2e8f0 !important;
}

</style>
@endpush

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">

    {{-- ── ACTION BAR ── --}}
    {{-- Title + Buttons in one row --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
        <h4 class="fw-bold text-white mb-0 d-flex align-items-center gap-2 text-truncate" style="font-size: clamp(13px, 3.5vw, 17px); min-width:0;">
            <i class="bi bi-list-check flex-shrink-0" style="color:#38bdf8; font-size:18px;"></i>
            <span class="text-truncate">Lembar Kontrol Pengerjaan Produksi SPK</span>
        </h4>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('produksi.realisasi.index') }}" class="btn-back-pill">
                <i class="bi bi-arrow-left"></i>
                <span class="d-none d-sm-inline">Kembali ke Realisasi Produksi</span>
                <span class="d-sm-none">Kembali</span>
            </a>
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('cetak-progress-spk'))
            <a href="{{ route('produksi.spk.print-progress', $spk->id) }}" target="_blank" class="btn-print-pill">
                <i class="bi bi-printer-fill"></i>
                <span class="d-none d-sm-inline">Cetak Progress</span>
            </a>
            @endif
        </div>
    </div>


    @php
        $totalTahapCount = $spk->tahapans->count();
        $completedTahapCount = $spk->tahapans->where('status', 'Selesai')->count();
        $isSpkCompletedLock = ($spk->status === 'Completed') || ($totalTahapCount > 0 && $completedTahapCount === $totalTahapCount);
    @endphp

    @if($isSpkCompletedLock && auth()->user()->hasRole('Super Admin'))
        <div class="alert alert-warning border border-warning border-opacity-50 bg-dark text-warning rounded-3 shadow-sm mb-4 d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center gap-2.5">
                <i class="bi bi-shield-lock-fill fs-4 text-warning"></i>
                <div>
                    <div class="fw-bold font-monospace text-uppercase" style="letter-spacing: 0.5px; font-size: 13px;">🔒 KONTROL TAHAPAN SPK SELESAI (Dikunci)</div>
                    <small class="text-warning text-opacity-75" style="font-size: 11.5px;">Realisasi SPK ini telah Selesai (Completed). Halaman ini hanya dapat diakses dengan wewenang otorisasi khusus <b>Administrator Sistem (Super Admin)</b>.</small>
                </div>
            </div>
            <span class="badge bg-warning text-dark font-monospace fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Otorisasi Super Admin</span>
        </div>
    @endif

    {{-- ── CINEMATIC HERO CARD ── --}}
    <div class="spk-hero-card mb-4">
        <div class="spk-hero-deco-left"></div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">

            {{-- Left: SPK info --}}
            <div class="flex-grow-1 min-w-0">
                {{-- Badges row --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="spk-number-badge">
                        <i class="bi bi-tag-fill" style="font-size:11px;"></i>
                        {{ $spk->nomor_spk }}
                    </span>
                    @php
                        $statusMap = [
                            'Draft'       => '<span class="badge-cinematic badge-draft"><i class="bi bi-file-earmark me-1"></i>Draft</span>',
                            'Pending'     => '<span class="badge-cinematic badge-pending"><i class="bi bi-clock me-1"></i>Pending</span>',
                            'Approved'    => '<span class="badge-cinematic badge-approved"><i class="bi bi-check-circle me-1"></i>Disetujui</span>',
                            'In Progress' => '<span class="badge-cinematic badge-progress"><span class="spinner-border spinner-border-sm me-1" style="width:.65rem;height:.65rem;"></span>Dalam Proses</span>',
                            'Completed'   => '<span class="badge-cinematic badge-completed"><i class="bi bi-patch-check-fill me-1"></i>Selesai</span>',
                            'Cancelled'   => '<span class="badge-cinematic badge-cancelled"><i class="bi bi-x-circle me-1"></i>Dibatalkan</span>',
                        ];
                    @endphp
                    {!! $statusMap[$spk->status] ?? '<span class="badge-cinematic badge-draft">'.$spk->status.'</span>' !!}
                </div>

                {{-- Product name --}}
                <h2 class="fw-bold text-white mb-1" style="font-size: clamp(16px, 4vw, 22px); letter-spacing: -0.3px; line-height:1.3;">
                    [{{ $spk->barangJadi->kode_barang ?? '-' }}] {{ $spk->barangJadi->nama_barang ?? '-' }}
                </h2>
                <div class="d-flex align-items-center gap-2 mb-4" style="color:rgba(148,163,184,0.7); font-size:12.5px;">
                    <i class="bi bi-diagram-2-fill" style="color:#818cf8;"></i>
                    <span>Resep BOM: [{{ $spk->bom->kode_bom ?? '-' }}] {{ $spk->bom->nama_bom ?? '-' }}</span>
                </div>

                {{-- Overall progress --}}
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="fw-semibold" style="color:rgba(148,163,184,0.8); font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="bi bi-layers me-1"></i>
                            Total Progres SPK ({{ $completedTahap }} dari {{ $totalTahap }} Tahapan Selesai)
                        </span>
                        <span class="fw-bold font-monospace" style="color: {{ $percentComplete >= 100 ? '#34d399' : '#38bdf8' }}; font-size:14px;">
                            {{ $percentComplete }}%
                        </span>
                    </div>
                    <div class="spk-progress-track">
                        <div class="spk-progress-fill {{ $percentComplete >= 100 ? 'complete' : '' }}" style="width: {{ $percentComplete }}%;"></div>
                    </div>
                    <div class="mt-1.5" style="color:rgba(148,163,184,0.55); font-size:11px;">
                        {{ $completedTahap }} tahapan selesai dari total {{ $totalTahap }} tahapan
                    </div>
                </div>
            </div>

            {{-- Right: Target stat --}}
            <div class="flex-shrink-0 text-start text-md-end pt-1">
                <div style="color:rgba(148,163,184,0.6); font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; margin-bottom:4px;">
                    Target Selesai Produksi
                </div>
                <div class="fw-bold" style="color:#38bdf8; font-size: clamp(22px,5vw,32px); font-family:var(--bs-font-monospace); letter-spacing:-0.5px; line-height:1.1;">
                    {{ number_format($spk->jumlah_rencana, 0, ',', '.') }}
                    <span style="font-size:50%; color:rgba(56,189,248,0.7);">{{ $spk->satuan->nama_satuan ?? 'Pcs' }}</span>
                </div>
                <div class="mt-2 d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-3"
                     style="background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.3); font-size:12px;">
                    <i class="bi bi-person-circle" style="color:#818cf8;"></i>
                    <span class="fw-semibold text-white">{{ $spk->penanggungJawab->nama_lengkap ?? 'Kepala Line' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION HEADER ── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <div style="width:3px; height:22px; background:linear-gradient(180deg,#38bdf8,#818cf8); border-radius:3px;"></div>
            <h5 class="fw-bold text-white mb-0" style="font-size:15px;">Daftar Tahapan Kontrol Produksi</h5>
        </div>
        <small style="color:rgba(148,163,184,0.55); font-size:11px;">Klik tombol status untuk memperbarui per divisi</small>
    </div>

    {{-- ── TAHAPAN CARDS ── --}}
    <div class="d-flex flex-column gap-3 mb-4">
        @forelse($spk->tahapans as $t)
            @php
                $satuan  = $spk->satuan->nama_satuan ?? 'Pcs';
                $target  = (float)$t->target_qty;
                $selesai = (float)$t->qty_selesai;
                $reject  = (float)$t->qty_reject;
                $percent = $target > 0 ? round(($selesai / $target) * 100, 1) : 0;

                $cardClass = 'status-menunggu';
                $bubbleClass = 'waiting';
                $statusBadge = '<span class="badge-cinematic badge-menunggu"><i class="bi bi-hourglass me-1"></i>Menunggu</span>';

                if ($t->status === 'Selesai') {
                    $cardClass   = 'status-selesai';
                    $bubbleClass = 'done';
                    $statusBadge = '<span class="badge-cinematic badge-completed"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
                } elseif ($t->status === 'Sedang Dikerjakan') {
                    $cardClass   = 'status-progress';
                    $bubbleClass = 'progress';
                    $statusBadge = '<span class="badge-cinematic badge-progress"><span class="spinner-border spinner-border-sm me-1" style="width:.6rem;height:.6rem;"></span>Sedang Dikerjakan</span>';
                }
            @endphp

            <div class="tahapan-card {{ $cardClass }}">

                {{-- Header row --}}
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3 pb-3"
                     style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="step-bubble {{ $bubbleClass }}">#{{ $t->urutan }}</div>
                        @php
                            $fotoTahapan = $t->foto_url;
                        @endphp
                        @if($fotoTahapan)
                            <div class="position-relative flex-shrink-0" style="cursor: pointer;" onclick="previewMasterTahapanFoto('{{ $fotoTahapan }}', '{{ e($t->nama_tahapan) }}')" title="Klik untuk memperbesar foto referensi / SOP">
                                <img src="{{ $fotoTahapan }}" alt="{{ $t->nama_tahapan }}" class="rounded-3 shadow-sm" style="width: 48px; height: 48px; object-fit: cover; border: 1.5px solid rgba(56, 189, 248, 0.4); background: #0f172a; transition: transform 0.2s ease, border-color 0.2s ease;" onmouseenter="this.style.transform='scale(1.08)'; this.style.borderColor='#38bdf8';" onmouseleave="this.style.transform='scale(1)'; this.style.borderColor='rgba(56, 189, 248, 0.4)';">
                                <span class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center" style="width: 15px; height: 15px; font-size: 8.5px; color: #38bdf8;">
                                    <i class="bi bi-zoom-in"></i>
                                </span>
                            </div>
                        @endif
                        <div>
                            <h5 class="fw-bold text-white mb-0" style="font-size:15px; letter-spacing:-0.2px;">{{ $t->nama_tahapan }}</h5>
                            @if($fotoTahapan)
                                <span class="d-inline-flex align-items-center gap-1 mt-0.5" style="font-size: 11px; color: #38bdf8; cursor: pointer;" onclick="previewMasterTahapanFoto('{{ $fotoTahapan }}', '{{ e($t->nama_tahapan) }}')">
                                    <i class="bi bi-image-fill" style="font-size: 10px;"></i> Foto Acuan SOP
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>{!! $statusBadge !!}</div>
                </div>

                {{-- Progress bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span style="color:rgba(148,163,184,0.65); font-size:11px; text-transform:uppercase; letter-spacing:0.4px; font-weight:600;">Progres Tahapan</span>
                        <span class="fw-bold font-monospace" style="color: {{ $percent >= 100 ? '#34d399' : '#38bdf8' }}; font-size:13px;">
                            {{ number_format($selesai,0,',','.') }} / {{ number_format($target,0,',','.') }} {{ $satuan }} ({{ $percent }}%)
                        </span>
                    </div>
                    <div class="spk-progress-track" style="height:8px;">
                        <div class="spk-progress-fill {{ $percent >= 100 ? 'complete' : '' }}" style="width: {{ min(100, $percent) }}%;"></div>
                    </div>
                </div>

                {{-- Stat chips row --}}
                <div class="stat-chips-grid mb-3">
                    <div class="stat-chip chip-target">
                        <span class="chip-label">Target Qty</span>
                        <span class="chip-val" style="color:#94a3b8;">{{ number_format($target,0,',','.') }} {{ $satuan }}</span>
                    </div>
                    <div class="stat-chip chip-selesai">
                        <span class="chip-label">Qty Selesai</span>
                        <span class="chip-val" style="color:#38bdf8;">{{ number_format($selesai,0,',','.') }} {{ $satuan }}</span>
                    </div>
                    <div class="stat-chip chip-reject">
                        <span class="chip-label">Qty Reject</span>
                        <span class="chip-val" style="color:{{ $reject > 0 ? '#f87171' : '#475569' }};">{{ number_format($reject,0,',','.') }} {{ $satuan }}</span>
                    </div>
                    <div class="stat-chip chip-operator">
                        <span class="chip-label"><i class="bi bi-person me-1"></i>PIC</span>
                        <span class="chip-val" style="font-family:inherit; font-size:13px; color:#c7d2fe;">{{ $t->pic_operator ?: ($spk->penanggungJawab->nama_lengkap ?? '-') }}</span>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center pt-3" style="border-top:1px solid rgba(255,255,255,0.05);">
                    @if($t->status === 'Menunggu')
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('mulai-tahapan-produksi'))
                            <button type="button" class="btn-tahapan start" onclick="quickStartTahapanDetail({{ $spk->id }}, {{ $t->id }})">
                                <i class="bi bi-play-fill"></i> Mulai Pengerjaan
                            </button>
                        @endif
                    @endif

                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('input-hasil-harian-produksi') || auth()->user()->can('input-realisasi-produksi'))
                        <button type="button" class="btn-tahapan input" onclick="openInputDailyLogModal({{ $spk->id }}, {{ $t->id }})">
                            <i class="bi bi-plus-circle-fill me-1"></i> Catat Hasil Harian
                        </button>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-riwayat-input-produksi') || auth()->user()->can('lihat-realisasi-produksi') || auth()->user()->can('kontrol-tahapan-realisasi') || auth()->user()->can('input-hasil-harian-produksi') || auth()->user()->can('input-realisasi-produksi'))
                        <button type="button" class="btn-tahapan history" onclick="viewLogHistory({{ $t->id }})">
                            <i class="bi bi-clock-history"></i> Riwayat Input
                        </button>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('selesai-tahapan-produksi'))
                        @if($t->status !== 'Selesai')
                            <button type="button" class="btn-tahapan selesai"
                                onclick="markTahapanSelesaiDirect({{ $spk->id }}, {{ $t->id }}, {{ (float)$t->target_qty }}, {{ (float)$t->qty_selesai }}, '{{ e($t->nama_tahapan) }}', '{{ e($spk->satuan->nama_satuan ?? 'Pcs') }}')">
                                <i class="bi bi-check-lg"></i> Tandai Selesai
                            </button>
                        @endif
                    @endif
                </div>

            </div>
        @empty
            <div class="text-center py-5 rounded-3" style="background:rgba(15,23,42,0.5); border:1px dashed rgba(71,85,105,0.4);">
                <i class="bi bi-inbox fs-2 d-block mb-2" style="color:rgba(100,116,139,0.5);"></i>
                <span class="text-white-50">Belum ada tahapan produksi terdaftar.</span>
            </div>
        @endforelse
    </div>

    {{-- ── COMPLETE SPK FOOTER CARD ── --}}
    @if($spk->status !== 'Completed' && (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('approve-realisasi-produksi')))
    <div class="complete-spk-card mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color:#34d399; font-size:14.5px;">
                    <i class="bi bi-patch-check-fill fs-5"></i> Persetujuan Final &amp; Penutupan Produksi SPK
                </h6>
                <small style="color:rgba(148,163,184,0.65);">Konfirmasi seluruh tahapan pabrik telah selesai 100% dan barang jadi siap diserahterimakan ke Gudang</small>
            </div>
            <button type="button" class="btn-complete-spk w-100 w-md-auto" onclick="completeSpkFromDetail({{ $spk->id }})">
                <i class="bi bi-check-all fs-5 me-1"></i> Konfirmasi Selesai Produksi
            </button>
        </div>
    </div>
    @endif

</div>

@include('modul_produksi.realisasi._modal_input')

@push('scripts')
<script>
    function quickStartTahapanDetail(spkId, tahapanId) {
        $.ajax({
            url: `/produksi/realisasi-produksi/${tahapanId}/start`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Mulai Pengerjaan', text: res.message, timer: 1200, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1200);
                }
            },
            error: function() { Swal.fire('Gagal', 'Tidak dapat memulai pengerjaan.', 'error'); }
        });
    }

    function markTahapanSelesaiDirect(spkId, tahapanId, targetQty = 0, currentQty = 0, namaTahapan = '', satuan = 'Pcs') {
        const isUnderTarget = targetQty > 0 && currentQty < targetQty;
        const sisa = targetQty - currentQty;

        if (isUnderTarget) {
            Swal.fire({
                title: '❌ Target Pengerjaan Belum Tercapai!',
                html: `<div class="text-start"><p class="mb-2 text-white-50">Hasil pengerjaan tahapan <strong>${namaTahapan}</strong> saat ini baru mencapai <strong>${currentQty} / ${targetQty} ${satuan}</strong>.</p><div class="alert alert-danger text-white py-2 mb-3 font-monospace fw-bold" style="font-size: 13px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Pengerjaan Masih Kurang: ${sisa} ${satuan}!</div><p class="small text-white-50 mb-0">Tahapan belum dapat ditandai <strong>Selesai</strong>. Silakan catat hasil harian hingga mencapai target total terlebih dahulu.</p></div>`,
                icon: 'error',
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'Mengerti / Catat Hasil Dulu'
            });
            return;
        }

        Swal.fire({
            title: 'Tandai Tahapan Selesai?',
            html: `Tahapan <strong>${namaTahapan}</strong> akan ditandai selesai 100% dan pengerjaan akan dilanjutkan ke divisi berikutnya.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Selesaikan Tahapan',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `/produksi/realisasi-produksi/${tahapanId}/store-log`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tanggal: '{{ date("Y-m-d") }}',
                        qty_hasil_hari_ini: 0,
                        qty_reject_hari_ini: 0,
                        mark_as_selesai: 1
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Selesai!', res.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui status tahapan.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    }

    function completeSpkFromDetail(spkId) {
        Swal.fire({
            title: 'Konfirmasi Selesai Produksi SPK?',
            html: 'Seluruh tahapan pengerjaan pabrik akan ditutup dan status SPK diubah menjadi <b class="text-success">Completed</b>.<br><br><span class="text-body-secondary small">Penerimaan fisik barang jadi ke rak gudang selanjutnya dapat diproses oleh Tim Gudang pada modul <b>Penerimaan Barang Jadi SPK</b>.</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check-all me-1"></i> Ya, Selesaikan Produksi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/spk/${spkId}/complete-production`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        setTimeout(() => location.reload(), 1200);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyelesaikan produksi SPK.';
                        Swal.fire({
                            title: '❌ Produksi Belum Selesai!',
                            html: `<div class="text-start text-white-50 mt-1">${msg}</div>`,
                            icon: 'error',
                            confirmButtonColor: '#0ea5e9',
                            confirmButtonText: 'Mengerti / Selesaikan Tahapan Dulu'
                        });
                    }
                });
            }
        });
    }

    function previewMasterTahapanFoto(url, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `<div class="d-flex align-items-center justify-content-center gap-2 text-info fs-6 fw-bold"><i class="bi bi-image-fill"></i> Foto Referensi / SOP: ${title}</div>`,
                html: `<div class="p-2 text-center">
                        <img src="${url}" class="img-fluid rounded-3 shadow-lg border border-info border-opacity-25" style="max-height: 480px; object-fit: contain; width: 100%;">
                       </div>`,
                showCloseButton: true,
                confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Tutup',
                confirmButtonColor: '#334155',
                customClass: {
                    popup: 'rounded-4 shadow-2xl border border-secondary border-opacity-25'
                }
            });
        } else {
            window.open(url, '_blank');
        }
    }
</script>
@endpush

</x-layouts>
