{{-- ===== MODAL CARI PO (LOOKUP PO) ===== --}}
<style>
    .gr-picker-dialog {
        max-width: 540px;
        width: 100%;
        margin: 1.5rem auto;
    }
    @media (max-width: 576px) {
        .gr-picker-dialog {
            max-width: calc(100% - 24px) !important;
            margin: auto !important;
        }
    }
    .gr-picker-content {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(24px) !important;
        -webkit-backdrop-filter: blur(24px) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 20px !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8) !important;
        overflow: hidden;
    }
    .gr-picker-item {
        background: rgba(30, 41, 59, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 14px !important;
        padding: 14px 16px !important;
        margin-bottom: 10px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        color: #ffffff !important;
    }
    .gr-picker-item:hover,
    .gr-picker-item:active {
        background: rgba(30, 41, 59, 1) !important;
        border-color: #3b82f6 !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;
    }
    .gr-picker-item.is-selected {
        background: rgba(37, 99, 235, 0.18) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6 !important;
    }
    .gr-picker-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .gr-picker-close-btn:hover,
    .gr-picker-close-btn:active {
        background: rgba(239, 68, 68, 0.25);
        border-color: rgba(239, 68, 68, 0.5);
        color: #ef4444;
        transform: scale(1.06);
    }
    .gr-picker-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .gr-picker-search-wrap i.bi-search {
        position: absolute;
        left: 14px;
        font-size: 0.92rem;
        pointer-events: none;
    }
    .gr-picker-search-input {
        background: rgba(30, 41, 59, 0.7) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 50px !important;
        color: #f8fafc !important;
        padding: 9px 40px 9px 38px !important;
        font-size: 0.88rem !important;
        transition: all 0.2s ease;
    }
    .gr-picker-search-input:focus {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        color: #ffffff !important;
    }
    .gr-picker-footer-btn {
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #cbd5e1 !important;
        font-size: 0.88rem !important;
        transition: all 0.2s ease;
    }
    .gr-picker-footer-btn:hover,
    .gr-picker-footer-btn:active {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }
</style>

<div class="modal fade picker-modal" id="modalCariPO" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 999999 !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable gr-picker-dialog" style="z-index: 1000000 !important;">
        <div class="modal-content border-0 shadow-2xl gr-picker-content">
            <div class="modal-header border-0 pb-2 px-3 px-md-4 pt-3 pt-md-4 d-flex align-items-start justify-content-between">
                <div class="d-flex align-items-center gap-2.5 flex-grow-1 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #38bdf8;">
                        <i class="bi bi-cart-check fs-5"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.1rem; letter-spacing: -0.2px;">Cari Purchase Order (PO)</h5>
                        <small class="text-white-50">Saring berdasarkan nomor PO atau nama supplier</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <button type="button" class="gr-picker-close-btn" onclick="window.closePOPickerModal()" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body px-3 px-md-4 py-3">
                <div id="panelCariPO">
                    <div class="gr-picker-search-wrap mb-3">
                        <i class="bi bi-search text-primary"></i>
                        <input type="text" id="poSearchInput" class="form-control gr-picker-search-input" placeholder="Cari nomor PO atau nama supplier...">
                    </div>
                    <div id="poResultsList" class="picker-results-list" style="max-height: 52vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <div class="picker-loading text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            <span class="text-white-50">Memuat data PO...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 px-md-4 pb-3 pb-md-4">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2.5 d-flex align-items-center justify-content-center gap-2 fw-semibold gr-picker-footer-btn" onclick="window.closePOPickerModal()">
                    <i class="bi bi-x-circle fs-6"></i>
                    <span>Tutup Pencarian</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    if (typeof window.escapeHtml !== 'function') {
        window.escapeHtml = function (str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };
    }

    window.openPOPickerModal = function () {
        const modalEl = document.getElementById('modalCariPO');
        if (!modalEl) return;
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        modalEl.style.display = 'flex';
        modalEl.style.alignItems = 'center';
        modalEl.style.justifyContent = 'center';
        modalEl.classList.add('show');
        modalEl.style.zIndex = '999999';
        modalEl.style.backgroundColor = 'rgba(15, 23, 42, 0.75)';
        modalEl.style.backdropFilter = 'blur(8px)';
        modalEl.style.webkitBackdropFilter = 'blur(8px)';

        const searchInput = document.getElementById('poSearchInput');
        if (searchInput) {
            searchInput.value = '';
            setTimeout(() => searchInput.focus(), 150);
        }

        window.fetchPOs('', $('#po_id').val() || $('#edit_po_id').val());
    };

    window.closePOPickerModal = function () {
        const modalEl = document.getElementById('modalCariPO');
        if (!modalEl) return;
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        if ($('#modalTambahGR').hasClass('show') || $('#modalEditGR').hasClass('show')) {
            document.body.classList.add('modal-open');
        }
    };

    // Close on backdrop click
    $(document).on('click', '#modalCariPO', function (e) {
        if ($(e.target).closest('.modal-content').length === 0) {
            window.closePOPickerModal();
        }
    });

    // Close on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#modalCariPO').hasClass('show')) {
            window.closePOPickerModal();
        }
    });

    window.selectPOItem = function (el) {
        const itemEl = $(el);
        const id = itemEl.attr('data-id');
        const no = itemEl.attr('data-no');
        const supplier = itemEl.attr('data-supplier');

        if (!id) return;

        if ($('#modalEditGR').hasClass('show')) {
            $('#edit_po_display').val(no + ' (' + supplier + ')');
            $('#edit_po_id').val(id).trigger('change');
            if (typeof window.loadEditPODetails === 'function') {
                window.loadEditPODetails(id);
            }
        } else {
            $('#po_display').val(no + ' (' + supplier + ')');
            $('#po_id').val(id).trigger('change');
            if (typeof window.loadPODetails === 'function') {
                window.loadPODetails(id);
            }
        }

        window.closePOPickerModal();
    };

    window.fetchPOs = function (search = '', currentVal = '') {
        const SEARCH_URL = '{{ route("gr.active-pos") }}';
        const resultsList = document.getElementById('poResultsList');
        if (!resultsList) return;

        resultsList.innerHTML = `
            <div class="picker-loading text-center py-5">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-white-50">Memuat data PO...</span>
            </div>
        `;

        $.get(SEARCH_URL, { search: search }, function (data) {
            const escapeHtml = window.escapeHtml || function (s) { return s || ''; };
            if (!data || data.length === 0) {
                resultsList.innerHTML = `
                    <div class="picker-empty text-center py-5">
                        <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                            <i class="bi bi-inbox fs-2"></i>
                        </div>
                        <h6 class="text-white fw-bold mb-1">PO Tidak Ditemukan</h6>
                        <p class="mb-0 text-white-50 small">Tidak ditemukan Purchase Order aktif yang cocok.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach(function (po) {
                const isSelected = String(po.id) === String(currentVal);
                const selectedClass = isSelected ? 'is-selected' : '';
                const isOrdered = po.status === 'Ordered';

                html += `
                    <div class="gr-picker-item ${selectedClass} d-flex align-items-center gap-3" data-id="${po.id}" data-no="${escapeHtml(po.nomor_po)}" data-supplier="${escapeHtml(po.supplier_name)}" onclick="window.selectPOItem(this)">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #38bdf8;">
                            <i class="bi bi-file-earmark-text fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold font-monospace" style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.74rem;">
                                    ${escapeHtml(po.nomor_po)}
                                </span>
                                <span class="badge rounded-pill px-2 py-0.5" style="background: ${isOrdered ? 'rgba(16, 185, 129, 0.2)' : 'rgba(14, 165, 233, 0.2)'}; color: ${isOrdered ? '#34d399' : '#38bdf8'}; border: 1px solid ${isOrdered ? 'rgba(16, 185, 129, 0.35)' : 'rgba(14, 165, 233, 0.35)'}; font-size: 0.68rem; font-weight: 600;">
                                    <i class="bi ${isOrdered ? 'bi-check2-circle' : 'bi-clock-history'} me-1"></i>${po.status}
                                </span>
                            </div>
                            <div class="fw-bold mt-1.5 text-truncate" style="font-size: 0.95rem; color: #ffffff !important;">
                                ${escapeHtml(po.supplier_name)}
                            </div>
                            <div class="mt-1 d-flex align-items-center gap-2" style="font-size: 0.75rem; color: #94a3b8 !important;">
                                <span><i class="bi bi-calendar3 me-1 text-primary"></i>Tanggal PO: <span style="color: #e2e8f0;">${po.tanggal_po}</span></span>
                            </div>
                        </div>
                        ${isSelected ? `
                            <span class="badge rounded-pill ms-auto px-2.5 py-1.5 fw-bold flex-shrink-0" style="background: #2563eb; color: #ffffff; font-size: 0.75rem;">
                                <i class="bi bi-check-lg me-1"></i>Terpilih
                            </span>
                        ` : `
                            <i class="bi bi-chevron-right ms-auto flex-shrink-0" style="color: #64748b; font-size: 0.9rem;"></i>
                        `}
                    </div>
                `;
            });

            resultsList.innerHTML = html;
        }).fail(function () {
            resultsList.innerHTML = `
                <div class="picker-empty text-danger text-center py-5">
                    <i class="bi bi-exclamation-triangle fs-2"></i>
                    <p class="mb-0 mt-2">Gagal mengambil data dari server.</p>
                </div>
            `;
        });
    };

    $(document).ready(function () {
        let poDebounce = null;
        $(document).on('input', '#poSearchInput', function () {
            clearTimeout(poDebounce);
            let val = $(this).val().trim();
            poDebounce = setTimeout(() => {
                window.fetchPOs(val, $('#po_id').val());
            }, 300);
        });
    });
</script>
@endpush
