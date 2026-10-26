{{-- MODAL DETAIL & RIWAYAT HASIL PRODUKSI SPK (PENERIMAAN BARANG JADI) - SPACIOUS EXECUTIVE DESIGN --}}
<div class="modal fade" id="modalDetailBarangJadi" tabindex="-1" aria-labelledby="modalDetailPBJTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable pbj-detail-dialog">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-detail-container">
            
            {{-- CINEMATIC HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative pbj-detail-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center pbj-detail-header-icon">
                        <i class="bi bi-clock-history fs-5 text-emerald-light"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6 fs-md-5" id="pbj_det_spk_title">
                                Riwayat Hasil Produksi SPK
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 pbj-header-badge">REALISASI FG</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;">Rincian rekapitulasi & riwayat sesi penyerahan barang jadi ke gudang</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-3 p-md-4">
                
                {{-- 1. SPACIOUS HEADER METRICS HERO CARD --}}
                <div class="pbj-hero-card p-3.5 p-md-4 mb-4 rounded-4">
                    {{-- ROW 1: 5 DISTINCT METRIC TILES --}}
                    <div class="row g-2.5 g-md-3 mb-3">
                        {{-- TILE 1: SPK & PRODUK --}}
                        <div class="col-12 col-lg-4">
                            <div class="pbj-hero-tile h-100">
                                <span class="pbj-hero-tile-sub">DOKUMEN SPK & PRODUK JADI</span>
                                <div class="fw-bold fs-6 text-primary font-monospace mb-1" id="pbj_det_spk_nomor">-</div>
                                <div class="fw-semibold text-truncate spk-text-main" id="pbj_det_barang_jadi" title="" style="font-size: 13.5px;">-</div>
                            </div>
                        </div>

                        {{-- TILE 2: TARGET QTY --}}
                        <div class="col-6 col-sm-6 col-lg-2">
                            <div class="pbj-hero-tile h-100">
                                <span class="pbj-hero-tile-sub">TARGET PRODUKSI</span>
                                <div class="fw-bold fs-5 spk-text-main font-monospace mt-1" id="pbj_det_target_qty">-</div>
                            </div>
                        </div>

                        {{-- TILE 3: TOTAL HASIL BAIK --}}
                        <div class="col-6 col-sm-6 col-lg-2">
                            <div class="pbj-hero-tile h-100 pbj-tile-good">
                                <span class="pbj-hero-tile-sub text-emerald">TOTAL HASIL BAIK</span>
                                <div class="fw-bold fs-5 text-emerald font-monospace mt-1" id="pbj_det_hasil_baik">-</div>
                            </div>
                        </div>

                        {{-- TILE 4: TOTAL REJECT --}}
                        <div class="col-6 col-sm-6 col-lg-2">
                            <div class="pbj-hero-tile h-100 pbj-tile-reject">
                                <span class="pbj-hero-tile-sub text-danger">TOTAL REJECT (BS)</span>
                                <div class="fw-bold fs-5 text-danger font-monospace mt-1" id="pbj_det_hasil_reject">-</div>
                            </div>
                        </div>

                        {{-- TILE 5: FREKUENSI SESI --}}
                        <div class="col-6 col-sm-6 col-lg-2">
                            <div class="pbj-hero-tile h-100">
                                <span class="pbj-hero-tile-sub text-info">FREKUENSI SESI</span>
                                <div class="fw-bold fs-5 text-info font-monospace mt-1" id="pbj_det_total_sesi">0 Sesi</div>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: OVERALL TARGET PROGRESS BAR (SPACIOUS & CRISP) --}}
                    <div class="p-2.5 px-3 rounded-3 pbj-hero-progress-box">
                        <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 11.5px;">
                            <span class="spk-text-muted fw-bold d-flex align-items-center gap-1.5">
                                <i class="bi bi-graph-up-arrow text-emerald"></i>
                                <span>Progres Kelengkapan Target Hasil</span>
                            </span>
                            <span id="pbj_det_overall_percent" class="badge bg-emerald-vibrant rounded-pill px-3 py-1 fw-bold font-monospace" style="font-size: 12px;">0%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 99px; background: rgba(128,128,128,0.18);">
                            <div id="pbj_det_overall_progressbar" class="progress-bar rounded-pill bg-emerald-vibrant" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. NAV TABS (PILL STYLE) --}}
                <ul class="nav nav-pills pbj-pill-tabs mb-3.5 gap-2" id="pbjDetailTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold rounded-pill px-4 py-2" id="pbj-tab-rekap-tab" data-bs-toggle="tab" data-bs-target="#pbj-tab-rekap" type="button" role="tab">
                            <i class="bi bi-pie-chart-fill me-2"></i>Rekapitulasi Hasil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold rounded-pill px-4 py-2" id="pbj-tab-riwayat-tab" data-bs-toggle="tab" data-bs-target="#pbj-tab-riwayat" type="button" role="tab">
                            <i class="bi bi-layers-fill me-2"></i>Riwayat Sesi Penyerahan (<span id="pbj_badge_count_sesi_tab">0</span>)
                        </button>
                    </li>
                </ul>

                {{-- 3. TAB CONTENT --}}
                <div class="tab-content" id="pbjDetailTabContent">
                    {{-- TAB 1: REKAPITULASI HASIL --}}
                    <div class="tab-pane fade show active" id="pbj-tab-rekap" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="p-4 p-md-5 rounded-4 text-center pbj-rekap-box pbj-rekap-good h-100">
                                    <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 pbj-icon-circle-good">
                                        <i class="bi bi-check-circle-fill fs-1 text-emerald"></i>
                                    </div>
                                    <h6 class="small fw-bold text-emerald text-uppercase mb-1" style="letter-spacing: 0.5px;">TOTAL KUANTITAS BAIK (GOOD OUTPUT)</h6>
                                    <h1 class="fw-extrabold text-emerald font-monospace mb-2" id="pbj_rekap_qty_baik" style="font-size: 2.2rem;">0</h1>
                                    <p class="text-muted small m-0">Kuantitas fisik yang telah diterima di gudang dan tercatat siap jual / dipakai.</p>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="p-4 p-md-5 rounded-4 text-center pbj-rekap-box pbj-rekap-reject h-100">
                                    <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 pbj-icon-circle-reject">
                                        <i class="bi bi-x-circle-fill fs-1 text-danger"></i>
                                    </div>
                                    <h6 class="small fw-bold text-danger text-uppercase mb-1" style="letter-spacing: 0.5px;">TOTAL KUANTITAS REJECT (BS / AFVAL)</h6>
                                    <h1 class="fw-extrabold text-danger font-monospace mb-2" id="pbj_rekap_qty_reject" style="font-size: 2.2rem;">0</h1>
                                    <p class="text-muted small m-0">Kuantitas cacat produksi / BS yang dicatat pada dokumen SPK.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: TIMELINE SESI RIWAYAT (SPACIOUS & EXPANSIVE CARDS) --}}
                    <div class="tab-pane fade" id="pbj-tab-riwayat" role="tabpanel">
                        <div id="container_pbj_riwayat_sessions" class="d-flex flex-column gap-3.5">
                            {{-- AJAX Load Sessions --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- CINEMATIC FOOTER --}}
            <div class="modal-footer pbj-modal-footer px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2" id="pbj_detail_action_wrapper">
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('buka-closing-penerimaan-barang-jadi'))
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3.5 py-1.5 fw-bold d-none align-items-center gap-1.5 shadow-2xs" id="btn_reopen_from_detail" onclick="reopenClosingFgFromDetail()">
                        <i class="bi bi-unlock-fill"></i>
                        <span>Buka Kembali Closing (Reopen)</span>
                    </button>
                    @endif
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('closing-penerimaan-barang-jadi') || auth()->user()->can('sesuaikan-hpp-penerimaan-barang-jadi'))
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-3.5 py-1.5 fw-bold d-none align-items-center gap-1.5 text-dark shadow-2xs" id="btn_closing_hpp_from_detail" onclick="closingHppFgFromDetail()">
                        <i class="bi bi-calculator-fill"></i>
                        <span>Hitung & Finalisasi HPP</span>
                    </button>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 py-2 fw-semibold ms-auto" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL JURNAL AKUNTANSI PENERIMAAN BARANG JADI --}}
<div class="modal fade" id="modalDetailJurnalPbj" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: var(--spk-card-bg, #ffffff);">
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); width: 38px; height: 38px;">
                        <i class="bi bi-journal-bookmark-fill fs-5 text-emerald-light"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0 text-white fs-6" id="mjurnal_pbj_title">Jurnal Akuntansi</h5>
                        <small class="text-white-50" style="font-size: 11.5px;" id="mjurnal_pbj_subtitle">Ref Transaksi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="table-responsive rounded-3 border">
                    <table class="table align-middle spk-table-main mb-0 w-100">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="ps-3 text-center" style="width: 50px;">No</th>
                                <th style="min-width: 200px;">Kode & Nama Akun (COA)</th>
                                <th>Keterangan</th>
                                <th class="text-end" style="width: 150px;">Debit</th>
                                <th class="text-end pe-3" style="width: 150px;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_detail_jurnal_pbj">
                            {{-- AJAX Load Jurnal --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-2.5">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* MODAL CONTAINER & SIZING */
    .pbj-detail-dialog {
        max-width: 1180px !important;
    }
    @media (max-width: 992px) {
        .pbj-detail-dialog {
            max-width: calc(100% - 1.5rem) !important;
            margin: 0.75rem auto !important;
        }
    }

    .pbj-detail-container {
        background-color: var(--spk-card-bg, #ffffff) !important;
        border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    }

    .pbj-detail-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%);
    }

    .pbj-detail-header-icon {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        width: 44px;
        height: 44px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* HERO CARD & METRICS */
    .pbj-hero-card {
        background: rgba(248, 250, 252, 0.85);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }
    html[data-bs-theme="dark"] .pbj-hero-card,
    .dark-mode .pbj-hero-card {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .pbj-hero-tile {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    html[data-bs-theme="dark"] .pbj-hero-tile,
    .dark-mode .pbj-hero-tile {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .pbj-hero-tile-sub {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: var(--spk-text-muted, #64748b);
        display: block;
        margin-bottom: 2px;
    }

    .pbj-hero-progress-box {
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-bs-theme="dark"] .pbj-hero-progress-box,
    .dark-mode .pbj-hero-progress-box {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* REKAPITULASI BOXES */
    .pbj-rekap-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .pbj-rekap-good {
        border-top: 4px solid #10b981 !important;
    }
    .pbj-rekap-reject {
        border-top: 4px solid #ef4444 !important;
    }
    html[data-bs-theme="dark"] .pbj-rekap-box,
    .dark-mode .pbj-rekap-box {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .pbj-icon-circle-good {
        background: rgba(16, 185, 129, 0.12);
        width: 70px;
        height: 70px;
    }
    .pbj-icon-circle-reject {
        background: rgba(239, 68, 68, 0.12);
        width: 70px;
        height: 70px;
    }

    /* SESSION CARDS - SPACIOUS & EXECUTIVE */
    .pbj-sess-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.4rem;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .pbj-sess-card:hover {
        border-color: rgba(16, 185, 129, 0.4) !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }
    html[data-bs-theme="dark"] .pbj-sess-card,
    .dark-mode .pbj-sess-card {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: none;
    }
    html[data-bs-theme="dark"] .pbj-sess-card:hover,
    .dark-mode .pbj-sess-card:hover {
        border-color: rgba(16, 185, 129, 0.35) !important;
        background: rgba(15, 23, 42, 0.85);
    }

    .pbj-sess-metric {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
    }
    html[data-bs-theme="dark"] .pbj-sess-metric,
    .dark-mode .pbj-sess-metric {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* TABS */
    .pbj-pill-tabs .nav-link {
        background: rgba(148, 163, 184, 0.1);
        color: var(--spk-contrast-color, #64748b);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.2s ease;
    }
    html[data-bs-theme="dark"] .pbj-pill-tabs .nav-link,
    .dark-mode .pbj-pill-tabs .nav-link {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border-color: rgba(255, 255, 255, 0.08);
    }
    .pbj-pill-tabs .nav-link:hover {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }
    .pbj-pill-tabs .nav-link.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    }
</style>

@push('scripts')
<script>
    let _currentPbjSpkId = null;
    let _pbjHistoryPagination = { page: 1, last: 1, from: 1, to: 1, total: 0 };

    window.showSpkHistoryModal = function(spkId) {
        _currentPbjSpkId = spkId;
        
        Swal.fire({
            title: 'Memuat Riwayat Produksi...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/history`,
            method: 'GET',
            success: function (res) {
                Swal.close();
                if (!res.success) {
                    Swal.fire('Gagal!', res.message || 'Gagal memuat riwayat.', 'error');
                    return;
                }

                _currentPbjSpkData = res.spk;
                const spk = res.spk;
                const bKode = spk.barang_jadi ? spk.barang_jadi.kode_barang : '-';
                const bNama = spk.barang_jadi ? spk.barang_jadi.nama_barang : '-';
                const satName = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');

                $('#pbj_det_spk_title').text(`[${spk.nomor_spk}] ${bNama}`);
                $('#pbj_det_spk_nomor').text(spk.nomor_spk);
                $('#pbj_det_barang_jadi').text(`[${bKode}] ${bNama}`).attr('title', `[${bKode}] ${bNama}`);
                $('#pbj_det_target_qty').text(`${Number(spk.jumlah_rencana).toLocaleString('id-ID')} ${satName}`);
                $('#pbj_det_hasil_baik').text(`${Number(res.total_hasil_baik).toLocaleString('id-ID')} ${satName}`);
                $('#pbj_det_hasil_reject').text(`${Number(res.total_reject).toLocaleString('id-ID')} ${satName}`);
                $('#pbj_det_total_sesi').text(`${res.total_sesi} Sesi`);
                $('#pbj_badge_count_sesi_tab').text(res.total_sesi);

                $('#pbj_rekap_qty_baik').text(`${Number(res.total_hasil_baik).toLocaleString('id-ID')} ${satName}`);
                $('#pbj_rekap_qty_reject').text(`${Number(res.total_reject).toLocaleString('id-ID')} ${satName}`);

                let overallPercent = 0;
                let percentText = '0%';
                const targetNum = Number(spk.jumlah_rencana);
                const baikNum = Number(res.total_hasil_baik);
                if (targetNum > 0 && baikNum > 0) {
                    const rawPct = (baikNum / targetNum) * 100;
                    if (rawPct >= 100) {
                        overallPercent = 100;
                        percentText = '100%';
                    } else if (rawPct < 0.1) {
                        overallPercent = Math.max(2, rawPct);
                        percentText = `${rawPct.toFixed(2)}%`;
                    } else if (rawPct < 1) {
                        overallPercent = Math.max(2, rawPct);
                        percentText = `${rawPct.toFixed(1)}%`;
                    } else {
                        overallPercent = Math.min(100, Math.round(rawPct));
                        percentText = `${overallPercent}%`;
                    }
                }
                $('#pbj_det_overall_percent').text(percentText);
                $('#pbj_det_overall_progressbar').css('width', `${overallPercent}%`);

                const isSpkClosed = spk.is_fg_closed || spk.fg_closing_status === 'Closed' || spk.is_hpp_closed;
                const hasSessions = (res.total_sesi || 0) > 0 || (res.total_hasil_baik || 0) > 0;

                if (isSpkClosed) {
                    $('#btn_reopen_from_detail').removeClass('d-none').addClass('d-inline-flex');
                    $('#btn_closing_hpp_from_detail').addClass('d-none').removeClass('d-inline-flex');
                } else {
                    $('#btn_reopen_from_detail').addClass('d-none').removeClass('d-inline-flex');
                    if (hasSessions) {
                        $('#btn_closing_hpp_from_detail').removeClass('d-none').addClass('d-inline-flex');
                    } else {
                        $('#btn_closing_hpp_from_detail').addClass('d-none').removeClass('d-inline-flex');
                    }
                }

                const rekapTabEl = document.getElementById('pbj-tab-rekap-tab');
                if (rekapTabEl && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    bootstrap.Tab.getOrCreateInstance(rekapTabEl).show();
                } else if ($.fn.tab) {
                    $('#pbj-tab-rekap-tab').tab('show');
                }

                const modalEl = document.getElementById('modalDetailBarangJadi');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else {
                    $('#modalDetailBarangJadi').modal('show');
                }

                loadPbjHistorySessions(spkId, 1);
            },
            error: function (err) {
                Swal.close();
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memuat data SPK.', 'error');
            }
        });
    };

    window.loadPbjHistorySessions = function(spkId, page = 1) {
        $('#container_pbj_riwayat_sessions').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-emerald spinner-border-sm" role="status"></div>
                <div class="small text-muted mt-2">Memuat daftar sesi...</div>
            </div>
        `);

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/sessions?page=${page}`,
            method: 'GET',
            success: function (res) {
                if (!res.success) return;

                const sessions = res.data;
                _pbjHistoryPagination = {
                    page: res.current_page,
                    last: res.last_page,
                    from: res.from || 1,
                    to: res.to || 1,
                    total: res.total
                };

                let sessHtml = '';
                const totalCount = res.total;

                if (sessions.length === 0) {
                    sessHtml = `
                        <div class="text-center py-5 rounded-4 pbj-hero-card">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2 opacity-50"></i>
                            <h6 class="fw-bold spk-text-main">Belum Ada Sesi Penyerahan</h6>
                            <small class="text-muted">Belum ada transaksi penerimaan barang jadi yang dicatat untuk SPK ini.</small>
                        </div>
                    `;
                } else {
                    sessions.forEach((s, sIdx) => {
                        const globalIdx = _pbjHistoryPagination.from + sIdx;
                        const sesiNum   = totalCount - globalIdx + 1;
                        const gudangName = s.gudang ? `[${s.gudang.kode_gudang}] ${s.gudang.nama_gudang}` : 'Gudang Utama';

                        const penerimaName = s.penerima?.nama_lengkap || s.penerima_nama || 'Petugas Gudang';
                        const penyerahName = s.penyerah?.nama_lengkap || s.penyerah_nama || 'Operator Line';
                        const satName = s.satuan?.nama_satuan || s.barang_jadi?.satuan?.nama_satuan || 'Pcs';

                        const tgl = s.tanggal_penerimaan ? new Date(s.tanggal_penerimaan).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';
                        const hasJurnal = s.jurnal && s.jurnal.details && s.jurnal.details.length > 0;

                        let rakName = '-';
                        if (s.lokasi_rak) {
                            rakName = `[${s.lokasi_rak.kode_rak}] ${s.lokasi_rak.nama_rak}`;
                            if (s.lokasi_rak.level) rakName += ` - Lantai: ${s.lokasi_rak.level}`;
                            if (s.lokasi_rak.baris || s.lokasi_rak.kolom) rakName += ` (Brs: ${s.lokasi_rak.baris || '-'}, Klm: ${s.lokasi_rak.kolom || '-'})`;
                        }
                        const tglProd = s.tanggal_produksi ? new Date(s.tanggal_produksi).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : null;
                        const tglExp  = s.tanggal_kedaluwarsa ? new Date(s.tanggal_kedaluwarsa).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : null;
                        const serialsList = s.serials || [];
                        const serialsCount = serialsList.length;

                        let serialsHtml = '';
                        if (serialsCount > 0) {
                            const chips = serialsList.map(sn => `
                                <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle font-monospace px-2 py-1 me-1 mb-1" style="font-size: 11px;">
                                    <i class="bi bi-upc me-1 text-muted"></i>${sn.serial_number}
                                </span>
                            `).join('');

                            serialsHtml = `
                                <div class="mt-2.5 pt-2 border-top">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <span class="small fw-semibold text-muted d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                                            <i class="bi bi-upc-scan text-primary"></i>
                                            <span>Serial Numbers (${serialsCount} SN Terdaftar):</span>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center" style="max-height: 85px; overflow-y: auto;">
                                        ${chips}
                                    </div>
                                </div>
                            `;
                        }

                        const sessionStatus = s.status || 'Menunggu HPP';
                        let statusBadgeHtml = '';
                        if (sessionStatus === 'Menunggu HPP') {
                            statusBadgeHtml = `<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 11px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.5);"><i class="bi bi-hourglass-split me-1"></i>Menunggu HPP</span>`;
                        } else if (sessionStatus === 'Diterima') {
                            statusBadgeHtml = `<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 11px; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.5);"><i class="bi bi-check-circle-fill me-1"></i>Diterima (Stok Masuk)</span>`;
                        } else if (sessionStatus === 'Ditolak') {
                            statusBadgeHtml = `<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 11px; background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.5);"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>`;
                        }

                        const isSpkClosed = _currentPbjSpkData && (_currentPbjSpkData.is_fg_closed || _currentPbjSpkData.fg_closing_status === 'Closed' || _currentPbjSpkData.is_hpp_closed);
                        const isUnderClosing = _currentPbjSpkData && _currentPbjSpkData.fg_closing_status === 'Menunggu Closing';

                        let actionButtonsHtml = '';
                        if (sessionStatus === 'Menunggu HPP') {
                            actionButtonsHtml = `
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('closing-penerimaan-barang-jadi'))
                                <button type="button" class="btn btn-xs btn-warning rounded-pill px-3 py-1 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-1" onclick="bukaModalApproveHppPbj(${s.id})">
                                    <i class="bi bi-calculator-fill"></i>
                                    <span>Hitung HPP & Approve</span>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="bukaModalRejectPbj(${s.id}, '${s.nomor_penerimaan}')">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Tolak</span>
                                </button>
                                @endif
                                ${(!isSpkClosed && !isUnderClosing) ? `
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" onclick="editPenerimaanBarangJadi(${s.id})">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('hapus-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1 fw-semibold" onclick="batalkanSesiPenerimaan(${s.id}, '${s.nomor_penerimaan}')" title="Batalkan Pengajuan">
                                        <i class="bi bi-trash-fill me-1"></i>Batalkan
                                    </button>
                                    @endif
                                ` : ''}
                            `;
                        } else if (sessionStatus === 'Diterima') {
                            actionButtonsHtml = `
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-jurnal-penerimaan-barang-jadi'))
                                 ${hasJurnal ? `
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold" onclick="bukaModalJurnalPbjDetail(${s.id})">
                                        <i class="bi bi-journal-bookmark-fill me-1"></i>Jurnal
                                    </button>
                                ` : ''}
                                @endif
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('cetak-penerimaan-barang-jadi'))
                                <a href="/produksi/barang-jadi/${s.id}/print" target="_blank" class="btn btn-xs btn-primary rounded-pill px-3 py-1 text-white border-0 fw-semibold" style="background: linear-gradient(135deg,#0a2540,#1e40af);">
                                    <i class="bi bi-printer-fill me-1"></i>Cetak
                                </a>
                                @endif
                                ${(!isSpkClosed && !isUnderClosing) ? `
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="editPenerimaanBarangJadi(${s.id})" title="Edit Detail Penyerahan Sesi Ini">
                                        <i class="bi bi-pencil-square me-1"></i>Edit Sesi
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('hapus-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="batalkanSesiPenerimaan(${s.id}, '${s.nomor_penerimaan}')" title="Batalkan Penyerahan Sesi Ini & Revert Stok Gudang">
                                        <i class="bi bi-x-circle-fill me-1"></i>Batalkan Sesi
                                    </button>
                                    @endif
                                ` : ''}
                            `;
                        } else if (sessionStatus === 'Ditolak') {
                            actionButtonsHtml = `
                                ${(!isSpkClosed && !isUnderClosing) ? `
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-semibold" onclick="editPenerimaanBarangJadi(${s.id})">
                                        <i class="bi bi-pencil-square me-1"></i>Ajukan Ulang / Edit
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('hapus-penerimaan-barang-jadi'))
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1 fw-semibold" onclick="batalkanSesiPenerimaan(${s.id}, '${s.nomor_penerimaan}')" title="Hapus Pengajuan">
                                        <i class="bi bi-trash-fill me-1"></i>Hapus
                                    </button>
                                    @endif
                                ` : ''}
                            `;
                        }

                        let costSectionHtml = '';
                        if (sessionStatus === 'Diterima') {
                            costSectionHtml = `
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="pbj-sess-metric h-100" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.25);">
                                        <span class="pbj-hero-tile-sub text-emerald">HPP SATUAN FINAL</span>
                                        <div class="fw-bold fs-6 text-emerald font-monospace mt-0.5">Rp ${Number(s.hpp_satuan || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                        <small class="text-muted font-monospace" style="font-size: 11px;">Total: Rp ${Number(s.total_nilai_hpp || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</small>
                                    </div>
                                </div>
                            `;
                        } else if (sessionStatus === 'Menunggu HPP') {
                            costSectionHtml = `
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="pbj-sess-metric h-100" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.25);">
                                        <span class="pbj-hero-tile-sub text-warning">STATUS HPP</span>
                                        <b class="text-warning-emphasis font-monospace d-block" style="font-size: 12.5px;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</b>
                                        <small class="text-muted" style="font-size: 11px;">Stok & Jurnal belum masuk</small>
                                    </div>
                                </div>
                            `;
                        } else {
                            costSectionHtml = `
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="pbj-sess-metric h-100" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.25);">
                                        <span class="pbj-hero-tile-sub text-danger">STATUS PENGAJUAN</span>
                                        <b class="text-danger font-monospace d-block" style="font-size: 12.5px;"><i class="bi bi-x-circle-fill me-1"></i>Pengajuan Ditolak</b>
                                        <small class="text-muted" style="font-size: 11px;">Perlu revisi input</small>
                                    </div>
                                </div>
                            `;
                        }

                        let rejectNoticeHtml = '';
                        if (sessionStatus === 'Ditolak' && s.reject_reason) {
                            rejectNoticeHtml = `
                                <div class="alert alert-danger py-2 px-3 rounded-3 small mb-3 d-flex align-items-start gap-2 border-0" style="background-color: rgba(239, 68, 68, 0.1); color: #991b1b;">
                                    <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <b>Alasan Penolakan:</b> ${s.reject_reason}
                                        ${s.rejected_by ? `<span class="text-muted ms-1">(Ditolak oleh: ${s.rejected_by.nama_lengkap})</span>` : ''}
                                    </div>
                                </div>
                            `;
                        }

                        sessHtml += `
                            <div class="pbj-sess-card">
                                {{-- CARD HEADER --}}
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-emerald-vibrant rounded-pill px-3 py-1.5 font-monospace fw-bold" style="font-size: 11.5px;">
                                            <i class="bi bi-layers-fill me-1"></i>SESI #${sesiNum}
                                        </span>
                                        <span class="fw-bold font-monospace text-primary fs-6">${s.nomor_penerimaan}</span>
                                        ${statusBadgeHtml}
                                        <span class="text-muted small ms-1 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-calendar-event text-secondary"></i>
                                            <span>Tanggal: <b class="spk-text-main">${tgl}</b></span>
                                        </span>
                                        ${s.nomor_batch ? `
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                                <i class="bi bi-tag-fill me-1"></i>Batch: ${s.nomor_batch}
                                            </span>
                                        ` : ''}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        ${actionButtonsHtml}
                                    </div>
                                </div>

                                {{-- REJECT REASON NOTICE --}}
                                ${rejectNoticeHtml}

                                {{-- CARD METRICS TILES --}}
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6 col-lg-3">
                                        <div class="pbj-sess-metric h-100">
                                            <span class="pbj-hero-tile-sub">GUDANG & POSISI RAK</span>
                                            <b class="spk-text-main d-block" style="font-size: 13px;">${gudangName}</b>
                                            ${rakName !== '-' ? `<div class="small text-info mt-1 d-flex align-items-center gap-1"><i class="bi bi-grid-3x3-gap"></i><span>${rakName}</span></div>` : '<small class="text-muted">Tanpa Alokasi Rak</small>'}
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-3">
                                        <div class="pbj-sess-metric h-100">
                                            <span class="pbj-hero-tile-sub">PETUGAS & OPERATOR</span>
                                            <b class="spk-text-main d-block" style="font-size: 13px;"><i class="bi bi-person-fill text-muted me-1"></i>${penerimaName}</b>
                                            <div class="small text-muted mt-1"><i class="bi bi-person-badge text-secondary me-1"></i>QC: <b>${penyerahName}</b></div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-lg-3">
                                        <div class="pbj-sess-metric h-100 pbj-tile-good">
                                            <span class="pbj-hero-tile-sub text-emerald">KUANTITAS (BAIK)</span>
                                            <div class="fw-bold fs-5 text-emerald font-monospace mt-0.5">${Number(s.jumlah_hasil_baik).toLocaleString('id-ID')} <small class="fs-6 fw-normal">${satName}</small></div>
                                            ${Number(s.jumlah_reject) > 0 ? `<div class="small text-danger mt-1 font-monospace"><i class="bi bi-x-circle me-1"></i>Reject: ${Number(s.jumlah_reject).toLocaleString('id-ID')} ${satName}</div>` : ''}
                                        </div>
                                    </div>

                                    ${costSectionHtml}
                                </div>

                                {{-- BATCH & TANGGAL PRODUKSI EXTRA BAR --}}
                                ${(tglProd || tglExp || s.nomor_batch) ? `
                                    <div class="d-flex align-items-center gap-3 flex-wrap mt-2.5 pt-2 border-top small text-muted" style="font-size: 11.5px;">
                                        ${s.nomor_batch ? `<div><i class="bi bi-qr-code text-info me-1"></i>No. Batch: <b class="font-monospace spk-text-main">${s.nomor_batch}</b></div>` : ''}
                                        ${tglProd ? `<div><i class="bi bi-calendar-check text-success me-1"></i>Tgl Produksi: <b class="spk-text-main">${tglProd}</b></div>` : ''}
                                        ${tglExp ? `<div><i class="bi bi-calendar-x text-danger me-1"></i>Tgl Kedaluwarsa: <b class="spk-text-main">${tglExp}</b></div>` : ''}
                                    </div>
                                ` : ''}

                                {{-- SERIAL NUMBERS LIST --}}
                                ${serialsHtml}

                                ${s.catatan ? `<div class="small text-muted fst-italic mt-2.5 pt-2 border-top d-flex align-items-center gap-1.5" style="font-size: 11.5px;"><i class="bi bi-chat-quote text-primary fs-6"></i><span>Catatan: ${s.catatan}</span></div>` : ''}
                            </div>
                        `;
                    });
                }

                let paginationHtml = '';
                if (_pbjHistoryPagination.last > 1) {
                    paginationHtml = `
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="small text-muted font-monospace">
                                Menampilkan sesi ${_pbjHistoryPagination.from} - ${_pbjHistoryPagination.to} dari ${_pbjHistoryPagination.total} sesi
                            </span>
                            <div class="btn-group btn-group-sm font-monospace">
                                <button class="btn btn-outline-secondary rounded-start-pill px-3" ${_pbjHistoryPagination.page <= 1 ? 'disabled' : ''} onclick="loadPbjHistorySessions(${spkId}, ${_pbjHistoryPagination.page - 1})">
                                    <i class="bi bi-chevron-left me-1"></i>Prev
                                </button>
                                <span class="btn btn-outline-secondary disabled px-3 text-primary font-monospace fw-bold">
                                    ${_pbjHistoryPagination.page} / ${_pbjHistoryPagination.last}
                                </span>
                                <button class="btn btn-outline-secondary rounded-end-pill px-3" ${_pbjHistoryPagination.page >= _pbjHistoryPagination.last ? 'disabled' : ''} onclick="loadPbjHistorySessions(${spkId}, ${_pbjHistoryPagination.page + 1})">
                                    Next<i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }

                $('#container_pbj_riwayat_sessions').html(sessHtml + paginationHtml);
            },
            error: function () {
                $('#container_pbj_riwayat_sessions').html(`
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>
                        Gagal memuat sesi penerimaan barang jadi.
                    </div>
                `);
            }
        });
    }

    window.bukaModalJurnalPbjDetail = function(pbjId) {
        Swal.fire({
            title: 'Memuat Jurnal Akuntansi...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/${pbjId}`,
            method: 'GET',
            success: function (res) {
                Swal.close();
                const p = res.penerimaan;
                if (!p || !p.jurnal) {
                    Swal.fire('Info', 'Belum ada data jurnal akuntansi untuk transaksi ini.', 'info');
                    return;
                }

                const j = p.jurnal;
                $('#mjurnal_pbj_title').text(`Jurnal: ${j.nomor_jurnal || 'OTOMATIS'}`);
                $('#mjurnal_pbj_subtitle').text(`Ref: ${p.nomor_penerimaan} | Tanggal: ${j.tanggal_jurnal}`);

                let tbody = '';
                let totalDebit = 0;
                let totalKredit = 0;

                if (j.details && j.details.length > 0) {
                    j.details.forEach((d, idx) => {
                        const deb = parseFloat(d.debit) || 0;
                        const kre = parseFloat(d.kredit) || 0;
                        totalDebit += deb;
                        totalKredit += kre;

                        const accCode = d.account ? d.account.code : '-';
                        const accName = d.account ? d.account.name : '-';

                        tbody += `
                            <tr>
                                <td class="ps-3 text-center font-monospace small">${idx + 1}</td>
                                <td class="fw-bold font-monospace text-primary">
                                    [${accCode}] ${accName}
                                </td>
                                <td class="small spk-text-contrast">${d.keterangan || '-'}</td>
                                <td class="text-end font-monospace text-success fw-bold">
                                    ${deb > 0 ? 'Rp ' + deb.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
                                </td>
                                <td class="text-end pe-3 font-monospace text-danger fw-bold">
                                    ${kre > 0 ? 'Rp ' + kre.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
                                </td>
                            </tr>
                        `;
                    });

                    tbody += `
                        <tr class="bg-body-tertiary fw-bold border-top-2">
                            <td colspan="3" class="ps-3 text-uppercase">Total</td>
                            <td class="text-end text-success font-monospace">Rp ${totalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="text-end pe-3 text-danger font-monospace">Rp ${totalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        </tr>
                    `;
                } else {
                    tbody = `<tr><td colspan="5" class="text-center py-3 text-muted">Belum ada baris entri jurnal.</td></tr>`;
                }

                $('#tbody_detail_jurnal_pbj').html(tbody);
                $('#modalDetailJurnalPbj').modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire('Error', 'Gagal memuat data jurnal.', 'error');
            }
        });
    };

    window.batalkanSesiPenerimaan = function(pbjId, nomorPbj) {
        Swal.fire({
            title: 'Batalkan Sesi Penyerahan?',
            html: `Apakah Anda yakin ingin membatalkan transaksi penerimaan <b>${nomorPbj}</b>?<br><br><small class="text-danger">Stok barang jadi yang bertambah dari sesi ini akan ditarik kembali secara otomatis.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Membatalkan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/produksi/barang-jadi/${pbjId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.close();
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dibatalkan!',
                                text: res.message,
                                timer: 1600,
                                showConfirmButton: false
                            });

                            if (_currentPbjSpkId) {
                                showSpkHistoryModal(_currentPbjSpkId);
                            }
                            reloadTableBarangJadi();
                        }
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal membatalkan sesi.', 'error');
                    }
                });
            }
        });
    };

    window.reopenClosingFgFromDetail = function() {
        if (!_currentPbjSpkId || !_currentPbjSpkData) return;
        const modalEl = document.getElementById('modalDetailBarangJadi');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        } else {
            $('#modalDetailBarangJadi').modal('hide');
        }
        if (typeof konfirmasiReopenFg === 'function') {
            konfirmasiReopenFg(_currentPbjSpkId, _currentPbjSpkData.nomor_spk);
        }
    };

    window.closingHppFgFromDetail = function() {
        if (!_currentPbjSpkId) return;
        const modalEl = document.getElementById('modalDetailBarangJadi');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        } else {
            $('#modalDetailBarangJadi').modal('hide');
        }
        if (typeof bukaModalReviewClosingFg === 'function') {
            bukaModalReviewClosingFg(_currentPbjSpkId);
        }
    };
</script>
@endpush
