{{-- MODAL TAMBAH --}}
<x-modal_form id="modalTambahWarna" formId="formTambahWarna" title="<i class='bi bi-palette me-2'></i> Tambah Warna">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Warna <span class="text-danger">*</span></label>
                <input type="text" name="nama_warna" id="tambah_nama_warna" class="form-control form-control-custom"
                    placeholder="Contoh: Merah, Hitam, Navy Blue" autocomplete="off" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Warna Visual</label>
                <div class="input-group">
                    <span class="input-group-text p-1" style="background:transparent;">
                        <input type="color" name="kode_hex" id="tambah_kode_hex"
                            class="form-control form-control-color border-0 p-0" style="height:32px;width:32px;" value="#3b82f6">
                    </span>
                    <input type="text" id="tambah_kode_hex_text" class="form-control text-uppercase"
                        placeholder="#3B82F6" value="#3B82F6" readonly>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="tambah_deskripsi" rows="3" class="form-control form-control-custom"
                    placeholder="Deskripsi singkat warna (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Sync color picker ↔ text input
        $('#tambah_kode_hex').on('input', function() {
            $('#tambah_kode_hex_text').val($(this).val().toUpperCase());
        });

        // Immediate character limit popup warnings
        $('#tambah_nama_warna').on('input', function() {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama warna tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#tambah_deskripsi').on('input', function() {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Deskripsi tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        // Submit form Tambah Warna
        $('#formTambahWarna').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const namaVal = $('#tambah_nama_warna').val() || '';
            const deskVal = $('#tambah_deskripsi').val() || '';

            if (namaVal.length > 30) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama warna tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            if (deskVal.length > 30) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Deskripsi tidak boleh lebih dari 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            btn.prop('disabled', true);

            let fd = new FormData(this);
            fd.set('kode_hex', $('#tambah_kode_hex_text').val());

            $.ajax({
                url: "{{ route('warna.store') }}",
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: res => {
                    $('#modalTambahWarna').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableWarna !== 'undefined') {
                            tableWarna.ajax.reload(null, false);
                        }
                    });
                },
                error: err => {
                    let m = 'Gagal menyimpan.';
                    if (err.status === 422 && err.responseJSON?.errors) {
                        m = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, (k, v) => m += '• ' + escapeHtml(v[0]) + '<br>');
                    } else if (err.responseJSON?.message) {
                        m = escapeHtml(err.responseJSON.message);
                    }
                    Swal.fire({ title: 'Gagal!', html: m, icon: 'error' });
                },
                complete: () => btn.prop('disabled', false)
            });
        });

        $('#modalTambahWarna').on('hidden.bs.modal', function() {
            $('#formTambahWarna')[0].reset();
            $('#tambah_kode_hex').val('#3b82f6');
            $('#tambah_kode_hex_text').val('#3B82F6');
        });
    });
</script>
@endpush
