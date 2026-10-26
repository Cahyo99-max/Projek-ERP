<!-- Modal Lookup / Pencarian Faktur Pembelian -->
<div class="modal fade" id="modalLookupInvoice" tabindex="-1" aria-labelledby="modalLookupInvoiceLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-payment-wide modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header pay-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-search text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalLookupInvoiceLabel">Pencarian &amp; Pemilihan Faktur Pembelian</h5>
                        <div class="text-subtitle">Cari dan pilih satu atau beberapa faktur yang akan dibayar</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pay-modal-body p-3 p-md-4">
                
                <!-- Filter Bar inside Lookup -->
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="lookupInvoiceSearch" class="form-control" placeholder="Ketik No. Faktur / No. Ref / Proyek Tender / Supplier...">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <select id="lookupInvoiceSupplierFilter" class="form-select form-select-sm">
                                <option value="all">Semua Supplier</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->kode_supplier }} - {{ $sup->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 w-100" id="btnRefreshLookupInvoices">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Invoices Table in Lookup -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive" style="max-height: 480px;">
                        <table class="table table-hover align-middle mb-0" id="tableLookupInvoices" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 38px;">
                                        <input class="form-check-input" type="checkbox" id="checkAllLookupInvoices">
                                    </th>
                                    <th>No. Faktur</th>
                                    <th>No. Ref Supplier</th>
                                    <th>Supplier</th>
                                    <th>Tgl Faktur</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-end">Total Faktur</th>
                                    <th class="text-end text-success">Terbayar</th>
                                    <th class="text-end text-primary">Sisa Utang</th>
                                    <th class="text-center">Status Tempo</th>
                                </tr>
                            </thead>
                            <tbody id="lookupInvoicesTbody">
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        <span>Memuat daftar faktur pembelian...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small">Terpilih: </span>
                    <strong class="text-primary fs-6" id="lookupSelectedCount">0 Faktur</strong>
                    <span class="text-muted small ms-2" id="lookupSelectedSupplierInfo"></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm" id="btnApplyLookupInvoices" disabled>
                        <i class="bi bi-check-circle-fill me-1"></i> Gunakan Faktur Terpilih
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
