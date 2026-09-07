// ===== DETAIL FISIK (SHOW MODAL) =====
$(document).on('click', '.btn-detail', function () {
    let id = $(this).data('id');
    let rowTr = $(this).closest('tr');
    let b = tableBarang.row(rowTr).data();
    if (!b) {
        let parentTr = $(this).closest('tr.child').prev('tr');
        b = tableBarang.row(parentTr).data();
    }
    
    if (!b && !id) return;

    const formatRp = (val) => {
        let num = Number(val) || 0;
        return num > 0 ? 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : '-';
    };

    const renderData = (item) => {
        $('#detail_sku').text(item.kode_barang || '-');
        $('#detail_barcode').text(item.barcode || '-');
        
        let pelacakanText = item.tipe_pelacakan || 'None';
        if (pelacakanText === 'Serial Number') {
            $('#detail_tipe_pelacakan').html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-upc me-1"></i>Serial Number (SN)</span>');
        } else if (pelacakanText === 'Batch') {
            $('#detail_tipe_pelacakan').html('<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-collection me-1"></i>Batch / Lot</span>');
        } else {
            $('#detail_tipe_pelacakan').html('<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Tanpa SN</span>');
        }

        let satName = item.satuan_name || (item.satuan ? item.satuan.nama_satuan : '-');
        $('#detail_satuan_nama').text(satName);

        if (item.ukuran && item.ukuran !== '-') {
            $('#detail_ukuran').html(`<span class="brg-size-chip">${item.ukuran}</span>`);
        } else {
            $('#detail_ukuran').html('<span class="text-muted small">-</span>');
        }

        $('#detail_panjang').text(item.panjang ? item.panjang + ' cm' : '-');
        $('#detail_lebar').text(item.lebar ? item.lebar + ' cm' : '-');
        $('#detail_tinggi').text(item.tinggi ? item.tinggi + ' cm' : '-');
        $('#detail_berat').text(item.berat ? item.berat + ' kg' : '-');
        
        let hppVal = Number(item.hpp_referensi) || 0;
        $('#detail_hpp_referensi').text(hppVal > 0 ? 'Rp ' + hppVal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : 'Rp 0');
        $('#detail_harga_retail').text(formatRp(item.harga_jual_retail));
        $('#detail_harga_grosir').text(formatRp(item.harga_jual_grosir));
        $('#detail_harga_tender').text(formatRp(item.harga_jual_tender));

        let wName = item.warna_name || (item.warna ? item.warna.nama_warna : '-');
        let wHex = item.warna_hex || (item.warna ? item.warna.kode_hex : '');
        if (wName && wName !== '-') {
            $('#detail_warna_circle').css('background-color', wHex ? wHex : '#e2e8f0');
            $('#detail_warna_nama').text(wName);
        } else {
            $('#detail_warna_circle').css('background-color', '#e2e8f0');
            $('#detail_warna_nama').text('Tanpa Warna');
        }

        // Render konversi satuan
        let konversiList = item.konversi_satuan || [];
        if (konversiList.length > 0) {
            let kRows = '';
            konversiList.forEach(k => {
                let kSatName = k.nama_satuan || (k.satuan ? k.satuan.nama_satuan : '-');
                let kSingkatan = k.singkatan || (k.satuan ? k.satuan.singkatan : '');
                let tags = [];
                if (k.is_satuan_beli) tags.push('<span class="badge bg-primary-subtle text-primary me-1"><i class="bi bi-bag-check me-1"></i>Pembelian (PO)</span>');
                if (k.is_satuan_jual) tags.push('<span class="badge bg-success-subtle text-success"><i class="bi bi-cart-check me-1"></i>Penjualan (SO)</span>');
                if (!tags.length) tags.push('<span class="text-muted small">-</span>');

                let faktorNum = parseFloat(k.faktor_pengali) || 0;
                let formattedFaktor = faktorNum % 1 === 0 
                    ? faktorNum.toLocaleString('id-ID') 
                    : parseFloat(faktorNum.toFixed(4)).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });

                kRows += `
                    <tr>
                        <td><span class="fw-bold">${kSatName}</span> <span class="badge bg-secondary-subtle text-secondary border ms-1">(${kSingkatan})</span></td>
                        <td><b>1 ${kSingkatan}</b> = <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7 font-monospace px-2 py-1">${formattedFaktor}</span> ${satName}</td>
                        <td>${tags.join(' ')}</td>
                    </tr>
                `;
            });
            $('#detail_konversi_table_body').html(kRows);
            $('#detail_konversi_section').removeClass('d-none');
        } else {
            $('#detail_konversi_section').addClass('d-none');
        }

        // Render variants
        if (item.variants && item.variants.length > 0) {
            let rows = '';
            item.variants.forEach(v => {
                let vWarna = v.warna_name || (v.warna ? v.warna.nama_warna : '-');
                let vHex = v.warna_hex || (v.warna ? v.warna.kode_hex : '');
                let dot = vHex ? `<span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:${vHex};margin-right:4px;"></span>` : '';
                rows += `
                    <tr>
                        <td><span class="badge bg-secondary-subtle text-secondary font-monospace">${v.kode_barang}</span></td>
                        <td><span class="brg-size-chip">${v.ukuran}</span></td>
                        <td>${dot} ${vWarna}</td>
                        <td class="text-center">
                            <span class="badge ${v.status === 'Active' ? 'bg-success' : 'bg-danger'}">${v.status === 'Active' ? 'Aktif' : 'Nonaktif'}</span>
                        </td>
                    </tr>
                `;
            });
            $('#detail_varian_table_body').html(rows);
            $('#detail_varian_section').removeClass('d-none');
        } else {
            $('#detail_varian_section').addClass('d-none');
        }

        // Render photo gallery
        let galleryHtml = '';
        let photos = item.all_photos || [];
        if (!photos.length && item.fotos && item.fotos.length > 0) {
            photos = item.fotos.map(f => ({
                url: f.foto_path.startsWith('uploads/') ? `/${f.foto_path}` : `/storage/${f.foto_path}`,
                is_primary: f.is_primary
            }));
        }

        if (photos.length > 0) {
            photos.forEach(foto => {
                let borderStyle = foto.is_primary ? 'border: 2.5px solid #ef4444;' : 'border: 1px solid rgba(0,0,0,0.1);';
                let titleText = foto.is_primary ? 'Foto Utama (Cover)' : 'Foto Detail';
                galleryHtml += `
                    <a href="javascript:void(0)" class="btn-view-photo" title="Klik untuk memperbesar">
                        <img src="${foto.url}" class="detail-photo-thumb" style="${borderStyle}" title="${titleText}">
                    </a>
                `;
            });
        } else {
            galleryHtml = '<div class="text-muted small">Tidak ada foto barang.</div>';
        }
        $('#detail_fotos_gallery').html(galleryHtml);
        
        $('#detail_spesifikasi').text(item.spesifikasi ? item.spesifikasi : 'Tidak ada spesifikasi khusus.');
    };

    if (b) {
        renderData(b);
    }
    
    $('#modalDetailBarang').modal('show');

    // Fetch fresh details (HPP, photos, etc.) from server
    let targetId = id || (b ? b.id : null);
    if (targetId) {
        $.get(`/master-barang/${targetId}/edit`, function (res) {
            if (res && res.barang) {
                renderData(res.barang);
            }
        });
    }
});

// ===== GENERATE QR CODE POP-UP =====
$(document).on('click', '.btn-qr-code', function () {
    let kode  = $(this).data('kode');
    let nama  = $(this).data('nama') || '';
    let warna = $(this).data('warna') || '';
    let hex   = $(this).data('warna-hex') || '';

    // Instantly generate QR Code locally (runs client-side, zero latency)
    let qr = new QRious({
        value: kode,
        size: 300,
        level: 'H'
    });

    $('#qr_title').text('QR Code Barang');
    $('#qr_nama').text(nama);

    // Render warna dengan dot berwarna
    if (warna) {
        let dotStyle = hex
            ? `background:${hex};border:1px solid #e2e8f0;`
            : `background:#94a3b8;border:1px solid #e2e8f0;`;
        $('#qr_warna').html(`<span style="display:inline-block;width:11px;height:11px;border-radius:50%;${dotStyle}flex-shrink:0;"></span> ${warna}`);
    } else {
        $('#qr_warna').html('<span style="color:#cbd5e1;">— Tanpa Warna</span>');
    }

    $('#qr_img').attr('src', qr.toDataURL());
    $('#qr_code_text').text(kode);
    $('#modalQrCode').modal('show');
});
