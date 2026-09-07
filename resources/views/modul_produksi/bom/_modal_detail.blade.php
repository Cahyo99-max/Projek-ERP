{{-- MODAL DETAIL RINCIAN RESEP BOM PRODUKSI GARMEN --}}
<div class="modal fade bom-modal-primary" id="modalDetailBom" tabindex="-1" aria-labelledby="modalDetailBomLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bom-modal-content border-0 shadow-lg">

            {{-- ══ HEADER MODAL ══ --}}
            <div class="modal-header bom-modal-header-dark">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-seam fs-4 text-info"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="detKodeBom">BOM/YYYY/MM/00000</h5>
                        <small class="text-white-50" id="detNamaBom">Detail Resep Produksi Garmen</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- ══ BODY MODAL ══ --}}
            <div class="modal-body bom-modal-body p-0">

                {{-- TAB NAVIGATION --}}
                <ul class="nav nav-tabs bom-detail-tabs border-0 px-4 pt-3" id="bomDetailTabs" role="tablist" style="gap: 4px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="tab-resep-btn" data-bs-toggle="tab" data-bs-target="#tab-resep" type="button" role="tab">
                            <i class="bi bi-clipboard-data me-1"></i> Resep BOM
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="tab-kalkulator-btn" data-bs-toggle="tab" data-bs-target="#tab-kalkulator" type="button" role="tab">
                            <i class="bi bi-calculator me-1"></i> Kalkulator Produksi
                            <span class="badge bg-warning text-dark ms-1 rounded-pill" id="badgeSimQty" style="font-size: 10px; display:none;">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- ╔══════════════════════════════════════════╗ --}}
                    {{-- ║   TAB 1 — RESEP BOM (detail komposisi)   ║ --}}
                    {{-- ╚══════════════════════════════════════════╝ --}}
                    <div class="tab-pane fade show active p-4" id="tab-resep" role="tabpanel">

                        {{-- INFO RINGKASAN BOM --}}
                        <div class="row g-3 mb-4 p-3 bg-light-theme rounded-3 border">
                            <div class="col-12 col-md-3">
                                <small class="text-theme-muted d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Produk Hasil (Barang Jadi)</small>
                                <span class="fw-bold fs-6 text-primary" id="detBarangJadi">-</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <small class="text-theme-muted d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Jumlah Target Hasil (Per Resep)</small>
                                <span class="fw-bold text-theme-main fs-6" id="detJumlahHasil">-</span>
                            </div>
                            <div class="col-12 col-md-2">
                                <small class="text-theme-muted d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Tipe Produksi</small>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill" id="detTipeBom">Garmen</span>
                            </div>
                            <div class="col-12 col-md-2">
                                <small class="text-theme-muted d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Status BOM</small>
                                <span id="detStatusBadge">-</span>
                            </div>
                            <div class="col-12 col-md-2">
                                <small class="text-theme-muted d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Tender / Project</small>
                                <span id="detTenderName" class="fw-semibold text-theme-main small">-</span>
                            </div>
                        </div>

                        {{-- TABEL KOMPOSISI BAHAN BAKU --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-theme-main mb-0">
                                <i class="bi bi-list-check me-2 text-info"></i>Komposisi Bahan Baku &amp; Aksesoris Jahit
                            </h6>
                        </div>
                        <div class="table-responsive rounded-3 border mb-4">
                            <table class="table bom-table-material align-middle mb-0" style="font-size:0.88rem;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:36px;">NO</th>
                                        <th style="width:110px;">KODE</th>
                                        <th>NAMA BAHAN BAKU / AKSESORIS</th>
                                        <th class="text-center" style="width:100px;">QTY NETTO</th>
                                        <th class="text-center" style="width:90px;">WASTE (%)</th>
                                        <th class="text-center" style="width:80px;">SATUAN</th>
                                        <th class="text-end" style="width:140px;">HPP / SATUAN</th>
                                        <th class="text-end" style="width:150px;">SUBTOTAL (RESEP)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDetailItemsBom">
                                    {{-- via AJAX --}}
                                </tbody>
                                <tfoot class="fw-bold bg-light-theme">
                                    <tr>
                                        <td colspan="7" class="text-end text-theme-muted">SUBTOTAL BAHAN BAKU:</td>
                                        <td class="text-end text-primary font-monospace" id="detSubtotalBahan">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end text-theme-muted">BIAYA TENAGA KERJA (CMT):</td>
                                        <td class="text-end text-theme-main font-monospace" id="detBiayaTk">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end text-theme-muted">BIAYA OVERHEAD PRODUKSI:</td>
                                        <td class="text-end text-theme-main font-monospace" id="detBiayaOh">Rp 0</td>
                                    </tr>
                                    <tr class="table-active border-top border-2">
                                        <td colspan="7" class="text-end text-success fs-6">TOTAL HPP PRODUKSI (PER RESEP):</td>
                                        <td class="text-end text-success font-monospace fs-5" id="detTotalHpp">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- CATATAN & HPP PER UNIT --}}
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">Catatan Spesifikasi</label>
                                <p class="text-theme-main bg-light-theme p-3 rounded-3 border mb-0 small" id="detKeterangan" style="min-height: 52px;">-</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="bom-hpp-summary-card text-end h-100 d-flex flex-column justify-content-center">
                                    <small class="text-uppercase fw-bold text-info d-block" style="font-size: 10px;">Estimasi HPP Per Unit / Satuan</small>
                                    <span class="fs-4 fw-bold text-success font-monospace mt-1" id="detHppPerUnit">Rp 0 / Pcs</span>
                                    <small class="text-theme-muted mt-1" style="font-size: 10px;">
                                        <i class="bi bi-calculator me-1"></i>
                                        Mau hitung untuk banyak unit?
                                        <a href="#" class="text-warning fw-bold text-decoration-none" onclick="pindahKeKalkulator(); return false;">Buka Kalkulator →</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ╔══════════════════════════════════════════════════════╗ --}}
                    {{-- ║   TAB 2 — KALKULATOR & PREVIEW KEBUTUHAN PRODUKSI   ║ --}}
                    {{-- ╚══════════════════════════════════════════════════════╝ --}}
                    <div class="tab-pane fade p-4" id="tab-kalkulator" role="tabpanel">

                        {{-- INPUT SECTION --}}
                        <div class="rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #1c1917 0%, #292524 60%, #1c1917 100%); border: 1px solid #d97706;">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-5">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px; height:44px; background: rgba(245,158,11,.2); border: 1px solid #d97706;">
                                            <i class="bi bi-calculator-fill text-warning fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white" style="font-size:15px;">Kalkulator Biaya Produksi</div>
                                            <small class="text-white-50" style="font-size:11px;">Input jumlah produksi → lihat rincian kebutuhan &amp; biaya</small>
                                        </div>
                                    </div>
                                    <label class="fw-semibold text-warning small mb-2 d-block">
                                        <i class="bi bi-boxes me-1"></i> Rencana Jumlah Produksi
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <button type="button" class="btn btn-outline-warning px-3" onclick="adjustSimQty(-10)" title="-10">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <input type="number" id="simJumlahProduksi" class="form-control text-center fw-bold fs-4"
                                               min="1" step="1" placeholder="100"
                                               style="border-color:#d97706; color:#d97706; background:#1c1917; border-left:none; border-right:none;"
                                               oninput="hitungSimulasiProduksi()">
                                        <button type="button" class="btn btn-outline-warning px-3" onclick="adjustSimQty(10)" title="+10">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <span class="input-group-text fw-bold" id="simSatuanLabel" style="background:#292524; border-color:#d97706; color:#fbbf24;">Unit</span>
                                    </div>
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setSimQty(10)">10</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setSimQty(50)">50</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setSimQty(100)">100</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setSimQty(500)">500</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" onclick="setSimQty(1000)">1.000</button>
                                    </div>
                                    <small class="text-white-50 mt-2 d-block" style="font-size:10px;">
                                        <i class="bi bi-info-circle me-1"></i>
                                        1 resep BOM = <strong class="text-white" id="simResepHasil">1 Pcs</strong>
                                    </small>
                                </div>

                                {{-- KPI CARDS --}}
                                <div class="col-12 col-md-7">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="rounded-3 p-3 text-center" style="background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.3);">
                                                <div style="font-size:10px; color:#fbbf24; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">
                                                    <i class="bi bi-boxes me-1"></i>Total Produksi
                                                </div>
                                                <div class="fw-bold text-white font-monospace" style="font-size: 1.6rem; letter-spacing:-1px;" id="kpiSimQty">-</div>
                                                <div style="font-size:10px; color:rgba(255,255,255,.4);" id="kpiSimSatuan">unit</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="rounded-3 p-3 text-center" style="background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3);">
                                                <div style="font-size:10px; color:#34d399; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">
                                                    <i class="bi bi-tag me-1"></i>HPP Per Unit
                                                </div>
                                                <div class="fw-bold text-white font-monospace" style="font-size:1rem; margin-top:4px;" id="kpiSimHppUnit">-</div>
                                                <div style="font-size:10px; color:rgba(255,255,255,.4);">per satuan</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, rgba(245,158,11,.2), rgba(239,68,68,.1)); border: 1px solid #d97706;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <div style="font-size:10px; color:#fbbf24; font-weight:700; text-transform:uppercase;">
                                                            <i class="bi bi-cash-stack me-1"></i>ESTIMASI TOTAL BIAYA PRODUKSI
                                                        </div>
                                                        <div style="font-size:10px; color:rgba(255,255,255,.4); margin-top:2px;" id="kpiSimDesc">Masukkan jumlah produksi</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-warning font-monospace" style="font-size:1.5rem; letter-spacing:-1px;" id="kpiSimGrandTotal">Rp 0</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- HASIL KALKULASI (muncul setelah input) --}}
                        <div id="previewKebutuhanSection" class="d-none">

                            {{-- HEADER PREVIEW --}}
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-theme-main mb-0">
                                    <i class="bi bi-card-list me-2 text-warning"></i>
                                    Preview Kebutuhan Material
                                    <span class="badge bg-warning text-dark ms-2 rounded-pill" id="previewQtyLabel">untuk 0 unit</span>
                                </h6>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-success rounded-pill" style="font-size:11px;" id="previewJumlahItem">0 Item</span>
                                </div>
                            </div>

                            {{-- TABEL RINCIAN KEBUTUHAN MATERIAL --}}
                            <div class="table-responsive rounded-3 border mb-4" style="border-color: #d97706 !important;">
                                <table class="table align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead style="background: linear-gradient(135deg, #1c1917, #292524);">
                                        <tr>
                                            <th class="text-center text-white" style="width:36px; border:none; padding: 10px 8px;">NO</th>
                                            <th class="text-white" style="width:105px; border:none; padding:10px 8px;">KODE</th>
                                            <th class="text-white" style="border:none; padding:10px 8px;">NAMA BAHAN BAKU / AKSESORIS</th>
                                            <th class="text-center text-white" style="width:110px; border:none; padding:10px 8px;">QTY / UNIT</th>
                                            <th class="text-center text-warning" style="width:130px; border:none; padding:10px 8px;">TOTAL QTY</th>
                                            <th class="text-center text-white" style="width:80px; border:none; padding:10px 8px;">SATUAN</th>
                                            <th class="text-end text-white" style="width:130px; border:none; padding:10px 8px;">HPP / SATUAN</th>
                                            <th class="text-end text-warning" style="width:150px; border:none; padding:10px 8px;">TOTAL BIAYA</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPreviewKebutuhan">
                                        {{-- via JS --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- RINCIAN BIAYA BREAKDOWN --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-7">
                                    <div class="rounded-3 border h-100 overflow-hidden">
                                        <div class="px-3 py-2 fw-bold small text-white" style="background: linear-gradient(135deg, #1e3a5f, #1e40af); font-size:11px; letter-spacing:.5px;">
                                            <i class="bi bi-receipt me-2"></i>RINCIAN BREAKDOWN BIAYA
                                        </div>
                                        <div class="p-0">
                                            <table class="table table-sm mb-0" style="font-size: 0.85rem;">
                                                <tbody>
                                                    <tr>
                                                        <td class="ps-3 text-theme-muted">
                                                            <i class="bi bi-boxes me-1 text-primary"></i>
                                                            Total Bahan Baku &amp; Aksesoris
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold text-primary font-monospace" id="rincianBahan">Rp 0</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="ps-3 text-theme-muted">
                                                            <i class="bi bi-person-workspace me-1 text-info"></i>
                                                            Biaya Tenaga Kerja / CMT
                                                            <small class="text-muted d-block" style="font-size:10px;" id="rincianTkDesc">(x0 batch)</small>
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold text-info font-monospace" id="rincianTk">Rp 0</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="ps-3 text-theme-muted">
                                                            <i class="bi bi-gear me-1 text-secondary"></i>
                                                            Biaya Overhead Produksi
                                                            <small class="text-muted d-block" style="font-size:10px;" id="rincianOhDesc">(x0 batch)</small>
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold text-secondary font-monospace" id="rincianOh">Rp 0</td>
                                                    </tr>
                                                    <tr style="background: rgba(16,185,129,.06); border-top: 2px solid #10b981 !important;">
                                                        <td class="ps-3 fw-bold text-success fs-6">
                                                            <i class="bi bi-check-circle-fill me-1"></i>
                                                            TOTAL HPP PRODUKSI
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold text-success font-monospace fs-5" id="rincianTotal">Rp 0</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-5">
                                    {{-- SUMMARY CARD GRAND TOTAL --}}
                                    <div class="rounded-3 h-100 d-flex flex-column justify-content-center p-4 text-center"
                                         style="background: linear-gradient(135deg,#064e3b,#065f46); border: 2px solid #10b981;">
                                        <div class="d-inline-flex align-items-center justify-content-center text-white fw-extrabold rounded-circle mx-auto mb-2 shadow-sm" style="width:48px; height:48px; font-size:1.3rem; font-family: 'Segoe UI', monospace; background: rgba(52, 211, 153, 0.25); border: 2px solid #34d399; color: #ffffff !important; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">Rp</div>
                                        <div class="text-white-50 mt-1" style="font-size:10px; text-transform:uppercase; letter-spacing:.8px; font-weight:700;">
                                            Estimasi Total Biaya Produksi
                                        </div>
                                        <div class="fw-bold text-white font-monospace mt-1" style="font-size: 1.7rem; letter-spacing:-1px; line-height:1;" id="summaryGrandTotal">
                                            Rp 0
                                        </div>
                                        <div class="text-success mt-2" style="font-size:11px;" id="summaryDesc">untuk 0 unit produksi</div>
                                        <hr style="border-color: rgba(255,255,255,.15); margin: 10px 0;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-white-50" style="font-size:10px;">HPP Per Unit:</span>
                                            <span class="fw-bold text-success font-monospace" style="font-size:13px;" id="summaryHppUnit">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-white-50" style="font-size:10px;">Porsi Bahan:</span>
                                            <span class="fw-bold text-white font-monospace" style="font-size:11px;" id="summaryPorsiBahan">0%</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-white-50" style="font-size:10px;">Porsi TK + Overhead:</span>
                                            <span class="fw-bold text-white font-monospace" style="font-size:11px;" id="summaryPorsiTkOh">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CHART KOMPOSISI BIAYA --}}
                            <div class="rounded-3 border p-3" style="background: var(--bg-light);">
                                <div class="fw-semibold small text-theme-muted mb-2">
                                    <i class="bi bi-bar-chart-fill me-1 text-warning"></i>Komposisi Biaya Produksi
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-theme-muted"><i class="bi bi-circle-fill me-1 text-primary" style="font-size:8px;"></i>Bahan Baku</span>
                                        <span class="small fw-bold text-primary font-monospace" id="barPorsiBahan">0%</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height:10px; background: rgba(0,0,0,.05);">
                                        <div class="progress-bar bg-primary rounded-pill" id="barBahan" role="progressbar" style="width:0%; transition: width .5s ease;"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-theme-muted"><i class="bi bi-circle-fill me-1 text-info" style="font-size:8px;"></i>Tenaga Kerja (CMT)</span>
                                        <span class="small fw-bold text-info font-monospace" id="barPorsiTk">0%</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height:10px; background: rgba(0,0,0,.05);">
                                        <div class="progress-bar bg-info rounded-pill" id="barTk" role="progressbar" style="width:0%; transition: width .5s ease;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-theme-muted"><i class="bi bi-circle-fill me-1 text-secondary" style="font-size:8px;"></i>Overhead</span>
                                        <span class="small fw-bold text-secondary font-monospace" id="barPorsiOh">0%</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height:10px; background: rgba(0,0,0,.05);">
                                        <div class="progress-bar bg-secondary rounded-pill" id="barOh" role="progressbar" style="width:0%; transition: width .5s ease;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- EMPTY STATE (sebelum input) --}}
                        <div id="previewEmptyState" class="text-center py-5">
                            <i class="bi bi-calculator text-warning opacity-25" style="font-size: 4rem;"></i>
                            <div class="fw-bold text-theme-muted mt-3">Masukkan Jumlah Produksi</div>
                            <small class="text-muted">Ketik atau pilih jumlah unit yang ingin diproduksi di atas untuk melihat rincian kebutuhan bahan &amp; total biaya.</small>
                        </div>

                    </div>{{-- end tab-kalkulator --}}
                </div>{{-- end tab-content --}}
            </div>{{-- end modal-body --}}

            <div class="modal-footer bom-modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex align-items-center gap-2">
                    <div id="wrapperApprovalDetailBom" class="d-none">
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('approve-bom'))
                            <button type="button" class="btn btn-success rounded-pill px-3.5 fw-bold shadow-xs" id="btnApproveFromDetail">
                                <i class="bi bi-check-circle-fill me-1"></i> Setujui (Approve)
                            </button>
                            <button type="button" class="btn btn-danger rounded-pill px-3.5 fw-bold ms-1 shadow-xs" id="btnRejectFromDetail">
                                <i class="bi bi-x-circle-fill me-1"></i> Tolak (Reject)
                            </button>
                            <button type="button" class="btn btn-info text-white rounded-pill px-3.5 fw-bold ms-1 shadow-xs d-none" id="btnCompleteFromDetail">
                                <i class="bi bi-check-all me-1"></i> Tandai Selesai
                            </button>
                        @endif
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                            <button type="button" class="btn btn-success rounded-pill px-3.5 fw-bold ms-1 shadow-xs d-none" id="btnReactivateFromDetail">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Aktifkan Kembali
                            </button>
                        @endif
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnPrintFromDetail">
                        <i class="bi bi-printer-fill me-1"></i> Cetak BOM
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
// STATE — Simpan data BOM aktif untuk kalkulasi
// ═══════════════════════════════════════════════════════════════════
if (typeof window.escapeHtmlBom !== 'function') {
    window.escapeHtmlBom = function(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
}

let _bomState = {
    bom            : null,
    items          : [],
    subtotalBahan  : 0,
    hppPerUnitBase : 0,
    satuanNama     : 'Unit',
};

// ═══════════════════════════════════════════════════════════════════
// SHOW DETAIL BOM
// ═══════════════════════════════════════════════════════════════════
function showDetailBom(id) {
    $('#detKodeBom').text('Loading...');
    $('#detNamaBom').text('Memuat rincian resep BOM...');
    $('#tbodyDetailItemsBom').html(`
        <tr>
            <td colspan="8" class="text-center py-4 text-theme-muted">
                <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                Memuat komposisi bahan baku...
            </td>
        </tr>
    `);
    resetKalkulator();

    $.ajax({
        url: `/produksi/bom/${id}/json`,
        method: 'GET',
        success: function(res) {
            if (!res || !res.bom) {
                Swal.fire('Error', 'Data BOM tidak ditemukan atau respons server tidak valid.', 'error');
                return;
            }

            const b          = res.bom;
            const satuanNama = b.satuan_hasil ? b.satuan_hasil.nama_satuan : 'Unit';

            // ─ Header info ─
            $('#detKodeBom').text(b.kode_bom || 'BOM');
            $('#detNamaBom').text(b.nama_bom || 'Detail Resep Produksi Garmen');
            $('#detBarangJadi').text(b.barang_jadi ? `[${b.barang_jadi.kode_barang}] ${b.barang_jadi.nama_barang}` : '-');
            $('#detJumlahHasil').text(`${cleanNumberInputValue(b.jumlah_hasil)} ${satuanNama}`);
            $('#detTipeBom').text(b.tipe_bom || 'Garmen');
            if (b.tender) {
                $('#detTenderName').html(`<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill" title="${window.escapeHtmlBom(b.tender.nama_tender)}"><i class="bi bi-briefcase me-1"></i>${window.escapeHtmlBom(b.tender.nomor_kontrak || 'TENDER')}</span>`);
            } else {
                $('#detTenderName').html('<span class="text-muted small"><i class="bi bi-dash-circle me-1"></i>Tanpa Tender</span>');
            }
            $('#detStatusBadge').html(res.status_badge || b.status);
            $('#detKeterangan').text(b.keterangan || 'Tidak ada catatan spesifikasi.');
            $('#btnPrintFromDetail').attr('onclick', `printBom(${b.id})`);

            if (b.status === 'Pending') {
                $('#wrapperApprovalDetailBom').removeClass('d-none');
                $('#btnApproveFromDetail, #btnRejectFromDetail').removeClass('d-none');
                $('#btnCompleteFromDetail, #btnReactivateFromDetail').addClass('d-none');
                $('#btnApproveFromDetail').off('click').on('click', function() {
                    $('#modalDetailBom').modal('hide');
                    setTimeout(() => { approveBom(b.id); }, 200);
                });
                $('#btnRejectFromDetail').off('click').on('click', function() {
                    $('#modalDetailBom').modal('hide');
                    setTimeout(() => { rejectBom(b.id); }, 200);
                });
            } else if (b.status === 'Approved' || b.status === 'Active') {
                $('#wrapperApprovalDetailBom').removeClass('d-none');
                $('#btnApproveFromDetail, #btnRejectFromDetail, #btnReactivateFromDetail').addClass('d-none');
                $('#btnCompleteFromDetail').removeClass('d-none');
                $('#btnCompleteFromDetail').off('click').on('click', function() {
                    $('#modalDetailBom').modal('hide');
                    setTimeout(() => { completeBom(b.id); }, 200);
                });
            } else if ((b.status === 'Completed' || b.status === 'Selesai' || b.status === 'Inactive') && {{ auth()->user()->hasAnyRole(['Super Admin', 'Owner']) ? 'true' : 'false' }}) {
                $('#wrapperApprovalDetailBom').removeClass('d-none');
                $('#btnApproveFromDetail, #btnRejectFromDetail, #btnCompleteFromDetail').addClass('d-none');
                $('#btnReactivateFromDetail').removeClass('d-none');
                $('#btnReactivateFromDetail').off('click').on('click', function() {
                    $('#modalDetailBom').modal('hide');
                    setTimeout(() => { reactivateBom(b.id); }, 200);
                });
            } else {
                $('#wrapperApprovalDetailBom').addClass('d-none');
            }

            // ─ Kalkulator hints ─
            $('#simSatuanLabel').text(satuanNama);
            $('#simResepHasil').text(`${cleanNumberInputValue(b.jumlah_hasil)} ${satuanNama}`);
            $('#kpiSimSatuan').text(satuanNama);

            // ─ Build tabel resep ─
            let itemsHtml  = '';
            let totalBahan = 0;

            if (res.items && res.items.length > 0) {
                res.items.forEach((item, idx) => {
                    const wasteVal = parseFloat(item.waste_percent) || 0;
                    const wasteFmt = (wasteVal % 1 === 0) ? wasteVal : wasteVal.toString().replace('.', ',');
                    const wasteBadgeHtml = wasteVal > 0 
                        ? `<span class="badge px-2.5 py-1 rounded-pill fw-extrabold" style="background-color: #f59e0b !important; color: #0f172a !important; border: 1px solid #fbbf24; font-size: 11px;">${wasteFmt}%</span>` 
                        : `<span class="text-theme-muted" style="font-size: 11px;">0%</span>`;

                    const reqQty = parseFloat(item.jumlah_kebutuhan) || 0;
                    const hppVal = parseFloat(item.estimasi_harga_satuan) || 0;
                    const grossQty = reqQty * (1 + (wasteVal / 100));
                    const subtotal = grossQty * hppVal;
                    totalBahan += subtotal;

                    const hppCell = res.can_view_hpp 
                        ? `<td class="text-end font-monospace" style="white-space:nowrap;">Rp ${numberFormatID(item.estimasi_harga_satuan)}</td>`
                        : `<td class="text-center text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></td>`;

                    const subtotalCell = res.can_view_hpp
                        ? `<td class="text-end font-monospace fw-bold text-primary" style="white-space:nowrap; min-width:170px;">Rp ${numberFormatID(subtotal)}</td>`
                        : `<td class="text-center text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></td>`;

                    itemsHtml += `
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td class="fw-bold text-primary">${escapeHtmlBom(item.barang_mentah ? item.barang_mentah.kode_barang : '-')}</td>
                            <td class="fw-semibold text-theme-main">${escapeHtmlBom(item.barang_mentah ? item.barang_mentah.nama_barang : '-')}</td>
                            <td class="text-center font-monospace">${cleanNumberInputValue(item.jumlah_kebutuhan)}</td>
                            <td class="text-center">${wasteBadgeHtml}</td>
                            <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-theme-main border-0">${escapeHtmlBom(item.satuan ? item.satuan.nama_satuan : '-')}</span></td>
                            ${hppCell}
                            ${subtotalCell}
                        </tr>`;
                });
            } else {
                itemsHtml = `<tr><td colspan="8" class="text-center py-4 text-muted">Belum ada rincian bahan baku.</td></tr>`;
            }

            $('#tbodyDetailItemsBom').html(itemsHtml);

            // ─ Footer resep ─
            const tk         = parseFloat(b.biaya_tenaga_kerja) || 0;
            const oh         = parseFloat(b.biaya_overhead) || 0;
            const totalHpp   = totalBahan + tk + oh;
            const target     = parseFloat(b.jumlah_hasil) || 1;
            const hppPerUnit = target > 0 ? (totalHpp / target) : totalHpp;

            if (res.can_view_hpp) {
                $('#detSubtotalBahan').text('Rp ' + numberFormatID(totalBahan));
                $('#detBiayaTk').text('Rp ' + numberFormatID(tk));
                $('#detBiayaOh').text('Rp ' + numberFormatID(oh));
                $('#detTotalHpp').text('Rp ' + numberFormatID(totalHpp));
                $('#detHppPerUnit').text(`Rp ${numberFormatID(hppPerUnit)} / ${satuanNama}`);
                $('#tab-kalkulator-btn').removeClass('d-none');
            } else {
                $('#detSubtotalBahan').html('<span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                $('#detBiayaTk').html('<span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                $('#detBiayaOh').html('<span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                $('#detTotalHpp').html('<span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                $('#detHppPerUnit').html('<span class="text-muted" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                $('#tab-kalkulator-btn').addClass('d-none');
            }

            // ─ Simpan state ─
            _bomState = { bom: b, items: res.items, subtotalBahan: totalBahan, hppPerUnitBase: hppPerUnit, satuanNama };

            $('#modalDetailBom').modal('show');
        },
        error: function(xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat rincian BOM.', 'error');
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// HELPER — pindah ke tab kalkulator
// ═══════════════════════════════════════════════════════════════════
function pindahKeKalkulator() {
    const tabEl = document.querySelector('#tab-kalkulator-btn');
    bootstrap.Tab.getOrCreateInstance(tabEl).show();
    setTimeout(() => $('#simJumlahProduksi').focus(), 300);
}

// ═══════════════════════════════════════════════════════════════════
// QUICK SET & ADJUST QTY
// ═══════════════════════════════════════════════════════════════════
function setSimQty(val) {
    $('#simJumlahProduksi').val(val);
    hitungSimulasiProduksi();
}

function adjustSimQty(delta) {
    const cur = parseFloat($('#simJumlahProduksi').val()) || 0;
    const nv  = Math.max(1, cur + delta);
    $('#simJumlahProduksi').val(nv);
    hitungSimulasiProduksi();
}

// ═══════════════════════════════════════════════════════════════════
// HITUNG SIMULASI PRODUKSI — INTI KALKULATOR
// ═══════════════════════════════════════════════════════════════════
function hitungSimulasiProduksi() {
    const qty = parseFloat($('#simJumlahProduksi').val()) || 0;

    // Update badge tab
    if (qty > 0) {
        $('#badgeSimQty').text(numberFormatID(qty)).show();
    } else {
        $('#badgeSimQty').hide();
    }

    if (qty <= 0 || !_bomState.bom) {
        $('#previewKebutuhanSection').addClass('d-none');
        $('#previewEmptyState').removeClass('d-none');
        $('#kpiSimQty').text('-');
        $('#kpiSimHppUnit').text('-');
        $('#kpiSimGrandTotal').text('Rp 0');
        $('#kpiSimDesc').text('Masukkan jumlah produksi');
        return;
    }

    const b           = _bomState.bom;
    const targetResep = parseFloat(b.jumlah_hasil) || 1;
    // Faktor: berapa batch resep yang dibutuhkan
    const faktor      = qty / targetResep;

    const tk = parseFloat(b.biaya_tenaga_kerja) || 0;
    const oh = parseFloat(b.biaya_overhead) || 0;

    // ── Hitung per item & build tabel preview ──
    let simSubtotalBahan = 0;
    let previewHtml      = '';

    if (_bomState.items && _bomState.items.length > 0) {
        _bomState.items.forEach((item, idx) => {
            // QTY per 1 unit hasil (gross termasuk waste)
            const grossQtyPerResep = item.jumlah_kebutuhan * (1 + (item.waste_percent / 100));
            // QTY per 1 unit output (qty yang dibutuhkan untuk 1 pcs output)
            const qtyPerUnit       = grossQtyPerResep / targetResep;
            // Total qty untuk semua unit
            const totalQty         = qtyPerUnit * qty;
            const hpp              = parseFloat(item.estimasi_harga_satuan) || 0;
            const totalBiayaItem   = totalQty * hpp;
            simSubtotalBahan      += totalBiayaItem;

            const satuanNama = item.satuan ? item.satuan.nama_satuan : '-';
            const namaBahan  = item.barang_mentah ? item.barang_mentah.nama_barang : '-';
            const kodeBahan  = item.barang_mentah ? item.barang_mentah.kode_barang : '-';

            previewHtml += `
                <tr>
                    <td class="text-center text-theme-muted">${idx + 1}</td>
                    <td class="fw-bold text-primary font-monospace" style="font-size:12px;">${escapeHtmlBom(kodeBahan)}</td>
                    <td class="fw-semibold text-theme-main">${escapeHtmlBom(namaBahan)}
                        ${item.waste_percent > 0 ? `<span class="badge rounded-pill ms-1 fw-extrabold shadow-2xs" style="font-size:9.5px; background-color:#f59e0b !important; color:#0f172a !important; border:1px solid #fbbf24;">${item.waste_percent}% waste</span>` : ''}
                    </td>
                    <td class="text-center font-monospace" style="font-size:12.5px;">
                        <span class="text-theme-muted">${numberFormatID(qtyPerUnit)}</span> <strong class="text-theme-main">${escapeHtmlBom(satuanNama)}</strong>
                    </td>
                    <td class="text-center font-monospace" style="padding:7px 8px;">
                        <div class="fw-bold" style="font-size:13.5px; color:#d97706;">${numberFormatID(totalQty)}</div>
                        <div class="small fw-bold text-theme-muted" style="font-size:11.5px;">${escapeHtmlBom(satuanNama)}</div>
                    </td>
                    <td class="text-center fw-bold text-theme-main" style="font-size:12.5px;">
                        ${escapeHtmlBom(satuanNama)}
                    </td>
                    <td class="text-end font-monospace text-theme-muted" style="font-size:0.85rem;">Rp ${numberFormatID(hpp)}</td>
                    <td class="text-end font-monospace fw-bold" style="color:#d97706; font-size:13px;">Rp ${numberFormatID(totalBiayaItem)}</td>
                </tr>`;
        });

        // ── Baris footer tabel preview ──
        previewHtml += `
            <tr style="background: rgba(245,158,11,.07); border-top: 2px solid #d97706;">
                <td colspan="7" class="text-end fw-bold text-warning pe-3">TOTAL KEBUTUHAN BAHAN (${numberFormatID(qty)} ${_bomState.satuanNama}):</td>
                <td class="text-end font-monospace fw-bold text-warning pe-3">Rp ${numberFormatID(simSubtotalBahan)}</td>
            </tr>`;
    } else {
        previewHtml = `<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data bahan baku.</td></tr>`;
    }

    $('#tbodyPreviewKebutuhan').html(previewHtml);

    // ── Hitung total biaya ──
    const simTk       = tk * faktor;
    const simOh       = oh * faktor;
    const grandTotal  = simSubtotalBahan + simTk + simOh;
    const hppPerUnit  = qty > 0 ? (grandTotal / qty) : 0;

    // Persentase komposisi
    const porsiBahan  = grandTotal > 0 ? (simSubtotalBahan / grandTotal * 100) : 0;
    const porsiTk     = grandTotal > 0 ? (simTk / grandTotal * 100) : 0;
    const porsiOh     = grandTotal > 0 ? (simOh / grandTotal * 100) : 0;
    const porsiTkOh   = porsiTk + porsiOh;

    // ── Update KPI header ──
    $('#kpiSimQty').text(numberFormatID(qty));
    $('#kpiSimHppUnit').text('Rp ' + numberFormatID(hppPerUnit));
    $('#kpiSimGrandTotal').text('Rp ' + numberFormatID(grandTotal));
    $('#kpiSimDesc').text(`untuk ${numberFormatID(qty)} ${_bomState.satuanNama} produksi`);

    // ── Update badge preview ──
    $('#previewQtyLabel').text(`untuk ${numberFormatID(qty)} ${_bomState.satuanNama}`);
    $('#previewJumlahItem').text(`${_bomState.items ? _bomState.items.length : 0} Item Bahan`);

    // ── Update rincian biaya ──
    const batchFmt = numberFormatID(parseFloat(faktor.toFixed(4)));
    $('#rincianBahan').text('Rp ' + numberFormatID(simSubtotalBahan));
    $('#rincianTk').text('Rp ' + numberFormatID(simTk));
    $('#rincianTkDesc').text(`(${batchFmt} batch × Rp ${numberFormatID(tk)} per batch)`);
    $('#rincianOh').text('Rp ' + numberFormatID(simOh));
    $('#rincianOhDesc').text(`(${batchFmt} batch × Rp ${numberFormatID(oh)} per batch)`);
    $('#rincianTotal').text('Rp ' + numberFormatID(grandTotal));

    // ── Update summary card ──
    $('#summaryGrandTotal').text('Rp ' + numberFormatID(grandTotal));
    $('#summaryDesc').text(`untuk ${numberFormatID(qty)} ${_bomState.satuanNama} produksi`);
    $('#summaryHppUnit').text('Rp ' + numberFormatID(hppPerUnit) + ' / ' + _bomState.satuanNama);
    $('#summaryPorsiBahan').text(porsiBahan.toFixed(1) + '%');
    $('#summaryPorsiTkOh').text(porsiTkOh.toFixed(1) + '%');

    // ── Update progress bar ──
    $('#barBahan').css('width', porsiBahan + '%');
    $('#barTk').css('width', porsiTk + '%');
    $('#barOh').css('width', porsiOh + '%');
    $('#barPorsiBahan').text(porsiBahan.toFixed(1) + '%');
    $('#barPorsiTk').text(porsiTk.toFixed(1) + '%');
    $('#barPorsiOh').text(porsiOh.toFixed(1) + '%');

    // ── Tampilkan section preview ──
    $('#previewEmptyState').addClass('d-none');
    $('#previewKebutuhanSection').removeClass('d-none');
}

// ═══════════════════════════════════════════════════════════════════
// RESET KALKULATOR
// ═══════════════════════════════════════════════════════════════════
function resetKalkulator() {
    $('#simJumlahProduksi').val('');
    $('#badgeSimQty').hide();
    $('#previewKebutuhanSection').addClass('d-none');
    $('#previewEmptyState').removeClass('d-none');
    $('#kpiSimQty, #kpiSimHppUnit').text('-');
    $('#kpiSimGrandTotal').text('Rp 0');
    $('#kpiSimDesc').text('Masukkan jumlah produksi');
    $('#tbodyPreviewKebutuhan').empty();
    $('#barBahan, #barTk, #barOh').css('width', '0%');
    // kembali ke tab resep
    const tabEl = document.querySelector('#tab-resep-btn');
    if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
}

// Reset saat modal ditutup
$('#modalDetailBom').on('hidden.bs.modal', function() {
    _bomState = { bom: null, items: [], subtotalBahan: 0, hppPerUnitBase: 0, satuanNama: 'Unit' };
    resetKalkulator();
});
</script>
@endpush
