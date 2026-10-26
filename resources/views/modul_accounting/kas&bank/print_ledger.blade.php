<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening Koran - {{ $selectedAccount ? $selectedAccount->nama_akun : 'Semua Rekening' }}</title>
    @php
        $appLogoSrc = $appLogo ?? asset('gambar/logo.png');
    @endphp
    <!-- Favicon Logo Aplikasi -->
    <link rel="shortcut icon" href="{{ $appLogoSrc }}" type="image/png">
    <link rel="icon" href="{{ $appLogoSrc }}" type="image/png">
    <!-- Modern Font: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-primary: #1d4ed8;
            --brand-navy: #1e3a8a;
            --brand-accent: #3b82f6;
            --border-color: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            background: #e2e8f0;
            padding: 24px 0 40px 0;
            font-size: 0.78rem;
            line-height: 1.4;
            min-height: 100vh;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }

        /* Screen Canvas: Format Lembar Kertas A4 Portrait (210mm x 297mm) */
        .print-canvas {
            width: 210mm;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 10mm 8mm 15mm 8mm;
            border-radius: 4px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.14), 0 2px 6px rgba(15, 23, 42, 0.08);
            position: relative;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        @media screen and (max-width: 215mm) {
            body {
                padding: 10px;
            }
            .print-canvas {
                width: 100%;
                max-width: 100%;
                padding: 12px 14px;
            }
        }

        /* Top Action Bar (Screen Only) */
        .no-print-bar {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 12px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        /* Divider Accent Ribbon di Bawah Periode Tanggal */
        .header-ribbon {
            height: 2.5px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #38bdf8 100%);
            border-radius: 99px;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        /* Corporate Header (Compact) */
        .header-corporate {
            padding-bottom: 2px;
            margin-bottom: 4px;
        }

        .brand-badge-compact {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Modern Table - Clean, borderless grid, fits 100% paper width */
        .table-modern {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: none;
            margin-bottom: 16px;
            box-sizing: border-box;
        }

        .table-modern thead tr {
            background: #1e3a8a !important;
            color: #ffffff !important;
        }

        .table-modern th {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            padding: 6px 5px;
            border: none !important;
            vertical-align: middle;
            color: #ffffff !important;
        }

        .table-modern td {
            padding: 5.5px 5px;
            font-size: 0.72rem;
            border: none !important;
            vertical-align: middle;
            color: #334155;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
        }

        .table-modern tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .table-modern tfoot tr {
            background-color: #f1f5f9;
            border: none !important;
        }

        .table-modern tfoot td {
            padding: 6px 5px;
            font-size: 0.74rem;
            font-weight: 700;
            vertical-align: middle;
            border: none !important;
        }

        .account-info-header {
            font-size: 0.76rem;
            margin-bottom: 8px;
            padding: 0 2px;
        }



        /* ========================================================
           PRINT STYLES FOR A4 PORTRAIT SPECIFICALLY
           ======================================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 4mm 5mm; /* Minimal margin agar tabel memenuhi lebar kertas A4 */
            }

            html, body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                font-size: 0.70rem !important;
            }

            .print-canvas {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                width: 100% !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .header-ribbon {
                height: 2px !important;
                margin-top: 2px !important;
                margin-bottom: 6px !important;
                background: #1d4ed8 !important;
            }

            .header-corporate {
                padding-bottom: 0 !important;
                margin-bottom: 2px !important;
                border-bottom: none !important;
            }

            .brand-badge-compact {
                width: 26px !important;
                height: 26px !important;
                font-size: 0.95rem !important;
                border-radius: 4px !important;
            }

            h5 {
                font-size: 0.95rem !important;
                margin-bottom: 1px !important;
            }

            h6 {
                font-size: 0.85rem !important;
                margin-bottom: 1px !important;
            }

            .table-modern {
                width: 100% !important;
                table-layout: fixed !important;
                margin-bottom: 6px !important;
                border: none !important;
                border-collapse: collapse !important;
                page-break-inside: auto;
            }

            .table-modern thead {
                display: table-header-group !important;
            }

            .table-modern tfoot {
                display: table-footer-group !important;
            }

            .table-modern thead tr {
                background: #1e3a8a !important;
                color: #ffffff !important;
                border: none !important;
            }

            .table-modern th {
                color: #ffffff !important;
                padding: 4px 6px !important;
                font-size: 0.62rem !important;
                border: none !important;
            }

            .table-modern td {
                padding: 3.5px 6px !important;
                font-size: 0.68rem !important;
                border: none !important;
                border-left: none !important;
                border-right: none !important;
                border-top: none !important;
                border-bottom: none !important;
            }

            .table-modern tfoot tr {
                border: none !important;
                border-top: none !important;
            }

            .table-modern tfoot td {
                padding: 4px 6px !important;
                font-size: 0.70rem !important;
                border: none !important;
                border-left: none !important;
                border-right: none !important;
                border-top: none !important;
                border-bottom: none !important;
            }

            .table-modern tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .account-info-header {
                font-size: 0.68rem !important;
                margin-bottom: 4px !important;
                padding: 0 !important;
            }


        }
    </style>
</head>
<body>

    <div class="print-canvas">

        <!-- Top Action Bar (Screen Only) -->
        <div class="d-flex justify-content-between align-items-center no-print-bar no-print">
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                <i class="bi bi-arrow-left"></i>
                <span>Tutup Jendela</span>
            </button>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="captureDocumentHD()" id="btn_screenshot_hd" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="font-size: 0.75rem;">
                    <i class="bi bi-camera-fill text-primary"></i>
                    <span>Screenshot</span>
                </button>
                <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none; font-size: 0.75rem;">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        @if(!empty($isTruncated))
        <div class="alert alert-warning py-2 px-3 small no-print d-flex align-items-center gap-2 mb-3 shadow-sm border-warning" role="alert" style="font-size: 0.73rem;">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-6 flex-shrink-0"></i>
            <div>
                <strong>Batas Tampilan Dokumen:</strong> Menampilkan <strong>{{ number_format($maxPrintRows, 0, ',', '.') }}</strong> transaksi pertama dari total <strong>{{ number_format($totalRowsCount, 0, ',', '.') }}</strong> transaksi pada periode ini. Untuk performa browser & cetak yang optimal tanpa lag, disarankan mempersempit rentang tanggal atau mengekspor ke Excel untuk data berukuran masif.
            </div>
        </div>
        @endif

        <!-- Header Laporan (Centered) -->
        <div class="text-center header-corporate">
            <h5 class="fw-bold mb-0.5" style="color: #1e3a8a; letter-spacing: -0.2px; font-size: 1.15rem; line-height: 1.25;">
                LAPORAN REKENING KORAN 
            </h5>
            <div class="fw-bold text-uppercase mb-1" style="font-size: 0.84rem; color: #334155; letter-spacing: 0.4px;">
                @if($activeBranch)
                    <span>{{ $activeBranch->nama_cabang }}</span>
                @else
                    <span>{{ $company ? $company->nama_perusahaan : 'PT Persada Aman Sentosa' }}</span>
                @endif
            </div>
            <div style="font-size: 0.76rem; color: #2563eb;">
                <strong style="color: #1d4ed8;">{{ date('d M Y', strtotime($startDate)) }}</strong> <span style="color: #3b82f6;">s/d</span> <strong style="color: #1d4ed8;">{{ date('d M Y', strtotime($endDate)) }}</strong>
            </div>
        </div>

        <!-- Garis Biru Pembatas di Bawah Periode Tanggal -->
        <div class="header-ribbon"></div>

        <!-- Informasi Rekening di Atas Tabel -->
        <div class="d-flex justify-content-between align-items-center account-info-header">
            <div>
              
                <strong class="text-dark ms-1">{{ $selectedAccount ? $selectedAccount->nama_akun : 'Semua Rekening Kas & Bank' }}</strong>
                @if($selectedAccount && $selectedAccount->atas_nama)
                    <span class="text-secondary ms-1" style="font-size: 0.72rem;">(a.n. {{ $selectedAccount->atas_nama }})</span>
                @endif
            </div>
            <div>
                <span class="text-muted">No. Rekening:</span>
                <strong class="text-dark font-mono ms-1" style="font-size: 0.80rem;">{{ $selectedAccount?->nomor_rekening ?: ($selectedAccount?->tipe === 'Kas Tunai' ? 'Kas Tunai' : '-') }}</strong>
            </div>
        </div>

        @php
            $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
        @endphp

        <!-- Tabel Rekening Koran Modern Blue (Pas 100% Kertas & Tanpa Grid Border Luar) -->
        <table class="table-modern">
            <thead>
                <tr class="text-center">
                    <th style="width: 24px;">NO</th>
                    <th style="width: 68px;" class="text-nowrap">TANGGAL</th>
                    <th style="width: 96px;" class="text-nowrap">NO. TRANSAKSI</th>
                    <th>KATEGORI &amp; KETERANGAN</th>
                    <th class="text-end text-nowrap" style="width: 120px;">MASUK</th>
                    <th class="text-end text-nowrap" style="width: 115px;">KELUAR</th>
                    <th class="text-end text-nowrap" style="width: 125px;">SALDO AKHIR</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $runningBalance = (float)($saldoAwalPeriode ?? ($summary['saldo_awal'] ?? 0));
                @endphp
                <!-- Baris Saldo Awal Periode Standar Akuntansi / Rekening Koran -->
                <tr style="background-color: #f8fafc;">
                    <td class="text-center font-mono text-muted">-</td>
                    <td class="font-mono text-secondary text-center text-nowrap">{{ date('d/m/Y', strtotime($startDate)) }}</td>
                    <td class="font-mono text-center text-nowrap">
                        <span class="text-secondary fw-semibold" style="font-size: 0.70rem;">SALDO AWAL</span>
                    </td>
                    <td>
                        <strong class="text-dark d-block" style="font-size: 0.74rem; line-height: 1.25;">Saldo Awal Periode</strong>
                        <div class="text-secondary" style="font-size: 0.68rem; line-height: 1.2; margin-top: 1px;">
                            {{ $summary['label_saldo_awal'] ?? ('Posisi per ' . date('d/m/Y', strtotime($startDate))) }}
                        </div>
                    </td>
                    <td class="text-end font-mono text-muted text-nowrap">-</td>
                    <td class="text-end font-mono text-muted text-nowrap">-</td>
                    <td class="text-end font-mono fw-bold text-nowrap" style="color: #1e3a8a;">
                        Rp {{ $fmtRupiah($runningBalance) }}
                    </td>
                </tr>
                @forelse($mutasis as $idx => $m)
                @php
                    $isCancelled = $m->status === 'Dibatalkan';
                    if (!$isCancelled) {
                        if ($m->tipe === 'Masuk') {
                            $runningBalance += (float)$m->jumlah;
                        } elseif ($m->tipe === 'Keluar' || $m->tipe === 'Transfer') {
                            $runningBalance -= (float)$m->jumlah;
                        }
                    }
                @endphp
                <tr class="{{ $isCancelled ? 'table-light' : '' }}" style="{{ $isCancelled ? 'opacity: 0.75;' : '' }}">
                    <td class="text-center font-mono text-muted">{{ $idx + 1 }}</td>
                    <td class="font-mono text-secondary text-center text-nowrap">{{ date('d/m/Y', strtotime($m->tanggal)) }}</td>
                    <td class="font-mono text-center text-nowrap">
                        <span class="{{ $isCancelled ? 'text-decoration-line-through text-muted' : 'text-dark fw-semibold' }}" style="font-size: 0.73rem;">{{ $m->nomor_transaksi }}</span>
                        @if($isCancelled)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 0.58rem;">Batal</span>
                        @endif
                    </td>
                    <td>
                        <strong class="{{ $isCancelled ? 'text-muted' : 'text-dark' }} d-block" style="font-size: 0.74rem; line-height: 1.25;">
                            {{ $m->kategori }}
                        </strong>
                        @if($m->nama_kontak || $m->keterangan)
                            <div class="text-secondary" style="font-size: 0.68rem; line-height: 1.2; margin-top: 1px;">
                                {{ $m->nama_kontak ?: $m->keterangan }}
                            </div>
                        @endif
                        @if($m->tender)
                            <div class="text-primary fw-medium" style="font-size: 0.65rem; margin-top: 1px;">
                                <i class="bi bi-briefcase-fill me-0.5"></i> Tender: {{ $m->tender->nomor_kontrak }} - {{ $m->tender->nama_tender }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end font-mono text-nowrap {{ $isCancelled ? 'text-muted text-decoration-line-through' : ($m->tipe === 'Masuk' ? 'text-success fw-bold' : 'text-muted') }}">
                        {{ $m->tipe === 'Masuk' ? $fmtRupiah($m->jumlah) : '-' }}
                    </td>
                    <td class="text-end font-mono text-nowrap {{ $isCancelled ? 'text-muted text-decoration-line-through' : (($m->tipe === 'Keluar' || $m->tipe === 'Transfer') ? 'text-danger fw-bold' : 'text-muted') }}">
                        {{ ($m->tipe === 'Keluar' || $m->tipe === 'Transfer') ? $fmtRupiah($m->jumlah) : '-' }}
                    </td>
                    <td class="text-end font-mono fw-bold text-nowrap {{ $isCancelled ? 'text-muted' : 'text-dark' }}" style="color: #1e3a8a;">
                        Rp {{ $fmtRupiah($runningBalance) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted fst-italic">
                        Tidak ada transaksi mutasi kas &amp; bank pada rentang periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.4px; color: #475569; padding-right: 8px;">
                        Total Mutasi Periode:
                    </td>
                    <td class="text-end font-mono text-success text-nowrap" style="font-size: 0.71rem; padding: 6px 4px;">
                        Rp {{ $fmtRupiah($totalMasuk) }}
                    </td>
                    <td class="text-end font-mono text-danger text-nowrap" style="font-size: 0.71rem; padding: 6px 4px;">
                        Rp {{ $fmtRupiah($totalKeluar) }}
                    </td>
                    <td class="text-end font-mono text-primary fw-bold text-nowrap" style="font-size: 0.78rem; background: #eff6ff; padding: 6px 4px;">
                        Rp {{ $fmtRupiah($saldoAkhirPeriode ?? ($summary['saldo_akhir'] ?? 0)) }}
                    </td>
                </tr>
            </tfoot>
        </table>

    </div>

    <!-- Bootstrap 5 JS Bundle & html2canvas for HD Screenshot -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function captureDocumentHD() {
            const btn = document.getElementById('btn_screenshot_hd');
            if (!btn) return;
            const originalHtml = btn.innerHTML;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span><span style="font-size:0.72rem;">Mengambil HD...</span>';

            const canvasTarget = document.querySelector('.print-canvas');
            const targetHeight = canvasTarget ? canvasTarget.scrollHeight : 0;
            // Adaptive scale: Scale 3 (300 DPI) untuk dokumen standar, scale 2/1.5 jika dokumen sangat panjang agar GPU canvas tidak OOM
            let scaleVal = 3;
            if (targetHeight > 10000) {
                scaleVal = 1.5;
            } else if (targetHeight > 5000) {
                scaleVal = 2;
            }

            // html2canvas dengan konfigurasi HD Ultra Crisp
            html2canvas(canvasTarget, {
                scale: scaleVal,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                ignoreElements: function(el) {
                    return el.classList && el.classList.contains('no-print');
                }
            }).then(function(canvas) {
                canvas.toBlob(function(blob) {
                    if (!blob) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        alert('Gagal mengambil screenshot dokumen.');
                        return;
                    }

                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const fileName = 'Rekening_Koran_{{ $selectedAccount ? preg_replace("/[^A-Za-z0-9_-]/", "_", $selectedAccount->nama_akun) : "Semua_Rekening" }}_{{ date("d_M_Y", strtotime($startDate)) }}_sd_{{ date("d_M_Y", strtotime($endDate)) }}_HD.png';
                    link.download = fileName;
                    link.href = url;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);

                    // Feedback sukses
                    btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i><span style="font-size:0.72rem;">Tersimpan (HD)</span>';
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }, 2500);
                }, 'image/png');
            }).catch(function(err) {
                console.error('Error saat capture HD:', err);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Terjadi kesalahan saat memproses screenshot dokumen.');
            });
        }
    </script>
</body>
</html>
