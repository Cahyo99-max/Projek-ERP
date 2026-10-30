{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detail Barang Keluar (Goods Issue)                  --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailGI" tabindex="-1" aria-labelledby="modalDetailGILabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- Professional Header --}}
            <div class="modal-header border-0 py-3.5 px-3 px-md-4 text-white d-flex align-items-center justify-content-between" 
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45);">
                        <i class="bi bi-box-arrow-right text-white fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0 text-white" id="modalDetailGILabel" style="font-size: 1.15rem; letter-spacing: -0.01em;">
                                Detail Pengeluaran Barang
                            </h5>
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30 rounded-pill px-2.5 py-0.5" style="font-size: 0.68rem; font-weight: 700;">GI</span>
                        </div>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 0.8rem;">Informasi lengkap transaksi & mutasi persediaan barang</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 transition" 
                        style="background: rgba(255,255,255,0.08); width: 34px; height: 34px;" 
                        data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg text-white fs-6"></i>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4 detail-gi-body" id="modalDetailGIBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="text-muted mt-2 small mb-0">Memuat data pengeluaran barang...</p>
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-3 px-md-4 d-flex justify-content-between align-items-center" style="background-color: var(--bg-color, #f8fafc);">
                <div class="small text-muted d-none d-sm-block">
                    <i class="bi bi-info-circle me-1"></i>Tekan ESC atau tombol tutup untuk keluar
                </div>
                <button type="button" class="btn btn-secondary rounded-pill px-4 py-1.5 fw-semibold shadow-xs ms-auto" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Pop-up Jurnal Akuntansi Otomatis                    --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalJurnalGI" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 92vw; width: 92vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success bg-gradient text-white py-3 px-3 px-md-4 border-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 bg-white text-success shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px;">
                        <i class="bi bi-journal-bookmark-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-md-5" id="modalJurnalGILabel">
                            Jurnal Akuntansi Otomatis
                        </h5>
                        <small class="text-white-50 font-monospace" id="modalJurnalGINo" style="font-size:.78rem;"></small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 transition" 
                        style="background: rgba(255,255,255,0.15); width: 34px; height: 34px;" 
                        data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg text-white fs-6"></i>
                </button>
            </div>
            <div class="modal-body p-0" id="modalJurnalGIBody">
                {{-- Journal Table injected here via JS --}}
            </div>
            <div class="modal-footer py-2.5 px-3 px-md-4 d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4 py-1.5 fw-semibold shadow-xs" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.currentGIData = null;

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJsStr(str) {
    if (!str) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function formatGIQty(val) {
    if (val === null || val === undefined || isNaN(val)) return '0';
    const num = parseFloat(val);
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function getGIUserName(user) {
    if (!user) return null;
    if (user.karyawan && user.karyawan.nama_lengkap) return user.karyawan.nama_lengkap;
    if (user.karyawan && user.karyawan.nama) return user.karyawan.nama;
    if (user.nama_lengkap) return user.nama_lengkap;
    if (user.name) return user.name;
    if (user.username) return user.username;
    return null;
}

// Show Detail Modal for Goods Issue
function showDetailGI(id) {
    $('#modalDetailGIBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-danger" role="status"></div>
            <p class="text-muted mt-2 small mb-0">Memuat data pengeluaran barang...</p>
        </div>
    `);
    const modal = new bootstrap.Modal(document.getElementById('modalDetailGI'));
    modal.show();

    $.ajax({
        url: `/goods-issue/${id}`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const gi = res.gi;
            const isSuperAdmin = res.is_super_admin || false;
            window.currentGIData = gi;

            let statusBadge = '';
            if (gi.status === 'Draft') {
                statusBadge = `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-30 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>`;
            } else if (gi.status === 'Pending') {
                statusBadge = `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-30 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>`;
            } else {
                statusBadge = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-30 rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-check-circle me-1"></i>Confirmed</span>`;
            }

            let detailRows = '';
            let totalQty = 0;

            (gi.details || []).forEach((d, i) => {
                const b = d.barang;
                const nama = b ? b.nama_barang : 'Barang ID #' + d.barang_id;
                const kode = b ? b.kode_barang : '-';
                const sat  = b && b.satuan ? b.satuan.nama_satuan : '-';
                const fotoPath = b && b.primary_foto ? `/storage/${b.primary_foto.file_path || b.primary_foto.foto_path}` : (b && b.primaryFoto ? `/storage/${b.primaryFoto.file_path || b.primaryFoto.foto_path}` : null);
                const rawQty = parseFloat(d.jumlah || 0);
                totalQty += rawQty;

                const imgHtml = fotoPath
                    ? `<img src="${fotoPath}" class="detail-gi-thumb border shadow-xs" title="Klik untuk perbesar foto" onclick="openPreviewFoto('${fotoPath}', '${escapeJsStr(kode)} - ${escapeJsStr(nama)}')">`
                    : `<div class="rounded-3 bg-secondary bg-opacity-10 text-muted d-inline-flex align-items-center justify-content-center border" style="width:44px; height:44px;"><i class="bi bi-image fs-5"></i></div>`;

                let snHtml = '';
                if (d.serial_numbers && Array.isArray(d.serial_numbers) && d.serial_numbers.length > 0) {
                    snHtml = `
                        <div class="mt-2 pt-1 border-top border-secondary border-opacity-10">
                            <div class="small fw-semibold text-danger mb-1 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                <i class="bi bi-upc-scan"></i> Serial Numbers (${d.serial_numbers.length} unit):
                            </div>
                            <div class="d-flex flex-wrap gap-1" style="max-height: 90px; overflow-y: auto;">
                                ${d.serial_numbers.map(sn => `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 font-monospace px-2 py-0.5" style="font-size: 0.7rem;">${escapeHtml(sn)}</span>`).join('')}
                            </div>
                        </div>
                    `;
                }

                detailRows += `
                <tr>
                    <td class="text-muted small text-center" style="width: 50px; min-width: 50px;">${i+1}</td>
                    <td class="text-center" style="width: 70px; min-width: 70px;">${imgHtml}</td>
                    <td style="min-width: 280px;">
                        <span class="fw-bold d-block text-body" style="font-size:.9rem">${escapeHtml(nama)}</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace mt-1" style="font-size:.72rem">${escapeHtml(kode)}</span>
                        ${snHtml}
                    </td>
                    <td class="text-end pe-3" style="width: 150px; min-width: 140px;">
                        <span class="fw-bold fs-6 text-danger">${formatGIQty(rawQty)}</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace ms-1" style="font-size:.72rem">${escapeHtml(sat)}</span>
                    </td>
                </tr>`;
            });

            const companyName = gi.company ? gi.company.nama_perusahaan : (gi.gudang?.company?.nama_perusahaan || '-');
            const branchName  = gi.branch ? gi.branch.nama_cabang : (gi.gudang?.cabang?.nama_cabang || 'Pusat');
            const creatorName = getGIUserName(gi.creator) || '-';
            const tglFmt = new Date(gi.tanggal_gi).toLocaleDateString('id-ID', {day:'2-digit',month:'long',year:'numeric'});

            let approverHtml = '';
            if (gi.status === 'Confirmed') {
                const approverName = getGIUserName(gi.approver) || getGIUserName(gi.updater) || getGIUserName(gi.creator) || '-';
                const approveDate = gi.approved_at 
                    ? new Date(gi.approved_at).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) + ' WIB'
                    : (gi.updated_at ? new Date(gi.updated_at).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) + ' WIB' : '-');

                approverHtml = `
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-success" style="font-size:.86rem"><i class="bi bi-patch-check-fill me-1"></i>${escapeHtml(approverName)}</span>
                        <small class="text-muted font-monospace" style="font-size:.74rem;">${escapeHtml(approveDate)}</small>
                    </div>`;
            } else if (gi.status === 'Pending') {
                approverHtml = `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-30 rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Menunggu Persetujuan</span>`;
            } else {
                approverHtml = `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-30 rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-pencil-square me-1"></i>Draf (Belum Diajukan)</span>`;
            }

            const canSeeJournal = res.can_see_journal !== undefined ? res.can_see_journal : true;
            const btnJurnalHtml = (canSeeJournal && gi.jurnal && gi.jurnal.details && gi.jurnal.details.length > 0)
                ? `<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="openModalJurnalGI()">
                        <i class="bi bi-journal-bookmark-fill me-1"></i>Lihat Jurnal Akuntansi
                   </button>`
                : '';

            const isCreator = res.is_creator || isSuperAdmin;
            const canApprove = res.can_approve || isSuperAdmin;
            
            // Tombol Cetak PDF
            const btnCetakPdfHtml = `<a href="/goods-issue/${gi.id}/print" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
                    <i class="bi bi-printer me-1"></i>Cetak PDF
               </a>`;

            // Tombol Ajukan (jika Draft dan adalah pembuat)
            const btnSubmitHtml = (gi.status === 'Draft' && isCreator)
                ? `<button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="submitFromDetail(${gi.id}, '${escapeJsStr(gi.nomor_gi)}')">
                        <i class="bi bi-send me-1"></i>Ajukan Pengeluaran
                   </button>`
                : '';

            // Tombol Setujui (jika Pending dan berhak approve)
            const btnApproveHtml = (gi.status === 'Pending' && canApprove)
                ? `<button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="confirmFromDetail(${gi.id}, '${escapeJsStr(gi.nomor_gi)}')">
                        <i class="bi bi-check2-circle me-1"></i>Setujui & Konfirmasi
                    </button>`
                : '';

            // Tombol Edit (Draf hanya pembuatnya, Pending bisa diedit, Confirmed hanya Super Admin)
            const isEditable = (gi.status === 'Draft' ? isCreator : (gi.status === 'Pending' || isSuperAdmin));
            const btnEditHtml = isEditable
                ? `<button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold shadow-xs text-dark" onclick="bootstrap.Modal.getInstance(document.getElementById('modalDetailGI')).hide(); openModalEditGI(${gi.id})">
                        <i class="bi bi-pencil-square me-1"></i>Edit ${gi.status === 'Confirmed' ? '(Super Admin)' : (gi.status === 'Pending' ? 'Pengajuan' : 'Draf')}
                   </button>`
                : '';

            let statusBannerHtml = '';
            if (gi.status === 'Draft') {
                statusBannerHtml = `
                <div class="card border-0 shadow-sm detail-gi-card mt-4 bg-warning bg-opacity-10 border border-warning border-opacity-25">
                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width:46px; height:46px;">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Status: Dokumen Draf Pribadi (Konsep)</h6>
                                <p class="text-muted small mb-0">Dokumen draf ini bersifat privat dan hanya dapat diakses oleh Anda. Klik tombol <b>Ajukan Pengeluaran</b> untuk mengirim permohonan persetujuan & notifikasi WhatsApp ke atasan.</p>
                            </div>
                        </div>
                        ${isCreator ? `
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm text-nowrap w-100 w-md-auto" onclick="submitFromDetail(${gi.id}, '${escapeJsStr(gi.nomor_gi)}')">
                            <i class="bi bi-send me-1"></i> Ajukan Pengeluaran
                        </button>` : ''}
                    </div>
                </div>`;
            } else if (gi.status === 'Pending') {
                statusBannerHtml = `
                <div class="card border-0 shadow-sm approval-gi-banner mt-4">
                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width:46px; height:46px;">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-info mb-1">Menunggu Persetujuan (Pending Approval)</h6>
                                <p class="text-muted small mb-0">Pengajuan ini sedang menunggu verifikasi atasan. Stok gudang akan dipotong dan jurnal akuntansi otomatis diterbitkan setelah disetujui.</p>
                            </div>
                        </div>
                        ${canApprove ? `
                        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-end">
                            <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-2 fw-semibold text-dark text-nowrap" onclick="rejectFromDetail(${gi.id}, '${escapeJsStr(gi.nomor_gi)}')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Kembalikan ke Draf
                            </button>
                            <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm text-nowrap" onclick="confirmFromDetail(${gi.id}, '${escapeJsStr(gi.nomor_gi)}')">
                                <i class="bi bi-check-circle me-1"></i> Setujui & Potong Stok
                            </button>
                        </div>` : ''}
                    </div>
                </div>`;
            }

            const html = `
            {{-- Top Summary Banner --}}
            <div class="card border-0 shadow-sm detail-gi-card mb-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 pb-3 border-bottom">
                        <div>
                            <span class="text-muted d-block small mb-1">Nomor Pengeluaran (Goods Issue)</span>
                            <h4 class="fw-bold font-monospace mb-0 text-danger">${escapeHtml(gi.nomor_gi)}</h4>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            ${btnCetakPdfHtml}
                            ${btnSubmitHtml}
                            ${btnApproveHtml}
                            ${btnEditHtml}
                            ${btnJurnalHtml}
                            ${statusBadge}
                        </div>
                    </div>

                    {{-- Metadata Grid Cards --}}
                    <div class="row g-3 g-md-3.5 mt-2">
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Perusahaan (PT)</small>
                                </div>
                                <div class="fw-bold text-primary text-truncate-2" style="font-size:.88rem">${escapeHtml(companyName)}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Cabang</small>
                                </div>
                                <div class="fw-bold text-body text-truncate-2" style="font-size:.88rem">${escapeHtml(branchName)}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-warning bg-opacity-15 text-warning">
                                        <i class="bi bi-house-door"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Gudang Asal</small>
                                </div>
                                <div class="fw-bold text-body text-truncate-2" style="font-size:.88rem">${escapeHtml(gi.gudang ? gi.gudang.nama_gudang : '-')}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-calendar3"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Tanggal</small>
                                </div>
                                <div class="fw-bold text-body font-monospace" style="font-size:.86rem">${escapeHtml(tglFmt)}</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Tujuan / Penerima</small>
                                </div>
                                <div class="fw-bold text-body text-truncate-2" style="font-size:.88rem">${escapeHtml(gi.tujuan)}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-tag"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Tipe Pengeluaran</small>
                                </div>
                                <div>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fw-bold" style="font-size:.8rem">${escapeHtml(gi.tipe_pengeluaran)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-dark bg-opacity-10 text-dark dark-invert">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Dibuat Oleh</small>
                                </div>
                                <div class="fw-bold text-body text-truncate-2" style="font-size:.88rem">${escapeHtml(creatorName)}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Disetujui Oleh</small>
                                </div>
                                <div>${approverHtml}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="detail-gi-meta-box">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <div class="meta-icon-bubble bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-chat-left-text"></i>
                                    </div>
                                    <small class="text-muted fw-semibold">Keterangan / Catatan</small>
                                </div>
                                <div class="text-body" style="font-size: .88rem; line-height: 1.5;">${gi.keterangan ? escapeHtml(gi.keterangan) : '<em class="text-muted">- Tidak ada catatan tambahan -</em>'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Detail Barang --}}
            <div class="card border-0 shadow-sm detail-gi-card overflow-hidden mb-4">
                <div class="card-header bg-transparent border-0 py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2 text-body">
                        <i class="bi bi-boxes text-danger fs-5"></i>Rincian Barang yang Dikeluarkan
                    </h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1.5 fw-bold font-monospace">
                        ${(gi.details || []).length} Item | Total: ${formatGIQty(totalQty)}
                    </span>
                </div>

                {{-- Mobile Swipe Hint Banner --}}
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-1.5 mx-3 mb-2 rounded-3 text-danger shadow-2xs" style="background: rgba(239, 68, 68, 0.08); font-size: 0.775rem; border: 1px dashed rgba(239, 68, 68, 0.25);">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Tabel rincian barang bisa digeser ke samping</span>
                    </div>
                    <span class="badge bg-danger bg-opacity-15 text-danger rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive detail-gi-table-wrapper">
                        <table class="table table-hover detail-gi-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px; min-width: 50px;" class="text-center">No</th>
                                    <th style="width: 70px; min-width: 70px;" class="text-center">Foto</th>
                                    <th style="min-width: 280px;">Nama Barang &amp; Kode</th>
                                    <th style="width: 150px; min-width: 140px;" class="text-end pe-3">Jumlah Keluar</th>
                                </tr>
                            </thead>
                            <tbody>${detailRows}</tbody>
                        </table>
                    </div>
                </div>
            </div>

            ${statusBannerHtml}
            `;

            $('#modalDetailGIBody').html(html);
        },
        error: function (xhr) {
            let errorMsg = 'Gagal memuat detail pengeluaran barang.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = escapeHtml(xhr.responseJSON.message);
            }
            $('#modalDetailGIBody').html(`<div class="alert alert-danger rounded-3 m-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>${errorMsg}</div>`);
        }
    });
}

// Submit Goods Issue for Approval from inside Detail Modal (Draft -> Pending)
window.submitFromDetail = function (id, nomorGi) {
    Swal.fire({
        title: 'Ajukan Pengeluaran Barang?',
        html: `Apakah Anda yakin ingin mengajukan dokumen <b>${escapeHtml(nomorGi)}</b> untuk persetujuan atasan?<br><br><small class="text-muted">Notifikasi WhatsApp permintaan persetujuan akan otomatis dikirim ke atasan/approver.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan Sekarang',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim Pengajuan...',
                text: 'Harap tunggu, sistem sedang memproses pengajuan & notifikasi WhatsApp.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/goods-issue/${id}/submit`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diajukan!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal mengajukan pengeluaran.';
                    Swal.fire({ icon: 'error', title: 'Gagal Mengajukan', text: msg, confirmButtonColor: '#ef4444' });
                }
            });
        }
    });
};

// Reject / Return Pending Goods Issue back to Draft
window.rejectFromDetail = function (id, nomorGi) {
    Swal.fire({
        title: 'Kembalikan ke Status Draf?',
        html: `Apakah Anda ingin mengembalikan pengajuan <b>${escapeHtml(nomorGi)}</b> ke status Draf untuk direvisi oleh pembuat dokumen?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Kembalikan ke Draf',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/goods-issue/${id}/reject`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dikembalikan ke Draf',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal mengembalikan ke draf.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#ef4444' });
                }
            });
        }
    });
};

// Confirm Goods Issue from inside Detail Modal (Pending -> Confirmed)
window.confirmFromDetail = function (id, nomorGi) {
    Swal.fire({
        title: 'Setujui Pengeluaran Barang?',
        html: `Apakah Anda yakin ingin menyetujui transaksi pengeluaran <b>${escapeHtml(nomorGi)}</b>?<br><br><div class="text-start bg-light p-3 rounded-3 border text-danger small"><i class="bi bi-exclamation-circle-fill me-1"></i> <b>Konsekuensi Tindakan:</b><br>&bull; Stok aktual fisik di gudang akan <b>langsung dipotong</b>.<br>&bull; Jurnal akuntansi otomatis akan <b>langsung diposting</b>.<br>&bull; Notifikasi WhatsApp persetujuan akan <b>dikirim ke pembuat dokumen</b>.</div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Setujui & Potong Stok',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Persetujuan...',
                text: 'Harap tunggu sebentar, sistem sedang memverifikasi stok dan membuat jurnal.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/goods-issue/${id}/confirm`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disetujui!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal mengonfirmasi pengeluaran.';
                    Swal.fire({ icon: 'error', title: 'Gagal Menyetujui', text: msg, confirmButtonColor: '#ef4444' });
                }
            });
        }
    });
};

// Open Dedicated Journal Modal
function openModalJurnalGI() {
    const gi = window.currentGIData;
    if (!gi || !gi.jurnal) {
        Swal.fire('Informasi', 'Jurnal akuntansi tidak ditemukan untuk dokumen ini.', 'info');
        return;
    }

    $('#modalJurnalGINo').html(`
        <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-30 px-2 py-1 me-1"><i class="bi bi-hash"></i>${escapeHtml(gi.jurnal.nomor_jurnal)}</span>
        <span class="badge bg-black bg-opacity-25 text-white border border-white border-opacity-15 px-2 py-1"><i class="bi bi-file-earmark-text me-1"></i>${escapeHtml(gi.nomor_gi)}</span>
    `);

    let jnlRows = '';
    let totDeb = 0;
    let totKre = 0;

    (gi.jurnal.details || []).forEach((jd, jIdx) => {
        const coaCode = jd.account ? jd.account.code : '-';
        const coaName = jd.account ? jd.account.name : '-';
        const deb = parseFloat(jd.debit || 0);
        const kre = parseFloat(jd.kredit || 0);
        totDeb += deb;
        totKre += kre;

        const ketFmt = jd.keterangan ? escapeHtml(jd.keterangan.replace(/\n/g, ' • ')) : '-';

        const debHtml = deb > 0 
            ? `<span class="journal-amount-debit">Rp ${deb.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`
            : `<span class="text-muted opacity-50">-</span>`;

        const kreHtml = kre > 0 
            ? `<span class="journal-amount-kredit">Rp ${kre.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`
            : `<span class="text-muted opacity-50">-</span>`;

        jnlRows += `
        <tr>
            <td class="text-muted small text-center fw-semibold">${jIdx + 1}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="journal-badge-coa font-monospace">${escapeHtml(coaCode)}</span>
                    <span class="fw-bold" style="font-size: 0.88rem;">${escapeHtml(coaName)}</span>
                </div>
            </td>
            <td class="text-end">${debHtml}</td>
            <td class="text-end">${kreHtml}</td>
            <td><span class="small text-muted">${ketFmt}</span></td>
        </tr>`;
    });

    const isBalanced = Math.abs(totDeb - totKre) < 0.01;
    const balanceBadge = isBalanced
        ? `<span class="journal-badge-balance"><i class="bi bi-check-circle-fill me-1"></i>Balance</span>`
        : `<span class="journal-badge-unbalanced"><i class="bi bi-exclamation-triangle-fill me-1"></i>Unbalanced</span>`;

    const journalTableHtml = `
    <div class="journal-scroll-hint">
        <i class="bi bi-arrow-left-right"></i> Geser tabel ke samping untuk melihat rincian debit/kredit
    </div>
    <div class="journal-gi-table-wrapper">
        <table class="table journal-gi-table align-middle mb-0">
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="35%">Akun Rekening (COA)</th>
                    <th class="text-end" width="20%">Debit (Rp)</th>
                    <th class="text-end" width="20%">Kredit (Rp)</th>
                    <th width="20%">Keterangan</th>
                </tr>
            </thead>
            <tbody>${jnlRows}</tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end pe-3">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            ${balanceBadge}
                            <span class="text-uppercase small fw-bold" style="letter-spacing: 0.5px;">TOTAL:</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <span class="journal-amount-debit fs-6">Rp ${totDeb.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    </td>
                    <td class="text-end">
                        <span class="journal-amount-kredit fs-6">Rp ${totKre.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>`;

    $('#modalJurnalGIBody').html(journalTableHtml);

    const journalModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalJurnalGI'));
    journalModal.show();
}
</script>
@endpush
