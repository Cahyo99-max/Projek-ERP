<x-layouts title="Peminjaman Barang Pameran">

    <link rel="stylesheet" href="{{ asset('inventori/pinjam_barang.css') }}?v={{ time() }}">

    <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4 peminjaman-page-container">
        <!-- Header Page -->
        <div class="page-header-wrapper d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="page-header-icon d-inline-flex align-items-center justify-content-center rounded-3 p-2 text-white flex-shrink-0" style="background: var(--accent-gradient); width: 44px; height: 44px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                        <i class="bi bi-box-arrow-up-right fs-5"></i>
                    </div>
                    <div>
                        <h3 class="page-header-title fw-bold mb-0 text-page-title">Peminjaman Barang Pameran</h3>
                        <p class="page-header-sub text-muted small mb-0">Kelola alur peminjaman unit sample &amp; display event pameran, tracking persetujuan, dan pengembalian fisik.</p>
                    </div>
                </div>
            </div>

            <!-- Action Toolbar (Filter & Tambah) -->
            <div class="page-header-actions d-flex align-items-center gap-2 flex-wrap">
                <!-- Tombol Filter Modal -->
                <button type="button" class="btn btn-cinematic-filter d-inline-flex align-items-center gap-2" id="btn-open-filter-modal" data-bs-toggle="modal" data-bs-target="#modalFilterPeminjaman" title="Buka Filter Data">
                    <i class="bi bi-funnel-fill text-info"></i>
                    <span>Filter</span>
                    <span class="badge rounded-pill bg-info text-dark ms-1 d-none" id="active-filter-badge">0</span>
                </button>

                <!-- Tombol Refresh Table -->
                <button type="button" class="btn btn-cinematic-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0" id="btn-refresh-table" title="Segarkan Data Tabel" style="width: 42px; height: 42px;">
                    <i class="bi bi-arrow-clockwise fs-6"></i>
                </button>

                <!-- Tombol Tambah Pengajuan -->
                @can('tambah-peminjaman')
                <a href="{{ route('peminjaman.create') }}" class="btn btn-cinematic-primary d-inline-flex align-items-center gap-2 px-3.5" id="btn-tambah-peminjaman">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Ajukan Peminjaman</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Summary KPI Cards (Interactive Filterable) -->
        <div class="row g-2 g-sm-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern stat-card-blue kpi-filter-trigger h-100" data-status="" title="Klik untuk menampilkan semua data">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pe-1">
                            <div class="text-muted small fw-medium stat-label">Total Peminjaman</div>
                            <div class="fs-4 fw-bold stat-number mt-1" id="stat-total-count">{{ number_format($counts['total']) }}</div>
                            <small class="text-info opacity-75 d-block mt-0.5 stat-sub" style="font-size: 0.725rem;">Semua Dokumen</small>
                        </div>
                        <div class="stat-icon-wrapper-modern stat-icon-blue">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern stat-card-yellow kpi-filter-trigger h-100" data-status="Menunggu" title="Klik untuk memfilter status Menunggu Approval">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pe-1">
                            <div class="text-muted small fw-medium stat-label">Menunggu Approval</div>
                            <div class="fs-4 fw-bold text-warning stat-number mt-1" id="stat-menunggu-count">{{ number_format($counts['menunggu']) }}</div>
                            <small class="text-warning opacity-75 d-block mt-0.5 stat-sub" style="font-size: 0.725rem;">Perlu Tindakan Atasan</small>
                        </div>
                        <div class="stat-icon-wrapper-modern stat-icon-yellow">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern stat-card-purple kpi-filter-trigger h-100" data-status="Sedang Dipinjam" title="Klik untuk memfilter status Sedang Dipinjam">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pe-1">
                            <div class="text-muted small fw-medium stat-label">Sedang di Pameran</div>
                            <div class="fs-4 fw-bold text-purple-adaptive stat-number mt-1" id="stat-dipinjam-count">{{ number_format($counts['dipinjam']) }}</div>
                            <small class="text-purple-adaptive opacity-75 d-block mt-0.5 stat-sub" style="font-size: 0.725rem;">Unit Aktif di Lapangan</small>
                        </div>
                        <div class="stat-icon-wrapper-modern stat-icon-purple">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-modern stat-card-green kpi-filter-trigger h-100" data-status="Selesai" title="Klik untuk memfilter status Selesai">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pe-1">
                            <div class="text-muted small fw-medium stat-label">Selesai / Kembali</div>
                            <div class="fs-4 fw-bold text-success stat-number mt-1" id="stat-selesai-count">{{ number_format($counts['selesai']) }}</div>
                            <small class="text-success opacity-75 d-block mt-0.5 stat-sub" style="font-size: 0.725rem;">Stok Masuk Gudang</small>
                        </div>
                        <div class="stat-icon-wrapper-modern stat-icon-green">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main DataTable Container Card -->
        <div class="card cinematic-card">
            <!-- Active Filters Bar (Visible dynamically when filters are active) -->
            <div id="active-filters-container" class="active-filters-bar d-none">
                <span class="small text-muted fw-semibold me-1"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                <div id="active-filter-chips" class="d-inline-flex flex-wrap gap-2"></div>
                <button type="button" class="btn btn-sm btn-link text-decoration-none text-danger p-0 ms-auto small" id="btn-clear-all-chips">
                    <i class="bi bi-x-circle me-1"></i>Hapus Semua Filter
                </button>
            </div>

            <!-- Table Responsive Area -->
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-custom align-middle" id="table-peminjaman">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 20%;">No. Dokumen</th>
                                <th style="width: 15%;">Tgl Pinjam</th>
                                <th style="width: 35%;">Nama Acara &amp; Lokasi</th>
                                <th style="width: 15%;" class="text-center">Status</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL FILTER PEMINJAMAN (Cinematic Backdrop Blur)
         ══════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalFilterPeminjaman" tabindex="-1" aria-labelledby="modalFilterLabel" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-filter-content">
                <div class="modal-header modal-filter-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-filter-icon">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title modal-filter-title fw-bold mb-0" id="modalFilterLabel">Filter Data Peminjaman</h5>
                            <small class="modal-filter-subtitle">Saring daftar dokumen berdasarkan status, tanggal, dan gudang asal</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body modal-filter-body">
                    <form id="form-filter-modal" autocomplete="off">
                        <!-- 1. Status Dokumen Filter -->
                        <div class="mb-4">
                            <label class="form-label-filter">
                                <i class="bi bi-tag-fill"></i> Status Dokumen
                            </label>
                            
                            <!-- Quick Chips Selector -->
                            <div class="d-flex flex-wrap gap-2 mb-2.5" id="status-quick-chips">
                                <span class="quick-filter-tag active" data-value="">Semua Status</span>
                                <span class="quick-filter-tag" data-value="Draft">Draft</span>
                                <span class="quick-filter-tag" data-value="Menunggu">Menunggu Approval</span>
                                <span class="quick-filter-tag" data-value="Barang Disiapkan">Barang Disiapkan</span>
                                <span class="quick-filter-tag" data-value="Barang Siap">Barang Siap</span>
                                <span class="quick-filter-tag" data-value="Sedang Dipinjam">Sedang Dipinjam</span>
                                <span class="quick-filter-tag" data-value="Selesai">Selesai (Kembali)</span>
                                <span class="quick-filter-tag" data-value="Selesai (Ada Masalah)">Ada Masalah</span>
                                <span class="quick-filter-tag" data-value="Ditolak">Ditolak</span>
                            </div>

                            <select class="form-select form-control-filter" id="modal-filter-status">
                                <option value="">-- Tampilkan Semua Status --</option>
                                <option value="Draft">Draft</option>
                                <option value="Menunggu">Menunggu Approval</option>
                                <option value="Barang Disiapkan">Barang Disiapkan (Gudang)</option>
                                <option value="Barang Siap">Barang Siap Diambil</option>
                                <option value="Sedang Dipinjam">Sedang Dipinjam (Pameran)</option>
                                <option value="Selesai">Selesai (Kembali Utuh)</option>
                                <option value="Selesai (Ada Masalah)">Selesai (Ada Masalah / Rusak / Hilang)</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>

                        <!-- 2. Gudang Asal Filter -->
                        <div class="mb-4">
                            <label class="form-label-filter">
                                <i class="bi bi-building"></i> Gudang Asal Barang
                            </label>
                            <select class="form-select form-control-filter" id="modal-filter-gudang">
                                <option value="">-- Semua Gudang Asal --</option>
                                @if (isset($gudangs))
                                    @foreach ($gudangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- 3. Rentang Tanggal Pinjam -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-filter mb-0">
                                    <i class="bi bi-calendar-range"></i> Rentang Tanggal Pinjam
                                </label>
                                <!-- Date Presets -->
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="date-preset-btn active" data-preset="all">Semua</button>
                                    <button type="button" class="date-preset-btn" data-preset="today">Hari Ini</button>
                                    <button type="button" class="date-preset-btn" data-preset="7days">7 Hari Terakhir</button>
                                    <button type="button" class="date-preset-btn" data-preset="thismonth">Bulan Ini</button>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-sublabel-filter">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-filter" id="modal-filter-start-date">
                                </div>
                                <div class="col-6">
                                    <label class="form-sublabel-filter">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-filter" id="modal-filter-end-date">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer modal-filter-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button type="button" class="btn-filter-reset d-inline-flex align-items-center gap-1.5" id="btn-reset-filter-modal">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-cinematic-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-cinematic-primary d-inline-flex align-items-center gap-2" id="btn-apply-filter-modal">
                            <i class="bi bi-check2-circle"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Section -->
    @push('scripts')
    <script>
        $(document).ready(function () {
            // Setup CSRF Token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Tooltips function
            function initTooltips() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, { container: 'body' });
                });
            }

            // Current Active Filter State
            let currentFilters = {
                status: '',
                gudang_asal_id: '',
                start_date: '',
                end_date: ''
            };

            // Flags for silent background real-time sync (< 1 detik)
            let isSilentReload = false;
            let isRealtimeFetching = false;

            // Helper to escape HTML characters in dynamic DataTables cell rendering (DOM XSS Protection)
            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Init DataTable (Urutkan tanggal pinjam terbaru paling atas)
            const table = $('#table-peminjaman').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                order: [[2, 'desc'], [1, 'desc']],
                ajax: {
                    url: "{{ route('peminjaman.index') }}",
                    data: function (d) {
                        d.status = currentFilters.status;
                        d.gudang_asal_id = currentFilters.gudang_asal_id;
                        d.start_date = currentFilters.start_date;
                        d.end_date = currentFilters.end_date;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', width: '50px' },
                    { 
                        data: 'nomor_peminjaman', 
                        name: 'nomor_peminjaman',
                        width: '18%',
                        render: function(data, type, row) {
                            const safeNomor = escapeHtml(data);
                            const safeId = parseInt(row.id) || 0;
                            return `<a href="/peminjaman-barang/${safeId}" class="doc-number-link" title="Buka Detail">
                                        <i class="bi bi-file-earmark-text me-1 text-info opacity-75"></i>${safeNomor}
                                    </a>`;
                        }
                    },
                    { data: 'tanggal_pinjam_fmt', name: 'tanggal_pinjam', width: '14%' },
                    { 
                        data: 'nama_acara', 
                        name: 'nama_acara',
                        width: '36%',
                        render: function(data, type, row) {
                            const safeAcara = escapeHtml(data);
                            const safeLokasi = escapeHtml(row.lokasi_acara || '-');
                            return `<div class="fw-semibold text-adaptive">${safeAcara}</div><small class="text-muted"><i class="bi bi-geo-alt me-1 text-info"></i>${safeLokasi}</small>`;
                        }
                    },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'text-center', width: '16%' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', width: '12%' }
                ],
                drawCallback: function () {
                    initTooltips();
                },
                language: {
                    processing: '<div class="spinner-border text-info spinner-border-sm me-2" role="status"></div> Memuat data persediaan...',
                    search: "Cari:",
                    searchPlaceholder: "Nomor dokumen, acara, PJ...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ data)",
                    zeroRecords: "Tidak ditemukan data peminjaman yang sesuai.",
                    paginate: {
                        first: "«",
                        last: "»",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            // Suppress processing indicator during silent real-time sync so header/table doesn't flicker
            table.on('preXhr.dt', function () {
                isRealtimeFetching = true;
                if (isSilentReload) {
                    $('#table-peminjaman_processing').css({ 'display': 'none', 'visibility': 'hidden' });
                } else {
                    $('#table-peminjaman_processing').css({ 'visibility': 'visible' });
                }
            });

            // Update KPI cards in real-time on every AJAX response without page reload
            table.on('xhr.dt', function (e, settings, json, xhr) {
                isRealtimeFetching = false;
                isSilentReload = false;
                if (json && json.counts) {
                    $('#stat-total-count').text(Number(json.counts.total).toLocaleString('id-ID'));
                    $('#stat-menunggu-count').text(Number(json.counts.menunggu).toLocaleString('id-ID'));
                    $('#stat-dipinjam-count').text(Number(json.counts.dipinjam).toLocaleString('id-ID'));
                    $('#stat-selesai-count').text(Number(json.counts.selesai).toLocaleString('id-ID'));
                }
            });

            // ─── REAL-TIME BACKGROUND POLLING (< 1 DETIK) ─────────────────────
            const REALTIME_INTERVAL_MS = 800; // Interval 800 milidetik (< 1 detik)

            function canRunRealtimePoll() {
                // 1. Jangan poll jika tab browser di-minimize atau pindah tab
                if (document.hidden) return false;

                // 2. Jangan poll jika modal sedang terbuka (Filter Modal dll)
                if ($('.modal.show').length > 0 || $('body').hasClass('modal-open')) return false;

                // 3. Jangan poll jika SweetAlert aktif (dialog hapus dll)
                if (typeof Swal !== 'undefined' && (Swal.isVisible() || $('.swal2-container').length > 0)) return false;

                // 4. Jangan poll jika user sedang fokus mengetik pada input search DataTables
                const activeEl = document.activeElement;
                if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
                    return false;
                }

                // 5. Jangan menumpuk request jika request sebelumnya masih berjalan
                if (isRealtimeFetching) return false;

                return true;
            }

            function triggerRealtimeSync() {
                if (canRunRealtimePoll()) {
                    isSilentReload = true;
                    // reload(null, false) memperbarui isi tabel secara instan tanpa me-reset pagination dan tanpa mengganggu header
                    table.ajax.reload(null, false);
                }
            }

            // Jalankan polling live real-time
            const realtimeTimer = setInterval(triggerRealtimeSync, REALTIME_INTERVAL_MS);

            // Bersihkan timer saat keluar dari halaman
            $(window).on('beforeunload', function () {
                clearInterval(realtimeTimer);
            });

            // Langsung sinkronkan saat tab kembali aktif
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    triggerRealtimeSync();
                }
            });

            // Refresh Table Button Manual
            $('#btn-refresh-table').on('click', function () {
                const $btn = $(this);
                $btn.find('i').addClass('bi-spin');
                isSilentReload = false;
                table.draw(false);
                setTimeout(() => $btn.find('i').removeClass('bi-spin'), 600);
            });

            // Sync Modal Input with Quick Chips
            $('#status-quick-chips .quick-filter-tag').on('click', function () {
                $('#status-quick-chips .quick-filter-tag').removeClass('active');
                $(this).addClass('active');
                const val = $(this).data('value');
                $('#modal-filter-status').val(val);
            });

            $('#modal-filter-status').on('change', function () {
                const val = $(this).val();
                $('#status-quick-chips .quick-filter-tag').removeClass('active');
                const targetChip = $(`#status-quick-chips .quick-filter-tag[data-value="${val}"]`);
                if (targetChip.length) {
                    targetChip.addClass('active');
                }
            });

            // Date Presets Handler
            $('.date-preset-btn').on('click', function () {
                $('.date-preset-btn').removeClass('active');
                $(this).addClass('active');

                const preset = $(this).data('preset');
                const today = new Date();
                const formatDate = (d) => d.toISOString().split('T')[0];

                if (preset === 'all') {
                    $('#modal-filter-start-date').val('');
                    $('#modal-filter-end-date').val('');
                } else if (preset === 'today') {
                    const str = formatDate(today);
                    $('#modal-filter-start-date').val(str);
                    $('#modal-filter-end-date').val(str);
                } else if (preset === '7days') {
                    const past = new Date();
                    past.setDate(today.getDate() - 7);
                    $('#modal-filter-start-date').val(formatDate(past));
                    $('#modal-filter-end-date').val(formatDate(today));
                } else if (preset === 'thismonth') {
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    $('#modal-filter-start-date').val(formatDate(firstDay));
                    $('#modal-filter-end-date').val(formatDate(lastDay));
                }
            });

            $('#modal-filter-start-date, #modal-filter-end-date').on('change input', function () {
                $('.date-preset-btn').removeClass('active');
            });

            // Update Active Filter UI & Chips
            function updateActiveFilterUI() {
                let activeCount = 0;
                let chipsHtml = '';

                // Status chip
                if (currentFilters.status) {
                    activeCount++;
                    chipsHtml += `
                        <span class="filter-chip">
                            <span>Status: <strong>${currentFilters.status}</strong></span>
                            <i class="bi bi-x chip-remove" data-type="status"></i>
                        </span>
                    `;
                }

                // Gudang chip
                if (currentFilters.gudang_asal_id) {
                    activeCount++;
                    const gudangText = $('#modal-filter-gudang option:selected').text() || 'Gudang Terpilih';
                    chipsHtml += `
                        <span class="filter-chip">
                            <span>Gudang: <strong>${gudangText}</strong></span>
                            <i class="bi bi-x chip-remove" data-type="gudang"></i>
                        </span>
                    `;
                }

                // Date chip
                if (currentFilters.start_date || currentFilters.end_date) {
                    activeCount++;
                    const dateText = (currentFilters.start_date || 'Awal') + ' s/d ' + (currentFilters.end_date || 'Akhir');
                    chipsHtml += `
                        <span class="filter-chip">
                            <span>Tanggal: <strong>${dateText}</strong></span>
                            <i class="bi bi-x chip-remove" data-type="date"></i>
                        </span>
                    `;
                }

                // Update Filter Badge
                const $badge = $('#active-filter-badge');
                if (activeCount > 0) {
                    $badge.text(activeCount).removeClass('d-none');
                    $('#btn-open-filter-modal').addClass('active');
                    $('#active-filter-chips').html(chipsHtml);
                    $('#active-filters-container').removeClass('d-none');
                } else {
                    $badge.addClass('d-none');
                    $('#btn-open-filter-modal').removeClass('active');
                    $('#active-filter-chips').empty();
                    $('#active-filters-container').addClass('d-none');
                }

                // Highlight KPI card if matching status
                $('.stat-card-modern').removeClass('active-filter');
                if (currentFilters.status) {
                    $(`.stat-card-modern[data-status="${currentFilters.status}"]`).addClass('active-filter');
                } else {
                    $(`.stat-card-modern[data-status=""]`).addClass('active-filter');
                }
            }

            // Apply Filter from Modal
            $('#btn-apply-filter-modal').on('click', function () {
                currentFilters.status = $('#modal-filter-status').val();
                currentFilters.gudang_asal_id = $('#modal-filter-gudang').val();
                currentFilters.start_date = $('#modal-filter-start-date').val();
                currentFilters.end_date = $('#modal-filter-end-date').val();

                updateActiveFilterUI();
                table.draw();
                $('#modalFilterPeminjaman').modal('hide');
            });

            // Reset Filter in Modal
            $('#btn-reset-filter-modal').on('click', function () {
                $('#modal-filter-status').val('');
                $('#status-quick-chips .quick-filter-tag').removeClass('active');
                $('#status-quick-chips .quick-filter-tag[data-value=""]').addClass('active');
                $('#modal-filter-gudang').val('');
                $('#modal-filter-start-date').val('');
                $('#modal-filter-end-date').val('');
                $('.date-preset-btn').removeClass('active');
                $('.date-preset-btn[data-preset="all"]').addClass('active');
            });

            // Clear Individual Chip
            $(document).on('click', '.chip-remove', function () {
                const type = $(this).data('type');
                if (type === 'status') {
                    currentFilters.status = '';
                    $('#modal-filter-status').val('');
                    $('#status-quick-chips .quick-filter-tag').removeClass('active');
                    $('#status-quick-chips .quick-filter-tag[data-value=""]').addClass('active');
                } else if (type === 'gudang') {
                    currentFilters.gudang_asal_id = '';
                    $('#modal-filter-gudang').val('');
                } else if (type === 'date') {
                    currentFilters.start_date = '';
                    currentFilters.end_date = '';
                    $('#modal-filter-start-date').val('');
                    $('#modal-filter-end-date').val('');
                    $('.date-preset-btn').removeClass('active');
                    $('.date-preset-btn[data-preset="all"]').addClass('active');
                }

                updateActiveFilterUI();
                table.draw();
            });

            // Clear All Chips
            $('#btn-clear-all-chips').on('click', function () {
                currentFilters.status = '';
                currentFilters.gudang_asal_id = '';
                currentFilters.start_date = '';
                currentFilters.end_date = '';

                $('#btn-reset-filter-modal').trigger('click');
                updateActiveFilterUI();
                table.draw();
            });

            // Interactive KPI Card Filter Click
            $('.kpi-filter-trigger').on('click', function () {
                const status = $(this).data('status');
                currentFilters.status = status;
                $('#modal-filter-status').val(status);
                $('#status-quick-chips .quick-filter-tag').removeClass('active');
                $(`#status-quick-chips .quick-filter-tag[data-value="${status}"]`).addClass('active');

                updateActiveFilterUI();
                table.draw();
            });

            // Delete Record Handler
            $(document).on('click', '.btn-delete', function () {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Hapus Dokumen?',
                    text: `Apakah Anda yakin ingin menghapus data peminjaman ${nomor}? Dokumen yang telah dihapus tidak dapat dipulihkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: `/peminjaman-barang/${id}`,
                            type: 'DELETE',
                            success: function (res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus!',
                                        text: res.message
                                    });
                                    table.draw(false);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: res.message
                                    });
                                }
                            },
                            error: function (xhr) {
                                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menghapus data.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: msg
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
