<x-layouts title="Data Warna Barang">

    @push('styles')
    <style>
        #tableWarna tbody td { vertical-align: middle; }
        .warna-nama { font-weight: 600; color: var(--bs-body-color); }
        .warna-desk { color: var(--bs-secondary-color, #6c757d); font-size: .875rem;
                      max-width: 240px; white-space: nowrap; overflow: hidden;
                      text-overflow: ellipsis; display: block; }
        .warna-swatch { display: inline-block; width: 24px; height: 24px;
                        border-radius: 50%; border: 2px solid rgba(128,128,128,0.3);
                        vertical-align: middle; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
        .warna-hex { font-family: monospace; font-size: .82rem; font-weight: 600;
                     color: var(--bs-body-color); }
    </style>
    @endpush

    <x-table_card tableId="tableWarna" title="Data Warna Barang" icon="bi-palette"
        btnTarget="{{ auth()->user()->can('tambah-warna') ? '#modalTambahWarna' : '' }}"
        btnText="Tambah Warna">

        <x-slot name="extraActions">
            @can('lihat-warna')
                <a href="{{ route('warna.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-warna" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-warna')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportWarna">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3">Kode Warna</th>
            <th class="py-3 text-center" width="7%">Visual</th>
            <th class="py-3">Nama Warna</th>
            <th class="py-3">Kode HEX</th>
            <th class="py-3">Deskripsi</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th class="py-3 text-center" width="12%">Aksi</th>
        </x-slot>
    </x-table_card>

    @include('data_master.warna_barang.tambah')
    @include('data_master.warna_barang.edit')
    @include('data_master.warna_barang.import')

    @push('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' } });

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        let tableWarna;

        $(document).ready(function () {
            tableWarna = $('#tableWarna').DataTable({
                processing: true, serverSide: true,
                ajax: { url: "{{ route('warna.data') }}", type: 'GET' },
                columns: [
                    { data: 'no',         orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_warna', orderable: true,  searchable: true },
                    { data: 'kode_hex',   orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_warna', orderable: true,  searchable: true },
                    { data: 'kode_hex',   orderable: false, searchable: true, name: 'kode_hex_text' },
                    { data: 'deskripsi',  orderable: false, searchable: true },
                    { data: 'status',     orderable: true,  searchable: false, className: 'text-center' },
                    { data: null,         orderable: false, searchable: false, className: 'text-center' },
                ],
                columnDefs: [
                    { targets: 1, render: d => '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">' + escapeHtml(d) + '</span>' },
                    { targets: 2, render: (d, t, row) => '<span class="warna-swatch" style="background-color:' + escapeHtml(row.kode_hex) + '" title="' + escapeHtml(row.kode_hex) + '"></span>' },
                    { targets: 3, render: d => '<span class="warna-nama">' + escapeHtml(d) + '</span>' },
                    { targets: 4, render: d => '<span class="warna-hex">' + escapeHtml(d) + '</span>' },
                    { targets: 5, render: d => d ? '<span class="warna-desk" title="' + escapeHtml(d) + '">' + escapeHtml(d) + '</span>' : '<span style="color:#6c757d">—</span>' },
                    { targets: 6, render: d => d === 'Active'
                        ? '<span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>'
                        : '<span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>' },
                    { targets: 7, render: (d, t, row) => {
                        let b = '<div class="d-flex gap-1 justify-content-center">';
                        @can('edit-warna') b += '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" data-id="' + row.id + '" title="Edit"><i class="bi bi-pencil"></i></button>'; @endcan
                        @can('hapus-warna') b += '<button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-hapus" data-id="' + row.id + '" data-nama="' + escapeHtml(row.nama_warna) + '" title="Hapus"><i class="bi bi-trash"></i></button>'; @endcan
                        return b + '</div>';
                    }}
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: { lengthMenu: 'Show _MENU_ entries', info: 'Showing _START_ to _END_ of _TOTAL_ entries', search: 'Search:',
                    paginate: { previous: '<', next: '>' }, zeroRecords: 'Tidak ditemukan data yang cocok',
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-palette fs-2 d-block mb-2 opacity-25"></i>Belum ada data warna.</div>',
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat...' },
                pageLength: 10, lengthMenu: [5,10,25,50,100], order: [[3,'asc']]
            });

            // Debounce search input to prevent keyup lag and database spamming (crucial for 1M+ records)
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this, args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }

            $('.dataTables_filter input')
                .unbind()
                .bind('input', debounce(function () {
                    tableWarna.search(this.value).draw();
                }, 400));

            // Delete action
            $(document).on('click', '.btn-hapus', function() {
                let id = parseInt($(this).data('id')), nama = $(this).data('nama'); if(!id) return;
                Swal.fire({ title:'Hapus Warna?', html:'Warna <strong>'+escapeHtml(nama)+'</strong> akan dihapus.<br><small class="text-muted">Warna yang digunakan oleh varian barang tidak dapat dihapus.</small>',
                    icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', cancelButtonColor:'#6c757d',
                    confirmButtonText:'<i class="bi bi-trash me-1"></i> Ya, Hapus!', cancelButtonText:'Batal', reverseButtons:true
                }).then(r => { if(r.isConfirmed) $.ajax({ url:"{{ url('/warna') }}/"+id+"/delete", type:'DELETE',
                    success: res => Swal.fire({title:'Terhapus!',text:res.message,icon:'success',timer:1500,showConfirmButton:false}).then(()=>tableWarna.ajax.reload(null,false)),
                    error: err => { let m='Gagal hapus.'; if(err.status===422&&err.responseJSON?.message)m=escapeHtml(err.responseJSON.message); else if(err.responseJSON?.message)m=escapeHtml(err.responseJSON.message); Swal.fire({title:'Gagal!',text:m,icon:'error'}); }
                }); });
            });

            // ===== EXPORT EXCEL ALERT =====
            $(document).on('click', '.btn-export-warna', function () {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                Swal.fire({
                    title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Ekspor Berhasil!</span>',
                    html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Data sedang diproses. File Excel Anda akan terunduh secara otomatis secara background.</div>',
                    icon: 'success',
                    iconColor: '#10b981',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#1e293b',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
