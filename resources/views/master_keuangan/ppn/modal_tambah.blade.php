{{-- ===== MODAL TAMBAH PAJAK ===== --}}
<div class="modal fade" id="modalTambahPajak" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-percent fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tambah Tipe Pajak</h5>
                        <small class="text-muted">Tambahkan jenis pajak / PPN baru</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahPajak" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode Pajak (Max 5, Manual / Otomatis) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode Pajak <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_pajak_kode">0/5</small>
                            </div>
                            <div class="input-group">
                                <input type="text" name="kode_pajak" id="tambah_kode_pajak"
                                    class="form-control form-control-custom"
                                    placeholder="Contoh: TX001, PPN11"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required style="text-transform: uppercase;">
                                <button type="button" id="btnAutoKodePajakTambah" class="btn btn-outline-primary fw-semibold px-3" style="border-radius: 0 8px 8px 0;">
                                    <i class="bi bi-magic me-1"></i> Otomatis
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">Maksimal 5 karakter. Ketik manual atau klik <b>Otomatis</b>.</small>
                        </div>

                        {{-- Nama Pajak (Max 15) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Pajak <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_pajak_nama">0/15</small>
                            </div>
                            <input type="text" name="nama_pajak" id="tambah_nama_pajak"
                                class="form-control form-control-custom"
                                placeholder="Contoh: PPN 11%, PPN 12%"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 15 karakter.</small>
                        </div>

                        {{-- Tarif (%) (Max 10 Digit) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Tarif (%) <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_pajak_tarif">0/10</small>
                            </div>
                            <div class="input-group">
                                <input type="number" name="tarif" id="tambah_tarif"
                                    class="form-control form-control-custom"
                                    placeholder="0.00" step="0.01" min="0"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Maksimal 10 digit.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan (Max 20) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Keterangan</label>
                                <small class="text-muted fw-bold" id="cnt_tambah_pajak_ket">0/20</small>
                            </div>
                            <textarea name="keterangan" id="tambah_keterangan" rows="2"
                                class="form-control form-control-custom"
                                placeholder="Catatan tambahan pajak..."
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                            <small class="text-muted mt-1 d-block">Maksimal 20 karakter.</small>
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
    // ── Generate Auto Kode Pajak ──────────────────────────────────────────────
    $('#btnAutoKodePajakTambah').on('click', function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get("{{ route('pajak.generate-code') }}", function(res) {
            $('#tambah_kode_pajak').val(res.kode).trigger('input');
            btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Otomatis');
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Otomatis');
            Swal.fire('Gagal!', 'Tidak dapat membuat kode pajak otomatis.', 'error');
        });
    });

    // ── Upper case & Realtime Character Limits with Alert ─────────────────────
    let alertShownPajakKodeTambah = false;
    $('#tambah_kode_pajak').on('input', function() {
        let val = $(this).val().toUpperCase();
        if (val.length > 5) {
            val = val.substring(0, 5);
            $(this).val(val);
            if (!alertShownPajakKodeTambah) {
                alertShownPajakKodeTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Kode pajak tidak boleh melebihi 5 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakKodeTambah = false; });
            }
        } else {
            $(this).val(val);
        }
        $('#cnt_tambah_pajak_kode').text(val.length + '/5');
    });

    let alertShownPajakNamaTambah = false;
    $('#tambah_nama_pajak').on('input', function() {
        let val = $(this).val();
        if (val.length > 15) {
            val = val.substring(0, 15);
            $(this).val(val);
            if (!alertShownPajakNamaTambah) {
                alertShownPajakNamaTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama pajak tidak boleh melebihi 15 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakNamaTambah = false; });
            }
        }
        $('#cnt_tambah_pajak_nama').text(val.length + '/15');
    });

    let alertShownPajakTarifTambah = false;
    $('#tambah_tarif').on('input', function() {
        let val = $(this).val();
        if (val.length > 10) {
            val = val.substring(0, 10);
            $(this).val(val);
            if (!alertShownPajakTarifTambah) {
                alertShownPajakTarifTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Tarif tidak boleh melebihi 10 digit yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakTarifTambah = false; });
            }
        }
        $('#cnt_tambah_pajak_tarif').text(val.length + '/10');
    });

    let alertShownPajakKetTambah = false;
    $('#tambah_keterangan').on('input', function() {
        let val = $(this).val();
        if (val.length > 20) {
            val = val.substring(0, 20);
            $(this).val(val);
            if (!alertShownPajakKetTambah) {
                alertShownPajakKetTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownPajakKetTambah = false; });
            }
        }
        $('#cnt_tambah_pajak_ket').text(val.length + '/20');
    });

    // ── Reset form saat modal ditutup ─────────────────────────────────────────
    $('#modalTambahPajak').on('hidden.bs.modal', function () {
        $('#formTambahPajak')[0].reset();
        $('#cnt_tambah_pajak_kode').text('0/5');
        $('#cnt_tambah_pajak_nama').text('0/15');
        $('#cnt_tambah_pajak_tarif').text('0/10');
        $('#cnt_tambah_pajak_ket').text('0/20');
    });

    // ── STORE (Tambah Pajak) ──────────────────────────────────────────────────
    $('#formTambahPajak').on('submit', function (e) {
        e.preventDefault();

        let kode  = $('#tambah_kode_pajak').val().trim();
        let nama  = $('#tambah_nama_pajak').val().trim();
        let tarif = $('#tambah_tarif').val().trim();
        let ket   = $('#tambah_keterangan').val().trim();

        if (kode.length > 5) {
            Swal.fire({ title: 'Gagal!', text: 'Kode pajak tidak boleh melebihi 5 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (nama.length > 15) {
            Swal.fire({ title: 'Gagal!', text: 'Nama pajak tidak boleh melebihi 15 karakter yang melebih.', icon: 'error' });
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

        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('pajak.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalTambahPajak').modal('hide');
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
                let errorMsg = 'Gagal menyimpan data pajak.';
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
