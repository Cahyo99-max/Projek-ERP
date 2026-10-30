<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanBarang;
use App\Models\PeminjamanBarangDetail;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterJenisGudang;
use App\Models\MasterSatuan;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Helpers\DecimalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\WhatsAppGatewayController;
use Yajra\DataTables\Facades\DataTables;

class PeminjamanBarangController extends Controller
{
    /**
     * Resolves active company and active branch scoping.
     */
    private function getActiveScope(): array
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $isSuperAdmin    = auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Owner') || 
            auth()->user()->hasRole('Admin')
        );

        return [
            'company_id'     => $activeCompanyId,
            'branch_id'      => $activeBranchId,
            'is_super_admin' => $isSuperAdmin,
        ];
    }

    /**
     * Helper to verify entity authorization on a PeminjamanBarang record.
     */
    private function authorizeRecord(PeminjamanBarang $peminjaman): void
    {
        // Strict Privacy: Dokumen status Draft HANYA BISA DIAKSES OLEH PEMBUATNYA (created_by / diajukan_oleh) atau Super Admin / Owner
        if ($peminjaman->status === 'Draft') {
            $userId = auth()->id();
            $isCreator = ($peminjaman->created_by == $userId) || ($peminjaman->diajukan_oleh == $userId);
            $isSuperOrOwner = auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
            if (!$isCreator && !$isSuperOrOwner) {
                abort(403, 'Akses ditolak. Dokumen peminjaman berstatus Draft hanya dapat diakses oleh pengguna yang membuatnya.');
            }
        }

        $scope = $this->getActiveScope();
        if ($scope['is_super_admin']) {
            return;
        }

        if ($scope['company_id'] && $peminjaman->company_id != $scope['company_id']) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses pada dokumen peminjaman perusahaan ini.');
        }

        if (!empty($scope['branch_id']) && $peminjaman->branch_id != $scope['branch_id']) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses pada dokumen peminjaman cabang ini.');
        }
    }

    /**
     * Helper: Resolve or auto-create virtual exhibition warehouse for current company & branch.
     */
    private function resolveGudangPameran($companyId, $branchId): MasterGudang
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $codeSuffix = $branch ? 'CAB-' . $branch->id : 'HO';
        $kodeGudang = 'GDG-PAMERAN-' . $codeSuffix;

        $gudang = MasterGudang::where('company_id', $companyId)
            ->where(function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('cabang_id', $branchId);
                } else {
                    $q->whereNull('cabang_id');
                }
            })
            ->where(function ($q) use ($kodeGudang) {
                $q->where('kode_gudang', $kodeGudang)
                  ->orWhere('nama_gudang', 'like', '%Pameran%');
            })
            ->first();

        if (!$gudang) {
            $namaCabangStr = $branch ? " ({$branch->nama_cabang})" : " (Kantor Pusat)";
            
            // Resolve jenis gudang for current company or fallback to first available
            $jenisGudang = MasterJenisGudang::where('company_id', $companyId)->first()
                ?? MasterJenisGudang::first();

            if (!$jenisGudang) {
                $jenisGudang = MasterJenisGudang::create([
                    'company_id'        => $companyId,
                    'kode_jenis_gudang' => 'JG-PAMERAN-' . substr(strtoupper(uniqid()), -4),
                    'nama_jenis_gudang' => 'Gudang Virtual & Pameran ' . ($companyId ?: '1'),
                    'deskripsi'         => 'Klasifikasi gudang transit virtual untuk peminjaman pameran',
                    'status'            => 'Active',
                ]);
            }

            $gudang = MasterGudang::create([
                'company_id'           => $companyId,
                'cabang_id'            => $branchId ?: null,
                'jenis_gudang_id'      => $jenisGudang->id,
                'kode_gudang'          => $kodeGudang,
                'nama_gudang'          => 'Gudang Virtual Pameran & Event' . $namaCabangStr,
                'status'               => 'Active',
                'keterangan'           => 'Gudang transit virtual penampung persediaan barang pameran/event.',
                'gunakan_lokasi_rak'   => false,
                'izinkan_stok_negatif' => true,
            ]);
        }

        return $gudang;
    }

    /**
     * Helper: Decode and save base64 signature image to storage/uploads with strict security checks.
     */
    private function saveSignatureImage(?string $base64String, string $prefix = 'sig'): ?string
    {
        if (empty($base64String)) {
            return null;
        }

        // Jika sudah berupa path file yang tersimpan, verifikasi ketat dari serangan path traversal (..)
        if (!str_starts_with($base64String, 'data:image')) {
            if (preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $base64String) && !str_contains($base64String, '..')) {
                return $base64String;
            }
            return null;
        }

        try {
            // Validasi ketat format data URI base64
            if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,([A-Za-z0-9+\/=\r\n]+)$/', $base64String, $matches)) {
                Log::warning('Keamanan: Format data URI tanda tangan ditolak karena tidak valid.');
                return null;
            }

            $decodedData = base64_decode($matches[2], true);
            if ($decodedData === false) {
                return null;
            }

            // Batasi ukuran gambar maksimal 2MB untuk mencegah serangan resource exhaustion
            if (strlen($decodedData) > 2 * 1024 * 1024) {
                Log::warning('Keamanan: Ukuran tanda tangan melebihi batas 2MB.');
                return null;
            }

            // Inspeksi binary MIME menggunakan finfo untuk mencegah injeksi script/shell malware
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($decodedData);
            $allowedMimes = ['image/png', 'image/jpeg', 'image/webp'];

            if (!in_array($detectedMime, $allowedMimes, true)) {
                Log::warning("Keamanan: Binary gambar terdeteksi tidak sah: {$detectedMime}");
                return null;
            }

            $folder = public_path('uploads/signatures');
            if (!File::isDirectory($folder)) {
                File::makeDirectory($folder, 0755, true, true);
            }

            // Nama file acak kriptografis (anti-collision & anti-predictable URL)
            $ext = ($detectedMime === 'image/jpeg') ? 'jpg' : (($detectedMime === 'image/webp') ? 'webp' : 'png');
            $fileName = $prefix . '_' . bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;
            $filePath = $folder . DIRECTORY_SEPARATOR . $fileName;

            file_put_contents($filePath, $decodedData);

            return 'uploads/signatures/' . $fileName;
        } catch (\Exception $e) {
            Log::error('Error saving signature image: ' . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $this->authorize('lihat-peminjaman');
        $scope = $this->getActiveScope();
        $isSuperOrOwner = auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Owner']);

        // Summary Counts Calculation Helper (Real-time live sync with single-pass aggregation)
        $calcCounts = function() use ($scope, $isSuperOrOwner) {
            $countQuery = PeminjamanBarang::query();
            if (!$scope['is_super_admin']) {
                if ($scope['company_id']) $countQuery->where('company_id', $scope['company_id']);
                if (!empty($scope['branch_id'])) $countQuery->where('branch_id', $scope['branch_id']);
                else $countQuery->whereNull('branch_id');
            }

            if (!$isSuperOrOwner) {
                $userId = auth()->id();
                $countQuery->where(function ($q) use ($userId) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $userId)
                      ->orWhere('diajukan_oleh', $userId);
                });
            }

            $raw = $countQuery->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'Sedang Dipinjam' THEN 1 ELSE 0 END) as dipinjam,
                SUM(CASE WHEN status IN ('Selesai', 'Selesai (Ada Masalah)') THEN 1 ELSE 0 END) as selesai
            ")->first();

            return [
                'total'    => (int)($raw->total ?? 0),
                'menunggu' => (int)($raw->menunggu ?? 0),
                'dipinjam' => (int)($raw->dipinjam ?? 0),
                'selesai'  => (int)($raw->selesai ?? 0),
            ];
        };

        if ($request->ajax()) {
            $query = PeminjamanBarang::with([
                'company',
                'branch',
                'gudangAsal',
                'gudangPameran',
                'pengaju',
                'pengetahu',
                'penyiap',
                'details.barang'
            ])->orderBy('tanggal_pinjam', 'desc')->orderBy('id', 'desc');

            // Multi-tenant & Branch Scoping
            if (!$scope['is_super_admin']) {
                if ($scope['company_id']) {
                    $query->where('company_id', $scope['company_id']);
                }
                if (!empty($scope['branch_id'])) {
                    $query->where('branch_id', $scope['branch_id']);
                } else {
                    $query->whereNull('branch_id');
                }
            }

            // Filter Privasi Draft: Dokumen berstatus Draft HANYA dapat dilihat oleh pembuat dokumen (created_by / diajukan_oleh)
            if (!$isSuperOrOwner) {
                $userId = auth()->id();
                $query->where(function ($q) use ($userId) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $userId)
                      ->orWhere('diajukan_oleh', $userId);
                });
            }

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tanggal_pinjam', [$request->start_date, $request->end_date]);
            }
            if ($request->filled('gudang_asal_id')) {
                $query->where('gudang_asal_id', $request->gudang_asal_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_peminjaman', fn($row) => $row->nomor_peminjaman)
                ->addColumn('tanggal_pinjam_fmt', fn($row) => $row->tanggal_pinjam ? $row->tanggal_pinjam->format('d/m/Y') : '-')
                ->addColumn('tanggal_kembali_rencana_fmt', fn($row) => $row->tanggal_kembali_rencana ? $row->tanggal_kembali_rencana->format('d/m/Y') : '-')
                ->addColumn('nama_acara', fn($row) => $row->nama_acara)
                ->addColumn('penanggung_jawab', fn($row) => $row->penanggung_jawab . ($row->kontak_pj ? ' (' . $row->kontak_pj . ')' : ''))
                ->addColumn('gudang_asal_nama', fn($row) => $row->gudangAsal ? $row->gudangAsal->nama_gudang : '-')
                ->addColumn('total_item', function ($row) {
                    $sum = (float)$row->details->sum('qty_pinjam');
                    $fmt = (floor($sum) == $sum) ? number_format($sum, 0, ',', '.') : rtrim(rtrim(number_format($sum, 4, ',', '.'), '0'), ',');
                    return $row->details->count() . ' item (' . $fmt . ' unit)';
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'Draft'                 => ['class' => 'badge-status-draft', 'text' => 'Draft', 'icon' => 'pencil-square'],
                        'Menunggu'              => ['class' => 'badge-status-warning', 'text' => 'Menunggu Approval', 'icon' => 'hourglass-split'],
                        'Barang Disiapkan'      => ['class' => 'badge-status-info', 'text' => 'Disiapkan Gudang', 'icon' => 'boxes'],
                        'Barang Siap'           => ['class' => 'badge-status-primary', 'text' => 'Barang Siap Diambil', 'icon' => 'box-seam'],
                        'Sedang Dipinjam'       => ['class' => 'badge-status-purple', 'text' => 'Sedang Dipinjam', 'icon' => 'geo-alt-fill'],
                        'Selesai'               => ['class' => 'badge-status-success', 'text' => 'Selesai (Kembali)', 'icon' => 'check-circle-fill'],
                        'Selesai (Ada Masalah)' => ['class' => 'badge-status-danger', 'text' => 'Ada Masalah', 'icon' => 'exclamation-triangle-fill'],
                        'Ditolak'               => ['class' => 'badge-status-danger', 'text' => 'Ditolak', 'icon' => 'x-circle-fill'],
                    ];
                    $st = $map[$row->status] ?? ['class' => 'badge-status-draft', 'text' => $row->status, 'icon' => 'info-circle'];
                    return '<span class="badge-status-pill ' . $st['class'] . '"><i class="bi bi-' . $st['icon'] . '"></i><span>' . $st['text'] . '</span></span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="d-inline-flex align-items-center justify-content-center gap-1.5 action-pill-container">';
                    
                    // View Detail Button
                    $actions .= '<a href="' . route('peminjaman.show', $row->id) . '" class="btn-action-pill btn-action-view" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail & Tracking">'
                             . '<i class="bi bi-eye"></i>'
                             . '</a>';

                    // Print Surat Jalan Button
                    $actions .= '<a href="' . route('peminjaman.print-surat-jalan', $row->id) . '" target="_blank" class="btn-action-pill btn-action-print" data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Surat Jalan">'
                             . '<i class="bi bi-printer"></i>'
                             . '</a>';
                    
                    // Edit Button
                    if (in_array($row->status, ['Draft', 'Menunggu'])) {
                        $isDraftCreator = ($row->status !== 'Draft') || ($row->created_by == auth()->id()) || ($row->diajukan_oleh == auth()->id()) || auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
                        if ($isDraftCreator && auth()->user()->can('edit-peminjaman')) {
                            $actions .= '<a href="' . route('peminjaman.edit', $row->id) . '" class="btn-action-pill btn-action-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Dokumen">'
                                     . '<i class="bi bi-pencil-square"></i>'
                                     . '</a>';
                        }
                    }

                    // Delete Button
                    if (in_array($row->status, ['Draft', 'Ditolak']) && auth()->user()->can('hapus-peminjaman')) {
                        $isDraftCreator = ($row->status !== 'Draft') || ($row->created_by == auth()->id()) || ($row->diajukan_oleh == auth()->id()) || auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
                        if ($isDraftCreator) {
                            $actions .= '<button type="button" class="btn-action-pill btn-action-delete btn-delete" data-id="' . $row->id . '" data-nomor="' . $row->nomor_peminjaman . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Dokumen">'
                                     . '<i class="bi bi-trash3"></i>'
                                     . '</button>';
                        }
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->with('counts', $calcCounts())
                ->make(true);
        }

        // Summary Counts (Initial View)
        $counts = $calcCounts();

        // Fetch Gudang Asal strictly scoped for filter modal
        $gudangQuery = MasterGudang::where('status', 'Active');
        if ($scope['company_id']) {
            $gudangQuery->where('company_id', $scope['company_id']);
        }
        if (!empty($scope['branch_id'])) {
            $gudangQuery->where('cabang_id', $scope['branch_id']);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang')->get();

        return view('modul_inventori.peminjaman_barang.index', compact('counts', 'gudangs'));
    }

    /**
     * Helper to generate a collision-proof sequential document number.
     * Looks at withTrashed() so that even if previous records were deleted,
     * the sequence always proceeds to the next number without reuse or duplicate collisions.
     * Concurrency safe.
     */
    public function generateNextNomor(): string
    {
        $prefix = 'PB-' . date('Ym') . '-';

        // Find highest sequence from all records (including deleted records)
        // Fixed-width pattern (PB-YYYYMM-XXXX) leverages indexed B-Tree scanning directly
        $latest = PeminjamanBarang::withTrashed()
            ->where('nomor_peminjaman', 'like', $prefix . '%')
            ->orderBy('nomor_peminjaman', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $numPart = substr($latest->nomor_peminjaman, strlen($prefix));
            $nextSeq = intval($numPart) + 1;
        }

        $nomorCandidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        // Safety loop to ensure never colliding even if manual inputs exist
        while (PeminjamanBarang::withTrashed()->where('nomor_peminjaman', $nomorCandidate)->exists()) {
            $nextSeq++;
            $nomorCandidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $nomorCandidate;
    }

    /**
     * AJAX endpoint to fetch real-time fresh auto number.
     */
    public function generateNomorAjax()
    {
        $this->authorize('tambah-peminjaman');
        return response()->json([
            'success' => true,
            'nomor'   => $this->generateNextNomor(),
        ]);
    }

    /**
     * AJAX endpoint to check uniqueness of manual number.
     */
    public function checkNomorUnique(Request $request)
    {
        $this->authorize('lihat-peminjaman');
        $nomor = trim((string)$request->get('nomor', ''));
        $ignoreId = $request->get('ignore_id');

        if (empty($nomor)) {
            return response()->json(['valid' => false, 'message' => 'Nomor peminjaman tidak boleh kosong.']);
        }

        if (!empty($ignoreId) && !is_numeric($ignoreId)) {
            return response()->json(['valid' => false, 'message' => 'Parameter tidak valid.']);
        }

        $query = PeminjamanBarang::withTrashed()->where('nomor_peminjaman', $nomor);
        if ($ignoreId) {
            $query->where('id', '!=', (int)$ignoreId);
        }

        $exists = $query->exists();

        return response()->json([
            'valid'   => !$exists,
            'message' => $exists ? 'Nomor dokumen ini sudah digunakan (termasuk riwayat terhapus).' : 'Nomor dokumen tersedia.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CREATE / INPUT
    // ══════════════════════════════════════════════════════════════
    public function create()
    {
        $this->authorize('tambah-peminjaman');
        $scope = $this->getActiveScope();

        // Generate Nomor Peminjaman: PB-YYYYMM-XXXX (with soft-delete & max sequence support)
        $autoNomor = $this->generateNextNomor();

        // Resolve Gudang Virtual Pameran first so it exists for selection
        $companyId = $scope['company_id'] ?: 1;
        $branchId  = $scope['branch_id'] ?: null;
        $gudangPameran = $this->resolveGudangPameran($companyId, $branchId);

        // Fetch Gudang Asal & Transit strictly scoped
        $gudangQuery = MasterGudang::where('status', 'Active');
        if ($scope['company_id']) {
            $gudangQuery->where('company_id', $scope['company_id']);
        }
        if (!empty($scope['branch_id'])) {
            $gudangQuery->where('cabang_id', $scope['branch_id']);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang')->get();

        // Active user signature
        $userSignature = auth()->user()->activeSignature ?? null;

        return view('modul_inventori.peminjaman_barang.input', compact('autoNomor', 'gudangs', 'gudangPameran', 'userSignature'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $this->authorize('tambah-peminjaman');
        $scope = $this->getActiveScope();

        $isAuto = $request->boolean('is_nomor_otomatis', true);
        $nomorInput = trim($request->input('nomor_peminjaman', ''));

        // If manual mode, validate uniqueness against all records including soft-deleted ones
        if (!$isAuto && !empty($nomorInput)) {
            $duplicate = PeminjamanBarang::withTrashed()->where('nomor_peminjaman', $nomorInput)->exists();
            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor Dokumen Peminjaman "' . $nomorInput . '" sudah pernah digunakan di sistem (termasuk riwayat terhapus). Silakan gunakan nomor lain.',
                ], 422);
            }
        }

        $request->validate([
            'nomor_peminjaman'        => 'required|string|max:50',
            'tanggal_pinjam'          => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'nama_acara'              => 'required|string|max:40',
            'lokasi_acara'            => 'nullable|string|max:50',
            'penanggung_jawab'        => 'nullable|string|max:100',
            'kontak_pj'               => 'nullable|string|max:50',
            'gudang_asal_id'          => 'required|exists:master_gudang,id|different:gudang_pameran_id',
            'gudang_pameran_id'       => 'required|exists:master_gudang,id|different:gudang_asal_id',
            'keterangan'              => 'nullable|string|max:40',
            'items'                   => 'required|array|min:1|max:80',
            'items.*.barang_id'       => 'required|exists:master_barang,id',
            'items.*.qty_pinjam'      => 'required|numeric|min:0.0001',
        ], [
            'nomor_peminjaman.required'             => 'Nomor Dokumen Peminjaman wajib diisi.',
            'nama_acara.required'                   => 'Nama Acara / Pameran wajib diisi.',
            'nama_acara.max'                        => 'Nama Acara / Pameran maksimal 40 karakter.',
            'lokasi_acara.max'                      => 'Lokasi Acara / Stand Booth maksimal 50 karakter.',
            'keterangan.max'                        => 'Keterangan tambahan maksimal 40 karakter.',
            'gudang_asal_id.different'              => 'Gudang Asal Barang dan Gudang Transit Pameran (Tujuan) tidak boleh sama.',
            'gudang_pameran_id.different'           => 'Gudang Transit Pameran (Tujuan) dan Gudang Asal Barang tidak boleh sama.',
            'items.required'                        => 'Minimal tambahkan 1 barang yang akan dipinjam.',
            'items.max'                             => 'Daftar barang yang dipinjam dibatasi maksimal 80 baris.',
            'items.*.qty_pinjam.min'                => 'Jumlah pinjam barang harus lebih dari 0.',
            'tanggal_kembali_rencana.after_or_equal' => 'Tanggal kembali harus sama atau setelah tanggal pinjam.',
        ]);

        // Security Guard: Validasi Kepemilikan Tenant (Anti-IDOR & Cross-Tenant Tampering)
        $gudangAsal = MasterGudang::findOrFail($request->gudang_asal_id);
        if (!$scope['is_super_admin']) {
            if ($scope['company_id'] && $gudangAsal->company_id != $scope['company_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang asal bukan milik perusahaan aktif Anda.'], 403);
            }
            if (!empty($scope['branch_id']) && !empty($gudangAsal->cabang_id) && $gudangAsal->cabang_id != $scope['branch_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang asal bukan milik cabang aktif Anda.'], 403);
            }
        }

        if ($request->filled('gudang_pameran_id')) {
            $gudangPameranCheck = MasterGudang::findOrFail($request->gudang_pameran_id);
            if (!$scope['is_super_admin'] && $scope['company_id'] && $gudangPameranCheck->company_id != $scope['company_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang transit pameran bukan milik perusahaan aktif Anda.'], 403);
            }
        }

        $barangIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        if (!$scope['is_super_admin'] && $scope['company_id']) {
            $invalidItemsCount = MasterBarang::whereIn('id', $barangIds)
                ->where('company_id', '!=', $scope['company_id'])
                ->count();
            if ($invalidItemsCount > 0) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Terdapat barang yang bukan milik entitas perusahaan Anda.'], 403);
            }
        }

        // Guard: Validasi ketersediaan stok fisik di gudang asal
        foreach ($request->items as $item) {
            $stokGudang = StokGudang::where('barang_id', $item['barang_id'])
                ->where('gudang_id', $request->gudang_asal_id)
                ->first();
            $stokTersedia = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;
            $qtyPinjam = floatval($item['qty_pinjam']);

            if ($qtyPinjam > $stokTersedia) {
                $barang = MasterBarang::find($item['barang_id']);
                $namaBarang = $barang ? ($barang->kode_barang . ' - ' . $barang->nama_barang) : 'Barang #' . $item['barang_id'];
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah pinjam untuk {$namaBarang} ({$qtyPinjam}) melebihi stok gudang asal yang tersedia ({$stokTersedia}). Silakan sesuaikan jumlah pinjam.",
                ], 422);
            }
        }

        $companyId = $scope['company_id'] ?: 1;
        $branchId  = $scope['branch_id'] ?: null;
        $gudangPameran = $this->resolveGudangPameran($companyId, $branchId);
        $selectedGudangPameranId = $request->gudang_pameran_id ?: $gudangPameran->id;

        DB::beginTransaction();
        try {
            // Determine final Nomor Peminjaman (with lock against race condition)
            if ($isAuto || empty($nomorInput)) {
                $nomorPeminjaman = $this->generateNextNomor();
            } else {
                $nomorPeminjaman = $nomorInput;
            }

            $statusAwal = $request->input('action') === 'submit' ? 'Menunggu' : 'Draft';

            // Tanda tangan pengaju otomatis diambil dari active signature akun user yang sedang login
            $userSig = auth()->user()->activeSignature 
                ?? \App\Models\UserSignature::where('user_id', auth()->id())->where('is_active', true)->first()
                ?? \App\Models\UserSignature::where('user_id', auth()->id())->latest()->first();
            $signaturePath = $userSig?->signature_path;

            // Penanggung Jawab & Kontak diambil otomatis dari profil akun user
            $pjNama   = $request->filled('penanggung_jawab') ? $request->penanggung_jawab : (auth()->user()->name ?? 'User');
            $pjKontak = $request->filled('kontak_pj') ? $request->kontak_pj : (auth()->user()->no_hp ?? auth()->user()->phone ?? null);

            $peminjaman = PeminjamanBarang::create([
                'company_id'              => $companyId,
                'branch_id'               => $branchId,
                'nomor_peminjaman'        => $nomorPeminjaman,
                'tanggal_pinjam'          => $request->tanggal_pinjam,
                'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
                'nama_acara'              => $request->nama_acara,
                'lokasi_acara'            => $request->lokasi_acara,
                'penanggung_jawab'        => $pjNama,
                'kontak_pj'               => $pjKontak,
                'gudang_asal_id'          => $request->gudang_asal_id,
                'gudang_pameran_id'       => $selectedGudangPameranId,
                'status'                  => $statusAwal,
                'keterangan'              => $request->keterangan,
                'diajukan_oleh'           => auth()->id(),
                'diajukan_at'             => now(),
                'tanda_tangan_pengaju'    => $signaturePath,
            ]);

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                PeminjamanBarangDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'barang_id'     => $item['barang_id'],
                    'satuan_id'     => $barang ? $barang->satuan_id : null,
                    'qty_pinjam'    => $item['qty_pinjam'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'kondisi_awal'  => $item['kondisi_awal'] ?? 'Bagus / Siap Pakai',
                    'catatan'       => $item['catatan'] ?? null,
                ]);
            }

            DB::commit();

            if ($statusAwal === 'Menunggu') {
                WhatsAppGatewayController::runAsync(fn() => 
                    WhatsAppGatewayController::sendPeminjamanSubmissionNotification($peminjaman->id)
                );
            }

            return response()->json([
                'success'      => true,
                'message'      => $statusAwal === 'Menunggu' 
                    ? 'Peminjaman barang berhasil diajukan dan menunggu persetujuan (Mengetahui).' 
                    : 'Draft peminjaman barang berhasil disimpan.',
                'redirect_url' => route('peminjaman.show', $peminjaman->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan peminjaman barang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW / DETAIL
    // ══════════════════════════════════════════════════════════════
    public function show($id)
    {
        $this->authorize('lihat-peminjaman');
        $peminjaman = PeminjamanBarang::with([
            'company',
            'branch',
            'gudangAsal',
            'gudangPameran',
            'pengaju.activeSignature',
            'pengetahu.activeSignature',
            'penyiap.activeSignature',
            'penerimaKembali.activeSignature',
            'details.barang.satuan',
            'details.barang.primaryFoto',
            'details.barang.fotos',
        ])->findOrFail($id);

        $this->authorizeRecord($peminjaman);

        // User active signature (dengan fallback ke tanda tangan aktif / terbaru milik user)
        $userSignature = auth()->user()->activeSignature 
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->where('is_active', true)->first()
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->latest()->first();

        return view('modul_inventori.peminjaman_barang.detail', compact('peminjaman', 'userSignature'));
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════════════════════
    public function edit($id)
    {
        $this->authorize('edit-peminjaman');
        $peminjaman = PeminjamanBarang::with([
            'details.barang.satuan',
            'details.barang.primaryFoto',
            'details.barang.fotos'
        ])->findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if (!in_array($peminjaman->status, ['Draft', 'Menunggu'])) {
            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('error', 'Dokumen tidak dapat diedit karena status sudah ' . $peminjaman->status);
        }

        $scope = $this->getActiveScope();
        $gudangQuery = MasterGudang::where('status', 'Active');
        if ($scope['company_id']) $gudangQuery->where('company_id', $scope['company_id']);
        if (!empty($scope['branch_id'])) $gudangQuery->where('cabang_id', $scope['branch_id']);
        else $gudangQuery->whereNull('cabang_id');
        $gudangs = $gudangQuery->orderBy('nama_gudang')->get();

        $userSignature = auth()->user()->activeSignature ?? null;

        // Fetch current stocks of existing items in gudang_asal_id
        $barangIds = $peminjaman->details->pluck('barang_id')->toArray();
        $stoks = StokGudang::where('gudang_id', $peminjaman->gudang_asal_id)
            ->whereIn('barang_id', $barangIds)
            ->pluck('stok_aktual', 'barang_id')
            ->toArray();

        return view('modul_inventori.peminjaman_barang.edit', compact('peminjaman', 'gudangs', 'userSignature', 'stoks'));
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, $id)
    {
        $this->authorize('edit-peminjaman');
        $peminjaman = PeminjamanBarang::findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if (!in_array($peminjaman->status, ['Draft', 'Menunggu'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat diedit karena sudah berstatus ' . $peminjaman->status,
            ], 422);
        }

        $nomorInput = trim($request->input('nomor_peminjaman', ''));
        if (!empty($nomorInput) && $nomorInput !== $peminjaman->nomor_peminjaman) {
            $duplicate = PeminjamanBarang::withTrashed()
                ->where('nomor_peminjaman', $nomorInput)
                ->where('id', '!=', $peminjaman->id)
                ->exists();
            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor Dokumen Peminjaman "' . $nomorInput . '" sudah pernah digunakan di sistem (termasuk riwayat terhapus). Silakan gunakan nomor lain.',
                ], 422);
            }
        }

        $request->validate([
            'tanggal_pinjam'          => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'nama_acara'              => 'required|string|max:40',
            'lokasi_acara'            => 'nullable|string|max:50',
            'penanggung_jawab'        => 'nullable|string|max:100',
            'kontak_pj'               => 'nullable|string|max:50',
            'gudang_asal_id'          => 'required|exists:master_gudang,id|different:gudang_pameran_id',
            'gudang_pameran_id'       => 'required|exists:master_gudang,id|different:gudang_asal_id',
            'keterangan'              => 'nullable|string|max:40',
            'items'                   => 'required|array|min:1|max:80',
            'items.*.barang_id'       => 'required|exists:master_barang,id',
            'items.*.qty_pinjam'      => 'required|numeric|min:0.0001',
        ], [
            'nama_acara.required'                   => 'Nama Acara / Pameran wajib diisi.',
            'nama_acara.max'                        => 'Nama Acara / Pameran maksimal 40 karakter.',
            'lokasi_acara.max'                      => 'Lokasi Acara / Stand Booth maksimal 50 karakter.',
            'keterangan.max'                        => 'Keterangan tambahan maksimal 40 karakter.',
            'gudang_asal_id.different'              => 'Gudang Asal Barang dan Gudang Transit Pameran (Tujuan) tidak boleh sama.',
            'gudang_pameran_id.different'           => 'Gudang Transit Pameran (Tujuan) dan Gudang Asal Barang tidak boleh sama.',
            'items.required'                        => 'Minimal tambahkan 1 barang yang akan dipinjam.',
            'items.max'                             => 'Daftar barang yang dipinjam dibatasi maksimal 80 baris.',
            'items.*.qty_pinjam.min'                => 'Jumlah pinjam barang harus lebih dari 0.',
            'tanggal_kembali_rencana.after_or_equal' => 'Tanggal kembali harus sama atau setelah tanggal pinjam.',
        ]);

        // Security Guard: Validasi Kepemilikan Tenant (Anti-IDOR & Cross-Tenant Tampering)
        $scope = $this->getActiveScope();
        $gudangAsal = MasterGudang::findOrFail($request->gudang_asal_id);
        if (!$scope['is_super_admin']) {
            if ($scope['company_id'] && $gudangAsal->company_id != $scope['company_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang asal bukan milik perusahaan aktif Anda.'], 403);
            }
            if (!empty($scope['branch_id']) && !empty($gudangAsal->cabang_id) && $gudangAsal->cabang_id != $scope['branch_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang asal bukan milik cabang aktif Anda.'], 403);
            }
        }

        if ($request->filled('gudang_pameran_id')) {
            $gudangPameranCheck = MasterGudang::findOrFail($request->gudang_pameran_id);
            if (!$scope['is_super_admin'] && $scope['company_id'] && $gudangPameranCheck->company_id != $scope['company_id']) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Gudang transit pameran bukan milik perusahaan aktif Anda.'], 403);
            }
        }

        $barangIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        if (!$scope['is_super_admin'] && $scope['company_id']) {
            $invalidItemsCount = MasterBarang::whereIn('id', $barangIds)
                ->where('company_id', '!=', $scope['company_id'])
                ->count();
            if ($invalidItemsCount > 0) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Terdapat barang yang bukan milik entitas perusahaan Anda.'], 403);
            }
        }

        // Guard: Validasi ketersediaan stok fisik di gudang asal
        foreach ($request->items as $item) {
            $stokGudang = StokGudang::where('barang_id', $item['barang_id'])
                ->where('gudang_id', $request->gudang_asal_id)
                ->first();
            $stokTersedia = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;
            $qtyPinjam = floatval($item['qty_pinjam']);

            if ($qtyPinjam > $stokTersedia) {
                $barang = MasterBarang::find($item['barang_id']);
                $namaBarang = $barang ? ($barang->kode_barang . ' - ' . $barang->nama_barang) : 'Barang #' . $item['barang_id'];
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah pinjam untuk {$namaBarang} ({$qtyPinjam}) melebihi stok gudang asal yang tersedia ({$stokTersedia}). Silakan sesuaikan jumlah pinjam.",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Tanda tangan pengaju: pertahankan yang sudah ada, atau ambil dari signature user jika sebelumnya kosong
            $signaturePath = $peminjaman->tanda_tangan_pengaju;
            if (empty($signaturePath)) {
                $userSig = auth()->user()->activeSignature 
                    ?? \App\Models\UserSignature::where('user_id', $peminjaman->diajukan_oleh ?: auth()->id())->where('is_active', true)->first()
                    ?? \App\Models\UserSignature::where('user_id', $peminjaman->diajukan_oleh ?: auth()->id())->latest()->first();
                $signaturePath = $userSig?->signature_path;
            }

            $updateData = [
                'tanggal_pinjam'          => $request->tanggal_pinjam,
                'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
                'nama_acara'              => $request->nama_acara,
                'lokasi_acara'            => $request->lokasi_acara,
                'penanggung_jawab'        => $request->filled('penanggung_jawab') ? $request->penanggung_jawab : ($peminjaman->penanggung_jawab ?: auth()->user()->name),
                'kontak_pj'               => $request->filled('kontak_pj') ? $request->kontak_pj : ($peminjaman->kontak_pj ?? (auth()->user()->no_hp ?? auth()->user()->phone ?? null)),
                'gudang_asal_id'          => $request->gudang_asal_id,
                'gudang_pameran_id'       => $request->gudang_pameran_id,
                'keterangan'              => $request->keterangan,
                'tanda_tangan_pengaju'    => $signaturePath,
            ];
            if (!empty($nomorInput)) {
                $updateData['nomor_peminjaman'] = $nomorInput;
            }

            $peminjaman->update($updateData);

            // Re-sync items
            PeminjamanBarangDetail::where('peminjaman_id', $peminjaman->id)->delete();
            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                PeminjamanBarangDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'barang_id'     => $item['barang_id'],
                    'satuan_id'     => $barang ? $barang->satuan_id : null,
                    'qty_pinjam'    => $item['qty_pinjam'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'kondisi_awal'  => $item['kondisi_awal'] ?? 'Bagus / Siap Pakai',
                    'catatan'       => $item['catatan'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Dokumen peminjaman barang berhasil diperbarui.',
                'redirect_url' => route('peminjaman.show', $peminjaman->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update peminjaman barang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 1: SUBMIT (DRAFT -> MENUNGGU)
    // ══════════════════════════════════════════════════════════════
    public function submit($id)
    {
        $peminjaman = PeminjamanBarang::findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => 'Dokumen bukan status Draft.'], 422);
        }

        $userId = auth()->id();
        $isCreator = ($peminjaman->created_by == $userId) || ($peminjaman->diajukan_oleh == $userId) || auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
        if (!$isCreator) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya pembuat dokumen yang dapat mengajukan draf peminjaman ini.'], 403);
        }

        $peminjaman->update([
            'status'        => 'Menunggu',
            'diajukan_oleh' => auth()->id(),
            'diajukan_at'   => now(),
        ]);

        WhatsAppGatewayController::runAsync(fn() => 
            WhatsAppGatewayController::sendPeminjamanSubmissionNotification($peminjaman->id)
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman berhasil disubmit. Menunggu persetujuan Atasan (Mengetahui).',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 2: APPROVE (MENGETAHUI -> BARANG DISIAPKAN)
    // ══════════════════════════════════════════════════════════════
    public function approve(Request $request, $id)
    {
        $this->authorize('approve-peminjaman');
        $peminjaman = PeminjamanBarang::findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Menunggu') {
            return response()->json(['success' => false, 'message' => 'Status dokumen bukan Menunggu Approval.'], 422);
        }

        // Security Guard: Segregation of Duties (SoD) - Pembuat dilarang meng-approve pengajuan dirinya sendiri kecuali Super Admin/Owner
        $userId = auth()->id();
        $isCreator = ($peminjaman->created_by == $userId) || ($peminjaman->diajukan_oleh == $userId);
        $isSuperOrOwner = auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
        if ($isCreator && !$isSuperOrOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Prinsip Segregation of Duties: Anda tidak dapat menyetujui pengajuan peminjaman barang yang Anda buat sendiri. Persetujuan harus dilakukan oleh atasan atau manager lain.'
            ], 403);
        }

        // Persetujuan 1: Otomatis mengambil tanda tangan resmi dari akun user yang sedang login
        $userSig = auth()->user()->activeSignature 
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->where('is_active', true)->first()
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->latest()->first();
        $signaturePath = $userSig?->signature_path;
        if (!$signaturePath && $request->filled('signature_mengetahui')) {
            $signaturePath = $this->saveSignatureImage($request->signature_mengetahui, 'mengetahui');
        }

        $peminjaman->update([
            'status'                  => 'Barang Disiapkan',
            'diketahui_oleh'          => auth()->id(),
            'diketahui_at'             => now(),
            'tanda_tangan_mengetahui' => $signaturePath,
            'catatan_mengetahui'      => $request->catatan_mengetahui,
        ]);

        $approverUser = auth()->user();
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendPeminjamanApprovedNotification($peminjaman->id, $approverUser));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman berhasil disetujui (Mengetahui). Tiket diteruskan ke tim Gudang untuk disiapkan.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 2B: REJECT (MENOLAK)
    // ══════════════════════════════════════════════════════════════
    public function reject(Request $request, $id)
    {
        $this->authorize('approve-peminjaman');
        $peminjaman = PeminjamanBarang::findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Menunggu') {
            return response()->json(['success' => false, 'message' => 'Status dokumen bukan Menunggu Approval.'], 422);
        }

        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        $peminjaman->update([
            'status'             => 'Ditolak',
            'diketahui_oleh'     => auth()->id(),
            'diketahui_at'        => now(),
            'catatan_mengetahui' => 'DITOLAK: ' . $request->alasan_penolakan,
        ]);

        $rejectorUser = auth()->user();
        $alasanTeks = $request->alasan_penolakan;
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendPeminjamanRejectedNotification($peminjaman->id, $alasanTeks, $rejectorUser));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman barang telah ditolak.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 3: BARANG SIAP (GUDANG SELESAI MENYIAPKAN)
    // ══════════════════════════════════════════════════════════════
    public function barangSiap(Request $request, $id)
    {
        $this->authorize('siapkan-peminjaman');
        $peminjaman = PeminjamanBarang::with('details')->findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Barang Disiapkan') {
            return response()->json(['success' => false, 'message' => 'Status dokumen bukan Barang Disiapkan.'], 422);
        }

        // Otomatis mengambil tanda tangan akun user yang sedang login
        $userSig = auth()->user()->activeSignature 
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->where('is_active', true)->first()
            ?? \App\Models\UserSignature::where('user_id', auth()->id())->latest()->first();
        $signaturePath = $userSig?->signature_path;
        if (!$signaturePath && $request->filled('signature_menyiapkan')) {
            $signaturePath = $this->saveSignatureImage($request->signature_menyiapkan, 'menyiapkan');
        }

        // Update serial numbers if provided by warehouse staff
        $serials = $request->input('serial_numbers', $request->input('serials', []));
        if (!empty($serials) && is_array($serials)) {
            foreach ($serials as $detailId => $serial) {
                PeminjamanBarangDetail::where('id', $detailId)
                    ->where('peminjaman_id', $peminjaman->id)
                    ->update(['serial_number' => $serial ?: null]);
            }
        }

        $peminjaman->update([
            'status'                  => 'Barang Siap',
            'disiapkan_oleh'          => auth()->id(),
            'disiapkan_at'            => now(),
            'tanda_tangan_menyiapkan' => $signaturePath,
        ]);

        $penyiapUser = auth()->user();
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendPeminjamanBarangSiapNotification($peminjaman->id, $penyiapUser));

        return response()->json([
            'success' => true,
            'message' => 'Barang telah selesai disiapkan dan dipacking oleh tim Gudang. Siap diserahkan ke pengaju!',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 4: SERAH TERIMA & BARANG KELUAR (SEDANG DIPINJAM)
    // * STOK POSTING: Gudang Asal -> Gudang Pameran Virtual *
    // ══════════════════════════════════════════════════════════════
    public function serahTerima(Request $request, $id)
    {
        if (!auth()->user()->can('serah-terima-peminjaman') && !auth()->user()->can('siapkan-peminjaman')) {
            abort(403, 'Anda tidak memiliki hak akses untuk serah terima dan pengeluaran barang peminjaman.');
        }
        $peminjaman = PeminjamanBarang::with(['details.barang', 'gudangAsal', 'gudangPameran'])->findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Barang Siap') {
            return response()->json(['success' => false, 'message' => 'Status barang belum berstatus Barang Siap.'], 422);
        }

        DB::beginTransaction();
        try {
            $gudangAsalId    = $peminjaman->gudang_asal_id;
            $gudangPameranId = $peminjaman->gudang_pameran_id;

            // 1. Cek kecukupan stok di Gudang Asal dengan pessimistic locking
            foreach ($peminjaman->details as $detail) {
                $stokGudangAsal = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $gudangAsalId)
                    ->lockForUpdate()
                    ->first();

                $stokAktualAsal = $stokGudangAsal ? (string)$stokGudangAsal->stok_aktual : '0.0000';
                $qtyPinjamStr   = (string)$detail->qty_pinjam;
                if (DecimalHelper::comp($stokAktualAsal, $qtyPinjamStr, 4) < 0) {
                    $namaBarang = $detail->barang ? $detail->barang->nama_barang : "Barang ID #{$detail->barang_id}";
                    throw new \Exception("Stok tidak mencukupi untuk '{$namaBarang}'. Tersedia: {$stokAktualAsal}, Diminta: {$qtyPinjamStr}.");
                }
            }

            // 2. Eksekusi perpindahan stok (Gudang Asal -> Gudang Pameran)
            foreach ($peminjaman->details as $detail) {
                // Potong Stok di Gudang Asal
                $stokAsal = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $gudangAsalId)
                    ->lockForUpdate()
                    ->first();

                if (!$stokAsal) {
                    $stokAsal = StokGudang::create([
                        'barang_id'             => $detail->barang_id,
                        'gudang_id'             => $gudangAsalId,
                        'stok_aktual'           => '0.0000',
                        'nilai_persediaan'      => '0.0000',
                        'harga_pokok_rata_rata' => '0.000000',
                    ]);
                    $stokAsal = StokGudang::where('id', $stokAsal->id)->lockForUpdate()->first();
                }

                $stokAwalAsalStr  = (string)($stokAsal->stok_aktual ?? '0.0000');
                $avgCostAsalStr   = (string)($stokAsal->harga_pokok_rata_rata ?? '0.000000');
                $qtyStr           = (string)$detail->qty_pinjam;

                $nilaiKeluarStr   = DecimalHelper::mul($qtyStr, $avgCostAsalStr, 4);
                $stokAkhirAsalStr = DecimalHelper::sub($stokAwalAsalStr, $qtyStr, 4);
                if (DecimalHelper::comp($stokAkhirAsalStr, '0', 4) < 0) {
                    $stokAkhirAsalStr = '0.0000';
                }

                $nilaiAwalAsalStr  = (string)($stokAsal->nilai_persediaan ?? DecimalHelper::mul($stokAwalAsalStr, $avgCostAsalStr, 4));
                $nilaiAkhirAsalStr = DecimalHelper::sub($nilaiAwalAsalStr, $nilaiKeluarStr, 4);
                if (DecimalHelper::comp($nilaiAkhirAsalStr, '0', 4) < 0) {
                    $nilaiAkhirAsalStr = '0.0000';
                }

                $stokAsal->stok_aktual      = $stokAkhirAsalStr;
                $stokAsal->nilai_persediaan = $nilaiAkhirAsalStr;
                $stokAsal->save();

                // Catat Kartu Stok Gudang Asal (Keluar ke Pameran)
                KartuStok::create([
                    'barang_id'       => $detail->barang_id,
                    'gudang_id'       => $gudangAsalId,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $qtyStr,
                    'stok_awal'       => $stokAwalAsalStr,
                    'stok_akhir'      => $stokAkhirAsalStr,
                    'harga_satuan'    => $avgCostAsalStr,
                    'nilai_total'     => $nilaiKeluarStr,
                    'referensi_jenis' => 'Peminjaman Pameran (Keluar)',
                    'referensi_id'    => $peminjaman->id,
                    'referensi_nomor' => $peminjaman->nomor_peminjaman,
                    'keterangan'      => "Peminjaman Acara {$peminjaman->nama_acara} - PJ: {$peminjaman->penanggung_jawab}",
                ]);

                // Tambah Stok di Gudang Pameran Virtual
                $stokPameran = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $gudangPameranId)
                    ->lockForUpdate()
                    ->first();

                if (!$stokPameran) {
                    $stokPameran = StokGudang::create([
                        'barang_id'             => $detail->barang_id,
                        'gudang_id'             => $gudangPameranId,
                        'stok_aktual'           => '0.0000',
                        'nilai_persediaan'      => '0.0000',
                        'harga_pokok_rata_rata' => $avgCostAsalStr,
                    ]);
                    $stokPameran = StokGudang::where('id', $stokPameran->id)->lockForUpdate()->first();
                }

                $stokAwalPameranStr  = (string)($stokPameran->stok_aktual ?? '0.0000');
                $stokAkhirPameranStr = DecimalHelper::add($stokAwalPameranStr, $qtyStr, 4);

                $nilaiAwalPameranStr  = (string)($stokPameran->nilai_persediaan ?? DecimalHelper::mul($stokAwalPameranStr, (string)($stokPameran->harga_pokok_rata_rata ?? $avgCostAsalStr), 4));
                $nilaiAkhirPameranStr = DecimalHelper::add($nilaiAwalPameranStr, $nilaiKeluarStr, 4);

                $stokPameran->stok_aktual           = $stokAkhirPameranStr;
                $stokPameran->nilai_persediaan      = $nilaiAkhirPameranStr;
                $stokPameran->harga_pokok_rata_rata = $avgCostAsalStr;
                $stokPameran->save();

                // Catat Kartu Stok Gudang Pameran (Masuk dari Gudang Asal)
                KartuStok::create([
                    'barang_id'       => $detail->barang_id,
                    'gudang_id'       => $gudangPameranId,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qtyStr,
                    'stok_awal'       => $stokAwalPameranStr,
                    'stok_akhir'      => $stokAkhirPameranStr,
                    'harga_satuan'    => $avgCostAsalStr,
                    'nilai_total'     => $nilaiKeluarStr,
                    'referensi_jenis' => 'Peminjaman Pameran (Masuk Stand)',
                    'referensi_id'    => $peminjaman->id,
                    'referensi_nomor' => $peminjaman->nomor_peminjaman,
                    'keterangan'      => "Masuk ke Stand Pameran {$peminjaman->nama_acara} dari Gudang {$peminjaman->gudangAsal->nama_gudang}",
                ]);
            }

            $peminjaman->update([
                'status'        => 'Sedang Dipinjam',
                'diserahkan_at' => now(),
            ]);

            DB::commit();

            $serahTerimaUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendPeminjamanSerahTerimaNotification($peminjaman->id, $serahTerimaUser));

            return response()->json([
                'success' => true,
                'message' => 'Serah terima barang berhasil! Status berubah menjadi Sedang Dipinjam & stok telah dipindahkan ke Gudang Pameran.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal serah terima peminjaman barang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses serah terima: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW 5: PENGEMBALIAN BARANG (RETURN IN & QC INSPECTION)
    // * STOK POSTING BALIK: Gudang Pameran -> Gudang Asal *
    // ══════════════════════════════════════════════════════════════
    public function kembalikan(Request $request, $id)
    {
        $this->authorize('kembalikan-peminjaman');
        $peminjaman = PeminjamanBarang::with(['details.barang', 'gudangAsal', 'gudangPameran'])->findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if ($peminjaman->status !== 'Sedang Dipinjam') {
            return response()->json(['success' => false, 'message' => 'Status peminjaman bukan Sedang Dipinjam.'], 422);
        }

        $request->validate([
            'returns'                       => 'required|array|min:1',
            'returns.*.qty_kembali_bagus'   => 'required|numeric|min:0',
            'returns.*.qty_kembali_rusak'   => 'required|numeric|min:0',
            'returns.*.qty_hilang'          => 'required|numeric|min:0',
            'catatan_pengembalian'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $gudangAsalId    = $peminjaman->gudang_asal_id;
            $gudangPameranId = $peminjaman->gudang_pameran_id;
            $adaMasalah      = false;

            // Handle Signature Penerima Kembali (Gudang QC - Otomatis Akun User)
            $userSig = auth()->user()->activeSignature 
                ?? \App\Models\UserSignature::where('user_id', auth()->id())->where('is_active', true)->first()
                ?? \App\Models\UserSignature::where('user_id', auth()->id())->latest()->first();
            $signaturePath = $userSig?->signature_path;
            if (!$signaturePath && $request->filled('signature_penerima')) {
                $signaturePath = $this->saveSignatureImage($request->signature_penerima, 'return');
            }

            foreach ($peminjaman->details as $detail) {
                $ret = $request->returns[$detail->id] ?? null;
                if (!$ret) {
                    $namaBarang = $detail->barang ? $detail->barang->nama_barang : "Barang ID #{$detail->barang_id}";
                    throw new \Exception("Data inspeksi pengembalian untuk '{$namaBarang}' belum diisi lengkap.");
                }

                $qtyBagusStr   = DecimalHelper::str($ret['qty_kembali_bagus'] ?? 0);
                $qtyRusakStr   = DecimalHelper::str($ret['qty_kembali_rusak'] ?? 0);
                $qtyHilangStr  = DecimalHelper::str($ret['qty_hilang'] ?? 0);
                $kondisiAkhir  = $ret['kondisi_akhir'] ?? null;

                $totalKembaliStr = DecimalHelper::add(DecimalHelper::add($qtyBagusStr, $qtyRusakStr, 4), $qtyHilangStr, 4);
                $totalPinjamStr  = DecimalHelper::str($detail->qty_pinjam);

                if (DecimalHelper::comp($totalKembaliStr, $totalPinjamStr, 4) !== 0) {
                    $namaBarang = $detail->barang ? $detail->barang->nama_barang : "Barang ID #{$detail->barang_id}";
                    throw new \Exception("Total pengembalian (Bagus + Rusak + Hilang) untuk '{$namaBarang}' harus sama dengan Qty Pinjam ({$totalPinjamStr}). Total diinput: {$totalKembaliStr}.");
                }

                if (DecimalHelper::comp($qtyRusakStr, '0', 4) > 0 || DecimalHelper::comp($qtyHilangStr, '0', 4) > 0) {
                    $adaMasalah = true;
                }

                // Update detail record
                $detail->update([
                    'qty_kembali_bagus' => $qtyBagusStr,
                    'qty_kembali_rusak' => $qtyRusakStr,
                    'qty_hilang'        => $qtyHilangStr,
                    'kondisi_akhir'     => $kondisiAkhir,
                ]);

                // ── 1. KURANGI STOK DARI GUDANG PAMERAN VIRTUAL (Total Pinjam) ──
                $stokPameran = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $gudangPameranId)
                    ->lockForUpdate()
                    ->first();

                if (!$stokPameran) {
                    $stokPameran = StokGudang::create([
                        'barang_id'             => $detail->barang_id,
                        'gudang_id'             => $gudangPameranId,
                        'stok_aktual'           => '0.0000',
                        'nilai_persediaan'      => '0.0000',
                        'harga_pokok_rata_rata' => '0.000000',
                    ]);
                    $stokPameran = StokGudang::where('id', $stokPameran->id)->lockForUpdate()->first();
                }

                $stokAwalPameranStr  = (string)($stokPameran->stok_aktual ?? '0.0000');
                $stokAkhirPameranStr = DecimalHelper::sub($stokAwalPameranStr, $totalPinjamStr, 4);
                if (DecimalHelper::comp($stokAkhirPameranStr, '0', 4) < 0) {
                    $stokAkhirPameranStr = '0.0000';
                }

                $avgCostPameranStr     = (string)($stokPameran->harga_pokok_rata_rata ?? '0.000000');
                $nilaiKeluarPameranStr = DecimalHelper::mul($totalPinjamStr, $avgCostPameranStr, 4);

                $nilaiAwalPameranStr  = (string)($stokPameran->nilai_persediaan ?? DecimalHelper::mul($stokAwalPameranStr, $avgCostPameranStr, 4));
                $nilaiAkhirPameranStr = DecimalHelper::sub($nilaiAwalPameranStr, $nilaiKeluarPameranStr, 4);
                if (DecimalHelper::comp($nilaiAkhirPameranStr, '0', 4) < 0) {
                    $nilaiAkhirPameranStr = '0.0000';
                }

                $stokPameran->stok_aktual      = $stokAkhirPameranStr;
                $stokPameran->nilai_persediaan = $nilaiAkhirPameranStr;
                $stokPameran->save();

                KartuStok::create([
                    'barang_id'       => $detail->barang_id,
                    'gudang_id'       => $gudangPameranId,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $totalPinjamStr,
                    'stok_awal'       => $stokAwalPameranStr,
                    'stok_akhir'      => $stokAkhirPameranStr,
                    'harga_satuan'    => $avgCostPameranStr,
                    'nilai_total'     => $nilaiKeluarPameranStr,
                    'referensi_jenis' => 'Pengembalian Pameran (Keluar Stand)',
                    'referensi_id'    => $peminjaman->id,
                    'referensi_nomor' => $peminjaman->nomor_peminjaman,
                    'keterangan'      => "Pameran Selesai - Kembali Bagus: {$qtyBagusStr}, Rusak: {$qtyRusakStr}, Hilang: {$qtyHilangStr}",
                ]);

                // ── 2. TAMBAH STOK KEMBALI KE GUDANG ASAL (HANYA YANG KONDISI BAGUS) ──
                if (DecimalHelper::comp($qtyBagusStr, '0', 4) > 0) {
                    $stokAsal = StokGudang::where('barang_id', $detail->barang_id)
                        ->where('gudang_id', $gudangAsalId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stokAsal) {
                        $stokAsal = StokGudang::create([
                            'barang_id'             => $detail->barang_id,
                            'gudang_id'             => $gudangAsalId,
                            'stok_aktual'           => '0.0000',
                            'nilai_persediaan'      => '0.0000',
                            'harga_pokok_rata_rata' => $avgCostPameranStr,
                        ]);
                        $stokAsal = StokGudang::where('id', $stokAsal->id)->lockForUpdate()->first();
                    }

                    $stokAwalAsalStr  = (string)($stokAsal->stok_aktual ?? '0.0000');
                    $stokAkhirAsalStr = DecimalHelper::add($stokAwalAsalStr, $qtyBagusStr, 4);

                    $avgCostAsalStr   = (string)($stokAsal->harga_pokok_rata_rata ?? $avgCostPameranStr);
                    $nilaiMasukAsalStr = DecimalHelper::mul($qtyBagusStr, $avgCostAsalStr, 4);

                    $nilaiAwalAsalStr  = (string)($stokAsal->nilai_persediaan ?? DecimalHelper::mul($stokAwalAsalStr, $avgCostAsalStr, 4));
                    $nilaiAkhirAsalStr = DecimalHelper::add($nilaiAwalAsalStr, $nilaiMasukAsalStr, 4);

                    $stokAsal->stok_aktual      = $stokAkhirAsalStr;
                    $stokAsal->nilai_persediaan = $nilaiAkhirAsalStr;
                    $stokAsal->save();

                    KartuStok::create([
                        'barang_id'       => $detail->barang_id,
                        'gudang_id'       => $gudangAsalId,
                        'tipe'            => 'Masuk',
                        'jumlah'          => $qtyBagusStr,
                        'stok_awal'       => $stokAwalAsalStr,
                        'stok_akhir'      => $stokAkhirAsalStr,
                        'harga_satuan'    => $avgCostAsalStr,
                        'nilai_total'     => $nilaiMasukAsalStr,
                        'referensi_jenis' => 'Pengembalian Pameran (Kembali Gudang)',
                        'referensi_id'    => $peminjaman->id,
                        'referensi_nomor' => $peminjaman->nomor_peminjaman,
                        'keterangan'      => "Pengembalian barang pameran dalam kondisi baik dari {$peminjaman->nama_acara}",
                    ]);
                }
            }

            $finalStatus = $adaMasalah ? 'Selesai (Ada Masalah)' : 'Selesai';

            $peminjaman->update([
                'status'                 => $finalStatus,
                'tanggal_kembali_aktual' => now(),
                'diterima_kembali_oleh'  => auth()->id(),
                'diterima_kembali_at'    => now(),
                'tanda_tangan_penerima'  => $signaturePath,
                'catatan_pengembalian'   => $request->catatan_pengembalian,
            ]);

            DB::commit();

            $penerimaUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendPeminjamanReturnedNotification($peminjaman->id, $penerimaUser));

            return response()->json([
                'success' => true,
                'message' => $adaMasalah 
                    ? 'Pengembalian selesai dengan catatan barang rusak/hilang. Stok kondisi bagus telah dikembalikan ke Gudang Asal & notifikasi WA telah terkirim.'
                    : 'Semua barang telah kembali utuh dan stok telah dimasukkan kembali ke Gudang Asal! Notifikasi WA telah terkirim.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal proses pengembalian peminjaman: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY / HAPUS
    // ══════════════════════════════════════════════════════════════
    public function destroy($id)
    {
        $this->authorize('hapus-peminjaman');
        $peminjaman = PeminjamanBarang::findOrFail($id);
        $this->authorizeRecord($peminjaman);

        if (!in_array($peminjaman->status, ['Draft', 'Ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokumen berstatus Draft atau Ditolak yang dapat dihapus.',
            ], 422);
        }

        $peminjaman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen peminjaman barang berhasil dihapus.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // API: GET BARANGS BY GUDANG
    // ══════════════════════════════════════════════════════════════
    public function getBarangsByGudang(Request $request, $gudangId)
    {
        $this->authorize('lihat-peminjaman');
        $gudang = MasterGudang::findOrFail($gudangId);
        $scope  = $this->getActiveScope();

        // Validasi Multi-Tenant IDOR: Cegah user membocorkan barang gudang PT/Cabang lain
        if (!$scope['is_super_admin']) {
            if ($scope['company_id'] && $gudang->company_id != $scope['company_id']) {
                abort(403, 'Akses ditolak: Gudang bukan milik entitas perusahaan Anda.');
            }
            if (!empty($scope['branch_id']) && !empty($gudang->cabang_id) && $gudang->cabang_id != $scope['branch_id']) {
                abort(403, 'Akses ditolak: Gudang bukan milik cabang aktif Anda.');
            }
        }

        $query = MasterBarang::where('status', 'Active')
            ->with(['satuan', 'kategori', 'primaryFoto', 'fotos']);

        if ($scope['company_id']) {
            $query->where('company_id', $scope['company_id']);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nama_barang', 'like', "%{$q}%")
                  ->orWhere('kode_barang', 'like', "%{$q}%");
            });
        }

        $barangs = $query->limit(200)->get();

        // Attach current stock in this warehouse
        $barangIds = $barangs->pluck('id')->toArray();
        $stoks = StokGudang::where('gudang_id', $gudangId)
            ->whereIn('barang_id', $barangIds)
            ->pluck('stok_aktual', 'barang_id')
            ->toArray();

        $results = $barangs->map(function ($item) use ($stoks) {
            $stokAktual = floatval($stoks[$item->id] ?? 0);
            
            // Resolve primary photo or first gallery photo
            $fotoPath = $item->primaryFoto?->foto_path ?? $item->fotos?->first()?->foto_path;
            $fotoUrl = null;
            if (!empty($fotoPath)) {
                if (str_starts_with($fotoPath, 'http')) {
                    $fotoUrl = $fotoPath;
                } elseif (str_starts_with($fotoPath, 'uploads/')) {
                    $fotoUrl = asset($fotoPath);
                } else {
                    $fotoUrl = asset('storage/' . ltrim($fotoPath, '/'));
                }
            }

            return [
                'id'          => $item->id,
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,
                'kategori'    => $item->kategori ? $item->kategori->nama_kategori : '-',
                'satuan_id'   => $item->satuan_id,
                'satuan'      => $item->satuan ? $item->satuan->nama_satuan : 'Unit',
                'stok'        => $stokAktual,
                'foto_url'    => $fotoUrl,
                'text'        => "{$item->kode_barang} - {$item->nama_barang}",
            ];
        });

        return response()->json($results);
    }

    // ══════════════════════════════════════════════════════════════
    // PRINT SURAT JALAN PEMINJAMAN BARANG
    // ══════════════════════════════════════════════════════════════
    public function printSuratJalan($id)
    {
        $this->authorize('lihat-peminjaman');

        $peminjaman = PeminjamanBarang::with([
            'company',
            'branch',
            'gudangAsal',
            'gudangPameran',
            'pengaju',
            'pengetahu',
            'penyiap',
            'penerimaKembali',
            'details.barang.satuan',
            'details.barang.kategori',
            'details.satuan',
        ])->findOrFail($id);

        $this->authorizeRecord($peminjaman);

        $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
        if (!empty($appLogoSetting) && file_exists(public_path('storage/' . $appLogoSetting))) {
            $appLogo = asset('storage/' . $appLogoSetting);
        } elseif (!empty($appLogoSetting) && file_exists(public_path($appLogoSetting))) {
            $appLogo = asset($appLogoSetting);
        } else {
            $appLogo = asset('gambar/logo.png');
        }

        return view('modul_inventori.peminjaman_barang.print_surat_jalan', compact('peminjaman', 'appLogo', 'appLogoSetting'));
    }
}
