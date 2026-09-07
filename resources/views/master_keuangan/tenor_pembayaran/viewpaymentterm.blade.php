<x-layouts title="Master Tenor Pembayaran">

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
        @media (max-width: 768px) {
            .form-control-custom, .form-select { font-size: 16px !important; }
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
        [data-bs-theme="dark"] .form-control-custom::placeholder,
        html[data-theme="dark"] .form-control-custom::placeholder,
        .dark-mode .form-control-custom::placeholder {
            color: #64748b !important;
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
        [data-bs-theme="dark"] .modal-body .text-dark,
        [data-bs-theme="dark"] .modal-body label,
        html[data-theme="dark"] .modal-title,
        html[data-theme="dark"] .modal-body .text-dark,
        .dark-mode .modal-title {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .input-group-text,
        html[data-theme="dark"] .input-group-text,
        .dark-mode .input-group-text {
            background-color: #334155 !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .modal-footer,
        html[data-theme="dark"] .modal-footer,
        .dark-mode .modal-footer {
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .alert-info,
        html[data-theme="dark"] .alert-info,
        .dark-mode .alert-info {
            background-color: rgba(14, 165, 233, 0.15) !important;
            border-color: rgba(14, 165, 233, 0.3) !important;
            color: #38bdf8 !important;
        }
    </style>
    @endpush

    {{-- ===== TABEL UTAMA ===== --}}
    <x-table_card tableId="tablePaymentTerm" title="Master Tenor Pembayaran" icon="bi-calendar-range"
        btnTarget="{{ auth()->user()->can('tambah-payment-term') ? '#modalTambahTerm' : '' }}"
        btnText="Tambah Tenor">

        <x-slot name="extraActions">
            @can('lihat-payment-term')
                <a href="{{ route('payment-term.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-term" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-payment-term')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportTerm">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Nama Tenor</th>
            <th class="py-3">Jumlah Hari</th>
            <th class="py-3">Keterangan</th>
            <th class="py-3 text-center">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>

        @foreach ($terms as $index => $term)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">
                    {{ $term->nama_term }}
                </span>
            </td>
            <td class="fw-bold text-dark">{{ $term->jumlah_hari }} Hari</td>
            <td class="text-secondary">{{ $term->keterangan ?? '-' }}</td>
            <td class="text-center">
                @if($term->status === 'Active')
                    <span class="badge bg-success-soft px-3 py-1.5 rounded-pill fw-semibold">Aktif</span>
                @else
                    <span class="badge bg-danger-soft px-3 py-1.5 rounded-pill fw-semibold">Nonaktif</span>
                @endif
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    @can('edit-payment-term')
                    <button class="btn btn-sm btn-light text-info border btn-edit" data-id="{{ $term->id }}" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    @endcan
                    @can('hapus-payment-term')
                    <button class="btn btn-sm btn-light text-danger border btn-delete" data-id="{{ $term->id }}" data-nama="{{ $term->nama_term }}" title="Hapus">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    @endcan
                </div>
            </td>
        </tr>
        @endforeach
    </x-table_card>

    {{-- ===== INCLUDE MODALS ===== --}}
    @include('master_keuangan.tenor_pembayaran.modal_tambah')
    @include('master_keuangan.tenor_pembayaran.modal_edit')
    @include('master_keuangan.tenor_pembayaran.modal_import')

    @push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $(document).ready(function () {

            // ── Matikan Autocomplete Browser Secara Total ─────────────────────────────
            $(document).on('focus', 'input:not([type="hidden"]), textarea', function() {
                $(this).attr('autocomplete', 'off');
                $(this).attr('autocorrect', 'off');
                $(this).attr('autocapitalize', 'off');
                $(this).attr('spellcheck', 'false');
            });

            // ── DataTables ────────────────────────────────────────────────────
            $('#tablePaymentTerm').DataTable({
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    search: 'Cari:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center py-3">Belum ada data tenor pembayaran.</div>'
                }
            });

            // ── DELETE ────────────────────────────────────────────────────────
            $(document).on('click', '.btn-delete', function () {
                let id   = $(this).data('id');
                let nama = $(this).data('nama');

                Swal.fire({
                    title: 'Hapus data tenor?',
                    html: `Anda akan menghapus tenor: <b>${nama}</b>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/payment-term/' + id + '/delete',
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => { location.reload(); });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data tenor.';
                                if (err.status === 403) {
                                    errorMsg = 'Akses ditolak. Anda tidak memiliki izin.';
                                }
                                Swal.fire('Gagal!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // ── EXPORT EXCEL ──────────────────────────────────────────────────
            $(document).on('click', '.btn-export-term', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');

                Swal.fire({
                    title: 'Mengekspor Data...',
                    text: 'Mohon tunggu sebentar, file Excel sedang disiapkan.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }
                })
                .then(async response => {
                    if (!response.ok) {
                        let errorMsg = 'Terjadi kesalahan sistem saat mengekspor data.';
                        try {
                            const json = await response.json();
                            if (json.message) errorMsg = json.message;
                        } catch (err) {
                            errorMsg = `Gagal mengekspor data (Kode Error: ${response.status}).`;
                        }
                        throw new Error(errorMsg);
                    }

                    let filename = 'data_tenor_pembayaran_' + new Date().toISOString().slice(0,10) + '.xlsx';
                    const contentDisposition = response.headers.get('Content-Disposition');
                    if (contentDisposition) {
                        const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (match && match[1]) {
                            filename = match[1].replace(/['"]/g, '');
                        }
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(downloadUrl);

                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'File Excel Tenor Pembayaran berhasil diunduh.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Ekspor Gagal!',
                        text: error.message || 'Gagal mengekspor data tenor.',
                        icon: 'error'
                    });
                });
            });

        });
    </script>
    @endpush

</x-layouts>
