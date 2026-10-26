@php
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoPath = null;

    // Prioritaskan Logo Aplikasi Resmi dari AppSetting / default logo.png
    if ($appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))) {
        $appLogoPath = public_path('storage/' . $appLogoSetting);
    } elseif ($appLogoSetting && file_exists(storage_path('app/public/' . $appLogoSetting))) {
        $appLogoPath = storage_path('app/public/' . $appLogoSetting);
    } elseif (file_exists(public_path('gambar/logo.png'))) {
        $appLogoPath = public_path('gambar/logo.png');
    }

    $appLogoUrl = $appLogo ?? asset('gambar/logo.png');
    if ($appLogoPath && file_exists($appLogoPath)) {
        $ext = strtolower(pathinfo($appLogoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
        $appLogoUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($appLogoPath));
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Progress Produksi - {{ $spk->nomor_spk }}</title>

    {{-- APP FAVICON / LOGO --}}
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $appLogoUrl }}" type="image/x-icon">

    {{-- BOOTSTRAP 5, GOOGLE FONTS & ICONS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-accent: #2563eb;
            --secondary-accent: #0284c7;
            --border-light: #e2e8f0;
            --bg-subtle: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #1e293b;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* TOOLBAR ATAS (DARK GLASSMORPHISM) */
        .preview-toolbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            padding: 12px 20px;
        }

        .report-filter-segmented {
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            padding: 3px;
        }

        .btn-filter-pill {
            color: #94a3b8;
            font-weight: 600;
            font-size: 11.5px;
            padding: 5px 14px;
            border-radius: 50px;
            border: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-filter-pill:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-filter-pill.active {
            background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%);
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.45);
        }

        @media (max-width: 576px) {
            .preview-toolbar {
                padding: 8px 12px;
            }
            .filter-label-text {
                display: none !important;
            }
            .report-filter-wrapper {
                width: 100%;
            }
            .report-filter-segmented {
                width: 100%;
                display: flex;
                justify-content: space-between;
            }
            .btn-filter-pill {
                flex: 1;
                text-align: center;
                font-size: 10.5px;
                padding: 6px 6px;
            }
            .toolbar-actions-group {
                width: 100%;
                justify-content: space-between !important;
                margin-top: 4px;
            }
            .toolbar-actions-group .btn {
                font-size: 11px;
                padding: 4px 12px;
            }
        }

        .preview-container {
            display: flex;
            justify-content: center;
            padding: 24px 12px;
            overflow-x: hidden;
            width: 100%;
        }

        /* PAPER FORMAT A4 */
        .page-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 14mm 16mm;
            background: #ffffff;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
            border-radius: 8px;
            box-sizing: border-box;
            position: relative;
            transform-origin: top center;
            border-top: 5px solid #1d4ed8;
        }

        /* HEADER KOP */
        .kop-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 2px solid #1e3a8a;
        }

        .company-title {
            font-weight: 800;
            font-size: 20px;
            color: #1e3a8a;
            letter-spacing: -0.3px;
            margin: 0;
            line-height: 1.1;
        }

        .company-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .spk-badge-main {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 800;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 6px;
            font-family: monospace;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        /* HEADER TITLE BANNER */
        .doc-title-banner {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
        }

        .doc-title-text {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .doc-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .meta-item-box {
            background: rgba(255, 255, 255, 0.12);
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .meta-label {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #93c5fd;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* PROGRESS BAR MODERN */
        .progress-card-modern {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }

        .progress-bar-container {
            height: 10px;
            background: #e2e8f0;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #2563eb;
            border-radius: 50px;
            transition: width 0.4s ease;
        }

        /* TABLE STYLING EXECUTIVE */
        .section-header {
            font-size: 11px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 4px;
            margin-bottom: 8px;
            border-bottom: 2px solid #1d4ed8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-executive {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
            font-size: 10.5px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-executive th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.6px;
            padding: 8px 10px;
            border-bottom: 2px solid #3b82f6;
            vertical-align: middle;
        }

        .table-executive td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }

        .table-executive tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table-executive tbody tr:last-child td {
            border-bottom: none;
        }

        /* STATUS BADGES */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
        }

        .status-selesai {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-proses {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .status-menunggu {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        /* FOTO THUMBNAIL */
        .img-thumb-frame {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1.5px solid #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        /* FOOTER DOCUMENT */
        .doc-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            color: #94a3b8;
        }

        .mode-cetak-kosong .log-row-filled {
            display: none !important;
        }
        .mode-cetak-kosong .log-row-blank {
            display: table-row !important;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print, .preview-toolbar, #modalPreviewFotoPdf {
                display: none !important;
            }
            .preview-container {
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-a4 {
                width: 100% !important;
                box-shadow: none !important;
                padding: 10mm 12mm !important;
                transform: scale(1) !important;
                margin: 0 !important;
                border-top: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- TOOLBAR PREVIEW --}}
    <div class="preview-toolbar no-print">
        <div class="container-fluid d-flex align-items-center justify-content-between gap-2 flex-wrap p-0">
            <div class="d-flex align-items-center gap-2 report-filter-wrapper">
                <i class="bi bi-funnel-fill text-info fs-6"></i>
                <span class="text-white-50 small font-monospace fw-semibold filter-label-text">Filter Histori:</span>
                <div class="report-filter-segmented rounded-pill d-inline-flex align-items-center">
                    <button type="button" class="btn btn-filter-pill active" id="btnFilterAll" onclick="setReportFilterMode('all')">
                        <i class="bi bi-collection-fill me-1"></i> Semua Histori
                    </button>
                    <button type="button" class="btn btn-filter-pill" id="btnFilterToday" onclick="setReportFilterMode('today')">
                        <i class="bi bi-calendar-check-fill me-1"></i> Hari Ini Saja
                        <span class="badge bg-white bg-opacity-20 text-white font-monospace ms-1 d-none d-sm-inline-block" style="font-size: 9.5px;">{{ date('d/m') }}</span>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 toolbar-actions-group">
                <button type="button" id="btnScreenshotPdf" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="takeScreenshot()">
                    <i class="bi bi-camera me-1"></i> SS / Gambar
                </button>
                
                <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak PDF
                </button>
                
                <button type="button" class="btn btn-sm btn-outline-light rounded-circle ms-1" onclick="window.close()" title="Tutup Tab">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- PREVIEW CONTAINER --}}
    <div class="preview-container">
        <div class="page-a4 {{ (request()->boolean('blank') || request()->boolean('kosong')) ? 'mode-cetak-kosong' : '' }}" id="printableArea">
            
@php
    function formatTanggalIndo($date) {
        if (!$date) return '-';
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        $hariMap = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $hari = $hariMap[$date->format('l')] ?? '';
        return $hari . ', ' . $date->format('d/m/Y');
    }
@endphp

            {{-- KOP PERUSAHAAN EXECUTIVE --}}
            <div class="kop-header">
                <div>
                    <h1 class="company-title">{{ $entityName ?? ($company->nama_perusahaan ?? 'PABRIK ERP PRODUCTION') }}</h1>
                    <div class="company-subtitle">System Manufaktur & Control Realisasi Produksi</div>
                </div>
                <div class="text-end">
                    <div class="spk-badge-main">{{ $spk->nomor_spk }}</div>
                    <div class="text-muted fw-semibold mt-1" style="font-size: 9.5px;">Tgl Cetak: {{ formatTanggalIndo(now()) }} {{ date('H:i') }} WIB</div>
                </div>
            </div>

            {{-- HEADER BANNER TITLE & META SUMMARY GRID --}}
            <div class="doc-title-banner">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="doc-title-text"><i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Kontrol & Realisasi Progress Produksi</div>
                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2.5 py-1 font-monospace fw-bold">{{ $spk->status }}</span>
                </div>
                
                <div class="doc-meta-grid">
                    <div class="meta-item-box">
                        <div class="meta-label">Produk Jadi</div>
                        <div class="meta-value" title="{{ $spk->barangJadi->nama_barang ?? '-' }}">
                            [{{ $spk->barangJadi->kode_barang ?? '-' }}] {{ $spk->barangJadi->nama_barang ?? '-' }}
                        </div>
                    </div>

                    <div class="meta-item-box">
                        <div class="meta-label">Target Qty Rencana</div>
                        <div class="meta-value text-info">
                            {{ number_format($spk->jumlah_rencana, 0, ',', '.') }} {{ $spk->satuan->nama_satuan ?? 'Pcs' }}
                        </div>
                    </div>

                    <div class="meta-item-box" style="text-align: center;">
                        <div class="meta-label">Foto Produk</div>
                        <div class="meta-value py-1">
                            @php
                                $fotoPath = $spk->barangJadi?->primaryFoto?->foto_path ?? $spk->barangJadi?->fotos?->first()?->foto_path;
                            @endphp
                            @if($fotoPath)
                                <a href="javascript:void(0)" onclick="openFotoPreviewModal('{{ asset('storage/' . $fotoPath) }}', 'Foto Produk Jadi - {{ e($spk->barangJadi->nama_barang ?? '') }}')">
                                    <img src="{{ asset('storage/' . $fotoPath) }}" crossorigin="anonymous" style="max-height: 42px; max-width: 85px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.4); cursor: pointer;" title="Klik untuk perbesar foto produk">
                                </a>
                            @else
                                <span class="text-white-50 small" style="font-size: 10px;">- No Photo -</span>
                            @endif
                        </div>
                    </div>

                    <div class="meta-item-box">
                        <div class="meta-label">Tanggal Terbit SPK</div>
                        <div class="meta-value">
                            {{ formatTanggalIndo($spk->tanggal_spk) }}
                        </div>
                    </div>
                </div>

                @php
                    $progressTender = $spk->tender ?? ($spk->bom?->tender);
                @endphp
                @if($progressTender)
                <div class="mt-2 pt-2 border-top border-white border-opacity-25 d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 8.5px;">📁 TENDER / PROYEK</span>
                    <span class="text-white fw-bold" style="font-size: 10.5px;">
                        {{ $progressTender->nama_tender }}
                    </span>
                </div>
                @endif
            </div>

            {{-- OVERALL PROGRESS BAR MODERN --}}
            @php
                $totalTahapCount = $spk->tahapans->count();
                $completedTahapCount = $spk->tahapans->where('status', 'Selesai')->count();
                $totTarget = (float)$spk->tahapans->sum('target_qty');
                $totSelesai = (float)$spk->tahapans->sum('qty_selesai');
                $calcPct = $totTarget > 0 ? round(($totSelesai / $totTarget) * 100, 1) : 0;
            @endphp
            <div class="progress-card-modern">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="fw-bold text-dark" style="font-size: 10.5px;">
                        <i class="bi bi-speedometer2 text-primary me-1"></i> AKUMULASI TOTAL PROGRES PENGERJAAN SPK
                    </div>
                    <div class="fw-extrabold text-primary" style="font-size: 12px;">
                        {{ $calcPct }}% SELESAI <span class="text-muted fw-normal" style="font-size: 10px;">({{ $completedTahapCount }} dari {{ $totalTahapCount }} Tahapan Selesai • Target SPK: {{ number_format($spk->jumlah_rencana, 0, ',', '.') }} {{ $spk->satuan->nama_satuan ?? 'Pcs' }})</span>
                    </div>
                </div>
                <div class="progress-bar-container">
                    @if($calcPct > 0)
                        <div class="progress-bar-fill" style="width: {{ min(100, $calcPct) }}%;"></div>
                    @endif
                </div>
            </div>

            {{-- TABEL 1: DAFTAR TAHAPAN & PROGRES KUMULATIF --}}
            <div class="section-header">
                <i class="bi bi-diagram-3-fill text-primary"></i> I. Status Realisasi Progress Tahapan Kerja
            </div>

            <table class="table-executive text-center">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th style="text-align: left;">Nama Tahapan Produksi</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 85px;">Target Qty</th>
                        <th style="width: 95px;">Hasil Selesai</th>
                        <th style="width: 65px;">Reject</th>
                        <th style="width: 75px;">Progres (%)</th>
                        <th style="width: 130px; text-align: left;">PIC Produksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spk->tahapans as $t)
                        @php
                            $sat = $spk->satuan->nama_satuan ?? 'Pcs';
                            $tgt = (float)$t->target_qty;
                            $fin = (float)$t->qty_selesai;
                            $rej = (float)$t->qty_reject;
                            $pct = $tgt > 0 ? round(($fin / $tgt) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="fw-bold text-muted">{{ $t->urutan }}</td>
                            <td style="text-align: left;" class="fw-bold text-dark">{{ $t->nama_tahapan }}</td>
                            <td>
                                @if($t->status === 'Selesai')
                                    <span class="status-pill status-selesai"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                                @elseif($t->status === 'Sedang Dikerjakan')
                                    <span class="status-pill status-proses"><i class="bi bi-gear-wide-connected"></i> Proses</span>
                                @else
                                    <span class="status-pill status-menunggu"><i class="bi bi-clock"></i> Menunggu</span>
                                @endif
                            </td>
                            <td>{{ number_format($tgt, 0, ',', '.') }}</td>
                            <td class="fw-bold text-primary">{{ number_format($fin, 0, ',', '.') }}</td>
                            <td class="{{ $rej > 0 ? 'fw-bold text-danger' : 'text-muted' }}">{{ number_format($rej, 0, ',', '.') }}</td>
                            <td class="fw-bold text-dark">{{ $pct }}%</td>
                            <td style="text-align: left;" class="fw-semibold">{{ $t->pic_operator ?: ($spk->penanggungJawab->nama_lengkap ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted italic py-3">Belum ada data tahapan kerja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- TABEL 2: CATATAN HISTORI LAPORAN HARIAN (LOGS) --}}
            <div class="section-header mt-3 d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-clock-history text-primary"></i> II. Rincian Histori Catatan Laporan Pengerjaan Harian
                    <span id="logSectionFilterBadge" class="ms-2"></span>
                </div>
            </div>

            <table class="table-executive">
                <thead>
                    <tr>
                        <th style="width: 100px; text-align: center;">Tanggal</th>
                        <th style="min-width: 170px;">Tahapan / Divisi</th>
                        <th style="width: 100px; text-align: center;">Hasil Output</th>
                        <th style="width: 70px; text-align: center;">Reject</th>
                        <th style="width: 80px; text-align: center;">Foto Bukti</th>
                        <th style="min-width: 200px;">Catatan Pengerjaan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allLogs = collect();
                        foreach($spk->tahapans as $t) {
                            foreach($t->logHarians as $log) {
                                $log->tahapan_nama = $t->nama_tahapan;
                                $allLogs->push($log);
                            }
                        }
                        $sortedLogs = $allLogs->sortByDesc(function($l) {
                            return (int)$l->id;
                        })->values();
                        $sat = $spk->satuan->nama_satuan ?? 'Pcs';
                        $todayYmd = date('Y-m-d');
                    @endphp

                    @forelse($sortedLogs as $log)
                        @php
                            $logDateYmd = $log->tanggal ? \Carbon\Carbon::parse($log->tanggal)->format('Y-m-d') : '';
                        @endphp
                        <tr class="log-row-filled" data-log-date="{{ $logDateYmd }}">
                            <td class="text-center fw-bold text-dark" style="white-space: nowrap;">{{ formatTanggalIndo($log->tanggal) }}</td>
                            <td class="fw-bold text-dark">{{ $log->tahapan_nama }}</td>
                            <td class="text-center fw-bold text-primary">+{{ number_format($log->qty_hasil_hari_ini, 0, ',', '.') }}</td>
                            <td class="text-center {{ (float)$log->qty_reject_hari_ini > 0 ? 'text-danger fw-bold' : 'text-muted' }}">+{{ number_format($log->qty_reject_hari_ini, 0, ',', '.') }}</td>
                            <td class="text-center py-1">
                                @if($log->foto_bukti_url)
                                    <a href="javascript:void(0)" onclick="openFotoPreviewModal('{{ $log->foto_bukti_url }}', 'Foto Bukti {{ formatTanggalIndo($log->tanggal) }} - {{ e($log->tahapan_nama) }}')">
                                        <img src="{{ $log->foto_bukti_url }}" crossorigin="anonymous" class="img-thumb-frame" style="cursor: pointer;" title="Klik untuk perbesar foto bukti">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $log->catatan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr class="log-row-filled">
                            <td colspan="6" class="text-center text-muted py-3">Belum ada riwayat laporan pengerjaan harian yang dicatat.</td>
                        </tr>
                    @endforelse

                    <tr id="rowLogTodayEmpty" class="d-none">
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle me-1 text-info"></i>Tidak ada laporan pengerjaan harian yang dicatat untuk hari ini ({{ date('d/m/Y') }}).
                        </td>
                    </tr>

                    {{-- FORM KOSONG UNTUK ISIAN MANUAL TULIS TANGAN --}}
                    @for($i = 1; $i <= 6; $i++)
                        <tr class="log-row-blank d-none">
                            <td class="text-center text-muted small py-2" style="height: 32px;">____ / ____ / 2026</td>
                            <td class="text-muted small">_________________________</td>
                            <td class="text-center text-muted small">+ ______</td>
                            <td class="text-center text-muted small">+ ______</td>
                            <td class="text-center text-muted small">[ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ]</td>
                            <td class="text-muted small">_____________________________________</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- FOOTER DOCUMENT WATERMARK --}}
            <div class="doc-footer">
                <div><span class="text-muted"><i class="bi bi-shield-check text-primary me-1"></i> Dokumen Kontrol Produksi</span></div>
                <div>Halaman 1 dari 1</div>
            </div>

        </div>
    </div>

    {{-- MODAL PREVIEW FOTO HIGH RESOLUTION --}}
    <div class="modal fade" id="modalPreviewFotoPdf" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
                <div class="modal-header border-bottom border-secondary py-2 px-3">
                    <h6 class="modal-title fw-bold text-info" id="previewFotoTitle">
                        <i class="bi bi-image me-1"></i> Preview Foto Bukti
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3" style="background-color: #0f172a;">
                    <img id="imgPreviewPdfTarget" src="" class="img-fluid rounded border border-secondary shadow" style="max-height: 75vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-top border-secondary py-2 px-3 justify-content-between">
                    <small class="text-white-50" id="previewFotoCaption"></small>
                    <div>
                        <a id="btnDownloadFotoPdf" href="" download target="_blank" class="btn btn-sm btn-info rounded-pill px-3 me-1">
                            <i class="bi bi-download me-1"></i> Unduh Foto Original
                        </a>
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setReportFilterMode(mode) {
            const btnAll = document.getElementById('btnFilterAll');
            const btnToday = document.getElementById('btnFilterToday');
            if (mode === 'today') {
                if (btnAll) btnAll.classList.remove('active');
                if (btnToday) btnToday.classList.add('active');
            } else {
                if (btnToday) btnToday.classList.remove('active');
                if (btnAll) btnAll.classList.add('active');
            }
            filterReportLogRange(mode);
        }

        function filterReportLogRange(mode) {
            const todayStr = "{{ date('Y-m-d') }}";
            const rows = document.querySelectorAll('.log-row-filled');
            let matchCount = 0;

            rows.forEach(row => {
                const logDate = row.getAttribute('data-log-date');
                if (mode === 'today') {
                    if (logDate === todayStr) {
                        row.style.display = '';
                        matchCount++;
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = '';
                    matchCount++;
                }
            });

            const emptyTodayRow = document.getElementById('rowLogTodayEmpty');
            if (emptyTodayRow) {
                if (mode === 'today' && matchCount === 0) {
                    emptyTodayRow.classList.remove('d-none');
                } else {
                    emptyTodayRow.classList.add('d-none');
                }
            }

            const badge = document.getElementById('logSectionFilterBadge');
            if (badge) {
                if (mode === 'today') {
                    badge.innerHTML = `<span class="badge bg-primary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 10px;"><i class="bi bi-calendar-check me-1"></i>Laporan Hari Ini (${todayStr})</span>`;
                } else {
                    badge.innerHTML = `<span class="badge bg-secondary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 10px;"><i class="bi bi-collection me-1"></i>Semua Histori</span>`;
                }
            }
        }

        function toggleBlankMode() {
            const page = document.getElementById('printableArea');
            if (!page) return;
            page.classList.toggle('mode-cetak-kosong');
            const btn = document.getElementById('btnToggleBlank');
            if (page.classList.contains('mode-cetak-kosong')) {
                btn.classList.remove('btn-outline-warning');
                btn.classList.add('btn-warning');
                btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Mode Kosong Aktif';
            } else {
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-outline-warning');
                btn.innerHTML = '<i class="bi bi-file-earmark-spreadsheet me-1"></i> Form Kosong (Manual)';
            }
        }

        function openFotoPreviewModal(url, title = 'Foto Bukti Produksi') {
            document.getElementById('imgPreviewPdfTarget').src = url;
            document.getElementById('btnDownloadFotoPdf').href = url;
            document.getElementById('previewFotoTitle').innerHTML = `<i class="bi bi-image me-1"></i> ${title}`;
            document.getElementById('previewFotoCaption').innerText = title;
            const modal = new bootstrap.Modal(document.getElementById('modalPreviewFotoPdf'));
            modal.show();
        }

        function takeScreenshot() {
            const btn = document.getElementById('btnScreenshotPdf');
            const originalHtml = btn ? btn.innerHTML : '<i class="bi bi-camera me-1"></i> SS / Gambar';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Proses...';
            }

            const page = document.getElementById('printableArea');
            if (!page) {
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                return;
            }

            // Reset transform for 100% crisp full-resolution capture
            page.style.transform = 'none';
            page.style.marginBottom = '0px';

            const fileNo = "{{ str_replace(['/', '\\'], '_', $spk->nomor_spk) }}";
            const filename = `Laporan_Progress_Produksi_${fileNo}.png`;

            const finish = () => {
                scaleA4ToScreen();
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            };

            html2canvas(page, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#ffffff',
                logging: false,
                onclone: (clonedDoc) => {
                    const clonedPage = clonedDoc.getElementById('printableArea');
                    if (clonedPage) {
                        clonedPage.style.transform = 'none';
                        clonedPage.style.marginBottom = '0px';

                        // 1. Remove background-image/gradient from any element with 0 width or 0 height to prevent CanvasRenderingContext2D.createPattern crash
                        const allEls = clonedPage.querySelectorAll('*');
                        allEls.forEach(el => {
                            const w = el.offsetWidth || el.clientWidth || 0;
                            const h = el.offsetHeight || el.clientHeight || 0;
                            if (w <= 0 || h <= 0) {
                                el.style.backgroundImage = 'none';
                                el.style.background = 'transparent';
                            }
                        });

                        // 2. Hide broken or 0-dimension images
                        const imgs = clonedPage.querySelectorAll('img');
                        imgs.forEach(img => {
                            if (img.naturalWidth === 0 || img.naturalHeight === 0) {
                                img.style.display = 'none';
                            } else {
                                img.crossOrigin = 'anonymous';
                            }
                        });
                    }
                }
            }).then(canvas => {
                if (canvas.toBlob) {
                    canvas.toBlob(blob => {
                        if (blob) {
                            const url = URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.download = filename;
                            link.href = url;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            setTimeout(() => URL.revokeObjectURL(url), 2000);
                        } else {
                            const link = document.createElement('a');
                            link.download = filename;
                            link.href = canvas.toDataURL('image/png');
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                        finish();
                    }, 'image/png');
                } else {
                    const link = document.createElement('a');
                    link.download = filename;
                    link.href = canvas.toDataURL('image/png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    finish();
                }
            }).catch(err => {
                console.error('Screenshot error:', err);
                finish();
                alert('Gagal mengambil screenshot dokumen: ' + (err.message || 'Silakan coba lagi.'));
            });
        }

        function scaleA4ToScreen() {
            const page = document.getElementById('printableArea');
            if (!page) return;
            const container = document.querySelector('.preview-container');
            const availableWidth = container.clientWidth - 20;
            const targetWidth = 794; // 210mm in px at 96 DPI
            if (availableWidth < targetWidth) {
                const scale = availableWidth / targetWidth;
                page.style.transform = `scale(${scale})`;
                page.style.marginBottom = `-${(page.offsetHeight * (1 - scale))}px`;
            } else {
                page.style.transform = 'scale(1)';
                page.style.marginBottom = '0px';
            }
        }

        window.addEventListener('resize', scaleA4ToScreen);
        document.addEventListener('DOMContentLoaded', scaleA4ToScreen);
    </script>
</body>
</html>
