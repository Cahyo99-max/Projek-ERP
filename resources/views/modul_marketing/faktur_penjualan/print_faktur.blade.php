<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Faktur Penjualan – {{ $invoice->nomor_faktur }} – {{ $invoice->customer?->nama_perusahaan ?: ($invoice->customer?->nama_customer ?: 'Pelanggan') }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- html2canvas for High-Definition Image Export -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        :root {
            --brand-primary: #1e40af;
            --brand-dark: #1e3a8a;
            --brand-accent: #2563eb;
            --brand-soft: #eff6ff;
            --brand-border: #bfdbfe;
            --text-dark: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
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
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #0f172a;
            color: var(--text-dark);
            font-size: 10.5px;
            line-height: 1.45;
            padding-bottom: 70px;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace; }

        .sheet-viewport-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 20px 10px;
            display: flex;
            justify-content: center;
        }

        /* Floating Modern Controls Toolbar */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.92);
            padding: 7px 14px;
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
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.5);
        }
        .btn-print:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.7); 
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
            top: 75px; left: 50%;
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
            backdrop-filter: blur(10px);
        }

        /* A4 Paper Sheet */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm 12mm 15mm;
            background: #ffffff;
            box-shadow: 0 14px 45px rgba(0, 0, 0, 0.4);
            border-radius: 6px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        /* Kop Surat (Header) */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 12px;
            margin-bottom: 0px;
        }

        .top-accent-bar {
            height: 3.5px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #60a5fa 100%);
            border-radius: 4px;
            margin-top: 4px;
            margin-bottom: 16px;
        }

        .kop-logo-box {
            display: flex;
            align-items: center;
        }

        .kop-text-box {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }

        .kop-logo {
            max-height: 54px;
            max-width: 140px;
            object-fit: contain;
        }

        .kop-company-title {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .kop-parent-title {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 2px;
        }

        .kop-company-desc {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            margin-top: 3px;
            text-align: right;
        }

        /* Invoice Title & Status */
        .invoice-title-bar {
            text-align: center;
            margin: 12px 0 16px 0;
            padding: 0;
            background: transparent;
            border: none;
        }

        .invoice-title-text h1 {
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 1.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .invoice-title-text .inv-sub {
            font-size: 11px;
            color: #475569;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        .invoice-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #86efac;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #b91c1c;
            border: 1.5px solid #fca5a5;
        }

        .status-draft {
            background: #fef3c7;
            color: #b45309;
            border: 1.5px solid #fde68a;
        }

        /* 2-Column Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .info-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
        }

        .info-box-header {
            font-size: 10px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #cbd5e1;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10px;
            line-height: 1.35;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
            width: 38%;
            flex-shrink: 0;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            width: 62%;
            text-align: right;
            word-break: break-word;
        }

        /* Items Table */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 12px;
            border: 1px solid #1e3a8a;
        }

        .print-table thead th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 8px 8px;
            border: 1px solid #1e40af;
            text-align: left;
        }

        .print-table thead th.text-center {
            text-align: center;
        }

        .print-table thead th.text-right {
            text-align: right;
        }

        .print-table tbody td {
            padding: 7px 8px;
            border: 1px solid #bfdbfe;
            color: #1e293b;
            vertical-align: middle;
        }

        .print-table tbody tr:nth-child(even) {
            background: #f0f7ff;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Additional Costs Table */
        .costs-section {
            margin-bottom: 12px;
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .costs-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 6px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .costs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .costs-table td {
            padding: 4px 6px;
            border-bottom: 1px dashed #e2e8f0;
        }

        /* Bottom Section: Notes & Totals */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 16px;
            margin-bottom: 14px;
        }

        .left-notes-box {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .terbilang-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .terbilang-title {
            font-size: 8.5px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .terbilang-text {
            font-size: 10px;
            font-weight: 700;
            color: #1d4ed8;
            font-style: italic;
        }

        .bank-info-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .bank-title {
            font-size: 9px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bank-item {
            font-size: 9.5px;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .invoice-notes-card {
            font-size: 9px;
            color: #64748b;
            line-height: 1.35;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 7px 10px;
        }

        /* Summary Calculations Card */
        .summary-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            margin-bottom: 5px;
            gap: 8px;
        }

        .summary-label {
            color: #475569;
            font-weight: 500;
        }

        .summary-val {
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
        }

        .summary-divider {
            border-top: 1px solid #e2e8f0;
            margin: 6px 0;
        }

        /* Nested Payment Breakdown Box */
        .payment-nested-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 6px 8px;
            margin: 6px 0;
        }

        .payment-nested-header {
            font-size: 8.5px;
            font-weight: 800;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .payment-nested-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            margin-bottom: 3px;
            color: #166534;
            gap: 6px;
        }

        .payment-nested-row:last-child {
            margin-bottom: 0;
        }

        .payment-nested-label {
            color: #166534;
            font-weight: 500;
        }

        .payment-nested-val {
            font-weight: 700;
            white-space: nowrap;
            color: #15803d;
        }

        .payment-nested-divider {
            border-top: 1px dashed #86efac;
            margin: 4px 0;
        }

        .grand-total-row {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff !important;
            padding: 8px 10px;
            border-radius: 6px;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.25);
        }

        .grand-total-row span {
            color: #ffffff !important;
        }

        /* Signature Blocks */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            text-align: center;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .sig-block {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
        }

        .sig-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 45px;
        }

        .sig-line {
            display: inline-block;
            min-width: 170px;
            border-top: 1.5px solid #1e3a8a;
            padding-top: 4px;
            font-weight: 700;
            color: #0f172a;
            font-size: 10px;
        }

        .sig-role {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }

        /* In-place Editable Text Hover */
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

        /* ===== PRINT STYLING ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .toolbar, .edit-mode-banner {
                display: none !important;
            }

            .sheet-viewport-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .paper-sheet {
                width: 100% !important;
                min-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Edit Mode Banner -->
    <div id="editModeBanner" class="edit-mode-banner" style="display: none;">
        <i class="bi bi-pencil-fill"></i> Mode Edit Teks Aktif — Klik langsung teks mana saja untuk mengedit redaksi sebelum dicetak.
    </div>

    <!-- Floating Action Toolbar -->
    <div class="toolbar">
        <button type="button" class="toolbar-btn btn-edit-mode" id="btnToggleEditMode" onclick="toggleEditMode()" title="Aktifkan / nonaktifkan edit teks langsung">
            <i class="bi bi-pencil-square"></i> <span id="btnEditText">Edit Teks</span>
        </button>
        <button type="button" class="toolbar-btn btn-print" onclick="window.print()" title="Cetak Faktur (Print)">
            <i class="bi bi-printer-fill"></i> Cetak Faktur
        </button>
        <button type="button" class="toolbar-btn btn-screenshot" id="btn_screenshot" onclick="takeScreenshot()" title="Simpan Faktur sebagai Gambar">
            <i class="bi bi-camera-fill"></i> Simpan Gambar
        </button>
        <button type="button" class="toolbar-btn btn-close-preview" onclick="window.close()" title="Tutup Halaman">
            <i class="bi bi-x-lg"></i> Tutup
        </button>
    </div>

    <!-- Sheet Viewport Wrapper -->
    <div class="sheet-viewport-wrapper">
        <div class="paper-sheet" id="invoice_paper_sheet">
            <div>
                <!-- Letterhead / Kop Surat -->
                <div class="kop-surat">
                    <div class="kop-logo-box">
                        @if(!empty($activeLogo))
                            <img src="{{ $activeLogo }}" alt="Logo" class="kop-logo" onerror="this.src='{{ asset('gambar/logo.png') }}';">
                        @else
                            <div style="font-size: 28px; color: #1e40af;"><i class="bi bi-receipt-cutoff"></i></div>
                        @endif
                    </div>
                    <div class="kop-text-box">
                        @if(!empty($parentName))
                            <div class="kop-parent-title" contenteditable="false">{{ $parentName }}</div>
                        @endif
                        <div class="kop-company-title" contenteditable="false">{{ $entityName }}</div>
                        @if(!empty($entityAddress) || !empty($entityPhone) || !empty($entityEmail) || !empty($entityNpwp))
                            <div class="kop-company-desc" contenteditable="false">
                                @if(!empty($entityAddress))
                                    <div>{{ $entityAddress }}</div>
                                @endif
                                @if(!empty($entityPhone) || !empty($entityEmail) || !empty($entityNpwp))
                                    <div>
                                        @if($entityPhone) <span>Telp: {{ $entityPhone }}</span> @endif
                                        @if($entityEmail) <span> &bull; Email: {{ $entityEmail }}</span> @endif
                                        @if($entityNpwp) <span> &bull; NPWP: {{ $entityNpwp }}</span> @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Accent Line / Garis Biru -->
                <div class="top-accent-bar"></div>

                <!-- Invoice Title Bar -->
                <div class="invoice-title-bar">
                    <div class="invoice-title-text">
                        <h1 contenteditable="false">FAKTUR PENJUALAN</h1>
                        <div class="inv-sub font-mono" contenteditable="false">NO: {{ $invoice->nomor_faktur }}</div>
                    </div>

                </div>

                <!-- 2-Column Info Grid -->
                <div class="info-grid">
                    <!-- Kolom Kiri: Tagihan Kepada -->
                    <div class="info-box">
                        <div class="info-box-header" contenteditable="false">
                            <i class="bi bi-building"></i> Tagihan Kepada (Bill To)
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Customer / PT:</span>
                            <span class="info-val" style="color: #1e3a8a; font-weight: 700;" contenteditable="false">{{ $invoice->customer?->nama_perusahaan ?: ($invoice->customer?->nama_customer ?: '-') }}</span>
                        </div>
                        @if($invoice->customer?->nama_perusahaan && $invoice->customer?->nama_customer)
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Kontak PIC:</span>
                            <span class="info-val" contenteditable="false">{{ $invoice->customer->nama_customer }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Telepon / HP:</span>
                            <span class="info-val" contenteditable="false">{{ $invoice->customer?->telepon ?: ($invoice->customer?->hp ?: '-') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Alamat:</span>
                            <span class="info-val" contenteditable="false">{{ $invoice->customer?->alamat ?: '-' }}</span>
                        </div>
                        @if($invoice->customer?->npwp)
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">NPWP Customer:</span>
                            <span class="info-val font-mono" contenteditable="false">{{ $invoice->customer->npwp }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Kolom Kanan: Detail Transaksi Faktur -->
                    <div class="info-box">
                        <div class="info-box-header" contenteditable="false">
                            <i class="bi bi-receipt-cutoff"></i> Rincian Faktur &amp; Dokumen
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Tanggal Faktur:</span>
                            <span class="info-val font-mono" contenteditable="false">{{ $invoice->tanggal_faktur ? $invoice->tanggal_faktur->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Jatuh Tempo:</span>
                            <span class="info-val font-mono" style="color: #b91c1c; font-weight: 700;" contenteditable="false">{{ $invoice->tanggal_jatuh_tempo ? $invoice->tanggal_jatuh_tempo->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Syarat Pembayaran:</span>
                            <span class="info-val" contenteditable="false">{{ $invoice->paymentTerm?->nama_term ?: 'Tunai / Cash' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Surat Jalan (DO):</span>
                            <span class="info-val font-mono" style="color: #0284c7;" contenteditable="false">{{ $invoice->nomor_do_list ?: ($invoice->deliveryOrder?->nomor_do ?? '-') }}</span>
                        </div>
                        @if($invoice->nomor_resi_list)
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">No. Resi Kirim:</span>
                            <span class="info-val font-mono" contenteditable="false">{{ $invoice->nomor_resi_list }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Sales Order (SO):</span>
                            <span class="info-val font-mono" contenteditable="false">{{ $invoice->salesOrder?->nomor_so ?? ($invoice->deliveryOrder?->salesOrder?->nomor_so ?? '-') }}</span>
                        </div>
                        @if($invoice->tender)
                        <div class="info-row">
                            <span class="info-label" contenteditable="false">Tender / Kontrak:</span>
                            <span class="info-val" contenteditable="false">{{ $invoice->tender->nama_tender }} ({{ $invoice->tender->nomor_kontrak }})</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                @php
                    $deliveredQtyMap = [];
                    $approvedDoStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered'];

                    if ($invoice->salesOrder) {
                        foreach ($invoice->salesOrder->details as $soDet) {
                            $bId = $soDet->barang_id;
                            if (!$bId) continue;
                            if ($soDet->deliveryOrderDetails) {
                                foreach ($soDet->deliveryOrderDetails as $dod) {
                                    if ($dod->deliveryOrder && in_array($dod->deliveryOrder->status, $approvedDoStatuses)) {
                                        $deliveredQtyMap[$bId] = ($deliveredQtyMap[$bId] ?? 0) + (float)$dod->qty_kirim;
                                    }
                                }
                            }
                        }
                    } elseif ($invoice->deliveryOrder) {
                        if (in_array($invoice->deliveryOrder->status, $approvedDoStatuses)) {
                            foreach ($invoice->deliveryOrder->details as $doDet) {
                                $bId = $doDet->barang_id;
                                if (!$bId) continue;
                                $deliveredQtyMap[$bId] = ($deliveredQtyMap[$bId] ?? 0) + (float)$doDet->qty_kirim;
                            }
                        }
                    }
                @endphp
                <table class="print-table">
                    <thead>
                        <tr>
                            <th style="width: 32px;" class="text-center" contenteditable="false">No</th>
                            <th contenteditable="false">Deskripsi Barang / Jasa</th>
                            <th style="width: 75px;" class="text-center" contenteditable="false">Qty</th>
                            <th style="width: 55px;" class="text-center" contenteditable="false">Satuan</th>
                            <th style="width: 105px;" class="text-right" contenteditable="false">Harga Satuan</th>
                            <th style="width: 75px;" class="text-center" contenteditable="false">Diskon</th>
                            <th style="width: 80px;" class="text-center" contenteditable="false">PPN</th>
                            <th style="width: 115px;" class="text-right" contenteditable="false">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->details as $idx => $item)
                        @php
                            $qtyKirimVal = ($item->barang_id && isset($deliveredQtyMap[$item->barang_id])) ? $deliveredQtyMap[$item->barang_id] : null;
                            $fmtKirimStr = $qtyKirimVal !== null ? (fmod((float)$qtyKirimVal, 1) == 0 ? number_format($qtyKirimVal, 0, ',', '.') : rtrim(rtrim(number_format($qtyKirimVal, 2, ',', '.'), '0'), ',')) : null;
                        @endphp
                        <tr>
                            <td class="text-center font-mono" contenteditable="false">{{ $idx + 1 }}</td>
                            <td contenteditable="false">
                                <div style="font-weight: 700; color: #0f172a;">{{ $item->nama_barang }}</div>
                                <div style="font-size: 8.5px; color: #64748b;" class="font-mono">
                                    <!-- @if($item->barang?->kode_barang)
                                        <span>SKU: {{ $item->barang->kode_barang }}</span>
                                    @endif -->
                                    @if($fmtKirimStr !== null)
                                        @if($item->barang?->kode_barang) &bull; @endif
                                        <span style="color: #0369a1; font-weight: 600;">Terkirim: {{ $fmtKirimStr }} {{ $item->satuan?->nama_satuan ?? ($item->barang?->satuan?->nama_satuan ?? 'Pcs') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center font-mono" style="font-weight: 600;" contenteditable="false">
                                {{ fmod((float)$item->qty, 1) == 0 ? number_format($item->qty, 0, ',', '.') : rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }}
                                <!-- @if($fmtKirimStr !== null)
                                    <div style="font-size: 7.5px; color: #0369a1; font-weight: 700; margin-top: 1px;">
                                        ({{ $fmtKirimStr }} Kirim)
                                    </div>
                                @endif -->
                            </td>
                            <td class="text-center" contenteditable="false">{{ $item->satuan?->nama_satuan ?? ($item->barang?->satuan?->nama_satuan ?? 'Pcs') }}</td>
                            <td class="text-right font-mono" contenteditable="false">Rp {{ number_format($item->harga_satuan, 2, ',', '.') }}</td>
                            <td class="text-center font-mono" contenteditable="false">
                                @if((float)$item->diskon_persen > 0)
                                    {{ fmod((float)$item->diskon_persen, 1) == 0 ? number_format($item->diskon_persen, 0, ',', '.') : rtrim(rtrim(number_format($item->diskon_persen, 2, ',', '.'), '0'), ',') }}%
                                @elseif((float)$item->diskon_nominal > 0)
                                    Rp {{ number_format($item->diskon_nominal, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center font-mono" contenteditable="false">
                                @if((float)$item->tarif_ppn > 0)
                                    {{ fmod((float)$item->tarif_ppn, 1) == 0 ? number_format($item->tarif_ppn, 0, ',', '.') : rtrim(rtrim(number_format($item->tarif_ppn, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right font-mono" style="font-weight: 700;" contenteditable="false">Rp {{ number_format($item->total_harga, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding: 16px;">Tidak ada item terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Rincian Biaya Tambahan Section (jika ada) -->
                @if($invoice->costs && $invoice->costs->count() > 0)
                <div class="costs-section">
                    <div class="costs-title" contenteditable="false">
                        <i class="bi bi-truck"></i> Rincian Biaya Tambahan &amp; Pengiriman
                    </div>
                    <table class="costs-table">
                        @foreach($invoice->costs as $cItem)
                        <tr>
                            <td style="width: 25%; font-weight: 700; color: #1e293b;" contenteditable="false">{{ $cItem->jenis_biaya }}</td>
                            <td style="width: 50%; color: #64748b;" contenteditable="false">{{ $cItem->keterangan ?: '-' }}</td>
                            <td style="width: 25%; text-align: right; font-weight: 700;" class="font-mono" contenteditable="false">+ Rp {{ number_format($cItem->nominal, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                <!-- Bottom Section: Notes & Summary Totals -->
                <div class="bottom-section">
                    <!-- Sisi Kiri: Terbilang, Bank Rekening, Catatan -->
                    <div class="left-notes-box">
                        <div class="terbilang-card">
                            <div class="terbilang-title" contenteditable="false">Terbilang (Spelled Out):</div>
                            <div class="terbilang-text font-mono" contenteditable="false"># {{ $terbilang }} #</div>
                        </div>

                        <!-- Rekening Pembayaran -->
                        @if($bankAccounts && $bankAccounts->count() > 0)
                        <div class="bank-info-card">
                            <div class="bank-title" contenteditable="false">
                                <i class="bi bi-bank"></i> Rekening Pembayaran Resmi:
                            </div>
                            @foreach($bankAccounts as $bank)
                            <div class="bank-item" contenteditable="false">
                                <strong>{{ $bank->nama_bank }}</strong>: 
                                <span class="font-mono fw-bold text-primary">{{ $bank->nomor_rekening }}</span> 
                                (a.n {{ $bank->atas_nama }})
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Catatan Faktur -->
                        <div class="invoice-notes-card" contenteditable="false">
                            <strong>Catatan &amp; Ketentuan:</strong><br>
                            {!! nl2br(e($invoice->catatan ?: "1. Pembayaran dianggap sah setelah dana masuk ke rekening resmi perusahaan.\n2. Keterlambatan pelunasan dapat dikenakan penyesuaian termin berikutnya.")) !!}
                        </div>
                    </div>

                    <!-- Sisi Kanan: Summary Total Box -->
                    <div class="summary-card">
                        <div class="summary-row">
                            <span class="summary-label" contenteditable="false">Subtotal Item:</span>
                            <span class="summary-val font-mono" contenteditable="false">Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                        </div>

                        @if($invoice->diskon_nominal > 0)
                        <div class="summary-row" style="color: #dc2626;">
                            <span class="summary-label" style="color: #dc2626;" contenteditable="false">Diskon Faktur:</span>
                            <span class="summary-val font-mono" contenteditable="false">- Rp {{ number_format($invoice->diskon_nominal, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($invoice->pajak_nominal > 0)
                        <div class="summary-row">
                            <span class="summary-label" contenteditable="false">Total PPN:</span>
                            <span class="summary-val font-mono" contenteditable="false">+ Rp {{ number_format($invoice->pajak_nominal, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($invoice->biaya_lain > 0)
                        <div class="summary-row">
                            <span class="summary-label" contenteditable="false">Total Biaya Tambahan:</span>
                            <span class="summary-val font-mono" contenteditable="false">+ Rp {{ number_format($invoice->biaya_lain, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($invoice->pph_nominal > 0)
                        <div class="summary-row" style="color: #d97706;">
                            <span class="summary-label" style="color: #d97706;" contenteditable="false">Potongan PPh ({{ $invoice->pphPajak?->nama_pajak ?? 'PPh' }}):</span>
                            <span class="summary-val font-mono" contenteditable="false">- Rp {{ number_format($invoice->pph_nominal, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        @php
                            $validPayments = $invoice->paymentDetails ? $invoice->paymentDetails->filter(function($d) {
                                return $d->payment && $d->payment->status !== 'Cancelled' && empty($d->payment->deleted_at);
                            }) : collect();

                            $paymentTotal = (float)$validPayments->sum(function($d) {
                                return (float)$d->jumlah_bayar + (float)$d->potongan;
                            });

                            $dpNominal = (float)($invoice->uang_muka ?? 0);
                            $totalSudahBayar = $dpNominal + $paymentTotal;
                            $sisaBayar = max(0, (float)$invoice->grand_total - $totalSudahBayar);
                        @endphp

                        <div class="summary-divider"></div>

                        <div class="summary-row">
                            <span class="summary-label" style="font-weight: 700; color: #1e293b;" contenteditable="false">TOTAL FAKTUR:</span>
                            <span class="summary-val font-mono" style="font-weight: 700; color: #1e293b;" contenteditable="false">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</span>
                        </div>

                        @if($totalSudahBayar > 0)
                        <!-- Nested Box: Rincian Riwayat Pembayaran Masuk -->
                        <div class="payment-nested-box" contenteditable="false">
                            <div class="payment-nested-header">
                                <span><i class="bi bi-shield-check"></i> Riwayat Pembayaran</span>
                                <!-- <span style="font-size: 8px; opacity: 0.85;">{{ ($dpNominal > 0 ? 1 : 0) + $validPayments->count() }}x Pembayaran</span> -->
                            </div>

                            @if($dpNominal > 0)
                            <div class="payment-nested-row">
                                <span class="payment-nested-label">&bull; DP ({{ \Carbon\Carbon::parse($invoice->tanggal_faktur)->format('d/m/y') }}):</span>
                                <span class="payment-nested-val font-mono">- Rp {{ number_format($dpNominal, 2, ',', '.') }}</span>
                            </div>
                            @endif

                            @foreach($validPayments as $idx => $pDetail)
                                @php
                                    $pBayar = (float)$pDetail->jumlah_bayar;
                                    $pPotongan = (float)($pDetail->potongan ?? 0);
                                    $pDate = $pDetail->payment?->tanggal_bayar ? \Carbon\Carbon::parse($pDetail->payment->tanggal_bayar)->format('d/m/y') : '-';
                                @endphp
                                @if($pPotongan > 0)
                                    @if($pBayar > 0)
                                    <div class="payment-nested-row">
                                        <span class="payment-nested-label">&bull; Ke-{{ $idx + 1 }} ({{ $pDate }}):</span>
                                        <span class="payment-nested-val font-mono">- Rp {{ number_format($pBayar, 2, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    <div class="payment-nested-row" style="color: #b91c1c;">
                                        <span class="payment-nested-label" style="color: #b91c1c; {{ $pBayar > 0 ? 'padding-left: 8px;' : '' }}">&bull; Potongan ({{ $pDate }}):</span>
                                        <span class="payment-nested-val font-mono" style="color: #b91c1c;">- Rp {{ number_format($pPotongan, 2, ',', '.') }}</span>
                                    </div>
                                @else
                                    <div class="payment-nested-row">
                                        <span class="payment-nested-label">&bull; Ke-{{ $idx + 1 }} ({{ $pDate }}):</span>
                                        <span class="payment-nested-val font-mono">- Rp {{ number_format($pBayar, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                            @endforeach

                            <div class="payment-nested-divider"></div>

                            <div class="payment-nested-row" style="font-weight: 700;">
                                <span style="color: #14532d;">Total Sudah Dibayar:</span>
                                <span class="payment-nested-val font-mono" style="color: #14532d; font-size: 9.5px;">Rp {{ number_format($totalSudahBayar, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="summary-divider"></div>

                        @if($sisaBayar <= 0.01 || $invoice->status === 'Paid')
                        <div class="summary-row grand-total-row" style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);">
                            <span contenteditable="false">STATUS:</span>
                            <span class="font-mono" contenteditable="false">LUNAS</span>
                        </div>
                        @else
                        <div class="summary-row grand-total-row">
                            <span contenteditable="false">SISA BAYAR:</span>
                            <span class="font-mono" contenteditable="false">Rp {{ number_format($sisaBayar, 2, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Signatures Area -->
                <div class="signature-grid">
                    <div class="sig-block">
                        <div class="sig-title" contenteditable="false">Penerima / Pelanggan</div>
                        <div class="sig-line" contenteditable="false">{{ $invoice->customer?->nama_perusahaan ?: ($invoice->customer?->nama_customer ?: 'Tanda Tangan & Cap') }}</div>
                    </div>
                    <div class="sig-block">
                        <div class="sig-title" contenteditable="false">Hormat Kami, Bagian Keuangan</div>
                        <div class="sig-line" contenteditable="false">{{ $invoice->creator?->name ?: 'Finance & Accounting' }}</div>
                        <div class="sig-role" contenteditable="false">{{ $entityName }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle In-Place Inline Text Edit Mode
        let isEditMode = false;
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const btn = document.getElementById('btnToggleEditMode');
            const btnText = document.getElementById('btnEditText');
            const banner = document.getElementById('editModeBanner');
            const editables = document.querySelectorAll('#invoice_paper_sheet [contenteditable]');

            if (isEditMode) {
                btn.classList.add('active');
                btnText.textContent = 'Selesai Edit';
                banner.style.display = 'flex';
                editables.forEach(el => el.setAttribute('contenteditable', 'true'));
            } else {
                btn.classList.remove('active');
                btnText.textContent = 'Edit Teks';
                banner.style.display = 'none';
                editables.forEach(el => el.setAttribute('contenteditable', 'false'));
            }
        }

        // High-Definition Screenshot Generator via html2canvas
        function takeScreenshot() {
            const sheet = document.getElementById('invoice_paper_sheet');
            const btn = document.getElementById('btn_screenshot');
            const originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            // Temporarily turn off edit mode if active
            if (isEditMode) toggleEditMode();

            html2canvas(sheet, {
                scale: 2.5,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.className = 'no-loader';
                link.download = 'Faktur_{{ str_replace(["/", "\\"], "_", $invoice->nomor_faktur) }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Gambar Berhasil Disimpan!',
                        text: 'Faktur penjualan berhasil disimpan dalam format gambar HD.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            }).catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Gagal mengambil gambar: ' + err.message);
            });
        }
    </script>
</body>
</html>
