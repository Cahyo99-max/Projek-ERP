// ===== INISIALISASI DATATABLES =====
const tableBarang = $('#tableBarang').DataTable({
    processing: true,
    serverSide: true,
    language: {
        @if(app()->getLocale() == 'id')
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(difilter dari _MAX_ total entri)",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            emptyTable: "Tidak ada data di dalam tabel",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        @else
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                previous: "Previous",
                next: "Next",
                last: "Last"
            }
        @endif
    },
    ajax: {
        url: "{{ route('barang.data') }}",
        data: function(d) {
            d.tipe_filter = activeFilter;
            d.kategori_id = activeKategoriFilter;
        }
    },
    columns: [
        { 
            data: null, 
            className: 'text-center py-3',
            orderable: false,
            render: function (data, type, row, meta) {
                return meta.row + 1 + meta.settings._iDisplayStart;
            }
        },
        { 
            data: 'nama_barang',
            className: 'py-3',
            render: function (data, type, row) {
                let typeBadge = '';
                if (row.tipe_item === 'Stok') {
                    typeBadge = '<span class="brg-tipe-badge brg-tipe-stok">Stok</span>';
                } else if (row.tipe_item === 'Bundle') {
                    typeBadge = '<span class="brg-tipe-badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded small" style="font-size:0.72rem;font-weight:600;">Set / Bundle</span>';
                } else if (row.tipe_item === 'Aset') {
                    typeBadge = '<span class="brg-tipe-badge brg-tipe-aset">Aset Tetap</span>';
                } else {
                    typeBadge = '<span class="brg-tipe-badge brg-tipe-jasa">Jasa</span>';
                }

                let warnaBadge = '';
                if (row.warna_name && row.warna_name !== '-') {
                    warnaBadge = `<span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center" style="font-size:0.72rem;font-weight:500;">${row.warna_name}</span>`;
                }

                let aliasSpan = row.nama_alias ? `<span class="brg-product-alias">${row.nama_alias}</span>` : '';

                let variantsHtml = '';
                if (row.variants && row.variants.length > 0) {
                    let chips = '';
                    row.variants.slice(0, 4).forEach(v => {
                        chips += `<span class="brg-size-chip" title="Warna: ${v.warna_name}">${v.ukuran}</span>`;
                    });
                    let more = '';
                    if (row.variants.length > 4) {
                        more = `<span class="brg-size-chip-more">+${row.variants.length - 4}</span>`;
                    }
                    variantsHtml = `
                        <div class="mt-1.5 d-flex align-items-center flex-wrap gap-1">
                            <span class="badge bg-secondary-subtle text-secondary py-1 px-2 border d-inline-flex align-items-center" style="font-size:0.68rem;font-weight:600; border-radius: 4px;">
                                <i class="bi bi-tags-fill me-1 text-secondary"></i>${row.variants.length} Varian
                            </span>
                            ${chips}
                            ${more}
                        </div>
                    `;
                }

                let componentsHtml = '';
                if (row.tipe_item === 'Bundle' && row.components && row.components.length > 0) {
                    componentsHtml = `
                        <div class="mt-1">
                            <span class="badge bg-primary-subtle text-primary py-1 px-2 border" style="font-size:0.68rem;font-weight:500;">
                                <i class="bi bi-box-seam me-1 text-primary"></i>Set: ${row.components.length} Komponen
                            </span>
                        </div>
                    `;
                }

                let trackingBadge = '';
                if (row.tipe_pelacakan === 'Serial Number') {
                    trackingBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 small" style="font-size:0.68rem;"><i class="bi bi-upc me-1"></i>SN</span>';
                } else if (row.tipe_pelacakan === 'Batch') {
                    trackingBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-1.5 py-0.5 small" style="font-size:0.68rem;"><i class="bi bi-collection me-1"></i>Lot</span>';
                }

                let konversiBadge = '';
                if (row.konversi_satuan && row.konversi_satuan.length > 0) {
                    let k0 = row.konversi_satuan[0];
                    let moreK = row.konversi_satuan.length > 1 ? ` (+${row.konversi_satuan.length - 1})` : '';
                    let cleanFaktor = parseFloat(k0.faktor_pengali) || 0;
                    konversiBadge = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 small" style="font-size:0.68rem;" title="1 ${k0.singkatan} = ${cleanFaktor} ${row.satuan_name}"><i class="bi bi-arrow-left-right me-1"></i>1 ${k0.singkatan}=${cleanFaktor} ${row.satuan_name}${moreK}</span>`;
                }

                return `
                    <div class="brg-product-cell">
                        <div class="brg-product-info">
                            <span class="brg-product-name" title="${row.nama_barang}">${row.nama_barang}</span>
                            <div class="brg-product-meta align-items-center flex-wrap gap-1 mt-1">
                                ${typeBadge}
                                ${trackingBadge}
                                ${konversiBadge}
                                ${warnaBadge}
                                ${aliasSpan}
                            </div>
                            ${variantsHtml}
                            ${componentsHtml}
                        </div>
                    </div>
                `;
            }
        },
        { 
            data: 'kode_barang',
            className: 'py-3',
            render: function (data, type, row) {
                return `
                    <div class="brg-kode-cell">
                        <span class="brg-kode-badge">${row.kode_barang}</span>
                        <button class="brg-qr-btn btn-qr-code"
                            data-kode="${row.kode_barang}"
                            data-nama="${row.nama_barang}"
                            title="Lihat QR Code">
                            <i class="bi bi-qr-code"></i>
                        </button>
                    </div>
                `;
            }
        },
        { 
            data: 'kategori_name',
            className: 'py-3',
            render: function (data, type, row) {
                return `
                    <div class="brg-info-cell">
                        <span class="brg-info-main">${row.kategori_name}</span>
                        <span class="brg-info-sub">${row.brand_name}</span>
                    </div>
                `;
            }
        },
        { 
            data: 'satuan_name',
            className: 'py-3',
            render: function (data, type, row) {
                return `
                    <div class="brg-info-cell">
                        <span class="brg-info-main">${row.satuan_name}</span>
                        <span class="brg-info-sub">${row.tipe_barang_name}</span>
                    </div>
                `;
            }
        },
        { 
            data: 'status',
            className: 'text-center py-3',
            render: function (data, type, row) {
                let statusClass = row.status === 'Active' ? 'brg-status-active' : 'brg-status-inactive';
                let statusText = row.status === 'Active' ? 'Aktif' : 'Nonaktif';
                return `
                    <span class="brg-status ${statusClass}">
                        <span class="brg-status-dot"></span>
                        ${statusText}
                    </span>
                `;
            }
        },
        { 
            data: null,
            orderable: false,
            className: 'text-center py-3',
            render: function (data, type, row) {
                let canEdit = @json(auth()->user()->can('edit-barang'));
                let canDelete = @json(auth()->user()->can('hapus-barang'));
                
                let editBtn = canEdit ? `<button class="brg-action-btn brg-act-edit btn-edit" data-id="${row.id}" title="Edit"><i class="bi bi-pencil"></i></button>` : '';
                let deleteBtn = canDelete ? `<button class="brg-action-btn brg-act-delete btn-hapus" data-id="${row.id}" data-nama="${row.nama_barang}" title="Hapus"><i class="bi bi-trash"></i></button>` : '';
                
                return `
                    <div class="brg-actions">
                        <button class="brg-action-btn brg-act-detail btn-detail" data-id="${row.id}" title="Detail Fisik"><i class="bi bi-eye"></i></button>
                        ${editBtn}
                        ${deleteBtn}
                    </div>
                `;
            }
        }
    ],
    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50, 100],
    createdRow: function (row, data, dataIndex) {
        $(row).attr('data-tipe-item', data.tipe_item);
    }
});

// ===== INJECT TAB FILTER PILLS & KATEGORI FILTER =====
@php
    $catOptions = '';
    if(isset($categoriesList)) {
        foreach($categoriesList as $c) {
            $catOptions .= '<option value="' . $c->id . '">' . e($c->nama_kategori) . '</option>';
        }
    }
@endphp

const tabHtml = `
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 w-100" id="brgToolbarFilter">
        <div class="brg-tab-wrapper mb-0" id="brgTabFilter">
            <button class="brg-tab-pill active" data-filter="Semua">
                <i class="bi bi-grid-3x3-gap-fill"></i> Semua
                <span class="brg-tab-count" id="tab-count-all">{{ $countAll }}</span>
            </button>
            <button class="brg-tab-pill" data-filter="Stok">
                <i class="bi bi-archive"></i> Stok
                <span class="brg-tab-count" id="tab-count-stok">{{ $countStok }}</span>
            </button>
            <button class="brg-tab-pill" data-filter="Jasa">
                <i class="bi bi-gear"></i> Jasa
                <span class="brg-tab-count" id="tab-count-jasa">{{ $countJasa }}</span>
            </button>
            <button class="brg-tab-pill" data-filter="Aset">
                <i class="bi bi-building"></i> Aset
                <span class="brg-tab-count" id="tab-count-aset">{{ $countAset }}</span>
            </button>
        </div>

        <div class="brg-kategori-filter-wrapper ms-sm-auto">
            <select id="filter_kategori_barang" class="form-select form-select-sm">
                <option value="Semua">🔍 Semua Kategori</option>
                {!! $catOptions !!}
            </select>
        </div>
    </div>
`;

// Inject tabs and category filter above the DataTables top controls
$('#tableBarang_wrapper .row:first').before(tabHtml);

// Initialize Select2 with Search & Scroll bounded to filter wrapper
if ($.fn.select2) {
    $('#filter_kategori_barang').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '🔍 Cari Kategori...',
        dropdownAutoWidth: false,
        dropdownParent: $('#brgToolbarFilter')
    });
}

// Tab click handler
$(document).on('click', '.brg-tab-pill', function() {
    $('.brg-tab-pill').removeClass('active');
    $(this).addClass('active');
    activeFilter = $(this).data('filter');
    tableBarang.draw();
});

// Category dropdown change handler
$(document).on('change select2:select', '#filter_kategori_barang', function() {
    activeKategoriFilter = $(this).val();
    tableBarang.draw();
});

// Export Excel with active filters
$(document).on('click', '.btn-export-excel', function(e) {
    e.preventDefault();
    let url = new URL("{{ route('barang.export') }}", window.location.origin);
    if (activeFilter && activeFilter !== 'Semua') {
        url.searchParams.set('tipe_filter', activeFilter);
    }
    if (activeKategoriFilter && activeKategoriFilter !== 'Semua') {
        url.searchParams.set('kategori_id', activeKategoriFilter);
    }
    window.location.href = url.toString();
});
