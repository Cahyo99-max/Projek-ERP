<x-layouts>
    <x-slot:title>Pengiriman &amp; Surat Jalan</x-slot:title>

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
    
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-truck fs-5"></i>
                    </span>
                    Pengiriman &amp; Surat Jalan 
                </h4>
                <p class="text-muted small mb-0 mt-1">Kelola pengeluaran fisik barang, pelacakan ekspedisi/kurir, dan cetak Surat Jalan resmi.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap do-header-actions">
                <!-- Tombol Trigger Modal Filter -->
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2 position-relative do-btn-filter-trigger" id="btn_open_modal_filter" data-bs-toggle="modal" data-bs-target="#modalFilterDO">
                    <i class="bi bi-funnel-fill text-primary fs-6"></i>
                    <span>Filter Data</span>
                    <span class="badge bg-primary text-white rounded-pill font-monospace d-none" id="badge_active_filter_count" style="font-size: 0.72rem;">0</span>
                </button>

                <!-- Tombol Buat DO Baru -->
                @can('tambah-delivery-order')
                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2 do-btn-primary" id="btn_open_modal_create" data-bs-toggle="modal" data-bs-target="#modalCreateDO" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                    <i class="bi bi-plus-circle-fill fs-6"></i> Buat Surat Jalan Baru
                </button>
                @endcan
            </div>
        </div>

        <!-- KPI Metric Cards (Modern & Cinematic) -->
        <div class="row g-3 g-sm-3.5 g-md-4 mb-4 mb-md-4.5">
            <!-- Total Pengiriman -->
            <div class="col-6 col-md-3">
                <div class="do-kpi-card do-kpi-primary h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="do-kpi-label">Total Pengiriman</span>
                            <div class="do-kpi-icon do-kpi-icon-primary">
                                <i class="bi bi-truck-flatbed"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-body font-monospace fs-4 fs-md-3">{{ number_format($kpis['total'] ?? 0) }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-file-text me-1"></i>Dokumen DO
                        </span>
                    </div>
                </div>
            </div>

            <!-- Menunggu Review -->
            <div class="col-6 col-md-3">
                <div class="do-kpi-card do-kpi-amber h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="do-kpi-label">Menunggu Review</span>
                            <div class="do-kpi-icon do-kpi-icon-amber">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-warning font-monospace fs-4 fs-md-3">{{ number_format($kpis['menunggu'] ?? 0) }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-hourglass-split me-1"></i>Draft &amp; Approval
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dalam Pengiriman -->
            <div class="col-6 col-md-3">
                <div class="do-kpi-card do-kpi-sky h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="do-kpi-label">Dalam Pengiriman</span>
                            <div class="do-kpi-icon do-kpi-icon-sky">
                                <i class="bi bi-send-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-info font-monospace fs-4 fs-md-3">{{ number_format($kpis['proses'] ?? 0) }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-truck me-1"></i>Proses Kirim
                        </span>
                    </div>
                </div>
            </div>

            <!-- Selesai / Terkirim -->
            <div class="col-6 col-md-3">
                <div class="do-kpi-card do-kpi-emerald h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="do-kpi-label">Selesai / Terkirim</span>
                            <div class="do-kpi-icon do-kpi-icon-emerald">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-success font-monospace fs-4 fs-md-3">{{ number_format($kpis['selesai'] ?? 0) }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-check-circle me-1"></i>Tiba di Tujuan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filter Chips Bar (Muncul Otomatis Jika Ada Filter Aktif) -->
        <div class="d-none align-items-center gap-2 mb-3 px-1 flex-wrap" id="active_filters_container">
            <span class="small text-muted fw-bold font-monospace d-flex align-items-center gap-1">
                <i class="bi bi-funnel text-primary"></i> Filter Aktif:
            </span>
            <div class="d-flex align-items-center gap-1.5 flex-wrap" id="active_filter_chips"></div>
            <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0 ms-2 small fw-semibold font-monospace" id="btn_quick_clear_all_filters">
                <i class="bi bi-x-circle me-1"></i>Hapus Semua Filter
            </button>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden table-custom-card">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i> Daftar Surat Jalan &amp; Pengiriman
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 fw-bold">Live Data</span>
            </div>
            <div class="card-body p-3 p-md-3.5">
                <!-- Swipe Hint Banner untuk Mobile -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2.5 mb-3 rounded-3 do-mobile-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-primary fs-6"></i>
                        <span class="fw-semibold small">Geser tabel untuk melihat rincian lengkap</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold do-swipe-pill">Swipe &rarr;</span>
                </div>

                <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                    <table class="table table-hover align-middle w-100 mb-0" id="tableDeliveryOrders" style="min-width: 820px;">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white">
                                <th style="width: 45px;" class="text-center">No</th>
                                <th style="min-width: 140px;">Nomor DO</th>
                                <th style="min-width: 150px;">No. Pesanan (SO)</th>
                                <th style="min-width: 200px;">Penerima / Instansi</th>
                                <th style="min-width: 130px;">Jumlah Item</th>
                                <th class="text-center" style="min-width: 130px;">Status</th>
                                <th class="text-center" style="min-width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals Include -->
    @include('modul_marketing.pengiriman._modal_create')
    @include('modul_marketing.pengiriman._modal_edit')
    @include('modul_marketing.pengiriman._modal_detail')
    @include('modul_marketing.pengiriman._modal_select_serials')
    @include('modul_marketing.pengiriman._modal_filter')

    @push('styles')
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_smd/pengiriman.css') }}?v={{ time() }}">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Hak Akses Pengiriman (Delivery Order)
        const CAN_APPROVE_DO = @json(auth()->user() ? auth()->user()->can('approve-delivery-order') : false);
        const CAN_COMPLETE_DO = @json(auth()->user() ? (auth()->user()->can('selesai-delivery-order') || auth()->user()->can('approve-delivery-order')) : false);
        const CAN_LIHAT_JURNAL = @json(auth()->user() ? auth()->user()->can('lihat-jurnal-delivery-order') : false);
        const CAN_EDIT_DO = @json(auth()->user() ? auth()->user()->can('edit-delivery-order') : false);

        // State Global Serial Number per Item Row (Create Modal)
        let itemSerialsStore = {}; // { [idx]: ['SN1', 'SN2'] }
        let itemSerialMeta  = {}; // { [idx]: { isRequired: bool, barangId: int, barangNama: str, barangSku: str } }

        // State Global Serial Number per Item Row (Edit Modal)
        let editItemSerialsStore = {}; // { [idx]: ['SN1', 'SN2'] }
        let editItemSerialMeta = {}; // { [idx]: { isRequired: bool, barangId: int, barangNama: str, barangSku: str, maxAllowed: num, gudangStock: num, satuan: str } }
        let activeSnContext = 'create'; // 'create' | 'edit'

        let currentSnIdx = null;
        let currentSnBarangId = null;
        let currentSnBarangNama = '';
        let currentSnBarangSku = '';
        let currentSnIsRequired = false;
        let currentSnTargetQty = 0;
        let modalWorkingSerials = [];
        let currentAvailableStockSerials = [];

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $(document).ready(function() {
            // Inisialisasi DataTable Delivery Orders
            let tableDO = $('#tableDeliveryOrders').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('marketing.delivery-order.data') }}",
                    data: function (d) {
                        d.status = $('#filter_status').val();
                        d.gudang_id = $('#filter_gudang_id').val();
                        d.tender_id = $('#filter_tender_id').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small text-nowrap' },
                    { data: 'nomor_do', name: 'nomor_do', className: 'text-nowrap' },
                    { data: 'nomor_so', name: 'salesOrder.nomor_so', className: 'text-nowrap' },
                    { data: 'customer_info', name: 'customer.nama_customer' },
                    { data: 'items_count', name: 'items_count', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'status', name: 'status', className: 'text-center text-nowrap' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' }
                ],
                order: [[1, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                deferRender: true,
                orderClasses: false,
                language: {
                    search: "",
                    searchPlaceholder: "Cari nomor DO, SO, penerima...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pengiriman",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total pengiriman)",
                    zeroRecords: "Tidak ada pengiriman yang cocok",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    },
                    emptyTable: "Belum ada riwayat pengiriman / surat jalan.",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                initComplete: function() {
                    let api = this.api();
                    let searchInput = $('#tableDeliveryOrders_filter input');
                    let debounceTimer = null;

                    // Unbind instant search and apply 400ms debounce to prevent heavy queries on millions of rows
                    searchInput.unbind().on('input keyup', function(e) {
                        let term = this.value;
                        clearTimeout(debounceTimer);
                        if (e.keyCode === 13) {
                            api.search(term).draw();
                            return;
                        }
                        debounceTimer = setTimeout(function() {
                            api.search(term).draw();
                        }, 400);
                    });
                }
            });

            // Filter Handlers (Modal Filter & Active Chips)
            function updateActiveFilterUI() {
                let status = $('#filter_status').val();
                let gudang = $('#filter_gudang_id').val();
                let gudangText = $('#filter_gudang_id option:selected').text();
                let tender = $('#filter_tender_id').val();
                let tenderText = $('#filter_tender_id option:selected').text().trim();
                let startDate = $('#filter_start_date').val();
                let endDate = $('#filter_end_date').val();

                let activeCount = 0;
                let chipsHtml = '';

                if (status) {
                    activeCount++;
                    chipsHtml += `
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size:0.75rem;">
                            <span>Status: <b>${escapeHtml(status)}</b></span>
                            <i class="bi bi-x-circle-fill cursor-pointer text-primary opacity-75 btn-remove-filter" data-target="#filter_status" style="cursor:pointer;" title="Hapus Filter Status"></i>
                        </span>
                    `;
                }

                if (gudang) {
                    activeCount++;
                    chipsHtml += `
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size:0.75rem;">
                            <span>Gudang: <b>${escapeHtml(gudangText)}</b></span>
                            <i class="bi bi-x-circle-fill cursor-pointer text-info opacity-75 btn-remove-filter" data-target="#filter_gudang_id" style="cursor:pointer;" title="Hapus Filter Gudang"></i>
                        </span>
                    `;
                }

                if (tender) {
                    activeCount++;
                    chipsHtml += `
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size:0.75rem;">
                            <span>Tender: <b>${escapeHtml(tenderText)}</b></span>
                            <i class="bi bi-x-circle-fill cursor-pointer text-warning opacity-75 btn-remove-filter" data-target="#filter_tender_id" style="cursor:pointer;" title="Hapus Filter Tender"></i>
                        </span>
                    `;
                }

                if (startDate || endDate) {
                    activeCount++;
                    let dateLabel = (startDate && endDate) ? `${startDate} s/d ${endDate}` : (startDate ? `Dari: ${startDate}` : `Sampai: ${endDate}`);
                    chipsHtml += `
                        <span class="badge bg-secondary-subtle text-body-emphasis border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size:0.75rem;">
                            <span>Tgl: <b>${escapeHtml(dateLabel)}</b></span>
                            <i class="bi bi-x-circle-fill cursor-pointer text-secondary opacity-75 btn-remove-date-filter" style="cursor:pointer;" title="Hapus Filter Tanggal"></i>
                        </span>
                    `;
                }

                if (activeCount > 0) {
                    $('#badge_active_filter_count').text(activeCount).removeClass('d-none');
                    $('#btn_open_modal_filter').addClass('btn-primary-subtle border-primary text-primary').removeClass('btn-outline-secondary');
                    $('#active_filter_chips').html(chipsHtml);
                    $('#active_filters_container').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#badge_active_filter_count').text('0').addClass('d-none');
                    $('#btn_open_modal_filter').removeClass('btn-primary-subtle border-primary text-primary').addClass('btn-outline-secondary');
                    $('#active_filter_chips').html('');
                    $('#active_filters_container').removeClass('d-flex').addClass('d-none');
                }
            }

            // Terapkan Filter dari Modal
            $('#btn_modal_apply_filter').on('click', function() {
                updateActiveFilterUI();
                tableDO.ajax.reload();
                $('#modalFilterDO').modal('hide');
            });

            // Reset Filter dari Modal
            $('#btn_modal_reset_filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_gudang_id').val('');
                $('#filter_tender_id').val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                updateActiveFilterUI();
                tableDO.ajax.reload();
            });

            // Hapus single filter dari chip
            $(document).on('click', '.btn-remove-filter', function() {
                let target = $(this).data('target');
                $(target).val('');
                updateActiveFilterUI();
                tableDO.ajax.reload();
            });

            $(document).on('click', '.btn-remove-date-filter', function() {
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                updateActiveFilterUI();
                tableDO.ajax.reload();
            });

            // Tombol Hapus Semua Filter
            $('#btn_quick_clear_all_filters').on('click', function() {
                $('#filter_status').val('');
                $('#filter_gudang_id').val('');
                $('#filter_tender_id').val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                updateActiveFilterUI();
                tableDO.ajax.reload();
            });

            // Tombol Preset Tanggal di Modal
            $(document).on('click', '.btn-preset-date', function() {
                let preset = $(this).data('preset');
                let now = new Date();
                let formatDate = (d) => {
                    let month = '' + (d.getMonth() + 1),
                        day = '' + d.getDate(),
                        year = d.getFullYear();
                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;
                    return [year, month, day].join('-');
                };

                if (preset === 'today') {
                    let t = formatDate(now);
                    $('#filter_start_date').val(t);
                    $('#filter_end_date').val(t);
                } else if (preset === 'last7') {
                    let d = new Date();
                    d.setDate(d.getDate() - 7);
                    $('#filter_start_date').val(formatDate(d));
                    $('#filter_end_date').val(formatDate(now));
                } else if (preset === 'thismonth') {
                    let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    $('#filter_start_date').val(formatDate(firstDay));
                    $('#filter_end_date').val(formatDate(lastDay));
                } else if (preset === 'clear') {
                    $('#filter_start_date').val('');
                    $('#filter_end_date').val('');
                }
            });

            // Inisialisasi Select2 Searchable Dropdown untuk SO (Remote AJAX Search dengan Debounce 300ms)
            function initSoSelect2() {
                if ($.fn.select2) {
                    if ($('#do_select_so').hasClass('select2-hidden-accessible')) {
                        $('#do_select_so').select2('destroy');
                    }
                    $('#do_select_so').empty().append('<option value=""></option>');
                    $('#do_select_so').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalCreateDO'),
                        placeholder: '-- Cari Nomor SO atau Nama Customer / Instansi --',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: "{{ route('marketing.delivery-order.available-sos') }}",
                            dataType: 'json',
                            delay: 300,
                            data: function (params) {
                                return {
                                    q: params.term || ''
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: (data.orders || []).map(function(o) {
                                        let cust = o.customer ? (o.customer.nama_perusahaan ? `${o.customer.nama_perusahaan} (${o.customer.nama_customer})` : o.customer.nama_customer) : (o.nama_customer_walkin || 'Pelanggan Umum');
                                        let tenderTag = o.tender_nomor ? ` [Tender: ${o.tender_nomor}]` : (o.pricing_tier === 'Tender' ? ' [Tier: Tender]' : '');
                                        return {
                                            id: o.id,
                                            text: `${o.nomor_so} — ${cust}${tenderTag} [${o.status}]`,
                                            tender_nomor: o.tender_nomor || null
                                        };
                                    })
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 0
                    });
                }
            }

            // Handler: Buka Modal Buat DO
            $('#btn_open_modal_create').on('click', function() {
                $('#formCreateDO')[0].reset();
                $('#mode_do_auto').prop('checked', true);
                $('#do_nomor_do').prop('readonly', true).val('').removeClass('is-invalid is-valid').attr('placeholder', '[Otomatis dari Sistem]');
                $('#do_nomor_badge').html('<i class="bi bi-shield-check text-success me-1"></i>AUTO');
                $('#do_nomor_feedback').empty();
                $('#do_nomor_hint').empty();

                $('#do_items_tbody').html('<tr><td colspan="8" class="text-center py-4 text-muted small fst-italic">Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.</td></tr>');
                $('#do_items_count_badge').text('0 Item');
                $('#do_so_tender_badge').hide();
                itemSerialsStore = {};
                itemSerialMeta = {};
                initSoSelect2();
            });

            // Toggle Mode Nomor DO: Otomatis vs Input Manual
            $('input[name="mode_nomor_do"]').on('change', function() {
                let mode = $(this).val();
                let inp = $('#do_nomor_do');
                let badge = $('#do_nomor_badge');
                let hint = $('#do_nomor_hint');
                let feedback = $('#do_nomor_feedback');

                feedback.empty();
                hint.empty();
                inp.removeClass('is-invalid is-valid');

                if (mode === 'manual') {
                    inp.prop('readonly', false).val('').attr('placeholder', 'Ketik No. DO (cth: DO/2609/001)').focus();
                    badge.html('<i class="bi bi-pencil me-1"></i>MANUAL');
                } else {
                    inp.prop('readonly', true).val('').attr('placeholder', '[Otomatis dari Sistem]');
                    badge.html('<i class="bi bi-shield-check text-success me-1"></i>AUTO');
                }
            });

            // Live Duplicate Check untuk Nomor DO Manual
            let checkNomorTimeout = null;
            $('#do_nomor_do').on('input blur', function() {
                let mode = $('input[name="mode_nomor_do"]:checked').val();
                if (mode !== 'manual') return;

                let val = $(this).val().trim();
                let inp = $(this);
                let feedback = $('#do_nomor_feedback');

                clearTimeout(checkNomorTimeout);
                if (!val) {
                    feedback.empty();
                    inp.removeClass('is-invalid is-valid');
                    return;
                }

                checkNomorTimeout = setTimeout(function() {
                    $.get("{{ route('marketing.delivery-order.check-nomor') }}", { nomor_do: val }, function(res) {
                        if (res.available) {
                            inp.removeClass('is-invalid').addClass('is-valid');
                            feedback.removeClass('text-danger').addClass('text-success').html(`<i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(res.message)}`);
                        } else {
                            inp.removeClass('is-valid').addClass('is-invalid');
                            feedback.removeClass('text-success').addClass('text-danger').html(`<i class="bi bi-x-circle-fill me-1"></i>${escapeHtml(res.message)}`);
                        }
                    });
                }, 300);
            });

            // Trigger init & refresh layout when modal shown
            $('#modalCreateDO').on('shown.bs.modal', function() {
                initSoSelect2();
            });

            // Helper Render Badge Stok Gudang
            function renderStockBadge(stock, unit, qtyKirim, context = 'create') {
                let gudangId = (context === 'edit') ? $('#edit_do_gudang_id').val() : $('#do_gudang_id').val();
                if (!gudangId) {
                    return `<span class="badge bg-secondary-subtle text-muted font-monospace" style="font-size:0.75rem;"><i class="bi bi-question-circle me-1"></i>Pilih Gudang</span>`;
                }
                stock = Number(stock || 0);
                qtyKirim = Number(qtyKirim || 0);
                if (stock <= 0) {
                    return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1" style="font-size:0.75rem;"><i class="bi bi-x-circle-fill me-1"></i>Habis (0 ${escapeHtml(unit)})</span>`;
                } else if (stock < qtyKirim) {
                    return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-1" style="font-size:0.75rem;" title="Stok gudang tidak cukup untuk jumlah kirim saat ini"><i class="bi bi-exclamation-triangle-fill me-1"></i>${stock} ${escapeHtml(unit)}</span>`;
                } else {
                    return `<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill me-1"></i>${stock} ${escapeHtml(unit)}</span>`;
                }
            }

            // Validasi baris stok & highlight jika kurang
            function validateStockRow(idx) {
                let meta = itemSerialMeta[idx];
                if (!meta || !meta.barangId) return;
                let gudangId = $('#do_gudang_id').val();
                let stock = Number(meta.gudangStock !== undefined ? meta.gudangStock : 0);
                let inp = $(`input[name="items[${idx}][qty_kirim]"]`);
                let qtyKirim = Number(inp.val()) || 0;
                let errBox = $(`#stock_err_${idx}`);
                let unit = meta.satuan || 'Unit';

                $(`#cell_stock_${idx}`).html(renderStockBadge(stock, unit, qtyKirim));

                if (gudangId && qtyKirim > 0 && stock < qtyKirim) {
                    inp.addClass('is-invalid border-danger text-danger');
                    errBox.html(`<div class="text-danger font-monospace small mt-1" style="font-size: 0.68rem;"><i class="bi bi-x-circle me-1"></i>Stok tidak cukup! (Tersedia: ${stock} ${escapeHtml(unit)})</div>`).show();
                } else {
                    inp.removeClass('is-invalid border-danger text-danger');
                    errBox.html('').hide();
                }
            }

            // Event: Pilih Sales Order -> Auto Load Details
            $('#do_select_so').on('change', function() {
                let orderId = $(this).val();
                if (!orderId) {
                    $('#do_nama_penerima').val('');
                    $('#do_telepon_penerima').val('');
                    $('#do_alamat_tujuan').val('');
                    $('#do_items_tbody').html('<tr><td colspan="9" class="text-center py-4 text-muted small fst-italic">Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.</td></tr>');
                    $('#do_items_count_badge').text('0 Item');
                    $('#do_so_tender_badge').hide();
                    itemSerialsStore = {};
                    itemSerialMeta = {};
                    return;
                }

                let gudangId = $('#do_gudang_id').val();

                $.get(`/marketing/delivery-order/so/${orderId}/details`, { gudang_id: gudangId }, function(res) {
                    if (res.success && res.order) {
                        let o = res.order;
                        let cust = o.customer;
                        
                        let namaPenerima = cust ? (cust.nama_perusahaan ? `${cust.nama_perusahaan} (Up: ${cust.nama_customer})` : cust.nama_customer) : (o.nama_customer_walkin || '');
                        $('#do_nama_penerima').val(namaPenerima);
                        
                        let teleponPenerima = cust ? (cust.telepon || cust.hp) : (o.telepon_customer_walkin || '');
                        $('#do_telepon_penerima').val(teleponPenerima);
                        
                        let alamatTujuan = o.alamat_pengiriman || (cust && cust.default_address ? cust.default_address.alamat_lengkap : '');
                        $('#do_alamat_tujuan').val(alamatTujuan);

                        // Tampilkan badge tender pada form pembuatan DO
                        if (o.tender && (o.tender.nomor_kontrak || o.tender.nama_tender)) {
                            let tNo = o.tender.nomor_kontrak || o.tender.nama_tender;
                            $('#do_so_tender_badge').html(`<i class="bi bi-briefcase me-1"></i>Tender: ${escapeHtml(tNo)}`).show();
                        } else if (o.pricing_tier === 'Tender') {
                            $('#do_so_tender_badge').html(`<i class="bi bi-briefcase me-1"></i>Tier Tender`).show();
                        } else {
                            $('#do_so_tender_badge').hide();
                        }

                        itemSerialsStore = {};
                        itemSerialMeta = {};

                        let tbodyHtml = '';
                        (o.details || []).forEach((d, idx) => {
                            let brgNama = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : 'Item');
                            let brgSku  = d.barang ? d.barang.kode_barang : '-';
                            let satuan  = d.nama_satuan || (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'Pcs');
                            let qtyOrder = Number(d.qty);
                            let qtyDelivered = Number(d.qty_delivered || 0);
                            let qtySisa = Number(d.qty_sisa !== undefined ? d.qty_sisa : qtyOrder);
                            let isFulfilled = qtySisa <= 0;

                            let isTrackingSn = Boolean(d.is_tracking_sn);
                            let availStockCount = Number(d.available_serials_count || 0);
                            let gudangStock = Number(d.stok_gudang !== undefined ? d.stok_gudang : 0);

                            itemSerialsStore[idx] = [];
                            itemSerialMeta[idx] = {
                                isRequired: isTrackingSn,
                                barangId: d.barang_id,
                                barangNama: brgNama,
                                barangSku: brgSku,
                                satuan: satuan,
                                availableStockCount: availStockCount,
                                gudangStock: gudangStock
                            };

                            let stockBadgeHtml = renderStockBadge(gudangStock, satuan, qtySisa);

                            tbodyHtml += `
                                <tr class="do-item-row ${isFulfilled ? 'opacity-75 bg-body-tertiary' : ''}" data-idx="${idx}">
                                    <td class="text-center font-monospace text-muted small">${idx + 1}</td>
                                    <td>
                                        <div class="fw-bold text-body-emphasis mb-1">${escapeHtml(brgNama)}</div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="badge font-monospace do-badge-sku">SKU: ${escapeHtml(brgSku)}</span>
                                            <span class="badge font-monospace do-badge-unit">${escapeHtml(satuan)}</span>
                                            ${isTrackingSn 
                                                ? '<span class="badge do-badge-sn-req font-monospace"><i class="bi bi-upc-scan me-1"></i>Wajib Serial</span>' 
                                                : '<span class="badge do-badge-nonsn font-monospace"><i class="bi bi-box-seam me-1"></i>Non-Serial</span>'}
                                            ${isFulfilled ? '<span class="badge do-badge-fulfilled font-monospace">Selesai</span>' : (qtyDelivered > 0 ? '<span class="badge do-badge-partial font-monospace">Sebagian</span>' : '')}
                                        </div>

                                        <input type="hidden" name="items[${idx}][serial_numbers]" id="input_serials_${idx}" value="[]">
                                        <input type="hidden" name="items[${idx}][sales_order_detail_id]" value="${d.id}">
                                        <input type="hidden" name="items[${idx}][barang_id]" value="${d.barang_id || ''}">
                                        <input type="hidden" name="items[${idx}][nama_barang_custom]" value="${d.nama_barang_custom || ''}">
                                        <input type="hidden" name="items[${idx}][qty_order]" value="${qtyOrder}">
                                        <input type="hidden" name="items[${idx}][nama_satuan]" value="${satuan}">
                                    </td>
                                    <td class="text-center">
                                        <span class="do-metric-badge-total font-monospace">${qtyOrder} ${satuan}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="${qtyDelivered > 0 ? 'do-metric-badge-delivered' : 'do-metric-badge-zero'} font-monospace">${qtyDelivered} ${satuan}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="${qtySisa > 0 ? 'do-metric-badge-remaining' : 'do-metric-badge-fulfilled'} font-monospace">${qtySisa} ${satuan}</span>
                                    </td>
                                    <td class="text-center" id="cell_stock_${idx}">
                                        ${stockBadgeHtml}
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" min="0" max="${qtySisa}" class="form-control do-form-control text-center font-monospace fw-bold text-primary inp-qty-kirim" name="items[${idx}][qty_kirim]" value="${qtySisa}" data-idx="${idx}" ${isFulfilled ? 'disabled readonly' : 'required'}>
                                            <span class="input-group-text do-input-icon font-monospace small fw-semibold">${satuan}</span>
                                        </div>
                                        <div id="stock_err_${idx}" style="display:none;"></div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control do-form-control form-control-sm" name="items[${idx}][catatan_item]" placeholder="Kondisi / Koli..." ${isFulfilled ? 'disabled' : ''}>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column align-items-center gap-1.5" id="sn_box_${idx}">
                                            <button type="button" class="btn btn-sm ${isTrackingSn ? 'do-btn-alloc-sn' : 'do-btn-opt-sn'} w-100 d-flex align-items-center justify-content-center gap-1.5 btn-open-sn-modal shadow-sm" 
                                                data-idx="${idx}" 
                                                data-barang-id="${d.barang_id || ''}" 
                                                data-barang-nama="${escapeHtml(brgNama)}" 
                                                data-barang-sku="${escapeHtml(brgSku)}"
                                                data-is-required="${isTrackingSn ? 1 : 0}"
                                                ${isFulfilled ? 'disabled' : ''}>
                                                <i class="bi bi-upc-scan fs-6"></i>
                                                <span>${isTrackingSn ? 'Alokasikan SN' : '+ Serial Number'}</span>
                                                <span class="badge rounded-pill do-sn-pill-counter font-monospace ms-0.5" id="sn_badge_count_${idx}">0</span>/<span id="sn_target_count_${idx}">${qtySisa}</span>
                                            </button>
                                            <div id="sn_status_text_${idx}" class="small font-monospace text-center">
                                                <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-dash-circle me-1"></i>Opsional</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        $('#do_items_tbody').html(tbodyHtml || '<tr><td colspan="9" class="text-center py-4 text-muted small">Tidak ada item dalam SO ini.</td></tr>');
                        $('#do_items_count_badge').text(`${(o.details || []).length} Item`);

                        // Validasi stok awal untuk setiap baris
                        (o.details || []).forEach((d, idx) => {
                            validateStockRow(idx);
                        });
                    }
                });
            });

            // Trigger saat Gudang Asal berubah -> refresh stok fisik & validasi stok tiap item
            $('#do_gudang_id').on('change', function() {
                let gudangId = $(this).val();
                let barangIds = [];
                Object.keys(itemSerialMeta).forEach(k => {
                    if (itemSerialMeta[k].barangId) {
                        barangIds.push(itemSerialMeta[k].barangId);
                    }
                });

                if (gudangId && barangIds.length > 0) {
                    $.post("{{ route('marketing.delivery-order.gudang-stocks') }}", {
                        _token: '{{ csrf_token() }}',
                        gudang_id: gudangId,
                        barang_ids: barangIds
                    }, function(res) {
                        if (res.success && res.stocks) {
                            Object.keys(itemSerialMeta).forEach(idx => {
                                let bId = itemSerialMeta[idx].barangId;
                                itemSerialMeta[idx].gudangStock = res.stocks[bId] !== undefined ? Number(res.stocks[bId]) : 0;
                                validateStockRow(idx);
                            });
                        }
                    }).fail(function() {
                        Object.keys(itemSerialMeta).forEach(idx => {
                            validateStockRow(idx);
                        });
                    });
                } else {
                    Object.keys(itemSerialMeta).forEach(idx => {
                        validateStockRow(idx);
                    });
                }
            });

            // Trigger saat Qty Kirim berubah -> sesuaikan target SN & validasi status
            $(document).on('input change', '.inp-qty-kirim', function() {
                let idx = $(this).data('idx');
                let newQty = Number($(this).val()) || 0;
                let maxAllowed = Number($(this).attr('max'));

                if (!isNaN(maxAllowed) && maxAllowed >= 0 && newQty > maxAllowed) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Melebihi Sisa Pesanan',
                        html: `Jumlah kirim tidak boleh melebihi sisa pesanan Sales Order!<br>Maksimal yang dapat dikirim saat ini adalah <b>${maxAllowed}</b> unit.`,
                        confirmButtonColor: '#2563eb'
                    });
                    newQty = maxAllowed;
                    $(this).val(maxAllowed);
                }

                $(`#sn_target_count_${idx}`).text(newQty);
                refreshSnRowDisplay(idx);
                validateStockRow(idx);
            });

            // Render ulang tampilan row SN (counter, chips, status)
            function refreshSnRowDisplay(idx) {
                let serials = itemSerialsStore[idx] || [];
                let meta = itemSerialMeta[idx] || {};
                let isRequired = meta.isRequired || false;
                let targetQty = Number($(`input[name="items[${idx}][qty_kirim]"]`).val()) || 0;

                $(`#sn_badge_count_${idx}`).text(serials.length);
                $(`#sn_target_count_${idx}`).text(targetQty);
                $(`#input_serials_${idx}`).val(JSON.stringify(serials));

                // Update text status alokasi serial number (bersifat opsional)
                let statusHtml = '';
                if (serials.length > 0) {
                    if (serials.length <= targetQty) {
                        statusHtml = `<span class="badge do-badge-fulfilled font-monospace" style="font-size:0.7rem;"><i class="bi bi-check2-circle me-1"></i>${serials.length} SN Teralokasi</span>`;
                    } else {
                        statusHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size:0.7rem;"><i class="bi bi-exclamation-circle me-1"></i>Kelebihan ${serials.length - targetQty} SN</span>`;
                    }
                } else {
                    statusHtml = `<span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-dash-circle me-1"></i>Tanpa SN</span>`;
                }
                $(`#sn_status_text_${idx}`).html(statusHtml);
            }

            // =========================================================================
            // MODAL KELOLA & ALOKASI SERIAL NUMBER
            // =========================================================================
            $(document).on('click', '.btn-open-sn-modal', function() {
                let isEditModal = $(this).closest('#modalEditDO').length > 0;
                activeSnContext = isEditModal ? 'edit' : 'create';

                currentSnIdx = $(this).data('idx');
                currentSnBarangId = $(this).data('barang-id');
                currentSnBarangNama = $(this).data('barang-nama');
                currentSnBarangSku = $(this).data('barang-sku');
                currentSnIsRequired = $(this).data('is-required') == 1;

                if (isEditModal) {
                    currentSnTargetQty = Number($(`#table_edit_do_items input[name="items[${currentSnIdx}][qty_kirim]"]`).val()) || 0;
                    modalWorkingSerials = [...(editItemSerialsStore[currentSnIdx] || [])];
                } else {
                    currentSnTargetQty = Number($(`input[name="items[${currentSnIdx}][qty_kirim]"]`).val()) || 0;
                    modalWorkingSerials = [...(itemSerialsStore[currentSnIdx] || [])];
                }

                $('#modal_sn_barang_nama').text(currentSnBarangNama);
                $('#modal_sn_barang_sku').text(`SKU: ${currentSnBarangSku}`);
                $('#modal_sn_search_stock').val('');
                $('#modal_sn_quick_scan_input').val('');
                $('#modal_sn_bulk_paste_input').val('');

                refreshModalSnUI();
                loadStockSerialsForModal();

                $('#modalSelectSerials').modal('show');
            });

            function refreshModalSnUI() {
                let count = modalWorkingSerials.length;
                let target = currentSnTargetQty;

                $('#modal_sn_allocated_num').text(count);
                $('#modal_sn_target_num').text(target);
                $('#modal_sn_active_tags_count').text(`${count} SN`);

                // Status indicator di header card
                let indHtml = '';
                if (count === 0) {
                    indHtml = `<span class="badge bg-secondary-subtle text-muted border border-secondary-subtle font-monospace px-2.5 py-1"><i class="bi bi-dash-circle me-1"></i>Tanpa SN (Opsional)</span>`;
                } else if (count <= target) {
                    indHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i>${count} SN Terpilih</span>`;
                } else {
                    indHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2.5 py-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Kelebihan ${count - target} SN</span>`;
                }
                $('#modal_sn_status_indicator').html(indHtml);

                // Render selected chips box
                if (modalWorkingSerials.length === 0) {
                    $('#modal_sn_selected_chips_box').html('<span class="text-muted small fst-italic" id="modal_sn_empty_selected_hint">Belum ada serial number yang dipilih.</span>');
                } else {
                    let chipsHtml = '';
                    modalWorkingSerials.forEach((sn) => {
                        chipsHtml += `
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-2" style="font-size:0.8rem;">
                                <i class="bi bi-upc-scan"></i> ${escapeHtml(sn)}
                                <button type="button" class="btn-close btn-close-xs btn-modal-remove-chip" data-sn="${escapeHtml(sn)}" style="width:0.5rem; height:0.5rem;" title="Hapus"></button>
                            </span>
                        `;
                    });
                    $('#modal_sn_selected_chips_box').html(chipsHtml);
                }

                // Sinkronisasi status checkbox di Tab 1
                $('.stock-sn-checkbox').each(function() {
                    let snVal = $(this).val();
                    let isChecked = modalWorkingSerials.includes(snVal);
                    $(this).prop('checked', isChecked);
                    let parentLabel = $(this).closest('label');
                    if (isChecked) {
                        parentLabel.addClass('is-selected border-primary');
                    } else {
                        parentLabel.removeClass('is-selected border-primary');
                    }
                });
            }

            function loadStockSerialsForModal() {
                $('#modal_sn_stock_list_container').html(`
                    <div class="text-center py-4 text-muted small fst-italic">
                        <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div> Memuat serial number dari gudang...
                    </div>
                `);

                let gudangId = (activeSnContext === 'edit') ? $('#edit_do_gudang_id').val() : $('#do_gudang_id').val();
                $.get("{{ route('marketing.delivery-order.available-serials') }}", {
                    barang_id: currentSnBarangId,
                    gudang_id: gudangId
                }, function(res) {
                    if (res.success && res.serials && res.serials.length > 0) {
                        currentAvailableStockSerials = res.serials;
                        $('#modal_sn_stock_available_count').text(res.serials.length);
                        renderStockList(res.serials);
                        enableManualInputTab(true);
                    } else {
                        currentAvailableStockSerials = [];
                        $('#modal_sn_stock_available_count').text(0);
                        $('#modal_sn_stock_list_container').html('<div class="text-center py-4 text-muted small"><i class="bi bi-box-seam fs-3 d-block text-secondary mb-2"></i>Tidak ada serial number berstatus Tersedia di gudang ini untuk produk ini.</div>');
                        enableManualInputTab(false);
                    }
                }).fail(function() {
                    currentAvailableStockSerials = [];
                    $('#modal_sn_stock_available_count').text(0);
                    $('#modal_sn_stock_list_container').html('<div class="text-center py-4 text-danger small">Gagal memuat serial number dari server.</div>');
                    enableManualInputTab(false);
                });
            }

            function enableManualInputTab(isEnabled) {
                if (isEnabled) {
                    $('#modal_sn_manual_alert_container').html(`
                        <div class="alert alert-primary-subtle border-primary-subtle d-flex align-items-center gap-2.5 py-2 px-3 rounded-3 mb-2.5" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-check fs-6 text-primary flex-shrink-0"></i>
                            <div>
                                <span class="fw-bold text-primary-emphasis">Pengecekan Kode Barang &amp; Serial Aktif:</span>
                                Seluruh nomor seri yang di-scan / diketik wajib terdaftar pada kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}) dan berstatus <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">Tersedia</span> di gudang.
                            </div>
                        </div>
                    `);
                    $('#modal_sn_quick_scan_input').prop('disabled', false).attr('placeholder', 'Scan barcode fisik unit atau ketik serial lalu tekan Enter...');
                    $('#btn_modal_sn_add_single').prop('disabled', false);
                    $('#modal_sn_bulk_paste_input').prop('disabled', false).attr('placeholder', 'SN-001\nSN-002\nSN-003');
                    $('#btn_modal_sn_apply_bulk').prop('disabled', false);
                } else {
                    $('#modal_sn_manual_alert_container').html(`
                        <div class="alert alert-danger-subtle border-danger-subtle d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 mb-2.5" style="font-size: 0.78rem;">
                            <i class="bi bi-x-octagon-fill fs-5 text-danger flex-shrink-0"></i>
                            <div>
                                <span class="fw-bold text-danger-emphasis">Produk Tidak Memiliki Serial Number:</span>
                                Kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}) <u>tidak memiliki nomor seri</u> yang terdaftar di stok gudang. Input nomor seri dinonaktifkan.
                            </div>
                        </div>
                    `);
                    $('#modal_sn_quick_scan_input').prop('disabled', true).val('').attr('placeholder', 'Produk ini tidak memiliki serial number terdaftar di gudang...');
                    $('#btn_modal_sn_add_single').prop('disabled', true);
                    $('#modal_sn_bulk_paste_input').prop('disabled', true).val('').attr('placeholder', 'Produk ini tidak memiliki serial number terdaftar di gudang...');
                    $('#btn_modal_sn_apply_bulk').prop('disabled', true);
                }
            }

            function renderStockList(list) {
                if (!list || list.length === 0) {
                    $('#modal_sn_stock_list_container').html('<div class="text-center py-4 text-muted small">Tidak ada serial number berstatus Tersedia di gudang ini.</div>');
                    return;
                }

                let search = ($('#modal_sn_search_stock').val() || '').toLowerCase().trim();
                let filtered = list.filter(item => {
                    if (!search) return true;
                    return (item.serial_number && item.serial_number.toLowerCase().includes(search)) ||
                           (item.nomor_batch && item.nomor_batch.toLowerCase().includes(search)) ||
                           (item.gudang && item.gudang.toLowerCase().includes(search));
                });

                if (filtered.length === 0) {
                    $('#modal_sn_stock_list_container').html('<div class="text-center py-4 text-muted small">Tidak ada serial number yang cocok dengan pencarian.</div>');
                    return;
                }

                let html = '<div class="d-flex flex-column gap-2.5">';
                filtered.forEach(item => {
                    let isChecked = modalWorkingSerials.includes(item.serial_number);
                    html += `
                        <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between stock-sn-item ${isChecked ? 'is-selected border-primary' : ''}">
                            <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                <div class="stock-sn-checkbox-wrapper">
                                    <input class="form-check-input stock-sn-checkbox" type="checkbox" value="${escapeHtml(item.serial_number)}" ${isChecked ? 'checked' : ''}>
                                </div>
                                <div class="flex-grow-1 text-truncate pe-2">
                                    <div class="fw-bold font-monospace text-body-emphasis" style="font-size: 0.92rem; letter-spacing: 0.4px;">${escapeHtml(item.serial_number)}</div>
                                    <div class="text-muted mt-1 d-flex align-items-center flex-wrap gap-2" style="font-size: 0.74rem;">
                                        <span><span class="text-secondary fw-semibold">Batch:</span> <span class="font-monospace text-body-secondary fw-medium">${escapeHtml(item.nomor_batch || '-')}</span></span>
                                        <span class="text-secondary">&bull;</span>
                                        <span class="text-primary-emphasis"><i class="bi bi-geo-alt me-0.5"></i>${escapeHtml(item.gudang)}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1.5 ms-2 flex-shrink-0" style="font-size: 0.72rem;">
                                <i class="bi bi-check2 me-1"></i>Tersedia
                            </span>
                        </label>
                    `;
                });
                html += '</div>';

                $('#modal_sn_stock_list_container').html(html);
            }

            // Search filter di Tab 1
            $(document).on('input', '#modal_sn_search_stock', function() {
                renderStockList(currentAvailableStockSerials);
            });

            // Checkbox klik di Tab 1
            $(document).on('change', '.stock-sn-checkbox', function() {
                let sn = $(this).val();
                if ($(this).is(':checked')) {
                    if (!modalWorkingSerials.includes(sn)) {
                        modalWorkingSerials.push(sn);
                    }
                } else {
                    modalWorkingSerials = modalWorkingSerials.filter(s => s !== sn);
                }
                refreshModalSnUI();
            });

            // Auto-select sesuai Target Qty
            $('#btn_modal_sn_auto_select').on('click', function() {
                let needed = currentSnTargetQty;
                if (needed <= 0) {
                    Swal.fire({ icon: 'info', title: 'Perhatian', text: 'Jumlah kirim saat ini adalah 0 unit.' });
                    return;
                }
                let picked = [];
                for (let i = 0; i < currentAvailableStockSerials.length && picked.length < needed; i++) {
                    picked.push(currentAvailableStockSerials[i].serial_number);
                }
                modalWorkingSerials = picked;
                refreshModalSnUI();
            });

            // Reset pilihan di modal
            $('#btn_modal_sn_clear_selection').on('click', function() {
                modalWorkingSerials = [];
                refreshModalSnUI();
            });

            // Audio Feedback Helper (Web Audio API)
            function playScannerBeep() {
                try {
                    let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    let osc = audioCtx.createOscillator();
                    let gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(1600, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.08);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.08);
                } catch(e) {}
            }

            function playWarningBeep() {
                try {
                    let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    let osc = audioCtx.createOscillator();
                    let gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(420, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.18);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.18);
                } catch(e) {}
            }

            // Fungsi Terpadu Tambah Serial (Input Keyboard / Scanner Eksternal / Kamera Live)
            function processSingleSerial(raw, source = 'manual') {
                raw = (raw || '').trim();
                if (!raw) return false;

                // Cek apakah barang memiliki stok serial terdaftar di gudang
                if (!currentAvailableStockSerials || currentAvailableStockSerials.length === 0) {
                    playWarningBeep();
                    if (source === 'camera') {
                        $('#modal_sn_scan_status_badge')
                            .removeClass('bg-primary-subtle text-primary bg-success text-white bg-warning text-dark')
                            .addClass('bg-danger text-white')
                            .html('<i class="bi bi-x-circle me-1"></i>Barang Ini Tidak Memiliki Stok SN!');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Bisa Menambahkan Serial',
                        html: `Kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}) tidak memiliki Serial Number yang terdaftar di stok gudang.<br><small class="text-muted mt-2 d-block">Hanya produk yang memiliki unit serial terdaftar di gudang yang dapat diinputkan nomor serinya.</small>`,
                        confirmButtonColor: '#2563eb'
                    });
                    return false;
                }

                // Cek apakah nomor seri yang diketik / di-scan terdaftar pada kode barang ini di gudang
                let matched = currentAvailableStockSerials.find(s => (s.serial_number || '').trim().toLowerCase() === raw.toLowerCase());
                if (!matched) {
                    playWarningBeep();
                    if (source === 'camera') {
                        $('#modal_sn_scan_status_badge')
                            .removeClass('bg-primary-subtle text-primary bg-success text-white bg-warning text-dark')
                            .addClass('bg-danger text-white')
                            .html(`<i class="bi bi-x-circle me-1"></i>SN "${escapeHtml(raw)}" Tidak Terdaftar!`);
                        
                        // Efek flash merah pada viewfinder kamera
                        $('.modal-sn-target-box').css('box-shadow', '0 0 35px 12px #ef4444');
                        setTimeout(() => {
                            $('.modal-sn-target-box').css('box-shadow', '0 0 0 9999px rgba(0, 0, 0, 0.45)');
                        }, 350);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Serial Number Tidak Cocok / Tidak Terdaftar',
                            html: `Serial Number <b>"${escapeHtml(raw)}"</b> tidak ditemukan pada stok gudang untuk kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}).<br><small class="text-muted mt-2 d-block">Sesuai SOP, seluruh nomor seri wajib terdaftar resmi dan berstatus <em>Tersedia</em> di gudang.</small>`,
                            confirmButtonColor: '#2563eb'
                        });
                    }
                    return false;
                }

                raw = matched.serial_number;

                if (modalWorkingSerials.includes(raw)) {
                    playWarningBeep();
                    if (source === 'camera') {
                        $('#modal_sn_scan_status_badge')
                            .removeClass('bg-primary-subtle text-primary bg-success text-white bg-danger text-white')
                            .addClass('bg-warning text-dark')
                            .html(`<i class="bi bi-exclamation-triangle me-1"></i>SN "${escapeHtml(raw)}" Sudah Terpilih`);
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Duplikasi', text: `Serial number "${raw}" sudah ada di dalam daftar terpilih!`, timer: 1500, showConfirmButton: false });
                    }
                    return false;
                }

                // Sukses
                playScannerBeep();
                modalWorkingSerials.push(raw);
                refreshModalSnUI();

                if (source === 'camera') {
                    $('#modal_sn_scan_status_badge')
                        .removeClass('bg-primary-subtle text-primary bg-danger text-white bg-warning text-dark')
                        .addClass('bg-success text-white')
                        .html(`<i class="bi bi-check2-circle me-1"></i>"${escapeHtml(raw)}" Berhasil Ditambahkan`);

                    // Efek flash hijau pada viewfinder kamera
                    $('.modal-sn-target-box').css('box-shadow', '0 0 35px 12px #22c55e');
                    setTimeout(() => {
                        $('.modal-sn-target-box').css('box-shadow', '0 0 0 9999px rgba(0, 0, 0, 0.45)');
                    }, 350);

                    if (currentSnTargetQty > 0 && modalWorkingSerials.length >= currentSnTargetQty) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `Target kuantiti (${currentSnTargetQty} SN) telah tercapai!`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                } else {
                    $('#modal_sn_quick_scan_input').val('').focus();
                }

                return true;
            }

            $('#btn_modal_sn_add_single').on('click', function() {
                processSingleSerial($('#modal_sn_quick_scan_input').val(), 'manual');
            });

            $('#modal_sn_quick_scan_input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    processSingleSerial($(this).val(), 'manual');
                }
            });

            // =========================================================================
            // LIVE CAMERA BARCODE & QR SCANNER (HTML5-QRCODE)
            // =========================================================================
            let modalSnCameraScanner = null;
            let isSnCameraActive = false;
            let snCameraScanCooldown = false;
            let snCameraSessionToken = 0;

            function stopSnCameraScanner() {
                isSnCameraActive = false;
                snCameraSessionToken++;

                if (modalSnCameraScanner) {
                    let scanner = modalSnCameraScanner;
                    modalSnCameraScanner = null;
                    try {
                        scanner.stop().then(() => {
                            try { scanner.clear(); } catch(e) {}
                        }).catch(() => {
                            try { scanner.clear(); } catch(e) {}
                        });
                    } catch(e) {
                        try { scanner.clear(); } catch(e) {}
                    }
                }

                $('#modal_sn_camera_scanner_container').addClass('d-none');
                $('#modal_sn_camera_reader').empty();
                $('#modal_sn_scanner_overlay').hide();
                $('#btn_modal_sn_toggle_camera')
                    .removeClass('btn-primary text-white')
                    .addClass('btn-outline-primary')
                    .html('<i class="bi bi-camera-video-fill text-primary"></i> <span class="d-none d-sm-inline">Scan Kamera</span>');
            }

            function startSnCameraScanner(selectedCameraId = null) {
                stopSnCameraScanner();

                if (typeof Html5Qrcode === 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Modul Scanner Belum Siap',
                        text: 'Pustaka Html5Qrcode belum selesai dimuat. Pastikan perangkat terhubung internet.',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                if (!currentAvailableStockSerials || currentAvailableStockSerials.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Bisa Menggunakan Scanner',
                        html: `Kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}) tidak memiliki serial number terdaftar di gudang.`,
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                isSnCameraActive = true;
                const currentSession = ++snCameraSessionToken;

                $('#modal_sn_camera_scanner_container').removeClass('d-none');
                $('#modal_sn_camera_reader').html(`
                    <div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <span class="small text-primary fw-semibold">Menginisialisasi Kamera...</span>
                    </div>
                `);
                $('#modal_sn_scanner_overlay').hide();
                $('#btn_modal_sn_toggle_camera')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-primary text-white')
                    .html('<i class="bi bi-camera-video-off-fill"></i> <span class="d-none d-sm-inline">Stop Kamera</span>');

                let allFormats = [];
                if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
                    allFormats = [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.DATA_MATRIX
                    ];
                }

                try {
                    modalSnCameraScanner = new Html5Qrcode("modal_sn_camera_reader", {
                        formatsToSupport: allFormats.length ? allFormats : undefined,
                        verbose: false
                    });
                } catch(e) {
                    console.error("Gagal instansiasi Html5Qrcode:", e);
                    return;
                }

                const qrSuccessCallback = (decodedText) => {
                    if (snCameraScanCooldown) return;
                    snCameraScanCooldown = true;
                    setTimeout(() => { snCameraScanCooldown = false; }, 900);

                    processSingleSerial(decodedText, 'camera');
                };

                const proceedStart = (cameraConfig) => {
                    if (!isSnCameraActive || currentSession !== snCameraSessionToken || !$('#modalSelectSerials').hasClass('show')) {
                        stopSnCameraScanner();
                        return;
                    }

                    const scanConfig = {
                        fps: 15,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            const w = Math.min(Math.floor(viewfinderWidth * 0.85), 320);
                            const h = Math.min(Math.floor(viewfinderHeight * 0.6), 160);
                            return { width: w, height: h };
                        }
                    };

                    modalSnCameraScanner.start(
                        cameraConfig,
                        scanConfig,
                        qrSuccessCallback,
                        (errorMsg) => {}
                    ).then(() => {
                        if (!isSnCameraActive || currentSession !== snCameraSessionToken || !$('#modalSelectSerials').hasClass('show')) {
                            stopSnCameraScanner();
                            return;
                        }
                        $('#modal_sn_scanner_overlay').fadeIn(150);
                        $('#modal_sn_scan_status_badge').text('Memindai...');
                    }).catch(err => {
                        if (!isSnCameraActive || currentSession !== snCameraSessionToken || !$('#modalSelectSerials').hasClass('show')) {
                            stopSnCameraScanner();
                            return;
                        }
                        console.error("Camera access failed:", err);
                        let cleanError = err ? err.toString() : 'Unknown Error';
                        $('#modal_sn_camera_reader').html(`
                            <div class="d-flex align-items-center justify-content-center text-white flex-column p-4 text-center" style="min-height: 220px; background: #131d2e;">
                                <i class="bi bi-exclamation-triangle-fill text-warning mb-2" style="font-size: 2rem;"></i>
                                <span class="fw-semibold">Gagal Mengakses Kamera</span>
                                <small class="text-muted mt-2">${cleanError}</small>
                                <small class="text-danger mt-1" style="font-size: 0.75rem;">Pastikan izin kamera browser aktif & koneksi aman (HTTPS / Localhost).</small>
                            </div>
                        `);
                    });
                };

                // Ambil daftar kamera jika belum
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        let select = $('#modal_sn_camera_select');
                        select.empty();
                        devices.forEach((cam, idx) => {
                            let label = cam.label || `Kamera ${idx + 1}`;
                            select.append(`<option value="${cam.id}">${label}</option>`);
                        });

                        if (selectedCameraId && devices.some(d => d.id === selectedCameraId)) {
                            select.val(selectedCameraId);
                            proceedStart({ deviceId: { exact: selectedCameraId } });
                        } else {
                            // Prioritaskan kamera belakang jika ada
                            let backCam = devices.find(d => /back|rear|environment|belakang/i.test(d.label));
                            if (backCam) {
                                select.val(backCam.id);
                                proceedStart({ deviceId: { exact: backCam.id } });
                            } else {
                                select.val(devices[0].id);
                                proceedStart({ facingMode: "environment" });
                            }
                        }
                    } else {
                        proceedStart({ facingMode: "environment" });
                    }
                }).catch(() => {
                    proceedStart({ facingMode: "environment" });
                });
            }

            $('#btn_modal_sn_toggle_camera').on('click', function() {
                if (isSnCameraActive) {
                    stopSnCameraScanner();
                } else {
                    startSnCameraScanner();
                }
            });

            $('#btn_modal_sn_stop_camera').on('click', function() {
                stopSnCameraScanner();
            });

            $('#modal_sn_camera_select').on('change', function() {
                let camId = $(this).val();
                if (camId) {
                    startSnCameraScanner(camId);
                }
            });

            // Stop kamera jika modal alokasi serial ditutup atau pengguna pindah tab
            $('#modalSelectSerials').on('hidden.bs.modal', function() {
                stopSnCameraScanner();
            });

            $('button[data-bs-target="#panel-stock-sn"]').on('shown.bs.tab', function() {
                stopSnCameraScanner();
            });

            // Bulk Paste
            $('#btn_modal_sn_apply_bulk').on('click', function() {
                let raw = $('#modal_sn_bulk_paste_input').val();
                if (!raw.trim()) return;

                if (!currentAvailableStockSerials || currentAvailableStockSerials.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Bisa Menambahkan Serial',
                        html: `Kode barang <b>${escapeHtml(currentSnBarangSku)}</b> (${escapeHtml(currentSnBarangNama)}) tidak memiliki Serial Number yang terdaftar di stok gudang.`,
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                let items = raw.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
                let validSerials = [];
                let invalidSerials = [];
                let duplicates = [];

                items.forEach(sn => {
                    let matched = currentAvailableStockSerials.find(s => (s.serial_number || '').trim().toLowerCase() === sn.toLowerCase());
                    if (matched) {
                        let canonicalSn = matched.serial_number;
                        if (modalWorkingSerials.includes(canonicalSn) || validSerials.includes(canonicalSn)) {
                            duplicates.push(canonicalSn);
                        } else {
                            validSerials.push(canonicalSn);
                        }
                    } else {
                        invalidSerials.push(sn);
                    }
                });

                validSerials.forEach(sn => {
                    modalWorkingSerials.push(sn);
                });

                $('#modal_sn_bulk_paste_input').val('');
                refreshModalSnUI();

                if (invalidSerials.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sebagian Serial Ditolak',
                        html: `Ditemukan <b>${invalidSerials.length}</b> nomor seri yang <b>tidak terdaftar</b> untuk kode barang <b>${escapeHtml(currentSnBarangSku)}</b> di stok gudang:<br><div class="bg-body-secondary p-2 rounded text-danger font-monospace small mt-2 text-start" style="max-height:100px; overflow-y:auto;">${invalidSerials.map(s => escapeHtml(s)).join(', ')}</div><br><small class="text-success fw-semibold">${validSerials.length} nomor seri valid berhasil ditambahkan.</small>`,
                        confirmButtonColor: '#2563eb'
                    });
                } else if (validSerials.length > 0) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: `${validSerials.length} serial number valid ditambahkan ke daftar.`, timer: 1500, showConfirmButton: false });
                }
            });

            // Hapus chip dari modal
            $(document).on('click', '.btn-modal-remove-chip', function() {
                let sn = $(this).data('sn');
                modalWorkingSerials = modalWorkingSerials.filter(s => s !== sn);
                refreshModalSnUI();
            });

            // Simpan / Terapkan dari Modal
            $('#btn_modal_sn_save').on('click', function() {
                if (modalWorkingSerials.length > currentSnTargetQty) {
                    Swal.fire({
                        title: 'Jumlah SN Melebihi Kirim',
                        text: `Jumlah serial number terpilih (${modalWorkingSerials.length}) melebihi target kirim (${currentSnTargetQty} unit). Mohon kurangi pilihan nomor seri.`,
                        icon: 'warning',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }
                commitModalSerials();
            });

            function commitModalSerials() {
                if (activeSnContext === 'edit') {
                    editItemSerialsStore[currentSnIdx] = [...modalWorkingSerials];
                    refreshEditSnRowDisplay(currentSnIdx);
                } else {
                    itemSerialsStore[currentSnIdx] = [...modalWorkingSerials];
                    refreshSnRowDisplay(currentSnIdx);
                }
                $('#modalSelectSerials').modal('hide');
            }

            // Submit Form Buat DO dengan Validasi Serial Number & Kecukupan Stok Gudang
            $('#formCreateDO').on('submit', function(e) {
                e.preventDefault();

                let modeDO = $('input[name="mode_nomor_do"]:checked').val();
                let nomorDO = $('#do_nomor_do').val().trim();
                if (modeDO === 'manual') {
                    if (!nomorDO) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nomor DO Manual Kosong',
                            text: 'Anda memilih mode Input Manual, silakan masukkan Nomor Surat Jalan (DO)!',
                            confirmButtonColor: '#2563eb'
                        });
                        $('#do_nomor_do').focus();
                        return;
                    }
                    if ($('#do_nomor_do').hasClass('is-invalid')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nomor DO Tidak Valid / Duplikat',
                            text: $('#do_nomor_feedback').text() || 'Nomor Surat Jalan ini sudah digunakan. Mohon gunakan nomor lain!',
                            confirmButtonColor: '#dc2626'
                        });
                        $('#do_nomor_do').focus();
                        return;
                    }
                }

                let gudangId = $('#do_gudang_id').val();
                if (!gudangId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gudang Belum Dipilih',
                        text: 'Silakan pilih Gudang Asal Pengeluaran Barang terlebih dahulu sebelum mengajukan pengiriman!',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                // Validasi kecukupan stok gudang dan kelengkapan Serial Number sebelum submit
                let hasValidationError = false;
                let errorMsg = '';

                $('#table_do_items tbody tr.do-item-row').each(function() {
                    let idx = $(this).data('idx');
                    let inp = $(this).find('.inp-qty-kirim');
                    let qtyKirim = Number(inp.val()) || 0;
                    let maxAllowed = Number(inp.attr('max'));
                    let meta = itemSerialMeta[idx];

                    if (!isNaN(maxAllowed) && maxAllowed >= 0 && qtyKirim > maxAllowed) {
                        hasValidationError = true;
                        errorMsg = `Jumlah kirim untuk produk "${meta ? meta.barangNama : 'item'}" (${qtyKirim}) melebihi sisa pesanan Sales Order (maksimal: ${maxAllowed}).`;
                        return false;
                    }

                    // Pengecekan Stok Fisik di Gudang Asal
                    if (meta && meta.barangId && qtyKirim > 0) {
                        let availStock = Number(meta.gudangStock !== undefined ? meta.gudangStock : 0);
                        if (qtyKirim > availStock) {
                            hasValidationError = true;
                            errorMsg = `Stok di gudang asal tidak mencukupi untuk "${meta.barangNama}"! Stok tersedia saat ini: ${availStock} ${meta.satuan || ''}, jumlah yang diajukan: ${qtyKirim} ${meta.satuan || ''}. Pengiriman tidak dapat diajukan jika stok kurang.`;
                            return false;
                        }
                    }

                    if (meta && qtyKirim > 0) {
                        let serials = itemSerialsStore[idx] || [];
                        if (serials.length > qtyKirim) {
                            hasValidationError = true;
                            errorMsg = `Jumlah Serial Number untuk "${meta.barangNama}" (${serials.length} SN) melebihi jumlah kirim (${qtyKirim} unit).`;
                            return false; // break loop
                        }
                    }
                });

                if (hasValidationError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Pengiriman Gagal',
                        html: errorMsg,
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                let btn = $('#btn_submit_do');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('marketing.delivery-order.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-send-check fs-6"></i> <span>Ajukan Pengiriman</span>');
                        if (res.success) {
                            $('#modalCreateDO').modal('hide');
                            tableDO.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-printer me-1"></i> Cetak Surat Jalan',
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#2563eb'
                            }).then((result) => {
                                if (result.isConfirmed && res.do_id) {
                                    window.open(`/marketing/delivery-order/${res.do_id}/print`, '_blank');
                                }
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-send-check fs-6"></i> <span>Ajukan Pengiriman</span>');
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    }
                });
            });

            // Action: ACC Pengiriman (Approval Atasan)
            $(document).on('click', '.btn-approve-do', function() {
                let id = $(this).data('id');
                let noDo = $(this).data('nomor') || 'Pengiriman';

                Swal.fire({
                    title: 'ACC Pengiriman?',
                    html: `Apakah Anda yakin ingin menyetujui (ACC) pengajuan Surat Jalan <b>${escapeHtml(noDo)}</b>?<br><small class="text-muted mt-2 d-block">Sistem akan otomatis mengirimkan notifikasi WhatsApp ke pembuat pengiriman bahwa pengiriman telah disetujui.</small>`,
                    icon: 'question',
                    input: 'text',
                    inputPlaceholder: 'Catatan persetujuan (opsional)...',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, ACC Pengiriman',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#10b981',
                    showLoaderOnConfirm: true,
                    preConfirm: (catatan) => {
                        return $.ajax({
                            url: `/marketing/delivery-order/${id}/approve`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                catatan_approval: catatan
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(error.responseJSON ? error.responseJSON.message : 'Gagal menyetujui pengiriman.');
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.success) {
                        $('#modalDetailDO').modal('hide');
                        tableDO.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengiriman Berhasil Di-ACC!',
                            text: result.value.message,
                            confirmButtonColor: '#10b981'
                        });
                    }
                });
            });

            // Action: Tolak Pengajuan Pengiriman
            $(document).on('click', '.btn-reject-do', function() {
                let id = $(this).data('id');
                let noDo = $(this).data('nomor') || 'Pengiriman';

                Swal.fire({
                    title: 'Tolak Pengajuan Pengiriman?',
                    html: `Tolak pengajuan Surat Jalan <b>${escapeHtml(noDo)}</b>?`,
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan penolakan di sini (wajib)...',
                    inputValidator: (value) => {
                        if (!value) return 'Alasan penolakan wajib diisi!';
                    },
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tolak Pengajuan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    showLoaderOnConfirm: true,
                    preConfirm: (alasan) => {
                        return $.ajax({
                            url: `/marketing/delivery-order/${id}/reject`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                alasan: alasan
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(error.responseJSON ? error.responseJSON.message : 'Gagal menolak pengajuan.');
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.success) {
                        $('#modalDetailDO').modal('hide');
                        tableDO.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'info',
                            title: 'Pengajuan Ditolak',
                            text: result.value.message,
                            confirmButtonColor: '#2563eb'
                        });
                    }
                });
            });

            // Action: Konfirmasi Barang Telah Sampai (Penyelesaian Pengiriman)
            $(document).on('click', '.btn-confirm-arrival', function() {
                let id = $(this).data('id');
                let noDo = $(this).data('nomor') || 'Pengiriman';

                Swal.fire({
                    title: 'Konfirmasi Barang Telah Sampai?',
                    html: `Konfirmasi bahwa barang untuk Surat Jalan <b>${escapeHtml(noDo)}</b> telah tiba di tujuan pelanggan?<br><small class="text-muted mt-2 d-block">Sistem akan menyelesaikan status pengiriman dan otomatis mengirimkan notifikasi WhatsApp ke pembuat pengiriman dan atasan approver.</small>`,
                    icon: 'question',
                    input: 'text',
                    inputPlaceholder: 'Catatan penerimaan / nama penerima fisik (opsional)...',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-box-seam-fill me-1"></i> Ya, Barang Telah Sampai',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    showLoaderOnConfirm: true,
                    preConfirm: (catatan) => {
                        return $.ajax({
                            url: `/marketing/delivery-order/${id}/confirm-arrival`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                catatan_penerimaan: catatan
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(error.responseJSON ? error.responseJSON.message : 'Gagal mengonfirmasi barang sampai.');
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.success) {
                        $('#modalDetailDO').modal('hide');
                        tableDO.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengiriman Selesai!',
                            text: result.value.message,
                            confirmButtonColor: '#2563eb'
                        });
                    }
                });
            });

            // Action: Buka Modal Detail DO
            $(document).on('click', '.btn-detail-do', function() {
                let id = $(this).data('id');
                showDetailDO(id);
            });

            // =========================================================================
            // MODAL EDIT SURAT JALAN (DELIVERY ORDER)
            // =========================================================================
            $(document).on('click', '.btn-edit-do', function() {
                let id = $(this).data('id');
                openEditDO(id);
            });

            $(document).on('click', '.btn-edit-do-from-detail', function() {
                let id = $(this).data('id');
                $('#modalDetailDO').modal('hide');
                openEditDO(id);
            });

            function openEditDO(doId) {
                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Mengambil data Surat Jalan dan ketersediaan stok...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.get(`/marketing/delivery-order/${doId}/edit`, function(res) {
                    Swal.close();
                    if (!res.success || !res.do) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Data',
                            text: res.message || 'Data Surat Jalan tidak ditemukan.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    let d = res.do;
                    let canEditItems = Boolean(res.can_edit_items);

                    $('#edit_do_id').val(d.id);
                    $('#edit_do_nomor_badge').text(d.nomor_do || 'DO-...');

                    let statusMap = {
                        'Draft': 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        'Menunggu Persetujuan': 'bg-warning-subtle text-warning border border-warning-subtle',
                        'Disetujui': 'bg-info-subtle text-info border border-info-subtle',
                        'Siap Dikirim': 'bg-info-subtle text-info border border-info-subtle',
                        'Dalam Perjalanan': 'bg-primary-subtle text-primary border border-primary-subtle',
                        'Terkirim': 'bg-success-subtle text-success border border-success-subtle',
                        'Dibatalkan': 'bg-danger-subtle text-danger border border-danger-subtle'
                    };
                    let stCls = statusMap[d.status] || 'bg-secondary-subtle text-secondary';
                    $('#edit_do_status_badge').attr('class', `badge rounded-pill font-monospace px-2.5 py-0.5 ${stCls}`).text(d.status);

                    let entitasText = 'PT PUSAT';
                    if (d.branch && d.branch.nama_cabang) {
                        entitasText = `Cabang: ${d.branch.nama_cabang}`;
                    } else if (d.company && d.company.nama_perusahaan) {
                        entitasText = d.company.nama_perusahaan;
                    }
                    $('#edit_do_entity_badge').text(entitasText);

                    // Notice Banner & Rejection alert handling
                    let isRejected = ['Dibatalkan', 'Ditolak'].includes(d.status);
                    if (isRejected) {
                        $('#edit_do_rejected_alert').removeClass('d-none');
                        let reasonText = d.catatan_approval ? d.catatan_approval : 'Tidak ada catatan alasan penolakan.';
                        $('#edit_do_rejected_reason').text(reasonText);
                        $('#modalEditDOTitle').text('Revisi Surat Jalan');
                        $('#btn_submit_edit_do').html('<i class="bi bi-send-check-fill fs-6"></i> <span>Simpan &amp; Ajukan Ulang</span>');
                    } else {
                        $('#edit_do_rejected_alert').addClass('d-none');
                        $('#modalEditDOTitle').text('Edit Surat Jalan');
                        $('#btn_submit_edit_do').html('<i class="bi bi-check-circle-fill fs-6"></i> <span>Simpan Perubahan</span>');
                    }

                    // Notice Banner & Field item locking
                    if (canEditItems) {
                        $('#edit_do_approved_alert').addClass('d-none');
                        $('#edit_do_gudang_id').prop('disabled', false);
                        $('#edit_do_gudang_required').show();
                    } else {
                        $('#edit_do_approved_alert').removeClass('d-none');
                        $('#edit_do_gudang_id').prop('disabled', true);
                        $('#edit_do_gudang_required').hide();
                    }

                    // Section 1 Header
                    $('#edit_do_nomor_do').val(d.nomor_do || '');
                    $('#edit_do_so_text').val(d.sales_order ? d.sales_order.nomor_so : 'Pengiriman Manual');

                    let cust = d.customer;
                    let custPreview = cust ? (cust.nama_perusahaan ? `${cust.nama_perusahaan} (${cust.nama_customer})` : cust.nama_customer) : (d.nama_penerima || '-');
                    $('#edit_do_customer_preview').text(custPreview);

                    let tenderNo = null;
                    if (d.sales_order && d.sales_order.tender) {
                        tenderNo = d.sales_order.tender.nomor_kontrak || d.sales_order.tender.nama_tender;
                    }
                    if (tenderNo) {
                        $('#edit_do_so_tender_badge').html(`<i class="bi bi-briefcase me-1"></i>Tender: ${escapeHtml(tenderNo)}`).show();
                    } else {
                        $('#edit_do_so_tender_badge').hide();
                    }

                    // Gudang options
                    let gudangOptionsHtml = '<option value="">-- Pilih Gudang Asal --</option>';
                    (res.gudangs || []).forEach(g => {
                        let isSel = (g.id == d.gudang_id) ? 'selected' : '';
                        gudangOptionsHtml += `<option value="${g.id}" ${isSel}>${escapeHtml(g.nama_gudang)} (${escapeHtml(g.kode_gudang || '')})</option>`;
                    });
                    $('#edit_do_gudang_id').html(gudangOptionsHtml);

                    // Section 2: Info Penerima & Logistik
                    $('#edit_do_nama_penerima').val(d.nama_penerima || '');
                    $('#edit_do_telepon_penerima').val(d.telepon_penerima || '');
                    $('#edit_do_alamat_tujuan').val(d.alamat_tujuan || '');

                    let tglKirimVal = '';
                    if (d.tanggal_kirim) {
                        tglKirimVal = String(d.tanggal_kirim).split('T')[0];
                    }
                    $('#edit_do_tanggal_kirim').val(tglKirimVal);
                    $('#edit_do_metode_pengiriman').val(d.metode_pengiriman || 'Kurir Internal');
                    $('#edit_do_nama_sopir').val(d.nama_sopir || d.nama_ekspedisi || '');
                    $('#edit_do_nomor_resi').val(d.nomor_resi || d.plat_nomor || '');
                    $('#edit_do_catatan_pengiriman').val(d.catatan_pengiriman || '');

                    // Section 3: Item Table
                    editItemSerialsStore = {};
                    editItemSerialMeta = {};

                    let tbodyHtml = '';
                    (res.items || []).forEach((it, idx) => {
                        let brgNama = it.nama_barang || 'Item';
                        let brgSku  = it.kode_barang || '-';
                        let satuan  = it.nama_satuan || 'Pcs';
                        let qtyOrder = Number(it.qty_order || 0);
                        let qtyKirim = Number(it.qty_kirim || 0);
                        let maxAllowed = Number(it.max_allowed !== undefined ? it.max_allowed : qtyKirim);
                        let isTrackingSn = it.tipe_pelacakan === 'Serial Number';

                        let serials = Array.isArray(it.serial_numbers) ? [...it.serial_numbers] : [];
                        editItemSerialsStore[idx] = serials;
                        editItemSerialMeta[idx] = {
                            isRequired: isTrackingSn,
                            barangId: it.barang_id,
                            barangNama: brgNama,
                            barangSku: brgSku,
                            satuan: satuan,
                            maxAllowed: maxAllowed,
                            gudangStock: Number(it.stok_gudang || 0)
                        };

                        let stockBadgeHtml = renderStockBadge(it.stok_gudang, satuan, qtyKirim, 'edit');

                        let serialStatusText = '<span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-dash-circle me-1"></i>Tanpa SN</span>';
                        if (serials.length > 0) {
                            if (serials.length <= qtyKirim) {
                                serialStatusText = `<span class="badge do-badge-fulfilled font-monospace" style="font-size:0.7rem;"><i class="bi bi-check2-circle me-1"></i>${serials.length} SN Teralokasi</span>`;
                            } else {
                                serialStatusText = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size:0.7rem;"><i class="bi bi-exclamation-circle me-1"></i>Kelebihan ${serials.length - qtyKirim} SN</span>`;
                            }
                        }

                        tbodyHtml += `
                            <tr class="do-item-row" data-idx="${idx}">
                                <td class="text-center font-monospace text-muted small">${idx + 1}</td>
                                <td>
                                    <div class="fw-bold text-body-emphasis mb-1">${escapeHtml(brgNama)}</div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge font-monospace do-badge-sku">SKU: ${escapeHtml(brgSku)}</span>
                                        <span class="badge font-monospace do-badge-unit">${escapeHtml(satuan)}</span>
                                        ${isTrackingSn 
                                            ? '<span class="badge do-badge-sn-req font-monospace"><i class="bi bi-upc-scan me-1"></i>Wajib Serial</span>' 
                                            : '<span class="badge do-badge-nonsn font-monospace"><i class="bi bi-box-seam me-1"></i>Non-Serial</span>'}
                                    </div>

                                    <input type="hidden" name="items[${idx}][serial_numbers]" id="edit_input_serials_${idx}" value='${JSON.stringify(serials)}'>
                                    <input type="hidden" name="items[${idx}][sales_order_detail_id]" value="${it.sales_order_detail_id || ''}">
                                    <input type="hidden" name="items[${idx}][barang_id]" value="${it.barang_id || ''}">
                                    <input type="hidden" name="items[${idx}][nama_barang_custom]" value="${escapeHtml(it.nama_barang || '')}">
                                    <input type="hidden" name="items[${idx}][qty_order]" value="${qtyOrder}">
                                    <input type="hidden" name="items[${idx}][nama_satuan]" value="${escapeHtml(satuan)}">
                                </td>
                                <td class="text-center">
                                    <span class="do-metric-badge-total font-monospace">${qtyOrder} ${escapeHtml(satuan)}</span>
                                </td>
                                <td class="text-center">
                                    <span class="do-metric-badge-remaining font-monospace">${maxAllowed} ${escapeHtml(satuan)}</span>
                                </td>
                                <td class="text-center" id="edit_cell_stock_${idx}">
                                    ${stockBadgeHtml}
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="any" min="0" max="${maxAllowed}" class="form-control do-form-control text-center font-monospace fw-bold text-primary edit-inp-qty-kirim" name="items[${idx}][qty_kirim]" value="${qtyKirim}" data-idx="${idx}" ${canEditItems ? 'required' : 'readonly'}>
                                        <span class="input-group-text do-input-icon font-monospace small fw-semibold">${escapeHtml(satuan)}</span>
                                    </div>
                                    <div id="edit_stock_err_${idx}" style="display:none;"></div>
                                </td>
                                <td>
                                    <input type="text" class="form-control do-form-control form-control-sm" name="items[${idx}][catatan_item]" value="${escapeHtml(it.catatan_item || '')}" placeholder="Kondisi / Koli..." ${canEditItems ? '' : 'readonly'}>
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-center gap-1.5" id="edit_sn_box_${idx}">
                                        <button type="button" class="btn btn-sm ${isTrackingSn ? 'do-btn-alloc-sn' : 'do-btn-opt-sn'} w-100 d-flex align-items-center justify-content-center gap-1.5 btn-open-sn-modal shadow-sm" 
                                            data-idx="${idx}" 
                                            data-barang-id="${it.barang_id || ''}" 
                                            data-barang-nama="${escapeHtml(brgNama)}" 
                                            data-barang-sku="${escapeHtml(brgSku)}"
                                            data-is-required="${isTrackingSn ? 1 : 0}"
                                            ${canEditItems ? '' : 'disabled'}>
                                            <i class="bi bi-upc-scan fs-6"></i>
                                            <span>${isTrackingSn ? 'Alokasikan SN' : '+ Serial Number'}</span>
                                            <span class="badge rounded-pill do-sn-pill-counter font-monospace ms-0.5" id="edit_sn_badge_count_${idx}">${serials.length}</span>/<span id="edit_sn_target_count_${idx}">${qtyKirim}</span>
                                        </button>
                                        <div id="edit_sn_status_text_${idx}" class="small font-monospace text-center">
                                            ${serialStatusText}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#edit_do_items_tbody').html(tbodyHtml || '<tr><td colspan="8" class="text-center py-4 text-muted small">Tidak ada item pengiriman.</td></tr>');
                    $('#edit_do_items_count_badge').text(`${(res.items || []).length} Item`);

                    if (canEditItems) {
                        (res.items || []).forEach((it, idx) => {
                            validateEditStockRow(idx);
                        });
                    }

                    $('#modalEditDO').modal('show');
                }).fail(function(xhr) {
                    Swal.close();
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi server untuk memuat data pengiriman.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data',
                        text: msg,
                        confirmButtonColor: '#dc2626'
                    });
                });
            }

            // Render ulang tampilan row SN pada Modal Edit
            function refreshEditSnRowDisplay(idx) {
                let serials = editItemSerialsStore[idx] || [];
                let meta = editItemSerialMeta[idx] || {};
                let targetQty = Number($(`#table_edit_do_items input[name="items[${idx}][qty_kirim]"]`).val()) || 0;

                $(`#edit_sn_badge_count_${idx}`).text(serials.length);
                $(`#edit_sn_target_count_${idx}`).text(targetQty);
                $(`#edit_input_serials_${idx}`).val(JSON.stringify(serials));

                let statusHtml = '';
                if (serials.length > 0) {
                    if (serials.length <= targetQty) {
                        statusHtml = `<span class="badge do-badge-fulfilled font-monospace" style="font-size:0.7rem;"><i class="bi bi-check2-circle me-1"></i>${serials.length} SN Teralokasi</span>`;
                    } else {
                        statusHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size:0.7rem;"><i class="bi bi-exclamation-circle me-1"></i>Kelebihan ${serials.length - targetQty} SN</span>`;
                    }
                } else {
                    statusHtml = `<span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-dash-circle me-1"></i>Tanpa SN</span>`;
                }
                $(`#edit_sn_status_text_${idx}`).html(statusHtml);
            }

            // Validasi baris stok & highlight jika kurang pada Modal Edit
            function validateEditStockRow(idx) {
                let meta = editItemSerialMeta[idx];
                if (!meta || !meta.barangId) return;
                let gudangId = $('#edit_do_gudang_id').val();
                let stock = Number(meta.gudangStock !== undefined ? meta.gudangStock : 0);
                let inp = $(`#table_edit_do_items input[name="items[${idx}][qty_kirim]"]`);
                let qtyKirim = Number(inp.val()) || 0;
                let errBox = $(`#edit_stock_err_${idx}`);
                let unit = meta.satuan || 'Unit';

                $(`#edit_cell_stock_${idx}`).html(renderStockBadge(stock, unit, qtyKirim, 'edit'));

                if (gudangId && qtyKirim > 0 && stock < qtyKirim) {
                    inp.addClass('is-invalid border-danger text-danger');
                    errBox.html(`<div class="text-danger font-monospace small mt-1" style="font-size: 0.68rem;"><i class="bi bi-x-circle me-1"></i>Stok tidak cukup! (Tersedia: ${stock} ${escapeHtml(unit)})</div>`).show();
                } else {
                    inp.removeClass('is-invalid border-danger text-danger');
                    errBox.html('').hide();
                }
            }

            // Trigger saat Qty Kirim berubah di Modal Edit -> sesuaikan target SN & validasi status
            $(document).on('input change', '.edit-inp-qty-kirim', function() {
                let idx = $(this).data('idx');
                let newQty = Number($(this).val()) || 0;
                let maxAllowed = Number($(this).attr('max'));

                if (!isNaN(maxAllowed) && maxAllowed >= 0 && newQty > maxAllowed) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Melebihi Sisa Pesanan',
                        html: `Jumlah kirim tidak boleh melebihi sisa pesanan Sales Order!<br>Maksimal yang dapat dikirim saat ini adalah <b>${maxAllowed}</b> unit.`,
                        confirmButtonColor: '#2563eb'
                    });
                    newQty = maxAllowed;
                    $(this).val(maxAllowed);
                }

                $(`#edit_sn_target_count_${idx}`).text(newQty);
                refreshEditSnRowDisplay(idx);
                validateEditStockRow(idx);
            });

            // Trigger saat Gudang Asal berubah di Modal Edit -> refresh stok fisik & validasi stok tiap item
            $('#edit_do_gudang_id').on('change', function() {
                let gudangId = $(this).val();
                let barangIds = [];
                Object.keys(editItemSerialMeta).forEach(k => {
                    if (editItemSerialMeta[k].barangId) {
                        barangIds.push(editItemSerialMeta[k].barangId);
                    }
                });

                if (gudangId && barangIds.length > 0) {
                    $.post("{{ route('marketing.delivery-order.gudang-stocks') }}", {
                        _token: '{{ csrf_token() }}',
                        gudang_id: gudangId,
                        barang_ids: barangIds
                    }, function(res) {
                        if (res.success && res.stocks) {
                            Object.keys(editItemSerialMeta).forEach(idx => {
                                let bId = editItemSerialMeta[idx].barangId;
                                editItemSerialMeta[idx].gudangStock = res.stocks[bId] !== undefined ? Number(res.stocks[bId]) : 0;
                                validateEditStockRow(idx);
                            });
                        }
                    }).fail(function() {
                        Object.keys(editItemSerialMeta).forEach(idx => {
                            validateEditStockRow(idx);
                        });
                    });
                } else {
                    Object.keys(editItemSerialMeta).forEach(idx => {
                        validateEditStockRow(idx);
                    });
                }
            });

            // Submit Form Edit DO dengan Validasi Serial Number & Kecukupan Stok Gudang
            $('#formEditDO').on('submit', function(e) {
                e.preventDefault();

                let doId = $('#edit_do_id').val();
                if (!doId) return;

                let isItemsLocked = !$('#edit_do_approved_alert').hasClass('d-none');

                if (!isItemsLocked) {
                    let gudangId = $('#edit_do_gudang_id').val();
                    if (!gudangId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gudang Belum Dipilih',
                            text: 'Silakan pilih Gudang Asal Pengeluaran Barang terlebih dahulu sebelum menyimpan pengiriman!',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    // Validasi kecukupan stok gudang dan kelengkapan Serial Number sebelum submit
                    let hasValidationError = false;
                    let errorMsg = '';

                    $('#table_edit_do_items tbody tr.do-item-row').each(function() {
                        let idx = $(this).data('idx');
                        let inp = $(this).find('.edit-inp-qty-kirim');
                        let qtyKirim = Number(inp.val()) || 0;
                        let maxAllowed = Number(inp.attr('max'));
                        let meta = editItemSerialMeta[idx];

                        if (!isNaN(maxAllowed) && maxAllowed >= 0 && qtyKirim > maxAllowed) {
                            hasValidationError = true;
                            errorMsg = `Jumlah kirim untuk produk "${meta ? meta.barangNama : 'item'}" (${qtyKirim}) melebihi sisa pesanan Sales Order (maksimal: ${maxAllowed}).`;
                            return false;
                        }

                        // Pengecekan Stok Fisik di Gudang Asal
                        if (meta && meta.barangId && qtyKirim > 0) {
                            let availStock = Number(meta.gudangStock !== undefined ? meta.gudangStock : 0);
                            if (qtyKirim > availStock) {
                                hasValidationError = true;
                                errorMsg = `Stok di gudang asal tidak mencukupi untuk "${meta.barangNama}"! Stok tersedia saat ini: ${availStock} ${meta.satuan || ''}, jumlah yang diajukan: ${qtyKirim} ${meta.satuan || ''}. Pengiriman tidak dapat disimpan jika stok kurang.`;
                                return false;
                            }
                        }
                    });

                    if (hasValidationError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Pengiriman Gagal',
                            text: errorMsg,
                            confirmButtonColor: '#dc2626'
                        });
                        return;
                    }
                }

                let btnSubmit = $('#btn_submit_edit_do');
                let originalHtml = btnSubmit.html();
                btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...');

                // Pastikan jika gudang_id disabled, nilainya tetap dikirim jika dibutuhkan
                let formData = $(this).serializeArray();
                if ($('#edit_do_gudang_id').is(':disabled')) {
                    formData.push({ name: 'gudang_id', value: $('#edit_do_gudang_id').val() });
                }

                $.ajax({
                    url: `/marketing/delivery-order/${doId}`,
                    type: 'POST',
                    data: $.param(formData),
                    dataType: 'json'
                }).done(function(res) {
                    if (res.success) {
                        $('#modalEditDO').modal('hide');
                        tableDO.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diperbarui!',
                            text: res.message || 'Perubahan Surat Jalan berhasil disimpan.',
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: res.message || 'Terjadi kesalahan saat memperbarui Surat Jalan.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                }).fail(function(xhr) {
                    let msg = 'Terjadi kesalahan sistem saat menyimpan perubahan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        html: msg,
                        confirmButtonColor: '#dc2626'
                    });
                }).always(function() {
                    btnSubmit.prop('disabled', false).html(originalHtml);
                });
            });

        });

        // Function Global showDetailDO (Rincian Surat Jalan, Workflow Tracking & Serial Number)
        function showDetailDO(doId) {
            $.get(`/marketing/delivery-order/${doId}`, function(res) {
                if (!res.success || !res.do) return;
                let d = res.do;


                $('#detail_do_nomor').text(`Nomor DO: ${d.nomor_do}`);
                let tgl = d.tanggal_kirim ? new Date(d.tanggal_kirim).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) : '-';
                $('#detail_do_tanggal').html(`<i class="bi bi-calendar3 me-1"></i> Tgl Kirim: ${tgl}`);
                $('#detail_do_gudang').text(d.gudang ? d.gudang.nama_gudang : '-');

                let statusMap = {
                    'Draft': 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    'Menunggu Persetujuan': 'bg-warning-subtle text-warning border border-warning-subtle',
                    'Disetujui': 'bg-info-subtle text-info border border-info-subtle',
                    'Siap Dikirim': 'bg-info-subtle text-info border border-info-subtle',
                    'Dalam Perjalanan': 'bg-primary-subtle text-primary border border-primary-subtle',
                    'Terkirim': 'bg-success-subtle text-success border border-success-subtle',
                    'Dibatalkan': 'bg-danger-subtle text-danger border border-danger-subtle'
                };
                let stCls = statusMap[d.status] || 'bg-secondary-subtle text-secondary';
                $('#detail_do_status_badge').html(`<span class="badge ${stCls} px-3 py-1 rounded-pill fw-semibold font-monospace">${d.status}</span>`);

                // 1. Workflow: Diajukan Oleh (Pembuat)
                let creatorName = d.creator ? (d.creator.nama_lengkap || d.creator.username) : 'Staf';
                let tglCreated = d.created_at ? new Date(d.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';
                $('#detail_timeline_creator').text(creatorName);
                $('#detail_timeline_created_at').text(tglCreated);

                // 2. Workflow: Persetujuan (ACC Atasan)
                if (d.approved_at) {
                    let apprName = d.approver ? (d.approver.nama_lengkap || d.approver.username) : 'Atasan';
                    let tglAppr = new Date(d.approved_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                    let catAppr = d.catatan_approval ? `<div class="text-muted small fst-italic mt-1"><i class="bi bi-chat-left-quote me-1"></i>"${escapeHtml(d.catatan_approval)}"</div>` : '';
                    $('#detail_timeline_approver_box').html(`
                        <div class="fw-semibold text-success d-flex align-items-center gap-1.5">
                            <i class="bi bi-check-circle-fill"></i> ${escapeHtml(apprName)}
                        </div>
                        <div class="text-muted small">${tglAppr}</div>
                        ${catAppr}
                    `);
                } else if (d.status === 'Dibatalkan') {
                    let catTolak = d.catatan_approval ? `<div class="text-danger small fst-italic mt-1">${escapeHtml(d.catatan_approval)}</div>` : '';
                    $('#detail_timeline_approver_box').html(`
                        <div class="fw-semibold text-danger d-flex align-items-center gap-1.5">
                            <i class="bi bi-x-circle-fill"></i> Ditolak / Dibatalkan
                        </div>
                        ${catTolak}
                    `);
                } else {
                    $('#detail_timeline_approver_box').html(`
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-1 rounded-pill small">
                            <i class="bi bi-clock-history me-1"></i>Menunggu ACC Atasan
                        </span>
                    `);
                }

                // 3. Workflow: Konfirmasi Sampai (Selesai)
                if (d.received_at || d.status === 'Terkirim') {
                    let rcvrName = d.receiver ? (d.receiver.nama_lengkap || d.receiver.username) : (d.nama_penerima || 'Penerima');
                    let tglRcv = d.received_at ? new Date(d.received_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Selesai';
                    let catRcv = d.catatan_penerimaan ? `<div class="text-muted small fst-italic mt-1"><i class="bi bi-chat-left-quote me-1"></i>"${escapeHtml(d.catatan_penerimaan)}"</div>` : '';
                    $('#detail_timeline_receiver_box').html(`
                        <div class="fw-semibold text-success d-flex align-items-center gap-1.5">
                            <i class="bi bi-box-seam-fill"></i> ${escapeHtml(rcvrName)}
                        </div>
                        <div class="text-muted small">${tglRcv}</div>
                        ${catRcv}
                    `);
                } else {
                    $('#detail_timeline_receiver_box').html(`
                        <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2 py-1 rounded-pill small">
                            <i class="bi bi-hourglass-split me-1"></i>Dalam Proses Kirim
                        </span>
                    `);
                }

                $('#detail_do_nomor_so').text(d.sales_order ? d.sales_order.nomor_so : 'Manual / Tanpa SO');
                $('#detail_do_penerima').text(d.nama_penerima || (d.customer ? d.customer.nama_customer : '-'));
                $('#detail_do_telepon').text(d.telepon_penerima || (d.customer ? (d.customer.telepon || d.customer.hp) : '-'));
                $('#detail_do_alamat').text(d.alamat_tujuan || '-');

                $('#detail_do_metode').text(d.metode_pengiriman || 'Kurir Internal');
                $('#detail_do_ekspedisi').text(d.nama_ekspedisi || d.nama_sopir || '-');
                $('#detail_do_resi').text(d.nomor_resi || d.plat_nomor || '-');
                $('#detail_do_catatan').text(d.catatan_pengiriman || '-');

                let tbodyHtml = '';
                (d.details || []).forEach((item, idx) => {
                    let brgNama = item.nama_barang_custom || (item.barang ? item.barang.nama_barang : 'Item');
                    let brgSku  = item.barang ? item.barang.kode_barang : '-';
                    let satuan  = item.nama_satuan || (item.barang && item.barang.satuan ? item.barang.satuan.nama_satuan : 'Pcs');

                    let snHtml = '';
                    let snList = item.serial_numbers;
                    if (typeof snList === 'string') {
                        try { snList = JSON.parse(snList); } catch(e) { snList = []; }
                    }
                    if (Array.isArray(snList) && snList.length > 0) {
                        snHtml = `
                            <div class="mt-2 p-2 rounded-3 border bg-body small">
                                <div class="fw-bold text-primary mb-1 font-monospace d-flex align-items-center gap-1.5" style="font-size: 0.73rem;">
                                    <i class="bi bi-upc-scan"></i> Serial Number Unit (${snList.length} Unit):
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    ${snList.map(sn => `<span class="do-sn-chip"><i class="bi bi-upc-scan me-1"></i>${escapeHtml(sn)}</span>`).join('')}
                                </div>
                            </div>
                        `;
                    } else {
                        snHtml = `<div class="mt-1.5"><span class="badge do-badge-nonsn font-monospace"><i class="bi bi-box-seam me-1"></i>Non-Serial</span></div>`;
                    }

                    tbodyHtml += `
                        <tr>
                            <td class="text-center font-monospace text-muted small text-nowrap">${idx + 1}</td>
                            <td>
                                <div class="fw-bold text-body-emphasis mb-1" style="min-width: 220px;">${escapeHtml(brgNama)}</div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="badge font-monospace do-badge-sku">SKU: ${escapeHtml(brgSku)}</span>
                                    <span class="badge font-monospace do-badge-unit">${escapeHtml(satuan)}</span>
                                </div>
                                ${snHtml}
                            </td>
                            <td class="text-center font-monospace text-nowrap"><span class="do-metric-badge-total">${Number(item.qty_order)} ${escapeHtml(satuan)}</span></td>
                            <td class="text-center font-monospace fw-bold text-nowrap"><span class="do-metric-badge-delivered">${Number(item.qty_kirim)} ${escapeHtml(satuan)}</span></td>
                            <td class="small text-muted" style="min-width: 160px;">${escapeHtml(item.catatan_item || '-')}</td>
                        </tr>
                    `;
                });
                $('#detail_do_items_tbody').html(tbodyHtml || '<tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada data item.</td></tr>');

                // Tampilkan No. Tender jika ada dari SO / Tender
                let tenderNo = null;
                if (d.sales_order && d.sales_order.tender) {
                    tenderNo = d.sales_order.tender.nomor_kontrak || d.sales_order.tender.nama_tender;
                }
                if (tenderNo) {
                    $('#detail_do_tender_no').text(tenderNo);
                    $('#detail_do_tender_row').show();
                } else {
                    $('#detail_do_tender_row').hide();
                }

                // Tampilkan Tombol Jurnal Akuntansi jika DO memiliki jurnal terposting dan user memiliki hak akses lihat-jurnal-delivery-order
                let jurnal = d.jurnal;
                if (CAN_LIHAT_JURNAL && jurnal && jurnal.details && jurnal.details.length > 0) {
                    $('#btn_modal_lihat_jurnal').removeClass('d-none').addClass('d-inline-flex').show();
                    $('#btn_modal_lihat_jurnal').off('click').on('click', function() {
                        $('#modalDetailDO').modal('hide');
                        showModalJurnalDO(jurnal, d);
                    });
                } else {
                    $('#btn_modal_lihat_jurnal').addClass('d-none').removeClass('d-inline-flex').hide();
                }

                // Button Print
                $('#btn_print_do_modal').attr('href', `/marketing/delivery-order/${d.id}/print`);

                // Dynamic modal footer actions based on status and user permissions
                let actionsHtml = '';
                if (d.status === 'Menunggu Persetujuan') {
                    if (CAN_APPROVE_DO) {
                        actionsHtml = `
                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 py-1.5 fw-bold btn-approve-do" data-id="${d.id}" data-nomor="${escapeHtml(d.nomor_do)}">
                                <i class="bi bi-check2-circle me-1"></i> ACC Pengiriman
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-bold btn-reject-do" data-id="${d.id}" data-nomor="${escapeHtml(d.nomor_do)}">
                                <i class="bi bi-x-circle me-1"></i> Tolak
                            </button>
                        `;
                    }
                } else if (['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan'].includes(d.status)) {
                    if (CAN_COMPLETE_DO) {
                        actionsHtml = `
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold btn-confirm-arrival" data-id="${d.id}" data-nomor="${escapeHtml(d.nomor_do)}">
                                <i class="bi bi-box-seam-fill me-1"></i> Konfirmasi Barang Telah Sampai (Selesai)
                            </button>
                        `;
                    }
                } else if (['Dibatalkan', 'Ditolak'].includes(d.status)) {
                    if (CAN_EDIT_DO) {
                        actionsHtml = `
                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 fw-bold btn-edit-do-from-detail" data-id="${d.id}">
                                <i class="bi bi-arrow-repeat me-1"></i> Revisi Surat Jalan
                            </button>
                        `;
                    }
                }
                $('#detail_modal_actions').html(actionsHtml);

                $('#modalDetailDO').modal('show');
            });
        }

        /**
         * Render dan Tampilkan Modal Voucher Jurnal Akuntansi untuk Pengiriman
         */
        function showModalJurnalDO(jurnal, doData) {
            if (!CAN_LIHAT_JURNAL) {
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak',
                    text: 'Anda tidak memiliki hak akses untuk melihat Jurnal Akuntansi Pengiriman.',
                });
                return;
            }

            $('#do_jurnal_nomor').text(jurnal.nomor_jurnal || '-');
            
            let tglStr = '-';
            if (jurnal.tanggal_jurnal) {
                let raw = String(jurnal.tanggal_jurnal);
                if (raw.includes('T')) {
                    let d = new Date(raw);
                    tglStr = !isNaN(d.getTime()) 
                        ? d.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) 
                        : raw.split('T')[0];
                } else {
                    let p = raw.split('-');
                    tglStr = p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : raw;
                }
            }
            $('#do_jurnal_tanggal').text(tglStr);

            // Entitas: Bedakan PT Pusat vs Cabang
            let entitasText = 'PT PUSAT';
            let entitasBadgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            if (jurnal.branch_id && jurnal.branch) {
                entitasText = `Cabang: ${jurnal.branch.nama_cabang}`;
                entitasBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
            } else if (jurnal.company) {
                entitasText = jurnal.company.nama_perusahaan || 'PT PUSAT';
            }
            $('#do_jurnal_entitas_badge').html(`
                <span class="badge ${entitasBadgeClass} px-2.5 py-1 rounded-pill fw-bold d-inline-flex align-items-center mw-100" style="max-width: 100%; white-space: normal; text-align: left; line-height: 1.3;" title="${escapeHtml(entitasText)}">
                    <i class="bi bi-building me-1.5 flex-shrink-0"></i>
                    <span class="text-truncate">${escapeHtml(entitasText)}</span>
                </span>
            `);

            // Proyek / Tender Terkait
            let tenderInfo = jurnal.no_tender;
            if (!tenderInfo && doData && doData.sales_order) {
                let so = doData.sales_order;
                if (so.tender) {
                    tenderInfo = (so.tender.nomor_kontrak && so.tender.nomor_kontrak !== '-')
                        ? `${so.tender.nomor_kontrak} - ${so.tender.nama_tender}`
                        : (so.tender.nama_tender || `Tender #${so.tender.id}`);
                }
            }

            if (tenderInfo) {
                $('#do_jurnal_tender_row').show();
                $('#do_jurnal_tender_text').text(tenderInfo);
                $('#do_jurnal_tender_badge').html(`<i class="bi bi-briefcase me-1"></i>Tender: ${escapeHtml(tenderInfo)}`).show();
            } else {
                $('#do_jurnal_tender_row').hide();
                $('#do_jurnal_tender_badge').hide();
            }

            $('#do_jurnal_keterangan').text(jurnal.keterangan || '-');

            // Baris Rincian Debit & Kredit
            let rowsHtml = '';
            let totalDebit = 0;
            let totalKredit = 0;

            (jurnal.details || []).forEach((row, idx) => {
                let debit = parseFloat(row.debit) || 0;
                let kredit = parseFloat(row.kredit) || 0;
                totalDebit += debit;
                totalKredit += kredit;

                let coaCode = row.account ? row.account.code : '-';
                let coaName = row.account ? row.account.name : 'Akun Tidak Ditemukan';
                let isDebit = debit > 0;

                let debitFmt = debit > 0 ? 'Rp ' + Number(debit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
                let kreditFmt = kredit > 0 ? 'Rp ' + Number(kredit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';

                rowsHtml += `
                    <tr>
                        <td class="text-center font-monospace text-muted small text-nowrap">${idx + 1}</td>
                        <td style="${isDebit ? '' : 'padding-left: 28px !important;'}" class="text-nowrap">
                            <span class="do-jurnal-coa-badge me-2">${escapeHtml(coaCode)}</span>
                            <span class="${isDebit ? 'fw-semibold text-body-emphasis' : 'text-body-secondary'}">${!isDebit ? '<i class="bi bi-arrow-return-right text-muted opacity-75 me-1.5"></i>' : ''}${escapeHtml(coaName)}</span>
                        </td>
                        <td class="text-end font-monospace text-nowrap ${debit > 0 ? 'do-jurnal-debit-val fw-bold' : 'text-muted opacity-50'}">
                            ${debitFmt}
                        </td>
                        <td class="text-end font-monospace text-nowrap ${kredit > 0 ? 'do-jurnal-kredit-val fw-bold' : 'text-muted opacity-50'}">
                            ${kreditFmt}
                        </td>
                        <td class="do-jurnal-memo-text text-nowrap" style="min-width: 180px;" title="${escapeHtml(row.keterangan || '-')}">${escapeHtml(row.keterangan || '-')}</td>
                    </tr>
                `;
            });

            $('#do_jurnal_rows_container').html(rowsHtml || '<tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada baris akun.</td></tr>');
            $('#do_jurnal_total_debit').text('Rp ' + Number(totalDebit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#do_jurnal_total_kredit').text('Rp ' + Number(totalKredit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            // Validasi Keseimbangan (Balance Check)
            let diff = Math.abs(totalDebit - totalKredit);
            let isBalanced = diff < 0.01 && totalDebit > 0;
            if (isBalanced) {
                $('#do_jurnal_balance_container').html(`
                    <span class="badge do-jurnal-balance-badge font-monospace px-3 py-1">
                        <i class="bi bi-check2-circle me-1"></i>Balance
                    </span>
                `);
            } else {
                $('#do_jurnal_balance_container').html(`
                    <span class="badge do-jurnal-unbalance-badge font-monospace px-3 py-1" title="Selisih Rp ${Number(diff).toLocaleString('id-ID', {minimumFractionDigits: 2})}">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Unbalanced
                    </span>
                `);
            }

            $('#btn_print_do_jurnal').attr('href', `/accounting/jurnal-umum/${jurnal.id}/print`);

            $('#btn_back_to_do').off('click').on('click', function() {
                $('#modalJurnalDO').modal('hide');
                $('#modalDetailDO').modal('show');
            });

            $('#modalJurnalDO').modal('show');
        }
    </script>
    @endpush
</x-layouts>
