@php
    $activeBranchId  = session('active_branch_id');
    $activeCompanyId = session('active_company_id');

    // Context Resolution (Active Branch vs Active PT/Company)
    $activeBranch = null;
    if (!empty($activeBranchId)) {
        $activeBranch = \App\Models\Branch::with('company')->find($activeBranchId);
    }

    $activeCompany = null;
    if (!empty($activeCompanyId)) {
        $activeCompany = \App\Models\Company::find($activeCompanyId);
    } elseif ($activeBranch && $activeBranch->company) {
        $activeCompany = $activeBranch->company;
    }

    $isBranchActive = !empty($activeBranch);

    if ($isBranchActive) {
        $entityTitle   = $activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang');
        $rawLogo       = $activeBranch->logo ?? null;
    } else {
        $entityTitle   = $activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT Persada Aman Sentosa');
        $rawLogo       = $activeCompany?->logo ?? null;
    }

    // Resolving clean logo URL (Only if logo exists on disk or valid URL, otherwise null/empty)
    $companyLogoUrl = null;
    if (!empty($rawLogo)) {
        $cleanLogo = ltrim($rawLogo, '/');
        if (file_exists(public_path('storage/' . $cleanLogo))) {
            $companyLogoUrl = asset('storage/' . $cleanLogo);
        } elseif (file_exists(public_path($cleanLogo))) {
            $companyLogoUrl = asset($cleanLogo);
        } elseif (filter_var($rawLogo, FILTER_VALIDATE_URL)) {
            $companyLogoUrl = $rawLogo;
        }
    }

    // Application Logo for Browser Favicon
    $faviconUrl = $appLogo ?? asset('gambar/logo.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Opname – {{ $stockOpname->nomor_opname }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: #e8eaf0;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            -webkit-font-smoothing: antialiased;
        }

        /* ── TOOLBAR (Floating action buttons) ── */
        .toolbar {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 999;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(8px);
            padding: 8px 14px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .toolbar-label {
            color: #ffffff;
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 4px;
            user-select: none;
        }

        .toolbar-label input {
            cursor: pointer;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-print-primary {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }
        .btn-print-primary:hover {
            background: #1d4ed8;
        }

        .btn-screenshot {
            background: #059669;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
        }
        .btn-screenshot:hover {
            background: #047857;
        }

        .btn-close-window {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-close-window:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* ── A4 SHEET CONTAINER ── */
        .sheet {
            max-width: 860px;
            margin: 24px auto 40px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 32px 36px;
            position: relative;
        }

        /* ── KOP SURAT ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 14px;
            margin-bottom: 18px;
            gap: 16px;
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
        }

        .company-logo {
            max-height: 54px;
            max-width: 130px;
            height: auto;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .company-info {
            display: flex;
            flex-direction: column;
        }

        .company-name {
            font-size: 15px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .company-desc {
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.4;
        }

        .doc-meta {
            text-align: right;
            flex-shrink: 0;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .doc-no {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 4px;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 9px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .status-closed { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-menunggu-closing { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .status-sedang-berjalan { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .status-menunggu-persetujuan { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .status-draft { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* ── INFO SECTION ── */
        .info-card {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .info-card-col {
            flex: 1;
            min-width: 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .info-table td.lbl {
            color: #64748b;
            width: 40%;
            font-weight: 600;
        }

        .info-table td.val {
            color: #0f172a;
            font-weight: 700;
        }

        /* ── CATATAN ── */
        .note-box {
            background: #ffffff;
            border-left: 3.5px solid #1e40af;
            border-top: 1px solid #bfdbfe;
            border-right: 1px solid #bfdbfe;
            border-bottom: 1px solid #bfdbfe;
            border-radius: 0 6px 6px 0;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 11px;
            color: #334155;
        }

        /* ── SUMMARY BAR ── */
        .summary-bar {
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-item {
            text-align: center;
            flex: 1;
            border-right: 1px solid #bfdbfe;
            padding: 0 8px;
        }
        .summary-item:last-child {
            border-right: none;
        }

        .summary-item .title {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 2px;
        }

        .summary-item .num {
            font-size: 12px;
            font-weight: 800;
            color: #1e40af;
        }

        /* ── TABLE DATA ── */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 10.5px;
        }

        .table-data th {
            background-color: #1e40af;
            color: #ffffff;
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 9px;
            border: 1px solid #1d4ed8;
            text-align: left;
        }

        .table-data td {
            padding: 7px 9px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }

        .table-data tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .code-font {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: #1e40af;
            white-space: nowrap;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }

        .selisih-plus { color: #15803d; font-weight: 700; }
        .selisih-minus { color: #b91c1c; font-weight: 700; }
        .selisih-zero { color: #64748b; font-weight: 600; }

        /* ── SIGNATURE ── */
        .signature-wrapper {
            margin-top: 32px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            text-align: center;
        }

        .sig-col {
            flex: 1;
            min-width: 0;
            padding: 0 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sig-header {
            font-size: 10px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .sig-box, .sig-space {
            height: 65px;
            min-height: 65px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .digital-sig-img {
            max-height: 56px;
            max-width: 130px;
            height: auto;
            width: auto;
            object-fit: contain;
            display: inline-block;
        }

        .sig-divider {
            border-top: 1.5px solid #1e40af;
            margin-top: 2px;
            padding-top: 6px;
            width: 100%;
            max-width: 210px;
        }

        .sig-person {
            font-weight: 700;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.3;
        }

        .sig-position {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
            font-weight: 500;
            line-height: 1.3;
        }

        .sig-timestamp {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
            font-style: italic;
            line-height: 1.3;
        }

        /* ── PRINT MEDIA RULES ── */
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 10mm 14mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .toolbar, .no-print {
                display: none !important;
            }

            .sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }

            .table-data {
                width: 100% !important;
                page-break-inside: auto;
            }

            .table-data thead {
                display: table-header-group;
            }

            .table-data tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .table-data th {
                background-color: #1e40af !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-bar, .info-card, .note-box, .signature-wrapper {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Toolbar -->
    <div class="toolbar no-print">
        <label class="toolbar-label" title="Centang untuk menampilkan TTD digital">
            <input type="checkbox" id="checkTTD" checked onchange="toggleDigitalSignature(this.checked)"> TTD Digital
        </label>
        @if($canSeeFinancials)
        <label class="toolbar-label" title="Centang untuk menampilkan Nilai Selisih (Rupiah)">
            <input type="checkbox" id="checkNilaiSelisih" checked onchange="toggleNilaiSelisih(this.checked)"> Nilai Selisih (Rp)
        </label>
        @endif
        <button onclick="window.print()" class="btn-action btn-print-primary"><i class="bi bi-printer-fill"></i> Cetak / Simpan PDF</button>
        <button onclick="downloadAsImage()" class="btn-action btn-screenshot"><i class="bi bi-image"></i> Simpan PNG</button>
        <button onclick="window.close()" class="btn-action btn-close-window"><i class="bi bi-x-circle"></i> Tutup</button>
    </div>

    <!-- Main Printable Sheet -->
    <div class="sheet">
        <!-- Header / Kop Surat -->
        <div class="doc-header">
            <div class="brand-box">
                @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="Logo {{ $entityTitle }}" class="company-logo">
                @endif
                <div class="company-info">
                    <h1 class="company-name">{{ $entityTitle }}</h1>
                </div>
            </div>
            <div class="doc-meta">
                <h2 class="doc-title">Laporan Stock Opname</h2>
                <div class="doc-no">{{ $stockOpname->nomor_opname }}</div>
                @php
                    $statusClass = 'status-draft';
                    if ($stockOpname->status === 'Closed') $statusClass = 'status-closed';
                    elseif ($stockOpname->status === 'Menunggu Closing') $statusClass = 'status-menunggu-closing';
                    elseif ($stockOpname->status === 'Sedang Berjalan') $statusClass = 'status-sedang-berjalan';
                    elseif ($stockOpname->status === 'Menunggu Persetujuan') $statusClass = 'status-menunggu-persetujuan';
                    elseif ($stockOpname->status === 'Rejected') $statusClass = 'status-rejected';
                @endphp
                <span class="badge-status {{ $statusClass }}">{{ $stockOpname->status === 'Rejected' ? 'Ditolak' : $stockOpname->status }}</span>
            </div>
        </div>

        <!-- Info Dokumen -->
        <div class="info-card">
            <div class="info-card-col">
                <table class="info-table">
                    <tr>
                        <td class="lbl">Nomor Pengajuan</td>
                        <td class="val">{{ $stockOpname->nomor_opname }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tanggal Buat</td>
                        <td class="val">{{ \Carbon\Carbon::parse($stockOpname->tanggal_opname)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Rencana Periode</td>
                        <td class="val">{{ \Carbon\Carbon::parse($stockOpname->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($stockOpname->tanggal_selesai)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
            <div class="info-card-col">
                <table class="info-table">
                    <tr>
                        <td class="lbl">Gudang Lokasi</td>
                        <td class="val">{{ $stockOpname->gudang?->nama_gudang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Jenis Opname</td>
                        <td class="val">{{ $stockOpname->jenis_opname ?? 'Semua Barang' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Pelaksana (Pembuat)</td>
                        <td class="val">{{ $stockOpname->creator?->nama_lengkap ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if(!empty($stockOpname->keterangan))
        <div class="note-box">
            <strong style="color: #1e40af;">Catatan / Alasan Pengajuan:</strong> {{ $stockOpname->keterangan }}
        </div>
        @endif

        @if($stockOpname->status === 'Rejected' && !empty($stockOpname->alasan_reject))
        <div class="note-box" style="border-left-color: #dc2626; border-color: #fca5a5; background: #fff5f5; color: #991b1b;">
            <strong style="color: #b91c1c;"><i class="bi bi-x-circle-fill me-1"></i> Alasan Penolakan:</strong> {{ $stockOpname->alasan_reject }}
        </div>
        @endif

        @if($stockOpname->status === 'Menunggu Closing' || $stockOpname->status === 'Closed')
        <!-- Summary Bar -->
        <div class="summary-bar">
            <div class="summary-item">
                <span class="title">Total Item</span>
                <span class="num">{{ $countTotal }}</span>
            </div>
            <div class="summary-item">
                <span class="title">Dihitung</span>
                <span class="num" style="color: #15803d;">{{ $countDihitung }}</span>
            </div>
            <div class="summary-item">
                <span class="title">Belum Dihitung</span>
                <span class="num" style="color: #d97706;">{{ $countBelum }}</span>
            </div>
            @if($canSeeFinancials)
            <div class="summary-item col-nilai-selisih">
                <span class="title" style="color: #15803d;">Selisih Lebih (+)</span>
                <span class="num" style="color: #15803d;">Rp {{ number_format($selisihLebihVal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item col-nilai-selisih">
                <span class="title" style="color: #b91c1c;">Selisih Kurang (-)</span>
                <span class="num" style="color: #b91c1c;">Rp {{ number_format($selisihKurangVal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item col-nilai-selisih">
                <span class="title">Net Selisih</span>
                <span class="num" style="color: {{ $nilaiSelisihTotal >= 0 ? '#15803d' : '#b91c1c' }};">
                    {{ $nilaiSelisihTotal > 0 ? '+' : '' }}Rp {{ number_format($nilaiSelisihTotal, 0, ',', '.') }}
                </span>
            </div>
            @endif
        </div>
        @endif

        <!-- Table Rincian Barang -->
        <table class="table-data">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%">NO</th>
                    <th style="width: 14%">KODE BARANG</th>
                    <th style="width: 26%">NAMA BARANG</th>
                    <th class="text-center" style="width: 12%">STOK SISTEM</th>
                    <th class="text-center" style="width: 12%">STOK FISIK</th>
                    <th class="text-center" style="width: 10%">SELISIH QTY</th>
                    <th class="text-end col-nilai-selisih" style="width: 14%; {{ !$canSeeFinancials ? 'display: none !important;' : '' }}">NILAI SELISIH</th>
                    <th style="width: 14%">CATATAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $d)
                    @php
                        $diffClass = 'selisih-zero';
                        $diffSign = '';
                        if ($d['selisih'] > 0) {
                            $diffClass = 'selisih-plus';
                            $diffSign = '+';
                        } elseif ($d['selisih'] < 0) {
                            $diffClass = 'selisih-minus';
                        }

                        $fmtQty = function($val) {
                            if (is_null($val)) return '—';
                            $floatVal = floatval($val);
                            return ($floatVal == floor($floatVal)) 
                                ? number_format($floatVal, 0, ',', '.') 
                                : rtrim(rtrim(number_format($floatVal, 2, ',', '.'), '0'), ',');
                        };
                    @endphp
                    <tr>
                        <td class="text-center" style="color: #64748b; font-weight: 600;">{{ $index + 1 }}</td>
                        <td class="code-font">{{ $d['barang_kode'] }}</td>
                        <td class="fw-semibold" style="color: #0f172a;">{{ ucwords(strtolower($d['barang_nama'])) }}</td>
                        <td class="text-center">{{ $fmtQty($d['stok_sistem']) }} {{ $d['satuan'] }}</td>
                        <td class="text-center fw-bold" style="color: #0f172a;">
                            {{ $d['stok_fisik'] !== null ? $fmtQty($d['stok_fisik']) . ' ' . $d['satuan'] : '—' }}
                        </td>
                        <td class="text-center fw-bold {{ $diffClass }}">
                            {{ $d['selisih'] !== null ? $diffSign . $fmtQty($d['selisih']) : '—' }}
                        </td>
                        <td class="text-end fw-bold col-nilai-selisih {{ $diffClass }}" style="{{ !$canSeeFinancials ? 'display: none !important;' : '' }}">
                            {{ $d['selisih'] !== null ? 'Rp ' . number_format($d['nilai_selisih'], 0, ',', '.') : '—' }}
                        </td>
                        <td style="color: #64748b; font-style: italic;">{{ $d['catatan'] ? $d['catatan'] : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signature-wrapper">
            <div class="sig-col">
                <div class="sig-header">Dibuat Oleh</div>
                <div class="sig-box">
                    @if($stockOpname->creator?->activeSignature?->signature_base64)
                        <img src="{{ $stockOpname->creator->activeSignature->signature_base64 }}" 
                             class="digital-sig-img" 
                             alt="TTD Dibuat">
                    @endif
                </div>
                <div class="sig-divider">
                    <div class="sig-person">{{ $stockOpname->creator?->nama_lengkap ?? '-' }}</div>
                    <div class="sig-position">
                        {{ $stockOpname->creator?->position?->position_name ?? $stockOpname->creator?->karyawan?->position?->position_name ?? $stockOpname->creator?->roles->first()?->name ?? 'Staff Operasional / Gudang' }}
                    </div>
                    <div class="sig-timestamp">{{ $stockOpname->created_at ? \Carbon\Carbon::parse($stockOpname->created_at)->format('d/m/Y H:i') : \Carbon\Carbon::parse($stockOpname->tanggal_opname)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="sig-col">
                @if($stockOpname->status === 'Rejected')
                    <div class="sig-header" style="color: #dc2626;">Ditolak Oleh</div>
                    <div class="sig-box">
                        @if($stockOpname->approver?->activeSignature?->signature_base64)
                            <img src="{{ $stockOpname->approver->activeSignature->signature_base64 }}" 
                                 class="digital-sig-img" 
                                 alt="TTD Ditolak">
                        @endif
                    </div>
                    <div class="sig-divider" style="border-top-color: #dc2626;">
                        <div class="sig-person" style="color: #b91c1c;">{{ $stockOpname->approver?->nama_lengkap ?? '( ..................................... )' }}</div>
                        <div class="sig-position">
                            {{ $stockOpname->approver?->position?->position_name ?? $stockOpname->approver?->karyawan?->position?->position_name ?? $stockOpname->approver?->roles->first()?->name ?? 'Kepala Cabang / Manager' }}
                        </div>
                        <div class="sig-timestamp" style="color: #dc2626; font-weight: 600;">{{ $stockOpname->disetujui_pada ? \Carbon\Carbon::parse($stockOpname->disetujui_pada)->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                @else
                    <div class="sig-header">Disetujui Oleh</div>
                    <div class="sig-box">
                        @if($stockOpname->approver?->activeSignature?->signature_base64)
                            <img src="{{ $stockOpname->approver->activeSignature->signature_base64 }}" 
                                 class="digital-sig-img" 
                                 alt="TTD Disetujui">
                        @endif
                    </div>
                    <div class="sig-divider">
                        <div class="sig-person">{{ $stockOpname->approver?->nama_lengkap ?? '( ..................................... )' }}</div>
                        <div class="sig-position">
                            {{ $stockOpname->approver?->position?->position_name ?? $stockOpname->approver?->karyawan?->position?->position_name ?? $stockOpname->approver?->roles->first()?->name ?? 'Kepala Cabang / Manager' }}
                        </div>
                        <div class="sig-timestamp">{{ $stockOpname->disetujui_pada ? \Carbon\Carbon::parse($stockOpname->disetujui_pada)->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                @endif
            </div>
            <div class="sig-col">
                <div class="sig-header">Diclosing Oleh</div>
                <div class="sig-box">
                    @if($stockOpname->status !== 'Rejected' && $stockOpname->closer?->activeSignature?->signature_base64)
                        <img src="{{ $stockOpname->closer->activeSignature->signature_base64 }}" 
                             class="digital-sig-img" 
                             alt="TTD Closing">
                    @endif
                </div>
                <div class="sig-divider">
                    <div class="sig-person">{{ ($stockOpname->status !== 'Rejected' && $stockOpname->closer) ? $stockOpname->closer->nama_lengkap : '( ..................................... )' }}</div>
                    <div class="sig-position">
                        {{ ($stockOpname->status !== 'Rejected' && $stockOpname->closer) ? ($stockOpname->closer->position?->position_name ?? $stockOpname->closer->karyawan?->position?->position_name ?? $stockOpname->closer->roles->first()?->name ?? 'Finance & Accounting') : 'Finance & Accounting' }}
                    </div>
                    <div class="sig-timestamp">{{ ($stockOpname->status !== 'Rejected' && $stockOpname->diclosing_pada) ? \Carbon\Carbon::parse($stockOpname->diclosing_pada)->format('d/m/Y H:i') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDigitalSignature(show) {
            let imgs = document.querySelectorAll('.digital-sig-img');
            imgs.forEach(img => {
                img.style.display = show ? 'inline-block' : 'none';
            });
        }

        function toggleNilaiSelisih(show) {
            let els = document.querySelectorAll('.col-nilai-selisih');
            els.forEach(el => {
                el.style.display = show ? '' : 'none';
            });
        }

        function downloadAsImage() {
            const tb = document.querySelector('.toolbar');
            if (tb) tb.style.display = 'none';

            window.scrollTo(0, 0);

            const sheetEl = document.querySelector('.sheet') || document.body;

            html2canvas(sheetEl, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                if (tb) tb.style.display = 'flex';
                let link = document.createElement('a');
                link.download = 'Stock_Opname_{{ str_replace(["/", "\\"], "_", $stockOpname->nomor_opname) }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                if (tb) tb.style.display = 'flex';
                alert('Gagal mengunduh gambar: ' + err.message);
            });
        }
    </script>
</body>
</html>
