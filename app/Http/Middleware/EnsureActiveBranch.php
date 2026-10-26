<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class EnsureActiveBranch
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            // If user has 0 assigned branches, ensure active_branch_id remains null
            $userBranchesCount = $user->branches()->where('branches.status', 'Active')->count();

            // If session not initialized
            if (!session()->has('active_company_id') && !session()->has('active_branch_id')) {
                // 1. Ambil PT Default yang dipilih user
                $defaultCompany = $user->companies()
                                       ->where('companies.status', 'Active')
                                       ->orderByRaw('user_companies.is_default DESC')
                                       ->first();

                if (!$defaultCompany && $user->hasRole('Super Admin')) {
                    $defaultCompany = \App\Models\Company::where('status', 'Active')->first();
                }

                // 2. Cek apakah ada Cabang yang EKSPLISIT dijadikan default (is_default = 1)
                $defaultBranch = $user->branches()
                                      ->with('company')
                                      ->where('branches.status', 'Active')
                                      ->wherePivot('is_default', 1)
                                      ->first();

                // 3. Pasang sesi sesuai preferensi default user
                if ($defaultBranch) {
                    session([
                        'active_branch_id'    => $defaultBranch->id,
                        'active_branch_name'  => $defaultBranch->nama_cabang,
                        'active_branch_kode'  => $defaultBranch->kode_cabang,
                        'active_company_id'   => $defaultBranch->company_id,
                        'active_company_name' => $defaultBranch->company?->nama_perusahaan ?? $defaultBranch->nama_perusahaan,
                    ]);
                    return $next($request);
                }

                if ($defaultCompany) {
                    session([
                        'active_branch_id'    => null,
                        'active_branch_name'  => null,
                        'active_branch_kode'  => null,
                        'active_company_id'   => $defaultCompany->id,
                        'active_company_name' => $defaultCompany->nama_perusahaan,
                    ]);
                    return $next($request);
                }

                // If no active branch found, abort or render error view
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Akun Anda belum diasosiasikan dengan cabang mana pun. Silakan hubungi Administrator.'
                    ], 403);
                }

                return response()->view('errors.no_branch', [], 403);
            }

            // ── Backfill active_company_id for old sessions ──────────────────
            // Sessions created before multi-company feature have active_branch_id
            // but no active_company_id, causing company-filtered queries to fail.
            if (session()->has('active_branch_id') && !session()->has('active_company_id')) {
                $branch = \App\Models\Branch::with('company')->find(session('active_branch_id'));
                if ($branch && $branch->company_id) {
                    session([
                        'active_company_id'   => $branch->company_id,
                        'active_company_name' => $branch->company?->nama_perusahaan ?? $branch->nama_perusahaan,
                    ]);
                }
            }
        }

        return $next($request);
    }
}
