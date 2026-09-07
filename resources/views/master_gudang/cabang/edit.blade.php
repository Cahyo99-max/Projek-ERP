{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditCabang" formId="formEditCabang" title="<i class='bi bi-pencil-square me-2'></i> Edit Cabang" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" id="edit_branch_id">
        <div class="row g-3">
            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-info-circle text-primary me-2"></i><strong>Identitas Cabang</strong>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Cabang <small class="text-muted">(opsional)</small></label>
                <input type="text" name="kode_cabang" id="edit_kode_cabang"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Otomatis" autocomplete="off" maxlength="6">
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold">Nama Cabang <span class="text-danger">*</span></label>
                <input type="text" name="nama_cabang" id="edit_nama_cabang"
                    class="form-control form-control-custom"
                    placeholder="Cabang Jakarta Selatan" autocomplete="off" required maxlength="30">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Perusahaan Induk (PT) <span class="text-danger">*</span></label>
                <select name="company_id" id="edit_company_id_select" class="form-select form-control-custom" required>
                    <option value="" disabled>-- Pilih Perusahaan (PT) Induk --</option>
                    @if(isset($companies))
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->nama_perusahaan }} ({{ $comp->kode_perusahaan }})</option>
                        @endforeach
                    @endif
                </select>
                <input type="hidden" name="nama_perusahaan" id="edit_nama_perusahaan">
            </div>

            <div class="col-12"><hr class="my-1"></div>

            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-telephone text-primary me-2"></i><strong>Informasi Kontak</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">No Telepon</label>
                <input type="text" name="phone" id="edit_phone"
                    class="form-control form-control-custom"
                    placeholder="0211234567" autocomplete="new-password" aria-autocomplete="none" maxlength="13">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Email Cabang</label>
                <input type="email" name="email" id="edit_email"
                    class="form-control form-control-custom"
                    placeholder="cabang@perusahaan.com" autocomplete="new-password" aria-autocomplete="none" maxlength="50">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat</label>
                <textarea name="address" id="edit_address" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Jl. Contoh No. 1..." autocomplete="new-password" aria-autocomplete="none" maxlength="60"></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kota</label>
                <input type="text" name="city" id="edit_city"
                    class="form-control form-control-custom"
                    placeholder="Jakarta" autocomplete="new-password" aria-autocomplete="none" maxlength="20">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Provinsi</label>
                <input type="text" name="province" id="edit_province"
                    class="form-control form-control-custom"
                    placeholder="DKI Jakarta" autocomplete="new-password" aria-autocomplete="none" maxlength="20">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Pos</label>
                <input type="text" name="postal_code" id="edit_postal_code"
                    class="form-control form-control-custom"
                    placeholder="12345" autocomplete="new-password" aria-autocomplete="none" maxlength="10">
            </div>

            <div class="col-12"><hr class="my-1"></div>

            <div class="col-12">
                <div class="section-title mb-2">
                    <i class="bi bi-person-badge text-primary me-2"></i><strong>Kepala Cabang</strong>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Kepala Cabang</label>
                <input type="text" name="manager_name" id="edit_manager_name"
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
        $(document).on('input', '#edit_kode_cabang', function () {
            this.value = this.value.toUpperCase();
        });

        // ===== BUKA MODAL EDIT =====
        $(document).on('click', '.btn-edit-branch', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/cabang/' + id + '/edit', function (res) {
                let b = res.branch;
                $('#edit_branch_id').val(b.id);
                $('#edit_kode_cabang').val(b.kode_cabang);
                $('#edit_nama_cabang').val(b.nama_cabang);
                $('#edit_company_id_select').val(b.company_id);
                $('#edit_nama_perusahaan').val(b.nama_perusahaan);
                $('#edit_phone').val(b.phone);
                $('#edit_email').val(b.email);
                $('#edit_address').val(b.address);
                $('#edit_city').val(b.city);
                $('#edit_province').val(b.province);
                $('#edit_postal_code').val(b.postal_code);
                $('#edit_manager_name').val(b.manager_name);
                $('#edit_status').val(b.status);
                $('#modalEditCabang').modal('show');
            }).fail(function() {
                Swal.fire('Gagal!', 'Data cabang tidak ditemukan.', 'error');
            });
        });

        $('#modalEditCabang').on('hidden.bs.modal', function () {
            $('#formEditCabang')[0].reset();
        });

        // ===== SIMPAN EDIT =====
        $('#formEditCabang').on('submit', function (e) {
            e.preventDefault();
            let id  = parseInt($('#edit_branch_id').val());
            if (!id) return;
            let btn = $(this).find('[type=submit]');
            btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '/cabang/' + id + '/update',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalEditCabang').modal('hide');
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
    });
</script>
@endpush
