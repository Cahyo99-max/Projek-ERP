<script>
$(document).ready(function () {
    // ===== CSRF SETUP =====
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ===== DATATABLE INITIALIZATION =====
    let tablePayments = $('#tablePayments').DataTable({
        processing: true,
        serverSide: true,
        scrollX: false,
        responsive: false,
        autoWidth: false,
        ajax: {
            url: "{{ route('purchase-payments.index') }}",
            data: function (d) {
                d.search_custom = $('#filterSearch').val();
                d.supplier_id   = $('#filterSupplierModal').val();
                d.kas_bank_id   = $('#filterKasBankModal').val();
                d.status        = $('#filterStatusModal').val();
                d.start_date    = $('#filterStartDateModal').val();
                d.end_date      = $('#filterEndDateModal').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-nowrap align-middle' },
            { data: 'nomor_pembayaran', name: 'nomor_pembayaran', className: 'text-nowrap align-middle fw-semibold' },
            { data: 'tanggal_bayar_fmt', name: 'tanggal_bayar', className: 'text-nowrap align-middle' },
            { data: 'supplier_name', name: 'supplier.nama_supplier', className: 'text-nowrap align-middle fw-medium' },
            { data: 'kas_bank_name', name: 'kasBank.nama_akun', className: 'text-nowrap align-middle' },
            { data: 'payment_method_name', name: 'paymentMethod.nama_metode', className: 'text-nowrap align-middle' },
            { data: 'total_bayar_fmt', name: 'total_bayar', className: 'text-end text-nowrap align-middle fw-bold font-monospace' },
            { data: 'total_potongan_fmt', name: 'total_potongan', className: 'text-end text-nowrap align-middle font-monospace' },
            { data: 'status_badge', name: 'status', className: 'text-center text-nowrap align-middle' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap align-middle' },
        ],
        dom: "<'w-100 position-relative'tr><'p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 border-top pay-dt-footer'<'small text-muted pay-dt-info'i><'d-flex align-items-center gap-3 pay-dt-controls'lp>>",
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari...",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: `<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 opacity-50 d-block mb-2"></i>Tidak ada data pembayaran utang ditemukan</div>`,
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        },
        order: [],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
    });

    // ===== DUAL SCROLLBAR SYNC (TOP SCROLLBAR & TABLE BODY) =====
    const $topScroll = $('#payTopScrollContainer');
    const $tableScroll = $('.pay-table-scroll-wrapper');
    let isSyncingTop = false;
    let isSyncingTable = false;

    $topScroll.on('scroll', function () {
        if (!isSyncingTop) {
            isSyncingTable = true;
            $tableScroll.scrollLeft($topScroll.scrollLeft());
        }
        isSyncingTop = false;
    });

    $tableScroll.on('scroll', function () {
        if (!isSyncingTable) {
            isSyncingTop = true;
            $topScroll.scrollLeft($tableScroll.scrollLeft());
        }
        isSyncingTable = false;
    });

    function syncScrollWidth() {
        let tableWidth = $('#tablePayments').outerWidth() || 1200;
        $('#payTopScrollDummy').css({
            'width': tableWidth + 'px',
            'min-width': tableWidth + 'px'
        });
    }
    syncScrollWidth();
    tablePayments.on('draw', syncScrollWidth);
    $(window).on('resize', syncScrollWidth);

    // Initialize Select2 in Filter Modal
    $('#modalFilterPayments').on('shown.bs.modal', function () {
        if ($.fn.select2) {
            $('#filterSupplierModal, #filterKasBankModal').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalFilterPayments'),
                width: '100%'
            });
        }
    });

    // Custom search input debounce
    let searchTimeout;
    $('#filterSearch').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            tablePayments.draw();
        }, 350);
    });

    // Apply Filter Button in Modal
    $('#btnApplyFilterModal').on('click', function () {
        $('#modalFilterPayments').modal('hide');
        updateActiveFilterBadge();
        tablePayments.draw();
    });

    // Reset Filter Button inside Modal
    $('#btnResetFilterModal').on('click', function () {
        resetAllFilters();
        $('#modalFilterPayments').modal('hide');
    });

    // Reset Filter Button on Table Header
    $('#btnResetFilters, #btnClearAllFilterChips').on('click', function () {
        resetAllFilters();
    });

    function resetAllFilters() {
        $('#filterSupplierModal').val('all').trigger('change');
        $('#filterKasBankModal').val('all').trigger('change');
        $('#filterStatusModal').val('all');
        $('#filterStartDateModal').val('');
        $('#filterEndDateModal').val('');
        $('#filterSearch').val('');
        updateActiveFilterBadge();
        tablePayments.draw();
    }

    // ===== REAL-TIME 1-SECOND SMART SYNC (ENTERPRISE HIGH-SPEED) =====
    let isUserInteracting = false;
    let isSilentReloading = false;
    let currentDataFingerprint = null;

    $(document).on('mouseenter', '#tablePayments, .pay-table-scroll-wrapper', function () {
        isUserInteracting = true;
    });
    $(document).on('mouseleave', '#tablePayments, .pay-table-scroll-wrapper', function () {
        isUserInteracting = false;
    });
    $(document).on('touchstart', '#tablePayments, .pay-table-scroll-wrapper', function () {
        isUserInteracting = true;
    });
    $(document).on('touchend', '#tablePayments, .pay-table-scroll-wrapper', function () {
        setTimeout(() => { isUserInteracting = false; }, 1200);
    });

    setInterval(function () {
        const isSearching = $('#filterSearch').is(':focus') || $('input:focus').length > 0;
        const isModalOpen = $('.modal.show').length > 0;
        const isSwalOpen  = $('body').hasClass('swal2-shown');
        const pageHidden  = document.hidden;

        if (!pageHidden && !isModalOpen && !isSwalOpen && !isSearching && !isUserInteracting && !isSilentReloading && $.fn.DataTable.isDataTable('#tablePayments')) {
            $.ajax({
                url: "{{ route('purchase-payments.check-sync') }}",
                type: "GET",
                dataType: "json",
                timeout: 850,
                success: function(res) {
                    if (currentDataFingerprint === null) {
                        currentDataFingerprint = res.fingerprint;
                        return;
                    }
                    // Hanya reload tabel jika ada perubahan data / status baru di database
                    if (res.fingerprint && res.fingerprint !== currentDataFingerprint) {
                        currentDataFingerprint = res.fingerprint;
                        isSilentReloading = true;
                        let settings = tablePayments.settings()[0];
                        if (settings) settings.oFeatures.bProcessing = false;
                        tablePayments.ajax.reload(function () {
                            if (settings) settings.oFeatures.bProcessing = true;
                            isSilentReloading = false;
                        }, false);
                    }
                }
            });
        }
    }, 1000);

    function updateActiveFilterBadge() {
        let count = 0;
        let badgesHtml = '';

        let supId = $('#filterSupplierModal').val();
        let supName = $('#filterSupplierModal option:selected').text();
        if (supId && supId !== 'all') {
            count++;
            badgesHtml += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-shop me-1"></i>${supName}</span>`;
        }

        let kbId = $('#filterKasBankModal').val();
        let kbName = $('#filterKasBankModal option:selected').text();
        if (kbId && kbId !== 'all') {
            count++;
            badgesHtml += `<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="bi bi-bank me-1"></i>${kbName}</span>`;
        }

        let status = $('#filterStatusModal').val();
        if (status && status !== 'all') {
            count++;
            badgesHtml += `<span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1"><i class="bi bi-tag me-1"></i>Status: ${status}</span>`;
        }

        let start = $('#filterStartDateModal').val();
        let end = $('#filterEndDateModal').val();
        if (start || end) {
            count++;
            let dateStr = (start || '...') + ' s/d ' + (end || '...');
            badgesHtml += `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1"><i class="bi bi-calendar-event me-1"></i>${dateStr}</span>`;
        }

        if (count > 0) {
            $('#activeFilterBadge').text(count).removeClass('d-none');
            $('#btnResetFilters').removeClass('d-none');
            $('#activeFilterBadgesContainer').html(badgesHtml);
            $('#activeFilterInfoBanner').removeClass('d-none');
        } else {
            $('#activeFilterBadge').addClass('d-none');
            $('#btnResetFilters').addClass('d-none');
            $('#activeFilterInfoBanner').addClass('d-none');
        }
    }

    // Refresh table button
    $('#btnRefreshTable').on('click', function () {
        tablePayments.ajax.reload(null, false);
    });

    // ===== SUBMIT FOR APPROVAL ACTION =====
    $(document).on('click', '.btn-submit-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Konfirmasi Pengajuan Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Apakah Anda hendak mengajukan dokumen pembayaran <strong>${nomor}</strong> untuk proses persetujuan (<em>approval workflow</em>)?</p>
                   <div class="alert alert-light border py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-info-circle-fill text-primary me-1"></i> Notifikasi otomatis akan dikirimkan melalui WhatsApp kepada pihak approver berwenang.
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan Dokumen',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mengajukan Dokumen...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/purchase-payments/${id}/submit`,
                    type: 'POST',
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diajukan',
                            text: res.message,
                            timer: 2200,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengajukan dokumen pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal Mengajukan', text: msg });
                    }
                });
            }
        });
    });

    // ===== APPROVE & POST PAYMENT ACTION =====
    $(document).on('click', '.btn-approve-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Persetujuan &amp; Posting Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Apakah Anda menyetujui dokumen pembayaran <strong>${nomor}</strong>?</p>
                   <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-check-circle-fill text-success me-1"></i> Transaksi akan <strong>langsung diposting</strong>, menerbitkan <strong>Jurnal Akuntansi</strong>, memotong <strong>Kas/Bank</strong>, serta mengirimkan konfirmasi WhatsApp kepada pembuat transaksi.
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Setujui &amp; Posting',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Persetujuan & Posting...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/purchase-payments/${id}/approve`,
                    type: 'POST',
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Disetujui &amp; Diposting',
                            text: res.message,
                            timer: 2200,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal Menyetujui', text: msg });
                    }
                });
            }
        });
    });

    // ===== REJECT PAYMENT ACTION =====
    $(document).on('click', '.btn-reject-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Penolakan Pengajuan Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Mohon cantumkan catatan / alasan penolakan untuk dokumen <strong>${nomor}</strong>:</p>
                   <small class="text-muted d-block mb-2 text-start"><i class="bi bi-info-circle me-1"></i>Pembuat dokumen akan menerima notifikasi WhatsApp dan dapat memperbaiki data untuk pengajuan ulang.</small>`,
            input: 'textarea',
            inputPlaceholder: 'Tuliskan catatan atau alasan penolakan di sini...',
            inputAttributes: {
                'aria-label': 'Tuliskan alasan penolakan'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tolak Pengajuan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            preConfirm: (alasan) => {
                if (!alasan || !alasan.trim()) {
                    Swal.showValidationMessage('Catatan alasan penolakan wajib diisi!');
                }
                return alasan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menolak Pengajuan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/purchase-payments/${id}/reject`,
                    type: 'POST',
                    data: { rejection_reason: result.value },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Ditolak',
                            text: res.message,
                            timer: 2200,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal menolak pengajuan pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal Menolak', text: msg });
                    }
                });
            }
        });
    });

    // ===== POST PAYMENT ACTION (BYPASS / MANUAL) =====
    $(document).on('click', '.btn-post-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Posting Pembayaran Utang?',
            html: `Apakah Anda yakin ingin memposting pembayaran <strong>${nomor}</strong>?<br><br><span class="text-danger small"><i class="bi bi-info-circle me-1"></i>Sistem akan menerbitkan <strong>Jurnal Akuntansi</strong> dan mencatat <strong>Pengeluaran Kas/Bank</strong> secara otomatis.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Posting Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Posting...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/purchase-payments/${id}/post`,
                    type: 'POST',
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diposting!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memposting pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal Posting', text: msg });
                    }
                });
            }
        });
    });

    // ===== CANCEL / VOID PAYMENT ACTION =====
    $(document).on('click', '.btn-cancel-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Batalkan Pembayaran Utang (Void)?',
            html: `Masukkan alasan pembatalan pembayaran <strong>${nomor}</strong>:<br><small class="text-muted">Saldo Kas/Bank akan dikembalikan dan jurnal akuntansi akan dihapus.</small>`,
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan pembatalan...',
            inputAttributes: {
                'aria-label': 'Tuliskan alasan pembatalan'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Batalkan Pembayaran',
            cancelButtonText: 'Tutup',
            reverseButtons: true,
            preConfirm: (alasan) => {
                if (!alasan || !alasan.trim()) {
                    Swal.showValidationMessage('Alasan pembatalan wajib diisi!');
                }
                return alasan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Membatalkan Transaksi...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `/purchase-payments/${id}/cancel`,
                    type: 'POST',
                    data: { alasan_pembatalan: result.value },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Dibatalkan!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal membatalkan pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });

    // ===== DELETE DRAFT PAYMENT ACTION =====
    $(document).on('click', '.btn-delete-payment', function () {
        let id    = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Hapus Draft Pembayaran?',
            html: `Apakah Anda yakin ingin menghapus draft pembayaran <strong>${nomor}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/purchase-payments/${id}/delete`,
                    type: 'DELETE',
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        tablePayments.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal menghapus draft pembayaran!';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });

    // ===== GLOBAL IMAGE PREVIEW / LIGHTBOX MODAL HANDLER =====
    window.openImagePreviewModal = function (imageUrl, title = 'Bukti Pembayaran', subtitle = '') {
        if (!imageUrl) return;
        $('#previewImageTitle').text(title);
        $('#previewImageSubtitle').text(subtitle || (imageUrl.indexOf('/') !== -1 ? imageUrl.split('/').pop() : 'Dokumen Terlampir'));
        $('#previewImageLoadingSpinner').removeClass('d-none');
        $('#previewImageSrc').attr('src', '').addClass('opacity-0');
        
        let img = new Image();
        img.onload = function () {
            $('#previewImageSrc').attr('src', imageUrl).removeClass('opacity-0');
            $('#previewImageLoadingSpinner').addClass('d-none');
        };
        img.onerror = function () {
            $('#previewImageSrc').attr('src', imageUrl).removeClass('opacity-0');
            $('#previewImageLoadingSpinner').addClass('d-none');
        };
        img.src = imageUrl;

        $('#btnDownloadPreviewImage, #btnDownloadPreviewImageMobile').attr('href', imageUrl);
        $('#modalPreviewImage').modal('show');
    };

    // Table Button & Generic Preview Click
    $(document).on('click', '.btn-preview-image', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let url = $(this).data('url');
        let title = $(this).data('title') || 'Foto Bukti Pembayaran';
        let subtitle = $(this).data('subtitle') || '';
        if (url) {
            window.openImagePreviewModal(url, title, subtitle);
        }
    });

    // View Modal Image Preview Trigger
    $(document).on('click', '#viewLampiranClickContainer, #viewLampiranModalBtn', function (e) {
        e.preventDefault();
        let url = $('#viewLampiranImg').attr('src');
        let noBayar = $('#viewNomorPembayaran').text().trim();
        if (url) {
            window.openImagePreviewModal(url, `Bukti Pembayaran ${noBayar}`, 'Dokumen Bukti Transfer / Kas');
        }
    });

    // Create Modal Image Preview Trigger
    $(document).on('click', '#createLampiranImgContainer', function (e) {
        e.preventDefault();
        let url = $('#createLampiranPreviewImg').attr('src');
        let filename = $('#createLampiranFileName').text().trim();
        if (url) {
            window.openImagePreviewModal(url, 'Pratinjau Dokumen Pembayaran', filename);
        }
    });

    // Edit Modal Image Preview Trigger
    $(document).on('click', '#editLampiranImgContainer', function (e) {
        e.preventDefault();
        let url = $('#editLampiranPreviewImg').attr('src');
        let filename = $('#editLampiranFileName').text().trim();
        if (url) {
            window.openImagePreviewModal(url, 'Pratinjau Dokumen Pembayaran', filename);
        }
    });
});
</script>
