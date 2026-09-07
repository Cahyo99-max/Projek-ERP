<!-- MODAL POPUP BROWSE GRN/BPB -->
<div class="modal fade" id="modalBrowseGR" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 999999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0">Pilih Dokumen Penerimaan Barang (GRN/BPB)</h5>
                        <div class="text-muted extra-small">Pilih dokumen penerimaan barang yang akan ditagihkan ke supplier</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 rounded-end-3" id="search_gr_lookup" placeholder="Ketik No. GRN, Supplier, atau No. PO untuk mencari...">
                    </div>
                </div>
                <div class="table-responsive rounded-3 border">
                    <table class="table align-middle table-hover mb-0" id="tableBrowseGR">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th width="22%">NO. GRN / BPB</th>
                                <th width="15%">TANGGAL</th>
                                <th width="28%">SUPPLIER</th>
                                <th width="20%">REF. PO</th>
                                <th width="15%" class="text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eligibleGrs as $gr)
                            <tr class="row-gr-item"
                                data-id="{{ $gr->id }}"
                                data-nomor="{{ $gr->nomor_gr }}"
                                data-supplier="{{ $gr->po?->supplier?->nama_supplier }}"
                                data-gudang="{{ $gr->gudang?->nama_gudang }}"
                                data-po="{{ $gr->po?->nomor_po }}"
                                data-currency="{{ $gr->po?->currency?->kode_currency ? ($gr->po->currency->kode_currency . ' (' . ($gr->po->currency->nama_currency ?? $gr->po->currency->kode_currency) . ')') : 'IDR (Rupiah)' }}"
                                data-currency-code="{{ $gr->po?->currency?->kode_currency ?? 'IDR' }}"
                                data-currency-symbol="{{ $gr->po?->currency?->simbol ?? ($gr->po?->currency?->kode_currency === 'USD' ? '$' : 'Rp') }}"
                                data-kurs="{{ $gr->po?->kurs ? number_format($gr->po->kurs, 4, '.', '') : '1.0000' }}"
                                data-payment-term="{{ $gr->po?->payment_term_id }}"
                                data-pajak="{{ $gr->po?->pajak_id }}"
                                style="cursor: pointer;">
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 font-monospace fw-bold">
                                        {{ $gr->nomor_gr }}
                                    </span>
                                </td>
                                <td class="small">{{ $gr->tanggal_gr ? date('d/m/Y', strtotime($gr->tanggal_gr)) : '-' }}</td>
                                <td>
                                    <div class="fw-bold text-body">{{ $gr->po?->supplier?->nama_supplier ?? '-' }}</div>
                                    <div class="extra-small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $gr->gudang?->nama_gudang ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace">
                                        {{ $gr->po?->nomor_po ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary rounded-2 px-3 btn-select-gr-row">
                                        <i class="bi bi-check2 me-1"></i> Pilih
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Tidak ada dokumen GRN/BPB yang siap ditagihkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4 bg-body-tertiary">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
