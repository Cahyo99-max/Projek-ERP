{{-- MODAL TAMBAH --}}
<x-modal_form id="modalTambahPosition" formId="formTambahPosition" title="<i class='bi bi-person-badge me-2'></i> Tambah Jabatan Baru">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kode Jabatan <small class="text-muted">(Opsional)</small></label>
                <input type="text" name="position_code" id="tambah_position_code"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Otomatis / Isi manual (Maks 4)" autocomplete="off" maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="tambah_pos_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="position_name" id="tambah_position_name"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Manager IT, Staff Keuangan (Maks 30)" autocomplete="off" required maxlength="100">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                <select name="department_id" id="tambah_department_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" id="tambah_pos_description" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Deskripsi tugas atau wewenang (Maks 30)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate Character Limit Popup Warnings
        $('#tambah_position_code').on('input', function() {
            if ($(this).val().length > 4) {
                $(this).val($(this).val().substring(0, 4));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Kode jabatan tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#tambah_position_name').on('input', function() {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama jabatan tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#tambah_pos_description').on('input', function() {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Deskripsi tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        // Submit form Tambah Position
        $('#formTambahPosition').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const codeVal = $('#tambah_position_code').val() || '';
            const namaVal = $('#tambah_position_name').val() || '';
            const deskVal = $('#tambah_pos_description').val() || '';

            if (codeVal.length > 4) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Kode jabatan tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            if (namaVal.length > 30) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama jabatan tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            if (deskVal.length > 30) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Deskripsi tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            btn.prop('disabled', true);

            $.ajax({
                url: '/positions/store',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahPosition').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tablePositions !== 'undefined') {
                            tablePositions.ajax.reload(null, false);
                        }
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

        $('#modalTambahPosition').on('hidden.bs.modal', function () {
            $('#formTambahPosition')[0].reset();
        });
    });
</script>
@endpush
