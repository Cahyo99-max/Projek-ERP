<x-layouts title="Penerimaan Barang Jadi SPK (Hasil Produksi) - Modul Produksi">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<style>
    .swal2-container,
    div:where(.swal2-container) {
        z-index: 999999 !important;
    }
    .kpi-stat-card {
        background-color: var(--spk-card-bg) !important;
        border: 1px solid var(--spk-border-color) !important;
        box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.15);
        border-radius: 20px;
        padding: 1.25rem 1.4rem;
        transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .kpi-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 35px -10px rgba(0, 0, 0, 0.35);
        border-color: var(--kpi-glow, #3b82f6) !important;
    }
    .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--kpi-glow, #3b82f6), transparent);
    }
    .kpi-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        border: 1px solid var(--spk-border-color);
    }
    .filter-glass-card {
        background: var(--spk-card-bg);
        border: 1px solid var(--spk-border-color);
        border-radius: 20px;
        backdrop-filter: blur(12px);
    }
    .table-glass-card {
        background-color: var(--spk-card-bg) !important;
        border: 1px solid var(--spk-border-color) !important;
        box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.25);
        border-radius: 22px;
        overflow: hidden;
    }
    html[data-bs-theme="dark"] .table-glass-card,
    .dark-mode .table-glass-card {
        border-color: rgba(59, 130, 246, 0.25) !important;
        box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }

    /* Table Dark & Light Mode Theme Support */
    html[data-bs-theme="dark"] #table_barang_jadi tbody tr:hover td,
    .dark-mode #table_barang_jadi tbody tr:hover td {
        background: rgba(37, 99, 235, 0.16) !important;
        color: #ffffff !important;
    }

    html[data-bs-theme="light"] #table_barang_jadi tbody tr:hover td,
    [data-bs-theme="light"] #table_barang_jadi tbody tr:hover td {
        background: rgba(59, 130, 246, 0.08) !important;
        color: #0f172a !important;
    }

    .btn-emerald-vibrant {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        transition: all 0.22s ease-in-out;
    }
    .btn-emerald-vibrant:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    }
    /* MODERN VIBRANT GRADIENT CARDS */
    .modern-summary-card {
        border-radius: 18px !important;
        padding: 1.2rem 1.35rem;
        position: relative;
        overflow: hidden;
        border: none !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: #ffffff !important;
    }
    .modern-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.25);
    }
    
    .card-grad-emerald {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%) !important;
    }
    .card-grad-blue {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%) !important;
    }
    .card-grad-crimson {
        background: linear-gradient(135deg, #9f1239 0%, #be123c 50%, #e11d48 100%) !important;
    }
    .card-grad-violet {
        background: linear-gradient(135deg, #581c87 0%, #6b21a8 50%, #7e22ce 100%) !important;
    }

    .summary-card-circle-bg {
        position: absolute;
        right: -20px;
        bottom: -20px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.12);
        pointer-events: none;
    }
    .summary-card-circle-bg-2 {
        position: absolute;
        right: 45px;
        top: -30px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }

    .summary-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
    }

    .summary-card-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: rgba(255, 255, 255, 0.88);
        margin-bottom: 0.35rem;
    }
    .summary-card-val {
        font-size: 1.8rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
        font-family: var(--bs-font-monospace);
    }
    .summary-card-sub {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.78);
        margin-top: 0.35rem;
    }
</style>
@endpush

<div class="container-fluid px-3 px-md-4 py-3">
    {{-- BREADCRUMB & HEADER TITLE --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Modul Produksi</li>
                    <li class="breadcrumb-item active fw-bold">Penerimaan Barang Jadi</li>
                </ol>
            </nav>
            <h4 class="fw-bold m-0 spk-text-contrast d-flex align-items-center gap-2.5">
                <span class="p-2 rounded-3 text-white d-inline-flex align-items-center justify-content-center"
                      style="background: linear-gradient(135deg, #10b981, #059669); width: 42px; height: 42px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                    <i class="bi bi-box-seam-fill fs-5"></i>
                </span>
                <span>Penerimaan Barang Jadi SPK (Hasil Produksi)</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2.5 flex-wrap">
            <button type="button" class="btn btn-outline-emerald-vibrant rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs position-relative" data-bs-toggle="modal" data-bs-target="#modalFilterPenerimaanBarangJadi" title="Buka Filter Data Penerimaan Hasil Produksi">
                <i class="bi bi-funnel-fill fs-6 text-emerald"></i>
                <span>Filter Data</span>
                <span id="filter_active_indicator" class="position-absolute top-0 start-100 translate-middle p-1.5 bg-emerald border border-light rounded-circle d-none"></span>
            </button>
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-penerimaan-barang-jadi'))
            <button type="button" class="btn btn-emerald-vibrant rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                    onclick="openModalInputPenerimaanBarangJadi()" title="Catat Penyerahan Hasil Produksi SPK Baru">
                <i class="bi bi-plus-lg fs-6"></i>
                <span>Terima Hasil Produksi</span>
            </button>
            @endif
        </div>
    </div>

    {{-- KPI STATISTICS HEADER CARDS (PREMIUM GRADIENT) --}}
    <div class="row g-3 mb-4">
        {{-- CARD 1: EMERALD (SPK SELESAI) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-emerald">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">SPK SELESAI (COMPLETED)</div>
                        <div class="summary-card-val">{{ number_format($totalSpkSelesai, 0, ',', '.') }}</div>
                        <div class="summary-card-sub">Target Tercapai</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-check2-all text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: BLUE (TOTAL HASIL BAIK) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-blue">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">TOTAL HASIL BAIK (GOOD)</div>
                        <div class="summary-card-val">{{ number_format($totalHasilBaik, 0, ',', '.') }}</div>
                        <div class="summary-card-sub">Masuk Stok Gudang</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-box-arrow-in-down-right text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: CRIMSON (TOTAL REJECT) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-crimson">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">TOTAL REJECT / AFVAL</div>
                        <div class="summary-card-val">{{ number_format($totalHasilReject, 0, ',', '.') }}</div>
                        <div class="summary-card-sub">Produk Cacat / Damage</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-x-circle-fill text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 4: VIOLET (SESI PENERIMAAN) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-violet">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">SESI PENERIMAAN</div>
                        <div class="summary-card-val" style="font-size:1.5rem;">{{ number_format($totalSesiPenerimaan, 0, ',', '.') }} Sesi</div>
                        <div class="summary-card-sub">Total Transaksi Penerimaan</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-layers-fill text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVE FILTER CHIP INDICATOR BAR (POPULAR COMPACT STYLE) --}}
    <div id="active_filter_bar" class="d-none mb-3">
        <div class="p-2.5 px-3 rounded-3 bg-emerald bg-opacity-10 border border-emerald border-opacity-25 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 font-monospace small">
                <span class="badge spk-badge-green rounded-pill px-2.5 py-1"><i class="bi bi-funnel-fill me-1"></i>Filter Aktif</span>
                <span id="active_filter_summary_text" class="spk-text-main fw-bold"></span>
            </div>
            <button type="button" onclick="resetFilterForm()" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 font-monospace fw-semibold d-inline-flex align-items-center gap-1">
                <i class="bi bi-x-circle-fill"></i> Hapus Filter
            </button>
        </div>
    </div>

    {{-- DATATABLES TABLE GLASS CARD --}}
    <div class="table-glass-card p-3 p-md-4">
        <div class="table-responsive">
            <table id="table_barang_jadi" class="table align-middle w-100 spk-table-main" style="width:100%;">
                <thead>
                    <tr>
                        <th class="ps-3 text-center" style="width: 50px;">No</th>
                        <th style="min-width: 280px;">Informasi SPK & Produk Jadi</th>
                        <th style="min-width: 220px;">Progress Hasil Produksi</th>
                        <th style="min-width: 170px;">Frekuensi Sesi</th>
                        <th class="text-center" style="width: 130px;">Status SPK</th>
                        <th class="text-center pe-3" style="min-width: 250px; width: 250px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Datatables AJAX load --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL FILTER PENERIMAAN BARANG JADI --}}
<div class="modal fade" id="modalFilterPenerimaanBarangJadi" tabindex="-1" aria-labelledby="modalFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content rounded-4 shadow-lg border-0">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold spk-text-main d-flex align-items-center gap-2 fs-6" id="modalFilterLabel">
                    <div class="p-2 rounded-circle bg-emerald bg-opacity-10 text-emerald d-inline-flex">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                    <span>Filter Penerimaan Barang Jadi</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formFilterPenerimaan">
                    <div class="mb-3">
                        <label class="form-label small fw-bold spk-text-muted mb-1">Gudang Hasil Produksi</label>
                        <select id="filter_gudang_id" class="form-select spk-input rounded-3 font-monospace">
                            <option value="all">-- Semua Gudang Hasil --</option>
                            @foreach($gudangList as $g)
                                <option value="{{ $g->id }}">[{{ $g->kode_gudang }}] {{ $g->nama_gudang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold spk-text-muted mb-1">Tanggal Awal</label>
                            <input type="date" id="filter_start_date" class="form-control spk-input rounded-3 font-monospace">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold spk-text-muted mb-1">Tanggal Akhir</label>
                            <input type="date" id="filter_end_date" class="form-control spk-input rounded-3 font-monospace">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2">
                        <small class="w-100 spk-text-muted font-monospace mb-1" style="font-size: 11px;">Pilihan Cepat Rentang Tanggal:</small>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace" onclick="setQuickDateFilter('today')">Hari Ini</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace" onclick="setQuickDateFilter('7days')">7 Hari</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace" onclick="setQuickDateFilter('month')">Bulan Ini</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace" onclick="setQuickDateFilter('all')">Semua Waktu</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Filter</span>
                </button>
                <button type="button" id="btn_apply_filter" class="btn btn-sm btn-emerald-vibrant rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-check-lg"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODALS COMPONENT INCLUDES --}}
@include('modul_produksi.barang_jadi._modal_input')
@include('modul_produksi.barang_jadi._modal_detail')
@include('modul_produksi.barang_jadi._modal_lookup_spk')
@include('modul_produksi.barang_jadi._modal_lookup_gudang')
@include('modul_produksi.barang_jadi._modal_lookup_rak')
@include('modul_produksi.barang_jadi._modal_sesuaikan_hpp')
@include('modul_produksi.barang_jadi._modal_jurnal_spk')

@push('scripts')
<script>
    let tableBarangJadi = null;

    $(document).ready(function () {
        tableBarangJadi = $('#table_barang_jadi').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('produksi.barang-jadi.index') }}",
                data: function (d) {
                    d.gudang_id  = $('#filter_gudang_id').val();
                    d.start_date = $('#filter_start_date').val();
                    d.end_date   = $('#filter_end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small ps-3 spk-text-muted-contrast' },
                { data: 'spk_info', name: 'nomor_spk' },
                { data: 'progress_hasil', name: 'jumlah_selesai', orderable: false, searchable: false },
                { data: 'frekuensi_sesi', name: 'id', orderable: false, searchable: false },
                { data: 'status_spk', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-3' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari SPK / Dokumen...",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ SPK",
                infoEmpty: "Menampilkan 0 data",
                zeroRecords: "Tidak ada data penyerahan barang jadi ditemukan.",
                processing: '<div class="spinner-border text-emerald-vibrant" role="status"><span class="visually-hidden">Memuat...</span></div>'
            },
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3"ip>',
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });

        $('#btn_apply_filter').on('click', function () {
            $('#modalFilterPenerimaanBarangJadi').modal('hide');
            tableBarangJadi.ajax.reload();
            updateFilterIndicator();
        });

        $('#btn_reset_filter').on('click', function () {
            resetFilterForm();
        });
    });

    function setQuickDateFilter(type) {
        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (type === 'today') {
            const dateStr = formatDate(today);
            $('#filter_start_date').val(dateStr);
            $('#filter_end_date').val(dateStr);
        } else if (type === '7days') {
            const past7 = new Date();
            past7.setDate(today.getDate() - 6);
            $('#filter_start_date').val(formatDate(past7));
            $('#filter_end_date').val(formatDate(today));
        } else if (type === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            $('#filter_start_date').val(formatDate(firstDay));
            $('#filter_end_date').val(formatDate(lastDay));
        } else {
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
        }
    }

    function updateFilterIndicator() {
        const gudangVal = $('#filter_gudang_id').val();
        const startVal  = $('#filter_start_date').val();
        const endVal    = $('#filter_end_date').val();

        const isFiltered = (gudangVal && gudangVal !== 'all') || startVal || endVal;
        
        if (isFiltered) {
            $('#filter_active_indicator').removeClass('d-none');
            $('#active_filter_bar').removeClass('d-none');

            let summaryParts = [];
            if (gudangVal && gudangVal !== 'all') {
                const gudangName = $('#filter_gudang_id option:selected').text();
                summaryParts.push(`Gudang: ${gudangName}`);
            }
            if (startVal && endVal) {
                summaryParts.push(`Periode: ${startVal} s/d ${endVal}`);
            } else if (startVal) {
                summaryParts.push(`Mulai: ${startVal}`);
            } else if (endVal) {
                summaryParts.push(`Sampai: ${endVal}`);
            }

            $('#active_filter_summary_text').text(summaryParts.join(' | '));
        } else {
            $('#filter_active_indicator').addClass('d-none');
            $('#active_filter_bar').addClass('d-none');
            $('#active_filter_summary_text').text('');
        }
    }

    function resetFilterForm() {
        $('#filter_gudang_id').val('all');
        $('#filter_start_date').val('');
        $('#filter_end_date').val('');
        $('#modalFilterPenerimaanBarangJadi').modal('hide');
        tableBarangJadi.ajax.reload();
        updateFilterIndicator();
    }

    function reloadTableBarangJadi() {
        if (tableBarangJadi) {
            tableBarangJadi.ajax.reload(null, false);
        }
    }

    function warnBahanBelumKeluar(nomorSpk) {
        Swal.fire({
            title: 'Pengeluaran Bahan Belum Di-input!',
            html: `Penerimaan Barang Jadi ditolak karena transaksi Pengeluaran Bahan Baku untuk <b>SPK #${nomorSpk}</b> belum dicatat oleh tim gudang bahan.<br><br><span class="text-muted small">Harap ingatkan tim gudang bahan untuk meng-input Pengeluaran Bahan Baku terlebih dahulu!</span>`,
            icon: 'warning',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Saya Mengerti'
        });
    }

    let _activeAdjSpkData = null;

    function bukaModalSesuaikanHpp(spkId) {
        Swal.fire({
            title: 'Memuat Data SPK...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/detail`,
            method: 'GET',
            success: function (res) {
                Swal.close();
                if (!res.success) {
                    Swal.fire('Gagal!', 'Gagal memuat detail SPK.', 'error');
                    return;
                }

                _activeAdjSpkData = res;
                const spk = res.spk;

                if (res.is_hpp_closed && !{{ auth()->user()->hasAnyRole(['Super Admin', 'Owner']) ? 'true' : 'false' }}) {
                    showHppClosedWarning(spk.nomor_spk);
                    return;
                }

                $('#adj_spk_id').val(spk.id);
                $('#adj_info_nomor_spk').text(spk.nomor_spk);
                
                const targetQty = Number(spk.jumlah_rencana || res.target_qty || 0);
                const satName = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');
                $('#adj_info_target_qty').text(`${targetQty.toLocaleString('id-ID')} ${satName}`);

                const totalBahan = parseFloat(res.total_material_cost || 0);
                $('#adj_cost_bahan').val(totalBahan.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#adj_cost_bahan').data('raw-val', totalBahan);

                const tkRaw    = parseFloat(spk.estimasi_biaya_tk) || 0;
                const ohRaw    = parseFloat(spk.estimasi_biaya_overhead) || 0;

                $('#adj_raw_tk').val(tkRaw);
                $('#adj_raw_overhead').val(ohRaw);
                $('#adj_cost_tk').val(tkRaw > 0 ? tkRaw.toLocaleString('id-ID') : '');
                $('#adj_cost_overhead').val(ohRaw > 0 ? ohRaw.toLocaleString('id-ID') : '');

                // Set preview jurnal keterangan berdasarkan nomor SPK
                // (tidak lagi dipakai — jurnal ada di modal terpisah)

                recalculateAdjustmentHpp();

                $('#modalSesuaikanHpp').modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire('Error', 'Gagal memuat detail SPK.', 'error');
            }
        });
    }

    function parseAdjRibuan(val) {
        if (typeof val === 'string') val = val.replace(/\./g, '').replace(',', '.');
        return parseFloat(val) || 0;
    }

    function onAdjCostInput(el, hiddenId) {
        let raw = el.value.replace(/[^\d]/g, '');
        const num = parseInt(raw, 10) || 0;
        $('#' + hiddenId).val(num);
        // Format dengan titik ribuan
        el.value = num > 0 ? num.toLocaleString('id-ID') : '';
        // Pindahkan kursor ke akhir
        const len = el.value.length;
        el.setSelectionRange(len, len);
        recalculateAdjustmentHpp();
    }

    function recalculateAdjustmentHpp() {
        if (!_activeAdjSpkData) return;
        const spk          = _activeAdjSpkData.spk;
        const targetQty    = parseFloat(spk.jumlah_rencana) || 0;
        const totalSelesai = parseFloat(_activeAdjSpkData.jumlah_selesai) || 0;
        const divisorQty   = totalSelesai > 0 ? totalSelesai : (targetQty > 0 ? targetQty : 1);
        const satName      = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');

        const totalBahan   = parseFloat($('#adj_cost_bahan').data('raw-val')) || 0;
        const tkCost       = parseFloat($('#adj_raw_tk').val()) || 0;
        const ohCost       = parseFloat($('#adj_raw_overhead').val()) || 0;

        const totalCost    = totalBahan + tkCost + ohCost;
        const hppUnit      = divisorQty > 0 ? (totalCost / divisorQty) : 0;

        const labelPembagi = totalSelesai > 0
            ? `${totalSelesai.toLocaleString('id-ID')} ${satName} (Hasil Bagus)`
            : `${targetQty.toLocaleString('id-ID')} ${satName} (Target Rencana)`;

        $('#adj_display_subtotal_biaya').text('Rp ' + totalCost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#adj_display_qty_pembagi').text(labelPembagi);
        $('#adj_display_hpp_unit').text('Rp ' + hppUnit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    function submitHppAdjustmentForm() {
        const form = $('#formSesuaikanHpp');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const spkId = $('#adj_spk_id').val();
        const submitBtn = $('#btn_save_adj_hpp');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/adjust-hpp`,
            method: 'POST',
            data: form.serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    $('#modalSesuaikanHpp').modal('hide');
                    reloadTableBarangJadi();
                }
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Gagal menyesuaikan nilai HPP SPK.';
                Swal.fire('Gagal!', msg, 'error');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle-fill fs-6 me-1"></i> Simpan Penyesuaian');
            }
        });
    }

    function showHppLockedWarning(pendingText) {
        const bulletList = pendingText.split(', ').map(item => `
            <div class="d-flex align-items-center gap-2.5 mb-2 p-2 rounded-2" style="background: rgba(239, 68, 68, 0.08); border-left: 3px solid #ef4444;">
                <i class="bi bi-x-circle-fill text-danger fs-6"></i> 
                <span class="fw-semibold spk-text-contrast" style="font-size: 0.88rem;">${item}</span>
            </div>`).join('');

        Swal.fire({
            title: '<div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-5 fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i> Prasyarat HPP Belum Terpenuhi</div>',
            html: `<div class="text-start mb-3" style="font-size: 0.92rem; line-height: 1.45;">
                        Untuk dapat melakukan kalkulasi Penyesuaian HPP Aktual secara presisi, seluruh tahapan produksi berikut wajib diselesaikan terlebih dahulu:
                   </div>
                   <div class="mb-3 text-start">
                        ${bulletList}
                   </div>
                   <div class="spk-text-muted text-start" style="font-size: 0.82rem; line-height: 1.4;">
                        <i class="bi bi-info-circle me-1 text-info"></i>Selesaikan seluruh proses di atas agar alokasi Biaya Bahan, Gaji, & Overhead dapat terhitung 100% akurat.
                   </div>`,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-semibold'
            }
        });
    }

    function showHppClosedWarning(nomorSpk) {
        Swal.fire({
            title: '<div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-5 fw-bold"><i class="bi bi-shield-lock-fill text-warning fs-4"></i> Status HPP Terkunci (Closed)</div>',
            html: `<div class="text-start mb-3" style="font-size: 0.93rem; line-height: 1.5;">
                        Kalkulasi HPP untuk SPK <span class="badge spk-badge-cyan font-monospace px-2 py-1">#${nomorSpk || ''}</span> telah difinalisasi dan berstatus <b>Closed (Terkunci)</b>.
                   </div>
                   <div class="p-3.5 rounded-3 border mb-3 text-start shadow-xs" style="background-color: var(--spk-card-bg, rgba(245, 158, 11, 0.06)); border-color: rgba(245, 158, 11, 0.25) !important;">
                       <div class="d-flex align-items-center gap-2 mb-1.5 text-warning fw-bold font-monospace" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                           <i class="bi bi-lock-fill fs-6"></i> STATUS: HPP SPK CLOSED
                       </div>
                       <div class="spk-text-muted" style="font-size: 0.86rem; line-height: 1.45;">
                           Demi menjaga integritas audit serta konsistensi laporan jurnal keuangan perusahaan, kalkulasi HPP yang sudah ditutup tidak dapat diubah kembali secara langsung.
                       </div>
                   </div>
                   <div class="spk-text-muted text-start" style="font-size: 0.82rem; line-height: 1.4;">
                       <i class="bi bi-headset text-info me-1"></i>Apabila memerlukan penyesuaian ulang HPP, silakan hubungi <b>Administrator Sistem (Super Admin)</b> untuk otorisasi pembukaan kunci.
                   </div>`,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-semibold'
            }
        });
    }

    function bukaKunciHppSpk(spkId, nomorSpk) {
        Swal.fire({
            title: '<div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-5 fw-bold"><i class="bi bi-unlock-fill text-warning fs-4"></i> Buka Kunci HPP SPK</div>',
            html: `<div class="text-start mb-3" style="font-size: 0.93rem; line-height: 1.45;">
                        Apakah Anda yakin ingin membuka kembali kunci kalkulasi HPP untuk SPK <span class="badge spk-badge-cyan font-monospace px-2 py-1">#${nomorSpk || ''}</span>?
                   </div>
                   <div class="p-3 rounded-3 border mb-2 text-start" style="background-color: var(--spk-card-bg, rgba(245, 158, 11, 0.06)); border-color: rgba(245, 158, 11, 0.25) !important;">
                       <div class="d-flex align-items-center gap-2 text-warning fw-semibold" style="font-size: 0.85rem;">
                           <i class="bi bi-exclamation-triangle-fill me-1"></i>Otorisasi Eksekutif (Super Admin / Owner)
                       </div>
                       <div class="spk-text-muted mt-1" style="font-size: 0.83rem;">
                           Tindakan ini mengizinkan biaya Tenaga Kerja & Overhead dihitung ulang serta memperbarui HPP barang jadi.
                       </div>
                   </div>`,
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-unlock-fill me-1"></i> Ya, Buka Kunci',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                confirmButton: 'btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark',
                cancelButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Membuka kunci HPP SPK...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/produksi/barang-jadi/spk/${spkId}/unlock-hpp`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Kunci HPP Dibuka!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBarangJadi();
                    },
                    error: function (err) {
                        const msg = err.responseJSON?.message || 'Gagal membuka kunci HPP SPK.';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    }

    /* ============================================================
     * MODAL JURNAL AKUNTANSI SPK PENERIMAAN
     * Klik tombol "Lihat Jurnal" → fetch sessions → render baris jurnal
     * ============================================================ */
    function showSpkJurnalModal(spkId, nomorSpk) {
        // Reset state
        $('#jurnal_spk_loading').removeClass('d-none');
        $('#jurnal_spk_empty').addClass('d-none');
        $('#jurnal_spk_content').addClass('d-none');
        $('#modalJurnalSpkSubtitle').text(`SPK: ${nomorSpk || '—'}`);
        $('#modalJurnalSpkPenerimaan').modal('show');

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/history/sessions`,
            method: 'GET',
            data: { per_page: 100 },
            success: function (res) {
                $('#jurnal_spk_loading').addClass('d-none');

                const sessions = res.data || [];
                const sessionsWithJurnal = sessions.filter(s => s.jurnal && s.jurnal.details && s.jurnal.details.length > 0);

                if (sessionsWithJurnal.length === 0) {
                    $('#jurnal_spk_empty').removeClass('d-none');
                    return;
                }

                // Hitung total
                let grandTotalDebit = 0;
                let grandTotalKredit = 0;
                let html = '';

                sessionsWithJurnal.forEach(function (s, idx) {
                    const j = s.jurnal;
                    const tgl = j.tanggal_jurnal
                        ? new Date(j.tanggal_jurnal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                        : '-';

                    grandTotalDebit  += parseFloat(j.total_debit || 0);
                    grandTotalKredit += parseFloat(j.total_kredit || 0);

                    // Header sesi
                    html += `
                    <div class="rounded-3 border overflow-hidden shadow-sm" style="border-color: rgba(99,102,241,0.25) !important;">
                        <div class="d-flex align-items-center gap-3 px-3 py-2.5"
                             style="background: linear-gradient(90deg, rgba(99,102,241,0.1) 0%, rgba(99,102,241,0.03) 100%); border-bottom: 1px solid rgba(99,102,241,0.15);">
                            <div class="flex-grow-1 d-flex align-items-center gap-2 flex-wrap">
                                <i class="bi bi-receipt text-indigo-500" style="color: #6366f1;"></i>
                                <span class="fw-bold font-monospace text-dark" style="font-size: 12.5px;">${j.nomor_jurnal || 'JRN-AUTO'}</span>
                                <span class="badge rounded-pill" style="background: rgba(16,185,129,0.15); color: #059669; font-size: 10px;">POSTED</span>
                                <span class="text-muted" style="font-size: 11px;">| Ref: ${s.nomor_penerimaan}</span>
                            </div>
                            <span class="text-muted font-monospace" style="font-size: 11px;">${tgl}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size: 12px;">
                                <thead style="background: rgba(248,250,252,0.8);">
                                    <tr class="text-muted" style="font-size: 10px; font-weight: 600;">
                                        <th class="ps-3" style="width: 30px;">#</th>
                                        <th>KODE AKUN</th>
                                        <th>NAMA AKUN</th>
                                        <th>KETERANGAN</th>
                                        <th class="text-end" style="width: 130px;">DEBIT</th>
                                        <th class="text-end pe-3" style="width: 130px;">KREDIT</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    j.details.forEach(function (d, di) {
                        const isDebit  = parseFloat(d.debit || 0) > 0;
                        const isKredit = parseFloat(d.kredit || 0) > 0;
                        const debitVal  = parseFloat(d.debit  || 0);
                        const kreditVal = parseFloat(d.kredit || 0);

                        const badge = isDebit
                            ? `<span class="badge rounded-1 fw-bold" style="background:rgba(16,185,129,.15);color:#059669;font-size:9px;">D</span>`
                            : `<span class="badge rounded-1 fw-bold" style="background:rgba(59,130,246,.15);color:#2563eb;font-size:9px;">K</span>`;

                        const kodeAkun = d.account ? d.account.code : '-';
                        const namaAkun = d.account ? d.account.name : '—';

                        html += `
                                        <tr class="${di < j.details.length - 1 ? 'border-bottom' : ''}">
                                            <td class="ps-3 text-center">${badge}</td>
                                            <td class="font-monospace fw-semibold" style="font-size: 11px;">${kodeAkun}</td>
                                            <td style="font-size: 11.5px; max-width: 200px;">${namaAkun}</td>
                                            <td class="text-muted" style="font-size: 11px;">${d.keterangan || '-'}</td>
                                            <td class="text-end font-monospace ${isDebit ? 'fw-bold text-success' : 'text-muted'}" style="font-size: 11.5px;">
                                                ${debitVal > 0 ? 'Rp ' + debitVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                                            </td>
                                            <td class="text-end pe-3 font-monospace ${isKredit ? 'fw-bold text-primary' : 'text-muted'}" style="font-size: 11.5px;">
                                                ${kreditVal > 0 ? 'Rp ' + kreditVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                                            </td>
                                        </tr>`;
                    });

                    // Total row per sesi
                    html += `
                                </tbody>
                                <tfoot style="background:rgba(99,102,241,.05); border-top:2px solid rgba(99,102,241,.2);">
                                    <tr>
                                        <td colspan="4" class="ps-3 text-end fw-bold" style="font-size:11.5px;">TOTAL JURNAL INI</td>
                                        <td class="text-end fw-extrabold text-success font-monospace" style="font-size:12.5px;">Rp ${parseFloat(j.total_debit||0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                        <td class="pe-3 text-end fw-extrabold text-primary font-monospace" style="font-size:12.5px;">Rp ${parseFloat(j.total_kredit||0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>`;
                });

                // Update summary strip
                $('#jurnal_spk_total_sesi').text(sessionsWithJurnal.length);
                $('#jurnal_spk_total_debit').text('Rp ' + grandTotalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#jurnal_spk_total_kredit').text('Rp ' + grandTotalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                $('#jurnal_spk_sessions_container').html(html);
                $('#jurnal_spk_content').removeClass('d-none');
            },
            error: function () {
                $('#jurnal_spk_loading').addClass('d-none');
                Swal.fire('Error', 'Gagal memuat data jurnal akuntansi.', 'error');
                $('#modalJurnalSpkPenerimaan').modal('hide');
            }
        });
    }
</script>
@endpush
</x-layouts>
