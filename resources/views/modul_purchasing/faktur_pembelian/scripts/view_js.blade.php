<script>
$(document).ready(function () {
    // Trigger View Click
    $(document).on('click', '.btn-view-invoice', function () {
        let id = $(this).data('id');
        $.get('/purchase-invoices/' + id, function (res) {
            if (res && res.invoice) {
                let inv = res.invoice;

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
                $('#view_disp_supplier_npwp').text('NPWP: ' + (sup.npwp || '-'));
                $('#view_disp_ref_gr').text(grnNum + ' (' + gdg + ')');
                $('#view_disp_ref_po').text('Ref. PO: ' + (po ? (po.nomor_po || '-') : '-'));

                let curCode = (inv.currency && inv.currency.kode_currency) 
                    ? inv.currency.kode_currency 
                    : ((po && po.currency && po.currency.kode_currency) ? po.currency.kode_currency : 'IDR');
                let curSymbol = (inv.currency && inv.currency.simbol) 
                    ? inv.currency.simbol 
                    : ((po && po.currency && po.currency.simbol) ? po.currency.simbol : (curCode === 'USD' ? '$' : 'Rp'));
                let kursVal = inv.kurs ? parseFloat(inv.kurs) : ((po && po.kurs) ? parseFloat(po.kurs) : 1.0);

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

                // Render Status Badge
                let statusBadgeHtml = '';
                if (inv.status === 'Draft') {
                    statusBadgeHtml = '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    $('#view_btn_edit_trigger').data('id', inv.id).show();
                    $('#view_btn_post_trigger').data('id', inv.id).show();
                } else {
                    statusBadgeHtml = '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check2-all me-1"></i>' + inv.status + '</span>';
                    $('#view_btn_edit_trigger').hide();
                    $('#view_btn_post_trigger').hide();
                }
                $('#view_status_badge').html(statusBadgeHtml);

                // Set PDF Print Link
                $('#view_btn_print_pdf').attr('href', '/purchase-invoices/' + inv.id + '/pdf');

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
                            let poDetail = d.gr_detail ? (d.gr_detail.po_detail || d.gr_detail.poDetail || {}) : {};
                            let diskonItem = parseFloat(poDetail.diskon_item || 0);
                            let lineSubtotalGross = qty * hargaSatuan;
                            let lineDiskonTotal = qty * diskonItem;
                            let lineNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);

                            subtotalGross += lineSubtotalGross;
                            totalDiskon += lineDiskonTotal;

                            itemsHtml += '<tr>' +
                                '<td class="text-center">' + no + '</td>' +
                                '<td class="fw-semibold text-primary">' + escapeHtml(kode) + '</td>' +
                                '<td class="fw-semibold text-body">' + escapeHtml(nama) + '</td>' +
                                '<td class="text-center font-monospace fw-bold">' + qty + '</td>' +
                                '<td class="small text-muted">' + escapeHtml(satuan) + '</td>' +
                                '<td class="text-end fw-semibold text-body">' + formatMoney(hargaSatuan, curCode, curSymbol) + '</td>' +
                                '<td class="text-end fw-bold text-success">' + formatMoney(lineNet, curCode, curSymbol) + '</td>' +
                            '</tr>';
                        }
                    });
                }
                $('#view_items_tbody').html(itemsHtml || '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada rincian item barang.</td></tr>');

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
                            let hppBadge = d.masuk_hpp ? '<span class="badge bg-primary bg-opacity-10 text-primary">Ya (HPP)</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak</span>';

                            if (d.jenis === 'PPN') {
                                totalPpn += nom;
                                hppBadge = '<span class="badge bg-danger bg-opacity-10 text-danger">Pajak Masukan</span>';
                            } else {
                                totalBiayaLain += nom;
                            }

                            extraHtml += '<tr>' +
                                '<td class="fw-semibold text-body">' + escapeHtml(jenis) + '</td>' +
                                '<td class="text-center">' + hppBadge + '</td>' +
                                '<td class="small text-muted">' + escapeHtml(coaTxt) + '</td>' +
                                '<td class="text-end fw-bold text-body">' + formatMoney(nom, curCode, curSymbol) + '</td>' +
                            '</tr>';
                        }
                    });
                }

                if (extraHtml) {
                    $('#view_extra_costs_tbody').html(extraHtml);
                    $('#view_card_extra_costs').show();
                } else {
                    $('#view_card_extra_costs').hide();
                }

                // Summary Financial Calculation
                let dpp = Math.max(0, subtotalGross - totalDiskon);
                let grandTotal = parseFloat(inv.grand_total || (dpp + totalBiayaLain + totalPpn));

                $('#view_sum_subtotal').text(formatNumberOnly(subtotalGross, curCode));
                $('#view_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, curCode));
                $('#view_sum_dpp').text(formatNumberOnly(dpp, curCode));
                $('#view_sum_ppn').text(formatNumberOnly(totalPpn, curCode));
                $('#view_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, curCode));
                $('#view_sum_total_invoice').text(formatMoney(grandTotal, curCode, curSymbol));

                if (curCode !== 'IDR' && kursVal > 1) {
                    let totalIdr = grandTotal * kursVal;
                    $('#view_sum_total_invoice_idr').html('<i class="bi bi-arrow-repeat me-1"></i>Ekuivalen: <strong>Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totalIdr) + '</strong> (Kurs: ' + formatSmartKurs(kursVal) + ')').show();
                } else {
                    $('#view_sum_total_invoice_idr').hide().empty();
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

                let sisaHutang = Math.max(0, grandTotal - totalPaid - totalPotongan);
                $('#view_pay_tagihan').text(formatMoney(grandTotal, curCode, curSymbol));
                $('#view_pay_terbayar').text(formatMoney(totalPaid, curCode, curSymbol));
                $('#view_pay_potongan').text(formatMoney(totalPotongan, curCode, curSymbol));
                $('#view_pay_sisa').text(formatMoney(sisaHutang, curCode, curSymbol));

                if (paymentsHtml) {
                    $('#view_payments_tbody').html(paymentsHtml);
                    $('#view_pay_count_badge').text(payCount + ' Pembayaran');
                    $('#view_card_payments').show();
                } else {
                    $('#view_payments_tbody').html('<tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi pembayaran untuk faktur ini.</td></tr>');
                    $('#view_pay_count_badge').text('0 Pembayaran');
                    $('#view_card_payments').show();
                }

                // Render Accounting Journal
                if (inv.jurnal && inv.jurnal.details && inv.jurnal.details.length > 0) {
                    let jHtml = '<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">' +
                        '<span class="badge bg-primary bg-opacity-10 text-primary font-monospace">' + escapeHtml(inv.jurnal.nomor_jurnal) + '</span>' +
                        '<span class="extra-small text-muted">' + (inv.jurnal.tanggal_jurnal ? String(inv.jurnal.tanggal_jurnal).substring(0, 10) : '-') + '</span>' +
                    '</div>' +
                    '<div class="table-responsive"><table class="table table-sm table-borderless extra-small mb-0">' +
                    '<thead><tr class="text-muted border-bottom"><th>AKUN</th><th class="text-end">DEBIT</th><th class="text-end">KREDIT</th></tr></thead><tbody>';

                    inv.jurnal.details.forEach(function (jd) {
                        let accName = jd.account ? (jd.account.code + ' ' + jd.account.name) : '-';
                        let deb = parseFloat(jd.debit || 0);
                        let kre = parseFloat(jd.kredit || 0);

                        jHtml += '<tr>' +
                            '<td class="text-truncate" style="max-width:140px;" title="' + escapeHtml(accName) + '">' + escapeHtml(accName) + '</td>' +
                            '<td class="text-end font-monospace ' + (deb > 0 ? 'text-body fw-semibold' : 'text-muted') + '">' + (deb > 0 ? new Intl.NumberFormat('id-ID').format(deb) : '-') + '</td>' +
                            '<td class="text-end font-monospace ' + (kre > 0 ? 'text-body fw-semibold' : 'text-muted') + '">' + (kre > 0 ? new Intl.NumberFormat('id-ID').format(kre) : '-') + '</td>' +
                        '</tr>';
                    });

                    jHtml += '</tbody></table></div>';
                    $('#view_jurnal_content').html(jHtml);
                } else {
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
            $('.btn-edit-invoice[data-id="' + id + '"]').trigger('click');
        }, 300);
    });

    $('#view_btn_post_trigger').on('click', function () {
        let id = $(this).data('id');
        $('#modalDetailInvoice').modal('hide');
        setTimeout(function () {
            $('.btn-post-invoice[data-id="' + id + '"]').trigger('click');
        }, 300);
    });
});
</script>
