<x-layouts title="Master Cabang">

    @push('styles')
    <style>
        #tableCabang tbody td { vertical-align: middle; }
        .cab-nama { font-weight: 600; color: var(--bs-body-color); }
        .cab-sub { color: var(--bs-secondary-color, #6c757d); font-size: 0.8rem; display: block; }
        .cab-text { color: var(--bs-body-color); }
    </style>
    @endpush

    {{-- ===== SUMMARY CARDS ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:54px;height:54px;">
                        <i class="bi bi-buildings-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">Total Cabang</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalCabang }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:54px;height:54px;">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">Cabang Aktif</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $aktifCabang }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:54px;height:54px;">
                        <i class="bi bi-x-circle-fill text-secondary fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">Cabang Nonaktif</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $nonaktifCabang }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABEL CABANG ===== --}}
    <x-table_card tableId="tableCabang" title="Data Cabang" icon="bi-buildings"
        btnTarget="{{ auth()->user()->can('tambah-cabang') ? '#modalTambahCabang' : '' }}"
        btnText="Tambah Cabang">

        <x-slot name="extraActions">
            @can('lihat-cabang')
                <a href="{{ route('branches.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-br" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-cabang')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportCabang">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="4%"  class="text-center py-3">No</th>
            <th class="py-3">Kode</th>
            <th class="py-3">Nama Cabang</th>
            <th class="py-3 d-none d-md-table-cell">Kota / Provinsi</th>
            <th class="py-3 d-none d-lg-table-cell">Kepala Cabang</th>
            <th class="py-3 d-none d-sm-table-cell">Telepon</th>
            <th class="py-3">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}

    </x-table_card>

    @include('master_gudang.cabang.tambah')
    @include('master_gudang.cabang.edit')
    @include('master_gudang.cabang.import')

    {{-- ===== MODAL DETAIL ===== --}}
    <div class="modal fade" id="modalDetailCabang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom bg-body">
                <div class="modal-header header-cinematic">
                    <h5 class="modal-title fw-semibold text-white">
                        <i class="bi bi-buildings me-2"></i> Detail Cabang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailCabangBody">
                    <div class="text-center py-4">
                        <span class="spinner-border text-primary"></span>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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

    function previewImage(input, target) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire('Ukuran Terlalu Besar', 'Maksimal ukuran logo adalah 2MB', 'warning');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                $(target).html(`<img src="${e.target.result}" class="w-100 h-100" style="object-fit:cover;">`);
                if (target === '#previewLogoEditCabang') {
                    $('#btnHapusLogoCabang').removeClass('d-none');
                    $('#edit_remove_logo_cabang').val('0');
                }
            };
            reader.readAsDataURL(file);
        }
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

    let tableCabang;

    $(document).ready(function () {

        // Restrict No Telepon and Kode Pos to digits only
        $(document).on('input keypress keydown paste', '#tambah_phone, #edit_phone, #tambah_postal_code, #edit_postal_code', function (e) {
            const isPaste = e.type === 'paste';
            const isInput = e.type === 'input';
            if (isPaste || isInput) {
                this.value = this.value.replace(/[^0-9]/g, '');
            } else if (e.type === 'keypress') {
                const charCode = (e.which) ? e.which : e.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    e.preventDefault();
                }
            }
        });

        // Setup character limit warnings
        setupCharLimitPopup('#tambah_kode_cabang, #edit_kode_cabang', 6, 'Kode Cabang');
        setupCharLimitPopup('#tambah_nama_cabang, #edit_nama_cabang', 30, 'Nama Cabang');
        setupCharLimitPopup('#tambah_nama_perusahaan, #edit_nama_perusahaan', 50, 'Nama Perusahaan');
        setupCharLimitPopup('#tambah_phone, #edit_phone', 13, 'No Telepon');
        setupCharLimitPopup('#tambah_email, #edit_email', 50, 'Email');
        setupCharLimitPopup('#tambah_address, #edit_address', 60, 'Alamat');
        setupCharLimitPopup('#tambah_city, #edit_city', 20, 'Kota');
        setupCharLimitPopup('#tambah_province, #edit_province', 20, 'Provinsi');
        setupCharLimitPopup('#tambah_postal_code, #edit_postal_code', 10, 'Kode Pos');
        setupCharLimitPopup('#tambah_manager_name, #edit_manager_name', 40, 'Nama Kepala Cabang');

        // ===== DATATABLES SERVER-SIDE AJAX =====
        tableCabang = $('#tableCabang').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('branches.data') }}",
                type: 'GET'
            },
            columns: [
                { data: 'no',           orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode_cabang',  orderable: true,  searchable: true },
                { data: 'nama_cabang',  orderable: true,  searchable: true },
                { data: 'city',         orderable: true,  searchable: true, className: 'd-none d-md-table-cell' },
                { data: 'manager_name', orderable: true,  searchable: true, className: 'd-none d-lg-table-cell' },
                { data: 'phone',        orderable: true,  searchable: true, className: 'd-none d-sm-table-cell' },
                { data: 'status',       orderable: true,  searchable: false, className: 'text-center' },
                { data: null,           orderable: false, searchable: false, className: 'text-center' },
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
                    render: function (data, type, row) {
                        let logoHtml = row.logo_url
                            ? `<img src="${row.logo_url}" class="rounded-3 border shadow-sm flex-shrink-0" style="width:38px;height:38px;object-fit:cover;">`
                            : `<div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0 border" style="width:38px;height:38px;font-size:0.85rem;"><i class="bi bi-buildings"></i></div>`;

                        let res = `<div class="d-flex align-items-center gap-2">
                            ${logoHtml}
                            <div>
                                <div class="cab-nama">${escapeHtml(data)}</div>
                                ${row.nama_perusahaan ? `<small class="cab-sub">${escapeHtml(row.nama_perusahaan)}</small>` : ''}
                            </div>
                        </div>`;
                        return res;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        if (!data) return '<span style="color:#6c757d">—</span>';
                        let loc = escapeHtml(data);
                        if (row.province) loc += ', ' + escapeHtml(row.province);
                        return '<span class="cab-text">' + loc + '</span>';
                    }
                },
                {
                    targets: 4,
                    render: function (data) {
                        return data ? '<span class="cab-text">' + escapeHtml(data) + '</span>' : '<span style="color:#6c757d">—</span>';
                    }
                },
                {
                    targets: 5,
                    render: function (data) {
                        return data ? '<span class="cab-text">' + escapeHtml(data) + '</span>' : '<span style="color:#6c757d">—</span>';
                    }
                },
                {
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
                    targets: 7,
                    render: function (data, type, row) {
                        let btns = '<div class="d-flex gap-2 justify-content-center">';
                        btns += '<button class="btn btn-sm btn-light text-info rounded-circle shadow-sm btn-view-branch border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" title="Detail"><i class="bi bi-eye"></i></button>';
                        @can('edit-cabang')
                        btns += '<button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm btn-edit-branch border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" title="Edit"><i class="bi bi-pencil"></i></button>';
                        @endcan
                        @can('hapus-cabang')
                        btns += '<button class="btn btn-sm btn-light text-danger rounded-circle shadow-sm btn-hapus-branch border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_cabang) + '" title="Hapus"><i class="bi bi-trash"></i></button>';
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
                emptyTable: '<div class="text-center text-muted py-5"><i class="bi bi-buildings fs-1 d-block mb-2 opacity-25"></i>Belum ada data cabang. Klik <strong>Tambah Cabang</strong> untuk memulai.</div>',
                zeroRecords: 'Tidak ditemukan data yang cocok',
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[2, 'asc']]
        });

        // ===== DETAIL =====
        $(document).on('click', '.btn-view-branch', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;
            $('#detailCabangBody').html('<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>');
            $('#modalDetailCabang').modal('show');

            $.get('/cabang/' + id + '/show', function (res) {
                let b = res.branch;
                let statusBadge = b.status === 'Active'
                    ? '<span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>'
                    : '<span class="badge bg-secondary rounded-pill px-3 py-2">Nonaktif</span>';

                let avatarHtml = b.logo_url
                    ? `<img src="${b.logo_url}" class="rounded-3 border shadow-sm" style="width:60px;height:60px;object-fit:cover;">`
                    : `<div class="rounded-3 d-flex align-items-center justify-content-center" style="width:60px;height:60px;background:linear-gradient(135deg,#dbeafe,#eff6ff);"><i class="bi bi-buildings-fill text-primary fs-2"></i></div>`;

                $('#detailCabangBody').html(`
                    <div class="row g-3">
                        <div class="col-12 d-flex align-items-center gap-3 mb-2">
                            ${avatarHtml}
                            <div>
                                <h5 class="mb-1 fw-bold text-body">${escapeHtml(b.nama_cabang)}</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill me-2">${escapeHtml(b.kode_cabang)}</span>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="col-12"><hr class="my-0"></div>
                        ${b.nama_perusahaan ? `<div class="col-12"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Perusahaan</small><p class="mb-0 fw-medium text-body">${escapeHtml(b.nama_perusahaan)}</p></div>` : ''}
                        <div class="col-md-6"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Telepon</small><p class="mb-0 text-body">${escapeHtml(b.phone) || '<span class=\'text-muted\'>—</span>'}</p></div>
                        <div class="col-md-6"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Email</small><p class="mb-0 text-body">${escapeHtml(b.email) || '<span class=\'text-muted\'>—</span>'}</p></div>
                        <div class="col-12"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Alamat</small><p class="mb-0 text-body">${escapeHtml(b.address) || '<span class=\'text-muted\'>—</span>'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Kota</small><p class="mb-0 text-body">${escapeHtml(b.city) || '—'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Provinsi</small><p class="mb-0 text-body">${escapeHtml(b.province) || '—'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Kode Pos</small><p class="mb-0 text-body">${escapeHtml(b.postal_code) || '—'}</p></div>
                        <div class="col-12"><hr class="my-0"></div>
                        <div class="col-12"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.5px;">Kepala Cabang</small><p class="mb-0 fw-medium text-body">${escapeHtml(b.manager_name) || '<span class=\'text-muted\'>Belum diisi</span>'}</p></div>
                    </div>
                `);
            }).fail(function() {
                $('#detailCabangBody').html('<div class="alert alert-danger">Gagal memuat detail cabang.</div>');
            });
        });

        // ===== HAPUS =====
        $(document).on('click', '.btn-hapus-branch', function () {
            let id   = parseInt($(this).data('id'));
            let nama = $(this).data('nama');
            if (!id) return;

            Swal.fire({
                title: 'Hapus Cabang?',
                html: `Kamu yakin mau hapus cabang <strong>"${escapeHtml(nama)}"</strong>?<br><small class="text-muted">Cabang yang digunakan oleh data gudang tidak dapat dihapus.</small>`,
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
                        url: '/cabang/' + id + '/delete',
                        type: 'DELETE',
                        success: function (res) {
                            Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                                .then(() => { tableCabang.ajax.reload(null, false); });
                        },
                        error: function (err) {
                            let msg = err.status === 422 && err.responseJSON?.message
                                ? escapeHtml(err.responseJSON.message)
                                : 'Gagal menghapus cabang.';
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });

        // ===== EXPORT EXCEL ALERT =====
        $(document).on('click', '.btn-export-br', function () {
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
