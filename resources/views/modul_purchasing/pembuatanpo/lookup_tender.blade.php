{{-- ===== MODAL CARI TENDER / KONTRAK ===== --}}
<div class="modal fade picker-modal" id="modalCariTender" tabindex="-1" data-bs-backdrop="false" style="z-index: 999999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                        <i class="bi bi-briefcase fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Cari Tender / Kontrak Project</h5>
                        <small class="text-muted">Pilih tender/kontrak untuk menghubungkan pengadaan bahan ini</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnClearTender" class="picker-clear-btn d-none">
                        <i class="bi bi-x-circle me-1"></i>Hapus Pilihan
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="tenderSearchInput" class="form-control picker-search-input" placeholder="Cari nama tender, nomor kontrak, atau customer...">
                </div>
                <div id="tenderResultsList" class="picker-results-list">
                    <div class="picker-loading">
                        <i class="bi bi-arrow-repeat spin-icon"></i>
                        Memuat data tender...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const SEARCH_URL  = '{{ route('po.search-tender') }}';
        const modalEl     = document.getElementById('modalCariTender');
        const searchInput = document.getElementById('tenderSearchInput');
        const resultsList = document.getElementById('tenderResultsList');
        const clearBtn    = document.getElementById('btnClearTender');

        let debounceTimer = null;
        let lastQuery     = null;
        let currentPage   = 1;
        let isFetching    = false;

        function renderTenders(items, hasMore, page) {
            let html = '';
            
            if (window.tenderTargetPrefix === 'filter_' && page === 1) {
                html += `
                    <div class="picker-item border-bottom pb-2 mb-2" data-id="non_tender" data-name="Hanya Non-Tender (Operasional)">
                        <div class="picker-icon" style="background:rgba(100,116,139,0.15);color:#64748b;"><i class="bi bi-x-circle-fill"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name fw-bold text-secondary">Hanya Non-Tender (Operasional)</div>
                            <div class="picker-sub">Tampilkan PO pengeluaran umum biasa tanpa proyek</div>
                        </div>
                    </div>
                `;
            }

            if (!items || !items.length) {
                if (window.tenderTargetPrefix === 'filter_' && page === 1) {
                    resultsList.innerHTML = html + `<div class="picker-empty py-2 small text-muted text-center">Tidak ada data proyek/tender yang cocok</div>`;
                } else {
                    resultsList.innerHTML = `<div class="picker-empty"><i class="bi bi-briefcase-slash"></i>Tidak ada data tender yang cocok</div>`;
                }
                return;
            }

            html += items.map(td => {
                let dispName = td.nama_tender + (td.nomor_kontrak && td.nomor_kontrak !== '-' ? ' (' + td.nomor_kontrak + ')' : '');
                return `
                    <div class="picker-item" data-id="${td.id}" data-name="${escapeHtml(dispName)}">
                        <div class="picker-icon" style="background:rgba(14,165,233,0.1);color:#0ea5e9;"><i class="bi bi-briefcase"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">${escapeHtml(td.nama_tender)}</div>
                            <div class="picker-sub">
                                <i class="bi bi-file-earmark-text me-1"></i>Kontrak: ${escapeHtml(td.nomor_kontrak)}
                                &nbsp;·&nbsp;
                                <i class="bi bi-building me-1"></i>Customer: ${escapeHtml(td.customer)}
                            </div>
                        </div>
                        <span class="picker-badge bg-info-subtle text-info fw-bold">${td.currency} ${td.nilai_estimasi}</span>
                    </div>`;
            }).join('');

            // Append pagination buttons
            if (page > 1 || hasMore) {
                html += `
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top px-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1.5 rounded-pill btn-prev-page" ${page <= 1 ? 'disabled' : ''}>
                            <i class="bi bi-arrow-left me-1"></i>Sebelumnya
                        </button>
                        <span class="small fw-bold text-secondary">Halaman ${page}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1.5 rounded-pill btn-next-page" ${!hasMore ? 'disabled' : ''}>
                            Selanjutnya<i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                `;
            }

            resultsList.innerHTML = html;

            resultsList.querySelectorAll('.picker-item').forEach(item => {
                item.addEventListener('click', () => {
                    let prefix = window.tenderTargetPrefix || '';
                    let targetIdEl = document.getElementById(prefix + 'tender_id');
                    let targetDispEl = document.getElementById(prefix + 'tender_display');
                    
                    if (targetIdEl) targetIdEl.value = item.dataset.id;
                    if (targetDispEl) targetDispEl.value = item.dataset.name;

                    if (clearBtn) clearBtn.classList.remove('d-none');
                    let bsInst = bootstrap.Modal.getInstance(modalEl);
                    if (bsInst) bsInst.hide();
                });
            });

            // Bind click handlers for pagination buttons
            const prevBtn = resultsList.querySelector('.btn-prev-page');
            const nextBtn = resultsList.querySelector('.btn-next-page');
            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    fetchTenders(searchInput.value.trim(), page - 1);
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    fetchTenders(searchInput.value.trim(), page + 1);
                });
            }
        }

        function fetchTenders(q, page = 1) {
            if (isFetching) return;
            isFetching = true;
            currentPage = page;

            resultsList.innerHTML = `<div class="picker-loading"><i class="bi bi-arrow-repeat spin-icon"></i>Mencari data tender...</div>`;
            $.ajax({
                url: SEARCH_URL,
                data: { q: q, page: page },
                dataType: 'json',
                success: function(data) {
                    isFetching = false;
                    renderTenders(data.items, data.has_more, data.current_page);
                },
                error: function() {
                    isFetching = false;
                    resultsList.innerHTML = `<div class="picker-empty"><i class="bi bi-exclamation-triangle"></i>Gagal memuat data tender</div>`;
                }
            });
        }

        // Delegated click handler to open Tender picker modal reliably
        $(document).on('click', '#tender_display, #btnOpenTenderPicker, #edit_tender_display, #btnOpenEditTenderPicker, #filter_tender_display, #btnOpenFilterTenderPicker', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).attr('id') || '';
            if (id.includes('edit_') || id.includes('Edit')) {
                window.tenderTargetPrefix = 'edit_';
            } else if (id.includes('filter_') || id.includes('Filter')) {
                window.tenderTargetPrefix = 'filter_';
            } else {
                window.tenderTargetPrefix = '';
            }

            let modalTarget = document.getElementById('modalCariTender');
            if (modalTarget) {
                if (modalTarget.parentNode !== document.body) {
                    document.body.appendChild(modalTarget);
                }

                let currentVal = document.getElementById(window.tenderTargetPrefix + 'tender_id')?.value;
                if (clearBtn) {
                    if (currentVal) {
                        clearBtn.classList.remove('d-none');
                    } else {
                        clearBtn.classList.add('d-none');
                    }
                }

                let bsModal = bootstrap.Modal.getOrCreateInstance(modalTarget, { backdrop: false, keyboard: true });
                bsModal.show();
            }
        });

        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', () => {
                if (searchInput) searchInput.value = '';
                lastQuery = null;
                currentPage = 1;
            });

            modalEl.addEventListener('shown.bs.modal', () => {
                if (searchInput) searchInput.focus();
                fetchTenders('', 1);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const q = e.target.value.trim();
                debounceTimer = setTimeout(() => {
                    lastQuery = q;
                    fetchTenders(q, 1);
                }, 250);
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                let prefix = window.tenderTargetPrefix || '';
                let targetIdEl = document.getElementById(prefix + 'tender_id');
                let targetDispEl = document.getElementById(prefix + 'tender_display');
                if (targetIdEl) targetIdEl.value = '';
                if (targetDispEl) targetDispEl.value = '';

                clearBtn.classList.add('d-none');
                let bsInst = bootstrap.Modal.getInstance(modalEl);
                if (bsInst) bsInst.hide();
            });
        }
    })();
</script>
@endpush
