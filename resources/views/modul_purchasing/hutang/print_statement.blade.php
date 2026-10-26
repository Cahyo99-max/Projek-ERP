<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Kartu Utang Usaha – {{ $supplier->nama_supplier }} – {{ $company->nama_perusahaan ?? 'ERP' }}</title>
    
    @php
        $appLogoSetting = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', 'app_logo')->value('value');
        $officialAppLogo = (!empty($appLogoSetting) && file_exists(public_path('storage/' . $appLogoSetting)))
            ? asset('storage/' . $appLogoSetting)
            : (file_exists(public_path('storage/gambar/logo_1786070712.png')) ? asset('storage/gambar/logo_1786070712.png') : ($appLogo ?? asset('gambar/logo.png')));
        $logoMtime = file_exists(public_path('storage/' . ($appLogoSetting ?: 'gambar/logo_1786070712.png')))
            ? filemtime(public_path('storage/' . ($appLogoSetting ?: 'gambar/logo_1786070712.png')))
            : time();
    @endphp
    <!-- Favicon Logo Aplikasi -->
    <link rel="shortcut icon" href="{{ $officialAppLogo }}?v={{ $logoMtime }}" type="image/x-icon">
    <link rel="icon" href="{{ $officialAppLogo }}?v={{ $logoMtime }}" type="image/png">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- html2canvas for High-Res Image Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root {
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --brand-navy: #1e3a8a;
            --brand-blue: #2563eb;
            --brand-blue-soft: #eff6ff;
            --accent-green: #059669;
            --accent-green-soft: #ecfdf5;
            --accent-red: #dc2626;
            --accent-red-soft: #fef2f2;
            --accent-amber: #d97706;
            --accent-amber-soft: #fffbeb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #0f172a;
            color: var(--slate-800);
            font-size: 10.5px;
            line-height: 1.45;
            padding-bottom: 60px;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Screen Wrapper & Paper Layout ───────────────────────────── */
        .sheet-viewport {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 20px 10px;
            overflow-x: auto;
        }

        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 14mm 16mm 14mm 16mm;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.45);
            border-radius: 4px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        /* ── Floating Controls Toolbar ──────────────────────────────── */
        .floating-toolbar {
            position: sticky;
            top: 14px;
            z-index: 1000;
            max-width: 820px;
            margin: 10px auto;
            background: rgba(15, 23, 42, 0.90);
            backdrop-filter: blur(14px);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
        }

        .floating-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .floating-btn:hover {
            transform: translateY(-1px);
        }

        .btn-toolbar-back {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-toolbar-back:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .btn-toolbar-img {
            background: #f59e0b;
            color: #0f172a;
            font-weight: 700;
        }
        .btn-toolbar-img:hover {
            background: #d97706;
            color: #ffffff;
        }

        .btn-toolbar-print {
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
        }
        .btn-toolbar-print:hover {
            background: #1d4ed8;
        }

        /* ── Header Kop Surat ───────────────────────────────────────── */
        .statement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--slate-900);
            margin-bottom: 14px;
        }

        .company-brand-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            max-width: 58%;
        }

        .company-logo {
            max-height: 48px;
            max-width: 130px;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--brand-navy), var(--brand-blue));
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -0.5px;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
            flex-shrink: 0;
        }

        .company-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.2px;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .company-meta {
            font-size: 9px;
            color: var(--slate-500);
            line-height: 1.35;
            margin-top: 2px;
        }

        .doc-identity {
            text-align: right;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--brand-navy);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-subtitle {
            font-size: 8.5px;
            font-weight: 600;
            color: var(--slate-500);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .doc-meta-badge {
            display: inline-block;
            background: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 600;
            margin-top: 3px;
        }

        /* ── Info Grid (Vendor & Meta) ───────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .info-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 9px 12px;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--brand-navy);
            border-bottom: 1px solid var(--slate-200);
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 9px;
            line-height: 1.35;
        }
        .info-table td.label {
            width: 95px;
            color: var(--slate-500);
            font-weight: 500;
        }
        .info-table td.value {
            color: var(--slate-800);
            font-weight: 600;
        }

        /* ── Summary KPI Banner ─────────────────────────────────────── */
        .kpi-banner {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-left: 3.5px solid var(--slate-400);
            border-radius: 5px;
            padding: 7px 10px;
            text-align: right;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }
        .kpi-card.kpi-primary {
            border-left-color: var(--brand-blue);
            background: #fafcff;
        }
        .kpi-card.kpi-success {
            border-left-color: var(--accent-green);
            background: #fbfdfc;
        }
        .kpi-card.kpi-danger {
            border-left-color: var(--accent-red);
            background: #fffafa;
        }

        .kpi-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--slate-500);
            margin-bottom: 2px;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.2;
        }
        .kpi-card.kpi-success .kpi-val {
            color: var(--accent-green);
        }
        .kpi-card.kpi-danger .kpi-val {
            color: var(--accent-red);
        }
        .kpi-sub {
            font-size: 8px;
            color: var(--slate-500);
            margin-top: 1px;
        }

        /* ── Transaction Details Table ──────────────────────────────── */
        .table-wrap {
            margin-bottom: 14px;
            border: 1px solid var(--slate-200);
            border-radius: 5px;
            overflow: hidden;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .statement-table thead th {
            background: var(--slate-100);
            color: var(--slate-700);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 8px;
            padding: 6px 7px;
            border-bottom: 1.5px solid var(--slate-300);
            border-right: 1px solid var(--slate-200);
            text-align: left;
            vertical-align: middle;
        }
        .statement-table thead th:last-child {
            border-right: none;
        }

        .statement-table tbody td {
            padding: 5.5px 7px;
            border-bottom: 1px solid var(--slate-200);
            border-right: 1px solid var(--slate-200);
            vertical-align: middle;
            color: var(--slate-800);
        }
        .statement-table tbody td:last-child {
            border-right: none;
        }

        .statement-table tbody tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .statement-table tfoot td {
            padding: 6px 7px;
            background: var(--slate-50);
            border-top: 2px solid var(--slate-800);
            border-bottom: 2px solid var(--slate-300);
            border-right: 1px solid var(--slate-200);
            font-weight: 700;
            vertical-align: middle;
        }
        .statement-table tfoot td:last-child {
            border-right: none;
        }

        /* ── Aging Grid ─────────────────────────────────────────────── */
        .aging-box {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 12px;
        }

        .aging-title {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--brand-navy);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .aging-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
        }

        .aging-cell {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 4px;
            padding: 5px 7px;
            text-align: right;
        }
        .aging-cell-lbl {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .aging-cell-amt {
            font-size: 9.5px;
            font-weight: 700;
            line-height: 1.2;
        }

        /* ── Company Bank Notice ────────────────────────────────────── */
        .bank-notice {
            background: #f8fafc;
            border: 1px dashed var(--slate-300);
            border-radius: 5px;
            padding: 6px 10px;
            font-size: 8px;
            color: var(--slate-600);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        /* ── Signatures ─────────────────────────────────────────────── */
        .signature-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .sig-item {
            text-align: center;
        }

        .sig-role {
            font-size: 8.5px;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 45px;
        }

        .sig-name {
            border-top: 1px solid var(--slate-700);
            margin: 0 auto;
            width: 80%;
            padding-top: 3px;
            font-size: 8.5px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .sig-desc {
            font-size: 7.5px;
            color: var(--slate-500);
        }

        /* ── Edit Mode Styling ──────────────────────────────────────── */
        .btn-toolbar-edit {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-toolbar-edit:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }
        .btn-toolbar-edit.active {
            background: #f59e0b !important;
            color: #0f172a !important;
            border-color: #f59e0b !important;
            font-weight: 700;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }

        .edit-notice-bar {
            position: fixed;
            top: 65px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            background: #f59e0b;
            color: #78350f;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 18px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            display: none;
            align-items: center;
            gap: 8px;
            animation: fadeInDown 0.2s ease;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -10px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        .editable-field[contenteditable="true"] {
            outline: none;
            transition: all 0.15s ease;
            cursor: text;
            padding: 1px 4px;
            border-radius: 3px;
            background: rgba(239, 246, 255, 0.85);
            box-shadow: 0 0 0 1px #93c5fd;
        }
        .editable-field[contenteditable="true"]:hover {
            background: #dbeafe;
            box-shadow: 0 0 0 1.5px #3b82f6;
        }
        .editable-field[contenteditable="true"]:focus {
            background: #ffffff;
            box-shadow: 0 0 0 2px #2563eb;
        }

        /* ── Print Media Optimization ───────────────────────────────── */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .floating-toolbar, .edit-notice-bar {
                display: none !important;
            }
            .sheet-viewport {
                padding: 0 !important;
                display: block !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .editable-field {
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
            .table-wrap {
                border-color: #cbd5e1 !important;
            }
            .statement-table thead th {
                background-color: #f1f5f9 !important;
            }
        }
    </style>
</head>
<body>

    @php
        $fmt = fn($val) => number_format((float)$val, 2, ',', '.');
        $totalInvoiced = $invoices->sum(fn($i) => (float)$i->grand_total * (float)($i->kurs ?: 1));
        $totalPaid     = $invoices->sum(fn($i) => (float)$i->paid_amount * (float)($i->kurs ?: 1));
        $totalSisa     = $invoices->sum(fn($i) => (float)$i->remaining_balance * (float)($i->kurs ?: 1));
        
        $hasValas      = $invoices->contains(fn($i) => $i->currency && !in_array(strtoupper($i->currency->kode_currency), ['IDR', 'RP']) && (float)($i->kurs ?: 1) > 1);
        $valasInvoiced = $invoices->filter(fn($i) => $i->currency && !in_array(strtoupper($i->currency->kode_currency), ['IDR', 'RP']) && (float)($i->kurs ?: 1) > 1)->sum('grand_total');
        $valasPaid     = $invoices->filter(fn($i) => $i->currency && !in_array(strtoupper($i->currency->kode_currency), ['IDR', 'RP']) && (float)($i->kurs ?: 1) > 1)->sum('paid_amount');
        $valasSisa     = $invoices->filter(fn($i) => $i->currency && !in_array(strtoupper($i->currency->kode_currency), ['IDR', 'RP']) && (float)($i->kurs ?: 1) > 1)->sum('remaining_balance');
        $valasSymbol   = $invoices->first(fn($i) => $i->currency && !in_array(strtoupper($i->currency->kode_currency), ['IDR', 'RP']))?->currency?->simbol ?: '$';

        // Sanitasi alamat dan kontak perusahaan/cabang agar tidak tampil '-' raw
        $companyAddr = trim($printScope === 'branch' && $branch ? ($branch->alamat ?? '') : ($company->alamat ?? ''));
        $companyTelp = trim($printScope === 'branch' && $branch ? ($branch->telepon ?? '') : ($company->telepon ?? ''));
        $companyEmail = trim($printScope === 'branch' && $branch ? ($branch->email ?? '') : ($company->email ?? ''));
    @endphp

    <!-- Floating Action Toolbar (Screen Only) -->
    <div class="floating-toolbar">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('modul_purchasing.hutang.index') }}" class="floating-btn btn-toolbar-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="button" class="floating-btn btn-toolbar-edit" id="btnToggleEdit" onclick="toggleEditMode()">
                <i class="bi bi-pencil-square"></i> <span id="btnEditText">Edit Dokumen</span>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="floating-btn btn-toolbar-img" onclick="downloadImage()">
                <i class="bi bi-file-earmark-image"></i> Simpan Gambar
            </button>
            <button type="button" class="floating-btn btn-toolbar-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Dokumen (PDF)
            </button>
        </div>
    </div>

    <!-- Edit Mode Floating Banner -->
    <div class="edit-notice-bar" id="editModeBanner">
        <i class="bi bi-pencil-fill"></i>
        <span>Mode Edit Aktif: Klik langsung pada teks Informasi Rekanan atau Nama Tanda Tangan untuk mengubahnya.</span>
    </div>

    <!-- Paper Container Viewport -->
    <div class="sheet-viewport">
        <div class="paper-sheet" id="printableStatementCard">
            
            <div>
                <!-- Company Header / Kop Surat -->
                <div class="statement-header">
                    <div class="company-brand-wrap">
                        @if(!empty($activeLogo))
                            <img src="{{ $activeLogo }}" alt="Logo" class="company-logo">
                        @elseif(!empty($branch?->logo) && (file_exists(public_path('storage/' . $branch->logo)) || file_exists(public_path($branch->logo))))
                            <img src="{{ file_exists(public_path('storage/' . $branch->logo)) ? asset('storage/' . $branch->logo) : asset($branch->logo) }}" alt="Logo" class="company-logo">
                        @elseif(!empty($company?->logo) && (file_exists(public_path('storage/' . $company->logo)) || file_exists(public_path($company->logo))))
                            <img src="{{ file_exists(public_path('storage/' . $company->logo)) ? asset('storage/' . $company->logo) : asset($company->logo) }}" alt="Logo" class="company-logo">
                        @else
                            <div class="logo-placeholder">
                                {{ strtoupper(substr(($branch?->nama_cabang ?? $company->nama_perusahaan ?? 'CP'), 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="company-title editable-field" contenteditable="false">
                                @if($printScope === 'branch' && $branch)
                                    {{ stripos($branch->nama_cabang, 'cabang') !== false ? strtoupper($branch->nama_cabang) : 'CABANG ' . strtoupper($branch->nama_cabang) }}
                                @else
                                    {{ $company->nama_perusahaan ?? 'PT PUSAT' }}
                                @endif
                            </div>
                            <div class="company-meta editable-field" contenteditable="false">
                                @if($companyAddr !== '' && $companyAddr !== '-')
                                    {{ $companyAddr }}<br>
                                @endif
                                @php
                                    $contactBits = [];
                                    if ($companyTelp !== '' && $companyTelp !== '-') $contactBits[] = 'Telp: ' . $companyTelp;
                                    if ($companyEmail !== '' && $companyEmail !== '-') $contactBits[] = 'Email: ' . $companyEmail;
                                @endphp
                                @if(count($contactBits) > 0)
                                    {{ implode(' | ', $contactBits) }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="doc-identity">
                        <div class="doc-title">Kartu Utang Usaha</div>
                        <div class="doc-subtitle">Statement of Accounts Payable</div>
                        <div class="doc-meta-badge font-mono">
                            Per: <strong>{{ now()->translatedFormat('d F Y') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Supplier & Metadata Grid -->
                <div class="info-grid">
                    <!-- Supplier Profile -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <span><i class="bi bi-person-badge me-1"></i> Rekanan / Supplier</span>
                            <span class="font-mono text-muted">SUP-#{{ $supplier->kode_supplier ?: $supplier->id }}</span>
                        </div>
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama Rekanan</td>
                                <td class="value text-primary editable-field" contenteditable="false">{{ $supplier->nama_supplier }}</td>
                            </tr>
                            <tr>
                                <td class="label">Kontak / PIC</td>
                                <td class="value editable-field" contenteditable="false">{{ ($supplier->nama_kontak && $supplier->nama_kontak !== '-') ? $supplier->nama_kontak : '–' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Telepon / HP</td>
                                <td class="value font-mono editable-field" contenteditable="false">{{ ($supplier->telepon && $supplier->telepon !== '-') ? $supplier->telepon : '–' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Alamat</td>
                                <td class="value editable-field" contenteditable="false">{{ ($supplier->alamat && $supplier->alamat !== '-') ? $supplier->alamat : '–' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Statement Meta -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <span><i class="bi bi-file-earmark-text me-1"></i> Informasi Pembayaran</span>
                            <span>
                                @if($aging['total_utang'] <= 0.01)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 7.5px;">Lunas</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 7.5px;">Belum Lunas</span>
                                @endif
                            </span>
                        </div>
                        <table class="info-table">
                            <tr>
                                <td class="label">Term Bayar</td>
                                <td class="value editable-field" contenteditable="false">{{ $supplier->term_pembayaran ?: 'Net 30' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Rek. Supplier</td>
                                <td class="value font-mono editable-field" contenteditable="false">
                                    @php
                                        $stBank = $supplier->primaryBank ?? $supplier->banks->first();
                                    @endphp
                                    @if($stBank)
                                        {{ $stBank->nama_bank }} – {{ $stBank->nomor_rekening }}
                                        @if(!empty($stBank->atas_nama_rekening)) <br><span class="text-muted fw-normal" style="font-size: 8px;">(a.n {{ $stBank->atas_nama_rekening }})</span> @endif
                                    @else
                                        –
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Entitas Pembukuan</td>
                                <td class="value">
                                    @if($printScope === 'branch' && $branch)
                                        Cabang {{ $branch->nama_cabang }}
                                    @else
                                        PT Pusat ({{ $company->nama_perusahaan ?? 'Kantor Pusat' }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Dicetak Oleh</td>
                                <td class="value editable-field" contenteditable="false">{{ auth()->user()->name ?? 'Administrator' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- KPI Summary Strip -->
                <div class="kpi-banner">
                    <div class="kpi-card kpi-primary">
                        <div class="kpi-label">Total Ditagihkan (Faktur)</div>
                        @if($hasValas && $valasInvoiced > 0.01)
                            <div class="kpi-val font-mono">{{ $valasSymbol }} {{ number_format($valasInvoiced, 2, ',', '.') }}</div>
                            <div class="kpi-sub font-mono">≈ Rp {{ $fmt($totalInvoiced) }}</div>
                        @else
                            <div class="kpi-val font-mono">Rp {{ $fmt($totalInvoiced) }}</div>
                        @endif
                    </div>
                    <div class="kpi-card kpi-success">
                        <div class="kpi-label">Total Terbayar &amp; DP</div>
                        @if($hasValas && $valasPaid > 0.01)
                            <div class="kpi-val font-mono">{{ $valasSymbol }} {{ number_format($valasPaid, 2, ',', '.') }}</div>
                            <div class="kpi-sub font-mono">≈ Rp {{ $fmt($totalPaid) }}</div>
                        @else
                            <div class="kpi-val font-mono">Rp {{ $fmt($totalPaid) }}</div>
                        @endif
                    </div>
                    <div class="kpi-card kpi-danger">
                        <div class="kpi-label">Sisa Utang (Outstanding)</div>
                        @if($hasValas && $valasSisa > 0.01)
                            <div class="kpi-val font-mono">{{ $valasSymbol }} {{ number_format($valasSisa, 2, ',', '.') }}</div>
                            <div class="kpi-sub font-mono">≈ Rp {{ $fmt($totalSisa) }} (IDR)</div>
                        @else
                            <div class="kpi-val font-mono">Rp {{ $fmt($totalSisa) }}</div>
                        @endif
                    </div>
                </div>

                <!-- Transaction Details Table -->
                <div class="table-wrap">
                    <table class="statement-table">
                        <thead>
                            <tr>
                                <th style="width: 4%; text-align: center;">NO</th>
                                <th style="width: 17%;">NO. FAKTUR</th>
                                <th style="width: 15%;">REF. SUPPLIER</th>
                                <th style="width: 10%;">TANGGAL</th>
                                <th style="width: 10%;">JATUH TEMPO</th>
                                <th style="width: 16%; text-align: right;">NILAI FAKTUR</th>
                                <th style="width: 13%; text-align: right;">DIBAYAR / DP</th>
                                <th style="width: 15%; text-align: right;">SISA UTANG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $idx => $inv)
                            @php
                                $cur = $inv->currency;
                                $curCode = strtoupper($cur?->kode_currency ?? 'IDR');
                                $kurs = (float)($inv->kurs ?: 1);
                                $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']) && $kurs > 1;
                                $sym = $cur?->simbol ?? ($isForeign ? '$' : 'Rp');

                                $sisaInv = (float)$inv->remaining_balance;
                                $paidInv = (float)$inv->paid_amount;
                                $grandInv = (float)$inv->grand_total;

                                $sisaInvIdr = $sisaInv * $kurs;
                                $paidInvIdr = $paidInv * $kurs;
                                $grandInvIdr = $grandInv * $kurs;

                                $isOverdue = $sisaInv > 0.01 && $inv->tanggal_jatuh_tempo && \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay()->isPast();
                            @endphp
                            <tr>
                                <td style="text-align: center;" class="font-mono text-muted">{{ $idx + 1 }}</td>
                                <td class="font-mono fw-bold text-primary">
                                    {{ $inv->nomor_faktur }}
                                    @if($isForeign)
                                        <span style="font-size: 7.5px; padding: 1px 4px; background: #e0f2fe; color: #0369a1; border-radius: 3px; font-weight: normal; margin-left: 2px;">{{ $curCode }}</span>
                                    @endif
                                </td>
                                <td class="font-mono text-muted">{{ ($inv->nomor_faktur_supplier && $inv->nomor_faktur_supplier !== '-') ? $inv->nomor_faktur_supplier : '–' }}</td>
                                <td>{{ $inv->tanggal_faktur ? \Carbon\Carbon::parse($inv->tanggal_faktur)->format('d/m/Y') : '–' }}</td>
                                <td class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $inv->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') : '–' }}
                                </td>
                                <td style="text-align: right;" class="font-mono">
                                    @if($isForeign)
                                        <div class="fw-bold">{{ $sym }} {{ number_format($grandInv, 2, ',', '.') }}</div>
                                        <div class="text-muted" style="font-size: 7.5px;">(Kurs: {{ number_format($kurs, 0, ',', '.') }})</div>
                                    @else
                                        Rp {{ $fmt($grandInv) }}
                                    @endif
                                </td>
                                <td style="text-align: right;" class="font-mono {{ $paidInv > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                    @if($isForeign)
                                        <div class="{{ $paidInv > 0 ? 'fw-bold' : '' }}">{{ $sym }} {{ number_format($paidInv, 2, ',', '.') }}</div>
                                    @else
                                        Rp {{ $fmt($paidInv) }}
                                    @endif
                                </td>
                                <td style="text-align: right;" class="font-mono fw-bold {{ $sisaInv > 0.01 ? 'text-danger' : 'text-success' }}">
                                    @if($isForeign)
                                        <div>{{ $sym }} {{ number_format($sisaInv, 2, ',', '.') }}</div>
                                    @else
                                        Rp {{ $fmt($sisaInv) }}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada data faktur pembelian untuk supplier ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right; text-transform: uppercase; font-size: 8.5px; letter-spacing: 0.5px;">
                                    Total Akumulasi (IDR):
                                </td>
                                <td style="text-align: right;" class="font-mono text-dark">
                                    Rp {{ $fmt($totalInvoiced) }}
                                </td>
                                <td style="text-align: right;" class="font-mono text-success">
                                    Rp {{ $fmt($totalPaid) }}
                                </td>
                                <td style="text-align: right;" class="font-mono text-danger fw-bold">
                                    Rp {{ $fmt($totalSisa) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Bottom Section: Signatures -->
            <div>
                <div class="signature-strip">
                    <div class="sig-item">
                        <div class="sig-role editable-field" contenteditable="false">Dibuat Oleh (Finance/AP)</div>
                        <div class="sig-name editable-field" contenteditable="false">{{ auth()->user()->name ?? 'Petugas AP' }}</div>
                        <div class="sig-desc editable-field" contenteditable="false">Staff Keuangan &amp; Pembelian</div>
                    </div>
                    <div class="sig-item">
                        <div class="sig-role editable-field" contenteditable="false">Diketahui Oleh</div>
                        <div class="sig-name editable-field" contenteditable="false">Manager Keuangan</div>
                        <div class="sig-desc editable-field" contenteditable="false">Finance &amp; Accounting Mgr</div>
                    </div>
                    <div class="sig-item">
                        <div class="sig-role editable-field" contenteditable="false">Dikonfirmasi Rekanan / Supplier</div>
                        <div class="sig-name editable-field" contenteditable="false">{{ ($supplier->nama_kontak && $supplier->nama_kontak !== '-') ? $supplier->nama_kontak : $supplier->nama_supplier }}</div>
                        <div class="sig-desc editable-field" contenteditable="false">Tanda Tangan &amp; Cap Stempel</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Export Image High-Res & Inline Edit Mode -->
    <script>
    let isEditMode = false;
    function toggleEditMode() {
        isEditMode = !isEditMode;
        const btn = document.getElementById('btnToggleEdit');
        const btnText = document.getElementById('btnEditText');
        const banner = document.getElementById('editModeBanner');
        const editables = document.querySelectorAll('.editable-field');

        if (isEditMode) {
            btn.classList.add('active');
            btnText.textContent = 'Selesai Edit';
            banner.style.display = 'flex';
            editables.forEach(el => el.setAttribute('contenteditable', 'true'));
            if (editables.length > 0) editables[0].focus();
        } else {
            btn.classList.remove('active');
            btnText.textContent = 'Edit Dokumen';
            banner.style.display = 'none';
            editables.forEach(el => el.setAttribute('contenteditable', 'false'));
        }
    }

    function downloadImage() {
        const card = document.getElementById('printableStatementCard');
        const activeEl = document.activeElement;
        if (activeEl && activeEl.blur) activeEl.blur();

        const wasEditMode = isEditMode;
        if (wasEditMode) {
            document.querySelectorAll('.editable-field').forEach(el => el.setAttribute('contenteditable', 'false'));
        }

        html2canvas(card, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            if (wasEditMode) {
                document.querySelectorAll('.editable-field').forEach(el => el.setAttribute('contenteditable', 'true'));
            }
            const link = document.createElement('a');
            const supSlug = {!! json_encode(\Illuminate\Support\Str::slug($supplier->nama_supplier ?? 'supplier')) !!};
            link.download = 'Kartu_Utang_' + supSlug + '_{{ date("Ymd_His") }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }
    </script>
</body>
</html>
