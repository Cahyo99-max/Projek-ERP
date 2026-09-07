<x-layouts title="Data Kategori Barang">

    @push('styles')
    <style>
        /* ===== KATEGORI TABLE — DARK MODE SAFE STYLES ===== */
        #tableKategori tbody td {
            vertical-align: middle;
        }
        /* Force nama kategori text visible in dark mode */
        #tableKategori .kat-nama {
            font-weight: 600;
            color: var(--bs-body-color);
        }
        /* Parent Utama badge — border style, works on dark + light */
        .badge-parent-utama {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 50rem;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1.5px solid #6c757d;
            color: #8a9ab5;
            background: transparent;
        }
        /* Parent child badge — cyan/info tint */
        .badge-parent-child {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 50rem;
            font-size: 0.78rem;
            font-weight: 600;
            background: rgba(13, 202, 240, 0.18);
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.35);
        }
        /* Urutan badge — bright blue, visible both modes */
        .badge-urutan {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 50rem;
            font-size: 0.82rem;
            font-weight: 700;
            background: rgba(58, 130, 246, 0.22);
            color: #60a5fa;
            border: 1px solid rgba(58, 130, 246, 0.4);
            min-width: 36px;
            text-align: center;
        }
    </style>
    @endpush

    <x-table_card tableId="tableKategori" title="Data Kategori Barang" icon="bi-tags"
        btnTarget="{{ auth()->user()->can('tambah-kategori-barang') ? '#modalTambahKategori' : '' }}"
        btnText="Tambah Kategori">

        <x-slot name="extraActions">
            @can('lihat-kategori-barang')
                <a href="{{ route('kategori-barang.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-excel" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-kategori-barang')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportKategori">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Kategori</th>
            <th class="py-3">Nama Kategori</th>
            <th class="py-3">Kategori Induk (Parent)</th>
            <th class="py-3 text-center" width="7%">Urutan</th>
            <th class="py-3">Deskripsi</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th class="py-3 text-center" width="12%">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>


    {{-- ===== MODALS PARTIALS ===== --}}
    @include('data_master.kategori_barang.tambah')
    @include('data_master.kategori_barang.edit')
    @include('data_master.kategori_barang.import')


    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // ===== XSS PROTECTION HELPER =====
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        let tableKategori;

        $(document).ready(function () {

            // ===== DATATABLES SERVER-SIDE AJAX =====
            tableKategori = $('#tableKategori').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('kategori-barang.data') }}",
                    type: 'GET',
                    error: function (xhr, error, thrown) {
                        console.error('DataTables AJAX error:', error, thrown);
                    }
                },
                columns: [
                    { data: 'no',            orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_kategori', orderable: true,  searchable: true  },
                    { data: 'nama_kategori', orderable: true,  searchable: true  },
                    { data: 'parent_nama',   orderable: true,  searchable: true  },
                    { data: 'urutan',        orderable: true,  searchable: false, className: 'text-center' },
                    { data: 'deskripsi',     orderable: false, searchable: true  },
                    { data: 'status',        orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,            orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    {
                        // Kode Kategori badge
                        targets: 1,
                        render: function (data) {
                            return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'
                                + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        // Nama Kategori bold — dark mode safe class
                        targets: 2,
                        render: function (data) {
                            return '<span class="kat-nama">' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        // Parent badge — dark mode safe classes
                        targets: 3,
                        render: function (data) {
                            if (data) {
                                return '<span class="badge-parent-child">'
                                    + '<i class="bi bi-arrow-return-right"></i>'
                                    + escapeHtml(data) + '</span>';
                            }
                            return '<span class="badge-parent-utama">'
                                + '<i class="bi bi-house-door"></i>Utama</span>';
                        }
                    },
                    {
                        // Urutan badge — bright blue, dark mode safe
                        targets: 4,
                        render: function (data) {
                            return data
                                ? '<span class="badge-urutan">' + escapeHtml(data) + '</span>'
                                : '<span style="color:#6c757d;font-size:1rem">—</span>';
                        }
                    },
                    {
                        // Deskripsi truncated
                        targets: 5,
                        render: function (data) {
                            if (!data) return '<span class="text-muted small">—</span>';
                            const safe = escapeHtml(data);
                            const short = safe.length > 60 ? safe.substring(0, 60) + '…' : safe;
                            return '<span class="text-muted" title="' + safe + '">' + short + '</span>';
                        }
                    },
                    {
                        // Status badge
                        targets: 6,
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
                        targets: 7,
                        render: function (data, type, row) {
                            let btns = '<div class="d-flex gap-1 justify-content-center">';
                            @can('edit-kategori-barang')
                            btns += '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" '
                                + 'data-id="' + row.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></button>';
                            @endcan
                            @can('hapus-kategori-barang')
                            btns += '<button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-hapus" '
                                + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_kategori) + '" title="Hapus">'
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
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-tags fs-2 d-block mb-2 opacity-25"></i>Belum ada data kategori.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data...'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[1, 'asc']]
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
                    tableKategori.search(this.value).draw();
                }, 400));

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

            // ===== HAPUS =====
            $(document).on('click', '.btn-hapus', function () {
                let id   = parseInt($(this).data('id'));
                let nama = $(this).data('nama');
                if (!id) return;

                Swal.fire({
                    title: 'Hapus Kategori?',
                    html: 'Kategori <strong>' + escapeHtml(nama) + '</strong> akan dihapus permanen.<br><small class="text-muted">Kategori yang memiliki subkategori tidak dapat dihapus.</small>',
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
                            url: "{{ url('/kategori-barang') }}/" + id + "/delete",
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    tableKategori.ajax.reload(null, false);
                                });
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

        });
    </script>
    @endpush
</x-layouts>
