{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Input & Edit Hitungan Stok Fisik (View & JS)        --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalInputFisik" tabindex="-1" aria-labelledby="modalInputFisikLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-info bg-gradient text-dark rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalInputFisikLabel">
                    <i class="bi bi-pencil-square me-2"></i>Input Hitungan Stok Fisik
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 mb-3 small d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                    <div>
                        Masukkan jumlah stok riil hasil penghitungan fisik di gudang. Selisih dengan sistem akan langsung dikalkulasikan.
                    </div>
                </div>

                <form id="formInputFisik">
                    @csrf
                    <input type="hidden" name="opname_id" id="input_so_id">
                    <div id="deleted_items_container"></div>

                    <div class="row g-2 mb-3 partial-filter-card p-3">
                        <div class="col-md-4">
                            <span class="text-muted small">Nomor Rencana:</span>
                            <div class="fw-bold fs-6" id="input_so_nomor">-</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small">Gudang Lokasi:</span>
                            <div class="fw-semibold" id="input_so_gudang">-</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small">Tgl Direncanakan:</span>
                            <div id="input_so_tanggal">-</div>
                        </div>
                    </div>

                    <!-- Barcode / Code Quick Scan Panel -->
                    <div class="card p-3 mb-3 border-info shadow-sm">
                        <h6 class="fw-bold mb-2 text-info"><i class="bi bi-upc-scan me-2"></i>Quick Input & Barcode Scan</h6>
                        <div class="row g-2 align-items-end">
                            <!-- Row 1 -->
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Ketik Kode/Nama / Scan Barcode</label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <input type="text" id="scan_kode" class="form-control" placeholder="Ketik kode/nama atau scan barcode..." autocomplete="off">
                                        <button type="button" class="btn btn-info text-dark" id="btn_scan_camera" title="Scan Barcode Kamera">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>
                                    <div id="scan_autocomplete_container" class="list-group position-absolute w-100 shadow-lg border" style="display: none; z-index: 2050; max-height: 260px; overflow-y: auto; top: calc(100% + 2px); left: 0; background: #ffffff; border-radius: 6px;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-bold text-muted mb-1">Nama Barang</label>
                                <input type="text" id="scan_nama" class="form-control" readonly placeholder="Nama barang otomatis...">
                            </div>
                            
                            <!-- Row 2 -->
                            <div class="col-4 col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Stok Sistem</label>
                                <input type="text" id="scan_sistem" class="form-control text-center" readonly placeholder="0">
                            </div>
                            <div class="col-4 col-md-3">
                                <label class="form-label small fw-bold text-info mb-1">Stok Fisik (Riil)</label>
                                <input type="number" id="scan_fisik" class="form-control text-center border-info" min="0" step="0.01" placeholder="0">
                            </div>
                            <div class="col-4 col-md-2 text-center">
                                <label class="form-label small fw-bold text-muted mb-1 d-block text-center">Selisih</label>
                                <div class="text-center py-1 fw-bold" id="scan_selisih_wrapper">
                                    <span id="scan_selisih" class="selisih-indicator selisih-zero">-</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold text-info mb-1">Catatan Penyesuaian</label>
                                <input type="text" id="scan_catatan" class="form-control border-info" placeholder="Contoh: Rusak, Hilang, Kurang...">
                            </div>

                            <div class="col-12 mt-2">
                                <button type="button" class="btn btn-info w-100 text-dark fw-bold" id="btn_scan_add">
                                    <i class="bi bi-plus-circle me-1"></i> Masukkan ke Daftar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="fw-bold mb-0 text-info d-flex align-items-center me-2">
                                <i class="bi bi-list-check me-1.5 fs-5"></i>
                                <span>Daftar Hitungan Fisik</span>
                            </h6>
                            <div class="btn-group btn-group-sm shadow-xs" role="group">
                                <button type="button" class="btn btn-primary active fw-semibold" id="tab_btn_sesi_baru" onclick="switchFisikTab('session')">
                                    <i class="bi bi-upc-scan me-1"></i>Sesi Scan Baru (<span id="count_sesi_baru">0</span>)
                                </button>
                                <button type="button" class="btn btn-outline-secondary fw-semibold" id="tab_btn_sudah_disimpan" onclick="switchFisikTab('saved')">
                                    <i class="bi bi-cloud-check me-1"></i>Sudah Tersimpan di Draf (<span id="count_sudah_disimpan">0</span>)
                                </button>
                            </div>
                        </div>
                        <div id="local_draft_indicator" style="display:none;"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-modern" id="tableInputFisikItems">
                            <thead class="table-dark">
                                <tr>
                                    <th class="small text-center" width="5%">No</th>
                                    <th class="small" width="15%">Kode Barang</th>
                                    <th class="small" width="20%">Nama Barang</th>
                                    <th class="small text-center" width="10%">Stok Sistem</th>
                                    <th class="small text-center" width="15%">Stok Fisik</th>
                                    <th class="small text-center" width="10%">Selisih</th>
                                    <th class="small" width="15%">Catatan</th>
                                    <th class="small text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="input_fisik_tbody">
                                <tr id="empty_row_placeholder">
                                    <td colspan="8" class="text-center py-4 bg-light bg-opacity-50 border-0 rounded-3">
                                        <div class="d-flex flex-column align-items-center justify-content-center py-3 px-2 w-100 text-center">
                                            <div class="mb-2 p-3 rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                                                <i class="bi bi-upc-scan fs-3"></i>
                                            </div>
                                            <div class="fw-bold text-dark fs-6 text-center w-100">Belum Ada Barang yang Di-scan</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="fisik_alert" class="mt-3" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 pb-4 pb-sm-3">
                <button type="button" class="btn btn-cancel w-100 w-sm-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-draft w-100 w-sm-auto" id="btnSimpanFisikDraft">
                    <i class="bi bi-save me-1"></i> Simpan Draf Progress
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Scanner camera barcode/QR code -->
<div class="modal modal-blur fade" id="modalScanner" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background-color: #0f172a !important; color: #f8fafc !important; border: 1.5px solid #334155 !important;">
            <div class="modal-header border-0 pb-2 d-flex justify-content-between align-items-center px-3 pt-3" style="background: #1e293b !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-3 py-1.5 fw-bold">
                        <i class="bi bi-camera-fill me-1"></i> Scanner Kamera Aktif
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="switchCamera()" title="Ganti Kamera">
                        <i class="bi bi-arrow-repeat fs-6"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow" data-bs-dismiss="modal" onclick="stopCameraScanner()" title="Tutup Kamera">
                        <i class="bi bi-x-lg"></i> Tutup
                    </button>
                </div>
            </div>
            <div class="modal-body p-3 text-center" style="background: #0f172a !important;">
                <div class="scanner-viewport-wrapper my-2">
                    <div id="scanner_reader"></div>
                    <div class="scanner-overlay" style="display: none;">
                        <div class="scanner-target-box">
                            <div class="scanner-corner-tr"></div>
                            <div class="scanner-corner-bl"></div>
                            <div class="scanner-laser-line"></div>
                            <div class="scanner-center-dot"></div>
                            <div class="scanner-guide-dots scanner-dot-left"></div>
                            <div class="scanner-guide-dots scanner-dot-right"></div>
                            <div class="scanner-guide-dots scanner-dot-top"></div>
                            <div class="scanner-guide-dots scanner-dot-bottom"></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="text-white-50 small">Arahkan barcode / QR Code ke dalam area bidik hijau</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 justify-content-center" style="background: #1e293b !important;">
                <button type="button" class="btn btn-danger rounded-pill px-5 py-2 fw-bold shadow-lg d-inline-flex align-items-center gap-2" data-bs-dismiss="modal" onclick="stopCameraScanner()">
                    <i class="bi bi-x-circle-fill fs-5"></i> Selesai & Tutup Kamera
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT KHUSUS EDIT / HITUNGEN STOK FISIK                 --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
// Universal HTML Escaping to prevent XSS and ReferenceErrors
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
window.escapeHtml = escapeHtml;
window.escapeHtmlAdj = escapeHtml;

let currentOpnameDetails = [];
let currentScannedItem = null;
let html5QrcodeScanner = null;
let sessionScannedBarangIds = new Set();
let currentActiveFisikTab = 'session'; // 'session' | 'saved'

function createRowHtml(d, displayedIdx) {
    let sistemVal = parseFloat(d.stok_sistem || 0);
    let sistemFormatted = (sistemVal % 1 === 0) ? sistemVal.toFixed(0) : sistemVal.toFixed(2).replace(/\.?0+$/, '');
    
    let fisikVal = d.stok_fisik !== null ? parseFloat(d.stok_fisik) : '';
    
    let diffVal = d.selisih !== null ? parseFloat(d.selisih) : 0;
    let diffFormatted = d.selisih !== null ? ((diffVal % 1 === 0) ? diffVal.toFixed(0) : diffVal.toFixed(2).replace(/\.?0+$/, '')) : '';
    if (diffVal > 0 && d.selisih !== null) diffFormatted = '+' + diffFormatted;

    let diffClass = 'selisih-zero';
    if (d.selisih !== null) {
        if (diffVal > 0) diffClass = 'selisih-plus';
        else if (diffVal < 0) diffClass = 'selisih-minus';
    }

    let catatanVal = d.catatan || '';

    let hasSerials = d.has_serials || false;
    let snList = d.serial_numbers || [];
    if (typeof snList === 'string') {
        try { snList = JSON.parse(snList); } catch(e) { snList = []; }
    }
    let snCount = Array.isArray(snList) ? snList.length : 0;
    let snBtnClass = 'btn-outline-info text-info';
    if (snCount === sistemVal && sistemVal > 0) {
        snBtnClass = 'btn-outline-success text-success';
    } else if (snCount < sistemVal) {
        snBtnClass = 'btn-outline-danger text-danger';
    } else if (snCount > sistemVal) {
        snBtnClass = 'btn-outline-warning text-dark';
    }

    return `
    <tr id="table_row_barang_${d.barang_id}">
        <td data-label="No" class="text-center text-muted small index-cell fw-bold">${displayedIdx}</td>
        <td data-label="Kode" class="small fw-bold text-primary">${d.barang_kode}</td>
        <td data-label="Barang" class="small fw-semibold">
            <div>${d.barang_nama}</div>
            ${hasSerials ? `
                <div class="mt-1 d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-xs ${snBtnClass} rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" 
                            id="btn_sn_${d.barang_id}" onclick="openModalManageSerialsOpname(${d.barang_id})" title="Cek & Verifikasi Serial Number">
                        <i class="bi bi-upc-scan"></i> <span class="sn-btn-text">Serial (${snCount}/${sistemFormatted})</span>
                    </button>
                    <input type="hidden" id="serials_${d.barang_id}" name="items[${d.barang_id}][serial_numbers]" value='${JSON.stringify(snList)}'>
                </div>
            ` : `
                <input type="hidden" id="serials_${d.barang_id}" name="items[${d.barang_id}][serial_numbers]" value="[]">
            `}
        </td>
        <td data-label="Sistem" class="text-center">
            <span class="stok-sistem-badge" id="badge_sistem_${d.barang_id}">${sistemFormatted}</span>
            <input type="hidden" id="input_sistem_${d.barang_id}" value="${sistemFormatted}">
        </td>
        <td data-label="Fisik">
            <div class="input-group input-group-sm flex-nowrap">
                <input type="number" class="form-control form-control-sm text-center fw-bold" 
                       id="input_fisik_${d.barang_id}" name="items[${d.barang_id}][stok_fisik]" 
                       min="0" step="0.01" value="${fisikVal}" placeholder="0" 
                       oninput="hitungDiffFisik(${d.barang_id})">
                <span class="input-group-text small py-0 text-nowrap">${d.satuan}</span>
                <input type="hidden" name="items[${d.barang_id}][barang_id]" value="${d.barang_id}">
            </div>
        </td>
        <td data-label="Selisih" class="text-center">
            <span class="selisih-indicator ${diffClass}" id="input_diff_label_${d.barang_id}">${diffFormatted === '' ? '-' : diffFormatted}</span>
        </td>
        <td data-label="Catatan">
            <input type="text" class="form-control form-control-sm" 
                   id="input_catatan_${d.barang_id}" name="items[${d.barang_id}][catatan]" 
                   value="${catatanVal}" placeholder="Catatan penyesuaian..."
                   oninput="updateCatatanMemory(${d.barang_id})">
        </td>
        <td data-label="Aksi" class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="hapusRowFisik(${d.barang_id})" title="Hapus dari daftar">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;
}

function updateCatatanMemory(barangId) {
    let notesVal = $(`#input_catatan_${barangId}`).val();
    let match = currentOpnameDetails.find(d => d.barang_id == barangId);
    if (match) {
        match.catatan = notesVal;
    }
}

function updateRowIndexes() {
    $('#input_fisik_tbody tr:not(#empty_row_placeholder)').each(function(idx) {
        $(this).find('.index-cell').text(idx + 1);
    });
}

function updateFisikTabCounters() {
    let sessionCount = sessionScannedBarangIds.size;
    let savedCount = currentOpnameDetails.filter(d => d.stok_fisik !== null && !sessionScannedBarangIds.has(d.barang_id)).length;

    $('#count_sesi_baru').text(sessionCount);
    $('#count_sudah_disimpan').text(savedCount);
}

function switchFisikTab(tab) {
    currentActiveFisikTab = tab;
    if (tab === 'session') {
        $('#tab_btn_sesi_baru').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#tab_btn_sudah_disimpan').removeClass('btn-primary active').addClass('btn-outline-secondary');
    } else {
        $('#tab_btn_sudah_disimpan').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#tab_btn_sesi_baru').removeClass('btn-primary active').addClass('btn-outline-secondary');
    }
    renderFisikTableByCurrentTab();
}

function renderFisikTableByCurrentTab() {
    let tbody = $('#input_fisik_tbody');
    tbody.empty();

    let itemsToRender = [];

    if (currentActiveFisikTab === 'session') {
        itemsToRender = currentOpnameDetails.filter(d => sessionScannedBarangIds.has(d.barang_id));
        if (itemsToRender.length === 0) {
            tbody.html(getEmptyRowHtml());
        } else {
            itemsToRender.forEach((d, idx) => {
                tbody.append(createRowHtml(d, idx + 1));
            });
        }
    } else {
        // Tab 'saved': tampilkan barang yang sudah tersimpan di database
        itemsToRender = currentOpnameDetails.filter(d => d.stok_fisik !== null && !sessionScannedBarangIds.has(d.barang_id));
        if (itemsToRender.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted bg-light bg-opacity-50 border-0 rounded-3">
                        <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary"></i>
                        <span>Belum ada item lain yang tersimpan di draf database.</span>
                    </td>
                </tr>
            `);
        } else {
            itemsToRender.forEach((d, idx) => {
                tbody.append(createRowHtml(d, idx + 1));
            });
        }
    }

    updateRowIndexes();
    updateFisikTabCounters();
}

function saveLocalFisikDraft(id) {
    if (!id) id = $('#input_so_id').val();
    if (!id) return;
    const key = `so_draft_fisik_${id}`;
    const items = [];
    if (currentOpnameDetails && currentOpnameDetails.length) {
        currentOpnameDetails.forEach(d => {
            // Hanya simpan item yang sedang ada di sesi scan saat ini ke draf lokal
            if (sessionScannedBarangIds.has(d.barang_id)) {
                items.push({
                    barang_id: d.barang_id,
                    barang_kode: d.barang_kode,
                    barang_nama: d.barang_nama,
                    stok_sistem: d.stok_sistem,
                    stok_fisik: d.stok_fisik,
                    serial_numbers: d.serial_numbers || [],
                    catatan: d.catatan,
                    selisih: d.selisih
                });
            }
        });
    }
    if (items.length > 0) {
        localStorage.setItem(key, JSON.stringify(items));
    } else {
        localStorage.removeItem(key);
    }
    updateFisikTabCounters();
}

function clearLocalFisikDraft(id) {
    if (!id) id = $('#input_so_id').val();
    if (!id) return;
    localStorage.removeItem(`so_draft_fisik_${id}`);
    $('#local_draft_indicator').hide();
}

function getEmptyRowHtml() {
    return `
        <tr id="empty_row_placeholder">
            <td colspan="8" class="text-center py-4 bg-light bg-opacity-50 border-0 rounded-3">
                <div class="d-flex flex-column align-items-center justify-content-center py-3 px-2 w-100 text-center">
                    <div class="mb-2 p-3 rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                        <i class="bi bi-upc-scan fs-3"></i>
                    </div>
                    <div class="fw-bold text-dark fs-6 text-center w-100">Belum Ada Barang yang Di-scan di Sesi Ini</div>
                    <div class="text-muted small mt-1">Arahkan scanner ke barcode atau QR Code nomor seri untuk mulai menghitung.</div>
                </div>
            </td>
        </tr>
    `;
}

function restoreLocalFisikDraft(id) {
    if (!id) return;
    const key = `so_draft_fisik_${id}`;
    const saved = localStorage.getItem(key);
    if (!saved) return;

    try {
        const localItems = JSON.parse(saved);
        if (Array.isArray(localItems) && localItems.length > 0) {
            localItems.forEach(local => {
                sessionScannedBarangIds.add(local.barang_id);
                let match = currentOpnameDetails.find(d => d.barang_id == local.barang_id);
                if (match) {
                    match.stok_fisik = local.stok_fisik;
                    match.serial_numbers = local.serial_numbers || [];
                    match.catatan = local.catatan;
                    match.selisih = local.selisih;
                } else {
                    currentOpnameDetails.push(local);
                }
            });
        }
    } catch (e) {
        console.warn("Error restoring local draft:", e);
    }
}

function openInputFisikModal(id) {
    $('#modalInputFisik').modal('show');
    $('#input_fisik_tbody').html(getEmptyRowHtml());
    $('#btnSimpanFisikDraft').prop('disabled', true);

    $('#scan_kode').val('');
    $('#scan_nama').val('');
    $('#scan_sistem').val('');
    $('#scan_fisik').val('');
    $('#scan_catatan').val('');
    $('#scan_selisih').removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
    $('#deleted_items_container').empty();
    currentScannedItem = null;

    sessionScannedBarangIds.clear();
    currentActiveFisikTab = 'session';
    $('#tab_btn_sesi_baru').addClass('btn-primary active').removeClass('btn-outline-secondary');
    $('#tab_btn_sudah_disimpan').removeClass('btn-primary active').addClass('btn-outline-secondary');

    $.ajax({
        url: `/stock-opname/${id}`,
        method: 'GET',
        success: function (res) {
            $('#input_so_id').val(res.id);
            $('#input_so_nomor').text(res.nomor_opname);
            $('#input_so_gudang').text(res.gudang_nama);
            $('#input_so_tanggal').text(res.tanggal_mulai);

            currentOpnameDetails = res.details || [];

            // Restore hanya item yang sedang dikerjakan pada sesi belum tersimpan
            restoreLocalFisikDraft(res.id);

            renderFisikTableByCurrentTab();
            $('#btnSimpanFisikDraft').prop('disabled', false);

            setTimeout(() => { $('#scan_kode').focus(); }, 400);
        },
        error: function () {
            $('#input_fisik_tbody').html('<tr><td colspan="8" class="alert alert-danger text-center py-2">Gagal memuat item opname.</td></tr>');
        }
    });
}

function playScannerBeep() {
    try {
        let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        let oscNode = audioCtx.createOscillator();
        let gainNode = audioCtx.createGain();
        oscNode.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscNode.type = 'sine';
        oscNode.frequency.setValueAtTime(1500, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.25, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.08);
        oscNode.start();
        oscNode.stop(audioCtx.currentTime + 0.08);
    } catch (e) {
        console.warn("AudioContext failed:", e);
    }
}

function playWarningBeep() {
    try {
        let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        let oscNode = audioCtx.createOscillator();
        let gainNode = audioCtx.createGain();
        oscNode.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscNode.type = 'sawtooth';
        oscNode.frequency.setValueAtTime(250, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.25, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
        oscNode.start();
        oscNode.stop(audioCtx.currentTime + 0.25);
    } catch (e) {
        console.warn("AudioContext failed:", e);
    }
}

function selectScannedItem(item, shouldFocus = true) {
    playScannerBeep();

    currentScannedItem = item;
    $('#scan_kode').val(item.barang_kode);
    $('#scan_nama').val(item.barang_nama);
    
    let sisVal = parseFloat(item.stok_sistem || 0);
    let sisFormatted = (sisVal % 1 === 0) ? sisVal.toFixed(0) : sisVal.toFixed(2).replace(/\.?0+$/, '');
    $('#scan_sistem').val(sisFormatted);
    
    let existingFisik = item.stok_fisik !== null ? item.stok_fisik : '';
    $('#scan_fisik').val(existingFisik);

    let existingCatatan = item.catatan !== null ? item.catatan : '';
    $('#scan_catatan').val(existingCatatan);
    
    hitungDiffScan();

    if (shouldFocus) {
        setTimeout(() => {
            $('#scan_fisik').focus().select();
        }, 10);
    }
}

function hitungDiffScan() {
    const sistem = parseFloat($('#scan_sistem').val()) || 0;
    const fisik = parseFloat($('#scan_fisik').val());
    const lbl = $('#scan_selisih');

    if (isNaN(fisik)) {
        lbl.removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
        return;
    }

    const diff = fisik - sistem;
    let diffFormatted = (diff % 1 === 0) ? diff.toFixed(0) : diff.toFixed(2).replace(/\.?0+$/, '');

    if (diff > 0) {
        lbl.removeClass('selisih-minus selisih-zero').addClass('selisih-plus').text('+' + diffFormatted);
    } else if (diff < 0) {
        lbl.removeClass('selisih-plus selisih-zero').addClass('selisih-minus').text(diffFormatted);
    } else {
        lbl.removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('0');
    }
}

function hitungDiffFisik(barangId) {
    const sistem = parseFloat($(`#input_sistem_${barangId}`).val()) || 0;
    const fisik = parseFloat($(`#input_fisik_${barangId}`).val());
    const lbl = $(`#input_diff_label_${barangId}`);

    let match = currentOpnameDetails.find(d => d.barang_id == barangId);
    if (match) {
        match.stok_fisik = isNaN(fisik) ? null : fisik;
        match.selisih = isNaN(fisik) ? null : (fisik - sistem);
    }

    if (isNaN(fisik)) {
        lbl.removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
        return;
    }

    const diff = fisik - sistem;
    let diffFormatted = (diff % 1 === 0) ? diff.toFixed(0) : diff.toFixed(2).replace(/\.?0+$/, '');

    if (diff > 0) {
        lbl.removeClass('selisih-minus selisih-zero').addClass('selisih-plus').text('+' + diffFormatted);
    } else if (diff < 0) {
        lbl.removeClass('selisih-plus selisih-zero').addClass('selisih-minus').text(diffFormatted);
    } else {
        lbl.removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('0');
    }
    saveLocalFisikDraft();
}

function hapusRowFisik(barangId) {
    let match = currentOpnameDetails.find(d => d.barang_id == barangId);
    if (match) {
        $('#deleted_items_container').append(`
            <input type="hidden" name="items[${barangId}][stok_fisik]" value="">
            <input type="hidden" name="items[${barangId}][barang_id]" value="${barangId}">
        `);
        match.stok_fisik = null;
        match.selisih = null;
        match.catatan = null;
    }

    $(`#table_row_barang_${barangId}`).remove();

    if ($('#input_fisik_tbody tr:not(#empty_row_placeholder)').length === 0) {
        $('#input_fisik_tbody').html(getEmptyRowHtml());
    } else {
        updateRowIndexes();
    }
    saveLocalFisikDraft();
}

function masukkanKeDaftar() {
    if (!currentScannedItem) {
        Swal.fire({ icon: 'warning', title: 'Barang Kosong', text: 'Scan atau ketik kode barang yang valid terlebih dahulu.', timer: 1500, showConfirmButton: false });
        return;
    }

    let fisikText = $('#scan_fisik').val().trim();
    if (fisikText === '') {
        Swal.fire({ icon: 'warning', title: 'Jumlah Fisik Kosong', text: 'Ketik kuantiti fisik barang riil.', timer: 1500, showConfirmButton: false });
        return;
    }

    let fisikVal = parseFloat( fisikText );
    let catatanVal = $('#scan_catatan').val().trim();
    
    $('#empty_row_placeholder').remove();

    currentScannedItem.stok_fisik = fisikVal;
    let sistemVal = parseFloat(currentScannedItem.stok_sistem || 0);
    let diffVal = fisikVal - sistemVal;
    currentScannedItem.selisih = diffVal;
    currentScannedItem.catatan = catatanVal;

    $(`#deleted_items_container input[name="items[${currentScannedItem.barang_id}][barang_id]"]`).parent().remove();

    sessionScannedBarangIds.add(currentScannedItem.barang_id);
    if (currentActiveFisikTab !== 'session') {
        switchFisikTab('session');
    }

    let targetRow = $(`#table_row_barang_${currentScannedItem.barang_id}`);
    if (targetRow.length) {
        let targetInput = $(`#input_fisik_${currentScannedItem.barang_id}`);
        targetInput.val(fisikVal);
        $(`#input_catatan_${currentScannedItem.barang_id}`).val(catatanVal);
        hitungDiffFisik(currentScannedItem.barang_id);
    } else {
        let nextIndex = $('#input_fisik_tbody tr:not(#empty_row_placeholder)').length + 1;
        let newRowHtml = createRowHtml(currentScannedItem, nextIndex);
        $('#input_fisik_tbody').append(newRowHtml);
        targetRow = $(`#table_row_barang_${currentScannedItem.barang_id}`);
    }

    updateRowIndexes();
    updateFisikTabCounters();
    saveLocalFisikDraft();

    if (targetRow.length) {
        targetRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        targetRow.css('transition', 'background-color 0.2s');
        targetRow.css('background-color', '#fff3cd');
        setTimeout(() => {
            targetRow.css('background-color', '');
        }, 1500);
    }

    $('#scan_kode').val('');
    $('#scan_nama').val('');
    $('#scan_sistem').val('');
    $('#scan_fisik').val('');
    $('#scan_catatan').val('');
    $('#scan_selisih').removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
    currentScannedItem = null;

    $('#scan_kode').focus();
}

function parseQrOrBarcodeString(str) {
    str = (str || '').toString().trim();
    let sn = null;
    let kode = null;

    if (str.includes('sn=')) {
        let m = str.match(/[?&]sn=([^&]+)/i);
        if (m) sn = decodeURIComponent(m[1].trim());
    }

    if (!sn) {
        let m = str.match(/SN\s*:\s*(.+?)(?=(?:(?:KODE|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
        if (m) sn = m[1].trim();
    }

    let mKode = str.match(/KODE\s*:\s*(.+?)(?=(?:(?:SN|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
    if (mKode) kode = mKode[1].trim();

    if (!sn) {
        let m = str.match(/^SN\s*[:\-\s]\s*(.+)$/i);
        if (m) sn = m[1].trim();
    }

    return { sn: sn, kode: kode, raw: str };
}

function processBarcodeInput(query) {
    query = (query || '').toString().trim();
    if (query === '') return;

    let parsed = parseQrOrBarcodeString(query);
    let isSerial = parsed.sn !== null;

    // Bersihkan tampilan di kolom input
    if (parsed.sn && parsed.kode) {
        $('#scan_kode').val(`${parsed.kode} (${parsed.sn})`);
    } else if (parsed.sn) {
        $('#scan_kode').val(parsed.sn);
    } else if (parsed.kode) {
        $('#scan_kode').val(parsed.kode);
    } else {
        $('#scan_kode').val(query);
    }

    $('#scan_autocomplete_container').hide().empty();

    // Jika yang di-scan adalah format Serial Number (misal: SN: PAS-TAS-... atau ada nomor seri)
    if (isSerial) {
        let opnameId = $('#input_so_id').val();
        if (!opnameId) return;

        $('#scan_nama').val('Memverifikasi Serial Number...');

        $.ajax({
            url: `/stock-opname/${opnameId}/find-barang`,
            method: 'GET',
            data: { code: query },
            success: function(res) {
                if (res.matched_type === 'serial_number' && res.matched_serial) {
                    handleScannedSerialNumber(res, res.matched_serial);
                    return;
                }
                currentOpnameDetails.push(res);
                selectScannedItem(res, true);
            },
            error: function(xhr) {
                playWarningBeep();
                let msg = 'Kode, Barcode, atau QR Serial tidak terdaftar di sistem!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ditemukan',
                    html: `<div class="text-danger fw-bold mb-1">${escapeHtmlAdj(msg)}</div><small class="text-muted font-monospace">${escapeHtmlAdj(parsed.sn || query)}</small>`,
                    confirmButtonText: 'OK'
                });
                $('#scan_kode').val('').focus();
                $('#scan_nama').val('');
                $('#scan_sistem').val('');
                $('#scan_fisik').val('');
                $('#scan_catatan').val('');
                $('#scan_selisih').removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
            }
        });
        return;
    }

    // Jika scan barcode / kode barang biasa:
    let qLower = (parsed.kode || query).toLowerCase();
    let exactMatch = currentOpnameDetails.find(d => 
        (d.barang_kode && d.barang_kode.toLowerCase() === qLower) || 
        (d.barang_barcode && d.barang_barcode.toLowerCase() === qLower)
    );

    if (exactMatch) {
        selectScannedItem(exactMatch, false);
        setTimeout(() => {
            $('#scan_fisik').focus().select();
        }, 100);
    } else {
        let opnameId = $('#input_so_id').val();
        if (!opnameId) return;

        $('#scan_nama').val('Mencari di Master Barang...');

        $.ajax({
            url: `/stock-opname/${opnameId}/find-barang`,
            method: 'GET',
            data: { code: query },
            success: function(res) {
                if (res.matched_type === 'serial_number' && res.matched_serial) {
                    handleScannedSerialNumber(res, res.matched_serial);
                    return;
                }

                currentOpnameDetails.push(res);
                selectScannedItem(res, true);
                setTimeout(() => {
                    $('#scan_fisik').focus().select();
                }, 100);
            },
            error: function(xhr) {
                playWarningBeep();
                
                Swal.fire({
                    icon: 'error',
                    title: 'Barang / QR Serial Tidak Terdaftar!',
                    html: `Kode, Barcode, atau QR Serial <strong class="text-danger font-monospace">${escapeHtmlAdj((parsed.kode || query).toUpperCase())}</strong> tidak terdaftar di sistem!`,
                    confirmButtonText: 'OK'
                });

                $('#scan_kode').val('').focus();
                $('#scan_nama').val('');
                $('#scan_sistem').val('');
                $('#scan_fisik').val('');
                $('#scan_catatan').val('');
                $('#scan_selisih').removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
            }
        });
    }
}

function handleScannedSerialNumber(res, sn) {
    let targetRow = $(`#table_row_barang_${res.barang_id}`);
    let itemInDetails = currentOpnameDetails.find(d => d.barang_id == res.barang_id);

    if (!itemInDetails) {
        currentOpnameDetails.push(res);
        itemInDetails = res;
    }

    // Ambil daftar nomor seri yang saat ini sudah diverifikasi di baris
    let currentSns = [];
    let rawVal = $(`#serials_${res.barang_id}`).val();
    if (rawVal) {
        try { currentSns = JSON.parse(rawVal); } catch(e) { currentSns = []; }
    } else if (itemInDetails.serial_numbers && Array.isArray(itemInDetails.serial_numbers)) {
        currentSns = itemInDetails.serial_numbers;
    }

    if (currentSns.includes(sn)) {
        playWarningBeep();
        Swal.fire({
            icon: 'info',
            title: 'Sudah Terverifikasi',
            html: `Nomor seri <strong class="text-primary font-monospace">${escapeHtmlAdj(sn)}</strong> sudah pernah dicentang fisik untuk barang <strong>${escapeHtmlAdj(res.barang_nama)}</strong>!`,
            timer: 1600,
            showConfirmButton: false
        });
        $('#scan_kode').val('').focus();
        return;
    }

    // Tambah serial baru yang diverifikasi
    currentSns.push(sn);
    let newCount = currentSns.length;
    itemInDetails.serial_numbers = currentSns;
    itemInDetails.stok_fisik = newCount;
    let sistemVal = parseFloat(itemInDetails.stok_sistem || 0);
    itemInDetails.selisih = newCount - sistemVal;

    sessionScannedBarangIds.add(res.barang_id);
    if (currentActiveFisikTab !== 'session') {
        switchFisikTab('session');
    }

    // Jika baris belum ada di tabel, tambahkan baris baru
    if (!targetRow.length) {
        $('#empty_row_placeholder').remove();
        let nextIndex = $('#input_fisik_tbody tr:not(#empty_row_placeholder)').length + 1;
        let newRowHtml = createRowHtml(itemInDetails, nextIndex);
        $('#input_fisik_tbody').append(newRowHtml);
        targetRow = $(`#table_row_barang_${res.barang_id}`);
        updateRowIndexes();
    } else {
        $(`#input_fisik_${res.barang_id}`).val(newCount);
        $(`#serials_${res.barang_id}`).val(JSON.stringify(currentSns));
        hitungDiffFisik(res.barang_id);
    }

    if (typeof updateOpnameSnRowBadge === 'function') {
        updateOpnameSnRowBadge(res.barang_id, newCount, currentSns);
    }

    updateFisikTabCounters();
    saveLocalFisikDraft();

    // Feedback visual dan audio
    playScannerBeep();
    if (targetRow.length) {
        targetRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        targetRow.css('transition', 'background-color 0.2s').css('background-color', '#dcfce7');
        setTimeout(() => { targetRow.css('background-color', ''); }, 1500);
    }

    Swal.fire({
        icon: 'success',
        title: 'Serial Terverifikasi!',
        html: `Nomor seri <strong class="text-success font-monospace">${escapeHtmlAdj(sn)}</strong> berhasil diverifikasi untuk <strong>${escapeHtmlAdj(res.barang_nama)}</strong>!<br><span class="badge bg-success mt-1">Total Fisik: ${newCount} Unit</span>`,
        timer: 1600,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });

    $('#scan_kode').val('').focus();
}

let availableCameraIds = [];
let currentCameraIndex = 0;
let isCameraScannerActive = false;
let cameraSessionToken = 0;
let cameraStartTimer = null;

function switchCamera() {
    if (!availableCameraIds || availableCameraIds.length <= 1) {
        Swal.fire({ icon: 'info', title: 'Info Kamera', text: 'Hanya 1 perangkat kamera yang terdeteksi pada perangkat ini.', timer: 1500, showConfirmButton: false });
        return;
    }
    stopCameraScanner();
    currentCameraIndex = (currentCameraIndex + 1) % availableCameraIds.length;
    startCameraScanner(availableCameraIds[currentCameraIndex]);
}

function stopCameraScanner() {
    isCameraScannerActive = false;
    cameraSessionToken++; // Invalidate any pending async camera initializations

    if (cameraStartTimer) {
        clearTimeout(cameraStartTimer);
        cameraStartTimer = null;
    }

    // 1. Force stop all video MediaStreamTracks across the entire page immediately
    try {
        $('video').each(function() {
            if (this.srcObject) {
                let stream = this.srcObject;
                if (stream && stream.getTracks) {
                    stream.getTracks().forEach(track => {
                        try {
                            track.stop();
                            track.enabled = false;
                        } catch(e) {}
                    });
                }
                this.srcObject = null;
            }
            try { this.pause(); } catch(e) {}
            this.removeAttribute('src');
            this.load();
        });
    } catch(e) {}

    // 2. Stop and clear Html5Qrcode instance
    if (html5QrcodeScanner) {
        let scanner = html5QrcodeScanner;
        html5QrcodeScanner = null;

        try {
            let isScanning = false;
            try {
                if (typeof scanner.getState === 'function') {
                    let state = scanner.getState();
                    isScanning = (state === 2 || state === 3); // 2: SCANNING, 3: PAUSED
                }
            } catch(e) {}

            if (isScanning || scanner.isScanning) {
                scanner.stop().then(() => {
                    try { scanner.clear(); } catch(e) {}
                }).catch(err => {
                    try { scanner.clear(); } catch(e) {}
                }).finally(() => {
                    try {
                        $('video').each(function() {
                            if (this.srcObject && this.srcObject.getTracks) {
                                this.srcObject.getTracks().forEach(t => { try { t.stop(); t.enabled = false; } catch(e) {} });
                                this.srcObject = null;
                            }
                        });
                    } catch(e) {}
                });
            } else {
                try { scanner.clear(); } catch(e) {}
            }
        } catch (e) {
            try { scanner.clear(); } catch(e) {}
        }
    }

    // 3. Clear container DOM and hide overlay
    $('#scanner_reader').empty();
    $('.scanner-overlay').hide();
}

function startCameraScanner(specificCameraId = null) {
    stopCameraScanner();

    isCameraScannerActive = true;
    const currentToken = ++cameraSessionToken;

    $('#scanner_reader').html('<div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;"><div class="spinner-border text-success mb-2" role="status"></div><span class="small text-success">Menginisialisasi Kamera...</span></div>');
    $('.scanner-overlay').hide();

    let allFormats = [];
    if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
        allFormats = [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.CODE_93,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION,
            Html5QrcodeSupportedFormats.ITF,
            Html5QrcodeSupportedFormats.CODABAR,
            Html5QrcodeSupportedFormats.DATA_MATRIX,
            Html5QrcodeSupportedFormats.AZTEC,
            Html5QrcodeSupportedFormats.PDF_417
        ];
    }

    try {
        html5QrcodeScanner = new Html5Qrcode("scanner_reader", {
            formatsToSupport: allFormats.length ? allFormats : undefined,
            verbose: false
        });
    } catch(e) {
        console.error("Gagal instansiasi Html5Qrcode:", e);
        return;
    }
    
    let isProcessingScan = false;
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        if (isProcessingScan) return;
        isProcessingScan = true;

        playScannerBeep();

        // Flash green box on successful scan
        $('.scanner-target-box').css('box-shadow', '0 0 35px 12px #22c55e');

        setTimeout(() => {
            stopCameraScanner();
            $('#modalScanner').modal('hide');
            processBarcodeInput(decodedText);
        }, 120);
    };

    const proceedStart = (cameraConfig) => {
        if (!isCameraScannerActive || currentToken !== cameraSessionToken || !$('#modalScanner').hasClass('show')) {
            stopCameraScanner();
            return;
        }

        const scanConfig = { 
            fps: 15,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                const w = Math.min(Math.floor(viewfinderWidth * 0.82), 300);
                const h = Math.min(Math.floor(viewfinderHeight * 0.52), 160);
                return { width: w, height: h };
            }
        };

        html5QrcodeScanner.start(
            cameraConfig, 
            scanConfig, 
            qrCodeSuccessCallback,
            (errorMessage) => {}
        ).then(() => {
            if (!isCameraScannerActive || currentToken !== cameraSessionToken || !$('#modalScanner').hasClass('show')) {
                stopCameraScanner();
                return;
            }
            $('.scanner-overlay').fadeIn(150);
        }).catch(err => {
            if (!isCameraScannerActive || currentToken !== cameraSessionToken || !$('#modalScanner').hasClass('show')) {
                stopCameraScanner();
                return;
            }
            console.error("Camera access failed:", err);
            let cleanError = err ? err.toString() : 'Unknown Error';
            
            $('#scanner_reader').html(`
                <div class="d-flex align-items-center justify-content-center text-white flex-column p-4 text-center" style="min-height: 220px; background: #27272a;">
                    <i class="bi bi-exclamation-triangle-fill text-warning mb-2" style="font-size: 2rem;"></i>
                    <span class="fw-semibold">Gagal Membuka Kamera</span>
                    <small class="text-muted mt-2">${cleanError}</small>
                    <small class="text-danger mt-1" style="font-size: 0.75rem;">Pastikan izin kamera aktif & menggunakan koneksi aman HTTPS / Localhost.</small>
                </div>
            `);
        });
    };

    if (specificCameraId) {
        proceedStart(specificCameraId);
    } else {
        Html5Qrcode.getCameras().then(devices => {
            if (!isCameraScannerActive || currentToken !== cameraSessionToken || !$('#modalScanner').hasClass('show')) {
                stopCameraScanner();
                return;
            }

            if (devices && devices.length) {
                availableCameraIds = devices.map(d => d.id);
                // Prefer rear / back camera (environment) on mobile
                let backCamera = devices.find(d => {
                    let label = (d.label || '').toLowerCase();
                    return label.includes('back') || label.includes('rear') || label.includes('belakang') || label.includes('environment');
                });
                if (backCamera) {
                    currentCameraIndex = availableCameraIds.indexOf(backCamera.id);
                    proceedStart(backCamera.id);
                } else {
                    currentCameraIndex = 0;
                    proceedStart(devices[0].id);
                }
            } else {
                proceedStart({ facingMode: "environment" });
            }
        }).catch(() => {
            if (!isCameraScannerActive || currentToken !== cameraSessionToken || !$('#modalScanner').hasClass('show')) {
                stopCameraScanner();
                return;
            }
            proceedStart({ facingMode: "environment" });
        });
    }
}

$(document).ready(function () {
    $(document).on('input keyup', '#scan_kode', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'Enter') return;

        let rawVal = $(this).val();
        let parsed = parseQrOrBarcodeString(rawVal);

        // Jika terdeteksi input/scan QR komposit (memiliki SN: / KODE: atau URL param)
        if (parsed.sn && (parsed.kode || rawVal.toUpperCase().includes('SN:'))) {
            $('#scan_autocomplete_container').hide().empty();
            if (e.type === 'keyup') {
                processBarcodeInput(rawVal);
            }
            return;
        }

        let query = rawVal.trim().toLowerCase();
        if (query === '') {
            $('#scan_nama').val('');
            $('#scan_sistem').val('');
            $('#scan_fisik').val('');
            $('#scan_catatan').val('');
            $('#scan_selisih').removeClass('selisih-plus selisih-minus').addClass('selisih-zero').text('-');
            $('#scan_autocomplete_container').hide().empty();
            currentScannedItem = null;
            return;
        }

        let exactMatch = currentOpnameDetails.find(d => 
            (d.barang_kode && d.barang_kode.toLowerCase() === query) || 
            (d.barang_barcode && d.barang_barcode.toLowerCase() === query)
        );

        if (exactMatch) {
            selectScannedItem(exactMatch, false);
            $('#scan_autocomplete_container').hide().empty();
            return;
        }

        let queryParts = query.split(/\s+/).filter(p => p.length > 0);
        let partialMatches = currentOpnameDetails.filter(d => {
            let combined = ((d.barang_kode || '') + ' ' + (d.barang_nama || '') + ' ' + (d.barang_barcode || '')).toLowerCase();
            return queryParts.every(part => combined.includes(part));
        });

        if (partialMatches.length > 0) {
            let listHtml = '';
            partialMatches.slice(0, 15).forEach((item, idx) => {
                let sisVal = parseFloat(item.stok_sistem || 0);
                let sisFormatted = (sisVal % 1 === 0) ? sisVal.toFixed(0) : sisVal.toFixed(2).replace(/\.?0+$/, '');
                listHtml += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2 px-3 small autocomplete-item d-flex justify-content-between align-items-center ${idx === 0 ? 'active' : ''}" data-barang-id="${item.barang_id}" data-barang-kode="${item.barang_kode}">
                    <div>
                        <strong class="text-primary">${item.barang_kode}</strong> - <span class="fw-semibold text-dark">${item.barang_nama}</span>
                    </div>
                    <span class="badge bg-secondary rounded-pill small ms-2 text-nowrap">Sistem: ${sisFormatted} ${item.satuan || ''}</span>
                </a>`;
            });
            $('#scan_autocomplete_container').html(listHtml).show();
        } else {
            let notFoundHtml = `
            <div class="list-group-item py-2 px-3 small text-muted text-center bg-light">
                <i class="bi bi-search me-1"></i> Tidak ditemukan barang dengan kata kunci "<strong>${query}</strong>"
            </div>`;
            $('#scan_autocomplete_container').html(notFoundHtml).show();
            currentScannedItem = null;
        }
    });

    $(document).on('click', '.autocomplete-item', function(e) {
        e.preventDefault();
        let barangId = $(this).data('barang-id');
        let code = $(this).data('barang-kode');
        let match = currentOpnameDetails.find(d => d.barang_id == barangId || d.barang_kode === code);
        if (match) {
            selectScannedItem(match, true);
        }
        $('#scan_autocomplete_container').hide().empty();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#scan_kode, #scan_autocomplete_container').length) {
            $('#scan_autocomplete_container').hide();
        }
    });

    $(document).on('keydown', '#scan_kode', function(e) {
        let container = $('#scan_autocomplete_container');
        if (!container.is(':visible')) return;

        let items = container.find('.autocomplete-item');
        if (items.length === 0) return;

        let activeItem = container.find('.autocomplete-item.active');
        let activeIdx = items.index(activeItem);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            items.removeClass('active');
            let nextIdx = (activeIdx + 1) % items.length;
            $(items[nextIdx]).addClass('active');
            items[nextIdx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            items.removeClass('active');
            let prevIdx = (activeIdx - 1 + items.length) % items.length;
            $(items[prevIdx]).addClass('active');
            items[prevIdx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeItem.length > 0) {
                activeItem.click();
            } else {
                $(items[0]).click();
            }
        }
    });

    $(document).on('input', '#scan_fisik', function() {
        hitungDiffScan();
    });

    $(document).on('click', '#btn_scan_add', function(e) {
        e.preventDefault();
        masukkanKeDaftar();
    });

    $(document).on('keypress', '#scan_kode', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            processBarcodeInput($(this).val());
        }
    });

    $(document).on('keypress', '#scan_fisik', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            masukkanKeDaftar();
        }
    });

    $(document).on('click', '#btn_scan_camera', function(e) {
        e.preventDefault();
        if (cameraStartTimer) clearTimeout(cameraStartTimer);
        $('#modalScanner').modal('show');
        cameraStartTimer = setTimeout(() => {
            if ($('#modalScanner').hasClass('show')) {
                startCameraScanner();
            }
        }, 300);
    });

    $('#modalScanner').on('hide.bs.modal hidden.bs.modal', function () {
        stopCameraScanner();
    });

    $('#modalInputFisik').on('hide.bs.modal hidden.bs.modal', function () {
        stopCameraScanner();
    });

    $('#btnSimpanFisikDraft').on('click', function (e) {
        e.preventDefault();
        $('#fisik_alert').hide();

        const id = $('#input_so_id').val();

        if ($('#input_fisik_tbody tr:not(#empty_row_placeholder)').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada Input',
                text: 'Belum ada barang fisik yang di-scan atau dihitung di sesi ini.',
                timer: 1600,
                showConfirmButton: false
            });
            return;
        }

        const data = $('#formInputFisik').serialize();

        $('#btnSimpanFisikDraft').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…');

        $.ajax({
            url: `/stock-opname/${id}/save-physical`,
            method: 'POST',
            data: data,
            success: function (res) {
                $('#btnSimpanFisikDraft').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Draf Progress');

                // Sinkronkan stok_fisik di memori currentOpnameDetails
                sessionScannedBarangIds.forEach(bId => {
                    let match = currentOpnameDetails.find(d => d.barang_id == bId);
                    if (match) {
                        let rowVal = $(`#input_fisik_${bId}`).val();
                        match.stok_fisik = (rowVal !== '' && !isNaN(rowVal)) ? parseFloat(rowVal) : null;
                    }
                });

                // Bersihkan sesi scan saat ini agar tidak tampil lagi
                sessionScannedBarangIds.clear();
                clearLocalFisikDraft(id);
                $('#deleted_items_container').empty();

                // Kembalikan ke tab sesi scan yang bersih
                currentActiveFisikTab = 'session';
                switchFisikTab('session');

                reloadTableOpname();

                Swal.fire({ 
                    icon: 'success', 
                    title: 'Progress Disimpan!', 
                    text: res.message || 'Data hitungan fisik berhasil disimpan. Siap scan barang berikutnya.', 
                    timer: 1800, 
                    showConfirmButton: false 
                });
            },
            error: function (xhr) {
                $('#btnSimpanFisikDraft').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Draf Progress');
                $('#fisik_alert').html(`<div class="alert alert-danger py-2 mb-0 small">${xhr.responseJSON?.message || 'Terjadi kesalahan.'}</div>`).show();
            }
        });
    });

    window.addEventListener('beforeunload', function (e) {
        stopCameraScanner();
        const opnameId = $('#input_so_id').val();
        if (opnameId && $('#modalInputFisik').hasClass('show')) {
            const key = `so_draft_fisik_${opnameId}`;
            if (localStorage.getItem(key)) {
                e.preventDefault();
                e.returnValue = 'Data input stok fisik belum disimpan sebagai Draf Progress!';
            }
        }
    });

    window.addEventListener('pagehide', stopCameraScanner);

    document.getElementById('modalInputFisik')?.addEventListener('hidden.bs.modal', function () {
        stopCameraScanner();
        document.getElementById('formInputFisik').reset();
        $('#input_fisik_tbody').empty();
        $('#deleted_items_container').empty();
        $('#fisik_alert').hide();
    });
});
</script>
@endpush
