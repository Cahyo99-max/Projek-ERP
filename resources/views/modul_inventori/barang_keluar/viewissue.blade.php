<x-layouts title="Barang Keluar (Goods Issue)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('inventori/barang_keluar.css') }}?v={{ time() }}">

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- ─── Page Header ─────────────────────────────────────────── -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right text-danger me-1"></i>Barang Keluar (Goods Issue)
                </h4>
                <small class="text-muted">Pencatatan pengeluaran barang dari gudang</small>
            </div>
            @can('tambah-gi')
            <button class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahGI" id="btnTambahGI">
                <i class="bi bi-plus-lg me-1"></i> Buat Pengeluaran
            </button>
            @endcan
        </div>

        <!-- ─── Table Card ───────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 py-3.5 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-list-ul fs-5"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">
                        Daftar Pengeluaran Barang
                    </h6>
                </div>
                <span class="badge rounded-pill gi-count-badge">
                    <i class="bi bi-file-earmark-text me-1"></i>{{ number_format($issues->total(), 0, ',', '.') }} Dokumen
                </span>
            </div>
            <div class="card-body p-0" id="gi_card_body">
                @if($issues->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                        <p class="text-muted mb-1">Belum ada pengeluaran barang yang dibuat.</p>
                        @can('tambah-gi')
                        <small class="text-muted">Klik tombol <strong>"Buat Pengeluaran"</strong> untuk memulai.</small>
                        @endcan
                    </div>
                @else
                    <div class="gi-table-scroll-hint d-md-none">
                        <i class="bi bi-arrow-left-right"></i> Geser tabel ke samping untuk melihat detail & aksi
                    </div>
                    <div class="table-responsive table-responsive-gi">
                        <table class="table table-hover table-modern mb-0" id="tableGI">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="min-width: 170px;">Nomor GI</th>
                                    <th style="min-width: 120px;">Tanggal</th>
                                    <th style="min-width: 220px;">Tujuan / Penerima</th>
                                    <th style="min-width: 140px;">Tipe</th>
                                    <th style="min-width: 110px;" class="text-center">Status</th>
                                    <th style="min-width: 110px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($issues as $idx => $gi)
                                <tr onclick="showDetailGI({{ $gi->id }})" id="row-gi-{{ $gi->id }}">
                                    <td class="text-muted small text-center">{{ $issues->firstItem() + $idx }}</td>
                                    <td>
                                        <span class="fw-bold text-dark font-monospace" style="font-size:.85rem">{{ $gi->nomor_gi }}</span>
                                    </td>
                                    <td data-order="{{ $gi->tanggal_gi->format('Ymd') }}{{ str_pad($gi->id, 8, '0', STR_PAD_LEFT) }}">
                                        <span style="font-size:.84rem">{{ $gi->tanggal_gi->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark" style="font-size:.85rem">{{ $gi->tujuan }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $tipeClass = match($gi->tipe_pengeluaran) {
                                                'Internal'       => 'tipe-internal',
                                                'Retur Supplier' => 'tipe-retur',
                                                'Penyesuaian'    => 'tipe-penyesuaian',
                                                default          => 'tipe-lainnya',
                                            };
                                        @endphp
                                        <span class="tipe-badge {{ $tipeClass }}">{{ $gi->tipe_pengeluaran }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($gi->status === 'Draft')
                                            <span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>
                                        @elseif($gi->status === 'Pending')
                                            <span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>
                                        @else
                                            <span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
                                        @endif
                                    </td>
                                     <td onclick="event.stopPropagation()" class="text-center">
                                         @php
                                             $isSuperAdmin = auth()->check() && (
                                                 auth()->user()->hasRole('Super Admin') || 
                                                 auth()->user()->hasRole('Superadmin') || 
                                                 auth()->user()->hasRole('superadmin') ||
                                                 auth()->user()->hasRole('Owner') ||
                                                 (property_exists(auth()->user(), 'role') && strtolower(strval(auth()->user()->role)) === 'superadmin')
                                             );
                                             $isCreator = auth()->check() && ((int) $gi->created_by === (int) auth()->id());
                                         @endphp
                                         <div class="d-flex justify-content-center gap-1">
                                             <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5" onclick="showDetailGI({{ $gi->id }})" title="Lihat Detail & Persetujuan">
                                                 <i class="bi bi-eye"></i>
                                             </button>
                                             <a href="/goods-issue/{{ $gi->id }}/print" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Cetak PDF Dokumen" onclick="event.stopPropagation()">
                                                 <i class="bi bi-printer"></i>
                                             </a>
                                             {{-- Edit: Draft hanya pembuatnya, Pending semua yang punya hak edit, Confirmed hanya Super Admin --}}
                                             @if((($gi->status === 'Draft' && ($isCreator || $isSuperAdmin)) || $gi->status === 'Pending' || $isSuperAdmin) && (auth()->user()->can('edit-gi') || auth()->user()->can('tambah-gi')))
                                             <button class="btn btn-sm btn-outline-warning rounded-pill px-2.5 text-dark" onclick="openModalEditGI({{ $gi->id }})" title="{{ $gi->status === 'Confirmed' ? 'Edit Dokumen (Super Admin)' : ($gi->status === 'Pending' ? 'Edit Pengajuan' : 'Edit Draf') }}">
                                                 <i class="bi bi-pencil"></i>
                                             </button>
                                             @endif
                                             {{-- Hapus: Hanya Draft milik pembuatnya yang bisa dihapus (Pending terkunci; Confirmed hanya Super Admin) --}}
                                             @if((($gi->status === 'Draft' && ($isCreator || $isSuperAdmin)) || $isSuperAdmin) && auth()->user()->can('hapus-gi'))
                                             <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="hapusGI({{ $gi->id }}, '{{ $gi->nomor_gi }}')" title="Hapus Dokumen">
                                                 <i class="bi bi-trash3"></i>
                                             </button>
                                             @endif
                                         </div>
                                     </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($issues->hasPages())
            <div class="card-footer bg-transparent border-top py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $issues->firstItem() ?? 0 }}</strong> - <strong>{{ $issues->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($issues->total(), 0, ',', '.') }}</strong> Dokumen
                </div>
                <div>
                    {{ $issues->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- MODULAR PARTIALS INCLUSION                                 --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @include('modul_inventori.barang_keluar._modal_input')
    @include('modul_inventori.barang_keluar._modal_detail')
    @include('modul_inventori.barang_keluar._modal_edit')
    @include('modul_inventori.barang_keluar.lookup_gudang')
    @include('modul_inventori.barang_keluar.lookup_barang')
    @include('modul_inventori.barang_keluar.preview_foto')
    @include('modul_inventori.barang_keluar._modal_select_serials')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    window.barangsData = @json($barangsData);
    let isFetchingGI = false;

    function reloadTableGI(showLoading = true) {
        if (isFetchingGI) return;
        isFetchingGI = true;

        let spinner = null;
        if (showLoading) {
            let tableCard = $('#gi_card_body').closest('.card');
            tableCard.addClass('position-relative');
            spinner = $('<div class="table-loading-overlay d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75" style="z-index: 1000;"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            tableCard.append(spinner);
        }

        // Capture current search input and pagination state if DataTable exists
        let currentSearch = '';
        let currentPage = 0;
        if ($.fn.DataTable.isDataTable('#tableGI')) {
            let dt = $('#tableGI').DataTable();
            currentSearch = dt.search();
            currentPage = dt.page();
        }

        $.get(window.location.href, function (html) {
            let $response = $(html);
            let $newRows = $response.find('#tableGI tbody tr');
            let $currentRows = $('#tableGI tbody tr');

            // If structure or row count changed, update card body smoothly
            if ($newRows.length !== $currentRows.length || $('#tableGI tbody tr td').length <= 1) {
                let newTbodyHtml = $response.find('#tableGI tbody').html();
                let currentTbodyHtml = $('#tableGI tbody').html();
                if (newTbodyHtml !== currentTbodyHtml) {
                    if ($.fn.DataTable.isDataTable('#tableGI')) {
                        $('#tableGI').DataTable().destroy();
                    }
                    $('#gi_card_body').html($response.find('#gi_card_body').html());
                    
                    let newBadgeCount = $response.find('.gi-count-badge').text();
                    if (newBadgeCount) $('.gi-count-badge').text(newBadgeCount);

                    if ($('#tableGI').length && $('#tableGI tbody tr').length > 0 && !$('#tableGI tbody tr td').hasClass('dataTables_empty')) {
                        let newDt = $('#tableGI').DataTable(giDataTableConfig);
                        if (currentSearch) newDt.search(currentSearch).draw(false);
                        if (currentPage) newDt.page(currentPage).draw(false);
                    }
                }
            } else {
                // If row count is identical, update each row's status & actions in place without flickering / re-init
                $newRows.each(function () {
                    let rowId = $(this).attr('id');
                    if (rowId) {
                        let $currentRow = $('#' + rowId);
                        if ($currentRow.length) {
                            let newStatusHtml = $(this).find('td:nth-child(6)').html();
                            let curStatusHtml = $currentRow.find('td:nth-child(6)').html();
                            if (newStatusHtml && newStatusHtml.trim() !== curStatusHtml.trim()) {
                                $currentRow.find('td:nth-child(6)').html(newStatusHtml);
                            }

                            let newActionHtml = $(this).find('td:nth-child(7)').html();
                            let curActionHtml = $currentRow.find('td:nth-child(7)').html();
                            if (newActionHtml && newActionHtml.trim() !== curActionHtml.trim()) {
                                $currentRow.find('td:nth-child(7)').html(newActionHtml);
                            }
                        }
                    }
                });
            }
        }).always(function () {
            if (spinner) spinner.remove();
            isFetchingGI = false;
        });
    }

    const giDataTableConfig = {
        dom: '<"row mb-0"<"col-md-6 col-12"l><"col-md-6 col-12"f>>rt<"row mt-0"<"col-md-5 col-12"i><"col-md-7 col-12"p>>',
        order: [],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            infoFiltered: '(disaring dari _MAX_ total)',
            zeroRecords: 'Tidak ada data pengeluaran yang cocok',
            search: '',
            searchPlaceholder: 'Cari no. GI, penerima, tipe...',
            paginate: { 
                previous: '<i class="bi bi-chevron-left"></i>', 
                next: '<i class="bi bi-chevron-right"></i>' 
            }
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // HELPER: ESCAPE HTML
    // ══════════════════════════════════════════════════════════════════════════
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    window.escapeHtml = escapeHtml;

    // ══════════════════════════════════════════════════════════════════════════
    // SERIAL NUMBER (SN) MANAGEMENT LOGIC FOR GOODS ISSUE (BARANG KELUAR)
    // ══════════════════════════════════════════════════════════════════════════
    window.giItemSerialsStore = window.giItemSerialsStore || {};
    window.editGiItemSerialsStore = window.editGiItemSerialsStore || {};

    let currentGiSnIdx = null;
    let currentGiSnContext = 'input'; // 'input' or 'edit'
    let currentGiSnBarangId = null;
    let currentGiSnBarangNama = '';
    let currentGiSnBarangSku = '';
    let currentGiSnTargetQty = 0;
    let giModalWorkingSerials = [];
    let giAvailableStockSerials = [];

    // Camera Scanner State
    let giModalCameraScanner = null;
    let isGiCameraActive = false;
    let giCameraSessionToken = 0;
    let giCameraScanCooldown = false;

    // Helper: Update badge and chip preview on each item row
    function updateRowSnBadge(rowIdx, context, targetQty) {
        const isEdit = (context === 'edit');
        const store = isEdit ? window.editGiItemSerialsStore : window.giItemSerialsStore;
        const serials = (store && store[rowIdx]) ? store[rowIdx] : [];
        const count = serials.length;

        if (targetQty === undefined || targetQty === null) {
            const input = isEdit ? $(`#edit_gi_jumlah_${rowIdx}`) : $(`#jumlah_${rowIdx}`);
            targetQty = parseFloat(input.val()) || 0;
        }

        const prefix = isEdit ? 'edit_' : '';
        const badgeElem = $(`#${prefix}sn_badge_count_${rowIdx}`);
        const statusElem = $(`#${prefix}sn_status_text_${rowIdx}`);
        const btnElem = $(`#${prefix}btn_open_sn_${rowIdx}`);
        const chipsElem = $(`#${prefix}sn_chips_preview_${rowIdx}`);

        badgeElem.text(`${count} / ${targetQty} Unit`);
        badgeElem.removeClass('bg-secondary-subtle bg-success-subtle bg-warning-subtle bg-danger-subtle text-muted text-success text-dark text-danger border-success-subtle border-warning-subtle border-danger-subtle');

        if (count === 0) {
            badgeElem.addClass('bg-secondary-subtle text-muted');
            statusElem.html('<span class="text-muted"><i class="bi bi-dash-circle me-1"></i>Tanpa SN (Opsional)</span>');
            btnElem.html('<i class="bi bi-upc-scan me-1"></i> Pilih Serial Number');
        } else if (count === targetQty) {
            badgeElem.addClass('bg-success-subtle text-success border border-success-subtle');
            statusElem.html('<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Lengkap (${count} SN)</span>');
            btnElem.html(`<i class="bi bi-pencil-square me-1"></i> Ubah SN (${count})`);
        } else if (count < targetQty) {
            badgeElem.addClass('bg-warning-subtle text-dark border border-warning-subtle');
            statusElem.html(`<span class="text-warning-emphasis fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Sebagian (${count}/${targetQty} SN)</span>`);
            btnElem.html(`<i class="bi bi-pencil-square me-1"></i> Ubah SN (${count})`);
        } else {
            badgeElem.addClass('bg-danger-subtle text-danger border border-danger-subtle');
            statusElem.html(`<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Kelebihan (${count - targetQty} SN)</span>`);
            btnElem.html(`<i class="bi bi-exclamation-octagon me-1"></i> Kelebihan (${count})`);
        }

        // Render chips preview (first 4 items)
        if (count === 0) {
            chipsElem.empty();
        } else {
            let chipsHtml = '';
            serials.slice(0, 4).forEach(sn => {
                chipsHtml += `<span class="badge bg-light text-dark border font-monospace px-2 py-0.5 small" style="font-size:0.72rem;">${escapeHtml(sn)}</span>`;
            });
            if (count > 4) {
                chipsHtml += `<span class="badge bg-secondary-subtle text-muted border font-monospace px-1.5 py-0.5 small" style="font-size:0.7rem;">+${count - 4} lainnya</span>`;
            }
            chipsElem.html(chipsHtml);
        }
    }
    window.updateRowSnBadge = updateRowSnBadge;

    // Refresh Modal SN UI (Counter, Status, Chips, and Checkboxes)
    function refreshGiModalSnUI() {
        const count = giModalWorkingSerials.length;
        const target = currentGiSnTargetQty;

        $('#modal_gi_sn_allocated_num').text(count);
        $('#modal_gi_sn_target_num').text(target);
        $('#modal_gi_sn_active_tags_count').text(`${count} SN`);

        // Status indicator in header card
        let indHtml = '';
        if (count === 0) {
            indHtml = `<span class="badge bg-secondary-subtle text-muted border border-secondary-subtle font-monospace px-2.5 py-1"><i class="bi bi-dash-circle me-1"></i>Tanpa SN (Opsional)</span>`;
        } else if (count === target) {
            indHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i>Lengkap (${count} SN)</span>`;
        } else if (count < target) {
            indHtml = `<span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-2.5 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Kurang ${target - count} SN</span>`;
        } else {
            indHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2.5 py-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>Kelebihan ${count - target} SN</span>`;
        }
        $('#modal_gi_sn_status_indicator').html(indHtml);

        // Render selected chips box
        if (count === 0) {
            $('#modal_gi_sn_selected_chips_box').html(`
                <div class="text-muted small fst-italic d-flex align-items-center gap-2 py-2" id="modal_gi_sn_empty_selected_hint">
                    <i class="bi bi-info-circle text-muted"></i>
                    <span>Belum ada serial number yang dipilih. Centang dari stok di atas atau ketik/scan nomor seri unit.</span>
                </div>
            `);
        } else {
            let chipsHtml = '';
            giModalWorkingSerials.forEach((sn) => {
                chipsHtml += `
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-3 py-1.5 rounded-pill d-inline-flex align-items-center gap-2 shadow-xs" style="font-size:0.82rem;">
                        <i class="bi bi-upc-scan"></i> <span>${escapeHtml(sn)}</span>
                        <button type="button" class="btn-close btn-close-xs btn-modal-remove-gi-chip" data-sn="${escapeHtml(sn)}" style="width:0.5rem; height:0.5rem;" title="Hapus"></button>
                    </span>
                `;
            });
            $('#modal_gi_sn_selected_chips_box').html(chipsHtml);
        }

        // Sync stock checkboxes in Tab 1
        $('.gi-stock-sn-checkbox').each(function () {
            const snVal = $(this).val();
            const isChecked = giModalWorkingSerials.includes(snVal);
            $(this).prop('checked', isChecked);
            const parentLabel = $(this).closest('label');
            if (isChecked) {
                parentLabel.addClass('border-danger bg-danger-subtle bg-opacity-25');
            } else {
                parentLabel.removeClass('border-danger bg-danger-subtle bg-opacity-25');
            }
        });
    }
    window.refreshGiModalSnUI = refreshGiModalSnUI;

    // Load available stock serial numbers via AJAX
    function loadGiStockSerialsForModal() {
        $('#modal_gi_sn_stock_list_container').html(`
            <div class="text-center py-5 text-muted small fst-italic">
                <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div> Memuat serial number dari gudang...
            </div>
        `);

        const isEdit = (currentGiSnContext === 'edit');
        const gudangId = isEdit ? $('#edit_gi_gudang_id').val() : $('#gi_gudang_id').val();
        const giId = isEdit ? $('#edit_gi_id').val() : null;

        $.get("{{ route('gi.available-serials') }}", {
            barang_id: currentGiSnBarangId,
            gudang_id: gudangId,
            goods_issue_id: giId
        }, function (res) {
            if (res.success && res.serials && res.serials.length > 0) {
                giAvailableStockSerials = res.serials;
                $('#modal_gi_sn_stock_available_count').text(res.serials.length);
                renderGiStockList(res.serials);
                enableGiManualInputTab(true);
            } else {
                giAvailableStockSerials = [];
                $('#modal_gi_sn_stock_available_count').text(0);
                $('#modal_gi_sn_stock_list_container').html('<div class="text-center py-5 text-muted small"><i class="bi bi-box-seam fs-3 d-block text-secondary mb-2"></i>Tidak ada serial number berstatus Tersedia di gudang ini untuk produk ini.</div>');
                enableGiManualInputTab(false);
            }
        }).fail(function () {
            giAvailableStockSerials = [];
            $('#modal_gi_sn_stock_available_count').text(0);
            $('#modal_gi_sn_stock_list_container').html('<div class="text-center py-5 text-danger small">Gagal memuat serial number dari server.</div>');
            enableGiManualInputTab(false);
        });
    }
    window.loadGiStockSerialsForModal = loadGiStockSerialsForModal;

    // Render Stock List checkboxes in Tab 1
    function renderGiStockList(serials) {
        let html = '<div class="row g-3" id="gi_stock_serials_grid">';
        serials.forEach(s => {
            const isChecked = giModalWorkingSerials.includes(s.serial_number);
            const borderClass = isChecked ? 'border-danger bg-danger-subtle bg-opacity-25' : '';
            html += `
                <div class="col-12 col-md-6 gi-stock-sn-item" data-sn="${escapeHtml((s.serial_number || '').toLowerCase())}" data-batch="${escapeHtml((s.nomor_batch || '').toLowerCase())}">
                    <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white h-100 ${borderClass} gi-sn-card-label shadow-xs" style="cursor: pointer; transition: all 0.2s ease;">
                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                            <input type="checkbox" class="form-check-input gi-stock-sn-checkbox mt-0 flex-shrink-0" value="${escapeHtml(s.serial_number)}" ${isChecked ? 'checked' : ''} style="width: 1.15rem; height: 1.15rem;">
                            <div class="overflow-hidden">
                                <div class="font-monospace fw-bold text-dark text-truncate d-flex align-items-center gap-1.5" style="font-size:0.88rem;">
                                    <i class="bi bi-upc-scan text-danger"></i> <span>${escapeHtml(s.serial_number)}</span>
                                </div>
                                <div class="text-muted small font-monospace mt-1 d-flex align-items-center gap-2" style="font-size:0.75rem;">
                                    <span>Batch: <span class="badge bg-light text-dark border px-1.5 py-0.5">${escapeHtml(s.nomor_batch || '-')}</span></span>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 small flex-shrink-0" style="font-size:0.72rem;">
                            <i class="bi bi-check-circle me-1"></i>Tersedia
                        </span>
                    </label>
                </div>
            `;
        });
        html += '</div>';
        $('#modal_gi_sn_stock_list_container').html(html);
    }
    window.renderGiStockList = renderGiStockList;

    function enableGiManualInputTab(isEnabled) {
        $('#modal_gi_sn_quick_scan_input').prop('disabled', !isEnabled);
        $('#btn_modal_gi_sn_toggle_camera').prop('disabled', !isEnabled);
        $('#btn_modal_gi_sn_add_single').prop('disabled', !isEnabled);
        $('#modal_gi_sn_bulk_paste_input').prop('disabled', !isEnabled);
        $('#btn_modal_gi_sn_apply_bulk').prop('disabled', !isEnabled);
    }
    window.enableGiManualInputTab = enableGiManualInputTab;

    // Single Manual Scan / Enter
    function processSingleGiSerial(inputVal) {
        const cleanSn = $.trim(inputVal);
        if (!cleanSn) return;

        // Check if exists in available stock
        const existsInStock = giAvailableStockSerials.some(s => s.serial_number.toLowerCase() === cleanSn.toLowerCase());
        if (!existsInStock) {
            Swal.fire({
                icon: 'error',
                title: 'Serial Number Tidak Ditemukan',
                html: `Nomor seri <b>${escapeHtml(cleanSn)}</b> tidak terdaftar berstatus <i>Tersedia</i> di gudang ini untuk produk ini!`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        // Get exact registered casing
        const stockRecord = giAvailableStockSerials.find(s => s.serial_number.toLowerCase() === cleanSn.toLowerCase());
        const officialSn = stockRecord ? stockRecord.serial_number : cleanSn;

        if (giModalWorkingSerials.includes(officialSn)) {
            Swal.fire({
                icon: 'warning',
                title: 'Sudah Terpilih',
                html: `Nomor seri <b>${escapeHtml(officialSn)}</b> sudah ada dalam daftar pilihan!`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        if (giModalWorkingSerials.length >= currentGiSnTargetQty) {
            Swal.fire({
                icon: 'warning',
                title: 'Kapasitas Unit Penuh',
                html: `Jumlah serial number terpilih sudah mencapai target (${currentGiSnTargetQty} unit).`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        giModalWorkingSerials.push(officialSn);
        $('#modal_gi_sn_quick_scan_input').val('').focus();
        refreshGiModalSnUI();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `SN ${officialSn} berhasil ditambahkan!`,
            showConfirmButton: false,
            timer: 1200
        });
    }
    window.processSingleGiSerial = processSingleGiSerial;

    // Open Modal Select Serials GI function
    function openModalSelectSerialsGI(rowIdx, context = 'input') {
        const isEdit = (context === 'edit');

        const gudangId = isEdit ? $('#edit_gi_gudang_id').val() : $('#gi_gudang_id').val();
        if (!gudangId) {
            Swal.fire({
                icon: 'warning',
                title: 'Gudang Belum Dipilih',
                text: 'Silakan pilih gudang pengeluaran barang terlebih dahulu!',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        const barangId = isEdit ? $(`#edit_barang_id_${rowIdx}`).val() : $(`#barang_id_${rowIdx}`).val();
        const barangDisplay = isEdit ? $(`#edit_barang_display_${rowIdx}`).val() : $(`#barang_display_${rowIdx}`).val();
        if (!barangId) {
            Swal.fire({
                icon: 'warning',
                title: 'Barang Belum Dipilih',
                text: 'Silakan pilih barang pada baris ini sebelum mengalokasikan serial number!',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        const qtyInput = isEdit ? $(`#edit_gi_jumlah_${rowIdx}`) : $(`#jumlah_${rowIdx}`);
        const qtyVal = parseFloat(qtyInput.val());
        if (isNaN(qtyVal) || qtyVal <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Jumlah Tidak Valid',
                text: 'Silakan isi jumlah pengeluaran barang yang valid (> 0) terlebih dahulu!',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        currentGiSnIdx = rowIdx;
        currentGiSnContext = context;
        currentGiSnBarangId = barangId;
        currentGiSnTargetQty = qtyVal;

        // Parse SKU and name from display
        let parts = (barangDisplay || '').split(' - ');
        if (parts.length >= 2) {
            currentGiSnBarangSku = parts[0].trim();
            currentGiSnBarangNama = parts.slice(1).join(' - ').trim();
        } else {
            currentGiSnBarangSku = 'PROD-' + barangId;
            currentGiSnBarangNama = barangDisplay || 'Barang #' + barangId;
        }

        $('#modal_gi_sn_barang_nama').text(currentGiSnBarangNama);
        $('#modal_gi_sn_barang_sku').text(`SKU: ${currentGiSnBarangSku}`);
        $('#modal_gi_sn_search_stock').val('');
        $('#modal_gi_sn_quick_scan_input').val('');
        $('#modal_gi_sn_bulk_paste_input').val('');

        const store = isEdit ? window.editGiItemSerialsStore : window.giItemSerialsStore;
        if (!store[rowIdx]) store[rowIdx] = [];
        giModalWorkingSerials = [...store[rowIdx]];

        refreshGiModalSnUI();
        loadGiStockSerialsForModal();

        const modalEl = document.getElementById('modalSelectSerialsGI');
        if (modalEl) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }
    }
    window.openModalSelectSerialsGI = openModalSelectSerialsGI;

    // Camera Scanner Handlers
    function stopGiCameraScanner() {
        isGiCameraActive = false;
        giCameraSessionToken++;

        if (giModalCameraScanner) {
            let scanner = giModalCameraScanner;
            giModalCameraScanner = null;
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

        $('#modal_gi_sn_camera_scanner_container').addClass('d-none');
        $('#modal_gi_sn_camera_reader').empty();
        $('#btn_modal_gi_sn_toggle_camera')
            .removeClass('btn-danger text-white')
            .addClass('btn-outline-danger')
            .html('<i class="bi bi-camera-video-fill text-danger"></i> <span class="d-none d-sm-inline">Scan Kamera</span>');
    }
    window.stopGiCameraScanner = stopGiCameraScanner;

    function startGiCameraScanner(selectedCameraId = null) {
        stopGiCameraScanner();

        if (typeof Html5Qrcode === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Scanner Belum Siap',
                text: 'Pustaka Html5Qrcode belum selesai dimuat.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        isGiCameraActive = true;
        const currentSession = ++giCameraSessionToken;

        $('#modal_gi_sn_camera_scanner_container').removeClass('d-none');
        $('#modal_gi_sn_camera_reader').html(`
            <div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;">
                <div class="spinner-border text-danger mb-2" role="status"></div>
                <span class="small text-danger fw-semibold">Menginisialisasi Kamera...</span>
            </div>
        `);
        $('#btn_modal_gi_sn_toggle_camera')
            .removeClass('btn-outline-danger')
            .addClass('btn-danger text-white')
            .html('<i class="bi bi-camera-video-off-fill"></i> <span class="d-none d-sm-inline">Stop Kamera</span>');

        Html5Qrcode.getCameras().then(devices => {
            if (!isGiCameraActive || currentSession !== giCameraSessionToken) return;

            if (devices && devices.length > 0) {
                let selectHtml = '';
                devices.forEach((dev, idx) => {
                    selectHtml += `<option value="${dev.id}">${dev.label || ('Kamera ' + (idx + 1))}</option>`;
                });
                $('#modal_gi_sn_camera_select').html(selectHtml);

                const cameraId = selectedCameraId || devices[devices.length - 1].id;
                modalSnCameraScanner = new Html5Qrcode("modal_gi_sn_camera_reader");
                giModalCameraScanner = modalSnCameraScanner;

                modalSnCameraScanner.start(
                    cameraId,
                    { fps: 15, qrbox: { width: 250, height: 160 } },
                    (decodedText) => {
                        if (giCameraScanCooldown) return;
                        giCameraScanCooldown = true;
                        processSingleGiSerial(decodedText);
                        setTimeout(() => { giCameraScanCooldown = false; }, 1500);
                    },
                    () => {}
                ).catch(() => {
                    $('#modal_gi_sn_camera_reader').html('<div class="text-danger p-4 text-center small">Gagal mengakses kamera perangkat.</div>');
                });
            } else {
                $('#modal_gi_sn_camera_reader').html('<div class="text-danger p-4 text-center small">Tidak ada perangkat kamera yang terdeteksi.</div>');
            }
        }).catch(() => {
            $('#modal_gi_sn_camera_reader').html('<div class="text-danger p-4 text-center small">Izin akses kamera ditolak oleh browser.</div>');
        });
    }
    window.startGiCameraScanner = startGiCameraScanner;

    // ══════════════════════════════════════════════════════════════════════════
    // DOCUMENT READY DOM INITIALIZATION & EVENT LISTENERS
    // ══════════════════════════════════════════════════════════════════════════
    $(document).ready(function () {
        @if($issues->count())
        $('#tableGI').DataTable(giDataTableConfig);
        @endif

        // ─── REAL-TIME POLLER (1 DETIK) ───────────────────────────────
        setInterval(function () {
            let isSearching = $('#tableGI_filter input').is(':focus');
            let isModalOpen = $('.modal.show').length > 0;
            if (!isSearching && !isModalOpen) {
                reloadTableGI(false); // Silent background auto-sync
            }
        }, 1000);

        // Click delegation on .btn-open-gi-sn
        $(document).on('click', '.btn-open-gi-sn', function (e) {
            e.preventDefault();
            // If inline onclick exists, let it handle directly to avoid double invocation
            if (this.getAttribute('onclick')) return;
            const rowIdx = $(this).data('idx');
            const context = $(this).data('context') || 'input';
            openModalSelectSerialsGI(rowIdx, context);
        });

        if (typeof window.parseQrOrBarcodeString !== 'function') {
            window.parseQrOrBarcodeString = function(str) {
                str = (str || '').toString().trim();
                let sn = null;
                let kode = null;

                if (str.includes('sn=')) {
                    let m = str.match(/[?&]sn=([^&]+)/i);
                    if (m) sn = decodeURIComponent(m[1].trim());
                }

                if (!sn) {
                    let m = str.match(/SN\s*:\s*(.+?)(?=(?:(?:KODE|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
                    if (m) sn = m[1].trim();
                }

                let mKode = str.match(/KODE\s*:\s*(.+?)(?=(?:(?:SN|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
                if (mKode) kode = mKode[1].trim();

                if (!sn) {
                    let m = str.match(/^SN\s*[:\-\s]\s*(.+)$/i);
                    if (m) sn = m[1].trim();
                }

                return { sn: sn, kode: kode, raw: str };
            };
        }

        // Filter stock serials search in Tab 1
        $('#modal_gi_sn_search_stock').on('input keyup', function () {
            let rawVal = $(this).val();
            let parsed = window.parseQrOrBarcodeString(rawVal);
            const query = (parsed.sn || parsed.kode || rawVal).toLowerCase().trim();
            let matchCount = 0;
            $('.gi-stock-sn-item').each(function () {
                const sn = $(this).data('sn') || '';
                const batch = $(this).data('batch') || '';
                if (sn.includes(query) || batch.includes(query)) {
                    $(this).show();
                    matchCount++;
                } else {
                    $(this).hide();
                }
            });
        });

        // Checkbox change event in Tab 1
        $(document).on('change', '.gi-stock-sn-checkbox', function () {
            const sn = $(this).val();
            if ($(this).is(':checked')) {
                if (!giModalWorkingSerials.includes(sn)) {
                    giModalWorkingSerials.push(sn);
                }
            } else {
                giModalWorkingSerials = giModalWorkingSerials.filter(item => item !== sn);
            }
            refreshGiModalSnUI();
        });

        // Auto-select button
        $('#btn_modal_gi_sn_auto_select').on('click', function () {
            const target = currentGiSnTargetQty;
            if (target <= 0) return;

            let addedCount = 0;
            for (let i = 0; i < giAvailableStockSerials.length; i++) {
                if (giModalWorkingSerials.length >= target) break;
                const sn = giAvailableStockSerials[i].serial_number;
                if (!giModalWorkingSerials.includes(sn)) {
                    giModalWorkingSerials.push(sn);
                    addedCount++;
                }
            }
            refreshGiModalSnUI();

            if (addedCount > 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${addedCount} serial number berhasil dipilih otomatis!`,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });

        // Reset selection button
        $('#btn_modal_gi_sn_clear_selection').on('click', function () {
            giModalWorkingSerials = [];
            refreshGiModalSnUI();
        });

        // Remove single chip from chips box
        $(document).on('click', '.btn-modal-remove-gi-chip', function () {
            const sn = $(this).data('sn');
            giModalWorkingSerials = giModalWorkingSerials.filter(item => item !== sn);
            refreshGiModalSnUI();
        });

        // Single Manual Scan button & Enter
        $('#btn_modal_gi_sn_add_single').on('click', function () {
            processSingleGiSerial($('#modal_gi_sn_quick_scan_input').val());
        });

        $('#modal_gi_sn_quick_scan_input').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                processSingleGiSerial($(this).val());
            }
        });

        // Bulk Paste Apply
        $('#btn_modal_gi_sn_apply_bulk').on('click', function () {
            const raw = $('#modal_gi_sn_bulk_paste_input').val();
            if (!raw || !$.trim(raw)) return;

            const tokens = raw.split(/[\r\n,]+/).map(t => $.trim(t)).filter(t => t.length > 0);
            if (tokens.length === 0) return;

            let added = 0;
            let notFound = [];
            let duplicates = [];

            tokens.forEach(sn => {
                const match = giAvailableStockSerials.find(s => s.serial_number.toLowerCase() === sn.toLowerCase());
                if (!match) {
                    notFound.push(sn);
                } else {
                    const officialSn = match.serial_number;
                    if (giModalWorkingSerials.includes(officialSn)) {
                        duplicates.push(officialSn);
                    } else {
                        if (giModalWorkingSerials.length < currentGiSnTargetQty) {
                            giModalWorkingSerials.push(officialSn);
                            added++;
                        }
                    }
                }
            });

            $('#modal_gi_sn_bulk_paste_input').val('');
            refreshGiModalSnUI();

            let msg = `<b>${added}</b> serial number berhasil ditambahkan!`;
            if (notFound.length > 0) {
                msg += `<br><small class="text-danger mt-1 d-block">${notFound.length} serial tidak terdaftar di stok gudang.</small>`;
            }
            if (duplicates.length > 0) {
                msg += `<br><small class="text-muted mt-0.5 d-block">${duplicates.length} serial sudah pernah dipilih sebelumnya.</small>`;
            }

            Swal.fire({
                icon: added > 0 ? 'success' : 'warning',
                title: 'Hasil Penambahan Massal',
                html: msg,
                confirmButtonColor: '#ef4444'
            });
        });

        // Camera Scanner Handlers
        $('#btn_modal_gi_sn_toggle_camera').on('click', function () {
            if (isGiCameraActive) {
                stopGiCameraScanner();
            } else {
                startGiCameraScanner();
            }
        });

        $('#btn_modal_gi_sn_stop_camera').on('click', function () {
            stopGiCameraScanner();
        });

        $('#modal_gi_sn_camera_select').on('change', function () {
            const devId = $(this).val();
            if (devId) startGiCameraScanner(devId);
        });

        // Apply Serial Numbers to Row
        $('#btn_modal_gi_sn_save').on('click', function () {
            if (currentGiSnIdx === null) return;

            const isEdit = (currentGiSnContext === 'edit');
            const store = isEdit ? window.editGiItemSerialsStore : window.giItemSerialsStore;
            store[currentGiSnIdx] = [...giModalWorkingSerials];

            updateRowSnBadge(currentGiSnIdx, currentGiSnContext, currentGiSnTargetQty);
            stopGiCameraScanner();
            const modalEl = document.getElementById('modalSelectSerialsGI');
            if (modalEl) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.hide();
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `${giModalWorkingSerials.length} serial number diterapkan ke baris rincian!`,
                showConfirmButton: false,
                timer: 1500
            });
        });

        // Stop camera when modal is closed
        $('#modalSelectSerialsGI').on('hidden.bs.modal', function () {
            stopGiCameraScanner();
        });
    });
    </script>
    @endpush
</x-layouts>
