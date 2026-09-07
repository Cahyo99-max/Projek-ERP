// ===== INITIALIZE FILEPOND FOR TAMBAH =====
const pondTambah = FilePond.create(document.querySelector('#tambah_foto_input'), {
    allowMultiple: true,
    maxFiles: MAX_FOTO,
    maxTotalFileSize: '10MB',
    acceptedFileTypes: ['image/*'],
    labelIdle: `<i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 1.5rem; display: block; margin-bottom: 2px;"></i>Seret foto ke sini atau <span class="filepond--label-action">Pilih File</span>`,
    labelMaxFileSizeExceeded: 'File terlalu besar',
    labelMaxTotalFileSizeExceeded: `Maksimal hanya ${MAX_FOTO} foto`,
    onwarning: (error) => {
        if (error.body === 'Max files') {
            Swal.fire({ title: 'Batas Foto!', text: `Maksimal hanya ${MAX_FOTO} foto yang bisa diupload.`, icon: 'warning', confirmButtonText: 'OK' });
        }
    },
    allowPaste: true,
    allowDrop: true,
    allowReorder: true,
});

// ===== AI BACKGROUND REMOVAL LOGIC FOR TAMBAH =====
let temporaryAiBgFilesTambah = [];

$('#btn_tambah_remove_bg').on('click', async function () {
    const files = pondTambah.getFiles();
    if (files.length === 0) {
        Swal.fire({ title: 'Kosong!', text: 'Silakan masukkan minimal 1 foto terlebih dahulu.', icon: 'warning' });
        return;
    }

    if (!window._executeStudioAiRemoveBg && !window._imglyRemoveBg) {
        Swal.fire({ title: 'Menyiapkan AI', text: 'AI Studio Hapus Background sedang menyiapkan model produk, harap tunggu beberapa detik lalu coba lagi.', icon: 'info' });
        return;
    }

    $('#btn_tambah_remove_bg').prop('disabled', true);
    $('#tambah_bg_status').show();
    $('#tambah_bg_progress_bar').css('width', '0%');
    $('#tambah_bg_counter_text').text('0%');
    $('#preview_bg_container').empty();
    temporaryAiBgFilesTambah = [];

    try {
        const total = files.length;
        for (let i = 0; i < total; i++) {
            const currentStep = i + 1;
            const startPercent = Math.round((i / total) * 100);
            $('#tambah_bg_progress_bar').css('width', startPercent + '%');
            $('#tambah_bg_counter_text').text(`${startPercent}%`);
            $('#tambah_bg_status_text').text(`[AI Studio] Memproses objek foto ${currentStep} dari ${total}...`);

            const originalBlob = files[i].file;
            
            // Execute BRIA RMBG-1.4 state-of-the-art product background removal
            const resultBlob = await window._executeStudioAiRemoveBg(originalBlob, (p) => {
                if (p.total) {
                    const dlPercent = Math.round((p.loaded / p.total) * 100);
                    $('#tambah_bg_status_text').text(`[AI Studio] Mengunduh neural weights (${dlPercent}%)...`);
                }
            });
            
            const safeName = (originalBlob.name || 'foto').replace(/\.[^.]+$/, '') + '_nobg.png';
            const newFile = new File([resultBlob], safeName, { type: 'image/png' });
            temporaryAiBgFilesTambah.push({ file: newFile });
            
            const endPercent = Math.round((currentStep / total) * 100);
            $('#tambah_bg_progress_bar').css('width', endPercent + '%');
            $('#tambah_bg_counter_text').text(`${endPercent}%`);
            $('#tambah_bg_status_text').text(`[AI Studio] Selesai foto ${currentStep} dari ${total}!`);

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

        $('#tambah_bg_status').hide();
        
        // Bind Apply Action
        $('#btn_apply_bg_removal').off('click').on('click', function () {
            pondTambah.removeFiles(); 
            temporaryAiBgFilesTambah.forEach(item => {
                pondTambah.addFile(item.file);
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
        $('#tambah_bg_status').hide();
        Swal.fire({ title: 'Proses AI Gagal', text: err.message || err, icon: 'error' });
    } finally {
        $('#btn_tambah_remove_bg').prop('disabled', false);
    }
});

// Reset form on open
$('#modalTambahBarang').on('show.bs.modal', function () {
    $('#tbody_tambah_varian').empty();
    $('#tbody_tambah_bundle').empty();
    $('#tbody_tambah_konversi').html(`
        <tr class="empty-konversi-row">
            <td colspan="4" class="text-center text-muted py-3 small">
                <i class="bi bi-info-circle me-1"></i> Belum ada satuan konversi alternatif. Klik tombol <b>Tambah Satuan Alternatif</b> jika barang memiliki satuan kemasan/grosir (misal: Roll, Dus, Bal).
            </td>
        </tr>
    `);
    tambahVarianIndex = 0;
    tambahBundleIndex = 0;
    tambahKonversiIndex = 0;
    $('#tambah_tipe_pelacakan').val('None');
    $('#tambah_satuan_id').val('').trigger('change.select2');
    $('#tambah_brand_id').val('').trigger('change.select2');
    $('#tambah_tipe_barang_id').val('').trigger('change.select2');
    $('#tambah_has_variants').prop('checked', false).trigger('change');
    $('#tambah_tipe_item').val('Stok').trigger('change');
});

// Initialize Select2 dropdowns when modal is fully visible
$('#modalTambahBarang').on('shown.bs.modal', function () {
    initSelect2Safely($('#tambah_satuan_id'), $('#modalTambahBarang'), '-- Pilih Satuan Dasar --');
    initSelect2Safely($('#tambah_brand_id'), $('#modalTambahBarang'), '-- Tanpa Merk --');
    initSelect2Safely($('#tambah_tipe_barang_id'), $('#modalTambahBarang'), '-- Pilih Tipe --');
    initSelect2Safely($('#table_tambah_konversi .select-konversi-satuan'), $('#modalTambahBarang'), '-- Pilih Satuan Alternatif --');
    initSelect2Safely($('#table_tambah_varian .select-warna-input'), $('#modalTambahBarang'), '-- Tanpa Warna --');
});

// Form submit handler for Tambah Barang
$('#formTambahBarang').on('submit', async function (e) {
    e.preventDefault();

    // Validasi panjang/karakter
    const kodeVal = $('#tambah_kode_barang').val() || '';
    const namaVal = $('#tambah_nama_barang').val() || '';
    const aliasVal = $('#tambah_nama_alias').val() || '';
    const spesifikasiVal = $('#tambah_spesifikasi').val() || '';
    const beratVal = $('#tambah_berat').val() || '';
    const panjangVal = $('#tambah_panjang').val() || '';
    const lebarVal = $('#tambah_lebar').val() || '';
    const tinggiVal = $('#tambah_tinggi').val() || '';
    const minStokVal = $('#tambah_minimum_stok').val() || '';
    const maxStokVal = $('#tambah_maksimum_stok').val() || '';
    const reorderVal = $('#tambah_reorder_point').val() || '';

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

    // Validasi max foto
    const filesToUpload = pondTambah.getFiles();
    if (filesToUpload.length > MAX_FOTO) {
        Swal.fire({ title: 'Batas Foto!', text: `Maksimal hanya ${MAX_FOTO} foto yang bisa diupload.`, icon: 'warning', confirmButtonText: 'OK' });
        return;
    }

    let formData = new FormData(this);

    // Proses kompresi & append FilePond files
    try {
        for (let i = 0; i < filesToUpload.length; i++) {
            const processed = await compressImage(filesToUpload[i].file);
            formData.append('fotos[]', processed);
        }
    } catch (err) {
        $('#tambah_bg_status').hide();
        Swal.fire({
            title: 'Proses Gagal',
            text: err.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    $.ajax({
        url: "{{ route('barang.store') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#modalTambahBarang').modal('hide');
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
            let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
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

// Real-time length validation
const fieldsTambah = [
    { id: '#tambah_kode_barang', max: 20, name: 'Kode Barang' },
    { id: '#tambah_nama_barang', max: 50, name: 'Nama Barang' },
    { id: '#tambah_nama_alias', max: 20, name: 'Nama Alias' },
    { id: '#tambah_spesifikasi', max: 20, name: 'Deskripsi / Spesifikasi' },
    { id: '#tambah_berat', max: 5, name: 'Berat' },
    { id: '#tambah_panjang', max: 5, name: 'Panjang' },
    { id: '#tambah_lebar', max: 5, name: 'Lebar' },
    { id: '#tambah_tinggi', max: 5, name: 'Tinggi' },
    { id: '#tambah_minimum_stok', max: 5, name: 'Min. Stok' },
    { id: '#tambah_maksimum_stok', max: 5, name: 'Max. Stok' },
    { id: '#tambah_reorder_point', max: 5, name: 'Reorder Pt.' }
];

fieldsTambah.forEach(field => {
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
