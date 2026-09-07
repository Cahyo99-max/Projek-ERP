{{-- MODAL EDIT --}}
<x-modal_form id="modalEditWarna" formId="formEditWarna" title="<i class='bi bi-pencil-square me-2'></i> Edit Warna">
    <x-slot name="slot">
        <input type="hidden" id="edit_warna_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Warna <span class="text-danger">*</span></label>
                <input type="text" name="nama_warna" id="edit_nama_warna" class="form-control form-control-custom"
                    placeholder="Contoh: Merah, Hitam, Navy Blue" autocomplete="off" maxlength="100" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Warna Visual</label>
                <div class="input-group">
                    <span class="input-group-text p-1" style="background:transparent;">
                        <input type="color" id="edit_kode_hex"
                            class="form-control form-control-color border-0 p-0" style="height:32px;width:32px;" value="#3b82f6">
                    </span>
                    <input type="text" name="kode_hex" id="edit_kode_hex_text" class="form-control text-uppercase"
                        placeholder="#3B82F6" value="#3B82F6" readonly>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="form-control form-control-custom"
                    placeholder="Deskripsi singkat warna (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Sync color picker ↔ text input
        $('#edit_kode_hex').on('input', function() {
            $('#edit_kode_hex_text').val($(this).val().toUpperCase());
        });

        // Immediate character limit popup warnings
        $('#edit_nama_warna').on('input', function() {
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

        // Immediate character limit popup warnings
        $('#edit_deskripsi').on('input', function() {
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
            $.get("{{ url('/warna') }}/" + id + "/edit", res => {
                let w = res.warna;
                let hex = w.kode_hex || '#000000';
                $('#edit_warna_id').val(w.id);
                $('#edit_nama_warna').val(w.nama_warna);
                $('#edit_kode_hex').val(hex);
                $('#edit_kode_hex_text').val(hex.toUpperCase());
                $('#edit_deskripsi').val(w.deskripsi);
                $('#edit_status').val(w.status);
                $('#modalEditWarna').modal('show');
            }).fail(err => Swal.fire({title:'Gagal!',text:err.status===403?'Akses ditolak.':'Gagal memuat data.',icon:'error'}));
        });

        $('#modalEditWarna').on('hidden.bs.modal', function() {
            $('#formEditWarna')[0].reset();
            $('#edit_kode_hex').val('#3b82f6');
            $('#edit_kode_hex_text').val('#3B82F6');
        });

        // Submit form Edit Warna
        $('#formEditWarna').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');

            const namaVal = $('#edit_nama_warna').val() || '';
            const deskVal = $('#edit_deskripsi').val() || '';

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
            let id = parseInt($('#edit_warna_id').val());

            $.ajax({
                url: "{{ url('/warna') }}/" + id + "/update",
                type: 'POST',
                data: {
                    nama_warna: $('#edit_nama_warna').val(),
                    kode_hex: $('#edit_kode_hex_text').val(),
                    deskripsi: $('#edit_deskripsi').val(),
                    status: $('#edit_status').val()
                },
                success: res => {
                    $('#modalEditWarna').modal('hide');
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
