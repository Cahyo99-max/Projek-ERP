{{-- MODAL LOOKUP CARI TENDER / PROJECT BOM --}}
<div class="modal fade bom-modal-secondary" id="modalLookupTenderBom" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 950px;">
        <div class="modal-content bom-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header p-3 p-md-3.5 border-0 position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%);">
                <!-- Ambient Glow -->
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-briefcase-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-extrabold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Cari & Pilih Tender / Project</h5>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 11.5px;">Hubungkan Resep BOM dengan Kontrak Tender atau Project Make-to-Order</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body bom-modal-body p-3 p-md-3.5" style="background: var(--bg-main, #0f172a);">
                
                {{-- Search Bar & Action Bar --}}
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-12 col-md-8">
                        <div class="input-group shadow-xs rounded-pill overflow-hidden" style="border: 1px solid var(--border-color, rgba(255,255,255,0.12)); background: var(--bg-card, #1e293b);">
                            <span class="input-group-text border-0 bg-transparent ps-3.5">
                                <i class="bi bi-search fs-6 text-teal" style="color: #14b8a6;"></i>
                            </span>
                            <input type="text" id="searchTenderBomInput" class="form-control border-0 bg-transparent ps-1 fs-6"
                                   placeholder="Ketik nomor kontrak, nama tender, atau instansi/customer..." autocomplete="off" style="color: var(--text-main, #fff);">
                            <button type="button" class="btn btn-link text-muted border-0 pe-3 d-none" id="btnClearSearchLookupTenderBom" onclick="$('#searchTenderBomInput').val('').trigger('input').focus();">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-xs"
                                onclick="handlePilihTenderLookup('', '', 'Tanpa Tender (Produksi Standar Mandiri)', '')" style="font-size: 12.5px;">
                            <i class="bi bi-dash-circle text-warning"></i>
                            <span>Tanpa Tender (Reset)</span>
                        </button>
                    </div>
                </div>

                {{-- Result Table Wrapper --}}
                <div class="rounded-4 border shadow-xs overflow-hidden" 
                     style="max-height: 480px; overflow-y: auto !important; -webkit-overflow-scrolling: touch; background: var(--bg-card, #1e293b); border-color: rgba(255,255,255,0.08) !important;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: var(--text-main, #e2e8f0); min-width: 650px;">
                            <thead class="small text-uppercase fw-bold sticky-top" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(4px); font-size: 11px; letter-spacing: 0.5px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                                <tr>
                                    <th style="width: 45px;" class="text-center py-2.5">No</th>
                                    <th class="py-2.5">Nomor Kontrak / Proyek</th>
                                    <th class="py-2.5">Nama Tender & Instansi</th>
                                    <th class="py-2.5 text-end">Nilai Estimasi</th>
                                    <th class="py-2.5 text-center">Deadline</th>
                                    <th class="py-2.5 text-center">Status</th>
                                    <th style="width: 100px;" class="text-center py-2.5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyLookupTenderBom">
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm text-teal me-2" role="status" style="color: #14b8a6;"></div>
                                        Memuat daftar Tender / Project...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer bom-modal-footer justify-content-between p-3 border-0" 
                 style="background: var(--bg-card, #1e293b); border-top: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                <small class="text-theme-muted" style="font-size: 11.5px;">
                    <i class="bi bi-info-circle me-1" style="color: #14b8a6;"></i>Pilih tender terkait untuk memudahkan pelacakan SPK & HPP berbasis proyek.
                </small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeTenderFormContext = 'input'; // 'input' or 'edit'
    let searchTenderBomTimeout = null;

    function bukaModalLookupTenderBom(context = 'input') {
        activeTenderFormContext = context;
        $('#searchTenderBomInput').val('');
        $('#btnClearSearchLookupTenderBom').addClass('d-none');
        $('#modalLookupTenderBom').modal('show');
        loadTenderLookupBomData('');
        setTimeout(() => $('#searchTenderBomInput').focus(), 350);
    }

    function clearTenderBom(context = 'input') {
        if (context === 'edit') {
            $('#edit_tender_id').val('');
            $('#edit_tender_display').val('');
            $('#btn_clear_tender_edit').addClass('d-none');
        } else {
            $('#inp_tender_id').val('');
            $('#inp_tender_display').val('');
            $('#btn_clear_tender_input').addClass('d-none');
        }
    }

    function loadTenderLookupBomData(search = '') {
        $('#tbodyLookupTenderBom').html(`
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status" style="color: #14b8a6;"></div>
                    Mencari data tender...
                </td>
            </tr>
        `);

        $.ajax({
            url: "{{ route('produksi.bom.search-tender') }}",
            method: 'GET',
            data: { q: search },
            success: function(res) {
                if (!Array.isArray(res) || res.length === 0) {
                    $('#tbodyLookupTenderBom').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada data tender yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    `);
                    return;
                }

                let rows = '';
                res.forEach((t, idx) => {
                    const noKontrakSafe = window.escapeHtmlBom ? window.escapeHtmlBom(t.nomor_kontrak) : t.nomor_kontrak;
                    const namaTenderSafe = window.escapeHtmlBom ? window.escapeHtmlBom(t.nama_tender) : t.nama_tender;
                    const custNameSafe = window.escapeHtmlBom ? window.escapeHtmlBom(t.customer_name) : t.customer_name;
                    
                    let statusBadge = '<span class="badge rounded-pill px-2 py-0.5" style="background: rgba(148, 163, 184, 0.2); color: #94a3b8; font-size: 10px;">' + (t.status || 'Draft') + '</span>';
                    if (t.status === 'Won' || t.status === 'Menang' || t.status === 'Running' || t.status === 'Active') {
                        statusBadge = '<span class="badge rounded-pill px-2 py-0.5 font-monospace" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10px; border: 1px solid rgba(16, 185, 129, 0.3);"><i class="bi bi-check-circle me-1"></i>' + (t.status) + '</span>';
                    } else if (t.status === 'Submitted' || t.status === 'Proses') {
                        statusBadge = '<span class="badge rounded-pill px-2 py-0.5 font-monospace" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; font-size: 10px; border: 1px solid rgba(59, 130, 246, 0.3);">' + (t.status) + '</span>';
                    }

                    rows += `
                        <tr style="cursor: pointer;" onclick="handlePilihTenderLookup('${t.id}', '${noKontrakSafe}', '${namaTenderSafe}', '${custNameSafe}')">
                            <td class="text-center font-monospace small text-muted">${idx + 1}</td>
                            <td>
                                <span class="badge font-monospace px-2 py-1 rounded-2" style="background: rgba(20, 184, 166, 0.15); color: #14b8a6; border: 1px solid rgba(20, 184, 166, 0.3); font-size: 11px;">
                                    <i class="bi bi-hash me-0.5"></i>${noKontrakSafe}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width: 280px; font-size: 13px;" title="${namaTenderSafe}">${namaTenderSafe}</div>
                                <div class="small text-muted text-truncate" style="max-width: 280px; font-size: 11.5px;"><i class="bi bi-building me-1"></i>${custNameSafe}</div>
                            </td>
                            <td class="text-end font-monospace fw-semibold" style="font-size: 12.5px; color: #38bdf8;">
                                ${t.nilai_fmt}
                            </td>
                            <td class="text-center font-monospace small text-muted" style="font-size: 11.5px;">
                                ${t.deadline}
                            </td>
                            <td class="text-center">
                                ${statusBadge}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-teal rounded-pill px-3 py-1 fw-semibold shadow-xs" 
                                        style="background: #0d9488; color: #fff; font-size: 11.5px;"
                                        onclick="event.stopPropagation(); handlePilihTenderLookup('${t.id}', '${noKontrakSafe}', '${namaTenderSafe}', '${custNameSafe}')">
                                    <i class="bi bi-check2-circle me-1"></i>Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#tbodyLookupTenderBom').html(rows);
            },
            error: function() {
                $('#tbodyLookupTenderBom').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-2 d-block mb-2"></i>
                            Gagal memuat data tender. Harap periksa koneksi atau coba lagi.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function handlePilihTenderLookup(id, nomorKontrak, namaTender, customerName) {
        let displayVal = '';
        if (id) {
            displayVal = `[${nomorKontrak || 'TENDER'}] ${namaTender}`;
        }

        if (activeTenderFormContext === 'edit') {
            $('#edit_tender_id').val(id || '');
            $('#edit_tender_display').val(displayVal);
            if (id) {
                $('#btn_clear_tender_edit').removeClass('d-none');
            } else {
                $('#btn_clear_tender_edit').addClass('d-none');
            }
        } else {
            $('#inp_tender_id').val(id || '');
            $('#inp_tender_display').val(displayVal);
            if (id) {
                $('#btn_clear_tender_input').removeClass('d-none');
            } else {
                $('#btn_clear_tender_input').addClass('d-none');
            }
        }

        $('#modalLookupTenderBom').modal('hide');
    }

    $(document).ready(function() {
        $('#searchTenderBomInput').on('input', function() {
            const val = $(this).val().trim();
            if (val.length > 0) {
                $('#btnClearSearchLookupTenderBom').removeClass('d-none');
            } else {
                $('#btnClearSearchLookupTenderBom').addClass('d-none');
            }

            clearTimeout(searchTenderBomTimeout);
            searchTenderBomTimeout = setTimeout(function() {
                loadTenderLookupBomData(val);
            }, 300);
        });
    });
</script>
@endpush
