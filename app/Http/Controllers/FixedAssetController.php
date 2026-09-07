<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\AssetDepreciation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-gr');

        $activeBranchId = session('active_branch_id');

        // Fetch assets belonging to the active branch
        $assets = FixedAsset::whereHas('grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->with(['barang.satuan', 'grDetail.goodsReceipt'])
            ->orderBy('tanggal_perolehan', 'desc')
            ->get();

        // Calculate summary metrics
        $totalPerolehan = $assets->sum('harga_perolehan');
        $totalNilaiBuku = $assets->sum('nilai_buku_saat_ini');
        $totalAkumulasiSusut = $totalPerolehan - $totalNilaiBuku;

        // Get latest depreciation period run in this branch
        $latestDep = AssetDepreciation::whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->orderBy('periode_penyusutan', 'desc')
            ->first();
            
        $latestPeriode = $latestDep ? $latestDep->periode_penyusutan : null;

        // 1. Calculate Monthly summaries
        $monthlyRecaps = AssetDepreciation::whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->select('periode_penyusutan', DB::raw('count(distinct asset_id) as total_assets'), DB::raw('sum(nilai_penyusutan) as total_penyusutan'))
            ->groupBy('periode_penyusutan')
            ->orderBy('periode_penyusutan', 'desc')
            ->get();

        // 2. Calculate next month's projected depreciation
        $projectedNextMonth = 0;
        $projectedAssetsCount = 0;
        foreach ($assets as $asset) {
            if ($asset->status === 'Aktif' && $asset->nilai_buku_saat_ini > $asset->nilai_residu) {
                $hargaPerolehan = floatval($asset->harga_perolehan);
                $nilaiResidu = floatval($asset->nilai_residu);
                $masaManfaat = intval($asset->masa_manfaat);
                if ($masaManfaat > 0) {
                    $susutBulanan = ($hargaPerolehan - $nilaiResidu) / $masaManfaat;
                    $nilaiBukuSblm = floatval($asset->nilai_buku_saat_ini);
                    if (($nilaiBukuSblm - $susutBulanan) < $nilaiResidu) {
                        $susutBulanan = $nilaiBukuSblm - $nilaiResidu;
                    }
                    if ($susutBulanan > 0) {
                        $projectedNextMonth += $susutBulanan;
                        $projectedAssetsCount++;
                    }
                }
            }
        }

        // 3. Dropdowns for editing assets
        $departments = \App\Models\Department::active()->orderBy('department_name', 'asc')->get();
        $suppliers   = \App\Models\MasterSupplier::active()->orderBy('nama_supplier', 'asc')->get();
        $users       = \App\Models\User::orderBy('nama_lengkap', 'asc')->get();
        $gudangs     = \App\Models\MasterGudang::active()->orderBy('nama_gudang', 'asc')->get();

        return view('finance.viewassets', compact(
            'assets',
            'totalPerolehan',
            'totalNilaiBuku',
            'totalAkumulasiSusut',
            'latestPeriode',
            'monthlyRecaps',
            'projectedNextMonth',
            'projectedAssetsCount',
            'departments',
            'suppliers',
            'users',
            'gudangs'
        ));
    }

    public function getDepreciationHistory(FixedAsset $asset)
    {
        $asset->load('depreciations.creator');
        
        $history = $asset->depreciations->map(function ($dep) {
            return [
                'periode' => $dep->periode_penyusutan,
                'nilai_penyusutan' => floatval($dep->nilai_penyusutan),
                'nilai_sebelum' => floatval($dep->nilai_buku_sebelumnya),
                'nilai_setelah' => floatval($dep->nilai_buku_setelahnya),
                'tanggal_proses' => $dep->created_at->format('d-m-Y H:i'),
                'proses_oleh' => $dep->creator ? $dep->creator->nama_lengkap : 'System',
            ];
        });

        return response()->json([
            'asset_code' => $asset->kode_aset,
            'asset_name' => $asset->barang->nama_barang,
            'history' => $history
        ]);
    }

    public function runDepreciation(Request $request)
    {
        try {
            $request->validate([
                'periode' => 'required|date_format:Y-m',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Format periode tidak valid! Gunakan format YYYY-MM.',
                'errors' => $e->errors()
            ], 422);
        }

        $periode = $request->periode;
        $activeBranchId = session('active_branch_id');

        // Check if depreciation has already been run for this period
        $alreadyRun = AssetDepreciation::where('periode_penyusutan', $periode)
            ->whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->exists();

        if ($alreadyRun) {
            return response()->json([
                'message' => "Penyusutan untuk periode {$periode} sudah pernah dijalankan di cabang ini!"
            ], 422);
        }

        // We must enforce sequential monthly depreciation. 
        // Find latest period run, if any, and make sure new period is the next month.
        $latestDep = AssetDepreciation::whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->orderBy('periode_penyusutan', 'desc')
            ->first();

        if ($latestDep) {
            $latestTime = strtotime($latestDep->periode_penyusutan . '-01');
            $newTime = strtotime($periode . '-01');
            $expectedNextTime = strtotime('+1 month', $latestTime);

            if ($newTime < $latestTime) {
                return response()->json([
                    'message' => "Periode {$periode} sudah terlewati. Anda tidak bisa menyusutkan periode sebelum penyusutan terakhir ({$latestDep->periode_penyusutan})!"
                ], 422);
            }

            if ($newTime > $expectedNextTime) {
                $expectedMonth = date('Y-m', $expectedNextTime);
                return response()->json([
                    'message' => "Periode penyusutan loncat. Anda harus menyusutkan secara berurutan. Periode selanjutnya yang diharapkan adalah: {$expectedMonth}."
                ], 422);
            }
        }

        $lastDayOfPeriod = date('Y-m-t', strtotime($periode . '-01'));

        try {
            DB::beginTransaction();

            // Find assets eligible for depreciation
            // 1. Status is 'Aktif'
            // 2. Nilai buku > Nilai residu
            // 3. Purchase date <= end of depreciation period
            // 4. Belongs to active branch
            $assets = FixedAsset::where('status', 'Aktif')
                ->where('nilai_buku_saat_ini', '>', DB::raw('nilai_residu'))
                ->where('tanggal_perolehan', '<=', $lastDayOfPeriod)
                ->whereHas('grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                    $q->where('branch_id', $activeBranchId);
                })
                ->get();

            if ($assets->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tidak ditemukan aset aktif yang memenuhi syarat untuk disusutkan pada periode ini.'
                ], 422);
            }

            $processedCount = 0;
            $totalSusut = 0;

            foreach ($assets as $asset) {
                $hargaPerolehan = floatval($asset->harga_perolehan);
                $nilaiResidu = floatval($asset->nilai_residu);
                $masaManfaat = intval($asset->masa_manfaat);

                if ($masaManfaat <= 0) continue;

                // Straight Line monthly calculation
                $susutBulanan = ($hargaPerolehan - $nilaiResidu) / $masaManfaat;
                $nilaiBukuSblm = floatval($asset->nilai_buku_saat_ini);

                // If sisa book value goes below residual, adjust monthly depreciation
                if (($nilaiBukuSblm - $susutBulanan) < $nilaiResidu) {
                    $susutBulanan = $nilaiBukuSblm - $nilaiResidu;
                }

                if ($susutBulanan <= 0) continue;

                $nilaiBukuStlh = $nilaiBukuSblm - $susutBulanan;

                // Write Log
                AssetDepreciation::create([
                    'asset_id' => $asset->id,
                    'periode_penyusutan' => $periode,
                    'nilai_penyusutan' => $susutBulanan,
                    'nilai_buku_sebelumnya' => $nilaiBukuSblm,
                    'nilai_buku_setelahnya' => $nilaiBukuStlh,
                ]);

                // Update Asset
                $asset->nilai_buku_saat_ini = $nilaiBukuStlh;
                if ($nilaiBukuStlh <= $nilaiResidu) {
                    $asset->status = 'Habis Susut';
                }
                $asset->save();

                $processedCount++;
                $totalSusut += $susutBulanan;
            }

            if ($processedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Penyusutan dibatalkan karena tidak ada aset yang memiliki nilai susut > 0.'
                ], 422);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Penyusutan periode {$periode} berhasil diproses untuk {$processedCount} aset dengan total susut Rp " . number_format($totalSusut, 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Depreciation Run Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rollbackDepreciation(Request $request)
    {
        try {
            $request->validate([
                'periode' => 'required|date_format:Y-m',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Format periode tidak valid!',
                'errors' => $e->errors()
            ], 422);
        }

        $periode = $request->periode;
        $activeBranchId = session('active_branch_id');

        // Check if there is depreciation logs for this period in active branch
        $depreciations = AssetDepreciation::where('periode_penyusutan', $periode)
            ->whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->get();

        if ($depreciations->isEmpty()) {
            return response()->json([
                'message' => "Tidak ditemukan riwayat penyusutan untuk periode {$periode}!"
            ], 422);
        }

        // Validate that this is the latest run period
        $latestDep = AssetDepreciation::whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->orderBy('periode_penyusutan', 'desc')
            ->first();

        if ($latestDep && $latestDep->periode_penyusutan !== $periode) {
            return response()->json([
                'message' => "Hanya penyusutan periode teranyar ({$latestDep->periode_penyusutan}) yang dapat dibatalkan (rollback)!"
            ], 422);
        }

        try {
            DB::beginTransaction();

            $revertCount = 0;
            foreach ($depreciations as $dep) {
                $asset = $dep->asset;

                // Revert book value & status
                $asset->nilai_buku_saat_ini = $dep->nilai_buku_sebelumnya;
                if ($asset->status === 'Habis Susut') {
                    $asset->status = 'Aktif';
                }
                $asset->save();

                // Delete log
                $dep->delete();
                $revertCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Penyusutan periode {$periode} berhasil dibatalkan untuk {$revertCount} aset."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Depreciation Rollback Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMonthlyDetail($periode)
    {
        $this->authorize('lihat-gr');
        $activeBranchId = session('active_branch_id');

        $details = AssetDepreciation::where('periode_penyusutan', $periode)
            ->whereHas('asset.grDetail.goodsReceipt.po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->with(['asset.barang', 'creator'])
            ->get();

        $mapped = $details->map(function ($d) {
            return [
                'asset_code' => $d->asset->kode_aset,
                'asset_name' => $d->asset->barang->nama_barang,
                'nilai_penyusutan' => floatval($d->nilai_penyusutan),
                'nilai_sebelum' => floatval($d->nilai_buku_sebelumnya),
                'nilai_setelah' => floatval($d->nilai_buku_setelahnya),
                'proses_oleh' => $d->creator ? $d->creator->nama_lengkap : 'System',
                'tanggal_proses' => $d->created_at->format('d-m-Y H:i')
            ];
        });

        return response()->json([
            'periode' => date('M Y', strtotime($periode . '-01')),
            'details' => $mapped,
            'total_penyusutan' => floatval($details->sum('nilai_penyusutan'))
        ]);
    }

    public function edit(FixedAsset $asset)
    {
        $this->authorize('lihat-gr');
        return response()->json(['asset' => $asset]);
    }

    public function update(Request $request, FixedAsset $asset)
    {
        $this->authorize('lihat-gr'); // Re-use GR receiver/finance auth

        $request->validate([
            'nomor_seri'          => 'nullable|string|max:100',
            'gudang_id'           => 'nullable|integer|exists:master_gudang,id',
            'departemen_id'       => 'nullable|integer|exists:departments,id',
            'supplier_id'         => 'nullable|integer|exists:master_suppliers,id',
            'penanggung_jawab_id' => 'nullable|integer|exists:users,id',
            'kondisi'             => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'status'              => 'required|in:Aktif,Habis Susut,Dijual,Rusak/Dibuang',
            'catatan'             => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $asset->update([
                'nomor_seri'          => $request->nomor_seri,
                'gudang_id'           => $request->gudang_id,
                'departemen_id'       => $request->departemen_id,
                'supplier_id'         => $request->supplier_id,
                'penanggung_jawab_id' => $request->penanggung_jawab_id,
                'kondisi'             => $request->kondisi,
                'status'              => $request->status,
                'catatan'             => $request->catatan,
            ]);

            DB::commit();
            return response()->json(['message' => 'Data Aset Tetap berhasil diupdate!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Fixed Asset Error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengupdate data aset.'], 500);
        }
    }
}
