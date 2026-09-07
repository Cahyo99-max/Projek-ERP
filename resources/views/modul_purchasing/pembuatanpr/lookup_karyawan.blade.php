{{-- ===== MODAL LOOKUP KARYAWAN ===== --}}
<div class="modal fade" id="modalLookupKaryawan" tabindex="-1" aria-hidden="true" style="z-index: 1090;" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-search me-2"></i> Pilih Karyawan (HRD)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="tableLookupKaryawan" style="width: 100%;">
                        <thead>
                            <tr class="bg-light text-muted small" style="font-size: 0.75rem;">
                                <th>Nama Lengkap</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($karyawans as $emp)
                                <tr>
                                    <td class="fw-semibold">{{ $emp->nama_lengkap }}</td>
                                    <td>{{ $emp->department ? $emp->department->department_name : '-' }}</td>
                                    <td>{{ $emp->position ? $emp->position->position_name : '-' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-select-karyawan"
                                            data-id="{{ $emp->id }}"
                                            data-nama="{{ $emp->nama_lengkap }}"
                                            data-dept-id="{{ $emp->departemen_id }}">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ===== FORM TAMBAH KARYAWAN BARU ===== --}}
                <div class="border-top pt-3 mt-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                            <i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Karyawan Baru
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-toggle-form-karyawan-baru">
                            <i class="bi bi-plus-lg me-1"></i> Buka Form
                        </button>
                    </div>
                    <div id="form-karyawan-baru-wrapper" class="d-none">
                        <div class="bg-light rounded-3 p-3">
                            <form id="formTambahKaryawanBaru" autocomplete="off">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label small fw-semibold mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                            <small class="text-muted" style="font-size:0.7rem;">Maks 50 karakter</small>
                                        </div>
                                        <input type="text" name="nama_lengkap" id="new_kar_nama" class="form-control form-control-sm" maxlength="50" placeholder="Nama lengkap karyawan" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label small fw-semibold mb-1">Inisial <span class="text-danger">*</span></label>
                                            <small class="text-muted" style="font-size:0.7rem;">Maks 4 karakter</small>
                                        </div>
                                        <input type="text" name="inisial" id="new_kar_inisial" class="form-control form-control-sm" maxlength="4" placeholder="Cth: JD" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-semibold mb-1">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select name="jenis_kelamin" id="new_kar_jk" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Departemen <span class="text-danger">*</span></label>
                                        <select name="departemen_id" id="new_kar_dept" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Departemen --</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Jabatan <span class="text-danger">*</span></label>
                                        <select name="jabatan_id" id="new_kar_jabatan" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Jabatan --</option>
                                            @foreach($jabatans as $jab)
                                                <option value="{{ $jab->id }}">{{ $jab->position_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label small fw-semibold mb-1">No. HP</label>
                                            <small class="text-muted" style="font-size:0.7rem;">Maks 13 angka</small>
                                        </div>
                                        <input type="text" name="no_hp" id="new_kar_nohp" class="form-control form-control-sm" maxlength="13" inputmode="numeric" pattern="[0-9]*" placeholder="Opsional (angka saja)" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="new_kar_status" class="form-select form-select-sm" required>
                                            <option value="Aktif" selected>Aktif</option>
                                            <option value="Nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-2 mt-1">
                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" id="btn-cancel-form-karyawan-baru">Batal</button>
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" id="btn-simpan-karyawan-baru">
                                            <i class="bi bi-check-lg me-1"></i>Simpan Karyawan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- ===== END FORM TAMBAH KARYAWAN BARU ===== --}}

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== INITIALIZE LOOKUP KARYAWAN DATATABLE =====
        const tableLookup = $('#tableLookupKaryawan').DataTable({
            dom: '<"row"<"col-sm-12"f>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ karyawan',
                paginate: { previous: '<', next: '>' }
            },
            pageLength: 5
        });

        $(document).on('click', '.btn-lookup-karyawan', function() {
            currentLookupTarget = $(this).data('type');
            $('#modalLookupKaryawan').modal('show');
            // Adjust columns since table is inside modal
            setTimeout(() => {
                tableLookup.columns.adjust().draw();
            }, 200);
        });

        $(document).on('click', '.btn-select-karyawan', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let deptId = $(this).data('dept-id');

            if (currentLookupTarget === 'tambah') {
                $('#tambah_diminta_oleh').val(id);
                $('#tambah_diminta_oleh_nama').val(nama);
                if (deptId) {
                    $('#tambah_departemen_id').val(deptId).trigger('change');
                }
            } else if (currentLookupTarget === 'edit') {
                $('#edit_diminta_oleh').val(id);
                $('#edit_diminta_oleh_nama').val(nama);
                if (deptId) {
                    $('#edit_departemen_id').val(deptId).trigger('change');
                }
            }

            $('#modalLookupKaryawan').modal('hide');
        });

        $('#modalLookupKaryawan').on('hidden.bs.modal', function () {
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });

        // ===== TOGGLE FORM TAMBAH KARYAWAN BARU =====
        $('#btn-toggle-form-karyawan-baru').on('click', function () {
            $('#form-karyawan-baru-wrapper').toggleClass('d-none');
            let isOpen = !$('#form-karyawan-baru-wrapper').hasClass('d-none');
            $(this).html(isOpen
                ? '<i class="bi bi-dash-lg me-1"></i> Tutup Form'
                : '<i class="bi bi-plus-lg me-1"></i> Buka Form'
            );
        });

        $('#btn-cancel-form-karyawan-baru').on('click', function () {
            $('#form-karyawan-baru-wrapper').addClass('d-none');
            $('#btn-toggle-form-karyawan-baru').html('<i class="bi bi-plus-lg me-1"></i> Buka Form');
            $('#formTambahKaryawanBaru')[0].reset();
            $('#new_kar_status').val('Aktif');
        });

        // ===== REAL-TIME ALERT SAAT BATAS KARAKTER TERCAPAI =====
        const newKarLimits = [
            { id: '#new_kar_nama',   max: 50, label: 'Nama Lengkap' },
            { id: '#new_kar_inisial', max: 4, label: 'Inisial' },
            { id: '#new_kar_nohp',   max: 13, label: 'No. HP' },
        ];
        let alertShownNewKar = {};
        newKarLimits.forEach(function (item) {
            $(document).on('input', item.id, function () {
                let val = $(this).val();
                if (val.length >= item.max && !alertShownNewKar[item.id]) {
                    alertShownNewKar[item.id] = true;
                    Swal.fire({
                        title: 'Batas Karakter Terlampaui!',
                        html: `<b>${item.label}</b> dibatasi maksimal <b>${item.max} karakter</b>.`,
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { alertShownNewKar[item.id] = false; });
                }
            });
        });

        // ===== SIMPAN KARYAWAN BARU =====
        $('#btn-simpan-karyawan-baru').on('click', function () {
            let btn = $(this);
            let nama     = $.trim($('#new_kar_nama').val());
            let inisial  = $.trim($('#new_kar_inisial').val());
            let jk       = $('#new_kar_jk').val();
            let dept     = $('#new_kar_dept').val();
            let jabatan  = $('#new_kar_jabatan').val();
            let status   = $('#new_kar_status').val();

            if (!nama || !inisial || !jk || !dept || !jabatan || !status) {
                Swal.fire({ title: 'Data Belum Lengkap!', text: 'Isi semua field yang wajib diisi (bertanda *).', icon: 'warning' });
                return;
            }

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('master-karyawan.store') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    nama_lengkap:  nama,
                    inisial:       inisial,
                    jenis_kelamin: jk,
                    departemen_id: dept,
                    jabatan_id:    jabatan,
                    no_hp:         $('#new_kar_nohp').val(),
                    status:        status,
                },
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan Karyawan');

                    // Tambahkan baris baru ke DataTable tanpa reload halaman
                    if (res.karyawan) {
                        let k = res.karyawan;
                        tableLookup.row.add([
                            '<span class="fw-semibold">' + k.nama_lengkap + '</span>',
                            k.department_name,
                            k.position_name,
                            '<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-select-karyawan" ' +
                                'data-id="' + k.id + '" ' +
                                'data-nama="' + k.nama_lengkap + '" ' +
                                'data-dept-id="' + (k.departemen_id || '') + '">' +
                                'Pilih' +
                            '</button>'
                        ]).draw(false);
                    }

                    // Reset & tutup form
                    $('#formTambahKaryawanBaru')[0].reset();
                    $('#new_kar_status').val('Aktif');
                    $('#form-karyawan-baru-wrapper').addClass('d-none');
                    $('#btn-toggle-form-karyawan-baru').html('<i class="bi bi-plus-lg me-1"></i> Buka Form');

                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message || 'Karyawan baru berhasil ditambahkan.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (err) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan Karyawan');
                    let msg = 'Gagal menyimpan data karyawan.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        msg = '<b>Validasi gagal:</b><br>';
                        $.each(err.responseJSON.errors, function (k, v) { msg += '• ' + v[0] + '<br>'; });
                    } else if (err.status === 403) {
                        msg = 'Anda tidak memiliki izin untuk menambah karyawan.';
                    } else if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }
                    Swal.fire({ title: 'Gagal!', html: msg, icon: 'error' });
                }
            });
        });
    });
</script>
@endpush
