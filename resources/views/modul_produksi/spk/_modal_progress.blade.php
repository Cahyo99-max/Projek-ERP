{{-- MODAL REALISASI & PROGRESS PRODUKSI SPK --}}
<div class="modal fade spk-modal-primary" id="modalProgressSpk" tabindex="-1" aria-labelledby="modalProgressSpkLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header spk-modal-header border-bottom border-light border-opacity-10 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="spk-header-icon bg-info bg-opacity-25 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-list-check fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalProgressSpkLabel">Catat Realisasi & Progress Tahapan Produksi</h5>
                        <small class="text-white-50" style="font-size: 11.5px;">Update status pengerjaan, hasil Qty selesai & reject per tahapan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body spk-modal-body p-4">
                
                {{-- CARD INFORMASI SPK & RINGKASAN PROGRESS --}}
                <div class="card border-0 bg-dark bg-opacity-40 rounded-3 p-3 mb-4 border-start border-4 border-info shadow-xs">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-info bg-opacity-25 text-info font-monospace fs-7 px-2.5 py-1" id="prog_nomor_spk">SPK-0000</span>
                                <span id="prog_status_badge"></span>
                            </div>
                            <h6 class="fw-bold text-white mb-0" id="prog_nama_barang">Nama Produk Jadi</h6>
                            <small class="text-white-50" id="prog_resep_bom">Resep BOM: -</small>
                        </div>
                        <div class="text-md-end">
                            <div class="small text-white-50">Target Selesai</div>
                            <div class="fw-bold text-white fs-6" id="prog_target_qty">0 Pcs</div>
                            <small class="text-info" id="prog_mandor">PIC: -</small>
                        </div>
                    </div>

                    {{-- PROGRESS BAR PRESENTASE --}}
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-white-50">Progress Produksi Keseluruhan</span>
                            <span class="small fw-bold text-info" id="prog_percent_text">0%</span>
                        </div>
                        <div class="progress bg-dark border border-secondary" style="height: 10px;">
                            <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" id="prog_percent_bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                {{-- DRAFT TAHAPAN CARDS --}}
                <div class="row g-3" id="containerTahapanProgressCards">
                    <div class="col-12 text-center text-muted py-4">Memuat tahapan kerja...</div>
                </div>
            </div>

            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" id="btnCompleteSpkFromProgress" onclick="completeSpkFromProgress()">
                    <i class="bi bi-check-circle-fill me-1"></i> Selesaikan SPK (Completed)
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeProgressSpkId = null;

    function openModalProgress(spkId) {
        activeProgressSpkId = spkId;
        $('#btnCompleteSpkFromProgress').attr('onclick', `completeSpkFromProgress(${spkId})`);
        loadSpkProgressData(spkId);
        $('#modalProgressSpk').modal('show');
    }

    function loadSpkProgressData(spkId) {
        $.ajax({
            url: `/produksi/spk/${spkId}/progress`,
            method: 'GET',
            success: function(res) {
                if (!res.success || !res.spk) return;

                const spk = res.spk;
                const tahapans = res.tahapans || [];
                const satuan = spk.satuan ? spk.satuan.nama_satuan : 'Pcs';

                // Populate Header
                $('#prog_nomor_spk').text(spk.nomor_spk);
                $('#prog_nama_barang').text(`[${spk.barang_jadi ? spk.barang_jadi.kode_barang : '-'}] ${spk.barang_jadi ? spk.barang_jadi.nama_barang : '-'}`);
                $('#prog_resep_bom').text(`Resep BOM: [${spk.bom ? spk.bom.kode_bom : '-'}] ${spk.bom ? spk.bom.nama_bom : '-'}`);
                $('#prog_target_qty').text(`${parseFloat(spk.jumlah_rencana)} ${satuan}`);
                $('#prog_mandor').text(`PIC: ${spk.penanggung_jawab ? spk.penanggung_jawab.nama_lengkap : 'Kepala Line'}`);

                // Status Badge Map
                const statusMap = {
                    'Draft': '<span class="badge bg-secondary rounded-pill">Draft</span>',
                    'Pending': '<span class="badge bg-warning text-dark rounded-pill">Pending</span>',
                    'Approved': '<span class="badge bg-success rounded-pill">Disetujui</span>',
                    'In Progress': '<span class="badge bg-info text-dark rounded-pill">Dalam Proses</span>',
                    'Completed': '<span class="badge bg-success rounded-pill">Selesai</span>',
                    'Cancelled': '<span class="badge bg-danger rounded-pill">Dibatalkan</span>'
                };
                $('#prog_status_badge').html(statusMap[spk.status] || `<span class="badge bg-light text-dark rounded-pill">${spk.status}</span>`);

                // Progress Bar
                $('#prog_percent_text').text(`${percent}% Selesai (${res.completed_tahap}/${res.total_tahap} Tahapan)`);
                $('#prog_percent_bar').css('width', `${percent}%`).attr('aria-valuenow', percent);

                // Configure Complete Button
                $('#btnCompleteSpkFromProgress').attr('onclick', `completeSpkFromProgress(${spk.id})`);
                if (spk.status === 'Completed') {
                    $('#btnCompleteSpkFromProgress').prop('disabled', true).html('<i class="bi bi-patch-check-fill me-1"></i> Produksi Sudah Selesai');
                } else {
                    $('#btnCompleteSpkFromProgress').prop('disabled', false).html('<i class="bi bi-patch-check-fill me-1"></i> Selesaikan Seluruh Produksi SPK');
                }

                // Render Tahapan List
                renderTahapanCards(spk.tahapans || [], satuan, spk.id);
            },
            error: function(xhr) {
                $container.html(`
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal memuat data progress tahapan SPK.
                    </div>
                `);
            }
        });
    }

    function renderTahapanCards(tahapans, satuan, spkId) {
        const $container = $('#containerTahapanProgress');
        if (!tahapans || tahapans.length === 0) {
            $container.html(`
                <div class="text-center py-4 text-white-50 border rounded-3 bg-dark bg-opacity-20">
                    <i class="bi bi-info-circle fs-3 text-muted mb-2 d-block"></i>
                    Belum ada tahapan produksi yang terdaftar pada SPK ini.
                </div>
            `);
            return;
        }

        let html = '';
        tahapans.forEach((t, idx) => {
            let statusBadge = '';
            let borderClass = 'border-secondary';
            let bgClass = 'bg-dark bg-opacity-30';

            if (t.status === 'Selesai') {
                statusBadge = '<span class="badge bg-success rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
                borderClass = 'border-success';
                bgClass = 'bg-success bg-opacity-10';
            } else if (t.status === 'Sedang Dikerjakan') {
                statusBadge = '<span class="badge bg-info text-dark rounded-pill"><i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-1" style="width:0.7rem;height:0.7rem;"></i>Sedang Dikerjakan</span>';
                borderClass = 'border-info';
                bgClass = 'bg-info bg-opacity-10';
            } else {
                statusBadge = '<span class="badge bg-secondary rounded-pill">Menunggu</span>';
            }

            html += `
                <div class="card border ${borderClass} ${bgClass} rounded-3 p-3 shadow-xs">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 border-bottom border-light border-opacity-10 pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-circle fw-bold" style="width: 28px; height: 28px; line-height: 18px;">#${t.urutan}</span>
                            <h6 class="fw-bold text-white mb-0">${t.nama_tahapan}</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${statusBadge}
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-white-50 mb-1">Target Qty</label>
                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary fw-bold" value="${parseFloat(t.target_qty)} ${satuan}" readonly>
                            <input type="hidden" id="prog_target_qty_${t.id}" value="${parseFloat(t.target_qty || 0)}">
                            <input type="hidden" id="prog_nama_tahap_${t.id}" value="${t.nama_tahapan}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-white-50 mb-1">Qty Selesai (${satuan})</label>
                            <input type="number" id="prog_qty_selesai_${t.id}" class="form-control form-control-sm bg-dark text-white border-info fw-bold" value="${parseFloat(t.qty_selesai || 0)}" min="0" step="any">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-white-50 mb-1">Qty Reject (${satuan})</label>
                            <input type="number" id="prog_qty_reject_${t.id}" class="form-control form-control-sm bg-dark text-white border-danger fw-bold" value="${parseFloat(t.qty_reject || 0)}" min="0" step="any">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-white-50 mb-1">Operator / PIC</label>
                            <input type="text" id="prog_pic_${t.id}" class="form-control form-control-sm bg-dark text-white border-secondary" value="${t.pic_operator || ''}" placeholder="Nama Operator Line">
                        </div>
                        <div class="col-12">
                            <input type="text" id="prog_catatan_${t.id}" class="form-control form-control-sm bg-dark text-white border-secondary" value="${t.catatan || ''}" placeholder="Catatan spesifik pengerjaan tahapan ini...">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        ${t.status === 'Menunggu' ? `
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="quickStartTahapanFromModalProgress(${spkId}, ${t.id})">
                                <i class="bi bi-play-fill me-1"></i> Mulai Pengerjaan
                            </button>
                        ` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="saveTahapanProgress(${spkId}, ${t.id}, '${t.status}')">
                            <i class="bi bi-save me-1"></i> Simpan Progress
                        </button>
                        
                        ${t.status !== 'Selesai' ? `
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="saveTahapanProgress(${spkId}, ${t.id}, 'Selesai')">
                                <i class="bi bi-check-lg me-1"></i> Tandai Selesai
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        $container.html(html);
    }

    function saveTahapanProgress(spkId, tahapanId, targetStatus) {
        const qtySelesai = parseFloat($(`#prog_qty_selesai_${tahapanId}`).val()) || 0;
        const qtyReject = parseFloat($(`#prog_qty_reject_${tahapanId}`).val()) || 0;
        const pic = $(`#prog_pic_${tahapanId}`).val();
        const catatan = $(`#prog_catatan_${tahapanId}`).val();
        const targetQty = parseFloat($(`#prog_target_qty_${tahapanId}`).val()) || 0;
        const namaTahap = $(`#prog_nama_tahap_${tahapanId}`).val() || 'Tahapan';

        const doSaveProgress = () => {
            $.ajax({
                url: `/produksi/spk/${spkId}/progress-tahapan/${tahapanId}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: targetStatus,
                    qty_selesai: qtySelesai,
                    qty_reject: qtyReject,
                    pic_operator: pic,
                    catatan: catatan
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Progress Diperbarui',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadSpkProgressData(spkId);
                        if (typeof dtSpk !== 'undefined' && dtSpk) dtSpk.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui progress tahapan.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        };

        if (targetStatus === 'Selesai' && targetQty > 0 && qtySelesai < targetQty) {
            const sisa = targetQty - qtySelesai;
            Swal.fire({
                title: '❌ Target Pengerjaan Belum Tercapai!',
                html: `
                    <div class="text-start">
                        <p class="mb-2 text-white-50">Hasil pengerjaan <strong>${namaTahap}</strong> saat ini baru mencapai <strong>${qtySelesai} / ${targetQty} Pcs</strong>.</p>
                        <div class="alert alert-danger text-white py-2 mb-3 font-monospace fw-bold" style="font-size: 13px;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Masih Kurang: ${sisa} Pcs!
                        </div>
                        <p class="small text-white-50 mb-0">Tahapan ini belum dapat ditandai <strong>Selesai</strong>. Harap lengkapi Qty Selesai hingga mencapai target.</p>
                    </div>
                `,
                icon: 'error',
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'Mengerti / Perbaiki Qty'
            });
            return;
        } else {
            doSaveProgress();
        }
    }

    function completeSpkFromProgress(spkId) {
        Swal.fire({
            title: 'Selesaikan Produksi SPK?',
            text: 'Status SPK akan diubah menjadi Selesai (Completed) dan seluruh tahapan kerja akan ditandai selesai.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Selesaikan SPK',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/spk/${spkId}/complete-production`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        $('#modalProgressSpk').modal('hide');
                        if (typeof dtSpk !== 'undefined' && dtSpk) dtSpk.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyelesaikan produksi SPK.';
                        Swal.fire({
                            title: '❌ Produksi Belum Selesai!',
                            html: `<div class="text-start text-white-50 mt-1">${msg}</div>`,
                            icon: 'error',
                            confirmButtonColor: '#0ea5e9',
                            confirmButtonText: 'Mengerti / Selesaikan Tahapan Dulu'
                        });
                    }
                });
            }
        });
    }

    function quickStartTahapanFromModalProgress(spkId, tahapanId) {
        $.ajax({
            url: `/produksi/realisasi-produksi/${tahapanId}/start`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Mulai Pengerjaan', text: res.message, timer: 1200, showConfirmButton: false });
                    loadSpkProgressData(spkId);
                    if (typeof dtSpk !== 'undefined' && dtSpk) dtSpk.ajax.reload(null, false);
                    if (typeof dtRealisasi !== 'undefined' && dtRealisasi) dtRealisasi.ajax.reload(null, false);
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memulai pengerjaan.', 'error');
            }
        });
    }
</script>
@endpush
