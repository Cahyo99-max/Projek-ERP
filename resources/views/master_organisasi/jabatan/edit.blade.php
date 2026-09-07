{{-- MODAL EDIT --}}
<x-modal_form id="modalEditPosition" formId="formEditPosition" title="<i class='bi bi-pencil-square me-2'></i> Edit Jabatan">
    <x-slot name="slot">
        <input type="hidden" id="edit_pos_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kode Jabatan</label>
                <input type="text" name="position_code" id="edit_position_code"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Maks 4" autocomplete="off" readonly>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_pos_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="position_name" id="edit_position_name"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Manager IT, Staff Keuangan (Maks 30)" autocomplete="off" required maxlength="100">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                <select name="department_id" id="edit_department_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Departemen --</option>
                    {{-- Diisi dinamis via JS --}}
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" id="edit_pos_description" rows="3"
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
        $('#edit_position_code').on('input', function() {
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

        $('#edit_position_name').on('input', function() {
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

        $('#edit_pos_description').on('input', function() {
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

        // ===== BUKA MODAL EDIT =====
        $(document).on('click', '.btn-edit-pos', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/positions/' + id + '/edit', function (res) {
                let p = res.position;
                $('#edit_pos_id').val(p.id);
                $('#edit_position_code').val(p.position_code);
                $('#edit_position_name').val(p.position_name);
                $('#edit_pos_description').val(p.description);
                $('#edit_pos_status').val(p.status);

                // Isi dropdown departemen
                let deptSelect = $('#edit_department_id');
                deptSelect.empty().append('<option value="">-- Pilih Departemen --</option>');
                $.each(res.departments, function (i, d) {
                    deptSelect.append(
                        $('<option>', {
                            value: d.id,
                            text: d.department_name,
                            selected: d.id == p.department_id
                        })
                    );
                });

                $('#modalEditPosition').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data jabatan.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditPosition').on('hidden.bs.modal', function () {
            $('#formEditPosition')[0].reset();
        });

        // ===== SIMPAN EDIT =====
        $('#formEditPosition').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const codeVal = $('#edit_position_code').val() || '';
            const namaVal = $('#edit_position_name').val() || '';
            const deskVal = $('#edit_pos_description').val() || '';

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
            let id = parseInt($('#edit_pos_id').val());

            $.ajax({
                url: '/positions/' + id + '/update',
                type: 'POST',
                data: {
                    department_id: $('#edit_department_id').val(),
                    position_code: $('#edit_position_code').val(),
                    position_name: $('#edit_position_name').val(),
                    description:   $('#edit_pos_description').val(),
                    status:          $('#edit_pos_status').val(),
                },
                success: function (res) {
                    $('#modalEditPosition').modal('hide');
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
    });
</script>
@endpush
