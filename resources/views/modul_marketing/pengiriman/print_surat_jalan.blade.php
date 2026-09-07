<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Surat Jalan – {{ $deliveryOrder->nomor_do }} – {{ $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan')) }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- html2canvas for High-Definition Screenshot Generation -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        :root {
            --primary-blue: #1e40af;
            --primary-dark: #1e3a8a;
            --accent-blue: #3b82f6;
            --soft-blue: #eff6ff;
            --border-blue: #dbeafe;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #525659;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            min-height: 100vh;
        }

        .sheet-viewport-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 0 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm 12mm 15mm;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            margin: 0 auto 20px auto;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        /* Top Action Toolbar */
        .no-print-toolbar {
            width: 210mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-print {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .btn-screenshot {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
        }

        .btn-close-view {
            background: #334155;
            color: #cbd5e1;
        }

        /* Letterhead / Kop Surat */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2.5px solid var(--primary-blue);
            margin-bottom: 12px;
            position: relative;
        }

        .kop-logo {
            max-height: 55px;
            max-width: 140px;
            object-fit: contain;
        }

        .company-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            max-width: 480px;
        }

        .company-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Document Title */
        .doc-title-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--primary-dark);
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .doc-number {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-blue);
            background: var(--soft-blue);
            padding: 3px 12px;
            border-radius: 20px;
            border: 1px solid var(--border-blue);
        }

        /* Meta Information Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .meta-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .meta-card-title {
            font-size: 10px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
            margin-bottom: 3px;
            line-height: 1.35;
        }

        .info-label {
            color: #64748b;
            width: 105px;
            flex-shrink: 0;
        }

        .info-val {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
            word-break: break-word;
        }

        /* Table Item */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .items-table thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            border: 1px solid #1e3a8a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .items-table tbody td {
            padding: 7px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .item-code-badge {
            display: inline-block;
            font-size: 8.5px;
            font-family: 'JetBrains Mono', monospace;
            background: var(--soft-blue);
            color: var(--primary-blue);
            padding: 1px 5px;
            border-radius: 4px;
            border: 1px solid var(--border-blue);
            margin-top: 2px;
        }

        /* Notice Box */
        .notice-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 9px;
            color: #475569;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        /* 4 Columns Signatures */
        .sig-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            text-align: center;
            margin-top: auto;
            padding-top: 10px;
        }

        .sig-block {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 95px;
            padding: 6px;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
            background: #fafafa;
        }

        .sig-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
        }

        .sig-line {
            font-weight: 700;
            color: #0f172a;
            font-size: 9.5px;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 2px;
            margin: 0 4px;
        }

        .sig-role {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Print Media Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .sheet-viewport-wrapper {
                padding: 0 !important;
                overflow: visible !important;
            }
            .page-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: 100vh !important;
                padding: 12mm 15mm !important;
                page-break-after: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print / Action Toolbar -->
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-truck text-primary" style="font-size: 16px;"></i>
            <span style="font-weight: 700; font-size: 13px;">Surat Jalan Pengiriman – {{ $deliveryOrder->nomor_do }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-toolbar btn-print" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Cetak Surat Jalan
            </button>
            <button type="button" class="btn-toolbar btn-screenshot" onclick="takeScreenshot()">
                <i class="bi bi-camera-fill"></i> Simpan Gambar (HD)
            </button>
            <button type="button" class="btn-toolbar btn-close-view" onclick="window.close()">
                <i class="bi bi-x-lg"></i> Tutup
            </button>
        </div>
    </div>

    <!-- Viewport Container -->
    <div class="sheet-viewport-wrapper">
        <div class="page-sheet" id="delivery_order_paper_sheet">
            
            <div>
                <!-- Kop Dokumen Resmi -->
                @php
                    $compNama = $company->nama_perusahaan ?? 'PT. PERSADA AMAN SENTOSA';
                    $compAlamat = $company->address ?? ($branch->address ?? 'Jl Cinere Raya No 40-41, Depok, Jawa Barat');
                    $compPhone = $company->phone ?? ($branch->phone ?? '-');
                    $compEmail = $company->email ?? ($branch->email ?? '-');
                    $compNpwp  = $company->npwp ?? '';
                @endphp

                <div class="kop-surat">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ $companyLogo ?? asset('gambar/logo.png') }}" class="kop-logo" alt="Logo">
                    </div>
                    <div style="text-align: right;">
                        <div class="company-name">{{ $compNama }}</div>
                        <div class="company-address">{{ $compAlamat }}</div>
                        <div class="company-meta">
                            Telp: {{ $compPhone }} | Email: {{ $compEmail }}
                            @if($compNpwp) | NPWP: {{ $compNpwp }} @endif
                        </div>
                    </div>
                </div>

                <!-- Document Title -->
                <div class="doc-title-box">
                    <h1 class="doc-title">SURAT JALAN PENGIRIMAN (DO)</h1>
                    <div class="doc-number">No: {{ $deliveryOrder->nomor_do }}</div>
                </div>

                <!-- Meta Information -->
                <div class="meta-grid">
                    <!-- Kartu Penerima -->
                    <div class="meta-card">
                        <div class="meta-card-title">
                            <i class="bi bi-geo-alt-fill"></i> Tujuan &amp; Informasi Penerima
                        </div>
                        @if($deliveryOrder->customer?->nama_perusahaan)
                        <div class="info-row">
                            <span class="info-label">Instansi / PT:</span>
                            <span class="info-val" style="color: var(--primary-blue);">{{ $deliveryOrder->customer->nama_perusahaan }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">Nama Penerima:</span>
                            <span class="info-val">{{ $deliveryOrder->nama_penerima ?: ($deliveryOrder->customer?->nama_customer ?: 'Pelanggan Umum') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Telepon / HP:</span>
                            <span class="info-val font-mono">{{ $deliveryOrder->telepon_penerima ?: ($deliveryOrder->customer?->telepon ?: '-') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Alamat Kirim:</span>
                            <span class="info-val">{{ $deliveryOrder->alamat_tujuan ?: ($deliveryOrder->customer?->alamat ?: 'Diambil di Tempat') }}</span>
                        </div>
                    </div>

                    <!-- Kartu Logistik & Pengiriman -->
                    <div class="meta-card">
                        <div class="meta-card-title">
                            <i class="bi bi-truck"></i> Detail Logistik &amp; Kurir
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tgl Pengiriman:</span>
                            <span class="info-val font-mono">{{ $deliveryOrder->tanggal_kirim ? $deliveryOrder->tanggal_kirim->format('d F Y') : date('d F Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Pesanan (SO):</span>
                            <span class="info-val font-mono">{{ $deliveryOrder->salesOrder?->nomor_so ?: '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gudang Asal:</span>
                            <span class="info-val">{{ $deliveryOrder->gudang?->nama_gudang ?: 'Gudang Utama' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode / Kurir:</span>
                            <span class="info-val">{{ $deliveryOrder->metode_pengiriman }} {{ $deliveryOrder->nama_ekspedisi ? '(' . $deliveryOrder->nama_ekspedisi . ')' : '' }}</span>
                        </div>
                        @if($deliveryOrder->nama_sopir || $deliveryOrder->plat_nomor || $deliveryOrder->nomor_resi)
                        <div class="info-row">
                            <span class="info-label">Sopir / No. Resi:</span>
                            <span class="info-val font-mono">{{ $deliveryOrder->nama_sopir ?: '' }} {{ $deliveryOrder->plat_nomor ? '[' . $deliveryOrder->plat_nomor . ']' : '' }} {{ $deliveryOrder->nomor_resi ? 'Resi: ' . $deliveryOrder->nomor_resi : '' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Table Items -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 35px;" class="text-center">No</th>
                            <th style="width: 110px;">Kode / SKU</th>
                            <th>Nama Produk &amp; Deskripsi Barang</th>
                            <th style="width: 80px;" class="text-center">Qty Kirim</th>
                            <th style="width: 65px;" class="text-center">Satuan</th>
                            <th style="width: 75px;" class="text-center">Check (✓)</th>
                            <th style="width: 140px;">Keterangan / Koli</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveryOrder->details as $index => $item)
                            @php
                                $namaBarang = $item->nama_barang_custom ?: ($item->barang?->nama_barang ?: 'Item');
                                $sku = $item->barang?->kode_barang ?: '-';
                                $satuan = $item->nama_satuan ?: ($item->barang?->satuan?->nama_satuan ?: 'Pcs');
                            @endphp
                            <tr>
                                <td class="text-center font-mono">{{ $index + 1 }}</td>
                                <td class="font-mono">
                                    <span class="item-code-badge">{{ $sku }}</span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $namaBarang }}</div>
                                </td>
                                <td class="text-center font-mono" style="font-weight: 700; font-size: 10.5px;">{{ number_format($item->qty_kirim, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $satuan }}</td>
                                <td class="text-center">
                                    <div style="width: 16px; height: 16px; border: 1.5px solid #94a3b8; border-radius: 3px; margin: 0 auto;"></div>
                                </td>
                                <td style="color: #64748b; font-size: 9px;">{{ $item->catatan_item ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada rincian item pengiriman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Notice / Catatan Pengiriman -->
                <div class="notice-box">
                    <div style="font-weight: 700; margin-bottom: 2px; color: var(--primary-blue);">
                        <i class="bi bi-info-circle me-1"></i> Ketentuan Penerimaan Barang:
                    </div>
                    <div>
                        {{ $deliveryOrder->catatan_pengiriman ?: 'Barang telah diperiksa dan diserahkan dalam kondisi baik, lengkap, dan baru. Segala klaim kekurangan barang wajib dicantumkan saat serah terima barang ini.' }}
                    </div>
                </div>
            </div>

            <!-- 4 Signatures Grid -->
            <div class="sig-container">
                <!-- Dibuat Oleh -->
                <div class="sig-block">
                    <div class="sig-title" contenteditable="true">Dibuat Oleh,</div>
                    <div style="height: 45px;"></div>
                    <div class="sig-line" contenteditable="true">{{ $deliveryOrder->creator?->nama_lengkap ?: ($deliveryOrder->creator?->username ?: 'Staf Logistik') }}</div>
                    <div class="sig-role">Logistik &amp; Gudang</div>
                </div>

                <!-- Pengemudi / Ekspedisi -->
                <div class="sig-block">
                    <div class="sig-title" contenteditable="true">Pengemudi / Kurir,</div>
                    <div style="height: 45px;"></div>
                    <div class="sig-line" contenteditable="true">{{ $deliveryOrder->nama_sopir ?: '( .................... )' }}</div>
                    <div class="sig-role">{{ $deliveryOrder->nama_ekspedisi ?: 'Driver / Ekspedisi' }}</div>
                </div>

                <!-- Security / Checker -->
                <div class="sig-block">
                    <div class="sig-title" contenteditable="true">Petugas Keamanan,</div>
                    <div style="height: 45px;"></div>
                    <div class="sig-line" contenteditable="true">( .................... )</div>
                    <div class="sig-role">Security / Checker</div>
                </div>

                <!-- Penerima Barang -->
                <div class="sig-block">
                    <div class="sig-title" contenteditable="true">Diterima Oleh,</div>
                    <div style="height: 45px;"></div>
                    <div class="sig-line" contenteditable="true">{{ $deliveryOrder->nama_penerima ?: '( .................... )' }}</div>
                    <div class="sig-role">Penerima / Stempel PT</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Screenshot Script -->
    <script>
        function takeScreenshot() {
            const element = document.getElementById('delivery_order_paper_sheet');
            const toolbar = document.querySelector('.no-print-toolbar');
            
            if (toolbar) toolbar.style.opacity = '0.5';

            html2canvas(element, {
                scale: 3,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                if (toolbar) toolbar.style.opacity = '1';
                const link = document.createElement('a');
                link.download = 'Surat_Jalan_{{ $deliveryOrder->nomor_do }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                if (toolbar) toolbar.style.opacity = '1';
                alert('Gagal mengambil screenshot: ' + err);
            });
        }
    </script>
</body>
</html>
