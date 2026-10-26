
<div class="row">
    <div class="col-12">
        <!-- MAIN TABLE CARD -->
        <x-table_card tableId="tableGR" title="Penerimaan Barang (Goods Receipt)" icon="bi-box-seam"
            btnTarget="{{ auth()->user()->can('tambah-gr') ? '#modalTambahGR' : '' }}" btnText="Barang Masuk">
            <x-slot name="thead">
                <tr>
                    <th width="5%">No</th>
                    <th width="18%">Nomor Penerimaan</th>
                    <th width="10%">Tanggal</th>
                    <th width="14%">No. PO Asal</th>
                    <th width="22%">Supplier</th>
                    <th width="14%">Gudang Penerima</th>
                    <th width="10%">Surat Jalan</th>
                    <th width="9%" class="text-center">Status</th>
                    <th width="8%" class="text-center">Aksi</th>
                </tr>
            </x-slot>
                @php
                    $isAuthorizedAdmin = auth()->check() && (
                        auth()->user()->hasRole('Super Admin') || 
                        auth()->user()->hasRole('Superadmin') || 
                        auth()->user()->hasRole('superadmin') ||
                        auth()->user()->hasRole('Admin') || 
                        auth()->user()->hasRole('admin') || 
                        (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
                    );
                @endphp
                @foreach($receipts as $index => $gr)
                <tr>
                    <td data-label="No">{{ $index + 1 }}</td>
                    <td data-label="Nomor Penerimaan">
                        <span class="badge bg-light text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold shadow-sm">
                            {{ $gr->nomor_gr }}
                        </span>
                    </td>
                    <td data-label="Tanggal">{{ $gr->tanggal_gr ? $gr->tanggal_gr->format('d/m/Y') : '-' }}</td>
                    <td data-label="No. PO Asal">
                        <span class="badge bg-light text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 fw-semibold shadow-sm">
                            {{ $gr->po ? $gr->po->nomor_po : '-' }}
                        </span>
                        @php
                            $tender = $gr->tender ?: ($gr->po ? $gr->po->tender : null);
                        @endphp
                        @if($tender)
                            <div class="mt-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.7rem;" title="Tender: {{ $tender->nama_tender }}">
                                    <i class="bi bi-briefcase me-1"></i>{{ $tender->nomor_tender }}
                                </span>
                            </div>
                        @endif
                    </td>
                    <td data-label="Supplier">
                        <span class="fw-bold text-dark">{{ $gr->po && $gr->po->supplier ? $gr->po->supplier->nama_supplier : '-' }}</span>
                    </td>
                    <td data-label="Gudang Penerima">{{ $gr->gudang ? $gr->gudang->nama_gudang : '-' }}</td>
                    <td data-label="Surat Jalan">{{ $gr->nomor_surat_jalan ?? '-' }}</td>
                    <td data-label="Status" class="text-center">
                        @if($gr->status === 'Draft')
                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-2.5 py-1.5 fw-bold shadow-sm">
                                <i class="bi bi-pencil-square me-1"></i>Draft
                            </span>
                        @elseif($gr->status === 'Dibatalkan')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold shadow-sm" data-bs-toggle="tooltip" title="{{ $gr->alasan_pembatalan ? 'Alasan: ' . $gr->alasan_pembatalan : 'Transaksi Dibatalkan' }}">
                                <i class="bi bi-x-circle me-1"></i>Dibatalkan
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-1"></i>Diposting
                            </span>
                        @endif
                    </td>
                    <td data-label="Aksi" class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            @if($gr->status === 'Draft')
                            @can('tambah-gr')
                            <button type="button" class="btn btn-sm btn-outline-warning border-0 btn-edit-gr" data-id="{{ $gr->id }}" data-bs-toggle="tooltip" title="Edit Draft">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            @endcan
                            @endif

                            @if($gr->status === 'Draft')
                            <button type="button" class="btn btn-sm btn-outline-success border-0 btn-post-gr" data-id="{{ $gr->id }}" data-nama="{{ $gr->nomor_gr }}" data-bs-toggle="tooltip" title="Posting Penerimaan">
                                <i class="bi bi-send-check"></i>
                            </button>
                            @endif

                            @can('lihat-gr')
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-view-gr" data-id="{{ $gr->id }}" data-bs-toggle="tooltip" title="Lihat Rincian">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="/goods-receipt/{{ $gr->id }}/print" target="_blank" class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="tooltip" title="Cetak Surat Jalan Masuk">
                                <i class="bi bi-printer"></i>
                            </a>
                            @endcan

                            @can('hapus-gr')
                            @if($gr->status === 'Draft')
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-gr" data-id="{{ $gr->id }}" data-nama="{{ $gr->nomor_gr }}" data-bs-toggle="tooltip" title="Hapus Draft">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
        </x-table_card>
    </div>
</div>

@push('scripts')
<script>
    // ===== DATATABLE INIT =====
    $('#tableGR').DataTable({
        order: [],
        dom: '<"row align-items-center mb-2"<"col-12 col-sm-4 mb-2 mb-sm-0"l><"col-12 col-sm-8 d-flex justify-content-sm-end"f>>rt<"row align-items-center mt-2"<"col-12 col-sm-5 mb-2 mb-sm-0 text-sm-start"i><"col-12 col-sm-7 d-flex justify-content-sm-end"p>>',
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            search: '',
            searchPlaceholder: '🔍 Cari data...',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div class="text-center py-3">Belum ada data Penerimaan Barang.</div>'
        },
        responsive: true
    });

    // ===== RELOAD TABLE GR (AJAX) =====
    function reloadTableGR() {
        let tableCard = $('#tableGR').closest('.card');
        tableCard.addClass('position-relative');
        let spinner = $('<div class="table-loading-overlay d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75" style="z-index: 1000;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        tableCard.append(spinner);

        $.get(window.location.href, function (html) {
            if ($.fn.DataTable.isDataTable('#tableGR')) {
                $('#tableGR').DataTable().destroy();
            }

            let newTableHtml = $(html).find('#tableGR').html();
            $('#tableGR').html(newTableHtml);

            $('#tableGR').DataTable({
                order: [],
                dom: '<"row align-items-center mb-2"<"col-12 col-sm-4 mb-2 mb-sm-0"l><"col-12 col-sm-8 d-flex justify-content-sm-end"f>>rt<"row align-items-center mt-2"<"col-12 col-sm-5 mb-2 mb-sm-0 text-sm-start"i><"col-12 col-sm-7 d-flex justify-content-sm-end"p>>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    search: '',
                    searchPlaceholder: '🔍 Cari data...',
                    paginate: { previous: '‹', next: '›' },
                    emptyTable: '<div class="text-center py-3">Belum ada data Penerimaan Barang.</div>'
                },
                responsive: true
            });

            spinner.remove();
        });
    }

    // Expose globally so other partials can call reloadTableGR()
    window.reloadTableGR = reloadTableGR;

    $(document).ready(function () {

        // ===== POST DRAFT GR =====
        $(document).on('click', '.btn-post-gr', function () {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'Posting Penerimaan Barang?',
                html: `Apakah Anda yakin ingin memposting transaksi: <b>${nama}</b>?<br><small class="text-muted">Stok gudang akan bertambah dan status PO akan diperbarui.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Posting Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Posting...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post("/goods-receipt/" + id + "/post", function (res) {
                        Swal.fire({
                            title: 'Berhasil Diposting!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            reloadTableGR();
                        });
                    }).fail(function (xhr) {
                        let msg = 'Terjadi kesalahan saat memposting penerimaan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', msg, 'error');
                    });
                }
            });
        });


        // ===== DELETE GR =====
        $(document).on('click', '.btn-delete-gr', function () {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Penerimaan Barang?',
                html: `Anda akan menghapus penerimaan: <b>${nama}</b>.<br><small class="text-muted">Aksi ini akan menghapus dokumen penerimaan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/goods-receipt/" + id + "/delete",
                        type: 'DELETE',
                        success: function (res) {
                            Swal.fire({
                                title: 'Terhapus!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                reloadTableGR();
                            });
                        },
                        error: function (xhr) {
                            let msg = 'Terjadi kesalahan saat menghapus penerimaan.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                msg = 'Akun Anda tidak memiliki izin untuk menghapus Penerimaan Barang.';
                            }
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush
