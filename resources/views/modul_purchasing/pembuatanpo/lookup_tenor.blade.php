{{-- ===== MODAL CARI TENOR ===== --}}
<div class="modal fade picker-modal" id="modalCariTenor" tabindex="-1" data-bs-backdrop="false" style="z-index: 99999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-3 px-3 px-sm-4 pt-3 pt-sm-4 d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2.5 flex-grow-1 min-w-0">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="bi bi-calendar-range fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="modal-title fw-bold mb-0 text-truncate text-dark fs-6">Pilih Tenor Pembayaran</h6>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">Cari atau tambah tenor baru</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 text-nowrap d-inline-flex align-items-center gap-1 shadow-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAddTenorForm" aria-expanded="false" style="font-size: 0.78rem;">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Tambah </span>Tenor
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <!-- Collapsible Form Tambah Tenor Baru -->
                <div class="collapse mb-3" id="collapseAddTenorForm">
                    <div class="card card-body bg-light border-0 rounded-3 shadow-sm p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0 text-primary fs-7"><i class="bi bi-plus-circle me-1"></i> Tambah Tenor Pembayaran Baru</h6>
                            <button type="button" class="btn-close ms-auto fs-8" data-bs-toggle="collapse" data-bs-target="#collapseAddTenorForm"></button>
                        </div>
                        <form id="formQuickAddTenor">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Nama Tenor <span class="text-danger">*</span> (Max 15)</label>
                                    <input type="text" name="nama_term" id="quick_nama_term" class="form-control form-control-sm" placeholder="Contoh: TOP 45 Hari" required maxlength="15">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Jumlah Hari <span class="text-danger">*</span> (Max 10 digit)</label>
                                    <input type="text" name="jumlah_hari" id="quick_jumlah_hari" class="form-control form-control-sm" placeholder="Contoh: 45" required maxlength="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="Active" selected>Active (Aktif)</option>
                                        <option value="Inactive">Inactive (Nonaktif)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Keterangan (Opsional) (Max 30)</label>
                                    <input type="text" name="keterangan" id="quick_keterangan" class="form-control form-control-sm" placeholder="Catatan syarat pembayaran..." maxlength="30">
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-toggle="collapse" data-bs-target="#collapseAddTenorForm">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold" id="btnSubmitQuickTenor"><i class="bi bi-save me-1"></i> Simpan Tenor</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="tenorSearchInput" class="form-control picker-search-input" placeholder="Cari nama tenor atau jumlah hari...">
                </div>
                <div id="tenorResultsList" class="picker-results-list">
                    <!-- Cash / COD Option -->
                    <div class="picker-item" data-id="" data-name="Cash / COD" data-hari="0">
                        <div class="picker-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;"><i class="bi bi-cash"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">Cash / COD</div>
                            <div class="picker-sub">Bayar tunai di tempat</div>
                        </div>
                        <span class="picker-badge bg-secondary-subtle text-secondary">0 Hari</span>
                    </div>
                    @foreach($paymentTerms as $pt)
                        <div class="picker-item" data-id="{{ $pt->id }}" data-name="{{ $pt->nama_term }}" data-hari="{{ $pt->jumlah_hari }}">
                            <div class="picker-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;"><i class="bi bi-calendar-range"></i></div>
                            <div class="picker-meta">
                                <div class="picker-name">{{ $pt->nama_term }}</div>
                                <div class="picker-sub">{{ $pt->keterangan ?? 'Tenor jangka waktu pembayaran' }}</div>
                            </div>
                            <span class="picker-badge bg-primary-subtle text-primary">{{ $pt->jumlah_hari }} Hari</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        let currentTenorTarget = 'create';

        // Open Trigger Delegated
        $(document).on('click', '#payment_term_display, #btnOpenTenorPicker, #edit_payment_term_display, #btnOpenEditTenorPicker', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).attr('id') || '';
            currentTenorTarget = id.includes('edit') ? 'edit' : 'create';
            let targetModal = document.getElementById('modalCariTenor');
            if (targetModal) {
                if (targetModal.parentNode !== document.body) {
                    document.body.appendChild(targetModal);
                }
                bootstrap.Modal.getOrCreateInstance(targetModal, { backdrop: false, keyboard: true }).show();
            }
        });

        // Select Tenor Item Delegated
        $(document).on('click', '#tenorResultsList .picker-item', function () {
            let id = $(this).data('id');
            let name = $(this).data('name');

            if (currentTenorTarget === 'edit') {
                $('#edit_payment_term_id').val(id);
                $('#edit_payment_term_display').val(name);
            } else {
                $('#payment_term_id').val(id);
                $('#payment_term_display').val(name);
            }

            let targetModal = document.getElementById('modalCariTenor');
            if (targetModal) {
                let m = bootstrap.Modal.getInstance(targetModal);
                if (m) m.hide();
            }
        });

        // Close on background click
        $(document).on('click', '#modalCariTenor', function (e) {
            if ($(e.target).hasClass('modal')) {
                let m = bootstrap.Modal.getInstance(this);
                if (m) m.hide();
            }
        });

        // Local Search Input
        $(document).on('input', '#tenorSearchInput', function () {
            let query = $(this).val().toLowerCase().trim();
            let items = $('#tenorResultsList .picker-item');
            let found = false;

            items.each(function () {
                let name = ($(this).data('name') || '').toString().toLowerCase();
                let subText = $(this).find('.picker-sub').text().toLowerCase();
                if (name.includes(query) || subText.includes(query)) {
                    $(this).removeClass('d-none');
                    found = true;
                } else {
                    $(this).addClass('d-none');
                }
            });

            let emptyEl = $('#tenorResultsList .picker-empty');
            if (!found) {
                if (emptyEl.length === 0) {
                    $('#tenorResultsList').append('<div class="picker-empty"><i class="bi bi-calendar-range"></i>Tenor tidak ditemukan</div>');
                }
            } else {
                emptyEl.remove();
            }
        });

        // Submit Quick Add Tenor Form
        $('#formQuickAddTenor').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btnSubmitQuickTenor');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: '{{ route("payment-term.store") }}',
                type: 'POST',
                data: form.serialize(),
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Tenor');
                    let pt = res.term;

                    if (pt) {
                        // Append to results list dynamically
                        let newHtml = `
                            <div class="picker-item" data-id="${pt.id}" data-name="${pt.nama_term}" data-hari="${pt.jumlah_hari}">
                                <div class="picker-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;"><i class="bi bi-calendar-range"></i></div>
                                <div class="picker-meta">
                                    <div class="picker-name">${pt.nama_term}</div>
                                    <div class="picker-sub">${pt.keterangan || 'Tenor jangka waktu pembayaran'}</div>
                                </div>
                                <span class="picker-badge bg-primary-subtle text-primary">${pt.jumlah_hari} Hari</span>
                            </div>
                        `;
                        $('#tenorResultsList').prepend(newHtml);

                        // Auto select the new tenor for PO form
                        if (currentTenorTarget === 'edit') {
                            $('#edit_payment_term_id').val(pt.id);
                            $('#edit_payment_term_display').val(pt.nama_term);
                        } else {
                            $('#payment_term_id').val(pt.id);
                            $('#payment_term_display').val(pt.nama_term);
                        }

                        // Reset & hide collapse form
                        form[0].reset();
                        let collapseEl = document.getElementById('collapseAddTenorForm');
                        if (collapseEl) {
                            let bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                            if (bsCollapse) bsCollapse.hide();
                            else $(collapseEl).removeClass('show');
                        }

                        // Hide modal
                        let targetModal = document.getElementById('modalCariTenor');
                        if (targetModal) {
                            let m = bootstrap.Modal.getInstance(targetModal);
                            if (m) m.hide();
                        }

                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Tenor baru berhasil ditambahkan!'
                        });
                    }
                },
                error: function (err) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Tenor');
                    let msg = err.responseJSON?.message || 'Gagal menyimpan tenor baru.';
                    if (err.responseJSON?.errors) {
                        msg = Object.values(err.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        title: 'Peringatan!',
                        html: msg,
                        icon: 'warning',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                }
            });
        });

        // Character Limit Warning Alert Handlers for Quick Add Tenor
        let tenorAlertShown = false;
        function enforceTenorLimit(el, maxLen, fieldName) {
            let val = $(el).val();
            if (val.length >= maxLen) {
                if (!tenorAlertShown) {
                    tenorAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: `Input ${fieldName} tidak boleh melebihi ${maxLen} ${fieldName === 'Jumlah Hari' ? 'digit' : 'karakter'} yang telah ditentukan.`,
                        confirmButtonColor: '#0d6efd',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        tenorAlertShown = false;
                    });
                }
            }
        }

        $(document).on('input', '#quick_nama_term', function () {
            if ($(this).val().length > 15) {
                $(this).val($(this).val().substring(0, 15));
            }
            enforceTenorLimit(this, 15, 'Nama Tenor');
        });

        $(document).on('input', '#quick_jumlah_hari', function () {
            let numVal = $(this).val().replace(/[^0-9]/g, '');
            if (numVal.length > 10) {
                numVal = numVal.substring(0, 10);
            }
            $(this).val(numVal);
            enforceTenorLimit(this, 10, 'Jumlah Hari');
        });

        $(document).on('input', '#quick_keterangan', function () {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
            }
            enforceTenorLimit(this, 30, 'Keterangan');
        });

        $(document).on('show.bs.modal', '#modalCariTenor', function () {
            $('#tenorSearchInput').val('');
            $('#tenorResultsList .picker-item').removeClass('d-none');
            $('#tenorResultsList .picker-empty').remove();
        });

        $(document).on('shown.bs.modal', '#modalCariTenor', function () {
            $('#tenorSearchInput').focus();
        });
    });
</script>
@endpush
