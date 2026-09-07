<!-- MODAL TRANSAKSI PENGELUARAN BAHAN BAKU (ULTRA MODERN & CINEMATIC) -->
<div class="modal fade spk-modal-primary" id="modalTambahPengeluaran" tabindex="-1" aria-labelledby="modalTambahPengeluaranLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1070;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-blue py-2.5 py-sm-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto" style="min-width: 0;">
                    <div class="p-1.5 p-sm-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.18);">
                        <i class="bi bi-box-arrow-right fs-5 fs-sm-4"></i>
                    </div>
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-sm-5 text-truncate" id="modalTambahPengeluaranLabel">Pengeluaran Bahan Baku SPK</h5>
                        <small class="text-white-50 d-none d-md-block" id="modalTambahPengeluaranSubtitle">Catat penyerahan bahan mentah dari gudang logistik ke line produksi per SPK.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formTambahPengeluaran" onsubmit="submitTambahPengeluaran(event)" autocomplete="off">
                @csrf
                <input type="hidden" id="inp_pengeluaran_edit_id" name="pengeluaran_edit_id" value="">
                <div class="modal-body spk-modal-body p-2.5 p-sm-4">
                    
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 1: INFORMASI SPK, GUDANG & PETUGAS                  --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-2.5 mb-sm-4">
                        <div class="spk-card-header border-bottom py-2 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main fs-6">
                                    Informasi SPK & Penyerahan
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Pilih SPK aktif dan gudang asal untuk memuat daftar bahan yang dibutuhkan</small>
                            </div>
                        </div>
                        <div class="card-body p-2.5 p-sm-4">
                            <div class="row g-2.5 g-sm-3">
                                {{-- SPK SELECTION (LOOKUP MODAL TRIGGER) --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold spk-text-muted small mb-1">
                                        SPK Produksi (Sedang Berjalan) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupSpk()" style="cursor: pointer;">
                                        <input type="text" id="display_spk_pilihan" class="form-control spk-input spk-readonly border-end-0 cursor-pointer text-truncate"
                                               placeholder="Pilih SPK produksi..." readonly autocomplete="off" style="cursor: pointer;">
                                        <button type="button" class="btn btn-primary px-3 fw-bold flex-shrink-0 text-nowrap" onclick="event.stopPropagation(); bukaModalLookupSpk();" title="Cari SPK">
                                            <i class="bi bi-search me-1"></i> Cari
                                        </button>
                                    </div>
                                    <input type="hidden" id="inp_spk_id" name="spk_id" required>
                                </div>

                                {{-- GUDANG BAHAN (LOOKUP MODAL TRIGGER) --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold spk-text-muted small mb-1">
                                        Gudang Asal Bahan Baku <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupGudang()" style="cursor: pointer;">
                                        <input type="text" id="display_gudang_pilihan" class="form-control spk-input spk-readonly border-end-0 cursor-pointer text-truncate"
                                               placeholder="Pilih gudang asal..." readonly autocomplete="off" style="cursor: pointer;">
                                        <button type="button" class="btn btn-primary px-3 fw-bold flex-shrink-0 text-nowrap" onclick="event.stopPropagation(); bukaModalLookupGudang();" title="Cari Gudang">
                                            <i class="bi bi-search me-1"></i> Cari
                                        </button>
                                    </div>
                                    <input type="hidden" id="inp_gudang_id" name="gudang_id" required>
                                </div>

                                {{-- TANGGAL PENGELUARAN --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold spk-text-muted small mb-1">
                                        Tanggal Penyerahan <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="inp_tanggal_pengeluaran" name="tanggal_pengeluaran" class="form-control spk-input rounded-3" value="{{ date('Y-m-d') }}" autocomplete="off" required>
                                </div>

                                {{-- PENERIMA (OPERATOR / LINE PRODUKSI - INPUT MANUAL MAKS 40 CHARS) --}}
                                <div class="col-12 col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold spk-text-muted small mb-0">
                                            Penerima / Operator Line <span class="text-danger">*</span>
                                        </label>
                                        <span id="penerima_char_counter" class="small text-muted font-monospace" style="font-size: 11px;">0/40</span>
                                    </div>
                                    <input type="text" id="inp_penerima_nama" name="penerima_nama" class="form-control spk-input rounded-3"
                                           maxlength="40" placeholder="Ketik nama operator penerima..." autocomplete="off" oninput="handlePenerimaInput(this)" required>
                                </div>

                                {{-- HIDDEN: PETUGAS GUDANG (OTOMATIS DARI USER LOGIN) --}}
                                <input type="hidden" id="inp_penyerah_nama" name="penyerah_nama" value="{{ auth()->user()->nama_lengkap ?: (auth()->user()->username ?? 'User') }}">

                                {{-- CATATAN (INPUT MAKS 40 CHARS) --}}
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold spk-text-muted small mb-0">
                                            Catatan Pengeluaran Bahan
                                        </label>
                                        <span id="catatan_char_counter" class="small text-muted font-monospace" style="font-size: 11px;">0/40</span>
                                    </div>
                                    <input type="text" id="inp_catatan" name="catatan" class="form-control spk-input rounded-3"
                                           maxlength="40" placeholder="Contoh: Pengambilan bahan kain batch 1..." autocomplete="off" oninput="handleCatatanInput(this)">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 2: ROW BUILDER ITEM BAHAN BAKU                      --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-2">
                        <div class="spk-card-header border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center gap-2">
                            <div style="min-width: 0;">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <h6 class="fw-bold mb-0 spk-text-main fs-6 text-truncate">
                                        Daftar Bahan yang Dikeluarkan
                                    </h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill font-monospace" id="badge_count_items_input" style="font-size: 11px;">0 Item</span>
                                </div>
                                <small class="spk-text-muted d-none d-sm-block">Kuantitas bahan yang diambil dari gudang.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm flex-shrink-0 text-nowrap" id="btnTambahBarisBahan" onclick="tambahBarisPengeluaran()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Bahan
                            </button>
                        </div>
                        
                        {{-- Mobile Swipe Hint --}}
                        <div class="d-block d-md-none bg-primary bg-opacity-10 border-bottom border-primary border-opacity-10 py-1 px-3 text-center">
                            <small class="text-primary fw-medium" style="font-size: 11px;">
                                <i class="bi bi-arrow-left-right me-1"></i> Geser tabel ke kanan untuk rincian & kuantitas
                            </small>
                        </div>

                        <div class="card-body p-0">
                            <div class="spk-table-wrapper rounded-bottom-4" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-hover align-middle mb-0 spk-table" style="min-width: 760px;">
                                    <thead>
                                        <tr class="small text-uppercase fw-bold spk-text-muted">
                                            <th style="min-width: 230px;" class="ps-3 ps-sm-4">BAHAN BAKU DARI SPK <span class="text-danger">*</span></th>
                                            <th class="text-center" style="width: 105px;">TOTAL SPK</th>
                                            <th class="text-center" style="width: 105px;">SDH DIAMBIL</th>
                                            <th class="text-center" style="width: 115px;">SISA KEBUTUHAN</th>
                                            <th class="text-center" style="width: 115px;">STOK GUDANG</th>
                                            <th class="text-center" style="width: 145px;">QTY DIKELUARKAN <span class="text-danger">*</span></th>
                                            <th class="text-center" style="width: 75px;">SATUAN</th>
                                            <th class="text-center pe-3 pe-sm-4" style="width: 45px;"><i class="bi bi-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPengeluaranRows">
                                        <tr>
                                            <td colspan="8" class="text-center py-5 spk-text-muted" id="trEmptyState">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                                                        <i class="bi bi-arrow-up-circle fs-3"></i>
                                                    </div>
                                                    <div class="fw-semibold">Silakan pilih SPK Produksi terlebih dahulu di bagian atas</div>
                                                    <small class="spk-text-muted">Daftar resep bahan baku akan otomatis tersedia untuk dipilih.</small>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER MODAL --}}
                <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 p-3 p-sm-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold w-100 w-sm-auto" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </button>
                    <button type="submit" id="btnSubmitPengeluaran" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-sm-auto" disabled>
                        <i class="bi bi-check-lg me-1"></i> Simpan & Potong Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentSpkMaterials = [];
    let rowCounter = 0;

    function openModalTambahPengeluaran() {
        $('#formTambahPengeluaran')[0].reset();
        $('#inp_pengeluaran_edit_id').val('');
        $('#modalTambahPengeluaranLabel').text('Pengeluaran Bahan Baku SPK');
        $('#modalTambahPengeluaranSubtitle').text('Catat penyerahan bahan mentah dari gudang logistik ke line produksi per SPK.');
        $('#btnSubmitPengeluaran').html('<i class="bi bi-check-lg me-1"></i> Simpan & Potong Stok');

        const loggedInUserName = "{{ auth()->user()->nama_lengkap ?: (auth()->user()->username ?? 'User') }}";
        $('#display_penyerah_nama').val(loggedInUserName);
        $('#inp_penyerah_nama').val(loggedInUserName);
        $('#inp_spk_id').val('');
        $('#display_spk_pilihan').val('').removeClass('fw-bold text-primary');

        @php
            $defaultGudang = $gudangList->first();
        @endphp
        const defGudangId = "{{ $defaultGudang ? $defaultGudang->id : '' }}";
        const defGudangKode = "{{ $defaultGudang ? $defaultGudang->kode_gudang : '' }}";
        const defGudangNama = "{{ $defaultGudang ? addslashes($defaultGudang->nama_gudang) : '' }}";

        if (defGudangId) {
            $('#inp_gudang_id').val(defGudangId);
            $('#display_gudang_pilihan').val(`[${defGudangKode}] ${defGudangNama}`).addClass('fw-bold text-primary');
        } else {
            $('#inp_gudang_id').val('');
            $('#display_gudang_pilihan').val('').removeClass('fw-bold text-primary');
        }

        $('#inp_penerima_nama').val('');
        $('#penerima_char_counter').text('0/40').removeClass('text-danger fw-bold').addClass('text-muted');
        $('#inp_catatan').val('');
        $('#catatan_char_counter').text('0/40').removeClass('text-danger fw-bold').addClass('text-muted');
        currentSpkMaterials = [];
        rowCounter = 0;
        $('#tbodyPengeluaranRows').html(`
            <tr>
                <td colspan="7" class="text-center py-5 spk-text-muted" id="trEmptyState">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                            <i class="bi bi-arrow-up-circle fs-3"></i>
                        </div>
                        <div class="fw-semibold">Silakan pilih SPK Produksi terlebih dahulu di bagian atas</div>
                        <small class="spk-text-muted">Daftar resep bahan baku akan otomatis tersedia untuk dipilih.</small>
                    </div>
                </td>
            </tr>
        `);
        $('#btnSubmitPengeluaran').prop('disabled', true);
        updateRowCount();
        $('#modalTambahPengeluaran').modal('show');
    }

    function openModalEditPengeluaran(pengeluaranId, spkId) {
        $('#formTambahPengeluaran')[0].reset();
        $('#inp_pengeluaran_edit_id').val(pengeluaranId);
        $('#modalTambahPengeluaranLabel').html('<i class="bi bi-pencil-square me-1"></i> Edit Sesi Pengeluaran');
        $('#modalTambahPengeluaranSubtitle').text('Revisi kuantitas bahan, gudang, penerima, atau catatan sesi pengeluaran ini.');
        $('#btnSubmitPengeluaran').html('<i class="bi bi-check-lg me-1"></i> Simpan Perubahan').prop('disabled', true);

        Swal.fire({
            title: 'Memuat Data Sesi...',
            text: 'Mengambil data rincian sesi pengeluaran.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: `/produksi/pengeluaran-bahan/${pengeluaranId}`,
            method: 'GET',
            success: function(res) {
                const p = res.pengeluaran;
                const spk = p.spk || {};
                const bNama = spk.barang_jadi ? `[${spk.barang_jadi.kode_barang}] ${spk.barang_jadi.nama_barang}` : '-';

                $('#inp_spk_id').val(p.spk_id);
                $('#display_spk_pilihan').val(`[${spk.nomor_spk || '-'}] ${bNama}`).addClass('fw-bold text-primary');

                const gKode = p.gudang ? p.gudang.kode_gudang : '-';
                const gNama = p.gudang ? p.gudang.nama_gudang : 'Gudang Utama';
                $('#inp_gudang_id').val(p.gudang_id);
                $('#display_gudang_pilihan').val(`[${gKode}] ${gNama}`).addClass('fw-bold text-primary');

                const tgl = p.tanggal_pengeluaran ? p.tanggal_pengeluaran.substring(0, 10) : '';
                $('#inp_tanggal_pengeluaran').val(tgl);

                let penyerahName = p.penyerah_nama || (p.penyerah ? p.penyerah.nama_lengkap : '') || 'Petugas Gudang';
                $('#inp_penyerah_nama').val(penyerahName);
                $('#display_penyerah_nama').val(penyerahName);

                let penerimaName = p.penerima_nama || (p.penerima ? p.penerima.nama_lengkap : '') || '';
                $('#inp_penerima_nama').val(penerimaName);
                handlePenerimaInput(document.getElementById('inp_penerima_nama'));

                $('#inp_catatan').val(p.catatan || '');
                handleCatatanInput(document.getElementById('inp_catatan'));

                // Fetch real-time materials of SPK for warehouse stock
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/spk-materials/${p.spk_id}`,
                    data: { gudang_id: p.gudang_id },
                    method: 'GET',
                    success: function(mRes) {
                        Swal.close();
                        currentSpkMaterials = mRes.materials || [];
                        $('#tbodyPengeluaranRows').empty();
                        rowCounter = 0;

                        const details = p.details || [];
                        if (details.length === 0) {
                            $('#tbodyPengeluaranRows').html(`
                                <tr>
                                    <td colspan="7" class="text-center py-5 spk-text-muted">
                                        Tidak ada rincian bahan pada sesi ini.
                                    </td>
                                </tr>
                            `);
                            $('#btnSubmitPengeluaran').prop('disabled', true);
                            return;
                        }

                        details.forEach(d => {
                            const idx = rowCounter++;
                            const bm = d.barang_mentah || {};
                            const sat = d.satuan ? d.satuan.nama_satuan : (bm.satuan ? bm.satuan.nama_satuan : 'Unit');
                            const totalSpk = Number(d.qty_total_spk || d.qty_kebutuhan_spk || 0);
                            const qtyTaken = Number(d.qty_diambil_sekarang || 0);

                            const foundMat = currentSpkMaterials.find(m => Number(m.spk_material_id) === Number(d.spk_material_id));
                            const currentAvail = foundMat ? Number(foundMat.stok_tersedia_gudang || 0) : 0;
                            const effectiveStock = currentAvail + qtyTaken;

                            const sdhDiambilOther = foundMat ? Math.max(0, Number(foundMat.qty_sudah_diambil || 0) - qtyTaken) : Math.max(0, totalSpk - Number(d.qty_sisa_kebutuhan || 0));
                            const sisaKebutuhan = foundMat ? (Number(foundMat.qty_sisa_kebutuhan || 0) + qtyTaken) : Math.max(0, totalSpk - sdhDiambilOther);

                            const rowHtml = `
                                <tr id="row_pengeluaran_${idx}" class="row-pengeluaran">
                                    <td class="ps-3 ps-sm-4">
                                        <div class="input-group input-group-sm shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupBahan(${idx})" style="cursor: pointer;" title="Klik untuk memilih bahan">
                                            <input type="text" id="txt_mat_display_${idx}" class="form-control spk-input spk-readonly border-end-0 cursor-pointer text-truncate fw-bold text-primary"
                                                   value="[${bm.kode_barang || '-'}] ${bm.nama_barang || '-'}" readonly autocomplete="off" style="cursor: pointer;">
                                            <button type="button" class="btn btn-outline-primary px-2.5 flex-shrink-0" onclick="event.stopPropagation(); bukaModalLookupBahan(${idx});" title="Cari Bahan">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="spk_mat_id_${idx}" name="items[${idx}][spk_material_id]" class="inp-spk-mat-id" value="${d.spk_material_id}">
                                        <input type="hidden" id="barang_mentah_id_${idx}" name="items[${idx}][barang_mentah_id]" class="inp-barang-mentah-id" value="${d.barang_mentah_id}">
                                        <input type="hidden" id="stok_gudang_${idx}" class="inp-stok-gudang" value="${effectiveStock}">
                                    </td>
                                    <td class="text-center font-monospace small">
                                        <span id="lbl_total_spk_${idx}" class="spk-text-main">${totalSpk.toLocaleString('id-ID', {maximumFractionDigits: 4})}</span>
                                    </td>
                                    <td class="text-center font-monospace small">
                                        <span id="lbl_sdh_diambil_${idx}" class="spk-text-muted font-monospace">${sdhDiambilOther.toLocaleString('id-ID', {maximumFractionDigits: 4})}</span>
                                    </td>
                                    <td class="text-center font-monospace small">
                                        <span id="lbl_sisa_spk_${idx}" class="fw-bold ${sisaKebutuhan > 0 ? 'text-warning-vibrant' : 'text-success-vibrant'}">${sisaKebutuhan.toLocaleString('id-ID', {maximumFractionDigits: 4})}</span>
                                    </td>
                                    <td class="text-center font-monospace small" id="lbl_stok_gudang_${idx}">
                                        <span class="badge ${effectiveStock > 0 ? 'spk-badge-green' : 'spk-badge-muted'} font-monospace px-2 py-0.5" style="font-size: 11px;">
                                            ${effectiveStock.toLocaleString('id-ID', {maximumFractionDigits: 4})} ${sat}
                                        </span>
                                    </td>
                                    <td style="width: 140px;">
                                        <div class="d-flex flex-column align-items-end">
                                            <div class="d-flex align-items-center gap-1 w-100 justify-content-end">
                                                <input type="number" id="inp_qty_taken_${idx}" name="items[${idx}][qty_diambil_sekarang]"
                                                       class="form-control form-control-sm text-end font-monospace fw-bold spk-input inp-qty-taken fs-6"
                                                       value="${qtyTaken}" step="any" min="0.0001" autocomplete="off"
                                                       placeholder="0"
                                                       oninput="handleQtyTakenInput(this, ${idx})"
                                                       onchange="handleQtyTakenChange(this, ${idx})" required>
                                                <button type="button" id="btn_max_${idx}" class="btn btn-sm btn-outline-primary px-1.5 py-0.5 rounded-2 font-monospace flex-shrink-0 ${sisaKebutuhan > 0 ? '' : 'd-none'}"
                                                        style="font-size: 10px; font-weight: 700;" title="Isi maksimal">
                                                    MAX
                                                </button>
                                            </div>
                                            <div id="feedback_stock_${idx}" class="text-danger small font-monospace mt-1 d-none" style="font-size: 10.5px;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span id="lbl_satuan_${idx}" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                            ${sat}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3 pe-sm-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-1.5"
                                                onclick="hapusBarisPengeluaran(${idx})" title="Hapus Baris">
                                            <i class="bi bi-trash3 fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            $('#tbodyPengeluaranRows').append(rowHtml);

                            if (sisaKebutuhan > 0) {
                                $(`#btn_max_${idx}`).off('click').on('click', function() {
                                    const takeVal = Math.min(sisaKebutuhan, effectiveStock);
                                    $(`#inp_qty_taken_${idx}`).val(takeVal).trigger('input').focus();
                                });
                            }
                        });

                        $('#btnSubmitPengeluaran').prop('disabled', false);
                        updateRowCount();
                        $('#modalTambahPengeluaran').modal('show');
                    },
                    error: function(mErr) {
                        Swal.close();
                        Swal.fire('Gagal!', 'Gagal memuat daftar stok bahan gudang.', 'error');
                    }
                });
            },
            error: function(err) {
                Swal.close();
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memuat detail sesi pengeluaran.', 'error');
            }
        });
    }

    function handlePenerimaInput(el) {
        const len = el.value.length;
        const max = 40;
        $('#penerima_char_counter').text(`${len}/${max}`);
        
        if (len >= max) {
            $('#penerima_char_counter').addClass('text-danger fw-bold').removeClass('text-muted');
            if (!el.dataset.alertShown) {
                el.dataset.alertShown = "true";
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Karakter Terpenuhi!',
                    text: 'Nama penerima / operator line maksimal 40 karakter.',
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Mengerti'
                });
            }
        } else {
            el.dataset.alertShown = "";
            $('#penerima_char_counter').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    }

    function handleCatatanInput(el) {
        const len = el.value.length;
        const max = 40;
        $('#catatan_char_counter').text(`${len}/${max}`);
        
        if (len >= max) {
            $('#catatan_char_counter').addClass('text-danger fw-bold').removeClass('text-muted');
            if (!el.dataset.alertShown) {
                el.dataset.alertShown = "true";
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Karakter Terpenuhi!',
                    text: 'Catatan pengeluaran bahan maksimal 40 karakter.',
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Mengerti'
                });
            }
        } else {
            el.dataset.alertShown = "";
            $('#catatan_char_counter').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    }

    function handleQtyTakenInput(el, idx) {
        const qty = parseFloat(el.value) || 0;
        const stok = parseFloat($(`#stok_gudang_${idx}`).val()) || 0;
        const matId = $(`#spk_mat_id_${idx}`).val();
        
        if (matId && stok >= 0 && qty > stok) {
            $(el).addClass('is-invalid border-danger text-danger');
            $(`#feedback_stock_${idx}`).removeClass('d-none').text(`Melebihi stok gudang (${stok})`);
        } else {
            $(el).removeClass('is-invalid border-danger text-danger');
            $(`#feedback_stock_${idx}`).addClass('d-none');
        }
    }

    function handleQtyTakenChange(el, idx) {
        const qty = parseFloat(el.value) || 0;
        const stok = parseFloat($(`#stok_gudang_${idx}`).val()) || 0;
        const matId = $(`#spk_mat_id_${idx}`).val();
        const matName = $(`#txt_mat_display_${idx}`).val() || 'Bahan Baku';
        const satName = $(`#lbl_satuan_${idx}`).text() || 'Unit';

        if (matId && qty > stok) {
            Swal.fire({
                icon: 'warning',
                title: 'Kuantitas Melebihi Stok Gudang!',
                html: `Kuantitas yang Anda masukkan untuk <b>${matName}</b> (<b>${qty.toLocaleString('id-ID', {maximumFractionDigits: 4})} ${satName}</b>) melebihi stok fisik yang tersedia di gudang saat ini (<b>${stok.toLocaleString('id-ID', {maximumFractionDigits: 4})} ${satName}</b>).<br><br><small class="text-muted">Kuantitas otomatis disesuaikan ke stok maksimal yang tersedia.</small>`,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            }).then(() => {
                el.value = stok > 0 ? stok : '';
                handleQtyTakenInput(el, idx);
                $(el).focus();
            });
        }
    }

    function pilihGudangFromLookup(gudangId, kodeGudang, namaGudang) {
        $('#inp_gudang_id').val(gudangId);
        const displayName = kodeGudang ? `[${kodeGudang}] ${namaGudang}` : namaGudang;
        $('#display_gudang_pilihan').val(displayName).addClass('fw-bold text-primary');
        $('#modalLookupGudang').modal('hide');

        // If SPK is already selected, refresh stock for this warehouse
        const spkId = $('#inp_spk_id').val();
        if (spkId) {
            $.ajax({
                url: `/produksi/pengeluaran-bahan/spk-materials/${spkId}`,
                data: { gudang_id: gudangId },
                method: 'GET',
                success: function(res) {
                    currentSpkMaterials = res.materials || [];
                    // Update stock on all active rows
                    $('.row-pengeluaran').each(function() {
                        const spkMatId = $(this).find('.inp-spk-mat-id').val();
                        if (spkMatId) {
                            const m = currentSpkMaterials.find(item => Number(item.spk_material_id) === Number(spkMatId));
                            if (m) {
                                const idxStr = $(this).attr('id').replace('row_pengeluaran_', '');
                                const stok = Number(m.stok_tersedia_gudang || 0);
                                $(`#stok_gudang_${idxStr}`).val(stok);
                                $(`#lbl_stok_gudang_${idxStr}`).html(stok > 0 
                                    ? `<span class="badge spk-badge-green font-monospace px-2 py-0.5" style="font-size: 11px;">${stok.toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}</span>`
                                    : `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 font-monospace px-2 py-0.5" style="font-size: 11px;"><i class="bi bi-x-circle me-1"></i>0 (Kosong)</span>`
                                );
                                const qtyEl = document.getElementById(`inp_qty_taken_${idxStr}`);
                                if (qtyEl) handleQtyTakenInput(qtyEl, idxStr);
                            }
                        }
                    });
                }
            });
        }
    }

    function pilihSpkFromLookup(spkId, nomorSpk, namaBarang, status) {
        $('#inp_spk_id').val(spkId);
        $('#display_spk_pilihan').val(`[${nomorSpk}] ${namaBarang}`).addClass('fw-bold text-primary');
        $('#modalLookupSpk').modal('hide');

        const gudangId = $('#inp_gudang_id').val();

        // Load materials of SPK
        $('#tbodyPengeluaranRows').html(`
            <tr>
                <td colspan="8" class="text-center py-5 spk-text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Memuat rincian bahan baku SPK...
                </td>
            </tr>
        `);

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk-materials/${spkId}`,
            data: { gudang_id: gudangId },
            method: 'GET',
            success: function(res) {
                currentSpkMaterials = res.materials || [];
                $('#tbodyPengeluaranRows').empty();
                rowCounter = 0;

                if (currentSpkMaterials.length === 0) {
                    $('#tbodyPengeluaranRows').html(`
                        <tr>
                            <td colspan="8" class="text-center py-5 spk-text-muted">
                                <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning d-inline-block mb-2">
                                    <i class="bi bi-exclamation-triangle fs-3"></i>
                                </div>
                                <div class="fw-semibold">SPK ini belum memiliki rincian bahan baku (BOM)</div>
                                <small class="spk-text-muted">Silakan periksa resep BOM pada SPK ini terlebih dahulu.</small>
                            </td>
                        </tr>
                    `);
                    $('#btnSubmitPengeluaran').prop('disabled', true);
                    updateRowCount();
                    return;
                }

                $('#btnSubmitPengeluaran').prop('disabled', false);

                // Tambahkan 1 baris awal dan buka lookup agar pengguna memilih bahan yang ingin dikeluarkan
                tambahBarisPengeluaran(true);
            },
            error: function(err) {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memuat rincian bahan SPK.', 'error');
                $('#tbodyPengeluaranRows').html(`
                    <tr>
                        <td colspan="8" class="text-center py-4 text-danger">
                            <i class="bi bi-x-circle me-1"></i> Gagal memuat rincian bahan SPK.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function tambahBarisPengeluaran(autoOpenLookup = true) {
        const spkId = $('#inp_spk_id').val();
        if (!spkId) {
            Swal.fire({
                icon: 'info',
                title: 'Pilih SPK Terlebih Dahulu',
                text: 'Silakan pilih SPK Produksi di bagian atas sebelum menambah bahan.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Pilih SPK Sekarang'
            }).then((result) => {
                if (result.isConfirmed) {
                    bukaModalLookupSpk();
                }
            });
            return;
        }

        if (!currentSpkMaterials || currentSpkMaterials.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Bahan SPK',
                text: 'SPK yang dipilih belum memiliki daftar bahan baku (BOM).',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        // Hapus empty state jika ada
        if ($('#trEmptyState').length > 0 || $('.row-pengeluaran').length === 0) {
            $('#tbodyPengeluaranRows').empty();
        }

        const idx = rowCounter++;
        const rowHtml = `
            <tr id="row_pengeluaran_${idx}" class="row-pengeluaran">
                <td class="ps-3 ps-sm-4">
                    <div class="input-group input-group-sm shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupBahan(${idx})" style="cursor: pointer;" title="Klik untuk memilih bahan">
                        <input type="text" id="txt_mat_display_${idx}" class="form-control spk-input spk-readonly border-end-0 cursor-pointer text-truncate"
                               placeholder="Pilih bahan baku..." readonly autocomplete="off" style="cursor: pointer;">
                        <button type="button" class="btn btn-primary px-2.5" onclick="event.stopPropagation(); bukaModalLookupBahan(${idx});" title="Cari Bahan">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <input type="hidden" id="spk_mat_id_${idx}" name="items[${idx}][spk_material_id]" class="inp-spk-mat-id">
                    <input type="hidden" id="barang_mentah_id_${idx}" name="items[${idx}][barang_mentah_id]">
                    <input type="hidden" id="stok_gudang_${idx}" class="inp-stok-gudang" value="0">
                </td>
                <td class="text-center font-monospace small spk-text-muted" id="lbl_total_spk_${idx}">-</td>
                <td class="text-center font-monospace small spk-text-muted" id="lbl_sdh_diambil_${idx}">-</td>
                <td class="text-center font-monospace small spk-text-muted" id="lbl_sisa_spk_${idx}">-</td>
                <td class="text-center" id="lbl_stok_gudang_${idx}">
                    <span class="badge spk-badge-muted font-monospace px-2 py-0.5" style="font-size: 11px;">-</span>
                </td>
                <td>
                    <div class="d-flex flex-column align-items-end">
                        <div class="d-flex align-items-center gap-1 w-100 justify-content-end">
                            <input type="number" step="any" min="0.0001"
                                   id="inp_qty_taken_${idx}" name="items[${idx}][qty_diambil_sekarang]"
                                   class="form-control form-control-sm text-end font-monospace fw-bold spk-input inp-qty-taken"
                                   placeholder="0"
                                   value=""
                                   oninput="handleQtyTakenInput(this, ${idx})"
                                   onchange="handleQtyTakenChange(this, ${idx})"
                                   onblur="handleQtyTakenChange(this, ${idx})"
                                   autocomplete="off" disabled required>
                            <button type="button" id="btn_max_${idx}" class="btn btn-sm btn-outline-primary px-1.5 py-0.5 rounded-2 font-monospace flex-shrink-0 d-none"
                                    style="font-size: 10px; font-weight: 700;" title="Isi semua sisa kebutuhan">
                                MAX
                            </button>
                        </div>
                        <small id="feedback_stock_${idx}" class="text-danger font-monospace mt-0.5 d-none" style="font-size: 10.5px;"></small>
                    </div>
                </td>
                <td class="text-center font-monospace small spk-text-muted" id="lbl_satuan_${idx}">-</td>
                <td class="text-center pe-3 pe-sm-4">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 opacity-75 hover-opacity-100" onclick="hapusBarisPengeluaran(${idx})" title="Hapus Baris">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyPengeluaranRows').append(rowHtml);
        updateRowCount();

        if (autoOpenLookup) {
            bukaModalLookupBahan(idx);
        }
    }

    function hapusBarisPengeluaran(rowIdx) {
        $(`#row_pengeluaran_${rowIdx}`).remove();
        updateRowCount();

        if ($('.row-pengeluaran').length === 0) {
            $('#tbodyPengeluaranRows').html(`
                <tr>
                    <td colspan="8" class="text-center py-5 spk-text-muted">
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary d-inline-block mb-2">
                            <i class="bi bi-inbox fs-3"></i>
                        </div>
                        <div class="fw-semibold">Belum ada item bahan yang dipilih</div>
                        <small class="spk-text-muted">Klik tombol <b>+ Tambah Bahan</b> di kanan atas untuk menambahkan bahan.</small>
                    </td>
                </tr>
            `);
        }
    }

    function updateRowCount() {
        const count = $('.row-pengeluaran').length;
        $('#badge_count_items_input').text(`${count} Item`);
    }

    function submitTambahPengeluaran(e) {
        e.preventDefault();
        const form = $('#formTambahPengeluaran');

        const spkId = $('#inp_spk_id').val();
        if (!spkId) {
            Swal.fire({
                icon: 'warning',
                title: 'SPK Belum Dipilih!',
                text: 'Silakan pilih SPK Produksi terlebih dahulu.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        const gudangId = $('#inp_gudang_id').val();
        if (!gudangId) {
            Swal.fire({
                icon: 'warning',
                title: 'Gudang Belum Dipilih!',
                text: 'Silakan pilih gudang asal bahan baku.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        const penerimaNama = $.trim($('#inp_penerima_nama').val());
        if (!penerimaNama) {
            Swal.fire({
                icon: 'warning',
                title: 'Penerima Wajib Diisi!',
                text: 'Silakan ketik nama penerima / operator line terlebih dahulu.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            $('#inp_penerima_nama').focus();
            return;
        }

        const rowCount = $('.row-pengeluaran').length;
        if (rowCount === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Daftar Bahan Kosong!',
                text: 'Silakan tambahkan minimal 1 item bahan baku yang ingin dikeluarkan!',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let hasValidItem = true;
        let emptyMaterial = false;
        let selectedMaterials = [];
        let duplicateMaterialFound = false;
        let duplicateMaterialName = '';
        let stockExceeded = false;
        let stockExceededMsg = '';

        $('.row-pengeluaran').each(function() {
            const matVal = $(this).find('.inp-spk-mat-id').val();
            const matName = $(this).find('.spk-input.cursor-pointer').val();
            const qtyVal = parseFloat($(this).find('.inp-qty-taken').val()) || 0;
            const stokVal = parseFloat($(this).find('.inp-stok-gudang').val()) || 0;
            const idxStr = $(this).attr('id').replace('row_pengeluaran_', '');
            const satName = $(`#lbl_satuan_${idxStr}`).text() || 'Unit';

            if (!matVal) emptyMaterial = true;
            if (!qtyVal || qtyVal <= 0) hasValidItem = false;

            if (matVal) {
                if (selectedMaterials.includes(matVal)) {
                    duplicateMaterialFound = true;
                    duplicateMaterialName = matName || 'Item Bahan';
                } else {
                    selectedMaterials.push(matVal);
                }

                // Cek ketersediaan stok fisik gudang
                if (stokVal <= 0) {
                    stockExceeded = true;
                    stockExceededMsg = `Stok bahan <b>${matName}</b> saat ini <b>0 (Kosong)</b> di gudang terpilih!`;
                    return false;
                } else if (qtyVal > stokVal) {
                    stockExceeded = true;
                    stockExceededMsg = `Kuantitas yang dikeluarkan untuk <b>${matName}</b> (${qtyVal} ${satName}) melebihi stok gudang yang tersedia (<b>${stokVal} ${satName}</b>)!`;
                    return false;
                }
            }
        });

        if (emptyMaterial) {
            Swal.fire({
                icon: 'warning',
                title: 'Bahan Belum Dipilih!',
                text: 'Ada baris bahan yang belum dipilih itemnya!',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (duplicateMaterialFound) {
            Swal.fire({
                icon: 'warning',
                title: 'Item Duplikat Terdeteksi!',
                text: `Terdapat bahan "${duplicateMaterialName}" yang dipilih lebih dari satu kali. Silakan gabungkan kuantitasnya dalam satu baris.`,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (!hasValidItem) {
            Swal.fire({
                icon: 'warning',
                title: 'Kuantitas Tidak Valid!',
                text: 'Pastikan kuantitas yang dikeluarkan lebih dari 0 pada setiap baris bahan, atau hapus baris bahan yang tidak dikeluarkan saat ini.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (stockExceeded) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Gudang Tidak Mencukupi!',
                html: stockExceededMsg,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Sesuaikan Kuantitas'
            });
            return;
        }

        const editId = $('#inp_pengeluaran_edit_id').val();
        const submitUrl = editId ? `/produksi/pengeluaran-bahan/${editId}` : '{{ route("produksi.pengeluaran-bahan.store") }}';
        const submitData = form.serialize() + (editId ? '&_method=PUT' : '');

        const submitBtn = $('#btnSubmitPengeluaran');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: submitUrl,
            method: 'POST',
            data: submitData,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#modalTambahPengeluaran').modal('hide');
                reloadTablePengeluaran();
                const activeSpkId = $('#inp_spk_id').val();
                if (activeSpkId && typeof showSpkHistoryModal === 'function') {
                    showSpkHistoryModal(activeSpkId);
                }
            },
            error: function(err) {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
            },
            complete: function() {
                const btnLabel = editId ? '<i class="bi bi-check-lg me-1"></i> Simpan Perubahan' : '<i class="bi bi-check-lg me-1"></i> Simpan & Potong Stok';
                submitBtn.prop('disabled', false).html(btnLabel);
            }
        });
    }
</script>
@endpush
