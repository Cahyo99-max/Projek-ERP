<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran Harga – {{ $tender->nomor_kontrak ?? 'TND-000' }} – {{ $tender->nama_tender }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? '' }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" defer></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #0f172a;
            color: #1e293b;
            font-size: 11.5px;
            line-height: 1.5;
            padding-bottom: 60px;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── FLOATING CONTROLS TOOLBAR ── */
        .toolbar {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.92);
            padding: 8px 14px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 6px;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            padding-right: 10px;
            margin-right: 2px;
        }

        .toolbar-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 15px;
            border-radius: 50px;
            font-size: 11px; font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            border: none;
        }

        .btn-toggle-edit {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }
        .btn-toggle-edit.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            border-color: #f59e0b;
            box-shadow: 0 0 16px rgba(245, 158, 11, 0.6);
        }

        .btn-toggle-option {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
            user-select: none;
        }
        .btn-toggle-option.active {
            background: #2563eb;
            color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 12px rgba(37, 99, 235, 0.4);
        }
        .btn-toggle-option:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .company-select {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 6px 12px;
            font-size: 11px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            outline: none;
        }

        .btn-back {
            background: #fff;
            color: #334155;
        }
        .btn-back:hover { background: #f1f5f9; color: #0f172a; }

        .btn-print {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }
        .btn-print:hover { background: #1e40af; transform: translateY(-1px); }

        .btn-screenshot {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        }
        .btn-screenshot:hover { background: #065f46; transform: translateY(-1px); }

        /* ── EDIT MODE TOAST / BANNER ── */
        #editModeBanner {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 998;
            background: #1e293b;
            color: #f8fafc;
            padding: 10px 22px;
            border-radius: 50px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid #f59e0b;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            display: none;
            align-items: center;
            gap: 10px;
        }

        /* ── EDITABLE STYLES ── */
        .editing-mode-active .editable-text {
            outline: 1.5px dashed #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.06) !important;
            border-radius: 4px;
            cursor: text;
            padding: 1px 4px;
            transition: all 0.15s ease;
        }
        .editing-mode-active .editable-text:hover {
            outline: 1.5px solid #2563eb !important;
            background-color: rgba(59, 130, 246, 0.12) !important;
        }
        .editing-mode-active .editable-text:focus {
            outline: 2px solid #1d4ed8 !important;
            background-color: #fff !important;
            color: #0f172a !important;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.35);
        }

        /* ── A4 SHEET CONTAINER ── */
        .sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 30px auto;
            padding: 16mm 18mm;
            box-shadow: 0 16px 40px rgba(0,0,0,0.4);
            position: relative;
            display: flex;
            flex-direction: column;
            border-radius: 6px;
        }

        /* ── MODERN ROYAL BLUE KOP SURAT ── */
        .letterhead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            position: relative;
        }

        .letterhead::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #1e40af 0%, #3b82f6 50%, #93c5fd 100%);
            border-radius: 2px;
        }

        .company-logo {
            width: 78px;
            height: 78px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #fff;
        }

        .company-details {
            text-align: right;
            line-height: 1.4;
        }

        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.02em;
        }

        .company-meta {
            font-size: 10px;
            color: #64748b;
        }

        /* ── DOCUMENT TITLE BANNER ── */
        .doc-title-bar {
            text-align: center;
            margin: 18px 0 16px 0;
            position: relative;
        }

        .doc-title {
            font-size: 17px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: inline-block;
            padding: 4px 18px;
            background: #eff6ff;
            border-radius: 50px;
            border: 1px solid #bfdbfe;
        }

        .doc-subtitle {
            font-size: 10.5px;
            color: #64748b;
            font-weight: 600;
            margin-top: 5px;
        }

        /* ── METADATA GRID (MODERN BLUE TINT) ── */
        .meta-box {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-size: 11px;
        }

        .meta-col {
            flex: 1;
        }

        .meta-row {
            display: flex;
            margin-bottom: 4px;
        }

        .meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            width: 130px;
            color: #64748b;
            font-weight: 600;
        }

        .meta-val {
            color: #0f172a;
            font-weight: 700;
            flex: 1;
        }

        /* ── INTRO PARAGRAPH ── */
        .intro-text {
            margin-bottom: 12px;
            color: #334155;
            text-align: justify;
            line-height: 1.55;
        }

        /* ── ITEM TABLE (ROYAL BLUE HEADERS) ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
        }

        .items-table th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
            padding: 9px 10px;
            border: 1px solid #1e3a8a;
        }

        .items-table td {
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .item-thumb {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
        }

        .bundle-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 50px;
            border: 1px solid #fde68a;
            margin-left: 4px;
        }

        /* Bundle Breakdown Box */
        .bundle-breakdown-box {
            margin-top: 6px;
            padding: 6px 10px;
            background: #f1f5f9;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            font-size: 10px;
            color: #334155;
            transition: all 0.2s ease;
        }

        .bundle-breakdown-title {
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .bundle-child-list {
            padding-left: 14px;
            margin-bottom: 0;
        }

        .bundle-child-item {
            margin-bottom: 2px;
        }

        /* ── TOTALS (MODERN BLUE CARD) ── */
        .total-card {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
        }

        .total-box {
            width: 320px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .total-row.grand {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            border-bottom: none;
        }

        .total-row.grand .font-mono {
            color: #67e8f9;
        }

        /* ── TERBILANG (ROYAL BLUE TINT) ── */
        .terbilang-box {
            background: #eff6ff;
            border: 1px dashed #93c5fd;
            border-radius: 6px;
            padding: 9px 14px;
            margin-bottom: 14px;
            font-size: 11px;
            color: #1e40af;
        }

        /* ── TERMS & CONDITIONS ── */
        .terms-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10.5px;
            line-height: 1.5;
        }

        .terms-title {
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terms-list {
            padding-left: 16px;
            color: #475569;
        }

        .terms-list li {
            margin-bottom: 3px;
        }

        /* ── SIGNATURES ── */
        .sig-container {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 12px;
        }

        .sig-box {
            width: 30%;
            text-align: center;
            font-size: 11px;
        }

        .sig-space {
            height: 65px;
        }

        .sig-name {
            font-weight: 700;
            color: #0f172a;
            text-decoration: underline;
        }

        .sig-title {
            font-size: 10px;
            color: #64748b;
        }

        /* ── PRINT MEDIA STYLES ── */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar, #editModeBanner, .no-print {
                display: none !important;
            }
            .sheet {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
            .editable-text {
                outline: none !important;
                background-color: transparent !important;
            }
            @page {
                size: A4 portrait;
                margin: 14mm 14mm 14mm 14mm;
            }
        }
    </style>
</head>
<body>

    <!-- FLOATING ACTION & TOGGLE CONTROLS TOOLBAR -->
    <div class="toolbar">
        <div class="toolbar-group">
            @if(isset($companies) && count($companies) > 1)
                <select class="company-select" title="Pilih Perusahaan / Kop Surat" onchange="window.location.href='?company_id=' + this.value;">
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ $company && $company->id == $c->id ? 'selected' : '' }}>
                            🏢 {{ $c->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="button" class="toolbar-btn btn-toggle-edit" id="btnToggleEditMode" onclick="toggleEditMode()">
                <i class="bi bi-pencil-square"></i> Mode Edit Teks: <strong>OFF</strong>
            </button>
            <button type="button" class="toolbar-btn btn-toggle-option active" id="btnToggleBundleBreakdown" onclick="toggleBundleBreakdown()">
                <i class="bi bi-check2-square"></i> Rincian Paket: <strong>Tampil</strong>
            </button>
            <button type="button" class="toolbar-btn btn-toggle-option active" id="btnTogglePhotos" onclick="toggleItemPhotos()">
                <i class="bi bi-image"></i> Foto Produk: <strong>Tampil</strong>
            </button>
        </div>

        <a href="{{ route('tender.index') }}" class="toolbar-btn btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button type="button" class="toolbar-btn btn-screenshot" onclick="downloadScreenshot()">
            <i class="bi bi-camera-fill"></i> Simpan Gambar
        </button>
        <button type="button" class="toolbar-btn btn-print" onclick="prepareAndPrint()">
            <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- TOAST NOTIFICATION WHEN EDIT MODE ACTIVE -->
    <div id="editModeBanner">
        <i class="bi bi-pencil-fill text-warning fs-5"></i>
        <span><strong>Mode Edit Aktif!</strong> Klik teks mana saja pada dokumen untuk mengubah isi, syarat & ketentuan, atau nama penandatangan secara bebas.</span>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-0.5 ms-2 font-mono" style="font-size: 10px;" onclick="addTermItem()">+ Tambah Syarat</button>
    </div>

    <!-- MAIN A4 SHEET -->
    <div class="sheet" id="printSheet">

        <!-- KOP PERUSAHAAN (SESUAI DATA PERUSAHAAN ASLI DI DATABASE) -->
        <div class="letterhead">
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="{{ $appLogo }}" class="company-logo" alt="Logo Perusahaan" onerror="this.src='{{ asset('uploads/barang/placeholder_garment.png') }}'">
                <div>
                    <div class="company-name editable-text" contenteditable="false">{{ $company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA' }}</div>
                    <div class="company-meta editable-text" contenteditable="false">{{ $company->bidang_usaha ?? 'Garment, Konveksi & General Procurement' }}</div>
                    <div class="company-meta">NPWP: <span class="editable-text" contenteditable="false">{{ $company->npwp ?? '-' }}</span> @if(!empty($company->nib)) | NIB: <span class="editable-text" contenteditable="false">{{ $company->nib }}</span> @endif</div>
                </div>
            </div>
            <div class="company-details">
                <div class="company-meta editable-text" contenteditable="false">{{ $company->address ?? 'Jl Cinere Raya No 40-41' }}</div>
                <div class="company-meta editable-text" contenteditable="false">{{ trim(($company->city ?? '') . ($company->province ? ', ' . $company->province : '') . ($company->postal_code ? ' ' . $company->postal_code : '')) ?: 'Depok, Jawa Barat' }}</div>
                <div class="company-meta">
                    @if(!empty($company->phone)) <i class="bi bi-telephone-fill"></i> <span class="editable-text" contenteditable="false">{{ $company->phone }}</span> @endif
                    @if(!empty($company->email)) | <i class="bi bi-envelope-fill"></i> <span class="editable-text" contenteditable="false">{{ $company->email }}</span> @endif
                </div>
                <div class="company-meta">
                    @if(!empty($company->website)) <i class="bi bi-globe"></i> <span class="editable-text" contenteditable="false">{{ $company->website }}</span> @endif
                </div>
            </div>
        </div>

        <!-- DOCUMENT TITLE BANNER -->
        <div class="doc-title-bar">
            <div class="doc-title editable-text" contenteditable="false">SURAT PENAWARAN HARGA (QUOTATION)</div>
            <div class="doc-subtitle">Nomor Surat: <span class="font-mono editable-text" contenteditable="false" style="color: #1e3a8a; font-weight: 700;">{{ $tender->nomor_kontrak ?? 'TND/' . date('Ymd') . '/' . sprintf('%04d', $tender->id) }}</span></div>
        </div>

        <!-- METADATA BOX -->
        <div class="meta-box">
            <div class="meta-col">
                <div class="meta-row">
                    <span class="meta-label">Kepada Yth. :</span>
                    <span class="meta-val editable-text" contenteditable="false">{{ $tender->customer ? ($tender->customer->nama_perusahaan ?? $tender->customer->nama_customer) : '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Kategori / Instansi :</span>
                    <span class="meta-val editable-text" contenteditable="false">{{ $tender->kategori_instansi ?? 'TNI / POLRI' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Perihal :</span>
                    <span class="meta-val editable-text" contenteditable="false" style="color: #2563eb;">Penawaran Harga {{ $tender->nama_tender }}</span>
                </div>
            </div>
            <div class="meta-col" style="border-left: 1px solid #e2e8f0; padding-left: 16px;">
                <div class="meta-row">
                    <span class="meta-label">Tanggal Surat :</span>
                    <span class="meta-val font-mono editable-text" contenteditable="false">{{ $tender->tanggal ? \Carbon\Carbon::parse($tender->tanggal)->isoFormat('D MMMM Y') : date('d F Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Batas Masa Berlaku :</span>
                    <span class="meta-val font-mono editable-text" contenteditable="false" style="color: #dc2626;">{{ $tender->deadline ? \Carbon\Carbon::parse($tender->deadline)->isoFormat('D MMMM Y') : '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Sales PIC / CP :</span>
                    <span class="meta-val editable-text" contenteditable="false">{{ $tender->sales ? $tender->sales->nama_lengkap : 'Marketing Executive' }} ({{ $tender->sales ? ($tender->sales->telepon ?? '-') : '-' }})</span>
                </div>
            </div>
        </div>

        <!-- OPENING INTRO -->
        <div class="intro-text editable-text" contenteditable="false">
            Dengan hormat,<br>
            Sehubungan dengan rencana pengadaan <strong>{{ $tender->nama_tender }}</strong>, bersama surat ini kami dari <strong>{{ $company->nama_perusahaan ?? 'PT Persada Aman Sentosa' }}</strong> bermaksud mengajukan penawaran harga resmi dengan rincian spesifikasi barang/jasa sebagai berikut:
        </div>

        <!-- ITEM DETAILS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="4%" style="text-align: center;">No</th>
                    <th width="8%" class="item-photo-col" style="text-align: center;">Foto</th>
                    <th width="44%">Spesifikasi Barang / Jasa & SKU</th>
                    <th width="10%" style="text-align: center;">Qty</th>
                    <th width="10%" style="text-align: center;">Satuan</th>
                    <th width="12%" style="text-align: right;">Harga Satuan</th>
                    <th width="12%" style="text-align: right;">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tender->items as $idx => $item)
                    @php
                        $brg = $item->barang;
                        $foto = $brg && $brg->primaryFoto ? asset('storage/' . $brg->primaryFoto->foto_path) : ($brg && $brg->fotos && $brg->fotos->first() ? asset('storage/' . $brg->fotos->first()->foto_path) : asset('uploads/barang/placeholder_garment.png'));
                        $isBundle = $brg && $brg->tipe_item === 'Bundle';
                        $comps = $isBundle ? ($brg->bundleComponents ?? []) : [];
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}</td>
                        <td class="item-photo-col" style="text-align: center;">
                            <img src="{{ $foto }}" class="item-thumb" alt="Foto" onerror="this.src='{{ asset('uploads/barang/placeholder_garment.png') }}'">
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">
                                <span class="editable-text" contenteditable="false">{{ $brg->nama_barang ?? 'Item Spesifikasi' }}</span>
                                @if($isBundle)
                                    <span class="bundle-badge"><i class="bi bi-boxes"></i> Paket Bundle</span>
                                @endif
                            </div>
                            <div class="font-mono" style="font-size: 9.5px; color: #64748b;">SKU: <span class="editable-text" contenteditable="false">{{ $brg->kode_barang ?? '-' }}</span></div>
                            
                            @if($isBundle && count($comps) > 0)
                                <div class="bundle-breakdown-box">
                                    <div class="bundle-breakdown-title">
                                        <i class="bi bi-boxes"></i> Rincian Komponen Paket ({{ count($comps) }} Item):
                                    </div>
                                    <ul class="bundle-child-list">
                                        @foreach($comps as $comp)
                                            @php $cb = $comp->childBarang; @endphp
                                            <li class="bundle-child-item editable-text" contenteditable="false">
                                                {{ $cb->nama_barang ?? 'Komponen' }} ({{ $cb->kode_barang ?? '-' }}) — <strong>{{ (float)$comp->qty }} {{ $cb->satuan->nama_satuan ?? 'Pcs' }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 700;" class="font-mono editable-text" contenteditable="false">{{ (float)$item->qty }}</td>
                        <td style="text-align: center;" class="editable-text" contenteditable="false">{{ $item->satuan }}</td>
                        <td style="text-align: right;" class="font-mono editable-text" contenteditable="false">Rp {{ number_format($item->harga_estimasi ?? 0, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;" class="font-mono editable-text" contenteditable="false">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 16px; color: #94a3b8; font-style: italic;">Tidak ada rincian item penawaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TOTALS SUMMARY -->
        <div class="total-card">
            <div class="total-box">
                <div class="total-row">
                    <span style="font-weight: 600; color: #475569;">Subtotal Barang / RAB :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700;">Rp {{ number_format($tender->subtotal ?: $tender->nilai_estimasi, 0, ',', '.') }}</span>
                </div>
                @if(($tender->diskon_nominal ?? 0) > 0)
                <div class="total-row">
                    <span style="font-weight: 600; color: #dc2626;">Diskon Proyek {{ ($tender->diskon_persen ?? 0) > 0 ? '(' . (float)$tender->diskon_persen . '%)' : '' }} :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700; color: #dc2626;">- Rp {{ number_format($tender->diskon_nominal, 0, ',', '.') }}</span>
                </div>
                @endif
                @php
                    $baseSubtotal = $tender->subtotal ?: $tender->nilai_estimasi;
                    $diskonNom = $tender->diskon_nominal ?? 0;
                    $biayaEkstraVal = $tender->biaya_ekstra ?? 0;
                    $isEkstraPpn = !empty($tender->biaya_ekstra_kena_ppn);
                    $dppVal = max(0, $baseSubtotal - $diskonNom) + ($isEkstraPpn ? $biayaEkstraVal : 0);
                @endphp
                <div class="total-row">
                    <span style="font-weight: 600; color: #475569;">Dasar Pengenaan Pajak (DPP) :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700;">Rp {{ number_format($dppVal, 0, ',', '.') }}</span>
                </div>
                @if(($tender->nilai_ppn ?? 0) > 0)
                @php
                    $ppnName = $tender->pajakPpn ? $tender->pajakPpn->nama_pajak : 'PPN';
                @endphp
                <div class="total-row">
                    <span style="font-weight: 600; color: #0284c7;">{{ $ppnName }} {{ (float)($tender->tarif_ppn ?? 11) }}% :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700; color: #0284c7;">+ Rp {{ number_format($tender->nilai_ppn, 0, ',', '.') }}</span>
                </div>
                @endif
                @if(($tender->nilai_pph ?? 0) > 0)
                @php
                    $pphName = $tender->pajakPph ? $tender->pajakPph->nama_pajak : ($tender->tipe_pph ?: 'PPh');
                @endphp
                <div class="total-row">
                    <span style="font-weight: 600; color: #d97706;">{{ $pphName }} {{ (float)$tender->tarif_pph }}% :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700; color: #d97706;">(Rp {{ number_format($tender->nilai_pph, 0, ',', '.') }})</span>
                </div>
                @endif
                @if($biayaEkstraVal > 0)
                <div class="total-row">
                    <span style="font-weight: 600; color: #475569;">Biaya Ekstra {{ $tender->catatan_biaya_ekstra ? '(' . $tender->catatan_biaya_ekstra . ')' : '' }} {{ $isEkstraPpn ? '(Kena PPN / Masuk DPP)' : '' }} :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700;">{{ $isEkstraPpn ? '(Rp ' . number_format($biayaEkstraVal, 0, ',', '.') . ')' : '+ Rp ' . number_format($biayaEkstraVal, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="total-row grand">
                    <span>GRAND TOTAL PENAWARAN :</span>
                    <span class="font-mono editable-text" contenteditable="false">Rp {{ number_format($tender->grand_total ?: $tender->nilai_estimasi, 0, ',', '.') }}</span>
                </div>
                @if(($tender->dp_nominal ?? 0) > 0)
                <div class="total-row" style="border-top: 1px dashed #cbd5e1; padding-top: 4px; margin-top: 4px;">
                    <span style="font-weight: 600; color: #059669;">Uang Muka / DP {{ ($tender->dp_persen ?? 0) > 0 ? '(' . (float)$tender->dp_persen . '%)' : '' }} :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700; color: #059669;">Rp {{ number_format($tender->dp_nominal, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span style="font-weight: 600; color: #2563eb;">Sisa Tagihan Pelunasan :</span>
                    <span class="font-mono editable-text" contenteditable="false" style="font-weight: 700; color: #2563eb;">Rp {{ number_format($tender->sisa_tagihan ?: max(0, ($tender->grand_total ?: $tender->nilai_estimasi) - $tender->dp_nominal), 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- TERBILANG -->
        <div class="terbilang-box">
            <strong>Terbilang :</strong> <em># <span class="editable-text" contenteditable="false">{{ $terbilang }}</span> #</em>
        </div>

        <!-- TERMS & CONDITIONS -->
        <div class="terms-box">
            <div class="terms-title">
                <span><i class="bi bi-shield-check"></i> SYARAT & KETENTUAN PENAWARAN (TERMS OF QUOTATION):</span>
                <span class="no-print" style="font-size: 9px; font-weight: normal; color: #64748b;">(Dapat diedit bebas saat Mode Edit aktif)</span>
            </div>
            <ol class="terms-list" id="termsList">
                <li class="editable-text" contenteditable="false">Harga penawaran di atas adalah sah dan mengikat hingga tanggal <strong>{{ $tender->deadline ? \Carbon\Carbon::parse($tender->deadline)->isoFormat('D MMMM Y') : 'batas akhir lelang' }}</strong>.</li>
                <li class="editable-text" contenteditable="false">Spesifikasi teknis, bahan baku, dan standar kualitas barang sesuai dengan dokumen Rencana Kerja & Syarat (RKS) / Kerangka Acuan Kerja (KAK).</li>
                <li class="editable-text" contenteditable="false">Jadwal pelaksanaan & pengiriman produk dilakukan sesuai kesepakatan Surat Perintah Kerja (SPK) / Kontrak resmi.</li>
                @if($tender->paymentTerm || ($tender->dp_nominal ?? 0) > 0 || !empty($tender->catatan_pembayaran))
                <li class="editable-text" contenteditable="false">
                    Syarat & Ketentuan Pembayaran: <strong>{{ $tender->paymentTerm ? $tender->paymentTerm->nama_term : 'Termin Proyek' }}</strong>@if(($tender->dp_nominal ?? 0) > 0) (Uang Muka DP: <strong>Rp {{ number_format($tender->dp_nominal, 0, ',', '.') }}</strong>)@endif{{ !empty($tender->catatan_pembayaran) ? ' — ' . $tender->catatan_pembayaran : '.' }}
                </li>
                @else
                <li class="editable-text" contenteditable="false">Sistem pembayaran mengacu pada termin atau ketentuan lelang yang telah disepakati bersama.</li>
                @endif
            </ol>
        </div>

        <!-- SIGNATURES -->
        <div class="sig-container">
            <div class="sig-box">
                <div class="editable-text" contenteditable="false">Diterima & Disetujui,</div>
                <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #64748b; margin-top: 2px;">Pihak Klien / Instansi</div>
                <div class="sig-space"></div>
                <div class="sig-name editable-text" contenteditable="false">( ............................................ )</div>
                <div class="sig-title editable-text" contenteditable="false">Tanda Tangan & Stempel</div>
            </div>
            <div class="sig-box">
                <div class="editable-text" contenteditable="false">Mengetahui,</div>
                <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #64748b; margin-top: 2px;">{{ $company->nama_perusahaan ?? 'Pimpinan Perusahaan' }}</div>
                <div class="sig-space"></div>
                <div class="sig-name editable-text" contenteditable="false">{{ $company->director_name ?? '( Direktur / Pimpinan )' }}</div>
                <div class="sig-title editable-text" contenteditable="false">Direktur Operasional / Keuangan</div>
            </div>
            <div class="sig-box">
                <div class="editable-text" contenteditable="false">Hormat Kami,</div>
                <div class="editable-text" contenteditable="false" style="font-weight: 700; color: #64748b; margin-top: 2px;">Account Executive / PIC</div>
                <div class="sig-space"></div>
                <div class="sig-name editable-text" contenteditable="false">{{ $tender->sales ? $tender->sales->nama_lengkap : '( ............................................ )' }}</div>
                <div class="sig-title editable-text" contenteditable="false">Marketing & Tender Specialist</div>
            </div>
        </div>

    </div>

    <script>
        let isEditMode = false;
        let showBundle = true;
        let showPhotos = true;

        // Toggle Live Free Text Editing Mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            const sheet = document.getElementById('printSheet');
            const btn = document.getElementById('btnToggleEditMode');
            const banner = document.getElementById('editModeBanner');
            const editables = document.querySelectorAll('.editable-text');

            if (isEditMode) {
                sheet.classList.add('editing-mode-active');
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-pencil-fill"></i> Mode Edit: <strong>AKTIF</strong>';
                banner.style.display = 'flex';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                });
            } else {
                sheet.classList.remove('editing-mode-active');
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-pencil-square"></i> Mode Edit Teks: <strong>OFF</strong>';
                banner.style.display = 'none';

                editables.forEach(el => {
                    el.setAttribute('contenteditable', 'false');
                });
            }
        }

        // Add customizable new term item
        function addTermItem() {
            const list = document.getElementById('termsList');
            if (!list) return;
            const li = document.createElement('li');
            li.className = 'editable-text';
            li.setAttribute('contenteditable', isEditMode ? 'true' : 'false');
            li.innerHTML = 'Ketik syarat atau catatan tambahan di sini...';
            list.appendChild(li);
            if (isEditMode) li.focus();
        }

        // Toggle Bundle Sub-Components Breakdown
        function toggleBundleBreakdown() {
            showBundle = !showBundle;
            const boxes = document.querySelectorAll('.bundle-breakdown-box');
            const btn = document.getElementById('btnToggleBundleBreakdown');

            boxes.forEach(el => {
                el.style.display = showBundle ? 'block' : 'none';
            });

            if (showBundle) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-check2-square"></i> Rincian Paket: <strong>Tampil</strong>';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-square"></i> Rincian Paket: <strong>Sembunyi</strong>';
            }
        }

        // Toggle Product Photos Column
        function toggleItemPhotos() {
            showPhotos = !showPhotos;
            const cols = document.querySelectorAll('.item-photo-col');
            const btn = document.getElementById('btnTogglePhotos');

            cols.forEach(el => {
                el.style.display = showPhotos ? '' : 'none';
            });

            if (showPhotos) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-image"></i> Foto Produk: <strong>Tampil</strong>';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-image-slash"></i> Foto Produk: <strong>Sembunyi</strong>';
            }
        }

        // Prepare and Print
        function prepareAndPrint() {
            if (isEditMode) {
                if (document.activeElement) document.activeElement.blur();
            }
            window.print();
        }

        // Download Screenshot PNG
        function downloadScreenshot() {
            const sheet = document.getElementById('printSheet');
            html2canvas(sheet, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `Surat_Penawaran_{{ $tender->nomor_kontrak ?? 'TND' }}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>
</body>
</html>
