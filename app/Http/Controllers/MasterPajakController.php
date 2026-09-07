<?php

namespace App\Http\Controllers;

use App\Models\MasterPajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterPajakController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-pajak');
        $pajaks = MasterPajak::forActiveCompany()->latest()->get();
        return view('master_keuangan.ppn.viewpajak', compact('pajaks'));
    }

    public function generateKode()
    {
        $this->authorize('tambah-pajak');
        $activeCompanyId = session('active_company_id');

        $latest = MasterPajak::whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->where('kode_pajak', 'LIKE', 'TX%')
            ->orderByRaw('CAST(SUBSTRING(kode_pajak, 3) AS UNSIGNED) DESC')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->kode_pajak, 2);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        $nextCode = 'TX' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return response()->json(['kode' => $nextCode]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-pajak');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'kode_pajak' => [
                'required', 'string', 'max:5',
                Rule::unique('master_pajaks', 'kode_pajak')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'nama_pajak'  => 'required|string|max:15',
            'tarif'       => 'required|numeric|min:0|max:9999999999',
            'keterangan'  => 'nullable|string|max:20',
            'status'      => 'required|in:Active,Inactive',
        ], [
            'kode_pajak.max' => 'Kode pajak tidak boleh melebihi 5 karakter yang telah ditentukan.',
            'nama_pajak.max' => 'Nama pajak tidak boleh melebihi 15 karakter yang telah ditentukan.',
            'tarif.max'      => 'Tarif tidak boleh melebihi 10 digit yang telah ditentukan.',
            'keterangan.max' => 'Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.',
            'kode_pajak.unique' => 'Kode pajak ini sudah ada.',
        ]);

        try {
            $pajak = MasterPajak::create(array_merge($request->all(), [
                'company_id' => $activeCompanyId,
                'kode_pajak' => strtoupper(trim($request->kode_pajak)),
            ]));
            return response()->json([
                'message' => 'Data pajak berhasil ditambahkan!',
                'pajak' => $pajak
            ]);
        } catch (\Exception $e) {
            Log::error('Error store Pajak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data.'], 500);
        }
    }

    public function edit(MasterPajak $pajak)
    {
        $this->authorize('edit-pajak');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $pajak->company_id && $pajak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data pajak milik PT lain.'], 403);
        }
        return response()->json(['pajak' => $pajak]);
    }

    public function update(Request $request, MasterPajak $pajak)
    {
        $this->authorize('edit-pajak');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $pajak->company_id && $pajak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit data pajak milik PT lain.'], 403);
        }

        // Kode Pajak tidak boleh diubah saat edit
        $request->merge(['kode_pajak' => $pajak->kode_pajak]);

        $request->validate([
            'nama_pajak' => 'required|string|max:15',
            'tarif'      => 'required|numeric|min:0|max:9999999999',
            'keterangan' => 'nullable|string|max:20',
            'status'     => 'required|in:Active,Inactive',
        ], [
            'nama_pajak.max' => 'Nama pajak tidak boleh melebihi 15 karakter yang telah ditentukan.',
            'tarif.max'      => 'Tarif tidak boleh melebihi 10 digit yang telah ditentukan.',
            'keterangan.max' => 'Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.',
        ]);

        try {
            $pajak->update([
                'nama_pajak' => trim($request->nama_pajak),
                'tarif'      => (float) $request->tarif,
                'keterangan' => $request->keterangan ? trim($request->keterangan) : null,
                'status'     => $request->status,
            ]);
            return response()->json(['message' => 'Data pajak berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Pajak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data.'], 500);
        }
    }

    public function destroy(MasterPajak $pajak)
    {
        $this->authorize('hapus-pajak');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $pajak->company_id && $pajak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus data pajak milik PT lain.'], 403);
        }

        try {
            $pajak->delete();
            return response()->json(['message' => 'Data pajak berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete Pajak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data.'], 500);
        }
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $this->authorize('lihat-pajak');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        $pajaks = DB::table('master_pajaks')
            ->select('kode_pajak', 'nama_pajak', 'tarif', 'keterangan', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Pajak</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Pajak</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Tarif (%)</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Keterangan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($pajaks as $p) {
            $statusStyle = $p->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $p->kode_pajak . '</style><center>',
                '<style border="thin">' . $p->nama_pajak . '</style><left>',
                '<style border="thin">' . number_format((float)$p->tarif, 2) . '</style><center>',
                '<style border="thin">' . ($p->keterangan ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_pajak_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── TEMPLATE IMPORT ─────────────────────────────────────────────────────
    public function downloadTemplate()
    {
        $this->authorize('lihat-pajak');

        $rows = [
            ['Kode Pajak', 'Nama Pajak', 'Tarif (%)', 'Keterangan', 'Status'],
            ['PPN11', 'PPN 11%', '11.00', 'Pajak PPN 11%', 'Active'],
            ['PPN12', 'PPN 12%', '12.00', 'Pajak PPN 12%', 'Active'],
            ['NON', 'Non Pajak', '0.00', 'Tanpa pajak', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_pajak.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT ──────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-pajak');

        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,csv,txt',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $file      = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());

        ini_set('memory_limit', '256M');
        set_time_limit(300);
        DB::disableQueryLog();

        $rows = [];
        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($file->path(), 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            if (!$xlsx = SimpleXLSX::parse($file->path())) {
                return response()->json(['message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()], 422);
            }
            $rows = $xlsx->rows();
        }

        if (count($rows) < 2) {
            return response()->json(['message' => 'File kosong atau tidak memiliki data!'], 422);
        }

        $processedCodes = [];
        $insertData     = [];
        $now            = now()->toDateTimeString();

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawKode        = isset($row[0]) ? strtoupper(trim($row[0])) : '';
                $rawNama        = isset($row[1]) ? trim($row[1]) : '';
                $rawTarif       = isset($row[2]) ? trim($row[2]) : '';
                $rawKeterangan  = isset($row[3]) ? trim($row[3]) : '';
                $rawStatus      = isset($row[4]) ? trim($row[4]) : 'Active';
                $rowNum         = $i + 1;

                // ── Validasi baris ──────────────────────────────────────────
                if (empty($rawKode)) {
                    throw new \Exception("Baris {$rowNum}: Kode Pajak wajib diisi.");
                }
                if (strlen($rawKode) > 5) {
                    throw new \Exception("Baris {$rowNum}: Kode Pajak tidak boleh melebihi 5 karakter yang telah ditentukan.");
                }
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Pajak wajib diisi.");
                }
                if (strlen($rawNama) > 15) {
                    throw new \Exception("Baris {$rowNum}: Nama Pajak tidak boleh melebihi 15 karakter yang telah ditentukan.");
                }
                if (!is_numeric($rawTarif) || (float)$rawTarif < 0) {
                    throw new \Exception("Baris {$rowNum}: Tarif harus berupa angka non-negatif.");
                }
                if (strlen((string)floor((float)$rawTarif)) > 10) {
                    throw new \Exception("Baris {$rowNum}: Tarif tidak boleh melebihi 10 digit yang telah ditentukan.");
                }
                if (!empty($rawKeterangan) && strlen($rawKeterangan) > 20) {
                    throw new \Exception("Baris {$rowNum}: Keterangan tidak boleh melebihi 20 karakter yang telah ditentukan.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // ── Cek duplikat dalam file ──────────────────────────────────
                $kodeLower = strtolower($rawKode);
                if (isset($processedCodes[$kodeLower])) {
                    throw new \Exception("Baris {$rowNum}: Kode Pajak '{$rawKode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                // ── Cek duplikat di database (scoped to active company) ──────
                $existsInDb = DB::table('master_pajaks')
                    ->where('kode_pajak', $rawKode)
                    ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                    ->whereNull('deleted_at')
                    ->exists();

                if ($existsInDb) {
                    throw new \Exception("Baris {$rowNum}: Kode Pajak '{$rawKode}' sudah ada di database.");
                }

                $insertData[] = [
                    'company_id'  => $activeCompanyId,
                    'kode_pajak'  => $rawKode,
                    'nama_pajak'  => $rawNama,
                    'tarif'       => (float) $rawTarif,
                    'keterangan'  => $rawKeterangan ?: null,
                    'status'      => $rawStatus,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('master_pajaks')->insert($chunk);
                }
            }

            DB::commit();
            $total = count($insertData);
            return response()->json(['message' => "{$total} data pajak berhasil diimpor!"]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import pajak: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import pajak: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
