{{-- ===== MODAL EDIT METODE PEMBAYARAN ===== --}}
<div class="modal fade" id="modalEditMethod" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-pencil-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Metode</h5>
                        <small class="text-muted">Perbarui data metode pembayaran</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditMethod" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_method_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode Metode (Readonly) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode Metode <span class="text-danger">*</span></label>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak dapat diubah</span>
                            </div>
                            <input type="text" name="kode_metode" id="edit_kode_metode"
                                class="form-control form-control-custom bg-light fw-bold text-secondary"
                                readonly tabindex="-1">
                            <small class="text-muted mt-1 d-block">Kode metode sudah permanen dan tidak dapat diedit.</small>
                        </div>

                        {{-- Nama Metode (Max 35) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Metode <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_edit_nama">0/35</small>
                            </div>
                            <input type="text" name="nama_metode" id="edit_nama_metode"
                                class="form-control form-control-custom"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 35 karakter.</small>
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
                                <small class="text-muted fw-bold" id="cnt_edit_ket">0/30</small>
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
    let alertShownNamaEdit = false;
    $('#edit_nama_metode').on('input', function() {
        let val = $(this).val();
        if (val.length > 35) {
            val = val.substring(0, 35);
            $(this).val(val);
            if (!alertShownNamaEdit) {
                alertShownNamaEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownNamaEdit = false; });
            }
        }
        $('#cnt_edit_nama').text(val.length + '/35');
    });

    let alertShownKetEdit = false;
    $('#edit_keterangan').on('input', function() {
        let val = $(this).val();
        if (val.length > 30) {
            val = val.substring(0, 30);
            $(this).val(val);
            if (!alertShownKetEdit) {
                alertShownKetEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownKetEdit = false; });
            }
        }
        $('#cnt_edit_ket').text(val.length + '/30');
    });

    // ── Reset Form Modal Ditutup ──────────────────────────────────────────────
    $('#modalEditMethod').on('hidden.bs.modal', function () {
        $('#formEditMethod')[0].reset();
        $('#cnt_edit_nama').text('0/35');
        $('#cnt_edit_ket').text('0/30');
    });

    // ── Buka Modal Edit + Load Data ───────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        $.get('/payment-method/' + id + '/edit', function (res) {
            let method = res.method;
            $('#edit_method_id').val(method.id);
            $('#edit_kode_metode').val(method.kode_metode);
            $('#edit_nama_metode').val(method.nama_metode).trigger('input');
            $('#edit_status').val(method.status);
            $('#edit_keterangan').val(method.keterangan || '').trigger('input');
            $('#modalEditMethod').modal('show');
        }).fail(function() {
            Swal.fire('Gagal!', 'Tidak dapat memuat data metode.', 'error');
        });
    });

    // ── UPDATE (Simpan Perubahan) ─────────────────────────────────────────────
    $('#formEditMethod').on('submit', function (e) {
        e.preventDefault();

        let nama = $('#edit_nama_metode').val().trim();
        let ket  = $('#edit_keterangan').val().trim();

        if (nama.length > 35) {
            Swal.fire({ title: 'Gagal!', text: 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (ket.length > 30) {
            Swal.fire({ title: 'Gagal!', text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let id = $('#edit_method_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: '/payment-method/' + id + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalEditMethod').modal('hide');
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
                let errorMsg = 'Gagal memperbarui data metode.';
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
