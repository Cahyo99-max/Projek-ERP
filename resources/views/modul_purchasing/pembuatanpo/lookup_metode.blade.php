{{-- ===== MODAL CARI METODE ===== --}}
<div class="modal fade picker-modal" id="modalCariMetode" tabindex="-1" data-bs-backdrop="false" style="z-index: 99999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-3 px-3 px-sm-4 pt-3 pt-sm-4 d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2.5 flex-grow-1 min-w-0">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="bi bi-credit-card fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="modal-title fw-bold mb-0 text-truncate text-dark fs-6">Pilih Metode Pembayaran</h6>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">Cari atau tambah metode bayar baru</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button class="btn btn-sm btn-success rounded-pill px-3 py-1.5 text-white text-nowrap d-inline-flex align-items-center gap-1 shadow-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAddMetodeForm" aria-expanded="false" style="font-size: 0.78rem;">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Tambah </span>Metode
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <!-- Collapsible Form Tambah Metode Baru -->
                <div class="collapse mb-3" id="collapseAddMetodeForm">
                    <div class="card card-body bg-light border-0 rounded-3 shadow-sm p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0 text-success fs-7"><i class="bi bi-plus-circle me-1"></i> Tambah Metode Pembayaran Baru</h6>
                            <button type="button" class="btn-close ms-auto fs-8" data-bs-toggle="collapse" data-bs-target="#collapseAddMetodeForm"></button>
                        </div>
                        <form id="formQuickAddMetode">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Kode Metode <span class="text-danger">*</span> (Max 15)</label>
                                    <input type="text" name="kode_metode" id="quick_kode_metode" class="form-control form-control-sm" placeholder="Contoh: TRF" required maxlength="15">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Nama Metode <span class="text-danger">*</span> (Max 15)</label>
                                    <input type="text" name="nama_metode" id="quick_nama_metode" class="form-control form-control-sm" placeholder="Contoh: Transfer Bank" required maxlength="15">
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
                                    <input type="text" name="keterangan" id="quick_keterangan_metode" class="form-control form-control-sm" placeholder="Catatan info metode pembayaran..." maxlength="30">
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-toggle="collapse" data-bs-target="#collapseAddMetodeForm">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold" id="btnSubmitQuickMetode"><i class="bi bi-save me-1"></i> Simpan Metode</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="metodeSearchInput" class="form-control picker-search-input" placeholder="Cari nama atau kode metode...">
                </div>
                <div id="metodeResultsList" class="picker-results-list">
                    <!-- None / Belum Ditentukan Option -->
                    <div class="picker-item" data-id="" data-name="Belum Ditentukan">
                        <div class="picker-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;"><i class="bi bi-question-circle"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">Belum Ditentukan</div>
                            <div class="picker-sub">Tanpa metode pembayaran spesifik</div>
                        </div>
                    </div>
                    @foreach($paymentMethods as $pm)
                        <div class="picker-item" data-id="{{ $pm->id }}" data-name="{{ $pm->nama_metode }}">
                            <div class="picker-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;"><i class="bi bi-credit-card"></i></div>
                            <div class="picker-meta">
                                <div class="picker-name">{{ $pm->nama_metode }}</div>
                                <div class="picker-sub">Kode: {{ $pm->kode_metode }} | {{ $pm->keterangan ?? '-' }}</div>
                            </div>
                            <span class="picker-badge bg-success-subtle text-success">{{ $pm->kode_metode }}</span>
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
        let currentMetodeTarget = 'create';

        // Open Trigger Delegated
        $(document).on('click', '#payment_method_display, #btnOpenMetodePicker, #edit_payment_method_display, #btnOpenEditMetodePicker', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).attr('id') || '';
            currentMetodeTarget = id.includes('edit') ? 'edit' : 'create';
            let targetModal = document.getElementById('modalCariMetode');
            if (targetModal) {
                if (targetModal.parentNode !== document.body) {
                    document.body.appendChild(targetModal);
                }
                bootstrap.Modal.getOrCreateInstance(targetModal, { backdrop: false, keyboard: true }).show();
            }
        });

        // Select Metode Item Delegated
        $(document).on('click', '#metodeResultsList .picker-item', function () {
            let id = $(this).data('id');
            let name = $(this).data('name');

            if (currentMetodeTarget === 'edit') {
                $('#edit_payment_method_id').val(id);
                $('#edit_payment_method_display').val(name);
            } else {
                $('#payment_method_id').val(id);
                $('#payment_method_display').val(name);
            }

            let targetModal = document.getElementById('modalCariMetode');
            if (targetModal) {
                let m = bootstrap.Modal.getInstance(targetModal);
                if (m) m.hide();
            }
        });

        // Close on background click
        $(document).on('click', '#modalCariMetode', function (e) {
            if ($(e.target).hasClass('modal')) {
                let m = bootstrap.Modal.getInstance(this);
                if (m) m.hide();
            }
        });

        // Local Search Input
        $(document).on('input', '#metodeSearchInput', function () {
            let query = $(this).val().toLowerCase().trim();
            let items = $('#metodeResultsList .picker-item');
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

            let emptyEl = $('#metodeResultsList .picker-empty');
            if (!found) {
                if (emptyEl.length === 0) {
                    $('#metodeResultsList').append('<div class="picker-empty"><i class="bi bi-credit-card"></i>Metode tidak ditemukan</div>');
                }
            } else {
                emptyEl.remove();
            }
        });

        // Submit Quick Add Metode Form
        $('#formQuickAddMetode').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btnSubmitQuickMetode');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: '{{ route("payment-method.store") }}',
                type: 'POST',
                data: form.serialize(),
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Metode');
                    let pm = res.method;

                    if (pm) {
                        let newHtml = `
                            <div class="picker-item" data-id="${pm.id}" data-name="${pm.nama_metode}">
                                <div class="picker-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;"><i class="bi bi-credit-card"></i></div>
                                <div class="picker-meta">
                                    <div class="picker-name">${pm.nama_metode}</div>
                                    <div class="picker-sub">Kode: ${pm.kode_metode} | ${pm.keterangan || '-'}</div>
                                </div>
                                <span class="picker-badge bg-success-subtle text-success">${pm.kode_metode}</span>
                            </div>
                        `;
                        $('#metodeResultsList').prepend(newHtml);

                        if (currentMetodeTarget === 'edit') {
                            $('#edit_payment_method_id').val(pm.id);
                            $('#edit_payment_method_display').val(pm.nama_metode);
                        } else {
                            $('#payment_method_id').val(pm.id);
                            $('#payment_method_display').val(pm.nama_metode);
                        }

                        form[0].reset();
                        let collapseEl = document.getElementById('collapseAddMetodeForm');
                        if (collapseEl) {
                            let bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                            if (bsCollapse) bsCollapse.hide();
                            else $(collapseEl).removeClass('show');
                        }

                        let targetModal = document.getElementById('modalCariMetode');
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
                            title: res.message || 'Metode pembayaran baru berhasil ditambahkan!'
                        });
                    }
                },
                error: function (err) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Metode');
                    let msg = err.responseJSON?.message || 'Gagal menyimpan metode pembayaran baru.';
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

        // Character Limit Warning Alert Handlers for Quick Add Metode
        let metodeAlertShown = false;
        function enforceMetodeLimit(el, maxLen, fieldName) {
            let val = $(el).val();
            if (val.length >= maxLen) {
                if (!metodeAlertShown) {
                    metodeAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: `Input ${fieldName} tidak boleh melebihi ${maxLen} karakter yang telah ditentukan.`,
                        confirmButtonColor: '#198754',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        metodeAlertShown = false;
                    });
                }
            }
        }

        $(document).on('input', '#quick_kode_metode', function () {
            if ($(this).val().length > 15) {
                $(this).val($(this).val().substring(0, 15));
            }
            enforceMetodeLimit(this, 15, 'Kode Metode');
        });

        $(document).on('input', '#quick_nama_metode', function () {
            if ($(this).val().length > 15) {
                $(this).val($(this).val().substring(0, 15));
            }
            enforceMetodeLimit(this, 15, 'Nama Metode');
        });

        $(document).on('input', '#quick_keterangan_metode', function () {
            if ($(this).val().length > 30) {
                $(this).val($(this).val().substring(0, 30));
            }
            enforceMetodeLimit(this, 30, 'Keterangan');
        });

        $(document).on('show.bs.modal', '#modalCariMetode', function () {
            $('#metodeSearchInput').val('');
            $('#metodeResultsList .picker-item').removeClass('d-none');
            $('#metodeResultsList .picker-empty').remove();
        });

        $(document).on('shown.bs.modal', '#modalCariMetode', function () {
            $('#metodeSearchInput').focus();
        });
    });
</script>
@endpush
