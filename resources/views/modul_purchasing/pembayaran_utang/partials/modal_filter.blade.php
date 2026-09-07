<!-- Modal Filter Pembayaran Utang -->
<div class="modal fade" id="modalFilterPayments" tabindex="-1" aria-labelledby="modalFilterPaymentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Header -->
            <div class="modal-header pay-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-funnel-fill text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalFilterPaymentsLabel">Filter Data Pembayaran Utang</h5>
                        <div class="text-subtitle">Saring daftar pembayaran berdasarkan kriteria</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body pay-modal-body p-3 p-md-4">
                <div class="row g-3">
                    
                    <!-- Filter Supplier -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Supplier</label>
                        <select id="filterSupplierModal" class="form-select form-select-sm">
                            <option value="all">-- Semua Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->kode_supplier }} - {{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kas / Bank -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Akun Kas / Bank</label>
                        <select id="filterKasBankModal" class="form-select form-select-sm">
                            <option value="all">-- Semua Kas / Bank --</option>
                            @foreach($kasBanks as $kb)
                                <option value="{{ $kb->id }}">{{ $kb->nama_akun }} ({{ $kb->tipe }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Status Pembayaran</label>
                        <select id="filterStatusModal" class="form-select form-select-sm">
                            <option value="all">-- Semua Status --</option>
                            <option value="Draft">Draft</option>
                            <option value="Diajukan">Diajukan (Pending Approval)</option>
                            <option value="Ditolak">Ditolak (Rejected)</option>
                            <option value="Posted">Diposting (Approved / Posted)</option>
                            <option value="Cancelled">Dibatalkan (Void)</option>
                        </select>
                    </div>

                    <!-- Filter Rentang Tanggal -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Rentang Tanggal Pembayaran</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="text-muted small mb-1" style="font-size: 0.72rem;">Dari Tanggal:</label>
                                <input type="date" id="filterStartDateModal" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="text-muted small mb-1" style="font-size: 0.72rem;">Sampai Tanggal:</label>
                                <input type="date" id="filterEndDateModal" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-top px-3 px-md-4 py-3 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" id="btnResetFilterModal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btnApplyFilterModal">
                        <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
