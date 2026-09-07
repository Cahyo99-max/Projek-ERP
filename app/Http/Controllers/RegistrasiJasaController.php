<?php

namespace App\Http\Controllers;

use App\Models\RegistrasiJasa;
use App\Models\MasterBarang;
use App\Models\MasterSupplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrasiJasaController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-barang'); // Re-use master barang permissions

        // Fetch all service transactions with relations
        $jasas = RegistrasiJasa::with(['barang', 'supplier', 'penanggungJawab'])->orderBy('tanggal_mulai', 'desc')->get();

        // Calculate summary metrics
        $totalPengeluaran = $jasas->sum('harga_transaksi');
        $langgananAktif   = $jasas->where('status', 'Aktif')->count();
        $jasaSelesai      = $jasas->where('status', 'Selesai')->count();

        // Dropdowns for Tambah / Edit
        $katalogJasa = MasterBarang::where('tipe_item', 'Jasa')->active()->orderBy('nama_barang', 'asc')->get();
        $suppliers   = MasterSupplier::active()->orderBy('nama_supplier', 'asc')->get();
        $users       = User::orderBy('nama_lengkap', 'asc')->get();

        return view('finance.viewjasa', compact(
            'jasas', 'totalPengeluaran', 'langgananAktif', 'jasaSelesai',
            'katalogJasa', 'suppliers', 'users'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-barang');

        $request->validate([
            'barang_id'           => 'required|integer|exists:master_barang,id',
            'no_invoice'          => 'nullable|string|max:100',
            'supplier_id'         => 'nullable|integer|exists:master_suppliers,id',
            'penanggung_jawab_id' => 'nullable|integer|exists:users,id',
            'harga_transaksi'     => 'required|numeric|min:0',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'              => 'required|in:Aktif,Selesai,Tenggang',
            'keterangan'          => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            RegistrasiJasa::create([
                'barang_id'           => $request->barang_id,
                'no_invoice'          => $request->no_invoice,
                'supplier_id'         => $request->supplier_id,
                'penanggung_jawab_id' => $request->penanggung_jawab_id,
                'harga_transaksi'     => $request->harga_transaksi,
                'tanggal_mulai'       => $request->tanggal_mulai,
                'tanggal_selesai'     => $request->tanggal_selesai,
                'status'              => $request->status,
                'keterangan'          => $request->keterangan,
            ]);

            DB::commit();
            return response()->json(['message' => 'Transaksi Jasa berhasil diregistrasi!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Jasa Error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data jasa.'], 500);
        }
    }

    public function edit(RegistrasiJasa $jasa)
    {
        return response()->json(['jasa' => $jasa]);
    }

    public function update(Request $request, RegistrasiJasa $jasa)
    {
        $this->authorize('edit-barang');

        $request->validate([
            'barang_id'           => 'required|integer|exists:master_barang,id',
            'no_invoice'          => 'nullable|string|max:100',
            'supplier_id'         => 'nullable|integer|exists:master_suppliers,id',
            'penanggung_jawab_id' => 'nullable|integer|exists:users,id',
            'harga_transaksi'     => 'required|numeric|min:0',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'              => 'required|in:Aktif,Selesai,Tenggang',
            'keterangan'          => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $jasa->update([
                'barang_id'           => $request->barang_id,
                'no_invoice'          => $request->no_invoice,
                'supplier_id'         => $request->supplier_id,
                'penanggung_jawab_id' => $request->penanggung_jawab_id,
                'harga_transaksi'     => $request->harga_transaksi,
                'tanggal_mulai'       => $request->tanggal_mulai,
                'tanggal_selesai'     => $request->tanggal_selesai,
                'status'              => $request->status,
                'keterangan'          => $request->keterangan,
            ]);

            DB::commit();
            return response()->json(['message' => 'Data Jasa berhasil diupdate!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Jasa Error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate data jasa.'], 500);
        }
    }

    public function destroy(RegistrasiJasa $jasa)
    {
        $this->authorize('hapus-barang');

        try {
            DB::beginTransaction();
            $jasa->delete();
            DB::commit();
            return response()->json(['message' => 'Data registrasi jasa berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Jasa Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data jasa.'], 500);
        }
    }
}
