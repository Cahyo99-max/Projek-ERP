<x-layouts title="Data Jenis Gudang">

    @push('styles')
    <style>
        #tableJenisGudang tbody td { vertical-align: middle; }
        .jg-nama { font-weight: 600; color: var(--bs-body-color); }
        .jg-desk { color: var(--bs-secondary-color, #6c757d); font-size: 0.875rem;
                   max-width: 250px; white-space: nowrap; overflow: hidden;
                   text-overflow: ellipsis; display: block; }
        .jg-urutan {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 50rem;
            font-size: 0.82rem;
            font-weight: 700;
            background: rgba(13, 110, 253, 0.25);
            color: #6ea8fe;
            border: 1px solid rgba(13, 110, 253, 0.4);
            min-width: 36px;
            text-align: center;
        }
    </style>
    @endpush

    <x-table_card tableId="tableJenisGudang" title="Data Jenis Gudang" icon="bi-grid-3x3-gap"
        btnTarget="{{ auth()->user()->can('tambah-jenis-gudang') ? '#modalTambahJenisGudang' : '' }}"
        btnText="Tambah Jenis Gudang">

        <x-slot name="extraActions">
            @can('lihat-jenis-gudang')
                <a href="{{ route('jenis-gudang.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-jg" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-jenis-gudang')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportJenisGudang">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Jenis</th>
            <th class="py-3">Nama Jenis Gudang</th>
            <th class="py-3 text-center" width="10%">Urutan</th>
            <th class="py-3">Deskripsi</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>

    @include('master_gudang.jenis_gudang.tambah')
    @include('master_gudang.jenis_gudang.edit')
    @include('master_gudang.jenis_gudang.import')

    @push('scripts')
    <script>
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

        let tableJenisGudang;

        $(document).ready(function () {
            // Setup character limit warnings
            setupCharLimitPopup('#tambah_nama_jenis_gudang, #edit_nama_jenis_gudang', 30, 'Nama Jenis Gudang');
            setupCharLimitPopup('#tambah_deskripsi, #edit_deskripsi', 30, 'Deskripsi');

            // ===== DATATABLES SERVER-SIDE AJAX =====
            tableJenisGudang = $('#tableJenisGudang').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('jenis-gudang.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'no',                orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_jenis_gudang', orderable: true,  searchable: true },
                    { data: 'nama_jenis_gudang', orderable: true,  searchable: true },
                    { data: 'urutan',            orderable: true,  searchable: false, className: 'text-center' },
                    { data: 'deskripsi',         orderable: false, searchable: true },
                    { data: 'status',            orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,                orderable: false, searchable: false, className: 'text-center' },
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
                            return '<span class="jg-nama">' + escapeHtml(data) + '</span>';
                        }
                    },
                    {
                        targets: 3,
                        render: function (data) {
                            return data
                                ? '<span class="jg-urutan">' + escapeHtml(data) + '</span>'
                                : '<span style="color:#6c757d">—</span>';
                        }
                    },
                    {
                        targets: 4,
                        render: function (data) {
                            return data
                                ? '<span class="jg-desk" title="' + escapeHtml(data) + '">' + escapeHtml(data) + '</span>'
                                : '<span style="color:#6c757d">—</span>';
                        }
                    },
                    {
                        targets: 5,
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
                        targets: 6,
                        render: function (data, type, row) {
                            let btns = '<div class="d-flex gap-1 justify-content-center">';
                            @can('edit-jenis-gudang')
                            btns += '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" '
                                + 'data-id="' + row.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></button>';
                            @endcan
                            @can('hapus-jenis-gudang')
                            btns += '<button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-hapus" '
                                + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_jenis_gudang) + '" title="Hapus">'
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
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-grid-3x3-gap fs-2 d-block mb-2 opacity-25"></i>Belum ada data jenis gudang.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[3, 'asc']]
            });

            // ===== HAPUS =====
            $(document).on('click', '.btn-hapus', function () {
                let id   = parseInt($(this).data('id'));
                let nama = $(this).data('nama');
                if (!id) return;

                Swal.fire({
                    title: 'Hapus Jenis Gudang?',
                    html: 'Jenis Gudang <strong>' + escapeHtml(nama) + '</strong> akan dihapus permanen.<br>'
                        + '<small class="text-muted">Jenis gudang yang masih digunakan oleh data gudang tidak dapat dihapus.</small>',
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
                            url: "{{ url('/jenis-gudang') }}/" + id + "/delete",
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => { tableJenisGudang.ajax.reload(null, false); });
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
            $(document).on('click', '.btn-export-jg', function () {
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
