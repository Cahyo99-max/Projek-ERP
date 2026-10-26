<?php

namespace App\Http\Controllers;

use App\Helpers\AccountMappingHelper;
use App\Models\AccountMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountMappingController extends Controller
{
    /**
     * Redirect route /settings/account-mappings ke Unified Akuntansi Terpadu View
     */
    public function index()
    {
        return redirect()->route('akuntansi.index', ['tab' => 'system_mapping']);
    }

    public function update(Request $request)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.group' => 'required|string',
            'mappings.*.mapping_key' => 'required|string',
            'mappings.*.coa_id' => 'nullable|exists:chart_of_accounts,id',
            'mappings.*.description' => 'nullable|string',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($request->mappings as $data) {
                if (!empty($data['coa_id'])) {
                    AccountMappingHelper::setMapping(
                        $data['group'],
                        $data['mapping_key'],
                        (int) $data['coa_id'],
                        $data['description'] ?? null,
                        $activeCompanyId
                    );
                } else {
                    // Delete mapping if unselected for active company
                    AccountMapping::where('company_id', $activeCompanyId)
                        ->where('group', $data['group'])
                        ->where('mapping_key', $data['mapping_key'])
                        ->delete();

                    \Illuminate\Support\Facades\Cache::forget("account_mapping_{$activeCompanyId}_{$data['group']}_{$data['mapping_key']}");
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            AccountMappingHelper::clearCache($activeCompanyId);

            return response()->json(['message' => 'Pengaturan Account Mapping berhasil disimpan untuk PT ini!']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Error update Account Mapping: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Account Mapping: ' . $e->getMessage()], 500);
        }
    }
}
