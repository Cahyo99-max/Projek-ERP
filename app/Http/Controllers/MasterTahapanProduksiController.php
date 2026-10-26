<?php

namespace App\Http\Controllers;

use App\Models\MasterTahapanProduksi;
use App\Models\Bom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class MasterTahapanProduksiController extends Controller
{
    /**
     * Index — tampilkan halaman master tahapan (dengan DataTables).
     */
    public function index(Request $request)
    {
        $this->authorize('lihat-master-tahapan');

        // AJAX DataTables request (Grouped per Resep BOM / Template Umum)
        if ($request->ajax()) {
            $query = MasterTahapanProduksi::forActiveCompany()
                ->with(['bom.barangJadi:id,nama_barang,kode_barang'])
                ->select(['id', 'company_id', 'branch_id', 'bom_id', 'nama_tahapan', 'deskripsi', 'foto', 'urutan', 'is_active', 'created_at', 'updated_at'])
                ->orderBy('urutan', 'asc');

            // Push filter langsung ke level query SQL (Hemat RAM & Skala Jutaan Data)
            if ($request->filled('filter_bom_id')) {
                $filterVal = (string) $request->filter_bom_id;
                if ($filterVal === 'general') {
                    $query->whereNull('bom_id');
                } else {
                    $query->where('bom_id', $filterVal);
                }
            }

            $allTahapans = $query->get();

            // Group by bom_id (null => 'general')
            $grouped = $allTahapans->groupBy(function ($item) {
                return $item->bom_id ? (string) $item->bom_id : 'general';
            });

            // Urutkan grup alur tahapan berdasarkan yang terakhir dibuat (created_at desc / id desc)
            $sortedGrouped = $grouped->sortByDesc(function ($items) {
                $maxCreated = $items->max('created_at');
                return $maxCreated ? $maxCreated->timestamp : $items->max('id');
            });

            $data = [];
            $no = 1;
            foreach ($sortedGrouped as $bomKey => $items) {
                $items = $items->sortBy('urutan')->values();
                $first = $items->first();
                $bom = $first->bom;
                $total = $items->count();

                $isAllActive = $items->every(fn($i) => (bool)$i->is_active);
                $isNoneActive = $items->every(fn($i) => !(bool)$i->is_active);

                $data[] = [
                    'DT_RowIndex'   => $no++,
                    'bom_key'       => (string) $bomKey,
                    'bom_id'        => $bom ? $bom->id : null,
                    'bom_nama'      => $bom ? $bom->nama_bom : 'Template Umum (Standar Semua BOM)',
                    'bom_kode'      => $bom ? $bom->kode_bom : 'STANDAR',
                    'barang_nama'   => $bom && $bom->barangJadi ? $bom->barangJadi->nama_barang : null,
                    'total_tahapan' => $total,
                    'items'         => $items->values(),
                    'is_active'     => $isAllActive ? true : ($isNoneActive ? false : 'mixed'),
                ];
            }

            return DataTables::of(collect($data))
                ->addColumn('bom_info', function ($row) {
                    if ($row['bom_id']) {
                        return '<div class="d-flex flex-column gap-1.5 py-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #1d4ed8, #2563eb); font-size: 12.5px; border: 1px solid rgba(255,255,255,0.25);">
                                            <i class="bi bi-diagram-2-fill me-1.5 text-white"></i>' . e($row['bom_nama']) . '
                                        </span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-semibold spk-text-main" style="background: var(--spk-bg-tertiary, #f1f5f9); border: 1px solid var(--spk-border-color, #cbd5e1); font-size: 11px;">
                                            ' . e($row['bom_kode']) . '
                                        </span>
                                    </div>
                                    ' . ($row['barang_nama'] ? '<div class="small mt-1 spk-text-muted" style="font-size: 12px;"><i class="bi bi-box-seam me-1 text-primary"></i>Produk: <strong class="spk-text-main fw-bold">' . e($row['barang_nama']) . '</strong></div>' : '') . '
                                </div>';
                    }
                    return '<div class="d-flex flex-column gap-1 py-1">
                                <div>
                                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #475569, #334155); font-size: 12px; border: 1px solid rgba(255,255,255,0.2);">
                                        <i class="bi bi-layers-fill me-1.5 text-white"></i>Template Umum (Semua BOM)
                                    </span>
                                </div>
                                <small class="spk-text-muted mt-0.5" style="font-size: 11px;">Alur standar umum untuk SPK yang tidak memiliki resep BOM khusus</small>
                            </div>';
                })
                ->addColumn('preview_alur', function ($row) {
                    $items = $row['items'];
                    $total = $row['total_tahapan'];

                    $html = '<div class="d-flex flex-column gap-2 py-1">';
                    $html .= '<div class="d-flex align-items-center gap-2 flex-wrap">';
                    $html .= '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #0284c7, #0ea5e9); font-size: 12px; border: 1px solid rgba(255,255,255,0.25);">
                                <i class="bi bi-list-check me-1.5 text-white"></i>' . $total . ' Tahapan Proses
                              </span>';

                    // Mini photo avatars
                    $photoCount = 0;
                    $photoHtml = '<div class="d-flex align-items-center gap-1.5 ms-1">';
                    foreach ($items as $item) {
                        if ($item->foto && $photoCount < 4) {
                            $url = asset('storage/' . $item->foto);
                            $photoHtml .= '<img src="' . $url . '" alt="' . e($item->nama_tahapan) . '" 
                                                class="rounded-3 border shadow-xs cursor-pointer img-thumbnail-tahapan" 
                                                style="width: 34px; height: 34px; object-fit: cover; border-color: var(--spk-border-color, #cbd5e1) !important;" 
                                                onclick="previewFotoTahapan(\'' . $url . '\', \'' . e(addslashes($item->nama_tahapan)) . '\')" 
                                                title="Tahap ' . $item->urutan . ': ' . e($item->nama_tahapan) . '">';
                            $photoCount++;
                        }
                    }
                    $photoHtml .= '</div>';
                    if ($photoCount > 0) {
                        $html .= $photoHtml;
                    }
                    $html .= '</div>';

                    // Stepper sequence summary
                    $stepNames = [];
                    $slice = $items->slice(0, 3);
                    foreach ($slice as $s) {
                        $stepNames[] = '<span class="spk-text-muted">' . $s->urutan . '. <strong class="spk-text-main fw-bold">' . e($s->nama_tahapan) . '</strong></span>';
                    }
                    if ($total > 3) {
                        $stepNames[] = '<span class="text-warning fw-semibold fst-italic">+' . ($total - 3) . ' lainnya...</span>';
                    }
                    $html .= '<div class="small spk-text-muted text-truncate" style="max-width: 440px; font-size: 12px;">' . implode(' <i class="bi bi-arrow-right text-primary mx-1" style="font-size: 11px;"></i> ', $stepNames) . '</div>';
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('status', function ($row) {
                    if ($row['is_active'] === true) {
                        return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" style="background: #10b981; border: 1px solid rgba(255,255,255,0.25); font-size: 11.5px;"><i class="bi bi-check-circle-fill me-1 text-white"></i>Aktif</span>';
                    } elseif ($row['is_active'] === false) {
                        return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" style="background: #ef4444; border: 1px solid rgba(255,255,255,0.25); font-size: 11.5px;"><i class="bi bi-x-circle-fill me-1 text-white"></i>Nonaktif</span>';
                    }
                    return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-dark shadow-xs" style="background: #f59e0b; border: 1px solid rgba(0,0,0,0.2); font-size: 11.5px;"><i class="bi bi-dash-circle-fill me-1"></i>Sebagian Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $canEdit   = auth()->user()->can('edit-master-tahapan');
                    $canDelete = auth()->user()->can('hapus-master-tahapan');
                    $bomKey    = $row['bom_key'];
                    $bomNamaSafe = e(addslashes($row['bom_nama']));

                    $btnDetail = "<button class='btn btn-sm btn-outline-primary rounded-pill px-2.5 me-1.5 shadow-xs' onclick='bukaDetailAlurBom(\"{$bomKey}\")' title='Lihat Seluruh Tahapan'><i class='bi bi-eye-fill me-1'></i>Detail</button>";
                    $btnEdit   = $canEdit ? "<button class='btn btn-sm btn-outline-warning rounded-pill px-2.5 me-1.5 shadow-xs' onclick='editAlurBom(\"{$bomKey}\")' title='Edit Alur BOM'><i class='bi bi-pencil'></i></button>" : '';
                    $btnDelete = $canDelete ? "<button class='btn btn-sm btn-outline-danger rounded-pill px-2.5 shadow-xs' onclick='hapusAlurBom(\"{$bomKey}\", \"{$bomNamaSafe}\")' title='Hapus Alur BOM'><i class='bi bi-trash'></i></button>" : '';

                    return "<div class='d-flex align-items-center justify-content-center'>{$btnDetail}{$btnEdit}{$btnDelete}</div>";
                })
                ->rawColumns(['bom_info', 'preview_alur', 'status', 'action'])
                ->make(true);
        }

        $activeBranchId    = session('active_branch_id');
        $activeBranchName  = session('active_branch_name');
        $activeCompanyName = session('active_company_name') ?: 'PT';
        $isCabang          = !empty($activeBranchId);
        $companyId         = session('active_company_id');

        // Untuk dropdown filter di halaman index:
        // Ambil SEMUA BOM yang alur tahapannya sedang tampil di tabel untuk company/branch aktif
        $bomIdsInTahapan = MasterTahapanProduksi::forActiveCompany()
            ->whereNotNull('bom_id')
            ->distinct()
            ->pluck('bom_id');

        $bomsQuery = Bom::forActiveCompany()
            ->with(['barangJadi:id,nama_barang,kode_barang'])
            ->whereIn('id', $bomIdsInTahapan)
            ->orderBy('nama_bom', 'asc');

        $boms = $bomsQuery->get(['id', 'kode_bom', 'nama_bom', 'barang_jadi_id', 'status', 'branch_id']);

        return view('modul_produksi.master_tahapan.index', compact(
            'boms', 'activeBranchId', 'activeBranchName', 'activeCompanyName', 'isCabang'
        ));
    }

    /**
     * Search Approved BOM via AJAX for Modal Lookup (Enterprise Scale / Millions of Records).
     * Strictly limited to 25 items, fast indexing, isolated by PT vs Cabang, and status Approved.
     */
    public function searchBom(Request $request)
    {
        $this->authorize('lihat-master-tahapan');

        $companyId       = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $isCabang        = !empty($activeBranchId);

        $query = Bom::forActiveCompany()
            ->with(['barangJadi:id,nama_barang,kode_barang'])
            ->whereIn('status', ['Approved', 'approved', 'Disetujui', 'disetujui']);

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($sub) use ($search) {
                $sub->where('kode_bom', 'like', "%{$search}%")
                    ->orWhere('nama_bom', 'like', "%{$search}%")
                    ->orWhereHas('barangJadi', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', "%{$search}%")
                          ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            });
        }

        $boms = $query->orderBy('id', 'desc')->limit(25)->get(['id', 'kode_bom', 'nama_bom', 'barang_jadi_id', 'status', 'branch_id']);

        // Cek BOM mana saja yang sudah pernah di-input alur tahapannya di company/branch aktif
        $existingBomIds = MasterTahapanProduksi::forActiveCompany()
            ->whereNotNull('bom_id')
            ->distinct()
            ->pluck('bom_id')
            ->toArray();

        // Cek apakah Template Umum sudah pernah di-input di company/branch aktif
        $hasGeneralTahapan = MasterTahapanProduksi::forActiveCompany()
            ->whereNull('bom_id')
            ->exists();

        return response()->json([
            'boms' => $boms->map(function ($b) use ($existingBomIds) {
                return [
                    'id'          => $b->id,
                    'kode_bom'    => $b->kode_bom,
                    'nama_bom'    => $b->nama_bom,
                    'barang_nama' => $b->barangJadi ? $b->barangJadi->nama_barang : '',
                    'barang_kode' => $b->barangJadi ? $b->barangJadi->kode_barang : '',
                    'status'      => $b->status,
                    'has_tahapan' => in_array($b->id, $existingBomIds),
                ];
            }),
            'count'               => $boms->count(),
            'has_general_tahapan' => $hasGeneralTahapan,
        ]);
    }


    /**
     * Store — simpan tahapan baru (Mendukung Batch Multi-Row hingga 50 tahapan & Single Row).
     */
    public function store(Request $request)
    {
        $this->authorize('tambah-master-tahapan');

        $activeCompanyId = session('active_company_id') ?? Auth::user()->company_id ?? 1;
        $activeBranchId  = session('active_branch_id') ?: null;

        // ── BATCH MULTI-ROW INPUT ──
        if ($request->has('tahapans') && is_array($request->tahapans)) {
            $validated = $request->validate([
                'bom_id'                  => 'required|exists:boms,id',
                'tahapans'                => 'required|array|min:1|max:50',
                'tahapans.*.nama_tahapan' => 'required|string|max:100',
                'tahapans.*.deskripsi'    => 'nullable|string|max:255',
                'tahapans.*.urutan'       => 'nullable|integer',
            ], [
                'bom_id.required'         => 'Resep BOM / Kode BOM wajib dipilih.',
                'bom_id.exists'           => 'Resep BOM yang dipilih tidak valid atau tidak ditemukan.',
                'tahapans.required'       => 'Daftar tahapan produksi wajib diisi.',
                'tahapans.min'            => 'Minimal 1 baris tahapan produksi harus diisi.',
            ]);

            DB::beginTransaction();
            try {
                $bomId = $validated['bom_id'] ?? null;
                if ($bomId) {
                    $bom = Bom::find($bomId);
                    if ($bom && !empty($bom->branch_id)) {
                        $finalBranchId  = $bom->branch_id;
                        $finalCompanyId = null; // Cabang input: PT null
                    } elseif ($bom) {
                        $finalBranchId  = null; // PT input: Cabang null
                        $finalCompanyId = $bom->company_id ?? $activeCompanyId;
                    } else {
                        $finalBranchId  = $activeBranchId;
                        $finalCompanyId = !empty($activeBranchId) ? null : $activeCompanyId;
                    }
                } else {
                    if (!empty($activeBranchId)) {
                        $finalBranchId  = $activeBranchId;
                        $finalCompanyId = null; // Cabang input: PT null
                    } else {
                        $finalBranchId  = null; // PT input: Cabang null
                        $finalCompanyId = $activeCompanyId;
                    }
                }

                // Keamanan & Integritas: Cek apakah alur tahapan untuk BOM / Template ini sudah pernah di-input sebelumnya
                $checkDuplicate = MasterTahapanProduksi::query();
                if ($finalBranchId) {
                    $checkDuplicate->where('branch_id', $finalBranchId)->whereNull('company_id');
                } else {
                    $checkDuplicate->whereNull('branch_id')->where('company_id', $finalCompanyId);
                }
                if ($bomId) {
                    $checkDuplicate->where('bom_id', $bomId);
                } else {
                    $checkDuplicate->whereNull('bom_id');
                }

                if ($checkDuplicate->exists()) {
                    $targetName = ($bomId && isset($bom) && $bom) ? "BOM \"{$bom->nama_bom}\"" : "Template Standar Umum";
                    return response()->json([
                        'message' => "Data alur tahapan untuk {$targetName} sudah pernah di-input sebelumnya! Silakan gunakan fitur Edit Alur pada daftar jika ingin mengubah alur proses, atau hapus data alur terlebih dahulu jika ingin membuat alur baru."
                    ], 422);
                }

                $createdCount = 0;

                // Ambil urutan terakhir untuk BOM ini
                $baseUrutanQuery = MasterTahapanProduksi::query();
                if ($finalBranchId) {
                    $baseUrutanQuery->where('branch_id', $finalBranchId)->whereNull('company_id');
                } else {
                    $baseUrutanQuery->whereNull('branch_id')->where('company_id', $finalCompanyId);
                }
                if ($bomId) {
                    $baseUrutanQuery->where('bom_id', $bomId);
                } else {
                    $baseUrutanQuery->whereNull('bom_id');
                }
                $maxUrutan = (int) ($baseUrutanQuery->max('urutan') ?? 0);

                foreach ($request->tahapans as $idx => $tRow) {
                    $nama = trim($tRow['nama_tahapan'] ?? '');
                    if (empty($nama)) continue;

                    $maxUrutan++;
                    $deskripsi = !empty($tRow['deskripsi']) ? trim($tRow['deskripsi']) : null;

                    // Cek upload foto untuk baris ini
                    $fotoFile = null;
                    if ($request->hasFile("tahapans.{$idx}.foto")) {
                        $fotoFile = $request->file("tahapans.{$idx}.foto");
                    } elseif ($request->hasFile("foto_{$idx}")) {
                        $fotoFile = $request->file("foto_{$idx}");
                    }

                    $fotoPath = $fotoFile ? $this->handleImageUpload($fotoFile) : null;

                    MasterTahapanProduksi::create([
                        'company_id'   => $finalCompanyId, // NULL jika cabang
                        'branch_id'    => $finalBranchId,  // NULL jika PT
                        'bom_id'       => $bomId,
                        'tipe_spk'     => 'Semua',
                        'urutan'       => !empty($tRow['urutan']) ? (int) $tRow['urutan'] : $maxUrutan,
                        'nama_tahapan' => $nama,
                        'deskripsi'    => $deskripsi,
                        'foto'         => $fotoPath,
                        'is_active'    => true,
                        'created_by'   => Auth::id(),
                        'updated_by'   => Auth::id(),
                    ]);

                    $createdCount++;
                }

                DB::commit();

                $bomName = ($bomId && isset($bom) && $bom) ? " untuk BOM \"{$bom->nama_bom}\"" : '';

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menambahkan {$createdCount} tahapan produksi{$bomName}!",
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Error batch store MasterTahapanProduksi: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
            }
        }

        // ── SINGLE ROW FALLBACK ──
        $validated = $request->validate([
            'nama_tahapan' => 'required|string|max:100',
            'deskripsi'    => 'nullable|string|max:255',
            'bom_id'       => 'required|exists:boms,id',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'bom_id.required' => 'Resep BOM / Kode BOM wajib dipilih.',
            'bom_id.exists'   => 'Resep BOM yang dipilih tidak valid atau tidak ditemukan.',
        ]);

        try {
            $bomId = $validated['bom_id'] ?? null;
            if ($bomId) {
                $bom = Bom::find($bomId);
                if ($bom && !empty($bom->branch_id)) {
                    $finalBranchId  = $bom->branch_id;
                    $finalCompanyId = null; // Cabang input: PT null
                } elseif ($bom) {
                    $finalBranchId  = null; // PT input: Cabang null
                    $finalCompanyId = $bom->company_id ?? $activeCompanyId;
                } else {
                    $finalBranchId  = $activeBranchId;
                    $finalCompanyId = !empty($activeBranchId) ? null : $activeCompanyId;
                }
            } else {
                if (!empty($activeBranchId)) {
                    $finalBranchId  = $activeBranchId;
                    $finalCompanyId = null; // Cabang input: PT null
                } else {
                    $finalBranchId  = null; // PT input: Cabang null
                    $finalCompanyId = $activeCompanyId;
                }
            }

            // Keamanan & Integritas: Cek apakah alur tahapan untuk BOM / Template ini sudah pernah di-input sebelumnya
            $checkDuplicate = MasterTahapanProduksi::query();
            if ($finalBranchId) {
                $checkDuplicate->where('branch_id', $finalBranchId)->whereNull('company_id');
            } else {
                $checkDuplicate->whereNull('branch_id')->where('company_id', $finalCompanyId);
            }
            if ($bomId) {
                $checkDuplicate->where('bom_id', $bomId);
            } else {
                $checkDuplicate->whereNull('bom_id');
            }

            if ($checkDuplicate->exists()) {
                $targetName = ($bomId && isset($bom) && $bom) ? "BOM \"{$bom->nama_bom}\"" : "Template Standar Umum";
                return response()->json([
                    'message' => "Data alur tahapan untuk {$targetName} sudah pernah di-input sebelumnya! Silakan gunakan fitur Edit Alur pada daftar jika ingin mengubah alur proses, atau hapus data alur terlebih dahulu jika ingin membuat alur baru."
                ], 422);
            }

            $nextUrutanQuery = MasterTahapanProduksi::query();
            if ($finalBranchId) {
                $nextUrutanQuery->where('branch_id', $finalBranchId)->whereNull('company_id');
            } else {
                $nextUrutanQuery->whereNull('branch_id')->where('company_id', $finalCompanyId);
            }
            if ($bomId) {
                $nextUrutanQuery->where('bom_id', $bomId);
            } else {
                $nextUrutanQuery->whereNull('bom_id');
            }
            $nextUrutan = (int) ($nextUrutanQuery->max('urutan') ?? 0) + 1;

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $this->handleImageUpload($request->file('foto'));
            }

            MasterTahapanProduksi::create([
                'company_id'   => $finalCompanyId, // NULL jika cabang
                'branch_id'    => $finalBranchId,  // NULL jika PT
                'bom_id'       => $bomId,
                'tipe_spk'     => 'Semua',
                'urutan'       => $nextUrutan,
                'nama_tahapan' => $validated['nama_tahapan'],
                'deskripsi'    => $validated['deskripsi'] ?? null,
                'foto'         => $fotoPath,
                'is_active'    => true,
                'created_by'   => Auth::id(),
                'updated_by'   => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => "Tahapan produksi \"{$validated['nama_tahapan']}\" berhasil ditambahkan!"]);
        } catch (\Throwable $e) {
            Log::error('Error store MasterTahapanProduksi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show — ambil detail untuk form edit (API JSON).
     */
    public function show(string $id)
    {
        $this->authorize('edit-master-tahapan');

        $tahapan = MasterTahapanProduksi::forActiveCompany()
            ->with(['bom.barangJadi'])
            ->findOrFail($id);

        return response()->json([
            'tahapan'  => $tahapan,
            'foto_url' => $tahapan->foto_url,
        ]);
    }

    /**
     * Update — perbarui tahapan (nama, deskripsi, bom_id, foto, is_active).
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-master-tahapan');

        $validated = $request->validate([
            'nama_tahapan' => 'required|string|max:100',
            'deskripsi'    => 'nullable|string|max:255',
            'bom_id'       => 'required|exists:boms,id',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hapus_foto'   => 'nullable|string',
            'is_active'    => 'sometimes|boolean',
        ], [
            'bom_id.required' => 'Resep BOM / Kode BOM wajib dipilih.',
            'bom_id.exists'   => 'Resep BOM yang dipilih tidak valid atau tidak ditemukan.',
        ]);

        try {
            $tahapan = MasterTahapanProduksi::forActiveCompany()->findOrFail($id);

            $fotoPath = $tahapan->foto;

            // Hapus foto jika user memilih hapus foto
            if ($request->input('hapus_foto') === '1' || $request->input('hapus_foto') === 'true') {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
                $fotoPath = null;
            }

            // Upload dan kompres foto baru jika ada
            if ($request->hasFile('foto')) {
                $fotoPath = $this->handleImageUpload($request->file('foto'), $tahapan->foto);
            }

            $tahapan->update([
                'nama_tahapan' => $validated['nama_tahapan'],
                'deskripsi'    => $validated['deskripsi'] ?? null,
                'bom_id'       => $validated['bom_id'] ?? null,
                'foto'         => $fotoPath,
                'is_active'    => $request->has('is_active') ? $request->boolean('is_active') : $tahapan->is_active,
                'updated_by'   => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => "Tahapan \"{$tahapan->nama_tahapan}\" berhasil diperbarui!"]);
        } catch (\Throwable $e) {
            Log::error('Error update MasterTahapanProduksi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Image Upload with Automatic Compression & Resizing to KB.
     * Mengompres gambar menjadi WebP / JPG ringan (~40-120 KB) dengan resolusi maksimal 1200px.
     */
    private function handleImageUpload($file, $oldPath = null): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Hapus foto lama jika ada
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $directory = 'tahapan_produksi';
        $fullDirPath = storage_path('app/public/' . $directory);
        if (!file_exists($fullDirPath)) {
            mkdir($fullDirPath, 0755, true);
        }

        $filename = 'tahapan_' . time() . '_' . Str::random(10) . '.webp';
        $targetPath = $fullDirPath . '/' . $filename;
        $relativePath = $directory . '/' . $filename;

        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // 🚀 FAST-PATH: Jika client sudah mengirim gambar WebP yang sudah dikompresi (<= 600 KB)
        // langsung simpan file tanpa re-encode ulang via GD! Memangkas waktu dari ~400ms menjadi < 1ms per gambar.
        if (($mime === 'image/webp' || $extension === 'webp') && $file->getSize() <= 614400) {
            $file->move($fullDirPath, $filename);
            return $relativePath;
        }

        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($file->getRealPath());
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($file->getRealPath());
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($file->getRealPath());
                break;
            default:
                return $file->store($directory, 'public');
        }

        if (!$srcImage) {
            return $file->store($directory, 'public');
        }

        $origWidth  = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Maximum dimensions (1200px max maintaining aspect ratio)
        $maxWidth  = 1200;
        $maxHeight = 1200;

        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $newWidth  = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);
        } else {
            $newWidth  = $origWidth;
            $newHeight = $origHeight;
        }

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as WebP with 75% quality (hasil kompresi jadi ~40-120 KB)
        if (function_exists('imagewebp')) {
            imagewebp($dstImage, $targetPath, 75);
        } else {
            $filename = str_replace('.webp', '.jpg', $filename);
            $targetPath = $fullDirPath . '/' . $filename;
            $relativePath = $directory . '/' . $filename;
            imagejpeg($dstImage, $targetPath, 75);
        }

        @imagedestroy($srcImage);
        @imagedestroy($dstImage);

        return $relativePath;
    }

    /**
     * Destroy — hard delete (langsung hapus permanen dari database & storage).
     */
     public function destroy(string $id)
     {
         $this->authorize('hapus-master-tahapan');
 
         try {
             $tahapan = MasterTahapanProduksi::forActiveCompany()->findOrFail($id);
             $nama    = $tahapan->nama_tahapan;
 
             // Hapus file foto dari disk storage jika ada
             if ($tahapan->foto && Storage::disk('public')->exists($tahapan->foto)) {
                 Storage::disk('public')->delete($tahapan->foto);
             }
 
             // Langsung hapus permanen dari database
             $tahapan->delete();
 
             return response()->json(['success' => true, 'message' => "Tahapan \"{$nama}\" berhasil dihapus permanen."]);
         } catch (\Throwable $e) {
             Log::error('Error delete MasterTahapanProduksi: ' . $e->getMessage());
             return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
         }
     }

    /**
     * getAllActive — API: ambil tahapan aktif khusus untuk resep BOM yang dipilih (untuk checklist di modal SPK).
     */
    public function getAllActive(Request $request)
    {
        $bomId = $request->input('bom_id');

        if (!$bomId) {
            return response()->json([
                'bom_id'      => null,
                'has_tahapan' => false,
                'tahapans'    => [],
                'count'       => 0,
                'message'     => 'Silakan pilih Resep BOM terlebih dahulu.',
            ]);
        }

        $bom = Bom::find($bomId);
        $namaBom = $bom ? $bom->nama_bom : ('BOM #' . $bomId);
        $kodeBom = $bom ? $bom->kode_bom : '-';

        $query = MasterTahapanProduksi::forActiveCompany()
            ->with(['bom.barangJadi'])
            ->where('is_active', true)
            ->where('bom_id', $bomId)
            ->orderBy('urutan', 'asc');

        $tahapans = $query->get();

        return response()->json([
            'bom_id'      => (int) $bomId,
            'nama_bom'    => $namaBom,
            'kode_bom'    => $kodeBom,
            'has_tahapan' => $tahapans->isNotEmpty(),
            'tahapans'    => $tahapans,
            'count'       => $tahapans->count(),
            'message'     => $tahapans->isEmpty() 
                ? "Resep BOM \"{$namaBom}\" belum memiliki konfigurasi tahapan produksi di Master Tahapan." 
                : "Berhasil memuat {$tahapans->count()} tahapan produksi untuk resep BOM \"{$namaBom}\".",
        ]);
    }

    /**
     * getTahapanByBom — API: ambil seluruh tahapan untuk satu resep BOM (atau Template Umum).
     */
    public function getTahapanByBom(string $bomKey)
    {
        $this->authorize('lihat-master-tahapan');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id') ?: null;

        $query = MasterTahapanProduksi::query()->with(['bom.barangJadi']);

        if ($bomKey === 'general' || $bomKey === '' || $bomKey === '0') {
            $query->whereNull('bom_id');
            if ($activeBranchId) {
                $query->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $query->whereNull('branch_id');
                if (!empty($activeCompanyId)) {
                    $query->where('company_id', $activeCompanyId);
                }
            }

            $bomInfo = [
                'id'          => null,
                'bom_key'     => 'general',
                'nama_bom'    => 'Template Umum (Standar Semua BOM)',
                'kode_bom'    => 'STANDAR',
                'nama_barang' => null,
            ];
        } else {
            $query->where('bom_id', $bomKey);
            $bom = Bom::with('barangJadi')->find($bomKey);
            $bomInfo = [
                'id'          => $bom ? $bom->id : $bomKey,
                'bom_key'     => (string) $bomKey,
                'nama_bom'    => $bom ? $bom->nama_bom : 'Resep BOM #' . $bomKey,
                'kode_bom'    => $bom ? $bom->kode_bom : '-',
                'nama_barang' => $bom && $bom->barangJadi ? $bom->barangJadi->nama_barang : null,
            ];
        }

        $tahapans = $query->orderBy('urutan', 'asc')->get()->map(function ($t) {
            return [
                'id'           => $t->id,
                'bom_id'       => $t->bom_id,
                'urutan'       => $t->urutan,
                'nama_tahapan' => $t->nama_tahapan,
                'deskripsi'    => $t->deskripsi,
                'foto'         => $t->foto,
                'foto_url'     => $t->foto_url,
                'is_active'    => (bool) $t->is_active,
            ];
        });

        return response()->json([
            'bom_info' => $bomInfo,
            'tahapans' => $tahapans,
            'total'    => $tahapans->count(),
        ]);
    }

    /**
     * updateBatchByBom — Simpan perubahan batch alur tahapan untuk satu resep BOM.
     */
    public function updateBatchByBom(Request $request, string $bomKey)
    {
        $this->authorize('edit-master-tahapan');

        $activeCompanyId = session('active_company_id') ?? Auth::user()->company_id ?? 1;
        $activeBranchId  = session('active_branch_id') ?: null;
        $targetBomId     = ($bomKey === 'general' || $bomKey === '' || $bomKey === '0') ? null : $bomKey;

        // Tentukan apakah target alur ini milik Cabang atau PT:
        // Jika cabang: company_id NULL & branch_id terisi.
        // Jika PT: branch_id NULL & company_id terisi.
        if ($targetBomId) {
            $bom = Bom::find($targetBomId);
            if ($bom && !empty($bom->branch_id)) {
                $finalBranchId  = $bom->branch_id;
                $finalCompanyId = null;
            } elseif ($bom) {
                $finalBranchId  = null;
                $finalCompanyId = $bom->company_id ?? $activeCompanyId;
            } else {
                $finalBranchId  = $activeBranchId;
                $finalCompanyId = !empty($activeBranchId) ? null : $activeCompanyId;
            }
        } else {
            if (!empty($activeBranchId)) {
                $finalBranchId  = $activeBranchId;
                $finalCompanyId = null;
            } else {
                $finalBranchId  = null;
                $finalCompanyId = $activeCompanyId;
            }
        }

        $validated = $request->validate([
            'tahapans'                => 'required|array|min:1|max:50',
            'tahapans.*.nama_tahapan' => 'required|string|max:100',
            'tahapans.*.deskripsi'    => 'nullable|string|max:255',
            'tahapans.*.urutan'       => 'nullable|integer',
            'tahapans.*.id'           => 'nullable|integer',
            'tahapans.*.hapus_foto'   => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil id yang ada saat ini untuk BOM ini di company & branch aktif
            $existingQuery = MasterTahapanProduksi::query();
            if ($targetBomId) {
                $existingQuery->where('bom_id', $targetBomId);
            } else {
                $existingQuery->whereNull('bom_id');
                if ($finalBranchId) {
                    $existingQuery->where('branch_id', $finalBranchId)->whereNull('company_id');
                } else {
                    $existingQuery->whereNull('branch_id')->where('company_id', $finalCompanyId);
                }
            }
            $existingTahapans = $existingQuery->get()->keyBy('id');

            $processedIds = [];

            foreach ($request->tahapans as $idx => $tRow) {
                $nama = trim($tRow['nama_tahapan'] ?? '');
                if (empty($nama)) continue;

                $tId = !empty($tRow['id']) ? (int) $tRow['id'] : null;
                $deskripsi = !empty($tRow['deskripsi']) ? trim($tRow['deskripsi']) : null;
                $urutan = !empty($tRow['urutan']) ? (int) $tRow['urutan'] : ($idx + 1);

                // Cek upload foto
                $fotoFile = null;
                if ($request->hasFile("tahapans.{$idx}.foto")) {
                    $fotoFile = $request->file("tahapans.{$idx}.foto");
                } elseif ($request->hasFile("foto_{$idx}")) {
                    $fotoFile = $request->file("foto_{$idx}");
                }

                $hapusFoto = !empty($tRow['hapus_foto']) && ($tRow['hapus_foto'] === '1' || $tRow['hapus_foto'] === 'true');

                if ($tId && isset($existingTahapans[$tId])) {
                    // Update existing
                    $current = $existingTahapans[$tId];
                    $fotoPath = $current->foto;

                    if ($hapusFoto) {
                        if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                            Storage::disk('public')->delete($fotoPath);
                        }
                        $fotoPath = null;
                    }

                    if ($fotoFile) {
                        $fotoPath = $this->handleImageUpload($fotoFile, $current->foto);
                    }

                    $current->update([
                        'company_id'   => $finalCompanyId,
                        'branch_id'    => $finalBranchId,
                        'urutan'       => $urutan,
                        'nama_tahapan' => $nama,
                        'deskripsi'    => $deskripsi,
                        'foto'         => $fotoPath,
                        'updated_by'   => Auth::id(),
                    ]);

                    $processedIds[] = $tId;
                } else {
                    // Create new
                    $fotoPath = $fotoFile ? $this->handleImageUpload($fotoFile) : null;

                    $newRecord = MasterTahapanProduksi::create([
                        'company_id'   => $finalCompanyId,
                        'branch_id'    => $finalBranchId,
                        'bom_id'       => $targetBomId,
                        'tipe_spk'     => 'Semua',
                        'urutan'       => $urutan,
                        'nama_tahapan' => $nama,
                        'deskripsi'    => $deskripsi,
                        'foto'         => $fotoPath,
                        'is_active'    => true,
                        'created_by'   => Auth::id(),
                        'updated_by'   => Auth::id(),
                    ]);

                    $processedIds[] = $newRecord->id;
                }
            }

            // Hapus baris lama yang tidak ada di form (hard delete secara bulk)
            $toDeleteIds = [];
            foreach ($existingTahapans as $oldId => $oldTahapan) {
                if (!in_array($oldId, $processedIds)) {
                    if ($oldTahapan->foto && Storage::disk('public')->exists($oldTahapan->foto)) {
                        Storage::disk('public')->delete($oldTahapan->foto);
                    }
                    $toDeleteIds[] = $oldId;
                }
            }
            if (!empty($toDeleteIds)) {
                MasterTahapanProduksi::whereIn('id', $toDeleteIds)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alur tahapan produksi berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updateBatchByBom: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui alur tahapan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * destroyByBom — Hapus seluruh tahapan alur untuk satu resep BOM.
     */
    public function destroyByBom(string $bomKey)
    {
        $this->authorize('hapus-master-tahapan');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id') ?: null;

        try {
            $query = MasterTahapanProduksi::query();
            if ($bomKey === 'general' || $bomKey === '' || $bomKey === '0') {
                $query->whereNull('bom_id');
                if ($activeBranchId) {
                    $query->where('branch_id', $activeBranchId)->whereNull('company_id');
                } else {
                    $query->whereNull('branch_id');
                    if (!empty($activeCompanyId)) {
                        $query->where('company_id', $activeCompanyId);
                    }
                }
                $bomNama = 'Template Umum';
            } else {
                $query->where('bom_id', $bomKey);
                $bom = Bom::find($bomKey);
                $bomNama = $bom ? "BOM \"{$bom->nama_bom}\"" : "BOM #{$bomKey}";
            }

            $tahapans = $query->get();
            $count = $tahapans->count();

            foreach ($tahapans as $t) {
                if ($t->foto && Storage::disk('public')->exists($t->foto)) {
                    Storage::disk('public')->delete($t->foto);
                }
                $t->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Seluruh alur tahapan untuk {$bomNama} ({$count} tahapan) berhasil dihapus permanen.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Error destroyByBom: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * getByTipeSpk — API: ambil tahapan aktif berdasarkan tipe SPK.
     * Digunakan oleh SpkProduksiController saat auto-generate tahapan.
     */
    public function getByTipeSpk(string $tipeSpk)
    {
        $tahapans = MasterTahapanProduksi::forActiveCompany()
            ->whereIn('tipe_spk', [$tipeSpk, 'Semua'])
            ->where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get(['urutan', 'nama_tahapan', 'bom_id', 'foto']);

        return response()->json(['tahapans' => $tahapans]);
    }
}

