<x-layouts title="Penyesuaian Stok (Stock Adjustment)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/stok_adjustment.css') }}">
    <link rel="stylesheet" href="{{ asset('inventori/barang_keluar.css') }}">

    <div class="container-fluid px-4 py-3">
        
        {{-- HEADER TITLE & ACTION BUTTON --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-extrabold page-title mb-1 tracking-tight">
                    <i class="bi bi-sliders text-primary me-2"></i>Penyesuaian Stok (Stock Adjustment)
                </h4>
                <p class="text-muted small mb-0">
                    Koreksi stok persediaan gudang akibat barang rusak, hilang, sampel promosi, dan penggunaan internal.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden border bg-body" style="width: 175px;">
                    <span class="input-group-text bg-transparent border-0 px-2.5 text-primary fw-semibold"><i class="bi bi-calendar-month"></i></span>
                    <input type="month" id="filter_bulan" class="form-control bg-transparent border-0 py-1.5 pe-3 fw-semibold text-body shadow-none" value="{{ date('Y-m') }}" title="Filter Periode Bulan">
                </div>
                <button type="button" id="btnOpenFilter" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalFilterAdjustment">
                    <i class="bi bi-funnel-fill text-primary"></i> Filter Data <span id="badgeFilterActive" class="badge bg-primary rounded-pill d-none">0</span>
                </button>
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-stok-adjustment'))
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalTambahAdjustment" onclick="bukaModalTambahAdjustment()">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Adjustment
                </button>
                @endif
            </div>
        </div>

        {{-- SUMMARY KPI CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div>
                            <div class="kpi-title">TOTAL DOKUMEN</div>
                            <div class="kpi-value" id="kpiTotalDoc">{{ number_format($kpiData['total_doc'], 0, ',', '.') }}</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-file-earmark-check"></i> Dokumen Bulan Ini
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div>
                            <div class="kpi-title">TOTAL PENGURANGAN</div>
                            <div class="kpi-value" id="kpiTotalPengurangan">{{ number_format($kpiData['total_pengurangan'], 0, ',', '.') }} Qty</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-arrow-down-circle"></i> Keluar Bulan Ini
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-box-arrow-down"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #047857 0%, #10b981 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div>
                            <div class="kpi-title">TOTAL PENAMBAHAN</div>
                            <div class="kpi-value" id="kpiTotalPenambahan">{{ number_format($kpiData['total_penambahan'], 0, ',', '.') }} Qty</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-arrow-up-circle"></i> Masuk Bulan Ini
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-box-arrow-in-up"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div>
                            <div class="kpi-title">TOTAL NILAI ADJUSTMENT</div>
                            <div class="kpi-value" id="kpiTotalNilai">
                                @if($kpiData['can_see_hpp'])
                                    Rp {{ number_format($kpiData['total_nilai'], 2, ',', '.') }}
                                @else
                                    Rp ***
                                @endif
                            </div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-cash-stack"></i> Nilai Bulan Ini
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tableAdjustment" class="table table-hover table-modern align-middle mb-0 w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">NO</th>
                                <th style="min-width: 160px;">NO. ADJUSTMENT</th>
                                <th style="min-width: 100px;">TANGGAL</th>
                                <th class="text-center" style="min-width: 120px;">TIPE</th>
                                <th class="text-end" style="min-width: 100px;">TOTAL QTY</th>
                                <th class="text-end" style="min-width: 140px;">TOTAL NILAI</th>
                                <th class="text-center" style="min-width: 130px;">STATUS</th>
                                <th class="text-center" style="width: 85px; min-width: 85px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- MODALS INCLUDE --}}
    @include('modul_inventori.stok_ajusmen._modal_filter')
    @include('modul_inventori.stok_ajusmen._modal_input')
    @include('modul_inventori.stok_ajusmen._modal_edit')
    @include('modul_inventori.stok_ajusmen._modal_detail')
    @include('modul_inventori.stok_ajusmen._modal_lookup_barang')
    @include('modul_inventori.stok_ajusmen._modal_preview_foto')
    @include('modul_inventori.stok_ajusmen._modal_manage_serials')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let tableAdjustment;

        $(document).ready(function() {
            tableAdjustment = $('#tableAdjustment').DataTable({
                processing: false,
                serverSide: true,
                responsive: false,
                ajax: {
                    url: '{{ route("stock-adjustment.index") }}',
                    data: function(d) {
                        d.filter_bulan    = $('#filter_bulan').val();
                        d.gudang_id       = $('#filter_gudang_id').val();
                        d.tipe_adjustment = $('#filter_tipe').val();
                        d.kategori_alasan = $('#filter_kategori').val();
                        d.status          = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted small fw-semibold' },
                    { data: 'nomor_adj_link', name: 'nomor_adjustment' },
                    { data: 'tanggal_fmt', name: 'tanggal_adjustment' },
                    { data: 'tipe_badge', name: 'tipe_adjustment', className: 'text-center' },
                    { data: 'total_qty_fmt', name: 'total_qty', className: 'text-end' },
                    { data: 'total_nilai_fmt', name: 'total_nilai', className: 'text-end' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan <span class="fw-bold text-body font-monospace">_START_ - _END_</span> dari <span class="fw-bold text-primary font-monospace">_TOTAL_</span> item',
                    search: 'Cari:',
                    paginate: { 
                        previous: '<i class="bi bi-chevron-left"></i>', 
                        next: '<i class="bi bi-chevron-right"></i>' 
                    },
                    emptyTable: 'Belum ada data penyesuaian stok pada periode ini.'
                }
            });

            // Auto Update KPI Cards dynamically on every DataTables AJAX response
            tableAdjustment.on('xhr', function() {
                const json = tableAdjustment.ajax.json();
                if (json && json.kpi) {
                    $('#kpiTotalDoc').text(new Intl.NumberFormat('id-ID').format(json.kpi.total_doc));
                    $('#kpiTotalPengurangan').text(new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(json.kpi.total_pengurangan) + ' Qty');
                    $('#kpiTotalPenambahan').text(new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(json.kpi.total_penambahan) + ' Qty');
                    if (json.kpi.can_see_hpp) {
                        $('#kpiTotalNilai').text('Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(json.kpi.total_nilai));
                    }
                }
            });

            $('#filter_bulan, #filter_gudang_id, #filter_tipe, #filter_kategori, #filter_status').on('change', function() {
                tableAdjustment.ajax.reload();
            });

            // REAL-TIME AUTO REFRESH: Polling background setiap 4 detik (tanpa mereset pagination/posisi halaman)
            setInterval(function() {
                if (document.visibilityState === 'visible' && !$('.modal.show').length) {
                    tableAdjustment.ajax.reload(null, false);
                }
            }, 4000);
        });

        function reloadTableAdjustment() {
            if (tableAdjustment) {
                tableAdjustment.ajax.reload(null, false);
            }
        }

        // Action: Setujui & Konfirmasi Adjustment (Real-time AJAX)
        function confirmAdjustment(id, nomor) {
            Swal.fire({
                title: 'Setujui & Konfirmasi Stok?',
                text: `Dokumen ${nomor} akan disetujui, stok gudang akan diperbarui, dan jurnal akuntansi otomatis akan dibuat.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Setujui Now!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof window.showLoader === 'function') window.showLoader();
                    $.ajax({
                        url: `/stock-adjustment/${id}/confirm`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (typeof window.hideLoader === 'function') window.hideLoader();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            reloadTableAdjustment();
                        },
                        error: function(xhr) {
                            if (typeof window.hideLoader === 'function') window.hideLoader();
                            const res = xhr.responseJSON ?? {};
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat mengkonfirmasi dokumen.', 'error');
                        }
                    });
                }
            });
        }

        // Action: Hapus Adjustment (Real-time AJAX)
        function hapusAdjustment(id, nomor) {
            Swal.fire({
                title: 'Hapus Dokumen Adjustment?',
                text: `Dokumen ${nomor} akan dihapus secara permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof window.showLoader === 'function') window.showLoader();
                    $.ajax({
                        url: `/stock-adjustment/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (typeof window.hideLoader === 'function') window.hideLoader();
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            reloadTableAdjustment();
                        },
                        error: function(xhr) {
                            if (typeof window.hideLoader === 'function') window.hideLoader();
                            const res = xhr.responseJSON ?? {};
                            Swal.fire('Gagal', res.message || 'Gagal menghapus dokumen.', 'error');
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</x-layouts>
