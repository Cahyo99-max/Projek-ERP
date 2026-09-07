{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahKategori" formId="formTambahKategori" title="<i class='bi bi-tags me-2'></i> Tambah Kategori">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="nama_kategori" id="tambah_nama_kategori"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Elektronik, Pakaian Pria" autocomplete="off" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kategori Induk (Parent)</label>
                <select name="parent_id" id="tambah_parent_id" class="form-select form-control-custom">
                    <option value="">-- Set sebagai Kategori Utama --</option>
                    @foreach ($parentOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['text'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Urutan Tampil</label>
                <input type="number" name="urutan" id="tambah_urutan"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2" min="1" autocomplete="off">
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
                    placeholder="Deskripsi singkat kategori (opsional)" maxlength="255"></textarea>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Handle Form Submit
        $('#formTambahKategori').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);

            // Validasi panjang nama & deskripsi
            const namaVal = $('#tambah_nama_kategori').val() || '';
            const deskripsiVal = $('#tambah_deskripsi').val() || '';
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

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('kategori-barang.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahKategori').modal('hide');
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
                    let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
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

        // Reset form on modal close
        $('#modalTambahKategori').on('hidden.bs.modal', function () {
            $('#formTambahKategori')[0].reset();
        });

        // Real-time length validation
        $('#tambah_nama_kategori, #tambah_deskripsi').on('input', function () {
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
