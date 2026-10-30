<x-layouts title="Stock Opname (Penyesuaian Stok)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/stok_opname.css') }}">

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- ─── Status Warning / Info Alert ─── -->
        @php
            $activeBranchId = session('active_branch_id');
            $activeGudangs = \App\Models\MasterGudang::where('cabang_id', $activeBranchId)->where('status', 'Active')->pluck('id');
            
            $activeOpnameExists = \App\Models\StockOpname::whereIn('gudang_id', $activeGudangs)
                ->whereIn('status', ['Approved', 'Sedang Berjalan'])
                ->exists();
            
            $isSupervisorOrAdmin = auth()->user()->hasAnyPermission(['setuju-opname', 'closing-opname']) || auth()->user()->hasRole('Super Admin');
        @endphp

        @if(!$activeOpnameExists && !$isSupervisorOrAdmin)
            <div class="alert alert-warning py-3 mb-4 rounded-3 border d-flex align-items-center shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Stock Opname belum memiliki persetujuan.</h6>
                    <span class="small text-muted">Anda tidak dapat melakukan input perhitungan fisik barang. Hubungi supervisor atau buat pengajuan rencana baru di bawah.</span>
                </div>
            </div>
        @endif

        <!-- ─── Page Header ─────────────────────────────────────────── -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-sliders text-primary me-2"></i>Pengajuan & Approval Stock Opname
                </h4>
                <small class="text-muted">Manajemen rencana opname (Full / Partial), approval atasan, dan input stok fisik</small>
            </div>
            @can('tambah-opname')
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahOpname" id="btnTambahOpname">
                <i class="bi bi-plus-lg me-1"></i> Buat Pengajuan Opname
            </button>
            @endcan
        </div>

        <!-- ─── Table Card ───────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>Daftar Rencana Stock Opname
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 fw-semibold">
                    {{ $opnames->count() }} Dokumen
                </span>
            </div>
            <div class="card-body p-0" id="opname_card_body">
                @if($opnames->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                        <p class="text-muted mb-1">Belum ada pengajuan stock opname yang dibuat.</p>
                        @can('tambah-opname')
                        <small class="text-muted">Klik tombol <strong>"Buat Pengajuan Opname"</strong> untuk memulai proposal.</small>
                        @endcan
                    </div>
                @else
                    <table class="table table-hover table-modern mb-0" id="tableOpname" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Nomor Pengajuan</th>
                                <th width="15%">Gudang</th>
                                <th width="10%">Tgl Mulai</th>
                                <th width="10%">Tgl Selesai</th>
                                <th width="12%">Jenis</th>
                                <th width="13%" class="text-center">Status</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_opname_list">
                            @foreach($opnames as $idx => $so)
                            <tr onclick="showDetailOpname({{ $so->id }})" id="row-so-{{ $so->id }}">
                                <td class="text-muted small text-center">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-dark" style="font-size:.85rem">{{ $so->nomor_opname }}</span>
                                </td>
                                    <td>
                                        <span style="font-size:.85rem">{{ $so->gudang ? $so->gudang->nama_gudang : '-' }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size:.84rem" class="text-primary fw-semibold">{{ $so->tanggal_mulai ? $so->tanggal_mulai->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size:.84rem" class="text-secondary fw-semibold">{{ $so->tanggal_selesai ? $so->tanggal_selesai->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 small fw-semibold">{{ $so->jenis_opname }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($so->status === 'Draft')
                                            <span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft Plan</span>
                                        @elseif($so->status === 'Menunggu Persetujuan')
                                            <span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>
                                        @elseif($so->status === 'Approved')
                                            <span class="badge-approved"><i class="bi bi-check2-circle me-1"></i>Approved (ACC)</span>
                                        @elseif($so->status === 'Sedang Berjalan')
                                            <span class="badge-running"><i class="bi bi-play-circle me-1"></i>Sedang Berjalan</span>
                                        @elseif($so->status === 'Menunggu Closing')
                                            <span class="badge-closing"><i class="bi bi-flag me-1"></i>Menunggu Closing</span>
                                        @elseif($so->status === 'Closed')
                                            <span class="badge-completed"><i class="bi bi-check-all me-1"></i>Closed</span>
                                        @else
                                            <span class="badge-rejected"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                        @endif
                                    </td>
                                    <td onclick="event.stopPropagation()" class="text-center">
                                        <div class="d-inline-flex gap-1 align-items-center" style="white-space: nowrap;">
                                            @can('lihat-opname')
                                            <button class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="showDetailOpname({{ $so->id }})" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @endcan
                                            
                                            {{-- Draft / Rejected Actions --}}
                                            @if($so->status === 'Draft' || $so->status === 'Rejected')
                                                @can('tambah-opname')
                                                <button type="button" class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="window.editOpname({{ $so->id }})" title="{{ $so->status === 'Rejected' ? 'Perbaiki & Edit' : 'Edit Pengajuan' }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @endcan
                                                @can('hapus-opname')
                                                <button class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="hapusOpname({{ $so->id }}, '{{ $so->nomor_opname }}')" title="Hapus">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                                @endcan
                                            @endif

                                            {{-- Pending Approval Actions --}}
                                            @if($so->status === 'Menunggu Persetujuan')
                                                @can('setuju-opname')
                                                <button class="btn btn-sm btn-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="approveOpname({{ $so->id }}, '{{ $so->nomor_opname }}')" title="Setujui (ACC)">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; padding: 0;" onclick="rejectOpname({{ $so->id }}, '{{ $so->nomor_opname }}')" title="Tolak (Reject)">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                                @endcan
                                            @endif

                                            {{-- Running Execution Actions --}}
                                            @if($so->status === 'Sedang Berjalan')
                                                @canany(['input-fisik-opname', 'tambah-opname'])
                                                <button type="button" class="btn btn-sm btn-info text-dark rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="white-space: nowrap;" onclick="openInputFisikModal({{ $so->id }})" title="Input Stok Fisik">
                                                    <i class="bi bi-pencil-square"></i> Hitung Fisik
                                                </button>
                                                @endcanany

                                                @canany(['posting-opname', 'tambah-opname'])
                                                <button type="button" class="btn btn-sm btn-success text-white rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="white-space: nowrap;" onclick="selesaikanOpname({{ $so->id }}, '{{ $so->nomor_opname }}')" title="Posting & Selesaikan Perhitungan Fisik">
                                                    <i class="bi bi-send-check"></i> Posting
                                                </button>
                                                @endcanany
                                            @endif

                                            {{-- Closing Review Actions --}}
                                            @if($so->status === 'Menunggu Closing')
                                                @can('closing-opname')
                                                <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="white-space: nowrap;" onclick="showDetailOpname({{ $so->id }})" title="Review & Close">
                                                    <i class="bi bi-lock-fill"></i> Closing
                                                </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- INCLUDE MODAL PARTIALS                                       --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @include('modul_inventori.stok_opname._modal_input')
    @include('modul_inventori.stok_opname._modal_edit_fisik')
    @include('modul_inventori.stok_opname._modal_manage_serials')
    @include('modul_inventori.stok_opname._modal_detail')

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    // ── Global Helper Utilities ──────────────────────────────────────────
    window.escapeHtml = function(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
    window.escapeHtmlAdj = window.escapeHtml;

    function formatRupiah(val) {
        let sign = val < 0 ? '-' : '';
        let absVal = Math.abs(val);
        let formatted = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(absVal);
        return sign + 'Rp ' + formatted;
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('[id^="err-"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
    }

    function showFieldError(field, msg) {
        const el = document.getElementById(`err-${field}`);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
        const input = document.querySelector(`[name="${field}"]`);
        if (input) input.classList.add('is-invalid');
    }

    function showOpnameAlert(type, msg) {
        $('#so_alert').html(`<div class="alert alert-${type} py-2 mb-0 small">${msg}</div>`).show();
    }

    let opnameIsReloading = false;

    function reloadTableOpname(showOverlay = false) {
        if (opnameIsReloading) return;
        
        // If background polling and user is actively interacting (focused on search or modal open or tab hidden), skip
        if (!showOverlay) {
            if (document.hidden) return;
            if ($('.modal.show, .modal.is-open').length > 0 || $('.swal2-container').length > 0) return;
            if ($('#tableOpname_filter input').is(':focus')) return;
        }

        let tableCard = $('#opname_card_body').closest('.card');
        let spinner = null;
        if (showOverlay) {
            tableCard.addClass('position-relative');
            spinner = $('<div class="table-loading-overlay d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75" style="z-index: 1000;"><div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            tableCard.append(spinner);
        }

        opnameIsReloading = true;

        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'html',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                let $parsed = $('<div>').append($.parseHTML(html));
                let newBadgeCount = $parsed.find('.badge.bg-primary').text();
                if (newBadgeCount) {
                    $('.badge.bg-primary').text(newBadgeCount);
                }

                // If background polling without overlay, do seamless in-place cell updates
                if (!showOverlay && $('#tableOpname').length && $parsed.find('#tableOpname').length) {
                    let currentRows = $('#tbody_opname_list tr[id^="row-so-"]');
                    let newRows = $parsed.find('#tbody_opname_list tr[id^="row-so-"]');

                    let currentIds = currentRows.map(function() { return this.id; }).get();
                    let newIds = newRows.map(function() { return this.id; }).get();

                    // If same records, update status and action columns in-place without touching DataTable instance
                    if (currentIds.length === newIds.length && currentIds.join(',') === newIds.join(',')) {
                        newRows.each(function() {
                            let rId = this.id;
                            let $curr = $('#' + rId);
                            if ($curr.length) {
                                let newStatusHtml = $(this).children('td').eq(6).html();
                                let currStatusHtml = $curr.children('td').eq(6).html();
                                if (newStatusHtml && currStatusHtml && newStatusHtml.trim() !== currStatusHtml.trim()) {
                                    $curr.children('td').eq(6).html(newStatusHtml);
                                }

                                let newActionHtml = $(this).children('td').eq(7).html();
                                let currActionHtml = $curr.children('td').eq(7).html();
                                if (newActionHtml && currActionHtml && newActionHtml.trim() !== currActionHtml.trim()) {
                                    $curr.children('td').eq(7).html(newActionHtml);
                                }
                            }
                        });
                        return;
                    }
                }

                // Full reload when row count changes or when triggered manually with overlay
                let newBodyHtml = $parsed.find('#opname_card_body').html();
                let currentPage = 0;
                let currentSearch = '';
                let currentOrder = [[2, 'desc']];
                let currentLen = 10;
                if ($.fn.DataTable.isDataTable('#tableOpname')) {
                    let dt = $('#tableOpname').DataTable();
                    currentPage = dt.page();
                    currentSearch = dt.search();
                    currentOrder = dt.order();
                    currentLen = dt.page.len();
                    dt.destroy();
                }
                
                $('#opname_card_body').html(newBodyHtml);

                if ($('#tableOpname').length) {
                    let dt = $('#tableOpname').DataTable({
                        dom: '<"p-3 pb-2 border-bottom"<"row g-2 align-items-center"<"col-12 col-sm-6"l><"col-12 col-sm-6 text-sm-end"f>>>r<"table-responsive"t><"p-3 pt-2 border-top"<"row g-2 align-items-center"<"col-12 col-sm-5 text-center text-sm-start"i><"col-12 col-sm-7 d-flex justify-content-center justify-content-sm-end"p>>>',
                        order: currentOrder && currentOrder.length ? currentOrder : [[2, 'desc']],
                        pageLength: currentLen || 10,
                        lengthMenu: [10, 25, 50, 100],
                        language: {
                            lengthMenu: 'Tampilkan _MENU_ data',
                            info: 'Menampilkan _START_ - _END_ dari _TOTAL_',
                            search: 'Cari:',
                            paginate: { previous: '←', next: '→' },
                        }
                    });

                    if (currentSearch) dt.search(currentSearch);
                    if (currentPage) dt.page(currentPage);
                    dt.draw(false);
                    initTableDragScroll();
                }
            },
            complete: function () {
                if (spinner) spinner.remove();
                opnameIsReloading = false;
            }
        });
    }

    // Background silent real-time updates every 2 seconds
    setInterval(function () {
        if (document.hidden) return;
        if ($('.modal.show, .modal.is-open').length > 0) return;
        if ($('#tableOpname_filter input').is(':focus')) return;

        reloadTableOpname(false);
    }, 2000);

    // ── Table Main Actions (Submit, Approve, Reject, Selesaikan, Hapus) ──
    window.submitOpname = function(id, nomor) {
        Swal.fire({
            title: 'Ajukan Rencana Opname?',
            html: `Dokumen <strong>${nomor}</strong> akan diajukan ke atasan untuk disetujui.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Ajukan!'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/stock-opname/${id}/submit`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    reloadTableOpname();
                    Swal.fire({ icon:'success', title:'Berhasil!', text:res.message, timer:1500, showConfirmButton:false });
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    };

    window.approveOpname = function(id, nomor) {
        Swal.fire({
            title: 'Setujui Rencana Opname?',
            html: `Proposal <strong>${nomor}</strong> akan disetujui (ACC). Tim gudang dapat segera melakukan penghitungan fisik.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Setujui (ACC)!'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/stock-opname/${id}/approve`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    reloadTableOpname();
                    Swal.fire({ icon:'success', title:'Rencana Disetujui!', text:res.message, timer:1500, showConfirmButton:false });
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    }

    function rejectOpname(id, nomor) {
        Swal.fire({
            title: 'Tolak Rencana Opname?',
            html: `Tulis alasan penolakan untuk proposal <strong>${nomor}</strong>:`,
            input: 'text',
            inputAttributes: { required: 'true', maxlength: '200' },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Tolak Proposal!'
        }).then(result => {
            if (!result.isConfirmed || !result.value) return;
            $.ajax({
                url: `/stock-opname/${id}/reject`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { alasan_reject: result.value },
                success: function (res) {
                    reloadTableOpname();
                    Swal.fire({ icon:'success', title:'Ditolak!', text:res.message, timer:1500, showConfirmButton:false });
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    }

    function selesaikanOpname(id, nomor) {
        Swal.fire({
            title: 'Selesaikan Stock Opname?',
            html: `Apakah Anda yakin ingin menyelesaikan perhitungan fisik untuk rencana <strong>${nomor}</strong>?<br><small class="text-danger fw-bold">Setelah diselesaikan, stok fisik tidak dapat diubah lagi dan akan diajukan ke atasan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Selesaikan!'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Memproses...',
                text: 'Harap tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: `/stock-opname/${id}/complete`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    reloadTableOpname();
                    Swal.fire({ icon:'success', title:'Hitung Fisik Selesai!', text:res.message, timer:1500, showConfirmButton:false });
                },
                error: function (xhr) {
                    Swal.fire('Gagal Menyelesaikan', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    }

    function hapusOpname(id, nomor) {
        Swal.fire({
            title: 'Hapus Rencana Opname?',
            html: `Rencana <strong>${nomor}</strong> akan dibatalkan/dihapus selamanya.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus!'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/stock-opname/${id}/delete`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    reloadTableOpname();
                    Swal.fire({ icon:'success', title:'Berhasil!', text:res.message, timer:1500, showConfirmButton:false });
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    }

    function initTableDragScroll() {
        $('.table-responsive').each(function () {
            const slider = this;
            let isDown = false;
            let startX;
            let scrollLeft;

            $(slider).off('mousedown.drag mouseleave.drag mouseup.drag mousemove.drag');

            $(slider).on('mousedown.drag', function (e) {
                if ($(e.target).closest('button, a, input, select, textarea, .btn').length) return;
                isDown = true;
                $(slider).addClass('table-grabbing');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            $(slider).on('mouseleave.drag', function () {
                isDown = false;
                $(slider).removeClass('table-grabbing');
            });

            $(slider).on('mouseup.drag', function () {
                isDown = false;
                $(slider).removeClass('table-grabbing');
            });

            $(slider).on('mousemove.drag', function (e) {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5;
                slider.scrollLeft = scrollLeft - walk;
            });
        });
    }

    $(document).ready(function () {
        @if($opnames->count())
        $('#tableOpname').DataTable({
            dom: '<"p-3 pb-2 border-bottom"<"row g-2 align-items-center"<"col-12 col-sm-6"l><"col-12 col-sm-6 text-sm-end"f>>>r<"table-responsive"t><"p-3 pt-2 border-top"<"row g-2 align-items-center"<"col-12 col-sm-5 text-center text-sm-start"i><"col-12 col-sm-7 d-flex justify-content-center justify-content-sm-end"p>>>',
            order: [[2, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_',
                search: 'Cari:',
                paginate: { previous: '←', next: '→' },
            }
        });
        initTableDragScroll();
        @endif
    });
    </script>
    @endpush
</x-layouts>
