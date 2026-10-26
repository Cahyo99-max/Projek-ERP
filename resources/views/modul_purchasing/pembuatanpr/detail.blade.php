{{-- ===== MODAL DETAIL ===== --}}
<div class="modal fade" id="modalDetailPR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-file-earmark-text-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Detail Pengajuan Pembelian (PR)</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-1 rounded-pill mt-1" id="detail_nomor_pr">
                            PR/0000/00/0000
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 mb-4">
                    <!-- Left Section: metadata -->
                    <div class="col-12 col-md-6">
                        <div class="pr-detail-card h-100">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle me-1.5"></i> Profil Pengajuan</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="pr-detail-label">Tanggal Pengajuan</div>
                                    <div class="pr-detail-val" id="detail_tanggal_pr">-</div>
                                </div>
                                <div class="col-6">
                                    <div class="pr-detail-label">Tanggal Dibutuhkan</div>
                                    <div class="pr-detail-val text-danger" id="detail_tanggal_dibutuhkan">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pr-detail-label">Departemen / Divisi</div>
                                    <div class="pr-detail-val" id="detail_departemen">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pr-detail-label">Diminta Oleh</div>
                                    <div class="pr-detail-val" id="detail_pembuat">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pr-detail-label">Lokasi / Asal Pengajuan</div>
                                    <div class="pr-detail-val mt-1" id="detail_asal_pengajuan">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: status / approver -->
                    <div class="col-12 col-md-6">
                        <div class="pr-detail-card h-100">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-check me-1.5"></i> Status & Persetujuan</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="pr-detail-label">Status Saat Ini</div>
                                    <div id="detail_status_badge">-</div>
                                </div>
                                <div class="col-6">
                                    <div class="pr-detail-label" id="detail_approver_label">Disetujui Oleh</div>
                                    <div class="pr-detail-val" id="detail_approver">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pr-detail-label" id="detail_tanggal_label">Tanggal Persetujuan</div>
                                    <div class="pr-detail-val" id="detail_tanggal_disetujui">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pr-detail-label">Keterangan / Alasan Pengadaan</div>
                                    <div class="pr-detail-val text-muted small" style="font-weight: 500;" id="detail_keterangan">-</div>
                                </div>
                                <div class="col-12" id="detail_alasan_reject_container" style="display: none;">
                                    <div class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 shadow-xs">
                                        <div class="pr-detail-label text-danger fw-bold d-flex align-items-center gap-1.5 mb-1">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Alasan Penolakan Atasan
                                        </div>
                                        <div class="pr-detail-val text-danger fw-semibold" id="detail_alasan_reject" style="white-space: pre-wrap; word-break: break-word;">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Detail Table -->
                <style>
                    .modal-detail-table thead th {
                        background-color: #1e3a8a !important;
                        color: #ffffff !important;
                        font-size: 0.72rem !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.5px !important;
                        vertical-align: middle !important;
                        border-bottom: 2px solid #3b82f6 !important;
                        border-right: 1px solid rgba(255, 255, 255, 0.18) !important;
                        padding-top: 8px !important;
                        padding-bottom: 8px !important;
                    }
                    .modal-detail-table thead th:last-child {
                        border-right: none !important;
                    }
                </style>
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-list-ol me-1.5"></i> Daftar Barang Pengadaan</h6>
                <div class="table-responsive border rounded-3 overflow-hidden bg-white mb-3 shadow-2xs">
                    <table class="table table-sm table-striped align-middle mb-0 responsive-pr-table modal-detail-table">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center py-2">No</th>
                                <th width="6%" class="text-center">Foto</th>
                                <th width="12%">Kode Barang</th>
                                <th width="20%">Nama Barang</th>
                                <th width="8%">Tipe</th>
                                <th width="8%">Satuan</th>
                                <th width="8%" class="text-center">Jumlah</th>
                                <th width="14%" class="text-end">Estimasi Harga</th>
                                <th width="14%" class="text-end">Subtotal</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="detail_items_tbody">
                            <!-- list of items details injected dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Total summary -->
                <div class="row justify-content-end mb-2">
                    <div class="col-12 col-md-4 text-end">
                        <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-secondary text-uppercase small" style="font-size: 0.72rem;">Total Estimasi:</span>
                            <span class="fw-bold text-primary fs-5" id="detail_grand_total">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Approval Buttons Area (Conditional) -->
                @can('approve-pr')
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 d-none" id="approval_action_panel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Panel Persetujuan Dokumen</h6>
                            <p class="small text-muted mb-0">Sebagai pemeriksa/approver, Anda berhak menyetujui atau menolak dokumen pengajuan ini.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger rounded-pill px-4 btn-reject-action"><i class="bi bi-x-circle me-1"></i> Tolak</button>
                            <button type="button" class="btn btn-success rounded-pill px-4 btn-approve-action"><i class="bi bi-check-circle me-1"></i> Setujui</button>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-between px-4">
                @if(auth()->user()->can('cetak-pr') || auth()->user()->can('print-pr') || auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing']))
                <a href="#" id="btn_print_detail" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-printer me-1"></i> Cetak PDF / Print
                </a>
                @else
                <div></div>
                @endif
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        let currentDetailId = null;
        let currentDetailXhr = null;

        // Self-contained safe helpers
        function safeEscapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function safeFormatRupiah(amount) {
            if (amount === null || amount === undefined || isNaN(amount)) return 'Rp 0';
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        // Expose to window if not already defined
        if (typeof window.escapeHtml !== 'function') window.escapeHtml = safeEscapeHtml;
        if (typeof window.formatRupiah !== 'function') window.formatRupiah = safeFormatRupiah;

        // ===== DETAIL SHOW MODAL & PERSETUJUAN =====
        function loadPrDetail(id) {
            if (!id) return;
            currentDetailId = id;

            // Abort previous pending request if any
            if (currentDetailXhr && typeof currentDetailXhr.abort === 'function') {
                currentDetailXhr.abort();
            }

            // Instantly show modal to give instant visual feedback
            $('#detail_nomor_pr').text('Memuat...');
            $('#detail_tanggal_pr').text('...');
            $('#detail_tanggal_dibutuhkan').text('...');
            $('#detail_departemen').text('...');
            $('#detail_pembuat').text('...');
            $('#detail_asal_pengajuan').text('...');
            $('#detail_keterangan').text('...');
            $('#detail_approver').text('...');
            $('#detail_tanggal_disetujui').text('...');
            $('#detail_status_badge').html('<span class="spinner-border spinner-border-sm text-primary" role="status"></span>');
            $('#detail_items_tbody').html('<tr><td colspan="10" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat rincian barang...</td></tr>');
            $('#detail_grand_total').text('Rp 0');
            $('#approval_action_panel').addClass('d-none');
            $('#modalDetailPR').modal('show');

            currentDetailXhr = $.ajax({
                url: '/purchase-requisition/' + id,
                type: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function (res) {
                    let pr = res.pr;
                    if (!pr) {
                        $('#detail_status_badge').html('<span class="badge bg-danger">Data Kosong</span>');
                        $('#detail_items_tbody').html('<tr><td colspan="10" class="text-center py-3 text-danger">Data PR tidak ditemukan.</td></tr>');
                        return;
                    }

                    $('#btn_print_detail').attr('href', '/purchase-requisition/' + id + '/print');
                    $('#detail_nomor_pr').text(pr.nomor_pr || '-');
                    
                    let tglPr = pr.tanggal_pr ? new Date(pr.tanggal_pr).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-';
                    let tglButuh = pr.tanggal_dibutuhkan ? new Date(pr.tanggal_dibutuhkan).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-';
                    $('#detail_tanggal_pr').text(tglPr);
                    $('#detail_tanggal_dibutuhkan').text(tglButuh);
                    
                    $('#detail_departemen').text(pr.department ? pr.department.department_name : '-');
                    let requesterName = pr.requester ? (pr.requester.nama_lengkap + (pr.requester.kode_karyawan ? ' (' + pr.requester.kode_karyawan + ')' : '')) : (pr.creator ? pr.creator.nama_lengkap : '-');
                    $('#detail_pembuat').text(requesterName);

                    // Asal Pengajuan (PT vs Cabang)
                    let compName = pr.company ? pr.company.nama_perusahaan : '';
                    let branchName = pr.branch ? pr.branch.nama_cabang : '';
                    let asalHtml = '';
                    if (pr.branch_id && branchName) {
                        asalHtml = `<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-3 py-1.5 rounded-pill"><i class="bi bi-geo-alt-fill me-1"></i>Cabang: ${safeEscapeHtml(branchName)} (${safeEscapeHtml(compName || 'PT')})</span>`;
                    } else {
                        asalHtml = `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill"><i class="bi bi-building me-1"></i>PT Pusat: ${safeEscapeHtml(compName || 'Kantor Pusat')}</span>`;
                    }
                    $('#detail_asal_pengajuan').html(asalHtml);

                    $('#detail_keterangan').text(pr.keterangan ? pr.keterangan : 'Tidak ada keterangan.');

                    // Status
                    let statusHtml = '';
                    if (pr.status === 'Draft') {
                        statusHtml = '<span class="badge bg-secondary rounded-pill px-3 py-1">Draft</span>';
                    } else if (pr.status === 'Pending') {
                        statusHtml = '<span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pending Approval</span>';
                    } else if (pr.status === 'Approved') {
                        statusHtml = '<span class="badge bg-success rounded-pill px-3 py-1">Approved</span>';
                    } else if (pr.status === 'Rejected') {
                        statusHtml = '<span class="badge bg-danger rounded-pill px-3 py-1">Rejected</span>';
                    } else {
                        statusHtml = '<span class="badge bg-info rounded-pill px-3 py-1">Closed</span>';
                    }
                    $('#detail_status_badge').html(statusHtml);

                    // Approver / Rejector Metadata
                    let actionPerson = '-';
                    let actionDate = '-';

                    if (pr.status === 'Rejected') {
                        $('#detail_approver_label').text('Ditolak Oleh').addClass('text-danger');
                        $('#detail_tanggal_label').text('Tanggal Penolakan');
                        $('#detail_approver').addClass('text-danger fw-bold');
                        if (pr.disetujui_oleh || pr.approver) {
                            actionPerson = pr.approver ? (pr.approver.nama_lengkap || (pr.approver.karyawan ? pr.approver.karyawan.nama_lengkap : '-')) : (pr.rejector_name || '-');
                        }
                        if (pr.tanggal_disetujui) {
                            actionDate = new Date(pr.tanggal_disetujui).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' });
                        }
                        let alasanTolak = pr.alasan_penolakan || 'Tidak ada alasan khusus yang dicantumkan.';
                        $('#detail_alasan_reject').text(alasanTolak);
                        $('#detail_alasan_reject_container').show();
                    } else {
                        $('#detail_approver_label').text('Disetujui Oleh').removeClass('text-danger');
                        $('#detail_tanggal_label').text('Tanggal Persetujuan');
                        $('#detail_approver').removeClass('text-danger fw-bold');
                        $('#detail_alasan_reject_container').hide();
                        if (pr.status === 'Approved' || pr.status === 'Closed') {
                            if (pr.disetujui_oleh || pr.approver) {
                                actionPerson = pr.approver ? (pr.approver.nama_lengkap || (pr.approver.karyawan ? pr.approver.karyawan.nama_lengkap : '-')) : (pr.atasan_name || '-');
                            }
                            if (pr.tanggal_disetujui) {
                                actionDate = new Date(pr.tanggal_disetujui).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' });
                            }
                        } else {
                            actionPerson = '-';
                            actionDate = '-';
                        }
                    }

                    $('#detail_approver').text(actionPerson);
                    $('#detail_tanggal_disetujui').text(actionDate);

                    // Render list of items in Detail Modal
                    let detailRows = '';
                    let grandTotal = 0;
                    if (pr.details && pr.details.length > 0) {
                        pr.details.forEach(function (item, index) {
                            let jumlah = parseFloat(item.jumlah) || 0;
                            let harga = parseFloat(item.harga_estimasi) || 0;
                            let sub = jumlah * harga;
                            grandTotal += sub;
                            
                            let b = item.barang;
                            let fotoUrl = '';
                            if (b && b.primary_foto) {
                                fotoUrl = `/storage/${b.primary_foto.foto_path}`;
                            }

                            let fotoHtml = `<i class="bi bi-image text-muted" style="font-size: 1.15rem;"></i>`;
                            if (fotoUrl) {
                                fotoHtml = `<img src="${fotoUrl}" style="width: 100%; height: 100%; object-fit: cover;">`;
                            }
                            
                            let itemTipe = b ? (b.tipe_item || 'Stok') : 'Stok';
                            let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
                            if (itemTipe === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
                            else if (itemTipe === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';
                            let itemTipeBadge = `<span class="badge small px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; ${bg}">${itemTipe.toUpperCase()}</span>`;

                            let itemWarnaBadge = '';
                            if (b && b.warna) {
                                itemWarnaBadge = `
                                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center" style="font-size:0.75rem;font-weight:600; margin-left:4px;">
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${b.warna.kode_hex || '#e2e8f0'};margin-right:6px;border:1px solid rgba(0,0,0,0.15);"></span>
                                        ${safeEscapeHtml(b.warna.nama_warna)}
                                    </span>
                                `;
                            }

                            let kodeBarang = b ? b.kode_barang : '-';
                            let namaBarang = b ? b.nama_barang : 'Barang';
                            let namaSatuan = (item.satuan ? (item.satuan.singkatan || item.satuan.nama_satuan) : ((b && b.satuan) ? (b.satuan.singkatan || b.satuan.nama_satuan) : 'pcs'));
                            let faktorPengali = parseFloat(item.faktor_pengali) || 1;
                            let jumlahDasar = parseFloat(item.jumlah_dasar) || (jumlah * faktorPengali);
                            let baseSatuanName = (b && b.satuan) ? (b.satuan.singkatan || b.satuan.nama_satuan) : '';
                            let konversiHint = '';
                            if (Math.abs(faktorPengali - 1) > 0.00001 && baseSatuanName) {
                                let formattedBase = jumlahDasar % 1 === 0 
                                    ? jumlahDasar.toLocaleString('id-ID') 
                                    : parseFloat(jumlahDasar.toFixed(4)).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });
                                konversiHint = `<div class="text-muted small mt-0.5" style="font-size:0.68rem; font-weight:500;">(Setara: ${formattedBase} ${safeEscapeHtml(baseSatuanName)})</div>`;
                            }

                            detailRows += `
                                <tr>
                                    <td class="text-center py-2 font-weight-bold pr-row-number-td" data-label="No">${index + 1}</td>
                                    <td class="text-center py-1" data-label="Foto">
                                         <div class="product-img-frame mx-auto ${fotoUrl ? 'img-thumbnail-preview cursor-pointer' : ''}" 
                                              title="${fotoUrl ? 'Klik untuk memperbesar foto' : ''}"
                                              data-nama="${safeEscapeHtml(namaBarang)}"
                                              data-semua-foto="${safeEscapeHtml(fotoUrl)}">
                                             ${fotoHtml}
                                         </div>
                                    </td>
                                    <td class="font-monospace small" data-label="Kode Barang">${safeEscapeHtml(kodeBarang)}</td>
                                    <td class="fw-semibold text-dark" data-label="Nama Barang">${safeEscapeHtml(namaBarang)}</td>
                                    <td data-label="Tipe">${itemTipeBadge}${itemWarnaBadge}</td>
                                    <td data-label="Satuan"><span class="badge bg-light text-dark px-2.5 py-1 fw-semibold">${safeEscapeHtml(namaSatuan)}</span></td>
                                    <td class="text-center fw-bold text-dark" data-label="Jumlah">
                                        <div>${jumlah.toLocaleString('id-ID')}</div>
                                        ${konversiHint}
                                    </td>
                                    <td class="text-end fw-semibold" data-label="Estimasi Harga">${safeFormatRupiah(harga)}</td>
                                    <td class="text-end fw-bold text-primary" data-label="Subtotal">${safeFormatRupiah(sub)}</td>
                                    <td class="small text-muted" data-label="Catatan">${item.keterangan ? safeEscapeHtml(item.keterangan) : '—'}</td>
                                </tr>
                            `;
                        });
                        $('#detail_items_tbody').html(detailRows);
                    } else {
                        $('#detail_items_tbody').html('<tr><td colspan="10" class="text-center py-3 text-muted">Tidak ada detail barang.</td></tr>');
                    }
                    $('#detail_grand_total').text(safeFormatRupiah(grandTotal));

                    // Show/Hide Approval Panel
                    if (pr.status === 'Pending') {
                        $('#approval_action_panel').removeClass('d-none');
                    } else {
                        $('#approval_action_panel').addClass('d-none');
                    }
                },
                error: function (xhr, status, error) {
                    if (status === 'abort') return;
                    console.error('[DetailPR] Gagal memuat data detail PR:', error, xhr);
                    let errMessage = 'Terjadi gangguan jaringan saat memuat data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errMessage = 'Anda tidak memiliki hak akses untuk melihat pengajuan ini.';
                    } else if (xhr.status === 404) {
                        errMessage = 'Pengajuan pembelian tidak ditemukan atau sudah dihapus.';
                    }

                    $('#detail_status_badge').html('<span class="badge bg-danger rounded-pill px-3 py-1">Gagal Memuat</span>');
                    $('#detail_items_tbody').html(`
                        <tr>
                            <td colspan="10" class="text-center py-4 text-danger">
                                <i class="bi bi-exclamation-octagon fs-3 d-block mb-2 text-danger"></i>
                                <b>${safeEscapeHtml(errMessage)}</b><br>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-3 btn-retry-detail" data-id="${id}">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Coba Muat Ulang
                                </button>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        $(document).off('click', '.btn-detail').on('click', '.btn-detail', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            loadPrDetail(id);
        });

        $(document).on('click', '.btn-retry-detail', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            loadPrDetail(id);
        });

        // ===== PREVENT ARIA-HIDDEN FOCUS CONSOLE WARNING ON MODAL CLOSE =====
        $('#modalDetailPR').on('hide.bs.modal', function () {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
        });

        // ===== APPROVAL ACTION (APPROVE) =====
        $(document).on('click', '.btn-approve-action', function () {
            if (!currentDetailId) return;
            
            Swal.fire({
                title: 'Setujui Pengajuan?',
                text: 'Pengajuan pembelian ini akan disetujui dan dilanjutkan ke alur pemesanan (PO).',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let targetId = currentDetailId;
                    
                    // 1. Instant Modal Hide (0ms latency)
                    $('#modalDetailPR').modal('hide');

                    // 2. Instant Main Table Row Update (0ms)
                    let tr = $(`button.btn-detail[data-id="${targetId}"]`).closest('tr');
                    let oldStatusHtml = tr.find('td:nth-child(8)').html();
                    let oldActionsHtml = tr.find('td:nth-child(9)').html();

                    if (tr.length) {
                        tr.find('td:nth-child(8)').html('<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">Approved</span>');
                        tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').remove();
                    }

                    // 3. Instant Centered Success Alert (0ms)
                    Swal.fire({
                        title: 'Disetujui!',
                        text: 'Pengajuan pembelian berhasil disetujui.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    // 4. Background AJAX Sync
                    $.ajax({
                        url: `/purchase-requisition/${targetId}/approve`,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function (res) {
                            if (typeof window.reloadTablePR === 'function') {
                                window.reloadTablePR();
                            }
                        },
                        error: function (err) {
                            if (tr.length) {
                                tr.find('td:nth-child(8)').html(oldStatusHtml);
                                tr.find('td:nth-child(9)').html(oldActionsHtml);
                            }
                            let errorMsg = 'Terjadi kesalahan sistem saat menyetujui.';
                            if (err.responseJSON && err.responseJSON.message) errorMsg = err.responseJSON.message;
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Prevent Bootstrap modal from stealing focus when SweetAlert2 textarea is opened
        $(document).on('focusin.bs.modal', function (e) {
            if ($(e.target).closest('.swal2-container').length) {
                e.stopImmediatePropagation();
            }
        });

        // ===== APPROVAL ACTION (REJECT) =====
        $(document).on('click', '.btn-reject-action', function () {
            if (!currentDetailId) return;

            let targetId = currentDetailId;

            // Hide detail modal first so Bootstrap focus enforcement is completely dismissed
            $('#modalDetailPR').modal('hide');

            setTimeout(() => {
                Swal.fire({
                    title: 'Tolak Pengajuan?',
                    text: 'Pengajuan ini akan ditolak dan dikembalikan ke pemohon.',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan penolakan (opsional)...',
                    inputAttributes: {
                        maxlength: 200,
                        style: 'font-size: 0.9rem; border-radius: 8px;'
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal',
                    didOpen: () => {
                        setTimeout(() => {
                            const textarea = Swal.getInput();
                            if (textarea) {
                                textarea.focus();
                            }
                        }, 50);
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let alasan = result.value || '';

                        // Instant Main Table Row Update (0ms)
                        let tr = $(`button.btn-detail[data-id="${targetId}"]`).closest('tr');
                        let oldStatusHtml = tr.find('td:nth-child(8)').html();
                        let oldActionsHtml = tr.find('td:nth-child(9)').html();

                        if (tr.length) {
                            tr.find('td:nth-child(8)').html('<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-circle me-1"></i>Rejected</span>');
                            tr.find('.btn-approve, .btn-reject, .btn-edit, .btn-delete').remove();
                        }

                        // Instant Centered Success Alert (0ms)
                        Swal.fire({
                            title: 'Ditolak!',
                            text: 'Pengajuan pembelian telah ditolak.',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });

                        // Background AJAX Sync
                        $.ajax({
                            url: `/purchase-requisition/${targetId}/reject`,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                alasan: alasan
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            success: function (res) {
                                if (typeof window.reloadTablePR === 'function') {
                                    window.reloadTablePR();
                                }
                            },
                            error: function (err) {
                                if (tr.length) {
                                    tr.find('td:nth-child(8)').html(oldStatusHtml);
                                    tr.find('td:nth-child(9)').html(oldActionsHtml);
                                }
                                let errorMsg = 'Terjadi kesalahan sistem saat menolak.';
                                if (err.responseJSON && err.responseJSON.message) errorMsg = err.responseJSON.message;
                                Swal.fire('Gagal!', errorMsg, 'error');
                            }
                        });
                    } else if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                        // Re-open detail modal if user cancels
                        $(`button.btn-detail[data-id="${targetId}"]`).trigger('click');
                    }
                });
            }, 150);
        });
    });
</script>
@endpush
