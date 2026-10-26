<?php

namespace App\Http\Controllers;

use App\Models\FakturPajak;
use App\Models\FakturPajakDetail;
use App\Models\FakturPajakInvoice;
use App\Models\NsfpQuota;
use App\Models\SalesInvoice;
use App\Models\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class FakturPajakController extends Controller
{
    /**
     * Isolasi Multi-tenant ketat untuk mencegah IDOR
     */
    private function findFakturForTenant($id, array $withRelations = []): FakturPajak
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = FakturPajak::query();
        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        return $query->findOrFail($id);
    }

    private function clearKpiCache(): void
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        Cache::forget("kpi_faktur_pajak_{$activeCompanyId}_" . ($activeBranchId ?: 'all'));
    }

    public function index(Request $request)
    {
        $this->authorize('lihat-faktur-pajak');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = FakturPajak::query()
            ->select([
                'faktur_pajaks.id',
                'faktur_pajaks.nomor_faktur_pajak',
                'faktur_pajaks.tanggal_faktur_pajak',
                'faktur_pajaks.masa_pajak',
                'faktur_pajaks.tahun_pajak',
                'faktur_pajaks.jenis_transaksi',
                'faktur_pajaks.jenis_faktur',
                'faktur_pajaks.is_gabungan',
                'faktur_pajaks.customer_id',
                'faktur_pajaks.nama_customer',
                'faktur_pajaks.npwp_customer',
                'faktur_pajaks.nik_customer',
                'faktur_pajaks.jumlah_dpp',
                'faktur_pajaks.jumlah_ppn',
                'faktur_pajaks.status',
                'faktur_pajaks.referensi',
                'faktur_pajaks.branch_id',
                'faktur_pajaks.company_id',
            ])
            ->with([
                'salesInvoices:sales_invoices.id,sales_invoices.nomor_faktur',
            ])
            ->orderByDesc('id');

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('masa_pajak')) {
            $query->where('masa_pajak', $request->masa_pajak);
        }

        if ($request->filled('tahun_pajak')) {
            $query->where('tahun_pajak', $request->tahun_pajak);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Summary KPI Metrics dengan Caching 30s
        $cacheKey = "kpi_faktur_pajak_{$activeCompanyId}_" . ($activeBranchId ?: 'all');
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        $kpiData = Cache::remember($cacheKey, 30, function () use ($activeBranchId, $activeCompanyId, $currentMonth, $currentYear) {
            $kpiBase = FakturPajak::query();
            if (!empty($activeBranchId)) {
                $kpiBase->where('branch_id', $activeBranchId);
            } else {
                $kpiBase->whereNull('branch_id')->where('company_id', $activeCompanyId);
            }

            $res = (clone $kpiBase)
                ->selectRaw("
                    COUNT(CASE WHEN status != 'Batal' THEN 1 END) as total_faktur,
                    COALESCE(SUM(CASE WHEN status != 'Batal' THEN jumlah_dpp ELSE 0 END), 0) as total_dpp,
                    COALESCE(SUM(CASE WHEN status != 'Batal' THEN jumlah_ppn ELSE 0 END), 0) as total_ppn,
                    COUNT(CASE WHEN status != 'Batal' AND masa_pajak = ? AND tahun_pajak = ? THEN 1 END) as total_bulan_ini
                ", [$currentMonth, $currentYear])
                ->first();

            // Hitung sisa kuota NSFP aktif
            $quotaBase = NsfpQuota::where('status', 'Aktif')->where('tahun', $currentYear);
            if (!empty($activeBranchId)) {
                $quotaBase->where('branch_id', $activeBranchId);
            } else {
                $quotaBase->whereNull('branch_id')->where('company_id', $activeCompanyId);
            }
            $activeQuota = $quotaBase->first();
            $sisaQuota = 0;
            if ($activeQuota) {
                $awal = (int) preg_replace('/[^\d]/', '', $activeQuota->nomor_awal);
                $akhir = (int) preg_replace('/[^\d]/', '', $activeQuota->nomor_akhir);
                $terakhir = $activeQuota->nomor_terakhir ? (int) preg_replace('/[^\d]/', '', $activeQuota->nomor_terakhir) : ($awal - 1);
                $sisaQuota = max(0, $akhir - $terakhir);
            }

            return [
                'totalFaktur'   => (int) ($res?->total_faktur ?? 0),
                'totalDpp'      => (float) ($res?->total_dpp ?? 0),
                'totalPpn'      => (float) ($res?->total_ppn ?? 0),
                'totalBulanIni' => (int) ($res?->total_bulan_ini ?? 0),
                'sisaQuota'     => $sisaQuota,
            ];
        });

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('nomor_faktur_pajak', function ($fp) {
                    $gabunganBadge = $fp->is_gabungan 
                        ? ' <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size:0.68rem;">Gabungan</span>' 
                        : '';
                    return '<a href="javascript:void(0)" class="fw-bold text-success text-decoration-none btn-view-fp" data-id="' . $fp->id . '">' . e($fp->nomor_faktur_pajak) . '</a>' . $gabunganBadge;
                })
                ->addColumn('tanggal_fmt', fn($fp) => $fp->tanggal_faktur_pajak ? $fp->tanggal_faktur_pajak->format('d/m/Y') : '-')
                ->addColumn('masa_tahun', fn($fp) => str_pad($fp->masa_pajak, 2, '0', STR_PAD_LEFT) . ' / ' . $fp->tahun_pajak)
                ->addColumn('customer_info', function ($fp) {
                    $taxId = !empty($fp->npwp_customer) ? e($fp->npwp_customer) : (!empty($fp->nik_customer) ? 'NIK: ' . e($fp->nik_customer) : '<span class="text-muted">Non-NPWP</span>');
                    return '<div class="fw-semibold text-body">' . e($fp->nama_customer) . '</div>'
                        . '<small class="text-body-secondary font-monospace" style="font-size:0.75rem;">' . $taxId . '</small>';
                })
                ->addColumn('ref_invoices', function ($fp) {
                    if ($fp->salesInvoices->isEmpty()) {
                        return '<span class="text-muted small font-monospace">-</span>';
                    }
                    $badges = [];
                    foreach ($fp->salesInvoices as $inv) {
                        $badges[] = '<span class="badge bg-secondary-subtle text-secondary border font-monospace me-1 mb-1">' . e($inv->nomor_faktur) . '</span>';
                    }
                    return implode('', $badges);
                })
                ->addColumn('jumlah_dpp_fmt', fn($fp) => '<div class="font-monospace text-end">Rp ' . number_format($fp->jumlah_dpp, 2, ',', '.') . '</div>')
                ->addColumn('jumlah_ppn_fmt', fn($fp) => '<div class="font-monospace text-end fw-bold text-success">Rp ' . number_format($fp->jumlah_ppn, 2, ',', '.') . '</div>')
                ->addColumn('status_badge', function ($fp) {
                    switch ($fp->status) {
                        case 'Draft':
                            return '<span class="badge bg-secondary text-white rounded-pill px-2.5 py-1">Draft</span>';
                        case 'Siap Upload':
                            return '<span class="badge bg-warning text-dark border border-warning rounded-pill px-2.5 py-1 fw-bold">Siap Upload</span>';
                        case 'Approve DJP':
                            return '<span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-check2-all me-1"></i>Approved DJP</span>';
                        case 'Batal':
                            return '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold">Batal</span>';
                        default:
                            return '<span class="badge bg-light text-dark rounded-pill px-2.5 py-1">' . e($fp->status) . '</span>';
                    }
                })
                ->addColumn('action', function ($fp) {
                    $canEdit = auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-faktur-pajak');
                    $canDelete = auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('hapus-faktur-pajak');

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-circle btn-view-fp" data-id="' . $fp->id . '" title="Lihat Detail"><i class="bi bi-eye"></i></button>';
                    $btn .= '<a href="' . route('modul_marketing.faktur_pajak.export_csv', ['id' => $fp->id]) . '" class="btn btn-sm btn-icon btn-outline-success rounded-circle" title="Download CSV e-Faktur"><i class="bi bi-filetype-csv"></i></a>';
                    
                    if ($fp->status !== 'Batal' && $canEdit) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-status-fp" data-id="' . $fp->id . '" data-status="' . $fp->status . '" title="Ubah Status DJP"><i class="bi bi-arrow-repeat"></i></button>';
                    }

                    if ($fp->status !== 'Approve DJP' && $canDelete) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-fp" data-id="' . $fp->id . '" data-nomor="' . e($fp->nomor_faktur_pajak) . '" title="Hapus / Batalkan"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_faktur_pajak', 'customer_info', 'ref_invoices', 'jumlah_dpp_fmt', 'jumlah_ppn_fmt', 'status_badge', 'action'])
                ->make(true);
        }

        $customers = MasterCustomer::orderBy('nama_customer')->get(['id', 'nama_customer', 'nama_perusahaan', 'npwp', 'kode_customer']);
        $totalFaktur = $kpiData['totalFaktur'];
        $totalDpp = $kpiData['totalDpp'];
        $totalPpn = $kpiData['totalPpn'];
        $sisaQuota = $kpiData['sisaQuota'];

        return view('modul_marketing.faktur_pajak.index', compact(
            'totalFaktur',
            'totalDpp',
            'totalPpn',
            'sisaQuota',
            'customers',
            'currentMonth',
            'currentYear'
        ));
    }

    /**
     * Ambil daftar invoice penjualan yang belum memiliki faktur pajak (bisa difilter per customer)
     */
    public function getUnlinkedInvoices(Request $request)
    {
        $this->authorize('tambah-faktur-pajak');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesInvoice::query()
            ->with(['customer:id,nama_customer,npwp'])
            ->whereNotIn('status', ['Draft', 'Cancelled']);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Subquery aman: Invoice yang belum di-link ke Faktur Pajak aktif manapun
        $query->whereNotExists(function($sub) {
            $sub->select(DB::raw(1))
                ->from('faktur_pajak_invoices')
                ->join('faktur_pajaks', 'faktur_pajaks.id', '=', 'faktur_pajak_invoices.faktur_pajak_id')
                ->whereColumn('faktur_pajak_invoices.sales_invoice_id', 'sales_invoices.id')
                ->where('faktur_pajaks.status', '!=', 'Batal')
                ->whereNull('faktur_pajaks.deleted_at');
        });

        $invoices = $query->orderByDesc('id')->limit(50)->get()->map(function ($inv) {
            $dpp = (float) $inv->subtotal - (float) $inv->diskon_nominal;
            $ppn = (float) $inv->pajak_nominal;

            return [
                'id'            => $inv->id,
                'nomor_faktur'  => $inv->nomor_faktur,
                'tanggal_faktur'=> $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                'customer_id'   => $inv->customer_id,
                'customer_name' => $inv->customer?->nama_customer ?? '-',
                'dpp'           => $dpp,
                'ppn'           => $ppn,
                'grand_total'   => (float) $inv->grand_total,
                'status'        => $inv->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $invoices,
        ]);
    }

    /**
     * Ambil rincian barang dari satu atau beberapa invoice untuk pembentukan Faktur Pajak
     */
    public function getInvoicesDetails(Request $request)
    {
        $this->authorize('tambah-faktur-pajak');

        $invoiceIds = $request->input('invoice_ids', []);
        if (empty($invoiceIds) || !is_array($invoiceIds)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pilih minimal satu Invoice Penjualan.',
            ], 422);
        }

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesInvoice::with([
            'customer.defaultAddress',
            'details.barang',
        ])->whereIn('id', $invoiceIds);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $invoices = $query->get();
        if ($invoices->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        // Validasi semua invoice harus memiliki customer yang sama jika dibuat Faktur Pajak Gabungan
        $customerIds = $invoices->pluck('customer_id')->unique();
        if ($customerIds->count() > 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Faktur Pajak Gabungan hanya boleh menggabungkan invoice dari Customer yang sama!',
            ], 422);
        }

        $firstInvoice = $invoices->first();
        $customer = $firstInvoice->customer;

        $items = [];
        $totalDpp = 0;
        $totalPpn = 0;

        foreach ($invoices as $inv) {
            foreach ($inv->details as $det) {
                $qty = (float) $det->qty;
                $harga = (float) $det->harga_satuan;
                $diskonNom = (float) $det->diskon_nominal;
                $subtotal = ($qty * $harga) - $diskonNom;
                $tarifPpn = (float) ($det->tarif_ppn ?: 11.0);
                $ppn = (float) ($det->nilai_ppn ?: ($subtotal * ($tarifPpn / 100)));

                $totalDpp += $subtotal;
                $totalPpn += $ppn;

                $items[] = [
                    'barang_id'    => $det->barang_id,
                    'nama_barang'  => $det->nama_barang,
                    'qty'          => $qty,
                    'harga_satuan' => $harga,
                    'total_harga'  => $qty * $harga,
                    'diskon'       => $diskonNom,
                    'dpp'          => $subtotal,
                    'tarif_ppn'    => $tarifPpn,
                    'ppn'          => $ppn,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'customer_id'     => $customer?->id,
                'nama_customer'   => $customer?->nama_perusahaan ?: ($customer?->nama_customer ?? '-'),
                'npwp_customer'   => $customer?->npwp ?? '',
                'alamat_customer' => $customer?->alamat ?? '',
                'total_dpp'       => $totalDpp,
                'total_ppn'       => $totalPpn,
                'items'           => $items,
            ],
        ]);
    }

    /**
     * Dapatkan saran nomor NSFP berikutnya secara otomatis
     */
    public function getNextNsfp(Request $request)
    {
        $this->authorize('tambah-faktur-pajak');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $nextNsfp = NsfpQuota::getNextAvailableNsfp(
            $activeCompanyId, 
            $activeBranchId, 
            $request->input('jenis_transaksi', '01'),
            $request->input('jenis_faktur', '0')
        );

        return response()->json([
            'status' => 'success',
            'nsfp'   => $nextNsfp,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-faktur-pajak');

        $request->validate([
            'nomor_faktur_pajak'   => 'required|string|max:50|unique:faktur_pajaks,nomor_faktur_pajak',
            'tanggal_faktur_pajak' => 'required|date',
            'customer_id'          => 'required|exists:master_customers,id',
            'nama_customer'        => 'required|string|max:255',
            'jenis_transaksi'      => 'required|string|max:10',
            'jenis_faktur'         => 'required|in:Normal,Pengganti,Batal',
            'invoice_ids'          => 'required|array|min:1',
            'invoice_ids.*'        => 'required|exists:sales_invoices,id',
            'items'                => 'required|array|min:1',
        ]);

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        DB::beginTransaction();
        try {
            $tgl = Carbon::parse($request->tanggal_faktur_pajak);
            $masaPajak = (int) $tgl->month;
            $tahunPajak = (int) $tgl->year;
            $isGabungan = count($request->invoice_ids) > 1;

            $totalDpp = 0;
            $totalPpn = 0;

            foreach ($request->items as $item) {
                $totalDpp += (float) ($item['dpp'] ?? 0);
                $totalPpn += (float) ($item['ppn'] ?? 0);
            }

            $fakturPajak = FakturPajak::create([
                'nomor_faktur_pajak'   => trim($request->nomor_faktur_pajak),
                'tanggal_faktur_pajak' => $request->tanggal_faktur_pajak,
                'masa_pajak'           => $masaPajak,
                'tahun_pajak'          => $tahunPajak,
                'jenis_transaksi'      => $request->jenis_transaksi,
                'jenis_faktur'         => $request->jenis_faktur,
                'is_gabungan'          => $isGabungan,
                'customer_id'          => $request->customer_id,
                'nama_customer'        => $request->nama_customer,
                'npwp_customer'        => $request->npwp_customer,
                'nik_customer'         => $request->nik_customer,
                'alamat_customer'      => $request->alamat_customer,
                'branch_id'            => $activeBranchId,
                'company_id'           => $activeBranchId ? null : $activeCompanyId,
                'jumlah_dpp'           => $totalDpp,
                'jumlah_ppn'           => $totalPpn,
                'status'               => $request->input('status', 'Siap Upload'),
                'referensi'            => $request->referensi,
                'keterangan'           => $request->keterangan,
                'created_by'           => auth()->id(),
            ]);

            // Link ke Sales Invoice dan update nomor_faktur_pajak di invoice tersebut
            foreach ($request->invoice_ids as $invId) {
                $inv = SalesInvoice::find($invId);
                $invDpp = $inv ? ((float)$inv->subtotal - (float)$inv->diskon_nominal) : 0;
                $invPpn = $inv ? (float)$inv->pajak_nominal : 0;

                FakturPajakInvoice::create([
                    'faktur_pajak_id'  => $fakturPajak->id,
                    'sales_invoice_id' => $invId,
                    'dpp'              => $invDpp,
                    'ppn'              => $invPpn,
                ]);

                if ($inv) {
                    $inv->update(['nomor_faktur_pajak' => $fakturPajak->nomor_faktur_pajak]);
                }
            }

            // Simpan Rincian Objek Faktur
            foreach ($request->items as $item) {
                FakturPajakDetail::create([
                    'faktur_pajak_id' => $fakturPajak->id,
                    'barang_id'       => $item['barang_id'] ?? null,
                    'nama_barang'     => $item['nama_barang'],
                    'qty'             => $item['qty'] ?? 1,
                    'harga_satuan'    => $item['harga_satuan'] ?? 0,
                    'total_harga'     => $item['total_harga'] ?? 0,
                    'diskon'          => $item['diskon'] ?? 0,
                    'dpp'             => $item['dpp'] ?? 0,
                    'tarif_ppn'       => $item['tarif_ppn'] ?? 11,
                    'ppn'             => $item['ppn'] ?? 0,
                ]);
            }

            $this->clearKpiCache();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pajak {$fakturPajak->nomor_faktur_pajak} berhasil diterbitkan!",
                'data'    => $fakturPajak,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store FakturPajak: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menerbitkan Faktur Pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $this->authorize('lihat-faktur-pajak');

        $fakturPajak = $this->findFakturForTenant($id, [
            'customer',
            'salesInvoices',
            'details',
            'creator',
            'company',
            'branch'
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $fakturPajak,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->authorize('edit-faktur-pajak');

        $request->validate([
            'status' => 'required|in:Draft,Siap Upload,Approve DJP,Batal',
        ]);

        $fakturPajak = $this->findFakturForTenant($id);
        $fakturPajak->update([
            'status'     => $request->status,
            'updated_by' => auth()->id(),
        ]);

        $this->clearKpiCache();

        return response()->json([
            'status'  => 'success',
            'message' => "Status Faktur Pajak {$fakturPajak->nomor_faktur_pajak} diperbarui menjadi {$request->status}.",
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('hapus-faktur-pajak');

        $fakturPajak = $this->findFakturForTenant($id, ['salesInvoices']);

        DB::beginTransaction();
        try {
            // Hapus referensi faktur pajak di Sales Invoices terkait
            foreach ($fakturPajak->salesInvoices as $inv) {
                if ($inv->nomor_faktur_pajak === $fakturPajak->nomor_faktur_pajak) {
                    $inv->update(['nomor_faktur_pajak' => null]);
                }
            }

            $fakturPajak->status = 'Batal';
            $fakturPajak->deleted_by = auth()->id();
            $fakturPajak->save();
            $fakturPajak->delete();

            $this->clearKpiCache();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Faktur Pajak berhasil dibatalkan / dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error destroy FakturPajak: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membatalkan Faktur Pajak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export CSV Format Impor Resmi e-Faktur DJP (FK & OF)
     */
    public function exportCsv(Request $request, $id = null)
    {
        $this->authorize('export-faktur-pajak');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = FakturPajak::with(['details', 'salesInvoices'])->where('status', '!=', 'Batal');

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        if ($id) {
            $query->where('id', $id);
        } else {
            if ($request->filled('masa_pajak')) {
                $query->where('masa_pajak', $request->masa_pajak);
            }
            if ($request->filled('tahun_pajak')) {
                $query->where('tahun_pajak', $request->tahun_pajak);
            }
        }

        $fakturs = $query->orderBy('nomor_faktur_pajak')->get();
        if ($fakturs->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data Faktur Pajak untuk diekspor.');
        }

        $filename = "eFaktur_Export_" . date('Ymd_His') . ".csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($fakturs) {
            $file = fopen('php://output', 'w');

            // Header Format DJP e-Faktur
            fputcsv($file, [
                'FK', 'KD_JENIS_TRANSAKSI', 'FG_PENGGANTI', 'NOMOR_FAKTUR', 'MASA_PAJAK', 'TAHUN_PAJAK',
                'TANGGAL_FAKTUR', 'NPWP', 'NAMA', 'ALAMAT_LENGKAP', 'JUMLAH_DPP', 'JUMLAH_PPN',
                'JUMLAH_PPNBM', 'ID_KETERANGAN_TAMBAHAN', 'FG_UANG_MUKA', 'UANG_MUKA_DPP',
                'UANG_MUKA_PPN', 'UANG_MUKA_PPNBM', 'REFERENSI', 'KODE_DOKUMEN_PENDUKUNG'
            ]);
            fputcsv($file, [
                'LT', 'NPWP', 'NAMA', 'JALAN', 'BLOK', 'NOMOR', 'RT', 'RW', 'KECAMATAN', 'KELURAHAN',
                'KABUPATEN', 'PROPINSI', 'KODE_POS', 'NOMOR_TELEPON'
            ]);
            fputcsv($file, [
                'OF', 'KODE_OBJEK', 'NAMA', 'HARGA_SATUAN', 'JUMLAH_BARANG', 'HARGA_TOTAL', 'DISKON',
                'DPP', 'PPN', 'TARIF_PPNBM', 'PPNBM'
            ]);

            foreach ($fakturs as $fp) {
                $rawNo = preg_replace('/[^\d]/', '', $fp->nomor_faktur_pajak);
                $npwpClean = preg_replace('/[^\d]/', '', $fp->npwp_customer ?: ($fp->nik_customer ?: '0000000000000000'));
                $npwpFmt = str_pad($npwpClean, 16, '0', STR_PAD_LEFT);

                $refText = $fp->referensi ?: $fp->salesInvoices->pluck('nomor_faktur')->implode(', ');

                // Baris FK (Faktur Keluaran Header)
                fputcsv($file, [
                    'FK',
                    str_pad($fp->jenis_transaksi ?: '01', 2, '0', STR_PAD_LEFT),
                    $fp->jenis_faktur === 'Pengganti' ? '1' : '0',
                    $rawNo,
                    $fp->masa_pajak,
                    $fp->tahun_pajak,
                    $fp->tanggal_faktur_pajak->format('d/m/Y'),
                    $npwpFmt,
                    $fp->nama_customer,
                    $fp->alamat_customer ?: '-',
                    (int) round($fp->jumlah_dpp),
                    (int) round($fp->jumlah_ppn),
                    (int) round($fp->jumlah_ppnbm),
                    '',
                    '0',
                    '0',
                    '0',
                    '0',
                    $refText,
                    ''
                ]);

                // Baris OF (Objek Faktur Lines)
                foreach ($fp->details as $det) {
                    fputcsv($file, [
                        'OF',
                        'ITEM',
                        $det->nama_barang,
                        (int) round($det->harga_satuan),
                        (float) $det->qty,
                        (int) round($det->total_harga),
                        (int) round($det->diskon),
                        (int) round($det->dpp),
                        (int) round($det->ppn),
                        (float) $det->tarif_ppnbm,
                        (int) round($det->ppnbm)
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
