{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditJenisGudang" formId="formEditJenisGudang" title="<i class='bi bi-pencil-square me-2'></i> Edit Jenis Gudang" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" id="edit_jg_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Jenis Gudang <span class="text-danger">*</span></label>
                <input type="text" name="nama_jenis_gudang" id="edit_nama_jenis_gudang"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Gudang Utama, Gudang Transit" autocomplete="off" maxlength="30" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Urutan Tampil</label>
                <input type="number" name="urutan" id="edit_urutan"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2, 3" min="1" autocomplete="off">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Deskripsi singkat jenis gudang (opsional)" maxlength="30"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== EDIT — SHOW MODAL =====
        $(document).on('click', '.btn-edit', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get("{{ url('/jenis-gudang') }}/" + id + "/edit", function (res) {
                let j = res.jenisGudang;
                $('#edit_jg_id').val(j.id);
                $('#edit_nama_jenis_gudang').val(j.nama_jenis_gudang);
                $('#edit_urutan').val(j.urutan);
                $('#edit_deskripsi').val(j.deskripsi);
                $('#edit_status').val(j.status);
                $('#modalEditJenisGudang').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data jenis gudang.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditJenisGudang').on('hidden.bs.modal', function () {
            $('#formEditJenisGudang')[0].reset();
        });

        // ===== UPDATE =====
        $('#formEditJenisGudang').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);
            let id = parseInt($('#edit_jg_id').val());

            $.ajax({
                url: "{{ url('/jenis-gudang') }}/" + id + "/update",
                type: 'POST',
                data: {
                    nama_jenis_gudang: $('#edit_nama_jenis_gudang').val(),
                    urutan:            $('#edit_urutan').val(),
                    deskripsi:         $('#edit_deskripsi').val(),
                    status:            $('#edit_status').val(),
                },
                success: function (res) {
                    $('#modalEditJenisGudang').modal('hide');
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
                    let errorMsg = 'Terjadi kesalahan saat mengupdate data.';
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
    });
</script>
@endpush
