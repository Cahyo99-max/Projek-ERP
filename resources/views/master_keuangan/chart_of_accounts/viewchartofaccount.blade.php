<x-layouts title="Chart of Accounts (COA)">

    @push('styles')
    <style>
        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.55rem 0.9rem;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* Filter Pills Styling */
        .filter-pill-btn {
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        /* ── Dark Mode Adaptations ────────────────────────────────────────────── */
        [data-bs-theme="dark"] .form-control-custom,
        html[data-theme="dark"] .form-control-custom,
        .dark-mode .form-control-custom {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .form-control-custom:focus,
        html[data-theme="dark"] .form-control-custom:focus,
        .dark-mode .form-control-custom:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }
        [data-bs-theme="dark"] .modal-content,
        html[data-theme="dark"] .modal-content,
        .dark-mode .modal-content {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-header.bg-light,
        html[data-theme="dark"] .modal-header,
        .dark-mode .modal-header {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .modal-title,
        [data-bs-theme="dark"] .modal-body label,
        [data-bs-theme="dark"] .modal-body .text-dark,
        html[data-theme="dark"] .modal-title,
        .dark-mode .modal-title {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .modal-footer,
        html[data-theme="dark"] .modal-footer,
        .dark-mode .modal-footer {
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .btn-close,
        html[data-theme="dark"] .btn-close,
        .dark-mode .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
    </style>
    @endpush

    {{-- ===== TABEL UTAMA COA ===== --}}
    <x-table_card tableId="tableCoaView" title="Chart of Accounts (COA)" icon="bi-journal-bookmark-fill"
        btnTarget="#modalTambahCoa"
        btnText="Tambah Akun COA">

        <x-slot name="extraActions">
            {{-- Filter Group Pills --}}
            <div class="d-flex flex-wrap gap-2 me-2">
                <a href="{{ route('chart-of-accounts.index') }}" class="btn {{ !$typeFilter ? 'btn-primary' : 'btn-outline-secondary' }} filter-pill-btn shadow-sm" style="font-size: 0.8rem;">
                    <i class="bi bi-grid-fill me-1"></i> Semua Akun
                </a>
                @foreach($accountTypes as $t)
                    <a href="{{ route('chart-of-accounts.index', ['type' => $t->id]) }}" class="btn {{ $typeFilter == $t->id ? 'btn-primary' : 'btn-outline-primary' }} filter-pill-btn" style="font-size: 0.8rem;">
                        {{ $t->nama_tipe }}
                    </a>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="thead">
            <th class="py-3 text-center" width="5%">No</th>
            <th class="py-3" style="width: 120px;">Kode Akun</th>
            <th class="py-3">Nama Akun</th>
            <th class="py-3">Tipe</th>
            <th class="py-3">Akun Induk (Parent)</th>
            <th class="py-3 text-center">Kategori</th>
            <th class="py-3 text-center" style="width: 100px;">Status</th>
            <th class="py-3 text-center" style="width: 100px;">Aksi</th>
        </x-slot>

        @foreach($accounts as $index => $acc)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill font-monospace">
                    {{ $acc->code }}
                </span>
            </td>
            <td class="fw-semibold text-dark">
                @if($acc->parent_id)
                    <span class="text-muted me-2">&rarr;</span>
                @endif
                <span class="{{ $acc->is_header ? 'fw-bold text-primary' : '' }}">
                    {{ $acc->name }}
                </span>
            </td>
            <td>
                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill">
                    {{ $acc->type_label }}
                </span>
            </td>
            <td class="text-secondary small">
                {{ $acc->parent ? $acc->parent->code . ' - ' . $acc->parent->name : '-' }}
            </td>
            <td class="text-center">
                @if($acc->is_header)
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold">Header (Induk)</span>
                @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill fw-semibold">Detail</span>
                @endif
            </td>
            <td class="text-center">
                @if($acc->is_active)
                    <span class="badge bg-success-soft px-3 py-1.5 rounded-pill fw-semibold">Aktif</span>
                @else
                    <span class="badge bg-danger-soft px-3 py-1.5 rounded-pill fw-semibold">Nonaktif</span>
                @endif
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-light text-info border btn-edit" data-id="{{ $acc->id }}" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger border btn-delete" data-id="{{ $acc->id }}" title="Hapus">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach

    </x-table_card>

    {{-- MODAL COMPONENT INCLUDES --}}
    @include('master_keuangan.chart_of_accounts.modal_tambah')
    @include('master_keuangan.chart_of_accounts.modal_edit')
    @include('master_keuangan.chart_of_accounts.modal_import')

    @push('scripts')
    <script>
        $(document).ready(function () {
            // Matikan Autocomplete Browser Secara Total
            $(document).on('focus', 'input:not([type="hidden"]), textarea', function() {
                $(this).attr('autocomplete', 'off');
                $(this).attr('autocorrect', 'off');
                $(this).attr('autocapitalize', 'off');
                $(this).attr('spellcheck', 'false');
            });

            // DataTables Initialization
            if ($('#tableCoaView').length) {
                $('#tableCoaView').DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        emptyTable: "Belum ada data Akun COA.",
                        zeroRecords: "Data tidak ditemukan.",
                        paginate: {
                            previous: "<",
                            next: ">"
                        }
                    },
                    ordering: false,
                    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>'
                });
            }

            // Menyisipkan Tombol Export & Import Excel tepat di samping Kotak Pencarian DataTables
            let btnExportImportGroup = `
                <div class="d-inline-flex align-items-center gap-1.5 ms-2">
                    <a href="{{ route('chart-of-accounts.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-inline-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold no-loader btn-export-coa" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        <span>Export</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportCoa">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i>
                        <span>Import</span>
                    </button>
                </div>
            `;
            $('#tableCoaView_filter').addClass('d-flex align-items-center justify-content-end gap-1').append(btnExportImportGroup);

            // Toast Peringatan Export Excel
            $(document).on('click', '.btn-export-coa', function() {
                Swal.fire({
                    title: 'Mengunduh Excel...',
                    text: 'File data Akun COA sedang disiapkan.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            });

            // Delete Handler via AJAX
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Akun COA?',
                    text: 'Akun ini akan dihapus jika tidak memiliki anak akun / transaksi.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/finance/chart-of-accounts/${id}`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON?.message || 'Gagal menghapus data.';
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
