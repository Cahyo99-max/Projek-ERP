{{-- MODAL EDIT --}}
<x-modal_form id="modalEditTipe" formId="formEditTipe" title="<i class='bi bi-pencil-square me-2'></i> Edit Tipe Barang">
    <x-slot name="slot">
        <input type="hidden" id="edit_tipe_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Tipe Barang <span class="text-danger">*</span></label>
                <input type="text" name="nama_tipe" id="edit_nama_tipe" class="form-control form-control-custom"
                    placeholder="Contoh: Bahan Baku, Barang Jadi" autocomplete="off" maxlength="100" required>
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
                <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="form-control form-control-custom"
                    placeholder="Deskripsi singkat tipe barang (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Immediate limit validation for Edit
        $('#edit_nama_tipe').on('input', function () {
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

        $('#edit_deskripsi').on('input', function () {
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

        // Trigger Edit Fetch
        $(document).on('click', '.btn-edit', function() {
            let id = parseInt($(this).data('id')); if(!id) return;
            $.get("{{ url('/tipe-barang') }}/" + id + "/edit", res => {
                let t = res.tipe;
                $('#edit_tipe_id').val(t.id);
                $('#edit_nama_tipe').val(t.nama_tipe);
                $('#edit_deskripsi').val(t.deskripsi);
                $('#edit_status').val(t.status);
                $('#modalEditTipe').modal('show');
            }).fail(err => Swal.fire({title:'Gagal!',text:err.status===403?'Akses ditolak.':'Gagal memuat data.',icon:'error'}));
        });

        $('#modalEditTipe').on('hidden.bs.modal', () => $('#formEditTipe')[0].reset());

        // Submit form Edit
        $('#formEditTipe').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const namaVal = $('#edit_nama_tipe').val() || '';
            const deskVal = $('#edit_deskripsi').val() || '';

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
            let id = parseInt($('#edit_tipe_id').val());
            $.ajax({
                url: "{{ url('/tipe-barang') }}/" + id + "/update",
                type: 'POST',
                data: {
                    nama_tipe: $('#edit_nama_tipe').val(),
                    deskripsi: $('#edit_deskripsi').val(),
                    status: $('#edit_status').val()
                },
                success: res => {
                    $('#modalEditTipe').modal('hide');
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
                    let m = 'Gagal update.';
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
    });
</script>
@endpush
