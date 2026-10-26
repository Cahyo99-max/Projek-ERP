<!-- Modal Filter Pengiriman / Delivery Order -->
<div class="modal fade" id="modalFilterDO" tabindex="-1" aria-labelledby="modalFilterDOLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg do-modal-container overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 do-modal-header border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="do-icon-badge">
                        <i class="bi bi-funnel-fill"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalFilterDOLabel">Filter Pengiriman &amp; Surat Jalan</h6>
                        <small class="text-muted" style="font-size: 0.75rem;">Saring data berdasarkan status, gudang asal, proyek tender, dan tanggal</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 d-flex flex-column gap-3.5">
                
                <!-- Filter 1: Status Pengiriman -->
                <div>
                    <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                        <i class="bi bi-flag-fill text-primary"></i>
                        <span>Status Pengiriman</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text do-input-icon"><i class="bi bi-tag text-muted"></i></span>
                        <select class="form-select do-form-control fw-semibold" id="filter_status">
                            <option value="">Semua Status</option>
                            <option value="Menunggu Persetujuan">⏳ Menunggu Persetujuan</option>
                            <option value="Disetujui">✅ Disetujui (ACC)</option>
                            <option value="Siap Dikirim">📦 Siap Dikirim</option>
                            <option value="Dalam Perjalanan">🚚 Dalam Perjalanan (In Transit)</option>
                            <option value="Terkirim">🎉 Terkirim (Selesai)</option>
                            <option value="Dibatalkan">❌ Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <!-- Filter 2: Gudang Asal Pengeluaran Barang -->
                <div>
                    <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center justify-content-between">
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-building-check text-primary"></i>
                            <span>Gudang Asal</span>
                        </span>
                        @if(!empty(session('active_branch_id')))
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Cabang Aktif
                            </span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>PT Pusat
                            </span>
                        @endif
                    </label>
                    <div class="input-group">
                        <span class="input-group-text do-input-icon"><i class="bi bi-box-seam text-muted"></i></span>
                        <select class="form-select do-form-control fw-semibold" id="filter_gudang_id">
                            <option value="">Semua Gudang</option>
                            @foreach($gudangs as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_gudang }} &nbsp;({{ $g->kode_gudang }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter 3: Proyek / Tender Kontrak -->
                <div>
                    <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                        <i class="bi bi-briefcase-fill text-primary"></i>
                        <span>Tender / Proyek Kontrak</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text do-input-icon"><i class="bi bi-file-earmark-text text-muted"></i></span>
                        <select class="form-select do-form-control fw-semibold" id="filter_tender_id">
                            <option value="">Semua (Tender &amp; Reguler)</option>
                            <option value="non_tender">🚫 Non-Tender (Pesanan Reguler)</option>
                            @if(isset($tenders) && $tenders->count() > 0)
                                <optgroup label="Daftar Tender Aktif">
                                    @foreach($tenders as $t)
                                        <option value="{{ $t->id }}">
                                            {{ $t->nomor_kontrak ? '[' . $t->nomor_kontrak . '] ' : '' }}{{ $t->nama_tender }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Filter 4: Rentang Tanggal Pengiriman -->
                <div>
                    <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                        <i class="bi bi-calendar3 text-primary"></i>
                        <span>Rentang Tanggal Pengiriman</span>
                    </label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text do-input-icon"><i class="bi bi-calendar-event text-muted"></i></span>
                                <input type="date" class="form-control do-form-control font-monospace" id="filter_start_date" placeholder="Dari">
                            </div>
                            <div class="form-text small text-muted mt-1" style="font-size: 0.7rem;">Tanggal Mulai</div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text do-input-icon"><i class="bi bi-calendar-check text-muted"></i></span>
                                <input type="date" class="form-control do-form-control font-monospace" id="filter_end_date" placeholder="Sampai">
                            </div>
                            <div class="form-text small text-muted mt-1" style="font-size: 0.7rem;">Tanggal Selesai</div>
                        </div>
                    </div>

                    <!-- Preset Tombol Cepat -->
                    <div class="d-flex align-items-center gap-1.5 mt-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 rounded-pill font-monospace btn-preset-date" data-preset="today" style="font-size: 0.72rem;">Hari Ini</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 rounded-pill font-monospace btn-preset-date" data-preset="last7" style="font-size: 0.72rem;">7 Hari Terakhir</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 rounded-pill font-monospace btn-preset-date" data-preset="thismonth" style="font-size: 0.72rem;">Bulan Ini</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2 rounded-pill font-monospace btn-preset-date" data-preset="clear" style="font-size: 0.72rem;">Hapus Tanggal</button>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2.5 px-4 do-modal-footer border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-none" id="btn_modal_reset_filter">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-none" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm do-btn-primary d-flex align-items-center gap-1.5" id="btn_modal_apply_filter">
                        <i class="bi bi-check2 fs-6"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
