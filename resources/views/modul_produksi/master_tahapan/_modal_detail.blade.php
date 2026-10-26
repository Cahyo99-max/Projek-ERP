{{-- MODAL DETAIL ALUR TAHAPAN PRODUKSI (PER RESEP BOM) --}}
<div class="modal fade spk-modal-primary" id="modalDetailAlurTahapan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" style="max-width: 900px;">
        <div class="modal-content spk-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- HEADER --}}
            <div class="modal-header py-3 px-3 px-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%); flex-shrink: 0;">
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.12); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                         style="width: 38px; height: 38px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-diagram-3-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-extrabold text-white mb-0" id="detail_alur_modal_title" style="font-size: 16px; letter-spacing: -0.2px;">
                            Detail Alur Tahapan Produksi
                        </h5>
                        <small class="text-white-50 d-block mt-0.5" id="detail_alur_modal_subtitle" style="font-size: 11.5px;">
                            Memuat data alur kerja produksi...
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body spk-modal-body p-2.5 p-sm-3.5 p-md-4 overflow-y-auto" style="min-height: 250px;">
                
                {{-- BOM Summary Info Banner --}}
                <div class="spk-card border-0 rounded-4 shadow-sm mb-3 p-3" style="border: 1px solid var(--spk-border-color) !important; background: var(--spk-bg-light, rgba(255,255,255,0.025));">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2.5">
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #1d4ed8, #2563eb); font-size: 13px; border: 1px solid rgba(255,255,255,0.25);" id="detail_badge_bom_nama">
                                    --
                                </span>
                                <span class="badge rounded-pill px-2.5 py-1 fw-semibold spk-text-main" style="background: var(--spk-bg-tertiary, #f1f5f9); border: 1px solid var(--spk-border-color, #cbd5e1); font-size: 11px;" id="detail_badge_bom_kode">
                                    --
                                </span>
                            </div>
                            <div class="spk-text-muted small mt-1" style="font-size: 12px;" id="detail_txt_barang_jadi">
                                <i class="bi bi-box-seam me-1 text-primary"></i>Produk Jadi: <strong class="spk-text-main">-</strong>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #0284c7, #0ea5e9); font-size: 12px; border: 1px solid rgba(255,255,255,0.25);" id="detail_badge_total_count">
                                0 Tahapan
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Loading Spinner State --}}
                <div id="detailAlurLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p class="spk-text-muted small mb-0">Memuat rincian alur tahapan...</p>
                </div>

                {{-- Container Cards / Step Stream --}}
                <div id="detailAlurContent" class="d-none">
                    <div class="d-flex flex-column gap-2.5" id="detailAlurListCards">
                        {{-- Injected dynamically --}}
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="detailAlurEmpty" class="text-center py-5 d-none">
                    <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="background: rgba(37,99,235,0.12);">
                        <i class="bi bi-diagram-3 fs-1 text-primary"></i>
                    </div>
                    <h6 class="fw-bold spk-text-main mb-1">Belum Ada Tahapan di BOM Ini</h6>
                    <p class="spk-text-muted small mb-3">Klik tombol edit untuk menambahkan tahapan kerja pada resep BOM ini.</p>
                </div>

            </div>

            {{-- FOOTER RESPONSIF --}}
            <div class="modal-footer spk-modal-footer d-flex flex-row justify-content-between gap-2 p-2.5 p-sm-3.5 border-0" 
                 style="background: var(--spk-card-bg, #ffffff); border-top: 1px solid var(--spk-border-color) !important; flex-shrink: 0;">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 px-sm-4" data-bs-dismiss="modal" style="font-size: 13px;">
                    Tutup
                </button>
                @if(auth()->user()->can('edit-master-tahapan'))
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-3 px-sm-4 fw-bold shadow-sm" onclick="bukaEditDariDetail()" style="font-size: 13px;">
                        <i class="bi bi-pencil-square me-1"></i> Edit Alur Tahapan
                    </button>
                @endif
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeDetailBomKey = null;

    function bukaDetailAlurBom(bomKey) {
        activeDetailBomKey = bomKey;
        $('#modalDetailAlurTahapan').modal('show');
        $('#detailAlurLoading').removeClass('d-none');
        $('#detailAlurContent').addClass('d-none');
        $('#detailAlurEmpty').addClass('d-none');
        $('#detailAlurListCards').empty();

        $.get(`/produksi/master-tahapan/bom/${bomKey}`, function(res) {
            $('#detailAlurLoading').addClass('d-none');

            const info = res.bom_info;
            const tahapans = res.tahapans || [];

            $('#detail_alur_modal_subtitle').text(`Rincian ${tahapans.length} langkah alur kerja produksi`);
            $('#detail_badge_bom_nama').text(info.nama_bom);
            $('#detail_badge_bom_kode').text(info.kode_bom);

            if (info.nama_barang) {
                $('#detail_txt_barang_jadi').html(`<i class="bi bi-box-seam me-1 text-primary"></i>Produk Jadi: <strong class="spk-text-main">${info.nama_barang}</strong>`).removeClass('d-none');
            } else {
                $('#detail_txt_barang_jadi').html(`<i class="bi bi-layers-fill me-1 text-primary"></i>Template Alur Standar Umum (Berlaku untuk semua BOM)`).removeClass('d-none');
            }

            $('#detail_badge_total_count').text(`${tahapans.length} Tahapan Proses`);

            if (tahapans.length === 0) {
                $('#detailAlurEmpty').removeClass('d-none');
                return;
            }

            $('#detailAlurContent').removeClass('d-none');

            tahapans.forEach((t, idx) => {
                const stepNo = t.urutan || (idx + 1);
                const hasPhoto = !!t.foto_url;
                const photoSrc = t.foto_url || '';
                const desc = t.deskripsi 
                    ? `<div class="p-2 rounded-3 mt-1.5 spk-text-muted small" style="background: var(--spk-bg-light, rgba(0,0,0,0.02)); border: 1px dashed var(--spk-border-color, #cbd5e1); font-size: 11.5px; line-height: 1.4;">
                            <i class="bi bi-info-circle text-primary me-1"></i>${escapeHtml(t.deskripsi)}
                       </div>` 
                    : '';

                const photoThumb = hasPhoto 
                    ? `<div class="position-relative cursor-pointer flex-shrink-0" onclick="previewFotoTahapan('${photoSrc}', '${escapeHtml(t.nama_tahapan)}')" title="Klik untuk memperbesar foto">
                            <img src="${photoSrc}" alt="${escapeHtml(t.nama_tahapan)}" class="rounded-3 border shadow-xs img-thumbnail-tahapan" 
                                 style="width: 58px; height: 58px; object-fit: cover; border-color: var(--spk-border-color, #cbd5e1) !important;">
                            <span class="badge bg-primary position-absolute bottom-0 end-0 p-0.5 rounded-circle d-flex align-items-center justify-content-center" 
                                  style="width: 18px; height: 18px; font-size: 9px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); transform: translate(15%, 15%);">
                                <i class="bi bi-zoom-in"></i>
                            </span>
                       </div>`
                    : `<div class="rounded-3 d-flex flex-column align-items-center justify-content-center border shadow-xs flex-shrink-0" 
                            style="width: 58px; height: 58px; background: var(--spk-bg-tertiary, #f8fafc); border-color: var(--spk-border-color, #cbd5e1) !important;">
                            <i class="bi bi-image spk-text-muted fs-5 opacity-50"></i>
                            <span style="font-size: 8.5px;" class="spk-text-muted mt-0.5">No Foto</span>
                       </div>`;

                const cardHtml = `
                    <div class="spk-card border rounded-3 shadow-xs p-2.5 p-sm-3 position-relative" 
                         style="border-color: var(--spk-border-color, #e2e8f0) !important; background: var(--spk-card-bg, #ffffff) !important;">
                        
                        {{-- Top Mini Header: Step Pill & Status --}}
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2 pb-1.5 border-bottom" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                                  style="background: linear-gradient(135deg, #2563eb, #1d4ed8); font-size: 11px;">
                                <i class="bi bi-diagram-3-fill" style="font-size: 10px;"></i>
                                <span>Tahap ${stepNo}</span>
                            </span>

                            ${t.is_active 
                                ? '<span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs d-inline-flex align-items-center gap-1" style="background: #10b981; font-size: 10.5px;"><i class="bi bi-check-circle-fill" style="font-size: 9.5px;"></i>Aktif</span>'
                                : '<span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs d-inline-flex align-items-center gap-1" style="background: #ef4444; font-size: 10.5px;"><i class="bi bi-x-circle-fill" style="font-size: 9.5px;"></i>Nonaktif</span>'
                            }
                        </div>

                        {{-- Body: Photo & Nama Tahapan --}}
                        <div class="d-flex align-items-start gap-2.5">
                            ${photoThumb}
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold spk-text-main mb-0 text-break" style="font-size: 14px; line-height: 1.35;">
                                    ${escapeHtml(t.nama_tahapan)}
                                </h6>
                                ${desc}
                            </div>
                        </div>
                    </div>
                `;

                $('#detailAlurListCards').append(cardHtml);
            });
        }).fail(function() {
            $('#detailAlurLoading').addClass('d-none');
            Swal.fire('Gagal', 'Tidak dapat memuat rincian alur tahapan.', 'error');
        });
    }

    function bukaEditDariDetail() {
        $('#modalDetailAlurTahapan').modal('hide');
        if (activeDetailBomKey) {
            setTimeout(() => editAlurBom(activeDetailBomKey), 300);
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
