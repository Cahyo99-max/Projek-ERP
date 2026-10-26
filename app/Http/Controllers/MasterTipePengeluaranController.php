<?php

namespace App\Http\Controllers;

use App\Models\MasterTipePengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MasterTipePengeluaranController extends Controller
{
    public function store(Request $request)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $request->validate([
            'nama_tipe'     => 'required|string|max:40',
            'kode_tipe'     => [
                'nullable', 'string', 'max:10',
                Rule::unique('master_tipe_pengeluaran', 'kode_tipe')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
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
            'coa_kredit_id' => 'nullable|exists:chart_of_accounts,id',
            'status'        => 'required|in:Active,Inactive',
            'deskripsi'     => 'nullable|string|max:40',
        ], [
            'nama_tipe.required' => 'Nama Tipe Pengeluaran wajib diisi.',
            'coa_id.required'    => 'Alokasi Akun COA Pengeluaran / Beban wajib dipilih.',
            'coa_id.exists'      => 'Akun COA yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'status.required'    => 'Status wajib dipilih.',
        ]);

        try {
            MasterTipePengeluaran::create([
                'company_id'    => $activeCompanyId,
                'nama_tipe'     => $request->nama_tipe,
                'kode_tipe'     => $request->kode_tipe,
                'coa_id'        => (int) $request->coa_id,
                'coa_kredit_id' => $request->coa_kredit_id ?: null,
                'status'        => $request->status,
                'deskripsi'     => $request->deskripsi,
            ]);

            return response()->json(['message' => 'Tipe Pengeluaran berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Master Tipe Pengeluaran: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Tipe Pengeluaran: ' . $e->getMessage()], 500);
        }
    }

    public function edit(MasterTipePengeluaran $masterTipePengeluaran)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        if ($activeCompanyId && $masterTipePengeluaran->company_id && $masterTipePengeluaran->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Pengeluaran milik PT lain.'], 403);
        }

        return response()->json(['tipe_pengeluaran' => $masterTipePengeluaran]);
    }

    public function update(Request $request, MasterTipePengeluaran $masterTipePengeluaran)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        if ($activeCompanyId && $masterTipePengeluaran->company_id && $masterTipePengeluaran->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Pengeluaran milik PT lain.'], 403);
        }

        $request->validate([
            'nama_tipe'     => 'required|string|max:40',
            'coa_id'        => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false)->where('is_active', true);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                }),
            ],
            'status'        => 'required|in:Active,Inactive',
            'deskripsi'     => 'nullable|string|max:40',
        ], [
            'nama_tipe.required' => 'Nama Tipe Pengeluaran wajib diisi.',
            'coa_id.required'    => 'Alokasi Akun COA Pengeluaran / Beban wajib dipilih.',
            'coa_id.exists'      => 'Akun COA yang dipilih tidak valid, berupa akun header, non-aktif, atau bukan milik perusahaan ini.',
            'status.required'    => 'Status wajib dipilih.',
        ]);

        try {
            $masterTipePengeluaran->update([
                'nama_tipe' => $request->nama_tipe,
                'coa_id'    => (int) $request->coa_id,
                'status'    => $request->status,
                'deskripsi' => $request->deskripsi,
            ]);

            return response()->json(['message' => 'Tipe Pengeluaran berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Master Tipe Pengeluaran: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Tipe Pengeluaran.'], 500);
        }
    }

    public function destroy(MasterTipePengeluaran $masterTipePengeluaran)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $masterTipePengeluaran->company_id && $masterTipePengeluaran->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Pengeluaran milik PT lain.'], 403);
        }

        try {
            $masterTipePengeluaran->delete();
            return response()->json(['message' => 'Tipe Pengeluaran berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error delete Master Tipe Pengeluaran: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus Tipe Pengeluaran.'], 500);
        }
    }
}
