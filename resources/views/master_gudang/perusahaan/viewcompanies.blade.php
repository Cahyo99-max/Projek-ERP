<x-layouts title="Master Perusahaan (PT)">

    @push('styles')
    <style>
        #tablePerusahaan tbody td { vertical-align: middle; }
        .pt-nama { font-weight: 600; color: var(--bs-body-color); }
        .pt-sub { color: var(--bs-secondary-color, #6c757d); font-size: 0.8rem; display: block; }
        .pt-text { color: var(--bs-body-color); }
    </style>
    @endpush

    {{-- ===== SUMMARY CARDS ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:54px;height:54px;">
                        <i class="bi bi-building-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">Total Perusahaan (PT)</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalPerusahaan }}</h3>
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
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">PT Aktif</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $aktifPerusahaan }}</h3>
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
                        <p class="text-dark text-opacity-75 mb-1 fw-semibold" style="font-size:0.9rem;">PT Nonaktif</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $nonaktifPerusahaan }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABEL PERUSAHAAN ===== --}}
    <x-table_card tableId="tablePerusahaan" title="Data Perusahaan (PT) Induk" icon="bi-building"
        btnTarget="{{ auth()->user()->can('tambah-perusahaan') ? '#modalTambahPerusahaan' : '' }}"
        btnText="Tambah PT">

        <x-slot name="extraActions">
            @can('lihat-perusahaan')
                <a href="{{ route('companies.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-pt" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-perusahaan')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportPerusahaan">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="4%" class="text-center py-3">No</th>
            <th width="8%" class="text-center py-3">Logo</th>
            <th class="py-3">Kode</th>
            <th class="py-3">Nama Perusahaan (PT)</th>
            <th class="py-3 d-none d-md-table-cell">Direktur / Penanggung Jawab</th>
            <th class="py-3 d-none d-lg-table-cell">Kontak / Kota</th>
            <th class="py-3 text-center">Jumlah Cabang</th>
            <th class="py-3">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

    </x-table_card>

    @include('master_gudang.perusahaan.tambah')
    @include('master_gudang.perusahaan.edit')
    @include('master_gudang.perusahaan.import')

    {{-- ===== MODAL DETAIL ===== --}}
    <div class="modal fade" id="modalDetailPerusahaan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom bg-body">
                <div class="modal-header header-cinematic">
                    <h5 class="modal-title fw-semibold text-white">
                        <i class="bi bi-building me-2"></i> Detail Perusahaan (PT)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailPerusahaanBody">
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

    function previewImage(input, previewSelector) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $(previewSelector).html(`<img src="${e.target.result}" class="w-100 h-100" style="object-fit:cover;">`);
            }
            reader.readAsDataURL(input.files[0]);
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
        $(document).on('keydown paste input', selector, function(e) {
            const el = this;
            if (e.type === 'paste') {
                setTimeout(() => {
                    if (el.value.length > maxLen) {
                        el.value = el.value.substring(0, maxLen);
                        showCharLimitPopup(selector, fieldLabel, maxLen);
                    }
                }, 10);
                return;
            }
            if (e.type === 'input') {
                if (el.value.length >= maxLen) {
                    showCharLimitPopup(selector, fieldLabel, maxLen);
                }
                return;
            }
            const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
            if (!isPrintable) return;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const currentLen = el.value.length - (end - start);
            if (currentLen >= maxLen) {
                e.preventDefault();
                showCharLimitPopup(selector, fieldLabel, maxLen);
            }
        });
    }

    let tablePerusahaan;

    $(document).ready(function () {

        // Restrict phone & postal code to numbers
        $(document).on('input keypress keydown paste', '#tambah_phone_pt, #edit_phone_pt, #tambah_postal_code_pt, #edit_postal_code_pt', function (e) {
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
        setupCharLimitPopup('#tambah_kode_perusahaan, #edit_kode_perusahaan', 10, 'Kode PT');
        setupCharLimitPopup('#tambah_nama_perusahaan, #edit_nama_perusahaan', 40, 'Nama Perusahaan');
        setupCharLimitPopup('#tambah_npwp, #edit_npwp', 30, 'No. NPWP');
        setupCharLimitPopup('#tambah_nib, #edit_nib', 30, 'No. NIB');
        setupCharLimitPopup('#tambah_director_name, #edit_director_name', 40, 'Direktur / Penanggung Jawab');
        setupCharLimitPopup('#tambah_phone_pt, #edit_phone_pt', 13, 'No. Telepon');
        setupCharLimitPopup('#tambah_email_pt, #edit_email_pt', 60, 'Email PT');
        setupCharLimitPopup('#tambah_address_pt, #edit_address_pt', 60, 'Alamat Kantor Pusat');
        setupCharLimitPopup('#tambah_city_pt, #edit_city_pt', 100, 'Kota / Kabupaten');
        setupCharLimitPopup('#tambah_province_pt, #edit_province_pt', 40, 'Provinsi');
        setupCharLimitPopup('#tambah_postal_code_pt, #edit_postal_code_pt', 10, 'Kode Pos');

        // ===== DATATABLES SERVER-SIDE AJAX =====
        tablePerusahaan = $('#tablePerusahaan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('companies.data') }}",
                type: 'GET'
            },
            columns: [
                { data: 'no',              orderable: false, searchable: false, className: 'text-center' },
                { data: 'logo_url',        orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode_perusahaan', orderable: true,  searchable: true },
                { data: 'nama_perusahaan', orderable: true,  searchable: true },
                { data: 'director_name',   orderable: true,  searchable: true, className: 'd-none d-md-table-cell' },
                { data: 'phone',           orderable: true,  searchable: true, className: 'd-none d-lg-table-cell' },
                { data: 'branches_count',  orderable: true,  searchable: false, className: 'text-center' },
                { data: 'status',          orderable: true,  searchable: false, className: 'text-center' },
                { data: null,              orderable: false, searchable: false, className: 'text-center' },
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data) {
                        if (data) {
                            return `<img src="${escapeHtml(data)}" class="rounded-3 shadow-sm border" style="width:36px;height:36px;object-fit:cover;">`;
                        }
                        return `<div class="rounded-3 bg-light text-muted d-inline-flex align-items-center justify-content-center border" style="width:36px;height:36px;"><i class="bi bi-building"></i></div>`;
                    }
                },
                {
                    targets: 2,
                    render: function (data) {
                        return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'
                            + escapeHtml(data) + '</span>';
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        let res = '<div class="pt-nama">' + escapeHtml(data) + '</div>';
                        if (row.npwp) {
                            res += '<small class="pt-sub"><i class="bi bi-card-heading me-1"></i>NPWP: ' + escapeHtml(row.npwp) + '</small>';
                        }
                        return res;
                    }
                },
                {
                    targets: 4,
                    render: function (data) {
                        return data ? '<span class="pt-text"><i class="bi bi-person-fill me-1 text-muted"></i>' + escapeHtml(data) + '</span>' : '<span style="color:#6c757d">—</span>';
                    }
                },
                {
                    targets: 5,
                    render: function (data, type, row) {
                        let kontak = [];
                        if (row.phone) kontak.push(escapeHtml(row.phone));
                        if (row.city) kontak.push(escapeHtml(row.city));
                        return kontak.length ? '<span class="pt-text">' + kontak.join(' • ') + '</span>' : '<span style="color:#6c757d">—</span>';
                    }
                },
                {
                    targets: 6,
                    render: function (data) {
                        return `<span class="badge bg-info bg-opacity-10 text-info fw-semibold px-3 py-2 rounded-pill"><i class="bi bi-buildings me-1"></i>${data} Cabang</span>`;
                    }
                },
                {
                    targets: 7,
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
                    targets: 8,
                    render: function (data, type, row) {
                        let btns = '<div class="d-flex gap-2 justify-content-center">';
                        btns += '<button class="btn btn-sm btn-light text-info rounded-circle shadow-sm btn-view-pt border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" title="Detail"><i class="bi bi-eye"></i></button>';
                        @can('edit-perusahaan')
                        btns += '<button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm btn-edit-pt border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" title="Edit"><i class="bi bi-pencil"></i></button>';
                        @endcan
                        @can('hapus-perusahaan')
                        btns += '<button class="btn btn-sm btn-light text-danger rounded-circle shadow-sm btn-hapus-pt border-0" '
                            + 'style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" '
                            + 'data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_perusahaan) + '" title="Hapus"><i class="bi bi-trash"></i></button>';
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
                emptyTable: '<div class="text-center text-muted py-5"><i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>Belum ada data perusahaan. Klik <strong>Tambah PT</strong> untuk memulai.</div>',
                zeroRecords: 'Tidak ditemukan data yang cocok',
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[3, 'asc']]
        });

        // ===== TAMBAH PT FORM SUBMIT =====
        $('#formTambahPerusahaan').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $('#btnSimpanTambahPT').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahPerusahaan').modal('hide');
                    $('#formTambahPerusahaan')[0].reset();
                    $('#previewLogoTambah').html('<i class="bi bi-building fs-1 text-muted"></i>');
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(() => { tablePerusahaan.ajax.reload(null, false); window.location.reload(); });
                },
                error: function (err) {
                    let msg = err.responseJSON?.message || 'Gagal menambahkan perusahaan.';
                    if (err.responseJSON?.errors) {
                        msg = Object.values(err.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire('Gagal!', msg, 'error');
                },
                complete: function () {
                    $('#btnSimpanTambahPT').prop('disabled', false).html('<i class="bi bi-save-fill me-1"></i> Simpan PT');
                }
            });
        });

        // ===== EDIT PT FETCH & SUBMIT =====
        $(document).on('click', '.btn-edit-pt', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/companies/' + id + '/edit', function (res) {
                let c = res.company;
                $('#edit_company_id').val(c.id);
                $('#edit_kode_perusahaan').val(c.kode_perusahaan);
                $('#edit_nama_perusahaan').val(c.nama_perusahaan);
                $('#edit_npwp').val(c.npwp);
                $('#edit_nib').val(c.nib);
                $('#edit_director_name').val(c.director_name);
                $('#edit_phone_pt').val(c.phone);
                $('#edit_email_pt').val(c.email);
                $('#edit_address_pt').val(c.address);
                $('#edit_city_pt').val(c.city);
                $('#edit_province_pt').val(c.province);
                $('#edit_postal_code_pt').val(c.postal_code);
                $('#edit_status').val(c.status);

                if (c.logo_url) {
                    $('#previewLogoEdit').html(`<img src="${c.logo_url}" class="w-100 h-100" style="object-fit:cover;">`);
                } else {
                    $('#previewLogoEdit').html('<i class="bi bi-building fs-1 text-muted"></i>');
                }

                $('#formEditPerusahaan').attr('action', '/companies/' + c.id + '/update');
                $('#modalEditPerusahaan').modal('show');
            }).fail(function() {
                Swal.fire('Error', 'Gagal memuat data perusahaan.', 'error');
            });
        });

        $('#formEditPerusahaan').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $('#btnUpdatePT').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalEditPerusahaan').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(() => { tablePerusahaan.ajax.reload(null, false); window.location.reload(); });
                },
                error: function (err) {
                    let msg = err.responseJSON?.message || 'Gagal mengupdate perusahaan.';
                    if (err.responseJSON?.errors) {
                        msg = Object.values(err.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire('Gagal!', msg, 'error');
                },
                complete: function () {
                    $('#btnUpdatePT').prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Update PT');
                }
            });
        });

        // ===== DETAIL PT =====
        $(document).on('click', '.btn-view-pt', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;
            $('#detailPerusahaanBody').html('<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>');
            $('#modalDetailPerusahaan').modal('show');

            $.get('/companies/' + id + '/show', function (res) {
                let c = res.company;
                let statusBadge = c.status === 'Active'
                    ? '<span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>'
                    : '<span class="badge bg-secondary rounded-pill px-3 py-2">Nonaktif</span>';

                let logoHtml = c.logo_url
                    ? `<img src="${c.logo_url}" class="rounded-3 shadow-sm border" style="width:70px;height:70px;object-fit:cover;">`
                    : `<div class="rounded-3 bg-light text-primary d-flex align-items-center justify-content-center border" style="width:70px;height:70px;"><i class="bi bi-building fs-1"></i></div>`;

                let branchesList = '';
                if (c.branches && c.branches.length > 0) {
                    branchesList = c.branches.map(b => `
                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-body-tertiary mb-1">
                            <div>
                                <strong>${escapeHtml(b.nama_cabang)}</strong>
                                <small class="text-muted ms-2">(${escapeHtml(b.kode_cabang)})</small>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1" style="font-size:0.75rem;">${escapeHtml(b.city || 'Cabang')}</span>
                        </div>
                    `).join('');
                } else {
                    branchesList = '<p class="text-muted small mb-0">Belum ada cabang terdaftar di bawah PT ini.</p>';
                }

                $('#detailPerusahaanBody').html(`
                    <div class="row g-3">
                        <div class="col-12 d-flex align-items-center gap-3 mb-2">
                            ${logoHtml}
                            <div>
                                <h5 class="mb-1 fw-bold text-body">${escapeHtml(c.nama_perusahaan)}</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill me-2">${escapeHtml(c.kode_perusahaan)}</span>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="col-12"><hr class="my-0"></div>
                        <div class="col-md-6"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">No. NPWP</small><p class="mb-0 fw-medium text-body">${escapeHtml(c.npwp) || '—'}</p></div>
                        <div class="col-md-6"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">No. NIB</small><p class="mb-0 fw-medium text-body">${escapeHtml(c.nib) || '—'}</p></div>
                        <div class="col-md-6"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Direktur Utama</small><p class="mb-0 fw-medium text-body">${escapeHtml(c.director_name) || '—'}</p></div>
                        <div class="col-md-3"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Telepon</small><p class="mb-0 text-body">${escapeHtml(c.phone) || '—'}</p></div>
                        <div class="col-md-3"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Email</small><p class="mb-0 text-body">${escapeHtml(c.email) || '—'}</p></div>
                        <div class="col-12"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Alamat Pusat</small><p class="mb-0 text-body">${escapeHtml(c.address) || '—'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Kota</small><p class="mb-0 text-body">${escapeHtml(c.city) || '—'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Provinsi</small><p class="mb-0 text-body">${escapeHtml(c.province) || '—'}</p></div>
                        <div class="col-md-4"><small class="text-muted fw-semibold text-uppercase" style="font-size:0.7rem;">Kode Pos</small><p class="mb-0 text-body">${escapeHtml(c.postal_code) || '—'}</p></div>
                        <div class="col-12"><hr class="my-0"></div>
                        <div class="col-12">
                            <h6 class="fw-bold mb-2"><i class="bi bi-buildings me-2 text-primary"></i> Daftar Cabang (${c.branches_count})</h6>
                            ${branchesList}
                        </div>
                    </div>
                `);
            }).fail(function() {
                $('#detailPerusahaanBody').html('<div class="alert alert-danger">Gagal memuat detail perusahaan.</div>');
            });
        });

        // ===== HAPUS PT =====
        $(document).on('click', '.btn-hapus-pt', function () {
            let id   = parseInt($(this).data('id'));
            let nama = $(this).data('nama');
            if (!id) return;

            Swal.fire({
                title: 'Hapus Perusahaan?',
                html: `Kamu yakin mau hapus perusahaan <strong>"${escapeHtml(nama)}"</strong>?<br><small class="text-muted">PT yang masih membawahi cabang tidak dapat dihapus.</small>`,
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
                        url: '/companies/' + id + '/delete',
                        type: 'DELETE',
                        success: function (res) {
                            Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                                .then(() => { tablePerusahaan.ajax.reload(null, false); window.location.reload(); });
                        },
                        error: function (err) {
                            let msg = err.responseJSON?.message || 'Gagal menghapus perusahaan.';
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });

        // ===== IMPORT SUBMIT =====
        $('#formImportPerusahaan').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $('#btnProsesImportPT').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengimpor...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalImportPerusahaan').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(() => { tablePerusahaan.ajax.reload(null, false); window.location.reload(); });
                },
                error: function (err) {
                    let msg = err.responseJSON?.message || 'Gagal mengimpor data.';
                    Swal.fire('Import Gagal!', msg, 'error');
                },
                complete: function () {
                    $('#btnProsesImportPT').prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Proses Import');
                }
            });
        });

        $('#modalTambahPerusahaan').on('hidden.bs.modal', function () {
            $('#formTambahPerusahaan')[0].reset();
            $('#previewLogoTambah').html('<i class="bi bi-building fs-1 text-muted"></i>');
        });

        $('#modalEditPerusahaan').on('hidden.bs.modal', function () {
            $('#formEditPerusahaan')[0].reset();
            $('#previewLogoEdit').html('<i class="bi bi-building fs-1 text-muted"></i>');
        });

        // ===== EXPORT EXCEL ALERT =====
        $(document).on('click', '.btn-export-pt', function () {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            Swal.fire({
                title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Ekspor Berhasil!</span>',
                html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Data sedang diproses. File Excel Anda akan terunduh secara otomatis.</div>',
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
