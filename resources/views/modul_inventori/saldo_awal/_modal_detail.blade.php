<!-- ─── MODAL: Detail Saldo Awal Stok (Cinematic & Modern) ────────── -->
<style>
    /* ─── Detail Modal Custom Bento Aesthetics ───────────────────────── */
    .opn-modal-container {
        background: #0b132b !important;
        color: #f8fafc !important;
        border: 1px solid rgba(59, 130, 246, 0.25) !important;
    }
    .opn-bento-card {
        background: linear-gradient(145deg, rgba(26, 38, 66, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%) !important;
        border: 1px solid rgba(148, 163, 184, 0.18) !important;
        border-radius: 16px !important;
        padding: 1.25rem !important;
        box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.45) !important;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .opn-bento-card:hover {
        border-color: rgba(59, 130, 246, 0.45) !important;
        transform: translateY(-2px);
    }
    .opn-hero-card {
        background: linear-gradient(145deg, rgba(6, 78, 59, 0.5) 0%, rgba(2, 44, 34, 0.85) 100%) !important;
        border: 1px solid rgba(52, 211, 153, 0.45) !important;
        border-radius: 16px !important;
        padding: 1.25rem !important;
        box-shadow: 0 8px 30px -4px rgba(16, 185, 129, 0.25) !important;
        position: relative;
        overflow: hidden;
    }
    .opn-card-tag {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        padding: 0.35rem 0.65rem !important;
        border-radius: 8px !important;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .opn-label-muted {
        color: #94a3b8 !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        margin-bottom: 0.25rem;
    }
    .opn-val-bright {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
    }
    .opn-table-wrapper {
        border: 1px solid rgba(148, 163, 184, 0.18) !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        background: #0f172a !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    }
    .opn-table-scroll {
        max-height: 420px !important;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        overscroll-behavior: contain !important;
        scrollbar-width: thin !important;
        scrollbar-color: rgba(59, 130, 246, 0.5) rgba(15, 23, 42, 0.8) !important;
    }
    .opn-table-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .opn-table-scroll::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.8);
        border-radius: 6px;
    }
    .opn-table-scroll::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.4);
        border-radius: 6px;
    }
    .opn-table-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.7);
    }
    .opn-table-scroll thead th {
        background: #1e293b !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2) !important;
        padding: 0.85rem 1rem !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25) !important;
    }
    .opn-table-scroll tbody td {
        background: transparent !important;
        color: #f1f5f9 !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1) !important;
        padding: 0.85rem 1rem !important;
    }
    .opn-table-scroll tbody tr:hover td {
        background: rgba(59, 130, 246, 0.08) !important;
    }
    .opn-table-scroll tfoot tr td {
        background: #1e293b !important;
        border-top: 1px solid rgba(148, 163, 184, 0.2) !important;
        position: sticky !important;
        bottom: 0 !important;
        z-index: 10 !important;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.25) !important;
    }
</style>

<div class="modal fade" id="modalDetailOpening" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden opn-modal-container">
            {{-- Cinematic Header --}}
            <div class="p-4 d-flex align-items-center justify-content-between position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #0284c7 100%); border-bottom: 1px solid rgba(255,255,255,0.15);">
                <div class="position-absolute end-0 top-0 bottom-0 opacity-10 pointer-events-none d-none d-md-block" style="font-size: 8rem; line-height: 1; transform: translate(15%, -10%); color: #fff;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="d-flex align-items-center gap-3 z-1">
                    <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.18) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important; border-radius: 14px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); flex-shrink: 0;">
                        <i class="bi bi-receipt-cutoff text-white" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-black text-white mb-0 fs-5 letter-spacing-tight">Rincian Dokumen Saldo Awal</h5>
                            <span id="detail_opn_status_pill"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="text-white-50 small" style="font-size: 0.8rem;">No. Dokumen:</span>
                            <span class="badge font-monospace" id="detail_opn_nomor_sub" style="background: rgba(0, 0, 0, 0.4) !important; color: #67e8f9 !important; border: 1px solid rgba(103, 232, 249, 0.3) !important; font-size: 0.85rem; padding: 0.3rem 0.6rem;">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white z-1 p-2 rounded-circle" style="background-color: rgba(255,255,255,0.15);" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-3 p-md-4" style="max-height: calc(88vh - 120px); overflow-y: auto; background: #0b132b;">
                <div id="modalDetailOpeningBody">
                    {{-- Dynamic Cinematic Content via AJAX --}}
                </div>
            </div>

            {{-- Modern Action Footer --}}
            <div class="modal-footer p-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top" style="background: #0f172a; border-color: rgba(148, 163, 184, 0.18) !important;">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="detail_footer_left">
                    {{-- Export & Jurnal buttons --}}
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap" id="opn_action_buttons">
                    {{-- Confirm / Edit / Submit buttons --}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── MODAL: Pop-up Jurnal Akuntansi Otomatis ─────────────────── -->
<div class="modal fade" id="modalJurnalOpening" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #0f172a; color: #f8fafc; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
            <div class="modal-header bg-success bg-gradient text-white py-3 px-3 px-md-4 border-0">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 bg-white text-success shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px;">
                        <i class="bi bi-journal-bookmark-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-md-5">
                            Jurnal Akuntansi Otomatis
                        </h5>
                        <small class="text-white-50 font-monospace" id="modalJurnalOpeningNo" style="font-size:.78rem;"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modalJurnalOpeningBody">
                {{-- Journal Table injected here via JS --}}
            </div>
            <div class="modal-footer border-0 bg-dark py-2 px-3 px-md-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.currentOpeningJurnal = null;

// ── Show Detail Modal ──────────────────────────────────────────────────
function showDetailOpening(id) {
    if (window.event && window.event.target) {
        const target = $(window.event.target);
        if (target.closest('.btn-outline-danger, .btn-outline-warning, .btn-outline-info, .btn-success, .btn-danger').length > 0 && !target.closest('.btn-outline-primary').length) {
            return;
        }
    }

    $('#modalDetailOpeningBody').html(`
        <div class="text-center py-5">
            <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
            <h6 class="fw-bold mt-3 mb-1 text-white">Memuat Data Saldo Awal...</h6>
            <small style="color: #94a3b8;">Mengambil rincian barang, otorisasi, dan jurnal akuntansi</small>
        </div>
    `);
    $('#detail_opn_nomor_sub').text('-');
    $('#detail_opn_status_pill').html('');
    $('#detail_footer_left').html('');
    $('#opn_action_buttons').html('');
    window.currentOpeningJurnal = null;
    
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailOpening'));
    modal.show();

    $.ajax({
        url: `/opening-stock/${id}`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const os = res.openingStock;
            window.currentOpeningJurnal = res.jurnal;

            $('#detail_opn_nomor_sub').text(os.nomor_opening_stock || '-');

            let statusPill = '';
            if (os.status === 'Draft') {
                statusPill = `<span class="badge rounded-pill" style="background: rgba(148, 163, 184, 0.25) !important; color: #f1f5f9 !important; border: 1px solid rgba(148, 163, 184, 0.4) !important; font-size: 0.8rem; padding: 0.35rem 0.75rem;"><i class="bi bi-pencil-fill me-1"></i>Draft (Konsep)</span>`;
            } else if (os.status === 'Pending') {
                statusPill = `<span class="badge rounded-pill" style="background: rgba(245, 158, 11, 0.25) !important; color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.5) !important; font-size: 0.8rem; padding: 0.35rem 0.75rem;"><i class="bi bi-hourglass-split me-1"></i>Menunggu Persetujuan</span>`;
            } else {
                statusPill = `<span class="badge rounded-pill" style="background: rgba(16, 185, 129, 0.25) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.5) !important; font-size: 0.8rem; padding: 0.35rem 0.75rem;"><i class="bi bi-check-circle-fill me-1"></i>Confirmed (Posting)</span>`;
            }
            $('#detail_opn_status_pill').html(statusPill);

            let detailRows = '';
            let totalNilaiDocument = 0;
            let totalQtyDocument = 0;
            
            (os.details || []).forEach((d, i) => {
                const nama = d.barang ? d.barang.nama_barang : '-';
                const kode = d.barang ? d.barang.kode_barang : '-';
                const sat  = d.barang?.satuan ? d.barang.satuan.nama_satuan : '-';
                const qty  = parseFloat(d.jumlah);
                const hpp  = parseFloat(d.harga_pokok);
                const subtotal = qty * hpp;
                totalNilaiDocument += subtotal;
                totalQtyDocument += qty;

                detailRows += `
                <tr>
                    <td class="text-center font-monospace" style="color: #94a3b8; font-size: 0.82rem;">${i+1}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2.5">
                            <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-box fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-white" style="font-size: .88rem;">${nama}</span>
                                <span class="badge font-monospace" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.73rem; padding: 0.2rem 0.45rem;">${kode}</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="fw-black font-monospace fs-6 text-white">${qty.toLocaleString('id-ID')}</span>
                        <span class="d-block" style="color: #94a3b8; font-size: 0.75rem;">${sat}</span>
                    </td>
                    <td class="text-end font-monospace" style="color: #cbd5e1; font-size: 0.88rem;">
                        Rp ${hpp.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}
                    </td>
                    <td class="text-end fw-black font-monospace fs-6" style="color: #4ade80;">
                        Rp ${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}
                    </td>
                </tr>`;
            });

            // Format tanggal dokumen
            let tglFmt = '-';
            if (os.tanggal_opening_stock) {
                try {
                    let parsedDate = new Date(os.tanggal_opening_stock);
                    if (!isNaN(parsedDate.getTime())) {
                        tglFmt = parsedDate.toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'});
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            // Otorisasi Pembuat & Penyetuju
            const creatorName = (os.creator && (os.creator.karyawan ? os.creator.karyawan.nama_lengkap : os.creator.nama_lengkap)) || os.creator?.name || 'Administrator';
            
            let approverSectionHtml = '';
            if (os.status === 'Confirmed') {
                const approverName = (os.updater && (os.updater.karyawan ? os.updater.karyawan.nama_lengkap : os.updater.nama_lengkap)) || os.updater?.name || 'Administrator / Atasan';
                let tglApproveFmt = '-';
                if (os.updated_at) {
                    try {
                        let pAppDate = new Date(os.updated_at);
                        if (!isNaN(pAppDate.getTime())) {
                            tglApproveFmt = pAppDate.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                        }
                    } catch(e) {}
                }
                approverSectionHtml = `
                    <div class="d-flex align-items-center gap-2.5 p-2 rounded-3" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35);">
                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(16, 185, 129, 0.25); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-shield-fill-check fs-5"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block text-white" style="font-size: .88rem;">${approverName}</span>
                            <small class="d-block" style="color: #6ee7b7; font-size: .74rem;"><i class="bi bi-check2-all me-1"></i>Disetujui ${tglApproveFmt}</small>
                        </div>
                    </div>
                `;
            } else if (os.status === 'Pending') {
                approverSectionHtml = `
                    <div class="d-flex align-items-center gap-2.5 p-2 rounded-3" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35);">
                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(245, 158, 11, 0.25); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                        <div>
                            <span class="badge" style="background: rgba(245, 158, 11, 0.25); color: #fef08a; border: 1px solid rgba(245, 158, 11, 0.4); font-size: .78rem; font-weight: 700; padding: 0.25rem 0.5rem;">
                                Menunggu Otorisasi Atasan
                            </span>
                            <small class="d-block mt-1" style="color: #94a3b8; font-size: .72rem;">Siap untuk disetujui & diposting</small>
                        </div>
                    </div>
                `;
            } else {
                approverSectionHtml = `
                    <div class="d-flex align-items-center gap-2.5 p-2 rounded-3" style="background: rgba(100, 116, 139, 0.12); border: 1px solid rgba(100, 116, 139, 0.25);">
                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(100, 116, 139, 0.25); color: #cbd5e1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block text-white" style="font-size: .85rem;">Draft (Konsep)</span>
                            <small class="d-block" style="color: #94a3b8; font-size: .72rem;">Belum diajukan untuk persetujuan</small>
                        </div>
                    </div>
                `;
            }

            const html = `
            {{-- ── TOP CINEMATIC BENTO GRID ───────────────────────────────────── --}}
            <div class="row g-3 mb-4">
                {{-- Bento 1: Info Dokumen & Gudang --}}
                <div class="col-12 col-md-4">
                    <div class="opn-bento-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="opn-card-tag" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);">
                                <i class="bi bi-geo-alt-fill text-primary"></i> Lokasi & Tanggal
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="opn-label-muted">Gudang Penyimpanan</div>
                            <div class="opn-val-bright fs-6"><i class="bi bi-house-door-fill text-primary me-1.5"></i>${os.gudang ? os.gudang.nama_gudang : '-'}</div>
                            <small style="color: #cbd5e1; font-size: 0.78rem;">Cabang: <strong class="text-white">${os.gudang && os.gudang.cabang ? os.gudang.cabang.nama_cabang : '-'}</strong></small>
                        </div>
                        <div class="pt-2.5" style="border-top: 1px solid rgba(148, 163, 184, 0.15);">
                            <div class="opn-label-muted">Tanggal Dokumen</div>
                            <div class="fw-bold font-monospace" style="color: #f8fafc; font-size: 0.88rem;"><i class="bi bi-calendar3 me-1.5 text-primary"></i>${tglFmt}</div>
                        </div>
                    </div>
                </div>

                {{-- Bento 2: Otorisasi (Dibuat Oleh & Disetujui Oleh) --}}
                <div class="col-12 col-md-4">
                    <div class="opn-bento-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="opn-card-tag" style="background: rgba(14, 165, 233, 0.2); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.4);">
                                <i class="bi bi-shield-lock-fill text-info"></i> Audit & Otorisasi
                            </span>
                        </div>
                        <div class="mb-2.5">
                            <div class="opn-label-muted mb-1.5">Dibuat Oleh</div>
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.25); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="fw-bold text-white" style="font-size: 0.88rem;">${creatorName}</span>
                            </div>
                        </div>
                        <div class="pt-2" style="border-top: 1px solid rgba(148, 163, 184, 0.15);">
                            <div class="opn-label-muted mb-1.5">Disetujui Oleh</div>
                            ${approverSectionHtml}
                        </div>
                    </div>
                </div>

                {{-- Bento 3: Financial Hero Summary (Glowing Emerald) --}}
                <div class="col-12 col-md-4">
                    <div class="opn-hero-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="opn-card-tag" style="background: rgba(16, 185, 129, 0.25); color: #a7f3d0; border: 1px solid rgba(52, 211, 153, 0.4);">
                                <i class="bi bi-cash-stack"></i> Total Nilai Persediaan
                            </span>
                            <span class="badge font-monospace" style="background: rgba(16, 185, 129, 0.25); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); font-size: 0.72rem; padding: 0.25rem 0.5rem;">HPP Awal</span>
                        </div>
                        <div class="my-2">
                            <div class="fw-black font-monospace fs-3 lh-1" style="color: #4ade80 !important; letter-spacing: -0.02em; text-shadow: 0 0 20px rgba(74, 222, 128, 0.35);">
                                Rp ${totalNilaiDocument.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 pt-2" style="border-top: 1px solid rgba(52, 211, 153, 0.25);">
                            <span class="badge font-monospace" style="background: rgba(16, 185, 129, 0.25); color: #d1fae5; border: 1px solid rgba(52, 211, 153, 0.35); font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                <i class="bi bi-boxes me-1 text-success"></i>${(os.details || []).length} Item
                            </span>
                            <span class="badge font-monospace" style="background: rgba(16, 185, 129, 0.25); color: #d1fae5; border: 1px solid rgba(52, 211, 153, 0.35); font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                <i class="bi bi-calculator me-1 text-success"></i>${totalQtyDocument.toLocaleString('id-ID')} Total Unit
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Keterangan bar if available --}}
            ${os.keterangan ? `
            <div class="p-2.5 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(148, 163, 184, 0.2); border-left: 3px solid #3b82f6;">
                <i class="bi bi-chat-left-text-fill text-primary"></i>
                <small style="color: #94a3b8;" class="me-1">Keterangan:</small>
                <span class="fw-bold text-white small">${os.keterangan}</span>
            </div>` : ''}

            {{-- ── TABEL RINCIAN BARANG (CINEMATIC & STABLE SCROLL) ─────── --}}
            <div class="opn-table-wrapper mb-2">
                <div class="p-3 px-3.5 d-flex align-items-center justify-content-between" style="background: #1e293b; border-bottom: 1px solid rgba(148, 163, 184, 0.15);">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-list-stars"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-white" style="font-size: 0.92rem;">Rincian Barang & Saldo Fisik</h6>
                    </div>
                    <span class="badge font-monospace" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        ${(os.details || []).length} Item Terdaftar
                    </span>
                </div>
                <div class="opn-table-scroll">
                    <table class="table table-hover align-middle mb-0 text-nowrap" style="min-width: 680px; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="min-width: 260px;">Kode & Nama Barang</th>
                                <th style="width: 120px;" class="text-center">Jumlah Fisik</th>
                                <th style="width: 140px;" class="text-end">HPP Satuan</th>
                                <th style="width: 160px;" class="text-end">Subtotal Nilai</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows}</tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="2" class="text-end text-uppercase" style="color: #94a3b8; font-size: 0.82rem;">Total Keseluruhan:</td>
                                <td class="text-center font-monospace fw-black text-white">${totalQtyDocument.toLocaleString('id-ID')}</td>
                                <td class="text-end" style="color: #64748b;">-</td>
                                <td class="text-end font-monospace fw-black fs-6" style="color: #4ade80;">Rp ${totalNilaiDocument.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;
            
            $('#modalDetailOpeningBody').html(html);

            // Footer Left: Export & Jurnal buttons
            let footerLeftHtml = `
                <a href="/opening-stock/${os.id}/export" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm no-loader d-inline-flex align-items-center gap-1.5" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4);" download>
                    <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Excel (.xlsx)
                </a>
            `;
            if (res.jurnal) {
                footerLeftHtml += `
                    <button type="button" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4);" onclick="openModalJurnalOpening()">
                        <i class="bi bi-journal-bookmark-fill"></i> Jurnal Akuntansi (${res.jurnal.nomor_jurnal})
                    </button>
                `;
            }
            $('#detail_footer_left').html(footerLeftHtml);

            // Footer Right: Action buttons (Confirm / Submit / Close)
            let actionButtonsHtml = '';

            if (os.status === 'Draft') {
                actionButtonsHtml += `
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" onclick="modalDetailSubmitOpn(${os.id}, '${os.nomor_opening_stock}')">
                        <i class="bi bi-send-fill"></i> Ajukan Sekarang
                    </button>
                `;
            } else if (os.status === 'Pending') {
                if (res.can_approve) {
                    actionButtonsHtml += `
                        <button type="button" class="btn btn-success rounded-pill px-4 py-2 btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: 1px solid rgba(34, 197, 94, 0.5); box-shadow: 0 4px 15px rgba(22, 163, 74, 0.4);" onclick="modalDetailConfirmOpn(${os.id}, '${os.nomor_opening_stock}')">
                            <i class="bi bi-check-circle-fill fs-6"></i> Setujui & Konfirmasi
                        </button>
                    `;
                }
            }

            actionButtonsHtml += `
                <button type="button" class="btn rounded-pill px-4 py-2 btn-sm fw-semibold d-inline-flex align-items-center gap-1.5" style="background: rgba(148, 163, 184, 0.15); color: #e2e8f0; border: 1px solid rgba(148, 163, 184, 0.3);" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Tutup
                </button>
            `;

            $('#opn_action_buttons').html(actionButtonsHtml);
        },
        error: function () {
            $('#modalDetailOpeningBody').html('<div class="alert alert-danger rounded-4 p-4 text-center"><i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>Gagal memuat detail saldo awal. Silakan muat ulang halaman.</div>');
        }
    });
}

function modalDetailEditOpn(id) {
    const modalDetail = bootstrap.Modal.getInstance(document.getElementById('modalDetailOpening'));
    if (modalDetail) modalDetail.hide();
    editOpeningModal(id);
}

function modalDetailSubmitOpn(id, nomor) {
    const modalDetail = bootstrap.Modal.getInstance(document.getElementById('modalDetailOpening'));
    if (modalDetail) modalDetail.hide();
    submitOpeningDraft(id, nomor);
}

function modalDetailConfirmOpn(id, nomor) {
    const modalDetail = bootstrap.Modal.getInstance(document.getElementById('modalDetailOpening'));
    confirmOpening(id, nomor, modalDetail);
}

// ── Open Pop-up Jurnal Akuntansi ─────────────────────────────────────────
function openModalJurnalOpening() {
    const jurnal = window.currentOpeningJurnal;
    if (!jurnal) {
        Swal.fire('Info', 'Jurnal akuntansi belum terbentuk atau belum diposting.', 'info');
        return;
    }

    $('#modalJurnalOpeningNo').text(jurnal.nomor_jurnal || '');

    let rowsHtml = '';
    let totalDebitSum = 0;
    let totalKreditSum = 0;

    (jurnal.details || []).forEach(d => {
        const coaCode = d.account ? d.account.code : '-';
        const coaName = d.account ? d.account.name : '-';
        const deb = parseFloat(d.debit) || 0;
        const kre = parseFloat(d.kredit) || 0;

        totalDebitSum += deb;
        totalKreditSum += kre;

        const isDebit = deb > 0;
        const indentationClass = isDebit ? '' : 'ps-4 text-muted';
        const debitDisplay = deb > 0 ? `Rp ${deb.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '-';
        const kreditDisplay = kre > 0 ? `Rp ${kre.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '-';

        rowsHtml += `
            <tr>
                <td class="font-monospace small fw-bold text-nowrap" style="color: #60a5fa;">${coaCode}</td>
                <td>
                    <div class="${indentationClass}">
                        <span class="fw-semibold text-white">${coaName}</span>
                        ${d.keterangan ? `<small class="d-block" style="font-size:0.75rem; color: #94a3b8;">${d.keterangan}</small>` : ''}
                    </div>
                </td>
                <td class="text-end font-monospace small ${deb > 0 ? 'fw-bold text-success' : 'text-muted'}">${debitDisplay}</td>
                <td class="text-end font-monospace small ${kre > 0 ? 'fw-bold text-danger' : 'text-muted'}">${kreditDisplay}</td>
            </tr>
        `;
    });

    const isBalanced = Math.abs(totalDebitSum - totalKreditSum) < 0.01;
    const balanceBadge = isBalanced
        ? `<span class="badge rounded-pill px-3 py-1" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4);"><i class="bi bi-check-circle-fill me-1"></i>BALANCED</span>`
        : `<span class="badge rounded-pill px-3 py-1" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4);"><i class="bi bi-exclamation-triangle-fill me-1"></i>UNBALANCED</span>`;

    const bodyHtml = `
        <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.15) !important;">
            <div>
                <small class="d-block text-uppercase fw-bold" style="color: #94a3b8; font-size: 0.72rem;">Keterangan Jurnal</small>
                <span class="fw-bold text-white small">${jurnal.keterangan || '-'}</span>
            </div>
            <div>
                ${balanceBadge}
            </div>
        </div>
        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead class="sticky-top" style="z-index: 2; background: #0f172a;">
                    <tr>
                        <th style="width: 20%; color: #94a3b8;">Kode Akun</th>
                        <th style="width: 45%; color: #94a3b8;">Nama Akun / Keterangan</th>
                        <th style="width: 17.5%; color: #94a3b8;" class="text-end">DEBIT</th>
                        <th style="width: 17.5%; color: #94a3b8;" class="text-end">KREDIT</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
                <tfoot class="fw-bold border-top" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                    <tr>
                        <td colspan="2" class="text-end text-uppercase" style="color: #94a3b8;">Total:</td>
                        <td class="text-end font-monospace" style="color: #4ade80;">Rp ${totalDebitSum.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end font-monospace" style="color: #f87171;">Rp ${totalKreditSum.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    $('#modalJurnalOpeningBody').html(bodyHtml);
    const modalJurnal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalJurnalOpening'));
    modalJurnal.show();
}

// ── Confirm Saldo Awal ─────────────────────────────────────────────────
function confirmOpening(id, nomor, openModalInstance = null) {
    if (window.event && window.event.stopPropagation) {
        window.event.stopPropagation();
    }

    Swal.fire({
        title: 'Konfirmasi Saldo Awal?',
        html: `Dokumen <strong>${nomor}</strong> akan disetujui & diposting.<br><small class="text-muted">Stok fisik gudang akan diisi, HPP awal ditetapkan, dan Jurnal Akuntansi otomatis diposting.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Ya, Setujui & Posting!'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/opening-stock/${id}/confirm`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (openModalInstance) {
                    openModalInstance.hide();
                } else {
                    const modalEl = document.getElementById('modalDetailOpening');
                    const mInst = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                    if (mInst) mInst.hide();
                }
                Swal.fire({ icon:'success', title:'Berhasil!', text:res.message, timer:2000, showConfirmButton:false })
                    .then(() => {
                        if (typeof reloadTableOpening === 'function') reloadTableOpening();
                        location.reload();
                    });
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses konfirmasi.';
                Swal.fire({
                    title: 'Konfirmasi Gagal',
                    html: `<div class="text-start p-2 bg-danger bg-opacity-10 text-danger rounded border border-danger border-opacity-25 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${msg}</div>`,
                    icon: 'error',
                    confirmButtonText: 'Mengerti'
                });
            }
        });
    });
}

// ── Delete Saldo Awal ──────────────────────────────────────────────────
function hapusOpening(id, nomor) {
    if (window.event && window.event.stopPropagation) {
        window.event.stopPropagation();
    }

    Swal.fire({
        title: 'Hapus Saldo Awal?',
        html: `Dokumen <strong>${nomor}</strong> akan dihapus dari sistem.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Ya, Hapus!'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/opening-stock/${id}/delete`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                const modalEl = document.getElementById('modalDetailOpening');
                const mInst = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                if (mInst) mInst.hide();

                Swal.fire({ icon:'success', title:'Berhasil!', text:res.message, timer:2000, showConfirmButton:false })
                    .then(() => {
                        if (typeof reloadTableOpening === 'function') reloadTableOpening();
                        location.reload();
                    });
            },
            error: function (xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
}
</script>
@endpush
