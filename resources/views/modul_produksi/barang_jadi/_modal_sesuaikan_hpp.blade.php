{{-- MODAL SESUAIKAN HPP PENERIMAAN BARANG JADI - PREMIUM DESIGN --}}
<div class="modal fade" id="modalSesuaikanHpp" tabindex="-1" aria-labelledby="modalSesuaikanHppTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered pbj-dialog">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">
            
            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative pbj-header-gradient" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center pbj-header-icon-box">
                        <i class="bi bi-calculator-fill fs-5 text-info"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalSesuaikanHppTitle" style="letter-spacing: -0.2px;">
                                Sesuaikan Nilai HPP SPK
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 pbj-header-badge" style="background: rgba(255, 255, 255, 0.2);">COST ADJUSTMENT</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;">Sesuaikan biaya Tenaga Kerja (Gaji) & Overhead Aktual produksi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-4">
                <form id="formSesuaikanHpp" autocomplete="off">
                    @csrf
                    <input type="hidden" id="adj_spk_id" name="spk_id" value="">
                    <input type="hidden" id="adj_raw_tk" name="biaya_tenaga_kerja" value="0">
                    <input type="hidden" id="adj_raw_overhead" name="biaya_overhead" value="0">

                    {{-- SPK INFO HEADER CARD --}}
                    <div class="pbj-panel-card p-3 mb-3.5 rounded-3 bg-body-tertiary">
                        <div class="row g-2 font-monospace" style="font-size: 12.5px;">
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 10px;">NOMOR SPK:</span>
                                <span id="adj_info_nomor_spk" class="fw-bold text-dark">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 10px;">TARGET QUANTITY:</span>
                                <span id="adj_info_target_qty" class="fw-bold text-primary">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- BIAYA BAHAN BAKU (READ-ONLY) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold spk-text-main mb-1">
                            Biaya Bahan Baku (Aktual Keluar Gudang)
                        </label>
                        <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                            <span class="input-group-text bg-body-tertiary font-monospace text-muted" style="width: 42px; justify-content: center;">Rp</span>
                            <input type="text" id="adj_cost_bahan" class="form-control spk-input spk-readonly text-end fw-bold text-dark font-monospace" readonly value="0">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                            <i class="bi bi-info-circle me-1"></i>Otomatis terisi dari mutasi Pengeluaran Bahan Baku SPK.
                        </small>
                    </div>

                    {{-- BIAYA TENAGA KERJA / GAJI (EDITABLE) --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold spk-text-main mb-1">
                            Biaya Tenaga Kerja / Gaji Aktual <span class="text-danger">*</span>
                        </label>
                        <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                            <span class="input-group-text bg-body-tertiary font-monospace text-muted" style="width: 42px; justify-content: center;">Rp</span>
                            <input type="text" inputmode="numeric" id="adj_cost_tk" class="form-control spk-input text-end fw-bold text-primary font-monospace" value="0" required oninput="onAdjCostInput(this, 'adj_raw_tk')" placeholder="0">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                            Input nominal pengeluaran gaji / upah operator produksi untuk SPK ini.
                        </small>
                    </div>

                    {{-- BIAYA OVERHEAD PABRIK (EDITABLE) --}}
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold spk-text-main mb-1">
                            Biaya Overhead Pabrik Aktual <span class="text-danger">*</span>
                        </label>
                        <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                            <span class="input-group-text bg-body-tertiary font-monospace text-muted" style="width: 42px; justify-content: center;">Rp</span>
                            <input type="text" inputmode="numeric" id="adj_cost_overhead" class="form-control spk-input text-end fw-bold text-primary font-monospace" value="0" required oninput="onAdjCostInput(this, 'adj_raw_overhead')" placeholder="0">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                            Input biaya operasional (listrik, air, penyusutan mesin, dll) untuk SPK ini.
                        </small>
                    </div>

                    {{-- REKAP SUBTOTAL BIAYA PRODUKSI & CARD HPP SATUAN (PREMIUM HIGH-CONTRAST CARD) --}}
                    <div class="rounded-3 p-3.5 border shadow-sm" style="background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(5,150,105,0.04) 100%); border-color: rgba(16,185,129,0.3) !important;">
                        
                        {{-- SUBTOTAL BIAYA PRODUKSI ROW --}}
                        <div class="d-flex align-items-center justify-content-between pb-2.5 mb-2.5" style="border-bottom: 1px dashed rgba(16,185,129,0.3) !important;">
                            <div>
                                <span class="text-muted fw-bold font-monospace d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="bi bi-wallet2 text-emerald me-1"></i>Subtotal Biaya Produksi SPK
                                </span>
                                <small class="text-muted" style="font-size: 10px;">(Bahan Baku + Gaji + Overhead)</small>
                            </div>
                            <div class="text-end">
                                <span class="fs-6 fw-bold text-dark font-monospace" id="adj_display_subtotal_biaya">Rp 0,00</span>
                            </div>
                        </div>

                        {{-- HPP PER UNIT ROW (PROMINENT RESULT) --}}
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-extrabold m-0 d-flex align-items-center gap-1.5" style="font-size: 13.5px; color: #047857;">
                                    <i class="bi bi-box-seam-fill"></i>
                                    <span>HPP Produk (Per Unit)</span>
                                </h6>
                                <small class="text-muted font-monospace" style="font-size: 10.5px;">
                                    Pembagi: <b id="adj_display_qty_pembagi" class="text-primary">0 Pcs</b>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-extrabold font-monospace text-emerald-vibrant" id="adj_display_hpp_unit" style="color: #059669;">Rp 0,00</span>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer pbj-modal-footer px-4 py-3 border-0">
                <div class="d-flex align-items-center justify-content-end gap-2.5 w-100">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btn_save_adj_hpp" class="btn btn-sm btn-emerald-cinematic rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.35) !important;" onclick="submitHppAdjustmentForm()">
                        <i class="bi bi-check-circle-fill fs-6"></i>
                        <span>Simpan Penyesuaian</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
