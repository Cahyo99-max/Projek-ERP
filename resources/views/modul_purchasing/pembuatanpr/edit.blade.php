{{-- ===== MODAL EDIT ===== --}}
<div class="modal fade" id="modalEditPR" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="formEditPR" class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" id="edit_pr_id">
            <div class="modal-header border-0 px-4 py-3.5 position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); width: 44px; height: 44px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                        <i class="bi bi-pencil-square fs-5 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6 fs-md-5">Edit Pengajuan Pembelian (PR)</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 11px; font-weight: 600;" id="edit_nomor_pr_text">
                                PR/0000/00/0000
                            </span>
                            <span class="badge rounded-pill px-2.5 py-0.5 ms-1" style="background: rgba(255, 255, 255, 0.3); color: #ffffff; font-size: 10px; font-weight: 700;" id="edit_status_pr_badge">
                                DRAFT
                            </span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;">Perubahan data pengajuan pembelian barang</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                    <!-- Header Section (Luxury Card) -->
                    <div class="luxury-card p-3 p-md-4 mb-3.5 rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-info-circle-fill fs-6"></i>
                            </span>
                            <h6 class="fw-bold text-primary mb-0 fs-6">Informasi Pengajuan</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pr" id="edit_tanggal_pr" class="form-control form-control-custom rounded-3" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Tanggal Dibutuhkan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_dibutuhkan" id="edit_tanggal_dibutuhkan" class="form-control form-control-custom rounded-3" required>
                            </div>
                            <input type="hidden" name="departemen_id" id="edit_departemen_id" value="">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Dibuat Oleh</label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary text-muted border-end-0"><i class="bi bi-person-check-fill text-success"></i></span>
                                    <input type="text" id="edit_dibuat_oleh_nama" class="form-control form-control-custom bg-body-tertiary fw-semibold" readonly placeholder="Pembuat PR">
                                    <input type="hidden" name="dibuat_oleh" id="edit_dibuat_oleh">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Diminta Oleh <span class="text-danger">*</span></label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <input type="text" id="edit_diminta_oleh_nama" class="form-control form-control-custom bg-light border-end-0 fw-semibold" readonly placeholder="Pilih Karyawan..." required>
                                    <input type="hidden" name="diminta_oleh" id="edit_diminta_oleh">
                                    <button class="btn btn-primary btn-lookup-karyawan border-0 px-3" type="button" data-type="edit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Keterangan / Alasan Pengadaan</label>
                                    <small class="text-muted" style="font-size: 0.72rem;">(Maksimal 40 Karakter)</small>
                                </div>
                                <textarea name="keterangan" id="edit_keterangan" rows="2" class="form-control form-control-custom rounded-3" maxlength="40" placeholder="Contoh: Stok gudang menipis / Pengadaan barang... (Maksimal 40 karakter)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid Section (Luxury Card) -->
                    <div class="luxury-card p-3 p-md-4 mb-3.5 rounded-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-list-stars fs-6"></i>
                                </span>
                                <h6 class="fw-bold mb-0 text-primary fs-6">Daftar Barang yang Diajukan</h6>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 btn-add-item-row fw-bold d-inline-flex align-items-center gap-1.5" data-type="edit">
                                <i class="bi bi-plus-circle-fill"></i> <span>Tambah Barang</span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0 responsive-pr-table w-100" id="edit_items_table">
                                <thead>
                                    <tr class="bg-light text-muted small border-bottom" style="font-size: 0.75rem; font-weight: 600;">
                                        <th width="4%" class="text-center py-2">No</th>
                                        <th width="12%">Tipe</th>
                                        <th width="26%">Nama Barang</th>
                                        <th width="10%">Satuan</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="15%">Estimasi Harga</th>
                                        <th width="13%">Total</th>
                                        <th width="16%">Catatan</th>
                                        <th width="4%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_items_tbody">
                                    <!-- Rows added dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Budget Summary Area -->
                    <div class="row justify-content-end align-items-center g-3">
                        <div class="col-12 col-md-6 col-lg-4 text-start text-md-end">
                            <div class="p-3 rounded-4 border d-flex justify-content-between align-items-center gap-3" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(59, 130, 246, 0.03) 100%); border-color: rgba(37, 99, 235, 0.25) !important;">
                                <span class="fw-bold text-uppercase small text-muted" style="letter-spacing: 0.5px; font-size: 11px;">Total Anggaran:</span>
                                <span class="fw-extrabold fs-4 text-primary font-monospace" id="edit_grand_total">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-3 px-md-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: rgba(0,0,0,0.02);">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-semibold btn-update-pr" data-status="Draft">
                            <i class="bi bi-save me-1"></i>Simpan Draft
                        </button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold btn-update-pr shadow-sm" data-status="Pending" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                            <i class="bi bi-send-fill me-1"></i>Kirim Pengajuan
                        </button>
                        @if(auth()->user()->hasRole('Super Admin'))
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-4 py-2 fw-bold btn-update-pr shadow-sm" id="btn_save_keep_approved" data-status="Approved" style="display: none; background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); border: none;">
                            <i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan
                        </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== EDIT SHOW =====
        $(document).on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            $.get('/purchase-requisition/' + id + '/edit', function (res) {
                let pr = res.pr;
                $('#edit_pr_id').val(pr.id);
                $('#edit_nomor_pr_text').text(pr.nomor_pr);
                let st = (pr.status || 'DRAFT').toUpperCase();
                $('#edit_status_pr_badge').text(st);
                if (pr.status === 'Approved') {
                    $('#edit_status_pr_badge').css('background', 'rgba(34, 197, 94, 0.4)').css('color', '#ffffff');
                    $('#btn_save_keep_approved').show();
                } else if (pr.status === 'Rejected') {
                    $('#edit_status_pr_badge').css('background', 'rgba(239, 68, 68, 0.4)').css('color', '#ffffff');
                    $('#btn_save_keep_approved').hide();
                } else if (pr.status === 'Pending') {
                    $('#edit_status_pr_badge').css('background', 'rgba(245, 158, 11, 0.4)').css('color', '#ffffff');
                    $('#btn_save_keep_approved').hide();
                } else {
                    $('#edit_status_pr_badge').css('background', 'rgba(255, 255, 255, 0.3)').css('color', '#ffffff');
                    $('#btn_save_keep_approved').hide();
                }
                
                let tanggalPr = pr.tanggal_pr.substring(0, 10);
                let tanggalDibutuhkan = pr.tanggal_dibutuhkan.substring(0, 10);
                
                $('#edit_tanggal_pr').val(tanggalPr);
                $('#edit_tanggal_dibutuhkan').val(tanggalDibutuhkan);
                $('#edit_departemen_id').val(pr.departemen_id);
                $('#edit_keterangan').val(pr.keterangan);
                $('#edit_dibuat_oleh').val(pr.dibuat_oleh);
                $('#edit_dibuat_oleh_nama').val(pr.creator ? pr.creator.nama_lengkap : 'Tidak Diketahui');
                $('#edit_diminta_oleh').val(pr.diminta_oleh);
                $('#edit_diminta_oleh_nama').val(pr.requester ? pr.requester.nama_lengkap : 'Tidak Diketahui');

                // Populate Detail items
                $('#edit_items_tbody').empty();
                if (pr.details && pr.details.length > 0) {
                    pr.details.forEach(item => {
                        addItemRow('edit_items_tbody', item);
                    });
                } else {
                    addItemRow('edit_items_tbody');
                }

                $('#modalEditPR').modal('show');
            }).fail(function(err) {
                let errorMsg = 'Gagal mengambil data pengajuan.';
                if (err.responseJSON && err.responseJSON.message) errorMsg = err.responseJSON.message;
                Swal.fire('Gagal!', errorMsg, 'error');
            });
        });

        // ===== RESET ON MODAL CLOSES =====
        $('#modalEditPR').on('hidden.bs.modal', function () {
            $('#formEditPR')[0].reset();
            $('#edit_diminta_oleh').val('');
            $('#edit_diminta_oleh_nama').val('');
            $('#edit_dibuat_oleh').val('');
            $('#edit_dibuat_oleh_nama').val('');
            $('#edit_items_tbody').empty();
            $('#edit_grand_total').text('Rp 0');
        });

        // ===== UPDATE PR =====
        $(document).on('click', '.btn-update-pr', function () {
            let status = $(this).data('status');
            let id = $('#edit_pr_id').val();
            let form = $('#formEditPR');
            let saveBtn = $(this);

            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            let itemRowsCount = $('#edit_items_tbody tr.item-row').length;
            if (itemRowsCount === 0) {
                Swal.fire({ title: 'Perhatian!', text: 'Mohon ajukan minimal 1 barang.', icon: 'warning' });
                return;
            }

            if (itemRowsCount > 20) {
                Swal.fire({
                    title: 'Batas Item Terlampaui!',
                    html: `Pengajuan PR dibatasi <b>maksimal 20 item barang</b>. Saat ini terdapat <b>${itemRowsCount} item</b>.`,
                    icon: 'error'
                });
                return;
            }

            let headerKet = $.trim($('#edit_keterangan').val() || '');
            if (headerKet.length > 40) {
                Swal.fire({
                    title: 'Keterangan Terlalu Panjang!',
                    html: `Keterangan / alasan pengadaan maksimal <b>40 karakter</b>. Panjang saat ini: <b class="text-danger">${headerKet.length} karakter</b>.`,
                    icon: 'error'
                });
                return;
            }

            // Gather items details manually to unformat prices cleanly
            let items = [];
            let invalidRow = false;
            let invalidRowNum = 0;
            let invalidKetRow = false;
            let invalidKetRowNum = 0;
            let invalidKetLen = 0;

            $('#edit_items_tbody tr.item-row').each(function (idx) {
                let bid = $(this).find('.select-product-input').val();
                let qty = parseFloat($(this).find('.input-jumlah').val()) || 0;
                let itemKet = $.trim($(this).find('.input-keterangan').val() || '');

                if (!bid) { invalidRow = true; invalidRowNum = idx + 1; return false; }
                if (qty <= 0) { invalidRow = true; invalidRowNum = idx + 1; return false; }
                if (itemKet.length > 40) {
                    invalidKetRow = true;
                    invalidKetRowNum = idx + 1;
                    invalidKetLen = itemKet.length;
                    return false;
                }

                let satuanVal = $(this).find('.select-satuan-input').val();
                let faktorVal = parseFloat($(this).find('.input-faktor-pengali').val()) || 1;

                items.push({
                    barang_id: bid,
                    satuan_id: satuanVal || null,
                    faktor_pengali: faktorVal,
                    jumlah: qty,
                    harga_estimasi: unformatNumber($(this).find('.input-harga-formatted').val()),
                    keterangan: itemKet
                });
            });

            if (invalidRow) {
                Swal.fire({ title: 'Data Belum Lengkap!', html: `Baris ke-<b>${invalidRowNum}</b>: pastikan barang sudah dipilih dan jumlah lebih dari 0.`, icon: 'warning' });
                return;
            }

            if (invalidKetRow) {
                Swal.fire({
                    title: 'Catatan Item Terlalu Panjang!',
                    html: `Baris ke-<b>${invalidKetRowNum}</b>: Catatan item maksimal <b>40 karakter</b>. Panjang saat ini: <b class="text-danger">${invalidKetLen} karakter</b>.`,
                    icon: 'error'
                });
                return;
            }

            let payload = {
                tanggal_pr: $('#edit_tanggal_pr').val(),
                tanggal_dibutuhkan: $('#edit_tanggal_dibutuhkan').val(),
                departemen_id: $('#edit_departemen_id').val(),
                diminta_oleh: $('#edit_diminta_oleh').val(),
                dibuat_oleh: $('#edit_dibuat_oleh').val(),
                keterangan: $('#edit_keterangan').val(),
                status: status,
                items: items
            };

            if (!payload.diminta_oleh) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Harap isikan peminta barang.',
                    icon: 'warning'
                });
                return;
            }

            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "/purchase-requisition/" + id + "/update",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    saveBtn.prop('disabled', false).html(status === 'Approved' ? '<i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan' : (status === 'Draft' ? 'Simpan Draft' : 'Kirim Pengajuan'));
                    $('#modalEditPR').modal('hide');
                    reloadTablePR();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message || 'Pengajuan PR berhasil diperbarui.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                },
                error: function (err) {
                    saveBtn.prop('disabled', false).html(status === 'Approved' ? '<i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan' : (status === 'Draft' ? 'Simpan Draft' : 'Kirim Pengajuan'));
                    let errorMsg = 'Terjadi kesalahan saat memperbarui pengajuan.';
                    if (err.status === 419) {
                        errorMsg = 'Sesi telah habis (CSRF). Silakan refresh halaman dan coba lagi.';
                    } else if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + val[0] + '<br>';
                        });
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    }
                    Swal.fire({ title: 'Gagal Menyimpan!', html: errorMsg, icon: 'error' });
                }
            });
        });
    });
</script>
@endpush
