{{-- MODAL TAMBAH --}}
<x-modal_form id="modalTambahTipe" formId="formTambahTipe" title="<i class='bi bi-grid me-2'></i> Tambah Tipe Barang">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Tipe Barang <span class="text-danger">*</span></label>
                <input type="text" name="nama_tipe" id="tambah_nama_tipe" class="form-control form-control-custom"
                    placeholder="Contoh: Bahan Baku, Barang Jadi" autocomplete="off" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="tambah_deskripsi" rows="3" class="form-control form-control-custom"
                    placeholder="Deskripsi singkat tipe barang (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate limit validation for Tambah
        $('#tambah_nama_tipe').on('input', function () {
            if ($(this).val().length > 25) {
                $(this).val($(this).val().substring(0, 25));
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama tipe barang tidak boleh lebih dari 25 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        $('#tambah_deskripsi').on('input', function () {
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

        // Submit form Tambah
        $('#formTambahTipe').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const namaVal = $('#tambah_nama_tipe').val() || '';
            const deskVal = $('#tambah_deskripsi').val() || '';

            if (namaVal.length > 25) {
                Swal.fire({
                    title: 'Batas Karakter Terlampaui!',
                    text: 'Nama tipe barang tidak boleh lebih dari 25 karakter.',
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
            $.ajax({
                url: "{{ route('tipe-barang.store') }}",
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: res => {
                    $('#modalTambahTipe').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableTipe !== 'undefined') {
                            tableTipe.ajax.reload(null, false);
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

        $('#modalTambahTipe').on('hidden.bs.modal', () => $('#formTambahTipe')[0].reset());
    });
</script>
@endpush
