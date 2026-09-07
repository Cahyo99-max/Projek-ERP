<x-layouts>
    <x-slot:title>Pengiriman &amp; Surat Jalan (Delivery Order)</x-slot:title>

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="bi bi-house-door"></i> Dashboard</a></li>
                        <li class="breadcrumb-item text-muted">Penjualan (Marketing)</li>
                        <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Pengiriman &amp; Surat Jalan</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-truck fs-5"></i>
                    </span>
                    Pengiriman &amp; Surat Jalan (Delivery Order)
                </h4>
                <p class="text-muted small mb-0 mt-1">Kelola pengeluaran fisik barang, pelacakan ekspedisi/kurir, dan cetak Surat Jalan resmi.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2 do-btn-primary" id="btn_open_modal_create" data-bs-toggle="modal" data-bs-target="#modalCreateDO">
                    <i class="bi bi-plus-circle-fill fs-6"></i> Buat Surat Jalan Baru
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 filter-custom-card">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-end">
                    
                    <!-- Filter Status -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Status Pengiriman</label>
                        <select class="form-select form-select-sm do-form-control rounded-3" id="filter_status">
                            <option value="">Semua Status</option>
                            <option value="Draft">Draft</option>
                            <option value="Siap Dikirim">Siap Dikirim</option>
                            <option value="Dalam Perjalanan">Dalam Perjalanan (In Transit)</option>
                            <option value="Terkirim">Terkirim (Delivered)</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Filter Gudang Asal -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Gudang Asal</label>
                        <select class="form-select form-select-sm do-form-control rounded-3" id="filter_gudang_id">
                            <option value="">Semua Gudang</option>
                            @foreach($gudangs as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tanggal Kirim -->
                    <div class="col-md-4 col-sm-8">
                        <label class="form-label small fw-semibold text-muted mb-1">Rentang Tanggal Kirim</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control do-form-control rounded-start-3" id="filter_start_date">
                            <span class="input-group-text bg-body-tertiary">s/d</span>
                            <input type="date" class="form-control do-form-control rounded-end-3" id="filter_end_date">
                        </div>
                    </div>

                    <!-- Button Reset Filter -->
                    <div class="col-md-2 col-sm-4 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100 fw-semibold" id="btn_reset_filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden table-custom-card">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i> Daftar Surat Jalan &amp; Pengiriman
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 fw-bold">Live Data</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle w-100" id="tableDeliveryOrders">
                        <thead style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important; color: #ffffff !important;">
                            <tr class="small text-uppercase fw-bold text-white">
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Nomor DO</th>
                                <th>No. Pesanan (SO)</th>
                                <th>Penerima / Instansi</th>
                                <th>Tgl Kirim</th>
                                <th>Metode &amp; Ekspedisi</th>
                                <th>Jumlah Item</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals Include -->
    @include('modul_marketing.pengiriman._modal_create')
    @include('modul_marketing.pengiriman._modal_detail')

    @push('styles')
    <style>
        /* ==========================================================================
           ENTERPRISE STYLING FOR DELIVERY ORDERS (LIGHT & DARK MODE)
           ========================================================================== */

        /* Light Mode Tokens */
        .do-modal-container {
            background-color: #ffffff !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh !important;
            overflow: hidden !important;
        }

        .do-modal-body {
            overflow-y: auto !important;
            max-height: calc(90vh - 130px) !important;
            scrollbar-width: thin;
        }

        .do-modal-header {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .do-modal-footer {
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
        }

        .do-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .do-mini-badge {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .do-card-section {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.2s ease-in-out;
        }

        .do-input-icon {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-right: none !important;
            color: #64748b;
        }

        .do-form-control {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            transition: all 0.2s ease-in-out;
        }

        .input-group .do-form-control {
            border-left: none !important;
        }

        .input-group:focus-within .do-input-icon,
        .input-group:focus-within .do-form-control {
            border-color: #2563eb !important;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
            border-radius: 8px;
        }

        .do-help-text {
            font-size: 0.75rem;
            color: #64748b;
        }

        .do-table-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.4px;
        }

        .do-item-row:hover {
            background-color: rgba(37, 99, 235, 0.04) !important;
        }

        .do-btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            border: none !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .do-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35) !important;
        }

        /* ==========================================================================
           Dark Mode Overrides
           ========================================================================== */
        [data-bs-theme="dark"] .do-modal-container {
            background-color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .do-modal-header {
            background: linear-gradient(180deg, #1e293b 0%, #151f30 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .do-modal-footer {
            background: #151f30 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .do-card-section {
            background-color: #1a2436 !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
        }

        [data-bs-theme="dark"] .do-mini-badge {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        [data-bs-theme="dark"] .do-input-icon {
            background-color: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-right: none !important;
            color: #94a3b8;
        }

        [data-bs-theme="dark"] .do-form-control {
            background-color: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .input-group:focus-within .do-input-icon,
        [data-bs-theme="dark"] .input-group:focus-within .do-form-control {
            border-color: #3b82f6 !important;
        }

        [data-bs-theme="dark"] .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        [data-bs-theme="dark"] .do-help-text {
            color: #94a3b8;
        }

        [data-bs-theme="dark"] .do-item-row:hover {
            background-color: rgba(59, 130, 246, 0.08) !important;
        }

        #tableDeliveryOrders thead th {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border: none;
        }

        #tableDeliveryOrders tbody td {
            padding: 12px 14px;
            vertical-align: middle;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable Delivery Orders
            let tableDO = $('#tableDeliveryOrders').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('marketing.delivery-order.data') }}",
                    data: function (d) {
                        d.status = $('#filter_status').val();
                        d.gudang_id = $('#filter_gudang_id').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small' },
                    { data: 'nomor_do', name: 'nomor_do' },
                    { data: 'nomor_so', name: 'salesOrder.nomor_so' },
                    { data: 'customer_info', name: 'customer.nama_customer' },
                    { data: 'tanggal_kirim', name: 'tanggal_kirim', className: 'font-monospace small' },
                    { data: 'ekspedisi_info', name: 'nama_ekspedisi' },
                    { data: 'items_count', name: 'items_count', orderable: false, searchable: false },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'desc']],
                language: {
                    search: "Cari Surat Jalan:",
                    searchPlaceholder: "Nomor DO, SO, atau Penerima...",
                    lengthMenu: "_MENU_ data per hal",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pengiriman",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    },
                    emptyTable: "Belum ada riwayat pengiriman / surat jalan.",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                }
            });

            // Filter triggers
            $('#filter_status, #filter_gudang_id, #filter_start_date, #filter_end_date').on('change', function() {
                tableDO.ajax.reload();
            });

            $('#btn_reset_filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_gudang_id').val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                tableDO.ajax.reload();
            });

            // Function Load Available SOs
            function loadAvailableSOs() {
                $.get("{{ route('marketing.delivery-order.available-sos') }}", function(res) {
                    if (res.success && res.orders && res.orders.length > 0) {
                        let options = '<option value="">-- Pilih Surat Pesanan (SO) --</option>';
                        res.orders.forEach(o => {
                            let cust = o.customer ? (o.customer.nama_perusahaan ? `${o.customer.nama_perusahaan} (${o.customer.nama_customer})` : o.customer.nama_customer) : (o.nama_customer_walkin || 'Pelanggan Umum');
                            options += `<option value="${o.id}">${o.nomor_so} — ${cust} [${o.status}]</option>`;
                        });
                        $('#do_select_so').html(options);
                    } else {
                        $('#do_select_so').html('<option value="">-- Belum ada Sales Order yang tersedia --</option>');
                    }
                }).fail(function() {
                    $('#do_select_so').html('<option value="">-- Gagal memuat Sales Order --</option>');
                });
            }

            // Handler: Buka Modal Buat DO
            $('#btn_open_modal_create').on('click', function() {
                $('#formCreateDO')[0].reset();
                $('#do_items_tbody').html('<tr><td colspan="5" class="text-center py-4 text-muted small fst-italic">Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.</td></tr>');
                $('#do_items_count_badge').text('0 Item');
                loadAvailableSOs();
            });

            // Trigger load when modal shown
            $('#modalCreateDO').on('show.bs.modal', function() {
                loadAvailableSOs();
            });

            // Event: Pilih Sales Order -> Auto Load Details
            $('#do_select_so').on('change', function() {
                let orderId = $(this).val();
                if (!orderId) {
                    $('#do_nama_penerima').val('');
                    $('#do_telepon_penerima').val('');
                    $('#do_alamat_tujuan').val('');
                    $('#do_items_tbody').html('<tr><td colspan="5" class="text-center py-4 text-muted small fst-italic">Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.</td></tr>');
                    $('#do_items_count_badge').text('0 Item');
                    return;
                }

                $.get(`/marketing/delivery-order/so/${orderId}/details`, function(res) {
                    if (res.success && res.order) {
                        let o = res.order;
                        let cust = o.customer;
                        
                        let namaPenerima = cust ? (cust.nama_perusahaan ? `${cust.nama_perusahaan} (Up: ${cust.nama_customer})` : cust.nama_customer) : (o.nama_customer_walkin || '');
                        $('#do_nama_penerima').val(namaPenerima);
                        
                        let teleponPenerima = cust ? (cust.telepon || cust.hp) : (o.telepon_customer_walkin || '');
                        $('#do_telepon_penerima').val(teleponPenerima);
                        
                        let alamatTujuan = o.alamat_pengiriman || (cust && cust.default_address ? cust.default_address.alamat_lengkap : '');
                        $('#do_alamat_tujuan').val(alamatTujuan);

                        let tbodyHtml = '';
                        (o.details || []).forEach((d, idx) => {
                            let brgNama = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : 'Item');
                            let brgSku  = d.barang ? d.barang.kode_barang : '-';
                            let satuan  = d.nama_satuan || (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'Pcs');
                            let qtyOrder = Number(d.qty);
                            let qtyDelivered = Number(d.qty_delivered || 0);
                            let qtySisa = Number(d.qty_sisa !== undefined ? d.qty_sisa : qtyOrder);
                            let isFulfilled = qtySisa <= 0;

                            tbodyHtml += `
                                <tr class="do-item-row ${isFulfilled ? 'opacity-75 bg-body-tertiary' : ''}">
                                    <td class="text-center font-monospace text-muted small">${idx + 1}</td>
                                    <td>
                                        <div class="fw-bold text-body-emphasis mb-1">${brgNama}</div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded" style="font-size: 0.72rem;">SKU: ${brgSku}</span>
                                            <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle px-2 py-0.5 rounded" style="font-size: 0.72rem;">${satuan}</span>
                                            ${isFulfilled ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.7rem;">Selesai Terkirim</span>' : (qtyDelivered > 0 ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.7rem;">Kirim Sebagian</span>' : '')}
                                        </div>
                                        <input type="hidden" name="items[${idx}][sales_order_detail_id]" value="${d.id}">
                                        <input type="hidden" name="items[${idx}][barang_id]" value="${d.barang_id || ''}">
                                        <input type="hidden" name="items[${idx}][nama_barang_custom]" value="${d.nama_barang_custom || ''}">
                                        <input type="hidden" name="items[${idx}][qty_order]" value="${qtyOrder}">
                                        <input type="hidden" name="items[${idx}][nama_satuan]" value="${satuan}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-body-secondary text-body border px-2 py-1 font-monospace fw-semibold" style="font-size: 0.82rem;">
                                            ${qtyOrder} ${satuan}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge ${qtyDelivered > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-body-secondary text-muted'} px-2 py-1 font-monospace fw-semibold" style="font-size: 0.82rem;">
                                            ${qtyDelivered} ${satuan}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge ${qtySisa > 0 ? 'bg-warning-subtle text-warning border border-warning-subtle fw-bold' : 'bg-success-subtle text-success border border-success-subtle'} px-2 py-1 font-monospace" style="font-size: 0.85rem;">
                                            ${qtySisa} ${satuan}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" min="0" max="${qtySisa}" class="form-control do-form-control text-center font-monospace fw-bold text-primary" name="items[${idx}][qty_kirim]" value="${qtySisa}" ${isFulfilled ? 'disabled readonly' : 'required'}>
                                            <span class="input-group-text do-input-icon font-monospace small fw-semibold">${satuan}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control do-form-control form-control-sm" name="items[${idx}][catatan_item]" placeholder="Kondisi / Koli..." ${isFulfilled ? 'disabled' : ''}>
                                    </td>
                                </tr>
                            `;
                        });

                        $('#do_items_tbody').html(tbodyHtml || '<tr><td colspan="7" class="text-center py-4 text-muted small">Tidak ada item dalam SO ini.</td></tr>');
                        $('#do_items_count_badge').text(`${(o.details || []).length} Item`);
                    }
                });
            });

            // Submit Form Buat DO
            $('#formCreateDO').on('submit', function(e) {
                e.preventDefault();

                let btn = $('#btn_submit_do');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('marketing.delivery-order.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Surat Jalan');
                        if (res.success) {
                            $('#modalCreateDO').modal('hide');
                            tableDO.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-printer me-1"></i> Cetak Surat Jalan',
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#2563eb'
                            }).then((result) => {
                                if (result.isConfirmed && res.do_id) {
                                    window.open(`/marketing/delivery-order/${res.do_id}/print`, '_blank');
                                }
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Surat Jalan');
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    }
                });
            });

            // Action: Ubah Status Cepat dari Tabel
            $(document).on('click', '.btn-change-status', function() {
                let id = $(this).data('id');
                let newStatus = $(this).data('status');

                Swal.fire({
                    title: 'Konfirmasi Pengiriman',
                    text: `Ubah status Surat Jalan menjadi "${newStatus}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/marketing/delivery-order/${id}/status`,
                            type: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: newStatus
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                                    tableDO.ajax.reload(null, false);
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                            }
                        });
                    }
                });
            });

            // Action: Buka Modal Detail DO
            $(document).on('click', '.btn-detail-do', function() {
                let id = $(this).data('id');
                showDetailDO(id);
            });

            // Quick status action from Modal Detail
            $(document).on('click', '.btn-quick-status', function() {
                let id = $(this).data('id');
                let newStatus = $(this).data('status');

                $.ajax({
                    url: `/marketing/delivery-order/${id}/status`,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#modalDetailDO').modal('hide');
                            tableDO.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    }
                });
            });

        });

        // Function Global showDetailDO
        function showDetailDO(doId) {
            $.get(`/marketing/delivery-order/${doId}`, function(res) {
                if (!res.success || !res.do) return;
                let d = res.do;

                let compNama = d.company ? d.company.nama_perusahaan : (d.branch ? d.branch.nama_cabang : 'PT. PERSADA AMAN SENTOSA');
                let compAlmt = d.company && d.company.address ? d.company.address : 'General Supplier, Military, Police & Tactical Equipment';
                let compPhone = d.company ? (d.company.phone || d.company.telepon) : '';
                let compEmail = d.company ? d.company.email : '';
                let compNpwp  = d.company ? d.company.npwp : '';

                let kontakArr = [];
                if (compPhone) kontakArr.push(`Telp: ${compPhone}`);
                if (compEmail) kontakArr.push(`Email: ${compEmail}`);
                if (compNpwp) kontakArr.push(`NPWP: ${compNpwp}`);

                $('#detail_do_company_nama').text(compNama);
                $('#detail_do_company_alamat').text(compAlmt);
                $('#detail_do_company_kontak').text(kontakArr.join(' | '));

                let compLogo = d.company && d.company.logo ? `/storage/${d.company.logo}` : '{{ asset("gambar/logo.png") }}';
                $('#detail_do_company_logo').attr('src', compLogo);

                $('#detail_do_nomor').text(`Nomor DO: ${d.nomor_do}`);
                let tgl = d.tanggal_kirim ? new Date(d.tanggal_kirim).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) : '-';
                $('#detail_do_tanggal').html(`<i class="bi bi-calendar3 me-1"></i> Tgl Kirim: ${tgl}`);
                $('#detail_do_gudang').text(d.gudang ? d.gudang.nama_gudang : '-');

                let statusMap = {
                    'Draft': 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    'Siap Dikirim': 'bg-info-subtle text-info border border-info-subtle',
                    'Dalam Perjalanan': 'bg-warning-subtle text-warning border border-warning-subtle',
                    'Terkirim': 'bg-success-subtle text-success border border-success-subtle',
                    'Dibatalkan': 'bg-danger-subtle text-danger border border-danger-subtle'
                };
                let stCls = statusMap[d.status] || 'bg-secondary-subtle text-secondary';
                $('#detail_do_status_badge').html(`<span class="badge ${stCls} px-3 py-1 rounded-pill fw-semibold font-monospace">${d.status}</span>`);

                $('#detail_do_nomor_so').text(d.sales_order ? d.sales_order.nomor_so : 'Manual / Tanpa SO');
                $('#detail_do_penerima').text(d.nama_penerima || (d.customer ? d.customer.nama_customer : '-'));
                $('#detail_do_telepon').text(d.telepon_penerima || (d.customer ? (d.customer.telepon || d.customer.hp) : '-'));
                $('#detail_do_alamat').text(d.alamat_tujuan || '-');

                $('#detail_do_metode').text(d.metode_pengiriman || 'Kurir Internal');
                $('#detail_do_ekspedisi').text(d.nama_ekspedisi || d.nama_sopir || '-');
                $('#detail_do_resi').text(d.nomor_resi || d.plat_nomor || '-');
                $('#detail_do_catatan').text(d.catatan_pengiriman || '-');

                let creatorName = d.creator ? (d.creator.nama_lengkap || d.creator.username) : 'Staf Logistik';
                $('#detail_do_sig_creator').text(`( ${creatorName} )`);
                $('#detail_do_sig_driver').text(`( ${d.nama_sopir || 'Pengemudi'} )`);
                $('#detail_do_sig_receiver').text(`( ${d.nama_penerima || 'Penerima Barang'} )`);

                let tbodyHtml = '';
                (d.details || []).forEach((item, idx) => {
                    let brgNama = item.nama_barang_custom || (item.barang ? item.barang.nama_barang : 'Item');
                    let brgSku  = item.barang ? item.barang.kode_barang : '-';
                    let satuan  = item.nama_satuan || (item.barang && item.barang.satuan ? item.barang.satuan.nama_satuan : 'Pcs');

                    tbodyHtml += `
                        <tr>
                            <td class="text-center font-monospace text-muted small">${idx + 1}</td>
                            <td>
                                <div class="fw-bold text-body-emphasis">${brgNama}</div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded" style="font-size: 0.75rem;">SKU: ${brgSku}</span>
                            </td>
                            <td class="text-center font-monospace">${Number(item.qty_order)} ${satuan}</td>
                            <td class="text-center font-monospace fw-bold text-primary">${Number(item.qty_kirim)} ${satuan}</td>
                            <td class="small text-muted">${item.catatan_item || '-'}</td>
                        </tr>
                    `;
                });
                $('#detail_do_items_tbody').html(tbodyHtml || '<tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada data item.</td></tr>');

                // Button Print
                $('#btn_print_do_modal').attr('href', `/marketing/delivery-order/${d.id}/print`);

                // Quick status actions
                $('.btn-quick-status').data('id', d.id).addClass('d-none');
                if (d.status === 'Draft') {
                    $('#btn_status_siap').removeClass('d-none');
                } else if (d.status === 'Siap Dikirim') {
                    $('#btn_status_kirim').removeClass('d-none');
                } else if (d.status === 'Dalam Perjalanan') {
                    $('#btn_status_selesai').removeClass('d-none');
                }

                $('#modalDetailDO').modal('show');
            });
        }
    </script>
    @endpush
</x-layouts>
