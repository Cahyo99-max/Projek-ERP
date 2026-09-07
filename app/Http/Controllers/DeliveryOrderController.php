<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOrderController extends Controller
{
    /**
     * Tampilkan halaman utama Pengiriman & Surat Jalan (Delivery Order)
     */
    public function index()
    {
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $gudangsQuery = MasterGudang::active()->forActiveCompany();
        if (!empty($activeBranchId)) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        }
        $gudangs = $gudangsQuery->orderBy('nama_gudang')->get();

        return view('modul_marketing.pengiriman.index', compact('gudangs'));
    }

    /**
     * JSON DataTables untuk Riwayat Pengiriman / Surat Jalan
     */
    public function getData(Request $request)
    {
        $query = DeliveryOrder::forActiveCompany()
            ->forActiveBranch()
            ->with([
                'salesOrder:id,nomor_so,customer_id,status',
                'customer:id,nama_customer,nama_perusahaan',
                'gudang:id,nama_gudang',
                'creator:id,nama_lengkap,username',
                'details.barang:id,nama_barang,kode_barang',
            ])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gudang_id')) {
            $query->where('gudang_id', $request->gudang_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_kirim', [$request->start_date, $request->end_date]);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nomor_do', function ($row) {
                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1 rounded-pill fw-bold">' . e($row->nomor_do) . '</span>';
            })
            ->addColumn('nomor_so', function ($row) {
                if ($row->salesOrder) {
                    return '<span class="font-monospace fw-semibold text-body-emphasis">' . e($row->salesOrder->nomor_so) . '</span>';
                }
                return '<span class="text-muted fst-italic">Manual / Tanpa SO</span>';
            })
            ->addColumn('customer_info', function ($row) {
                $comp = $row->customer?->nama_perusahaan;
                $penerima = $row->nama_penerima ?: ($row->customer?->nama_customer ?: '-');
                $html = '<div class="fw-bold text-body-emphasis">' . e($penerima) . '</div>';
                if ($comp) {
                    $html .= '<div class="text-primary small fw-semibold"><i class="bi bi-building me-1"></i>' . e($comp) . '</div>';
                }
                return $html;
            })
            ->editColumn('tanggal_kirim', function ($row) {
                return $row->tanggal_kirim ? $row->tanggal_kirim->format('d/m/Y') : '-';
            })
            ->addColumn('ekspedisi_info', function ($row) {
                $metode = e($row->metode_pengiriman);
                $eksp   = $row->nama_ekspedisi ? '<div class="text-muted small">' . e($row->nama_ekspedisi) . ($row->nomor_resi ? ' - Resi: <code>' . e($row->nomor_resi) . '</code>' : '') . '</div>' : '';
                $driver = $row->nama_sopir ? '<div class="text-muted small"><i class="bi bi-truck me-1"></i>' . e($row->nama_sopir) . ($row->plat_nomor ? ' (' . e($row->plat_nomor) . ')' : '') . '</div>' : '';
                return '<div class="fw-semibold">' . $metode . '</div>' . $eksp . $driver;
            })
            ->addColumn('items_count', function ($row) {
                $totalQty = $row->details->sum('qty_kirim');
                $countItem = $row->details->count();
                return '<span class="badge bg-secondary-subtle text-body border px-2 py-1 rounded font-monospace">' . $countItem . ' Produk (' . number_format($totalQty, 0, ',', '.') . ' pcs)</span>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'Draft'            => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    'Siap Dikirim'     => 'bg-info-subtle text-info border border-info-subtle',
                    'Dalam Perjalanan' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    'Terkirim'         => 'bg-success-subtle text-success border border-success-subtle',
                    'Dibatalkan'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                ];
                $cls = $map[$row->status] ?? 'bg-secondary-subtle text-secondary';
                return '<span class="badge ' . $cls . ' px-2.5 py-1 rounded-pill fw-bold font-monospace">' . e($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $btnDetail = '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 btn-detail-do" data-id="' . $row->id . '" title="Lihat Rincian Pengiriman"><i class="bi bi-eye"></i> Detail</button>';
                $btnPrint  = '<a href="' . route('marketing.delivery-order.print', $row->id) . '" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1" title="Cetak Surat Jalan A4"><i class="bi bi-printer"></i> Print</a>';
                
                $btnStatus = '';
                if ($row->status === 'Draft') {
                    $btnStatus = '<button type="button" class="btn btn-info btn-sm rounded-pill px-2.5 py-1 btn-change-status" data-id="' . $row->id . '" data-status="Siap Dikirim" title="Siap Dikirim"><i class="bi bi-box-arrow-up-right"></i> Siap Kirim</button>';
                } elseif ($row->status === 'Siap Dikirim') {
                    $btnStatus = '<button type="button" class="btn btn-warning btn-sm rounded-pill px-2.5 py-1 btn-change-status" data-id="' . $row->id . '" data-status="Dalam Perjalanan" title="Kirim Sekarang"><i class="bi bi-truck"></i> Kirim</button>';
                } elseif ($row->status === 'Dalam Perjalanan') {
                    $btnStatus = '<button type="button" class="btn btn-success btn-sm rounded-pill px-2.5 py-1 btn-change-status" data-id="' . $row->id . '" data-status="Terkirim" title="Konfirmasi Diterima"><i class="bi bi-check2-circle"></i> Selesai</button>';
                }

                return '<div class="d-flex align-items-center gap-1.5 flex-nowrap">' . $btnDetail . $btnPrint . $btnStatus . '</div>';
            })
            ->rawColumns(['nomor_do', 'nomor_so', 'customer_info', 'ekspedisi_info', 'items_count', 'status', 'action'])
            ->make(true);
    }

    /**
     * Dapatkan daftar Sales Order yang siap dikirim
     */
    public function getAvailableSalesOrders()
    {
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $query = SalesOrder::query();

        if (!empty($activeCompanyId)) {
            $query->where(function($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
            });
        }

        if (!empty($activeBranchId)) {
            $query->where(function($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            });
        }

        $orders = $query->where('status', '!=', 'Cancelled')
            ->with(['customer:id,nama_customer,nama_perusahaan,telepon,hp'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get(['id', 'nomor_so', 'tanggal_so', 'customer_id', 'nama_customer_walkin', 'telepon_customer_walkin', 'alamat_pengiriman', 'status']);

        return response()->json([
            'success' => true,
            'orders'  => $orders,
        ]);
    }

    /**
     * Dapatkan detail rincian Sales Order untuk pembuatan Delivery Order (dengan kalkulasi Pengiriman Parsial)
     */
    public function getSalesOrderDetails(SalesOrder $order)
    {
        $order->load([
            'customer:id,nama_customer,nama_perusahaan,telepon,hp',
            'customer.defaultAddress',
            'details.barang:id,kode_barang,nama_barang,satuan_id',
            'details.barang.satuan:id,nama_satuan',
        ]);

        // Hitung akumulasi qty yang sudah pernah dikirim untuk setiap item
        foreach ($order->details as $d) {
            $deliveredQty = (float) DeliveryOrderDetail::where('sales_order_detail_id', $d->id)
                ->whereHas('deliveryOrder', function($q) {
                    $q->where('status', '!=', 'Dibatalkan');
                })
                ->sum('qty_kirim');

            $qtyOrder = (float) $d->qty;
            $qtySisa  = max(0, $qtyOrder - $deliveredQty);

            $d->qty_delivered = $deliveredQty;
            $d->qty_sisa      = $qtySisa;
            $d->is_fulfilled  = ($qtySisa <= 0);
        }

        return response()->json([
            'success' => true,
            'order'   => $order,
        ]);
    }

    /**
     * Simpan Surat Jalan / Delivery Order baru (Mendukung Pengiriman Parsial)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_kirim'      => 'required|date',
            'gudang_id'          => 'required|exists:master_gudang,id',
            'metode_pengiriman'  => 'required|string|max:50',
            'items'              => 'required|array|min:1',
            'items.*.qty_kirim'  => 'required|numeric|min:0.01',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        DB::beginTransaction();
        try {
            // Generate Nomor DO: DO-YYMM-XXXX
            $prefix = 'DO-' . date('ym') . '-';
            $lastDo = DeliveryOrder::where('company_id', $activeCompanyId)
                ->where('nomor_do', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNum = $lastDo ? ((int) substr($lastDo->nomor_do, -4)) + 1 : 1;
            $nomorDo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $salesOrder = null;
            if ($request->filled('sales_order_id')) {
                $salesOrder = SalesOrder::find($request->sales_order_id);
            }

            // VALIDASI KETIDAKCUKUPAN STOK GUDANG SEBELUM PROSES
            if (in_array($request->status ?: 'Siap Dikirim', ['Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                foreach ($request->items as $item) {
                    $qtyKirim = (float) ($item['qty_kirim'] ?? 0);
                    $barangId = $item['barang_id'] ?? null;

                    if ($qtyKirim > 0 && !empty($barangId)) {
                        $stokGudang = StokGudang::where('barang_id', $barangId)
                            ->where('gudang_id', $request->gudang_id)
                            ->first();

                        $stokTersedia = $stokGudang ? (float) $stokGudang->stok : 0;

                        if ($qtyKirim > $stokTersedia) {
                            $namaBarang = $item['nama_barang_custom'] ?? 'Produk';
                            if (empty($item['nama_barang_custom']) && class_exists(MasterBarang::class)) {
                                $bObj = MasterBarang::find($barangId);
                                if ($bObj) $namaBarang = $bObj->nama_barang;
                            }

                            $satuan = $item['nama_satuan'] ?? 'Unit';

                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Stok untuk produk \"{$namaBarang}\" di gudang yang dipilih tidak mencukupi! Stok fisik tersedia: {$stokTersedia} {$satuan}, permintaan kirim: {$qtyKirim} {$satuan}. Silakan sesuaikan jumlah kirim atau lakukan mutasi stok antar gudang terlebih dahulu.",
                            ], 422);
                        }
                    }
                }
            }

            $deliveryOrder = DeliveryOrder::create([
                'nomor_do'           => $nomorDo,
                'sales_order_id'     => $salesOrder?->id,
                'customer_id'        => $salesOrder?->customer_id ?? $request->customer_id,
                'nama_penerima'      => $request->nama_penerima ?: ($salesOrder?->customer?->nama_customer ?: $salesOrder?->nama_customer_walkin),
                'telepon_penerima'   => $request->telepon_penerima ?: ($salesOrder?->customer?->telepon ?: $salesOrder?->telepon_customer_walkin),
                'alamat_tujuan'      => $request->alamat_tujuan ?: $salesOrder?->alamat_pengiriman,
                'company_id'         => $activeCompanyId,
                'branch_id'          => $activeBranchId,
                'gudang_id'          => $request->gudang_id,
                'tanggal_kirim'      => $request->tanggal_kirim,
                'estimasi_tiba'      => $request->estimasi_tiba,
                'metode_pengiriman'  => $request->metode_pengiriman,
                'nama_ekspedisi'     => $request->nama_ekspedisi,
                'nomor_resi'         => $request->nomor_resi,
                'nama_sopir'         => $request->nama_sopir,
                'plat_nomor'         => $request->plat_nomor,
                'biaya_ongkir'       => $request->biaya_ongkir ?: 0,
                'catatan_pengiriman' => $request->catatan_pengiriman,
                'status'             => $request->status ?: 'Siap Dikirim',
            ]);

            foreach ($request->items as $item) {
                $qtyKirim = (float) $item['qty_kirim'];
                if ($qtyKirim <= 0) continue;

                $doDetail = DeliveryOrderDetail::create([
                    'delivery_order_id'     => $deliveryOrder->id,
                    'sales_order_detail_id' => $item['sales_order_detail_id'] ?? null,
                    'barang_id'             => $item['barang_id'] ?? null,
                    'nama_barang_custom'    => $item['nama_barang_custom'] ?? null,
                    'qty_order'             => (float) ($item['qty_order'] ?? $qtyKirim),
                    'qty_kirim'             => $qtyKirim,
                    'nama_satuan'           => $item['nama_satuan'] ?? 'Pcs',
                    'catatan_item'          => $item['catatan_item'] ?? null,
                ]);

                // Jika status langsung Siap Dikirim / Dalam Perjalanan / Terkirim, kurangi stok gudang
                if (in_array($deliveryOrder->status, ['Siap Dikirim', 'Dalam Perjalanan', 'Terkirim']) && !empty($item['barang_id'])) {
                    $this->potongStokGudang($deliveryOrder, $item['barang_id'], $qtyKirim, $request->gudang_id);
                }
            }

            // Sinkronisasi status Sales Order berdasarkan kelengkapan pengiriman
            if ($salesOrder) {
                $this->syncSalesOrderStatus($salesOrder);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Surat Jalan {$nomorDo} berhasil dibuat.",
                'do_id'   => $deliveryOrder->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store DeliveryOrder: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Surat Jalan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lihat Rincian Surat Jalan (Modal Detail JSON)
     */
    public function show(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load([
            'salesOrder:id,nomor_so,tanggal_so,status,grand_total',
            'customer:id,nama_customer,nama_perusahaan,telepon,alamat',
            'company:id,nama_perusahaan,logo,address,phone,email,npwp',
            'branch:id,nama_cabang,address,phone,email',
            'gudang:id,nama_gudang,kode_gudang,alamat',
            'creator:id,nama_lengkap,username',
            'details.barang:id,kode_barang,nama_barang,satuan_id',
            'details.barang.satuan:id,nama_satuan',
        ]);

        return response()->json([
            'success' => true,
            'do'      => $deliveryOrder,
        ]);
    }

    /**
     * Update Status Pengiriman (Siap Dikirim, Dalam Perjalanan, Terkirim, Dibatalkan)
     */
    public function updateStatus(DeliveryOrder $deliveryOrder, Request $request)
    {
        $request->validate([
            'status' => 'required|in:Draft,Siap Dikirim,Dalam Perjalanan,Terkirim,Dibatalkan',
        ]);

        $oldStatus = $deliveryOrder->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'message' => 'Status tidak berubah.']);
        }

        DB::beginTransaction();
        try {
            // Jika status berubah dari Draft ke Siap Dikirim/Dalam Perjalanan/Terkirim -> validasi stok & kurangi stok
            if ($oldStatus === 'Draft' && in_array($newStatus, ['Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                // Validasi stok sebelum dipotong
                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $stokGudang = StokGudang::where('barang_id', $d->barang_id)
                            ->where('gudang_id', $deliveryOrder->gudang_id)
                            ->first();

                        $stokTersedia = $stokGudang ? (float) $stokGudang->stok : 0;
                        $qtyKirim = (float) $d->qty_kirim;

                        if ($qtyKirim > $stokTersedia) {
                            $brgNama = $d->nama_barang_custom ?: ($d->barang ? $d->barang->nama_barang : 'Produk');
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Gagal mengubah status: Stok untuk \"{$brgNama}\" tidak mencukupi di gudang! Tersedia: {$stokTersedia} {$d->nama_satuan}, dibutuhkan: {$qtyKirim} {$d->nama_satuan}.",
                            ], 422);
                        }
                    }
                }

                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $this->potongStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);
                    }
                }
            }

            // Jika status dibatalkan dan sebelumnya sudah potong stok -> kembalikan stok
            if ($newStatus === 'Dibatalkan' && in_array($oldStatus, ['Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $this->kembalikanStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);
                    }
                }
            }

            $deliveryOrder->update(['status' => $newStatus]);

            // Sinkronisasi status SO
            if ($deliveryOrder->sales_order_id && $deliveryOrder->salesOrder) {
                $this->syncSalesOrderStatus($deliveryOrder->salesOrder);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status Surat Jalan {$deliveryOrder->nomor_do} berhasil diperbarui menjadi {$newStatus}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update status DO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cetak Surat Jalan A4 Resmi (Print View)
     */
    public function printSuratJalan(DeliveryOrder $deliveryOrder, Request $request)
    {
        $deliveryOrder->load([
            'salesOrder:id,nomor_so,tanggal_so,catatan',
            'customer:id,nama_customer,nama_perusahaan,telepon,alamat',
            'company:id,nama_perusahaan,logo,address,city,province,postal_code,phone,email,npwp',
            'branch:id,nama_cabang,address,city,province,postal_code,phone,email',
            'gudang:id,nama_gudang,kode_gudang,alamat',
            'creator:id,nama_lengkap,username',
            'details.barang:id,kode_barang,nama_barang,satuan_id',
            'details.barang.satuan:id,nama_satuan',
        ]);

        $company = $deliveryOrder->company ?? \App\Models\Company::active()->first() ?? \App\Models\Company::first();
        $branch  = $deliveryOrder->branch ?? auth()->user()?->branch;

        $appLogo = asset('gambar/logo.png');
        if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
            $settingLogo = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', 'app_logo')->value('value');
            if ($settingLogo) {
                $appLogo = file_exists(public_path('storage/' . $settingLogo)) 
                    ? asset('storage/' . $settingLogo) 
                    : (file_exists(public_path($settingLogo)) ? asset($settingLogo) : $appLogo);
            }
        }

        $companyLogo = null;
        if ($company && $company->logo) {
            if (file_exists(public_path('storage/' . $company->logo))) {
                $companyLogo = asset('storage/' . $company->logo);
            } elseif (file_exists(public_path($company->logo))) {
                $companyLogo = asset($company->logo);
            }
        } elseif ($branch && $branch->logo) {
            if (file_exists(public_path('storage/' . $branch->logo))) {
                $companyLogo = asset('storage/' . $branch->logo);
            } elseif (file_exists(public_path($branch->logo))) {
                $companyLogo = asset($branch->logo);
            }
        }

        if (!$companyLogo) {
            $companyLogo = $appLogo;
        }

        return view('modul_marketing.pengiriman.print_surat_jalan', compact('deliveryOrder', 'company', 'branch', 'appLogo', 'companyLogo'));
    }

    /**
     * Helper: Potong Stok Aktual & Buat Mutasi Kartu Stok
     */
    private function potongStokGudang(DeliveryOrder $do, int $barangId, float $qty, int $gudangId)
    {
        $stokGudang = StokGudang::firstOrCreate(
            ['barang_id' => $barangId, 'gudang_id' => $gudangId],
            ['stok' => 0]
        );

        $stokSebelum = (float) $stokGudang->stok;
        $stokSesudah = $stokSebelum - $qty;

        $stokGudang->stok = $stokSesudah;
        $stokGudang->save();

        if (class_exists(KartuStok::class)) {
            KartuStok::create([
                'company_id'    => $do->company_id,
                'branch_id'     => $do->branch_id,
                'barang_id'     => $barangId,
                'gudang_id'     => $gudangId,
                'tanggal'       => $do->tanggal_kirim,
                'tipe_transaksi'=> 'Pengeluaran DO',
                'nomor_referensi'=> $do->nomor_do,
                'keterangan'    => "Pengiriman ke " . ($do->customer?->nama_perusahaan ?: ($do->nama_penerima ?: 'Pelanggan')),
                'stok_masuk'    => 0,
                'stok_keluar'   => $qty,
                'stok_sisa'     => $stokSesudah,
                'created_by'    => auth()->id() ?? $do->created_by,
            ]);
        }
    }

    /**
     * Helper: Kembalikan Stok Gudang jika DO dibatalkan
     */
    private function kembalikanStokGudang(DeliveryOrder $do, int $barangId, float $qty, int $gudangId)
    {
        $stokGudang = StokGudang::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->first();

        if ($stokGudang) {
            $stokSebelum = (float) $stokGudang->stok;
            $stokSesudah = $stokSebelum + $qty;
            $stokGudang->stok = $stokSesudah;
            $stokGudang->save();

            if (class_exists(KartuStok::class)) {
                KartuStok::create([
                    'company_id'    => $do->company_id,
                    'branch_id'     => $do->branch_id,
                    'barang_id'     => $barangId,
                    'gudang_id'     => $gudangId,
                    'tanggal'       => now()->toDateString(),
                    'tipe_transaksi'=> 'Pembatalan DO',
                    'nomor_referensi'=> $do->nomor_do,
                    'keterangan'    => "Pembatalan Surat Jalan {$do->nomor_do}",
                    'stok_masuk'    => $qty,
                    'stok_keluar'   => 0,
                    'stok_sisa'     => $stokSesudah,
                    'created_by'    => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Helper: Sinkronisasi Status Sales Order berdasarkan Akumulasi Pengiriman (Parsial / Penuh)
     */
    private function syncSalesOrderStatus(SalesOrder $salesOrder)
    {
        $salesOrder->load('details');
        $allDelivered = true;
        $hasAnyDelivered = false;

        foreach ($salesOrder->details as $d) {
            $deliveredQty = (float) DeliveryOrderDetail::where('sales_order_detail_id', $d->id)
                ->whereHas('deliveryOrder', function($q) {
                    $q->where('status', '!=', 'Dibatalkan');
                })
                ->sum('qty_kirim');

            if ($deliveredQty > 0) {
                $hasAnyDelivered = true;
            }

            if ($deliveredQty < (float) $d->qty) {
                $allDelivered = false;
            }
        }

        if ($allDelivered && count($salesOrder->details) > 0) {
            $salesOrder->update(['status' => 'Delivered']);
        } elseif ($hasAnyDelivered) {
            $salesOrder->update(['status' => 'Processing']); // Pengiriman Parsial
        }
    }
}
