{{-- MODAL DETAIL & JURNAL STOCK ADJUSTMENT --}}
<div class="modal fade" id="modalDetailAdjustment" tabindex="-1" aria-labelledby="modalDetailAdjustmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3.5 rounded-top-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-file-earmark-text fs-4 text-info"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="detailNomorAdj">ADJ/YYYY/MM/0000</h5>
                        <small class="text-white-50" id="detailTanggalGudang">Detail Transaksi Penyesuaian Stok</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('stock-adjustment.index') }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" title="Buka di halaman modul Stock Adjustment">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span class="d-none d-sm-inline">Buka Modul</span>
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-4">
                {{-- NAV TABS DETAIL vs JURNAL --}}
                <ul class="nav nav-pills nav-fill nav-pills-custom p-1.5 rounded-3 mb-4" id="pills-tab-detail" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 rounded-3" id="tab-adj-detail" data-bs-toggle="pill" data-bs-target="#content-adj-detail" type="button" role="tab">
                            <i class="bi bi-box-seam me-1"></i> Rincian Barang Adjustment
                        </button>
                    </li>
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-jurnal-stok-adjustment'))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 rounded-3" id="tab-adj-jurnal" data-bs-toggle="pill" data-bs-target="#content-adj-jurnal" type="button" role="tab">
                            <i class="bi bi-journal-bookmark-fill me-1"></i> Jurnal Akuntansi Otomatis
                        </button>
                    </li>
                    @endif
                </ul>

                <div class="tab-content" id="pills-tabContentDetail">
                    {{-- TAB 1: RINCIAN ADJUSTMENT --}}
                    <div class="tab-pane fade show active" id="content-adj-detail" role="tabpanel">
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <small class="text-secondary d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Gudang</small>
                                <span class="fw-bold fs-6 text-body d-inline-flex align-items-center gap-1.5 mt-0.5" id="detGudangName">-</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-secondary d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Tipe Penyesuaian</small>
                                <div class="mt-0.5" id="detTipeBadge">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-secondary d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Kategori Alasan</small>
                                <div class="mt-0.5"><span class="badge-alasan fw-bold text-body" id="detKategoriAlasan">-</span></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-secondary d-block text-uppercase fw-bold opacity-75" style="font-size:10px;">Status Dokumen</small>
                                <div class="mt-0.5" id="detStatusBadge">-</div>
                            </div>
                        </div>

                        <div class="card detail-info-card rounded-3 mb-4 p-3 border shadow-xs">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-4">
                                    <small class="text-secondary d-block text-uppercase fw-bold opacity-75 mb-1" style="font-size:10px;">Diajukan / Dibuat Oleh</small>
                                    <div id="detCreatorName"><i class="bi bi-person me-1 text-primary"></i>-</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <small class="text-secondary d-block text-uppercase fw-bold opacity-75 mb-1" style="font-size:10px;">Disetujui Oleh (Approver)</small>
                                    <div id="detApproverName"><i class="bi bi-person-check me-1 text-success"></i>-</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <small class="text-secondary d-block text-uppercase fw-bold opacity-75 mb-1" style="font-size:10px;">Waktu Persetujuan</small>
                                    <div id="detApprovedAt"><i class="bi bi-clock-history me-1 text-secondary"></i>-</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive rounded-3 border mb-3 shadow-xs">
                            <table class="table table-bordered table-hover align-middle mb-0" id="tableDetailItems" style="min-width: 720px;">
                                <thead class="small">
                                    <tr class="text-uppercase text-nowrap">
                                        <th class="text-center" style="width: 50px;">NO</th>
                                        <th style="min-width: 120px;">KODE BARANG</th>
                                        <th style="min-width: 200px;">NAMA BARANG</th>
                                        <th class="text-center" style="min-width: 90px;">SATUAN</th>
                                        <th class="text-center" style="min-width: 110px;">JUMLAH QTY</th>
                                        <th class="text-end" style="min-width: 130px;">HPP SATUAN (RP)</th>
                                        <th class="text-end" style="min-width: 130px;">NILAI TOTAL (RP)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDetailItems"></tbody>
                                <tfoot class="fw-bold">
                                    <tr class="align-middle">
                                        <td colspan="4" class="text-end text-muted small text-uppercase pe-3">TOTAL RINGKASAN:</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 rounded-pill fw-bold font-monospace" id="detTotalQty">0 Qty</span>
                                        </td>
                                        <td class="text-end text-muted small text-uppercase pe-2">TOTAL NILAI:</td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success font-monospace fs-6" id="detTotalNilai">Rp 0</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="p-3 rounded-3 box-keterangan-detail">
                            <small class="d-block fw-bold mb-1 opacity-75"><i class="bi bi-chat-left-text me-1"></i>Catatan Keterangan:</small>
                            <span id="detKeteranganText" class="fw-semibold text-body">-</span>
                        </div>
                    </div>

                    {{-- TAB 2: JURNAL AKUNTANSI --}}
                    <div class="tab-pane fade" id="content-adj-jurnal" role="tabpanel">
                        <div id="jurnalContainer">
                            {{-- Populated by JS --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top p-4 pt-3 justify-content-between">
                <div>
                    <button type="button" id="btnCetakPdfDetail" class="btn btn-outline-danger rounded-pill px-3" onclick="cetakPdfDetail()">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Dokumen PDF
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('approve-stok-adjustment'))
                    <button type="button" id="btnConfirmFromDetail" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm d-none d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check-circle-fill"></i> Setujui & Konfirmasi Stok
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DETAIL JAVASCRIPT INLINE --}}
@push('scripts')
<script>
    let activeDetailId = null;

    if (typeof window.escapeHtmlAdj !== 'function') {
        window.escapeHtmlAdj = function(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };
    }

    if (typeof window.numberFormatID !== 'function') {
        window.numberFormatID = function(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            let num = parseFloat(val);
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 4
            }).format(num);
        };
    }

    if (typeof window.numberFormatCurrencyID !== 'function') {
        window.numberFormatCurrencyID = function(val) {
            if (val === null || val === undefined || isNaN(val)) return '0,00';
            let num = parseFloat(val);
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };
    }

    function showDetailAdjustment(id) {
        activeDetailId = id;
        $.ajax({
            url: `/stock-adjustment/${id}`,
            method: 'GET',
            success: function(res) {
                const adj = res.adjustment;
                const jurnal = res.jurnal;

                $('#detailNomorAdj').text(adj.nomor_adjustment);
                $('#detailTanggalGudang').text(`Tanggal: ${adj.tanggal_adjustment ? adj.tanggal_adjustment.substring(0,10) : '-'} | Gudang: ${adj.gudang ? adj.gudang.nama_gudang : '-'}`);
                $('#detGudangName').text(adj.gudang ? adj.gudang.nama_gudang : '-');
                $('#detKategoriAlasan').text(adj.kategori_alasan);
                $('#detKeteranganText').text(adj.keterangan || '-');

                const creatorName = adj.creator ? (adj.creator.nama_lengkap || adj.creator.name) : '-';
                const approverName = adj.approver ? (adj.approver.nama_lengkap || adj.approver.name) : null;
                const approvedAt = adj.disetujui_pada ? adj.disetujui_pada.substring(0, 16).replace('T', ' ') : null;

                $('#detCreatorName').html(`<span class="fw-bold text-body d-inline-flex align-items-center gap-1.5"><i class="bi bi-person-circle text-primary"></i> ${escapeHtmlAdj(creatorName)}</span>`);

                if (approverName) {
                    $('#detApproverName').html(`<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-check2-circle"></i> ${escapeHtmlAdj(approverName)}</span>`);
                } else {
                    $('#detApproverName').html(`<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1.5 rounded-pill fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-hourglass-split"></i> Belum Disetujui</span>`);
                }

                if (approvedAt) {
                    $('#detApprovedAt').html(`<span class="text-body font-monospace d-inline-flex align-items-center gap-1.5"><i class="bi bi-clock-history text-secondary"></i> ${approvedAt}</span>`);
                } else {
                    $('#detApprovedAt').html(`<span class="text-muted fst-italic">-</span>`);
                }

                if (adj.tipe_adjustment === 'Pengurangan') {
                    $('#detTipeBadge').html('<span class="badge-tipe-pengurangan"><i class="bi bi-arrow-down-circle me-1"></i>Pengurangan Stok</span>');
                } else {
                    $('#detTipeBadge').html('<span class="badge-tipe-penambahan"><i class="bi bi-arrow-up-circle me-1"></i>Penambahan Stok</span>');
                }

                if (adj.status === 'Draft') {
                    $('#detStatusBadge').html('<span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>');
                    $('#btnConfirmFromDetail').addClass('d-none');
                } else if (adj.status === 'Pending') {
                    $('#detStatusBadge').html('<span class="badge-pending"><i class="bi bi-clock-history me-1"></i>Pending Approval</span>');
                    $('#btnConfirmFromDetail').removeClass('d-none');
                } else if (adj.status === 'Confirmed') {
                    $('#detStatusBadge').html('<span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>');
                    $('#btnConfirmFromDetail').addClass('d-none');
                } else {
                    $('#detStatusBadge').html('<span class="badge bg-danger text-white px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i>Rejected</span>');
                    $('#btnConfirmFromDetail').addClass('d-none');
                }

                // Render Table Items
                let rowsHtml = '';
                let totalQty = 0;
                let totalNilai = 0;

                if (adj.details && adj.details.length > 0) {
                    adj.details.forEach((item, idx) => {
                        totalQty += parseFloat(item.jumlah);
                        totalNilai += parseFloat(item.nilai_total);

                        const fotoUrl = item.barang && item.barang.primary_foto ? `/storage/${item.barang.primary_foto.foto_path}` : '';
                        const itemDisp = item.barang ? `[${item.barang.kode_barang}] ${item.barang.nama_barang}` : '-';

                        const hppDisp = res.can_see_hpp ? `Rp ${numberFormatCurrencyID(item.harga_satuan)}` : 'Rp ***';
                        const totalDisp = res.can_see_hpp ? `Rp ${numberFormatCurrencyID(item.nilai_total)}` : 'Rp ***';

                        let snHtml = '';
                        if (item.serial_numbers && Array.isArray(item.serial_numbers) && item.serial_numbers.length > 0) {
                            const isPenambahan = (res.adjustment && res.adjustment.tipe_adjustment === 'Penambahan');
                            const snColor = isPenambahan ? 'text-primary border-primary' : 'text-danger border-danger';
                            snHtml = `
                                <div class="mt-1.5 pt-1 border-top border-secondary border-opacity-10">
                                    <div class="small fw-semibold ${isPenambahan ? 'text-primary' : 'text-danger'} mb-1 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-upc-scan"></i> Serial Numbers (${item.serial_numbers.length} unit):
                                    </div>
                                    <div class="d-flex flex-wrap gap-1" style="max-height: 85px; overflow-y: auto;">
                                        ${item.serial_numbers.map(sn => `<span class="badge bg-light text-dark border ${snColor} border-opacity-30 font-monospace px-2 py-0.5" style="font-size: 0.7rem;">${escapeHtmlAdj(sn)}</span>`).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        rowsHtml += `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td class="fw-bold text-primary font-monospace">${item.barang ? item.barang.kode_barang : '-'}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="product-thumb-box border rounded-3 d-flex align-items-center justify-content-center bg-light cursor-pointer shadow-sm"
                                             style="width: 32px; height: 32px; flex-shrink: 0; overflow: hidden;"
                                             onclick="bukaModalPreviewFoto('${fotoUrl}', '${escapeHtmlAdj(itemDisp)}')"
                                             title="Klik untuk lihat foto barang">
                                            <img src="${fotoUrl}" class="product-img ${fotoUrl ? '' : 'd-none'}" style="width: 100%; height: 100%; object-fit: cover;">
                                            <i class="bi bi-image text-muted fs-6 ${fotoUrl ? 'd-none' : ''}"></i>
                                        </div>
                                        <div>
                                            <span class="text-body fw-semibold">${item.barang ? item.barang.nama_barang : '-'}</span>
                                            ${snHtml}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">${item.barang && item.barang.satuan ? item.barang.satuan.nama_satuan : 'Pcs'}</td>
                                <td class="text-center fw-semibold text-body">${numberFormatID(item.jumlah)}</td>
                                <td class="text-end text-body">${hppDisp}</td>
                                <td class="text-end fw-bold text-body">${totalDisp}</td>
                            </tr>
                        `;
                    });
                }
                $('#tbodyDetailItems').html(rowsHtml);
                $('#detTotalQty').text(numberFormatID(totalQty) + ' Qty');
                $('#detTotalNilai').text(res.can_see_hpp ? ('Rp ' + numberFormatCurrencyID(totalNilai)) : 'Rp ***');

                // Render Accounting Journal Tab
                renderJurnalTab(jurnal);

                $('#tab-adj-detail').tab('show');
                $('#modalDetailAdjustment').modal('show');
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat detail transaksi.', 'error');
            }
        });
    }

    function renderJurnalTab(jurnal) {
        if (!jurnal) {
            $('#jurnalContainer').html(`
                <div class="text-center py-5">
                    <i class="bi bi-info-circle text-muted fs-1 d-block mb-2"></i>
                    <p class="text-muted fw-semibold mb-0">Jurnal Akuntansi Otomatis Belum Terbentuk.</p>
                    <small class="text-muted">Jurnal akuntansi akan otomatis terbentuk saat dokumen penyesuaian dikonfirmasi (Confirmed).</small>
                </div>
            `);
            return;
        }

        let detailsHtml = '';
        let totalDebit = 0;
        let totalKredit = 0;

        jurnal.details.forEach(d => {
            const deb = parseFloat(d.debit) || 0;
            const kre = parseFloat(d.kredit) || 0;
            totalDebit += deb;
            totalKredit += kre;

            detailsHtml += `
                <tr>
                    <td class="fw-bold text-body">${d.account ? (d.account.code || d.account.kode_akun) : '-'}</td>
                    <td class="text-body">${d.account ? (d.account.name || d.account.nama_akun) : '-'}</td>
                    <td class="text-end fw-semibold text-primary">${deb > 0 ? 'Rp ' + numberFormatCurrencyID(deb) : '-'}</td>
                    <td class="text-end fw-semibold text-success">${kre > 0 ? 'Rp ' + numberFormatCurrencyID(kre) : '-'}</td>
                </tr>
            `;
        });

        const containerHtml = `
            <div class="card detail-info-card rounded-3 mb-3 p-3">
                <div class="row g-2 small">
                    <div class="col-6 col-md-3"><strong>No. Jurnal:</strong> <span class="text-body">${jurnal.nomor_jurnal}</span></div>
                    <div class="col-6 col-md-3"><strong>Tanggal:</strong> <span class="text-body">${jurnal.tanggal_jurnal}</span></div>
                    <div class="col-12 col-md-6"><strong>Keterangan:</strong> <span class="text-body">${jurnal.keterangan}</span></div>
                </div>
            </div>
            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>KODE AKUN</th>
                            <th>NAMA AKUN COA</th>
                            <th class="text-end">DEBET (RP)</th>
                            <th class="text-end">KREDIT (RP)</th>
                        </tr>
                    </thead>
                    <tbody>${detailsHtml}</tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">TOTAL DEBET / KREDIT:</td>
                            <td class="text-end text-primary">Rp ${numberFormatCurrencyID(totalDebit)}</td>
                            <td class="text-end text-success">Rp ${numberFormatCurrencyID(totalKredit)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        $('#jurnalContainer').html(containerHtml);
    }

    function cetakPdfDetail() {
        if (activeDetailId) {
            window.open(`/stock-adjustment/${activeDetailId}/print`, '_blank');
        }
    }

    $('#btnConfirmFromDetail').on('click', function() {
        if (activeDetailId) {
            $('#modalDetailAdjustment').modal('hide');
            confirmAdjustment(activeDetailId, $('#detailNomorAdj').text());
        }
    });

</script>
@endpush
