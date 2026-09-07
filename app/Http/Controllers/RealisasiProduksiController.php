<?php

namespace App\Http\Controllers;

use App\Models\MasterTahapanProduksi;
use App\Models\SpkProduksi;
use App\Models\SpkProduksiTahapan;
use App\Models\SpkProduksiLogHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RealisasiProduksiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('lihat-realisasi-produksi') && 
            !$user->can('kontrol-tahapan-realisasi') && 
            !$user->can('mulai-tahapan-produksi') && 
            !$user->can('input-hasil-harian-produksi') && 
            !$user->can('input-realisasi-produksi') && 
            !$user->can('lihat-riwayat-input-produksi') && 
            !$user->can('selesai-tahapan-produksi') && 
            !$user->can('approve-realisasi-produksi') && 
            !$user->can('cetak-progress-spk')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman Realisasi & Kontrol Tahapan Produksi.');
        }

        if ($request->ajax()) {
            $query = SpkProduksi::forActiveCompany()
                ->forActiveBranch()
                ->with([
                    'barangJadi.satuan',
                    'satuan',
                    'tahapans',
                    'pengeluaranBahans',
                    'penerimaanBarangJadis'
                ])
                ->orderBy('id', 'desc');

            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'lengkap') {
                    // Tahapan 100% dan FG Received >= Target Qty ATAU status SPK Completed
                    $query->where(function($q) {
                        $q->where('spk_produksi.status', 'Completed')
                          ->orWhere(function($sub) {
                              $sub->whereHas('tahapans')
                                  ->whereDoesntHave('tahapans', function($t) {
                                      $t->where('status', '!=', 'Selesai');
                                  })
                                  ->whereHas('pengeluaranBahans');
                          });
                    });
                } elseif ($request->status === 'menunggu_gudang') {
                    // Tahapan 100% Selesai, tetapi penerimaan barang jadi belum 100%
                    $query->whereNotIn('spk_produksi.status', ['Completed', 'Cancelled'])
                        ->whereHas('tahapans')
                        ->whereDoesntHave('tahapans', function($t) {
                            $t->where('status', '!=', 'Selesai');
                        });
                } elseif ($request->status === 'proses') {
                    $query->whereNotIn('spk_produksi.status', ['Completed', 'Cancelled'])
                        ->where(function($q) {
                            $q->whereHas('tahapans', function($t) {
                                $t->where('status', 'Sedang Dikerjakan');
                            })->orWhere('status', 'In Progress');
                        });
                } elseif ($request->status === 'belum_bahan') {
                    $query->whereNotIn('spk_produksi.status', ['Completed', 'Cancelled'])
                        ->whereDoesntHave('pengeluaranBahans');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('spk_info', function ($spk) {
                    $bKode = $spk->barangJadi ? $spk->barangJadi->kode_barang : '-';
                    $bNama = $spk->barangJadi ? $spk->barangJadi->nama_barang : '-';
                    $sat   = $spk->satuan ? $spk->satuan->nama_satuan : (($spk->barangJadi && $spk->barangJadi->satuan) ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');

                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-bold font-monospace text-primary" style="font-size: 13.5px;"><i class="bi bi-file-earmark-code me-1"></i>' . e($spk->nomor_spk) . '</span>
                            <span class="fw-semibold text-truncate spk-text-contrast mt-0.5" style="max-width: 320px; font-size: 12.5px;">[' . e($bKode) . '] ' . e($bNama) . '</span>
                            <small class="spk-text-muted-contrast mt-0.5">Target: <b class="spk-text-contrast font-monospace">' . number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . e($sat) . '</b></small>
                        </div>
                    ';
                })
                ->addColumn('tahapan_progress', function ($spk) {
                    $totalTahap     = $spk->tahapans->count();
                    $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
                    $inProgress     = $spk->tahapans->where('status', 'Sedang Dikerjakan')->count();
                    $percent        = $totalTahap > 0 ? round(($completedTahap / $totalTahap) * 100) : 0;
                    $barClass       = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-info' : 'bg-secondary');

                    // 1. Status Pengeluaran Bahan
                    $hasBahan = $spk->pengeluaranBahans->count() > 0;
                    $bahanPill = $hasBahan
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-box-arrow-right me-1"></i>Bahan Out</span>'
                        : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-exclamation-circle me-1"></i>Bahan Belum Out</span>';

                    // 2. Status Penerimaan Barang Jadi
                    $receivedQty = (float) $spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
                    $targetQty   = (float) $spk->jumlah_rencana;
                    $sat         = $spk->satuan ? $spk->satuan->nama_satuan : (($spk->barangJadi && $spk->barangJadi->satuan) ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');

                    if ($receivedQty >= $targetQty && $targetQty > 0) {
                        $fgPill = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-box-arrow-in-down-right me-1"></i>Gudang Complete (' . number_format($receivedQty, 0, ',', '.') . ' ' . e($sat) . ')</span>';
                    } elseif ($receivedQty > 0) {
                        $fgPill = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-box-arrow-in-down-right me-1"></i>Gudang ' . number_format($receivedQty, 0, ',', '.') . '/' . number_format($targetQty, 0, ',', '.') . ' ' . e($sat) . '</span>';
                    } else {
                        $fgPill = '<span class="badge bg-secondary-subtle text-muted border border-secondary-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-inbox me-1"></i>Belum Input Gudang</span>';
                    }

                    // 3. Status Tahapan Aktif Pengerjaan
                    $tahapanDone = ($totalTahap > 0 && $completedTahap === $totalTahap);
                    $activeStage = $spk->tahapans->where('status', 'Sedang Dikerjakan')->first();
                    $activeStagePill = $activeStage
                        ? '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5" style="font-size: 10px;" title="Tahapan Aktif"><i class="bi bi-gear-fill me-1"></i>Tahap ' . $activeStage->urutan . ': ' . e($activeStage->nama_tahapan) . '</span>'
                        : ($tahapanDone ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;"><i class="bi bi-check-circle-fill me-1"></i>100% Tahapan Selesai</span>' : '');

                    return '
                        <div class="d-flex flex-column gap-1.5" style="min-width: 230px;">
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="fw-bold text-muted" style="font-size: 11px;">TAHAPAN: <b class="text-primary font-monospace">' . $completedTahap . ' / ' . $totalTahap . ' Selesai</b></span>
                                <b class="font-monospace text-primary fw-bold" style="font-size: 11px;">' . $percent . '%</b>
                            </div>
                            <div class="progress rounded-pill overflow-hidden" style="height: 7px; background: rgba(148, 163, 184, 0.2);">
                                <div class="progress-bar ' . $barClass . ' progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' . $percent . '%;"></div>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                ' . $bahanPill . '
                                ' . $fgPill . '
                                ' . $activeStagePill . '
                            </div>
                        </div>
                    ';
                })
                ->addColumn('status_spk', function ($spk) {
                    $hasBahan       = $spk->pengeluaranBahans->count() > 0;
                    $totalTahap     = $spk->tahapans->count();
                    $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
                    $inProgress     = $spk->tahapans->where('status', 'Sedang Dikerjakan')->count();

                    $tahapanDone    = ($totalTahap > 0 && $completedTahap === $totalTahap);
                    $receivedQty    = (float) $spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
                    $targetQty      = (float) $spk->jumlah_rencana;
                    $fgDone         = ($receivedQty >= $targetQty && $targetQty > 0);

                    // Multi-Pillar Integrated Status Logic
                    if ($spk->status === 'Completed' || ($hasBahan && $tahapanDone && $fgDone)) {
                        return '<span class="badge spk-badge-green rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs"><i class="bi bi-check-all fs-6 me-1"></i>LENGKAP & COMPLETE</span>';
                    } elseif ($tahapanDone && !$fgDone) {
                        return '<span class="badge spk-badge-amber rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs"><i class="bi bi-box-arrow-in-down-right me-1"></i>TAHAPAN SELESAI (MENUNGGU GUDANG FG)</span>';
                    } elseif (!$hasBahan) {
                        return '<span class="badge spk-badge-red rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs"><i class="bi bi-exclamation-triangle-fill me-1"></i>BELUM KELUAR BAHAN</span>';
                    } elseif ($inProgress > 0 || ($totalTahap > 0 && $completedTahap > 0)) {
                        return '<span class="badge spk-badge-cyan rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs"><i class="bi bi-gear-wide-connected me-1"></i>PROSES TAHAPAN PABRIK</span>';
                    } else {
                        return '<span class="badge spk-badge-blue rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs"><i class="bi bi-hourglass-split me-1"></i>MENUNGGU PROSES</span>';
                    }
                })
                ->addColumn('action', function ($spk) {
                    $user = auth()->user();
                    if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
                        !$user->can('lihat-realisasi-produksi') && 
                        !$user->can('kontrol-tahapan-realisasi') && 
                        !$user->can('mulai-tahapan-produksi') && 
                        !$user->can('input-hasil-harian-produksi') && 
                        !$user->can('input-realisasi-produksi') && 
                        !$user->can('lihat-riwayat-input-produksi') && 
                        !$user->can('selesai-tahapan-produksi') && 
                        !$user->can('approve-realisasi-produksi') && 
                        !$user->can('cetak-progress-spk')) {
                        return '<span class="badge bg-secondary-subtle text-muted px-2.5 py-1 rounded-pill font-monospace" style="font-size:11px;"><i class="bi bi-lock me-1"></i>No Access</span>';
                    }

                    $hasBahan = $spk->pengeluaranBahans->count() > 0;
                    $totalTahap = $spk->tahapans->count();
                    $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
                    $isCompleted = ($spk->status === 'Completed') || ($totalTahap > 0 && $completedTahap === $totalTahap);

                    if (!$hasBahan) {
                        $nomorSpk = e($spk->nomor_spk);
                        return '
                            <div class="d-flex align-items-center justify-content-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary border-dashed rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 opacity-75" onclick="alertBahanBelumDisiapkan(\'' . $nomorSpk . '\')" title="Klik untuk melihat status bahan baku">
                                    <i class="bi bi-lock-fill text-warning"></i>
                                    <span>Kontrol Tahapan</span>
                                </button>
                            </div>
                        ';
                    }

                    if ($isCompleted) {
                        $nomorSpk = e($spk->nomor_spk);
                        if ($user->hasRole('Super Admin')) {
                            return '
                                <div class="d-flex align-items-center justify-content-center text-nowrap">
                                    <a href="' . route('produksi.realisasi.spk-detail', $spk->id) . '" class="btn btn-sm btn-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 border border-warning" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #f59e0b;" title="Buka Kontrol (Otorisasi Khusus Super Admin)">
                                        <i class="bi bi-shield-lock-fill text-warning fs-6"></i>
                                        <span>Kontrol Tahapan (Super Admin)</span>
                                    </a>
                                </div>
                            ';
                        } else {
                            return '
                                <div class="d-flex align-items-center justify-content-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-dashed rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 opacity-75" onclick="showKontrolTerkunciWarning(\'' . $nomorSpk . '\')" title="Kontrol Tahapan Terkunci (Hanya Wewenang Super Admin)">
                                        <i class="bi bi-lock-fill text-danger fs-6"></i>
                                        <span>Terkunci (Completed)</span>
                                    </button>
                                </div>
                            ';
                        }
                    }

                    return '
                        <div class="d-flex align-items-center justify-content-center text-nowrap">
                            <a href="' . route('produksi.realisasi.spk-detail', $spk->id) . '" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                                <i class="bi bi-ui-checks fs-6"></i>
                                <span>Kontrol Tahapan</span>
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['spk_info', 'tahapan_progress', 'status_spk', 'action'])
                ->make(true);
        }

        $totalSpkActive = SpkProduksi::forActiveCompany()->forActiveBranch()->where('status', 'In Progress')->count();
        $totalTahapBerjalan = SpkProduksiTahapan::whereHas('spk', function($q) {
            $q->forActiveCompany()->forActiveBranch();
        })->where('status', 'Sedang Dikerjakan')->count();
        $totalTahapSelesai = SpkProduksiTahapan::whereHas('spk', function($q) {
            $q->forActiveCompany()->forActiveBranch();
        })->where('status', 'Selesai')->count();
        $totalSpkCompleted = SpkProduksi::forActiveCompany()->forActiveBranch()->where('status', 'Completed')->count();

        return view('modul_produksi.realisasi.index', compact('totalSpkActive', 'totalTahapBerjalan', 'totalTahapSelesai', 'totalSpkCompleted'));
    }

    /**
     * Dedicated Full Page for SPK Production Realisasi & Progress per Stage.
     */
    public function detailSpk($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('lihat-realisasi-produksi') && 
            !$user->can('kontrol-tahapan-realisasi') && 
            !$user->can('mulai-tahapan-produksi') && 
            !$user->can('input-hasil-harian-produksi') && 
            !$user->can('input-realisasi-produksi') && 
            !$user->can('lihat-riwayat-input-produksi') && 
            !$user->can('selesai-tahapan-produksi') && 
            !$user->can('approve-realisasi-produksi') && 
            !$user->can('cetak-progress-spk')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka Lembar Kontrol & Realisasi Produksi SPK ini.');
        }
        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'tahapans',           // lightweight: no logHarians eager load
                'materials.barangMentah.satuan',
                'materials.satuan',
                'pengeluaranBahans'
            ])
            ->findOrFail($id);

        if ($spk->pengeluaranBahans->isEmpty() && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return redirect()->route('produksi.realisasi.index')
                ->with('warning', "Bahan baku untuk SPK {$spk->nomor_spk} belum dikeluarkan dari gudang. Harap lakukan pengeluaran bahan baku terlebih dahulu!");
        }

        $totalTahap = $spk->tahapans->count();
        $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
        $inProgressTahap = $spk->tahapans->where('status', 'Sedang Dikerjakan')->count();
        $percentComplete = $totalTahap > 0 ? round(($completedTahap / $totalTahap) * 100) : 0;
        $isCompleted = ($spk->status === 'Completed') || ($totalTahap > 0 && $completedTahap === $totalTahap);

        if ($isCompleted && !$user->hasRole('Super Admin')) {
            return redirect()->route('produksi.realisasi.index')
                ->with('warning', "OTORISASI KHUSUS: Kontrol Tahapan untuk SPK {$spk->nomor_spk} telah Selesai (Completed) dan TERKUNCI. Silakan hubungi Administrator Sistem (Super Admin) untuk pembukaan kunci!");
        }

        return view('modul_produksi.realisasi.spk_detail', compact(
            'spk',
            'totalTahap',
            'completedTahap',
            'inProgressTahap',
            'percentComplete'
        ));
    }



    /**
     * Store daily production log for a stage.
     */
    public function storeDailyLog(Request $request, $tahapanId)
    {
        $user = auth()->user();
        $isMarkSelesai = $request->boolean('mark_as_selesai');

        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            if ($isMarkSelesai && !$user->can('selesai-tahapan-produksi') && !$user->can('approve-realisasi-produksi') && !$user->can('input-realisasi-produksi')) {
                return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menandai tahapan ini Selesai.'], 403);
            }
            if (!$isMarkSelesai && !$user->can('input-hasil-harian-produksi') && !$user->can('input-realisasi-produksi')) {
                return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mencatat hasil realisasi produksi harian.'], 403);
            }
        }

        $tahap = SpkProduksiTahapan::with('spk')->findOrFail($tahapanId);
        $spk   = $tahap->spk;

        if ($tahap->status === 'Menunggu' && !$request->boolean('mark_as_selesai')) {
            return response()->json([
                'message' => "Gagal: Pengerjaan tahapan '{$tahap->nama_tahapan}' belum dimulai! Klik tombol 'Mulai Pengerjaan' terlebih dahulu."
            ], 422);
        }

        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'qty_hasil_hari_ini'   => 'required|numeric|min:0|max:999999',
            'qty_reject_hari_ini'  => 'nullable|numeric|min:0|max:999999',
            'pic_operator'         => 'nullable|string|max:150',
            'catatan'              => 'nullable|string|max:40',
            'foto_bukti'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'qty_hasil_hari_ini.max'  => 'Hasil produksi harian tidak boleh melebihi 6 karakter (maks 999.999)!',
            'qty_reject_hari_ini.max' => 'Qty reject tidak boleh melebihi 6 karakter (maks 999.999)!',
            'catatan.max'             => 'Catatan pengerjaan harian maksimal 40 karakter!',
        ]);

        $qtyHasil  = (float)$validated['qty_hasil_hari_ini'];
        $qtyReject = (float)($validated['qty_reject_hari_ini'] ?? 0);

        // Security Validation: Qty Hasil cannot exceed remaining allowed Target Qty
        $targetTotal = (float)$tahap->target_qty;
        $qtySelesaiLama = (float)$tahap->qty_selesai;
        $sisaAllowed = max(0, $targetTotal - $qtySelesaiLama);

        if ($targetTotal > 0 && $qtyHasil > $sisaAllowed) {
            $unitText = $spk && $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            return response()->json([
                'success' => false,
                'message' => "Input ditolak! Hasil produksi hari ini (" . number_format($qtyHasil, 0, ',', '.') . " {$unitText}) melebihi sisa target pengerjaan yang diizinkan (" . number_format($sisaAllowed, 0, ',', '.') . " {$unitText}). Total akumulasi pengerjaan tidak boleh melebihi Target Total Qty (" . number_format($targetTotal, 0, ',', '.') . " {$unitText})."
            ], 422);
        }

        $fotoPath = null;
        if ($request->filled('foto_bukti_base64')) {
            $base64Image = $request->input('foto_bukti_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);
                $fileName = 'spk_log_fotos/' . uniqid() . '.' . $type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $data);
                $fotoPath = $fileName;
            }
        } elseif ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('spk_log_fotos', 'public');
        }

        $isMarkSelesai = $request->input('mark_as_selesai') == '1';

        if (!$fotoPath && !$isMarkSelesai) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: Foto bukti hasil pengerjaan harian WAJIB diunggah atau dijepret langsung dari kamera!'
            ], 422);
        }

        $spk->load('penanggungJawab');
        $userOperator = $spk->penanggungJawab->nama_lengkap ?? $spk->penanggungJawab->nama ?? auth()->user()->nama_lengkap ?? auth()->user()->name;

        // Record Daily Log if results or photo exist
        $log = null;
        if ($qtyHasil > 0 || $qtyReject > 0 || $fotoPath) {
            $log = SpkProduksiLogHarian::create([
                'spk_id'              => $spk->id,
                'spk_tahapan_id'      => $tahap->id,
                'tanggal'             => $validated['tanggal'],
                'qty_hasil_hari_ini'  => $qtyHasil,
                'qty_reject_hari_ini' => $qtyReject,
                'pic_operator'        => $userOperator,
                'catatan'             => $validated['catatan'] ?? null,
                'foto_bukti'          => $fotoPath,
                'created_by'          => auth()->id(),
            ]);
        }

        // Calculate new cumulative finished Qty and Reject Qty as exact SUM of all recorded log entries
        $cumSelesai = (float)$tahap->logHarians()->sum('qty_hasil_hari_ini');
        $cumReject  = (float)$tahap->logHarians()->sum('qty_reject_hari_ini');

        $tahapUpdate = [
            'qty_selesai'  => $cumSelesai,
            'qty_reject'   => $cumReject,
            'pic_operator' => $userOperator,
        ];

        // Auto change status to 'Sedang Dikerjakan' if 'Menunggu'
        if ($tahap->status === 'Menunggu') {
            $tahapUpdate['status'] = 'Sedang Dikerjakan';
            $tahapUpdate['waktu_mulai'] = now();
        }

        // Auto mark as 'Selesai' if cumulative >= target_qty
        $nextStatus = $tahap->status;
        if ((float)$tahap->target_qty > 0 && $cumSelesai >= (float)$tahap->target_qty) {
            $nextStatus = 'Selesai';
        } elseif ($request->input('mark_as_selesai') == '1') {
            if ((float)$tahap->target_qty > 0 && $cumSelesai < (float)$tahap->target_qty) {
                $sisa = (float)$tahap->target_qty - $cumSelesai;
                return response()->json([
                    'success' => false,
                    'message' => "Gagal: Target pengerjaan belum tercapai ({$cumSelesai} / {$tahap->target_qty}). Masih kurang {$sisa}!"
                ], 422);
            }
            $nextStatus = 'Selesai';
        }

        if ($nextStatus === 'Selesai') {
            $tahapUpdate['status'] = 'Selesai';
            $tahapUpdate['waktu_selesai'] = now();
        }

        $tahap->update($tahapUpdate);

        // IMPORTANT: Refresh model to get the newly updated status
        $tahap->refresh();

        // Auto update SPK status to In Progress
        if (in_array($spk->status, ['Approved', 'Draft'])) {
            $spk->update([
                'status'     => 'In Progress',
                'updated_by' => auth()->id()
            ]);
        }

        // Auto activate next stage if current stage just became 'Selesai'
        if ($tahap->status === 'Selesai') {
            $nextTahapan = SpkProduksiTahapan::where('spk_id', $spk->id)
                ->where('urutan', '>', $tahap->urutan)
                ->where('status', 'Menunggu')
                ->orderBy('urutan', 'asc')
                ->first();

            if ($nextTahapan) {
                $nextTahapan->update([
                    'status'      => 'Sedang Dikerjakan',
                    'waktu_mulai' => now()
                ]);
            }
        }

        // Update overall SPK finished Qty from latest completed or updated stage
        $lastCompleted = $spk->tahapans()->orderBy('urutan', 'desc')->first();
        if ($lastCompleted) {
            $spk->update([
                'jumlah_selesai' => $lastCompleted->qty_selesai,
                'jumlah_reject'  => $lastCompleted->qty_reject,
            ]);
        }

        $tahap->refresh();

        return response()->json([
            'success'    => true,
            'message'   => "Laporan Harian +{$qtyHasil} Pcs untuk Tahapan '{$tahap->nama_tahapan}' berhasil dicatat!",
            'log'       => $log,
            'tahap'     => $tahap,
            'persentase' => $tahap->persentase_selesai
        ]);
    }

    /**
     * Get history of daily logs for a specific stage.
     */
    public function getLogsHistory($tahapanId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('lihat-riwayat-input-produksi') && 
            !$user->can('input-hasil-harian-produksi') && 
            !$user->can('input-realisasi-produksi') && 
            !$user->can('lihat-realisasi-produksi') && 
            !$user->can('kontrol-tahapan-realisasi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat riwayat input hasil harian.'], 403);
        }

        $tahap = SpkProduksiTahapan::with([
            'spk.barangJadi',
            'spk.satuan',
        ])->findOrFail($tahapanId);

        // Auto sync cumulative stage metrics with exact sum of log entries
        if ($tahap->logHarians()->count() > 0) {
            $sumHasil  = (float)$tahap->logHarians()->sum('qty_hasil_hari_ini');
            $sumReject = (float)$tahap->logHarians()->sum('qty_reject_hari_ini');
            if ((float)$tahap->qty_selesai !== $sumHasil || (float)$tahap->qty_reject !== $sumReject) {
                $tahap->update([
                    'qty_selesai' => $sumHasil,
                    'qty_reject'  => $sumReject,
                ]);

                // Sync overall SPK finished Qty
                $spk = $tahap->spk;
                $lastCompleted = $spk->tahapans()->orderBy('urutan', 'desc')->first();
                if ($lastCompleted) {
                    $spk->update([
                        'jumlah_selesai' => $lastCompleted->qty_selesai,
                        'jumlah_reject'  => $lastCompleted->qty_reject,
                    ]);
                }
            }
        }

        // Limit to latest 100 entries — prevents loading 50k rows for a very active SPK
        $logs = SpkProduksiLogHarian::where('spk_tahapan_id', $tahapanId)
            ->with('creator')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success'    => true,
            'tahap'      => $tahap->fresh(['spk.satuan']),
            'spk'        => $tahap->spk,
            'logs'       => $logs,
            'persentase' => $tahap->fresh()->persentase_selesai,
            'total_logs' => SpkProduksiLogHarian::where('spk_tahapan_id', $tahapanId)->count(),
        ]);
    }

    /**
     * Directly start a specific production stage without creating a dummy daily log.
     */
    public function startTahapan($tahapanId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('mulai-tahapan-produksi') && 
            !$user->can('input-realisasi-produksi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk memulai pengerjaan tahapan produksi.'], 403);
        }

        $tahap = SpkProduksiTahapan::with('spk')->findOrFail($tahapanId);
        $spk   = $tahap->spk;

        if ($tahap->status === 'Menunggu') {
            $tahap->update([
                'status'      => 'Sedang Dikerjakan',
                'waktu_mulai' => now(),
            ]);
        }

        if ($spk && in_array($spk->status, ['Approved', 'Draft'])) {
            $spk->update([
                'status'     => 'In Progress',
                'updated_by' => auth()->id()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Pengerjaan tahapan '{$tahap->nama_tahapan}' berhasil dimulai!",
            'tahap'   => $tahap->fresh(),
        ]);
    }

    /**
     * Legacy quick update progress method.
     */
    public function updateProgress(Request $request, $tahapanId)
    {
        return $this->storeDailyLog($request, $tahapanId);
    }

    /**
     * Delete a daily log entry and recalculate stage cumulative metrics.
     */
    public function deleteLog($logId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('input-hasil-harian-produksi') && 
            !$user->can('input-realisasi-produksi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghapus catatan realisasi produksi.'], 403);
        }

        $log = SpkProduksiLogHarian::with('tahapan.spk')->findOrFail($logId);
        $tahap = $log->tahapan;
        $spk = $tahap->spk;

        if ($log->foto_bukti) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($log->foto_bukti);
        }

        $log->delete();

        // Recalculate cumulative totals from remaining log entries
        $totalHasil = (float)$tahap->logHarians()->sum('qty_hasil_hari_ini');
        $totalReject = (float)$tahap->logHarians()->sum('qty_reject_hari_ini');

        $tahap->update([
            'qty_selesai' => $totalHasil,
            'qty_reject'  => $totalReject,
        ]);

        // If stage was completed but remaining total is less than target, revert status
        if ($tahap->status === 'Selesai' && $totalHasil < $tahap->target_qty) {
            $tahap->update(['status' => 'Sedang Dikerjakan']);
        }

        // Update overall SPK finished Qty from latest stage
        $lastCompleted = $spk->tahapans()->orderBy('urutan', 'desc')->first();
        if ($lastCompleted) {
            $spk->update([
                'jumlah_selesai' => $lastCompleted->qty_selesai,
                'jumlah_reject'  => $lastCompleted->qty_reject,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan log harian berhasil dihapus dan akumulasi progres telah diperbarui.'
        ]);
    }

    /**
     * Get specific daily log detail for editing.
     */
    public function getLogDetail($logId)
    {
        $log = SpkProduksiLogHarian::with('tahapan.spk')->findOrFail($logId);
        return response()->json([
            'success'  => true,
            'log'      => $log,
            'foto_url' => $log->foto_bukti_url
        ]);
    }

    /**
     * Update an existing daily log entry and recalculate stage metrics.
     */
    public function updateLog(Request $request, $logId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('input-hasil-harian-produksi') && 
            !$user->can('input-realisasi-produksi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah catatan realisasi produksi.'], 403);
        }

        $log   = SpkProduksiLogHarian::with('tahapan.spk')->findOrFail($logId);
        $tahap = $log->tahapan;
        $spk   = $tahap->spk;

        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'qty_hasil_hari_ini'   => 'required|numeric|min:0|max:999999',
            'qty_reject_hari_ini'  => 'nullable|numeric|min:0|max:999999',
            'pic_operator'         => 'nullable|string|max:150',
            'catatan'              => 'nullable|string|max:40',
            'foto_bukti'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'qty_hasil_hari_ini.max'  => 'Hasil produksi harian tidak boleh melebihi 6 karakter (maks 999.999)!',
            'qty_reject_hari_ini.max' => 'Qty reject tidak boleh melebihi 6 karakter (maks 999.999)!',
            'catatan.max'             => 'Catatan pengerjaan harian maksimal 40 karakter!',
        ]);

        $qtyHasil  = (float)$validated['qty_hasil_hari_ini'];
        $qtyReject = (float)($validated['qty_reject_hari_ini'] ?? 0);

        // Security Validation: Updated Qty Hasil cannot exceed remaining allowed Target Qty
        $targetTotal = (float)$tahap->target_qty;
        $otherLogsDone = (float)$tahap->logHarians()->where('id', '!=', $log->id)->sum('qty_hasil_hari_ini');
        $sisaAllowed = max(0, $targetTotal - $otherLogsDone);

        if ($targetTotal > 0 && $qtyHasil > $sisaAllowed) {
            $unitText = $spk && $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            return response()->json([
                'success' => false,
                'message' => "Update ditolak! Hasil produksi harian baru (" . number_format($qtyHasil, 0, ',', '.') . " {$unitText}) melebihi sisa target pengerjaan yang diizinkan (" . number_format($sisaAllowed, 0, ',', '.') . " {$unitText}). Total akumulasi pengerjaan tidak boleh melebihi Target Total Qty (" . number_format($targetTotal, 0, ',', '.') . " {$unitText})."
            ], 422);
        }

        $fotoPath = null;
        if ($request->filled('foto_bukti_base64')) {
            $base64Image = $request->input('foto_bukti_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);
                $fileName = 'spk_log_fotos/' . uniqid() . '.' . $type;
                if ($log->foto_bukti) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($log->foto_bukti);
                }
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $data);
                $fotoPath = $fileName;
            }
        } elseif ($request->hasFile('foto_bukti')) {
            if ($log->foto_bukti) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($log->foto_bukti);
            }
            $fotoPath = $request->file('foto_bukti')->store('spk_log_fotos', 'public');
        }

        $logUpdate = [
            'tanggal'             => $validated['tanggal'],
            'qty_hasil_hari_ini'  => $qtyHasil,
            'qty_reject_hari_ini' => $qtyReject,
            'catatan'             => $validated['catatan'] ?? null,
        ];
        if ($fotoPath) {
            $logUpdate['foto_bukti'] = $fotoPath;
        }

        $log->update($logUpdate);

        // Recalculate stage cumulative metrics from all logs
        $totalHasil  = (float)$tahap->logHarians()->sum('qty_hasil_hari_ini');
        $totalReject = (float)$tahap->logHarians()->sum('qty_reject_hari_ini');

        $tahap->update([
            'qty_selesai' => $totalHasil,
            'qty_reject'  => $totalReject,
        ]);

        if ($tahap->status === 'Selesai' && $totalHasil < $tahap->target_qty) {
            $tahap->update(['status' => 'Sedang Dikerjakan']);
        } elseif ($tahap->status === 'Sedang Dikerjakan' && $tahap->target_qty > 0 && $totalHasil >= $tahap->target_qty) {
            $tahap->update(['status' => 'Selesai', 'waktu_selesai' => now()]);
        }

        // Update overall SPK finished Qty from latest stage
        $lastCompleted = $spk->tahapans()->orderBy('urutan', 'desc')->first();
        if ($lastCompleted) {
            $spk->update([
                'jumlah_selesai' => $lastCompleted->qty_selesai,
                'jumlah_reject'  => $lastCompleted->qty_reject,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan log harian berhasil diperbarui dan akumulasi progres telah dihitung ulang.',
            'log'     => $log->fresh()
        ]);
    }
}

