<x-layouts>
    <x-slot:title>Faktur Pajak Keluaran (e-Faktur PPN)</x-slot:title>

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-file-earmark-check-fill fs-5"></i>
                    </span>
                    Faktur Pajak Keluaran (PPN)
                </h4>
                <p class="text-body-secondary small mb-0 mt-1">Penerbitan faktur pajak resmi DJP, pengelolaan kuota NSFP, faktur gabungan, dan ekspor CSV e-Faktur.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" id="btn_reload_table" title="Segarkan Data Tabel">
                    <i class="bi bi-arrow-clockwise fs-6 text-primary"></i>
                    <span>Segarkan</span>
                </button>

                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" id="btn_open_filter" data-bs-toggle="modal" data-bs-target="#modalFilterFakturPajak">
                    <i class="bi bi-funnel-fill text-success fs-6"></i>
                    <span>Filter Data</span>
                </button>

                @can('export-faktur-pajak')
                <a href="{{ route('modul_marketing.faktur_pajak.export_csv') }}" id="btn_bulk_export_csv" class="btn btn-outline-success rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-filetype-csv fs-6"></i>
                    <span>Export CSV DJP</span>
                </a>
                @endcan

                @can('tambah-faktur-pajak')
                <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btn_open_modal_create" data-bs-toggle="modal" data-bs-target="#modalCreateFakturPajak">
                    <i class="bi bi-plus-circle-fill fs-6"></i> Terbitkan Faktur Pajak
                </button>
                @endcan
            </div>
        </div>

        <!-- KPI Cards Summary Row -->
        <div class="row g-3 mb-4">
            <!-- Total Faktur Pajak -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-semibold font-monospace text-uppercase d-block mb-1">Total Faktur Pajak</span>
                            <h4 class="fw-bold mb-0 text-body-emphasis font-monospace" id="kpi_total_faktur">{{ number_format($totalFaktur, 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-success-subtle text-success fs-4">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total DPP -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-primary small fw-semibold font-monospace text-uppercase d-block mb-1">Total DPP Penyerahan</span>
                            <h4 class="fw-bold mb-0 text-primary font-monospace" id="kpi_total_dpp">Rp {{ number_format($totalDpp, 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-primary-subtle text-primary fs-4">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total PPN Terbit -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-success small fw-semibold font-monospace text-uppercase d-block mb-1">Total PPN Terbit</span>
                            <h4 class="fw-bold mb-0 text-success font-monospace" id="kpi_total_ppn">Rp {{ number_format($totalPpn, 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-success-subtle text-success fs-4">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sisa Kuota NSFP -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-warning small fw-semibold font-monospace text-uppercase d-block mb-1">Sisa Kuota NSFP</span>
                            <h4 class="fw-bold mb-0 text-warning font-monospace" id="kpi_sisa_quota">{{ number_format($sisaQuota, 0, ',', '.') }} <small class="fs-6 text-body-secondary">Nomor</small></h4>
                        </div>
                        <div class="p-3 rounded-4 bg-warning-subtle text-warning fs-4">
                            <i class="bi bi-upc-scan"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border border-body-tertiary shadow-sm rounded-4 overflow-hidden bg-body-tertiary">
            <div class="card-header bg-body-secondary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-success"></i> Daftar Faktur Pajak Keluaran
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle w-100" id="tableFakturPajak">
                        <thead>
                            <tr class="small text-uppercase fw-bold bg-body-secondary text-body-secondary">
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>No. Faktur Pajak (NSFP)</th>
                                <th>Tanggal</th>
                                <th>Masa/Thn</th>
                                <th>Customer / Wajib Pajak</th>
                                <th>Ref Faktur Komersial</th>
                                <th class="text-end">Jumlah DPP</th>
                                <th class="text-end">Jumlah PPN</th>
                                <th class="text-center">Status DJP</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Server-side DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="modalFilterFakturPajak" tabindex="-1" aria-labelledby="modalFilterFakturPajakLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 bg-body">
                <div class="modal-header border-bottom py-3 px-4 bg-body-secondary">
                    <h5 class="modal-title fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2" id="modalFilterFakturPajakLabel">
                        <i class="bi bi-funnel-fill text-success"></i> Filter Faktur Pajak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formFilterFakturPajak">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Customer</label>
                            <select name="filter_customer_id" id="filter_customer_id" class="form-select rounded-3">
                                <option value="">-- Semua Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Masa Pajak</label>
                                <select name="filter_masa_pajak" id="filter_masa_pajak" class="form-select rounded-3">
                                    <option value="">-- Semua Masa --</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">Masa {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Tahun Pajak</label>
                                <input type="number" name="filter_tahun_pajak" id="filter_tahun_pajak" class="form-control rounded-3 font-monospace" placeholder="Contoh: {{ date('Y') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status DJP</label>
                            <select name="filter_status" id="filter_status" class="form-select rounded-3">
                                <option value="">-- Semua Status --</option>
                                <option value="Draft">Draft</option>
                                <option value="Siap Upload">Siap Upload</option>
                                <option value="Approve DJP">Approve DJP</option>
                                <option value="Batal">Batal</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-body-secondary d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="btn_reset_filter">Reset</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" id="btn_apply_filter" data-bs-dismiss="modal">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Include -->
    @include('modul_marketing.faktur_pajak._modal_create')
    @include('modul_marketing.faktur_pajak._modal_detail')

    @push('scripts')
    <script>
        $(document).ready(function () {
            // Setup CSRF header
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Inisialisasi DataTable Server-Side
            let table = $('#tableFakturPajak').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searchDelay: 400,
                ajax: {
                    url: "{{ route('modul_marketing.faktur_pajak.index') }}",
                    data: function (d) {
                        d.customer_id = $('#filter_customer_id').val();
                        d.masa_pajak = $('#filter_masa_pajak').val();
                        d.tahun_pajak = $('#filter_tahun_pajak').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_faktur_pajak', name: 'nomor_faktur_pajak' },
                    { data: 'tanggal_fmt', name: 'tanggal_faktur_pajak' },
                    { data: 'masa_tahun', name: 'masa_pajak' },
                    { data: 'customer_info', name: 'nama_customer' },
                    { data: 'ref_invoices', name: 'referensi', orderable: false },
                    { data: 'jumlah_dpp_fmt', name: 'jumlah_dpp' },
                    { data: 'jumlah_ppn_fmt', name: 'jumlah_ppn' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari NSFP / Customer...",
                    processing: '<div class="spinner-border spinner-border-sm text-success" role="status"></div> Memuat data...',
                    lengthMenu: "Tampil _MENU_ data",
                    zeroRecords: "Tidak ada data faktur pajak yang sesuai.",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ faktur pajak",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&rarr;",
                        previous: "&larr;"
                    }
                }
            });

            // Reload Data Table Button
            $('#btn_reload_table').on('click', function () {
                const icon = $(this).find('i');
                icon.addClass('spin-animation');
                table.ajax.reload(function () {
                    setTimeout(() => icon.removeClass('spin-animation'), 400);
                }, false);
            });

            // Filter Apply & Reset
            $('#btn_apply_filter').on('click', function () {
                table.ajax.reload();
            });

            $('#btn_reset_filter').on('click', function () {
                $('#formFilterFakturPajak')[0].reset();
                table.ajax.reload();
                $('#modalFilterFakturPajak').modal('hide');
            });

            // Helper format rupiah
            function formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // 2. Fetch Auto NSFP
            $('#btn_fetch_nsfp').on('click', function () {
                const $btn = $(this);
                $btn.addClass('text-muted');
                $.get("{{ route('modul_marketing.faktur_pajak.next_nsfp') }}", function (res) {
                    $btn.removeClass('text-muted');
                    if (res.status === 'success') {
                        $('#fp_create_nomor').val(res.nsfp);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'NSFP Dialokasikan: ' + res.nsfp,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire('Info Kuota', res.message || 'Gagal mengambil NSFP.', 'warning');
                    }
                }).fail(function () {
                    $btn.removeClass('text-muted');
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat mengambil kuota NSFP.', 'error');
                });
            });

            // Otomatis minta NSFP saat buka modal create jika masih kosong
            $('#modalCreateFakturPajak').on('shown.bs.modal', function () {
                if (!$('#fp_create_nomor').val()) {
                    $('#btn_fetch_nsfp').trigger('click');
                }
            });

            // 3. Customer Selection -> Load Invoices
            $('#fp_create_customer_id').on('change', function () {
                const custId = $(this).val();
                const npwp = $(this).find('option:selected').data('npwp') || '-';
                $('#fp_cust_tax_info').text('NPWP / NIK: ' + npwp);

                // Reset table items & invoices
                $('#tbody_selectable_invoices').empty();
                $('#tbody_fp_items').html('<tr><td colspan="7" class="text-center text-muted py-3 small">Pilih faktur penjualan di atas untuk memuat item.</td></tr>');
                $('#label_total_dpp').text('Rp 0');
                $('#label_total_ppn').text('Rp 0');
                $('#label_total_keseluruhan').text('Rp 0');

                if (!custId) {
                    $('#fp_invoices_empty').removeClass('d-none').html('<i class="bi bi-info-circle me-1"></i> Silakan pilih customer terlebih dahulu.');
                    $('#fp_invoices_wrapper').addClass('d-none');
                    return;
                }

                $('#fp_invoices_empty').addClass('d-none');
                $('#fp_invoices_loading').removeClass('d-none');
                $('#fp_invoices_wrapper').addClass('d-none');

                $.get("{{ url('modul-marketing/faktur-pajak/unlinked-invoices') }}/" + custId, function (res) {
                    $('#fp_invoices_loading').addClass('d-none');
                    if (res.status === 'success' && res.invoices && res.invoices.length > 0) {
                        $('#fp_invoices_wrapper').removeClass('d-none');
                        let rows = '';
                        res.invoices.forEach(function (inv) {
                            rows += `
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="sales_invoice_ids[]" value="${inv.id}" class="form-check-input invoice-checkbox" data-dpp="${inv.subtotal}" data-ppn="${inv.ppn_nominal}" data-grand="${inv.grand_total}">
                                    </td>
                                    <td class="fw-bold font-monospace text-body-emphasis">${inv.nomor_faktur}</td>
                                    <td>${inv.tanggal_faktur}</td>
                                    <td>${inv.due_date || '-'}</td>
                                    <td class="text-end font-monospace">${formatRupiah(inv.subtotal)}</td>
                                    <td class="text-end font-monospace text-success">${formatRupiah(inv.ppn_nominal)}</td>
                                    <td class="text-end font-monospace fw-bold">${formatRupiah(inv.grand_total)}</td>
                                </tr>
                            `;
                        });
                        $('#tbody_selectable_invoices').html(rows);
                    } else {
                        $('#fp_invoices_empty').removeClass('d-none').html('<i class="bi bi-exclamation-circle me-1"></i> Tidak ada faktur penjualan berstatus Approved/Posted yang belum diterbitkan Faktur Pajak untuk customer ini.');
                    }
                }).fail(function () {
                    $('#fp_invoices_loading').addClass('d-none');
                    $('#fp_invoices_empty').removeClass('d-none').html('<i class="bi bi-x-circle me-1 text-danger"></i> Gagal memuat data faktur penjualan customer.');
                });
            });

            // Check all invoices
            $('#check_all_invoices').on('change', function () {
                const isChecked = $(this).is(':checked');
                $('.invoice-checkbox').prop('checked', isChecked).trigger('change');
            });

            // Checkbox invoice change -> recalculate badge & sync details
            $(document).on('change', '.invoice-checkbox', function () {
                updateFakturGabunganStatus();
                syncItemsFromSelectedInvoices();
            });

            function updateFakturGabunganStatus() {
                const selectedCount = $('.invoice-checkbox:checked').length;
                if (selectedCount > 1) {
                    $('#fp_gabungan_title').text('Tipe Faktur: FAKTUR PAJAK GABUNGAN');
                    $('#fp_gabungan_desc').text(`Menggabungkan ${selectedCount} Faktur Penjualan dalam 1 Faktur Pajak resmi.`);
                    $('#fp_gabungan_badge').removeClass('bg-secondary').addClass('bg-info text-dark').text(`${selectedCount} Faktur Gabungan`);
                } else if (selectedCount === 1) {
                    $('#fp_gabungan_title').text('Tipe Faktur: Tunggal');
                    $('#fp_gabungan_desc').text('Faktur pajak ditautkan ke 1 faktur komersial.');
                    $('#fp_gabungan_badge').removeClass('bg-info text-dark').addClass('bg-secondary').text('1 Faktur');
                } else {
                    $('#fp_gabungan_title').text('Tipe Faktur: Belum Ada Pilihan');
                    $('#fp_gabungan_desc').text('Pilih minimal 1 faktur penjualan.');
                    $('#fp_gabungan_badge').removeClass('bg-info text-dark').addClass('bg-secondary').text('0 Faktur');
                }
            }

            function syncItemsFromSelectedInvoices() {
                const selectedIds = [];
                $('.invoice-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    $('#tbody_fp_items').html('<tr><td colspan="7" class="text-center text-muted py-3 small">Pilih faktur penjualan di atas untuk memuat item.</td></tr>');
                    $('#label_total_dpp').text('Rp 0');
                    $('#label_total_ppn').text('Rp 0');
                    $('#label_total_keseluruhan').text('Rp 0');
                    return;
                }

                $('#tbody_fp_items').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success" role="status"></div> Mengambil rincian item...</td></tr>');

                $.post("{{ route('modul_marketing.faktur_pajak.invoices_details') }}", {
                    invoice_ids: selectedIds
                }, function (res) {
                    if (res.status === 'success' && res.items) {
                        let rows = '';
                        let grandDpp = 0;
                        let grandPpn = 0;

                        res.items.forEach(function (it, idx) {
                            grandDpp += Number(it.dpp || 0);
                            grandPpn += Number(it.ppn || 0);

                            rows += `
                                <tr>
                                    <td class="text-center small">${idx + 1}</td>
                                    <td>
                                        <div class="fw-semibold text-body-emphasis">${it.nama_barang}</div>
                                        <small class="text-muted font-monospace">${it.nomor_faktur || ''}</small>
                                    </td>
                                    <td class="text-center font-monospace">${it.qty}</td>
                                    <td class="text-end font-monospace">${formatRupiah(it.harga_satuan)}</td>
                                    <td class="text-end font-monospace">${formatRupiah(it.diskon)}</td>
                                    <td class="text-end font-monospace">${formatRupiah(it.dpp)}</td>
                                    <td class="text-end font-monospace text-success">${formatRupiah(it.ppn)}</td>
                                </tr>
                            `;
                        });

                        $('#tbody_fp_items').html(rows);
                        $('#label_total_dpp').text(formatRupiah(grandDpp));
                        $('#label_total_ppn').text(formatRupiah(grandPpn));
                        $('#label_total_keseluruhan').text(formatRupiah(grandDpp + grandPpn));
                    }
                }).fail(function () {
                    $('#tbody_fp_items').html('<tr><td colspan="7" class="text-center text-danger py-3 small">Gagal menyinkronkan item faktur.</td></tr>');
                });
            }

            $('#btn_recalculate_details').on('click', function () {
                syncItemsFromSelectedInvoices();
            });

            // 4. Form Submit Create Faktur Pajak
            $('#formCreateFakturPajak').on('submit', function (e) {
                e.preventDefault();

                const selectedCount = $('.invoice-checkbox:checked').length;
                if (selectedCount === 0) {
                    Swal.fire('Validasi Gagal', 'Harap pilih minimal 1 Faktur Penjualan untuk diterbitkan Faktur Pajak.', 'warning');
                    return;
                }

                const $btn = $('#btn_save_faktur_pajak');
                $btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1" role="status"></div> Menyimpan...');

                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $btn.prop('disabled', false).html('<i class="bi bi-save2-fill"></i> Simpan Faktur Pajak');
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Faktur Pajak berhasil diterbitkan.',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                $('#modalCreateFakturPajak').modal('hide');
                                $('#formCreateFakturPajak')[0].reset();
                                table.ajax.reload();
                                location.reload(); // Refresh KPI
                            });
                        } else {
                            Swal.fire('Gagal Menyimpan', res.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html('<i class="bi bi-save2-fill"></i> Simpan Faktur Pajak');
                        let errMsg = 'Terjadi kesalahan validasi atau server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errMsg, 'error');
                    }
                });
            });

            // 5. View Detail Faktur Pajak
            $(document).on('click', '.btn-view-fp', function () {
                const fpId = $(this).data('id');
                if (!fpId) return;

                $('#modalDetailFakturPajak').modal('show');
                $('#fp_detail_loading').removeClass('d-none');
                $('#fp_detail_content').addClass('d-none');

                $.get("{{ url('modul-marketing/faktur-pajak') }}/" + fpId, function (res) {
                    $('#fp_detail_loading').addClass('d-none');
                    if (res.status === 'success' && res.data) {
                        const fp = res.data;
                        $('#fp_detail_content').removeClass('d-none');

                        $('#detail_nama_customer').text(fp.nama_customer);
                        $('#detail_npwp_customer').text(fp.npwp_customer || (fp.nik_customer ? 'NIK: ' + fp.nik_customer : 'Non-NPWP'));
                        $('#detail_alamat_customer').text(fp.alamat_customer || '-');

                        $('#detail_nomor_fp').text(fp.nomor_faktur_pajak);
                        $('#detail_tanggal_fp').text(fp.tanggal_faktur_pajak ? fp.tanggal_faktur_pajak.substring(0, 10) : '-');
                        $('#detail_masa_tahun').text(String(fp.masa_pajak).padStart(2, '0') + ' / ' + fp.tahun_pajak);
                        
                        $('#detail_status_badge').text(fp.status).attr('class', 'badge ' + (
                            fp.status === 'Approve DJP' ? 'bg-success' : (fp.status === 'Batal' ? 'bg-danger' : 'bg-warning text-dark')
                        ));

                        $('#detail_gabungan_badge').text(fp.is_gabungan ? 'Faktur Gabungan' : 'Faktur Tunggal');

                        // Linked invoices
                        let invBadges = '';
                        if (fp.sales_invoices && fp.sales_invoices.length > 0) {
                            fp.sales_invoices.forEach(function (inv) {
                                invBadges += `<span class="badge bg-secondary border p-2 font-monospace"><i class="bi bi-receipt me-1"></i>${inv.nomor_faktur} (Rp ${Number(inv.grand_total || 0).toLocaleString('id-ID')})</span>`;
                            });
                        } else {
                            invBadges = '<span class="text-muted small">Tidak ada invoice tertaut.</span>';
                        }
                        $('#detail_linked_invoices_container').html(invBadges);

                        // Items detail
                        let itemRows = '';
                        if (fp.details && fp.details.length > 0) {
                            fp.details.forEach(function (d, i) {
                                itemRows += `
                                    <tr>
                                        <td class="text-center small">${i + 1}</td>
                                        <td class="fw-semibold text-body-emphasis">${d.nama_barang}</td>
                                        <td class="text-center font-monospace">${d.qty}</td>
                                        <td class="text-end font-monospace">${formatRupiah(d.harga_satuan)}</td>
                                        <td class="text-end font-monospace">${formatRupiah(d.diskon)}</td>
                                        <td class="text-end font-monospace">${formatRupiah(d.dpp)}</td>
                                        <td class="text-end font-monospace text-success">${formatRupiah(d.ppn)}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#detail_tbody_items').html(itemRows);

                        $('#detail_total_dpp').text(formatRupiah(fp.jumlah_dpp));
                        $('#detail_total_ppn').text(formatRupiah(fp.jumlah_ppn));
                        $('#detail_grand_total').text(formatRupiah(Number(fp.jumlah_dpp) + Number(fp.jumlah_ppn)));

                        // Export single CSV link
                        $('#btn_detail_export_csv').attr('href', "{{ url('modul-marketing/faktur-pajak/export-csv') }}?id=" + fp.id);
                    }
                }).fail(function () {
                    $('#fp_detail_loading').addClass('d-none');
                    Swal.fire('Error', 'Gagal mengambil detail faktur pajak.', 'error');
                });
            });

            // 6. Update Status DJP
            $(document).on('click', '.btn-status-fp', function () {
                const fpId = $(this).data('id');
                const currentStatus = $(this).data('status');

                Swal.fire({
                    title: 'Update Status DJP',
                    text: 'Pilih status baru untuk Faktur Pajak ini:',
                    input: 'select',
                    inputOptions: {
                        'Draft': 'Draft',
                        'Siap Upload': 'Siap Upload',
                        'Approve DJP': 'Approve DJP',
                        'Batal': 'Batal'
                    },
                    inputValue: currentStatus,
                    showCancelButton: true,
                    confirmButtonText: 'Update Status',
                    confirmButtonColor: '#059669',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        $.post("{{ url('modul-marketing/faktur-pajak') }}/" + fpId + "/update-status", {
                            status: result.value
                        }, function (res) {
                            if (res.status === 'success') {
                                Swal.fire('Berhasil', res.message, 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal', res.message || 'Tidak dapat memperbarui status.', 'error');
                            }
                        }).fail(function () {
                            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                        });
                    }
                });
            });

            // 7. Hapus / Batalkan Faktur Pajak
            $(document).on('click', '.btn-delete-fp', function () {
                const fpId = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Hapus Faktur Pajak?',
                    text: `Apakah Anda yakin ingin menghapus/membatalkan Faktur Pajak "${nomor}"? Faktur Penjualan tertaut akan dibebaskan kembali.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-pajak') }}/" + fpId,
                            method: 'DELETE',
                            success: function (res) {
                                if (res.status === 'success') {
                                    Swal.fire('Terhapus!', res.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Gagal', res.message || 'Tidak dapat menghapus faktur.', 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus faktur pajak.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .spin-animation {
            display: inline-block;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @endpush
</x-layouts>
