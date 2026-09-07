{{-- MODAL EDIT --}}
<x-modal_form id="modalEditDepartemen" formId="formEditDepartemen" title="<i class='bi bi-pencil-square me-2'></i> Edit Departemen">
    <x-slot name="slot">
        <input type="hidden" id="edit_dept_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kode Departemen</label>
                <input type="text" name="department_code" id="edit_department_code"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Maks 4" autocomplete="off" readonly>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Departemen <span class="text-danger">*</span></label>
                <input type="text" name="department_name" id="edit_department_name"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Teknologi Informasi (Maks 30)" autocomplete="off" required maxlength="100">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" id="edit_description" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Deskripsi singkat departemen (Maks 30)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate Character Limit Popup Warnings
        $('#edit_department_code').on('input', function() {
            if ($(this).val().length > 4) {
                $(this).val($(this).val().substring(0, 4));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Kode departemen tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_department_name').on('input', function() {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama departemen tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#edit_description').on('input', function() {
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

        // Trigger Edit Fetch
        $(document).on('click', '.btn-edit-dept', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/departemen/' + id + '/edit', function (res) {
                let d = res.departemen;
                $('#edit_dept_id').val(d.id);
                $('#edit_department_code').val(d.department_code);
                $('#edit_department_name').val(d.department_name);
                $('#edit_description').val(d.description);
                $('#edit_status').val(d.status);
                $('#modalEditDepartemen').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data departemen.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditDepartemen').on('hidden.bs.modal', function () {
            $('#formEditDepartemen')[0].reset();
        });

        // Submit Form Edit
        $('#formEditDepartemen').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const codeVal = $('#edit_department_code').val() || '';
            const namaVal = $('#edit_department_name').val() || '';
            const deskVal = $('#edit_description').val() || '';

            if (codeVal.length > 4) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Kode departemen tidak boleh lebih dari 4 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            if (namaVal.length > 30) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama departemen tidak boleh lebih dari 30 karakter.',
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
            let id = parseInt($('#edit_dept_id').val());

            $.ajax({
                url: '/departemen/' + id + '/update',
                type: 'POST',
                data: {
                    department_code: $('#edit_department_code').val(),
                    department_name: $('#edit_department_name').val(),
                    description:     $('#edit_description').val(),
                    status:          $('#edit_status').val(),
                },
                success: function (res) {
                    $('#modalEditDepartemen').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableDepartemen !== 'undefined') {
                            tableDepartemen.ajax.reload(null, false);
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
