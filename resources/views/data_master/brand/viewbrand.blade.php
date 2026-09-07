<x-layouts title="Data Brand / Merk Barang">

    @push('styles')
    <style>
        /* ===== BRAND TABLE — DARK MODE SAFE STYLES ===== */
        #tableBrand tbody td {
            vertical-align: middle;
        }
        .brand-nama {
            font-weight: 600;
            color: var(--bs-body-color);
        }
        .brand-desk {
            color: var(--bs-secondary-color, #6c757d);
            font-size: 0.875rem;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
    </style>
    @endpush

    <x-table_card tableId="tableBrand" title="Data Brand / Merk Barang" icon="bi-award"
        btnTarget="{{ auth()->user()->can('tambah-brand') ? '#modalTambahBrand' : '' }}"
        btnText="Tambah Brand">

        <x-slot name="extraActions">
            @can('lihat-brand')
                <a href="{{ route('brand.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-excel" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-brand')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportBrand">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Brand</th>
            <th class="py-3">Nama Brand</th>
            <th class="py-3">Deskripsi</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th class="py-3 text-center" width="12%">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>

    @include('data_master.brand.tambah')
    @include('data_master.brand.edit')
    @include('data_master.brand.import')

    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // ===== XSS PROTECTION =====
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        let tableBrand;

        $(document).ready(function () {

            // ===== DATATABLES SERVER-SIDE AJAX =====
            tableBrand = $('#tableBrand').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('brand.data') }}",
                    type: 'GET',
                    error: function (xhr) {
                        console.error('DataTables AJAX error:', xhr.statusText);
                    }
                },
                columns: [
                    { data: 'no',         orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_brand', orderable: true,  searchable: true  },
                    { data: 'nama_brand', orderable: true,  searchable: true  },
                    { data: 'deskripsi',  orderable: false, searchable: true  },
                    { data: 'status',     orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,         orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    {
                        // Kode badge
                        targets: 1,
                        render: function (data) {
                            return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'
                                + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        // Nama brand
                        targets: 2,
                        render: function (data) {
                            return '<span class="brand-nama">' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        // Deskripsi truncated
                        targets: 3,
                        render: function (data) {
                            if (!data) return '<span style="color:#6c757d">—</span>';
                            const safe = escapeHtml(data);
                            return '<span class="brand-desk" title="' + safe + '">' + safe + '</span>';
                        }
                    },
                    {
                        // Status badge
                        targets: 4,
                        render: function (data) {
                            if (data === 'Active') {
                                return '<span class="badge bg-success rounded-pill px-3 py-2">'
                                    + '<i class="bi bi-check-circle-fill me-1"></i>Aktif</span>';
                            }
                            return '<span class="badge bg-secondary rounded-pill px-3 py-2">'
                                + '<i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>';
                        }
                    },
                    {
                        // Aksi buttons
                        targets: 5,
                        render: function (data, type, row) {
                            let btns = '<div class="d-flex gap-1 justify-content-center">';
                            @can('edit-brand')
                            btns += '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" '
                                + 'data-id="' + row.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></button>';
                            @endcan
                            @can('hapus-brand')
                            btns += '<button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-hapus" '
                                + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_brand) + '" title="Hapus">'
                                + '<i class="bi bi-trash"></i></button>';
                            @endcan
                            btns += '</div>';
                            return btns;
                        }
                    }
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-award fs-2 d-block mb-2 opacity-25"></i>Belum ada data brand.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data...'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[2, 'asc']]
            });

            // Debounce search input to prevent keyup lag and database spamming (crucial for 1M+ records)
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this, args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }

            $('.dataTables_filter input')
                .unbind()
                .bind('input', debounce(function () {
                    tableBrand.search(this.value).draw();
                }, 400));

            // ===== HAPUS =====
            $(document).on('click', '.btn-hapus', function () {
                let id   = parseInt($(this).data('id'));
                let nama = $(this).data('nama');
                if (!id) return;

                Swal.fire({
                    title: 'Hapus Brand?',
                    html: 'Brand <strong>' + escapeHtml(nama) + '</strong> akan dihapus permanen.<br>'
                        + '<small class="text-muted">Brand yang masih digunakan oleh data barang tidak dapat dihapus.</small>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('/brand') }}/" + id + "/delete",
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => { tableBrand.ajax.reload(null, false); });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data.';
                                if (err.status === 422 && err.responseJSON && err.responseJSON.message) {
                                    errorMsg = escapeHtml(err.responseJSON.message);
                                } else if (err.status === 429) {
                                    errorMsg = 'Terlalu banyak permintaan. Harap tunggu sebentar.';
                                } else if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg = escapeHtml(err.responseJSON.message);
                                }
                                Swal.fire({ title: 'Gagal!', text: errorMsg, icon: 'error' });
                            }
                        });
                    }
                });
            });

            // ===== EXPORT EXCEL ALERT =====
            $(document).on('click', '.btn-export-excel', function () {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Ekspor Berhasil!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Data sedang diproses. File Excel Anda akan terunduh secara otomatis secara background.</div>',
                    icon: 'success',
                    iconColor: '#10b981',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#1e293b',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                    }
                });
            });

        });
    </script>
    @endpush
</x-layouts>
