<?php

namespace App\Http\Controllers;

use App\Models\MasterTipeAkun;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterTipeAkunController extends Controller
{
    /**
     * Generate Kode Tipe Akun Otomatis (Anti-Duplikat & Selalu Lompat ke Nomor Berikutnya)
     */
    public function generateCode(Request $request)
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        $namaTipe = trim($request->query('nama', ''));

        $code = $this->getNextUniqueTypeCode($activeCompanyId, $namaTipe);

        return response()->json(['code' => $code]);
    }

    /**
     * Helper untuk generate kode tipe akun yang unik & tidak pernah menduplikasi data yang dihapus
     */
    private function getNextUniqueTypeCode($activeCompanyId, $namaTipe = ''): string
    {
        $code = null;
        $namaTipe = trim($namaTipe);

        // 1. Jika nama tipe akun diinput, buat format berbasis singkatan nama
        if (!empty($namaTipe)) {
            $words = array_values(array_filter(preg_split('/[\s\/\-_]+/', $namaTipe)));
            $abbr = '';
            if (count($words) >= 2) {
                foreach ($words as $w) {
                    $abbr .= strtoupper(substr($w, 0, 1));
                }
            } else {
                $cleaned = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $namaTipe));
                $abbr = substr($cleaned, 0, 4);
            }

            if (!empty($abbr)) {
                // Cek apakah singkatan murni ($abbr) sudah pernah ada (termasuk yang di-soft-delete)
                $exactExists = MasterTipeAkun::withTrashed()
                    ->where('company_id', $activeCompanyId)
                    ->where('kode_tipe', $abbr)
                    ->exists();

                if (!$exactExists) {
                    $code = $abbr;
                } else {
                    // Jika sudah pernah ada (aktif atau pernah dihapus), cari suffix angka tertinggi dari prefix ini
                    $existingCodes = MasterTipeAkun::withTrashed()
                        ->where('company_id', $activeCompanyId)
                        ->where(function($q) use ($abbr) {
                            $q->where('kode_tipe', 'like', $abbr . '%')
                              ->orWhere('kode_tipe', 'like', $abbr . '-%');
                        })
                        ->pluck('kode_tipe');

                    $maxSuffix = 0;
                    foreach ($existingCodes as $c) {
                        $clean = str_replace([$abbr, '-'], '', $c);
                        if (is_numeric($clean)) {
                            $num = (int)$clean;
                            if ($num > $maxSuffix) {
                                $maxSuffix = $num;
                            }
                        }
                    }

                    // Selalu lompat ke nomor berikutnya setelah max yang pernah ada
                    $nextSuffix = $maxSuffix + 1;
                    do {
                        $candidate = $abbr . str_pad($nextSuffix, 2, '0', STR_PAD_LEFT);
                        $exists = MasterTipeAkun::withTrashed()
                            ->where('company_id', $activeCompanyId)
                            ->where('kode_tipe', $candidate)
                            ->exists();
                        if ($exists) {
                            $nextSuffix++;
                        }
                    } while ($exists);

                    $code = $candidate;
                }
            }
        }

        // 2. Jika nama kosong atau tidak menghasilkan kode, gunakan format sequential standar "TYP-xxx"
        if (empty($code)) {
            // Ambil semua kode tipe akun yang diawali "TYP-" termasuk yang di-soft-delete
            $typCodes = MasterTipeAkun::withTrashed()
                ->where('company_id', $activeCompanyId)
                ->where('kode_tipe', 'like', 'TYP-%')
                ->pluck('kode_tipe');

            $maxTypNumber = 0;
            foreach ($typCodes as $tc) {
                $suffix = substr($tc, 4); // Potong "TYP-"
                if (is_numeric($suffix)) {
                    $val = (int)$suffix;
                    if ($val > $maxTypNumber) {
                        $maxTypNumber = $val;
                    }
                }
            }

            // Selalu lompat ke nomor berikutnya setelah max yang pernah ada
            $nextNumber = $maxTypNumber + 1;
            do {
                $candidate = 'TYP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $exists = MasterTipeAkun::withTrashed()
                    ->where('company_id', $activeCompanyId)
                    ->where('kode_tipe', $candidate)
                    ->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            $code = $candidate;
        }

        return $code;
    }

    public function store(Request $request)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);

        // Jika kode_tipe kosong, auto-generate kode unik secara otomatis (selalu lompat nomor berikutnya)
        $kodeTipe = trim($request->kode_tipe ?? '');
        if (empty($kodeTipe)) {
            $kodeTipe = $this->getNextUniqueTypeCode($activeCompanyId, $request->nama_tipe ?? '');
            $request->merge(['kode_tipe' => $kodeTipe]);
        }

        $request->validate([
            'kode_tipe' => [
                'required',
                'string',
                'max:50',
                // Pastikan unik terhadap seluruh data termasuk yang di-soft delete agar tidak pernah terjadi bentrok
                Rule::unique('master_tipe_akuns', 'kode_tipe')->where(function ($q) use ($activeCompanyId) {
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                }),
            ],
            'nama_tipe'            => 'required|string|max:255',
            'kategori_laporan'     => 'required|in:neraca,laba_rugi',
            'saldo_normal_default' => 'required|in:debit,credit',
            'status'               => 'required|in:Active,Inactive',
            'keterangan'           => 'nullable|string|max:500',
        ], [
            'kode_tipe.unique' => 'Kode Tipe Akun sudah terdaftar atau pernah digunakan pada perusahaan (PT) ini. Silakan gunakan nomor berikutnya atau klik Otomatis.',
        ]);

        try {
            MasterTipeAkun::create([
                'company_id'           => $activeCompanyId,
                'kode_tipe'            => strtoupper(trim($request->kode_tipe)),
                'nama_tipe'            => trim($request->nama_tipe),
                'kategori_laporan'     => $request->kategori_laporan,
                'saldo_normal_default' => $request->saldo_normal_default,
                'status'               => $request->status,
                'keterangan'           => $request->keterangan ? trim($request->keterangan) : null,
            ]);

            return response()->json(['message' => 'Master Tipe Akun berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan Tipe Akun: ' . $e->getMessage()], 500);
        }
    }

    public function edit(MasterTipeAkun $masterTipeAkun)
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId && $masterTipeAkun->company_id && $masterTipeAkun->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Akun milik perusahaan (PT) lain.'], 403);
        }

        return response()->json(['tipe_akun' => $masterTipeAkun]);
    }

    public function update(Request $request, MasterTipeAkun $masterTipeAkun)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId && $masterTipeAkun->company_id && $masterTipeAkun->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Akun milik perusahaan (PT) lain.'], 403);
        }

        $request->validate([
            'kode_tipe' => [
                'required',
                'string',
                'max:50',
                Rule::unique('master_tipe_akuns', 'kode_tipe')->where(function ($q) use ($activeCompanyId) {
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    } else {
                        $q->whereNull('company_id');
                    }
                })->ignore($masterTipeAkun->id),
            ],
            'nama_tipe'            => 'required|string|max:255',
            'kategori_laporan'     => 'required|in:neraca,laba_rugi',
            'saldo_normal_default' => 'required|in:debit,credit',
            'status'               => 'required|in:Active,Inactive',
            'keterangan'           => 'nullable|string|max:500',
        ], [
            'kode_tipe.unique' => 'Kode Tipe Akun sudah terdaftar atau pernah digunakan pada perusahaan (PT) ini.',
        ]);

        try {
            $masterTipeAkun->update([
                'kode_tipe'            => strtoupper(trim($request->kode_tipe)),
                'nama_tipe'            => trim($request->nama_tipe),
                'kategori_laporan'     => $request->kategori_laporan,
                'saldo_normal_default' => $request->saldo_normal_default,
                'status'               => $request->status,
                'keterangan'           => $request->keterangan ? trim($request->keterangan) : null,
            ]);

            return response()->json(['message' => 'Master Tipe Akun berhasil diperbarui!']);
        } catch (\Exception $e) {
            Log::error('Error update Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Tipe Akun: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(MasterTipeAkun $masterTipeAkun)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId && $masterTipeAkun->company_id && $masterTipeAkun->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Data Tipe Akun milik perusahaan (PT) lain.'], 403);
        }

        try {
            if ($masterTipeAkun->accounts()->count() > 0) {
                return response()->json(['message' => 'Tipe Akun ini sedang digunakan oleh Akun COA pada perusahaan ini, tidak dapat dihapus.'], 422);
            }

            $masterTipeAkun->delete();
            return response()->json(['message' => 'Master Tipe Akun berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete Master Tipe Akun: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus Tipe Akun.'], 500);
        }
    }

    /**
     * Export Master Tipe Akun ke Excel (.xlsx) per PT
     */
    public function exportExcel()
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $companyName = session('active_company_name') ?? 'Perusahaan';
        $types = MasterTipeAkun::forActiveCompany()->orderBy('kode_tipe')->get();

        $rows = [
            ['KODE TIPE', 'NAMA TIPE AKUN', 'KATEGORI LAPORAN', 'SALDO NORMAL DEFAULT', 'STATUS', 'KETERANGAN']
        ];

        foreach ($types as $t) {
            $rows[] = [
                $t->kode_tipe,
                $t->nama_tipe,
                $t->kategori_laporan === 'laba_rugi' ? 'Laba Rugi' : 'Neraca',
                ucfirst($t->saldo_normal_default ?? 'debit'),
                $t->status,
                $t->keterangan ?? ''
            ];
        }

        $cleanCompanyName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $companyName);
        $filename = 'master_tipe_akun_' . strtolower($cleanCompanyName) . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download Template Import Excel Master Tipe Akun
     */
    public function downloadTemplate()
    {
        $this->authorize('lihat-pengaturan-akuntansi');

        $rows = [
            ['Kode Tipe (Opsional)', 'Nama Tipe Akun *', 'Kategori Laporan (Neraca / Laba Rugi) *', 'Saldo Normal Default (Debit / Kredit) *', 'Status (Active / Inactive) *', 'Keterangan'],
            ['AST-01', 'Aset Lancar Kas & Bank', 'Neraca', 'Debit', 'Active', 'Kategori aset lancar dan setara kas'],
            ['', 'Beban Operasional Lapangan', 'Laba Rugi', 'Debit', 'Active', 'Kosongkan kolom kode untuk auto-generate kode unik'],
            ['', 'Pendapatan Bunga Bank', 'Laba Rugi', 'Kredit', 'Active', 'Pendapatan non-operasional'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_master_tipe_akun.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Master Tipe Akun dari File Excel (.xlsx / .csv)
     */
    public function importExcel(Request $request)
    {
        $this->authorize('edit-pengaturan-akuntansi');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if (!$activeCompanyId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Perusahaan (PT) aktif tidak ditemukan pada sesi Anda. Silakan pilih PT terlebih dahulu.',
            ], 403);
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,csv,txt|max:10240',
        ], [
            'excel_file.required' => 'File Excel wajib diunggah.',
            'excel_file.file'     => 'Berkas yang diunggah tidak valid.',
            'excel_file.mimes'    => 'Format file harus berupa .xlsx atau .csv.',
            'excel_file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $request->file('excel_file');

        // Validasi Keamanan MIME Type Asli File
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];
        $detectedMime = $file->getMimeType();
        if (!in_array($detectedMime, $allowedMimes)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Keamanan: Tipe MIME file (' . htmlspecialchars($detectedMime) . ') tidak diizinkan. Harap unggah file spreadsheet resmi (.xlsx / .csv).',
            ], 422);
        }

        if (!$xlsx = SimpleXLSX::parse($file->path())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membaca isi file Excel: ' . SimpleXLSX::parseError(),
            ], 422);
        }

        $rows = $xlsx->rows();
        if (count($rows) < 2) {
            return response()->json([
                'status'  => 'error',
                'message' => 'File Excel kosong atau tidak memiliki baris data (hanya header).',
            ], 422);
        }

        // Batasi Maksimal Baris untuk Mencegah DoS / Memory Exhaustion
        if (count($rows) > 5001) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Jumlah baris melebihi batas maksimal 5.000 data per proses import demi kestabilan sistem.',
            ], 422);
        }

        // Deteksi apakah kolom pertama adalah kolom NO/Nomor (agar fleksibel jika ada file yang menyertakan NO)
        $hasNoCol = false;
        if (isset($rows[0][0])) {
            $firstHeader = strtolower(trim((string)$rows[0][0]));
            if (in_array($firstHeader, ['no', 'no.', 'nomor', '#'], true)) {
                $hasNoCol = true;
            }
        }
        $offset = $hasNoCol ? 1 : 0;

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) {
                    continue; // Lewati baris kosong
                }

                $rowNum = $i + 1; // Nomor baris di Excel (1-indexed)

                // Sanitasi Input (Cegah XSS & HTML Injection)
                $rawKode        = isset($row[0 + $offset]) ? trim(strip_tags((string)$row[0 + $offset])) : '';
                $namaTipe       = isset($row[1 + $offset]) ? trim(strip_tags((string)$row[1 + $offset])) : '';
                $rawKategori    = isset($row[2 + $offset]) ? strtolower(trim(strip_tags((string)$row[2 + $offset]))) : 'neraca';
                $rawSaldo       = isset($row[3 + $offset]) ? strtolower(trim(strip_tags((string)$row[3 + $offset]))) : 'debit';
                $rawStatus      = isset($row[4 + $offset]) ? strtolower(trim(strip_tags((string)$row[4 + $offset]))) : 'active';
                $keterangan     = isset($row[5 + $offset]) ? trim(strip_tags((string)$row[5 + $offset])) : null;

                // Cegah Formula / CSV Injection (Karakter =, +, -, @ di awal teks)
                $rawKode        = ltrim($rawKode, "=+-@\t\r");
                $namaTipe       = ltrim($namaTipe, "=+-@\t\r");
                $keterangan     = $keterangan !== null ? ltrim($keterangan, "=+-@\t\r") : null;

                // 1. Validasi Wajib: Nama Tipe Akun
                if (empty($namaTipe)) {
                    $errors[] = "Baris {$rowNum}: Kolom 'Nama Tipe Akun' kosong (wajib diisi).";
                    continue;
                }

                if (mb_strlen($namaTipe) > 255) {
                    $errors[] = "Baris {$rowNum}: 'Nama Tipe Akun' terlalu panjang (maksimal 255 karakter).";
                    continue;
                }

                // 2. Normalisasi Kategori Laporan
                $kategoriLaporan = 'neraca';
                if (str_contains($rawKategori, 'laba') || str_contains($rawKategori, 'rugi') || str_contains($rawKategori, 'profit') || str_contains($rawKategori, 'loss')) {
                    $kategoriLaporan = 'laba_rugi';
                }

                // 3. Normalisasi Saldo Normal Default
                $saldoNormal = 'debit';
                if (str_contains($rawSaldo, 'kredit') || str_contains($rawSaldo, 'credit') || $rawSaldo === 'cr') {
                    $saldoNormal = 'credit';
                }

                // 4. Normalisasi Status
                $status = 'Active';
                if (str_contains($rawStatus, 'inact') || str_contains($rawStatus, 'non') || $rawStatus === '0') {
                    $status = 'Inactive';
                }

                // 5. Normalisasi Kode Tipe Akun:
                // Jika user tidak mengisi kode atau kode sudah pernah terdaftar di PT ini (termasuk yang di-soft delete),
                // maka otomatis cari kode baru yang unik dan lompat ke nomor berikutnya!
                $kodeTipe = strtoupper($rawKode);
                $isCodeDuplicate = false;
                if (!empty($kodeTipe)) {
                    $isCodeDuplicate = MasterTipeAkun::withTrashed()
                        ->where('company_id', $activeCompanyId)
                        ->where('kode_tipe', $kodeTipe)
                        ->exists();
                }

                if (empty($kodeTipe) || $isCodeDuplicate) {
                    $kodeTipe = $this->getNextUniqueTypeCode($activeCompanyId, $namaTipe);
                }

                if (mb_strlen($keterangan) > 500) {
                    $keterangan = mb_substr($keterangan, 0, 500);
                }

                MasterTipeAkun::create([
                    'company_id'           => $activeCompanyId,
                    'kode_tipe'            => mb_substr($kodeTipe, 0, 50),
                    'nama_tipe'            => $namaTipe,
                    'kategori_laporan'     => $kategoriLaporan,
                    'saldo_normal_default' => $saldoNormal,
                    'status'               => $status,
                    'keterangan'           => $keterangan ?: null,
                ]);

                $importedCount++;
            }

            if ($importedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Proses import gagal: Tidak ada data yang berhasil disimpan.',
                    'errors'  => $errors,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'status'   => !empty($errors) ? 'warning' : 'success',
                'message'  => "Berhasil mengimpor {$importedCount} data Master Tipe Akun!" . (!empty($errors) ? " (Namun terdapat " . count($errors) . " baris yang dilewati karena tidak valid)" : ""),
                'imported' => $importedCount,
                'skipped'  => count($errors),
                'errors'   => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import Master Tipe Akun: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat memproses file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
