<x-layouts title="Data Supplier / Vendor">

    @push('styles')
    <style>
        .nav-tabs-custom {
            border-bottom: 2px solid var(--border-color, #e2e8f0);
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            padding: 0.85rem 1.25rem;
            position: relative;
            background: transparent;
            transition: all 0.2s ease;
        }
        .nav-tabs-custom .nav-link:hover {
            color: var(--primary, #2563eb);
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--primary, #2563eb);
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--primary, #2563eb);
            border-radius: 2px;
        }
        .supplier-detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .supplier-detail-val {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main, #1e293b);
        }
        .card-detail-section {
            background-color: var(--card-bg, #f8fafc);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            padding: 1.25rem;
        }

        /* ── DYNAMIC DARK MODE ADAPTABILITY ── */
        [data-bs-theme="dark"],
        html[data-theme="dark"],
        html.dark,
        .dark-mode {
            --sup-card-bg: #1e293b;
            --sup-bg-subtle: #0f172a;
            --sup-text-main: #f8fafc;
            --sup-text-muted: #cbd5e1;
            --sup-border: #334155;
        }

        [data-bs-theme="dark"] .card-detail-section,
        html[data-theme="dark"] .card-detail-section,
        .dark-mode .card-detail-section {
            background-color: var(--sup-card-bg, #1e293b) !important;
            border-color: var(--sup-border, #334155) !important;
            color: var(--sup-text-main, #f8fafc) !important;
        }

        [data-bs-theme="dark"] .supplier-detail-val,
        html[data-theme="dark"] .supplier-detail-val,
        .dark-mode .supplier-detail-val {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .supplier-detail-label,
        html[data-theme="dark"] .supplier-detail-label,
        .dark-mode .supplier-detail-label {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .nav-tabs-custom,
        html[data-theme="dark"] .nav-tabs-custom,
        .dark-mode .nav-tabs-custom {
            border-bottom-color: var(--sup-border, #334155) !important;
        }

        [data-bs-theme="dark"] .nav-tabs-custom .nav-link,
        html[data-theme="dark"] .nav-tabs-custom .nav-link,
        .dark-mode .nav-tabs-custom .nav-link {
            color: var(--sup-text-muted, #cbd5e1) !important;
        }

        [data-bs-theme="dark"] .nav-tabs-custom .nav-link.active,
        html[data-theme="dark"] .nav-tabs-custom .nav-link.active,
        .dark-mode .nav-tabs-custom .nav-link.active {
            color: #60a5fa !important;
        }

        [data-bs-theme="dark"] .modal-content,
        html[data-theme="dark"] .modal-content,
        .dark-mode .modal-content {
            background-color: var(--sup-card-bg, #1e293b) !important;
            color: var(--sup-text-main, #f8fafc) !important;
            border-color: var(--sup-border, #334155) !important;
        }

        [data-bs-theme="dark"] .modal-header,
        html[data-theme="dark"] .modal-header,
        .dark-mode .modal-header {
            background-color: var(--sup-bg-subtle, #0f172a) !important;
            border-bottom-color: var(--sup-border, #334155) !important;
        }

        [data-bs-theme="dark"] .modal-header .text-dark,
        [data-bs-theme="dark"] .modal-body .text-dark,
        html[data-theme="dark"] .text-dark,
        .dark-mode .text-dark {
            color: var(--sup-text-main, #f8fafc) !important;
        }

        [data-bs-theme="dark"] .text-muted,
        [data-bs-theme="dark"] .text-secondary,
        html[data-theme="dark"] .text-muted,
        html[data-theme="dark"] .text-secondary,
        .dark-mode .text-muted,
        .dark-mode .text-secondary {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .bg-light,
        html[data-theme="dark"] .bg-light,
        .dark-mode .bg-light {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .input-group-text,
        html[data-theme="dark"] .input-group-text,
        .dark-mode .input-group-text {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        .sup-bank-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            transition: all 0.2s ease;
        }

        [data-bs-theme="dark"] .sup-bank-item,
        html[data-theme="dark"] .sup-bank-item,
        .dark-mode .sup-bank-item {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .sup-bank-item .text-secondary,
        html[data-theme="dark"] .sup-bank-item .text-secondary,
        .dark-mode .sup-bank-item .text-secondary {
            color: #94a3b8 !important;
        }

        /* ── ACTION BUTTONS EXPORT & IMPORT STYLING ── */
        .btn-sup-export {
            background-color: rgba(16, 124, 65, 0.08);
            border: 1px solid rgba(16, 124, 65, 0.35);
            color: #107c41;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-sup-export:hover {
            background-color: #107c41;
            border-color: #107c41;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 124, 65, 0.3) !important;
        }
        .btn-sup-export:hover i {
            color: #ffffff !important;
        }

        .btn-sup-import {
            background-color: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.35);
            color: #2563eb;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-sup-import:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3) !important;
        }
        .btn-sup-import:hover i {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-sup-export,
        html[data-theme="dark"] .btn-sup-export,
        .dark-mode .btn-sup-export {
            background-color: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.4);
            color: #4ade80;
        }
        [data-bs-theme="dark"] .btn-sup-export:hover,
        html[data-theme="dark"] .btn-sup-export:hover,
        .dark-mode .btn-sup-export:hover {
            background-color: #16a34a;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-sup-import,
        html[data-theme="dark"] .btn-sup-import,
        .dark-mode .btn-sup-import {
            background-color: rgba(96, 165, 250, 0.15);
            border-color: rgba(96, 165, 250, 0.4);
            color: #60a5fa;
        }
        [data-bs-theme="dark"] .btn-sup-import:hover,
        html[data-theme="dark"] .btn-sup-import:hover,
        .dark-mode .btn-sup-import:hover {
            background-color: #2563eb;
            color: #ffffff !important;
        }
    </style>
    @endpush

    <!-- Header Card -->
    <x-table_card tableId="tableSupplier" title="Data Supplier / Vendor" icon="bi-people-fill"
        btnTarget="{{ auth()->user()->can('tambah-supplier') ? '#modalTambahSupplier' : '' }}"
        btnText="Tambah Supplier">

        <x-slot name="extraActions">
            <a href="{{ route('supplier.export') }}" id="btnExportSupplier" class="btn btn-sup-export rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm no-loader" title="Export Excel Supplier">
                <i class="bi bi-file-earmark-excel-fill text-success fs-6"></i>
                <span>Export Excel</span>
            </a>
            @can('tambah-supplier')
            <button type="button" class="btn btn-sup-import rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSupplier" title="Import Excel Supplier">
                <i class="bi bi-file-earmark-arrow-up-fill text-primary fs-6"></i>
                <span>Import Excel</span>
            </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Supplier</th>
            <th class="py-3">Nama Supplier</th>
            <th class="py-3">Kontak PIC</th>
            <th class="py-3">Telepon</th>
            <th class="py-3 text-center">Termin</th>
            <th class="py-3">Info Bank Utama</th>
            <th class="py-3 text-center">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

    </x-table_card>

    {{-- ===== MODALS & THEIR JS (INCLUDED VIA BLADE @push) ===== --}}
    @include('modul_purchasing.supplier.tambah')
    @include('modul_purchasing.supplier.edit')
    @include('modul_purchasing.supplier.detail')
    @include('modul_purchasing.supplier.import')

    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $(document).ready(function () {
            // ===== ALERT FEEDBACK ON EXPORT EXCEL =====
            $('#btnExportSupplier').on('click', function() {
                Swal.fire({
                    title: 'Memproses Export!',
                    text: 'File Excel Data Supplier sedang diunduh.',
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
            });

            // ===== INITIALIZE SERVER-SIDE DATATABLE =====
            $('#tableSupplier').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('supplier.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_badge', name: 'kode_supplier' },
                    { data: 'nama_info', name: 'nama_supplier' },
                    { data: 'kontak_pic', name: 'nama_kontak' },
                    { data: 'telepon_val', name: 'telepon' },
                    { data: 'termin_badge', name: 'term_pembayaran', className: 'text-center' },
                    { data: 'bank_info', name: 'primaryBank.nama_bank', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>Belum ada data supplier.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });
        });

        // Helper to format currency
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        // Security helper to escape HTML entities (XSS protection)
        function escapeHtml(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // ===== DYNAMIC BANK ROWS HELPER =====
        let bankIndex = 0;

        function addBankRow(containerId, prefix, bankData = null) {
            let uniqueId = bankIndex++;
            let namaBank = bankData ? bankData.nama_bank : '';
            let nomorRekening = bankData ? bankData.nomor_rekening : '';
            let atasNama = bankData ? bankData.atas_nama_rekening : '';
            let isPrimary = bankData && bankData.is_primary ? true : false;
            
            // If it is the first row, auto check primary radio
            let isPrimaryChecked = '';
            if (isPrimary || ($(`#${containerId} tr.bank-row`).length === 0 && !bankData)) {
                isPrimaryChecked = 'checked';
            }

            let html = `
                <tr class="bank-row">
                    <td class="ps-2 py-1">
                        <input type="text" name="banks[${uniqueId}][nama_bank]" class="form-control form-control-sm form-control-custom" placeholder="BCA / Mandiri" value="${escapeHtml(namaBank)}" data-max="15" data-label="Nama Bank" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </td>
                    <td class="py-1">
                        <input type="text" name="banks[${uniqueId}][nomor_rekening]" class="form-control form-control-sm form-control-custom font-monospace" placeholder="1234567890" value="${escapeHtml(nomorRekening)}" data-max="15" data-label="Nomor Rekening" data-numeric="true" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </td>
                    <td class="py-1">
                        <input type="text" name="banks[${uniqueId}][atas_nama_rekening]" class="form-control form-control-sm form-control-custom" placeholder="Nama Pemilik" value="${escapeHtml(atasNama)}" data-max="40" data-label="Atas Nama Rekening" autocomplete="new-password" spellcheck="false" autocorrect="off">
                    </td>
                    <td class="text-center py-1">
                        <input type="radio" name="${prefix}_banks_primary" value="${uniqueId}" class="form-check-input bank-primary-radio" ${isPrimaryChecked} style="cursor:pointer;">
                        <input type="hidden" name="banks[${uniqueId}][is_primary]" class="bank-primary-hidden" value="${isPrimaryChecked ? '1' : '0'}">
                    </td>
                    <td class="text-center py-1 pe-2">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-delete-bank-row" title="Hapus Rekening">
                            <i class="bi bi-trash-fill" style="font-size:0.85rem;"></i>
                        </button>
                    </td>
                </tr>
            `;
            $(`#${containerId}`).append(html);
        }

        // Disable browser autocomplete globally for all input/textarea inside modal forms
        $(document).on('show.bs.modal', '.modal', function() {
            $(this).find('input, textarea').attr('autocomplete', 'new-password').attr('spellcheck', 'false').attr('autocorrect', 'off');
        });

        // Prevent Chrome aria-hidden console warning when closing modal
        $(document).on('hide.bs.modal hidden.bs.modal', '.modal', function() {
            if (document.activeElement) {
                document.activeElement.blur();
            }
            $('.main-content, main, #app').removeAttr('aria-hidden');
        });

        // Bind click to add bank row
        $(document).on('click', '.btn-add-bank-row', function() {
            let type = $(this).data('type');
            let containerId = type === 'tambah' ? 'tambah_bank_tbody' : 'edit_bank_tbody';
            addBankRow(containerId, type);
        });

        // Bind click to delete bank row
        $(document).on('click', '.btn-delete-bank-row', function() {
            let tbody = $(this).closest('tbody');
            let radioGroup = tbody.attr('id') === 'tambah_bank_tbody' ? 'tambah_banks_primary' : 'edit_banks_primary';
            $(this).closest('tr').remove();
            
            // If the deleted row was checked, auto-check the first remaining row
            if (tbody.find(`.bank-primary-radio:checked`).length === 0) {
                let firstRadio = tbody.find('.bank-primary-radio').first();
                if (firstRadio.length > 0) {
                    firstRadio.prop('checked', true);
                    firstRadio.closest('tr').find('.bank-primary-hidden').val('1');
                }
            }
        });

        // Sync radio button checks with hidden fields
        $(document).on('change', '.bank-primary-radio', function() {
            let tbody = $(this).closest('tbody');
            tbody.find('.bank-primary-hidden').val('0');
            $(this).closest('tr').find('.bank-primary-hidden').val('1');
        });

        // ===== LIVE INPUT VALIDATION & SWEETALERT WARNING =====
        let isAlerting = false;

        $(document).on('input keyup paste', 'input[data-max], textarea[data-max]', function () {
            let $this = $(this);
            let val = $this.val();
            let maxLen = parseInt($this.data('max'));
            let label = $this.data('label') || 'Input';
            let isNumeric = $this.data('numeric') === true || $this.data('numeric') === 'true';

            // 1. Check numeric restriction
            if (isNumeric && val && /[^0-9]/.test(val)) {
                $this.val(val.replace(/[^0-9]/g, ''));
                if (!isAlerting) {
                    isAlerting = true;
                    Swal.fire({
                        title: 'Format Tidak Valid!',
                        text: `${label} hanya boleh diisi angka!`,
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { isAlerting = false; });
                }
                return;
            }

            // 2. Check max length restriction
            if (val && val.length > maxLen) {
                $this.val(val.substring(0, maxLen));
                if (!isAlerting) {
                    isAlerting = true;
                    Swal.fire({
                        title: 'Batas Karakter Melebihi!',
                        text: `${label} tidak boleh melebihi ${maxLen} ${isNumeric ? 'digit' : 'karakter'}!`,
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { isAlerting = false; });
                }
            }
        });

        // ===== DELETE SUPPLIER =====
        $(document).on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus data supplier: <b>${name}</b>.<br><small class="text-danger">Tindakan ini aman dan tidak akan merusak history transaksi yang sudah ada.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/supplier/" + id + "/delete",
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
                        error: function (err) {
                            let errorMsg = 'Gagal menghapus data supplier.';
                            if (err.responseJSON && err.responseJSON.message) {
                                errorMsg = err.responseJSON.message;
                            }
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    </script>
    @endpush
</x-layouts>
