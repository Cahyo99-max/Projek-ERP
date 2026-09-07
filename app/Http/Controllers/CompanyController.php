<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class CompanyController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-perusahaan');
        $totalPerusahaan    = Company::count();
        $aktifPerusahaan    = Company::where('status', 'Active')->count();
        $nonaktifPerusahaan = Company::where('status', 'Inactive')->count();

        return view('master_gudang.perusahaan.viewcompanies', compact(
            'totalPerusahaan',
            'aktifPerusahaan',
            'nonaktifPerusahaan'
        ));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-perusahaan');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_perusahaan',
            2 => 'nama_perusahaan',
            3 => 'director_name',
            4 => 'phone',
            5 => 'city',
            6 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';

        $query = Company::withCount('branches');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_perusahaan', 'like', "%{$search}%")
                  ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('npwp', 'like', "%{$search}%")
                  ->orWhere('nib', 'like', "%{$search}%")
                  ->orWhere('director_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();
        $totalData     = Company::count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($c, $index) use ($start) {
            return [
                'no'              => $start + $index + 1,
                'id'              => $c->id,
                'kode_perusahaan' => e($c->kode_perusahaan),
                'nama_perusahaan' => e($c->nama_perusahaan),
                'logo_url'        => $c->logo ? asset('storage/' . $c->logo) : null,
                'npwp'            => $c->npwp ? e($c->npwp) : null,
                'nib'             => $c->nib ? e($c->nib) : null,
                'director_name'   => $c->director_name ? e($c->director_name) : null,
                'phone'           => $c->phone ? e($c->phone) : null,
                'email'           => $c->email ? e($c->email) : null,
                'city'            => $c->city ? e($c->city) : null,
                'province'        => $c->province ? e($c->province) : null,
                'status'          => $c->status,
                'branches_count'  => $c->branches_count,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-perusahaan');

        if ($request->has('kode_perusahaan') && !empty($request->kode_perusahaan)) {
            $request->merge(['kode_perusahaan' => strtoupper(trim($request->kode_perusahaan))]);
        }

        $request->validate([
            'kode_perusahaan' => 'nullable|string|max:10|unique:companies,kode_perusahaan',
            'nama_perusahaan' => 'required|string|max:40',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'npwp'            => 'nullable|string|max:30',
            'nib'             => 'nullable|string|max:30',
            'director_name'   => 'nullable|string|max:40',
            'phone'           => 'nullable|regex:/^[0-9]+$/|max:13',
            'email'           => 'nullable|email|max:60',
            'address'         => 'nullable|string|max:60',
            'city'            => 'nullable|string|max:100',
            'province'        => 'nullable|string|max:40',
            'postal_code'     => 'nullable|regex:/^[0-9]+$/|max:10',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_perusahaan;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('companies', 'PT-', 'kode_perusahaan', 2);
            }

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('company_logos', 'public');
            }

            Company::create([
                'kode_perusahaan' => strtoupper($kode),
                'nama_perusahaan' => $request->nama_perusahaan,
                'logo'            => $logoPath,
                'npwp'            => $request->npwp,
                'nib'             => $request->nib,
                'director_name'   => $request->director_name,
                'phone'           => $request->phone,
                'email'           => $request->email,
                'address'         => $request->address,
                'city'            => $request->city,
                'province'        => $request->province,
                'postal_code'     => $request->postal_code,
                'status'          => $request->status,
                'created_by'      => auth()->id(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Perusahaan (PT) berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store company: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah perusahaan.'], 500);
        }
    }

    public function show(Company $company)
    {
        $this->authorize('lihat-perusahaan');
        $company->loadCount('branches');
        $company->load(['branches' => fn($q) => $q->select('id', 'company_id', 'kode_cabang', 'nama_cabang', 'city', 'status')]);
        if ($company->logo) {
            $company->logo_url = asset('storage/' . $company->logo);
        }
        return response()->json(['company' => $company]);
    }

    public function edit(Company $company)
    {
        $this->authorize('edit-perusahaan');
        if ($company->logo) {
            $company->logo_url = asset('storage/' . $company->logo);
        }
        return response()->json(['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('edit-perusahaan');

        if ($request->has('kode_perusahaan') && !empty($request->kode_perusahaan)) {
            $request->merge(['kode_perusahaan' => strtoupper(trim($request->kode_perusahaan))]);
        }

        $request->validate([
            'kode_perusahaan' => 'nullable|string|max:10|unique:companies,kode_perusahaan,' . $company->id,
            'nama_perusahaan' => 'required|string|max:40',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'npwp'            => 'nullable|string|max:30',
            'nib'             => 'nullable|string|max:30',
            'director_name'   => 'nullable|string|max:40',
            'phone'           => 'nullable|regex:/^[0-9]+$/|max:13',
            'email'           => 'nullable|email|max:60',
            'address'         => 'nullable|string|max:60',
            'city'            => 'nullable|string|max:100',
            'province'        => 'nullable|string|max:40',
            'postal_code'     => 'nullable|regex:/^[0-9]+$/|max:10',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_perusahaan;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('companies', 'PT-', 'kode_perusahaan', 2);
            }

            $logoPath = $company->logo;
            if ($request->hasFile('logo')) {
                if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                    Storage::disk('public')->delete($company->logo);
                }
                $logoPath = $request->file('logo')->store('company_logos', 'public');
            }

            $company->update([
                'kode_perusahaan' => $company->kode_perusahaan,
                'nama_perusahaan' => $request->nama_perusahaan,
                'logo'            => $logoPath,
                'npwp'            => $request->npwp,
                'nib'             => $request->nib,
                'director_name'   => $request->director_name,
                'phone'           => $request->phone,
                'email'           => $request->email,
                'address'         => $request->address,
                'city'            => $request->city,
                'province'        => $request->province,
                'postal_code'     => $request->postal_code,
                'status'          => $request->status,
                'updated_by'      => auth()->id(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Perusahaan (PT) berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update company: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate perusahaan.'], 500);
        }
    }

    public function destroy(Company $company)
    {
        $this->authorize('hapus-perusahaan');

        $usedCount = $company->branches()->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Perusahaan \"{$company->nama_perusahaan}\" tidak dapat dihapus karena masih membawahi {$usedCount} cabang."
            ], 422);
        }

        try {
            DB::beginTransaction();
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $company->delete();
            DB::commit();

            return response()->json(['message' => 'Perusahaan berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete company: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus perusahaan.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-perusahaan');

        $companies = DB::table('companies')
            ->select('kode_perusahaan', 'nama_perusahaan', 'npwp', 'nib', 'director_name', 'phone', 'email', 'address', 'city', 'province', 'postal_code', 'status')
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode PT</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Perusahaan (PT)</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>NPWP</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>NIB</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Direktur / Penanggung Jawab</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>No Telepon</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Email</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Alamat</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kota</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Provinsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Pos</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($companies as $c) {
            $statusStyle = $c->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $c->kode_perusahaan . '</style><center>',
                '<style border="thin">' . $c->nama_perusahaan . '</style><left>',
                '<style border="thin">' . ($c->npwp ?? '') . '</style><left>',
                '<style border="thin">' . ($c->nib ?? '') . '</style><left>',
                '<style border="thin">' . ($c->director_name ?? '') . '</style><left>',
                '<style border="thin">' . ($c->phone ?? '') . '</style><center>',
                '<style border="thin">' . ($c->email ?? '') . '</style><left>',
                '<style border="thin">' . ($c->address ?? '') . '</style><left>',
                '<style border="thin">' . ($c->city ?? '') . '</style><left>',
                '<style border="thin">' . ($c->province ?? '') . '</style><left>',
                '<style border="thin">' . ($c->postal_code ?? '') . '</style><center>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_perusahaan_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-perusahaan');

        $rows = [
            ['Kode PT', 'Nama Perusahaan', 'NPWP', 'NIB', 'Nama Direktur', 'No Telepon', 'Email', 'Alamat', 'Kota', 'Provinsi', 'Kode Pos', 'Status'],
            ['PT-MB', 'PT. Contoh Maju Bersama', '01.234.567.8-901.000', '1234567890123', 'Bambang Sudibyo', '0215551234', 'info@majubersama.co.id', 'Jl. Sudirman No 1', 'Jakarta Selatan', 'DKI Jakarta', '12190', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_perusahaan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-perusahaan');

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

        $file = $request->file('excel_file');
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        DB::disableQueryLog();

        $rows      = [];
        $extension = strtolower($file->getClientOriginalExtension());

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
        $processedNames = [];
        $insertData     = [];

        try {
            DB::beginTransaction();

            $existingCodes = DB::table('companies')
                ->pluck('kode_perusahaan')
                ->map(fn($c) => strtolower($c))
                ->flip()
                ->toArray();

            $existingNames = DB::table('companies')
                ->whereNull('deleted_at')
                ->pluck('nama_perusahaan')
                ->map(fn($n) => strtolower($n))
                ->flip()
                ->toArray();

            $now = now()->toDateTimeString();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawKode     = isset($row[0]) ? trim($row[0]) : '';
                $rawNama     = isset($row[1]) ? trim($row[1]) : '';
                $rawNpwp     = isset($row[2]) ? trim($row[2]) : '';
                $rawNib      = isset($row[3]) ? trim($row[3]) : '';
                $rawDirector = isset($row[4]) ? trim($row[4]) : '';
                $rawPhone    = isset($row[5]) ? trim($row[5]) : '';
                $rawEmail    = isset($row[6]) ? trim($row[6]) : '';
                $rawAddress  = isset($row[7]) ? trim($row[7]) : '';
                $rawCity     = isset($row[8]) ? trim($row[8]) : '';
                $rawProvince = isset($row[9]) ? trim($row[9]) : '';
                $rawPostal   = isset($row[10]) ? trim($row[10]) : '';
                $rawStatus   = isset($row[11]) ? trim($row[11]) : 'Active';
                $rowNum      = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Perusahaan wajib diisi.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Perusahaan '{$rawNama}' duplikat di dalam file.");
                }
                if (isset($existingNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Perusahaan '{$rawNama}' sudah terdaftar di database.");
                }
                $processedNames[$namaLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $autoSeq = 1;
                    do {
                        $candidate = CodeGeneratorHelper::generate('companies', 'PT-', 'kode_perusahaan', 2);
                        if (isset($processedCodes[strtolower($candidate)]) || isset($existingCodes[strtolower($candidate)])) {
                            $nextNum = (int) preg_replace('/[^0-9]/', '', $candidate) + $autoSeq++;
                            $candidate = 'PT-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
                        } else {
                            $kode = $candidate;
                            break;
                        }
                    } while (isset($processedCodes[strtolower($candidate)]) || isset($existingCodes[strtolower($candidate)]));
                    $kode = $candidate;
                } else {
                    if (isset($processedCodes[strtolower($kode)]) || isset($existingCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Perusahaan '{$kode}' sudah terdaftar.");
                    }
                }
                $processedCodes[strtolower($kode)] = true;

                $insertData[] = [
                    'kode_perusahaan' => strtoupper($kode),
                    'nama_perusahaan' => $rawNama,
                    'npwp'            => $rawNpwp ?: null,
                    'nib'             => $rawNib ?: null,
                    'director_name'   => $rawDirector ?: null,
                    'phone'           => $rawPhone ?: null,
                    'email'           => $rawEmail ?: null,
                    'address'         => $rawAddress ?: null,
                    'city'            => $rawCity ?: null,
                    'province'        => $rawProvince ?: null,
                    'postal_code'     => $rawPostal ?: null,
                    'status'          => in_array($rawStatus, ['Active', 'Inactive']) ? $rawStatus : 'Active',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (!empty($insertData)) {
                DB::table('companies')->insert($insertData);
            }

            DB::commit();
            return response()->json(['message' => 'Data perusahaan berhasil diimpor!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import perusahaan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 422);
        }
    }
}
