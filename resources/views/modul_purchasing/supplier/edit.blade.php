{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditSupplier" formId="formEditSupplier" title="<i class='bi bi-pencil-square me-2'></i> Edit Supplier / Vendor" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" id="edit_supplier_id">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="editTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="edit-umum-tab" data-bs-toggle="tab" data-bs-target="#edit-umum" type="button" role="tab" aria-controls="edit-umum" aria-selected="true">
                    <i class="bi bi-info-circle me-1.5"></i> Informasi Umum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="edit-alamat-tab" data-bs-toggle="tab" data-bs-target="#edit-alamat" type="button" role="tab" aria-controls="edit-alamat" aria-selected="false">
                    <i class="bi bi-geo-alt me-1.5"></i> Alamat & Pajak
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="edit-keuangan-tab" data-bs-toggle="tab" data-bs-target="#edit-keuangan" type="button" role="tab" aria-controls="edit-keuangan" aria-selected="false">
                    <i class="bi bi-wallet2 me-1.5"></i> Keuangan & Bank
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="editTabsContent">
            <!-- Tab 1: Informasi Umum -->
            <div class="tab-pane fade show active" id="edit-umum" role="tabpanel" aria-labelledby="edit-umum-tab">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Kode Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="kode_supplier" id="edit_kode_supplier"
                            class="form-control form-control-custom bg-light"
                            placeholder="Kode Supplier" readonly>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="nama_supplier" id="edit_nama_supplier"
                            class="form-control form-control-custom"
                            placeholder="Nama perusahaan supplier" data-max="40" data-label="Nama Supplier" autocomplete="new-password" spellcheck="false" autocorrect="off" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Nama Kontak (PIC)</label>
                        <input type="text" name="nama_kontak" id="edit_nama_kontak"
                            class="form-control form-control-custom"
                            placeholder="Nama PIC Penanggung Jawab" data-max="30" data-label="Nama Kontak" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="text" id="edit_telepon"
                            class="form-control form-control-custom"
                            placeholder="No Telepon (Hanya Angka)" data-max="13" data-label="Telepon" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="text" id="edit_email"
                            class="form-control form-control-custom"
                            placeholder="alamat@email.com" data-max="40" data-label="Email" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select form-control-custom" required>
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Alamat & Pajak -->
            <div class="tab-pane fade" id="edit-alamat" role="tabpanel" aria-labelledby="edit-alamat-tab">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nomor NPWP</label>
                        <input type="text" name="npwp" id="edit_npwp"
                            class="form-control form-control-custom"
                            placeholder="00.000.000.0-000.000" data-max="20" data-label="Nomor NPWP" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea id="edit_alamat" rows="2"
                            class="form-control form-control-custom"
                            placeholder="Nama Jalan, RT/RW, Blok..." data-max="60" data-label="Alamat Lengkap" autocomplete="new-password" spellcheck="false" autocorrect="off"></textarea>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Kota / Kabupaten</label>
                        <input type="text" id="edit_kota"
                            class="form-control form-control-custom"
                            placeholder="Kota asal" data-max="20" data-label="Kota" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Provinsi</label>
                        <input type="text" id="edit_provinsi"
                            class="form-control form-control-custom"
                            placeholder="Provinsi asal" data-max="20" data-label="Provinsi" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Kode Pos</label>
                        <input type="text" name="kode_pos" id="edit_kode_pos"
                            class="form-control form-control-custom"
                            placeholder="Kode pos (Hanya Angka)" data-max="10" data-label="Kode Pos" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                </div>
            </div>

            <!-- Tab 3: Keuangan & Bank -->
            <div class="tab-pane fade" id="edit-keuangan" role="tabpanel" aria-labelledby="edit-keuangan-tab">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Termin Pembayaran (Hari)</label>
                        <div class="input-group">
                            <input type="text" name="term_pembayaran" id="edit_term_pembayaran"
                                class="form-control form-control-custom"
                                placeholder="0 jika tunai / COD" data-max="5" data-label="Termin Pembayaran" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                            <span class="input-group-text fw-semibold">Hari</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Batas Limit Kredit</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input type="text" name="limit_kredit" id="edit_limit_kredit"
                                class="form-control form-control-custom"
                                placeholder="Limit kredit utang" data-max="20" data-label="Batas Limit Kredit" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                        </div>
                    </div>
                    
                    <!-- DYNAMIC BANK ACCOUNTS SECTION (EDIT) -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
                            <label class="form-label fw-bold mb-0 text-primary" style="font-size: 0.9rem;">
                                <i class="bi bi-credit-card-2-front me-1"></i> Daftar Rekening Bank Penerima (Bisa Multi)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 btn-add-bank-row" data-type="edit">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Rekening
                            </button>
                        </div>
                        <div class="table-responsive border rounded-3 p-1 bg-white">
                            <table class="table table-sm table-borderless align-middle mb-0" id="edit_bank_table">
                                <thead>
                                    <tr class="bg-light text-muted small border-bottom" style="font-size: 0.75rem;">
                                        <th width="28%" class="ps-2 py-2">Nama Bank</th>
                                        <th width="35%">Nomor Rekening</th>
                                        <th width="28%">Atas Nama</th>
                                        <th width="5%" class="text-center">Utama</th>
                                        <th width="4%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_bank_tbody">
                                    <!-- Rows added dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="edit_keterangan" rows="2"
                            class="form-control form-control-custom"
                            placeholder="Catatan tambahan..." data-max="30" data-label="Keterangan Tambahan" autocomplete="off"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        $('#formEditSupplier').attr('novalidate', true);

        // ===== RESET TABS ON MODAL EDIT HIDE =====
        $('#modalEditSupplier').on('hidden.bs.modal', function () {
            $('#editTabs button:first').tab('show');
            $('#formEditSupplier')[0].reset();
            $('#edit_bank_tbody').empty();
            $('#formEditSupplier').find('button[type="submit"]').prop('disabled', false).html('Simpan Perubahan');
        });

        // ===== EDIT SHOW MODAL =====
        $(document).on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            $.get('/supplier/' + id + '/edit', function (res) {
                let s = res.supplier;
                $('#edit_supplier_id').val(s.id);
                $('#edit_kode_supplier').val(s.kode_supplier);
                $('#edit_nama_supplier').val(s.nama_supplier);
                $('#edit_nama_kontak').val(s.nama_kontak);
                $('#edit_telepon').val(s.telepon);
                $('#edit_email').val(s.email);
                $('#edit_status').val(s.status);
                
                $('#edit_npwp').val(s.npwp);
                $('#edit_alamat').val(s.alamat);
                $('#edit_kota').val(s.kota);
                $('#edit_provinsi').val(s.provinsi);
                $('#edit_kode_pos').val(s.kode_pos);

                $('#edit_term_pembayaran').val(s.term_pembayaran);
                $('#edit_limit_kredit').val(s.limit_kredit);
                $('#edit_keterangan').val(s.keterangan);

                // Populate Dynamic Banks Rows
                $('#edit_bank_tbody').empty();
                if (s.banks && s.banks.length > 0) {
                    s.banks.forEach(function(b) {
                        addBankRow('edit_bank_tbody', 'edit', b);
                    });
                } else {
                    addBankRow('edit_bank_tbody', 'edit'); // default blank row
                }

                $('#modalEditSupplier').modal('show');
            });
        });

        // ===== UPDATE SUPPLIER =====
        $('#formEditSupplier').on('submit', function (e) {
            e.preventDefault();
            let id = $('#edit_supplier_id').val();

            // Client-side validation check
            let kode = $('#edit_kode_supplier').val();
            let nama = $('#edit_nama_supplier').val();
            let kontak = $('#edit_nama_kontak').val();
            let telepon = $('#edit_telepon').val();
            let email = $('#edit_email').val();
            let npwp = $('#edit_npwp').val();
            let alamat = $('#edit_alamat').val();
            let kota = $('#edit_kota').val();
            let provinsi = $('#edit_provinsi').val();
            let kode_pos = $('#edit_kode_pos').val();
            let term = $('#edit_term_pembayaran').val();
            let limit = $('#edit_limit_kredit').val();
            let keterangan = $('#edit_keterangan').val();

            let errors = [];
            if (!kode || !kode.trim()) {
                errors.push('Kode Supplier wajib diisi.');
                $('#edit-umum-tab').tab('show');
            }
            if (!nama || !nama.trim()) {
                errors.push('Nama Supplier wajib diisi.');
                $('#edit-umum-tab').tab('show');
            }
            if (kode && kode.length > 10) errors.push('Kode Supplier tidak boleh melebihi 10 karakter.');
            if (nama && nama.length > 40) errors.push('Nama Supplier tidak boleh melebihi 40 karakter.');
            if (kontak && kontak.length > 30) errors.push('Nama Kontak tidak boleh melebihi 30 karakter.');
            if (telepon && telepon.length > 13) errors.push('Telepon tidak boleh melebihi 13 digit.');
            if (telepon && !/^[0-9]*$/.test(telepon)) errors.push('Telepon hanya boleh berisi angka.');
            if (email && email.length > 40) errors.push('Email tidak boleh melebihi 40 karakter.');
            if (npwp && npwp.length > 20) errors.push('Nomor NPWP tidak boleh melebihi 20 karakter.');
            if (alamat && alamat.length > 60) errors.push('Alamat tidak boleh melebihi 60 karakter.');
            if (kota && kota.length > 20) errors.push('Kota tidak boleh melebihi 20 karakter.');
            if (provinsi && provinsi.length > 20) errors.push('Provinsi tidak boleh melebihi 20 karakter.');
            if (kode_pos && kode_pos.length > 10) errors.push('Kode Pos tidak boleh melebihi 10 digit.');
            if (kode_pos && !/^[0-9]*$/.test(kode_pos)) errors.push('Kode Pos hanya boleh berisi angka.');
            if (term && term.toString().length > 5) errors.push('Termin Pembayaran tidak boleh melebihi 5 digit.');
            if (limit && limit.toString().length > 20) errors.push('Batas Limit Kredit tidak boleh melebihi 20 digit.');
            if (keterangan && keterangan.length > 30) errors.push('Keterangan Tambahan tidak boleh melebihi 30 karakter.');

            $('#edit_bank_tbody tr.bank-row').each(function(idx) {
                let nb = $(this).find('input[name*="[nama_bank]"]').val();
                let nr = $(this).find('input[name*="[nomor_rekening]"]').val();
                let an = $(this).find('input[name*="[atas_nama_rekening]"]').val();

                if ((nb || nr || an) && (!nb || !nr || !an)) {
                    errors.push(`Baris rekening ${idx+1}: Harap lengkapi Nama Bank, Nomor Rekening, dan Atas Nama Rekening.`);
                    $('#edit-keuangan-tab').tab('show');
                }
                if (nb && nb.length > 15) errors.push(`Nama Bank (baris ${idx+1}) tidak boleh melebihi 15 karakter.`);
                if (nr && nr.length > 15) errors.push(`Nomor Rekening (baris ${idx+1}) tidak boleh melebihi 15 karakter.`);
                if (an && an.length > 40) errors.push(`Atas Nama Rekening (baris ${idx+1}) tidak boleh melebihi 40 karakter.`);
            });

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Peringatan Input!',
                    html: '<b>Harap perbaiki data berikut:</b><br>• ' + errors.join('<br>• '),
                    icon: 'error'
                });
                return false;
            }

            // Build form payload including non-named fields to prevent Chrome Autofill
            let formData = $(this).serializeArray();
            formData.push({ name: 'telepon', value: telepon });
            formData.push({ name: 'email', value: email });
            formData.push({ name: 'alamat', value: alamat });
            formData.push({ name: 'kota', value: kota });
            formData.push({ name: 'provinsi', value: provinsi });

            let saveBtn = $(this).find('button[type="submit"]');
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memperbarui...');

            $.ajax({
                url: "/supplier/" + id + "/update",
                type: 'POST',
                data: $.param(formData),
                success: function (res) {
                    saveBtn.prop('disabled', false).html('Simpan Perubahan');
                    $('#modalEditSupplier').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    if ($.fn.DataTable.isDataTable('#tableSupplier')) {
                        $('#tableSupplier').DataTable().ajax.reload(null, false);
                    } else {
                        location.reload();
                    }
                },
                error: function (err) {
                    saveBtn.prop('disabled', false).html('Simpan Perubahan');
                    let errorMsg = 'Terjadi kesalahan saat memperbarui data.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + val[0] + '<br>';
                        });
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                }
            });
        });
    });
</script>
@endpush
