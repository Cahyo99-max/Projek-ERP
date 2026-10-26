{{-- MODAL DETAIL SPK & ROUTING PRODUKSI --}}
<div class="modal fade spk-modal-primary" id="modalDetailSpk" tabindex="-1" aria-labelledby="modalDetailSpkLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-dark py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white" id="det_nomor_spk">SPK/...</h5>
                            <span id="det_status_badge"></span>
                            <span id="det_prioritas_badge"></span>
                        </div>
                        <small class="text-white-50" id="det_nama_bom">BOM Produksi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body spk-modal-body p-4">
                {{-- BANNER PENDING APPROVAL --}}
                <div id="det_pending_banner" class="rounded-4 d-flex align-items-center justify-content-between p-3 mb-4 border d-none" style="background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.35) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-shield-exclamation fs-2 text-warning"></i>
                        <div>
                            <strong class="d-block text-warning fw-bold fs-6">Menunggu Persetujuan Atasan (Pending Approval)</strong>
                            <span class="small spk-text-muted">SPK ini membutuhkan persetujuan (Approve) atasan sebelum pengerjaan produksi dapat dimulai.</span>
                        </div>
                    </div>
                </div>

                {{-- BANNER REJECTED --}}
                <div id="det_rejected_banner" class="rounded-4 d-flex align-items-center justify-content-between p-3 mb-4 border d-none" style="background: rgba(220, 53, 69, 0.1); border-color: rgba(220, 53, 69, 0.4) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-x-circle-fill fs-2" style="color: #dc3545;"></i>
                        <div>
                            <strong class="d-block fw-bold fs-6" style="color: #dc3545;">SPK Ditolak (Rejected)</strong>
                            <span class="small spk-text-muted" id="det_rejected_reason">SPK ini telah ditolak oleh atasan. Silakan revisi dan ajukan kembali.</span>
                        </div>
                    </div>
                </div>

                {{-- SECTION 1: SUMMARY KPI & INFO PRODUK --}}
                <div class="spk-card border-0 rounded-4 shadow-xs mb-4 p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-4 col-xl-4">
                            <small class="spk-text-muted text-uppercase fw-semibold" style="font-size: 11px;">Barang Jadi (Hasil Produksi)</small>
                            <h6 class="fw-bold text-primary mb-0 mt-1" id="det_barang_jadi">-</h6>
                            <small class="spk-text-muted d-block mt-0.5" id="det_nama_bom_sub">-</small>
                        </div>
                        <div class="col-6 col-md-3 col-xl-3 text-md-center">
                            <small class="spk-text-muted text-uppercase fw-semibold" style="font-size: 11px;">Target Qty Produksi</small>
                            <h5 class="fw-extrabold spk-text-main mb-0 mt-1" id="det_target_qty">0 Pcs</h5>
                        </div>
                        <div class="col-6 col-md-2 col-xl-2 text-md-center">
                            <small class="spk-text-muted text-uppercase fw-semibold" style="font-size: 11px;">Target Selesai</small>
                            <h6 class="fw-bold spk-text-muted mb-0 mt-1" id="det_deadline">-</h6>
                        </div>
                        <div class="col-12 col-md-3 col-xl-3 text-md-end d-flex flex-column align-items-md-end justify-content-center">
                            <small class="spk-text-muted text-uppercase fw-semibold d-block mb-1.5" style="font-size: 11px;">Foto Produk</small>
                            <div id="det_foto_produk_direct">
                                {{-- Direct photo preview rendered here --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BATCH & SERIAL NUMBER CARD --}}
                <div id="det_batch_sn_box" class="spk-card border-0 rounded-4 shadow-xs mb-4 p-3.5 d-none">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-4">
                            <small class="spk-text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                <i class="bi bi-upc-scan text-primary me-1"></i>Nomor Batch / Lot Produksi
                            </small>
                            <div class="mt-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-3 font-monospace fs-6" id="det_batch_no">-</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <small class="spk-text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-qr-code text-success me-1"></i>Serial Number (SN) / Barcode Produk
                                </small>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-0.5" id="det_sn_count" style="font-size: 11px;">0 SN</span>
                            </div>
                            <div class="p-2 rounded-3 border custom-scrollbar font-monospace" id="det_serials_list" style="background: rgba(0,0,0,0.02); max-height: 85px; overflow-y: auto; font-size: 11.5px; line-height: 1.6;">
                                -
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TENDER / KONTRAK INFO JIKA TERKAIT --}}
                <div id="det_tender_box" class="rounded-4 p-3 mb-4 d-none" style="background: rgba(139, 92, 246, 0.08); border: 1.5px solid rgba(139, 92, 246, 0.25);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-xs flex-shrink-0" style="width: 38px; height: 38px; background: linear-gradient(135deg, #7c3aed, #8b5cf6);">
                                <i class="bi bi-file-earmark-text-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold d-block" style="color: #7c3aed; font-size: 11px; letter-spacing: 0.5px;">Terkait Pesanan Tender / Proyek</small>
                                <span class="fw-bold spk-text-main" style="font-size: 14px;" id="det_tender_title">-</span>
                            </div>
                        </div>
                        <div id="det_tender_customer_badge"></div>
                    </div>
                </div>

                {{-- SECTION 2: TAHAPAN OPERASI KERJA --}}
                <div class="spk-card border-0 rounded-4 shadow-xs mb-4">
                    <div class="spk-card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="fw-bold mb-0 spk-text-main">
                            <i class="bi bi-diagram-3-fill me-2 text-primary"></i> Alur Tahapan Kerja Produksi (Routing)
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            {{-- Tombol Mulai Produksi (Jika Status Approved) --}}
                            <button type="button" id="btnDetailStartSpk" class="btn btn-primary rounded-pill px-3.5 py-1.5 btn-sm fw-bold shadow-xs d-none">
                                <i class="bi bi-play-circle-fill me-1"></i> Mulai Produksi
                            </button>
                            {{-- Tombol Selesaikan Produksi (Jika Status In Progress) --}}
                            <button type="button" id="btnDetailCompleteSpk" class="btn btn-success rounded-pill px-3.5 py-1.5 btn-sm fw-bold shadow-xs d-none">
                                <i class="bi bi-check-all me-1"></i> Selesaikan Produksi
                            </button>
                            {{-- Tombol Batalkan SPK (Khusus Super Admin & Owner) --}}
                            <button type="button" id="btnDetailCancelSpk" class="btn btn-outline-danger rounded-pill px-3.5 py-1.5 btn-sm fw-bold shadow-xs d-none">
                                <i class="bi bi-x-circle-fill me-1"></i> Batalkan SPK
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3" id="containerTahapanCards">
                            {{-- Dynamically populated tahapan cards --}}
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: ANALISIS KEBUTUHAN BAHAN BAKU --}}
                <div id="sectionBahanBakuDetail" class="spk-card border-0 rounded-4 shadow-xs mb-4">
                    <div class="spk-card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 spk-text-main">
                            <i class="bi bi-box-seam-fill me-2 text-primary"></i> Analisis Kebutuhan Bahan Baku & Aksesoris
                        </h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold" id="det_total_bahan_badge">0 Bahan</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap spk-table spk-table-materials-comparison">
                                <thead>
                                    <tr class="small text-uppercase fw-bold border-bottom">
                                        <th style="width: 35px;" class="text-center bg-body-tertiary">No</th>
                                        <th class="bg-body-tertiary">Bahan Baku</th>
                                        <th class="text-center bg-body-tertiary">Kebutuhan / Unit</th>
                                        <th class="text-center bg-body-tertiary">Waste %</th>
                                        <th class="text-center fw-bold" style="background-color: rgba(59, 130, 246, 0.1) !important; color: #1d4ed8; border-left: 2px solid rgba(59, 130, 246, 0.25);">
                                            <i class="bi bi-bullseye me-1"></i> Target SPK
                                        </th>
                                        <th class="text-center fw-bold" style="background-color: rgba(16, 185, 129, 0.12) !important; color: #047857; border-left: 1px solid rgba(16, 185, 129, 0.25);">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Realisasi Keluar
                                        </th>
                                        <th class="text-center bg-body-tertiary">Status Keluar</th>
                                        <th class="text-end bg-body-tertiary">HPP</th>
                                        <th class="text-end fw-bold" style="background-color: rgba(59, 130, 246, 0.1) !important; color: #1d4ed8; border-left: 2px solid rgba(59, 130, 246, 0.25);">
                                            Subtotal (Rencana)
                                        </th>
                                        <th class="text-end fw-bold pe-3" style="background-color: rgba(16, 185, 129, 0.12) !important; color: #047857; border-left: 1px solid rgba(16, 185, 129, 0.25);">
                                            Subtotal (Aktual)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDetailMaterials">
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold border-top">
                                        <td colspan="8" class="text-end spk-text-main py-2.5">
                                            <i class="bi bi-boxes text-primary me-1"></i> Total Biaya Bahan Baku:
                                        </td>
                                        <td class="text-end font-monospace text-primary py-2.5" id="det_foot_total_bahan" title="Total Biaya Rencana (Target SPK)" style="background-color: rgba(59, 130, 246, 0.08); border-left: 2px solid rgba(59, 130, 246, 0.25); font-size: 13px;">Rp 0</td>
                                        <td class="text-end font-monospace fw-extrabold text-emerald pe-3 py-2.5" id="det_foot_total_bahan_aktual" title="Total Biaya Aktual (Fisik Keluar Gudang)" style="background-color: rgba(16, 185, 129, 0.1); border-left: 1px solid rgba(16, 185, 129, 0.25); font-size: 13px; color: #059669;">Rp 0</td>
                                    </tr>
                                    <tr class="small">
                                        <td colspan="8" class="text-end spk-text-main py-2">
                                            <i class="bi bi-person-gear text-info me-1"></i> Biaya Tenaga Kerja / CMT (Alokasi):
                                        </td>
                                        <td class="text-end font-monospace text-primary py-2" id="det_foot_tk_rencana" title="Biaya Tenaga Kerja Rencana" style="background-color: rgba(59, 130, 246, 0.05); border-left: 2px solid rgba(59, 130, 246, 0.25); font-size: 12.5px;">Rp 0</td>
                                        <td class="text-end font-monospace pe-3 py-2 spk-text-main" id="det_foot_tk_aktual" title="Biaya Tenaga Kerja Aktual" style="background-color: rgba(16, 185, 129, 0.06); border-left: 1px solid rgba(16, 185, 129, 0.25); font-size: 12.5px;">Rp 0</td>
                                    </tr>
                                    <tr class="small">
                                        <td colspan="8" class="text-end spk-text-main py-2">
                                            <i class="bi bi-lightning-charge text-warning me-1"></i> Biaya Overhead Pabrik (Alokasi):
                                        </td>
                                        <td class="text-end font-monospace text-primary py-2" id="det_foot_oh_rencana" title="Biaya Overhead Rencana" style="background-color: rgba(59, 130, 246, 0.05); border-left: 2px solid rgba(59, 130, 246, 0.25); font-size: 12.5px;">Rp 0</td>
                                        <td class="text-end font-monospace pe-3 py-2 spk-text-main" id="det_foot_oh_aktual" title="Biaya Overhead Aktual" style="background-color: rgba(16, 185, 129, 0.06); border-left: 1px solid rgba(16, 185, 129, 0.25); font-size: 12.5px;">Rp 0</td>
                                    </tr>
                                    <tr class="fw-extrabold border-top-2" style="background: linear-gradient(90deg, rgba(37,99,235,0.05) 0%, rgba(16,185,129,0.08) 100%);">
                                        <td colspan="8" class="text-end fs-6 spk-text-main py-3">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <i class="bi bi-wallet2 text-primary"></i>
                                                <span>Grand Total Biaya SPK:</span>
                                            </div>
                                        </td>
                                        <td class="text-end font-monospace fs-6 text-primary py-3" id="det_foot_grand_total" title="Grand Total Rencana" style="background-color: rgba(59, 130, 246, 0.12); border-left: 2px solid rgba(59, 130, 246, 0.3);">Rp 0</td>
                                        <td class="text-end font-monospace fs-6 pe-3 py-3" id="det_foot_grand_total_aktual" title="Grand Total Aktual Realisasi" style="background-color: rgba(16, 185, 129, 0.18); border-left: 1px solid rgba(16, 185, 129, 0.3); color: #047857;">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- PANDUAN RUMUS KALKULASI OTOMATIS SPK --}}
                    <div class="p-2.5 px-3 rounded-bottom-4 border-top d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2" style="background: rgba(37,99,235,0.03); font-size: 11.5px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                <i class="bi bi-calculator me-1"></i> Rumus SPK
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 spk-text-muted small">
                            <span><strong class="text-primary">Kebutuhan Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(37,99,235,0.08); color:var(--spk-accent);">Target SPK × Qty Resep × (1 + Waste%)</code></span>
                            <span><strong class="text-success">Subtotal Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(16,185,129,0.08); color:#059669;">Total Kebutuhan × HPP</code></span>
                            <span><strong class="text-warning">Grand Total SPK:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.08); color:#d97706;">Bahan + TK + Overhead</code></span>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3 LOCKED (JIKA TIDAK PUNYA HAK AKSES LIHAT BAHAN) --}}
                <div id="sectionBahanBakuLocked" class="spk-card border-0 rounded-4 shadow-xs p-4 text-center d-none" style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3) !important;">
                    <div class="py-3">
                        <i class="bi bi-shield-lock-fill fs-2 text-warning mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1" style="color:var(--spk-text-main,#0f172a);">Rincian Bahan Baku & Aksesoris Dibatasi</h6>
                        <p class="small text-muted mb-0">Akun Anda tidak memiliki hak akses <strong>Lihat Rincian Bahan Baku & Aksesoris SPK</strong> (lihat-bahan-spk).</p>
                    </div>
                </div>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between align-items-stretch align-items-sm-center gap-2 p-3 p-sm-4">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="button" id="btnDetailPrintSpk" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-printer me-1"></i> Cetak SPK
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2" id="detailActionButtonsGroup">
                    {{-- Tombol Tolak SPK (Jika Pending & Can Approve) --}}
                    <button type="button" id="btnDetailRejectSpk" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold shadow-xs d-none">
                        <i class="bi bi-x-circle me-1"></i> Tolak SPK
                    </button>

                    {{-- Tombol Setujui SPK (Jika Pending & Can Approve) --}}
                    <button type="button" id="btnDetailApproveSpk" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-xs d-none">
                        <i class="bi bi-check-circle-fill me-1"></i> Setujui (Approve) SPK
                    </button>

                    {{-- Tombol Ajukan Kembali (Jika Rejected) --}}
                    <button type="button" id="btnDetailResubmitSpk" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-xs d-none">
                        <i class="bi bi-send-fill me-1"></i> Ajukan Kembali ke Atasan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO PRODUK (LIGHTBOX) --}}
<div class="modal fade" id="modalPreviewFotoProduk" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: rgba(10, 15, 30, 0.97); backdrop-filter: blur(16px);">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="previewFotoTitle">Foto Barang Jadi</h5>
                    <small class="text-white-50" id="previewFotoSub">-</small>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="d-inline-block rounded-3 overflow-hidden shadow-lg" style="background: rgba(0,0,0,0.3); padding: 6px; max-width: 100%;">
                    <img id="imgPreviewFotoProduk" src="" class="img-fluid rounded-3" style="max-height: 65vh; max-width: 100%; object-fit: contain; display: block;" alt="Preview Foto Produk">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewFotoSpk(url, title, sub) {
        $('#imgPreviewFotoProduk').attr('src', '').attr('src', url);
        $('#previewFotoTitle').text(title || 'Foto Barang Jadi');
        $('#previewFotoSub').text(sub || '');
        // Handle nested modal properly
        const previewModal = new bootstrap.Modal(document.getElementById('modalPreviewFotoProduk'), { backdrop: true, keyboard: true });
        previewModal.show();
    }

    // When photo preview closes, ensure detail modal stays visible
    document.addEventListener('DOMContentLoaded', function() {
        const previewEl = document.getElementById('modalPreviewFotoProduk');
        if (previewEl) {
            previewEl.addEventListener('hidden.bs.modal', function() {
                document.body.classList.add('modal-open');
                const detailModal = document.getElementById('modalDetailSpk');
                if (detailModal && detailModal.classList.contains('show')) {
                    detailModal.style.display = 'block';
                }
            });
        }
    });
    function showDetailSpk(id) {
        Swal.fire({ title: 'Memuat detail SPK...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        $.ajax({
            url: `/produksi/spk/${id}`,
            method: 'GET',
            success: function(res) {
                Swal.close();
                const s = res.spk;
                $('#det_nomor_spk').text(s.nomor_spk);
                $('#det_nama_bom').text(s.bom ? `Resep: [${s.bom.kode_bom}] ${s.bom.nama_bom}` : 'Resep BOM');
                $('#det_barang_jadi').text(s.barang_jadi ? `[${s.barang_jadi.kode_barang}] ${s.barang_jadi.nama_barang}` : '-');
                $('#det_target_qty').text(Number(s.jumlah_rencana).toLocaleString('id-ID') + ' ' + (s.satuan ? s.satuan.nama_satuan : 'Pcs'));
                $('#det_deadline').text(s.target_selesai ? new Date(s.target_selesai).toLocaleDateString('id-ID') : '-');
                $('#det_nama_bom_sub').text(s.bom ? `Resep: [${s.bom.kode_bom}] ${s.bom.nama_bom}` : '');

                // Tender Info
                const tenderObj = s.tender || (s.bom && s.bom.tender);
                if (tenderObj && (tenderObj.id || tenderObj.nama_tender)) {
                    $('#det_tender_title').text(tenderObj.nama_tender || '');
                    $('#det_tender_customer_badge').empty();
                    $('#det_tender_box').removeClass('d-none');
                } else {
                    $('#det_tender_box').addClass('d-none');
                }

                // Batch & Serial Numbers
                if (s.nomor_batch || s.serial_numbers) {
                    $('#det_batch_sn_box').removeClass('d-none');
                    $('#det_batch_no').text(s.nomor_batch || '-');
                    if (s.serial_numbers) {
                        const snList = s.serial_numbers.split(/[\n,]+/).map(x => x.trim()).filter(Boolean);
                        $('#det_sn_count').text(`${Number(snList.length).toLocaleString('id-ID')} SN`);
                        if (snList.length <= 80) {
                            $('#det_serials_list').html(snList.map(sn => `<span class="badge bg-body-tertiary text-body border me-1 mb-1 font-monospace">${sn}</span>`).join(' '));
                        } else {
                            const previewItems = snList.slice(0, 80);
                            const remaining = snList.length - 80;
                            let html = previewItems.map(sn => `<span class="badge bg-body-tertiary text-body border me-1 mb-1 font-monospace">${sn}</span>`).join(' ');
                            html += ` <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 mb-1 font-monospace fw-semibold">+ ${Number(remaining).toLocaleString('id-ID')} SN lainnya (Total: ${Number(snList.length).toLocaleString('id-ID')} SN)</span>`;
                            $('#det_serials_list').html(html);
                        }
                    } else {
                        $('#det_sn_count').text('0 SN');
                        $('#det_serials_list').text('-');
                    }
                } else {
                    $('#det_batch_sn_box').addClass('d-none');
                }
                let fotoUrl = res.foto_produk_url || '';
                if (!fotoUrl) {
                    if (s.barang_jadi && s.barang_jadi.primary_foto && s.barang_jadi.primary_foto.foto_path) {
                        fotoUrl = `/storage/${s.barang_jadi.primary_foto.foto_path}`;
                    } else if (s.barang_jadi && s.barang_jadi.fotos && s.barang_jadi.fotos.length > 0 && s.barang_jadi.fotos[0].foto_path) {
                        fotoUrl = `/storage/${s.barang_jadi.fotos[0].foto_path}`;
                    }
                }

                if (fotoUrl) {
                    const barangName = (s.barang_jadi ? `[${s.barang_jadi.kode_barang}] ${s.barang_jadi.nama_barang}` : 'Foto Produk');
                    const spkNomor = s.nomor_spk || 'SPK Produksi';
                    $('#det_foto_produk_direct').html(`
                        <a href="javascript:void(0)" onclick="previewFotoSpk('${fotoUrl}', '${barangName.replace(/'/g, "\\'")}', '${spkNomor}')" title="Klik untuk lihat foto ukuran penuh" class="d-inline-block position-relative">
                            <img src="${fotoUrl}" class="rounded-3 border border-primary border-opacity-50 shadow-sm" style="width: 72px; height: 72px; object-fit: cover; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" alt="Foto Produk">
                            <span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-50 translate-middle-x mb-1 px-1.5 py-0.5 rounded-pill" style="font-size: 9px; pointer-events: none;">
                                <i class="bi bi-zoom-in me-0.5"></i> Zoom
                            </span>
                        </a>
                    `);
                } else {
                    $('#det_foto_produk_direct').html(`
                        <div class="rounded-3 border border-secondary border-opacity-25 d-inline-flex flex-column align-items-center justify-content-center bg-body-tertiary px-3 py-2 text-center" style="min-width: 80px; min-height: 70px;">
                            <i class="bi bi-image text-muted fs-4"></i>
                            <small class="text-muted mt-1" style="font-size: 10px;">Tidak ada foto</small>
                        </div>
                    `);
                }

                // Status Badge
                const statusMap = {
                    'Draft':       '<span class="badge bg-secondary rounded-pill px-2.5 py-1">Draft</span>',
                    'Pending':     '<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">Pending Approval</span>',
                    'Rejected':    '<span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:#dc3545;color:#fff;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak (Rejected)</span>',
                    'Approved':    '<span class="badge bg-success rounded-pill px-2.5 py-1">Approved</span>',
                    'In Progress': '<span class="badge bg-info text-dark rounded-pill px-2.5 py-1 fw-bold">In Progress</span>',
                    'Completed':   '<span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:#059669;color:#fff;">Completed</span>',
                    'Cancelled':   '<span class="badge bg-danger rounded-pill px-2.5 py-1">Cancelled</span>'
                };
                $('#det_status_badge').html(statusMap[s.status] || `<span class="badge bg-secondary rounded-pill">${s.status}</span>`);

                const prioritasMap = {
                    'Tinggi (Urgent)': '<span class="badge bg-danger rounded-pill px-2 py-0.5"><i class="bi bi-fire me-1"></i>Urgent</span>',
                    'Normal': '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5">Normal</span>',
                    'Rendah': '<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-0.5">Rendah</span>'
                };
                $('#det_prioritas_badge').html(prioritasMap[s.prioritas] || '');

                // Tahapan Cards
                let tahapanHtml = '';
                const tahapans = s.tahapans || [];
                tahapans.forEach((t) => {
                    let statusBadgeCls = 'bg-secondary';
                    if (t.status === 'Sedang Dikerjakan') statusBadgeCls = 'bg-info text-dark';
                    if (t.status === 'Selesai') statusBadgeCls = 'bg-success';

                    tahapanHtml += `
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <div class="spk-tahapan-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary rounded-circle" style="width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;">${t.urutan}</span>
                                    <span class="badge ${statusBadgeCls} rounded-pill" style="font-size:10px;">${t.status}</span>
                                </div>
                                <div class="spk-tahapan-title">${t.nama_tahapan}</div>
                                <div class="spk-tahapan-sub mt-2">
                                    Target: <b>${Number(t.target_qty).toLocaleString('id-ID')}</b> &nbsp;|&nbsp; Selesai: <b>${Number(t.qty_selesai).toLocaleString('id-ID')}</b>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#containerTahapanCards').html(tahapanHtml || '<div class="col-12 spk-text-muted">Belum ada tahapan kerja yang diatur.</div>');

                // Check Material View Permission
                if (res.can_view_materials) {
                    $('#sectionBahanBakuDetail').removeClass('d-none');
                    $('#sectionBahanBakuLocked').addClass('d-none');

                    // Materials Table
                    let matHtml = '';
                    const mats = s.materials || [];
                    let totalBiayaBahanRencana = 0;
                    let totalBiayaBahanAktual = 0;

                    mats.forEach((m, index) => {
                        const bm = m.barang_mentah || {};
                        const satName = m.satuan ? m.satuan.nama_satuan : (bm.satuan ? bm.satuan.nama_satuan : '');
                        const hargaSatuan = Number(m.estimasi_harga_satuan || 0);
                        const targetQty = Number(m.total_kebutuhan || 0);
                        const keluarQty = Number(m.total_keluar_gudang || 0);

                        const hargaSatuan2Desimal = Math.round(hargaSatuan * 100) / 100;
                        const subtotalRencanaVal  = Math.round((targetQty * hargaSatuan2Desimal) * 100) / 100;
                        const subtotalAktualVal   = Math.round((keluarQty * hargaSatuan2Desimal) * 100) / 100;

                        totalBiayaBahanRencana += subtotalRencanaVal;
                        totalBiayaBahanAktual  += subtotalAktualVal;

                        const subtotalRencana = 'Rp ' + subtotalRencanaVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const subtotalAktual  = 'Rp ' + subtotalAktualVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                        // Status & Realisasi Keluar Badge & Styling
                        const diff = keluarQty - targetQty;
                        let statusBadge = '';
                        let keluarBadge = '';
                        let subtotalAktualHtml = '';

                        if (keluarQty <= 0) {
                            statusBadge = '<span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size:10.5px;">Belum Keluar</span>';
                            keluarBadge = `<span class="badge bg-body-secondary text-secondary border rounded-pill font-monospace px-2.5 py-1" style="font-size: 11px;">0 ${satName}</span>`;
                            subtotalAktualHtml = `<span class="font-monospace text-muted" style="font-size: 12px;">Rp 0,00</span>`;
                        } else if (diff > 0) {
                            statusBadge = `<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold shadow-2xs" style="font-size:10.5px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;" title="Pengeluaran melebihi target SPK"><i class="bi bi-arrow-up-right me-1 text-warning"></i>Lebih (+${diff.toLocaleString('id-ID', { maximumFractionDigits: 4 })})</span>`;
                            keluarBadge = `<span class="badge rounded-pill font-monospace fw-extrabold px-2.5 py-1 shadow-2xs" style="font-size: 11.5px; background-color: #fef08a !important; color: #854d0e !important; border: 1px solid #fde047 !important;">${keluarQty.toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satName}</span>`;
                            subtotalAktualHtml = `<span class="font-monospace fw-extrabold" style="font-size: 12px; color: #b45309 !important;">${subtotalAktual}</span>`;
                        } else if (Math.abs(diff) < 0.0001) {
                            statusBadge = '<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold shadow-2xs" style="font-size:10.5px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;"><i class="bi bi-check-circle-fill me-1 text-success"></i>Sesuai (100%)</span>';
                            keluarBadge = `<span class="badge rounded-pill font-monospace fw-bold px-2.5 py-1 shadow-2xs" style="font-size: 11.5px; background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;">${keluarQty.toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satName}</span>`;
                            subtotalAktualHtml = `<span class="font-monospace fw-bold" style="font-size: 12px; color: #059669 !important;">${subtotalAktual}</span>`;
                        } else {
                            const pct = targetQty > 0 ? Math.round((keluarQty / targetQty) * 100) : 0;
                            statusBadge = `<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold shadow-2xs" style="font-size:10.5px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;" title="Pengeluaran belum lengkap"><i class="bi bi-hourglass-split me-1 text-info"></i>Parsial (${pct}%)</span>`;
                            keluarBadge = `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill font-monospace fw-bold px-2.5 py-1" style="font-size: 11.5px;">${keluarQty.toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satName}</span>`;
                            subtotalAktualHtml = `<span class="font-monospace fw-bold text-info-emphasis" style="font-size: 12px;">${subtotalAktual}</span>`;
                        }

                        matHtml += `
                            <tr>
                                <td class="text-center spk-text-muted small">${index + 1}</td>
                                <td><div class="fw-semibold spk-text-main" style="font-size: 12.5px;">[${bm.kode_barang || '-'}] ${bm.nama_barang || '-'}</div></td>
                                <td class="text-center font-monospace small">${Number(m.kebutuhan_per_unit).toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satName}</td>
                                <td class="text-center spk-text-muted small">${parseFloat(m.waste_percent || 0)}%</td>
                                <td class="text-center" style="background-color: rgba(59, 130, 246, 0.03); border-left: 2px solid rgba(59, 130, 246, 0.15);">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fw-bold px-2.5 py-1" style="font-size: 11.5px;">
                                        ${targetQty.toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satName}
                                    </span>
                                </td>
                                <td class="text-center" style="background-color: rgba(16, 185, 129, 0.04); border-left: 1px solid rgba(16, 185, 129, 0.15);">
                                    ${keluarBadge}
                                </td>
                                <td class="text-center">${statusBadge}</td>
                                <td class="text-end font-monospace small">Rp ${hargaSatuan.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                                <td class="text-end font-monospace text-primary fw-semibold" style="background-color: rgba(59, 130, 246, 0.03); border-left: 2px solid rgba(59, 130, 246, 0.15); font-size: 12px;">
                                    ${subtotalRencana}
                                </td>
                                <td class="text-end pe-3" style="background-color: rgba(16, 185, 129, 0.04); border-left: 1px solid rgba(16, 185, 129, 0.15);">
                                    ${subtotalAktualHtml}
                                </td>
                            </tr>
                        `;
                    });
                    $('#tbodyDetailMaterials').html(matHtml || '<tr><td colspan="10" class="text-center py-4 spk-text-muted">Tidak ada rincian bahan baku.</td></tr>');
                    $('#det_total_bahan_badge').text(`${mats.length} Bahan`);

                    // Footers
                    const biayaTk = Number(s.estimasi_biaya_tk || 0);
                    const biayaOh = Number(s.estimasi_biaya_overhead || 0);
                    const totalTkOh = biayaTk + biayaOh;
                    const grandTotalRencana = totalBiayaBahanRencana + totalTkOh;
                    const grandTotalAktual  = totalBiayaBahanAktual + totalTkOh;

                    $('#det_foot_total_bahan').text('Rp ' + totalBiayaBahanRencana.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#det_foot_total_bahan_aktual').text('Rp ' + totalBiayaBahanAktual.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    $('#det_foot_tk_rencana').text('Rp ' + biayaTk.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#det_foot_tk_aktual').text('Rp ' + biayaTk.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    $('#det_foot_oh_rencana').text('Rp ' + biayaOh.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#det_foot_oh_aktual').text('Rp ' + biayaOh.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    $('#det_foot_grand_total').text('Rp ' + grandTotalRencana.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#det_foot_grand_total_aktual').text('Rp ' + grandTotalAktual.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                } else {
                    $('#sectionBahanBakuDetail').addClass('d-none');
                    $('#sectionBahanBakuLocked').removeClass('d-none');
                }

                // Banner Pending
                if (s.status === 'Pending') {
                    $('#det_pending_banner').removeClass('d-none');
                } else {
                    $('#det_pending_banner').addClass('d-none');
                }

                // Banner Rejected or Cancelled
                if (s.status === 'Rejected') {
                    $('#det_rejected_banner').removeClass('d-none');
                    const alasanMatch = s.keterangan ? s.keterangan.match(/\[Ditolak[^\]]+\]:(.+)/) : null;
                    const alasanText = alasanMatch ? alasanMatch[1].trim() : 'SPK ini telah ditolak oleh atasan.';
                    $('#det_rejected_reason').text(alasanText);
                } else if (s.status === 'Cancelled') {
                    $('#det_rejected_banner').removeClass('d-none');
                    const alasanMatch = s.keterangan ? s.keterangan.match(/\[Dibatalkan[^\]]+\]:(.+)/) : null;
                    const alasanText = alasanMatch ? 'Dibatalkan: ' + alasanMatch[1].trim() : 'SPK ini telah dibatalkan. Silakan perbaiki dan ajukan kembali.';
                    $('#det_rejected_reason').text(alasanText);
                } else {
                    $('#det_rejected_banner').addClass('d-none');
                }

                // Approve & Reject Buttons in Detail Modal
                if (s.status === 'Pending' && res.can_approve) {
                    $('#btnDetailApproveSpk').removeClass('d-none').off('click').on('click', function() {
                        approveSpk(s.id);
                    });
                    $('#btnDetailRejectSpk').removeClass('d-none').off('click').on('click', function() {
                        rejectSpk(s.id);
                    });
                } else {
                    $('#btnDetailApproveSpk').addClass('d-none');
                    $('#btnDetailRejectSpk').addClass('d-none');
                }

                // Re-submit Button (when Rejected or Cancelled — for creator to resubmit)
                if (['Rejected', 'Cancelled'].includes(s.status)) {
                    $('#btnDetailResubmitSpk').removeClass('d-none').off('click').on('click', function() {
                        sendPendingSpk(s.id);
                    });
                } else {
                    $('#btnDetailResubmitSpk').addClass('d-none');
                }

                // Mulai Produksi Button (Di Header Tabel Tahapan)
                if (s.status === 'Approved' && res.can_mulai) {
                    $('#btnDetailStartSpk').removeClass('d-none').off('click').on('click', function() {
                        startProductionSpk(s.id);
                    });
                } else {
                    $('#btnDetailStartSpk').addClass('d-none');
                }

                // Selesaikan Produksi Button (Di Header Detail Modal jika status In Progress)
                if (s.status === 'In Progress' && res.can_selesai) {
                    $('#btnDetailCompleteSpk').removeClass('d-none').off('click').on('click', function() {
                        completeProductionSpk(s.id);
                    });
                } else {
                    $('#btnDetailCompleteSpk').addClass('d-none');
                }

                // Batalkan SPK Button (Khusus Super Admin & Owner jika status Approved atau In Progress)
                if (res.is_super_admin && ['Approved', 'In Progress'].includes(s.status)) {
                    $('#btnDetailCancelSpk').removeClass('d-none').off('click').on('click', function() {
                        cancelSpk(s.id, s.nomor_spk);
                    });
                } else {
                    $('#btnDetailCancelSpk').addClass('d-none');
                }

                // Cetak SPK Button
                if (res.can_print) {
                    $('#btnDetailPrintSpk').removeClass('d-none').off('click').on('click', function() {
                        printSpk(s.id);
                    });
                } else {
                    $('#btnDetailPrintSpk').addClass('d-none');
                }

                $('#modalDetailSpk').modal('show');
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak dapat memuat detail SPK.', 'error');
            }
        });
    }

    function quickStartTahapanFromModalDetail(spkId, tahapanId) {
        $.ajax({
            url: `/produksi/realisasi-produksi/${tahapanId}/start`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Mulai Pengerjaan', text: res.message, timer: 1200, showConfirmButton: false });
                    showDetailSpk(spkId);
                    if (typeof dtSpk !== 'undefined' && dtSpk) dtSpk.ajax.reload(null, false);
                    if (typeof dtRealisasi !== 'undefined' && dtRealisasi) dtRealisasi.ajax.reload(null, false);
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memulai pengerjaan.', 'error');
            }
        });
    }
</script>
@endpush
