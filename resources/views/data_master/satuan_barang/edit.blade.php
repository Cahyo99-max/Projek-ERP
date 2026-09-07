{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditSatuan" formId="formEditSatuan" title="<i class='bi bi-pencil-square me-2'></i> Edit Satuan">
    <x-slot name="slot">
        <input type="hidden" id="edit_satuan_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Singkatan <span class="text-danger">*</span></label>
                <input type="text" name="singkatan" id="edit_singkatan"
                    class="form-control form-control-custom text-uppercase"
                    placeholder="Contoh: PCS, KG" autocomplete="off" required>
                <div class="form-text">Maks. 4 karakter. Harus unik.</div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                <input type="text" name="nama_satuan" id="edit_nama_satuan"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Pieces, Kilogram" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Deskripsi singkat satuan (opsional)"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Auto uppercase singkatan input
        $(document).on('input', '#edit_singkatan', function () {
            this.value = this.value.toUpperCase();
        });

        // Real-time length validation
        $('#edit_singkatan').on('input', function () {
            if ($(this).val().length > 4) {
                $(this).val($(this).val().substring(0, 4));
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Input singkatan dibatasi maksimal <b>4 karakter</b>.</div>',
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#1e293b',
                    confirmButtonText: 'Saya Mengerti',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-warning rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                    }
                });
            }
        });

        $('#edit_nama_satuan, #edit_deskripsi').on('input', function () {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Input nama satuan dan deskripsi dibatasi maksimal <b>30 karakter</b>.</div>',
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#1e293b',
                    confirmButtonText: 'Saya Mengerti',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-warning rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                    }
                });
            }
        });

        // ===== EDIT — SHOW MODAL =====
        $(document).on('click', '.btn-edit', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get("{{ url('/satuan') }}/" + id + "/edit", function (res) {
                let s = res.satuan;
                $('#edit_satuan_id').val(s.id);
                $('#edit_singkatan').val(s.singkatan);
                $('#edit_nama_satuan').val(s.nama_satuan);
                $('#edit_deskripsi').val(s.deskripsi);
                $('#edit_status').val(s.status);
                $('#modalEditSatuan').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit satuan ini.'
                    : 'Gagal memuat data satuan.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        // Reset form saat modal ditutup
        $('#modalEditSatuan').on('hidden.bs.modal', function () {
            $('#formEditSatuan')[0].reset();
        });

        // ===== UPDATE =====
        $('#formEditSatuan').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);
            let id = parseInt($('#edit_satuan_id').val());
            let data = {
                singkatan:   $('#edit_singkatan').val(),
                nama_satuan: $('#edit_nama_satuan').val(),
                deskripsi:   $('#edit_deskripsi').val(),
                status:      $('#edit_status').val(),
            };

            $.ajax({
                url: "{{ url('/satuan') }}/" + id + "/update",
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#modalEditSatuan').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableSatuan !== 'undefined') {
                            tableSatuan.ajax.reload(null, false);
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
                    } else {
                        let rawMsg = '';
                        if (err.responseJSON && err.responseJSON.message) {
                            rawMsg = err.responseJSON.message;
                        } else if (err.responseText) {
                            rawMsg = err.responseText;
                        }
                        
                        if (rawMsg.indexOf('SQLSTATE[23000]') !== -1 || rawMsg.indexOf('Duplicate entry') !== -1) {
                            errorMsg = 'Data ini sudah pernah disimpan di database.';
                        } else if (rawMsg) {
                            errorMsg = escapeHtml(rawMsg.substring(0, 200));
                        }
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });
    });
</script>
@endpush
