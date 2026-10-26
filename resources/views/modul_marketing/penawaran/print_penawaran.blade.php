<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran Harga – {{ $quotation->nomor_quotation }} – {{ $quotation->customer?->nama_perusahaan ?: ($quotation->customer?->nama_customer ?? 'Klien') }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- html2canvas for Screenshot & Image Download -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #090d16;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            padding-bottom: 60px;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Floating Controls Toolbar (Cinematic Glassmorphism) */
        .toolbar {
            position: fixed;
            top: 14px; 
            right: 16px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.92);
            padding: 7px 14px;
            border-radius: 50px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(59, 130, 246, 0.3);
            backdrop-filter: blur(20px);
            max-width: calc(100vw - 24px);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .toolbar::-webkit-scrollbar {
            display: none;
        }

        .toolbar-btn {
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            gap: 6px;
            padding: 7px 15px;
            border-radius: 50px;
            font-size: 11.5px; 
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            border: none;
            white-space: nowrap;
            flex-shrink: 0;
            line-height: 1.2;
        }

        .btn-text-short {
            display: none;
        }
        .btn-text-full {
            display: inline;
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
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
        }
        .btn-screenshot:hover {
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.6); 
        }

        .btn-edit-mode {
            background: rgba(255, 255, 255, 0.12);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-edit-mode:hover {
            background: rgba(255, 255, 255, 0.22);
        }
        .btn-edit-mode.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border-color: #f59e0b !important;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.5) !important;
        }

        .btn-close-preview {
            background: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-close-preview:hover { background: rgba(255, 255, 255, 0.18); }

        /* Floating Edit Mode Banner */
        .edit-mode-banner {
            position: fixed;
            top: 68px; left: 50%;
            transform: translateX(-50%);
            z-index: 998;
            background: rgba(245, 158, 11, 0.95);
            color: #78350f;
            font-weight: 700;
            font-size: 11px;
            padding: 6px 18px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: calc(100vw - 32px);
            text-align: center;
            animation: fadeInDown 0.3s ease;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -15px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        /* Editing Mode Styles */
        .editing-mode-active .editable-text {
            outline: 1px dashed rgba(59, 130, 246, 0.6) !important;
            background-color: rgba(239, 246, 255, 0.6) !important;
            cursor: text !important;
            border-radius: 3px;
            transition: all 0.15s ease;
        }
        .editing-mode-active .editable-text:hover,
        .editing-mode-active .editable-text:focus {
            outline: 2px solid #2563eb !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.25) !important;
        }

        /* Viewport Wrapper */
        .sheet-viewport-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 68px 12px 40px 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* A4 Paper Sheet */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 16mm 18mm 16mm 18mm;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(59, 130, 246, 0.15);
            border-radius: 6px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Top Decorative Color Accent Bar */
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
            max-height: 65px;
            max-width: 145px;
            object-fit: contain;
        }

        .kop-info {
            text-align: right;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
        }
        .kop-info strong {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            display: block;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        /* Doc Title */
        .doc-title-box {
            text-align: center;
            margin-bottom: 18px;
            padding-top: 4px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            padding: 3px 12px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
        }

        /* Info Grid */
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
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3.5px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748b; font-size: 10px; }
        .info-val { font-weight: 600; color: #0f172a; text-align: right; font-size: 10.5px; }

        /* Items Table */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            overflow: hidden;
        }
        .print-table thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 9px 10px;
            border: none;
        }
        .print-table td {
            padding: 7.5px 10px;
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

        /* Summary Box */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
            margin-bottom: 18px;
        }
        .terbilang-card {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            padding: 10px 14px;
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
        .summary-row.grand-total-row {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 7px 10px;
            border-radius: 6px;
            margin-top: 6px;
            margin-bottom: 0;
            box-shadow: 0 3px 8px rgba(30, 58, 138, 0.25);
        }
        .summary-row.grand-total-row span {
            color: #ffffff !important;
        }

        /* Terms & Signatures */
        .terms-box {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 20px;
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .terms-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 4px;
            letter-spacing: 0.4px;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
            font-size: 10.5px;
            margin-top: 18px;
        }
        .sig-space { height: 55px; }
        .sig-line {
            display: inline-block;
            min-width: 180px;
            border-top: 1.5px solid #1e3a8a;
            padding-top: 4px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Footer Watermark */
        .doc-footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* Responsive Mobile Styles */
        @media screen and (max-width: 768px) {
            .toolbar {
                top: 8px;
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                padding: 5px 8px;
                gap: 5px;
                max-width: calc(100vw - 16px);
                border-radius: 40px;
            }
            .toolbar-btn {
                padding: 6px 10px;
                font-size: 11px;
                gap: 4px;
            }
            .btn-text-full {
                display: none !important;
            }
            .btn-text-short {
                display: inline !important;
            }
            .edit-mode-banner {
                top: 52px;
                font-size: 9.5px;
                padding: 5px 12px;
            }
            .sheet-viewport-wrapper {
                padding-top: 55px;
            }
        }

        /* Print Media Styles (Suppress Browser Header/Footer like URL & Title) */
        @page {
            size: A4 portrait;
            margin: 0mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .toolbar, .edit-mode-banner { display: none !important; }
            .sheet-viewport-wrapper {
                padding: 0 !important;
                overflow: visible !important;
                display: block !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 12mm 15mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Edit Mode Banner -->
    <div id="editModeBanner" class="edit-mode-banner" style="display: none;">
        <i class="bi bi-pencil-fill"></i> Mode Edit Manual Aktif — Klik langsung teks pada surat penawaran untuk mengubahnya.
    </div>

    <!-- Floating Toolbar (Cinematic Glassmorphism) -->
    <div class="toolbar">
        @if($isBranch)
        <div style="display: flex; align-items: center; gap: 6px; border-right: 1px solid rgba(255, 255, 255, 0.2); padding-right: 10px; margin-right: 2px;">
            <span style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; white-space: nowrap;">
                <i class="bi bi-geo-alt-fill me-1"></i> <span class="btn-text-full">Cabang: </span>{{ $entityName }}
            </span>
        </div>
        @elseif(isset($companies) && $companies->count() > 1)
        <div style="display: flex; align-items: center; gap: 6px; border-right: 1px solid rgba(255, 255, 255, 0.2); padding-right: 10px; margin-right: 2px;">
            <label class="btn-text-full" style="color: #94a3b8; font-size: 11px; font-weight: 600; white-space: nowrap;"><i class="bi bi-building me-1 text-primary"></i> PT:</label>
            <select style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; outline: none; cursor: pointer;" onchange="window.location.href='?company_id=' + this.value">
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ $company && $company->id == $c->id ? 'selected' : '' }}>
                        {{ $c->nama_perusahaan }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <button class="toolbar-btn btn-edit-mode" id="btnToggleEditMode" onclick="toggleEditMode()" title="Edit teks langsung di halaman">
            <i class="bi bi-pencil-square"></i> <span class="btn-text-full">Mode Edit Teks</span><span class="btn-text-short">Edit</span>
        </button>

        <button class="toolbar-btn btn-screenshot" id="btnScreenshot" onclick="downloadScreenshot()" title="Download Gambar Surat Penawaran (PNG)">
            <i class="bi bi-camera-fill"></i> <span class="btn-text-full">Simpan Gambar</span><span class="btn-text-short">Gambar</span>
        </button>

        <button class="toolbar-btn btn-print" onclick="prepareAndPrint()" title="Cetak ke PDF / Printer">
            <i class="bi bi-printer-fill"></i> <span class="btn-text-full">Cetak Dokumen (PDF)</span><span class="btn-text-short">Cetak</span>
        </button>

        <button class="toolbar-btn btn-close-preview" onclick="window.close()" title="Tutup Tab">
            <i class="bi bi-x-lg"></i> <span class="btn-text-full">Tutup</span><span class="btn-text-short">Tutup</span>
        </button>
    </div>

    <!-- Sheet Viewport Wrapper for Responsive Scrolling -->
    <div class="sheet-viewport-wrapper">
        <!-- Paper Sheet Container -->
        <div class="paper-sheet" id="printSheet">
            
            <!-- Top Cinematic Accent Bar -->
            <div class="top-accent-bar"></div>

            <!-- Kop Surat -->
            <div class="kop-surat" style="{{ empty($activeLogo) ? 'justify-content: flex-end;' : '' }}">
                @if(!empty($activeLogo))
                <div>
                    <img src="{{ $activeLogo }}" alt="Logo" class="kop-logo">
                </div>
                @endif
                <div class="kop-info">
                    @if(!empty($entityName))
                    <strong class="editable-text" contenteditable="false">{{ $entityName }}</strong>
                    @endif
                    @if(!empty(trim($entityAddress)))
                    <div class="editable-text" contenteditable="false">{{ $entityAddress }}</div>
                    @endif
                    @if(!empty(trim($entityPhone)) || !empty(trim($entityEmail)))
                    <div class="editable-text" contenteditable="false">
                        @if(!empty(trim($entityPhone)))
                        <span>Telp: {{ $entityPhone }}</span>
                        @endif
                        @if(!empty(trim($entityPhone)) && !empty(trim($entityEmail)))
                        <span> | </span>
                        @endif
                        @if(!empty(trim($entityEmail)))
                        <span>Email: {{ $entityEmail }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Document Title -->
            <div class="doc-title-box">
                <h1 class="doc-title editable-text" contenteditable="false">SURAT PENAWARAN HARGA</h1>
                <div class="doc-number editable-text" contenteditable="false">
                    No: {{ $quotation->nomor_quotation }}
                </div>
            </div>

            <!-- Info Grid -->
            @php
                $clientCompany = $quotation->customer?->nama_perusahaan ?: $quotation->customer?->nama_customer;
                $clientPic = ($quotation->customer?->nama_customer && $quotation->customer?->nama_customer !== $quotation->customer?->nama_perusahaan) ? $quotation->customer?->nama_customer : null;
            @endphp
            <div class="info-grid">
                <div class="info-box">
                    <div class="info-box-title"><i class="bi bi-building"></i> Kepada Yth. (Customer / Klien)</div>
                    <div class="editable-text" contenteditable="false" style="font-weight: 800; font-size: 13px; color: #0f172a; margin-bottom: 2px; text-transform: uppercase;">{{ $clientCompany ?? '-' }}</div>
                    @if($clientPic)
                    <div class="editable-text" contenteditable="false" style="color: #334155; font-size: 10px; margin-bottom: 3px; font-weight: 600;"><i class="bi bi-person-badge text-primary me-1"></i>Attn / PIC: <strong>{{ $clientPic }}</strong></div>
                    @endif
                    <div class="editable-text" contenteditable="false" style="color: #475569; font-size: 10px; margin-bottom: 2px;">{{ $quotation->customer?->defaultAddress ? ($quotation->customer->defaultAddress->alamat . ($quotation->customer->defaultAddress->kota ? ', ' . $quotation->customer->defaultAddress->kota : '')) : ($quotation->customer?->alamat_utama ?? '-') }}</div>
                    <div class="editable-text" contenteditable="false" style="color: #64748b; font-size: 9.5px;">Telp: {{ $quotation->customer?->telepon ?? '-' }} | Email: {{ $quotation->customer?->email ?? '-' }}</div>
                    @if($quotation->tender)
                    <div class="editable-text" contenteditable="false" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #bfdbfe; font-size: 10px; color: #1d4ed8; font-weight: 600;">
                        <i class="bi bi-briefcase me-1"></i> Proyek: {{ $quotation->tender->nama_tender }} ({{ $quotation->tender->nomor_kontrak }})
                    </div>
                    @endif
                </div>

                <div class="info-box">
                    <div class="info-box-title"><i class="bi bi-file-earmark-text"></i> Informasi Penawaran</div>
                    <div class="info-row">
                        <span class="info-label">Tanggal:</span>
                        <span class="info-val font-mono editable-text" contenteditable="false">{{ $quotation->tanggal_quotation ? $quotation->tanggal_quotation->format('d F Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Masa Berlaku:</span>
                        <span class="info-val font-mono editable-text" contenteditable="false">{{ $quotation->berlaku_hingga ? $quotation->berlaku_hingga->format('d F Y') : '30 Hari' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Perihal:</span>
                        <span class="info-val editable-text" contenteditable="false">{{ $quotation->perihal ?: 'Penawaran Harga' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Sales / PIC:</span>
                        <span class="info-val editable-text" contenteditable="false">{{ $quotation->sales?->nama_lengkap ?: ($quotation->creator?->name ?? '-') }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="print-table">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th>Nama Barang & Rincian Spesifikasi</th>
                        <th style="width: 75px; text-align: center;">Qty</th>
                        <th style="width: 105px; text-align: right;">Harga Satuan</th>
                        <th style="width: 70px; text-align: right;">Diskon</th>
                        <th style="width: 120px; text-align: right;">Subtotal</th>
                        <th style="width: 65px; text-align: center;">PPN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $idx => $item)
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #0f172a;">{{ $item->nama_barang_custom ?: ($item->barang?->nama_barang ?? '-') }}</div>
                            @if($item->barang)
                            <div style="margin-top: 1px;">
                                <span class="item-code-badge editable-text" contenteditable="false">{{ $item->barang->kode_barang }}</span>
                            </div>
                            @endif
                            @if($item->deskripsi_item)
                            <div class="editable-text" contenteditable="false" style="color: #64748b; font-size: 9px; margin-top: 2px;">{{ $item->deskripsi_item }}</div>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 600;" class="editable-text" contenteditable="false">
                            {{ (float)$item->qty }} <span style="font-weight: 400; color: #64748b; font-size: 9px;">{{ $item->nama_satuan ?: ($item->satuan?->nama_satuan ?? 'Pcs') }}</span>
                        </td>
                        <td style="text-align: right;" class="font-mono editable-text" contenteditable="false">
                            Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;" class="font-mono editable-text" contenteditable="false">
                            {{ (float)$item->diskon_persen > 0 ? (float)$item->diskon_persen . '%' : ($item->diskon_nominal > 0 ? 'Rp ' . number_format($item->diskon_nominal, 0, ',', '.') : '-') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;" class="font-mono editable-text" contenteditable="false">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;" class="font-mono editable-text" contenteditable="false">
                            {{ (float)$item->tarif_ppn > 0 ? (float)$item->tarif_ppn . '%' : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary Grid -->
            <div class="summary-wrapper">
                <div class="terbilang-card">
                    <div class="info-box-title"><i class="bi bi-chat-quote"></i> Terbilang:</div>
                    <div class="editable-text" contenteditable="false" style="font-style: italic; font-weight: 600; color: #1e3a8a; line-height: 1.4;">
                        "{{ $terbilang }}"
                    </div>
                    @if($quotation->paymentTerm)
                    <div class="editable-text" contenteditable="false" style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #bfdbfe; font-size: 9px; color: #475569;">
                        <strong>Ketentuan Pembayaran:</strong> {{ $quotation->paymentTerm->nama_term }} ({{ $quotation->paymentTerm->jumlah_hari }} Hari)
                        @if($quotation->dp_nominal > 0)
                        | <strong style="color: #1d4ed8;">DP: Rp {{ number_format($quotation->dp_nominal, 0, ',', '.') }}</strong>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="summary-card">
                    <div class="summary-row">
                        <span style="color: #64748b;">Subtotal Barang:</span>
                        <span class="font-mono fw-semibold editable-text" contenteditable="false">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($quotation->diskon_nominal > 0)
                    <div class="summary-row" style="color: #dc2626;">
                        <span>Diskon Tambahan{{ (float)$quotation->diskon_persen > 0 ? ' (' . (float)$quotation->diskon_persen . '%)' : '' }}:</span>
                        <span class="font-mono editable-text" contenteditable="false">- Rp {{ number_format($quotation->diskon_nominal, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($quotation->nilai_ppn > 0)
                    <div class="summary-row">
                        <span style="color: #64748b;">Total PPN:</span>
                        <span class="font-mono text-primary editable-text" contenteditable="false">Rp {{ number_format($quotation->nilai_ppn, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($quotation->biaya_pengiriman > 0)
                    <div class="summary-row">
                        <span style="color: #64748b;">Biaya Pengiriman:</span>
                        <span class="font-mono editable-text" contenteditable="false">Rp {{ number_format($quotation->biaya_pengiriman, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="summary-row grand-total-row">
                        <span>GRAND TOTAL:</span>
                        <span class="font-mono editable-text" contenteditable="false">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
                    </div>
                    @if($quotation->dp_nominal > 0)
                    <div class="summary-row" style="color: #2563eb; font-weight: bold; border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 4px;">
                        <span>Minimal Uang Muka (DP{{ (float)$quotation->dp_persen > 0 ? ' ' . (float)$quotation->dp_persen . '%' : '' }}):</span>
                        <span class="font-mono editable-text" contenteditable="false">Rp {{ number_format($quotation->dp_nominal, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Terms & Conditions -->
            @if($quotation->syarat_ketentuan)
            <div class="terms-box">
                <div class="terms-title"><i class="bi bi-card-checklist"></i> Syarat & Ketentuan Penawaran:</div>
                <div class="editable-text" contenteditable="false" style="white-space: pre-line; margin-top: 2px;">{{ $quotation->syarat_ketentuan }}</div>
            </div>
            @endif

            <!-- Signature Blocks -->
            <div class="signature-grid">
                <div>
                    <div style="color: #64748b; margin-bottom: 2px;">Diterima Oleh,</div>
                    <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #1e3a8a;">{{ $clientCompany ?? 'Pihak Pembeli' }}</div>
                    <div class="sig-space"></div>
                    <div class="sig-line editable-text" contenteditable="false">{{ $clientPic ? '( ' . $clientPic . ' )' : '( ........................................ )' }}</div>
                    <div style="color: #94a3b8; font-size: 8.5px; margin-top: 2px;">Tanda Tangan & Stempel Resmi</div>
                </div>

                <div>
                    <div style="color: #64748b; margin-bottom: 2px;">Hormat Kami,</div>
                    <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #1e3a8a;">{{ $entityName }}</div>
                    <div class="sig-space"></div>
                    <div class="sig-line editable-text" contenteditable="false">{{ $quotation->sales?->nama_lengkap ?: ($quotation->creator?->name ?? 'Sales & Marketing') }}</div>
                    <div class="editable-text" contenteditable="false" style="color: #475569; font-size: 9.5px; font-weight: 600; margin-top: 3px;">{{ $quotation->sales?->position?->position_name ?: ($quotation->creator?->karyawan?->position?->position_name ?: 'Marketing / Sales') }}</div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let isEditMode = false;

        // Toggle Live Free-Text Editing Mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const sheet = document.getElementById('printSheet');
            const btn = document.getElementById('btnToggleEditMode');
            const banner = document.getElementById('editModeBanner');
            const editables = document.querySelectorAll('.editable-text');

            if (isEditMode) {
                sheet.classList.add('editing-mode-active');
                btn.classList.add('active');
                btn.querySelector('.btn-text-full').innerHTML = 'Mode Edit: <strong>AKTIF</strong>';
                btn.querySelector('.btn-text-short').innerHTML = '<strong>AKTIF</strong>';
                banner.style.display = 'flex';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                    el.setAttribute('spellcheck', 'false');
                });
            } else {
                sheet.classList.remove('editing-mode-active');
                btn.classList.remove('active');
                btn.querySelector('.btn-text-full').innerText = 'Mode Edit Teks';
                btn.querySelector('.btn-text-short').innerText = 'Edit';
                banner.style.display = 'none';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'false');
                });
            }
        }

        // Prepare and Print
        function prepareAndPrint() {
            if (isEditMode) {
                if (document.activeElement) document.activeElement.blur();
            }
            window.print();
        }

        // Download High-Definition Screenshot PNG
        function downloadScreenshot() {
            const btn = document.getElementById('btnScreenshot');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span class="btn-text-full">Memproses...</span><span class="btn-text-short">...</span>';
            btn.disabled = true;

            if (document.activeElement) document.activeElement.blur();

            const sheet = document.getElementById('printSheet');
            const wasEditMode = isEditMode;
            if (wasEditMode) {
                sheet.classList.remove('editing-mode-active');
            }

            html2canvas(sheet, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                const cleanNo = "{{ str_replace(['/', '\\', ' ', '-'], '_', $quotation->nomor_quotation) }}";
                link.download = `Surat_Penawaran_${cleanNo}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                console.error('Screenshot error:', err);
                alert('Gagal mengambil screenshot dokumen.');
            }).finally(() => {
                if (wasEditMode) {
                    sheet.classList.add('editing-mode-active');
                }
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
