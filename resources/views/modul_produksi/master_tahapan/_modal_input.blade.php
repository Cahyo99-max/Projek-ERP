{{-- MODAL TAMBAH TAHAPAN PRODUKSI (BATCH MULTI-ROW INPUT) --}}
<div class="modal fade spk-modal-primary" id="modalTambahTahapan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" style="max-width: 1250px;">
        <div class="modal-content spk-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- HEADER (CINEMATIC BLUE GRADIENT) --}}
            <div class="modal-header spk-modal-header-blue py-3 px-3 px-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #0284c7 100%); flex-shrink: 0;">
                <!-- Decorative Ambient Glow -->
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 gap-md-3 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-diagram-3-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-extrabold text-white mb-0" style="letter-spacing: -0.2px;">
                                Buat & Konfigurasi Alur Tahapan Produksi
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
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 12px;">
                            Konfigurasi alur tahapan kerja & SOP produksi.
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- FORM --}}
            <form id="formInputTahapan" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1 overflow-hidden" style="min-height: 0;">
                @csrf
                <div class="modal-body spk-modal-body p-2.5 p-md-4 overflow-y-auto" style="min-height: 0; max-width: 100%; overflow-x: hidden;">
                    
                    {{-- ── SECTION 1: PILIH RESEP BOM TERKAIT ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5 w-100" style="border: 1px solid var(--spk-border-color) !important; max-width: 100%; min-width: 0; box-sizing: border-box; overflow: hidden;">
                        <div class="row g-2 g-md-3 align-items-center" style="min-width: 0; max-width: 100%;">
                            <div class="col-12 col-md-7" style="min-width: 0; max-width: 100%;">
                                <label class="form-label fw-bold spk-text-main small mb-1">
                                    <i class="bi bi-diagram-2-fill text-primary me-1"></i> Resep BOM / Kode BOM <span class="text-danger">* (Wajib Diisi)</span>
                                </label>

                                {{-- Hidden Input Actual Value --}}
                                <input type="hidden" name="bom_id" id="inp_tah_bom_id" value="" required>

                                {{-- Input Group Picker dengan Icon Cari & Modal --}}
                                <div class="input-group shadow-xs">
                                    <div class="form-control spk-input rounded-start-3 d-flex align-items-center justify-content-between cursor-pointer py-2 px-3" 
                                         id="display_bom_selected" 
                                         onclick="bukaModalLookupBomTahapan()"
                                         style="background: var(--spk-input-bg, #ffffff); border: 1px solid var(--spk-border-color, #cbd5e1); border-right: none; min-height: 42px;"
                                         title="Klik untuk memilih resep BOM dari modal">
                                        <div class="d-flex align-items-center gap-2 text-truncate" id="lbl_bom_terpilih">
                                            <span class="spk-text-muted small fw-semibold">
                                                <i class="bi bi-exclamation-circle text-warning me-1"></i> -- Silakan Pilih Resep BOM (Wajib Diisi) --
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2 d-none" id="btn_reset_bom_terpilih" 
                                                onclick="event.stopPropagation(); resetBomTahapan();" 
                                                title="Hapus Pilihan BOM">
                                            <i class="bi bi-x-circle-fill text-danger fs-6"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-primary rounded-end-3 px-3.5 d-flex align-items-center gap-1.5 fw-bold shadow-sm" 
                                            onclick="bukaModalLookupBomTahapan()" 
                                            style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 1px solid rgba(255,255,255,0.2);"
                                            title="Cari & Pilih Resep BOM">
                                        <i class="bi bi-search"></i>
                                        <span class="d-none d-sm-inline">Pilih BOM</span>
                                    </button>
                                </div>
                                <small class="spk-text-muted d-block mt-1" style="font-size: 11px;">
                                    Pilih resep BOM yang akan dikonfigurasi alur tahapannya. Kode BOM wajib diisi agar terhubung dengan SPK.
                                </small>
                            </div>
                            <div class="col-12 col-md-5" style="min-width: 0; max-width: 100%;">
                                <div class="p-2.5 rounded-3 d-flex align-items-center gap-2 mt-1 mt-md-0" style="background: rgba(37,99,235,0.08); border: 1px dashed rgba(37,99,235,0.25); max-width: 100%;">
                                    <i class="bi bi-shield-check text-primary fs-5 flex-shrink-0"></i>
                                    <small class="spk-text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                        Dukung multi-baris alur & foto panduan SOP.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 2: DAFTAR BARIS TAHAPAN KERJA ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm p-2.5 p-md-3.5 w-100" style="border: 1px solid var(--spk-border-color) !important; max-width: 100%; min-width: 0; box-sizing: border-box; overflow: hidden;">
                        
                        {{-- Action Toolbar --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom w-100" style="border-color: var(--spk-border-color) !important; min-width: 0; max-width: 100%;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1.5 d-flex align-items-center justify-content-center text-white" style="width: 26px; height: 26px; background: rgba(37,99,235,0.25); border: 1px solid rgba(37,99,235,0.4);">
                                    <i class="bi bi-list-check" style="font-size: 13px;"></i>
                                </span>
                                <span class="fw-bold spk-text-main small text-uppercase" style="letter-spacing: 0.5px;">Alur Tahapan Proses</span>
                                <span class="badge rounded-pill px-3 py-1 text-white fw-bold shadow-xs" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); font-size: 11px;" id="badgeTotalBarisTahapan">0 / 50</span>
                            </div>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 shadow-xs" onclick="muatPresetStandarGarmen()" title="Muat 6 alur garmen standar otomatis" style="font-size: 12px;">
                                    <i class="bi bi-magic me-1"></i> <span class="d-none d-sm-inline">Muat 6 Alur </span>Preset Garmen
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 shadow-xs" onclick="bersihkanSemuaBarisTahapan()" title="Kosongkan semua baris" style="font-size: 12px;">
                                    <i class="bi bi-trash me-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="tambahBarisTahapan()" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); font-size: 12px;">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris
                                </button>
                            </div>
                        </div>

                        {{-- Petunjuk Geser di Layar HP --}}
                        <div class="d-flex d-md-none align-items-center justify-content-between mb-2 px-1 spk-text-muted" style="font-size: 11px;">
                            <span><i class="bi bi-arrow-left-right text-primary me-1"></i>Geser ke samping untuk detail deskripsi & foto SOP</span>
                        </div>

                        {{-- Dynamic Table / Rows --}}
                        <div class="batch-table-scroll-container w-100">
                            <table class="table table-hover align-middle mb-0" id="tabelInputBatchTahapan">
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
                                <tbody id="containerBarisBatchTahapan">
                                    {{-- Dynamic Rows Injected Here --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Empty State --}}
                        <div id="emptyStateBatchTahapan" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="background: rgba(37,99,235,0.08);">
                                <i class="bi bi-diagram-3 fs-1 text-primary"></i>
                            </div>
                            <h6 class="fw-bold spk-text-main mb-1">Belum Ada Baris Tahapan</h6>
                            <p class="spk-text-muted small mb-3">Klik tombol di bawah atau pilih template standar untuk mulai mengisi alur proses.</p>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold me-2" onclick="tambahBarisTahapan()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Tahapan Pertama
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3" onclick="muatPresetStandarGarmen()">
                                <i class="bi bi-magic me-1"></i> Muat 6 Alur Standar
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
                    <button type="button" id="btnSimpanBatchTahapan" class="btn btn-primary rounded-pill px-4 fw-bold shadow-md" onclick="simpanBatchTahapan()"
                            style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #0ea5e9 100%); border: 1px solid rgba(255,255,255,0.25);">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Tahapan (<span id="lblCountSimpan">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let batchTahapanRowCount = 0;
    let batchCompressedBlobs = {}; // key: rowIndex -> Blob

    // Preset 6 Alur Standar Industri Garmen
    const PRESET_ALUR_GARMEN = [
        { nama: 'Persiapan & Pola (Pattern / Spreading)', desk: 'Pemeriksaan kain, pembuatan pola marker, dan penggelaran kain (spreading).' },
        { nama: 'Pemotongan Bahan (Cutting)', desk: 'Pemotongan lembaran kain sesuai pola marker menggunakan mesin potong/laser.' },
        { nama: 'Sablon & Bordir (Embellishment / Print)', desk: 'Pengerjaan sablon grafis, bordir logo dada, atau aplikasi cetak kain.' },
        { nama: 'Penjahitan & Perakitan (Sewing / Assembly)', desk: 'Proses jahit, obras, pasang kancing/resleting, dan perakitan komponen baju.' },
        { nama: 'Pemeriksaan Kualitas (QC & Trimming)', desk: 'Pemeriksaan cacat jahitan, pengukuran dimensi size, dan pembuangan sisa benang.' },
        { nama: 'Finishing & Kemasan (Ironing & Packing)', desk: 'Penyetrikaan uap (steam pressing), pasang hangtag, pelipatan, dan kemas polybag.' }
    ];

    function updateBatchTahapanCounter() {
        const total = $('#containerBarisBatchTahapan tr.row-batch-tahapan').length;
        $('#badgeTotalBarisTahapan').text(`${total} / 50`);
        $('#lblCountSimpan').text(total);

        if (total === 0) {
            $('#emptyStateBatchTahapan').removeClass('d-none');
            $('#tabelInputBatchTahapan').addClass('d-none');
            $('#btnSimpanBatchTahapan').prop('disabled', true);
        } else {
            $('#emptyStateBatchTahapan').addClass('d-none');
            $('#tabelInputBatchTahapan').removeClass('d-none');
            $('#btnSimpanBatchTahapan').prop('disabled', false);
        }

        // Re-numbering index kolom No
        $('#containerBarisBatchTahapan tr.row-batch-tahapan').each(function(index) {
            $(this).find('.badge-row-number').text(index + 1);
            $(this).find('.inp-row-urutan').val(index + 1);
        });
    }

    function tambahBarisTahapan(preset = null) {
        const totalCurrent = $('#containerBarisBatchTahapan tr.row-batch-tahapan').length;
        if (totalCurrent >= 50) {
            Swal.fire('Batas Maksimal', 'Maksimal 50 tahapan dalam satu kali input.', 'warning');
            return;
        }

        batchTahapanRowCount++;
        const rowId = batchTahapanRowCount;
        const namaVal = preset ? preset.nama : '';
        const deskVal = preset ? preset.desk : '';

        const rowHtml = `
            <tr class="row-batch-tahapan" id="row_batch_tahapan_${rowId}" data-row-id="${rowId}">
                {{-- No Urutan --}}
                <td class="text-center">
                    <span class="badge rounded-circle p-2 badge-row-number spk-text-main fw-bold" style="width: 28px; height: 28px; font-size: 11px; background: rgba(100,116,139,0.12); border: 1px solid var(--spk-border-color);">1</span>
                    <input type="hidden" name="tahapans[${rowId}][urutan]" class="inp-row-urutan" value="1">
                </td>

                {{-- Foto SOP Upload & Preview Thumbnail --}}
                <td class="text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        {{-- Trigger Box / Thumbnail --}}
                        <div id="box_foto_thumb_${rowId}" class="rounded-3 border d-flex flex-column align-items-center justify-content-center cursor-pointer shadow-xs position-relative overflow-hidden" 
                             style="width: 50px; height: 50px; background: rgba(37,99,235,0.06); border-color: rgba(37,99,235,0.25) !important;"
                             onclick="$('#inp_file_row_${rowId}').click()" title="Klik untuk unggah/ganti foto SOP">
                            <i class="bi bi-camera-fill text-primary fs-5"></i>
                            <span style="font-size: 9px;" class="spk-text-muted mt-0.5">Foto</span>
                        </div>

                        {{-- Preview Image Container --}}
                        <div id="container_foto_preview_${rowId}" class="d-none position-relative">
                            <img id="img_preview_row_${rowId}" src="" class="rounded-3 border shadow-xs cursor-pointer" 
                                 style="width: 50px; height: 50px; object-fit: cover;" 
                                 onclick="previewFotoTahapan(this.src, $('#inp_nama_row_${rowId}').val())" title="Klik untuk memperbesar">
                            <button type="button" class="btn btn-danger btn-sm p-0 rounded-circle position-absolute d-flex align-items-center justify-content-center" 
                                    style="top: -6px; right: -6px; width: 18px; height: 18px; font-size: 10px;" 
                                    onclick="hapusFotoBaris(${rowId})" title="Hapus foto">
                                <i class="bi bi-x"></i>
                            </button>
                            <small id="badge_size_row_${rowId}" class="badge bg-success bg-opacity-75 rounded-pill p-0 px-1 mt-0.5 d-block" style="font-size: 8px;">0 KB</small>
                        </div>

                        <input type="file" id="inp_file_row_${rowId}" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="handleFotoBarisChange(this, ${rowId})">
                    </div>
                </td>

                {{-- Nama Tahapan --}}
                <td>
                    <input type="text" name="tahapans[${rowId}][nama_tahapan]" id="inp_nama_row_${rowId}"
                           class="form-control form-control-sm spk-input rounded-3 fw-semibold inp-batch-nama" 
                           placeholder="cth: Pemotongan Bahan (Cutting)" value="${namaVal}" required maxlength="100" autocomplete="off">
                </td>

                {{-- Deskripsi / Catatan Kerja --}}
                <td>
                    <input type="text" name="tahapans[${rowId}][deskripsi]" id="inp_desk_row_${rowId}"
                           class="form-control form-control-sm spk-input rounded-3 text-muted inp-batch-desk" 
                           placeholder="Catatan pengerjaan / instruksi khusus..." value="${deskVal}" maxlength="255" autocomplete="off">
                </td>

                {{-- Tombol Hapus Baris --}}
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1.5 shadow-xs" onclick="hapusBarisTahapan(${rowId})" title="Hapus baris ini">
                        <i class="bi bi-trash" style="font-size: 13px;"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#containerBarisBatchTahapan').append(rowHtml);
        updateBatchTahapanCounter();

        if (!preset && window.innerWidth >= 768) {
            setTimeout(() => $(`#inp_nama_row_${rowId}`).focus(), 150);
        }
    }

    function hapusBarisTahapan(rowId) {
        $(`#row_batch_tahapan_${rowId}`).remove();
        delete batchCompressedBlobs[rowId];
        updateBatchTahapanCounter();
    }

    function bersihkanSemuaBarisTahapan() {
        $('#containerBarisBatchTahapan').empty();
        batchCompressedBlobs = {};
        updateBatchTahapanCounter();
    }

    function muatPresetStandarGarmen() {
        bersihkanSemuaBarisTahapan();
        PRESET_ALUR_GARMEN.forEach(p => tambahBarisTahapan(p));
    }

    function compressImageFile(file, maxWidth = 1000, maxHeight = 1000, quality = 0.72) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        resolve(blob || file);
                    }, 'image/webp', quality);
                };
                img.onerror = () => resolve(file);
                img.src = event.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }
    window.compressImageFile = compressImageFile;

    // Client-side image compress for batch rows
    async function handleFotoBarisChange(input, rowId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const origSizeKb = (file.size / 1024).toFixed(1);

            try {
                const blob = await compressImageFile(file);
                batchCompressedBlobs[rowId] = blob;
                const compSizeKb = (blob.size / 1024).toFixed(1);

                const url = URL.createObjectURL(blob);
                $(`#img_preview_row_${rowId}`).attr('src', url);
                $(`#badge_size_row_${rowId}`).text(`${compSizeKb} KB`);
                $(`#container_foto_preview_${rowId}`).removeClass('d-none');
                $(`#box_foto_thumb_${rowId}`).addClass('d-none');
            } catch (e) {
                batchCompressedBlobs[rowId] = file;
                const url = URL.createObjectURL(file);
                $(`#img_preview_row_${rowId}`).attr('src', url);
                $(`#badge_size_row_${rowId}`).text(`${origSizeKb} KB`);
                $(`#container_foto_preview_${rowId}`).removeClass('d-none');
                $(`#box_foto_thumb_${rowId}`).addClass('d-none');
            }
        }
    }

    function hapusFotoBaris(rowId) {
        delete batchCompressedBlobs[rowId];
        $(`#inp_file_row_${rowId}`).val('');
        $(`#img_preview_row_${rowId}`).attr('src', '');
        $(`#container_foto_preview_${rowId}`).addClass('d-none');
        $(`#box_foto_thumb_${rowId}`).removeClass('d-none');
    }

    function simpanBatchTahapan() {
        const bomId = $('#inp_tah_bom_id').val();
        if (!bomId) {
            $('#display_bom_selected').addClass('border-danger');
            Swal.fire({
                icon: 'warning',
                title: 'Kode BOM Wajib Diisi!',
                text: 'Resep BOM / Kode BOM wajib dipilih terlebih dahulu sebelum menyimpan tahapan produksi.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: '<i class="bi bi-search me-1"></i> Pilih Resep BOM Sekarang'
            }).then(() => {
                bukaModalLookupBomTahapan();
            });
            return;
        }

        const rows = $('#containerBarisBatchTahapan tr.row-batch-tahapan');
        if (rows.length === 0) {
            Swal.fire('Form Kosong', 'Silakan tambahkan minimal 1 baris tahapan produksi.', 'warning');
            return;
        }

        // Validasi seluruh input nama tahapan
        let isValid = true;
        let emptyIndex = -1;

        rows.each(function(idx) {
            const val = $(this).find('.inp-batch-nama').val().trim();
            if (!val) {
                isValid = false;
                emptyIndex = idx + 1;
                $(this).find('.inp-batch-nama').focus().addClass('border-danger');
                return false;
            }
        });

        if (!isValid) {
            Swal.fire('Nama Tahapan Kosong', `Baris ke-${emptyIndex}: Nama tahapan produksi wajib diisi.`, 'warning');
            return;
        }

        const $btn = $('#btnSimpanBatchTahapan');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('bom_id', bomId);

        rows.each(function(idx) {
            const rowId = $(this).data('row-id');
            const nama = $(this).find('.inp-batch-nama').val().trim();
            const desk = $(this).find('.inp-batch-desk').val().trim();
            const urutan = idx + 1;

            formData.append(`tahapans[${idx}][urutan]`, urutan);
            formData.append(`tahapans[${idx}][nama_tahapan]`, nama);
            if (desk) {
                formData.append(`tahapans[${idx}][deskripsi]`, desk);
            }

            // Lampirkan compressed foto jika ada
            if (batchCompressedBlobs[rowId]) {
                formData.append(`tahapans[${idx}][foto]`, batchCompressedBlobs[rowId], `tahapan_${idx}.webp`);
            }
        });

        $.ajax({
            url: '{{ route("produksi.master-tahapan.store") }}',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                // 1. Langsung reload tabel di background secara instan tanpa jeda!
                reloadTabelTahapan();

                // 2. Tutup modal & pulihkan tombol seketika
                $('#modalTambahTahapan').modal('hide');
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Semua Tahapan');

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
                    title: res.message || 'Tahapan produksi berhasil disimpan!'
                });
            },
            error: function(xhr) {
                const totalRows = $('#containerBarisBatchTahapan tr.row-batch-tahapan').length;
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Tahapan (<span id="lblCountSimpan">' + totalRows + '</span>)');
                const err = xhr.responseJSON?.errors;
                const msg = err ? Object.values(err).flat().join('<br>') : (xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.');
                
                if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        html: msg,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: '<i class="bi bi-check2 me-1"></i> Mengerti'
                    });
                } else {
                    Swal.fire('Gagal Menyimpan', msg, 'error');
                }
            }
        });
    }

    // ── LOOKUP BOM MODAL HANDLER ──
    function bukaModalLookupBomTahapan() {
        $('#inputSearchBomLookupTahapan').val('');
        filterBomLookupTahapan('');
        $('#modalLookupBomTahapan').modal('show');
        setTimeout(() => $('#inputSearchBomLookupTahapan').focus(), 350);
    }

    function pilihBomTahapan(id, nama, kode, namaBarang) {
        if (!id) {
            resetBomTahapan();
            $('#modalLookupBomTahapan').modal('hide');
            return;
        }

        $('#inp_tah_bom_id').val(id);
        $('#display_bom_selected').removeClass('border-danger');

        // Specific BOM
        const produkHtml = namaBarang ? `<span class="spk-text-muted small text-truncate">• ${namaBarang}</span>` : '';
        $('#lbl_bom_terpilih').html(`
            <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" style="background: linear-gradient(135deg, #1d4ed8, #2563eb); font-size: 11.5px;">
                <i class="bi bi-diagram-2-fill me-1"></i>${kode}
            </span>
            <strong class="small spk-text-main fw-bold text-truncate">${nama}</strong>
            ${produkHtml}
        `);
        $('#btn_reset_bom_terpilih').removeClass('d-none');

        $('#modalLookupBomTahapan').modal('hide');
    }

    function resetBomTahapan() {
        $('#inp_tah_bom_id').val('');
        $('#lbl_bom_terpilih').html(`
            <span class="spk-text-muted small fw-semibold">
                <i class="bi bi-exclamation-circle text-warning me-1"></i> -- Silakan Pilih Resep BOM (Wajib Diisi) --
            </span>
        `);
        $('#btn_reset_bom_terpilih').addClass('d-none');
    }

    function filterBomLookupTahapan(keyword) {
        if (typeof cariBomLookupTahapanDebounced === 'function') {
            cariBomLookupTahapanDebounced(keyword);
        }
    }

    // Modal open handler: Reset form & siapkan 3 baris awal
    document.getElementById('modalTambahTahapan').addEventListener('show.bs.modal', function() {
        bersihkanSemuaBarisTahapan();
        resetBomTahapan();
        $('#display_bom_selected').removeClass('border-danger');
        // Tambah 3 baris default untuk kenyamanan
        tambahBarisTahapan();
        tambahBarisTahapan();
        tambahBarisTahapan();
    });
</script>
@endpush

