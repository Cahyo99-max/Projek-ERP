{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detail Transfer                                      --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<style>
/* ─── Modern Cinematic Detail Modal Tokens ───────────────────────── */
#modalDetailTransfer .modal-content,
#modalJurnalTransfer .modal-content {
    background: var(--trf-modal-bg, #ffffff);
    color: var(--trf-text-main, #0f172a);
    border: 1px solid var(--trf-card-border, rgba(226, 232, 240, 0.8));
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

[data-bs-theme="dark"] #modalDetailTransfer .modal-content,
html[data-theme="dark"] #modalDetailTransfer .modal-content,
.dark-mode #modalDetailTransfer .modal-content,
[data-bs-theme="dark"] #modalJurnalTransfer .modal-content,
html[data-theme="dark"] #modalJurnalTransfer .modal-content,
.dark-mode #modalJurnalTransfer .modal-content {
    background: #0f172a !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

/* Detail Table Modernization */
.table-detail-trf {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.table-detail-trf thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 14px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap;
}

[data-bs-theme="dark"] .table-detail-trf thead th,
html[data-theme="dark"] .table-detail-trf thead th,
.dark-mode .table-detail-trf thead th {
    background-color: #1e293b !important;
    color: #94a3b8 !important;
    border-bottom: 1px solid #334155 !important;
}

.table-detail-trf tbody tr td {
    padding: 12px 14px !important;
    border-bottom: 1px solid #f1f5f9 !important;
    vertical-align: middle;
    font-size: 0.88rem;
    color: #0f172a;
}

[data-bs-theme="dark"] .table-detail-trf tbody tr td,
html[data-theme="dark"] .table-detail-trf tbody tr td,
.dark-mode .table-detail-trf tbody tr td {
    border-bottom: 1px solid #334155 !important;
    color: #f8fafc !important;
}

.table-detail-trf tbody tr:hover td {
    background-color: rgba(37, 99, 235, 0.04) !important;
}

[data-bs-theme="dark"] .table-detail-trf tbody tr:hover td,
html[data-theme="dark"] .table-detail-trf tbody tr:hover td,
.dark-mode .table-detail-trf tbody tr:hover td {
    background-color: rgba(255, 255, 255, 0.03) !important;
}

.table-detail-trf tfoot td {
    background-color: #f8fafc !important;
    padding: 14px 16px !important;
    border-top: 2px solid #e2e8f0 !important;
    font-weight: 700;
    color: #0f172a;
}

[data-bs-theme="dark"] .table-detail-trf tfoot td,
html[data-theme="dark"] .table-detail-trf tfoot td,
.dark-mode .table-detail-trf tfoot td {
    background-color: #1e293b !important;
    border-top: 2px solid #334155 !important;
    color: #f8fafc !important;
}

.text-subtotal-val {
    color: #2563eb !important;
}
[data-bs-theme="dark"] .text-subtotal-val,
html[data-theme="dark"] .text-subtotal-val,
.dark-mode .text-subtotal-val {
    color: #38bdf8 !important;
}

.text-hpp-val {
    color: #475569 !important;
}
[data-bs-theme="dark"] .text-hpp-val,
html[data-theme="dark"] .text-hpp-val,
.dark-mode .text-hpp-val {
    color: #cbd5e1 !important;
}

.route-banner-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
[data-bs-theme="dark"] .route-banner-card,
html[data-theme="dark"] .route-banner-card,
.dark-mode .route-banner-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
</style>

<div class="modal fade" id="modalDetailTransfer" tabindex="-1" aria-labelledby="modalDetailTransferLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom d-flex align-items-center justify-content-between p-3 p-md-4">
                <h5 class="modal-title fw-bold text-body mb-0" id="modalDetailTransferLabel">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Transfer Gudang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="modalDetailTransferBody">
                {{-- Loaded dynamically --}}
            </div>
            <div class="modal-footer border-top p-3" id="trf_modal_detail_footer">
                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                    <div id="trf_detail_footer_left"></div>
                    <div class="d-flex align-items-center gap-2" id="trf_detail_footer_right">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Jurnal Akuntansi Transfer                            --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalJurnalTransfer" tabindex="-1" aria-labelledby="modalJurnalTransferLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom p-3 p-md-4">
                <h5 class="modal-title fw-bold text-body mb-0" id="modalJurnalTransferLabel">
                    <i class="bi bi-journal-check text-primary me-2"></i>Jurnal Akuntansi Otomatis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="modalJurnalTransferBody">
                {{-- Loaded dynamically --}}
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Open Jurnal Modal ──────────────────────────────────────────────────
function showModalJurnalTransfer() {
    if (window.currentJurnalContent) {
        $('#modalJurnalTransferBody').html(window.currentJurnalContent);
        const jModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalJurnalTransfer'));
        jModal.show();
    }
}

// ── Show Detail Modal ──────────────────────────────────────────────────
function showDetailTransfer(id) {
    $('#modalDetailTransferBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    $('#trf_approval_action_panel').hide();
    window.currentJurnalContent = null;
    
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailTransfer'));
    modal.show();

    $.ajax({
        url: `/warehouse-transfer/${id}`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const trf = res.transfer;
            const canSeeHpp = res.can_see_hpp !== false;

            let statusBadge = '';
            if (trf.status === 'Draft') {
                statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-30 rounded-pill px-2.5 py-1 font-semibold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
            } else if (trf.status === 'Pending') {
                statusBadge = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-30 rounded-pill px-2.5 py-1 font-semibold"><i class="bi bi-hourglass-split me-1"></i>Pending Approval</span>';
            } else {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-30 rounded-pill px-2.5 py-1 font-semibold"><i class="bi bi-check-circle-fill me-1"></i>Confirmed</span>';
            }

            let detailRows = '';
            let grandTotalNilai = 0;

            (trf.details || []).forEach((d, i) => {
                const nama = d.barang ? d.barang.nama_barang : '-';
                const kode = d.barang ? d.barang.kode_barang : '-';
                const sat  = d.barang?.satuan ? d.barang.satuan.nama_satuan : '';
                const hpp  = parseFloat(d.hpp_satuan || 0);
                const sub  = parseFloat(d.subtotal_nilai || (parseFloat(d.jumlah) * hpp));
                grandTotalNilai += sub;

                const hppFmt = hpp > 0 ? `Rp ${hpp.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}` : 'Rp 0,00';
                const subFmt = sub > 0 ? `Rp ${sub.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}` : 'Rp 0,00';

                detailRows += `
                <tr>
                    <td class="text-center text-muted small fw-bold">${i+1}</td>
                    <td>
                        <span class="fw-bold d-block text-wrap" style="font-size:.9rem">${nama}</span>
                        <span class="badge-kode-barang mt-1" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">${kode}</span>
                    </td>
                    <td class="text-center fw-bold"><span class="badge bg-secondary bg-opacity-10 text-body border px-2.5 py-1 rounded-pill">${parseFloat(d.jumlah).toLocaleString('id-ID')} ${sat}</span></td>
                    ${canSeeHpp ? `<td class="text-end font-monospace text-hpp-val fw-semibold">${hppFmt}</td>` : ''}
                    ${canSeeHpp ? `<td class="text-end font-monospace fw-bold text-subtotal-val">${subFmt}</td>` : ''}
                </tr>`;
            });

            const grandTotalFmt = `Rp ${grandTotalNilai.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

            // Safe Date parsing
            let tglFmt = '-';
            if (trf.tanggal_transfer) {
                try {
                    let parsedDate = new Date(trf.tanggal_transfer);
                    if (!isNaN(parsedDate.getTime())) {
                        tglFmt = parsedDate.toLocaleDateString('id-ID', {day:'2-digit',month:'long',year:'numeric'});
                    }
                } catch (e) {
                    console.error("Date parse error:", e);
                }
            }

            // Safe relation resolving (handles both snake_case and camelCase serialization)
            const asalGudang   = trf.gudang_asal || trf.gudangAsal;
            const tujuanGudang = trf.gudang_tujuan || trf.gudangTujuan;

            const asalGudangName   = asalGudang ? asalGudang.nama_gudang : '-';
            const asalPtName       = (asalGudang && asalGudang.company) ? (asalGudang.company.nama_perusahaan || asalGudang.company.nama_company || '-') : '-';
            const asalCabangName   = (asalGudang && asalGudang.cabang) ? asalGudang.cabang.nama_cabang : '-';

            const tujuanGudangName = tujuanGudang ? tujuanGudang.nama_gudang : '-';
            const tujuanPtName     = (tujuanGudang && tujuanGudang.company) ? (tujuanGudang.company.nama_perusahaan || tujuanGudang.company.nama_company || '-') : '-';
            const tujuanCabangName = (tujuanGudang && tujuanGudang.cabang) ? tujuanGudang.cabang.nama_cabang : '-';

            const activeCompanyId = res.active_company_id;
            const activeBranchId  = res.active_branch_id;

            // Determine whether current viewer is in Receiver context (Gudang Tujuan) or Sender context (Gudang Asal)
            let isReceiverContext = false;
            if (tujuanGudang) {
                if (activeBranchId && (tujuanGudang.cabang_id == activeBranchId) && (asalGudang && asalGudang.cabang_id != activeBranchId)) {
                    isReceiverContext = true;
                } else if (activeCompanyId && (tujuanGudang.company_id == activeCompanyId) && (asalGudang && asalGudang.company_id != activeCompanyId)) {
                    isReceiverContext = true;
                }
            }

            let coaDetailHtml = '';
            const isInterPt = trf.jenis_transfer === 'Inter-PT' || (asalPtName !== '-' && tujuanPtName !== '-' && asalPtName !== tujuanPtName);
            if (isInterPt || trf.coa_debit || trf.coa_kredit || trf.coaDebit || trf.coaKredit) {
                const coaDebitObj = trf.coa_debit || trf.coaDebit;
                const coaKreditObj = trf.coa_kredit || trf.coaKredit;
                const coaDebitText = coaDebitObj ? `${coaDebitObj.code} - ${coaDebitObj.name}` : '-';
                const coaKreditText = coaKreditObj ? `${coaKreditObj.code} - ${coaKreditObj.name}` : '-';
                
                // Flip Debit and Kredit for Receiver context so it matches the Receiver's Jurnal
                const displayDebitText  = isReceiverContext ? coaKreditText : coaDebitText;
                const displayKreditText = isReceiverContext ? coaDebitText : coaKreditText;
                const debitLabel  = isReceiverContext ? 'Akun Debit (Persediaan Masuk PT Penerima)' : 'Akun Debit (Piutang Intercompany / Biaya Transfer)';
                const kreditLabel = isReceiverContext ? 'Akun Kredit (Hutang Intercompany PT Penerima)' : 'Akun Kredit (Persediaan Keluar PT Pengirim)';

                coaDetailHtml = `
                <div class="col-12 mt-2 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-20 rounded-3 text-body">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                        <span class="badge bg-warning text-dark font-semibold px-2.5 py-1 rounded-pill">
                            <i class="bi bi-arrow-left-right me-1"></i>Transfer Inter-PT (Antar Perusahaan)
                        </span>
                        <small class="text-secondary fw-bold">Mapping Jurnal Intercompany COA</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-secondary d-block" style="font-size:0.75rem;">${debitLabel}</small>
                            <span class="fw-bold text-body small"><i class="bi bi-journal-text me-1 text-primary"></i>${displayDebitText}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-secondary d-block" style="font-size:0.75rem;">${kreditLabel}</small>
                            <span class="fw-bold text-body small"><i class="bi bi-journal-text me-1 text-success"></i>${displayKreditText}</span>
                        </div>
                    </div>
                </div>`;
            } else {
                coaDetailHtml = `
                <div class="col-12 mt-1">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill">
                        <i class="bi bi-building-check me-1"></i>Transfer Intra-PT (Internal Perusahaan)
                    </span>
                </div>`;
            }

            let jurnalBannerHtml = '';
            const allJurnals = trf.jurnals && trf.jurnals.length > 0 ? trf.jurnals : (trf.jurnal ? [trf.jurnal] : []);
            
            if (allJurnals.length > 0) {
                // Determine whether current viewer is in Receiver context (Gudang Tujuan) or Sender context (Gudang Asal)
                let isReceiverContext = false;
                if (tujuanGudang) {
                    if (activeBranchId && (tujuanGudang.cabang_id == activeBranchId) && (asalGudang && asalGudang.cabang_id != activeBranchId)) {
                        isReceiverContext = true;
                    } else if (activeCompanyId && (tujuanGudang.company_id == activeCompanyId) && (asalGudang && asalGudang.company_id != activeCompanyId)) {
                        isReceiverContext = true;
                    }
                }

                // Pick ONLY 1 relevant Jurnal based on viewer context
                let targetJurnal = allJurnals.find(j => {
                    const isMasuk = j.keterangan && j.keterangan.toLowerCase().includes('masuk');
                    return isReceiverContext ? isMasuk : !isMasuk;
                });

                // Fallback to first journal if specific match not found
                if (!targetJurnal) {
                    targetJurnal = allJurnals[0];
                }

                let jRows = '';
                let totalDebit = 0;
                let totalKredit = 0;

                (targetJurnal.details || []).forEach((jd, idx) => {
                    const accName = jd.account ? `${jd.account.code} - ${jd.account.name}` : '-';
                    const debVal  = parseFloat(jd.debit) || 0;
                    const kreVal  = parseFloat(jd.kredit) || 0;
                    totalDebit  += debVal;
                    totalKredit += kreVal;

                    const deb = debVal > 0 ? `Rp ${debVal.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}` : '-';
                    const kre = kreVal > 0 ? `Rp ${kreVal.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}` : '-';

                    jRows += `
                    <tr>
                        <td class="text-center text-muted small">${idx + 1}</td>
                        <td class="fw-semibold">${accName}</td>
                        <td class="text-end text-success fw-bold">${deb}</td>
                        <td class="text-end text-danger fw-bold">${kre}</td>
                    </tr>`;
                });

                const totDebFmt = `Rp ${totalDebit.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                const totKreFmt = `Rp ${totalKredit.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                const isReceiverJurnal = targetJurnal.keterangan && targetJurnal.keterangan.toLowerCase().includes('masuk');
                const badgeClass = isReceiverJurnal ? 'bg-success' : 'bg-primary';
                const iconClass  = isReceiverJurnal ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up-right';
                const titleText  = isReceiverJurnal ? 'Jurnal PT Penerima (Gudang Tujuan)' : 'Jurnal PT Pengirim (Gudang Asal)';

                let itemsSummaryList = [];
                (trf.details || []).forEach(d => {
                    const bNama = d.barang ? d.barang.nama_barang : 'Barang';
                    const bSat  = d.barang?.satuan ? d.barang.satuan.nama_satuan : '';
                    const bQty  = parseFloat(d.jumlah).toLocaleString('id-ID');
                    itemsSummaryList.push(`${bNama} (${bQty} ${bSat})`);
                });
                const itemsSummaryText = itemsSummaryList.length > 0 ? itemsSummaryList.join(', ') : '-';

                let tglJurnalStr = targetJurnal.tanggal_jurnal_fmt || targetJurnal.tanggal_jurnal || '-';
                let tglApprovalStr = targetJurnal.tanggal_approval_fmt || targetJurnal.created_at || '-';

                window.currentJurnalContent = `
                <div class="card border rounded-3 shadow-sm overflow-hidden mb-0">
                    <div class="p-3 bg-body-tertiary border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge ${badgeClass} rounded-pill px-3 py-1.5 fw-bold">
                                <i class="bi ${iconClass} me-1.5"></i>${titleText}
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-3 py-1.5 fw-bold">
                                <i class="bi bi-check-all me-1"></i>Terposting
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-end flex-wrap">
                            <div>
                                <small class="text-secondary d-block" style="font-size:0.72rem;"><i class="bi bi-calendar-check me-1"></i>Tgl Jurnal / Approval</small>
                                <span class="fw-bold text-body" style="font-size:0.85rem;">${tglJurnalStr} <small class="text-muted">(${tglApprovalStr})</small></span>
                            </div>
                            <div class="ps-2 border-start">
                                <small class="text-secondary d-block" style="font-size:0.72rem;">Nomor Jurnal</small>
                                <code class="fw-bold text-primary" style="font-size:0.92rem;">${targetJurnal.nomor_jurnal || '-'}</code>
                            </div>
                        </div>
                    </div>

                    <div class="p-3">
                        <div class="row g-2 p-3 mb-3 rounded-3 bg-body-tertiary border text-body small">
                            <div class="col-md-6">
                                <small class="text-secondary d-block mb-1" style="font-size:0.75rem;">Entitas Pengirim (Asal)</small>
                                <strong class="d-block text-truncate"><i class="bi bi-building me-1.5 text-primary"></i>${asalPtName}</strong>
                                <span class="text-secondary" style="font-size:0.8rem;"><i class="bi bi-house-door me-1"></i>${asalGudangName}</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-secondary d-block mb-1" style="font-size:0.75rem;">Entitas Penerima (Tujuan)</small>
                                <strong class="d-block text-truncate"><i class="bi bi-building me-1.5 text-success"></i>${tujuanPtName}</strong>
                                <span class="text-secondary" style="font-size:0.8rem;"><i class="bi bi-house-door me-1"></i>${tujuanGudangName}</span>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <small class="text-secondary d-block mb-1" style="font-size:0.75rem;">Item Barang & Quantity Transaksi</small>
                                <span class="fw-semibold text-primary"><i class="bi bi-box-seam me-1.5"></i>${itemsSummaryText}</span>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <small class="text-secondary d-block mb-1" style="font-size:0.75rem;">Deskripsi Transaksi Jurnal</small>
                                <span class="fw-medium">${targetJurnal.keterangan || '-'}</span>
                            </div>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="bg-body-tertiary">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>Kode & Nama Akun (COA)</th>
                                        <th class="text-end" width="28%">Debit</th>
                                        <th class="text-end" width="28%">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>${jRows}</tbody>
                                <tfoot class="bg-body-tertiary border-top">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">TOTAL BALANCE:</td>
                                        <td class="text-end text-success fw-bold">${totDebFmt}</td>
                                        <td class="text-end text-danger fw-bold">${totKreFmt}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>`;

                if (res.can_view_jurnal) {
                    jurnalBannerHtml = `
                    <hr class="my-3 opacity-25">
                    <div class="p-3 rounded-3 border bg-body-tertiary d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="fw-bold d-block text-body"><i class="bi bi-journal-check me-1.5 text-primary"></i>Jurnal Akuntansi Otomatis</span>
                            <small class="text-secondary">Jurnal pembukuan double-entry telah terposting otomatis untuk transaksi ini.</small>
                        </div>
                        <button type="button" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-bold btn-sm shadow-sm" onclick="showModalJurnalTransfer()">
                            <i class="bi bi-journal-text me-1.5"></i>Lihat Jurnal Akuntansi
                        </button>
                    </div>`;
                } else {
                    jurnalBannerHtml = `
                    <hr class="my-3 opacity-25">
                    <div class="p-3 rounded-3 border bg-body-tertiary d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="fw-bold d-block text-body"><i class="bi bi-lock-fill me-1.5 text-secondary"></i>Jurnal Akuntansi Otomatis</span>
                            <small class="text-secondary">Jurnal pembukuan telah terposting. Memerlukan hak akses Accounting / Finance untuk melihat voucher jurnal.</small>
                        </div>
                        <button type="button" class="btn btn-secondary rounded-pill px-3.5 py-1.5 fw-bold btn-sm disabled opacity-50" disabled title="Akses Dibatasi">
                            <i class="bi bi-shield-lock me-1.5"></i>Akses Dibatasi
                        </button>
                    </div>`;
                }
            }

            const tableTitleText = canSeeHpp ? 'Rincian Barang & HPP Transfer' : 'Rincian Barang Transfer';
            const tableHeaderCols = canSeeHpp ? `
                <th width="5%" class="text-center">No</th>
                <th>Nama & Kode Barang</th>
                <th class="text-center" width="18%">Jumlah (Qty)</th>
                <th class="text-end" width="22%">HPP Satuan</th>
                <th class="text-end" width="25%">Subtotal Nilai</th>
            ` : `
                <th width="5%" class="text-center">No</th>
                <th>Nama & Kode Barang</th>
                <th class="text-center" width="25%">Jumlah (Qty)</th>
            `;

            const tableFooterHtml = canSeeHpp ? `
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">TOTAL NILAI MUTASI:</td>
                        <td class="text-end font-monospace fw-bold text-subtotal-val fs-6">${grandTotalFmt}</td>
                    </tr>
                </tfoot>
            ` : '';

            let approverHtml = '';
            if (trf.status === 'Confirmed') {
                const approverName = (trf.updater ? (trf.updater.nama_lengkap || trf.updater.name) : null) 
                    || (trf.creator ? (trf.creator.nama_lengkap || trf.creator.name) : null) 
                    || '-';
                
                let approveDateStr = '';
                if (trf.updated_at) {
                    try {
                        let d = new Date(trf.updated_at);
                        if (!isNaN(d.getTime())) {
                            approveDateStr = d.toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) + ' WIB';
                        }
                    } catch(e){}
                }

                approverHtml = `
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-success" style="font-size:.88rem;"><i class="bi bi-patch-check-fill me-1"></i>${approverName}</span>
                        ${approveDateStr ? `<small class="text-muted font-monospace" style="font-size:.72rem;">${approveDateStr}</small>` : ''}
                    </div>`;
            } else if (trf.status === 'Pending') {
                approverHtml = `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-30 rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Menunggu Persetujuan</span>`;
            } else {
                approverHtml = `<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-pencil-square me-1"></i>Draf (Belum Diajukan)</span>`;
            }

            const html = `
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Nomor Transfer</small>
                    <span class="fw-bold text-primary font-monospace">${trf.nomor_transfer}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Tanggal</small>
                    <span class="fw-semibold">${tglFmt}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Status</small>
                    <div>${statusBadge}</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Tipe Rute</small>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill">${trf.jenis_transfer || 'Intra-PT'}</span>
                </div>
                <div class="col-12 d-flex flex-column flex-md-row align-items-center justify-content-between p-3 route-banner-card rounded-3 my-2 gap-3">
                    <div class="text-center text-md-start px-3">
                        <small class="text-primary fw-bold d-block text-uppercase mb-1" style="font-size:0.7rem; letter-spacing:0.5px;">Gudang Asal (Keluar)</small>
                        <div class="fw-bold text-body" style="font-size: 1.05rem;"><i class="bi bi-house-door-fill text-primary me-1"></i>${asalGudangName}</div>
                        <small class="text-muted d-block" style="font-size: 0.8rem;"><i class="bi bi-building me-1 text-primary"></i><strong>PT:</strong> ${asalPtName}</small>
                        <small class="text-muted d-block" style="font-size: 0.8rem;"><i class="bi bi-geo-alt me-1 text-secondary"></i><strong>Cabang:</strong> ${asalCabangName}</small>
                    </div>
                    <i class="bi bi-arrow-right-circle-fill text-primary fs-2 shadow-sm rounded-circle"></i>
                    <div class="text-center text-md-start px-3">
                        <small class="text-success fw-bold d-block text-uppercase mb-1" style="font-size:0.7rem; letter-spacing:0.5px;">Gudang Tujuan (Masuk)</small>
                        <div class="fw-bold text-body" style="font-size: 1.05rem;"><i class="bi bi-house-door-fill text-success me-1"></i>${tujuanGudangName}</div>
                        <small class="text-muted d-block" style="font-size: 0.8rem;"><i class="bi bi-building me-1 text-primary"></i><strong>PT:</strong> ${tujuanPtName}</small>
                        <small class="text-muted d-block" style="font-size: 0.8rem;"><i class="bi bi-geo-alt me-1 text-secondary"></i><strong>Cabang:</strong> ${tujuanCabangName}</small>
                    </div>
                </div>
                ${coaDetailHtml}
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block mb-1">Dibuat Oleh</small>
                    <span class="fw-semibold text-body"><i class="bi bi-person me-1 text-primary"></i>${trf.creator ? (trf.creator.nama_lengkap || '-') : '-'}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block mb-1">Disetujui Oleh</small>
                    <div>${approverHtml}</div>
                </div>
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block mb-1">Keterangan</small>
                    <span class="text-body">${trf.keterangan || '<em class="text-muted">-</em>'}</span>
                </div>
            </div>
            <hr class="my-3 opacity-25">
            <h6 class="fw-bold mb-3 text-body"><i class="bi bi-boxes me-2 text-primary"></i>${tableTitleText}</h6>
            <div class="table-responsive rounded-3 border overflow-hidden">
                <table class="table table-detail-trf align-middle mb-0">
                    <thead>
                        <tr>
                            ${tableHeaderCols}
                        </tr>
                    </thead>
                    <tbody>${detailRows}</tbody>
                    ${tableFooterHtml}
                </table>
            </div>
            ${jurnalBannerHtml}`;

            let statusBannerHtml = '';
            if (trf.status === 'Draft') {
                statusBannerHtml = `
                <div class="alert bg-warning bg-opacity-10 border border-warning border-opacity-30 rounded-3 p-3 my-3 text-body d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width:40px; height:40px;">
                            <i class="bi bi-file-earmark-text fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-body mb-0.5" style="font-size:0.9rem;">Status: Konsep Draf (Belum Diajukan)</h6>
                            <small class="text-secondary d-block" style="font-size:0.78rem;">Dokumen ini masih berstatus Draf. Klik <strong>Ajukan Transfer</strong> untuk memasukkan dokumen ke antrean persetujuan.</small>
                        </div>
                    </div>
                    ${(res.is_creator || res.is_super_admin || res.can_edit) ? `
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold text-nowrap shadow-sm" onclick="submitDraftTransfer(${trf.id}, '${escapeJsStr(trf.nomor_transfer)}')">
                        <i class="bi bi-send-check me-1"></i> Ajukan Transfer
                    </button>` : ''}
                </div>`;
            }

            $('#modalDetailTransferBody').html(statusBannerHtml + html);

            const isCreator = res.is_creator;
            const canEdit = res.can_edit;
            const canApprove = res.can_approve;

            let footerRightHtml = '<button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>';

            if (canEdit && trf.status !== 'Confirmed') {
                footerRightHtml = `
                    <button type="button" class="btn btn-outline-warning rounded-pill px-3.5 fw-semibold" onclick="bootstrap.Modal.getInstance(document.getElementById('modalDetailTransfer')).hide(); editTransfer(${trf.id})">
                        <i class="bi bi-pencil-square me-1"></i>Edit ${trf.status === 'Pending' ? 'Pengajuan' : 'Draft'}
                    </button>
                ` + footerRightHtml;
            }

            if (trf.status === 'Draft' && (isCreator || res.is_super_admin || canEdit)) {
                footerRightHtml = `
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="submitDraftTransfer(${trf.id}, '${escapeJsStr(trf.nomor_transfer)}')">
                        <i class="bi bi-send-check me-1"></i> Ajukan Transfer Sekarang
                    </button>
                ` + footerRightHtml;
            } else if (trf.status === 'Pending' && canApprove) {
                footerRightHtml = `
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm btn-confirm-action" onclick="confirmTransfer(${trf.id}, '${escapeJsStr(trf.nomor_transfer)}')">
                        <i class="bi bi-check-lg me-1"></i> Setujui & Konfirmasi
                    </button>
                ` + footerRightHtml;
            }

            $('#trf_detail_footer_right').html(footerRightHtml);
        },
        error: function () {
            $('#modalDetailTransferBody').html('<div class="alert alert-danger">Gagal memuat detail transfer gudang.</div>');
        }
    });
}

function escapeJsStr(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ── Submit Draft Transfer Handler ──────────────────────────────────────
function submitDraftTransfer(id, nomor) {
    const modalEl = document.getElementById('modalDetailTransfer');
    const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

    Swal.fire({
        title: 'Ajukan Transfer Gudang',
        text: `Apakah Anda yakin ingin mengajukan Transfer No. "${nomor}" untuk mendapatkan persetujuan (Pending Approval)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan Sekarang!',
        cancelButtonText: 'Batal',
        target: modalEl || document.body
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim Pengajuan...',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                target: modalEl || document.body
            });

            $.ajax({
                url: `/warehouse-transfer/${id}/submit`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (modalInstance) modalInstance.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diajukan!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => reloadTableTransfer());
                },
                error: function (xhr) {
                    const res = xhr.responseJSON ?? {};
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengajukan',
                        text: res.message || 'Terjadi kesalahan server.',
                        target: modalEl || document.body
                    });
                }
            });
        }
    });
}

// ── Confirm Transfer Action Handler ────────────────────────────────────
function confirmTransfer(id, nomor) {
    const modalEl = document.getElementById('modalDetailTransfer');
    const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

    Swal.fire({
        title: 'Konfirmasi Transfer Gudang',
        text: `Apakah Anda yakin ingin menyetujui dan memposting Transfer No. "${nomor}"? Stok di gudang asal akan dipotong, stok gudang tujuan akan bertambah, dan jurnal akuntansi otomatis diposting.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Setujui & Posting!',
        cancelButtonText: 'Batal',
        target: modalEl || document.body
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Posting...',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                target: modalEl || document.body
            });

            $.ajax({
                url: `/warehouse-transfer/${id}/confirm`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (modalInstance) modalInstance.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => reloadTableTransfer());
                },
                error: function (xhr) {
                    const res = xhr.responseJSON ?? {};
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Posting',
                        text: res.message || 'Terjadi kesalahan server.',
                        target: modalEl || document.body
                    });
                }
            });
        }
    });
}
</script>
@endpush
