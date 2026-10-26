<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Surat Jalan – {{ $deliveryOrder->nomor_do }} – {{ $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan')) }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- html2canvas & SweetAlert2 for Modern High-Definition Screenshot & Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

        :root {
            --brand-primary: #1e40af;
            --brand-dark: #1e3a8a;
            --brand-accent: #2563eb;
            --brand-soft: #eff6ff;
            --brand-border: #bfdbfe;
            --brand-border-subtle: #dbeafe;
            --text-dark: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #3b4252;
            color: var(--text-dark);
            font-size: 10.5px;
            line-height: 1.4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0;
            min-height: 100vh;
        }

        .sheet-viewport-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 0 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Standard A4 Paper */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm 12mm 15mm;
            background: #ffffff;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
            margin: 0 auto 20px auto;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        /* Top Action Toolbar */
        .no-print-toolbar {
            width: 210mm;
            max-width: calc(100vw - 24px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.3);
            border: 1px solid #3b82f6;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 15px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-edit-mode {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
        .btn-edit-mode:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        .btn-edit-mode.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border-color: #f59e0b !important;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.5) !important;
        }

        .btn-print {
            background: #ffffff;
            color: #1e40af;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .btn-print:hover {
            background: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-screenshot {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.4);
        }
        .btn-screenshot:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            transform: translateY(-1px);
        }

        .btn-close-view {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-close-view:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* Floating Edit Mode Banner Alert */
        .edit-mode-banner {
            width: 210mm;
            max-width: calc(100vw - 24px);
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #f59e0b;
            font-size: 11px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            animation: fadeInDown 0.25s ease-out;
            flex-shrink: 0;
            box-sizing: border-box;
        }
        .btn-banner-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-banner-done {
            background: #d97706;
            color: #ffffff;
        }
        .btn-banner-done:hover {
            background: #b45309;
        }
        .btn-banner-reset {
            background: #ffffff;
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .btn-banner-reset:hover {
            background: #fef3c7;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Editing Mode Active Styles on Sheet Elements */
        .editing-mode-active .editable-text {
            outline: 1.5px dashed rgba(245, 158, 11, 0.75) !important;
            background-color: rgba(254, 243, 199, 0.45) !important;
            cursor: text !important;
            border-radius: 3px;
            transition: background 0.15s, outline 0.15s, box-shadow 0.15s;
            display: inline-block;
            min-width: 15px;
        }
        .editing-mode-active .info-val.editable-text,
        .editing-mode-active .item-product-name.editable-text,
        .editing-mode-active .company-name.editable-text,
        .editing-mode-active .sig-name.editable-text,
        .editing-mode-active .delivery-notes-text.editable-text {
            display: block;
        }
        .editing-mode-active .editable-text:hover {
            outline: 1.5px solid #d97706 !important;
            background-color: rgba(254, 243, 199, 0.85) !important;
        }
        .editing-mode-active .editable-text:focus {
            outline: 2px solid #2563eb !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
        }

        .row-delete-btn {
            display: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 11px;
            margin-left: 5px;
            transition: transform 0.15s;
        }
        .row-delete-btn:hover {
            transform: scale(1.2);
        }
        .editing-mode-active .row-delete-btn {
            display: inline-block;
        }
        .add-row-action-tr {
            display: none;
        }
        .editing-mode-active .add-row-action-tr {
            display: table-row !important;
        }

        /* Modern SweetAlert Custom Styling */
        .swal2-popup {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border-radius: 16px !important;
            padding: 1.5rem !important;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.28) !important;
        }
        .swal2-title {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        .swal2-html-container {
            font-size: 13px !important;
            color: #475569 !important;
            line-height: 1.5 !important;
        }
        .swal2-actions {
            margin-top: 1.25rem !important;
            gap: 10px !important;
        }
        .swal2-confirm, .swal2-cancel {
            font-size: 12px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            padding: 8px 18px !important;
        }

        /* ==================== KOP SURAT RESMI ==================== */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            position: relative;
        }

        .kop-divider {
            height: 3px;
            background: linear-gradient(90deg, #1e40af 0%, #2563eb 55%, #93c5fd 100%);
            border-radius: 2px;
            margin-bottom: 12px;
        }

        .kop-logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            max-height: 56px;
            max-width: 170px;
            object-fit: contain;
            display: block;
        }

        .kop-info {
            text-align: right;
            max-width: 480px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-primary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            line-height: 1.25;
        }

        /* ==================== DOCUMENT TITLE ==================== */
        .doc-title-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--brand-primary);
            text-transform: uppercase;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .doc-number-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-primary);
            background: var(--brand-soft);
            padding: 3px 14px;
            border-radius: 30px;
            border: 1.5px solid var(--brand-border);
            letter-spacing: 0.3px;
        }

        /* ==================== META INFORMATION GRID ==================== */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .meta-card {
            background: #ffffff;
            border: 1.5px solid var(--brand-border);
            border-radius: 8px;
            padding: 9px 12px;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.05);
        }

        .meta-card-title {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--brand-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid var(--brand-border-subtle);
            padding-bottom: 5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 9px;
            margin-bottom: 3.5px;
            line-height: 1.35;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 500;
            width: 95px;
            flex-shrink: 0;
        }

        .info-val {
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
            flex: 1;
            word-break: break-word;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.2px;
        }

        /* ==================== ITEMS TABLE ==================== */
        .table-container {
            border: 1.5px solid var(--brand-border);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .items-table thead th {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.6px;
            padding: 7px 8px;
            border: 1px solid #1e40af;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .items-table tbody td {
            padding: 6.5px 8px;
            border: 1px solid var(--brand-border-subtle);
            vertical-align: middle;
            color: var(--text-body);
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fbff;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .item-sku-badge {
            display: inline-block;
            font-size: 8.5px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            background: var(--brand-soft);
            color: var(--brand-primary);
            padding: 1.5px 6px;
            border-radius: 4px;
            border: 1px solid var(--brand-border);
        }

        .item-product-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 9.5px;
        }

        .serial-box {
            margin-top: 3px;
            padding: 2.5px 6px;
            background: var(--brand-soft);
            border: 1px solid var(--brand-border);
            border-radius: 4px;
            font-size: 8px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--brand-primary);
            line-height: 1.35;
            word-break: break-all;
        }

        .check-square {
            width: 14px;
            height: 14px;
            border: 1.5px solid var(--brand-accent);
            border-radius: 3px;
            margin: 0 auto;
            background: #ffffff;
        }

        /* ==================== NOTICE BOX ==================== */
        .notice-box {
            background: var(--brand-soft);
            border: 1px solid var(--brand-border);
            border-left: 3.5px solid var(--brand-accent);
            border-radius: 6px;
            padding: 7px 12px;
            font-size: 8.5px;
            color: #334155;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .notice-title {
            font-weight: 700;
            color: var(--brand-primary);
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ==================== MODERN SIGNATURES (TTD) ==================== */
        .sig-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: auto;
            padding-top: 8px;
        }

        .sig-card {
            background: #ffffff;
            border: 1.5px solid var(--brand-border);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.06);
            min-height: 118px;
        }

        .sig-header {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-bottom: 1px solid var(--brand-border);
            padding: 5px 6px;
            text-align: center;
            font-size: 8.5px;
            font-weight: 700;
            color: var(--brand-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-body {
            position: relative;
            flex: 1;
            min-height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sig-stamp-guide {
            font-size: 7.5px;
            color: #93c5fd;
            border: 1px dashed var(--brand-border);
            border-radius: 4px;
            padding: 4px 6px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            pointer-events: none;
            user-select: none;
        }

        .sig-footer {
            padding: 4px 8px 6px 8px;
            text-align: center;
            border-top: 1.5px solid var(--brand-primary);
            background: #ffffff;
        }

        .sig-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 9.5px;
            line-height: 1.3;
            margin-bottom: 2px;
            word-break: break-word;
            min-height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sig-role {
            font-size: 7.5px;
            color: var(--brand-accent);
            font-weight: 600;
        }

        .sig-date-line {
            font-size: 7.5px;
            color: var(--text-muted);
            margin-top: 2px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* ==================== PRINT MEDIA STYLES ==================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .no-print-toolbar, .edit-mode-banner, .add-row-action-tr, .row-delete-btn, .swal2-container {
                display: none !important;
            }
            .sheet-viewport-wrapper {
                padding: 0 !important;
                overflow: visible !important;
            }
            .page-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                padding: 10mm 14mm 10mm 14mm !important;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            .editable-text, [contenteditable] {
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print / Action Toolbar -->
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-truck text-light" style="font-size: 16px;"></i>
            <span style="font-weight: 700; font-size: 13px;">Surat Jalan Pengiriman – {{ $deliveryOrder->nomor_do }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-toolbar btn-edit-mode" id="btnToggleEditMode" onclick="toggleEditMode()" title="Klik untuk mengedit langsung teks di surat jalan">
                <i class="bi bi-pencil-square"></i> <span id="btnEditText">Edit Manual</span>
            </button>
            <button type="button" class="btn-toolbar btn-print" onclick="prepareAndPrint()">
                <i class="bi bi-printer-fill"></i> Cetak Surat Jalan
            </button>
            <button type="button" class="btn-toolbar btn-screenshot" onclick="takeScreenshot()">
                <i class="bi bi-camera-fill"></i> Simpan Gambar (HD)
            </button>
            <button type="button" class="btn-toolbar btn-close-view" onclick="window.close()">
                <i class="bi bi-x-lg"></i> Tutup
            </button>
        </div>
    </div>

    <!-- Floating Edit Mode Banner Alert -->
    <div id="editModeBanner" class="edit-mode-banner" style="display: none;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-pencil-fill" style="color: #b45309; font-size: 13px;"></i>
            <span><strong>Mode Edit Manual Aktif:</strong> Anda dapat mengklik &amp; mengedit teks secara bebas (Kop, No. DO, Alamat, Qty, Item, TTD, Catatan, dll). Garis bantu edit tidak akan tercetak.</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <button type="button" class="btn-banner-action btn-banner-reset" onclick="resetToOriginal()" title="Kembalikan teks ke data asli">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Asli
            </button>
            <button type="button" class="btn-banner-action btn-banner-done" onclick="toggleEditMode()" title="Kunci teks dan selesaikan edit">
                <i class="bi bi-check-circle-fill"></i> Selesai Edit
            </button>
        </div>
    </div>

    <!-- Viewport Container -->
    <div class="sheet-viewport-wrapper">
        <div class="page-sheet" id="delivery_order_paper_sheet">
            
            <div>
                <!-- ==================== KOP DOKUMEN RESMI ==================== -->
                @php
                    // Ambil nama entitas aktif: Prioritaskan nama Cabang jika Cabang
                    $compNama = $isCabang ? ($branch->nama_cabang ?? 'Cabang') : ($company->nama_perusahaan ?? 'PT. PERSADA AMAN SENTOSA');
                @endphp

                <div class="kop-surat">
                    <div class="kop-logo-box">
                        <img src="{{ $companyLogo }}" class="kop-logo" alt="Logo">
                    </div>
                    <div class="kop-info">
                        <div class="company-name editable-text">{{ $compNama }}</div>
                    </div>
                </div>

                <!-- Accent Line (Blue Gradient) -->
                <div class="kop-divider"></div>

                <!-- ==================== DOCUMENT TITLE ==================== -->
                <div class="doc-title-box">
                    <h1 class="doc-title editable-text">SURAT JALAN PENGIRIMAN</h1>
                    <div class="doc-number-badge">
                        <i class="bi bi-qr-code"></i> No: <span class="editable-text">{{ $deliveryOrder->nomor_do }}</span>
                    </div>
                </div>

                <!-- ==================== META INFORMATION ==================== -->
                <div class="meta-grid">
                    <!-- Kartu Penerima -->
                    <div class="meta-card">
                        <div class="meta-card-title">
                            <i class="bi bi-geo-alt-fill"></i> Tujuan &amp; Informasi Penerima
                        </div>
                        <div class="info-row">
                            <span class="info-label">Instansi / PT:</span>
                            <span class="info-val editable-text" style="color: var(--brand-primary); font-weight: 700;">{{ $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: '-') }}</span>
                        </div>
                        @php
                            // Prioritaskan nama pemesan (personal name), BUKAN nama perusahaan
                            $namaPemesan = $deliveryOrder->customer?->nama_customer;
                            if (!$namaPemesan && $deliveryOrder->nama_penerima) {
                                if (preg_match('/\(Up:\s*([^)]+)\)/i', $deliveryOrder->nama_penerima, $matches)) {
                                    $namaPemesan = trim($matches[1]);
                                } elseif (!($deliveryOrder->customer?->nama_perusahaan && trim($deliveryOrder->nama_penerima) === trim($deliveryOrder->customer->nama_perusahaan))) {
                                    $namaPemesan = $deliveryOrder->nama_penerima;
                                }
                            }
                            $namaPemesan = $namaPemesan ?: ($deliveryOrder->salesOrder?->nama_customer_walkin ?: 'Pelanggan Umum');
                        @endphp
                        <div class="info-row">
                            <span class="info-label">Nama Penerima:</span>
                            <span class="info-val editable-text">{{ $namaPemesan }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Telepon / HP:</span>
                            <span class="info-val font-mono editable-text">{{ $deliveryOrder->telepon_penerima ?: ($deliveryOrder->customer?->telepon ?: ($deliveryOrder->customer?->hp ?: '-')) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Alamat Kirim:</span>
                            <span class="info-val editable-text" style="font-size: 8.5px; line-height: 1.3;">
                                {{ $deliveryOrder->alamat_tujuan ?: ($deliveryOrder->customer?->alamat ?: 'Diambil di Tempat') }}
                            </span>
                        </div>
                    </div>

                    <!-- Kartu Logistik & Pengiriman -->
                    <div class="meta-card">
                        <div class="meta-card-title">
                            <i class="bi bi-truck"></i> Detail Logistik &amp; Kurir
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tgl Pengiriman:</span>
                            <span class="info-val font-mono editable-text">{{ $deliveryOrder->tanggal_kirim ? $deliveryOrder->tanggal_kirim->format('d F Y') : date('d F Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Pesanan (SO):</span>
                            <span class="info-val font-mono editable-text">{{ $deliveryOrder->salesOrder?->nomor_so ?: '-' }}</span>
                        </div>
                        @php
                            $soTender = $deliveryOrder->salesOrder?->tender;
                            $tenderNomor = $soTender ? ($soTender->nomor_kontrak ?: $soTender->nama_tender) : null;
                        @endphp
                        @if($tenderNomor)
                        <div class="info-row">
                            <span class="info-label">No. Tender:</span>
                            <span class="info-val font-mono editable-text" style="color: var(--brand-primary); font-weight: 700;">{{ $tenderNomor }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">Gudang Asal:</span>
                            <span class="info-val editable-text">{{ $deliveryOrder->gudang?->nama_gudang ?: ($isCabang ? 'Gudang Cabang' : 'Gudang Utama') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode / Kurir:</span>
                            <span class="info-val editable-text">
                                {{ $deliveryOrder->metode_pengiriman }}
                                @if($deliveryOrder->nama_ekspedisi) ({{ $deliveryOrder->nama_ekspedisi }}) @endif
                            </span>
                        </div>
                        @if($deliveryOrder->nama_sopir || $deliveryOrder->metode_pengiriman === 'Kurir Internal')
                        <div class="info-row">
                            <span class="info-label">Nama Sopir:</span>
                            <span class="info-val editable-text">{{ $deliveryOrder->nama_sopir ?: '-' }}</span>
                        </div>
                        @endif
                        @if($deliveryOrder->plat_nomor || $deliveryOrder->metode_pengiriman === 'Kurir Internal')
                        <div class="info-row">
                            <span class="info-label">No. Polisi / Plat:</span>
                            <span class="info-val font-mono editable-text">{{ $deliveryOrder->plat_nomor ?: '-' }}</span>
                        </div>
                        @endif
                        @if($deliveryOrder->nomor_resi)
                        <div class="info-row">
                            <span class="info-label">No. Resi:</span>
                            <span class="info-val font-mono editable-text" style="color: var(--brand-accent); font-weight: 700;">{{ $deliveryOrder->nomor_resi }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ==================== TABEL ITEM PENGIRIMAN ==================== -->
                <div class="table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">No</th>
                                <th style="width: 120px;">Kode / SKU</th>
                                <th>Nama Produk &amp; Deskripsi Barang</th>
                                <th style="width: 85px;" class="text-center">Qty Kirim</th>
                                <th style="width: 75px;" class="text-center">Satuan</th>
                                <th style="width: 140px;">Keterangan / Koli</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveryOrder->details as $index => $item)
                                @php
                                    $namaBarang = $item->nama_barang_custom ?: ($item->barang?->nama_barang ?: 'Item');
                                    $sku = $item->barang?->kode_barang ?: '-';
                                    $satuan = $item->nama_satuan ?: ($item->barang?->satuan?->nama_satuan ?: 'Pcs');
                                @endphp
                                <tr class="item-row">
                                    <td class="text-center font-mono" style="color: var(--text-muted);">
                                        <span class="editable-text">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <span class="item-sku-badge editable-text">{{ $sku }}</span>
                                    </td>
                                    <td>
                                        <div class="item-product-name editable-text">{{ $namaBarang }}</div>
                                    </td>
                                    <td class="text-center font-mono" style="font-weight: 700; font-size: 10.5px; color: var(--text-dark);">
                                        <span class="editable-text">{{ number_format($item->qty_kirim, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center" style="font-size: 9px; color: var(--text-body);">
                                        <span class="editable-text">{{ $satuan }}</span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 8.5px;">
                                        <span class="editable-text">{{ $item->catatan_item ?: '-' }}</span>
                                        <i class="bi bi-trash row-delete-btn" onclick="deleteTableRow(this)" title="Hapus Baris Ini"></i>
                                    </td>
                                </tr>
                            @empty
                                <tr class="item-row">
                                    <td class="text-center font-mono" style="color: var(--text-muted);">
                                        <span class="editable-text">1</span>
                                    </td>
                                    <td>
                                        <span class="item-sku-badge editable-text">-</span>
                                    </td>
                                    <td>
                                        <div class="item-product-name editable-text">Barang Contoh</div>
                                    </td>
                                    <td class="text-center font-mono" style="font-weight: 700; font-size: 10.5px; color: var(--text-dark);">
                                        <span class="editable-text">1</span>
                                    </td>
                                    <td class="text-center" style="font-size: 9px; color: var(--text-body);">
                                        <span class="editable-text">Pcs</span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 8.5px;">
                                        <span class="editable-text">-</span>
                                        <i class="bi bi-trash row-delete-btn" onclick="deleteTableRow(this)" title="Hapus Baris Ini"></i>
                                    </td>
                                </tr>
                            @endforelse
                            <tr class="add-row-action-tr">
                                <td colspan="6" class="text-center" style="padding: 6px; background: #fffbeb; border: 1px dashed #f59e0b;">
                                    <button type="button" onclick="addNewTableRow()" style="background: #f59e0b; color: #fff; border: none; padding: 3px 12px; border-radius: 4px; font-size: 9.5px; font-weight: 700; cursor: pointer;">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- ==================== 4 KOLOM TANDA TANGAN MODERN (BLUE THEME) ==================== -->
            <div class="sig-container">
                <!-- 1. Dibuat Oleh -->
                <div class="sig-card">
                    <div class="sig-header editable-text">
                        <i class="bi bi-pencil-square me-1"></i> Dibuat Oleh
                    </div>
                    <div class="sig-body"></div>
                    <div class="sig-footer">
                        <div class="sig-name editable-text">
                            {{ $deliveryOrder->creator?->nama_lengkap ?: ($deliveryOrder->creator?->username ?: 'Staf Logistik') }}
                        </div>
                        <div class="sig-role editable-text">Logistik &amp; Gudang</div>
                    </div>
                </div>

                <!-- 2. Disetujui Oleh -->
                <div class="sig-card">
                    <div class="sig-header editable-text">
                        <i class="bi bi-patch-check-fill me-1"></i> Disetujui Oleh
                    </div>
                    <div class="sig-body"></div>
                    <div class="sig-footer">
                        <div class="sig-name editable-text">
                            @if($deliveryOrder->approver)
                                {{ $deliveryOrder->approver->nama_lengkap ?? $deliveryOrder->approver->username }}
                            @else
                                ( .................... )
                            @endif
                        </div>
                        <div class="sig-role editable-text">
                            @if($deliveryOrder->approver)
                                {{ $deliveryOrder->approver->position?->position_name ?? 'Manajer / Atasan' }}
                            @else
                                Manajer / Atasan
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 3. Petugas Keamanan -->
                <div class="sig-card">
                    <div class="sig-header editable-text">
                        <i class="bi bi-shield-check me-1"></i> Petugas Keamanan
                    </div>
                    <div class="sig-body"></div>
                    <div class="sig-footer">
                        <div class="sig-name editable-text">( .................... )</div>
                        <div class="sig-role editable-text">Security</div>
                    </div>
                </div>

                <!-- 4. Penerima Barang -->
                <div class="sig-card">
                    <div class="sig-header editable-text">
                        <i class="bi bi-person-check-fill me-1"></i> Diterima Oleh
                    </div>
                    <div class="sig-body"></div>
                    <div class="sig-footer">
                        <div class="sig-name editable-text">
                            {{ $namaPemesan ?: '( .................... )' }}
                        </div>
                        <div class="sig-role editable-text">Penerima</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Logic: Edit Mode, Print, and Screenshot -->
    <script>
        let isEditMode = false;

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function toggleEditMode() {
            isEditMode = !isEditMode;
            const sheet = document.getElementById('delivery_order_paper_sheet');
            const btn = document.getElementById('btnToggleEditMode');
            const btnText = document.getElementById('btnEditText');
            const banner = document.getElementById('editModeBanner');
            const editables = document.querySelectorAll('.editable-text');

            if (isEditMode) {
                sheet.classList.add('editing-mode-active');
                btn.classList.add('active');
                btnText.innerHTML = '<i class="bi bi-check-lg me-1"></i> Edit Aktif';
                banner.style.display = 'flex';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                    el.setAttribute('spellcheck', 'false');
                });

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Mode Edit AKTIF: Klik langsung teks untuk mengubah',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
            } else {
                sheet.classList.remove('editing-mode-active');
                btn.classList.remove('active');
                btnText.innerText = 'Edit Manual';
                banner.style.display = 'none';

                if (document.activeElement) document.activeElement.blur();

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'false');
                });

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Teks dikunci dan siap dicetak',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                }
            }
        }

        function addNewTableRow() {
            const tbody = document.querySelector('.items-table tbody');
            const existingRows = tbody.querySelectorAll('tr.item-row');
            const newIndex = existingRows.length + 1;
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td class="text-center font-mono" style="color: var(--text-muted);">
                    <span class="editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">${newIndex}</span>
                </td>
                <td>
                    <span class="item-sku-badge editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">-</span>
                </td>
                <td>
                    <div class="item-product-name editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">Nama Produk / Item Baru</div>
                </td>
                <td class="text-center font-mono" style="font-weight: 700; font-size: 10.5px; color: var(--text-dark);">
                    <span class="editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">1</span>
                </td>
                <td class="text-center" style="font-size: 9px; color: var(--text-body);">
                    <span class="editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">Pcs</span>
                </td>
                <td style="color: var(--text-muted); font-size: 8.5px;">
                    <span class="editable-text" contenteditable="${isEditMode ? 'true' : 'false'}" spellcheck="false">-</span>
                    <i class="bi bi-trash row-delete-btn" onclick="deleteTableRow(this)" title="Hapus Baris Ini"></i>
                </td>
            `;
            const actionTr = tbody.querySelector('.add-row-action-tr');
            if (actionTr) {
                tbody.insertBefore(tr, actionTr);
            } else {
                tbody.appendChild(tr);
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Baris item baru ditambahkan ke tabel',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }
        }

        function deleteTableRow(icon) {
            const tr = icon.closest('tr.item-row');
            if (!tr) return;

            const itemName = tr.querySelector('.item-product-name')?.innerText.trim() || 'item ini';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Baris Item?',
                    html: `Apakah Anda yakin ingin menghapus baris <strong>"${escapeHtml(itemName)}"</strong> dari cetakan Surat Jalan?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        tr.remove();
                        // Renumber
                        const rows = document.querySelectorAll('.items-table tbody tr.item-row');
                        rows.forEach((row, idx) => {
                            const noCell = row.querySelector('td:first-child .editable-text');
                            if (noCell) noCell.innerText = idx + 1;
                        });

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Baris item berhasil dihapus dari cetakan',
                            showConfirmButton: false,
                            timer: 1600,
                            timerProgressBar: true
                        });
                    }
                });
            } else {
                if (confirm(`Hapus baris "${itemName}" dari cetakan Surat Jalan?`)) {
                    tr.remove();
                    const rows = document.querySelectorAll('.items-table tbody tr.item-row');
                    rows.forEach((row, idx) => {
                        const noCell = row.querySelector('td:first-child .editable-text');
                        if (noCell) noCell.innerText = idx + 1;
                    });
                }
            }
        }

        function resetToOriginal() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Reset Teks ke Data Asli?',
                    text: 'Semua perubahan manual akan dibatalkan dan teks dikembalikan ke data awal database.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Reset Asli',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else {
                if (confirm('Batalkan perubahan dan kembalikan teks ke data asli?')) {
                    window.location.reload();
                }
            }
        }

        function prepareAndPrint() {
            if (document.activeElement) document.activeElement.blur();
            window.print();
        }

        function takeScreenshot() {
            const element = document.getElementById('delivery_order_paper_sheet');
            const toolbar = document.querySelector('.no-print-toolbar');
            const btn = document.querySelector('.btn-screenshot');
            const origText = btn ? btn.innerHTML : '';
            
            if (document.activeElement) document.activeElement.blur();

            const wasEditing = isEditMode;
            if (wasEditing) {
                element.classList.remove('editing-mode-active');
            }

            if (btn) {
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
                btn.disabled = true;
            }
            if (toolbar) toolbar.style.opacity = '0.5';

            html2canvas(element, {
                scale: 3,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Surat_Jalan_{{ str_replace(["/", "\\", " ", "-"], "_", $deliveryOrder->nomor_do) }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Gambar HD berhasil diunduh',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            }).catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Gagal Mengambil Gambar',
                        text: err ? err.toString() : 'Terjadi kesalahan saat membuat screenshot.',
                        icon: 'error',
                        confirmButtonColor: '#1e40af'
                    });
                } else {
                    alert('Gagal mengambil screenshot: ' + err);
                }
            }).finally(() => {
                if (wasEditing) {
                    element.classList.add('editing-mode-active');
                }
                if (toolbar) toolbar.style.opacity = '1';
                if (btn) {
                    btn.innerHTML = origText;
                    btn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
