<?php

namespace App\Http\Controllers;

use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class PaymentTermController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-payment-term');
        $terms = PaymentTerm::forActiveCompany()->latest()->get();
        return view('master_keuangan.tenor_pembayaran.viewpaymentterm', compact('terms'));
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-payment-term');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'nama_term' => [
                'required', 'string', 'max:100',
                Rule::unique('payment_terms', 'nama_term')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'jumlah_hari' => 'required|numeric|digits_between:1,10',
            'status'      => 'required|in:Active,Inactive',
            'keterangan'  => 'nullable|string|max:255',
        ], [
            'jumlah_hari.max' => 'Jumlah hari tidak boleh melebihi 10 digit yang telah ditentukan.',
        ]);

        try {
            $data = $request->all();
            $data['company_id'] = $activeCompanyId;
            $data['status']     = $data['status'] ?? 'Active';
            $data['created_by'] = auth()->id();

            $term = PaymentTerm::create($data);
            return response()->json([
                'message' => 'Tenor pembayaran berhasil ditambahkan!',
                'term'    => $term
            ]);
        } catch (\Exception $e) {
            Log::error('Error store PaymentTerm: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data.'], 500);
        }
    }

    public function edit(PaymentTerm $paymentTerm)
    {
        $this->authorize('edit-payment-term');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentTerm->company_id && $paymentTerm->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data tenor pembayaran milik PT lain.'], 403);
        }

        return response()->json(['term' => $paymentTerm]);
    }

    public function update(Request $request, PaymentTerm $paymentTerm)
    {
        $this->authorize('edit-payment-term');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentTerm->company_id && $paymentTerm->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit tenor pembayaran milik PT lain.'], 403);
        }

        $request->validate([
            'nama_term' => [
                'required', 'string', 'max:100',
                Rule::unique('payment_terms', 'nama_term')->ignore($paymentTerm->id)
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'jumlah_hari' => 'required|integer|min:0',
            'status'      => 'required|in:Active,Inactive',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        try {
            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $paymentTerm->update($data);
            return response()->json(['message' => 'Tenor pembayaran berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update PaymentTerm: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data.'], 500);
        }
    }

    public function destroy(PaymentTerm $paymentTerm)
    {
        $this->authorize('hapus-payment-term');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $paymentTerm->company_id && $paymentTerm->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus tenor pembayaran milik PT lain.'], 403);
        }

        try {
            $paymentTerm->deleted_by = auth()->id();
            $paymentTerm->save();
            $paymentTerm->delete();
            return response()->json(['message' => 'Tenor pembayaran berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete PaymentTerm: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data.'], 500);
        }
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $this->authorize('lihat-payment-term');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        $terms = DB::table('payment_terms')
            ->select('nama_term', 'jumlah_hari', 'keterangan', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Tenor</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Jumlah Hari</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Keterangan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($terms as $t) {
            $statusStyle = $t->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $t->nama_term . '</style><center>',
                '<style border="thin">' . $t->jumlah_hari . '</style><center>',
                '<style border="thin">' . ($t->keterangan ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_tenor_pembayaran_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── TEMPLATE IMPORT ─────────────────────────────────────────────────────
    public function downloadTemplate()
    {
        $this->authorize('lihat-payment-term');

        $rows = [
            ['Nama Tenor', 'Jumlah Hari', 'Keterangan', 'Status'],
            ['COD', '0', 'Cash on Delivery (Tunai)', 'Active'],
            ['Net 14', '14', 'Jatuh tempo 14 hari', 'Active'],
            ['Net 30', '30', 'Jatuh tempo 30 hari', 'Active'],
            ['Net 60', '60', 'Jatuh tempo 60 hari', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_tenor_pembayaran.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT ──────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-payment-term');

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

        $processedNames = [];
        $insertData     = [];
        $now            = now()->toDateTimeString();

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawNama       = isset($row[0]) ? trim($row[0]) : '';
                $rawHari       = isset($row[1]) ? trim($row[1]) : '0';
                $rawKeterangan = isset($row[2]) ? trim($row[2]) : '';
                $rawStatus     = isset($row[3]) ? trim($row[3]) : 'Active';
                $rowNum        = $i + 1;

                // ── Validasi baris ──────────────────────────────────────────
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Tenor wajib diisi.");
                }
                if (strlen($rawNama) > 100) {
                    throw new \Exception("Baris {$rowNum}: Nama Tenor maksimal 100 karakter.");
                }
                if (!is_numeric($rawHari) || (int)$rawHari < 0) {
                    throw new \Exception("Baris {$rowNum}: Jumlah Hari harus berupa angka non-negatif.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // ── Cek duplikat dalam file ──────────────────────────────────
                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Tenor '{$rawNama}' duplikat di dalam file.");
                }
                $processedNames[$namaLower] = true;

                // ── Cek duplikat di database (scoped to active company) ──────
                $existsInDb = DB::table('payment_terms')
                    ->where('nama_term', $rawNama)
                    ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                    ->whereNull('deleted_at')
                    ->exists();

                if ($existsInDb) {
                    throw new \Exception("Baris {$rowNum}: Nama Tenor '{$rawNama}' sudah ada di database.");
                }

                $insertData[] = [
                    'company_id'  => $activeCompanyId,
                    'nama_term'   => $rawNama,
                    'jumlah_hari' => (int) $rawHari,
                    'keterangan'  => $rawKeterangan ?: null,
                    'status'      => $rawStatus,
                    'created_by'  => auth()->id(),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('payment_terms')->insert($chunk);
                }
            }

            DB::commit();
            $total = count($insertData);
            return response()->json(['message' => "{$total} data tenor pembayaran berhasil diimpor!"]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import tenor: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import tenor: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
