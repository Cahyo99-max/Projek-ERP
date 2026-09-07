<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Produksi BOM - {{ $bom->kode_bom }}</title>
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/png">
    <link rel="shortcut icon" href="{{ $appLogoUrl }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, Roboto, Arial, sans-serif;
            font-size: 11px;
            color: #0f172a;
            background: #f1f5f9;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Screen Preview Paper Sheet */
        .print-paper-sheet {
            max-width: 900px;
            margin: 20px auto 40px auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 12px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.1), 0 0 1px rgba(15, 23, 42, 0.15);
        }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0284c7;
        }
        .header-sub {
            font-size: 11.5px;
            color: #64748b;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .table-print th {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            text-align: center;
            font-weight: 700;
            padding: 8px 8px;
            border: 1px solid #1e3a8a;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.3px;
        }
        .table-print td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-print tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge-print {
            font-size: 9.5px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            display: inline-block;
        }
        .editable-field {
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 1px 4px;
        }
        .editable-field:hover {
            background-color: #fef9c3;
            outline: 1px dashed #ca8a04;
            cursor: text;
        }
        .editable-field:focus {
            background-color: #ffffff;
            outline: 2px solid #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
        }

        /* Mobile Screen Optimizations */
        @media screen and (max-width: 768px) {
            .print-paper-sheet {
                margin: 10px 8px 30px 8px;
                padding: 18px 14px;
                border-radius: 10px;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
            }
            .header-title {
                font-size: 15px;
            }
            .header-sub {
                font-size: 10.5px;
            }
            .table-responsive-mobile {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin-bottom: 1rem;
            }
            .table-responsive-mobile .table-print {
                min-width: 620px;
            }
            .no-print-bar {
                padding: 12px !important;
            }
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            .no-print, .no-print-bar { display: none !important; }
            .editable-field {
                background: none !important;
                outline: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            body {
                background: #ffffff !important;
            }
            .print-paper-sheet {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 8mm 10mm !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #ffffff !important;
            }
            .table-responsive-mobile {
                overflow: visible !important;
            }
            .table-responsive-mobile .table-print {
                min-width: 100% !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="checkAutoPrintBom()">

    <!-- NO PRINT ACTION BAR (MOBILE & DESKTOP OPTIMIZED) -->
    <div class="no-print no-print-bar sticky-top p-2.5 p-md-3 bg-dark text-white border-bottom shadow-md" style="background: rgba(15, 23, 42, 0.96) !important; backdrop-filter: blur(12px); z-index: 1050;">
        <div class="container-fluid px-1 px-md-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2.5">
                
                {{-- Left: Info & Switches --}}
                <div class="d-flex flex-column flex-sm-row flex-wrap align-items-start align-items-sm-center gap-2 gap-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary bg-opacity-20 p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-printer-fill text-info fs-6"></i>
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size: 13px;">Pratinjau Cetak BOM</strong>
                            <small class="text-white-50 font-monospace" style="font-size: 11px;">{{ $bom->kode_bom }}</small>
                        </div>
                    </div>

                    {{-- Toggle Switches --}}
                    <div class="d-flex align-items-center gap-2.5 flex-wrap ps-sm-3 border-sm-start" style="border-color: rgba(255,255,255,0.15) !important;">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="chkShowFoto" checked onchange="togglePdfSection('col-foto', this.checked)">
                            <label class="form-check-label small text-white cursor-pointer" for="chkShowFoto" style="font-size: 11px;"><i class="bi bi-image me-1 text-info"></i>Foto</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="chkShowSignatureImg" checked onchange="togglePdfSection('box-ttd-img', this.checked)">
                            <label class="form-check-label small text-white cursor-pointer" for="chkShowSignatureImg" style="font-size: 11px;"><i class="bi bi-vector-pen me-1 text-info"></i>TTD</label>
                        </div>
                        @if($canSeeHpp ?? true)
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="chkShowHpp" checked onchange="togglePdfSection('col-hpp', this.checked)">
                            <label class="form-check-label small text-white cursor-pointer" for="chkShowHpp" style="font-size: 11px;"><i class="bi bi-cash-stack me-1 text-info"></i>HPP</label>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap">
                    <button onclick="downloadPdfScreenshot()" type="button" class="btn btn-outline-info btn-sm px-3 py-1.5 rounded-pill fw-bold text-white btn-download-ss flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5" style="font-size: 12px;">
                        <i class="bi bi-camera"></i> <span>Simpan Gambar</span>
                    </button>
                    <button onclick="window.print()" type="button" class="btn btn-primary btn-sm px-3.5 py-1.5 rounded-pill fw-bold flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="font-size: 12px; background: linear-gradient(135deg, #2563eb, #0284c7);">
                        <i class="bi bi-printer"></i> <span>Cetak / PDF</span>
                    </button>
                    <button onclick="window.close()" type="button" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-pill text-white flex-fill flex-sm-grow-0" style="font-size: 12px;">
                        ✕ Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    @php
        // ── Penentuan Entitas Aktif (PT Pusat vs Cabang) ──
        $activeBranchId  = $bom->branch_id ?: session('active_branch_id');
        $activeCompanyId = $bom->company_id ?: session('active_company_id');

        $activeBranch  = $bom->branch ?: ($activeBranchId ? \App\Models\Branch::find($activeBranchId) : null);
        $activeCompany = $bom->company ?: ($activeCompanyId ? \App\Models\Company::find($activeCompanyId) : null);

        $isBranchActive = !empty($activeBranch);

        if ($isBranchActive) {
            $entityTitle = $activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang Aktif');
        } else {
            $entityTitle = $activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT Persada Aman Sentosa');
        }
    @endphp

    <!-- MAIN PAPER SHEET CONTAINER -->
    <div class="print-paper-sheet">
        
        <!-- HEADER KOP SURAT PERUSAHAAN / CABANG -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-2.5 mb-3 gap-2">
            <div>
                <div class="header-title">{{ $entityTitle }}</div>
            </div>
            <div class="text-start text-sm-end">
                <div class="header-title text-dark">RESEP PRODUKSI (BOM)</div>
                <div class="header-sub">No: <strong class="text-primary font-monospace">{{ $bom->kode_bom }}</strong></div>
            </div>
        </div>

        <!-- INFO RINGKASAN BOM -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-8">
                <div class="info-card h-100">
                    <div class="row g-2">
                        <div class="col-12">
                            <strong class="text-secondary">Nama Resep BOM:</strong> <span class="fw-bold text-primary fs-6 d-block d-sm-inline">{{ $bom->nama_bom }}</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <strong class="text-secondary">Produk Barang Jadi:</strong> <span class="fw-semibold text-dark d-block">{{ $bom->barangJadi ? ('[' . $bom->barangJadi->kode_barang . '] ' . $bom->barangJadi->nama_barang) : '-' }}</span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <strong class="text-secondary">Target Hasil Produksi:</strong> 
                            @php
                                $satuanHasil = $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : ($bom->barangJadi && $bom->barangJadi->satuan ? $bom->barangJadi->satuan->nama_satuan : 'Pcs');
                                $qtyHasil = number_format($bom->jumlah_hasil, str_contains((string)$bom->jumlah_hasil, '.') ? 4 : 0, ',', '.');
                                $qtyHasilClean = str_contains($qtyHasil, ',') ? rtrim(rtrim($qtyHasil, '0'), ',') : $qtyHasil;
                            @endphp
                            <span class="fw-bold text-dark fs-6 d-block">{{ $qtyHasilClean }} {{ $satuanHasil }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="info-card bg-light border h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary-subtle">
                        <span class="fw-semibold text-secondary" style="font-size: 10px;">STATUS RESEP:</span>
                        @if($bom->status === 'Draft')
                            <span class="badge bg-secondary text-white px-2 py-0.5 fw-bold" style="font-size: 10px;">Draft</span>
                        @elseif($bom->status === 'Pending')
                            <span class="badge bg-warning text-dark px-2 py-0.5 fw-bold" style="font-size: 10px;">Pending Approval</span>
                        @elseif($bom->status === 'Approved' || $bom->status === 'Active')
                            <span class="badge bg-success text-white px-2 py-0.5 fw-bold" style="font-size: 10px;">Approved</span>
                        @elseif($bom->status === 'Rejected')
                            <span class="badge bg-danger text-white px-2 py-0.5 fw-bold" style="font-size: 10px;">Rejected</span>
                        @else
                            <span class="badge bg-secondary text-white px-2 py-0.5 fw-bold" style="font-size: 10px;">{{ $bom->status }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary-subtle">
                        <span class="fw-semibold text-secondary" style="font-size: 10px;">PENYUSUN:</span>
                        <strong class="text-dark text-truncate" style="font-size: 11px; max-width: 130px;">{{ $bom->creator ? ($bom->creator->nama_lengkap ?: $bom->creator->name) : '-' }}</strong>
                    </div>
                    @if($bom->approver || in_array($bom->status, ['Approved', 'Active', 'Completed', 'Selesai']))
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary-subtle">
                        <span class="fw-semibold text-secondary" style="font-size: 10px;">DISETUJUI OLEH:</span>
                        <strong class="text-dark text-truncate" style="font-size: 11px; max-width: 130px;">{{ $bom->approver ? ($bom->approver->nama_lengkap ?: $bom->approver->name) : ($bom->updater ? ($bom->updater->nama_lengkap ?: $bom->updater->name) : 'Supervisor') }}</strong>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-secondary" style="font-size: 10px;">WAKTU CETAK:</span>
                        <span class="text-dark font-monospace" style="font-size: 10.5px;">{{ date('d/m/Y H:i') }} WIB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL DETAIL KOMPOSISI BAHAN BAKU BOM (RESPONSIVE TOUCH SWIPE ON MOBILE SCREEN) -->
        <div class="table-responsive-mobile">
            <table class="table-print mb-3">
                <thead>
                    <tr>
                        <th style="width: 35px;">NO</th>
                        <th class="col-foto" style="width: 45px;">FOTO</th>
                        <th style="width: 120px;">KODE BAHAN</th>
                        <th>NAMA BAHAN BAKU / AKSESORIS</th>
                        <th style="width: 80px;" class="text-center">QTY NETTO</th>
                        <th style="width: 70px;" class="text-center">WASTE (%)</th>
                        <th style="width: 75px;" class="text-center">SATUAN</th>
                        @if($canSeeHpp ?? true)
                        <th class="col-hpp text-end" style="width: 120px;">ESTIMASI HPP (RP)</th>
                        <th class="col-hpp text-end" style="width: 135px;">SUBTOTAL (RP)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        if (!function_exists('formatMoneyCleanBom')) {
                            function formatMoneyCleanBom($val): string {
                                if ($val === null || $val === '') return '0';
                                $num = (float)$val;
                                return number_format($num, 0, ',', '.');
                            }
                        }

                        if (!function_exists('formatQtyCleanBom')) {
                            function formatQtyCleanBom($val): string {
                                if ($val === null || $val === '') return '0';
                                $num = (float)$val;
                                $str = number_format($num, 4, ',', '.');
                                return str_contains($str, ',') ? rtrim(rtrim($str, '0'), ',') : $str;
                            }
                        }

                        $totalQtyNetto = 0;
                        $totalSubtotalBahan = 0;
                    @endphp

                    @forelse($bom->items as $idx => $item)
                        @php
                            $bm = $item->barangMentah;
                            $fotoPath = $bm && $bm->primaryFoto ? $bm->primaryFoto->foto_path : null;
                            $fotoUrl = $fotoPath ? asset('storage/' . $fotoPath) : null;
                            $satuanNama = $item->satuan ? $item->satuan->nama_satuan : ($bm && $bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');

                            $qty = (float)$item->jumlah_kebutuhan;
                            $waste = (float)$item->waste_percent;
                            $hpp = (float)$item->estimasi_harga_satuan;
                            $grossQty = $qty * (1 + ($waste / 100));
                            $subtotal = $grossQty * $hpp;

                            $totalQtyNetto += $qty;
                            $totalSubtotalBahan += $subtotal;
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-secondary">{{ $idx + 1 }}</td>
                            <td class="text-center col-foto p-1">
                                @if($fotoUrl)
                                    <img src="{{ $fotoUrl }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;">
                                @else
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" style="width: 32px; height: 32px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 10px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="font-monospace fw-bold text-dark">{{ $bm ? $bm->kode_barang : '-' }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $bm ? $bm->nama_barang : '-' }}</div>
                                @if($item->catatan)
                                    <small class="text-muted fst-italic" style="font-size: 9px;">* {{ $item->catatan }}</small>
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold">{{ formatQtyCleanBom($qty) }}</td>
                            <td class="text-center font-monospace">{{ $waste > 0 ? (rtrim(rtrim(number_format($waste, 2, ',', '.'), '0'), ',') . '%') : '-' }}</td>
                            <td class="text-center font-monospace text-secondary">{{ $satuanNama }}</td>
                            @if($canSeeHpp ?? true)
                            <td class="col-hpp text-end font-monospace">{{ formatMoneyCleanBom($hpp) }}</td>
                            <td class="col-hpp text-end font-monospace fw-bold text-dark">{{ formatMoneyCleanBom($subtotal) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">Belum ada komposisi bahan baku yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background-color: #f1f5f9; border-top: 2px solid #1e3a8a;">
                        <td colspan="7" class="text-end text-dark pe-3" id="tdTotalLabel">
                            TOTAL ESTIMASI BAHAN BAKU:
                        </td>
                        @if($canSeeHpp ?? true)
                        <td colspan="2" class="col-hpp text-end font-monospace fs-6 text-primary pe-2">
                            Rp {{ formatMoneyCleanBom($totalSubtotalBahan) }}
                        </td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BIAYA OPERASIONAL & TOTAL ESTIMASI HPP -->
        <div class="row g-2 mb-4">
            <div class="col-12 col-md-7">
                <div class="border rounded p-2.5 h-100" style="background-color: #f8fafc; font-size: 10px;">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle me-1 text-primary"></i> Catatan Spesifikasi Produksi:</div>
                    <div class="text-secondary" style="min-height: 40px; line-height: 1.4;">
                        {{ $bom->keterangan ?: 'Resep BOM standar untuk proses garmen & konveksi. Perubahan komposisi bahan baku wajib melalui persetujuan Supervisor / Manager Produksi.' }}
                    </div>
                </div>
            </div>
            @if($canSeeHpp ?? true)
            <div class="col-12 col-md-5 col-hpp">
                <table class="table-print">
                    <tr>
                        <td class="fw-semibold text-secondary" style="width: 55%;">BIAYA TENAGA KERJA (CMT):</td>
                        <td class="text-end font-monospace text-dark">Rp {{ formatMoneyCleanBom($bom->biaya_tenaga_kerja) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">BIAYA OVERHEAD PABRIK:</td>
                        <td class="text-end font-monospace text-dark">Rp {{ formatMoneyCleanBom($bom->biaya_overhead) }}</td>
                    </tr>
                    <tr style="background-color: #e0f2fe; border-top: 2px solid #0284c7;">
                        <td class="fw-bold text-primary">ESTIMASI TOTAL HPP / SATUAN:</td>
                        <td class="text-end font-monospace fw-bold text-primary" style="font-size: 12px;">
                            Rp {{ formatMoneyCleanBom($bom->total_estimasi_hpp) }}
                        </td>
                    </tr>
                </table>
            </div>
            @endif
        </div>

        @php
            if (!function_exists('resolveSignatureUrlBom')) {
                function resolveSignatureUrlBom($path): ?string {
                    if (!$path) return null;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image')) {
                        return $path;
                    }
                    if (file_exists(public_path($path))) {
                        return asset($path);
                    }
                    if (file_exists(public_path('storage/' . $path))) {
                        return asset('storage/' . $path);
                    }
                    return asset('storage/' . $path);
                }
            }

            // 1. Creator / Penyusun (Disusun Oleh)
            $creatorName    = $bom->creator ? ($bom->creator->nama_lengkap ?: $bom->creator->name) : 'Staff Produksi';
            $creatorPos     = $bom->creator && $bom->creator->position ? ($bom->creator->position->position_name ?: $bom->creator->position->nama_jabatan) : null;
            $creatorRole    = $bom->creator && $bom->creator->roles->first() ? $bom->creator->roles->first()->name : null;
            $creatorJabatan = $creatorPos ?: ($creatorRole ?: 'Kabag Produksi');

            $creatorSig     = $bom->creator && $bom->creator->activeSignature ? $bom->creator->activeSignature->signature_path : null;
            $creatorSigUrl  = resolveSignatureUrlBom($creatorSig);

            // 2. Approver / Penyetuju (Disetujui Oleh)
            $approverUser   = $bom->approver ?: $bom->updater;
            $approverName    = $approverUser ? ($approverUser->nama_lengkap ?: $approverUser->name) : 'Manager Produksi';
            $approverPos     = $approverUser && $approverUser->position ? ($approverUser->position->position_name ?: $approverUser->position->nama_jabatan) : null;
            $approverRole    = $approverUser && $approverUser->roles->first() ? $approverUser->roles->first()->name : null;
            $approverJabatan = $approverPos ?: ($approverRole ?: 'Factory Manager');

            $approverSig     = $approverUser && $approverUser->activeSignature ? $approverUser->activeSignature->signature_path : null;
            $approverSigUrl  = resolveSignatureUrlBom($approverSig);
        @endphp

        <!-- KOLOM TANDA TANGAN (2 TTD: DISUSUN OLEH & DISETUJUI OLEH) -->
        <div class="row justify-content-between text-center mt-4 mt-md-5 section-signature" style="page-break-inside: avoid;">
            <div class="col-6 col-md-5">
                <div class="small text-muted fw-bold mb-2">DISUSUN OLEH,</div>
                <div class="box-ttd-img d-flex align-items-center justify-content-center mb-1" style="height: 60px;">
                    @if($creatorSigUrl)
                        <img src="{{ $creatorSigUrl }}" style="max-height: 55px; max-width: 150px; object-fit: contain;">
                    @endif
                </div>
                <div class="fw-bold text-dark border-bottom d-inline-block pb-1 editable-field" contenteditable="true" title="Klik untuk mengedit nama penyusun" style="min-width: 130px; max-width: 100%;">
                    {{ $creatorName }}
                </div>
                <div class="small text-muted mt-1 editable-field" contenteditable="true" title="Klik untuk mengedit jabatan penyusun">
                    {{ $creatorJabatan }}
                </div>
            </div>
            <div class="col-6 col-md-5">
                <div class="small text-muted fw-bold mb-2">DISETUJUI OLEH,</div>
                <div class="box-ttd-img d-flex align-items-center justify-content-center mb-1" style="height: 60px;">
                    @if($approverSigUrl)
                        <img src="{{ $approverSigUrl }}" style="max-height: 55px; max-width: 150px; object-fit: contain;">
                    @endif
                </div>
                <div class="fw-bold text-dark border-bottom d-inline-block pb-1 editable-field" contenteditable="true" title="Klik untuk mengedit nama penyetujui" style="min-width: 130px; max-width: 100%;">
                    {{ $approverName }}
                </div>
                <div class="small text-muted mt-1 editable-field" contenteditable="true" title="Klik untuk mengedit jabatan penyetujui">
                    {{ $approverJabatan }}
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function checkAutoPrintBom() {
            // Auto open print dialog on desktop only, or allow user to inspect first
            if (window.innerWidth > 768) {
                window.print();
            }
        }

        function togglePdfSection(className, show) {
            if (className === 'box-ttd-img') {
                if (show) {
                    $('.box-ttd-img img').css('visibility', 'visible');
                } else {
                    $('.box-ttd-img img').css('visibility', 'hidden');
                }
            } else {
                if (show) {
                    $('.' + className).removeClass('d-none');
                } else {
                    $('.' + className).addClass('d-none');
                }
                updateTfootColspan();
            }
        }

        function updateTfootColspan() {
            let baseColspan = 6; // NO, KODE, NAMA, QTY, WASTE, SATUAN
            if ($('#chkShowFoto').length && $('#chkShowFoto').is(':checked')) {
                baseColspan += 1; // FOTO
            }
            $('#tdTotalLabel').attr('colspan', baseColspan);
            $('tfoot tr td[colspan]').not('#tdTotalLabel').attr('colspan', baseColspan);
        }

        function downloadPdfScreenshot() {
            const $btn = $('.btn-download-ss');
            const originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Mengambil Gambar...').prop('disabled', true);

            const targetElem = document.querySelector('.print-paper-sheet');

            html2canvas(targetElem, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                const fileNo = "{{ str_replace(['/', '\\'], '_', $bom->kode_bom) }}";
                link.download = 'Resep_BOM_' + fileNo + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                $btn.html(originalText).prop('disabled', false);
            }).catch(err => {
                console.error('Screenshot error:', err);
                alert('Gagal mengambil screenshot dokumen. Silakan coba lagi.');
                $btn.html(originalText).prop('disabled', false);
            });
        }

        $(document).ready(function() {
            updateTfootColspan();
        });
    </script>
</body>
</html>
