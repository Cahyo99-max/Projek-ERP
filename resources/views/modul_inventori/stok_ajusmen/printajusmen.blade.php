<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Dokumen Penyesuaian Stok - {{ $stockAdjustment->nomor_adjustment }}</title>
    <link rel="icon" href="{{ $appLogoUrl ?? asset('gambar/logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ $appLogoUrl ?? asset('gambar/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-blue: #1e40af;
            --primary-blue-dark: #1e3a8a;
            --primary-blue-light: #eff6ff;
            --primary-blue-border: #bfdbfe;
            --accent-blue: #2563eb;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: var(--text-dark);
            background: #090d16;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Outer Viewport Wrapper for Mobile & Desktop Pan/Zoom ── */
        .sheet-viewport-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 20px 10px 80px 10px;
            display: flex;
            justify-content: center;
        }

        /* ── Standard A4 Paper Sheet ── */
        .paper-sheet {
            background: #ffffff;
            width: 210mm;
            min-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 16mm 18mm;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45);
            border-radius: 6px;
            position: relative;
            flex-shrink: 0;
        }

        /* ── Top Action Toolbar (Responsive for PC & HP) ── */
        .top-action-bar {
            background: #0f172a;
            border-bottom: 2px solid #2563eb;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .toggle-chip {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .toggle-chip:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* ── Header / Kop Surat ── */
        .header-container {
            border-bottom: 2.5px solid var(--primary-blue);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--primary-blue-dark);
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 3px;
        }
        .doc-title-main {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .doc-number-badge {
            display: inline-block;
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue-border);
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            margin-top: 4px;
        }

        /* ── Info Card Grid ── */
        .info-grid {
            background: #ffffff;
            border: 1px solid var(--primary-blue-border);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .info-grid-header {
            background: linear-gradient(90deg, var(--primary-blue-light) 0%, #ffffff 100%);
            border-bottom: 1px solid var(--primary-blue-border);
            padding: 6px 12px;
            font-weight: 700;
            font-size: 10.5px;
            color: var(--primary-blue-dark);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 12px;
            font-size: 10.5px;
            vertical-align: middle;
            border: none;
        }
        .info-label {
            color: var(--text-muted);
            font-size: 10px;
            width: 120px;
        }
        .info-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* ── Badges ── */
        .badge-doc {
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }
        .badge-draft {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-confirmed {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* ── Table Styling ── */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 16px;
        }
        .table-custom thead th {
            background-color: var(--primary-blue) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 8px;
            border: 1px solid var(--primary-blue-dark);
            text-align: center;
            vertical-align: middle;
        }
        .table-custom tbody td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: var(--text-dark);
        }
        .table-custom tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .table-custom tbody tr:hover {
            background-color: var(--primary-blue-light);
        }
        .table-custom tfoot td {
            background-color: var(--primary-blue-light) !important;
            border-top: 2px solid var(--accent-blue);
            border-bottom: 2px solid var(--accent-blue);
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            padding: 7px 8px;
            font-size: 10.5px;
            font-weight: 700;
        }

        /* ── Notes Box ── */
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--accent-blue);
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 24px;
        }
        .notes-title {
            font-size: 10px;
            font-weight: 700;
            color: var(--primary-blue-dark);
            margin-bottom: 2px;
        }
        .notes-content {
            font-size: 10.5px;
            color: #334155;
            margin: 0;
        }

        /* ── Signatures ── */
        .signature-section {
            page-break-inside: avoid;
            margin-top: 20px;
        }
        .sig-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid var(--primary-blue);
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            height: 100%;
        }
        .sig-role {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--primary-blue-dark);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }
        .sig-space {
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
        }
        .sig-name {
            font-weight: 700;
            font-size: 10.5px;
            color: var(--text-dark);
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            display: inline-block;
            min-width: 140px;
        }
        .sig-title {
            font-size: 9.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Editable inline elements ── */
        .editable-field {
            transition: all 0.2s ease;
            border-radius: 3px;
            padding: 1px 4px;
        }
        .editable-field:hover {
            background-color: #fef9c3;
            outline: 1px dashed #ca8a04;
            cursor: text;
        }
        .editable-field:focus {
            background-color: #ffffff;
            outline: 2px solid var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        /* ── Floating Bottom Toolbar on Mobile ── */
        .mobile-bottom-dock {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .desktop-action-btns {
                display: none !important;
            }
            .mobile-bottom-dock {
                display: flex !important;
                position: fixed;
                bottom: 12px;
                left: 12px;
                right: 12px;
                z-index: 1050;
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(59, 130, 246, 0.35);
                border-radius: 50px;
                padding: 8px 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.6);
                gap: 8px;
            }
            .mobile-bottom-dock .btn {
                flex: 1;
                font-size: 11px;
                padding: 8px 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
            }
            .sheet-viewport-wrapper {
                padding: 10px 4px 90px 4px;
                justify-content: flex-start;
            }
            .top-action-bar {
                padding: 10px 12px;
            }
        }

        /* ── Print Styles ── */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            .no-print { display: none !important; }
            body {
                background: #ffffff !important;
            }
            .sheet-viewport-wrapper {
                padding: 0 !important;
                display: block !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-width: 100% !important;
                margin: 0 !important;
                padding: 12mm 14mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .editable-field {
                background: none !important;
                outline: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            html, body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .table-custom thead th {
                background-color: #1e40af !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
            }
            .table-custom tfoot td {
                background-color: #eff6ff !important;
                -webkit-print-color-adjust: exact;
            }
            .info-grid-header {
                background-color: #eff6ff !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- NO PRINT ACTION BAR (TOP CONTROLS) -->
    <div class="no-print top-action-bar text-white">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            
            <!-- LEFT: TITLE & TOGGLES -->
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-25 p-1.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-file-earmark-text-fill text-info fs-5"></i>
                    </div>
                    <div>
                        <strong class="fs-6 text-white d-block lh-1">Pratinjau Cetak Penyesuaian Stok</strong>
                        <span class="text-info font-monospace" style="font-size: 10px;">{{ $stockAdjustment->nomor_adjustment }}</span>
                    </div>
                </div>

                <!-- SWITCH TOGGLES CHIPS -->
                <div class="d-flex flex-wrap align-items-center gap-2 border-start border-secondary ps-3 ms-md-1">
                    <label class="toggle-chip form-check-label text-white small" for="chkShowFoto">
                        <input class="form-check-input mt-0 me-1" type="checkbox" id="chkShowFoto" checked onchange="togglePdfSection('col-foto', this.checked)">
                        <i class="bi bi-image text-info"></i> Foto Barang
                    </label>
                    <label class="toggle-chip form-check-label text-white small" for="chkShowSignatureImg">
                        <input class="form-check-input mt-0 me-1" type="checkbox" id="chkShowSignatureImg" checked onchange="togglePdfSection('box-ttd-img', this.checked)">
                        <i class="bi bi-vector-pen text-info"></i> Gambar TTD
                    </label>
                    @if($canSeeHpp ?? true)
                    <label class="toggle-chip form-check-label text-white small" for="chkShowHpp">
                        <input class="form-check-input mt-0 me-1" type="checkbox" id="chkShowHpp" checked onchange="togglePdfSection('col-hpp', this.checked)">
                        <i class="bi bi-cash-stack text-info"></i> HPP & Nilai
                    </label>
                    @endif
                </div>
            </div>

            <!-- RIGHT: DESKTOP ACTION BUTTONS -->
            <div class="desktop-action-btns d-flex align-items-center gap-2">
                <button onclick="downloadPdfScreenshot()" class="btn btn-outline-info btn-sm px-3 rounded-pill fw-semibold text-white btn-download-ss shadow-xs">
                    <i class="bi bi-camera me-1"></i> Simpan Gambar / SS
                </button>
                <button onclick="window.print()" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm" style="background-color: #2563eb; border-color: #2563eb;">
                    <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
                </button>
                <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-white border-secondary">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MOBILE FLOATING BOTTOM ACTION DOCK -->
    <div class="no-print mobile-bottom-dock">
        <button type="button" class="btn btn-primary rounded-pill fw-bold" onclick="window.print();" style="background-color: #2563eb; border-color: #2563eb;">
            <i class="bi bi-printer-fill"></i> Cetak PDF
        </button>
        <button type="button" class="btn btn-outline-info text-white rounded-pill fw-semibold btn-download-ss" onclick="downloadPdfScreenshot();">
            <i class="bi bi-camera-fill"></i> Screenshot
        </button>
        <button type="button" class="btn btn-outline-light rounded-pill" onclick="window.close();">
            <i class="bi bi-x-lg"></i> Tutup
        </button>
    </div>

    <!-- MAIN PAPER A4 SHEET VIEWPORT WRAPPER -->
    <div class="sheet-viewport-wrapper">
        <div class="paper-sheet" id="printContent">
            
            <!-- HEADER KOP SURAT PERUSAHAAN (BLUE ACCENT) -->
            <div class="header-container d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="Logo" style="max-height: 48px; max-width: 120px; object-fit: contain;">
                    @endif
                    <div>
                        <div class="company-name">{{ $entityTitle }}</div>
                        @if(!empty($entitySubtitle))
                            <div class="company-sub">
                                <i class="bi bi-building me-1 text-primary"></i>Perusahaan: <strong>{{ $entitySubtitle }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="doc-title-main">DOKUMEN PENYESUAIAN STOK</div>
                    <div class="doc-number-badge">
                        <i class="bi bi-qr-code me-1"></i>{{ $stockAdjustment->nomor_adjustment }}
                    </div>
                </div>
            </div>

            <!-- INFORMASI DOKUMEN (ELEGANT BLUE GRID) -->
            <div class="info-grid">
                <div class="info-grid-header">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>INFORMASI TRANSAKSI PENYESUAIAN STOK</span>
                </div>
                <div class="row g-0">
                    <div class="col-7 border-end border-light-subtle">
                        <table class="info-table">
                            <tr>
                                <td class="info-label"><i class="bi bi-calendar3 me-1 text-primary"></i>Tanggal</td>
                                <td style="width: 10px;">:</td>
                                <td class="info-value">{{ $stockAdjustment->tanggal_adjustment ? $stockAdjustment->tanggal_adjustment->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="bi bi-buildings me-1 text-primary"></i>Gudang</td>
                                <td>:</td>
                                <td class="info-value">{{ $stockAdjustment->gudang ? $stockAdjustment->gudang->nama_gudang : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Tipe Penyesuaian</td>
                                <td>:</td>
                                <td class="info-value">
                                    @if($stockAdjustment->tipe_adjustment === 'Pengurangan')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-0.5 rounded">
                                            <i class="bi bi-dash-circle me-1"></i>Pengurangan Stok
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-0.5 rounded">
                                            <i class="bi bi-plus-circle me-1"></i>Penambahan Stok
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="bi bi-tag-fill me-1 text-primary"></i>Kategori Alasan</td>
                                <td>:</td>
                                <td class="info-value"><span class="text-primary fw-bold">{{ $stockAdjustment->kategori_alasan }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-5">
                        <table class="info-table">
                            <tr>
                                <td class="info-label"><i class="bi bi-shield-check me-1 text-primary"></i>Status</td>
                                <td style="width: 10px;">:</td>
                                <td class="info-value">
                                    @php
                                        $stClass = match($stockAdjustment->status) {
                                            'Confirmed' => 'badge-confirmed',
                                            'Pending'   => 'badge-pending',
                                            default     => 'badge-draft'
                                        };
                                        $stIcon = match($stockAdjustment->status) {
                                            'Confirmed' => 'bi-check-circle-fill text-success',
                                            'Pending'   => 'bi-clock-fill text-warning',
                                            default     => 'bi-pencil-fill text-secondary'
                                        };
                                    @endphp
                                    <span class="badge-doc {{ $stClass }}">
                                        <i class="bi {{ $stIcon }} me-1"></i>{{ $stockAdjustment->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="bi bi-person-fill me-1 text-primary"></i>Dibuat Oleh</td>
                                <td>:</td>
                                <td class="info-value">{{ $stockAdjustment->creator ? $stockAdjustment->creator->nama_lengkap : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="bi bi-clock-history me-1 text-primary"></i>Waktu Cetak</td>
                                <td>:</td>
                                <td class="info-value text-muted" style="font-size: 9.5px;">{{ date('d/m/Y H:i') }} WIB</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TABEL DETAIL BARANG (NAVY / BLUE THEME) -->
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 32px;">NO</th>
                        <th class="col-foto" style="width: 44px;">FOTO</th>
                        <th style="width: 120px;">KODE BARANG</th>
                        <th>NAMA BARANG</th>
                        <th style="width: 70px;" class="text-center">SATUAN</th>
                        <th style="width: 85px;" class="text-center">QTY</th>
                        @if($canSeeHpp ?? true)
                        <th class="col-hpp text-end" style="width: 110px;">HPP SATUAN (RP)</th>
                        <th class="col-hpp text-end" style="width: 125px;">TOTAL NILAI (RP)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        if (!function_exists('formatQtyClean')) {
                            function formatQtyClean($val): string {
                                if ($val === null || $val === '') return '0';
                                $num = (float)$val;
                                $formatted = number_format($num, 4, ',', '.');
                                return str_contains($formatted, ',') ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                            }
                        }
                        $totalQty = 0;
                        $totalNilai = 0;
                    @endphp
                    @foreach($stockAdjustment->details as $idx => $item)
                        @php
                            $totalQty += $item->jumlah;
                            $totalNilai += $item->nilai_total;
                            $fotoPath = $item->barang && $item->barang->primaryFoto ? $item->barang->primaryFoto->foto_path : null;
                            $fotoUrl  = $fotoPath ? asset('storage/' . $fotoPath) : null;
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $idx + 1 }}</td>
                            <td class="text-center col-foto">
                                @if($fotoUrl)
                                    <img src="{{ $fotoUrl }}" style="width: 28px; height: 28px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;">
                                @else
                                    <div style="width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; color: #94a3b8;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-bold text-dark font-monospace" style="font-size: 10px;">
                                {{ $item->barang ? $item->barang->kode_barang : '-' }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->barang ? $item->barang->nama_barang : '-' }}</div>
                                @if(!empty($item->keterangan_detail))
                                    <div class="text-muted" style="font-size: 9px;"><i class="bi bi-info-circle me-1"></i>{{ $item->keterangan_detail }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded" style="font-size: 9.5px;">
                                    {{ $item->barang && $item->barang->satuan ? $item->barang->satuan->nama_satuan : 'Pcs' }}
                                </span>
                            </td>
                            <td class="text-center fw-bold text-dark" style="font-size: 10.5px;">
                                {{ formatQtyClean($item->jumlah) }}
                            </td>
                            @if($canSeeHpp ?? true)
                            <td class="text-end col-hpp text-muted" style="font-size: 10px;">
                                {{ number_format($item->harga_satuan, 2, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold col-hpp text-dark" style="font-size: 10.5px;">
                                {{ number_format($item->nilai_total, 2, ',', '.') }}
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td id="tdTotalLabel" colspan="5" class="text-center text-uppercase" style="color: var(--primary-blue-dark); letter-spacing: 0.5px;">
                            <i class="bi bi-calculator me-1"></i>TOTAL RINGKASAN:
                        </td>
                        <td class="text-center fw-bold" style="color: var(--primary-blue); font-size: 11px;">
                            {{ formatQtyClean($totalQty) }}
                        </td>
                        @if($canSeeHpp ?? true)
                        <td class="col-hpp"></td>
                        <td class="text-end fw-bold col-hpp" style="color: #15803d; font-size: 11px;">
                            Rp {{ number_format($totalNilai, 2, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                </tfoot>
            </table>

            <!-- CATATAN KETERANGAN -->
            <div class="notes-box">
                <div class="notes-title"><i class="bi bi-chat-left-text me-1"></i>Catatan Keterangan:</div>
                <div class="notes-content">{{ $stockAdjustment->keterangan ?: 'Tidak ada catatan khusus.' }}</div>
            </div>

            @php
                if (!function_exists('resolveSignatureUrl')) {
                    function resolveSignatureUrl(?string $path): ?string {
                        if (!$path) return null;
                        $cleanPath = ltrim($path, '/');
                        if (file_exists(public_path($cleanPath))) {
                            return asset($cleanPath);
                        }
                        if (file_exists(public_path('storage/' . $cleanPath))) {
                            return asset('storage/' . $cleanPath);
                        }
                        return asset($cleanPath);
                    }
                }

                // Creator / Pengaju
                $creatorName    = $stockAdjustment->creator ? ($stockAdjustment->creator->nama_lengkap ?: $stockAdjustment->creator->name) : 'Staff Gudang';
                $creatorPos     = $stockAdjustment->creator && $stockAdjustment->creator->position ? ($stockAdjustment->creator->position->position_name ?: $stockAdjustment->creator->position->nama_jabatan) : null;
                $creatorRole    = $stockAdjustment->creator && $stockAdjustment->creator->roles->first() ? $stockAdjustment->creator->roles->first()->name : null;
                $creatorJabatan = $creatorPos ?: ($creatorRole ?: 'Petugas Gudang');

                $creatorSig     = $stockAdjustment->creator && $stockAdjustment->creator->activeSignature ? $stockAdjustment->creator->activeSignature->signature_path : null;
                $creatorSigUrl  = resolveSignatureUrl($creatorSig);

                // Approver
                $approverName    = $stockAdjustment->approver ? ($stockAdjustment->approver->nama_lengkap ?: $stockAdjustment->approver->name) : ($stockAdjustment->isConfirmed() ? 'Manager Operasional' : '(Belum Disetujui)');
                $approverPos     = $stockAdjustment->approver && $stockAdjustment->approver->position ? ($stockAdjustment->approver->position->position_name ?: $stockAdjustment->approver->position->nama_jabatan) : null;
                $approverRole    = $stockAdjustment->approver && $stockAdjustment->approver->roles->first() ? $stockAdjustment->approver->roles->first()->name : null;
                $approverJabatan = $approverPos ?: ($approverRole ?: 'Manager Inventaris / Finance');

                $approverSig     = $stockAdjustment->approver && $stockAdjustment->approver->activeSignature ? $stockAdjustment->approver->activeSignature->signature_path : null;
                $approverSigUrl  = resolveSignatureUrl($approverSig);
            @endphp

            <!-- KOLOM TANDA TANGAN (2-BOX CORPORATE BLUE DESIGN: DIBUAT & DISETUJUI) -->
            <div class="row g-4 text-center signature-section justify-content-between">
                <div class="col-5">
                    <div class="sig-box">
                        <div class="sig-role">DIBUAT OLEH (PENGAJU)</div>
                        <div class="sig-space box-ttd-img">
                            @if($creatorSigUrl)
                                <img src="{{ $creatorSigUrl }}" style="max-height: 46px; max-width: 120px; object-fit: contain;">
                            @endif
                        </div>
                        <div class="sig-name editable-field" contenteditable="true" title="Klik untuk mengedit nama pengaju">
                            {{ $creatorName }}
                        </div>
                        <div class="sig-title editable-field" contenteditable="true" title="Klik untuk mengedit jabatan pengaju">
                            {{ $creatorJabatan }}
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="sig-box">
                        <div class="sig-role">DISETUJUI OLEH</div>
                        <div class="sig-space box-ttd-img">
                            @if($approverSigUrl)
                                <img src="{{ $approverSigUrl }}" style="max-height: 46px; max-width: 120px; object-fit: contain;">
                            @endif
                        </div>
                        <div class="sig-name editable-field" contenteditable="true" title="Klik untuk mengedit nama penyetujui">
                            {{ $approverName }}
                        </div>
                        <div class="sig-title editable-field" contenteditable="true" title="Klik untuk mengedit jabatan penyetujui">
                            {{ $approverJabatan }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function togglePdfSection(className, show) {
            if (className === 'box-ttd-img') {
                if (show) {
                    $('.box-ttd-img img').css('visibility', 'visible');
                } else {
                    $('.box-ttd-img img').css('visibility', 'hidden');
                }
            } else {
                if (show) {
                    $('.' + className).removeClass('d-none');
                } else {
                    $('.' + className).addClass('d-none');
                }
                updateTfootColspan();
            }
        }

        function updateTfootColspan() {
            let baseColspan = 4; // NO, KODE BARANG, NAMA BARANG, SATUAN
            if ($('#chkShowFoto').length && $('#chkShowFoto').is(':checked')) {
                baseColspan += 1; // FOTO
            }
            $('#tdTotalLabel').attr('colspan', baseColspan);
        }

        function downloadPdfScreenshot() {
            const $btns = $('.btn-download-ss');
            const originalTexts = $btns.map(function() { return $(this).html(); }).get();
            $btns.html('<span class="spinner-border spinner-border-sm me-1"></span> Mengambil Gambar...').prop('disabled', true);

            const targetElem = document.getElementById('printContent');

            html2canvas(targetElem, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                const fileNo = "{{ str_replace(['/', '\\'], '_', $stockAdjustment->nomor_adjustment) }}";
                link.download = 'Doc_Penyesuaian_Stok_' + fileNo + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                $btns.each(function(i) { $(this).html(originalTexts[i]).prop('disabled', false); });
            }).catch(err => {
                console.error('Screenshot error:', err);
                alert('Gagal mengambil screenshot dokumen. Silakan coba lagi.');
                $btns.each(function(i) { $(this).html(originalTexts[i]).prop('disabled', false); });
            });
        }

        $(document).ready(function() {
            updateTfootColspan();
        });
    </script>
</body>
</html>
