<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengeluaran Barang - {{ $goodsIssue->nomor_gi }}</title>
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/png">
    <link rel="shortcut icon" href="{{ $appLogoUrl }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    
    <style>
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #0284c7;
            --accent-blue: #2563eb;
            --light-blue: #f0f7ff;
            --border-blue: #bfdbfe;
            --dark-blue: #0f172a;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .print-container {
            max-width: 840px;
            margin: 16px auto 30px;
            background: #ffffff;
            padding: 26px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        /* ── Modern Floating Toolbar (No-Print) ── */
        .no-print-toolbar {
            max-width: 840px;
            margin: 14px auto 0;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.07);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            position: sticky;
            top: 10px;
            z-index: 1000;
        }

        /* ── Header Styling (Blue Theme) ── */
        .doc-header {
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .company-logo {
            max-height: 48px;
            max-width: 130px;
            object-fit: contain;
        }

        .company-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--primary-blue);
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .company-address {
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.35;
        }

        .doc-title-box {
            text-align: right;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }

        .doc-subtitle {
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .doc-number-badge {
            display: inline-block;
            background-color: var(--light-blue);
            color: var(--primary-blue);
            border: 1.5px solid var(--border-blue);
            font-family: var(--bs-font-monospace, monospace);
            font-size: 11.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            margin-top: 3px;
        }

        /* ── Status Badges ── */
        .status-badge-print {
            display: inline-block;
            font-size: 8.5px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .status-confirmed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-pending   { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .status-draft     { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

        /* ── Info Cards (Blue Tinted) ── */
        .info-card-blue {
            background-color: var(--light-blue);
            border: 1px solid var(--border-blue);
            border-radius: 10px;
            padding: 10px 14px;
            height: 100%;
        }

        .info-card-blue .info-label {
            font-size: 8.5px;
            font-weight: 700;
            color: var(--secondary-blue);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .info-card-blue .info-value {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .info-card-blue .info-sub {
            font-size: 9px;
            color: #64748b;
        }

        /* ── Table Styling (Blue Theme) ── */
        .table-gi-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .table-gi-print thead th {
            background-color: var(--primary-blue) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border: 1px solid var(--primary-blue);
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table-gi-print tbody td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 10px;
        }

        .table-gi-print tbody tr:nth-child(even) td {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table-gi-print tfoot td {
            background-color: var(--light-blue) !important;
            border: 1.5px solid var(--border-blue);
            padding: 6px 8px;
            font-weight: 700;
            font-size: 10.5px;
            color: var(--primary-blue);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Signature Section ── */
        .signature-section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .sig-box {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 8px;
            padding: 8px 6px;
            text-align: center;
            height: 125px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-title {
            font-size: 9px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }

        .box-ttd-img {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: auto;
            margin-bottom: 2px;
        }

        .box-ttd-img img {
            max-height: 46px;
            max-width: 120px;
            object-fit: contain;
        }

        .sig-name {
            font-weight: 700;
            font-size: 10px;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            margin-top: 0;
        }

        .editable-field {
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 1px 4px;
            cursor: text;
            display: inline-block;
            max-width: 100%;
            word-break: break-word;
        }

        .editable-field:hover {
            background-color: #fef9c3;
            outline: 1px dashed #ca8a04;
        }

        .editable-field:focus {
            background-color: #ffffff;
            outline: 2px solid var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.15);
        }

        /* ── Notes Box ── */
        .notes-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 9.5px;
        }

        /* ── Mobile Responsive Styles (Tampilan Layar HP) ── */
        @media (max-width: 768px) {
            body {
                padding: 0 6px 20px 6px;
            }
            .no-print-toolbar {
                margin: 8px 4px 12px 4px;
                padding: 10px 14px;
                border-radius: 16px;
                flex-direction: column;
                gap: 8px;
            }
            .toolbar-row-top {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
            }
            .toolbar-row-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .btn-toolbar-mobile {
                width: 100%;
                justify-content: center;
                padding: 7px 10px !important;
                font-size: 11px !important;
            }
            .print-container {
                margin: 0 auto;
                padding: 16px 14px;
                border-radius: 14px;
            }
            .doc-header {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .doc-title-box {
                text-align: left;
                border-top: 1px dashed #bfdbfe;
                padding-top: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 6px;
            }
            .doc-title {
                font-size: 13.5px;
            }
            .doc-subtitle {
                display: none;
            }
            .doc-number-badge {
                font-size: 11px;
                padding: 2px 7px;
            }
            .table-gi-print {
                font-size: 9.5px;
            }
            .table-gi-print thead th, 
            .table-gi-print tbody td {
                padding: 5px 6px;
            }
            .sig-box {
                height: 110px;
                padding: 6px 4px;
            }
            .sig-title {
                font-size: 8px;
                letter-spacing: 0;
            }
            .sig-name {
                font-size: 9.5px;
            }
            .box-ttd-img {
                height: 40px;
            }
            .box-ttd-img img {
                max-height: 38px;
            }
        }

        /* ── Print Media Queries (Hilangkan URL & Header Browser) ── */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            .no-print, .no-print-toolbar {
                display: none !important;
            }
            .editable-field {
                background: none !important;
                outline: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            body {
                background: #ffffff !important;
                font-size: 10px;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            html, body {
                margin: 0 !important;
                padding: 8mm 12mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Toolbar (No Print) --}}
    <div class="no-print-toolbar no-print">
        <div class="toolbar-row-top">
            <a href="javascript:window.close();" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> Tutup
            </a>

            {{-- Switch / Checkbox Pilih Mau Pake TTD atau Tidak --}}
            <div class="form-check form-switch d-inline-flex align-items-center gap-2 m-0 bg-white border px-3 py-1.5 rounded-pill shadow-xs">
                <input class="form-check-input mt-0 cursor-pointer" type="checkbox" role="switch" id="toggle-ttd" checked onchange="toggleSignatureVisibility(this.checked)">
                <label class="form-check-label small fw-bold text-dark user-select-none cursor-pointer" for="toggle-ttd" style="font-size: 11px;">
                    <i class="bi bi-pen me-1 text-primary"></i>Tanda Tangan Digital
                </label>
            </div>
        </div>

        <div class="toolbar-row-actions">
            {{-- Tombol Screenshot / Download Gambar --}}
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold shadow-xs btn-toolbar-mobile d-inline-flex align-items-center justify-content-center" id="btn-ss" onclick="takeScreenshot()">
                <i class="bi bi-camera-fill me-1"></i>SS / Simpan Gambar
            </button>

            {{-- Tombol Cetak / Print --}}
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm btn-toolbar-mobile d-inline-flex align-items-center justify-content-center" style="background-color: var(--primary-blue); border-color: var(--primary-blue);" onclick="doPrint()">
                <i class="bi bi-printer me-1"></i> Print / Cetak
            </button>
        </div>
    </div>

    <div class="print-container">
        @php
            $company = $goodsIssue->company ?: ($goodsIssue->gudang?->company ?: (session('active_company_id') ? \App\Models\Company::find(session('active_company_id')) : \App\Models\Company::first()));
            $branch  = $goodsIssue->branch ?: ($goodsIssue->gudang?->cabang ?: (session('active_branch_id') ? \App\Models\Branch::find(session('active_branch_id')) : null));
            
            $companyName = $company ? $company->nama_perusahaan : 'PT PERSADA AMAN SENTOSA';
            if ($branch) {
                // Jika transaksi di cabang, gunakan data cabang yang aktif (jika tidak ada maka kosongkan)
                $unitTitle   = strtoupper($branch->nama_cabang);
                $unitAddress = $branch->address ?? $branch->alamat ?? null;
                $unitPhone   = $branch->phone ?? $branch->telepon ?? null;
                $unitEmail   = $branch->email ?? null;
            } else {
                // Jika transaksi di PT (Pusat), gunakan data PT yang aktif (jika tidak ada maka kosongkan)
                $unitTitle   = strtoupper($companyName);
                $unitAddress = $company?->address ?? $company?->alamat ?? null;
                $unitPhone   = $company?->phone ?? $company?->telepon ?? null;
                $unitEmail   = $company?->email ?? null;
            }

            // Resolusi Logo:
            // - Jika transaksi di Cabang: hanya gunakan logo cabang jika cabang memiliki file logo. Jika tidak ada logo cabang, KOSONGKAN (null).
            // - Jika transaksi di PT (Pusat): hanya gunakan logo PT jika PT memiliki file logo. Jika tidak ada, KOSONGKAN (null).
            $logoUrl = null;
            if ($branch) {
                if (!empty($branch->logo) && file_exists(public_path('storage/' . $branch->logo))) {
                    $logoUrl = asset('storage/' . $branch->logo);
                } elseif (!empty($branch->logo) && file_exists(public_path($branch->logo))) {
                    $logoUrl = asset($branch->logo);
                }
            } else {
                if (!empty($company?->logo) && file_exists(public_path('storage/' . $company->logo))) {
                    $logoUrl = asset('storage/' . $company->logo);
                } elseif (!empty($company?->logo) && file_exists(public_path($company->logo))) {
                    $logoUrl = asset($company->logo);
                }
            }

            $creatorUser  = $goodsIssue->creator;
            $creatorName  = $creatorUser?->karyawan?->nama_lengkap ?? $creatorUser?->nama_lengkap ?? $creatorUser?->name ?? $creatorUser?->username ?? '-';
            $creatorSig   = $creatorUser?->activeSignature?->signature_url ?: $creatorUser?->activeSignature?->signature_base64;
            
            $approverUser = $goodsIssue->approver ?? $goodsIssue->updater;
            $approverName = $approverUser?->karyawan?->nama_lengkap ?? $approverUser?->nama_lengkap ?? $approverUser?->name ?? $approverUser?->username ?? '-';
            $approverSig  = ($goodsIssue->status === 'Confirmed') ? ($approverUser?->activeSignature?->signature_url ?: $approverUser?->activeSignature?->signature_base64) : null;
        @endphp

        {{-- ── 1. Document Header ── --}}
        <div class="doc-header d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center gap-3">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo" class="company-logo">
                @endif
                <div>
                    <div class="company-name">{{ $unitTitle }}</div>
                    @if(!empty($unitAddress) || !empty($unitPhone) || !empty($unitEmail))
                    <div class="company-address mt-1">
                        @if(!empty($unitAddress) && $unitAddress !== '-')
                            <div>{{ $unitAddress }}</div>
                        @endif
                        @if((!empty($unitPhone) && $unitPhone !== '-') || (!empty($unitEmail) && $unitEmail !== '-'))
                            <div>
                                @if(!empty($unitPhone) && $unitPhone !== '-') Telp: {{ $unitPhone }} @endif
                                @if(!empty($unitPhone) && $unitPhone !== '-' && !empty($unitEmail) && $unitEmail !== '-') | @endif
                                @if(!empty($unitEmail) && $unitEmail !== '-') Email: {{ $unitEmail }} @endif
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="doc-title-box">
                <div>
                    <div class="doc-title">BUKTI PENGELUARAN BARANG</div>
                    <div class="doc-subtitle">GOODS ISSUE SLIP</div>
                </div>
                <div class="doc-number-badge">{{ $goodsIssue->nomor_gi }}</div>
            </div>
        </div>

        {{-- ── 2. Document Information (Responsive Grid) ── --}}
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="info-card-blue">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-calendar3 me-1"></i>Tanggal Transaksi</div>
                            <div class="info-value">{{ $goodsIssue->tanggal_gi->format('d F Y') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-shield-check me-1"></i>Status Dokumen</div>
                            <div>
                                @if($goodsIssue->status === 'Confirmed')
                                    <span class="status-badge-print status-confirmed"><i class="bi bi-check-circle-fill me-1"></i>Confirmed / Disetujui</span>
                                @elseif($goodsIssue->status === 'Pending')
                                    <span class="status-badge-print status-pending"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>
                                @else
                                    <span class="status-badge-print status-draft"><i class="bi bi-pencil-square me-1"></i>Draft (Konsep)</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-house-door-fill me-1"></i>Gudang Pengeluar</div>
                            <div class="info-value">{{ $goodsIssue->gudang?->nama_gudang ?? '-' }}</div>
                            <div class="info-sub">{{ $goodsIssue->gudang?->kode_gudang ?? '' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-tag-fill me-1"></i>Tipe Pengeluaran</div>
                            <div class="info-value text-primary fw-bold">{{ $goodsIssue->tipe_pengeluaran }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="info-card-blue">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="info-label"><i class="bi bi-person-badge-fill me-1"></i>Tujuan / Penerima Barang</div>
                            <div class="info-value text-dark" style="font-size: 11.5px;">{{ $goodsIssue->tujuan }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-person-fill me-1"></i>Dibuat Oleh</div>
                            <div class="info-value">{{ $creatorName }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label"><i class="bi bi-patch-check-fill me-1"></i>Disetujui Oleh</div>
                            <div class="info-value text-success">
                                @if($goodsIssue->status === 'Confirmed')
                                    {{ $approverName }}
                                @else
                                    <span class="text-muted fst-italic">- Belum disetujui -</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. Table of Issued Items ── --}}
        <div class="table-responsive">
            <table class="table-gi-print">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 18%;" class="text-center">Kode Barang</th>
                        <th style="width: 47%;">Nama Barang</th>
                        <th style="width: 12%;" class="text-center">Satuan</th>
                        <th style="width: 18%;" class="text-end">Jumlah Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalQty = 0;
                    @endphp
                    @forelse($goodsIssue->details as $index => $detail)
                        @php
                            $qty = (float) $detail->jumlah;
                            $totalQty += $qty;
                            $barang = $detail->barang;
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                            <td class="text-center font-monospace fw-bold" style="color: var(--primary-blue);">{{ $barang?->kode_barang ?? '-' }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $barang?->nama_barang ?? '-' }}</div>
                            </td>
                            <td class="text-center fw-semibold text-muted">{{ $barang?->satuan?->nama_satuan ?? '-' }}</td>
                            <td class="text-end fw-bold font-monospace" style="font-size: 11px; color: #0f172a;">
                                {{ number_format($qty, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Tidak ada rincian barang</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end pe-2 text-uppercase" style="letter-spacing: 0.4px;">
                            TOTAL BARANG KELUAR:
                        </td>
                        <td class="text-end font-monospace" style="font-size: 11.5px; font-weight: 800;">
                            {{ number_format($totalQty, 0, ',', '.') }} Unit
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ── 4. Notes / Keterangan ── --}}
        @if($goodsIssue->keterangan)
        <div class="notes-box mb-2">
            <strong class="text-primary"><i class="bi bi-info-circle me-1"></i>Catatan:</strong>
            <span class="text-dark ms-1">{{ $goodsIssue->keterangan }}</span>
        </div>
        @endif

        {{-- ── 5. Signature Section (3 Columns) ── --}}
        <div class="signature-section">
            <div class="row g-2 g-md-3">
                <div class="col-4">
                    <div class="sig-box">
                        <div class="sig-title">Dikeluarkan Oleh</div>
                        <div class="box-ttd-img">
                            @if($creatorSig)
                                <img src="{{ $creatorSig }}" alt="TTD Pengeluar">
                            @endif
                        </div>
                        <div class="sig-name">
                            <span class="editable-field" contenteditable="true" title="Klik untuk edit nama">{{ $creatorName }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="sig-box">
                        <div class="sig-title">Disetujui Oleh</div>
                        <div class="box-ttd-img">
                            @if($approverSig)
                                <img src="{{ $approverSig }}" alt="TTD Approver">
                            @endif
                        </div>
                        <div class="sig-name">
                            <span class="editable-field" contenteditable="true" title="Klik untuk edit nama">
                                @if($goodsIssue->status === 'Confirmed')
                                    {{ $approverName }}
                                @else
                                    ( .................... )
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="sig-box">
                        <div class="sig-title">Diterima Oleh</div>
                        <div class="box-ttd-img">
                            {{-- Kosong untuk tanda tangan basah penerima --}}
                        </div>
                        <div class="sig-name">
                            <span class="editable-field" contenteditable="true" title="Klik untuk edit nama">{{ $goodsIssue->tujuan }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibilitas gambar tanda tangan digital
        function toggleSignatureVisibility(show) {
            const boxTtd = document.querySelectorAll('.box-ttd-img img');
            boxTtd.forEach(img => {
                img.style.display = show ? 'block' : 'none';
            });
        }

        // Ambil screenshot & simpan sebagai file gambar PNG (SS)
        function takeScreenshot() {
            const btn = document.getElementById('btn-ss');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';

            const targetElem = document.querySelector('.print-container');
            
            html2canvas(targetElem, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                const safeNomor = '{{ str_replace(['/', '\\'], '_', $goodsIssue->nomor_gi) }}';
                link.download = 'Bukti_Pengeluaran_Barang_' + safeNomor + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            }).catch(err => {
                console.error('Screenshot failed:', err);
                alert('Gagal mengambil screenshot gambar: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        // Print helper yang mengosongkan title sementara agar tidak muncul header URL browser
        function doPrint() {
            const originalTitle = document.title;
            document.title = ' ';
            window.print();
            setTimeout(() => {
                document.title = originalTitle;
            }, 500);
        }

        // Auto trigger print dialog if requested via ?print=1
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => doPrint(), 350);
            });
        }
    </script>
</body>
</html>
