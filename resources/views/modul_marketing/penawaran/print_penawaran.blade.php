<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran Harga – {{ $quotation->nomor_quotation }} – {{ $quotation->customer?->nama_perusahaan ?: ($quotation->customer?->nama_customer ?? 'Klien') }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #090d16;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            padding-bottom: 60px;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Floating Controls Toolbar (Cinematic Glassmorphism) */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.88);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(59, 130, 246, 0.3);
            backdrop-filter: blur(20px);
        }

        .toolbar-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 11.5px; font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            border: none;
        }

        .btn-print {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.5);
        }
        .btn-print:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.7); 
        }

        .btn-close-preview {
            background: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-close-preview:hover { background: rgba(255, 255, 255, 0.18); }

        /* A4 Paper Sheet */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 30px auto;
            background: #ffffff;
            padding: 16mm 18mm 16mm 18mm;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(59, 130, 246, 0.15);
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }

        /* Top Decorative Color Accent Bar */
        .top-accent-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #60a5fa 100%);
        }

        /* Letterhead / Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 14px;
            margin-bottom: 16px;
            position: relative;
        }
        .kop-surat::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0; right: 0;
            height: 1px;
            background: #93c5fd;
        }

        .kop-logo {
            max-height: 65px;
            max-width: 145px;
            object-fit: contain;
        }

        .kop-info {
            text-align: right;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
        }
        .kop-info strong {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            display: block;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        /* Doc Title */
        .doc-title-box {
            text-align: center;
            margin-bottom: 18px;
            padding-top: 4px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1e3a8a;
            margin-bottom: 2px;
        }
        .doc-number {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            padding: 3px 12px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
            margin-bottom: 18px;
            font-size: 10.5px;
        }
        .info-box {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.03);
        }
        .info-box-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3.5px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748b; font-size: 10px; }
        .info-val { font-weight: 600; color: #0f172a; text-align: right; font-size: 10.5px; }

        /* Items Table */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            overflow: hidden;
        }
        .print-table thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 9px 10px;
            border: none;
        }
        .print-table td {
            padding: 7.5px 10px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .print-table td:last-child { border-right: none; }
        .print-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .item-code-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 8.5px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        /* Summary Box */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
            margin-bottom: 18px;
        }
        .terbilang-card {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .summary-card {
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            margin-bottom: 4px;
        }
        .summary-row.grand-total-row {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 7px 10px;
            border-radius: 6px;
            margin-top: 6px;
            margin-bottom: 0;
            box-shadow: 0 3px 8px rgba(30, 58, 138, 0.25);
        }
        .summary-row.grand-total-row span {
            color: #ffffff !important;
        }

        /* Terms & Signatures */
        .terms-box {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 20px;
            background: linear-gradient(180deg, #f8fafc 0%, #f0f7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .terms-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 4px;
            letter-spacing: 0.4px;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
            font-size: 10.5px;
            margin-top: 18px;
        }
        .sig-space { height: 55px; }
        .sig-line {
            display: inline-block;
            min-width: 180px;
            border-top: 1.5px solid #1e3a8a;
            padding-top: 4px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Footer Watermark */
        .doc-footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* Print Media Styles (Suppress Browser Header/Footer like URL & Title) */
        @page {
            size: A4 portrait;
            margin: 0mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .toolbar { display: none !important; }
            .paper-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 12mm 15mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Toolbar (Cinematic Glassmorphism) -->
    <div class="toolbar">
        @if(isset($companies) && $companies->count() > 1)
        <div style="display: flex; align-items: center; gap: 6px; border-right: 1px solid rgba(255, 255, 255, 0.2); padding-right: 12px; margin-right: 4px;">
            <label style="color: #94a3b8; font-size: 11px; font-weight: 600; white-space: nowrap;"><i class="bi bi-building me-1 text-primary"></i> Perusahaan:</label>
            <select style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 600; outline: none; cursor: pointer;" onchange="window.location.href='?company_id=' + this.value">
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ $company && $company->id == $c->id ? 'selected' : '' }}>
                        {{ $c->nama_perusahaan }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <button class="toolbar-btn btn-print" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Cetak Dokumen (PDF)
        </button>
        <button class="toolbar-btn btn-close-preview" onclick="window.close()">
            <i class="bi bi-x-lg"></i> Tutup
        </button>
    </div>

    <!-- Paper Sheet Container -->
    <div class="paper-sheet">
        
        <!-- Top Cinematic Accent Bar -->
        <div class="top-accent-bar"></div>

        <!-- Kop Surat -->
        <div class="kop-surat">
            <div>
                @if(!empty($companyLogo))
                <img src="{{ $companyLogo }}" alt="Logo" class="kop-logo">
                @endif
            </div>
            <div class="kop-info">
                <strong>{{ $company->nama_perusahaan ?? 'PERUSAHAAN' }}</strong>
                @if(!empty(trim($company->alamat ?? '')))
                <div>{{ $company->alamat }}</div>
                @endif
                @if(!empty(trim($company->telepon ?? '')) || !empty(trim($company->email ?? '')))
                <div>
                    @if(!empty(trim($company->telepon ?? '')))
                    <span>Telp: {{ $company->telepon }}</span>
                    @endif
                    @if(!empty(trim($company->telepon ?? '')) && !empty(trim($company->email ?? '')))
                    <span> | </span>
                    @endif
                    @if(!empty(trim($company->email ?? '')))
                    <span>Email: {{ $company->email }}</span>
                    @endif
                </div>
                @endif
                @if(!empty(trim($company->npwp ?? '')))
                <div>NPWP: {{ $company->npwp }}</div>
                @endif
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title-box">
            <h1 class="doc-title">SURAT PENAWARAN HARGA</h1>
            <div class="doc-number">
                No: {{ $quotation->nomor_quotation }}
                @if($quotation->revisi > 0)
                <span style="color: #1e40af; font-weight: 800;">(Rev. {{ $quotation->revisi }})</span>
                @endif
            </div>
        </div>

        <!-- Info Grid -->
        @php
            $clientCompany = $quotation->customer?->nama_perusahaan ?: $quotation->customer?->nama_customer;
            $clientPic = ($quotation->customer?->nama_customer && $quotation->customer?->nama_customer !== $quotation->customer?->nama_perusahaan) ? $quotation->customer?->nama_customer : null;
        @endphp
        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-title"><i class="bi bi-building"></i> Kepada Yth. (Customer / Klien)</div>
                <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-bottom: 2px; text-transform: uppercase;">{{ $clientCompany ?? '-' }}</div>
                @if($clientPic)
                <div style="color: #334155; font-size: 10px; margin-bottom: 3px; font-weight: 600;"><i class="bi bi-person-badge text-primary me-1"></i>Attn / PIC: <strong>{{ $clientPic }}</strong></div>
                @endif
                <div style="color: #475569; font-size: 10px; margin-bottom: 2px;">{{ $quotation->customer?->defaultAddress ? ($quotation->customer->defaultAddress->alamat . ($quotation->customer->defaultAddress->kota ? ', ' . $quotation->customer->defaultAddress->kota : '')) : ($quotation->customer?->alamat_utama ?? '-') }}</div>
                <div style="color: #64748b; font-size: 9.5px;">Telp: {{ $quotation->customer?->telepon ?? '-' }} | Email: {{ $quotation->customer?->email ?? '-' }}</div>
                @if($quotation->tender)
                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #bfdbfe; font-size: 10px; color: #1d4ed8; font-weight: 600;">
                    <i class="bi bi-briefcase me-1"></i> Proyek: {{ $quotation->tender->nama_tender }} ({{ $quotation->tender->nomor_kontrak }})
                </div>
                @endif
            </div>

            <div class="info-box">
                <div class="info-box-title"><i class="bi bi-file-earmark-text"></i> Informasi Penawaran</div>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-val font-mono">{{ $quotation->tanggal_quotation ? $quotation->tanggal_quotation->format('d F Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Masa Berlaku:</span>
                    <span class="info-val font-mono">{{ $quotation->berlaku_hingga ? $quotation->berlaku_hingga->format('d F Y') : '30 Hari' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Perihal:</span>
                    <span class="info-val">{{ $quotation->perihal ?: 'Penawaran Harga' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sales / PIC:</span>
                    <span class="info-val">{{ $quotation->sales?->nama_lengkap ?: ($quotation->creator?->name ?? '-') }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 35px; text-align: center;">No</th>
                    <th>Nama Barang & Rincian Spesifikasi</th>
                    <th style="width: 85px; text-align: center;">Qty</th>
                    <th style="width: 110px; text-align: right;">Harga Satuan</th>
                    <th style="width: 75px; text-align: right;">Diskon</th>
                    <th style="width: 125px; text-align: right;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $idx => $item)
                <tr>
                    <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $item->nama_barang_custom ?: ($item->barang?->nama_barang ?? '-') }}</div>
                        @if($item->barang)
                        <div style="margin-top: 1px;">
                            <span class="item-code-badge">{{ $item->barang->kode_barang }}</span>
                            @if($item->kategori)
                            <span style="font-size: 8.5px; color: #64748b; margin-left: 4px;">{{ $item->kategori->nama_kategori }}</span>
                            @endif
                        </div>
                        @endif
                        @if($item->deskripsi_item)
                        <div style="color: #64748b; font-size: 9px; margin-top: 2px;">{{ $item->deskripsi_item }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 600;">
                        {{ (float)$item->qty }} <span style="font-weight: 400; color: #64748b; font-size: 9px;">{{ $item->nama_satuan ?: ($item->satuan?->nama_satuan ?? 'Pcs') }}</span>
                    </td>
                    <td style="text-align: right;" class="font-mono">
                        Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right;" class="font-mono">
                        {{ (float)$item->diskon_persen > 0 ? (float)$item->diskon_persen . '%' : ($item->diskon_nominal > 0 ? 'Rp ' . number_format($item->diskon_nominal, 0, ',', '.') : '-') }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;" class="font-mono">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Grid -->
        <div class="summary-wrapper">
            <div class="terbilang-card">
                <div class="info-box-title"><i class="bi bi-chat-quote"></i> Terbilang:</div>
                <div style="font-style: italic; font-weight: 600; color: #1e3a8a; line-height: 1.4;">
                    "{{ $terbilang }}"
                </div>
                @if($quotation->paymentTerm)
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #bfdbfe; font-size: 9px; color: #475569;">
                    <strong>Ketentuan Pembayaran:</strong> {{ $quotation->paymentTerm->nama_term }} ({{ $quotation->paymentTerm->jumlah_hari }} Hari)
                    @if($quotation->dp_nominal > 0)
                    | <strong style="color: #1d4ed8;">DP: Rp {{ number_format($quotation->dp_nominal, 0, ',', '.') }}</strong>
                    @endif
                </div>
                @endif
            </div>

            <div class="summary-card">
                <div class="summary-row">
                    <span style="color: #64748b;">Subtotal Barang:</span>
                    <span class="font-mono fw-semibold">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($quotation->diskon_nominal > 0)
                <div class="summary-row" style="color: #dc2626;">
                    <span>Diskon Tambahan ({{ (float)$quotation->diskon_persen }}%):</span>
                    <span class="font-mono">- Rp {{ number_format($quotation->diskon_nominal, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($quotation->nilai_ppn > 0)
                <div class="summary-row">
                    <span style="color: #64748b;">PPN ({{ (float)$quotation->tarif_ppn }}%):</span>
                    <span class="font-mono text-primary">+ Rp {{ number_format($quotation->nilai_ppn, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($quotation->biaya_pengiriman > 0)
                <div class="summary-row">
                    <span style="color: #64748b;">Biaya Pengiriman:</span>
                    <span class="font-mono">+ Rp {{ number_format($quotation->biaya_pengiriman, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="summary-row grand-total-row">
                    <span>GRAND TOTAL:</span>
                    <span class="font-mono">Rp {{ number_format($quotation->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        @if($quotation->syarat_ketentuan)
        <div class="terms-box">
            <div class="terms-title"><i class="bi bi-card-checklist"></i> Syarat & Ketentuan Penawaran:</div>
            <div style="white-space: pre-line; margin-top: 2px;">{{ $quotation->syarat_ketentuan }}</div>
        </div>
        @endif

        <!-- Signature Blocks -->
        <div class="signature-grid">
            <div>
                <div style="color: #64748b; margin-bottom: 2px;">Disetujui & Diterima Oleh,</div>
                <div style="font-weight: 700; color: #1e3a8a;">{{ $clientCompany ?? 'Pihak Pembeli' }}</div>
                <div class="sig-space"></div>
                <div class="sig-line">{{ $clientPic ? '( ' . $clientPic . ' )' : '( ........................................ )' }}</div>
                <div style="color: #94a3b8; font-size: 8.5px; margin-top: 2px;">Tanda Tangan & Stempel Resmi</div>
            </div>

            <div>
                <div style="color: #64748b; margin-bottom: 2px;">Hormat Kami,</div>
                <div style="font-weight: 700; color: #1e3a8a;">{{ $company->nama_perusahaan ?? 'PASTA CREATIVE' }}</div>
                <div class="sig-space"></div>
                <div class="sig-line">{{ $quotation->sales?->nama_lengkap ?: ($quotation->creator?->name ?? 'Sales & Marketing') }}</div>
                <div style="color: #94a3b8; font-size: 8.5px; margin-top: 2px;">Bagian Marketing / Penjualan</div>
            </div>
        </div>

    </div>

</body>
</html>
