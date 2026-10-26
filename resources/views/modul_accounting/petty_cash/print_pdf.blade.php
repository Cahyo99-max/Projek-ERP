<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas Kecil - {{ $activeEntityName }}</title>
    <!-- Favicon / Logo Aplikasi di Tab Browser -->
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <!-- Modern Font: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --blue-primary: #1d4ed8;
            --blue-header: #1e40af;
            --blue-dark: #1e3a8a;
            --blue-light: #eff6ff;
            --blue-subtle: #dbeafe;
            --blue-border: #bfdbfe;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
            background: #f1f5f9;
            padding: 12px;
            font-size: 0.85rem;
            line-height: 1.45;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }

        .print-canvas {
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
            border: 1px solid #e2e8f0;
        }

        /* Top Action Bar (Screen Only) */
        .no-print-bar {
            background: #ffffff;
            border: 1px solid var(--blue-border);
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(29, 78, 216, 0.05);
        }

        /* Kop Surat Resmi (Clean Blue Style - Tanpa Logo) */
        .kop-surat {
            border-bottom: 2.5px solid var(--blue-header);
            padding-bottom: 16px;
            margin-bottom: 22px;
        }

        .kop-title {
            color: var(--blue-dark);
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .kop-subtitle {
            color: #475569;
            font-size: 12px;
            margin-top: 4px;
        }

        .report-badge {
            background: var(--blue-header) !important;
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: 0.6px;
            font-size: 11px;
            padding: 6px 14px;
            border-radius: 9999px;
            display: inline-block;
        }

        /* Section Title Laporan */
        .report-main-title {
            color: var(--blue-dark);
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .report-filter-info {
            background-color: var(--blue-light);
            border: 1px solid var(--blue-border);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 12px;
            color: #334155;
            display: inline-block;
            margin-bottom: 18px;
        }

        /* Tabel Transaksi Kas Kecil (Clean Elegan - Tanpa Garis Grid Kasar) */
        .table-petty-cash {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 24px;
            border: none !important;
        }

        .table-petty-cash th {
            background-color: var(--blue-header) !important;
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.4px;
            padding: 10px 12px;
            border: none !important;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-petty-cash td {
            padding: 9px 12px;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
            color: var(--text-dark);
        }

        .table-petty-cash tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table-petty-cash tbody tr:hover {
            background-color: var(--blue-light);
        }

        .table-petty-cash tfoot tr td {
            background-color: var(--blue-light) !important;
            color: var(--blue-dark);
            font-weight: 800;
            border: none !important;
            border-top: 2px solid var(--blue-header) !important;
            border-bottom: 2px solid var(--blue-header) !important;
            padding: 10px 12px;
        }

        /* Badge Kategori */
        .badge-cat {
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--blue-subtle);
            color: var(--blue-dark);
            border: 1px solid var(--blue-border);
            display: inline-block;
            white-space: nowrap;
        }


        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 32px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 33.33%;
            padding: 0 16px;
            vertical-align: top;
        }

        .signature-role {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--blue-dark);
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Editable Signature Elements */
        .editable-field {
            display: inline-block;
            min-width: 60px;
            outline: none;
            border-radius: 4px;
            padding: 2px 6px;
            transition: all 0.2s ease;
        }
        .editable-field:hover {
            background-color: rgba(29, 78, 216, 0.08);
            cursor: pointer;
            box-shadow: 0 0 0 1px rgba(29, 78, 216, 0.2);
        }
        .editable-field:focus {
            background-color: #eff6ff;
            box-shadow: 0 0 0 2px var(--blue-primary);
        }

        .signature-role-row {
            display: block;
            width: 100%;
            text-align: center;
        }

        .signature-line-box {
            height: 60px;
            margin-bottom: 4px;
        }

        .signature-name-row {
            display: block;
            width: 100%;
            text-align: center;
            margin-bottom: 2px;
        }

        .signature-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
        }

        .signature-sub-row {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 2px;
        }

        .signature-title-sub {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
            display: inline-block;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9.5pt !important;
            }

            .print-canvas {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .editable-field {
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .table-petty-cash {
                width: 100% !important;
            }

            .no-print, .no-print-bar {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 6mm 6mm 8mm 6mm;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN ONLY: Action Bar -->
    <div class="print-canvas p-0 mb-3 bg-transparent border-0 shadow-none">
        <div class="no-print-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('accounting.petty-cash.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Kas 
                </a>
                <span class="text-muted small">&bull;</span>
                <span class="small fw-semibold text-secondary">
                    Total: <strong class="text-dark">{{ $transactions->count() }} Transaksi</strong>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Tombol Edit TTD -->
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditTtd" title="Atur nama dan jabatan penandatangan">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit TTD</span>
                </button>

                <!-- Tombol Screenshot -->
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm" id="btnScreenshot" title="Ambil tangkapan layar & unduh gambar PNG">
                    <i class="bi bi-camera-fill"></i>
                    <span id="btnScreenshotText">Screenshot</span>
                </button>

                <!-- Tombol Cetak Excel -->
                <a href="{{ route('accounting.petty-cash.export-excel', request()->all()) }}" class="btn btn-outline-success btn-sm rounded-pill px-3.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm text-decoration-none" title="Unduh laporan ini dalam format spreadsheet Excel (.xlsx)">
                    <i class="bi bi-file-earmark-excel-fill text-success"></i>
                    <span>Cetak Excel</span>
                </a>

                <!-- Tombol Cetak / PDF -->
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-1.5" onclick="window.print()" style="background: var(--blue-primary); border-color: var(--blue-primary);" title="Cetak atau simpan sebagai dokumen PDF">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN PRINT CANVAS -->
    <div class="print-canvas" id="reportCanvas">
        
        <!-- KOP SURAT RESMI (NAMA PT / CABANG YANG INPUT - WARNA BIRU, TANPA LOGO) -->
        <div class="kop-surat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <!-- Nama PT / Cabang Utama yang Menginput Data -->
                    <div class="kop-title text-uppercase">
                        {{ $activeEntityName }}
                    </div>
                    @if(!empty($branch->alamat) || !empty($company->alamat))
                        <div class="kop-subtitle text-muted" style="font-size: 11px;">
                            <i class="bi bi-geo-alt me-0.5"></i>{{ $branch->alamat ?? $company->alamat }}
                        </div>
                    @endif
                </div>

                <div class="text-end flex-shrink-0">
                    <span class="report-badge text-uppercase font-mono">
                        Laporan Kas
                    </span>
                    <div class="text-muted font-mono mt-1" style="font-size: 10px;">
                        Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- JUDUL LAPORAN & KOTAK FILTER PERIODE -->
        <div class="text-center mb-3">
            <div class="report-main-title text-uppercase">
                LAPORAN BUKU KAS 
            </div>
            <div class="report-filter-info">
                <span>Periode: <strong>{{ date('d/m/Y', strtotime($startDate)) }}</strong> s/d <strong>{{ date('d/m/Y', strtotime($endDate)) }}</strong></span>
                @if(!empty($kasAccount))
                    <span class="mx-1.5">&bull;</span>
                    <span>Akun: <strong>{{ $kasAccount->nama_akun ?: ($kasAccount->coa?->name ?? 'Kas Kecil') }}</strong></span>
                @endif
                @if(!empty($pemohonFilter))
                    <span class="mx-1.5">&bull;</span>
                    <span>Pemohon: <strong>{{ $pemohonFilter }}</strong></span>
                @endif
                @if(!empty($kategoriFilter) && $kategoriFilter !== 'all')
                    <span class="mx-1.5">&bull;</span>
                    <span>Kategori: <strong>{{ $kategoriFilter }}</strong></span>
                @endif
                @if(!empty($sortBy) && $sortBy !== 'tanggal')
                    <span class="mx-1.5">&bull;</span>
                    @php
                        $sortLabel = ucfirst(str_replace('_', ' ', $sortBy));
                        $orderText = ($sortBy === 'kategori') ? (($sortOrder === 'asc') ? 'A-Z' : 'Z-A') : strtoupper($sortOrder ?? 'asc');
                    @endphp
                    <span>Urutan: <strong>{{ $sortLabel }} ({{ $orderText }})</strong></span>
                @endif
            </div>
        </div>

        <!-- TABEL DATA TRANSAKSI KAS KECIL -->
        @php
            $runningBalance = (float)($saldoAwal ?? 0);
            $totalMasuk = 0;
            $totalKeluar = 0;
        @endphp

        <table class="table-petty-cash">
            <thead>
                <tr>
                    <th class="text-center" style="width: 38px;">NO.</th>
                    <th class="text-center" style="width: 95px;">TANGGAL</th>
                    <th style="min-width: 145px;">NO. REF</th>
                    <th style="min-width: 130px;">KATEGORI</th>
                    <th>KETERANGAN</th>
                    <th class="text-end" style="min-width: 105px;">MASUK</th>
                    <th class="text-end" style="min-width: 105px;">KELUAR</th>
                    <th class="text-end" style="min-width: 115px;">SALDO</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $isAsc = strtolower($sortOrder ?? 'desc') === 'asc';
                @endphp

                @if($isAsc)
                <!-- BARIS SALDO AWAL PERIODE (Urutan Terlama ke Terbaru) -->
                <tr style="background-color: rgba(224, 242, 254, 0.45);">
                    <td class="text-center font-mono text-secondary">-</td>
                    <td class="text-center font-mono">
                        <div class="fw-bold text-dark" style="font-size: 11px;">{{ date('d/m/Y', strtotime($startDate)) }}</div>
                    </td>
                    <td class="font-mono fw-bold text-primary text-nowrap">SALDO AWAL</td>
                    <td>
                        <span class="badge-cat" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">Saldo Awal</span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">Saldo Awal Kas Kecil per {{ date('d/m/Y', strtotime($startDate)) }}</div>
                    </td>
                    <td class="text-end font-mono text-muted">-</td>
                    <td class="text-end font-mono text-muted">-</td>
                    <td class="text-end font-mono fw-bold text-dark">
                        {{ number_format((float)($saldoAwal ?? 0), 2, ',', '.') }}
                    </td>
                </tr>
                @endif

                @forelse($transactions as $index => $trx)
                    @php
                        $isMasuk = ($trx->tipe === 'Pemasukan');
                        $masuk = $isMasuk ? (float)$trx->nominal : 0;
                        $keluar = !$isMasuk ? (float)$trx->nominal : 0;
                        $totalMasuk += $masuk;
                        $totalKeluar += $keluar;
                        
                        // Gunakan running_balance kronologis presisi atau fallback perhitungan
                        $rowBalance = isset($trx->running_balance) ? $trx->running_balance : ($runningBalance = $runningBalance + $masuk - $keluar);

                        // Styling Badge Kategori Khusus (Top Up / Pengosongan / Operasional)
                        $catStyle = '';
                        if ($trx->kategori === 'Top Up Kas Kecil') {
                            $catStyle = 'background: #dcfce7; color: #15803d; border-color: #bbf7d0;';
                        } elseif ($trx->kategori === 'Pengosongan Kas Kecil') {
                            $catStyle = 'background: #fee2e2; color: #b91c1c; border-color: #fecaca;';
                        }
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-secondary">{{ $index + 1 }}</td>
                        <td class="text-center font-mono">
                            <div class="fw-bold text-dark" style="font-size: 11px;">{{ date('d/m/Y', strtotime($trx->tanggal)) }}</div>
                        </td>
                        <td class="font-mono fw-semibold text-dark text-nowrap">{{ $trx->nomor_transaksi }}</td>
                        <td>
                            <span class="badge-cat" style="{{ $catStyle }}">{{ $trx->kategori ?: 'Operasional' }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $trx->keterangan ?: '-' }}</div>
                            <div class="text-muted" style="font-size: 10.5px;">
                                <i class="bi bi-person me-0.5"></i>{{ $trx->nama_pengaju ?: 'Admin' }}
                                @if(!empty($trx->tender))
                                    <span class="mx-1">&bull;</span>🎯 {{ $trx->tender->nomor_kontrak }}
                                @endif
                            </div>
                        </td>
                        <td class="text-end font-mono text-success fw-bold">
                            {{ $masuk > 0 ? number_format($masuk, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-end font-mono text-danger fw-bold">
                            {{ $keluar > 0 ? number_format($keluar, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-end font-mono fw-bold text-dark">
                            {{ number_format($rowBalance, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3 text-muted fst-italic">
                            Tidak ditemukan catatan mutasi kas kecil tambahan untuk filter periode ini.
                        </td>
                    </tr>
                @endforelse

                @if(!$isAsc)
                <!-- BARIS SALDO AWAL PERIODE (Urutan Terbaru ke Terlama) -->
                <tr style="background-color: rgba(224, 242, 254, 0.45);">
                    <td class="text-center font-mono text-secondary">-</td>
                    <td class="text-center font-mono">
                        <div class="fw-bold text-dark" style="font-size: 11px;">{{ date('d/m/Y', strtotime($startDate)) }}</div>
                    </td>
                    <td class="font-mono fw-bold text-primary text-nowrap">SALDO AWAL</td>
                    <td>
                        <span class="badge-cat" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">Saldo Awal</span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">Saldo Awal Kas Kecil per {{ date('d/m/Y', strtotime($startDate)) }}</div>
                    </td>
                    <td class="text-end font-mono text-muted">-</td>
                    <td class="text-end font-mono text-muted">-</td>
                    <td class="text-end font-mono fw-bold text-dark">
                        {{ number_format((float)($saldoAwal ?? 0), 2, ',', '.') }}
                    </td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end text-uppercase pe-3" style="letter-spacing: 0.3px;">
                        TOTAL MUTASI PERIODE INI:
                    </td>
                    <td class="text-end font-mono text-success fw-bold">
                        {{ $totalMasuk > 0 ? 'Rp ' . number_format($totalMasuk, 2, ',', '.') : 'Rp 0,00' }}
                    </td>
                    <td class="text-end font-mono text-danger fw-bold">
                        {{ $totalKeluar > 0 ? 'Rp ' . number_format($totalKeluar, 2, ',', '.') : 'Rp 0,00' }}
                    </td>
                    <td class="text-end font-mono text-dark fw-bold">
                        Rp {{ number_format($saldoAkhirPeriod ?? ($saldoAwal + $totalMasuk - $totalKeluar), 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>


        <!-- LEMBAR TANDA TANGAN / OTORISASI RESMI -->
        <table class="signature-table" id="tableSignature">
            <tr>
                <td class="signature-box" id="sigBox1">
                    <div class="signature-role-row">
                        <div class="signature-role text-muted editable-field" id="sigRole1" contenteditable="true" title="Klik untuk edit judul (cth: Dibuat)">Dibuat</div>
                    </div>
                    <div class="signature-line-box"></div>
                    <div class="signature-name-row">
                        <div class="signature-name font-mono editable-field" id="sigName1" contenteditable="true" title="Klik untuk ketik nama penandatangan">( _________________________ )</div>
                    </div>
                    <div class="signature-sub-row">
                        <div class="signature-title-sub text-muted editable-field" id="sigSub1" contenteditable="true" title="Klik untuk edit jabatan (di bawah nama)">Staff Kasir</div>
                    </div>
                </td>
                <td class="signature-box" id="sigBox2">
                    <div class="signature-role-row">
                        <div class="signature-role text-muted editable-field" id="sigRole2" contenteditable="true" title="Klik untuk edit judul (cth: Diperiksa)">Diperiksa</div>
                    </div>
                    <div class="signature-line-box"></div>
                    <div class="signature-name-row">
                        <div class="signature-name font-mono editable-field" id="sigName2" contenteditable="true" title="Klik untuk ketik nama penandatangan">( _________________________ )</div>
                    </div>
                    <div class="signature-sub-row">
                        <div class="signature-title-sub text-muted editable-field" id="sigSub2" contenteditable="true" title="Klik untuk edit jabatan (di bawah nama)">Supervisor Keuangan</div>
                    </div>
                </td>
                <td class="signature-box" id="sigBox3">
                    <div class="signature-role-row">
                        <div class="signature-role text-muted editable-field" id="sigRole3" contenteditable="true" title="Klik untuk edit judul (cth: Diketahui)">Diketahui</div>
                    </div>
                    <div class="signature-line-box"></div>
                    <div class="signature-name-row">
                        <div class="signature-name font-mono editable-field" id="sigName3" contenteditable="true" title="Klik untuk ketik nama penandatangan">( _________________________ )</div>
                    </div>
                    <div class="signature-sub-row">
                        <div class="signature-title-sub text-muted editable-field" id="sigSub3" contenteditable="true" title="Klik untuk edit jabatan (di bawah nama)">Kepala Cabang / Pimpinan</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    <!-- MODAL EDIT TANDA TANGAN (SCREEN ONLY) -->
    <div class="modal fade no-print" id="modalEditTtd" tabindex="-1" aria-labelledby="modalEditTtdLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 py-3" style="background: #f8fafc;">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalEditTtdLabel">
                        <i class="bi bi-pencil-square text-primary"></i>
                        <span>Pengaturan Penandatangan (TTD)</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2.5 rounded-3 bg-light border">
                        <div>
                            <span class="fw-semibold text-dark small d-block">Jumlah Kolom Tanda Tangan:</span>
                            <small class="text-muted" style="font-size: 11px;">Pilih 3 kolom (lengkap) atau 2 kolom (pembuat &amp; penyetuju)</small>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="colCount" id="colCount3" value="3" checked>
                            <label class="btn btn-outline-primary px-3" for="colCount3">3 Kolom</label>
                            <input type="radio" class="btn-check" name="colCount" id="colCount2" value="2">
                            <label class="btn btn-outline-primary px-3" for="colCount2">2 Kolom</label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- KOLOM 1: DIBUAT -->
                        <div class="col-12 col-md-4" id="modalColWrap1">
                            <div class="card h-100 border p-3 rounded-3" style="background: #f8fafc;">
                                <span class="badge bg-primary-subtle text-primary fw-bold mb-2 align-self-start">Kolom 1 (Pembuat)</span>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Judul Otorisasi:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputTitle1" value="Dibuat">
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Nama Lengkap:</label>
                                    <input type="text" class="form-control form-control-sm font-mono" id="inputName1" placeholder="Nama Petugas Kasir">
                                </div>
                                <div>
                                    <label class="small text-muted mb-1 fw-semibold">Jabatan / Bagian:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputSub1" value="Staff Kasir">
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM 2: DIPERIKSA -->
                        <div class="col-12 col-md-4" id="modalColWrap2">
                            <div class="card h-100 border p-3 rounded-3" style="background: #f8fafc;">
                                <span class="badge bg-info-subtle text-info fw-bold mb-2 align-self-start">Kolom 2 (Pemeriksa)</span>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Judul Otorisasi:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputTitle2" value="Diperiksa">
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Nama Lengkap:</label>
                                    <input type="text" class="form-control form-control-sm font-mono" id="inputName2" placeholder="Nama Supervisor">
                                </div>
                                <div>
                                    <label class="small text-muted mb-1 fw-semibold">Jabatan / Bagian:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputSub2" value="Supervisor Keuangan">
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM 3: DIKETAHUI -->
                        <div class="col-12 col-md-4" id="modalColWrap3">
                            <div class="card h-100 border p-3 rounded-3" style="background: #f8fafc;">
                                <span class="badge bg-success-subtle text-success fw-bold mb-2 align-self-start">Kolom 3 (Penyetuju)</span>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Judul Otorisasi:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputTitle3" value="Diketahui">
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-1 fw-semibold">Nama Lengkap:</label>
                                    <input type="text" class="form-control form-control-sm font-mono" id="inputName3" placeholder="Kepala Cabang / Pimpinan">
                                </div>
                                <div>
                                    <label class="small text-muted mb-1 fw-semibold">Jabatan / Bagian:</label>
                                    <input type="text" class="form-control form-control-sm" id="inputSub3" value="Kepala Cabang / Pimpinan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-2 rounded-2 bg-light border text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary fs-5"></i>
                        <span>Tips: Anda juga bisa langsung klik &amp; ketik nama atau jabatan langsung di lembar dokumen cetak (klik pada teks ttd).</span>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-2.5 bg-light justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="btnResetTtd">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Default
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" id="btnApplyTtd" style="background: var(--blue-primary); border-color: var(--blue-primary);">
                            <i class="bi bi-check-lg me-1"></i> Terapkan Tanda Tangan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const STORAGE_KEY = 'petty_cash_ttd_data';

            // Helper format nama
            function cleanNameDisplay(val) {
                const trimmed = (val || '').trim();
                return (trimmed && trimmed !== '_' && trimmed !== '( _________________________ )') ? `( ${trimmed} )` : '( _________________________ )';
            }

            function extractRawName(val) {
                let cleaned = (val || '').replace(/^\(\s*/, '').replace(/\s*\)$/, '').trim();
                if (cleaned.includes('____') || cleaned === '') return '';
                return cleaned;
            }

            // 1. Muat TTD dari LocalStorage jika ada
            function loadTtdData() {
                try {
                    const saved = localStorage.getItem(STORAGE_KEY);
                    if (saved) {
                        const data = JSON.parse(saved);
                        if (data.role1) $('#sigRole1').text(data.role1);
                        if (data.name1 !== undefined) $('#sigName1').text(cleanNameDisplay(data.name1));
                        if (data.sub1) $('#sigSub1').text(data.sub1);

                        if (data.role2) $('#sigRole2').text(data.role2);
                        if (data.name2 !== undefined) $('#sigName2').text(cleanNameDisplay(data.name2));
                        if (data.sub2) $('#sigSub2').text(data.sub2);

                        if (data.role3) $('#sigRole3').text(data.role3);
                        if (data.name3 !== undefined) $('#sigName3').text(cleanNameDisplay(data.name3));
                        if (data.sub3) $('#sigSub3').text(data.sub3);

                        if (data.colCount == 2) {
                            $('#sigBox2').hide();
                            $('#sigBox1, #sigBox3').css('width', '50%');
                            $('#colCount2').prop('checked', true);
                            $('#modalColWrap2').addClass('opacity-50');
                        } else {
                            $('#sigBox2').show();
                            $('#sigBox1, #sigBox2, #sigBox3').css('width', '33.33%');
                            $('#colCount3').prop('checked', true);
                            $('#modalColWrap2').removeClass('opacity-50');
                        }
                    }
                } catch(e) {
                    console.error('Failed to load TTD storage', e);
                }
            }

            // Simpan state saat ini ke LocalStorage
            function saveCurrentTtd() {
                const data = {
                    colCount: $('input[name="colCount"]:checked').val() || 3,
                    role1: $('#sigRole1').text().trim(),
                    name1: extractRawName($('#sigName1').text()),
                    sub1: $('#sigSub1').text().trim(),
                    role2: $('#sigRole2').text().trim(),
                    name2: extractRawName($('#sigName2').text()),
                    sub2: $('#sigSub2').text().trim(),
                    role3: $('#sigRole3').text().trim(),
                    name3: extractRawName($('#sigName3').text()),
                    sub3: $('#sigSub3').text().trim()
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            }

            loadTtdData();

            // 2. Buka Modal Edit TTD: Populate values
            $('#modalEditTtd').on('show.bs.modal', function() {
                $('#inputTitle1').val($('#sigRole1').text().trim());
                $('#inputName1').val(extractRawName($('#sigName1').text()));
                $('#inputSub1').val($('#sigSub1').text().trim());

                $('#inputTitle2').val($('#sigRole2').text().trim());
                $('#inputName2').val(extractRawName($('#sigName2').text()));
                $('#inputSub2').val($('#sigSub2').text().trim());

                $('#inputTitle3').val($('#sigRole3').text().trim());
                $('#inputName3').val(extractRawName($('#sigName3').text()));
                $('#inputSub3').val($('#sigSub3').text().trim());
            });

            // Toggle radio 2 atau 3 kolom
            $('input[name="colCount"]').on('change', function() {
                if ($(this).val() == '2') {
                    $('#modalColWrap2').addClass('opacity-50');
                } else {
                    $('#modalColWrap2').removeClass('opacity-50');
                }
            });

            // 3. Terapkan TTD dari Modal
            $('#btnApplyTtd').on('click', function() {
                const cols = $('input[name="colCount"]:checked').val();

                $('#sigRole1').text($('#inputTitle1').val().trim() || 'Dibuat');
                $('#sigName1').text(cleanNameDisplay($('#inputName1').val()));
                $('#sigSub1').text($('#inputSub1').val().trim());

                $('#sigRole2').text($('#inputTitle2').val().trim() || 'Diperiksa');
                $('#sigName2').text(cleanNameDisplay($('#inputName2').val()));
                $('#sigSub2').text($('#inputSub2').val().trim());

                $('#sigRole3').text($('#inputTitle3').val().trim() || 'Diketahui');
                $('#sigName3').text(cleanNameDisplay($('#inputName3').val()));
                $('#sigSub3').text($('#inputSub3').val().trim());

                if (cols == '2') {
                    $('#sigBox2').hide();
                    $('#sigBox1, #sigBox3').css('width', '50%');
                } else {
                    $('#sigBox2').show();
                    $('#sigBox1, #sigBox2, #sigBox3').css('width', '33.33%');
                }

                saveCurrentTtd();
                $('#modalEditTtd').modal('hide');

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Tanda tangan berhasil diperbarui!',
                    showConfirmButton: false,
                    timer: 2000
                });
            });

            // 4. Reset TTD ke Default
            $('#btnResetTtd').on('click', function() {
                localStorage.removeItem(STORAGE_KEY);
                $('#sigRole1').text('Dibuat');
                $('#sigName1').text('( _________________________ )');
                $('#sigSub1').text('Staff Kasir');

                $('#sigRole2').text('Diperiksa');
                $('#sigName2').text('( _________________________ )');
                $('#sigSub2').text('Supervisor Keuangan');

                $('#sigRole3').text('Diketahui');
                $('#sigName3').text('( _________________________ )');
                $('#sigSub3').text('Kepala Cabang / Pimpinan');

                $('#sigBox2').show();
                $('#sigBox1, #sigBox2, #sigBox3').css('width', '33.33%');
                $('#colCount3').prop('checked', true);
                $('#modalColWrap2').removeClass('opacity-50');

                $('#modalEditTtd').modal('hide');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Format TTD direset ke bawaan.',
                    showConfirmButton: false,
                    timer: 2000
                });
            });

            // 5. Dukungan Klik Langsung Edit di Dokumen (contenteditable auto-save)
            $('.editable-field').on('blur', function() {
                const el = $(this);
                if (el.hasClass('signature-name')) {
                    const raw = extractRawName(el.text());
                    el.text(cleanNameDisplay(raw));
                }
                saveCurrentTtd();
            });

            // 6. FITUR SCREENSHOT (PNG)
            $('#btnScreenshot').on('click', async function() {
                const btn = $(this);
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5"></span><span>Mengambil Gambar...</span>');

                try {
                    const reportElement = document.getElementById('reportCanvas');
                    
                    // Hilangkan outline fokus jika ada sebelum capture
                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }

                    const canvas = await html2canvas(reportElement, {
                        scale: 2, // Kualitas HD Retina
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        logging: false
                    });

                    // 1. Download sebagai file PNG
                    const entitySlug = '{{ \Illuminate\Support\Str::slug($activeEntityName) }}' || 'laporan_kas';
                    const fileName = `Laporan_Kas_Kecil_${entitySlug}_${new Date().toISOString().slice(0, 10)}.png`;
                    
                    const link = document.createElement('a');
                    link.download = fileName;
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    // 2. Salin gambar ke Clipboard (agar bisa langsung Ctrl+V di WhatsApp / Chat)
                    let copiedToClipboard = false;
                    if (navigator.clipboard && window.ClipboardItem) {
                        try {
                            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                            if (blob) {
                                await navigator.clipboard.write([
                                    new ClipboardItem({ 'image/png': blob })
                                ]);
                                copiedToClipboard = true;
                            }
                        } catch (clipErr) {
                            console.log('Clipboard copy permission error', clipErr);
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Screenshot Berhasil Diambil!',
                        html: `Gambar resolusi tinggi telah diunduh: <strong>${fileName}</strong>` +
                              (copiedToClipboard ? '<br><div class="mt-2 text-success small fw-semibold"><i class="bi bi-clipboard-check me-1"></i> Gambar juga otomatis disalin ke Clipboard! Anda bisa langsung tekan <strong>Ctrl + V</strong> di WhatsApp atau Chat.</div>' : ''),
                        confirmButtonColor: '#1d4ed8',
                        confirmButtonText: 'Mantap!'
                    });

                } catch (err) {
                    console.error('Screenshot error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengambil Screenshot',
                        text: 'Terjadi kendala: ' + err.message,
                        confirmButtonColor: '#1d4ed8'
                    });
                } finally {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    </script>
</body>
</html>
