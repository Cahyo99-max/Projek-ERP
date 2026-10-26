@php
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoPath = null;
    $appLogoUrl  = null;

    if ($appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))) {
        $appLogoPath = public_path('storage/' . $appLogoSetting);
        $appLogoUrl  = asset('storage/' . $appLogoSetting);
    } elseif (file_exists(public_path('gambar/logo_1786070712.png'))) {
        $appLogoPath = public_path('gambar/logo_1786070712.png');
        $appLogoUrl  = asset('gambar/logo_1786070712.png');
    } elseif ($company && !empty($company->logo) && file_exists(public_path('storage/' . $company->logo))) {
        $appLogoPath = public_path('storage/' . $company->logo);
        $appLogoUrl  = asset('storage/' . $company->logo);
    } elseif (file_exists(public_path('gambar/logo.png'))) {
        $appLogoPath = public_path('gambar/logo.png');
        $appLogoUrl  = asset('gambar/logo.png');
    }

    $logoBase64 = null;
    if ($appLogoPath && file_exists($appLogoPath)) {
        $ext = strtolower(pathinfo($appLogoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($appLogoPath));
    }

    $penyerahName = $pengeluaran->penyerah ? ($pengeluaran->penyerah->nama_lengkap ?? $pengeluaran->penyerah->nama ?? $pengeluaran->penyerah->name) : ($pengeluaran->penyerah_nama ?: ($pengeluaran->creator ? $pengeluaran->creator->name : 'Petugas Gudang'));
    $penyerahPos = $pengeluaran->penyerah && $pengeluaran->penyerah->position ? $pengeluaran->penyerah->position->name : 'Bagian Gudang';

    $penerimaName = $pengeluaran->penerima ? ($pengeluaran->penerima->nama_lengkap ?? $pengeluaran->penerima->nama ?? $pengeluaran->penerima->name) : ($pengeluaran->penerima_nama ?: 'Tim Produksi');
    $penerimaPos = $pengeluaran->penerima && $pengeluaran->penerima->position ? $pengeluaran->penerima->position->name : 'Line Produksi';

    $spkObj = $pengeluaran->spk;
    $barangJadi = $spkObj ? $spkObj->barangJadi : null;
    $targetQty = $spkObj ? (float)$spkObj->jumlah_rencana : 0;
    $targetSat = $spkObj && $spkObj->satuan ? $spkObj->satuan->nama_satuan : ($barangJadi && $barangJadi->satuan ? $barangJadi->satuan->nama_satuan : 'Pcs');

    if (!function_exists('formatCleanQty')) {
        function formatCleanQty($val) {
            $num = (float)$val;
            if ($num == floor($num)) {
                return number_format($num, 0, ',', '.');
            }
            $formatted = number_format($num, 4, ',', '.');
            return rtrim(rtrim($formatted, '0'), ',');
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran Bahan Baku - {{ $pengeluaran->nomor_pengeluaran }}</title>
    @if(!empty($appLogoUrl))
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/png">
    @elseif(!empty($logoBase64))
    <link rel="icon" href="{{ $logoBase64 }}">
    @endif
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9pt;
            color: #0f172a;
            background: #090d16;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* TOP ACTION BAR (SCREEN ONLY) */
        .top-action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* MOBILE RESPONSIVE TOOLBAR */
        @media (max-width: 768px) {
            .top-action-bar {
                padding: 10px 14px;
                flex-direction: column;
                gap: 8px;
            }
            .mobile-header-row {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .mobile-btn-grid {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .top-action-bar .btn {
                font-size: 8.5pt;
                padding: 7px 10px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* SCALABLE VIEWPORT CONTAINER */
        .a4-scalable-viewport {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 0 50px 0;
            overflow-x: auto;
            transition: height 0.2s ease;
        }

        /* PRINT DOCUMENT A4 CONTAINER */
        .print-document-area {
            width: 794px; /* Standard A4 width */
            min-height: 1123px; /* Standard A4 height */
            background: #ffffff;
            padding: 36px 40px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255,255,255,0.08);
            border-radius: 6px;
            box-sizing: border-box;
            position: relative;
        }

        /* KOP PERUSAHAAN & HEADER */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .company-badge-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .company-accent-line {
            width: 4.5px;
            height: 36px;
            background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 4px;
        }
        .company-name {
            font-size: 15pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.3px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .company-tagline {
            font-size: 7.5pt;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }

        .doc-header-right {
            text-align: right;
        }
        .doc-main-title {
            font-size: 14pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.2px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .doc-sub-title {
            font-size: 7.5pt;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .doc-number-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border: 1.5px solid #1e3a8a;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5pt;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 5px;
        }

        /* METADATA EXECUTIVE GRID */
        .meta-grid-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .meta-label {
            color: #475569;
            font-weight: 600;
            width: 18%;
        }
        .meta-sep {
            width: 2%;
            color: #94a3b8;
            font-weight: bold;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 700;
            width: 30%;
        }
        .badge-spk-pill {
            background: #eff6ff;
            color: #0a2540;
            border: 1px solid #bfdbfe;
            padding: 2px 7px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8.5pt;
            font-weight: 700;
            display: inline-block;
        }

        /* SECTION TITLE */
        .section-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #0a2540;
        }
        .section-header-title {
            font-size: 9pt;
            font-weight: 800;
            color: #0a2540;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .section-header-sub {
            font-size: 7.5pt;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        /* CINEMATIC DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 18px;
            border-radius: 8px;
            overflow: hidden;
            border: 1.5px solid #1e3a8a;
        }
        .data-table th {
            background: #1e3a8a !important; /* Deep Royal Blue matching user reference design */
            color: #ffffff !important;
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 11px 12px;
            border: none;
        }
        .data-table th:first-child {
            border-top-left-radius: 7px;
        }
        .data-table th:last-child {
            border-top-right-radius: 7px;
        }
        .data-table td {
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            padding: 9px 12px;
            font-size: 8.5pt;
            color: #0f172a;
            vertical-align: middle;
        }
        .data-table td:last-child {
            border-right: none;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-num {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
            font-size: 8.5pt;
            font-weight: 700;
            color: #0f172a;
        }
        .item-code-badge {
            background: #eff6ff;
            color: #091e36;
            border: 1px solid #93c5fd;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8pt;
            font-weight: 700;
            margin-right: 4px;
        }
        .pill-diambil {
            background: #eff6ff;
            color: #091e36;
            border: 1.5px solid #1e40af;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            display: inline-block;
        }
        .pill-sisa-ok {
            color: #059669;
            font-weight: 700;
        }
        .pill-sisa-amber {
            color: #091e36;
            font-weight: 700;
        }

        /* CATATAN BOX */
        .remarks-callout {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #0a2540;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 24px;
            font-size: 8.5pt;
        }

        /* SIGNATURE SECTION */
        .sign-grid-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .sign-grid-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 12px;
        }
        .sign-role-title {
            font-size: 8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .sign-space-gap {
            height: 62px;
        }
        .sign-person-name {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 160px;
        }
        .sign-person-pos {
            font-size: 7.5pt;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
        }

        /* INLINE EDITABLE STYLING */
        .editable-field {
            transition: all 0.15s ease;
            border-radius: 4px;
            padding: 1px 4px;
            cursor: text;
        }
        .editable-field:hover {
            background-color: #fef9c3;
            outline: 1px dashed #ca8a04;
        }
        .editable-field:focus {
            background-color: #ffffff;
            outline: 2px solid #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        @page {
            size: A4 portrait;
            margin: 0mm;
        }
        @media print {
            html, body {
                background: #ffffff !important;
                font-size: 8.5pt;
                margin: 0 !important;
                padding: 10mm 14mm !important;
            }
            .editable-field {
                background-color: transparent !important;
                outline: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .no-print, .top-action-bar {
                display: none !important;
            }
            .a4-scalable-viewport {
                padding: 0 !important;
                display: block !important;
                width: 100% !important;
                height: auto !important;
            }
            .print-document-area {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- TOP ACTION BAR (SCREEN ONLY) --}}
    <div class="top-action-bar no-print">
        <div class="mobile-header-row">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-2" onclick="window.close()">
                <i class="bi bi-arrow-left"></i>
                <span>Tutup</span>
            </button>
            <span class="text-white font-monospace small px-2.5 py-1 rounded" style="background: rgba(255,255,255,0.08); font-size: 8pt;">
                <i class="bi bi-file-earmark-check text-info me-1"></i><strong>{{ $pengeluaran->nomor_pengeluaran }}</strong>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
            <div class="d-none d-md-flex align-items-center gap-1.5 me-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1.5 fw-semibold" onclick="zoomDoc(0.9)">
                    <i class="bi bi-zoom-out"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1.5 fw-semibold" onclick="zoomDoc(1.0)">
                    <span>100%</span>
                </button>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1.5 fw-semibold" onclick="zoomDoc(1.1)">
                    <i class="bi bi-zoom-in"></i>
                </button>
            </div>
            <div class="mobile-btn-grid">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-2 btn-download-ss" onclick="downloadDocScreenshot()">
                    <i class="bi bi-camera-fill text-warning"></i>
                    <span>Download SS</span>
                </button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="window.print()" style="background: linear-gradient(135deg, #0a2540, #1e40af); border: none;">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MAIN A4 SCALABLE CONTAINER --}}
    <div class="a4-scalable-viewport">
        <div class="print-document-area" id="printArea">
            
            {{-- KOP PERUSAHAAN & HEADER --}}
            <div class="header-container">
                <div class="company-badge-wrap">
                    <div class="company-accent-line"></div>
                    <div>
                        <div class="company-name editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama entitas">{{ $entityName ?? ($company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA') }}</div>
                    </div>
                </div>
                <div class="doc-header-right">
                    <div class="doc-main-title">Pengeluaran Bahan Baku</div>
                
                    <div class="doc-number-pill">
                        <i class="bi bi-file-earmark-check text-primary"></i>
                        <span>{{ $pengeluaran->nomor_pengeluaran }}</span>
                    </div>
                </div>
            </div>

            {{-- METADATA EXECUTIVE GRID (2 KOLOM) --}}
            <div class="meta-grid-card">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">No. SPK Produksi</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            <span class="badge-spk-pill">{{ $spkObj->nomor_spk ?? '-' }}</span>
                        </td>

                        <td class="meta-label">Tgl. Pengeluaran</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val" style="font-family: 'JetBrains Mono', monospace;">
                            {{ $pengeluaran->tanggal_pengeluaran ? $pengeluaran->tanggal_pengeluaran->format('d/m/Y') : date('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Produk Jadi (Output)</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            @if($barangJadi)
                                <span class="text-dark">[{{ $barangJadi->kode_barang }}] {{ $barangJadi->nama_barang }}</span>
                                <div style="font-size: 7.5pt; font-weight: normal; color: #64748b; margin-top: 1px;">
                                    Target Rencana: <b class="text-dark">{{ number_format($targetQty, 0, ',', '.') }} {{ $targetSat }}</b>
                                </div>
                            @else
                                -
                            @endif
                        </td>

                        <td class="meta-label">Gudang Asal</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            {{ $pengeluaran->gudang ? '['.$pengeluaran->gudang->kode_gudang.'] '.$pengeluaran->gudang->nama_gudang : 'Gudang Utama' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Petugas Penyerah</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            <span class="editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama">{{ $penyerahName }}</span>
                        </td>

                        <td class="meta-label">Penerima / PIC Line</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">
                            <span class="editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama">{{ $penerimaName }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- SECTION HEADER --}}
            <div class="section-header-bar">
                <span class="section-header-title">Rincian Bahan Mentah yang Diserahkan</span>
                <span class="section-header-sub">Total: {{ $pengeluaran->details->count() }} Jenis Item</span>
            </div>

            {{-- CINEMATIC DATA TABLE --}}
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 36px;" class="text-center">NO</th>
                        <th>KODE & NAMA BAHAN BAKU</th>
                        <th class="text-end" style="width: 110px;">TOTAL SPK</th>
                        <th class="text-end" style="width: 110px;">SDH DIAMBIL</th>
                        <th class="text-end" style="width: 125px;">DIAMBIL SEKARANG</th>
                        <th class="text-end" style="width: 110px;">SISA KEBUTUHAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumTotalSpk = 0;
                        $sumDiambil = 0;
                    @endphp
                    @forelse($pengeluaran->details as $index => $d)
                        @php
                            $bm = $d->barangMentah;
                            $satName = $d->satuan ? $d->satuan->nama_satuan : ($bm && $bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');
                            $totalSpk = (float)($d->qty_total_spk ?: 0);
                            $sdhDiambil = (float)($d->qty_sudah_diambil ?: 0);
                            $diambilSekarang = (float)($d->qty_diambil_sekarang ?: 0);
                            $sisaKebutuhan = max(0, $totalSpk - ($sdhDiambil + $diambilSekarang));

                            $sumTotalSpk += $totalSpk;
                            $sumDiambil += $diambilSekarang;
                        @endphp
                        <tr>
                            <td class="text-center data-num text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold" style="color: #0f172a;">
                                    <span class="item-code-badge">[{{ $bm->kode_barang ?? '-' }}]</span>
                                    <span>{{ $bm->nama_barang ?? '-' }}</span>
                                </div>
                                @if(!empty($d->catatan))
                                    <div style="font-size: 7.5pt; color: #64748b; font-style: italic; margin-top: 1px;">
                                        Catatan: {{ $d->catatan }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end data-num">{{ formatCleanQty($totalSpk) }} <span style="font-size: 7.5pt; color: #64748b; font-weight: normal;">{{ $satName }}</span></td>
                            <td class="text-end data-num" style="color: #64748b;">{{ formatCleanQty($sdhDiambil) }} <span style="font-size: 7.5pt; color: #64748b; font-weight: normal;">{{ $satName }}</span></td>
                            <td class="text-end data-num">
                                <span class="pill-diambil">+{{ formatCleanQty($diambilSekarang) }} {{ $satName }}</span>
                            </td>
                            <td class="text-end data-num {{ $sisaKebutuhan <= 0 ? 'pill-sisa-ok' : 'pill-sisa-amber' }}">
                                {{ formatCleanQty($sisaKebutuhan) }} <span style="font-size: 7.5pt; font-weight: normal; color: #64748b;">{{ $satName }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Tidak ada rincian bahan pada dokumen pengeluaran ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- CATATAN KHUSUS / REMARKS --}}
            @if(!empty($pengeluaran->catatan))
                <div class="remarks-callout">
                    <strong style="color: #0f172a;"><i class="bi bi-info-circle me-1 text-primary"></i>Catatan Pengeluaran / Instruksi:</strong>
                    <div class="editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit catatan" style="color: #334155; margin-top: 2px;">{{ $pengeluaran->catatan }}</div>
                </div>
            @endif

            {{-- TANDA TANGAN 3 PIHAK --}}
            <table class="sign-grid-table">
                <tr>
                    <td>
                        <div class="sign-role-title editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit role">Diserahkan Oleh (Gudang)</div>
                        <div class="sign-space-gap"></div>
                        <div class="sign-person-name editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama">{{ $penyerahName }}</div>
                        <div class="sign-person-pos editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan">{{ $penyerahPos }}</div>
                    </td>
                    <td>
                        <div class="sign-role-title editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit role">Diterima Oleh (Produksi)</div>
                        <div class="sign-space-gap"></div>
                        <div class="sign-person-name editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama">{{ $penerimaName }}</div>
                        <div class="sign-person-pos editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan">{{ $penerimaPos }}</div>
                    </td>
                    <td>
                        <div class="sign-role-title editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit role">Mengetahui (Supervisor / QC)</div>
                        <div class="sign-space-gap"></div>
                        <div class="sign-person-name editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
                        <div class="sign-person-pos editable-field" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan">Supervisor Operasional</div>
                    </td>
                </tr>
            </table>

            {{-- END OF DOCUMENT --}}
        </div>
    </div>

    <script>
        let currentZoom = 1.0;

        function scaleA4ToScreen() {
            const wrapper = document.querySelector('.a4-scalable-viewport');
            const doc = document.getElementById('printArea');
            if (!wrapper || !doc) return;

            const screenWidth = window.innerWidth;
            const targetA4Width = 794; // Exact standard A4 width in px

            if (screenWidth < 820) {
                const availableWidth = screenWidth - 20; // 10px margin on each side
                const scale = availableWidth / targetA4Width;
                
                doc.style.transform = `scale(${scale})`;
                doc.style.transformOrigin = 'top center';
                
                const scaledHeight = doc.offsetHeight * scale;
                wrapper.style.height = (scaledHeight + 30) + 'px';
                wrapper.style.padding = '12px 0 30px 0';
            } else {
                doc.style.transform = (currentZoom !== 1.0) ? `scale(${currentZoom})` : 'none';
                doc.style.transformOrigin = 'top center';
                wrapper.style.height = 'auto';
                wrapper.style.padding = '24px 0 50px 0';
            }
        }

        function zoomDoc(factor) {
            currentZoom = factor;
            const printArea = document.getElementById('printArea');
            if (printArea && window.innerWidth >= 820) {
                printArea.style.transform = `scale(${currentZoom})`;
                printArea.style.transformOrigin = 'top center';
            }
        }

        window.addEventListener('load', scaleA4ToScreen);
        window.addEventListener('resize', scaleA4ToScreen);
        document.addEventListener('DOMContentLoaded', scaleA4ToScreen);

        function downloadDocScreenshot() {
            const btn = document.querySelector('.btn-download-ss');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengambil SS...';
            btn.disabled = true;

            const doc = document.getElementById('printArea');
            const originalTransform = doc.style.transform;
            
            // Temporarily reset transform scale for crisp full 2x capture
            doc.style.transform = 'none';

            html2canvas(doc, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                doc.style.transform = originalTransform;
                scaleA4ToScreen();

                const link = document.createElement('a');
                const fileNo = "{{ str_replace(['/', '\\'], '_', $pengeluaran->nomor_pengeluaran) }}";
                link.download = 'PENGELUARAN_BAHAN_' + fileNo + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }).catch(err => {
                console.error('Screenshot error:', err);
                doc.style.transform = originalTransform;
                scaleA4ToScreen();
                alert('Gagal mengambil screenshot dokumen. Silakan coba lagi.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>

