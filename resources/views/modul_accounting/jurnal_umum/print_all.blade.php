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
            margin-top: 45px;
            margin-bottom: 5px;
        }

        /* Editable Signature Fields */
        [contenteditable="true"] {
            outline: 1px dashed transparent;
            border-radius: 4px;
            padding: 1px 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
        }

        [contenteditable="true"]:hover {
            outline: 1px dashed #3b82f6;
            background: rgba(59, 130, 246, 0.06);
        }

        [contenteditable="true"]:focus {
            outline: 1.5px solid #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            cursor: text;
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
            /* Mencegah header tabel diulang di halaman baru saat print (hanya tampil di halaman 1) */
            .table-custom thead,
            thead {
                display: table-row-group !important;
            }
            .table-custom tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .jurnal-block-group {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            [contenteditable="true"] {
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
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
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-mono d-none d-md-inline-flex align-items-center gap-1.5 px-3 py-1.5" style="font-size: 10px;">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Tanda Tangan Dapat Diedit Langsung (Klik Teks)</span>
                </span>
                <a href="{{ route('accounting.jurnal-umum.export-excel', ['start_date' => $startDate, 'end_date' => $endDate, 'referensi_tipe' => $referensiTipe]) }}" class="btn btn-outline-success btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 text-decoration-none">
                    <i class="bi bi-file-earmark-excel-fill text-success"></i>
                    <span>Export Excel</span>
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); border: none;">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan (PDF)
                </button>
            </div>
        </div>

        @if(!empty($isTruncated))
        <div class="alert alert-warning border-0 rounded-3 p-2.5 mb-3 d-flex align-items-center gap-2 small no-print">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
            <div>
                <strong>Perhatian Skala Data Besar:</strong> Ditemukan total <strong>{{ number_format($totalCount, 0, ',', '.') }}</strong> transaksi jurnal pada rentang ini. Sistem membatasi pratinjau cetak ke <strong>{{ number_format($maxPrintLimit, 0, ',', '.') }} transaksi pertama</strong> untuk mencegah browser freeze / kehabisan memori. Nilai Grand Total di bawah tetap mengakumulasi seluruh {{ number_format($totalCount, 0, ',', '.') }} transaksi secara akurat. Silakan perkecil rentang tanggal (misal per bulan/minggu) untuk cetak dokumen fisik per periode.
            </div>
        </div>
        @endif

        <!-- Header Laporan Bersih (Tanpa Nama PT / Cabang Sesuai Permintaan) -->
        <div class="d-flex justify-content-between align-items-center kop-surat-wrapper">
            <div>
                <h5 class="fw-bold mb-0 text-uppercase" style="color: #1e3a8a; letter-spacing: 0.5px; font-size: 15px;">
                    LAPORAN JURNAL UMUM
                </h5>
            </div>
            <div class="text-end">
                <div class="fw-semibold text-dark" style="font-size: 11px;">
                    Periode: <strong class="text-primary">{{ date('d F Y', strtotime($startDate)) }}</strong> s/d <strong class="text-primary">{{ date('d F Y', strtotime($endDate)) }}</strong>
                </div>
                <div class="text-muted font-mono" style="font-size: 9.5px;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
            </div>
        </div>

@php
if (!function_exists('parseJurnalKeteranganAndTender')) {
    function parseJurnalKeteranganAndTender($jurnal) {
        $text   = $jurnal->keterangan ?: '-';
        $tender = $jurnal->no_tender ?? null;

        // 1. Ekstrak tender jika ada di dalam teks
        if (!$tender && preg_match('/(?:Tender|TDN)[\s:]*([A-Za-z0-9\-_]+)/i', $text, $matches)) {
            $tender = $matches[1];
        }

        // 2. Bersihkan tag tender dalam kurung dari teks agar tidak berulang
        $clean = preg_replace('/\s*\((?:Tender)[\s:][^)]*\)/i', '', $text);
        $clean = preg_replace('/\s*\[(?:Tender)[\s:][^\]]*\]/i', '', $clean);

        // 3. Hapus kurung siku nama PT / Cabang di awal keterangan, misal: [Kas Kecil Cabang - Iconic Durable Reliable] atau [PT ...]
        $clean = preg_replace('/^\[[^\]]+\]\s*/i', '', $clean);

        // 4. Hapus referensi kode dokumen / nomor ref yang panjang (PC-..., DO-..., INV-..., CR-..., CD-..., PO-..., SO-..., GR-..., GI-..., KM-..., KK-..., dsb.)
        $clean = preg_replace('/\b(?:PC|DO|INV|CR|CD|PO|SO|GR|GI|KM|KK|SPK|TF|ADJ|OPN)(?:-[A-Z0-9\/\-_]+|\/[A-Z0-9\/\-_]+)\b/i', '', $clean);

        // 5. Bersihkan nomor ref dalam kurung jika masih ada
        $clean = preg_replace('/\s*\((?:SO|PO|DO|CR|CD|INV|GR|GI|SPK|PC)[^)]*\)/i', '', $clean);
        $clean = preg_replace('/\s*\([A-Z0-9\/\-_]{6,}\)/i', '', $clean);

        // 6. Bersihkan duplikasi "PT PT" menjadi "PT"
        $clean = preg_replace('/\bPT\s+PT\b/i', 'PT', $clean);

        // 7. Ubah variasi kata sambung " dari " atau " ke " menjadi separator " - "
        $clean = preg_replace('/\s+(?:ke|dari)\s+/i', ' - ', $clean);

        // 8. Rapikan pemisah ganda seperti " - - " atau ": -" atau "- :", kurung kosong, dan tanda pagar menggantung
        $clean = preg_replace('/\s*[-:]\s*[-:]+\s*/', ' - ', $clean);
        $clean = preg_replace('/\s*:\s*/', ': ', $clean);
        $clean = preg_replace('/\s*\(\s*\)/', '', $clean);
        $clean = preg_replace('/\s*#\s*/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean, " -:,#");

        return [
            'keterangan' => $clean ?: $text,
            'tender'     => $tender
        ];
    }
}
@endphp

        <!-- Tabel Jurnal Umum -->
        <div class="table-responsive mb-2">
            <table class="table table-custom w-100 mb-0">
                <thead>
                    <tr>
                        <th style="width: 85px;" class="text-center">Tanggal</th>
                        <th style="width: 140px;">No. Jurnal</th>
                        <th>Nama Akun COA / Keterangan</th>
                        <th style="width: 125px;" class="text-end">Debit</th>
                        <th style="width: 125px;" class="text-end">Kredit</th>
                    </tr>
                </thead>

                <!-- Mengelompokkan per transaksi jurnal dengan tbody tersendiri (page-break safe) -->
                @forelse($jurnals as $j)
                @php
                    $parsed = parseJurnalKeteranganAndTender($j);
                @endphp
                <tbody class="jurnal-block-group">
                    <!-- Header Judul Jurnal: Ringkas, Tanpa Nama PT/Cabang, Tanpa Nomor Ref, Menampilkan Tender -->
                    <tr class="jurnal-group-row">
                        <td class="text-center font-mono" style="color: #1d4ed8 !important;">{{ date('d/m/Y', strtotime($j->tanggal_jurnal)) }}</td>
                        <td class="font-mono">
                            <span class="badge" style="background: rgba(37, 99, 235, 0.12); color: #1e40af; border: 1px solid rgba(37, 99, 235, 0.25); font-size: 9.5px;">{{ $j->nomor_jurnal }}</span>
                        </td>
                        <td colspan="3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5">
                                <div class="d-flex align-items-center flex-wrap gap-1.5">
                                    <span class="fw-bold text-dark">{{ $parsed['keterangan'] }}</span>
                                    @if(!empty($parsed['tender']))
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-mono px-2 py-0.5" style="font-size: 8.5px; font-weight: 700;">
                                            <i class="bi bi-briefcase me-1"></i>Tender: {{ $parsed['tender'] }}
                                        </span>
                                    @endif
                                </div>
                                <span class="badge rounded-pill font-mono" style="background: rgba(37, 99, 235, 0.12); color: #1d4ed8; border: 1px solid rgba(37, 99, 235, 0.25); font-size: 8.5px; padding: 2px 8px; font-weight: 600;">
                                    {{ $j->referensi_tipe ?: 'Manual' }}
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Baris Rincian Akun (Bersih & Padat: Tanpa Catatan Tambahan) -->
                    @foreach($j->details as $d)
                    <tr class="jurnal-detail-row">
                        <td class="border-0"></td>
                        <td class="border-0 text-center font-mono text-muted" style="font-size: 10px;">↳</td>
                        <td class="{{ $d->kredit > 0 ? 'kredit-indent' : '' }}">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-mono me-1" style="font-size: 9.5px;">{{ $d->account ? $d->account->code : '-' }}</span>
                            <span class="text-dark fw-semibold">{{ $d->account ? $d->account->name : 'Akun Tidak Ditemukan' }}</span>
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
                        <td colspan="3" class="text-end text-uppercase" style="font-size: 11px; font-weight: 700;">
                            GRAND TOTAL PERIODE:
                            @if(isset($isBalanced))
                                @if($isBalanced)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 font-mono" style="font-size: 9px; font-weight: 700; padding: 2px 7px;">
                                        <i class="bi bi-check2-circle me-0.5"></i>BALANCE
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 font-mono" style="font-size: 9px; font-weight: 700; padding: 2px 7px;">
                                        <i class="bi bi-exclamation-triangle me-0.5"></i>SELISIH: Rp {{ number_format(abs($totalDebit - $totalKredit), 2, ',', '.') }}
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td class="text-end font-mono" style="font-size: 11px; font-weight: 700; color: #16a34a !important;">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="text-end font-mono" style="font-size: 11px; font-weight: 700; color: #1d4ed8 !important;">Rp {{ number_format($totalKredit, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Otorisasi Resmi (Dapat Diedit Langsung Sebelum Dicetak) -->
        <div class="row text-center signature-section">
            <div class="col-4 sig-box">
                <div class="text-muted" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks" style="font-size: 10.5px;">Dibuat Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-bold text-dark" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 10.5px;">( Staff Accounting )</div>
                <div class="text-muted mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 9.5px;">Staff Pembukuan</div>
            </div>
            <div class="col-4 sig-box">
                <div class="text-muted" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks" style="font-size: 10.5px;">Diperiksa Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-bold text-dark" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 10.5px;">( Accounting Manager )</div>
                <div class="text-muted mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 9.5px;">Manager Akuntansi &amp; Keuangan</div>
            </div>
            <div class="col-4 sig-box">
                <div class="text-muted" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks" style="font-size: 10.5px;">Disetujui Oleh:</div>
                <div class="sig-line"></div>
                <div class="fw-bold text-dark" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 10.5px;">( Direktur Keuangan )</div>
                <div class="text-muted mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 9.5px;">Direksi Perusahaan</div>
            </div>
        </div>

    </div>

</body>
</html>
