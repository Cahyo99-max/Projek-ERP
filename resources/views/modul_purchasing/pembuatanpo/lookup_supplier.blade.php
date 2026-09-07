{{-- ===== MODAL CARI SUPPLIER ===== --}}
<div class="modal fade picker-modal" id="modalCariSupplier" tabindex="-1" data-bs-backdrop="false" style="z-index: 99999 !important; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Cari Supplier</h5>
                        <small class="text-muted">Ketik nama, kode, atau kota supplier</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnClearSupplier" class="picker-clear-btn d-none">
                        <i class="bi bi-x-circle me-1"></i>Hapus Pilihan
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body px-4 pb-4 pt-3 position-relative" style="min-height: 400px;">
                
                <!-- PANEL 1: CARI SUPPLIER -->
                <div id="panelCariSupplier">
                    <div class="d-flex gap-2 mb-3">
                        <div class="picker-search-wrap flex-grow-1">
                            <i class="bi bi-search"></i>
                            <input type="text" id="supplierSearchInput" class="form-control picker-search-input" placeholder="Ketik untuk mencari supplier...">
                        </div>
                        <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center rounded-3 shadow-sm px-3" id="btnToggleQuickAddSupplier" title="Tambah Supplier Baru" style="white-space: nowrap;">
                            <i class="bi bi-plus-lg me-1"></i> Baru
                        </button>
                    </div>
                    <div id="supplierResultsList" class="picker-results-list">
                        <div class="picker-loading">
                            <i class="bi bi-arrow-repeat spin-icon"></i>
                            Memuat data supplier...
                        </div>
                    </div>
                </div>

                <!-- PANEL 2: QUICK ADD SUPPLIER (LENGKAP SAMA SEPERTI MODUL SUPPLIER) -->
                <div id="panelQuickAddSupplier" class="d-none">
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                        <button type="button" class="btn btn-light btn-sm rounded-circle me-3" id="btnBackToCariSupplier" title="Kembali ke Pencarian">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                        <div>
                            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-building-add me-2"></i>Tambah Supplier / Vendor Baru</h6>
                            <small class="text-muted">Lengkapi data supplier. Data akan otomatis terpilih setelah disimpan.</small>
                        </div>
                    </div>

                    <form id="formQuickAddSupplier" autocomplete="off" novalidate>
                        @csrf
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="quickSupplierTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="qs-umum-tab" data-bs-toggle="tab" data-bs-target="#qs-umum" type="button" role="tab" aria-controls="qs-umum" aria-selected="true">
                                    <i class="bi bi-info-circle me-1.5"></i> Informasi Umum
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="qs-alamat-tab" data-bs-toggle="tab" data-bs-target="#qs-alamat" type="button" role="tab" aria-controls="qs-alamat" aria-selected="false">
                                    <i class="bi bi-geo-alt me-1.5"></i> Alamat & Pajak
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="qs-keuangan-tab" data-bs-toggle="tab" data-bs-target="#qs-keuangan" type="button" role="tab" aria-controls="qs-keuangan" aria-selected="false">
                                    <i class="bi bi-wallet2 me-1.5"></i> Keuangan & Bank
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="quickSupplierTabsContent">
                            <!-- Tab 1: Informasi Umum -->
                            <div class="tab-pane fade show active" id="qs-umum" role="tabpanel" aria-labelledby="qs-umum-tab">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold small">Kode Supplier (Max 10)</label>
                                        <input type="text" name="kode_supplier" class="form-control form-control-sm" placeholder="SPL-XXXX (Otomatis jika kosong)" data-max="10" data-label="Kode Supplier" maxlength="10">
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label fw-semibold small">Nama Supplier <span class="text-danger">*</span> (Max 40)</label>
                                        <input type="text" name="nama_supplier" class="form-control form-control-sm" placeholder="Contoh: PT. Sumber Makmur" data-max="40" data-label="Nama Supplier" required maxlength="40">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold small">Nama Kontak (PIC) (Max 30)</label>
                                        <input type="text" name="nama_kontak" class="form-control form-control-sm" placeholder="Nama PIC Penanggung Jawab" data-max="30" data-label="Nama Kontak" maxlength="30">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold small">Telepon (Max 13 digit)</label>
                                        <input type="text" name="telepon" class="form-control form-control-sm" placeholder="No Telepon (Hanya Angka)" data-max="13" data-label="Telepon" data-numeric="true" maxlength="13">
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label fw-semibold small">Email (Max 40)</label>
                                        <input type="email" name="email" class="form-control form-control-sm" placeholder="alamat@email.com" data-max="40" data-label="Email" maxlength="40">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold small">Status Keaktifan <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="Active" selected>Aktif</option>
                                            <option value="Inactive">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Alamat & Pajak -->
                            <div class="tab-pane fade" id="qs-alamat" role="tabpanel" aria-labelledby="qs-alamat-tab">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Nomor NPWP (Max 20 digit)</label>
                                        <input type="text" name="npwp" class="form-control form-control-sm" placeholder="Nomor NPWP (Hanya Angka)" data-max="20" data-label="Nomor NPWP" data-numeric="true" maxlength="20">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Alamat Lengkap (Max 60)</label>
                                        <textarea name="alamat" rows="2" class="form-control form-control-sm" placeholder="Nama Jalan, RT/RW, Blok, Kecamatan..." data-max="60" data-label="Alamat Lengkap" maxlength="60"></textarea>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold small">Kota / Kabupaten (Max 20)</label>
                                        <input type="text" name="kota" class="form-control form-control-sm" placeholder="Contoh: Jakarta Barat" data-max="20" data-label="Kota" maxlength="20">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold small">Provinsi (Max 20)</label>
                                        <input type="text" name="provinsi" class="form-control form-control-sm" placeholder="Contoh: DKI Jakarta" data-max="20" data-label="Provinsi" maxlength="20">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold small">Kode Pos (Max 10 digit)</label>
                                        <input type="text" name="kode_pos" class="form-control form-control-sm" placeholder="12345 (Hanya Angka)" data-max="10" data-label="Kode Pos" data-numeric="true" maxlength="10">
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Keuangan & Bank -->
                            <div class="tab-pane fade" id="qs-keuangan" role="tabpanel" aria-labelledby="qs-keuangan-tab">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold small">Termin Pembayaran (Hari) (Max 5 digit)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="term_pembayaran" class="form-control" placeholder="0 jika tunai / COD" data-max="5" data-label="Termin Pembayaran" data-numeric="true" value="0" maxlength="5">
                                            <span class="input-group-text bg-light text-muted fw-semibold">Hari</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold small">Batas Limit Kredit (Max 20 digit)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted fw-semibold">Rp</span>
                                            <input type="text" name="limit_kredit" class="form-control" placeholder="Batas utang maksimal" data-max="20" data-label="Batas Limit Kredit" data-numeric="true" value="0" maxlength="20">
                                        </div>
                                    </div>
                                    
                                    <!-- Dynamic Bank Accounts Section -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
                                            <label class="form-label fw-bold mb-0 text-primary small">
                                                <i class="bi bi-credit-card-2-front me-1"></i> Daftar Rekening Bank Penerima (Bisa Multi)
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0.5 btn-add-quick-bank-row">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah Rekening
                                            </button>
                                        </div>
                                        <div class="table-responsive border rounded-3 p-1 bg-white">
                                            <table class="table table-sm table-borderless align-middle mb-0" id="quick_bank_table">
                                                <thead>
                                                    <tr class="bg-light text-muted small border-bottom" style="font-size: 0.75rem;">
                                                        <th width="30%" class="ps-2 py-1.5">Nama Bank</th>
                                                        <th width="35%">Nomor Rekening</th>
                                                        <th width="26%">Atas Nama</th>
                                                        <th width="5%" class="text-center">Utama</th>
                                                        <th width="4%" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="quick_bank_tbody">
                                                    <!-- Bank rows injected dynamically -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                            <button type="button" class="btn btn-light border rounded-pill px-4" id="btnCancelQuickAdd">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSaveQuickSupplier">
                                <i class="bi bi-save me-1"></i> Simpan Supplier
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        const SEARCH_URL = '{{ route('po.search-supplier') }}';
        let debounceTimer = null;
        let lastQuery = null;

        function formatName(raw) {
            if (!raw) return '';
            return raw.replace(/^Cv\b/i, 'CV').replace(/^Pt\b/i, 'PT').replace(/\b\w/g, c => c.toUpperCase());
        }

        function renderSuppliers(items) {
            let resultsList = $('#supplierResultsList');
            if (!items || !items.length) {
                resultsList.html('<div class="picker-empty"><i class="bi bi-building-slash"></i>Supplier tidak ditemukan</div>');
                return;
            }
            let html = items.map(s => {
                const name = formatName(s.nama_supplier || '');
                const code = s.kode_supplier ? `<span class="badge bg-secondary-subtle text-secondary me-1">${s.kode_supplier}</span>` : '';
                const city = s.kota ? `<i class="bi bi-geo-alt me-1"></i>${s.kota}` : '';
                const phone = s.telepon ? ` &nbsp;·&nbsp; <i class="bi bi-telephone me-1"></i>${s.telepon}` : '';
                const status = s.status === 'Active'
                    ? `<span class="picker-badge bg-success-subtle text-success">Aktif</span>`
                    : `<span class="picker-badge bg-danger-subtle text-danger">Non-Aktif</span>`;
                return `
                    <div class="picker-item" data-id="${s.id}" data-name="${name}">
                        <div class="picker-icon"><i class="bi bi-building"></i></div>
                        <div class="picker-meta">
                            <div class="picker-name">${code}${name}</div>
                            <div class="picker-sub">${city}${phone}</div>
                        </div>
                        ${status}
                    </div>`;
            }).join('');

            resultsList.html(html);
        }

        function fetchSuppliers(q) {
            $('#supplierResultsList').html('<div class="picker-loading"><i class="bi bi-arrow-repeat spin-icon"></i>Mencari data supplier...</div>');
            $.ajax({
                url: SEARCH_URL,
                data: { q: q },
                dataType: 'json',
                success: function(data) {
                    renderSuppliers(data);
                },
                error: function() {
                    $('#supplierResultsList').html('<div class="picker-empty"><i class="bi bi-exclamation-triangle"></i>Gagal memuat data supplier</div>');
                }
            });
        }

        // Open Trigger Delegated — Always opens modal reliably
        $(document).on('click', '#supplier_display, #btnOpenSupplierPicker, #edit_supplier_display, #btnOpenEditSupplierPicker', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).attr('id') || '';
            window.supplierTargetPrefix = (id.includes('edit_') || id.includes('Edit')) ? 'edit_' : '';
            let modalEl = document.getElementById('modalCariSupplier');
            if (modalEl) {
                if (modalEl.parentNode !== document.body) {
                    document.body.appendChild(modalEl);
                }
                let m = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: false, keyboard: true });
                m.show();
            }
        });

        // Select Supplier Item Delegated
        $(document).on('click', '#supplierResultsList .picker-item', function () {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let prefix = window.supplierTargetPrefix || '';

            if (prefix === 'edit_') {
                $('#edit_supplier_id').val(id).trigger('change').trigger('input');
                $('#edit_supplier_display').val(name).trigger('change').trigger('input');
                $('#edit_btn_save_draft_po, #edit_btn_save_po').prop('disabled', false);
            } else {
                $('#supplier_id').val(id).trigger('change').trigger('input');
                $('#supplier_display').val(name).trigger('change').trigger('input');
                $('#btn_save_draft_po, #btn_save_po').prop('disabled', false);
            }

            let targetModal = document.getElementById('modalCariSupplier');
            if (targetModal) {
                let m = bootstrap.Modal.getInstance(targetModal);
                if (m) m.hide();
            }
        });

        // Search Input Delegated
        $(document).on('input', '#supplierSearchInput', function () {
            clearTimeout(debounceTimer);
            let q = $(this).val().trim();
            debounceTimer = setTimeout(() => {
                if (q === lastQuery) return;
                lastQuery = q;
                fetchSuppliers(q);
            }, 280);
        });

        // Modal Events Delegated
        $(document).on('show.bs.modal', '#modalCariSupplier', function () {
            $('#supplierSearchInput').val('');
            lastQuery = null;
            showCariPanel();
        });

        $(document).on('shown.bs.modal', '#modalCariSupplier', function () {
            $('#supplierSearchInput').focus();
            fetchSuppliers('');
        });

        // Quick Add Panel Toggles
        function showQuickAddPanel() {
            $('#panelCariSupplier').addClass('d-none');
            $('#panelQuickAddSupplier').removeClass('d-none').addClass('fade-in');
            if ($('#quick_bank_tbody tr').length === 0) {
                addQuickBankRow();
            }
        }

        function showCariPanel() {
            $('#panelQuickAddSupplier').addClass('d-none');
            $('#panelCariSupplier').removeClass('d-none').addClass('fade-in');
        }

        $(document).on('click', '#btnToggleQuickAddSupplier', showQuickAddPanel);
        $(document).on('click', '#btnBackToCariSupplier, #btnCancelQuickAdd', showCariPanel);

        $(document).on('hidden.bs.modal', '#modalCariSupplier', function () {
            showCariPanel();
            let form = document.getElementById('formQuickAddSupplier');
            if (form) form.reset();
            $('#quick_bank_tbody').empty();
            quickBankCounter = 0;
        });

        // Dynamic Bank Row Handler
        let quickBankCounter = 0;
        function addQuickBankRow(bank = {}) {
            quickBankCounter++;
            let index = quickBankCounter;
            let isPrimaryChecked = (index === 1 || bank.is_primary) ? 'checked' : '';
            let html = `
                <tr class="quick-bank-row" data-index="${index}">
                    <td class="ps-2 py-1">
                        <input type="text" name="banks[${index}][nama_bank]" class="form-control form-control-sm" placeholder="BCA/Mandiri" value="${bank.nama_bank || ''}" data-max="15" data-label="Nama Bank" maxlength="15">
                    </td>
                    <td class="py-1">
                        <input type="text" name="banks[${index}][nomor_rekening]" class="form-control form-control-sm" placeholder="1234567890 (Hanya Angka)" value="${bank.nomor_rekening || ''}" data-max="15" data-label="Nomor Rekening" data-numeric="true" maxlength="15">
                    </td>
                    <td class="py-1">
                        <input type="text" name="banks[${index}][atas_nama_rekening]" class="form-control form-control-sm" placeholder="Atas Nama" value="${bank.atas_nama_rekening || ''}" data-max="40" data-label="Atas Nama" maxlength="40">
                    </td>
                    <td class="text-center py-1">
                        <input type="radio" name="quick_bank_primary_radio" value="${index}" class="form-check-input quick-bank-primary-radio" ${isPrimaryChecked}>
                        <input type="hidden" name="banks[${index}][is_primary]" class="quick-bank-is-primary-hidden" value="${isPrimaryChecked ? '1' : '0'}">
                    </td>
                    <td class="text-center py-1">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-quick-bank"><i class="bi bi-trash fs-6"></i></button>
                    </td>
                </tr>
            `;
            $('#quick_bank_tbody').append(html);
        }

        $(document).on('click', '.btn-add-quick-bank-row', function () {
            addQuickBankRow();
        });

        $(document).on('click', '.btn-remove-quick-bank', function () {
            $(this).closest('tr').remove();
            if ($('#quick_bank_tbody tr').length === 0) {
                addQuickBankRow();
            }
        });

        $(document).on('change', '.quick-bank-primary-radio', function () {
            $('.quick-bank-is-primary-hidden').val('0');
            $(this).siblings('.quick-bank-is-primary-hidden').val('1');
        });

        // Real-Time Character Limit & Numeric Restriction Alert Handler
        let supplierAlertShown = false;
        $(document).on('input', '#formQuickAddSupplier [data-max]', function () {
            let maxLen = parseInt($(this).data('max')) || 255;
            let label = $(this).data('label') || 'Field';
            let isNumeric = $(this).data('numeric') === true || $(this).data('numeric') === 'true';

            let val = $(this).val();
            if (isNumeric) {
                val = val.replace(/[^0-9]/g, '');
            }

            if (val.length >= maxLen) {
                val = val.substring(0, maxLen);
                if (!supplierAlertShown) {
                    supplierAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: `Input ${label} tidak boleh melebihi ${maxLen} ${isNumeric ? 'digit' : 'karakter'} yang telah ditentukan.`,
                        confirmButtonColor: '#0d6efd',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        supplierAlertShown = false;
                    });
                }
            }
            $(this).val(val);
        });

        // Submit Quick Add Supplier Form
        $(document).on('submit', '#formQuickAddSupplier', function (e) {
            e.preventDefault();

            let namaSupplierInput = $('input[name="nama_supplier"]', this);
            let namaSupplierVal = (namaSupplierInput.val() || '').trim();

            if (!namaSupplierVal) {
                let tabTrigger = new bootstrap.Tab(document.getElementById('qs-umum-tab'));
                tabTrigger.show();
                namaSupplierInput.addClass('is-invalid').focus();

                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Supplier Wajib Diisi!',
                    text: 'Silakan isi nama supplier terlebih dahulu.',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                });
                return false;
            }
            namaSupplierInput.removeClass('is-invalid');

            let submitBtn = $('#btnSaveQuickSupplier');
            let originalHtml = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            let csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]', this).val();
            let data = $(this).serialize();

            $.ajax({
                url: '{{ route('supplier.store') }}',
                type: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (res) {
                    submitBtn.prop('disabled', false).html(originalHtml);
                    let prefix = window.supplierTargetPrefix || '';

                    if (res.supplier && res.supplier.id) {
                        if (prefix === 'edit_') {
                            $('#edit_supplier_id').val(res.supplier.id).trigger('change').trigger('input');
                            $('#edit_supplier_display').val(formatName(res.supplier.nama_supplier)).trigger('change').trigger('input');
                            $('#edit_btn_save_draft_po, #edit_btn_save_po').prop('disabled', false);
                        } else {
                            $('#supplier_id').val(res.supplier.id).trigger('change').trigger('input');
                            $('#supplier_display').val(formatName(res.supplier.nama_supplier)).trigger('change').trigger('input');
                            $('#btn_save_draft_po, #btn_save_po').prop('disabled', false);
                        }
                    }

                    let modalEl = document.getElementById('modalCariSupplier');
                    if (modalEl) {
                        let m = bootstrap.Modal.getInstance(modalEl);
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
                        title: 'Supplier baru berhasil ditambahkan!'
                    });

                    let form = document.getElementById('formQuickAddSupplier');
                    if (form) form.reset();
                    showCariPanel();
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalHtml);
                    let body = xhr.responseJSON || {};
                    let errorMsg = body.message || 'Terjadi kesalahan saat menyimpan data.';
                    if (body.errors) {
                        errorMsg = Object.values(body.errors).map(err => err.join(', ')).join('<br>');
                    }
                    Swal.fire({
                        title: 'Gagal Menyimpan',
                        html: errorMsg,
                        icon: 'error',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
