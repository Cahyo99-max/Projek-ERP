{{-- MODAL LOOKUP RESEP BOM PRODUKSI (MASTER TAHAPAN) --}}
<div class="modal fade" id="modalLookupBomTahapan" tabindex="-1" aria-labelledby="modalLookupBomTahapanLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- HEADER (CINEMATIC BLUE GRADIENT) --}}
            <div class="modal-header py-3 px-3 px-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%); flex-shrink: 0;">
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 gap-md-3 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-diagram-2-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-extrabold text-white mb-0" id="modalLookupBomTahapanLabel" style="letter-spacing: -0.2px;">
                                Pilih Resep BOM Produksi
                            </h5>
                            @if(!empty($isCabang))
                                <span class="badge rounded-pill bg-white text-success fw-bold px-2.5 py-0.5 shadow-xs" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ \Illuminate\Support\Str::startsWith($activeBranchName, 'Cabang') ? $activeBranchName : 'Cabang ' . $activeBranchName }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-white text-primary fw-bold px-2.5 py-0.5 shadow-xs" style="font-size: 11px;">
                                    <i class="bi bi-building me-1"></i>PT Pusat
                                </span>
                            @endif
                        </div>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 12px;">
                            Pilih resep BOM spesifik yang akan dikonfigurasi alur tahapannya (Wajib Diisi).
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body spk-modal-body p-3 p-md-4 overflow-y-auto" style="min-height: 0;">

                {{-- ── SEARCH BAR RESEP BOM ── --}}
                <div class="input-group mb-2.5 shadow-xs">
                    <span class="input-group-text spk-input-group-text rounded-start-3 border" style="background: var(--spk-card-bg, #ffffff);">
                        <i class="bi bi-search text-primary"></i>
                    </span>
                    <input type="text" id="inputSearchBomLookupTahapan"
                           class="form-control spk-input border-start-0 rounded-end-3 py-2"
                           placeholder="Cari Kode BOM / Produk Jadi..."
                           autocomplete="off"
                           style="font-size: 13px;"
                           oninput="cariBomLookupTahapanDebounced(this.value)">
                </div>

                {{-- ── TABEL DAFTAR BOM (SERVER-SIDE AJAX LOADED) ── --}}
                <div class="batch-table-scroll-container w-100 position-relative" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 spk-table text-nowrap" id="tabelLookupBomTahapan">
                        <thead class="sticky-top bg-body">
                            <tr class="small text-uppercase fw-bold" style="border-bottom: 2px solid var(--spk-border-color); background: var(--spk-table-head-bg, #f1f5f9);">
                                <th class="text-center py-2.5 px-2" style="width: 50px;">No</th>
                                <th class="py-2.5 px-3">Kode & Nama Resep BOM</th>
                                <th class="py-2.5 px-3">Barang Jadi (Produk)</th>
                                <th class="text-center py-2.5 px-2" style="width: 125px;">Status Alur</th>
                                <th class="text-center py-2.5 px-2" style="width: 95px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupBomTahapan">
                            {{-- Baris BOM diinjeksi via AJAX Server-Side --}}
                        </tbody>
                    </table>

                    {{-- Loading Spinner State --}}
                    <div id="loadingBomLookupTahapan" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="spk-text-muted small mb-0">Mencari resep BOM...</p>
                    </div>

                    {{-- Empty Search State --}}
                    <div id="emptySearchBomLookupTahapan" class="text-center py-5 d-none">
                        <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="background: rgba(37,99,235,0.08);">
                            <i class="bi bi-search fs-3 text-primary"></i>
                        </div>
                        <h6 class="fw-bold spk-text-main mb-1">Resep BOM Tidak Ditemukan</h6>
                        <p class="spk-text-muted small mb-0">Coba ubah kata kunci pencarian Anda.</p>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer spk-modal-footer d-flex justify-content-between align-items-center py-2.5 px-3 px-md-4 border-0"
                 style="background: var(--spk-card-bg, #1e293b); border-top: 1px solid var(--spk-border-color) !important; flex-shrink: 0;">
                <small class="spk-text-muted" style="font-size: 11.5px;">
                    Ditemukan: <strong class="spk-text-main" id="countBomLookupTahapan">0</strong> Resep BOM
                    @if(!empty($isCabang))
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill ms-1 px-2 py-0.5" style="font-size: 10px;">{{ \Illuminate\Support\Str::startsWith($activeBranchName, 'Cabang') ? $activeBranchName : 'Cabang ' . $activeBranchName }}</span>
                    @else
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill ms-1 px-2 py-0.5" style="font-size: 10px;">PT Pusat</span>
                    @endif
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let lookupBomDebounceTimer = null;

    function alertBomTahapanSudahAda(namaBom, kodeBom) {
        Swal.fire({
            icon: 'warning',
            title: 'Data BOM Sudah Pernah Diinput',
            html: `Alur tahapan untuk resep BOM <b>${namaBom}</b> (${kodeBom}) sudah pernah di-input sebelumnya!<br><br>` +
                  `• Silakan gunakan tombol <b>Edit Alur</b> pada daftar tabel jika ingin mengubah alur proses.<br>` +
                  `• Jika ingin membuat alur baru dari awal, silakan <b>hapus</b> data alur BOM ini terlebih dahulu.`,
            confirmButtonColor: '#2563eb',
            confirmButtonText: '<i class="bi bi-check2 me-1"></i> Mengerti'
        });
    }

    function loadBomLookupAjax(query = '') {
        $('#loadingBomLookupTahapan').removeClass('d-none');
        $('#tbodyLookupBomTahapan').addClass('d-none');
        $('#emptySearchBomLookupTahapan').addClass('d-none');

        $.ajax({
            url: '{{ route("produksi.master-tahapan.search-bom") }}',
            method: 'GET',
            data: { q: query },
            dataType: 'json',
            success: function(res) {
                $('#loadingBomLookupTahapan').addClass('d-none');
                const boms = res.boms || [];
                $('#countBomLookupTahapan').text(res.count || 0);

                if (boms.length === 0) {
                    $('#tbodyLookupBomTahapan').empty().addClass('d-none');
                    $('#emptySearchBomLookupTahapan').removeClass('d-none');
                    return;
                }

                let rows = '';
                boms.forEach(function(b, idx) {
                    const safeNama = $('<div>').text(b.nama_bom).html();
                    const safeKode = $('<div>').text(b.kode_bom).html();
                    const safeBarang = b.barang_nama ? $('<div>').text(b.barang_nama).html() : '';
                    const safeBarangKode = b.barang_kode ? $('<div>').text(b.barang_kode).html() : '';

                    const barangHtml = safeBarang
                        ? `<div class="d-flex align-items-center gap-1.5 spk-text-main small">
                               <i class="bi bi-box-seam text-primary"></i>
                               <span class="fw-semibold">${safeBarang}</span>
                               ${safeBarangKode ? `<span class="spk-text-muted">(${safeBarangKode})</span>` : ''}
                           </div>`
                        : `<span class="spk-text-muted fst-italic small">- Tanpa barang jadi -</span>`;

                    const isSudahAda = b.has_tahapan === true;

                    const statusHtml = isSudahAda
                        ? `<span class="badge rounded-pill px-2.5 py-1 text-dark fw-bold shadow-xs" style="background: #f59e0b; font-size: 11px;">
                               <i class="bi bi-exclamation-triangle-fill me-1"></i>Sudah Ada Alur
                           </span>`
                        : `<span class="badge rounded-pill px-2.5 py-1 text-white fw-bold shadow-xs" style="background: #10b981; font-size: 11px;">
                               <i class="bi bi-check-circle-fill me-1"></i>Tersedia
                           </span>`;

                    const btnActionHtml = isSudahAda
                        ? `<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 fw-bold shadow-xs" 
                                   onclick="event.stopPropagation(); alertBomTahapanSudahAda('${safeNama.replace(/'/g, "\\'")}', '${safeKode.replace(/'/g, "\\'")}')"
                                   style="font-size: 11.5px;">
                               <i class="bi bi-exclamation-circle me-1"></i>Sudah Ada
                           </button>`
                        : `<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs" 
                                   onclick="event.stopPropagation(); pilihBomTahapan('${b.id}', '${safeNama.replace(/'/g, "\\'")}', '${safeKode.replace(/'/g, "\\'")}', '${safeBarang.replace(/'/g, "\\'")}')"
                                   style="background: linear-gradient(135deg, #2563eb, #1d4ed8); font-size: 12px;">
                               <i class="bi bi-check-lg me-1"></i>Pilih
                           </button>`;

                    const rowClickAction = isSudahAda
                        ? `alertBomTahapanSudahAda('${safeNama.replace(/'/g, "\\'")}', '${safeKode.replace(/'/g, "\\'")}')`
                        : `pilihBomTahapan('${b.id}', '${safeNama.replace(/'/g, "\\'")}', '${safeKode.replace(/'/g, "\\'")}', '${safeBarang.replace(/'/g, "\\'")}')`;

                    const rowBgStyle = isSudahAda
                        ? `style="background: rgba(245, 158, 11, 0.03);"`
                        : ``;

                    rows += `
                        <tr class="row-lookup-bom cursor-pointer" 
                            data-id="${b.id}"
                            data-nama="${safeNama}"
                            data-kode="${safeKode}"
                            data-barang="${safeBarang}"
                            ${rowBgStyle}
                            onclick="${rowClickAction}">
                            <td class="text-center fw-bold spk-text-muted" style="font-size: 12px;">${idx + 1}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" 
                                          style="background: linear-gradient(135deg, #1d4ed8, #2563eb); font-size: 11px;">
                                        ${safeKode}
                                    </span>
                                    <strong class="spk-text-main fw-bold">${safeNama}</strong>
                                </div>
                            </td>
                            <td>${barangHtml}</td>
                            <td class="text-center">${statusHtml}</td>
                            <td class="text-center">${btnActionHtml}</td>
                        </tr>
                    `;
                });

                $('#tbodyLookupBomTahapan').html(rows).removeClass('d-none');
            },
            error: function() {
                $('#loadingBomLookupTahapan').addClass('d-none');
                $('#tbodyLookupBomTahapan').empty().addClass('d-none');
                $('#emptySearchBomLookupTahapan').removeClass('d-none');
            }
        });
    }

    function cariBomLookupTahapanDebounced(keyword) {
        clearTimeout(lookupBomDebounceTimer);
        lookupBomDebounceTimer = setTimeout(function() {
            loadBomLookupAjax(keyword);
        }, 300);
    }

    // Modal Stack & Auto Load saat modal dibuka
    $(document).on('show.bs.modal', '#modalLookupBomTahapan', function () {
        const zIndex = 1060;
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);

        $('#inputSearchBomLookupTahapan').val('');
        loadBomLookupAjax('');
    });

    $(document).on('hidden.bs.modal', '#modalLookupBomTahapan', function () {
        if ($('#modalTambahTahapan').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });
</script>
@endpush
