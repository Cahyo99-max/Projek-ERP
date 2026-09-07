<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Jurnal - {{ $jurnal->nomor_jurnal }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            padding: 20px;
        }
        .print-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table-custom th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-custom td {
            font-size: 0.85rem;
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        
        <!-- Action Top Bar (No Print) -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom no-print">
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Tutup
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen (PDF)
            </button>
        </div>

        <!-- Header Kop Surat Resmi -->
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4" style="border-bottom: 2.5px solid #1e40af;">
            <div class="d-flex align-items-center gap-3">
                @if(!empty($companyLogo))
                    <img src="{{ $companyLogo }}" alt="Logo Perusahaan" style="max-height: 55px; max-width: 140px; object-fit: contain;">
                @else
                    <div class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                @endif
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1e3a8a; letter-spacing: 0.5px;">{{ $company?->nama_perusahaan ?: config('app.name', 'PT PERSADA AMAN SENTOSA') }}</h5>
                    <div class="text-muted small" style="font-size: 0.8rem;">
                        {{ $company?->address ?: ($company?->city ?: 'Sistem Informasi Manajemen Akuntansi & Keuangan ERP') }}
                        @if($company?->phone) | Telp: {{ $company->phone }} @endif
                    </div>
                </div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">BUKTI MEMORIAL / VOUCHER JURNAL</h5>
                <div class="text-muted small">Nomor: <strong class="font-monospace text-primary">{{ $jurnal->nomor_jurnal }}</strong></div>
                <div class="text-muted small font-monospace" style="font-size: 0.72rem;">Tanggal: {{ date('d F Y', strtotime($jurnal->tanggal_jurnal)) }}</div>
            </div>
        </div>

        <!-- Info Transaksi -->
        <div class="p-3 rounded-3 bg-light border mb-4">
            <div class="row g-2 small">
                <div class="col-6">
                    <span class="text-muted">Sumber Transaksi:</span>
                    <strong class="d-block text-dark">{{ $jurnal->referensi_tipe ?: 'Manual (Penyesuaian)' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Dibuat Oleh:</span>
                    <strong class="d-block text-dark">{{ $jurnal->creator ? $jurnal->creator->name : 'System Accounting' }}</strong>
                </div>
                <div class="col-12 mt-2 pt-2 border-top">
                    <span class="text-muted">Keterangan:</span>
                    <div class="fw-semibold text-dark">{{ $jurnal->keterangan ?: '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Tabel Baris Jurnal -->
        <div class="table-responsive mb-4">
            <table class="table table-custom w-100">
                <thead>
                    <tr>
                        <th style="width: 30px;" class="text-center">No</th>
                        <th>Kode &amp; Nama Akun COA</th>
                        <th class="text-end" style="width: 170px;">Debit (Rp)</th>
                        <th class="text-end" style="width: 170px;">Kredit (Rp)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnal->details as $idx => $d)
                    <tr>
                        <td class="text-center font-monospace">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="font-monospace text-dark">{{ $d->account ? $d->account->code : '-' }}</strong>
                            <div class="text-muted small">{{ $d->account ? $d->account->name : 'Akun Tidak Ditemukan' }}</div>
                        </td>
                        <td class="text-end font-monospace" style="{{ $d->debit > 0 ? 'font-weight: 700; color: #16a34a !important;' : 'color: #94a3b8;' }}">
                            {{ $d->debit > 0 ? number_format($d->debit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-end font-monospace" style="{{ $d->kredit > 0 ? 'font-weight: 700; color: #1d4ed8 !important;' : 'color: #94a3b8;' }}">
                            {{ $d->kredit > 0 ? number_format($d->kredit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="small text-muted">{{ $d->keterangan ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold bg-light">
                    <tr>
                        <td colspan="2" class="text-end">TOTAL VOUCHER:</td>
                        <td class="text-end font-monospace" style="font-weight: 700; color: #16a34a !important;">Rp {{ number_format($jurnal->total_debit, 2, ',', '.') }}</td>
                        <td class="text-end font-monospace" style="font-weight: 700; color: #1d4ed8 !important;">Rp {{ number_format($jurnal->total_kredit, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Otorisasi -->
        <div class="row text-center mt-5 pt-2">
            <div class="col-4 d-flex flex-column align-items-center">
                <div class="text-muted" style="font-size: 11px;">Dibuat Oleh:</div>
                <div style="width: 150px; border-bottom: 1.5px solid #334155; margin-top: 50px; margin-bottom: 5px;"></div>
                <div class="fw-semibold text-dark" style="font-size: 11px;">Staff Accounting</div>
            </div>
            <div class="col-4 d-flex flex-column align-items-center">
                <div class="text-muted" style="font-size: 11px;">Diperiksa Oleh:</div>
                <div style="width: 150px; border-bottom: 1.5px solid #334155; margin-top: 50px; margin-bottom: 5px;"></div>
                <div class="fw-semibold text-dark" style="font-size: 11px;">Accounting Manager</div>
            </div>
            <div class="col-4 d-flex flex-column align-items-center">
                <div class="text-muted" style="font-size: 11px;">Disetujui Oleh:</div>
                <div style="width: 150px; border-bottom: 1.5px solid #334155; margin-top: 50px; margin-bottom: 5px;"></div>
                <div class="fw-semibold text-dark" style="font-size: 11px;">Direktur Keuangan</div>
            </div>
        </div>

    </div>

</body>
</html>
