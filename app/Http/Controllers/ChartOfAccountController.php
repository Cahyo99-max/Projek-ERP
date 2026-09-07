<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\MasterTipeAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $typeFilter = $request->query('type');
        
        $query = ChartOfAccount::forActiveCompany()
            ->with('masterTipeAkun', 'parent', 'children')
            ->orderBy('code');

        if ($typeFilter) {
            $query->where(function($q) use ($typeFilter) {
                $q->where('type', $typeFilter)
                  ->orWhere('master_tipe_akun_id', $typeFilter);
            });
        }

        $accounts = $query->get();
        $parentAccounts = ChartOfAccount::forActiveCompany()->where('is_header', true)->orderBy('code')->get();
        $accountTypes = MasterTipeAkun::active()->orderBy('kode_tipe')->get();

        return view('master_keuangan.chart_of_accounts.viewchartofaccount', compact('accounts', 'parentAccounts', 'accountTypes', 'typeFilter'));
    }

    public function store(Request $request)
    {
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'code' => [
                'required', 'string', 'max:10',
                Rule::unique('chart_of_accounts', 'code')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'name' => 'required|string|max:40',
            'master_tipe_akun_id' => 'nullable|exists:master_tipe_akuns,id',
            'type' => 'nullable|string',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'level' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:30',
        ], [
            'code.max' => 'Kode Akun tidak boleh lebih dari 10 karakter!',
            'name.max' => 'Nama Akun tidak boleh lebih dari 40 karakter!',
            'description.max' => 'Deskripsi tidak boleh lebih dari 30 karakter!',
            'code.unique' => 'Kode Akun sudah digunakan untuk PT ini!',
        ]);

        try {
            $masterType = $request->master_tipe_akun_id ? MasterTipeAkun::find($request->master_tipe_akun_id) : null;
            $typeEnum = $request->type ?: ($masterType ? strtolower($masterType->kode_tipe) : 'asset');

            ChartOfAccount::create([
                'company_id' => $activeCompanyId,
                'code' => trim($request->code),
                'name' => trim($request->name),
                'master_tipe_akun_id' => $request->master_tipe_akun_id ?: null,
                'type' => $typeEnum,
                'normal_balance' => $request->normal_balance,
                'parent_id' => $request->parent_id ?: null,
                'is_header' => $request->has('is_header') ? (bool)$request->is_header : false,
                'level' => $request->level ?: 1,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
                'description' => $request->description ? trim($request->description) : null,
            ]);

            return response()->json(['message' => 'Akun COA berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store COA: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Akun COA.'], 500);
        }
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $chartOfAccount->company_id && $chartOfAccount->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Akun COA milik PT lain.'], 403);
        }

        return response()->json(['account' => $chartOfAccount]);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $chartOfAccount->company_id && $chartOfAccount->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit Akun COA milik PT lain.'], 403);
        }

        $request->validate([
            'code' => [
                'required', 'string', 'max:10',
                Rule::unique('chart_of_accounts', 'code')->ignore($chartOfAccount->id)
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'name' => 'required|string|max:40',
            'master_tipe_akun_id' => 'nullable|exists:master_tipe_akuns,id',
            'type' => 'nullable|string',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'level' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:30',
        ], [
            'code.max' => 'Kode Akun tidak boleh lebih dari 10 karakter!',
            'name.max' => 'Nama Akun tidak boleh lebih dari 40 karakter!',
            'description.max' => 'Deskripsi tidak boleh lebih dari 30 karakter!',
            'code.unique' => 'Kode Akun sudah digunakan untuk PT ini!',
        ]);

        try {
            $masterType = $request->master_tipe_akun_id ? MasterTipeAkun::find($request->master_tipe_akun_id) : null;
            $typeEnum = $request->type ?: ($masterType ? strtolower($masterType->kode_tipe) : $chartOfAccount->type);

            $chartOfAccount->update([
                'code' => trim($request->code),
                'name' => trim($request->name),
                'master_tipe_akun_id' => $request->master_tipe_akun_id ?: null,
                'type' => $typeEnum,
                'normal_balance' => $request->normal_balance,
                'parent_id' => $request->parent_id ?: null,
                'is_header' => $request->has('is_header') ? (bool)$request->is_header : false,
                'level' => $request->level ?: 1,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
                'description' => $request->description ? trim($request->description) : null,
            ]);

            return response()->json(['message' => 'Akun COA berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update COA: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Akun COA.'], 500);
        }
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $chartOfAccount->company_id && $chartOfAccount->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus Akun COA milik PT lain.'], 403);
        }

        try {
            if ($chartOfAccount->children()->count() > 0) {
                return response()->json(['message' => 'Tidak dapat menghapus akun yang memiliki anak akun.'], 400);
            }

            $chartOfAccount->delete();
            return response()->json(['message' => 'Akun COA berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete COA: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus Akun COA.'], 500);
        }
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $activeCompanyId = session('active_company_id');
        set_time_limit(0);

        $accounts = DB::table('chart_of_accounts as c')
            ->leftJoin('master_tipe_akuns as t', 'c.master_tipe_akun_id', '=', 't.id')
            ->leftJoin('chart_of_accounts as p', 'c.parent_id', '=', 'p.id')
            ->select(
                'c.code', 'c.name', 't.kode_tipe', 't.nama_tipe',
                'c.normal_balance', 'p.code as parent_code', 'p.name as parent_name',
                'c.is_header', 'c.is_active', 'c.description'
            )
            ->whereNull('c.deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('c.company_id', $activeCompanyId))
            ->orderBy('c.code', 'asc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Akun</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Akun</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Tipe Akun</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Saldo Normal</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Akun Induk (Parent)</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kategori</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
            ]
        ];

        foreach ($accounts as $acc) {
            $statusStyle = $acc->is_active
                ? '<style border="thin" color="#10b981"><b>Aktif</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Nonaktif</b></style><center>';

            $kategori = $acc->is_header ? 'Header (Induk)' : 'Detail';
            $parentInfo = $acc->parent_code ? ($acc->parent_code . ' - ' . $acc->parent_name) : '-';
            $tipeInfo = $acc->kode_tipe ? ($acc->kode_tipe . ' - ' . $acc->nama_tipe) : '-';

            $rows[] = [
                '<style border="thin">' . $acc->code . '</style><center>',
                '<style border="thin">' . $acc->name . '</style><left>',
                '<style border="thin">' . $tipeInfo . '</style><left>',
                '<style border="thin">' . strtoupper($acc->normal_balance ?? 'DEBIT') . '</style><center>',
                '<style border="thin">' . $parentInfo . '</style><left>',
                '<style border="thin">' . $kategori . '</style><center>',
                $statusStyle,
                '<style border="thin">' . ($acc->description ?? '') . '</style><left>',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_chart_of_accounts_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── TEMPLATE IMPORT ─────────────────────────────────────────────────────
    public function downloadTemplate()
    {
        $rows = [
            ['Kode Akun', 'Nama Akun', 'Tipe Akun', 'Saldo Normal', 'Akun Induk (Parent)', 'Kategori', 'Status', 'Deskripsi'],
            ['1-1000', 'ASET / AKTIVA', 'AST - Aset / Aktiva', 'DEBIT', '', 'Header (Induk)', 'Aktif', 'Induk Utama Aktiva'],
            ['1-1100', 'Kas & Bank', 'AST - Aset / Aktiva', 'DEBIT', '1-1000', 'Header (Induk)', 'Aktif', 'Sub-induk Kas & Bank'],
            ['1-1101', 'Kas Kecil / Kasir', 'AST - Aset / Aktiva', 'DEBIT', '1-1100', 'Detail', 'Aktif', 'Kas Operasional Harian'],
            ['1-1102', 'Bank BCA', 'AST - Aset / Aktiva', 'DEBIT', '1-1100', 'Detail', 'Aktif', 'Rekening Operasional BCA'],
            ['2-1000', 'KEWAJIBAN / UTANG', 'LIAB - Kewajiban / Utang', 'CREDIT', '', 'Header (Induk)', 'Aktif', 'Induk Kewajiban'],
            ['2-1100', 'Hutang Usaha', 'LIAB - Kewajiban / Utang', 'CREDIT', '2-1000', 'Detail', 'Aktif', 'Hutang Dagang Supplier'],
            ['3-1000', 'EKUITAS / MODAL', 'EQT - Ekuitas / Modal', 'CREDIT', '', 'Header (Induk)', 'Aktif', 'Induk Ekuitas'],
            ['4-1000', 'PENDAPATAN / PENJUALAN', 'REV - Pendapatan / Penjualan', 'CREDIT', '', 'Header (Induk)', 'Aktif', 'Induk Pendapatan'],
            ['5-1000', 'HARGA POKOK PENJUALAN', 'COGS - Harga Pokok Penjualan (HPP)', 'DEBIT', '', 'Header (Induk)', 'Aktif', 'Induk HPP'],
            ['6-1000', 'BEBAN OPERASIONAL', 'EXP - Beban Operasional', 'DEBIT', '', 'Header (Induk)', 'Aktif', 'Induk Beban Operasional'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_chart_of_accounts.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT ──────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
    {
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

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            $tipeAkuns = MasterTipeAkun::all();
            $parsedRows = [];
            $processedCodes = [];

            // Helper to strip HTML / formatting
            $cleanCell = function ($val) {
                if ($val === null) return '';
                $str = is_string($val) ? strip_tags($val) : (string)$val;
                return trim($str);
            };

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawCode        = $cleanCell($row[0] ?? '');
                $rawName        = $cleanCell($row[1] ?? '');
                $rawTipe        = $cleanCell($row[2] ?? '');
                $rawNormal      = $cleanCell($row[3] ?? '');
                $rawParent      = $cleanCell($row[4] ?? '');
                $rawIsHeader    = $cleanCell($row[5] ?? '');
                $rawIsActive    = $cleanCell($row[6] ?? '');
                $rawDescription = $cleanCell($row[7] ?? '');
                $rowNum         = $i + 1;

                if (empty($rawCode)) {
                    throw new \Exception("Baris {$rowNum}: Kode Akun wajib diisi.");
                }
                if (strlen($rawCode) > 20) {
                    throw new \Exception("Baris {$rowNum}: Kode Akun tidak boleh melebihi 20 karakter.");
                }
                if (empty($rawName)) {
                    throw new \Exception("Baris {$rowNum}: Nama Akun wajib diisi.");
                }
                if (strlen($rawName) > 100) {
                    throw new \Exception("Baris {$rowNum}: Nama Akun tidak boleh melebihi 100 karakter.");
                }

                $codeLower = strtolower($rawCode);
                if (isset($processedCodes[$codeLower])) {
                    throw new \Exception("Baris {$rowNum}: Kode Akun '{$rawCode}' duplikat di dalam file import.");
                }
                $processedCodes[$codeLower] = true;

                // 1. Resolve Master Tipe Akun & Type Enum
                $masterTipeId = null;
                $typeEnum     = 'asset';

                if (!empty($rawTipe)) {
                    $cleanTipe = trim(explode(' - ', $rawTipe)[0]);
                    $matchedTipe = $tipeAkuns->first(function($t) use ($cleanTipe, $rawTipe) {
                        return strcasecmp($t->kode_tipe, $cleanTipe) === 0 
                            || strcasecmp($t->nama_tipe, $rawTipe) === 0
                            || strcasecmp($t->kode_tipe, $rawTipe) === 0;
                    });

                    if ($matchedTipe) {
                        $masterTipeId = $matchedTipe->id;
                        $typeEnum     = strtolower($matchedTipe->kode_tipe);
                    } else {
                        // Fallback common mappings
                        $tipeLower = strtolower($rawTipe);
                        if (str_contains($tipeLower, 'asset') || str_contains($tipeLower, 'aktiva') || str_contains($tipeLower, 'aset') || $cleanTipe === 'AST') {
                            $typeEnum = 'asset';
                        } elseif (str_contains($tipeLower, 'liab') || str_contains($tipeLower, 'kewajiban') || str_contains($tipeLower, 'hutang') || str_contains($tipeLower, 'utang') || $cleanTipe === 'LIAB') {
                            $typeEnum = 'liability';
                        } elseif (str_contains($tipeLower, 'eq') || str_contains($tipeLower, 'modal') || str_contains($tipeLower, 'ekuitas') || $cleanTipe === 'EQT') {
                            $typeEnum = 'equity';
                        } elseif (str_contains($tipeLower, 'rev') || str_contains($tipeLower, 'pendapatan') || str_contains($tipeLower, 'penjualan') || $cleanTipe === 'REV') {
                            $typeEnum = 'revenue';
                        } elseif (str_contains($tipeLower, 'cogs') || str_contains($tipeLower, 'hpp') || str_contains($tipeLower, 'pokok') || $cleanTipe === 'COGS') {
                            $typeEnum = 'cogs';
                        } elseif (str_contains($tipeLower, 'exp') || str_contains($tipeLower, 'beban') || str_contains($tipeLower, 'biaya') || $cleanTipe === 'EXP') {
                            $typeEnum = 'expense';
                        } elseif (str_contains($tipeLower, 'orev') || str_contains($tipeLower, 'pendapatan lain') || $cleanTipe === 'OREV') {
                            $typeEnum = 'other_revenue';
                        } elseif (str_contains($tipeLower, 'oexp') || str_contains($tipeLower, 'beban lain') || $cleanTipe === 'OEXP') {
                            $typeEnum = 'other_expense';
                        }
                    }
                }

                // 2. Resolve Saldo Normal
                $strNormal = strtolower($rawNormal);
                $normalBalance = (str_contains($strNormal, 'kredit') || str_contains($strNormal, 'credit') || $strNormal === 'cr') ? 'credit' : 'debit';

                // 3. Resolve Header / Detail Category
                $strHeader = strtolower($rawIsHeader);
                $isHeader  = in_array($strHeader, ['1', 'true', 'yes', 'y', 'header', 'header (induk)', 'induk', 'ya'], true);

                // 4. Resolve Active Status
                $strActive = strtolower($rawIsActive);
                $isActive  = !in_array($strActive, ['0', 'false', 'no', 'n', 'nonaktif', 'non-aktif', 'tidak aktif', 'tidak'], true);

                // 5. Resolve Parent Code
                $parentCode = null;
                if (!empty($rawParent) && $rawParent !== '-') {
                    $parentCode = trim(explode(' - ', $rawParent)[0]);
                }

                $parsedRows[] = [
                    'code'                => $rawCode,
                    'name'                => $rawName,
                    'master_tipe_akun_id' => $masterTipeId,
                    'type'                => $typeEnum,
                    'normal_balance'      => $normalBalance,
                    'parent_code'         => $parentCode,
                    'is_header'           => $isHeader,
                    'is_active'           => $isActive,
                    'description'         => !empty($rawDescription) ? substr($rawDescription, 0, 150) : null,
                ];
            }

            // ── PASS 1: UPSERT / CREATE ALL ACCOUNTS FOR THIS COMPANY ───────────
            $accountMap = [];
            foreach ($parsedRows as $p) {
                $acc = ChartOfAccount::updateOrCreate(
                    [
                        'company_id' => $activeCompanyId,
                        'code'       => $p['code'],
                    ],
                    [
                        'name'                => $p['name'],
                        'master_tipe_akun_id' => $p['master_tipe_akun_id'],
                        'type'                => $p['type'],
                        'normal_balance'      => $p['normal_balance'],
                        'is_header'           => $p['is_header'],
                        'is_active'           => $p['is_active'],
                        'description'         => $p['description'],
                    ]
                );
                $accountMap[$p['code']] = $acc;
            }

            // Also load any existing accounts in DB for this company
            $dbAccounts = ChartOfAccount::whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->get()
                ->keyBy('code');

            foreach ($dbAccounts as $code => $acc) {
                if (!isset($accountMap[$code])) {
                    $accountMap[$code] = $acc;
                }
            }

            // ── PASS 2: RESOLVE PARENT_ID & CALCULATE HIERARCHY LEVELS ───────────
            foreach ($parsedRows as $p) {
                $acc = $accountMap[$p['code']] ?? null;
                if (!$acc) continue;

                $parentId = null;
                $level    = $p['is_header'] ? 1 : 2;

                if (!empty($p['parent_code']) && isset($accountMap[$p['parent_code']])) {
                    $parentAcc = $accountMap[$p['parent_code']];
                    $parentId  = $parentAcc->id;
                    $level     = min(5, ($parentAcc->level ?: 1) + 1);
                }

                $acc->parent_id = $parentId;
                $acc->level     = $level;
                $acc->save();
            }

            DB::commit();
            $total = count($parsedRows);
            return response()->json([
                'message' => "{$total} data Akun COA berhasil diimpor dengan hierarki akun yang sempurna!",
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import COA: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import COA: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
