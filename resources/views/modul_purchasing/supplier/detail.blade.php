{{-- ===== MODAL DETAIL SUPPLIER ===== --}}
<div class="modal fade" id="modalDetailSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-bottom py-3.5 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="detail_nama_supplier">PT. Nama Supplier</h5>
                        <span class="badge bg-primary text-white fw-semibold px-3 py-1 rounded-pill mt-1" id="detail_kode_supplier">
                            SPL-0000
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Left Section: General Info & Address -->
                    <div class="col-12 col-md-6">
                        <div class="card-detail-section h-100">
                            <h6 class="fw-bold mb-3 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-person-lines-fill"></i> Profil Kontak
                            </h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="supplier-detail-label">PIC Kontak</div>
                                    <div class="supplier-detail-val" id="detail_pic">-</div>
                                </div>
                                <div class="col-6">
                                    <div class="supplier-detail-label">Status Keaktifan</div>
                                    <div id="detail_status_badge" class="mt-0.5">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="supplier-detail-label">Telepon / WhatsApp</div>
                                    <div class="supplier-detail-val d-flex align-items-center gap-1.5" id="detail_telepon_container">
                                        <i class="bi bi-telephone-fill text-primary small"></i>
                                        <span id="detail_telepon">-</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="supplier-detail-label">Email Perusahaan</div>
                                    <div class="supplier-detail-val d-flex align-items-center gap-1.5 text-break" id="detail_email_container">
                                        <i class="bi bi-envelope-fill text-primary small"></i>
                                        <span id="detail_email">-</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="supplier-detail-label">NPWP Perusahaan</div>
                                    <div class="supplier-detail-val font-monospace" id="detail_npwp">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Finance & Bank -->
                    <div class="col-12 col-md-6">
                        <div class="card-detail-section h-100">
                            <h6 class="fw-bold mb-3 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-wallet2"></i> Keuangan & Detail Bank
                            </h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="supplier-detail-label">Termin Bayar</div>
                                    <div class="supplier-detail-val fw-bold" id="detail_termin">0 Hari (COD)</div>
                                </div>
                                <div class="col-6">
                                    <div class="supplier-detail-label">Limit Kredit</div>
                                    <div class="supplier-detail-val text-danger fw-bold" id="detail_limit_kredit">Rp 0</div>
                                </div>
                                <div class="col-12">
                                    <div class="supplier-detail-label mb-1.5">Daftar Rekening Pembayaran</div>
                                    <div id="detail_banks_container">
                                        <!-- List of bank accounts injected dynamically -->
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="supplier-detail-label">Keterangan / Catatan</div>
                                    <div class="supplier-detail-val text-muted small" style="font-weight: 500;" id="detail_keterangan">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Section -->
                    <div class="col-12">
                        <div class="card-detail-section">
                            <h6 class="fw-bold mb-2.5 d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt-fill text-danger"></i> Alamat Pengiriman / Operasional
                            </h6>
                            <div class="supplier-detail-val" id="detail_alamat" style="font-weight: 500; line-height: 1.6;">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 pt-1 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-5 py-2 fw-semibold" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== DETAIL SHOW MODAL =====
        $(document).on('click', '.btn-detail', function () {
            let id = $(this).data('id');
            $.get('/supplier/' + id + '/edit', function (res) {
                let s = res.supplier;
                $('#detail_nama_supplier').text(s.nama_supplier);
                $('#detail_kode_supplier').text(s.kode_supplier);
                $('#detail_pic').text(s.nama_kontak ? s.nama_kontak : '-');
                $('#detail_telepon').text(s.telepon ? s.telepon : '-');
                $('#detail_email').text(s.email ? s.email : '-');
                $('#detail_npwp').text(s.npwp ? s.npwp : '-');
                
                // Alamat Lengkap
                let fullAlamat = s.alamat ? s.alamat : '';
                let cityState = [];
                if (s.kota) cityState.push(s.kota);
                if (s.provinsi) cityState.push(s.provinsi);
                if (s.kode_pos) cityState.push(s.kode_pos);
                
                if (cityState.length > 0) {
                    fullAlamat += (fullAlamat ? ', ' : '') + cityState.join(', ');
                }
                $('#detail_alamat').text(fullAlamat ? fullAlamat : 'Tidak mencantumkan alamat.');

                // Keuangan
                $('#detail_termin').text(s.term_pembayaran > 0 ? s.term_pembayaran + ' Hari' : '0 Hari (COD)');
                $('#detail_limit_kredit').text(formatRupiah(s.limit_kredit));
                
                // Render List of Bank Accounts with High-Contrast Layout
                if (s.banks && s.banks.length > 0) {
                    let bankHtml = '<div class="d-flex flex-column gap-2">';
                    s.banks.forEach(function(b) {
                        let starBadge = b.is_primary 
                            ? '<span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 ms-auto d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;"><i class="bi bi-star-fill"></i> Utama</span>' 
                            : '';
                        bankHtml += `
                            <div class="sup-bank-item d-flex align-items-center flex-wrap gap-2">
                                <span class="badge text-white fw-bold px-2.5 py-1.5 rounded-2" style="background-color: #0284c7; min-width: 65px; text-align: center; font-size: 0.78rem;">${escapeHtml(b.nama_bank)}</span>
                                <span class="font-monospace fw-bold me-1" style="letter-spacing: 0.5px;">${escapeHtml(b.nomor_rekening)}</span>
                                <span class="fw-semibold small opacity-90">A/N ${escapeHtml(b.atas_nama_rekening)}</span>
                                ${starBadge}
                            </div>
                        `;
                    });
                    bankHtml += '</div>';
                    $('#detail_banks_container').html(bankHtml);
                } else {
                    $('#detail_banks_container').html('<span class="text-muted small italic">Tidak mencantumkan info rekening bank.</span>');
                }

                // Keterangan
                $('#detail_keterangan').text(s.keterangan ? s.keterangan : '-');

                // Status Badge (Solid High-Contrast)
                if (s.status === 'Active') {
                    $('#detail_status_badge').html('<span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>');
                } else {
                    $('#detail_status_badge').html('<span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>');
                }

                $('#modalDetailSupplier').modal('show');
            });
        });
    });
</script>
@endpush
