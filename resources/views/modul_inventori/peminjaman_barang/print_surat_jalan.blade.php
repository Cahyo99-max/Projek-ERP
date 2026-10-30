<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan – {{ $peminjaman->nomor_peminjaman }}</title>
    @php
        $faviconUrl = $appLogo ?? asset('gambar/logo.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- html2canvas for Screenshot Feature -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #e2e8f0;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        /* ── TOP SCREEN TOOLBAR (HIDDEN ON PRINT) ── */
        .toolbar-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #f8fafc;
        }

        .toolbar-badge {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Toolbar Toggles */
        .toolbar-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
        }
        .toolbar-toggle:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }
        .toolbar-toggle input[type="checkbox"] {
            cursor: pointer;
            accent-color: #3b82f6;
            width: 14px;
            height: 14px;
        }
        .toolbar-toggle.toggle-edit-wrap {
            background: rgba(234, 179, 8, 0.12);
            color: #fde047;
            border-color: rgba(234, 179, 8, 0.3);
        }
        .toolbar-toggle.toggle-edit-wrap:hover {
            background: rgba(234, 179, 8, 0.22);
            color: #fef08a;
        }
        .toolbar-toggle.toggle-edit-wrap input[type="checkbox"] {
            accent-color: #eab308;
        }

        /* Toolbar Action Buttons */
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-toolbar-close {
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border-color: rgba(255, 255, 255, 0.15);
        }
        .btn-toolbar-close:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .btn-toolbar-screenshot {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35);
        }
        .btn-toolbar-screenshot:hover {
            background: #047857;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.55);
            transform: translateY(-1px);
        }

        .btn-toolbar-print {
            background: #1e3a8a;
            color: #ffffff;
            border-color: #1e40af;
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.4);
        }
        .btn-toolbar-print:hover {
            background: #1e40af;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.6);
            transform: translateY(-1px);
        }

        /* ── SCREEN WRAPPER & A4 SHEET ── */
        .sheet-container {
            padding-top: 80px;
            padding-bottom: 40px;
            display: flex;
            justify-content: center;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        .sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 16mm 18mm 16mm 18mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            position: relative;
            color: #000000;
        }

        /* ── EDIT MODE STYLES ── */
        .edit-mode-active .editable-text {
            outline: 1.5px dashed #3b82f6 !important;
            background-color: rgba(239, 246, 255, 0.85) !important;
            cursor: text !important;
            border-radius: 3px;
            padding: 1px 4px;
            display: inline-block;
            min-width: 15px;
        }
        .edit-mode-active .editable-text:hover {
            background-color: rgba(219, 234, 254, 1) !important;
        }
        .edit-mode-active .editable-text:focus {
            outline: 2px solid #1e40af !important;
            background-color: #ffffff !important;
        }

        /* Hide signatures toggle */
        .hide-signatures .sig-image {
            display: none !important;
        }

        /* ── 1. HEADER (Surat Jalan - Nama Perusahaan) ── */
        .doc-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding-bottom: 4px;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.2px;
        }

        .company-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
            letter-spacing: -0.1px;
        }

        .header-divider {
            border-bottom: 1.5px solid #334155;
            margin-top: 2px;
            margin-bottom: 12px;
        }

        /* ── 2. METADATA SECTION ── */
        .meta-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 11px;
            line-height: 1.6;
        }

        .meta-col-left {
            width: 58%;
        }

        .meta-col-right {
            width: 38%;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 1.5px 0;
            color: #1e293b;
        }

        .meta-label {
            width: 80px;
            font-weight: 500;
            color: #334155;
        }

        .meta-colon {
            width: 14px;
            text-align: left;
            font-weight: 500;
        }

        .meta-value {
            font-weight: 600;
            color: #0f172a;
        }

        /* ── 3. ITEMS TABLE ── */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
            page-break-inside: auto;
        }

        /* Corporate ERP Blue header bar matching other reports */
        .table-items thead th {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            font-weight: 700;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #1e3a8a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-items thead th.col-jumlah,
        .table-items thead th.col-satuan {
            text-align: center;
        }

        /* Item rows */
        .item-row {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .item-row td {
            padding: 6px 8px;
            color: #1e293b;
            vertical-align: top;
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
        }

        .item-row td.col-jumlah {
            text-align: center;
        }

        .item-row td.col-satuan {
            text-align: center;
        }

        .item-row td.col-gudang {
            text-align: left;
        }

        /* ── 4. SPACER & BOTTOM SIGNATURES ── */
        .content-body {
            flex: 1; /* Pushes bottom signature to the lower section */
        }

        .signature-section {
            margin-top: auto;
            padding-top: 15px;
        }

        .signature-divider {
            border-top: 1.5px solid #334155;
            margin-bottom: 12px;
        }

        .signature-grid {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .signature-col {
            flex: 1;
            text-align: center;
            padding: 0 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sig-title {
            font-size: 10.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
            text-align: center;
            width: 100%;
        }

        .sig-box {
            height: 70px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .sig-image {
            max-height: 65px;
            max-width: 130px;
            object-fit: contain;
            filter: contrast(120%);
            margin: 0 auto;
            display: block;
        }

        .sig-line {
            width: 85%;
            border-bottom: 1.5px solid #334155;
            margin: 0 auto 6px auto;
        }

        .sig-name {
            font-size: 10px;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 95%;
            text-align: center;
            width: 100%;
        }

        /* ── RESPONSIVE SCREEN ADJUSTMENTS (MOBILE & TABLET PREVIEW) ── */
        @media screen and (max-width: 991.98px) {
            .toolbar-container {
                position: sticky;
                top: 0;
                height: auto;
                min-height: 56px;
                padding: 10px 14px;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .toolbar-info {
                justify-content: space-between;
                width: 100%;
            }
            .toolbar-actions {
                flex-wrap: wrap;
                width: 100%;
                gap: 6px;
            }
            .toolbar-toggle {
                padding: 5px 8px;
                font-size: 11px;
            }
            .btn-toolbar {
                padding: 6px 10px;
                font-size: 11px;
                flex: 1 1 auto;
                justify-content: center;
            }
            .sheet-container {
                padding-top: 15px;
                padding-left: 10px;
                padding-right: 10px;
                justify-content: flex-start;
            }
            .sheet {
                margin: 0 auto;
                flex-shrink: 0;
            }
        }

        @media screen and (max-width: 575.98px) {
            .toolbar-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
            .toolbar-actions .btn-toolbar-close {
                grid-column: span 2;
            }
        }

        /* ── HILANGKAN URL, TITLE, & HEADER/FOOTER BROWSER SAAT PRINT ── */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* ── PRINT MEDIA QUERY ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .toolbar-container {
                display: none !important;
            }

            .sheet-container {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
            }

            .sheet {
                width: 100% !important;
                min-height: 297mm !important;
                padding: 12mm 16mm 12mm 16mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: avoid;
            }

            .edit-mode-active .editable-text {
                outline: none !important;
                background-color: transparent !important;
                padding: 0 !important;
            }

            .table-items thead th {
                background-color: #1e3a8a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .signature-section {
                page-break-inside: avoid !important;
            }

            .header-divider,
            .signature-divider,
            .sig-line {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN FLOATING TOOLBAR -->
    <div class="toolbar-container">
        <div class="toolbar-info">
            <span class="toolbar-badge">Surat Jalan</span>
            <span style="font-weight: 600; font-size: 13px;">{{ $peminjaman->nomor_peminjaman }}</span>
            <span style="color: #94a3b8; font-size: 12px;">| {{ $peminjaman->status }}</span>
        </div>
        <div class="toolbar-actions">
            <!-- Toggle Tampilkan TTD -->
            <label class="toolbar-toggle" title="Tampilkan atau sembunyikan tanda tangan digital">
                <input type="checkbox" id="toggle-ttd" checked>
                <span>Tampilkan TTD</span>
            </label>

            <!-- Toggle Mode Edit -->
            <label class="toolbar-toggle toggle-edit-wrap" title="Aktifkan mode edit untuk mengubah teks langsung sebelum print / screenshot">
                <input type="checkbox" id="toggle-edit">
                <span>Mode Edit</span>
            </label>

            <!-- Tombol Screenshot -->
            <button type="button" id="btn-screenshot" class="btn-toolbar btn-toolbar-screenshot" title="Simpan sebagai gambar PNG">
                <i class="bi bi-camera-fill"></i> Screenshot
            </button>

            <!-- Tombol Cetak Dokumen -->
            <button type="button" onclick="doPrint()" class="btn-toolbar btn-toolbar-print" title="Cetak atau simpan PDF">
                <i class="bi bi-printer-fill"></i> Cetak Dokumen
            </button>

            <!-- Tombol Tutup -->
            <button type="button" onclick="window.close()" class="btn-toolbar btn-toolbar-close" title="Tutup jendela">
                <i class="bi bi-x-lg"></i> Tutup
            </button>
        </div>
    </div>

    <!-- SHEET PREVIEW WRAPPER -->
    <div class="sheet-container">
        <div class="sheet">
            <div class="content-body">
                <!-- 1. DOC HEADER -->
                <div class="doc-header">
                    <div class="doc-title editable-text">Surat Jalan</div>
                    <div class="company-name editable-text">
                        {{ $peminjaman->company->nama_perusahaan ?? 'PT. Persada Aman Sentosa' }}
                    </div>
                </div>
                <div class="header-divider"></div>

                <!-- 2. METADATA SECTION -->
                <div class="meta-grid">
                    <!-- Left Column -->
                    <div class="meta-col-left">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Nomor</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="editable-text">{{ $peminjaman->nomor_peminjaman }}</span></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tanggal</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value">
                                    <span class="editable-text">{{ $peminjaman->tanggal_pinjam ? date('d-M-y', strtotime($peminjaman->tanggal_pinjam)) : date('d-M-y') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="meta-label">Keterangan</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value">
                                    @php
                                        $ket = $peminjaman->keterangan ?: ($peminjaman->nama_acara . ($peminjaman->lokasi_acara ? ' (' . $peminjaman->lokasi_acara . ')' : ''));
                                    @endphp
                                    <span class="editable-text">{{ $ket ?: '-' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Right Column -->
                    <div class="meta-col-right">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label" style="width: 95px;">Gudang Asal</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="editable-text">{{ $peminjaman->gudangAsal->nama_gudang ?? 'Head Quarter' }}</span></td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="width: 95px;">Gudang Tujuan</td>
                                <td class="meta-colon">:</td>
                                <td class="meta-value"><span class="editable-text">{{ $peminjaman->gudangPameran->nama_gudang ?? 'Gudang Pameran' }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- 3. ITEMS TABLE -->
                <table class="table-items">
                    <thead>
                        <tr>
                            <th style="width: 18%;">Kode</th>
                            <th style="width: 44%;">Nama</th>
                            <th style="width: 12%;" class="col-jumlah">Jumlah</th>
                            <th style="width: 10%;" class="col-satuan">Satuan</th>
                            <th style="width: 16%;" class="col-gudang">Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjaman->details as $item)
                            @php
                                $qtyVal = (float)$item->qty_pinjam;
                                $qtyDisplay = ((int)$qtyVal == $qtyVal) ? number_format($qtyVal, 0, ',', '.') : number_format($qtyVal, 2, ',', '.');
                                $satuanNama = $item->satuan->nama_satuan ?? $item->barang?->satuan?->nama_satuan ?? 'Pcs';
                                $gudangNama = $peminjaman->gudangAsal->nama_gudang ?? 'Head Quarter';
                            @endphp
                            <tr class="item-row">
                                <td><span class="editable-text">{{ $item->barang?->kode_barang ?? '' }}</span></td>
                                <td><span class="editable-text">{{ $item->barang?->nama_barang ?? '-' }}</span></td>
                                <td class="col-jumlah"><span class="editable-text">{{ $qtyDisplay }}</span></td>
                                <td class="col-satuan"><span class="editable-text">{{ $satuanNama }}</span></td>
                                <td class="col-gudang"><span class="editable-text">{{ $gudangNama }}</span></td>
                            </tr>
                        @empty
                            <tr class="item-row">
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 16px;">
                                     Tidak ada item barang dalam dokumen ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 4. BOTTOM SIGNATURES SECTION -->
            <div class="signature-section">
                <div class="signature-divider"></div>
                <div class="signature-grid">
                    @php
                        // Helper to resolve signature URL/image
                        $resolveSignature = function($sig) {
                            if (empty($sig)) return null;
                            if (str_starts_with($sig, 'data:image') || str_starts_with($sig, 'http://') || str_starts_with($sig, 'https://')) {
                                return $sig;
                            }
                            return asset($sig);
                        };

                        $sigMengetahui = $resolveSignature($peminjaman->tanda_tangan_mengetahui) ?: $resolveSignature($peminjaman->pengetahu?->activeSignature?->signature_path);
                        $sigPengaju    = $resolveSignature($peminjaman->tanda_tangan_pengaju) ?: $resolveSignature($peminjaman->pengaju?->activeSignature?->signature_path);
                        $sigMenyiapkan = $resolveSignature($peminjaman->tanda_tangan_menyiapkan) ?: $resolveSignature($peminjaman->penyiap?->activeSignature?->signature_path);

                        $namaMengetahui = $peminjaman->pengetahu?->name ?? '...........................';
                        $namaPengaju    = $peminjaman->pengaju?->name ?? $peminjaman->penanggung_jawab ?? '...........................';
                        $namaMenyiapkan = $peminjaman->penyiap?->name ?? '...........................';
                    @endphp

                    <!-- 1. Diajukan Oleh -->
                    <div class="signature-col">
                        <div class="sig-title"><span class="editable-text">Diajukan Oleh,</span></div>
                        <div class="sig-box">
                            @if($sigPengaju)
                                <img src="{{ $sigPengaju }}" class="sig-image" alt="TTD Diajukan">
                            @endif
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-name"><span class="editable-text">{{ $namaPengaju }}</span></div>
                    </div>

                    <!-- 2. Diketahui Oleh -->
                    <div class="signature-col">
                        <div class="sig-title"><span class="editable-text">Diketahui Oleh,</span></div>
                        <div class="sig-box">
                            @if($sigMengetahui)
                                <img src="{{ $sigMengetahui }}" class="sig-image" alt="TTD Diketahui">
                            @endif
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-name"><span class="editable-text">{{ $namaMengetahui }}</span></div>
                    </div>

                    <!-- 3. Disiapkan Oleh -->
                    <div class="signature-col">
                        <div class="sig-title"><span class="editable-text">Disiapkan Oleh,</span></div>
                        <div class="sig-box">
                            @if($sigMenyiapkan)
                                <img src="{{ $sigMenyiapkan }}" class="sig-image" alt="TTD Disiapkan">
                            @endif
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-name"><span class="editable-text">{{ $namaMenyiapkan }}</span></div>
                    </div>

                    <!-- 4. Diperiksa Oleh -->
                    <div class="signature-col">
                        <div class="sig-title"><span class="editable-text">Diperiksa Oleh,</span></div>
                        <div class="sig-box">
                            {{-- Dikosongkan untuk tanda tangan fisik Security --}}
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-name"><span class="editable-text">Security</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Tampilkan/Sembunyikan Tanda Tangan
        const toggleTtd = document.getElementById('toggle-ttd');
        if (toggleTtd) {
            toggleTtd.addEventListener('change', function() {
                const sheet = document.querySelector('.sheet');
                if (this.checked) {
                    sheet.classList.remove('hide-signatures');
                } else {
                    sheet.classList.add('hide-signatures');
                }
            });
        }

        // Toggle Mode Edit
        const toggleEdit = document.getElementById('toggle-edit');
        if (toggleEdit) {
            toggleEdit.addEventListener('change', function() {
                const sheet = document.querySelector('.sheet');
                const editables = document.querySelectorAll('.editable-text');
                if (this.checked) {
                    sheet.classList.add('edit-mode-active');
                    editables.forEach(el => el.setAttribute('contenteditable', 'true'));
                } else {
                    sheet.classList.remove('edit-mode-active');
                    editables.forEach(el => el.removeAttribute('contenteditable'));
                }
            });
        }

        // Screenshot capture support
        const btnScreenshot = document.getElementById('btn-screenshot');
        if (btnScreenshot) {
            btnScreenshot.addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

                const sheet = document.querySelector('.sheet');
                const wasEdit = toggleEdit ? toggleEdit.checked : false;

                // Temporarily disable edit outline during capture
                if (wasEdit) {
                    sheet.classList.remove('edit-mode-active');
                }

                html2canvas(sheet, {
                    useCORS: true,
                    allowTaint: true,
                    scale: 2,
                    backgroundColor: '#ffffff',
                    imageTimeout: 15000,
                    logging: false
                }).then(canvas => {
                    const rawDocNo = @json($peminjaman->nomor_peminjaman) || 'Surat-Jalan';
                    const docNo = String(rawDocNo).replace(/[^a-zA-Z0-9_-]/g, '_');
                    const link = document.createElement('a');
                    link.download = 'Surat-Jalan-' + docNo + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    if (wasEdit) {
                        sheet.classList.add('edit-mode-active');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }).catch(err => {
                    console.error('Screenshot error:', err);
                    alert('Gagal mengambil screenshot.');
                    if (wasEdit) {
                        sheet.classList.add('edit-mode-active');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });
        }

        // Helper cetak dokumen
        function doPrint() {
            const sheet = document.querySelector('.sheet');
            const wasEdit = toggleEdit ? toggleEdit.checked : false;
            if (wasEdit) {
                sheet.classList.remove('edit-mode-active');
            }
            window.print();
            if (wasEdit) {
                setTimeout(() => sheet.classList.add('edit-mode-active'), 500);
            }
        }

        // Optional: Auto open print dialog if requested via ?autoprint=1
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('autoprint') === '1') {
                setTimeout(() => doPrint(), 400);
            }
        });
    </script>
</body>
</html>
