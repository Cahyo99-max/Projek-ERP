{{-- ===== MODAL CARI PR ===== --}}
<div class="modal fade picker-modal" id="modalCariPR" tabindex="-1" data-bs-backdrop="false" style="z-index: 999999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                        <i class="bi bi-file-earmark-text fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Cari PR (Purchase Request)</h5>
                        <small class="text-muted">Hanya PR yang sudah disetujui & masih ada sisa qty</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnClearPR" class="picker-clear-btn d-none">
                        <i class="bi bi-x-circle me-1"></i>Hapus Pilihan
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="prSearchInput" class="form-control picker-search-input" placeholder="Cari nomor PR, departemen, atau pengaju...">
                </div>
                <div id="prResultsList" class="picker-results-list">
                    <div class="picker-loading">
                        <i class="bi bi-arrow-repeat spin-icon"></i>
                        Memuat data PR...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const SEARCH_URL  = '{{ route('po.search-pr') }}';
        const modalEl     = document.getElementById('modalCariPR');
        const searchInput = document.getElementById('prSearchInput');
        const resultsList = document.getElementById('prResultsList');
        const clearBtn    = document.getElementById('btnClearPR');

        let debounceTimer = null;
        let lastQuery     = null;

        function getActivePrefix() {
            if ($('#modalEditPO').hasClass('show')) return 'edit_';
            if ($('#modalTambahPO').hasClass('show')) return '';
            return window.prTargetPrefix || '';
        }

        function renderPRs(items) {
            if (!items || !items.length) {
                resultsList.innerHTML = `<div class="picker-empty"><i class="bi bi-file-earmark-slash"></i>PR tidak ditemukan atau sudah selesai dibuatkan PO</div>`;
                return;
            }
            resultsList.innerHTML = items.map(pr => {
                let nomorPrEscaped = escapeHtml(pr.nomor_pr || '');
                let deptEscaped = escapeHtml(pr.department || '-');
                let reqEscaped = escapeHtml(pr.requester || '-');
                let tglEscaped = escapeHtml(pr.tanggal_pr || '-');

                return `
                    <div class="picker-item" data-id="${pr.id}" data-name="${nomorPrEscaped}">
                        <div class="picker-icon" style="background:rgba(13,202,240,0.1);color:#0dcaf0;"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">${nomorPrEscaped}</div>
                            <div class="picker-sub">
                                <i class="bi bi-diagram-3 me-1"></i>${deptEscaped}
                                &nbsp;·&nbsp;
                                <i class="bi bi-person me-1"></i>${reqEscaped}
                                &nbsp;·&nbsp;
                                <i class="bi bi-calendar3 me-1"></i>${tglEscaped}
                            </div>
                        </div>
                        <span class="picker-badge bg-success-subtle text-success">Approved</span>
                    </div>`;
            }).join('');
        }

        function fetchPRs(q) {
            resultsList.innerHTML = `<div class="picker-loading"><i class="bi bi-arrow-repeat spin-icon"></i>Mencari data PR...</div>`;
            fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(renderPRs)
            .catch(() => {
                resultsList.innerHTML = `<div class="picker-empty"><i class="bi bi-exclamation-triangle"></i>Gagal memuat data PR</div>`;
            });
        }

        $(document).on('click', '#pr_display, #btnOpenPRPicker, #edit_pr_display, #btnOpenEditPRPicker', function (e) {
            e.preventDefault();
            let id = $(this).attr('id') || '';
            window.prTargetPrefix = (id.includes('edit_') || id.includes('Edit')) ? 'edit_' : '';
            let prefix = getActivePrefix();
            
            let supId = document.getElementById(prefix + 'supplier_id')?.value;
            if (!supId) {
                Swal.fire({
                    title: 'Supplier Belum Dipilih!',
                    text: 'Silakan cari & pilih Supplier terlebih dahulu untuk memilih sumber PR.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Supplier Sekarang',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        let openSupBtn = prefix ? '#btnOpenEditSupplierPicker' : '#btnOpenSupplierPicker';
                        $(openSupBtn).trigger('click');
                    }
                });
                return false;
            }
            let targetModal = document.getElementById('modalCariPR');
            if (targetModal) {
                if (targetModal.parentNode !== document.body) {
                    document.body.appendChild(targetModal);
                }
                bootstrap.Modal.getOrCreateInstance(targetModal, { backdrop: false, keyboard: true }).show();
            }
        });

        // Select PR Item Delegated
        $(document).on('click', '#prResultsList .picker-item', function () {
            let id = $(this).attr('data-id') || $(this).data('id');
            let name = $(this).attr('data-name') || $(this).data('name');
            let prefix = getActivePrefix();

            $('#' + prefix + 'pr_id').val(id);
            $('#' + prefix + 'pr_display').val(name);
            $('#btnClearPR').removeClass('d-none');

            let targetModal = document.getElementById('modalCariPR');
            if (targetModal) {
                let m = bootstrap.Modal.getInstance(targetModal);
                if (m) m.hide();
            }

            // Direct loader call & fallback change trigger
            if (prefix === 'edit_') {
                if (typeof window.loadPrItemsEdit === 'function') {
                    window.loadPrItemsEdit(id);
                } else {
                    $('#edit_pr_id').trigger('change');
                }
            } else {
                if (typeof window.loadPrItemsTambah === 'function') {
                    window.loadPrItemsTambah(id);
                } else {
                    $('#pr_id').trigger('change');
                }
            }
        });

        // Reset & focus on show
        modalEl.addEventListener('show.bs.modal', () => {
            searchInput.value = '';
            lastQuery = null;
        });
        modalEl.addEventListener('shown.bs.modal', () => {
            searchInput.focus();
            fetchPRs('');
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const q = searchInput.value.trim();
                if (q === lastQuery) return;
                lastQuery = q;
                fetchPRs(q);
            }, 280);
        });

        clearBtn.addEventListener('click', () => {
            let prefix = getActivePrefix();
            document.getElementById(prefix + 'pr_id').value      = '';
            document.getElementById(prefix + 'pr_display').value = '';
            clearBtn.classList.add('d-none');
            // Reset PR items section
            $('#' + prefix + 'pr_items_card').addClass('d-none');
            $('#' + prefix + 'pr_items_tbody').empty();
            $('#' + prefix + 'po_details_tbody').empty();
            $('#' + prefix + 'btn_add_manual_row').prop('disabled', false).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            $('#' + prefix + 'manual_row_info').addClass('d-none');
            if (typeof calculateTotals === 'function') calculateTotals();
            if (typeof calculateEditTotals === 'function') calculateEditTotals();
        });
    })();
</script>
@endpush
