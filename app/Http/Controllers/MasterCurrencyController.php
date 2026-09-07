<?php

namespace App\Http\Controllers;

use App\Models\MasterCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterCurrencyController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-currency');
        $currencies = MasterCurrency::forActiveCompany()->latest()->get();
        return view('master_keuangan.mata_uang.viewcurrency', compact('currencies'));
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-currency');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'kode_currency' => [
                'required', 'string', 'max:5',
                Rule::unique('master_currencies', 'kode_currency')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'nama_currency'  => 'required|string|max:20',
            'simbol'         => 'required|string|max:5',
            'status'         => 'required|in:Active,Inactive',
        ], [
            'kode_currency.max' => 'Kode ISO mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
            'nama_currency.max' => 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.',
            'simbol.max'        => 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
            'kode_currency.unique' => 'Kode ISO mata uang ini sudah ada.',
        ]);

        try {
            MasterCurrency::create([
                'company_id'    => $activeCompanyId,
                'kode_currency' => strtoupper(trim($request->kode_currency)),
                'nama_currency' => trim($request->nama_currency),
                'simbol'        => trim($request->simbol),
                'status'        => $request->status,
            ]);
            return response()->json(['message' => 'Mata uang berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Currency: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data.'], 500);
        }
    }

    public function edit(MasterCurrency $currency)
    {
        $this->authorize('edit-currency');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $currency->company_id && $currency->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data mata uang milik PT lain.'], 403);
        }

        return response()->json(['currency' => $currency]);
    }

    public function update(Request $request, MasterCurrency $currency)
    {
        $this->authorize('edit-currency');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $currency->company_id && $currency->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit mata uang milik PT lain.'], 403);
        }

        // Kode ISO mata uang tidak boleh diubah saat edit
        $request->merge(['kode_currency' => $currency->kode_currency]);

        $request->validate([
            'nama_currency' => 'required|string|max:20',
            'simbol'        => 'required|string|max:5',
            'status'        => 'required|in:Active,Inactive',
        ], [
            'nama_currency.max' => 'Nama mata uang tidak boleh melebihi 20 karakter yang telah ditentukan.',
            'simbol.max'        => 'Simbol mata uang tidak boleh melebihi 5 karakter yang telah ditentukan.',
        ]);

        try {
            $currency->update([
                'nama_currency' => trim($request->nama_currency),
                'simbol'        => trim($request->simbol),
                'status'        => $request->status,
            ]);
            return response()->json(['message' => 'Mata uang berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Currency: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data.'], 500);
        }
    }

    public function destroy(MasterCurrency $currency)
    {
        $this->authorize('hapus-currency');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $currency->company_id && $currency->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus mata uang milik PT lain.'], 403);
        }

        try {
            $currency->delete();
            return response()->json(['message' => 'Mata uang berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete Currency: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data.'], 500);
        }
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $this->authorize('lihat-currency');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        $currencies = DB::table('master_currencies')
            ->select('kode_currency', 'nama_currency', 'simbol', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode ISO</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Mata Uang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Simbol</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($currencies as $c) {
            $statusStyle = $c->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $c->kode_currency . '</style><center>',
                '<style border="thin">' . $c->nama_currency . '</style><left>',
                '<style border="thin">' . $c->simbol . '</style><center>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_mata_uang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── TEMPLATE IMPORT ─────────────────────────────────────────────────────
    public function downloadTemplate()
    {
        $this->authorize('lihat-currency');

        $rows = [
            ['Kode ISO', 'Nama Mata Uang', 'Simbol', 'Status'],
            ['IDR', 'Rupiah', 'Rp', 'Active'],
            ['USD', 'US Dollar', '$', 'Active'],
            ['EUR', 'Euro', '€', 'Active'],
            ['SGD', 'Singapore Dollar', 'S$', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_mata_uang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT ──────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-currency');

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

                $rawKode   = isset($row[0]) ? strtoupper(trim($row[0])) : '';
                $rawNama   = isset($row[1]) ? trim($row[1]) : '';
                $rawSimbol = isset($row[2]) ? trim($row[2]) : '';
                $rawStatus = isset($row[3]) ? trim($row[3]) : 'Active';
                $rowNum    = $i + 1;

                // ── Validasi baris ──────────────────────────────────────────
                if (empty($rawKode)) {
                    throw new \Exception("Baris {$rowNum}: Kode ISO Mata Uang wajib diisi.");
                }
                if (strlen($rawKode) > 5) {
                    throw new \Exception("Baris {$rowNum}: Kode ISO Mata Uang tidak boleh melebihi 5 karakter yang telah ditentukan.");
                }
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Mata Uang wajib diisi.");
                }
                if (strlen($rawNama) > 20) {
                    throw new \Exception("Baris {$rowNum}: Nama Mata Uang tidak boleh melebihi 20 karakter yang telah ditentukan.");
                }
                if (empty($rawSimbol)) {
                    throw new \Exception("Baris {$rowNum}: Simbol Mata Uang wajib diisi.");
                }
                if (strlen($rawSimbol) > 5) {
                    throw new \Exception("Baris {$rowNum}: Simbol Mata Uang tidak boleh melebihi 5 karakter yang telah ditentukan.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // ── Cek duplikat dalam file ──────────────────────────────────
                $kodeLower = strtolower($rawKode);
                if (isset($processedCodes[$kodeLower])) {
                    throw new \Exception("Baris {$rowNum}: Kode ISO '{$rawKode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                // ── Cek duplikat di database (scoped to active company) ──────
                $existsInDb = DB::table('master_currencies')
                    ->where('kode_currency', $rawKode)
                    ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                    ->whereNull('deleted_at')
                    ->exists();

                if ($existsInDb) {
                    throw new \Exception("Baris {$rowNum}: Kode ISO '{$rawKode}' sudah ada di database.");
                }

                $insertData[] = [
                    'company_id'    => $activeCompanyId,
                    'kode_currency' => $rawKode,
                    'nama_currency' => $rawNama,
                    'simbol'        => $rawSimbol,
                    'status'        => $rawStatus,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('master_currencies')->insert($chunk);
                }
            }

            DB::commit();
            $total = count($insertData);
            return response()->json(['message' => "{$total} data mata uang berhasil diimpor!"]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import mata uang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import mata uang: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
