<x-layouts title="Manajemen Aset Tetap & Penyusutan">

    @push('styles')
    <style>
        .metric-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.08);
            border-color: rgba(59, 130, 246, 0.3);
        }
        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.4rem;
        }
        .table-modern th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 16px !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        .table-modern td {
            padding: 12px 16px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .panel-depresiasi {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        }
        .panel-depresiasi hr {
            border-color: rgba(255, 255, 255, 0.15);
        }
    </style>
    @endpush

    <!-- METRICS SECTION -->
    <div class="row g-4 mb-4">
        <!-- Metric 1: Total Perolehan -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Perolehan Aset</span>
                    <h3 class="fw-extrabold text-dark mb-0">Rp {{ number_format($totalPerolehan, 0, ',', '.') }}</h3>
                </div>
                <div class="metric-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>

        <!-- Metric 2: Akumulasi Penyusutan -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Akumulasi Penyusutan</span>
                    <h3 class="fw-extrabold text-danger mb-0">Rp {{ number_format($totalAkumulasiSusut, 0, ',', '.') }}</h3>
                </div>
                <div class="metric-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-graph-down-arrow"></i>
                </div>
            </div>
        </div>

        <!-- Metric 3: Nilai Buku -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Nilai Buku Aset Saat Ini</span>
                    <h3 class="fw-extrabold text-success mb-0">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</h3>
                </div>
                <div class="metric-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN BODY SECTION -->
    <div class="row g-4">
        <!-- LEFT: FIXED ASSETS REGISTRY -->
        <div class="col-12 col-lg-8">
            <ul class="nav nav-pills mb-3 bg-light p-1.5 rounded-3 d-inline-flex gap-2" id="assetTab" role="tablist" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-2 small fw-bold px-3 py-2" id="register-tab" data-bs-toggle="pill" data-bs-target="#register-panel" type="button" role="tab" aria-controls="register-panel" aria-selected="true">
                        <i class="bi bi-shield-check me-1"></i> Register Aset Tetap
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-2 small fw-bold px-3 py-2" id="recap-tab" data-bs-toggle="pill" data-bs-target="#recap-panel" type="button" role="tab" aria-controls="recap-panel" aria-selected="false">
                        <i class="bi bi-calendar-range me-1"></i> Riwayat Penyusutan Bulanan
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="assetTabContent">
                <!-- PANEL 1: REGISTER -->
                <div class="tab-pane fade show active" id="register-panel" role="tabpanel" aria-labelledby="register-tab">
                    <x-table_card tableId="tableAssets" title="Register Aset Tetap" icon="bi-shield-check">
                        <x-slot name="thead">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Kode Aset</th>
                                <th width="28%">Nama Aset</th>
                                <th width="15%">Tgl Perolehan</th>
                                <th width="15%">Harga Perolehan</th>
                                <th width="15%">Nilai Buku</th>
                                <th width="8%">Manfaat</th>
                                <th width="12%">Status</th>
                                <th width="5%" class="text-center">Aksi</th>
                            </tr>
                        </x-slot>
                        @foreach($assets as $index => $asset)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold shadow-sm">
                                    {{ $asset->kode_aset }}
                                </span>
                                @if($asset->nomor_seri)
                                <div class="small text-muted font-monospace mt-0.5" style="font-size: 0.72rem;">S/N: {{ $asset->nomor_seri }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $asset->barang->nama_barang }}</div>
                                <div class="small text-muted">Ref GR: {{ $asset->grDetail && $asset->grDetail->goodsReceipt ? $asset->grDetail->goodsReceipt->nomor_gr : '-' }}</div>
                            </td>
                            <td>{{ $asset->tanggal_perolehan->format('d/m/Y') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($asset->harga_perolehan, 0, ',', '.') }}</td>
                            <td class="fw-bold text-success">Rp {{ number_format($asset->nilai_buku_saat_ini, 0, ',', '.') }}</td>
                            <td class="text-center text-muted font-monospace">{{ $asset->masa_manfaat }} Bln</td>
                            <td>
                                @if($asset->status === 'Aktif')
                                    <span class="badge rounded-pill bg-success-soft text-success px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Aktif</span>
                                @elseif($asset->status === 'Habis Susut')
                                    <span class="badge rounded-pill bg-info-soft text-info px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Habis Susut</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-soft text-secondary px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">{{ $asset->status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-edit-asset" data-id="{{ $asset->id }}" title="Edit Detail Aset">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 btn-view-history" data-id="{{ $asset->id }}" title="Histori Penyusutan">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </x-table_card>
                </div>

                <!-- PANEL 2: RECAP -->
                <div class="tab-pane fade" id="recap-panel" role="tabpanel" aria-labelledby="recap-tab">
                    <x-table_card tableId="tableRecaps" title="Riwayat Penyusutan Bulanan" icon="bi-calendar-range">
                        <x-slot name="thead">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Periode</th>
                                <th width="25%" class="text-center">Aset Tersusut</th>
                                <th width="30%" class="text-end">Total Penyusutan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </x-slot>
                        @foreach($monthlyRecaps as $index => $recap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold text-dark">
                                    {{ date('F Y', strtotime($recap->periode_penyusutan . '-01')) }}
                                </span>
                                <div class="small text-muted font-monospace mt-0.5" style="font-size: 0.72rem;">{{ $recap->periode_penyusutan }}</div>
                            </td>
                            <td class="text-center fw-semibold text-secondary">{{ $recap->total_assets }} Unit</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($recap->total_penyusutan, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-view-monthly-detail" data-periode="{{ $recap->periode_penyusutan }}" title="Detail Penyusutan Bulanan">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </x-table_card>
                </div>
            </div>
        </div>

        <!-- RIGHT: DEPRECIATION CONTROL PANEL -->
        <div class="col-12 col-lg-4">
            <div class="panel-depresiasi shadow-lg">
                <h5 class="fw-bold text-white mb-2"><i class="bi bi-calculator-fill me-2 text-warning"></i> Penyusutan Berkala</h5>
                <p class="small text-white-50 mb-3">Jalankan perhitungan beban penyusutan semua aset tetap secara otomatis per bulan.</p>
                
                <hr>

                <div class="mb-3">
                    <small class="text-white-50 d-block">Penyusutan Terakhir Diproses:</small>
                    @if($latestPeriode)
                        <span class="badge bg-warning text-white rounded-pill px-3 py-1.5 fw-bold mt-1 fs-7">
                            <i class="bi bi-calendar-check me-1"></i> {{ date('M Y', strtotime($latestPeriode . '-01')) }}
                        </span>
                    @else
                        <span class="badge bg-secondary text-white rounded-pill px-3 py-1.5 fw-bold mt-1 fs-7">
                            Belum Pernah Diproses
                        </span>
                    @endif
                </div>

                <form id="formRunDepreciation">
                    <div class="mb-3">
                        <label class="form-label text-white-50 small fw-bold" for="dep_period">Pilih Periode Penyusutan Baru</label>
                        @php
                            // Calculate default next period based on latest period
                            if ($latestPeriode) {
                                $nextTime = strtotime('+1 month', strtotime($latestPeriode . '-01'));
                                $defaultPeriod = date('Y-m', $nextTime);
                            } else {
                                $defaultPeriod = date('Y-m');
                            }
                        @endphp
                        <input type="month" name="periode" id="dep_period" class="form-control form-control-sm rounded-pill text-dark fw-bold border-0 px-3 py-2 bg-white" 
                            value="{{ $defaultPeriod }}" required>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill text-white w-100 fw-bold py-2 mb-2 shadow-sm" id="btn_run_depreciation">
                        <i class="bi bi-play-circle me-1"></i> Proses Penyusutan
                    </button>
                </form>

                 @if($latestPeriode)
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-semibold py-2 mt-2" id="btn_rollback_depreciation" data-periode="{{ $latestPeriode }}">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback Periode {{ date('M Y', strtotime($latestPeriode . '-01')) }}
                </button>
                @endif
            </div>

            <!-- PROJECTION WIDGET -->
            <div class="card border-0 shadow-sm mt-4 p-4" style="border-radius: 16px; background-color: var(--tbl-card-bg, #f0f7ff); border: 1px solid var(--tbl-card-border, #bfdbfe) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-magic me-2 text-primary"></i> Proyeksi Bulan Depan</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.7rem;">Estimasi</span>
                </div>
                <div class="p-3 bg-light bg-opacity-50 rounded-3 mb-2 border">
                    <small class="text-secondary d-block">Bulan Proyeksi:</small>
                    <span class="fw-bold text-dark fs-6">
                        {{ date('F Y', strtotime(($latestPeriode ? $latestPeriode : date('Y-m')) . ' +1 month')) }}
                    </span>
                </div>
                <div class="p-3 bg-light bg-opacity-50 rounded-3 mb-2 border">
                    <small class="text-secondary d-block">Total Aset Tersusut:</small>
                    <span class="fw-bold text-dark fs-6">{{ $projectedAssetsCount }} Unit</span>
                </div>
                <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                    <small class="text-danger d-block fw-bold">Beban Penyusutan:</small>
                    <span class="fw-extrabold text-danger fs-5">Rp {{ number_format($projectedNextMonth, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DEPRECIATION LOG HISTORY -->
    <div class="modal fade" id="modalHistoryAsset" role="dialog" aria-labelledby="modalHistoryAssetLabel">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Histori Penyusutan Aset</h5>
                            <small class="text-muted" id="history_asset_code_label">-</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <h6 class="fw-bold text-dark mb-3">Nama Aset: <span class="text-primary" id="history_asset_name_label">-</span></h6>
                    <div class="table-responsive">
                        <table class="table align-middle table-modern">
                            <thead>
                                <tr>
                                    <th width="20%">Periode</th>
                                    <th width="25%" class="text-end">Nilai Penyusutan</th>
                                    <th width="25%" class="text-end">Nilai Buku Awal</th>
                                    <th width="25%" class="text-end">Nilai Buku Akhir</th>
                                    <th width="30%">Diproses Oleh</th>
                                    <th width="25%">Tanggal Proses</th>
                                </tr>
                            </thead>
                            <tbody id="history_asset_tbody">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL MONTHLY DEPRECIATION DETAIL -->
    <div class="modal fade" id="modalMonthlyDetail" role="dialog" aria-labelledby="modalMonthlyDetailLabel">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                            <i class="bi bi-calendar-check fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Rincian Penyusutan Bulanan</h5>
                            <small class="text-muted" id="monthly_detail_period_label">-</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Daftar Unit Aset Tersusutkan</h6>
                        <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold" id="monthly_detail_total_label">Total: Rp 0</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-modern">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Kode Aset</th>
                                    <th width="30%">Nama Aset</th>
                                    <th width="22%" class="text-end">Beban Penyusutan</th>
                                    <th width="23%" class="text-end">Nilai Buku Akhir</th>
                                </tr>
                            </thead>
                            <tbody id="monthly_detail_tbody">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL EDIT DETAIL ASET TETAP ===== --}}
    <x-modal_form id="modalEditAsset" formId="formEditAsset" title="<i class='bi bi-pencil-square me-2'></i> Edit Detail Unit Aset Tetap">
        <x-slot name="slot">
            <input type="hidden" id="edit_asset_id">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Kode Aset (Read-Only)</label>
                    <input type="text" id="edit_asset_kode_aset" class="form-control form-control-custom bg-light" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nama Barang / Model (Read-Only)</label>
                    <input type="text" id="edit_asset_nama_barang" class="form-control form-control-custom bg-light" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nomor Seri Aset</label>
                    <input type="text" name="nomor_seri" id="edit_asset_nomor_seri" class="form-control form-control-custom" placeholder="Contoh: SN-XYZ1234">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Kondisi Aset <span class="text-danger">*</span></label>
                    <select name="kondisi" id="edit_asset_kondisi" class="form-select form-control-custom" required>
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Lokasi Gudang Penempatan <span class="text-danger">*</span></label>
                    <select name="gudang_id" id="edit_asset_gudang_id" class="form-select form-control-custom" required>
                        <option value="">-- Pilih Lokasi Gudang --</option>
                        @foreach ($gudangs as $gudang)
                            <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }} ({{ $gudang->kode_gudang }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Departemen Pengguna</label>
                    <select name="departemen_id" id="edit_asset_departemen_id" class="form-select form-control-custom">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Vendor / Supplier Pembelian</label>
                    <select name="supplier_id" id="edit_asset_supplier_id" class="form-select form-control-custom">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Penanggung Jawab / PIC</label>
                    <select name="penanggung_jawab_id" id="edit_asset_penanggung_jawab_id" class="form-select form-control-custom">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Status Aset <span class="text-danger">*</span></label>
                    <select name="status" id="edit_asset_status" class="form-select form-control-custom" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Habis Susut">Habis Susut</option>
                        <option value="Dijual">Dijual</option>
                        <option value="Rusak/Dibuang">Rusak/Dibuang</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Catatan / Detail Tambahan Unit</label>
                    <textarea name="catatan" id="edit_asset_catatan" rows="3" class="form-control form-control-custom" placeholder="Catat kelengkapan, aksesoris, atau riwayat perbaikan unit..."></textarea>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $(document).ready(function () {
            // Initialize main asset DataTable
            $('#tableAssets').DataTable({
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    search: 'Cari:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center py-3">Belum ada aset terdaftar di cabang ini.</div>'
                }
            });

            // Initialize monthly recap DataTable
            $('#tableRecaps').DataTable({
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    search: 'Cari:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center py-3">Belum ada riwayat penyusutan bulanan.</div>'
                }
            });

            // ===== RUN DEPRECIATION =====
            $('#formRunDepreciation').on('submit', function (e) {
                e.preventDefault();
                let period = $('#dep_period').val();

                Swal.fire({
                    title: 'Jalankan Penyusutan?',
                    html: `Anda akan memproses penyusutan aset untuk periode: <b>${period}</b>.<br><small class="text-danger">Aksi ini akan mendepresiasi semua aset tetap aktif di cabang ini.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let submitBtn = $('#btn_run_depreciation');
                        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

                        $.ajax({
                            url: "{{ route('assets.depreciate') }}",
                            type: 'POST',
                            data: { periode: period },
                            success: function (res) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'Mantap'
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                submitBtn.prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Proses Penyusutan');
                                let msg = 'Gagal memproses penyusutan.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Gagal!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // ===== ROLLBACK DEPRECIATION =====
            $('#btn_rollback_depreciation').on('click', function () {
                let period = $(this).data('periode');

                Swal.fire({
                    title: 'Batalkan Penyusutan?',
                    html: `Apakah Anda yakin ingin membatalkan (rollback) penyusutan periode <b>${period}</b>?<br><small class="text-danger">Semua log periode ini akan dihapus dan nilai buku aset akan dikembalikan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Rollback!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let rollbackBtn = $(this);
                        rollbackBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Membatalkan...');

                        $.ajax({
                            url: "{{ route('assets.rollback') }}",
                            type: 'POST',
                            data: { periode: period },
                            success: function (res) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'Oke'
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                rollbackBtn.prop('disabled', false).html(`<i class="bi bi-arrow-counterclockwise me-1"></i> Rollback Periode ${period}`);
                                let msg = 'Gagal membatalkan penyusutan.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Gagal!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // ===== VIEW HISTORY ASSET =====
            $(document).on('click', '.btn-view-history', function () {
                let id = $(this).data('id');

                $.get('/fixed-assets/' + id + '/history', function (res) {
                    $('#history_asset_code_label').text('KODE ASET: ' + res.asset_code);
                    $('#history_asset_name_label').text(res.asset_name);

                    let html = '';
                    if (res.history.length === 0) {
                        html = '<tr><td colspan="6" class="text-center text-muted py-3">Aset ini belum memiliki riwayat penyusutan.</td></tr>';
                    } else {
                        res.history.forEach(function (dep) {
                            html += `
                                <tr>
                                    <td class="fw-bold text-dark">${dep.periode}</td>
                                    <td class="text-end fw-semibold text-danger">Rp ${dep.nilai_penyusutan.toLocaleString('id-ID')}</td>
                                    <td class="text-end text-secondary">Rp ${dep.nilai_sebelum.toLocaleString('id-ID')}</td>
                                    <td class="text-end fw-bold text-success">Rp ${dep.nilai_setelah.toLocaleString('id-ID')}</td>
                                    <td>${dep.proses_oleh}</td>
                                    <td class="small text-muted">${dep.tanggal_proses}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#history_asset_tbody').html(html);
                    $('#modalHistoryAsset').modal('show');
                }).fail(function () {
                    Swal.fire('Gagal!', 'Gagal mengambil histori penyusutan aset.', 'error');
                });
            });

            // ===== VIEW MONTHLY DETAIL =====
            $(document).on('click', '.btn-view-monthly-detail', function () {
                let period = $(this).data('periode');

                $.get('/fixed-assets/monthly-detail/' + period, function (res) {
                    $('#monthly_detail_period_label').text('PERIODE: ' + res.periode);
                    $('#monthly_detail_total_label').text('Total: Rp ' + res.total_penyusutan.toLocaleString('id-ID'));

                    let html = '';
                    if (res.details.length === 0) {
                        html = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data rincian penyusutan untuk bulan ini.</td></tr>';
                    } else {
                        res.details.forEach(function (det, idx) {
                            html += `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                            ${det.asset_code}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">${det.asset_name}</td>
                                    <td class="text-end fw-semibold text-danger">Rp ${det.nilai_penyusutan.toLocaleString('id-ID')}</td>
                                    <td class="text-end fw-bold text-success">Rp ${det.nilai_setelah.toLocaleString('id-ID')}</td>
                                </tr>
                             `;
                        });
                    }

                    $('#monthly_detail_tbody').html(html);
                    $('#modalMonthlyDetail').modal('show');
                }).fail(function () {
                    Swal.fire('Gagal!', 'Gagal mengambil rincian penyusutan bulanan.', 'error');
                });
            });

            // ===== SHOW EDIT ASSET MODAL =====
            $(document).on('click', '.btn-edit-asset', function () {
                let id = $(this).data('id');
                
                $.get('/fixed-assets/' + id + '/edit', function (res) {
                    let a = res.asset;
                    
                    $('#edit_asset_id').val(a.id);
                    $('#edit_asset_kode_aset').val(a.kode_aset);
                    $('#edit_asset_nama_barang').val(a.barang ? a.barang.nama_barang : '-');
                    $('#edit_asset_nomor_seri').val(a.nomor_seri ? a.nomor_seri : '');
                    $('#edit_asset_kondisi').val(a.kondisi);
                    $('#edit_asset_gudang_id').val(a.gudang_id ? a.gudang_id : '');
                    $('#edit_asset_departemen_id').val(a.departemen_id ? a.departemen_id : '');
                    $('#edit_asset_supplier_id').val(a.supplier_id ? a.supplier_id : '');
                    $('#edit_asset_penanggung_jawab_id').val(a.penanggung_jawab_id ? a.penanggung_jawab_id : '');
                    $('#edit_asset_status').val(a.status);
                    $('#edit_asset_catatan').val(a.catatan);

                    $('#modalEditAsset').modal('show');
                }).fail(function() {
                    Swal.fire('Gagal!', 'Gagal mengambil data aset.', 'error');
                });
            });

            // ===== SUBMIT EDIT ASSET =====
            $('#formEditAsset').on('submit', function (e) {
                e.preventDefault();
                let id = $('#edit_asset_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: '/fixed-assets/' + id + '/update',
                    type: 'POST',
                    data: formData,
                    success: function (res) {
                        $('#modalEditAsset').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Gagal mengupdate detail aset.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
