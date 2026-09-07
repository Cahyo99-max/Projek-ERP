<x-layouts title="Realisasi & Kontrol Tahapan Produksi - Modul Produksi">
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
    <style>
        .realisasi-glass-hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 58, 138, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(16px);
        }
        html:not([data-bs-theme="dark"]) .realisasi-glass-hero {
            background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%);
            color: #ffffff !important;
        }

        .realisasi-kpi-card {
            background-color: var(--spk-card-bg) !important;
            border: 1px solid var(--spk-border-color) !important;
            color: var(--spk-text-main) !important;
            border-radius: 1rem;
            transition: all 0.25s ease;
        }
        .realisasi-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        /* MODERN VIBRANT GRADIENT CARDS (IMAGE MATCH) */
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
        
        .card-grad-blue {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%) !important;
        }
        .card-grad-green {
            background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%) !important;
        }
        .card-grad-amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%) !important;
        }
        .card-grad-orange {
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 50%, #f97316 100%) !important;
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

        .realisasi-table-card {
            background-color: var(--spk-card-bg) !important;
            border: 1px solid var(--spk-border-color) !important;
            color: var(--spk-text-main) !important;
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        /* DATATABLES THEME CONTROLS */
        .dataTables_length select,
        .dataTables_filter input,
        #filter_status_spk {
            border-radius: 9999px !important;
            padding: 0.35rem 0.85rem !important;
            font-size: 0.85rem !important;
            transition: all 0.2s ease !important;
        }
        html:not([data-bs-theme="dark"]) .dataTables_length select,
        html:not([data-bs-theme="dark"]) .dataTables_filter input,
        html:not([data-bs-theme="dark"]) #filter_status_spk {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .dataTables_length select,
        html[data-bs-theme="dark"] .dataTables_filter input,
        html[data-bs-theme="dark"] #filter_status_spk {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid rgba(59, 130, 246, 0.35) !important;
        }
        .dataTables_length label,
        .dataTables_filter label {
            color: var(--spk-text-muted) !important;
            font-weight: 600;
        }
    </style>
    @endpush

    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- HERO HEADER --}}
        <div class="realisasi-glass-hero p-4 rounded-4 mb-4 text-white position-relative overflow-hidden">
            <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center shadow-lg" style="background: rgba(255,255,255,0.15); width: 48px; height: 48px;">
                            <i class="bi bi-clock-history fs-3 text-white"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold m-0 text-white font-monospace" style="letter-spacing: -0.5px;">Realisasi & Kontrol Tahapan Produksi</h3>
                            <small class="text-white-50" style="font-size: 13px;">Monitoring harian progres pengerjaan stasiun kerja / divisi & penginputan hasil kerja operator</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-start text-lg-end">
                    <span class="badge px-3.5 py-2 rounded-pill font-monospace shadow-sm border" style="background: rgba(255, 255, 255, 0.25); border-color: rgba(255, 255, 255, 0.5) !important; color: #ffffff !important; font-size: 12px; font-weight: 800; letter-spacing: 0.6px; text-shadow: 0 1px 2px rgba(0,0,0,0.4); backdrop-filter: blur(8px);">
                        <i class="bi bi-shield-check text-warning me-1.5 fs-6"></i>KONTROL AREA PRODUKSI
                    </span>
                </div>
            </div>
        </div>

        {{-- SUMMARY KPI CARDS (PREMIUM GRADIENT) --}}
        <div class="row g-3 mb-4">
            {{-- CARD 1: BLUE (SPK AKTIF) --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="modern-summary-card card-grad-blue">
                    <div class="summary-card-circle-bg"></div>
                    <div class="summary-card-circle-bg-2"></div>
                    <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                        <div>
                            <div class="summary-card-title">SPK AKTIF BERJALAN</div>
                            <div class="summary-card-val">{{ number_format($totalSpkActive, 0, ',', '.') }}</div>
                            <div class="summary-card-sub">Dokumen SPK In Progress</div>
                        </div>
                        <div class="summary-icon-box">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: GREEN (TAHAPAN SELESAI) --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="modern-summary-card card-grad-green">
                    <div class="summary-card-circle-bg"></div>
                    <div class="summary-card-circle-bg-2"></div>
                    <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                        <div>
                            <div class="summary-card-title">TAHAPAN SELESAI (100%)</div>
                            <div class="summary-card-val">{{ number_format($totalTahapSelesai, 0, ',', '.') }}</div>
                            <div class="summary-card-sub">Stasiun kerja tuntas 100%</div>
                        </div>
                        <div class="summary-icon-box">
                            <i class="bi bi-check-circle-fill text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: AMBER (SEDANG DIKERJAKAN) --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="modern-summary-card card-grad-amber">
                    <div class="summary-card-circle-bg"></div>
                    <div class="summary-card-circle-bg-2"></div>
                    <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                        <div>
                            <div class="summary-card-title">SEDANG DIKERJAKAN</div>
                            <div class="summary-card-val d-flex align-items-center gap-2">
                                <span>{{ number_format($totalTahapBerjalan, 0, ',', '.') }}</span>
                                <span class="badge bg-white bg-opacity-25 text-white px-2 py-0.5 font-monospace fw-normal" style="font-size:10.5px;">Progres</span>
                            </div>
                            <div class="summary-card-sub">Stasiun kerja aktif diproses</div>
                        </div>
                        <div class="summary-icon-box">
                            <i class="bi bi-gear-wide-connected text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 4: ORANGE (SPK COMPLETED & SIAP KE GUDANG) --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="modern-summary-card card-grad-orange">
                    <div class="summary-card-circle-bg"></div>
                    <div class="summary-card-circle-bg-2"></div>
                    <div class="d-flex align-items-start justify-content-between position-relative" style="z-index:2;">
                        <div>
                            <div class="summary-card-title">SPK COMPLETED</div>
                            <div class="summary-card-val">{{ number_format($totalSpkCompleted, 0, ',', '.') }}</div>
                            <div class="summary-card-sub">Siap diserahterimakan ke Gudang</div>
                        </div>
                        <div class="summary-icon-box">
                            <i class="bi bi-patch-check-fill text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DATATABLE MAIN CARD --}}
        <div class="realisasi-table-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 pb-3 border-bottom border-secondary-subtle">
                <h5 class="fw-bold spk-text-contrast m-0 font-monospace">
                    <i class="bi bi-list-task text-primary me-2"></i>Daftar SPK & Status Tahapan Produksi
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs position-relative" data-bs-toggle="modal" data-bs-target="#modalFilterRealisasiProduksi" title="Buka Filter Data Realisasi Produksi">
                        <i class="bi bi-funnel-fill fs-6 text-primary"></i>
                        <span>Filter Data</span>
                        <span id="filter_active_indicator" class="position-absolute top-0 start-100 translate-middle p-1.5 bg-primary border border-light rounded-circle d-none"></span>
                    </button>
                </div>
            </div>

            {{-- ACTIVE FILTER CHIP INDICATOR BAR --}}
            <div id="active_filter_bar" class="d-none mb-3">
                <div class="p-2.5 px-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 font-monospace small">
                        <span class="badge spk-badge-blue rounded-pill px-2.5 py-1"><i class="bi bi-funnel-fill me-1"></i>Filter Aktif</span>
                        <span id="active_filter_summary_text" class="spk-text-main fw-bold"></span>
                    </div>
                    <button type="button" onclick="resetRealisasiFilter()" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 font-monospace fw-semibold d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-x-circle-fill"></i> Hapus Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table_realisasi_spk" class="table align-middle w-100 spk-table-main" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="ps-3 text-center" style="width: 50px;">No</th>
                            <th style="min-width: 300px;">Informasi SPK & Barang Jadi</th>
                            <th style="min-width: 240px;">Progress Realisasi Tahapan</th>
                            <th class="text-center" style="width: 140px;">Status SPK</th>
                            <th class="text-center pe-3" style="width: 180px;">Aksi Kontrol</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL FILTER REALISASI PRODUKSI --}}
    <div class="modal fade" id="modalFilterRealisasiProduksi" tabindex="-1" aria-labelledby="modalFilterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content spk-modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold spk-text-main d-flex align-items-center gap-2 fs-6" id="modalFilterLabel">
                        <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex">
                            <i class="bi bi-funnel-fill"></i>
                        </div>
                        <span>Filter Realisasi Produksi</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formFilterRealisasi">
                        <div class="mb-3">
                            <label class="form-label small fw-bold spk-text-muted mb-1">Status Operasional SPK</label>
                            <select id="filter_status_spk" class="form-select spk-input rounded-3 font-monospace">
                                <option value="all" selected>-- Semua Status Operasional --</option>
                                <option value="proses">⚡ PROSES TAHAPAN PABRIK</option>
                                <option value="menunggu_gudang">📦 MENUNGGU GUDANG FG</option>
                                <option value="lengkap">✅ LENGKAP & COMPLETE (100%)</option>
                                <option value="belum_bahan">🔴 BELUM KELUAR BAHAN</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="resetRealisasiFilter()">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset Filter</span>
                    </button>
                    <button type="button" id="btn_apply_filter" class="btn btn-sm btn-primary rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" onclick="applyRealisasiFilter()">
                        <i class="bi bi-check-lg"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let tableRealisasiSpk = null;

        $(document).ready(function () {
            tableRealisasiSpk = $('#table_realisasi_spk').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('produksi.realisasi.index') }}",
                    data: function (d) {
                        d.status = $('#filter_status_spk').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small ps-3 spk-text-muted-contrast' },
                    { data: 'spk_info', name: 'nomor_spk' },
                    { data: 'tahapan_progress', name: 'id', orderable: false, searchable: false },
                    { data: 'status_spk', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-3' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari SPK / Barang Jadi...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ SPK",
                    infoEmpty: "Menampilkan 0 data",
                    zeroRecords: "Tidak ada data SPK produksi ditemukan.",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>'
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3"ip>',
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
            });

            updateFilterIndicator();
        });

        function applyRealisasiFilter() {
            $('#modalFilterRealisasiProduksi').modal('hide');
            tableRealisasiSpk.ajax.reload();
            updateFilterIndicator();
        }

        function resetRealisasiFilter() {
            $('#filter_status_spk').val('all');
            $('#modalFilterRealisasiProduksi').modal('hide');
            tableRealisasiSpk.ajax.reload();
            updateFilterIndicator();
        }

        function updateFilterIndicator() {
            const statusVal = $('#filter_status_spk').val();
            const isFiltered = statusVal && statusVal !== 'all';
            
            if (isFiltered) {
                $('#filter_active_indicator').removeClass('d-none');
                $('#active_filter_bar').removeClass('d-none');

                let statusName = $('#filter_status_spk option:selected').text();
                $('#active_filter_summary_text').text(`Status: ${statusName}`);
            } else {
                $('#filter_active_indicator').addClass('d-none');
                $('#active_filter_bar').addClass('d-none');
                $('#active_filter_summary_text').text('');
            }
        }

        function alertBahanBelumDisiapkan(nomorSpk) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bahan Baku Belum Disiapkan!',
                    html: 'Bahan baku untuk <b>' + nomorSpk + '</b> belum dikeluarkan dari gudang oleh tim Warehouse.<br><br><span class="text-body-secondary small">Harap lakukan pengeluaran bahan baku terlebih dahulu di modul Pengeluaran Bahan Baku sebelum membuka kontrol tahapan pengerjaan.</span>',
                    confirmButtonText: 'Saya Mengerti',
                    confirmButtonColor: '#2563eb',
                    customClass: {
                        popup: 'rounded-4 shadow-lg'
                    }
                });
            } else {
                alert('PERINGATAN: Bahan baku untuk ' + nomorSpk + ' belum disiapkan/dikeluarkan dari gudang oleh tim Warehouse.');
            }
        }

        function showKontrolTerkunciWarning(nomorSpk) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '<div class="d-flex align-items-center justify-content-center gap-2 text-danger fs-5 fw-bold"><i class="bi bi-lock-fill text-danger fs-4"></i> Kontrol Tahapan Terkunci</div>',
                    html: `<div class="text-start mb-3" style="font-size: 0.93rem; line-height: 1.45;">
                                Realisasi SPK (<b>${nomorSpk}</b>) telah <b>Selesai (Completed)</b> dan status kontrol tahapan saat ini <b>TERKUNCI</b>.
                           </div>
                           <div class="p-3.5 rounded-3 border mb-3 text-start shadow-xs" style="background-color: var(--spk-card-bg, rgba(239, 68, 68, 0.06)); border-color: rgba(239, 68, 68, 0.25) !important;">
                               <div class="d-flex align-items-center gap-2 text-danger fw-bold font-monospace" style="font-size: 0.85rem;">
                                   <i class="bi bi-shield-lock-fill fs-6"></i> OTORISASI KHUSUS SUPER ADMIN:
                               </div>
                               <div class="spk-text-muted mt-1.5" style="font-size: 0.84rem; line-height: 1.5;">
                                   Hanya wewenang <b>Administrator Sistem (Super Admin)</b> yang diizinkan untuk membuka kembali kunci kontrol tahapan SPK yang sudah completed.
                               </div>
                           </div>
                           <div class="spk-text-muted text-start" style="font-size: 0.82rem;">
                               <i class="bi bi-headset me-1 text-info"></i>Silakan hubungi Administrator Sistem (Super Admin) untuk pembukaan kunci.
                           </div>`,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-semibold'
                    }
                });
            } else {
                alert('OTORISASI KHUSUS: Kontrol Tahapan SPK ' + nomorSpk + ' telah Selesai (Completed) dan TERKUNCI. Silakan hubungi Administrator Sistem (Super Admin).');
            }
        }
    </script>
    @endpush
</x-layouts>
