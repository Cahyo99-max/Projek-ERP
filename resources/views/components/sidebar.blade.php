<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand" style="display: flex; align-items: center; gap: 8px;">
        <img src="{{ $appLogo }}" alt="Logo" class="sidebar-logo" style="width: 28px; height: 28px; object-fit: contain; border-radius: 6px;">
        <span class="brand-text">{{ $appName }}</span>
    </div>

    <div class="sidebar-nav">
        <ul class="nav flex-column">
            @canany(['lihat-dashboard', 'lihat-dashboard-purchasing', 'lihat-dashboard-inventori', 'lihat-dashboard-produksi', 'lihat-dashboard-sales', 'lihat-dashboard-keuangan'])
            <li class="nav-item">
                <a href="/dashbord" class="nav-link {{ request()->is('dashbord*') || request()->is('finance/dashboard*') || request()->routeIs('finance.dashboard') || request()->routeIs('dashboard') || request()->routeIs('produksi.dashboard') || request()->routeIs('sales.dashboard') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right"
                    title="{{ __('messages.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span class="nav-text">{{ __('messages.dashboard') }}</span>
                </a>
            </li>
            @endcanany

            <div class="nav-label">{{ __('messages.modules') }}</div>

            <!-- Accordion Menu: Master Produk -->
            @canany(['lihat-barang','lihat-kategori-barang','lihat-satuan','lihat-brand','lihat-tipe-barang','lihat-warna','lihat-bundle','tambah-bundle'])
            <li class="nav-item">
                <a href="#menuMasterProduk" class="nav-link {{ request()->is('barang*') || request()->is('kategori-barang*') || request()->is('satuan*') || request()->is('brand*') || request()->is('tipe-barang*') || request()->is('warna*') || request()->is('master-bundle') || (request()->is('master-bundle*') && !request()->is('master-bundle/pengaturan*')) ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('barang*') || request()->is('kategori-barang*') || request()->is('satuan*') || request()->is('brand*') || request()->is('tipe-barang*') || request()->is('warna*') || request()->is('master-bundle') || (request()->is('master-bundle*') && !request()->is('master-bundle/pengaturan*')) ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.master_product') }}">
                    <i class="bi bi-boxes"></i>
                    <span class="nav-text">{{ __('messages.master_product') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('barang*') || request()->is('kategori-barang*') || request()->is('satuan*') || request()->is('brand*') || request()->is('tipe-barang*') || request()->is('warna*') || request()->is('master-bundle') || (request()->is('master-bundle*') && !request()->is('master-bundle/pengaturan*')) ? 'show' : '' }}" id="menuMasterProduk">
                    <ul class="sub-menu">
                        @can('lihat-barang')
                        <li><a href="/barang" class="nav-link {{ request()->is('barang*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> {{ __('messages.product') }}</a></li>
                        @endcan
                        @can('lihat-kategori-barang')
                        <li><a href="/kategori-barang" class="nav-link {{ request()->is('kategori-barang*') ? 'active' : '' }}"><i class="bi bi-tags"></i> {{ __('messages.category') }}</a></li>
                        @endcan
                        @can('lihat-satuan')
                        <li><a href="/satuan" class="nav-link {{ request()->is('satuan*') ? 'active' : '' }}"><i class="bi bi-tag"></i> {{ __('messages.unit') }}</a></li>
                        @endcan
                        @can('lihat-brand')
                        <li><a href="/brand" class="nav-link {{ request()->is('brand*') ? 'active' : '' }}"><i class="bi bi-award"></i> {{ __('messages.brand') }}</a></li>
                        @endcan
                        @can('lihat-tipe-barang')
                        <li><a href="/tipe-barang" class="nav-link {{ request()->is('tipe-barang*') ? 'active' : '' }}"><i class="bi bi-grid"></i> {{ __('messages.type') }}</a></li>
                        @endcan
                        @can('lihat-warna')
                        <li><a href="/warna" class="nav-link {{ request()->is('warna*') ? 'active' : '' }}"><i class="bi bi-palette"></i> {{ __('messages.color') }}</a></li>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-bundle') || auth()->user()->can('tambah-bundle'))
                        <li><a href="/master-bundle" class="nav-link {{ request()->is('master-bundle') || (request()->is('master-bundle*') && !request()->is('master-bundle/pengaturan*')) ? 'active' : '' }}"><i class="bi bi-boxes text-info"></i> Daftar Paket Bundle</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Master Gudang -->
            @canany(['lihat-gudang','lihat-jenis-gudang','lihat-perusahaan','lihat-cabang','lihat-lokasi-rak'])
            <li class="nav-item">
                <a href="#menuMasterGudang" class="nav-link {{ request()->is('gudang*') || request()->is('jenis-gudang*') || request()->is('companies*') || request()->is('branches*') || request()->is('cabang*') || request()->is('lokasi-rak*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('gudang*') || request()->is('jenis-gudang*') || request()->is('companies*') || request()->is('branches*') || request()->is('cabang*') || request()->is('lokasi-rak*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.master_warehouse') }}">
                    <i class="bi bi-building-gear"></i>
                    <span class="nav-text">{{ __('messages.master_warehouse') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('gudang*') || request()->is('jenis-gudang*') || request()->is('companies*') || request()->is('branches*') || request()->is('cabang*') || request()->is('lokasi-rak*') ? 'show' : '' }}" id="menuMasterGudang">
                    <ul class="sub-menu">
                        @can('lihat-gudang')
                        <li><a href="/gudang" class="nav-link {{ request()->is('gudang*') ? 'active' : '' }}"><i class="bi bi-house-gear"></i> {{ __('messages.warehouse') }}</a></li>
                        @endcan
                        @can('lihat-jenis-gudang')
                        <li><a href="/jenis-gudang" class="nav-link {{ request()->is('jenis-gudang*') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i> {{ __('messages.warehouse_type') }}</a></li>
                        @endcan
                        @can('lihat-perusahaan')
                        <li><a href="/companies" class="nav-link {{ request()->is('companies*') ? 'active' : '' }}"><i class="bi bi-building"></i> Perusahaan (PT)</a></li>
                        @endcan
                        @can('lihat-cabang')
                        <li><a href="/branches" class="nav-link {{ request()->is('branches*') || request()->is('cabang*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> {{ __('messages.branch') }}</a></li>
                        @endcan
                        @can('lihat-lokasi-rak')
                        <li><a href="/lokasi-rak" class="nav-link {{ request()->is('lokasi-rak*') ? 'active' : '' }}"><i class="bi bi-grid-3x3"></i> {{ __('messages.rack_location') }}</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Master Organisasi -->
            @canany(['lihat-departemen','lihat-jabatan','lihat-karyawan'])
            <li class="nav-item">
                <a href="#menuMasterOrganisasi" class="nav-link {{ request()->is('departemen*') || request()->is('positions*') || request()->is('karyawan*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('departemen*') || request()->is('positions*') || request()->is('karyawan*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.master_organization') }}">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-text">{{ __('messages.master_organization') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('departemen*') || request()->is('positions*') || request()->is('karyawan*') ? 'show' : '' }}" id="menuMasterOrganisasi">
                    <ul class="sub-menu">
                        @can('lihat-karyawan')
                        <li><a href="/karyawan" class="nav-link {{ request()->is('karyawan*') ? 'active' : '' }}"><i class="bi bi-person-badge-fill"></i> Karyawan (HRD)</a></li>
                        @endcan
                        @can('lihat-departemen')
                        <li><a href="/departemen" class="nav-link {{ request()->is('departemen*') ? 'active' : '' }}"><i class="bi bi-building"></i> {{ __('messages.department') }}</a></li>
                        @endcan
                        @can('lihat-jabatan')
                        <li><a href="/positions" class="nav-link {{ request()->is('positions*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> {{ __('messages.position') }}</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Master Keuangan -->
            @canany(['lihat-pajak','lihat-currency','lihat-payment-term','lihat-payment-method'])
            <li class="nav-item">
                <a href="#menuMasterKeuangan" class="nav-link {{ request()->is('pajak*') || request()->is('currency*') || request()->is('payment-term*') || request()->is('payment-method*') || request()->is('finance/chart-of-accounts*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('pajak*') || request()->is('currency*') || request()->is('payment-term*') || request()->is('payment-method*') || request()->is('finance/chart-of-accounts*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.master_finance') }}">
                    <i class="bi bi-wallet2"></i>
                    <span class="nav-text">{{ __('messages.master_finance') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('pajak*') || request()->is('currency*') || request()->is('payment-term*') || request()->is('payment-method*') || request()->is('finance/chart-of-accounts*') ? 'show' : '' }}" id="menuMasterKeuangan">
                    <ul class="sub-menu">
                        @can('lihat-pajak')
                        <li><a href="/pajak" class="nav-link {{ request()->is('pajak*') ? 'active' : '' }}"><i class="bi bi-percent"></i> {{ __('messages.tax') }}</a></li>
                        @endcan
                        @can('lihat-currency')
                        <li><a href="/currency" class="nav-link {{ request()->is('currency*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> {{ __('messages.currency') }}</a></li>
                        @endcan
                        @can('lihat-payment-term')
                        <li><a href="/payment-term" class="nav-link {{ request()->is('payment-term*') ? 'active' : '' }}"><i class="bi bi-calendar-range"></i> Tenor Pembayaran</a></li>
                        @endcan
                        @can('lihat-payment-method')
                        <li><a href="/payment-method" class="nav-link {{ request()->is('payment-method*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i> Metode Pembayaran</a></li>
                        @endcan
                        <li><a href="/finance/chart-of-accounts" class="nav-link {{ request()->is('finance/chart-of-accounts*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark-fill"></i> Chart of Accounts (COA)</a></li>
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Inventory -->
            @canany(['lihat-gr', 'tambah-gr', 'hapus-gr', 'lihat-kartu-stok', 'lihat-gi', 'tambah-gi', 'hapus-gi', 'lihat-stok-aktual', 'lihat-transfer', 'tambah-transfer', 'hapus-transfer', 'lihat-opname', 'tambah-opname', 'hapus-opname', 'konfirmasi-opname', 'lihat-stok-adjustment', 'tambah-stok-adjustment', 'approve-stok-adjustment', 'lihat-opening-stock'])
            <li class="nav-item">
                <a href="#menuInventory" class="nav-link {{ request()->is('goods-receipt*') || request()->is('stock-card*') || request()->is('goods-issue*') || request()->is('stock-overview*') || request()->is('warehouse-transfer*') || request()->is('stock-opname*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('goods-receipt*') || request()->is('stock-card*') || request()->is('goods-issue*') || request()->is('stock-overview*') || request()->is('warehouse-transfer*') || request()->is('stock-opname*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.inventory') }}">
                    <i class="bi bi-box-seam"></i>
                    <span class="nav-text">{{ __('messages.inventory') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('goods-receipt*') || request()->is('stock-card*') || request()->is('goods-issue*') || request()->is('stock-overview*') || request()->is('warehouse-transfer*') || request()->is('stock-opname*') || request()->is('stock-adjustment*') ? 'show' : '' }}" id="menuInventory">
                    <ul class="sub-menu">
                        @can('lihat-gr')
                        <li><a href="/goods-receipt" class="nav-link {{ request()->is('goods-receipt*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> {{ __('messages.incoming_goods') }}</a></li>
                        @endcan
                         @can('lihat-gi')
                        <li><a href="{{ route('gi.index') }}" class="nav-link {{ request()->is('goods-issue*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Barang Keluar</a></li>
                        @endcan
                       
                        @can('lihat-stok-aktual')
                        <li><a href="{{ route('stock-overview.index') }}" class="nav-link {{ request()->is('stock-overview*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Stok Saat Ini</a></li>
                        @endcan
                        @can('lihat-opening-stock')
                        <li><a href="{{ route('opening-stock.index') }}" class="nav-link {{ request()->is('opening-stock*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Saldo Awal Stok</a></li>
                        @endcan
                          @can('lihat-opname')
                        <li><a href="{{ route('opname.index') }}" class="nav-link {{ request()->is('stock-opname*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> {{ __('messages.stock_opname') }}</a></li>
                        @endcan
                        @can('lihat-stok-adjustment')
                        <li><a href="{{ route('stock-adjustment.index') }}" class="nav-link {{ request()->is('stock-adjustment*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Penyesuaian Stok</a></li>
                        @endcan
                        @can('lihat-transfer')
                        <li><a href="{{ route('transfer.index') }}" class="nav-link {{ request()->is('warehouse-transfer*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Transfer Gudang</a></li>
                        @endcan
                         @can('lihat-kartu-stok')
                        <li><a href="{{ route('stock-card.index') }}" class="nav-link {{ request()->is('stock-card*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i> Kartu Stok</a></li>
                        @endcan
                      
                    </ul>
                </div>
            </li>
            @endcanany

            @canany(['lihat-dashboard-sales', 'lihat-sales-order', 'tambah-sales-order', 'lihat-tender', 'lihat-penawaran', 'lihat-customer', 'akses-sales', 'lihat-delivery-order', 'lihat-sales-invoice', 'lihat-sales-payment', 'lihat-piutang'])
            <li class="nav-item">
                <a href="#menuSales" class="nav-link {{ (request()->is('sales/dashboard*') || request()->is('customer*') || request()->is('tender*') || request()->is('penawaran*') || request()->is('marketing/sales-order*') || request()->is('marketing/delivery-order*') || request()->is('modul-marketing/faktur-penjualan*') || request()->is('modul-marketing/pembayaran*') || request()->is('modul-marketing/piutang*')) ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ (request()->is('sales/dashboard*') || request()->is('customer*') || request()->is('tender*') || request()->is('penawaran*') || request()->is('marketing/sales-order*') || request()->is('marketing/delivery-order*') || request()->is('modul-marketing/faktur-penjualan*') || request()->is('modul-marketing/pembayaran*') || request()->is('modul-marketing/piutang*')) ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.sales') }}">
                    <i class="bi bi-cart3"></i>
                    <span class="nav-text">{{ __('messages.sales') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ (request()->is('sales/dashboard*') || request()->is('customer*') || request()->is('tender*') || request()->is('penawaran*') || request()->is('marketing/sales-order*') || request()->is('marketing/delivery-order*') || request()->is('modul-marketing/faktur-penjualan*') || request()->is('modul-marketing/pembayaran*') || request()->is('modul-marketing/piutang*')) ? 'show' : '' }}" id="menuSales">
                    <ul class="sub-menu">
                      
                        @can('lihat-customer')
                        <li><a href="/customer" class="nav-link {{ request()->is('customer*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Customer</a></li>
                        @endcan
                        @can('lihat-tender')
                        <li><a href="/tender" class="nav-link {{ request()->is('tender*') ? 'active' : '' }}"><i class="bi bi-briefcase-fill"></i> Tender</a></li>
                        @endcan
                        @can('lihat-penawaran')
                        <li><a href="{{ route('penawaran.index') }}" class="nav-link {{ request()->is('penawaran*') ? 'active' : '' }}"><i class="bi bi-file-earmark-ruled-fill"></i> Penawaran Harga</a></li>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-sales-order') || auth()->user()->can('tambah-sales-order') || auth()->user()->can('akses-sales'))
                        <li><a href="{{ route('marketing.sales-order.index') }}" class="nav-link {{ request()->is('marketing/sales-order*') ? 'active' : '' }}"><i class="bi bi-cart-check-fill"></i> Order Penjualan</a></li>
                        @endif
                        @can('lihat-delivery-order')
                        <li><a href="{{ route('marketing.delivery-order.index') }}" class="nav-link {{ request()->is('marketing/delivery-order*') ? 'active' : '' }}"><i class="bi bi-truck"></i> Pengiriman</a></li>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-sales-invoice'))
                        <li><a href="{{ route('modul_marketing.faktur_penjualan.index') }}" class="nav-link {{ request()->is('modul-marketing/faktur-penjualan*') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Faktur Penjualan</a></li>
                        @endif
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-sales-payment'))
                        <li><a href="{{ route('modul_marketing.pembayaran.index') }}" class="nav-link {{ request()->is('modul-marketing/pembayaran*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Penerimaan Pembayaran</a></li>
                        @endif
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-piutang'))
                        <li><a href="{{ route('modul_marketing.piutang.index') }}" class="nav-link {{ request()->is('modul-marketing/piutang*') ? 'active' : '' }}"><i class="bi bi-wallet2 text-warning"></i> Rekap Piutang Pelanggan</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Pembelian -->
            @canany(['lihat-supplier', 'lihat-alokasi-biaya', 'lihat-pr', 'tambah-pr', 'approve-pr', 'lihat-po', 'tambah-po', 'approve-po', 'lihat-purchase-invoice', 'lihat-pembayaran-utang', 'lihat-hutang'])
            <li class="nav-item">
                <a href="#menuPurchase" class="nav-link {{ request()->is('purchase-requisition*') || request()->is('supplier*') || request()->is('purchase-order*') || request()->is('purchase-invoices*') || request()->is('purchase-payments*') || request()->is('alokasi-biaya*') || request()->is('modul-purchasing/hutang*') || request()->is('supplier-payables*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('purchase-requisition*') || request()->is('supplier*') || request()->is('purchase-order*') || request()->is('purchase-invoices*') || request()->is('purchase-payments*') || request()->is('alokasi-biaya*') || request()->is('modul-purchasing/hutang*') || request()->is('supplier-payables*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.purchase') }}">
                    <i class="bi bi-bag"></i>
                    <span class="nav-text">{{ __('messages.purchase') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('purchase-requisition*') || request()->is('supplier*') || request()->is('purchase-order*') || request()->is('purchase-invoices*') || request()->is('purchase-payments*') || request()->is('alokasi-biaya*') || request()->is('modul-purchasing/hutang*') || request()->is('supplier-payables*') ? 'show' : '' }}" id="menuPurchase">
                    <ul class="sub-menu">
                        @can('lihat-supplier')
                        <li><a href="/supplier" class="nav-link {{ request()->is('supplier*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Supplier</a></li>
                        @endcan
                      
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-pr') || auth()->user()->can('tambah-pr') || auth()->user()->can('approve-pr'))
                        <li><a href="/purchase-requisition" class="nav-link {{ request()->is('purchase-requisition*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text-fill"></i> Pengajuan (PR)</a></li>
                        @endif
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-po') || auth()->user()->can('tambah-po') || auth()->user()->can('approve-po'))
                        <li><a href="/purchase-order" class="nav-link {{ request()->is('purchase-order*') ? 'active' : '' }}"><i class="bi bi-cart-check-fill"></i> {{ __('messages.purchase_order') }}</a></li>
                        @endif
                          @can('lihat-alokasi-biaya')
                        <li><a href="{{ route('alokasi-biaya.index') }}" class="nav-link {{ request()->is('alokasi-biaya*') ? 'active' : '' }}"><i class="bi bi-calculator-fill text-warning"></i> Alokasi Biaya</a></li>
                        @endcan
                        @can('lihat-purchase-invoice')
                        <li><a href="/purchase-invoices" class="nav-link {{ request()->is('purchase-invoices*') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Faktur Pembelian</a></li>
                        @endcan
                        @can('lihat-pembayaran-utang')
                        <li><a href="{{ route('purchase-payments.index') }}" class="nav-link {{ request()->is('purchase-payments*') ? 'active' : '' }}"><i class="bi bi-credit-card-2-front-fill text-success"></i> Pembayaran Utang</a></li>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-hutang'))
                        <li><a href="{{ route('modul_purchasing.hutang.index') }}" class="nav-link {{ request()->is('modul-purchasing/hutang*') || request()->is('supplier-payables*') ? 'active' : '' }}"><i class="bi bi-wallet2 text-danger"></i> Rekap Utang Supplier</a></li>
                        @endif
                        <!-- <li><a href="#" class="nav-link"><i class="bi bi-circle-fill"></i> {{ __('messages.purchase_return') }}</a></li> -->
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Keuangan -->
            @canany(['lihat-dashboard-keuangan', 'lihat-kas-bank', 'lihat-mutasi-kas-bank', 'lihat-jurnal-umum', 'lihat-petty-cash', 'lihat-pengaturan-akuntansi'])
            <li class="nav-item">
                <a href="#menuFinance" class="nav-link {{ request()->is('finance/dashboard*') || request()->is('accounting/*') || request()->is('fixed-assets*') || request()->is('registrasi-jasa*') || request()->is('settings/akuntansi*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('finance/dashboard*') || request()->is('accounting/*') || request()->is('fixed-assets*') || request()->is('registrasi-jasa*') || request()->is('settings/akuntansi*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.finance') }}">
                    <i class="bi bi-wallet2"></i>
                    <span class="nav-text">{{ __('messages.finance') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('finance/dashboard*') || request()->is('accounting/*') || request()->is('fixed-assets*') || request()->is('registrasi-jasa*') || request()->is('settings/akuntansi*') ? 'show' : '' }}" id="menuFinance">
                    <ul class="sub-menu">
                       
                        @can('lihat-kas-bank')
                        <li><a href="{{ route('accounting.kas-bank.index') }}" class="nav-link {{ request()->routeIs('accounting.kas-bank.index') ? 'active' : '' }}"><i class="bi bi-bank2 text-success"></i> Kas &amp; Bank</a></li>
                        @endcan
                        @can('lihat-mutasi-kas-bank')
                        <li><a href="{{ route('accounting.kas-bank.ledger') }}" class="nav-link {{ request()->is('accounting/kas-bank/ledger*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark-fill text-info"></i> Buku Kas</a></li>
                        @endcan
                        @can('lihat-jurnal-umum')
                        <li><a href="{{ route('accounting.jurnal-umum.index') }}" class="nav-link {{ request()->is('accounting/jurnal-umum*') ? 'active' : '' }}"><i class="bi bi-journal-text text-primary"></i> Jurnal Umum</a></li>
                        @endcan
                        @can('lihat-petty-cash')
                        <li><a href="{{ route('accounting.petty-cash.index') }}" class="nav-link {{ request()->is('accounting/petty-cash*') || request()->is('accounting/paty-cas*') ? 'active' : '' }}"><i class="bi bi-wallet2 text-warning"></i> Kas Kecil</a></li>
                        @endcan

                        @can('lihat-pengaturan-akuntansi')
                        <li><a href="/settings/akuntansi" class="nav-link {{ request()->is('settings/akuntansi*') ? 'active' : '' }}"><i class="bi bi-sliders text-warning"></i> Pengaturan Akuntansi</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            <!-- Accordion Menu: Produksi Garmen -->
            @canany(['lihat-bom','tambah-bom','approve-bom','lihat-spk','tambah-spk','approve-spk','lihat-realisasi-produksi','kontrol-tahapan-realisasi','input-hasil-harian-produksi','input-realisasi-produksi','approve-realisasi-produksi','lihat-pengeluaran-bahan','lihat-penerimaan-barang-jadi','lihat-tracking-sn','lihat-master-tahapan','cetak-spk'])
            <li class="nav-item">
                <a href="#menuProduction" class="nav-link {{ request()->is('produksi*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('produksi*') ? 'true' : 'false' }}"
                    data-tooltip-title="Produksi">
                    <i class="bi bi-tools"></i>
                    <span class="nav-text">Produksi Garmen</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('produksi*') ? 'show' : '' }}" id="menuProduction">
                    <ul class="sub-menu">
                       

                        @canany(['lihat-bom', 'tambah-bom', 'approve-bom', 'detail-bom', 'edit-bom'])
                        <li><a href="{{ route('produksi.bom.index') }}" class="nav-link {{ request()->is('produksi/bom*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Master BOM</a></li>
                        @endcanany

                        @canany(['lihat-spk', 'tambah-spk', 'approve-spk', 'detail-spk', 'edit-spk'])
                        <li><a href="{{ route('produksi.spk.index') }}" class="nav-link {{ request()->is('produksi/spk*') ? 'active' : '' }}"><i class="bi bi-file-earmark-ruled"></i> SPK</a></li>
                        @endcanany

                        @canany(['lihat-realisasi-produksi', 'kontrol-tahapan-realisasi', 'input-hasil-harian-produksi', 'input-realisasi-produksi', 'approve-realisasi-produksi'])
                        <li><a href="{{ route('produksi.realisasi.index') }}" class="nav-link {{ request()->is('produksi/realisasi-produksi*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Kontrol Tahapan</a></li>
                        @endcanany

                        @can('lihat-pengeluaran-bahan')
                        <li><a href="{{ route('produksi.pengeluaran-bahan.index') }}" class="nav-link {{ request()->is('produksi/pengeluaran-bahan*') ? 'active' : '' }}"><i class="bi bi-box-arrow-right"></i> Bahan Baku</a></li>
                        @endcan

                        @can('lihat-penerimaan-barang-jadi')
                        <li><a href="{{ route('produksi.barang-jadi.index') }}" class="nav-link {{ request()->is('produksi/barang-jadi*') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-down-right"></i> Barang Jadi</a></li>
                        @endcan

                        @can('lihat-tracking-sn')
                        <li><a href="{{ route('produksi.tracking-sn.index') }}" class="nav-link {{ request()->is('produksi/tracking-sn*') ? 'active' : '' }}"><i class="bi bi-upc-scan text-info"></i> Pelacakan SN</a></li>
                        @endcan

                        @can('lihat-master-tahapan')
                        <li><a href="{{ route('produksi.master-tahapan.index') }}" class="nav-link {{ request()->is('produksi/master-tahapan*') ? 'active' : '' }}"><i class="bi bi-diagram-3-fill"></i> Master Tahapan</a></li>
                        @endcan

                        @canany(['cetak-spk', 'lihat-penerimaan-barang-jadi'])
                        <!-- <li><a href="{{ route('produksi.cetak-label.index') }}" class="nav-link {{ request()->is('produksi/cetak-label*') ? 'active' : '' }}"><i class="bi bi-printer-fill text-warning"></i> Cetak Label QR &amp; Barcode</a></li> -->
                        @endcanany
                    </ul>
                </div>
            </li>
            @endcanany

            <div class="nav-label">{{ __('messages.system') }}</div>

            <!-- <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right"
                    title="{{ __('messages.reports') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span class="nav-text">{{ __('messages.reports') }}</span>
                </a>
            </li> -->
            <!-- Accordion Menu: Pengaturan -->
            @canany(['lihat-karyawan','tambah-karyawan','lihat-hak-akses','edit-hak-akses','lihat-pengaturan-aplikasi','edit-pengaturan-aplikasi','lihat-log-aktivitas-user','akses-whatsapp-gateway','kelola-kategori-bundle'])
            <li class="nav-item">
                <a href="#menuSettings" class="nav-link {{ request()->is('viewusers*') || request()->is('roles*') || request()->is('app-settings*') || request()->is('activity-logs*') || request()->is('settings/whatsapp*') || request()->is('master-bundle/pengaturan*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('viewusers*') || request()->is('roles*') || request()->is('app-settings*') || request()->is('activity-logs*') || request()->is('settings/whatsapp*') || request()->is('master-bundle/pengaturan*') ? 'true' : 'false' }}"
                    data-tooltip-title="{{ __('messages.settings') }}">
                    <i class="bi bi-gear"></i>
                    <span class="nav-text">{{ __('messages.settings') }}</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->is('viewusers*') || request()->is('roles*') || request()->is('app-settings*') || request()->is('activity-logs*') || request()->is('settings/whatsapp*') || request()->is('master-bundle/pengaturan*') ? 'show' : '' }}" id="menuSettings">
                    <ul class="sub-menu">
                        @can('lihat-karyawan')
                        <li><a href="/viewusers" class="nav-link {{ request()->is('viewusers*') ? 'active' : '' }}"><i class="bi bi-people"></i> Manajemen User</a></li>
                        @endcan
                        @can('lihat-hak-akses')
                        <li><a href="/roles" class="nav-link {{ request()->is('roles*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> {{ __('messages.roles') }}</a></li>
                        @endcan
                        @can('lihat-pengaturan-aplikasi')
                        <li><a href="/app-settings" class="nav-link {{ request()->is('app-settings*') ? 'active' : '' }}"><i class="bi bi-sliders"></i> Pengaturan Aplikasi</a></li>
                        @endcan
                        @can('lihat-log-aktivitas-user')
                        <li><a href="/activity-logs" class="nav-link {{ request()->is('activity-logs*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Log Aktivitas User</a></li>
                        @endcan
                        @can('akses-whatsapp-gateway')
                        <li><a href="/settings/whatsapp" class="nav-link {{ request()->is('settings/whatsapp*') ? 'active' : '' }}"><i class="bi bi-whatsapp text-success"></i> WhatsApp Gateway</a></li>
                        @endcan
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('kelola-kategori-bundle'))
                        <li><a href="/master-bundle/pengaturan-kategori" class="nav-link {{ request()->is('master-bundle/pengaturan*') ? 'active' : '' }}"><i class="bi bi-sliders2-vertical text-warning"></i> Visibilitas Kategori Bundle</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endcanany
        </ul>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
