@php
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoPath = null;

    // Prioritaskan Logo Aplikasi Resmi dari AppSetting / default logo.png
    if ($appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))) {
        $appLogoPath = public_path('storage/' . $appLogoSetting);
    } elseif ($appLogoSetting && file_exists(storage_path('app/public/' . $appLogoSetting))) {
        $appLogoPath = storage_path('app/public/' . $appLogoSetting);
    } elseif (file_exists(public_path('gambar/logo.png'))) {
        $appLogoPath = public_path('gambar/logo.png');
    }

    $logoBase64 = null;
    if ($appLogoPath && file_exists($appLogoPath)) {
        $ext = strtolower(pathinfo($appLogoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($appLogoPath));
    }

    // Resolusi Foto Produk Barang Jadi yang sedang diproduksi
    $barangFoto = $spk->barangJadi?->primaryFoto ?? ($spk->barangJadi?->fotos?->first());
    $fotoProdukSrc = null;
    if ($barangFoto && !empty($barangFoto->foto_path)) {
        $candidatePaths = [
            public_path('storage/' . ltrim($barangFoto->foto_path, '/')),
            storage_path('app/public/' . ltrim($barangFoto->foto_path, '/')),
            public_path(ltrim($barangFoto->foto_path, '/')),
        ];
        foreach ($candidatePaths as $p) {
            if (file_exists($p)) {
                $extF = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mimeF = in_array($extF, ['png', 'webp', 'gif', 'svg']) ? ('image/' . $extF) : 'image/jpeg';
                $fotoProdukSrc = 'data:' . $mimeF . ';base64,' . base64_encode(file_get_contents($p));
                break;
            }
        }
        if (!$fotoProdukSrc) {
            $fotoProdukSrc = asset('storage/' . ltrim($barangFoto->foto_path, '/'));
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Kerja - {{ $spk->nomor_spk }}</title>
    <link rel="icon" href="{{ $logoBase64 ?: ($appLogo ?? asset('gambar/logo.png')) }}" type="image/png">
    <link rel="shortcut icon" href="{{ $logoBase64 ?: ($appLogo ?? asset('gambar/logo.png')) }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5pt;
            color: #0f172a;
            background: #090d16;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* SCALABLE VIEWPORT CONTAINER FOR PERFECT A4 MOBILE FIT */
        .a4-scalable-viewport {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 16px 0 40px 0;
            overflow-x: hidden;
        }

        /* PRINT DOCUMENT A4 CONTAINER */
        .print-document-area {
            width: 760px; /* Fixed A4 Proportions */
            min-height: 1050px;
            background: #ffffff;
            padding: 28px 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            box-sizing: border-box;
            position: relative;
            transition: transform 0.15s ease-out;
        }

        /* KOP SURAT HEADER - CINEMATIC BRANDING */
        .header-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-logo {
            max-height: 52px;
            max-width: 180px;
            object-fit: contain;
        }
        .company-name {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .company-sub {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.25;
        }

        /* DOCUMENT TITLE BADGE */
        .doc-badge-box {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 8px;
            border-bottom: 2.5px solid #3b82f6;
            text-align: right;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.2);
        }
        .doc-badge-title {
            font-size: 12pt;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #93c5fd;
            line-height: 1;
        }
        .doc-badge-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10pt;
            font-weight: 700;
            color: #ffffff;
            margin-top: 3px;
            letter-spacing: 0.5px;
        }
        .doc-badge-date {
            font-size: 8pt;
            color: #dbeafe;
            margin-top: 2px;
        }

        /* INFO CARD GRID */
        .info-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1e40af;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 20%;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
            width: 30%;
        }
        .info-val-primary {
            color: #1e40af;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10pt;
            font-weight: 800;
        }

        /* PRODUCT PHOTO PREVIEW IN SPK INFO CARD */
        .spk-product-photo-card {
            width: 125px;
            max-width: 125px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 5px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            vertical-align: middle;
        }
        .spk-product-photo-inner {
            width: 100%;
            height: 105px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .spk-product-photo-img {
            max-width: 100%;
            max-height: 105px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            transition: transform 0.2s ease;
        }
        .spk-product-photo-img:hover {
            transform: scale(1.05);
        }
        .spk-product-photo-caption {
            font-size: 6.8pt;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 4px;
            line-height: 1.1;
        }
        .spk-product-photo-code {
            font-size: 6.2pt;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 1px;
            line-height: 1.1;
        }

        /* BADGES */
        .badge-pill-custom {
            display: inline-block;
            padding: 2.5px 10px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            line-height: 1.3;
            vertical-align: middle;
        }
        .badge-priority-normal {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .badge-priority-urgent {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .badge-status-spk {
            background-color: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        /* SECTION HEADER & TABLE HEADERS - ROYAL BLUE THEME MATCHING USER IMAGE */
        .section-title-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px 8px 0 0;
            border-bottom: 2.5px solid #3b82f6;
            margin-top: 16px;
        }
        .section-title-text {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .section-title-sub {
            font-size: 7.5pt;
            color: #93c5fd;
        }

        /* DATA TABLES */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            border-top: none;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }
        .data-table th {
            background: #1e40af; /* Deep Royal Blue */
            color: #ffffff;
            font-weight: 800;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            border-bottom: 2.5px solid #2563eb;
            padding: 8px 8px;
            text-align: center;
        }
        .data-table th:last-child {
            border-right: none;
        }
        .data-table td {
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 8pt;
            color: #0f172a;
        }
        .data-table td:last-child {
            border-right: none;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .fw-bold { font-weight: 700; }

        /* CHECKBOX BOX FOR PRINT CHECKLIST */
        .chk-box-print {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #64748b;
            border-radius: 3px;
            margin-right: 4px;
            vertical-align: middle;
        }

        /* CATATAN / INSTRUKSI CARD */
        .catatan-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 8pt;
            color: #1e3a8a;
        }
        .catatan-title {
            font-weight: 700;
            margin-bottom: 2px;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }

        /* TANDA TANGAN SECTION */
        .sign-container {
            margin-top: 24px;
            margin-bottom: 10px;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .sign-title {
            font-size: 8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
        }
        .box-ttd-img {
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px 0;
        }
        .digital-stamp-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3px 10px;
            border: 1.5px dashed #2563eb;
            border-radius: 6px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 7.5pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .digital-stamp-badge.stamp-approved {
            border-color: #16a34a;
            background: #f0fdf4;
            color: #15803d;
        }
        .digital-stamp-date {
            font-size: 6.5pt;
            font-weight: 600;
            color: #64748b;
            margin-top: 1px;
            font-family: 'JetBrains Mono', monospace;
        }
        .sign-name {
            font-size: 9pt;
            font-weight: 700;
            color: #0f172a;
            text-decoration: underline;
            margin-top: 2px;
        }
        .sign-pos {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        .editable-field {
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 1px 4px;
        }
        .editable-field:hover {
            background-color: #fef9c3;
            outline: 1px dashed #ca8a04;
            cursor: text;
        }
        .editable-field:focus {
            background-color: #ffffff;
            outline: 2px solid #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
        }

        /* DOCUMENT FOOTER */
        .doc-footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 7pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* MOBILE TOOLBAR STYLING */
        @media (max-width: 768px) {
            .spk-toolbar-container {
                padding: 8px 10px !important;
            }
            .spk-toolbar-wrapper {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }
            .spk-switch-pill-box {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 50px;
                padding: 5px 8px;
                width: 100% !important;
                box-sizing: border-box;
                display: flex;
                justify-content: space-around;
            }
            .spk-btn-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr 1fr !important;
                gap: 6px !important;
                width: 100% !important;
                box-sizing: border-box;
            }
            .spk-btn-grid .btn {
                width: 100% !important;
                padding: 6px 4px !important;
                font-size: 11px !important;
                white-space: nowrap !important;
                justify-content: center !important;
                display: flex !important;
                align-items: center !important;
            }
            .spk-btn-grid .btn-tutup {
                grid-column: span 1 !important;
            }
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            .no-print { display: none !important; }
            .a4-scalable-viewport {
                padding: 0 !important;
                height: auto !important;
                width: 100% !important;
                display: block !important;
            }
            .print-document-area {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10mm 12mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                transform: none !important;
            }
            .editable-field {
                background: none !important;
                outline: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- NO PRINT TOOLBAR CONTROL BAR -->
    <div class="no-print bg-dark text-white border-bottom shadow-lg mb-2 spk-toolbar-container" style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;">
        <div class="container-fluid px-2 px-md-4 py-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 spk-toolbar-wrapper">
                
                {{-- TITLE HEADER --}}
                <div class="d-flex align-items-center justify-content-between w-100 w-md-auto">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="bi bi-printer fs-6"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold lh-1 text-truncate" style="font-size: 13.5px;">Pratinjau Cetak SPK</div>
                            <small class="text-info font-monospace text-truncate d-block" style="font-size: 11px;">{{ $spk->nomor_spk }}</small>
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-info d-md-none border border-info border-opacity-25 fs-8 flex-shrink-0 ms-2">A4 Fit</span>
                </div>

                {{-- SWITCH TOGGLES PILL BOX --}}
                <div class="d-flex align-items-center gap-2 gap-md-3 spk-switch-pill-box">
                    <div class="form-check form-switch mb-0 ps-4">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="chkShowFoto" checked onchange="togglePdfSection('spk-product-photo-card', this.checked)">
                        <label class="form-check-label small text-white cursor-pointer" for="chkShowFoto"><i class="bi bi-image me-1 text-info"></i>Foto</label>
                    </div>
                    <div class="form-check form-switch mb-0 ps-4">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="chkShowTtd" {{ (!isset($showTtd) || $showTtd) ? 'checked' : '' }} onchange="togglePdfSection('box-ttd-img', this.checked)">
                        <label class="form-check-label small text-white cursor-pointer" for="chkShowTtd"><i class="bi bi-vector-pen me-1 text-info"></i>TTD</label>
                    </div>
                    <div class="form-check form-switch mb-0 ps-4">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="chkShowBahan" checked onchange="togglePdfSection('section-bahan', this.checked)">
                        <label class="form-check-label small text-white cursor-pointer" for="chkShowBahan"><i class="bi bi-box-seam me-1 text-info"></i>Bahan</label>
                    </div>
                    <div class="form-check form-switch mb-0 ps-4">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="chkShowTahapan" checked onchange="togglePdfSection('section-tahapan', this.checked)">
                        <label class="form-check-label small text-white cursor-pointer" for="chkShowTahapan"><i class="bi bi-diagram-3 me-1 text-info"></i>Tahapan</label>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="spk-btn-grid d-flex align-items-center gap-2 w-100 w-md-auto">
                    <button onclick="downloadPdfScreenshot()" class="btn btn-outline-info btn-sm px-2 px-md-3 rounded-pill fw-bold text-white btn-download-ss shadow-xs">
                        <i class="bi bi-camera me-1"></i> Gambar
                    </button>
                    <button onclick="window.print()" class="btn btn-primary btn-sm px-2 px-md-3 rounded-pill fw-bold shadow-xs">
                        <i class="bi bi-printer me-1"></i> Cetak/PDF
                    </button>
                    <button onclick="window.close()" class="btn btn-outline-light btn-sm px-2 px-md-3 rounded-pill btn-tutup opacity-75">
                        <i class="bi bi-x-lg me-1 d-md-none"></i> Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- SCALABLE VIEWPORT CONTAINER FOR PERFECT MOBILE FIT --}}
    <div class="a4-scalable-viewport">
        <div class="print-document-area">
            
            {{-- KOP SURAT / HEADER --}}
            <div class="header-container">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 58%; vertical-align: middle;">
                            <div class="company-name">{{ $entityName ?? ($company->nama_perusahaan ?? 'PERUSAHAAN GARMEN & TEKSTIL') }}</div>
                            <div class="company-sub">Manufaktur & Produksi Industri Garmen</div>
                        </td>
                        <td style="width: 42%; vertical-align: middle;">
                            <div class="doc-badge-box">
                                <div class="doc-badge-title">Surat Perintah Kerja</div>
                                <div class="doc-badge-num">{{ $spk->nomor_spk }}</div>
                                <div class="doc-badge-date">Tanggal Terbit: <b>{{ $spk->tanggal_spk ? $spk->tanggal_spk->format('d/m/Y') : date('d/m/Y') }}</b></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- KARTU INFORMASI UTAMA SPK --}}
            <div class="info-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top; padding-right: 12px;">
                            <table class="info-table">
                                <tr>
                                    <td class="info-label">Produk Barang Jadi:</td>
                                    <td class="info-val" style="width: 38%;">
                                        [{{ $spk->barangJadi->kode_barang ?? '-' }}] {{ $spk->barangJadi->nama_barang ?? '-' }}
                                    </td>
                                    <td class="info-label" style="width: 18%;">Target Selesai:</td>
                                    <td class="info-val font-mono">
                                        {{ $spk->target_selesai ? $spk->target_selesai->format('d/m/Y') : 'Sesuai Antrean' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-label">Acuan Resep BOM:</td>
                                    <td class="info-val">
                                        [{{ $spk->bom->kode_bom ?? '-' }}] {{ $spk->bom->nama_bom ?? '-' }}
                                    </td>
                                    <td class="info-label">Tingkat Prioritas:</td>
                                    <td class="info-val">
                                        @if($spk->prioritas === 'Tinggi (Urgent)')
                                            <span class="badge-pill-custom badge-priority-urgent">🔥 Urgent</span>
                                        @else
                                            <span class="badge-pill-custom badge-priority-normal">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-label">Target Produksi:</td>
                                    <td class="info-val-primary">
                                        {{ number_format($spk->jumlah_rencana, 0, ',', '.') }} {{ $spk->satuan->nama_satuan ?? 'Pcs' }}
                                    </td>
                                    <td class="info-label">PIC Produksi:</td>
                                    <td class="info-val">
                                        {{ $spk->penanggungJawab->nama_lengkap ?? 'Kepala Line Produksi' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-label">Tipe Produksi:</td>
                                    <td class="info-val">{{ $spk->tipe_spk ?? 'Make to Stock' }}</td>
                                    <td class="info-label">Status SPK:</td>
                                    <td class="info-val">
                                        <span class="badge-pill-custom badge-status-spk">{{ $spk->status }}</span>
                                    </td>
                                </tr>
                                @php
                                    $printTender = $spk->tender ?? ($spk->bom?->tender);
                                @endphp
                                @if($printTender)
                                <tr>
                                    <td class="info-label">Terkait Tender:</td>
                                    <td class="info-val" colspan="3">
                                        <span class="badge-pill-custom" style="background:#f3e8ff;color:#6b21a8;border:1px solid #d8b4fe;font-size:8pt;">
                                            {{ $printTender->nama_tender }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                @if($spk->nomor_batch || $spk->serial_numbers)
                                <tr>
                                    <td class="info-label">No. Batch / Lot:</td>
                                    <td class="info-val" colspan="{{ $spk->serial_numbers ? '1' : '3' }}">
                                        <span class="badge-pill-custom" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:7.5pt;font-weight:700;">
                                            🏷️ {{ $spk->nomor_batch ?: '-' }}
                                        </span>
                                    </td>
                                    @if($spk->serial_numbers)
                                        @php
                                            $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $spk->serial_numbers)));
                                        @endphp
                                        <td class="info-label">Total SN:</td>
                                        <td class="info-val">
                                            <span class="badge-pill-custom" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:7.5pt;font-weight:700;">
                                                {{ count($snArray) }} UNIT TERDAFTAR
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                                @endif
                            </table>
                        </td>
                        <td style="width: 125px; vertical-align: middle; text-align: center;" class="spk-product-photo-card" id="spk-product-photo-card">
                            <div class="spk-product-photo-inner">
                                @if($fotoProdukSrc)
                                    <a href="javascript:void(0)" onclick="openProductPhotoModal('{{ $fotoProdukSrc }}', '{{ addslashes($spk->barangJadi->nama_barang ?? 'Produk Jadi') }}')" title="Klik untuk memperbesar gambar">
                                        <img src="{{ $fotoProdukSrc }}" alt="{{ $spk->barangJadi->nama_barang ?? 'Produk Jadi' }}" class="spk-product-photo-img">
                                    </a>
                                @else
                                    <div class="text-center p-2">
                                        <i class="bi bi-image text-muted" style="font-size: 26pt; display: block; opacity: 0.5;"></i>
                                        <span style="font-size: 6.8pt; color: #94a3b8; font-weight: 600; display: block; margin-top: 3px;">Tidak Ada Foto</span>
                                    </div>
                                @endif
                            </div>
                            <div class="spk-product-photo-caption">
                                Visual Produk Jadi
                            </div>
                            <div class="spk-product-photo-code text-truncate" title="{{ $spk->barangJadi->kode_barang ?? '' }}">
                                {{ $spk->barangJadi->kode_barang ?? '-' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- SECTION 1: DAFTAR KEBUTUHAN BAHAN BAKU --}}
            <div class="section-bahan">
                <div class="section-title-box">
                    <span class="section-title-text">1. Rincian Kebutuhan Bahan Baku (Material Requirements)</span>
                    <span class="section-title-sub">Gudang Bahan Baku</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;" class="text-center">No</th>
                            <th>Kode & Nama Bahan Baku</th>
                            <th style="width: 100px;" class="text-center">Kebutuhan/Unit</th>
                            <th style="width: 65px;" class="text-center">Waste %</th>
                            <th style="width: 110px;" class="text-center">Total Kebutuhan</th>
                            <th style="width: 80px;" class="text-center">Satuan</th>
                            <th style="width: 100px;" class="text-center">Status Ambil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spk->materials as $idx => $mat)
                        @php
                            $satName = $mat->satuan->nama_satuan ?? ($mat->barangMentah->satuan->nama_satuan ?? 'Pcs');
                            $needPerUnitFmt = (float)$mat->kebutuhan_per_unit == (int)$mat->kebutuhan_per_unit ? number_format($mat->kebutuhan_per_unit, 0, ',', '.') : rtrim(rtrim(number_format((float)$mat->kebutuhan_per_unit, 4, ',', '.'), '0'), ',');
                            $totalNeedFmt   = (float)$mat->total_kebutuhan == (int)$mat->total_kebutuhan ? number_format($mat->total_kebutuhan, 0, ',', '.') : rtrim(rtrim(number_format((float)$mat->total_kebutuhan, 4, ',', '.'), '0'), ',');
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                <b>[{{ $mat->barangMentah->kode_barang ?? '-' }}]</b> {{ $mat->barangMentah->nama_barang ?? '-' }}
                            </td>
                            <td class="text-center font-mono">{{ $needPerUnitFmt }}</td>
                            <td class="text-center">{{ (float)$mat->waste_percent }}%</td>
                            <td class="text-center font-mono fw-bold" style="color: #1d4ed8;">
                                {{ $totalNeedFmt }}
                            </td>
                            <td class="text-center fw-bold">{{ $satName }}</td>
                            <td class="text-center">
                                <span class="chk-box-print"></span> Sesuai
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 12px; color: #64748b;">Tidak ada rincian bahan baku.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- SECTION 2: TAHAPAN OPERASI & PENGERJAAN (ROUTING) --}}
            <div class="section-tahapan">
                <div class="section-title-box">
                    <span class="section-title-text">2. Lembar Kontrol Tahapan Kerja (Production Routing Control)</span>
                    <span class="section-title-sub">Line Operasional Produksi</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;" class="text-center">No</th>
                            <th>Tahapan Proses Kerja</th>
                            <th style="width: 90px;" class="text-center">Target Qty</th>
                            <th style="width: 90px;" class="text-center">Qty Selesai</th>
                            <th style="width: 80px;" class="text-center">Qty Reject</th>
                            <th style="width: 130px;" class="text-center">Operator / PIC</th>
                            <th style="width: 75px;" class="text-center">Paraf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spk->tahapans as $tahap)
                        <tr>
                            <td class="text-center fw-bold">{{ $tahap->urutan }}</td>
                            <td class="fw-bold" style="color: #0f172a;">{{ $tahap->nama_tahapan }}</td>
                            <td class="text-center font-mono fw-bold">{{ number_format($tahap->target_qty, 0, ',', '.') }}</td>
                            <td class="text-center font-mono">
                                @if($tahap->status !== 'Menunggu')
                                    <b>{{ number_format($tahap->qty_selesai, 0, ',', '.') }}</b>
                                @else
                                    <span style="color: #94a3b8; font-size: 7.5pt;">. . . . . . . . . . . . .</span>
                                @endif
                            </td>
                            <td class="text-center font-mono">
                                @if($tahap->status !== 'Menunggu')
                                    <span class="{{ $tahap->qty_reject > 0 ? 'text-danger fw-bold' : '' }}">
                                        {{ number_format($tahap->qty_reject, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-size: 7.5pt;">. . . . . . . . . . . . .</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tahap->status !== 'Menunggu')
                                    {{ $tahap->pic_operator ?: '-' }}
                                @else
                                    <span style="color: #94a3b8; font-size: 7.5pt;">. . . . . . . . . . . . .</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tahap->status === 'Selesai')
                                    <span style="color: #10b981; font-weight: bold; font-size: 8pt;"><i class="bi bi-check2-all"></i> OK</span>
                                @elseif($tahap->status === 'Sedang Dikerjakan')
                                    <span style="color: #0ea5e9; font-weight: bold; font-size: 7.5pt;"><i class="bi bi-arrow-repeat"></i> Proses</span>
                                @else
                                    <span style="color: #94a3b8; font-size: 7.5pt;">. . . . . . . . . .</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 12px; color: #64748b;">Tidak ada tahapan kerja.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>



            {{-- CATATAN / INSTRUKSI KHUSUS --}}
            @if($spk->keterangan)
            <div class="catatan-box">
                <div class="catatan-title"><i class="bi bi-info-circle-fill me-1"></i> Catatan / Spesifikasi Khusus:</div>
                <div>{{ $spk->keterangan }}</div>
            </div>
            @endif

            @php
                $sigCreator   = $creatorSigUrl ?? $ttdDibuatUrl ?? null;
                $sigInspector = $inspectorSigUrl ?? $ttdDiperiksaUrl ?? null;
                $sigApprover  = $approverSigUrl ?? $ttdDisetujuiUrl ?? null;
            @endphp
            {{-- 3 BLOK TANDA TANGAN RESMI --}}
            <div class="sign-container">
                <table class="sign-table">
                    <tr>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true" title="Klik untuk mengedit judul">Dibuat Oleh (Perencana)</div>
                                <div class="box-ttd-img" style="{{ (!isset($showTtd) || $showTtd) ? '' : 'visibility: hidden;' }}">
                                    @if(!empty($sigCreator))
                                        <img src="{{ $sigCreator }}" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                    @else
                                        <div class="digital-stamp-badge">
                                            <span><i class="bi bi-patch-check-fill text-primary me-1"></i>VERIFIED CREATED</span>
                                            <span class="digital-stamp-date">{{ $spk->created_at ? $spk->created_at->format('d/m/Y H:i') : date('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="sign-name editable-field" contenteditable="true" title="Klik untuk mengedit nama">{{ $namaDibuat ?? ($spk->creator->nama_lengkap ?? 'Staff PPIC / Produksi') }}</div>
                                <div class="sign-pos editable-field" contenteditable="true" title="Klik untuk mengedit jabatan">{{ $spk->creator->position->nama_posisi ?? 'Production Planner' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true" title="Klik untuk mengedit judul">Diperiksa (PIC Line)</div>
                                <div class="box-ttd-img" style="{{ (!isset($showTtd) || $showTtd) ? '' : 'visibility: hidden;' }}">
                                    @if(in_array($spk->status, ['In Progress', 'Completed']))
                                        @if(!empty($sigInspector))
                                            <img src="{{ $sigInspector }}" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                        @else
                                            <div class="digital-stamp-badge">
                                                <span><i class="bi bi-person-check-fill text-info me-1"></i>ASSIGNED PIC</span>
                                                <span class="digital-stamp-date">Line Produksi</span>
                                            </div>
                                        @endif
                                    @else
                                        <div style="height: 42px;"></div>
                                    @endif
                                </div>
                                <div class="sign-name editable-field" contenteditable="true" title="Klik untuk mengedit nama">
                                    @if(in_array($spk->status, ['In Progress', 'Completed']))
                                        {{ $namaDiperiksa ?? ($spk->penanggungJawab->nama_lengkap ?? 'Supervisor / PIC Line') }}
                                    @else
                                        {{ $spk->penanggungJawab ? $spk->penanggungJawab->nama_lengkap : '( .................... )' }}
                                    @endif
                                </div>
                                <div class="sign-pos editable-field" contenteditable="true" title="Klik untuk mengedit jabatan">
                                    @if(in_array($spk->status, ['In Progress', 'Completed']))
                                        {{ $jabatanDiperiksa ?? ($spk->penanggungJawab->position->nama_posisi ?? 'Supervisor / PIC Line') }}
                                    @else
                                        {{ $spk->penanggungJawab ? ($spk->penanggungJawab->position->nama_posisi ?? 'Supervisor / PIC Line') : 'PIC Line Produksi' }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true" title="Klik untuk mengedit judul">Disetujui (Manajer Operasional)</div>
                                <div class="box-ttd-img" style="{{ (!isset($showTtd) || $showTtd) ? '' : 'visibility: hidden;' }}">
                                    @if(!empty($sigApprover))
                                        <img src="{{ $sigApprover }}" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                    @elseif(in_array($spk->status, ['Approved', 'In Progress', 'Completed']))
                                        <div class="digital-stamp-badge stamp-approved">
                                            <span><i class="bi bi-patch-check-fill text-success me-1"></i>SYSTEM APPROVED</span>
                                            <span class="digital-stamp-date">{{ $spk->updated_at ? $spk->updated_at->format('d/m/Y H:i') : date('d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <div style="height: 42px;"></div>
                                    @endif
                                </div>
                                <div class="sign-name editable-field" contenteditable="true" title="Klik untuk mengedit nama">{{ $namaDisetujui ?? ($spk->approver->nama_lengkap ?? 'Manajer Operasional') }}</div>
                                <div class="sign-pos editable-field" contenteditable="true" title="Klik untuk mengedit jabatan">{{ $spk->approver->position->nama_posisi ?? 'Production Manager' }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- DOCUMENT FOOTER BRANDING REMOVED --}}

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function scaleA4ToScreen() {
            const wrapper = document.querySelector('.a4-scalable-viewport');
            const doc = document.querySelector('.print-document-area');
            if (!wrapper || !doc) return;

            const screenWidth = window.innerWidth;
            const targetA4Width = 760; // Exact A4 document width in px

            if (screenWidth < 780) {
                const availableWidth = screenWidth - 16; // 8px padding left/right
                const scale = availableWidth / targetA4Width;
                
                doc.style.transform = `scale(${scale})`;
                doc.style.transformOrigin = 'top center';
                
                // Recalculate parent wrapper height after scaling
                const scaledHeight = doc.offsetHeight * scale;
                wrapper.style.height = (scaledHeight + 20) + 'px';
            } else {
                doc.style.transform = 'none';
                wrapper.style.height = 'auto';
            }
        }

        function togglePdfSection(className, show) {
            if (className === 'box-ttd-img') {
                if (show) {
                    $('.box-ttd-img').css('visibility', 'visible');
                } else {
                    $('.box-ttd-img').css('visibility', 'hidden');
                }
            } else {
                if (show) {
                    $('.' + className).removeClass('d-none');
                } else {
                    $('.' + className).addClass('d-none');
                }
            }
            setTimeout(scaleA4ToScreen, 100);
        }

        function downloadPdfScreenshot() {
            const $btn = $('.btn-download-ss');
            const originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Mengambil Gambar...').prop('disabled', true);

            const doc = document.querySelector('.print-document-area');
            const originalTransform = doc.style.transform;
            
            // Temporarily reset transform scale for crisp 2x full resolution capture
            doc.style.transform = 'none';

            html2canvas(doc, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                // Restore scale
                scaleA4ToScreen();

                const link = document.createElement('a');
                const fileNo = "{{ str_replace(['/', '\\'], '_', $spk->nomor_spk) }}";
                link.download = 'SPK_' + fileNo + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                $btn.html(originalText).prop('disabled', false);
            }).catch(err => {
                console.error('Screenshot error:', err);
                scaleA4ToScreen();
                alert('Gagal mengambil screenshot dokumen. Silakan coba lagi.');
                $btn.html(originalText).prop('disabled', false);
            });
        }

        $(document).ready(function() {
            scaleA4ToScreen();
        });
        $(window).on('resize', scaleA4ToScreen);

        function openProductPhotoModal(imgSrc, title) {
            if (!imgSrc) return;
            $('#modalFotoProdukImg').attr('src', imgSrc);
            if (title) $('#modalFotoProdukTitle').html('<i class="bi bi-image text-info"></i> ' + title);
            const modalEl = document.getElementById('modalPreviewFotoProduk');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    </script>

    <!-- MODAL PREVIEW FOTO PRODUK HD (NO PRINT) -->
    <div class="modal fade no-print" id="modalPreviewFotoProduk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-white">
                <div class="modal-header border-bottom border-secondary py-2.5 px-3">
                    <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0" id="modalFotoProdukTitle">
                        <i class="bi bi-image text-info"></i> Visual Produk Jadi
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2 text-center bg-black d-flex align-items-center justify-content-center" style="min-height: 350px;">
                    <img id="modalFotoProdukImg" src="" alt="Foto Produk" class="img-fluid rounded-3" style="max-height: 75vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-top border-secondary py-2 px-3 justify-content-between">
                    <small class="text-secondary font-monospace">{{ $spk->barangJadi->kode_barang ?? '' }} - {{ $spk->barangJadi->nama_barang ?? '' }}</small>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
