{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detail View & Review Closing Stock Opname (View & JS) --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailOpname" tabindex="-1" aria-labelledby="modalDetailOpnameLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalDetailOpnameLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Rincian Transaksi Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="modalDetailOpnameBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 pb-4 pb-sm-2 d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2" id="modalDetailOpnameFooter">
                <button type="button" class="btn btn-secondary btn-sm px-4 fw-semibold rounded-2 w-100 w-sm-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
                <div id="modalDetailOpnameActionButtons" class="d-flex flex-row gap-2 w-100 w-sm-auto justify-content-end">
                    <!-- Dynamic action buttons -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT KHUSUS TAMPILAN RINCIAN & CLOSING REVIEW         --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
if (typeof window.formatRupiah !== 'function') {
    window.formatRupiah = function(val) {
        if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
        let num = parseFloat(val);
        let sign = num < 0 ? '-' : '';
        let absVal = Math.abs(num);
        let formatted = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(absVal);
        return sign + 'Rp ' + formatted;
    };
}

function showDetailOpname(id) {
    $('#modalDetailOpname').modal('show');
    $('#modalDetailOpnameBody').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `);

    $.ajax({
        url: `/stock-opname/${id}`,
        method: 'GET',
        success: function (res) {
            try {
            let statusBadge = '';
            if (res.status === 'Draft') statusBadge = `<span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft Plan</span>`;
            else if (res.status === 'Menunggu Persetujuan') statusBadge = `<span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>`;
            else if (res.status === 'Approved') statusBadge = `<span class="badge-approved"><i class="bi bi-check2-circle me-1"></i>Approved (ACC)</span>`;
            else if (res.status === 'Sedang Berjalan') statusBadge = `<span class="badge-running"><i class="bi bi-play-circle me-1"></i>Sedang Berjalan</span>`;
            else if (res.status === 'Menunggu Closing') statusBadge = `<span class="badge-closing"><i class="bi bi-flag me-1"></i>Menunggu Closing</span>`;
            else if (res.status === 'Closed') statusBadge = `<span class="badge-completed"><i class="bi bi-check-all me-1"></i>Closed</span>`;
            else statusBadge = `<span class="badge-rejected"><i class="bi bi-x-circle me-1"></i>Rejected</span>`;

            let rejectAlertHtml = '';
            if (res.status === 'Rejected') {
                rejectAlertHtml = `
                <div class="alert alert-danger py-2 mb-3 small d-flex align-items-center">
                    <i class="bi bi-x-octagon-fill me-2 fs-5"></i>
                    <div><strong>Alasan Penolakan:</strong> ${res.alasan_reject}</div>
                </div>`;
            }

            let closingReviewHeaderHtml = '';
            if (res.status === 'Menunggu Closing' || res.status === 'Closed') {
                let nettoClass = res.nilai_selisih_netto >= 0 ? 'text-success' : 'text-danger';
                let nettoSign = res.nilai_selisih_netto > 0 ? '+' : '';
                
                if (res.can_see_financials) {
                    closingReviewHeaderHtml = `
                    <div class="card p-3 mb-4 shadow-none bg-light border">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Ringkasan Hasil Perhitungan & Valuasi Fisik</h6>
                        <div class="row g-2 text-center">
                            <div class="col-6 col-md-2 border-end">
                                <span class="text-muted small d-block">Total Item</span>
                                <strong class="fs-5 text-dark">${res.total_items}</strong>
                            </div>
                            <div class="col-6 col-md-2 border-end">
                                <span class="text-muted small d-block">Dihitung</span>
                                <strong class="fs-5 text-success">${res.items_dihitung}</strong>
                            </div>
                            <div class="col-6 col-md-2 border-end">
                                <span class="text-muted small d-block">Belum Dihitung</span>
                                <strong class="fs-5 text-warning">${res.items_belum}</strong>
                            </div>
                            <div class="col-6 col-md-2 border-end">
                                <span class="text-muted small d-block">Selisih Lebih (+)</span>
                                <strong class="text-success small">${formatRupiah(res.nilai_selisih_lebih)}</strong>
                            </div>
                            <div class="col-6 col-md-2 border-end">
                                <span class="text-muted small d-block">Selisih Kurang (-)</span>
                                <strong class="text-danger small">${formatRupiah(res.nilai_selisih_kurang)}</strong>
                            </div>
                            <div class="col-6 col-md-2">
                                <span class="text-muted small d-block">Net Selisih</span>
                                <strong class="${nettoClass} small">${nettoSign}${formatRupiah(res.nilai_selisih_netto)}</strong>
                            </div>
                        </div>
                    </div>`;
                } else {
                    closingReviewHeaderHtml = `
                    <div class="card p-3 mb-4 shadow-none bg-light border">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Ringkasan Hasil Perhitungan Fisik</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4 border-end">
                                <span class="text-muted small d-block">Total Item</span>
                                <strong class="fs-5 text-dark">${res.total_items}</strong>
                            </div>
                            <div class="col-4 border-end">
                                <span class="text-muted small d-block">Dihitung</span>
                                <strong class="fs-5 text-success">${res.items_dihitung}</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">Belum Dihitung</span>
                                <strong class="fs-5 text-warning">${res.items_belum}</strong>
                            </div>
                        </div>
                    </div>`;
                }
            }

            let jurnalBannerHtml = '';
            if (res.status === 'Closed' && res.can_view_jurnal) {
                if (res.jurnal) {
                    jurnalBannerHtml = `
                    <div class="card p-3 mb-4 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-info mb-1"><i class="bi bi-journal-check me-2"></i>Jurnal Akuntansi Terposting</h6>
                                <small class="text-secondary">Nomor Jurnal: <code class="fw-bold text-dark">${res.jurnal.nomor_jurnal}</code> | Tanggal: ${res.jurnal.tanggal_jurnal}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-info text-dark fw-bold rounded-pill px-3 shadow-sm" onclick="showOpnameJurnalModal()">
                                <i class="bi bi-eye-fill me-1"></i> Lihat Voucher Jurnal
                            </button>
                        </div>
                    </div>`;
                    window.currentOpnameJurnal = res.jurnal;
                }
            }

            let actionButtonsHtml = '';
            let coaPreviewHtml = '';
            if (res.status === 'Menunggu Closing') {
                window.availableOpnameCoas = res.available_coas || [];
                window.selectedOpnameInvCoaId = res.preview_coa_inventory ? res.preview_coa_inventory.id : null;
                window.selectedOpnameAdjCoaId = res.preview_coa_adjustment ? res.preview_coa_adjustment.id : null;

                if (res.can_view_jurnal) {
                    let invCoaText = res.preview_coa_inventory 
                        ? `<div id="displayInvCoaBox"><code class="fw-bold text-dark me-1">${res.preview_coa_inventory.code}</code> ${res.preview_coa_inventory.name}</div>` 
                        : `<div id="displayInvCoaBox"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Terkonfigurasi</span></div>`;

                    let adjCoaText = res.preview_coa_adjustment 
                        ? `<div id="displayAdjCoaBox"><code class="fw-bold text-dark me-1">${res.preview_coa_adjustment.code}</code> ${res.preview_coa_adjustment.name}</div>` 
                        : `<div id="displayAdjCoaBox"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Terkonfigurasi</span></div>`;

                    coaPreviewHtml = `
                    <div class="card p-3 mb-3 bg-light border rounded-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Pratinjau Akun Posting Jurnal</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small fw-bold">Otomatis / Kustom</span>
                        </div>
                        <div class="row g-3 small">
                            <div class="col-md-6 border-end">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted fw-semibold">Akun Persediaan Barang:</span>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0" onclick="openSelectCoaOpnameModal('inventory')" title="Pilih / Cari Akun Persediaan">
                                        <i class="bi bi-search me-1"></i> Ubah Akun
                                    </button>
                                </div>
                                ${invCoaText}
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted fw-semibold">Akun Selisih / Penyesuaian Opname:</span>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0" onclick="openSelectCoaOpnameModal('adjustment')" title="Pilih / Cari Akun Selisih">
                                        <i class="bi bi-search me-1"></i> Ubah Akun
                                    </button>
                                </div>
                                ${adjCoaText}
                            </div>
                        </div>
                    </div>`;
                }

                actionButtonsHtml = `
                @can('closing-opname')
                <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-2 shadow-sm flex-fill flex-sm-grow-0" onclick="postClosingOpname(${res.id}, '${res.nomor_opname}')">
                    <i class="bi bi-lock-fill me-1"></i> Posting Adjustment (Close)
                </button>
                @endcan`;
            } else if (res.status === 'Menunggu Persetujuan') {
                actionButtonsHtml = `
                @can('setuju-opname')
                <button type="button" class="btn btn-outline-danger fw-semibold px-3 py-2 rounded-2 flex-fill flex-sm-grow-0" onclick="rejectOpnameModal(${res.id}, '${res.nomor_opname}')">
                    <i class="bi bi-x-lg me-1"></i> Tolak Proposal
                </button>
                <button type="button" class="btn btn-success fw-semibold px-4 py-2 rounded-2 shadow-sm flex-fill flex-sm-grow-0" onclick="approveOpnameModal(${res.id}, '${res.nomor_opname}')">
                    <i class="bi bi-check-lg me-1"></i> Setujui (ACC)
                </button>
                @endcan`;
            } else if (res.status === 'Draft' || res.status === 'Rejected') {
                const editBtnText = res.status === 'Rejected' ? 'Perbaiki & Edit' : 'Edit Proposal';
                const submitBtnText = res.status === 'Rejected' ? 'Ajukan Ulang ke Atasan' : 'Ajukan ke Atasan';
                actionButtonsHtml = `
                @can('tambah-opname')
                <button type="button" class="btn btn-warning fw-semibold px-3 py-2 rounded-2 text-dark flex-fill flex-sm-grow-0" onclick="$('#modalDetailOpname').modal('hide'); editOpname(${res.id})">
                    <i class="bi bi-pencil-square me-1"></i> ${editBtnText}
                </button>
                <button type="button" class="btn btn-primary fw-semibold px-4 py-2 rounded-2 shadow-sm flex-fill flex-sm-grow-0" onclick="$('#modalDetailOpname').modal('hide'); submitOpname(${res.id}, '${res.nomor_opname}')">
                    <i class="bi bi-send-fill me-1"></i> ${submitBtnText}
                </button>
                @endcan`;
            }

            let html = `
            ${rejectAlertHtml}
            ${closingReviewHeaderHtml}
            ${jurnalBannerHtml}
            ${res.status === 'Menunggu Closing' ? coaPreviewHtml : ''}

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card p-3 shadow-none bg-light border-0 h-100">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted py-1" width="35%">Nomor Pengajuan</td><td class="fw-bold py-1 text-dark">${res.nomor_opname}</td></tr>
                            <tr><td class="text-muted py-1">Tgl Pengajuan</td><td class="py-1 text-dark">${res.tanggal_opname}</td></tr>
                            <tr><td class="text-muted py-1">Rencana Periode</td><td class="py-1 text-dark fw-semibold text-primary">${res.tanggal_mulai} s/d ${res.tanggal_selesai}</td></tr>
                            <tr><td class="text-muted py-1">Gudang</td><td class="py-1 text-dark">${res.gudang_nama}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3 shadow-none bg-light border-0 h-100">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted py-1" width="35%">Status Alur</td><td class="py-1">${statusBadge}</td></tr>
                            <tr><td class="text-muted py-1">Dibuat Oleh</td><td class="py-1 text-dark">${res.creator_name}</td></tr>
                            <tr><td class="text-muted py-1">Disetujui Oleh</td><td class="py-1 text-dark">${res.approver_name !== '-' ? res.approver_name + ' <small class="text-muted">('+res.approved_at+')</small>' : '-'}</td></tr>
                            <tr><td class="text-muted py-1">Closed Oleh</td><td class="py-1 text-dark">${res.closer_name !== '-' ? res.closer_name + ' <small class="text-muted">('+res.closed_at+')</small>' : '-'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card p-2 shadow-none bg-light border-0">
                        <span class="text-muted small px-2">Alasan Pengajuan (Keterangan):</span>
                        <div class="text-dark small px-2 py-1 text-wrap fw-semibold">${res.keterangan}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3 mt-4 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="bi bi-list-task me-2 text-primary fs-5"></i>
                    <span>Rincian Penyesuaian Barang</span>
                </h6>
                <div class="d-flex gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
                    <a href="/stock-opname/${id}/print" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm flex-fill flex-sm-grow-0 text-center">
                        <i class="bi bi-file-pdf me-1"></i> Cetak PDF
                    </a>
                    <button type="button" onclick="exportOpnameExcel(${res.id}, '${res.nomor_opname}')" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm flex-fill flex-sm-grow-0 text-center">
                        <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped" id="table_detail_items" style="width:100%;">
                    <thead class="table-dark">
                        <tr>
                            <th class="small text-center text-white" width="5%">No</th>
                            <th class="small text-white" width="15%">Kode</th>
                            <th class="small text-white" width="25%">Nama Barang</th>
                            <th class="small text-center text-white" width="10%">Stok Sistem</th>
                            <th class="small text-center text-white" width="12%">Stok Fisik</th>
                            <th class="small text-center text-white" width="10%">Selisih Qty</th>
                            <th class="small text-end text-white" width="13%">Nilai Selisih</th>
                            <th class="small text-white" width="15%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>`;

            $('#modalDetailOpnameBody').html(html);
            $('#modalDetailOpnameActionButtons').html(actionButtonsHtml);

            setTimeout(() => {
                if ($.fn.DataTable.isDataTable('#table_detail_items')) {
                    $('#table_detail_items').DataTable().destroy();
                }
                
                $('#table_detail_items').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: `/stock-opname/${id}/details-datatable`,
                        type: 'GET'
                    },
                    columns: [
                        { data: 'index', name: 'index', orderable: false, searchable: false, className: 'text-center text-muted small' },
                        { data: 'barang_kode', name: 'barang_kode', className: 'fw-semibold small' },
                        { data: 'barang_nama', name: 'barang_nama', className: 'small' },
                        { 
                            data: 'stok_sistem', 
                            name: 'stok_sistem', 
                            className: 'text-center small',
                            render: function(data, type, row) {
                                let formatted = (data % 1 === 0) ? data.toFixed(0) : data.toFixed(2).replace(/\.?0+$/, '');
                                return `${formatted} ${row.satuan}`;
                            }
                        },
                        { 
                            data: 'stok_fisik', 
                            name: 'stok_fisik', 
                            className: 'text-center small fw-semibold',
                            render: function(data, type, row) {
                                if (data === null) return '—';
                                let formatted = (data % 1 === 0) ? data.toFixed(0) : data.toFixed(2).replace(/\.?0+$/, '');
                                return `${formatted} ${row.satuan}`;
                            }
                        },
                        { 
                            data: 'selisih', 
                            name: 'selisih', 
                            className: 'text-center fw-bold',
                            render: function(data, type, row) {
                                if (data === null) return '—';
                                let formatted = (data % 1 === 0) ? data.toFixed(0) : data.toFixed(2).replace(/\.?0+$/, '');
                                let diffClass = 'selisih-zero';
                                let sign = '';
                                if (data > 0) { diffClass = 'selisih-plus'; sign = '+'; }
                                else if (data < 0) { diffClass = 'selisih-minus'; }
                                return `<span class="${diffClass}">${sign}${formatted}</span>`;
                            }
                        },
                        { 
                            data: 'nilai_selisih', 
                            name: 'nilai_selisih', 
                            className: 'text-end small',
                            visible: res.can_see_financials,
                            render: function(data, type, row) {
                                if (row.selisih === null) return '—';
                                let diffClass = row.selisih > 0 ? 'selisih-plus' : (row.selisih < 0 ? 'selisih-minus' : 'selisih-zero');
                                return `<span class="${diffClass}">${formatRupiah(data)}</span>`;
                            }
                        },
                        { 
                            data: 'catatan', 
                            name: 'catatan', 
                            className: 'small text-muted text-wrap',
                            render: function(data) {
                                return data ? data : '—';
                            }
                        }
                    ],
                    order: [],
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    language: {
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_',
                        search: 'Cari:',
                        paginate: { previous: '←', next: '→' },
                        processing: '<div class="spinner-border text-primary spinner-border-sm me-2"></div> Memuat data...'
                    }
                });
                if (typeof initTableDragScroll === 'function') initTableDragScroll();
            }, 100);
            } catch (err) {
                console.error("DetailRenderError:", err);
                $('#modalDetailOpnameBody').html(`<div class="alert alert-danger py-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terjadi Kesalahan Tampilan:</strong> ${err.message}</div>`);
            }
        },
        error: function (xhr) {
            let msg = xhr.responseJSON?.message || 'Gagal memuat detail stock opname (Status ' + xhr.status + ').';
            $('#modalDetailOpnameBody').html(`<div class="alert alert-danger py-3"><i class="bi bi-exclamation-octagon-fill me-2"></i><strong>Gagal Memuat:</strong> ${msg}</div>`);
        }
    });
}

function approveOpnameModal(id, nomor) {
    $('#modalDetailOpname').modal('hide');
    approveOpname(id, nomor);
}

function rejectOpnameModal(id, nomor) {
    $('#modalDetailOpname').modal('hide');
    rejectOpname(id, nomor);
}

function postClosingOpname(id, nomor) {
    Swal.fire({
        title: 'Konfirmasi Closing & Posting Adjustment',
        html: `
        <div class="text-center p-2">
            <div class="mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 fs-6 rounded-pill fw-bold">
                    <i class="bi bi-file-earmark-check me-1"></i> ${nomor}
                </span>
            </div>
            <p class="text-dark fs-6 mb-2">Apakah Anda yakin ingin memproses <strong>Closing Stock Opname</strong> ini?</p>
            <div class="alert alert-warning py-2.5 mb-0 border-0 rounded-3 text-start small">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Dampak Sistem:</div>
                <ul class="mb-0 ps-3 text-secondary">
                    <li>Stok aktual gudang akan otomatis disinkronkan.</li>
                    <li>Jurnal akuntansi penyesuaian akan langsung diposting ke Buku Besar.</li>
                </ul>
            </div>
        </div>`,
        icon: 'question',
        iconColor: '#3b82f6',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-lock-fill me-1"></i> Ya, Posting & Close!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-4 shadow-lg border-0',
            confirmButton: 'px-4 py-2 fw-bold rounded-3',
            cancelButton: 'px-4 py-2 fw-bold rounded-3'
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Memproses Closing...',
            text: 'Mohon tunggu, sedang memperbarui stok dan memposting jurnal akuntansi.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: `/stock-opname/${id}/close`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                override_inventory_coa_id: window.selectedOpnameInvCoaId,
                override_adjustment_coa_id: window.selectedOpnameAdjCoaId
            },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                reloadTableOpname();
                Swal.fire({
                    icon: 'success',
                    title: 'Closed & Posted!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-4 shadow-lg border-0' }
                });
                $('#modalDetailOpname').modal('hide');
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Closing',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memproses closing.',
                    customClass: { popup: 'rounded-4 shadow-lg border-0' }
                });
            }
        });
    });
}
</script>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL SEARCH & PILIH AKUN COA FOR STOCK OPNAME CLOSING        --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalSelectCoaOpname" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header with Sleek Accent -->
            <div class="modal-header bg-dark text-white p-3 px-4 border-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0 fs-6" id="titleSelectCoaOpname">Pilih Akun Akuntansi</h6>
                        <small class="text-white-50" style="font-size: 11px;">Pilih akun COA yang sesuai untuk alokasi penyesuaian stok opname</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4 bg-light bg-opacity-50" style="overflow-y: auto;">
                <!-- Search Input Container -->
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute start-0 ms-3 top-50 translate-middle-y text-muted fs-6"></i>
                    <input type="text" id="inputSearchCoaOpname" class="form-control form-control-lg rounded-3 ps-5 fs-6 shadow-sm border" placeholder="Cari berdasarkan Kode Akun, Nama Akun, atau Tipe..." onkeyup="filterOpnameCoaList()" autocomplete="off">
                </div>

                <!-- Table List Container -->
                <div class="card border rounded-3 shadow-sm" style="overflow: hidden;">
                    <div class="table-responsive" style="max-height: 48vh; overflow-y: auto !important; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableSelectCoaOpname" style="width: 100% !important;">
                            <thead class="text-uppercase text-secondary fw-bold border-bottom sticky-top bg-white" style="font-size: 11px; letter-spacing: 0.5px; z-index: 5; background-color: #ffffff;">
                                <tr>
                                    <th class="ps-3 py-3" style="min-width: 110px; width: 22%;">KODE AKUN</th>
                                    <th class="py-3" style="min-width: 180px; width: 45%;">NAMA AKUN COA</th>
                                    <th class="py-3" style="min-width: 100px; width: 18%;">TIPE AKUN</th>
                                    <th class="pe-3 py-3 text-center" style="min-width: 90px; width: 15%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySelectCoaOpname" class="small">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="countCoaFoundText">Menampilkan akun COA aktif</span>
                <button type="button" class="btn btn-secondary btn-sm px-4 fw-semibold rounded-2" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Batal
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL VOUCHER JURNAL AKUNTANSI                              --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalJurnalOpname" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalJurnalOpnameLabel">
                    <i class="bi bi-journal-bookmark-fill me-2 text-info"></i>Voucher Jurnal Akuntansi Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 border">
                    <div>
                        <span class="text-muted small d-block">Nomor Jurnal</span>
                        <strong class="fs-5 text-primary" id="jurnalOpnameNomor">-</strong>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block">Tanggal Posting</span>
                        <strong class="text-dark" id="jurnalOpnameTanggal">-</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle small">
                        <thead class="table-dark">
                            <tr>
                                <th width="18%">Kode Akun</th>
                                <th width="38%">Nama Akun</th>
                                <th class="text-end" width="22%">Debit</th>
                                <th class="text-end" width="22%">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="jurnalOpnameTbody"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total Balance:</td>
                                <td class="text-end text-success" id="jurnalOpnameTotalDebit">Rp 0</td>
                                <td class="text-end text-danger" id="jurnalOpnameTotalKredit">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-3">
                <button type="button" class="btn btn-secondary px-4 fw-semibold rounded-2" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
window.activeCoaSelectionType = null;

$(document).on('hidden.bs.modal', '#modalSelectCoaOpname', function () {
    if ($('#modalDetailOpname').hasClass('show')) {
        $('body').addClass('modal-open');
    }
});

function openSelectCoaOpnameModal(type) {
    window.activeCoaSelectionType = type;
    const title = type === 'inventory' ? 'Pilih Akun Persediaan Barang' : 'Pilih Akun Selisih / Penyesuaian Opname';
    $('#titleSelectCoaOpname').html(`${title}`);
    $('#inputSearchCoaOpname').val('');
    
    renderOpnameCoaTable(window.availableOpnameCoas || []);
    $('#modalSelectCoaOpname').modal('show');
}

function renderOpnameCoaTable(list) {
    let rows = '';
    if (!list || list.length === 0) {
        rows = `
        <tr>
            <td colspan="4" class="text-center text-muted py-4">
                <i class="bi bi-search fs-3 d-block text-secondary opacity-50 mb-1"></i>
                Tidak ada akun COA yang cocok dengan pencarian.
            </td>
        </tr>`;
    } else {
        list.forEach(c => {
            const escapedName = (c.name || '').replace(/'/g, "\\'");
            
            // Color badges based on type
            let badgeClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary';
            const t = (c.type || '').toLowerCase();
            if (t.includes('aset') || t.includes('asset') || t.includes('persediaan')) {
                badgeClass = 'bg-info bg-opacity-10 text-info border-info';
            } else if (t.includes('beban') || t.includes('expense') || t.includes('hpp') || t.includes('cogs')) {
                badgeClass = 'bg-warning bg-opacity-10 text-dark border-warning';
            } else if (t.includes('pendapatan') || t.includes('revenue')) {
                badgeClass = 'bg-success bg-opacity-10 text-success border-success';
            }

            rows += `
            <tr class="align-middle">
                <td class="ps-3 fw-bold">
                    <span class="font-monospace text-dark bg-light px-2 py-1 rounded border small">${c.code}</span>
                </td>
                <td>
                    <div class="fw-bold text-dark">${c.name}</div>
                </td>
                <td>
                    <span class="badge ${badgeClass} border rounded-pill px-2 py-1 fw-semibold small">${c.type || '-'}</span>
                </td>
                <td class="pe-3 text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold shadow-sm d-inline-flex align-items-center gap-1" onclick="pickOpnameCoa(${c.id}, '${c.code}', '${escapedName}')">
                        <i class="bi bi-check-lg"></i> Pilih
                    </button>
                </td>
            </tr>`;
        });
    }
    $('#tbodySelectCoaOpname').html(rows);
    $('#countCoaFoundText').text(`Menampilkan ${list ? list.length : 0} akun COA aktif`);
}

function filterOpnameCoaList() {
    const q = $('#inputSearchCoaOpname').val().toLowerCase();
    const filtered = (window.availableOpnameCoas || []).filter(c => 
        (c.code && c.code.toLowerCase().includes(q)) || 
        (c.name && c.name.toLowerCase().includes(q)) ||
        (c.type && c.type.toLowerCase().includes(q))
    );
    renderOpnameCoaTable(filtered);
}

function pickOpnameCoa(id, code, name) {
    if (window.activeCoaSelectionType === 'inventory') {
        window.selectedOpnameInvCoaId = id;
        $('#displayInvCoaBox').html(`
            <code class="fw-bold text-dark me-1">${code}</code> ${name} 
            <span class="badge bg-warning text-dark border border-warning border-opacity-50 ms-1" style="font-size: 10px;">Pilihan User</span>
        `);
    } else if (window.activeCoaSelectionType === 'adjustment') {
        window.selectedOpnameAdjCoaId = id;
        $('#displayAdjCoaBox').html(`
            <code class="fw-bold text-dark me-1">${code}</code> ${name} 
            <span class="badge bg-warning text-dark border border-warning border-opacity-50 ms-1" style="font-size: 10px;">Pilihan User</span>
        `);
    }

    $('#modalSelectCoaOpname').modal('hide');

    Swal.fire({
        title: 'Akun Terpilih!',
        text: `Akun ${code} - ${name} dipilih untuk posting opname ini.`,
        icon: 'success',
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

function showOpnameJurnalModal() {
    const j = window.currentOpnameJurnal;
    if (!j) return;

    $('#jurnalOpnameNomor').text(j.nomor_jurnal);
    $('#jurnalOpnameTanggal').text(j.tanggal_jurnal);

    let rowsHtml = '';
    (j.details || []).forEach(d => {
        rowsHtml += `
        <tr>
            <td class="fw-bold text-dark">${d.account_code}</td>
            <td>${d.account_name} <br><small class="text-muted">${d.keterangan || ''}</small></td>
            <td class="text-end ${d.debit > 0 ? 'fw-bold text-success' : 'text-muted'}">${d.debit > 0 ? formatRupiah(d.debit) : '-'}</td>
            <td class="text-end ${d.kredit > 0 ? 'fw-bold text-danger' : 'text-muted'}">${d.kredit > 0 ? formatRupiah(d.kredit) : '-'}</td>
        </tr>`;
    });

    $('#jurnalOpnameTbody').html(rowsHtml);
    $('#jurnalOpnameTotalDebit').text(formatRupiah(j.total_debit));
    $('#jurnalOpnameTotalKredit').text(formatRupiah(j.total_kredit));

    $('#modalJurnalOpname').modal('show');
}

function exportOpnameExcel(id, nomor) {
    Swal.fire({
        title: 'Mempersiapkan File Excel...',
        text: 'Mengunduh data hasil Stock Opname ' + nomor,
        icon: 'info',
        timer: 1200,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });

    window.location.href = `/stock-opname/${id}/export-excel`;

    setTimeout(function() {
        Swal.fire({
            title: 'Berhasil Diekspor!',
            text: 'File Excel Stock Opname ' + nomor + ' berhasil diunduh.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }, 1000);
}
</script>
@endpush
