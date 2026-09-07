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
    {{-- BREADCRUMB & HEADER --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1.5 small">
                    <li class="breadcrumb-item"><a href="{{ url('/dashbord') }}" class="text-decoration-none spk-text-muted"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item spk-text-muted">Modul Produksi</li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Pengeluaran Bahan</li>
                </ol>
            </nav>
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
                        <div class="summary-card-val">{{ \App\Models\SpkPengeluaranBahan::forActiveCompany()->count() }}</div>
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
                        <div class="summary-card-val">{{ \App\Models\SpkProduksi::forActiveCompany()->where('status', 'In Progress')->count() }}</div>
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
                        <div class="summary-card-val">{{ $gudangList->count() }}</div>
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
                        <div class="summary-card-val">{{ \App\Models\SpkPengeluaranBahan::forActiveCompany()->whereDate('tanggal_pengeluaran', date('Y-m-d'))->count() }}</div>
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
            <table id="tabelPengeluaranBahan" class="table table-hover align-middle text-nowrap w-100 mb-0" style="min-width: 1200px;">
                <thead>
                    <tr class="small text-uppercase fw-bold spk-text-muted" style="border-bottom: 2px solid rgba(59, 130, 246, 0.18); font-size: 11.5px; letter-spacing: 0.04em;">
                        <th style="width: 60px;" class="text-center">NO</th>
                        <th style="min-width: 320px;">SPK & PRODUK JADI</th>
                        <th style="min-width: 260px;">PROGRES PENGELUARAN BAHAN</th>
                        <th style="min-width: 220px;">SESI PENYERAHAN</th>
                        <th class="text-center" style="width: 160px;">STATUS SPK</th>
                        <th class="text-center" style="width: 200px;">AKSI</th>
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

@push('scripts')
<script>
    let dtPengeluaran = null;

    $(document).ready(function() {
        dtPengeluaran = $('#tabelPengeluaranBahan').DataTable({
            processing: true,
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

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/history/sessions`,
            method: 'GET',
            data: { per_page: 100 },
            success: function (res) {
                $('#jurnal_spk_pb_loading').addClass('d-none');

                const sessions = res.data || [];
                const sessionsWithJurnal = sessions.filter(s => s.jurnal && s.jurnal.details && s.jurnal.details.length > 0);

                if (sessionsWithJurnal.length === 0) {
                    $('#jurnal_spk_pb_empty').removeClass('d-none');
                    return;
                }

                // Agregasi nilai akumulasi per Kepala Akun COA untuk seluruh SPK
                let accountSummaryMap = {};
                let grandTotalDebit = 0;
                let grandTotalKredit = 0;

                sessionsWithJurnal.forEach(function (s) {
                    const j = s.jurnal;
                    grandTotalDebit  += parseFloat(j.total_debit || 0);
                    grandTotalKredit += parseFloat(j.total_kredit || 0);

                    j.details.forEach(function (d) {
                        const kodeAkun = d.account ? d.account.code : (d.chart_of_account_id || 'AKUN');
                        const namaAkun = d.account ? d.account.name : 'Akun Jurnal';
                        const dVal = parseFloat(d.debit || 0);
                        const kVal = parseFloat(d.kredit || 0);

                        if (!accountSummaryMap[kodeAkun]) {
                            accountSummaryMap[kodeAkun] = {
                                code: kodeAkun,
                                name: namaAkun,
                                debit: 0,
                                kredit: 0,
                                ket: d.keterangan || ''
                            };
                        }
                        accountSummaryMap[kodeAkun].debit  += dVal;
                        accountSummaryMap[kodeAkun].kredit += kVal;
                    });
                });

                let tableRowsHtml = '';
                let rowCounter = 0;

                // Urutkan standar akuntansi: DEBIT selalu di ATAS, KREDIT selalu di BAWAH
                const sortedAccounts = Object.values(accountSummaryMap).sort((a, b) => {
                    if (a.debit > 0 && b.debit === 0) return -1;
                    if (a.debit === 0 && b.debit > 0) return 1;
                    return a.code.localeCompare(b.code);
                });

                sortedAccounts.forEach(function (acc) {
                    rowCounter++;
                    const isDebit  = acc.debit > 0;
                    const isKredit = acc.kredit > 0;

                    const badge = isDebit
                        ? `<span class="badge rounded-1 fw-bold" style="background:rgba(16,185,129,.15);color:#059669;font-size:10px;">DEBIT (WIP)</span>`
                        : `<span class="badge rounded-1 fw-bold" style="background:rgba(59,130,246,.15);color:#2563eb;font-size:10px;">KREDIT (BAHAN)</span>`;

                    tableRowsHtml += `
                        <tr class="border-bottom">
                            <td class="ps-3 text-center text-muted" style="font-size:11px;">${rowCounter}</td>
                            <td class="font-monospace fw-extrabold text-dark fs-6">${acc.code}</td>
                            <td class="fw-bold text-theme-main fs-6">${acc.name}</td>
                            <td class="text-muted" style="font-size: 11.5px;">${badge} <span class="ms-1 font-monospace text-muted" style="font-size:11px;">(Akumulasi Konsolidasi SPK)</span></td>
                            <td class="text-end font-monospace ${isDebit ? 'fw-extrabold text-success fs-6' : 'text-muted'}" style="font-size: 12.5px;">
                                ${acc.debit > 0 ? 'Rp ' + acc.debit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                            </td>
                            <td class="text-end pe-3 font-monospace ${isKredit ? 'fw-extrabold text-primary fs-6' : 'text-muted'}" style="font-size: 12.5px;">
                                ${acc.kredit > 0 ? 'Rp ' + acc.kredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}
                            </td>
                        </tr>`;
                });

                const consolidatedHtml = `
                    <div class="rounded-3 border overflow-hidden shadow-sm" style="border-color: rgba(56,189,248,0.25) !important;">
                        <div class="d-flex align-items-center justify-content-between px-3.5 py-3"
                             style="background: linear-gradient(90deg, rgba(56,189,248,0.12) 0%, rgba(56,189,248,0.04) 100%); border-bottom: 1px solid rgba(56,189,248,0.2);">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="bi bi-journal-check text-cyan-500 fs-5" style="color: #0284c7;"></i>
                                <div>
                                    <span class="fw-bold text-dark font-monospace d-block" style="font-size: 13.5px; letter-spacing: -0.2px;">RINGKASAN KONSOLIDASI AKUN JURNAL (TOTAL SPK)</span>
                                    <small class="text-muted" style="font-size: 11px;">Penggabungan akumulasi Debit & Kredit per Kepala Akun COA dari ${sessionsWithJurnal.length} Sesi Pengeluaran</small>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-2xs" style="background: rgba(56, 189, 248, 0.15); color: #0284c7; font-size: 11px;">
                                ${Object.keys(accountSummaryMap).length} Kepala Akun COA
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead style="background: rgba(248,250,252,0.95);">
                                    <tr class="text-muted" style="font-size: 10.5px; font-weight: 700; text-transform: uppercase;">
                                        <th class="ps-3 text-center" style="width: 40px;">#</th>
                                        <th style="width: 120px;">KODE AKUN</th>
                                        <th style="width: 280px;">NAMA AKUN COA</th>
                                        <th>POSISI AKUN / DESKRIPSI</th>
                                        <th class="text-end" style="width: 170px;">TOTAL DEBIT (WIP)</th>
                                        <th class="text-end pe-3" style="width: 170px;">TOTAL KREDIT (BAHAN)</th>
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

                // Update summary strip
                $('#jurnal_spk_pb_total_sesi').text(sessionsWithJurnal.length);
                $('#jurnal_spk_pb_total_debit').text('Rp ' + grandTotalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#jurnal_spk_pb_total_kredit').text('Rp ' + grandTotalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                $('#jurnal_spk_pb_sessions_container').html(consolidatedHtml);
                $('#jurnal_spk_pb_content').removeClass('d-none');
            },
            error: function () {
                $('#jurnal_spk_pb_loading').addClass('d-none');
                Swal.fire('Error', 'Gagal memuat data jurnal akuntansi pengeluaran bahan.', 'error');
                $('#modalJurnalSpkPengeluaran').modal('hide');
            }
        });
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

    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal.show').length > 0) {
            $('body').addClass('modal-open');
        }
    });
</script>
@endpush
</x-layouts>
