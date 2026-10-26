<!-- Modal Filter Data Faktur Penjualan -->
<div class="modal fade" id="modalFilterInvoice" tabindex="-1" aria-labelledby="modalFilterInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 bg-body-secondary border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-funnel-fill fs-6"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalFilterInvoiceLabel">Filter Faktur Penjualan</h6>
                        <small class="text-body-secondary" style="font-size: 0.75rem;">Saring data berdasarkan tanggal, customer, dan status tagihan</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 d-flex flex-column gap-3.5">
                <form id="formFilterInvoice" onsubmit="return false;">
                    
                    <!-- Rentang Tanggal Faktur -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="bi bi-calendar3 text-success"></i>
                            <span>Rentang Tanggal Faktur</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" class="form-control form-control-sm rounded-end-3" id="filter_start_date" placeholder="Dari Tanggal">
                                </div>
                                <div class="form-text small" style="font-size: 0.7rem;">Dari Tanggal</div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control form-control-sm rounded-end-3" id="filter_end_date" placeholder="Sampai Tanggal">
                                </div>
                                <div class="form-text small" style="font-size: 0.7rem;">Sampai Tanggal</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="bi bi-person-fill text-success"></i>
                            <span>Customer</span>
                        </label>
                        <select class="form-select rounded-3" id="filter_customer_id" style="width: 100%;">
                            <option value="">-- Semua Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_customer }} ({{ $c->kode_customer }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Faktur -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="bi bi-tag-fill text-success"></i>
                            <span>Status Faktur</span>
                        </label>
                        <select class="form-select rounded-3" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="Draft">📝 Draft</option>
                            <option value="Menunggu Persetujuan">⏳ Menunggu Persetujuan</option>
                            <option value="Unpaid">🔵 Disetujui (Belum Bayar / Unpaid)</option>
                            <option value="Partial">🟡 Pembayaran Sebagian (Partial)</option>
                            <option value="Paid">🟢 Lunas (Paid)</option>
                            <option value="Rejected">🔴 Ditolak (Rejected)</option>
                            <option value="Cancelled">❌ Dibatalkan (Cancelled)</option>
                        </select>
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-secondary border-top py-2.5 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5" id="btn_reset_filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Filter</span>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 py-1.5" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn_apply_filter">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
