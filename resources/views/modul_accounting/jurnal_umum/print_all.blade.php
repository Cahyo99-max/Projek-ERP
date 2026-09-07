<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jurnal Umum - Periode {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            padding: 20px;
            font-size: 11.5px;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .print-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        /* Kop Surat Resmi */
        .kop-surat-wrapper {
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        /* Table Styling - Clean Minimalist (No Grid Lines) */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
            border: none !important;
        }

        .table-custom thead th {
            background-color: #1e40af !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            border: none !important;
            border-bottom: 2px solid #1e3a8a !important;
        }

        .table-custom thead th:first-child {
            border-top-left-radius: 6px;
        }

        .table-custom thead th:last-child {
            border-top-right-radius: 6px;
        }

        .table-custom td {
            padding: 6px 12px;
            vertical-align: middle;
            border: none !important;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }

        /* Cegah Pemotongan Grup Transaksi Jurnal di Tengah Halaman */
        .jurnal-block-group {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            border-bottom: 1px solid #e2e8f0;
        }

        .jurnal-group-row td {
            background-color: #eff6ff !important;
            color: #1e3a8a !important;
            font-weight: 700;
            padding: 7px 12px !important;
            border-top: 1.5px solid #bfdbfe !important;
            border-bottom: 1px solid #dbeafe !important;
        }

        .jurnal-detail-row td {
            background-color: #ffffff;
            border-bottom: 1px solid #f8fafc !important;
        }

        .jurnal-detail-row:hover td {
            background-color: #f8fafc;
        }

        .kredit-indent {
            padding-left: 28px !important;
        }

        .table-custom tfoot {
            display: table-row-group !important; /* Mencegah tfoot nongol di tengah halaman */
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .table-custom tfoot td {
            background-color: #f8fafc !important;
            font-weight: 700;
            padding: 10px 12px;
            border: none !important;
        }

        .signature-section {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            margin-top: 35px;
            padding-top: 10px;
        }

        .sig-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .sig-line {
            width: 155px;
            border-bottom: 1.5px solid #334155;
            margin-top: 50px;
            margin-bottom: 5px;
        }

        /* Print Specific Setup */
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 15mm 12mm;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .table-custom thead {
                display: table-header-group;
            }
            .table-custom tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .jurnal-block-group {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        
        <!-- Action Top Bar (No Print) -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom no-print">
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1">
                <i class="bi bi-arrow-left me-1"></i> Tutup
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); border: none;">
                <i class="bi bi-printer me-1"></i> Cetak Laporan (PDF)
            </button>
        </div>

        <!-- Kop Surat Resmi Perusahaan -->
        <div class="d-flex justify-content-between align-items-center kop-surat-wrapper">
            <div class="d-flex align-items-center gap-3">
                @if(!empty($companyLogo))
                    <img src="{{ $companyLogo }}" alt="Logo Perusahaan" style="max-height: 50px; max-width: 130px; object-fit: contain;">
                @else
                    <div class="p-2 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                @endif
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1e3a8a; letter-spacing: 0.5px; font-size: 15px;">{{ $company?->nama_perusahaan ?: config('app.name', 'PT PERSADA AMAN SENTOSA') }}</h5>
                    <div class="text-muted" style="font-size: 10px;">
                        {{ $company?->address ?: ($company?->city ?: 'Sistem Informasi Manajemen Akuntansi & Keuangan ERP') }}
                        @if($company?->phone) | Telp: {{ $company->phone }} @endif
                    </div>
                </div>
            </div>
            <div class="text-end">
                <h6 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px; font-size: 13px;">LAPORAN BUKU JURNAL UMUM</h6>
                <div class="text-muted" style="font-size: 10px;">
                    Periode: <strong class="text-dark">{{ date('d F Y', strtotime($startDate)) }}</strong> s/d <strong class="text-dark">{{ date('d F Y', strtotime($endDate)) }}</strong>
                </div>
                <div class="text-muted font-mono" style="font-size: 9.5px;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
            </div>
        </div>

        <!-- Tabel Jurnal Umum -->
        <div class="table-responsive mb-2">
            <table class="table table-custom w-100 mb-0">
                <thead>
                    <tr>
                        <th style="width: 85px;" class="text-center">Tanggal</th>
                        <th style="width: 145px;">No. Jurnal</th>
                        <th>Kode &amp; Nama Akun COA / Keterangan</th>
                        <th style="width: 130px;" class="text-end">Debit (Rp)</th>
                        <th style="width: 130px;" class="text-end">Kredit (Rp)</th>
                    </tr>
                </thead>

                <!-- Mengelompokkan per transaksi jurnal dengan tbody tersendiri (page-break safe) -->
                @forelse($jurnals as $j)
                <tbody class="jurnal-block-group">
                    <!-- Header Judul Jurnal -->
                    <tr class="jurnal-group-row">
                        <td class="text-center font-mono" style="color: #1d4ed8 !important;">{{ date('d/m/Y', strtotime($j->tanggal_jurnal)) }}</td>
                        <td class="font-mono">
                            <span class="badge" style="background: rgba(37, 99, 235, 0.15); color: #1e40af; border: 1px solid rgba(37, 99, 235, 0.3); font-size: 9.5px;">{{ $j->nomor_jurnal }}</span>
                        </td>
                        <td colspan="3">
                            <span class="fw-bold text-dark">{{ $j->keterangan ?: '-' }}</span>
                            <span class="badge rounded-pill ms-1.5" style="background: #2563eb; color: #ffffff; font-size: 9px; padding: 2px 6px;">
                                {{ $j->referensi_tipe ?: 'Manual' }}
                            </span>
                        </td>
                    </tr>

                    <!-- Baris Rincian Akun (Debit & Kredit) -->
                    @foreach($j->details as $d)
                    <tr class="jurnal-detail-row">
                        <td class="border-0"></td>
                        <td class="border-0 text-center font-mono text-muted" style="font-size: 10px;">↳</td>
                        <td class="{{ $d->kredit > 0 ? 'kredit-indent' : '' }}">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-mono me-1" style="font-size: 9.5px;">{{ $d->account ? $d->account->code : '-' }}</span>
                            <strong class="text-dark">{{ $d->account ? $d->account->name : 'Akun Tidak Ditemukan' }}</strong>
                            @if($d->keterangan && $d->keterangan !== $j->keterangan)
                                <div class="text-muted fst-italic ps-2" style="font-size: 9.5px;">Catatan: {{ $d->keterangan }}</div>
                            @endif
                        </td>
                        <td class="text-end font-mono" style="{{ $d->debit > 0 ? 'font-weight: 700; color: #16a34a !important;' : 'color: #94a3b8;' }}">
                            {{ $d->debit > 0 ? number_format($d->debit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-end font-mono" style="{{ $d->kredit > 0 ? 'font-weight: 700; color: #1d4ed8 !important;' : 'color: #94a3b8;' }}">
                            {{ $d->kredit > 0 ? number_format($d->kredit, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted fst-italic">
                            Tidak ada transaksi jurnal akuntansi pada rentang periode ini.
                        </td>
                    </tr>
                </tbody>
                @endforelse

                <!-- Grand Total (Hanya Tampil di Akhir Dokumen) -->
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end text-uppercase" style="font-size: 11px; font-weight: 700;">GRAND TOTAL PERIODE:</td>
                        <td class="text-end font-mono" style="font-size: 11px; font-weight: 700; color: #16a34a !important;">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="text-end font-mono" style="font-size: 11px; font-weight: 700; color: #1d4ed8 !important;">Rp {{ number_format($totalKredit, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Otorisasi Resmi -->
        <div class="row text-center signature-section">
            <div class="col-4 sig-box">
                <div class="text-muted" style="font-size: 10.5px;">Dibuat Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-semibold text-dark" style="font-size: 10.5px;">Staff Accounting</div>
            </div>
            <div class="col-4 sig-box">
                <div class="text-muted" style="font-size: 10.5px;">Diperiksa Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-semibold text-dark" style="font-size: 10.5px;">Accounting Manager</div>
            </div>
            <div class="col-4 sig-box">
                <div class="text-muted" style="font-size: 10.5px;">Disetujui Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-semibold text-dark" style="font-size: 10.5px;">Direktur Keuangan</div>
            </div>
        </div>

    </div>

</body>
</html>
