@php
    // Deteksi Entitas Aktif: PT Pusat vs Cabang
    $activeBranch = $branch ?? $penerimaan->branch ?? (session('active_branch_id') ? \App\Models\Branch::find(session('active_branch_id')) : null);
    $activeCompany = $company ?? $penerimaan->company ?? (session('active_company_id') ? \App\Models\Company::find(session('active_company_id')) : \App\Models\Company::first());

    $isBranch = !empty($activeBranch);

    if ($isBranch && $activeBranch) {
        $entityTitle   = $activeBranch->nama_cabang;
        $entitySub     = $activeCompany ? $activeCompany->nama_perusahaan : 'Kantor Cabang';
        $entityAddress = $activeBranch->address ?: ($activeCompany ? $activeCompany->address : '-');
        $entityPhone   = $activeBranch->phone ?: ($activeCompany ? $activeCompany->phone : '-');
        $entityEmail   = $activeBranch->email ?: ($activeCompany ? $activeCompany->email : '-');
        $entityBadge   = 'CABANG';
    } else {
        $entityTitle   = $activeCompany ? $activeCompany->nama_perusahaan : 'PT PERSADA AMAN SENTOSA';
        $entitySub     = 'Kantor Pusat & Manufaktur';
        $entityAddress = $activeCompany ? $activeCompany->address : 'Alamat Perusahaan Utama';
        $entityPhone   = $activeCompany ? ($activeCompany->phone ?: '-') : '-';
        $entityEmail   = $activeCompany ? ($activeCompany->email ?: '-') : '-';
        $entityBadge   = 'KANTOR PUSAT';
    }

    // Prioritaskan Logo Aplikasi Resmi (App Logo / Favicon)
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoPath = null;
    $appLogoUrl  = null;

    if ($appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))) {
        $appLogoPath = public_path('storage/' . $appLogoSetting);
        $appLogoUrl  = asset('storage/' . $appLogoSetting);
    } elseif ($appLogoSetting && file_exists(storage_path('app/public/' . $appLogoSetting))) {
        $appLogoPath = storage_path('app/public/' . $appLogoSetting);
        $appLogoUrl  = asset('storage/' . $appLogoSetting);
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

    $spkObj     = $penerimaan->spk;
    $barangJadi = $penerimaan->barangJadi ?: ($spkObj ? $spkObj->barangJadi : null);
    $satName    = $penerimaan->satuan ? $penerimaan->satuan->nama_satuan : ($barangJadi && $barangJadi->satuan ? $barangJadi->satuan->nama_satuan : 'Pcs');

    $penerimaName = $penerimaan->penerima ? ($penerimaan->penerima->nama_lengkap ?? $penerimaan->penerima->nama ?? $penerimaan->penerima->name) : ($penerimaan->penerima_nama ?: 'Petugas Gudang');
    $penyerahName = $penerimaan->penyerah ? ($penerimaan->penyerah->nama_lengkap ?? $penerimaan->penyerah->nama ?? $penerimaan->penyerah->name) : ($penerimaan->penyerah_nama ?: 'QC / Line Operator');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Barang Jadi - {{ $penerimaan->nomor_penerimaan }}</title>
    <link rel="icon" href="{{ $logoBase64 ?: ($appLogo ?? $appLogoUrl ?? asset('gambar/logo.png')) }}" type="image/png">
    <link rel="shortcut icon" href="{{ $logoBase64 ?: ($appLogo ?? $appLogoUrl ?? asset('gambar/logo.png')) }}" type="image/png">
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9pt;
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
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.15;
        }
        .company-sub {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.25;
        }
        .company-contact {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* DOCUMENT TITLE BADGE - ROYAL BLUE THEME */
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
            font-size: 11pt;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #93c5fd;
            line-height: 1.1;
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
            font-size: 7.5pt;
            color: #dbeafe;
            margin-top: 2px;
        }

        /* INFO CARD GRID */
        .info-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1e40af;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3.5px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 22%;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
            width: 28%;
        }
        .info-val-primary {
            color: #1e40af;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5pt;
            font-weight: 800;
        }

        /* BADGES */
        .badge-pill-custom {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            line-height: 1.3;
            vertical-align: middle;
        }
        .badge-status-completed {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-status-progress {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }
        .badge-entity-type {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-size: 7pt;
            padding: 1px 6px;
        }

        /* DATA TABLES - ROYAL BLUE */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
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
            padding: 8px 8px;
            font-size: 8.5pt;
            color: #0f172a;
            vertical-align: middle;
        }
        .data-table td:last-child {
            border-right: none;
        }
        .data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: 800;
            border-bottom: none;
            padding: 8px 8px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-end { text-align: right; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .fw-bold { font-weight: 700; }

        /* SERIAL NUMBER CHIPS CONTAINER */
        .sn-container-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }
        .sn-header-title {
            font-size: 8pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 5px;
        }
        .sn-chip {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 3px 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 7.5pt;
            font-weight: 600;
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sn-chip .sn-idx {
            color: #64748b;
            font-weight: 500;
            margin-right: 4px;
        }
        .sn-chip .sn-code {
            color: #1e3a8a;
            font-weight: 700;
        }

        /* CATATAN / KETERANGAN CARD */
        .catatan-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 8pt;
            color: #1e3a8a;
        }
        .catatan-title {
            font-weight: 700;
            margin-bottom: 2px;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.3px;
        }

        /* TANDA TANGAN SECTION - RATA & PRESISI */
        .sign-container {
            margin-top: 24px;
            margin-bottom: 8px;
        }
        .sign-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            table-layout: fixed;
        }
        .sign-table td {
            width: 33.333%;
            text-align: center;
            vertical-align: top;
            padding: 0;
        }
        .sign-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            min-height: 155px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
        .sign-title {
            font-size: 7.5pt;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            min-height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.25;
        }
        .box-ttd-img {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px 0;
        }
        .box-ttd-img img {
            max-height: 56px;
            max-width: 140px;
            object-fit: contain;
        }
        .sign-bottom {
            margin-top: auto;
        }
        .sign-name {
            font-size: 8.5pt;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sign-pos {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        /* DOCUMENT FOOTER */
        .doc-footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* MOBILE TOOLBAR STYLING */
        @media (max-width: 768px) {
            .pbj-toolbar-container {
                padding: 8px 10px !important;
            }
            .pbj-toolbar-wrapper {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }
            .pbj-btn-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr 1fr !important;
                gap: 6px !important;
                width: 100% !important;
                box-sizing: border-box;
            }
            .pbj-btn-grid .btn {
                width: 100% !important;
                padding: 6px 4px !important;
                font-size: 11px !important;
                white-space: nowrap !important;
                justify-content: center !important;
                display: flex !important;
                align-items: center !important;
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
                padding: 8mm 10mm !important;
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
    <div class="no-print bg-dark text-white border-bottom shadow-lg mb-2 pbj-toolbar-container" style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;">
        <div class="container-fluid px-2 px-md-4 py-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 pbj-toolbar-wrapper">
                
                {{-- TITLE HEADER --}}
                <div class="d-flex align-items-center justify-content-between w-100 w-md-auto">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="bi bi-box-seam fs-6"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold lh-1 text-truncate" style="font-size: 13px;">Pratinjau Penerimaan Barang Jadi</div>
                            <small class="text-info font-monospace text-truncate d-block" style="font-size: 11px;">{{ $penerimaan->nomor_penerimaan }}</small>
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-info d-md-none border border-info border-opacity-25 fs-8 flex-shrink-0 ms-2">A4 Fit</span>
                </div>

                {{-- SWITCH TOGGLES PILL BOX --}}
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="form-check form-switch mb-0 ps-4">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="chkShowTtd" checked onchange="togglePdfSection('sign-container', this.checked)">
                        <label class="form-check-label small text-white cursor-pointer" for="chkShowTtd"><i class="bi bi-vector-pen me-1 text-info"></i>Area TTD</label>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="pbj-btn-grid d-flex align-items-center gap-2 w-100 w-md-auto">
                    <button onclick="downloadPdfScreenshot()" class="btn btn-outline-info btn-sm px-2 px-md-3 rounded-pill fw-bold text-white shadow-xs">
                        <i class="bi bi-camera me-1"></i> Gambar
                    </button>
                    <button onclick="window.print()" class="btn btn-primary btn-sm px-2 px-md-3 rounded-pill fw-bold shadow-xs">
                        <i class="bi bi-printer me-1"></i> Cetak/PDF
                    </button>
                    <button onclick="window.close()" class="btn btn-outline-light btn-sm px-2 px-md-3 rounded-pill opacity-75">
                        <i class="bi bi-x-lg me-1 d-md-none"></i> Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- SCALABLE VIEWPORT CONTAINER FOR PERFECT MOBILE FIT --}}
    <div class="a4-scalable-viewport">
        <div class="print-document-area" id="printArea">
            
            {{-- KOP SURAT / HEADER RESMI DINAMIS (PT vs CABANG) - TANPA LOGO & TANPA ALAMAT/KONTAK --}}
            <div class="header-container">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 58%; vertical-align: middle;">
                            <div class="company-name">{{ $entityTitle }}</div>
                            <div class="company-sub">Manufaktur & Produksi Industri Garmen</div>
                        </td>
                        <td style="width: 42%; vertical-align: middle;">
                            <div class="doc-badge-box">
                                <div class="doc-badge-title">Bukti Penerimaan Barang Jadi</div>
                                <div class="doc-badge-num">{{ $penerimaan->nomor_penerimaan }}</div>
                                <div class="doc-badge-date">
                                    Tanggal Terbit: <b>{{ $penerimaan->tanggal_penerimaan ? $penerimaan->tanggal_penerimaan->translatedFormat('d F Y') : date('d F Y') }}</b>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- KARTU INFORMASI SPK & GUDANG TUJUAN --}}
            <div class="info-card">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nomor SPK Produksi</td>
                        <td class="info-val">
                            : <span class="info-val-primary">{{ $spkObj ? $spkObj->nomor_spk : '-' }}</span>
                        </td>
                        <td class="info-label">Gudang Tujuan</td>
                        <td class="info-val">
                            : <span class="fw-bold">[{{ $penerimaan->gudang->kode_gudang ?? '-' }}] {{ $penerimaan->gudang->nama_gudang ?? '-' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Target Qty Rencana</td>
                        <td class="info-val">
                            : <span class="font-mono fw-bold">{{ number_format($spkObj ? $spkObj->jumlah_rencana : 0, 0, ',', '.') }} {{ $satName }}</span>
                        </td>
                        <td class="info-label">Nomor / Lokasi Rak</td>
                        <td class="info-val">
                            : <span class="font-mono">{{ $penerimaan->lokasiRak ? '[' . $penerimaan->lokasiRak->kode_rak . '] ' . $penerimaan->lokasiRak->nama_rak : '-' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Status SPK</td>
                        <td class="info-val">
                            : @if(($spkObj->status ?? '') === 'Completed')
                                <span class="badge-pill-custom badge-status-completed">✓ Completed</span>
                            @else
                                <span class="badge-pill-custom badge-status-progress">{{ $spkObj->status ?? 'In Progress' }}</span>
                            @endif
                        </td>
                        <td class="info-label">Petugas Penerima</td>
                        <td class="info-val">
                            : <span>{{ $penerimaName }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Batch / Lot</td>
                        <td class="info-val">
                            : @if($penerimaan->nomor_batch)
                                <span class="badge-pill-custom" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-family:'JetBrains Mono',monospace;">
                                    🏷️ {{ $penerimaan->nomor_batch }}
                                </span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="info-label">QC / Penyerah</td>
                        <td class="info-val">
                            : <span>{{ $penyerahName }}</span>
                        </td>
                    </tr>
                    @if($penerimaan->tanggal_produksi || $penerimaan->tanggal_kedaluwarsa)
                    <tr>
                        <td class="info-label">Tgl Produksi / Exp</td>
                        <td class="info-val" colspan="3">
                            : <span class="font-mono fw-bold">{{ $penerimaan->tanggal_produksi ? $penerimaan->tanggal_produksi->format('d/m/Y') : '-' }}</span> 
                            <span class="text-muted mx-1">/</span> 
                            <span class="font-mono fw-bold text-danger">{{ $penerimaan->tanggal_kedaluwarsa ? $penerimaan->tanggal_kedaluwarsa->format('d/m/Y') : '-' }}</span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- TABEL RINCIAN HASIL PENERIMAAN BARANG JADI (OPERASIONAL PRODUKSI) --}}
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">NO</th>
                        <th>KODE & NAMA BARANG JADI</th>
                        <th style="width: 100px;">SATUAN</th>
                        <th style="width: 150px;">HASIL BAIK (GOOD)</th>
                        <th style="width: 130px;">HASIL REJECT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center font-mono">1</td>
                        <td>
                            <div class="fw-bold" style="color: #0f172a;">
                                [{{ $barangJadi ? $barangJadi->kode_barang : '-' }}] {{ $barangJadi ? $barangJadi->nama_barang : '-' }}
                            </div>
                            <small class="text-muted" style="font-size: 7.5pt;">
                                Kategori: {{ $barangJadi && $barangJadi->kategori ? $barangJadi->kategori->nama_kategori : 'Barang Jadi Produksi' }}
                            </small>
                        </td>
                        <td class="text-center font-mono fw-semibold">{{ $satName }}</td>
                        <td class="text-center font-mono fw-bold" style="color: #16a34a; font-size: 9.5pt;">
                            {{ number_format($penerimaan->jumlah_hasil_baik, 0, ',', '.') }} {{ $satName }}
                        </td>
                        <td class="text-center font-mono" style="color: #dc2626; font-weight: 600;">
                            {{ number_format($penerimaan->jumlah_reject, 0, ',', '.') }} {{ $satName }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end pe-3 fw-bold" style="color: #1e3a8a;">TOTAL HASIL:</td>
                        <td class="text-center font-mono fw-bold" style="color: #16a34a; font-size: 9.5pt;">
                            {{ number_format($penerimaan->jumlah_hasil_baik, 0, ',', '.') }} {{ $satName }}
                        </td>
                        <td class="text-center font-mono fw-bold" style="color: #dc2626;">
                            {{ number_format($penerimaan->jumlah_reject, 0, ',', '.') }} {{ $satName }}
                        </td>
                    </tr>
                </tfoot>
            </table>



            {{-- CATATAN TRANSAKSI --}}
            @if($penerimaan->catatan)
                <div class="catatan-box">
                    <div class="catatan-title"><i class="bi bi-info-circle-fill me-1"></i> Catatan Transaksi:</div>
                    <div>{{ $penerimaan->catatan }}</div>
                </div>
            @endif

            @php
                // Helper otomatis cari tanda tangan aktif
                $getSig = function($person) {
                    if (!$person) return null;
                    if ($person instanceof \App\Models\User && $person->activeSignature) {
                        return $person->activeSignature->signature_base64 ?: $person->activeSignature->signature_url;
                    }
                    if ($person instanceof \App\Models\Karyawan) {
                        if ($person->user && $person->user->activeSignature) {
                            return $person->user->activeSignature->signature_base64 ?: $person->user->activeSignature->signature_url;
                        }
                        $u = \App\Models\User::where('karyawan_id', $person->id)->with('activeSignature')->first();
                        if ($u && $u->activeSignature) {
                            return $u->activeSignature->signature_base64 ?: $u->activeSignature->signature_url;
                        }
                    }
                    return null;
                };

                $penyerahSig = $getSig($penerimaan->penyerah);
                $penerimaSig = $getSig($penerimaan->penerima);
                $creatorSig  = $getSig($penerimaan->creator);

                // Jabatan Dinamis dari Master Jabatan
                $penyerahPos = $penerimaan->penyerah?->position?->position_name ?: ($penerimaan->penyerah?->department?->department_name ? 'QC / Line ' . $penerimaan->penyerah->department->department_name : 'Operator QC / Line Produksi');
                $penerimaPos = $penerimaan->penerima?->position?->position_name ?: ($penerimaan->penerima?->department?->department_name ? 'Petugas ' . $penerimaan->penerima->department->department_name : 'Petugas Gudang Barang Jadi');
                $manajerPos  = 'Manajer Produksi / Pabrik';
            @endphp

            {{-- 3 BLOK TANDA TANGAN RESMI - RATA & SEJAJAR --}}
            <div class="sign-container">
                <table class="sign-table">
                    <tr>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true">Diserahkan Oleh (QC / Line Operator)</div>
                                <div class="box-ttd-img">
                                    @if($penyerahSig)
                                        <img src="{{ $penyerahSig }}" alt="TTD Penyerah">
                                    @endif
                                </div>
                                <div class="sign-bottom">
                                    <div class="sign-name editable-field" contenteditable="true">( {{ $penyerahName }} )</div>
                                    <div class="sign-pos editable-field" contenteditable="true">{{ $penyerahPos }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true">Diterima Oleh (Petugas Gudang)</div>
                                <div class="box-ttd-img">
                                    @if($penerimaSig)
                                        <img src="{{ $penerimaSig }}" alt="TTD Penerima">
                                    @endif
                                </div>
                                <div class="sign-bottom">
                                    <div class="sign-name editable-field" contenteditable="true">( {{ $penerimaName }} )</div>
                                    <div class="sign-pos editable-field" contenteditable="true">{{ $penerimaPos }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="sign-card">
                                <div class="sign-title editable-field" contenteditable="true">Mengetahui (Manajer Produksi)</div>
                                <div class="box-ttd-img">
                                    {{-- Kosong jika belum ada tanda tangan digital --}}
                                </div>
                                <div class="sign-bottom">
                                    <div class="sign-name editable-field" contenteditable="true">( ........................................ )</div>
                                    <div class="sign-pos editable-field" contenteditable="true">{{ $manajerPos }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- DOKUMEN FOOTER DIHAPUS PER PERMINTAAN USER --}}

        </div>
    </div>

    {{-- SCRIPT TOGGLES & SCREENSHOT --}}
    <script>
        function togglePdfSection(className, isVisible) {
            const elements = document.getElementsByClassName(className);
            for (let el of elements) {
                el.style.display = isVisible ? 'block' : 'none';
            }
        }

        function downloadPdfScreenshot() {
            const area = document.getElementById('printArea');
            const originalTransform = area.style.transform;
            area.style.transform = 'none';

            html2canvas(area, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                area.style.transform = originalTransform;
                const link = document.createElement('a');
                link.download = 'PBJ_{{ str_replace(["/", "\\"], "_", $penerimaan->nomor_penerimaan) }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                area.style.transform = originalTransform;
                alert('Gagal mengambil gambar dokumen.');
            });
        }
    </script>
</body>
</html>
