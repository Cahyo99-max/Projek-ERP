{{-- ===== MODAL IMPORT SUPPLIER ===== --}}
<div class="modal fade" id="modalImportSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-bottom py-3.5 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-15 text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="bi bi-file-earmark-excel-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Import Data Supplier / Vendor</h5>
                        <span class="text-secondary small">Unggah data supplier dalam jumlah banyak via Excel</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Step 1: Download Template -->
                <div class="card-detail-section mb-4 p-3.5 border rounded-3" style="background: rgba(16, 124, 65, 0.05);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 36px; height: 36px; min-width: 36px;">1</span>
                            <div>
                                <h6 class="fw-bold mb-0 text-success">Langkah 1: Unduh Format Template Excel</h6>
                                <p class="text-secondary small mb-0">Gunakan format Excel resmi agar kolom terpetakan dengan presisi.</p>
                            </div>
                        </div>
                        <a href="{{ route('supplier.download-template') }}" id="btnDownloadTemplate" class="btn btn-success btn-sm rounded-pill px-3.5 py-2 fw-semibold no-loader shadow-sm">
                            <i class="bi bi-download me-1.5"></i> Download Template (.xlsx)
                        </a>
                    </div>
                </div>

                <!-- Step 2: Form Upload File -->
                <form id="formImportSupplier" enctype="multipart/form-data" novalidate>
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 36px; height: 36px; min-width: 36px;">2</span>
                            <label class="form-label fw-bold mb-0">Langkah 2: Pilih File Excel / CSV <span class="text-danger">*</span></label>
                        </div>
                        
                        <div class="p-3.5 border rounded-3 card-detail-section">
                            <input type="file" name="file" id="file_excel" class="form-control form-control-custom" accept=".xlsx, .xls, .csv" required>
                            <div class="d-flex align-items-center gap-2 mt-2 text-secondary small">
                                <i class="bi bi-info-circle text-primary"></i>
                                <span>Format didukung: <span class="badge bg-secondary bg-opacity-20 text-body font-monospace me-1">.XLSX</span><span class="badge bg-secondary bg-opacity-20 text-body font-monospace me-1">.XLS</span><span class="badge bg-secondary bg-opacity-20 text-body font-monospace">.CSV</span> (Maksimal 10 MB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines Alert -->
                    <div class="alert alert-secondary bg-secondary bg-opacity-10 border border-secondary border-opacity-20 rounded-3 p-3.5 mb-0" style="font-size: 0.85rem;">
                        <h6 class="fw-bold text-body mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-5"></i> Catatan & Ketentuan Import Data:
                        </h6>
                        <ul class="mb-0 ps-3.5 text-secondary" style="line-height: 1.6;">
                            <li>Kolom <b>Nama Supplier</b> wajib diisi (Maksimal 40 karakter).</li>
                            <li><b>Kode Supplier</b> opsional (Maksimal 10 karakter). Jika kosong, sistem otomatis membuatkan kode unik.</li>
                            <li><b>Telepon</b> max 13 digit angka & <b>Kode Pos</b> max 10 digit angka.</li>
                            <li><b>Email</b> max 40 karakter & <b>NPWP</b> max 20 karakter.</li>
                            <li>Jika ada kesalahan baris saat import, sistem akan menampilkan rincian error secara mendalam.</li>
                        </ul>
                    </div>

                    <div class="modal-footer border-0 pb-0 pt-4 px-0 justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm">
                            <i class="bi bi-upload me-1.5"></i> Mulai Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== DOWNLOAD TEMPLATE WITH DIRECT LINK & ALERT FEEDBACK =====
        $('#btnDownloadTemplate').on('click', function() {
            Swal.fire({
                title: 'Berhasil Mengunduh!',
                text: 'Template Excel supplier sedang diunduh.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // ===== SUBMIT IMPORT FILE WITH DETAILED ERROR BREAKDOWN =====
        $('#formImportSupplier').on('submit', function(e) {
            e.preventDefault();

            let fileInput = $('#file_excel')[0].files[0];
            if (!fileInput) {
                Swal.fire({
                    title: 'File Belum Dipilih!',
                    text: 'Harap pilih file Excel / CSV terlebih dahulu.',
                    icon: 'warning'
                });
                return false;
            }

            let formData = new FormData(this);
            let btnSubmit = $(this).find('button[type="submit"]');
            btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses Import...');

            $.ajax({
                url: "{{ route('supplier.import') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modalImportSupplier').modal('hide');
                    $('#formImportSupplier')[0].reset();
                    btnSubmit.prop('disabled', false).html('<i class="bi bi-upload me-1.5"></i> Mulai Import Data');

                    Swal.fire({
                        title: 'Import Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(err) {
                    btnSubmit.prop('disabled', false).html('<i class="bi bi-upload me-1.5"></i> Mulai Import Data');
                    
                    let errorList = [];
                    if (err.status === 422 && err.responseJSON) {
                        if (err.responseJSON.errors) {
                            if (Array.isArray(err.responseJSON.errors)) {
                                errorList = err.responseJSON.errors;
                            } else if (typeof err.responseJSON.errors === 'object') {
                                $.each(err.responseJSON.errors, function(k, v) {
                                    errorList.push(v[0]);
                                });
                            }
                        } else if (err.responseJSON.message) {
                            errorList.push(err.responseJSON.message);
                        }
                    }

                    if (errorList.length > 0) {
                        let htmlError = '<div class="text-start mt-2 p-2 bg-body-tertiary rounded border" style="max-height: 250px; overflow-y: auto;">' +
                                        '<span class="fw-bold">Rincian Kesalahan Baris Excel:</span>' +
                                        '<ul class="text-danger ps-3 mb-0 small mt-1" style="font-size: 0.825rem;">';
                        errorList.forEach(function(eMsg) {
                            htmlError += `<li class="mb-1">${escapeHtml(eMsg)}</li>`;
                        });
                        htmlError += '</ul></div>';

                        Swal.fire({
                            title: 'Gagal Mengimpor Data!',
                            html: htmlError,
                            icon: 'error',
                            confirmButtonText: 'Tutup & Perbaiki File'
                        });
                    } else {
                        let msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan sistem saat memproses import.';
                        Swal.fire({
                            title: 'Gagal Import!',
                            text: msg,
                            icon: 'error'
                        });
                    }
                }
            });
        });
    });
</script>
@endpush
