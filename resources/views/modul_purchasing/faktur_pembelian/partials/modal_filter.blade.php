<!-- ===== MODAL FILTER PENCARIAN FAKTUR PEMBELIAN ===== -->
<style>
    /* Trigger Button */
    .inv-filter-trigger {
        display: inline-flex; align-items: center;
        padding: 0.38rem 0.85rem; border-radius: 10px;
        font-size: 0.82rem; font-weight: 600;
        color: #c7d2fe; background: rgba(99,102,241,0.12);
        border: 1px solid rgba(99,102,241,0.4);
        transition: all .2s ease;
    }
    .inv-filter-trigger:hover, .inv-filter-trigger:focus {
        color: #fff; background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-color: transparent; box-shadow: 0 6px 18px -6px rgba(139,92,246,0.7);
        transform: translateY(-1px);
    }
    .inv-filter-trigger.is-active {
        color: #fff; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-color: transparent;
    }
    .inv-filter-count {
        position: absolute; top: -7px; right: -7px;
        min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px;
        background: #f43f5e; color: #fff; font-size: 0.66rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 0 0 2px var(--bs-body-bg, #0f172a);
        animation: invFilterPop .25s ease;
    }
    @keyframes invFilterPop { from { transform: scale(0); } to { transform: scale(1); } }

    /* Active chips */
    .inv-active-filters {
        display: flex; align-items: center; flex-wrap: wrap; gap: .5rem;
        padding: .55rem .75rem; margin-bottom: .75rem; border-radius: 12px;
        background: rgba(99,102,241,0.07); border: 1px dashed rgba(99,102,241,0.35);
    }
    .inv-active-filters.d-none { display: none !important; }
    .inv-active-filters-label { font-size: .75rem; font-weight: 600; color: #a5b4fc; }
    .inv-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .2rem .35rem .2rem .65rem; border-radius: 999px;
        font-size: .73rem; font-weight: 500; color: #e0e7ff;
        background: rgba(99,102,241,0.18); border: 1px solid rgba(99,102,241,0.45);
    }
    .inv-chip b { color: #fff; font-weight: 600; }
    .inv-chip-x {
        border: 0; background: rgba(255,255,255,0.12); color: #e0e7ff;
        width: 17px; height: 17px; border-radius: 50%; line-height: 1;
        display: inline-flex; align-items: center; justify-content: center; font-size: .62rem;
    }
    .inv-chip-x:hover { background: #f43f5e; color: #fff; }
    .inv-clear-all-filter { color: #fda4af !important; font-size: .75rem; text-decoration: none; margin-left: auto; padding: 0; }
    .inv-clear-all-filter:hover { color: #f43f5e !important; }

    /* Modal */
    #modalFilterInvoice .modal-content {
        border: 1px solid rgba(148,163,184,0.18); border-radius: 18px; overflow: hidden;
        background: linear-gradient(180deg, rgba(30,41,59,0.98), rgba(15,23,42,0.98));
        box-shadow: 0 30px 60px -20px rgba(0,0,0,0.65);
        color: #e2e8f0;
    }
    #modalFilterInvoice .inv-filter-head {
        position: relative; padding: 1.1rem 1.35rem; overflow: hidden;
        background: linear-gradient(135deg, rgba(99,102,241,0.35), rgba(139,92,246,0.18) 55%, transparent);
        border-bottom: 1px solid rgba(148,163,184,0.15);
    }
    #modalFilterInvoice .inv-filter-head::after {
        content: ""; position: absolute; right: -40px; top: -60px; width: 180px; height: 180px;
        border-radius: 50%; background: radial-gradient(circle, rgba(139,92,246,0.45), transparent 70%);
    }
    #modalFilterInvoice .inv-filter-icon {
        width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
        color: #fff; background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 8px 20px -6px rgba(139,92,246,0.8);
    }
    #modalFilterInvoice .inv-filter-section {
        font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        color: #94a3b8; margin: 0 0 .55rem; display: flex; align-items: center; gap: .5rem;
    }
    #modalFilterInvoice .inv-filter-section::after { content: ""; flex: 1; height: 1px; background: rgba(148,163,184,0.18); }
    #modalFilterInvoice .form-label { font-size: .76rem; font-weight: 600; color: #cbd5e1; margin-bottom: .3rem; }
    #modalFilterInvoice .form-control, #modalFilterInvoice .form-select {
        background-color: rgba(15,23,42,0.7); border: 1px solid rgba(148,163,184,0.25);
        color: #f1f5f9; border-radius: 10px; font-size: .84rem;
    }
    #modalFilterInvoice .form-control:focus, #modalFilterInvoice .form-select:focus {
        border-color: #8b5cf6; box-shadow: 0 0 0 .2rem rgba(139,92,246,0.22);
    }
    #modalFilterInvoice input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.8); cursor: pointer; }
    #modalFilterInvoice .inv-range-sep { color: #64748b; font-size: .8rem; }

    /* Pill radio groups */
    #modalFilterInvoice .inv-pill-group { display: flex; flex-wrap: wrap; gap: .4rem; }
    #modalFilterInvoice .inv-pill-group input { display: none; }
    #modalFilterInvoice .inv-pill-group label {
        cursor: pointer; padding: .32rem .75rem; border-radius: 999px; font-size: .76rem; font-weight: 500;
        color: #cbd5e1; background: rgba(148,163,184,0.08); border: 1px solid rgba(148,163,184,0.25);
        transition: all .15s ease; user-select: none;
    }
    #modalFilterInvoice .inv-pill-group label:hover { border-color: rgba(139,92,246,0.6); color: #fff; }
    #modalFilterInvoice .inv-pill-group input:checked + label {
        color: #fff; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-color: transparent;
        box-shadow: 0 4px 12px -4px rgba(139,92,246,0.8);
    }
    #modalFilterInvoice .inv-pill-group input.pill-danger:checked + label { background: linear-gradient(135deg, #e11d48, #f43f5e); box-shadow: 0 4px 12px -4px rgba(244,63,94,0.8); }
    #modalFilterInvoice .inv-pill-group input.pill-warning:checked + label { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 4px 12px -4px rgba(245,158,11,0.8); }
    #modalFilterInvoice .inv-pill-group input.pill-success:checked + label { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 4px 12px -4px rgba(16,185,129,0.8); }

    #modalFilterInvoice .inv-quick-date { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
    #modalFilterInvoice .inv-quick-date button {
        font-size: .68rem; padding: .15rem .55rem; border-radius: 6px;
        color: #a5b4fc; background: transparent; border: 1px solid rgba(99,102,241,0.35);
    }
    #modalFilterInvoice .inv-quick-date button:hover { background: rgba(99,102,241,0.2); color: #fff; }

    #modalFilterInvoice .modal-footer { border-top: 1px solid rgba(148,163,184,0.15); background: rgba(15,23,42,0.6); }
    #modalFilterInvoice .btn-apply-filter {
        background: linear-gradient(135deg, #6366f1, #8b5cf6); border: 0; color: #fff; font-weight: 600;
        border-radius: 10px; padding: .5rem 1.2rem; box-shadow: 0 8px 20px -8px rgba(139,92,246,0.9);
    }
    #modalFilterInvoice .btn-apply-filter:hover { filter: brightness(1.1); color: #fff; }
    #modalFilterInvoice .btn-reset-filter {
        color: #cbd5e1; border: 1px solid rgba(148,163,184,0.3); border-radius: 10px; background: transparent;
    }
    #modalFilterInvoice .btn-reset-filter:hover { color: #fff; border-color: #f43f5e; background: rgba(244,63,94,0.12); }

    /* Select2 inside filter modal */
    #modalFilterInvoice .select2-container--bootstrap-5 .select2-selection {
        background-color: rgba(15,23,42,0.7); border: 1px solid rgba(148,163,184,0.25); border-radius: 10px; color: #f1f5f9; min-height: 38px;
    }
    #modalFilterInvoice .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { color: #f1f5f9; font-size: .84rem; }
</style>

<div class="modal fade" id="modalFilterInvoice" tabindex="-1" aria-labelledby="modalFilterInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="inv-filter-head d-flex align-items-center gap-3">
                <div class="inv-filter-icon"><i class="bi bi-funnel-fill"></i></div>
                <div class="position-relative" style="z-index:1;">
                    <h5 class="modal-title fw-bold mb-0 text-white" id="modalFilterInvoiceLabel">Filter Pencarian Faktur</h5>
                    <div class="small" style="color:#c7d2fe;">Persempit daftar tagihan supplier sesuai kriteria</div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto position-relative" style="z-index:1;" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form id="formFilterInvoice" autocomplete="off">
                <div class="modal-body p-3 p-md-4">

                    <!-- SUPPLIER -->
                    <div class="inv-filter-section"><i class="bi bi-building"></i>Supplier</div>
                    <div class="mb-4">
                        <select class="form-select" name="f_supplier_id" id="f_supplier_id" data-label="Supplier">
                            <option value="">Semua Supplier</option>
                            @foreach(($suppliers ?? []) as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->kode_supplier ? $sp->kode_supplier . ' - ' : '' }}{{ $sp->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- STATUS -->
                    <div class="inv-filter-section"><i class="bi bi-flag"></i>Status</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Status Dokumen</label>
                            <div class="inv-pill-group" data-name="f_status" data-label="Status">
                                <input type="radio" name="f_status" id="fs_all" value="" checked><label for="fs_all">Semua</label>
                                <input type="radio" name="f_status" id="fs_draft" value="Draft"><label for="fs_draft"><i class="bi bi-pencil-square me-1"></i>Draft</label>
                                <input type="radio" name="f_status" id="fs_approval" value="Approval" class="pill-warning"><label for="fs_approval"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</label>
                                <input type="radio" name="f_status" id="fs_posted" value="Posted" class="pill-success"><label for="fs_posted"><i class="bi bi-check-circle me-1"></i>Posted</label>
                                <input type="radio" name="f_status" id="fs_rejected" value="Rejected" class="pill-danger"><label for="fs_rejected"><i class="bi bi-x-circle me-1"></i>Ditolak</label>
                                <input type="radio" name="f_status" id="fs_cancel" value="Cancelled" class="pill-danger"><label for="fs_cancel"><i class="bi bi-x-octagon me-1"></i>Dibatalkan</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status Pembayaran</label>
                            <div class="inv-pill-group" data-name="f_payment" data-label="Pembayaran">
                                <input type="radio" name="f_payment" id="fp_all" value="" checked><label for="fp_all">Semua</label>
                                <input type="radio" name="f_payment" id="fp_belum" value="belum" class="pill-danger"><label for="fp_belum"><i class="bi bi-wallet2 me-1"></i>Belum Bayar</label>
                                <input type="radio" name="f_payment" id="fp_sebagian" value="sebagian" class="pill-warning"><label for="fp_sebagian"><i class="bi bi-pie-chart-fill me-1"></i>Sebagian</label>
                                <input type="radio" name="f_payment" id="fp_lunas" value="lunas" class="pill-success"><label for="fp_lunas"><i class="bi bi-check2-all me-1"></i>Lunas</label>
                            </div>
                        </div>
                    </div>

                    <!-- PERIODE -->
                    <div class="inv-filter-section"><i class="bi bi-calendar-range"></i>Periode</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Faktur</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="date" class="form-control" name="f_tgl_from" id="f_tgl_from">
                                <span class="inv-range-sep">s/d</span>
                                <input type="date" class="form-control" name="f_tgl_to" id="f_tgl_to">
                            </div>
                            <div class="inv-quick-date" data-from="#f_tgl_from" data-to="#f_tgl_to">
                                <button type="button" data-range="today">Hari ini</button>
                                <button type="button" data-range="7d">7 hari</button>
                                <button type="button" data-range="month">Bulan ini</button>
                                <button type="button" data-range="lastmonth">Bulan lalu</button>
                                <button type="button" data-range="year">Tahun ini</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Jatuh Tempo</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="date" class="form-control" name="f_due_from" id="f_due_from">
                                <span class="inv-range-sep">s/d</span>
                                <input type="date" class="form-control" name="f_due_to" id="f_due_to">
                            </div>
                            <div class="inv-quick-date" data-from="#f_due_from" data-to="#f_due_to">
                                <button type="button" data-range="today">Hari ini</button>
                                <button type="button" data-range="month">Bulan ini</button>
                                <button type="button" data-range="nextmonth">Bulan depan</button>
                            </div>
                        </div>
                    </div>

                    <!-- KONDISI JATUH TEMPO -->
                    <div class="inv-filter-section"><i class="bi bi-alarm"></i>Kondisi Jatuh Tempo</div>
                    <div class="inv-pill-group" data-name="f_due_state" data-label="Jatuh Tempo">
                        <input type="radio" name="f_due_state" id="fd_all" value="" checked><label for="fd_all">Semua</label>
                        <input type="radio" name="f_due_state" id="fd_overdue" value="overdue" class="pill-danger"><label for="fd_overdue"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat Jatuh Tempo</label>
                        <input type="radio" name="f_due_state" id="fd_today" value="today" class="pill-warning"><label for="fd_today"><i class="bi bi-bell-fill me-1"></i>Jatuh Tempo Hari Ini</label>
                        <input type="radio" name="f_due_state" id="fd_week" value="week"><label for="fd_week"><i class="bi bi-hourglass-split me-1"></i>7 Hari ke Depan</label>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between px-3 px-md-4 py-3">
                    <button type="button" class="btn btn-reset-filter" id="btnResetInvFilter">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-link text-decoration-none" style="color:#94a3b8;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-apply-filter" id="btnApplyInvFilter">
                            <i class="bi bi-search me-1"></i>Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
