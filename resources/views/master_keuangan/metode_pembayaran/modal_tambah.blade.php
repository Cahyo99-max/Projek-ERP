{{-- ===== MODAL TAMBAH METODE PEMBAYARAN ===== --}}
<div class="modal fade" id="modalTambahMethod" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-credit-card fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tambah Metode</h5>
                        <small class="text-muted">Tambahkan opsi metode pembayaran baru</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahMethod" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Kode Metode (Max 5, Manual / Otomatis) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Kode Metode <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_kode">0/5</small>
                            </div>
                            <div class="input-group">
                                <input type="text" name="kode_metode" id="tambah_kode_metode"
                                    class="form-control form-control-custom"
                                    placeholder="Contoh: PM001"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required style="text-transform: uppercase;">
                                <button type="button" id="btnAutoKodeTambah" class="btn btn-outline-primary fw-semibold px-3" style="border-radius: 0 8px 8px 0;">
                                    <i class="bi bi-magic me-1"></i> Otomatis
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">Maksimal 5 karakter. Ketik manual atau klik <b>Otomatis</b>.</small>
                        </div>

                        {{-- Nama Metode (Max 35) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Metode <span class="text-danger">*</span></label>
                                <small class="text-muted fw-bold" id="cnt_tambah_nama">0/35</small>
                            </div>
                            <input type="text" name="nama_metode" id="tambah_nama_metode"
                                class="form-control form-control-custom"
                                placeholder="Contoh: Cash / Tunai"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
                            <small class="text-muted mt-1 d-block">Maksimal 35 karakter.</small>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                                <option value="Active">Aktif</option>
                                <option value="Inactive">Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan (Max 30) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Keterangan</label>
                                <small class="text-muted fw-bold" id="cnt_tambah_ket">0/30</small>
                            </div>
                            <textarea name="keterangan" id="tambah_keterangan" rows="2"
                                class="form-control form-control-custom"
                                placeholder="Catatan tambahan..."
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                            <small class="text-muted mt-1 d-block">Maksimal 30 karakter.</small>
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
    // ── Generate Auto Kode ──────────────────────────────────────────────────
    $('#btnAutoKodeTambah').on('click', function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get("{{ route('payment-method.generate-code') }}", function(res) {
            $('#tambah_kode_metode').val(res.kode).trigger('input');
            btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Otomatis');
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Otomatis');
            Swal.fire('Gagal!', 'Tidak dapat membuat kode otomatis.', 'error');
        });
    });

    // ── Upper Case & Realtime Character Limits with Alert ─────────────────────
    let alertShownKodeTambah = false;
    $('#tambah_kode_metode').on('input', function() {
        let val = $(this).val().toUpperCase();
        if (val.length > 5) {
            val = val.substring(0, 5);
            $(this).val(val);
            if (!alertShownKodeTambah) {
                alertShownKodeTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Kode metode tidak boleh melebihi 5 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownKodeTambah = false; });
            }
        } else {
            $(this).val(val);
        }
        $('#cnt_tambah_kode').text(val.length + '/5');
    });

    let alertShownNamaTambah = false;
    $('#tambah_nama_metode').on('input', function() {
        let val = $(this).val();
        if (val.length > 35) {
            val = val.substring(0, 35);
            $(this).val(val);
            if (!alertShownNamaTambah) {
                alertShownNamaTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownNamaTambah = false; });
            }
        }
        $('#cnt_tambah_nama').text(val.length + '/35');
    });

    let alertShownKetTambah = false;
    $('#tambah_keterangan').on('input', function() {
        let val = $(this).val();
        if (val.length > 30) {
            val = val.substring(0, 30);
            $(this).val(val);
            if (!alertShownKetTambah) {
                alertShownKetTambah = true;
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                }).then(() => { alertShownKetTambah = false; });
            }
        }
        $('#cnt_tambah_ket').text(val.length + '/30');
    });

    // ── Reset Form Modal Ditutup ──────────────────────────────────────────────
    $('#modalTambahMethod').on('hidden.bs.modal', function () {
        $('#formTambahMethod')[0].reset();
        $('#cnt_tambah_kode').text('0/5');
        $('#cnt_tambah_nama').text('0/35');
        $('#cnt_tambah_ket').text('0/30');
    });

    // ── STORE (Simpan Data Baru) ──────────────────────────────────────────────
    $('#formTambahMethod').on('submit', function (e) {
        e.preventDefault();

        let kode = $('#tambah_kode_metode').val().trim();
        let nama = $('#tambah_nama_metode').val().trim();
        let ket  = $('#tambah_keterangan').val().trim();

        if (kode.length > 5) {
            Swal.fire({ title: 'Gagal!', text: 'Kode metode tidak boleh melebihi 5 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (nama.length > 35) {
            Swal.fire({ title: 'Gagal!', text: 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }
        if (ket.length > 30) {
            Swal.fire({ title: 'Gagal!', text: 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.', icon: 'error' });
            return;
        }

        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('payment-method.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modalTambahMethod').modal('hide');
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
                let errorMsg = 'Gagal menyimpan data metode.';
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
