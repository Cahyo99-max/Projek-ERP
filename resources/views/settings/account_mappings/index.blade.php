<x-layouts title="Pengaturan Account Mapping - ">

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/barang_dashboard.css?v=' . time()) }}">
    <style>
        /* ─── Select2 Custom & Scrollable (Display ~5 items) ─── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important;
            padding: 3px 8px !important;
            min-height: 36px !important;
            font-size: 0.82rem !important;
            font-weight: 500;
            display: flex !important;
            align-items: center !important;
            border-color: #cbd5e1;
            transition: all 0.2s ease;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 4px !important;
            padding-right: 24px !important;
            color: inherit;
            font-family: var(--bs-font-monospace);
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            font-size: 1.15rem !important;
            line-height: 1 !important;
            margin-right: 22px !important;
            color: #94a3b8 !important;
            position: absolute !important;
            right: 0 !important;
            cursor: pointer !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
            color: #ef4444 !important;
        }
        .select2-dropdown {
            border-radius: 12px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #cbd5e1 !important;
            overflow: hidden !important;
            z-index: 99999 !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options,
        .select2-results__options {
            max-height: 195px !important;
            overflow-y: auto !important;
            padding: 4px !important;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 6px 10px !important;
            font-size: 0.78rem !important;
            border-radius: 6px !important;
            margin-bottom: 2px !important;
            font-family: var(--bs-font-monospace);
            transition: all 0.15s ease;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 8px !important;
            padding: 5px 10px !important;
            font-size: 0.78rem !important;
            margin: 4px !important;
            width: calc(100% - 8px) !important;
        }
        .select2-results__options::-webkit-scrollbar {
            width: 5px;
        }
        .select2-results__options::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 10px;
        }
        .select2-results__options::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }
        
        /* Dark Mode Select2 */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        .dark-mode .select2-container--bootstrap-5 .select2-selection {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        .dark-mode .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-dropdown,
        .dark-mode .select2-dropdown {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        .dark-mode .select2-container--bootstrap-5 .select2-search__field {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb,
        .dark-mode .select2-results__options::-webkit-scrollbar-thumb {
            background: #475569;
        }
        [data-bs-theme="dark"] .select2-results__option,
        .dark-mode .select2-results__option {
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        .nav-tabs-akuntansi {
            border-bottom: 1px solid #e2e8f0;
        }
        .nav-tabs-akuntansi .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 20px;
            background: transparent;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .nav-tabs-akuntansi .nav-link:hover {
            color: #2563eb;
        }
        .nav-tabs-akuntansi .nav-link.active {
            color: #2563eb;
            font-weight: 700;
            border-bottom: 3px solid #2563eb;
            background: transparent;
        }

        /* Table Header PR Style (Light Mode: Blue Header) */
        .table-akuntansi thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 800;
            color: #1e40af !important;
            background-color: #dbeafe !important;
            padding: 14px 16px;
            border-bottom: 1px solid #bfdbfe !important;
        }

        /* Zebra Striping PR Style */
        .table-akuntansi tbody tr:nth-of-type(odd) {
            background-color: #eff6ff !important;
        }
        .table-akuntansi tbody tr:nth-of-type(even) {
            background-color: #ffffff !important;
        }
        .table-akuntansi tbody tr:hover {
            background-color: #dbeafe !important;
        }

        .table-akuntansi td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .mapping-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.2s ease;
        }
        .mapping-box:hover {
            border-color: #cbd5e1;
        }

        /* ===== DARK MODE OVERRIDES (Flexible Theme Support) ===== */
        [data-bs-theme="dark"] .text-dark,
        html[data-theme="dark"] .text-dark,
        .dark-mode .text-dark,
        [data-bs-theme="dark"] h1, [data-bs-theme="dark"] h2, [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4, [data-bs-theme="dark"] h5, [data-bs-theme="dark"] h6,
        html[data-theme="dark"] h4, html[data-theme="dark"] h5, html[data-theme="dark"] h6 {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-body,
        html[data-theme="dark"] .card,
        html[data-theme="dark"] .card-header,
        html[data-theme="dark"] .card-body,
        .dark-mode .card,
        .dark-mode .card-header,
        .dark-mode .card-body {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi,
        html[data-theme="dark"] .nav-tabs-akuntansi,
        .dark-mode .nav-tabs-akuntansi {
            border-bottom-color: #334155 !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi .nav-link,
        html[data-theme="dark"] .nav-tabs-akuntansi .nav-link,
        .dark-mode .nav-tabs-akuntansi .nav-link {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi .nav-link.active,
        html[data-theme="dark"] .nav-tabs-akuntansi .nav-link.active,
        .dark-mode .nav-tabs-akuntansi .nav-link.active {
            color: #3b82f6 !important;
            border-bottom-color: #3b82f6 !important;
            background: transparent !important;
        }

        [data-bs-theme="dark"] .mapping-box,
        html[data-theme="dark"] .mapping-box,
        .dark-mode .mapping-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi thead th,
        html[data-theme="dark"] .table-akuntansi thead th,
        .dark-mode .table-akuntansi thead th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi,
        html[data-theme="dark"] .table-akuntansi,
        .dark-mode .table-akuntansi {
            --bs-table-bg: transparent !important;
            --bs-table-accent-bg: transparent !important;
            --bs-table-striped-bg: transparent !important;
            --bs-table-hover-bg: transparent !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:nth-of-type(odd) td,
        html[data-theme="dark"] .table-akuntansi tbody tr:nth-of-type(odd) td,
        .dark-mode .table-akuntansi tbody tr:nth-of-type(odd) td {
            background-color: #151e2e !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:nth-of-type(even) td,
        html[data-theme="dark"] .table-akuntansi tbody tr:nth-of-type(even) td,
        .dark-mode .table-akuntansi tbody tr:nth-of-type(even) td {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:hover td,
        html[data-theme="dark"] .table-akuntansi tbody tr:hover td,
        .dark-mode .table-akuntansi tbody tr:hover td {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .table-akuntansi td,
        html[data-theme="dark"] .table-akuntansi td,
        .dark-mode .table-akuntansi td {
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        html[data-theme="dark"] .form-control,
        .dark-mode .form-select,
        .dark-mode .form-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-select:focus,
        [data-bs-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus,
        html[data-theme="dark"] .form-control:focus,
        .dark-mode .form-select:focus,
        .dark-mode .form-control:focus {
            background-color: #0f172a !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-select option,
        html[data-theme="dark"] .form-select option,
        .dark-mode .form-select option {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted,
        html[data-theme="dark"] .text-muted,
        .dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .form-label,
        html[data-theme="dark"] .form-label,
        .dark-mode .form-label {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .bg-light,
        html[data-theme="dark"] .bg-light,
        .dark-mode .bg-light {
            background-color: #0f172a !important;
        }
    </style>
    @endpush

    <div class="container-fluid py-4">
        <!-- Header Page -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold m-0 text-dark">Central Account Mapping</h4>
                <p class="text-muted small m-0">Pemetaan terpusat antara modul operasional (Gudang, Pembelian, Penjualan, Pajak, Ongkir) dengan akun COA.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary px-4 rounded-3 shadow-sm" id="btnSaveAllMappings">
                    <i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan
                </button>
            </div>
        </div>

        <form id="formAccountMapping">
            @csrf

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header border-bottom px-4 pt-3 pb-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs-akuntansi gap-2" id="mappingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-default" data-bs-toggle="tab" data-bs-target="#content-default" type="button" role="tab">
                                <i class="bi bi-sliders me-1"></i> Default Utang/Stok/HPP
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-freight" data-bs-toggle="tab" data-bs-target="#content-freight" type="button" role="tab">
                                <i class="bi bi-truck me-1"></i> Ongkir & Jasa Pengiriman
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-category" data-bs-toggle="tab" data-bs-target="#content-category" type="button" role="tab">
                                <i class="bi bi-tags me-1"></i> Kategori Barang
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-tax" data-bs-toggle="tab" data-bs-target="#content-tax" type="button" role="tab">
                                <i class="bi bi-receipt me-1"></i> Pajak (PPN/PPh)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-payment" data-bs-toggle="tab" data-bs-target="#content-payment" type="button" role="tab">
                                <i class="bi bi-wallet2 me-1"></i> Metode Pembayaran
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-goods-issue" data-bs-toggle="tab" data-bs-target="#content-goods-issue" type="button" role="tab">
                                <i class="bi bi-box-arrow-up me-1"></i> Barang Keluar (GI)
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="mappingTabsContent">
                        
                        <!-- TAB 1: DEFAULT SYSTEM ACCOUNTS -->
                        <div class="tab-pane fade show active" id="content-default" role="tabpanel">
                            <h6 class="fw-bold mb-2 text-primary">Default Account System</h6>
                            <p class="text-muted small mb-4">Akun bawaan yang digunakan jika suatu barang/transaksi tidak memiliki pemetaan khusus.</p>

                            @php
                                $defaultKeys = [
                                    'default_inventory' => ['label' => 'Default Persediaan Barang', 'desc' => 'Akun persediaan bawaan untuk transaksi Gudang & Purchasing'],
                                    'default_cogs' => ['label' => 'Default HPP (Cost of Goods Sold)', 'desc' => 'Akun modal pokok bawaan saat penjualan/pengeluaran barang'],
                                    'default_sales' => ['label' => 'Default Penjualan (Revenue)', 'desc' => 'Akun pendapatan bawaan penjualan produk'],
                                    'default_ap' => ['label' => 'Default Utang Dagang (Accounts Payable)', 'desc' => 'Akun kewajiban utang ke supplier'],
                                    'default_ar' => ['label' => 'Default Piutang Usaha (Accounts Receivable)', 'desc' => 'Akun piutang tagihan customer'],
                                    'default_gr_clearing' => ['label' => 'Default GR/IR Clearing (Goods Receipt)', 'desc' => 'Akun penampung penerimaan barang sebelum invoice terbit'],
                                    'default_stock_adjustment' => ['label' => 'Default Selisih Stok (Stock Opname)', 'desc' => 'Akun penyesuaian selisih stok opname'],
                                    'default_opening_balance' => ['label' => 'Default Ekuitas Saldo Awal (Modal)', 'desc' => 'Akun ekuitas/modal kredit saat konfirmasi Saldo Awal Stok'],
                                    'realized_forex_gain' => ['label' => 'Keuntungan Selisih Kurs (Realized Forex Gain)', 'desc' => 'Akun pendapatan selisih kurs saat pelunasan utang/piutang valas (Pendapatan Lain-lain)'],
                                    'realized_forex_loss' => ['label' => 'Kerugian Selisih Kurs (Realized Forex Loss)', 'desc' => 'Akun beban selisih kurs saat pelunasan utang/piutang valas (Beban Lain-lain)'],
                                ];
                            @endphp

                            <div class="row g-3">
                                @foreach($defaultKeys as $key => $meta)
                                    @php
                                        $selectedCoa = $mappings->get("default.{$key}")->coa_id ?? null;
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="mapping-box">
                                            <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                                            <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>
                                            
                                            <input type="hidden" name="mappings[{{ $key }}][group]" value="default">
                                            <input type="hidden" name="mappings[{{ $key }}][mapping_key]" value="{{ $key }}">
                                            <input type="hidden" name="mappings[{{ $key }}][description]" value="{{ $meta['label'] }}">
                                            
                                            <select class="form-select rounded-3 select-coa" name="mappings[{{ $key }}][coa_id]">
                                                <option value="">-- Pilih Akun COA --</option>
                                                @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}" {{ $selectedCoa == $coa->id ? 'selected' : '' }}>
                                                        {{ $coa->code }} - {{ $coa->name }} ({{ $coa->type_label }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- TAB 2: FREIGHT / ONGKIR -->
                        <div class="tab-pane fade" id="content-freight" role="tabpanel">
                            <h6 class="fw-bold mb-2 text-primary">Biaya Ongkos Kirim & Pengiriman</h6>
                            <p class="text-muted small mb-4">Pemetaan akun untuk Jasa Pengiriman (Freight-In pada Pembelian & Freight-Out pada Penjualan).</p>

                            @php
                                $freightKeys = [
                                    'freight_in' => ['label' => 'Biaya Angkut Pembelian (Freight-In / Landed Cost)', 'desc' => 'Digunakan saat menginput ongkir pada Purchase Order (menambah modal barang/HPP)'],
                                    'freight_out' => ['label' => 'Beban Pengiriman Penjualan (Freight-Out)', 'desc' => 'Digunakan saat perusahaan menanggung ongkir kirim barang ke customer'],
                                ];
                            @endphp

                            <div class="row g-3">
                                @foreach($freightKeys as $key => $meta)
                                    @php
                                        $selectedCoa = $mappings->get("freight.{$key}")->coa_id ?? null;
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="mapping-box">
                                            <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                                            <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>

                                            <input type="hidden" name="mappings[freight_{{ $key }}][group]" value="freight">
                                            <input type="hidden" name="mappings[freight_{{ $key }}][mapping_key]" value="{{ $key }}">
                                            <input type="hidden" name="mappings[freight_{{ $key }}][description]" value="{{ $meta['label'] }}">

                                            <select class="form-select rounded-3 select-coa" name="mappings[freight_{{ $key }}][coa_id]">
                                                <option value="">-- Gunakan Default HPP / Beban --</option>
                                                @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}" {{ $selectedCoa == $coa->id ? 'selected' : '' }}>
                                                        {{ $coa->code }} - {{ $coa->name }} ({{ $coa->type_label }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- TAB 3: KATEGORI BARANG -->
                        <div class="tab-pane fade" id="content-category" role="tabpanel">
                            <h6 class="fw-bold mb-2 text-primary">Pemetaan Khusus Kategori Barang</h6>
                            <p class="text-muted small mb-4">Opsional. Isi jika kategori tertentu memiliki akun persediaan terpisah (misal: Persediaan Bahan Baku vs Barang Jadi).</p>

                            <div class="table-responsive border rounded-3">
                                <table class="table table-hover table-akuntansi align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama Kategori</th>
                                            <th style="width: 45%;">Akun Persediaan (Inventory)</th>
                                            <th style="width: 45%;">Akun HPP (COGS)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @forelse($categories as $cat)
                                            @php
                                                $keyInv = "cat_{$cat->id}_inventory";
                                                $keyCogs = "cat_{$cat->id}_cogs";
                                                $selectedInv = $mappings->get("inventory_category.{$keyInv}")->coa_id ?? null;
                                                $selectedCogs = $mappings->get("inventory_category.{$keyCogs}")->coa_id ?? null;
                                            @endphp
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $cat->nama_kategori }}</td>
                                                <td>
                                                    <input type="hidden" name="mappings[{{ $keyInv }}][group]" value="inventory_category">
                                                    <input type="hidden" name="mappings[{{ $keyInv }}][mapping_key]" value="{{ $keyInv }}">
                                                    <select class="form-select form-select-sm rounded-2 select-coa" name="mappings[{{ $keyInv }}][coa_id]">
                                                        <option value="">-- Gunakan Default Persediaan --</option>
                                                        @foreach($coas as $coa)
                                                            <option value="{{ $coa->id }}" {{ $selectedInv == $coa->id ? 'selected' : '' }}>
                                                                {{ $coa->code }} - {{ $coa->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="mappings[{{ $keyCogs }}][group]" value="inventory_category">
                                                    <input type="hidden" name="mappings[{{ $keyCogs }}][mapping_key]" value="{{ $keyCogs }}">
                                                    <select class="form-select form-select-sm rounded-2 select-coa" name="mappings[{{ $keyCogs }}][coa_id]">
                                                        <option value="">-- Gunakan Default HPP --</option>
                                                        @foreach($coas as $coa)
                                                            <option value="{{ $coa->id }}" {{ $selectedCogs == $coa->id ? 'selected' : '' }}>
                                                                {{ $coa->code }} - {{ $coa->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Belum ada Kategori Barang.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 4: PAJAK -->
                        <div class="tab-pane fade" id="content-tax" role="tabpanel">
                            <h6 class="fw-bold mb-2 text-primary">Pemetaan Akun Pajak</h6>
                            <p class="text-muted small mb-4">Hubungkan Master Pajak dengan Akun PPN Masukan / PPN Keluaran.</p>

                            <div class="table-responsive border rounded-3">
                                <table class="table table-hover table-akuntansi align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama Pajak</th>
                                            <th>Tarif (%)</th>
                                            <th style="width: 40%;">Akun PPN Masukan (Pembelian)</th>
                                            <th style="width: 40%;">Akun PPN Keluaran (Penjualan)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @forelse($taxes as $tax)
                                            @php
                                                $keyIn = "tax_{$tax->id}_in";
                                                $keyOut = "tax_{$tax->id}_out";
                                                $selectedIn = $mappings->get("tax.{$keyIn}")->coa_id ?? null;
                                                $selectedOut = $mappings->get("tax.{$keyOut}")->coa_id ?? null;
                                            @endphp
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $tax->nama_pajak }}</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">{{ $tax->tarif }}%</span></td>
                                                <td>
                                                    <input type="hidden" name="mappings[{{ $keyIn }}][group]" value="tax">
                                                    <input type="hidden" name="mappings[{{ $keyIn }}][mapping_key]" value="{{ $keyIn }}">
                                                    <select class="form-select form-select-sm rounded-2 select-coa" name="mappings[{{ $keyIn }}][coa_id]">
                                                        <option value="">-- Pilih Akun PPN Masukan --</option>
                                                        @foreach($coas as $coa)
                                                            <option value="{{ $coa->id }}" {{ $selectedIn == $coa->id ? 'selected' : '' }}>
                                                                {{ $coa->code }} - {{ $coa->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="mappings[{{ $keyOut }}][group]" value="tax">
                                                    <input type="hidden" name="mappings[{{ $keyOut }}][mapping_key]" value="{{ $keyOut }}">
                                                    <select class="form-select form-select-sm rounded-2 select-coa" name="mappings[{{ $keyOut }}][coa_id]">
                                                        <option value="">-- Pilih Akun PPN Keluaran --</option>
                                                        @foreach($coas as $coa)
                                                            <option value="{{ $coa->id }}" {{ $selectedOut == $coa->id ? 'selected' : '' }}>
                                                                {{ $coa->code }} - {{ $coa->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Belum ada Master Pajak.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 5: METODE PEMBAYARAN -->
                        <div class="tab-pane fade" id="content-payment" role="tabpanel">
                            <h6 class="fw-bold mb-2 text-primary">Pemetaan Metode Pembayaran (Kas & Bank)</h6>
                            <p class="text-muted small mb-4">Hubungkan metode pembayaran dengan Akun Kas / Rekening Bank yang bersangkutan.</p>

                            <div class="table-responsive border rounded-3">
                                <table class="table table-hover table-akuntansi align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <th>Kode</th>
                                            <th style="width: 60%;">Akun Kas / Bank COA</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @forelse($paymentMethods as $pm)
                                            @php
                                                $keyPm = "pm_{$pm->id}";
                                                $selectedPm = $mappings->get("payment_method.{$keyPm}")->coa_id ?? null;
                                            @endphp
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $pm->nama_metode }}</td>
                                                <td><span class="badge bg-light text-dark border font-monospace rounded-pill">{{ $pm->kode_metode }}</span></td>
                                                <td>
                                                    <input type="hidden" name="mappings[{{ $keyPm }}][group]" value="payment_method">
                                                    <input type="hidden" name="mappings[{{ $keyPm }}][mapping_key]" value="{{ $keyPm }}">
                                                    <select class="form-select form-select-sm rounded-2 select-coa" name="mappings[{{ $keyPm }}][coa_id]">
                                                        <option value="">-- Pilih Akun Kas / Bank --</option>
                                                        @foreach($coas as $coa)
                                                            <option value="{{ $coa->id }}" {{ $selectedPm == $coa->id ? 'selected' : '' }}>
                                                                {{ $coa->code }} - {{ $coa->name }} ({{ $coa->type_label }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Belum ada Metode Pembayaran.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 6: BARANG KELUAR (GOODS ISSUE) -->
                        <div class="tab-pane fade" id="content-goods-issue" role="tabpanel">
                            <h6 class="fw-bold mb-1 text-primary"><i class="bi bi-box-arrow-up me-2"></i>Pemetaan Akun Jurnal Barang Keluar (Goods Issue)</h6>
                            <p class="text-muted small mb-4">Atur akun COA yang digunakan saat membuat jurnal otomatis dari transaksi Pengeluaran Barang (GI). Akun Kredit (Persediaan) adalah akun yang akan dikurangi nilainya setiap kali barang keluar dari gudang.</p>

                            @php
                                $giKeys = [
                                    'gi_credit_inventory' => [
                                        'label' => 'Akun Kredit Persediaan (COA yang dikurangi saat barang keluar)',
                                        'desc'  => 'Akun persediaan/inventory yang nilainya berkurang saat terjadi pengeluaran barang. Contoh: Persediaan Barang Dagang.',
                                        'group' => 'goods_issue',
                                        'placeholder' => '-- Gunakan Default Persediaan (dari tab Default) --',
                                    ],
                                    'gi_default_expense' => [
                                        'label' => 'Akun Beban Default Pengeluaran Barang',
                                        'desc'  => 'Akun beban/biaya bawaan jika Tipe Pengeluaran tidak memiliki mapping COA di Master Tipe Pengeluaran. Contoh: Beban Lain-lain.',
                                        'group' => 'goods_issue',
                                        'placeholder' => '-- Gunakan Default HPP / COGS (dari tab Default) --',
                                    ],
                                ];
                            @endphp

                            <div class="row g-3">
                                @foreach($giKeys as $key => $meta)
                                    @php
                                        $selectedCoa = $mappings->get("{$meta['group']}.{$key}")->coa_id ?? null;
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="mapping-box">
                                            <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                                            <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>

                                            <input type="hidden" name="mappings[gi_{{ $key }}][group]" value="{{ $meta['group'] }}">
                                            <input type="hidden" name="mappings[gi_{{ $key }}][mapping_key]" value="{{ $key }}">
                                            <input type="hidden" name="mappings[gi_{{ $key }}][description]" value="{{ $meta['label'] }}">

                                            <select class="form-select rounded-3 select-coa" name="mappings[gi_{{ $key }}][coa_id]">
                                                <option value="">{{ $meta['placeholder'] }}</option>
                                                @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}" {{ $selectedCoa == $coa->id ? 'selected' : '' }}>
                                                        {{ $coa->code }} - {{ $coa->name }} ({{ $coa->type_label }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-info border-0 rounded-3 mt-4 d-flex align-items-start gap-3" style="background: #eff6ff;">
                                <i class="bi bi-info-circle-fill text-primary mt-1" style="font-size:1.1rem;"></i>
                                <div class="small">
                                    <strong class="d-block mb-1">Prioritas Pencarian Akun Jurnal GI:</strong>
                                    <ol class="mb-0 ps-3">
                                        <li><strong>Kategori Barang</strong> → tab Kategori Barang (akun persediaan per kategori)</li>
                                        <li><strong>GI Credit Inventory</strong> → setting di tab ini (berlaku untuk semua GI)</li>
                                        <li><strong>Default Persediaan</strong> → tab Default Utang/Stok/HPP</li>
                                        <li><strong>Fallback Otomatis</strong> → COA bertipe Aset/Persediaan (jika semua kosong)</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Matikan Autocomplete Browser Secara Total
            $(document).on('focus', 'input:not([type="hidden"]), textarea', function() {
                $(this).attr('autocomplete', 'off');
                $(this).attr('autocorrect', 'off');
                $(this).attr('autocapitalize', 'off');
                $(this).attr('spellcheck', 'false');
            });

            // Initialize Select2 with Search
            function initSelect2() {
                if ($.fn.select2) {
                    $('.select-coa').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            const placeholder = $(this).find('option[value=""]').first().text().trim() || '-- Pilih Akun COA --';
                            $(this).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: placeholder,
                                allowClear: true,
                                dropdownParent: $(document.body)
                            });
                        }
                    });
                }
            }
            initSelect2();

            // Re-initialize / adjust on tab switch
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
                initSelect2();
            });

            $('#btnSaveAllMappings').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: '{{ route("account-mappings.update") }}',
                    type: 'POST',
                    data: $('#formAccountMapping').serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan');
                        const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
