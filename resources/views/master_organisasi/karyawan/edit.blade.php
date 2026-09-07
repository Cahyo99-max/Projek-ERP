{{-- MODAL EDIT --}}
<x-modal_form id="modalEditKaryawan" formId="formEditKaryawan" title="<i class='bi bi-pencil-square me-2'></i> Edit Data Karyawan" enctype="multipart/form-data">
    <x-slot name="slot">
        <input type="hidden" id="edit_id">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" id="edit_nik" class="form-control form-control-custom" placeholder="Maks 17 digit NIK" maxlength="100" required autocomplete="chrome-off">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Karyawan</label>
                <input type="text" name="kode_karyawan" id="edit_kode_karyawan" class="form-control form-control-custom text-uppercase" placeholder="Kode Karyawan" autocomplete="chrome-off" readonly>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Inisial <span class="text-danger">*</span> <small class="text-muted">(Maks 4)</small></label>
                <input type="text" name="inisial" id="edit_inisial" class="form-control form-control-custom text-uppercase" placeholder="Contoh: JD" autocomplete="chrome-off" maxlength="50" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control form-control-custom" placeholder="Masukkan nama lengkap (Maks 50)" required autocomplete="chrome-off" maxlength="150">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                <select name="departemen_id" id="edit_departemen_id" class="form-select form-control-custom" required>
                    <option value="">Pilih Departemen...</option>
                    {{-- Diisi via JS --}}
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                <select name="jabatan_id" id="edit_jabatan_id" class="form-select form-control-custom" required>
                    <option value="">Pilih Jabatan...</option>
                    {{-- Diisi via JS --}}
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                <input type="text" name="no_hp" id="edit_no_hp" class="form-control form-control-custom" placeholder="08xxxxxxxxxx (Maks 13)" autocomplete="chrome-off" maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select form-control-custom" required>
                    <option value="">Pilih...</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" class="form-control form-control-custom">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="edit_tanggal_masuk" class="form-control form-control-custom">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status Karyawan <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Foto Karyawan <small class="text-muted">(Biarkan kosong jika tidak diganti)</small></label>
                <input type="file" name="foto" id="edit_foto" class="form-control form-control-custom" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat Lengkap</label>
                <textarea name="alamat" id="edit_alamat" rows="3" class="form-control form-control-custom" placeholder="Masukkan alamat lengkap (Maks 60)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate Character Limit Popup Warnings
        $('#edit_nik').on('input', function() {
            if ($(this).val().length > 17) {
                $(this).val($(this).val().substring(0, 17));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'NIK tidak boleh lebih dari 17 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_nama_lengkap').on('input', function() {
            if ($(this).val().length > 50) {
                $(this).val($(this).val().substring(0, 50));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama lengkap tidak boleh lebih dari 50 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_inisial').on('input', function() {
            if ($(this).val().length > 4) {
                $(this).val($(this).val().substring(0, 4));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Inisial tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_alamat').on('input', function() {
            if ($(this).val().length > 60) {
                $(this).val($(this).val().substring(0, 60));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Alamat lengkap tidak boleh lebih dari 60 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_no_hp').on('input', function() {
            // Hapus karakter selain angka
            let val = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(val);

            if (val.length > 13) {
                $(this).val(val.substring(0, 13));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'No. HP tidak boleh lebih dari 13 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        // ===== GET DATA UNTUK EDIT =====
        $(document).on('click', '.btn-edit-karyawan', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/karyawan/' + id + '/edit', function (res) {
                let k = res.karyawan;
                $('#edit_id').val(k.id);
                $('#edit_nik').val(k.nik);
                $('#edit_kode_karyawan').val(k.kode_karyawan);
                $('#edit_inisial').val(k.inisial);
                $('#edit_nama_lengkap').val(k.nama_lengkap);
                $('#edit_no_hp').val(k.no_hp);
                $('#edit_alamat').val(k.alamat);
                $('#edit_jenis_kelamin').val(k.jenis_kelamin);
                $('#edit_tanggal_lahir').val(k.tanggal_lahir);
                $('#edit_tanggal_masuk').val(k.tanggal_masuk);
                $('#edit_status').val(k.status);

                let deptSelect = $('#edit_departemen_id');
                deptSelect.empty().append('<option value="">Pilih Departemen...</option>');
                $.each(res.departments, function (i, d) {
                    deptSelect.append($('<option>', {
                        value: d.id, 
                        text: d.department_name, 
                        selected: d.id === k.departemen_id
                    }));
                });

                let jabSelect = $('#edit_jabatan_id');
                jabSelect.empty().append('<option value="">Pilih Jabatan...</option>');
                $.each(res.jabatans, function (i, j) {
                    jabSelect.append($('<option>', {
                        value: j.id, 
                        text: j.position_name, 
                        selected: j.id === k.jabatan_id
                    }));
                });

                $('#edit_foto').val('');
                $('#modalEditKaryawan').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data karyawan.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditKaryawan').on('hidden.bs.modal', function () {
            $('#formEditKaryawan')[0].reset();
        });

        // ===== UPDATE DATA =====
        $('#formEditKaryawan').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const nikVal = $('#edit_nik').val() || '';
            const codeVal = $('#edit_kode_karyawan').val() || '';
            const namaVal = $('#edit_nama_lengkap').val() || '';
            const alamatVal = $('#edit_alamat').val() || '';
            const noHpVal = $('#edit_no_hp').val() || '';
            const inisialVal = $('#edit_inisial').val() || '';

            if (nikVal.length > 17) {
                Swal.fire({ title: 'Peringatan!', text: 'NIK tidak boleh lebih dari 17 karakter.', icon: 'warning' });
                return false;
            }
            if (codeVal.length > 4) {
                Swal.fire({ title: 'Peringatan!', text: 'Kode karyawan tidak boleh lebih dari 4 karakter.', icon: 'warning' });
                return false;
            }
            if (namaVal.length > 50) {
                Swal.fire({ title: 'Peringatan!', text: 'Nama lengkap tidak boleh lebih dari 50 karakter.', icon: 'warning' });
                return false;
            }
            if (alamatVal.length > 60) {
                Swal.fire({ title: 'Peringatan!', text: 'Alamat tidak boleh lebih dari 60 karakter.', icon: 'warning' });
                return false;
            }
            if (noHpVal.length > 13) {
                Swal.fire({ title: 'Peringatan!', text: 'No. HP tidak boleh lebih dari 13 karakter.', icon: 'warning' });
                return false;
            }
            if (inisialVal.length > 4) {
                Swal.fire({ title: 'Peringatan!', text: 'Inisial tidak boleh lebih dari 4 karakter.', icon: 'warning' });
                return false;
            }

            btn.prop('disabled', true);
            let id = parseInt($('#edit_id').val());

            $.ajax({
                url: '/karyawan/' + id + '/update',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalEditKaryawan').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => { 
                        if (typeof tableKaryawan !== 'undefined') {
                            tableKaryawan.ajax.reload(null, false); 
                        }
                        location.reload(); // Refresh to update summaries counters
                    });
                },
                error: function (err) {
                    let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + escapeHtml(val[0]) + '<br>';
                        });
                    } else if (err.status === 429) {
                        errorMsg = 'Terlalu banyak permintaan. Harap tunggu sebentar.';
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = escapeHtml(err.responseJSON.message);
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });
    });
</script>
@endpush
