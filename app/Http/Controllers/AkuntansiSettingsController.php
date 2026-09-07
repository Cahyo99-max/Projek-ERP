<?php

namespace App\Http\Controllers;

use App\Helpers\AccountMappingHelper;
use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use App\Models\MasterJenisBiaya;
use App\Models\MasterTipePengeluaran;
use App\Models\MasterKategoriBarang;
use App\Models\MasterPajak;
use App\Models\MasterTipeAkun;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AkuntansiSettingsController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'coa');
        $activeCompanyId = session('active_company_id');

        // Master Tipe Akun
        $accountTypes = MasterTipeAkun::orderBy('kode_tipe')->get();

        // Master Data COA per PT
        $typeFilter = $request->query('type');
        $searchQuery = $request->query('search');

        $coaQuery = ChartOfAccount::forActiveCompany()->with('masterTipeAkun', 'parent')->orderBy('code');
        if ($typeFilter) {
            $coaQuery->where(function($q) use ($typeFilter) {
                $q->where('type', $typeFilter)
                  ->orWhere('master_tipe_akun_id', $typeFilter);
            });
        }
        if ($searchQuery) {
            $coaQuery->where(function ($q) use ($searchQuery) {
                $q->where('code', 'like', "%{$searchQuery}%")
                  ->orWhere('name', 'like', "%{$searchQuery}%");
            });
        }
        $accounts = $coaQuery->get();
        $parentAccounts = ChartOfAccount::forActiveCompany()->where('is_header', true)->orderBy('code')->get();

        // COA list for dropdowns (detail only) per PT
        $detailCoas = ChartOfAccount::forActiveCompany()->detailOnly()->active()->orderBy('code')->get();

        // Master Data per PT
        $categories = MasterKategoriBarang::forActiveCompany()->orderBy('nama_kategori')->get();
        $expenseTypes = MasterJenisBiaya::forActiveCompany()->orderBy('nama_jenis_biaya')->get();
        $issueTypes = MasterTipePengeluaran::forActiveCompany()->with(['coa', 'coaKredit'])->orderBy('nama_tipe')->get();
        $taxes = MasterPajak::forActiveCompany()->orderBy('nama_pajak')->get();
        $paymentMethods = PaymentMethod::forActiveCompany()->orderBy('nama_metode')->get();

        // Indexed Account Mappings per PT
        $mappings = AccountMapping::forActiveCompany()->get()->keyBy(function ($item) {
            return $item->group . '.' . $item->mapping_key;
        });

        return view('settings.akuntansi.index', compact(
            'activeTab',
            'accountTypes',
            'accounts',
            'parentAccounts',
            'detailCoas',
            'categories',
            'expenseTypes',
            'issueTypes',
            'taxes',
            'paymentMethods',
            'mappings',
            'typeFilter',
            'searchQuery'
        ));
    }

    /**
     * Store Category Account Mappings
     */
    public function updateCategoryMappings(Request $request)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);

        $request->validate([
            'category_mappings' => 'required|array',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($request->category_mappings as $catId => $coaMap) {
                $cat = MasterKategoriBarang::find($catId);
                $catName = $cat ? $cat->nama_kategori : "Kategori ID {$catId}";

                $keys = [
                    'inventory'  => ["cat_{$catId}_inventory", "Persediaan {$catName}"],
                    'return'     => ["cat_{$catId}_return", "Retur Pembelian {$catName}"],
                    'adjustment' => ["cat_{$catId}_adjustment", "Selisih Stok {$catName}"],
                    'cogs'       => ["cat_{$catId}_cogs", "HPP {$catName}"],
                ];

                foreach ($keys as $type => $info) {
                    $key  = $info[0];
                    $desc = $info[1];
                    $coaId = $coaMap[$type] ?? null;

                    if (!empty($coaId)) {
                        AccountMapping::updateOrCreate(
                            [
                                'company_id'  => $activeCompanyId,
                                'group'       => 'inventory_category',
                                'mapping_key' => $key,
                            ],
                            [
                                'coa_id'      => (int) $coaId,
                                'description' => $desc,
                            ]
                        );
                    } else {
                        AccountMapping::where('company_id', $activeCompanyId)
                            ->where('group', 'inventory_category')
                            ->where('mapping_key', $key)
                            ->delete();
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            AccountMappingHelper::clearCache($activeCompanyId);

            return response()->json([
                'status'  => 'success',
                'message' => 'Mapping Kategori Barang berhasil disimpan & diperbarui untuk PT ini!',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Error update Category Mappings: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Mapping Kategori Barang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store System Account Mappings (Central Mappings)
     */
    public function updateSystemMappings(Request $request)
    {
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'mappings' => 'required|array',
        ]);

        try {
            foreach ($request->mappings as $key => $data) {
                $group = $data['group'] ?? 'default';
                $mappingKey = $data['mapping_key'] ?? $key;
                $coaId = $data['coa_id'] ?? null;
                $desc = $data['description'] ?? null;

                if (!empty($coaId)) {
                    AccountMappingHelper::setMapping($group, $mappingKey, (int) $coaId, $desc);
                } else {
                    AccountMapping::where('company_id', $activeCompanyId ?: 1)
                        ->where('group', $group)
                        ->where('mapping_key', $mappingKey)
                        ->delete();
                }
            }

            AccountMappingHelper::clearCache($activeCompanyId);

            return response()->json(['message' => 'System Account Mapping berhasil diperbarui untuk PT ini!']);
        } catch (\Exception $e) {
            Log::error('Error update System Account Mappings: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui System Account Mapping.'], 500);
        }
    }
}
