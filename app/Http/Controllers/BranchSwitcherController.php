<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSwitcherController extends Controller
{
    /**
     * Ganti cabang aktif user yang sedang login.
     * Cabang yang dipilih harus terdaftar di user_branches milik user ini.
     */
    public function switchBranch(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'branch_id'  => 'nullable|integer|exists:branches,id',
        ]);

        $user = Auth::user();
        $companyId = $request->company_id;
        $branchId  = $request->branch_id;

        try {
            $company = \App\Models\Company::findOrFail($companyId);
            $hasCompanyAccess = $user->hasRole('Super Admin') || $user->companies()->where('companies.id', $companyId)->exists() || $user->branches()->where('company_id', $companyId)->exists();

            if (!$hasCompanyAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak! Kamu tidak memiliki izin ke Perusahaan (PT) ini.',
                ], 403);
            }

            if ($branchId) {
                $branch = Branch::where('company_id', $companyId)->findOrFail($branchId);
                $hasBranchAccess = $user->hasRole('Super Admin') || $user->branches()->where('branches.id', $branchId)->exists();

                if (!$hasBranchAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak! Kamu tidak memiliki izin ke Cabang ini.',
                    ], 403);
                }

                session([
                    'active_company_id'   => $company->id,
                    'active_company_name' => $company->nama_perusahaan,
                    'active_branch_id'    => $branch->id,
                    'active_branch_name'  => $branch->nama_cabang,
                    'active_branch_kode'  => $branch->kode_cabang,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil pindah ke cabang <strong>{$branch->nama_cabang}</strong> ({$company->nama_perusahaan}).",
                ]);
            } else {
                // Accessing PT Pusat Level (No specific branch locked)
                session([
                    'active_company_id'   => $company->id,
                    'active_company_name' => $company->nama_perusahaan,
                    'active_branch_id'    => null,
                    'active_branch_name'  => null,
                    'active_branch_kode'  => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil pindah ke PT Pusat: <strong>{$company->nama_perusahaan}</strong>.",
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Gagal switch cabang/PT: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem. Gagal pindah akses.',
            ], 500);
        }
    }

    /**
     * Mendapatkan daftar PT & cabang yang bisa diakses oleh user yang sedang login.
     * Digunakan oleh navbar dropdown.
     */
    public function getBranchesList()
    {
        try {
            $user = Auth::user();

            $userHasCompanies = $user->companies()->exists();
            $userHasBranches  = $user->branches()->exists();

            if ($user->hasRole('Super Admin') && !$userHasCompanies && !$userHasBranches) {
                // Fallback for Super Admin with no explicit assignments at all
                $companies = \App\Models\Company::where('status', 'Active')
                    ->with(['branches' => function ($q) {
                        $q->where('status', 'Active');
                    }])
                    ->get();
            } else {
                $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

                // Get user's assigned companies
                $companies = $user->companies()
                                  ->where('companies.status', 'Active')
                                  ->with(['branches' => function ($q) use ($userBranchIds) {
                                      $q->where('branches.status', 'Active')
                                        ->whereIn('branches.id', $userBranchIds);
                                  }])
                                  ->get();

                // Check if user has assigned branches under companies not explicitly in user_companies
                $branchCompanyIds  = $user->branches()->pluck('branches.company_id')->filter()->unique();
                $missingCompanyIds = $branchCompanyIds->diff($companies->pluck('id'));

                if ($missingCompanyIds->isNotEmpty()) {
                    $additionalCompanies = \App\Models\Company::whereIn('id', $missingCompanyIds)
                        ->where('status', 'Active')
                        ->with(['branches' => function ($q) use ($userBranchIds) {
                            $q->where('branches.status', 'Active')
                              ->whereIn('branches.id', $userBranchIds);
                        }])
                        ->get();
                    $companies = $companies->concat($additionalCompanies);
                }
            }

            return response()->json([
                'success'           => true,
                'companies'         => $companies,
                'active_company_id' => session('active_company_id'),
                'active_branch_id'  => session('active_branch_id'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Gagal memuat list PT & Cabang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar PT & Cabang.',
            ], 500);
        }
    }
}
