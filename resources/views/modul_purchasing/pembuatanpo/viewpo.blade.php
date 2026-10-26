<x-layouts title="Purchase Order (PO)">

    @push('styles')
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Barang Dashboard CSS for Tab Filter Pills -->
    <link rel="stylesheet" href="{{ asset('css/barang_dashboard.css?v=' . time()) }}">
    <!-- PO Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/po.css?v=' . time()) }}">
    @endpush

    <!-- Header Table Card — DataTables server-side, tbody populated via AJAX -->
    <x-table_card tableId="tablePO" title="Purchase Order (PO)" icon="bi-cart-check"
        btnTarget="{{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-po')) ? '#modalTambahPO' : '' }}"
        btnText="Buat PO">

        <x-slot name="extraActions">
            <button type="button"
                class="btn btn-outline-secondary rounded-pill d-flex align-items-center gap-2 px-3 position-relative"
                data-bs-toggle="modal" data-bs-target="#modalFilterPO"
                style="height: 38px; font-weight: 600; font-size: 0.85rem; border-color: #cbd5e1;">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
                <span id="filter-badge" class="position-absolute top-0 start-100 translate-middle p-1.5 bg-primary border border-light rounded-circle d-none">
                    <span class="visually-hidden">Filter Aktif</span>
                </span>
            </button>
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Nomor PO</th>
            <th class="py-3">Tanggal</th>
            <th class="py-3">Supplier</th>
            <th class="py-3 text-end">Grand Total</th>
            <th class="py-3 text-center">Status</th>
            <th width="10%" class="text-center py-3">Aksi</th>
        </x-slot>

        {{-- DataTables server-side: tbody diisi via AJAX endpoint /purchase-order/datatable --}}
    </x-table_card>

    <!-- Modal Filter PO (Cinematic Mobile & Desktop) -->
    <div class="modal fade" id="modalFilterPO" tabindex="-1" aria-labelledby="modalFilterPOLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 620px !important;">
            <div class="modal-content shadow-lg border-0">
                <!-- Mobile Drag Indicator -->
                <div class="mobile-drag-pill d-sm-none"></div>

                <div class="modal-header border-bottom-0 pb-2 px-4 pt-3 pt-sm-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="filter-cinematic-icon">
                            <i class="bi bi-sliders2-vertical"></i>
                        </div>
                        <div>
                            <h5 class="modal-title filter-cinematic-title" id="modalFilterPOLabel">Filter Purchase Order</h5>
                            <span class="filter-cinematic-subtitle">Sesuaikan kriteria & periode pencarian data PO</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <!-- Status Filter -->
                        <div class="col-md-6 col-12">
                            <label class="form-label filter-cinematic-label mb-1.5">
                                <i class="bi bi-tag-fill me-1.5 text-primary"></i> Status PO
                            </label>
                            <select id="filter_status" class="form-select form-select-custom cinematic-select">
                                <option value="">Semua Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Ordered">Ordered</option>
                                <option value="Partially Received">Partially Received</option>
                                <option value="Completed">Completed</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Mata Uang Filter -->
                        <div class="col-md-6 col-12">
                            <label class="form-label filter-cinematic-label mb-1.5">
                                <i class="bi bi-cash-stack me-1.5 text-info"></i> Mata Uang
                            </label>
                            <select id="filter_currency" class="form-select form-select-custom cinematic-select">
                                <option value="">Semua Mata Uang</option>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}">{{ $c->kode_currency }} - {{ $c->nama_currency }} ({{ $c->simbol }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tender Filter -->
                        <div class="col-12">
                            <label class="form-label filter-cinematic-label mb-1.5">
                                <i class="bi bi-briefcase-fill me-1.5 text-warning"></i> Referensi Tender / Project
                            </label>
                            <input type="hidden" id="filter_tender_id">
                            <div class="input-group">
                                <input type="text" id="filter_tender_display" class="form-control form-control-custom cinematic-input" placeholder="Semua Pembelian / Cari Tender..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                <button type="button" class="btn btn-primary px-3 cinematic-tender-btn" id="btnOpenFilterTenderPicker" title="Cari Tender">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tanggal Mulai -->
                        <div class="col-6">
                            <label class="form-label filter-cinematic-label mb-1.5">
                                <i class="bi bi-calendar-event me-1.5 text-primary"></i> Tanggal Mulai
                            </label>
                            <input type="date" id="filter_tgl_mulai" class="form-control form-control-custom cinematic-input">
                        </div>
                        
                        <!-- Tanggal Selesai -->
                        <div class="col-6">
                            <label class="form-label filter-cinematic-label mb-1.5">
                                <i class="bi bi-calendar-check me-1.5 text-primary"></i> Tanggal Selesai
                            </label>
                            <input type="date" id="filter_tgl_selesai" class="form-control form-control-custom cinematic-input">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                    <div class="filter-footer-grid">
                        <!-- Pada HP: Tombol Terapkan Utama di paling atas/prioritas, Reset & Export di bawahnya seimbang -->
                        <button type="button" id="btn-apply-filter" class="btn btn-cinematic-apply d-sm-none w-100">
                            <i class="bi bi-check2-circle me-1.5 fs-6"></i> Terapkan Filter
                        </button>
                        
                        <div class="filter-footer-secondary">
                            <button type="button" id="btn-reset-filter" class="btn btn-cinematic-reset flex-fill" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-counterclockwise me-1.5"></i> Reset Filter
                            </button>
                            <button type="button" id="btn-export-excel" class="btn btn-cinematic-export flex-fill">
                                <i class="bi bi-file-earmark-excel-fill me-1.5"></i> Export Excel
                            </button>
                        </div>

                        <button type="button" id="btn-apply-filter-desktop" class="btn btn-cinematic-apply d-none d-sm-flex px-4">
                            <i class="bi bi-check2-circle me-1.5 fs-6"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modals Section -->
    @include('modul_purchasing.pembuatanpo.tambah')
    @include('modul_purchasing.pembuatanpo.edit')
    @include('modul_purchasing.pembuatanpo.detail')

    <!-- Custom Lightbox Preview Foto Barang -->
    <div id="customPhotoLightbox" class="custom-lightbox-overlay" style="display: none;">
        <div class="custom-lightbox-content">
            <button type="button" class="custom-lightbox-close">&times;</button>
            <div id="lightboxCarousel" class="carousel slide w-100 h-100" data-bs-ride="false">
                <div class="carousel-inner" id="lightbox_carousel_inner"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
    @include('modul_purchasing.pembuatanpo.lookup_supplier')
    @include('modul_purchasing.pembuatanpo.lookup_pr')
    @include('modul_purchasing.pembuatanpo.lookup_pajak')
    @include('modul_purchasing.pembuatanpo.lookup_tenor')
    @include('modul_purchasing.pembuatanpo.lookup_metode')
    @include('modul_purchasing.pembuatanpo.lookup_barang')
    @include('modul_purchasing.pembuatanpo.lookup_tender')

    @push('scripts')
    <!-- Select2 Searchable Dropdown JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // AJAX endpoint URL for barang search picker — replaces old global @@json($barang)
        const SEARCH_BARANG_URL = '{{ route('po.search-barang') }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Global PPh mapping & default COA
        window.pphCoaMap = @json($pphCoaMap ?? new \stdClass());
        window.defaultPphCoaId = {{ $defaultPphCoaId ?? 'null' }};

        // Helper to force Select2 dropdown to ALWAYS render directly below the selection element
        function forceSelect2DropdownBelow(s2) {
            if (!s2 || !s2.dropdown) return;
            let proto = Object.getPrototypeOf(s2.dropdown);
            if (proto && !proto._hasFixedBelowPositioning) {
                proto._hasFixedBelowPositioning = true;
                proto._positionDropdown = function () {
                    if (!this.$container || !this.$dropdown || !this.$dropdownContainer) return;

                    let offset = this.$container.offset();
                    if (!offset) return;

                    let containerHeight = this.$container.outerHeight(false) || 34;
                    let containerWidth = this.$container.outerWidth(false);
                    let css = {
                        left: offset.left,
                        top: offset.top + containerHeight,
                        width: containerWidth ? (containerWidth + 'px') : '100%',
                        'min-width': containerWidth ? (containerWidth + 'px') : '180px'
                    };

                    let $offsetParent = this.$dropdownParent;
                    if ($offsetParent && $offsetParent.length) {
                        if ($offsetParent.css('position') === 'static') {
                            $offsetParent = $offsetParent.offsetParent();
                        }
                        let parentOffset = { top: 0, left: 0 };
                        if ($.contains(document.body, $offsetParent[0]) || $offsetParent[0].isConnected) {
                            parentOffset = $offsetParent.offset();
                        }
                        css.top -= parentOffset.top;
                        css.left -= parentOffset.left;

                        // Perbaiki scroll offset jika parent atau ancestor modal di-scroll
                        let scrollY = ($offsetParent[0] ? ($offsetParent[0].scrollTop || 0) : 0);
                        let scrollX = ($offsetParent[0] ? ($offsetParent[0].scrollLeft || 0) : 0);
                        if (!scrollY) {
                            let $scrollingModal = $offsetParent.closest('.modal');
                            if ($scrollingModal.length && $offsetParent[0] === $scrollingModal[0]) {
                                scrollY = $scrollingModal[0].scrollTop || 0;
                                scrollX = $scrollingModal[0].scrollLeft || 0;
                            }
                        }
                        css.top += scrollY;
                        css.left += scrollX;
                    }

                    if (this.$dropdown[0]) {
                        this.$dropdown[0].classList.remove('select2-dropdown--above');
                        this.$dropdown[0].classList.add('select2-dropdown--below');
                    }
                    if (this.$container[0]) {
                        this.$container[0].classList.remove('select2-container--above');
                        this.$container[0].classList.add('select2-container--below');
                    }

                    this.$dropdownContainer.css({
                        top: css.top + 'px',
                        left: css.left + 'px',
                        width: css.width,
                        'min-width': css['min-width'],
                        position: 'absolute',
                        'z-index': 1090
                    });
                };
            }
        }

        // ===== SEARCHABLE COA (AKUN UTANG PPH) SELECT2 INITIALIZER =====
        window.initCoaSelect2 = function($elem, $modal, placeholder = '-- Pilih Akun PPh --') {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let parentCol = $this.closest('.po-pph-col');
                let targetParent = parentCol.length ? parentCol : ($this.closest('.po-pph-box').length ? $this.closest('.po-pph-box') : (($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body))));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: placeholder,
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true
                });

                let s2 = $this.data('select2');
                forceSelect2DropdownBelow(s2);

                $this.on('select2:open', function() {
                    let data = $(this).data('select2');
                    if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                        data.dropdown._positionDropdown();
                    }
                    setTimeout(function() {
                        if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                            data.dropdown._positionDropdown();
                        }
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') ||
                                          document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Cari akun (kode / nama, misal: 2105, PPh)...');
                            searchField.focus();
                        }
                    }, 30);
                });

                $this.on('select2:select', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // ===== SEARCHABLE JENIS PPH (WITHHOLDING TAX) SELECT2 INITIALIZER =====
        window.initPphPajakSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let parentCol = $this.closest('.po-pph-col');
                let targetParent = parentCol.length ? parentCol : ($this.closest('.po-pph-box').length ? $this.closest('.po-pph-box') : (($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body))));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: 'Tanpa PPh',
                    allowClear: false,
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true
                });

                let s2 = $this.data('select2');
                forceSelect2DropdownBelow(s2);

                $this.on('select2:open', function() {
                    let data = $(this).data('select2');
                    if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                        data.dropdown._positionDropdown();
                    }
                    setTimeout(function() {
                        if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                            data.dropdown._positionDropdown();
                        }
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') ||
                                          document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Cari jenis PPh (misal: 1,5%, PPh 23)...');
                            searchField.focus();
                        }
                    }, 30);
                });

                $this.on('select2:select select2:clear', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                            $(self).trigger('change');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Click fallback handler untuk membuka select2 pada area PPh jika diklik
        $(document).on('click', '.po-pph-box .select2-selection', function() {
            let $select = $(this).closest('.select2-container').prev('select');
            if ($select.length && !$select.prop('disabled')) {
                let s2 = $select.data('select2');
                if (s2 && !s2.isOpen()) {
                    $select.select2('open');
                }
            }
        });

        // ===== SHARED SELECT2 INITIALIZER =====
        window.initSelect2Safely = function($elem, $modal, placeholder = '-- Pilih --') {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let localTd = $this.closest('td');
                let targetParent = localTd.length ? localTd : (($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body)));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: placeholder,
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true
                });

                let s2 = $this.data('select2');
                forceSelect2DropdownBelow(s2);

                $this.on('select2:open', function() {
                    let data = $(this).data('select2');
                    if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                        data.dropdown._positionDropdown();
                    }
                    if (localTd.length) {
                        localTd.closest('tr').css({ 'z-index': 1050, 'position': 'relative' });
                        localTd.css({ 'z-index': 1055, 'position': 'relative' });
                    }
                    setTimeout(function() {
                        if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                            data.dropdown._positionDropdown();
                        }
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') ||
                                          document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            let ph = 'Ketik untuk mencari...';
                            if ($this.hasClass('select2-expense-type') || $this.hasClass('exp-jenis-select')) {
                                ph = 'Cari jenis biaya (misal: Pengiriman, Bongkar)...';
                            } else if (placeholder && placeholder !== '-- Pilih --') {
                                ph = 'Cari ' + placeholder.replace(/--|\//g, '').trim() + '...';
                            }
                            searchField.setAttribute('placeholder', ph);
                            searchField.focus();
                        }
                    }, 30);
                });

                $this.on('select2:close', function() {
                    if (localTd.length) {
                        localTd.closest('tr').css({ 'z-index': '', 'position': '' });
                        localTd.css({ 'z-index': '', 'position': '' });
                    }
                });

                $this.on('select2:select', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // ===== SEARCHABLE PAJAK (PPN) SELECT2 INITIALIZER =====
        window.initPajakSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let localTd = $this.closest('td');
                let targetParent = localTd.length ? localTd : (($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body)));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: 'Non PPN (0%)',
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0, // ALWAYS show search input so user can type and search immediately
                    closeOnSelect: true
                });

                let s2 = $this.data('select2');
                forceSelect2DropdownBelow(s2);

                $this.on('select2:open', function() {
                    let data = $(this).data('select2');
                    if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                        data.dropdown._positionDropdown();
                    }
                    if (localTd.length) {
                        localTd.closest('tr').css({ 'z-index': 1050, 'position': 'relative' });
                        localTd.css({ 'z-index': 1055, 'position': 'relative' });
                    }
                    setTimeout(function() {
                        if (data && data.dropdown && typeof data.dropdown._positionDropdown === 'function') {
                            data.dropdown._positionDropdown();
                        }
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') ||
                                          document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Cari PPN (misal: 11%, 12%)...');
                            searchField.focus();
                        }
                    }, 30);
                });

                $this.on('select2:close', function() {
                    if (localTd.length) {
                        localTd.closest('tr').css({ 'z-index': '', 'position': '' });
                        localTd.css({ 'z-index': '', 'position': '' });
                    }
                });

                $this.on('select2:select', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Scroll listener on modal to maintain dropdown glued right below
        $(document).on('scroll', '.modal', function() {
            $('.select2-hidden-accessible').each(function() {
                let s2 = $(this).data('select2');
                if (s2 && typeof s2.isOpen === 'function' && s2.isOpen() && s2.dropdown && typeof s2.dropdown._positionDropdown === 'function') {
                    s2.dropdown._positionDropdown();
                }
            });
        });

        // Click handler fallback to guarantee select2 opens on click
        $(document).on('click', 'td[data-label="PPN"] .select2-container:not(.select2-container--open)', function () {
            let $select = $(this).closest('td').find('.select-item-ppn');
            if ($select.length && !$select.data('select2-opening-guard')) {
                $select.data('select2-opening-guard', true);
                setTimeout(function() { $select.data('select2-opening-guard', false); }, 250);
                try {
                    $select.select2('open');
                } catch (err) {}
            }
        });

        // Prevent Bootstrap modal from stealing focus from Select2 search input
        document.addEventListener('focusin', function (e) {
            if (e.target && e.target.closest && (e.target.closest('.select2-container') || e.target.closest('.select2-dropdown') || e.target.closest('.select2-search'))) {
                e.stopImmediatePropagation();
            }
        }, true);

        // Close Select2 immediately upon clicking any option
        $(document).on('mouseup touchend click', '.select2-results__option', function () {
            setTimeout(function () {
                $('.select2-container--open').prev('.select2-hidden-accessible').each(function () {
                    try {
                        $(this).select2('close');
                    } catch (e) {}
                });
            }, 20);
        });

        // Close Select2 when clicking outside
        $(document).on('mousedown touchstart', function (e) {
            if ($('.select2-container--open').length > 0) {
                if (!$(e.target).closest('.select2-container').length && 
                    !$(e.target).closest('.select2-dropdown').length &&
                    !$(e.target).closest('td[data-label="PPN"]').length &&
                    !$(e.target).closest('td[data-label="Jenis Biaya"]').length) {
                    $('.select2-container--open').prev('.select2-hidden-accessible').each(function () {
                        try {
                            $(this).select2('close');
                        } catch (err) {}
                    });
                }
            }
        });

        // Close Select2 when modal is closed
        $(document).on('hidden.bs.modal', '.modal', function () {
            $('.select2-hidden-accessible').each(function () {
                try {
                    $(this).select2('close');
                } catch (err) {}
            });
        });

        window.escapeHtml = function(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        // Numeric Separator Helpers (Always 2 Decimals for Currency & Grand Totals)
        window.formatNumber = function(num) {
            if (num === null || num === undefined || isNaN(num)) return '0,00';
            return parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        window.formatKurs = function(num) {
            if (num === null || num === undefined || isNaN(num)) return '1,00';
            return parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        };
        window.formatQty = function(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            return parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });
        };
        window.formatDiscountVal = function(num, type = 'percent') {
            if (num === null || num === undefined || isNaN(num)) return '0';
            let val = parseFloat(num || 0);
            if (type === 'percent') {
                if (val === 0) return '0';
                if (val % 1 === 0) return val.toFixed(0);
                let str = val.toFixed(2).replace(/\.?0+$/, '');
                return str.replace('.', ',');
            }
            return formatNumber(val);
        };
        window.unformatNumber = function(str) {
            if (str === null || str === undefined || str === '') return 0;
            if (typeof str === 'number') return isNaN(str) ? 0 : str;
            let s = str.toString().trim();
            if (s === '') return 0;

            // Indonesian ERP Standard:
            // If comma exists, comma is decimal separator (e.g. "18.600,50" -> 18600.5, "672,67" -> 672.67, "15,5" -> 15.5)
            if (s.includes(',')) {
                s = s.replace(/\./g, '').replace(/,/g, '.');
            } else {
                // If NO comma exists, check if it has a single dot decimal (e.g. "12.50", "0.75", "16250.5540", "1.2345")
                if (/^0\.\d+$/.test(s) || (/^\d+\.\d{1,4}$/.test(s) && !/^\d{1,3}\.\d{3}$/.test(s))) {
                    // keep dot as decimal
                } else {
                    // All dots are thousands separators (e.g. "18.600" -> 18600, "1.500.000" -> 1500000, "597" -> 597)
                    s = s.replace(/\./g, '');
                }
            }
            let res = parseFloat(s);
            return isNaN(res) ? 0 : res;
        };

        // Auto format kurs input on blur
        $(document).on('blur', '#kurs_input, #edit_kurs_input', function() {
            let val = ($(this).val() || '').trim();
            if (val === '' || unformatNumber(val) === 0) {
                $(this).val('1,00');
            } else {
                $(this).val(formatKurs(unformatNumber(val)));
            }
            if (typeof calculateTotals === 'function' && $('#modalTambahPO').hasClass('show')) {
                calculateTotals();
            }
            if (typeof calculateEditTotals === 'function' && $('#modalEditPO').hasClass('show')) {
                calculateEditTotals();
            }
        });

        // Convert dot key (from standard numpad / keyboard) to decimal comma automatically
        $(document).on('keydown', '.number-format', function(e) {
            if (e.key === '.' || e.key === 'Decimal') {
                let val = $(this).val();
                if (!val.includes(',')) {
                    e.preventDefault();
                    let start = this.selectionStart !== null ? this.selectionStart : val.length;
                    let end = this.selectionEnd !== null ? this.selectionEnd : val.length;
                    let newVal = val.substring(0, start) + ',' + val.substring(end);
                    $(this).val(newVal);
                    if (this.setSelectionRange) {
                        this.setSelectionRange(start + 1, start + 1);
                    }
                    $(this).trigger('input');
                } else {
                    e.preventDefault();
                }
            }
        });

        // Auto-clear zero (0 / 0,00) on focus/click so user doesn't have to backspace manually
        $(document).on('focus', '.price-input, .discount-val-input, .number-format', function() {
            if ($(this).prop('readonly') || $(this).prop('disabled')) return;
            let val = ($(this).val() || '').trim();
            if (val === '0,00' || val === '0' || val === '0.00' || unformatNumber(val) === 0) {
                $(this).val('');
            } else {
                $(this).select();
            }
        });

        $(document).on('click', '.price-input, .discount-val-input, .number-format', function() {
            if ($(this).prop('readonly') || $(this).prop('disabled')) return;
            let val = ($(this).val() || '').trim();
            if (val === '0,00' || val === '0' || val === '0.00' || unformatNumber(val) === 0) {
                $(this).val('');
            }
        });

        // Restore formatting on blur if left empty or format decimal places
        $(document).on('blur', '.price-input', function() {
            let val = ($(this).val() || '').trim();
            if (val === '' || unformatNumber(val) === 0) {
                $(this).val('0,00');
            } else {
                $(this).val(formatNumber(unformatNumber(val)));
            }
            if (typeof calculateTotals === 'function' && $('#modalTambahPO').hasClass('show')) {
                calculateTotals();
            }
            if (typeof calculateEditTotals === 'function' && $('#modalEditPO').hasClass('show')) {
                calculateEditTotals();
            }
        });

        $(document).on('blur', '.discount-val-input', function() {
            let val = ($(this).val() || '').trim();
            let tr = $(this).closest('tr');
            let discType = tr.find('.discount-type-input').val() || 'percent';
            if (val === '' || unformatNumber(val) === 0) {
                $(this).val('0');
            } else {
                $(this).val(formatDiscountVal(unformatNumber(val), discType));
            }
            if (typeof calculateTotals === 'function' && $('#modalTambahPO').hasClass('show')) {
                calculateTotals();
            }
            if (typeof calculateEditTotals === 'function' && $('#modalEditPO').hasClass('show')) {
                calculateEditTotals();
            }
        });

        $(document).on('blur', '.exp-nom-input, #down_payment, #edit_down_payment', function() {
            let val = ($(this).val() || '').trim();
            if (val === '' || unformatNumber(val) === 0) {
                $(this).val('0,00');
            } else {
                $(this).val(formatNumber(unformatNumber(val)));
            }
            if (typeof calculateTotals === 'function' && $('#modalTambahPO').hasClass('show')) {
                calculateTotals();
            }
            if (typeof calculateEditTotals === 'function' && $('#modalEditPO').hasClass('show')) {
                calculateEditTotals();
            }
        });

        // Apply Real-time Thousands Separator & Decimal Handling (Supports up to Trillions & Preserves Cursor)
        $(document).on('input', '.number-format', function() {
            let raw = $(this).val();
            if (raw === undefined || raw === null) return;
            if (raw === '') return;

            let cursor = this.selectionStart;
            let originalLen = raw.length;

            // Split by decimal comma if present
            let parts = raw.split(',');
            let intStr = parts[0].replace(/\D/g, ''); // remove non-digits (dots, etc.)
            let decStr = parts.length > 1 ? parts.slice(1).join('').replace(/\D/g, '').substring(0, 4) : null;

            if (intStr === '' && decStr === null) {
                $(this).val('');
                return;
            }

            // High-precision regex formatting (no integer overflow even for trillions/quadrillions)
            let cleanInt = intStr.replace(/^0+(?=\d)/, ''); // strip leading zeroes if multi-digit
            let formattedInt = cleanInt !== '' ? cleanInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : (intStr === '' ? '' : '0');
            let result = formattedInt;
            if (decStr !== null) {
                result += ',' + decStr;
            }
            $(this).val(result);

            // Restore smooth cursor position
            if (cursor !== null && this.setSelectionRange) {
                let newLen = result.length;
                let newCursor = cursor + (newLen - originalLen);
                if (newCursor < 0) newCursor = 0;
                if (newCursor > newLen) newCursor = newLen;
                this.setSelectionRange(newCursor, newCursor);
            }
        });

        // Helper to open picker modals — globally accessible on window
        window.openPicker = function (modalElement, onOpenCallback) {
            if (!modalElement) return;
            const m = bootstrap.Modal.getOrCreateInstance(modalElement, { keyboard: true });
            m.show();
            if (typeof onOpenCallback === 'function') {
                onOpenCallback();
            }
        };

        $(document).ready(function () {
            // Append all PO modals directly to document.body to prevent backdrop stacking clipping
            ['modalTambahPO', 'modalEditPO', 'modalDetailPO', 'modalCariSupplier', 'modalCariPr', 'modalCariPajak', 'modalCariTenor', 'modalCariMetode', 'modalCariBarang', 'modalCariTender', 'modalFilterPO'].forEach(function(id) {
                let el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });

            // Select2 initialized only if select elements require it (none for filter tender anymore)

            // ===================================================================
            // SERVER-SIDE DATATABLES — PO Table
            // All filtering, sorting & pagination happen in the database.
            // Safe for millions of rows — no full data load into PHP or browser.
            // ===================================================================
            let poTable = $('#tablePO').DataTable({
                processing: false, // Disabled processing spinner entirely to prevent annoyance
                serverSide: true,
                stateSave: true,
                stateLoadCallback: function(settings) {
                    try {
                        let key = 'DataTables_' + settings.sInstance + '_' + location.pathname;
                        let saved = JSON.parse(localStorage.getItem(key));
                        if (saved && saved.columns && saved.columns.length !== settings.aoColumns.length) {
                            localStorage.removeItem(key);
                            return null;
                        }
                        return saved;
                    } catch (e) {
                        return null;
                    }
                },
                deferRender: true,
                ajax: {
                    url: '{{ route('po.datatable') }}',
                    type: 'GET',
                    data: function(d) {
                        d.filter_status = $('#filter_status').val();
                        d.filter_currency = $('#filter_currency').val();
                        d.filter_tender = $('#filter_tender_id').val();
                        d.filter_tgl_mulai = $('#filter_tgl_mulai').val();
                        d.filter_tgl_selesai = $('#filter_tgl_selesai').val();
                    },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    error: function (xhr) {
                        console.error('DataTables AJAX error:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'DT_RowIndex',      name: 'DT_RowIndex',      orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_po_badge',   name: 'nomor_po',          className: '' },
                    { data: 'tanggal_fmt',      name: 'tanggal_po',        className: 'fw-medium text-secondary' },
                    { data: 'supplier_name',    name: 'supplier_name',     className: 'fw-semibold' },
                    { data: 'grand_total_info', name: 'grand_total_info',  className: 'text-end' },
                    { data: 'status',           name: 'status',            className: 'text-center' },
                    { data: 'id',               name: 'id',                orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    {
                        targets: 1, // Nomor PO — badge
                        render: function (data, type, row) {
                            let branchLabel = row.cabang_name && row.cabang_name !== '-' ? `Cabang ${row.cabang_name}` : 'PT Pusat';
                            let html = `<span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill shadow-sm">${escapeHtml(data)}</span>`;
                            html += `<br><span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2 py-0.5 rounded-pill mt-1" style="font-size:0.7rem;"><i class="bi bi-building me-1"></i>${escapeHtml(branchLabel)}</span>`;
                            if (row.tender) {
                                html += `<br><span class="badge bg-info bg-opacity-10 text-info fw-semibold px-2 py-1 rounded-pill mt-1" style="font-size:0.72rem;" title="${escapeHtml(row.tender.nama_tender)}"><i class="bi bi-briefcase me-1"></i>${escapeHtml(row.tender.nama_tender)}</span>`;
                            }
                            return html;
                        }
                    },
                    {
                        targets: 4, // Grand Total — dengan IDR fallback & DP info
                        render: function (data) {
                            let html = `<span class="fw-bold text-success">${escapeHtml(data.simbol)} ${formatNumber(data.grand_total)}</span>`;
                            if (data.kode_currency !== 'IDR') {
                                html += `<br><small class="text-dark fw-medium">Rp ${formatNumber(data.grand_total_idr)}</small>`;
                            }
                            if (data.pph_nominal > 0) {
                                let tarifTxt = data.tarif_pph ? ` (${data.tarif_pph}%)` : '';
                                html += `<br><span class="badge px-2 py-0.5 rounded-pill mt-1" style="font-size:0.7rem; background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);" title="Potongan PPh: ${escapeHtml(data.nama_pph || 'PPh')}"><i class="bi bi-percent me-0.5"></i>PPh: -${escapeHtml(data.simbol)} ${formatNumber(data.pph_nominal)}</span>`;
                            }
                            if (data.down_payment > 0) {
                                html += `<br><span class="badge px-2 py-0.5 rounded-pill mt-1" style="font-size:0.72rem; background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25);" title="Uang Muka (DP)"><i class="bi bi-wallet2 me-1"></i>DP: ${escapeHtml(data.simbol)} ${formatNumber(data.down_payment)}</span>`;
                                html += `<br><small class="text-muted d-block mt-0.5" style="font-size:0.73rem;">Sisa: ${escapeHtml(data.simbol)} ${formatNumber(data.sisa_bayar)}</small>`;
                            }
                            return html;
                        }
                    },
                    {
                        targets: 5, // Status — badge berwarna
                        render: function (data) {
                            const statusMap = {
                                'Draft':              `<span class="badge px-3 py-1 rounded-pill fw-semibold shadow-sm" style="background: rgba(249, 115, 22, 0.15) !important; color: #ea580c !important; border: 1px solid rgba(249, 115, 22, 0.25);"><i class="bi bi-file-earmark-text me-1"></i>Draft</span>`,
                                'Pending':            `<span class="badge bg-warning-soft text-warning px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-clock-history me-1"></i>Pending</span>`,
                                'Ordered':            `<span class="badge bg-success-soft text-success px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-check-circle me-1"></i>Ordered</span>`,
                                'Partially Received': `<span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-hourglass-split me-1"></i>Partially Received</span>`,
                                'Received':           `<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-check-all me-1"></i>Received</span>`,
                                'Rejected':           `<span class="badge bg-danger-soft text-danger px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-x-circle me-1"></i>Rejected</span>`,
                            };
                            return statusMap[data] || `<span class="badge bg-secondary-soft text-secondary px-3 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-dash-circle me-1"></i>${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        targets: 6, // Aksi — tombol aksi dinamis berdasarkan permission
                        render: function (data, type, row) {
                            let html = `<div class="d-flex justify-content-center gap-1">`;
                            html += `<button class="btn btn-sm btn-light text-primary border btn-detail" data-id="${data}" title="Lihat Detail"><i class="bi bi-eye-fill"></i></button>`;
                            html += `<a href="/purchase-order/${data}/print" target="_blank" class="btn btn-sm btn-light text-info border" title="Cetak PO"><i class="bi bi-printer-fill"></i></a>`;
                            if (row.can_edit) {
                                html += `<button class="btn btn-sm btn-light text-warning border btn-edit" data-id="${data}" title="Edit PO"><i class="bi bi-pencil-fill"></i></button>`;
                            }
                            if (row.can_reopen) {
                                html += `<button class="btn btn-sm btn-light text-warning-emphasis border btn-reopen-po" data-id="${data}" data-nama="${escapeHtml(row.nomor_po_badge)}" title="Kembalikan ke Draft / Buka PO"><i class="bi bi-arrow-counterclockwise"></i></button>`;
                            }
                            if (row.can_delete) {
                                html += `<button class="btn btn-sm btn-light text-danger border btn-delete" data-id="${data}" data-nama="${escapeHtml(row.nomor_po_badge)}" title="Hapus"><i class="bi bi-trash-fill"></i></button>`;
                            }
                            html += `</div>`;
                            return html;
                        }
                    },
                ],
                order: [[2, 'desc']], // Default sort: tanggal PO terbaru di atas
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-cart-check fs-2 d-block mb-2 opacity-25"></i>Belum ada data Purchase Order.</div>',
                    zeroRecords: 'Tidak ditemukan data Purchase Order yang cocok'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
            });

            // ===== INJECT TAB FILTER PILLS =====
            const tabHtml = `
                <div class="brg-tab-wrapper" id="poTabFilter">
                    <button class="brg-tab-pill po-tab-pill active" data-status="">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Semua
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Draft">
                        <i class="bi bi-file-earmark-text"></i> Draft
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Pending">
                        <i class="bi bi-clock-history"></i> Pending
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Ordered">
                        <i class="bi bi-cart-check"></i> Ordered
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Partially Received">
                        <i class="bi bi-hourglass-split"></i> Partially Received
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Received">
                        <i class="bi bi-check-all"></i> Received
                    </button>
                    <button class="brg-tab-pill po-tab-pill" data-status="Rejected">
                        <i class="bi bi-x-circle"></i> Rejected
                    </button>
                </div>
            `;

            // Inject tabs above the table responsive container inside the card body (guaranteed to exist in DOM)
            const $tblResponsive = $('#tablePO').closest('.table-responsive');
            $tblResponsive.before(tabHtml);

            // Mobile scroll hint above table
            const scrollHintHtml = `
                <div class="po-mobile-scroll-hint d-lg-none">
                    <i class="bi bi-arrows-expand text-primary" style="transform: rotate(45deg); font-size: 0.85rem;"></i>
                    <span>Tabel dapat digeser ke samping untuk melihat rincian & tombol aksi &rarr;</span>
                </div>
            `;
            $tblResponsive.before(scrollHintHtml);

            // Tab click handler with smooth auto-scroll into view
            $(document).on('click', '.po-tab-pill', function() {
                $('.po-tab-pill').removeClass('active');
                $(this).addClass('active');
                
                // Ensure clicked pill is smoothly visible in the horizontal swipe area
                if (typeof this.scrollIntoView === 'function') {
                    this.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }

                const statusVal = $(this).data('status');
                $('#filter_status').val(statusVal);
                updateFilterBadge();
                poTable.ajax.reload();
            });

            // Two-way sync: Update active tab when select input changes in filter modal
            $('#filter_status').on('change', function() {
                const val = $(this).val() || "";
                $('.po-tab-pill').removeClass('active');
                const $active = $(`.po-tab-pill[data-status="${val}"]`).addClass('active');
                if ($active.length && $active[0] && typeof $active[0].scrollIntoView === 'function') {
                    $active[0].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            });

            // Mouse drag-to-scroll support for touchscreens and desktop mice
            const poTab = document.getElementById('poTabFilter');
            if (poTab) {
                let isDown = false;
                let startX;
                let scrollLeft;

                poTab.addEventListener('mousedown', (e) => {
                    isDown = true;
                    poTab.classList.add('grabbing');
                    startX = e.pageX - poTab.offsetLeft;
                    scrollLeft = poTab.scrollLeft;
                });
                poTab.addEventListener('mouseleave', () => {
                    isDown = false;
                    poTab.classList.remove('grabbing');
                });
                poTab.addEventListener('mouseup', () => {
                    isDown = false;
                    poTab.classList.remove('grabbing');
                });
                poTab.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - poTab.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    poTab.scrollLeft = scrollLeft - walk;
                });
            }

            // Mouse drag-to-scroll & Touch support for Table PO
            const tableWrapper = document.querySelector('#tablePO')?.closest('.table-responsive');
            if (tableWrapper) {
                let isTableDown = false;
                let tableStartX;
                let tableScrollLeft;

                tableWrapper.addEventListener('mousedown', (e) => {
                    if (e.target.closest('button, a, select, input, .btn')) return;
                    isTableDown = true;
                    tableWrapper.classList.add('table-grabbing');
                    tableStartX = e.pageX - tableWrapper.offsetLeft;
                    tableScrollLeft = tableWrapper.scrollLeft;
                });
                tableWrapper.addEventListener('mouseleave', () => {
                    isTableDown = false;
                    tableWrapper.classList.remove('table-grabbing');
                });
                tableWrapper.addEventListener('mouseup', () => {
                    isTableDown = false;
                    tableWrapper.classList.remove('table-grabbing');
                });
                tableWrapper.addEventListener('mousemove', (e) => {
                    if (!isTableDown) return;
                    e.preventDefault();
                    const x = e.pageX - tableWrapper.offsetLeft;
                    const walk = (x - tableStartX) * 1.5;
                    tableWrapper.scrollLeft = tableScrollLeft - walk;
                });
            }

            // Expose globally — called by tambah.blade.php, edit.blade.php, etc. after save/update
            window.reloadTablePO = function () {
                let container = $(poTable.table().container());
                container.addClass('dt-silent'); // Re-enable silent mode to hide spinner
                poTable.ajax.reload(function() {
                    container.removeClass('dt-silent');
                }, false);
            };

            // Silent auto-refresh every 10 seconds (real-time behind the scenes - no spinner popup)
            setInterval(function () {
                let isSearching = $('div.dataTables_filter input').is(':focus');
                let isModalOpen = $('.modal.show').length > 0;
                if (!document.hidden && !isSearching && !isModalOpen && $.fn.DataTable.isDataTable('#tablePO')) {
                    let container = $(poTable.table().container());
                    container.addClass('dt-silent'); // Enable silent mode
                    
                    poTable.ajax.reload(function() {
                        container.removeClass('dt-silent'); // Disable silent mode when done
                    }, false);
                }
            }, 10000);

            // Automatic redraw removed to enforce validation on Apply button click

            // Reset filters
            $('#btn-reset-filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_currency').val('');
                $('#filter_tender_id').val('');
                $('#filter_tender_display').val('');
                $('#filter_tgl_mulai').val('');
                $('#filter_tgl_selesai').val('');
                updateFilterBadge();
                poTable.ajax.reload();
            });

            // Apply filters with validation
            $(document).on('click', '#btn-apply-filter, #btn-apply-filter-desktop', function(e) {
                e.preventDefault();
                
                const tglMulai = $('#filter_tgl_mulai').val() || '';
                const tglSelesai = $('#filter_tgl_selesai').val() || '';

                if (!tglMulai || !tglSelesai) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang Tanggal Wajib Diisi!',
                        text: 'Silakan tentukan Tanggal Mulai dan Tanggal Selesai terlebih dahulu untuk memfilter data PO (Maksimal 6 bulan).',
                        confirmButtonColor: '#0d6efd',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                    return;
                }

                let start = new Date(tglMulai);
                let end = new Date(tglSelesai);
                let diffTime = Math.abs(end - start);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays > 186) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang Tanggal Melebihi Batas!',
                        text: 'Rentang tanggal filter maksimal adalah 6 bulan (186 hari). Pilihan Anda adalah ' + diffDays + ' hari.',
                        confirmButtonColor: '#0d6efd',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                    return;
                }

                updateFilterBadge();
                poTable.ajax.reload();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFilterPO')).hide();
            });

            // Export Excel click handler
            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                
                const status = $('#filter_status').val() || '';
                const currency = $('#filter_currency').val() || '';
                const tender = $('#filter_tender_id').val() || '';
                const tglMulai = $('#filter_tgl_mulai').val() || '';
                const tglSelesai = $('#filter_tgl_selesai').val() || '';

                if (!tglMulai || !tglSelesai) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang Tanggal Wajib Diisi!',
                        text: 'Silakan pilih Tanggal Mulai dan Tanggal Selesai terlebih dahulu untuk melakukan export data PO (Maksimal 6 bulan).',
                        confirmButtonColor: '#0d6efd',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                    return;
                }

                let start = new Date(tglMulai);
                let end = new Date(tglSelesai);
                let diffTime = Math.abs(end - start);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays > 186) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang Tanggal Melebihi Batas!',
                        text: 'Rentang tanggal export maksimal adalah 6 bulan (186 hari). Pilihan Anda adalah ' + diffDays + ' hari.',
                        confirmButtonColor: '#0d6efd',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                    return;
                }

                // Construct export URL
                let url = '{{ route('po.export') }}' + 
                    '?tgl_mulai=' + encodeURIComponent(tglMulai) + 
                    '&tgl_selesai=' + encodeURIComponent(tglSelesai) + 
                    '&filter_status=' + encodeURIComponent(status) + 
                    '&filter_currency=' + encodeURIComponent(currency) + 
                    '&filter_tender=' + encodeURIComponent(tender);

                // Show processing indicator
                Swal.fire({
                    title: 'Memproses Export...',
                    text: 'Mohon tunggu sebentar, file Excel sedang disiapkan.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                });

                fetch(url)
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        if (!response.ok || contentType.includes('application/json')) {
                            let errorData = {};
                            try {
                                errorData = await response.json();
                            } catch(e) {}
                            let message = errorData.message || 'Terjadi kesalahan saat memproses data PO.';
                            throw new Error(message);
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Create download link
                        let downloadUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = 'Export_PO_' + tglMulai.replace(/-/g, '') + '_sd_' + tglSelesai.replace(/-/g, '') + '.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Berhasil!',
                            text: 'File Purchase Order berhasil diunduh.',
                            timer: 2000,
                            showConfirmButton: false,
                            didOpen: () => {
                                const c = document.querySelector('.swal2-container');
                                if (c) c.style.setProperty('z-index', '999999', 'important');
                            }
                        });
                        
                        // Close modal
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFilterPO')).hide();
                    })
                    .catch(error => {
                        // Error Alert
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Gagal!',
                            text: error.message || 'Gagal memproses unduhan Excel.',
                            confirmButtonColor: '#0d6efd',
                            didOpen: () => {
                                const c = document.querySelector('.swal2-container');
                                if (c) c.style.setProperty('z-index', '999999', 'important');
                            }
                        });
                    });
            });

            function updateFilterBadge() {
                const status = $('#filter_status').val();
                const currency = $('#filter_currency').val();
                const tender = $('#filter_tender_id').val();
                const tglMulai = $('#filter_tgl_mulai').val();
                const tglSelesai = $('#filter_tgl_selesai').val();
                
                if (status || currency || tender || tglMulai || tglSelesai) {
                    $('#filter-badge').removeClass('d-none');
                } else {
                    $('#filter-badge').addClass('d-none');
                }
            }

            // ===== DETAIL PO =====
            $(document).on('click', '.btn-detail', function (e) {
                e.preventDefault();
                let id = $(this).attr('data-id') || $(this).data('id');
                if (typeof window.openDetailPO === 'function') {
                    window.openDetailPO(id);
                } else {
                    console.error('window.openDetailPO belum tersedia. Pastikan detail.blade.php sudah di-include.');
                }
            });

            // ===== EDIT PO =====
            $(document).on('click', '.btn-edit', function (e) {
                e.preventDefault();
                let id = $(this).attr('data-id') || $(this).data('id');
                if (typeof window.openEditPO === 'function') {
                    window.openEditPO(id);
                } else {
                    console.error('window.openEditPO belum tersedia. Pastikan edit.blade.php sudah di-include.');
                }
            });

            // ===== DELETE PO =====
            $(document).on('click', '.btn-delete', function () {
                let id   = $(this).data('id');
                let nama = $(this).data('nama');

                Swal.fire({
                    title: 'Hapus Purchase Order?',
                    html: 'Anda akan menghapus PO nomor: <b>' + escapeHtml(nama) + '</b>.<br><small class="text-muted">Tindakan ini juga akan mengembalikan sisa qty pengajuan PR asal.</small>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/purchase-order/' + id + '/delete',
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1000,
                                    showConfirmButton: false
                                });
                                reloadTablePO();
                            },
                            error: function (err) {
                                let errorMsg = 'Terjadi kesalahan saat menghapus PO.';
                                if (err.status === 403) {
                                    errorMsg = '<b>Akses Ditolak:</b> Akun Anda tidak memiliki izin untuk menghapus Purchase Order.';
                                }
                                Swal.fire('Gagal!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // ===== KEMBALIKAN KE DRAFT / BUKA PO =====
            $(document).on('click', '.btn-reopen-po', function () {
                let id = $(this).data('id');
                let rawNama = $(this).data('nama') || '';
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = rawNama;
                let nama = tempDiv.textContent || tempDiv.innerText || 'PO';

                Swal.fire({
                    title: 'Kembalikan PO ke Draft?',
                    html: `Apakah Anda yakin ingin membuka kembali Purchase Order <b>${escapeHtml(nama)}</b> dan mengembalikannya ke status <b>Draft</b> agar dapat diedit kembali?<br><br>
                           <div class="alert alert-warning text-start small mb-0 p-2" style="font-size:0.8rem;">
                               <i class="bi bi-shield-exclamation me-1"></i> <b>Persyaratan Pembukaan PO:</b>
                               <ul class="mb-0 ps-3 mt-1">
                                   <li>Belum memiliki Penerimaan Barang (GR) aktif (atau harus dibatalkan).</li>
                                   <li>Belum masuk dalam transaksi Alokasi Biaya (atau harus dibatalkan).</li>
                                   <li>Belum memiliki transaksi Faktur Pembelian (Invoice).</li>
                               </ul>
                           </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Buka & Kembalikan ke Draft',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang memvalidasi persyaratan dan mengembalikan PO ke status Draft...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: '/purchase-order/' + id + '/reopen',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    html: res.message || 'Purchase Order berhasil dikembalikan ke status Draft.',
                                    icon: 'success'
                                });
                                reloadTablePO();
                                if ($('#modalDetailPO').hasClass('show')) {
                                    $('#modalDetailPO').modal('hide');
                                }
                            },
                            error: function (xhr) {
                                let errMsg = 'Terjadi kesalahan saat mengembalikan PO ke draft.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: 'Tidak Dapat Dibuka!',
                                    html: errMsg,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            // ===== PRODUCT IMAGE PREVIEW CAROUSEL (CUSTOM LIGHTBOX) =====
            $(document).on('click', '.product-img-wrapper', function (e) {
                e.preventDefault();
                let fotosData = $(this).attr('data-fotos') || $(this).data('fotos');
                let fotos = [];
                if (fotosData) {
                    try {
                        fotos = typeof fotosData === 'string' ? JSON.parse(fotosData) : fotosData;
                    } catch (err) {
                        console.error('Failed to parse fotos', err);
                    }
                }
                
                if (!Array.isArray(fotos)) {
                    fotos = [];
                }

                // Fallback to single img if empty
                if (fotos.length === 0) {
                    let src = $(this).find('img').attr('src');
                    if (src) {
                        fotos.push(src);
                    }
                }

                if (fotos.length === 0) return;

                let innerHtml = '';
                fotos.forEach((url, idx) => {
                    let active = idx === 0 ? 'active' : '';
                    innerHtml += `
                        <div class="carousel-item ${active}">
                            <img src="${url}" alt="Foto Barang">
                        </div>
                    `;
                });
                $('#lightbox_carousel_inner').html(innerHtml);

                if (fotos.length <= 1) {
                    $('#lightboxCarousel .carousel-control-prev, #lightboxCarousel .carousel-control-next').addClass('d-none');
                } else {
                    $('#lightboxCarousel .carousel-control-prev, #lightboxCarousel .carousel-control-next').removeClass('d-none');
                }

                // Append to body dynamically to break out of nested container stacking contexts
                let lb = document.getElementById('customPhotoLightbox');
                if (lb) {
                    if (lb.parentElement !== document.body) {
                        document.body.appendChild(lb);
                    }
                    $(lb).css('display', 'flex');
                    lb.offsetHeight; // force layout reflow
                    $(lb).addClass('show');
                }
            });

            // Close Custom Lightbox on Close Button or Clicking Backdrop
            $(document).on('click', '.custom-lightbox-close, .custom-lightbox-overlay', function (e) {
                if (e.target !== this && !$(e.target).hasClass('custom-lightbox-close')) return;
                $('#customPhotoLightbox').removeClass('show');
                setTimeout(function() {
                    $('#customPhotoLightbox').css('display', 'none');
                }, 200);
            });
        });
    </script>
    @endpush
</x-layouts>

