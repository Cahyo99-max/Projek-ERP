<?php

namespace App\Http\Controllers;

use App\Models\MasterTipeAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterTipeAkunController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kode_tipe' => 'required|string|max:50|unique:master_tipe_akuns,kode_tipe',
            'nama_tipe' => 'required|string|max:255',
            'kategori_laporan' => 'required|in:neraca,laba_rugi',
            'saldo_normal_default' => 'required|in:debit,credit',
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            MasterTipeAkun::create([
                'kode_tipe' => strtoupper($request->kode_tipe),
                'nama_tipe' => $request->nama_tipe,
                'kategori_laporan' => $request->kategori_laporan,
                'saldo_normal_default' => $request->saldo_normal_default,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Master Tipe Akun berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Tipe Akun.'], 500);
        }
    }

    public function edit(MasterTipeAkun $masterTipeAkun)
    {
        return response()->json(['tipe_akun' => $masterTipeAkun]);
    }

    public function update(Request $request, MasterTipeAkun $masterTipeAkun)
    {
        $request->validate([
            'kode_tipe' => 'required|string|max:50|unique:master_tipe_akuns,kode_tipe,' . $masterTipeAkun->id,
            'nama_tipe' => 'required|string|max:255',
            'kategori_laporan' => 'required|in:neraca,laba_rugi',
            'saldo_normal_default' => 'required|in:debit,credit',
            'status' => 'required|in:Active,Inactive',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $masterTipeAkun->update([
                'kode_tipe' => strtoupper($request->kode_tipe),
                'nama_tipe' => $request->nama_tipe,
                'kategori_laporan' => $request->kategori_laporan,
                'saldo_normal_default' => $request->saldo_normal_default,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json(['message' => 'Master Tipe Akun berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Tipe Akun.'], 500);
        }
    }

    public function destroy(MasterTipeAkun $masterTipeAkun)
    {
        try {
            if ($masterTipeAkun->accounts()->count() > 0) {
                return response()->json(['message' => 'Tipe Akun ini sedang digunakan oleh Akun COA, tidak dapat dihapus.'], 422);
            }

            $masterTipeAkun->delete();
            return response()->json(['message' => 'Master Tipe Akun berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus Tipe Akun.'], 500);
        }
    }
}
