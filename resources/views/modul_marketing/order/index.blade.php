<x-layouts title="Order Penjualan & Katalog Kasir (POS)">

    @push('styles')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Tokopedia Style Sales Order CSS -->
    <link rel="stylesheet" href="{{ asset('css/sales_order_tokopedia.css?v=' . time()) }}">
    <style>
        @keyframes spinClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin-clockwise {
            display: inline-block;
            animation: spinClockwise 0.75s linear infinite;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            color: #64748b;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        @media (max-width: 575.98px) {
            .nav-tabs .nav-link {
                padding: 8px 10px;
                font-size: 0.8rem;
            }
        }
        .nav-tabs .nav-link:hover {
            color: #2563eb;
        }
        .nav-tabs .nav-link.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: transparent;
        }

        /* Order History Modern Card & Table */
        .order-history-card {
            background: var(--tkpd-card-bg, #ffffff);
            border: 1px solid var(--tkpd-card-border, #e2e8f0);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        [data-bs-theme="dark"] .order-history-card {
            background: #111827;
            border-color: #1f2937;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        #tableSalesOrders thead th {
            background: rgba(37, 99, 235, 0.05);
            color: #1e3a8a;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            border-top: none;
            border-bottom: 2px solid rgba(37, 99, 235, 0.15);
        }

        [data-bs-theme="dark"] #tableSalesOrders thead th {
            background: rgba(30, 58, 138, 0.35);
            color: #93c5fd;
            border-bottom: 2px solid rgba(37, 99, 235, 0.3);
        }

        #tableSalesOrders tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            font-size: 0.88rem;
        }

        [data-bs-theme="dark"] #tableSalesOrders tbody td {
            border-bottom: 1px solid rgba(31, 41, 55, 0.8);
            color: #e5e7eb;
        }

        #tableSalesOrders tbody tr:hover td {
            background-color: rgba(37, 99, 235, 0.025);
        }

        [data-bs-theme="dark"] #tableSalesOrders tbody tr:hover td {
            background-color: rgba(37, 99, 235, 0.08);
        }

        /* Action Buttons Group */
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .action-btn-group .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.15s ease;
        }
        .action-btn-group .btn:hover {
            transform: translateY(-1px);
        }
        .action-btn-group .btn-view-so {
            width: auto;
            padding: 0 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* DataTables Controls Styling */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 20px;
            padding: 4px 28px 4px 12px;
            font-size: 0.84rem;
            border: 1px solid var(--tkpd-card-border, #cbd5e1);
            background-color: var(--tkpd-card-bg, #ffffff);
            color: inherit;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.84rem;
            border: 1px solid var(--tkpd-card-border, #cbd5e1);
            background-color: var(--tkpd-card-bg, #ffffff);
            color: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(37, 99, 235, 0.1) !important;
            color: #2563eb !important;
            border-radius: 8px !important;
            border: 1px solid transparent !important;
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm 15mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body * {
                visibility: hidden !important;
            }
            .modal-backdrop, .modal-header, .modal-footer, .tkpd-cart-floating-btn, .sidebar, .navbar, .page-header {
                display: none !important;
            }
            #modalDetailOrder {
                position: static !important;
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }
            .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .modal-content {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            #printable_order_area, #printable_order_area * {
                visibility: visible !important;
            }
            #printable_order_area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #111827 !important;
                background: #ffffff !important;
            }
        }
    </style>
    @endpush

    <div class="container-fluid px-3 px-md-4 py-3">
        
        <!-- Header & Nav Tabs -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center">
                            <i class="bi bi-bag-check-fill fs-5"></i>
                        </span>
                        Order Penjualan 
                    </h4>
                </div>

            </div>

            <!-- Right Header Actions -->
            <div class="d-flex align-items-center gap-2">
                <!-- Cart Trigger Button in Header -->
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                    <i class="bi bi-cart3 fs-5"></i>
                    <span class="d-none d-sm-inline">Keranjang</span>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 font-monospace" id="cart_badge_header">0</span>
                </button>

                <!-- Tab Switcher -->
                <ul class="nav nav-tabs border-bottom-0" id="orderTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="katalog-tab" data-bs-toggle="tab" data-bs-target="#tab-katalog" type="button" role="tab">
                            <i class="bi bi-shop me-1.5"></i> Katalog & Kasir POS
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button" role="tab">
                            <i class="bi bi-clock-history me-1.5"></i> Riwayat Pesanan
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="orderTabContent">
            
            <!-- ===== TAB 1: KATALOG & KASIR POS (TOKOPEDIA STYLE - FULL WIDTH) ===== -->
            <div class="tab-pane fade show active" id="tab-katalog" role="tabpanel">
                
                <div class="row g-3">
                    
                    <!-- Search Filter & Product Grid (Full Width) -->
                    <div class="col-12">
                        
                        <!-- Top Search & Filter Bar -->
                        <div class="tkpd-search-container mb-3">
                            <div class="row g-2.5 align-items-center">
                                
                                <!-- Search Input (Full Width Clean Tokopedia Style) -->
                                <div class="col-12">
                                    <div class="tkpd-search-input-group">
                                        <i class="bi bi-search tkpd-search-icon"></i>
                                        <input type="text" class="form-control tkpd-search-input" id="catalog_search_input" placeholder="Cari nama barang, kode SKU, atau barcode...">
                                    </div>
                                </div>

                                <!-- Category Pills (Tokopedia Style Interactive Buttons) -->
                                <div class="col-12 pt-1">
                                    <div class="tkpd-category-pills">
                                        <button type="button" class="tkpd-cat-pill active" data-cat-id="all">
                                            <i class="bi bi-grid-fill me-1"></i> Semua Kategori
                                            <span class="badge bg-white text-dark ms-1 px-1.5 py-0.5 rounded-pill font-monospace small" style="font-size: 0.72rem;">{{ $barangList->count() }}</span>
                                        </button>
                                        @foreach($categories as $cat)
                                            @php
                                                $catCount = $barangList->where('kategori_barang_id', $cat->id)->count();
                                            @endphp
                                            <button type="button" class="tkpd-cat-pill" data-cat-id="{{ $cat->id }}">
                                                {{ $cat->nama_kategori }}
                                                @if($catCount > 0)
                                                    <span class="badge bg-primary-subtle text-primary ms-1 px-1.5 py-0.5 rounded-pill font-monospace small" style="font-size: 0.72rem;">{{ $catCount }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Product Grid Container (Instant Server-Side Render + Dynamic AJAX Support) -->
                        <div id="product_grid_container">
                            @if($barangList->isEmpty())
                                <div class="col-12 text-center py-5">
                                    <div class="mb-3 text-muted" style="font-size: 3rem;"><i class="bi bi-search"></i></div>
                                    <h6 class="fw-bold text-secondary">Produk Tidak Ditemukan</h6>
                                    <p class="text-muted small">Belum ada katalog produk yang tersedia untuk entitas ini.</p>
                                </div>
                            @else
                                <div class="tkpd-product-grid">
                                    @foreach($barangList as $p)
                                        @php
                                            $isJasa = ($p->tipe_item === 'Jasa');
                                            $isBundle = ($p->tipe_item === 'Bundle');
                                            $isOutOfStock = (!$isJasa && $p->total_stok <= 0);
                                            $photoSrc = $p->resolved_photo_url ?: "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300' fill='%23f1f5f9'><rect width='300' height='300' fill='%23f1f5f9'/><g transform='translate(110, 100)'><rect x='0' y='0' width='80' height='80' rx='16' fill='%23e2e8f0'/><path d='M25 50 L38 35 L48 45 L58 32 L68 46 L68 56 L25 56 Z' fill='%2394a3b8'/><circle cx='34' cy='28' r='6' fill='%23cbd5e1'/></g><text x='150' y='210' font-family='sans-serif' font-size='13' font-weight='600' fill='%2394a3b8' text-anchor='middle'>Tidak Ada Foto</text></svg>";
                                            $hRetail = (float)($p->harga_jual_retail ?? $p->harga_retail ?? 0);
                                            $hGrosir = (float)($p->harga_jual_grosir ?? $p->harga_grosir ?? $hRetail);
                                            $hTender = (float)($p->harga_jual_tender ?? $p->harga_tender ?? $hRetail);
                                            $activePrice = $hRetail;
                                        @endphp
                                        <div class="tkpd-card {{ $isOutOfStock ? 'out-of-stock' : '' }}" data-retail="{{ $hRetail }}" data-grosir="{{ $hGrosir }}" data-tender="{{ $hTender }}">
                                            <div class="tkpd-card-img-wrap btn-show-prod-detail cursor-pointer" data-id="{{ $p->id }}" title="Klik untuk lihat detail & foto">
                                                @if($isJasa)
                                                    <span class="tkpd-badge-stock bg-info text-white">Layanan / Jasa</span>
                                                @elseif(!$isOutOfStock)
                                                    <span class="tkpd-badge-stock bg-success text-white">Stok: {{ (float)$p->total_stok }}</span>
                                                @else
                                                    <span class="tkpd-badge-stock bg-danger text-white">Stok Habis</span>
                                                @endif

                                                @if($p->tipe_item === 'Bundle')
                                                    <span class="tkpd-badge-type bg-primary text-white">Bundle</span>
                                                @endif

                                                <img src="{{ $photoSrc }}" class="tkpd-card-img" alt="{{ $p->nama_barang }}" onerror="window.handleImgError(this)">
                                            </div>
                                            <div class="tkpd-card-body">
                                                <div>
                                                    <div class="tkpd-card-meta d-flex align-items-center justify-content-between gap-1 mb-1">
                                                        <span class="text-truncate text-muted" style="max-width: 65%; font-size: 0.72rem;">{{ $p->kategori ? $p->kategori->nama_kategori : '-' }}</span>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace px-1.5 py-0.5 rounded flex-shrink-0" style="font-size: 0.65rem;">{{ $p->kode_barang }}</span>
                                                    </div>
                                                    <div class="tkpd-card-title btn-show-prod-detail cursor-pointer" data-id="{{ $p->id }}" title="{{ $p->nama_barang }}">{{ $p->nama_barang }}</div>
                                                </div>
                                                <div class="mt-2">
                                                    <div class="tkpd-card-price tkpd-price-retail">
                                                        Rp {{ number_format($activePrice, 2, ',', '.') }}
                                                    </div>
                                                    @if($isOutOfStock)
                                                        <button class="tkpd-btn-add disabled" disabled title="Stok barang sedang kosong">
                                                            <i class="bi bi-slash-circle"></i> Stok Habis
                                                        </button>
                                                    @else
                                                        <button class="tkpd-btn-add btn-add-cart" 
                                                                data-id="{{ $p->id }}" 
                                                                data-kode="{{ $p->kode_barang }}" 
                                                                data-nama="{{ $p->nama_barang }}" 
                                                                data-satuan="{{ $p->satuan ? $p->satuan->nama_satuan : 'Pcs' }}" 
                                                                data-tipe="{{ $p->tipe_item }}"
                                                                data-photo="{{ $photoSrc }}" 
                                                                data-retail="{{ $hRetail }}" 
                                                                data-grosir="{{ $hGrosir }}" 
                                                                data-tender="{{ $hTender }}" 
                                                                data-stock="{{ $p->total_stok }}">
                                                            <i class="bi bi-cart-plus-fill"></i> + Keranjang
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Simple Pagination -->
                        <div id="catalog_pagination"></div>

                    </div>

                </div>

            </div>


            <!-- ===== TAB 2: RIWAYAT PESANAN (ORDER HISTORY) ===== -->
            <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                
                <div class="order-history-card rounded-4 p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-journal-text text-primary"></i>
                                Daftar Pesanan 
                            </h5>
                            
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3"><i class="bi bi-funnel"></i></span>
                                <select class="form-select form-select-sm border-start-0 rounded-end-3" id="history_status_filter" style="min-width: 170px;">
                                    <option value="all">Semua Status</option>
                                    <option value="Confirmed">Menunggu Kirim</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Processing">Diproses</option>
                                    <option value="Delivered">Terkirim</option>
                                    <option value="Completed">Selesai</option>
                                    <option value="Cancelled">Batal</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Swipe Hint Banner -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2 rounded-3 text-primary shadow-2xs" style="background: rgba(37, 99, 235, 0.08); font-size: 0.775rem; border: 1px dashed rgba(37, 99, 235, 0.25);">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Tabel riwayat pesanan dapat digeser ke samping</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                        <table class="table table-hover align-middle mb-0" id="tableSalesOrders" style="width: 100%; min-width: 960px;">
                            <thead>
                                <tr class="text-uppercase fw-bold">
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th style="min-width: 140px;">Nomor SO</th>
                                    <th style="min-width: 110px;">Tanggal</th>
                                    <th style="min-width: 190px;">Nama Pelanggan</th>
                                    <th class="text-center" style="min-width: 80px;">Tier</th>
                                    <th class="text-center" style="min-width: 75px;">Item</th>
                                    <th class="text-end" style="min-width: 130px;">Total Nilai</th>
                                    <th class="text-center" style="min-width: 120px;">Status</th>
                                    <th class="text-center" style="min-width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables Populates -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </div>

    @push('modals')
    {{-- Modals & Offcanvas Drawer --}}
    @include('modul_marketing.order._drawer_cart')
    @include('modul_marketing.order._modal_product_detail')
    @include('modul_marketing.order._modal_checkout')
    @include('modul_marketing.order._modal_order_detail')
    @include('modul_marketing.order._modal_edit_order')
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('modul_marketing.order.js.order')
    @endpush

</x-layouts>
