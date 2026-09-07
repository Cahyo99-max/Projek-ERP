<x-layouts title="Master Tahapan Produksi - Konfigurasi Alur Kerja Garmen">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<style>
    .img-thumbnail-tahapan {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .img-thumbnail-tahapan:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35) !important;
    }
    .tahapan-upload-box {
        border: 2px dashed rgba(59, 130, 246, 0.4);
        background: rgba(59, 130, 246, 0.04);
        border-radius: 14px;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .tahapan-upload-box:hover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.08);
    }

    /* ── SMOOTH HORIZONTAL SCROLL FOR TABLE ── */
    .spk-table-scroll-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(37, 99, 235, 0.4) transparent;
        border: 1px solid var(--spk-border-color, rgba(255,255,255,0.1));
        border-radius: 0.75rem;
    }
    .spk-table-scroll-container::-webkit-scrollbar {
        height: 6px;
    }
    .spk-table-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(37, 99, 235, 0.4);
        border-radius: 4px;
    }
    .spk-table-scroll-container::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Controls styling */
    @media (max-width: 767.98px) {
        .spk-hero-actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }
        .spk-hero-actions > * {
            flex: 1;
            text-align: center;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 0.5rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            display: block;
            margin-top: 0.25rem;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: center !important;
            margin-top: 0.75rem;
        }
        .dataTables_wrapper .dataTables_paginate ul.pagination {
            justify-content: center !important;
        }
    }
</style>
@endpush
<div class="container-fluid px-2 px-sm-4 py-2 py-sm-3">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 mb-sm-4 px-1 px-sm-0">
        <div>
            <p class="spk-text-muted small mb-0">
                <a href="{{ url('/dashbord') }}" class="text-decoration-none spk-text-muted"><i class="bi bi-house-door me-1"></i>Dashboard</a>
                <i class="bi bi-chevron-right mx-1" style="font-size:10px;"></i>
                <span>Produksi</span>
                <i class="bi bi-chevron-right mx-1" style="font-size:10px;"></i>
                <span class="fw-semibold text-primary">Master Tahapan</span>
            </p>
            <h5 class="fw-bold spk-text-main mb-0 mt-1">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Master Tahapan & Alur Kerja Produksi
            </h5>
        </div>
        <div class="d-flex align-items-center gap-2 spk-hero-actions">
            <a href="{{ route('produksi.spk.index') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold" style="font-size:13px;">
                <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Kembali ke SPK</span><span class="d-inline d-sm-none">SPK</span>
            </a>
            @if(auth()->user()->can('tambah-master-tahapan') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="bukaTambahTahapan()" style="font-size:13px;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tahapan
                </button>
            @endif
        </div>
    </div>

    {{-- ── FILTER CARD (BOM SELECTION) ────────────────────────── --}}
    <div class="spk-card border-0 rounded-4 shadow-sm mb-3 mb-sm-4 p-3 p-sm-3.5">
        <div class="row g-2.5 align-items-center">
            <div class="col-12 col-md-5 col-lg-4">
                <label class="form-label small fw-semibold spk-text-muted mb-1">
                    <i class="bi bi-funnel-fill text-primary me-1"></i> Filter Berdasarkan Resep BOM:
                </label>
                <select id="filterBomTahapan" class="form-select spk-input rounded-3 shadow-xs">
                    <option value="">-- Semua Tahapan (Semua BOM & Template Umum) --</option>
                    <option value="general">Template Umum Saja (Tanpa Spesifik BOM)</option>
                    @if(isset($boms) && $boms->count() > 0)
                        <optgroup label="Spesifik Resep BOM:">
                            @foreach($boms as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_bom }} ({{ $b->kode_bom }})</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
            <div class="col-12 col-md-7 col-lg-8 text-md-end mt-2 mt-md-4">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 small">
                    <i class="bi bi-info-circle me-1"></i>Tahapan dapat dikhususkan untuk resep BOM tertentu atau dijadikan template umum SPK.
                </span>
            </div>
        </div>
    </div>

    {{-- ── TABLE CARD ──────────────────────────────────────── --}}
    <div class="spk-card border-0 rounded-4 shadow-sm">
        <div class="card-body p-2 p-sm-4">
            
            {{-- Petunjuk Geser di Layar HP --}}
            <div class="d-flex d-md-none align-items-center justify-content-between mb-2 px-1 text-white-50 small" style="font-size: 11px;">
                <span><i class="bi bi-arrow-left-right text-primary me-1"></i>Geser tabel ke samping untuk melihat seluruh data</span>
            </div>

            {{-- TABLE WITH HORIZONTAL SCROLL --}}
            <div class="table-responsive spk-table-scroll-container">
                <table class="table table-hover align-middle mb-0 w-100 spk-table text-nowrap" id="tabelMasterTahapan" style="min-width: 820px;">
                    <thead>
                        <tr class="small text-uppercase fw-bold">
                            <th style="width: 45px;" class="text-center">No</th>
                            <th style="min-width: 250px;">Resep BOM / Target Alur</th>
                            <th style="min-width: 340px;">Alur & Cuplikan Tahapan Proses</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th style="width: 150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO LIGHTBOX --}}
<div class="modal fade" id="modalPreviewFotoTahapan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-0 p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-image text-primary fs-4"></i>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="previewFotoTahapanTitle">Foto SOP / Contoh Hasil</h6>
                        <small class="text-white-50">Pratinjau visual instruksi pengerjaan tahapan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 p-md-4 text-center">
                <img id="previewFotoTahapanImg" src="" alt="Foto Tahapan" class="img-fluid rounded-3 shadow-lg" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
@include('modul_produksi.master_tahapan._modal_input')
@include('modul_produksi.master_tahapan._modal_edit')
@include('modul_produksi.master_tahapan._modal_detail')

@push('scripts')
<script>
    let dtTahapan = null;

    $(document).ready(function() {
        dtTahapan = $('#tabelMasterTahapan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("produksi.master-tahapan.index") }}',
                data: function(d) {
                    d.filter_bom_id = $('#filterBomTahapan').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center spk-text-muted' },
                { data: 'bom_info' },
                { data: 'preview_alur' },
                { data: 'status', className: 'text-center', orderable: false },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                search: 'Cari BOM / Alur:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Alur tahapan BOM tidak ditemukan',
                emptyTable: '<div class="text-center py-4 spk-text-muted"><i class="bi bi-diagram-3 fs-2 d-block mb-2 text-secondary"></i>Belum ada alur tahapan. Klik "+ Tambah Tahapan" untuk mulai.</div>',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                processing: '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span>Memuat...</span></div>'
            }
        });

        $('#filterBomTahapan').on('change', function() {
            reloadTabelTahapan();
        });
    });

    function reloadTabelTahapan() {
        if (dtTahapan) dtTahapan.ajax.reload();
    }

    function previewFotoTahapan(url, title) {
        $('#previewFotoTahapanImg').attr('src', url);
        $('#previewFotoTahapanTitle').text(title || 'Foto SOP Tahapan Produksi');
        $('#modalPreviewFotoTahapan').modal('show');
    }

    function bukaTambahTahapan() {
        const activeFilter = $('#filterBomTahapan').val();
        $('#modalTambahTahapan').modal('show');
        if (activeFilter && activeFilter !== 'general') {
            $('#inp_tah_bom_id').val(activeFilter);
        }
    }

    function hapusAlurBom(bomKey, bomNama) {
        Swal.fire({
            title: 'Hapus Alur Tahapan?',
            html: `Yakin ingin menghapus seluruh tahapan kerja untuk <strong>"${bomNama}"</strong>?<br><small class='text-danger'>Seluruh langkah alur kerja dan foto SOP terkait akan langsung dihapus permanen dari sistem.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Batal',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Semua Alur'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/master-tahapan/bom/${bomKey}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1800, showConfirmButton: false })
                            .then(() => reloadTabelTahapan());
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
</x-layouts>
