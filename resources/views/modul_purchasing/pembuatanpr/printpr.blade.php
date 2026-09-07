<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request – {{ $purchaseRequisition->nomor_pr }}</title>
    <link rel="shortcut icon" href="{{ $appLogo }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background: #e8eaf0;
            color: #222;
            font-size: 12px;
            line-height: 1.5;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
            max-width: 650px;
        }

        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 11.5px; font-weight: 600;
            background: #fff;
            color: #444;
            border: 1px solid #ccc;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 11.5px; font-weight: 700;
            background: #1a56db;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .btn-screenshot {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 11.5px; font-weight: 700;
            background: #059669;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        /* ── A4 SHEET ── */
        .sheet {
            max-width: 794px;
            min-height: 1123px; /* A4 height at 96 DPI */
            margin: 30px auto 50px;
            background: #fff;
            box-shadow: 0 2px 20px rgba(0,0,0,.12);
            padding: 48px 52px 52px;
            display: flex;
            flex-direction: column;
        }

        .sig-wrapper {
            margin-top: auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ── DOCUMENT HEADER ── */
        .doc-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            border-bottom: 3px solid #222;
        }

        .doc-head-title {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: .5px;
            color: #111;
            line-height: 1;
        }

        .doc-head-subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }

        .doc-head-company {
            text-align: right;
        }

        .doc-head-company-name {
            font-size: 14px;
            font-weight: 700;
            color: #111;
            font-style: italic;
        }

        .doc-head-company-meta {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
            line-height: 1.55;
        }

        .doc-subline {
            height: 1px;
            background: #aaa;
            margin-top: 3px;
        }

        /* ── META INFO ── */
        .meta-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 32px;
            margin: 16px 0 20px;
        }

        .meta-row {
            display: flex;
            padding: 3px 0;
            font-size: 11.5px;
        }

        .meta-key {
            flex: 0 0 96px;
            color: #333;
            font-weight: 400;
        }

        .meta-colon {
            flex: 0 0 14px;
            color: #333;
        }

        .meta-val {
            flex: 1;
            font-weight: 600;
            color: #111;
        }

        /* ── TABLE ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            border: none;
        }

        .items-table thead tr {
            background: #1e3a8a;
        }

        .items-table thead th {
            background: #1e3a8a;
            padding: 8px 10px;
            font-size: 9.5px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border: none;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            text-align: left;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table thead th:last-child {
            border-right: none;
        }

        .items-table thead th.c { text-align: center; }

        .items-table tbody tr {
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .items-table tbody td {
            padding: 8px 10px;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            color: #334155;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table tbody td.c { text-align: center; }

        .td-no { text-align: center; font-weight: 600; color: #475569; }

        .td-photo {
            width: auto;
            max-width: 60px;
            height: auto;
            border: none;
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            margin-top: 4px;
            display: inline-block;
        }

        .td-photo img { 
            max-width: 60px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain; 
            border: none;
            background: transparent;
            box-shadow: none;
            display: block;
        }
        .td-photo-ph { font-size: 22px; color: #c0cdd8; }

        .item-name { font-weight: 600; color: #111; }
        .item-note { color: #555; font-size: 11px; margin-top: 2px; }

        /* ── CATATAN PR ── */
        .pr-note-box {
            border-left: 3px solid #3a87b0;
            padding: 8px 12px;
            background: #f3f8fb;
            margin-bottom: 20px;
            font-size: 11.5px;
        }

        .pr-note-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #3a87b0;
            margin-bottom: 3px;
        }

        /* ── TABLE NOTE & SPACER ── */
        .table-note-row td {
            background: #fdfefe !important;
            padding: 10px 12px !important;
            border: 1px solid transparent !important;
            text-align: left;
        }

        .print-foot-spacer td {
            border: none !important;
            padding: 0 !important;
        }

        .print-foot-spacer-div {
            height: 0;
        }

        /* ── SIGNATURE ── */
        .sig-divider {
            border: none;
            border-top: 1px solid #bbb;
            margin: 30px 0 24px;
        }

        .sig-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sig-block {
            width: 210px;
            text-align: center;
        }

        .sig-role {
            font-size: 11px;
            color: #333;
            margin-bottom: 12px;
        }

        .sig-box {
            height: 80px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 6px;
        }

        .sig-box img {
            max-height: 76px;
            max-width: 180px;
            object-fit: contain;
        }

        .sig-line {
            border: none;
            border-top: 1px solid #333;
            margin: 0 0 6px;
        }

        .sig-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #111;
        }

        .sig-sub {
            font-size: 10px;
            color: #666;
            margin-top: 1px;
        }

        .sig-empty {
            font-size: 10px;
            color: #bbb;
            font-style: italic;
        }

        .stamp {
            display: inline-flex; align-items: center;
            padding: 4px 14px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transform: rotate(-8deg);
        }

        .stamp-approved { border: 2.5px solid #059669; color: #059669; }
        .stamp-rejected { border: 2.5px solid #dc2626; color: #dc2626; }
        .stamp-pending  { border: 2px dashed #aaa; color: #aaa; }

        /* ── EDIT MODE STYLING ── */
        .edit-mode-active .editable-text {
            outline: 1px dashed #1a56db;
            padding: 1px 3px;
            border-radius: 3px;
            cursor: text;
            background-color: #fffdf0; /* light yellow tint */
            transition: all 0.2s ease;
        }
        
        .edit-mode-active .editable-text:hover {
            background-color: #fff9db;
            outline: 1px solid #1a56db;
        }
        
        .edit-mode-active .editable-text:focus {
            outline: 2px solid #1a56db;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(26, 86, 219, 0.3);
        }
        
        /* Make sure empty fields have a clickable width and distinct visual */
        .edit-mode-active span.editable-text:empty,
        .edit-mode-active div.editable-text:empty {
            display: inline-block;
            min-width: 30px;
            height: 15px;
            background-color: #ffe8e8;
            outline: 1px dashed #dc2626;
        }

        /* ── RESPONSIVE SCREEN LAYOUT (Tablet & Mobile) ── */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
                background: #e8eaf0;
                overflow-x: auto !important; /* Enable horizontal scroll for A4 sheet on mobile */
            }
            .toolbar {
                position: sticky !important; /* Keep floating at the top of device screen */
                left: 10px;
                top: 10px;
                width: calc(100% - 20px);
                max-width: none !important;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
                margin-bottom: 16px;
                z-index: 999;
            }
            .sheet {
                width: 794px !important; /* Force exact A4 desktop width */
                max-width: none !important;
                margin: 0 auto 30px !important;
                padding: 48px 52px 52px !important; /* Original desktop padding */
                box-shadow: 0 2px 20px rgba(0,0,0,.12) !important;
                min-height: 1123px;
            }
            .meta-section {
                grid-template-columns: 1fr 1fr !important; /* 2 columns like desktop */
                gap: 0 32px !important;
            }
            .sig-section {
                flex-direction: row !important; /* side-by-side like desktop */
                justify-content: space-between !important;
            }
            .sig-block {
                width: 220px !important;
            }
            .items-table {
                font-size: 11.5px !important;
            }
            .items-table thead th, 
            .items-table tbody td {
                padding: 8px 10px !important;
            }
        }

        @media screen and (max-width: 480px) {
            /* Override doc-head vertical stack, keep desktop layout */
            .doc-head {
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                text-align: left !important;
            }
            .doc-head-company {
                text-align: right !important;
            }
            .items-table thead th:nth-child(2), 
            .items-table tbody td:nth-child(2) {
                display: table-cell !important; /* Keep product code column */
            }
        }

        /* ── PRINT ── */
        @media print {
            @page {
                size: A4;
                margin: 0; /* Hides default browser header (title, date) and footer (URL) */
            }

            html, body {
                width: 210mm !important;
                min-width: 210mm !important; /* Force desktop width on print viewport to prevent squeezing */
                height: auto !important; /* Let document flow naturally to support multiple pages */
                margin: 0 !important;
                padding: 12mm 14mm !important;
                background: #fff !important;
                overflow: visible !important;
            }

            .toolbar { display: none !important; }

            .table-responsive-print {
                overflow: visible !important;
            }

            .sheet {
                position: static !important;
                width: 100% !important;
                min-width: 182mm !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                display: block !important;
            }

            /* Force A4 layout even if printed from mobile screen state */
            .doc-head {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                text-align: left !important;
            }

            .doc-head-company {
                text-align: right !important;
            }

            .meta-section {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0 32px !important;
                margin: 16px 0 20px !important;
            }

            .sig-section {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
            }

            .sig-block {
                width: 180px !important;
                text-align: center !important;
            }

            .items-table {
                font-size: 11.5px !important;
            }

            .items-table thead th:nth-child(2), 
            .items-table tbody td:nth-child(2) {
                display: table-cell !important; /* Force show product code column on print */
            }

            .items-table thead tr {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #1e3a8a !important;
            }

            .items-table thead th {
                background: #1e3a8a !important;
                color: #ffffff !important;
                border-bottom: 2px solid #3b82f6 !important;
                border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            }

            .items-table thead th:last-child {
                border-right: none !important;
            }

            tr { page-break-inside: avoid; }

            .print-foot-spacer-div {
                height: 240px !important; /* Reserves 240px space at the bottom of the table on each page for TTD */
            }

            .sig-wrapper {
                position: fixed !important;
                bottom: 22mm !important; /* Safe print margin boundary to prevent bottom clipping */
                left: 14mm !important; /* Align with left body padding */
                right: 14mm !important; /* Align with right body padding */
                z-index: 9999 !important; /* Bring to front to prevent sheet background from hiding signatures */
                padding: 0 !important; /* Reset screen view padding for print layout */
                margin-top: 0 !important;
                page-break-inside: avoid;
                break-inside: avoid;
                background: #fff !important;
            }

            .edit-mode-active .editable-text {
                outline: none !important;
                background-color: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="toolbar">
        <a href="/purchase-requisition" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <label class="btn-back" style="cursor: pointer; user-select: none;">
            <input type="checkbox" id="toggle-foto" checked style="margin-right: 6px; cursor: pointer;"> Tampilkan Foto
        </label>
        <label class="btn-back" style="cursor: pointer; user-select: none;">
            <input type="checkbox" id="toggle-ttd" checked style="margin-right: 6px; cursor: pointer;"> Tampilkan TTD
        </label>
        <label class="btn-back" style="cursor: pointer; user-select: none; background: #fffdf0; border-color: #d1b44c;">
            <input type="checkbox" id="toggle-edit" style="margin-right: 6px; cursor: pointer;"> Mode Edit
        </label>
        <button id="btn-screenshot" class="btn-screenshot">
            <i class="bi bi-camera-fill"></i> Ambil Screenshot
        </button>
        <button onclick="doPrint()" class="btn-print">
            <i class="bi bi-printer-fill"></i> Cetak / PDF
        </button>
    </div>

    <div class="sheet">

        {{-- ── HEADER ── --}}
        <div class="doc-head">
            <div>
                <div class="doc-head-title editable-text">PURCHASE REQUEST</div>
                <div class="doc-head-subtitle editable-text">Surat Permintaan Pembelian</div>
            </div>
            <div class="doc-head-company">
                <div class="doc-head-company-name editable-text">
                    {{ $activeCompany?->nama_perusahaan ?? ($purchaseRequisition->company->nama_perusahaan ?? ($activeBranch ? ($activeBranch->nama_perusahaan ?? $activeBranch->nama_cabang) : 'PASTAC ERP')) }}
                </div>

            </div>
        </div>
        <div class="doc-subline"></div>

        {{-- ── META INFO ── --}}
        <div class="meta-section">

            {{-- Kolom kiri --}}
            <div>
                <div class="meta-row">
                    <span class="meta-key">Nomor</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseRequisition->nomor_pr }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Tanggal</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ \Carbon\Carbon::parse($purchaseRequisition->tanggal_pr)->format('d/m/Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Kepada</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseRequisition->requester?->nama_lengkap ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Departemen</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseRequisition->department?->department_name ?? '-' }}</span>
                </div>
            </div>

            {{-- Kolom kanan --}}
            <div>
                <div class="meta-row">
                    <span class="meta-key">Tgl. Pengiriman</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ \Carbon\Carbon::parse($purchaseRequisition->tanggal_dibutuhkan)->format('d/m/Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Dikirim Ke</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">
                        <span class="editable-text" style="font-weight:700;">{{ $activeCompany?->nama_perusahaan ?? ($purchaseRequisition->company?->nama_perusahaan ?? ($activeBranch?->nama_cabang ?? 'Kantor Pusat')) }}</span><br>
                        @php
                            $targetEntity = $activeCompany ?? ($purchaseRequisition->company ?? $activeBranch);
                        @endphp
                        @if($targetEntity && $targetEntity->address)
                            <span class="editable-text" style="font-weight:400; font-size:11px; color:#555;">
                                {{ $targetEntity->address }}
                                @if($targetEntity->city)
                                    , {{ $targetEntity->city }}
                                @endif
                                @if($targetEntity->province)
                                    , {{ $targetEntity->province }}
                                @endif
                                @if($targetEntity->postal_code)
                                    , {{ $targetEntity->postal_code }}
                                @endif
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ── TABEL BARANG ── --}}
        <div class="table-responsive-print" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 24px;">
            <table class="items-table" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th class="c" style="width:4%">No</th>
                    <th style="width:12%">Kode Barang</th>
                    <th style="width:34%">Deskripsi</th>
                    <th class="c" style="width:12%">Jumlah</th>
                    <th style="width:10%">Satuan</th>
                    <th style="width:28%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseRequisition->details as $idx => $detail)
                    @php
                        $fotoPath = null;
                        if ($detail->barang) {
                            $fotoPath = $detail->barang->primaryFoto?->foto_path
                                ?? $detail->barang->fotos->first()?->foto_path;
                        }
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td class="td-no">{{ $idx + 1 }}.</td>

                        {{-- Kode --}}
                        <td><span class="editable-text">{{ $detail->barang?->kode_barang ?? '-' }}</span></td>

                        {{-- Deskripsi --}}
                        <td>
                            <div class="item-name editable-text">{{ $detail->barang?->nama_barang ?? '-' }}</div>
                            @if($detail->barang?->warna)
                                <div style="font-size: 10px; color: #555; margin-top: 2px;">
                                    <span class="editable-text">{{ $detail->barang->warna->nama_warna }}</span>
                                </div>
                            @endif

                            @if($fotoPath)
                                <div class="td-photo" style="margin-top:6px;">
                                    <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto">
                                </div>
                            @endif
                        </td>

                        {{-- Jumlah --}}
                        <td class="c" style="font-weight:600; vertical-align: middle;">
                            <span class="editable-text" style="font-size: 12px; font-weight: 700;">{{ floatval($detail->jumlah) == intval($detail->jumlah) ? number_format(floatval($detail->jumlah), 0, ',', '.') : rtrim(rtrim(number_format(floatval($detail->jumlah), 4, ',', '.'), '0'), ',') }}</span>
                            @if(abs(($detail->faktor_pengali ?? 1) - 1) > 0.00001 && $detail->barang?->satuan)
                                @php
                                    $jd = floatval($detail->jumlah_dasar ?? ($detail->jumlah * $detail->faktor_pengali));
                                    $formattedJd = $jd == intval($jd) ? number_format($jd, 0, ',', '.') : rtrim(rtrim(number_format($jd, 4, ',', '.'), '0'), ',');
                                    $satuanDasarNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                                @endphp
                                <div style="font-size: 8.5px; color: #475569; font-weight: 500; margin-top: 3px; white-space: nowrap; line-height: 1.2;">
                                    Setara: {{ $formattedJd }} {{ $satuanDasarNama }}
                                </div>
                            @endif
                        </td>

                        {{-- Satuan --}}
                        <td style="vertical-align: middle;"><span class="editable-text">{{ $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? 'pcs') }}</span></td>

                        {{-- Keterangan --}}
                        <td>
                            <span class="editable-text">
                                @if($detail->keterangan)
                                    {{ $detail->keterangan }}
                                @else
                                    —
                                @endif
                            </span>
                        </td>
                    </tr>
                @endforeach


            </tbody>
            <tfoot>
                <tr class="print-foot-spacer">
                    <td colspan="6" style="border: none; padding: 0;">
                        <div class="print-foot-spacer-div"></div>
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>

    {{-- ── TANDA TANGAN ── --}}
    <div class="sig-wrapper">
            <hr class="sig-divider">

            <div class="sig-section">

                {{-- 1. Dibuat Oleh (Purchasing / Pembuat) --}}
                <div class="sig-block">
                    <div class="sig-role editable-text">Dibuat Oleh,</div>
                    <div class="sig-box">
                        @if($purchaseRequisition->creator?->activeSignature)
                            <img src="{{ asset($purchaseRequisition->creator->activeSignature->signature_path) }}" alt="TTD">
                        @endif
                    </div>
                    <hr class="sig-line">
                    <div class="sig-name editable-text">{{ $purchaseRequisition->creator?->nama_lengkap ?? '..............................' }}</div>
                    <div class="sig-sub editable-text">{{ $purchaseRequisition->creator?->position?->position_name ?? 'Staff Purchasing' }}</div>
                </div>

                {{-- 2. Disetujui Oleh (Pimpinan) --}}
                <div class="sig-block">
                    <div class="sig-role editable-text">Disetujui (Pimpinan),</div>
                    <div class="sig-box">
                        @if(in_array($purchaseRequisition->status, ['Approved', 'Closed']))
                            @if($purchaseRequisition->approver?->activeSignature)
                                <img src="{{ asset($purchaseRequisition->approver->activeSignature->signature_path) }}" alt="TTD Approver">
                            @else
                                <span class="stamp stamp-approved">DISETUJUI</span>
                            @endif
                        @elseif($purchaseRequisition->status === 'Rejected')
                            <span class="stamp stamp-rejected">DITOLAK</span>
                        @endif
                    </div>
                    <hr class="sig-line">
                    <div class="sig-name editable-text">
                        @if(in_array($purchaseRequisition->status, ['Approved', 'Closed']) && $purchaseRequisition->approver)
                            {{ $purchaseRequisition->approver->nama_lengkap }}
                        @else
                            ..............................
                        @endif
                    </div>
                    <div class="sig-sub editable-text">
                        @if(in_array($purchaseRequisition->status, ['Approved', 'Closed']) && $purchaseRequisition->approver)
                            {{ $purchaseRequisition->approver->position?->position_name ?? 'Manager / Director' }}
                        @else
                            Manager / Director
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>{{-- /sheet --}}

    <script>
        document.getElementById('toggle-foto').addEventListener('change', function() {
            const photos = document.querySelectorAll('.td-photo');
            photos.forEach(photo => {
                photo.style.display = this.checked ? 'flex' : 'none';
            });
        });

        // Toggle TTD (Signature) Visibility
        document.getElementById('toggle-ttd').addEventListener('change', function() {
            const sigBoxes = document.querySelectorAll('.sig-box');
            sigBoxes.forEach(box => {
                box.style.visibility = this.checked ? 'visible' : 'hidden';
            });
        });

        // Toggle Edit Mode
        document.getElementById('toggle-edit').addEventListener('change', function() {
            const editables = document.querySelectorAll('.editable-text');
            const sheet = document.querySelector('.sheet');
            
            if (this.checked) {
                sheet.classList.add('edit-mode-active');
                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                });
            } else {
                sheet.classList.remove('edit-mode-active');
                editables.forEach(el => {
                    el.removeAttribute('contenteditable');
                });
            }
        });

        document.getElementById('btn-screenshot').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            const sheet = document.querySelector('.sheet');
            
            // Check if edit mode is active, and temporarily disable the styling class for screenshot
            const editModeChecked = document.getElementById('toggle-edit').checked;
            if (editModeChecked) {
                sheet.classList.remove('edit-mode-active');
            }

            html2canvas(sheet, {
                useCORS: true,
                scale: 2, // High resolution (retina support)
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'PR-' + '{{ $purchaseRequisition->nomor_pr }}'.replace(/\//g, '-') + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                // Restore edit mode styling if it was active
                if (editModeChecked) {
                    sheet.classList.add('edit-mode-active');
                }

                btn.disabled = false;
                btn.innerHTML = originalText;
            }).catch(err => {
                console.error('Screenshot capture failed:', err);
                alert('Gagal mengambil screenshot.');
                
                // Restore edit mode styling on failure too
                if (editModeChecked) {
                    sheet.classList.add('edit-mode-active');
                }

                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Universal print helper to ensure all images & signatures are loaded before opening print preview
        function doPrint() {
            const originalTitle = document.title;
            document.title = ' ';
            
            const images = Array.from(document.querySelectorAll('img'));
            const imagePromises = images.map(img => {
                if (img.complete && img.naturalHeight !== 0) {
                    return Promise.resolve();
                }
                return new Promise(resolve => {
                    img.onload = resolve;
                    img.onerror = resolve;
                });
            });

            Promise.all(imagePromises).then(() => {
                setTimeout(() => {
                    window.print();
                    setTimeout(() => {
                        document.title = originalTitle;
                    }, 500);
                }, 100);
            });
        }

        // Fallback listeners for browser print shortcut (Ctrl+P)
        let originalTitle = document.title;
        window.addEventListener('beforeprint', function() {
            originalTitle = document.title;
            document.title = ' ';
        });
        window.addEventListener('afterprint', function() {
            document.title = originalTitle;
        });
    </script>
</body>
</html>
