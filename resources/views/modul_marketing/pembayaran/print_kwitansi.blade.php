<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Kas – {{ $payment->nomor_pembayaran }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f0f4f8;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ── FLOATING TOOLBAR ── */
        .toolbar {
            position: fixed;
            top: 20px;
            right: 24px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(12px);
            padding: 8px 14px;
            border-radius: 50px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 11.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: #f8fafc;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .btn-print {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .btn-print:hover {
            background: linear-gradient(135deg, #1e40af, #1d4ed8);
            transform: translateY(-1px);
        }

        .btn-download {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }
        .btn-download:hover {
            background: linear-gradient(135deg, #0369a1, #075985);
            transform: translateY(-1px);
        }

        /* ── VIEWPORT WRAPPER (Auto-Fit for Mobile, Tablet, PC) ── */
        .sheet-viewport-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 75px 12px 35px 12px;
            overflow-x: hidden;
            transition: min-height 0.2s ease;
        }

        /* ── A4 SHEET CONTAINER ── */
        .sheet-container {
            width: 210mm;
            min-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 16mm 18mm;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            border-radius: 6px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-top: 5px solid #1d4ed8;
            transform-origin: top center;
            -webkit-transform-origin: top center;
            transition: transform 0.15s ease-out;
        }

        @media screen and (max-width: 860px) {
            .toolbar {
                top: 10px !important;
                right: auto !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: calc(100% - 20px) !important;
                max-width: 460px !important;
                justify-content: center !important;
                padding: 6px 10px !important;
                gap: 6px !important;
            }
            .btn-action {
                padding: 6px 11px !important;
                font-size: 10.5px !important;
                gap: 4px !important;
            }
            .sheet-viewport-wrapper {
                padding-top: 60px !important;
                padding-left: 6px !important;
                padding-right: 6px !important;
                padding-bottom: 25px !important;
            }
        }

        /* ── HEADER ── */
        .header-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid #1d4ed8;
            margin-bottom: 20px;
        }

        .company-profile {
            max-width: 62%;
        }

        .company-name {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.3px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .company-meta {
            font-size: 10px;
            color: #475569;
            line-height: 1.45;
        }

        .doc-profile {
            text-align: right;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .doc-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-posted {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }
        .status-diajukan {
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #7dd3fc;
        }
        .status-draft {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-ditolak {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .status-cancelled {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        /* ── CARDS GRID ── */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #2563eb;
        }

        .info-card.customer::before {
            background: #1d4ed8;
        }
        .info-card.payment::before {
            background: #0284c7;
        }

        .card-header-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #1e40af;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #bfdbfe;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 10.5px;
        }
        .card-row:last-child {
            margin-bottom: 0;
        }

        .card-label {
            width: 110px;
            color: #64748b;
            font-weight: 500;
            flex-shrink: 0;
        }

        .card-val {
            flex: 1;
            font-weight: 600;
            color: #0f172a;
        }

        /* ── TABLE STYLING ── */
        .table-wrap {
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            table-layout: fixed;
        }

        table.items-table th {
            background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 6px;
            border: 1px solid #1e40af;
            font-size: 9px;
            line-height: 1.3;
        }

        table.items-table td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
            word-break: break-word;
            line-height: 1.35;
        }

        table.items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
        }

        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .text-danger { color: #dc2626 !important; }
        .text-primary { color: #1d4ed8 !important; }
        .text-muted { color: #64748b !important; }

        /* ── SUMMARY SECTION ── */
        .summary-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
            align-items: start;
        }

        .catatan-box {
            font-size: 10px;
            color: #0c4a6e;
            background: #f0f7ff;
            border: 1px solid #bae6fd;
            border-left: 3.5px solid #0284c7;
            padding: 8px 12px;
            border-radius: 6px;
            line-height: 1.5;
        }

        .totals-card {
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.04);
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 12px;
            font-size: 10.5px;
            border-bottom: 1px solid #f1f5f9;
        }

        .totals-row.grand {
            background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
            color: #ffffff;
            padding: 12px 14px;
            border-bottom: none;
        }

        .totals-row.grand .grand-label {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .totals-row.grand .grand-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        /* ── FOOTER ── */
        .doc-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8.5px;
            color: #64748b;
        }

        /* ── PRINT RULES ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html, body {
                width: 210mm !important;
                min-width: 210mm !important;
                height: auto !important;
                margin: 0 !important;
                padding: 10mm 14mm !important;
                background: #ffffff !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet-viewport-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                display: block !important;
                min-height: auto !important;
            }

            .sheet-container {
                width: 100% !important;
                min-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                border-top: none !important;
                transform: none !important;
                -webkit-transform: none !important;
            }
        }
    </style>
</head>
<body>

    @php
        $curCode   = strtoupper($payment->currency?->kode_currency ?? 'IDR');
        $curSymbol = $payment->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
        $kurs      = (float) ($payment->kurs ?? 1);
        if ($kurs <= 0) {
            $kurs = 1;
        }
        $isForeign = ($curCode !== 'IDR');

        $statusClass = match(strtolower($payment->status)) {
            'posted'    => 'status-posted',
            'diajukan'  => 'status-diajukan',
            'draft'     => 'status-draft',
            'ditolak'   => 'status-ditolak',
            default     => 'status-cancelled',
        };

        $statusIcon = match(strtolower($payment->status)) {
            'posted'    => 'bi-check-circle-fill',
            'diajukan'  => 'bi-clock-fill',
            'draft'     => 'bi-pencil-fill',
            'ditolak'   => 'bi-x-circle-fill',
            default     => 'bi-slash-circle-fill',
        };

        // Resolusi Perusahaan & Cabang Aktif saat Login
        $displayCompany = $activeCompany ?? ($payment->company ?? (auth()->user()?->companies()->first() ?? \App\Models\Company::first()));
        $displayBranch  = $activeBranch ?? $payment->branch;

        $isBranch = !empty($displayBranch);

        if ($isBranch) {
            $rawBranch = trim($displayBranch->nama_cabang ?? session('active_branch_name', ''));
            if (empty($rawBranch)) {
                $rawBranch = 'Cabang Operasional';
            }
            $entityTitle    = stripos($rawBranch, 'cabang') === 0 
                ? mb_strtoupper($rawBranch) 
                : 'CABANG ' . mb_strtoupper($rawBranch);
            $entitySubtitle = 'Kantor Cabang';

            // Alamat Cabang dengan fallback PT jika data cabang kosong
            $addrList = array_filter([
                $displayBranch->address,
                $displayBranch->city,
                $displayBranch->province,
                $displayBranch->postal_code,
            ]);
            if (empty($addrList) && !empty($displayCompany)) {
                $addrList = array_filter([
                    $displayCompany->address,
                    $displayCompany->city,
                    $displayCompany->province,
                    $displayCompany->postal_code,
                ]);
            }
            $entityAddress = !empty($addrList) ? implode(', ', $addrList) : 'Jl Cinere Raya No 40-41, Depok, Jawa Barat';
            $entityPhone   = $displayBranch->phone ?: ($displayCompany?->phone ?: '-');
            $entityEmail   = $displayBranch->email ?: ($displayCompany?->email ?: null);
        } else {
            $rawCompany     = trim($displayCompany?->nama_perusahaan ?? session('active_company_name', 'PT PERSADA AMAN SENTOSA'));
            $entityTitle    = mb_strtoupper($rawCompany ?: 'PT PERSADA AMAN SENTOSA');
            $entitySubtitle = 'Kantor Pusat';

            $addrList = array_filter([
                $displayCompany?->address,
                $displayCompany?->city,
                $displayCompany?->province,
                $displayCompany?->postal_code,
            ]);
            $entityAddress = !empty($addrList) ? implode(', ', $addrList) : 'Jl Cinere Raya No 40-41, Depok, Jawa Barat';
            $entityPhone   = $displayCompany?->phone ?? '-';
            $entityEmail   = $displayCompany?->email ?? null;
        }

        // Resolusi Nama Pelanggan
        $instansi = trim($payment->customer?->nama_perusahaan ?? '');
        $person   = trim($payment->customer?->nama_customer ?? '');
        if ($instansi && $person && strcasecmp($instansi, $person) !== 0) {
            $customerName = $instansi . ' (' . $person . ')';
        } else {
            $customerName = $instansi ?: ($person ?: 'Pelanggan Umum');
        }
    @endphp

    <!-- FLOATING TOOLBAR -->
    <div class="toolbar">
        <button type="button" class="btn-action btn-back" onclick="window.close(); if(!window.closed){ window.history.back(); }">
            <i class="bi bi-arrow-left"></i> Kembali
        </button>
        <button type="button" class="btn-action btn-download" id="btnDownloadImage">
            <i class="bi bi-image"></i> Simpan Gambar
        </button>
        <button type="button" class="btn-action btn-print" onclick="window.print();">
            <i class="bi bi-printer-fill"></i> Cetak Dokumen
        </button>
    </div>

    <!-- MAIN A4 SHEET WRAPPER (FOR MOBILE & TABLET AUTO-SCALE) -->
    <div class="sheet-viewport-wrapper" id="sheetViewportWrapper">
        <div class="sheet-container" id="printableSheet">
        
        <div>
            <!-- HEADER -->
            <div class="header-wrap">
                <div class="company-profile">
                    <div>
                        <div class="company-name">{{ $entityTitle }}</div>
                    </div>
                </div>

                <div class="doc-profile">
                    <div class="doc-title">BUKTI PENERIMAAN KAS</div>
                    <div class="doc-number">{{ $payment->nomor_pembayaran }}</div>
                    <div>
                        <span class="status-pill {{ $statusClass }}">
                            <i class="bi {{ $statusIcon }}"></i>
                            {{ strtoupper($payment->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- CARDS GRID -->
            <div class="cards-grid">
                <!-- CUSTOMER INFO -->
                <div class="info-card customer">
                    <div class="card-header-label">
                        <i class="bi bi-person-badge"></i> Diterima Dari (Pelanggan)
                    </div>
                    <div class="card-row">
                        <span class="card-label">Nama Pelanggan:</span>
                        <span class="card-val text-primary">{{ $customerName }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Kontak / Telp:</span>
                        <span class="card-val">{{ $payment->customer?->telepon ?? '-' }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Alamat:</span>
                        <span class="card-val">{{ $payment->customer?->alamat ?? '-' }}</span>
                    </div>
                </div>

                <!-- PAYMENT INFO -->
                <div class="info-card payment">
                    <div class="card-header-label">
                        <i class="bi bi-credit-card-2-front"></i> Rincian &amp; Sumber Dana
                    </div>
                    <div class="card-row">
                        <span class="card-label">Tanggal Bayar:</span>
                        <span class="card-val">{{ $payment->tanggal_bayar ? \Carbon\Carbon::parse($payment->tanggal_bayar)->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Akun Kas / Bank:</span>
                        <span class="card-val">{{ $payment->kasBank?->nama_akun ?? '-' }} ({{ $payment->kasBank?->nomor_rekening ?? 'Kas Tunai' }})</span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Metode / Ref:</span>
                        <span class="card-val">{{ $payment->paymentMethod?->nama_metode ?? 'Transfer Bank' }} {{ $payment->nomor_referensi ? '• Ref: ' . $payment->nomor_referensi : '' }}</span>
                    </div>
                    @if($isForeign)
                    <div class="card-row">
                        <span class="card-label">Mata Uang &amp; Kurs:</span>
                        <span class="card-val text-primary font-mono fw-bold">{{ $curCode }} (Kurs: Rp {{ number_format($kurs, 2, ',', '.') }})</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- INVOICES TABLE -->
            <div class="table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 28px;">#</th>
                            <th style="width: 17%;">No. Faktur Penjualan</th>
                            <th style="width: 13%;">No. PO / Ref</th>
                            <th class="text-center" style="width: 9%;">Tgl Faktur</th>
                            <th class="text-end" style="width: 12%;">Total Faktur</th>
                            <th class="text-end" style="width: 12%;">Sisa Sblm Bayar</th>
                            <th class="text-end" style="width: 10%;">Potongan</th>
                            <th class="text-end" style="width: 13%;">Jumlah Bayar</th>
                            <th class="text-end" style="width: 11%;">Sisa Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payment->details as $idx => $det)
                            @php
                                $bayarDet = (float) $det->jumlah_bayar;
                                $potongDet = (float) $det->potongan;
                                $sisaAkhirDet = (float) ($det->sisa_akhir ?? max(0, (float)$det->sisa_piutang - (float)$det->potongan - (float)$det->jumlah_bayar));
                                $inv = $det->invoice;
                            @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-bold font-mono text-primary">{{ $inv?->nomor_faktur ?? ('Faktur #' . $det->sales_invoice_id) }}</span>
                                    @php
                                        $tenderObj = $inv?->tender ?? $inv?->salesOrder?->tender ?? $inv?->deliveryOrder?->salesOrder?->tender;
                                        $nomorTender = $tenderObj ? (!empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-' ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : '')) : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)))) : null;
                                    @endphp
                                    @if($nomorTender)
                                        <div style="font-size: 8px; color: #b45309; margin-top: 2px; font-weight: 600;">
                                            <i class="bi bi-briefcase"></i> Proyek: {{ $nomorTender }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted font-mono">{{ $inv?->salesOrder?->nomor_po_customer ?? ($inv?->deliveryOrder?->nomor_surat_jalan ?? '-') }}</span>
                                </td>
                                <td class="text-center">{{ $inv?->tanggal_faktur ? \Carbon\Carbon::parse($inv->tanggal_faktur)->format('d/m/Y') : '-' }}</td>
                                <td class="text-end font-mono">
                                    @if($isForeign)
                                        <span class="fw-semibold">{{ $curSymbol }} {{ number_format($det->total_faktur, 2, ',', '.') }}</span>
                                    @else
                                        Rp {{ number_format($det->total_faktur, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end font-mono text-muted">
                                    @if($isForeign)
                                        <span>{{ $curSymbol }} {{ number_format($det->sisa_piutang, 2, ',', '.') }}</span>
                                    @else
                                        Rp {{ number_format($det->sisa_piutang, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end font-mono {{ $potongDet > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    @if($potongDet > 0)
                                        @if($isForeign)
                                            <span>- {{ $curSymbol }} {{ number_format($potongDet, 2, ',', '.') }}</span>
                                        @else
                                            - Rp {{ number_format($potongDet, 2, ',', '.') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end font-mono fw-bold text-primary">
                                    @if($isForeign)
                                        <span>{{ $curSymbol }} {{ number_format($bayarDet, 2, ',', '.') }}</span>
                                    @else
                                        Rp {{ number_format($bayarDet, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end font-mono">
                                    @if($sisaAkhirDet <= 0.01)
                                        <span style="color: #1d4ed8; font-weight: 700;">Lunas (0)</span>
                                    @else
                                        @if($isForeign)
                                            <span class="fw-semibold text-warning" style="color: #d97706;">{{ $curSymbol }} {{ number_format($sisaAkhirDet, 2, ',', '.') }}</span>
                                        @else
                                            <span style="color: #d97706; font-weight: 600;">Rp {{ number_format($sisaAkhirDet, 2, ',', '.') }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">Tidak ada rincian faktur yang terdaftar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- SUMMARY & CATATAN -->
            <div class="summary-grid">
                <div>
                    @if($payment->keterangan)
                    <div class="catatan-box mb-2">
                        <strong>Catatan:</strong> {{ $payment->keterangan }}
                    </div>
                    @endif

                    @if(!empty($terbilang))
                    <div class="catatan-box" style="border-left-color: #1d4ed8; background: #f8fafc; color: #1e3a8a;">
                        <strong>Terbilang:</strong> <em>"{{ $terbilang }}"</em>
                    </div>
                    @endif
                </div>

                @php
                    $totalSisaAkhir = $payment->details->sum(function($d) {
                        return (float) ($d->sisa_akhir ?? max(0, (float)$d->sisa_piutang - (float)$d->potongan - (float)$d->jumlah_bayar));
                    });
                @endphp

                <div class="totals-card">
                    <div class="totals-row">
                        <span class="text-muted fw-semibold">Total Alokasi Piutang:</span>
                        <span class="font-mono fw-bold">
                            @if($isForeign)
                                {{ $curSymbol }} {{ number_format($payment->total_faktur, 2, ',', '.') }}
                            @else
                                Rp {{ number_format($payment->total_faktur, 2, ',', '.') }}
                            @endif
                        </span>
                    </div>

                    @if((float)$payment->total_potongan > 0)
                    <div class="totals-row">
                        <span class="text-danger fw-semibold">
                            Total Potongan / Diskon:
                        </span>
                        <span class="font-mono fw-bold text-danger">
                            @if($isForeign)
                                - {{ $curSymbol }} {{ number_format($payment->total_potongan, 2, ',', '.') }}
                            @else
                                - Rp {{ number_format($payment->total_potongan, 2, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    @endif

                    <div class="totals-row grand">
                        <span class="grand-label">TOTAL DIBAYAR:</span>
                        <div class="text-end">
                            <div class="grand-val">
                                @if($isForeign)
                                    {{ $curSymbol }} {{ number_format($payment->total_bayar, 2, ',', '.') }}
                                @else
                                    Rp {{ number_format($payment->total_bayar, 2, ',', '.') }}
                                @endif
                            </div>
                            @if($isForeign)
                            <small class="d-block" style="font-size: 9px; opacity: 0.85; font-family: 'JetBrains Mono', monospace;">≈ Rp {{ number_format($payment->total_bayar * $kurs, 2, ',', '.') }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="totals-row" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <span class="text-muted fw-bold">Sisa Piutang Belum Dibayar:</span>
                        <span class="font-mono fw-bold" style="color: {{ $totalSisaAkhir <= 0.01 ? '#1d4ed8' : '#d97706' }}; font-size: 11px;">
                            @if($totalSisaAkhir <= 0.01)
                                Lunas (0)
                            @else
                                @if($isForeign)
                                    {{ $curSymbol }} {{ number_format($totalSisaAkhir, 2, ',', '.') }}
                                    <small class="d-block text-muted" style="font-size: 8px;">≈ Rp {{ number_format($totalSisaAkhir * $kurs, 2, ',', '.') }}</small>
                                @else
                                    Rp {{ number_format($totalSisaAkhir, 2, ',', '.') }}
                                @endif
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENT FOOTER INFO -->
        <div class="doc-footer">
            <div>
                Dokumen resmi sistem ERP {{ $entityTitle }} • Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
            </div>
            <div>
                Ref: {{ $payment->nomor_pembayaran }} • Status: {{ strtoupper($payment->status) }}
            </div>
        </div>

        </div> <!-- /.sheet-container -->
    </div> <!-- /.sheet-viewport-wrapper -->

    <!-- SCRIPT AUTO-SCALE FOR MOBILE / TABLET & IMAGE DOWNLOAD -->
    <script>
        function scaleSheetForMobile() {
            const sheet = document.getElementById('printableSheet');
            const wrapper = document.getElementById('sheetViewportWrapper');
            if (!sheet || !wrapper) return;

            // Lebar standar sheet A4 (794px ~ 210mm di 96dpi)
            const baseWidth = 794;
            const containerWidth = wrapper.clientWidth - 16;

            if (containerWidth < baseWidth) {
                const scale = Math.max(0.28, Math.min(1, containerWidth / baseWidth));
                sheet.style.transform = `scale(${scale})`;
                sheet.style.webkitTransform = `scale(${scale})`;
                sheet.style.transformOrigin = 'top center';
                sheet.style.webkitTransformOrigin = 'top center';
                
                // Menyesuaikan tinggi wrapper agar tidak menyisakan ruang kosong berlebih di bawah
                const scaledHeight = sheet.offsetHeight * scale;
                wrapper.style.minHeight = `${scaledHeight + 40}px`;
            } else {
                sheet.style.transform = 'none';
                sheet.style.webkitTransform = 'none';
                wrapper.style.minHeight = 'auto';
            }
        }

        window.addEventListener('resize', scaleSheetForMobile);
        window.addEventListener('orientationchange', scaleSheetForMobile);
        document.addEventListener('DOMContentLoaded', scaleSheetForMobile);
        window.addEventListener('load', scaleSheetForMobile);

        // Download Image High-Res
        document.getElementById('btnDownloadImage')?.addEventListener('click', function() {
            const element = document.getElementById('printableSheet');
            const prevTransform = element.style.transform;
            element.style.transform = 'none';
            element.style.webkitTransform = 'none';
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                element.style.transform = prevTransform;
                element.style.webkitTransform = prevTransform;
                const link = document.createElement('a');
                link.download = 'Bukti_Penerimaan_{{ $payment->nomor_pembayaran }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                element.style.transform = prevTransform;
                element.style.webkitTransform = prevTransform;
                console.error(err);
            });
        });
    </script>
</body>
</html>
