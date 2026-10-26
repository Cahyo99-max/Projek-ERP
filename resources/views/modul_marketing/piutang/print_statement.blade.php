<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Piutang - {{ $customer->nama_perusahaan ?: $customer->nama_customer }}</title>
    
    <!-- Favicon Logo Aplikasi -->
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- html2canvas for High-Res Image Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root {
            --primary-navy: #1e3a8a;
            --primary-blue: #1e40af;
            --primary-light: #eff6ff;
            --border-blue: #bfdbfe;
            --border-color: #cbd5e1;
            --border-light: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background-color: #f0f4f8;
            color: var(--text-dark);
            font-size: 11px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Container Paper (Dioptimasi untuk A4) ──────────────────── */
        .statement-card {
            width: 100%;
            max-width: 840px;
            margin: 20px auto;
            background: #ffffff;
            padding: 24px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(30, 64, 175, 0.08);
            border: 1px solid #bfdbfe;
            border-top: 4px solid #1e40af;
            position: relative;
        }

        /* ── Header Kop Surat ───────────────────────────────────────── */
        .company-header {
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .company-logo {
            max-height: 50px;
            max-width: 140px;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.15rem;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.25);
        }

        .company-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.2px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e40af;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        /* ── Meta Table Info Pelanggan & Dokumen ─────────────────────── */
        .meta-box {
            background-color: #f8faff;
            border: 1px solid #bfdbfe;
            border-left: 3.5px solid #1e40af;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        /* ── Tabel Faktur Penjualan ─────────────────────────────────── */
        .table-statement {
            width: 100% !important;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10.5px;
            table-layout: auto;
        }

        .table-statement thead th {
            background-color: #1e40af !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6.5px 7px;
            border: 1px solid #1d4ed8;
            vertical-align: middle;
        }

        .table-statement tbody td {
            vertical-align: middle;
            padding: 5.5px 7px;
            border: 1px solid #e2e8f0;
        }

        .table-statement tbody tr:nth-child(even) {
            background-color: #f8faff;
        }

        .table-statement tfoot td {
            padding: 6.5px 7px;
            border: 1px solid #cbd5e1;
            border-top: 2px solid #1e40af;
            background-color: #eff6ff;
            color: #1e3a8a;
        }

        /* ── Status Badges Compact ──────────────────────────────────── */
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            white-space: nowrap;
        }

        .badge-lunas {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-overdue {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-today {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-current {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* ── Bottom Section: Bank & Total ───────────────────────────── */
        .bottom-card {
            background-color: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 8px 12px;
            height: 100%;
        }

        .bank-item {
            background-color: #f0f7ff;
            border: 1px solid #dbeafe;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 10.5px;
            margin-bottom: 3px;
        }

        .total-highlight-card {
            background-color: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* ── Signatures ─────────────────────────────────────────────── */
        .signatures-row {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-block {
            text-align: center;
            padding: 2px 8px;
        }

        .signature-title {
            font-size: 10px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 44px;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 80%;
            margin: 0 auto 4px auto;
        }

        /* ── Mode Edit Manual (Live Free-Text Editing) ──────────────── */
        .editable-field {
            transition: all 0.15s ease-in-out;
            border-radius: 3px;
            padding: 1px 3px;
        }

        .editing-active .editable-field {
            background-color: #fffbeb !important;
            outline: 1.5px dashed #f59e0b !important;
            cursor: text;
        }

        .editing-active .editable-field:hover {
            background-color: #fef3c7 !important;
            outline: 1.5px dashed #d97706 !important;
        }

        .editing-active .editable-field:focus {
            background-color: #ffffff !important;
            outline: 2px solid #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
        }

        .editing-active .table-statement tbody td {
            background-color: #fffbeb !important;
            outline: 1px dashed #fbbf24 !important;
            cursor: text;
        }

        .editing-active .table-statement tbody td:focus {
            background-color: #ffffff !important;
            outline: 2px solid #2563eb !important;
        }

        /* ── Print Controls Toolbar ─────────────────────────────────── */
        .no-print {
            max-width: 840px;
            margin: 15px auto;
        }

        /* ── Print Styles (Clean A4 Fit) ────────────────────────────── */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .statement-card {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10mm 12mm !important;
                border: none !important;
                border-top: 4px solid #1e40af !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .company-header {
                border-bottom: 2.5px solid #1e40af !important;
            }
            .table-statement thead th {
                background-color: #1e40af !important;
                color: #ffffff !important;
            }
            .editable-field, .table-statement tbody td {
                background-color: transparent !important;
                outline: none !important;
                box-shadow: none !important;
            }
            .no-print, #edit_mode_banner {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @php
        // Resolusi Logo Entitas: Cabang prioritas, jika tidak ada fallback ke Company / Default
        $activeLogo = null;
        if ($printScope === 'branch' && $branch && !empty($branch->logo)) {
            if (file_exists(public_path('storage/' . $branch->logo))) {
                $activeLogo = asset('storage/' . $branch->logo);
            } elseif (file_exists(public_path($branch->logo))) {
                $activeLogo = asset($branch->logo);
            }
        }
        if (!$activeLogo && $company && !empty($company->logo)) {
            if (file_exists(public_path('storage/' . $company->logo))) {
                $activeLogo = asset('storage/' . $company->logo);
            } elseif (file_exists(public_path($company->logo))) {
                $activeLogo = asset($company->logo);
            }
        }
        if (!$activeLogo && file_exists(public_path('gambar/logo.png'))) {
            $activeLogo = asset('gambar/logo.png');
        }

        // Perhitungan Akumulasi Sisa Piutang yang Konsisten
        $totalSisaPiutang = (float) $invoices->filter(fn($i) => (float)$i->sisa_tagihan > 0 && $i->status !== 'Paid')->sum('sisa_tagihan');
        $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
    @endphp

    <!-- Tombol Aksi / Kontrol Cetak (Hidden saat Print) -->
    <div class="no-print">
        <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-2.5 px-3 rounded-3 shadow-sm border gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-piutang'))
                <a href="{{ route('modul_marketing.piutang.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
                </a>
                @endif

                @if(empty($sessionBranchId))
                    <!-- Dropdown Pilihan Entitas Cetak (Khusus Akses Manajemen PT Pusat) -->
                    <div class="d-flex align-items-center gap-1.5 ms-sm-2">
                        <span class="small fw-bold text-muted d-none d-sm-inline"><i class="bi bi-filter-circle-fill text-primary"></i> Entitas Cetak:</span>
                        <select class="form-select form-select-sm rounded-pill fw-semibold border-primary-subtle" style="width: auto; min-width: 220px;" onchange="window.location.href=this.value">
                            <option value="{{ route('modul_marketing.piutang.print_statement', $customer->id) }}" {{ $printScope === 'pusat' ? 'selected' : '' }}>
                                🏢 Transaksi PT Pusat
                            </option>
                            @foreach($availableBranches as $b)
                                <option value="{{ route('modul_marketing.piutang.print_statement', $customer->id) }}?branch_id={{ $b->id }}" {{ ($printScope === 'branch' && $branch?->id == $b->id) ? 'selected' : '' }}>
                                    🏪 Cabang: {{ $b->nama_cabang }} 
                                </option>
                            @endforeach
                            <option value="{{ route('modul_marketing.piutang.print_statement', $customer->id) }}?branch_id=all" {{ $printScope === 'all' ? 'selected' : '' }}>
                                🌐 Konsolidasi Seluruh Cabang &amp; Pusat
                            </option>
                        </select>
                    </div>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 font-monospace">
                        <i class="bi bi-geo-alt-fill me-1"></i>Cabang: {{ $branch->nama_cabang ?? 'Cabang Aktif' }}
                    </span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold" id="btn_toggle_edit" onclick="toggleEditMode()" title="Edit teks langsung di halaman sebelum dicetak">
                    <i class="bi bi-pencil-square me-1"></i> <span id="text_edit_mode">Edit Manual</span>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" id="btn_export_image">
                    <i class="bi bi-image me-1"></i> Unduh PNG
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" onclick="prepareAndPrint()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak (A4)
                </button>
            </div>
        </div>

        <!-- Banner Info Mode Edit Manual -->
        <div id="edit_mode_banner" class="alert alert-warning py-2 px-3 mt-2 mb-0 rounded-3 small d-none align-items-center justify-content-between border-warning shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-pencil-fill text-warning-emphasis fs-6"></i>
                <span><strong>Mode Edit Manual Aktif:</strong> Anda dapat mengklik dan mengubah langsung teks (nama, tanggal, alamat, kontak, rekening, tanda tangan, atau baris tabel) pada dokumen sebelum mencetak atau mengunduh.</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 rounded-pill font-mono" style="font-size: 10px;" onclick="window.location.reload()" title="Kembalikan semua data ke versi asli">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Asli
            </button>
        </div>
    </div>

    <!-- Container Dokumen Kartu Piutang -->
    <div class="statement-card" id="printable_statement">
        
        <!-- Kop Surat Perusahaan / Cabang -->
        <div class="company-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                @if($activeLogo)
                    <img src="{{ $activeLogo }}" alt="Logo {{ ($printScope === 'branch' && $branch) ? $branch->nama_cabang : ($company->nama_perusahaan ?? 'Perusahaan') }}" class="company-logo">
                @else
                    <div class="logo-placeholder">
                        {{ strtoupper(substr(($printScope === 'branch' && $branch) ? $branch->nama_cabang : ($company->nama_perusahaan ?? 'ERP'), 0, 2)) }}
                    </div>
                @endif
                <div>
                    @if($printScope === 'branch' && $branch)
                        <div class="company-title editable-field" spellcheck="false">
                            {{ strtoupper($branch->nama_cabang) }}
                        </div>
                        <div class="text-muted font-mono small fw-semibold editable-field" style="font-size: 10.5px;" spellcheck="false">
                            Cabang Kode: {{ $branch->kode_cabang }}
                        </div>
                    @elseif($printScope === 'all')
                        <div class="company-title editable-field" spellcheck="false">{{ $company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA' }}</div>
                        <div class="text-muted font-mono small fw-semibold editable-field" style="font-size: 10.5px;" spellcheck="false">
                            Laporan Konsolidasi Seluruh Cabang &amp; Pusat
                        </div>
                    @else
                        <div class="company-title editable-field" spellcheck="false">{{ $company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA' }}</div>
                        <div class="text-muted font-mono small editable-field" style="font-size: 10.5px;" spellcheck="false">
                            Kantor Pusat | Telp: {{ $company->phone ?? '-' }} | Email: {{ $company->email ?? '-' }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="text-end">
                <div class="doc-title editable-field" spellcheck="false">KARTU PIUTANG</div>
               
                <div class="text-muted font-mono small" style="font-size: 10px;">
                    Per Tanggal: <strong class="editable-field" spellcheck="false">{{ now()->translatedFormat('d F Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Informasi Pelanggan & Ketentuan (Clean Compact Grid) -->
        <div class="meta-box">
            <div class="row g-2">
                <div class="col-7 pe-2 border-end" style="border-color: #dbeafe !important;">
                    <div class="text-uppercase fw-bold mb-0.5" style="font-size: 9px; letter-spacing: 0.5px; color: #1e40af;">Kepada (Debitur):</div>
                    <div class="fw-bold text-dark editable-field" style="font-size: 12.5px;" spellcheck="false">{{ $customer->nama_perusahaan ?: $customer->nama_customer }}</div>
                    @if($customer->nama_perusahaan && $customer->nama_customer)
                        <div class="text-secondary small" style="font-size: 10.5px;">PIC: <strong class="editable-field" spellcheck="false">{{ $customer->nama_customer }}</strong></div>
                    @endif
                    <div class="text-secondary small text-truncate editable-field" style="font-size: 10.5px; max-width: 420px;" spellcheck="false">
                        {{ $customer->alamat ?: ($customer->defaultAddress?->alamat_lengkap ?? '-') }}
                    </div>
                    <div class="text-secondary small font-mono" style="font-size: 10px;">
                        Kontak: <span class="editable-field" spellcheck="false">{{ $customer->hp ?: ($customer->telepon ?: '-') }}</span>
                    </div>
                </div>
                <div class="col-5 ps-2">
                    <div class="text-uppercase fw-bold mb-0.5" style="font-size: 9px; letter-spacing: 0.5px; color: #1e40af;">Informasi Piutang:</div>
                    <table class="table table-sm table-borderless mb-0" style="font-size: 10.5px;">
                        <tr>
                            <td class="p-0 text-muted" style="width: 100px;">Kode Debitur</td>
                            <td class="p-0 font-mono fw-bold text-primary">: [<span class="editable-field" spellcheck="false">{{ $customer->kode_customer }}</span>]</td>
                        </tr>
                        <tr>
                            <td class="p-0 text-muted">Termin Bayar</td>
                            <td class="p-0 font-mono">: <span class="editable-field" spellcheck="false">{{ $customer->paymentTerm?->nama_term ?? 'Net 30' }}</span></td>
                        </tr>
                        <tr>
                            <td class="p-0 text-muted">Batas Kredit</td>
                            <td class="p-0 font-mono">: <span class="editable-field" spellcheck="false">Rp {{ $fmtRupiah($customer->credit_limit ?? 0) }}</span></td>
                        </tr>
                        <tr>
                            <td class="p-0 text-muted fw-bold" style="color: #1e40af;">Total Sisa Tagihan</td>
                            <td class="p-0 font-mono fw-bold" style="color: #1e40af;">: <span class="editable-field" spellcheck="false">Rp {{ $fmtRupiah($totalSisaPiutang) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rincian Faktur Penjualan (Clean Table 100% Fit A4 Tanpa Scrollbar) -->
        <table class="table-statement">
            <thead>
                <tr>
                    <th class="text-center" style="width: 25px;">No</th>
                    <th style="width: 26%;">No. Faktur</th>
                    @if($printScope === 'all')
                        <th class="text-center" style="width: 12%;">Cabang</th>
                    @endif
                    <th class="text-center" style="width: 11%;">Tgl Faktur</th>
                    <th class="text-center" style="width: 11%;">Jatuh Tempo</th>
                    <th class="text-center" style="width: 13%;">Status Tempo</th>
                    <th class="text-end" style="width: 13%;">Nilai Faktur</th>
                    <th class="text-end" style="width: 12%;">Terbayar</th>
                    <th class="text-end" style="width: 14%;">Sisa Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumGrand = 0;
                    $sumPaid = 0;
                    $sumSisa = 0;
                @endphp
                @forelse($invoices as $idx => $inv)
                @php
                    $grand = (float) $inv->grand_total;
                    $dp = (float) $inv->uang_muka;
                    $sisa = (float) $inv->sisa_tagihan;
                    $paid = max(0, $grand - $sisa);
                    $sumGrand += $grand;
                    $sumPaid += $paid;
                    $sumSisa += $sisa;

                    $daysOverdue = 0;
                    $daysRemaining = 0;
                    $dueStatus = 'no_date';

                    if ($inv->tanggal_jatuh_tempo) {
                        $dueDate = \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay();
                        $today = \Carbon\Carbon::now()->startOfDay();

                        if ($sisa > 0 && $inv->status !== 'Paid') {
                            if ($today->gt($dueDate)) {
                                $daysOverdue = abs((int) $today->diffInDays($dueDate));
                                $dueStatus = 'overdue';
                            } elseif ($today->eq($dueDate)) {
                                $dueStatus = 'today';
                            } else {
                                $daysRemaining = abs((int) $today->diffInDays($dueDate));
                                $dueStatus = 'upcoming';
                            }
                        } else {
                            $dueStatus = 'paid';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td class="fw-bold font-mono text-dark" style="white-space: nowrap;">{{ $inv->nomor_faktur }}</td>
                    @if($printScope === 'all')
                        <td class="text-center font-mono small">
                            <span class="badge {{ $inv->branch ? 'bg-secondary-subtle text-dark border' : 'bg-light text-dark border' }}">
                                {{ $inv->branch ? $inv->branch->nama_cabang : 'Pusat' }}
                            </span>
                        </td>
                    @endif
                    <td class="text-center font-mono">{{ $inv->tanggal_faktur ? \Carbon\Carbon::parse($inv->tanggal_faktur)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center font-mono">{{ $inv->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @if($sisa <= 0 || $inv->status === 'Paid')
                            <span class="badge-status badge-lunas">Lunas</span>
                        @elseif($dueStatus === 'overdue')
                            <span class="badge-status badge-overdue">Lewat {{ $daysOverdue }} Hari</span>
                        @elseif($dueStatus === 'today')
                            <span class="badge-status badge-today">Tempo Hari Ini</span>
                        @else
                            <span class="badge-status badge-current">Belum Tempo</span>
                        @endif
                    </td>
                    <td class="text-end font-mono">Rp {{ $fmtRupiah($grand) }}</td>
                    <td class="text-end font-mono text-success">
                        <div>Rp {{ $fmtRupiah($paid) }}</div>
                        @if($dp > 0 && $sisa > 0)
                            <div class="text-muted" style="font-size: 8px;">(DP: Rp {{ $fmtRupiah($dp) }})</div>
                        @endif
                    </td>
                    <td class="text-end font-mono fw-bold {{ $sisa > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ $fmtRupiah($sisa) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $printScope === 'all' ? 9 : 8 }}" class="text-center py-3 text-muted">Tidak ada riwayat transaksi faktur untuk pelanggan ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="{{ $printScope === 'all' ? 6 : 5 }}" class="text-end text-uppercase" style="color: #1e3a8a;">TOTAL AKUMULASI:</td>
                    <td class="text-end font-mono" style="color: #1e3a8a;">Rp {{ $fmtRupiah($sumGrand) }}</td>
                    <td class="text-end font-mono text-success">Rp {{ $fmtRupiah($sumPaid) }}</td>
                    <td class="text-end font-mono fw-bold" style="color: #1e40af;">Rp {{ $fmtRupiah($sumSisa) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Instruksi Pembayaran & Total Bayar (Compact 2-Column) -->
        <div class="row g-2 mb-2">
            <div class="col-7">
                <div class="bottom-card">
                    <div class="fw-bold mb-1" style="font-size: 10px; color: #1e3a8a;">
                        <i class="bi bi-bank2 me-1" style="color: #1e40af;"></i> REKENING PEMBAYARAN RESMI:
                    </div>
                    <div class="text-muted mb-1 editable-field" style="font-size: 9.5px;" spellcheck="false">
                        Mohon transfer pembayaran piutang hanya ke rekening resmi berikut:
                    </div>
                    @forelse($bankAccounts as $bank)
                        <div class="bank-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="font-mono editable-field" style="color: #1e40af;" spellcheck="false">{{ $bank->nama_bank ?: $bank->nama_akun }}</strong>: 
                                <span class="font-mono fw-bold editable-field" style="color: #0f172a;" spellcheck="false">{{ $bank->nomor_rekening }}</span>
                            </div>
                            <span class="text-muted editable-field" style="font-size: 10px;" spellcheck="false">a.n. {{ $bank->atas_nama }}</span>
                        </div>
                    @empty
                        <div class="small text-muted fst-italic">Hubungi Finance untuk info rekening transfer.</div>
                    @endforelse
                    <div class="text-muted mt-1 fst-italic editable-field" style="font-size: 9px;" spellcheck="false">
                        *Cantumkan No. Faktur pada berita transfer dan konfirmasikan bukti bayar.
                    </div>
                </div>
            </div>
            <div class="col-5">
                <div class="total-highlight-card">
                    <div class="fw-bold text-uppercase editable-field" style="font-size: 9.5px; letter-spacing: 0.5px; color: #1e40af;" spellcheck="false">
                        Total Tagihan Harus Dibayar:
                    </div>
                    <div class="fw-bold font-mono my-1 editable-field" style="font-size: 1.25rem; color: #1e40af;" spellcheck="false">
                        Rp {{ $fmtRupiah($totalSisaPiutang) }}
                    </div>
                    @php
                        $oldestDue = $invoices->filter(fn($i) => (float)$i->sisa_tagihan > 0 && $i->status !== 'Paid' && !empty($i->tanggal_jatuh_tempo))->min('tanggal_jatuh_tempo');
                    @endphp
                    <div class="text-muted font-mono editable-field" style="font-size: 9.5px;" spellcheck="false">
                        Jatuh tempo terlama: <strong>{{ $oldestDue ? \Carbon\Carbon::parse($oldestDue)->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tanda Tangan Resmi Sesuai Entitas Penerbit -->
        <div class="row text-center signatures-row">
            <div class="col-4">
                <div class="signature-block">
                    <div class="signature-title editable-field" spellcheck="false">
                        @if($printScope === 'branch' && $branch)
                            Dibuat Oleh (Finance Cabang),
                        @else
                            Dibuat Oleh (Finance Pusat),
                        @endif
                    </div>
                    <div class="signature-line"></div>
                    <div class="fw-bold text-dark editable-field" style="font-size: 10.5px;" spellcheck="false">{{ auth()->user()?->nama_lengkap ?? auth()->user()?->name ?? 'Staff Finance' }}</div>
                    <div class="text-muted editable-field" style="font-size: 9px;" spellcheck="false">
                        @if($printScope === 'branch' && $branch)
                            Finance AR Cabang {{ $branch->nama_cabang }}
                        @else
                            Finance &amp; AR Officer
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-block">
                    <div class="signature-title editable-field" spellcheck="false">Mengetahui / Disetujui,</div>
                    <div class="signature-line"></div>
                    <div class="fw-bold text-dark editable-field" style="font-size: 10.5px;" spellcheck="false">
                        @if($printScope === 'branch' && $branch)
                            {{ $branch->manager_name ?: 'Pimpinan Cabang' }}
                        @else
                            Manager Keuangan
                        @endif
                    </div>
                    <div class="text-muted editable-field" style="font-size: 9px;" spellcheck="false">
                        @if($printScope === 'branch' && $branch)
                            Branch Manager {{ $branch->nama_cabang }}
                        @else
                            Finance &amp; Accounting Dept.
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-block">
                    <div class="signature-title editable-field" spellcheck="false">Diterima &amp; Dikonfirmasi Oleh,</div>
                    <div class="signature-line"></div>
                    <div class="fw-bold text-dark editable-field" style="font-size: 10.5px;" spellcheck="false">{{ $customer->nama_perusahaan ?: $customer->nama_customer }}</div>
                    <div class="text-muted editable-field" style="font-size: 9px;" spellcheck="false">Debitur / Bagian Keuangan</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Export Gambar & Mode Edit Manual -->
    <script>
        let isEditMode = false;

        // Toggle Live Free-Text Editing Mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const card = document.getElementById('printable_statement');
            const btn = document.getElementById('btn_toggle_edit');
            const banner = document.getElementById('edit_mode_banner');
            const editables = document.querySelectorAll('.editable-field, .table-statement tbody td');

            if (isEditMode) {
                card.classList.add('editing-active');
                btn.className = 'btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm';
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Selesai Edit';
                if (banner) {
                    banner.classList.remove('d-none');
                    banner.classList.add('d-flex');
                }

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                    el.setAttribute('spellcheck', 'false');
                });
            } else {
                if (document.activeElement) document.activeElement.blur();
                card.classList.remove('editing-active');
                btn.className = 'btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold';
                btn.innerHTML = '<i class="bi bi-pencil-square me-1"></i> Edit Manual';
                if (banner) {
                    banner.classList.add('d-none');
                    banner.classList.remove('d-flex');
                }

                editables.forEach(el => {
                    el.removeAttribute('contenteditable');
                });
            }
        }

        // Cetak Dokumen Bersih
        function prepareAndPrint() {
            if (document.activeElement) document.activeElement.blur();
            window.print();
        }
        document.getElementById('btn_export_image')?.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
            btn.disabled = true;

            if (document.activeElement) document.activeElement.blur();

            const element = document.getElementById('printable_statement');
            const wasEditing = isEditMode;
            if (wasEditing) {
                element.classList.remove('editing-active');
            }
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Kartu_Piutang_{{ str_replace(' ', '_', $customer->nama_customer) }}_{{ date('Ymd') }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

            }).catch(err => {
                alert('Gagal mengekspor gambar kartu piutang.');
            }).finally(() => {
                if (wasEditing) {
                    element.classList.add('editing-active');
                }
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>

</body>
</html>
