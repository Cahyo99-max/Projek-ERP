<x-layouts title="Pelacakan & Registrasi Jasa Garmen">

    @push('styles')
    <style>
        .metric-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.08);
            border-color: rgba(59, 130, 246, 0.3);
        }
        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.4rem;
        }
        .table-modern th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 16px !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        .table-modern td {
            padding: 12px 16px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
    </style>
    @endpush

    <!-- METRICS SECTION -->
    <div class="row g-4 mb-4">
        <!-- Metric 1: Total Pengeluaran Jasa -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Pengeluaran Jasa</span>
                    <h3 class="fw-extrabold text-dark mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                </div>
                <div class="metric-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>

        <!-- Metric 2: Langganan Aktif -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Jasa / Langganan Aktif</span>
                    <h3 class="fw-extrabold text-success mb-0">{{ $langgananAktif }} Transaksi</h3>
                </div>
                <div class="metric-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>

        <!-- Metric 3: Jasa Selesai -->
        <div class="col-12 col-md-4">
            <div class="metric-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Pengerjaan Selesai</span>
                    <h3 class="fw-extrabold text-info mb-0">{{ $jasaSelesai }} Selesai</h3>
                </div>
                <div class="metric-icon-wrapper bg-info bg-opacity-10 text-info">
                    <i class="bi bi-flag-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TABLE SECTION -->
    <div class="row">
        <div class="col-12">
            <x-table_card tableId="tableJasa" title="Register & Transaksi Jasa" icon="bi-gear-wide-connected"
                btnTarget="{{ auth()->user()->can('tambah-barang') ? '#modalTambahJasa' : '' }}"
                btnText="Registrasi Jasa">
                
                <x-slot name="thead">
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Jasa</th>
                        <th width="15%">No Invoice</th>
                        <th width="20%">Supplier / Vendor</th>
                        <th width="15%">Harga Jasa</th>
                        <th width="18%">Periode Aktif</th>
                        <th width="15%">Penanggung Jawab</th>
                        <th width="10%">Status</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </x-slot>

                @foreach($jasas as $index => $jasa)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $jasa->barang->nama_barang }}</div>
                        <small class="text-muted">Kode: {{ $jasa->barang->kode_barang }}</small>
                    </td>
                    <td>
                        @if($jasa->no_invoice)
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-bold">{{ $jasa->no_invoice }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="fw-medium">
                        {{ $jasa->supplier->nama_supplier ?? 'Tanpa Vendor' }}
                    </td>
                    <td class="fw-bold text-dark">
                        Rp {{ number_format($jasa->harga_transaksi, 0, ',', '.') }}
                    </td>
                    <td>
                        <div class="small">Mulai: <span class="fw-semibold text-dark">{{ $jasa->tanggal_mulai->format('d/m/Y') }}</span></div>
                        @if($jasa->tanggal_selesai)
                            <div class="small">Selesai: <span class="fw-semibold text-dark">{{ $jasa->tanggal_selesai->format('d/m/Y') }}</span></div>
                        @else
                            <div class="small text-muted italic">Langganan Selamanya</div>
                        @endif
                    </td>
                    <td class="fw-medium">
                        {{ $jasa->penanggungJawab->nama_lengkap ?? 'Belum ditentukan' }}
                    </td>
                    <td>
                        @if($jasa->status === 'Aktif')
                            <span class="badge rounded-pill bg-success-soft text-success px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Aktif</span>
                        @elseif($jasa->status === 'Selesai')
                            <span class="badge rounded-pill bg-info-soft text-info px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Selesai</span>
                        @else
                            <span class="badge rounded-pill bg-warning-soft text-warning px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">Tenggang</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @can('edit-barang')
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-edit-jasa" data-id="{{ $jasa->id }}" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            @endcan
                            @can('hapus-barang')
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-jasa" data-id="{{ $jasa->id }}" data-nama="{{ $jasa->barang->nama_barang }}" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-table_card>
        </div>
    </div>

    {{-- ===== MODAL TAMBAH JASA ===== --}}
    <x-modal_form id="modalTambahJasa" formId="formTambahJasa" title="<i class='bi bi-gear-wide-connected me-2'></i> Registrasi Transaksi Jasa">
        <x-slot name="slot">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Pilih Katalog Jasa <span class="text-danger">*</span></label>
                    <select name="barang_id" id="tambah_jasa_barang_id" class="form-select form-control-custom" required>
                        <option value="">-- Pilih Jasa --</option>
                        @foreach ($katalogJasa as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_barang }} ({{ $kat->kode_barang }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nomor Invoice</label>
                    <input type="text" name="no_invoice" id="tambah_jasa_no_invoice" class="form-control form-control-custom" placeholder="Contoh: INV-2026-XXXX">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Harga / Nilai Transaksi (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga_transaksi" id="tambah_jasa_harga_transaksi" class="form-control form-control-custom" placeholder="0" min="0" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Vendor / Supplier</label>
                    <select name="supplier_id" id="tambah_jasa_supplier_id" class="form-select form-control-custom">
                        <option value="">-- Tanpa Vendor --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Internal PIC / Penanggung Jawab</label>
                    <select name="penanggung_jawab_id" id="tambah_jasa_penanggung_jawab_id" class="form-select form-control-custom">
                        <option value="">-- Pilih PIC --</option>
                        @foreach ($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Tanggal Mulai Pengerjaan <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" id="tambah_jasa_tanggal_mulai" class="form-control form-control-custom" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Tanggal Selesai (Opsional)</label>
                    <input type="date" name="tanggal_selesai" id="tambah_jasa_tanggal_selesai" class="form-control form-control-custom">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Status Pengerjaan / Tagihan <span class="text-danger">*</span></label>
                    <select name="status" id="tambah_jasa_status" class="form-select form-control-custom" required>
                        <option value="Aktif" selected>Aktif (Berjalan / Berlangganan)</option>
                        <option value="Selesai">Selesai (Selesai Dikerjakan)</option>
                        <option value="Tenggang">Tenggang (Jatuh Tempo Pembayaran)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan Tambahan / Detail Jasa</label>
                    <textarea name="keterangan" id="tambah_jasa_keterangan" rows="3" class="form-control form-control-custom" placeholder="Tulis rincian pengerjaan jasa atau catatan billing..."></textarea>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    {{-- ===== MODAL EDIT JASA ===== --}}
    <x-modal_form id="modalEditJasa" formId="formEditJasa" title="<i class='bi bi-pencil-square me-2'></i> Edit Transaksi Jasa">
        <x-slot name="slot">
            <input type="hidden" id="edit_jasa_id">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Pilih Katalog Jasa <span class="text-danger">*</span></label>
                    <select name="barang_id" id="edit_jasa_barang_id" class="form-select form-control-custom" required>
                        <option value="">-- Pilih Jasa --</option>
                        @foreach ($katalogJasa as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_barang }} ({{ $kat->kode_barang }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nomor Invoice</label>
                    <input type="text" name="no_invoice" id="edit_jasa_no_invoice" class="form-control form-control-custom">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Harga / Nilai Transaksi (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga_transaksi" id="edit_jasa_harga_transaksi" class="form-control form-control-custom" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Vendor / Supplier</label>
                    <select name="supplier_id" id="edit_jasa_supplier_id" class="form-select form-control-custom">
                        <option value="">-- Tanpa Vendor --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Internal PIC / Penanggung Jawab</label>
                    <select name="penanggung_jawab_id" id="edit_jasa_penanggung_jawab_id" class="form-select form-control-custom">
                        <option value="">-- Pilih PIC --</option>
                        @foreach ($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Tanggal Mulai Pengerjaan <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" id="edit_jasa_tanggal_mulai" class="form-control form-control-custom" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Tanggal Selesai (Opsional)</label>
                    <input type="date" name="tanggal_selesai" id="edit_jasa_tanggal_selesai" class="form-control form-control-custom">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">Status Pengerjaan / Tagihan <span class="text-danger">*</span></label>
                    <select name="status" id="edit_jasa_status" class="form-select form-control-custom" required>
                        <option value="Aktif">Aktif (Berjalan / Berlangganan)</option>
                        <option value="Selesai">Selesai (Selesai Dikerjakan)</option>
                        <option value="Tenggang">Tenggang (Jatuh Tempo Pembayaran)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan Tambahan / Detail Jasa</label>
                    <textarea name="keterangan" id="edit_jasa_keterangan" rows="3" class="form-control form-control-custom"></textarea>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $(document).ready(function () {
            // Initialize DataTable
            $('#tableJasa').DataTable({
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    search: 'Cari:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center py-3">Belum ada transaksi jasa terdaftar.</div>'
                }
            });

            // ===== TAMBAH JASA SUBMIT =====
            $('#formTambahJasa').on('submit', function (e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('jasa.store') }}",
                    type: 'POST',
                    data: formData,
                    success: function (res) {
                        $('#modalTambahJasa').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Gagal menyimpan transaksi jasa.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            });

            // ===== SHOW EDIT MODAL =====
            $(document).on('click', '.btn-edit-jasa', function () {
                let id = $(this).data('id');
                $.get('/registrasi-jasa/' + id + '/edit', function (res) {
                    let j = res.jasa;
                    $('#edit_jasa_id').val(j.id);
                    $('#edit_jasa_barang_id').val(j.barang_id);
                    $('#edit_jasa_no_invoice').val(j.no_invoice);
                    $('#edit_jasa_harga_transaksi').val(parseFloat(j.harga_transaksi));
                    $('#edit_jasa_supplier_id').val(j.supplier_id ? j.supplier_id : '');
                    $('#edit_jasa_penanggung_jawab_id').val(j.penanggung_jawab_id ? j.penanggung_jawab_id : '');
                    
                    // Format dates
                    if(j.tanggal_mulai) {
                        let dateMulai = new Date(j.tanggal_mulai).toISOString().split('T')[0];
                        $('#edit_jasa_tanggal_mulai').val(dateMulai);
                    }
                    if(j.tanggal_selesai) {
                        let dateSelesai = new Date(j.tanggal_selesai).toISOString().split('T')[0];
                        $('#edit_jasa_tanggal_selesai').val(dateSelesai);
                    } else {
                        $('#edit_jasa_tanggal_selesai').val('');
                    }

                    $('#edit_jasa_status').val(j.status);
                    $('#edit_jasa_keterangan').val(j.keterangan);
                    $('#modalEditJasa').modal('show');
                });
            });

            // ===== EDIT SUBMIT =====
            $('#formEditJasa').on('submit', function (e) {
                e.preventDefault();
                let id = $('#edit_jasa_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: '/registrasi-jasa/' + id + '/update',
                    type: 'POST',
                    data: formData,
                    success: function (res) {
                        $('#modalEditJasa').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Gagal mengupdate data jasa.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            });

            // ===== DELETE =====
            $(document).on('click', '.btn-delete-jasa', function () {
                let id = $(this).data('id');
                let nama = $(this).data('nama');

                Swal.fire({
                    title: 'Hapus Data Jasa?',
                    html: `Apakah Anda yakin ingin menghapus transaksi registrasi jasa <b>${nama}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/registrasi-jasa/' + id + '/delete',
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function () {
                                Swal.fire('Gagal!', 'Gagal menghapus data jasa.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts>
