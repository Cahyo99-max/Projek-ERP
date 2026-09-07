{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Buat & Edit Pengajuan Rencana Stock Opname (View & JS) --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@can('tambah-opname')
<div class="modal fade" id="modalTambahOpname" tabindex="-1" aria-labelledby="modalTambahOpnameLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary bg-gradient text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalTambahOpnameLabel">
                    <i class="bi bi-sliders me-2"></i>Form Rencana & Pengajuan Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formTambahOpname" novalidate action="{{ route('opname.store') }}">
                    @csrf
                    <input type="hidden" name="edit_id" id="so_edit_id" value="">
                    <input type="hidden" name="submit_directly" id="so_submit_directly" value="0">
                    <div id="partial_hidden_inputs_container"></div>

                    <div class="row g-3">
                        {{-- Gudang --}}
                        <div class="col-12 col-md-6 col-lg-6">
                            <label class="form-label fw-semibold text-secondary small">Gudang <span class="text-danger">*</span></label>
                            <select name="gudang_id" id="so_gudang_id" class="form-select select2-so" required>
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($gudangs as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-gudang_id"></div>
                        </div>
                        
                        {{-- Tanggal Pengajuan --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-semibold text-secondary small">Tgl Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_opname" id="so_tanggal" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="err-tanggal_opname"></div>
                        </div>

                        {{-- Tanggal Rencana Mulai --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-semibold text-secondary small">Rencana Tgl Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="so_tanggal_mulai" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="err-tanggal_mulai"></div>
                        </div>

                        {{-- Tanggal Rencana Selesai --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-semibold text-secondary small">Rencana Tgl Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="so_tanggal_selesai" class="form-control"
                                   value="{{ now()->addDays(2)->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="err-tanggal_selesai"></div>
                        </div>

                        {{-- Jenis Stock Opname --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label fw-semibold text-secondary small">Jenis Stock Opname <span class="text-danger">*</span></label>
                            <select name="jenis_opname" id="so_jenis" class="form-select" required onchange="toggleJenisOpname()">
                                <option value="Full Stock Opname">Full Stock Opname</option>
                                <option value="Partial Stock Opname">Partial Stock Opname</option>
                            </select>
                        </div>

                        {{-- Nomor Opname --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label fw-semibold text-secondary small">Nomor Pengajuan</label>
                            <div class="input-group">
                                <div class="input-group-text bg-light">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="so_auto_number" name="auto_number" checked>
                                        <label class="form-check-label small" for="so_auto_number">Auto</label>
                                    </div>
                                </div>
                                <input type="text" name="nomor_opname" id="so_nomor" class="form-control"
                                       placeholder="SO/YYYY/MM/XXXX" disabled maxlength="25">
                            </div>
                            <div class="invalid-feedback d-block" id="err-nomor_opname"></div>
                        </div>

                        {{-- Keterangan / Alasan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Alasan Stock Opname <span class="text-danger">*</span></label>
                            <textarea name="keterangan" id="so_keterangan" class="form-control" rows="2"
                                      placeholder="Tulis alasan pengajuan penyesuaian stok (wajib, max 40 karakter)" required maxlength="40"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="invalid-feedback m-0" id="err-keterangan"></div>
                                <span class="small text-muted" id="keterangan_char_counter">0/40 karakter</span>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Partial Filters --}}
                    <div id="section_partial_filters" style="display:none;" class="partial-filter-card p-3 mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h6 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-funnel-fill"></i> Filter Penyesuaian Partial
                            </h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold small border" id="badge_partial_selected_count">
                                <i class="bi bi-box-seam me-1"></i>0 Barang Terpilih
                            </span>
                        </div>
                        <div class="row g-3">
                            {{-- Kategori Barang dengan Tombol Cari Modal --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-tags me-1"></i>Kategori Barang <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <select name="filter_kategori_id" id="so_filter_kategori" class="form-select select2-partial" onchange="onFilterChange()">
                                        <option value="">-- Pilih Kategori (Wajib) --</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm px-3 fw-semibold" id="btnOpenLookupKategori" onclick="openModalLookupKategori()" title="Cari & Pilih Kategori via Modal">
                                        <i class="bi bi-search"></i>
                                        <span>Cari Kategori</span>
                                    </button>
                                </div>
                                <div class="form-text small text-muted">Pilih atau cari kategori terlebih dahulu sebelum memilih barang.</div>
                            </div>

                            {{-- Lokasi Rak / Blok --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-geo-alt me-1"></i>Lokasi Rak / Blok
                                </label>
                                <input type="text" name="filter_rak" id="so_filter_rak" class="form-control" placeholder="Contoh: RAK-A1, RAK-B2 (opsional)">
                                <div class="form-text small text-muted">Catatan lokasi rak sebagai referensi tim di lapangan.</div>
                            </div>

                            {{-- KOTAK CARI BARANG SPESIFIK --}}
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-search me-1"></i>Cari & Tambah Barang Spesifik
                                </label>
                                <div class="input-group">
                                    <select id="so_single_barang_search" class="form-select select2-single-barang">
                                    </select>
                                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-3 fw-semibold" id="btnOpenLookupBarang" onclick="openModalLookupBarang()" title="Cari & Pilih Banyak Barang via Modal">
                                        <i class="bi bi-grid-3x3-gap-fill me-1"></i>
                                        <span>Cari via Modal</span>
                                    </button>
                                </div>
                                <div class="form-text small text-muted mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Ketik nama/kode barang pada kotak pencarian di atas untuk langsung menambah ke tabel, atau klik <strong>Cari via Modal</strong>.
                                </div>
                            </div>

                            {{-- TABEL BARANG TERPILIH --}}
                            <div class="col-12 mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-semibold text-secondary mb-0">
                                        <i class="bi bi-table me-1"></i>Tabel Barang Spesifik Terpilih
                                    </label>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small" onclick="clearAllPartialItems()" id="btn_clear_all_partial" style="display:none;">
                                        <i class="bi bi-trash me-1"></i>Hapus Semua Pilihan
                                    </button>
                                </div>
                                <div class="table-responsive border rounded-3 bg-card" style="max-height: 250px;">
                                    <table class="table table-hover table-striped align-middle mb-0" id="tablePartialSelectedItems">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th class="text-center" width="6%">No</th>
                                                <th width="20%">Kode Barang</th>
                                                <th width="44%">Nama Barang</th>
                                                <th class="text-end" width="18%">Stok Sistem</th>
                                                <th class="text-center" width="12%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyPartialSelectedItems">
                                            <tr id="empty_partial_selected_row">
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox display-6 d-block mb-2 text-secondary opacity-50"></i>
                                                    Belum ada barang spesifik yang dipilih. Ketik nama barang di atas atau klik <strong>Cari via Modal</strong>.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="so_alert" class="mt-3" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 pb-4 pb-sm-3">
                <button type="button" class="btn btn-cancel w-100 w-sm-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
                <div class="d-flex flex-row gap-2 w-100 w-sm-auto justify-content-end">
                    <button type="button" class="btn btn-draft flex-fill flex-sm-grow-0" id="btnSimpanOpnameDraft">
                        <i class="bi bi-save me-1"></i> Simpan Draf
                    </button>
                    <button type="button" class="btn btn-submit flex-fill flex-sm-grow-0" id="btnPostingOpname">
                        <i class="bi bi-send-fill me-1"></i> Posting Pengajuan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SEARCH LOOKUP KATEGORI --}}
<div class="modal fade" id="modalLookupKategori" tabindex="-1" aria-labelledby="modalLookupKategoriLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalLookupKategoriLabel">
                    <i class="bi bi-tags me-2 text-warning"></i>Pilih Kategori Barang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="lookup_kategori_keyword" class="form-control" placeholder="Ketik nama kategori..." onkeyup="filterKategoriTable()">
                </div>

                <div class="table-responsive border rounded" style="max-height: 350px;">
                    <table class="table table-hover table-striped align-middle mb-0" id="tableLookupKategori">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center" width="12%">No</th>
                                <th width="68%">Nama Kategori</th>
                                <th class="text-center" width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupKategori">
                            @foreach($kategoris as $index => $k)
                                <tr class="kategori-row-item" data-name="{{ strtolower($k->nama_kategori) }}">
                                    <td class="text-center font-monospace text-muted small">{{ $index + 1 }}</td>
                                    <td class="fw-semibold text-primary-emphasis">{{ $k->nama_kategori }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary px-3 rounded-pill fw-semibold" onclick="selectKategoriFromModal('{{ $k->id }}', '{{ addslashes($k->nama_kategori) }}')">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SEARCH LOOKUP BARANG --}}
<div class="modal fade" id="modalLookupBarang" tabindex="-1" aria-labelledby="modalLookupBarangLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white rounded-top-3">
                <h5 class="modal-title fw-bold" id="modalLookupBarangLabel">
                    <i class="bi bi-search me-2 text-warning"></i>Pilih Barang Partial Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="lookup_search_keyword" class="form-control" placeholder="Ketik nama atau kode barang..." onkeyup="if(event.key==='Enter') performLookupSearch()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary w-100 fw-semibold" onclick="performLookupSearch()">
                            <i class="bi bi-search me-1"></i> Cari Barang
                        </button>
                    </div>
                </div>

                <div class="table-responsive border rounded" style="max-height: 380px;">
                    <table class="table table-hover table-striped align-middle mb-0" id="tableLookupBarang">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center" width="5%">
                                    <input type="checkbox" class="form-check-input" id="check_all_lookup" onchange="toggleCheckAllLookup(this)">
                                </th>
                                <th width="20%">Kode Barang</th>
                                <th width="45%">Nama Barang</th>
                                <th width="15%">Kategori</th>
                                <th class="text-end" width="15%">Stok Sistem</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupBarang">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-box-seam display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    Pilih gudang terlebih dahulu atau klik tombol Cari Barang.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-3 d-flex justify-content-between">
                <span class="small fw-semibold text-secondary" id="lookup_selected_count">0 barang dipilih</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" onclick="applyLookupSelection()">
                        <i class="bi bi-check-lg me-1"></i> Tambahkan Barang Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT KHUSUS INPUT & EDIT PENGAJUAN                     --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
let partialSelectedItemsMap = new Map();

function toggleJenisOpname() {
    const val = $('#so_jenis').val();
    if (val === 'Partial Stock Opname') {
        $('#section_partial_filters').slideDown(250);
    } else {
        $('#section_partial_filters').slideUp(200);
        $('#so_filter_kategori').val('').trigger('change');
        clearAllPartialItems();
        $('#so_filter_rak').val('');
    }
}

function onFilterChange() {
    renderPartialSelectedTable();
}

function openModalLookupKategori() {
    $('#lookup_kategori_keyword').val('');
    filterKategoriTable();
    $('#modalLookupKategori').modal('show');
}

function filterKategoriTable() {
    const q = $('#lookup_kategori_keyword').val().toLowerCase().trim();
    $('.kategori-row-item').each(function () {
        const name = $(this).data('name');
        if (!q || name.includes(q)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function selectKategoriFromModal(id, name) {
    $('#so_filter_kategori').val(id).trigger('change');
    $('#modalLookupKategori').modal('hide');
    Swal.fire({
        icon: 'success',
        title: 'Kategori Dipilih!',
        text: `Kategori ${name} berhasil dipilih.`,
        timer: 1200,
        showConfirmButton: false
    });
}

function renderPartialSelectedTable() {
    const tbody = $('#tbodyPartialSelectedItems');
    const container = $('#partial_hidden_inputs_container');
    container.empty();

    if (partialSelectedItemsMap.size === 0) {
        tbody.html(`
            <tr id="empty_partial_selected_row">
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox display-6 d-block mb-2 text-secondary opacity-50"></i>
                    Belum ada barang spesifik yang dipilih. Ketik nama barang di atas atau klik <strong>Cari via Modal</strong>.
                </td>
            </tr>
        `);
        $('#btn_clear_all_partial').hide();

        const kategoriId = $('#so_filter_kategori').val();
        let badgeText = '0 Barang Terpilih';
        if (kategoriId) {
            const kategoriText = $('#so_filter_kategori option:selected').text();
            badgeText = `Kategori: ${kategoriText.replace('-- Pilih Kategori (Wajib) --', 'Semua Kategori')}`;
        }
        $('#badge_partial_selected_count').html(`<i class="bi bi-box-seam me-1"></i>${badgeText}`);
        return;
    }

    let html = '';
    let index = 1;
    partialSelectedItemsMap.forEach(function (item, id) {
        container.append(`<input type="hidden" name="filter_barang_ids[]" value="${id}">`);
        html += `
            <tr>
                <td class="text-center font-monospace text-muted small">${index++}</td>
                <td><span class="badge bg-secondary font-monospace">${item.kode_barang}</span></td>
                <td class="fw-semibold text-primary-emphasis">${item.nama_barang}</td>
                <td class="text-end fw-bold text-success">${item.stok ?? 0}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0" 
                            style="width:26px; height:26px;" onclick="removePartialItem('${id}')" title="Hapus Barang">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
    $('#btn_clear_all_partial').show();
    $('#badge_partial_selected_count').html(`<i class="bi bi-box-seam me-1"></i>${partialSelectedItemsMap.size} Barang Spesifik Terpilih`);
}

function removePartialItem(id) {
    partialSelectedItemsMap.delete(id.toString());
    partialSelectedItemsMap.delete(parseInt(id));
    renderPartialSelectedTable();
}

function clearAllPartialItems() {
    partialSelectedItemsMap.clear();
    renderPartialSelectedTable();
}

function openModalLookupBarang() {
    const gudangId = $('#so_gudang_id').val();
    if (!gudangId) {
        Swal.fire({
            icon: 'warning',
            title: 'Gudang Belum Dipilih!',
            text: 'Harap pilih Gudang terlebih dahulu sebelum mencari barang!',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    const kategoriId = $('#so_filter_kategori').val();
    if (!kategoriId) {
        Swal.fire({
            icon: 'warning',
            title: 'Kategori Belum Dipilih!',
            text: 'Harap pilih Kategori Barang terlebih dahulu sebelum mencari atau menambahkan barang!',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    $('#lookup_search_keyword').val('');
    $('#modalLookupBarang').modal('show');
    performLookupSearch();
}

function performLookupSearch() {
    const gudangId = $('#so_gudang_id').val();
    const kategoriId = $('#so_filter_kategori').val();
    const q = $('#lookup_search_keyword').val();

    const tbody = $('#tbodyLookupBarang');
    tbody.html(`
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Searching data barang berstok di gudang...
            </td>
        </tr>
    `);

    $.ajax({
        url: '{{ route("opname.search-barang") }}',
        method: 'GET',
        data: {
            q: q,
            gudang_id: gudangId,
            kategori_id: kategoriId,
            lookup_full: 1
        },
        success: function (barangs) {
            if (!barangs || barangs.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Tidak ditemukan barang berstok di gudang ini untuk kategori terpilih.
                        </td>
                    </tr>
                `);
                updateLookupSelectedCount();
                return;
            }

            let html = '';
            barangs.forEach(function (b) {
                const isChecked = partialSelectedItemsMap.has(b.id.toString()) || partialSelectedItemsMap.has(parseInt(b.id)) ? 'checked' : '';
                html += `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input chk-lookup-item" 
                                   value="${b.id}" data-code="${b.kode_barang}" data-name="${b.nama_barang}" data-stok="${b.stok}"
                                   ${isChecked} onchange="updateLookupSelectedCount()">
                        </td>
                        <td><span class="badge bg-secondary font-monospace">${b.kode_barang}</span></td>
                        <td class="fw-semibold text-primary-emphasis">${b.nama_barang}</td>
                        <td><span class="badge bg-light text-dark border">${b.kategori}</span></td>
                        <td class="text-end fw-bold text-success">${b.stok} <small class="text-muted fw-normal">${b.satuan}</small></td>
                    </tr>
                `;
            });

            tbody.html(html);
            updateLookupSelectedCount();
        },
        error: function () {
            tbody.html(`
                <tr>
                    <td colspan="5" class="text-center text-danger py-3">Gagal mengambil data barang dari server.</td>
                </tr>
            `);
        }
    });
}

function toggleCheckAllLookup(chk) {
    $('.chk-lookup-item').prop('checked', chk.checked);
    updateLookupSelectedCount();
}

function updateLookupSelectedCount() {
    const count = $('.chk-lookup-item:checked').length;
    $('#lookup_selected_count').text(`${count} barang dipilih`);
}

function applyLookupSelection() {
    const checkedItems = $('.chk-lookup-item:checked');
    if (checkedItems.length === 0) {
        Swal.fire('Info', 'Tidak ada barang yang dicentang.', 'info');
        return;
    }

    checkedItems.each(function () {
        const id = $(this).val();
        const code = $(this).data('code');
        const name = $(this).data('name');
        const stok = $(this).data('stok');

        partialSelectedItemsMap.set(id.toString(), {
            id: id,
            kode_barang: code,
            nama_barang: name,
            stok: stok
        });
    });

    renderPartialSelectedTable();
    $('#modalLookupBarang').modal('hide');

    Swal.fire({
        icon: 'success',
        title: 'Berhasil Ditambahkan!',
        text: `${checkedItems.length} barang dimasukkan ke daftar tabel partial.`,
        timer: 1500,
        showConfirmButton: false
    });
}

window.editOpname = function(id) {
    clearErrors();
    $('#so_alert').hide();

    // Show modal instantly without delay or SweetAlert loading popup
    $('#modalTambahOpnameLabel').html('<i class="bi bi-pencil-square me-2"></i>Edit Rencana Stock Opname');
    $('#modalTambahOpname').modal('show');

    $.ajax({
        url: `/stock-opname/${id}/edit`,
        method: 'GET',
        success: function (res) {
            const opname = res.opname;
            $('#so_edit_id').val(opname.id);

            $('#so_gudang_id').val(opname.gudang_id).trigger('change');
            
            const dtTanggal = opname.tanggal_opname ? opname.tanggal_opname.split('T')[0] : '';
            const dtMulai   = opname.tanggal_mulai ? opname.tanggal_mulai.split('T')[0] : '';
            const dtSelesai = opname.tanggal_selesai ? opname.tanggal_selesai.split('T')[0] : '';

            $('#so_tanggal').val(dtTanggal);
            $('#so_tanggal_mulai').val(dtMulai);
            $('#so_tanggal_selesai').val(dtSelesai);

            $('#so_jenis').val(opname.jenis_opname).trigger('change');
            $('#so_keterangan').val(opname.keterangan);
            $('#so_nomor').val(opname.nomor_opname).prop('disabled', true);
            $('#so_auto_number').prop('checked', true);

            partialSelectedItemsMap.clear();

            if (opname.jenis_opname === 'Partial Stock Opname') {
                $('#section_partial_filters').show();

                if (res.selected_barangs && res.selected_barangs.length > 0) {
                    res.selected_barangs.forEach(function (b) {
                        partialSelectedItemsMap.set(b.id.toString(), {
                            id: b.id,
                            kode_barang: b.kode_barang,
                            nama_barang: b.nama_barang,
                            stok: b.stok
                        });
                    });
                }
            }

            renderPartialSelectedTable();
        },
        error: function (xhr) {
            $('#modalTambahOpname').modal('hide');
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat data rencana opname.', 'error');
        }
    });
};

function editOpname(id) {
    return window.editOpname(id);
}

$(document).ready(function () {

    if ($('.select2-so').length) {
        $('.select2-so').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            placeholder: '-- Pilih Gudang --',
            dropdownParent: $('#modalTambahOpname'),
        });
    }

    if ($('#so_filter_kategori').length) {
        $('#so_filter_kategori').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            dropdownParent: $('#modalTambahOpname'),
        });
    }

    // Single Barang Autocomplete search for Partial Selection with Category Guard
    if ($('#so_single_barang_search').length) {
        $('#so_single_barang_search').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            dropdownParent: $('#modalTambahOpname'),
            ajax: {
                url: '{{ route("opname.search-barang") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        gudang_id: $('#so_gudang_id').val(),
                        kategori_id: $('#so_filter_kategori').val(),
                        lookup_full: 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: `${item.kode_barang} - ${item.nama_barang} (Stok: ${item.stok})`,
                                rawData: item
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: '-- Ketik Nama / Kode Barang untuk Menambah ke Tabel --'
        }).on('select2:opening', function (e) {
            const gudangId = $('#so_gudang_id').val();
            if (!gudangId) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Gudang Belum Dipilih!',
                    text: 'Harap pilih Gudang terlebih dahulu!',
                    confirmButtonColor: '#f59e0b'
                });
                return false;
            }

            const kategoriId = $('#so_filter_kategori').val();
            if (!kategoriId) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Kategori Belum Dipilih!',
                    text: 'Harap pilih Kategori Barang terlebih dahulu sebelum mencari barang!',
                    confirmButtonColor: '#f59e0b'
                });
                return false;
            }
        }).on('select2:select', function(e) {
            const data = e.params.data;
            if (data && data.rawData) {
                const b = data.rawData;
                partialSelectedItemsMap.set(b.id.toString(), {
                    id: b.id,
                    kode_barang: b.kode_barang,
                    nama_barang: b.nama_barang,
                    stok: b.stok
                });
                renderPartialSelectedTable();
                $(this).val(null).trigger('change');
            }
        });
    }

    $('#so_auto_number').on('change', function () {
        const manual = !this.checked;
        $('#so_nomor').prop('disabled', !manual);
        if (!manual) $('#so_nomor').val('');
    });

    // Real-time character limit enforcement and warnings
    $('#so_keterangan').on('input', function () {
        const val = $(this).val();
        const len = val.length;
        $('#keterangan_char_counter').text(`${len}/40 karakter`);

        if (len >= 40) {
            $('#keterangan_char_counter').removeClass('text-muted').addClass('text-danger fw-bold');
            if (len > 40) {
                $(this).val(val.substring(0, 40));
                $('#keterangan_char_counter').text(`40/40 karakter`);
            }
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai!',
                text: 'Alasan stock opname maksimal 40 karakter!',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            $('#keterangan_char_counter').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    $('#so_nomor').on('input', function () {
        const val = $(this).val();
        if (val.length >= 25) {
            if (val.length > 25) {
                $(this).val(val.substring(0, 25));
            }
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai!',
                text: 'Nomor pengajuan maksimal 25 karakter!',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    function submitFormProposal(isPosting) {
        clearErrors();
        $('#so_alert').hide();

        const form = document.getElementById('formTambahOpname');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            showOpnameAlert('danger', 'Harap lengkapi semua field bertanda bintang (*).');
            return;
        }

        const keterangan = $('#so_keterangan').val();
        if (keterangan && keterangan.length > 40) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                text: 'Alasan stock opname maksimal 40 karakter!',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        const manualNomor = !$('#so_auto_number').is(':checked');
        const nomor = $('#so_nomor').val();
        if (manualNomor && nomor && nomor.length > 25) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                text: 'Nomor pengajuan maksimal 25 karakter!',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        $('#so_submit_directly').val(isPosting ? 1 : 0);

        const editId = $('#so_edit_id').val();
        const url = editId ? `/stock-opname/${editId}/update` : '{{ route("opname.store") }}';
        const data = $('#formTambahOpname').serialize();

        const targetBtn = isPosting ? $('#btnPostingOpname') : $('#btnSimpanOpnameDraft');
        const originalHtml = targetBtn.html();

        $('#btnSimpanOpnameDraft, #btnPostingOpname').prop('disabled', true);
        targetBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses…');

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function (res) {
                reloadTableOpname();
                const msgTitle = isPosting ? 'Berhasil Diposting!' : 'Berhasil Disimpan Draf!';
                Swal.fire({ icon:'success', title: msgTitle, text: res.message, timer: 1500, showConfirmButton: false })
                .then(() => {
                    $('#modalTambahOpname').modal('hide');
                });
            },
            error: function (xhr) {
                $('#btnSimpanOpnameDraft, #btnPostingOpname').prop('disabled', false);
                targetBtn.html(originalHtml);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    for (let field in errors) {
                        if (field === 'tanggal_opname' && errors[field][0].includes('dibatasi minimal')) {
                            continue;
                        }
                        showFieldError(field, errors[field][0]);
                    }

                    if (errors.tanggal_opname && errors.tanggal_opname[0].includes('dibatasi minimal')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batasi Frekuensi Pengajuan!',
                            html: `<div class="p-2 text-secondary-emphasis" style="font-size:0.95rem; line-height:1.6;">${errors.tanggal_opname[0]}</div>`,
                            confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Saya Mengerti',
                            confirmButtonColor: '#f59e0b',
                            customClass: {
                                confirmButton: 'btn btn-warning px-4 rounded-pill fw-bold text-dark shadow-sm'
                            }
                        });
                    } else {
                        showOpnameAlert('danger', xhr.responseJSON.message || 'Validasi form gagal.');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        });
    }

    $('#btnSimpanOpnameDraft').on('click', function (e) {
        e.preventDefault();
        submitFormProposal(false);
    });

    $('#btnPostingOpname').on('click', function (e) {
        e.preventDefault();
        submitFormProposal(true);
    });

    document.getElementById('modalTambahOpname')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('formTambahOpname').reset();
        $('#so_edit_id').val('');
        $('#so_submit_directly').val('0');
        $('#modalTambahOpnameLabel').html('<i class="bi bi-sliders me-2"></i>Form Rencana & Pengajuan Stock Opname');
        $('#btnSimpanOpnameDraft, #btnPostingOpname').prop('disabled', false);
        $('#btnSimpanOpnameDraft').html('<i class="bi bi-save me-1"></i> Simpan Draf');
        $('#btnPostingOpname').html('<i class="bi bi-send-fill me-1"></i> Posting Pengajuan');
        $('#so_gudang_id').val('').trigger('change');
        $('#so_filter_kategori').val('').trigger('change');
        clearAllPartialItems();
        $('#section_partial_filters').hide();
        clearErrors();
        $('#so_auto_number').prop('checked', true);
        $('#so_nomor').prop('disabled', true).val('');
        $('#so_alert').hide();
    });
});
</script>
@endpush
