{{-- ===== MODAL CARI GUDANG (LOOKUP GUDANG) ===== --}}
@php
    $activeBranchId   = session('active_branch_id');
    $isBranchActive   = !empty($activeBranchId);
    $activeCompName   = session('active_company_name') ?: (auth()->user()?->company?->nama_perusahaan ?? 'PT Aktif');
    
    $activeBranchName = session('active_branch_name');
    if ($isBranchActive && empty($activeBranchName)) {
        $activeBranchName = \App\Models\Branch::find($activeBranchId)?->nama_cabang ?? 'Cabang Aktif';
    }

    $activeScopeName  = $isBranchActive ? $activeBranchName : $activeCompName;
    $activeScopeType  = $isBranchActive ? 'Cabang' : 'PT';

    // Clean redundant label (prevent "Cabang Cabang Limo")
    $scopeDisplayLabel = $activeScopeName;
    if ($isBranchActive && !preg_match('/^cabang\b/i', $scopeDisplayLabel)) {
        $scopeDisplayLabel = 'Cabang ' . $scopeDisplayLabel;
    }
@endphp

<style>
    .gr-picker-dialog {
        max-width: 520px;
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
        border-color: #f59e0b !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3) !important;
    }
    .gr-picker-item.is-selected {
        background: rgba(245, 158, 11, 0.18) !important;
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 1px #f59e0b !important;
    }
    .gr-picker-icon-box {
        width: 44px;
        height: 44px;
        background: rgba(245, 158, 11, 0.15);
        border: 1px solid rgba(245, 158, 11, 0.3);
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
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
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

<div class="modal fade picker-modal" id="modalCariGudang" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 999999 !important;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable gr-picker-dialog" style="z-index: 1000000 !important;">
        <div class="modal-content border-0 shadow-2xl gr-picker-content">
            <!-- Modal Header with Ambient Glow -->
            <div class="modal-header border-0 pb-2 px-3 px-md-4 pt-3 pt-md-4 d-flex align-items-start justify-content-between">
                <div class="d-flex align-items-center gap-2.5 flex-grow-1 min-w-0">
                    <div class="gr-picker-icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bi bi-house-door-fill fs-5 text-warning"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <h5 class="modal-title fw-bold text-white mb-0 text-truncate" style="font-size: 1.1rem; letter-spacing: -0.2px;">
                            Cari Gudang - {{ $activeScopeName }}
                        </h5>
                        <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                            @if($isBranchActive)
                                <span class="badge rounded-pill px-2.5 py-1 text-white shadow-2xs" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); font-size: 0.72rem; font-weight: 600;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $scopeDisplayLabel }}
                                </span>
                                <span class="badge rounded-pill bg-white bg-opacity-10 text-white-50 px-2 py-0.5" style="font-size: 0.68rem;">
                                    {{ $activeCompName }}
                                </span>
                            @else
                                <span class="badge rounded-pill px-2.5 py-1 text-white shadow-2xs" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); font-size: 0.72rem; font-weight: 600;">
                                    <i class="bi bi-building-fill me-1"></i>PT: {{ $activeScopeName }} (Pusat)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modern Prominent Close Pill (Touch-friendly 38x38px) -->
                <button type="button" class="gr-picker-close-btn flex-shrink-0 ms-2" onclick="window.closeGudangPickerModal()" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Body with Search & Results -->
            <div class="modal-body px-3 px-md-4 py-3">
                <div class="gr-picker-search-wrap mb-3">
                    <i class="bi bi-search text-warning"></i>
                    <input type="text" id="gudangSearchInput" class="form-control gr-picker-search-input" placeholder="Ketik nama / kode gudang...">
                </div>

                <div id="gudangResultsList" class="picker-results-list" style="max-height: 52vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <div class="picker-loading text-center py-5">
                        <div class="spinner-border spinner-border-sm text-warning me-2"></div>
                        <span class="text-white-50">Memuat data gudang...</span>
                    </div>
                </div>
            </div>

            <!-- Mobile & Desktop Footer with Easy Close Button -->
            <div class="modal-footer border-0 pt-0 px-3 px-md-4 pb-3 pb-md-4">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2.5 d-flex align-items-center justify-content-center gap-2 fw-semibold gr-picker-footer-btn" onclick="window.closeGudangPickerModal()">
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
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };
    }

    window.openGudangPickerModal = function () {
        const modalEl = document.getElementById('modalCariGudang');
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

        const searchInput = document.getElementById('gudangSearchInput');
        if (searchInput) {
            searchInput.value = '';
            setTimeout(() => searchInput.focus(), 150);
        }

        window.fetchGudangs('', $('#gudang_id').val() || $('#edit_gudang_id').val());
    };

    window.closeGudangPickerModal = function () {
        const modalEl = document.getElementById('modalCariGudang');
        if (!modalEl) return;
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        if ($('#modalTambahGR').hasClass('show') || $('#modalEditGR').hasClass('show')) {
            document.body.classList.add('modal-open');
        }
    };

    // Close on backdrop click
    $(document).on('click', '#modalCariGudang', function (e) {
        if ($(e.target).closest('.modal-content').length === 0) {
            window.closeGudangPickerModal();
        }
    });

    // Close on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#modalCariGudang').hasClass('show')) {
            window.closeGudangPickerModal();
        }
    });

    window.selectGudangItem = function (el) {
        const itemEl = $(el);
        const id = itemEl.attr('data-id');
        const nama = itemEl.attr('data-nama');

        if (!id) return;

        if ($('#modalEditGR').hasClass('show')) {
            $('#edit_gudang_display').val(nama);
            $('#edit_gudang_id').val(id).trigger('change');
            let currentPoId = $('#edit_po_id').val();
            if (currentPoId && typeof window.loadEditPODetails === 'function') {
                window.loadEditPODetails(currentPoId);
            }
        } else {
            $('#gudang_display').val(nama);
            $('#gudang_id').val(id).trigger('change');
            let currentPoId = $('#po_id').val();
            if (currentPoId && typeof window.loadPODetails === 'function') {
                window.loadPODetails(currentPoId);
            }
        }

        window.closeGudangPickerModal();
    };

    window.fetchGudangs = function (search = '', currentVal = '') {
        const SEARCH_URL = '{{ route("gr.gudangs") }}';
        const resultsList = document.getElementById('gudangResultsList');
        if (!resultsList) return;

        resultsList.innerHTML = `
            <div class="picker-loading text-center py-5">
                <div class="spinner-border spinner-border-sm text-warning me-2"></div>
                <span class="text-white-50">Memuat data gudang...</span>
            </div>
        `;

        $.get(SEARCH_URL, { search: search }, function (data) {
            if (!data || data.length === 0) {
                resultsList.innerHTML = `
                    <div class="picker-empty text-center py-5">
                        <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 text-warning mb-3">
                            <i class="bi bi-house-x fs-2"></i>
                        </div>
                        <h6 class="text-white fw-bold mb-1">Gudang Tidak Ditemukan</h6>
                        <p class="mb-0 text-white-50 small">Tidak ditemukan gudang aktif untuk <b>${escapeHtml('{{ $scopeDisplayLabel }}')}</b>.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach(function (g) {
                const isSelected = String(g.id) === String(currentVal);
                const selectedClass = isSelected ? 'is-selected' : '';

                html += `
                    <div class="gr-picker-item ${selectedClass} d-flex align-items-center gap-3" data-id="${g.id}" data-nama="${escapeHtml(g.nama_gudang)}" onclick="window.selectGudangItem(this)">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24;">
                            <i class="bi bi-house-door-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold fs-6 text-truncate" style="color: #ffffff !important;">${escapeHtml(g.nama_gudang)}</span>
                                <span class="badge ${g.is_pusat ? 'bg-primary bg-opacity-25 text-primary border border-primary border-opacity-35' : 'bg-info bg-opacity-25 text-info border border-info border-opacity-35'} rounded-pill" style="font-size: 0.68rem; font-weight: 600;">
                                    <i class="bi ${g.is_pusat ? 'bi-building' : 'bi-geo-alt'} me-1"></i>${escapeHtml(g.branch_name)}
                                </span>
                            </div>
                            <div class="mt-1 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.75rem; color: #94a3b8 !important;">
                                <span>Kode: <span class="badge rounded-pill font-monospace" style="background: rgba(255, 255, 255, 0.1); color: #e2e8f0;">${escapeHtml(g.kode_gudang)}</span></span>
                                <span>•</span>
                                <span><i class="bi bi-building me-1"></i>${escapeHtml(g.company_name)}</span>
                            </div>
                        </div>
                        ${isSelected ? `
                            <span class="badge rounded-pill ms-auto px-2.5 py-1.5 fw-bold flex-shrink-0" style="background: #f59e0b; color: #0f172a; font-size: 0.75rem;">
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
        let gudangDebounce = null;
        $(document).on('input', '#gudangSearchInput', function () {
            clearTimeout(gudangDebounce);
            let val = $(this).val().trim();
            gudangDebounce = setTimeout(() => {
                window.fetchGudangs(val, $('#gudang_id').val() || $('#edit_gudang_id').val());
            }, 300);
        });
    });
</script>
@endpush
