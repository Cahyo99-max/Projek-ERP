// ===== INITIALIZE FILEPOND FOR EDIT =====
const pondEdit = FilePond.create(document.querySelector('#edit_foto_input'), {
    allowMultiple: true,
    maxFiles: MAX_FOTO,
    maxTotalFileSize: '10MB',
    acceptedFileTypes: ['image/*'],
    labelIdle: `<i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 1.5rem; display: block; margin-bottom: 2px;"></i>Seret foto ke sini atau <span class="filepond--label-action">Pilih File</span>`,
    labelMaxFileSizeExceeded: 'File terlalu besar',
    labelMaxTotalFileSizeExceeded: `Maksimal hanya ${MAX_FOTO} foto`,
    onwarning: (error) => {
        if (error.body === 'Max files') {
            Swal.fire({ title: 'Batas Foto!', text: `Maksimal hanya ${MAX_FOTO} foto yang bisa ditambahkan.`, icon: 'warning', confirmButtonText: 'OK' });
        }
    },
    allowPaste: true,
    allowDrop: true,
    allowReorder: true,
});

// ===== AI BACKGROUND REMOVAL LOGIC FOR EDIT =====
let temporaryAiBgFilesEdit = [];

$('#btn_edit_remove_bg').on('click', async function () {
    const files = pondEdit.getFiles();
    if (files.length === 0) {
        Swal.fire({ title: 'Kosong!', text: 'Silakan masukkan minimal 1 foto terlebih dahulu.', icon: 'warning' });
        return;
    }

    if (!window._executeStudioAiRemoveBg && !window._imglyRemoveBg) {
        Swal.fire({ title: 'Menyiapkan AI', text: 'AI Studio Hapus Background sedang menyiapkan model produk, harap tunggu beberapa detik lalu coba lagi.', icon: 'info' });
        return;
    }

    $('#btn_edit_remove_bg').prop('disabled', true);
    $('#edit_bg_status').show();
    $('#edit_bg_progress_bar').css('width', '0%');
    $('#edit_bg_counter_text').text('0%');
    $('#preview_bg_container').empty();
    temporaryAiBgFilesEdit = [];

    try {
        const total = files.length;
        for (let i = 0; i < total; i++) {
            const currentStep = i + 1;
            const startPercent = Math.round((i / total) * 100);
            $('#edit_bg_progress_bar').css('width', startPercent + '%');
            $('#edit_bg_counter_text').text(`${startPercent}%`);
            $('#edit_bg_status_text').text(`[AI Studio] Memproses objek foto ${currentStep} dari ${total}...`);

            const originalBlob = files[i].file;
            
            // Execute BRIA RMBG-1.4 state-of-the-art product background removal
            const resultBlob = await window._executeStudioAiRemoveBg(originalBlob, (p) => {
                if (p.total) {
                    const dlPercent = Math.round((p.loaded / p.total) * 100);
                    $('#edit_bg_status_text').text(`[AI Studio] Mengunduh neural weights (${dlPercent}%)...`);
                }
            });
            
            const safeName = (originalBlob.name || 'foto').replace(/\.[^.]+$/, '') + '_nobg.png';
            const newFile = new File([resultBlob], safeName, { type: 'image/png' });
            temporaryAiBgFilesEdit.push({ file: newFile });
            
            const endPercent = Math.round((currentStep / total) * 100);
            $('#edit_bg_progress_bar').css('width', endPercent + '%');
            $('#edit_bg_counter_text').text(`${endPercent}%`);
            $('#edit_bg_status_text').text(`[AI Studio] Selesai foto ${currentStep} dari ${total}!`);

            const html = `
                <div class="col-12 col-md-6 mb-2">
                    <div class="card border border-light-subtle shadow-sm">
                        <div class="card-body p-2 d-flex">
                            <div class="w-50 pe-2 border-end text-center d-flex flex-column align-items-center justify-content-center">
                                <small class="d-block mb-1 text-muted fw-semibold">Asli (Foto ${currentStep})</small>
                                <img src="${URL.createObjectURL(originalBlob)}" class="img-fluid rounded img-bg-preview" style="max-height:140px; object-fit:contain; cursor: zoom-in;" title="Klik untuk memperbesar">
                            </div>
                            <div class="w-50 ps-2 text-center d-flex flex-column align-items-center justify-content-center" style="background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAMUlEQVQ4T2NkYNgfQEhD/oMBQ1Q1jDAagPGBkQEBo//Hh4gDDEYDMDAgMIyGgYECAwAAd4kH0X42a78AAAAASUVORK5CYII=');">
                                <small class="d-block mb-1 text-success fw-bold bg-white bg-opacity-90 px-2 py-0.5 rounded shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Hasil AI Foto ${currentStep}</small>
                                <img src="${URL.createObjectURL(newFile)}" class="img-fluid rounded img-bg-preview" style="max-height:140px; object-fit:contain; cursor: zoom-in;" title="Klik untuk memperbesar">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#preview_bg_container').append(html);
        }

        $('#edit_bg_status').hide();
        
        // Bind Apply Action
        $('#btn_apply_bg_removal').off('click').on('click', function () {
            pondEdit.removeFiles(); 
            temporaryAiBgFilesEdit.forEach(item => {
                pondEdit.addFile(item.file);
            });
            $('#modalPreviewBG').modal('hide');
        });

        $('#modalPreviewBG').modal('show');
        
        // Bind click event for zooming images using simple lightbox
        $('.img-bg-preview').off('click').on('click', function() {
            $('#lightboxImg').attr('src', $(this).attr('src'));
            $('#simpleLightbox').css('display', 'flex').hide().fadeIn('fast');
        });
        
        $('#closeLightbox').off('click').on('click', function() {
            $('#simpleLightbox').fadeOut('fast');
        });
        
        $('#simpleLightbox').off('click').on('click', function(e) {
            if (e.target === this) {
                $(this).fadeOut('fast');
            }
        });
    } catch (err) {
        $('#edit_bg_status').hide();
        Swal.fire({ title: 'Proses AI Gagal', text: err.message || err, icon: 'error' });
    } finally {
        $('#btn_edit_remove_bg').prop('disabled', false);
    }
});

// ===== EDIT (SHOW MODAL) =====
$(document).on('click', '.btn-edit', function () {
    let id = $(this).data('id');
    $.get('/barang/' + id + '/edit', function (res) {
        let b = res.barang;
        $('#edit_barang_id').val(b.id);
        $('#edit_kode_barang').val(b.kode_barang);
        $('#edit_nama_barang').val(b.nama_barang);
        $('#edit_nama_alias').val(b.nama_alias);
        $('#edit_kategori_barang_id').val(b.kategori_barang_id);
        if (b.kategori) {
            let kCode = b.kategori.kode_kategori || 'KAT-' + b.kategori.id;
            $('#edit_kategori_display').val(`[${kCode}] ${b.kategori.nama_kategori}`);
        } else {
            $('#edit_kategori_display').val('');
        }
        $('#edit_brand_id').val(b.brand_id ? b.brand_id : '').trigger('change.select2');
        $('#edit_tipe_barang_id').val(b.tipe_barang_id).trigger('change.select2');
        $('#edit_satuan_id').val(b.satuan_id).trigger('change.select2');
        $('#edit_warna_id').val(b.warna_id ? b.warna_id : '');
        $('#edit_ukuran').val(b.ukuran && b.ukuran !== '-' ? b.ukuran : '');
        $('#edit_berat').val(b.berat ? parseFloat(b.berat) : '');
        $('#edit_panjang').val(b.panjang ? parseFloat(b.panjang) : '');
        $('#edit_lebar').val(b.lebar ? parseFloat(b.lebar) : '');
        $('#edit_tinggi').val(b.tinggi ? parseFloat(b.tinggi) : '');
        $('#edit_minimum_stok').val(b.minimum_stok ? parseFloat(b.minimum_stok) : 0);
        $('#edit_display_hpp_referensi').text(b.hpp_referensi > 0 ? 'Rp ' + Number(b.hpp_referensi).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : 'Belum ada data HPP');
        $('#edit_harga_jual_retail').val(b.harga_jual_retail > 0 ? Math.round(b.harga_jual_retail).toLocaleString('id-ID') : '');
        $('#edit_harga_jual_grosir').val(b.harga_jual_grosir > 0 ? Math.round(b.harga_jual_grosir).toLocaleString('id-ID') : '');
        $('#edit_harga_jual_tender').val(b.harga_jual_tender > 0 ? Math.round(b.harga_jual_tender).toLocaleString('id-ID') : '');
        $('#edit_spesifikasi').val(b.spesifikasi);
        $('#edit_status').val(b.status);
        $('#edit_tipe_pelacakan').val(b.tipe_pelacakan || 'None');
        $('#edit_tipe_item').val(b.tipe_item).trigger('change');

        // Reset & Populate Konversi Satuan rows
        $('#tbody_edit_konversi').empty();
        editKonversiIndex = 0;
        if (b.konversi_satuan && b.konversi_satuan.length > 0) {
            b.konversi_satuan.forEach(konv => {
                addKonversiRow('edit', konv);
            });
        } else {
            $('#tbody_edit_konversi').html(`
                <tr class="empty-konversi-row">
                    <td colspan="4" class="text-center text-muted py-3 small">
                        <i class="bi bi-info-circle me-1"></i> Belum ada satuan konversi alternatif. Klik tombol <b>Tambah Satuan Alternatif</b> jika barang memiliki satuan kemasan/grosir (misal: Roll, Dus, Bal).
                    </td>
                </tr>
            `);
        }

        // Reset varian rows
        $('#tbody_edit_varian').empty();
        editVarianIndex = 0;

        // Populate Varian Checkbox & Rows
        if (b.variants && b.variants.length > 0) {
            $('#edit_has_variants').prop('checked', true).trigger('change');
            b.variants.forEach(variant => {
                addVarianRow('edit', variant);
            });
        } else {
            $('#edit_has_variants').prop('checked', false).trigger('change');
        }

        // Clear file uploader
        pondEdit.removeFiles();

        // Render existing fotos
        let galleryHtml = '';
        if (b.fotos && b.fotos.length > 0) {
            b.fotos.forEach(foto => {
                let primaryBadge = foto.is_primary ? '<span class="primary-badge">Cover</span>' : '';
                galleryHtml += `
                    <div class="photo-gallery-item" id="foto-item-${foto.id}" draggable="true">
                        <img src="/storage/${foto.foto_path}" alt="Foto">
                        ${primaryBadge}
                        <div class="btn-actions">
                            ${!foto.is_primary ? `<button type="button" class="btn-action-icon btn-set-primary" data-id="${foto.id}" title="Set Utama"><i class="bi bi-star-fill text-warning"></i></button>` : ''}
                            <button type="button" class="btn-action-icon text-danger btn-delete-foto" data-id="${foto.id}" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                        </div>
                    </div>
                `;
            });
        } else {
            galleryHtml = '<span class="text-muted small">Belum ada foto yang diupload.</span>';
        }
        $('#edit_fotos_gallery').html(galleryHtml);

        $('#modalEditBarang').modal('show');
    });
});

// Initialize Select2 dropdowns when edit modal is fully visible
$('#modalEditBarang').on('shown.bs.modal', function () {
    initSelect2Safely($('#edit_satuan_id'), $('#modalEditBarang'), '-- Pilih Satuan Dasar --');
    initSelect2Safely($('#edit_brand_id'), $('#modalEditBarang'), '-- Tanpa Merk --');
    initSelect2Safely($('#edit_tipe_barang_id'), $('#modalEditBarang'), '-- Pilih Tipe --');
    initSelect2Safely($('#table_edit_konversi .select-konversi-satuan'), $('#modalEditBarang'), '-- Pilih Satuan Alternatif --');
    initSelect2Safely($('#table_edit_varian .select-warna-input'), $('#modalEditBarang'), '-- Tanpa Warna --');
});

// ===== UPDATE BARANG =====
$('#formEditBarang').on('submit', async function (e) {
    e.preventDefault();

    // Validasi panjang/karakter
    const kodeVal = $('#edit_kode_barang').val() || '';
    const namaVal = $('#edit_nama_barang').val() || '';
    const aliasVal = $('#edit_nama_alias').val() || '';
    const spesifikasiVal = $('#edit_spesifikasi').val() || '';
    const beratVal = $('#edit_berat').val() || '';
    const panjangVal = $('#edit_panjang').val() || '';
    const lebarVal = $('#edit_lebar').val() || '';
    const tinggiVal = $('#edit_tinggi').val() || '';
    const minStokVal = $('#edit_minimum_stok').val() || '';
    const maxStokVal = $('#edit_maksimum_stok').val() || '';
    const reorderVal = $('#edit_reorder_point').val() || '';

    if (kodeVal.length > 20) {
        Swal.fire({ title: 'Peringatan!', text: 'Kode Barang tidak boleh melebihi 20 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (namaVal.length > 50) {
        Swal.fire({ title: 'Peringatan!', text: 'Nama Barang tidak boleh melebihi 50 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (aliasVal.length > 20) {
        Swal.fire({ title: 'Peringatan!', text: 'Nama Alias tidak boleh melebihi 20 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (spesifikasiVal.length > 20) {
        Swal.fire({ title: 'Peringatan!', text: 'Spesifikasi/Deskripsi tidak boleh melebihi 20 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (beratVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Berat tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (panjangVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Panjang tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (lebarVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Lebar tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (tinggiVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Tinggi tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (minStokVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Min. Stok tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (maxStokVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Max. Stok tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }
    if (reorderVal.length > 5) {
        Swal.fire({ title: 'Peringatan!', text: 'Reorder Pt. tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
        return;
    }

    let id = $('#edit_barang_id').val();

    // Hitung foto existing (yang sudah tersimpan di DB) + foto baru
    const existingFotoCount = $('#edit_fotos_gallery .photo-gallery-item').length;
    const newFiles = pondEdit.getFiles();
    const totalFoto = existingFotoCount + newFiles.length;
    if (totalFoto > MAX_FOTO) {
        Swal.fire({
            title: 'Batas Foto!',
            html: `Total foto tidak boleh lebih dari <b>${MAX_FOTO}</b>.<br>Saat ini: <b>${existingFotoCount}</b> foto tersimpan + <b>${newFiles.length}</b> foto baru = <b>${totalFoto}</b>.<br>Hapus beberapa foto lama terlebih dahulu.`,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    let formData = new FormData(this);

    // Proses kompresi & append FilePond files
    try {
        for (let i = 0; i < newFiles.length; i++) {
            const processed = await compressImage(newFiles[i].file);
            formData.append('fotos[]', processed);
        }
    } catch (err) {
        $('#edit_bg_status').hide();
        Swal.fire({
            title: 'Proses Gagal',
            text: err.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    $('#edit_bg_status').hide();

    $.ajax({
        url: '/barang/' + id + '/update',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#modalEditBarang').modal('hide');
            Swal.fire({
                title: 'Berhasil!',
                text: res.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        },
        error: function (err) {
            let errorMsg = 'Terjadi kesalahan saat mengupdate data.';
            if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                errorMsg = '<b>Data tidak valid:</b><br>';
                $.each(err.responseJSON.errors, function (key, val) {
                    errorMsg += '• ' + val[0] + '<br>';
                });
            } else {
                let rawMsg = '';
                if (err.responseJSON && err.responseJSON.message) {
                    rawMsg = err.responseJSON.message;
                } else if (err.responseText) {
                    rawMsg = err.responseText;
                }
                
                if (rawMsg.indexOf('SQLSTATE[23000]') !== -1 || rawMsg.indexOf('Duplicate entry') !== -1) {
                    errorMsg = 'Data ini sudah pernah disimpan di database.';
                } else if (rawMsg) {
                    errorMsg = rawMsg;
                }
            }
            Swal.fire({
                title: 'Gagal!',
                html: errorMsg,
                icon: 'error'
            });
        }
    });
});

// ===== SET PRIMARY FOTO =====
$(document).on('click', '.btn-set-primary', function () {
    let fotoId = $(this).data('id');
    $.ajax({
        url: '/barang/foto/' + fotoId + '/set-primary',
        type: 'POST',
        success: function (res) {
            Swal.fire({
                title: 'Berhasil!',
                text: res.message,
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
            });
            photoChanged = true;

            // Re-fetch edit modal contents
            let barangId = $('#edit_barang_id').val();
            $.get('/barang/' + barangId + '/edit', function (resEdit) {
                let b = resEdit.barang;
                let galleryHtml = '';
                b.fotos.forEach(foto => {
                    let primaryBadge = foto.is_primary ? '<span class="primary-badge">Cover</span>' : '';
                    galleryHtml += `
                        <div class="photo-gallery-item" id="foto-item-${foto.id}" draggable="true">
                            <img src="/storage/${foto.foto_path}" alt="Foto">
                            ${primaryBadge}
                            <div class="btn-actions">
                                ${!foto.is_primary ? `<button type="button" class="btn-action-icon btn-set-primary" data-id="${foto.id}" title="Set Utama"><i class="bi bi-star-fill text-warning"></i></button>` : ''}
                                <button type="button" class="btn-action-icon text-danger btn-delete-foto" data-id="${foto.id}" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        </div>
                    `;
                });
                $('#edit_fotos_gallery').html(galleryHtml);
            });
        }
    });
});

// ===== DELETE FOTO =====
$(document).on('click', '.btn-delete-foto', function () {
    let fotoId = $(this).data('id');
    Swal.fire({
        title: 'Hapus foto ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/barang/foto/' + fotoId + '/delete',
                type: 'DELETE',
                success: function (res) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    photoChanged = true;
                    $(`#foto-item-${fotoId}`).remove();
                    if ($('#edit_fotos_gallery').children().length === 0) {
                        $('#edit_fotos_gallery').html('<span class="text-muted small">Belum ada foto yang diupload.</span>');
                    }
                }
            });
        }
    });
});

// Real-time length validation
const fieldsEdit = [
    { id: '#edit_kode_barang', max: 20, name: 'Kode Barang' },
    { id: '#edit_nama_barang', max: 50, name: 'Nama Barang' },
    { id: '#edit_nama_alias', max: 20, name: 'Nama Alias' },
    { id: '#edit_spesifikasi', max: 20, name: 'Deskripsi / Spesifikasi' },
    { id: '#edit_berat', max: 5, name: 'Berat' },
    { id: '#edit_panjang', max: 5, name: 'Panjang' },
    { id: '#edit_lebar', max: 5, name: 'Lebar' },
    { id: '#edit_tinggi', max: 5, name: 'Tinggi' },
    { id: '#edit_minimum_stok', max: 5, name: 'Min. Stok' },
    { id: '#edit_maksimum_stok', max: 5, name: 'Max. Stok' },
    { id: '#edit_reorder_point', max: 5, name: 'Reorder Pt.' }
];

fieldsEdit.forEach(field => {
    $(field.id).on('input', function () {
        if ($(this).val().length > field.max) {
            $(this).val($(this).val().substring(0, field.max));
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            Swal.fire({
                title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                html: `<div style="font-family: 'Inter', sans-serif; font-size: 0.85rem; color: ${isDark ? '#cbd5e1' : '#64748b'}; line-height: 1.5; margin-top: 5px;">Input <b>${field.name}</b> dibatasi maksimal <b>${field.max} karakter</b>.</div>`,
                icon: 'warning',
                iconColor: '#f59e0b',
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#f8fafc' : '#1e293b',
                confirmButtonText: 'Saya Mengerti',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-warning rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                }
            });
        }
    });
});

// ===== HTML5 DRAG & DROP FOR PHOTO REORDER =====
let dragSource = null;

$(document).on('dragstart', '.photo-gallery-item', function (e) {
    dragSource = this;
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    $(this).addClass('dragging');
});

$(document).on('dragover', '.photo-gallery-item', function (e) {
    e.preventDefault();
    e.originalEvent.dataTransfer.dropEffect = 'move';
});

$(document).on('dragenter', '.photo-gallery-item', function (e) {
    if (this !== dragSource) {
        $(this).addClass('drag-over');
    }
});

$(document).on('dragleave', '.photo-gallery-item', function (e) {
    $(this).removeClass('drag-over');
});

$(document).on('drop', '.photo-gallery-item', function (e) {
    e.stopPropagation();
    $(this).removeClass('drag-over');
    
    if (dragSource !== this) {
        const list = $('#edit_fotos_gallery');
        const items = [...list.children('.photo-gallery-item')];
        const srcIndex = items.indexOf(dragSource);
        const destIndex = items.indexOf(this);
        
        if (srcIndex < destIndex) {
            $(this).after(dragSource);
        } else {
            $(this).before(dragSource);
        }
        
        saveNewPhotoOrder();
    }
});

$(document).on('dragend', '.photo-gallery-item', function (e) {
    $('.photo-gallery-item').removeClass('dragging drag-over');
});

function saveNewPhotoOrder() {
    const ids = [];
    $('#edit_fotos_gallery .photo-gallery-item').each(function () {
        const id = $(this).attr('id').replace('foto-item-', '');
        ids.push(id);
    });

    if (ids.length === 0) return;

    $.ajax({
        url: '/barang/foto/reorder',
        type: 'POST',
        data: { ids: ids },
        success: function (res) {
            // Update badges and buttons dynamically in DOM to reflect new order
            $('#edit_fotos_gallery .photo-gallery-item').each(function (index) {
                $(this).find('.primary-badge').remove();
                $(this).find('.btn-set-primary').remove();
                
                const id = $(this).attr('id').replace('foto-item-', '');
                if (index === 0) {
                    $(this).append('<span class="primary-badge">Cover</span>');
                } else {
                    $(this).find('.btn-actions').prepend(
                        `<button type="button" class="btn-action-icon btn-set-primary" data-id="${id}" title="Set Utama"><i class="bi bi-star-fill text-warning"></i></button>`
                    );
                }
            });
            
            photoChanged = true;
            
            // Show a quick toast notification
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'success',
                title: res.message
            });
        },
        error: function (err) {
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengurutkan foto.',
                icon: 'error'
            });
        }
    });
}
