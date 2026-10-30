<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Jurnal - {{ $jurnal->nomor_jurnal }} - {{ $entityName }}</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1e40af;
            --primary-dark: #1e3a8a;
            --accent-blue: #2563eb;
            --soft-blue: #eff6ff;
            --border-blue: #bfdbfe;
            --border-subtle: #dbeafe;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
            background: #f1f5f9;
            padding: 24px 16px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Top Action Bar (No Print) */
        .top-action-bar {
            max-width: 900px;
            margin: 0 auto 18px auto;
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-action-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .top-action-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .top-action-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action-close {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-action-close:hover {
            background: rgba(255, 255, 255, 0.28);
            color: #ffffff;
        }

        .btn-action-print {
            background: #ffffff;
            color: #1e40af;
            border: none;
            padding: 7px 22px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-print:hover {
            background: #f8faff;
            color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
        }

        /* Paper Document Container */
        .print-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 38px 42px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -5px rgba(30, 64, 175, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
        }

        .kop-entity-left {
            display: flex;
            align-items: center;
            gap: 16px;
            max-width: 58%;
        }

        .kop-logo-frame {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #ffffff;
            border: 1.5px solid var(--border-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.06);
            flex-shrink: 0;
        }

        .kop-logo-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .kop-entity-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.25;
            letter-spacing: -0.3px;
            margin-bottom: 3px;
        }

        .badge-entity {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--soft-blue);
            color: var(--primary-blue);
            border: 1px solid var(--border-blue);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .kop-entity-meta {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.4;
        }

        .kop-title-right {
            text-align: right;
            max-width: 40%;
        }

        .doc-title-text {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-blue);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .voucher-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--soft-blue);
            border: 1.5px solid var(--border-blue);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .voucher-pill-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .voucher-pill-num {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .voucher-date-line {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 6px;
            font-weight: 500;
        }

        /* Gradient Divider Line */
        .kop-divider {
            height: 3.5px;
            background: linear-gradient(90deg, #1e40af 0%, #3b82f6 55%, #bfdbfe 100%);
            border-radius: 3px;
            margin-bottom: 20px;
        }

        /* Info Transaksi Card */
        .info-card {
            background: linear-gradient(135deg, #f8faff 0%, #edf5ff 100%);
            border: 1px solid var(--border-subtle);
            border-left: 4.5px solid var(--accent-blue);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }

        .info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .info-val {
            font-size: 0.86rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.35;
        }

        .info-desc-box {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
        }

        /* Tabel Voucher Jurnal */
        .table-voucher-wrap {
            border: 1.5px solid var(--border-blue);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.04);
        }

        .table-voucher {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table-voucher thead th {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border: none;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-voucher tbody td {
            font-size: 0.84rem;
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .table-voucher tbody tr:nth-child(even) {
            background-color: #fcfdff;
        }

        .table-voucher tbody tr:hover {
            background-color: #f1f7ff;
        }

        .coa-badge-code {
            display: inline-block;
            background: #eff6ff;
            color: var(--primary-blue);
            border: 1px solid var(--border-blue);
            font-weight: 700;
            font-size: 0.82rem;
            padding: 2px 7px;
            border-radius: 5px;
        }

        .val-debit {
            color: #0369a1;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .val-kredit {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.88rem;
        }

        .table-voucher tfoot td {
            background: #eff6ff !important;
            border-top: 2px solid var(--accent-blue) !important;
            border-bottom: 3px double var(--primary-blue) !important;
            padding: 11px 12px;
            vertical-align: middle;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ==================== RESPONSIVE MOBILE STYLES ==================== */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px 8px 30px 8px;
                background: #0f172a;
            }
            .top-action-bar {
                padding: 10px 12px;
                flex-direction: row;
                gap: 8px;
                align-items: center;
                border-radius: 12px;
                margin-bottom: 12px;
            }
            .top-action-left {
                display: flex;
                align-items: center;
                gap: 8px;
                overflow: hidden;
                flex: 1 1 auto;
            }
            .top-action-title {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 0.88rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .top-action-right {
                flex-shrink: 0;
            }
            .btn-action-close {
                padding: 6px 12px;
                font-size: 12px;
            }
            .btn-action-print {
                padding: 6px 14px;
                font-size: 12px;
                white-space: nowrap;
            }
            .print-container {
                padding: 18px 14px;
                border-radius: 14px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            }
            .kop-surat {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                padding-bottom: 12px;
            }
            .kop-entity-left {
                max-width: 100% !important;
                width: 100%;
                gap: 12px;
            }
            .kop-logo-frame {
                width: 58px;
                height: 58px;
                border-radius: 10px;
                padding: 4px;
            }
            .kop-entity-name {
                font-size: 1.12rem;
                line-height: 1.25;
            }
            .kop-title-right {
                max-width: 100% !important;
                width: 100%;
                text-align: left;
            }
            .doc-title-text {
                font-size: 0.95rem;
                margin-bottom: 4px;
                letter-spacing: 0.5px;
            }
            .voucher-pill {
                width: 100%;
                justify-content: flex-start;
                padding: 4px 10px;
                font-size: 0.8rem;
            }
            .voucher-pill-num {
                font-size: 0.82rem;
            }
            .voucher-date-line {
                text-align: left;
                font-size: 0.76rem;
                margin-top: 4px;
            }
            .info-card {
                padding: 12px 14px;
                margin-bottom: 16px;
                border-left-width: 3.5px;
            }
            .info-label {
                font-size: 0.68rem;
                margin-bottom: 1px;
            }
            .info-val {
                font-size: 0.82rem;
                line-height: 1.3;
            }
            .signature-section .font-mono {
                font-size: 0.72rem !important;
            }
            .signature-section .text-muted {
                font-size: 0.68rem !important;
            }
        }

        /* ==================== PRINT SPECIFIC STYLES ==================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .kop-surat {
                flex-direction: row !important;
                align-items: flex-start !important;
            }
            .kop-entity-left {
                max-width: 58% !important;
            }
            .kop-title-right {
                max-width: 40% !important;
                text-align: right !important;
            }
            .info-card .col-md-4 {
                width: 33.333333% !important;
            }
            .table-voucher-wrap,
            .table-responsive {
                overflow: visible !important;
                border: none !important;
            }
            .table-voucher {
                min-width: 100% !important;
                width: 100% !important;
            }
            .table-voucher thead th {
                background: #1e40af !important;
                color: #ffffff !important;
            }
            .table-voucher tfoot td {
                background: #eff6ff !important;
            }
            .info-card {
                background: #f8faff !important;
                border: 1px solid #cbd5e1 !important;
                border-left: 4.5px solid #1e40af !important;
            }
            [contenteditable="true"] {
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }

        /* Editable Signature Fields on Screen */
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
    </style>
</head>
<body>

    <!-- Top Action Bar (No Print) -->
    <div class="top-action-bar no-print">
        <div class="top-action-left">
            <button onclick="window.close()" class="btn-action-close">
                <i class="bi bi-arrow-left"></i> Tutup
            </button>
            <div class="top-action-title">
                <i class="bi bi-file-earmark-check fs-5"></i>
                <span class="d-none d-sm-inline">Pratinjau Voucher Jurnal Akuntansi</span>
                <span class="d-inline d-sm-none">Voucher Jurnal</span>
            </div>
            <span class="badge bg-light text-secondary border font-mono d-none d-md-inline-flex align-items-center gap-1.5 px-3 py-1 ms-2" style="font-size: 10px;">
                <i class="bi bi-pencil-square text-primary"></i> Tanda Tangan Dapat Diedit Langsung (Klik Teks)
            </span>
        </div>
        <div class="top-action-right">
            <button onclick="window.print()" class="btn-action-print">
                <i class="bi bi-printer-fill"></i> <span class="d-none d-sm-inline">Cetak Dokumen (PDF)</span><span class="d-inline d-sm-none">Cetak</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Voucher -->
    <div class="print-container">
        
        <!-- Header Kop Surat Resmi -->
        <div class="kop-surat">
            <!-- Left: Logo & Dynamic Entity (PT / Cabang) -->
            <div class="kop-entity-left">
                <div class="kop-logo-frame">
                    @if(!empty($entityLogo))
                        <img src="{{ $entityLogo }}" alt="Logo {{ $entityName }}" class="kop-logo-img">
                    @else
                        <i class="bi {{ $isBranch ? 'bi-shop' : 'bi-building' }} fs-2 text-primary"></i>
                    @endif
                </div>
                <div>
                    <h1 class="kop-entity-name mb-0">{{ $entityName }}</h1>
                </div>
            </div>

            <!-- Right: Document Title & Metadata -->
            <div class="kop-title-right">
                <div class="doc-title-text">BUKTI MEMORIAL / VOUCHER JURNAL</div>
                <div class="voucher-pill">
                    <span class="voucher-pill-label">No. Voucher:</span>
                    <span class="voucher-pill-num font-mono">{{ $jurnal->nomor_jurnal }}</span>
                </div>
                <div class="voucher-date-line">
                    <i class="bi bi-calendar3 text-primary me-1"></i>Tanggal: 
                    <strong class="font-mono text-dark">{{ date('d F Y', strtotime($jurnal->tanggal_jurnal)) }}</strong>
                </div>
            </div>
        </div>

        <!-- Gradient Accent Divider -->
        <div class="kop-divider"></div>

        <!-- Info Transaksi Card -->
        <div class="info-card">
            <div class="row g-2">
                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0">
                    <div class="info-label">Sumber Transaksi</div>
                    <div class="info-val d-flex align-items-center gap-1.5">
                        <span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 0.78rem; font-weight: 700; padding: 4px 9px;">
                            <i class="bi bi-tag-fill me-1"></i>{{ $jurnal->referensi_tipe ?: 'Manual (Penyesuaian)' }}
                        </span>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0">
                    <div class="info-label">No. Referensi / Dokumen</div>
                    <div class="info-val font-mono">
                        @if($refData && isset($refData->nomor_do))
                            <span class="text-primary">{{ $refData->nomor_do }}</span>
                            @if($refData->salesOrder)
                                <span class="text-muted small fw-normal"> (SO: {{ $refData->salesOrder->nomor_so }})</span>
                            @endif
                        @elseif($refData && isset($refData->nomor_so))
                            <span class="text-primary">{{ $refData->nomor_so }}</span>
                        @elseif($refData && isset($refData->nomor_faktur))
                            <span class="text-primary">{{ $refData->nomor_faktur }}</span>
                        @elseif($refData && isset($refData->nomor_pembayaran))
                            <span class="text-primary">{{ $refData->nomor_pembayaran }}</span>
                        @elseif($refData && isset($refData->nomor_spk))
                            <span class="text-primary">{{ $refData->nomor_spk }}</span>
                        @else
                            {{ $jurnal->referensi_id ? '#' . $jurnal->referensi_id : '-' }}
                        @endif
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0">
                    <div class="info-label">Dibuat Oleh</div>
                    <div class="info-val">
                        <i class="bi bi-person-circle text-primary me-1"></i>
                        {{ $jurnal->creator ? ($jurnal->creator->nama_lengkap ?: $jurnal->creator->name) : 'System Accounting' }}
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0 mt-md-2">
                    <div class="info-label">Unit Pembukuan</div>
                    <div class="info-val">
                        @if($isBranch)
                            <i class="bi bi-shop text-primary me-1"></i>{{ $entityName }} ({{ $entityCode }})
                        @else
                            <i class="bi bi-building text-primary me-1"></i>{{ $entityName }}
                        @endif
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0 mt-md-2">
                    <div class="info-label">No. Tender / Proyek</div>
                    <div class="info-val font-mono">
                        {{ $jurnal->no_tender ?: '-' }}
                    </div>
                </div>

                @if($refData && $refData->customer)
                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0 mt-md-2">
                    <div class="info-label">Customer / Instansi</div>
                    <div class="info-val text-truncate" title="{{ $refData->customer->nama_perusahaan ?: $refData->customer->nama_customer }}">
                        <i class="bi bi-person-vcard text-primary me-1"></i>
                        {{ $refData->customer->nama_perusahaan ?: $refData->customer->nama_customer }}
                    </div>
                </div>
                @else
                <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0 mt-md-2">
                    <div class="info-label">Status Posting</div>
                    <div class="info-val">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.75rem;">
                            <i class="bi bi-check2-all me-1"></i>Posted / Terbuku
                        </span>
                    </div>
                </div>
                @endif

                <div class="col-12 info-desc-box">
                    <div class="info-label">Keterangan / Uraian Transaksi:</div>
                    <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.88rem; line-height: 1.45;">
                        {{ $jurnal->keterangan ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Swipe Hint Banner untuk Mobile -->
        <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2 rounded-3 no-print" style="background: rgba(37, 99, 235, 0.08); border: 1px dashed rgba(37, 99, 235, 0.3); color: #1e40af; font-size: 0.76rem;">
            <span><i class="bi bi-arrow-left-right me-1"></i> Geser tabel voucher ke samping</span>
            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Swipe &rarr;</span>
        </div>

        <!-- Tabel Baris Jurnal Akuntansi -->
        <div class="table-voucher-wrap mb-0">
            <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                <table class="table table-voucher" style="min-width: 620px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">NO</th>
                            <th style="width: 120px;" class="text-center">KODE AKUN</th>
                            <th>NAMA AKUN COA &amp; DESKRIPSI BARIS</th>
                            <th class="text-end text-nowrap" style="width: 175px;">DEBIT (RP)</th>
                            <th class="text-end text-nowrap" style="width: 175px;">KREDIT (RP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jurnal->details as $idx => $d)
                        <tr>
                            <td class="text-center font-mono text-muted" style="font-size: 0.82rem;">{{ $idx + 1 }}</td>
                            <td class="text-center font-mono">
                                <span class="coa-badge-code">{{ $d->account ? $d->account->code : '-' }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $d->account ? $d->account->name : 'Akun Tidak Ditemukan' }}</div>
                                @if(!empty($d->keterangan) && $d->keterangan !== $jurnal->keterangan)
                                    <div class="text-secondary small mt-0.5 fst-italic">
                                        <i class="bi bi-arrow-return-right text-primary me-1"></i>{{ $d->keterangan }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end font-mono text-nowrap">
                                @if($d->debit > 0)
                                    <span class="val-debit">Rp {{ number_format($d->debit, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-muted opacity-50">-</span>
                                @endif
                            </td>
                            <td class="text-end font-mono text-nowrap">
                                @if($d->kredit > 0)
                                    <span class="val-kredit">Rp {{ number_format($d->kredit, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-muted opacity-50">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <strong class="text-uppercase" style="color: var(--primary-dark); font-size: 0.82rem; letter-spacing: 0.5px;">
                                        TOTAL NILAI VOUCHER:
                                    </strong>
                                    @php
                                        $isBalance = abs($jurnal->total_debit - $jurnal->total_kredit) < 0.01;
                                    @endphp
                                    @if($isBalance)
                                        <span class="badge" style="background: #10b981; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 4px 10px;">
                                            <i class="bi bi-check-circle-fill me-1"></i>SEIMBANG (BALANCE)
                                        </span>
                                    @else
                                        <span class="badge" style="background: #ef4444; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 4px 10px;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>TIDAK SEIMBANG (UNBALANCED)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end font-mono text-nowrap">
                                <strong class="val-debit" style="font-size: 0.94rem;">Rp {{ number_format($jurnal->total_debit, 2, ',', '.') }}</strong>
                            </td>
                            <td class="text-end font-mono text-nowrap">
                                <strong class="val-kredit" style="font-size: 0.94rem;">Rp {{ number_format($jurnal->total_kredit, 2, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Lembar Tanda Tangan Otorisasi Voucher Jurnal (Dapat Diedit Langsung) -->
        <div class="row text-center signature-section mt-4 pt-3" style="page-break-inside: avoid; break-inside: avoid;">
            <div class="col-4 d-flex flex-column align-items-center justify-content-between">
                <div class="text-muted small" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks otorisasi" style="font-size: 0.82rem;">Dibuat Oleh:</div>
                <div style="width: 80%; max-width: 165px; border-bottom: 1.5px solid #334155; margin: 45px auto 6px auto;"></div>
                <div class="fw-bold text-dark font-mono text-truncate w-100" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 0.85rem;">
                    ( {{ $jurnal->creator ? ($jurnal->creator->nama_lengkap ?: $jurnal->creator->name) : 'Staff Accounting' }} )
                </div>
                <div class="text-muted small mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 0.75rem;">Staff Pembukuan</div>
            </div>
            <div class="col-4 d-flex flex-column align-items-center justify-content-between">
                <div class="text-muted small" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks otorisasi" style="font-size: 0.82rem;">Diperiksa Oleh:</div>
                <div style="width: 80%; max-width: 165px; border-bottom: 1.5px solid #334155; margin: 45px auto 6px auto;"></div>
                <div class="fw-bold text-dark font-mono text-truncate w-100" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 0.85rem;">( Accounting Manager )</div>
                <div class="text-muted small mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 0.75rem;">Manager Keuangan</div>
            </div>
            <div class="col-4 d-flex flex-column align-items-center justify-content-between">
                <div class="text-muted small" contenteditable="true" spellcheck="false" title="Klik untuk mengedit teks otorisasi" style="font-size: 0.82rem;">Disetujui Oleh:</div>
                <div style="width: 80%; max-width: 165px; border-bottom: 1.5px solid #334155; margin: 45px auto 6px auto;"></div>
                <div class="fw-bold text-dark font-mono text-truncate w-100" contenteditable="true" spellcheck="false" title="Klik untuk mengedit nama" style="font-size: 0.85rem;">( Direktur Keuangan )</div>
                <div class="text-muted small mt-0.5" contenteditable="true" spellcheck="false" title="Klik untuk mengedit jabatan" style="font-size: 0.75rem;">Direksi Perusahaan</div>
            </div>
        </div>

    </div>

</body>
</html>
