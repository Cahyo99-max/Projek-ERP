<!-- ─── MODAL: Tambah & Edit Saldo Awal ─────────────────────────── -->
@can('tambah-opening-stock')
<style>
/* ─── Modern Cinematic Theme Tokens for Saldo Awal (Opening Stock) ─── */
:root {
    --opn-modal-bg: #ffffff;
    --opn-card-bg: #f8fafc;
    --opn-card-border: #e2e8f0;
    --opn-text-main: #0f172a;
    --opn-text-muted: #64748b;
    --opn-input-bg: #ffffff;
    --opn-addon-bg: #f1f5f9;
    --opn-footer-bg: #f8fafc;
    --opn-border: #cbd5e1;
    --opn-primary: #3b82f6;
    --opn-success: #10b981;
}

[data-bs-theme="dark"],
html[data-theme="dark"],
.dark-mode {
    --opn-modal-bg: #0f172a;
    --opn-card-bg: #1e293b;
    --opn-card-border: #334155;
    --opn-text-main: #f8fafc;
    --opn-text-muted: #94a3b8;
    --opn-input-bg: #1e293b;
    --opn-addon-bg: #334155;
    --opn-footer-bg: #0f172a;
    --opn-border: #334155;
    --opn-primary: #60a5fa;
    --opn-success: #34d399;
}

/* Modals Content */
#modalTambahOpening .modal-content,
#modalImportOpening .modal-content,
#modalLookupGudangOpn .modal-content,
#modalPilihBarang .modal-content,
#modalDetailOpening .modal-content {
    background: var(--opn-modal-bg) !important;
    color: var(--opn-text-main) !important;
    border: 1px solid var(--opn-card-border) !important;
    border-radius: 1.25rem !important;
    overflow: hidden !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
}

/* Header Gradient */
.opn-modal-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%) !important;
    padding: 1.15rem 1.5rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}
.opn-modal-header-success {
    background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%) !important;
    padding: 1.15rem 1.5rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}

.opn-header-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Form Controls & Labels */
.opn-form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--opn-text-muted) !important;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

#modalTambahOpening .form-control,
#modalTambahOpening .form-select,
#modalImportOpening .form-control,
#modalImportOpening .form-select,
#modalLookupGudangOpn .form-control,
#modalPilihBarang .form-control {
    background-color: var(--opn-input-bg) !important;
    color: var(--opn-text-main) !important;
    border: 1px solid var(--opn-border) !important;
    border-radius: 0.65rem;
}
#modalTambahOpening .form-control:focus,
#modalImportOpening .form-control:focus {
    border-color: var(--opn-primary) !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
}

#modalTambahOpening .input-group-text,
#modalImportOpening .input-group-text {
    background-color: var(--opn-addon-bg) !important;
    color: var(--opn-text-main) !important;
    border: 1px solid var(--opn-border) !important;
}

.opn-item-card {
    background: var(--opn-card-bg) !important;
    border: 1px solid var(--opn-card-border) !important;
    border-radius: 0.9rem !important;
    padding: 1rem 1.15rem !important;
    margin-bottom: 0.75rem !important;
    transition: all 0.2s ease;
}
.opn-item-card:hover {
    border-color: rgba(59, 130, 246, 0.5) !important;
}

.opn-summary-box {
    background: var(--opn-card-bg) !important;
    border: 1px solid var(--opn-card-border) !important;
    border-radius: 0.85rem !important;
    padding: 1rem 1.25rem !important;
}

.opn-modal-footer {
    background: var(--opn-footer-bg) !important;
    border-top: 1px solid var(--opn-card-border) !important;
    padding: 1rem 1.5rem !important;
}
</style>

<div class="modal fade" id="modalTambahOpening" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="opn-modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="opn-header-icon-box">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTambahOpeningTitle">Buat Saldo Awal Persediaan</h5>
                        <small class="text-white-50" id="modalTambahOpeningSubtitle">Isi rincian barang, kuantitas fisik, dan HPP awal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formTambahOpening">
                    <input type="hidden" id="edit_opening_id" value="">

                    <div class="row g-3">
                        {{-- Gudang Lookup Input --}}
                        <div class="col-md-6">
                            <label class="opn-form-label d-block">Gudang Penyimpanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="opn_gudang_display" readonly placeholder="Klik Cari Gudang..." required onclick="openLookupGudangModalOpn('opn')" style="cursor: pointer;">
                                <button class="btn btn-primary" type="button" onclick="openLookupGudangModalOpn('opn')" title="Cari Gudang">
                                    <i class="bi bi-search"></i>
                                </button>
                                <input type="hidden" name="gudang_id" id="opn_gudang_id" required>
                            </div>
                            <div class="invalid-feedback" id="err-gudang_id"></div>
                        </div>

                        {{-- Tanggal --}}
                        <div class="col-md-6">
                            <label class="opn-form-label d-block">Tanggal Dokumen <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_opening_stock" id="opn_tanggal" class="form-control" 
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="err-tanggal_opening_stock"></div>
                        </div>

                        {{-- Nomor Dokumen --}}
                        <div class="col-md-4" id="opn_nomor_wrapper">
                            <label class="opn-form-label d-block">Nomor Dokumen</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="opn_auto_number" name="auto_number" checked>
                                        <label class="form-check-label small text-muted" for="opn_auto_number">Auto</label>
                                    </div>
                                </div>
                                <input type="text" name="nomor_opening_stock" id="opn_nomor_opening_stock" class="form-control font-monospace"
                                       placeholder="OPN/YYYY/MM/XXXX" disabled>
                            </div>
                            <div class="invalid-feedback d-block" id="err-nomor_opening_stock"></div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-md-8" id="opn_keterangan_wrapper">
                            <label class="opn-form-label d-block">Keterangan / Alasan <small class="text-muted">(Maks 40 Karakter)</small></label>
                            <textarea name="keterangan" id="opn_keterangan" class="form-control" rows="1"
                                      placeholder="Catatan saldo awal persediaan..." maxlength="40"></textarea>
                        </div>
                    </div>

                    {{-- Detail Items --}}
                    <hr class="my-4" style="border-color: var(--opn-card-border);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--opn-text-main);"><i class="bi bi-list-check me-2 text-primary"></i>Daftar Item & HPP Awal</h6>
                            <small class="text-muted">Tambahkan barang yang memiliki saldo awal fisik di gudang ini</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 fw-semibold shadow-sm" onclick="tambahItemOpn()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                        </button>
                    </div>
                    <div id="opn_items_container" style="max-height: 440px; overflow-y: auto; overflow-x: hidden; padding-right: 4px;">
                        {{-- Items rendered dynamically --}}
                    </div>
                    <div id="err-items" class="text-danger small mt-1" style="display:none;"></div>

                    <div class="d-flex justify-content-end align-items-center mt-3 opn-summary-box">
                        <span class="fw-semibold text-muted me-2">Total Estimasi Nilai:</span>
                        <span class="fs-5 fw-bold text-success font-monospace" id="opn_grand_total">Rp 0</span>
                    </div>
                </form>
            </div>
            <div class="opn-modal-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" id="btnSimpanDraftOpening">
                        <i class="bi bi-pencil-square me-1"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnAjukanOpening">
                        <i class="bi bi-send-fill me-1"></i> Ajukan Saldo Awal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- ─── MODAL: Import Excel Saldo Awal ─────────────────────────── -->
@can('tambah-opening-stock')
<div class="modal fade" id="modalImportOpening" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="opn-modal-header-success d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="opn-header-icon-box">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Import Saldo Awal dari Excel</h5>
                        <small class="text-white-50">Unggah data stok awal secara massal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formImportOpening" enctype="multipart/form-data">
                    <div class="row g-3">
                        {{-- Gudang Lookup Input --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">Gudang Penyimpanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="imp_gudang_display" readonly placeholder="Klik Cari Gudang..." required onclick="openLookupGudangModalOpn('imp')" style="cursor: pointer;">
                                <button class="btn btn-primary" type="button" onclick="openLookupGudangModalOpn('imp')" title="Cari Gudang">
                                    <i class="bi bi-search"></i>
                                </button>
                                <input type="hidden" name="gudang_id" id="imp_gudang_id" required>
                            </div>
                        </div>

                        {{-- Tanggal --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">Tanggal Dokumen <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_opening_stock" id="imp_tanggal" class="form-control" 
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Nomor Dokumen --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">Nomor Dokumen</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="imp_auto_number" name="auto_number" checked>
                                        <label class="form-check-label small text-muted" for="imp_auto_number">Auto</label>
                                    </div>
                                </div>
                                <input type="text" name="nomor_opening_stock" id="imp_nomor_opening_stock" class="form-control font-monospace"
                                       placeholder="OPN/YYYY/MM/XXXX" disabled>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">Keterangan <small class="text-muted">(Maks 40 Karakter)</small></label>
                            <textarea name="keterangan" id="imp_keterangan" class="form-control" rows="1"
                                      placeholder="Catatan import..." maxlength="40"></textarea>
                        </div>

                        {{-- Pilihan Tindakan --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">Status Setelah Import</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="imp_submit_action" id="imp_action_draft" value="draft" checked>
                                    <label class="form-check-label small fw-semibold" for="imp_action_draft" style="color: var(--opn-text-main);">
                                        Simpan Draft
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="imp_submit_action" id="imp_action_pending" value="pending">
                                    <label class="form-check-label small fw-semibold" for="imp_action_pending" style="color: var(--opn-text-main);">
                                        Langsung Ajukan (Pending)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- File Input --}}
                        <div class="col-12">
                            <label class="opn-form-label d-block">File Excel (.xlsx) <span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" id="imp_excel_file" class="form-control" accept=".xlsx" required>
                        </div>

                        {{-- Download Template Banner Box --}}
                        <div class="col-12">
                            <div class="p-3 opn-summary-box rounded-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border" style="border-color: var(--opn-card-border) !important;">
                                <div>
                                    <div class="fw-bold small" style="color: var(--opn-text-main);"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-1"></i> Template Excel Otomatis</div>
                                    <small class="text-muted">Template sudah terisi seluruh daftar Master Barang & satuannya.</small>
                                </div>
                                <a href="{{ route('opening-stock.template') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold no-loader flex-shrink-0" download>
                                    <i class="bi bi-download me-1"></i> Unduh Template
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="opn-modal-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnProsesImport">
                    <i class="bi bi-upload me-1"></i> Jalankan Import
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- ─── MODAL: Lookup Master Gudang ─────────────────────────────────── -->
<div class="modal fade" id="modalLookupGudangOpn" tabindex="-1" style="z-index: 1055;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="opn-modal-header d-flex align-items-center justify-content-between py-2.5 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-house-door fs-5 text-white"></i>
                    <h6 class="modal-title fw-bold text-white mb-0">Pilih Gudang Penyimpanan</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" id="lookupGudangSearchOpn" placeholder="Cari nama atau kode gudang...">
                </div>
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-hover table-modern w-100 align-middle mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th width="30%">Kode</th>
                                <th>Nama Gudang</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lookupGudangTbodyOpn">
                            @forelse($gudangs as $g)
                            <tr class="lookup-gudang-row-opn" data-kode="{{ strtolower($g->kode_gudang ?? '') }}" data-nama="{{ strtolower($g->nama_gudang) }}">
                                <td class="fw-bold text-primary font-monospace small">{{ $g->kode_gudang ?: '-' }}</td>
                                <td class="fw-semibold" style="color: var(--opn-text-main);">{{ $g->nama_gudang }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary px-3 rounded-pill fw-semibold" 
                                            onclick="selectGudangFromModalOpn({{ $g->id }}, '{{ addslashes($g->kode_gudang) }}', '{{ addslashes($g->nama_gudang) }}')">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted small">Belum ada data gudang aktif</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── MODAL: Pilih Barang (Search & Add Popup) ───────────────────── -->
<div class="modal fade" id="modalPilihBarang" tabindex="-1" style="z-index: 1056;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="opn-modal-header d-flex align-items-center justify-content-between py-2.5 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam fs-5 text-white"></i>
                    <h6 class="modal-title fw-bold text-white mb-0">Cari & Pilih Master Barang</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover table-modern w-100 align-middle mb-0" id="tablePilihBarang">
                        <thead>
                            <tr>
                                <th width="25%">Kode Barang</th>
                                <th width="45%">Nama Barang</th>
                                <th width="15%" class="text-center">Satuan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Rendered dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemCounter = 0;
let currentGudangTarget = 'opn';
let lookupGudangModalInstance = null;
let pilihBarangModalInstance = null;
let activeRowId = null;
let dtPilihBarang = null;

function openLookupGudangModalOpn(target) {
    currentGudangTarget = target;
    $('#lookupGudangSearchOpn').val('');
    $('#lookupGudangTbodyOpn tr.lookup-gudang-row-opn').show();

    if (!lookupGudangModalInstance) {
        lookupGudangModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLookupGudangOpn'));
    }
    lookupGudangModalInstance.show();
}

function selectGudangFromModalOpn(id, kode, nama) {
    const displayText = kode ? `${kode} - ${nama}` : nama;
    if (currentGudangTarget === 'opn') {
        $('#opn_gudang_id').val(id);
        $('#opn_gudang_display').val(displayText);
        if ($('#opn_items_container .item-row').length === 0) {
            tambahItemOpn();
        }
    } else if (currentGudangTarget === 'imp') {
        $('#imp_gudang_id').val(id);
        $('#imp_gudang_display').val(displayText);
    }
    if (lookupGudangModalInstance) {
        lookupGudangModalInstance.hide();
    }
}

$(document).ready(function () {
    // Search filter for Gudang lookup modal
    $('#lookupGudangSearchOpn').on('keyup', function () {
        const term = $(this).val().toLowerCase().trim();
        $('#lookupGudangTbodyOpn tr.lookup-gudang-row-opn').each(function () {
            const kode = $(this).data('kode') || '';
            const nama = $(this).data('nama') || '';
            if (kode.includes(term) || nama.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Auto Number toggles
    $('#opn_auto_number').on('change', function () {
        const manual = !this.checked;
        $('#opn_nomor_opening_stock').prop('disabled', !manual);
        if (!manual) $('#opn_nomor_opening_stock').val('');
    });

    $('#imp_auto_number').on('change', function () {
        const manual = !this.checked;
        $('#imp_nomor_opening_stock').prop('disabled', !manual);
        if (!manual) $('#imp_nomor_opening_stock').val('');
    });

    // Keterangan 40 char limit listener with warning alert
    $(document).on('input keyup paste', '#opn_keterangan, #imp_keterangan', function () {
        const val = $(this).val();
        if (val.length >= 40) {
            $(this).val(val.substring(0, 40));
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Terlampaui',
                text: 'Keterangan / Alasan maksimal 40 karakter! Teks telah disesuaikan.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    // Reset form when create opening button is clicked
    $('#btnTambahOpening').on('click', function () {
        $('#formTambahOpening')[0].reset();
        $('#edit_opening_id').val('');
        $('#modalTambahOpeningTitle').text('Buat Saldo Awal Persediaan');
        $('#modalTambahOpeningSubtitle').text('Isi rincian barang, kuantitas fisik, dan HPP awal');
        $('#opn_nomor_wrapper').show();
        $('#opn_gudang_id').val('');
        $('#opn_gudang_display').val('');
        $('#opn_items_container').empty();
        itemCounter = 0;
        $('#opn_grand_total').text('Rp 0');
        $('#opn_auto_number').prop('checked', true).trigger('change');
    });

    // Bind Action Buttons for Save Form
    $('#btnSimpanDraftOpening').on('click', function () {
        saveOpeningStockForm('draft');
    });

    $('#btnAjukanOpening').on('click', function () {
        saveOpeningStockForm('pending');
    });
});

// ── Indonesian Currency & Number Formatting Helpers ────────────────────
function unformatNumberID(str) {
    if (str === null || str === undefined || str === '') return 0;
    if (typeof str === 'number') return isNaN(str) ? 0 : str;
    let clean = String(str).trim();
    if (clean.indexOf(',') >= 0) {
        clean = clean.replace(/\./g, '').replace(/,/g, '.');
    } else {
        clean = clean.replace(/\./g, '');
    }
    let num = parseFloat(clean);
    return isNaN(num) ? 0 : num;
}

function formatRibuanID(val) {
    if (val === null || val === undefined || val === '') return '';
    let num = typeof val === 'number' ? val : unformatNumberID(val);
    if (isNaN(num) || num < 0) return '0';
    let parts = num.toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts[1] === '00' ? parts[0] : parts[0] + ',' + parts[1];
}

// ── Add Item Row ───────────────────────────────────────────────────────
function tambahItemOpn(prefill = null) {
    const gudangId = $('#opn_gudang_id').val();
    if (!gudangId && !prefill) {
        Swal.fire({
            icon: 'warning',
            title: 'Gudang Belum Dipilih',
            text: 'Silakan pilih Gudang Penyimpanan terlebih dahulu sebelum menambahkan barang!',
            confirmButtonText: 'Pilih Gudang Sekarang',
            confirmButtonColor: '#3b82f6'
        }).then(() => {
            openLookupGudangModalOpn('opn');
        });
        return;
    }

    const currentRows = $('#opn_items_container .item-row').length;
    if (currentRows >= 25 && !prefill) {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Maksimal Tercapai',
            text: 'Batas maksimal input manual per dokumen adalah 25 barang. Untuk penginputan item lebih dari 25, disarankan menggunakan fitur Import Excel!',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    const id = itemCounter++;
    const barangId = prefill?.barang_id || '';
    const barangDisplay = prefill?.barang_display || '';
    const satuan = prefill?.satuan || '-';
    const jumlah = prefill?.jumlah !== undefined ? prefill.jumlah : '';
    const rawHpp = prefill?.harga_pokok !== undefined ? prefill.harga_pokok : '';
    const hargaPokokFmt = rawHpp !== '' ? formatRibuanID(rawHpp) : '';

    const html = `
    <div class="item-row opn-item-card" id="item-row-${id}">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <label class="opn-form-label d-block">Nama Barang <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="barang-display-${id}" value="${barangDisplay}" readonly placeholder="Klik Cari Barang..." required onclick="pilihBarangForBtn(${id})" style="cursor: pointer;">
                    <button class="btn btn-primary" type="button" onclick="pilihBarangForBtn(${id})" title="Cari Barang">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="hidden" class="select-barang-input" id="select-barang-${id}" value="${barangId}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="opn-form-label d-block">Jumlah Stok <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="number" step="any" name="items[${id}][jumlah]" id="jumlah-${id}" value="${jumlah}"
                           class="form-control text-center input-jumlah font-monospace fw-bold" placeholder="0" min="0.01" required>
                    <span class="input-group-text small" id="satuan-label-${id}" style="font-size:0.75rem;">${satuan}</span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="opn-form-label d-block">HPP Awal Satuan <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text small" style="font-size:0.72rem;">Rp</span>
                    <input type="text" name="items[${id}][harga_pokok]" id="harga-pokok-${id}" value="${hargaPokokFmt}"
                           class="form-control text-end input-hpp font-monospace fw-bold" placeholder="0" inputmode="numeric" autocomplete="off" required>
                </div>
            </div>
            <div class="col-md-1 text-center mt-3">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRowOpn(${id})" title="Hapus Baris">
                    <i class="bi bi-trash3 fs-6"></i>
                </button>
            </div>
            <div class="col-12 mt-1">
                <div class="row-estimasi small text-muted" id="estimasi-row-${id}">
                    Estimasi Nilai: <strong class="text-success font-monospace">Rp 0</strong>
                </div>
            </div>
        </div>
    </div>`;

    $('#opn_items_container').append(html);
    hitungEstimasiBaris(id);
    hitungGrandTotal();
}

function hapusRowOpn(id) {
    $(`#item-row-${id}`).remove();
    hitungGrandTotal();
}

// ── Open Pilih Barang Popup ────────────────────────────────────────────
function pilihBarangForBtn(rowId) {
    const gudangId = $('#opn_gudang_id').val();
    if (!gudangId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Gudang Terlebih Dahulu',
            text: 'Silakan pilih Gudang Penyimpanan sebelum memilih barang!',
            confirmButtonColor: '#3b82f6'
        }).then(() => {
            openLookupGudangModalOpn('opn');
        });
        return;
    }

    activeRowId = rowId;

    if (!dtPilihBarang) {
        initPilihBarangTable();
    } else {
        dtPilihBarang.ajax.reload(null, false);
    }

    if (!pilihBarangModalInstance) {
        pilihBarangModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPilihBarang'));
    }
    pilihBarangModalInstance.show();
}

function initPilihBarangTable() {
    dtPilihBarang = $('#tablePilihBarang').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        responsive: true,
        ajax: {
            url: "{{ route('opening-stock.search-barang') }}",
            type: 'GET'
        },
        columns: [
            { data: 'kode_barang', name: 'kode_barang', class: 'fw-bold text-primary font-monospace small' },
            { data: 'nama_barang', name: 'nama_barang', class: 'fw-semibold' },
            { data: 'satuan_name', name: 'satuan.nama_satuan', class: 'text-muted small text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center' }
        ],
        pageLength: 10,
        lengthMenu: [5, 10, 25],
        language: {
            search: 'Cari Barang:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_',
            paginate: { previous: '←', next: '→' }
        }
    });
}

function selectBarangFromModalDirect(barangId, kode, nama, satuan) {
    if (activeRowId === null) return;

    // Duplicate check
    let isDuplicate = false;
    $('.select-barang-input').each(function () {
        if ($(this).attr('id') !== `select-barang-${activeRowId}` && $(this).val() == barangId) {
            isDuplicate = true;
            return false;
        }
    });

    if (isDuplicate) {
        Swal.fire('Barang Ganda', 'Barang ini sudah ditambahkan ke daftar!', 'warning');
        return;
    }

    $(`#select-barang-${activeRowId}`).val(barangId);
    $(`#barang-display-${activeRowId}`).val(`${kode} - ${nama}`);
    $(`#satuan-label-${activeRowId}`).text(satuan);
    
    // Close modal
    if (pilihBarangModalInstance) {
        pilihBarangModalInstance.hide();
    }
    
    const currentActive = activeRowId;
    activeRowId = null;
    hitungEstimasiBaris(currentActive);
    hitungGrandTotal();
}

// ── Live Calculations ──────────────────────────────────────────────────
function hitungEstimasiBaris(id) {
    const qty = parseFloat($(`#jumlah-${id}`).val()) || 0;
    const hpp = unformatNumberID($(`#harga-pokok-${id}`).val());
    const total = qty * hpp;
    
    $(`#estimasi-row-${id}`).html(`Estimasi Nilai: <strong class="text-success font-monospace">Rp ${total.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}</strong>`);
}

function hitungGrandTotal() {
    let grandTotal = 0;
    $('.item-row').each(function() {
        const idAttr = $(this).attr('id');
        const id = idAttr.split('-').pop();
        const qty = parseFloat($(`#jumlah-${id}`).val()) || 0;
        const hpp = unformatNumberID($(`#harga-pokok-${id}`).val());
        grandTotal += (qty * hpp);
    });
    
    $('#opn_grand_total').text(`Rp ${grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`);
}

$(document).on('input change', '.input-jumlah', function () {
    const idAttr = $(this).attr('id');
    if (!idAttr) return;
    const idx = idAttr.split('-').pop();
    hitungEstimasiBaris(idx);
    hitungGrandTotal();
});

$(document).on('keyup input paste', '.input-hpp', function (e) {
    let rawVal = $(this).val();
    if (rawVal === '') {
        const idAttr = $(this).attr('id');
        if (idAttr) {
            const idx = idAttr.split('-').pop();
            hitungEstimasiBaris(idx);
            hitungGrandTotal();
        }
        return;
    }
    
    let num = unformatNumberID(rawVal);
    let formatted = formatRibuanID(num);
    $(this).val(formatted);

    const idAttr = $(this).attr('id');
    if (!idAttr) return;
    const idx = idAttr.split('-').pop();
    hitungEstimasiBaris(idx);
    hitungGrandTotal();
});

// ── Edit Opening Modal Trigger ─────────────────────────────────────────
function editOpeningModal(id) {
    if (window.event && window.event.stopPropagation) {
        window.event.stopPropagation();
    }

    Swal.fire({
        title: 'Memuat Data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: `/opening-stock/${id}/edit`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            Swal.close();
            const os = res.openingStock;
            
            $('#formTambahOpening')[0].reset();
            $('#edit_opening_id').val(os.id);
            $('#modalTambahOpeningTitle').text(`Edit Saldo Awal: ${os.nomor_opening_stock}`);
            $('#modalTambahOpeningSubtitle').text('Perbarui daftar barang fisik atau nilai HPP awal');
            
            $('#opn_gudang_id').val(os.gudang_id);
            const gudangName = os.gudang ? (os.gudang.kode_gudang ? `${os.gudang.kode_gudang} - ${os.gudang.nama_gudang}` : os.gudang.nama_gudang) : '';
            $('#opn_gudang_display').val(gudangName);

            // Format date YYYY-MM-DD
            if (os.tanggal_opening_stock) {
                const d = new Date(os.tanggal_opening_stock);
                const yr = d.getFullYear();
                const mo = String(d.getMonth() + 1).padStart(2, '0');
                const dt = String(d.getDate()).padStart(2, '0');
                $('#opn_tanggal').val(`${yr}-${mo}-${dt}`);
            }

            $('#opn_nomor_wrapper').hide();
            $('#opn_keterangan').val(os.keterangan || '');

            $('#opn_items_container').empty();
            itemCounter = 0;

            (os.details || []).forEach(d => {
                const b = d.barang;
                const bDisplay = b ? `${b.kode_barang} - ${b.nama_barang}` : '-';
                const satName = b && b.satuan ? b.satuan.nama_satuan : 'Pcs';
                tambahItemOpn({
                    barang_id: d.barang_id,
                    barang_display: bDisplay,
                    satuan: satName,
                    jumlah: parseFloat(d.jumlah),
                    harga_pokok: parseFloat(d.harga_pokok)
                });
            });

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambahOpening'));
            modal.show();
        },
        error: function (xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Tidak dapat memuat data saldo awal.', 'error');
        }
    });
}

// ── Unified Store / Update Form Handler ────────────────────────────────
function saveOpeningStockForm(actionType) {
    const form = $('#formTambahOpening')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const editId = $('#edit_opening_id').val();
    const isEdit = !!editId;
    const items = [];
    let valid = true;
    let validationMsg = '';

    $('.select-barang-input').each(function () {
        const idAttr = $(this).attr('id');
        const idx = idAttr.split('-').pop();
        const bid = $(this).val();
        const qty = parseFloat($(`#jumlah-${idx}`).val());
        const hpp = unformatNumberID($(`#harga-pokok-${idx}`).val());

        if (!bid || isNaN(qty) || qty <= 0 || isNaN(hpp) || hpp < 0) {
            valid = false;
            $(`#barang-display-${idx}`).addClass('is-invalid');
            $(`#jumlah-${idx}`).addClass('is-invalid');
            $(`#harga-pokok-${idx}`).addClass('is-invalid');
            validationMsg = 'Mohon lengkapi seluruh kolom barang, jumlah stok, dan HPP awal!';
        } else {
            items.push({ barang_id: bid, jumlah: qty, harga_pokok: hpp });
        }
    });

    if (items.length === 0) {
        valid = false;
        validationMsg = 'Tambahkan minimal 1 barang!';
    } else if (items.length > 25) {
        valid = false;
        validationMsg = 'Maksimal 25 barang per dokumen! Silakan gunakan fitur Import Excel untuk penginputan item lebih banyak.';
    }

    if (!valid) {
        Swal.fire('Validasi Gagal', validationMsg, 'error');
        return;
    }

    const $btnDraft = $('#btnSimpanDraftOpening');
    const $btnAjukan = $('#btnAjukanOpening');
    $btnDraft.prop('disabled', true);
    $btnAjukan.prop('disabled', true);

    const gudangId = $('#opn_gudang_id').val();
    const tanggal = $('#opn_tanggal').val();
    const keterangan = $('#opn_keterangan').val();
    const autoNum = $('#opn_auto_number').is(':checked') ? 1 : 0;
    const nomor = $('#opn_nomor_opening_stock').val();

    const targetUrl = isEdit ? `/opening-stock/${editId}` : '/opening-stock/store';
    const targetMethod = isEdit ? 'PUT' : 'POST';

    $.ajax({
        url: targetUrl,
        method: targetMethod,
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data: JSON.stringify({
            gudang_id: gudangId,
            tanggal_opening_stock: tanggal,
            keterangan: keterangan,
            auto_number: autoNum,
            nomor_opening_stock: nomor,
            submit_action: actionType,
            items: items,
        }),
        success: function (res) {
            $('#modalTambahOpening').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                if (typeof reloadTableOpening === 'function') reloadTableOpening();
                location.reload();
            });
        },
        error: function (xhr) {
            $btnDraft.prop('disabled', false);
            $btnAjukan.prop('disabled', false);
            const res = xhr.responseJSON ?? {};
            
            if (xhr.status === 422 && res.errors) {
                let errMsgs = [];
                if (typeof res.errors === 'object') {
                    Object.keys(res.errors).forEach(k => {
                        const msg = Array.isArray(res.errors[k]) ? res.errors[k][0] : res.errors[k];
                        errMsgs.push(msg);
                    });
                }
                Swal.fire({
                    title: 'Validasi Gagal',
                    html: `<div class="text-start"><ul class="text-danger small ps-3 mb-0">${errMsgs.map(m => `<li>${m}</li>`).join('')}</ul></div>`,
                    icon: 'error'
                });
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan server.', 'error');
            }
        }
    });
}

// ── Submit Excel Import ────────────────────────────────────────────────
$('#btnProsesImport').on('click', function () {
    const form = $('#formImportOpening')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const fileInput = $('#imp_excel_file')[0];
    if (fileInput.files.length === 0) {
        Swal.fire('Validasi Gagal', 'Silakan pilih file Excel terlebih dahulu!', 'warning');
        return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengimport...');

    const submitAction = $('input[name="imp_submit_action"]:checked').val() || 'draft';

    const formData = new FormData();
    formData.append('gudang_id', $('#imp_gudang_id').val());
    formData.append('tanggal_opening_stock', $('#imp_tanggal').val());
    formData.append('keterangan', $('#imp_keterangan').val());
    formData.append('auto_number', $('#imp_auto_number').is(':checked') ? 1 : 0);
    formData.append('nomor_opening_stock', $('#imp_nomor_opening_stock').val());
    formData.append('submit_action', submitAction);
    formData.append('excel_file', fileInput.files[0]);

    $.ajax({
        url: '/opening-stock/import',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            $('#modalImportOpening').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Import Berhasil!',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                if (typeof reloadTableOpening === 'function') reloadTableOpening();
                location.reload();
            });
        },
        error: function (xhr) {
            $btn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Jalankan Import');
            const res = xhr.responseJSON ?? {};
            
            if (xhr.status === 422 && res.errors && Array.isArray(res.errors)) {
                let errorListHtml = '<div class="text-start overflow-auto" style="max-height: 250px;"><ul class="text-danger small ps-3">';
                res.errors.forEach(err => {
                    errorListHtml += `<li>${err}</li>`;
                });
                errorListHtml += '</ul></div>';
                
                Swal.fire({
                    title: 'Data Excel Tidak Valid',
                    html: errorListHtml,
                    icon: 'error'
                });
            } else if (xhr.status === 422 && res.errors && typeof res.errors === 'object') {
                let firstErrKey = Object.keys(res.errors)[0];
                Swal.fire('Validasi Gagal', res.errors[firstErrKey][0], 'error');
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat memproses data Excel.', 'error');
            }
        }
    });
});

function notifyTemplateDownload() {
    if (typeof window.hideLoader === 'function') {
        window.hideLoader();
    }
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Template Excel Saldo Awal sedang diunduh.',
        timer: 2000,
        showConfirmButton: false,
        position: 'center'
    });
}
</script>
@endpush
