<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
use App\Models\OpeningStockDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Models\User;
use App\Helpers\AccountMappingHelper;
use App\Helpers\DecimalHelper;
use App\Http\Controllers\WhatsAppGatewayController;
use Yajra\DataTables\Facades\DataTables;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpeningStockController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function resolveGudang(int $gudangId): ?MasterGudang
    {
        $activeBranchId = session('active_branch_id');
        $query = MasterGudang::where('id', $gudangId)
            ->forActiveCompany()
            ->where('status', 'Active');

        if (!empty($activeBranchId)) {
            $query->where('cabang_id', $activeBranchId);
        } else {
            $query->whereNull('cabang_id');
        }

        return $query->first();
    }

    private function resolveOpeningStock(OpeningStock $os): bool
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($os->branch_id != $activeBranchId) {
                return false;
            }
        } else {
            if (!empty($os->branch_id)) {
                return false;
            }
            if (!empty($activeCompanyId) && !empty($os->company_id) && $os->company_id != $activeCompanyId) {
                return false;
            }
        }

        return MasterGudang::where('id', $os->gudang_id)
            ->forActiveCompany()
            ->exists();
    }

    private function isPrivilegedUser(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin');
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — Saldo Awal List (AJAX + Blade)
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $this->authorize('lihat-opening-stock');

        $activeBranchId = session('active_branch_id');
        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();

        if ($request->ajax()) {
            $query = OpeningStock::forActiveCompany()
                ->forActiveBranch();

            // Status Draft HANYA bisa dilihat oleh pembuat atau Admin/Super Admin
            if (!$isPrivileged) {
                $query->where(function($q) use ($user) {
                    $q->where('opening_stocks.status', '!=', 'Draft')
                      ->orWhere('opening_stocks.created_by', $user->id);
                });
            }

            $query->with([
                    'gudang:id,nama_gudang',
                    'creator:id,nama_lengkap',
                ])
                ->select('opening_stocks.id', 'opening_stocks.nomor_opening_stock',
                         'opening_stocks.tanggal_opening_stock', 'opening_stocks.gudang_id',
                         'opening_stocks.status', 'opening_stocks.created_by', 'opening_stocks.branch_id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_opening_stock_fmt', function ($os) {
                    return $os->nomor_opening_stock;
                })
                ->addColumn('tanggal_fmt', function ($os) {
                    return $os->tanggal_opening_stock ? $os->tanggal_opening_stock->format('d/m/Y') : '-';
                })
                ->addColumn('gudang_name', function ($os) {
                    return $os->gudang ? $os->gudang->nama_gudang : '-';
                })
                ->addColumn('status_badge', function ($os) {
                    if ($os->status === 'Draft') {
                        return '<span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } elseif ($os->status === 'Pending') {
                        return '<span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span>';
                    } else {
                        return '<span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>';
                    }
                })
                ->addColumn('creator_name', function ($os) {
                    return $os->creator ? $os->creator->nama_lengkap : '-';
                })
                ->addColumn('action', function ($os) use ($user, $isPrivileged) {
                    $isCreator = $os->created_by == $user->id;

                    // 1. Detail Button (Membuka popup rincian barang, approval, dan jurnal)
                    $btnDetail = '<button class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="showDetailOpening('.$os->id.')" title="Lihat Detail & Persetujuan"><i class="bi bi-eye"></i></button>';

                    // 2. Edit Button
                    $btnEdit = '';
                    if ($os->status === 'Draft' && ($isCreator || $isPrivileged)) {
                        $btnEdit = '<button class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="editOpeningModal('.$os->id.')" title="Edit Draft"><i class="bi bi-pencil"></i></button>';
                    } elseif ($os->status === 'Pending' && ($isCreator || $isPrivileged)) {
                        $btnEdit = '<button class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="editOpeningModal('.$os->id.')" title="Edit Pengajuan"><i class="bi bi-pencil"></i></button>';
                    } elseif ($os->status === 'Confirmed' && $isPrivileged) {
                        $btnEdit = '<button class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="editOpeningModal('.$os->id.')" title="Edit Dokumen"><i class="bi bi-pencil"></i></button>';
                    }

                    // 3. Delete Button
                    $btnDelete = '';
                    if ($os->status === 'Draft' && ($isCreator || $isPrivileged)) {
                        $btnDelete = '<button class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="hapusOpening('.$os->id.', \''.$os->nomor_opening_stock.'\')" title="Hapus Draft"><i class="bi bi-trash3"></i></button>';
                    } elseif (($os->status === 'Pending' || $os->status === 'Confirmed') && $isPrivileged) {
                        $btnDelete = '<button class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="hapusOpening('.$os->id.', \''.$os->nomor_opening_stock.'\')" title="Hapus Dokumen"><i class="bi bi-trash3"></i></button>';
                    }

                    return '<div class="d-inline-flex align-items-center" style="white-space: nowrap;">' . $btnDetail . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $gudangsQuery = MasterGudang::forActiveCompany()
            ->where('status', 'Active');

        if (!empty($activeBranchId)) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangsQuery->whereNull('cabang_id');
        }

        $gudangs = $gudangsQuery->orderBy('nama_gudang')->get();

        $baseCountQuery = OpeningStock::forActiveCompany()
            ->forActiveBranch();

        if (!$isPrivileged) {
            $baseCountQuery->where(function($q) use ($user) {
                $q->where('opening_stocks.status', '!=', 'Draft')
                  ->orWhere('opening_stocks.created_by', $user->id);
            });
        }

        $openingCount = (clone $baseCountQuery)->count();

        $draftCount = (clone $baseCountQuery)
            ->where('status', 'Draft')
            ->count();

        $pendingCount = (clone $baseCountQuery)
            ->where('status', 'Pending')
            ->count();

        $confirmedCount = (clone $baseCountQuery)
            ->where('status', 'Confirmed')
            ->count();

        return view('modul_inventori.saldo_awal.viewopening', compact('gudangs', 'openingCount', 'draftCount', 'pendingCount', 'confirmedCount'));
    }

    public function searchBarang(Request $request)
    {
        $this->authorize('tambah-opening-stock');

        if ($request->ajax()) {
            $query = MasterBarang::active()
                ->forActiveCompany()
                ->with(['satuan:id,nama_satuan'])
                ->select('id', 'kode_barang', 'nama_barang', 'satuan_id');

            return DataTables::of($query)
                ->addColumn('satuan_name', function ($b) {
                    return $b->satuan ? $b->satuan->nama_satuan : '-';
                })
                ->addColumn('action', function ($b) {
                    $satName = $b->satuan ? $b->satuan->nama_satuan : '-';
                    $kodeEsc = addslashes($b->kode_barang);
                    $namaEsc = addslashes($b->nama_barang);
                    $satEsc  = addslashes($satName);
                    return '<button type="button" class="btn btn-sm btn-primary px-3 rounded-pill fw-semibold" onclick="selectBarangFromModalDirect('.$b->id.', \''.$kodeEsc.'\', \''.$namaEsc.'\', \''.$satEsc.'\')"><i class="bi bi-plus-lg me-1"></i>Pilih</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Create Draft or Submit Pending Document
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $this->authorize('tambah-opening-stock');

        try {
            $request->validate([
                'tanggal_opening_stock' => 'required|date',
                'gudang_id'             => 'required|integer',
                'keterangan'            => 'nullable|string|max:40',
                'auto_number'           => 'required|boolean',
                'nomor_opening_stock'   => 'nullable|required_if:auto_number,0|string|max:50',
                'submit_action'         => 'nullable|string|in:draft,pending,confirm',
                'items'                 => 'required|array|min:1',
                'items.*.barang_id'     => 'required|integer|exists:master_barang,id',
                'items.*.jumlah'        => 'required|numeric|min:0.01',
                'items.*.harga_pokok'   => 'required|numeric|min:0.00',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // Validate manual number uniqueness
        if (!$request->auto_number) {
            $exists = OpeningStock::where('nomor_opening_stock', $request->nomor_opening_stock)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors'  => ['nomor_opening_stock' => ['Nomor saldo awal sudah digunakan!']],
                ], 422);
            }
        }

        // Group duplicate items inside the request
        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            $qty = floatval($item['jumlah']);
            $hpp = floatval($item['harga_pokok']);
            
            if (isset($itemsGrouped[$bid])) {
                $totalVal = ($itemsGrouped[$bid]['qty'] * $itemsGrouped[$bid]['hpp']) + ($qty * $hpp);
                $itemsGrouped[$bid]['qty'] += $qty;
                $itemsGrouped[$bid]['hpp'] = $itemsGrouped[$bid]['qty'] > 0 ? ($totalVal / $itemsGrouped[$bid]['qty']) : 0.00;
            } else {
                $itemsGrouped[$bid] = [
                    'qty' => $qty,
                    'hpp' => $hpp
                ];
            }
        }

        $submitAction = strtolower($request->input('submit_action', 'draft'));
        $targetStatus = $submitAction === 'pending' ? 'Pending' : 'Draft';

        try {
            DB::beginTransaction();

            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_opening_stock));
                $month  = date('m', strtotime($request->tanggal_opening_stock));
                $prefix = "OPN/{$year}/{$month}/";

                $latest = OpeningStock::withTrashed()
                    ->where('nomor_opening_stock', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('nomor_opening_stock', 'desc')
                    ->first();

                $number = 1;
                if ($latest) {
                    $lastPart = substr($latest->nomor_opening_stock, strlen($prefix));
                    $number = ((int) $lastPart) + 1;
                }

                do {
                    $nomorOpn = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
                    $exists = OpeningStock::withTrashed()->where('nomor_opening_stock', $nomorOpn)->exists();
                    if ($exists) {
                        $number++;
                    }
                } while ($exists);
            } else {
                $nomorOpn = $request->nomor_opening_stock;
            }

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            // Create Opening Stock
            $openingStock = OpeningStock::create([
                'nomor_opening_stock'   => $nomorOpn,
                'tanggal_opening_stock' => $request->tanggal_opening_stock,
                'gudang_id'             => $gudang->id,
                'company_id'            => $targetCompanyId,
                'branch_id'             => $targetBranchId,
                'keterangan'            => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'                => $targetStatus,
            ]);

            // Save details in bulk chunks
            $detailsData = [];
            $nowStr = now()->toDateTimeString();
            foreach ($itemsGrouped as $barangId => $data) {
                $detailsData[] = [
                    'opening_stock_id' => $openingStock->id,
                    'barang_id'        => $barangId,
                    'jumlah'           => $data['qty'],
                    'harga_pokok'      => $data['hpp'],
                    'created_at'       => $nowStr,
                    'updated_at'       => $nowStr,
                ];
            }
            foreach (array_chunk($detailsData, 500) as $chunk) {
                OpeningStockDetail::insert($chunk);
            }

            DB::commit();

            // WhatsApp Notification jika langsung diajukan
            if ($targetStatus === 'Pending') {
                WhatsAppGatewayController::sendOpeningStockSubmissionNotification($openingStock);
                $msg = "Saldo awal {$nomorOpn} berhasil dibuat dan diajukan (Pending Approval)!";
            } else {
                $msg = "Saldo awal {$nomorOpn} berhasil disimpan sebagai Draft!";
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'nomor_opening_stock' => $nomorOpn,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OpeningStock Store Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Gagal menyimpan saldo awal: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SUBMIT — Transition Draft to Pending
    // ══════════════════════════════════════════════════════════════
    public function submit(OpeningStock $openingStock)
    {
        $this->authorize('tambah-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Saldo awal tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        if ($openingStock->created_by != $user->id && !$isPrivileged) {
            return response()->json(['message' => 'Hanya pembuat atau Admin yang dapat mengajukan draf ini.'], 403);
        }

        if ($openingStock->status !== 'Draft') {
            return response()->json(['message' => 'Hanya dokumen berstatus Draft yang dapat diajukan.'], 422);
        }

        try {
            DB::beginTransaction();

            $openingStock->update(['status' => 'Pending']);

            DB::commit();

            // WhatsApp notification to approvers
            WhatsAppGatewayController::sendOpeningStockSubmissionNotification($openingStock);

            return response()->json([
                'status'  => 'success',
                'message' => "Dokumen saldo awal {$openingStock->nomor_opening_stock} berhasil diajukan (Pending) untuk peninjauan!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengajukan dokumen: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT — Return Data for Modal Edit
    // ══════════════════════════════════════════════════════════════
    public function edit(OpeningStock $openingStock)
    {
        $this->authorize('tambah-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Saldo awal tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        $isCreator = $openingStock->created_by == $user->id;

        if ($openingStock->status === 'Confirmed' && !$isPrivileged) {
            return response()->json(['message' => 'Hanya Admin/Super Admin yang dapat mengedit dokumen Confirmed.'], 403);
        }

        if (($openingStock->status === 'Draft' || $openingStock->status === 'Pending') && !$isCreator && !$isPrivileged) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $openingStock->load([
            'gudang',
            'details.barang.satuan',
        ]);

        return response()->json([
            'status'       => 'success',
            'openingStock' => $openingStock,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Update Draft, Pending, or Confirmed Document
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, OpeningStock $openingStock)
    {
        $this->authorize('tambah-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Saldo awal tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        $isCreator = $openingStock->created_by == $user->id;

        if ($openingStock->status === 'Confirmed' && !$isPrivileged) {
            return response()->json(['message' => 'Hanya Admin/Super Admin yang dapat mengubah dokumen Confirmed.'], 403);
        }

        if (($openingStock->status === 'Draft' || $openingStock->status === 'Pending') && !$isCreator && !$isPrivileged) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        try {
            $request->validate([
                'tanggal_opening_stock' => 'required|date',
                'gudang_id'             => 'required|integer',
                'keterangan'            => 'nullable|string|max:40',
                'submit_action'         => 'nullable|string|in:draft,pending,confirm',
                'items'                 => 'required|array|min:1',
                'items.*.barang_id'     => 'required|integer|exists:master_barang,id',
                'items.*.jumlah'        => 'required|numeric|min:0.01',
                'items.*.harga_pokok'   => 'required|numeric|min:0.00',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // Group duplicate items
        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            $qty = floatval($item['jumlah']);
            $hpp = floatval($item['harga_pokok']);
            
            if (isset($itemsGrouped[$bid])) {
                $totalVal = ($itemsGrouped[$bid]['qty'] * $itemsGrouped[$bid]['hpp']) + ($qty * $hpp);
                $itemsGrouped[$bid]['qty'] += $qty;
                $itemsGrouped[$bid]['hpp'] = $itemsGrouped[$bid]['qty'] > 0 ? ($totalVal / $itemsGrouped[$bid]['qty']) : 0.00;
            } else {
                $itemsGrouped[$bid] = [
                    'qty' => $qty,
                    'hpp' => $hpp
                ];
            }
        }

        $wasConfirmed = $openingStock->status === 'Confirmed';
        $submitAction = strtolower($request->input('submit_action', ''));

        // Determine new status
        if ($wasConfirmed) {
            $newStatus = 'Confirmed';
        } else {
            if ($submitAction === 'pending') {
                $newStatus = 'Pending';
            } elseif ($submitAction === 'draft') {
                $newStatus = 'Draft';
            } else {
                $newStatus = $openingStock->status;
            }
        }

        try {
            DB::beginTransaction();

            $openingStock = OpeningStock::where('id', $openingStock->id)->lockForUpdate()->first();

            // STEP 1: If previously confirmed, REVERT stocks and journals completely
            if ($wasConfirmed) {
                $this->revertOpeningStockPosting($openingStock);
            }

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            // STEP 2: Update header and replace details
            $openingStock->update([
                'tanggal_opening_stock' => $request->tanggal_opening_stock,
                'gudang_id'             => $gudang->id,
                'company_id'            => $targetCompanyId,
                'branch_id'             => $targetBranchId,
                'keterangan'            => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'                => $newStatus,
            ]);

            $openingStock->details()->delete();

            $detailsData = [];
            $nowStr = now()->toDateTimeString();
            foreach ($itemsGrouped as $barangId => $data) {
                $detailsData[] = [
                    'opening_stock_id' => $openingStock->id,
                    'barang_id'        => $barangId,
                    'jumlah'           => $data['qty'],
                    'harga_pokok'      => $data['hpp'],
                    'created_at'       => $nowStr,
                    'updated_at'       => $nowStr,
                ];
            }
            foreach (array_chunk($detailsData, 500) as $chunk) {
                OpeningStockDetail::insert($chunk);
            }

            // STEP 3: If new status is Confirmed, post stock and journal
            if ($newStatus === 'Confirmed') {
                $this->executePostingStocks($openingStock);
                $this->postJournalForOpeningStock($openingStock);
            }

            DB::commit();

            if ($newStatus === 'Pending' && !$wasConfirmed && $openingStock->status === 'Draft') {
                WhatsAppGatewayController::sendOpeningStockSubmissionNotification($openingStock);
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Saldo awal {$openingStock->nomor_opening_stock} berhasil diperbarui!",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OpeningStock Update Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Gagal memperbarui saldo awal: ' . $e->getMessage()], 422);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // IMPORT EXCEL — Import Opening Stock from Excel
    // ══════════════════════════════════════════════════════════════
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-opening-stock');

        try {
            $request->validate([
                'tanggal_opening_stock' => 'required|date',
                'gudang_id'             => 'required|integer',
                'keterangan'            => 'nullable|string|max:40',
                'auto_number'           => 'required|boolean',
                'nomor_opening_stock'   => 'nullable|required_if:auto_number,0|string|max:50',
                'submit_action'         => 'nullable|string|in:draft,pending',
                'excel_file'            => 'required|file|mimes:xlsx',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Gudang tidak ditemukan di cabang aktif Anda!',
            ], 422);
        }

        // Validate manual number uniqueness
        if (!$request->auto_number) {
            $exists = OpeningStock::where('nomor_opening_stock', $request->nomor_opening_stock)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Nomor saldo awal sudah digunakan!',
                ], 422);
            }
        }

        $file = $request->file('excel_file');
        
        if (!$xlsx = SimpleXLSX::parse($file->path())) {
            return response()->json([
                'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
            ], 422);
        }

        $rows = $xlsx->rows();
        if (count($rows) < 2) {
            return response()->json([
                'message' => 'File Excel kosong atau tidak memiliki baris data!',
            ], 422);
        }

        // First check if MasterBarang exists for active company
        $hasBarang = MasterBarang::active()->forActiveCompany()->exists();
        if (!$hasBarang) {
            return response()->json([
                'message' => 'Master Barang pada PT ini masih kosong! Silakan tambahkan Data Barang terlebih dahulu di menu Master Barang sebelum melakukan Import Saldo Awal.',
            ], 422);
        }

        // Collect all distinct item codes from Excel rows for targeted O(1) memory lookup
        $excelCodes = [];
        for ($i = 1; $i < count($rows); $i++) {
            $k = isset($rows[$i][0]) ? trim((string)$rows[$i][0]) : '';
            if ($k !== '') {
                $excelCodes[$k] = true;
            }
        }
        $excelCodeList = array_keys($excelCodes);

        $activeBarangMap = [];
        if (!empty($excelCodeList)) {
            foreach (array_chunk($excelCodeList, 1000) as $codeChunk) {
                $items = MasterBarang::active()
                    ->forActiveCompany()
                    ->where(function($q) use ($codeChunk) {
                        $q->whereIn('kode_barang', $codeChunk)
                          ->orWhereIn('barcode', $codeChunk);
                    })
                    ->select('id', 'kode_barang', 'barcode')
                    ->get();

                foreach ($items as $b) {
                    if ($b->kode_barang) {
                        $activeBarangMap[trim($b->kode_barang)] = $b->id;
                    }
                    if ($b->barcode) {
                        $activeBarangMap[trim($b->barcode)] = $b->id;
                    }
                }
            }
        }

        $itemsImported = [];
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if (empty($row) || (!isset($row[0]) && !isset($row[1]) && !isset($row[2]) && !isset($row[3]) && !isset($row[4]))) {
                continue;
            }

            $kode = isset($row[0]) ? trim((string)$row[0]) : '';
            if (empty($kode)) {
                continue;
            }
            
            // Flexible Column Mapping:
            // Standard 5 Columns: [Kode Barang, Nama Barang, Satuan, Jumlah Stok, HPP Awal]
            if (isset($row[4])) {
                $qtyStr = $row[3];
                $hppStr = $row[4];
            } elseif (isset($row[3])) {
                // 4 Columns: [Kode Barang, Nama Barang, Jumlah Stok, HPP Awal]
                $qtyStr = $row[2];
                $hppStr = $row[3];
            } elseif (isset($row[2])) {
                // 3 Columns: [Kode Barang, Jumlah Stok, HPP Awal]
                $qtyStr = $row[1];
                $hppStr = $row[2];
            } else {
                $qtyStr = isset($row[1]) ? $row[1] : 0;
                $hppStr = 0;
            }

            $qty = $this->parseNumber($qtyStr);
            $hpp = $this->parseNumber($hppStr);

            if ($qty <= 0) {
                continue;
            }

            $barangId = $activeBarangMap[$kode] ?? null;

            if (!$barangId) {
                $errors[] = "Baris " . ($i + 1) . ": Barang dengan kode/barcode '{$kode}' belum terdaftar atau tidak aktif di Master Barang PT ini. Silakan daftarkan barang tersebut terlebih dahulu di menu Master Barang.";
                continue;
            }

            if ($hpp < 0) {
                $errors[] = "Baris " . ($i + 1) . ": HPP Awal untuk '{$kode}' tidak boleh bernilai negatif.";
                continue;
            }

            if (isset($itemsImported[$barangId])) {
                $totalVal = ($itemsImported[$barangId]['qty'] * $itemsImported[$barangId]['hpp']) + ($qty * $hpp);
                $itemsImported[$barangId]['qty'] += $qty;
                $itemsImported[$barangId]['hpp'] = $itemsImported[$barangId]['qty'] > 0 ? ($totalVal / $itemsImported[$barangId]['qty']) : 0.00;
            } else {
                $itemsImported[$barangId] = [
                    'qty' => $qty,
                    'hpp' => $hpp,
                ];
            }
        }

        if (!empty($errors)) {
            $previewErrors = array_slice($errors, 0, 10);
            $remaining = count($errors) - count($previewErrors);
            if ($remaining > 0) {
                $previewErrors[] = "... dan {$remaining} kesalahan baris lainnya.";
            }
            return response()->json([
                'message' => "Terdapat " . count($errors) . " data yang tidak valid pada file Excel!",
                'errors'  => $previewErrors,
            ], 422);
        }

        if (empty($itemsImported)) {
            return response()->json([
                'message' => 'Tidak ada data barang dengan jumlah stok > 0 yang dapat diimport dari file Excel!',
            ], 422);
        }

        $submitAction = strtolower($request->input('submit_action', 'draft'));
        $targetStatus = $submitAction === 'pending' ? 'Pending' : 'Draft';

        try {
            DB::beginTransaction();

            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_opening_stock));
                $month  = date('m', strtotime($request->tanggal_opening_stock));
                $prefix = "OPN/{$year}/{$month}/";

                $latest = OpeningStock::withTrashed()
                    ->where('nomor_opening_stock', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('nomor_opening_stock', 'desc')
                    ->first();

                $number = 1;
                if ($latest) {
                    $lastPart = substr($latest->nomor_opening_stock, strlen($prefix));
                    $number = ((int) $lastPart) + 1;
                }

                do {
                    $nomorOpn = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
                    $exists = OpeningStock::withTrashed()->where('nomor_opening_stock', $nomorOpn)->exists();
                    if ($exists) {
                        $number++;
                    }
                } while ($exists);
            } else {
                $nomorOpn = $request->nomor_opening_stock;
            }

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            $openingStock = OpeningStock::create([
                'nomor_opening_stock'   => $nomorOpn,
                'tanggal_opening_stock' => $request->tanggal_opening_stock,
                'gudang_id'             => $gudang->id,
                'company_id'            => $targetCompanyId,
                'branch_id'             => $targetBranchId,
                'keterangan'            => $request->keterangan ? strip_tags($request->keterangan) : 'Import Excel',
                'status'                => $targetStatus,
            ]);

            $detailsData = [];
            $nowStr = now()->toDateTimeString();
            foreach ($itemsImported as $barangId => $data) {
                $detailsData[] = [
                    'opening_stock_id' => $openingStock->id,
                    'barang_id'        => $barangId,
                    'jumlah'           => $data['qty'],
                    'harga_pokok'      => $data['hpp'],
                    'created_at'       => $nowStr,
                    'updated_at'       => $nowStr,
                ];
            }
            foreach (array_chunk($detailsData, 500) as $chunk) {
                OpeningStockDetail::insert($chunk);
            }

            DB::commit();

            if ($targetStatus === 'Pending') {
                try {
                    WhatsAppGatewayController::sendOpeningStockSubmissionNotification($openingStock);
                } catch (\Exception $we) {
                    Log::warning('WA Notification on Import failed: ' . $we->getMessage());
                }
                $msg = "Import berhasil! {$nomorOpn} dengan " . count($itemsImported) . " item diajukan (Pending Approval).";
            } else {
                $msg = "Import berhasil! {$nomorOpn} disimpan sebagai Draft dengan " . count($itemsImported) . " item.";
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'nomor_opening_stock' => $nomorOpn,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OpeningStock Import Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Gagal memproses import: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DOWNLOAD TEMPLATE EXCEL
    // ══════════════════════════════════════════════════════════════
    public function downloadTemplate()
    {
        $this->authorize('tambah-opening-stock');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!$activeCompanyId && $activeBranchId) {
            $activeCompanyId = \App\Models\Branch::where('id', $activeBranchId)->value('company_id');
        }

        $query = MasterBarang::with(['satuan:id,nama_satuan'])
            ->select('id', 'kode_barang', 'nama_barang', 'satuan_id', 'status');

        if ($activeCompanyId) {
            $query->where(function($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)
                  ->orWhereNull('company_id');
            });
        }

        $query->where(function($q) {
            $q->whereIn('status', ['Active', 'active', 'Aktif', 'aktif', '1'])
              ->orWhereNull('status');
        });

        $masterBarang = $query->orderBy('kode_barang')->get();

        $rows = [
            ['Kode Barang', 'Nama Barang', 'Satuan', 'Jumlah Stok', 'HPP Awal (Rp Satuan)']
        ];

        if ($masterBarang->isNotEmpty()) {
            foreach ($masterBarang as $b) {
                $satuanNama = $b->satuan ? $b->satuan->nama_satuan : 'Pcs';
                $rows[] = [
                    $b->kode_barang,
                    $b->nama_barang,
                    $satuanNama,
                    '', // Jumlah Stok dikosongkan agar user tinggal isi angka fisiknya
                    ''  // HPP Awal dikosongkan agar user tinggal isi nominal HPP
                ];
            }
        } else {
            $rows[] = ['BRG001', 'Contoh Barang A', 'Pcs', '', 50000];
            $rows[] = ['BRG002', 'Contoh Barang B', 'Box', '', 120000];
        }

        $filename = 'template_import_saldo_awal_' . date('Ymd') . '.xlsx';
        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // EXPORT EXCEL — Seluruh Data Dokumen Saldo Awal
    // ══════════════════════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-opening-stock');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();

        $query = OpeningStock::with(['gudang.cabang', 'creator', 'details.barang.satuan'])
            ->forActiveCompany()
            ->forActiveBranch();

        if (!$isPrivileged) {
            $query->where(function($q) use ($user) {
                $q->where('opening_stocks.status', '!=', 'Draft')
                  ->orWhere('opening_stocks.created_by', $user->id);
            });
        }

        $openingStocks = $query->latest('tanggal_opening_stock')->get();

        if ($openingStocks->isEmpty()) {
            return $this->downloadTemplate();
        }

        $rows = [
            [
                'No', 'Nomor Dokumen', 'Tanggal', 'Gudang', 'Cabang', 'Status',
                'Kode Barang', 'Nama Barang', 'Satuan', 'Jumlah Stok', 'HPP Awal (Rp)', 'Total Nilai (Rp)',
                'Keterangan', 'Dibuat Oleh'
            ]
        ];

        $no = 1;
        foreach ($openingStocks as $os) {
            if ($os->details->isNotEmpty()) {
                foreach ($os->details as $d) {
                    $totalNilai = (float)$d->jumlah * (float)$d->harga_pokok;
                    $rows[] = [
                        $no++,
                        $os->nomor_opening_stock,
                        $os->tanggal_opening_stock ? date('d/m/Y', strtotime($os->tanggal_opening_stock)) : '-',
                        $os->gudang?->nama_gudang ?? '-',
                        $os->gudang?->cabang?->nama_cabang ?? '-',
                        $os->status,
                        $d->barang?->kode_barang ?? '-',
                        $d->barang?->nama_barang ?? '-',
                        $d->barang?->satuan?->nama_satuan ?? '-',
                        (float)$d->jumlah,
                        (float)$d->harga_pokok,
                        $totalNilai,
                        $os->keterangan ?? '-',
                        $os->creator?->nama_lengkap ?? ($os->creator?->name ?? '-')
                    ];
                }
            } else {
                $rows[] = [
                    $no++,
                    $os->nomor_opening_stock,
                    $os->tanggal_opening_stock ? date('d/m/Y', strtotime($os->tanggal_opening_stock)) : '-',
                    $os->gudang?->nama_gudang ?? '-',
                    $os->gudang?->cabang?->nama_cabang ?? '-',
                    $os->status,
                    '-', '-', '-', 0, 0, 0,
                    $os->keterangan ?? '-',
                    $os->creator?->nama_lengkap ?? ($os->creator?->name ?? '-')
                ];
            }
        }

        $filename = 'data_saldo_awal_' . date('Ymd_His') . '.xlsx';
        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // EXPORT EXCEL — Rincian 1 Dokumen Saldo Awal Spesifik
    // ══════════════════════════════════════════════════════════════
    public function exportDocExcel(OpeningStock $openingStock)
    {
        $this->authorize('lihat-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            abort(404, 'Dokumen saldo awal tidak ditemukan.');
        }

        $openingStock->load([
            'gudang.cabang',
            'creator',
            'details.barang.satuan',
        ]);

        $rows = [
            ['RINCIAN DOKUMEN SALDO AWAL PERSEDIAAN'],
            ['Nomor Dokumen', $openingStock->nomor_opening_stock],
            ['Tanggal', $openingStock->tanggal_opening_stock ? date('d/m/Y', strtotime($openingStock->tanggal_opening_stock)) : '-'],
            ['Gudang', $openingStock->gudang?->nama_gudang ?? '-'],
            ['Cabang', $openingStock->gudang?->cabang?->nama_cabang ?? '-'],
            ['Status', $openingStock->status],
            ['Keterangan', $openingStock->keterangan ?? '-'],
            ['Dibuat Oleh', $openingStock->creator?->nama_lengkap ?? ($openingStock->creator?->name ?? '-')],
            [''],
            ['No', 'Kode Barang', 'Nama Barang', 'Satuan', 'Jumlah Stok', 'HPP Awal (Rp)', 'Total Nilai (Rp)']
        ];

        $no = 1;
        $grandTotalNilai = 0;
        $totalQty = 0;
        foreach ($openingStock->details as $d) {
            $subtotal = (float)$d->jumlah * (float)$d->harga_pokok;
            $grandTotalNilai += $subtotal;
            $totalQty += (float)$d->jumlah;
            $rows[] = [
                $no++,
                $d->barang?->kode_barang ?? '-',
                $d->barang?->nama_barang ?? '-',
                $d->barang?->satuan?->nama_satuan ?? '-',
                (float)$d->jumlah,
                (float)$d->harga_pokok,
                $subtotal
            ];
        }

        $rows[] = ['', '', '', 'TOTAL', $totalQty, '', $grandTotalNilai];

        $safeNo = str_replace(['/', '\\'], '_', $openingStock->nomor_opening_stock);
        $filename = 'Saldo_Awal_' . $safeNo . '.xlsx';

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Detail (AJAX)
    // ══════════════════════════════════════════════════════════════
    public function show(OpeningStock $openingStock)
    {
        $this->authorize('lihat-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Saldo awal tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        $isCreator = $openingStock->created_by == $user->id;

        // Draft hanya bisa dilihat oleh pembuat atau Admin/Super Admin
        if ($openingStock->status === 'Draft' && !$isCreator && !$isPrivileged) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $openingStock->load([
            'gudang.cabang',
            'creator.karyawan',
            'updater.karyawan',
            'details.barang.satuan',
        ]);

        $jurnal = Jurnal::with(['details.account'])
            ->where('referensi_tipe', 'Saldo Awal')
            ->where('referensi_id', $openingStock->id)
            ->first();

        return response()->json([
            'openingStock' => $openingStock,
            'jurnal'       => $jurnal,
            'is_creator'   => $isCreator,
            'can_approve'  => $user->can('konfirmasi-opening-stock') || $isPrivileged,
            'can_edit'     => ($openingStock->status === 'Draft' && ($isCreator || $isPrivileged))
                           || ($openingStock->status === 'Pending' && ($isCreator || $isPrivileged))
                           || ($openingStock->status === 'Confirmed' && $isPrivileged),
            'is_privileged'=> $isPrivileged,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CONFIRM — Post starting balance to Inventory & Journal
    // ══════════════════════════════════════════════════════════════
    public function confirm(OpeningStock $openingStock)
    {
        $this->authorize('konfirmasi-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        if (!$user->can('konfirmasi-opening-stock') && !$isPrivileged) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menyetujui dokumen ini.'], 403);
        }

        if ($openingStock->status !== 'Pending' && $openingStock->status !== 'Draft') {
            return response()->json(['message' => 'Hanya dokumen berstatus Pending atau Draft yang dapat dikonfirmasi.'], 422);
        }

        try {
            DB::beginTransaction();

            // Lock header
            $openingStock = OpeningStock::where('id', $openingStock->id)->lockForUpdate()->first();
            if ($openingStock->status !== 'Pending' && $openingStock->status !== 'Draft') {
                DB::rollBack();
                return response()->json(['message' => 'Status transaksi sudah berubah.'], 422);
            }

            // Execute Stock Posting & Kartu Stok
            $this->executePostingStocks($openingStock);

            // Update status & recorded approver
            $openingStock->update([
                'status'     => 'Confirmed',
                'updated_by' => $user->id,
            ]);

            // Post Automatic Accounting Journal for Opening Stock
            $this->postJournalForOpeningStock($openingStock);

            DB::commit();

            // Send WhatsApp Approval notification
            WhatsAppGatewayController::sendOpeningStockApprovedNotification($openingStock, $user);

            return response()->json([
                'status'  => 'success',
                'message' => "Saldo awal {$openingStock->nomor_opening_stock} berhasil disetujui & dikonfirmasi! Stok fisik dan jurnal akuntansi telah terposting.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OpeningStock Confirm Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Delete Document
    // ══════════════════════════════════════════════════════════════
    public function destroy(OpeningStock $openingStock)
    {
        $this->authorize('hapus-opening-stock');

        if (!$this->resolveOpeningStock($openingStock)) {
            return response()->json(['message' => 'Saldo awal tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        $isPrivileged = $this->isPrivilegedUser();
        $isCreator = $openingStock->created_by == $user->id;

        if ($openingStock->status === 'Confirmed' && !$isPrivileged) {
            return response()->json([
                'message' => 'Hanya Admin/Super Admin yang dapat menghapus dokumen yang sudah Confirmed.',
            ], 403);
        }

        if (($openingStock->status === 'Draft' || $openingStock->status === 'Pending') && !$isCreator && !$isPrivileged) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        try {
            DB::beginTransaction();

            $openingStock = OpeningStock::where('id', $openingStock->id)->lockForUpdate()->first();

            // If confirmed, revert stock & journal before deleting
            if ($openingStock->status === 'Confirmed') {
                $this->revertOpeningStockPosting($openingStock);
            }

            $openingStock->details()->delete();
            $openingStock->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Dokumen saldo awal {$openingStock->nomor_opening_stock} berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OpeningStock Destroy Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // POSTING HELPER: Execute Posting to StokGudang & KartuStok
    // ══════════════════════════════════════════════════════════════
    private function executePostingStocks(OpeningStock $openingStock): void
    {
        $gudangId = $openingStock->gudang_id;
        $barangIds = $openingStock->details->pluck('barang_id')->unique()->toArray();

        // Bulk lock all existing stock records in one single query
        $existingStok = StokGudang::where('gudang_id', $gudangId)
            ->whereIn('barang_id', $barangIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('barang_id');

        $kartuStokData = [];
        $nowStr = now()->toDateTimeString();

        foreach ($openingStock->details as $detail) {
            $barangId = $detail->barang_id;
            $jumlahStr     = (string)$detail->jumlah;
            $hargaPokokStr = (string)$detail->harga_pokok;
            $nilaiPersediaanStr = DecimalHelper::mul($jumlahStr, $hargaPokokStr, 4);

            $stokGudang = $existingStok->get($barangId);

            if (!$stokGudang) {
                $stokGudang = StokGudang::create([
                    'barang_id'             => $barangId,
                    'gudang_id'             => $gudangId,
                    'stok_aktual'           => 0.00,
                    'nilai_persediaan'      => 0.0000,
                    'harga_pokok_rata_rata' => 0.000000,
                    'hpp_sebelumnya'        => 0.000000,
                    'harga_beli_terakhir'   => 0.000000,
                    'harga_beli_sebelumnya' => 0.000000,
                ]);
                $stokGudang = StokGudang::where('id', $stokGudang->id)->lockForUpdate()->first();
            }

            $hppAwalStr = (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');

            $stokGudang->hpp_sebelumnya        = $hppAwalStr;
            $stokGudang->harga_beli_sebelumnya = (string)($stokGudang->harga_beli_terakhir ?? '0.000000');
            $stokGudang->harga_beli_terakhir   = $hargaPokokStr;
            $stokGudang->harga_pokok_rata_rata = $hargaPokokStr;
            $stokGudang->stok_aktual           = $jumlahStr;
            $stokGudang->nilai_persediaan      = $nilaiPersediaanStr;
            $stokGudang->save();

            StokGudang::syncMasterHpp($barangId);

            $kartuStokData[] = [
                'barang_id'       => $barangId,
                'gudang_id'       => $gudangId,
                'tipe'            => 'Masuk',
                'jumlah'          => $jumlahStr,
                'stok_awal'       => '0.00',
                'stok_akhir'      => $jumlahStr,
                'harga_satuan'    => $hargaPokokStr,
                'nilai_total'     => $nilaiPersediaanStr,
                'referensi_jenis' => 'Saldo Awal',
                'referensi_id'    => $openingStock->id,
                'referensi_nomor' => $openingStock->nomor_opening_stock,
                'keterangan'      => 'Saldo Awal Persediaan',
                'created_at'      => $nowStr,
            ];
        }

        foreach (array_chunk($kartuStokData, 500) as $chunk) {
            KartuStok::insert($chunk);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // REVERT HELPER: Revert StokGudang, KartuStok, and Jurnal
    // ══════════════════════════════════════════════════════════════
    private function revertOpeningStockPosting(OpeningStock $openingStock): void
    {
        $gudangId = $openingStock->gudang_id;

        foreach ($openingStock->details as $detail) {
            $barangId = $detail->barang_id;
            $qtyRevert = (string)$detail->jumlah;
            $nilaiRevert = DecimalHelper::mul((string)$detail->jumlah, (string)$detail->harga_pokok, 4);

            $stokGudang = StokGudang::where('gudang_id', $gudangId)
                ->where('barang_id', $barangId)
                ->lockForUpdate()
                ->first();

            if ($stokGudang) {
                $newStok = DecimalHelper::sub((string)$stokGudang->stok_aktual, $qtyRevert, 2);
                if (DecimalHelper::lt($newStok, '0.00')) {
                    $newStok = '0.00';
                }

                $newNilai = DecimalHelper::sub((string)$stokGudang->nilai_persediaan, $nilaiRevert, 4);
                if (DecimalHelper::lt($newNilai, '0.0000')) {
                    $newNilai = '0.0000';
                }

                $stokGudang->stok_aktual = $newStok;
                $stokGudang->nilai_persediaan = $newNilai;
                if (DecimalHelper::gt($newStok, '0.00')) {
                    $stokGudang->harga_pokok_rata_rata = DecimalHelper::div($newNilai, $newStok, 6);
                }
                $stokGudang->save();

                StokGudang::syncMasterHpp($barangId);
            }
        }

        // Delete KartuStok
        KartuStok::where('referensi_jenis', 'Saldo Awal')
            ->where('referensi_id', $openingStock->id)
            ->delete();

        // Delete Jurnal
        $jurnal = Jurnal::where('referensi_tipe', 'Saldo Awal')
            ->where('referensi_id', $openingStock->id)
            ->first();

        if ($jurnal) {
            $jurnal->details()->delete();
            $jurnal->delete();
        }
    }

    // ══════════════════════════════════════════════════════════════
    // POST AUTOMATIC JOURNAL FOR OPENING STOCK
    // DEBIT : Persediaan Barang (per barang / kategori)
    // KREDIT: Modal / Ekuitas Saldo Awal
    // ══════════════════════════════════════════════════════════════
    private function postJournalForOpeningStock(OpeningStock $os): void
    {
        $existingJurnal = Jurnal::where('referensi_tipe', 'Saldo Awal')
            ->where('referensi_id', $os->id)
            ->exists();

        if ($existingJurnal) {
            return;
        }

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $coaCompanyId = $os->company_id 
            ?: ($os->gudang ? $os->gudang->company_id : null) 
            ?: ($activeCompanyId ?: 1);

        $totalNilai = 0;
        $groupedDebit = [];

        foreach ($os->details as $d) {
            $val = floatval($d->jumlah) * floatval($d->harga_pokok);
            if ($val <= 0) continue;

            $totalNilai += $val;

            // Determine inventory COA (DEBIT):
            // 1. Mapping Kategori Barang (e.g. cat_1_inventory)
            $catId = $d->barang ? $d->barang->kategori_barang_id : null;
            $invCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory", null, $coaCompanyId) : null;

            // 2. Mapping System Default Persediaan (default_inventory / gi_credit_inventory)
            if (!$invCoaId) {
                $invCoaId = AccountMappingHelper::getCoaId('goods_issue', 'gi_credit_inventory', null, $coaCompanyId)
                         ?? AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId);
            }

            // 3. Fallback COA Persediaan / Aset pada PT bersangkutan
            if (!$invCoaId) {
                $invCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                    ->whereIn('type', ['Aset', 'Asset', 'Persediaan', 'Inventory', 'ast1', 'asset'])
                    ->where('is_header', false)
                    ->value('id');
            }

            if (!$invCoaId) {
                throw new \InvalidArgumentException("Gagal membuat Jurnal Akuntansi! Pemetaan Akun (COA) Persediaan Barang belum diatur di sistem. Silakan atur 'Default Persediaan Barang' atau Mapping Kategori Barang terlebih dahulu di menu Setting -> Account Mapping System.");
            }

            if (!isset($groupedDebit[$invCoaId])) {
                $groupedDebit[$invCoaId] = [
                    'account_id' => $invCoaId,
                    'total_nilai'=> 0,
                    'item_count' => 0,
                    'total_qty'  => 0,
                    'sample_name'=> $d->barang ? $d->barang->nama_barang : 'Barang',
                ];
            }
            $groupedDebit[$invCoaId]['total_nilai'] += $val;
            $groupedDebit[$invCoaId]['item_count']  += 1;
            $groupedDebit[$invCoaId]['total_qty']   += floatval($d->jumlah);
        }

        if ($totalNilai <= 0) {
            return;
        }

        // Determine Equity / Opening Balance COA for KREDIT entry:
        // 1. Mapping System Default Saldo Awal / Ekuitas (default_opening_balance / default_equity)
        $equityCoaId = AccountMappingHelper::getCoaId('default', 'default_opening_balance', 'default_equity', $coaCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'default_equity', null, $coaCompanyId);

        // 2. Fallback COA Modal / Ekuitas pada PT bersangkutan
        if (!$equityCoaId) {
            $equityCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->where(function($q) {
                    $q->whereIn('type', ['Modal', 'Ekuitas', 'Equity', 'modal', 'ekuitas', 'equity'])
                      ->orWhere('code', 'like', '3-%')
                      ->orWhere('name', 'like', '%Modal%')
                      ->orWhere('name', 'like', '%Ekuitas%')
                      ->orWhere('name', 'like', '%Saldo Awal%');
                })
                ->where('is_header', false)
                ->value('id');
        }

        // 3. Fallback sembarang COA non-header jika belum ditemukan
        if (!$equityCoaId) {
            $equityCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->where('is_header', false)
                ->value('id');
        }

        if (!$equityCoaId) {
            throw new \InvalidArgumentException("Gagal membuat Jurnal Akuntansi! Pemetaan Akun (COA) Ekuitas Saldo Awal belum diatur. Silakan atur 'Default Ekuitas Saldo Awal (Modal)' terlebih dahulu di menu Setting -> Account Mapping System.");
        }

        // Auto Journal Number: JRN/YYYY/MM/XXXX (Collision-proof including soft-deletes)
        $year   = date('Y', strtotime($os->tanggal_opening_stock));
        $month  = date('m', strtotime($os->tanggal_opening_stock));
        $prefix = "JRN/{$year}/{$month}/";

        $nextNum = Jurnal::withTrashed()->where('nomor_jurnal', 'like', $prefix . '%')->count() + 1;
        do {
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            $exists = Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        $isBranch = !empty($activeBranchId) || !empty($os->branch_id) || ($os->gudang && !empty($os->gudang->cabang_id));
        $jurnalBranchId  = $isBranch ? ($os->branch_id ?: ($os->gudang ? $os->gudang->cabang_id : $activeBranchId)) : null;
        $jurnalCompanyId = $isBranch ? null : ($os->company_id ?: $activeCompanyId ?: ($os->gudang ? $os->gudang->company_id : 1));

        // Create Jurnal Header
        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $os->tanggal_opening_stock,
            'referensi_tipe' => 'Saldo Awal',
            'referensi_id'   => $os->id,
            'company_id'     => $jurnalCompanyId, // NULL ketika diinput oleh Cabang (Strict Branch Isolation)
            'branch_id'      => $jurnalBranchId,  // ID Cabang ketika di Cabang, NULL saat di PT Pusat
            'total_debit'    => $totalNilai,
            'total_kredit'   => $totalNilai,
            'keterangan'     => "Jurnal Saldo Awal Persediaan {$os->nomor_opening_stock}",
            'created_by'     => auth()->id(),
        ]);

        // Post DEBIT entries (Grouped per Account COA)
        foreach ($groupedDebit as $accId => $group) {
            $keteranganDebit = $group['item_count'] > 1
                ? "Saldo Awal Persediaan ({$group['item_count']} Item Barang)"
                : "Saldo Awal {$group['sample_name']}";

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $accId,
                'debit'      => $group['total_nilai'],
                'kredit'     => 0,
                'keterangan' => $keteranganDebit,
            ]);
        }

        // Post 1 KREDIT entry
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $equityCoaId,
            'debit'      => 0,
            'kredit'     => $totalNilai,
            'keterangan' => "Modal / Ekuitas Saldo Awal Persediaan {$os->nomor_opening_stock}",
        ]);
    }

    /**
     * Helper method to parse numeric string inputs with Indonesian / US formatting.
     */
    private function parseNumber($val): float
    {
        if (is_int($val) || is_float($val)) {
            return (float) $val;
        }
        if ($val === null || $val === '') {
            return 0.0;
        }

        $clean = trim((string)$val);
        if ($clean === '') return 0.0;

        $clean = preg_replace('/[^\d.,-]/', '', $clean);
        if ($clean === '') return 0.0;

        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $lastDot = strrpos($clean, '.');
            $lastComma = strrpos($clean, ',');
            if ($lastComma > $lastDot) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif (strpos($clean, '.') !== false) {
            $parts = explode('.', $clean);
            if (count($parts) > 2) {
                $clean = str_replace('.', '', $clean);
            } else {
                if (strlen($parts[1]) === 3 && strlen($parts[0]) <= 3) {
                    $clean = str_replace('.', '', $clean);
                }
            }
        } elseif (strpos($clean, ',') !== false) {
            $parts = explode(',', $clean);
            if (count($parts) > 2) {
                $clean = str_replace(',', '', $clean);
            } else {
                if (strlen($parts[1]) === 3 && strlen($parts[0]) <= 3) {
                    $clean = str_replace(',', '', $clean);
                } else {
                    $clean = str_replace(',', '.', $clean);
                }
            }
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }
}
