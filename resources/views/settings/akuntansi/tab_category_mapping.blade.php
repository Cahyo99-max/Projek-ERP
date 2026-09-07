<div class="row g-4">
    {{-- Main Content Area: Category Mapping Table --}}
    <div class="col-lg-9">
        {{-- Cinematic Header & Toolbar --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.03) 0%, rgba(59, 130, 246, 0.05) 100%);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm flex-shrink-0"
                         style="width: 46px; height: 46px; background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold m-0 text-dark">Mapping Kategori Barang</h5>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.75rem;">
                                {{ count($categories) }} Kategori
                            </span>
                        </div>
                        <p class="text-muted small m-0 mt-0.5">Konfigurasi otomatis akun Persediaan, Retur, Selisih Stok, dan HPP untuk setiap kategori material.</p>
                    </div>
                </div>

                {{-- Toolbar Actions --}}
                <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-md-end">
                    {{-- Search Filter --}}
                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <span class="input-group-text bg-body border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchCategoryMapping" class="form-control bg-body border-start-0 rounded-end-pill pe-3" 
                               placeholder="Cari kategori..." autocomplete="off">
                    </div>

                    {{-- Live Status Indicator --}}
                    <span id="catMappingStatus" class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 d-none d-sm-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Semua Tersimpan</span>
                    </span>

                    {{-- Glowing Save Button --}}
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-semibold d-inline-flex align-items-center gap-1.5" id="btnSaveCategoryMapping" style="background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%); border: none;">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Simpan Mapping</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Horizontal Scroll Helper Notice --}}
        <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.76rem;">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5">
                    <i class="bi bi-arrows-left-right me-1"></i> Geser Tabel
                </span>
                <span>Tabel dapat digeser ke samping untuk melihat seluruh akun pemetaan.</span>
            </div>
            <small class="text-muted" style="font-size: 0.74rem;">
                <i class="bi bi-pin-angle-fill text-info me-1"></i> Kolom Kategori Terkunci (*Sticky*)
            </small>
        </div>

        {{-- Form & Table Container with Horizontal Drag-to-Scroll --}}
        <form id="formCategoryMapping" autocomplete="off">
            @csrf
            <div class="table-responsive rounded-4 shadow-sm border p-0 table-category-wrapper" style="background-color: var(--bs-body-bg);">
                <table class="table table-akuntansi align-middle mb-0" id="tableCategoryMapping" style="min-width: 1380px;">
                    <thead>
                        <tr>
                            <th style="width: 55px; min-width: 55px;" class="text-center sticky-col-no">NO</th>
                            <th style="width: 240px; min-width: 240px;" class="sticky-col-cat">KATEGORI BARANG</th>
                            <th style="width: 270px; min-width: 270px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-box-seam text-primary"></i>
                                    <span>AKUN PERSEDIAAN</span>
                                </div>
                            </th>
                            <th style="width: 270px; min-width: 270px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-arrow-return-left text-warning"></i>
                                    <span>AKUN RETUR BELI</span>
                                </div>
                            </th>
                            <th style="width: 270px; min-width: 270px;">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-sliders text-info"></i>
                                    <span>AKUN SELISIH STOK</span>
                                </div>
                            </th>
                            <th style="width: 270px; min-width: 270px;">
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
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary"
                                             style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: 700;">
                                            {{ strtoupper(substr($cat->nama_kategori, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block text-truncate" style="max-width: 175px;" title="{{ $cat->nama_kategori }}">{{ $cat->nama_kategori }}</span>
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

    {{-- Sidebar: Cinematic Information Panel --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.02) 0%, rgba(59, 130, 246, 0.04) 100%);">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                    <i class="bi bi-lightbulb-fill text-warning"></i>
                    <span>Panduan Alur Jurnal</span>
                </h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                    <i class="bi bi-dot"></i> Live Sync
                </span>
            </div>

            <p class="text-muted small mb-3.5" style="line-height: 1.5;">
                Pemetaan akun kategori barang akan digunakan otomatis oleh modul transaksi ketika barang dengan kategori terkait diproses:
            </p>
            
            <div class="d-flex flex-column gap-3">
                {{-- Persediaan --}}
                <div class="p-2.5 rounded-3 border bg-body shadow-xs">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">
                            <i class="bi bi-box-seam" style="font-size: 0.7rem;"></i>
                        </span>
                        <strong class="text-dark small">1. Akun Persediaan</strong>
                    </div>
                    <p class="text-muted m-0 small" style="font-size: 0.75rem;">
                        Otomatis terdebit saat <b>Penerimaan Barang (Goods Receipt / GR)</b> dan bertambah pada Neraca Aktiva.
                    </p>
                </div>

                {{-- Retur Pembelian --}}
                <div class="p-2.5 rounded-3 border bg-body shadow-xs">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">
                            <i class="bi bi-arrow-return-left" style="font-size: 0.7rem;"></i>
                        </span>
                        <strong class="text-dark small">2. Akun Retur Pembelian</strong>
                    </div>
                    <p class="text-muted m-0 small" style="font-size: 0.75rem;">
                        Otomatis terkredit saat pengembalian material/barang reject ke vendor.
                    </p>
                </div>

                {{-- Selisih Stok --}}
                <div class="p-2.5 rounded-3 border bg-body shadow-xs">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-info bg-opacity-10 text-info rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">
                            <i class="bi bi-sliders" style="font-size: 0.7rem;"></i>
                        </span>
                        <strong class="text-dark small">3. Akun Selisih Stok</strong>
                    </div>
                    <p class="text-muted m-0 small" style="font-size: 0.75rem;">
                        Digunakan pada saat <b>Penyesuaian Stok (Stock Adjustment)</b> & Stock Opname gudang.
                    </p>
                </div>

                {{-- Akun HPP --}}
                <div class="p-2.5 rounded-3 border bg-body shadow-xs">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">
                            <i class="bi bi-receipt" style="font-size: 0.7rem;"></i>
                        </span>
                        <strong class="text-dark small">4. Akun HPP</strong>
                    </div>
                    <p class="text-muted m-0 small" style="font-size: 0.75rem;">
                        Digunakan saat pengeluaran bahan produksi (SPK) atau pengakuan beban pokok pada Invoice Penjualan.
                    </p>
                </div>
            </div>

            {{-- Fallback Callout --}}
            <div class="mt-4 p-2.5 rounded-3 border border-dashed text-center bg-body">
                <small class="text-muted" style="font-size: 0.73rem;">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Bila dikosongkan (<em>Gunakan Default</em>), sistem otomatis memakai akun dari <b>Account Mapping System</b>.
                </small>
            </div>
        </div>
    </div>
</div>
