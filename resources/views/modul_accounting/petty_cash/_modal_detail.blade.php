<!-- MODAL DETAIL TRANSAKSI KAS KECIL (MODERN BENTO FINTECH SLIP) -->
<div class="modal fade" id="modalDetailPettyCash" tabindex="-1" aria-labelledby="modalDetailPettyCashLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 960px;">
        <div class="modal-content pc-modal-content border-0 rounded-4 overflow-hidden shadow-2xl">
            
            <!-- Modal Header -->
            <div class="modal-header pc-modal-header px-3 px-md-4 py-3 flex-shrink-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.35) 100%); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3);">
                        <i class="bi bi-receipt-cutoff fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold pc-text-title-adaptive mb-0" id="modalDetailPettyCashLabel" style="letter-spacing: -0.2px;">Voucher Kas Kecil</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 11px;" id="detail_status_badge">
                                Disetujui
                            </span>
                        </div>
                        <p class="pc-text-muted-adaptive small mb-0 font-monospace mt-0.5" id="detail_nomor_header">PC-xxxx-xxxx</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (Printable Area & Modern Bento Grid) -->
            <div class="modal-body pc-modal-body-canvas p-3 p-md-4" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(85vh - 120px);" id="receiptPrintArea">
                
                <div class="row g-3 g-lg-4">
                    
                    <!-- ==================== KOLOM KIRI: SLIP VOUCHER & DATA TRANSAKSI ==================== -->
                    <div class="col-12 col-lg-6 d-flex flex-column gap-3">
                        
                        <!-- 1. HERO DIGITAL VOUCHER SLIP (Fintech Slip Card) -->
                        <div class="card pc-bento-card border-0 rounded-4 overflow-hidden position-relative shadow-sm p-3 p-md-4">
                            <!-- Accent Glow Line Top -->
                            <div class="position-absolute top-0 start-0 w-100" style="height: 3px; background: linear-gradient(90deg, #10b981 0%, #0ea5e9 50%, #6366f1 100%);"></div>

                            <div class="text-center">
                                <!-- Company & Entity Info Badge -->
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-2.5 pc-bento-company-pill">
                                    <i class="bi bi-buildings-fill text-info"></i>
                                    <span class="fw-bold pc-text-title-adaptive small" id="detail_company_name">PT PUSAT</span>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 10px;" id="detail_entitas_badge">Kantor Pusat</span>
                                </div>

                                <!-- Tender Tag (If any) -->
                                <div id="detail_tender_container" class="mb-2.5 d-none">
                                    <span class="badge pc-badge-tender rounded-pill px-3 py-1 font-monospace" style="font-size: 11px;">
                                        🎯 <span id="detail_tender_title">-</span>
                                    </span>
                                </div>

                                <!-- Total Nominal Display Box -->
                                <div class="rounded-3 my-2 pc-bento-nominal-box p-3 p-md-3.5">
                                    <span class="pc-text-muted-adaptive text-uppercase fw-bold letter-spacing-1 d-block" style="font-size: 11px; letter-spacing: 1px;" id="detail_tipe_label">TOTAL PENGELUARAN</span>
                                    <h2 class="fw-bold font-monospace my-1 text-danger tracking-tight fs-2 fs-md-1" id="detail_nominal" style="font-weight: 800;">Rp 0,00</h2>
                                    <div class="pc-text-muted-adaptive font-monospace d-flex align-items-center justify-content-center gap-1.5 mt-2" style="font-size: 12px;">
                                        <i class="bi bi-calendar-event text-info"></i>
                                        <span id="detail_tanggal">Tanggal: -</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. DETAIL INFORMASI TRANSAKSI (Bento Meta Card) -->
                        <div class="card pc-bento-card border-0 rounded-4 shadow-sm p-3 p-md-4">
                            <div class="small fw-bold text-uppercase mb-3 font-monospace d-flex align-items-center gap-2 pc-text-muted-adaptive" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="bi bi-info-circle-fill text-info"></i>
                                <span>Informasi &amp; Catatan Pengeluaran</span>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center rounded-3 pc-bento-info-row" style="padding: 9px 14px;">
                                    <span class="pc-text-muted-adaptive small">No. Transaksi</span>
                                    <span class="fw-bold font-monospace text-info small" id="detail_nomor">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center rounded-3 pc-bento-info-row" style="padding: 9px 14px;">
                                    <span class="pc-text-muted-adaptive small">Kategori</span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" id="detail_kategori">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center rounded-3 pc-bento-info-row" style="padding: 9px 14px;">
                                    <span class="pc-text-muted-adaptive small">Pemohon / PIC</span>
                                    <span class="pc-text-title-adaptive fw-semibold small" id="detail_pengaju">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center rounded-3 pc-bento-info-row" style="padding: 9px 14px;">
                                    <span class="pc-text-muted-adaptive small">Diinput Oleh</span>
                                    <span class="pc-text-muted-adaptive small font-monospace" id="detail_creator">-</span>
                                </div>
                                <div class="rounded-3 mt-1 pc-bento-info-row" style="padding: 12px 14px;">
                                    <span class="pc-text-muted-adaptive small d-block mb-1.5 fw-semibold" style="font-size: 12px;">Catatan / Keperluan:</span>
                                    <div class="small p-2.5 rounded-2 pc-bento-note-box" style="line-height: 1.55;" id="detail_keterangan">
                                        -
                                    </div>
                                    <!-- Alasan Pembatalan (Tampil jika Dibatalkan) -->
                                    <div id="detail_container_alasan_batal" class="d-none mt-2.5 pt-2" style="border-top: 1px dashed rgba(225, 29, 72, 0.3);">
                                        <span class="text-danger small d-block mb-1 fw-semibold" style="font-size: 11.5px;">
                                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Alasan Pembatalan Transaksi:
                                        </span>
                                        <div class="small p-2.5 rounded-2 fw-medium text-danger-emphasis" style="background: rgba(225, 29, 72, 0.12); border: 1px solid rgba(225, 29, 72, 0.28); line-height: 1.55;" id="detail_alasan_batal">
                                            -
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ==================== KOLOM KANAN: AKUN PEMBUKUAN, JURNAL & BUKTI ==================== -->
                    <div class="col-12 col-lg-6 d-flex flex-column gap-3">
                        
                        <!-- 3. ALIRAN AKUN PEMBUKUAN (COA FLOW CARD) -->
                        <div class="card pc-bento-card border-0 rounded-4 shadow-sm p-3 p-md-4">
                            <div class="small fw-bold text-uppercase mb-3 font-monospace d-flex align-items-center gap-2 pc-text-muted-adaptive" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="bi bi-diagram-3-fill text-info"></i>
                                <span>Aliran Akun Pembukuan (COA)</span>
                            </div>

                            <!-- Akun Kas Kecil (Sumber Kas) -->
                            <div class="rounded-3 mb-2.5 p-2.5 p-md-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small pc-text-muted-adaptive" style="font-size: 11px;">Akun Kas Kecil (Sumber Dana):</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 10px;">Kas / Kredit</span>
                                </div>
                                <div class="small fw-bold font-monospace text-success fs-6" id="detail_coa_kas">-</div>
                            </div>

                            <!-- Visual Flow Indicator -->
                            <div class="text-center my-1">
                                <span class="badge rounded-pill px-2.5 py-1 pc-bento-company-pill" style="font-size: 10px;">
                                    <i class="bi bi-arrow-down text-info me-1"></i>Dialokasikan ke Pos Beban
                                </span>
                            </div>

                            <!-- Lawan COA (Akun Biaya / Beban) -->
                            <div class="rounded-3 p-2.5 p-md-3" style="background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.25);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small pc-text-muted-adaptive" style="font-size: 11px;">Lawan COA (Pos Pengeluaran):</span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 10px;">Beban / Debit</span>
                                </div>
                                <div class="small fw-bold font-monospace text-danger fs-6" id="detail_lawan_coa">-</div>
                            </div>
                        </div>

                        <!-- 4. JURNAL AKUNTANSI OTOMATIS -->
                        <div class="card pc-bento-card border-0 rounded-4 shadow-sm p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="small fw-bold text-uppercase font-monospace d-flex align-items-center gap-2 pc-text-muted-adaptive" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="bi bi-journal-bookmark-fill text-primary"></i>
                                    <span>Jurnal Akuntansi Otomatis</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 10px;" id="detail_nomor_jurnal">
                                    JV-xxxx
                                </span>
                            </div>
                            
                            <div class="table-responsive rounded-3 overflow-hidden pc-table-jurnal-wrapper">
                                <table class="table table-sm table-hover align-middle mb-0 pc-table-jurnal" style="font-size: 12px;">
                                    <thead>
                                        <tr>
                                            <th class="py-2 fw-semibold" style="padding-left: 14px !important;">Akun Rekening</th>
                                            <th class="text-end py-2 fw-semibold">Debit</th>
                                            <th class="text-end py-2 fw-semibold" style="padding-right: 14px !important;">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_jurnal_tbody">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Memuat jurnal...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Jurnal Pembalik (Reversal Entry) jika Dibatalkan -->
                            <div class="d-none mt-3 pt-3 border-top pc-border-adaptive" id="detail_reversal_jurnal_container">
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <div class="small fw-bold text-uppercase font-monospace d-flex align-items-center gap-2 text-danger" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="bi bi-arrow-counterclockwise text-danger"></i>
                                        <span>Jurnal Pembalik (Reversal Entry)</span>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 10px;" id="detail_nomor_reversal_jurnal">
                                        JV-REV-xxxx
                                    </span>
                                </div>
                                <div class="table-responsive rounded-3 overflow-hidden" style="border: 1px solid rgba(244, 63, 94, 0.25);">
                                    <table class="table table-sm table-hover align-middle mb-0 pc-table-reversal-jurnal" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th class="py-2 text-danger-emphasis fw-semibold" style="padding-left: 14px !important;">Akun Rekening (Pembalikan)</th>
                                                <th class="text-end py-2 text-danger-emphasis fw-semibold">Debit</th>
                                                <th class="text-end py-2 text-danger-emphasis fw-semibold" style="padding-right: 14px !important;">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detail_reversal_jurnal_tbody">
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-2">Memuat jurnal pembalik...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 5. LAMPIRAN NOTA / BUKTI STRUK (JIKA ADA) -->
                        <div class="card pc-bento-card border-0 rounded-4 shadow-sm p-3 p-md-4 d-none" id="detail_receipt_photo_container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="small fw-bold text-uppercase font-monospace d-flex align-items-center gap-2 pc-text-muted-adaptive" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="bi bi-paperclip text-info"></i>
                                    <span>Lampiran Nota / Struk</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1.5" id="detail_receipt_download_btn" style="font-size: 11px;">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                    <span>Perbesar Bukti</span>
                                </button>
                            </div>
                            <div class="rounded-3 text-center position-relative overflow-hidden pc-bento-info-row p-2.5">
                                <img id="detail_receipt_img" src="#" alt="Foto Bukti Struk" class="img-fluid rounded-2 shadow-sm" style="max-height: 180px; object-fit: contain; cursor: zoom-in;" title="Klik untuk memperbesar bukti">
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer pc-modal-footer px-3 px-md-4 py-3 flex-shrink-0 d-flex justify-content-between">
                @can('cetak-pdf-petty-cash')
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5" onclick="printPettyCashVoucher()" title="Cetak slip voucher">
                    <i class="bi bi-printer-fill text-info"></i>
                    <span>Cetak Bon</span>
                </button>
                @else
                <div></div>
                @endcan
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5" data-bs-dismiss="modal">Tutup</button>
                    @can('batalkan-petty-cash')
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-1.5" id="btnBatalFromDetail" title="Batalkan transaksi dan kembalikan saldo kas">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Batalkan Transaksi</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
