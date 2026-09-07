<!-- MODAL DETAIL TRANSAKSI KAS KECIL (DIGITAL RECEIPT & JOURNAL BREAKDOWN) -->
<div class="modal fade" id="modalDetailPettyCash" tabindex="-1" aria-labelledby="modalDetailPettyCashLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 text-white px-3 px-md-4 py-3 flex-shrink-0" style="background: linear-gradient(135deg, #047857 0%, #065f46 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-white bg-opacity-20 d-flex align-items-center justify-content-center">
                        <i class="bi bi-receipt fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalDetailPettyCashLabel">Voucher Kas Kecil</h5>
                        <p class="text-white-50 small mb-0 font-monospace" id="detail_nomor_header">PC-xxxx-xxxx</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="modal-body p-3 p-md-4 bg-body-tertiary" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(100vh - 170px);">
                
                <!-- Digital Receipt Ticket Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 bg-body" id="receiptPrintArea">
                    <!-- Ticket Header Stamp -->
                    <div class="p-3 text-center border-bottom border-dashed">
                        <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold mb-1" id="detail_status_badge">
                            Disetujui
                        </div>
                        <h6 class="fw-bold mb-0 text-body-emphasis" id="detail_company_name">PT PUSAT</h6>
                        <p class="small text-muted mb-0" id="detail_entitas_badge">Kantor Pusat</p>
                        
                        <!-- Tender Link Badge (If any) -->
                        <div id="detail_tender_container" class="mt-2 d-none">
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1 font-monospace">
                                <i class="bi bi-briefcase-fill text-warning me-1"></i> <span id="detail_tender_title">-</span>
                            </span>
                        </div>
                    </div>

                    <!-- Nominal Display Big -->
                    <div class="p-4 text-center bg-body-secondary bg-opacity-50 border-bottom border-dashed">
                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1" id="detail_tipe_label">TOTAL PENGELUARAN</span>
                        <h2 class="fw-bold font-monospace mb-0 text-success" id="detail_nominal">Rp 0</h2>
                        <span class="small text-muted font-monospace d-block mt-1" id="detail_tanggal">Tanggal: -</span>
                    </div>

                    <!-- Detail Rows -->
                    <div class="p-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted small py-1" style="width: 38%;">No. Transaksi</td>
                                    <td class="fw-bold font-monospace small py-1" id="detail_nomor">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">Kategori</td>
                                    <td class="fw-semibold small py-1" id="detail_kategori">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">Pemohon / PIC</td>
                                    <td class="small py-1" id="detail_pengaju">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">Diinput Oleh</td>
                                    <td class="small py-1" id="detail_creator">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1 align-top">Catatan</td>
                                    <td class="small py-1" id="detail_keterangan">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- COA Information -->
                    <div class="p-3 border-top bg-body-secondary bg-opacity-25">
                        <div class="small fw-bold text-muted text-uppercase mb-2 font-monospace" style="font-size: 11px;">Akun Terkait (COA)</div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Akun Kas Kecil:</span>
                            <span class="small fw-bold font-monospace text-success" id="detail_coa_kas">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Lawan COA (Beban/Sumber):</span>
                            <span class="small fw-bold font-monospace text-danger" id="detail_lawan_coa">-</span>
                        </div>
                    </div>
                </div>

                <!-- JURNAL UMUM SECTION -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 bg-body">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase d-flex align-items-center gap-1">
                                <i class="bi bi-journal-bookmark text-primary"></i> Jurnal Akuntansi
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 10px;" id="detail_nomor_jurnal">
                                JV-xxxx
                            </span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Akun Rekening</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_jurnal_tbody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Memuat jurnal...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RECEIPT PHOTO (If any) -->
                <div class="card border-0 shadow-sm rounded-4 bg-body d-none" id="detail_receipt_photo_container">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase d-flex align-items-center gap-1">
                                <i class="bi bi-paperclip text-info"></i> Lampiran Nota / Struk
                            </span>
                            <a href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" id="detail_receipt_download_btn" style="font-size: 11px;">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Foto Asli
                            </a>
                        </div>
                        <div class="border rounded-3 p-1 bg-light overflow-hidden">
                            <img id="detail_receipt_img" src="#" alt="Foto Bukti Struk" class="img-fluid rounded-2" style="max-height: 250px; object-fit: contain;">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-3 px-md-4 py-3 bg-body flex-shrink-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold" onclick="printPettyCashVoucher()">
                    <i class="bi bi-printer me-1"></i> Cetak Bon
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-3 py-2 btn-sm fw-bold" id="btnEditFromDetail">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-3 py-2 btn-sm fw-bold" id="btnDeleteFromDetail">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
