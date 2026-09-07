<!-- MODAL CARI BARANG — AJAX search, tidak load semua data di awal -->
<div class="modal fade picker-modal" id="modalCariBarang" tabindex="-1" data-bs-backdrop="false" style="z-index: 999999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-boxes fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Pilih Barang / Jasa</h5>
                        <small class="text-muted">Ketik nama, kode, atau barcode untuk mencari</small>
                    </div>
                </div>
                <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Search input -->
                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="barangSearchInput" class="form-control picker-search-input"
                        placeholder="Cari nama barang, kode, atau barcode..." autocomplete="off">
                </div>

                <!-- Results list -->
                <div id="barangPickerResults" class="picker-results-list">
                    <div class="picker-loading">
                        <i class="bi bi-search"></i>
                        Ketik minimal 1 karakter untuk mencari barang...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        let currentSelectorRow  = null;
        let barangSearchTimeout = null;

        // ── OPEN PICKER ──
        $(document).on('click', '.btn-search-barang-modal, .label-product-name', function (e) {
            let activeModalId = $('.modal.show').attr('id');
            let supId = (activeModalId === 'modalEditPO') ? $('#edit_supplier_id').val() : $('#supplier_id').val();
            if (!supId) {
                e.preventDefault();
                e.stopImmediatePropagation();
                Swal.fire({
                    title: 'Supplier Belum Dipilih!',
                    text: 'Silakan cari & pilih Supplier terlebih dahulu untuk memilih barang.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Supplier Sekarang',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        let openSupBtn = (activeModalId === 'modalEditPO') ? '#btnOpenEditSupplierPicker' : '#btnOpenSupplierPicker';
                        $(openSupBtn).trigger('click');
                    }
                });
                return false;
            }
            currentSelectorRow = $(this).closest('tr');
            let targetModal = document.getElementById('modalCariBarang');
            if (targetModal) {
                if (targetModal.parentNode !== document.body) {
                    document.body.appendChild(targetModal);
                }
                bootstrap.Modal.getOrCreateInstance(targetModal, { backdrop: false, keyboard: true }).show();
            }
            $('#barangSearchInput').val('').trigger('input').focus();
        });

        // ── AJAX SEARCH (debounce 300ms) ──
        $('#barangSearchInput').on('input', function () {
            clearTimeout(barangSearchTimeout);
            let q = $(this).val().trim();

            $('#barangPickerResults').html(`
                <div class="picker-loading">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div><br>
                    Mencari...
                </div>
            `);

            barangSearchTimeout = setTimeout(function () {
                $.get(SEARCH_BARANG_URL, { q: q }, function (data) {
                    if (!data || data.length === 0) {
                        $('#barangPickerResults').html(`
                            <div class="picker-empty">
                                <i class="bi bi-inbox"></i>
                                Tidak ada barang ditemukan untuk "<b>${escapeHtml(q)}</b>"
                            </div>
                        `);
                        return;
                    }

                    let html = '';
                    data.forEach(function (b) {
                        let fotoHtml = b.foto_url
                            ? `<img src="${escapeHtml(b.foto_url)}" style="width:100%;height:100%;object-fit:cover;" alt="${escapeHtml(b.nama_barang)}">`
                            : `<i class="bi bi-image text-muted fs-5"></i>`;

                        let tipeBg = 'rgba(100,116,139,0.1); color:#64748b;';
                        if (b.tipe_item === 'Stok')  tipeBg = 'rgba(16,185,129,0.1); color:#10b981;';
                        if (b.tipe_item === 'Aset')  tipeBg = 'rgba(245,158,11,0.1); color:#f59e0b;';
                        if (b.tipe_item === 'Jasa')  tipeBg = 'rgba(59,130,246,0.1); color:#3b82f6;';

                        html += `
                            <div class="picker-item" 
                                data-id="${b.id}" 
                                data-kode="${escapeHtml(b.kode_barang)}"
                                data-nama="${escapeHtml(b.nama_barang)}"
                                data-satuan-id="${b.satuan_id || ''}"
                                data-satuan="${escapeHtml(b.satuan)}"
                                data-satuan-dasar="${escapeHtml(b.satuan_dasar || b.satuan)}"
                                data-alternate-units='${escapeHtml(JSON.stringify(b.alternate_units || []))}'
                                data-tipe="${escapeHtml(b.tipe_item)}"
                                data-foto="${escapeHtml(b.foto_url || '')}"
                                data-fotos='${escapeHtml(JSON.stringify(b.fotos || []))}'>
                                <div class="product-img-wrapper" style="flex-shrink:0;">
                                    ${fotoHtml}
                                </div>
                                <div class="picker-meta">
                                    <div class="picker-name">${escapeHtml(b.nama_barang)}</div>
                                    <div class="picker-sub">
                                        <span class="font-monospace">${escapeHtml(b.kode_barang)}</span>
                                        &bull; ${escapeHtml(b.satuan)}
                                    </div>
                                </div>
                                <span class="picker-badge" style="background-color:${tipeBg}">${escapeHtml(b.tipe_item).toUpperCase()}</span>
                            </div>
                        `;
                    });

                    $('#barangPickerResults').html(html);
                }).fail(function () {
                    $('#barangPickerResults').html(`
                        <div class="picker-empty text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Gagal memuat data barang.
                        </div>
                    `);
                });
            }, 300);
        });

        // ── PILIH BARANG ──
        $(document).on('click', '#barangPickerResults .picker-item', function () {
            if (!currentSelectorRow) return;

            let id         = $(this).data('id');
            let kode       = $(this).data('kode');
            let nama       = $(this).data('nama');
            let satuanId   = $(this).data('satuan-id');
            let satuan     = $(this).data('satuan');
            let satuanDasar= $(this).data('satuan-dasar') || satuan;
            let altUnits   = $(this).data('alternate-units') || [];
            let tipe       = $(this).data('tipe');
            let foto       = $(this).data('foto');
            let fotos      = $(this).attr('data-fotos') || $(this).data('fotos') || [];

            // Fill hidden ID + display fields
            currentSelectorRow.find('.select-product-input').val(id);
            currentSelectorRow.find('.label-product-name').val(`[${kode}] ${nama}`);
            
            // Satuan & Konversi handling
            if (currentSelectorRow.find('.satuan-id-val').length === 0) {
                currentSelectorRow.append(`<input type="hidden" class="satuan-id-val" value="${satuanId || ''}">`);
            } else {
                currentSelectorRow.find('.satuan-id-val').val(satuanId || '');
            }

            if (currentSelectorRow.find('.faktor-pengali-val').length === 0) {
                currentSelectorRow.append(`<input type="hidden" class="faktor-pengali-val" value="1">`);
            } else {
                currentSelectorRow.find('.faktor-pengali-val').val(1);
            }

            // Build unit selector if alternate units exist, otherwise text badge
            let unitCell = currentSelectorRow.find('.label-satuan-manual');
            if (altUnits && altUnits.length > 0) {
                let unitOptions = `<option value="${satuanId}" data-factor="1" selected>${escapeHtml(satuan)}</option>`;
                altUnits.forEach(function(u) {
                    unitOptions += `<option value="${u.satuan_id}" data-factor="${u.faktor_pengali}">${escapeHtml(u.nama_satuan)}</option>`;
                });
                unitCell.html(`
                    <select class="form-select form-select-sm text-center satuan-select-input rounded-3 shadow-none border-secondary border-opacity-25" style="min-width: 85px; font-weight: 600;">
                        ${unitOptions}
                    </select>
                    <div class="conversion-preview-badge text-muted mt-1 small" style="font-size: 0.72rem; display: none;"></div>
                `);
            } else {
                unitCell.html(`<span class="badge bg-light text-secondary border px-2 py-1">${escapeHtml(satuan)}</span><div class="conversion-preview-badge text-muted mt-1 small" style="font-size: 0.72rem; display: none;"></div>`);
            }

            // Update photo preview
            let imgPreview = currentSelectorRow.find('.product-img-wrapper');
            imgPreview.attr('data-fotos', typeof fotos === 'string' ? fotos : JSON.stringify(fotos))
                      .addClass('cursor-pointer')
                      .css('cursor', 'pointer');
            if (foto) {
                imgPreview.html(`<img src="${foto}" alt="${nama}" class="product-img" style="width:100%;height:100%;object-fit:cover;">`);
            } else {
                imgPreview.html(`<i class="bi bi-image text-muted"></i>`);
            }

            // Update tipe badge
            let tipeBg = 'rgba(100,116,139,0.1); color:#64748b;';
            if (tipe === 'Stok') tipeBg = 'rgba(16,185,129,0.1); color:#10b981;';
            if (tipe === 'Aset') tipeBg = 'rgba(245,158,11,0.1); color:#f59e0b;';
            if (tipe === 'Jasa') tipeBg = 'rgba(59,130,246,0.1); color:#3b82f6;';

            let badge = currentSelectorRow.find('.label-tipe-item');
            if (badge.length === 0) {
                currentSelectorRow.find('.badge-container').html(`<span class="badge small px-2 py-1 rounded-pill label-tipe-item" style="${tipeBg}">${tipe.toUpperCase()}</span>`);
            } else {
                badge.removeClass('d-none').attr('style', tipeBg).text(tipe.toUpperCase());
            }

            // Trigger recalculation
            let isEditMode = currentSelectorRow.closest('tbody').attr('id') === 'edit_po_details_tbody';
            if (isEditMode) {
                (typeof window.calculateEditTotals === 'function') && window.calculateEditTotals();
            } else {
                (typeof window.calculateTotals === 'function') && window.calculateTotals();
            }

            // Close modal & reset
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCariBarang')).hide();
            currentSelectorRow = null;
        });

        // Reset search state when modal is closed
        document.getElementById('modalCariBarang').addEventListener('hidden.bs.modal', function () {
            $('#barangSearchInput').val('');
            $('#barangPickerResults').html(`
                <div class="picker-loading">
                    <i class="bi bi-search"></i>
                    Ketik minimal 1 karakter untuk mencari barang...
                </div>
            `);
            currentSelectorRow = null;
        });
    });
</script>
@endpush
