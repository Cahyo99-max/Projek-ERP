<script>
$(document).ready(function () {
    let currentViewInvoice = null;

    let formatJournalDecimal = function (num) {
        let n = parseFloat(num || 0);
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(n);
    };

    // Trigger View Click
    $(document).on('click', '.btn-view-invoice', function () {
        let id = $(this).data('id');
        $.get('/purchase-invoices/' + id, function (res) {
            if (res && res.invoice) {
                let inv = res.invoice;
                inv.active_payment_vouchers = res.active_payment_vouchers || [];
                inv.has_active_payment = !!res.has_active_payment;
                currentViewInvoice = inv;

                $('#view_nomor_faktur_sub').text('Purchase Invoice (AP) — ' + (inv.nomor_faktur || '-'));
                $('#view_disp_nomor_faktur').text(inv.nomor_faktur || '-');
                $('#view_disp_faktur_supplier').text(inv.nomor_faktur_supplier || '-');
                $('#view_disp_tanggal_faktur').text(inv.tanggal_faktur ? String(inv.tanggal_faktur).substring(0, 10) : '-');
                $('#view_disp_tanggal_jatuh_tempo').text(inv.tanggal_jatuh_tempo ? String(inv.tanggal_jatuh_tempo).substring(0, 10) : '-');

                let sup = inv.supplier || (inv.goods_receipt && inv.goods_receipt.po ? inv.goods_receipt.po.supplier : {});
                let po = inv.po || (inv.goods_receipt ? inv.goods_receipt.po : {});
                let gdg = (inv.goods_receipt && inv.goods_receipt.gudang) ? inv.goods_receipt.gudang.nama_gudang : '-';
                let grnNum = inv.goods_receipt ? inv.goods_receipt.nomor_gr : '-';

                $('#view_disp_supplier_name').text(sup.nama_supplier || '-');
                if (sup.npwp && String(sup.npwp).trim() !== '' && String(sup.npwp).trim() !== '-') {
                    $('#view_disp_supplier_npwp').html('<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-monospace px-2 py-0.5"><i class="bi bi-card-text me-1"></i>NPWP: ' + escapeHtml(sup.npwp) + '</span>');
                } else {
                    $('#view_disp_supplier_npwp').html('<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle font-monospace px-2 py-0.5"><i class="bi bi-dash-circle me-1"></i>Non-PKP (Tanpa NPWP)</span>');
                }
                $('#view_disp_ref_gr').text(inv.goods_receipt ? (grnNum + ' (' + gdg + ')') : '— (Langsung dari PO)');
                $('#view_disp_ref_po').text('Ref. PO: ' + (po ? (po.nomor_po || '-') : '-'));

                // Resolve Tender Info
                let tenderObj = inv.tender || (inv.goods_receipt && inv.goods_receipt.tender ? inv.goods_receipt.tender : ((po && po.tender) ? po.tender : (inv.goods_receipt && inv.goods_receipt.po ? inv.goods_receipt.po.tender : null)));
                let tenderNomor = tenderObj ? (tenderObj.nomor_tender || tenderObj.nomor_kontrak || ('Tender #' + tenderObj.id)) : '';
                if (tenderObj && tenderObj.nama_tender && !tenderNomor.includes(tenderObj.nama_tender)) {
                    tenderNomor += ' - ' + tenderObj.nama_tender;
                }

                if (tenderNomor) {
                    $('#view_disp_tender').text(tenderNomor);
                    $('#view_container_tender').show();
                } else {
                    $('#view_disp_tender').text('-');
                    $('#view_container_tender').hide();
                }

                let curCode = (inv.currency && inv.currency.kode_currency) 
                    ? inv.currency.kode_currency 
                    : ((po && po.currency && po.currency.kode_currency) ? po.currency.kode_currency : 'IDR');
                let curSymbol = (inv.currency && inv.currency.simbol) 
                    ? inv.currency.simbol 
                    : ((po && po.currency && po.currency.simbol) ? po.currency.simbol : (curCode === 'USD' ? '$' : 'Rp'));
                let kursVal = (inv.kurs && parseFloat(inv.kurs) > 0)
                    ? parseFloat(inv.kurs)
                    : ((inv.goods_receipt && inv.goods_receipt.kurs && parseFloat(inv.goods_receipt.kurs) > 0)
                        ? parseFloat(inv.goods_receipt.kurs)
                        : ((po && po.kurs) ? parseFloat(po.kurs) : 1.0));
                let showIdrEquiv = (curCode !== 'IDR' || Math.abs(kursVal - 1.0) > 0.0001);

                $('#view_disp_currency_kurs').text(curCode + ' (Kurs: ' + formatSmartKurs(kursVal) + ')');

                let termTxt = inv.payment_term ? (inv.payment_term.nama_term + ' (' + inv.payment_term.jumlah_hari + ' Hari)') : 'Cash / 0 Hari';
                let pjkTxt = inv.pajak ? (inv.pajak.nama_pajak + ' (' + inv.pajak.tarif + '%)') : 'Non-PPN (0%)';
                $('#view_disp_term_pajak').text(termTxt + ' • ' + pjkTxt);

                if (inv.keterangan) {
                    $('#view_disp_keterangan').text(inv.keterangan);
                    $('#view_container_keterangan').show();
                } else {
                    $('#view_container_keterangan').hide();
                }

                // Render Lampiran / Foto Bukti Faktur Supplier (Modal Preview, Bukan Buka Halaman Baru)
                if (inv.lampiran_faktur || inv.lampiran_url) {
                    let fileUrl = inv.lampiran_url || ('/' + String(inv.lampiran_faktur).replace(/^\//, ''));
                    let safeFileUrl = escapeHtml(fileUrl);
                    let isImg = /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(fileUrl);
                    let invTitle = escapeHtml(inv.nomor_faktur || 'Faktur Pembelian');
                    let lampiranHtml = '';
                    if (isImg) {
                        lampiranHtml = '<div class="p-2 px-2.5 rounded-3 bg-body border d-flex align-items-center justify-content-between gap-2 shadow-xs">' +
                            '<div class="d-flex align-items-center gap-2 overflow-hidden">' +
                                '<button type="button" class="btn p-0 border rounded p-0.5 bg-white shadow-xs btn-preview-lampiran flex-shrink-0" data-url="' + safeFileUrl + '" data-title="' + invTitle + '" title="Klik untuk memperbesar foto">' +
                                    '<img src="' + safeFileUrl + '" alt="Bukti Faktur" style="height: 38px; width: 48px; object-fit: cover;" class="rounded">' +
                                '</button>' +
                                '<div class="overflow-hidden">' +
                                    '<span class="extra-small text-muted fw-bold text-uppercase d-block" style="font-size:0.65rem;">Bukti Tagihan:</span>' +
                                    '<div class="fw-semibold text-body small text-truncate">Foto Terlampir</div>' +
                                '</div>' +
                            '</div>' +
                            '<button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 shadow-xs btn-preview-lampiran flex-shrink-0" data-url="' + safeFileUrl + '" data-title="' + invTitle + '" style="font-size:0.75rem;">' +
                                '<i class="bi bi-eye me-1"></i>Buka Gambar' +
                            '</button>' +
                        '</div>';
                    } else {
                        lampiranHtml = '<div class="p-2 px-2.5 rounded-3 bg-body border d-flex align-items-center justify-content-between gap-2 shadow-xs">' +
                            '<div class="d-flex align-items-center gap-2 overflow-hidden">' +
                                '<div class="p-1.5 rounded-2 bg-danger bg-opacity-10 text-danger flex-shrink-0">' +
                                    '<i class="bi bi-file-earmark-pdf-fill fs-5"></i>' +
                                '</div>' +
                                '<div class="overflow-hidden">' +
                                    '<span class="extra-small text-muted fw-bold text-uppercase d-block" style="font-size:0.65rem;">Bukti Tagihan:</span>' +
                                    '<div class="fw-semibold text-body small text-truncate">Dokumen PDF</div>' +
                                '</div>' +
                            '</div>' +
                            '<button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 shadow-xs btn-preview-lampiran flex-shrink-0" data-url="' + safeFileUrl + '" data-title="' + invTitle + '" style="font-size:0.75rem;">' +
                                '<i class="bi bi-eye me-1"></i>Buka PDF' +
                            '</button>' +
                        '</div>';
                    }
                    $('#view_disp_lampiran_content').html(lampiranHtml);
                } else {
                    $('#view_disp_lampiran_content').html('<div class="p-2 rounded-3 bg-body border text-center text-muted extra-small fst-italic"><i class="bi bi-file-earmark-x me-1"></i>Tidak ada berkas bukti diunggah</div>');
                }

                // Render Status Badge & Banner
                let statusBadgeHtml = '';
                let bannerHtml = '';

                $('#view_btn_edit_trigger').data('id', inv.id).hide();
                $('#view_btn_submit_approval_trigger').data('id', inv.id).hide();
                $('#view_btn_approve_trigger').data('id', inv.id).hide();
                $('#view_btn_reject_trigger').data('id', inv.id).hide();
                $('#view_btn_wa_trigger').data('id', inv.id).hide();
                $('#view_btn_cancel_trigger').data('id', inv.id).hide();

                let perms = window.userInvoicePermissions || { canEdit: false, isApprover: false, canPrint: false, canCancel: false };
                let isCreatorOrAdmin = (perms.currentUserId && inv.created_by == perms.currentUserId) || perms.canEdit;
                let canApprove = (res.can_approve !== undefined) ? (res.can_approve === true) : (perms.isApprover === true);
                let canCancel = (res.can_cancel !== undefined) ? res.can_cancel : (perms.canCancel || false);

                if (inv.status === 'Draft') {
                    statusBadgeHtml = '<span class="badge bg-secondary bg-opacity-20 text-white border border-secondary rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5"><span class="inv-pulse-dot bg-secondary"></span><span>Draft</span></span>';
                    bannerHtml = '<div class="cinematic-workflow-banner draft">' +
                        '<div class="p-1.5 rounded-circle bg-secondary bg-opacity-15 text-secondary flex-shrink-0">' +
                            '<i class="bi bi-pencil-square fs-5"></i>' +
                        '</div>' +
                        '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-grow-1 gap-1">' +
                            '<div>' +
                                '<div class="fw-bold text-body small">Status Dokumen: Draft</div>' +
                                '<div class="extra-small text-muted">Faktur ini hanya dapat dilihat oleh pembuat dan belum diposting ke Jurnal Akuntansi.</div>' +
                            '</div>' +
                            '<span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace extra-small px-2 py-0.5">DRAFT MODE</span>' +
                        '</div>' +
                    '</div>';
                    if (isCreatorOrAdmin) {
                        $('#view_btn_edit_trigger').show();
                        $('#view_btn_submit_approval_trigger').show();
                    }
                    if (canApprove) {
                        $('#view_btn_approve_trigger').show();
                    }
                } else if (inv.status === 'Menunggu Persetujuan' || inv.status === 'Menunggu Approval') {
                    statusBadgeHtml = '<span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5"><span class="inv-pulse-dot pulse-amber"></span><span>Menunggu Persetujuan</span></span>';
                    bannerHtml = '<div class="cinematic-workflow-banner pending">' +
                        '<div class="p-1.5 rounded-circle bg-warning bg-opacity-20 text-warning-emphasis flex-shrink-0">' +
                            '<i class="bi bi-hourglass-split fs-5"></i>' +
                        '</div>' +
                        '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-grow-1 gap-1">' +
                            '<div>' +
                                '<div class="fw-bold text-warning-emphasis small">Menunggu Persetujuan (Approval) Atasan</div>' +
                                '<div class="extra-small text-muted">Faktur telah diajukan ke Atasan via WhatsApp. Jurnal Utang otomatis diterbitkan setelah disetujui (ACC).</div>' +
                            '</div>' +
                            '<span class="badge bg-warning text-dark font-monospace extra-small px-2 py-0.5">WAITING APPROVAL</span>' +
                        '</div>' +
                    '</div>';
                    if (isCreatorOrAdmin) {
                        $('#view_btn_edit_trigger').show();
                        $('#view_btn_wa_trigger').show();
                    }
                    if (canApprove) {
                        $('#view_btn_approve_trigger').show();
                        $('#view_btn_reject_trigger').show();
                    }
                } else if (inv.status === 'Rejected') {
                    statusBadgeHtml = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5"><span class="inv-pulse-dot pulse-red"></span><span>Ditolak</span></span>';
                    bannerHtml = '<div class="cinematic-workflow-banner rejected">' +
                        '<div class="p-1.5 rounded-circle bg-danger bg-opacity-15 text-danger flex-shrink-0">' +
                            '<i class="bi bi-x-circle-fill fs-5"></i>' +
                        '</div>' +
                        '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-grow-1 gap-1">' +
                            '<div>' +
                                '<div class="fw-bold text-danger small">Pengajuan Faktur Ditolak Atasan</div>' +
                                '<div class="extra-small text-body mt-0.5"><b>Alasan:</b> ' + escapeHtml(inv.alasan_penolakan || 'Perlu revisi data.') + '</div>' +
                            '</div>' +
                            '<span class="badge bg-danger bg-opacity-10 text-danger font-monospace extra-small px-2 py-0.5">REJECTED</span>' +
                        '</div>' +
                    '</div>';
                    if (isCreatorOrAdmin) {
                        $('#view_btn_edit_trigger').show();
                        $('#view_btn_submit_approval_trigger').show();
                    }
                } else if (inv.status === 'Cancelled' || inv.status === 'Void') {
                    let cancellerName = inv.canceller ? (inv.canceller.nama_lengkap || (inv.canceller.karyawan ? inv.canceller.karyawan.nama_lengkap : (inv.canceller.name || 'User'))) : 'Sistem / Pengguna';
                    let cancelledAt = inv.cancelled_at ? String(inv.cancelled_at).substring(0, 16) : '-';

                    statusBadgeHtml = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5"><span class="inv-pulse-dot pulse-red"></span><span>Dibatalkan</span></span>';
                    bannerHtml = '<div class="cinematic-workflow-banner cancelled">' +
                        '<div class="p-1.5 rounded-circle bg-danger bg-opacity-15 text-danger flex-shrink-0">' +
                            '<i class="bi bi-x-octagon-fill fs-5"></i>' +
                        '</div>' +
                        '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-grow-1 gap-1">' +
                            '<div>' +
                                '<div class="fw-bold text-danger small">Transaksi Faktur Pembelian Dibatalkan (Void)</div>' +
                                '<div class="extra-small text-muted mt-0.5">Oleh: <b>' + escapeHtml(cancellerName) + '</b>' + (cancelledAt !== '-' ? (' pada ' + cancelledAt) : '') + ' • Alasan: ' + escapeHtml(inv.alasan_pembatalan || '-') + '</div>' +
                            '</div>' +
                            '<span class="badge bg-danger bg-opacity-10 text-danger font-monospace extra-small px-2 py-0.5">VOID</span>' +
                        '</div>' +
                    '</div>';
                } else {
                    let approverName = inv.approver ? (inv.approver.nama_lengkap || (inv.approver.karyawan ? inv.approver.karyawan.nama_lengkap : 'Atasan')) : 'Sistem / Atasan';
                    let approvedAt = inv.approved_at ? String(inv.approved_at).substring(0, 16) : '-';

                    statusBadgeHtml = '<span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5"><span class="inv-pulse-dot pulse-green"></span><span>' + escapeHtml(inv.status) + '</span></span>';
                    bannerHtml = '<div class="cinematic-workflow-banner approved">' +
                        '<div class="p-1.5 rounded-circle bg-success bg-opacity-15 text-success flex-shrink-0">' +
                            '<i class="bi bi-check-circle-fill fs-5"></i>' +
                        '</div>' +
                        '<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-grow-1 gap-1">' +
                            '<div>' +
                                '<div class="fw-bold text-success small">Faktur Telah Disetujui (ACC) &amp; Diposting</div>' +
                                '<div class="extra-small text-muted">Disetujui oleh: <b>' + escapeHtml(approverName) + '</b>' + (approvedAt !== '-' ? (' pada ' + approvedAt) : '') + '. Jurnal Otomatis diterbitkan.</div>' +
                            '</div>' +
                            '<span class="badge bg-success bg-opacity-10 text-success font-monospace extra-small px-2 py-0.5">POSTED</span>' +
                        '</div>' +
                    '</div>';

                    if (canCancel) {
                        $('#view_btn_cancel_trigger').show();
                    }
                }

                $('#view_status_badge').html(statusBadgeHtml);
                $('#view_approval_banner_container').html(bannerHtml);

                // Set PDF Print Link and toggle visibility
                if (perms.canPrint) {
                    $('#view_btn_print_pdf').attr('href', '/purchase-invoices/' + inv.id + '/pdf').show();
                } else {
                    $('#view_btn_print_pdf').hide();
                }

                // Pre-calculate Total Net Barang & Total Biaya Tambahan yang Masuk HPP
                let totalNetBarang = 0;
                let totalBiayaHpp = 0;
                if (inv.details && inv.details.length > 0) {
                    inv.details.forEach(function (d) {
                        if (d.jenis === 'Barang') {
                            let q = parseFloat(d.jumlah || 0);
                            let pr = parseFloat(d.harga_satuan || 0);
                            let poD = d.po_detail || d.poDetail || (d.gr_detail ? (d.gr_detail.po_detail || d.gr_detail.poDetail || {}) : {});
                            let disc = parseFloat(d.diskon_item !== undefined && d.diskon_item !== null ? d.diskon_item : (poD.diskon_item || 0));
                            totalNetBarang += Math.max(0, (q * pr) - (q * disc));
                        } else if (d.jenis !== 'PPN' && (d.masuk_hpp === 1 || d.masuk_hpp === true || d.masuk_hpp === '1')) {
                            totalBiayaHpp += parseFloat(d.subtotal || d.harga_satuan || 0);
                        }
                    });
                }

                // Render Item Details
                let itemsHtml = '';
                let no = 0;
                let subtotalGross = 0;
                let totalDiskon = 0;

                if (inv.details && inv.details.length > 0) {
                    inv.details.forEach(function (d) {
                        if (d.jenis === 'Barang') {
                            no++;
                            let kode = d.barang ? d.barang.kode_barang : '-';
                            let nama = d.barang ? d.barang.nama_barang : (d.keterangan || 'Barang');
                            let satuan = d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'pcs';
                            let qty = parseFloat(d.jumlah || 0);
                            let hargaSatuan = parseFloat(d.harga_satuan || 0);
                            let poDetail = d.po_detail || d.poDetail || (d.gr_detail ? (d.gr_detail.po_detail || d.gr_detail.poDetail || {}) : {});
                            let diskonItem = parseFloat(d.diskon_item !== undefined && d.diskon_item !== null ? d.diskon_item : (poDetail.diskon_item || 0));
                            let lineSubtotalGross = qty * hargaSatuan;
                            let lineDiskonTotal = qty * diskonItem;
                            let lineNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);

                            let tarifPpn = parseFloat(d.tarif_ppn !== undefined && d.tarif_ppn !== null ? d.tarif_ppn : (poDetail.tarif_ppn || 0));
                            let ppnNominal = parseFloat(d.ppn_nominal !== undefined && d.ppn_nominal !== null ? d.ppn_nominal : (lineNet * (tarifPpn / 100)));

                            let ppnBadgeHtml = '';
                            if (tarifPpn > 0) {
                                ppnBadgeHtml = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 font-monospace px-2.5 py-1">' + tarifPpn + '% <small class="opacity-75">(+' + formatMoney(ppnNominal, curCode, curSymbol) + ')</small></span>';
                            } else {
                                ppnBadgeHtml = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 font-monospace px-2 py-0.5 small">0% (Non-PPN)</span>';
                            }

                            let receiptInfoHtml = '';
                            if (inv.goods_receipt_id || d.gr_detail_id) {
                                receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size:0.68rem; line-height:1.2;">' +
                                    '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-1.5 py-0.5 rounded" title="Kuantitas dari Penerimaan Barang (GR)">' +
                                        '<i class="bi bi-box-seam me-1"></i>Diterima (GR)' +
                                    '</span>' +
                                '</div>';
                            } else if (inv.po_id || d.po_detail_id) {
                                let grDetails = poDetail.gr_details || poDetail.grDetails || [];
                                let qtyReceived = 0;
                                if (Array.isArray(grDetails)) {
                                    grDetails.forEach(function(grd) {
                                        let grObj = grd.goods_receipt || grd.goodsReceipt;
                                        if (!grObj || grObj.status !== 'Dibatalkan') {
                                            qtyReceived += parseFloat(grd.jumlah || 0);
                                        }
                                    });
                                }
                                let qtyOrdered = parseFloat(poDetail.jumlah != null ? poDetail.jumlah : qty);
                                let qtyRemaining = Math.max(0, qtyOrdered - qtyReceived);

                                if (qtyReceived > 0) {
                                    let isFull = qtyReceived >= qtyOrdered;
                                    receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size: 0.68rem; line-height: 1.2;">' +
                                        '<span class="badge ' + (isFull ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-info bg-opacity-10 text-info border border-info-subtle') + ' px-1.5 py-0.5 rounded" title="Sudah diterima di gudang">' +
                                            '<i class="bi bi-box-seam me-1"></i>Diterima: ' + qtyReceived + ' ' + escapeHtml(satuan) +
                                        '</span>' +
                                        (qtyRemaining > 0 ? '<div class="text-warning-emphasis fw-medium extra-small mt-0.5" style="font-size:0.65rem;"><i class="bi bi-hourglass-split me-0.5"></i>Sisa: ' + qtyRemaining + ' ' + escapeHtml(satuan) + '</div>' : '') +
                                    '</div>';
                                } else {
                                    receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size: 0.68rem; line-height: 1.2;">' +
                                        '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-1.5 py-0.5 rounded" title="Belum ada penerimaan barang">' +
                                            '<i class="bi bi-clock-history me-1"></i>Belum diterima' +
                                        '</span>' +
                                    '</div>';
                                }
                            }

                            subtotalGross += lineSubtotalGross;
                            totalDiskon += lineDiskonTotal;

                            // Alokasi Biaya HPP (Ongkir, dll) proporsional berdasarkan Subtotal Net Barang
                            let alokasiBiayaLine = 0;
                            if (totalBiayaHpp > 0 && totalNetBarang > 0) {
                                alokasiBiayaLine = (lineNet / totalNetBarang) * totalBiayaHpp;
                            }
                            let alokasiBiayaPerUnit = qty > 0 ? (alokasiBiayaLine / qty) : 0;
                            let hppAkhirTotal = lineNet + alokasiBiayaLine;
                            let hppAkhirPerUnit = qty > 0 ? (hppAkhirTotal / qty) : (lineNet);

                            let alokasiBiayaHtml = '';
                            let hppAkhirHtml = '';

                            if (totalBiayaHpp > 0) {
                                alokasiBiayaHtml = '<div class="fw-bold text-primary font-monospace">+ ' + formatMoney(alokasiBiayaLine, curCode, curSymbol) + '</div>' +
                                    '<div class="extra-small text-muted font-monospace mt-0.5">(+ ' + formatMoney(alokasiBiayaPerUnit, curCode, curSymbol) + ' / unit)</div>';

                                hppAkhirHtml = '<div class="fw-bold text-success font-monospace">' + formatMoney(hppAkhirPerUnit, curCode, curSymbol) + ' <small class="text-body-secondary font-sans-serif">/ unit</small></div>' +
                                    '<div class="extra-small text-muted font-monospace mt-0.5">Total: ' + formatMoney(hppAkhirTotal, curCode, curSymbol) + '</div>';
                            } else {
                                alokasiBiayaHtml = '<span class="text-muted opacity-50 font-monospace small">-</span>';
                                hppAkhirHtml = '<div class="fw-bold font-monospace text-body">' + formatMoney(qty > 0 ? (lineNet / qty) : 0, curCode, curSymbol) + ' <small class="text-body-secondary font-sans-serif">/ unit</small></div>';
                            }

                            itemsHtml += '<tr>' +
                                '<td class="text-center"><span class="inv-row-num">' + no + '</span></td>' +
                                '<td><span class="inv-badge-code">' + escapeHtml(kode) + '</span></td>' +
                                '<td><div class="inv-item-name">' + escapeHtml(nama) + '</div></td>' +
                                '<td class="text-center">' +
                                    '<span class="inv-badge-qty">' + qty + '</span>' +
                                    receiptInfoHtml +
                                '</td>' +
                                '<td class="text-center"><span class="inv-badge-unit">' + escapeHtml(satuan) + '</span></td>' +
                                '<td class="text-end fw-bold font-monospace text-body">' +
                                    formatMoney(hargaSatuan, curCode, curSymbol) +
                                    (showIdrEquiv ? '<div class="extra-small text-muted font-monospace mt-1">≈ Rp ' + formatNumber(hargaSatuan * kursVal) + ' / unit</div>' : '') +
                                '</td>' +
                                '<td class="text-end">' + (lineDiskonTotal > 0 ? '<span class="inv-badge-discount">- ' + formatMoney(lineDiskonTotal, curCode, curSymbol) + '</span>' : '<span class="text-muted opacity-50 font-monospace small">-</span>') + '</td>' +
                                '<td class="text-center">' + ppnBadgeHtml + '</td>' +
                                '<td class="text-end">' +
                                    '<span class="inv-val-subtotal">' + formatMoney(lineNet, curCode, curSymbol) + '</span>' +
                                    (showIdrEquiv ? '<div class="small text-muted font-monospace mt-1">≈ Rp ' + formatNumber(lineNet * kursVal) + '</div>' : '') +
                                '</td>' +
                                '<td class="text-end">' + alokasiBiayaHtml + '</td>' +
                                '<td class="text-end bg-success bg-opacity-10">' + hppAkhirHtml + '</td>' +
                            '</tr>';
                        }
                    });
                }
                $('#view_items_tbody').html(itemsHtml || '<tr><td colspan="11" class="text-center text-muted py-3">Tidak ada rincian item barang.</td></tr>');

                // Render Extra Costs & PPN
                let extraHtml = '';
                let totalBiayaLain = 0;
                let totalPpn = 0;

                if (inv.details && inv.details.length > 0) {
                    inv.details.forEach(function (d) {
                        if (d.jenis !== 'Barang') {
                            let nom = parseFloat(d.subtotal || d.harga_satuan || 0);
                            let jenis = d.jenis === 'PPN' ? ('PPN Masukan (' + (inv.pajak ? inv.pajak.tarif : 0) + '%)') : (d.jenis || d.keterangan || 'Biaya Tambahan');
                            let coaTxt = d.coa ? (d.coa.code + ' - ' + d.coa.name) : '-';
                            let hppBadge = (d.masuk_hpp === 1 || d.masuk_hpp === true || d.masuk_hpp === '1')
                                ? '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2.5 py-1"><i class="bi bi-box-arrow-in-down me-1"></i>Ya (Masuk HPP)</span>'
                                : '<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-0.5">Tidak</span>';

                            let alokasiSubTxt = '';
                            if (d.jenis === 'PPN') {
                                totalPpn += nom;
                                hppBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Pajak Masukan</span>';
                            } else {
                                totalBiayaLain += nom;
                                if (d.masuk_hpp === 1 || d.masuk_hpp === true || d.masuk_hpp === '1') {
                                    alokasiSubTxt = '<div class="extra-small text-primary mt-0.5 font-monospace"><i class="bi bi-diagram-3 me-1"></i>Dialokasikan ke HPP rincian barang di atas</div>';
                                }
                            }

                            extraHtml += '<tr>' +
                                '<td class="fw-semibold text-body">' + escapeHtml(jenis) + alokasiSubTxt + '</td>' +
                                '<td class="text-center">' + hppBadge + '</td>' +
                                '<td class="small text-muted">' + escapeHtml(coaTxt) + '</td>' +
                                '<td class="text-end fw-bold text-body">' + formatMoney(nom, curCode, curSymbol) + '</td>' +
                            '</tr>';
                        }
                    });
                }

                let pphNom = parseFloat(inv.pph_nominal || 0);
                if (pphNom > 0) {
                    let tarifTxt = inv.tarif_pph ? '(' + String(inv.tarif_pph).replace('.', ',') + '%)' : '';
                    let pphName = (inv.pph_pajak && inv.pph_pajak.nama_pajak) ? inv.pph_pajak.nama_pajak : 'PPh';
                    $('#view_label_pph_title').text('Potongan ' + pphName);
                    $('#view_label_pph_tarif').text(tarifTxt);
                    $('#view_sum_pph').text('- ' + formatNumberOnly(pphNom, curCode));
                    $('#view_row_pph').show();

                    let pphCoaTxt = inv.pph_coa ? (inv.pph_coa.code + ' - ' + inv.pph_coa.name) : '-';
                    extraHtml += '<tr>' +
                        '<td><span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle font-monospace px-2.5 py-1">Potongan ' + escapeHtml(pphName) + ' ' + escapeHtml(tarifTxt) + '</span></td>' +
                        '<td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-0.5 small">Tidak</span></td>' +
                        '<td class="small text-muted">' + escapeHtml(pphCoaTxt) + '</td>' +
                        '<td class="text-end fw-bold text-warning">- ' + formatMoney(pphNom, curCode, curSymbol) + '</td>' +
                    '</tr>';
                } else {
                    $('#view_row_pph').hide();
                }

                if (extraHtml) {
                    $('#view_extra_costs_tbody').html(extraHtml);
                    $('#view_card_extra_costs').show();
                } else {
                    $('#view_card_extra_costs').hide();
                }

                // Summary Financial Calculation
                let dpp = Math.max(0, subtotalGross - totalDiskon);
                let grandTotal = parseFloat(inv.grand_total !== undefined && inv.grand_total !== null ? inv.grand_total : Math.max(0, dpp + totalBiayaLain + totalPpn - pphNom));

                $('#view_sum_subtotal').text(formatNumberOnly(subtotalGross, curCode));
                $('#view_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, curCode));
                $('#view_sum_dpp').text(formatNumberOnly(dpp, curCode));
                $('#view_sum_ppn').text(formatNumberOnly(totalPpn, curCode));
                $('#view_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, curCode));
                $('#view_sum_total_invoice').text(formatMoney(grandTotal, curCode, curSymbol));

                let uangMukaVal = (inv.uang_muka !== null && inv.uang_muka !== undefined) ? parseFloat(inv.uang_muka) : 0;

                if (uangMukaVal > 0) {
                    $('#view_sum_uang_muka').text('- ' + formatMoney(uangMukaVal, curCode, curSymbol));
                    if (showIdrEquiv) {
                        $('#view_sum_uang_muka_idr').text('≈ -Rp ' + formatNumber(uangMukaVal * kursVal)).show();
                    } else {
                        $('#view_sum_uang_muka_idr').hide();
                    }
                    $('#view_row_uang_muka').show();

                    let sisaTagihan = Math.max(0, grandTotal - uangMukaVal);
                    $('#view_box_sisa_tagihan').show();
                    $('#view_sum_sisa_tagihan').text(formatMoney(sisaTagihan, curCode, curSymbol));

                    $('#view_pay_row_dp').show();
                    $('#view_pay_uang_muka').text(formatMoney(uangMukaVal, curCode, curSymbol));

                    let kbInfo = inv.kas_bank ? ('Via: ' + (inv.kas_bank.nama_akun || '') + ' (' + (inv.kas_bank.nama_bank || 'Kas') + ')') : '';
                    if (kbInfo) {
                        $('#view_pay_kas_bank_info').text(kbInfo).show();
                    } else {
                        $('#view_pay_kas_bank_info').hide();
                    }
                } else {
                    $('#view_row_uang_muka').hide();
                    $('#view_box_sisa_tagihan').hide();
                    $('#view_pay_row_dp').hide();
                    $('#view_pay_kas_bank_info').hide();
                }

                if (showIdrEquiv) {
                    let subtotalIdr = subtotalGross * kursVal;
                    let diskonIdr = totalDiskon * kursVal;
                    let dppIdr = dpp * kursVal;
                    let ppnIdr = totalPpn * kursVal;
                    let biayaLainIdr = totalBiayaLain * kursVal;
                    let pphIdr = pphNom * kursVal;
                    let grandTotalIdr = grandTotal * kursVal;

                    $('#view_sum_subtotal_idr').text('≈ Rp ' + formatNumber(subtotalIdr)).show();
                    $('#view_sum_diskon_idr').text(totalDiskon > 0 ? '≈ -Rp ' + formatNumber(diskonIdr) : '').toggle(totalDiskon > 0);
                    $('#view_sum_dpp_idr').text('≈ Rp ' + formatNumber(dppIdr)).show();
                    $('#view_sum_ppn_idr').text('≈ Rp ' + formatNumber(ppnIdr)).show();
                    $('#view_sum_biaya_lain_idr').text('≈ Rp ' + formatNumber(biayaLainIdr)).show();
                    $('#view_sum_pph_idr').text(pphNom > 0 ? '≈ -Rp ' + formatNumber(pphIdr) : '').toggle(pphNom > 0);

                    $('#view_box_total_idr').show();
                    $('#view_sum_total_invoice_idr').text('Rp ' + formatNumber(grandTotalIdr));
                    $('#view_sum_kurs_info').text('Kurs: ' + formatSmartKurs(kursVal));
                    if (curCode !== 'IDR') {
                        $('#view_label_total_tagihan').html('Total Tagihan (' + escapeHtml(curCode) + ')');
                    } else {
                        $('#view_label_total_tagihan').text('Total Tagihan (Grand Total)');
                    }
                } else {
                    $('#view_sum_subtotal_idr, #view_sum_diskon_idr, #view_sum_dpp_idr, #view_sum_ppn_idr, #view_sum_biaya_lain_idr, #view_sum_pph_idr').hide().empty();
                    $('#view_box_total_idr').hide();
                    $('#view_label_total_tagihan').text('Total Tagihan (Grand Total)');
                }

                // Render Payment History & Status
                let paymentsHtml = '';
                let payCount = 0;
                let totalPaid = 0;
                let totalPotongan = 0;

                if (inv.payment_details && inv.payment_details.length > 0) {
                    inv.payment_details.forEach(function (pd) {
                        let p = pd.payment;
                        if (!p || p.status === 'Cancelled' || p.status === 'Dibatalkan') return;

                        payCount++;
                        let jmlBayar = parseFloat(pd.jumlah_bayar || 0);
                        let potong = parseFloat(pd.potongan || 0);
                        if (p.status === 'Posted') {
                            totalPaid += jmlBayar;
                            totalPotongan += potong;
                        }

                        let payStatusBadge = p.status === 'Posted' 
                            ? '<span class="badge bg-success text-white">Posted</span>' 
                            : '<span class="badge bg-warning text-dark">' + p.status + '</span>';

                        paymentsHtml += '<tr>' +
                            '<td class="fw-bold font-monospace text-primary">' + escapeHtml(p.nomor_pembayaran) + '</td>' +
                            '<td class="small">' + (p.tanggal_bayar ? String(p.tanggal_bayar).substring(0, 10) : '-') + '</td>' +
                            '<td>' + escapeHtml(p.kas_bank ? p.kas_bank.nama_kas_bank : '-') + '</td>' +
                            '<td class="text-end fw-bold text-success">' + formatMoney(jmlBayar, curCode, curSymbol) + (potong > 0 ? ('<br><small class="text-info">Potongan: ' + formatMoney(potong, curCode, curSymbol) + '</small>') : '') + '</td>' +
                            '<td class="text-center">' + payStatusBadge + '</td>' +
                        '</tr>';
                    });
                }

                let sisaHutang = Math.max(0, grandTotal - uangMukaVal - totalPaid - totalPotongan);
                $('#view_pay_tagihan').text(formatMoney(grandTotal, curCode, curSymbol));
                $('#view_pay_terbayar').text(formatMoney(totalPaid, curCode, curSymbol));
                $('#view_pay_potongan').text(formatMoney(totalPotongan, curCode, curSymbol));
                $('#view_pay_sisa').text(formatMoney(sisaHutang, curCode, curSymbol));

                // Hitung Progress Pelunasan
                let totalPaidEffective = uangMukaVal + totalPaid + totalPotongan;
                let pctPaid = grandTotal > 0 ? Math.min(100, Math.round((totalPaidEffective / grandTotal) * 100)) : 0;
                $('#view_pay_progress_bar').css('width', pctPaid + '%');
                if (pctPaid >= 100) {
                    $('#view_pay_progress_bar').removeClass('bg-warning bg-secondary').addClass('bg-success');
                    $('#view_pay_progress_text').html('<i class="bi bi-check-circle-fill me-1 text-success"></i>100% LUNAS');
                } else if (pctPaid > 0) {
                    $('#view_pay_progress_bar').removeClass('bg-success bg-secondary').addClass('bg-warning');
                    $('#view_pay_progress_text').html('<i class="bi bi-pie-chart-fill me-1 text-warning"></i>' + pctPaid + '% SEBAGIAN');
                } else {
                    $('#view_pay_progress_bar').removeClass('bg-success bg-warning').addClass('bg-secondary');
                    $('#view_pay_progress_text').html('<i class="bi bi-dash-circle me-1 text-muted"></i>0% BELUM DIBAYAR');
                }

                if (paymentsHtml) {
                    $('#view_payments_tbody').html(paymentsHtml);
                    $('#view_pay_count_badge').text(payCount + ' Pembayaran');
                    $('#view_card_payments').show();
                } else {
                    $('#view_payments_tbody').html('<tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi pembayaran untuk faktur ini.</td></tr>');
                    $('#view_pay_count_badge').text('0 Pembayaran');
                    $('#view_card_payments').show();
                }

                // Render Accounting Journal (Protected by Role & Permission)
                let canViewJurnal = (res.can_view_jurnal !== undefined) 
                    ? res.can_view_jurnal 
                    : (perms.canViewJurnal !== undefined ? perms.canViewJurnal : true);

                let hasUtama = inv.jurnal && inv.jurnal.details && inv.jurnal.details.length > 0;
                let hasPembalik = inv.reversal_jurnal && inv.reversal_jurnal.details && inv.reversal_jurnal.details.length > 0;

                if (!canViewJurnal) {
                    $('#view_btn_open_jurnal_header').addClass('d-none');
                    $('#view_badge_gl_default').removeClass('d-none');
                    $('#view_jurnal_content').html(
                        '<div class="text-center py-4 text-muted">' +
                            '<div class="p-2.5 rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width: 46px; height: 46px;">' +
                                '<i class="bi bi-shield-lock-fill fs-4 text-secondary"></i>' +
                            '</div>' +
                            '<div class="fw-semibold text-body-secondary small">Akses Jurnal Terkunci</div>' +
                            '<div class="extra-small text-muted mt-1 px-3">Role akun Anda tidak memiliki hak akses untuk melihat rincian jurnal akuntansi. Hubungi Administrator.</div>' +
                        '</div>'
                    );
                } else if (hasUtama || hasPembalik) {
                    let isDefaultPembalik = (inv.status === 'Cancelled' || inv.status === 'Void') && hasPembalik;
                    let defaultJnlId = isDefaultPembalik ? inv.reversal_jurnal.id : (hasUtama ? inv.jurnal.id : inv.reversal_jurnal.id);
                    let defaultType = isDefaultPembalik ? 'pembalik' : (hasUtama ? 'utama' : 'pembalik');

                    $('#view_btn_open_jurnal_header')
                        .removeClass('d-none')
                        .data('jurnal-id', defaultJnlId)
                        .data('target-type', defaultType);
                    $('#view_badge_gl_default').addClass('d-none');

                    let tenderDisplay = (inv.jurnal ? inv.jurnal.no_tender : null) || (inv.reversal_jurnal ? inv.reversal_jurnal.no_tender : null) || tenderNomor;
                    let tenderBarHtml = '';
                    if (tenderDisplay) {
                        tenderBarHtml = '<div class="mb-2.5 p-2 px-2.5 rounded-3 border border-warning-subtle bg-warning bg-opacity-10 d-flex align-items-center justify-content-between gap-2 shadow-xs">' +
                            '<div class="d-flex align-items-center gap-2 text-warning-emphasis overflow-hidden">' +
                                '<i class="bi bi-briefcase-fill flex-shrink-0 fs-6"></i>' +
                                '<div class="extra-small text-truncate" title="' + escapeHtml(tenderDisplay) + '">' +
                                    '<span class="fw-bold">Tender:</span> <span class="font-monospace fw-semibold">' + escapeHtml(tenderDisplay) + '</span>' +
                                '</div>' +
                            '</div>' +
                            '<span class="badge bg-warning text-dark font-monospace extra-small py-0.5 px-1.5 flex-shrink-0">PROYEK</span>' +
                        '</div>';
                    }

                    // Helper render mini-table per voucher jurnal dengan layout rapi dan proporsional
                    function renderMiniJurnalBlock(j, isRev) {
                        if (!j || !j.details) return '';
                        let debSum = 0;
                        let kreSum = 0;
                        let rows = '';

                        j.details.forEach(function (jd) {
                            let coaCode = jd.account ? jd.account.code : '-';
                            let coaName = jd.account ? jd.account.name : '-';
                            let deb = parseFloat(jd.debit || 0);
                            let kre = parseFloat(jd.kredit || 0);
                            debSum += deb;
                            kreSum += kre;

                            rows += '<tr class="border-bottom border-light-subtle">' +
                                '<td class="py-2 ps-2 pe-1">' +
                                    '<div class="d-flex align-items-center gap-1.5 mb-0.5">' +
                                        '<span class="badge bg-secondary bg-opacity-15 text-body font-monospace px-1.5 py-0.5 rounded extra-small" style="font-size:0.67rem;">' + escapeHtml(coaCode) + '</span>' +
                                    '</div>' +
                                    '<div class="fw-semibold text-body text-truncate" style="max-width: 140px; font-size: 0.76rem;" title="' + escapeHtml(coaName) + '">' +
                                        escapeHtml(coaName) +
                                    '</div>' +
                                '</td>' +
                                '<td class="text-end font-monospace py-2 px-2 text-nowrap ' + (deb > 0 ? (isRev ? 'text-danger fw-bold' : 'text-success fw-bold') : 'text-muted opacity-50') + '" style="font-size:0.76rem;">' +
                                    (deb > 0 ? formatJournalDecimal(deb) : '-') +
                                '</td>' +
                                '<td class="text-end font-monospace py-2 ps-2 pe-2 text-nowrap ' + (kre > 0 ? (isRev ? 'text-primary fw-bold' : 'text-info fw-bold') : 'text-muted opacity-50') + '" style="font-size:0.76rem;">' +
                                    (kre > 0 ? formatJournalDecimal(kre) : '-') +
                                '</td>' +
                            '</tr>';
                        });

                        let badgeTheme = isRev ? 'danger' : 'primary';
                        let statusText = isRev ? 'PEMBALIKAN / REVERSAL' : 'POSTED';
                        let isBalanced = Math.abs(debSum - kreSum) < 0.01;

                        // Header Metadata (2 Baris Rapi, No Voucher Full Tanpa Terpotong)
                        let headerBox = 
                            '<div class="p-2.5 rounded-3 mb-2.5 bg-body-tertiary border shadow-xs">' +
                                '<div class="d-flex align-items-center justify-content-between gap-2 mb-1.5">' +
                                    '<div class="d-flex align-items-center gap-1.5 overflow-hidden">' +
                                        '<i class="bi ' + (isRev ? 'bi-arrow-repeat text-danger' : 'bi-journal-check text-primary') + ' fs-6 flex-shrink-0"></i>' +
                                        '<span class="font-monospace fw-bold ' + (isRev ? 'text-danger' : 'text-primary') + ' small text-truncate" title="' + escapeHtml(j.nomor_jurnal) + '">' +
                                            escapeHtml(j.nomor_jurnal) +
                                        '</span>' +
                                    '</div>' +
                                    '<span class="extra-small text-muted font-monospace flex-shrink-0">' +
                                        '<i class="bi bi-calendar3 me-1"></i>' + (j.tanggal_jurnal ? String(j.tanggal_jurnal).substring(0, 10) : '-') +
                                    '</span>' +
                                '</div>' +
                                '<div class="d-flex align-items-center justify-content-between gap-2 pt-1.5 border-top border-light-subtle">' +
                                    '<span class="badge bg-' + badgeTheme + '-subtle text-' + badgeTheme + ' border border-' + badgeTheme + '-subtle font-monospace extra-small px-2 py-0.5 rounded-pill">' +
                                        statusText +
                                    '</span>' +
                                    '<span class="badge ' + (isBalanced ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') + ' font-monospace extra-small px-2 py-0.5 rounded-pill">' +
                                        (isBalanced ? '<i class="bi bi-check2-circle me-1"></i>Balance' : '<i class="bi bi-exclamation-triangle me-1"></i>Selisih') +
                                    '</span>' +
                                '</div>' +
                            '</div>';

                        // Banner penjelasan Reversal yang ringkas dan elegan
                        let reversalBanner = isRev 
                            ? ('<div class="p-2 px-2.5 rounded-3 mb-2.5 d-flex align-items-center gap-2 border border-danger-subtle shadow-xs" style="background: rgba(239, 68, 68, 0.08);">' +
                                    '<i class="bi bi-exclamation-octagon-fill text-danger fs-6 flex-shrink-0"></i>' +
                                    '<div class="extra-small text-danger fw-medium" style="font-size: 0.72rem; line-height: 1.35;">' +
                                        'Mutasi pembalik untuk menetralkan saldo transaksi faktur yang dibatalkan (VOID).' +
                                    '</div>' +
                               '</div>')
                            : tenderBarHtml;

                        return headerBox +
                            reversalBanner +
                            '<div class="table-responsive rounded-2 border overflow-hidden mb-2.5 bg-body shadow-xs">' +
                                '<table class="table table-sm align-middle mb-0" style="font-size:0.75rem;">' +
                                    '<thead>' +
                                        '<tr class="bg-body-tertiary text-muted border-bottom" style="font-size:0.68rem; letter-spacing:0.04em;">' +
                                            '<th class="py-2 ps-2 pe-1 text-uppercase fw-bold">Akun COA</th>' +
                                            '<th class="py-2 px-2 text-end text-uppercase fw-bold" style="width: 85px;">Debit</th>' +
                                            '<th class="py-2 ps-2 pe-2 text-end text-uppercase fw-bold" style="width: 85px;">Kredit</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>' +
                                        rows +
                                    '</tbody>' +
                                    '<tfoot class="bg-body-tertiary border-top fw-bold">' +
                                        '<tr>' +
                                            '<td class="py-2 ps-2 pe-1 text-body-secondary text-uppercase extra-small" style="font-size:0.68rem;">Total</td>' +
                                            '<td class="text-end font-monospace py-2 px-2 text-success text-nowrap" style="font-size:0.76rem;">' + formatJournalDecimal(debSum) + '</td>' +
                                            '<td class="text-end font-monospace py-2 ps-2 pe-2 text-primary text-nowrap" style="font-size:0.76rem;">' + formatJournalDecimal(kreSum) + '</td>' +
                                        '</tr>' +
                                    '</tfoot>' +
                                '</table>' +
                            '</div>' +
                            '<div class="pt-1">' +
                                '<button type="button" class="btn ' + (isRev ? 'btn-outline-danger' : 'btn-outline-primary') + ' btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-2 fw-semibold btn-open-modal-jurnal-pi shadow-xs py-2" data-jurnal-id="' + j.id + '" data-target-type="' + (isRev ? 'pembalik' : 'utama') + '" style="font-size:0.78rem;">' +
                                    '<i class="bi ' + (isRev ? 'bi-arrow-repeat' : 'bi-arrows-fullscreen') + ' fs-6"></i>' +
                                    '<span>Buka Voucher ' + (isRev ? 'Jurnal Pembalik' : 'Jurnal Faktur') + '</span>' +
                                '</button>' +
                            '</div>';
                    }

                    let jCardHtml = '';
                    if (hasUtama && hasPembalik) {
                        jCardHtml = '<div class="mb-3">' +
                            '<div class="p-1 rounded-pill border bg-body-tertiary d-flex gap-1" style="background: rgba(15, 23, 42, 0.35) !important;">' +
                                '<button type="button" class="btn btn-sm flex-fill rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-1.5 pi-mini-tab-btn ' + (!isDefaultPembalik ? 'btn-primary active text-white shadow-xs' : 'btn-ghost text-muted') + '" data-target="utama" style="font-size:0.74rem; padding: 0.35rem 0.65rem;">' +
                                    '<i class="bi bi-journal-text"></i>' +
                                    '<span>Jurnal Faktur</span>' +
                                '</button>' +
                                '<button type="button" class="btn btn-sm flex-fill rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-1.5 pi-mini-tab-btn ' + (isDefaultPembalik ? 'btn-danger active text-white shadow-xs' : 'btn-ghost text-muted') + '" data-target="pembalik" style="font-size:0.74rem; padding: 0.35rem 0.65rem;">' +
                                    '<i class="bi bi-arrow-repeat"></i>' +
                                    '<span>Pembalik (Void)</span>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div id="pi_card_view_utama" class="' + (isDefaultPembalik ? 'd-none' : '') + '">' + renderMiniJurnalBlock(inv.jurnal, false) + '</div>' +
                        '<div id="pi_card_view_pembalik" class="' + (!isDefaultPembalik ? 'd-none' : '') + '">' + renderMiniJurnalBlock(inv.reversal_jurnal, true) + '</div>';
                    } else if (hasPembalik) {
                        jCardHtml = renderMiniJurnalBlock(inv.reversal_jurnal, true);
                    } else {
                        jCardHtml = renderMiniJurnalBlock(inv.jurnal, false);
                    }

                    $('#view_jurnal_content').html(jCardHtml);
                } else {
                    $('#view_btn_open_jurnal_header').addClass('d-none');
                    $('#view_badge_gl_default').removeClass('d-none');
                    $('#view_jurnal_content').html('<div class="small text-muted text-center py-3">Belum ada jurnal akuntansi diterbitkan (Status Draft).</div>');
                }

                $('#modalDetailInvoice').modal('show');
            }
        });
    });

    // Action button triggers from view modal
    $('#view_btn_edit_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-edit-invoice[data-id="' + id + '"]').first().trigger('click');
        }, 300);
    });

    $('#view_btn_submit_approval_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-submit-approval-invoice[data-id="' + id + '"]').first().trigger('click');
        }, 300);
    });

    $('#view_btn_approve_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-approve-invoice[data-id="' + id + '"]').first().trigger('click');
        }, 300);
    });

    $('#view_btn_reject_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-reject-invoice[data-id="' + id + '"]').first().trigger('click');
        }, 300);
    });

    $('#view_btn_cancel_trigger').on('click', function () {
        let id = $(this).data('id');
        let noFaktur = currentViewInvoice ? (currentViewInvoice.nomor_faktur || '') : '';
        if (typeof window.promptCancelInvoice === 'function') {
            window.promptCancelInvoice(id, noFaktur);
        }
    });

    $('#view_btn_wa_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-wa-invoice[data-id="' + id + '"]').first().trigger('click');
        }, 300);
    });

    // Event Handler Buka Modal Popup Jurnal Akuntansi Faktur
    $(document).on('click', '.btn-open-modal-jurnal-pi', function (e) {
        e.preventDefault();
        let perms = window.userInvoicePermissions || {};
        if (perms.canViewJurnal === false) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Dibatasi',
                    text: 'Role akun Anda tidak memiliki hak akses untuk melihat voucher jurnal akuntansi.',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('Role akun Anda tidak memiliki hak akses untuk melihat voucher jurnal akuntansi.');
            }
            return;
        }

        let targetType = $(this).data('target-type') || 'utama'; // 'utama' or 'pembalik'
        let explicitJurnalId = $(this).data('jurnal-id');
        let invoiceId = $(this).data('invoice-id') || (currentViewInvoice ? currentViewInvoice.id : null);

        // Tampilkan modal jurnal
        $('#modalJurnalInvoice').modal('show');

        // Skenario 1: Data jurnal sudah lengkap di memori (Instant render tanpa loading)
        if (currentViewInvoice) {
            let targetJurnal = null;
            let isRev = (targetType === 'pembalik');

            if (targetType === 'pembalik' && currentViewInvoice.reversal_jurnal) {
                targetJurnal = currentViewInvoice.reversal_jurnal;
            } else if (explicitJurnalId && currentViewInvoice.reversal_jurnal && currentViewInvoice.reversal_jurnal.id == explicitJurnalId) {
                targetJurnal = currentViewInvoice.reversal_jurnal;
                isRev = true;
            } else if (currentViewInvoice.jurnal) {
                targetJurnal = currentViewInvoice.jurnal;
                isRev = false;
            }

            if (targetJurnal && targetJurnal.details && targetJurnal.details.length > 0) {
                populateModalJurnal(targetJurnal, currentViewInvoice, isRev);
                return;
            }
        }

        // Skenario 2: Belum di memori, tampilkan loading state dan fetch dari endpoint controller
        $('#pi_jurnal_loading').removeClass('d-none');
        $('#pi_jurnal_content').addClass('d-none');

        let url = invoiceId 
            ? ('/purchase-invoices/' + invoiceId + '/jurnal-data')
            : (explicitJurnalId ? ('/accounting/jurnal-umum/' + explicitJurnalId) : null);

        if (!url) {
            $('#pi_jurnal_loading').html('<div class="alert alert-warning mb-0 text-center py-3"><i class="bi bi-info-circle fs-4 d-block mb-1 text-warning"></i><span>Jurnal akuntansi belum diterbitkan untuk faktur ini.</span></div>');
            return;
        }

        $.get(url, function (res) {
            if (res && res.success) {
                let j = null;
                let isRev = (targetType === 'pembalik');

                if (res.jurnal || res.reversal_jurnal) {
                    currentViewInvoice = currentViewInvoice || {};
                    currentViewInvoice.jurnal = res.jurnal;
                    currentViewInvoice.reversal_jurnal = res.reversal_jurnal;
                    if (res.invoice) {
                        currentViewInvoice.nomor_faktur = res.invoice.nomor_faktur;
                        currentViewInvoice.nomor_faktur_supplier = res.invoice.nomor_faktur_supplier;
                    }

                    if (targetType === 'pembalik' && res.reversal_jurnal) {
                        j = res.reversal_jurnal;
                        isRev = true;
                    } else {
                        j = res.jurnal || res.reversal_jurnal;
                        isRev = (j === res.reversal_jurnal);
                    }
                } else if (res.data) {
                    j = res.data;
                }

                if (j) {
                    populateModalJurnal(j, currentViewInvoice, isRev);
                } else {
                    $('#pi_jurnal_loading').html('<div class="alert alert-info mb-0 text-center py-3"><span>Tidak ada rincian jurnal ditemukan.</span></div>');
                }
            } else {
                $('#pi_jurnal_loading').html('<div class="alert alert-warning mb-0 text-center py-3"><span>Gagal memuat rincian jurnal akuntansi.</span></div>');
            }
        }).fail(function (xhr) {
            // Fallback: Jika endpoint purchase-invoices jurnal-data gagal, coba endpoint accounting jurnal-umum
            if (explicitJurnalId && !url.includes('accounting/jurnal-umum')) {
                $.get('/accounting/jurnal-umum/' + explicitJurnalId, function (res) {
                    if (res && res.success && res.data) {
                        populateModalJurnal(res.data, currentViewInvoice, (targetType === 'pembalik'));
                    } else {
                        renderJurnalError(xhr);
                    }
                }).fail(function (xhrFallback) {
                    renderJurnalError(xhrFallback);
                });
            } else {
                renderJurnalError(xhr);
            }
        });

        function renderJurnalError(xhrObj) {
            let msg = (xhrObj.responseJSON && xhrObj.responseJSON.message) 
                ? xhrObj.responseJSON.message 
                : 'Anda tidak memiliki hak akses atau gagal memuat rincian jurnal.';
            $('#pi_jurnal_loading').html('<div class="alert alert-warning mb-0 text-center py-3"><i class="bi bi-shield-lock fs-4 d-block mb-1 text-warning"></i><span>' + escapeHtml(msg) + '</span></div>');
        }
    });

    // Handler Tab Switcher di dalam Modal Voucher Jurnal (Toggle Faktur Asli vs Reversal)
    $(document).on('click', '.pi-tab-jurnal-toggle', function () {
        let target = $(this).data('target'); // 'utama' or 'pembalik'
        $('.pi-tab-jurnal-toggle').removeClass('active shadow-xs');
        $(this).addClass('active shadow-xs');

        if (currentViewInvoice) {
            if (target === 'pembalik' && currentViewInvoice.reversal_jurnal) {
                populateModalJurnal(currentViewInvoice.reversal_jurnal, currentViewInvoice, true);
            } else if (currentViewInvoice.jurnal) {
                populateModalJurnal(currentViewInvoice.jurnal, currentViewInvoice, false);
            }
        }
    });

    // Handler Mini Tab Switcher di dalam View Detail Faktur
    $(document).on('click', '.pi-mini-tab-btn', function () {
        let target = $(this).data('target');
        $('.pi-mini-tab-btn').removeClass('active btn-primary btn-danger text-white shadow-xs').addClass('btn-ghost text-muted');

        if (target === 'pembalik') {
            $(this).removeClass('btn-ghost text-muted').addClass('btn-danger active text-white shadow-xs');
            $('#pi_card_view_utama').addClass('d-none');
            $('#pi_card_view_pembalik').removeClass('d-none');
            if (currentViewInvoice && currentViewInvoice.reversal_jurnal) {
                $('#view_btn_open_jurnal_header')
                    .data('jurnal-id', currentViewInvoice.reversal_jurnal.id)
                    .data('target-type', 'pembalik');
            }
        } else {
            $(this).removeClass('btn-ghost text-muted').addClass('btn-primary active text-white shadow-xs');
            $('#pi_card_view_pembalik').addClass('d-none');
            $('#pi_card_view_utama').removeClass('d-none');
            if (currentViewInvoice && currentViewInvoice.jurnal) {
                $('#view_btn_open_jurnal_header')
                    .data('jurnal-id', currentViewInvoice.jurnal.id)
                    .data('target-type', 'utama');
            }
        }
    });

    function populateModalJurnal(jurnal, invoice, isReversal) {
        invoice = invoice || {};
        if (!jurnal) return;

        // Deteksi apakah voucher ini adalah jurnal pembalik
        if (isReversal === undefined) {
            isReversal = (jurnal.referensi_tipe === 'Purchase Invoice Reversal') || 
                         (jurnal.keterangan && String(jurnal.keterangan).toUpperCase().includes('PEMBALIKAN'));
        }

        // Header Modal: Judul, Icon, Badge & Notice Reversal
        if (isReversal) {
            $('#modalJurnalInvoiceLabel').text('Jurnal Pembalik (Reversal) Faktur Pembelian');
            $('#pi_jurnal_status_badge')
                .removeClass('bg-success-subtle text-success border-success-subtle')
                .addClass('bg-danger-subtle text-danger border-danger-subtle')
                .text('PEMBALIKAN / REVERSAL');
            $('#pi_jurnal_header_icon')
                .removeClass('bi-journal-check text-info')
                .addClass('bi-arrow-repeat text-danger');
            $('#pi_jurnal_reversal_notice').removeClass('d-none');
            $('#pi_jurnal_footer_note_icon')
                .removeClass('bi-shield-check text-success')
                .addClass('bi-arrow-counterclockwise text-danger');
            $('#pi_jurnal_footer_note_text').text('Voucher jurnal pembalik ini diterbitkan otomatis untuk menetralkan mutasi akun karena faktur dibatalkan (VOID).');
        } else {
            $('#modalJurnalInvoiceLabel').text('Jurnal Akuntansi Faktur Pembelian');
            $('#pi_jurnal_status_badge')
                .removeClass('bg-danger-subtle text-danger border-danger-subtle')
                .addClass('bg-success-subtle text-success border-success-subtle')
                .text('POSTED');
            $('#pi_jurnal_header_icon')
                .removeClass('bi-arrow-repeat text-danger')
                .addClass('bi-journal-check text-info');
            $('#pi_jurnal_reversal_notice').addClass('d-none');
            $('#pi_jurnal_footer_note_icon')
                .removeClass('bi-arrow-counterclockwise text-danger')
                .addClass('bi-shield-check text-success');
            $('#pi_jurnal_footer_note_text').text('Voucher jurnal ini diterbitkan otomatis oleh sistem ERP saat Faktur Pembelian disetujui (ACC).');
        }

        // Tampilkan Tab Switcher jika memiliki kedua jurnal (Asli & Pembalik)
        let hasBoth = invoice.jurnal && invoice.reversal_jurnal;
        if (hasBoth) {
            $('#pi_jurnal_tabs_container').removeClass('d-none');
            $('#pi_tab_jurnal_utama_no').text(invoice.jurnal.nomor_jurnal || 'JNL');
            $('#pi_tab_jurnal_pembalik_no').text(invoice.reversal_jurnal.nomor_jurnal || 'REV');

            if (isReversal) {
                $('#pi_tab_jurnal_utama').removeClass('active shadow-xs');
                $('#pi_tab_jurnal_pembalik').addClass('active shadow-xs');
            } else {
                $('#pi_tab_jurnal_pembalik').removeClass('active shadow-xs');
                $('#pi_tab_jurnal_utama').addClass('active shadow-xs');
            }
        } else {
            $('#pi_jurnal_tabs_container').addClass('d-none');
        }

        $('#pi_jurnal_nomor').text(jurnal.nomor_jurnal || '-');

        // Tanggal Jurnal
        let tglJurnal = jurnal.tanggal_jurnal ? String(jurnal.tanggal_jurnal).substring(0, 10) : '-';
        $('#pi_jurnal_tanggal').text(tglJurnal);

        // Nomor Faktur & Supplier
        let noFakturInternal = invoice.nomor_faktur || (jurnal.referensi_tipe === 'Purchase Invoice' ? ('INV #' + jurnal.referensi_id) : '-');
        let noFakturSupplier = invoice.nomor_faktur_supplier || '-';
        let namaSupplier = (invoice.supplier ? invoice.supplier.nama_supplier : null) || '-';

        $('#pi_jurnal_ref_faktur').text(noFakturInternal);
        $('#pi_jurnal_ref_supplier_inv').text(noFakturSupplier);
        $('#pi_jurnal_supplier').text(namaSupplier);
        $('#pi_jurnal_keterangan').text(jurnal.keterangan || (isReversal ? ('Jurnal Pembalik Faktur ' + noFakturInternal) : ('Jurnal Pembelian Faktur ' + noFakturInternal)));

        // TENDER / PROYEK HIGHLIGHT CARD
        let tenderObj = invoice.tender || (invoice.goods_receipt && invoice.goods_receipt.tender ? invoice.goods_receipt.tender : (invoice.po ? invoice.po.tender : null));
        let tenderCode = jurnal.no_tender || (tenderObj ? ((tenderObj.nomor_kontrak || tenderObj.nomor_tender || ('Tender #' + tenderObj.id)) + (tenderObj.nama_tender ? ' - ' + tenderObj.nama_tender : '')) : null);

        if (tenderCode && String(tenderCode).trim() !== '' && String(tenderCode).trim() !== '-') {
            $('#pi_jurnal_tender_title').text(tenderCode);
            let custName = tenderObj ? (tenderObj.instansi || tenderObj.customer || tenderObj.nama_instansi || tenderObj.klien || '') : '';
            if (custName && custName.trim() !== '' && custName.trim() !== '-') {
                $('#pi_jurnal_tender_customer').text(custName);
                $('#pi_jurnal_tender_customer_wrapper').show();
            } else {
                $('#pi_jurnal_tender_customer_wrapper').hide();
            }
            $('#pi_jurnal_tender_card').removeClass('d-none');
        } else {
            $('#pi_jurnal_tender_card').addClass('d-none');
        }

        // TABEL DETAIL AKUN (DOUBLE ENTRY)
        let tbodyHtml = '';
        let totDeb = 0;
        let totKre = 0;
        let details = jurnal.details || [];

        if (details.length > 0) {
            details.forEach(function (d, idx) {
                let deb = parseFloat(d.debit || 0);
                let kre = parseFloat(d.kredit || 0);
                totDeb += deb;
                totKre += kre;

                let coaCode = d.account ? d.account.code : '-';
                let coaName = d.account ? d.account.name : '-';
                let rowKet = d.keterangan || '-';

                tbodyHtml += '<tr>' +
                    '<td class="text-center font-monospace text-muted py-2 px-3">' + (idx + 1) + '</td>' +
                    '<td class="font-monospace fw-bold text-primary py-2 px-3 text-nowrap">' + escapeHtml(coaCode) + '</td>' +
                    '<td class="py-2 px-3 fw-semibold text-body-emphasis">' + escapeHtml(coaName) + '</td>' +
                    '<td class="text-end font-monospace py-2 px-3 text-nowrap ' + (deb > 0 ? (isReversal ? 'text-danger fw-bold' : 'text-success fw-bold') : 'text-muted') + '">' +
                        (deb > 0 ? ('Rp ' + formatJournalDecimal(deb)) : '-') +
                    '</td>' +
                    '<td class="text-end font-monospace py-2 px-3 text-nowrap ' + (kre > 0 ? 'text-primary fw-bold' : 'text-muted') + '">' +
                        (kre > 0 ? ('Rp ' + formatJournalDecimal(kre)) : '-') +
                    '</td>' +
                    '<td class="py-2 px-3 small text-body-secondary">' + escapeHtml(rowKet) + '</td>' +
                '</tr>';
            });
        } else {
            tbodyHtml = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada rincian baris akun.</td></tr>';
        }

        $('#pi_jurnal_items_tbody').html(tbodyHtml);
        $('#pi_jurnal_total_debit').text('Rp ' + formatJournalDecimal(totDeb));
        $('#pi_jurnal_total_kredit').text('Rp ' + formatJournalDecimal(totKre));

        let isBalanced = Math.abs(totDeb - totKre) < 0.01;
        if (isBalanced) {
            $('#pi_jurnal_balance_badge').html('<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1"><i class="bi bi-check-circle me-1"></i>100% Balance</span>');
        } else {
            let selisih = Math.abs(totDeb - totKre);
            $('#pi_jurnal_balance_badge').html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2.5 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Selisih Rp ' + formatJournalDecimal(selisih) + '</span>');
        }

        $('#pi_jurnal_loading').addClass('d-none');
        $('#pi_jurnal_content').removeClass('d-none');
    }

    // ===== HANDLER MODAL PREVIEW FOTO / DOKUMEN BUKTI FAKTUR =====
    window.bukaModalPreviewLampiran = function(fileUrl, titleText) {
        if (!fileUrl) return;
        titleText = titleText || 'Bukti Faktur Supplier';
        $('#preview_lampiran_title').text('Bukti Faktur — ' + titleText);
        $('#preview_lampiran_download_btn').attr('href', fileUrl);

        let isImg = /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(fileUrl);
        if (isImg) {
            $('#preview_lampiran_img').attr('src', fileUrl);
            $('#preview_lampiran_img_wrapper').removeClass('d-none');
            $('#preview_lampiran_iframe').attr('src', '');
            $('#preview_lampiran_pdf_wrapper').addClass('d-none');
        } else {
            $('#preview_lampiran_img').attr('src', '');
            $('#preview_lampiran_img_wrapper').addClass('d-none');
            $('#preview_lampiran_iframe').attr('src', fileUrl);
            $('#preview_lampiran_pdf_wrapper').removeClass('d-none');
        }

        $('#modalPreviewLampiranInvoice').modal('show');
    };

    $(document).on('click', '.btn-preview-lampiran', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let title = $(this).data('title');
        window.bukaModalPreviewLampiran(url, title);
    });

    // Pertahankan class modal-open pada body jika modal detail masih aktif saat modal preview ditutup
    $('#modalPreviewLampiranInvoice').on('hidden.bs.modal', function () {
        if ($('#modalDetailInvoice').hasClass('show') || $('#modalEditInvoice').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });
});
</script>
