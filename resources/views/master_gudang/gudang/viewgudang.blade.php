<x-layouts title="Data Gudang">

    @push('styles')
    <style>
        #tableGudang tbody td { vertical-align: middle; }
        .gdg-nama { font-weight: 600; color: var(--bs-body-color); }
        .gdg-text { color: var(--bs-body-color); }
        .gdg-sub { color: var(--bs-secondary-color, #6c757d); font-size: 0.8rem; display: block; }
        
        /* Custom badges for backward compatibility and dark mode visibility */
        .badge-jg {
            background-color: rgba(13, 202, 240, 0.15) !important;
            color: #0dcaf0 !important;
            border: 1px solid rgba(13, 202, 240, 0.3) !important;
        }
        .badge-rak-aktif {
            background-color: rgba(25, 135, 84, 0.15) !important;
            color: #198754 !important;
            border: 1px solid rgba(25, 135, 84, 0.3) !important;
        }
        .badge-rak-tidak, .badge-stok-proteksi {
            background-color: rgba(108, 117, 125, 0.15) !important;
            color: #adb5bd !important;
            border: 1px solid rgba(108, 117, 125, 0.3) !important;
        }
        .badge-stok-izinkan {
            background-color: rgba(255, 193, 7, 0.15) !important;
            color: #ffc107 !important;
            border: 1px solid rgba(255, 193, 7, 0.3) !important;
        }

        /* DataTables dark mode text visibility */
        .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
            color: var(--bs-body-color) !important;
        }
        .dataTables_length select, .dataTables_filter input {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
            border: 1px solid var(--bs-border-color) !important;
        }
    </style>
    @endpush

    <x-table_card tableId="tableGudang" title="Data Gudang" icon="bi-house-gear"
        btnTarget="{{ auth()->user()->can('tambah-gudang') ? '#modalTambahGudang' : '' }}"
        btnText="Tambah Gudang">

        <x-slot name="extraActions">
            @can('lihat-gudang')
                <a href="{{ route('gudang.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-gdg" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-gudang')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportGudang">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Gudang</th>
            <th class="py-3">Nama Gudang</th>
            <th class="py-3">Cabang</th>
            <th class="py-3">Jenis Gudang</th>
            <th class="py-3">Penanggung Jawab</th>
            <th class="py-3 text-center">Lokasi Rak</th>
            <th class="py-3 text-center">Stok Negatif</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>

    @include('master_gudang.gudang.tambah')
    @include('master_gudang.gudang.edit')
    @include('master_gudang.gudang.import')

    @push('scripts')
    <script>
        let tableGudang;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ===== HELPER: Popup peringatan saat mencapai batas karakter =====
        let _charPopupCooldown = {};

        function showCharLimitPopup(selector, fieldLabel, maxLen) {
            if (_charPopupCooldown[selector]) return;
            _charPopupCooldown[selector] = true;
            Swal.fire({
                title: '⚠️ Batas Karakter Tercapai!',
                html: 'Kolom <b>' + fieldLabel + '</b> hanya boleh <b>' + maxLen + '</b> karakter.<br><small class="text-muted">Input lebih tidak akan diterima.</small>',
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#6366f1',
                showClass: { popup: 'animate__animated animate__shakeX' }
            }).then(() => {
                _charPopupCooldown[selector] = false;
            });
        }

        function setupCharLimitPopup(selector, maxLen, fieldLabel) {
            $(document).on('keydown paste', selector, function(e) {
                const el = this;
                const isPaste = e.type === 'paste';
                if (!isPaste) {
                    const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
                    if (!isPrintable) return;
                }
                const start = el.selectionStart;
                const end = el.selectionEnd;
                const currentLen = el.value.length - (end - start);
                if (currentLen >= maxLen) {
                    e.preventDefault();
                    showCharLimitPopup(selector, fieldLabel, maxLen);
                }
            });
        }

        $(document).ready(function () {
            // Setup character limit warnings
            setupCharLimitPopup('#tambah_nama_gudang, #edit_nama_gudang', 30, 'Nama Gudang');
            setupCharLimitPopup('#tambah_email, #edit_email', 50, 'Email Gudang');
            setupCharLimitPopup('#tambah_alamat, #edit_alamat', 60, 'Alamat Gudang');
            setupCharLimitPopup('#tambah_keterangan, #edit_keterangan', 30, 'Keterangan');

            // ===== Digit-only, max 13 – No. Telepon =====
            $(document).on('keydown', '#tambah_telepon, #edit_telepon', function(e) {
                const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
                if (!isPrintable) return;
                if (!/[0-9]/.test(e.key)) { e.preventDefault(); return; }
                const start = this.selectionStart;
                const end   = this.selectionEnd;
                const currentLen = this.value.length - (end - start);
                if (currentLen >= 13) {
                    e.preventDefault();
                    showCharLimitPopup('#tambah_telepon, #edit_telepon', 'No. Telepon', 13);
                }
            });
            $(document).on('input', '#tambah_telepon, #edit_telepon', function() {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 13);
            });

            // ===== DATATABLES SERVER-SIDE AJAX =====
            tableGudang = $('#tableGudang').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('gudang.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'no',                  orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_gudang',         orderable: true,  searchable: true },
                    { data: 'nama_gudang',         orderable: true,  searchable: true },
                    { data: 'cabang_nama',         orderable: true,  searchable: true },
                    { data: 'jenis_gudang_nama',   orderable: true,  searchable: true },
                    { data: 'penanggung_jawab_nama', orderable: true, searchable: true },
                    { data: 'gunakan_lokasi_rak',  orderable: true,  searchable: false, className: 'text-center' },
                    { data: 'izinkan_stok_negatif',orderable: true,  searchable: false, className: 'text-center' },
                    { data: 'status',              orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,                  orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    {
                        targets: 1,
                        render: function (data) {
                            return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'
                                + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            return '<span class="gdg-nama">' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        targets: 3,
                        render: function (data, type, row) {
                            if (row.is_pt_pusat) {
                                return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2 py-1 rounded-pill"><i class="bi bi-building me-1"></i>' + escapeHtml(data) + '</span>';
                            }
                            return '<span class="gdg-text"><i class="bi bi-geo-alt me-1 text-secondary"></i>' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        targets: 4,
                        render: function (data) {
                            return data
                                ? '<span class="badge badge-jg rounded-pill px-3 py-1 fw-semibold">' + escapeHtml(data) + '</span>'
                                : '<span style="color:#6c757d">—</span>';
                        }
                    },
                    {
                        targets: 5,
                        render: function (data) {
                            return data
                                ? '<span class="gdg-text"><i class="bi bi-person me-1 text-primary"></i>' + escapeHtml(data) + '</span>'
                                : '<span style="color:#6c757d">—</span>';
                        }
                    },
                    {
                        targets: 6,
                        render: function (data) {
                            if (data) {
                                return '<span class="badge badge-rak-aktif rounded-pill px-3 py-1 fw-medium"><i class="bi bi-check-circle me-1"></i>Aktif</span>';
                            }
                            return '<span class="badge badge-rak-tidak rounded-pill px-3 py-1 fw-medium"><i class="bi bi-x-circle me-1"></i>Tidak</span>';
                        }
                    },
                    {
                        targets: 7,
                        render: function (data) {
                            if (data) {
                                return '<span class="badge badge-stok-izinkan rounded-pill px-3 py-1 fw-medium"><i class="bi bi-exclamation-triangle me-1"></i>Izinkan</span>';
                            }
                            return '<span class="badge badge-stok-proteksi rounded-pill px-3 py-1 fw-medium"><i class="bi bi-shield-check me-1"></i>Proteksi</span>';
                        }
                    },
                    {
                        targets: 8,
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
                        targets: 9,
                        render: function (data, type, row) {
                            let btns = '<div class="d-flex gap-1 justify-content-center">';
                            @can('edit-gudang')
                            btns += '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" '
                                + 'data-id="' + row.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></button>';
                            @endcan
                            @can('hapus-gudang')
                            btns += '<button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-hapus" '
                                + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_gudang) + '" title="Hapus">'
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
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-house-gear fs-2 d-block mb-2 opacity-25"></i>Belum ada data gudang.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[2, 'asc']]
            });

            // ===== HAPUS =====
            $(document).on('click', '.btn-hapus', function () {
                let id   = parseInt($(this).data('id'));
                let nama = $(this).data('nama');
                if (!id) return;

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    html: 'Gudang <strong>' + escapeHtml(nama) + '</strong> akan dihapus permanen.<br>'
                        + '<small class="text-muted">Gudang yang masih memiliki stok barang atau riwayat transaksi tidak dapat dihapus.</small>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('/gudang') }}/" + id + "/delete",
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => { tableGudang.ajax.reload(null, false); });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data.';
                                if (err.status === 422 && err.responseJSON && err.responseJSON.message) {
                                    errorMsg = escapeHtml(err.responseJSON.message);
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
            $(document).on('click', '.btn-export-gdg', function () {
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
