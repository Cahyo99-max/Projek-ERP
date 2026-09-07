{{-- MODAL TAMBAH / BUAT SPK PRODUKSI BARU --}}
<div class="modal fade spk-modal-primary" id="modalTambahSpk" tabindex="-1" aria-labelledby="modalTambahSpkLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-blue py-2.5 py-sm-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto" style="min-width: 0;">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.18);">
                        <i class="bi bi-file-earmark-ruled fs-5 fs-sm-4"></i>
                    </div>
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-sm-5 text-truncate" id="modalTambahSpkLabel">Terbitkan SPK Produksi</h5>
                        <small class="text-white-50 d-none d-sm-block">Tentukan target Qty, periksa stok bahan baku, dan pilih tahapan pengerjaan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- FORM INPUT --}}
            <form id="formInputSpk">
                @csrf
                <div class="modal-body spk-modal-body p-3 p-sm-4">

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 1: HEADER SPK & ACUAN RESEP BOM                    --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-3 mb-sm-4">
                        <div class="spk-card-header border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main fs-6">
                                    <i class="bi bi-journal-text me-2 text-primary"></i>Informasi Utama & Resep Acuan
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Pilih Resep BOM untuk mengisi informasi produk dan membuka form SPK</small>
                            </div>
                        </div>
                        <div class="card-body p-3 p-sm-4">
                            <div class="row g-3">
                                {{-- Nomor SPK --}}
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-hash text-primary me-1"></i>Nomor SPK <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs">
                                        <input type="text" name="nomor_spk" id="inp_nomor_spk"
                                               class="form-control spk-input fw-bold font-monospace spk-readonly" readonly required>
                                        <button type="button" class="btn btn-outline-secondary spk-input-group-text border"
                                                onclick="generateNomorSpkInput()" title="Refresh Nomor SPK">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" id="chk_auto_number_spk" checked onchange="toggleAutoNumberSpk(this.checked)">
                                        <label class="form-check-label small spk-text-muted" for="chk_auto_number_spk">Nomor Otomatis</label>
                                    </div>
                                </div>

                                {{-- Pilih Resep BOM --}}
                                <div class="col-12 col-md-8">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-box-seam text-primary me-1"></i>Acuan Resep BOM (Approved) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs">
                                        <input type="hidden" name="bom_id" id="inp_bom_id" required>
                                        <input type="hidden" name="barang_jadi_id" id="inp_barang_jadi_id">
                                        <input type="hidden" name="satuan_id" id="inp_satuan_id">
                                        <input type="text" id="inp_bom_name_display"
                                               class="form-control spk-input spk-readonly rounded-start-3 fw-semibold"
                                               placeholder="-- Klik 🔍 untuk memilih Resep BOM yang disetujui --"
                                               readonly required onclick="bukaModalLookupBomSpk()">
                                        <button type="button" class="btn btn-primary rounded-end-3 px-3 fw-bold" onclick="bukaModalLookupBomSpk()">
                                            <i class="bi bi-search me-1"></i> Cari BOM
                                        </button>
                                    </div>
                                    <div id="disp_tender_info" class="mt-2 d-none">
                                        <span class="badge rounded-pill px-3 py-1.5 fw-semibold" style="background:rgba(139,92,246,0.12);color:#7c3aed;border:1px solid rgba(139,92,246,0.3);font-size:12px;">
                                            <i class="bi bi-file-earmark-text-fill me-1"></i> Terkait Tender / Kontrak: <strong id="lbl_tender_name"></strong>
                                        </span>
                                    </div>
                                </div>

                                {{-- Barang Jadi + Target Qty --}}
                                <div class="col-12 col-md-5">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-box2 text-primary me-1"></i>Produk Barang Jadi (Hasil)
                                    </label>
                                    <input type="text" id="inp_barang_jadi_display"
                                           class="form-control spk-input spk-readonly fw-bold text-primary" readonly placeholder="-- Otomatis terisi dari Resep BOM --">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calculator text-primary me-1"></i>Target Qty Produksi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs">
                                        <input type="number" step="1" min="1" name="jumlah_rencana" id="inp_jumlah_rencana"
                                               class="form-control spk-input fw-extrabold text-center text-primary fs-5"
                                               value="" placeholder="0" required disabled oninput="hitungUlangBahanSpk()">
                                        <span class="input-group-text spk-input-group-text fw-semibold" id="label_satuan_spk">Satuan</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calendar-event text-primary me-1"></i>Tanggal Terbit <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_spk" id="inp_tanggal_spk"
                                           class="form-control spk-input" value="{{ date('Y-m-d') }}" required disabled>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calendar-check text-primary me-1"></i>Target Selesai
                                    </label>
                                    <input type="date" name="target_selesai" id="inp_target_selesai"
                                           class="form-control spk-input" value="{{ date('Y-m-d', strtotime('+7 days')) }}" disabled>
                                </div>

                                {{-- Tipe, Prioritas, Mandor --}}
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-diagram-2 text-primary me-1"></i>Tipe SPK
                                    </label>
                                    <select name="tipe_spk" id="inp_tipe_spk" class="form-select spk-select" disabled>
                                        <option value="Make to Stock" selected>Make to Stock (Stok Gudang)</option>
                                        <option value="Make to Order">Make to Order (Pesanan/Tender)</option>
                                        <option value="Custom/Sampel">Custom / Sampel (Prototype)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-flag text-primary me-1"></i>Prioritas Produksi
                                    </label>
                                    <select name="prioritas" id="inp_prioritas" class="form-select spk-select" disabled>
                                        <option value="Normal" selected>Normal (Standard Process)</option>
                                        <option value="Tinggi (Urgent)">Tinggi (Urgent / Prioritas Utama)</option>
                                        <option value="Rendah">Rendah (Fleksibel)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-person-badge text-primary me-1"></i>PIC / Penanggung Jawab <span class="text-danger">*</span>
                                    </label>
                                    <select name="penanggung_jawab_id" id="inp_penanggung_jawab_id" class="form-select spk-select" disabled>
                                        <option value="">-- Pilih PIC Produksi --</option>
                                        @foreach($picUsers as $pic)
                                            <option value="{{ $pic->id }}">{{ $pic->nama_lengkap }} {{ $pic->position ? '('.$pic->position->nama_posisi.')' : ($pic->kode_karyawan ? '('.$pic->kode_karyawan.')' : '') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-card-text text-primary me-1"></i>Instruksi Khusus / Catatan Pengerjaan
                                    </label>
                                    <textarea name="keterangan" id="inp_keterangan_spk" rows="2"
                                              class="form-control spk-input" disabled
                                              placeholder="Catatan khusus pemotongan, warna benang, bordir, kemasan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 2: PILIH TAHAPAN PRODUKSI (CHECKLIST INTERAKTIF)   --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-3 mb-sm-4">
                        <div class="spk-card-header border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main fs-6">
                                    <i class="bi bi-diagram-3-fill me-2 text-primary"></i>Pilih Tahapan Proses Produksi <span class="text-danger">*</span>
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Centang tahapan yang berlaku untuk SPK ini. Urutan otomatis muncul saat dicentang.</small>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 gap-sm-2 ms-auto">
                                <button type="button" id="btnPilihSemuaTahapan" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 px-sm-3 py-1" style="font-size:12px;" onclick="pilihSemuaTahapan()" disabled>
                                    <i class="bi bi-check-all me-1"></i> Pilih Semua
                                </button>
                                <button type="button" id="btnHapusSemuaTahapan" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 px-sm-3 py-1" style="font-size:12px;" onclick="hapusSemuaTahapan()" disabled>
                                    <i class="bi bi-x me-1"></i> Batal Semua
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3 p-sm-4">
                            {{-- BANNER BUKTI PENGUNCIAN BOM --}}
                            <div id="lockBannerTahapan" class="alert alert-warning border-0 rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25) !important;">
                                <i class="bi bi-lock-fill text-warning fs-5 flex-shrink-0"></i>
                                <div>
                                    <strong class="text-warning d-block">Form Terkunci!</strong>
                                    <span class="spk-text-muted">Silakan <strong>pilih Resep BOM</strong> di atas terlebih dahulu untuk mengaktifkan pilihan tahapan dan rincian SPK.</span>
                                </div>
                            </div>

                            {{-- GRID TAHAPAN CARDS (MAX 3 BARIS DENGAN SCROLL) --}}
                            <div class="tahapan-scroll-wrapper custom-scrollbar">
                                <div id="gridTahapanSpk" class="row g-3 pe-none opacity-50">
                                    <div class="col-12 text-center py-4 spk-text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Memuat daftar tahapan...
                                    </div>
                                </div>
                            </div>

                            {{-- PREVIEW URUTAN YANG DIPILIH --}}
                            <div id="previewTahapanDipilih" class="mt-4 d-none">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="fw-bold spk-text-main small">Urutan Tahapan yang Akan Digunakan:</span>
                                </div>
                                <div id="listTahapanDipilih" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 3: LIVE CHECKLIST KEBUTUHAN BAHAN BAKU             --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-4 overflow-hidden">
                        <div class="spk-card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main">
                                    <i class="bi bi-stack me-2 text-primary"></i> Analisis Kebutuhan Bahan Baku & Stok Gudang
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Kalkulasi otomatis dari resep BOM yang dipilih</small>
                                <small class="spk-text-muted d-block d-sm-none mt-0.5"><i class="bi bi-arrows-left-right text-primary me-1"></i>Geser tabel ke samping jika terpotong</small>
                            </div>
                            <div id="badgeStatusStokKeseluruhan" class="d-none">
                                <span class="badge stock-pill-sufficient rounded-pill px-3 py-1.5 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Semua Stok Cukup
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 380px; overflow-x: auto; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-hover align-middle mb-0 text-nowrap spk-table" id="tblSpkMaterials" style="min-width: 760px;">
                                    <thead>
                                        <tr class="small text-uppercase fw-bold">
                                            <th class="text-center sticky-top bg-body" style="width:45px;">No</th>
                                            <th class="sticky-top bg-body">Bahan Baku</th>
                                            <th class="text-center sticky-top bg-body">Kebutuhan/Unit</th>
                                            <th class="text-center sticky-top bg-body">Waste %</th>
                                            <th class="text-center sticky-top bg-body" title="Rumus: (Target SPK ÷ Target BOM) × Qty Resep × (1 + Waste %)">
                                                Total Kebutuhan <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-center sticky-top bg-body">Stok Gudang</th>
                                            <th class="text-center sticky-top bg-body" title="Status ketersediaan stok fisik gudang">
                                                Status <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-end sticky-top bg-body" title="HPP acuan dari master barang / resep BOM">
                                                HPP/Satuan <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-end sticky-top bg-body" title="Rumus: Total Kebutuhan × HPP / Satuan">
                                                Est. Subtotal <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodySpkMaterials">
                                        <tr>
                                            <td colspan="9" class="text-center py-5 spk-text-muted">
                                                <i class="bi bi-arrow-up-circle fs-3 d-block text-secondary mb-2"></i>
                                                Silakan pilih Resep BOM terlebih dahulu.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- PANDUAN RUMUS KALKULASI OTOMATIS SPK --}}
                    <div class="p-2.5 px-3 rounded-3 mb-3 border d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2" style="background: rgba(37,99,235,0.04); border-color: rgba(37,99,235,0.2) !important; font-size: 11.5px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                <i class="bi bi-calculator me-1"></i> Rumus Kalkulasi SPK
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 spk-text-muted small">
                            <span><strong class="text-primary">Kebutuhan Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(37,99,235,0.08); color:var(--spk-accent);">Target SPK × Qty Resep × (1 + Waste%)</code></span>
                            <span><strong class="text-success">Subtotal Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(16,185,129,0.08); color:#059669;">Total Kebutuhan × HPP</code></span>
                            <span><strong class="text-warning">Grand Total SPK:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.08); color:#d97706;">Bahan + TK + Overhead</code></span>
                        </div>
                    </div>

                    {{-- SECTION 4: ESTIMASI BIAYA (2x2 Grid di HP) --}}
                    <div class="row g-2 g-sm-3">
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Total Biaya Bahan</span>
                                <h5 class="fw-bold text-primary mb-0 mt-1 fs-6 fs-sm-5" id="disp_total_bahan">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Σ Subtotal Seluruh Bahan</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Biaya Tenaga Kerja</span>
                                <h5 class="fw-bold spk-text-main mb-0 mt-1 fs-6 fs-sm-5" id="disp_total_tk">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Target SPK × Tarif TK BOM</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Biaya Overhead</span>
                                <h5 class="fw-bold spk-text-main mb-0 mt-1 fs-6 fs-sm-5" id="disp_total_oh">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Target SPK × Tarif OH BOM</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 text-white shadow-xs" style="background: linear-gradient(135deg, #1e40af, #2563eb);">
                                <span class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 10px;">Est. Total Biaya SPK</span>
                                <h5 class="fw-extrabold text-white mb-0 mt-1 fs-6 fs-sm-5" id="disp_grand_total">Rp 0</h5>
                                <span class="text-white-50 d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Bahan + TK + Overhead</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between align-items-stretch align-items-sm-center gap-2 p-3 pb-3 p-sm-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <div class="d-flex align-items-center gap-2 w-100 w-sm-auto order-1 order-sm-2 mb-1 mb-sm-0">
                        <button type="button" id="btnSimpanDraftSpk" class="btn text-white rounded-pill flex-fill w-sm-auto px-3 px-sm-4 py-2.5 fw-bold shadow-sm text-nowrap"
                                onclick="submitFormInputSpk('Draft')" disabled
                                style="background: linear-gradient(135deg, #475569, #334155); border: 1px solid #64748b;">
                            <i class="bi bi-file-earmark-text-fill me-1 text-warning"></i> Simpan Draft
                        </button>
                        <button type="button" id="btnSimpanPendingSpk" class="btn btn-primary rounded-pill flex-fill w-sm-auto px-3 px-sm-4 py-2.5 fw-bold shadow-sm text-nowrap"
                                onclick="submitFormInputSpk('Pending')" disabled
                                style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                            <i class="bi bi-send-fill me-1"></i> Simpan & Kirim<span class="d-none d-sm-inline"> (Pending)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── STYLES KHUSUS UNTUK GRID TAHAPAN ──────────────────────── --}}
<style>
.tahapan-scroll-wrapper {
    max-height: 285px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-top: 12px;
    padding-bottom: 8px;
    padding-right: 6px;
    margin-right: -4px;
    scroll-behavior: smooth;
}
.tahapan-scroll-wrapper::-webkit-scrollbar {
    width: 6px;
}
.tahapan-scroll-wrapper::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 999px;
}
.tahapan-scroll-wrapper::-webkit-scrollbar-thumb {
    background: rgba(37, 99, 235, 0.45);
    border-radius: 999px;
}
.tahapan-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: rgba(37, 99, 235, 0.75);
}

.tahapan-card-item {
    border: 2px solid var(--spk-border-color);
    border-radius: 0.875rem;
    padding: 0.9rem 1rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: var(--spk-card-bg);
    position: relative;
    user-select: none;
}
.tahapan-card-item:hover {
    border-color: var(--spk-accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37,99,235,0.15);
}
.tahapan-card-item.checked {
    border-color: var(--spk-accent) !important;
    background: linear-gradient(135deg, rgba(37,99,235,0.07), rgba(37,99,235,0.03)) !important;
    box-shadow: 0 4px 14px rgba(37,99,235,0.2);
}
.tahapan-card-item .urutan-badge {
    position: absolute;
    top: -10px;
    left: 12px;
    width: 26px;
    height: 26px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    box-shadow: 0 3px 8px rgba(37,99,235,0.4);
    z-index: 2;
}
.tahapan-card-item.checked .urutan-badge {
    display: flex;
    animation: popIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
.tahapan-card-item .check-icon {
    position: absolute;
    top: 8px;
    right: 10px;
    width: 20px;
    height: 20px;
    border: 2px solid var(--spk-border-color);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: #fff;
    background: transparent;
    transition: all 0.15s ease;
}
.tahapan-card-item.checked .check-icon {
    background: #2563eb;
    border-color: #2563eb;
}
.tahapan-preview-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, rgba(37,99,235,0.1), rgba(37,99,235,0.06));
    border: 1px solid rgba(37,99,235,0.25);
    border-radius: 2rem;
    padding: 0.3rem 0.75rem;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--spk-text-main);
    animation: chipIn 0.2s ease;
}
@keyframes chipIn {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}
.tahapan-preview-chip .chip-num {
    background: #2563eb;
    color: #fff;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 800;
    flex-shrink: 0;
}
</style>

@push('scripts')
<script>
    let currentLoadedBomMaterials = [];
    let hitungBahanSpkDebounce   = null;

    // State tahapan yang dipilih: [{nama_tahapan, urutan, cardIndex}]
    let selectedTahapans = [];
    let allMasterTahapans = [];

    // ── Function Pengunci State Form SPK ─────────────────────────
    function setFormSpkDisabledState(isDisabled) {
        // 1. Lock/Unlock Form Fields
        $('#inp_jumlah_rencana, #inp_tanggal_spk, #inp_target_selesai, #inp_tipe_spk, #inp_prioritas, #inp_penanggung_jawab_id, #inp_keterangan_spk').prop('disabled', isDisabled);

        // 2. Lock/Unlock Tahapan Section
        $('#btnPilihSemuaTahapan, #btnHapusSemuaTahapan').prop('disabled', isDisabled);
        if (isDisabled) {
            $('#gridTahapanSpk').addClass('pe-none opacity-50');
            $('#lockBannerTahapan').removeClass('d-none');
        } else {
            $('#gridTahapanSpk').removeClass('pe-none opacity-50');
            $('#lockBannerTahapan').addClass('d-none');
        }

        // 3. Lock/Unlock Submit Buttons
        $('#btnSimpanDraftSpk, #btnSimpanPendingSpk').prop('disabled', isDisabled);
    }

    // ── Buka Modal & Init ────────────────────────────────────────
    function bukaModalTambahSpk() {
        $('#formInputSpk')[0].reset();
        $('#inp_bom_id').val('');
        $('#inp_barang_jadi_id').val('');
        $('#inp_satuan_id').val('');
        $('#inp_bom_name_display').val('');
        $('#inp_barang_jadi_display').val('');
        $('#inp_jumlah_rencana').val('');
        $('#label_satuan_spk').text('Satuan');
        currentLoadedBomMaterials = [];
        selectedTahapans = [];

        renderMaterialEmpty();
        $('#badgeStatusStokKeseluruhan').addClass('d-none');
        resetBiayaDisplay();
        generateNomorSpkInput();

        // Kunci form di awal sampai BOM dipilih
        setFormSpkDisabledState(true);

        // Load tahapan dari master
        loadMasterTahapansForSpk();

        $('#modalTambahSpk').modal('show');
    }

    // ── Load Tahapan dari Master ─────────────────────────────────
    function loadMasterTahapansForSpk() {
        $('#gridTahapanSpk').html(`
            <div class="col-12 text-center py-4 spk-text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Memuat daftar tahapan...
            </div>
        `);

        $.ajax({
            url: '{{ route("produksi.master-tahapan.all-active") }}',
            method: 'GET',
            success: function(res) {
                allMasterTahapans = res.tahapans || [];
                renderTahapanGrid(allMasterTahapans);
            },
            error: function() {
                $('#gridTahapanSpk').html(`
                    <div class="col-12 text-center py-3 text-danger small">
                        <i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat tahapan.
                    </div>
                `);
            }
        });
    }

    // ── Render Grid Cards Tahapan ────────────────────────────────
    function renderTahapanGrid(tahapans) {
        if (!tahapans || tahapans.length === 0) {
            $('#gridTahapanSpk').html(`<div class="col-12 text-center py-3 spk-text-muted small">Belum ada master tahapan. Silakan konfigurasi di menu Master Tahapan.</div>`);
            return;
        }

        let html = '';
        tahapans.forEach(function(t, idx) {
            const tipeLabel = t.tipe_spk ? `<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2" style="font-size:10px;border-color:var(--spk-border-color)!important;">${t.tipe_spk}</span>` : '';
            const fotoThumb = t.foto_url 
                ? `<img src="${t.foto_url}" class="rounded-2 me-2 border shadow-xs flex-shrink-0" style="width:38px;height:38px;object-fit:cover;">` 
                : `<div class="rounded-2 me-2 d-flex align-items-center justify-content-center border flex-shrink-0" style="width:38px;height:38px;background:rgba(255,255,255,0.04);"><i class="bi bi-diagram-3 text-primary opacity-50"></i></div>`;

            html += `
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="tahapan-card-item" id="tahapanCard_${idx}" onclick="toggleTahapan(${idx})">
                        <div class="urutan-badge" id="urtBadge_${idx}">?</div>
                        <div class="check-icon" id="chkIcon_${idx}">
                            <i class="bi bi-check2"></i>
                        </div>
                        <div class="d-flex align-items-center mb-1 pe-4">
                            ${fotoThumb}
                            <div class="fw-bold spk-text-main text-truncate" style="font-size:13px;" title="${t.nama_tahapan}">${t.nama_tahapan}</div>
                        </div>
                        <div class="mt-1 ps-1">${tipeLabel}</div>
                    </div>
                </div>
            `;
        });

        $('#gridTahapanSpk').html(html);
        updatePreviewChips();
    }

    // ── Toggle Centang Tahapan ───────────────────────────────────
    function toggleTahapan(idx) {
        if (!$('#inp_bom_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Resep BOM Belum Dipilih',
                text: 'Silakan pilih Resep BOM terlebih dahulu sebelum memilih tahapan produksi.',
                confirmButtonText: 'Cari BOM'
            }).then(() => bukaModalLookupBomSpk());
            return;
        }

        const t = allMasterTahapans[idx];
        const card = document.getElementById('tahapanCard_' + idx);
        const existing = selectedTahapans.findIndex(s => s.cardIndex === idx);

        if (existing !== -1) {
            // Uncheck
            selectedTahapans.splice(existing, 1);
            // Re-number sisanya
            selectedTahapans.forEach((s, i) => { s.urutan = i + 1; });
            card.classList.remove('checked');
        } else {
            // Check → assign urutan selanjutnya
            const urutanBaru = selectedTahapans.length + 1;
            selectedTahapans.push({
                nama_tahapan: t.nama_tahapan,
                urutan: urutanBaru,
                cardIndex: idx
            });
            card.classList.add('checked');
        }

        // Update semua badge urutan di cards
        refreshAllBadges();
        updatePreviewChips();
    }

    // ── Refresh Badge Urutan di Semua Card ──────────────────────
    function refreshAllBadges() {
        // Reset semua
        allMasterTahapans.forEach(function(_, idx) {
            const badge = document.getElementById('urtBadge_' + idx);
            const card  = document.getElementById('tahapanCard_' + idx);
            if (badge) badge.textContent = '?';
            if (card)  card.classList.remove('checked');
        });

        // Pasang urutan yang benar
        selectedTahapans.forEach(function(s) {
            const badge = document.getElementById('urtBadge_' + s.cardIndex);
            const card  = document.getElementById('tahapanCard_' + s.cardIndex);
            if (badge) badge.textContent = s.urutan;
            if (card)  card.classList.add('checked');
        });
    }

    // ── Preview Chips di Bawah Grid ─────────────────────────────
    function updatePreviewChips() {
        const preview = $('#previewTahapanDipilih');
        const list    = $('#listTahapanDipilih');

        if (selectedTahapans.length === 0) {
            preview.addClass('d-none');
            return;
        }

        preview.removeClass('d-none');
        let html = '';
        [...selectedTahapans].sort((a, b) => a.urutan - b.urutan).forEach(function(s) {
            html += `
                <div class="tahapan-preview-chip">
                    <span class="chip-num">${s.urutan}</span>
                    ${s.nama_tahapan}
                </div>
            `;
        });
        list.html(html);
    }

    // ── Pilih Semua / Batal Semua ────────────────────────────────
    function pilihSemuaTahapan() {
        if (!$('#inp_bom_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Resep BOM Belum Dipilih',
                text: 'Silakan pilih Resep BOM terlebih dahulu.',
                confirmButtonText: 'Cari BOM'
            }).then(() => bukaModalLookupBomSpk());
            return;
        }

        selectedTahapans = [];
        allMasterTahapans.forEach(function(t, idx) {
            selectedTahapans.push({
                nama_tahapan: t.nama_tahapan,
                urutan: idx + 1,
                cardIndex: idx
            });
        });
        refreshAllBadges();
        updatePreviewChips();
    }

    function hapusSemuaTahapan() {
        selectedTahapans = [];
        refreshAllBadges();
        updatePreviewChips();
        $('#previewTahapanDipilih').addClass('d-none');
    }

    // ── BOM & Material Helpers ───────────────────────────────────
    function generateNomorSpkInput() {
        $.get('{{ route("produksi.spk.generate-kode") }}', function(res) {
            $('#inp_nomor_spk').val(res.nomor_spk);
        });
    }

    function toggleAutoNumberSpk(isChecked) {
        $('#inp_nomor_spk').prop('readonly', isChecked).toggleClass('spk-readonly', isChecked);
        if (isChecked) generateNomorSpkInput();
    }

    function pilihBomSpk(id, kode, nama, bjNama, bjKode, bjId, satId, satNama, tipeBom, tenderObj) {
        $('#modalLookupBomSpk').modal('hide');
        $('#inp_bom_id').val(id);
        $('#inp_barang_jadi_id').val(bjId);
        $('#inp_satuan_id').val(satId);
        $('#inp_bom_name_display').val(`[${kode}] ${nama}`);
        $('#inp_barang_jadi_display').val(`[${bjKode}] ${bjNama}`);
        
        // 1. Auto-sync Satuan (Pcs/Set/Meter/Yard/dll) dari BOM
        $('#label_satuan_spk').text(satNama || 'Pcs');

        // 2. Auto-sync Tender & Tipe SPK jika BOM terhubung ke Tender
        if (tenderObj && (tenderObj.nama_tender || tenderObj.nomor_kontrak)) {
            const tenderTxt = (tenderObj.nomor_kontrak ? `[${tenderObj.nomor_kontrak}] ` : '') + tenderObj.nama_tender + (tenderObj.nama_customer ? ` (${tenderObj.nama_customer})` : '');
            $('#lbl_tender_name').text(tenderTxt);
            $('#disp_tender_info').removeClass('d-none');
            
            // Auto-select Tipe SPK ke Make to Order jika ada Tender
            $('#inp_tipe_spk').val('Make to Order');
        } else {
            $('#disp_tender_info').addClass('d-none');
            $('#lbl_tender_name').text('');
            
            // Auto-sync Tipe SPK biasa dari BOM jika tidak ada tender
            if (tipeBom) {
                let tipeVal = tipeBom;
                if (tipeVal === 'Stok' || tipeVal === 'Stok (Make to Stock)') tipeVal = 'Make to Stock';
                if (tipeVal === 'Tender / Project' || tipeVal === 'Tender') tipeVal = 'Make to Order';
                $('#inp_tipe_spk').val(tipeVal);
            }
        }

        // BUKA KUNCI FORM & TAHAPAN KARENA BOM SUDAH DIPILIH
        setFormSpkDisabledState(false);

        hitungUlangBahanSpk();
    }

    function renderMaterialEmpty() {
        $('#tbodySpkMaterials').html(`
            <tr>
                <td colspan="8" class="text-center py-5 spk-text-muted">
                    <i class="bi bi-arrow-up-circle fs-3 d-block text-secondary mb-2"></i>
                    Silakan pilih Resep BOM terlebih dahulu.
                </td>
            </tr>
        `);
    }

    function resetBiayaDisplay() {
        ['disp_total_bahan','disp_total_tk','disp_total_oh','disp_grand_total'].forEach(id => $(`#${id}`).text('Rp 0'));
    }

    function hitungUlangBahanSpk() {
        const bomId = $('#inp_bom_id').val();
        if (!bomId) return;
        clearTimeout(hitungBahanSpkDebounce);
        hitungBahanSpkDebounce = setTimeout(function() {
            const targetQty = parseFloat($('#inp_jumlah_rencana').val()) || 1;
            $('#tbodySpkMaterials').html(`
                <tr><td colspan="8" class="text-center py-4 spk-text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Menghitung kebutuhan bahan...
                </td></tr>
            `);
            $.ajax({
                url: `/produksi/spk/bom/${bomId}/materials`,
                method: 'GET',
                data: { target_qty: targetQty },
                success: function(res) {
                    currentLoadedBomMaterials = res.materials || [];
                    renderMaterialChecklist(res);
                },
                error: function() {
                    $('#tbodySpkMaterials').html(`<tr><td colspan="8" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat bahan.</td></tr>`);
                }
            });
        }, 250);
    }

    function renderMaterialChecklist(data) {
        const mats = data.materials || [];
        if (!mats.length) {
            $('#tbodySpkMaterials').html(`<tr><td colspan="9" class="text-center py-4 spk-text-muted">Tidak ada rincian bahan.</td></tr>`);
            return;
        }
        let allOk = true;
        let html = '';
        mats.forEach((m, i) => {
            if (!m.is_sufficient) allOk = false;
            const satNama = m.satuan_nama || m.nama_satuan || (m.satuan ? m.satuan.nama_satuan : (m.barang_mentah?.satuan?.nama_satuan || 'Pcs'));
            const needFmt = Number(m.total_kebutuhan).toLocaleString('id-ID', {maximumFractionDigits:4});
            const stokFmt = Number(m.stok_aktual).toLocaleString('id-ID', {maximumFractionDigits:4});
            const hppFmt  = 'Rp ' + Number(m.estimasi_harga_satuan || 0).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            const subtot  = 'Rp ' + Number(m.estimasi_subtotal).toLocaleString('id-ID', {minimumFractionDigits:2});
            const badge   = m.is_sufficient
                ? `<span class="badge stock-pill-sufficient rounded-pill px-2 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Cukup</span>`
                : `<span class="badge stock-pill-deficit rounded-pill px-2 py-1 fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Kurang ${Number(m.deficit || 0).toLocaleString('id-ID', {maximumFractionDigits:4})} ${satNama}</span>`;
            html += `
                <tr class="${m.is_sufficient ? '' : 'table-danger'}">
                    <td class="text-center spk-text-muted">${i+1}</td>
                    <td><div class="fw-semibold spk-text-main">[${m.kode_barang}] ${m.nama_barang}</div></td>
                    <td class="text-center font-monospace">${Number(m.kebutuhan_per_unit).toLocaleString('id-ID',{maximumFractionDigits:4})} ${satNama}</td>
                    <td class="text-center spk-text-muted">${parseFloat(m.waste_percent || 0)}%</td>
                    <td class="text-center fw-bold font-monospace text-primary">${needFmt} ${satNama}</td>
                    <td class="text-center font-monospace">${stokFmt} ${satNama}</td>
                    <td class="text-center">${badge}</td>
                    <td class="text-end font-monospace spk-text-muted">${hppFmt}</td>
                    <td class="text-end fw-bold font-monospace text-success">${subtot}</td>
                </tr>
            `;
        });
        $('#tbodySpkMaterials').html(html);
        $('#badgeStatusStokKeseluruhan').removeClass('d-none').html(
            allOk
                ? `<span class="badge stock-pill-sufficient rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Semua Stok Cukup</span>`
                : `<span class="badge stock-pill-deficit rounded-pill px-3 py-1 fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Ada Bahan Kurang</span>`
        );
        const costs = data.estimasi_biaya || {};
        const fmt = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID',{minimumFractionDigits:2});
        $('#disp_total_bahan').text(fmt(costs.total_bahan));
        $('#disp_total_tk').text(fmt(costs.total_tk));
        $('#disp_total_oh').text(fmt(costs.total_overhead));
        $('#disp_grand_total').text(fmt(costs.grand_total));
    }

    // ── Submit SPK ───────────────────────────────────────────────
    function submitFormInputSpk(statusTarget) {
        const bomId = $('#inp_bom_id').val();
        const qty   = parseFloat($('#inp_jumlah_rencana').val()) || 0;

        if (!bomId)   { Swal.fire('Validasi', 'Pilih Resep BOM terlebih dahulu!', 'warning'); return; }
        if (qty <= 0) { Swal.fire('Validasi', 'Target Qty harus lebih dari 0!', 'warning'); return; }
        if (!currentLoadedBomMaterials.length) { Swal.fire('Validasi', 'Rincian bahan baku tidak boleh kosong!', 'warning'); return; }
        if (!selectedTahapans.length) {
            Swal.fire({
                title: 'Tahapan Belum Dipilih',
                html: 'Kamu belum memilih tahapan produksi.<br><small class="text-muted">Sistem akan menggunakan tahapan default jika kamu lanjutkan.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Lanjut Tanpa Pilih',
                cancelButtonText: 'Kembali Pilih Tahapan'
            }).then(r => { if (r.isConfirmed) doSubmitSpk(statusTarget); });
            return;
        }
        doSubmitSpk(statusTarget);
    }

    function doSubmitSpk(statusTarget) {
        const bomId = $('#inp_bom_id').val();
        const targetQty = parseFloat($('#inp_jumlah_rencana').val()) || 0;
        const pjId = $('#inp_penanggung_jawab_id').val();

        if (!bomId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Silakan pilih Resep BOM terlebih dahulu.' });
            return;
        }
        if (targetQty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Target Qty Produksi harus lebih dari 0.' });
            return;
        }
        if (!pjId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Penanggung Jawab (PIC Produksi) wajib diisi!' });
            return;
        }
        if (!selectedTahapans || selectedTahapans.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Tahapan proses produksi wajib dipilih minimal 1 tahapan!' });
            return;
        }

        const $btnD = $('#btnSimpanDraftSpk');
        const $btnP = $('#btnSimpanPendingSpk');
        $btnD.prop('disabled', true);
        $btnP.prop('disabled', true);

        const payload = {
            _token:             '{{ csrf_token() }}',
            status:             statusTarget,
            auto_number:        $('#chk_auto_number_spk').is(':checked'),
            nomor_spk:          $('#inp_nomor_spk').val(),
            bom_id:             $('#inp_bom_id').val(),
            jumlah_rencana:     parseFloat($('#inp_jumlah_rencana').val()),
            tanggal_spk:        $('#inp_tanggal_spk').val(),
            target_selesai:     $('#inp_target_selesai').val(),
            tipe_spk:           $('#inp_tipe_spk').val(),
            prioritas:          $('#inp_prioritas').val(),
            penanggung_jawab_id:$('#inp_penanggung_jawab_id').val() || null,
            keterangan:         $('#inp_keterangan_spk').val(),
            materials: currentLoadedBomMaterials.map(m => ({
                barang_mentah_id:       m.barang_mentah_id,
                satuan_id:              m.satuan_id,
                kebutuhan_per_unit:     m.kebutuhan_per_unit,
                waste_percent:          m.waste_percent,
                total_kebutuhan:        m.total_kebutuhan,
                estimasi_harga_satuan:  m.estimasi_harga_satuan
            })),
            // Kirim tahapan yang dipilih user (urutan sesuai pilihan)
            selected_tahapans: selectedTahapans.length > 0
                ? [...selectedTahapans].sort((a,b) => a.urutan - b.urutan).map(s => ({
                    nama_tahapan: s.nama_tahapan,
                    urutan: s.urutan
                  }))
                : []
        };

        $.ajax({
            url: '{{ route("produksi.spk.store") }}',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res) {
                $('#modalTambahSpk').modal('hide');
                $btnD.prop('disabled', false);
                $btnP.prop('disabled', false);
                Swal.fire({
                    icon: 'success',
                    title: statusTarget === 'Draft' ? 'Draft SPK Tersimpan!' : 'SPK Berhasil Diajukan!',
                    text: res.message || 'SPK berhasil dibuat.',
                    timer: 2200,
                    showConfirmButton: false
                }).then(() => reloadTableSpk());
            },
            error: function(xhr) {
                $btnD.prop('disabled', false);
                $btnP.prop('disabled', false);
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
            }
        });
    }
</script>
@endpush
