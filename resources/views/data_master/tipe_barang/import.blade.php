<!-- Modal Import Tipe Barang -->
@can('tambah-tipe-barang')
<div class="modal fade" id="modalImportTipe" tabindex="-1" aria-labelledby="modalImportTipeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom px-4 py-3" style="background: var(--navbar-bg) !important;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalImportTipeLabel">
                    <i class="bi bi-file-earmark-arrow-up text-primary fs-4"></i>
                    <span>Import Data Tipe Barang</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImportTipe" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-4">
                    <!-- Instruction Card -->
                    <div class="card border-0 mb-4 p-3 rounded-3" style="background: rgba(37, 99, 235, 0.08) !important; border: 1.5px solid rgba(37, 99, 235, 0.15) !important;">
                        <div class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-info-circle text-primary fs-5 mt-0.5"></i>
                            <div>
                                <h6 class="fw-bold mb-1 fs-7">Panduan Pengisian Data Tipe Barang</h6>
                                <p class="text-muted mb-3 fs-8" style="line-height: 1.4;">
                                    Silakan unduh template Excel terlebih dahulu. Kolom Nama Tipe Barang (maksimal 25 karakter), Deskripsi (maksimal 30 karakter), dan Status wajib diisi dengan nilai <strong>Active</strong> atau <strong>Inactive</strong>.
                                </p>
                                <a href="{{ route('tipe-barang.import.template') }}" class="btn btn-sm btn-success rounded-pill d-inline-flex align-items-center gap-2 px-3 fw-semibold no-loader btn-download-template-tipe">
                                    <i class="bi bi-cloud-arrow-down-fill"></i>
                                    <span>Download Template Excel</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- File Input -->
                    <div class="mb-3">
                        <label for="excel_file_tipe" class="form-label fw-bold fs-7">Pilih File Excel (.xlsx)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--bs-tertiary-bg) !important; border-color: var(--bs-border-color) !important; border-radius: 8px 0 0 8px;">
                                <i class="bi bi-file-earmark-spreadsheet text-muted"></i>
                            </span>
                            <input type="file" name="excel_file" id="excel_file_tipe" class="form-control border-start-0" accept=".xlsx" required style="border-radius: 0 8px 8px 0; font-size: 0.85rem; background: transparent; border-color: var(--bs-border-color) !important; color: inherit;">
                        </div>
                        <div class="form-text text-muted fs-8">Hanya menerima format file `.xlsx` dari template resmi.</div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3" style="background: var(--navbar-bg) !important; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal" style="font-size: 0.8rem; font-weight: 600;">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2" id="btnSubmitImportTipe" style="font-size: 0.8rem; font-weight: 600;">
                        <i class="bi bi-check-lg"></i>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    $(document).ready(function () {
        // Handle Form Submit Import Tipe Barang
        $('#formImportTipe').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btnSubmitImportTipe');
            const originalText = btn.find('span').text();

            btn.prop('disabled', true).find('span').text('Mengimport...');
            btn.find('i').attr('class', 'spinner-border spinner-border-sm me-1').text('');

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('tipe-barang.import') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalImportTipe').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableTipe !== 'undefined') {
                            tableTipe.ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                },
                error: function (err) {
                    let errorMsg = 'Terjadi kesalahan saat mengimpor data.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    } else if (err.responseJSON && err.responseJSON.errors && err.responseJSON.errors.excel_file) {
                        errorMsg = err.responseJSON.errors.excel_file[0];
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
                            errorMsg = rawMsg;
                        }
                    }
                    Swal.fire({
                        title: 'Gagal Import!',
                        html: '<div class="alert alert-danger text-start fs-7 p-3 mb-0" style="border-radius: 8px;">'
                            + '<i class="bi bi-exclamation-triangle-fill me-2"></i>'
                            + escapeHtml(errorMsg) + '</div>',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function () {
                    btn.prop('disabled', false).find('span').text(originalText);
                    btn.find('i').attr('class', 'bi bi-check-lg');
                    $('#formImportTipe')[0].reset();
                }
            });
        });

        // Template download alert
        $(document).on('click', '.btn-download-template-tipe', function () {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            Swal.fire({
                title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Template Berhasil Diunduh!</span>',
                html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Silakan isi data Anda sesuai dengan format kolom yang telah ditentukan di dalam file template Excel tersebut.</div>',
                icon: 'success',
                iconColor: '#10b981',
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#f8fafc' : '#1e293b',
                confirmButtonText: 'Baik, Saya Mengerti',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                }
            });
        });
    });
</script>
@endpush
