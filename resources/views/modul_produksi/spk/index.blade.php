<x-layouts title="Surat Perintah Kerja (SPK / Work Order) - Modul Produksi">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<style>
    @keyframes spkPulse {
        0% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(2.2); opacity: 0; }
        100% { transform: scale(1); opacity: 0; }
    }
</style>
@endpush
<div class="container-fluid px-2 px-sm-4 py-3">
    {{-- BREADCRUMB & HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 gap-sm-3 mb-3 mb-sm-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-nowrap flex-nowrap overflow-hidden">
                    <li class="breadcrumb-item"><a href="{{ url('/dashbord') }}" class="text-decoration-none text-theme-muted"><i class="bi bi-house-door-fill me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item text-theme-muted d-none d-sm-inline">Modul Produksi</li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">SPK Produksi</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold page-title mb-0 fs-5 fs-sm-3">Surat Perintah Kerja (SPK)</h4>
                <div id="spkLiveIndicator"></div>
            </div>
            <p class="text-theme-muted small mb-0 d-none d-sm-block">Penerbitan perintah produksi, kalkulasi kebutuhan bahan baku, dan kontrol tahapan pengerjaan.</p>
        </div>
        <div class="mt-1 mt-sm-0">
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-spk'))
                <button type="button" class="btn btn-primary rounded-pill px-3 px-sm-4 py-2 fw-bold shadow-sm w-100 w-sm-auto" onclick="bukaModalTambahSpk()">
                    <i class="bi bi-plus-lg me-1"></i> Terbitkan SPK Baru
                </button>
            @endif
        </div>
    </div>

    {{-- KARTU KPI RINGKASAN SPK (4 VIBRANT GRADIENT CARDS) --}}
    <div class="row g-3 mb-4">
        <!-- Card 1: TOTAL SEMUA SPK (Blue) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer"
                 onclick="filterSpkByStatus('')" title="Klik untuk menampilkan semua SPK"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); min-height: 115px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative" style="z-index: 2;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">TOTAL SPK</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                            <i class="bi bi-file-earmark-ruled fs-5 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-extrabold text-white mb-0 mt-2" style="font-size: 32px; line-height: 1;" id="kpiTotalSpk">0</h2>
                        <small class="text-white-50 d-block mt-1" style="font-size: 11.5px;">Semua surat perintah kerja</small>
                    </div>
                </div>
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
            </div>
        </div>

        <!-- Card 2: DALAM PROSES / IN PROGRESS (Cyan / Blue) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer"
                 onclick="filterSpkByStatus('In Progress')" title="Klik untuk filter SPK yang sedang dikerjakan"
                 style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); min-height: 115px; box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.45) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative" style="z-index: 2;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">DALAM PROSES (IN PROGRESS)</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                            <i class="bi bi-arrow-repeat fs-5 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-extrabold text-white mb-0 mt-2" style="font-size: 32px; line-height: 1;" id="kpiInProgressSpk">0</h2>
                        <small class="text-white-50 d-block mt-1" style="font-size: 11.5px;">Sedang berjalan di line produksi</small>
                    </div>
                </div>
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
            </div>
        </div>

        <!-- Card 3: SELESAI / COMPLETED (Green) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer"
                 onclick="filterSpkByStatus('Completed')" title="Klik untuk filter SPK selesai"
                 style="background: linear-gradient(135deg, #047857 0%, #10b981 100%); min-height: 115px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.45) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative" style="z-index: 2;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">SELESAI (COMPLETED)</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                            <i class="bi bi-patch-check-fill fs-5 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-extrabold text-white mb-0 mt-2" style="font-size: 32px; line-height: 1;" id="kpiCompletedSpk">0</h2>
                        <small class="text-white-50 d-block mt-1" style="font-size: 11.5px;">Siap transfer ke Gudang Jadi</small>
                    </div>
                </div>
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
            </div>
        </div>

        <!-- Card 4: TOTAL TARGET UNIT (Indigo / Purple) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%); min-height: 115px; box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.45) !important;">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative" style="z-index: 2;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">TOTAL TARGET PRODUKSI</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                            <i class="bi bi-bullseye fs-5 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-extrabold text-white mb-0 mt-2 font-monospace" style="font-size: 28px; line-height: 1;" id="kpiTotalQty">0 Pcs</h2>
                        <small class="text-white-50 d-block mt-1" style="font-size: 11.5px;">Akumulasi target seluruh SPK</small>
                    </div>
                </div>
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
            </div>
        </div>
    </div>

    {{-- FILTER & TABLE CARD --}}
    <div class="spk-card border-0 rounded-4 shadow-sm">
        <div class="card-body p-2.5 p-sm-4">
            {{-- FILTER TRIGGER BAR & ACTIVE BADGES --}}
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 pb-3 border-bottom gap-2.5">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalFilterSpk">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Filter Data</span>
                        <span id="badgeActiveFilterCount" class="badge bg-primary rounded-circle d-none" style="font-size: 11px;">0</span>
                    </button>
                    <button type="button" id="btnResetFilterQuick" class="btn btn-sm btn-link text-decoration-none text-muted d-none small p-0 ms-1 text-nowrap" onclick="resetFilterSpk()">
                        <i class="bi bi-x-circle me-1"></i>Reset Filter
                    </button>
                </div>
                <div id="activeFilterBadges" class="d-flex flex-wrap gap-1.5 align-items-center"></div>
            </div>

            {{-- TABLE SPK (Smooth Touch Horizontal Scroll) --}}
            <div class="spk-table-wrapper rounded-3" style="border: 1px solid var(--spk-border-color); overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch;">
                <table class="table table-hover align-middle mb-0 text-nowrap w-100 spk-table" id="tabelMasterSpk" style="min-width: 920px;">
                    <thead>
                        <tr class="small text-uppercase fw-bold">
                            <th style="width: 45px;" class="text-center">No</th>
                            <th style="min-width: 140px;">No. SPK</th>
                            <th style="min-width: 240px;">Barang Jadi & Resep</th>
                            <th style="min-width: 110px;" class="text-center">Target Qty</th>
                            <th style="min-width: 110px;" class="text-center">Deadline</th>
                            <th style="min-width: 120px;" class="text-center">Status</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
@include('modul_produksi.spk._modal_filter')
@include('modul_produksi.spk._modal_input')
@include('modul_produksi.spk._modal_edit')
@include('modul_produksi.spk._modal_detail')
@include('modul_produksi.spk._modal_lookup_bom')
@include('modul_produksi.spk._modal_cetak')
@include('modul_produksi.spk._modal_progress')

@push('scripts')
<script>
    let dtSpk = null;

    $(document).ready(function() {
        dtSpk = $('#tabelMasterSpk').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("produksi.spk.index") }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.prioritas = $('#filter_prioritas').val();
                    d.tipe_spk = $('#filter_tipe').val();
                },
                dataSrc: function(json) {
                    if (json.kpi) {
                        $('#kpiTotalSpk').text(json.kpi.total_spk || 0);
                        $('#kpiInProgressSpk').text(json.kpi.in_progress_spk || 0);
                        $('#kpiCompletedSpk').text(json.kpi.completed_spk || 0);
                        $('#kpiTotalQty').text(Number(json.kpi.total_qty_rencana || 0).toLocaleString('id-ID') + ' Pcs');
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-secondary' },
                { data: 'nomor_spk_badge', name: 'nomor_spk' },
                { data: 'barang_jadi_name', name: 'barangJadi.nama_barang' },
                { data: 'target_qty_fmt', name: 'jumlah_rencana', className: 'text-center' },
                { data: 'deadline_fmt', name: 'target_selesai', className: 'text-center' },
                { data: 'status_badge', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nomor SPK, barang jadi, PIC...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ SPK",
                infoEmpty: "Tidak ada data SPK",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            }
        });
    });

    // ═══════════════════════════════════════════════════════════
    // REAL-TIME AUTO POLLING — Fast 3 Detik (Near Instant)
    // Pause saat modal terbuka / tab tidak aktif
    // Silent reload: Tanpa menampilkan spinner loading DataTables
    // ═══════════════════════════════════════════════════════════
    const SPK_POLL_INTERVAL = 3000; // 3 detik
    let spkPollTimer      = null;
    let spkCountdown      = SPK_POLL_INTERVAL / 1000;
    let spkCountdownTimer = null;
    let spkPollPaused     = false;
    let spkIsLoading      = false;
    let spkIsSilentReload = false;
    let spkLastSnapshot   = null; // untuk deteksi perubahan status

    function reloadTableSpk(silent = false) {
        if (dtSpk && !spkIsLoading) {
            spkIsLoading = true;
            spkIsSilentReload = silent;
            if (silent) {
                $('#tabelMasterSpk_wrapper .dataTables_processing').hide();
            }
            dtSpk.ajax.reload(function(json) {
                spkIsLoading = false;
                spkIsSilentReload = false;
                // Deteksi perubahan status lewat KPI
                if (spkLastSnapshot && json.kpi) {
                    const cur  = json.kpi;
                    const prev = spkLastSnapshot;
                    if (cur.total_spk       !== prev.total_spk       ||
                        cur.in_progress_spk !== prev.in_progress_spk ||
                        cur.completed_spk   !== prev.completed_spk) {
                        showSpkLiveToast('⚡ Ada perubahan status SPK — data diperbarui.');
                    }
                }
                if (json.kpi) spkLastSnapshot = {...json.kpi};
            }, false);
        }
    }

    function startSpkPolling() {
        stopSpkPolling();
        spkCountdown = SPK_POLL_INTERVAL / 1000;
        updateLiveIndicator();

        spkCountdownTimer = setInterval(function() {
            if (spkPollPaused) return;
            spkCountdown--;
            updateLiveIndicator();
            if (spkCountdown <= 0) {
                spkCountdown = SPK_POLL_INTERVAL / 1000;
                reloadTableSpk(true);
            }
        }, 1000);
    }

    function stopSpkPolling() {
        if (spkCountdownTimer) { clearInterval(spkCountdownTimer); spkCountdownTimer = null; }
    }

    function pauseSpkPolling()  { spkPollPaused = true;  updateLiveIndicator(); }
    function resumeSpkPolling() {
        spkPollPaused = false;
        spkCountdown  = SPK_POLL_INTERVAL / 1000;
        updateLiveIndicator();
    }

    function updateLiveIndicator() {
        const el = document.getElementById('spkLiveIndicator');
        if (!el) return;
        if (spkPollPaused) {
            el.innerHTML = `<span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1" style="font-size:10px;"><i class="bi bi-pause-circle me-1"></i>Paused</span>`;
        } else {
            el.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size:10px;">
                <span class="position-relative me-1" style="display:inline-block;width:7px;height:7px;vertical-align:middle;">
                    <span class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-success opacity-75" style="animation:spkPulse 1s ease-in-out infinite;"></span>
                    <span class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-success"></span>
                </span>
                Live &bull; ${spkCountdown}s
            </span>`;
        }
    }

    function showSpkLiveToast(msg) {
        const existing = document.getElementById('spkLiveToast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.id = 'spkLiveToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;max-width:320px;';
        toast.innerHTML = `
            <div class="toast show align-items-center text-white border-0 rounded-3 shadow" style="background:#1d4ed8;">
                <div class="d-flex">
                    <div class="toast-body fw-semibold" style="font-size:13px;"><i class="bi bi-arrow-repeat me-2"></i>${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('#spkLiveToast').remove()"></button>
                </div>
            </div>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    // Pause polling saat modal apapun terbuka
    document.addEventListener('show.bs.modal', function() { pauseSpkPolling(); });
    document.addEventListener('hidden.bs.modal', function() {
        // Hanya resume jika tidak ada modal lain yang masih terbuka
        if (!document.querySelector('.modal.show')) resumeSpkPolling();
    });

    // Pause saat tab tidak aktif (Page Visibility API)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) { pauseSpkPolling(); }
        else { resumeSpkPolling(); reloadTableSpk(true); }
    });

    // Suppress spinner loading bawaan DataTables saat auto-poll silent
    $('#tabelMasterSpk').on('processing.dt', function(e, settings, processing) {
        if (spkIsSilentReload && processing) {
            $(this).closest('.dataTables_wrapper').find('.dataTables_processing').css('display', 'none');
        }
    });

    // Mulai polling setelah DataTable pertama kali siap
    $('#tabelMasterSpk').on('init.dt', function() {
        startSpkPolling();
    });


    function terapkanFilterSpkModal() {
        $('#modalFilterSpk').modal('hide');
        updateActiveFilterBadges();
        reloadTableSpk();
    }

    function updateActiveFilterBadges() {
        const status = $('#filter_status').val();
        const prioritas = $('#filter_prioritas').val();
        const tipe = $('#filter_tipe').val();
        
        let activeCount = 0;
        let badgesHtml = '';

        if (status && status !== 'all') {
            activeCount++;
            badgesHtml += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">Status: ${status}</span>`;
        }
        if (prioritas && prioritas !== 'all') {
            activeCount++;
            badgesHtml += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">Prioritas: ${prioritas}</span>`;
        }
        if (tipe && tipe !== 'all') {
            activeCount++;
            badgesHtml += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">Tipe: ${tipe}</span>`;
        }

        if (activeCount > 0) {
            $('#badgeActiveFilterCount').text(activeCount).removeClass('d-none');
            $('#btnResetFilterQuick').removeClass('d-none');
        } else {
            $('#badgeActiveFilterCount').addClass('d-none');
            $('#btnResetFilterQuick').addClass('d-none');
        }
        $('#activeFilterBadges').html(badgesHtml);
    }

    function filterSpkByStatus(status) {
        $('#filter_status').val(status || 'all');
        updateActiveFilterBadges();
        reloadTableSpk();
    }

    function resetFilterSpk() {
        $('#filter_status').val('all');
        $('#filter_prioritas').val('all');
        $('#filter_tipe').val('all');
        $('#modalFilterSpk').modal('hide');
        updateActiveFilterBadges();
        reloadTableSpk();
    }

    let isSpkActionProcessing = false;

    function closeModalThenAction(modalId, callback) {
        const $modal = $(modalId);
        if ($modal.length && $modal.hasClass('show')) {
            $modal.one('hidden.bs.modal', function() {
                setTimeout(callback, 100);
            });
            $modal.modal('hide');
        } else {
            callback();
        }
    }

    function sendPendingSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Ajukan SPK ke Pending?',
                text: 'Surat Perintah Kerja akan diajukan ke atasan untuk disetujui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Ajukan (Pending)',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/send-pending`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Berhasil Diajukan!', text: res.message, timer: 1800, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    }

    function approveSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Setujui SPK ini?',
                text: 'SPK yang disetujui (Approved) akan siap dimulai pengerjaannya di line produksi.',
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Setujui (Approve)',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Menyimpan Persetujuan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/approve`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Disetujui!', text: res.message, timer: 1800, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal Menyetujui', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    }

    function rejectSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Tolak SPK Produksi?',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan / Catatan Revisi',
                inputPlaceholder: 'Tuliskan alasan penolakan atau penyesuaian jadwal/bahan...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Tolak SPK',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value || value.trim() === '') return 'Harap masukkan alasan penolakan!';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/reject`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', alasan: result.value },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'info', title: 'SPK Ditolak', text: res.message, timer: 1800, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal Menolak', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    }

    function startProductionSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Mulai Pengerjaan Produksi?',
                text: 'Status SPK akan diubah menjadi Dalam Proses (In Progress) dan tahapan pertama akan dimulai.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Mulai Produksi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/start-production`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Produksi Dimulai!', text: res.message, timer: 1800, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });
    }

    function cancelSpk(id, nomorSpk) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Batalkan SPK Produksi?',
                html: `Apakah Anda yakin ingin membatalkan SPK <b>${nomorSpk || ''}</b>?<br><small class="text-danger">Tindakan ini hanya dapat dilakukan oleh Super Admin & Owner.</small>`,
                input: 'textarea',
                inputLabel: 'Alasan Pembatalan SPK',
                inputPlaceholder: 'Masukkan alasan pembatalan SPK ini...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Ya, Batalkan SPK',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value || value.trim() === '') return 'Harap masukkan alasan pembatalan SPK!';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Membatalkan SPK...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/cancel-production`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', alasan: result.value },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Dibatalkan!', text: res.message, timer: 2000, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal Membatalkan', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    }

    function completeProductionSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Selesaikan Produksi SPK ini?',
                text: 'Pastikan seluruh produk jadi telah melewati proses QC dan siap ditransfer ke Gudang Jadi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Selesaikan (Completed)',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}/complete-production`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Produksi Selesai!', text: res.message, timer: 1800, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                            Swal.fire({
                                title: '❌ Produksi Belum Selesai!',
                                html: `<div class="text-start text-white-50 mt-1">${msg}</div>`,
                                icon: 'error',
                                confirmButtonColor: '#0ea5e9',
                                confirmButtonText: 'Mengerti / Selesaikan Tahapan Dulu'
                            });
                        }
                    });
                }
            });
        });
    }

    function deleteSpk(id) {
        if (isSpkActionProcessing) return;
        closeModalThenAction('#modalDetailSpk', function() {
            Swal.fire({
                title: 'Hapus SPK ini?',
                text: 'Data SPK yang dihapus tidak dapat dipulihkan kembali.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isSpkActionProcessing = true;
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: `/produksi/spk/${id}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            isSpkActionProcessing = false;
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                            reloadTableSpk();
                        },
                        error: function(xhr) {
                            isSpkActionProcessing = false;
                            Swal.fire('Gagal Menghapus', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });
    }
</script>
@endpush
</x-layouts>
