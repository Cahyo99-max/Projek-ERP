<x-layouts title="Pengajuan Pembelian (Purchase Request)">

    @push('styles')
    <!-- Select2 Searchable Dropdown with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <!-- Barang Dashboard CSS for Tab Filter Pills -->
    <link rel="stylesheet" href="{{ asset('css/barang_dashboard.css?v=' . time()) }}">
    <!-- PR Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/pr.css?v=' . time()) }}">
    @endpush

    <div id="pr_table_container">
    <!-- Header Table Card -->
    <x-table_card tableId="tablePR" title="Pengajuan Pembelian (Purchase Request)" icon="bi-file-earmark-text-fill"
        btnTarget="{{ auth()->user()->can('tambah-pr') ? '#modalTambahPR' : '' }}"
        btnText="Buat Pengajuan">

        <x-slot name="extraActions">
            <button type="button"
                class="btn btn-outline-secondary rounded-pill d-flex align-items-center gap-2 px-3 position-relative"
                data-bs-toggle="modal" data-bs-target="#modalFilterPR"
                style="height: 38px; font-weight: 600; font-size: 0.85rem; border-color: #cbd5e1;">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
                <span id="filter-badge" class="position-absolute top-0 start-100 translate-middle p-1.5 bg-primary border border-light rounded-circle d-none">
                    <span class="visually-hidden">Filter Aktif</span>
                </span>
            </button>
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Nomor PR</th>
            <th class="py-3">Tanggal PR</th>
            <th class="py-3">Dibutuhkan</th>
            <th class="py-3">Departemen</th>
            <th class="py-3">Diminta Oleh</th>
            <th class="py-3 text-center">Total Item</th>
            <th class="py-3 text-center">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>


    </x-table_card>
    </div>

    <!-- Modal Filter PR -->
    <div class="modal fade" id="modalFilterPR" tabindex="-1" aria-labelledby="modalFilterPRLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalFilterPRLabel">
                        <i class="bi bi-funnel-fill text-primary me-2"></i>Filter Pengajuan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <!-- Status Filter -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select id="filter_status" class="form-select" style="border-radius: 8px; border-color: #cbd5e1; height: 40px; font-size: 0.9rem;">
                                <option value="">Semua Status</option>
                                <option value="Draft">Draft</option>
                                <option value="Pending">Pending Approval</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <!-- Departemen Filter -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Departemen</label>
                            <select id="filter_departemen" class="form-select" style="border-radius: 8px; border-color: #cbd5e1; height: 40px; font-size: 0.9rem;">
                                <option value="">Semua Departemen</option>
                                @foreach($departments as $dept)
                                    @if(strtolower($dept->status) === 'active')
                                        <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <!-- Rentang Tanggal -->
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                            <input type="date" id="filter_tgl_mulai" class="form-control" style="border-radius: 8px; border-color: #cbd5e1; height: 40px; font-size: 0.9rem;">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Tanggal Selesai</label>
                            <input type="date" id="filter_tgl_selesai" class="form-control" style="border-radius: 8px; border-color: #cbd5e1; height: 40px; font-size: 0.9rem;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-between flex-wrap gap-2">
                    <button type="button" id="btn-reset-filter" class="btn btn-light border rounded-pill px-3" style="height: 38px; font-weight: 600; font-size: 0.85rem;" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btn-export-excel-pr" class="btn btn-success rounded-pill px-3 no-loader" style="height: 38px; font-weight: 600; font-size: 0.85rem;">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" style="height: 38px; font-weight: 600; font-size: 0.85rem;" data-bs-dismiss="modal">
                            <i class="bi bi-check2-circle me-1"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL COMPONENT INCLUSIONS ===== --}}
    @include('modul_purchasing.pembuatanpr.tambah')
    @include('modul_purchasing.pembuatanpr.edit')
    @include('modul_purchasing.pembuatanpr.detail')
    @include('modul_purchasing.pembuatanpr.cari_barang')
    @include('modul_purchasing.pembuatanpr.quick_add_barang')
    @include('modul_purchasing.pembuatanpr.lookup_karyawan')
    @include('modul_purchasing.pembuatanpr.lookup_user')

    {{-- ===== CORE LIBRARIES & GLOBAL VIEWPR HELPERS (FIRST IN STACK) ===== --}}
    @push('scripts')
    <!-- FilePond JS & Plugins -->
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <!-- Select2 Searchable Dropdown Script Library -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Local storage for dynamically pushed/added items
        const activeBarang = [];

        // Global listener to release focus from modal elements before closing (prevents Chrome aria-hidden console warning)
        $(document).on('hide.bs.modal', '.modal', function () {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
        });

        // Global variables for Lookups & Dynamic Rows
        let currentSelectorRow = null;
        let currentSearchTipe = null;
        let currentLookupTarget = 'tambah';
        let currentUserLookupTarget = 'tambah';
        let itemRowIndex = 0;

        // Global formatting/helpers
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        function formatRupiahRaw(amount) {
            if (!amount) return '0';
            return Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatNumberInput(value) {
            let number_string = value.replace(/[^0-9]/g, '').toString();
            let sisa = number_string.length % 3;
            let rupiah = number_string.substr(0, sisa);
            let ribuan = number_string.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }

        function unformatNumber(value) {
            if (!value) return 0;
            return parseFloat(value.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Table refresh trigger
        function reloadTablePR(showSpinner = true) {
            if ($.fn.DataTable.isDataTable('#tablePR')) {
                $('#tablePR').DataTable().ajax.reload(null, false);
            }
        }

        // Add dynamic item rows helper function
        function updateRowConversionHelper(row) {
            let selectSatuan = row.find('.select-satuan-input');
            let faktor = 1;
            if (selectSatuan.is('select')) {
                faktor = parseFloat(selectSatuan.find('option:selected').data('faktor')) || 1;
            } else {
                faktor = parseFloat(row.find('.input-faktor-pengali').val()) || 1;
            }
            row.find('.input-faktor-pengali').val(faktor);

            let qty = parseFloat(row.find('.input-jumlah').val()) || 0;
            let baseUnit = row.find('.input-base-satuan-nama').val() || '';
            let previewEl = row.find('.label-jumlah-dasar-preview');

            if (Math.abs(faktor - 1) > 0.00001 && qty > 0) {
                let totalBase = (qty * faktor);
                let formattedBase = totalBase % 1 === 0 
                    ? totalBase.toLocaleString('id-ID') 
                    : parseFloat(totalBase.toFixed(4)).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });
                previewEl.removeClass('d-none').show();
                previewEl.find('.val-jumlah-dasar').text(`Setara: ${formattedBase} ${baseUnit}`);
            } else {
                previewEl.addClass('d-none').hide();
            }
        }
        window.updateRowConversionHelper = updateRowConversionHelper;

        $(document).on('change', '.select-satuan-input', function() {
            updateRowConversionHelper($(this).closest('tr'));
        });
        $(document).on('input', '.input-jumlah', function() {
            updateRowConversionHelper($(this).closest('tr'));
        });

        function addItemRow(containerId, rowData = null) {
            let uniqueId = itemRowIndex++;
            let selectedBarangId = rowData ? rowData.barang_id : '';
            let jumlah = rowData ? parseFloat(rowData.jumlah) : 1;
            let hargaEstimasi = rowData ? parseFloat(rowData.harga_estimasi) : 0;
            let lineKeterangan = rowData ? rowData.keterangan : '';
            let selectedSatuanId = rowData ? (rowData.satuan_id || (rowData.barang ? rowData.barang.satuan_id : '')) : '';
            let faktorPengali = rowData ? (parseFloat(rowData.faktor_pengali) || 1) : 1;
            let konversis = [];
            
            let initialSatuan = '';
            let initialFotoHtml = `<i class="bi bi-image text-muted" style="font-size: 1.1rem;"></i>`;
            let selectedBarangName = '';
            let selectedBarangType = '';
            let selectedBarangWarnaNama = '';
            let selectedBarangWarnaHex = '';
            let selectedBarangSpesifikasi = '';
            let initialFotoClass = 'product-img-frame product-img-preview';
            let initialFotoData = '';
            let initialFotoStyle = '';
            
            if (selectedBarangId) {
                let match = activeBarang.find(b => b.id == selectedBarangId);
                if (!match && rowData && rowData.barang) {
                    let b = rowData.barang;
                    let semuaFoto = [];
                    if (b.fotos) {
                        semuaFoto = b.fotos.map(f => `/storage/${f.foto_path}`);
                    }
                    let primaryFotoUrl = '';
                    if (b.primary_foto) {
                        primaryFotoUrl = `/storage/${b.primary_foto.foto_path}`;
                    } else if (b.fotos && b.fotos.length > 0) {
                        primaryFotoUrl = `/storage/${b.fotos[0].foto_path}`;
                    }

                    let konversisList = [];
                    if (b.satuan) {
                        konversisList.push({
                            satuan_id: b.satuan.id,
                            nama_satuan: b.satuan.nama_satuan,
                            singkatan: b.satuan.singkatan,
                            faktor_pengali: 1,
                            is_base: true,
                            label: b.satuan.nama_satuan + ' (Satuan Dasar)'
                        });
                    }
                    let ksArray = b.konversi_satuan || b.konversis || [];
                    if (Array.isArray(ksArray)) {
                        ksArray.forEach(ks => {
                            if (ks.satuan && ks.is_satuan_beli) {
                                let baseUnitName = b.satuan ? (b.satuan.singkatan || b.satuan.nama_satuan) : '';
                                konversisList.push({
                                    satuan_id: ks.satuan.id,
                                    nama_satuan: ks.satuan.nama_satuan,
                                    singkatan: ks.satuan.singkatan,
                                    faktor_pengali: parseFloat(ks.faktor_pengali),
                                    is_base: false,
                                    label: ks.satuan.nama_satuan + ' (1 ' + ks.satuan.nama_satuan + ' = ' + (parseFloat(ks.faktor_pengali) + 0) + ' ' + baseUnitName + ')'
                                });
                            }
                        });
                    }

                    match = {
                        id: b.id,
                        kode: b.kode_barang,
                        nama: b.nama_barang,
                        tipe_item: b.tipe_item,
                        satuan_id: b.satuan_id,
                        satuan: (b.satuan ? (b.satuan.singkatan || b.satuan.nama_satuan) : '') || 'pcs',
                        satuan_nama: (b.satuan ? b.satuan.nama_satuan : '') || 'pcs',
                        foto: primaryFotoUrl,
                        semua_foto: semuaFoto,
                        warna_nama: b.warna ? b.warna.nama_warna : '',
                        warna_hex: b.warna ? b.warna.kode_hex : '',
                        spesifikasi: b.spesifikasi || '',
                        konversis: konversisList
                    };
                }

                if (match) {
                    initialSatuan = match.satuan;
                    selectedBarangName = `[${match.kode}] ${match.nama}`;
                    selectedBarangType = match.tipe_item;
                    selectedBarangWarnaNama = match.warna_nama;
                    selectedBarangWarnaHex = match.warna_hex;
                    selectedBarangSpesifikasi = match.spesifikasi || '';
                    konversis = match.konversis || [];
                    if (!selectedSatuanId) selectedSatuanId = match.satuan_id || '';
                    if (match.foto) {
                        initialFotoHtml = `<img src="${match.foto}" style="width: 100%; height: 100%; object-fit: cover;">`;
                        initialFotoClass += ' img-thumbnail-preview cursor-pointer';
                        initialFotoData = `data-nama="${escapeHtml(match.nama)}" data-semua-foto="${escapeHtml(Array.isArray(match.semua_foto) ? match.semua_foto.join(',') : match.semua_foto)}"`;
                    }
                }
            }

            let defaultType = selectedBarangType || 'Stok';

            let typeBadgeHtml = '';
            if (selectedBarangType) {
                let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
                if (selectedBarangType === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
                else if (selectedBarangType === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';
                typeBadgeHtml = `<span class="badge small px-2 py-0.5 rounded-pill label-tipe-item" style="font-size: 0.65rem; ${bg}">${selectedBarangType.toUpperCase()}</span>`;
            } else {
                typeBadgeHtml = `<span class="badge small px-2 py-0.5 rounded-pill label-tipe-item d-none" style="font-size: 0.65rem;"></span>`;
            }

            let warnaBadgeHtml = '';
            if (selectedBarangWarnaNama) {
                warnaBadgeHtml = `
                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center label-warna-badge" style="font-size:0.75rem;font-weight:600; margin-left: 4px;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${selectedBarangWarnaHex || '#e2e8f0'};margin-right:6px;border:1px solid rgba(0,0,0,0.15);"></span>
                        ${escapeHtml(selectedBarangWarnaNama)}
                    </span>
                `;
            } else {
                warnaBadgeHtml = `
                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center label-warna-badge d-none" style="font-size:0.75rem;font-weight:600; margin-left: 4px;"></span>
                `;
            }

            let spesifikasiHtml = '';
            if (selectedBarangSpesifikasi) {
                spesifikasiHtml = `<div class="text-muted small mt-1 label-spesifikasi" style="font-size: 0.7rem;"><i class="bi bi-info-circle me-1"></i><span>${escapeHtml(selectedBarangSpesifikasi)}</span></div>`;
            } else {
                spesifikasiHtml = `<div class="text-muted small mt-1 label-spesifikasi d-none" style="font-size: 0.7rem;"><i class="bi bi-info-circle me-1"></i><span></span></div>`;
            }

            let satuanCellHtml = '';
            if (konversis && konversis.length > 1) {
                let optionsHtml = '';
                konversis.forEach(k => {
                    let sel = (k.satuan_id == selectedSatuanId) ? 'selected' : '';
                    let optText = k.label || (k.nama_satuan || k.singkatan);
                    optionsHtml += `<option value="${k.satuan_id}" data-faktor="${k.faktor_pengali}" data-is-base="${k.is_base ? 1 : 0}" data-nama="${escapeHtml(k.nama_satuan || k.singkatan)}" ${sel}>${escapeHtml(optText)}</option>`;
                });
                satuanCellHtml = `
                    <select class="form-select form-select-sm select-satuan-input" style="min-width: 120px; font-size: 0.78rem; border-radius: 6px; border-color: #cbd5e1;">
                        ${optionsHtml}
                    </select>
                    <input type="hidden" class="input-faktor-pengali" value="${faktorPengali}">
                    <input type="hidden" class="input-base-satuan-nama" value="${escapeHtml(initialSatuan)}">
                `;
            } else {
                satuanCellHtml = `
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill fw-semibold label-satuan-name">${escapeHtml(initialSatuan) || '-'}</span>
                    <input type="hidden" class="select-satuan-input" value="${selectedSatuanId}">
                    <input type="hidden" class="input-faktor-pengali" value="${faktorPengali}">
                    <input type="hidden" class="input-base-satuan-nama" value="${escapeHtml(initialSatuan)}">
                `;
            }

            let html = `
                <tr class="item-row">
                    <td class="text-center fw-semibold pr-row-number py-2 pr-row-number-td"></td>
                    <td data-label="Tipe">
                        <select class="form-select form-select-sm select-tipe-input" style="min-width: 90px; border-radius: 6px; border-color: #cbd5e1;">
                            <option value="Stok" ${defaultType === 'Stok' ? 'selected' : ''}>Stok</option>
                            <option value="Jasa" ${defaultType === 'Jasa' ? 'selected' : ''}>Jasa</option>
                            <option value="Aset" ${defaultType === 'Aset' ? 'selected' : ''}>Aset</option>
                        </select>
                    </td>
                    <td data-label="Nama Barang">
                        <div class="d-flex align-items-center gap-2">
                            <div class="${initialFotoClass}" ${initialFotoData} style="${initialFotoStyle}" title="${selectedBarangId ? 'Klik untuk memperbesar foto' : ''}">
                                ${initialFotoHtml}
                            </div>
                            <div class="flex-grow-1">
                                <div class="input-group input-group-sm select-product-container">
                                    <input type="hidden" class="select-product-input" value="${selectedBarangId}" required>
                                    <input type="text" class="form-control form-control-custom label-product-name" value="${escapeHtml(selectedBarangName)}" placeholder="Pilih barang..." readonly required style="background-color: #f8fafc; cursor: pointer;">
                                    <button type="button" class="btn btn-outline-primary btn-search-barang-modal" title="Cari Barang"><i class="bi bi-search"></i></button>
                                </div>
                                <div class="mt-1 badge-container">
                                    ${typeBadgeHtml}
                                    ${warnaBadgeHtml}
                                </div>
                                ${spesifikasiHtml}
                            </div>
                        </div>
                    </td>
                    <td data-label="Satuan" class="satuan-cell-container">
                        ${satuanCellHtml}
                    </td>
                    <td data-label="Jumlah">
                        <input type="number" class="form-control form-control-sm form-control-custom input-jumlah" min="0.01" step="any" value="${jumlah}" required>
                        <div class="label-jumlah-dasar-preview small text-muted mt-0.5 d-none" style="font-size: 0.68rem; font-weight: 500;">
                            <i class="bi bi-arrow-return-right"></i> <span class="val-jumlah-dasar"></span>
                        </div>
                    </td>
                    <td data-label="Estimasi Harga">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted small">Rp</span>
                            <input type="text" class="form-control form-control-sm form-control-custom input-harga-formatted" value="${formatRupiahRaw(hargaEstimasi)}" required>
                        </div>
                    </td>
                    <td data-label="Total" class="pr-row-subtotal py-2">${formatRupiah(jumlah * hargaEstimasi)}</td>
                    <td data-label="Catatan">
                        <input type="text" class="form-control form-control-sm form-control-custom input-keterangan" placeholder="Catatan (Max 40)..." maxlength="40" value="${escapeHtml(lineKeterangan)}" autocomplete="new-password">
                    </td>
                    <td class="text-center py-2 pr-row-delete-td">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-delete-item-row" title="Hapus Barang">
                            <i class="bi bi-trash-fill" style="font-size:0.85rem;"></i>
                        </button>
                    </td>
                </tr>
            `;

            $(`#${containerId}`).append(html);

            let newRow = $(`#${containerId} tr.item-row:last`);
            updateRowConversionHelper(newRow);

            reindexRows(containerId);
            calculateGrandTotal(containerId);
        }

        function reindexRows(containerId) {
            $(`#${containerId} tr.item-row`).each(function (index) {
                $(this).find('.pr-row-number').text(index + 1);
            });
        }

        function calculateGrandTotal(containerId) {
            let grandTotal = 0;
            $(`#${containerId} tr.item-row`).each(function () {
                let qty = parseFloat($(this).find('.input-jumlah').val()) || 0;
                let priceVal = $(this).find('.input-harga-formatted').val();
                let price = unformatNumber(priceVal);
                let sub = qty * price;
                grandTotal += sub;
                $(this).find('.pr-row-subtotal').text(formatRupiah(sub));
            });
            
            let targetLabel = containerId === 'tambah_items_tbody' ? '#tambah_grand_total' : '#edit_grand_total';
            $(targetLabel).text(formatRupiah(grandTotal));
        }

        // Silence default browser alert popups on DataTables errors (like network drop)
        $.fn.dataTable.ext.errMode = 'none';

        // Hide loading spinner if DataTables request fails
        $(document).on('error.dt', function (e, settings, techNote, message) {
            console.warn('DataTables error captured:', message);
            $('.dataTables_processing').hide();
        });

        $(document).ready(function () {
            // ===== INITIALIZE MAIN TABLE DATATABLE (SERVER-SIDE) =====
            const dtPR = $('#tablePR').DataTable({
                processing: true, // Enable loading overlay
                serverSide: true,
                ajax: {
                    url: window.location.href,
                    data: function(d) {
                        d.filter_status = $('#filter_status').val();
                        d.filter_departemen = $('#filter_departemen').val();
                        d.filter_tgl_mulai = $('#filter_tgl_mulai').val();
                        d.filter_tgl_selesai = $('#filter_tgl_selesai').val();
                    }
                },
                columns: [
                    { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_pr', name: 'nomor_pr' },
                    { data: 'tanggal_pr', name: 'tanggal_pr' },
                    { data: 'tanggal_dibutuhkan', name: 'tanggal_dibutuhkan' },
                    { data: 'departemen', name: 'departemen' },
                    { data: 'diminta_oleh', name: 'diminta_oleh' },
                    { data: 'total_item', name: 'total_item', className: 'text-center', orderable: false, searchable: false },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', className: 'text-center', orderable: false, searchable: false }
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-file-earmark-text fs-2 d-block mb-2 opacity-25"></i>Belum ada data pengajuan pembelian.</div>',
                    zeroRecords: 'Tidak ditemukan data pengajuan yang cocok'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[1, 'desc']] // Order by nomor_pr desc by default
            });

            // Global reload function for both main page and detail modal ajax callbacks (Silent, no spinner popup)
            window.reloadTablePR = function() {
                let settings = dtPR.settings()[0];
                if (settings) settings.oFeatures.bProcessing = false;
                dtPR.ajax.reload(function() {
                    if (settings) settings.oFeatures.bProcessing = true;
                }, false);
            };

            // ===== REAL-TIME PR STATUS AUTO-SYNC (ULTRA-FAST SILENT POLL WITH INTERACTION GUARDS) =====
            let isSilentReloading = false;
            let isUserInteracting = false;

            $(document).on('mouseenter', '#tablePR', function() { isUserInteracting = true; });
            $(document).on('mouseleave', '#tablePR', function() { isUserInteracting = false; });
            $(document).on('touchstart', '#tablePR', function() { isUserInteracting = true; });
            $(document).on('touchend', '#tablePR', function() { setTimeout(() => { isUserInteracting = false; }, 1500); });

            setInterval(function () {
                const isSearching = $('div.dataTables_filter input').is(':focus');
                const isModalOpen = $('.modal.show').length > 0;
                const pageHidden  = document.hidden;
                if (!pageHidden && !isModalOpen && !isSearching && !isUserInteracting && !isSilentReloading && $.fn.DataTable.isDataTable('#tablePR')) {
                    isSilentReloading = true;
                    let settings = dtPR.settings()[0];
                    if (settings) settings.oFeatures.bProcessing = false;
                    dtPR.ajax.reload(function() {
                        if (settings) settings.oFeatures.bProcessing = true;
                        isSilentReloading = false;
                    }, false);
                }
            }, 1500);

            // ===== INJECT TAB FILTER PILLS =====
            const tabHtml = `
                <div class="brg-tab-wrapper" id="prTabFilter">
                    <button class="brg-tab-pill pr-tab-pill active" data-status="">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Semua
                    </button>
                    <button class="brg-tab-pill pr-tab-pill" data-status="Draft">
                        <i class="bi bi-file-earmark-text"></i> Draft
                    </button>
                    <button class="brg-tab-pill pr-tab-pill" data-status="Pending">
                        <i class="bi bi-clock-history"></i> Pending
                    </button>
                    <button class="brg-tab-pill pr-tab-pill" data-status="Approved">
                        <i class="bi bi-check-circle"></i> Approved
                    </button>
                    <button class="brg-tab-pill pr-tab-pill" data-status="Rejected">
                        <i class="bi bi-x-circle"></i> Rejected
                    </button>
                    <button class="brg-tab-pill pr-tab-pill" data-status="Closed">
                        <i class="bi bi-archive"></i> Closed
                    </button>
                </div>
            `;

            // Inject tabs above the DataTables top controls
            $('#tablePR_wrapper .row:first').before(tabHtml);

            // Tab click handler
            $(document).on('click', '.pr-tab-pill', function() {
                $('.pr-tab-pill').removeClass('active');
                $(this).addClass('active');
                
                const statusVal = $(this).data('status');
                $('#filter_status').val(statusVal);
                updateFilterBadge();
                dtPR.ajax.reload();
            });

            // Two-way sync: Update active tab when select input changes in filter modal
            $('#filter_status').on('change', function() {
                const val = $(this).val() || "";
                $('.pr-tab-pill').removeClass('active');
                $(`.pr-tab-pill[data-status="${val}"]`).addClass('active');
            });

            function updateFilterBadge() {
                const status = $('#filter_status').val();
                const dept = $('#filter_departemen').val();
                const tglMulai = $('#filter_tgl_mulai').val();
                const tglSelesai = $('#filter_tgl_selesai').val();
                
                if (status || dept || tglMulai || tglSelesai) {
                    $('#filter-badge').removeClass('d-none');
                } else {
                    $('#filter-badge').addClass('d-none');
                }
            }

            // Re-draw table when filters change
            $('#filter_status, #filter_departemen, #filter_tgl_mulai, #filter_tgl_selesai').on('change input', function() {
                updateFilterBadge();
                dtPR.ajax.reload();
            });

            // Reset filters
            $('#btn-reset-filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_departemen').val('');
                $('#filter_tgl_mulai').val('');
                $('#filter_tgl_selesai').val('');
                updateFilterBadge();
                dtPR.ajax.reload();
            });

            // ===== APPROVE PR =====
            $(document).on('click', '.btn-approve', function () {
                let id = $(this).data('id');
                let tr = $(this).closest('tr');
                let oldStatusHtml = tr.find('td:nth-child(8)').html();
                let oldActionsHtml = tr.find('td:nth-child(9)').html();
                
                Swal.fire({
                    title: 'Setujui Pengajuan?',
                    text: "Apakah Anda yakin ingin menyetujui pengajuan pembelian ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Instant UI Update (0ms): Change badge and remove buttons immediately
                        let approvedHtml = '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">Approved</span>';
                        tr.find('td:nth-child(8)').html(approvedHtml);
                        tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').remove();

                        // Instant Centered Success Popup (0ms)
                        Swal.fire({
                            icon: 'success',
                            title: 'Disetujui!',
                            text: 'Pengajuan pembelian berhasil disetujui.',
                            timer: 1000,
                            showConfirmButton: false
                        });

                        $.ajax({
                            url: "/purchase-requisition/" + id + "/approve",
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function (res) {
                                reloadTablePR();
                            },
                            error: function (err) {
                                // Revert optimistic changes on error
                                tr.find('td:nth-child(8)').html(oldStatusHtml);
                                tr.find('td:nth-child(9)').html(oldActionsHtml);
                                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });

            // ===== REJECT PR =====
            $(document).on('click', '.btn-reject', function () {
                let id = $(this).data('id');
                let tr = $(this).closest('tr');
                let oldStatusHtml = tr.find('td:nth-child(8)').html();
                let oldActionsHtml = tr.find('td:nth-child(9)').html();
                
                Swal.fire({
                    title: 'Tolak Pengajuan?',
                    text: 'Pengajuan ini akan ditolak dan dikembalikan ke pemohon.',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan penolakan (opsional)...',
                    inputAttributes: {
                        maxlength: 200,
                        style: 'font-size: 0.9rem; border-radius: 8px;'
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal',
                    didOpen: () => {
                        const textarea = Swal.getInput();
                        if (textarea) {
                            setTimeout(() => textarea.focus(), 50);
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let alasan = result.value || '';

                        // Instant UI Update (0ms): Change badge and remove buttons immediately
                        let rejectedHtml = '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold">Rejected</span>';
                        tr.find('td:nth-child(8)').html(rejectedHtml);
                        tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').remove();

                        // Instant Centered Success Popup (0ms)
                        Swal.fire({
                            icon: 'success',
                            title: 'Ditolak!',
                            text: 'Pengajuan pembelian telah ditolak.',
                            timer: 1000,
                            showConfirmButton: false
                        });

                        $.ajax({
                            url: "/purchase-requisition/" + id + "/reject",
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                alasan: alasan
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function (res) {
                                reloadTablePR();
                            },
                            error: function (err) {
                                // Revert optimistic changes on error
                                tr.find('td:nth-child(8)').html(oldStatusHtml);
                                tr.find('td:nth-child(9)').html(oldActionsHtml);
                                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });



            // ===== DELETE PR =====
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor');
                let tr = $(this).closest('tr');

                Swal.fire({
                    title: 'Hapus Pengajuan?',
                    html: `Anda akan menghapus pengajuan pembelian: <b>${nomor}</b>.<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Optimistic UI Update: Fade out row instantly
                        tr.css('transition', 'all 0.5s ease').css('opacity', '0.4');
                        tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').addClass('d-none');

                        $.ajax({
                            url: "/purchase-requisition/" + id + "/delete",
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function (res) {
                                tr.css('opacity', '0');
                                setTimeout(() => {
                                    tr.remove();
                                }, 500);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Pengajuan pembelian berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Silently reload data in background to sync state
                                reloadTablePR();
                            },
                            error: function (err) {
                                // Revert optimistic changes on error
                                tr.css('opacity', '1');
                                tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').removeClass('d-none');
                                let errorMsg = 'Gagal menghapus data pengajuan.';
                                if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg = err.responseJSON.message;
                                }
                                Swal.fire('Gagal!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // ===== EVENT DELEGATIONS FOR DYNAMIC ROWS =====
            $(document).on('change', '.select-tipe-input', function () {
                let row = $(this).closest('tr');
                row.find('.select-product-input').val('');
                row.find('.label-product-name').val('');
                row.find('.label-satuan-name').text('-');
                row.find('.product-img-preview')
                    .removeClass('img-thumbnail-preview')
                    .removeAttr('data-nama')
                    .removeAttr('data-semua-foto')
                    .css('cursor', 'default')
                    .removeAttr('title')
                    .html('<i class="bi bi-image text-muted" style="font-size: 1.1rem;"></i>');
                row.find('.label-tipe-item').addClass('d-none');
                row.find('.label-warna-badge').addClass('d-none').html('');
                row.find('.pr-row-subtotal').text(formatRupiah(0));
                
                let tbodyId = row.closest('tbody').attr('id');
                calculateGrandTotal(tbodyId);
            });

            // Real-time currency format as thousands with dots
            $(document).on('input', '.input-harga-formatted', function () {
                let val = $(this).val();
                let formatted = formatNumberInput(val);
                $(this).val(formatted);
                
                // Trigger subtotal recalculation
                let tr = $(this).closest('tr');
                let qty = parseFloat(tr.find('.input-jumlah').val()) || 0;
                let price = unformatNumber(formatted);
                let sub = qty * price;
                tr.find('.pr-row-subtotal').text(formatRupiah(sub));
                
                let tbodyId = $(this).closest('tbody').attr('id');
                calculateGrandTotal(tbodyId);
            });

            // Clear zero value on focus and restore to zero if empty on blur
            $(document).on('focus', '.input-harga-formatted', function () {
                let val = $(this).val();
                if (val === '0') {
                    $(this).val('');
                }
            });

            $(document).on('blur', '.input-harga-formatted', function () {
                let val = $(this).val();
                if (val === '') {
                    $(this).val('0');
                    let tbodyId = $(this).closest('tbody').attr('id');
                    calculateGrandTotal(tbodyId);
                }
            });

            // Listeners for Select2 changes
            $(document).on('change', '.select-product-input', function () {
                let selectedOpt = $(this).find('option:selected');
                let unit = selectedOpt.data('satuan') || '-';
                let fotoUrl = selectedOpt.data('foto') || '';
                
                let tr = $(this).closest('tr');
                tr.find('.label-satuan-name').text(unit);
                
                let imgPreview = tr.find('.product-img-preview');
                if (fotoUrl) {
                    imgPreview.html(`<img src="${fotoUrl}" style="width: 100%; height: 100%; object-fit: cover;">`);
                } else {
                    imgPreview.html(`<i class="bi bi-image text-muted" style="font-size: 1.1rem;"></i>`);
                }
            });

            $(document).on('input change', '.input-jumlah', function () {
                let tbodyId = $(this).closest('tbody').attr('id');
                calculateGrandTotal(tbodyId);
            });

            // Bind click to add item row with 20 items limit
            $(document).on('click', '.btn-add-item-row', function () {
                let type = $(this).data('type');
                let containerId = type === 'tambah' ? 'tambah_items_tbody' : 'edit_items_tbody';
                let currentCount = $('#' + containerId + ' tr.item-row').length;

                if (currentCount >= 20) {
                    Swal.fire({
                        title: 'Batas Item Terlampaui!',
                        html: 'Pengajuan PR dibatasi <b>maksimal 20 item barang</b>.<br><span class="text-danger small fw-semibold">Tidak dapat menambah item barang lebih banyak lagi.</span>',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                addItemRow(containerId);
            });

            // Remove row
            $(document).on('click', '.btn-delete-item-row', function () {
                let tr = $(this).closest('tr');
                let tbody = tr.closest('tbody');
                let tbodyId = tbody.attr('id');
                
                if (tbody.find('tr.item-row').length <= 1) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'warning',
                        title: 'Pengajuan harus memiliki minimal 1 item barang!'
                    });
                    return;
                }
                
                tr.remove();
                reindexRows(tbodyId);
                calculateGrandTotal(tbodyId);
            });

            // ===== REAL-TIME INPUT LIMIT ALERT LISTENERS =====
            let alertShownPrNo = false;
            $(document).on('input', '#tambah_nomor_pr', function () {
                let val = $(this).val();
                if (val.length >= 15 && !alertShownPrNo) {
                    alertShownPrNo = true;
                    Swal.fire({
                        title: 'Batas Karakter Terlampaui!',
                        html: 'Nomor PR manual dibatasi <b>maksimal 15 karakter</b>.',
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { alertShownPrNo = false; });
                }
            });

            let alertShownHeaderKet = false;
            $(document).on('input', '#tambah_keterangan, #edit_keterangan', function () {
                let val = $(this).val();
                if (val.length >= 40 && !alertShownHeaderKet) {
                    alertShownHeaderKet = true;
                    Swal.fire({
                        title: 'Batas Karakter Terlampaui!',
                        html: 'Keterangan / alasan pengadaan dibatasi <b>maksimal 40 karakter</b>.',
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { alertShownHeaderKet = false; });
                }
            });

            let alertShownItemKet = false;
            $(document).on('input', '.input-keterangan', function () {
                let val = $(this).val();
                if (val.length >= 40 && !alertShownItemKet) {
                    alertShownItemKet = true;
                    Swal.fire({
                        title: 'Batas Karakter Terlampaui!',
                        html: 'Catatan item dibatasi <b>maksimal 40 karakter</b>.',
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { alertShownItemKet = false; });
                }
            });

            // ===== EXPORT EXCEL PR WITH 30-DAY LIMIT VALIDATION & ALERTS =====
            $(document).on('click', '#btn-export-excel-pr', function (e) {
                e.preventDefault();

                let tglMulai   = $('#filter_tgl_mulai').val();
                let tglSelesai = $('#filter_tgl_selesai').val();
                let status     = $('#filter_status').val();
                let departemen = $('#filter_departemen').val();

                if (!tglMulai || !tglSelesai) {
                    Swal.fire({
                        title: 'Rentang Tanggal Belum Lengkap!',
                        text: 'Harap tentukan Tanggal Mulai dan Tanggal Selesai (Maksimal 30 hari) untuk mengunduh Export Excel.',
                        icon: 'warning',
                        confirmButtonText: 'Pilih Tanggal'
                    });
                    return false;
                }

                let start = new Date(tglMulai);
                let end   = new Date(tglSelesai);

                if (start > end) {
                    Swal.fire({
                        title: 'Tanggal Tidak Valid!',
                        text: 'Tanggal Mulai tidak boleh lebih besar dari Tanggal Selesai.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                let diffTime = Math.abs(end - start);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                if (diffDays > 30) {
                    let formattedStart = formatDateIndo(tglMulai);
                    let formattedEnd   = formatDateIndo(tglSelesai);

                    Swal.fire({
                        title: 'Batas Export Terlampaui!',
                        html: '<div class="text-start mt-2 p-3 bg-light rounded border">' +
                              '<p class="mb-2 text-dark">Batas maksimal export data PR adalah <b>30 hari</b>.</p>' +
                              '<ul class="mb-0 text-danger small ps-3">' +
                              '<li>Rentang dipilih: <b>' + diffDays + ' hari</b> (' + formattedStart + ' s/d ' + formattedEnd + ')</li>' +
                              '<li>Keterangan: Melebihi batas sistem (' + (diffDays - 30) + ' hari lebih panjang)</li>' +
                              '</ul></div>' +
                              '<p class="small text-muted mt-2 mb-0"><i class="bi bi-info-circle me-1"></i> Silakan persempit Tanggal Mulai dan Tanggal Selesai Anda.</p>',
                        icon: 'error',
                        confirmButtonText: 'Tutup & Perbaiki'
                    });
                    return false;
                }

                function formatDateIndo(dateStr) {
                    if (!dateStr) return '';
                    let p = dateStr.split('-');
                    return p[2] + '-' + p[1] + '-' + p[0];
                }

                let formattedStart = formatDateIndo(tglMulai);
                let formattedEnd   = formatDateIndo(tglSelesai);

                $('#modalFilterPR').modal('hide');

                Swal.fire({
                    title: 'Export Berhasil!',
                    text: 'File Export PR periode ' + formattedStart + ' s/d ' + formattedEnd + ' sedang diunduh.',
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });

                let exportUrl = "{{ route('pr.export') }}" +
                    "?tgl_mulai=" + encodeURIComponent(tglMulai) +
                    "&tgl_selesai=" + encodeURIComponent(tglSelesai) +
                    "&status=" + encodeURIComponent(status || '') +
                    "&departemen=" + encodeURIComponent(departemen || '');

                window.location.href = exportUrl;
            });
        });
    </script>
    @endpush
</x-layouts>
