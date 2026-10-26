<?php

namespace App\Http\Controllers;

use App\Helpers\AccountMappingHelper;
use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use App\Models\KasKategoriMapping;
use App\Models\PettyCashKategoriMapping;
use App\Models\MasterJenisBiaya;
use App\Models\MasterTipePengeluaran;
use App\Models\MasterKategoriBarang;
use App\Models\MasterPajak;
use App\Models\MasterTipeAkun;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AkuntansiSettingsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $activeTab = $request->query('tab', 'coa');
        $activeCompanyId = session('active_company_id');

        // Master Tipe Akun per PT
        $accountTypes = MasterTipeAkun::forActiveCompany()->orderBy('kode_tipe')->get();

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

        // Kas & Bank Kategori Mappings per PT
        $kasKategoriMappings = KasKategoriMapping::forActiveCompany()->with('coa')->orderBy('tipe')->orderBy('nama_kategori')->get();

        // Petty Cash (Kas Kecil) Kategori Mappings per PT
        $pettyCashMappings = PettyCashKategoriMapping::forActiveCompany()->with('coa')->orderBy('nama_kategori')->get();

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
            'kasKategoriMappings',
            'pettyCashMappings',
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
        $this->authorize('edit-pengaturan-akuntansi');

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
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.group' => 'nullable|string|max:50',
            'mappings.*.mapping_key' => 'nullable|string|max:100',
            'mappings.*.coa_id' => 'nullable',
            'mappings.*.description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Fetch allowed COA IDs for this company once (detail and active only) for security
            $validCoaIds = ChartOfAccount::forActiveCompany()
                ->detailOnly()
                ->active()
                ->pluck('id')
                ->flip();

            foreach ($request->mappings as $key => $data) {
                $group = $data['group'] ?? 'default';
                $mappingKey = $data['mapping_key'] ?? $key;
                $coaId = !empty($data['coa_id']) ? (int) $data['coa_id'] : null;
                $desc = $data['description'] ?? null;

                if ($coaId) {
                    // Security verification: ensure coa_id belongs to active company and is not a header account
                    if (!$validCoaIds->has($coaId)) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Akun COA ID {$coaId} tidak valid, berstatus non-aktif, berupa akun header, atau bukan milik perusahaan ini."
                        ], 422);
                    }

                    AccountMappingHelper::setMapping($group, $mappingKey, $coaId, $desc, $activeCompanyId);
                } else {
                    AccountMapping::where('company_id', $activeCompanyId)
                        ->where('group', $group)
                        ->where('mapping_key', $mappingKey)
                        ->delete();

                    \Illuminate\Support\Facades\Cache::forget("account_mapping_{$activeCompanyId}_{$group}_{$mappingKey}");
                }
            }

            DB::commit();

            AccountMappingHelper::clearCache($activeCompanyId);

            return response()->json(['message' => 'System Account Mapping berhasil diperbarui untuk PT ini!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update System Account Mappings: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui System Account Mapping: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Kas & Bank Category Mapping
     */
    public function storeKasKategoriMapping(Request $request)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:40',
                Rule::unique('kas_kategori_mappings', 'nama_kategori')->where(function ($q) use ($activeCompanyId, $request) {
                    $q->where('tipe', $request->tipe);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                }),
            ],
            'tipe'          => 'required|in:Masuk,Keluar',
            'coa_id'        => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'deskripsi'     => 'nullable|string|max:60',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 40 karakter.',
            'nama_kategori.unique'   => 'Kategori dengan nama dan tipe ini sudah terdaftar untuk perusahaan ini.',
            'coa_id.required'        => 'Akun Lawan (COA) wajib dipilih.',
            'coa_id.exists'          => 'Akun Lawan yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'deskripsi.max'          => 'Deskripsi maksimal 60 karakter.',
        ]);

        try {
            $mapping = KasKategoriMapping::create([
                'company_id'    => $activeCompanyId,
                'nama_kategori' => trim(strip_tags($request->nama_kategori)),
                'tipe'          => $request->tipe,
                'coa_id'        => $request->coa_id,
                'deskripsi'     => $request->deskripsi ? trim(strip_tags($request->deskripsi)) : null,
                'is_active'     => true,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori Kas & Bank "' . $mapping->nama_kategori . '" berhasil ditambahkan!',
                'data'    => $mapping
            ]);
        } catch (\Exception $e) {
            Log::error('Error store KasKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan kategori: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit Kas & Bank Category Mapping (Return JSON)
     */
    public function editKasKategoriMapping($id)
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $mapping = KasKategoriMapping::with('coa')->findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori milik PT lain.'], 403);
        }

        return response()->json(['mapping' => $mapping]);
    }

    /**
     * Update Kas & Bank Category Mapping
     */
    public function updateKasKategoriMapping(Request $request, $id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = KasKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori milik PT lain.'], 403);
        }

        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:40',
                Rule::unique('kas_kategori_mappings', 'nama_kategori')->where(function ($q) use ($activeCompanyId, $request) {
                    $q->where('tipe', $request->tipe);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                })->ignore($mapping->id),
            ],
            'tipe'          => 'required|in:Masuk,Keluar',
            'coa_id'        => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'deskripsi'     => 'nullable|string|max:60',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 40 karakter.',
            'nama_kategori.unique'   => 'Kategori dengan nama dan tipe ini sudah terdaftar untuk perusahaan ini.',
            'coa_id.required'        => 'Akun Lawan (COA) wajib dipilih.',
            'coa_id.exists'          => 'Akun Lawan yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'deskripsi.max'          => 'Deskripsi maksimal 60 karakter.',
        ]);

        try {
            $mapping->update([
                'nama_kategori' => trim(strip_tags($request->nama_kategori)),
                'tipe'          => $request->tipe,
                'coa_id'        => $request->coa_id,
                'deskripsi'     => $request->deskripsi ? trim(strip_tags($request->deskripsi)) : null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori Kas & Bank "' . $mapping->nama_kategori . '" berhasil diperbarui!',
                'data'    => $mapping
            ]);
        } catch (\Exception $e) {
            Log::error('Error update KasKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui kategori: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Kas & Bank Category Mapping
     */
    public function destroyKasKategoriMapping($id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = KasKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        // Proteksi kategori bawaan sistem global
        if ($mapping->company_id === null) {
            return response()->json(['status' => 'error', 'message' => 'Kategori template bawaan sistem tidak dapat dihapus.'], 403);
        }

        // Proteksi multi-tenancy
        if ($activeCompanyId && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori milik PT lain.'], 403);
        }

        try {
            $name = $mapping->nama_kategori;
            $mapping->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori "' . $name . '" berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error delete KasKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus kategori: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle Active Status of Kas & Bank Category Mapping
     */
    public function toggleKasKategoriMapping($id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = KasKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori milik PT lain.'], 403);
        }

        try {
            $mapping->is_active = !$mapping->is_active;
            $mapping->save();

            return response()->json([
                'status'    => 'success',
                'is_active' => $mapping->is_active,
                'message'   => 'Status kategori "' . $mapping->nama_kategori . '" berhasil diubah menjadi ' . ($mapping->is_active ? 'Aktif' : 'Non-Aktif') . '!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggle KasKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengubah status kategori: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Petty Cash Category Mapping
     */
    public function storePettyCashMapping(Request $request)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:40',
                Rule::unique('petty_cash_kategori_mappings', 'nama_kategori')->where(function ($q) use ($activeCompanyId) {
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                }),
            ],
            'coa_id'        => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'deskripsi'     => 'nullable|string|max:60',
            'warna_badge'   => 'nullable|string|max:20',
            'icon'          => 'nullable|string|max:50',
        ], [
            'nama_kategori.required' => 'Nama kategori kas kecil wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori kas kecil maksimal 40 karakter.',
            'nama_kategori.unique'   => 'Kategori kas kecil dengan nama ini sudah terdaftar untuk perusahaan ini.',
            'coa_id.required'        => 'Akun Beban / Lawan (COA) wajib dipilih.',
            'coa_id.exists'          => 'Akun COA yang dipilih tidak valid, berupa header, non-aktif, atau milik perusahaan lain.',
            'deskripsi.max'          => 'Deskripsi maksimal 60 karakter.',
        ]);

        try {
            $mapping = PettyCashKategoriMapping::create([
                'company_id'    => $activeCompanyId,
                'nama_kategori' => trim(strip_tags($request->nama_kategori)),
                'coa_id'        => $request->coa_id,
                'deskripsi'     => $request->deskripsi ? trim(strip_tags($request->deskripsi)) : null,
                'warna_badge'   => $request->warna_badge ?: 'primary',
                'icon'          => $request->icon ? trim(strip_tags($request->icon)) : null,
                'is_active'     => true,
                'created_by'    => auth()->id(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori Kas Kecil "' . $mapping->nama_kategori . '" berhasil ditambahkan!',
                'data'    => $mapping
            ]);
        } catch (\Exception $e) {
            Log::error('Error store PettyCashKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan kategori kas kecil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit Petty Cash Category Mapping (Return JSON)
     */
    public function editPettyCashMapping($id)
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $mapping = PettyCashKategoriMapping::with('coa')->findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori Kas Kecil milik PT lain.'], 403);
        }

        return response()->json(['mapping' => $mapping]);
    }

    /**
     * Update Petty Cash Category Mapping
     */
    public function updatePettyCashMapping(Request $request, $id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = PettyCashKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori Kas Kecil milik PT lain.'], 403);
        }

        $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:40',
                Rule::unique('petty_cash_kategori_mappings', 'nama_kategori')->where(function ($q) use ($activeCompanyId) {
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                })->ignore($mapping->id),
            ],
            'coa_id'        => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'deskripsi'     => 'nullable|string|max:60',
            'warna_badge'   => 'nullable|string|max:20',
            'icon'          => 'nullable|string|max:50',
        ], [
            'nama_kategori.required' => 'Nama kategori kas kecil wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori kas kecil maksimal 40 karakter.',
            'nama_kategori.unique'   => 'Kategori kas kecil dengan nama ini sudah terdaftar untuk perusahaan ini.',
            'coa_id.required'        => 'Akun Beban / Lawan (COA) wajib dipilih.',
            'coa_id.exists'          => 'Akun COA yang dipilih tidak valid, berupa header, non-aktif, atau milik perusahaan lain.',
            'deskripsi.max'          => 'Deskripsi maksimal 60 karakter.',
        ]);

        try {
            $mapping->update([
                'nama_kategori' => trim(strip_tags($request->nama_kategori)),
                'coa_id'        => $request->coa_id,
                'deskripsi'     => $request->deskripsi ? trim(strip_tags($request->deskripsi)) : null,
                'warna_badge'   => $request->warna_badge ?: 'primary',
                'icon'          => $request->icon ? trim(strip_tags($request->icon)) : null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori Kas Kecil "' . $mapping->nama_kategori . '" berhasil diperbarui!',
                'data'    => $mapping
            ]);
        } catch (\Exception $e) {
            Log::error('Error update PettyCashKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui kategori kas kecil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Petty Cash Category Mapping
     */
    public function destroyPettyCashMapping($id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = PettyCashKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        // Proteksi kategori bawaan sistem global jika company_id null
        if ($mapping->company_id === null) {
            return response()->json(['status' => 'error', 'message' => 'Kategori template bawaan sistem tidak dapat dihapus.'], 403);
        }

        // Proteksi multi-tenancy
        if ($activeCompanyId && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori Kas Kecil milik PT lain.'], 403);
        }

        try {
            $name = $mapping->nama_kategori;
            $mapping->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori Kas Kecil "' . $name . '" berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error delete PettyCashKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus kategori kas kecil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle Active Status of Petty Cash Category Mapping
     */
    public function togglePettyCashMapping($id)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $mapping = PettyCashKategoriMapping::findOrFail($id);
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        if ($activeCompanyId && $mapping->company_id && $mapping->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Kategori Kas Kecil milik PT lain.'], 403);
        }

        try {
            $mapping->is_active = !$mapping->is_active;
            $mapping->save();

            return response()->json([
                'status'    => 'success',
                'is_active' => $mapping->is_active,
                'message'   => 'Status kategori "' . $mapping->nama_kategori . '" berhasil diubah menjadi ' . ($mapping->is_active ? 'Aktif' : 'Non-Aktif') . '!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggle PettyCashKategoriMapping: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengubah status kategori: ' . $e->getMessage()], 500);
        }
    }
}
