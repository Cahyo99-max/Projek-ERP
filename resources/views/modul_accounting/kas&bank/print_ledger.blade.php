<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening Koran - {{ $selectedAccount ? $selectedAccount->nama_akun : 'Semua Rekening' }}</title>
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
            max-width: 900px;
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
            <button onclick="window.print()" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen (PDF)
            </button>
        </div>

        <!-- Header Kop Perusahaan -->
        <div class="d-flex justify-content-between align-items-start pb-3 mb-4 border-bottom border-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">LAPORAN MUTASI BUKU KAS &amp; REKENING KORAN</h4>
                <div class="text-muted small">Periode: <strong>{{ date('d/m/Y', strtotime($startDate)) }}</strong> s/d <strong>{{ date('d/m/Y', strtotime($endDate)) }}</strong></div>
            </div>
            <div class="text-end">
                <h6 class="fw-bold text-success mb-0">{{ $company ? $company->nama_perusahaan : config('app.name', 'ERP SYSTEM') }}</h6>
                <div class="text-dark small fw-semibold">{{ $activeBranch ? ('🏬 Cabang: ' . $activeBranch->nama_cabang) : '🏢 PT Pusat (Perusahaan Induk)' }}</div>
                <div class="text-muted small">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
            </div>
        </div>

        <!-- Info Rekening -->
        <div class="p-3 rounded-3 bg-light border mb-4">
            <div class="row g-2 small">
                <div class="col-6">
                    <span class="text-muted">Akun / Buku Kas:</span>
                    <strong class="d-block text-dark">{{ $selectedAccount ? $selectedAccount->nama_akun : 'Konsolidasi Seluruh Rekening Kas & Bank' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">No. Rekening / Bank:</span>
                    <strong class="d-block font-monospace text-dark">{{ $selectedAccount && $selectedAccount->nomor_rekening ? ($selectedAccount->nama_bank . ' - ' . $selectedAccount->nomor_rekening) : ($selectedAccount ? 'Kas Fisik Tunai' : 'Semua Rekening') }}</strong>
                </div>
                @if($selectedAccount && $selectedAccount->atas_nama)
                <div class="col-6 mt-2">
                    <span class="text-muted">Atas Nama:</span>
                    <strong class="d-block text-dark">{{ $selectedAccount->atas_nama }}</strong>
                </div>
                @endif
                @if($selectedAccount && $selectedAccount->coa)
                <div class="col-6 mt-2">
                    <span class="text-muted">Akun COA Terhubung:</span>
                    <strong class="d-block font-monospace text-dark">{{ $selectedAccount->coa->code }} - {{ $selectedAccount->coa->name }}</strong>
                </div>
                @endif
            </div>
        </div>

        <!-- Ringkasan Mutasi -->
        <div class="row g-3 mb-4 text-center">
            <div class="col-4">
                <div class="p-2.5 rounded-3 border bg-light">
                    <div class="text-muted small">Total Penerimaan (Masuk)</div>
                    <div class="fw-bold text-success font-monospace">+ Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2.5 rounded-3 border bg-light">
                    <div class="text-muted small">Total Pengeluaran (Keluar)</div>
                    <div class="fw-bold text-danger font-monospace">- Rp {{ number_format($totalKeluar, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2.5 rounded-3 border bg-light">
                    <div class="text-muted small">Arus Kas Bersih (Net)</div>
                    <div class="fw-bold font-monospace {{ ($totalMasuk - $totalKeluar) >= 0 ? 'text-primary' : 'text-danger' }}">
                        Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekening Koran -->
        <div class="table-responsive mb-4">
            <table class="table table-custom w-100">
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Kategori &amp; Pihak</th>
                        <th class="text-end text-success">Masuk (Rp)</th>
                        <th class="text-end text-danger">Keluar (Rp)</th>
                        <th class="text-end">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $idx => $m)
                    <tr>
                        <td class="font-monospace">{{ $idx + 1 }}</td>
                        <td class="font-monospace text-muted">{{ date('d/m/Y', strtotime($m->tanggal)) }}</td>
                        <td class="font-monospace fw-bold text-dark">{{ $m->nomor_transaksi }}</td>
                        <td>
                            <div class="fw-semibold">{{ $m->kategori }}</div>
                            <div class="text-muted small">{{ $m->nama_kontak ?: ($m->keterangan ?: '-') }}</div>
                        </td>
                        <td class="text-end font-monospace {{ $m->tipe === 'Masuk' ? 'text-success fw-bold' : 'text-muted' }}">
                            {{ $m->tipe === 'Masuk' ? ('+ ' . number_format($m->jumlah, 0, ',', '.')) : '-' }}
                        </td>
                        <td class="text-end font-monospace {{ ($m->tipe === 'Keluar' || $m->tipe === 'Transfer') ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ ($m->tipe === 'Keluar' || $m->tipe === 'Transfer') ? ('- ' . number_format($m->jumlah, 0, ',', '.')) : '-' }}
                        </td>
                        <td class="text-end font-monospace fw-semibold text-dark">
                            Rp {{ number_format($m->saldo_sesudah, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted fst-italic">
                            Tidak ada transaksi mutasi pada rentang periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan Pengesahan -->
        <div class="row pt-4 mt-4 border-top text-center" style="font-size: 0.85rem;">
            <div class="col-4">
                <div class="text-muted mb-5">Dibuat Oleh (Staff/Operator),</div>
                <div class="fw-bold text-dark">____________________</div>
            </div>
            <div class="col-4">
                <div class="text-muted mb-5">Diperiksa Oleh (Accounting),</div>
                <div class="fw-bold text-dark">____________________</div>
            </div>
            <div class="col-4">
                <div class="text-muted mb-5">Disetujui Oleh (Finance/Owner),</div>
                <div class="fw-bold text-dark">____________________</div>
            </div>
        </div>

    </div>

</body>
</html>
