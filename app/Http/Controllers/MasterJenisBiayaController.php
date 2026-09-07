<?php

namespace App\Http\Controllers;

use App\Models\MasterJenisBiaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MasterJenisBiayaController extends Controller
{
    public function store(Request $request)
    {
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'nama_jenis_biaya' => 'required|string|max:255',
            'kode_biaya' => [
                'nullable', 'string', 'max:50',
                Rule::unique('master_jenis_biayas', 'kode_biaya')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'masuk_hpp' => 'boolean',
            'alokasi_biaya' => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty,Tanpa Alokasi',
            'coa_id' => 'nullable|exists:chart_of_accounts,id',
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            MasterJenisBiaya::create([
                'company_id' => $activeCompanyId,
                'nama_jenis_biaya' => $request->nama_jenis_biaya,
                'kode_biaya' => $request->kode_biaya,
                'masuk_hpp' => $request->has('masuk_hpp') ? (bool)$request->masuk_hpp : false,
                'alokasi_biaya' => $request->alokasi_biaya,
                'coa_id' => $request->coa_id ?: null,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Jenis Biaya berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Master Jenis Biaya: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Jenis Biaya.'], 500);
        }
    }

    public function edit(MasterJenisBiaya $masterJenisBiaya)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $masterJenisBiaya->company_id && $masterJenisBiaya->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Jenis Biaya milik PT lain.'], 403);
        }

        return response()->json(['jenis_biaya' => $masterJenisBiaya]);
    }

    public function update(Request $request, MasterJenisBiaya $masterJenisBiaya)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $masterJenisBiaya->company_id && $masterJenisBiaya->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit Jenis Biaya milik PT lain.'], 403);
        }

        $request->validate([
            'nama_jenis_biaya' => 'required|string|max:255',
            'kode_biaya' => [
                'nullable', 'string', 'max:50',
                Rule::unique('master_jenis_biayas', 'kode_biaya')->ignore($masterJenisBiaya->id)
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'masuk_hpp' => 'boolean',
            'alokasi_biaya' => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty,Tanpa Alokasi',
            'coa_id' => 'nullable|exists:chart_of_accounts,id',
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $masterJenisBiaya->update([
                'nama_jenis_biaya' => $request->nama_jenis_biaya,
                'kode_biaya' => $request->kode_biaya,
                'masuk_hpp' => $request->has('masuk_hpp') ? (bool)$request->masuk_hpp : false,
                'alokasi_biaya' => $request->alokasi_biaya,
                'coa_id' => $request->coa_id ?: null,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Jenis Biaya berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Master Jenis Biaya: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Jenis Biaya.'], 500);
        }
    }

    public function destroy(MasterJenisBiaya $masterJenisBiaya)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $masterJenisBiaya->company_id && $masterJenisBiaya->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus Jenis Biaya milik PT lain.'], 403);
        }

        try {
            $masterJenisBiaya->delete();
            return response()->json(['message' => 'Jenis Biaya berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete Master Jenis Biaya: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus Jenis Biaya.'], 500);
        }
    }
}
