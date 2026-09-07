<x-layouts title="Order Penjualan & Katalog Kasir (POS)">

    @push('styles')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Tokopedia Style Sales Order CSS -->
    <link rel="stylesheet" href="{{ asset('css/sales_order_tokopedia.css?v=' . time()) }}">
    <style>
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            color: #64748b;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
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
                        Order Penjualan & Kasir Sales
                    </h4>
                    @if($activeCompany)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fs-7 d-inline-flex align-items-center gap-1.5 shadow-sm">
                        <i class="bi bi-building"></i>
                        <span>PT: <strong>{{ $activeCompany->nama_perusahaan }}</strong></span>
                        @if($activeBranch)
                            <span class="mx-1 text-muted">|</span>
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ $activeBranch->nama_cabang }}</span>
                        @endif
                    </span>
                    @endif
                </div>
                <div class="text-muted small">Katalog produk, persediaan stok, dan transaksi penjualan khusus entitas <strong>{{ $activeCompany->nama_perusahaan ?? 'Perusahaan Aktif' }}</strong>.</div>
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

                                <!-- Category Pills -->
                                <div class="col-12 pt-1">
                                    <div class="tkpd-category-pills">
                                        <div class="tkpd-cat-pill active" data-cat-id="all">
                                            <i class="bi bi-grid-fill me-1"></i> Semua Kategori
                                        </div>
                                        @foreach($categories as $cat)
                                             <div class="tkpd-cat-pill" data-cat-id="{{ $cat->id }}">
                                                {{ $cat->nama_kategori }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Product Grid Container -->
                        <div id="product_grid_container">
                            <!-- Dynamic Content Rendered by JS -->
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
                                Daftar Surat Pesanan (Sales Orders)
                            </h5>
                            <div class="text-muted small">Kelola, tinjau faktur pesanan, dan perbarui status pesanan pelanggan.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3"><i class="bi bi-funnel"></i></span>
                                <select class="form-select form-select-sm border-start-0 rounded-end-3" id="history_status_filter" style="min-width: 170px;">
                                    <option value="all">Semua Status</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Confirmed">Terkonfirmasi</option>
                                    <option value="Processing">Diproses</option>
                                    <option value="Delivered">Dikirim</option>
                                    <option value="Completed">Selesai</option>
                                    <option value="Cancelled">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableSalesOrders" style="width:100%;">
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

    {{-- Modals & Offcanvas Drawer --}}
    @include('modul_marketing.order._drawer_cart')
    @include('modul_marketing.order._modal_product_detail')
    @include('modul_marketing.order._modal_checkout')
    @include('modul_marketing.order._modal_order_detail')
    @include('modul_marketing.order._modal_edit_order')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('modul_marketing.order.js.order')
    @endpush

</x-layouts>
