@php
    $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
    $appLogoUrl = $appLogoSetting ? asset('storage/' . $appLogoSetting) : asset('gambar/logo.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Posisi Inventaris Stok - {{ date('d_m_Y') }}</title>
    <link rel="icon" href="{{ $appLogoUrl }}" type="image/png">
    <link rel="shortcut icon" href="{{ $appLogoUrl }}" type="image/png">
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- html2canvas for Screenshot / Save Image -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
            background-color: #ffffff;
            font-size: 10px;
            line-height: 1.4;
        }
        .print-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .print-title {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .meta-table td {
            padding: 2px 6px;
            border: none !important;
            font-size: 9.5px;
            white-space: nowrap;
        }
        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-report th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.4px;
            padding: 7px 8px;
            border: 1px solid #cbd5e1 !important;
            text-align: center;
            white-space: nowrap;
        }
        .table-report td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0 !important;
            vertical-align: middle;
            font-size: 9.5px;
        }
        .num-cell {
            white-space: nowrap !important;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 9.5px;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm 6mm 8mm 6mm;
            }
            body {
                background: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .table-report th {
                background-color: #e2e8f0 !important;
                border: 1px solid #475569 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table-report td {
                border: 1px solid #94a3b8 !important;
            }
            .num-cell {
                white-space: nowrap !important;
            }
            .no-print-break {
                page-break-inside: avoid !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid py-2">
        
        <!-- BUTTONS FOR PREVIEW MODE -->
        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <button onclick="captureReportAsImage()" id="btnScreenshot" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1">
                <i class="bi bi-camera-fill me-1"></i> Simpan Gambar (SS PNG)
            </button>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold d-flex align-items-center gap-1">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                Tutup Halaman
            </button>
        </div>

        <!-- PRINT HEADER -->
        <div class="print-header d-flex align-items-center justify-content-between">
            <div>
                <h2 class="print-title mb-1">LAPORAN POSISI INVENTARIS STOK</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white rounded-pill px-2.5 py-1" style="font-size: 9px;">Cabang: {{ session('active_branch_name', '-') }}</span>
                    @if(!($canSeeHpp ?? true))
                    <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 9px;"><i class="bi bi-shield-lock me-1"></i>Akses HPP Dibatasi</span>
                    @endif
                </div>
            </div>
            <div>
                <table class="meta-table">
                    <tr>
                        <td class="text-secondary text-end">Gudang:</td>
                        <td><strong class="text-dark">{{ $selectedGudangName }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-secondary text-end">Kategori:</td>
                        <td><strong class="text-dark">{{ $selectedKategoriName }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-secondary text-end">Barang:</td>
                        <td><strong class="text-dark">{{ $selectedBarangName }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-secondary text-end">Tanggal Cetak:</td>
                        <td><strong class="text-dark">{{ date('d/m/Y H:i:s') }} WIB</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- STOCK DATA TABLE -->
        <table class="table-report">
            <thead>
                <tr>
                    <th width="3%">NO</th>
                    @if($tampilkanFoto ?? true)
                    <th width="6%">FOTO</th>
                    @endif
                    <th width="10%">KODE BARANG</th>
                    <th width="{{ ($tampilkanFoto ?? true) ? '17%' : '22%' }}" class="text-start">NAMA BARANG</th>
                    <th width="11%">GUDANG</th>
                    <th width="10%">KATEGORI</th>
                    <th width="6%">WARNA</th>
                    <th width="8%">STOK AKTUAL</th>
                    @if($canSeeHpp ?? true)
                    <th width="9%" class="text-end">HPP (AVG COST)</th>
                    <th width="9%" class="text-end">BELI TERAKHIR</th>
                    <th width="8%" class="text-end">NILAI PERSEDIAAN</th>
                    @else
                    <th width="26%" class="text-center">AKSES HPP</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQty = 0;
                    $totalNilai = 0;
                @endphp
                @forelse($items as $index => $item)
                    @php
                        $qty = floatval($item->stok_aktual);
                        $hpp = floatval($item->harga_pokok_rata_rata);
                        $beliTerakhir = floatval($item->harga_beli_terakhir);
                        $isStokItem = (!$item->barang || $item->barang->tipe_item === 'Stok');
                        $nilai = $isStokItem ? (floatval($item->nilai_persediaan) > 0 ? floatval($item->nilai_persediaan) : ($qty * $hpp)) : 0;

                        $totalQty += $qty;
                        $totalNilai += $nilai;

                        $satuan = ($item->barang && $item->barang->satuan) ? $item->barang->satuan->nama_satuan : '';
                        $warna = ($item->barang && $item->barang->warna) ? $item->barang->warna->nama_warna : '-';

                        $primaryFoto = $item->barang ? ($item->barang->primaryFoto ?? $item->barang->fotos?->first()) : null;
                        $fotoUrl = $primaryFoto ? asset('storage/' . $primaryFoto->foto_path) : null;
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        @if($tampilkanFoto ?? true)
                        <td class="text-center p-1">
                            @if($fotoUrl)
                                <img src="{{ $fotoUrl }}" alt="Foto" class="rounded border shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <span class="text-muted" style="font-size: 8px;">-</span>
                            @endif
                        </td>
                        @endif
                        <td class="fw-semibold text-center font-monospace">{{ $item->barang ? $item->barang->kode_barang : '-' }}</td>
                        <td class="fw-medium">{{ $item->barang ? $item->barang->nama_barang : '-' }}</td>
                        <td class="text-center">{{ $item->gudang ? $item->gudang->nama_gudang : '-' }}</td>
                        <td class="text-center">{{ ($item->barang && $item->barang->kategori) ? $item->barang->kategori->nama_kategori : '-' }}</td>
                        <td class="text-center">{{ $warna }}</td>
                        @php
                            $roundedQty = round($qty, 2);
                            $formattedQty = (floor($roundedQty) == $roundedQty)
                                ? number_format($roundedQty, 0, ',', '.')
                                : rtrim(rtrim(number_format($roundedQty, 2, ',', '.'), '0'), ',');
                        @endphp
                        <td class="text-center fw-bold num-cell">{{ $formattedQty }} <small class="text-muted fw-normal">{{ $satuan }}</small></td>
                        @if($canSeeHpp ?? true)
                        <td class="text-end num-cell text-success fw-semibold">Rp&nbsp;{{ number_format($hpp, 2, ',', '.') }}</td>
                        <td class="text-end num-cell text-dark">Rp&nbsp;{{ number_format($beliTerakhir, 2, ',', '.') }}</td>
                        <td class="text-end num-cell fw-bold text-dark">Rp&nbsp;{{ number_format($nilai, 2, ',', '.') }}</td>
                        @else
                        <td class="text-center text-muted font-monospace small">*** Access Restricted ***</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($canSeeHpp ?? true) ? (($tampilkanFoto ?? true) ? '11' : '10') : (($tampilkanFoto ?? true) ? '9' : '8') }}" class="text-center py-4 text-muted">Tidak ada data stok yang sesuai dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($items->count() > 0)
                <tfoot>
                    <tr style="background-color: #f8fafc; border-top: 2px solid #0f172a;">
                        <td colspan="{{ ($tampilkanFoto ?? true) ? '4' : '3' }}" class="fw-bold text-end py-2">TOTAL RINGKASAN:</td>
                        <td colspan="3" class="text-center text-secondary" style="font-size: 9px;">{{ $items->count() }} Baris Item</td>
                        @php
                            $roundedTotalQty = round($totalQty, 2);
                            $formattedTotalQty = (floor($roundedTotalQty) == $roundedTotalQty)
                                ? number_format($roundedTotalQty, 0, ',', '.')
                                : rtrim(rtrim(number_format($roundedTotalQty, 2, ',', '.'), '0'), ',');
                        @endphp
                        <td class="text-center fw-bold num-cell text-primary" style="font-size: 10.5px; border-bottom: 2px double #0f172a;">
                            {{ $formattedTotalQty }}
                        </td>
                        @if($canSeeHpp ?? true)
                        <td colspan="2" class="text-end text-secondary fw-semibold">Total Nilai Asset:</td>
                        <td class="text-end fw-bold num-cell text-primary" style="font-size: 10.5px; border-bottom: 2px double #0f172a;">
                            Rp&nbsp;{{ number_format($totalNilai, 2, ',', '.') }}
                        </td>
                        @else
                        <td class="text-center text-muted font-monospace small">*** Access Restricted ***</td>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- AUTHORIZATION SIGNATURES -->
        @if($items->count() > 0)
            <div class="row mt-5 pt-2 no-print-break">
                <div class="col-4 text-center">
                    <p class="mb-0 text-muted" style="font-size: 9px;">Dibuat Oleh,</p>
                    <div style="height: 50px;"></div>
                    <p class="mb-0 fw-bold border-top pt-1 mx-auto" style="width: 140px; font-size: 9.5px;">Staf Gudang</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-0 text-muted" style="font-size: 9px;">Diperiksa Oleh,</p>
                    <div style="height: 50px;"></div>
                    <p class="mb-0 fw-bold border-top pt-1 mx-auto" style="width: 140px; font-size: 9.5px;">Supervisor Gudang</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-0 text-muted" style="font-size: 9px;">Disetujui Oleh,</p>
                    <div style="height: 50px;"></div>
                    <p class="mb-0 fw-bold border-top pt-1 mx-auto" style="width: 140px; font-size: 9.5px;">Manajer Operasional</p>
                </div>
            </div>
        @endif

    </div>

    <!-- AUTO PRINT TRIGGER & SCREENSHOT FUNCTION -->
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(() => {
                window.print();
            }, 500);
        });

        function captureReportAsImage() {
            const btnSS = document.getElementById('btnScreenshot');
            const noPrint = document.querySelector('.no-print');
            const origText = btnSS ? btnSS.innerHTML : '';
            
            if (btnSS) {
                btnSS.disabled = true;
                btnSS.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengambil Gambar...';
            }

            // Temporarily hide control buttons during canvas render
            if (noPrint) noPrint.style.display = 'none';

            html2canvas(document.body, {
                scale: 2, // High resolution (Retina HD print quality)
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                if (noPrint) noPrint.style.display = '';
                if (btnSS) {
                    btnSS.disabled = false;
                    btnSS.innerHTML = origText;
                }

                // Trigger PNG download
                const link = document.createElement('a');
                const tgl = new Date().toISOString().slice(0, 10);
                link.download = 'Laporan_Posisi_Stok_' + tgl + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                if (noPrint) noPrint.style.display = '';
                if (btnSS) {
                    btnSS.disabled = false;
                    btnSS.innerHTML = origText;
                }
                alert('Gagal mengambil gambar/SS: ' + err.message);
            });
        }
    </script>
</body>
</html>
