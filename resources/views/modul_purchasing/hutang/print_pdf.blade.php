<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Utang &amp; Aging Supplier - {{ $company->nama_perusahaan ?? 'ERP' }}</title>

    @php
        $appLogoSetting = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', 'app_logo')->value('value');
        $officialAppLogo = (!empty($appLogoSetting) && file_exists(public_path('storage/' . $appLogoSetting)))
            ? asset('storage/' . $appLogoSetting)
            : (file_exists(public_path('storage/gambar/logo_1786070712.png')) ? asset('storage/gambar/logo_1786070712.png') : ($appLogo ?? asset('gambar/logo.png')));
        $logoMtime = file_exists(public_path('storage/' . ($appLogoSetting ?: 'gambar/logo_1786070712.png')))
            ? filemtime(public_path('storage/' . ($appLogoSetting ?: 'gambar/logo_1786070712.png')))
            : time();
    @endphp
    <!-- Favicon Logo Aplikasi -->
    <link rel="shortcut icon" href="{{ $officialAppLogo }}?v={{ $logoMtime }}" type="image/x-icon">
    <link rel="icon" href="{{ $officialAppLogo }}?v={{ $logoMtime }}" type="image/png">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue-950: #0f172a;
            --blue-900: #1e3a8a;
            --blue-800: #1e40af;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-400: #60a5fa;
            --blue-200: #bfdbfe;
            --blue-100: #dbeafe;
            --blue-50:  #eff6ff;
            --blue-ice: #f8faff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            font-size: 11px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Container Paper (Dioptimasi untuk A4 Landscape) ── */
        .report-paper {
            width: 100%;
            max-width: 1140px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px 36px;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(30, 58, 138, 0.08);
            border: 1px solid var(--blue-200);
            position: relative;
            overflow: hidden;
        }

        /* Top Blue Accent Ribbon */
        .report-paper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #60a5fa 100%);
        }

        /* ── Header Kop Surat Modern ── */
        .company-header {
            border-bottom: 2px solid var(--blue-100);
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .company-logo {
            max-height: 52px;
            max-width: 150px;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            letter-spacing: -0.02em;
        }

        .company-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--blue-900);
            letter-spacing: -0.02em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .report-main-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--blue-900);
            letter-spacing: -0.02em;
            text-transform: uppercase;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }

        /* ── Filter Meta Strip Modern ── */
        .meta-strip {
            background: linear-gradient(135deg, #f0f7ff 0%, #eff6ff 100%);
            border: 1px solid var(--blue-200);
            border-radius: 10px;
            padding: 10px 16px;
            margin-bottom: 16px;
        }

        /* ── Metric Cards Modern Blue ── */
        .metric-card {
            background: #ffffff;
            border: 1.5px solid var(--blue-100);
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.04);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .metric-card-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .metric-card-value {
            font-size: 1.25rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.2;
            margin-top: 4px;
        }

        /* ── Table Styling Ultra Modern ── */
        .table-report-wrapper {
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid var(--blue-200);
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(30, 58, 138, 0.03);
        }

        .table-report {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 10.5px;
            margin-bottom: 0;
        }

        .table-report thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.07em;
            padding: 12px 12px;
            border: none;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            vertical-align: middle;
            text-align: center;
        }
        .table-report thead th:last-child {
            border-right: none;
        }

        .table-report tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid var(--blue-100);
            border-right: 1px solid rgba(226, 232, 240, 0.7);
            vertical-align: middle;
            color: #1e293b;
        }
        .table-report tbody td:last-child {
            border-right: none;
        }

        .table-report tbody tr:nth-child(even) {
            background-color: var(--blue-ice);
        }

        .table-report tfoot th {
            background: #eff6ff;
            font-weight: 800;
            padding: 12px 12px;
            border-top: 2px solid var(--blue-600);
            border-right: 1px solid var(--blue-200);
            vertical-align: middle;
            font-family: 'JetBrains Mono', monospace;
        }
        .table-report tfoot th:last-child {
            border-right: none;
        }

        /* ── Supplier Avatar & Pods ── */
        .supplier-print-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(59, 130, 246, 0.22));
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: var(--blue-700);
            font-weight: 800;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Badge Styling Modern ── */
        .badge-aging {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 50px;
            font-size: 9px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            border: 1px solid transparent;
            text-align: center;
            white-space: nowrap;
        }
        .badge-lancar { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .badge-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-orange { background: #ffedd5; color: #9a3412; border-color: #fed7aa; }
        .badge-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

        /* ── Signature Section Modern ── */
        .signature-box {
            border: 1.5px dashed var(--blue-200);
            border-radius: 10px;
            background: #ffffff;
            padding: 12px 14px;
            text-align: center;
            height: 105px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
        }

        /* ── Floating Print Button ── */
        .no-print-bar {
            position: sticky;
            top: 15px;
            z-index: 1000;
            margin: 0 auto 15px auto;
            max-width: 1140px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            backdrop-filter: blur(12px);
            padding: 10px 20px;
            border-radius: 50px;
            color: #ffffff;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .report-paper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .report-paper::before {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 8mm 8mm 8mm 8mm;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Top Action Bar -->
    <div class="no-print-bar">
        <div class="d-flex align-items-center gap-2.5">
            <span class="badge bg-white text-primary rounded-pill px-3 py-1.5 font-monospace fw-bold">
                <i class="bi bi-file-earmark-pdf-fill me-1 text-primary"></i>MODE CETAK PDF
            </span>
            <span class="small opacity-75 d-none d-sm-inline">Tema Biru Modern &bull; Orientasi landscape disarankan</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-primary shadow-sm">
                <i class="bi bi-printer-fill me-1.5 text-primary"></i>Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light rounded-pill px-3">
                <i class="bi bi-x-lg me-1"></i>Tutup
            </button>
        </div>
    </div>

    @php
        $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
    @endphp

    <div class="report-paper">

        @php
            $entityAddr = trim($branch ? ($branch->alamat ?? '') : ($company->alamat ?? ''));
            $entityTelp = trim($branch ? ($branch->telepon ?? '') : ($company->telepon ?? ''));
            $entityEmail = trim($branch ? ($branch->email ?? '') : ($company->email ?? ''));
        @endphp

        <!-- Kop Surat Modern Biru -->
        <div class="company-header d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center gap-3">
                @if(!empty($activeLogo))
                    <img src="{{ $activeLogo }}" alt="Logo" class="company-logo">
                @elseif(!empty($branch?->logo) && (file_exists(public_path('storage/' . $branch->logo)) || file_exists(public_path($branch->logo))))
                    <img src="{{ file_exists(public_path('storage/' . $branch->logo)) ? asset('storage/' . $branch->logo) : asset($branch->logo) }}" alt="Logo" class="company-logo">
                @elseif(!empty($company?->logo) && (file_exists(public_path('storage/' . $company->logo)) || file_exists(public_path($company->logo))))
                    <img src="{{ file_exists(public_path('storage/' . $company->logo)) ? asset('storage/' . $company->logo) : asset($company->logo) }}" alt="Logo" class="company-logo">
                @else
                    <div class="logo-placeholder">
                        {{ strtoupper(substr(($branch?->nama_cabang ?? $company->nama_perusahaan ?? 'ERP'), 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="company-title">
                        @if($branch)
                            {{ stripos($branch->nama_cabang, 'cabang') !== false ? strtoupper($branch->nama_cabang) : 'CABANG ' . strtoupper($branch->nama_cabang) }}
                        @else
                            {{ $company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA' }}
                        @endif
                    </div>
                    <div class="text-muted small mt-0.5" style="font-size: 10px;">
                        @if($entityAddr !== '' && $entityAddr !== '-')
                            {{ $entityAddr }}
                        @endif
                        @php
                            $contactBits = [];
                            if ($entityTelp !== '' && $entityTelp !== '-') $contactBits[] = 'Telp: ' . $entityTelp;
                            if ($entityEmail !== '' && $entityEmail !== '-') $contactBits[] = 'Email: ' . $entityEmail;
                        @endphp
                        @if(count($contactBits) > 0)
                            @if($entityAddr !== '' && $entityAddr !== '-') &bull; @endif
                            {{ implode(' &bull; ', $contactBits) }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-end">
                <div class="report-main-title">
                    <i class="bi bi-wallet2 text-primary"></i>
                    <span>LAPORAN REKAP UTANG SUPPLIER</span>
                </div>
                <div class="text-muted font-mono" style="font-size: 9.5px;">Monitoring Kewajiban &amp; Analisis Umur Utang (AP Aging)</div>
                <div class="badge bg-primary text-white rounded-pill font-mono px-3 py-1 mt-1 shadow-sm" style="font-size: 9.5px; background: #2563eb !important;">
                    <i class="bi bi-calendar-check me-1"></i>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>
        </div>

        <!-- Filter Meta Strip -->
        <div class="meta-strip d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="text-muted" style="font-size: 9.5px; font-weight: 700;">SUPPLIER / PT:</span>
                    <span class="fw-bold text-dark font-monospace" style="font-size: 10.5px;">{{ $selectedSupplierName }}</span>
                </div>
                <div class="border-start border-primary border-opacity-25 ps-3 d-flex align-items-center gap-1.5">
                    <span class="text-muted" style="font-size: 9.5px; font-weight: 700;">KRITERIA AGING:</span>
                    <span class="badge bg-white text-primary border border-primary-subtle rounded-pill font-mono px-2.5 py-0.5 shadow-sm" style="font-size: 9.5px;">
                        <i class="bi bi-funnel-fill me-1 text-primary"></i>{{ $selectedAgingLabel }}
                    </span>
                </div>
            </div>
            <div class="text-muted font-mono small">
                Petugas: <strong class="text-dark">{{ auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Administrator' }}</strong>
            </div>
        </div>

        <!-- Metric Summary Cards Row: FOKUS KE SISA UTANG & VENDOR -->
        <div class="row g-2.5 mb-3">
            <!-- 1. Total Sisa Utang Valas (Mata Uang Asing) -->
            <div class="col-3">
                <div class="metric-card" style="border-left: 4px solid #2563eb;">
                    <div class="metric-card-label" style="color: #2563eb;">
                        <i class="bi bi-currency-exchange"></i>
                        <span>Total Utang Valas (Asing)</span>
                    </div>
                    <div>
                        @if(($summary['total_foreign_sisa'] ?? 0) > 0.01)
                            <div class="metric-card-value" style="color: #1e40af;">
                                {{ $summary['foreign_symbol'] ?? '$' }} {{ $fmtRupiah($summary['total_foreign_sisa']) }}
                            </div>
                            <div class="text-muted" style="font-size: 9px;">Kewajiban valas berjalan</div>
                        @else
                            <div class="metric-card-value text-muted">-</div>
                            <div class="text-muted" style="font-size: 9px;">Tidak ada saldo valas</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Total Sisa Utang Rupiah -->
            <div class="col-3">
                <div class="metric-card" style="border-left: 4px solid #dc2626;">
                    <div class="metric-card-label text-danger">
                        <i class="bi bi-wallet2"></i>
                        <span>Total Sisa Utang (Rupiah)</span>
                    </div>
                    <div>
                        <div class="metric-card-value text-danger">
                            Rp {{ $fmtRupiah($summary['total_sisa'] ?? 0) }}
                        </div>
                        <div class="text-muted font-mono" style="font-size: 9px;">Akumulasi kewajiban IDR</div>
                    </div>
                </div>
            </div>

            <!-- 3. Vendor Berutang -->
            <div class="col-3">
                <div class="metric-card" style="border-left: 4px solid #0891b2;">
                    <div class="metric-card-label" style="color: #0891b2;">
                        <i class="bi bi-buildings"></i>
                        <span>Vendor Berutang</span>
                    </div>
                    <div>
                        <div class="metric-card-value text-dark">
                            {{ number_format($summary['unpaid_vendor_count'] ?? 0) }} <span class="text-muted fw-normal" style="font-size:0.75rem;">Vendor</span>
                        </div>
                        <div class="text-muted" style="font-size: 9px;">Dari {{ number_format($summary['supplier_count'] ?? 0) }} master supplier</div>
                    </div>
                </div>
            </div>

            <!-- 4. Faktur Belum Lunas -->
            <div class="col-3">
                <div class="metric-card" style="border-left: 4px solid #f59e0b;">
                    <div class="metric-card-label" style="color: #d97706;">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>Faktur Belum Lunas</span>
                    </div>
                    <div>
                        <div class="metric-card-value text-dark">
                            {{ number_format($summary['unpaid_faktur_count'] ?? 0) }} <span class="text-muted fw-normal" style="font-size:0.75rem;">Faktur</span>
                        </div>
                        <div class="text-muted" style="font-size: 9px;">Tagihan aktif berjalan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Report Table: TOTAL FAKTUR, UANG MUKA, TERBAYAR DIHAPUS. VALAS & RUPIAH DIPISAH -->
        <div class="table-report-wrapper">
            <table class="table-report">
                <thead>
                    <tr>
                        <th width="4%">NO</th>
                        <th width="9%">KODE</th>
                        <th width="37%" style="text-align: left;">NAMA PERUSAHAAN (PT) / SUPPLIER</th>
                        <th width="16%">STATUS UMUR UTANG</th>
                        <th width="17%" style="text-align: right;">SISA UTANG (VALAS / ASING)</th>
                        <th width="17%" style="text-align: right;">SISA UTANG (RUPIAH)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $idx => $s)
                        @php
                            $sisaIdr = (float) $s->sisa_utang;
                            $days = (int) $s->max_overdue_days;
                            $oldestDue = $s->oldest_due_date ? \Carbon\Carbon::parse($s->oldest_due_date)->format('d/m/Y') : '-';
                            $hasForeignSisa = (float) ($s->foreign_sisa_valas ?? 0) > 0.01;
                            $foreignSym = $s->foreign_currency_symbol ?: '$';

                            // Initials for avatar
                            $cleanName = preg_replace('/^(PT\.?|CV\.?|UD\.?)\s+/i', '', trim($s->nama_supplier));
                            $words = preg_split('/\s+/', $cleanName);
                            if (count($words) >= 2 && !empty($words[0]) && !empty($words[1])) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($cleanName, 0, 2)) ?: 'SP';
                            }
                        @endphp
                        <tr>
                            <td class="text-center font-mono text-muted">{{ $idx + 1 }}</td>
                            <td class="text-center font-mono">
                                <span class="badge bg-light text-secondary border font-mono px-2 py-0.5" style="font-size: 8.5px;">
                                    {{ $s->kode_supplier ?: ('SUP-' . $s->id) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="supplier-print-avatar">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark" style="font-size: 11px; letter-spacing: -0.01em;">{{ $s->nama_supplier }}</div>
                                        @if($s->nama_kontak || $s->telepon)
                                            <div class="text-muted" style="font-size: 9px;">
                                                @if($s->nama_kontak) <i class="bi bi-person me-0.5 text-primary"></i>{{ $s->nama_kontak }} @endif
                                                @if($s->telepon) &bull; <i class="bi bi-telephone me-0.5 text-success"></i>{{ $s->telepon }} @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($sisaIdr <= 0.01)
                                    <span class="badge-aging badge-lancar"><i class="bi bi-check-all"></i>Lunas</span>
                                @elseif($days <= 0)
                                    <span class="badge-aging badge-lancar"><i class="bi bi-clock-history"></i>Lancar (Tempo)</span>
                                    <div class="text-muted font-mono" style="font-size: 8px; margin-top: 2px;">Jth: {{ $oldestDue }}</div>
                                @elseif($days <= 30)
                                    <span class="badge-aging badge-warning"><i class="bi bi-exclamation-circle-fill"></i>Tempo {{ $days }} Hari</span>
                                    <div class="text-muted font-mono" style="font-size: 8px; margin-top: 2px;">1 – 30 Hari</div>
                                @elseif($days <= 60)
                                    <span class="badge-aging badge-orange"><i class="bi bi-exclamation-triangle-fill"></i>Tempo {{ $days }} Hari</span>
                                    <div class="text-muted font-mono" style="font-size: 8px; margin-top: 2px;">31 – 60 Hari</div>
                                @else
                                    <span class="badge-aging badge-danger"><i class="bi bi-fire"></i>Kritis (> {{ $days }} Hari)</span>
                                    <div class="text-danger font-mono" style="font-size: 8px; margin-top: 2px;">Macet > 60 Hari</div>
                                @endif
                            </td>

                            <!-- KOLOM 1: SISA UTANG MATA UANG ASING (VALAS) - GEDE & TEBAL -->
                            <td class="text-end font-mono">
                                @if($hasForeignSisa)
                                    <div class="fw-bold" style="font-size: 13.5px; color: #1e40af; letter-spacing: -0.01em;">
                                        {{ $foreignSym }} {{ $fmtRupiah($s->foreign_sisa_valas) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 8px;">Valas Aktif</div>
                                @else
                                    <span class="text-muted font-mono" style="font-size: 11px;">-</span>
                                @endif
                            </td>

                            <!-- KOLOM 2: SISA UTANG RUPIAH - TEBAL & MERAH -->
                            <td class="text-end font-mono">
                                <div class="fw-bold {{ $sisaIdr > 0.01 ? 'text-danger' : 'text-success' }}" style="font-size: 12px;">
                                    Rp {{ $fmtRupiah($sisaIdr) }}
                                </div>
                                @if($s->unpaid_count > 0)
                                    <div class="mt-0.5">
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c; font-size: 8px; padding: 2px 6px;">
                                            {{ $s->unpaid_count }} Belum Lunas
                                        </span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-1 text-primary opacity-50"></i>
                                Tidak ada data utang supplier yang cocok dengan filter kriteria yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end text-uppercase" style="letter-spacing: 0.06em; color: var(--blue-900);">
                            TOTAL KESELURUHAN:
                        </th>

                        <!-- Total Valas Asing -->
                        <th class="text-end font-mono">
                            @if(($summary['total_foreign_sisa'] ?? 0) > 0.01)
                                <div class="fw-bold" style="font-size: 13px; color: #1e40af;">
                                    {{ $summary['foreign_symbol'] ?? '$' }} {{ $fmtRupiah($summary['total_foreign_sisa']) }}
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </th>

                        <!-- Total Rupiah -->
                        <th class="text-end font-mono">
                            <div class="text-danger fw-bold" style="font-size: 12.5px;">
                                Rp {{ $fmtRupiah($summary['total_sisa'] ?? 0) }}
                            </div>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures & Verification Block -->
        <div class="row g-3 mt-2 pt-1">
            <div class="col-4">
                <div class="signature-box">
                    <div class="fw-bold text-primary small text-uppercase" style="font-size: 9.5px; letter-spacing: 0.04em;">
                        <i class="bi bi-pencil-square me-1"></i>Dibuat Oleh (Purchasing)
                    </div>
                    <div class="text-dark fw-bold border-top border-primary border-opacity-25 pt-1" style="font-size: 10px;">
                        ( {{ auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Staff Purchasing' }} )
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <div class="fw-bold text-primary small text-uppercase" style="font-size: 9.5px; letter-spacing: 0.04em;">
                        <i class="bi bi-clipboard-check me-1"></i>Diperiksa Oleh (Accounting)
                    </div>
                    <div class="text-dark fw-bold border-top border-primary border-opacity-25 pt-1" style="font-size: 10px;">
                        ( .................................................. )
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <div class="fw-bold text-primary small text-uppercase" style="font-size: 9.5px; letter-spacing: 0.04em;">
                        <i class="bi bi-shield-check me-1"></i>Disetujui Oleh (Finance / Direktur)
                    </div>
                    <div class="text-dark fw-bold border-top border-primary border-opacity-25 pt-1" style="font-size: 10px;">
                        ( .................................................. )
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
