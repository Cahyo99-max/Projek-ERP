<?php

namespace App\Http\Controllers;

use App\Helpers\AccountMappingHelper;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Karyawan;
use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterLokasiRak;
use App\Models\SpkPenerimaanBarangJadi;
use App\Models\SpkPenerimaanBarangJadiDetail;
use App\Models\SpkPenerimaanBarangJadiSerial;
use App\Models\SpkPengeluaranBahanDetail;
use App\Models\SpkProduksi;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanBarangJadiController extends Controller
{
    /**
     * Display listing of Finished Goods Receipts (Hasil Produksi SPK).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Penerimaan Barang Jadi SPK.');
        }

        if ($request->ajax()) {
            $query = SpkProduksi::query()
                ->forActiveCompany()
                ->where(function ($q) {
                    $q->where('status', 'In Progress')
                      ->orWhere('status', 'Completed')
                      ->orWhereHas('penerimaanBarangJadis');
                })
                ->with([
                    'barangJadi:id,kode_barang,nama_barang,satuan_id,kategori_barang_id',
                    'barangJadi.satuan:id,nama_satuan',
                    'satuan:id,nama_satuan',
                    'tahapans:id,spk_id,status,target_qty,qty_selesai',
                    'materials:id,spk_id,barang_mentah_id,total_kebutuhan,total_keluar_gudang,satuan_id',
                    'fgClosedBy:id,nama_lengkap,username',
                    'fgClosingRequestedBy:id,nama_lengkap,username',
                    'fgClosingRejectedBy:id,nama_lengkap,username',
                    'pengeluaranBahans:id,spk_id',
                    'penerimaanBarangJadis' => function ($q) {
                        $q->forActiveCompany()->select('id', 'spk_id', 'nomor_penerimaan', 'tanggal_penerimaan', 'jumlah_hasil_baik', 'jumlah_reject', 'total_nilai_hpp', 'status')->latest('id');
                    }
                ])
                ->latest('id');

            if ($request->filled('gudang_id') && $request->gudang_id !== 'all') {
                $query->whereHas('penerimaanBarangJadis', fn($q) => $q->where('gudang_id', $request->gudang_id));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereHas('penerimaanBarangJadis', fn($q) => $q->whereBetween('tanggal_penerimaan', [$request->start_date, $request->end_date]));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('spk_info', function ($spk) {
                    $kode = $spk->barangJadi ? e($spk->barangJadi->kode_barang) : '-';
                    $nama = $spk->barangJadi ? e($spk->barangJadi->nama_barang) : '-';
                    $satuan = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');
                    $qtyRencana = number_format($spk->jumlah_rencana, 0, ',', '.');
                    return '<div>
                        <div class="fw-bold text-info font-monospace fs-6 mb-1 d-flex align-items-center gap-1.5">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>' . e($spk->nomor_spk) . '</span>
                        </div>
                        <div class="fw-bold mb-1" style="font-size: 0.92rem;">
                            <span class="badge spk-badge-cyan rounded-2 px-2 py-0.5 font-monospace me-1" style="font-size: 11px;">' . $kode . '</span>
                            <span class="spk-text-contrast fw-bold">' . $nama . '</span>
                        </div>
                        <div>
                            <span class="badge rounded-pill px-2.5 py-0.5 font-monospace" style="background: rgba(148, 163, 184, 0.18); color: var(--spk-text-main, #e2e8f0); border: 1px solid rgba(148, 163, 184, 0.4); font-size: 11px;">
                                Target: <b class="text-primary">' . $qtyRencana . '</b> ' . $satuan . '
                            </span>
                        </div>
                    </div>';
                })
                ->addColumn('progress_hasil', function ($spk) {
                    $target = (float)$spk->jumlah_rencana;
                    $diterimaSessions = $spk->penerimaanBarangJadis->where('status', 'Diterima');
                    $pendingSessions  = $spk->penerimaanBarangJadis->where('status', 'Menunggu HPP');
                    $selesai = (float)$diterimaSessions->sum('jumlah_hasil_baik');
                    $reject  = (float)$diterimaSessions->sum('jumlah_reject');
                    $satuan = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');

                    $rawPercent = $target > 0 ? ($selesai / $target) * 100 : 0;

                    if ($selesai >= $target && $target > 0) {
                        $badgeStyle = 'background: rgba(16, 185, 129, 0.18); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.45);';
                        $statusText = 'Lengkap (100%)';
                        $barGradient = 'background: linear-gradient(90deg, #10b981, #059669);';
                        $barWidth = 100;
                    } elseif ($selesai > 0) {
                        $badgeStyle = 'background: rgba(6, 182, 212, 0.18); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.45);';
                        if ($rawPercent < 0.1) {
                            $pctFormatted = number_format($rawPercent, 2, ',', '.') . '%';
                        } elseif ($rawPercent < 1) {
                            $pctFormatted = number_format($rawPercent, 1, ',', '.') . '%';
                        } else {
                            $pctFormatted = round($rawPercent, 1) . '%';
                        }
                        $statusText = 'Sebagian (' . $pctFormatted . ')';
                        $barGradient = 'background: linear-gradient(90deg, #3b82f6, #06b6d4);';
                        $barWidth = max(2, min(100, round($rawPercent, 1)));
                    } else {
                        $badgeStyle = 'background: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.45);';
                        $statusText = 'Belum Ada Hasil (0%)';
                        $barGradient = 'background: linear-gradient(90deg, #f59e0b, #d97706);';
                        $barWidth = 0;
                    }

                    $rejectText = $reject > 0 ? ' • <span class="text-danger fw-bold"><i class="bi bi-x-circle me-0.5"></i>Reject: ' . number_format($reject, 0, ',', '.') . ' ' . $satuan . '</span>' : '';
                    $pendingBadge = $pendingSessions->count() > 0 ? '<div class="mt-1"><span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 10.5px; background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45);"><i class="bi bi-hourglass-split me-1 text-warning"></i>' . $pendingSessions->count() . ' Sesi Menunggu HPP</span></div>' : '';

                    return '<div style="min-width: 180px;">
                        <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 11px;">
                            <span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="' . $badgeStyle . '">' . $statusText . '</span>
                            <span class="spk-text-muted font-monospace fw-semibold">' . number_format($selesai, 0, ',', '.') . ' / ' . number_format($target, 0, ',', '.') . ' ' . $satuan . '</span>
                        </div>
                        <div class="progress shadow-inner" style="height: 7px; border-radius: 99px; background-color: rgba(128,128,128,0.2);">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: ' . $barWidth . '%; ' . $barGradient . '" aria-valuenow="' . $barWidth . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="small mt-1 font-monospace" style="font-size: 10.5px;">' . $rejectText . '</div>
                        ' . $pendingBadge . '
                    </div>';
                })
                ->addColumn('frekuensi_sesi', function ($spk) {
                    $totalCount = $spk->penerimaanBarangJadis->count();
                    $approvedCount = $spk->penerimaanBarangJadis->where('status', 'Diterima')->count();
                    $pendingCount = $spk->penerimaanBarangJadis->where('status', 'Menunggu HPP')->count();
                    $last = $spk->penerimaanBarangJadis->first();
                    $lastDate = $last && $last->tanggal_penerimaan ? $last->tanggal_penerimaan->format('d/m/Y') : '-';

                    if ($totalCount === 0) {
                        return '<span class="badge rounded-pill px-3 py-1 font-monospace fw-semibold" style="font-size: 11px; background: rgba(148, 163, 184, 0.15); color: var(--spk-text-muted, #94a3b8); border: 1px solid rgba(148, 163, 184, 0.3);"><i class="bi bi-inbox me-1"></i>Belum ada penyerahan</span>';
                    }

                    $html = '<div>';
                    if ($approvedCount > 0) {
                        $html .= '<span class="badge rounded-pill px-3 py-1 font-monospace fw-bold me-1" style="font-size: 11px; background: rgba(59, 130, 246, 0.18); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.45);">
                            <i class="bi bi-box-arrow-in-down-right me-1"></i>' . $approvedCount . ' Sesi Diterima
                        </span>';
                    }
                    if ($pendingCount > 0) {
                        $html .= '<span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 11px; background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45);">
                            <i class="bi bi-hourglass-split me-1 text-warning"></i>' . $pendingCount . ' Menunggu HPP
                        </span>';
                    }
                    $html .= '<div class="small spk-text-muted mt-1 font-monospace" style="font-size: 11px;">
                        <i class="bi bi-clock me-1 text-primary"></i>Terakhir: ' . $lastDate . '
                    </div>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status_spk', function ($spk) {
                    $statusHtml = '<div class="d-flex flex-column align-items-center justify-content-center gap-1 text-nowrap">';
                    if ($spk->status === 'In Progress') {
                        $statusHtml .= '<span class="badge spk-badge-cyan rounded-pill px-2.5 py-0.5 fw-bold spk-badge-pulse" style="font-size: 10.5px;"><i class="bi bi-play-circle-fill me-1"></i>In Progress</span>';
                    } elseif ($spk->status === 'Completed') {
                        $statusHtml .= '<span class="badge spk-badge-green rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 10.5px;"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>';
                    } else {
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace" style="background: rgba(148, 163, 184, 0.15); color: var(--spk-text-main, #e2e8f0); border: 1px solid rgba(148, 163, 184, 0.35); font-size: 10.5px;">' . e($spk->status) . '</span>';
                    }

                    $statusClosing = $spk->fg_closing_status ?: ($spk->is_fg_closed || $spk->is_hpp_closed ? 'Closed' : 'Open');

                    if ($statusClosing === 'Closed' || $spk->is_fg_closed || $spk->is_hpp_closed) {
                        $closedAt = $spk->fg_closed_at ? $spk->fg_closed_at->format('d/m/Y') : ($spk->hpp_closed_at ? $spk->hpp_closed_at->format('d/m/Y') : '');
                        $byName = $spk->fgClosedBy?->nama_lengkap ?: ($spk->hppClosedBy?->nama_lengkap ?: 'User');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 10px;" title="Closed oleh ' . e($byName) . ' (' . $closedAt . ')"><i class="bi bi-lock-fill me-1"></i>Closed</span>';
                    } elseif ($statusClosing === 'Menunggu Closing') {
                        $reqAt = $spk->fg_closing_requested_at ? $spk->fg_closing_requested_at->format('d/m/Y H:i') : '';
                        $reqBy = $spk->fgClosingRequestedBy?->nama_lengkap ?: ($spk->fgClosingRequestedBy?->username ?? 'Tim Produksi');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-dark shadow-2xs spk-badge-pulse" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); font-size: 10px; border: 1px solid #d97706;" title="Diajukan oleh: ' . e($reqBy) . ' (' . $reqAt . ')"><i class="bi bi-hourglass-split me-1"></i>Menunggu Closing</span>';
                    } elseif ($statusClosing === 'Ditolak') {
                        $rejBy = $spk->fgClosingRejectedBy?->nama_lengkap ?: ($spk->fgClosingRejectedBy?->username ?? 'Accounting');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); font-size: 10px;" title="Ditolak oleh ' . e($rejBy) . ': ' . e($spk->fg_closing_reject_reason) . '"><i class="bi bi-x-circle-fill me-1"></i>Closing Ditolak</span>';
                    } else {
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-semibold" style="background: rgba(6, 182, 212, 0.18); color: #38bdf8; border: 1px solid rgba(6, 182, 212, 0.45); font-size: 10.5px;" title="Open (Penerimaan Hasil Terbuka)"><i class="bi bi-unlock-fill me-1"></i>Open</span>';
                    }
                    $statusHtml .= '</div>';
                    return $statusHtml;
                })
                ->addColumn('action', function ($spk) use ($user) {
                    $canTambah      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-penerimaan-barang-jadi');
                    $canDetail      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('detail-penerimaan-barang-jadi') || $user->can('lihat-penerimaan-barang-jadi');
                    $canAjukan      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('ajukan-closing-penerimaan-barang-jadi');
                    $canBatalAjukan = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('batal-ajukan-closing-penerimaan-barang-jadi');
                    $canClosing     = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('closing-penerimaan-barang-jadi');
                    $canReopen      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('buka-closing-penerimaan-barang-jadi');
                    $canJurnal      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-penerimaan-barang-jadi');
                    $canSesuaikanHpp = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('sesuaikan-hpp-penerimaan-barang-jadi');

                    $statusClosing = $spk->fg_closing_status ?: ($spk->is_fg_closed || $spk->is_hpp_closed ? 'Closed' : 'Open');
                    $totalSelesai  = (float) $spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
                    $targetQty     = (float) $spk->jumlah_rencana;
                    $spkNomor      = addslashes($spk->nomor_spk);

                    // 1. Realisasi Selesai (Semua tahapan selesai 100%)
                    $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                        return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
                    })->count() === 0;

                    // 2. Pengeluaran Bahan Baku Selesai (Sudah Closing ATAU Qty Keluar sudah 100% memenuhi kebutuhan)
                    $hasPengeluaran     = $spk->pengeluaranBahans->count() > 0;
                    $bahanClosed        = $spk->is_material_closed || $spk->material_closing_status === 'Closed';
                    $bahanKeluarLengkap = $hasPengeluaran && !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();

                    // Jika sudah Closing, maka pengeluaran bahan dianggap selesai & sah (walaupun ada efisiensi pemakaian bahan)
                    $bahanSelesai       = $hasPengeluaran && ($bahanClosed || $bahanKeluarLengkap);
                    $siapTerima         = $realisasiSelesai && $bahanSelesai;
                    $isLocked           = ($statusClosing === 'Closed' || $spk->is_fg_closed || $spk->is_hpp_closed);

                    $btn = '<div class="d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap">';

                    // 1. Tombol Terima Hasil (Hanya jika Open / Ditolak dan belum Closed)
                    if ($statusClosing !== 'Closed' && $statusClosing !== 'Menunggu Closing' && !$spk->is_fg_closed && !$spk->is_hpp_closed && $canTambah && !in_array($spk->status, ['Draft', 'Dibatalkan', 'Canceled'])) {
                        if ($siapTerima) {
                            $btn .= '<button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
                                        onclick="terimaBarangJadiSpk(' . $spk->id . ')"
                                        title="Terima Hasil Produksi Barang Jadi">
                                        <i class="bi bi-plus-lg"></i>
                                        <span>Terima</span>
                                     </button>';
                        } elseif (!$siapTerima) {
                            $reasons = [];
                            if (!$hasPengeluaran) {
                                $reasons[] = 'Belum Ada Pengeluaran Bahan Baku';
                            } elseif (!$bahanSelesai) {
                                if (!$bahanClosed && !$bahanKeluarLengkap) {
                                    $reasons[] = 'Pengeluaran Bahan Baku Belum 100% Selesai & Belum Closing';
                                } elseif (!$bahanClosed) {
                                    $reasons[] = 'Pengeluaran Bahan Baku Belum Closing (Jurnal Belum Terbit)';
                                }
                            }
                            if (!$realisasiSelesai) $reasons[] = 'Realisasi Tahapan Belum 100% Selesai';
                            $reasonText = implode(' & ', $reasons);

                            if (!$bahanSelesai) {
                                $btnLabel = !$bahanClosed ? 'Bahan Belum Closing' : 'Bahan Belum Lengkap';
                            } else {
                                $btnLabel = 'Realisasi Belum Selesai';
                            }

                            $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                        onclick="warnSpkBelumSiap(\'' . e($reasonText) . '\', \'' . $spkNomor . '\')"
                                        title="Belum Siap: ' . e($reasonText) . '">
                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                        <span>' . $btnLabel . '</span>
                                     </button>';
                        }
                    }

                    // 2. Tombol Riwayat (Selalu tampil jika punya hak akses)
                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="showSpkHistoryModal(' . $spk->id . ')"
                                    title="Lihat Detail & Riwayat Hasil Produksi">
                                    <i class="bi bi-clock-history text-info"></i>
                                    <span>Riwayat</span>
                                 </button>';
                    }

                    // 3. Tombol Hitung & Closing HPP / Ajukan Closing
                    $totalSessions = $spk->penerimaanBarangJadis->count();
                    $pendingSessions = $spk->penerimaanBarangJadis->where('status', 'Menunggu HPP');

                    if ($statusClosing === 'Menunggu Closing') {
                        if ($canClosing) {
                            $btn .= '<button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1 text-dark"
                                        onclick="bukaModalReviewClosingFg(' . $spk->id . ')"
                                        title="Review & Otorisasi Closing HPP SPK">
                                        <i class="bi bi-calculator-fill"></i>
                                        <span>Hitung & Closing HPP</span>
                                     </button>';
                        } elseif ($canBatalAjukan) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                        onclick="batalAjukanClosingFg(' . $spk->id . ', \'' . $spkNomor . '\')"
                                        title="Batalkan Pengajuan Closing SPK">
                                        <i class="bi bi-x-circle"></i>
                                        <span>Batal Ajukan</span>
                                     </button>';
                        }
                    } elseif ($statusClosing !== 'Closed' && !$spk->is_fg_closed && !$spk->is_hpp_closed && $totalSessions > 0) {
                        if ($canClosing || $canSesuaikanHpp) {
                            $btn .= '<button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1 text-dark"
                                        onclick="bukaModalReviewClosingFg(' . $spk->id . ')"
                                        title="Hitung Ulang & Finalisasi HPP SPK">
                                        <i class="bi bi-calculator-fill"></i>
                                        <span>Hitung & Closing HPP</span>
                                     </button>';
                        } elseif ($canAjukan) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                        onclick="bukaModalAjukanClosingFg(' . $spk->id . ', \'' . $spkNomor . '\')"
                                        title="Ajukan Closing Hasil Produksi ke Accounting">
                                        <i class="bi bi-send-check"></i>
                                        <span>Ajukan Closing</span>
                                     </button>';
                        }
                    }

                    // 4. Tombol Lihat Jurnal (Jika SPK sudah memiliki jurnal penerimaan / closing)
                    $hasAnyJurnal = $spk->is_fg_closed || $spk->is_hpp_closed || $spk->penerimaanBarangJadis->where('status', 'Diterima')->count() > 0;
                    if ($hasAnyJurnal && $canJurnal) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="showSpkJurnalClosingFgModal(' . $spk->id . ', \'' . $spkNomor . '\')"
                                    title="Lihat Jurnal Akuntansi Hasil Produksi">
                                    <i class="bi bi-journal-check text-primary"></i>
                                    <span>Jurnal</span>
                                 </button>';
                    }

                    // 5. Tombol Buka Kembali Closing (Reopen) jika sudah Closed
                    if (($statusClosing === 'Closed' || $spk->is_fg_closed || $spk->is_hpp_closed) && $canReopen) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="konfirmasiReopenFg(' . $spk->id . ', \'' . $spkNomor . '\')"
                                    title="Buka Kembali Closing Hasil Produksi (Reopen)">
                                    <i class="bi bi-unlock-fill text-warning"></i>
                                    <span>Reopen</span>
                                 </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['spk_info', 'progress_hasil', 'frekuensi_sesi', 'status_spk', 'action'])
                ->make(true);
        }

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        // Active SPK List for select dropdown (Termasuk SPK yang Reopened & belum Closed)
        $spkList = SpkProduksi::forActiveCompany()
            ->where(function ($q) {
                $q->where('status', 'In Progress')
                  ->orWhere('status', 'Completed')
                  ->orWhere('fg_closing_status', 'Open');
            })
            ->where('is_fg_closed', false)
            ->where(function ($q) {
                $q->whereNull('fg_closing_status')
                  ->orWhere('fg_closing_status', 'Open')
                  ->orWhere('fg_closing_status', 'Ditolak');
            })
            ->whereHas('pengeluaranBahans')
            ->where(function ($q) {
                $q->where('is_material_closed', true)
                  ->orWhere('material_closing_status', 'Closed')
                  ->orWhereDoesntHave('materials', fn($qm) => $qm->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan'));
            })
            ->whereHas('tahapans')
            ->whereDoesntHave('tahapans', fn($qt) => $qt->where('status', '!=', 'Selesai')->orWhere(fn($sq) => $sq->where('target_qty', '>', 0)->whereColumn('qty_selesai', '<', 'target_qty')))
            ->with(['barangJadi.satuan', 'satuan'])
            ->orderBy('id', 'desc')
            ->get();

        // Warehouse list (Barang Jadi / Umum) - DISARING KETAT SESUAI CABANG VS PT PUSAT
        $gudangQuery = MasterGudang::forActiveCompany()->active();
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangList = $gudangQuery->orderBy('nama_gudang', 'asc')->get();

        // Employees for Penerima & Penyerah
        $karyawanList = Karyawan::forActiveCompany()
            ->active()
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // Master Rack Locations - DISARING SESUAI GUDANG ENTITAS AKTIF
        $rakQuery = MasterLokasiRak::forActiveCompany()->active()->with('gudang');
        if (!empty($activeBranchId)) {
            $rakQuery->whereHas('gudang', fn($q) => $q->where('cabang_id', $activeBranchId));
        } else {
            $rakQuery->whereHas('gudang', fn($q) => $q->whereNull('cabang_id'));
        }
        $rakList = $rakQuery->orderBy('kode_rak', 'asc')->get();

        // Aggregate Metrics for Header Statistics Cards (Optimized single-pass query for 7M+ scale)
        $totalSpkSelesai = SpkProduksi::forActiveCompany()->where('status', 'Completed')->count();

        $pbgStats = SpkPenerimaanBarangJadi::forActiveCompany()
            ->selectRaw('COUNT(*) as total_sesi, COALESCE(SUM(jumlah_hasil_baik), 0) as total_baik, COALESCE(SUM(jumlah_reject), 0) as total_reject')
            ->first();

        $totalSesiPenerimaan = $pbgStats ? (int)$pbgStats->total_sesi : 0;
        $totalHasilBaik      = $pbgStats ? (float)$pbgStats->total_baik : 0.0;
        $totalHasilReject    = $pbgStats ? (float)$pbgStats->total_reject : 0.0;

        return view('modul_produksi.barang_jadi.index', compact(
            'spkList', 'gudangList', 'karyawanList', 'rakList',
            'totalSpkSelesai', 'totalHasilBaik', 'totalHasilReject', 'totalSesiPenerimaan'
        ));
    }

    /**
     * Get JSON details of SPK for finished goods receipt modal.
     */
    /**
     * Calculate ONLY the actual raw material cost total for an SPK
     * (without TK/Overhead — used specifically for HPP adjustment display).
     */
    private function calculateActualMaterialCost(SpkProduksi $spk): float
    {
        $spk->loadMissing(['pengeluaranBahans.details.barangMentah']);

        $pengeluaranIds = $spk->pengeluaranBahans->pluck('id')->toArray();
        if (!empty($pengeluaranIds)) {
            // 1. First attempt: fetch exact historical monetary value of material issued from KartuStok
            $kartuStokTotal = (float)KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                ->whereIn('referensi_id', $pengeluaranIds)
                ->where('tipe', 'Keluar')
                ->sum('nilai_total');

            if ($kartuStokTotal > 0) {
                return round($kartuStokTotal, 4);
            }
        }

        // 2. Fallback: calculate using detail quantities & average price if KartuStok entries are missing
        $totalMaterialCost = 0.0;
        foreach ($spk->pengeluaranBahans as $pengeluaran) {
            foreach ($pengeluaran->details as $detail) {
                $qty = (float)$detail->qty_diambil_sekarang;
                if ($qty <= 0) continue;

                $barangId = $detail->barang_mentah_id;
                $stokG = StokGudang::where('barang_id', $barangId)
                    ->where('gudang_id', $pengeluaran->gudang_id)
                    ->first();

                $hppItem = ($stokG && (float)$stokG->harga_pokok_rata_rata > 0)
                    ? (float)$stokG->harga_pokok_rata_rata
                    : ($detail->barangMentah ? (float)($detail->barangMentah->harga_pokok_rata_rata ?: $detail->barangMentah->harga_pokok) : 0);

                $totalMaterialCost += ($qty * $hppItem);
            }
        }

        return round($totalMaterialCost, 4);
    }

    /**
     * Helper to calculate SPK unit HPP (Harga Pokok Produksi per unit).
     * Uses stored estimasi_biaya_tk & estimasi_biaya_overhead for TK/overhead components.
     * Uses total actual good yield produced (if available) to ensure 100% absorption of production costs into Finished Goods inventory.
     */
    private function calculateSpkUnitHpp(SpkProduksi $spk): float
    {
        $targetQty = (float)$spk->jumlah_rencana;
        if ($targetQty <= 0) return 0.0;

        $spk->loadMissing(['bom', 'barangJadi']);

        $totalMaterialCost = $this->calculateActualMaterialCost($spk);

        if ($totalMaterialCost > 0) {
            // Determine divisor: use actual good yield produced if available, otherwise fallback to target qty
            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : $targetQty;

            // HPP = (Biaya Bahan Baku Aktual + Gaji Aktual yg sudah disimpan + Overhead Aktual) / Divisor Qty
            $totalProductionCost = $totalMaterialCost
                + (float)$spk->estimasi_biaya_tk
                + (float)$spk->estimasi_biaya_overhead;
            return round($totalProductionCost / $divisorQty, 4);
        }

        if ((float)$spk->estimasi_total_biaya > 0) {
            return round((float)$spk->estimasi_total_biaya / $targetQty, 4);
        }

        if ($spk->bom && (float)$spk->bom->total_estimasi_hpp > 0) {
            return (float)$spk->bom->total_estimasi_hpp;
        }

        if ($spk->barangJadi && (float)$spk->barangJadi->harga_pokok_rata_rata > 0) {
            return (float)$spk->barangJadi->harga_pokok_rata_rata;
        }

        return 0.0;
    }

    /**
     * Get JSON details of SPK for finished goods receipt modal.
     */
    public function getSpkDetail($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $activeBranchId = session('active_branch_id');

        try {
            $spk = SpkProduksi::forActiveCompany()
                ->with(['barangJadi.satuan', 'satuan', 'tahapans', 'materials.barangMentah'])
                ->findOrFail($spkId);

            // 1. Validasi: Pengeluaran Bahan Baku wajib ada dan sudah di-Closing (atau 100% lengkap)
            if (!$spk->pengeluaranBahans()->whereNull('deleted_at')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Penerimaan Barang Jadi Ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum dicatat oleh tim gudang bahan. Harap selesaikan pengeluaran bahan terlebih dahulu!"
                ], 422);
            }

            $isMaterialClosed = $spk->is_material_closed || $spk->material_closing_status === 'Closed';

            if (!$isMaterialClosed) {
                $unfinishedMaterials = $spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->get();
                if ($unfinishedMaterials->count() > 0) {
                    $matNames = $unfinishedMaterials->map(function ($m) {
                        $nama = $m->barangMentah ? $m->barangMentah->nama_barang : "Item #{$m->barang_mentah_id}";
                        $kurang = number_format((float)$m->total_kebutuhan - (float)$m->total_keluar_gudang, 2, ',', '.');
                        return "{$nama} (kurang {$kurang})";
                    })->implode(', ');
                    return response()->json([
                        'success' => false,
                        'message' => "Penerimaan Barang Jadi Ditolak! Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum 100% selesai (Material belum lengkap: {$matNames}) dan belum di-Closing oleh Accounting!"
                    ], 422);
                }

                $statusText = $spk->material_closing_status === 'Menunggu Closing' 
                    ? 'sedang Menunggu Otorisasi Closing oleh Accounting' 
                    : 'belum di-Closing & Jurnalnya belum diterbitkan';
                return response()->json([
                    'success' => false,
                    'message' => "Penerimaan Barang Jadi Ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} {$statusText}. Harap selesaikan Otorisasi Closing Pengeluaran Bahan Baku terlebih dahulu agar Jurnal Persediaan Bahan Baku terbit!"
                ], 422);
            }

            // 2. Validasi: Realisasi Tahapan Produksi wajib 100% selesai
            $incompleteStages = $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            });
            if ($spk->tahapans->count() === 0 || $incompleteStages->count() > 0) {
                $stageNames = $incompleteStages->count() > 0 
                    ? $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ')
                    : 'Belum ada tahapan';
                return response()->json([
                    'success' => false,
                    'message' => "Penerimaan Barang Jadi Ditolak! Proses Realisasi Produksi SPK #{$spk->nomor_spk} belum 100% selesai (Tahapan belum selesai: {$stageNames}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu sebelum menerima barang jadi!"
                ], 422);
            }

            $targetQty = (float)$spk->jumlah_rencana;
            $selesaiQty = (float)SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)->whereNull('deleted_at')->sum('jumlah_hasil_baik');
            $rejectQty = (float)SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)->whereNull('deleted_at')->sum('jumlah_reject');
            $sisaTarget = max(0, round($targetQty - $selesaiQty, 4));

            $hppPerUnit = $this->calculateSpkUnitHpp($spk);

            // Gudang default hasil produksi (Gudang Barang Jadi) disaring ketat berdasarkan entitas aktif
            $gudangQuery = MasterGudang::forActiveCompany()->active();
            if (!empty($activeBranchId)) {
                $gudangQuery->where('cabang_id', $activeBranchId);
            } else {
                $gudangQuery->whereNull('cabang_id');
            }

            $defaultGudang = (clone $gudangQuery)->where('nama_gudang', 'like', '%Barang Jadi%')->first()
                ?: (clone $gudangQuery)->first();

            // Calculate ONLY actual material cost (without TK/overhead) for HPP adjustment form display
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);

            // Auto-generate suggested batch number: use SPK nomor_batch if defined, otherwise generate fallback BAT-SPK-YYYYMMDD
            $dateStr = date('Ymd');
            $spkClean = preg_replace('/[^A-Za-z0-9]/', '', $spk->nomor_spk);
            $suggestedBatchNo = $spk->nomor_batch ?: "BAT-{$spkClean}-{$dateStr}";

            return response()->json([
                'success'                 => true,
                'spk'                     => $spk,
                'target_qty'              => $targetQty,
                'jumlah_selesai'          => $selesaiQty,
                'jumlah_reject'           => $rejectQty,
                'sisa_target'             => $sisaTarget,
                'total_material_cost'     => round($actualMaterialCost, 4),
                'estimasi_hpp_per_unit'   => $hppPerUnit,
                'is_hpp_closed'           => (bool)$spk->is_hpp_closed,
                'hpp_closed_at'           => $spk->hpp_closed_at ? $spk->hpp_closed_at->format('d/m/Y H:i') : null,
                'suggested_batch_no'      => $suggestedBatchNo,
                'spk_nomor_batch'         => $spk->nomor_batch,
                'spk_serial_numbers'      => $spk->serial_numbers,
                'default_tanggal_produksi'=> date('Y-m-d'),
                'default_gudang_id'       => $defaultGudang ? $defaultGudang->id : null,
                'default_gudang_kode'     => $defaultGudang ? $defaultGudang->kode_gudang : null,
                'default_gudang_nama'     => $defaultGudang ? $defaultGudang->nama_gudang : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('getSpkDetail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get metadata + recap for SPK finished goods history modal.
     */
    public function showSpkHistory($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat riwayat SPK.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()
                ->with(['barangJadi.satuan', 'satuan'])
                ->findOrFail($spkId);

            $totalSesi = SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->count();

            $totalHasilBaik = (float)SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $totalReject = (float)SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('jumlah_reject');

            $totalNilaiHpp = (float)SpkPenerimaanBarangJadi::forActiveCompany()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('total_nilai_hpp');

            return response()->json([
                'success'           => true,
                'spk'               => $spk,
                'total_sesi'        => $totalSesi,
                'total_hasil_baik'  => $totalHasilBaik,
                'total_reject'      => $totalReject,
                'total_nilai_hpp'   => $totalNilaiHpp,
            ]);
        } catch (\Throwable $e) {
            Log::error('showSpkHistory error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat riwayat SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Paginated history sessions for finished goods receipts (25/page).
     */
    public function getSpkHistorySessions(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi') && !$user->can('lihat-jurnal-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);
            $perPage = (int)$request->get('per_page', 25);
            $perPage = max(5, min(100, $perPage));

            $sessions = SpkPenerimaanBarangJadi::forActiveCompany()
                ->where('spk_id', $spkId)
                ->with([
                    'gudang:id,kode_gudang,nama_gudang',
                    'lokasiRak:id,kode_rak,nama_rak',
                    'barangJadi:id,kode_barang,nama_barang,satuan_id',
                    'barangJadi.satuan:id,nama_satuan',
                    'satuan:id,nama_satuan',
                    'penerima:id,nama_lengkap',
                    'penyerah:id,nama_lengkap',
                    'creator:id,nama_lengkap',
                    'approvedBy:id,nama_lengkap',
                    'rejectedBy:id,nama_lengkap',
                    'serials:id,penerimaan_id,serial_number,status,catatan',
                    'jurnal.details.account:id,code,name',
                ])
                ->latest('id')
                ->paginate($perPage);

            return response()->json([
                'success'      => true,
                'data'         => $sessions->items(),
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'per_page'     => $sessions->perPage(),
                'total'        => $sessions->total(),
                'from'         => $sessions->firstItem(),
                'to'           => $sessions->lastItem(),
            ]);
        } catch (\Throwable $e) {
            Log::error('getSpkHistorySessions error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat sesi riwayat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Suggest unique sequential serial numbers continuing from the last sequence in the database for the given Lot/prefix.
     */
    public function suggestSerials(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-penerimaan-barang-jadi') && !$user->can('edit-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()->with('barangJadi')->findOrFail($spkId);
            $qty = (int)$request->get('qty', 1);
            $qty = max(1, min(500, $qty));

            $rawLot = trim((string)$request->get('lot', ''));
            if (empty($rawLot)) {
                $bKode = $spk->barangJadi ? preg_replace('/[^A-Za-z0-9\-_]/', '', $spk->barangJadi->kode_barang) : 'PROD';
                $todayStr = date('Ymd');
                $rawLot = "{$bKode}-{$todayStr}";
            }

            $prefix = preg_replace('/[^A-Za-z0-9\-_]/', '', $rawLot);

            // Cek apakah nomor lot ini pernah dipakai oleh produk LAIN di database
            $lotUsedByOtherProduct = SpkPenerimaanBarangJadi::where('nomor_batch', $prefix)
                ->where('barang_jadi_id', '!=', $spk->barang_jadi_id)
                ->whereNull('deleted_at')
                ->with('barangJadi')
                ->first();

            if ($lotUsedByOtherProduct) {
                $otherName = $lotUsedByOtherProduct->barangJadi ? "[{$lotUsedByOtherProduct->barangJadi->kode_barang}] {$lotUsedByOtherProduct->barangJadi->nama_barang}" : "Produk Lain";
                return response()->json([
                    'success' => false,
                    'message' => "Nomor Lot/Batch '{$prefix}' sudah digunakan pada produk lain ({$otherName})! Harap gunakan Nomor Lot yang berbeda."
                ], 422);
            }

            // Cari Serial Number yang sudah pernah ada dengan awalan prefix ini untuk produk ini
            $existingSerials = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $spk->barang_jadi_id)
                ->where('serial_number', 'like', "{$prefix}-%")
                ->pluck('serial_number');

            $maxSeq = 0;
            foreach ($existingSerials as $sn) {
                if (preg_match('/-(\d+)$/', $sn, $matches)) {
                    $seq = (int)$matches[1];
                    if ($seq > $maxSeq) {
                        $maxSeq = $seq;
                    }
                }
            }

            $qty = max(1, min(100000, (int)$qty));
            $startSeq = $maxSeq + 1;
            $endSeq = $maxSeq + $qty;
            $padLen = max(3, strlen((string)$endSeq));

            $serials = [];
            for ($i = $startSeq; $i <= $endSeq; $i++) {
                $pad = str_pad($i, $padLen, '0', STR_PAD_LEFT);
                $serials[] = "{$prefix}-{$pad}";
            }

            return response()->json([
                'success'   => true,
                'prefix'    => $prefix,
                'lot'       => $prefix,
                'start_seq' => $startSeq,
                'end_seq'   => $endSeq,
                'last_seq'  => $maxSeq,
                'serials'   => $serials,
                'raw_text'  => implode("\n", $serials),
                'total'     => count($serials),
            ]);
        } catch (\Throwable $e) {
            Log::error('suggestSerials error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat serial number: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store new Finished Goods Receipt (Penerimaan Barang Jadi).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menerima hasil produksi.'], 403);
        }

        $request->validate([
            'spk_id'                => 'required|exists:spk_produksi,id',
            'gudang_id'             => 'required|exists:master_gudang,id',
            'lokasi_rak_id'         => 'nullable|exists:master_lokasi_rak,id',
            'nomor_batch'           => 'nullable|string|max:50',
            'tanggal_penerimaan'   => 'required|date',
            'tanggal_produksi'     => 'nullable|date',
            'tanggal_kedaluwarsa'  => 'nullable|date',
            'jumlah_hasil_baik'     => 'required|numeric|min:0.0001',
            'jumlah_reject'        => 'nullable|numeric|min:0',
            'penerima_karyawan_id'  => 'nullable|exists:karyawans,id',
            'penerima_nama'         => 'required|string|max:100',
            'penyerah_karyawan_id'  => 'nullable|exists:karyawans,id',
            'penyerah_nama'         => 'required|string|max:100',
            'serial_numbers'        => 'nullable|string',
            'catatan'               => 'nullable|string|max:255',
        ], [
            'jumlah_hasil_baik.required' => 'Jumlah hasil produksi baik wajib diisi!',
            'jumlah_hasil_baik.min'      => 'Jumlah hasil produksi baik minimal 0,0001!',
            'penerima_nama.required'     => 'Nama penerima / petugas gudang wajib diisi!',
            'penyerah_nama.required'     => 'Nama penyerah / operator QC wajib diisi!',
        ]);

        $spk = SpkProduksi::forActiveCompany()->with(['tahapans', 'materials.barangMentah', 'barangJadi'])->findOrFail($request->spk_id);

        // 1. Validasi: Pengeluaran Bahan Baku wajib ada dan sudah di-Closing oleh Accounting (atau 100% lengkap)
        if (!$spk->pengeluaranBahans()->whereNull('deleted_at')->exists()) {
            return response()->json([
                'message' => "Penerimaan ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum dicatat dari gudang. Harap selesaikan pengeluaran bahan terlebih dahulu!"
            ], 422);
        }

        $isMaterialClosed = $spk->is_material_closed || $spk->material_closing_status === 'Closed';

        if (!$isMaterialClosed) {
            $unfinishedMaterials = $spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->get();
            if ($unfinishedMaterials->count() > 0) {
                $matNames = $unfinishedMaterials->map(function ($m) {
                    $nama = $m->barangMentah ? $m->barangMentah->nama_barang : "Item #{$m->barang_mentah_id}";
                    $kurang = number_format((float)$m->total_kebutuhan - (float)$m->total_keluar_gudang, 2, ',', '.');
                    return "{$nama} (kurang {$kurang})";
                })->implode(', ');
                return response()->json([
                    'message' => "Penerimaan ditolak! Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum 100% selesai (Material belum lengkap: {$matNames}) dan belum di-Closing oleh Accounting!"
                ], 422);
            }

            $statusText = $spk->material_closing_status === 'Menunggu Closing' 
                ? 'sedang Menunggu Otorisasi Closing oleh Accounting' 
                : 'belum di-Closing & Jurnalnya belum diterbitkan';
            return response()->json([
                'message' => "Penerimaan ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} {$statusText}. Harap selesaikan Otorisasi Closing Pengeluaran Bahan Baku terlebih dahulu agar HPP dan Jurnal Persediaan akurat!"
            ], 422);
        }

        // 2. Validasi: Realisasi Tahapan Produksi wajib 100% selesai
        $incompleteStages = $spk->tahapans->filter(function ($t) {
            return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
        });
        if ($spk->tahapans->count() === 0 || $incompleteStages->count() > 0) {
            $stageNames = $incompleteStages->count() > 0 
                ? $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ')
                : 'Belum ada tahapan';
            return response()->json([
                'message' => "Penerimaan ditolak! Proses Realisasi Produksi SPK #{$spk->nomor_spk} belum 100% selesai (Tahapan belum selesai: {$stageNames}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu!"
            ], 422);
        }

        // 3. Validasi Keunikan Nomor Lot/Batch: Jangan sampai Nomor Lot yang sama dipakai pada Produk Lain
        $nomorBatch = $request->filled('nomor_batch') ? trim($request->nomor_batch) : null;
        if ($nomorBatch) {
            $lotUsedByOtherProduct = SpkPenerimaanBarangJadi::where('nomor_batch', $nomorBatch)
                ->where('barang_jadi_id', '!=', $spk->barang_jadi_id)
                ->whereNull('deleted_at')
                ->with('barangJadi')
                ->first();

            if ($lotUsedByOtherProduct) {
                $otherName = $lotUsedByOtherProduct->barangJadi ? "[{$lotUsedByOtherProduct->barangJadi->kode_barang}] {$lotUsedByOtherProduct->barangJadi->nama_barang}" : "Produk Lain";
                return response()->json([
                    'message' => "Penerimaan ditolak! Nomor Lot/Batch '{$nomorBatch}' sudah pernah digunakan untuk produk lain ({$otherName}). Harap gunakan Nomor Lot yang unik!"
                ], 422);
            }
        }

        // 4. Validasi Keunikan Serial Number (SN)
        $cleanSerials = [];
        if ($request->filled('serial_numbers')) {
            $rawSerials = preg_split('/[\r\n,]+/', $request->serial_numbers);
            $seenInInput = [];
            $inputDuplicates = [];

            foreach ($rawSerials as $sn) {
                $snTrim = trim($sn);
                if (empty($snTrim)) continue;

                $key = strtolower($snTrim);
                if (isset($seenInInput[$key])) {
                    $inputDuplicates[] = $snTrim;
                } else {
                    $seenInInput[$key] = true;
                    $cleanSerials[] = $snTrim;
                }
            }

            // Cek duplikasi internal di dalam form
            if (!empty($inputDuplicates)) {
                $dupList = implode(', ', array_unique($inputDuplicates));
                return response()->json([
                    'message' => "Penerimaan ditolak! Terdapat Serial Number ganda di dalam input Anda: {$dupList}. Setiap unit produk harus memiliki Serial Number unik yang berbeda!"
                ], 422);
            }

            // Cek duplikasi ke database: SN tidak boleh ada yang sama untuk produk ini di database
            $existingInDb = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $spk->barang_jadi_id)
                ->whereIn('serial_number', $cleanSerials)
                ->pluck('serial_number')
                ->toArray();

            if (!empty($existingInDb)) {
                $dbDupList = implode(', ', array_slice($existingInDb, 0, 5));
                $moreCount = count($existingInDb) > 5 ? ' dan ' . (count($existingInDb) - 5) . ' SN lainnya' : '';
                return response()->json([
                    'message' => "Penerimaan ditolak! Serial Number berikut sudah pernah terdaftar di database: {$dbDupList}{$moreCount}. Harap gunakan nomor seri yang belum pernah terpakai!"
                ], 422);
            }
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: ($user->company_id ?? 1);

        // Pastikan entitas transaksi mengikuti pemilik SPK / sesi aktif
        $isBranch = !empty($spk->branch_id) || !empty($activeBranchId);
        $finalBranchId  = $isBranch ? ($spk->branch_id ?: $activeBranchId) : null;
        $finalCompanyId = $isBranch ? null : ($spk->company_id ?: $activeCompanyId);

        // Security check: Pastikan gudang yang dipilih sesuai dengan entitas (Cabang vs PT)
        $selectedGudang = MasterGudang::findOrFail($request->gudang_id);
        if ($isBranch) {
            if ($selectedGudang->cabang_id != $finalBranchId) {
                return response()->json([
                    'message' => 'Gudang yang dipilih tidak sesuai dengan Cabang aktif!'
                ], 422);
            }
        } else {
            if (!empty($selectedGudang->cabang_id)) {
                return response()->json([
                    'message' => 'Gudang yang dipilih adalah milik Cabang, bukan PT Pusat!'
                ], 422);
            }
        }

        $qtyGood = round((float)$request->jumlah_hasil_baik, 4);
        $qtyReject = round((float)($request->jumlah_reject ?: 0), 4);

        // Validasi Keamanan: Kuantitas Hasil Baik tidak boleh melebihi Sisa Target SPK
        $targetQty = (float)$spk->jumlah_rencana;
        $alreadyReceived = (float)SpkPenerimaanBarangJadi::forActiveCompany()
            ->where('spk_id', $spk->id)
            ->whereNull('deleted_at')
            ->sum('jumlah_hasil_baik');
        $sisaTarget = max(0, round($targetQty - $alreadyReceived, 4));

        if ($qtyGood > $sisaTarget) {
            $satName = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi?->satuan?->nama_satuan ?? 'Pcs');
            return response()->json([
                'message' => "Penerimaan ditolak! Jumlah hasil baik (" . number_format($qtyGood, 0, ',', '.') . " {$satName}) melebihi sisa kuota target SPK yang belum diterima (" . number_format($sisaTarget, 0, ',', '.') . " {$satName})."
            ], 422);
        }

        // Auto generate document number: PBJ-CBG/YYYY/MM/XXXXX atau PBJ-PT/YYYY/MM/XXXXX
        $yearMonth = date('Y/m', strtotime($request->tanggal_penerimaan));
        $prefix = $isBranch ? "PBJ-CBG/{$yearMonth}/" : "PBJ-PT/{$yearMonth}/";
        $legacyPrefix = "PBJ/{$yearMonth}/";

        $lastNo = SpkPenerimaanBarangJadi::withTrashed()
            ->where(function ($q) use ($prefix, $legacyPrefix) {
                $q->where('nomor_penerimaan', 'like', "{$prefix}%")
                  ->orWhere('nomor_penerimaan', 'like', "{$legacyPrefix}%");
            })
            ->orderBy('id', 'desc')
            ->value('nomor_penerimaan');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        do {
            $nomorPenerimaan = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = SpkPenerimaanBarangJadi::withTrashed()
                ->where('nomor_penerimaan', $nomorPenerimaan)
                ->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        DB::beginTransaction();
        try {
            $nomorBatch = $request->filled('nomor_batch') ? trim($request->nomor_batch) : null;
            $tanggalProduksi = $request->filled('tanggal_produksi') ? $request->tanggal_produksi : $request->tanggal_penerimaan;
            $tanggalKedaluwarsa = $request->filled('tanggal_kedaluwarsa') ? $request->tanggal_kedaluwarsa : null;
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);

            // Create Penerimaan in 'Menunggu HPP' status (Fisik belum masuk ke Stok Aktif Gudang)
            $penerimaan = SpkPenerimaanBarangJadi::create([
                'company_id'           => $finalCompanyId,
                'branch_id'            => $finalBranchId,
                'nomor_penerimaan'     => $nomorPenerimaan,
                'nomor_batch'          => $nomorBatch,
                'tanggal_penerimaan'   => $request->tanggal_penerimaan,
                'tanggal_produksi'     => $tanggalProduksi,
                'tanggal_kedaluwarsa'  => $tanggalKedaluwarsa,
                'spk_id'               => $spk->id,
                'barang_jadi_id'       => $spk->barang_jadi_id,
                'gudang_id'            => $request->gudang_id,
                'lokasi_rak_id'        => $request->lokasi_rak_id ?: null,
                'satuan_id'            => $spk->satuan_id,
                'jumlah_hasil_baik'    => $qtyGood,
                'jumlah_reject'        => $qtyReject,
                'hpp_satuan'           => 0,
                'total_nilai_hpp'      => 0,
                'biaya_bahan_baku'     => round($actualMaterialCost, 4),
                'biaya_tenaga_kerja'   => 0,
                'biaya_overhead'       => 0,
                'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                'penerima_nama'        => trim($request->penerima_nama),
                'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                'penyerah_nama'        => $request->penyerah_nama ? trim($request->penyerah_nama) : null,
                'status'               => 'Menunggu HPP',
                'catatan'              => $request->catatan,
                'created_by'           => $user->id,
            ]);

            // Save Serial Numbers if supplied
            if ($request->filled('serial_numbers')) {
                $rawSerials = preg_split('/[\r\n,]+/', $request->serial_numbers);
                foreach ($rawSerials as $sn) {
                    $snTrim = trim($sn);
                    if (empty($snTrim)) continue;
                    SpkPenerimaanBarangJadiSerial::create([
                        'penerimaan_id'  => $penerimaan->id,
                        'spk_id'         => $spk->id,
                        'barang_jadi_id' => $spk->barang_jadi_id,
                        'nomor_batch'    => $nomorBatch,
                        'serial_number'  => $snTrim,
                        'status'         => 'Tersedia',
                    ]);
                }
            }

            DB::commit();

            // Send WhatsApp Notification to Finance/Accounting / Owner
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingSubmissionNotification($spk, $user, "Pengajuan Penerimaan {$nomorPenerimaan} ({$qtyGood} item) menunggu perhitungan HPP.");
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification error: ' . $we->getMessage());
            }

            return response()->json([
                'success'       => true,
                'message'       => "Pengajuan penerimaan hasil produksi {$nomorPenerimaan} ({$qtyGood} item) berhasil dicatat! Status: Menunggu Perhitungan HPP & Approval Accounting.",
                'id'            => $penerimaan->id,
                'spk_id'        => $spk->id,
                'nomor_spk'     => $spk->nomor_spk,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error store penerimaan barang jadi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal mencatat pengajuan penerimaan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview HPP calculation data for modal approval.
     */
    public function previewHppSession($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->with(['spk.materials.barangMentah', 'spk.bom', 'spk.barangJadi.satuan', 'spk.satuan', 'gudang', 'lokasiRak', 'penerima', 'penyerah', 'serials'])
            ->findOrFail($id);

        $spk = $penerimaan->spk;
        $actualMaterialCost = $this->calculateActualMaterialCost($spk);
        $targetQty = (float)($spk->jumlah_rencana ?: 1);
        $qtyGood   = (float)($penerimaan->jumlah_hasil_baik ?: 1);

        $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->sum('jumlah_hasil_baik');
        $divisorQty = ($selesaiQty > 0) ? $selesaiQty : ($targetQty > 0 ? $targetQty : $qtyGood);

        $biayaTK = (float)($spk->estimasi_biaya_tk ?: 0);
        $biayaOH = (float)($spk->estimasi_biaya_overhead ?: 0);

        if ($biayaTK <= 0 && $spk->bom) {
            $targetHasilBom = max((float)$spk->bom->jumlah_hasil, 0.0001);
            $biayaTK = round(((float)$spk->bom->biaya_tenaga_kerja / $targetHasilBom) * $targetQty, 4);
        }
        if ($biayaOH <= 0 && $spk->bom) {
            $targetHasilBom = max((float)$spk->bom->jumlah_hasil, 0.0001);
            $biayaOH = round(((float)$spk->bom->biaya_overhead / $targetHasilBom) * $targetQty, 4);
        }

        $totalBiaya = $actualMaterialCost + $biayaTK + $biayaOH;
        $simulasiHpp = round($totalBiaya / $divisorQty, 4);
        $satuanName = $spk->barangJadi?->satuan?->nama_satuan ?: ($spk->satuan?->nama_satuan ?: 'Pcs');

        return response()->json([
            'success'            => true,
            'penerimaan'         => $penerimaan,
            'spk'                => $spk,
            'satuan'             => $satuanName,
            'biaya_bahan_baku'   => $actualMaterialCost,
            'biaya_bahan_riil'   => $actualMaterialCost,
            'biaya_tenaga_kerja' => $biayaTK,
            'biaya_tk_default'   => $biayaTK,
            'biaya_overhead'     => $biayaOH,
            'biaya_oh_default'   => $biayaOH,
            'total_biaya'        => $totalBiaya,
            'simulasi_hpp'       => $simulasiHpp,
            'target_qty'         => $targetQty,
            'qty_baik'           => $qtyGood,
        ]);
    }

    /**
     * Calculate HPP, insert stock, write KartuStok, post accounting journal, and approve session.
     */
    public function approveAndCalculateHpp(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghitung HPP & menyetujui penerimaan barang jadi.'], 403);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->with(['spk.tahapans', 'spk.materials', 'barangJadi', 'gudang'])
            ->findOrFail($id);

        if ($penerimaan->status === 'Diterima') {
            return response()->json(['message' => 'Transaksi penerimaan ini sudah disetujui sebelumnya & stok sudah masuk.'], 422);
        }

        $request->validate([
            'biaya_tenaga_kerja' => 'required|numeric|min:0',
            'biaya_overhead'     => 'required|numeric|min:0',
            'hpp_satuan'         => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $spk = $penerimaan->spk;
            $qtyGood = (float)$penerimaan->jumlah_hasil_baik;
            $qtyReject = (float)$penerimaan->jumlah_reject;

            // 1. Kalkulasi Biaya Riil
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);
            $biayaTK = round((float)$request->biaya_tenaga_kerja, 4);
            $biayaOH = round((float)$request->biaya_overhead, 4);
            $totalBiaya = round($actualMaterialCost + $biayaTK + $biayaOH, 4);

            $targetQty  = (float)$spk->jumlah_rencana;
            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->sum('jumlah_hasil_baik');
            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : ($targetQty > 0 ? $targetQty : $qtyGood);
            if ($divisorQty <= 0) $divisorQty = 1;

            $finalHppSatuan = ($request->filled('hpp_satuan') && (float)$request->hpp_satuan > 0)
                ? round((float)$request->hpp_satuan, 4)
                : round($totalBiaya / $divisorQty, 4);

            $finalTotalNilaiHpp = round($qtyGood * $finalHppSatuan, 4);

            // Update estimasi SPK
            $spk->update([
                'estimasi_biaya_bahan'    => round($actualMaterialCost, 4),
                'estimasi_biaya_tk'       => $biayaTK,
                'estimasi_biaya_overhead' => $biayaOH,
                'estimasi_total_biaya'    => $totalBiaya,
            ]);

            // Update Status Penerimaan menjadi 'Diterima' (Approved)
            $penerimaan->update([
                'status'             => 'Diterima',
                'hpp_satuan'         => $finalHppSatuan,
                'total_nilai_hpp'    => $finalTotalNilaiHpp,
                'biaya_bahan_baku'   => round($actualMaterialCost, 4),
                'biaya_tenaga_kerja' => $biayaTK,
                'biaya_overhead'     => $biayaOH,
                'approved_by'        => $user->id,
                'approved_at'        => now(),
                'rejected_by'        => null,
                'rejected_at'        => null,
                'reject_reason'      => null,
            ]);

            // Propagate final HPP rate to all FG receipt records for this SPK, update StokGudang, KartuStok, and Jurnal
            self::adjustHppForSpkWithRate($spk, $finalHppSatuan);

            // 6. Update Counter SPK (Hanya Sesi yang 'Diterima')
            $activeSessions = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->where('status', 'Diterima');
            $realSelesai = (float) $activeSessions->sum('jumlah_hasil_baik');
            $realReject  = (float) $activeSessions->sum('jumlah_reject');

            $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            })->count() === 0;

            $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
            $barangMasukSelesai = ($realSelesai >= $targetQty && $targetQty > 0);

            $spk->update([
                'jumlah_selesai'    => $realSelesai,
                'jumlah_reject'     => $realReject,
                'status'            => ($realisasiSelesai && $bahanSelesai && $barangMasukSelesai) ? 'Completed' : 'In Progress',
                'is_fg_closed'      => $barangMasukSelesai,
                'is_hpp_closed'     => $barangMasukSelesai,
                'fg_closing_status' => $barangMasukSelesai ? 'Closed' : 'Open',
                'fg_closed_at'      => $barangMasukSelesai ? now() : null,
                'fg_closed_by'      => $barangMasukSelesai ? $user->id : null,
                'hpp_closed_at'     => $barangMasukSelesai ? now() : null,
                'hpp_closed_by'     => $barangMasukSelesai ? $user->id : null,
            ]);

            DB::commit();

            // WhatsApp Notification to Creator
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingApprovedNotification($spk, $user);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp approval notification error: ' . $we->getMessage());
            }

            return response()->json([
                'success'     => true,
                'message'     => "Penerimaan {$penerimaan->nomor_penerimaan} berhasil disetujui, HPP Rp " . number_format($finalHppSatuan, 2, ',', '.') . " ditetapkan, stok bertambah ke gudang & jurnal akuntansi otomatis diposting!",
                'id'          => $penerimaan->id,
                'hpp_satuan'  => $finalHppSatuan,
                'total_nilai' => $finalTotalNilaiHpp,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('approveAndCalculateHpp error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal menyetujui dan menghitung HPP: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject Finished Goods Receipt submission.
     */
    public function rejectSession(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menolak penerimaan barang jadi.'], 403);
        }

        $request->validate([
            'reject_reason' => 'required|string|max:255',
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi!'
        ]);

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()->with('spk')->findOrFail($id);

        if ($penerimaan->status === 'Diterima') {
            return response()->json(['message' => 'Penerimaan yang sudah diterima & masuk stok tidak dapat ditolak secara langsung. Batalkan sesi terlebih dahulu!'], 422);
        }

        $penerimaan->update([
            'status'         => 'Ditolak',
            'rejected_by'    => $user->id,
            'rejected_at'    => now(),
            'reject_reason'  => trim($request->reject_reason),
        ]);

        if ($penerimaan->spk) {
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingRejectedNotification($penerimaan->spk, $user, $request->reject_reason);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp reject notification error: ' . $we->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Pengajuan penerimaan {$penerimaan->nomor_penerimaan} telah ditolak & dikembalikan ke tim produksi."
        ], 200);
    }

    /**
     * Show detail of Finished Goods Receipt transaction.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses.');
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->with([
                'company',
                'branch',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'barangJadi.satuan',
                'gudang',
                'lokasiRak',
                'penerima',
                'penyerah',
                'creator',
                'serials',
                'jurnal.details.account'
            ])
            ->findOrFail($id);

        return response()->json(['success' => true, 'penerimaan' => $penerimaan]);
    }

    /**
     * Update Finished Goods Receipt transaction with inventory & accounting rebalancing.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-penerimaan-barang-jadi') && !$user->can('tambah-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.']);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()->with(['spk.tahapans', 'spk.materials.barangMentah'])->findOrFail($id);

        $request->validate([
            'gudang_id'           => 'required|exists:master_gudang,id',
            'lokasi_rak_id'       => 'nullable|exists:master_lokasi_rak,id',
            'nomor_batch'         => 'nullable|string|max:50',
            'tanggal_penerimaan' => 'required|date',
            'tanggal_produksi'   => 'nullable|date',
            'tanggal_kedaluwarsa'=> 'nullable|date',
            'jumlah_hasil_baik'   => 'required|numeric|min:0.0001',
            'jumlah_reject'      => 'nullable|numeric|min:0',
            'hpp_satuan'          => 'nullable|numeric|min:0',
            'penerima_karyawan_id'=> 'nullable|exists:karyawans,id',
            'penerima_nama'       => 'required|string|max:100',
            'penyerah_karyawan_id'=> 'nullable|exists:karyawans,id',
            'penyerah_nama'       => 'required|string|max:100',
            'serial_numbers'      => 'nullable|string',
            'catatan'             => 'nullable|string|max:255',
        ], [
            'jumlah_hasil_baik.required' => 'Jumlah hasil produksi baik wajib diisi!',
            'jumlah_hasil_baik.min'      => 'Jumlah hasil produksi baik minimal 0,0001!',
            'penerima_nama.required'     => 'Nama penerima / petugas gudang wajib diisi!',
            'penyerah_nama.required'     => 'Nama penyerah / operator QC wajib diisi!',
        ]);

        $activeBranchId  = session('active_branch_id');
        $isBranch = !empty($penerimaan->branch_id) || !empty($activeBranchId);
        $finalBranchId  = $isBranch ? ($penerimaan->branch_id ?: $activeBranchId) : null;
        $finalCompanyId = $isBranch ? null : ($penerimaan->company_id ?: (session('active_company_id') ?: 1));

        // Security check: Pastikan gudang yang dipilih sesuai dengan entitas (Cabang vs PT)
        $selectedGudang = MasterGudang::findOrFail($request->gudang_id);
        if ($isBranch) {
            if ($selectedGudang->cabang_id != $finalBranchId) {
                return response()->json([
                    'message' => 'Gudang yang dipilih tidak sesuai dengan Cabang aktif!'
                ], 422);
            }
        } else {
            if (!empty($selectedGudang->cabang_id)) {
                return response()->json([
                    'message' => 'Gudang yang dipilih adalah milik Cabang, bukan PT Pusat!'
                ], 422);
            }
        }

        $qtyGoodOld = (float)$penerimaan->jumlah_hasil_baik;
        $qtyRejectOld = (float)$penerimaan->jumlah_reject;
        $spk = $penerimaan->spk;

        if ($spk) {
            if (!$spk->pengeluaranBahans()->whereNull('deleted_at')->exists()) {
                return response()->json([
                    'message' => "Update ditolak! SPK #{$spk->nomor_spk} belum mencatat transaksi Pengeluaran Bahan Baku dari gudang."
                ], 422);
            }

            $isMaterialClosed = $spk->is_material_closed || $spk->material_closing_status === 'Closed';

            if (!$isMaterialClosed) {
                $unfinishedMaterials = $spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->get();
                if ($unfinishedMaterials->count() > 0) {
                    $matNames = $unfinishedMaterials->map(function ($m) {
                        $nama = $m->barangMentah ? $m->barangMentah->nama_barang : "Item #{$m->barang_mentah_id}";
                        $kurang = number_format((float)$m->total_kebutuhan - (float)$m->total_keluar_gudang, 2, ',', '.');
                        return "{$nama} (kurang {$kurang})";
                    })->implode(', ');
                    return response()->json([
                        'message' => "Update ditolak! Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum 100% selesai (Material belum lengkap: {$matNames}) dan belum di-Closing oleh Accounting!"
                    ], 422);
                }

                $statusText = $spk->material_closing_status === 'Menunggu Closing' 
                    ? 'sedang Menunggu Otorisasi Closing oleh Accounting' 
                    : 'belum di-Closing & Jurnalnya belum diterbitkan';
                return response()->json([
                    'message' => "Update ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} {$statusText}. Harap selesaikan Otorisasi Closing Pengeluaran Bahan Baku terlebih dahulu!"
                ], 422);
            }

            $incompleteStages = $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            });

            if ($spk->tahapans->count() === 0 || $incompleteStages->count() > 0) {
                $names = $incompleteStages->count() > 0 
                    ? $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ')
                    : 'Belum ada tahapan';
                return response()->json([
                    'message' => "Update ditolak! Proses Realisasi Produksi SPK #{$spk->nomor_spk} belum selesai sepenuhnya (tahapan: {$names}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu!"
                ], 422);
            }
        }

        // Validasi Keunikan Nomor Lot/Batch jika diubah: Jangan sampai dipakai pada Produk Lain
        $nomorBatchNew = $request->filled('nomor_batch') ? trim($request->nomor_batch) : null;
        if ($nomorBatchNew) {
            $lotUsedByOtherProduct = SpkPenerimaanBarangJadi::where('nomor_batch', $nomorBatchNew)
                ->where('barang_jadi_id', '!=', $penerimaan->barang_jadi_id)
                ->where('id', '!=', $penerimaan->id)
                ->whereNull('deleted_at')
                ->with('barangJadi')
                ->first();

            if ($lotUsedByOtherProduct) {
                $otherName = $lotUsedByOtherProduct->barangJadi ? "[{$lotUsedByOtherProduct->barangJadi->kode_barang}] {$lotUsedByOtherProduct->barangJadi->nama_barang}" : "Produk Lain";
                return response()->json([
                    'message' => "Update ditolak! Nomor Lot/Batch '{$nomorBatchNew}' sudah pernah digunakan untuk produk lain ({$otherName}). Harap gunakan Nomor Lot yang unik!"
                ], 422);
            }
        }

        // Validasi Keunikan Serial Number (SN) jika diisi
        $cleanSerials = [];
        if ($request->filled('serial_numbers')) {
            $rawSerials = preg_split('/[\r\n,]+/', $request->serial_numbers);
            $seenInInput = [];
            $inputDuplicates = [];

            foreach ($rawSerials as $sn) {
                $snTrim = trim($sn);
                if (empty($snTrim)) continue;

                $key = strtolower($snTrim);
                if (isset($seenInInput[$key])) {
                    $inputDuplicates[] = $snTrim;
                } else {
                    $seenInInput[$key] = true;
                    $cleanSerials[] = $snTrim;
                }
            }

            // Cek duplikasi internal di dalam form
            if (!empty($inputDuplicates)) {
                $dupList = implode(', ', array_unique($inputDuplicates));
                return response()->json([
                    'message' => "Update ditolak! Terdapat Serial Number ganda di dalam input Anda: {$dupList}. Setiap unit produk harus memiliki Serial Number unik yang berbeda!"
                ], 422);
            }

            // Cek duplikasi ke database (kecuali data SN milik transaksi penerimaan ini sendiri)
            $existingInDb = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $penerimaan->barang_jadi_id)
                ->where('penerimaan_id', '!=', $penerimaan->id)
                ->whereIn('serial_number', $cleanSerials)
                ->pluck('serial_number')
                ->toArray();

            if (!empty($existingInDb)) {
                $dbDupList = implode(', ', array_slice($existingInDb, 0, 5));
                $moreCount = count($existingInDb) > 5 ? ' dan ' . (count($existingInDb) - 5) . ' SN lainnya' : '';
                return response()->json([
                    'message' => "Update ditolak! Serial Number berikut sudah pernah terdaftar pada transaksi penerimaan lain: {$dbDupList}{$moreCount}. Harap gunakan nomor seri yang belum pernah terpakai!"
                ], 422);
            }
        }

        $qtyGoodNew = round((float)$request->jumlah_hasil_baik, 4);
        $qtyRejectNew = round((float)($request->jumlah_reject ?: 0), 4);
        $wasApproved  = ($penerimaan->status === 'Diterima');

        // Validasi Keamanan: Kuantitas Hasil Baik tidak boleh melebihi Sisa Kuota SPK
        if ($spk) {
            $targetQty = (float)$spk->jumlah_rencana;
            $alreadyReceivedOther = (float)SpkPenerimaanBarangJadi::forActiveCompany()
                ->where('spk_id', $spk->id)
                ->where('id', '!=', $penerimaan->id)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');
            $sisaTarget = max(0, round($targetQty - $alreadyReceivedOther, 4));

            if ($qtyGoodNew > $sisaTarget) {
                $satName = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi?->satuan?->nama_satuan ?? 'Pcs');
                return response()->json([
                    'message' => "Pembaruan ditolak! Jumlah hasil baik (" . number_format($qtyGoodNew, 0, ',', '.') . " {$satName}) melebihi sisa kuota target SPK (" . number_format($sisaTarget, 0, ',', '.') . " {$satName})."
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Update SPK wages & overhead if supplied from the receipt form
            if ($spk) {
                $tk = $request->filled('biaya_tenaga_kerja') ? round((float)$request->biaya_tenaga_kerja, 4) : (float)$spk->estimasi_biaya_tk;
                $oh = $request->filled('biaya_overhead') ? round((float)$request->biaya_overhead, 4) : (float)$spk->estimasi_biaya_overhead;
                $spk->update([
                    'estimasi_biaya_tk'      => $tk,
                    'estimasi_biaya_overhead'=> $oh,
                    'estimasi_total_biaya'   => round((float)$spk->estimasi_biaya_bahan + $tk + $oh, 4)
                ]);
            }

            $nomorBatch = $request->filled('nomor_batch') ? trim($request->nomor_batch) : null;
            $tanggalProduksi = $request->filled('tanggal_produksi') ? $request->tanggal_produksi : $request->tanggal_penerimaan;
            $tanggalKedaluwarsa = $request->filled('tanggal_kedaluwarsa') ? $request->tanggal_kedaluwarsa : null;

            if ($wasApproved) {
                // Calculate HPP Satuan
                $hppSatuanNew = $request->filled('hpp_satuan') && (float)$request->hpp_satuan > 0 
                    ? round((float)$request->hpp_satuan, 4) 
                    : ((float)$penerimaan->hpp_satuan > 0 ? (float)$penerimaan->hpp_satuan : $this->calculateSpkUnitHpp($spk));
                $totalNilaiHppNew = round($qtyGoodNew * $hppSatuanNew, 4);

                // Revert Old Inventory Stock
                $stokGudangOld = StokGudang::firstOrCreate(
                    ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                    ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                );

                $stokAktualOld = (float)$stokGudangOld->stok_aktual;
                $stokAfterRevert = max(0, $stokAktualOld - $qtyGoodOld);
                $nilaiAfterRevert = max(0, (float)$stokGudangOld->nilai_persediaan - (float)$penerimaan->total_nilai_hpp);
                $hppOldAvg = $stokAfterRevert > 0 ? round($nilaiAfterRevert / $stokAfterRevert, 6) : (float)$stokGudangOld->harga_pokok_rata_rata;

                $stokGudangOld->update([
                    'stok_aktual'            => $stokAfterRevert,
                    'nilai_persediaan'       => $nilaiAfterRevert,
                    'harga_pokok_rata_rata'  => $hppOldAvg,
                ]);

                // Record Reversal of Old Stock in KartuStok for Old Gudang
                if ($qtyGoodOld > 0) {
                    KartuStok::create([
                        'barang_id'       => $penerimaan->barang_jadi_id,
                        'gudang_id'       => $penerimaan->gudang_id,
                        'tipe'            => 'Keluar',
                        'jumlah'          => $qtyGoodOld,
                        'stok_awal'       => $stokAktualOld,
                        'stok_akhir'      => $stokAfterRevert,
                        'harga_satuan'    => (float)$penerimaan->hpp_satuan,
                        'nilai_total'     => (float)$penerimaan->total_nilai_hpp,
                        'referensi_jenis' => 'Revisi Penerimaan Barang Jadi SPK',
                        'referensi_id'    => $penerimaan->id,
                        'referensi_nomor' => $penerimaan->nomor_penerimaan,
                        'keterangan'      => "Pembatalan/revisi hasil produksi lama untuk SPK #{$spk->nomor_spk}",
                    ]);
                }

                // Add New Inventory Stock in Selected Gudang
                $stokGudangNew = StokGudang::firstOrCreate(
                    ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $request->gudang_id],
                    ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                );

                $stokAwalNew = (float)$stokGudangNew->stok_aktual;
                $nilaiAwalNew = (float)$stokGudangNew->nilai_persediaan;
                $stokAkhirNew = $stokAwalNew + $qtyGoodNew;
                $nilaiAkhirNew = $nilaiAwalNew + $totalNilaiHppNew;
                $hppNewAvg = $stokAkhirNew > 0 ? round($nilaiAkhirNew / $stokAkhirNew, 6) : (float)$stokGudangNew->harga_pokok_rata_rata;

                $stokGudangNew->update([
                    'stok_aktual'            => $stokAkhirNew,
                    'nilai_persediaan'       => $nilaiAkhirNew,
                    'hpp_sebelumnya'         => (string)($stokGudangNew->harga_pokok_rata_rata ?? '0.000000'),
                    'harga_pokok_rata_rata'  => $hppNewAvg,
                    'harga_beli_sebelumnya'  => (string)($stokGudangNew->harga_beli_terakhir ?? '0.000000'),
                    'harga_beli_terakhir'    => $hppSatuanNew,
                ]);
                StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

                // Record New KartuStok entry
                KartuStok::create([
                    'barang_id'       => $penerimaan->barang_jadi_id,
                    'gudang_id'       => $request->gudang_id,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qtyGoodNew,
                    'stok_awal'       => $stokAwalNew,
                    'stok_akhir'      => $stokAkhirNew,
                    'harga_satuan'    => $hppSatuanNew,
                    'nilai_total'     => $totalNilaiHppNew,
                    'referensi_jenis' => 'Penerimaan Barang Jadi SPK',
                    'referensi_id'    => $penerimaan->id,
                    'referensi_nomor' => $penerimaan->nomor_penerimaan,
                    'keterangan'      => "Revisi hasil produksi barang jadi untuk SPK #{$spk->nomor_spk}",
                ]);

                $penerimaan->update([
                    'company_id'           => $finalCompanyId,
                    'branch_id'            => $finalBranchId,
                    'nomor_batch'          => $nomorBatch,
                    'gudang_id'            => $request->gudang_id,
                    'lokasi_rak_id'        => $request->lokasi_rak_id ?: null,
                    'tanggal_penerimaan'   => $request->tanggal_penerimaan,
                    'tanggal_produksi'     => $tanggalProduksi,
                    'tanggal_kedaluwarsa'  => $tanggalKedaluwarsa,
                    'jumlah_hasil_baik'    => $qtyGoodNew,
                    'jumlah_reject'        => $qtyRejectNew,
                    'hpp_satuan'           => $hppSatuanNew,
                    'total_nilai_hpp'      => $totalNilaiHppNew,
                    'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                    'penerima_nama'        => trim($request->penerima_nama),
                    'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                    'penyerah_nama'        => $request->penyerah_nama ? trim($request->penyerah_nama) : null,
                    'catatan'              => $request->catatan,
                    'updated_by'           => $user->id,
                ]);

                // Re-post Accounting Journal hanya jika HPP SPK sudah difinalisasi (Closed) atau jurnal sudah ada sebelumnya
                if (($spk && $spk->is_hpp_closed) || $penerimaan->jurnal()->exists()) {
                    $this->createJurnalPenerimaanBarangJadi($penerimaan);
                }
            } else {
                // Untuk sesi yang 'Menunggu HPP' atau 'Ditolak' (diajukan ulang):
                // Reset status kembali ke 'Menunggu HPP' dan kosongkan catatan penolakan
                $actualMaterialCost = $this->calculateActualMaterialCost($spk);

                $penerimaan->update([
                    'company_id'           => $finalCompanyId,
                    'branch_id'            => $finalBranchId,
                    'nomor_batch'          => $nomorBatch,
                    'gudang_id'            => $request->gudang_id,
                    'lokasi_rak_id'        => $request->lokasi_rak_id ?: null,
                    'tanggal_penerimaan'   => $request->tanggal_penerimaan,
                    'tanggal_produksi'     => $tanggalProduksi,
                    'tanggal_kedaluwarsa'  => $tanggalKedaluwarsa,
                    'jumlah_hasil_baik'    => $qtyGoodNew,
                    'jumlah_reject'        => $qtyRejectNew,
                    'hpp_satuan'           => 0,
                    'total_nilai_hpp'      => 0,
                    'biaya_bahan_baku'     => round($actualMaterialCost, 4),
                    'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                    'penerima_nama'        => trim($request->penerima_nama),
                    'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                    'penyerah_nama'        => $request->penyerah_nama ? trim($request->penyerah_nama) : null,
                    'status'               => 'Menunggu HPP',
                    'rejected_by'          => null,
                    'rejected_at'          => null,
                    'reject_reason'        => null,
                    'approved_by'          => null,
                    'approved_at'          => null,
                    'catatan'              => $request->catatan,
                    'updated_by'           => $user->id,
                ]);
            }

            // Rebalance SPK Counters accurately from database (hanya sesi yang 'Diterima')
            if ($spk) {
                $approvedSessions = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->where('status', 'Diterima');
                $realSelesai = (float) $approvedSessions->sum('jumlah_hasil_baik');
                $realReject  = (float) $approvedSessions->sum('jumlah_reject');

                $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                    return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
                })->count() === 0;

                $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
                $barangMasukSelesai = ($realSelesai >= (float)$spk->jumlah_rencana && (float)$spk->jumlah_rencana > 0);

                $spk->update([
                    'jumlah_selesai' => $realSelesai,
                    'jumlah_reject'  => $realReject,
                    'status'         => ($realisasiSelesai && $bahanSelesai && $barangMasukSelesai) ? 'Completed' : 'In Progress'
                ]);
            }

            // Sync Serial Numbers
            $penerimaan->serials()->delete();
            if ($request->filled('serial_numbers')) {
                $rawSerials = preg_split('/[\r\n,]+/', $request->serial_numbers);
                foreach ($rawSerials as $sn) {
                    $snTrim = trim($sn);
                    if (empty($snTrim)) continue;
                    SpkPenerimaanBarangJadiSerial::create([
                        'penerimaan_id'  => $penerimaan->id,
                        'spk_id'         => $spk->id,
                        'barang_jadi_id' => $spk->barang_jadi_id,
                        'nomor_batch'    => $nomorBatch,
                        'serial_number'  => $snTrim,
                        'status'         => 'Tersedia',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengajuan penerimaan {$penerimaan->nomor_penerimaan} berhasil diperbarui! Status dikembalikan ke 'Menunggu HPP' untuk dihitung HPP & di-approve.",
                'id'      => $penerimaan->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error update penerimaan barang jadi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete & revert Finished Goods Receipt transaction.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('hapus-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()->with('spk')->findOrFail($id);
        $spk = $penerimaan->spk;

        if ($spk && ($spk->is_fg_closed || $spk->fg_closing_status === 'Closed' || $spk->is_hpp_closed)) {
            return response()->json(['message' => 'SPK ini sudah di-Closing. Buka kembali (Reopen) status closing terlebih dahulu jika ingin membatalkan sesi ini!'], 422);
        }
        if ($spk && $spk->fg_closing_status === 'Menunggu Closing') {
            return response()->json(['message' => 'SPK ini sedang dalam status Menunggu Closing. Batalkan pengajuan closing terlebih dahulu jika ingin membatalkan sesi ini!'], 422);
        }

        $wasApproved = ($penerimaan->status === 'Diterima');

        DB::beginTransaction();
        try {
            $qtyGood = (float)$penerimaan->jumlah_hasil_baik;

            // Only revert stock & kartu stok if the session was previously 'Diterima' (had physical stock in warehouse)
            if ($wasApproved) {
                // Deduct inventory stock in StokGudang
                $stokGudang = StokGudang::firstOrCreate(
                    ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                    ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                );

                $stokAwal = (float)$stokGudang->stok_aktual;
                $stokAkhir = max(0, $stokAwal - $qtyGood);
                $nilaiAwal = (float)$stokGudang->nilai_persediaan;
                $nilaiAkhir = max(0, $nilaiAwal - (float)$penerimaan->total_nilai_hpp);
                $hppAvgNew = $stokAkhir > 0 ? round($nilaiAkhir / $stokAkhir, 6) : 0.000000;

                $stokGudang->update([
                    'stok_aktual'            => $stokAkhir,
                    'nilai_persediaan'       => $nilaiAkhir,
                    'harga_pokok_rata_rata'  => $hppAvgNew,
                    'hpp_sebelumnya'         => $hppAvgNew,
                ]);
                StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

                // Record KartuStok Reversal (Keluar)
                KartuStok::create([
                    'barang_id'       => $penerimaan->barang_jadi_id,
                    'gudang_id'       => $penerimaan->gudang_id,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $qtyGood,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => (float)$penerimaan->hpp_satuan,
                    'nilai_total'     => (float)$penerimaan->total_nilai_hpp,
                    'referensi_jenis' => 'Pembatalan Penerimaan Barang Jadi SPK',
                    'referensi_id'    => $penerimaan->id,
                    'referensi_nomor' => $penerimaan->nomor_penerimaan,
                    'keterangan'      => "Pembatalan penerimaan hasil produksi SPK #{$penerimaan->nomor_penerimaan}",
                ]);

                // Delete associated Jurnal if any
                Jurnal::where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                    ->where('referensi_id', $penerimaan->id)
                    ->delete();
            }

            $penerimaan->serials()->delete();
            $penerimaan->update([
                'status'     => 'Dibatalkan',
                'deleted_by' => $user->id,
            ]);
            $penerimaan->delete();

            // Recalculate remaining SPK counters accurately (hanya sesi yang 'Diterima')
            if ($spk) {
                $approvedSessions = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->where('status', 'Diterima');
                $remainingSelesai = (float) $approvedSessions->sum('jumlah_hasil_baik');
                $remainingReject  = (float) $approvedSessions->sum('jumlah_reject');
                $remainingCount   = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->count();

                $targetQty = (float) $spk->jumlah_rencana;
                
                $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                    return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
                })->count() === 0;
                $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
                $isCompleted = ($targetQty > 0 && $remainingSelesai >= $targetQty && $realisasiSelesai && $bahanSelesai);

                $updateSpk = [
                    'jumlah_selesai' => $remainingSelesai,
                    'jumlah_reject'  => $remainingReject,
                    'status'         => $isCompleted ? 'Completed' : 'In Progress',
                ];

                // If no sessions remain or target is not met, reset closing state
                if ($remainingCount === 0) {
                    $updateSpk['is_fg_closed']             = false;
                    $updateSpk['is_hpp_closed']            = false;
                    $updateSpk['fg_closing_status']        = 'Open';
                    $updateSpk['fg_closed_at']             = null;
                    $updateSpk['fg_closed_by']             = null;
                    $updateSpk['hpp_closed_at']            = null;
                    $updateSpk['hpp_closed_by']            = null;
                    $updateSpk['fg_closing_requested_at']  = null;
                    $updateSpk['fg_closing_requested_by']  = null;
                    $updateSpk['fg_closing_note']          = null;
                    $updateSpk['fg_closing_reject_reason'] = null;
                    $updateSpk['fg_closing_rejected_at']   = null;
                    $updateSpk['fg_closing_rejected_by']   = null;

                    // Delete consolidated closing journal if any
                    Jurnal::where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                        ->where('referensi_id', $spk->id)
                        ->delete();
                } elseif (!$isCompleted && ($spk->is_fg_closed || $spk->fg_closing_status === 'Closed' || $spk->is_hpp_closed)) {
                    $updateSpk['is_fg_closed']             = false;
                    $updateSpk['is_hpp_closed']            = false;
                    $updateSpk['fg_closing_status']        = 'Open';
                    $updateSpk['fg_closed_at']             = null;
                    $updateSpk['fg_closed_by']             = null;
                    $updateSpk['hpp_closed_at']            = null;
                    $updateSpk['hpp_closed_by']            = null;
                }

                $spk->update($updateSpk);

                // Recalculate HPP & update warehouse average for remaining active approved sessions if any
                if ($approvedSessions->count() > 0) {
                    self::adjustHppForSpk($spk);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Penerimaan hasil produksi {$penerimaan->nomor_penerimaan} berhasil dibatalkan & status SPK disesuaikan!"
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error delete penerimaan barang jadi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membatalkan penerimaan barang jadi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create automatic accounting journal (Debit: Finished Goods Inventory, Credit: WIP).
     */
    public function createJurnalPenerimaanBarangJadi(SpkPenerimaanBarangJadi $penerimaan): void
    {
        try {
            $totalNilai = (float)$penerimaan->total_nilai_hpp;
            if ($totalNilai <= 0) return;

            $spk = $penerimaan->spk ?: SpkProduksi::find($penerimaan->spk_id);
            $spkNomor = $spk ? $spk->nomor_spk : '-';

            $isBranch = !empty($penerimaan->branch_id);
            $finalBranchId  = $isBranch ? $penerimaan->branch_id : null;
            $finalCompanyId = $isBranch ? null : ($penerimaan->company_id ?: (session('active_company_id') ?: 1));

            // 1. Debit Account: Persediaan Barang Jadi (Finished Goods)
            $bjBarang = $penerimaan->barangJadi ?: MasterBarang::find($penerimaan->barang_jadi_id);
            $catId = $bjBarang ? ($bjBarang->kategori_barang_id ?? $bjBarang->kategori_id) : null;

            $fgCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null)
                    ?? AccountMappingHelper::getCoaId('default', 'default_inventory');

            if (!$fgCoaId) {
                $fgCoa = ChartOfAccount::forActiveCompany()
                    ->where(function ($q) {
                        $q->where('code', '1109')
                          ->orWhere('code', 'like', '1109%')
                          ->orWhere('name', 'like', '%Persediaan Barang Jadi%')
                          ->orWhere('name', 'like', '%Finished Goods%');
                    })
                    ->where('is_header', false)
                    ->first();
                $fgCoaId = $fgCoa ? $fgCoa->id : null;
            }

            if (!$fgCoaId) {
                $fgCoaId = ChartOfAccount::forActiveCompany()
                    ->where('name', 'like', '%Persediaan%')
                    ->where('is_header', false)
                    ->value('id');
            }

            // 2. Credit Account: WIP / Persediaan Barang Dalam Proses
            $wipCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_wip") : null)
                     ?? AccountMappingHelper::getCoaId('production', 'wip_inventory')
                     ?? AccountMappingHelper::getCoaId('default', 'default_wip');

            if (!$wipCoaId) {
                $wipCoa = ChartOfAccount::forActiveCompany()
                    ->where(function ($q) {
                        $q->where('code', '1108')
                          ->orWhere('code', 'like', '1108%')
                          ->orWhere('name', 'like', '%WIP%')
                          ->orWhere('name', 'like', '%Dalam Proses%')
                          ->orWhere('name', 'like', '%Setengah Jadi%');
                    })
                    ->where('is_header', false)
                    ->first();
                $wipCoaId = $wipCoa ? $wipCoa->id : null;
            }

            if (!$wipCoaId) {
                $wipCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory')
                         ?? ChartOfAccount::forActiveCompany()->where('code', 'like', '11%')->where('is_header', false)->value('id');
            }

            if (!$fgCoaId || !$wipCoaId) {
                Log::warning("Accounting Journal for FG Receipt #{$penerimaan->nomor_penerimaan} skipped due to missing COA mappings (FG: {$fgCoaId}, WIP: {$wipCoaId}).");
                return;
            }

            // Create or Update Jurnal safely (handling soft-deleted or existing nomor_jurnal collisions)
            $jurnal = Jurnal::withTrashed()
                ->where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                ->where('referensi_id', $penerimaan->id)
                ->first();

            $rawQty = (float)$penerimaan->jumlah_hasil_baik;
            $qtyClean = ($rawQty == (int)$rawQty) ? number_format((int)$rawQty, 0, ',', '.') : rtrim(rtrim(number_format($rawQty, 4, ',', '.'), '0'), ',');
            $satuanName = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi?->satuan?->nama_satuan ?: 'Pcs');

            $todayDate = now()->toDateString();

            $spkTender = \App\Models\SpkProduksi::with('tender')->find($penerimaan->spk_id);
            $nomorTender = $spkTender?->tender ? ($spkTender->tender->nomor_kontrak ?: $spkTender->tender->nama_tender) : null;

            if ($jurnal) {
                $jurnal->restore();
                $jurnal->update([
                    'company_id'     => $finalCompanyId,
                    'branch_id'      => $finalBranchId,
                    'tanggal_jurnal' => $todayDate,
                    'keterangan'     => "Penerimaan Barang Jadi SPK #{$spkNomor} ({$qtyClean} {$satuanName})",
                    'no_tender'      => $nomorTender,
                    'total_debit'    => $totalNilai,
                    'total_kredit'   => $totalNilai,
                    'updated_by'     => $penerimaan->created_by,
                ]);
            } else {
                $dateToUse = $todayDate;
                $yearMonth = date('Y/m', strtotime($dateToUse));
                $prefix    = $isBranch ? "JRN/FG-CBG/{$yearMonth}/" : "JRN/FG-PT/{$yearMonth}/";

                $lastNo = Jurnal::withTrashed()
                    ->where('nomor_jurnal', 'like', "{$prefix}%")
                    ->orderBy('nomor_jurnal', 'desc')
                    ->orderBy('id', 'desc')
                    ->value('nomor_jurnal');

                $nextSeq = 1;
                if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
                    $nextSeq = (int)$m[1] + 1;
                }

                do {
                    $nomorJurnal = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
                    $exists = Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists();
                    if ($exists) {
                        $nextSeq++;
                    }
                } while ($exists);

                $jurnal = Jurnal::create([
                    'referensi_tipe' => 'Penerimaan Barang Jadi SPK',
                    'referensi_id'   => $penerimaan->id,
                    'company_id'     => $finalCompanyId,
                    'branch_id'      => $finalBranchId,
                    'nomor_jurnal'   => $nomorJurnal,
                    'tanggal_jurnal' => $todayDate,
                    'keterangan'     => "Penerimaan Barang Jadi SPK #{$spkNomor} ({$qtyClean} {$satuanName})",
                    'no_tender'      => $nomorTender,
                    'total_debit'    => $totalNilai,
                    'total_kredit'   => $totalNilai,
                    'created_by'     => $penerimaan->created_by,
                ]);
            }

            // Delete old details if updating
            JurnalDetail::where('jurnal_id', $jurnal->id)->delete();

            // 1. Line Debit: Persediaan Barang Jadi
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $fgCoaId,
                'debit'      => $totalNilai,
                'kredit'     => 0,
                'keterangan' => "Penambahan Persediaan Barang Jadi SPK #{$spkNomor}",
            ]);

            // 2. Line Credit: WIP / Barang Dalam Proses
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $wipCoaId,
                'debit'      => 0,
                'kredit'     => $totalNilai,
                'keterangan' => "Pengurangan WIP / Barang Dalam Proses SPK #{$spkNomor}",
            ]);

        } catch (\Throwable $e) {
            Log::error('createJurnalPenerimaanBarangJadi error: ' . $e->getMessage());
        }
    }

    /**
     * Print PDF Bukti Penerimaan Barang Jadi SPK.
     */
    public function printPdf($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak bukti penerimaan.');
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->with([
                'company',
                'branch',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'barangJadi.satuan',
                'barangJadi.kategori',
                'gudang',
                'lokasiRak',
                'penerima.position',
                'penerima.user.activeSignature',
                'penyerah.position',
                'penyerah.user.activeSignature',
                'creator.position',
                'creator.activeSignature',
                'serials',
            ])
            ->findOrFail($id);

        $company = $penerimaan->company ?? Company::find(session('active_company_id')) ?? Company::first();
        $branch = $penerimaan->branch ?? (session('active_branch_id') ? Branch::find(session('active_branch_id')) : null);

        return view('modul_produksi.barang_jadi.print_pdf', compact('penerimaan', 'company', 'branch'));
    }

    /**
     * Adjust HPP for SPK manually.
     */
    public function adjustHpp(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('sesuaikan-hpp-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menyesuaikan HPP.'], 403);
        }

        $request->validate([
            'biaya_tenaga_kerja' => 'required|numeric|min:0',
            'biaya_overhead'     => 'required|numeric|min:0',
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        // Security Validation: If HPP is already closed, only Super Admin can recalculate
        if ($spk->is_hpp_closed && !$user->hasRole('Super Admin')) {
            return response()->json([
                'message' => "Penyesuaian ditolak! Nilai HPP SPK #{$spk->nomor_spk} sudah ditutup (Closed). Hanya Super Admin yang berhak menghitung ulang HPP."
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tk = round((float)$request->biaya_tenaga_kerja, 4);
            $oh = round((float)$request->biaya_overhead, 4);

            // Calculate actual material cost fresh from pengeluaran bahan records
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);

            // Persist the adjusted TK & Overhead, update total biaya, and lock HPP (Close)
            $spk->update([
                'status'                  => 'Completed',
                'estimasi_biaya_tk'       => $tk,
                'estimasi_biaya_overhead' => $oh,
                'estimasi_biaya_bahan'    => round($actualMaterialCost, 4),
                'estimasi_total_biaya'    => round($actualMaterialCost + $tk + $oh, 4),
                'is_hpp_closed'           => true,
                'hpp_closed_at'           => now(),
                'hpp_closed_by'           => $user->id,
            ]);

            // Refresh model to get saved values, then compute final HPP per unit explicitly
            $spk->refresh();
            $targetQty = (float)$spk->jumlah_rencana;

            // Use actual good yield produced if available to absorb 100% of batch costs into FG inventory
            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : $targetQty;

            if ($divisorQty <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Quantity pembagi HPP SPK tidak valid (0).'], 422);
            }

            $finalHppPerUnit = round(($actualMaterialCost + $tk + $oh) / $divisorQty, 4);

            // Propagate HPP to all finished goods receipt records, stock ledgers, and journals
            self::adjustHppForSpkWithRate($spk, $finalHppPerUnit);

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => "HPP SPK {$spk->nomor_spk} berhasil disesuaikan & status HPP ditutup (Closed)! HPP per unit: Rp " . number_format($finalHppPerUnit, 2, ',', '.'),
                'hpp_per_unit'      => $finalHppPerUnit,
                'total_biaya_bahan' => $actualMaterialCost,
                'biaya_tk'          => $tk,
                'biaya_overhead'    => $oh,
                'is_hpp_closed'     => true,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('adjustHpp error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyesuaikan HPP: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unlock HPP for an SPK (Super Admin only).
     */
    public function unlockHpp(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Akses ditolak! Hanya Super Admin yang berhak membuka kembali kunci HPP SPK.'], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        $spk->update([
            'is_hpp_closed' => false,
            'hpp_closed_at' => null,
            'hpp_closed_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kunci HPP SPK #{$spk->nomor_spk} berhasil dibuka kembali oleh Super Admin! Nilai HPP kini dapat disesuaikan ulang."
        ], 200);
    }

    /**
     * Recalculate HPP for SPK using the stored estimasi values (called on general reflows).
     */
    public static function adjustHppForSpk(SpkProduksi $spk): void
    {
        $controller = new self();
        $finalHpp = $controller->calculateSpkUnitHpp($spk);
        if ($finalHpp <= 0) return;
        self::adjustHppForSpkWithRate($spk, $finalHpp);
    }

    /**
     * Apply a specific pre-computed HPP per unit to all FG receipt records, stock ledgers, and journals.
     * Used by adjustHpp (manual adjustment) so TK & overhead are NOT double-counted.
     */
    public static function adjustHppForSpkWithRate(SpkProduksi $spk, float $finalHpp): void
    {
        $controller = new self();

        $penerimaans = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
            ->whereNull('deleted_at')
            ->get();

        foreach ($penerimaans as $penerimaan) {
            $qtyGood = (float)$penerimaan->jumlah_hasil_baik;
            if ($qtyGood <= 0) continue;

            $oldHppSatuan = (float)$penerimaan->hpp_satuan;
            $oldTotalHpp  = (float)$penerimaan->total_nilai_hpp;
            $newTotalHpp  = round($qtyGood * $finalHpp, 4);
            $diffTotalHpp = $newTotalHpp - $oldTotalHpp;

            $wasApproved  = ($penerimaan->status === 'Diterima');

            // Skip if no meaningful change AND session was already approved AND journal already exists
            if ($wasApproved && abs($diffTotalHpp) < 0.0001 && abs($finalHpp - $oldHppSatuan) < 0.0001) {
                continue;
            }

            // Update Penerimaan record to 'Diterima' and apply final HPP rate
            $penerimaan->update([
                'status'          => 'Diterima',
                'hpp_satuan'      => $finalHpp,
                'total_nilai_hpp' => $newTotalHpp,
                'approved_by'     => $penerimaan->approved_by ?: auth()->id(),
                'approved_at'     => $penerimaan->approved_at ?: now(),
            ]);

            // Update StokGudang
            $stokGudang = StokGudang::firstOrCreate(
                ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $stokAwal  = (float)$stokGudang->stok_aktual;
            $nilaiAwal = (float)$stokGudang->nilai_persediaan;

            if ($wasApproved) {
                // Sesi sudah 'Diterima' sebelumnya: stok fisik sudah terhitung, hanya menyesuaikan selisih nilai persediaan
                $stokAkhir  = $stokAwal;
                $nilaiAkhir = max(0, $nilaiAwal + $diffTotalHpp);
            } else {
                // Sesi sebelumnya 'Menunggu HPP': Baru sekarang resmi masuk stok! Tambahkan fisik stok & nilai persediaan
                $stokAkhir  = $stokAwal + $qtyGood;
                $nilaiAkhir = $nilaiAwal + $newTotalHpp;
            }

            $hppAvgNew = $stokAkhir > 0 ? round($nilaiAkhir / $stokAkhir, 6) : $finalHpp;

            $stokGudang->update([
                'stok_aktual'           => $stokAkhir,
                'nilai_persediaan'      => $nilaiAkhir,
                'hpp_sebelumnya'        => (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000'),
                'harga_pokok_rata_rata' => $hppAvgNew,
                'harga_beli_terakhir'   => $finalHpp,
            ]);

            StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

            // Update or Create KartuStok
            $kartuStok = KartuStok::where('referensi_jenis', 'Penerimaan Barang Jadi SPK')
                ->where('referensi_id', $penerimaan->id)
                ->first();

            if ($kartuStok) {
                $kartuStok->update([
                    'stok_awal'    => $wasApproved ? $kartuStok->stok_awal : $stokAwal,
                    'stok_akhir'   => $wasApproved ? $kartuStok->stok_akhir : $stokAkhir,
                    'harga_satuan' => $finalHpp,
                    'nilai_total'  => $newTotalHpp,
                ]);
            } else {
                KartuStok::create([
                    'barang_id'       => $penerimaan->barang_jadi_id,
                    'gudang_id'       => $penerimaan->gudang_id,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qtyGood,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $finalHpp,
                    'nilai_total'     => $newTotalHpp,
                    'referensi_jenis' => 'Penerimaan Barang Jadi SPK',
                    'referensi_id'    => $penerimaan->id,
                    'referensi_nomor' => $penerimaan->nomor_penerimaan,
                    'keterangan'      => "Hasil produksi barang jadi dari SPK #{$spk->nomor_spk} (HPP Final Disetujui)",
                ]);
            }

            // Re-post or create Accounting Journal for session
            $controller->createJurnalPenerimaanBarangJadi($penerimaan);
        }
    }

    /**
     * Preview closing data for Finished Goods SPK.
     */
    public function previewClosingFg($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('ajukan-closing-penerimaan-barang-jadi') && !$user->can('closing-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'satuan',
                'penerimaanBarangJadis',
                'fgClosingRequestedBy',
            ])
            ->findOrFail($spkId);

        $targetQty   = (float)$spk->jumlah_rencana;
        $selesaiQty  = (float)$spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
        $rejectQty   = (float)$spk->penerimaanBarangJadis->sum('jumlah_reject');
        $totalSesi   = $spk->penerimaanBarangJadis->count();
        $satuanName  = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');

        $actualMaterialCost = $this->calculateActualMaterialCost($spk);
        $tk = (float)($spk->estimasi_biaya_tk ?: 0);
        $oh = (float)($spk->estimasi_biaya_overhead ?: 0);
        $totalBiaya = round($actualMaterialCost + $tk + $oh, 4);

        $divisor = $selesaiQty > 0 ? $selesaiQty : ($targetQty > 0 ? $targetQty : 1);
        $hppPerUnit = round($totalBiaya / $divisor, 4);

        $isCompleted = ($targetQty > 0) && ($selesaiQty >= $targetQty);
        $pct = $targetQty > 0 ? round(($selesaiQty / $targetQty) * 100, 1) : 0;
        $isEarlyClosing = ($targetQty > 0) && ($selesaiQty < $targetQty);

        return response()->json([
            'success' => true,
            'spk' => [
                'id'            => $spk->id,
                'nomor_spk'     => $spk->nomor_spk,
                'barang_jadi'   => $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-',
                'satuan'        => $satuanName,
                'target_qty'    => $targetQty,
                'total_selesai' => $selesaiQty,
                'total_reject'  => $rejectQty,
                'total_sesi'    => $totalSesi,
                'status'        => $spk->status,
                'closing_status'=> $spk->fg_closing_status ?: ($spk->is_fg_closed || $spk->is_hpp_closed ? 'Closed' : 'Open'),
                'is_completed'  => $isCompleted,
            ],
            'target_qty'         => $targetQty,
            'total_selesai'      => $selesaiQty,
            'total_reject'       => $rejectQty,
            'total_sesi'         => $totalSesi,
            'persentase_selesai' => $pct,
            'is_early_closing'   => $isEarlyClosing,
            'closing_requested_by_name' => $spk->fgClosingRequestedBy?->nama_lengkap ?: ($spk->fgClosingRequestedBy?->username ?? null),
            'closing_requested_at'      => $spk->fg_closing_requested_at ? $spk->fg_closing_requested_at->toIso8601String() : null,
            'closing_note'               => $spk->fg_closing_note ?: null,
            'biaya' => [
                'bahan_riil'     => $actualMaterialCost,
                'tenaga_kerja'   => $tk,
                'overhead'       => $oh,
                'total_biaya'    => $totalBiaya,
                'hpp_per_unit'   => $hppPerUnit,
                'divisor_qty'    => $divisor,
            ],
            'requester_info' => [
                'name'         => $spk->fgClosingRequestedBy?->nama_lengkap ?: ($spk->fgClosingRequestedBy?->username ?? '-'),
                'requested_at' => $spk->fg_closing_requested_at ? $spk->fg_closing_requested_at->format('d/m/Y H:i') : '-',
                'note'         => $spk->fg_closing_note ?: '-',
            ]
        ]);
    }

    /**
     * Submit Finished Goods Closing Request (Produksi -> Accounting).
     */
    public function requestClosingFg(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('ajukan-closing-penerimaan-barang-jadi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengajukan closing penerimaan barang jadi.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->is_fg_closed || $spk->fg_closing_status === 'Closed' || $spk->is_hpp_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan barang jadi untuk SPK ini sudah di-Closing!'
            ], 422);
        }

        if ($spk->penerimaanBarangJadis()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada penerimaan hasil produksi untuk SPK ini. Closing tidak dapat diajukan.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $catatan = $request->filled('catatan') ? trim($request->catatan) : null;

            $spk->update([
                'fg_closing_status'          => 'Menunggu Closing',
                'fg_closing_requested_at'    => now(),
                'fg_closing_requested_by'    => $user->id,
                'fg_closing_note'            => $catatan,
                'fg_closing_reject_reason'   => null,
                'fg_closing_rejected_at'     => null,
                'fg_closing_rejected_by'     => null,
            ]);

            DB::commit();

            // Trigger WhatsApp notification for reviewer / accounting
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingSubmissionNotification($spk, $user, $catatan);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on FG closing submission error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing penerimaan barang jadi untuk SPK {$spk->nomor_spk} berhasil dikirim ke bagian Accounting!",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('requestClosingFg error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel Finished Goods Closing Request (Strict Permission check).
     */
    public function cancelClosingFgRequest(Request $request, $spkId)
    {
        $user = auth()->user();
        $canCancel = $user->hasAnyRole(['Super Admin', 'Owner']) 
            || $user->can('batal-ajukan-closing-penerimaan-barang-jadi');

        if (!$canCancel) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membatalkan pengajuan closing hasil produksi.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->fg_closing_status !== 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'Status pengajuan bukan Menunggu Closing.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'fg_closing_status'          => 'Open',
                'fg_closing_requested_at'    => null,
                'fg_closing_requested_by'    => null,
                'fg_closing_note'            => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing untuk SPK {$spk->nomor_spk} berhasil dibatalkan. Operasional penerimaan barang jadi kembali terbuka.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('cancelClosingFgRequest error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Closing Approval, Finalize HPP, and Post Consolidated Accounting Journal.
     */
    public function closeFg(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-penerimaan-barang-jadi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melakukan otorisasi closing hasil produksi.'
            ], 403);
        }

        $request->validate([
            'tanggal_closing'    => 'required|date',
            'biaya_tenaga_kerja' => 'required|numeric|min:0',
            'biaya_overhead'     => 'required|numeric|min:0',
            'catatan'            => 'nullable|string|max:255',
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->is_fg_closed || $spk->fg_closing_status === 'Closed' || $spk->is_hpp_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan barang jadi untuk SPK ini sudah di-Closing!'
            ], 422);
        }

        if ($spk->penerimaanBarangJadis()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada penerimaan hasil produksi untuk SPK ini. Closing tidak dapat diproses.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tk = round((float)$request->biaya_tenaga_kerja, 4);
            $oh = round((float)$request->biaya_overhead, 4);
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);
            $totalBiaya = round($actualMaterialCost + $tk + $oh, 4);

            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)->whereNull('deleted_at')->sum('jumlah_hasil_baik');
            $targetQty  = (float)$spk->jumlah_rencana;
            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : ($targetQty > 0 ? $targetQty : 1);
            $finalHppPerUnit = round($totalBiaya / $divisorQty, 4);

            $spk->update([
                'status'                  => 'Completed',
                'estimasi_biaya_tk'       => $tk,
                'estimasi_biaya_overhead' => $oh,
                'estimasi_biaya_bahan'    => round($actualMaterialCost, 4),
                'estimasi_total_biaya'    => $totalBiaya,
                'is_hpp_closed'           => true,
                'hpp_closed_at'           => now(),
                'hpp_closed_by'           => $user->id,
                'is_fg_closed'            => true,
                'fg_closing_status'       => 'Closed',
                'fg_closed_at'            => now(),
                'fg_closed_by'            => $user->id,
            ]);

            // Propagate HPP to all finished goods receipt records and stock ledgers
            self::adjustHppForSpkWithRate($spk, $finalHppPerUnit);

            // Create 1 Consolidated Journal for FG
            $this->createConsolidatedJurnalClosingFg($spk, $request->tanggal_closing, $request->catatan);

            DB::commit();

            // Trigger WhatsApp notification for requester
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingApprovedNotification($spk, $user);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on FG closing approval error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Closing penerimaan hasil produksi SPK {$spk->nomor_spk} berhasil disetujui, HPP final Rp " . number_format($finalHppPerUnit, 2, ',', '.') . " per unit ditetapkan & jurnal akuntansi konsolidasi diterbitkan!",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('closeFg error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses closing hasil produksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Consolidated Accounting Journal for Finished Goods Receipt.
     */
    private function createConsolidatedJurnalClosingFg(SpkProduksi $spk, $tanggalClosing = null, $catatan = null): void
    {
        $totalNilai = (float)$spk->penerimaanBarangJadis()->whereNull('deleted_at')->sum('total_nilai_hpp');
        if ($totalNilai <= 0) {
            $totalNilai = (float)$spk->estimasi_total_biaya;
        }
        if ($totalNilai <= 0) return;

        $isBranch = !empty($spk->branch_id);
        $finalBranchId  = $isBranch ? $spk->branch_id : null;
        $finalCompanyId = $isBranch ? null : ($spk->company_id ?: (session('active_company_id') ?: 1));

        $bjBarang = $spk->barangJadi ?: MasterBarang::find($spk->barang_jadi_id);
        $catId = $bjBarang ? ($bjBarang->kategori_barang_id ?? $bjBarang->kategori_id) : null;

        // 1. Debit Account: Persediaan Barang Jadi (Finished Goods 1109)
        $fgCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null)
                ?? AccountMappingHelper::getCoaId('default', 'default_inventory');

        if (!$fgCoaId) {
            $fgCoa = ChartOfAccount::forActiveCompany()
                ->where(function ($q) {
                    $q->where('code', 'like', '1109%')
                      ->orWhere('name', 'like', '%Persediaan Barang Jadi%')
                      ->orWhere('name', 'like', '%Finished Goods%');
                })
                ->where('is_header', false)
                ->first();
            $fgCoaId = $fgCoa ? $fgCoa->id : null;
        }

        if (!$fgCoaId) {
            $fgCoaId = ChartOfAccount::forActiveCompany()->where('name', 'like', '%Persediaan%')->where('is_header', false)->value('id');
        }

        // 2. Credit Account: WIP / Persediaan Barang Dalam Proses (1108)
        $wipCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_wip") : null)
                 ?? AccountMappingHelper::getCoaId('production', 'wip_inventory')
                 ?? AccountMappingHelper::getCoaId('default', 'default_wip');

        if (!$wipCoaId) {
            $wipCoa = ChartOfAccount::forActiveCompany()
                ->where(function ($q) {
                    $q->where('code', 'like', '1108%')
                      ->orWhere('name', 'like', '%WIP%')
                      ->orWhere('name', 'like', '%Dalam Proses%')
                      ->orWhere('name', 'like', '%Setengah Jadi%');
                })
                ->where('is_header', false)
                ->first();
            $wipCoaId = $wipCoa ? $wipCoa->id : null;
        }

        if (!$wipCoaId) {
            $wipCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory')
                     ?? ChartOfAccount::forActiveCompany()->where('code', 'like', '11%')->where('is_header', false)->value('id');
        }

        if (!$fgCoaId || !$wipCoaId) {
            Log::warning("Consolidated FG Journal for SPK #{$spk->nomor_spk} skipped due to missing COA mappings (FG: {$fgCoaId}, WIP: {$wipCoaId}).");
            return;
        }

        $dateToUse = $tanggalClosing ?: now()->toDateString();
        $yearMonth = date('Y/m', strtotime($dateToUse));
        $prefix    = $isBranch ? "JRN/FG-CBG/{$yearMonth}/" : "JRN/FG-PT/{$yearMonth}/";

        $lastNo = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', "{$prefix}%")
            ->orderBy('nomor_jurnal', 'desc')
            ->orderBy('id', 'desc')
            ->value('nomor_jurnal');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        do {
            $nomorJurnal = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        $jurnal = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
            ->where('referensi_id', $spk->id)
            ->first();

        $totalQtyGood = (float)$spk->penerimaanBarangJadis()->whereNull('deleted_at')->sum('jumlah_hasil_baik');
        $qtyClean     = ($totalQtyGood == (int)$totalQtyGood) ? number_format((int)$totalQtyGood, 0, ',', '.') : rtrim(rtrim(number_format($totalQtyGood, 4, ',', '.'), '0'), ',');
        $satuanName   = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi?->satuan?->nama_satuan ?: 'Pcs');
        $ket = "Konsolidasi Penerimaan Barang Jadi SPK #{$spk->nomor_spk} ({$qtyClean} {$satuanName})" . ($catatan ? " - {$catatan}" : "");

        $spk->loadMissing('tender');
        $nomorTender = $spk->tender ? ($spk->tender->nomor_kontrak ?: $spk->tender->nama_tender) : null;

        if ($jurnal) {
            $jurnal->restore();
            $jurnal->update([
                'nomor_jurnal'    => $jurnal->nomor_jurnal ?: $nomorJurnal,
                'tanggal_jurnal'  => $dateToUse,
                'branch_id'       => $finalBranchId,
                'company_id'      => $finalCompanyId,
                'total_debit'     => $totalNilai,
                'total_kredit'    => $totalNilai,
                'keterangan'      => $ket,
                'no_tender'       => $nomorTender,
                'updated_by'      => auth()->id(),
            ]);
            $jurnal->details()->delete();
        } else {
            $jurnal = Jurnal::create([
                'nomor_jurnal'    => $nomorJurnal,
                'tanggal_jurnal'  => $dateToUse,
                'referensi_tipe'  => 'Penerimaan Barang Jadi SPK',
                'referensi_id'    => $spk->id,
                'branch_id'       => $finalBranchId,
                'company_id'      => $finalCompanyId,
                'total_debit'     => $totalNilai,
                'total_kredit'    => $totalNilai,
                'keterangan'      => $ket,
                'no_tender'       => $nomorTender,
                'created_by'      => auth()->id(),
            ]);
        }

        // Line 1: Debit Persediaan Barang Jadi
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $fgCoaId,
            'debit'      => $totalNilai,
            'kredit'     => 0,
            'keterangan' => "Penambahan Persediaan Barang Jadi SPK #{$spk->nomor_spk}",
        ]);

        // Line 2: Credit WIP / Barang Dalam Proses
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $wipCoaId,
            'debit'      => 0,
            'kredit'     => $totalNilai,
            'keterangan' => "Pengurangan WIP / Barang Dalam Proses SPK #{$spk->nomor_spk}",
        ]);
    }

    /**
     * Reject Finished Goods Closing Request by Accounting / Approver.
     */
    public function rejectClosingFg(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tolak-closing-penerimaan-barang-jadi') && !$user->can('closing-penerimaan-barang-jadi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menolak pengajuan closing hasil produksi.'
            ], 403);
        }

        $request->validate([
            'alasan_reject' => 'required|string|max:500',
        ], [
            'alasan_reject.required' => 'Alasan penolakan closing wajib diisi!'
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->fg_closing_status !== 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'Status SPK bukan Menunggu Closing!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $alasan = trim($request->alasan_reject);

            $spk->update([
                'fg_closing_status'          => 'Ditolak',
                'fg_closing_reject_reason'   => $alasan,
                'fg_closing_rejected_at'     => now(),
                'fg_closing_rejected_by'     => $user->id,
            ]);

            DB::commit();

            // Trigger WhatsApp notification for requester
            try {
                WhatsAppGatewayController::sendPenerimaanBarangJadiClosingRejectedNotification($spk, $user, $alasan);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on FG closing rejection error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing penerimaan barang jadi SPK {$spk->nomor_spk} berhasil ditolak. Operasional penerimaan kembali terbuka.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('rejectClosingFg error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pengajuan closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen Finished Goods Closing for Adjustments.
     */
    public function reopenFg(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('buka-closing-penerimaan-barang-jadi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membuka kembali closing hasil produksi.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if (!$spk->is_fg_closed && $spk->fg_closing_status !== 'Closed' && !$spk->is_hpp_closed) {
            return response()->json([
                'success' => false,
                'message' => 'SPK ini belum di-closing atau status hasil produksi masih terbuka.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'status'                     => 'In Progress',
                'is_fg_closed'               => false,
                'is_hpp_closed'              => false,
                'fg_closing_status'          => 'Open',
                'fg_closed_at'               => null,
                'fg_closed_by'               => null,
                'hpp_closed_at'              => null,
                'hpp_closed_by'              => null,
                'fg_closing_requested_at'    => null,
                'fg_closing_requested_by'    => null,
                'fg_closing_note'            => null,
                'fg_closing_reject_reason'   => null,
                'fg_closing_rejected_at'     => null,
                'fg_closing_rejected_by'     => null,
            ]);

            // Soft-delete consolidated journal and session journals for this SPK
            Jurnal::where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                ->where(function ($q) use ($spk) {
                    $q->where('referensi_id', $spk->id)
                      ->orWhereIn('referensi_id', $spk->penerimaanBarangJadis()->pluck('id'));
                })
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Closing hasil produksi untuk SPK {$spk->nomor_spk} berhasil dibuka kembali. Jurnal akuntansi ditarik & penerimaan hasil dapat dilanjutkan."
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('reopenFg error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka closing hasil produksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display consolidated closing journal details for Finished Goods SPK.
     */
    public function showSpkJurnalClosing($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-jurnal-penerimaan-barang-jadi') && !$user->can('lihat-jurnal')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat jurnal penerimaan barang jadi.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()
            ->with(['barangJadi', 'fgClosedBy', 'fgClosingJurnal.details.account'])
            ->findOrFail($spkId);

        $jurnal = $spk->fgClosingJurnal;
        if (!$jurnal) {
            // Check if there is an individual journal from penerimaan sessions
            $sessionJurnal = Jurnal::where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                ->whereIn('referensi_id', $spk->penerimaanBarangJadis()->pluck('id'))
                ->with('details.account')
                ->first();
            $jurnal = $sessionJurnal;
        }

        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal konsolidasi closing belum ditemukan untuk SPK ini.'
            ], 404);
        }

        // Clean up any trailing decimal zeros like (5.0000 item) -> (5 item) or (5 Pcs)
        $cleanKeterangan = preg_replace_callback('/\((\d+)\.0{1,4}(\s+[a-zA-Z]+)?\)/', function ($m) {
            $num = number_format((int)$m[1], 0, ',', '.');
            $unit = isset($m[2]) ? $m[2] : '';
            return "({$num}{$unit})";
        }, $jurnal->keterangan ?? '-');

        return response()->json([
            'success' => true,
            'spk' => [
                'id'          => $spk->id,
                'nomor_spk'   => $spk->nomor_spk,
                'barang_jadi' => $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-',
                'closed_by'   => $spk->fgClosedBy?->nama_lengkap ?: ($spk->hppClosedBy?->nama_lengkap ?: 'User'),
                'closed_at'   => $spk->fg_closed_at ? $spk->fg_closed_at->format('d/m/Y H:i') : ($spk->hpp_closed_at ? $spk->hpp_closed_at->format('d/m/Y H:i') : '-'),
            ],
            'jurnal' => [
                'id'             => $jurnal->id,
                'nomor_jurnal'   => $jurnal->nomor_jurnal,
                'tanggal_jurnal' => $jurnal->tanggal_jurnal ? date('d/m/Y', strtotime($jurnal->tanggal_jurnal)) : '-',
                'keterangan'     => $cleanKeterangan,
                'total_debit'    => (float)$jurnal->total_debit,
                'total_kredit'   => (float)$jurnal->total_kredit,
                'details'        => $jurnal->details->map(function ($d) {
                    return [
                        'kode_akun'  => $d->account?->code ?? '-',
                        'nama_akun'  => $d->account?->name ?? '-',
                        'debit'      => (float)$d->debit,
                        'kredit'     => (float)$d->kredit,
                        'keterangan' => $d->keterangan,
                    ];
                }),
            ]
        ]);
    }
}
