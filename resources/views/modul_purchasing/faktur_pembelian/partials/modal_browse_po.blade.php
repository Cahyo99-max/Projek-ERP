<!-- MODAL POPUP BROWSE PURCHASE ORDER (PO) -->
<div class="modal fade" id="modalBrowsePO" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 1065 !important; background: rgba(15, 23, 42, 0.72) !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1280px; width: 96%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header with Modern Emerald Gradient -->
            <div class="modal-header py-3 px-4 border-0 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #065f46 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px);">
                        <i class="bi bi-file-earmark-text fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="letter-spacing: -0.01em;">Pilih Dokumen Purchase Order (PO)</h5>
                        <div class="text-white-50 small mt-0.5" style="font-size: 0.76rem;">Pilih dokumen purchase order yang akan dibuatkan tagihan faktur langsung</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-sm" id="btn_refresh_browse_po" style="background: rgba(255,255,255,0.18);" title="Muat ulang dokumen PO">
                        <i class="bi bi-arrow-clockwise fs-6"></i>
                        <span>Segarkan</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4">
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-pill ps-3 pe-2 text-success">
                            <i class="bi bi-search fs-6"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill py-2" id="search_po_lookup" placeholder="Ketik No. PO, Supplier, atau Tender untuk mencari dokumen..." autocomplete="off">
                    </div>
                </div>

                <!-- Table Wrapper -->
                <div class="table-responsive rounded-3 border shadow-sm">
                    <table class="table align-middle table-hover mb-0" id="tableBrowsePO">
                        <thead class="bg-body-secondary text-body" style="font-size: 0.73rem; letter-spacing: 0.05em; text-transform: uppercase;">
                            <tr>
                                <th width="20%" class="py-2.5 px-3">NO. PURCHASE ORDER</th>
                                <th width="12%" class="py-2.5 px-3">TANGGAL</th>
                                <th width="32%" class="py-2.5 px-3">SUPPLIER & PROYEK TENDER</th>
                                <th width="22%" class="py-2.5 px-3 text-end">TOTAL ORDER</th>
                                <th width="14%" class="py-2.5 px-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tableBrowsePO_tbody">
                            <tr id="browse_po_loading_row" style="display:none;">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm text-success me-2"></div>
                                    Memuat daftar dokumen Purchase Order...
                                </td>
                            </tr>
                            @forelse($eligiblePos ?? [] as $po)
                            @php
                                $poCur = $po->currency;
                                $poCurCode = strtoupper($poCur?->kode_currency ?? ($poCur?->code ?? 'IDR'));
                                $poCurName = $poCur?->nama_currency ?? ($poCur?->name ?? $poCurCode);
                                $poCurSymbol = $poCur?->simbol ?? ($poCur?->symbol ?? ($poCurCode === 'USD' ? '$' : 'Rp'));
                                $poKurs = ($po->kurs && floatval($po->kurs) > 0) ? number_format($po->kurs, 4, '.', '') : '1.0000';
                                $poTender = $po->tender;
                                $poTenderNomor = $poTender ? (!empty($poTender->nomor_kontrak) && $poTender->nomor_kontrak !== '-' ? ($poTender->nomor_kontrak . (!empty($poTender->nama_tender) ? ' - ' . $poTender->nama_tender : '')) : ($poTender->nomor_kontrak ?: ($poTender->nama_tender ?: ('Tender #' . $poTender->id)))) : null;
                                
                                $totalQtyOrdered = floatval($po->details->sum('jumlah'));
                                $totalQtyReceived = floatval($po->details->sum(function($d) {
                                    return $d->grDetails ? $d->grDetails->where('goodsReceipt.status', '!=', 'Dibatalkan')->sum('jumlah') : 0;
                                }));
                                
                                if ($totalQtyReceived == 0) {
                                    $receiptBadgeStyle = 'background: rgba(148, 163, 184, 0.16); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.35);';
                                    $receiptIcon = 'bi-clock-history';
                                    $receiptLabel = 'Belum Ada Penerimaan (' . number_format($totalQtyReceived, 0, ',', '.') . ' / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
                                } elseif ($totalQtyReceived < $totalQtyOrdered) {
                                    $receiptBadgeStyle = 'background: rgba(14, 165, 233, 0.18); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.45);';
                                    $receiptIcon = 'bi-pie-chart-fill';
                                    $receiptLabel = 'Diterima Sebagian (' . number_format($totalQtyReceived, 0, ',', '.') . ' / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
                                } else {
                                    $receiptBadgeStyle = 'background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.45);';
                                    $receiptIcon = 'bi-check2-circle';
                                    $receiptLabel = 'Diterima Lengkap (' . number_format($totalQtyReceived, 0, ',', '.') . ' / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
                                }
                            @endphp
                            <tr class="row-po-item"
                                data-id="{{ $po->id }}"
                                data-nomor="{{ $po->nomor_po }}"
                                data-supplier="{{ $po->supplier?->nama_supplier }}"
                                data-currency="{{ $poCurCode . ($poCurName ? ' (' . $poCurName . ')' : '') }}"
                                data-currency-code="{{ $poCurCode }}"
                                data-currency-symbol="{{ $poCurSymbol }}"
                                data-kurs="{{ $poKurs }}"
                                data-payment-term="{{ $po->payment_term_id }}"
                                data-pajak="{{ $po->pajak_id }}"
                                data-supplier-npwp="{{ $po->supplier?->npwp ?? '' }}"
                                data-tender-id="{{ $poTender?->id }}"
                                data-tender-nomor="{{ $poTenderNomor }}"
                                style="cursor: pointer;">
                                <td class="px-3">
                                    <span class="badge font-monospace fw-bold px-2.5 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.82rem;">
                                        {{ $po->nomor_po }}
                                    </span>
                                </td>
                                <td class="px-3">
                                    <span class="font-monospace fw-medium text-body"><i class="bi bi-calendar3 me-1 text-success small"></i>{{ $po->tanggal_po ? date('d/m/Y', strtotime($po->tanggal_po)) : '-' }}</span>
                                </td>
                                <td class="px-3">
                                    <div class="fw-bold text-body fs-6 mb-0.5">{{ $po->supplier?->nama_supplier ?? '-' }}</div>
                                    @if(!empty($po->supplier?->npwp) && $po->supplier->npwp !== '-')
                                    <div class="mt-1">
                                        <span class="badge font-monospace px-2 py-0.5 rounded-pill" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; font-weight: 600;" title="NPWP Terdaftar">
                                            <i class="bi bi-card-text me-1 text-success"></i>NPWP: {{ $po->supplier->npwp }}
                                        </span>
                                    </div>
                                    @endif
                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-1.5">
                                        <span class="badge bg-secondary bg-opacity-25 text-body border border-secondary-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.7rem;">
                                            {{ $po->status }}
                                        </span>
                                        <span class="text-muted extra-small" style="font-size: 0.74rem;">
                                            <i class="bi bi-boxes me-1"></i>{{ $po->details->count() }} item barang
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge font-monospace px-2.5 py-0.5 rounded-pill" style="{{ $receiptBadgeStyle }}; font-size: 0.7rem;" title="Status Penerimaan Barang">
                                            <i class="bi {{ $receiptIcon }} me-1"></i>{{ $receiptLabel }}
                                        </span>
                                    </div>
                                    @if($poTenderNomor)
                                    <div class="mt-1.5">
                                        <span class="badge font-monospace px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 0.74rem; font-weight: 600;" title="Terkait Proyek Tender">
                                            <i class="bi bi-briefcase-fill text-warning"></i>
                                            <span>{{ $poTenderNomor }}</span>
                                        </span>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-3 text-end">
                                    <div class="fw-bold font-monospace text-body fs-6">{{ $poCurSymbol }} {{ number_format($po->grand_total, 2, ',', '.') }}</div>
                                    @if($poCurCode !== 'IDR' && $po->kurs > 0)
                                    <div class="text-muted font-monospace extra-small" style="font-size: 0.72rem;">
                                        ≈ Rp {{ number_format($po->grand_total * $po->kurs, 2, ',', '.') }}
                                    </div>
                                    @endif
                                </td>
                                <td class="px-3 text-end">
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-sm btn-select-po-row d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-lg fs-6"></i>
                                        <span>Pilih</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="no-po-row">
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-semibold">Tidak ada dokumen Purchase Order (PO) yang siap ditagihkan</div>
                                    <small class="text-muted">Semua Purchase Order telah dibuatkan faktur atau belum ada PO yang berstatus disetujui.</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2.5 px-4 bg-body-tertiary d-flex justify-content-between align-items-center">
                <div class="small text-muted d-none d-md-block">
                    <i class="bi bi-info-circle me-1 text-success"></i>Klik pada salah satu baris atau tekan tombol <strong>Pilih</strong> untuk membuat tagihan langsung dari PO.
                </div>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold ms-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
