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
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $request->validate([
            'nama_jenis_biaya' => 'required|string|max:255',
            'kode_biaya' => [
                'nullable', 'string', 'max:50',
                Rule::unique('master_jenis_biayas', 'kode_biaya')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'masuk_hpp' => 'boolean',
            'alokasi_biaya' => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty,Tanpa Alokasi',
            'coa_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama_jenis_biaya.required' => 'Nama Jenis Biaya wajib diisi.',
            'coa_id.required'           => 'Akun COA (Mapping) wajib dipilih.',
            'coa_id.exists'             => 'Akun COA yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'alokasi_biaya.required'    => 'Alokasi Biaya wajib dipilih.',
            'status.required'           => 'Status wajib dipilih.',
        ]);

        try {
            MasterJenisBiaya::create([
                'company_id' => $activeCompanyId,
                'nama_jenis_biaya' => $request->nama_jenis_biaya,
                'kode_biaya' => $request->kode_biaya,
                'masuk_hpp' => $request->has('masuk_hpp') ? (bool)$request->masuk_hpp : false,
                'alokasi_biaya' => $request->alokasi_biaya,
                'coa_id' => (int) $request->coa_id,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Jenis Biaya berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Master Jenis Biaya: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Jenis Biaya: ' . $e->getMessage()], 500);
        }
    }

    public function edit(MasterJenisBiaya $masterJenisBiaya)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        if ($activeCompanyId && $masterJenisBiaya->company_id && $masterJenisBiaya->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Jenis Biaya milik PT lain.'], 403);
        }

        return response()->json(['jenis_biaya' => $masterJenisBiaya]);
    }

    public function update(Request $request, MasterJenisBiaya $masterJenisBiaya)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

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
            'coa_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama_jenis_biaya.required' => 'Nama Jenis Biaya wajib diisi.',
            'coa_id.required'           => 'Akun COA (Mapping) wajib dipilih.',
            'coa_id.exists'             => 'Akun COA yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'alokasi_biaya.required'    => 'Alokasi Biaya wajib dipilih.',
            'status.required'           => 'Status wajib dipilih.',
        ]);

        try {
            $masterJenisBiaya->update([
                'nama_jenis_biaya' => $request->nama_jenis_biaya,
                'kode_biaya' => $request->kode_biaya,
                'masuk_hpp' => $request->has('masuk_hpp') ? (bool)$request->masuk_hpp : false,
                'alokasi_biaya' => $request->alokasi_biaya,
                'coa_id' => (int) $request->coa_id,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Jenis Biaya berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Master Jenis Biaya: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Jenis Biaya: ' . $e->getMessage()], 500);
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
