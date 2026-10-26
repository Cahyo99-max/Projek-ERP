<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\SpkPenerimaanBarangJadiSerial;
use App\Models\SpkProduksi;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;
use Yajra\DataTables\Facades\DataTables;

class TrackingSerialController extends Controller
{
    /**
     * Tampilan Utama Pelacakan Serial Number.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('lihat-tracking-sn')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka modul Pelacakan Serial Number.');
        }

        // Ringkasan Statistik Metrik Serial Number (Single SQL Pass with Index Optimization)
        $stats = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->selectRaw("
                COUNT(*) as total_sn,
                SUM(CASE WHEN status = 'Tersedia' THEN 1 ELSE 0 END) as total_tersedia,
                SUM(CASE WHEN status = 'Terjual' THEN 1 ELSE 0 END) as total_terjual,
                SUM(CASE WHEN status IN ('Rusak', 'Reject', 'RMA') THEN 1 ELSE 0 END) as total_rusak
            ")
            ->first();

        $totalSn       = (int) ($stats->total_sn ?? 0);
        $totalTersedia = (int) ($stats->total_tersedia ?? 0);
        $totalTerjual  = (int) ($stats->total_terjual ?? 0);
        $totalRusak    = (int) ($stats->total_rusak ?? 0);

        // Deteksi Entitas Aktif Sesi
        $activeBranchId    = session('active_branch_id');
        $activeBranch      = $activeBranchId ? Branch::find($activeBranchId) : null;
        $activeCompanyId   = session('active_company_id');
        $activeCompany     = $activeCompanyId ? Company::find($activeCompanyId) : null;

        $isBranchSession   = !empty($activeBranchId);
        $entityName        = $isBranchSession 
            ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
            : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

        // Data Filter Dropdown (Otomatis Sesuai Sesi User Aktif)
        $barangJadis = MasterBarang::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'kode_barang', 'nama_barang']);

        // Gudang Penyimpanan
        $gudangQuery = MasterGudang::query()->where('status', 'Active');
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
            if (!empty($activeCompanyId)) {
                $gudangQuery->where('company_id', $activeCompanyId);
            }
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang', 'asc')->get(['id', 'kode_gudang', 'nama_gudang']);

        // SPK Produksi
        $spks = SpkProduksi::forActiveCompany()->latest('id')->limit(100)->get(['id', 'nomor_spk']);

        return view('modul_produksi.tracking_sn.index', compact(
            'totalSn',
            'totalTersedia',
            'totalTerjual',
            'totalRusak',
            'barangJadis',
            'gudangs',
            'spks',
            'isBranchSession',
            'entityName'
        ));
    }

    /**
     * Server-side DataTables Endpoint untuk Serial Number.
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('lihat-tracking-sn')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengakses data Serial Number.');
        }

        $query = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'barangJadi.kategori',
                'barangJadi.warna',
                'spk.satuan',
                'spk.penanggungJawab',
                'penerimaan.gudang',
                'penerimaan.lokasiRak',
                'penerimaan.penerima',
                'penerimaan.penyerah',
            ])
            ->select('spk_penerimaan_barang_jadi_serials.*');

        // Filter: Barang Jadi
        if ($request->filled('barang_jadi_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.barang_jadi_id', $request->barang_jadi_id);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('spk_penerimaan_barang_jadi_serials.status', $request->status);
        }

        // Filter: Gudang
        if ($request->filled('gudang_id')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->where('gudang_id', $request->gudang_id);
            });
        }

        // Filter: SPK
        if ($request->filled('spk_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.spk_id', $request->spk_id);
        }

        // Filter: Batch
        if ($request->filled('nomor_batch')) {
            $query->where('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', '%' . $request->nomor_batch . '%');
        }

        // Filter: Rentang Tanggal Penerimaan
        if ($request->filled('tanggal_awal')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->whereDate('tanggal_penerimaan', '>=', $request->tanggal_awal);
            });
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->whereDate('tanggal_penerimaan', '<=', $request->tanggal_akhir);
            });
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where(function ($q) use ($search) {
                        $q->where('spk_penerimaan_barang_jadi_serials.serial_number', 'like', "%{$search}%")
                          ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', "%{$search}%")
                          ->orWhere('spk_penerimaan_barang_jadi_serials.status', 'like', "%{$search}%")
                          ->orWhereHas('barangJadi', function ($b) use ($search) {
                              $b->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                          })
                          ->orWhereHas('spk', function ($s) use ($search) {
                              $s->where('nomor_spk', 'like', "%{$search}%");
                          })
                          ->orWhereHas('penerimaan.gudang', function ($g) use ($search) {
                              $g->where('nama_gudang', 'like', "%{$search}%")
                                ->orWhere('kode_gudang', 'like', "%{$search}%");
                          });
                    });
                }
            })
            ->addColumn('serial_number_badge', function ($row) {
                $sn = e($row->serial_number);
                return '
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="badge sn-code-badge font-monospace">
                            <i class="bi bi-upc-scan me-1"></i>' . $sn . '
                        </span>
                    </div>';
            })
            ->addColumn('barang_info', function ($row) {
                $barang = $row->barangJadi;
                if (!$barang) return '<span class="text-muted">-</span>';
                $kode = e($barang->kode_barang);
                $nama = e($barang->nama_barang);
                $kat  = e($barang->kategori->nama_kategori ?? 'Barang Jadi');
                $warna = $barang->warna ? e($barang->warna->nama_warna) : null;
                $warnaHex = $barang->warna ? e($barang->warna->kode_hex) : null;

                $warnaBadge = '';
                if ($warna) {
                    $dot = $warnaHex ? 'background:' . $warnaHex . ';border:1px solid #cbd5e1;' : 'background:#64748b;';
                    $warnaBadge = ' <span class="badge rounded-pill bg-light text-dark border ms-1 py-0.5 px-1.5 fs-9"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;' . $dot . 'margin-right:3px;"></span>' . $warna . '</span>';
                }

                return '
                    <div>
                        <div class="fw-bold item-title-text text-truncate" style="max-width: 220px;" title="' . $nama . '">[' . $kode . '] ' . $nama . '</div>
                        <div class="d-flex align-items-center gap-1 mt-0.5">
                            <small class="item-sub-text fs-8">' . $kat . '</small>' . $warnaBadge . '
                        </div>
                    </div>';
            })
            ->addColumn('batch_info', function ($row) {
                if ($row->nomor_batch) {
                    return '<span class="badge batch-code-badge font-monospace">🏷️ ' . e($row->nomor_batch) . '</span>';
                }
                return '<span class="text-muted fs-8">-</span>';
            })
            ->addColumn('spk_info', function ($row) {
                $spk = $row->spk;
                if (!$spk) return '<span class="text-muted">-</span>';
                return '
                    <div>
                        <span class="fw-bold font-monospace spk-num-text">' . e($spk->nomor_spk) . '</span>
                        <div class="small item-sub-text fs-8">Target: ' . number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . ($spk->satuan->nama_satuan ?? 'Pcs') . '</div>
                    </div>';
            })
            ->addColumn('gudang_lokasi', function ($row) {
                $pbj = $row->penerimaan;
                if (!$pbj) return '<span class="text-muted">-</span>';
                $gudang = $pbj->gudang ? '[' . e($pbj->gudang->kode_gudang) . '] ' . e($pbj->gudang->nama_gudang) : '-';
                $rak    = $pbj->lokasiRak ? '[' . e($pbj->lokasiRak->kode_rak) . '] ' . e($pbj->lokasiRak->nama_rak) : 'Belum di Rak';
                return '
                    <div>
                        <div class="fw-semibold item-title-text fs-8 text-truncate" style="max-width: 190px;" title="' . $gudang . '">' . $gudang . '</div>
                        <small class="rak-sub-text font-monospace fs-8"><i class="bi bi-grid-3x3 me-1"></i>' . $rak . '</small>
                    </div>';
            })
            ->addColumn('tanggal_terima', function ($row) {
                $pbj = $row->penerimaan;
                if ($pbj && $pbj->tanggal_penerimaan) {
                    return '
                        <div>
                            <span class="fw-semibold item-title-text">' . $pbj->tanggal_penerimaan->format('d/m/Y') . '</span>
                            <div class="small item-sub-text font-monospace fs-8">' . e($pbj->nomor_penerimaan) . '</div>
                        </div>';
                }
                return '<span class="text-muted fs-8">' . $row->created_at->format('d/m/Y') . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $status = $row->status ?: 'Tersedia';
                if ($status === 'Tersedia') {
                    return '<span class="badge status-pill-tersedia"><i class="bi bi-check-circle me-1"></i>Tersedia</span>';
                } elseif ($status === 'Terjual') {
                    return '<span class="badge status-pill-terjual"><i class="bi bi-box-arrow-up-right me-1"></i>Terjual / Keluar</span>';
                } elseif (in_array($status, ['Rusak', 'Reject', 'RMA'])) {
                    return '<span class="badge status-pill-rusak"><i class="bi bi-exclamation-triangle me-1"></i>' . e($status) . '</span>';
                }
                return '<span class="badge status-pill-other">' . e($status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $snEscaped    = e($row->serial_number);
                $kodeEscaped  = e($row->barangJadi->kode_barang ?? '-');
                $namaEscaped  = e($row->barangJadi->nama_barang ?? '-');
                $batchEscaped = e($row->nomor_batch ?: '-');
                $warnaEscaped = e($row->barangJadi->warna->nama_warna ?? '-');
                $warnaHex     = e($row->barangJadi->warna->kode_hex ?? '');

                return '
                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                        <button type="button" class="btn btn-sm btn-track-action" onclick="openTimelineModal(\'' . $snEscaped . '\')" title="Lacak Jejak Riwayat SN">
                            <i class="bi bi-clock-history"></i>
                            <span>Lacak</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-bold fs-8 d-inline-flex align-items-center gap-1 shadow-xs" onclick="openQrLabelModal(\'' . $snEscaped . '\', \'' . $kodeEscaped . '\', \'' . $namaEscaped . '\', \'' . $batchEscaped . '\', \'' . $warnaEscaped . '\', \'' . $warnaHex . '\')" title="Lihat & Cetak Label QR Unit">
                            <i class="bi bi-qr-code"></i>
                            <span>QR</span>
                        </button>
                    </div>';
            })
            ->rawColumns(['serial_number_badge', 'barang_info', 'batch_info', 'spk_info', 'gudang_lokasi', 'tanggal_terima', 'status_badge', 'action'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Export Data Serial Number ke Format Excel (Import-Ready & Packing List).
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('lihat-tracking-sn')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh data Serial Number.');
        }

        $query = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'barangJadi.kategori',
                'barangJadi.warna',
                'spk.satuan',
                'spk.penanggungJawab',
                'penerimaan.gudang',
                'penerimaan.lokasiRak',
                'penerimaan.penerima',
                'penerimaan.penyerah',
            ]);

        // Filter: Barang Jadi
        if ($request->filled('barang_jadi_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.barang_jadi_id', $request->barang_jadi_id);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('spk_penerimaan_barang_jadi_serials.status', $request->status);
        }

        // Filter: Gudang
        if ($request->filled('gudang_id')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->where('gudang_id', $request->gudang_id);
            });
        }

        // Filter: SPK
        if ($request->filled('spk_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.spk_id', $request->spk_id);
        }

        // Filter: Batch / Lot
        if ($request->filled('nomor_batch')) {
            $query->where('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', '%' . $request->nomor_batch . '%');
        }

        // Filter: Rentang Tanggal Penerimaan
        if ($request->filled('tanggal_awal')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->whereDate('tanggal_penerimaan', '>=', $request->tanggal_awal);
            });
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->whereDate('tanggal_penerimaan', '<=', $request->tanggal_akhir);
            });
        }

        // Filter: Search query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('spk_penerimaan_barang_jadi_serials.serial_number', 'like', "%{$search}%")
                  ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', "%{$search}%")
                  ->orWhere('spk_penerimaan_barang_jadi_serials.status', 'like', "%{$search}%")
                  ->orWhereHas('barangJadi', function ($b) use ($search) {
                      $b->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                  })
                  ->orWhereHas('spk', function ($s) use ($search) {
                      $s->where('nomor_spk', 'like', "%{$search}%");
                  })
                  ->orWhereHas('penerimaan.gudang', function ($g) use ($search) {
                      $g->where('nama_gudang', 'like', "%{$search}%")
                        ->orWhere('kode_gudang', 'like', "%{$search}%");
                  });
            });
        }

        // Optimasi Memory & Time Limit untuk Data Skala Besar (Jutaan Baris)
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        // Keamanan Kuota Unduh Maksimal per File (50.000 SN) untuk mencegah memory crash
        $serials = $query->orderBy('spk_penerimaan_barang_jadi_serials.id', 'asc')
            ->limit(50000)
            ->cursor();

        $rows = [
            [
                '<b>No</b>',
                '<b>Serial Number</b>',
                '<b>Kode Barang</b>',
                '<b>Nama Barang</b>',
                '<b>Varian / Warna</b>',
                '<b>Satuan</b>',
                '<b>Nomor Batch / Lot</b>',
                '<b>Nomor SPK</b>',
                '<b>Nomor Penerimaan</b>',
                '<b>Tanggal Penerimaan</b>',
                '<b>Gudang</b>',
                '<b>Lokasi Rak</b>',
                '<b>Status</b>',
                '<b>Petugas Penerima</b>'
            ]
        ];

        $no = 1;
        foreach ($serials as $sn) {
            $barang = $sn->barangJadi;
            $spk    = $sn->spk;
            $pbj    = $sn->penerimaan;

            $rows[] = [
                $no++,
                $sn->serial_number,
                $barang->kode_barang ?? '-',
                $barang->nama_barang ?? '-',
                $barang && $barang->warna ? $barang->warna->nama_warna : '-',
                $barang && $barang->satuan ? ($barang->satuan->singkatan ?? $barang->satuan->nama_satuan) : ($spk && $spk->satuan ? $spk->satuan->singkatan : 'PCS'),
                $sn->nomor_batch ?: ($pbj->nomor_batch ?? '-'),
                $spk->nomor_spk ?? '-',
                $pbj->nomor_penerimaan ?? '-',
                $pbj && $pbj->tanggal_penerimaan ? $pbj->tanggal_penerimaan->format('d/m/Y') : '-',
                $pbj && $pbj->gudang ? $pbj->gudang->nama_gudang : '-',
                $pbj && $pbj->lokasiRak ? $pbj->lokasiRak->nama_rak : 'Belum di Rak',
                $sn->status ?: 'Tersedia',
                $pbj ? ($pbj->penerima->nama_lengkap ?? $pbj->penerima_nama ?? '-') : '-'
            ];
        }

        $filename = 'Daftar_Serial_Number_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Detail Riwayat Hidup / Lifecycle Timeline Serial Number (API).
     * Mendukung pencarian instan berdasarkan: Serial Number (SN), No. Lot/Batch (LOD), atau Kode Barang.
     * Menggunakan multi-tier indexed search hierarchy (O(1) Exact Seek -> O(log N) Prefix Range -> Fallback).
     */
    public function track($queryParam)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('lihat-tracking-sn')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melacak Serial Number.',
            ], 403);
        }

        $keyword = trim($queryParam);

        $relations = [
            'barangJadi.satuan',
            'barangJadi.kategori',
            'barangJadi.warna',
            'spk.barangJadi.satuan',
            'spk.barangJadi.warna',
            'spk.satuan',
            'spk.bom.tender',
            'spk.penanggungJawab',
            'spk.creator',
            'penerimaan.gudang',
            'penerimaan.lokasiRak',
            'penerimaan.penerima',
            'penerimaan.penyerah',
            'penerimaan.creator',
            'penerimaan.company',
            'penerimaan.branch',
        ];

        // Tier 1: Fast Exact B-Tree Index Seek (<1ms on millions of rows)
        $sn = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->with($relations)
            ->where(function ($q) use ($keyword) {
                $q->where('spk_penerimaan_barang_jadi_serials.serial_number', $keyword)
                  ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', $keyword)
                  ->orWhereHas('barangJadi', function ($b) use ($keyword) {
                      $b->where('kode_barang', $keyword);
                  });
            })
            ->latest('spk_penerimaan_barang_jadi_serials.id')
            ->first();

        // Tier 2: Prefix Range Scan Fallback (uses B-Tree index)
        if (!$sn) {
            $sn = SpkPenerimaanBarangJadiSerial::forActiveCompany()
                ->with($relations)
                ->where(function ($q) use ($keyword) {
                    $q->where('spk_penerimaan_barang_jadi_serials.serial_number', 'like', "{$keyword}%")
                      ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', "{$keyword}%")
                      ->orWhereHas('barangJadi', function ($b) use ($keyword) {
                          $b->where('kode_barang', 'like', "{$keyword}%");
                      });
                })
                ->latest('spk_penerimaan_barang_jadi_serials.id')
                ->first();
        }

        // Tier 3: Partial Match Fallback (contains)
        if (!$sn) {
            $sn = SpkPenerimaanBarangJadiSerial::forActiveCompany()
                ->with($relations)
                ->where(function ($q) use ($keyword) {
                    $q->where('spk_penerimaan_barang_jadi_serials.serial_number', 'like', "%{$keyword}%")
                      ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', "%{$keyword}%")
                      ->orWhereHas('barangJadi', function ($b) use ($keyword) {
                          $b->where('kode_barang', 'like', "%{$keyword}%");
                      });
                })
                ->latest('spk_penerimaan_barang_jadi_serials.id')
                ->first();
        }

        if (!$sn) {
            return response()->json([
                'success' => false,
                'message' => "Data pelacakan untuk '{$keyword}' (Serial Number / No. Lot / Kode Barang) tidak ditemukan.",
            ], 404);
        }

        $spk = $sn->spk;
        $pbj = $sn->penerimaan;
        $barang = $sn->barangJadi ?: ($spk ? $spk->barangJadi : null);

        // Susun Timeline Stepper
        $timeline = [];

        // 1. TAHAP SPK PRODUKSI
        if ($spk) {
            $timeline[] = [
                'step_number' => 1,
                'title'       => 'Penerbitan Surat Perintah Kerja (SPK)',
                'badge'       => 'PRODUKSI',
                'badge_color' => 'primary',
                'icon'        => 'bi-tools',
                'date'        => $spk->tanggal_spk ? $spk->tanggal_spk->translatedFormat('d F Y') : $spk->created_at->translatedFormat('d F Y'),
                'time'        => $spk->created_at ? $spk->created_at->format('H:i') . ' WIB' : '-',
                'doc_no'      => $spk->nomor_spk,
                'doc_link'    => route('produksi.spk.print', $spk->id),
                'details'     => [
                    'Produk'         => '[' . ($barang->kode_barang ?? '-') . '] ' . ($barang->nama_barang ?? '-'),
                    'Target Qty SPK' => number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . ($spk->satuan->nama_satuan ?? 'Pcs'),
                    'Resep BOM'      => $spk->bom ? '[' . $spk->bom->kode_bom . '] ' . $spk->bom->nama_bom : 'Manual Input',
                    'Tipe Produksi'  => $spk->tipe_spk ?? 'Make to Stock',
                    'Status SPK'     => $spk->status,
                    'PIC Produksi'   => $spk->penanggungJawab->nama_lengkap ?? ($spk->penanggungJawab->name ?? '-'),
                    'Dibuat Oleh'    => $spk->creator->nama_lengkap ?? ($spk->creator->name ?? 'Perencana Produksi'),
                ],
            ];
        }

        // 2. TAHAP PENERIMAAN BARANG JADI (PBJ) KE GUDANG
        if ($pbj) {
            $gudangName = $pbj->gudang ? '[' . $pbj->gudang->kode_gudang . '] ' . $pbj->gudang->nama_gudang : '-';
            $rakName    = $pbj->lokasiRak ? '[' . $pbj->lokasiRak->kode_rak . '] ' . $pbj->lokasiRak->nama_rak : 'Belum diatur ke Rak';
            $penyerah   = $pbj->penyerah ? ($pbj->penyerah->nama_lengkap ?? $pbj->penyerah->name) : ($pbj->penyerah_nama ?: 'Operator QC');
            $penerima   = $pbj->penerima ? ($pbj->penerima->nama_lengkap ?? $pbj->penerima->name) : ($pbj->penerima_nama ?: 'Petugas Gudang');

            $timeline[] = [
                'step_number' => 2,
                'title'       => 'Penerimaan Hasil Jadi di Gudang (PBJ)',
                'badge'       => 'GUDANG',
                'badge_color' => 'success',
                'icon'        => 'bi-box-arrow-in-down-right',
                'date'        => $pbj->tanggal_penerimaan ? $pbj->tanggal_penerimaan->translatedFormat('d F Y') : $pbj->created_at->translatedFormat('d F Y'),
                'time'        => $pbj->created_at ? $pbj->created_at->format('H:i') . ' WIB' : '-',
                'doc_no'      => $pbj->nomor_penerimaan,
                'doc_link'    => route('produksi.barang-jadi.print', $pbj->id),
                'details'     => [
                    'Gudang Tujuan'     => $gudangName,
                    'Nomor / Posisi Rak'=> $rakName,
                    'Nomor Batch / Lot' => $sn->nomor_batch ?: ($pbj->nomor_batch ?: '-'),
                    'Tgl Produksi'      => $pbj->tanggal_produksi ? $pbj->tanggal_produksi->format('d/m/Y') : '-',
                    'Tgl Kedaluwarsa'   => $pbj->tanggal_kedaluwarsa ? $pbj->tanggal_kedaluwarsa->format('d/m/Y') : 'Tanpa Expired',
                    'Diserahkan Oleh'   => $penyerah . ' (QC / Operator)',
                    'Diterima Oleh'     => $penerima . ' (Petugas Gudang)',
                ],
            ];
        }

        // 3. TAHAP STATUS TERKINI
        $currentStatus = $sn->status ?: 'Tersedia';
        $statusText = 'Unit tersimpan di Gudang Barang Jadi dan siap untuk dipesan/didistribusikan.';
        if ($currentStatus === 'Terjual') {
            $statusText = 'Unit telah dikeluarkan dari gudang melalui transaksi penjualan / pengiriman.';
        } elseif (in_array($currentStatus, ['Rusak', 'Reject', 'RMA'])) {
            $statusText = 'Unit ditandai dalam kondisi rusak/reject atau sedang dalam proses klaim perbaikan.';
        }

        $timeline[] = [
            'step_number' => 3,
            'title'       => 'Status Unit Terkini: ' . $currentStatus,
            'badge'       => strtoupper($currentStatus),
            'badge_color' => $currentStatus === 'Tersedia' ? 'success' : ($currentStatus === 'Terjual' ? 'primary' : 'danger'),
            'icon'        => $currentStatus === 'Tersedia' ? 'bi-check-circle-fill' : ($currentStatus === 'Terjual' ? 'bi-truck' : 'bi-exclamation-octagon-fill'),
            'date'        => $sn->updated_at->translatedFormat('d F Y'),
            'time'        => $sn->updated_at->format('H:i') . ' WIB',
            'doc_no'      => 'Kondisi Saat Ini',
            'doc_link'    => null,
            'details'     => [
                'Status Stok'   => $currentStatus,
                'Lokasi Fisik'  => ($pbj && $pbj->gudang ? $pbj->gudang->nama_gudang : 'Gudang') . ($pbj && $pbj->lokasiRak ? ' - Rak: ' . $pbj->lokasiRak->nama_rak : ''),
                'Keterangan'    => $sn->catatan ?: $statusText,
                'Update Terakhir'=> $sn->updated_at->format('d/m/Y H:i') . ' WIB',
            ],
        ];

        return response()->json([
            'success'  => true,
            'data'     => [
                'id'            => $sn->id,
                'serial_number' => $sn->serial_number,
                'nomor_batch'   => $sn->nomor_batch,
                'status'        => $sn->status ?: 'Tersedia',
                'barang'        => [
                    'kode'      => $barang->kode_barang ?? '-',
                    'nama'      => $barang->nama_barang ?? '-',
                    'kategori'  => $barang->kategori->nama_kategori ?? 'Barang Jadi',
                    'satuan'    => $barang->satuan->nama_satuan ?? 'Pcs',
                    'warna'     => $barang->warna->nama_warna ?? ($barang->warna_nama ?? '-'),
                    'warna_hex' => $barang->warna->kode_hex ?? null,
                ],
                'timeline'      => $timeline,
            ],
        ]);
    }
}
