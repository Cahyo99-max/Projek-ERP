<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Sales Order – {{ $order->nomor_so }} – {{ $order->customer?->nama_perusahaan ?: ($order->customer?->nama_customer ?: ($order->nama_customer_walkin ?: 'Pelanggan')) }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- html2canvas for High-Definition Screenshot Generation -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #090d16;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            padding-bottom: 70px;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Outer Container for Fluid Scrolling & Zoom on Mobile */
        .sheet-viewport-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px;
            display: flex;
            justify-content: center;
        }

        /* Floating Controls Toolbar (Sleek Compact Glassmorphism) */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.92);
            padding: 6px 12px;
            border-radius: 50px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
        }

        .toolbar-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 15px;
            border-radius: 50px;
            font-size: 11.5px; font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            border: none;
            white-space: nowrap;
        }

        .btn-edit-mode {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.22);
        }
        .btn-edit-mode:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
        .btn-edit-mode.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border-color: #f59e0b !important;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.5) !important;
        }

        .btn-print {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.5);
        }
        .btn-print:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.7); 
        }

        .btn-screenshot {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.45);
        }
        .btn-screenshot:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.65);
        }

        .btn-close-preview {
            background: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-close-preview:hover { 
            background: rgba(255, 255, 255, 0.18); 
            transform: translateY(-1px);
        }

        /* Floating Edit Mode Banner */
        .edit-mode-banner {
            position: fixed;
            top: 70px; left: 50%;
            transform: translateX(-50%);
            z-index: 998;
            background: rgba(245, 158, 11, 0.95);
            color: #78350f;
            font-weight: 700;
            font-size: 11px;
            padding: 7px 20px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.35);
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: calc(100vw - 32px);
            text-align: center;
            animation: fadeInDown 0.25s ease;
            backdrop-filter: blur(10px);
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -12px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        /* Inline Editable State */
        .editable-text {
            border-radius: 3px;
        }

        [contenteditable="true"] {
            outline: none;
            transition: all 0.15s ease-in-out;
            cursor: text;
        }

        [contenteditable="true"]:hover {
            background-color: rgba(245, 158, 11, 0.12) !important;
            box-shadow: 0 0 0 1.5px rgba(245, 158, 11, 0.5) !important;
        }

        [contenteditable="true"]:focus {
            background-color: #ffffff !important;
            box-shadow: 0 0 0 2px #f59e0b, 0 2px 8px rgba(245, 158, 11, 0.25) !important;
        }

        /* Standard A4 Paper Sheet (Consistent PC & Mobile A4 Aspect Ratio) */
        .paper-sheet {
            width: 210mm;
            min-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 16mm 18mm;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(37, 99, 235, 0.15);
            border-radius: 6px;
            position: relative;
            flex-shrink: 0;
        }

        /* Top Accent Bar (Royal Blue Gradient) */
        .top-accent-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #60a5fa 100%);
        }

        /* Letterhead / Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 14px;
            margin-bottom: 16px;
            position: relative;
        }
        .kop-surat::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0; right: 0;
            height: 1px;
            background: #93c5fd;
        }

        .kop-logo {
            max-height: 72px;
            max-width: 165px;
            object-fit: contain;
        }

        .kop-info {
            text-align: right;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
        }
        .kop-info strong {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            display: block;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        /* Document Title */
        .doc-title-box {
            text-align: center;
            margin-bottom: 18px;
            padding-top: 4px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1e3a8a;
            margin-bottom: 2px;
        }

        .doc-number {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            padding: 3px 14px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
        }

        /* Metadata Columns (Info Grid) */
        .info-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
            margin-bottom: 18px;
            font-size: 10.5px;
        }

        .info-box {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.03);
        }

        .info-box-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #bfdbfe;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3.5px;
        }

        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748b; font-size: 10px; font-weight: 500; }
        .info-val { font-weight: 600; color: #0f172a; text-align: right; font-size: 10.5px; }

        /* Items Table */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .print-table thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.5px;
            padding: 9px 10px;
            border: none;
        }

        .print-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .print-table td:last-child { border-right: none; }

        .print-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .item-code-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 8.5px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* Summary Wrapper */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 14px;
            margin-bottom: 20px;
        }

        .notes-box {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .summary-card {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            margin-bottom: 4px;
        }

        .summary-row .label {
            color: #64748b;
            font-weight: 500;
        }

        .summary-row .amount {
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            color: #0f172a;
        }

        .summary-row.grand-total-row {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 7px 10px;
            border-radius: 6px;
            margin-top: 6px;
            margin-bottom: 4px;
            box-shadow: 0 3px 8px rgba(30, 58, 138, 0.25);
        }

        .summary-row.grand-total-row span {
            color: #ffffff !important;
        }

        .summary-row.dp-row {
            padding: 3px 10px;
            font-size: 10.5px;
            color: #2563eb;
            font-weight: 600;
        }

        .summary-row.sisa-row {
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 6px 10px;
            border-radius: 6px;
            margin-top: 4px;
            font-size: 11.5px;
            font-weight: 800;
            color: #b45309;
        }

        .summary-row.sisa-row span {
            color: #b45309 !important;
        }

        /* Signatures Area */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
            font-size: 10.5px;
            margin-top: 18px;
        }

        .sig-block {
            border: 1px dashed #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
        }

        .sig-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 50px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-line {
            display: inline-block;
            min-width: 180px;
            border-top: 1.5px solid #1e3a8a;
            padding-top: 4px;
            font-weight: 700;
            color: #0f172a;
        }

        .sig-role {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Interactive In-Place Edit Styling */
        [contenteditable="true"] {
            outline: none;
            transition: all 0.15s ease;
            cursor: text;
        }
        [contenteditable="true"]:hover {
            background: #eff6ff;
            box-shadow: 0 0 0 1px #93c5fd;
            border-radius: 3px;
        }
        [contenteditable="true"]:focus {
            background: #ffffff;
            box-shadow: 0 0 0 1.5px #2563eb;
            border-radius: 3px;
        }

        /* ===== MOBILE CONTROLS & FLUID WRAPPER ===== */
        @media screen and (max-width: 768px) {
            body {
                padding-bottom: 95px;
                background: #090d16;
            }

            /* Floating toolbar menempel rapi di bawah layar HP */
            .toolbar {
                top: auto;
                bottom: 12px;
                left: 12px;
                right: 12px;
                justify-content: space-between;
                gap: 6px;
                padding: 8px 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(37, 99, 235, 0.4);
            }

            .toolbar-btn {
                padding: 8px 10px;
                font-size: 11px;
                flex: 1;
                justify-content: center;
                gap: 5px;
            }

            .sheet-viewport-wrapper {
                padding: 8px 4px;
                justify-content: flex-start;
            }

            .paper-sheet {
                margin: 10px 0;
            }
        }

        /* ===== PRINT MEDIA STYLES (STANDAR A4 RESMI) ===== */
        @page {
            size: A4 portrait;
            margin: 0mm; /* Menghilangkan URL dan header/footer bawaan browser secara tuntas */
        }

        @media print {
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .toolbar, .edit-mode-banner { display: none !important; }

            [contenteditable="true"]:hover, [contenteditable="true"]:focus {
                background: transparent !important;
                box-shadow: none !important;
            }

            .sheet-viewport-wrapper {
                padding: 0 !important;
                display: block !important;
            }

            .paper-sheet {
                width: 100% !important;
                min-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 12mm 15mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .print-table {
                min-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Edit Mode Banner -->
    <div id="editModeBanner" class="edit-mode-banner" style="display: none;">
        <i class="bi bi-pencil-fill"></i> Mode Edit Aktif — Klik langsung teks pada dokumen untuk mengubah isi sebelum cetak.
    </div>

    <!-- Floating Toolbar -->
    <div class="toolbar">
        <button type="button" class="toolbar-btn btn-edit-mode" id="btnToggleEditMode" onclick="toggleEditMode()" title="Aktifkan / nonaktifkan edit teks langsung">
            <i class="bi bi-pencil-square"></i> <span id="btnEditText">Edit Teks</span>
        </button>
        <button type="button" class="toolbar-btn btn-print" onclick="prepareAndPrint()" title="Cetak Sales Order (Print)">
            <i class="bi bi-printer-fill"></i> Cetak
        </button>
        <button type="button" class="toolbar-btn btn-screenshot" id="btn_screenshot" onclick="takeScreenshot()" title="Simpan Sales Order sebagai Gambar">
            <i class="bi bi-camera-fill"></i> Gambar
        </button>
        <button type="button" class="toolbar-btn btn-close-preview" onclick="window.close()" title="Tutup Halaman">
            <i class="bi bi-x-lg"></i> Tutup
        </button>
    </div>

    <!-- Outer Viewport Wrapper for Smooth Scrolling / Pan / Zoom -->
    <div class="sheet-viewport-wrapper">
        <!-- Paper Sheet Container -->
        <div class="paper-sheet" id="invoice_paper_sheet">
            <div class="top-accent-bar"></div>

            @php
                $isOrderBranch = !empty($order->branch_id);
                if ($isOrderBranch) {
                    $orderBranch = $order->branch ?? ($branch ?? \App\Models\Branch::with('company')->find($order->branch_id));
                    $compNama = !empty($orderBranch?->nama_cabang) ? $orderBranch->nama_cabang : ($entityName ?: 'Cabang');
                    $compParent = $orderBranch?->company?->nama_perusahaan ?: ($parentName ?: '');
                } else {
                    $orderCompany = $order->company ?? ($company ?? \App\Models\Company::find($order->company_id) ?? \App\Models\Company::first());
                    $compNama = !empty($orderCompany?->nama_perusahaan) ? $orderCompany->nama_perusahaan : ($entityName ?: 'PT PERSADA AMAN SENTOSA');
                    $compParent = '';
                }
                $logoUrl = !empty($activeLogo) ? $activeLogo : ($companyLogo ?? asset('gambar/logo.png'));
            @endphp

            <!-- Letterhead / Kop Surat -->
            <div class="kop-surat">
                <div>
                    @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo Perusahaan" class="kop-logo" onerror="this.src='{{ asset('gambar/logo.png') }}';">
                    @else
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-building fs-1 text-primary"></i>
                    </div>
                    @endif
                </div>
                <div class="kop-info" contenteditable="false" title="Klik untuk mengedit">
                    <strong>{{ $compNama }}</strong>
                </div>
            </div>

            <!-- Title -->
            <div class="doc-title-box">
                <h1 class="doc-title" contenteditable="false" title="Klik untuk mengedit judul">SALES ORDER</h1>
                <div class="doc-number" contenteditable="false" title="Klik untuk mengedit nomor pesanan">
                    No. SO: {{ $order->nomor_so }}
                </div>
                @if($order->quotation || $order->tender)
                <div style="margin-top: 4px; font-size: 9.5px; color: #475569;" contenteditable="false" title="Klik untuk mengedit referensi">
                    @if($order->quotation)
                    <span>Ref. Penawaran: <strong>{{ $order->quotation->nomor_quotation }}</strong></span>
                    @endif
                    @if($order->quotation && $order->tender)
                    <span> | </span>
                    @endif
                    @if($order->tender)
                    <span>Ref. Tender: <strong>{{ $order->tender->nomor_kontrak ?: $order->tender->nama_tender }}</strong></span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Meta Information Grid -->
            <div class="info-grid">
                <div class="info-box">
                    <div class="info-box-title" contenteditable="false" title="Klik untuk mengedit judul informasi">
                        <i class="bi bi-person-fill"></i> Informasi Pembeli &amp; Pengiriman
                    </div>
                    @if($order->customer?->nama_perusahaan)
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Instansi / PT:</span>
                        <span class="info-val" style="color: #1e3a8a; font-weight: 700;" contenteditable="false" title="Klik untuk mengedit">{{ $order->customer->nama_perusahaan }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Nama Pemesan:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->customer->nama_customer ?: ($order->nama_customer_walkin ?: '-') }}</span>
                    </div>
                    @else
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Nama Pemesan:</span>
                        <span class="info-val" style="font-weight: 700;" contenteditable="false" title="Klik untuk mengedit">{{ $order->customer?->nama_customer ?: ($order->nama_customer_walkin ?: 'Pelanggan Umum') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">No. Telepon:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->telepon_customer_walkin ?: ($order->customer?->telepon ?: ($order->customer?->hp ?: '-')) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Alamat Kirim:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->alamat_pengiriman ?: ($order->customer?->defaultAddress?->alamat_lengkap ?: ($order->customer?->alamat ?: 'Diambil di Tempat')) }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title" contenteditable="false" title="Klik untuk mengedit judul transaksi">
                        <i class="bi bi-receipt"></i> Rincian Transaksi
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Nomor Dokumen:</span>
                        <span class="info-val font-mono" style="color: #2563eb;" contenteditable="false" title="Klik untuk mengedit">{{ $order->nomor_so }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Tanggal Order:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->tanggal_so ? \Carbon\Carbon::parse($order->tanggal_so)->translatedFormat('d F Y') : date('d F Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Metode Bayar:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->paymentMethod?->nama_metode ?: 'Cash / Tunai' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Termin:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->paymentTerm?->nama_term ?: 'Langsung (Cash)' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label" contenteditable="false">Sales / PIC:</span>
                        <span class="info-val" contenteditable="false" title="Klik untuk mengedit">{{ $order->sales?->nama_lengkap ?: ($order->creator?->name ?: 'Marketing') }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="print-table">
                <thead>
                    <tr>
                        <th style="width: 32px;" class="text-center" contenteditable="false">No</th>
                        <th contenteditable="false">Nama Produk / Deskripsi Item</th>
                        <th style="width: 80px;" class="text-center" contenteditable="false">Qty</th>
                        <th style="width: 105px;" class="text-right" contenteditable="false">Harga Satuan</th>
                        <th style="width: 85px;" class="text-center" contenteditable="false">Diskon</th>
                        <th style="width: 110px;" class="text-right" contenteditable="false">Subtotal</th>
                        <th style="width: 80px;" class="text-center" contenteditable="false">Pajak (PPN)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->details as $index => $item)
                        @php
                            $namaBarang = $item->nama_barang_custom ?: ($item->barang?->nama_barang ?: 'Item');
                            $sku = $item->barang?->kode_barang ?: '-';
                            $satuan = $item->nama_satuan ?: ($item->barang?->satuan?->nama_satuan ?: 'Pcs');
                            $kategori = $item->kategori?->nama_kategori ?: ($item->barang?->kategori?->nama_kategori ?: '');
                        @endphp
                        <tr>
                            <td class="text-center font-mono" contenteditable="false">{{ $index + 1 }}</td>
                            <td contenteditable="false">
                                <div style="font-weight: 700; color: #0f172a;">{{ $namaBarang }}</div>
                                <span class="item-code-badge">{{ $sku }}</span>
                                @if($item->deskripsi_item)
                                    <div style="font-size: 9px; color: #475569; margin-top: 2px;">{{ $item->deskripsi_item }}</div>
                                @endif
                            </td>
                            <td class="text-center font-mono" style="font-weight: 600;" contenteditable="false">
                                {{ number_format($item->qty, 0, ',', '.') }} {{ $satuan }}
                            </td>
                            <td class="text-right font-mono" contenteditable="false">
                                Rp {{ number_format($item->harga_satuan, 2, ',', '.') }}
                            </td>
                            <td class="text-center font-mono" style="color: #dc2626;" contenteditable="false">
                                @if((float)$item->diskon_persen > 0)
                                    <span style="font-weight: 700; color: #dc2626;">{{ (float)$item->diskon_persen }}%</span>
                                @elseif((float)$item->diskon_nominal > 0)
                                    <span style="font-weight: 700; color: #dc2626;"> Rp {{ number_format($item->diskon_nominal, 2, ',', '.') }}</span>
                                @else
                                    <span style="color: #64748b;">-</span>
                                @endif
                            </td>
                            <td class="text-right font-mono" style="font-weight: 700; color: #0f172a;" contenteditable="false">
                                Rp {{ number_format($item->subtotal, 2, ',', '.') }}
                            </td>
                            <td class="text-center font-mono" contenteditable="false">
                                @if((float)$item->tarif_ppn > 0)
                                    <span style="font-weight: 700; color: #0284c7;">{{ (float)$item->tarif_ppn }}%</span>
                                @elseif((float)$item->nilai_ppn > 0 && (float)$item->subtotal > 0)
                                    <span style="font-weight: 700; color: #0284c7;">{{ round(((float)$item->nilai_ppn / (float)$item->subtotal) * 100, 1) }}%</span>
                                @else
                                    <span style="color: #64748b;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;" contenteditable="false">Tidak ada rincian item pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Summary & Totals -->
            @php
                $subtotal     = (float) $order->subtotal;
                $diskonNom    = (float) $order->diskon_nominal;
                $ppnNom       = (float) ($order->nilai_ppn > 0 ? $order->nilai_ppn : $order->pajak_nominal);
                $pphNom       = (float) $order->nilai_pph;
                $ongkirNom    = (float) $order->biaya_pengiriman;
                $grandTotal   = (float) ($order->grand_total > 0 ? $order->grand_total : $order->total);
                $dpNom        = (float) $order->dp_nominal;
                $sisaTagihan  = (float) ($order->sisa_tagihan > 0 ? $order->sisa_tagihan : ($dpNom > 0 ? ($grandTotal - $dpNom) : $grandTotal));
            @endphp

            <div class="summary-wrapper">
                <div class="notes-box">
                    <div>
                        <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: #1e40af; margin-bottom: 4px;" contenteditable="false">
                            <i class="bi bi-file-earmark-text me-1"></i> Syarat &amp; Ketentuan:
                        </div>
                        <div style="font-size: 9.5px; color: #334155; line-height: 1.45; white-space: pre-line; margin-bottom: 8px;" contenteditable="false" title="Klik untuk mengedit syarat & ketentuan">
                            {{ $order->syarat_ketentuan ?: "1. Barang yang sudah dipesan telah sesuai dengan spesifikasi order.\n2. Pembayaran dianggap sah setelah dana efektif masuk ke rekening perusahaan." }}
                        </div>

                        @if($order->catatan)
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 2px;" contenteditable="false">
                            <i class="bi bi-sticky me-1"></i> Catatan Khusus:
                        </div>
                        <div style="font-size: 9px; color: #475569; font-style: italic; margin-bottom: 8px;" contenteditable="false" title="Klik untuk mengedit catatan">
                            {{ $order->catatan }}
                        </div>
                        @endif

                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 6px 10px; margin-top: 6px;">
                            <span style="font-size: 8.5px; font-weight: 700; color: #1e40af; text-transform: uppercase;" contenteditable="false">Terbilang:</span>
                            <div style="font-size: 9.5px; font-weight: 700; font-style: italic; color: #1d4ed8;" contenteditable="false" title="Klik untuk mengedit terbilang">
                                {{ $terbilang ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-row">
                        <span class="label" contenteditable="false">Subtotal Produk:</span>
                        <span class="amount" contenteditable="false">Rp {{ number_format($subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if($ppnNom > 0)
                    <div class="summary-row">
                        <span class="label" contenteditable="false">Total Pajak (PPN):</span>
                        <span class="amount" style="color: #0284c7;" contenteditable="false">Rp {{ number_format($ppnNom, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($ongkirNom > 0)
                    <div class="summary-row">
                        <span class="label" contenteditable="false">Ongkir / Biaya Lain:</span>
                        <span class="amount" style="color: #2563eb;" contenteditable="false">Rp {{ number_format($ongkirNom, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    
                    <div class="summary-row grand-total-row">
                        <span contenteditable="false">GRAND TOTAL:</span>
                        <span class="amount" contenteditable="false">Rp {{ number_format($grandTotal, 2, ',', '.') }}</span>
                    </div>

                    @if($dpNom > 0)
                    <div class="summary-row dp-row">
                        <span class="label" contenteditable="false">Uang Muka (DP {{ $order->tipe_dp === 'Persen' && (float)$order->dp_persen > 0 ? (float)$order->dp_persen . '%' : '' }}):</span>
                        <span class="amount" contenteditable="false">Rp {{ number_format($dpNom, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Signatures Grid -->
            <div class="signature-grid">
                <div class="sig-block">
                    <div class="sig-title" contenteditable="false" title="Klik untuk mengedit">Penerima / Pembeli</div>
                    <div style="height: 55px;"></div>
                    <div class="sig-line" contenteditable="false" title="Klik untuk mengedit nama penerima">{{ $order->customer?->nama_perusahaan ?: ($order->customer?->nama_customer ?: ($order->nama_customer_walkin ?: '....................................')) }}</div>
                    <div class="sig-role" contenteditable="false" title="Klik untuk mengedit">Tanda Tangan &amp; Stempel</div>
                </div>

                <div class="sig-block">
                    <div class="sig-title" contenteditable="false" title="Klik untuk mengedit">Hormat Kami,</div>
                    <div style="height: 55px;"></div>
                    <div class="sig-line" contenteditable="false" title="Klik untuk mengedit nama marketing/kasir">{{ $order->sales?->nama_lengkap ?: ($order->creator?->name ?: 'Marketing') }}</div>
                    <div class="sig-role" contenteditable="false" title="Klik untuk mengedit">{{ $compNama }}</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Action Scripts -->
    <script>
        let isEditMode = false;

        // Toggle Live Free-Text Editing Mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const btn = document.getElementById('btnToggleEditMode');
            const btnText = document.getElementById('btnEditText');
            const banner = document.getElementById('editModeBanner');
            const editables = document.querySelectorAll('.editable-text, [contenteditable]');

            if (isEditMode) {
                btn.classList.add('active');
                btnText.innerHTML = 'Mode Edit: <strong>AKTIF</strong>';
                banner.style.display = 'flex';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                    el.setAttribute('spellcheck', 'false');
                });
            } else {
                btn.classList.remove('active');
                btnText.innerText = 'Edit Teks';
                banner.style.display = 'none';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'false');
                });
            }
        }

        // Prepare and Print
        function prepareAndPrint() {
            if (document.activeElement) document.activeElement.blur();
            window.print();
        }

        // Take Screenshot HD
        function takeScreenshot() {
            const btn = document.getElementById('btn_screenshot');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengambil...';
            btn.disabled = true;

            if (document.activeElement) document.activeElement.blur();

            const element = document.getElementById('invoice_paper_sheet');
            
            html2canvas(element, {
                scale: 2, // Resolusi HD 2x
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                let safeNomor = '{{ preg_replace("/[^A-Za-z0-9_\-]/", "_", $order->nomor_so) }}';
                link.download = `SalesOrder_${safeNomor}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Selesai!';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 2000);
            }).catch(err => {
                console.error('Screenshot error:', err);
                alert('Gagal mengambil screenshot.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }

        // Secara default saat halaman pertama kali dimuat, kunci semua elemen agar tidak bisa diedit tanpa menekan tombol 'Edit Teks'
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[contenteditable]').forEach(el => {
                el.setAttribute('contenteditable', 'false');
                el.classList.add('editable-text');
            });
        });
    </script>

</body>
</html>
