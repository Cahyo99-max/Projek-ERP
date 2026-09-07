{{-- ===== MODAL IMPORT METODE PEMBAYARAN ===== --}}
<div class="modal fade" id="modalImportMethod" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-file-earmark-arrow-up fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Import Metode Pembayaran</h5>
                        <small class="text-muted">Upload file Excel / CSV untuk menambah data massal</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImportMethod" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex gap-2 align-items-start py-3" role="alert">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Panduan Import:</strong>
                            <ul class="mb-0 ps-3 mt-1 small">
                                <li>Format kolom: <b>Kode Metode, Nama Metode, Keterangan, Status</b></li>
                                <li>Batas karakter: <b>Kode (max 5)</b>, <b>Nama (max 35)</b>, <b>Keterangan (max 30)</b></li>
                                <li>Status harus: <b>Active</b> atau <b>Inactive</b></li>
                                <li>Kode Metode tidak boleh duplikat</li>
                                <li>Format file: <b>.xlsx</b> atau <b>.csv</b></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('payment-method.import.template') }}"
                            class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-download me-1"></i> Download Template Excel
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" name="excel_file" id="import_method_file"
                            class="form-control form-control-custom"
                            accept=".xlsx,.csv,.txt" required>
                        <div class="form-text">Maksimal ukuran file: 10 MB</div>
                    </div>

                    <div id="importMethodProgress" class="d-none">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Sedang mengimpor...</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success w-100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnImportMethodSubmit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-upload me-1"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#modalImportMethod').on('hidden.bs.modal', function () {
        $('#formImportMethod')[0].reset();
        $('#importMethodProgress').addClass('d-none');
        $('#btnImportMethodSubmit').prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Import Sekarang');
    });

    $('#formImportMethod').on('submit', function (e) {
        e.preventDefault();

        let fileInput = $('#import_method_file')[0];
        if (!fileInput.files.length) {
            Swal.fire('Perhatian!', 'Pilih file terlebih dahulu.', 'warning');
            return;
        }

        let submitBtn = $('#btnImportMethodSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor...');
        $('#importMethodProgress').removeClass('d-none');

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('payment-method.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#modalImportMethod').modal('hide');
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function (err) {
                $('#importMethodProgress').addClass('d-none');
                submitBtn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Import Sekarang');

                let errorMsg = 'Gagal mengimpor data metode.';
                if (err.responseJSON) {
                    if (err.responseJSON.message) errorMsg = err.responseJSON.message;
                    if (err.status === 422 && err.responseJSON.errors) {
                        errorMsg = '<b>Validasi gagal:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + val[0] + '<br>';
                        });
                    }
                }
                Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
            }
        });
    });
</script>
@endpush
