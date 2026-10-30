{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Input / Tambah Barang Keluar (Goods Issue)          --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@can('tambah-gi')
<div class="modal fade" id="modalTambahGI" tabindex="-1" aria-labelledby="modalTambahGILabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-danger bg-gradient text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalTambahGILabel">
                    <i class="bi bi-box-arrow-right me-2"></i>Buat Pengeluaran Barang (Goods Issue)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formTambahGI" novalidate>
                    @csrf
                    <div class="row g-3">

                        {{-- Baris 1: Gudang + Tanggal + Tipe --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Gudang <span class="text-danger">*</span></label>
                            <input type="hidden" name="gudang_id" id="gi_gudang_id" required>
                            <div class="input-group">
                                <input type="text" class="form-control" id="gi_gudang_display" readonly
                                       placeholder="-- Klik tombol cari untuk memilih gudang --" style="cursor: pointer;"
                                       onclick="openLookupGudangModal()">
                                <button class="btn btn-outline-danger px-3" type="button" onclick="openLookupGudangModal()" title="Cari Gudang">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="err-gudang_id"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_gi" id="gi_tanggal_gi" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="err-tanggal_gi"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small">Tipe Pengeluaran <span class="text-danger">*</span></label>
                            <select name="tipe_pengeluaran" id="gi_tipe" class="form-select" required>
                                @if(isset($tipePengeluarans) && $tipePengeluarans->isNotEmpty())
                                    @foreach($tipePengeluarans as $tp)
                                        <option value="{{ $tp->nama_tipe }}">{{ $tp->nama_tipe }}</option>
                                    @endforeach
                                @else
                                    <option value="Internal">Internal</option>
                                    <option value="Retur Supplier">Retur Supplier</option>
                                    <option value="Penyesuaian">Penyesuaian</option>
                                    <option value="Lainnya">Lainnya</option>
                                @endif
                            </select>
                        </div>

                        {{-- Baris 2: Tujuan --}}
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-secondary small">Tujuan / Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="tujuan" id="gi_tujuan" class="form-control"
                                   placeholder="Nama departemen, proyek, atau penerima" maxlength="41" required>
                            <div class="invalid-feedback" id="err-tujuan"></div>
                        </div>

                        {{-- Baris 2: Nomor GI --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Nomor GI</label>
                            <div class="input-group">
                                <div class="input-group-text bg-light">
                                     <div class="form-check form-switch mb-0">
                                         <input class="form-check-input" type="checkbox" id="gi_auto_number" name="auto_number">
                                         <label class="form-check-label small" for="gi_auto_number">Auto</label>
                                     </div>
                                 </div>
                                 <input type="text" name="nomor_gi" id="gi_nomor_gi" class="form-control"
                                        placeholder="GI/YYYY/MM/XXXX">
                            </div>
                            <div class="invalid-feedback d-block" id="err-nomor_gi"></div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Keterangan</label>
                            <textarea name="keterangan" id="gi_keterangan" class="form-control" rows="2"
                                       placeholder="Catatan tambahan (opsional, maks 40 karakter)" maxlength="41"></textarea>
                        </div>
                    </div>

                    {{-- Detail Items --}}
                    <hr class="my-4">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-2 text-danger"></i>Detail Barang Keluar</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold text-nowrap" onclick="tambahItemGI()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                        </button>
                    </div>
                    <div id="gi_items_container" class="gi-items-scrollable">
                        {{-- Items rendered dynamically with modal lookup search --}}
                    </div>
                    <div id="err-items" class="text-danger small mt-1" style="display:none;"></div>

                    {{-- Alert area --}}
                    <div id="gi_alert" class="mt-3" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer bg-light rounded-bottom-3 p-3">
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 w-100 w-sm-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-2 fw-semibold text-dark w-100 w-sm-auto" id="btnSimpanDraftGI" onclick="submitGIForm('Draft')">
                        <i class="bi bi-file-earmark me-1"></i> Simpan Draf
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm w-100 w-sm-auto" id="btnAjukanGI" onclick="submitGIForm('Pending')">
                        <i class="bi bi-send me-1"></i> Simpan & Ajukan
                    </button>
                    @if(auth()->user()->can('approve-gi') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm w-100 w-sm-auto" id="btnSimpanGI" onclick="submitGIForm('Confirmed')">
                        <i class="bi bi-check-circle me-1"></i> Setujui Langsung
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.availableBarangsForSelectedGudang = [];

$(document).ready(function () {
    // Init Select2 for gudang picker in Input Modal
    $('.select2-gi').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: '-- Pilih Gudang --',
        dropdownParent: $('#modalTambahGI'),
    }).on('change', function () {
        const gudangId = $(this).val();
        $('#gi_items_container').empty();
        window.availableBarangsForSelectedGudang = [];
        
        if (gudangId) {
            $.get(`/goods-issue/barangs-by-gudang/${gudangId}`, function (res) {
                window.availableBarangsForSelectedGudang = res.barangs || [];
            });
        }
    });

    // Auto Number toggle
    $('#gi_auto_number').on('change', function () {
        const manual = !this.checked;
        $('#gi_nomor_gi').prop('disabled', !manual);
        if (!manual) $('#gi_nomor_gi').val('');
    });

    // Limit tujuan and keterangan to 40 characters with warning alert
    $('#gi_tujuan, #gi_keterangan').on('input paste keyup', function () {
        if (this.value.length > 40) {
            this.value = this.value.substring(0, 40);
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai',
                text: 'Maksimal input adalah 40 karakter!',
                confirmButtonColor: '#ef4444'
            });
        }
    });

    // Reset buttons when modal is shown
    $('#modalTambahGI').on('show.bs.modal', function () {
        $('#btnSimpanDraftGI').prop('disabled', false).html('<i class="bi bi-file-earmark me-1"></i> Simpan Draf');
        $('#btnAjukanGI').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Simpan & Ajukan');
        $('#btnSimpanGI').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Setujui Langsung');
    });
});

let itemCounter = 0;

// Add an item row dynamically using Modal Search Lookup
function tambahItemGI() {
    const gudangId = $('#gi_gudang_id').val();
    if (!gudangId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Gudang Terlebih Dahulu',
            text: 'Silakan pilih gudang sebelum menambahkan barang keluar.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const currentItemCount = $('#gi_items_container .item-row').length;
    if (currentItemCount >= 25) {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Maksimum Tercapai',
            text: 'Maksimum penambahan barang dalam satu transaksi pengeluaran adalah 25 item.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    if (!window.availableBarangsForSelectedGudang || window.availableBarangsForSelectedGudang.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Ada Stok Tersedia',
            text: 'Tidak ada barang yang memiliki stok tersedia di gudang ini.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const idx = itemCounter++;
    if (!window.giItemSerialsStore) window.giItemSerialsStore = {};
    window.giItemSerialsStore[idx] = [];

    const html = `
    <div class="item-row card p-3 mb-2 border rounded-3 shadow-xs" id="item-row-${idx}">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-6">
                <label class="form-label small fw-semibold text-secondary">Nama Barang <span class="text-danger">*</span></label>
                <input type="hidden" id="barang_id_${idx}" class="gi-input-barang-id" name="items[${idx}][barang_id]" required>
                <div class="input-group input-group-sm">
                    <div id="foto_preview_wrap_${idx}" class="input-group-text p-1 bg-light border" style="display:none; cursor:pointer;" title="Klik untuk perbesar foto">
                        <img id="foto_preview_img_${idx}" src="" class="rounded" style="width:28px; height:28px; object-fit:cover;">
                    </div>
                    <input type="text" class="form-control gi-input-barang-display" id="barang_display_${idx}" readonly
                           placeholder="-- Klik tombol cari barang --" style="cursor: pointer;"
                           onclick="openLookupBarangModal(${idx})">
                    <button class="btn btn-outline-danger px-3" type="button" onclick="openLookupBarangModal(${idx})" title="Cari Barang">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <label class="form-label small fw-semibold text-secondary">Jumlah <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" id="jumlah_${idx}"
                           name="items[${idx}][jumlah]" min="0.0001" step="any"
                           placeholder="0" required oninput="validateJumlah(${idx})">
                    <span class="input-group-text bg-light" id="satuan_label_${idx}">-</span>
                </div>
                <div class="stok-indicator mt-1" id="stok_info_${idx}">
                    <span class="text-muted small">Pilih barang terlebih dahulu</span>
                </div>
                <div id="stok_error_${idx}"></div>
            </div>
            <div class="col-12 col-md-2 col-lg-2 text-end pt-md-3 pt-1">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="hapusItemGI(${idx})" title="Hapus baris">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
        </div>

        <!-- Serial Number Row -->
        <div class="mt-2.5 pt-2 border-top" id="sn_wrapper_${idx}">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 bg-light-subtle p-2 rounded-3 border border-light-subtle">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 btn-open-gi-sn shadow-xs" 
                        id="btn_open_sn_${idx}" data-idx="${idx}" data-context="input" onclick="openModalSelectSerialsGI(${idx}, 'input')" style="font-size: 0.78rem;">
                        <i class="bi bi-upc-scan me-1"></i> Pilih Serial Number
                    </button>
                    <span class="badge bg-secondary-subtle text-muted border font-monospace px-2 py-1" id="sn_badge_count_${idx}" style="font-size: 0.74rem;">
                        0 / 0 Unit
                    </span>
                    <span id="sn_status_text_${idx}" class="small text-muted font-monospace" style="font-size: 0.72rem;">
                        <i class="bi bi-dash-circle me-1"></i>Tanpa SN (Opsional)
                    </span>
                </div>
                <div id="sn_chips_preview_${idx}" class="d-flex flex-wrap gap-1 align-items-center"></div>
            </div>
        </div>
    </div>`;

    $('#gi_items_container').append(html);
}

function hapusItemGI(idx) {
    if (window.giItemSerialsStore) delete window.giItemSerialsStore[idx];
    $(`#item-row-${idx}`).remove();
}

function formatGIQty(val) {
    if (val === null || val === undefined || isNaN(val)) return '0';
    const num = parseFloat(val);
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

// When barang is selected: fetch live stok
function onBarangChange(idx) {
    const gudangId = $('#gi_gudang_id').val();
    const barangId = $(`#barang_id_${idx}`).val();

    $(`#stok_info_${idx}`).html('<span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Mengecek stok…</span>');
    $(`#stok_error_${idx}`).empty();
    $(`#jumlah_${idx}`).removeAttr('max');

    if (!gudangId || !barangId) {
        $(`#stok_info_${idx}`).html('<span class="text-muted small">Pilih barang terlebih dahulu</span>');
        return;
    }

    $.ajax({
        url: `/goods-issue/stok/${gudangId}/${barangId}`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const stok = parseFloat(res.stok_aktual || 0);
            const izin = res.izinkan_negatif ? 1 : 0;
            const satuan = res.satuan || '-';

            $(`#satuan_label_${idx}`).text(satuan);
            $(`#jumlah_${idx}`).attr('data-stok', stok).attr('data-izin-negatif', izin);

            let html = '';
            if (stok <= 0 && !izin) {
                html = `<span class="badge bg-danger bg-opacity-10 text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Stok Kosong: 0 ${satuan}</span>`;
                $(`#jumlah_${idx}`).attr('max', 0);
            } else if (izin === 1) {
                html = `<span class="badge bg-success bg-opacity-10 text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Stok Gudang: ${formatGIQty(stok)} ${satuan}</span>`;
            } else if (stok < 10) {
                html = `<span class="badge bg-warning bg-opacity-10 text-dark fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Stok Tersedia: ${formatGIQty(stok)} ${satuan}</span>`;
                $(`#jumlah_${idx}`).attr('max', stok);
            } else {
                html = `<span class="badge bg-success bg-opacity-10 text-success fw-semibold"><i class="bi bi-box-seam me-1"></i>Stok Tersedia: ${formatGIQty(stok)} ${satuan}</span>`;
                $(`#jumlah_${idx}`).attr('max', stok);
            }
            $(`#stok_info_${idx}`).html(html);
            validateJumlah(idx);
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.error || 'Gagal mengecek stok.';
            $(`#stok_info_${idx}`).html(`<span class="badge bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle me-1"></i>${msg}</span>`);
        }
    });
}

function validateJumlah(idx) {
    const input   = $(`#jumlah_${idx}`);
    const rawVal  = input.val();
    const jumlah  = parseFloat(rawVal) || 0;
    const stok    = parseFloat(input.attr('data-stok') || 0);
    const izin    = input.attr('data-izin-negatif') === '1';
    const satuan  = $(`#satuan_label_${idx}`).text() || '';

    $(`#stok_error_${idx}`).empty();

    if (!izin && rawVal !== '' && jumlah > stok) {
        input.addClass('is-invalid');
        $(`#stok_error_${idx}`).html(`
            <div class="alert alert-danger py-1 px-2 mt-1 mb-0 small d-flex align-items-center gap-1 rounded-2 shadow-xs">
                <i class="bi bi-exclamation-triangle-fill text-danger flex-shrink-0"></i>
                <span>Melebihi stok tersedia (${formatGIQty(stok)} ${satuan})</span>
            </div>
        `);
    } else {
        input.removeClass('is-invalid');
    }

    if (typeof updateRowSnBadge === 'function') {
        updateRowSnBadge(idx, 'input', isNaN(jumlah) ? 0 : jumlah);
    }
}

// Submit GI form (Draft, Pending, or Confirmed)
window.submitGIForm = function (targetStatus) {
    clearGIInputErrors();
    const $btnDraft  = $('#btnSimpanDraftGI');
    const $btnAjukan = $('#btnAjukanGI');
    const $btnConf   = $('#btnSimpanGI');

    if ($btnDraft.prop('disabled') || $btnAjukan.prop('disabled') || $btnConf.prop('disabled')) {
        return; // Cegah double click / duplikasi request
    }

    const activeBtn = targetStatus === 'Confirmed' ? $btnConf : (targetStatus === 'Pending' ? $btnAjukan : $btnDraft);
    const oldHtml   = activeBtn.html();

    const restoreInputGIBtns = function() {
        $btnDraft.prop('disabled', false);
        $btnAjukan.prop('disabled', false);
        $btnConf.prop('disabled', false);
        activeBtn.html(oldHtml);
    };

    $btnDraft.prop('disabled', true);
    $btnAjukan.prop('disabled', true);
    $btnConf.prop('disabled', true);

    const spinnerText = targetStatus === 'Confirmed' ? 'Menyimpan & Konfirmasi…' : (targetStatus === 'Pending' ? 'Mengajukan…' : 'Menyimpan Draf…');
    activeBtn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${spinnerText}`);

    // Collect form data
    const gudangId   = $('#gi_gudang_id').val();
    const tanggal    = $('#gi_tanggal_gi').val();
    const tujuan     = $('#gi_tujuan').val().trim();
    const tipe       = $('#gi_tipe').val();
    const keterangan = $('#gi_keterangan').val().trim();
    const autoNum    = $('#gi_auto_number').is(':checked') ? 1 : 0;
    const nomorGi    = autoNum ? null : $('#gi_nomor_gi').val().trim();

    // Collect items
    const items = [];
    const seenBarangIds = new Set();
    let hasEmptyItem = false;
    let duplicateItems = [];
    let stokExceededItems = [];

    $('#gi_items_container .item-row').each(function () {
        const barangId   = $(this).find('.gi-input-barang-id').val();
        const barangName = $(this).find('.gi-input-barang-display').val() || $(this).find('input[type=text]').val() || 'Barang';
        const jumlah     = parseFloat($(this).find('input[type=number]').val());
        const satuan     = $(this).find('.input-group-text').last().text() || '';

        if (!barangId || isNaN(jumlah) || jumlah <= 0) {
            hasEmptyItem = true;
            return;
        }

        if (seenBarangIds.has(barangId)) {
            duplicateItems.push(`<b>${barangName}</b>`);
        } else {
            seenBarangIds.add(barangId);
        }

        const izin = $(this).find('input[type=number]').attr('data-izin-negatif') === '1';
        const stok = parseFloat($(this).find('input[type=number]').attr('data-stok') || 0);

        if (!izin && jumlah > stok) {
            stokExceededItems.push(`<b>${barangName}</b> (Diminta: <b>${formatGIQty(jumlah)}</b>, Stok: <b>${formatGIQty(stok)} ${satuan}</b>)`);
        }

        const rowId = $(this).attr('id');
        const rIdx = rowId ? rowId.replace('item-row-', '') : null;
        const serials = (rIdx !== null && window.giItemSerialsStore && window.giItemSerialsStore[rIdx]) ? window.giItemSerialsStore[rIdx] : [];

        items.push({ barang_id: barangId, jumlah: jumlah, serial_numbers: serials });
    });

    if (!gudangId) {
        showGIFieldError('gudang_id', 'Gudang wajib dipilih.');
        Swal.fire({ icon: 'warning', title: 'Gudang Belum Dipilih!', text: 'Silakan pilih gudang asal pengeluaran barang terlebih dahulu.', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }
    if (!tanggal) {
        showGIFieldError('tanggal_gi', 'Tanggal wajib diisi.');
        Swal.fire({ icon: 'warning', title: 'Tanggal Wajib Diisi!', text: 'Silakan pilih tanggal transaksi pengeluaran barang.', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }
    if (!tujuan) {
        showGIFieldError('tujuan', 'Tujuan / Penerima wajib diisi.');
        Swal.fire({ icon: 'warning', title: 'Tujuan Belum Diisi!', text: 'Silakan isi tujuan, departemen, atau nama penerima barang.', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }
    if (tujuan.length > 40) {
        showGIFieldError('tujuan', 'Tujuan/Penerima maksimal 40 karakter.');
        Swal.fire({ icon: 'warning', title: 'Peringatan Validasi', text: 'Tujuan / Penerima maksimal 40 karakter!', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }
    if (keterangan.length > 40) {
        Swal.fire({ icon: 'warning', title: 'Peringatan Validasi', text: 'Keterangan maksimal 40 karakter!', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }
    if (!autoNum && !nomorGi) {
        showGIFieldError('nomor_gi', 'Nomor GI wajib diisi.');
        Swal.fire({ icon: 'warning', title: 'Nomor GI Belum Diisi!', text: 'Silakan isi nomor GI manual atau centang opsi Auto.', confirmButtonColor: '#ef4444' });
        restoreInputGIBtns();
        return;
    }

    const totalRowElements = $('#gi_items_container .item-row').length;
    if (totalRowElements === 0 || items.length === 0) {
        showGIError('#err-items', 'Tambahkan minimal 1 barang.');
        Swal.fire({
            icon: 'warning',
            title: 'Daftar Barang Masih Kosong!',
            text: 'Silakan tambahkan minimal 1 barang pada rincian pengeluaran ini.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-plus-lg me-1"></i> Tambah Barang'
        });
        restoreInputGIBtns();
        return;
    }

    if (hasEmptyItem) {
        showGIError('#err-items', 'Lengkapi pilihan barang dan jumlah yang valid.');
        Swal.fire({
            icon: 'warning',
            title: 'Data Barang Belum Lengkap!',
            html: 'Terdapat baris barang yang <b>belum dipilih</b> atau <b>jumlah pengeluaran masih 0 / kosong</b>.<br><br><small class="text-muted">Silakan pilih barang dan isi jumlah yang valid pada setiap baris.</small>',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Lengkapi Data'
        });
        restoreInputGIBtns();
        return;
    }

    // Professional Alert when duplicate items are selected
    if (duplicateItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Terdapat Barang Duplikat!',
            html: `Barang berikut diinput lebih dari 1 kali:<br><br><div class="text-start bg-light p-3 rounded-3 border text-danger small">${duplicateItems.join('<br>')}</div><br><small class="text-muted">Harap hapus baris duplikat dan gabungkan jumlahnya menjadi satu baris.</small>`,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Perbaiki Daftar Barang'
        });
        restoreInputGIBtns();
        return;
    }

    // Professional Alert when stock is insufficient
    if (stokExceededItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Stok Tidak Mencukupi!',
            html: `Pengeluaran barang melebihi stok yang tersedia di gudang:<br><br><div class="text-start bg-light p-3 rounded-3 border text-danger small">${stokExceededItems.join('<br>')}</div><br><small class="text-muted">Gudang ini tidak mengizinkan pengeluaran dengan stok minus.</small>`,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Perbaiki Jumlah'
        });
        restoreInputGIBtns();
        return;
    }

    $.ajax({
        url: '{{ route("gi.store") }}',
        method: 'POST',
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data: JSON.stringify({
            gudang_id:        gudangId,
            tanggal_gi:       tanggal,
            tujuan:           tujuan,
            tipe_pengeluaran: tipe,
            keterangan:       keterangan,
            auto_number:      autoNum,
            nomor_gi:         nomorGi,
            items:            items,
            status:           targetStatus,
        }),
        success: function (res) {
            $('#modalTambahGI').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                location.reload();
            });
        },
        error: function (xhr) {
            const res = xhr.responseJSON ?? {};
            if (xhr.status === 422 && res.errors) {
                Object.keys(res.errors).forEach(k => {
                    showGIFieldError(k, res.errors[k][0]);
                });
                showGIAlert('danger', res.message || 'Validasi gagal!');
            } else {
                showGIAlert('danger', res.message || 'Terjadi kesalahan server.');
            }
            restoreInputGIBtns();
        },
        complete: function() {
            restoreInputGIBtns();
        }
    });
};

// Reset modal on close
document.getElementById('modalTambahGI')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formTambahGI').reset();
    $('#gi_gudang_id').val('');
    $('#gi_gudang_display').val('');
    $('#gi_items_container').empty();
    window.availableBarangsForSelectedGudang = [];
    itemCounter = 0;
    clearGIInputErrors();
    $('#gi_auto_number').prop('checked', false);
    $('#gi_nomor_gi').prop('disabled', false).val('');
    $('#gi_alert').hide();
});

// Helpers for input modal
function clearGIInputErrors() {
    document.querySelectorAll('#modalTambahGI .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#modalTambahGI [id^="err-"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
}
function showGIFieldError(field, msg) {
    const el = document.getElementById(`err-${field}`);
    if (el) { el.textContent = msg; el.style.display = 'block'; }
    const input = document.querySelector(`#modalTambahGI [name="${field}"]`);
    if (input) input.classList.add('is-invalid');
}
function showGIError(selector, msg) {
    const el = document.querySelector(selector);
    if (el) { el.textContent = msg; el.style.display = 'block'; }
}
function showGIAlert(type, msg) {
    $('#gi_alert').html(`<div class="alert alert-${type} py-2">${msg}</div>`).show();
}
</script>
@endpush
@endcan
