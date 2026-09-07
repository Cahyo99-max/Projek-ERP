<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-payment-method');
        $methods = PaymentMethod::forActiveCompany()->latest()->get();
        return view('master_keuangan.metode_pembayaran.viewpaymentmethod', compact('methods'));
    }

    // ─── GENERATE AUTOMATIC KODE ──────────────────────────────────────────────
    public function generateKode()
    {
        $this->authorize('tambah-payment-method');
        $activeCompanyId = session('active_company_id');

        $lastRecord = PaymentMethod::where('company_id', $activeCompanyId)
            ->where('kode_metode', 'LIKE', 'PM%')
            ->orderByRaw('CAST(SUBSTRING(kode_metode, 3) AS UNSIGNED) DESC')
            ->first();

        if ($lastRecord && preg_match('/^PM(\d+)$/', $lastRecord->kode_metode, $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            $nextNum = 1;
        }

        $kode = 'PM' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        return response()->json(['kode' => $kode]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-payment-method');
        $activeCompanyId = session('active_company_id');

        // Jika kode_metode dikosongkan, generate otomatis
        if (empty($request->kode_metode)) {
            $genResponse = $this->generateKode();
            $request->merge(['kode_metode' => $genResponse->getData()->kode]);
        }

        $request->validate([
            'kode_metode' => [
                'required', 'string', 'max:5',
                Rule::unique('payment_methods', 'kode_metode')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'nama_metode' => 'required|string|max:35',
            'status'      => 'required|in:Active,Inactive',
            'keterangan'  => 'nullable|string|max:30',
        ], [
            'kode_metode.max' => 'Kode metode tidak boleh melebihi 5 karakter yang telah ditentukan.',
            'nama_metode.max' => 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.',
            'keterangan.max'  => 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.',
            'kode_metode.unique' => 'Kode metode ini sudah digunakan.',
        ]);

        try {
            $data = $request->all();
            $data['company_id']  = $activeCompanyId;
            $data['kode_metode'] = strtoupper(trim($data['kode_metode']));
            $data['status']      = $data['status'] ?? 'Active';
            $data['created_by']  = auth()->id();

            $method = PaymentMethod::create($data);
            return response()->json([
                'message' => 'Metode pembayaran berhasil ditambahkan!',
                'method'  => $method
            ]);
        } catch (\Exception $e) {
            Log::error('Error store PaymentMethod: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data.'], 500);
        }
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $this->authorize('edit-payment-method');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentMethod->company_id && $paymentMethod->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data metode pembayaran milik PT lain.'], 403);
        }

        return response()->json(['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('edit-payment-method');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentMethod->company_id && $paymentMethod->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit metode pembayaran milik PT lain.'], 403);
        }

        // Kode metode tidak boleh diubah saat edit
        $request->merge(['kode_metode' => $paymentMethod->kode_metode]);

        $request->validate([
            'nama_metode' => 'required|string|max:35',
            'status'      => 'required|in:Active,Inactive',
            'keterangan'  => 'nullable|string|max:30',
        ], [
            'nama_metode.max' => 'Nama metode tidak boleh melebihi 35 karakter yang telah ditentukan.',
            'keterangan.max'  => 'Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.',
        ]);

        try {
            $data = $request->only(['nama_metode', 'status', 'keterangan']);
            $data['updated_by'] = auth()->id();

            $paymentMethod->update($data);
            return response()->json(['message' => 'Metode pembayaran berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update PaymentMethod: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data.'], 500);
        }
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('hapus-payment-method');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentMethod->company_id && $paymentMethod->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus metode pembayaran milik PT lain.'], 403);
        }

        try {
            $paymentMethod->deleted_by = auth()->id();
            $paymentMethod->save();
            $paymentMethod->delete();
            return response()->json(['message' => 'Metode pembayaran berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete PaymentMethod: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data.'], 500);
        }
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $this->authorize('lihat-payment-method');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        $methods = DB::table('payment_methods')
            ->select('kode_metode', 'nama_metode', 'keterangan', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Metode</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Metode</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Keterangan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($methods as $m) {
            $statusStyle = $m->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $m->kode_metode . '</style><center>',
                '<style border="thin">' . $m->nama_metode . '</style><left>',
                '<style border="thin">' . ($m->keterangan ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_metode_pembayaran_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── TEMPLATE IMPORT ─────────────────────────────────────────────────────
    public function downloadTemplate()
    {
        $this->authorize('lihat-payment-method');

        $rows = [
            ['Kode Metode', 'Nama Metode', 'Keterangan', 'Status'],
            ['PM001', 'Tunai / Cash', 'Pembayaran tunai langsung', 'Active'],
            ['PM002', 'Transfer BCA', 'Transfer Bank BCA', 'Active'],
            ['PM003', 'Transfer Mandiri', 'Transfer Bank Mandiri', 'Active'],
            ['PM004', 'QRIS Pembayaran', 'Scan QRIS All Payment', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_metode_pembayaran.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT ──────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-payment-method');

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

                $rawKode       = isset($row[0]) ? strtoupper(trim($row[0])) : '';
                $rawNama       = isset($row[1]) ? trim($row[1]) : '';
                $rawKeterangan = isset($row[2]) ? trim($row[2]) : '';
                $rawStatus     = isset($row[3]) ? trim($row[3]) : 'Active';
                $rowNum        = $i + 1;

                // ── Validasi baris ──────────────────────────────────────────
                if (empty($rawKode)) {
                    throw new \Exception("Baris {$rowNum}: Kode Metode wajib diisi.");
                }
                if (strlen($rawKode) > 5) {
                    throw new \Exception("Baris {$rowNum}: Kode Metode tidak boleh melebihi 5 karakter yang telah ditentukan.");
                }
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Metode wajib diisi.");
                }
                if (strlen($rawNama) > 35) {
                    throw new \Exception("Baris {$rowNum}: Nama Metode tidak boleh melebihi 35 karakter yang telah ditentukan.");
                }
                if (!empty($rawKeterangan) && strlen($rawKeterangan) > 30) {
                    throw new \Exception("Baris {$rowNum}: Keterangan tidak boleh melebihi 30 karakter yang telah ditentukan.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // ── Cek duplikat dalam file ──────────────────────────────────
                $kodeLower = strtolower($rawKode);
                if (isset($processedCodes[$kodeLower])) {
                    throw new \Exception("Baris {$rowNum}: Kode Metode '{$rawKode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                // ── Cek duplikat di database (scoped to active company) ──────
                $existsInDb = DB::table('payment_methods')
                    ->where('kode_metode', $rawKode)
                    ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                    ->whereNull('deleted_at')
                    ->exists();

                if ($existsInDb) {
                    throw new \Exception("Baris {$rowNum}: Kode Metode '{$rawKode}' sudah ada di database.");
                }

                $insertData[] = [
                    'company_id'  => $activeCompanyId,
                    'kode_metode' => $rawKode,
                    'nama_metode' => $rawNama,
                    'keterangan'  => $rawKeterangan ?: null,
                    'status'      => $rawStatus,
                    'created_by'  => auth()->id(),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('payment_methods')->insert($chunk);
                }
            }

            DB::commit();
            $total = count($insertData);
            return response()->json(['message' => "{$total} data metode pembayaran berhasil diimpor!"]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import metode: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import metode: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
