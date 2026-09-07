{{-- ===== MODAL EDIT COA ===== --}}
<div class="modal fade" id="modalEditCoa" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-pencil-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Akun COA</h5>
                        <small class="text-muted">Perbarui data akun perkiraan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditCoa" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_coa_id" name="id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Kode Akun <span class="text-danger">*</span></label>
                            <small class="text-muted fw-bold" id="cnt_edit_coa_code">0/10</small>
                        </div>
                        <input type="text" class="form-control rounded-3" id="edit_code" name="code"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                        <small class="text-muted mt-1 d-block">Maksimal 10 karakter.</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Nama Akun <span class="text-danger">*</span></label>
                            <small class="text-muted fw-bold" id="cnt_edit_coa_name">0/40</small>
                        </div>
                        <input type="text" class="form-control rounded-3" id="edit_name" name="name"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                        <small class="text-muted mt-1 d-block">Maksimal 40 karakter.</small>
                    </div>

                    <div class="row mb-3 g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Master Tipe Akun</label>
                            <select class="form-select rounded-3" id="edit_master_tipe_akun_id" name="master_tipe_akun_id">
                                <option value="">-- Pilih Tipe Akun --</option>
                                @foreach($accountTypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->kode_tipe }} - {{ $t->nama_tipe }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Saldo Normal <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="edit_normal_balance" name="normal_balance" required>
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Akun Induk (Parent)</label>
                        <select class="form-select rounded-3" id="edit_parent_id" name="parent_id">
                            <option value="">-- Tanpa Induk (Top Level) --</option>
                            @foreach($parentAccounts as $pAcc)
                                <option value="{{ $pAcc->id }}">{{ $pAcc->code }} - {{ $pAcc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3 g-2">
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_header" name="is_header" value="1">
                                <label class="form-check-label small" for="edit_is_header">Akun Header (Induk)</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label small" for="edit_is_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Keterangan / Deskripsi</label>
                            <small class="text-muted fw-bold" id="cnt_edit_coa_desc">0/30</small>
                        </div>
                        <textarea class="form-control rounded-3" id="edit_description" name="description" rows="2"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                        <small class="text-muted mt-1 d-block">Maksimal 30 karakter.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSubmitEditCoa">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let alertShownCoaCodeEdit = false;
    $('#edit_code').on('input', function() {
        let val = $(this).val();
        if (val.length > 10) {
            val = val.substring(0, 10);
            $(this).val(val);
            if (!alertShownCoaCodeEdit) {
                alertShownCoaCodeEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Kode Akun tidak boleh melebihi 10 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCoaCodeEdit = false; });
            }
        }
        $('#cnt_edit_coa_code').text(val.length + '/10');
    });

    let alertShownCoaNameEdit = false;
    $('#edit_name').on('input', function() {
        let val = $(this).val();
        if (val.length > 40) {
            val = val.substring(0, 40);
            $(this).val(val);
            if (!alertShownCoaNameEdit) {
                alertShownCoaNameEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama Akun tidak boleh melebihi 40 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCoaNameEdit = false; });
            }
        }
        $('#cnt_edit_coa_name').text(val.length + '/40');
    });

    let alertShownCoaDescEdit = false;
    $('#edit_description').on('input', function() {
        let val = $(this).val();
        if (val.length > 30) {
            val = val.substring(0, 30);
            $(this).val(val);
            if (!alertShownCoaDescEdit) {
                alertShownCoaDescEdit = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Deskripsi tidak boleh melebihi 30 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCoaDescEdit = false; });
            }
        }
        $('#cnt_edit_coa_desc').text(val.length + '/30');
    });

    // Reset Form Edit Saat Modal Ditutup
    $('#modalEditCoa').on('hidden.bs.modal', function () {
        $('#formEditCoa')[0].reset();
        $('#cnt_edit_coa_code').text('0/10');
        $('#cnt_edit_coa_name').text('0/40');
        $('#cnt_edit_coa_desc').text('0/30');
    });

    // Edit Button Handler - Load Data
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get(`/finance/chart-of-accounts/${id}/edit`, function(res) {
            const acc = res.account;
            $('#edit_coa_id').val(acc.id);
            $('#edit_code').val(acc.code).trigger('input');
            $('#edit_name').val(acc.name).trigger('input');
            $('#edit_master_tipe_akun_id').val(acc.master_tipe_akun_id || '');
            $('#edit_normal_balance').val(acc.normal_balance || 'debit');
            $('#edit_parent_id').val(acc.parent_id || '');
            $('#edit_is_header').prop('checked', acc.is_header);
            $('#edit_is_active').prop('checked', acc.is_active);
            $('#edit_description').val(acc.description || '').trigger('input');

            $('#modalEditCoa').modal('show');
        }).fail(function() {
            Swal.fire('Gagal!', 'Tidak dapat memuat data Akun COA.', 'error');
        });
    });

    // Submit Form Update Edit
    $('#formEditCoa').on('submit', function(e) {
        e.preventDefault();

        const codeVal = ($('#edit_code').val() || '').trim();
        const nameVal = ($('#edit_name').val() || '').trim();
        const descVal = ($('#edit_description').val() || '').trim();

        if (codeVal.length > 10) {
            Swal.fire({ title: 'Gagal!', text: 'Kode Akun tidak boleh melebihi 10 karakter yang telah ditentukan.', icon: 'error' });
            return false;
        }
        if (nameVal.length > 40) {
            Swal.fire({ title: 'Gagal!', text: 'Nama Akun tidak boleh melebihi 40 karakter yang telah ditentukan.', icon: 'error' });
            return false;
        }
        if (descVal.length > 30) {
            Swal.fire({ title: 'Gagal!', text: 'Deskripsi tidak boleh melebihi 30 karakter yang telah ditentukan.', icon: 'error' });
            return false;
        }

        const id = $('#edit_coa_id').val();
        const submitBtn = $('#btnSubmitEditCoa');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: `/finance/chart-of-accounts/${id}`,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalEditCoa').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text('Simpan Perubahan');
                const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Terjadi kesalahan sistem.');
                Swal.fire({ title: 'Gagal!', html: msg, icon: 'error' });
            }
        });
    });
</script>
@endpush
