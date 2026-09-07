{{-- MODAL JURNAL AKUNTANSI SPK PENGELUARAN BAHAN BAKU --}}
<div class="modal fade" id="modalJurnalSpkPengeluaran" tabindex="-1" aria-labelledby="modalJurnalSpkPengeluaranTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 960px;">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">

            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative"
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center"
                         style="background: rgba(255,255,255,0.15); width: 44px; height: 44px;">
                        <i class="bi bi-journal-bookmark-fill fs-5 text-cyan-400" style="color: #38bdf8;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalJurnalSpkPengeluaranTitle"
                                style="letter-spacing: -0.2px;">Jurnal Full Pengeluaran Bahan Baku</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-semibold"
                                  style="background: rgba(56, 189, 248, 0.2); font-size: 10px; color: #7dd3fc;">RAW MATERIAL JOURNALS</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;" id="modalJurnalSpkPengeluaranSubtitle">SPK: —</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0">

                {{-- LOADING STATE --}}
                <div id="jurnal_spk_pb_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-3 text-muted fw-semibold" style="font-size: 13px;">Memuat data jurnal pengeluaran bahan...</div>
                </div>

                {{-- EMPTY STATE --}}
                <div id="jurnal_spk_pb_empty" class="text-center py-5 d-none">
                    <i class="bi bi-journal-x text-muted" style="font-size: 3rem; opacity: 0.4;"></i>
                    <div class="mt-3 fw-bold text-muted" style="font-size: 14px;">Belum Ada Jurnal Pengeluaran</div>
                    <small class="text-muted">Jurnal akan terbuat secara otomatis saat transaksi pengeluaran bahan baku dicatat dari gudang.</small>
                </div>

                {{-- CONTENT --}}
                <div id="jurnal_spk_pb_content" class="d-none">

                    {{-- SUMMARY STRIP --}}
                    <div class="px-4 py-3 border-bottom d-flex align-items-center gap-4 flex-wrap"
                         style="background: rgba(56, 189, 248, 0.06);">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold" style="font-size: 11px; letter-spacing: 0.3px;">TOTAL SESI</span>
                            <span id="jurnal_spk_pb_total_sesi" class="fw-bold text-theme-main font-monospace" style="font-size: 13px;">0</span>
                        </div>
                        <div class="vr opacity-25 d-none d-md-block"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold" style="font-size: 11px; letter-spacing: 0.3px;">TOTAL DEBIT (WIP)</span>
                            <span id="jurnal_spk_pb_total_debit" class="fw-bold text-success font-monospace" style="font-size: 13px;">Rp 0</span>
                        </div>
                        <div class="vr opacity-25 d-none d-md-block"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold" style="font-size: 11px; letter-spacing: 0.3px;">TOTAL KREDIT (PERSEDIAAN)</span>
                            <span id="jurnal_spk_pb_total_kredit" class="fw-bold text-primary font-monospace" style="font-size: 13px;">Rp 0</span>
                        </div>
                    </div>

                    {{-- JURNAL PER SESI CONTAINER --}}
                    <div id="jurnal_spk_pb_sessions_container" class="p-4 d-flex flex-column gap-4">
                        {{-- Diisi via JS --}}
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer px-4 py-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold"
                        data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
