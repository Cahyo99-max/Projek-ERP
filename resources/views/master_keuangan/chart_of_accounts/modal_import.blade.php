{{-- ===== MODAL IMPORT COA ===== --}}
<div class="modal fade" id="modalImportCoa" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-file-earmark-arrow-up fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Import Akun COA</h5>
                        <small class="text-muted">Upload file Excel / CSV untuk menambah data massal</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImportCoa" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex gap-2 align-items-start py-3" role="alert">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Panduan Import:</strong>
                            <ul class="mb-0 ps-3 mt-1 small">
                                <li>Format kolom: <b>Kode Akun, Nama Akun, Tipe Akun, Saldo Normal, Akun Induk (Parent), Kategori, Status, Deskripsi</b></li>
                                <li>Bisa langsung menggunakan file hasil <b>Export Excel</b> dari PT lain tanpa perlu diedit ulang!</li>
                                <li>Sistem otomatis menyusun pohon hierarki <b>Akun Induk (Header)</b> dan <b>Anak Akun (Detail)</b>.</li>
                                <li>Format file yang didukung: <b>.xlsx</b> atau <b>.csv</b></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('chart-of-accounts.import.template') }}"
                            class="btn btn-outline-success btn-sm rounded-pill fw-semibold no-loader d-inline-flex align-items-center gap-1.5 px-3 py-1.5" id="btnDownloadTemplateCoa">
                            <i class="bi bi-file-earmark-arrow-down-fill fs-6"></i>
                            <span>Download Template Excel</span>
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="excel_file" id="excel_file_coa" class="form-control form-control-custom"
                            accept=".xlsx, .csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4" id="btnSubmitImportCoa">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#modalImportCoa').on('hidden.bs.modal', function () {
        $('#formImportCoa')[0].reset();
    });

    $('#formImportCoa').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let submitBtn = $('#btnSubmitImportCoa');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor...');

        $.ajax({
            url: "{{ route('chart-of-accounts.import') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                $('#modalImportCoa').modal('hide');
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function (err) {
                submitBtn.prop('disabled', false).text('Upload & Import');
                let errorMsg = 'Gagal mengimpor data.';
                if (err.status === 422 && err.responseJSON && err.responseJSON.message) {
                    errorMsg = err.responseJSON.message;
                } else if (err.status === 403) {
                    errorMsg = 'Akses ditolak. Anda tidak memiliki izin.';
                }
                Swal.fire({ title: 'Gagal Import!', html: errorMsg, icon: 'error' });
            }
        });
    });
</script>
@endpush
