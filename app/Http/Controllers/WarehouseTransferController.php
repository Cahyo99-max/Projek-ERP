<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Helpers\DecimalHelper;
use App\Helpers\AccountMappingHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseTransferController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Resolve & verify source warehouse belongs to active company and active branch.
     */
    private function resolveGudangAsal(int $gudangId): ?MasterGudang
    {
        $isSuperAdmin = auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Owner') || auth()->user()->hasRole('Superadmin'));
        if ($isSuperAdmin) {
            return MasterGudang::where('id', $gudangId)->where('status', 'Active')->first();
        }

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        $query = MasterGudang::where('id', $gudangId)->where('status', 'Active');

        if ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        }
        if ($activeBranchId) {
            $query->where('cabang_id', $activeBranchId);
        }

        $res = $query->first();
        if (!$res && $activeCompanyId) {
            $res = MasterGudang::where('id', $gudangId)->where('company_id', $activeCompanyId)->where('status', 'Active')->first();
        }

        return $res ?: MasterGudang::where('id', $gudangId)->where('status', 'Active')->first();
    }

    /**
     * Resolve & verify destination warehouse belongs to active company or allow inter-company destination.
     */
    private function resolveGudangTujuan(int $gudangId): ?MasterGudang
    {
        return MasterGudang::where('id', $gudangId)->where('status', 'Active')->first();
    }

    /**
     * Apply strict multi-company and branch visibility security scope.
     * Rules:
     * 1. DRAFT & PENDING transfers: CAN ONLY be seen by the Owner/Sender (Sender PT / Sender Branch / Creator).
     * 2. CONFIRMED transfers: Can be seen by Super Admin, Sender PT/Branch, and Receiver PT/Branch.
     */
    private function applySecurityScope($query, $activeCompanyId, $activeBranchId, bool $isSuperAdmin): void
    {
        $userId = auth()->id();

        // STRICT PRIVACY: Status Draft HANYA BISA DILIHAT OLEH PEMBUATNYA (Super Admin pun tidak melihat draf milik user lain)
        $query->where(function ($q) use ($userId) {
            $q->where('status', '!=', 'Draft')
              ->orWhere(function ($draftQ) use ($userId) {
                  $draftQ->where('status', 'Draft')
                         ->where('created_by', $userId);
              });
        });

        if ($isSuperAdmin && !$activeBranchId && !$activeCompanyId) {
            return;
        }

        $query->where(function ($mainQuery) use ($activeCompanyId, $activeBranchId) {
            if ($activeBranchId) {
                // BRANCH CONTEXT:
                // As Sender (Gudang Asal = activeBranchId) -> any status
                // As Receiver (Gudang Tujuan = activeBranchId) -> only Confirmed transfers
                $mainQuery->whereHas('gudangAsal', function ($g) use ($activeBranchId) {
                    $g->where('cabang_id', $activeBranchId);
                })
                ->orWhere(function ($qRec) use ($activeBranchId) {
                    $qRec->where('status', 'Confirmed')
                         ->whereHas('gudangTujuan', function ($g) use ($activeBranchId) {
                             $g->where('cabang_id', $activeBranchId);
                         });
                });
            } elseif ($activeCompanyId) {
                // COMPANY CONTEXT:
                // As Sender (Gudang Asal = activeCompanyId) -> any status
                // As Receiver (Gudang Tujuan = activeCompanyId) -> only Confirmed transfers
                $mainQuery->whereHas('gudangAsal', function ($g) use ($activeCompanyId) {
                    $g->where('company_id', $activeCompanyId);
                })
                ->orWhere(function ($qRec) use ($activeCompanyId) {
                    $qRec->where('status', 'Confirmed')
                         ->whereHas('gudangTujuan', function ($g) use ($activeCompanyId) {
                             $g->where('company_id', $activeCompanyId);
                         });
                });
            } else {
                $mainQuery->where('created_by', auth()->id());
            }
        });
    }

    /**
     * Check if the authenticated user is allowed to access a specific draft/transfer.
     * STRICT PRIVACY: Draft status is strictly accessible ONLY by its creator.
     */
    private function canAccessTransfer(WarehouseTransfer $wt): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        if ($isSuperAdmin) return true;

        if ($wt->status === 'Draft') {
            return ((int) $wt->created_by === (int) $user->id) || $user->can('edit-transfer') || $user->can('tambah-transfer');
        }

        return true;
    }

    /**
     * Verify a WarehouseTransfer belongs to the active company or branch.
     */
    private function resolveTransfer(WarehouseTransfer $wt): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        if ($isSuperAdmin) {
            return true;
        }

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if ((int)$wt->created_by === (int)$user->id) {
            return true;
        }

        // Sender check (Gudang Asal)
        $isSender = false;
        if ($activeBranchId && $wt->gudangAsal && $wt->gudangAsal->cabang_id == $activeBranchId) {
            $isSender = true;
        } elseif ($activeCompanyId && $wt->gudangAsal && $wt->gudangAsal->company_id == $activeCompanyId) {
            $isSender = true;
        }

        if ($isSender) {
            return true;
        }

        // Receiver check (Gudang Tujuan) for Confirmed transfers
        if ($wt->isConfirmed()) {
            if ($activeBranchId && $wt->gudangTujuan && $wt->gudangTujuan->cabang_id == $activeBranchId) {
                return true;
            }
            if ($activeCompanyId && $wt->gudangTujuan && $wt->gudangTujuan->company_id == $activeCompanyId) {
                return true;
            }
        }

        return false;
    }

    private function canSeeHpp(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasRole('Super Admin')
            || $user->hasRole('Owner')
            || $user->hasRole('Admin')
            || $user->can('lihat-hpp-transfer')
            || $user->can('akses-accounting');
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — List all Transfers for active branch
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $this->authorize('lihat-transfer');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $isSuperAdmin    = auth()->user()->hasRole('Super Admin');

        if ($request->ajax()) {
            $query = WarehouseTransfer::with(['company', 'gudangAsal.company', 'gudangAsal.cabang', 'gudangTujuan.company', 'gudangTujuan.cabang', 'coaDebit', 'coaKredit', 'creator'])
                ->select('warehouse_transfers.*');

            $this->applySecurityScope($query, $activeCompanyId, $activeBranchId, $isSuperAdmin);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_transfer_fmt', function ($trf) {
                    return $trf->nomor_transfer;
                })
                ->addColumn('tanggal_fmt', function ($trf) {
                    return $trf->tanggal_transfer ? $trf->tanggal_transfer->format('d/m/Y') : '-';
                })
                ->addColumn('rute', function ($trf) {
                    $asalGudang = $trf->gudangAsal ? $trf->gudangAsal->nama_gudang : '-';
                    $asalPt = $trf->gudangAsal && $trf->gudangAsal->company ? $trf->gudangAsal->company->nama_perusahaan : '-';
                    $asalCabang = $trf->gudangAsal && $trf->gudangAsal->cabang ? $trf->gudangAsal->cabang->nama_cabang : 'Pusat (Head Office)';
                    
                    $tujuanGudang = $trf->gudangTujuan ? $trf->gudangTujuan->nama_gudang : '-';
                    $tujuanPt = $trf->gudangTujuan && $trf->gudangTujuan->company ? $trf->gudangTujuan->company->nama_perusahaan : '-';
                    $tujuanCabang = $trf->gudangTujuan && $trf->gudangTujuan->cabang ? $trf->gudangTujuan->cabang->nama_cabang : 'Pusat (Head Office)';
                    
                    $jenisTrf = $trf->jenis_transfer ?: (($asalPt === $tujuanPt) ? 'Intra-PT' : 'Inter-PT');
                    
                    return [
                        'asal_gudang'   => $asalGudang,
                        'asal_pt'       => $asalPt,
                        'asal_cabang'   => $asalCabang,
                        'tujuan_gudang' => $tujuanGudang,
                        'tujuan_pt'     => $tujuanPt,
                        'tujuan_cabang' => $tujuanCabang,
                        'jenis_transfer'=> $jenisTrf,
                    ];
                })
                ->addColumn('status_badge', function ($trf) {
                    if ($trf->status === 'Draft') {
                        return '<span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } elseif ($trf->status === 'Pending') {
                        return '<span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span>';
                    } else {
                        return '<span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>';
                    }
                })
                ->addColumn('creator_name', function ($trf) {
                    return $trf->creator ? $trf->creator->nama_lengkap : '-';
                })
                ->addColumn('action', function ($trf) {
                    $isSuperAdmin = auth()->check() && (
                        auth()->user()->hasRole('Super Admin') || 
                        auth()->user()->hasRole('Superadmin') || 
                        auth()->user()->hasRole('superadmin') ||
                        auth()->user()->hasRole('Owner') ||
                        auth()->user()->hasRole('Admin') ||
                        (property_exists(auth()->user(), 'role') && strtolower(strval(auth()->user()->role)) === 'superadmin')
                    );
                    $isCreator = auth()->check() && ((int) $trf->created_by === (int) auth()->id());

                    $btnDetail = '<button class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="event.stopPropagation(); showDetailTransfer('.$trf->id.')" title="Detail"><i class="bi bi-eye"></i></button>';

                    // Edit:
                    // - Draft: pembuatnya, Admin, atau Super Admin
                    // - Pending: pembuatnya, user dengan izin edit/tambah, Admin, atau Super Admin
                    // - Confirmed: Admin atau Super Admin (Admin Override)
                    $btnEdit = '';
                    $canEdit = $isSuperAdmin || (
                        ($trf->status === 'Draft' && $isCreator) ||
                        ($trf->status === 'Pending' && ($isCreator || auth()->user()->can('edit-transfer') || auth()->user()->can('tambah-transfer')))
                    );

                    if ($canEdit) {
                        $editTitle = $trf->status === 'Confirmed' ? 'Edit Transfer (Admin Override)' : ($trf->status === 'Pending' ? 'Edit Pengajuan Transfer' : 'Edit Draf');
                        $btnEdit = '<button class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width: 30px; height: 30px; padding: 0;" onclick="event.stopPropagation(); editTransfer('.$trf->id.')" title="'.$editTitle.'"><i class="bi bi-pencil"></i></button>';
                    }

                    // Delete:
                    // - Draft: hanya pembuatnya atau Super Admin
                    // - Pending: HILANG (kecuali Super Admin)
                    // - Confirmed: HILANG (kecuali Super Admin)
                    $btnDelete = '';
                    $canDelete = (
                        ($trf->status === 'Draft' && ($isCreator || $isSuperAdmin)) ||
                        ($trf->status === 'Pending' && $isSuperAdmin) ||
                        ($trf->status === 'Confirmed' && $isSuperAdmin)
                    ) && auth()->user()->can('hapus-transfer');

                    if ($canDelete) {
                        $btnDelete = '<button class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="event.stopPropagation(); hapusTransfer('.$trf->id.', \''.$trf->nomor_transfer.'\')" title="Hapus"><i class="bi bi-trash3"></i></button>';
                    }

                    return '<div class="d-inline-flex align-items-center" style="white-space: nowrap;">' . $btnDetail . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Source warehouses must strictly belong to active branch (if activeBranchId set) or active company head office (if activeBranchId is null)
        $gudangsAsalQuery = MasterGudang::with(['company', 'cabang'])
            ->where('status', 'Active');
        if ($activeBranchId) {
            $gudangsAsalQuery->where('cabang_id', $activeBranchId);
        } elseif ($activeCompanyId) {
            $gudangsAsalQuery->where('company_id', $activeCompanyId)->whereNull('cabang_id');
        }
        $gudangsAsal = $gudangsAsalQuery->orderBy('nama_gudang')->get();

        // Destination warehouses: can be any active warehouse across all PTs (so Inter-PT transfer is supported)
        $gudangsTujuan = MasterGudang::with(['company', 'cabang'])
            ->where('status', 'Active')
            ->orderBy('nama_gudang')
            ->get();

        $barangs = MasterBarang::active()
            ->with('satuan')
            ->orderBy('nama_barang')
            ->get();

        $coas = ChartOfAccount::forActiveCompany()
            ->detailOnly()
            ->active()
            ->orderBy('code')
            ->get();

        $defaultCoaDebitId = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_debit')
            ?? AccountMappingHelper::getCoaId('default', 'default_ar');

        $defaultCoaKreditId = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_kredit');

        $barangsData = $barangs->map(function ($b) {
            return [
                'id'    => $b->id,
                'kode'  => $b->kode_barang,
                'nama'  => $b->nama_barang,
                'satuan'=> $b->satuan ? $b->satuan->nama_satuan : '-',
            ];
        })->values();

        $transfersCountQuery = WarehouseTransfer::query();
        $this->applySecurityScope($transfersCountQuery, $activeCompanyId, $activeBranchId, $isSuperAdmin);
        $transfersCount = $transfersCountQuery->count();

        $canSeeHpp = $this->canSeeHpp();

        return view('modul_inventori.transfer_gudang.viewtransfer', compact('transfersCount', 'gudangsAsal', 'gudangsTujuan', 'barangs', 'barangsData', 'coas', 'defaultCoaDebitId', 'defaultCoaKreditId', 'canSeeHpp'));
    }

    // ══════════════════════════════════════════════════════════════
    // GET STOK — AJAX endpoint: real-time stok for a barang in source gudang
    // ══════════════════════════════════════════════════════════════
    public function getStokBarang(int $gudangId, int $barangId)
    {
        $this->authorize('lihat-transfer');

        $gudang = $this->resolveGudangAsal($gudangId);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang asal tidak ditemukan atau bukan milik cabang aktif.'], 403);
        }

        $barang = MasterBarang::active()->with('satuan')->find($barangId);
        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan atau tidak aktif.'], 404);
        }

        $stokG = StokGudang::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->first();

        $stok = $stokG ? floatval($stokG->stok_aktual) : 0.00;
        $hpp = ($stokG && $this->canSeeHpp()) ? floatval($stokG->harga_pokok_rata_rata) : 0.00;

        return response()->json([
            'stok_aktual'      => $stok,
            'harga_pokok'      => $hpp,
            'satuan'           => $barang->satuan ? $barang->satuan->nama_satuan : '-',
            'izinkan_negatif'  => (bool) $gudang->izinkan_stok_negatif,
            'can_see_hpp'      => $this->canSeeHpp(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // GET GUDANG ITEMS — AJAX endpoint: active items with positive stock in a warehouse
    // ══════════════════════════════════════════════════════════════
    public function getGudangItems(int $gudangId)
    {
        $this->authorize('lihat-transfer');

        $gudang = $this->resolveGudangAsal($gudangId);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang asal tidak ditemukan atau bukan milik cabang aktif.'], 403);
        }

        $stokGudang = StokGudang::where('gudang_id', $gudangId)
            ->where('stok_aktual', '>', 0)
            ->whereHas('barang', function ($query) {
                $query->where('status', 'Active');
            })
            ->with(['barang.satuan', 'barang.kategori'])
            ->get();

        $canSeeHpp = $this->canSeeHpp();
        $items = $stokGudang->map(function ($sg) use ($canSeeHpp) {
            $catId = $sg->barang ? ($sg->barang->kategori_barang_id ?? $sg->barang->kategori_id) : null;
            $catCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null;
            if (!$catCoaId) {
                $catCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory');
            }

            return [
                'id'          => $sg->barang_id,
                'kode'        => $sg->barang ? $sg->barang->kode_barang : '-',
                'nama'        => $sg->barang ? $sg->barang->nama_barang : '-',
                'satuan'      => $sg->barang && $sg->barang->satuan ? $sg->barang->satuan->nama_satuan : '-',
                'stok_aktual' => floatval($sg->stok_aktual),
                'harga_pokok' => $canSeeHpp ? floatval($sg->harga_pokok_rata_rata) : 0.00,
                'kategori_id' => $catId,
                'cat_coa_id'  => $catCoaId,
            ];
        })->values();

        return response()->json([
            'items' => $items,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // GET COMPANY COAS — AJAX endpoint: fetch COAs for specific company ID
    // ══════════════════════════════════════════════════════════════
    public function getCompanyCoas(int $companyId)
    {
        $this->authorize('lihat-transfer');

        $coas = ChartOfAccount::where('company_id', $companyId)
            ->detailOnly()
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $defaultDebit = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_debit')
            ?? AccountMappingHelper::getCoaId('default', 'default_ar');

        $defaultKredit = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_kredit');

        return response()->json([
            'coas'           => $coas,
            'default_debit'  => $defaultDebit,
            'default_kredit' => $defaultKredit,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Create a new Warehouse Transfer (Draft)
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $this->authorize('tambah-transfer');

        Log::info('WT Store Request: ', $request->all());

        try {
            $request->validate([
                'tanggal_transfer' => 'required|date',
                'gudang_asal_id'   => 'required|integer|exists:master_gudang,id',
                'gudang_tujuan_id' => 'required|integer|exists:master_gudang,id',
                'keterangan'       => 'nullable|string|max:40',
                'auto_number'      => 'required|boolean',
                'nomor_transfer'   => 'nullable|required_if:auto_number,0|string|max:20',
                'jenis_transfer'   => 'nullable|in:Intra-PT,Inter-PT',
                'coa_debit_id'     => 'nullable|integer|exists:chart_of_accounts,id',
                'coa_kredit_id'    => 'nullable|integer|exists:chart_of_accounts,id',
                'items'            => 'required|array|min:1|max:25',
                'items.*.barang_id'=> 'required|integer|exists:master_barang,id',
                'items.*.jumlah'   => 'required|numeric|min:0.01',
            ], [
                'keterangan.max'     => 'Keterangan tidak boleh melebihi 40 karakter.',
                'nomor_transfer.max' => 'Nomor transfer tidak boleh melebihi 20 karakter.',
                'items.max'          => 'Jumlah barang transfer tidak boleh melebihi 25 item.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        // Logic check: source cannot be same as destination
        if ($request->gudang_asal_id == $request->gudang_tujuan_id) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => [
                    'gudang_tujuan_id' => ['Gudang tujuan tidak boleh sama dengan Gudang asal!']
                ],
            ], 422);
        }

        // Verify warehouses
        $gudangAsal = $this->resolveGudangAsal((int) $request->gudang_asal_id);
        $gudangTujuan = $this->resolveGudangTujuan((int) $request->gudang_tujuan_id);
        if (!$gudangAsal) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_asal_id' => ['Gudang asal tidak valid atau bukan milik cabang aktif Anda!']],
            ], 422);
        }
        if (!$gudangTujuan) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_tujuan_id' => ['Gudang tujuan tidak valid atau tidak aktif!']],
            ], 422);
        }

        // Verify unique manual transfer number
        if (!$request->auto_number) {
            $exists = WarehouseTransfer::where('nomor_transfer', $request->nomor_transfer)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors'  => ['nomor_transfer' => ['Nomor Transfer Gudang tersebut sudah terdaftar!']],
                ], 422);
            }
        }

        // Group duplicate items
        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            if (isset($itemsGrouped[$bid])) {
                $itemsGrouped[$bid] += floatval($item['jumlah']);
            } else {
                $itemsGrouped[$bid] = floatval($item['jumlah']);
            }
        }

        // Verify active items, target company Master Barang existence, and stock sufficiency
        $targetCompanyId   = $gudangTujuan->company_id;
        $targetCompanyNama = $gudangTujuan->company ? $gudangTujuan->company->nama_perusahaan : 'PT Tujuan';
        $targetCabangNama  = $gudangTujuan->cabang ? $gudangTujuan->cabang->nama_cabang : 'Cabang Utama';

        $sourceCompanyNama = $gudangAsal->company ? $gudangAsal->company->nama_perusahaan : 'PT Asal';
        $sourceCabangNama  = $gudangAsal->cabang ? $gudangAsal->cabang->nama_cabang : 'Cabang Utama';

        foreach ($itemsGrouped as $barangId => $jumlah) {
            $sourceBarang = MasterBarang::find($barangId);
            if (!$sourceBarang || $sourceBarang->status !== 'Active') {
                return response()->json([
                    'message' => "Barang ID #{$barangId} tidak ditemukan atau tidak aktif di Master Barang.",
                ], 422);
            }

            // Security Check: Verify item strictly exists in Master Barang of target company by KODE BARANG and NAMA BARANG
            if ($targetCompanyId && $targetCompanyId != $gudangAsal->company_id) {
                // Step A: Check Kode Barang existence
                $targetBarang = MasterBarang::where('company_id', $targetCompanyId)
                    ->where('status', 'Active')
                    ->where(DB::raw('LOWER(TRIM(kode_barang))'), strtolower(trim($sourceBarang->kode_barang)))
                    ->first();

                if (!$targetBarang) {
                    return response()->json([
                        'message' => "Pemberitahuan Keamanan Inter-PT: Kode barang '{$sourceBarang->kode_barang}' ({$sourceBarang->nama_barang}) belum terdaftar pada Katalog Master Barang milik {$targetCompanyNama} ({$targetCabangNama}). Mohon daftarkan kode barang '{$sourceBarang->kode_barang}' terlebih dahulu di Master Barang PT Tujuan sebelum melanjutkan transaksi transfer.",
                    ], 422);
                }

                // Step B: Check Nama Barang consistency (case-insensitive & trim whitespace)
                $sourceNamaClean = strtolower(trim($sourceBarang->nama_barang));
                $targetNamaClean = strtolower(trim($targetBarang->nama_barang));

                if ($sourceNamaClean !== $targetNamaClean) {
                    return response()->json([
                        'message' => "Pemberitahuan Keamanan Inter-PT: Kode barang '{$sourceBarang->kode_barang}' terdaftar dengan Nama Barang yang berbeda pada PT Tujuan {$targetCompanyNama} ({$targetCabangNama}).\n\n- Nama di PT Asal: '{$sourceBarang->nama_barang}'\n- Nama di PT Tujuan: '{$targetBarang->nama_barang}'\n\nMohon samakan Kode Barang dan Nama Barang pada Katalog Master Barang kedua PT terlebih dahulu.",
                    ], 422);
                }
            }

            // Verify stock sufficiency in source warehouse
            $stokGudangAsal = StokGudang::where('barang_id', $barangId)
                ->where('gudang_id', $gudangAsal->id)
                ->first();
            $stokAktualAsal = $stokGudangAsal ? floatval($stokGudangAsal->stok_aktual) : 0;

            if ($stokAktualAsal < $jumlah) {
                return response()->json([
                    'message' => "Stok tidak mencukupi untuk '{$sourceBarang->nama_barang}' pada Gudang Asal '{$gudangAsal->nama_gudang}' ({$sourceCompanyNama} - {$sourceCabangNama}). Stok tersedia: {$stokAktualAsal}, jumlah diminta: {$jumlah}.",
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Auto-generate transfer number
            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_transfer));
                $month  = date('m', strtotime($request->tanggal_transfer));
                $prefix = "TRF/{$year}/{$month}/";

                $latest = WarehouseTransfer::where('nomor_transfer', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('nomor_transfer', 'desc')
                    ->first();

                $number  = !$latest ? 1 : ((int) substr($latest->nomor_transfer, strlen($prefix))) + 1;
                $nomorTrf = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            } else {
                $nomorTrf = $request->nomor_transfer;
            }

            // Detect inter-company transfer
            $isInterCompany = ($gudangAsal->company_id != $gudangTujuan->company_id);
            $jenisTransfer = $isInterCompany ? 'Inter-PT' : 'Intra-PT';

            // Validate required COA for Inter-PT transfer
            if ($isInterCompany) {
                if (!$request->coa_debit_id || !$request->coa_kredit_id) {
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors'  => [
                            'coa_debit_id'  => ['Transfer Antar PT (Inter-PT) wajib memilih Akun Debit & Kredit Jurnal COA!'],
                            'coa_kredit_id' => ['Transfer Antar PT (Inter-PT) wajib memilih Akun Debit & Kredit Jurnal COA!'],
                        ],
                    ], 422);
                }
                if ($request->coa_debit_id == $request->coa_kredit_id) {
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors'  => [
                            'coa_debit_id'  => ['Akun Debit (Piutang Intercompany) dan Akun Kredit (Persediaan) tidak boleh sama!'],
                            'coa_kredit_id' => ['Akun Debit (Piutang Intercompany) dan Akun Kredit (Persediaan) tidak boleh sama!'],
                        ],
                    ], 422);
                }
            }

            // Create transfer header
            $activeCompanyId = session('active_company_id') ?? ($gudangAsal->company_id ?? 1);
            $activeBranchId  = session('active_branch_id') ?? ($gudangAsal->cabang_id ?? 1);

            $submitAction = strtolower($request->input('submit_action', 'pending'));
            $statusToSet  = ($submitAction === 'draft') ? 'Draft' : 'Pending';

            $transfer = WarehouseTransfer::create([
                'company_id'       => $activeCompanyId,
                'branch_id'        => $activeBranchId,
                'nomor_transfer'   => $nomorTrf,
                'tanggal_transfer' => $request->tanggal_transfer,
                'gudang_asal_id'   => $gudangAsal->id,
                'gudang_tujuan_id' => $gudangTujuan->id,
                'keterangan'       => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'           => $statusToSet,
                'jenis_transfer'   => $jenisTransfer,
                'coa_debit_id'     => $isInterCompany ? $request->coa_debit_id : null,
                'coa_kredit_id'    => $isInterCompany ? $request->coa_kredit_id : null,
            ]);

            // Save details
            foreach ($itemsGrouped as $barangId => $jumlah) {
                WarehouseTransferDetail::create([
                    'transfer_id' => $transfer->id,
                    'barang_id'   => $barangId,
                    'jumlah'      => $jumlah,
                ]);
            }

            if ($statusToSet === 'Pending') {
                $msg = "Pengajuan Transfer Gudang {$nomorTrf} berhasil dibuat! Status PENDING (Menunggu Persetujuan/Konfirmasi).";
            } else {
                $msg = "Transfer Gudang {$nomorTrf} berhasil disimpan sebagai Draft!";
            }

            DB::commit();

            // Send WhatsApp Notification asynchronously without blocking response
            $transferId = $transfer->id;
            $statusVal  = $statusToSet;
            $approverId = auth()->id();
            WhatsAppGatewayController::runAsync(function() use ($transferId, $statusVal, $approverId) {
                $trf = \App\Models\WarehouseTransfer::find($transferId);
                $approver = $approverId ? \App\Models\User::find($approverId) : null;
                if ($trf) {
                    if ($statusVal === 'Confirmed') {
                        WhatsAppGatewayController::sendTransferApprovedNotification($trf, $approver);
                    } elseif ($statusVal === 'Pending') {
                        WhatsAppGatewayController::sendTransferSubmissionNotification($trf);
                    }
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'nomor_transfer' => $nomorTrf,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WT Store Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => $e->getMessage() ?: 'Terjadi kesalahan server. Silakan coba lagi.',
            ], 422);
        }
    }

    /**
     * Internal helper to execute stock deduction from source warehouse, stock addition to destination warehouse,
     * and KartuStok logging for a confirmed transfer.
     */
    private function executeStockPostingInternal(WarehouseTransfer $warehouseTransfer): void
    {
        $warehouseTransfer->load(['details', 'gudangAsal.cabang', 'gudangTujuan.cabang']);
        $gudangAsal = $warehouseTransfer->gudangAsal;
        $gudangTujuan = $warehouseTransfer->gudangTujuan;

        // Step 1: Pessimistic locking & stock sufficiency check
        foreach ($warehouseTransfer->details as $detail) {
            $stokGudangAsal = StokGudang::where('barang_id', $detail->barang_id)
                ->where('gudang_id', $warehouseTransfer->gudang_asal_id)
                ->lockForUpdate()
                ->first();

            $stokAktualAsal = $stokGudangAsal ? floatval($stokGudangAsal->stok_aktual) : 0;

            if ($stokAktualAsal < $detail->jumlah) {
                $barang = MasterBarang::find($detail->barang_id);
                $namaBarang = $barang ? $barang->nama_barang : "ID #{$detail->barang_id}";
                throw new \Exception("Stok tidak cukup untuk '{$namaBarang}' di gudang asal '{$gudangAsal->nama_gudang}'! Stok tersedia: {$stokAktualAsal}, diminta: {$detail->jumlah}.");
            }
        }

        // Step 2: Perform stock movement and log mutations
        foreach ($warehouseTransfer->details as $detail) {
            // ── A. DEDUCT STOCK FROM SOURCE ──
            $stokAsal = StokGudang::firstOrCreate(
                ['barang_id' => $detail->barang_id, 'gudang_id' => $warehouseTransfer->gudang_asal_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );
            $stokAwalAsalStr  = (string)($stokAsal->stok_aktual ?? '0');
            $nilaiLamaAsalStr = (string)($stokAsal->nilai_persediaan ?? DecimalHelper::mul($stokAwalAsalStr, (string)($stokAsal->harga_pokok_rata_rata ?? '0'), 4));
            $qtyTransferStr   = (string)$detail->jumlah;
            $avgCostAsalStr   = (string)($stokAsal->harga_pokok_rata_rata ?? '0.000000');

            $nilaiTransferStr            = DecimalHelper::mul($qtyTransferStr, $avgCostAsalStr, 4);
            $nilaiPersediaanAsalBaruStr  = max('0.0000', DecimalHelper::sub($nilaiLamaAsalStr, $nilaiTransferStr, 4));
            $stokAkhirAsalStr            = max('0.00', DecimalHelper::sub($stokAwalAsalStr, $qtyTransferStr, 2));

            $stokAsal->stok_aktual           = $stokAkhirAsalStr;
            $stokAsal->nilai_persediaan      = $nilaiPersediaanAsalBaruStr;
            $stokAsal->harga_pokok_rata_rata = DecimalHelper::comp($stokAkhirAsalStr, '0', 4) > 0 ? $avgCostAsalStr : '0.000000';
            $stokAsal->save();

            // Determine transfer subtype (Inter-PT, Inter-Cabang, Internal)
            $isInterCompany = ($gudangAsal->company_id && $gudangTujuan->company_id && $gudangAsal->company_id != $gudangTujuan->company_id);
            $isInterBranch  = (!$isInterCompany && $gudangAsal->cabang_id != $gudangTujuan->cabang_id);

            $refJenisTransfer = 'Transfer Internal';
            if ($isInterCompany) {
                $refJenisTransfer = 'Transfer Inter-PT';
            } elseif ($isInterBranch) {
                $refJenisTransfer = 'Transfer Inter-Cabang';
            }

            $ptTujuanName  = $gudangTujuan->company ? $gudangTujuan->company->nama_perusahaan : '';
            $cabTujuanName = $gudangTujuan->cabang ? $gudangTujuan->cabang->nama_cabang : '';
            $ptAsalName    = $gudangAsal->company ? $gudangAsal->company->nama_perusahaan : '';
            $cabAsalName   = $gudangAsal->cabang ? $gudangAsal->cabang->nama_cabang : '';

            $ketKeluar = $isInterCompany
                ? "Transfer Inter-PT (Ke PT {$ptTujuanName} - {$gudangTujuan->nama_gudang})"
                : ($isInterBranch
                    ? "Transfer Inter-Cabang (Ke Cabang {$cabTujuanName} - {$gudangTujuan->nama_gudang})"
                    : "Transfer Internal (Ke Gudang {$gudangTujuan->nama_gudang})");

            $ketMasuk = $isInterCompany
                ? "Transfer Inter-PT (Dari PT {$ptAsalName} - {$gudangAsal->nama_gudang})"
                : ($isInterBranch
                    ? "Transfer Inter-Cabang (Dari Cabang {$cabAsalName} - {$gudangAsal->nama_gudang})"
                    : "Transfer Internal (Dari Gudang {$gudangAsal->nama_gudang})");

            // Log to Kartu Stok for Source Gudang (Keluar)
            KartuStok::create([
                'barang_id'       => $detail->barang_id,
                'gudang_id'       => $warehouseTransfer->gudang_asal_id,
                'tipe'            => 'Keluar',
                'jumlah'          => $qtyTransferStr,
                'stok_awal'       => $stokAwalAsalStr,
                'stok_akhir'      => $stokAkhirAsalStr,
                'harga_satuan'    => $avgCostAsalStr,
                'nilai_total'     => $nilaiTransferStr,
                'referensi_jenis' => $refJenisTransfer,
                'referensi_id'    => $warehouseTransfer->id,
                'referensi_nomor' => $warehouseTransfer->nomor_transfer,
                'keterangan'      => $ketKeluar,
            ]);

            // Resolve target company's matching barang_id strictly by KODE BARANG and NAMA BARANG for Inter-PT transfer
            $targetBarangId = $detail->barang_id;
            $sourceBarang   = MasterBarang::find($detail->barang_id);
            if ($sourceBarang && $gudangTujuan->company_id && $gudangTujuan->company_id != $gudangAsal->company_id) {
                $targetBarangMatch = MasterBarang::where('company_id', $gudangTujuan->company_id)
                    ->where('status', 'Active')
                    ->where(DB::raw('LOWER(TRIM(kode_barang))'), strtolower(trim($sourceBarang->kode_barang)))
                    ->first();

                if ($targetBarangMatch) {
                    $sourceNamaClean = strtolower(trim($sourceBarang->nama_barang));
                    $targetNamaClean = strtolower(trim($targetBarangMatch->nama_barang));

                    if ($sourceNamaClean === $targetNamaClean) {
                        $targetBarangId = $targetBarangMatch->id;
                    }
                }
            }

            // ── B. ADD STOCK TO DESTINATION ──
            $stokTujuan = StokGudang::where('barang_id', $targetBarangId)
                ->where('gudang_id', $warehouseTransfer->gudang_tujuan_id)
                ->lockForUpdate()
                ->first();

            if (!$stokTujuan) {
                $stokTujuan = StokGudang::create([
                    'barang_id' => $targetBarangId,
                    'gudang_id' => $warehouseTransfer->gudang_tujuan_id,
                    'stok_aktual' => 0.00,
                    'nilai_persediaan' => 0.0000,
                    'harga_pokok_rata_rata' => 0.000000
                ]);
                $stokTujuan = StokGudang::where('id', $stokTujuan->id)->lockForUpdate()->first();
            }

            $stokAwalTujuanStr    = (string)($stokTujuan->stok_aktual ?? '0');
            $nilaiLamaTujuanStr   = (string)($stokTujuan->nilai_persediaan ?? DecimalHelper::mul($stokAwalTujuanStr, (string)($stokTujuan->harga_pokok_rata_rata ?? '0'), 4));
            $nilaiPersediaanTujuanBaruStr = DecimalHelper::add($nilaiLamaTujuanStr, $nilaiTransferStr, 4);
            $stokAkhirTujuanStr   = DecimalHelper::add($stokAwalTujuanStr, $qtyTransferStr, 2);

            $newAvgCostTujuanStr  = DecimalHelper::comp($stokAkhirTujuanStr, '0', 4) > 0 
                ? DecimalHelper::div($nilaiPersediaanTujuanBaruStr, $stokAkhirTujuanStr, 6) 
                : $avgCostAsalStr;

            $stokTujuan->stok_aktual           = $stokAkhirTujuanStr;
            $stokTujuan->nilai_persediaan      = $nilaiPersediaanTujuanBaruStr;
            $stokTujuan->hpp_sebelumnya        = (string)($stokTujuan->harga_pokok_rata_rata ?? '0.000000');
            $stokTujuan->harga_pokok_rata_rata = $newAvgCostTujuanStr;
            $stokTujuan->save();

            StokGudang::syncMasterHpp($targetBarangId);

            // Log to Kartu Stok for Destination Gudang (Masuk)
            KartuStok::create([
                'barang_id'       => $targetBarangId,
                'gudang_id'       => $warehouseTransfer->gudang_tujuan_id,
                'tipe'            => 'Masuk',
                'jumlah'          => $qtyTransferStr,
                'stok_awal'       => $stokAwalTujuanStr,
                'stok_akhir'      => $stokAkhirTujuanStr,
                'harga_satuan'    => $avgCostAsalStr,
                'nilai_total'     => $nilaiTransferStr,
                'referensi_jenis' => $refJenisTransfer,
                'referensi_id'    => $warehouseTransfer->id,
                'referensi_nomor' => $warehouseTransfer->nomor_transfer,
                'keterangan'      => $ketMasuk,
            ]);
        }

        // ── Step 3: Automatic Jurnal Posting ──
        $this->generateJurnalForTransfer($warehouseTransfer);
    }

    /**
     * Generate or update Jurnal & JurnalDetail for a Confirmed WarehouseTransfer
     */
    private function generateJurnalForTransfer(WarehouseTransfer $warehouseTransfer): ?Jurnal
    {
        $warehouseTransfer->load(['details', 'gudangAsal.company', 'gudangAsal.cabang', 'gudangTujuan.company', 'gudangTujuan.cabang']);
        $gudangAsal   = $warehouseTransfer->gudangAsal;
        $gudangTujuan = $warehouseTransfer->gudangTujuan;

        // Delete old Jurnal entries if re-posting/updating (forceDelete to clear unique nomor_jurnal constraint)
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Transfer Gudang')
            ->where('referensi_id', $warehouseTransfer->id)
            ->get();
        foreach ($existingJurnals as $existingJurnal) {
            $existingJurnal->details()->delete();
            $existingJurnal->forceDelete();
        }

        // ATURAN AKUNTANSI STANDAR:
        // Transfer Intra-PT (Antar Gudang / Antar Cabang dalam 1 PT yang sama) TIDAK MEMBUAT JURNAL KEUANGAN,
        // karena hanya mutasi fisik barang (kartu stok) dan total nilai persediaan PT tetap sama.
        // HANYA Transfer Inter-PT (Antar PT / Beda Badan Hukum) yang membuat Jurnal Akuntansi Intercompany.
        $isInterCompany = ($warehouseTransfer->jenis_transfer === 'Inter-PT') || ($gudangAsal && $gudangTujuan && $gudangAsal->company_id && $gudangTujuan->company_id && $gudangAsal->company_id != $gudangTujuan->company_id);

        if (!$isInterCompany) {
            return null;
        }

        // Calculate total transfer value from KartuStok (Keluar) or StokGudang (Batch Query Optimized)
        $totalNilaiTransfer = '0.0000';
        $barangIds = $warehouseTransfer->details->pluck('barang_id')->toArray();

        $kartuStokMap = KartuStok::where('referensi_jenis', 'Transfer Gudang')
            ->where('referensi_id', $warehouseTransfer->id)
            ->where('tipe', 'Keluar')
            ->whereIn('barang_id', $barangIds)
            ->get()
            ->keyBy('barang_id');

        $stokGudangMap = StokGudang::where('gudang_id', $warehouseTransfer->gudang_asal_id)
            ->whereIn('barang_id', $barangIds)
            ->get()
            ->keyBy('barang_id');

        // Resolve Debit & Kredit COA Accounts
        $debitCoaId  = $warehouseTransfer->coa_debit_id;
        $kreditCoaId = $warehouseTransfer->coa_kredit_id;

        if (!$debitCoaId || $debitCoaId == $kreditCoaId) {
            $debitCoaId = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_debit')
                ?? AccountMappingHelper::getCoaId('default', 'default_ar');
        }

        if (!$kreditCoaId || $kreditCoaId == $debitCoaId) {
            $kreditCoaId = AccountMappingHelper::getCoaId('warehouse_transfer', 'intercompany_kredit')
                ?? AccountMappingHelper::getCoaId('default', 'default_inventory');
        }

        if (!$debitCoaId) {
            $firstCoa = ChartOfAccount::detailOnly()->active()->first();
            $debitCoaId = $firstCoa ? $firstCoa->id : null;
        }
        if (!$kreditCoaId) {
            $firstCoa = ChartOfAccount::detailOnly()->active()->first();
            $kreditCoaId = $firstCoa ? $firstCoa->id : null;
        }

        // Guardrail: Ensure Debit COA and Kredit COA are NOT identical
        if ($debitCoaId && $kreditCoaId && $debitCoaId == $kreditCoaId) {
            $altDebitCoa = ChartOfAccount::forActiveCompany()
                ->where(function($q) {
                    $q->where('name', 'like', '%piutang%')
                      ->orWhere('name', 'like', '%intercompany%')
                      ->orWhere('code', 'like', '113%');
                })
                ->where('is_header', false)
                ->where('id', '!=', $kreditCoaId)
                ->value('id');

            if ($altDebitCoa) {
                $debitCoaId = $altDebitCoa;
            }
        }

        // Group transferred items by Inventory COA (Form Explicit > Category Mapping > System Default)
        $inventoryGrouped = [];
        $grandTotalTransfer = 0.00;

        foreach ($warehouseTransfer->details as $detail) {
            $kartu = $kartuStokMap->get($detail->barang_id);
            if ($kartu && floatval($kartu->nilai_total) > 0) {
                $subtotal = floatval($kartu->nilai_total);
            } else {
                $stokAsal = $stokGudangMap->get($detail->barang_id);
                $avgCost  = floatval($stokAsal->harga_pokok_rata_rata ?? 0);
                $subtotal = floatval($detail->jumlah) * $avgCost;
            }

            if ($subtotal <= 0) continue;
            $grandTotalTransfer += $subtotal;

            // Resolve Inventory COA for this item
            $catId = $detail->barang ? $detail->barang->kategori_barang_id : null;
            $invCoaId = null;

            if ($warehouseTransfer->coa_kredit_id && $warehouseTransfer->coa_kredit_id != $debitCoaId) {
                $invCoaId = $warehouseTransfer->coa_kredit_id;
            } elseif ($catId) {
                $invCoaId = AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory");
            }

            if (!$invCoaId || $invCoaId == $debitCoaId) {
                $invCoaId = $kreditCoaId;
            }

            if (!isset($inventoryGrouped[$invCoaId])) {
                $inventoryGrouped[$invCoaId] = ['inv' => $invCoaId, 'amount' => 0.00, 'items' => []];
            }
            $inventoryGrouped[$invCoaId]['amount'] += $subtotal;
            $barangNama = $detail->barang ? $detail->barang->nama_barang : 'Barang';
            $inventoryGrouped[$invCoaId]['items'][] = $barangNama;
        }

        $totalNilaiFmt = number_format(floatval($grandTotalTransfer), 2, '.', '');
        if (floatval($totalNilaiFmt) <= 0) {
            $totalNilaiFmt = '0.00';
        }

        if (!$debitCoaId || empty($inventoryGrouped)) {
            return null;
        }

        $year   = date('Y', strtotime($warehouseTransfer->tanggal_transfer));
        $month  = date('m', strtotime($warehouseTransfer->tanggal_transfer));
        $prefix = "JRN/{$year}/{$month}/";

        $gudangAsalNama   = $gudangAsal ? $gudangAsal->nama_gudang : 'Gudang Asal';
        $gudangTujuanNama = $gudangTujuan ? $gudangTujuan->nama_gudang : 'Gudang Tujuan';
        $sourceBranchId   = $gudangAsal ? $gudangAsal->cabang_id : null;
        $targetBranchId   = $gudangTujuan ? $gudangTujuan->cabang_id : null;
        $sourceCompanyId  = $gudangAsal ? ($gudangAsal->company_id ?? 1) : 1;
        $targetCompanyId  = $gudangTujuan ? ($gudangTujuan->company_id ?? $sourceCompanyId) : $sourceCompanyId;

        $jurnalSenderCompanyId = !empty($sourceBranchId) ? null : $sourceCompanyId;
        $jurnalSenderBranchId  = !empty($sourceBranchId) ? $sourceBranchId : null;

        $jurnalReceiverCompanyId = !empty($targetBranchId) ? null : $targetCompanyId;
        $jurnalReceiverBranchId  = !empty($targetBranchId) ? $targetBranchId : null;

        $asalPtName = ($gudangAsal && $gudangAsal->company)
            ? ($gudangAsal->company->nama_perusahaan ?? $gudangAsal->company->nama_company ?? 'PT Pengirim')
            : 'PT Pengirim';

        $tujuanPtName = ($gudangTujuan && $gudangTujuan->company)
            ? ($gudangTujuan->company->nama_perusahaan ?? $gudangTujuan->company->nama_company ?? 'PT Penerima')
            : 'PT Penerima';

        $itemsList = [];
        foreach ($warehouseTransfer->details as $detail) {
            $barangNama = $detail->barang ? $detail->barang->nama_barang : 'Barang';
            $satuanNama = ($detail->barang && $detail->barang->satuan) ? $detail->barang->satuan->nama_satuan : '';
            $qtyFmt     = number_format((float)$detail->jumlah, 0, ',', '.');
            $itemsList[] = "{$barangNama} ({$qtyFmt} {$satuanNama})";
        }
        $itemSummaryStr = count($itemsList) > 0 ? implode(', ', $itemsList) : '-';

        $ketSender = "Jurnal Pengiriman Persediaan (Transfer Keluar) No. {$warehouseTransfer->nomor_transfer} [{$warehouseTransfer->jenis_transfer}] - Dari: {$asalPtName} ({$gudangAsalNama}) -> Ke: {$tujuanPtName} ({$gudangTujuanNama}) | Item: {$itemSummaryStr}";

        $ketReceiver = "Jurnal Penerimaan Persediaan (Transfer Masuk) No. {$warehouseTransfer->nomor_transfer} [{$warehouseTransfer->jenis_transfer}] - Di: {$tujuanPtName} ({$gudangTujuanNama}) <- Dari: {$asalPtName} ({$gudangAsalNama}) | Item: {$itemSummaryStr}";

        // ── 1. JURNAL PENGIRIM (PT A / Gudang Asal) ──
        // Debit = Piutang Inter-PT / Biaya Transfer (debitCoaId)
        // Kredit = Persediaan Barang Keluar per Kategori (inventoryGrouped)
        $latestSender = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('nomor_jurnal', 'desc')
            ->first();
        $numberSender = !$latestSender ? 1 : ((int) substr($latestSender->nomor_jurnal, strlen($prefix))) + 1;
        $nomorJurnalSender = $prefix . str_pad($numberSender, 4, '0', STR_PAD_LEFT);

        $jurnalSender = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnalSender,
            'tanggal_jurnal' => $warehouseTransfer->tanggal_transfer,
            'referensi_tipe' => 'Transfer Gudang',
            'referensi_id'   => $warehouseTransfer->id,
            'company_id'     => $jurnalSenderCompanyId,
            'branch_id'      => $jurnalSenderBranchId,
            'total_debit'    => $totalNilaiFmt,
            'total_kredit'   => $totalNilaiFmt,
            'keterangan'     => $ketSender,
            'created_by'     => auth()->id(),
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnalSender->id,
            'account_id' => $debitCoaId,
            'debit'      => $totalNilaiFmt,
            'kredit'     => 0,
            'keterangan' => "Transfer Keluar {$warehouseTransfer->nomor_transfer} (Debit) - {$itemSummaryStr}",
        ]);

        foreach ($inventoryGrouped as $group) {
            $amtFmt = number_format($group['amount'], 2, '.', '');
            $itemsStr = implode(', ', array_unique($group['items']));

            JurnalDetail::create([
                'jurnal_id'  => $jurnalSender->id,
                'account_id' => $group['inv'],
                'debit'      => 0,
                'kredit'     => $amtFmt,
                'keterangan' => "Transfer Keluar Persediaan {$warehouseTransfer->nomor_transfer} - {$itemsStr}",
            ]);
        }

        // ── 2. JURNAL PENERIMA (PT B / Gudang Tujuan) — Pembalikan Akun ──
        // Debit = Persediaan Barang Masuk per Kategori (inventoryGrouped)
        // Kredit = Hutang Intercompany (debitCoaId)
        if ($targetBranchId != $sourceBranchId || $warehouseTransfer->jenis_transfer === 'Inter-PT') {
            $latestReceiver = Jurnal::withTrashed()
                ->where('nomor_jurnal', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('nomor_jurnal', 'desc')
                ->first();
            $numberReceiver = !$latestReceiver ? 1 : ((int) substr($latestReceiver->nomor_jurnal, strlen($prefix))) + 1;
            $nomorJurnalReceiver = $prefix . str_pad($numberReceiver, 4, '0', STR_PAD_LEFT);

            $jurnalReceiver = Jurnal::create([
                'nomor_jurnal'   => $nomorJurnalReceiver,
                'tanggal_jurnal' => $warehouseTransfer->tanggal_transfer,
                'referensi_tipe' => 'Transfer Gudang',
                'referensi_id'   => $warehouseTransfer->id,
                'company_id'     => $jurnalReceiverCompanyId,
                'branch_id'      => $jurnalReceiverBranchId,
                'total_debit'    => $totalNilaiFmt,
                'total_kredit'   => $totalNilaiFmt,
                'keterangan'     => $ketReceiver,
                'created_by'     => auth()->id(),
            ]);

            foreach ($inventoryGrouped as $group) {
                $amtFmt = number_format($group['amount'], 2, '.', '');
                $itemsStr = implode(', ', array_unique($group['items']));

                JurnalDetail::create([
                    'jurnal_id'  => $jurnalReceiver->id,
                    'account_id' => $group['inv'],
                    'debit'      => $amtFmt,
                    'kredit'     => 0,
                    'keterangan' => "Transfer Masuk Persediaan {$warehouseTransfer->nomor_transfer} - {$itemsStr}",
                ]);
            }

            JurnalDetail::create([
                'jurnal_id'  => $jurnalReceiver->id,
                'account_id' => $debitCoaId,
                'debit'      => 0,
                'kredit'     => $totalNilaiFmt,
                'keterangan' => "Transfer Masuk {$warehouseTransfer->nomor_transfer} (Kredit) - {$itemSummaryStr}",
            ]);
        }

        return $jurnalSender;
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Detail Transfer (AJAX for modal)
    // ══════════════════════════════════════════════════════════════
    public function show(WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('lihat-transfer');

        if (!$this->canAccessTransfer($warehouseTransfer) || !$this->resolveTransfer($warehouseTransfer)) {
            return response()->json(['message' => 'Transfer gudang tidak ditemukan atau Anda tidak memiliki hak akses.'], 404);
        }

        // Auto-generate Jurnal for Confirmed transfers if missing, incomplete, or missing Item summary
        $isInterPt = $warehouseTransfer->jenis_transfer === 'Inter-PT' || ($warehouseTransfer->gudangAsal && $warehouseTransfer->gudangTujuan && $warehouseTransfer->gudangAsal->company_id != $warehouseTransfer->gudangTujuan->company_id);
        $expectedJurnalCount = $isInterPt ? 2 : 1;

        $hasOldKet = false;
        foreach ($warehouseTransfer->jurnals as $j) {
            if ($j->keterangan && strpos($j->keterangan, 'Item:') === false) {
                $hasOldKet = true;
                break;
            }
        }

        if ($warehouseTransfer->isConfirmed() && (count($warehouseTransfer->jurnals) < $expectedJurnalCount || $hasOldKet)) {
            $this->generateJurnalForTransfer($warehouseTransfer);
        }

        $warehouseTransfer->load([
            'company',
            'gudangAsal.company',
            'gudangAsal.cabang',
            'gudangTujuan.company',
            'gudangTujuan.cabang',
            'coaDebit',
            'coaKredit',
            'creator',
            'updater',
            'details.barang.satuan',
            'jurnal.details.account',
            'jurnals.details.account',
            'jurnals.branch',
        ]);

        foreach ($warehouseTransfer->jurnals as $j) {
            $j->tanggal_jurnal_fmt = $j->tanggal_jurnal ? \Carbon\Carbon::parse($j->tanggal_jurnal)->locale('id')->translatedFormat('d F Y') : '-';
            $j->tanggal_approval_fmt = $j->created_at ? \Carbon\Carbon::parse($j->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-';
        }

        // Attach HPP Satuan and Subtotal Nilai for each detail item (Batch Query Optimized)
        $detailBarangIds = $warehouseTransfer->details->pluck('barang_id')->toArray();

        $detailKartuMap = KartuStok::where('referensi_jenis', 'Transfer Gudang')
            ->where('referensi_id', $warehouseTransfer->id)
            ->where('tipe', 'Keluar')
            ->whereIn('barang_id', $detailBarangIds)
            ->get()
            ->keyBy('barang_id');

        $detailStokAsalMap = StokGudang::where('gudang_id', $warehouseTransfer->gudang_asal_id)
            ->whereIn('barang_id', $detailBarangIds)
            ->get()
            ->keyBy('barang_id');

        foreach ($warehouseTransfer->details as $detail) {
            $kartu = $detailKartuMap->get($detail->barang_id);

            if ($kartu && floatval($kartu->harga_satuan) > 0) {
                $detail->hpp_satuan = floatval($kartu->harga_satuan);
                $detail->subtotal_nilai = floatval($kartu->nilai_total);
            } else {
                $stokAsal = $detailStokAsalMap->get($detail->barang_id);
                $hpp = floatval($stokAsal->harga_pokok_rata_rata ?? 0);
                $detail->hpp_satuan = $hpp;
                $detail->subtotal_nilai = floatval($detail->jumlah) * $hpp;
            }
        }

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        $isCreator = auth()->check() && ((int) $warehouseTransfer->created_by === (int) $user->id);

        $canViewJurnal = $isSuperAdmin 
            || $user->can('lihat-jurnal-transfer') 
            || $user->can('akses-accounting') 
            || $user->can('approve-transfer');

        $canSeeHpp = $this->canSeeHpp();

        $canEdit = (
            ($warehouseTransfer->status === 'Draft' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Pending' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Confirmed' && $isSuperAdmin)
        );

        return response()->json([
            'transfer'          => $warehouseTransfer,
            'active_company_id' => session('active_company_id'),
            'active_branch_id'  => session('active_branch_id'),
            'is_super_admin'    => $isSuperAdmin,
            'is_creator'        => $isCreator,
            'can_approve'       => $isSuperAdmin || $user->can('approve-transfer'),
            'can_edit'          => $canEdit,
            'can_view_jurnal'   => $canViewJurnal,
            'can_see_hpp'       => $canSeeHpp,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SUBMIT — Submit Draft Transfer to Pending Approval
    // ══════════════════════════════════════════════════════════════
    public function submit(WarehouseTransfer $warehouseTransfer)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        $isCreator = auth()->check() && ((int) $warehouseTransfer->created_by === (int) $user->id);

        if (!$isCreator && !$isSuperAdmin && !$user->can('tambah-transfer') && !$user->can('edit-transfer')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengajukan transfer ini.'], 403);
        }

        if (!$warehouseTransfer->isDraft()) {
            return response()->json(['message' => 'Hanya transfer berstatus Draft yang dapat diajukan.'], 422);
        }

        // Validate stock sufficiency before submitting
        $gudangAsal = $warehouseTransfer->gudangAsal;
        foreach ($warehouseTransfer->details as $detail) {
            $stokGudangAsal = StokGudang::where('barang_id', $detail->barang_id)
                ->where('gudang_id', $warehouseTransfer->gudang_asal_id)
                ->first();
            $stokAktualAsal = $stokGudangAsal ? floatval($stokGudangAsal->stok_aktual) : 0;

            if ($stokAktualAsal < $detail->jumlah) {
                $barang = MasterBarang::find($detail->barang_id);
                $namaBarang = $barang ? $barang->nama_barang : "ID #{$detail->barang_id}";
                return response()->json([
                    'message' => "Stok tidak mencukupi untuk '{$namaBarang}' pada Gudang Asal '{$gudangAsal->nama_gudang}'. Stok tersedia: {$stokAktualAsal}, diminta: {$detail->jumlah}.",
                ], 422);
            }
        }

        $warehouseTransfer->update([
            'status' => 'Pending',
        ]);

        // WhatsApp Notification
        $transferId = $warehouseTransfer->id;
        WhatsAppGatewayController::runAsync(function() use ($transferId) {
            $trf = \App\Models\WarehouseTransfer::find($transferId);
            if ($trf) {
                WhatsAppGatewayController::sendTransferSubmissionNotification($trf);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Transfer Gudang {$warehouseTransfer->nomor_transfer} berhasil diajukan! Status berubah menjadi PENDING (Menunggu Persetujuan).",
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CONFIRM — Execute Transfer (deduct/add stock and log kartu stok)
    // ══════════════════════════════════════════════════════════════
    public function confirm(WarehouseTransfer $warehouseTransfer)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin'));

        if (!$isSuperAdmin && !$user->can('approve-transfer')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menyetujui transfer gudang ini.'], 403);
        }

        if (!$this->resolveTransfer($warehouseTransfer)) {
            return response()->json(['message' => 'Transfer gudang tidak ditemukan.'], 404);
        }

        if ($warehouseTransfer->isConfirmed()) {
            return response()->json(['message' => 'Dokumen ini sudah dikonfirmasi / diposting.'], 422);
        }

        try {
            DB::beginTransaction();

            $this->executeStockPostingInternal($warehouseTransfer);

            $warehouseTransfer->update([
                'status'     => 'Confirmed',
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            // Send WhatsApp Notification asynchronously without blocking response
            $transferId = $warehouseTransfer->id;
            $approverId = auth()->id();
            WhatsAppGatewayController::runAsync(function() use ($transferId, $approverId) {
                $trf = \App\Models\WarehouseTransfer::find($transferId);
                $approver = $approverId ? \App\Models\User::find($approverId) : null;
                if ($trf) {
                    WhatsAppGatewayController::sendTransferApprovedNotification($trf, $approver);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => "Transfer gudang {$warehouseTransfer->nomor_transfer} berhasil diposting!",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WT Confirm Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => $e->getMessage() ?: 'Terjadi kesalahan server saat konfirmasi.',
            ], 422);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT — Fetch Transfer for Modal Edit
    // ══════════════════════════════════════════════════════════════
    public function edit(WarehouseTransfer $warehouseTransfer)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        $isCreator = auth()->check() && ((int) $warehouseTransfer->created_by === (int) $user->id);

        $canEdit = (
            ($warehouseTransfer->status === 'Draft' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Pending' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Confirmed' && $isSuperAdmin)
        );

        if (!$canEdit) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengedit transfer gudang ini.'], 403);
        }

        if (!$this->canAccessTransfer($warehouseTransfer) || !$this->resolveTransfer($warehouseTransfer)) {
            return response()->json(['message' => 'Transfer gudang tidak ditemukan atau Anda tidak memiliki hak akses.'], 404);
        }

        $warehouseTransfer->load([
            'details.barang.satuan',
            'gudangAsal',
            'gudangTujuan',
        ]);

        return response()->json([
            'status'   => 'success',
            'transfer' => $warehouseTransfer,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Update Transfer Header & Items (Supports Super Admin Override)
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('Owner') || $user->hasRole('Superadmin') || (property_exists($user, 'role') && strtolower(strval($user->role)) === 'superadmin');
        $isCreator = auth()->check() && ((int) $warehouseTransfer->created_by === (int) $user->id);

        $canEdit = (
            ($warehouseTransfer->status === 'Draft' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Pending' && ($isCreator || $isSuperAdmin || $user->can('edit-transfer') || $user->can('tambah-transfer'))) ||
            ($warehouseTransfer->status === 'Confirmed' && $isSuperAdmin)
        );

        if (!$canEdit) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengedit transfer gudang ini.'], 403);
        }

        if (!$this->canAccessTransfer($warehouseTransfer) || !$this->resolveTransfer($warehouseTransfer)) {
            return response()->json(['message' => 'Transfer gudang tidak ditemukan atau Anda tidak memiliki hak akses.'], 404);
        }

        $request->validate([
            'tanggal_transfer' => 'required|date',
            'gudang_asal_id'   => 'required|exists:master_gudang,id',
            'gudang_tujuan_id' => 'required|exists:master_gudang,id',
            'keterangan'       => 'nullable|string|max:40',
            'nomor_transfer'   => 'nullable|string|max:20',
            'items'            => 'required|array|min:1|max:25',
            'items.*.barang_id'=> 'required|exists:master_barang,id',
            'items.*.jumlah'   => 'required|numeric|min:0.01',
        ]);

        if ($request->gudang_asal_id == $request->gudang_tujuan_id) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => [
                    'gudang_tujuan_id' => ['Gudang tujuan tidak boleh sama dengan Gudang asal!']
                ],
            ], 422);
        }

        $gudangAsal   = $this->resolveGudangAsal((int) $request->gudang_asal_id);
        $gudangTujuan = $this->resolveGudangTujuan((int) $request->gudang_tujuan_id);

        if (!$gudangAsal) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_asal_id' => ['Gudang asal tidak valid atau bukan milik cabang aktif Anda!']],
            ], 422);
        }
        if (!$gudangTujuan) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_tujuan_id' => ['Gudang tujuan tidak valid atau tidak aktif!']],
            ], 422);
        }

        // Group duplicate items
        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            if (isset($itemsGrouped[$bid])) {
                $itemsGrouped[$bid] += floatval($item['jumlah']);
            } else {
                $itemsGrouped[$bid] = floatval($item['jumlah']);
            }
        }

        // Verify active items, target company Master Barang existence, and stock sufficiency
        $targetCompanyId   = $gudangTujuan->company_id;
        $targetCompanyNama = $gudangTujuan->company ? $gudangTujuan->company->nama_perusahaan : 'PT Tujuan';
        $targetCabangNama  = $gudangTujuan->cabang ? $gudangTujuan->cabang->nama_cabang : 'Cabang Utama';

        $sourceCompanyNama = $gudangAsal->company ? $gudangAsal->company->nama_perusahaan : 'PT Asal';
        $sourceCabangNama  = $gudangAsal->cabang ? $gudangAsal->cabang->nama_cabang : 'Cabang Utama';

        foreach ($itemsGrouped as $barangId => $jumlah) {
            $sourceBarang = MasterBarang::find($barangId);
            if (!$sourceBarang || $sourceBarang->status !== 'Active') {
                return response()->json([
                    'message' => "Barang ID #{$barangId} tidak ditemukan atau tidak aktif di Master Barang.",
                ], 422);
            }

            // Security Check: Verify item strictly exists in Master Barang of target company by KODE BARANG and NAMA BARANG
            if ($targetCompanyId && $targetCompanyId != $gudangAsal->company_id) {
                $targetBarang = MasterBarang::where('company_id', $targetCompanyId)
                    ->where('status', 'Active')
                    ->where(DB::raw('LOWER(TRIM(kode_barang))'), strtolower(trim($sourceBarang->kode_barang)))
                    ->first();

                if (!$targetBarang) {
                    return response()->json([
                        'message' => "Pemberitahuan Keamanan Inter-PT: Kode barang '{$sourceBarang->kode_barang}' ({$sourceBarang->nama_barang}) belum terdaftar pada Katalog Master Barang milik {$targetCompanyNama} ({$targetCabangNama}). Mohon daftarkan kode barang '{$sourceBarang->kode_barang}' terlebih dahulu di Master Barang PT Tujuan sebelum melanjutkan transaksi transfer.",
                    ], 422);
                }

                $sourceNamaClean = strtolower(trim($sourceBarang->nama_barang));
                $targetNamaClean = strtolower(trim($targetBarang->nama_barang));

                if ($sourceNamaClean !== $targetNamaClean) {
                    return response()->json([
                        'message' => "Pemberitahuan Keamanan Inter-PT: Kode barang '{$sourceBarang->kode_barang}' terdaftar dengan Nama Barang yang berbeda pada PT Tujuan {$targetCompanyNama} ({$targetCabangNama}).\n\n- Nama di PT Asal: '{$sourceBarang->nama_barang}'\n- Nama di PT Tujuan: '{$targetBarang->nama_barang}'\n\nMohon samakan Kode Barang dan Nama Barang pada Katalog Master Barang kedua PT terlebih dahulu.",
                    ], 422);
                }
            }

            // Verify stock sufficiency in source warehouse (considering old stock if Confirmed)
            $stokGudangAsal = StokGudang::where('barang_id', $barangId)
                ->where('gudang_id', $gudangAsal->id)
                ->first();
            $stokAktualAsal = $stokGudangAsal ? floatval($stokGudangAsal->stok_aktual) : 0;

            // If previously confirmed and edited by Super Admin, consider old reverted stock
            if ($warehouseTransfer->isConfirmed() && $isSuperAdmin) {
                $oldItemQty = $warehouseTransfer->details()->where('barang_id', $barangId)->sum('jumlah');
                $stokAktualAsal += floatval($oldItemQty);
            }

            $submitAction = strtolower($request->input('submit_action', 'draft'));
            if ($submitAction !== 'draft') {
                if ($stokAktualAsal < $jumlah) {
                    return response()->json([
                        'message' => "Stok tidak mencukupi untuk '{$sourceBarang->nama_barang}' pada Gudang Asal '{$gudangAsal->nama_gudang}' ({$sourceCompanyNama} - {$sourceCabangNama}). Stok tersedia: {$stokAktualAsal}, jumlah diminta: {$jumlah}.",
                    ], 422);
                }
            }
        }

        try {
            DB::beginTransaction();

            $wasConfirmed = $warehouseTransfer->isConfirmed();

            // STEP 1: Revert previous stock movements & KartuStok if previously Confirmed
            if ($wasConfirmed && $isSuperAdmin) {
                $oldGudangAsal   = $warehouseTransfer->gudangAsal;
                $oldGudangTujuan = $warehouseTransfer->gudangTujuan;

                // Find old KartuStok records for this transfer
                $oldKartuStoks = KartuStok::whereIn('referensi_jenis', ['Transfer Internal', 'Transfer Inter-Cabang', 'Transfer Inter-PT', 'Transfer Gudang'])
                    ->where('referensi_id', $warehouseTransfer->id)
                    ->get();

                foreach ($warehouseTransfer->details as $oldDetail) {
                    $kartuKeluar = $oldKartuStoks->where('tipe', 'Keluar')->where('barang_id', $oldDetail->barang_id)->first();
                    $revertQtyAsal   = $kartuKeluar ? (string)$kartuKeluar->jumlah : (string)$oldDetail->jumlah;
                    $revertNilaiAsal = $kartuKeluar ? (string)$kartuKeluar->nilai_total : '0.0000';
                    $revertHppAsal   = $kartuKeluar ? (string)$kartuKeluar->harga_satuan : null;

                    // Revert Source Gudang (Add back stock and inventory value)
                    $stokAsal = StokGudang::where('barang_id', $oldDetail->barang_id)
                        ->where('gudang_id', $warehouseTransfer->gudang_asal_id)
                        ->lockForUpdate()
                        ->first();
                    if ($stokAsal) {
                        $stokAsalAwal  = (string)($stokAsal->stok_aktual ?? '0');
                        $nilaiAsalAwal = (string)($stokAsal->nilai_persediaan ?? DecimalHelper::mul($stokAsalAwal, (string)($stokAsal->harga_pokok_rata_rata ?? '0'), 4));

                        $stokAsalBaru  = DecimalHelper::add($stokAsalAwal, $revertQtyAsal, 2);
                        $nilaiAsalBaru = DecimalHelper::add($nilaiAsalAwal, $revertNilaiAsal, 4);

                        $stokAsal->stok_aktual      = $stokAsalBaru;
                        $stokAsal->nilai_persediaan = $nilaiAsalBaru;
                        if ($revertHppAsal && DecimalHelper::comp($revertHppAsal, '0', 4) > 0) {
                            $stokAsal->harga_pokok_rata_rata = $revertHppAsal;
                        }
                        $stokAsal->save();
                        StokGudang::syncMasterHpp($oldDetail->barang_id);
                    }

                    // Resolve target barang ID
                    $targetBarangId = $oldDetail->barang_id;
                    $sourceBarang   = MasterBarang::find($oldDetail->barang_id);
                    if ($sourceBarang && $oldGudangTujuan && $oldGudangAsal && $oldGudangTujuan->company_id != $oldGudangAsal->company_id) {
                        $tbMatch = MasterBarang::where('company_id', $oldGudangTujuan->company_id)
                            ->where('status', 'Active')
                            ->where(DB::raw('LOWER(TRIM(kode_barang))'), strtolower(trim($sourceBarang->kode_barang)))
                            ->first();
                        if ($tbMatch) {
                            $targetBarangId = $tbMatch->id;
                        }
                    }

                    $kartuMasuk = $oldKartuStoks->where('tipe', 'Masuk')->where('barang_id', $targetBarangId)->first();
                    $revertQtyTujuan   = $kartuMasuk ? (string)$kartuMasuk->jumlah : (string)$oldDetail->jumlah;
                    $revertNilaiTujuan = $kartuMasuk ? (string)$kartuMasuk->nilai_total : '0.0000';

                    // Revert Destination Gudang (Subtract stock and inventory value)
                    $stokTujuan = StokGudang::where('barang_id', $targetBarangId)
                        ->where('gudang_id', $warehouseTransfer->gudang_tujuan_id)
                        ->lockForUpdate()
                        ->first();
                    if ($stokTujuan) {
                        $stokTujuanAwal  = (string)($stokTujuan->stok_aktual ?? '0');
                        $nilaiTujuanAwal = (string)($stokTujuan->nilai_persediaan ?? DecimalHelper::mul($stokTujuanAwal, (string)($stokTujuan->harga_pokok_rata_rata ?? '0'), 4));

                        $stokTujuanBaru  = max('0.00', DecimalHelper::sub($stokTujuanAwal, $revertQtyTujuan, 2));
                        $nilaiTujuanBaru = max('0.0000', DecimalHelper::sub($nilaiTujuanAwal, $revertNilaiTujuan, 4));

                        $newAvgCostTujuan = DecimalHelper::comp($stokTujuanBaru, '0', 4) > 0
                            ? DecimalHelper::div($nilaiTujuanBaru, $stokTujuanBaru, 6)
                            : (string)($stokTujuan->hpp_sebelumnya ?? '0.000000');

                        $stokTujuan->stok_aktual           = $stokTujuanBaru;
                        $stokTujuan->nilai_persediaan      = $nilaiTujuanBaru;
                        $stokTujuan->harga_pokok_rata_rata = $newAvgCostTujuan;
                        $stokTujuan->save();
                        StokGudang::syncMasterHpp($targetBarangId);
                    }
                }

                // Delete old KartuStok logs for this transfer
                KartuStok::whereIn('referensi_jenis', ['Transfer Internal', 'Transfer Inter-Cabang', 'Transfer Inter-PT', 'Transfer Gudang'])
                    ->where('referensi_id', $warehouseTransfer->id)
                    ->delete();
            }

            $isInterCompany = ($gudangAsal->company_id != $gudangTujuan->company_id);
            $jenisTransfer  = $isInterCompany ? 'Inter-PT' : 'Intra-PT';

            if ($isInterCompany) {
                if (!$request->coa_debit_id || !$request->coa_kredit_id) {
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors'  => [
                            'coa_debit_id'  => ['Transfer Antar PT (Inter-PT) wajib memilih Akun Debit & Kredit Jurnal COA!'],
                            'coa_kredit_id' => ['Transfer Antar PT (Inter-PT) wajib memilih Akun Debit & Kredit Jurnal COA!'],
                        ],
                    ], 422);
                }
                if ($request->coa_debit_id == $request->coa_kredit_id) {
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors'  => [
                            'coa_debit_id'  => ['Akun Debit (Piutang Intercompany) dan Akun Kredit (Persediaan) tidak boleh sama!'],
                            'coa_kredit_id' => ['Akun Debit (Piutang Intercompany) dan Akun Kredit (Persediaan) tidak boleh sama!'],
                        ],
                    ], 422);
                }
            }

            $submitAction = strtolower($request->input('submit_action', 'draft'));
            $statusToSet  = $warehouseTransfer->status;

            if ($wasConfirmed && $isSuperAdmin) {
                $statusToSet = 'Confirmed';
            } elseif ($submitAction === 'post' || $submitAction === 'confirm') {
                $statusToSet = 'Confirmed';
            } elseif ($submitAction === 'pending') {
                $statusToSet = 'Pending';
            } elseif ($submitAction === 'draft') {
                $statusToSet = 'Draft';
            }

            $warehouseTransfer->update([
                'tanggal_transfer' => $request->tanggal_transfer,
                'gudang_asal_id'   => $gudangAsal->id,
                'gudang_tujuan_id' => $gudangTujuan->id,
                'keterangan'       => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'           => $statusToSet,
                'jenis_transfer'   => $jenisTransfer,
                'coa_debit_id'     => $isInterCompany ? $request->coa_debit_id : null,
                'coa_kredit_id'    => $isInterCompany ? $request->coa_kredit_id : null,
            ]);

            // Sync details
            $warehouseTransfer->details()->delete();
            foreach ($itemsGrouped as $barangId => $jumlah) {
                WarehouseTransferDetail::create([
                    'transfer_id' => $warehouseTransfer->id,
                    'barang_id'   => $barangId,
                    'jumlah'      => $jumlah,
                ]);
            }

            // STEP 2: Re-apply new stock movements & KartuStok if status is Confirmed
            if ($statusToSet === 'Confirmed') {
                $this->executeStockPostingInternal($warehouseTransfer);
                $msg = "Dokumen transfer {$warehouseTransfer->nomor_transfer} berhasil diposting dan stok telah diperbarui!";
            } elseif ($statusToSet === 'Pending') {
                $msg = "Dokumen transfer {$warehouseTransfer->nomor_transfer} berhasil diperbarui dengan status Pending!";
            } else {
                $msg = "Draft transfer {$warehouseTransfer->nomor_transfer} berhasil diperbarui!";
            }

            DB::commit();

            // Send WhatsApp Notification asynchronously without blocking response
            $transferId = $warehouseTransfer->id;
            $statusVal  = $statusToSet;
            $approverId = auth()->id();
            WhatsAppGatewayController::runAsync(function() use ($transferId, $statusVal, $approverId) {
                $trf = \App\Models\WarehouseTransfer::find($transferId);
                $approver = $approverId ? \App\Models\User::find($approverId) : null;
                if ($trf) {
                    if ($statusVal === 'Confirmed') {
                        WhatsAppGatewayController::sendTransferApprovedNotification($trf, $approver);
                    } elseif ($statusVal === 'Pending') {
                        WhatsAppGatewayController::sendTransferSubmissionNotification($trf);
                    }
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WT Update Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => $e->getMessage() ?: 'Terjadi kesalahan server saat memperbarui transfer.',
            ], 422);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Delete Draft Transfer
    // ══════════════════════════════════════════════════════════════
    public function destroy(WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('hapus-transfer');

        if (!$this->canAccessTransfer($warehouseTransfer) || !$this->resolveTransfer($warehouseTransfer)) {
            return response()->json(['message' => 'Transfer gudang tidak ditemukan atau Anda tidak memiliki hak akses.'], 404);
        }

        $isSuperAdmin = auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Owner');
        $isCreator = auth()->check() && ((int) $warehouseTransfer->created_by === (int) auth()->id());

        // Status Draft: hanya pembuatnya atau Super Admin yang bisa menghapus
        if ($warehouseTransfer->status === 'Draft' && !$isCreator && !$isSuperAdmin) {
            return response()->json([
                'message' => 'Draf transfer hanya dapat dihapus oleh pembuatnya.',
            ], 403);
        }

        // Status Pending: tombol hapus hilang, tidak dapat dihapus kecuali Super Admin
        if ($warehouseTransfer->status === 'Pending' && !$isSuperAdmin) {
            return response()->json([
                'message' => 'Dokumen transfer yang berstatus Pending tidak dapat dihapus kecuali oleh Super Admin.',
            ], 422);
        }

        // Status Confirmed: tidak dapat dihapus kecuali Super Admin
        if ($warehouseTransfer->status === 'Confirmed' && !$isSuperAdmin) {
            return response()->json([
                'message' => 'Dokumen transfer yang sudah Confirmed tidak dapat dihapus kecuali oleh Super Admin.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // If Confirmed and deleted by Super Admin, revert stock & kartu stok first
            if ($warehouseTransfer->status === 'Confirmed' && $isSuperAdmin) {
                $oldGudangAsal   = $warehouseTransfer->gudangAsal;
                $oldGudangTujuan = $warehouseTransfer->gudangTujuan;

                $oldKartuStoks = KartuStok::whereIn('referensi_jenis', ['Transfer Internal', 'Transfer Inter-Cabang', 'Transfer Inter-PT', 'Transfer Gudang'])
                    ->where('referensi_id', $warehouseTransfer->id)
                    ->get();

                foreach ($warehouseTransfer->details as $oldDetail) {
                    $kartuKeluar = $oldKartuStoks->where('tipe', 'Keluar')->where('barang_id', $oldDetail->barang_id)->first();
                    $revertQtyAsal   = $kartuKeluar ? (string)$kartuKeluar->jumlah : (string)$oldDetail->jumlah;
                    $revertNilaiAsal = $kartuKeluar ? (string)$kartuKeluar->nilai_total : '0.0000';
                    $revertHppAsal   = $kartuKeluar ? (string)$kartuKeluar->harga_satuan : null;

                    // Revert Source Gudang (Add back)
                    $stokAsal = StokGudang::where('barang_id', $oldDetail->barang_id)
                        ->where('gudang_id', $warehouseTransfer->gudang_asal_id)
                        ->lockForUpdate()
                        ->first();
                    if ($stokAsal) {
                        $stokAsalAwal  = (string)($stokAsal->stok_aktual ?? '0');
                        $nilaiAsalAwal = (string)($stokAsal->nilai_persediaan ?? DecimalHelper::mul($stokAsalAwal, (string)($stokAsal->harga_pokok_rata_rata ?? '0'), 4));

                        $stokAsalBaru  = DecimalHelper::add($stokAsalAwal, $revertQtyAsal, 2);
                        $nilaiAsalBaru = DecimalHelper::add($nilaiAsalAwal, $revertNilaiAsal, 4);

                        $stokAsal->stok_aktual      = $stokAsalBaru;
                        $stokAsal->nilai_persediaan = $nilaiAsalBaru;
                        if ($revertHppAsal && DecimalHelper::comp($revertHppAsal, '0', 4) > 0) {
                            $stokAsal->harga_pokok_rata_rata = $revertHppAsal;
                        }
                        $stokAsal->save();
                        StokGudang::syncMasterHpp($oldDetail->barang_id);
                    }

                    // Revert Destination Gudang (Subtract)
                    $targetBarangId = $oldDetail->barang_id;
                    $sourceBarang   = MasterBarang::find($oldDetail->barang_id);
                    if ($sourceBarang && $oldGudangTujuan && $oldGudangAsal && $oldGudangTujuan->company_id != $oldGudangAsal->company_id) {
                        $tbMatch = MasterBarang::where('company_id', $oldGudangTujuan->company_id)
                            ->where('status', 'Active')
                            ->where(DB::raw('LOWER(TRIM(kode_barang))'), strtolower(trim($sourceBarang->kode_barang)))
                            ->first();
                        if ($tbMatch) {
                            $targetBarangId = $tbMatch->id;
                        }
                    }

                    $kartuMasuk = $oldKartuStoks->where('tipe', 'Masuk')->where('barang_id', $targetBarangId)->first();
                    $revertQtyTujuan   = $kartuMasuk ? (string)$kartuMasuk->jumlah : (string)$oldDetail->jumlah;
                    $revertNilaiTujuan = $kartuMasuk ? (string)$kartuMasuk->nilai_total : '0.0000';

                    $stokTujuan = StokGudang::where('barang_id', $targetBarangId)
                        ->where('gudang_id', $warehouseTransfer->gudang_tujuan_id)
                        ->lockForUpdate()
                        ->first();
                    if ($stokTujuan) {
                        $stokTujuanAwal  = (string)($stokTujuan->stok_aktual ?? '0');
                        $nilaiTujuanAwal = (string)($stokTujuan->nilai_persediaan ?? DecimalHelper::mul($stokTujuanAwal, (string)($stokTujuan->harga_pokok_rata_rata ?? '0'), 4));

                        $stokTujuanBaru  = max('0.00', DecimalHelper::sub($stokTujuanAwal, $revertQtyTujuan, 2));
                        $nilaiTujuanBaru = max('0.0000', DecimalHelper::sub($nilaiTujuanAwal, $revertNilaiTujuan, 4));

                        $newAvgCostTujuan = DecimalHelper::comp($stokTujuanBaru, '0', 4) > 0
                            ? DecimalHelper::div($nilaiTujuanBaru, $stokTujuanBaru, 6)
                            : (string)($stokTujuan->hpp_sebelumnya ?? '0.000000');

                        $stokTujuan->stok_aktual           = $stokTujuanBaru;
                        $stokTujuan->nilai_persediaan      = $nilaiTujuanBaru;
                        $stokTujuan->harga_pokok_rata_rata = $newAvgCostTujuan;
                        $stokTujuan->save();
                        StokGudang::syncMasterHpp($targetBarangId);
                    }
                }

                // Delete KartuStok
                KartuStok::whereIn('referensi_jenis', ['Transfer Internal', 'Transfer Inter-Cabang', 'Transfer Inter-PT', 'Transfer Gudang'])
                    ->where('referensi_id', $warehouseTransfer->id)
                    ->delete();

                // Delete auto-journals
                foreach ($warehouseTransfer->jurnals as $jrn) {
                    $jrn->details()->delete();
                    $jrn->delete();
                }
            }

            // Delete details
            $warehouseTransfer->details()->delete();

            // Soft delete header
            $warehouseTransfer->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Transfer gudang {$warehouseTransfer->nomor_transfer} berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WT Destroy Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }
}
