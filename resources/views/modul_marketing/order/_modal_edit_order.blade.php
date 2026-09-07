{{-- Modal Edit Sales Order --}}
<div class="modal modal-blur fade" id="modalEditOrder" tabindex="-1" aria-labelledby="modalEditOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-5 d-flex align-items-center" id="modalEditOrderLabel">
                    <span class="p-2 bg-warning-subtle text-warning rounded-3 me-2 d-inline-flex align-items-center">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </span>
                    Edit Pesanan: <span class="text-warning ms-2 font-monospace" id="edit_so_nomor">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditOrder">
                @csrf
                <input type="hidden" id="edit_order_id" name="order_id">

                <div class="modal-body p-4">

                    {{-- Alert info --}}
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div class="small">Perubahan hanya berlaku untuk <strong>item yang sudah ada</strong>. Pesanan dengan status <strong>Selesai</strong> atau <strong>Dibatalkan</strong> tidak dapat diedit.</div>
                    </div>

                    {{-- Header: Customer & Payment --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pelanggan</label>
                            <select class="form-select form-select-sm" id="edit_customer_id" name="customer_id">
                                <option value="">-- Walk-In / Pelanggan Umum --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-telepon="{{ $cust->telepon ?? '' }}">
                                        {{ $cust->nama_customer }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="edit_walkin_wrap">
                            <label class="form-label fw-semibold small">Nama Walk-In</label>
                            <input type="text" class="form-control form-control-sm" id="edit_nama_walkin" name="nama_customer_walkin" placeholder="Nama pelanggan walk-in...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Metode Pembayaran</label>
                            <select class="form-select form-select-sm" id="edit_payment_method_id" name="payment_method_id">
                                <option value="">-- Pilih Metode --</option>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Termin Pembayaran</label>
                            <select class="form-select form-select-sm" id="edit_payment_term_id" name="payment_term_id">
                                <option value="">-- Pilih Termin --</option>
                                @foreach($paymentTerms as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->nama_term }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Catatan Order</label>
                            <textarea class="form-control form-control-sm" id="edit_catatan" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>

                    {{-- Item Table --}}
                    <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul text-primary"></i> Daftar Item Pesanan
                    </h6>
                    <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                        <table class="table table-hover align-middle mb-0" id="edit_items_table">
                            <thead class="table-light">
                                <tr class="small text-uppercase text-secondary fw-bold">
                                    <th style="width:40px" class="text-center">No</th>
                                    <th>Barang / Produk</th>
                                    <th class="text-center" style="width:110px">Qty</th>
                                    <th class="text-end" style="width:150px">Harga Satuan</th>
                                    <th class="text-end" style="width:130px">Diskon</th>
                                    <th class="text-end" style="width:150px">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="edit_items_tbody">
                                {{-- Populated by JS --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Diskon & Pajak --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-body-tertiary">
                                <div class="fw-semibold small mb-2">Diskon Order</div>
                                <div class="input-group input-group-sm">
                                    <select class="form-select" id="edit_diskon_tipe" name="diskon_tipe" style="max-width: 70px;">
                                        <option value="Nominal">Rp</option>
                                        <option value="Persen">%</option>
                                    </select>
                                    <input type="number" class="form-control" id="edit_diskon_val" name="diskon_nilai" value="0" min="0" step="any">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-body-tertiary">
                                <div class="fw-semibold small mb-2">Pajak (PPN)</div>
                                <select class="form-select form-select-sm" id="edit_pajak_tipe" name="pajak_tipe">
                                    <option value="None">Non PPN</option>
                                    <option value="PPN11">PPN 11%</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="p-3 rounded-3 border bg-body-tertiary">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-body-secondary">Subtotal Barang:</span>
                            <span class="font-monospace fw-bold" id="edit_summary_subtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-body-secondary">Diskon Order:</span>
                            <span class="font-monospace fw-bold text-danger" id="edit_summary_diskon">- Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-body-secondary">Pajak (PPN):</span>
                            <span class="font-monospace fw-bold text-info" id="edit_summary_pajak">+ Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fw-bold">Grand Total:</span>
                            <span class="font-monospace fw-bold fs-4 text-success" id="edit_summary_grand_total">Rp 0</span>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold" id="btn_submit_edit_order">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
