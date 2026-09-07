{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahCabang" formId="formTambahCabang" title="<i class='bi bi-buildings me-2'></i> Tambah Cabang Baru" autocomplete="off">
    <x-slot name="slot">
        <div class="row g-3">
            {{-- SECTION: Identitas Cabang --}}
            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-info-circle text-primary me-2"></i><strong>Identitas Cabang</strong>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Cabang <small class="text-muted">(opsional)</small></label>
                <input type="text" name="kode_cabang" id="tambah_kode_cabang"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Otomatis" autocomplete="off" maxlength="6">
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold">Nama Cabang <span class="text-danger">*</span></label>
                <input type="text" name="nama_cabang" id="tambah_nama_cabang"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Cabang Jakarta Selatan" autocomplete="off" required maxlength="30">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Perusahaan Induk (PT) <span class="text-danger">*</span></label>
                <select name="company_id" id="tambah_company_id" class="form-select form-control-custom" required>
                    <option value="" disabled selected>-- Pilih Perusahaan (PT) Induk --</option>
                    @if(isset($companies))
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->nama_perusahaan }} ({{ $comp->kode_perusahaan }})</option>
                        @endforeach
                    @endif
                </select>
                <input type="hidden" name="nama_perusahaan" id="tambah_nama_perusahaan">
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- SECTION: Kontak --}}
            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-telephone text-primary me-2"></i><strong>Informasi Kontak</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">No Telepon</label>
                <input type="text" name="phone" id="tambah_phone"
                    class="form-control form-control-custom"
                    placeholder="0211234567" autocomplete="new-password" aria-autocomplete="none" maxlength="13">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Email Cabang</label>
                <input type="email" name="email" id="tambah_email"
                    class="form-control form-control-custom"
                    placeholder="cabang@perusahaan.com" autocomplete="new-password" aria-autocomplete="none" maxlength="50">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat</label>
                <textarea name="address" id="tambah_address" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan" autocomplete="new-password" aria-autocomplete="none" maxlength="60"></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kota</label>
                <input type="text" name="city" id="tambah_city"
                    class="form-control form-control-custom"
                    placeholder="Jakarta" autocomplete="new-password" aria-autocomplete="none" maxlength="20">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Provinsi</label>
                <input type="text" name="province" id="tambah_province"
                    class="form-control form-control-custom"
                    placeholder="DKI Jakarta" autocomplete="new-password" aria-autocomplete="none" maxlength="20">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Pos</label>
                <input type="text" name="postal_code" id="tambah_postal_code"
                    class="form-control form-control-custom"
                    placeholder="12345" autocomplete="new-password" aria-autocomplete="none" maxlength="10">
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- SECTION: Kepala Cabang --}}
            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-person-badge text-primary me-2"></i><strong>Kepala Cabang</strong>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Kepala Cabang</label>
                <input type="text" name="manager_name" id="tambah_manager_name"
                    class="form-control form-control-custom"
                    placeholder="Nama lengkap kepala cabang" autocomplete="new-password" aria-autocomplete="none" maxlength="40">
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Auto uppercase kode cabang
        $(document).on('input', '#tambah_kode_cabang', function () {
            this.value = this.value.toUpperCase();
        });

        // ===== TAMBAH =====
        $('#formTambahCabang').on('submit', function (e) {
            e.preventDefault();
            let btn = $(this).find('[type=submit]');
            btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '/cabang/store',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahCabang').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(() => {
                            if (typeof tableCabang !== 'undefined') {
                                tableCabang.ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                },
                error: function (err) {
                    let msg = '<b>Data tidak valid:</b><br>';
                    if (err.status === 422 && err.responseJSON?.errors) {
                        $.each(err.responseJSON.errors, (k, v) => msg += '• ' + escapeHtml(v[0]) + '<br>');
                    } else if (err.status === 429) {
                        msg = 'Terlalu banyak permintaan. Harap tunggu sebentar.';
                    } else {
                        msg = escapeHtml(err.responseJSON?.message ?? 'Terjadi kesalahan.');
                    }
                    Swal.fire({ title: 'Gagal!', html: msg, icon: 'error' });
                },
                complete: function () {
                    btn.html('Simpan Data').prop('disabled', false);
                }
            });
        });

        $('#modalTambahCabang').on('hidden.bs.modal', function () {
            $('#formTambahCabang')[0].reset();
        });
    });
</script>
@endpush
