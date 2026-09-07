{{-- MODAL FILTER SPK PRODUKSI --}}
<div class="modal fade spk-modal-secondary" id="modalFilterSpk" tabindex="-1" aria-labelledby="modalFilterSpkLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-dark py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-1.5 rounded-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-funnel-fill fs-5 text-primary"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalFilterSpkLabel">Filter Data SPK Produksi</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY MODAL --}}
            <div class="modal-body spk-modal-body p-4">
                <div class="row g-3">
                    {{-- Status SPK --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-info-circle text-primary me-1"></i>Status SPK
                        </label>
                        <select id="filter_status" class="form-select spk-select rounded-3">
                            <option value="all">-- Semua Status --</option>
                            <option value="Draft">Draft</option>
                            <option value="Pending">Pending Approval</option>
                            <option value="Approved">Approved (Disetujui)</option>
                            <option value="In Progress">Dalam Proses (In Progress)</option>
                            <option value="Completed">Selesai (Completed)</option>
                            <option value="Cancelled">Dibatalkan</option>
                        </select>
                    </div>

                    {{-- Prioritas --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-flag text-primary me-1"></i>Tingkat Prioritas
                        </label>
                        <select id="filter_prioritas" class="form-select spk-select rounded-3">
                            <option value="all">-- Semua Prioritas --</option>
                            <option value="Normal">Normal</option>
                            <option value="Tinggi (Urgent)">Tinggi (Urgent)</option>
                            <option value="Rendah">Rendah</option>
                        </select>
                    </div>

                    {{-- Tipe SPK --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-diagram-2 text-primary me-1"></i>Tipe SPK Produksi
                        </label>
                        <select id="filter_tipe" class="form-select spk-select rounded-3">
                            <option value="all">-- Semua Tipe --</option>
                            <option value="Make to Stock">Make to Stock (Stok Gudang)</option>
                            <option value="Make to Order">Make to Order (Pesanan/Tender)</option>
                            <option value="Custom/Sampel">Custom / Sampel</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="modal-footer spk-modal-footer d-flex justify-content-between p-3 p-sm-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" onclick="resetFilterSpk()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="terapkanFilterSpkModal()">
                    <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>
