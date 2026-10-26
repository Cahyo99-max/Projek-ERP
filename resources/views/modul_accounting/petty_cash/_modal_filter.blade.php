<!-- MODAL FILTER & PENGURUTAN PETTY CASH (LEBAR / MODAL-LG DENGAN CETAK PDF) -->
<div class="modal fade" id="modalFilterPettyCash" tabindex="-1" aria-labelledby="modalFilterPettyCashLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content pc-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <form action="{{ route('accounting.petty-cash.index') }}" method="GET" id="formModalFilterPettyCash">
                <!-- Pertahankan Parameter Pencarian & Per Page jika ada -->
                @if(!empty($search))
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif
                @if(!empty($perPage) && $perPage != 10)
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif

                <!-- Modal Header -->
                <div class="modal-header pc-modal-header px-4 py-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold pc-text-title-adaptive mb-0" id="modalFilterPettyCashLabel">Filter &amp; Cetak Laporan Kas Kecil</h6>
                            <small class="pc-text-muted-adaptive" style="font-size: 12px;">Saring data kas kecil atau cetak laporan buku kas ke format PDF</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body (2 Kolom Responsif) -->
                <div class="modal-body px-4 py-3.5" style="max-height: 72vh; overflow-y: auto;">
                    <div class="row g-3">
                        
                        <!-- KOLOM KIRI: Periode, Kategori & Akun Kas -->
                        <div class="col-12 col-md-6 border-end-md pc-border-adaptive pe-md-3">
                            
                            <!-- 1. Rentang Tanggal Transaksi -->
                            <div class="mb-3">
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center justify-content-between mb-2">
                                    <span><i class="bi bi-calendar3 text-info me-1"></i> Rentang Tanggal</span>
                                    <span class="pc-text-muted-adaptive font-monospace" style="font-size: 11px;">Periode</span>
                                </label>
                                
                                <!-- Quick Presets -->
                                <div class="d-flex flex-wrap gap-1.5 mb-2.5">
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 pc-btn-preset-adaptive btn-filter-date-preset" style="font-size: 11px;" data-preset="this_month">
                                        Bulan Ini
                                    </button>
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 pc-btn-preset-adaptive btn-filter-date-preset" style="font-size: 11px;" data-preset="last_month">
                                        Bulan Lalu
                                    </button>
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 pc-btn-preset-adaptive btn-filter-date-preset" style="font-size: 11px;" data-preset="today">
                                        Hari Ini
                                    </button>
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 pc-btn-preset-adaptive btn-filter-date-preset" style="font-size: 11px;" data-preset="this_year">
                                        Tahun Ini
                                    </button>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small pc-text-muted-adaptive mb-1" style="font-size: 11px;">Dari Tanggal:</label>
                                        <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm rounded-3 pc-input-adaptive" value="{{ $startDate }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small pc-text-muted-adaptive mb-1" style="font-size: 11px;">Sampai Tanggal:</label>
                                        <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm rounded-3 pc-input-adaptive" value="{{ $endDate }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="pc-border-adaptive my-3">

                            <!-- 2. Kategori Transaksi (Mapping) -->
                            <div class="mb-3">
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1.5">
                                    <i class="bi bi-tag text-warning me-1"></i> Kategori Transaksi
                                </label>
                                <select name="kategori" id="filter_kategori" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                    <option value="all">-- Semua Kategori --</option>
                                    @if(isset($categoryMapping) && is_array($categoryMapping))
                                        @foreach($categoryMapping as $catKey => $catInfo)
                                             <option value="{{ $catKey }}" {{ ($kategoriFilter ?? '') == $catKey ? 'selected' : '' }}>
                                                {{ $catInfo['label'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="pc-text-muted-adaptive d-block mt-1" style="font-size: 11px;">Pos anggaran pengeluaran kas kecil.</small>
                            </div>

                            <hr class="pc-border-adaptive my-3">

                            <!-- 3. Akun Kas Kecil (COA Kas) -->
                            <div>
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1.5">
                                    <i class="bi bi-wallet2 text-success me-1"></i> Akun Kas (COA Kas) <span class="text-danger">*</span>
                                </label>
                                <select name="coa_kas_id" id="filter_coa_kas_id" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;" required>
                                    <option value="" disabled {{ empty($coaKasFilter) || $coaKasFilter === 'all' ? 'selected' : '' }}>-- Pilih Akun Kas (Wajib) --</option>
                                    @if(isset($coaKasAccounts))
                                        @foreach($coaKasAccounts as $kas)
                                            @php
                                                $cId = $kas->coa_id ?? $kas->coa?->id;
                                                $cCode = $kas->coa?->code ? '[' . $kas->coa->code . '] ' : '';
                                                $cName = $kas->nama_akun ?: ($kas->coa?->name ?? 'Kas');
                                                $saldo = (float) $kas->saldo_berjalan;
                                                $isSelected = (($coaKasFilter ?? '') == $cId);
                                            @endphp
                                            @if($cId)
                                                <option value="{{ $cId }}" {{ $isSelected ? 'selected' : '' }}>
                                                    💳 {{ $cCode }}{{ $cName }} (Saldo: Rp {{ number_format($saldo, 2, ',', '.') }})
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-danger-emphasis d-block mt-1 fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-asterisk text-danger me-1"></i>Akun kas wajib dipilih untuk menyaring atau mencetak laporan.
                                </small>
                            </div>

                        </div>

                        <!-- KOLOM KANAN: Pemohon, Tender, Tipe & Urutan -->
                        <div class="col-12 col-md-6 ps-md-3">
                            
                            <!-- 4. Nama Pemohon / Pengaju -->
                            <div class="mb-3">
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1.5">
                                    <i class="bi bi-person-badge text-info me-1"></i> Nama Pemohon
                                </label>
                                <select name="nama_pengaju" id="filter_nama_pengaju" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                    <option value="">-- Semua Pemohon --</option>
                                    @if(isset($pemohonList) && $pemohonList->isNotEmpty())
                                        @foreach($pemohonList as $pNama)
                                            <option value="{{ $pNama }}" {{ ($pemohonFilter ?? '') == $pNama ? 'selected' : '' }}>
                                                👤 {{ $pNama }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="pc-text-muted-adaptive d-block mt-1" style="font-size: 11px;">Personil atau pemohon dana kas kecil.</small>
                            </div>

                            <hr class="pc-border-adaptive my-3">

                            <!-- 5. Tender / Proyek -->
                            <div class="mb-3">
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center justify-content-between mb-1.5">
                                    <span><i class="bi bi-bullseye text-primary me-1"></i> Alokasi Tender / Proyek</span>
                                    @if(!empty($activeBranchId))
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-truncate" style="font-size: 9px; max-width: 170px;">
                                            Cabang {{ $currentBranch->nama_cabang ?? 'Aktif' }}
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle text-truncate" style="font-size: 9px; max-width: 170px;">
                                            {{ $currentCompany->nama_perusahaan ?? 'PT Pusat' }}
                                        </span>
                                    @endif
                                </label>
                                <select name="tender_id" id="filter_tender_id" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                    <option value="">-- Semua Tender / Proyek --</option>
                                    @if(isset($tenders) && $tenders->isNotEmpty())
                                        @foreach($tenders as $t)
                                            @php
                                                $ownerLabel = $t->branch ? ('Cabang ' . $t->branch->nama_cabang) : ($t->company ? $t->company->nama_perusahaan : ($currentCompany->nama_perusahaan ?? 'PT Pusat'));
                                            @endphp
                                            <option value="{{ $t->id }}" {{ ($tenderFilter ?? '') == $t->id ? 'selected' : '' }}>
                                                🎯 {{ $t->nomor_kontrak }} - {{ $t->nama_tender }} ({{ $t->status ?? 'Aktif' }}) [{{ $ownerLabel }}]
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <hr class="pc-border-adaptive my-3">

                            <!-- 6. Tipe Transaksi -->
                            <div class="mb-3">
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1.5">
                                    <i class="bi bi-arrow-left-right text-success me-1"></i> Tipe Transaksi
                                </label>
                                <select name="tipe" id="filter_tipe" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                    <option value="all" {{ ($tipeFilter ?? 'all') == 'all' ? 'selected' : '' }}>-- Semua Tipe Transaksi --</option>
                                    <option value="Pengeluaran" {{ ($tipeFilter ?? '') == 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran Kas Kecil</option>
                                    <option value="Klaim" {{ ($tipeFilter ?? '') == 'Klaim' ? 'selected' : '' }}>Klaim Reimbursement</option>
                                </select>
                            </div>

                            <hr class="pc-border-adaptive my-3">

                            <!-- 7. Pengurutan Data (Sorting) -->
                            <div>
                                <label class="form-label pc-text-muted-adaptive small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1.5">
                                    <i class="bi bi-sort-down text-info me-1"></i> Urutan Data
                                </label>
                                <div class="row g-2">
                                    <div class="col-7">
                                        <select name="sort_by" id="filter_sort_by" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                            <option value="tanggal" {{ ($sortBy ?? 'tanggal') == 'tanggal' ? 'selected' : '' }}>📅 Tanggal Transaksi</option>
                                            <option value="kategori" {{ ($sortBy ?? '') == 'kategori' ? 'selected' : '' }}>🏷️ Kategori</option>
                                            <option value="nominal" {{ ($sortBy ?? '') == 'nominal' ? 'selected' : '' }}>💰 Nominal Rupiah</option>
                                            <option value="nomor_transaksi" {{ ($sortBy ?? '') == 'nomor_transaksi' ? 'selected' : '' }}>🔢 Nomor Transaksi</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <select name="sort_order" id="filter_sort_order" class="form-select form-select-sm rounded-3 pc-input-adaptive" style="width: 100%;">
                                            @if(($sortBy ?? 'tanggal') == 'kategori')
                                                <option value="asc" {{ ($sortOrder ?? 'asc') == 'asc' ? 'selected' : '' }}>A ke Z (Abjad A-Z)</option>
                                                <option value="desc" {{ ($sortOrder ?? '') == 'desc' ? 'selected' : '' }}>Z ke A (Terbalik)</option>
                                            @elseif(($sortBy ?? '') == 'nominal')
                                                <option value="desc" {{ ($sortOrder ?? 'desc') == 'desc' ? 'selected' : '' }}>Terbesar ke Terkecil</option>
                                                <option value="asc" {{ ($sortOrder ?? '') == 'asc' ? 'selected' : '' }}>Terkecil ke Terbesar</option>
                                            @elseif(($sortBy ?? '') == 'nomor_transaksi')
                                                <option value="desc" {{ ($sortOrder ?? 'desc') == 'desc' ? 'selected' : '' }}>Terbaru / Terbesar</option>
                                                <option value="asc" {{ ($sortOrder ?? '') == 'asc' ? 'selected' : '' }}>Terlama / Terkecil</option>
                                            @else
                                                <option value="desc" {{ ($sortOrder ?? 'desc') == 'desc' ? 'selected' : '' }}>Terbaru ke Terlama (Tanggal &amp; Jam &darr;)</option>
                                                <option value="asc" {{ ($sortOrder ?? '') == 'asc' ? 'selected' : '' }}>Terlama ke Terbaru (Tanggal &amp; Jam &uarr;)</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer pc-modal-footer px-4 py-3 justify-content-between">
                    
                    <!-- Kiri: Reset Default & Cetak PDF / Excel -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('accounting.petty-cash.index', request()->only('search')) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-flex align-items-center gap-1.5" title="Reset filter ke default">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span class="d-none d-sm-inline">Reset</span>
                        </a>

                        @can('cetak-pdf-petty-cash')
                        <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3.5 d-flex align-items-center gap-1.5 fw-semibold" id="btnCetakPdfPettyCash" title="Cetak Laporan PDF Kas Kecil sesuai filter">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                            <span>Cetak PDF</span>
                        </button>
                        @endcan

                        @can('export-excel-petty-cash')
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3.5 d-flex align-items-center gap-1.5 fw-semibold" id="btnCetakExcelPettyCash" title="Download Laporan Excel Kas Kecil (.xlsx) sesuai filter">
                            <i class="bi bi-file-earmark-excel-fill text-success"></i>
                            <span>Cetak Excel</span>
                        </button>
                        @endcan
                    </div>
                    
                    <!-- Kanan: Batal & Terapkan Filter -->
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        @can('filter-petty-cash')
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow d-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border: none;">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Terapkan Filter</span>
                        </button>
                        @endcan
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
