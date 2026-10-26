<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterKategoriBarang;
use App\Models\MasterWarna;
use App\Models\SpkPenerimaanBarangJadiSerial;
use App\Models\SpkProduksi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CetakLabelController extends Controller
{
    /**
     * Halaman Utama Studio Cetak Label QR & Barcode.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-penerimaan-barang-jadi') && !$user->can('lihat-spk') && !$user->can('akses-produksi')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka modul Cetak Label.');
        }

        // Deteksi Entitas Aktif Sesi
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $activeCompanyId = session('active_company_id');
        $activeCompany   = $activeCompanyId ? Company::find($activeCompanyId) : null;

        $isBranchSession = !empty($activeBranchId);
        $entityName      = $isBranchSession 
            ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
            : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

        // Master Data untuk Filter
        $barangJadis = MasterBarang::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'kode_barang', 'nama_barang']);

        $kategoris = MasterKategoriBarang::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_kategori', 'asc')
            ->get(['id', 'nama_kategori']);

        $warnas = MasterWarna::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_warna', 'asc')
            ->get(['id', 'nama_warna', 'kode_hex']);

        $gudangQuery = MasterGudang::forActiveCompany()->where('status', 'Active');
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang', 'asc')->get(['id', 'kode_gudang', 'nama_gudang']);

        $spks = SpkProduksi::forActiveCompany()
            ->latest('id')
            ->limit(50)
            ->get(['id', 'nomor_spk']);

        return view('modul_produksi.cetak_label.index', compact(
            'barangJadis',
            'kategoris',
            'warnas',
            'gudangs',
            'spks',
            'isBranchSession',
            'entityName',
            'activeCompany',
            'activeBranch'
        ));
    }

    /**
     * DataTables Server-Side untuk Sumber Data: Unit Serial Number (SN).
     */
    public function dataSerials(Request $request)
    {
        $query = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->with([
                'barangJadi.warna',
                'barangJadi.satuan',
                'barangJadi.kategori',
                'spk',
                'penerimaan.gudang',
                'penerimaan.lokasiRak',
            ])
            ->select('spk_penerimaan_barang_jadi_serials.*');

        if ($request->filled('barang_jadi_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.barang_jadi_id', $request->barang_jadi_id);
        }
        if ($request->filled('status')) {
            $query->where('spk_penerimaan_barang_jadi_serials.status', $request->status);
        }
        if ($request->filled('gudang_id')) {
            $query->whereHas('penerimaan', function ($q) use ($request) {
                $q->where('gudang_id', $request->gudang_id);
            });
        }
        if ($request->filled('spk_id')) {
            $query->where('spk_penerimaan_barang_jadi_serials.spk_id', $request->spk_id);
        }
        if ($request->filled('nomor_batch')) {
            $query->where('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', '%' . $request->nomor_batch . '%');
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where(function ($q) use ($search) {
                        $q->where('spk_penerimaan_barang_jadi_serials.serial_number', 'like', "%{$search}%")
                          ->orWhere('spk_penerimaan_barang_jadi_serials.nomor_batch', 'like', "%{$search}%")
                          ->orWhereHas('barangJadi', function ($b) use ($search) {
                              $b->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                          })
                          ->orWhereHas('spk', function ($s) use ($search) {
                              $s->where('nomor_spk', 'like', "%{$search}%");
                          });
                    });
                }
            })
            ->addColumn('checkbox', function ($row) {
                $snEscaped    = e($row->serial_number);
                $kodeEscaped  = e($row->barangJadi->kode_barang ?? '-');
                $namaEscaped  = e($row->barangJadi->nama_barang ?? '-');
                $batchEscaped = e($row->nomor_batch ?: '-');
                $warnaEscaped = e($row->barangJadi->warna->nama_warna ?? '-');
                $warnaHex     = e($row->barangJadi->warna->kode_hex ?? '');
                $harga        = (float) ($row->barangJadi->harga_jual ?? 0);

                return '<div class="text-center">
                    <input type="checkbox" class="form-check-input check-label-item cursor-pointer" 
                        value="' . $row->id . '" 
                        data-type="sn"
                        data-code="' . $snEscaped . '" 
                        data-sn="' . $snEscaped . '" 
                        data-sku="' . $kodeEscaped . '" 
                        data-name="' . $namaEscaped . '" 
                        data-batch="' . $batchEscaped . '" 
                        data-color="' . $warnaEscaped . '" 
                        data-color-hex="' . $warnaHex . '" 
                        data-price="' . $harga . '" 
                        data-qty="1"
                        onchange="handleLabelItemCheck(this)">
                </div>';
            })
            ->addColumn('sn_badge', function ($row) {
                return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace fw-bold px-2 py-1 fs-8"><i class="bi bi-upc-scan me-1"></i>' . e($row->serial_number) . '</span>';
            })
            ->addColumn('barang_info', function ($row) {
                $b = $row->barangJadi;
                if (!$b) return '-';
                $warna = $b->warna ? '<span class="badge bg-light text-dark border ms-1 fs-9">' . e($b->warna->nama_warna) . '</span>' : '';
                return '<div class="fw-bold fs-8 item-title-text text-truncate" style="max-width: 220px;" title="' . e($b->nama_barang) . '">[' . e($b->kode_barang) . '] ' . e($b->nama_barang) . '</div>
                        <div class="small item-sub-text fs-9">' . e($b->kategori->nama_kategori ?? 'Barang Jadi') . $warna . '</div>';
            })
            ->addColumn('batch_spk', function ($row) {
                $batch = $row->nomor_batch ? '<span class="badge bg-warning bg-opacity-15 text-warning-emphasis font-monospace fs-9 me-1">🏷️ ' . e($row->nomor_batch) . '</span>' : '';
                $spk   = $row->spk ? '<span class="text-primary font-monospace fs-9">' . e($row->spk->nomor_spk) . '</span>' : '-';
                return '<div>' . $batch . '<div class="small text-muted fs-9">' . $spk . '</div></div>';
            })
            ->addColumn('gudang_info', function ($row) {
                $pbj = $row->penerimaan;
                if (!$pbj) return '-';
                $gudang = $pbj->gudang ? e($pbj->gudang->nama_gudang) : '-';
                $rak    = $pbj->lokasiRak ? e($pbj->lokasiRak->nama_rak) : 'Tanpa Rak';
                return '<div class="fs-9 fw-semibold">' . $gudang . '</div><small class="text-info fs-10 font-monospace"><i class="bi bi-grid-3x3 me-0.5"></i>' . $rak . '</small>';
            })
            ->addColumn('status_badge', function ($row) {
                $status = $row->status ?: 'Tersedia';
                $badgeClass = $status === 'Tersedia' ? 'bg-success text-white' : ($status === 'Terjual' ? 'bg-primary text-white' : 'bg-danger text-white');
                return '<span class="badge rounded-pill ' . $badgeClass . ' px-2 py-0.5 fs-9">' . e($status) . '</span>';
            })
            ->rawColumns(['checkbox', 'sn_badge', 'barang_info', 'batch_spk', 'gudang_info', 'status_badge'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * DataTables Server-Side untuk Sumber Data: Katalog Produk / SKU Barang Jadi.
     */
    public function dataProducts(Request $request)
    {
        $query = MasterBarang::forActiveCompany()
            ->with(['warna', 'satuan', 'kategori', 'brand'])
            ->where('status', 'Active');

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('warna_id')) {
            $query->where('warna_id', $request->warna_id);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                $kodeEscaped  = e($row->kode_barang);
                $namaEscaped  = e($row->nama_barang);
                $warnaEscaped = e($row->warna->nama_warna ?? '-');
                $warnaHex     = e($row->warna->kode_hex ?? '');
                $barcodeVal   = $row->barcode ?: $row->kode_barang;

                return '<div class="text-center">
                    <input type="checkbox" class="form-check-input check-label-item cursor-pointer" 
                        value="' . $row->id . '" 
                        data-type="product"
                        data-code="' . e($barcodeVal) . '" 
                        data-sn="" 
                        data-sku="' . $kodeEscaped . '" 
                        data-name="' . $namaEscaped . '" 
                        data-batch="" 
                        data-color="' . $warnaEscaped . '" 
                        data-color-hex="' . $warnaHex . '" 
                        data-qty="1"
                        id="check_prod_' . $row->id . '"
                        onchange="handleLabelItemCheck(this)">
                </div>';
            })
            ->addColumn('kode_barcode', function ($row) {
                $barcode = $row->barcode ?: $row->kode_barang;
                return '<div>
                    <span class="font-monospace fw-bold fs-8 text-primary d-block">[' . e($row->kode_barang) . ']</span>
                    <small class="item-sub-text font-monospace fs-9"><i class="bi bi-upc me-1"></i>' . e($barcode) . '</small>
                </div>';
            })
            ->addColumn('nama_produk', function ($row) {
                $brand = $row->brand ? '<span class="badge bg-secondary bg-opacity-25 text-secondary border px-1.5 py-0.5 fs-10 rounded me-1">' . e($row->brand->nama_brand) . '</span>' : '';
                $warna = $row->warna ? '<span class="badge bg-light text-dark border px-1.5 py-0.5 fs-10 rounded">' . e($row->warna->nama_warna) . '</span>' : '';
                return '<div>
                    <div class="fw-bold fs-8 item-title-text text-truncate" style="max-width: 240px;" title="' . e($row->nama_barang) . '">' . e($row->nama_barang) . '</div>
                    <div class="d-flex align-items-center gap-1 mt-0.5">' . $brand . $warna . '</div>
                </div>';
            })
            ->addColumn('kategori', function ($row) {
                return '<span class="badge bg-info bg-opacity-15 text-info-emphasis border border-info border-opacity-25 fs-9">' . e($row->kategori->nama_kategori ?? '-') . '</span>';
            })
            ->addColumn('satuan_brand', function ($row) {
                $satuan = e($row->satuan->nama_satuan ?? 'Pcs');
                $brand  = e($row->brand->nama_brand ?? '-');
                return '<div class="fs-9 fw-semibold">' . $satuan . '</div><small class="text-muted fs-10">' . $brand . '</small>';
            })
            ->addColumn('qty_input', function ($row) {
                return '<div class="d-flex align-items-center justify-content-center" style="max-width: 90px; margin: 0 auto;">
                    <input type="number" min="1" max="500" value="1" class="form-control form-control-sm text-center font-monospace fw-bold input-print-qty" 
                        data-id="' . $row->id . '" 
                        onchange="handleQtyChange(' . $row->id . ', this.value)" 
                        style="height: 28px; font-size: 11px; padding: 2px 4px;">
                </div>';
            })
            ->rawColumns(['checkbox', 'kode_barcode', 'nama_produk', 'kategori', 'satuan_brand', 'qty_input'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Endpoint untuk memuat seluruh serial number sesuai filter untuk cetak masal.
     */
    public function getBulkSerials(Request $request)
    {
        $query = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->with(['barangJadi.warna', 'barangJadi.satuan']);

        if ($request->filled('barang_jadi_id')) {
            $query->where('barang_jadi_id', $request->barang_jadi_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('spk_id')) {
            $query->where('spk_id', $request->spk_id);
        }
        if ($request->filled('nomor_batch')) {
            $query->where('nomor_batch', 'like', '%' . $request->nomor_batch . '%');
        }

        $serials = $query->limit(500)->get()->map(function ($row) {
            return [
                'id'        => $row->id,
                'code'      => $row->serial_number,
                'sn'        => $row->serial_number,
                'sku'       => $row->barangJadi->kode_barang ?? '-',
                'name'      => $row->barangJadi->nama_barang ?? '-',
                'batch'     => $row->nomor_batch ?: '-',
                'color'     => $row->barangJadi->warna->nama_warna ?? '-',
                'color_hex' => $row->barangJadi->warna->kode_hex ?? '',
                'price'     => (float) ($row->barangJadi->harga_jual ?? 0),
                'qty'       => 1,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => count($serials),
            'data'    => $serials,
        ]);
    }
}
