<x-layouts title="Manajemen User">
    @push('styles')
    <style>
        .signature-container {
            border: 2px dashed #cbd5e1 !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-image: linear-gradient(rgba(241, 245, 249, 0.3) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(241, 245, 249, 0.3) 1px, transparent 1px);
            background-size: 20px 20px;
            background-color: #ffffff;
        }
        .signature-container:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
        }
        canvas {
            border-radius: 6px;
        }

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
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">Total User</p>
                        <h3 class="mb-0 fw-extrabold" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $totalUsers }}</h3>
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
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">User Aktif</p>
                        <h3 class="mb-0 fw-extrabold" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $aktifUsers }}</h3>
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
                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.8px;">User Nonaktif</p>
                        <h3 class="mb-0 fw-extrabold" style="font-size: 2rem; font-weight: 800; color: var(--bs-body-color);">{{ $nonaktifUsers }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-table_card tableId="tableKaryawan" title="Manajemen User / Akun Pengguna" icon="bi-people-fill" btnTarget="#modalTambah"
        btnText="Tambah User Baru">

        <x-slot name="extraActions">
            @can('lihat-karyawan')
                <a href="{{ route('karyawan.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-kry" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
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
            <th class="py-3">Kode</th>
            <th class="py-3">Nama Lengkap</th>
            <th class="py-3">Username</th>
            <th class="py-3">Email</th>
            <th class="py-3">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- tbody kosong — diisi via AJAX DataTables --}}
    </x-table_card>

    @include('user.tambah')
    @include('user.edit')
    @include('user.detail')
    @include('user.import')

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

            $(document).ready(function() {
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
                        // Untuk keydown: hanya karakter printable (bukan Backspace, Delete, Arrow, dll)
                        if (!isPaste) {
                            const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
                            if (!isPrintable) return;
                        }
                        // Hitung panjang setelah karakter baru masuk (pertimbangkan seleksi teks)
                        const start = el.selectionStart;
                        const end = el.selectionEnd;
                        const currentLen = el.value.length - (end - start);
                        if (currentLen >= maxLen) {
                            e.preventDefault();
                            showCharLimitPopup(selector, fieldLabel, maxLen);
                        }
                    });
                }

                // ===== Auto-uppercase, huruf saja, max 4 – Inisial =====
                $(document).on('keydown', '#tambah_inisial, #edit_inisial', function(e) {
                    const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
                    if (!isPrintable) return;
                    const start = this.selectionStart;
                    const end   = this.selectionEnd;
                    const currentLen = this.value.length - (end - start);
                    // Blokir non-huruf
                    if (!/[a-zA-Z]/.test(e.key)) { e.preventDefault(); return; }
                    if (currentLen >= 4) {
                        e.preventDefault();
                        showCharLimitPopup('#tambah_inisial, #edit_inisial', 'Inisial', 4);
                        return;
                    }
                });
                // Auto-uppercase saat mengetik
                $(document).on('input', '#tambah_inisial, #edit_inisial', function() {
                    const pos = this.selectionStart;
                    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 4);
                    this.setSelectionRange(pos, pos);
                });

                // ===== Digit-only, max 13 – No HP =====
                $(document).on('keydown', '#tambah_no_hp, #edit_no_hp', function(e) {
                    const isPrintable = e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey;
                    if (!isPrintable) return;
                    if (!/[0-9]/.test(e.key)) { e.preventDefault(); return; }
                    const start = this.selectionStart;
                    const end   = this.selectionEnd;
                    const currentLen = this.value.length - (end - start);
                    if (currentLen >= 13) {
                        e.preventDefault();
                        showCharLimitPopup('#tambah_no_hp, #edit_no_hp', 'No HP', 13);
                    }
                });

                // ===== Field lainnya dengan batas karakter =====
                setupCharLimitPopup('#tambah_kode_karyawan, #edit_kode_karyawan', 4,  'Kode Karyawan');
                setupCharLimitPopup('#tambah_nama_lengkap,  #edit_nama_lengkap',  50, 'Nama Lengkap');
                setupCharLimitPopup('#tambah_username,      #edit_username',       20, 'Username');
                setupCharLimitPopup('#tambah_email,         #edit_email',          50, 'Email');

                // Listener hubungkan Karyawan pada Modal Tambah
                $('#tambah_karyawan_id').on('change', function() {
                    let selected = $(this).val();
                    let isLinked = selected !== '';
                    
                    $('#tambah_kode_karyawan').prop('disabled', isLinked);
                    $('#tambah_nama_lengkap').prop('disabled', isLinked).prop('required', !isLinked);
                    $('#tambah_inisial').prop('disabled', isLinked).prop('required', !isLinked);
                    $('#tambah_no_hp').prop('disabled', isLinked);
                    $('#tambah_departemen_id').prop('disabled', isLinked).prop('required', !isLinked);
                    $('#tambah_jabatan_id').prop('disabled', isLinked).prop('required', !isLinked);
                    $('#tambah_jenis_kelamin').prop('disabled', isLinked).prop('required', !isLinked);
                    
                    if (isLinked) {
                        let opt = $(this).find(':selected');
                        $('#tambah_kode_karyawan').val(opt.data('kode'));
                        $('#tambah_nama_lengkap').val(opt.data('nama'));
                        $('#tambah_inisial').val(opt.data('inisial'));
                        $('#tambah_no_hp').val(opt.data('no-hp'));
                        $('#tambah_departemen_id').val(opt.data('dept'));
                        $('#tambah_jabatan_id').val(opt.data('jab'));
                        $('#tambah_jenis_kelamin').val(opt.data('jk'));
                    } else {
                        $('#tambah_kode_karyawan').val('');
                        $('#tambah_nama_lengkap').val('');
                        $('#tambah_inisial').val('');
                        $('#tambah_no_hp').val('');
                        $('#tambah_departemen_id').val('');
                        $('#tambah_jabatan_id').val('');
                        $('#tambah_jenis_kelamin').val('');
                    }
                });

                // Edit karyawan_id adalah read-only — change listener tidak diperlukan.

                $('#modalTambah').on('hidden.bs.modal', function() {
                    $('#formTambahKaryawan')[0].reset();
                    $('#tambah_karyawan_id').trigger('change');
                    $('.check-company-tambah').prop('checked', false);
                    updateTambahDefaultCompanyOptions();
                });
                $('#modalEdit').on('hidden.bs.modal', function() {
                    $('#formEditKaryawan')[0].reset();
                });

                // ===== DYNAMIC COMPANY & BRANCH OPTIONS LOGIC =====
                function updateTambahDefaultCompanyOptions() {
                    let select = $('#tambah_default_company_id');
                    let currentDefault = select.val();
                    select.empty().append('<option value="">Pilih PT Default...</option>');
                    
                    let checkedCompanies = [];
                    $('.check-company-tambah:checked').each(function() {
                        let id = $(this).val();
                        checkedCompanies.push(String(id));
                        let name = $(this).parent().find('label').text().trim();
                        select.append($('<option>', {value: id, text: name, selected: id === currentDefault}));
                    });

                    if (checkedCompanies.length > 0) {
                        $('#container_akses_cabang_tambah').show();
                        $('#select_pt_first_msg_tambah').hide();

                        let visibleCount = 0;
                        $('.branch-item-tambah').each(function() {
                            let compId = String($(this).data('company-id'));
                            if (checkedCompanies.includes(compId)) {
                                $(this).show();
                                visibleCount++;
                            } else {
                                $(this).hide();
                                $(this).find('.check-branch-tambah').prop('checked', false);
                            }
                        });

                        if (visibleCount === 0) {
                            $('#no_branch_msg_tambah').show();
                        } else {
                            $('#no_branch_msg_tambah').hide();
                        }
                    } else {
                        $('#container_akses_cabang_tambah').hide();
                        $('#select_pt_first_msg_tambah').show();
                        $('.check-branch-tambah').prop('checked', false);
                    }
                    updateTambahDefaultBranchOptions();
                }
                $(document).on('change', '.check-company-tambah', updateTambahDefaultCompanyOptions);

                function updateTambahDefaultBranchOptions() {
                    let select = $('#tambah_default_branch_id');
                    let currentDefault = select.val();
                    select.empty().append('<option value="">Pilih Cabang Default...</option>');
                    
                    $('.check-branch-tambah:checked').each(function() {
                        let id = $(this).val();
                        let name = $(this).parent().find('label').text().trim();
                        select.append($('<option>', {value: id, text: name, selected: id === currentDefault}));
                    });
                }
                $(document).on('change', '.check-branch-tambah', updateTambahDefaultBranchOptions);

                function updateEditDefaultCompanyOptions(selectedDefaultId = null) {
                    let select = $('#edit_default_company_id');
                    select.empty().append('<option value="">Pilih PT Default...</option>');
                    
                    let checkedCompanies = [];
                    $('.check-company-edit:checked').each(function() {
                        let id = $(this).val();
                        checkedCompanies.push(String(id));
                        let name = $(this).parent().find('label').text().trim();
                        select.append($('<option>', {value: id, text: name, selected: parseInt(id) === parseInt(selectedDefaultId)}));
                    });

                    if (checkedCompanies.length > 0) {
                        $('#container_akses_cabang_edit').show();
                        $('#select_pt_first_msg_edit').hide();

                        let visibleCount = 0;
                        $('.branch-item-edit').each(function() {
                            let compId = String($(this).data('company-id'));
                            if (checkedCompanies.includes(compId)) {
                                $(this).show();
                                visibleCount++;
                            } else {
                                $(this).hide();
                                $(this).find('.check-branch-edit').prop('checked', false);
                            }
                        });

                        if (visibleCount === 0) {
                            $('#no_branch_msg_edit').show();
                        } else {
                            $('#no_branch_msg_edit').hide();
                        }
                    } else {
                        $('#container_akses_cabang_edit').hide();
                        $('#select_pt_first_msg_edit').show();
                        $('.check-branch-edit').prop('checked', false);
                    }
                    updateEditDefaultBranchOptions($('#edit_default_branch_id').val());
                }
                $(document).on('change', '.check-company-edit', function() {
                    updateEditDefaultCompanyOptions($('#edit_default_company_id').val());
                });

                function updateEditDefaultBranchOptions(selectedDefaultId = null) {
                    let select = $('#edit_default_branch_id');
                    select.empty().append('<option value="">Pilih Cabang Default...</option>');
                    
                    $('.check-branch-edit:checked').each(function() {
                        let id = $(this).val();
                        let name = $(this).parent().find('label').text().trim();
                        select.append($('<option>', {value: id, text: name, selected: parseInt(id) === parseInt(selectedDefaultId)}));
                    });
                }
                $(document).on('change', '.check-branch-edit', function() {
                    updateEditDefaultBranchOptions($('#edit_default_branch_id').val());
                });

                // DataTable Server-Side
                tableKaryawan = $('#tableKaryawan').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('karyawan.data') }}",
                        type: 'GET'
                    },
                    columns: [
                        { data: 'no',            orderable: false, searchable: false, className: 'text-center' },
                        { data: 'foto',          orderable: false, searchable: false, className: 'text-center' },
                        { data: 'kode_karyawan',  orderable: true,  searchable: true },
                        { data: 'nama_lengkap',   orderable: true,  searchable: true },
                        { data: 'username',      orderable: true,  searchable: true },
                        { data: 'email',         orderable: true,  searchable: true },
                        { data: 'status',        orderable: true,  searchable: false, className: 'text-center' },
                        { data: null,            orderable: false, searchable: false, className: 'text-center' },
                    ],
                    columnDefs: [
                        {
                            targets: 1,
                            render: function (data) {
                                return '<img src="' + data + '" class="rounded-circle border" width="36" height="36" style="object-fit: cover;" alt="Foto">';
                            }
                        },
                        {
                            targets: 2,
                            render: function (data) {
                                return '<span class="badge bg-secondary">' + escapeHtml(data) + '</span>';
                            }
                        },
                        {
                            targets: 3,
                            render: function (data, type, row) {
                                let res = escapeHtml(data);
                                if (row.inisial) {
                                    res += ' <span class="badge bg-info bg-opacity-10 text-info fw-semibold ms-1 px-2 py-0.5 rounded-pill" style="font-size:0.75rem;">' + escapeHtml(row.inisial) + '</span>';
                                }
                                if (row.companies_list) {
                                    res += '<br><small class="text-muted"><i class="bi bi-building me-1"></i>' + escapeHtml(row.companies_list);
                                    if (row.branches_list) {
                                        res += ' <span class="text-secondary fw-normal">(' + escapeHtml(row.branches_list) + ')</span>';
                                    }
                                    res += '</small>';
                                }
                                return res;
                            }
                        },
                        {
                            targets: 6,
                            render: function (data) {
                                let badgeClass = data === 'Aktif' ? 'bg-success' : 'bg-danger';
                                return '<span class="badge ' + badgeClass + '">' + escapeHtml(data) + '</span>';
                            }
                        },
                        {
                            targets: 7,
                            render: function (data, type, row) {
                                let btns = '<div class="d-flex gap-2 justify-content-md-center justify-content-end">';
                                btns += '<button class="btn btn-sm btn-light text-info rounded-circle shadow-sm btn-view border-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-id="' + row.id + '" title="Detail"><i class="bi bi-eye"></i></button>';
                                if (row.can_edit) {
                                    btns += '<button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm btn-edit border-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-id="' + row.id + '" title="Edit"><i class="bi bi-pencil"></i></button>';
                                    btns += '<button class="btn btn-sm btn-light text-warning rounded-circle shadow-sm btn-reset border-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_lengkap) + '" title="Reset Password"><i class="bi bi-key"></i></button>';
                                }
                                if (row.can_hapus) {
                                    btns += '<button class="btn btn-sm btn-light text-danger rounded-circle shadow-sm btn-hapus border-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_lengkap) + '" title="Hapus"><i class="bi bi-trash"></i></button>';
                                }
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
                        emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>Belum ada data user.</div>',
                        zeroRecords: 'Tidak ditemukan data yang cocok',
                        processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...'
                    },
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    order: [[0, 'desc']]
                });

                // Submit Form Tambah
                $('#formTambahKaryawan').on('submit', function(e) {
                    e.preventDefault();
                    if ($('#tambah_inisial').val() && $('#tambah_inisial').val().length > 4) {
                        Swal.fire({ title: 'Peringatan!', text: 'Inisial tidak boleh lebih dari 4 karakter.', icon: 'warning' });
                        return false;
                    }
                    let formData = new FormData(this);
                    
                    $.ajax({
                        url: '/users/store',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            $('#modalTambah').modal('hide');
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                tableKaryawan.ajax.reload(null, false);
                            });
                        },
                        error: function(err) {
                            console.error("Store Error:", err);
                            let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                            if (err.status === 422) {
                                errorMsg = '<b>Data tidak valid:</b><br>';
                                if (err.responseJSON && err.responseJSON.errors) {
                                    $.each(err.responseJSON.errors, function(key, val) {
                                        errorMsg += '• ' + val[0] + '<br>';
                                    });
                                } else if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg += err.responseJSON.message;
                                }
                            } else if(err.responseJSON && err.responseJSON.message) {
                                errorMsg = err.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Gagal!',
                                html: errorMsg,
                                icon: 'error'
                            });
                        }
                    });
                });

                // Submit Form Edit
                $('#formEditKaryawan').on('submit', function(e) {
                    e.preventDefault();
                    if ($('#edit_inisial').val() && $('#edit_inisial').val().length > 4) {
                        Swal.fire({ title: 'Peringatan!', text: 'Inisial tidak boleh lebih dari 4 karakter.', icon: 'warning' });
                        return false;
                    }
                    let id = $('#edit_id').val();
                    let formData = new FormData(this);
                    
                    $.ajax({
                        url: '/users/' + id + '/update',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            $('#modalEdit').modal('hide');
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                tableKaryawan.ajax.reload(null, false);
                            });
                        },
                        error: function(err) {
                            console.error("Update Error:", err);
                            let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                            if (err.status === 422) {
                                errorMsg = '<b>Data tidak valid:</b><br>';
                                if (err.responseJSON && err.responseJSON.errors) {
                                    $.each(err.responseJSON.errors, function(key, val) {
                                        errorMsg += '• ' + val[0] + '<br>';
                                    });
                                } else if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg += err.responseJSON.message;
                                }
                            } else if(err.responseJSON && err.responseJSON.message) {
                                errorMsg = err.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Gagal!',
                                html: errorMsg,
                                icon: 'error'
                            });
                        }
                    });
                });

                // View Detail
                $(document).on('click', '.btn-view', function() {
                    let id = $(this).data('id');
                    $.get('/users/' + id + '/edit', function(res) {
                        let user = res.user;
                        let photoUrl = user.foto ? '/' + user.foto : (user.jenis_kelamin === 'P' ? '/gambar/perempuan.jpg' : '/gambar/laki.jpg');
                        
                        $('#detail_foto').attr('src', photoUrl);
                        $('#detail_nama_lengkap').text(user.nama_lengkap);
                        $('#detail_kode_karyawan').text(user.kode_karyawan || '-');
                        $('#detail_username').text(user.username);
                        $('#detail_email').text(user.email);
                        $('#detail_no_hp').text(user.no_hp || '-');
                        
                        if (user.inisial) {
                            $('#detail_inisial').text(user.inisial);
                            $('#detail_inisial_wrapper').show();
                        } else {
                            $('#detail_inisial_wrapper').hide();
                        }
                        
                        if(user.status == 'Aktif') {
                            $('#detail_status').html('<span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>');
                        } else {
                            $('#detail_status').html('<span class="badge bg-danger rounded-pill px-3 py-2">Nonaktif</span>');
                        }

                        let jk = user.jenis_kelamin == 'L' ? 'Laki-laki' : (user.jenis_kelamin == 'P' ? 'Perempuan' : '-');
                        $('#detail_jenis_kelamin').text(jk);

                        $('#detail_departemen').text(user.department ? user.department.department_name : '-');
                        $('#detail_jabatan').text(user.position ? user.position.position_name : '-');
                        $('#detail_role').text(user.role_name || '-');
                        
                        let companiesHtml = '';
                        if (user.companies && user.companies.length > 0) {
                            $.each(user.companies, function(i, c) {
                                let badgeClass = c.pivot && c.pivot.is_default ? 'bg-primary' : 'bg-secondary';
                                let defaultSuffix = c.pivot && c.pivot.is_default ? ' (Default)' : '';
                                companiesHtml += `<span class="badge ${badgeClass} px-3 py-2 rounded-pill"><i class="bi bi-building me-1"></i>${escapeHtml(c.nama_perusahaan)}${defaultSuffix}</span> `;
                            });
                            $('#detail_companies').html(companiesHtml);
                            $('#detail_companies_wrapper').show();
                        } else {
                            $('#detail_companies_wrapper').hide();
                        }

                        let branchesHtml = '';
                        if (user.branches && user.branches.length > 0) {
                            $.each(user.branches, function(i, b) {
                                let badgeClass = b.pivot && b.pivot.is_default ? 'bg-primary' : 'bg-secondary';
                                let defaultSuffix = b.pivot && b.pivot.is_default ? ' (Default)' : '';
                                branchesHtml += `<span class="badge ${badgeClass} px-3 py-2 rounded-pill">${escapeHtml(b.nama_cabang)}${defaultSuffix}</span> `;
                            });
                            $('#detail_branches').html(branchesHtml);
                            $('#detail_branches_wrapper').show();
                        } else {
                            $('#detail_branches_wrapper').hide();
                        }

                        if (user.active_signature_path) {
                            $('#detail_signature').attr('src', user.active_signature_path).show();
                            $('#detail_signature_empty').hide();
                        } else {
                            $('#detail_signature').hide();
                            $('#detail_signature_empty').show();
                        }

                        $('#modalDetail').modal('show');
                    });
                });

                // Edit click
                $(document).on('click', '.btn-edit', function() {
                    let id = $(this).data('id');
                    $.get('/users/' + id + '/edit', function(res) {
                        $('#edit_id').val(res.user.id);
                        
                        // Isi hidden input agar nilai karyawan_id tetap terkirim
                        $('#edit_karyawan_id_hidden').val(res.user.karyawan_id || '');

                        // Tampilkan di select (disabled — hanya untuk tampilan)
                        let karyawanSelect = $('#edit_karyawan_id');
                        karyawanSelect.empty().append('<option value="">-- Tidak Terhubung ke Karyawan HRD --</option>');
                        $.each(res.availableKaryawan, function(i, emp) {
                            karyawanSelect.append($('<option>', {
                                value: emp.id,
                                text: emp.nama_lengkap + (emp.kode_karyawan ? ' (' + emp.kode_karyawan + ')' : ' (Tanpa Kode)'),
                                selected: emp.id === res.user.karyawan_id
                            }));
                        });

                        // Lock/unlock field profil berdasarkan status link ke karyawan HRD
                        let isLinked = !!res.user.karyawan_id;
                        $('#edit_kode_karyawan').prop('disabled', isLinked);
                        $('#edit_nama_lengkap').prop('disabled', isLinked).prop('required', !isLinked);
                        $('#edit_inisial').prop('disabled', isLinked).prop('required', !isLinked);
                        $('#edit_no_hp').prop('disabled', isLinked);
                        $('#edit_departemen_id').prop('disabled', isLinked).prop('required', !isLinked);
                        $('#edit_jabatan_id').prop('disabled', isLinked).prop('required', !isLinked);
                        $('#edit_jenis_kelamin').prop('disabled', isLinked).prop('required', !isLinked);

                        $('#edit_kode_karyawan').val(res.user.kode_karyawan);
                        $('#edit_nama_lengkap').val(res.user.nama_lengkap);
                        $('#edit_inisial').val(res.user.inisial);
                        $('#edit_username').val(res.user.username);
                        $('#edit_email').val(res.user.email);
                        $('#edit_no_hp').val(res.user.no_hp);
                        $('#edit_jenis_kelamin').val(res.user.jenis_kelamin);
                        $('#edit_status').val(res.user.status);

                        let roleSelect = $('#edit_role_id');
                        roleSelect.empty();
                        $.each(res.roles, function(i, r) {
                            roleSelect.append($('<option>', {value: r.id, text: r.name, selected: r.id === res.user.role_id}));
                        });

                        let deptSelect = $('#edit_departemen_id');
                        deptSelect.empty();
                        $.each(res.departments, function(i, d) {
                            deptSelect.append($('<option>', {value: d.id, text: d.department_name, selected: d.id === res.user.departemen_id}));
                        });

                        let jabatanSelect = $('#edit_jabatan_id');
                        jabatanSelect.empty();
                        $.each(res.jabatans, function(i, j) {
                            jabatanSelect.append($('<option>', {value: j.id, text: j.position_name, selected: j.id === res.user.jabatan_id}));
                        });
                        
                        $('.check-company-edit').prop('checked', false);
                        let defaultCompanyId = null;
                        if (res.user.companies) {
                            $.each(res.user.companies, function(i, c) {
                                $(`#edit_company_${c.id}`).prop('checked', true);
                                if (c.pivot && c.pivot.is_default) {
                                    defaultCompanyId = c.id;
                                }
                            });
                        }
                        updateEditDefaultCompanyOptions(defaultCompanyId);

                        $('.check-branch-edit').prop('checked', false);
                        let defaultBranchId = null;
                        if (res.user.branches) {
                            $.each(res.user.branches, function(i, b) {
                                $(`#edit_branch_${b.id}`).prop('checked', true);
                                if (b.pivot && b.pivot.is_default) {
                                    defaultBranchId = b.id;
                                }
                            });
                        }
                        updateEditDefaultBranchOptions(defaultBranchId);

                        $('#modalEdit').modal('show');
                    });
                });

                // Hapus click
                $(document).on('click', '.btn-hapus', function() {
                    let id = $(this).data('id');
                    let nama = $(this).data('nama');
                    
                    Swal.fire({
                        title: 'Hapus Data User?',
                        text: "Anda yakin ingin menghapus user " + nama + "?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/users/' + id + '/delete',
                                type: 'DELETE',
                                success: function(res) {
                                    Swal.fire({
                                        title: 'Terhapus!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        tableKaryawan.ajax.reload(null, false);
                                    });
                                },
                                error: function(err) {
                                    Swal.fire('Error!', 'Gagal menghapus data', 'error');
                                }
                            });
                        }
                    });
                });

                // Reset Password click
                $(document).on('click', '.btn-reset', function() {
                    let id = $(this).data('id');
                    let nama = $(this).data('nama');
                    
                    Swal.fire({
                        title: 'Reset Password?',
                        text: "Anda yakin ingin mereset password untuk user " + nama + " menjadi password default?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#ff9f43',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, reset!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/users/' + id + '/reset-password',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(res) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        html: res.message,
                                        icon: 'success'
                                    }).then(() => {
                                        tableKaryawan.ajax.reload(null, false);
                                    });
                                },
                                error: function(err) {
                                    let msg = 'Gagal mereset password.';
                                    if (err.responseJSON && err.responseJSON.message) {
                                        msg = err.responseJSON.message;
                                    }
                                    Swal.fire('Error!', msg, 'error');
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

                // Show/hide password toggle
                $(document).on('click', '.toggle-password', function() {
                    let input = $(this).parent().find('input');
                    if (input.attr('type') === 'password') {
                        input.attr('type', 'text');
                        $(this).removeClass('bi-eye').addClass('bi-eye-slash');
                    } else {
                        input.attr('type', 'password');
                        $(this).removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                });
            });
        </script>
    @endpush
</x-layouts>
