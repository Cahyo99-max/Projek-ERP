<div class="row g-4">
    {{-- Main Content Area: Category Mapping Table (Full Width) --}}
    <div class="col-12">
        {{-- Cinematic Header & Toolbar --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.03) 0%, rgba(59, 130, 246, 0.05) 100%);">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm flex-shrink-0"
                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold m-0 text-body-emphasis">Mapping Kategori Barang</h5>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.75rem;">
                                {{ count($categories) }} Kategori
                            </span>
                        </div>
                        <p class="text-muted small m-0 mt-0.5">Konfigurasi otomatis akun Persediaan, Retur, Selisih Stok, dan HPP untuk setiap kategori material.</p>
                    </div>
                </div>

                {{-- Toolbar Actions --}}
                <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-lg-auto justify-content-lg-end">
                    {{-- Search Filter --}}
                    <div class="input-group input-group-sm" style="min-width: 240px; max-width: 320px;">
                        <span class="input-group-text bg-body border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchCategoryMapping" class="form-control bg-body border-start-0 rounded-end-pill pe-3" 
                               placeholder="Cari kategori barang..." autocomplete="off">
                    </div>

                    {{-- Live Status Indicator --}}
                    <span id="catMappingStatus" class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 d-none d-sm-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Semua Tersimpan</span>
                    </span>

                    {{-- Save Button --}}
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2" id="btnSaveCategoryMapping" style="background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%); border: none;">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Horizontal Scroll Helper Notice --}}
        <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.76rem;">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-0.5">
                    <i class="bi bi-arrows-left-right me-1"></i> Responsif
                </span>
                <span>Geser ke samping jika menggunakan layar compact untuk melihat seluruh kolom akun.</span>
            </div>
            <small class="text-muted d-none d-md-inline" style="font-size: 0.74rem;">
                <i class="bi bi-pin-angle-fill text-info me-1"></i> Kolom Kategori Terkunci (<em>Sticky</em>)
            </small>
        </div>

        {{-- Form & Table Container with Horizontal Drag-to-Scroll --}}
        <form id="formCategoryMapping" autocomplete="off">
            @csrf
            <div class="table-responsive rounded-4 shadow-sm border p-0 table-category-wrapper" style="background-color: var(--bs-body-bg);">
                <table class="table table-akuntansi align-middle mb-0" id="tableCategoryMapping" style="min-width: 1100px; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 55px; min-width: 55px;" class="text-center sticky-col-no">NO</th>
                            <th style="width: 280px; min-width: 280px;" class="sticky-col-cat">KATEGORI BARANG</th>
                            <th style="width: 25%; min-width: 210px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-box-seam text-primary"></i>
                                    <span>AKUN PERSEDIAAN</span>
                                </div>
                            </th>
                            <th style="width: 25%; min-width: 210px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-arrow-return-left text-warning"></i>
                                    <span>AKUN RETUR BELI</span>
                                </div>
                            </th>
                            <th style="width: 25%; min-width: 210px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-sliders text-info"></i>
                                    <span>AKUN SELISIH STOK</span>
                                </div>
                            </th>
                            <th style="width: 25%; min-width: 210px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-receipt text-danger"></i>
                                    <span>AKUN HPP</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($categories as $idx => $cat)
                            @php
                                $selInv = $mappings->get("inventory_category.cat_{$cat->id}_inventory")->coa_id ?? null;
                                $selRet = $mappings->get("inventory_category.cat_{$cat->id}_return")->coa_id ?? null;
                                $selAdj = $mappings->get("inventory_category.cat_{$cat->id}_adjustment")->coa_id ?? null;
                                $selCogs = $mappings->get("inventory_category.cat_{$cat->id}_cogs")->coa_id ?? null;
                            @endphp
                            <tr class="category-map-row" data-category-name="{{ strtolower($cat->nama_kategori) }}">
                                <td class="text-center text-muted fw-bold sticky-col-no">{{ $idx + 1 }}</td>
                                <td class="sticky-col-cat">
                                    <div class="d-flex align-items-center gap-2.5" style="min-width: 250px;">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary border border-primary-subtle"
                                             style="width: 34px; height: 34px; font-size: 0.75rem; font-weight: 700;">
                                            {{ strtoupper(substr($cat->nama_kategori, 0, 2)) }}
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <span class="fw-bold text-body-emphasis d-block" style="white-space: normal; word-break: break-word; line-height: 1.35;">{{ $cat->nama_kategori }}</span>
                                            @if(!empty($cat->kode_kategori))
                                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $cat->kode_kategori }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm rounded-pill cat-select font-monospace {{ $selInv ? 'border-primary' : '' }}" 
                                            name="category_mappings[{{ $cat->id }}][inventory]" style="font-size: 0.78rem;">
                                        <option value="">-- Gunakan Default --</option>
                                        @foreach($detailCoas as $coa)
                                            <option value="{{ $coa->id }}" {{ $selInv == $coa->id ? 'selected' : '' }}>
                                                [{{ $coa->code }}] {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm rounded-pill cat-select font-monospace {{ $selRet ? 'border-warning' : '' }}" 
                                            name="category_mappings[{{ $cat->id }}][return]" style="font-size: 0.78rem;">
                                        <option value="">-- Gunakan Default --</option>
                                        @foreach($detailCoas as $coa)
                                            <option value="{{ $coa->id }}" {{ $selRet == $coa->id ? 'selected' : '' }}>
                                                [{{ $coa->code }}] {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm rounded-pill cat-select font-monospace {{ $selAdj ? 'border-info' : '' }}" 
                                            name="category_mappings[{{ $cat->id }}][adjustment]" style="font-size: 0.78rem;">
                                        <option value="">-- Gunakan Default --</option>
                                        @foreach($detailCoas as $coa)
                                            <option value="{{ $coa->id }}" {{ $selAdj == $coa->id ? 'selected' : '' }}>
                                                [{{ $coa->code }}] {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm rounded-pill cat-select font-monospace {{ $selCogs ? 'border-danger' : '' }}" 
                                            name="category_mappings[{{ $cat->id }}][cogs]" style="font-size: 0.78rem;">
                                        <option value="">-- Gunakan Default --</option>
                                        @foreach($detailCoas as $coa)
                                            <option value="{{ $coa->id }}" {{ $selCogs == $coa->id ? 'selected' : '' }}>
                                                [{{ $coa->code }}] {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block text-secondary opacity-50 mb-2"></i>
                                    Belum ada data Kategori Barang yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    {{-- Panduan Alur Jurnal: Horizontal Executive Cards Layout Under the Table --}}
    <div class="col-12 mt-3">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.02) 0%, rgba(59, 130, 246, 0.03) 100%);">
            {{-- Header Panduan --}}
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" style="width: 36px; height: 36px;">
                        <i class="bi bi-lightbulb-fill fs-6"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-body-emphasis m-0">Panduan Alur Jurnal Otomatis</h6>
                        <small class="text-muted">Pemetaan akun kategori barang akan digunakan otomatis oleh modul transaksi ketika barang terkait diproses.</small>
                    </div>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 6px; height: 6px;"></span>
                    <span>Live Sync Transaksi</span>
                </span>
            </div>

            {{-- 4 Alur Cards Grid --}}
            <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-xl-4">
                {{-- 1. Akun Persediaan --}}
                <div class="col">
                    <div class="p-3 rounded-4 border bg-body shadow-xs h-100 d-flex flex-column transition-hover" style="border-top: 3px solid #3b82f6 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                    <i class="bi bi-box-seam" style="font-size: 0.75rem;"></i>
                                </span>
                                <strong class="text-body-emphasis small">1. Akun Persediaan</strong>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 0.65rem;">ASET</span>
                        </div>
                        <ul class="text-muted m-0 small ps-3 flex-grow-1" style="font-size: 0.76rem; line-height: 1.55;">
                            <li><span class="text-success fw-bold font-monospace">[DEBET]</span> Saat <b>Penerimaan Barang (GR)</b> dari Supplier / Pembelian (stok bertambah).</li>
                            <li><span class="text-danger fw-bold font-monospace">[KREDIT]</span> Saat <b>Pengiriman Barang (DO)</b> ke Pelanggan (stok berkurang).</li>
                        </ul>
                    </div>
                </div>

                {{-- 2. Akun Retur Pembelian --}}
                <div class="col">
                    <div class="p-3 rounded-4 border bg-body shadow-xs h-100 d-flex flex-column transition-hover" style="border-top: 3px solid #eab308 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                    <i class="bi bi-arrow-return-left" style="font-size: 0.75rem;"></i>
                                </span>
                                <strong class="text-body-emphasis small">2. Akun Retur Beli</strong>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace" style="font-size: 0.65rem;">KONTRA</span>
                        </div>
                        <p class="text-muted m-0 small flex-grow-1" style="font-size: 0.76rem; line-height: 1.55;">
                            <span class="text-danger fw-bold font-monospace">[KREDIT]</span> Otomatis saat pengembalian material atau barang reject ke pihak vendor supplier.
                        </p>
                    </div>
                </div>

                {{-- 3. Akun Selisih Stok --}}
                <div class="col">
                    <div class="p-3 rounded-4 border bg-body shadow-xs h-100 d-flex flex-column transition-hover" style="border-top: 3px solid #06b6d4 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info bg-opacity-10 text-info rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                    <i class="bi bi-sliders" style="font-size: 0.75rem;"></i>
                                </span>
                                <strong class="text-body-emphasis small">3. Akun Selisih Stok</strong>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size: 0.65rem;">ADJ</span>
                        </div>
                        <p class="text-muted m-0 small flex-grow-1" style="font-size: 0.76rem; line-height: 1.55;">
                            Digunakan pada modul <b>Penyesuaian Stok (Stock Adjustment)</b> &amp; <b>Stock Opname</b> gudang untuk menampung selisih lebih/kurang stok fisik.
                        </p>
                    </div>
                </div>

                {{-- 4. Akun HPP --}}
                <div class="col">
                    <div class="p-3 rounded-4 border bg-body shadow-xs h-100 d-flex flex-column transition-hover" style="border-top: 3px solid #ef4444 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                    <i class="bi bi-receipt" style="font-size: 0.75rem;"></i>
                                </span>
                                <strong class="text-body-emphasis small">4. Akun HPP (Beban Pokok)</strong>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size: 0.65rem;">BEBAN</span>
                        </div>
                        <ul class="text-muted m-0 small ps-3 flex-grow-1" style="font-size: 0.76rem; line-height: 1.55;">
                            <li><span class="text-success fw-bold font-monospace">[DEBET]</span> Saat <b>Pengiriman Barang (DO)</b> disetujui (ACC) sebagai beban pokok modal produk terjual.</li>
                            <li><span class="text-success fw-bold font-monospace">[DEBET]</span> Saat <b>Pengeluaran Bahan Baku</b> ke lini produksi (SPK).</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Fallback Callout Note --}}
            <div class="mt-3 p-2.5 rounded-3 border border-dashed bg-body d-flex align-items-center justify-content-center gap-2 text-center">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <small class="text-muted" style="font-size: 0.78rem;">
                    Bila pilihan akun dikosongkan (<em>Gunakan Default</em>), sistem otomatis memakai akun bawaan dari <b>Account Mapping System</b>.
                </small>
            </div>
        </div>
    </div>
</div>
