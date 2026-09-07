<x-layouts title="Data Karyawan (HRD)">

    @push('styles')
    <style>
        #tableKaryawan tbody td { vertical-align: middle; }
        .kry-nama { font-weight: 600; color: var(--bs-body-color); }
        .kry-detail-label { font-size: 0.8rem; color: var(--bs-secondary-color, #6c757d); }

        /* Premium & Professional Summary Cards */
        .premium-card {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease;
            border-radius: 20px !important;
            overflow: hidden;
            background-color: var(--bs-body-bg) !important;
            border-width: 1.5px !important;
        }
        .premium-card:hover {
            transform: translateY(-6px);
        }
        .card-total {
            border-color: rgba(99, 102, 241, 0.18) !important;
            background: linear-gradient(145deg, rgba(99, 102, 241, 0.06) 0%, rgba(99, 102, 241, 0.01) 100%) !important;
        }
        .card-total:hover {
            border-color: rgba(99, 102, 241, 0.45) !important;
            box-shadow: 0 15px 30px -10px rgba(99, 102, 241, 0.25) !important;
        }
        .card-aktif {
            border-color: rgba(16, 185, 129, 0.18) !important;
            background: linear-gradient(145deg, rgba(16, 185, 129, 0.06) 0%, rgba(16, 185, 129, 0.01) 100%) !important;
        }
        .card-aktif:hover {
            border-color: rgba(16, 185, 129, 0.45) !important;
            box-shadow: 0 15px 30px -10px rgba(16, 185, 129, 0.25) !important;
        }
        .card-nonaktif {
            border-color: rgba(239, 68, 68, 0.18) !important;
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.06) 0%, rgba(239, 68, 68, 0.01) 100%) !important;
        }
        .card-nonaktif:hover {
            border-color: rgba(239, 68, 68, 0.45) !important;
            box-shadow: 0 15px 30px -10px rgba(239, 68, 68, 0.25) !important;
        }
        .icon-glow-total {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.65);
        }
        .icon-glow-aktif {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.65);
        }
        .icon-glow-nonaktif {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.65);
        }
    </style>
    @endpush

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card premium-card card-total h-100 shadow-sm">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-4 icon-glow-total text-white" style="width: 58px; height: 58px; flex-shrink: 0;">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">Total Karyawan</p>
                        <h3 class="mb-0 fw-extrabold" id="summary-total" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $totalKaryawan }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card premium-card card-aktif h-100 shadow-sm">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-4 icon-glow-aktif text-white" style="width: 58px; height: 58px; flex-shrink: 0;">
                        <i class="bi bi-person-check fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">Karyawan Aktif</p>
                        <h3 class="mb-0 fw-extrabold" id="summary-aktif" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $aktifKaryawan }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card premium-card card-nonaktif h-100 shadow-sm">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-4 icon-glow-nonaktif text-white" style="width: 58px; height: 58px; flex-shrink: 0;">
                        <i class="bi bi-person-dash fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">Karyawan Nonaktif</p>
                        <h3 class="mb-0 fw-extrabold" id="summary-nonaktif" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $nonaktifKaryawan }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-table_card tableId="tableKaryawan" title="Data Karyawan (HRD)" icon="bi-person-badge"
        btnTarget="{{ auth()->user()->can('tambah-karyawan') ? '#modalTambahKaryawan' : '' }}"
        btnText="Tambah Karyawan">

        <x-slot name="extraActions">
            @can('lihat-karyawan')
                <a href="{{ route('master-karyawan.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-kry" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-karyawan')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportKaryawan">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3 text-center" width="8%">Foto</th>
            <th class="py-3">NIK</th>
            <th class="py-3">Kode Karyawan</th>
            <th class="py-3">Nama Lengkap</th>
            <th class="py-3">Departemen</th>
            <th class="py-3">Jabatan</th>
            <th class="py-3">No. HP</th>
            <th class="py-3 text-center">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>

    @include('master_organisasi.karyawan.tambah')
    @include('master_organisasi.karyawan.edit')
    @include('master_organisasi.karyawan.detail')
    @include('master_organisasi.karyawan.import')

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

        let tableKaryawan;

        $(document).ready(function () {

            // Auto-uppercase & max 4 chars untuk Inisial
            $('#tambah_inisial, #edit_inisial').on('input', function() {
                let val = $(this).val().toUpperCase().replace(/[^A-Z]/g, '');
                if (val.length > 4) val = val.substring(0, 4);
                $(this).val(val);
            });

            // Digits-only untuk No HP
            $('#tambah_no_hp, #edit_no_hp').on('input', function() {
                let val = $(this).val().replace(/[^0-9]/g, '');
                if (val.length > 13) val = val.substring(0, 13);
                $(this).val(val);
            });

            // ===== DATATABLES SERVER-SIDE AJAX =====
            tableKaryawan = $('#tableKaryawan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('master-karyawan.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'no',            orderable: false, searchable: false, className: 'text-center' },
                    { data: 'foto',          orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nik',           orderable: true,  searchable: true },
                    { data: 'kode_karyawan', orderable: true,  searchable: true },
                    { data: 'nama_lengkap',  orderable: true,  searchable: true },
                    { data: 'department_name', orderable: true, searchable: true },
                    { data: 'position_name', orderable: true,  searchable: true },
                    { data: 'no_hp',         orderable: true,  searchable: true },
                    { data: 'status',        orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,            orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    {
                        targets: 1,
                        render: function (data) {
                            return '<img src="' + escapeHtml(data) + '" class="rounded-circle border" width="36" height="36" style="object-fit: cover;" alt="Foto">';
                        }
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            return data
                                ? '<span class="text-secondary fw-medium">' + escapeHtml(data) + '</span>'
                                : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        targets: 3,
                        render: function (data) {
                            return data
                                ? '<span class="badge bg-secondary">' + escapeHtml(data) + '</span>'
                                : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        targets: 4,
                        render: function (data, type, row) {
                            let initBadge = row.inisial ? ' <span class="badge bg-info bg-opacity-10 text-info fw-semibold ms-1 px-2 py-0.5 rounded-pill" style="font-size:0.75rem;">' + escapeHtml(row.inisial) + '</span>' : '';
                            return '<span class="kry-nama">' + escapeHtml(data) + '</span>' + initBadge;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data) {
                            return data ? escapeHtml(data) : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        targets: 6,
                        render: function (data) {
                            return data ? escapeHtml(data) : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        targets: 7,
                        render: function (data) {
                            return data ? escapeHtml(data) : '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        targets: 8,
                        render: function (data) {
                            if (data === 'Aktif') {
                                return '<span class="badge bg-success rounded-pill px-3 py-2">'
                                    + '<i class="bi bi-check-circle-fill me-1"></i>Aktif</span>';
                            }
                            return '<span class="badge bg-danger rounded-pill px-3 py-2">'
                                + '<i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>';
                        }
                    },
                    {
                        targets: 9,
                        render: function (data, type, row) {
                            let btns = '<div class="d-flex gap-2 justify-content-center">';
                            btns += '<button class="btn btn-sm btn-light text-info rounded-circle shadow-sm btn-view-karyawan border-0" '
                                + 'style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" '
                                + 'data-id="' + row.id + '" title="Detail">'
                                + '<i class="bi bi-eye"></i></button>';

                            @can('edit-karyawan')
                            btns += '<button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm btn-edit-karyawan border-0" '
                                + 'style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" '
                                + 'data-id="' + row.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></button>';
                            @endcan

                            @can('hapus-karyawan')
                            btns += '<button class="btn btn-sm btn-light text-danger rounded-circle shadow-sm btn-hapus-karyawan border-0" '
                                + 'style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" '
                                + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_lengkap) + '" title="Hapus">'
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
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-person-badge fs-2 d-block mb-2 opacity-25"></i>Belum ada data karyawan.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[4, 'asc']]
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
                    tableKaryawan.search(this.value).draw();
                }, 400));

            // ===== HAPUS KARYAWAN =====
            $(document).on('click', '.btn-hapus-karyawan', function () {
                let id   = parseInt($(this).data('id'));
                let nama = $(this).data('nama');
                if (!id) return;

                Swal.fire({
                    title: 'Hapus Data Karyawan?',
                    html: 'Karyawan <strong>' + escapeHtml(nama) + '</strong> akan dihapus permanen.<br>'
                        + '<small class="text-danger">Tindakan ini juga menghapus akun login terkait jika ada.</small>',
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
                            url: '/karyawan/' + id + '/delete',
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => { 
                                    tableKaryawan.ajax.reload(null, false); 
                                    location.reload(); 
                                });
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
            $(document).on('click', '.btn-export-kry', function () {
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
