{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Edit Barang Keluar (Goods Issue Draft)              --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditGI" tabindex="-1" aria-labelledby="modalEditGILabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-warning bg-gradient text-dark rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalEditGILabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Draft Pengeluaran Barang (<span id="edit_gi_title_nomor"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formEditGI" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_gi_id">
                    <div class="row g-3">

                        {{-- Baris 1: Gudang + Tanggal + Tipe --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Gudang <span class="text-danger">*</span></label>
                            <input type="hidden" name="gudang_id" id="edit_gi_gudang_id" required>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="edit_gi_gudang_display" readonly style="cursor: pointer;" onclick="openLookupGudangModalEdit()">
                                <button class="btn btn-outline-warning px-3" type="button" onclick="openLookupGudangModalEdit()" title="Ganti Gudang">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="edit-err-gudang_id"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_gi" id="edit_gi_tanggal_gi" class="form-control" required>
                            <div class="invalid-feedback" id="edit-err-tanggal_gi"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small">Tipe Pengeluaran <span class="text-danger">*</span></label>
                            <select name="tipe_pengeluaran" id="edit_gi_tipe" class="form-select" required>
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

                        {{-- Baris 2: Tujuan + Nomor GI --}}
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-secondary small">Tujuan / Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="tujuan" id="edit_gi_tujuan" class="form-control"
                                   placeholder="Nama departemen, proyek, atau penerima" maxlength="41" required>
                            <div class="invalid-feedback" id="edit-err-tujuan"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Nomor GI</label>
                            <input type="text" id="edit_gi_nomor_gi" class="form-control bg-light" readonly>
                        </div>

                        {{-- Baris 3: Keterangan --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary small">Keterangan / Catatan</label>
                            <input type="text" name="keterangan" id="edit_gi_keterangan" class="form-control"
                                   placeholder="Catatan tambahan (opsional, maks 40 karakter)..." maxlength="41">
                        </div>

                    </div>

                    <hr class="my-4 border-secondary opacity-25">

                    {{-- Section Rincian Barang --}}
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-box-seam text-warning me-1"></i> Rincian Barang Keluar
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold text-nowrap" id="edit_btn_tambah_item" onclick="addEditGIItemRow()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                        </button>
                    </div>

                    <div id="edit-err-items" class="alert alert-danger py-2 mb-2 small" style="display:none;"></div>

                    {{-- Items Container --}}
                    <div id="edit_gi_items_container" class="gi-items-scrollable">
                        <!-- Dynamic Item Rows populated by JS -->
                    </div>

                </form>
            </div>
            <div class="modal-footer bg-light border-top-0 p-3">
                <div id="edit_gi_alert" class="alert py-2 mb-0 me-auto small w-100 w-sm-auto" style="display:none;"></div>
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-pill w-100 w-sm-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-warning text-dark fw-semibold px-3 py-2 rounded-pill w-100 w-sm-auto" id="btnSaveEditGIDraft" onclick="submitEditGIForm('Draft')">
                        <i class="bi bi-file-earmark me-1"></i> Simpan Draf
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold px-4 py-2 rounded-pill shadow-sm w-100 w-sm-auto" id="btnSaveEditGIPending" onclick="submitEditGIForm('Pending')">
                        <i class="bi bi-send me-1"></i> Simpan & Ajukan
                    </button>
                    @if(auth()->user()->can('approve-gi') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                    <button type="button" class="btn btn-success fw-semibold px-4 py-2 rounded-pill shadow-sm w-100 w-sm-auto" id="btnSaveEditGIConfirm" onclick="submitEditGIForm('Confirmed')">
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
let editItemCounter = 0;

// Open Edit Modal & Load Data
function openModalEditGI(id) {
    $('#edit_gi_items_container').empty();
    editItemCounter = 0;
    clearEditGIInputErrors();

    $.ajax({
        url: `/goods-issue/${id}`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const gi = res.gi;
            const isSuperAdmin = res.is_super_admin;
            if (gi.status === 'Confirmed' && !isSuperAdmin) {
                Swal.fire('Peringatan', 'Dokumen yang sudah disetujui (Confirmed) hanya dapat diubah oleh Super Admin.', 'warning');
                return;
            }

            $('#edit_gi_id').val(gi.id);
            if (gi.status === 'Confirmed') {
                $('#modalEditGILabel').html('<i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran Barang (<span id="edit_gi_title_nomor"></span>) <span class="badge bg-danger ms-2">Confirmed - Super Admin</span>');
                $('#btnSaveEditGIDraft').hide();
                $('#btnSaveEditGIPending').hide();
                $('#btnSaveEditGIConfirm').show().html('<i class="bi bi-save me-1"></i> Simpan Perubahan (Super Admin)');
            } else if (gi.status === 'Pending') {
                $('#modalEditGILabel').html('<i class="bi bi-pencil-square me-2"></i>Edit Pengajuan Pengeluaran (<span id="edit_gi_title_nomor"></span>) <span class="badge bg-info text-dark ms-2">Menunggu Approval</span>');
                $('#btnSaveEditGIDraft').hide();
                $('#btnSaveEditGIPending').show().html('<i class="bi bi-save me-1"></i> Simpan Perubahan Pengajuan');
                $('#btnSaveEditGIConfirm').show().html('<i class="bi bi-check-circle me-1"></i> Setujui Langsung');
            } else {
                $('#modalEditGILabel').html('<i class="bi bi-pencil-square me-2"></i>Edit Draf Pengeluaran Barang (<span id="edit_gi_title_nomor"></span>)');
                $('#btnSaveEditGIDraft').show().html('<i class="bi bi-file-earmark me-1"></i> Simpan Draf');
                $('#btnSaveEditGIPending').show().html('<i class="bi bi-send me-1"></i> Simpan & Ajukan');
                $('#btnSaveEditGIConfirm').show().html('<i class="bi bi-check-circle me-1"></i> Setujui Langsung');
            }
            $('#edit_gi_title_nomor').text(gi.nomor_gi);
            $('#edit_gi_nomor_gi').val(gi.nomor_gi);
            $('#edit_gi_tanggal_gi').val(gi.tanggal_gi ? gi.tanggal_gi.substring(0, 10) : '');
            $('#edit_gi_gudang_id').val(gi.gudang_id);
            $('#edit_gi_gudang_display').val(gi.gudang ? `${gi.gudang.kode_gudang} - ${gi.gudang.nama_gudang}` : '');
            $('#edit_gi_tipe').val(gi.tipe_pengeluaran);
            $('#edit_gi_tujuan').val(gi.tujuan);
            $('#edit_gi_keterangan').val(gi.keterangan || '');

            // Load Available Barangs for this warehouse
            fetchEditAvailableBarangs(gi.gudang_id, function () {
                if (gi.details && gi.details.length > 0) {
                    gi.details.forEach(d => {
                        addEditGIItemRow(d.barang_id, d.jumlah);
                    });
                } else {
                    addEditGIItemRow();
                }

                const modalEditGI = new bootstrap.Modal(document.getElementById('modalEditGI'));
                modalEditGI.show();

                // Real-time limit listener for tujuan & keterangan in Edit modal
                $('#edit_gi_tujuan, #edit_gi_keterangan').off('input paste keyup').on('input paste keyup', function () {
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
            });
        },
        error: function () {
            Swal.fire('Error', 'Gagal memuat data pengeluaran barang.', 'error');
        }
    });
}

function fetchEditAvailableBarangs(gudangId, callback) {
    window.availableBarangsForSelectedGudangEdit = [];
    $.ajax({
        url: `/goods-issue/barangs-by-gudang/${gudangId}`,
        method: 'GET',
        success: function (res) {
            window.availableBarangsForSelectedGudangEdit = res.barangs || [];
            if (typeof callback === 'function') callback();
        },
        error: function () {
            window.availableBarangsForSelectedGudangEdit = [];
            if (typeof callback === 'function') callback();
        }
    });
}

function addEditGIItemRow(itemData = null, selectedJumlah = 1) {
    editItemCounter++;
    const idx = editItemCounter;
    const cleanJumlah = (parseFloat(selectedJumlah) % 1 === 0) ? parseInt(selectedJumlah) : parseFloat(selectedJumlah);

    let barangId = '';
    let barangDisplay = '';
    let satuan = '-';
    let fotoUrl = null;

    if (itemData) {
        if (typeof itemData === 'object') {
            const b = itemData.barang || itemData;
            barangId = itemData.barang_id || b.id || '';
            const kode = b.kode_barang || b.kode || '';
            const nama = b.nama_barang || b.nama || '';
            barangDisplay = (kode || nama) ? `${kode} - ${nama}` : '';
            satuan = b.satuan ? (b.satuan.nama_satuan || b.satuan) : (b.nama_satuan || '-');
            
            const pFoto = b.primary_foto || b.primaryFoto;
            if (b.foto) {
                fotoUrl = b.foto;
            } else if (pFoto) {
                fotoUrl = `/storage/${pFoto.file_path || pFoto.foto_path}`;
            }
        } else {
            barangId = itemData;
            const b = (window.availableBarangsForSelectedGudangEdit || []).find(x => parseInt(x.id) === parseInt(barangId));
            if (b) {
                barangDisplay = `${b.kode || b.kode_barang} - ${b.nama || b.nama_barang}`;
                satuan = b.nama_satuan || b.satuan || '-';
                const pFoto = b.primary_foto || b.primaryFoto;
                fotoUrl = b.foto || (pFoto ? `/storage/${pFoto.file_path || pFoto.foto_path}` : null);
            }
        }
    }

    const fotoImgHtml = fotoUrl
        ? `<img id="edit_foto_preview_img_${idx}" src="${fotoUrl}" class="rounded" style="width:28px; height:28px; object-fit:cover;">`
        : `<img id="edit_foto_preview_img_${idx}" src="" class="rounded" style="width:28px; height:28px; object-fit:cover;">`;
    const fotoWrapStyle = fotoUrl ? 'cursor:pointer;' : 'display:none; cursor:pointer;';

    const rowHtml = `
    <div class="item-row card p-3 mb-2 border rounded-3 shadow-xs" id="edit_item_row_${idx}">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-secondary">Nama Barang <span class="text-danger">*</span></label>
                <input type="hidden" id="edit_barang_id_${idx}" class="edit-gi-barang-id" value="${barangId}" required>
                <div class="input-group input-group-sm">
                    <div id="edit_foto_preview_wrap_${idx}" class="input-group-text p-1 bg-light border" style="${fotoWrapStyle}" title="Klik untuk perbesar foto">
                        ${fotoImgHtml}
                    </div>
                    <input type="text" class="form-control edit-gi-barang-display" id="edit_barang_display_${idx}" readonly
                           value="${escapeHtml(barangDisplay)}"
                           placeholder="-- Klik tombol cari barang --" style="cursor: pointer;"
                           onclick="openLookupBarangModal(${idx}, true)">
                    <button class="btn btn-outline-danger px-3" type="button" onclick="openLookupBarangModal(${idx}, true)" title="Cari Barang">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-secondary">Jumlah Keluar <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="number" step="any" min="0.0001" class="form-control edit-gi-qty-input" id="edit_gi_jumlah_${idx}"
                           value="${cleanJumlah}" oninput="validateEditGIStock(${idx})" required>
                    <span class="input-group-text bg-light small text-muted" id="edit_gi_satuan_${idx}">${satuan}</span>
                </div>
                <div class="stok-indicator mt-1" id="edit_gi_stok_info_${idx}">
                    ${barangId ? '<span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Mengecek stok…</span>' : '<span class="text-muted small">Pilih barang terlebih dahulu</span>'}
                </div>
                <div id="edit_stok_error_${idx}"></div>
            </div>
            <div class="col-12 col-md-3 text-end pt-md-3 pt-1">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="removeEditGIItemRow(${idx})" title="Hapus baris">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>`;

    $('#edit_gi_items_container').append(rowHtml);

    if (fotoUrl) {
        $(`#edit_foto_preview_wrap_${idx}`).off('click').on('click', function(e) {
            e.stopPropagation();
            openPreviewFoto(fotoUrl, barangDisplay);
        });
    }

    if (barangId) {
        onEditGIBarangChange(idx);
    }
}

function removeEditGIItemRow(idx) {
    $(`#edit_item_row_${idx}`).remove();
}

function onEditGIBarangChange(idx) {
    const barangId = $(`#edit_barang_id_${idx}`).val();
    const gudangId = $('#edit_gi_gudang_id').val();

    if (!barangId || !gudangId) {
        $(`#edit_gi_satuan_${idx}`).text('-');
        $(`#edit_gi_stok_info_${idx}`).html('<span class="text-muted small">Pilih barang terlebih dahulu</span>');
        $(`#edit_foto_preview_wrap_${idx}`).hide();
        $(`#edit_gi_jumlah_${idx}`).attr('data-stok', 9999).attr('data-izin-negatif', '1');
        return;
    }

    $(`#edit_gi_stok_info_${idx}`).html('<span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Mengecek stok…</span>');

    const b = (window.availableBarangsForSelectedGudangEdit || []).find(x => parseInt(x.id) === parseInt(barangId));
    
    // Foto rendering
    const pFoto = b ? (b.primary_foto || b.primaryFoto) : null;
    const fotoUrl = b ? (b.foto || (pFoto ? `/storage/${pFoto.file_path || pFoto.foto_path}` : null)) : null;
    const kodeNama = b ? `${b.kode || b.kode_barang} - ${b.nama || b.nama_barang}` : '';
    
    if (fotoUrl) {
        $(`#edit_foto_preview_img_${idx}`).attr('src', fotoUrl);
        $(`#edit_foto_preview_wrap_${idx}`).off('click').on('click', function (e) {
            e.stopPropagation();
            openPreviewFoto(fotoUrl, kodeNama);
        }).show();
    } else {
        $(`#edit_foto_preview_wrap_${idx}`).hide();
    }

    if (b) {
        $(`#edit_gi_satuan_${idx}`).text(b.nama_satuan || b.satuan || '-');
        const stok = parseFloat(b.stok_aktual || 0);
        const izin = b.izinkan_stok_negatif ? 1 : 0;
        $(`#edit_gi_jumlah_${idx}`).attr('data-stok', stok).attr('data-izin-negatif', izin);
        
        let badge = izin === 1
            ? `<span class="badge bg-success bg-opacity-10 text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Stok Gudang: ${formatGIQty(stok)} ${b.nama_satuan || b.satuan}</span>`
            : `<span class="badge bg-warning bg-opacity-10 text-dark fw-semibold"><i class="bi bi-box-seam me-1"></i>Stok Tersedia: ${formatGIQty(stok)} ${b.nama_satuan || b.satuan}</span>`;
        
        $(`#edit_gi_stok_info_${idx}`).html(badge);
        validateEditGIStock(idx);
    } else {
        $.ajax({
            url: `/goods-issue/stok/${gudangId}/${barangId}`,
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                $(`#edit_gi_satuan_${idx}`).text(res.satuan || '-');
                const stok = parseFloat(res.stok_aktual || 0);
                const izin = res.izinkan_negatif ? 1 : 0;
                $(`#edit_gi_jumlah_${idx}`).attr('data-stok', stok).attr('data-izin-negatif', izin);

                let badge = izin === 1
                    ? `<span class="badge bg-success bg-opacity-10 text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Stok Gudang: ${formatGIQty(stok)} ${res.satuan}</span>`
                    : `<span class="badge bg-warning bg-opacity-10 text-dark fw-semibold"><i class="bi bi-box-seam me-1"></i>Stok Tersedia: ${formatGIQty(stok)} ${res.satuan}</span>`;
                
                $(`#edit_gi_stok_info_${idx}`).html(badge);
                validateEditGIStock(idx);
            }
        });
    }
}

function validateEditGIStock(idx) {
    const input   = $(`#edit_gi_jumlah_${idx}`);
    const rawVal  = input.val();
    const val     = parseFloat(rawVal) || 0;
    const stok    = parseFloat(input.attr('data-stok') || 0);
    const izin    = input.attr('data-izin-negatif') === '1';
    const satuan  = $(`#edit_gi_satuan_${idx}`).text() || '';

    if (!izin && rawVal !== '' && val > stok) {
        input.addClass('is-invalid');
        $(`#edit_gi_stok_info_${idx}`).html(`<span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle me-1"></i>Melebihi Stok (${formatGIQty(stok)} ${satuan})</span>`);
    } else {
        input.removeClass('is-invalid');
        let badge = izin === 1
            ? `<span class="badge bg-success bg-opacity-10 text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Stok Gudang: ${formatGIQty(stok)} ${satuan}</span>`
            : `<span class="badge bg-warning bg-opacity-10 text-dark fw-semibold"><i class="bi bi-box-seam me-1"></i>Stok Tersedia: ${formatGIQty(stok)} ${satuan}</span>`;
        $(`#edit_gi_stok_info_${idx}`).html(badge);
    }
}

function submitEditGIForm(targetStatus) {
    clearEditGIInputErrors();

    const id         = $('#edit_gi_id').val();
    const gudangId   = $('#edit_gi_gudang_id').val();
    const tanggal    = $('#edit_gi_tanggal_gi').val();
    const tipe       = $('#edit_gi_tipe').val();
    const tujuan     = $('#edit_gi_tujuan').val().trim();
    const keterangan = $('#edit_gi_keterangan').val().trim();

    const items = [];
    const seenBarangIds = new Set();
    let hasEmptyItem = false;
    let duplicateItems = [];
    let stokExceededItems = [];

    $('#edit_gi_items_container .item-row').each(function () {
        const barangId   = $(this).find('.edit-gi-barang-id').val();
        const barangName = $(this).find('.edit-gi-barang-display').val() || 'Barang';
        const jumlah     = parseFloat($(this).find('input[type=number]').val() || 0);
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
        items.push({ barang_id: barangId, jumlah: jumlah });
    });

    if (!gudangId) {
        $('#edit-err-gudang_id').text('Gudang wajib dipilih.').show();
        Swal.fire({ icon: 'warning', title: 'Gudang Belum Dipilih!', text: 'Silakan pilih gudang pengeluaran barang.', confirmButtonColor: '#ef4444' });
        return;
    }
    if (!tanggal) {
        $('#edit-err-tanggal_gi').text('Tanggal wajib diisi.').show();
        Swal.fire({ icon: 'warning', title: 'Tanggal Wajib Diisi!', text: 'Silakan isi tanggal transaksi pengeluaran barang.', confirmButtonColor: '#ef4444' });
        return;
    }
    if (!tujuan) {
        $('#edit-err-tujuan').text('Tujuan wajib diisi.').show();
        Swal.fire({ icon: 'warning', title: 'Tujuan Belum Diisi!', text: 'Silakan isi tujuan, departemen, atau penerima barang.', confirmButtonColor: '#ef4444' });
        return;
    }
    if (tujuan.length > 40) {
        $('#edit-err-tujuan').text('Tujuan/Penerima maksimal 40 karakter.').show();
        Swal.fire({ icon: 'warning', title: 'Peringatan Validasi', text: 'Tujuan / Penerima maksimal 40 karakter!', confirmButtonColor: '#ef4444' });
        return;
    }
    if (keterangan.length > 40) {
        Swal.fire({ icon: 'warning', title: 'Peringatan Validasi', text: 'Keterangan maksimal 40 karakter!', confirmButtonColor: '#ef4444' });
        return;
    }

    const totalEditRows = $('#edit_gi_items_container .item-row').length;
    if (totalEditRows === 0 || items.length === 0) {
        $('#edit-err-items').text('Tambahkan minimal 1 barang.').show();
        Swal.fire({
            icon: 'warning',
            title: 'Daftar Barang Masih Kosong!',
            text: 'Silakan tambahkan minimal 1 barang pada transaksi ini.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-plus-lg me-1"></i> Tambah Barang'
        });
        return;
    }

    if (hasEmptyItem) {
        $('#edit-err-items').text('Lengkapi pilihan barang dan jumlah yang valid.').show();
        Swal.fire({
            icon: 'warning',
            title: 'Data Barang Belum Lengkap!',
            html: 'Terdapat baris barang yang <b>belum dipilih</b> atau <b>jumlah pengeluaran masih 0 / kosong</b>.<br><br><small class="text-muted">Silakan lengkapi atau hapus baris yang tidak digunakan sebelum menyimpan.</small>',
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Lengkapi Data'
        });
        return;
    }

    if (duplicateItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Barang Terduplikasi!',
            html: `Terdapat barang yang dipilih lebih dari 1 kali:<br><br><div class="text-start bg-light p-3 rounded-3 border text-danger small">${duplicateItems.join('<br>')}</div><br><small class="text-muted">Setiap barang hanya boleh diinput 1 kali. Silakan gabungkan jumlahnya.</small>`,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Perbaiki Daftar Barang'
        });
        return;
    }

    if (stokExceededItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Stok Tidak Mencukupi!',
            html: `Pengeluaran barang melebihi stok yang tersedia di gudang:<br><br><div class="text-start bg-light p-3 rounded-3 border text-danger small">${stokExceededItems.join('<br>')}</div><br><small class="text-muted">Gudang ini tidak mengizinkan pengeluaran dengan stok minus.</small>`,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Perbaiki Jumlah'
        });
        return;
    }

    const $btnDraft   = $('#btnSaveEditGIDraft');
    const $btnPending = $('#btnSaveEditGIPending');
    const $btnConf    = $('#btnSaveEditGIConfirm');
    $btnDraft.prop('disabled', true);
    $btnPending.prop('disabled', true);
    $btnConf.prop('disabled', true);

    $.ajax({
        url: `/goods-issue/${id}`,
        method: 'PUT',
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data: JSON.stringify({
            gudang_id:        gudangId,
            tanggal_gi:       tanggal,
            tujuan:           tujuan,
            tipe_pengeluaran: tipe,
            keterangan:       keterangan,
            items:            items,
            status:           targetStatus,
        }),
        success: function (res) {
            $('#modalEditGI').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                timer: 2500,
                showConfirmButton: false,
            }).then(() => {
                location.reload();
            });
        },
        error: function (xhr) {
            const res = xhr.responseJSON ?? {};
            Swal.fire('Error', res.message || 'Gagal mengedit draft pengeluaran barang.', 'error');
            $btnDraft.prop('disabled', false);
            $btnPending.prop('disabled', false);
            $btnConf.prop('disabled', false);
        }
    });
}

function clearEditGIInputErrors() {
    $('#edit-err-gudang_id').hide().text('');
    $('#edit-err-tanggal_gi').hide().text('');
    $('#edit-err-tujuan').hide().text('');
    $('#edit-err-items').hide().text('');
}

function openLookupGudangModalEdit() {
    openLookupGudangModal(function (selectedGudang) {
        $('#edit_gi_gudang_id').val(selectedGudang.id);
        $('#edit_gi_gudang_display').val(`${selectedGudang.kode_gudang} - ${selectedGudang.nama_gudang}`);
        fetchEditAvailableBarangs(selectedGudang.id, function () {
            $('#edit_gi_items_container').empty();
            editItemCounter = 0;
            addEditGIItemRow();
        });
    });
}
</script>
@endpush
