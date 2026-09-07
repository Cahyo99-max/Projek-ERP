{{-- ===== MODAL LOOKUP USER ===== --}}
<div class="modal fade" id="modalLookupUser" tabindex="-1" aria-hidden="true" style="z-index: 1090;" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-search me-2"></i> Pilih Pembuat PR (User)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="tableLookupUser" style="width: 100%;">
                        <thead>
                            <tr class="bg-light text-muted small" style="font-size: 0.75rem;">
                                <th>Nama Lengkap</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $usr)
                                <tr>
                                    <td class="fw-semibold">{{ $usr->nama_lengkap }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-select-user"
                                            data-id="{{ $usr->id }}"
                                            data-nama="{{ $usr->nama_lengkap }}">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== INITIALIZE LOOKUP USER DATATABLE =====
        const tableLookupUser = $('#tableLookupUser').DataTable({
            dom: '<"row"<"col-sm-12"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ user',
                paginate: { previous: '<', next: '>' }
            },
            pageLength: 5
        });

        $(document).on('click', '.btn-lookup-user', function() {
            currentUserLookupTarget = $(this).data('type');
            $('#modalLookupUser').modal('show');
            // Adjust columns since table is inside modal
            setTimeout(() => {
                tableLookupUser.columns.adjust().draw();
            }, 200);
        });

        $(document).on('click', '.btn-select-user', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            if (currentUserLookupTarget === 'tambah') {
                $('#tambah_dibuat_oleh').val(id);
                $('#tambah_dibuat_oleh_nama').val(nama);
            } else if (currentUserLookupTarget === 'edit') {
                $('#edit_dibuat_oleh').val(id);
                $('#edit_dibuat_oleh_nama').val(nama);
            }

            $('#modalLookupUser').modal('hide');
        });

        $('#modalLookupUser').on('hidden.bs.modal', function () {
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });
    });
</script>
@endpush
