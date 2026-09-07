{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahSupplier" formId="formTambahSupplier" title="<i class='bi bi-people-fill me-2'></i> Tambah Supplier / Vendor" autocomplete="off">
    <x-slot name="slot">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="tambahTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tambah-umum-tab" data-bs-toggle="tab" data-bs-target="#tambah-umum" type="button" role="tab" aria-controls="tambah-umum" aria-selected="true">
                    <i class="bi bi-info-circle me-1.5"></i> Informasi Umum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tambah-alamat-tab" data-bs-toggle="tab" data-bs-target="#tambah-alamat" type="button" role="tab" aria-controls="tambah-alamat" aria-selected="false">
                    <i class="bi bi-geo-alt me-1.5"></i> Alamat & Pajak
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tambah-keuangan-tab" data-bs-toggle="tab" data-bs-target="#tambah-keuangan" type="button" role="tab" aria-controls="tambah-keuangan" aria-selected="false">
                    <i class="bi bi-wallet2 me-1.5"></i> Keuangan & Bank
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="tambahTabsContent">
            <!-- Tab 1: Informasi Umum -->
            <div class="tab-pane fade show active" id="tambah-umum" role="tabpanel" aria-labelledby="tambah-umum-tab">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Kode Supplier</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold text-primary" id="btnAutoGenerateKode" style="font-size: 0.78rem;" title="Generate Kode Otomatis">
                                <i class="bi bi-magic me-1"></i> Auto-Generate
                            </button>
                        </div>
                        <input type="text" name="kode_supplier" id="tambah_kode_supplier"
                            class="form-control form-control-custom font-monospace"
                            placeholder="SPL-XXXX (Otomatis jika kosong)" data-max="10" data-label="Kode Supplier" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="nama_supplier" id="tambah_nama_supplier"
                            class="form-control form-control-custom"
                            placeholder="Contoh: PT. Sumber Makmur" data-max="40" data-label="Nama Supplier" autocomplete="new-password" spellcheck="false" autocorrect="off" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Nama Kontak (PIC)</label>
                        <input type="text" name="nama_kontak" id="tambah_nama_kontak"
                            class="form-control form-control-custom"
                            placeholder="Nama PIC Penanggung Jawab" data-max="30" data-label="Nama Kontak" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="text" id="tambah_telepon"
                            class="form-control form-control-custom"
                            placeholder="No Telepon (Hanya Angka)" data-max="13" data-label="Telepon" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="text" id="tambah_email"
                            class="form-control form-control-custom"
                            placeholder="alamat@email.com" data-max="40" data-label="Email" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Status Keaktifan <span class="text-danger">*</span></label>
                        <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Alamat & Pajak -->
            <div class="tab-pane fade" id="tambah-alamat" role="tabpanel" aria-labelledby="tambah-alamat-tab">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nomor NPWP</label>
                        <input type="text" name="npwp" id="tambah_npwp"
                            class="form-control form-control-custom"
                            placeholder="00.000.000.0-000.000" data-max="20" data-label="Nomor NPWP" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea id="tambah_alamat" rows="2"
                            class="form-control form-control-custom"
                            placeholder="Nama Jalan, RT/RW, Blok, Kecamatan..." data-max="60" data-label="Alamat Lengkap" autocomplete="new-password" spellcheck="false" autocorrect="off"></textarea>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Kota / Kabupaten</label>
                        <input type="text" id="tambah_kota"
                            class="form-control form-control-custom"
                            placeholder="Contoh: Jakarta Barat" data-max="20" data-label="Kota" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Provinsi</label>
                        <input type="text" id="tambah_provinsi"
                            class="form-control form-control-custom"
                            placeholder="Contoh: DKI Jakarta" data-max="20" data-label="Provinsi" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Kode Pos</label>
                        <input type="text" name="kode_pos" id="tambah_kode_pos"
                            class="form-control form-control-custom"
                            placeholder="12345 (Hanya Angka)" data-max="10" data-label="Kode Pos" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </div>
                </div>
            </div>

            <!-- Tab 3: Keuangan & Bank -->
            <div class="tab-pane fade" id="tambah-keuangan" role="tabpanel" aria-labelledby="tambah-keuangan-tab">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Termin Pembayaran (Hari)</label>
                        <div class="input-group">
                            <input type="text" name="term_pembayaran" id="tambah_term_pembayaran"
                                class="form-control form-control-custom"
                                placeholder="0 jika tunai / COD" data-max="5" data-label="Termin Pembayaran" data-numeric="true" value="0" autocomplete="new-password" spellcheck="false" autocorrect="off">
                            <span class="input-group-text fw-semibold">Hari</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Batas Limit Kredit</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input type="text" name="limit_kredit" id="tambah_limit_kredit"
                                class="form-control form-control-custom"
                                placeholder="Batas utang maksimal" data-max="20" data-label="Batas Limit Kredit" data-numeric="true" value="0" autocomplete="new-password" spellcheck="false" autocorrect="off">
                        </div>
                    </div>
                    
                    <!-- DYNAMIC BANK ACCOUNTS SECTION -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
                            <label class="form-label fw-bold mb-0 text-primary" style="font-size: 0.9rem;">
                                <i class="bi bi-credit-card-2-front me-1"></i> Daftar Rekening Bank Penerima (Bisa Multi)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 btn-add-bank-row" data-type="tambah">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Rekening
                            </button>
                        </div>
                        <div class="table-responsive border rounded-3 p-1 bg-white">
                            <table class="table table-sm table-borderless align-middle mb-0" id="tambah_bank_table">
                                <thead>
                                    <tr class="bg-light text-muted small border-bottom" style="font-size: 0.75rem;">
                                        <th width="28%" class="ps-2 py-2">Nama Bank</th>
                                        <th width="35%">Nomor Rekening</th>
                                        <th width="28%">Atas Nama</th>
                                        <th width="5%" class="text-center">Utama</th>
                                        <th width="4%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tambah_bank_tbody">
                                    <!-- Rows added dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="tambah_keterangan" rows="2"
                            class="form-control form-control-custom"
                            placeholder="Keterangan lain/catatan penting tentang supplier..." data-max="30" data-label="Keterangan Tambahan" autocomplete="off"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        $('#formTambahSupplier').attr('novalidate', true);

        function fetchNextSupplierCode() {
            $.get("{{ route('supplier.generate-code') }}", function (res) {
                if (res && res.code) {
                    $('#tambah_kode_supplier').val(res.code);
                }
            });
        }

        // ===== RESET TABS & REKENING ON MODAL TAMBAH =====
        $('#modalTambahSupplier').on('show.bs.modal', function () {
            $('#tambah_bank_tbody').empty();
            addBankRow('tambah_bank_tbody', 'tambah'); // default empty row
            if (!$('#tambah_kode_supplier').val().trim()) {
                fetchNextSupplierCode();
            }
        });

        $(document).on('click', '#btnAutoGenerateKode', function () {
            fetchNextSupplierCode();
        });

        $(document).on('blur', '#tambah_kode_supplier', function () {
            if (!$(this).val().trim()) {
                fetchNextSupplierCode();
            }
        });

        $('#modalTambahSupplier').on('hidden.bs.modal', function () {
            $('#tambahTabs button:first').tab('show');
            $('#formTambahSupplier')[0].reset();
            $('#tambah_bank_tbody').empty();
            $('#formTambahSupplier').find('button[type="submit"]').prop('disabled', false).html('Simpan');
        });

        // ===== STORE SUPPLIER =====
        $('#formTambahSupplier').on('submit', function (e) {
            e.preventDefault();
            
            // Client-side validation check
            let kode = $('#tambah_kode_supplier').val();
            let nama = $('#tambah_nama_supplier').val();
            let kontak = $('#tambah_nama_kontak').val();
            let telepon = $('#tambah_telepon').val();
            let email = $('#tambah_email').val();
            let npwp = $('#tambah_npwp').val();
            let alamat = $('#tambah_alamat').val();
            let kota = $('#tambah_kota').val();
            let provinsi = $('#tambah_provinsi').val();
            let kode_pos = $('#tambah_kode_pos').val();
            let term = $('#tambah_term_pembayaran').val();
            let limit = $('#tambah_limit_kredit').val();
            let keterangan = $('#tambah_keterangan').val();

            let errors = [];
            if (!nama || !nama.trim()) {
                errors.push('Nama Supplier wajib diisi.');
                $('#tambah-umum-tab').tab('show');
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

            $('#tambah_bank_tbody tr.bank-row').each(function(idx) {
                let nb = $(this).find('input[name*="[nama_bank]"]').val();
                let nr = $(this).find('input[name*="[nomor_rekening]"]').val();
                let an = $(this).find('input[name*="[atas_nama_rekening]"]').val();
                
                // If any of the bank fields in a row is filled, all 3 should be filled
                if ((nb || nr || an) && (!nb || !nr || !an)) {
                    errors.push(`Baris rekening ${idx+1}: Harap lengkapi Nama Bank, Nomor Rekening, dan Atas Nama Rekening.`);
                    $('#tambah-keuangan-tab').tab('show');
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
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('supplier.store') }}",
                type: 'POST',
                data: $.param(formData),
                success: function (res) {
                    saveBtn.prop('disabled', false).html('Simpan');
                    $('#modalTambahSupplier').modal('hide');
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
                    saveBtn.prop('disabled', false).html('Simpan');
                    let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
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
