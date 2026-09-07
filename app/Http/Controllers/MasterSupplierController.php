<?php

namespace App\Http\Controllers;

use App\Models\MasterSupplier;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Yajra\DataTables\Facades\DataTables;

class MasterSupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-supplier');

        if ($request->ajax()) {
            $query = MasterSupplier::forActiveCompany()->with('primaryBank')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kode_badge', function ($row) {
                    return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'
                        . e($row->kode_supplier) . '</span>';
                })
                ->addColumn('nama_info', function ($row) {
                    $html = '<div class="fw-bold">' . e($row->nama_supplier) . '</div>';
                    if ($row->email) {
                        $html .= '<div class="small text-muted"><i class="bi bi-envelope me-1"></i>' . e($row->email) . '</div>';
                    }
                    return $html;
                })
                ->addColumn('kontak_pic', function ($row) {
                    return e($row->nama_kontak ?? '-');
                })
                ->addColumn('telepon_val', function ($row) {
                    return e($row->telepon ?? '-');
                })
                ->addColumn('termin_badge', function ($row) {
                    $text = $row->term_pembayaran > 0 ? $row->term_pembayaran . ' Hari' : 'COD';
                    return '<span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill fw-semibold">' . $text . '</span>';
                })
                ->addColumn('bank_info', function ($row) {
                    if ($row->primaryBank) {
                        return '<div class="d-flex align-items-center flex-wrap gap-1.5">'
                            . '<span class="badge bg-info bg-opacity-10 text-info fw-semibold px-2.5 py-1" title="Rekening Utama">'
                            . e($row->primaryBank->nama_bank) . '</span>'
                            . '<span class="small font-monospace text-muted" title="Nomor Rekening">' . e($row->primaryBank->nomor_rekening) . '</span>'
                            . '</div>';
                    }
                    return '<span class="text-muted small italic">Tidak ada info bank</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'Active') {
                        return '<span class="badge bg-success bg-opacity-10 text-success fw-semibold px-3 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>';
                    }
                    return '<span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-3 py-1.5 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>';
                })
                ->addColumn('aksi', function ($row) {
                    $user = auth()->user();
                    $btn = '<div class="d-flex justify-content-center gap-1">';
                    if ($user->can('lihat-supplier')) {
                        $btn .= '<button class="btn btn-sm btn-light text-primary border btn-detail" data-id="' . $row->id . '" title="Lihat Detail"><i class="bi bi-eye"></i></button> ';
                    }
                    if ($user->can('edit-supplier')) {
                        $btn .= '<button class="btn btn-sm btn-light text-info border btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button> ';
                    }
                    if ($user->can('hapus-supplier')) {
                        $btn .= '<button class="btn btn-sm btn-light text-danger border btn-delete" data-id="' . $row->id . '" data-name="' . e($row->nama_supplier) . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['kode_badge', 'nama_info', 'termin_badge', 'bank_info', 'status_badge', 'aksi'])
                ->make(true);
        }

        return view('modul_purchasing.supplier.viewsupplier');
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-supplier');

        if ($request->has('supplier_email')) {
            $request->merge(['email' => $request->supplier_email]);
        }

        // Filter out empty bank account rows
        $banks = [];
        if ($request->has('banks')) {
            foreach ($request->banks as $bank) {
                if (!empty($bank['nama_bank']) || !empty($bank['nomor_rekening']) || !empty($bank['atas_nama_rekening'])) {
                    $banks[] = $bank;
                }
            }
        }

        $request->validate([
            'kode_supplier'       => 'nullable|string|max:10|unique:master_suppliers,kode_supplier,NULL,id,deleted_at,NULL',
            'nama_supplier'       => 'required|string|max:40',
            'nama_kontak'         => 'nullable|string|max:30',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => 'nullable|email|max:40|unique:master_suppliers,email,NULL,id,deleted_at,NULL',
            'npwp'                => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'alamat'              => 'nullable|string|max:60',
            'kota'                => 'nullable|string|max:20',
            'provinsi'            => 'nullable|string|max:20',
            'kode_pos'            => 'nullable|string|max:10|regex:/^[0-9]+$/',
            'term_pembayaran'     => 'nullable|integer|min:0|max:99999',
            'limit_kredit'        => 'nullable|numeric|min:0',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string|max:30',
            'banks.*.nama_bank'           => 'nullable|string|max:15',
            'banks.*.nomor_rekening'      => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'banks.*.atas_nama_rekening'  => 'nullable|string|max:40',
        ], [
            'kode_supplier.max'            => 'Kode supplier tidak boleh melebihi 10 karakter.',
            'kode_supplier.unique'         => 'Kode supplier sudah digunakan.',
            'nama_supplier.required'       => 'Nama supplier wajib diisi.',
            'nama_supplier.max'            => 'Nama supplier tidak boleh melebihi 40 karakter.',
            'nama_kontak.max'              => 'Nama kontak (PIC) tidak boleh melebihi 30 karakter.',
            'telepon.max'                  => 'Telepon tidak boleh melebihi 13 digit.',
            'telepon.regex'                => 'Telepon hanya boleh diisi angka.',
            'email.email'                  => 'Format email tidak valid.',
            'email.max'                    => 'Email tidak boleh melebihi 40 karakter.',
            'email.unique'                 => 'Email sudah digunakan.',
            'npwp.max'                     => 'Nomor NPWP tidak boleh melebihi 20 digit.',
            'npwp.regex'                   => 'Nomor NPWP hanya boleh diisi angka.',
            'alamat.max'                   => 'Alamat tidak boleh melebihi 60 karakter.',
            'kota.max'                     => 'Kota tidak boleh melebihi 20 karakter.',
            'provinsi.max'                 => 'Provinsi tidak boleh melebihi 20 karakter.',
            'kode_pos.max'                 => 'Kode pos tidak boleh melebihi 10 digit.',
            'kode_pos.regex'               => 'Kode pos hanya boleh diisi angka.',
            'term_pembayaran.max'          => 'Termin pembayaran tidak boleh melebihi 5 digit.',
            'keterangan.max'               => 'Keterangan tambahan tidak boleh melebihi 30 karakter.',
            'banks.*.nama_bank.max'          => 'Nama bank tidak boleh melebihi 15 karakter.',
            'banks.*.nomor_rekening.max'     => 'Nomor rekening tidak boleh melebihi 15 digit.',
            'banks.*.nomor_rekening.regex'   => 'Nomor rekening hanya boleh diisi angka.',
            'banks.*.atas_nama_rekening.max' => 'Atas nama rekening tidak boleh melebihi 40 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_supplier;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('master_suppliers', 'SPL-', 'kode_supplier', 4, false);
            }

            $supplier = MasterSupplier::create([
                'kode_supplier'       => $kode,
                'nama_supplier'       => $request->nama_supplier,
                'nama_kontak'         => $request->nama_kontak,
                'telepon'             => $request->telepon,
                'email'               => $request->email,
                'npwp'                => $request->npwp,
                'alamat'              => $request->alamat,
                'kota'                => $request->kota,
                'provinsi'            => $request->provinsi,
                'kode_pos'            => $request->kode_pos,
                'term_pembayaran'     => $request->term_pembayaran ?? 0,
                'limit_kredit'        => $request->limit_kredit ?? 0,
                'status'              => $request->status,
                'keterangan'          => $request->keterangan,
            ]);

            // Save multiple banks
            if (count($banks) > 0) {
                // Find if a primary bank was checked
                $hasPrimary = false;
                foreach ($banks as $b) {
                    if (isset($b['is_primary']) && ($b['is_primary'] == '1' || $b['is_primary'] == 'true')) {
                        $hasPrimary = true;
                        break;
                    }
                }

                foreach ($banks as $index => $b) {
                    $isPrimary = false;
                    if ($hasPrimary) {
                        $isPrimary = isset($b['is_primary']) && ($b['is_primary'] == '1' || $b['is_primary'] == 'true');
                    } else {
                        // Default to the first account as primary if none were selected
                        $isPrimary = ($index === 0);
                    }

                    $supplier->banks()->create([
                        'nama_bank'           => $b['nama_bank'] ?? '-',
                        'nomor_rekening'      => $b['nomor_rekening'] ?? '-',
                        'atas_nama_rekening'  => $b['atas_nama_rekening'] ?? '-',
                        'is_primary'          => $isPrimary,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message'  => 'Supplier berhasil ditambahkan!',
                'supplier' => $supplier
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah supplier.'], 500);
        }
    }

    public function edit(MasterSupplier $supplier)
    {
        $this->authorize('edit-supplier');

        if (session('active_company_id') && $supplier->company_id != session('active_company_id')) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data supplier perusahaan lain.'], 403);
        }

        // Eager load bank relations
        $supplier->load('banks');
        return response()->json(['supplier' => $supplier]);
    }

    public function update(Request $request, MasterSupplier $supplier)
    {
        $this->authorize('edit-supplier');

        if (session('active_company_id') && $supplier->company_id != session('active_company_id')) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data supplier perusahaan lain.'], 403);
        }

        if ($request->has('supplier_email')) {
            $request->merge(['email' => $request->supplier_email]);
        }

        // Filter out empty bank account rows
        $banks = [];
        if ($request->has('banks')) {
            foreach ($request->banks as $bank) {
                if (!empty($bank['nama_bank']) || !empty($bank['nomor_rekening']) || !empty($bank['atas_nama_rekening'])) {
                    $banks[] = $bank;
                }
            }
        }

        $request->validate([
            'kode_supplier'       => 'required|string|max:10|unique:master_suppliers,kode_supplier,' . $supplier->id . ',id,deleted_at,NULL',
            'nama_supplier'       => 'required|string|max:40',
            'nama_kontak'         => 'nullable|string|max:30',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => 'nullable|email|max:40|unique:master_suppliers,email,' . $supplier->id . ',id,deleted_at,NULL',
            'npwp'                => 'nullable|string|max:20',
            'alamat'              => 'nullable|string|max:60',
            'kota'                => 'nullable|string|max:20',
            'provinsi'            => 'nullable|string|max:20',
            'kode_pos'            => 'nullable|string|max:10|regex:/^[0-9]+$/',
            'term_pembayaran'     => 'nullable|integer|min:0|max:99999',
            'limit_kredit'        => 'nullable|numeric|min:0',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string|max:30',
            'banks.*.nama_bank'           => 'nullable|string|max:15',
            'banks.*.nomor_rekening'      => 'nullable|string|max:15',
            'banks.*.atas_nama_rekening'  => 'nullable|string|max:40',
        ], [
            'kode_supplier.required'       => 'Kode supplier wajib diisi.',
            'kode_supplier.max'            => 'Kode supplier tidak boleh melebihi 10 karakter.',
            'kode_supplier.unique'         => 'Kode supplier sudah digunakan.',
            'nama_supplier.required'       => 'Nama supplier wajib diisi.',
            'nama_supplier.max'            => 'Nama supplier tidak boleh melebihi 40 karakter.',
            'nama_kontak.max'              => 'Nama kontak (PIC) tidak boleh melebihi 30 karakter.',
            'telepon.max'                  => 'Telepon tidak boleh melebihi 13 digit.',
            'telepon.regex'                => 'Telepon hanya boleh diisi angka.',
            'email.email'                  => 'Format email tidak valid.',
            'email.max'                    => 'Email tidak boleh melebihi 40 karakter.',
            'email.unique'                 => 'Email sudah digunakan.',
            'npwp.max'                     => 'Nomor NPWP tidak boleh melebihi 20 karakter.',
            'alamat.max'                   => 'Alamat tidak boleh melebihi 60 karakter.',
            'kota.max'                     => 'Kota tidak boleh melebihi 20 karakter.',
            'provinsi.max'                 => 'Provinsi tidak boleh melebihi 20 karakter.',
            'kode_pos.max'                 => 'Kode pos tidak boleh melebihi 10 digit.',
            'kode_pos.regex'               => 'Kode pos hanya boleh diisi angka.',
            'term_pembayaran.max'          => 'Termin pembayaran tidak boleh melebihi 5 digit.',
            'keterangan.max'               => 'Keterangan tambahan tidak boleh melebihi 30 karakter.',
            'banks.*.nama_bank.max'          => 'Nama bank tidak boleh melebihi 15 karakter.',
            'banks.*.nomor_rekening.max'     => 'Nomor rekening tidak boleh melebihi 15 karakter.',
            'banks.*.atas_nama_rekening.max' => 'Atas nama rekening tidak boleh melebihi 40 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $supplier->update([
                'kode_supplier'       => $request->kode_supplier,
                'nama_supplier'       => $request->nama_supplier,
                'nama_kontak'         => $request->nama_kontak,
                'telepon'             => $request->telepon,
                'email'               => $request->email,
                'npwp'                => $request->npwp,
                'alamat'              => $request->alamat,
                'kota'                => $request->kota,
                'provinsi'            => $request->provinsi,
                'kode_pos'            => $request->kode_pos,
                'term_pembayaran'     => $request->term_pembayaran ?? 0,
                'limit_kredit'        => $request->limit_kredit ?? 0,
                'status'              => $request->status,
                'keterangan'          => $request->keterangan,
            ]);

            // Sync bank accounts (re-write strategy)
            $supplier->banks()->delete();

            if (count($banks) > 0) {
                // Find if a primary bank was checked
                $hasPrimary = false;
                foreach ($banks as $b) {
                    if (isset($b['is_primary']) && ($b['is_primary'] == '1' || $b['is_primary'] == 'true')) {
                        $hasPrimary = true;
                        break;
                    }
                }

                foreach ($banks as $index => $b) {
                    $isPrimary = false;
                    if ($hasPrimary) {
                        $isPrimary = isset($b['is_primary']) && ($b['is_primary'] == '1' || $b['is_primary'] == 'true');
                    } else {
                        // Default to the first account as primary if none were selected
                        $isPrimary = ($index === 0);
                    }

                    $supplier->banks()->create([
                        'nama_bank'           => $b['nama_bank'] ?? '-',
                        'nomor_rekening'      => $b['nomor_rekening'] ?? '-',
                        'atas_nama_rekening'  => $b['atas_nama_rekening'] ?? '-',
                        'is_primary'          => $isPrimary,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Supplier berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui supplier.'], 500);
        }
    }

    public function destroy(MasterSupplier $supplier)
    {
        $this->authorize('hapus-supplier');

        if (session('active_company_id') && $supplier->company_id != session('active_company_id')) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data supplier perusahaan lain.'], 403);
        }

        try {
            DB::beginTransaction();
            $supplier->delete();
            DB::commit();
            return response()->json(['message' => 'Supplier berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus supplier.'], 500);
        }
    }

    public function getNextCode()
    {
        $code = CodeGeneratorHelper::generate('master_suppliers', 'SPL-', 'kode_supplier', 4, false);
        return response()->json(['code' => $code]);
    }

    public function downloadTemplate()
    {
        $this->authorize('tambah-supplier');

        try {
            $headers = [
                'Kode Supplier (Opsional max 10)',
                'Nama Supplier (Wajib max 40)',
                'Nama Kontak PIC (max 30)',
                'Telepon (Hanya Angka max 13)',
                'Email (max 40)',
                'NPWP (max 20)',
                'Alamat (max 60)',
                'Kota (max 20)',
                'Provinsi (max 20)',
                'Kode Pos (Hanya Angka max 10)',
                'Termin Pembayaran (Hari)',
                'Limit Kredit (Rp)',
                'Status (Active / Inactive)',
                'Nama Bank Utama (max 15)',
                'Nomor Rekening Utama (max 15)',
                'Atas Nama Rekening (max 40)',
                'Keterangan (max 30)'
            ];

            $sample1 = [
                'SPL-0001',
                'PT. Sumber Makmur',
                'Budi Santoso',
                '081234567890',
                'budi@sumbermakmur.com',
                '01.234.567.8-000.000',
                'Jl. Sudirman No. 12',
                'Jakarta Selatan',
                'DKI Jakarta',
                '12190',
                '30',
                '10000000',
                'Active',
                'BCA',
                '1234567890',
                'PT. Sumber Makmur',
                'Supplier Utama Bahan'
            ];

            $xlsx = SimpleXLSXGen::fromArray([$headers, $sample1]);
            $xlsx->downloadAs('Template_Import_Supplier.xlsx');
            exit();
        } catch (\Exception $e) {
            Log::error('Error download template supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengunduh template.'], 500);
        }
    }

    public function import(Request $request)
    {
        $this->authorize('tambah-supplier');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ], [
            'file.required' => 'Harap pilih file Excel atau CSV.',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file tidak boleh melebihi 10 MB.'
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            $rows = [];
            if ($xlsx = SimpleXLSX::parse($filePath)) {
                $rows = $xlsx->rows();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membaca isi file Excel. Pastikan file tidak terenkripsi atau rusak.'
                ], 422);
            }

            if (count($rows) <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau hanya berisi baris header.'
                ], 422);
            }

            // Remove header row
            array_shift($rows);

            // Pre-fetch all existing kodes and emails (including soft deleted ones) in bulk to avoid DB queries inside loop
            $existingKodes = MasterSupplier::withTrashed()
                ->whereNotNull('kode_supplier')
                ->pluck('kode_supplier')
                ->mapWithKeys(function ($item) {
                    return [strtolower(trim($item)) => true];
                })->toArray();

            $existingEmails = MasterSupplier::withTrashed()
                ->whereNotNull('email')
                ->pluck('email')
                ->mapWithKeys(function ($item) {
                    return [strtolower(trim($item)) => true];
                })->toArray();

            $errors = [];
            $dataToInsert = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Line in Excel (header is row 1)

                $kode       = trim((string)($row[0] ?? ''));
                $nama       = trim((string)($row[1] ?? ''));
                $kontak     = trim((string)($row[2] ?? ''));
                $telepon    = trim((string)($row[3] ?? ''));
                $email      = trim((string)($row[4] ?? ''));
                $npwp       = trim((string)($row[5] ?? ''));
                $alamat     = trim((string)($row[6] ?? ''));
                $kota       = trim((string)($row[7] ?? ''));
                $provinsi   = trim((string)($row[8] ?? ''));
                $kodePos    = trim((string)($row[9] ?? ''));
                $termin     = trim((string)($row[10] ?? '0'));
                $limit      = trim((string)($row[11] ?? '0'));
                $status     = trim((string)($row[12] ?? 'Active'));
                $namaBank   = trim((string)($row[13] ?? ''));
                $noRek      = trim((string)($row[14] ?? ''));
                $anRek      = trim((string)($row[15] ?? ''));
                $keterangan = trim((string)($row[16] ?? ''));

                // Check if row is completely empty or is a template sample row
                if ((empty($kode) && empty($nama) && empty($kontak) && empty($telepon) && empty($email)) ||
                    $nama === 'PT. Sumber Makmur' || $nama === 'CV. Jaya Bersama') {
                    continue;
                }

                // Validation rules per row
                if (empty($nama)) {
                    $errors[] = "Baris {$rowNumber}: Nama Supplier wajib diisi.";
                } elseif (strlen($nama) > 40) {
                    $errors[] = "Baris {$rowNumber}: Nama Supplier ('{$nama}') melebihi 40 karakter.";
                }

                if (!empty($kode)) {
                    $lowerKode = strtolower($kode);
                    if (strlen($kode) > 10) {
                        $errors[] = "Baris {$rowNumber}: Kode Supplier ('{$kode}') melebihi 10 karakter.";
                    } elseif (isset($existingKodes[$lowerKode])) {
                        $errors[] = "Baris {$rowNumber}: Kode Supplier ('{$kode}') sudah terdaftar atau duplikat.";
                    } else {
                        $existingKodes[$lowerKode] = true;
                    }
                }

                if (!empty($kontak) && strlen($kontak) > 30) {
                    $errors[] = "Baris {$rowNumber}: Nama Kontak ('{$kontak}') melebihi 30 karakter.";
                }

                if (!empty($telepon)) {
                    if (strlen($telepon) > 13) {
                        $errors[] = "Baris {$rowNumber}: Telepon ('{$telepon}') melebihi 13 digit.";
                    } elseif (!preg_match('/^[0-9]+$/', $telepon)) {
                        $errors[] = "Baris {$rowNumber}: Telepon ('{$telepon}') hanya boleh berupa angka.";
                    }
                }

                if (!empty($email)) {
                    $lowerEmail = strtolower($email);
                    if (strlen($email) > 40) {
                        $errors[] = "Baris {$rowNumber}: Email ('{$email}') melebihi 40 karakter.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Baris {$rowNumber}: Format email ('{$email}') tidak valid.";
                    } elseif (isset($existingEmails[$lowerEmail])) {
                        $errors[] = "Baris {$rowNumber}: Email ('{$email}') sudah terdaftar atau duplikat.";
                    } else {
                        $existingEmails[$lowerEmail] = true;
                    }
                }

                if (!empty($npwp) && strlen($npwp) > 20) {
                    $errors[] = "Baris {$rowNumber}: Nomor NPWP melebihi 20 karakter.";
                }

                if (!empty($alamat) && strlen($alamat) > 60) {
                    $errors[] = "Baris {$rowNumber}: Alamat Lengkap melebihi 60 karakter.";
                }

                if (!empty($kota) && strlen($kota) > 20) {
                    $errors[] = "Baris {$rowNumber}: Kota ('{$kota}') melebihi 20 karakter.";
                }

                if (!empty($provinsi) && strlen($provinsi) > 20) {
                    $errors[] = "Baris {$rowNumber}: Provinsi ('{$provinsi}') melebihi 20 karakter.";
                }

                if (!empty($kodePos)) {
                    if (strlen($kodePos) > 10) {
                        $errors[] = "Baris {$rowNumber}: Kode Pos ('{$kodePos}') melebihi 10 digit.";
                    } elseif (!preg_match('/^[0-9]+$/', $kodePos)) {
                        $errors[] = "Baris {$rowNumber}: Kode Pos ('{$kodePos}') hanya boleh berupa angka.";
                    }
                }

                if (!empty($status) && !in_array(strtolower($status), ['active', 'inactive'])) {
                    $errors[] = "Baris {$rowNumber}: Status ('{$status}') harus 'Active' atau 'Inactive'.";
                }

                if (!empty($namaBank) && strlen($namaBank) > 15) {
                    $errors[] = "Baris {$rowNumber}: Nama Bank ('{$namaBank}') melebihi 15 karakter.";
                }

                if (!empty($noRek) && strlen($noRek) > 15) {
                    $errors[] = "Baris {$rowNumber}: Nomor Rekening ('{$noRek}') melebihi 15 karakter.";
                }

                if (!empty($anRek) && strlen($anRek) > 40) {
                    $errors[] = "Baris {$rowNumber}: Atas Nama Rekening ('{$anRek}') melebihi 40 karakter.";
                }

                if (!empty($keterangan) && strlen($keterangan) > 30) {
                    $errors[] = "Baris {$rowNumber}: Keterangan Tambahan melebihi 30 karakter.";
                }

                // If no errors so far, prepare data
                $dataToInsert[] = [
                    'kode_supplier'   => $kode,
                    'nama_supplier'   => $nama,
                    'nama_kontak'     => $kontak,
                    'telepon'         => $telepon,
                    'email'           => $email,
                    'npwp'            => $npwp,
                    'alamat'          => $alamat,
                    'kota'            => $kota,
                    'provinsi'        => $provinsi,
                    'kode_pos'        => $kodePos,
                    'term_pembayaran' => (int) $termin,
                    'limit_kredit'    => (float) $limit,
                    'status'          => ucfirst(strtolower($status ?: 'active')),
                    'keterangan'      => $keterangan,
                    'nama_bank'       => $namaBank,
                    'nomor_rekening'  => $noRek,
                    'atas_nama'       => $anRek,
                ];
            }

            // Return errors if any row failed validation
            if (count($errors) > 0) {
                return response()->json([
                    'success' => false,
                    'errors'  => $errors
                ], 422);
            }

            if (count($dataToInsert) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada baris data valid yang ditemukan untuk diimpor.'
                ], 422);
            }

            // Perform Database Insert inside Transaction
            DB::beginTransaction();

            $importedCount = 0;

            foreach ($dataToInsert as $item) {
                $kode = $item['kode_supplier'];
                if (empty($kode)) {
                    do {
                        $kode = CodeGeneratorHelper::generate('master_suppliers', 'SPL-', 'kode_supplier', 4, false);
                    } while (isset($existingKodes[strtolower($kode)]));
                    $existingKodes[strtolower($kode)] = true;
                }

                $supplier = MasterSupplier::create([
                    'kode_supplier'   => $kode,
                    'nama_supplier'   => $item['nama_supplier'],
                    'nama_kontak'     => $item['nama_kontak'],
                    'telepon'         => $item['telepon'],
                    'email'           => $item['email'],
                    'npwp'            => $item['npwp'],
                    'alamat'          => $item['alamat'],
                    'kota'            => $item['kota'],
                    'provinsi'        => $item['provinsi'],
                    'kode_pos'        => $item['kode_pos'],
                    'term_pembayaran' => $item['term_pembayaran'],
                    'limit_kredit'    => $item['limit_kredit'],
                    'status'          => $item['status'],
                    'keterangan'      => $item['keterangan'],
                ]);

                if (!empty($item['nama_bank']) || !empty($item['nomor_rekening']) || !empty($item['atas_nama'])) {
                    $supplier->banks()->create([
                        'nama_bank'          => $item['nama_bank'] ?: '-',
                        'nomor_rekening'     => $item['nomor_rekening'] ?: '-',
                        'atas_nama_rekening' => $item['atas_nama'] ?: '-',
                        'is_primary'         => true,
                    ]);
                }

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} data supplier!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses import file.'
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-supplier');

        try {
            $suppliers = MasterSupplier::forActiveCompany()
                ->with('primaryBank')
                ->latest()
                ->get();

            $headers = [
                'No',
                'Kode Supplier',
                'Nama Supplier',
                'Nama Kontak (PIC)',
                'Telepon',
                'Email',
                'NPWP',
                'Alamat Lengkap',
                'Kota',
                'Provinsi',
                'Kode Pos',
                'Termin Pembayaran (Hari)',
                'Limit Kredit (Rp)',
                'Status',
                'Bank Utama',
                'No. Rekening Utama',
                'Atas Nama Rekening',
                'Keterangan'
            ];

            $rows = [$headers];
            $no = 1;

            foreach ($suppliers as $s) {
                $bank = $s->primaryBank;
                $rows[] = [
                    $no++,
                    $s->kode_supplier ?? '-',
                    $s->nama_supplier ?? '-',
                    $s->nama_kontak ?? '-',
                    $s->telepon ?? '-',
                    $s->email ?? '-',
                    $s->npwp ?? '-',
                    $s->alamat ?? '-',
                    $s->kota ?? '-',
                    $s->provinsi ?? '-',
                    $s->kode_pos ?? '-',
                    (int) $s->term_pembayaran,
                    (float) $s->limit_kredit,
                    $s->status ?? 'Active',
                    $bank ? $bank->nama_bank : '-',
                    $bank ? $bank->nomor_rekening : '-',
                    $bank ? $bank->atas_nama_rekening : '-',
                    $s->keterangan ?? '-'
                ];
            }

            $filename = 'Export_Supplier_' . date('Ymd_His') . '.xlsx';
            if (ob_get_length()) {
                ob_end_clean();
            }
            $xlsx = SimpleXLSXGen::fromArray($rows);
            $xlsx->downloadAs($filename);
            exit();

        } catch (\Exception $e) {
            Log::error('Error Export Supplier: ' . $e->getMessage());
            return response("<script>alert('Terjadi kesalahan saat memproses export data supplier.'); history.back();</script>");
        }
    }
}
