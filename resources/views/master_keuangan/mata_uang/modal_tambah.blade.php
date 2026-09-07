{{-- ===== MODAL TAMBAH MATA UANG ===== --}}
<div class="modal fade" id="modalTambahCurrency" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tambah Mata Uang</h5>
                        <small class="text-muted">Tambahkan kode valas / mata uang baru</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahCurrency" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode ISO (Max 5) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode ISO Mata Uang <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_curr_kode">0/5</small>
                            </div>
                            <input type="text" name="kode_currency" id="tambah_kode_currency"
                                class="form-control form-control-custom"
                                placeholder="Contoh: IDR, USD, SGD, JPY"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required style="text-transform: uppercase;">
                            <small class="text-muted mt-1 d-block">Maksimal 5 karakter.</small>
                        </div>

                        {{-- Nama Mata Uang (Max 20) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Mata Uang <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_curr_nama">0/20</small>
                            </div>
                            <input type="text" name="nama_currency" id="tambah_nama_currency"
                                class="form-control form-control-custom"
                                placeholder="Contoh: Rupiah, US Dollar"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 20 karakter.</small>
                        </div>

                        {{-- Simbol (Max 5) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Simbol Mata Uang <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_curr_simbol">0/5</small>
                            </div>
                            <input type="text" name="simbol" id="tambah_simbol"
                                class="form-control form-control-custom"
                                placeholder="Contoh: Rp, $, €, S$"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 5 karakter.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Upper case & Realtime Character Limits with Alert ─────────────────────
    let alertShownCurrKodeTambah = false;
    $('#tambah_kode_currency').on('input', function() {
        let val = $(this).val().toUpperCase();
        if (val.length > 5) {
            val = val.substring(0, 5);
            $(this).val(val);
            if (!alertShownCurrKodeTambah) {
                alertShownCurrKodeTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Kode ISO mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCurrKodeTambah = false; });
            }
        } else {
            $(this).val(val);
        }
        $('#cnt_tambah_curr_kode').text(val.length + '/5');
    });

    let alertShownCurrNamaTambah = false;
    $('#tambah_nama_currency').on('input', function() {
        let val = $(this).val();
        if (val.length > 20) {
            val = val.substring(0, 20);
            $(this).val(val);
            if (!alertShownCurrNamaTambah) {
                alertShownCurrNamaTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCurrNamaTambah = false; });
            }
        }
        $('#cnt_tambah_curr_nama').text(val.length + '/20');
    });

    let alertShownCurrSimbolTambah = false;
    $('#tambah_simbol').on('input', function() {
        let val = $(this).val();
        if (val.length > 5) {
            val = val.substring(0, 5);
            $(this).val(val);
            if (!alertShownCurrSimbolTambah) {
                alertShownCurrSimbolTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownCurrSimbolTambah = false; });
            }
        }
        $('#cnt_tambah_curr_simbol').text(val.length + '/5');
    });

    // ── Reset Form Modal Ditutup ──────────────────────────────────────────────
    $('#modalTambahCurrency').on('hidden.bs.modal', function () {
        $('#formTambahCurrency')[0].reset();
        $('#cnt_tambah_curr_kode').text('0/5');
        $('#cnt_tambah_curr_nama').text('0/20');
        $('#cnt_tambah_curr_simbol').text('0/5');
    });

    // ── STORE (Simpan Data Baru) ──────────────────────────────────────────────
    $('#formTambahCurrency').on('submit', function (e) {
        e.preventDefault();

        let kode   = $('#tambah_kode_currency').val().trim();
        let nama   = $('#tambah_nama_currency').val().trim();
        let simbol = $('#tambah_simbol').val().trim();

        if (kode.length > 5) {
            Swal.fire({ title: 'Gagal!', text: 'Kode ISO mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (nama.length > 20) {
            Swal.fire({ title: 'Gagal!', text: 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (simbol.length > 5) {
            Swal.fire({ title: 'Gagal!', text: 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('currency.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalTambahCurrency').modal('hide');
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function (err) {
                submitBtn.prop('disabled', false).text('Simpan Data');
                let errorMsg = 'Gagal menyimpan data mata uang.';
                if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                    errorMsg = '<b>Data tidak valid:</b><br>';
                    $.each(err.responseJSON.errors, function (key, val) {
                        errorMsg += '• ' + val[0] + '<br>';
                    });
                }
                Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
            }
        });
    });
</script>
@endpush
