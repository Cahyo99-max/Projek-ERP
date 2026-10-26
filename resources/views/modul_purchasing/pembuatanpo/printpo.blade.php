<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order – {{ $purchaseOrder->nomor_po }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? '' }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? '' }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" defer></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background: #e8eaf0;
            color: #222;
            font-size: 11px;
            line-height: 1.45;
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
            padding: 7px 20px;
            border-radius: 5px;
            font-size: 11.5px; font-weight: 700;
            background: #1a56db;
            color: #fff;
            border: 1px solid #1a56db;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(26,86,219,.24);
            transition: background 0.15s;
        }
        .btn-print:hover { background: #1e429f; }

        .btn-screenshot {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 11.5px; font-weight: 600;
            background: #059669;
            color: #fff;
            border: 1px solid #059669;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(5,150,105,.2);
            transition: background 0.15s;
        }
        .btn-screenshot:hover { background: #047857; }

        /* ── SHEET LAYOUT (A4 Portrait) ── */
        .sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 30px auto;
            padding: 16mm 18mm;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* ── DOC HEADER ── */
        .doc-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .doc-head-title {
            font-size: 24px;
            font-weight: 700;
            color: #111;
            letter-spacing: 0.5px;
        }

        .doc-head-company-name {
            font-size: 14px;
            font-weight: 700;
            font-style: italic;
            color: #333;
            text-align: right;
        }

        .doc-subline {
            border-top: 2.5px solid #111;
            margin-bottom: 16px;
        }

        /* ── META INFO SECTION ── */
        .meta-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 40px;
            margin-bottom: 20px;
        }

        .meta-row {
            display: flex;
            margin-bottom: 5px;
            align-items: flex-start;
        }

        .meta-key {
            width: 110px;
            font-weight: 600;
            color: #444;
            flex-shrink: 0;
        }

        .meta-colon {
            width: 15px;
            color: #444;
            flex-shrink: 0;
            text-align: center;
        }

        .meta-val {
            flex-grow: 1;
            color: #111;
            font-weight: 500;
        }

        /* ── ITEMS TABLE ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead tr {
            background: #1e3a8a;
        }

        .items-table th {
            background: #1e3a8a;
            color: #fff;
            font-weight: 700;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            border: none;
            border-bottom: 2px solid #3b82f6;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table td {
            padding: 8px 10px;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }

        .items-table th.c, .items-table td.c { text-align: center; }
        .items-table th.r, .items-table td.r { text-align: right; }

        .item-name {
            font-weight: 700;
            color: #111;
            margin-bottom: 2px;
        }

        .td-photo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px; height: 46px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .td-photo img {
            max-width: 100%; max-height: 100%;
            object-fit: contain;
        }

        /* ── SUMMARY SECTION ── */
        .summary-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .notes-box {
            flex: 1;
            max-width: 420px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 11px;
            color: #334155;
        }

        .notes-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card {
            width: 340px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 0;
            font-size: 11px;
            white-space: nowrap;
        }

        .summary-key {
            color: #475569;
            font-weight: 500;
            text-align: left;
        }

        .summary-colon {
            width: 10px;
            text-align: center;
            color: #64748b;
        }

        .summary-curr {
            width: 25px;
            text-align: left;
            padding-left: 4px;
            color: #475569;
            font-weight: 500;
        }

        .summary-val {
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }

        .summary-total-row td {
            padding-top: 8px;
            border-top: 1.5px solid #0f172a;
            color: #0f172a;
        }

        .summary-grand-total {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #1e40af !important;
        }

        .summary-sisa-total {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #047857 !important;
        }

        /* ── SIGNATURE ── */
        .sig-wrapper {
            margin-top: auto;
            padding-top: 20px;
            page-break-inside: avoid;
        }

        .sig-divider {
            border: none;
            border-top: 1.5px solid #bbb;
            margin: 0 0 16px;
        }

        .sig-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            page-break-inside: avoid;
        }

        .sig-block {
            width: 30%;
            max-width: 210px;
            text-align: center;
        }

        .sig-role {
            font-size: 11px;
            color: #333;
            margin-bottom: 12px;
            font-weight: 600;
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

        .print-foot-spacer-div {
            height: 0;
        }

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
        
        .edit-mode-active span.editable-text:empty,
        .edit-mode-active div.editable-text:empty {
            display: inline-block;
            min-width: 40px;
            height: 15px;
            background-color: #ffe8e8;
            outline: 1px dashed #dc2626;
        }

        /* ── RESPONSIVE SCREEN LAYOUT ── */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
                background: #e8eaf0;
                overflow-x: auto !important;
            }
            .toolbar {
                position: sticky !important;
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
                width: 794px !important;
                max-width: none !important;
                margin: 0 auto 30px !important;
                padding: 48px 52px 52px !important;
                box-shadow: 0 2px 20px rgba(0,0,0,.12) !important;
                min-height: 1123px;
            }
        }

        /* ── PRINT MEDIA SPECIFIC STYLING ── */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            html, body {
                width: 210mm !important;
                min-width: 210mm !important;
                height: auto !important;
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
                position: relative !important;
                width: 100% !important;
                min-width: 182mm !important;
                min-height: 265mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .doc-head {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }

            .meta-section {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0 40px !important;
            }

            .sig-section {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }

            .sig-block {
                width: 30% !important;
                max-width: 210px !important;
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

            thead {
                display: table-header-group !important;
            }

            tbody {
                display: table-row-group !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .summary-wrapper, .sig-wrapper {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .print-foot-spacer-div {
                height: 0 !important;
                display: none !important;
            }

            .sig-wrapper {
                position: static !important;
                margin-top: auto !important;
                padding-top: 24px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
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
        <a href="/purchase-order" onclick="if(window.history.length > 1 && document.referrer.includes('/purchase-order')) { window.history.back(); return false; }" class="btn-back">
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
                <div class="doc-head-title editable-text">PURCHASE ORDER</div>
            </div>
            <div class="doc-head-company">
                <div class="doc-head-company-name editable-text">
                    {{ $activeBranch ? ($activeBranch->nama_perusahaan ?? $activeBranch->nama_cabang) : ($purchaseOrder->company->nama_perusahaan ?? 'PT. Persada Aman Sentosa') }}
                </div>
            </div>
        </div>
        <div class="doc-subline"></div>

        {{-- ── META INFO ── --}}
        <div class="meta-section">
            {{-- Kolom Kiri --}}
            <div>
                <div class="meta-row">
                    <span class="meta-key">No. Faktur</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->nomor_po }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Tanggal</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->tanggal_po->format('d/m/Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Kepada</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->nama_supplier }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Up.</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->pic ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Alamat</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">
                        <span class="editable-text">{{ $purchaseOrder->supplier->alamat ?? '-' }}</span>
                        @if($purchaseOrder->supplier->kota)
                            <span class="editable-text">, {{ $purchaseOrder->supplier->kota }}</span>
                        @endif
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">No. Telpon</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->telepon ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">No. Fax</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->fax ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">No. HP</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->hp ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Alamat Email</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->email ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Website</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->supplier->website ?? '-' }}</span>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div>
                <div class="meta-row">
                    <span class="meta-key">Tgl. Pengiriman</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->tanggal_pengiriman ? $purchaseOrder->tanggal_pengiriman->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">No. Pesanan</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->pr ? $purchaseOrder->pr->nomor_pr : '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Mata Uang</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->currency->kode_currency }} ({{ $purchaseOrder->currency->nama_currency }})</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Term of Payment</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text">{{ $purchaseOrder->paymentTerm ? $purchaseOrder->paymentTerm->nama_term : 'Cash / COD' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Dikirim Ke</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">
                        @if($activeBranch)
                            <span class="editable-text fw-bold">{{ $activeBranch->nama_cabang }}</span><br>
                            <span class="editable-text" style="font-weight:400; font-size:10px; color:#555;">
                                {{ $activeBranch->address }}
                                @if($activeBranch->city), {{ $activeBranch->city }}@endif
                                @if($activeBranch->province), {{ $activeBranch->province }}@endif
                                @if($activeBranch->postal_code), {{ $activeBranch->postal_code }}@endif
                            </span>
                        @else
                            <span class="editable-text fw-bold">{{ $purchaseOrder->company->nama_perusahaan ?? 'Kantor Pusat' }}</span><br>
                            <span class="editable-text" style="font-weight:400; font-size:10px; color:#555;">
                                {{ $purchaseOrder->company->alamat ?? 'Kantor Pusat' }}
                            </span>
                        @endif
                    </span>
                </div>
                <div class="meta-row mt-2">
                    <span class="meta-key">Transfer Ke</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val editable-text" style="white-space: pre-line; line-height: 1.4;">{{ $purchaseOrder->paymentMethod ? $purchaseOrder->paymentMethod->nama_metode : "BANK BCA\n028-830-8899\na/n PT. Usaha Sukses Sentosa" }}</span>
                </div>
            </div>
        </div>

        {{-- ── TABEL BARANG ── --}}
        <div class="table-responsive-print" style="width: 100%; overflow-x: auto; margin-bottom: 12px;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="c" style="width:4%">No</th>
                        <th style="width:14%">No. Kode Barang</th>
                        <th style="width:34%">Deskripsi</th>
                        <th class="c" style="width:8%">Jumlah</th>
                        <th style="width:8%">Satuan</th>
                        <th class="r" style="width:12%">Harga Satuan</th>
                        <th class="c" style="width:8%">Diskon (%)</th>
                        <th class="r" style="width:12%">Sub Total</th>
                        <th class="c" style="width:4%">Pajak (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->details as $idx => $detail)
                        @php
                            $fotoSrc = null;
                            if ($detail->barang) {
                                $rawPath = $detail->barang->primaryFoto?->foto_path
                                    ?? $detail->barang->fotos->first()?->foto_path;
                                if ($rawPath) {
                                    $localPath = storage_path('app/public/' . $rawPath);
                                    if (file_exists($localPath)) {
                                        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                                        $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg']) ? ($ext === 'jpg' ? 'image/jpeg' : ($ext === 'svg' ? 'image/svg+xml' : 'image/' . $ext)) : 'image/jpeg';
                                        $fotoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localPath));
                                    } else {
                                        $pubPath = public_path('storage/' . $rawPath);
                                        if (file_exists($pubPath)) {
                                            $ext = strtolower(pathinfo($pubPath, PATHINFO_EXTENSION));
                                            $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg']) ? ($ext === 'jpg' ? 'image/jpeg' : ($ext === 'svg' ? 'image/svg+xml' : 'image/' . $ext)) : 'image/jpeg';
                                            $fotoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($pubPath));
                                        } else {
                                            $fotoSrc = asset('storage/' . $rawPath);
                                        }
                                    }
                                }
                            }
                            $discPercent = 0.00;
                            if (floatval($detail->harga_satuan) > 0 && floatval($detail->diskon_item) > 0) {
                                $discPercent = (floatval($detail->diskon_item) / floatval($detail->harga_satuan)) * 100;
                            } elseif (floatval($purchaseOrder->diskon_persen) > 0) {
                                $discPercent = floatval($purchaseOrder->diskon_persen);
                            } elseif (floatval($purchaseOrder->total_diskon) > 0 && floatval($purchaseOrder->subtotal) > 0) {
                                $discPercent = (floatval($purchaseOrder->total_diskon) / floatval($purchaseOrder->subtotal)) * 100;
                            }

                            $satuanPO = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? 'pcs');
                            $satuanDasar = $detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? $satuanPO;
                            $faktor = floatval($detail->faktor_pengali) ?: 1;
                            $qtyPO = floatval($detail->jumlah);
                            $qtyDasar = floatval($detail->jumlah_dasar) ?: ($qtyPO * $faktor);
                        @endphp
                        <tr>
                            <td class="c">{{ $idx + 1 }}</td>
                            <td><span class="editable-text font-monospace">{{ $detail->barang?->kode_barang ?? '-' }}</span></td>
                            <td>
                                <div class="item-name editable-text">{{ $detail->barang?->nama_barang ?? '-' }}</div>
                                @if($detail->barang?->warna)
                                    <div style="font-size: 9px; color: #555; margin-top: 1px;">
                                        Warna: <span class="editable-text">{{ $detail->barang->warna->nama_warna }}</span>
                                    </div>
                                @endif
                                
                                @if($fotoSrc)
                                    <div class="td-photo mt-2">
                                        <img src="{{ $fotoSrc }}" alt="Foto" crossOrigin="anonymous">
                                    </div>
                                @endif
                            </td>
                            <td class="c fw-semibold"><span class="editable-text">{{ rtrim(rtrim(number_format($qtyPO, 4, ',', '.'), '0'), ',') }}</span></td>
                            <td>
                                <span class="editable-text">{{ $satuanPO }}</span>
                                @if($faktor != 1)
                                    <div style="font-size: 8px; color: #64748b; margin-top: 1px; font-style: italic; white-space: nowrap;">
                                        (Setara {{ rtrim(rtrim(number_format($qtyDasar, 4, ',', '.'), '0'), ',') }} {{ $satuanDasar }})
                                    </div>
                                @endif
                            </td>
                            <td class="r"><span class="editable-text">{{ number_format(floatval($detail->harga_satuan), 2, ',', '.') }}</span></td>
                            <td class="c" style="white-space: nowrap;">
                                @php
                                    $itemDiskonTipe = $detail->diskon_tipe ?? 'percent';
                                    $itemDiskonPersen = floatval($detail->diskon_persen ?? 0);
                                    $itemDiskonNominal = floatval($detail->diskon_item ?? 0);
                                @endphp
                                @if($itemDiskonTipe === 'nominal' && $itemDiskonNominal > 0)
                                    <span class="editable-text" style="color: #dc2626; font-weight: 600;">
                                        @if($currSym === 'Rp')
                                            Rp {{ (floor($itemDiskonNominal) == $itemDiskonNominal) ? number_format($itemDiskonNominal, 0, ',', '.') : number_format($itemDiskonNominal, 2, ',', '.') }}
                                        @else
                                            {{ $currSym }} {{ number_format($itemDiskonNominal, 2, ',', '.') }}
                                        @endif
                                    </span>
                                @elseif($itemDiskonPersen > 0)
                                    <span class="editable-text" style="color: #dc2626; font-weight: 600;">
                                        {{ (floor($itemDiskonPersen) == $itemDiskonPersen) ? number_format($itemDiskonPersen, 0) : rtrim(rtrim(number_format($itemDiskonPersen, 2, ',', '.'), '0'), '.') }}%
                                    </span>
                                @elseif($itemDiskonNominal > 0)
                                    <span class="editable-text" style="color: #dc2626; font-weight: 600;">
                                        @if($currSym === 'Rp')
                                            Rp {{ (floor($itemDiskonNominal) == $itemDiskonNominal) ? number_format($itemDiskonNominal, 0, ',', '.') : number_format($itemDiskonNominal, 2, ',', '.') }}
                                        @else
                                            {{ $currSym }} {{ number_format($itemDiskonNominal, 2, ',', '.') }}
                                        @endif
                                    </span>
                                @else
                                    <span class="editable-text text-muted">-</span>
                                @endif
                            </td>
                            <td class="r fw-semibold"><span class="editable-text">{{ number_format(floatval($detail->subtotal), 2, ',', '.') }}</span></td>
                            <td class="c text-muted">
                                <span class="editable-text">
                                    @if(floatval($detail->tarif_ppn) > 0)
                                        {{ rtrim(rtrim(number_format($detail->tarif_ppn, 2, ',', '.'), '0'), ',') }}%
                                    @elseif($detail->ppnPajak)
                                        {{ rtrim(rtrim(number_format($detail->ppnPajak->tarif, 2, ',', '.'), '0'), ',') }}%
                                    @elseif($purchaseOrder->pajak)
                                        {{ rtrim(rtrim(number_format($purchaseOrder->pajak->tarif, 2, ',', '.'), '0'), ',') }}%
                                    @else
                                        -
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── SUMMARY & NOTES BOX ── --}}
        <div class="summary-wrapper">
            {{-- Catatan / Keterangan PO (Left) --}}
            <div class="notes-box">
                <div class="notes-title">Catatan / Keterangan PO:</div>
                <div class="editable-text" style="white-space: pre-line; line-height: 1.4; color: #475569;">{{ $purchaseOrder->keterangan ?: 'Tidak ada catatan tambahan.' }}</div>
            </div>

            {{-- Summary Table Card (Right) --}}
            <div class="summary-card">
                @php
                    $currSym = $purchaseOrder->currency ? $purchaseOrder->currency->simbol : 'Rp';
                    $totalDiskonVal = floatval($purchaseOrder->total_diskon ?: $purchaseOrder->diskon_nominal);
                @endphp
                <table class="summary-table">
                    <tr>
                        <td class="summary-key">Subtotal Barang</td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr">{{ $currSym }}</td>
                        <td class="summary-val"><span class="editable-text">{{ number_format(floatval($purchaseOrder->subtotal), 2, ',', '.') }}</span></td>
                    </tr>
                    @if($totalDiskonVal > 0)
                    <tr>
                        <td class="summary-key" style="color: #dc2626; font-weight: 500;">
                            Total Diskon Barang
                        </td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr" style="color: #dc2626; font-weight: 600;">{{ $currSym }}</td>
                        <td class="summary-val" style="color: #dc2626; font-weight: 600;">- <span class="editable-text">{{ number_format($totalDiskonVal, 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="summary-key" style="font-weight: 600;">DPP (Dasar Pajak)</td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr" style="font-weight: 600;">{{ $currSym }}</td>
                        <td class="summary-val" style="font-weight: 600;"><span class="editable-text">{{ number_format(max(0, floatval($purchaseOrder->subtotal) - $totalDiskonVal), 2, ',', '.') }}</span></td>
                    </tr>
                    <tr>
                        <td class="summary-key">Total PPN (Pajak Barang)</td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr">{{ $currSym }}</td>
                        <td class="summary-val"><span class="editable-text">{{ number_format(floatval($purchaseOrder->total_pajak), 2, ',', '.') }}</span></td>
                    </tr>
                    @if(floatval($purchaseOrder->biaya_lain) > 0)
                    <tr>
                        <td class="summary-key">Biaya Lain @if($purchaseOrder->deskripsi_biaya_lain)({{ $purchaseOrder->deskripsi_biaya_lain }})@endif</td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr">{{ $currSym }}</td>
                        <td class="summary-val"><span class="editable-text">{{ number_format(floatval($purchaseOrder->biaya_lain), 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    @php
                        $pphNomPrint = floatval($purchaseOrder->pph_nominal ?? $purchaseOrder->total_pph ?? 0);
                    @endphp
                    @if($pphNomPrint > 0)
                    <tr>
                        <td class="summary-key" style="color: #b45309 !important;">
                            Potongan {{ $purchaseOrder->pphPajak?->nama_pajak ?: 'PPh' }}
                            @if(floatval($purchaseOrder->tarif_pph) > 0)
                                ({{ rtrim(rtrim(number_format($purchaseOrder->tarif_pph, 2, ',', '.'), '0'), ',') }}%)
                            @endif
                        </td>
                        <td class="summary-colon" style="color: #b45309 !important;">:</td>
                        <td class="summary-curr" style="color: #b45309 !important;">{{ $currSym }}</td>
                        <td class="summary-val" style="color: #b45309 !important;"><span class="editable-text">- {{ number_format($pphNomPrint, 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    <tr class="summary-total-row">
                        <td class="summary-key" style="font-weight: 700;">Grand Total</td>
                        <td class="summary-colon" style="font-weight: 700;">:</td>
                        <td class="summary-curr summary-grand-total">{{ $currSym }}</td>
                        <td class="summary-val summary-grand-total"><span class="editable-text">{{ number_format(floatval($purchaseOrder->grand_total), 2, ',', '.') }}</span></td>
                    </tr>
                    @if(floatval($purchaseOrder->down_payment) > 0)
                    <tr>
                        <td class="summary-key">Dibayar / DP</td>
                        <td class="summary-colon">:</td>
                        <td class="summary-curr" style="color: #d97706 !important;">{{ $currSym }}</td>
                        <td class="summary-val" style="color: #d97706 !important;"><span class="editable-text">{{ number_format(floatval($purchaseOrder->down_payment), 2, ',', '.') }}</span></td>
                    </tr>
                    <tr>
                        <td class="summary-key" style="font-weight: 700; color: #047857;">Sisa Pembayaran</td>
                        <td class="summary-colon" style="font-weight: 700; color: #047857;">:</td>
                        <td class="summary-curr summary-sisa-total">{{ $currSym }}</td>
                        <td class="summary-val summary-sisa-total"><span class="editable-text">{{ number_format(floatval($purchaseOrder->grand_total - $purchaseOrder->down_payment), 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    @if($purchaseOrder->currency && $purchaseOrder->currency->kode_currency !== 'IDR')
                    <tr>
                        <td class="summary-key" style="font-size: 10px; color: #64748b; padding-top: 6px; border-top: 1px dashed #cbd5e1;">Kurs Rupiah</td>
                        <td class="summary-colon" style="font-size: 10px; color: #64748b; padding-top: 6px; border-top: 1px dashed #cbd5e1;">:</td>
                        @php
                            $kursVal = floatval($purchaseOrder->kurs);
                            $kursDecimals = ($kursVal == round($kursVal, 2)) ? 2 : 4;
                        @endphp
                        <td class="summary-curr" style="font-size: 10px; color: #64748b; padding-top: 6px; border-top: 1px dashed #cbd5e1;">Rp</td>
                        <td class="summary-val" style="font-size: 10px; color: #64748b; padding-top: 6px; border-top: 1px dashed #cbd5e1;"><span class="editable-text">{{ number_format($kursVal, $kursDecimals, ',', '.') }}</span></td>
                    </tr>
                    <tr>
                        <td class="summary-key" style="font-weight: 700; color: #334155;">Grand Total (IDR)</td>
                        <td class="summary-colon" style="font-weight: 700; color: #334155;">:</td>
                        <td class="summary-curr" style="font-weight: 700; color: #334155;">Rp</td>
                        <td class="summary-val" style="font-weight: 700; color: #334155;"><span class="editable-text">{{ number_format(floatval($purchaseOrder->grand_total_idr ?: ($purchaseOrder->grand_total * $purchaseOrder->kurs)), 2, ',', '.') }}</span></td>
                    </tr>
                    @if(floatval($purchaseOrder->down_payment) > 0)
                    <tr>
                        <td class="summary-key" style="font-weight: 600; color: #047857; font-size: 10.5px;">Sisa Bayar (IDR)</td>
                        <td class="summary-colon" style="font-weight: 600; color: #047857; font-size: 10.5px;">:</td>
                        <td class="summary-curr" style="font-weight: 600; color: #047857; font-size: 10.5px;">Rp</td>
                        <td class="summary-val" style="font-weight: 600; color: #047857; font-size: 10.5px;"><span class="editable-text">{{ number_format(floatval(($purchaseOrder->grand_total - $purchaseOrder->down_payment) * $purchaseOrder->kurs), 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    @endif
                </table>
            </div>
        </div>

        {{-- Spacer to push signatures --}}
        <div class="print-foot-spacer-div"></div>

        {{-- ── SIGNATURES ── --}}
        <div class="sig-wrapper">
            <hr class="sig-divider">
            <div class="sig-section">
                
                {{-- Dibuat Oleh (Creator) --}}
                @php
                    $creatorSigSrc = null;
                    if ($purchaseOrder->creator?->activeSignature) {
                        $sigPath = $purchaseOrder->creator->activeSignature->signature_path;
                        if (str_starts_with($sigPath, 'data:image')) {
                            $creatorSigSrc = $sigPath;
                        } else {
                            $fullSigPath = public_path($sigPath);
                            if (file_exists($fullSigPath)) {
                                $ext = strtolower(pathinfo($fullSigPath, PATHINFO_EXTENSION));
                                $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                                $creatorSigSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullSigPath));
                            } else {
                                $creatorSigSrc = asset($sigPath);
                            }
                        }
                    }

                    $approverSigSrc = null;
                    if ($purchaseOrder->approver?->activeSignature) {
                        $sigPath = $purchaseOrder->approver->activeSignature->signature_path;
                        if (str_starts_with($sigPath, 'data:image')) {
                            $approverSigSrc = $sigPath;
                        } else {
                            $fullSigPath = public_path($sigPath);
                            if (file_exists($fullSigPath)) {
                                $ext = strtolower(pathinfo($fullSigPath, PATHINFO_EXTENSION));
                                $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                                $approverSigSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullSigPath));
                            } else {
                                $approverSigSrc = asset($sigPath);
                            }
                        }
                    }
                @endphp
                <div class="sig-block">
                    <div class="sig-role">Dibuat Oleh,</div>
                    <div class="sig-box">
                        @if($creatorSigSrc)
                            <img class="sig-image" src="{{ $creatorSigSrc }}" alt="TTD Dibuat" crossOrigin="anonymous">
                        @endif
                    </div>
                    <hr class="sig-line">
                    <div class="sig-name editable-text">{{ $purchaseOrder->creator?->nama_lengkap ?? '..............................' }}</div>
                    <div class="sig-sub editable-text">{{ $purchaseOrder->creator?->position?->position_name ?? 'Staff Purchasing' }}</div>
                </div>

                {{-- Disetujui Oleh (Approver) --}}
                <div class="sig-block">
                    <div class="sig-role">Disetujui Oleh,</div>
                    <div class="sig-box">
                        @if($approverSigSrc)
                            <img class="sig-image" src="{{ $approverSigSrc }}" alt="TTD Disetujui" crossOrigin="anonymous">
                        @elseif($purchaseOrder->approved_at)
                            <div class="text-success fw-bold" style="font-size:10px; color:#10b981;"><i class="bi bi-check-circle-fill me-1"></i>Approved Digital</div>
                            <small class="text-muted" style="font-size:8px; display:block;">{{ \Carbon\Carbon::parse($purchaseOrder->approved_at)->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                    <hr class="sig-line">
                    <div class="sig-name editable-text">{{ $purchaseOrder->approver?->nama_lengkap ?? ($purchaseOrder->approved_at || in_array($purchaseOrder->status, ['Approved', 'Ordered', 'Completed']) ? 'Direktur / Atasan' : '..............................') }}</div>
                    <div class="sig-sub editable-text">{{ $purchaseOrder->approver?->position?->position_name ?? 'Direktur / Management' }}</div>
                </div>

                {{-- Supplier / Penerima (Right) --}}
                <div class="sig-block">
                    <div class="sig-role">Supplier,</div>
                    <div class="sig-box">
                    </div>
                    <hr class="sig-line">
                    <div class="sig-name editable-text">{{ $purchaseOrder->supplier->nama_supplier }}</div>
                    <div class="sig-sub">Mitra Pemesanan</div>
                </div>

            </div>
        </div>

    </div>

    {{-- Use same dependencies (JQuery is already available in head or layout, but let's load it if not exists) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Toggle Photo Visibility
        document.getElementById('toggle-foto').addEventListener('change', function() {
            const photos = document.querySelectorAll('.td-photo');
            photos.forEach(photo => {
                photo.style.display = this.checked ? 'flex' : 'none';
            });
        });

        // Toggle Signature Visibility
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

        // unformat helper
        function parseLocalNum(str) {
            if (!str) return 0;
            let clean = str.replace(/\./g, '').replace(/,/g, '.');
            return parseFloat(clean) || 0;
        }

        // format helper
        function formatLocalNum(num) {
            return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Live calculations if values are edited
        $(document).on('blur', '#biaya_lain_val, #dp_val, #grand_total_cell span', function() {
            recalculateTotals();
        });

        function recalculateTotals() {
            let baseTotal = parseLocalNum('{{ $purchaseOrder->grand_total }}');
            let biayaLain = parseLocalNum($('#biaya_lain_val').text());
            let dp = parseLocalNum($('#dp_val').text());

            let grandTotal = baseTotal + biayaLain;
            let sisa = grandTotal - dp;

            $('#grand_total_cell span').text(formatLocalNum(grandTotal));
            $('#sisa_cell span').text(formatLocalNum(sisa));
        }

        // Native fallback listener
        document.addEventListener('focusout', function(e) {
            if (e.target && (e.target.id === 'biaya_lain_val' || e.target.id === 'dp_val')) {
                recalculateTotals();
            }
        });

        // Screenshot capture support
        document.getElementById('btn-screenshot').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            const sheet = document.querySelector('.sheet');
            
            const editModeChecked = document.getElementById('toggle-edit').checked;
            if (editModeChecked) {
                sheet.classList.remove('edit-mode-active');
            }

            html2canvas(sheet, {
                useCORS: true,
                allowTaint: true,
                scale: 2,
                backgroundColor: '#ffffff',
                imageTimeout: 15000,
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'PO-' + '{{ $purchaseOrder->nomor_po }}'.replace(/\//g, '-') + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                if (editModeChecked) {
                    sheet.classList.add('edit-mode-active');
                }

                btn.disabled = false;
                btn.innerHTML = originalText;
            }).catch(err => {
                console.error('Screenshot capture failed:', err);
                alert('Gagal mengambil screenshot.');
                
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
