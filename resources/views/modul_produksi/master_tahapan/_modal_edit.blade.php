{{-- MODAL EDIT BATCH ALUR TAHAPAN PRODUKSI (PER RESEP BOM) --}}
<div class="modal fade spk-modal-primary" id="modalEditAlurTahapan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" style="max-width: 1250px;">
        <div class="modal-content spk-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- HEADER (AMBER/ORANGE GRADIENT) --}}
            <div class="modal-header py-3 px-3 px-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%); flex-shrink: 0;">
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 gap-md-3 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-pencil-square fs-4 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-extrabold text-white mb-0" id="edit_alur_modal_title" style="letter-spacing: -0.2px;">
                                Edit Alur Tahapan Produksi
                            </h5>
                            @if(!empty($isCabang))
                                <span class="badge rounded-pill bg-white text-success fw-bold px-2.5 py-1 shadow-xs" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ \Illuminate\Support\Str::startsWith($activeBranchName, 'Cabang') ? $activeBranchName : 'Cabang ' . $activeBranchName }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-white text-primary fw-bold px-2.5 py-1 shadow-xs" style="font-size: 11px;">
                                    <i class="bi bi-building me-1"></i>PT Pusat
                                </span>
                            @endif
                        </div>
                        <small class="text-white-50 d-block mt-0.5" id="edit_alur_modal_subtitle" style="font-size: 12px;">
                            Edit konfigurasi alur tahapan kerja & SOP produksi.
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- FORM --}}
            <form id="formEditAlurTahapan" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1 overflow-hidden" style="min-height: 0;">
                @csrf
                <input type="hidden" id="edit_alur_bom_key" value="">

                <div class="modal-body spk-modal-body p-2.5 p-md-4 overflow-y-auto" style="min-height: 0; max-width: 100%; overflow-x: hidden;">
                    
                    {{-- ── SECTION 1: BOM INFO BANNER ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5 w-100" style="border: 1px solid var(--spk-border-color) !important; background: rgba(245,158,11,0.04); max-width: 100%; min-width: 0; box-sizing: border-box; overflow: hidden;">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2" style="min-width: 0; max-width: 100%;">
                            <div style="min-width: 0; max-width: 100%;">
                                <div class="d-flex align-items-center gap-2 flex-wrap" style="min-width: 0; max-width: 100%;">
                                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm text-truncate" style="background: linear-gradient(135deg, #d97706, #f59e0b); font-size: 13px; border: 1px solid rgba(255,255,255,0.25); max-width: 100%;" id="edit_badge_bom_nama">
                                        --
                                    </span>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold spk-text-main text-truncate" style="background: var(--spk-bg-tertiary, #f1f5f9); border: 1px solid var(--spk-border-color, #cbd5e1); font-size: 11px; max-width: 100%;" id="edit_badge_bom_kode">
                                        --
                                    </span>
                                </div>
                                <small class="spk-text-muted d-block mt-1 text-break" id="edit_txt_bom_desc" style="font-size: 11.5px;">
                                    Alur tahapan khusus untuk resep produk ini.
                                </small>
                            </div>
                            <div class="p-2 px-3 rounded-3 mt-1 mt-md-0" style="background: var(--spk-bg-light, rgba(255,255,255,0.03)); border: 1px dashed var(--spk-border-color); max-width: 100%;">
                                <small class="spk-text-muted" style="font-size: 11.5px;">
                                    <i class="bi bi-info-circle text-warning me-1"></i>Perubahan alur tersimpan permanen.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 2: DAFTAR BARIS TAHAPAN KERJA ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm p-2.5 p-md-3.5 w-100" style="border: 1px solid var(--spk-border-color) !important; max-width: 100%; min-width: 0; box-sizing: border-box; overflow: hidden;">
                        
                        {{-- Action Toolbar --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom w-100" style="border-color: var(--spk-border-color) !important; min-width: 0; max-width: 100%;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1.5 d-flex align-items-center justify-content-center text-white" style="width: 26px; height: 26px; background: rgba(245,158,11,0.2); border: 1px solid rgba(245,158,11,0.4);">
                                    <i class="bi bi-list-check" style="font-size: 13px;"></i>
                                </span>
                                <span class="fw-bold spk-text-main small text-uppercase" style="letter-spacing: 0.5px;">Alur Tahapan Proses</span>
                                <span class="badge rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" style="background: #f59e0b; font-size: 11px;" id="badgeTotalEditBarisTahapan">0 / 50</span>
                            </div>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 shadow-xs" onclick="tambahBarisEditTahapanPreset()" title="Tambah alur standar" style="font-size: 12px;">
                                    <i class="bi bi-magic me-1"></i> <span class="d-none d-sm-inline">Tambah </span>Preset Standar
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold shadow-sm" onclick="tambahBarisEditTahapan()" style="font-size: 12px;">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris
                                </button>
                            </div>
                        </div>

                        {{-- Petunjuk Geser di Layar HP --}}
                        <div class="d-flex d-md-none align-items-center justify-content-between mb-2 px-1 spk-text-muted" style="font-size: 11px;">
                            <span><i class="bi bi-arrow-left-right text-warning me-1"></i>Geser ke samping untuk detail deskripsi & foto SOP</span>
                        </div>

                        {{-- Dynamic Table / Rows --}}
                        <div class="batch-table-scroll-container w-100">
                            <table class="table table-hover align-middle mb-0" id="tabelEditBatchTahapan">
                                <colgroup>
                                    <col class="batch-col-no">
                                    <col class="batch-col-foto">
                                    <col class="batch-col-nama">
                                    <col class="batch-col-desk">
                                    <col class="batch-col-aksi">
                                </colgroup>
                                <thead>
                                    <tr class="small text-uppercase fw-bold" style="border-bottom: 2px solid var(--spk-border-color); background: var(--spk-table-head-bg, #f1f5f9);">
                                        <th class="text-center py-2.5 px-1">No</th>
                                        <th class="text-center py-2.5 px-2">Foto SOP</th>
                                        <th class="py-2.5 px-2">Nama Tahapan Proses <span class="text-danger">*</span></th>
                                        <th class="py-2.5 px-2">Deskripsi / Instruksi Kerja <span class="spk-text-muted fw-normal">(Opsional)</span></th>
                                        <th class="text-center py-2.5 px-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerBarisEditTahapan">
                                    {{-- Dynamic Rows Injected Here --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Empty State --}}
                        <div id="emptyStateEditTahapan" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="background: rgba(245,158,11,0.08);">
                                <i class="bi bi-diagram-3 fs-1 text-warning"></i>
                            </div>
                            <h6 class="fw-bold spk-text-main mb-1">Belum Ada Baris Tahapan</h6>
                            <p class="spk-text-muted small mb-3">Klik tombol di bawah untuk menambah langkah alur kerja.</p>
                            <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-bold" onclick="tambahBarisEditTahapan()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                            </button>
                        </div>
                    </div>
                </div>

                {{-- FOOTER RESPONSIF --}}
                <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-between align-items-center gap-2 p-3 p-md-3.5 border-0 spk-modal-footer-btns" 
                     style="background: var(--spk-card-bg, #1e293b); border-top: 1px solid var(--spk-border-color) !important; flex-shrink: 0;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> Batal
                    </button>
                    <button type="button" id="btnSimpanEditBatchTahapan" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-md" onclick="simpanBatchEditTahapan()">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan (<span id="lblCountSimpanEdit">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editTahapanRowCount = 0;
    let editCompressedBlobs = {}; // key: rowIndex -> Blob

    function updateEditTahapanCounter() {
        const total = $('#containerBarisEditTahapan tr.row-edit-tahapan').length;
        $('#badgeTotalEditBarisTahapan').text(`${total} / 50`);
        $('#lblCountSimpanEdit').text(total);

        if (total === 0) {
            $('#emptyStateEditTahapan').removeClass('d-none');
            $('#tabelEditBatchTahapan').addClass('d-none');
            $('#btnSimpanEditBatchTahapan').prop('disabled', true);
        } else {
            $('#emptyStateEditTahapan').addClass('d-none');
            $('#tabelEditBatchTahapan').removeClass('d-none');
            $('#btnSimpanEditBatchTahapan').prop('disabled', false);
        }

        // Re-numbering
        $('#containerBarisEditTahapan tr.row-edit-tahapan').each(function(index) {
            $(this).find('.badge-row-number').text(index + 1);
            $(this).find('.inp-row-urutan').val(index + 1);
        });
    }

    function editAlurBom(bomKey) {
        $('#edit_alur_bom_key').val(bomKey);
        $('#containerBarisEditTahapan').empty();
        editCompressedBlobs = {};
        editTahapanRowCount = 0;

        $('#modalEditAlurTahapan').modal('show');
        $('#btnSimpanEditBatchTahapan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memuat...');

        $.get(`/produksi/master-tahapan/bom/${bomKey}`, function(res) {
            $('#btnSimpanEditBatchTahapan').prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan (<span id="lblCountSimpanEdit">0</span>)');

            const info = res.bom_info;
            const tahapans = res.tahapans || [];

            $('#edit_badge_bom_nama').text(info.nama_bom);
            $('#edit_badge_bom_kode').text(info.kode_bom);
            $('#edit_txt_bom_desc').text(info.nama_barang ? `Produk Jadi: ${info.nama_barang}` : 'Template Standar Umum untuk semua resep BOM');

            if (tahapans.length === 0) {
                tambahBarisEditTahapan();
            } else {
                tahapans.forEach(t => {
                    tambahBarisEditTahapan(t);
                });
            }
        }).fail(function() {
            $('#modalEditAlurTahapan').modal('hide');
            Swal.fire('Gagal', 'Tidak dapat memuat alur tahapan BOM.', 'error');
        });
    }

    function tambahBarisEditTahapan(data = null) {
        const totalCurrent = $('#containerBarisEditTahapan tr.row-edit-tahapan').length;
        if (totalCurrent >= 50) {
            Swal.fire('Batas Maksimal', 'Maksimal 50 tahapan dalam satu alur BOM.', 'warning');
            return;
        }

        editTahapanRowCount++;
        const rowId = editTahapanRowCount;
        const dbId = data && data.id ? data.id : '';
        const namaVal = data ? (data.nama_tahapan || '') : '';
        const deskVal = data ? (data.deskripsi || '') : '';
        const hasExistingPhoto = data && data.foto_url;
        const existingPhotoUrl = hasExistingPhoto ? data.foto_url : '';

        const rowHtml = `
            <tr class="row-edit-tahapan" id="row_edit_tahapan_${rowId}" data-row-id="${rowId}">
                {{-- No Urutan --}}
                <td class="text-center">
                    <span class="badge rounded-circle p-2 badge-row-number spk-text-main fw-bold" style="width: 28px; height: 28px; font-size: 11px; background: rgba(100,116,139,0.12); border: 1px solid var(--spk-border-color);">1</span>
                    <input type="hidden" name="tahapans[${rowId}][urutan]" class="inp-row-urutan" value="1">
                    <input type="hidden" name="tahapans[${rowId}][id]" class="inp-row-id" value="${dbId}">
                    <input type="hidden" name="tahapans[${rowId}][hapus_foto]" id="inp_hapus_foto_row_${rowId}" value="0">
                </td>

                {{-- Foto SOP Upload & Preview Thumbnail --}}
                <td class="text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        {{-- Trigger Box --}}
                        <div id="box_edit_foto_thumb_${rowId}" class="rounded-3 border d-flex flex-column align-items-center justify-content-center cursor-pointer shadow-xs position-relative overflow-hidden ${hasExistingPhoto ? 'd-none' : ''}" 
                             style="width: 50px; height: 50px; background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.25) !important;"
                             onclick="$('#inp_edit_file_row_${rowId}').click()" title="Klik untuk unggah foto SOP">
                            <i class="bi bi-camera-fill text-warning fs-5"></i>
                            <span style="font-size: 9px;" class="spk-text-muted mt-0.5">Foto</span>
                        </div>

                        {{-- Preview Image Container --}}
                        <div id="container_edit_foto_preview_${rowId}" class="${hasExistingPhoto ? '' : 'd-none'} position-relative">
                            <img id="img_edit_preview_row_${rowId}" src="${existingPhotoUrl}" class="rounded-3 border shadow-xs cursor-pointer" 
                                 style="width: 50px; height: 50px; object-fit: cover;" 
                                 onclick="previewFotoTahapan(this.src, $('#inp_edit_nama_row_${rowId}').val())" title="Klik untuk memperbesar">
                            <button type="button" class="btn btn-danger btn-sm p-0 rounded-circle position-absolute d-flex align-items-center justify-content-center" 
                                    style="top: -6px; right: -6px; width: 18px; height: 18px; font-size: 10px;" 
                                    onclick="hapusFotoBarisEdit(${rowId})" title="Hapus foto">
                                <i class="bi bi-x"></i>
                            </button>
                            <small id="badge_edit_size_row_${rowId}" class="badge bg-success bg-opacity-75 rounded-pill p-0 px-1 mt-0.5 d-block" style="font-size: 8px;">${hasExistingPhoto ? 'Foto Aktif' : '0 KB'}</small>
                        </div>

                        <input type="file" id="inp_edit_file_row_${rowId}" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="handleFotoBarisEditChange(this, ${rowId})">
                    </div>
                </td>

                {{-- Nama Tahapan --}}
                <td>
                    <input type="text" name="tahapans[${rowId}][nama_tahapan]" id="inp_edit_nama_row_${rowId}"
                           class="form-control form-control-sm spk-input rounded-3 fw-semibold inp-edit-nama" 
                           placeholder="cth: Pemotongan Bahan (Cutting)" value="${escapeHtml(namaVal)}" required maxlength="100" autocomplete="off">
                </td>

                {{-- Deskripsi / Catatan Kerja --}}
                <td>
                    <input type="text" name="tahapans[${rowId}][deskripsi]" id="inp_edit_desk_row_${rowId}"
                           class="form-control form-control-sm spk-input rounded-3 text-muted inp-edit-desk" 
                           placeholder="Catatan pengerjaan / instruksi khusus..." value="${escapeHtml(deskVal)}" maxlength="255" autocomplete="off">
                </td>

                {{-- Tombol Hapus Baris --}}
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1.5 shadow-xs" onclick="hapusBarisEditTahapan(${rowId})" title="Hapus baris ini">
                        <i class="bi bi-trash" style="font-size: 13px;"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#containerBarisEditTahapan').append(rowHtml);
        updateEditTahapanCounter();
    }

    function tambahBarisEditTahapanPreset() {
        if (typeof PRESET_ALUR_GARMEN !== 'undefined') {
            PRESET_ALUR_GARMEN.forEach(p => tambahBarisEditTahapan({ nama_tahapan: p.nama, deskripsi: p.desk }));
        } else {
            tambahBarisEditTahapan();
        }
    }

    function hapusBarisEditTahapan(rowId) {
        $(`#row_edit_tahapan_${rowId}`).remove();
        delete editCompressedBlobs[rowId];
        updateEditTahapanCounter();
    }

    async function handleFotoBarisEditChange(input, rowId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const origSizeKb = (file.size / 1024).toFixed(1);

            try {
                const blob = await compressImageFile(file);
                editCompressedBlobs[rowId] = blob;
                const compSizeKb = (blob.size / 1024).toFixed(1);

                const url = URL.createObjectURL(blob);
                $(`#img_edit_preview_row_${rowId}`).attr('src', url);
                $(`#badge_edit_size_row_${rowId}`).text(`${compSizeKb} KB`);
                $(`#container_edit_foto_preview_${rowId}`).removeClass('d-none');
                $(`#box_edit_foto_thumb_${rowId}`).addClass('d-none');
                $(`#inp_hapus_foto_row_${rowId}`).val('0');
            } catch (e) {
                editCompressedBlobs[rowId] = file;
                const url = URL.createObjectURL(file);
                $(`#img_edit_preview_row_${rowId}`).attr('src', url);
                $(`#badge_edit_size_row_${rowId}`).text(`${origSizeKb} KB`);
                $(`#container_edit_foto_preview_${rowId}`).removeClass('d-none');
                $(`#box_edit_foto_thumb_${rowId}`).addClass('d-none');
                $(`#inp_hapus_foto_row_${rowId}`).val('0');
            }
        }
    }

    function hapusFotoBarisEdit(rowId) {
        delete editCompressedBlobs[rowId];
        $(`#inp_edit_file_row_${rowId}`).val('');
        $(`#img_edit_preview_row_${rowId}`).attr('src', '');
        $(`#container_edit_foto_preview_${rowId}`).addClass('d-none');
        $(`#box_edit_foto_thumb_${rowId}`).removeClass('d-none');
        $(`#inp_hapus_foto_row_${rowId}`).val('1');
    }

    function simpanBatchEditTahapan() {
        const rows = $('#containerBarisEditTahapan tr.row-edit-tahapan');
        if (rows.length === 0) {
            Swal.fire('Form Kosong', 'Silakan tambahkan minimal 1 baris tahapan produksi.', 'warning');
            return;
        }

        let isValid = true;
        let emptyIndex = -1;

        rows.each(function(idx) {
            const val = $(this).find('.inp-edit-nama').val().trim();
            if (!val) {
                isValid = false;
                emptyIndex = idx + 1;
                $(this).find('.inp-edit-nama').focus().addClass('border-danger');
                return false;
            }
        });

        if (!isValid) {
            Swal.fire('Nama Tahapan Kosong', `Baris ke-${emptyIndex}: Nama tahapan produksi wajib diisi.`, 'warning');
            return;
        }

        const bomKey = $('#edit_alur_bom_key').val();
        const $btn = $('#btnSimpanEditBatchTahapan');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Perubahan...');

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        rows.each(function(idx) {
            const rowId = $(this).data('row-id');
            const dbId = $(this).find('.inp-row-id').val();
            const nama = $(this).find('.inp-edit-nama').val().trim();
            const desk = $(this).find('.inp-edit-desk').val().trim();
            const hapusFoto = $(this).find(`#inp_hapus_foto_row_${rowId}`).val();
            const urutan = idx + 1;

            if (dbId) {
                formData.append(`tahapans[${idx}][id]`, dbId);
            }
            formData.append(`tahapans[${idx}][urutan]`, urutan);
            formData.append(`tahapans[${idx}][nama_tahapan]`, nama);
            if (desk) {
                formData.append(`tahapans[${idx}][deskripsi]`, desk);
            }
            if (hapusFoto === '1') {
                formData.append(`tahapans[${idx}][hapus_foto]`, '1');
            }

            if (editCompressedBlobs[rowId]) {
                formData.append(`tahapans[${idx}][foto]`, editCompressedBlobs[rowId], `tahapan_edit_${idx}.webp`);
            }
        });

        $.ajax({
            url: `/produksi/master-tahapan/bom/${bomKey}/batch-update`,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                // 1. Langsung reload tabel di background secara instan tanpa jeda!
                reloadTabelTahapan();

                // 2. Tutup modal & pulihkan tombol seketika
                $('#modalEditAlurTahapan').modal('hide');
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan Alur');

                // 3. Notifikasi Toast cepat, modern, dan non-blocking
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1600,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: res.message || 'Alur tahapan produksi berhasil diperbarui!'
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan Alur');
                const err = xhr.responseJSON?.errors;
                const msg = err ? Object.values(err).flat().join('<br>') : (xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui alur.');
                Swal.fire('Gagal Memperbarui', msg, 'error');
            }
        });
    }
</script>
@endpush
