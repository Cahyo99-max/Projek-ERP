{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahJenisGudang" formId="formTambahJenisGudang" title="<i class='bi bi-grid-3x3-gap me-2'></i> Tambah Jenis Gudang" autocomplete="off">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Jenis Gudang <span class="text-danger">*</span></label>
                <input type="text" name="nama_jenis_gudang" id="tambah_nama_jenis_gudang"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Gudang Utama, Gudang Transit" autocomplete="off" maxlength="30" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Urutan Tampil</label>
                <input type="number" name="urutan" id="tambah_urutan"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2, 3" min="1" autocomplete="off">
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
                <textarea name="deskripsi" id="tambah_deskripsi" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Deskripsi singkat jenis gudang (opsional)" maxlength="30"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== TAMBAH =====
        $('#formTambahJenisGudang').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('jenis-gudang.store') }}",
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahJenisGudang').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableJenisGudang !== 'undefined') {
                            tableJenisGudang.ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                },
                error: function (err) {
                    let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + escapeHtml(val[0]) + '<br>';
                        });
                    } else if (err.status === 429) {
                        errorMsg = 'Terlalu banyak permintaan. Harap tunggu sebentar.';
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = escapeHtml(err.responseJSON.message);
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });

        $('#modalTambahJenisGudang').on('hidden.bs.modal', function () {
            $('#formTambahJenisGudang')[0].reset();
        });
    });
</script>
@endpush
