{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditKategori" formId="formEditKategori" title="<i class='bi bi-pencil-square me-2'></i> Edit Kategori">
    <x-slot name="slot">
        <input type="hidden" id="edit_kategori_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="nama_kategori" id="edit_nama_kategori"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Elektronik, Pakaian Pria" autocomplete="off" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kategori Induk (Parent)</label>
                <select name="parent_id" id="edit_parent_id" class="form-select form-control-custom">
                    <option value="">-- Set sebagai Kategori Utama --</option>
                    @foreach ($parentOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['text'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Urutan Tampil</label>
                <input type="number" name="urutan" id="edit_urutan"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2" min="1" autocomplete="off">
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
                    placeholder="Deskripsi singkat kategori (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== EDIT (SHOW MODAL) =====
        $(document).on('click', '.btn-edit', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get("{{ url('/kategori-barang') }}/" + id + "/edit", function (res) {
                let k = res.kategori;
                $('#edit_kategori_id').val(k.id);
                $('#edit_nama_kategori').val(k.nama_kategori);
                $('#edit_parent_id').val(k.parent_id ? k.parent_id : '');
                $('#edit_urutan').val(k.urutan);
                $('#edit_deskripsi').val(k.deskripsi);
                $('#edit_status').val(k.status);

                // Disable the edited category option inside parent list (prevent self-reference)
                $('#edit_parent_id option').prop('disabled', false);
                $('#edit_parent_id option[value="' + k.id + '"]').prop('disabled', true);

                $('#modalEditKategori').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit kategori ini.'
                    : 'Gagal memuat data kategori.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        // Reset form on modal close
        $('#modalEditKategori').on('hidden.bs.modal', function () {
            $('#formEditKategori')[0].reset();
            $('#edit_parent_id option').prop('disabled', false);
        });

        // ===== UPDATE =====
        $('#formEditKategori').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);

            // Validasi panjang nama & deskripsi
            const namaVal = $('#edit_nama_kategori').val() || '';
            const deskripsiVal = $('#edit_deskripsi').val() || '';
            if (namaVal.length > 30 || deskripsiVal.length > 30) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Nama Kategori dan Deskripsi tidak boleh melebihi 30 karakter.',
                    icon: 'warning',
                    confirmButtonColor: '#eab308'
                });
                btn.prop('disabled', false);
                return;
            }

            let id = parseInt($('#edit_kategori_id').val());
            let data = {
                nama_kategori: namaVal,
                parent_id:     $('#edit_parent_id').val(),
                urutan:        $('#edit_urutan').val(),
                deskripsi:     $('#edit_deskripsi').val(),
                status:        $('#edit_status').val(),
            };

            $.ajax({
                url: "{{ url('/kategori-barang') }}/" + id + "/update",
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#modalEditKategori').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableKategori !== 'undefined') {
                            tableKategori.ajax.reload(null, false);
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

        // Real-time length validation
        $('#edit_nama_kategori, #edit_deskripsi').on('input', function () {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Input nama dan deskripsi dibatasi maksimal <b>30 karakter</b> untuk kerapian tampilan data.</div>',
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
    });
</script>
@endpush
