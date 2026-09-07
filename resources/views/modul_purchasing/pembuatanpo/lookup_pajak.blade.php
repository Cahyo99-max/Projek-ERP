{{-- ===== MODAL CARI PAJAK ===== --}}
<div class="modal fade picker-modal" id="modalCariPajak" tabindex="-1" data-bs-backdrop="false" style="z-index: 999999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-3 px-3 px-sm-4 pt-3 pt-sm-4 d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2.5 flex-grow-1 min-w-0">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="bi bi-percent fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="modal-title fw-bold mb-0 text-truncate text-dark fs-6">Pilih Pajak (PPN)</h6>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">Cari atau tambah tarif pajak baru</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button class="btn btn-sm btn-warning rounded-pill px-3 py-1.5 text-dark text-nowrap d-inline-flex align-items-center gap-1 shadow-sm fw-semibold" type="button" id="btnToggleAddPajakForm" data-bs-toggle="collapse" data-bs-target="#collapseAddPajakForm" aria-expanded="false" style="font-size: 0.78rem;">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Tambah </span>Pajak
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <!-- Collapsible Form Tambah Pajak Baru -->
                <div class="collapse mb-3" id="collapseAddPajakForm">
                    <div class="card card-body bg-light border-0 rounded-3 shadow-sm p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0 text-warning fs-7"><i class="bi bi-plus-circle me-1"></i> Tambah Tarif Pajak (PPN) Baru</h6>
                            <button type="button" class="btn-close ms-auto fs-8" data-bs-toggle="collapse" data-bs-target="#collapseAddPajakForm"></button>
                        </div>
                        <form id="formQuickAddPajak">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Kode Pajak <span class="text-danger">*</span> (Max 5)</label>
                                    <input type="text" name="kode_pajak" id="quick_kode_pajak" class="form-control form-control-sm bg-white" placeholder="TX001" required maxlength="5" style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Nama Pajak <span class="text-danger">*</span> (Max 15)</label>
                                    <input type="text" name="nama_pajak" id="quick_nama_pajak" class="form-control form-control-sm" placeholder="Contoh: PPN 11%" required maxlength="15">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Tarif (%) <span class="text-danger">*</span> (Max 10 digit)</label>
                                    <input type="number" step="any" min="0" max="100" name="tarif" id="quick_tarif_pajak" class="form-control form-control-sm" placeholder="Contoh: 11" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Keterangan (Opsional) (Max 20)</label>
                                    <input type="text" name="keterangan" id="quick_keterangan_pajak" class="form-control form-control-sm" placeholder="Catatan tarif pajak..." maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="Active" selected>Active (Aktif)</option>
                                        <option value="Inactive">Inactive (Nonaktif)</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-toggle="collapse" data-bs-target="#collapseAddPajakForm">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-warning px-3 fw-semibold text-white" id="btnSubmitQuickPajak"><i class="bi bi-save me-1"></i> Simpan Pajak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="picker-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="pajakSearchInput" class="form-control picker-search-input" placeholder="Cari nama atau tarif pajak...">
                </div>
                <div id="pajakResultsList" class="picker-results-list">
                    <!-- Non-PPN Option -->
                    <div class="picker-item" data-id="" data-tarif="0" data-name="Non-PPN (0%)">
                        <div class="picker-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;"><i class="bi bi-x-circle"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">Non-PPN (0%)</div>
                            <div class="picker-sub">Tanpa Pajak Pertambahan Nilai</div>
                        </div>
                        <span class="picker-badge bg-secondary-subtle text-secondary">0%</span>
                    </div>
                    @foreach($pajaks as $pj)
                        <div class="picker-item" data-id="{{ $pj->id }}" data-tarif="{{ $pj->tarif }}" data-name="{{ $pj->nama_pajak }} ({{ number_format($pj->tarif, 0) }}%)">
                            <div class="picker-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="bi bi-percent"></i></div>
                            <div class="picker-meta">
                                <div class="picker-name">{{ $pj->nama_pajak }} ({{ number_format($pj->tarif, 0) }}%)</div>
                                <div class="picker-sub">Kode: {{ $pj->kode_pajak }} | Tarif: {{ number_format($pj->tarif, 0) }}%</div>
                            </div>
                            <span class="picker-badge bg-warning-subtle text-warning">{{ number_format($pj->tarif, 0) }}%</span>
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
        let currentPajakTarget = 'create';

        // Open Trigger Delegated
        $(document).on('click', '#pajak_display, #btnOpenPajakPicker, #edit_pajak_display, #btnOpenEditPajakPicker', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).attr('id') || '';
            currentPajakTarget = (id.includes('edit') || id.includes('Edit')) ? 'edit' : 'create';
            let targetModal = document.getElementById('modalCariPajak');
            if (targetModal) {
                if (targetModal.parentNode !== document.body) {
                    document.body.appendChild(targetModal);
                }
                bootstrap.Modal.getOrCreateInstance(targetModal, { backdrop: false, keyboard: true }).show();
            }
        });

        // Select Pajak Item Delegated
        $(document).on('click', '#pajakResultsList .picker-item', function () {
            let id = $(this).data('id') !== undefined ? $(this).data('id') : '';
            let name = $(this).data('name') || 'Non-PPN (0%)';
            let tarif = parseFloat($(this).data('tarif')) || 0;

            if (currentPajakTarget === 'edit') {
                if (id && $('#edit_pajak_id option[value="' + id + '"]').length === 0) {
                    $('#edit_pajak_id').append($('<option>', {
                        value: id,
                        text: name,
                        'data-tarif': tarif
                    }));
                }
                $('#edit_pajak_id').val(id).trigger('change');
                $('#edit_pajak_display').val(name);
            } else {
                if (id && $('#pajak_id option[value="' + id + '"]').length === 0) {
                    $('#pajak_id').append($('<option>', {
                        value: id,
                        text: name,
                        'data-tarif': tarif
                    }));
                }
                $('#pajak_id').val(id).trigger('change');
                $('#pajak_display').val(name);
            }

            let targetModal = document.getElementById('modalCariPajak');
            if (targetModal) {
                let m = bootstrap.Modal.getInstance(targetModal);
                if (m) m.hide();
            }
        });

        // Close on background click
        $(document).on('click', '#modalCariPajak', function (e) {
            if ($(e.target).hasClass('modal')) {
                let m = bootstrap.Modal.getInstance(this);
                if (m) m.hide();
            }
        });

        // Local Search Input
        $(document).on('input', '#pajakSearchInput', function () {
            let query = $(this).val().toLowerCase().trim();
            let items = $('#pajakResultsList .picker-item');
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

            let emptyEl = $('#pajakResultsList .picker-empty');
            if (!found) {
                if (emptyEl.length === 0) {
                    $('#pajakResultsList').append('<div class="picker-empty"><i class="bi bi-percent"></i>Pajak tidak ditemukan</div>');
                }
            } else {
                emptyEl.remove();
            }
        });

        // Auto generate code when opening collapse add form
        $('#btnToggleAddPajakForm').on('click', function () {
            if (!$('#collapseAddPajakForm').hasClass('show')) {
                $.ajax({
                    url: '{{ route("pajak.generate-code") }}',
                    type: 'GET',
                    success: function (res) {
                        if (res.kode) {
                            $('#quick_kode_pajak').val(res.kode);
                        }
                    }
                });
            }
        });

        // Submit Quick Add Pajak Form
        $('#formQuickAddPajak').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btnSubmitQuickPajak');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: '{{ route("pajak.store") }}',
                type: 'POST',
                data: form.serialize(),
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pajak');
                    let pj = res.pajak;

                    if (pj) {
                        let tarifFormatted = parseFloat(pj.tarif) || 0;
                        let dispName = `${pj.nama_pajak} (${tarifFormatted}%)`;
                        let newHtml = `
                            <div class="picker-item" data-id="${pj.id}" data-tarif="${pj.tarif}" data-name="${dispName}">
                                <div class="picker-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="bi bi-percent"></i></div>
                                <div class="picker-meta">
                                    <div class="picker-name">${dispName}</div>
                                    <div class="picker-sub">Kode: ${pj.kode_pajak} | Tarif: ${tarifFormatted}%</div>
                                </div>
                                <span class="picker-badge bg-warning-subtle text-warning">${tarifFormatted}%</span>
                            </div>
                        `;
                        $('#pajakResultsList').prepend(newHtml);

                        // Auto select the new pajak for PO form
                        if (currentPajakTarget === 'edit') {
                            if ($('#edit_pajak_id option[value="' + pj.id + '"]').length === 0) {
                                $('#edit_pajak_id').append($('<option>', {
                                    value: pj.id,
                                    text: dispName,
                                    'data-tarif': pj.tarif
                                }));
                            }
                            $('#edit_pajak_id').val(pj.id).trigger('change');
                            $('#edit_pajak_display').val(dispName);
                        } else {
                            if ($('#pajak_id option[value="' + pj.id + '"]').length === 0) {
                                $('#pajak_id').append($('<option>', {
                                    value: pj.id,
                                    text: dispName,
                                    'data-tarif': pj.tarif
                                }));
                            }
                            $('#pajak_id').val(pj.id).trigger('change');
                            $('#pajak_display').val(dispName);
                        }

                        // Reset & hide collapse form
                        form[0].reset();
                        let collapseEl = document.getElementById('collapseAddPajakForm');
                        if (collapseEl) {
                            let bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                            if (bsCollapse) bsCollapse.hide();
                            else $(collapseEl).removeClass('show');
                        }

                        // Hide modal
                        let targetModal = document.getElementById('modalCariPajak');
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
                            title: res.message || 'Pajak baru berhasil ditambahkan!'
                        });
                    }
                },
                error: function (err) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pajak');
                    let msg = err.responseJSON?.message || 'Gagal menyimpan data pajak.';
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

        // Character Limit Warning Alert Handlers for Quick Add Pajak
        let pajakAlertShown = false;
        function enforcePajakLimit(el, maxLen, fieldName) {
            let val = $(el).val();
            if (val.length >= maxLen) {
                if (!pajakAlertShown) {
                    pajakAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: `Input ${fieldName} tidak boleh melebihi ${maxLen} karakter yang telah ditentukan.`,
                        confirmButtonColor: '#f59e0b',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        pajakAlertShown = false;
                    });
                }
            }
        }

        $(document).on('input', '#quick_kode_pajak', function () {
            if ($(this).val().length > 5) {
                $(this).val($(this).val().substring(0, 5));
            }
            enforcePajakLimit(this, 5, 'Kode Pajak');
        });

        $(document).on('input', '#quick_nama_pajak', function () {
            if ($(this).val().length > 15) {
                $(this).val($(this).val().substring(0, 15));
            }
            enforcePajakLimit(this, 15, 'Nama Pajak');
        });

        $(document).on('input', '#quick_keterangan_pajak', function () {
            if ($(this).val().length > 20) {
                $(this).val($(this).val().substring(0, 20));
            }
            enforcePajakLimit(this, 20, 'Keterangan');
        });

        $(document).on('show.bs.modal', '#modalCariPajak', function () {
            $('#pajakSearchInput').val('');
            $('#pajakResultsList .picker-item').removeClass('d-none');
            $('#pajakResultsList .picker-empty').remove();
        });

        $(document).on('shown.bs.modal', '#modalCariPajak', function () {
            $('#pajakSearchInput').focus();
        });
    });
</script>
@endpush
