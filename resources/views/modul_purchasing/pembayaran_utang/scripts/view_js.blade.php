<script>
$(document).ready(function () {

    function formatRp(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dtStr) {
        if (!dtStr) return '-';
        let d = new Date(dtStr);
        if (isNaN(d.getTime())) return dtStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDateTime(dtStr) {
        if (!dtStr) return '-';
        let d = new Date(dtStr);
        if (isNaN(d.getTime())) return dtStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    // ===== OPEN VIEW MODAL =====
    $(document).on('click', '.btn-view-payment', function () {
        let paymentId = $(this).data('id');

        Swal.fire({
            title: 'Memuat Detail Pembayaran...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/purchase-payments/${paymentId}`,
            type: 'GET',
            success: function (res) {
                Swal.close();
                let p = res.payment;

                let curCode = p.currency?.kode_currency || 'IDR';
                let curSymbol = p.currency?.simbol || (curCode === 'IDR' ? 'Rp' : curCode);
                let kurs = parseFloat(p.kurs || 1);
                let isForeign = (curCode !== 'IDR' && kurs > 1);

                // Status Badge & Alerts
                let statusBadgeHtml = '';
                if (p.status === 'Draft') {
                    statusBadgeHtml = '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    $('#viewCancelledAlert').addClass('d-none').removeClass('d-flex');
                    $('#viewCancelledRow').addClass('d-none').removeClass('d-flex');
                } else if (p.status === 'Diajukan') {
                    statusBadgeHtml = '<span class="badge bg-primary text-white border border-primary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-clock-history me-1"></i>Diajukan</span>';
                    $('#viewCancelledAlert').addClass('d-none').removeClass('d-flex');
                    $('#viewCancelledRow').addClass('d-none').removeClass('d-flex');
                } else if (p.status === 'Ditolak') {
                    statusBadgeHtml = '<span class="badge bg-danger text-white border border-danger rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>';
                    $('#viewCancelledAlert').removeClass('d-none').addClass('d-flex');
                    $('#viewCancelledReason').text(p.rejection_reason ? `Alasan Penolakan: ${p.rejection_reason}` : 'Pengajuan pembayaran ditolak.');
                    $('#viewCancelledRow').addClass('d-none').removeClass('d-flex');
                } else if (p.status === 'Posted') {
                    statusBadgeHtml = '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Diposting</span>';
                    $('#viewCancelledAlert').addClass('d-none').removeClass('d-flex');
                    $('#viewCancelledRow').addClass('d-none').removeClass('d-flex');
                } else if (p.status === 'Cancelled') {
                    statusBadgeHtml = '<span class="badge bg-secondary text-white border border-secondary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-slash-circle me-1"></i>Dibatalkan</span>';
                    $('#viewCancelledAlert').removeClass('d-none').addClass('d-flex');
                    $('#viewCancelledReason').text(p.alasan_pembatalan ? `Alasan Pembatalan: ${p.alasan_pembatalan}` : 'Pembayaran telah dibatalkan.');
                    $('#viewCancelledRow').removeClass('d-none').addClass('d-flex');
                    $('#viewCancelledBy').text(p.canceller?.name || p.canceller?.nama_lengkap || 'System');
                }
                $('#viewStatusBadge').html(statusBadgeHtml);

                // Header Information
                $('#viewNomorPembayaran').text(p.nomor_pembayaran);
                $('#viewNomorReferensi').text(p.nomor_referensi ? `Ref: ${p.nomor_referensi}` : '');
                $('#viewTanggalBayar').text(formatDate(p.tanggal_bayar));
                $('#viewMetodeBayar').text(p.payment_method?.nama_metode || 'Transfer Bank');
                $('#viewSupplierNama').text(p.supplier?.nama_supplier || '-');
                $('#viewSupplierKode').text(p.supplier?.kode_supplier ? `Kode: ${p.supplier.kode_supplier}` : '');

                let kasBankStr = p.kas_bank?.nama_akun || '-';
                if (p.kas_bank?.nomor_rekening) kasBankStr += ` (${p.kas_bank.nomor_rekening})`;
                $('#viewKasBankNama').text(kasBankStr);
                $('#viewKasBankCoa').text(p.kas_bank?.coa ? `COA: ${p.kas_bank.coa.code} - ${p.kas_bank.coa.name}` : '');

                // Invoices Table
                let tbody = $('#viewInvoicesTbody');
                tbody.empty();

                let totBayar = 0;
                let totPotongan = 0;
                let totSisaAkhir = 0;

                if (p.details && p.details.length > 0) {
                    p.details.forEach(function (det, idx) {
                        let inv = det.invoice;
                        let bayar = parseFloat(det.jumlah_bayar || 0);
                        let potong = parseFloat(det.potongan || 0);
                        let sisaAkhir = parseFloat(det.sisa_akhir || 0);

                        totBayar += bayar;
                        totPotongan += potong;
                        totSisaAkhir += sisaAkhir;

                        let totFakturStr = isForeign 
                            ? `<div class="fw-bold">${curSymbol} ${Number(det.total_faktur).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(det.total_faktur * kurs)}</small>` 
                            : formatRp(det.total_faktur);

                        let sisaUtangStr = isForeign 
                            ? `<div class="fw-bold text-muted">${curSymbol} ${Number(det.sisa_utang).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(det.sisa_utang * kurs)}</small>` 
                            : formatRp(det.sisa_utang);

                        let bayarStr = isForeign 
                            ? `<div class="fw-bold text-primary">${curSymbol} ${Number(bayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(bayar * kurs)}</small>` 
                            : formatRp(bayar);

                        let potongStr = (potong > 0) 
                            ? (isForeign 
                                ? `<div class="fw-bold text-danger">${curSymbol} ${Number(potong).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(potong * kurs)}</small>` 
                                : formatRp(potong)) 
                            : '-';

                        let sisaAkhirStr = isForeign 
                            ? (sisaAkhir <= 0.01 
                                ? `<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (${curSymbol} 0)</span>` 
                                : `<div class="fw-bold text-warning">${curSymbol} ${Number(sisaAkhir).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(sisaAkhir * kurs)}</small>`) 
                            : (sisaAkhir <= 0.01 
                                ? `<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (Rp 0)</span>` 
                                : formatRp(sisaAkhir));

                        let tr = `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>
                                    <div class="fw-bold text-dark">${inv?.nomor_faktur || '-'}</div>
                                    <small class="text-muted">No. Supp: ${inv?.nomor_faktur_supplier || '-'}</small>
                                </td>
                                <td>${inv ? formatDate(inv.tanggal_faktur) : '-'}</td>
                                <td>${inv ? formatDate(inv.tanggal_jatuh_tempo) : '-'}</td>
                                <td class="text-end">${totFakturStr}</td>
                                <td class="text-end">${sisaUtangStr}</td>
                                <td class="text-end">${bayarStr}</td>
                                <td class="text-end">${potongStr}</td>
                                <td class="text-end">${sisaAkhirStr}</td>
                                <td>${det.catatan || '-'}</td>
                            </tr>
                        `;
                        tbody.append(tr);
                    });
                } else {
                    tbody.append('<tr><td colspan="10" class="text-center text-muted py-3">Tidak ada faktur yang dibayar</td></tr>');
                }

                // Summary Totals
                if (isForeign) {
                    $('#viewTotalFaktur').html(`<strong>${curSymbol} ${Number(p.total_faktur).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong> <small class="text-muted">(${formatRp(p.total_faktur * kurs)})</small>`);
                    $('#viewTotalPotongan').html(`<strong>${curSymbol} ${Number(p.total_potongan).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong> <small class="text-muted">(${formatRp(p.total_potongan * kurs)})</small>`);
                    $('#viewTotalBayar').html(`<strong>${curSymbol} ${Number(p.total_bayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong> <small class="text-muted">(${formatRp(p.total_bayar * kurs)})</small>`);
                    $('#viewTotalSisaAkhir').html(`<strong>${curSymbol} ${Number(totSisaAkhir).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong> <small class="text-muted">(${formatRp(totSisaAkhir * kurs)})</small>`);
                    $('#viewSummaryGrandTotal').html(`${curSymbol} ${Number(p.total_bayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})} <small class="d-block text-white-50" style="font-size:0.8rem;">≈ ${formatRp(p.total_bayar * kurs)}</small>`);
                } else {
                    $('#viewTotalFaktur').text(formatRp(p.total_faktur));
                    $('#viewTotalPotongan').text(formatRp(p.total_potongan));
                    $('#viewTotalBayar').text(formatRp(p.total_bayar));
                    $('#viewTotalSisaAkhir').text(formatRp(totSisaAkhir));
                    $('#viewSummaryGrandTotal').text(formatRp(p.total_bayar));
                }

                // Keterangan / Catatan
                $('#viewKeterangan').text(p.keterangan || 'Tidak ada catatan tambahan.');

                // Lampiran Bukti Pembayaran
                if (p.lampiran) {
                    let fileUrl = p.lampiran.startsWith('http') ? p.lampiran : `/storage/${p.lampiran}`;
                    let fileName = p.lampiran.split('/').pop();

                    $('#viewLampiranWrapper').removeClass('d-none');
                    $('#viewLampiranImg').attr('src', fileUrl);

                    // Bind click event untuk memperbesar foto menggunakan modal preview
                    $('#viewLampiranClickContainer, #viewLampiranModalBtn, #viewLampiranImg').off('click').on('click', function (e) {
                        e.preventDefault();
                        if (typeof window.openImagePreviewModal === 'function') {
                            window.openImagePreviewModal(fileUrl, `Bukti Pembayaran ${p.nomor_pembayaran}`, fileName);
                        } else {
                            window.open(fileUrl, '_blank');
                        }
                    });
                } else {
                    $('#viewLampiranWrapper').addClass('d-none');
                    $('#viewLampiranImg').attr('src', '');
                }

                // Jurnal Akuntansi Table
                if (p.jurnal && p.jurnal.details && p.jurnal.details.length > 0) {
                    $('#viewJurnalWrapper').removeClass('d-none');
                    $('#viewJurnalDraftNotice').addClass('d-none').removeClass('d-flex');
                    $('#viewNomorJurnal').text(p.jurnal.nomor_jurnal);

                    let jTbody = $('#viewJurnalTbody');
                    jTbody.empty();
                    p.jurnal.details.forEach(function (jDet) {
                        let debitStr = parseFloat(jDet.debit) > 0 ? formatRp(jDet.debit) : '-';
                        let kreditStr = parseFloat(jDet.kredit) > 0 ? formatRp(jDet.kredit) : '-';
                        let accountStr = jDet.account ? `${jDet.account.code} - ${jDet.account.name}` : '-';

                        jTbody.append(`
                            <tr>
                                <td>${accountStr}</td>
                                <td class="text-end fw-semibold text-primary">${debitStr}</td>
                                <td class="text-end fw-semibold text-danger">${kreditStr}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#viewJurnalWrapper').addClass('d-none');
                    if (p.status === 'Draft' || p.status === 'Diajukan' || p.status === 'Ditolak') {
                        $('#viewJurnalDraftNotice').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#viewJurnalDraftNotice').addClass('d-none').removeClass('d-flex');
                    }
                }

                // Jurnal Pembalik (Reversal Journal) jika transaksi Dibatalkan
                if (p.reversal_jurnal && p.reversal_jurnal.details && p.reversal_jurnal.details.length > 0) {
                    $('#viewReversalJurnalWrapper').removeClass('d-none');
                    $('#viewNomorReversalJurnal').text(p.reversal_jurnal.nomor_jurnal);

                    let rTbody = $('#viewReversalJurnalTbody');
                    rTbody.empty();
                    p.reversal_jurnal.details.forEach(function (rDet) {
                        let debitStr = parseFloat(rDet.debit) > 0 ? formatRp(rDet.debit) : '-';
                        let kreditStr = parseFloat(rDet.kredit) > 0 ? formatRp(rDet.kredit) : '-';
                        let accountStr = rDet.account ? `${rDet.account.code} - ${rDet.account.name}` : '-';

                        rTbody.append(`
                            <tr>
                                <td>${accountStr}</td>
                                <td class="text-end fw-semibold text-success">${debitStr}</td>
                                <td class="text-end fw-semibold text-danger">${kreditStr}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#viewReversalJurnalWrapper').addClass('d-none');
                }

                function getInitials(name) {
                    if (!name || name === 'System') return 'SY';
                    let parts = name.trim().split(/\s+/);
                    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
                }

                // 1. Dibuat Oleh
                let creatorName = p.creator?.name || p.creator?.nama_lengkap || 'System';
                $('#viewCreatedBy').text(creatorName);
                $('#viewCreatedAt').text(formatDateTime(p.created_at));
                $('#viewCreatedAvatar').text(getInitials(creatorName));

                // 2. Diajukan Oleh (Submitted)
                if (p.submitted_at || p.submitter || p.status === 'Diajukan' || p.status === 'Posted' || p.status === 'Ditolak') {
                    $('#viewSubmittedItem').removeClass('d-none');
                    let submitterName = p.submitter?.name || p.submitter?.nama_lengkap || creatorName;
                    $('#viewSubmittedBy').text(submitterName);
                    $('#viewSubmittedAt').text(formatDateTime(p.submitted_at || p.created_at));
                    $('#viewSubmittedAvatar').text(getInitials(submitterName));
                } else {
                    $('#viewSubmittedItem').addClass('d-none');
                }

                // 3. Ditolak Oleh (Rejected)
                if (p.status === 'Ditolak' || p.rejected_at || p.rejecter) {
                    $('#viewRejectedItem').removeClass('d-none');
                    let rejecterName = p.rejecter?.name || p.rejecter?.nama_lengkap || 'Manajemen';
                    $('#viewRejectedBy').text(rejecterName);
                    $('#viewRejectedAt').text(formatDateTime(p.rejected_at || p.updated_at));
                    $('#viewRejectedAvatar').text(getInitials(rejecterName));
                    $('#viewRejectedReasonAudit').text(p.rejection_reason || 'Tanpa catatan alasan.');
                } else {
                    $('#viewRejectedItem').addClass('d-none');
                }

                // 4. Disetujui & Diposting Oleh (Approver / Poster)
                if (p.posted_at || p.poster || p.approved_at || p.approver || p.status === 'Posted' || p.status === 'Cancelled') {
                    $('#viewPostedItem').removeClass('d-none');
                    let approverName = p.approver?.name || p.approver?.nama_lengkap || p.poster?.name || p.poster?.nama_lengkap || 'Manajemen';
                    $('#viewPostedBy').text(approverName);
                    $('#viewPostedAt').text(formatDateTime(p.approved_at || p.posted_at || p.updated_at));
                    $('#viewPostedAvatar').text(getInitials(approverName));
                    $('#viewPostedSubtitle').text('Approver & Otorisator Kas');
                } else {
                    $('#viewPostedItem').addClass('d-none');
                }

                // 5. Updater (jika ada pembaruan setelah create)
                if (p.updater && p.updated_at && p.updated_at !== p.created_at && p.status !== 'Cancelled' && p.status !== 'Ditolak') {
                    $('#viewUpdatedItem').removeClass('d-none');
                    let updaterName = p.updater?.name || p.updater?.nama_lengkap || 'System';
                    $('#viewUpdatedBy').text(updaterName);
                    $('#viewUpdatedAt').text(formatDateTime(p.updated_at));
                    $('#viewUpdatedAvatar').text(getInitials(updaterName));
                } else {
                    $('#viewUpdatedItem').addClass('d-none');
                }

                // 6. Canceller (jika status Cancelled)
                if (p.status === 'Cancelled' || p.cancelled_at) {
                    $('#viewCancelledItem').removeClass('d-none');
                    let cancellerName = p.canceller?.name || p.canceller?.nama_lengkap || 'System';
                    $('#viewCancelledBy').text(cancellerName);
                    $('#viewCancelledAt').text(formatDateTime(p.cancelled_at || p.updated_at));
                    $('#viewCancelledAvatar').text(getInitials(cancellerName));
                    $('#viewCancelledReasonAudit').text(p.alasan_pembatalan || 'Tanpa keterangan alasan.');
                } else {
                    $('#viewCancelledItem').addClass('d-none');
                }

                // Tombol Cetak Bukti Bayar (Voucher PDF)
                let pdfUrl = `{{ url('purchase-payments') }}/${p.id}/pdf`;
                $('#btnViewPrintVoucher').attr('href', pdfUrl);

                $('#modalViewPayment').modal('show');
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memuat detail pembayaran!';
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: msg });
            }
        });
    });

});
</script>
