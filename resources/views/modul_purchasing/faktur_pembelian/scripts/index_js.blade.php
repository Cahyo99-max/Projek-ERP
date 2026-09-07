<script>
    // Safe HTML Escape Helper
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Number & Currency Formatting Helpers
    function formatMoney(amount, code, symbol) {
        let num = parseFloat(amount) || 0;
        let currCode = code || 'IDR';
        let currSymbol = symbol || (currCode === 'USD' ? '$' : 'Rp');

        if (currCode === 'USD') {
            return currSymbol + ' ' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        } else {
            return currSymbol + ' ' + new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    }

    function formatNumberOnly(amount, code) {
        let num = parseFloat(amount) || 0;
        let currCode = code || 'IDR';

        if (currCode === 'USD') {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        } else {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    }

    function formatSmartKurs(val) {
        let num = parseFloat(val) || 1;
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 4
        }).format(num);
    }

    $(document).ready(function () {
        // 1. DataTables Server-Side List View
        var table = $('#tableInvoice').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('purchase-invoices.index') }}",
            columns: [
                { 
                    data: 'DT_RowIndex', 
                    name: 'DT_RowIndex', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-center text-muted small' 
                },
                { 
                    data: 'nomor_faktur', 
                    name: 'nomor_faktur', 
                    className: 'fw-bold' 
                },
                { 
                    data: 'nomor_faktur_supplier', 
                    name: 'nomor_faktur_supplier', 
                    className: 'fw-semibold' 
                },
                { 
                    data: 'tanggal_faktur_fmt', 
                    name: 'tanggal_faktur' 
                },
                { 
                    data: 'tanggal_jatuh_tempo_fmt', 
                    name: 'tanggal_jatuh_tempo',
                    render: function(data) {
                        return '<span class="fw-semibold text-warning-emphasis">' + (data || '-') + '</span>';
                    }
                },
                { 
                    data: 'supplier_name', 
                    name: 'supplier.nama_supplier', 
                    className: 'fw-semibold' 
                },
                { 
                    data: 'nomor_gr', 
                    name: 'goodsReceipt.nomor_gr',
                    render: function(data) {
                        return data && data !== '-' ? '<span class="text-info fw-semibold font-monospace">' + escapeHtml(data) + '</span>' : '-';
                    }
                },
                { 
                    data: 'grand_total_fmt', 
                    name: 'grand_total', 
                    className: 'text-end fw-bold text-success' 
                },
                { 
                    data: 'status_badge', 
                    name: 'status', 
                    className: 'text-center' 
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-center' 
                }
            ],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nomor faktur / supplier...",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                zeroRecords: "Tidak ada data faktur yang sesuai",
                emptyTable: "Belum ada data faktur pembelian",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>'
                }
            }
        });

        // Dual Horizontal Scroll Sync for Touch Mobile & Desktop Table
        const topScroll = document.getElementById('invTopScrollContainer');
        const topInner = document.getElementById('invTopScrollInner');
        const tableWrapper = document.getElementById('invTableScrollWrapper');
        const tableEl = document.getElementById('tableInvoice');

        function syncTableScrollWidth() {
            if (topInner && tableEl) {
                topInner.style.width = tableEl.scrollWidth + 'px';
            }
        }

        if (topScroll && tableWrapper) {
            topScroll.addEventListener('scroll', function() {
                tableWrapper.scrollLeft = topScroll.scrollLeft;
            });
            tableWrapper.addEventListener('scroll', function() {
                topScroll.scrollLeft = tableWrapper.scrollLeft;
            });
        }

        table.on('draw', function() {
            setTimeout(syncTableScrollWidth, 100);
        });
        window.addEventListener('resize', syncTableScrollWidth);
        setTimeout(syncTableScrollWidth, 250);

        // Datatable Direct Post Action
        $(document).on('click', '.btn-post-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Posting & Terbitkan Jurnal Akuntansi?',
                text: 'Faktur yang diposting akan mengunci tagihan & menerbitkan Jurnal Utang Usaha otomatis.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Post Faktur',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/purchase-invoices/' + id + '/post', { _token: '{{ csrf_token() }}' }, function (res) {
                        table.ajax.reload();
                        Swal.fire('Berhasil!', res.message, 'success');
                    }).fail(function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal memposting faktur.', 'error');
                    });
                }
            });
        });

        // Datatable Delete Action
        $(document).on('click', '.btn-delete-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Faktur Pembelian?',
                text: 'Faktur yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/purchase-invoices/' + id + '/delete',
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            table.ajax.reload();
                            Swal.fire('Berhasil!', res.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus faktur.', 'error');
                        }
                    });
                }
            });
        });

        // Expose table reload helper
        window.reloadInvoiceTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
