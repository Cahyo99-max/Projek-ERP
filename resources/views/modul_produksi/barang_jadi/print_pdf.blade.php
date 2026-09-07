@php
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoPath = null;

    if ($company && !empty($company->logo) && file_exists(public_path('storage/' . $company->logo))) {
        $appLogoPath = public_path('storage/' . $company->logo);
    } elseif ($appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))) {
        $appLogoPath = public_path('storage/' . $appLogoSetting);
    } elseif (file_exists(public_path('gambar/logo.png'))) {
        $appLogoPath = public_path('gambar/logo.png');
    }

    $logoBase64 = null;
    if ($appLogoPath && file_exists($appLogoPath)) {
        $ext = strtolower(pathinfo($appLogoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($appLogoPath));
    }

    $spkObj = $penerimaan->spk;
    $barangJadi = $penerimaan->barangJadi ?: ($spkObj ? $spkObj->barangJadi : null);
    $satName = $penerimaan->satuan ? $penerimaan->satuan->nama_satuan : ($barangJadi && $barangJadi->satuan ? $barangJadi->satuan->nama_satuan : 'Pcs');

    $penerimaName = $penerimaan->penerima ? ($penerimaan->penerima->nama_lengkap ?? $penerimaan->penerima->nama) : ($penerimaan->penerima_nama ?: 'Petugas Gudang');
    $penyerahName = $penerimaan->penyerah ? ($penerimaan->penyerah->nama_lengkap ?? $penerimaan->penyerah->nama) : ($penerimaan->penyerah_nama ?: 'Operator Line');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Barang Jadi - {{ $penerimaan->nomor_penerimaan }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5pt;
            color: #0f172a;
            background: #090d16;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .top-action-bar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 0.75rem 1.5rem;
        }

        .document-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 1.5rem auto;
            background: #ffffff;
            padding: 2.2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .doc-header {
            border-bottom: 2.5px solid #0a2540;
            padding-bottom: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .company-logo {
            max-height: 55px;
            width: auto;
            object-fit: contain;
        }

        .doc-title-badge {
            background: linear-gradient(135deg, #0a2540, #1e40af);
            color: #ffffff;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            display: inline-block;
            font-size: 11pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .info-card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .pdf-table th {
            background: #0a2540 !important;
            color: #ffffff !important;
            padding: 0.75rem 0.9rem;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #0a2540;
        }

        .pdf-table td {
            padding: 0.75rem 0.9rem;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9.5pt;
        }

        .signature-box {
            margin-top: 3rem;
            page-break-inside: avoid;
        }

        .sig-cell {
            text-align: center;
            padding: 0.5rem;
        }

        .sig-line {
            border-bottom: 1.5px solid #0f172a;
            margin-top: 4.5rem;
            margin-bottom: 0.3rem;
        }

        @media print {
            .top-action-bar { display: none !important; }
            body { background: #ffffff !important; margin: 0 !important; padding: 0 !important; }
            .document-wrapper { width: 100% !important; min-height: auto !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border-radius: 0 !important; }
        }
    </style>
</head>
<body>
    {{-- TOP ACTION BAR --}}
    <div class="top-action-bar d-flex align-items-center justify-content-between text-white">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check-fill text-success fs-5"></i>
            <span class="fw-bold font-monospace fs-6">Bukti Penerimaan Barang Jadi: {{ $penerimaan->nomor_penerimaan }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-printer-fill"></i>
                <span>Cetak Cetakan</span>
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold">
                Tutup
            </button>
        </div>
    </div>

    {{-- PRINTABLE DOCUMENT WRAPPER --}}
    <div class="document-wrapper">
        {{-- HEADER COMPANY LOGO & DOC TITLE --}}
        <div class="doc-header d-flex justify-content-between align-items-start">
            <div>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Company" class="company-logo mb-2">
                @endif
                <h5 class="fw-bold m-0" style="color: #0a2540;">{{ $company ? $company->nama_perusahaan : 'PERUSAHAAN ERP' }}</h5>
                <div class="small text-muted">{{ $company ? $company->alamat : 'Alamat Perusahaan Utama' }}</div>
                <div class="small text-muted">Telp: {{ $company ? ($company->telepon ?: '-') : '-' }} | Email: {{ $company ? ($company->email ?: '-') : '-' }}</div>
            </div>

            <div class="text-end">
                <div class="doc-title-badge mb-2">BUKTI PENERIMAAN BARANG JADI</div>
                <div class="fw-bold font-monospace fs-6 text-primary">{{ $penerimaan->nomor_penerimaan }}</div>
                <div class="small text-muted font-monospace">Tanggal: {{ $penerimaan->tanggal_penerimaan ? $penerimaan->tanggal_penerimaan->format('d F Y') : '-' }}</div>
            </div>
        </div>

        {{-- INFO CARDS --}}
        <div class="info-card-box">
            <div class="row g-3">
                <div class="col-6">
                    <table class="w-100 small">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nomor SPK Produksi</td>
                            <td>: <b class="font-monospace text-primary">{{ $spkObj ? $spkObj->nomor_spk : '-' }}</b></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Target Qty Rencana</td>
                            <td>: <b class="font-monospace">{{ number_format($spkObj ? $spkObj->jumlah_rencana : 0, 0, ',', '.') }} {{ $satName }}</b></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status SPK</td>
                            <td>: <b class="font-monospace text-success">{{ $spkObj ? $spkObj->status : '-' }}</b></td>
                        </tr>
                    </table>
                </div>

                <div class="col-6">
                    <table class="w-100 small">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Gudang Tujuan</td>
                            <td>: <b class="font-monospace">[{{ $penerimaan->gudang ? $penerimaan->gudang->kode_gudang : '-' }}] {{ $penerimaan->gudang ? $penerimaan->gudang->nama_gudang : '-' }}</b></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nomor / Lokasi Rak</td>
                            <td>: <b class="font-monospace">{{ $penerimaan->lokasiRak ? '[' . $penerimaan->lokasiRak->kode_rak . '] ' . $penerimaan->lokasiRak->nama_rak : '-' }}</b></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Petugas Penerima</td>
                            <td>: <b>{{ $penerimaName }}</b></td>
                        </tr>
                        <tr>
                            <td class="text-muted">QC / Penyerah</td>
                            <td>: <b>{{ $penyerahName }}</b></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE DETAIL HASIL PRODUKSI --}}
        <table class="pdf-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Kode & Nama Barang Jadi</th>
                    <th class="text-center" style="width: 110px;">Satuan</th>
                    <th class="text-center" style="width: 130px;">Hasil Baik (Good)</th>
                    <th class="text-center" style="width: 120px;">Hasil Reject</th>
                    <th class="text-end" style="width: 140px;">HPP Satuan</th>
                    <th class="text-end" style="width: 160px;">Total Nilai HPP</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center font-monospace small text-muted">1</td>
                    <td>
                        <div class="fw-bold">[{{ $barangJadi ? $barangJadi->kode_barang : '-' }}] {{ $barangJadi ? $barangJadi->nama_barang : '-' }}</div>
                        <small class="text-muted">Kategori: {{ $barangJadi && $barangJadi->kategori ? $barangJadi->kategori->nama_kategori : 'Barang Jadi' }}</small>
                    </td>
                    <td class="text-center font-monospace">{{ $satName }}</td>
                    <td class="text-center font-monospace fw-bold text-success fs-6">{{ number_format($penerimaan->jumlah_hasil_baik, 0, ',', '.') }} {{ $satName }}</td>
                    <td class="text-center font-monospace text-danger">{{ number_format($penerimaan->jumlah_reject, 0, ',', '.') }} {{ $satName }}</td>
                    <td class="text-end font-monospace">Rp {{ number_format($penerimaan->hpp_satuan, 2, ',', '.') }}</td>
                    <td class="text-end font-monospace fw-bold text-primary">Rp {{ number_format($penerimaan->total_nilai_hpp, 2, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background: #f1f5f9;">
                    <td colspan="3" class="text-end pe-3">TOTAL PERSYARATAN:</td>
                    <td class="text-center font-monospace text-success fs-6">{{ number_format($penerimaan->jumlah_hasil_baik, 0, ',', '.') }} {{ $satName }}</td>
                    <td class="text-center font-monospace text-danger">{{ number_format($penerimaan->jumlah_reject, 0, ',', '.') }} {{ $satName }}</td>
                    <td class="text-end font-monospace">TOTAL HPP:</td>
                    <td class="text-end font-monospace text-primary fs-6">Rp {{ number_format($penerimaan->total_nilai_hpp, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($penerimaan->catatan)
            <div class="p-2.5 mb-3 rounded-2 border border-secondary-subtle bg-light small">
                <b>Catatan Transaksi:</b> <i>{{ $penerimaan->catatan }}</i>
            </div>
        @endif

        {{-- SIGNATURE SECTION --}}
        <div class="signature-box">
            <div class="row text-center">
                <div class="col-4 sig-cell">
                    <div class="small text-muted mb-1">Diserahkan Oleh (QC / Line Operator)</div>
                    <div class="sig-line"></div>
                    <b class="small font-monospace">{{ $penyerahName }}</b>
                </div>
                <div class="col-4 sig-cell">
                    <div class="small text-muted mb-1">Diterima Oleh (Petugas Gudang)</div>
                    <div class="sig-line"></div>
                    <b class="small font-monospace">{{ $penerimaName }}</b>
                </div>
                <div class="col-4 sig-cell">
                    <div class="small text-muted mb-1">Mengetahui (Manajer Produksi)</div>
                    <div class="sig-line"></div>
                    <b class="small font-monospace">( ........................................ )</b>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
