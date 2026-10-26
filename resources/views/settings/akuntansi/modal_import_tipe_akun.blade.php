<!-- Modal Import Master Tipe Akun -->
<div class="modal fade" id="modalImportMasterTipeAkun" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom border-light-subtle py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="bi bi-file-earmark-spreadsheet-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Import Master Tipe Akun</h5>
                        <small class="text-body-secondary">Untuk Perusahaan: <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">{{ session('active_company_name') ?? 'Perusahaan Aktif' }}</span></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImportMasterTipeAkun" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    {{-- Petunjuk Format Excel --}}
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
                            <span class="fw-bold small text-info">Petunjuk Format File Excel:</span>
                        </div>
                        <ul class="small text-body-secondary mb-0 ps-3" style="line-height: 1.6; font-size: 12px;">
                            <li><strong class="text-body">Kode Tipe</strong>: Opsional. Jika kosong atau duplikat, sistem otomatis men-generate kode unik berikutnya.</li>
                            <li><strong class="text-body">Nama Tipe Akun</strong>: <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0" style="font-size: 10px;">Wajib Diisi</span></li>
                            <li><strong class="text-body">Kategori Laporan</strong>: Isi <code class="px-1 py-0.5 rounded bg-body-secondary text-primary">Neraca</code> atau <code class="px-1 py-0.5 rounded bg-body-secondary text-primary">Laba Rugi</code>.</li>
                            <li><strong class="text-body">Saldo Normal Default</strong>: Isi <code class="px-1 py-0.5 rounded bg-body-secondary text-primary">Debit</code> atau <code class="px-1 py-0.5 rounded bg-body-secondary text-primary">Kredit</code>.</li>
                            <li><strong class="text-body">Status</strong>: Isi <code class="px-1 py-0.5 rounded bg-body-secondary text-success">Active</code> atau <code class="px-1 py-0.5 rounded bg-body-secondary text-secondary">Inactive</code>.</li>
                        </ul>
                    </div>

                    {{-- Download Template Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3" style="background: rgba(99, 102, 241, 0.06); border: 1px dashed rgba(99, 102, 241, 0.3);">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(99, 102, 241, 0.15); color: #6366f1;">
                                <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold text-body">Gunakan Format Standar</div>
                                <div class="text-body-secondary" style="font-size: 11px;">Template resmi dengan contoh data siap isi</div>
                            </div>
                        </div>
                        <a href="{{ route('master-tipe-akuns.template') }}" id="btnDownloadTemplateTipeAkun" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-flex align-items-center gap-1.5 shadow-2xs no-loader" data-no-loader="true">
                            <i class="bi bi-download"></i>
                            <span>Download Template</span>
                        </a>
                    </div>

                    {{-- File Dropzone Area --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body d-flex justify-content-between align-items-center mb-1.5">
                            <span>Pilih Berkas Excel / CSV <span class="text-danger">*</span></span>
                            <span class="text-body-secondary" style="font-size: 11px;">Maks. 10 MB (hingga 5.000 baris)</span>
                        </label>

                        <div id="importTypeDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center position-relative transition-all" style="background: rgba(var(--bs-primary-rgb, 37, 99, 235), 0.02); border-color: rgba(var(--bs-primary-rgb, 37, 99, 235), 0.3) !important; cursor: pointer;">
                            <input type="file" name="excel_file" id="import_type_excel_file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept=".xlsx,.csv,.txt" required style="z-index: 5;">
                            
                            <!-- Empty / Initial State -->
                            <div id="dropzoneEmptyState">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; background: rgba(var(--bs-primary-rgb, 37, 99, 235), 0.1); color: var(--bs-primary, #3b82f6);">
                                    <i class="bi bi-cloud-arrow-up-fill fs-3"></i>
                                </div>
                                <div class="small fw-bold text-body mb-1">
                                    Tarik & letakkan file di sini atau <span class="text-primary text-decoration-underline">Pilih Berkas</span>
                                </div>
                                <div class="text-body-secondary" style="font-size: 11.5px;">
                                    Format didukung: <strong>.xlsx</strong> (Excel) atau <strong>.csv</strong>
                                </div>
                            </div>

                            <!-- Selected State -->
                            <div id="dropzoneSelectedState" class="d-none">
                                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <div class="d-flex align-items-center gap-3 text-start overflow-hidden">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: rgba(16, 185, 129, 0.2); color: #10b981;">
                                            <i class="bi bi-file-earmark-excel-fill fs-4"></i>
                                        </div>
                                        <div class="text-truncate">
                                            <div id="selectedFileName" class="small fw-bold text-body text-truncate">file_excel.xlsx</div>
                                            <div id="selectedFileSize" class="text-body-secondary" style="font-size: 11px;">0 KB</div>
                                        </div>
                                    </div>
                                    <button type="button" id="btnResetSelectedFile" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1.5 ms-2 flex-shrink-0" title="Ganti berkas" style="z-index: 10;">
                                        <i class="bi bi-trash3-fill fs-6"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div id="importTypeProgress" class="d-none mt-3">
                        <div class="d-flex justify-content-between small text-body-secondary mb-1.5">
                            <span class="d-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm text-primary" style="width: 14px; height: 14px;" role="status"></span>
                                <span>Sedang memvalidasi & mengimpor data...</span>
                            </span>
                            <span id="importTypeProgressPercent" class="fw-semibold text-primary">Harap tunggu...</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px; background: rgba(var(--bs-primary-rgb, 37, 99, 235), 0.15);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100 rounded-pill"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-light-subtle py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm fw-semibold" id="btnSubmitImportType">
                        <i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
