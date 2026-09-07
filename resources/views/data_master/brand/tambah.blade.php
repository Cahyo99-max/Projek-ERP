{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahBrand" formId="formTambahBrand" title="<i class='bi bi-award me-2'></i> Tambah Brand">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Brand <span class="text-danger">*</span></label>
                <input type="text" name="nama_brand" id="tambah_nama_brand"
                    class="form-control form-control-custom"
                    placeholder="Contoh: SAMSUNG, TOYOTA" autocomplete="off" required>
                <div class="form-text">Maks. 20 karakter. Harus unik.</div>
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
                    placeholder="Deskripsi singkat brand (opsional)"></textarea>
                <div class="form-text">Maks. 30 karakter.</div>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Real-time length validation for nama_brand
        $('#tambah_nama_brand').on('input', function () {
            if ($(this).val().length > 20) {
                $(this).val($(this).val().substring(0, 20));
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Input Nama Brand dibatasi maksimal <b>20 karakter</b>.</div>',
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

        // Real-time length validation for deskripsi
        $('#tambah_deskripsi').on('input', function () {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Input Deskripsi dibatasi maksimal <b>30 karakter</b>.</div>',
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

        // ===== TAMBAH =====
        $('#formTambahBrand').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('brand.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahBrand').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableBrand !== 'undefined') {
                            tableBrand.ajax.reload(null, false);
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

        // Reset form saat modal ditutup
        $('#modalTambahBrand').on('hidden.bs.modal', function () {
            $('#formTambahBrand')[0].reset();
        });
    });
</script>
@endpush
