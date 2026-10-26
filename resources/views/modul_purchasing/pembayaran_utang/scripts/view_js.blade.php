<script>
$(document).ready(function () {

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

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
                let payKurs = parseFloat(p.kurs || 1);
                let isForeign = (curCode !== 'IDR' && payKurs > 1);

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

                // Kurs Pas Diterima / Bayar di Header
                if (isForeign) {
                    $('#viewKursBayarHeader').html(`<span class="font-monospace fw-bold text-success">1 ${curCode} = ${formatRp(payKurs)}</span>`);
                    $('#viewMataUangHeader').html(`Valuta: <strong>${curCode} (${curSymbol})</strong>`);
                } else {
                    $('#viewKursBayarHeader').html(`<span class="font-monospace fw-bold text-secondary">Rp 1,00</span>`);
                    $('#viewMataUangHeader').html(`Mata Uang: <strong>IDR (Rp)</strong>`);
                }

                // Invoices Table
                let tbody = $('#viewInvoicesTbody');
                tbody.empty();

                let totFakturTotal = 0;
                let totSisaUtangTotal = 0;
                let totBayar = 0;
                let totPotongan = 0;
                let totSisaAkhir = 0;
                let totalApIdr = 0;
                let totalKasIdr = 0;

                if (p.details && p.details.length > 0) {
                    p.details.forEach(function (det, idx) {
                        let inv = det.invoice;
                        let invKurs = parseFloat(inv?.kurs || payKurs || 1);
                        if (invKurs <= 0) invKurs = payKurs;

                        let totalFaktur = parseFloat(det.total_faktur || 0);
                        let sisaUtang   = parseFloat(det.sisa_utang || 0);
                        let bayar       = parseFloat(det.jumlah_bayar || 0);
                        let potong      = parseFloat(det.potongan || 0);
                        let sisaAkhir   = parseFloat(det.sisa_akhir || 0);

                        totFakturTotal    += totalFaktur;
                        totSisaUtangTotal += sisaUtang;
                        totBayar          += bayar;
                        totPotongan       += potong;
                        totSisaAkhir      += sisaAkhir;

                        totalApIdr  += (bayar + potong) * invKurs;
                        totalKasIdr += bayar * payKurs;

                        let diffKurs = payKurs - invKurs;

                        // Perbandingan Kurs Kolom
                        let kursComparisonHtml = '';
                        if (isForeign || invKurs > 1 || payKurs > 1) {
                            let selisihBadge = '';
                            if (diffKurs > 0.01) {
                                selisihBadge = `<div class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 mt-0.5 w-100 text-center" style="font-size:0.68rem;" title="Kurs saat bayar lebih tinggi: timbul Beban Rugi Selisih Kurs"><i class="bi bi-graph-up-arrow me-0.5"></i>Naik +${formatRp(diffKurs)} (Rugi)</div>`;
                            } else if (diffKurs < -0.01) {
                                selisihBadge = `<div class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 mt-0.5 w-100 text-center" style="font-size:0.68rem;" title="Kurs saat bayar lebih rendah: timbul Pendapatan Untung Selisih Kurs"><i class="bi bi-graph-down-arrow me-0.5"></i>Turun -${formatRp(Math.abs(diffKurs))} (Untung)</div>`;
                            } else {
                                selisihBadge = `<div class="badge bg-secondary-subtle text-secondary border px-1.5 py-0.5 mt-0.5 w-100 text-center" style="font-size:0.68rem;"><i class="bi bi-dash me-0.5"></i>Kurs Sama</div>`;
                            }

                            kursComparisonHtml = `
                                <div class="d-flex flex-column gap-1" style="font-size:0.75rem;">
                                    <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded border bg-light">
                                        <span class="text-muted"><i class="bi bi-file-earmark-text me-1"></i>Di Faktur:</span>
                                        <strong class="text-dark font-monospace">${formatRp(invKurs)}</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded border bg-primary bg-opacity-10 border-primary-subtle">
                                        <span class="text-primary fw-semibold"><i class="bi bi-cash-coin me-1"></i>Pas Bayar:</span>
                                        <strong class="text-primary font-monospace">${formatRp(payKurs)}</strong>
                                    </div>
                                    ${selisihBadge}
                                </div>
                            `;
                        } else {
                            kursComparisonHtml = `
                                <div class="text-center text-muted small">
                                    <span class="badge bg-light text-secondary border px-2 py-1 font-monospace">IDR (Kurs 1,00)</span>
                                </div>
                            `;
                        }

                        // Total Faktur dihitung dengan Kurs Faktur
                        let totFakturStr = isForeign 
                            ? `<div class="fw-bold">${curSymbol} ${Number(totalFaktur).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(totalFaktur * invKurs)} <span class="badge bg-light text-secondary border py-0 px-1 font-monospace" style="font-size:0.65rem;">@ ${formatRp(invKurs)}</span></small>` 
                            : formatRp(totalFaktur);

                        // Sisa Utang dihitung dengan Kurs Faktur
                        let sisaUtangStr = isForeign 
                            ? `<div class="fw-bold text-muted">${curSymbol} ${Number(sisaUtang).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(sisaUtang * invKurs)} <span class="badge bg-light text-secondary border py-0 px-1 font-monospace" style="font-size:0.65rem;">@ ${formatRp(invKurs)}</span></small>` 
                            : formatRp(sisaUtang);

                        // Jumlah Bayar dihitung dengan Kurs Pas Bayar / Diterima
                        let bayarStr = isForeign 
                            ? `<div class="fw-bold text-primary">${curSymbol} ${Number(bayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-primary d-block" style="font-size:0.72rem;">≈ ${formatRp(bayar * payKurs)} <span class="badge bg-primary-subtle text-primary py-0 px-1 font-monospace" style="font-size:0.65rem;">@ ${formatRp(payKurs)}</span></small>` 
                            : formatRp(bayar);

                        let potongStr = (potong > 0) 
                            ? (isForeign 
                                ? `<div class="fw-bold text-danger">${curSymbol} ${Number(potong).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-danger d-block" style="font-size:0.72rem;">≈ ${formatRp(potong * payKurs)}</small>` 
                                : formatRp(potong)) 
                            : '-';

                        let sisaAkhirStr = isForeign 
                            ? (sisaAkhir <= 0.01 
                                ? `<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (${curSymbol} 0)</span>` 
                                : `<div class="fw-bold text-warning">${curSymbol} ${Number(sisaAkhir).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(sisaAkhir * invKurs)}</small>`) 
                            : (sisaAkhir <= 0.01 
                                ? `<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (Rp 0)</span>` 
                                : formatRp(sisaAkhir));

                        let tenderObj = inv?.tender || inv?.goods_receipt?.tender || inv?.po?.tender;
                        let nomorTender = '';
                        if (tenderObj) {
                            nomorTender = (tenderObj.nomor_kontrak && tenderObj.nomor_kontrak !== '-')
                                ? (tenderObj.nomor_kontrak + (tenderObj.nama_tender ? ' - ' + tenderObj.nama_tender : ''))
                                : (tenderObj.nomor_kontrak || tenderObj.nama_tender || ('Tender #' + tenderObj.id));
                        }

                        let tr = `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>
                                    <div class="fw-bold text-dark">${escapeHtml(inv?.nomor_faktur || '-')}</div>
                                    <small class="text-muted">No. Supp: ${escapeHtml(inv?.nomor_faktur_supplier || '-')}</small>
                                    ${nomorTender ? `<div class="mt-0.5"><span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 0.68rem;" title="Terkait Proyek Tender"><i class="bi bi-briefcase me-1"></i>${escapeHtml(nomorTender)}</span></div>` : ''}
                                </td>
                                <td>${inv ? formatDate(inv.tanggal_faktur) : '-'}</td>
                                <td>${inv ? formatDate(inv.tanggal_jatuh_tempo) : '-'}</td>
                                <td>${kursComparisonHtml}</td>
                                <td class="text-end">${totFakturStr}</td>
                                <td class="text-end">${sisaUtangStr}</td>
                                <td class="text-end">${bayarStr}</td>
                                <td class="text-end">${potongStr}</td>
                                <td class="text-end">${sisaAkhirStr}</td>
                                <td>${escapeHtml(det.catatan || '-')}</td>
                            </tr>
                        `;
                        tbody.append(tr);
                    });
                } else {
                    tbody.append('<tr><td colspan="11" class="text-center text-muted py-3">Tidak ada faktur yang dibayar</td></tr>');
                }

                // Summary Totals
                if (isForeign) {
                    $('#viewTotalFaktur').html(`<strong>${curSymbol} ${Number(totFakturTotal).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>`);
                    $('#viewTotalSisaUtang').html(`<strong>${curSymbol} ${Number(totSisaUtangTotal).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>`);
                    $('#viewTotalPotongan').html(`<strong>${curSymbol} ${Number(totPotongan).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(totPotongan * payKurs)}</small>`);
                    $('#viewTotalBayar').html(`<strong>${curSymbol} ${Number(totBayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong><small class="text-muted d-block" style="font-size:0.72rem;">≈ ${formatRp(totBayar * payKurs)}</small>`);
                    $('#viewTotalSisaAkhir').html(`<strong>${curSymbol} ${Number(totSisaAkhir).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>`);
                    $('#viewSummaryGrandTotal').html(`${curSymbol} ${Number(totBayar).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})} <small class="d-block text-white-50" style="font-size:0.8rem;">≈ ${formatRp(totBayar * payKurs)} <span class="badge bg-white bg-opacity-25 text-white ms-1 py-0 px-1 font-monospace">Kurs Bayar: ${formatRp(payKurs)}</span></small>`);
                } else {
                    $('#viewTotalFaktur').text(formatRp(totFakturTotal));
                    $('#viewTotalSisaUtang').text(formatRp(totSisaUtangTotal));
                    $('#viewTotalPotongan').text(formatRp(totPotongan));
                    $('#viewTotalBayar').text(formatRp(totBayar));
                    $('#viewTotalSisaAkhir').text(formatRp(totSisaAkhir));
                    $('#viewSummaryGrandTotal').text(formatRp(totBayar));
                }

                // Tampilkan info Akun COA Potongan jika ada potongan
                if (p.potongan_coa && totPotongan > 0) {
                    $('#viewPotonganCoaBadge').text(p.potongan_coa.code);
                    $('#viewPotonganCoaName').text(p.potongan_coa.name);
                    $('#viewPotonganCoaWrapper').removeClass('d-none');
                } else {
                    $('#viewPotonganCoaWrapper').addClass('d-none');
                }

                // Forex Difference Header Pill
                let netForex = totalApIdr - totalKasIdr;
                if (isForeign && Math.abs(netForex) > 0.01) {
                    if (netForex < -0.01) {
                        $('#viewForexSummaryPill').html(`
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 shadow-sm" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i>Rugi Selisih Kurs: <strong>${formatRp(Math.abs(netForex))}</strong>
                            </span>
                        `).removeClass('d-none');
                    } else {
                        $('#viewForexSummaryPill').html(`
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 shadow-sm" style="font-size:0.78rem;">
                                <i class="bi bi-check-circle-fill me-1"></i>Untung Selisih Kurs: <strong>${formatRp(netForex)}</strong>
                            </span>
                        `).removeClass('d-none');
                    }
                } else {
                    $('#viewForexSummaryPill').addClass('d-none').empty();
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

                // Cek Hak Akses Jurnal: lihat-jurnal-pembayaran-utang
                let canSeeJurnal = (res.can_see_jurnal === true);

                if (!canSeeJurnal) {
                    $('#viewJurnalMainContainer').addClass('d-none');
                    $('#viewJurnalCardHeaderTitle').html('<i class="bi bi-card-text me-1 text-primary"></i> Catatan &amp; Lampiran');
                } else {
                    $('#viewJurnalMainContainer').removeClass('d-none');
                    $('#viewJurnalCardHeaderTitle').html('<i class="bi bi-journal-text me-1 text-primary"></i> Catatan &amp; Jurnal Akuntansi');

                    // Jurnal Akuntansi Table
                    if (p.jurnal && p.jurnal.details && p.jurnal.details.length > 0) {
                        $('#viewJurnalWrapper').removeClass('d-none');
                        $('#viewJurnalDraftNotice').addClass('d-none').removeClass('d-flex');
                        $('#viewNomorJurnal').text(p.jurnal.nomor_jurnal);
                        if (p.jurnal.no_tender) {
                            $('#viewJurnalTenderText').text(p.jurnal.no_tender);
                            $('#viewJurnalTenderBadge').removeClass('d-none');
                        } else {
                            $('#viewJurnalTenderBadge').addClass('d-none');
                        }

                        let jTbody = $('#viewJurnalTbody');
                        jTbody.empty();
                        p.jurnal.details.forEach(function (jDet) {
                            let debitStr = parseFloat(jDet.debit) > 0 ? formatRp(jDet.debit) : '-';
                            let kreditStr = parseFloat(jDet.kredit) > 0 ? formatRp(jDet.kredit) : '-';
                            let accountStr = jDet.account ? `${jDet.account.code} - ${jDet.account.name}` : '-';

                            jTbody.append(`
                                <tr>
                                    <td>${escapeHtml(accountStr)}</td>
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
                        if (p.reversal_jurnal.no_tender) {
                            $('#viewReversalJurnalTenderText').text(p.reversal_jurnal.no_tender);
                            $('#viewReversalJurnalTenderBadge').removeClass('d-none');
                        } else {
                            $('#viewReversalJurnalTenderBadge').addClass('d-none');
                        }

                        let rTbody = $('#viewReversalJurnalTbody');
                        rTbody.empty();
                        p.reversal_jurnal.details.forEach(function (rDet) {
                            let debitStr = parseFloat(rDet.debit) > 0 ? formatRp(rDet.debit) : '-';
                            let kreditStr = parseFloat(rDet.kredit) > 0 ? formatRp(rDet.kredit) : '-';
                            let accountStr = rDet.account ? `${rDet.account.code} - ${rDet.account.name}` : '-';

                            rTbody.append(`
                                <tr>
                                    <td>${escapeHtml(accountStr)}</td>
                                    <td class="text-end fw-semibold text-success">${debitStr}</td>
                                    <td class="text-end fw-semibold text-danger">${kreditStr}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#viewReversalJurnalWrapper').addClass('d-none');
                    }
                }

                // Tombol Hapus Draf di Footer Modal View
                if ((p.status === 'Draft' || p.status === 'Ditolak') && res.can_delete) {
                    $('#btnViewDeletePayment')
                        .removeClass('d-none')
                        .data('id', p.id)
                        .data('nomor', p.nomor_pembayaran);
                } else {
                    $('#btnViewDeletePayment').addClass('d-none');
                }

                // Tombol Batalkan Pembayaran di Footer Modal View
                if (p.status === 'Posted' && res.can_cancel) {
                    $('#btnViewCancelPayment')
                        .removeClass('d-none')
                        .data('id', p.id)
                        .data('nomor', p.nomor_pembayaran);
                } else {
                    $('#btnViewCancelPayment').addClass('d-none');
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
