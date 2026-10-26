<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekening Kas & Bank - {{ $activeBranch ? $activeBranch->nama_cabang : ($company ? $company->nama_perusahaan : 'ERP System') }}</title>
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
            --brand-dark: #0f172a;
            --brand-navy: #1e3a8a;
            --brand-light: #eff6ff;
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
            background: #f1f5f9;
            padding: 24px;
            font-size: 0.85rem;
            line-height: 1.45;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }

        .print-canvas {
            max-width: 1140px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 42px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            position: relative;
        }

        /* Top Action Bar (Screen Only) */
        .no-print-bar {
            background: #ffffff;
            border: 1px solid rgba(203, 213, 225, 0.8);
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        /* Header Accent Ribbon */
        .header-ribbon {
            height: 5px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #38bdf8 100%);
            border-radius: 99px;
            margin-bottom: 24px;
        }

        /* Logo Brand Badge */
        .brand-badge {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 1.45rem;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
            overflow: hidden;
            padding: 4px;
        }

        .brand-badge.has-logo {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        .doc-tag {
            font-size: 0.68rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-weight: 800;
            color: #1d4ed8;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 3px 10px;
            border-radius: 6px;
            display: inline-block;
        }

        /* KPI Bento Cards */
        .kpi-card {
            border-radius: 14px;
            padding: 18px 22px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .kpi-card-silver {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .kpi-card-info {
            background: linear-gradient(145deg, #f0f7ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.05);
        }

        .kpi-card-blue {
            background: linear-gradient(135deg, #0f2744 0%, #1e3a8a 50%, #2563eb 100%);
            color: #ffffff;
            border: 1px solid #1d4ed8;
            box-shadow: 0 8px 22px rgba(29, 78, 216, 0.25);
        }

        .kpi-card-blue .kpi-label {
            color: rgba(255, 255, 255, 0.85);
        }

        .kpi-card-blue .kpi-value {
            color: #ffffff;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .kpi-watermark {
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 5rem;
            opacity: 0.08;
            pointer-events: none;
        }

        /* Modern Blue Table */
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #94a3b8;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-modern thead tr {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1d4ed8 100%) !important;
            color: #ffffff !important;
        }

        .table-modern th {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 12px 10px;
            border: none;
            vertical-align: middle;
            color: #ffffff !important;
        }

        .table-modern td {
            padding: 10px 10px;
            font-size: 0.8rem;
            border-bottom: 1px solid #e2e8f0;
            border-top: none;
            vertical-align: middle;
            color: #334155;
        }

        .table-modern tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }

        .table-modern tfoot tr {
            background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%) !important;
            color: #1e3a8a;
            font-weight: 700;
        }

        .table-modern tfoot td {
            padding: 13px 10px;
            font-size: 0.84rem;
            border-top: 2px solid #2563eb;
            border-bottom: none;
        }

        .badge-entity-pt {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-entity-cabang {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-status-active {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .code-pill {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 700;
        }


        /* Print Media Styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0; /* Menghilangkan URL dan header/footer otomatis dari browser */
            }
            body {
                background: #ffffff !important;
                padding: 6mm 8mm !important;
                font-size: 0.78rem !important;
            }
            .print-canvas {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            .header-ribbon {
                height: 3px !important;
                margin-bottom: 6px !important;
                background: #1d4ed8 !important;
            }
            .header-corporate {
                padding-bottom: 6px !important;
                margin-bottom: 8px !important;
            }
            .brand-badge {
                width: 36px !important;
                height: 36px !important;
                font-size: 1.15rem !important;
                border-radius: 8px !important;
            }
            .doc-tag {
                font-size: 0.62rem !important;
                padding: 2px 6px !important;
            }
            h3 {
                font-size: 1.15rem !important;
                margin-bottom: 2px !important;
            }
            h5 {
                font-size: 0.95rem !important;
                margin-bottom: 2px !important;
            }
            .kpi-row {
                margin-bottom: 10px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .kpi-card {
                padding: 8px 12px !important;
                border-radius: 8px !important;
            }
            .kpi-card-blue {
                background: #1e3a8a !important;
                color: #ffffff !important;
            }
            .kpi-card-blue .kpi-value {
                color: #ffffff !important;
            }
            .kpi-card .fs-3,
            .kpi-card .fs-4 {
                font-size: 1.1rem !important;
                margin-bottom: 2px !important;
            }
            .kpi-card .kpi-watermark {
                font-size: 2.8rem !important;
            }
            .table-modern {
                margin-bottom: 10px !important;
                border-radius: 8px !important;
            }
            .table-modern thead {
                display: table-header-group;
            }
            .table-modern tfoot {
                display: table-footer-group;
            }
            .table-modern thead tr {
                background: #1e3a8a !important;
                color: #ffffff !important;
            }
            .table-modern th {
                color: #ffffff !important;
                padding: 5px 6px !important;
                font-size: 0.66rem !important;
            }
            .table-modern td {
                padding: 4px 6px !important;
                font-size: 0.72rem !important;
            }
            .table-modern tfoot td {
                padding: 6px 6px !important;
                font-size: 0.76rem !important;
            }
            .table-modern tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-canvas">

        <!-- Top Action Bar (Screen Only) -->
        <div class="d-flex justify-content-between align-items-center no-print-bar no-print">
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i>
                <span>Tutup Jendela</span>
            </button>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="captureDocumentHD()" id="btn_screenshot_hd" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="font-size: 0.78rem;">
                    <i class="bi bi-camera-fill text-primary"></i>
                    <span>Screenshot HD</span>
                </button>
                <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                    <i class="bi bi-printer-fill fs-6"></i>
                    <span>Cetak Dokumen / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Top Accent Ribbon -->
        <div class="header-ribbon"></div>

        <!-- Corporate Header Section -->
        <div class="d-flex justify-content-between align-items-start pb-3 mb-4 border-bottom border-light-subtle header-corporate">
            <div class="d-flex align-items-start gap-3">
                <div class="brand-badge">
                    <i class="bi bi-bank2"></i>
                </div>
                <div>
                    <div class="doc-tag mb-1.5">
                        <i class="bi bi-shield-check me-1"></i>TREASURY &amp; CASH EQUIVALENTS REPORT
                    </div>
                    <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">LAPORAN REKENING KAS &amp; BANK</h3>
                    <div class="text-secondary small">Daftar Buku Kas Fisik, Rekening Bank Perusahaan, &amp; Posisi Saldo Likuiditas Berjalan</div>
                </div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-1" style="color: #1e3a8a; letter-spacing: -0.3px;">
                    {{ $company ? $company->nama_perusahaan : config('app.name', 'ERP SYSTEM') }}
                </h5>
                <div class="mb-1">
                    @if($activeBranch)
                        <span class="badge-entity-cabang"><i class="bi bi-shop text-primary"></i> Cabang: {{ $activeBranch->nama_cabang }}</span>
                    @else
                        <span class="badge-entity-pt"><i class="bi bi-building text-primary"></i> PT Pusat (Entitas Induk)</span>
                    @endif
                </div>
                <div class="text-muted font-mono" style="font-size: 0.72rem;">
                    <i class="bi bi-clock me-1 text-primary"></i>Waktu Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>
        </div>

        <!-- Modern Bento KPI Cards -->
        <div class="row g-3 mb-4 kpi-row">
            <!-- Card 1: Total Rekening Terdaftar -->
            <div class="col-3">
                <div class="kpi-card kpi-card-info h-100">
                    <i class="bi bi-folder2-open kpi-watermark text-primary"></i>
                    <div class="text-primary small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                        <i class="bi bi-collection me-1"></i>TOTAL REKENING
                    </div>
                    <div class="font-mono fw-bold fs-4 text-dark mb-1">
                        {{ $accounts->count() }} <span class="fs-6 fw-normal text-muted">Akun Aktif</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.7rem;">
                        Kas tunai &amp; rekening bank terdaftar
                    </div>
                </div>
            </div>

            <!-- Card 2: Saldo Awal -->
            <div class="col-4">
                <div class="kpi-card kpi-card-silver h-100">
                    <i class="bi bi-safe kpi-watermark"></i>
                    <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                        <i class="bi bi-clock-history me-1 text-primary"></i>TOTAL SALDO AWAL REKENING
                    </div>
                    <div class="font-mono fw-bold fs-4 text-dark mb-1">
                        Rp {{ number_format($totalSaldoAwal, 2, ',', '.') }}
                    </div>
                    <div class="text-muted" style="font-size: 0.7rem;">
                        Akumulasi modal saldo awal pada buku kas
                    </div>
                </div>
            </div>

            <!-- Card 3: Saldo Saat Ini (Royal Blue) -->
            <div class="col-5">
                <div class="kpi-card kpi-card-blue h-100">
                    <i class="bi bi-cash-stack kpi-watermark"></i>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="kpi-label small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                            <i class="bi bi-wallet2 me-1 text-white"></i>TOTAL SALDO SAAT INI (SALDO AKHIR)
                        </div>
                        <span class="badge bg-white bg-opacity-25 text-white font-mono" style="font-size: 0.65rem;">Liquid Treasury</span>
                    </div>
                    <div class="kpi-value font-mono fw-bold fs-3 mb-1">
                        Rp {{ number_format($totalSaldoAkhir, 2, ',', '.') }}
                    </div>
                    <div class="text-white text-opacity-80" style="font-size: 0.7rem;">
                        Total likuiditas dana kas &amp; bank aktif yang dapat digunakan
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Blue Table -->
        <table class="table-modern">
            <thead>
                <tr class="text-center">
                    <th style="width: 36px;">No</th>
                    <th style="width: 80px;">Kode</th>
                    <th>Nama Akun / Buku Kas</th>
                    <th style="width: 100px;">Entitas</th>
                    <th style="width: 90px;">Tipe</th>
                    <th>Bank &amp; No. Rekening</th>
                    <th>Atas Nama</th>
                    <th>Akun COA Terhubung</th>
                    <th class="text-end" style="width: 130px;">Saldo Awal</th>
                    <th class="text-end" style="width: 145px;">Saldo Saat Ini</th>
                    <th class="text-center" style="width: 75px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $idx => $acc)
                <tr>
                    <td class="text-center font-mono text-muted">{{ $idx + 1 }}</td>
                    <td class="text-center">
                        <span class="code-pill font-mono">{{ $acc->kode_akun ?: ('KB-' . $acc->id) }}</span>
                    </td>
                    <td>
                        <strong class="text-dark">{{ $acc->nama_akun }}</strong>
                        @if($acc->deskripsi)
                            <div class="text-muted" style="font-size: 0.7rem; line-height: 1.2;">{{ $acc->deskripsi }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($acc->branch)
                            <span class="badge-entity-cabang"><i class="bi bi-shop text-primary"></i> {{ $acc->branch->nama_cabang }}</span>
                        @else
                            <span class="badge-entity-pt"><i class="bi bi-building text-primary"></i> PT Pusat</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill font-mono px-2 py-0.5" style="background: rgba(30, 58, 138, 0.08); color: #1e3a8a; border: 1px solid rgba(30, 58, 138, 0.2); font-size: 0.68rem;">
                            {{ $acc->tipe }}
                        </span>
                    </td>
                    <td>
                        @if($acc->nomor_rekening)
                            <div class="fw-semibold text-dark">{{ $acc->nama_bank ?: 'Bank' }}</div>
                            <div class="font-mono text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ $acc->nomor_rekening }}</div>
                        @else
                            <span class="text-muted fst-italic" style="font-size: 0.72rem;">Kas Tunai Fisik</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-dark fw-medium">{{ $acc->atas_nama ?: '-' }}</span>
                    </td>
                    <td>
                        @if($acc->coa)
                            <span class="font-mono text-primary fw-bold" style="font-size: 0.72rem;">{{ $acc->coa->code }}</span>
                            <div class="text-secondary" style="font-size: 0.7rem;">{{ $acc->coa->name }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end font-mono text-secondary">
                        Rp {{ number_format($acc->saldo_awal, 2, ',', '.') }}
                    </td>
                    <td class="text-end font-mono fw-bold" style="color: #1d4ed8; font-size: 0.88rem;">
                        Rp {{ number_format($acc->saldo_berjalan, 2, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge-status-active">
                            <i class="bi bi-check-circle-fill"></i>{{ $acc->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-4 text-muted fst-italic">
                        Tidak ada rekening kas &amp; bank yang terdaftar dalam entitas ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="text-end text-uppercase" style="letter-spacing: 0.8px;">
                        <i class="bi bi-calculator me-1 text-primary"></i>GRAND TOTAL REKENING KAS &amp; BANK:
                    </td>
                    <td class="text-end font-mono fw-bold text-secondary">
                        Rp {{ number_format($totalSaldoAwal, 2, ',', '.') }}
                    </td>
                    <td class="text-end font-mono fw-bold fs-6" style="color: #1e3a8a;">
                        Rp {{ number_format($totalSaldoAkhir, 2, ',', '.') }}
                    </td>
                    <td></td>
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

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span><span style="font-size:0.75rem;">Mengambil HD...</span>';

            const canvasTarget = document.querySelector('.print-canvas');

            html2canvas(canvasTarget, {
                scale: 3,
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
                    link.download = 'Laporan_Rekening_Kas_Bank_{{ now()->format("d_M_Y") }}_HD.png';
                    link.href = url;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);

                    btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i><span style="font-size:0.75rem;">Tersimpan (HD)</span>';
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }, 2500);
                }, 'image/png');
            }).catch(function(err) {
                console.error('Error capture HD:', err);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Terjadi kesalahan saat memproses screenshot dokumen.');
            });
        }
    </script>
</body>
</html>
