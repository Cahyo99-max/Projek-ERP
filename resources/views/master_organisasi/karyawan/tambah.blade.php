{{-- MODAL TAMBAH --}}
<x-modal_form id="modalTambahKaryawan" formId="formTambahKaryawan" title="<i class='bi bi-person-plus-fill me-2'></i> Tambah Karyawan Baru" enctype="multipart/form-data">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" id="tambah_nik" class="form-control form-control-custom" placeholder="Maks 17 digit NIK" maxlength="100" required autocomplete="chrome-off">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Karyawan</label>
                <input type="text" name="kode_karyawan" id="tambah_kode_karyawan" class="form-control form-control-custom text-uppercase" placeholder="Otomatis / Isi manual" maxlength="50" autocomplete="chrome-off">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Inisial <span class="text-danger">*</span> <small class="text-muted">(Maks 4)</small></label>
                <input type="text" name="inisial" id="tambah_inisial" class="form-control form-control-custom text-uppercase" placeholder="Contoh: JD" autocomplete="chrome-off" maxlength="50" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" id="tambah_nama_lengkap" class="form-control form-control-custom" placeholder="Masukkan nama lengkap (Maks 50)" required autocomplete="chrome-off" maxlength="150">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                <select name="departemen_id" id="tambah_departemen_id" class="form-select form-control-custom" required>
                    <option value="">Pilih Departemen...</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                <select name="jabatan_id" id="tambah_jabatan_id" class="form-select form-control-custom" required>
                    <option value="">Pilih Jabatan...</option>
                    @foreach ($jabatans as $jab)
                        <option value="{{ $jab->id }}">{{ $jab->position_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                <input type="text" name="no_hp" id="tambah_no_hp" class="form-control form-control-custom" placeholder="08xxxxxxxxxx (Maks 13)" autocomplete="chrome-off" maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="jenis_kelamin" id="tambah_jenis_kelamin" class="form-select form-control-custom" required>
                    <option value="">Pilih...</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tambah_tanggal_lahir" class="form-control form-control-custom">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="tambah_tanggal_masuk" class="form-control form-control-custom">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status Karyawan <span class="text-danger">*</span></label>
                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Foto Karyawan</label>
                <input type="file" name="foto" id="tambah_foto" class="form-control form-control-custom" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat Lengkap</label>
                <textarea name="alamat" id="tambah_alamat" rows="3" class="form-control form-control-custom" placeholder="Masukkan alamat lengkap (Maks 60)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate Character Limit Popup Warnings
        $('#tambah_nik').on('input', function() {
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

        $('#tambah_kode_karyawan').on('input', function() {
            if ($(this).val().length > 4) {
                $(this).val($(this).val().substring(0, 4));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Kode karyawan tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#tambah_inisial').on('input', function() {
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

        $('#tambah_nama_lengkap').on('input', function() {
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

        $('#tambah_alamat').on('input', function() {
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

        $('#tambah_no_hp').on('input', function() {
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

        // Submit form Tambah Karyawan
        $('#formTambahKaryawan').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const nikVal = $('#tambah_nik').val() || '';
            const codeVal = $('#tambah_kode_karyawan').val() || '';
            const namaVal = $('#tambah_nama_lengkap').val() || '';
            const alamatVal = $('#tambah_alamat').val() || '';
            const noHpVal = $('#tambah_no_hp').val() || '';
            const inisialVal = $('#tambah_inisial').val() || '';

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

            $.ajax({
                url: '{{ route("master-karyawan.store") }}',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahKaryawan').modal('hide');
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

        $('#modalTambahKaryawan').on('hidden.bs.modal', function () {
            $('#formTambahKaryawan')[0].reset();
        });
    });
</script>
@endpush
