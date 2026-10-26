<!-- Modal Detail Faktur Pajak -->
<div class="modal fade" id="modalDetailFakturPajak" tabindex="-1" aria-labelledby="modalDetailFakturPajakLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%;">
        <div class="modal-content border-0 shadow-lg rounded-4 bg-body">
            <div class="modal-header border-bottom py-3 px-4 bg-body-secondary">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);">
                        <i class="bi bi-file-earmark-text-fill fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="detail_modal_title">Detail Faktur Pajak</h5>
                        <p class="text-body-secondary small mb-0 font-monospace" id="detail_modal_subtitle">Rincian e-Faktur PPN DJP &amp; Faktur Penjualan Tertaut</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div id="fp_detail_loading" class="text-center py-5">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="text-body-secondary small mt-2">Memuat rincian faktur pajak...</p>
                </div>

                <div id="fp_detail_content" class="d-none">
                    <!-- Info Header -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <div class="card border h-100 bg-body-tertiary rounded-3 p-3">
                                <h6 class="fw-bold text-body-emphasis mb-3 border-bottom pb-2">Informasi Wajib Pajak Penerima</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-body-secondary small" style="width: 140px;">Nama Customer:</td>
                                        <td class="fw-semibold text-body-emphasis" id="detail_nama_customer">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-body-secondary small">NPWP / NIK:</td>
                                        <td class="font-monospace fw-bold text-success" id="detail_npwp_customer">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-body-secondary small">Alamat Lengkap:</td>
                                        <td class="text-body" id="detail_alamat_customer">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card border h-100 bg-body-tertiary rounded-3 p-3">
                                <h6 class="fw-bold text-body-emphasis mb-3 border-bottom pb-2">Atribut Faktur Pajak</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-body-secondary small" style="width: 130px;">No. Faktur Pajak:</td>
                                        <td class="font-monospace fw-bold text-body-emphasis" id="detail_nomor_fp">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-body-secondary small">Tanggal Pajak:</td>
                                        <td class="font-monospace" id="detail_tanggal_fp">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-body-secondary small">Masa / Tahun:</td>
                                        <td class="font-monospace" id="detail_masa_tahun">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-body-secondary small">Tipe / Status:</td>
                                        <td>
                                            <span class="badge" id="detail_status_badge">-</span>
                                            <span class="badge bg-secondary-subtle text-secondary border font-monospace ms-1" id="detail_gabungan_badge">-</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Linked Invoices Badge Box -->
                    <div class="card border bg-body-tertiary rounded-3 p-3 mb-4">
                        <h6 class="fw-bold text-body-emphasis mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-success"></i> Faktur Penjualan Tertaut (Commercial Invoices)
                        </h6>
                        <div id="detail_linked_invoices_container" class="d-flex flex-wrap gap-2 pt-1">
                            <!-- Injected via JS -->
                        </div>
                    </div>

                    <!-- Items Detail Table -->
                    <div class="card border bg-body-tertiary rounded-3 p-3 mb-4">
                        <h6 class="fw-bold text-body-emphasis mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-box-seam text-warning"></i> Rincian Objek Faktur Pajak (OF)
                        </h6>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="bg-body-secondary sticky-top small text-uppercase fw-bold text-body-secondary">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">No</th>
                                        <th>Nama Barang / Jasa Kena Pajak</th>
                                        <th style="width: 90px;" class="text-center">Qty</th>
                                        <th style="width: 140px;" class="text-end">Harga Satuan</th>
                                        <th style="width: 110px;" class="text-end">Diskon</th>
                                        <th style="width: 140px;" class="text-end">DPP</th>
                                        <th style="width: 130px;" class="text-end">PPN</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_tbody_items">
                                    <!-- Injected via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Financial Total Summary -->
                    <div class="row g-3 justify-content-end">
                        <div class="col-md-5">
                            <div class="card border-0 bg-body-secondary rounded-4 p-3.5 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold text-body-secondary">Total DPP:</span>
                                    <span class="fw-bold font-monospace" id="detail_total_dpp">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold text-body-secondary">Total PPN:</span>
                                    <span class="fw-bold text-success font-monospace" id="detail_total_ppn">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <span class="fw-bold text-body-emphasis">Grand Total DPP + PPN:</span>
                                    <span class="fs-5 fw-bold text-primary font-monospace" id="detail_grand_total">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-3 px-4 bg-body-secondary d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" id="btn_detail_export_csv" class="btn btn-outline-success rounded-pill px-3.5 fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-filetype-csv"></i> Download CSV e-Faktur
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
