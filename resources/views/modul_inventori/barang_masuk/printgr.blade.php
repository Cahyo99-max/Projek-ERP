<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penerimaan Barang – {{ $goodsReceipt->nomor_gr }}</title>
    <link rel="shortcut icon" href="{{ $appLogo }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', 'Segoe UI', Arial, sans-serif;
            background: #e8eaf0;
            color: #000;
            font-size: 11.5px;
            line-height: 1.4;
        }

        /* ── TOOLBAR (EXACT MATCHING USER SCREENSHOT) ── */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
            max-width: 720px;
        }

        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 11.5px; font-weight: 600;
            background: #fff;
            color: #333;
            border: 1px solid #d1d5db;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: all 0.15s ease;
        }
        .btn-back:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 20px;
            border-radius: 6px;
            font-size: 11.5px; font-weight: 700;
            background: #1d4ed8;
            color: #fff;
            border: 1px solid #1d4ed8;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(29,78,216,0.25);
            transition: background 0.15s;
        }
        .btn-print:hover { background: #1e40af; }

        .btn-screenshot {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 11.5px; font-weight: 700;
            background: #059669;
            color: #fff;
            border: 1px solid #059669;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(5,150,105,0.2);
            transition: background 0.15s;
        }
        .btn-screenshot:hover { background: #047857; }

        /* ── EDIT MODE STYLING ── */
        .edit-mode-active .editable-text {
            outline: 1px dashed #d97706 !important;
            background-color: #fffbeb !important;
            padding: 1px 4px;
            border-radius: 3px;
            cursor: text;
        }

        /* ── A4 SHEET ── */
        .sheet {
            max-width: 820px;
            min-height: 1120px;
            margin: 24px auto 40px;
            background: #fff;
            box-shadow: 0 2px 20px rgba(0,0,0,.15);
            padding: 36px 44px 44px;
            display: flex;
            flex-direction: column;
        }

        .sig-wrapper {
            margin-top: auto;
            page-break-inside: avoid;
            break-inside: avoid;
            padding-top: 24px;
        }

        /* ── DOCUMENT HEADER ── */
        .header-container {
            margin-bottom: 12px;
        }

        .doc-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 2px;
        }

        .doc-title {
            font-size: 19px;
            font-weight: 800;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.1;
        }

        .company-header-name {
            font-size: 15px;
            font-weight: 700;
            font-style: italic;
            color: #000;
            text-align: right;
            margin: 0;
        }

        .header-double-line {
            border-bottom: 3px double #000;
            margin-top: 4px;
            margin-bottom: 16px;
        }

        /* ── META GRID (2 COLUMNS) ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11.5px;
        }

        .meta-label {
            color: #000;
            font-weight: 400;
        }

        .meta-sep {
            text-align: center;
            color: #000;
        }

        .meta-val {
            font-weight: 400;
            color: #000;
        }

        /* ── REPORT ITEMS TABLE ── */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .report-table thead tr {
            background-color: #134074;
            color: #ffffff;
        }

        .report-table thead th {
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            border: none;
            text-align: left;
        }

        .report-table thead th.text-center { text-align: center; }
        .report-table thead th.text-right { text-align: right; }

        .report-table tbody tr {
            border: none;
        }

        .report-table tbody td {
            padding: 7px 10px;
            vertical-align: top;
            font-size: 11px;
            color: #000;
            border: none;
        }

        .report-table tbody td.text-center { text-align: center; }
        .report-table tbody td.text-right { text-align: right; }

        .font-mono {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
        }

        .fw-semibold { font-weight: 600; }
        .fw-bold { font-weight: 700; }

        /* ── FOOTER TERBILANG & SALDO ── */
        .footer-summary-container {
            margin-top: 10px;
        }

        .summary-top-line {
            border-top: 2px solid #000;
            margin-bottom: 8px;
        }

        .summary-flex-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .terbilang-box {
            font-style: italic;
            font-weight: 600;
            font-size: 11.5px;
            color: #000;
            flex: 1;
        }

        .saldo-box {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-size: 12px;
            font-weight: 400;
            min-width: 280px;
        }

        .saldo-label {
            width: 80px;
            text-align: left;
        }

        .saldo-sep {
            width: 20px;
            text-align: center;
        }

        .saldo-currency {
            width: 30px;
            text-align: left;
        }

        .saldo-amount {
            font-weight: 700;
            text-align: right;
            flex: 1;
        }

        .saldo-double-line {
            border-bottom: 3px double #000;
            margin-top: 6px;
            margin-left: auto;
            width: 340px;
        }

        /* ── SIGNATURE AREA ── */
        .sig-divider {
            border: none;
            border-top: 1px solid #aaa;
            margin: 20px 0 18px;
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
            height: 70px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 6px;
        }

        .sig-line {
            border: none;
            border-top: 1px solid #333;
            margin: 0 0 4px;
        }

        .sig-name {
            font-size: 11px;
            font-weight: 700;
            color: #111;
        }

        .sig-sub {
            font-size: 10px;
            color: #666;
            margin-top: 1px;
        }

        /* ── PRINT STYLES ── */
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 10mm 15mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .toolbar {
                display: none !important;
            }
            .sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                display: flex !important;
                flex-direction: column !important;
            }
            .report-table thead tr,
            .report-table thead th {
                background-color: #134074 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .sig-wrapper {
                display: block !important;
                position: static !important;
                margin-top: auto !important;
                padding-top: 24px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .sig-section {
                display: flex !important;
                justify-content: space-between !important;
            }
            .edit-mode-active .editable-text {
                outline: none !important;
                background-color: transparent !important;
            }
        }
    </style>
</head>
<body>

    @php
    if (!function_exists('terbilangRupiahGR')) {
        function terbilangRupiahGR($angka) {
            $angka = abs((float)$angka);
            $baca = array('', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas');
            $terbilang = '';

            if ($angka < 12) {
                $terbilang = ' ' . $baca[(int)$angka];
            } else if ($angka < 20) {
                $terbilang = terbilangRupiahGR($angka - 10) . ' Belas';
            } else if ($angka < 100) {
                $terbilang = terbilangRupiahGR($angka / 10) . ' Puluh' . terbilangRupiahGR($angka % 10);
            } else if ($angka < 200) {
                $terbilang = ' Seratus' . terbilangRupiahGR($angka - 100);
            } else if ($angka < 1000) {
                $terbilang = terbilangRupiahGR($angka / 100) . ' Ratus' . terbilangRupiahGR($angka % 100);
            } else if ($angka < 2000) {
                $terbilang = ' Seribu' . terbilangRupiahGR($angka - 1000);
            } else if ($angka < 1000000) {
                $terbilang = terbilangRupiahGR($angka / 1000) . ' Ribu' . terbilangRupiahGR($angka % 1000);
            } else if ($angka < 1000000000) {
                $terbilang = terbilangRupiahGR($angka / 1000000) . ' Juta' . terbilangRupiahGR($angka % 1000000);
            } else if ($angka < 1000000000000) {
                $terbilang = terbilangRupiahGR($angka / 1000000000) . ' Miliar' . terbilangRupiahGR($angka % 1000000000);
            } else if ($angka < 1000000000000000) {
                $terbilang = terbilangRupiahGR($angka / 1000000000000) . ' Triliun' . terbilangRupiahGR($angka % 1000000000000);
            }
            return trim($terbilang);
        }
    }
    @endphp

    <!-- TOOLBAR (EXACT REPLICA OF USER REFERENCE UI) -->
    <div class="toolbar">
        <a href="{{ route('gr.index') }}" onclick="if(window.history.length > 1 && document.referrer.includes('/goods-receipt')) { window.history.back(); return false; }" class="btn-back">
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
        <button type="button" class="btn-screenshot" id="btn-capture">
            <i class="bi bi-camera-fill"></i> Ambil Screenshot
        </button>
        <button type="button" class="btn-print" onclick="doPrint()">
            <i class="bi bi-printer-fill"></i> Cetak / PDF
        </button>
    </div>

    <!-- SHEET CONTAINER -->
    <div class="sheet" id="print-sheet">
        
        <!-- DOCUMENT HEADER -->
        <div class="header-container">
            <div class="doc-title-row">
                <h1 class="doc-title editable-text">PENERIMAAN BARANG</h1>
                <div class="company-header-name editable-text">{{ $goodsReceipt->company ? $goodsReceipt->company->nama_perusahaan : ($goodsReceipt->po && $goodsReceipt->po->company ? $goodsReceipt->po->company->nama_perusahaan : 'PT. Persada Aman Sentosa') }}</div>
            </div>
            <div class="header-double-line"></div>
        </div>

        <!-- META TRANSACTION INFO (2-COLUMN KEY-VALUE GRID) -->
        <table class="meta-table">
            <tr>
                <td width="13%" class="meta-label">Nomor Surat Jalan</td>
                <td width="2%" class="meta-sep">:</td>
                <td width="35%" class="meta-val editable-text">{{ $goodsReceipt->nomor_surat_jalan ?? '-' }}</td>
                
                <td width="13%" class="meta-label">Tanggal</td>
                <td width="2%" class="meta-sep">:</td>
                <td width="35%" class="meta-val editable-text">{{ $goodsReceipt->tanggal_gr ? $goodsReceipt->tanggal_gr->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Kepada</td>
                <td class="meta-sep">:</td>
                <td class="meta-val editable-text">{{ $goodsReceipt->po && $goodsReceipt->po->supplier ? $goodsReceipt->po->supplier->nama_supplier : '-' }}</td>
                
                <td class="meta-label">Mata Uang</td>
                <td class="meta-sep">:</td>
                <td class="meta-val editable-text">
                    @php
                        $poCurrency = $goodsReceipt->po && $goodsReceipt->po->currency ? $goodsReceipt->po->currency : null;
                        $currCode = $poCurrency ? strtoupper($poCurrency->kode_currency) : 'IDR';
                        $currSymbol = $poCurrency ? $poCurrency->simbol : 'Rp';
                        $isValas = $currSymbol !== 'Rp' && $currCode !== 'IDR';
                        $grKurs = floatval($goodsReceipt->kurs ?? ($goodsReceipt->po ? $goodsReceipt->po->kurs : 1));
                        if ($grKurs <= 0) $grKurs = 1;
                        $namaMataUang = $poCurrency && !empty($poCurrency->nama_currency) ? $poCurrency->nama_currency : 'Rupiah';
                    @endphp
                    {{ $namaMataUang }}
                    @if($isValas)
                        <span style="font-size: 10px; color: #1d4ed8; font-weight: 600; margin-left: 6px;">[Kurs: Rp {{ number_format($grKurs, 2, ',', '.') }}]</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="meta-label">Up.</td>
                <td class="meta-sep">:</td>
                <td class="meta-val editable-text">{{ $goodsReceipt->po && $goodsReceipt->po->supplier ? ($goodsReceipt->po->supplier->nama_kontak ?? '-') : '-' }}</td>
                
                <td class="meta-label">Nomor PO</td>
                <td class="meta-sep">:</td>
                <td class="meta-val editable-text">{{ $goodsReceipt->po ? $goodsReceipt->po->nomor_po : '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label" style="vertical-align: top;">Alamat</td>
                <td class="meta-sep" style="vertical-align: top;">:</td>
                <td class="meta-val editable-text" style="vertical-align: top;">{{ $goodsReceipt->po && $goodsReceipt->po->supplier ? ($goodsReceipt->po->supplier->alamat ?? '-') : '-' }}</td>
                
                <td class="meta-label" style="vertical-align: top;">Dikirim Ke</td>
                <td class="meta-sep" style="vertical-align: top;">:</td>
                <td class="meta-val editable-text" style="vertical-align: top;">
                    @php
                        $branchName = $goodsReceipt->branch ? $goodsReceipt->branch->nama_cabang : ($goodsReceipt->po && $goodsReceipt->po->branch ? $goodsReceipt->po->branch->nama_cabang : null);
                        $companyName = $goodsReceipt->company ? $goodsReceipt->company->nama_perusahaan : ($goodsReceipt->po && $goodsReceipt->po->company ? $goodsReceipt->po->company->nama_perusahaan : null);
                        $gudangName = $goodsReceipt->gudang ? $goodsReceipt->gudang->nama_gudang : null;
                        $destText = $branchName ?: ($companyName ?: ($gudangName ?: 'Head Quarter'));
                    @endphp
                    {{ $destText }}
                </td>
            </tr>
        </table>

        <!-- ITEMS TABLE -->
        <table class="report-table">
            <thead>
                <tr>
                    <th width="4%">No.</th>
                    <th width="8%" class="td-photo-col text-center">Foto</th>
                    <th width="10%">Kode</th>
                    <th width="26%">Nama Barang</th>
                    <th width="16%" class="text-right">Jumlah Unit</th>
                    <th width="12%" class="text-right">Harga @</th>
                    <th width="8%" class="text-right">Disc.</th>
                    <th width="10%" class="text-right">Sub Total</th>
                    <th width="6%" class="text-right">Pajak</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $subtotalReport = 0; 
                @endphp
                @foreach($goodsReceipt->details as $index => $detail)
                @php
                    $barangNama = $detail->barang ? $detail->barang->nama_barang : 'Barang';
                    $barangKode = $detail->barang ? $detail->barang->kode_barang : '-';
                    
                    $poFaktor = $detail->poDetail && floatval($detail->poDetail->faktor_pengali) > 0 ? floatval($detail->poDetail->faktor_pengali) : 1.0;
                    $detailFaktor = floatval($detail->faktor_pengali) > 0 ? floatval($detail->faktor_pengali) : 1.0;

                    $satuanNama = $detail->satuan 
                        ? ($detail->satuan->singkatan ?: $detail->satuan->nama_satuan) 
                        : ($detail->barang && $detail->barang->satuan ? ($detail->barang->satuan->singkatan ?: $detail->barang->satuan->nama_satuan) : 'pcs');
                    $satuanDasarNama = $detail->barang && $detail->barang->satuan ? ($detail->barang->satuan->singkatan ?: $detail->barang->satuan->nama_satuan) : $satuanNama;

                    $qtyReceived = floatval($detail->jumlah ?? 0);
                    $qtyDasar = floatval($detail->jumlah_dasar ?? 0) > 0 ? floatval($detail->jumlah_dasar) : ($qtyReceived * $detailFaktor);

                    // Price and discount per received unit
                    $poHargaSatuan = $detail->poDetail ? floatval($detail->poDetail->harga_satuan) : 0;
                    $hargaSatuan = ($poFaktor > 0) ? (($poHargaSatuan / poFaktor) * $detailFaktor) : $poHargaSatuan;

                    $diskonItemPO = $detail->poDetail ? floatval($detail->poDetail->diskon_item) : 0;
                    $diskonItem = ($poFaktor > 0) ? (($diskonItemPO / poFaktor) * $detailFaktor) : $diskonItemPO;

                    $hargaNetto  = max(0, $hargaSatuan - $diskonItem);
                    $subtotalRow = $qtyReceived * $hargaNetto;
                    $subtotalReport += $subtotalRow;

                    $taxRateRow = ($goodsReceipt->po && $goodsReceipt->po->pajak) ? floatval($goodsReceipt->po->pajak->tarif) : 0;
                    $fmtQ = function($v) {
                        return (floor($v) == $v) ? number_format($v, 0, '.', ',') : rtrim(rtrim(number_format($v, 4, '.', ','), '0'), '.');
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="td-photo-col text-center">
                        <div class="td-photo" style="width: 44px; height: 44px; border-radius: 4px; border: 1px solid #e2e8f0; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            @if($detail->barang && $detail->barang->primaryFoto)
                                <img src="/storage/{{ $detail->barang->primaryFoto->foto_path }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Foto Barang">
                            @else
                                <i class="bi bi-image" style="font-size: 18px; color: #cbd5e1;"></i>
                            @endif
                        </div>
                    </td>
                    <td class="font-mono editable-text">{{ $barangKode }}</td>
                    <td class="fw-semibold">
                        <div class="editable-text">{{ $barangNama }}</div>
                        @if($detail->lokasiRak)
                            <div style="font-size: 9.5px; color: #059669; font-weight: 600; margin-top: 2px;">
                                <i class="bi bi-geo-alt"></i> Rak: [{{ $detail->lokasiRak->kode_rak }}] {{ $detail->lokasiRak->nama_rak }}
                            </div>
                        @endif
                        @if(floatval($detail->jumlah_reject) > 0)
                            <div style="font-size: 9.5px; color: #dc2626; font-weight: 600; margin-top: 1px;">
                                <i class="bi bi-x-circle"></i> Qty Reject: {{ $fmtQ(floatval($detail->jumlah_reject)) }} {{ $satuanNama }}
                            </div>
                        @endif
                        @if($detail->fixedAssets && $detail->fixedAssets->count() > 0)
                            <div style="font-size: 9px; color: #475569; margin-top: 1px;">
                                <strong>Seri/Aset:</strong> {{ $detail->fixedAssets->pluck('kode_aset')->implode(', ') }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right editable-text">
                        {{ $fmtQ($qtyReceived) }} {{ $satuanNama }}
                        @if($detailFaktor > 1)
                            <div style="font-size: 9px; color: #64748b; margin-top: 1px;">(= {{ $fmtQ($qtyDasar) }} {{ $satuanDasarNama }})</div>
                        @endif
                    </td>
                    <td class="text-right editable-text">{{ number_format($hargaSatuan, 2, '.', ',') }}</td>
                    <td class="text-right editable-text">{{ number_format($diskonItem, 2, '.', ',') }}</td>
                    <td class="text-right editable-text">{{ number_format($subtotalRow, 2, '.', ',') }}</td>
                    <td class="text-right editable-text">{{ number_format($taxRateRow, 2, '.', ',') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- FOOTER TERBILANG & SALDO SUMMARY -->
        @php
            $taxRateHeader = ($goodsReceipt->po && $goodsReceipt->po->pajak) ? floatval($goodsReceipt->po->pajak->tarif) : 0;
            $totalTaxReport = $subtotalReport * ($taxRateHeader / 100);
            $grandTotalReport = $subtotalReport + $totalTaxReport;
            $grandTotalIdrReport = $grandTotalReport * $grKurs;
            $terbilangTeks = terbilangRupiahGR($isValas ? $grandTotalIdrReport : $grandTotalReport) . ' Rupiah';
        @endphp

        <div class="footer-summary-container">
            <div class="summary-top-line"></div>
            <div class="summary-flex-row">
                <div class="terbilang-box">
                    <span>Terbilang : </span>
                    <span class="editable-text">{{ $terbilangTeks }}</span>
                </div>
                <div class="saldo-box">
                    <span class="saldo-label">Saldo</span>
                    <span class="saldo-sep">:</span>
                    <span class="saldo-currency">{{ $currSymbol }}</span>
                    <span class="saldo-amount editable-text">
                        {{ number_format($grandTotalReport, 2, '.', ',') }}
                        @if($isValas)
                            <span style="font-size: 10px; font-weight: normal; color: #475569; display: block;">(Rp {{ number_format($grandTotalIdrReport, 2, ',', '.') }})</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="saldo-double-line"></div>
        </div>

        <!-- CATATAN / KETERANGAN -->
        @if($goodsReceipt->keterangan)
        <div style="margin-top: 14px; font-size: 10.5px; color: #444;">
            <strong>Catatan:</strong> <span class="editable-text">{{ $goodsReceipt->keterangan }}</span>
        </div>
        @endif

        <!-- SIGNATURE AREA (TTD OTOMATIS + TOGGLEABLE) -->
        <div class="sig-wrapper">
            <hr class="sig-divider">
            <div class="sig-section">
                <div class="sig-block">
                    <p class="sig-role editable-text">Yang Menyerahkan,</p>
                    <div class="sig-box">
                        <span class="sig-empty" style="font-size: 10px; color: #888;">( Sopir / Ekspedisi )</span>
                    </div>
                    <hr class="sig-line">
                    <p class="sig-name editable-text">{{ $goodsReceipt->po && $goodsReceipt->po->supplier ? $goodsReceipt->po->supplier->nama_supplier : '______________________' }}</p>
                    <p class="sig-sub editable-text">Supplier / Pengirim</p>
                </div>
                <div class="sig-block">
                    <p class="sig-role editable-text">Yang Menerima,</p>
                    <div class="sig-box">
                        @if($goodsReceipt->creator?->activeSignature)
                            <img src="{{ asset($goodsReceipt->creator->activeSignature->signature_path) }}" style="max-height: 55px; object-fit: contain;" alt="TTD Penerima">
                        @else
                            <div style="border: 2px dashed #059669; background: #ecfdf5; border-radius: 8px; padding: 5px 10px; text-align: center; display: inline-block;">
                                <div style="color: #059669; font-weight: 800; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="bi bi-patch-check-fill me-1"></i>VERIFIED DIGITAL SIGNATURE
                                </div>
                                <div style="color: #047857; font-size: 9px; font-weight: 700; margin-top: 1px;">
                                    {{ $goodsReceipt->creator ? $goodsReceipt->creator->nama_lengkap : 'Staff Penerima' }}
                                </div>
                                <div style="color: #4b5563; font-size: 8px; margin-top: 1px;">
                                    Tgl: {{ $goodsReceipt->tanggal_gr ? $goodsReceipt->tanggal_gr->format('d/m/Y') : date('d/m/Y') }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <hr class="sig-line">
                    <p class="sig-name editable-text">{{ $goodsReceipt->creator ? $goodsReceipt->creator->nama_lengkap : '______________________' }}</p>
                    <p class="sig-sub editable-text">Penerima (Gudang)</p>
                </div>
                <div class="sig-block">
                    <p class="sig-role editable-text">Mengetahui,</p>
                    <div class="sig-box">
                        @if($goodsReceipt->status === 'Posted')
                            <div style="border: 2px dashed #1e40af; background: #eff6ff; border-radius: 8px; padding: 5px 10px; text-align: center; display: inline-block;">
                                <div style="color: #1e40af; font-weight: 800; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="bi bi-shield-check me-1"></i>APPROVED & POSTED
                                </div>
                                <div style="color: #1d4ed8; font-size: 9px; font-weight: 700; margin-top: 1px;">
                                    Kepala Gudang / Logistik
                                </div>
                                <div style="color: #4b5563; font-size: 8px; margin-top: 1px;">
                                    System Validated
                                </div>
                            </div>
                        @else
                            <span class="sig-empty" style="font-size: 10px; color: #888;">( Menunggu Posting )</span>
                        @endif
                    </div>
                    <hr class="sig-line">
                    <p class="sig-name editable-text">{{ $goodsReceipt->status === 'Posted' ? 'Kepala Gudang / SPV' : '______________________' }}</p>
                    <p class="sig-sub editable-text">Kepala Gudang / SPV</p>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPTS -->
    <script>
        // Toggle Foto Visibility
        document.getElementById('toggle-foto').addEventListener('change', function() {
            const photos = document.querySelectorAll('.td-photo-col, .td-photo');
            photos.forEach(el => {
                el.style.display = this.checked ? '' : 'none';
            });
        });

        // Toggle Signature Visibility (Hides/Shows automatic signature image/badge inside .sig-box, keeping form structure)
        document.getElementById('toggle-ttd').addEventListener('change', function() {
            const sigBoxes = document.querySelectorAll('.sig-box');
            sigBoxes.forEach(box => {
                box.style.visibility = this.checked ? 'visible' : 'hidden';
            });
        });

        // Toggle Mode Edit (Inline Editing)
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

        // Save Image / Screenshot Feature
        document.getElementById('btn-capture').addEventListener('click', function () {
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
            
            const sheet = document.getElementById('print-sheet');
            const editModeChecked = document.getElementById('toggle-edit').checked;

            if (editModeChecked) {
                sheet.classList.remove('edit-mode-active');
            }
            
            const options = {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false
            };

            html2canvas(sheet, options).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Penerimaan_Barang_{{ $goodsReceipt->nomor_gr }}.png';
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

        // Universal print helper
        function doPrint() {
            const originalTitle = document.title;
            document.title = ' ';
            
            const editModeChecked = document.getElementById('toggle-edit').checked;
            const sheet = document.querySelector('.sheet');
            if (editModeChecked && sheet) {
                sheet.classList.remove('edit-mode-active');
            }

            window.print();

            setTimeout(function () {
                document.title = originalTitle;
                if (editModeChecked && sheet) {
                    sheet.classList.add('edit-mode-active');
                }
            }, 1000);
        }

        // Fallback listeners for browser print shortcut (Ctrl+P)
        let originalTitle = document.title;
        window.addEventListener('beforeprint', function() {
            originalTitle = document.title;
            document.title = ' ';
            const editModeChecked = document.getElementById('toggle-edit').checked;
            const sheet = document.querySelector('.sheet');
            if (editModeChecked && sheet) {
                sheet.classList.remove('edit-mode-active');
            }
        });

        window.addEventListener('afterprint', function() {
            document.title = originalTitle;
            const editModeChecked = document.getElementById('toggle-edit').checked;
            const sheet = document.querySelector('.sheet');
            if (editModeChecked && sheet) {
                sheet.classList.add('edit-mode-active');
            }
        });
    </script>
</body>
</html>
