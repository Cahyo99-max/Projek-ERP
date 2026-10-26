{{-- ===== MODAL TAMBAH ===== --}}
<div class="modal fade" id="modalTambahPR" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="formTambahPR" class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <div class="modal-header border-0 px-4 py-3.5 position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); width: 44px; height: 44px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                        <i class="bi bi-file-earmark-plus-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6 fs-md-5">Buat Pengajuan Pembelian (PR)</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 10px; font-weight: 600;">NEW DRAFT</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;">Nomor PR akan dibuat secara otomatis oleh sistem</small>
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
                            <!-- Nomor PR Field -->
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label small fw-bold mb-0">Nomor PR</label>
                                    <div class="form-check form-switch mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="tambah_nomor_pr_manual_toggle" role="switch">
                                        <label class="form-check-label small text-muted" for="tambah_nomor_pr_manual_toggle" style="font-size: 11px;">Input Manual</label>
                                    </div>
                                </div>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary text-primary border-end-0">
                                        <i class="bi bi-hash"></i>
                                    </span>
                                    <input type="text" name="nomor_pr" id="tambah_nomor_pr"
                                        class="form-control form-control-custom border-start-0 fw-semibold"
                                        placeholder="Otomatis: PR/YYYY/MM/0001"
                                        maxlength="15"
                                        style="background-color: #f8fafc;"
                                        disabled readonly>
                                    <span class="input-group-text bg-body-tertiary border-start-0" id="tambah_nomor_pr_badge">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bi bi-magic me-1"></i>Auto Generate
                                        </span>
                                    </span>
                                </div>
                                <div class="form-text text-muted" id="tambah_nomor_pr_hint" style="font-size: 11px;">
                                    <i class="bi bi-info-circle me-1"></i>Nomor PR akan dibuat otomatis sesuai format <code>PR/YYYY/MM/XXXX</code>. Aktifkan <b>Input Manual</b> untuk mengisi sendiri.
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pr" id="tambah_tanggal_pr" class="form-control form-control-custom rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Tanggal Dibutuhkan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_dibutuhkan" id="tambah_tanggal_dibutuhkan" class="form-control form-control-custom rounded-3" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>
                            <input type="hidden" name="departemen_id" id="tambah_departemen_id" value="">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Dibuat Oleh</label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary text-muted border-end-0"><i class="bi bi-person-check-fill text-success"></i></span>
                                    <input type="text" id="tambah_dibuat_oleh_nama" class="form-control form-control-custom bg-body-tertiary fw-semibold" readonly value="{{ auth()->user()->nama_lengkap }}">
                                    <input type="hidden" name="dibuat_oleh" id="tambah_dibuat_oleh" value="{{ auth()->id() }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small fw-bold">Diminta Oleh <span class="text-danger">*</span></label>
                                <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                                    <input type="text" id="tambah_diminta_oleh_nama" class="form-control form-control-custom bg-light border-end-0 fw-semibold" readonly placeholder="Pilih Karyawan..." required value="">
                                    <input type="hidden" name="diminta_oleh" id="tambah_diminta_oleh" value="">
                                    <button class="btn btn-primary btn-lookup-karyawan border-0 px-3" type="button" data-type="tambah">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Keterangan / Alasan Pengadaan</label>
                                    <small class="text-muted" style="font-size: 0.72rem;">(Maksimal 40 Karakter)</small>
                                </div>
                                <textarea name="keterangan" id="tambah_keterangan" rows="2" class="form-control form-control-custom rounded-3" maxlength="40" placeholder="Contoh: Stok gudang menipis / Pengadaan barang... (Maksimal 40 karakter)"></textarea>
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
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 btn-add-item-row fw-bold d-inline-flex align-items-center gap-1.5" data-type="tambah">
                                <i class="bi bi-plus-circle-fill"></i> <span>Tambah Barang</span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0 responsive-pr-table w-100" id="tambah_items_table">
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
                                <tbody id="tambah_items_tbody">
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
                                <span class="fw-extrabold fs-4 text-primary font-monospace" id="tambah_grand_total">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-3 px-md-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: rgba(0,0,0,0.02);">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-semibold btn-save-pr" data-status="Draft">
                            <i class="bi bi-save me-1"></i>Simpan Draft
                        </button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold btn-save-pr shadow-sm" data-status="Pending" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                            <i class="bi bi-send-fill me-1"></i>Kirim Pengajuan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== RESET ON MODAL CLOSES =====
        $('#modalTambahPR').on('show.bs.modal', function () {
            $('#tambah_items_tbody').empty();
            addItemRow('tambah_items_tbody'); // default row
        });

        $('#modalTambahPR').on('hidden.bs.modal', function () {
            $('#formTambahPR')[0].reset();
            $('#tambah_diminta_oleh').val('');
            $('#tambah_diminta_oleh_nama').val('');
            // Reset dibuat_oleh back to current logged-in user (User ID)
            $('#tambah_dibuat_oleh').val('{{ auth()->id() }}');
            $('#tambah_dibuat_oleh_nama').val('{{ auth()->user()->nama_lengkap }}');
            $('#tambah_items_tbody').empty();
            $('#tambah_grand_total').text('Rp 0');
            // Reset nomor PR back to auto mode
            resetNomorPrToAuto();
        });

        // ===== NOMOR PR AUTO / MANUAL TOGGLE =====
        function resetNomorPrToAuto() {
            $('#tambah_nomor_pr_manual_toggle').prop('checked', false);
            $('#tambah_nomor_pr')
                .prop('disabled', true)
                .prop('readonly', true)
                .val('')
                .css('background-color', '#f8fafc');
            $('#tambah_nomor_pr_badge').html(`
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                    <i class="bi bi-magic me-1"></i>Auto Generate
                </span>`);
            $('#tambah_nomor_pr_hint').html(`<i class="bi bi-info-circle me-1"></i>Nomor PR akan dibuat otomatis sesuai format <code>PR/YYYY/MM/XXXX</code>. Aktifkan <b>Input Manual</b> untuk mengisi sendiri.`);
        }

        $('#tambah_nomor_pr_manual_toggle').on('change', function () {
            const isManual = $(this).is(':checked');
            const input = $('#tambah_nomor_pr');
            const badge = $('#tambah_nomor_pr_badge');
            const hint  = $('#tambah_nomor_pr_hint');

            if (isManual) {
                input.prop('disabled', false).prop('readonly', false)
                     .css('background-color', '').attr('placeholder', 'Contoh: PR/2026/07/0099')
                     .focus();
                badge.html(`
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                        <i class="bi bi-pencil-fill me-1"></i>Input Manual
                    </span>`);
                hint.html(`<i class="bi bi-exclamation-triangle text-warning me-1"></i>Nomor PR <b>harus unik</b> (Maksimal 15 karakter). Format bebas, contoh: <code>PR/2026/07/0099</code>`);
            } else {
                input.prop('disabled', true).prop('readonly', true)
                     .val('').css('background-color', '#f8fafc')
                     .attr('placeholder', 'Otomatis: PR/YYYY/MM/0001');
                badge.html(`
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                        <i class="bi bi-magic me-1"></i>Auto Generate
                    </span>`);
                hint.html(`<i class="bi bi-info-circle me-1"></i>Nomor PR akan dibuat otomatis sesuai format <code>PR/YYYY/MM/XXXX</code>. Aktifkan <b>Input Manual</b> untuk mengisi sendiri.`);
            }
        });

        // ===== SAVE PR (STORE) =====
        $(document).on('click', '.btn-save-pr', function () {
            let status = $(this).data('status');
            let form = $('#formTambahPR');
            let saveBtn = $(this);
            
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            let itemRowsCount = $('#tambah_items_tbody tr.item-row').length;
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

            let headerKet = $.trim($('#tambah_keterangan').val() || '');
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

            $('#tambah_items_tbody tr.item-row').each(function (idx) {
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
                nomor_pr: $('#tambah_nomor_pr_manual_toggle').is(':checked') ? $.trim($('#tambah_nomor_pr').val()) : '',
                tanggal_pr: $('#tambah_tanggal_pr').val(),
                tanggal_dibutuhkan: $('#tambah_tanggal_dibutuhkan').val(),
                departemen_id: $('#tambah_departemen_id').val(),
                diminta_oleh: $('#tambah_diminta_oleh').val(),
                dibuat_oleh: $('#tambah_dibuat_oleh').val(),
                keterangan: $('#tambah_keterangan').val(),
                status: status,
                items: items
            };

            // Validate manual nomor_pr not empty & max 15 chars when toggle is on
            if ($('#tambah_nomor_pr_manual_toggle').is(':checked')) {
                if (!payload.nomor_pr) {
                    Swal.fire({ title: 'Perhatian!', text: 'Nomor PR tidak boleh kosong jika mode manual diaktifkan.', icon: 'warning' });
                    return;
                }
                if (payload.nomor_pr.length > 15) {
                    Swal.fire({
                        title: 'Nomor PR Terlalu Panjang!',
                        html: `Nomor PR manual maksimal <b>15 karakter</b>. Panjang saat ini: <b class="text-danger">${payload.nomor_pr.length} karakter</b>.`,
                        icon: 'error'
                    });
                    return;
                }
            }

            if (!payload.diminta_oleh) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Harap isikan peminta barang.',
                    icon: 'warning'
                });
                return;
            }

            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');

            $.ajax({
                url: "{{ route('pr.store') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    saveBtn.prop('disabled', false).html(status === 'Draft' ? 'Simpan Draft' : 'Kirim Pengajuan');
                    $('#modalTambahPR').modal('hide');
                    reloadTablePR();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message || (status === 'Draft' ? 'Draft PR berhasil disimpan.' : 'Pengajuan PR berhasil dikirim.'),
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                },
                error: function (err) {
                    saveBtn.prop('disabled', false).html(status === 'Draft' ? 'Simpan Draft' : 'Kirim Pengajuan');
                    let errorMsg = 'Terjadi kesalahan saat menyimpan pengajuan.';
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
