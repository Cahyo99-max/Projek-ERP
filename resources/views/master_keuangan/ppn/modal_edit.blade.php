{{-- ===== MODAL EDIT PAJAK ===== --}}
<div class="modal fade" id="modalEditPajak" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-pencil-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Tipe Pajak</h5>
                        <small class="text-muted">Perbarui data jenis pajak</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditPajak" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_pajak_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode Pajak (Readonly saat edit) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode Pajak <span class="text-danger">*</span></label>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak dapat diubah</span>
                            </div>
                            <input type="text" name="kode_pajak" id="edit_kode_pajak"
                                class="form-control form-control-custom bg-light fw-bold text-secondary"
                                readonly tabindex="-1">
                            <small class="text-muted mt-1 d-block">Kode pajak sudah permanen dan tidak dapat diedit.</small>
                        </div>

                        {{-- Nama Pajak (Max 15) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Pajak <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_pajak_nama">0/15</small>
                            </div>
                            <input type="text" name="nama_pajak" id="edit_nama_pajak"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 15 karakter.</small>
                        </div>

                        {{-- Tarif (%) (Max 10 Digit) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Tarif (%) <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_pajak_tarif">0/10</small>
                            </div>
                            <div class="input-group">
                                <input type="number" name="tarif" id="edit_tarif"
                                    class="form-control form-control-custom"
                                    step="0.01" min="0"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Maksimal 10 digit.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan (Max 20) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Keterangan</label>
                                <small class="text-muted fw-bold" id="cnt_edit_pajak_ket">0/20</small>
                            </div>
                            <textarea name="keterangan" id="edit_keterangan" rows="2"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                            <small class="text-muted mt-1 d-block">Maksimal 20 karakter.</small>
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
    let alertShownPajakNamaEdit = false;
    $('#edit_nama_pajak').on('input', function() {
        let val = $(this).val();
        if (val.length > 15) {
            val = val.substring(0, 15);
            $(this).val(val);
            if (!alertShownPajakNamaEdit) {
                alertShownPajakNamaEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama pajak tidak boleh melebihi 15 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakNamaEdit = false; });
            }
        }
        $('#cnt_edit_pajak_nama').text(val.length + '/15');
    });

    let alertShownPajakTarifEdit = false;
    $('#edit_tarif').on('input', function() {
        let val = $(this).val();
        if (val.length > 10) {
            val = val.substring(0, 10);
            $(this).val(val);
            if (!alertShownPajakTarifEdit) {
                alertShownPajakTarifEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Tarif tidak boleh melebihi 10 digit yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakTarifEdit = false; });
            }
        }
        $('#cnt_edit_pajak_tarif').text(val.length + '/10');
    });

    let alertShownPajakKetEdit = false;
    $('#edit_keterangan').on('input', function() {
        let val = $(this).val();
        if (val.length > 20) {
            val = val.substring(0, 20);
            $(this).val(val);
            if (!alertShownPajakKetEdit) {
                alertShownPajakKetEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakKetEdit = false; });
            }
        }
        $('#cnt_edit_pajak_ket').text(val.length + '/20');
    });

    // ── Reset form saat modal ditutup ─────────────────────────────────────────
    $('#modalEditPajak').on('hidden.bs.modal', function () {
        $('#formEditPajak')[0].reset();
        $('#cnt_edit_pajak_nama').text('0/15');
        $('#cnt_edit_pajak_tarif').text('0/10');
        $('#cnt_edit_pajak_ket').text('0/20');
    });

    // ── Buka Modal Edit + Isi Data ────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        $.get('/pajak/' + id + '/edit', function (res) {
            let p = res.pajak;
            $('#edit_pajak_id').val(p.id);
            $('#edit_kode_pajak').val(p.kode_pajak);
            $('#edit_nama_pajak').val(p.nama_pajak).trigger('input');
            $('#edit_tarif').val(p.tarif).trigger('input');
            $('#edit_status').val(p.status);
            $('#edit_keterangan').val(p.keterangan || '').trigger('input');
            $('#modalEditPajak').modal('show');
        }).fail(function() {
            Swal.fire('Gagal!', 'Tidak dapat memuat data pajak.', 'error');
        });
    });

    // ── UPDATE (Simpan Edit) ──────────────────────────────────────────────────
    $('#formEditPajak').on('submit', function (e) {
        e.preventDefault();

        let nama  = $('#edit_nama_pajak').val().trim();
        let tarif = $('#edit_tarif').val().trim();
        let ket   = $('#edit_keterangan').val().trim();

        if (nama.length > 15) {
            Swal.fire({ title: 'Gagal!', text: 'Nama pajak tidak boleh melebihi 15 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (tarif.length > 10) {
            Swal.fire({ title: 'Gagal!', text: 'Tarif tidak boleh melebihi 10 digit yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (ket.length > 20) {
            Swal.fire({ title: 'Gagal!', text: 'Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let id = $('#edit_pajak_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: '/pajak/' + id + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalEditPajak').modal('hide');
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
                let errorMsg = 'Gagal memperbarui data pajak.';
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
