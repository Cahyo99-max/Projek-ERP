<!-- MODAL CARI BARANG (DATATABLE POPUP) -->
<div class="modal fade" id="modalCariBarang" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center justify-content-between w-100 me-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                            <i class="bi bi-boxes fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Pilih Barang / Jasa</h5>
                            <small class="text-muted">Cari barang berdasarkan nama, kode, tipe, atau kategori</small>
                        </div>
                    </div>
                    <button type="button" class="btn-add-master-luxury btn btn-success" id="btn-quick-add-barang" title="Tambah Master Barang Baru">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline ms-1">Tambah Master Barang</span>
                    </button>
                </div>
                <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="table-responsive">
                    <table class="table align-middle table-modern" id="tablePopupCariBarang" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="15%">Kode</th>
                                <th width="35%">Nama Barang</th>
                                <th width="20%">Brand</th>
                                <th width="15%">Tipe Item</th>
                                <th width="10%">Satuan</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic JS injection -->
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
        // ===== POPUP BARANG DATATABLE INITIALIZATION =====
        $('#tablePopupCariBarang').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 150,   // Wait 150ms after user stops typing before sending request
            search: { return: false }, // Allow real-time search (not just on Enter)
            ajax: {
                url: '/purchase-requisition/search-barang',
                data: function(d) {
                    d.tipe_item = currentSearchTipe;
                }
            },
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            columns: [
                { data: 'kode' },
                { data: 'nama' },
                { data: 'brand' },
                { data: 'tipe_item' },
                { data: 'satuan' },
                { data: 'id', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function(data, type, row) {
                        return `<span class="fw-bold font-monospace">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    targets: 1,
                    render: function(data, type, row) {
                        let warnaBadge = '';
                        if (row.warna_nama) {
                            warnaBadge = `
                                <span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center" style="font-size:0.65rem;font-weight:500; margin-left:4px;">
                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:${row.warna_hex || '#e2e8f0'};margin-right:4px;border:1px solid rgba(0,0,0,0.15);"></span>
                                    ${escapeHtml(row.warna_nama)}
                                </span>
                            `;
                        }
                        let spesifikasiHtml = '';
                        if (row.spesifikasi) {
                            spesifikasiHtml = `<div class="text-muted small mt-0.5" style="font-size: 0.72rem; line-height: 1.3;"><i class="bi bi-info-circle me-1"></i>${escapeHtml(row.spesifikasi)}</div>`;
                        }
                        return `<div><span class="fw-semibold text-dark">${escapeHtml(data)}</span>${warnaBadge}${spesifikasiHtml}</div>`;
                    }
                },
                {
                    targets: 2,
                    render: function(data, type, row) {
                        return `<span class="text-secondary fw-semibold">${escapeHtml(data || '-')}</span>`;
                    }
                },
                {
                    targets: 3,
                    render: function(data, type, row) {
                        let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
                        if (data === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
                        else if (data === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';
                        return `<span class="badge small px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; ${bg}">${data.toUpperCase()}</span>`;
                    }
                },
                {
                    targets: 4,
                    render: function(data, type, row) {
                        return `<span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.8rem;">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    targets: 5,
                    render: function(data, type, row) {
                        return `<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-pilih-barang-row">Pilih</button>`;
                    }
                }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-id', data.id)
                      .attr('data-kode', data.kode)
                      .attr('data-nama', data.nama)
                      .attr('data-satuan', data.satuan)
                      .attr('data-satuan-id', data.satuan_id || '')
                      .attr('data-satuan-nama', data.satuan_nama || data.satuan || '')
                      .attr('data-tipe', data.tipe_item)
                      .attr('data-foto', data.foto)
                      .attr('data-semua-foto', data.semua_foto)
                      .attr('data-warna-nama', data.warna_nama)
                      .attr('data-warna-hex', data.warna_hex)
                      .attr('data-spesifikasi', data.spesifikasi || '');
                $(row).data('konversis', data.konversis || []);
            },
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                emptyTable: "Tidak ada barang tersedia",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Mengambil data...'
            }
        });

        // ===== CLICK TO SHOW IMAGES CAROUSEL MODAL =====
        $(document).on('click', '.img-thumbnail-preview', function (e) {
            e.stopPropagation();
            let el = $(this);
            let tr = el.closest('tr');
            
            let nama = el.data('nama') || el.attr('data-nama') || tr.data('nama') || tr.attr('data-nama') || 'Foto Barang';
            let semuaFotoStr = el.data('semua-foto') || el.attr('data-semua-foto') || tr.data('semua-foto') || tr.attr('data-semua-foto') || el.data('foto') || el.attr('data-foto') || tr.data('foto') || tr.attr('data-foto') || '';
            let fotos = semuaFotoStr.split(',').filter(f => f.trim() !== '');

            if (fotos.length === 0) {
                Swal.fire({
                    title: 'Tidak Ada Foto',
                    text: 'Barang ini tidak memiliki foto.',
                    icon: 'info',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            let indicatorsHtml = '';
            let slidesHtml = '';

            fotos.forEach((fotoUrl, index) => {
                indicatorsHtml += `
                    <button type="button" data-bs-target="#carouselSwalPreview" data-bs-slide-to="${index}" class="${index === 0 ? 'active' : ''}" aria-current="${index === 0 ? 'true' : 'false'}" aria-label="Slide ${index + 1}" style="background-color: #ffffff; width: 8px; height: 8px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.5);"></button>
                `;
                slidesHtml += `
                    <div class="carousel-item ${index === 0 ? 'active' : ''}">
                        <img src="${fotoUrl}" class="d-block w-100 rounded" style="height: 320px; object-fit: contain; background-color: #0f172a;" alt="Foto Barang ${index + 1}">
                    </div>
                `;
            });

            Swal.fire({
                title: nama,
                html: `
                    <div id="carouselSwalPreview" class="carousel slide mt-2" data-bs-ride="false">
                        ${fotos.length > 1 ? `
                        <div class="carousel-indicators mb-2" style="z-index: 10;">
                            ${indicatorsHtml}
                        </div>
                        ` : ''}
                        <div class="carousel-inner rounded-3" style="background-color: #0f172a;">
                            ${slidesHtml}
                        </div>
                        ${fotos.length > 1 ? `
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselSwalPreview" data-bs-slide="prev" style="z-index: 11; width: 10%;">
                            <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));"></span>
                            <span class="visually-hidden">Sebelumnya</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselSwalPreview" data-bs-slide="next" style="z-index: 11; width: 10%;">
                            <span class="carousel-control-next-icon" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));"></span>
                            <span class="visually-hidden">Selanjutnya</span>
                        </button>
                        ` : ''}
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'border-0 shadow-lg rounded-4 overflow-hidden',
                    title: 'pt-3 pb-0 px-4 fs-6 fw-bold'
                }
            });
        });

        // ===== TRIGGER SEARCH BARANG MODAL =====
        $(document).on('click', '.btn-search-barang-modal, .label-product-name', function () {
            currentSelectorRow = $(this).closest('tr');
            currentSearchTipe = currentSelectorRow.find('.select-tipe-input').val() || 'Stok';
            
            let popupTable = $('#tablePopupCariBarang').DataTable();
            popupTable.draw();
            
            $('#modalCariBarang').modal('show');
        });

        $(document).on('click', '.btn-pilih-barang-row', function () {
            if (!currentSelectorRow) return;

            let row = $(this).closest('tr');
            let id = row.data('id');
            let kode = row.data('kode');
            let nama = row.data('nama');
            let satuan = row.data('satuan');
            let satuanId = row.attr('data-satuan-id') || row.data('satuan-id') || '';
            let satuanNama = row.attr('data-satuan-nama') || row.data('satuan-nama') || satuan;
            let tipe = row.data('tipe');
            let foto = row.data('foto');
            let spesifikasi = row.attr('data-spesifikasi') || row.data('spesifikasi') || '';
            let konversis = row.data('konversis') || [];

            // Fill values
            currentSelectorRow.find('.select-product-input').val(id);
            currentSelectorRow.find('.label-product-name').val(`[${kode}] ${nama}`);

            // Multi-Satuan selector setup
            let satuanContainer = currentSelectorRow.find('.satuan-cell-container');
            if (konversis && konversis.length > 1) {
                let optionsHtml = '';
                konversis.forEach(k => {
                    let sel = (k.satuan_id == satuanId || k.is_base) ? 'selected' : '';
                    let optText = k.label || (k.nama_satuan || k.singkatan);
                    optionsHtml += `<option value="${k.satuan_id}" data-faktor="${k.faktor_pengali}" data-is-base="${k.is_base ? 1 : 0}" data-nama="${escapeHtml(k.nama_satuan || k.singkatan)}" ${sel}>${escapeHtml(optText)}</option>`;
                });
                satuanContainer.html(`
                    <select class="form-select form-select-sm select-satuan-input" style="min-width: 120px; font-size: 0.78rem; border-radius: 6px; border-color: #cbd5e1;">
                        ${optionsHtml}
                    </select>
                    <input type="hidden" class="input-faktor-pengali" value="1">
                    <input type="hidden" class="input-base-satuan-nama" value="${escapeHtml(satuanNama)}">
                `);
            } else {
                satuanContainer.html(`
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill fw-semibold label-satuan-name">${escapeHtml(satuan) || '-'}</span>
                    <input type="hidden" class="select-satuan-input" value="${satuanId}">
                    <input type="hidden" class="input-faktor-pengali" value="1">
                    <input type="hidden" class="input-base-satuan-nama" value="${escapeHtml(satuanNama)}">
                `);
            }

            // Set photo
            let imgPreview = currentSelectorRow.find('.product-img-preview');
            let semuaFoto = row.attr('data-semua-foto') || row.data('semua-foto') || foto || '';
            if (foto) {
                imgPreview.addClass('img-thumbnail-preview cursor-pointer')
                    .attr('data-nama', nama)
                    .attr('data-semua-foto', semuaFoto)
                    .attr('title', 'Klik untuk memperbesar foto')
                    .html(`<img src="${foto}" style="width: 100%; height: 100%; object-fit: cover;">`);
            } else {
                imgPreview.removeClass('img-thumbnail-preview cursor-pointer')
                    .removeAttr('data-nama')
                    .removeAttr('data-semua-foto')
                    .removeAttr('title')
                    .html(`<i class="bi bi-image text-muted" style="font-size: 1.1rem;"></i>`);
            }

            // Set type badge
            let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
            if (tipe === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
            else if (tipe === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';
            
            let badge = currentSelectorRow.find('.label-tipe-item');
            badge.removeClass('d-none').attr('style', `font-size: 0.65rem; ${bg}`).text(tipe.toUpperCase());

            // Set warna badge
            let warnaNama = row.attr('data-warna-nama') || '';
            let warnaHex = row.attr('data-warna-hex') || '';
            let warnaBadge = currentSelectorRow.find('.label-warna-badge');
            if (warnaNama) {
                warnaBadge.removeClass('d-none')
                    .attr('style', 'font-size:0.75rem;font-weight:600; margin-left: 4px;')
                    .html(`<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${warnaHex || '#e2e8f0'};margin-right:6px;border:1px solid rgba(0,0,0,0.15);"></span> ${escapeHtml(warnaNama)}`);
            } else {
                warnaBadge.addClass('d-none').html('');
            }

            // Set spesifikasi
            let specLabel = currentSelectorRow.find('.label-spesifikasi');
            if (spesifikasi) {
                specLabel.removeClass('d-none').show().html(`<i class="bi bi-info-circle me-1"></i><span>${escapeHtml(spesifikasi)}</span>`);
            } else {
                specLabel.addClass('d-none').hide().html('');
            }

            if (typeof window.updateRowConversionHelper === 'function') {
                window.updateRowConversionHelper(currentSelectorRow);
            }

            // Recalculate if values change
            let qty = parseFloat(currentSelectorRow.find('.input-jumlah').val()) || 0;
            let price = unformatNumber(currentSelectorRow.find('.input-harga-formatted').val());
            currentSelectorRow.find('.pr-row-subtotal').text(formatRupiah(qty * price));
            
            let tbodyId = currentSelectorRow.closest('tbody').attr('id');
            calculateGrandTotal(tbodyId);

            $('#modalCariBarang').modal('hide');
            currentSelectorRow = null;
        });
    });
</script>
@endpush
