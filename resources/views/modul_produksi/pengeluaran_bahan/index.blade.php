<x-layouts title="Pengeluaran Bahan Baku SPK - Modul Produksi">

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

    /* Sembunyikan box loading processing agar live reload berjalan mulus di latar belakang */
    div.dataTables_processing,
    #tabelPengeluaranBahan_processing {
        display: none !important;
    }

    @media (max-width: 575.98px) {
        .table-glass-card {
            border-radius: 16px !important;
            padding: 0.75rem 0.65rem !important;
            margin-left: -0.35rem;
            margin-right: -0.35rem;
        }
        .kpi-stat-card {
            border-radius: 16px !important;
            padding: 1rem 1.15rem !important;
        }
    }

    /* Multi-level Modal Stacking Layering */
    #modalDetailPengeluaran {
        z-index: 1050 !important;
    }
    #modalTambahPengeluaran {
        z-index: 1070 !important;
    }
    #modalDetailJurnalPengeluaran {
        z-index: 1075 !important;
    }
    #modalLookupSpk,
    #modalLookupBahanSpk,
    #modalLookupGudang,
    #modalLookupKaryawan {
        z-index: 1090 !important;
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
    
    .card-grad-cyan {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%) !important;
    }
    .card-grad-purple {
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 50%, #8b5cf6 100%) !important;
    }
    .card-grad-rose {
        background: linear-gradient(135deg, #be123c 0%, #e11d48 50%, #f43f5e 100%) !important;
    }
    .card-grad-emerald {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%) !important;
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

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">
    {{-- HEADER & ACTIONS --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2.5">
                <div class="p-2.5 rounded-3 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.2);">
                    <i class="bi bi-box-arrow-right fs-4"></i>
                </div>
                <div>
                    <h4 class="fw-bold spk-text-main mb-0 fs-5 fs-sm-4">Pengeluaran Bahan Baku SPK</h4>
                    <p class="spk-text-muted small mb-0 d-none d-sm-block">Kontrol serah terima material bertahap dari gudang logistik ke line produksi.</p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap flex-sm-nowrap">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3.5 py-2 fw-semibold shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalFilterPengeluaran">
                <i class="bi bi-funnel-fill text-primary"></i>
                <span>Filter Data</span>
            </button>
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-pengeluaran-bahan'))
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="openModalTambahPengeluaran()">
                <i class="bi bi-plus-circle-fill"></i>
                <span>+ Pengeluaran Bahan</span>
            </button>
            @endif
        </div>
    </div>

    {{-- STATS KPI CARDS (PREMIUM GRADIENT) --}}
    <div class="row g-3 mb-4">
        {{-- CARD 1: CYAN (TOTAL PENGELUARAN) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-cyan">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">TOTAL PENGELUARAN</div>
                        <div class="summary-card-val" id="cardTotalPengeluaran">{{ \App\Models\SpkPengeluaranBahan::forActiveCompany()->count() }}</div>
                        <div class="summary-card-sub">Dokumen Terbit</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-boxes text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: PURPLE (SPK IN PROGRESS) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-purple">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">SPK IN PROGRESS</div>
                        <div class="summary-card-val" id="cardSpkInProgress">{{ \App\Models\SpkProduksi::forActiveCompany()->where('status', 'In Progress')->count() }}</div>
                        <div class="summary-card-sub">Sedang Berjalan</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-gear-wide-connected text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: ROSE (GUDANG BAHAN BAKU) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-rose">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">GUDANG BAHAN BAKU</div>
                        <div class="summary-card-val" id="cardGudangBahan">{{ $gudangList->count() }}</div>
                        <div class="summary-card-sub">Gudang Terdaftar</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-building-check text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 4: EMERALD (PENGELUARAN HARI INI) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="modern-summary-card card-grad-emerald">
                <div class="summary-card-circle-bg"></div>
                <div class="summary-card-circle-bg-2"></div>
                <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                    <div>
                        <div class="summary-card-title">PENGELUARAN HARI INI</div>
                        <div class="summary-card-val" id="cardPengeluaranHariIni">{{ \App\Models\SpkPengeluaranBahan::forActiveCompany()->where(function ($q) {
                            $today = date('Y-m-d');
                            $q->whereDate('tanggal_pengeluaran', $today)
                              ->orWhereDate('created_at', $today);
                        })->count() }}</div>
                        <div class="summary-card-sub">{{ date('d M Y') }}</div>
                    </div>
                    <div class="summary-icon-box">
                        <i class="bi bi-calendar-event text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DATATABLE CARD --}}
    <div class="table-glass-card p-3 p-md-4 shadow-lg">
        <div class="d-flex align-items-center justify-content-between mb-2 d-md-none text-muted small">
            <span><i class="bi bi-arrows-expand me-1"></i>Tabel dapat digeser ke samping</span>
        </div>
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="tabelPengeluaranBahan" class="table table-hover align-middle text-nowrap w-100 mb-0" style="min-width: 1100px;">
                <thead>
                    <tr class="small text-uppercase fw-bold spk-text-muted" style="border-bottom: 2px solid rgba(59, 130, 246, 0.18); font-size: 11.5px; letter-spacing: 0.04em;">
                        <th style="width: 50px;" class="text-center">NO</th>
                        <th style="min-width: 270px;">SPK & PRODUK JADI</th>
                        <th style="min-width: 220px;">PROGRES PENGELUARAN BAHAN</th>
                        <th style="min-width: 190px;">SESI PENYERAHAN</th>
                        <th class="text-center" style="width: 140px;">STATUS SPK</th>
                        <th class="text-center" style="min-width: 240px; width: 250px;">AKSI</th>
                    </tr>
                </thead>
                <tbody class="spk-text-main">
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('modul_produksi.pengeluaran_bahan._modal_filter')
@include('modul_produksi.pengeluaran_bahan._modal_input')
@include('modul_produksi.pengeluaran_bahan._modal_detail')
@include('modul_produksi.pengeluaran_bahan._modal_lookup_material')
@include('modul_produksi.pengeluaran_bahan._modal_lookup_spk')
@include('modul_produksi.pengeluaran_bahan._modal_lookup_gudang')
@include('modul_produksi.pengeluaran_bahan._modal_lookup_karyawan')
@include('modul_produksi.pengeluaran_bahan._modal_jurnal_spk')
@include('modul_produksi.pengeluaran_bahan._modal_closing')

@push('scripts')
<script>
    let dtPengeluaran = null;

    $(document).ready(function() {
        dtPengeluaran = $('#tabelPengeluaranBahan').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: '{{ route("produksi.pengeluaran-bahan.index") }}',
                data: function(d) {
                    d.gudang_id = $('#modal_filter_gudang').val();
                    d.start_date = $('#modal_filter_start_date').val();
                    d.end_date = $('#modal_filter_end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center spk-text-muted fw-bold' },
                { data: 'spk_info', name: 'nomor_spk' },
                { data: 'progress_pengeluaran', name: 'progress_pengeluaran', orderable: false, searchable: false },
                { data: 'frekuensi_sesi', name: 'frekuensi_sesi', orderable: false, searchable: false },
                { data: 'status_spk', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari SPK, produk...",
                lengthMenu: "Tampil _MENU_ data",
                info: "Menampilkan _START_-_END_ dari _TOTAL_ SPK",
                infoEmpty: "Tidak ada data pengeluaran",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            }
        });

        dtPengeluaran.on('xhr', function() {
            const json = dtPengeluaran.ajax.json();
            if (json) {
                if (json.totalPengeluaran !== undefined) $('#cardTotalPengeluaran').text(json.totalPengeluaran);
                if (json.spkInProgress !== undefined) $('#cardSpkInProgress').text(json.spkInProgress);
                if (json.pengeluaranHariIni !== undefined) $('#cardPengeluaranHariIni').text(json.pengeluaranHariIni);
            }
        });

        // Live Real-Time Auto Refresh Poller (Update Status Menunggu, Closed, Open secara otomatis setiap 4 detik)
        setInterval(function () {
            let isSearching = $('#tabelPengeluaranBahan_filter input').is(':focus');
            let isModalOpen = $('.modal.show').length > 0;
            let isSwalOpen = $('.swal2-container').length > 0;
            if (!isSearching && !isModalOpen && !isSwalOpen && dtPengeluaran && document.visibilityState === 'visible') {
                dtPengeluaran.ajax.reload(null, false); // Silent background reload tanpa reset pagination
            }
        }, 4000);
    });

    /* ============================================================
     * MODAL JURNAL FULL AKUNTANSI PENGELUARAN BAHAN SPK
     * ============================================================ */
    function showSpkJurnalFullPbModal(spkId, nomorSpk) {
        if (!spkId && typeof _currentSpkId !== 'undefined') {
            spkId = _currentSpkId;
        }
        if (!spkId) return;

        // Reset state
        $('#jurnal_spk_pb_loading').removeClass('d-none');
        $('#jurnal_spk_pb_empty').addClass('d-none');
        $('#jurnal_spk_pb_content').addClass('d-none');
        $('#modalJurnalSpkPengeluaranSubtitle').text(`SPK: ${nomorSpk || '—'}`);
        $('#modalJurnalSpkPengeluaran').modal('show');

        // 1. Try to load published consolidated closing journal
        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/jurnal-closing`,
            method: 'GET',
            success: function (res) {
                $('#jurnal_spk_pb_loading').addClass('d-none');

                if (res.success && res.jurnal && res.jurnal.details && res.jurnal.details.length > 0) {
                    renderJurnalClosingModalContent(res.jurnal, res.spk, false);
                } else {
                    loadJurnalClosingPreviewFallback(spkId);
                }
            },
            error: function () {
                // If not closed yet (404), fallback to closing preview draft
                loadJurnalClosingPreviewFallback(spkId);
            }
        });
    }

    function loadJurnalClosingPreviewFallback(spkId) {
        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/closing-preview`,
            method: 'GET',
            success: function (res) {
                $('#jurnal_spk_pb_loading').addClass('d-none');

                if (!res.success || !res.items || res.items.length === 0) {
                    $('#jurnal_spk_pb_empty').removeClass('d-none');
                    return;
                }

                renderJurnalClosingPreviewContent(res);
            },
            error: function () {
                $('#jurnal_spk_pb_loading').addClass('d-none');
                $('#jurnal_spk_pb_empty').removeClass('d-none');
            }
        });
    }

    function renderJurnalClosingModalContent(jurnal, spkInfo, isPreview) {
        let grandTotalDebit = parseFloat(jurnal.total_debit || 0);
        let grandTotalKredit = parseFloat(jurnal.total_kredit || 0);

        let tableRowsHtml = '';
        let rowCounter = 0;

        (jurnal.details || []).forEach(function (d) {
            rowCounter++;
            const isDebit = parseFloat(d.debit || 0) > 0;
            const isKredit = parseFloat(d.kredit || 0) > 0;

            const badge = isDebit
                ? `<span class="badge rounded-1 fw-bold" style="background:rgba(16,185,129,.15);color:#059669;font-size:10px;">DEBIT (WIP)</span>`
                : `<span class="badge rounded-1 fw-bold" style="background:rgba(59,130,246,.15);color:#2563eb;font-size:10px;">KREDIT (PERSEDIAAN)</span>`;

            tableRowsHtml += `
                <tr class="border-bottom">
                    <td class="ps-3 text-center text-muted" style="font-size:11px;">${rowCounter}</td>
                    <td class="font-monospace fw-extrabold text-dark fs-6">${d.kode_akun || '-'}</td>
                    <td class="fw-bold text-theme-main fs-6">${d.nama_akun || '-'}</td>
                    <td class="text-muted" style="font-size: 11.5px;">${badge} <span class="ms-1 text-muted" style="font-size:11px;">(${d.keterangan || 'Konsolidasi Closing'})</span></td>
                    <td class="text-end font-monospace ${isDebit ? 'fw-extrabold text-success fs-6' : 'text-muted'}" style="font-size: 12.5px;">
                        ${isDebit ? 'Rp ' + parseFloat(d.debit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                    </td>
                    <td class="text-end pe-3 font-monospace ${isKredit ? 'fw-extrabold text-primary fs-6' : 'text-muted'}" style="font-size: 12.5px;">
                        ${isKredit ? 'Rp ' + parseFloat(d.kredit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                    </td>
                </tr>`;
        });

        const statusTag = `<span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-2xs" style="background: rgba(16,185,129,0.15); color: #059669; font-size: 11px;">
            JURNAL RESMI RESMI (#${jurnal.nomor_jurnal})
        </span>`;

        const consolidatedHtml = `
            <div class="rounded-3 border overflow-hidden shadow-sm" style="border-color: rgba(56,189,248,0.25) !important;">
                <div class="d-flex align-items-center justify-content-between px-3.5 py-3"
                     style="background: linear-gradient(90deg, rgba(56,189,248,0.12) 0%, rgba(56,189,248,0.04) 100%); border-bottom: 1px solid rgba(56,189,248,0.2);">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-journal-check text-cyan-500 fs-5" style="color: #0284c7;"></i>
                        <div>
                            <span class="fw-bold text-dark font-monospace d-block" style="font-size: 13.5px; letter-spacing: -0.2px;">JURNAL KONSOLIDASI CLOSING PENGELUARAN BAHAN</span>
                            <small class="text-muted" style="font-size: 11px;">Nomor Dokumen: ${jurnal.nomor_jurnal} | Tanggal: ${jurnal.tanggal_jurnal}</small>
                        </div>
                    </div>
                    ${statusTag}
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead style="background: rgba(248,250,252,0.95);">
                            <tr class="text-muted" style="font-size: 10.5px; font-weight: 700; text-transform: uppercase;">
                                <th class="ps-3 text-center" style="width: 40px;">#</th>
                                <th style="width: 120px;">KODE AKUN</th>
                                <th style="width: 280px;">NAMA AKUN COA</th>
                                <th>POSISI AKUN / DESKRIPSI</th>
                                <th class="text-end" style="width: 170px;">DEBIT (WIP)</th>
                                <th class="text-end pe-3" style="width: 170px;">KREDIT (PERSEDIAAN)</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            ${tableRowsHtml}
                        </tbody>
                        <tfoot style="background: rgba(56,189,248,.08); border-top: 2px solid rgba(56,189,248,.25);">
                            <tr>
                                <td colspan="4" class="ps-3 text-end fw-extrabold text-dark" style="font-size: 12.5px; letter-spacing: 0.2px;">TOTAL REKAPITULASI JURNAL AKUNTANSI SPK</td>
                                <td class="text-end fw-extrabold text-success font-monospace fs-6">Rp ${grandTotalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                <td class="pe-3 text-end fw-extrabold text-primary font-monospace fs-6">Rp ${grandTotalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;

        $('#jurnal_spk_pb_total_sesi').text('—');
        $('#jurnal_spk_pb_total_debit').text('Rp ' + grandTotalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#jurnal_spk_pb_total_kredit').text('Rp ' + grandTotalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#jurnal_spk_pb_sessions_container').html(consolidatedHtml);
        $('#jurnal_spk_pb_content').removeClass('d-none');
    }

    function renderJurnalClosingPreviewContent(res) {
        let totalVal = parseFloat(res.grand_total_nilai || 0);
        let items = res.items || [];

        let tableRowsHtml = '';
        let rowCounter = 0;

        items.forEach(function (item) {
            if (parseFloat(item.total_nilai || 0) <= 0) return;
            rowCounter++;

            tableRowsHtml += `
                <tr class="border-bottom">
                    <td class="ps-3 text-center text-muted" style="font-size:11px;">${rowCounter}</td>
                    <td class="font-monospace fw-bold text-dark fs-6">${item.kode_barang || '-'}</td>
                    <td class="fw-bold text-theme-main fs-6">${item.nama_barang || '-'}</td>
                    <td class="text-muted" style="font-size: 11.5px;">
                        <span class="badge rounded-1 fw-bold me-1" style="background:rgba(59,130,246,.15);color:#2563eb;font-size:10px;">KREDIT</span>
                        Total Keluaran: <strong class="font-monospace">${item.total_qty} ${item.satuan}</strong>
                    </td>
                    <td class="text-end font-monospace text-muted" style="font-size: 12.5px;">—</td>
                    <td class="text-end pe-3 font-monospace fw-extrabold text-primary fs-6" style="font-size: 12.5px;">
                        Rp ${parseFloat(item.total_nilai).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </td>
                </tr>`;
        });

        // Add WIP Debit Summary row
        tableRowsHtml = `
            <tr class="border-bottom" style="background: rgba(16,185,129,0.04);">
                <td class="ps-3 text-center text-muted" style="font-size:11px;">1</td>
                <td class="font-monospace fw-bold text-dark fs-6">WIP-AUTO</td>
                <td class="fw-bold text-success fs-6">Persediaan Barang Dalam Proses (WIP)</td>
                <td class="text-muted" style="font-size: 11.5px;">
                    <span class="badge rounded-1 fw-bold me-1" style="background:rgba(16,185,129,.15);color:#059669;font-size:10px;">DEBIT</span>
                    Akumulasi Persediaan Bahan Baku Keluar Produksi
                </td>
                <td class="text-end font-monospace fw-extrabold text-success fs-6" style="font-size: 12.5px;">
                    Rp ${totalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </td>
                <td class="text-end pe-3 font-monospace text-muted" style="font-size: 12.5px;">—</td>
            </tr>` + tableRowsHtml;

        const consolidatedHtml = `
            <div class="rounded-3 border overflow-hidden shadow-sm" style="border-color: rgba(56,189,248,0.25) !important;">
                <div class="d-flex align-items-center justify-content-between px-3.5 py-3"
                     style="background: linear-gradient(90deg, rgba(56,189,248,0.12) 0%, rgba(56,189,248,0.04) 100%); border-bottom: 1px solid rgba(56,189,248,0.2);">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-journal-text text-cyan-500 fs-5" style="color: #0284c7;"></i>
                        <div>
                            <span class="fw-bold text-dark font-monospace d-block" style="font-size: 13.5px; letter-spacing: -0.2px;">PRATINJAU JURNAL KONSOLIDASI (DRAFT / BELUM CLOSING)</span>
                            <small class="text-muted" style="font-size: 11px;">Jurnal konsolidasi ini akan diterbitkan secara resmi saat Otorisasi Closing Pengeluaran Bahan dilakukan.</small>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-2xs" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 11px;">
                        DRAFT KONSOLIDASI
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead style="background: rgba(248,250,252,0.95);">
                            <tr class="text-muted" style="font-size: 10.5px; font-weight: 700; text-transform: uppercase;">
                                <th class="ps-3 text-center" style="width: 40px;">#</th>
                                <th style="width: 120px;">KODE BARANG / AKUN</th>
                                <th style="width: 280px;">NAMA BARANG / AKUN</th>
                                <th>POSISI AKUN / DETAIL PENGELUARAN</th>
                                <th class="text-end" style="width: 170px;">DEBIT (WIP)</th>
                                <th class="text-end pe-3" style="width: 170px;">KREDIT (PERSEDIAAN)</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            ${tableRowsHtml}
                        </tbody>
                        <tfoot style="background: rgba(56,189,248,.08); border-top: 2px solid rgba(56,189,248,.25);">
                            <tr>
                                <td colspan="4" class="ps-3 text-end fw-extrabold text-dark" style="font-size: 12.5px; letter-spacing: 0.2px;">TOTAL REKAPITULASI ESTIMASI JURNAL HPP</td>
                                <td class="text-end fw-extrabold text-success font-monospace fs-6">Rp ${totalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                <td class="pe-3 text-end fw-extrabold text-primary font-monospace fs-6">Rp ${totalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;

        $('#jurnal_spk_pb_total_sesi').text('Draft');
        $('#jurnal_spk_pb_total_debit').text('Rp ' + totalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#jurnal_spk_pb_total_kredit').text('Rp ' + totalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#jurnal_spk_pb_sessions_container').html(consolidatedHtml);
        $('#jurnal_spk_pb_content').removeClass('d-none');
    }

    function keluarkanBahanSpk(spkId, nomorSpk, namaBarang, status) {
        openModalTambahPengeluaran();
        pilihSpkFromLookup(spkId, nomorSpk, namaBarang, status);
    }

    function reloadTablePengeluaran() {
        if (dtPengeluaran) dtPengeluaran.ajax.reload(null, false);
    }

    function resetFilterPengeluaran() {
        $('#filter_gudang').val('all');
        $('#filter_start_date').val('');
        $('#filter_end_date').val('');
        reloadTablePengeluaran();
    }

    function deletePengeluaran(id) {
        Swal.fire({
            title: 'Batalkan & Hapus Transaksi?',
            text: "Stok bahan baku di gudang akan dikembalikan (revert) dan catatan akumulasi SPK akan diperbarui!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dibatalkan!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTablePengeluaran();
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    }

    // Multi-Modal Stacking & Backdrop Layer Manager
    $(document).on('show.bs.modal', '#modalTambahPengeluaran', function () {
        setTimeout(function() {
            if ($('#modalDetailPengeluaran').hasClass('show')) {
                $('.modal-backdrop:last-child').css('z-index', '1065');
            }
        }, 10);
    });

    $(document).on('show.bs.modal', '#modalDetailJurnalPengeluaran', function () {
        setTimeout(function() {
            if ($('#modalDetailPengeluaran').hasClass('show')) {
                $('.modal-backdrop:last-child').css('z-index', '1072');
            }
        }, 10);
    });

    $(document).on('show.bs.modal', '#modalLookupSpk, #modalLookupBahanSpk, #modalLookupGudang, #modalLookupKaryawan', function () {
        setTimeout(function() {
            $('.modal-backdrop:last-child').css('z-index', '1085');
        }, 10);
    });

    // ==========================================
    // CLOSING & REOPEN PENGELUARAN BAHAN SPK
    // ==========================================

    // ==========================================
    // CLOSING WORKFLOW PENGELUARAN BAHAN SPK
    // ==========================================

    $(document).on('change', '#checkConfirmEarlyClosing', function() {
        const isChecked = this.checked;
        const mode = $('#closing_modal_mode').val();
        const targetBtn = (mode === 'ajukan') ? $('#btnSubmitAjukanClosingBahan') : $('#btnSubmitClosingBahan');
        targetBtn.prop('disabled', !isChecked);
        if (isChecked) {
            targetBtn.css('cursor', 'pointer').removeClass('opacity-50');
        } else {
            targetBtn.css('cursor', 'not-allowed').addClass('opacity-50');
        }
    });

    /**
     * Buka Modal dalam Mode Pengajuan Closing (Oleh Tim Produksi / Gudang)
     */
    function bukaModalAjukanClosing(spkId, nomorSpk) {
        setupClosingModalUI('ajukan', spkId, nomorSpk);
    }

    /**
     * Buka Modal dalam Mode Review & Setujui Closing (Oleh Accounting / Otorisator)
     */
    function bukaModalReviewClosing(spkId, nomorSpk) {
        setupClosingModalUI('review', spkId, nomorSpk);
    }

    /**
     * Generic Modal Setup & Preview Loader
     */
    function setupClosingModalUI(mode, spkId, nomorSpk) {
        $('#closing_spk_id').val(spkId);
        $('#closing_modal_mode').val(mode);
        $('#modalClosingSpkSubtitle').text('SPK: #' + nomorSpk);
        $('#closing_tanggal').val(new Date().toISOString().split('T')[0]);
        $('#closing_catatan').val('');
        $('#closing_ajukan_catatan').val('');
        $('#closing_warning_container').empty();
        $('#closing_status_banner').addClass('d-none').empty();
        $('#closing_preview_loading').removeClass('d-none');
        $('#closing_preview_content').addClass('d-none');

        // Configure Header & Buttons based on Mode
        if (mode === 'ajukan') {
            $('#modalClosingHeader').css('background', 'linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%) !important');
            $('#modalClosingHeaderIcon').attr('class', 'bi bi-send-check-fill fs-4 text-white');
            $('#modalConfirmClosingBahanTitle').text('Ajukan Closing Pengeluaran Bahan Baku SPK');
            $('#modalClosingBadgeMode').text('PENGAJUAN CLOSING').css('background', 'rgba(255,255,255,0.25)');
            
            $('#container_input_ajukan_closing').removeClass('d-none');
            $('#container_input_review_closing').addClass('d-none');

            $('#btnSubmitAjukanClosingBahan').removeClass('d-none').prop('disabled', true).addClass('opacity-50');
            $('#btnSubmitClosingBahan').addClass('d-none');
            $('#btnTolakClosingBahan').addClass('d-none');
        } else {
            // Mode Review & Approval
            $('#modalClosingHeader').css('background', 'linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%) !important');
            $('#modalClosingHeaderIcon').attr('class', 'bi bi-shield-lock-fill fs-4 text-white');
            $('#modalConfirmClosingBahanTitle').text('Review & Persetujuan Closing Pengeluaran Bahan SPK');
            $('#modalClosingBadgeMode').text('OTORISASI & JURNAL').css('background', 'rgba(255,255,255,0.25)');
            
            $('#container_input_ajukan_closing').addClass('d-none');
            $('#container_input_review_closing').removeClass('d-none');

            $('#btnSubmitAjukanClosingBahan').addClass('d-none');
            $('#btnSubmitClosingBahan').removeClass('d-none').prop('disabled', true).addClass('opacity-50');
            $('#btnTolakClosingBahan').removeClass('d-none');
        }

        $('#modalConfirmClosingBahan').modal('show');

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/closing-preview`,
            method: 'GET',
            success: function(res) {
                if (!res.success) {
                    Swal.fire('Perhatian', res.message || 'Gagal memuat data closing.', 'warning');
                    $('#modalConfirmClosingBahan').modal('hide');
                    return;
                }

                const canLihatHpp = (res.user_permissions && res.user_permissions.can_lihat_hpp) ? true : false;

                $('#modalClosingSpkSubtitle').text(`SPK: #${res.spk.nomor_spk} (${res.spk.barang_jadi} - ${res.spk.qty_spk.toLocaleString('id-ID')} ${res.spk.satuan})`);
                $('#closing_badge_total_sesi').text(res.spk.total_sesi + ' Sesi');
                $('#closing_badge_total_item').text(res.items.length + ' Bahan');
                
                if (canLihatHpp) {
                    $('#closing_kpi_col_nilai').removeClass('d-none');
                    $('#closing_kpi_col_sesi, #closing_kpi_col_item').removeClass('col-6').addClass('col-4');
                    $('.col-closing-hpp').removeClass('d-none');
                    $('#closing_badge_total_nilai').text('Rp ' + Number(res.grand_total_nilai || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#closing_tfoot_nilai').text('Rp ' + Number(res.grand_total_nilai || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#closing_tfoot_label').html('<i class="bi bi-calculator text-success me-1"></i>Total Estimasi Nilai HPP');
                } else {
                    $('#closing_kpi_col_nilai').addClass('d-none');
                    $('#closing_kpi_col_sesi, #closing_kpi_col_item').removeClass('col-4').addClass('col-6');
                    $('.col-closing-hpp').addClass('d-none');
                    $('#closing_tfoot_label').html('<i class="bi bi-check-all text-primary me-1"></i>Status Kelengkapan Bahan');
                }

                $('#closing_table_item_count').text(res.items.length + ' Bahan');

                // Status Banner: Jika sedang Menunggu Closing atau pernah Ditolak
                let bannerHtml = '';
                if (res.spk.material_closing_status === 'Menunggu Closing') {
                    bannerHtml = `
                        <div class="closing-alert-card closing-alert-warning mb-0 shadow-2xs">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="background: rgba(245, 158, 11, 0.2); width: 38px; height: 38px;">
                                    <i class="bi bi-hourglass-split text-warning fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">Pengajuan Closing Sedang Menunggu Review Accounting</div>
                                    <div class="spk-text-muted small mt-0.5" style="font-size: 11.5px;">
                                        Diajukan oleh <strong class="spk-text-main">${res.spk.requested_by_name}</strong> pada ${res.spk.requested_at || '-'}
                                        ${res.spk.requested_note ? `<br><i class="bi bi-chat-left-quote me-1 text-warning"></i>Catatan: <em>"${res.spk.requested_note}"</em>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#closing_status_banner').html(bannerHtml).removeClass('d-none');
                } else if (res.spk.material_closing_status === 'Ditolak' && res.spk.reject_reason) {
                    bannerHtml = `
                        <div class="closing-alert-card closing-alert-danger mb-0 shadow-2xs">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="background: rgba(239, 68, 68, 0.2); width: 38px; height: 38px;">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-danger" style="font-size: 13px;">Pengajuan Closing Sebelumnya Ditolak</div>
                                    <div class="spk-text-main small mt-0.5" style="font-size: 11.5px;">
                                        Ditolak oleh <strong class="text-danger">${res.spk.rejected_by_name}</strong> (${res.spk.rejected_at || '-'})<br>
                                        <i class="bi bi-chat-left-text me-1 text-danger"></i>Alasan Penolakan: <strong class="text-danger">"${res.spk.reject_reason}"</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#closing_status_banner').html(bannerHtml).removeClass('d-none');
                }

                // Render Warning Alert if unfulfilled, or Success Alert if complete
                const status = res.completion_status;
                let warningHtml = '';

                if (status && !status.is_complete) {
                    let incompleteRows = '';
                    (status.incomplete_list || []).forEach(function(inc, idx) {
                        incompleteRows += `
                            <tr>
                                <td class="ps-3 py-1.5">
                                    <div class="fw-bold spk-text-main" style="font-size: 12px;">${idx + 1}. ${inc.nama_barang}</div>
                                    <div class="spk-text-muted font-monospace" style="font-size: 10.5px;">${inc.kode_barang}</div>
                                </td>
                                <td class="text-center py-1.5 font-monospace text-secondary">
                                    ${Number(inc.kebutuhan).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} <span class="small spk-text-muted">${inc.satuan}</span>
                                </td>
                                <td class="text-center py-1.5 font-monospace fw-semibold ${inc.keluar > 0 ? 'text-primary' : 'text-danger'}">
                                    ${Number(inc.keluar).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} <span class="small spk-text-muted">${inc.satuan}</span>
                                </td>
                                <td class="text-end pe-3 py-1.5">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 11px;">
                                        Kurang ${Number(inc.sisa).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} ${inc.satuan}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });

                    warningHtml = `
                        <div class="closing-alert-card closing-alert-warning mb-0 shadow-2xs">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="background: rgba(245, 158, 11, 0.2); width: 36px; height: 36px;">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0.5" style="font-size: 13px;">
                                            Peringatan: Pengeluaran Bahan Baku SPK Belum Selesai (100%)
                                        </h6>
                                        <div class="small spk-text-muted" style="font-size: 11.5px;">
                                            Masih terdapat <strong class="spk-text-main">${status.incomplete_count} dari ${status.total_materials} item bahan</strong> yang belum mencapai target SPK.
                                        </div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-2xs"
                                      style="background: rgba(245, 158, 11, 0.18); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 11px;">
                                    Progress: ${status.completed_count} / ${status.total_materials} Bahan Selesai (${status.avg_progress}%)
                                </span>
                            </div>

                            <div class="table-responsive early-table-wrapper mb-2.5 shadow-2xs" style="max-height: 140px; overflow-y: auto;">
                                <table class="table table-sm align-middle early-table closing-custom-table mb-0" style="font-size: 11.5px;">
                                    <thead style="position: sticky; top: 0; z-index: 1;">
                                        <tr class="fw-bold">
                                            <th class="ps-3 py-1.5">Bahan Baku Belum Selesai</th>
                                            <th class="text-center py-1.5">Target SPK</th>
                                            <th class="text-center py-1.5">Sudah Keluar</th>
                                            <th class="text-end pe-3 py-1.5">Sisa Kekurangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${incompleteRows}
                                    </tbody>
                                </table>
                            </div>

                            <div class="early-closing-confirm-box d-flex align-items-start gap-2.5 shadow-2xs">
                                <input class="form-check-input border-warning mt-0.5 flex-shrink-0" type="checkbox" id="checkConfirmEarlyClosing" style="cursor: pointer; width: 1.25rem; height: 1.25rem;">
                                <label class="form-check-label fw-bold small mb-0" for="checkConfirmEarlyClosing" style="cursor: pointer; font-size: 11.5px; line-height: 1.45;">
                                    Saya memahami konsekuensi penutupan (<span class="text-danger fw-semibold">SPK dikunci permanen, sisa bahan tidak dapat dikeluarkan lagi, dan jurnal hanya mencatat pengeluaran riil</span>) dan tetap setuju melakukan Early Closing.
                                </label>
                            </div>
                        </div>
                    `;

                    $('#closing_tfoot_status').html(`<span class="badge bg-warning text-dark font-monospace">${status.completed_count}/${status.total_materials} Selesai (${status.avg_progress}%)</span>`);
                    $('#closing_warning_container').html(warningHtml);
                    
                    if (mode === 'ajukan') {
                        $('#btnSubmitAjukanClosingBahan').prop('disabled', true).addClass('opacity-50');
                    } else {
                        $('#btnSubmitClosingBahan').prop('disabled', true).addClass('opacity-50');
                    }
                } else {
                    warningHtml = `
                        <div class="closing-alert-card closing-alert-success mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-2xs">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-xs"
                                     style="background: rgba(16, 185, 129, 0.2); width: 38px; height: 38px;">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0.5" style="font-size: 13.5px;">
                                        Bahan Baku Lengkap (100% Selesai)
                                    </h6>
                                    <div class="small spk-text-muted" style="font-size: 11.5px;">
                                        Seluruh kebutuhan bahan baku untuk SPK ini (${status ? status.total_materials : res.items.length} item) telah berhasil dikeluarkan dari gudang.
                                    </div>
                                </div>
                            </div>
                            <span class="badge rounded-pill bg-success text-white px-3 py-1.5 font-monospace fw-bold shadow-2xs" style="font-size: 11px;">
                                <i class="bi bi-shield-check me-1"></i>SIAP CLOSING
                            </span>
                        </div>
                    `;

                    $('#closing_tfoot_status').html('<span class="badge bg-success text-white font-monospace"><i class="bi bi-check-circle me-1"></i>100% Lengkap</span>');
                    $('#closing_warning_container').html(warningHtml);
                    
                    if (mode === 'ajukan') {
                        $('#btnSubmitAjukanClosingBahan').prop('disabled', false).removeClass('opacity-50');
                    } else {
                        $('#btnSubmitClosingBahan').prop('disabled', false).removeClass('opacity-50');
                    }
                }

                let rowsHtml = '';
                if (res.items.length === 0) {
                    const colspan = canLihatHpp ? 5 : 4;
                    rowsHtml = `<tr><td colspan="${colspan}" class="text-center text-muted py-3">Tidak ada data bahan dikeluarkan</td></tr>`;
                } else {
                    res.items.forEach(function(it) {
                        const targetQty = Number(it.target_kebutuhan || 0);
                        const totalKeluar = Number(it.total_qty || 0);
                        const sisaTarget = Number(it.sisa_target || 0);
                        const isLengkap = it.is_lengkap;

                        let statusBadge = '';
                        if (isLengkap) {
                            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;"><i class="bi bi-check2-circle me-1"></i>Lengkap (100%)</span>';
                        } else if (totalKeluar <= 0) {
                            statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;"><i class="bi bi-x-circle me-1"></i>Belum Keluar</span>';
                        } else {
                            statusBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;"><i class="bi bi-exclamation-circle me-1"></i>Kurang ${sisaTarget.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} ${it.satuan}</span>`;
                        }

                        const hppCell = canLihatHpp 
                            ? `<td class="text-end font-monospace pe-3 col-closing-hpp">${totalKeluar > 0 ? `<span class="text-success fw-bold">Rp ${Number(it.total_nilai || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>` : '<span class="text-muted">Rp 0,00</span>'}</td>`
                            : `<td class="text-end font-monospace pe-3 col-closing-hpp d-none">-</td>`;

                        rowsHtml += `
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold spk-text-main" style="font-size: 12px;">${it.nama_barang}</div>
                                    <div class="small spk-text-muted font-monospace" style="font-size: 10.5px;">${it.kode_barang}</div>
                                </td>
                                <td class="text-center font-monospace text-secondary">
                                    <span class="fw-semibold">${targetQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}</span>
                                    <span class="text-muted small" style="font-size: 10.5px;">${it.satuan}</span>
                                </td>
                                <td class="text-center font-monospace ${totalKeluar > 0 ? 'text-primary fw-bold' : 'text-danger fw-semibold'}">
                                    ${totalKeluar.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
                                    <span class="text-muted small" style="font-size: 10.5px;">${it.satuan}</span>
                                </td>
                                <td class="text-center">
                                    ${statusBadge}
                                </td>
                                ${hppCell}
                            </tr>
                        `;
                    });
                }
                $('#closing_table_tbody').html(rowsHtml);

                $('#closing_preview_loading').addClass('d-none');
                $('#closing_preview_content').removeClass('d-none');
            },
            error: function(err) {
                $('#modalConfirmClosingBahan').modal('hide');
                Swal.fire('Error', err.responseJSON?.message || 'Gagal memuat pratinjau closing.', 'error');
            }
        });
    }

    /**
     * Submit Permintaan Closing (Ajukan Closing) oleh Tim Produksi
     */
    function submitAjukanClosingBahan() {
        const spkId = $('#closing_spk_id').val();
        const catatan = $('#closing_ajukan_catatan').val();

        if ($('#checkConfirmEarlyClosing').length > 0 && !$('#checkConfirmEarlyClosing').is(':checked')) {
            Swal.fire({
                target: document.getElementById('modalConfirmClosingBahan') || document.body,
                icon: 'warning',
                title: 'Konfirmasi Diperlukan',
                text: 'Harap beri centang pada persetujuan Early Closing sebelum mengajukan!'
            });
            return;
        }

        const btn = $('#btnSubmitAjukanClosingBahan');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim Pengajuan...');

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/request-closing`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                catatan: catatan
            },
            success: function(res) {
                $('#modalConfirmClosingBahan').modal('hide');
                btn.prop('disabled', false).html('<i class="bi bi-send-check"></i><span>Kirim Pengajuan Closing ke Accounting</span>');
                Swal.fire({
                    icon: 'success',
                    title: 'Pengajuan Berhasil!',
                    text: res.message,
                    confirmButtonText: 'OK'
                });
                reloadTablePengeluaran();
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="bi bi-send-check"></i><span>Kirim Pengajuan Closing ke Accounting</span>');
                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan saat mengajukan closing.', 'error');
            }
        });
    }

    /**
     * Batalkan Pengajuan Closing oleh Pembuat / Admin
     */
    function batalAjukanClosing(spkId, nomorSpk) {
        Swal.fire({
            title: 'Batalkan Pengajuan Closing?',
            html: `Apakah Anda yakin ingin membatalkan pengajuan closing untuk SPK <b>#${nomorSpk}</b>?<br><span class="text-muted small">Status akan kembali terbuka (Open) dan operasional pengeluaran bahan dapat dilanjutkan.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Ya, Batalkan Pengajuan!',
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/spk/${spkId}/cancel-closing-request`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dibatalkan!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTablePengeluaran();
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan saat membatalkan pengajuan.', 'error');
                    }
                });
            }
        });
    }

    /**
     * Tolak Pengajuan Closing oleh Accounting / Approver
     */
    function rejectClosingBahan() {
        const spkId = $('#closing_spk_id').val();

        Swal.fire({
            target: document.getElementById('modalConfirmClosingBahan') || document.body,
            title: 'Tolak Pengajuan Closing?',
            text: 'Masukkan alasan penolakan agar tim produksi dapat melakukan penyesuaian bahan:',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan penolakan di sini (wajib)...',
            inputAttributes: {
                'aria-label': 'Alasan penolakan',
                'rows': 4
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tolak Pengajuan',
            cancelButtonText: 'Batal',
            didOpen: () => {
                setTimeout(() => {
                    const input = Swal.getInput();
                    if (input) input.focus();
                }, 150);
            },
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Alasan penolakan wajib diisi!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/spk/${spkId}/reject-closing`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        alasan_reject: result.value
                    },
                    success: function(res) {
                        $('#modalConfirmClosingBahan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Ditolak!',
                            text: res.message,
                            confirmButtonText: 'OK'
                        });
                        reloadTablePengeluaran();
                    },
                    error: function(err) {
                        Swal.fire({
                            target: document.getElementById('modalConfirmClosingBahan') || document.body,
                            icon: 'error',
                            title: 'Gagal!',
                            text: err.responseJSON?.message || 'Terjadi kesalahan saat menolak pengajuan.'
                        });
                    }
                });
            }
        });
    }

    /**
     * Setujui Closing & Terbitkan Jurnal Akuntansi oleh Accounting / Approver
     */
    function submitClosingBahan() {
        const spkId = $('#closing_spk_id').val();
        const tanggalClosing = $('#closing_tanggal').val();
        const catatan = $('#closing_catatan').val();

        if (!tanggalClosing) {
            Swal.fire({
                target: document.getElementById('modalConfirmClosingBahan') || document.body,
                icon: 'warning',
                title: 'Validasi',
                text: 'Tanggal closing jurnal wajib diisi!'
            });
            return;
        }

        if ($('#checkConfirmEarlyClosing').length > 0 && !$('#checkConfirmEarlyClosing').is(':checked')) {
            Swal.fire({
                target: document.getElementById('modalConfirmClosingBahan') || document.body,
                icon: 'warning',
                title: 'Konfirmasi Diperlukan',
                text: 'Harap beri centang pada persetujuan Early Closing sebelum melanjutkan!'
            });
            return;
        }

        const btn = $('#btnSubmitClosingBahan');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan & Menerbitkan Jurnal...');

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/close`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tanggal_closing: tanggalClosing,
                catatan: catatan
            },
            success: function(res) {
                $('#modalConfirmClosingBahan').modal('hide');
                btn.prop('disabled', false).html('<i class="bi bi-lock-fill"></i><span>Setujui & Terbitkan Jurnal</span>');
                Swal.fire({
                    icon: 'success',
                    title: 'Closing Berhasil Disetujui!',
                    text: res.message,
                    confirmButtonText: 'OK'
                });
                reloadTablePengeluaran();
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="bi bi-lock-fill"></i><span>Setujui & Terbitkan Jurnal</span>');
                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan saat memproses closing.', 'error');
            }
        });
    }

    function showSpkJurnalClosingModal(spkId, nomorSpk) {
        $('#modalJurnalClosingSubtitle').text('SPK: #' + nomorSpk);
        $('#jurnal_closing_loading').removeClass('d-none');
        $('#jurnal_closing_content').addClass('d-none');

        $('#modalDetailJurnalClosing').modal('show');

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/jurnal-closing`,
            method: 'GET',
            success: function(res) {
                if (!res.success) {
                    $('#modalDetailJurnalClosing').modal('hide');
                    Swal.fire('Informasi', res.message || 'Jurnal belum ditemukan.', 'info');
                    return;
                }

                const j = res.jurnal;
                const spk = res.spk;

                $('#modalJurnalClosingSubtitle').text(`SPK: #${spk.nomor_spk} (${spk.barang_jadi})`);
                $('#jurnal_closing_nomor').text(j.nomor_jurnal);
                $('#jurnal_closing_tanggal').text(j.tanggal_jurnal);
                $('#jurnal_closing_user').text(`Ditutup oleh ${spk.closed_by} (${spk.closed_at})`);
                $('#jurnal_closing_keterangan').text(j.keterangan || '-');

                $('#jurnal_closing_total_debit').text('Rp ' + Number(j.total_debit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#jurnal_closing_total_kredit').text('Rp ' + Number(j.total_kredit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                let rowsHtml = '';
                j.details.forEach(function(d) {
                    rowsHtml += `
                        <tr>
                            <td class="font-monospace fw-bold text-primary">${d.kode_akun}</td>
                            <td>
                                <div class="fw-semibold text-dark">${d.nama_akun}</div>
                                <div class="text-muted small">${d.keterangan || '-'}</div>
                            </td>
                            <td class="text-end font-monospace fw-bold ${d.debit > 0 ? 'text-success' : 'text-muted'}">
                                ${d.debit > 0 ? 'Rp ' + Number(d.debit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
                            </td>
                            <td class="text-end font-monospace fw-bold ${d.kredit > 0 ? 'text-primary' : 'text-muted'}">
                                ${d.kredit > 0 ? 'Rp ' + Number(d.kredit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
                            </td>
                        </tr>
                    `;
                });
                $('#jurnal_closing_tbody').html(rowsHtml);

                $('#jurnal_closing_loading').addClass('d-none');
                $('#jurnal_closing_content').removeClass('d-none');
            },
            error: function(err) {
                $('#modalDetailJurnalClosing').modal('hide');
                Swal.fire('Error', err.responseJSON?.message || 'Gagal memuat rincian jurnal closing.', 'error');
            }
        });
    }

    function konfirmasiReopenBahan(spkId, nomorSpk) {
        Swal.fire({
            title: 'Buka Kembali Closing Bahan?',
            html: `Apakah Anda yakin ingin membuka closing bahan untuk SPK <b>#${nomorSpk}</b>?<br><br><span class="text-danger small">Jurnal akuntansi konsolidasi terkait akan ditarik dari Jurnal Umum & pengeluaran bahan dapat diinput/diedit kembali.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-unlock-fill me-1"></i> Ya, Buka Closing!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/spk/${spkId}/reopen`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dibuka Kembali!',
                            text: res.message,
                            timer: 2500,
                            showConfirmButton: false
                        });
                        reloadTablePengeluaran();
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan saat membuka closing.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
</x-layouts>
