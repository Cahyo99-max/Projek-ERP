{{-- ===== MODAL EDIT MATA UANG ===== --}}
<div class="modal fade" id="modalEditCurrency" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-pencil-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Mata Uang</h5>
                        <small class="text-muted">Perbarui data mata uang</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditCurrency" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_currency_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode ISO (Readonly saat edit) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode ISO Mata Uang <span class="text-danger">*</span></label>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak dapat diubah</span>
                            </div>
                            <input type="text" name="kode_currency" id="edit_kode_currency"
                                class="form-control form-control-custom bg-light fw-bold text-secondary"
                                readonly tabindex="-1">
                            <small class="text-muted mt-1 d-block">Kode ISO mata uang sudah permanen dan tidak dapat diedit.</small>
                        </div>

                        {{-- Nama Mata Uang (Max 20) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Mata Uang <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_curr_nama">0/20</small>
                            </div>
                            <input type="text" name="nama_currency" id="edit_nama_currency"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 20 karakter.</small>
                        </div>

                        {{-- Simbol (Max 5) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Simbol Mata Uang <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_curr_simbol">0/5</small>
                            </div>
                            <input type="text" name="simbol" id="edit_simbol"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 5 karakter.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let alertShownCurrNamaEdit = false;
    $('#edit_nama_currency').on('input', function() {
        let val = $(this).val();
        if (val.length > 20) {
            val = val.substring(0, 20);
            $(this).val(val);
            if (!alertShownCurrNamaEdit) {
                alertShownCurrNamaEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCurrNamaEdit = false; });
            }
        }
        $('#cnt_edit_curr_nama').text(val.length + '/20');
    });

    let alertShownCurrSimbolEdit = false;
    $('#edit_simbol').on('input', function() {
        let val = $(this).val();
        if (val.length > 5) {
            val = val.substring(0, 5);
            $(this).val(val);
            if (!alertShownCurrSimbolEdit) {
                alertShownCurrSimbolEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCurrSimbolEdit = false; });
            }
        }
        $('#cnt_edit_curr_simbol').text(val.length + '/5');
    });

    // ── Reset Form Modal Ditutup ──────────────────────────────────────────────
    $('#modalEditCurrency').on('hidden.bs.modal', function () {
        $('#formEditCurrency')[0].reset();
        $('#cnt_edit_curr_nama').text('0/20');
        $('#cnt_edit_curr_simbol').text('0/5');
    });

    // ── Buka Modal Edit + Load Data ───────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        $.get('/currency/' + id + '/edit', function (res) {
            let c = res.currency;
            $('#edit_currency_id').val(c.id);
            $('#edit_kode_currency').val(c.kode_currency);
            $('#edit_nama_currency').val(c.nama_currency).trigger('input');
            $('#edit_simbol').val(c.simbol).trigger('input');
            $('#edit_status').val(c.status);
            
            $('#modalEditCurrency').modal('show');
        }).fail(function() {
            Swal.fire('Gagal!', 'Tidak dapat memuat data mata uang.', 'error');
        });
    });

    // ── UPDATE (Simpan Perubahan) ─────────────────────────────────────────────
    $('#formEditCurrency').on('submit', function (e) {
        e.preventDefault();

        let nama   = $('#edit_nama_currency').val().trim();
        let simbol = $('#edit_simbol').val().trim();

        if (nama.length > 20) {
            Swal.fire({ title: 'Gagal!', text: 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (simbol.length > 5) {
            Swal.fire({ title: 'Gagal!', text: 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let id = $('#edit_currency_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "/currency/" + id + "/update",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalEditCurrency').modal('hide');
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function (err) {
                submitBtn.prop('disabled', false).text('Simpan Perubahan');
                let errorMsg = 'Gagal memperbarui data mata uang.';
                if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                    errorMsg = '<b>Data tidak valid:</b><br>';
                    $.each(err.responseJSON.errors, function (key, val) {
                        errorMsg += '• ' + val[0] + '<br>';
                    });
                } else if (err.status === 403) {
                    errorMsg = 'Akses ditolak. Anda tidak memiliki izin.';
                }
                Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
            }
        });
    });
</script>
@endpush
