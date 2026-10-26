@php
    $activeBranchId  = session('active_branch_id');
    $activeCompanyId = session('active_company_id');

    $activeBranch = null;
    if ($activeBranchId) {
        $activeBranch = \App\Models\Branch::with('company')->find($activeBranchId);
    }

    $activeCompany = null;
    if ($activeBranch && $activeBranch->company) {
        $activeCompany = $activeBranch->company;
    } elseif ($activeCompanyId) {
        $activeCompany = \App\Models\Company::find($activeCompanyId);
    }

    $isBranchActive = !empty($activeBranch);
    $entityTitle    = $isBranchActive ? ($activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang')) : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

    // Logo Perusahaan / Cabang (untuk Kop Surat Dokumen)
    $companyLogoUrl = null;
    $rawLogo = $isBranchActive ? ($activeBranch->logo ?? null) : ($activeCompany?->logo ?? null);
    if ($rawLogo) {
        $cleanLogo = ltrim($rawLogo, '/');
        if (file_exists(public_path('storage/' . $cleanLogo))) {
            $companyLogoUrl = asset('storage/' . $cleanLogo);
        } elseif (file_exists(public_path($cleanLogo))) {
            $companyLogoUrl = asset($cleanLogo);
        } elseif (filter_var($rawLogo, FILTER_VALIDATE_URL)) {
            $companyLogoUrl = $rawLogo;
        }
    }

    $appLogoUrl = $companyLogoUrl;

    // Logo Aplikasi Resmi (untuk Favicon di Tab Browser)
    $appFaviconUrl = $appLogo ?? asset('gambar/logo.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title></title>
    <link rel="shortcut icon" href="{{ $appFaviconUrl }}" type="image/x-icon">
    <link rel="icon" href="{{ $appFaviconUrl }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --blue-900: #0f274a;
            --blue-800: #1e3a8a;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-100: #dbeafe;
            --blue-50: #eff6ff;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: var(--slate-900);
            background: #0b1324;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'JetBrains Mono', Consolas, Monaco, monospace;
        }

        /* ── Outer Viewport Wrapper for Mobile & Desktop Pan/Zoom ── */
        .sheet-viewport-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 24px 12px 80px 12px;
            display: flex;
            justify-content: center;
        }

        /* ── Standard A4 Landscape Paper Sheet ── */
        .paper-sheet {
            background: #ffffff;
            width: 297mm;
            min-width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            padding: 12mm 15mm;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            position: relative;
            flex-shrink: 0;
        }

        /* ── Top Action Toolbar ── */
        .top-action-bar {
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid var(--blue-600);
            padding: 10px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        /* ── Header / Kop Surat ── */
        .header-container {
            border-bottom: 3px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--blue-800);
            letter-spacing: 0.5px;
            line-height: 1.15;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 9.5px;
            color: var(--slate-500);
            margin-top: 3px;
            line-height: 1.35;
        }
        .doc-title-main {
            font-size: 17px;
            font-weight: 800;
            color: var(--blue-700);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            line-height: 1.15;
        }
        .doc-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--blue-50) 0%, #ffffff 100%);
            color: var(--blue-800);
            border: 1px solid var(--blue-100);
            padding: 3px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 10px;
            margin-top: 4px;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
        }

        /* ── Info & Summary Cards ── */
        .meta-card {
            background: #ffffff;
            border: 1px solid var(--blue-100);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(15, 39, 74, 0.03);
            margin-bottom: 14px;
        }
        .meta-card-header {
            background: linear-gradient(90deg, #f0f7ff 0%, #ffffff 100%);
            border-bottom: 1px solid var(--blue-100);
            padding: 7px 14px;
            font-weight: 700;
            font-size: 9.5px;
            color: var(--blue-800);
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 6px 14px;
            font-size: 9.5px;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-table tr:last-child td {
            border-bottom: none;
        }
        .meta-label {
            color: var(--slate-500);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }
        .meta-val {
            font-weight: 700;
            color: var(--slate-900);
        }

        /* ── Modern Table Styling with Royal Blue Theme ── */
        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 14px;
        }
        .table-print th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 55%, #2563eb 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 9px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 5px;
            border: 1px solid #1e3a8a !important;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-print td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            color: var(--slate-900);
            font-size: 9px;
        }
        .table-print tr:nth-child(even) td {
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-print tr:hover td {
            background-color: #f0f7ff;
        }
        .table-print tr.table-summary-row td {
            background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 100%) !important;
            color: var(--blue-900) !important;
            border-top: 2px solid var(--blue-600) !important;
            border-bottom: 2px solid var(--blue-600) !important;
            font-weight: 800;
            font-size: 9.5px;
            padding: 6px 8px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Badges ── */
        .badge-print {
            font-size: 8px;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
            letter-spacing: 0.3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .badge-masuk {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #86efac !important;
        }
        .badge-keluar {
            background-color: #ffe4e6 !important;
            color: #be123c !important;
            border: 1px solid #fecdd3 !important;
        }
        .badge-ref-tag {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
        }

        /* ── Floating Bottom Dock for Mobile ── */
        .mobile-bottom-dock {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            border-top: 2px solid var(--blue-600);
            padding: 10px 16px;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.4);
        }

        @media (max-width: 991.98px) {
            .mobile-bottom-dock {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }
        }

        /* ── Print Page Settings (Suppress Browser URL & Header/Footer) ── */
        @page {
            size: A4 landscape;
            margin: 0mm !important;
        }

        /* ── Print Specific Rules ── */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0mm !important;
            }
            *, *::before, *::after {
                box-sizing: border-box;
            }
            html, body {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print,
            .top-action-bar,
            .mobile-bottom-dock,
            nav, .sidebar, header {
                display: none !important;
            }
            .sheet-viewport-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                display: block !important;
            }
            .paper-sheet {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 8mm 10mm !important;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- ── TOP ACTION BAR (TOOLBAR FOR PC & MOBILE) ── -->
    <div class="top-action-bar no-print d-flex flex-wrap align-items-center justify-content-between gap-2 text-white">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold text-white d-inline-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                <span class="d-none d-sm-inline">Pratinjau Cetak Laporan Kartu Stok</span>
                <span class="d-sm-none">Cetak Kartu Stok</span>
            </span>
            <span class="badge bg-primary bg-opacity-25 border border-primary border-opacity-50 text-info rounded-pill px-2.5 py-1 small">
                A4 Landscape
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Zoom controls for comfortable inspection -->
            <div class="d-none d-md-flex align-items-center gap-1 bg-dark bg-opacity-50 border border-secondary border-opacity-50 rounded-pill p-1">
                <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2 py-0" onclick="adjustZoom(-0.1)" title="Zoom Out">
                    <i class="bi bi-zoom-out"></i>
                </button>
                <span id="zoomLabel" class="small fw-semibold px-1 font-mono" style="font-size:0.75rem;">100%</span>
                <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2 py-0" onclick="adjustZoom(0.1)" title="Zoom In">
                    <i class="bi bi-zoom-in"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-1.5 py-0" onclick="resetZoom()" title="Reset Zoom">
                    <i class="bi bi-arrows-angle-contract"></i>
                </button>
            </div>

            <button type="button" onclick="triggerPrint()" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-printer-fill"></i>
                <span>Cetak / PDF</span>
            </button>

            <button type="button" onclick="window.close()" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 fw-semibold">
                <i class="bi bi-x-lg me-1"></i> Tutup
            </button>
        </div>
    </div>

    <!-- ── SHEET VIEWPORT WRAPPER ── -->
    <div class="sheet-viewport-wrapper">
        <div class="paper-sheet" id="printSheet">

            <!-- ── KOP SURAT / HEADER ── -->
            <div class="header-container d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    @if($appLogoUrl)
                        <img src="{{ $appLogoUrl }}" alt="Logo" style="height: 48px; max-width: 140px; object-fit: contain;">
                    @endif
                    <div>
                        <div class="company-name">{{ $entityTitle }}</div>
                        @if(!$isBranchActive && $activeCompany && $activeCompany->alamat)
                            <div class="company-sub">{{ $activeCompany->alamat }}</div>
                        @elseif($isBranchActive && $activeBranch && $activeBranch->alamat)
                            <div class="company-sub">{{ $activeBranch->alamat }}</div>
                        @endif
                    </div>
                </div>

                <div class="text-end">
                    <div class="doc-title-main">LAPORAN KARTU STOK</div>
                    <div class="doc-period-badge">
                        <i class="bi bi-calendar-range text-primary"></i> Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
                    </div>
                </div>
            </div>

            <!-- ── FILTER PARAMETER CARD (CLEAN FULL-WIDTH) ── -->
            <div class="meta-card">
                <div class="meta-card-header">
                    <span><i class="bi bi-funnel-fill text-primary me-1"></i> Parameter Filter & Informasi Laporan</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 8.5px;">{{ $mutasi->count() }} Baris Transaksi</span>
                </div>
                <table class="meta-table">
                    <tr>
                        <td style="width: 12%;" class="meta-label">Barang:</td>
                        <td style="width: 38%;" class="meta-val text-primary">{{ $barang ? '[' . $barang->kode_barang . '] ' . $barang->nama_barang : 'Semua Barang' }}</td>
                        <td style="width: 12%;" class="meta-label">Gudang:</td>
                        <td style="width: 38%;" class="meta-val">{{ $gudang ? $gudang->nama_gudang : 'Semua Gudang' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tipe Mutasi:</td>
                        <td class="meta-val text-uppercase">{{ $tipeMutasi ? str_replace('_', ' ', $tipeMutasi) : 'Semua Mutasi' }}</td>
                        <td class="meta-label">Waktu Cetak:</td>
                        <td class="meta-val font-mono" style="font-size: 9px;">{{ date('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>

            <!-- ── MAIN MUTASI TABLE (ROYAL BLUE HEADER THEME) ── -->
            <table class="table-print">
                <thead>
                    <tr>
                        <th style="width: 25px;">NO</th>
                        <th style="width: 95px;">TANGGAL & WAKTU</th>
                        <th>NAMA BARANG</th>
                        <th style="width: 110px;">GUDANG</th>
                        <th style="width: 55px;">TIPE</th>
                        <th style="width: 65px;">STOK AWAL</th>
                        <th style="width: 85px;">HARGA SATUAN</th>
                        <th style="width: 60px;">JUMLAH</th>
                        <th style="width: 90px;">TOTAL NILAI</th>
                        <th style="width: 65px;">STOK AKHIR</th>
                        <th style="width: 125px;">REFERENSI</th>
                        <th style="width: 130px;">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasi as $idx => $m)
                        @php
                            $rStokAwal = round($m->stok_awal, 2);
                            $rowStokAwal = (floor($rStokAwal) == $rStokAwal) ? number_format($rStokAwal, 0, ',', '.') : rtrim(rtrim(number_format($rStokAwal, 2, ',', '.'), '0'), ',');
                            $rJumlah = round($m->jumlah, 2);
                            $rowJumlah = (floor($rJumlah) == $rJumlah) ? number_format($rJumlah, 0, ',', '.') : rtrim(rtrim(number_format($rJumlah, 2, ',', '.'), '0'), ',');
                            $rStokAkhir = round($m->stok_akhir, 2);
                            $rowStokAkhir = (floor($rStokAkhir) == $rStokAkhir) ? number_format($rStokAkhir, 0, ',', '.') : rtrim(rtrim(number_format($rStokAkhir, 2, ',', '.'), '0'), ',');
                        @endphp
                        <tr>
                            <td class="text-center font-mono text-muted">{{ $idx + 1 }}</td>
                            <td class="text-nowrap font-mono text-center">
                                <span class="fw-bold d-block">{{ $m->created_at ? $m->created_at->format('d/m/Y') : '-' }}</span>
                                <small class="text-muted">{{ $m->created_at ? $m->created_at->format('H:i:s') : '' }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $m->barang?->nama_barang ?? '-' }}</strong>
                                <small class="text-muted font-mono" style="font-size: 8px;">{{ $m->barang?->kode_barang ?? '' }}</small>
                            </td>
                            <td>
                                <span class="d-block fw-semibold">{{ $m->gudang?->nama_gudang ?? '-' }}</span>
                                @if($m->gudang && $m->gudang->cabang)
                                    <small class="text-muted d-block" style="font-size: 8px;">{{ $m->gudang->cabang->nama_cabang }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($m->tipe === 'Masuk')
                                    <span class="badge-print badge-masuk">MASUK</span>
                                @else
                                    <span class="badge-print badge-keluar">KELUAR</span>
                                @endif
                            </td>
                            <td class="text-center font-mono text-muted">{{ $rowStokAwal }}</td>
                            <td class="text-end font-mono text-nowrap">
                                {{ $m->harga_satuan > 0 ? 'Rp ' . number_format($m->harga_satuan, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-center fw-bold font-mono text-nowrap {{ $m->tipe === 'Masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $m->tipe === 'Masuk' ? '+' : '-' }}{{ $rowJumlah }}
                            </td>
                            <td class="text-end fw-semibold font-mono text-nowrap">
                                {{ $m->nilai_total > 0 ? 'Rp ' . number_format($m->nilai_total, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-center fw-bold font-mono text-dark">{{ $rowStokAkhir }}</td>
                            <td>
                                <span class="badge-ref-tag mb-0.5">{{ $m->referensi_jenis ?? 'Mutasi' }}</span><br>
                                <strong class="text-primary font-mono" style="font-size: 8.5px;">{{ $m->referensi_nomor ?? '-' }}</strong>
                            </td>
                            <td style="font-size: 8.5px; line-height: 1.25;">{{ $m->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted fst-italic">
                                Tidak ada catatan mutasi stok pada periode ini.
                            </td>
                        </tr>
                    @endforelse

                    @if($mutasi->isNotEmpty())
                        @php
                            $rTotMasuk = round($totalMasuk, 2);
                            $dispTotalMasuk = (floor($rTotMasuk) == $rTotMasuk) ? number_format($rTotMasuk, 0, ',', '.') : rtrim(rtrim(number_format($rTotMasuk, 2, ',', '.'), '0'), ',');
                            $rTotKeluar = round($totalKeluar, 2);
                            $dispTotalKeluar = (floor($rTotKeluar) == $rTotKeluar) ? number_format($rTotKeluar, 0, ',', '.') : rtrim(rtrim(number_format($rTotKeluar, 2, ',', '.'), '0'), ',');
                        @endphp
                        <tr class="table-summary-row">
                            <td colspan="7" class="text-end fw-bold">TOTAL MUTASI PERIODE:</td>
                            <td class="text-center font-mono fw-bold">
                                <span class="text-success">+{{ $dispTotalMasuk }}</span> / 
                                <span class="text-danger">-{{ $dispTotalKeluar }}</span>
                            </td>
                            <td></td>
                            <td colspan="3"></td>
                        </tr>
                    @endif
                </tbody>
            </table>

        </div>
    </div>

    <!-- ── FLOATING BOTTOM DOCK FOR MOBILE ── -->
    <div class="mobile-bottom-dock">
        <button type="button" onclick="window.close()" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-semibold flex-fill">
            <i class="bi bi-x-lg me-1"></i> Tutup
        </button>
        <button type="button" onclick="triggerPrint()" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5 shadow">
            <i class="bi bi-printer-fill"></i> Cetak Laporan
        </button>
    </div>

    <script>
        let currentZoom = 1.0;
        const sheet = document.getElementById('printSheet');
        const zoomLabel = document.getElementById('zoomLabel');

        function triggerPrint() {
            document.title = '';
            window.print();
        }

        window.addEventListener('beforeprint', () => {
            document.title = '';
        });

        window.addEventListener('afterprint', () => {
            document.title = '';
        });

        function adjustZoom(delta) {
            currentZoom = Math.min(Math.max(0.5, currentZoom + delta), 1.8);
            applyZoom();
        }

        function resetZoom() {
            currentZoom = 1.0;
            applyZoom();
        }

        function applyZoom() {
            if (sheet) {
                sheet.style.transform = `scale(${currentZoom})`;
                sheet.style.transformOrigin = 'top center';
                if (zoomLabel) {
                    zoomLabel.textContent = `${Math.round(currentZoom * 100)}%`;
                }
            }
        }
    </script>
</body>
</html>
