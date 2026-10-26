<!-- MODAL POPUP BROWSE GRN/BPB -->
<div class="modal fade" id="modalBrowseGR" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 1065 !important; background: rgba(15, 23, 42, 0.72) !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1280px; width: 96%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header with Modern Gradient -->
            <div class="modal-header py-3 px-4 border-0 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px);">
                        <i class="bi bi-box-seam fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="letter-spacing: -0.01em;">Pilih Dokumen Penerimaan Barang (GRN/BPB)</h5>
                        <div class="text-white-50 small mt-0.5" style="font-size: 0.76rem;">Pilih dokumen penerimaan barang yang akan ditagihkan ke supplier</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-sm" id="btn_refresh_browse_gr" style="background: rgba(255,255,255,0.18);" title="Muat ulang dokumen GRN">
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
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-pill ps-3 pe-2 text-primary">
                            <i class="bi bi-search fs-6"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill py-2" id="search_gr_lookup" placeholder="Ketik No. GRN, Supplier, Gudang, atau No. PO / Tender untuk mencari dengan cepat..." autocomplete="off">
                    </div>
                </div>

                <!-- Table Wrapper -->
                <div class="table-responsive rounded-3 border shadow-sm">
                    <table class="table align-middle table-hover mb-0" id="tableBrowseGR">
                        <thead class="bg-body-secondary text-body" style="font-size: 0.73rem; letter-spacing: 0.05em; text-transform: uppercase;">
                            <tr>
                                <th width="18%" class="py-2.5 px-3">NO. GRN / BPB</th>
                                <th width="12%" class="py-2.5 px-3">TANGGAL</th>
                                <th width="32%" class="py-2.5 px-3">SUPPLIER & GUDANG</th>
                                <th width="26%" class="py-2.5 px-3">REFERENSI PO & TENDER</th>
                                <th width="12%" class="py-2.5 px-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tableBrowseGR_tbody">
                            <tr id="browse_gr_loading_row" style="display:none;">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    Memuat daftar dokumen penerimaan barang...
                                </td>
                            </tr>
                            @forelse($eligibleGrs as $gr)
                            @php
                                $grTender = $gr->tender ?? $gr->po?->tender;
                                $grTenderNomor = $grTender ? (!empty($grTender->nomor_kontrak) && $grTender->nomor_kontrak !== '-' ? ($grTender->nomor_kontrak . (!empty($grTender->nama_tender) ? ' - ' . $grTender->nama_tender : '')) : ($grTender->nomor_kontrak ?: ($grTender->nama_tender ?: ('Tender #' . $grTender->id)))) : null;
                            @endphp
                            <tr class="row-gr-item"
                                data-id="{{ $gr->id }}"
                                data-nomor="{{ $gr->nomor_gr }}"
                                data-supplier="{{ $gr->po?->supplier?->nama_supplier }}"
                                data-gudang="{{ $gr->gudang?->nama_gudang }}"
                                data-po="{{ $gr->po?->nomor_po }}"
                                data-currency="{{ $gr->po?->currency?->kode_currency ? ($gr->po->currency->kode_currency . ' (' . ($gr->po->currency->nama_currency ?? $gr->po->currency->kode_currency) . ')') : 'IDR (Rupiah)' }}"
                                data-currency-code="{{ $gr->po?->currency?->kode_currency ?? 'IDR' }}"
                                data-currency-symbol="{{ $gr->po?->currency?->simbol ?? ($gr->po?->currency?->kode_currency === 'USD' ? '$' : 'Rp') }}"
                                data-kurs="{{ ($gr->kurs && floatval($gr->kurs) > 0) ? number_format($gr->kurs, 4, '.', '') : ($gr->po?->kurs ? number_format($gr->po->kurs, 4, '.', '') : '1.0000') }}"
                                data-payment-term="{{ $gr->po?->payment_term_id }}"
                                data-pajak="{{ $gr->po?->pajak_id }}"
                                data-supplier-npwp="{{ $gr->po?->supplier?->npwp ?? '' }}"
                                data-tender-id="{{ $grTender?->id }}"
                                data-tender-nomor="{{ $grTenderNomor }}"
                                style="cursor: pointer;">
                                <td class="px-3">
                                    <span class="badge font-monospace fw-bold px-2.5 py-1 rounded-pill" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.82rem;">
                                        {{ $gr->nomor_gr }}
                                    </span>
                                </td>
                                <td class="px-3">
                                    <span class="font-monospace fw-medium text-body"><i class="bi bi-calendar3 me-1 text-primary small"></i>{{ $gr->tanggal_gr ? date('d/m/Y', strtotime($gr->tanggal_gr)) : '-' }}</span>
                                </td>
                                <td class="px-3">
                                    <div class="fw-bold text-body fs-6 mb-0.5">{{ $gr->po?->supplier?->nama_supplier ?? '-' }}</div>
                                    @if(!empty($gr->po?->supplier?->npwp) && $gr->po->supplier->npwp !== '-')
                                    <div class="mt-1">
                                        <span class="badge font-monospace px-2 py-0.5 rounded-pill" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; font-weight: 600;" title="NPWP Terdaftar">
                                            <i class="bi bi-card-text me-1 text-success"></i>NPWP: {{ $gr->po->supplier->npwp }}
                                        </span>
                                    </div>
                                    @endif
                                    <div class="mt-1 small" style="color: #94a3b8; font-size: 0.75rem;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>Gudang: <strong class="text-body-secondary">{{ $gr->gudang?->nama_gudang ?? '-' }}</strong>
                                    </div>
                                </td>
                                <td class="px-3">
                                    <div>
                                        <span class="badge font-monospace fw-semibold px-2.5 py-1 rounded-pill" style="background: rgba(100, 116, 139, 0.18); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-text me-1 text-info"></i>{{ $gr->po?->nomor_po ?? '-' }}
                                        </span>
                                    </div>
                                    @if($grTenderNomor)
                                    <div class="mt-1.5">
                                        <span class="badge font-monospace px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 0.74rem; font-weight: 600;" title="Terkait Proyek Tender">
                                            <i class="bi bi-briefcase-fill text-warning"></i>
                                            <span>{{ $grTenderNomor }}</span>
                                        </span>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-3 text-end">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm btn-select-gr-row d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-lg fs-6"></i>
                                        <span>Pilih</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-semibold">Tidak ada dokumen GRN/BPB yang siap ditagihkan</div>
                                    <small class="text-muted">Semua penerimaan barang telah dibuatkan faktur atau belum ada penerimaan barang yang berstatus selesai.</small>
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
                    <i class="bi bi-info-circle me-1 text-primary"></i>Klik pada salah satu baris atau tekan tombol <strong>Pilih</strong> untuk memilih dokumen penerimaan barang.
                </div>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold ms-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
