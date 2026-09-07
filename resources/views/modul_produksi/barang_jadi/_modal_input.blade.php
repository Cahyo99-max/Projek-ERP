{{-- MODAL INPUT / EDIT PENERIMAAN BARANG JADI (HASIL PRODUKSI) - ULTRA SLEEK EXECUTIVE DESIGN --}}
<div class="modal fade" id="modalInputPenerimaanBarangJadi" tabindex="-1" aria-labelledby="modalInputPBJTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable pbj-dialog">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">
            
            {{-- ULTRA SLEEK HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative pbj-header-gradient">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center pbj-header-icon-box">
                        <i class="bi bi-box-seam-fill fs-5 text-emerald-light"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalInputPBJTitle" style="letter-spacing: -0.2px;">
                                Penerimaan Hasil Produksi
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 pbj-header-badge">FG RECEIPT</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;">Pencatatan penyerahan barang jadi ke gudang & pemutakhiran stok otomatis</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" onclick="closeModalInputPenerimaanBarangJadi()" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-3 p-md-4">
                <form id="formInputPenerimaanBarangJadi" autocomplete="off">
                    @csrf
                    <input type="hidden" id="pbj_edit_id" name="pbj_edit_id" value="">

                    {{-- 1. SPK SELECTION HERO CARD --}}
                    <div class="pbj-panel-card p-3 p-md-3.5 mb-3.5 rounded-3">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-bold spk-text-main mb-1.5 d-flex align-items-center gap-2">
                                    <span class="pbj-dot bg-primary"></span>
                                    <span>Pilih Dokumen SPK Produksi</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <select id="pbj_spk_id" name="spk_id" class="form-select spk-input fw-semibold" onchange="onPbjSpkChanged(this.value)" required style="font-size: 13.5px;">
                                        <option value="">-- Pilih SPK Siap Diterima --</option>
                                        @foreach($spkList as $s)
                                            @php
                                                $bNamaShort = $s->barangJadi ? \Illuminate\Support\Str::limit($s->barangJadi->nama_barang, 36) : '-';
                                            @endphp
                                            <option value="{{ $s->id }}">[{{ $s->nomor_spk }}] {{ $bNamaShort }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary px-3.5" type="button" onclick="bukaModalLookupSpkPbj()" title="Cari Dokumen SPK">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 text-start text-md-end">
                                <span class="small fw-semibold spk-text-muted d-block mb-1 text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">Status Work Order</span>
                                <span id="pbj_info_status_spk" class="badge bg-secondary rounded-pill px-3 py-1.5 shadow-2xs" style="font-size: 11.5px; font-weight: 600;">-</span>
                            </div>
                        </div>

                        {{-- 4 MINIMALIST METRIC TILES --}}
                        <div id="pbj_spk_highlight_box" class="pt-3 border-top border-secondary border-opacity-10 d-none">
                            <div class="row g-2 mb-2.5">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card">
                                        <span class="pbj-metric-sub">PRODUK JADI</span>
                                        <div id="pbj_info_barang_jadi" class="pbj-metric-main text-truncate" title="">-</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card">
                                        <span class="pbj-metric-sub">TARGET QTY</span>
                                        <div id="pbj_info_target_qty" class="pbj-metric-main text-primary">-</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card">
                                        <span class="pbj-metric-sub text-emerald">SDH DITERIMA</span>
                                        <div id="pbj_info_selesai_qty" class="pbj-metric-main text-emerald">-</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card">
                                        <span class="pbj-metric-sub text-warning">SISA TARGET</span>
                                        <div id="pbj_info_sisa_target" class="pbj-metric-main text-warning">-</div>
                                    </div>
                                </div>
                            </div>

                            {{-- REAL-TIME PROGRESS BAR --}}
                            <div class="p-2 px-3 rounded-2 pbj-progress-track">
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                    <span class="spk-text-muted d-flex align-items-center gap-1.5">
                                        <i class="bi bi-graph-up-arrow text-emerald"></i>
                                        <span>Progres Target Produksi</span>
                                    </span>
                                    <span id="pbj_info_progress_percent" class="fw-bold text-emerald">0% Selesai</span>
                                </div>
                                <div class="progress" style="height: 5px; border-radius: 99px; background-color: rgba(128,128,128,0.15);">
                                    <div id="pbj_info_progress_bar" class="progress-bar rounded-pill bg-emerald-vibrant" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. KUANTITAS HASIL PRODUKSI (CLEAN & PROFESSIONAL) --}}
                    <div class="row g-3 mb-3.5">
                        {{-- KUANTITAS DITERIMA (BAIK) --}}
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <label class="form-label small fw-bold spk-text-main mb-0">
                                    Kuantitas Diterima (Baik) <span class="text-danger">*</span>
                                </label>
                                <button type="button" id="btn_max_sisa_pbj" class="btn btn-link btn-sm text-decoration-none p-0 text-success fw-bold d-none" onclick="fillMaxSisaTargetPbj()" style="font-size: 12px;">
                                    <i class="bi bi-lightning-charge-fill me-0.5"></i> Isi Sisa Target
                                </button>
                            </div>
                            <div class="input-group input-group-lg shadow-2xs rounded-3 overflow-hidden">
                                <input type="number" step="any" min="0.0001" id="pbj_jumlah_hasil_baik" name="jumlah_hasil_baik" 
                                       class="form-control spk-input text-end fw-bold fs-5 text-success no-spin-input" 
                                       placeholder="0" value="" autocomplete="off" required>
                                <span class="input-group-text spk-input text-muted pbj_satuan_label fw-semibold fs-6 px-3">Pieces</span>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                <i class="bi bi-check-circle text-success me-1"></i>Menambah persediaan barang jadi siap jual di gudang.
                            </small>
                        </div>

                        {{-- KUANTITAS REJECT (AFVAL / BS) --}}
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <label class="form-label small fw-bold spk-text-main mb-0">
                                    Kuantitas Reject (BS / Afval)
                                </label>
                                <span class="text-muted" style="font-size: 11px;">Opsional</span>
                            </div>
                            <div class="input-group input-group-lg shadow-2xs rounded-3 overflow-hidden">
                                <input type="number" step="any" min="0" id="pbj_jumlah_reject" name="jumlah_reject" 
                                       class="form-control spk-input text-end fw-bold fs-5 text-danger no-spin-input" 
                                       placeholder="0" value="" autocomplete="off">
                                <span class="input-group-text spk-input text-muted pbj_satuan_label fw-semibold fs-6 px-3">Pieces</span>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                <i class="bi bi-x-circle text-danger me-1"></i>Barang rusak/afval tidak masuk stok penjualan.
                            </small>
                        </div>
                    </div>

                    {{-- 3. LOKASI & WAKTU PENERIMAAN --}}
                    <div class="pbj-panel-card p-3 p-md-3.5 mb-3.5 rounded-3">
                        <label class="form-label small fw-bold spk-text-main mb-2.5 d-flex align-items-center gap-2">
                            <span class="pbj-dot bg-info"></span>
                            <span>Lokasi & Tanggal Penerimaan</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-1">Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" id="pbj_tanggal_penerimaan" name="tanggal_penerimaan" class="form-control spk-input rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-1">Gudang Tujuan Hasil <span class="text-danger">*</span></label>
                                <input type="hidden" id="pbj_gudang_id" name="gudang_id" value="" required>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden" onclick="bukaModalLookupGudangPbj()" style="cursor: pointer;" title="Klik untuk memilih gudang">
                                    <input type="text" id="pbj_gudang_nama_display" class="form-control spk-input spk-readonly border-end-0 cursor-pointer fw-semibold text-primary" placeholder="-- Pilih Gudang Tujuan --" readonly required style="cursor: pointer;">
                                    <button class="btn btn-outline-primary px-3 flex-shrink-0" type="button" onclick="event.stopPropagation(); bukaModalLookupGudangPbj();" title="Cari Gudang">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-1">Lokasi Rak / Bin (Opsional)</label>
                                <input type="hidden" id="pbj_lokasi_rak_id" name="lokasi_rak_id" value="">
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden" onclick="bukaModalLookupRakPbj()" style="cursor: pointer;" title="Klik untuk memilih rak">
                                    <input type="text" id="pbj_lokasi_rak_nama_display" class="form-control spk-input spk-readonly border-end-0 cursor-pointer" placeholder="-- Tanpa Rak / Pilih Rak --" readonly style="cursor: pointer;">
                                    <button class="btn btn-outline-info px-3 flex-shrink-0" type="button" onclick="event.stopPropagation(); bukaModalLookupRakPbj();" title="Cari Rak">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. SERAH TERIMA & CATATAN --}}
                    <div class="pbj-panel-card p-3 p-md-3.5 mb-1 rounded-3">
                        <label class="form-label small fw-bold spk-text-main mb-2.5 d-flex align-items-center gap-2">
                            <span class="pbj-dot bg-secondary"></span>
                            <span>Petugas & Catatan Serah Terima</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small spk-text-muted mb-1 d-flex align-items-center justify-content-between">
                                    <span>Petugas Penerima (Gudang)</span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                                        <i class="bi bi-lock-fill me-0.5"></i>User Login
                                    </span>
                                </label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted">
                                        <i class="bi bi-person-fill-lock"></i>
                                    </span>
                                    <input type="text" id="pbj_penerima_nama" name="penerima_nama" class="form-control spk-input spk-readonly border-start-0 fw-semibold" placeholder="Nama petugas gudang..." value="{{ auth()->user()->nama_lengkap }}" maxlength="40" readonly required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small spk-text-muted mb-1">
                                    Penyerah / Operator QC <span class="text-danger">* (Wajib)</span>
                                </label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted">
                                        <i class="bi bi-person-badge"></i>
                                    </span>
                                    <input type="text" id="pbj_penyerah_nama" name="penyerah_nama" class="form-control spk-input border-start-0" placeholder="Ketik nama penyerah / operator QC..." maxlength="40" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small spk-text-muted mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea id="pbj_catatan" name="catatan" class="form-control spk-input rounded-3" rows="2" placeholder="Catatan tambahan batch produksi, kondisi barang, dsb..." maxlength="100"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- CINEMATIC FOOTER --}}
            <div class="modal-footer pbj-modal-footer px-3 px-md-4 py-3">
                <div class="d-flex align-items-center justify-content-end gap-2.5 w-100 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold flex-fill flex-md-grow-0" onclick="closeModalInputPenerimaanBarangJadi()">
                        Batal
                    </button>
                    <button type="button" id="btn_save_pbj" class="btn btn-sm btn-emerald-cinematic rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 flex-fill flex-md-grow-0" onclick="submitPenerimaanBarangJadiForm()">
                        <i class="bi bi-check-circle-fill fs-6"></i>
                        <span>Simpan Penerimaan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* MODAL BASE STYLING */
    .pbj-modal-container {
        background-color: var(--spk-card-bg, #ffffff) !important;
        border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    }

    .pbj-header-gradient {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%);
    }

    .pbj-header-icon-box {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        width: 44px;
        height: 44px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .pbj-header-badge {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .pbj-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    /* CARD PANELS */
    .pbj-panel-card {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }
    html[data-bs-theme="dark"] .pbj-panel-card,
    .dark-mode .pbj-panel-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.07);
    }

    /* METRIC TILES */
    .pbj-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.6rem 0.8rem;
    }
    html[data-bs-theme="dark"] .pbj-metric-card,
    .dark-mode .pbj-metric-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .pbj-metric-sub {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.4px;
        display: block;
        color: var(--spk-text-muted, #64748b);
        margin-bottom: 2px;
    }
    .pbj-metric-main {
        font-size: 13.5px;
        font-weight: 700;
    }

    .pbj-progress-track {
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-bs-theme="dark"] .pbj-progress-track,
    .dark-mode .pbj-progress-track {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* NO SPINNER NUMBER INPUT */
    .no-spin-input::-webkit-outer-spin-button,
    .no-spin-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-spin-input {
        -moz-appearance: textfield;
    }

    .pbj-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0 !important;
    }
    html[data-bs-theme="dark"] .pbj-modal-footer,
    .dark-mode .pbj-modal-footer {
        background: rgba(15, 23, 42, 0.7);
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .btn-emerald-cinematic {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-emerald-cinematic:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff !important;
        transform: translateY(-1.5px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    }

    @media (max-width: 576px) {
        .pbj-dialog {
            margin: 0.5rem !important;
            max-width: calc(100% - 1rem) !important;
        }
    }
</style>

@push('scripts')
<script>
    let _activePbjSpkData = null;

    function openModalInputPenerimaanBarangJadi(spkId = null) {
        $('#formInputPenerimaanBarangJadi')[0].reset();
        $('#pbj_edit_id').val('');
        $('#pbj_gudang_id').val('');
        $('#pbj_gudang_nama_display').val('');
        $('#pbj_lokasi_rak_id').val('');
        $('#pbj_lokasi_rak_nama_display').val('');
        $('#pbj_jumlah_hasil_baik').val('');
        $('#pbj_jumlah_reject').val('');
        $('#pbj_penerima_nama').val("{{ auth()->user()->nama_lengkap }}");
        $('#pbj_penyerah_nama').val('');
        $('#modalInputPBJTitle').text('Penerimaan Hasil Produksi');
        $('#pbj_spk_highlight_box').addClass('d-none');
        _activePbjSpkData = null;

        if (spkId) {
            $('#pbj_spk_id').val(spkId).trigger('change');
        }

        $('#modalInputPenerimaanBarangJadi').modal('show');
    }

    function closeModalInputPenerimaanBarangJadi() {
        $('#modalInputPenerimaanBarangJadi').modal('hide');
    }

    function onPbjSpkChanged(spkId) {
        if (!spkId) {
            $('#pbj_spk_highlight_box').addClass('d-none');
            _activePbjSpkData = null;
            return;
        }

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/detail`,
            method: 'GET',
            success: function (res) {
                if (!res.success) return;

                const spk = res.spk;
                _activePbjSpkData = res;

                const bKode = spk.barang_jadi ? spk.barang_jadi.kode_barang : '-';
                const bNama = spk.barang_jadi ? spk.barang_jadi.nama_barang : '-';
                const satName = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');

                const targetQty = Number(spk.jumlah_rencana || res.target_qty || 0);
                const selesaiQty = Number(res.jumlah_selesai || res.total_diterima || 0);
                const sisaTarget = Number(res.sisa_target || Math.max(0, targetQty - selesaiQty));

                let progressPercent = 0;
                let percentDisplay = '0%';
                if (targetQty > 0 && selesaiQty > 0) {
                    const rawPct = (selesaiQty / targetQty) * 100;
                    if (rawPct >= 100) {
                        progressPercent = 100;
                        percentDisplay = '100%';
                    } else if (rawPct < 0.1) {
                        progressPercent = Math.max(1, rawPct);
                        percentDisplay = `${rawPct.toFixed(2)}%`;
                    } else if (rawPct < 1) {
                        progressPercent = Math.max(1, rawPct);
                        percentDisplay = `${rawPct.toFixed(1)}%`;
                    } else {
                        progressPercent = Math.min(100, Math.round(rawPct));
                        percentDisplay = `${progressPercent}%`;
                    }
                }

                $('#pbj_info_barang_jadi').text(`[${bKode}] ${bNama}`).attr('title', `[${bKode}] ${bNama}`);
                $('#pbj_info_target_qty').text(`${targetQty.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_selesai_qty').text(`${selesaiQty.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_sisa_target').text(`${sisaTarget.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_progress_percent').text(`${percentDisplay} Selesai`);
                $('#pbj_info_progress_bar').css('width', `${progressPercent}%`).attr('aria-valuenow', progressPercent);

                $('.pbj_satuan_label').text(satName);

                // Auto-fill default warehouse if available
                if (!$('#pbj_edit_id').val() && !$('#pbj_gudang_id').val() && res.default_gudang_id) {
                    $('#pbj_gudang_id').val(res.default_gudang_id);
                    const dGudangKode = res.default_gudang_kode ? `[${res.default_gudang_kode}] ` : '';
                    $('#pbj_gudang_nama_display').val(`${dGudangKode}${res.default_gudang_nama || 'Gudang Barang Jadi'}`);
                }

                if (spk.status === 'In Progress') {
                    $('#pbj_info_status_spk').html('<span class="badge spk-badge-cyan rounded-pill px-3 py-1 fw-bold"><i class="bi bi-play-circle-fill me-1"></i>In Progress</span>');
                } else if (spk.status === 'Completed') {
                    $('#pbj_info_status_spk').html('<span class="badge spk-badge-green rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>');
                } else {
                    $('#pbj_info_status_spk').html(`<span class="badge spk-badge-muted rounded-pill px-3 py-1">${spk.status}</span>`);
                }

                if (sisaTarget > 0) {
                    $('#btn_max_sisa_pbj').removeClass('d-none');
                } else {
                    $('#btn_max_sisa_pbj').addClass('d-none');
                }

                // Qty diset kosong saat tambah data baru agar diisi manual oleh user
                if (!$('#pbj_edit_id').val()) {
                    $('#pbj_jumlah_hasil_baik').val('');
                    $('#pbj_jumlah_reject').val('');
                }

                $('#pbj_spk_highlight_box').removeClass('d-none');
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Gagal memuat detail SPK.';
                Swal.fire({
                    title: 'Penerimaan Ditolak!',
                    html: msg,
                    icon: 'error',
                    confirmButtonColor: '#1d4ed8'
                });
                $('#pbj_spk_id').val('');
                $('#pbj_spk_highlight_box').addClass('d-none');
                _activePbjSpkData = null;
                closeModalInputPenerimaanBarangJadi();
            }
        });
    }

    function fillMaxSisaTargetPbj() {
        if (!_activePbjSpkData) return;
        const sisa = Number(_activePbjSpkData.sisa_target || 0);
        if (sisa > 0) {
            $('#pbj_jumlah_hasil_baik').val(sisa).focus();
        }
    }

    function editPenerimaanBarangJadi(id) {
        Swal.fire({
            title: 'Memuat Data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/${id}`,
            method: 'GET',
            success: function (res) {
                Swal.close();
                if (!res.success) return;

                const p = res.penerimaan;
                $('#pbj_edit_id').val(p.id);
                $('#modalInputPBJTitle').text(`Edit Penerimaan #${p.nomor_penerimaan}`);

                $('#pbj_spk_id').val(p.spk_id).trigger('change');
                $('#pbj_tanggal_penerimaan').val(p.tanggal_penerimaan ? p.tanggal_penerimaan.split('T')[0] : '');

                $('#pbj_gudang_id').val(p.gudang_id);
                const gNama = p.gudang ? `[${p.gudang.kode_gudang}] ${p.gudang.nama_gudang}` : '';
                $('#pbj_gudang_nama_display').val(gNama);

                $('#pbj_lokasi_rak_id').val(p.lokasi_rak_id || '');
                if (p.lokasi_rak) {
                    const r = p.lokasi_rak;
                    let rLabel = `[${r.kode_rak}] ${r.nama_rak}`;
                    if (r.level) rLabel += ` - Lantai: ${r.level}`;
                    if (r.baris || r.kolom) rLabel += ` (Brs: ${r.baris || '-'}, Klm: ${r.kolom || '-'})`;
                    $('#pbj_lokasi_rak_nama_display').val(rLabel);
                } else {
                    $('#pbj_lokasi_rak_nama_display').val('');
                }

                $('#pbj_jumlah_hasil_baik').val(parseFloat(p.jumlah_hasil_baik) || 0);
                $('#pbj_jumlah_reject').val(parseFloat(p.jumlah_reject) || 0);
                $('#pbj_penerima_nama').val(p.penerima_nama || '');

                $('#pbj_penyerah_nama').val(p.penyerah_nama || '');
                $('#pbj_catatan').val(p.catatan || '');

                $('#modalDetailBarangJadi').modal('hide');
                $('#modalInputPenerimaanBarangJadi').modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire('Error', 'Gagal memuat detail transaksi penerimaan.', 'error');
            }
        });
    }

    function submitPenerimaanBarangJadiForm() {
        const form = $('#formInputPenerimaanBarangJadi');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const editId = $('#pbj_edit_id').val();
        const url = editId ? `/produksi/barang-jadi/${editId}` : '/produksi/barang-jadi';
        const method = editId ? 'PUT' : 'POST';

        const submitBtn = $('#btn_save_pbj');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: editId ? 'Berhasil Diperbarui!' : 'Penerimaan Berhasil Dicatat!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    $('#modalInputPenerimaanBarangJadi').modal('hide');
                    reloadTableBarangJadi();
                }
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Gagal menyimpan data penerimaan barang jadi.';
                Swal.fire('Gagal Simpan!', msg, 'error');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle-fill fs-6 me-1"></i> Simpan Penerimaan');
            }
        });
    }

    function terimaBarangJadiSpk(spkId) {
        openModalInputPenerimaanBarangJadi(spkId);
    }
</script>
@endpush
