<!-- Modal Tambah Jurnal Manual / Penyesuaian -->
<div class="modal fade" id="modalCreateJurnal" tabindex="-1" aria-labelledby="modalCreateJurnalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <div class="modal-header py-3 px-4 kb-modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge bg-primary-subtle text-primary rounded-3">
                        <i class="bi bi-journal-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalCreateJurnalLabel">Buat Jurnal Penyesuaian / Manual</h5>
                        <p class="text-muted small mb-0 font-monospace">Pencatatan pembukuan double-entry (Debit &amp; Kredit)</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCreateJurnal" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Header Info Transaksi -->
                    <div class="row g-3 mb-4 p-3 rounded-3 bg-body-tertiary border">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted mb-1">Tanggal Jurnal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm kb-form-control rounded-3" name="tanggal_jurnal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted mb-1">Keterangan / Deskripsi Transaksi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm kb-form-control rounded-3" name="keterangan" placeholder="Contoh: Penyesuaian Beban Sewa Kantor Bulan Ini / Koreksi Pembukuan..." required>
                        </div>
                    </div>

                    <!-- Dynamic Entry Rows -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-1.5">
                            <i class="bi bi-list-columns-reverse text-primary"></i> Rincian Baris Akun COA (Double-Entry)
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" id="btn_add_jurnal_row">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Baris Akun
                        </button>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableJurnalRows">
                            <thead>
                                <tr class="text-uppercase small fw-bold text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 55%, #1d4ed8 100%) !important;">
                                    <th class="py-2.5 px-3" style="min-width: 250px; border-right: 1px solid rgba(255,255,255,0.2);">Akun COA (Chart of Account) <span class="text-warning">*</span></th>
                                    <th class="py-2.5 px-3 text-end" style="width: 200px; border-right: 1px solid rgba(255,255,255,0.2);">Debit (Rp)</th>
                                    <th class="py-2.5 px-3 text-end" style="width: 200px; border-right: 1px solid rgba(255,255,255,0.2);">Kredit (Rp)</th>
                                    <th class="py-2.5 px-3" style="min-width: 200px; border-right: 1px solid rgba(255,255,255,0.2);">Catatan Baris</th>
                                    <th class="py-2.5 px-3 text-center" style="width: 50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="jurnal_rows_container">
                                <!-- Row 1: Debit Default -->
                                <tr class="jurnal-row">
                                    <td>
                                        <select class="form-select form-select-sm kb-form-control select-coa" name="details[0][account_id]" required>
                                            <option value="">-- Pilih Akun COA --</option>
                                            @foreach($coaAccounts as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }} ({{ $coa->account_type }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-debit" name="details[0][debit]" placeholder="0" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-kredit" name="details[0][kredit]" placeholder="0" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control" name="details[0][keterangan]" placeholder="Opsional...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-remove-row shadow-none disabled" style="width: 28px; height: 28px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 2: Kredit Default -->
                                <tr class="jurnal-row">
                                    <td>
                                        <select class="form-select form-select-sm kb-form-control select-coa" name="details[1][account_id]" required>
                                            <option value="">-- Pilih Akun COA --</option>
                                            @foreach($coaAccounts as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }} ({{ $coa->account_type }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-debit" name="details[1][debit]" placeholder="0" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-kredit" name="details[1][kredit]" placeholder="0" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kb-form-control" name="details[1][keterangan]" placeholder="Opsional...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-remove-row shadow-none" style="width: 28px; height: 28px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-body-tertiary fw-bold">
                                <tr>
                                    <td class="text-end">TOTAL:</td>
                                    <td class="text-end font-monospace text-success" id="calc_total_debit">Rp 0</td>
                                    <td class="text-end font-monospace text-primary" id="calc_total_kredit">Rp 0</td>
                                    <td colspan="2" class="text-center">
                                        <span id="badge_balance_status" class="badge bg-secondary-subtle text-secondary border rounded-pill font-monospace px-3 py-1">
                                            Masukkan Nominal
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="modal-footer py-2.5 px-4 kb-modal-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Total Debit &amp; Total Kredit harus bernilai sama (Balance).
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btn_save_jurnal">
                            <i class="bi bi-check-circle me-1"></i> Posting Jurnal
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
