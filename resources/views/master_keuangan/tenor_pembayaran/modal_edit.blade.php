{{-- ===== MODAL EDIT TENOR PEMBAYARAN ===== --}}
<div class="modal fade" id="modalEditTerm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-pencil-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Tenor</h5>
                        <small class="text-muted">Perbarui data tenor pembayaran</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTerm" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_term_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Nama Tenor (Max 15) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Tenor <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_term_nama">0/15</small>
                            </div>
                            <input type="text" name="nama_term" id="edit_nama_term"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 15 karakter.</small>
                        </div>

                        {{-- Jumlah Hari (Max 10 Digit) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Jumlah Hari <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_term_hari">0/10</small>
                            </div>
                            <div class="input-group">
                                <input type="number" name="jumlah_hari" id="edit_jumlah_hari"
                                    class="form-control form-control-custom" min="0"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                                <span class="input-group-text bg-light text-muted fw-bold">Hari</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Gunakan 0 untuk COD. Maksimal 10 digit.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan (Max 30) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Keterangan</label>
                                <small class="text-muted fw-bold" id="cnt_edit_term_ket">0/30</small>
                            </div>
                            <textarea name="keterangan" id="edit_keterangan" rows="2"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                            <small class="text-muted mt-1 d-block">Maksimal 30 karakter.</small>
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
    let alertShownEditTermNama = false;
    $('#edit_nama_term').on('input', function() {
        let val = $(this).val();
        if (val.length > 15) {
            val = val.substring(0, 15);
            $(this).val(val);
            if (!alertShownEditTermNama) {
                alertShownEditTermNama = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama tenor tidak boleh melebihi 15 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownEditTermNama = false; });
            }
        }
        $('#cnt_edit_term_nama').text(val.length + '/15');
    });

    let alertShownEditTermHari = false;
    $('#edit_jumlah_hari').on('input', function() {
        let val = $(this).val();
        if (val.length > 10) {
            val = val.substring(0, 10);
            $(this).val(val);
            if (!alertShownEditTermHari) {
                alertShownEditTermHari = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Jumlah hari tidak boleh melebihi 10 digit yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownEditTermHari = false; });
            }
        }
        $('#cnt_edit_term_hari').text(val.length + '/10');
    });

    let alertShownEditTermKet = false;
    $('#edit_keterangan').on('input', function() {
        let val = $(this).val();
        if (val.length > 30) {
            val = val.substring(0, 30);
            $(this).val(val);
            if (!alertShownEditTermKet) {
                alertShownEditTermKet = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownEditTermKet = false; });
            }
        }
        $('#cnt_edit_term_ket').text(val.length + '/30');
    });

    // ── Reset Form Modal Ditutup ──────────────────────────────────────────────
    $('#modalEditTerm').on('hidden.bs.modal', function () {
        $('#formEditTerm')[0].reset();
        $('#cnt_edit_term_nama').text('0/15');
        $('#cnt_edit_term_hari').text('0/10');
        $('#cnt_edit_term_ket').text('0/30');
    });

    // ── Buka Modal Edit + Load Data ───────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        $.get('/payment-term/' + id + '/edit', function (res) {
            let term = res.term;
            $('#edit_term_id').val(term.id);
            $('#edit_nama_term').val(term.nama_term).trigger('input');
            $('#edit_jumlah_hari').val(term.jumlah_hari).trigger('input');
            $('#edit_status').val(term.status);
            $('#edit_keterangan').val(term.keterangan || '').trigger('input');
            $('#modalEditTerm').modal('show');
        }).fail(function() {
            Swal.fire('Gagal!', 'Tidak dapat memuat data tenor.', 'error');
        });
    });

    // ── UPDATE (Simpan Perubahan) ─────────────────────────────────────────────
    $('#formEditTerm').on('submit', function (e) {
        e.preventDefault();

        let nama = $('#edit_nama_term').val().trim();
        let hari = $('#edit_jumlah_hari').val().trim();
        let ket  = $('#edit_keterangan').val().trim();

        if (nama.length > 15) {
            Swal.fire({ title: 'Gagal!', text: 'Nama tenor tidak boleh melebihi 15 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (hari.length > 10) {
            Swal.fire({ title: 'Gagal!', text: 'Jumlah hari tidak boleh melebihi 10 digit yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (ket.length > 30) {
            Swal.fire({ title: 'Gagal!', text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let id = $('#edit_term_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: '/payment-term/' + id + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalEditTerm').modal('hide');
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
                let errorMsg = 'Gagal memperbarui data tenor.';
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
