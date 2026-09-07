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
                        <h5 class="modal-title fw-extrabold text-white mb-0" style="letter-spacing: -0.2px;">
                            Buat & Konfigurasi Alur Tahapan Produksi
                        </h5>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 12px;">
                            Definisikan satu atau banyak tahapan proses sekaligus untuk satu resep BOM atau template standar
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- FORM --}}
            <form id="formInputTahapan" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1 overflow-hidden" style="min-height: 0;">
                @csrf
                <div class="modal-body spk-modal-body p-3 p-md-4 overflow-y-auto" style="min-height: 0;">
                    
                    {{-- ── SECTION 1: PILIH RESEP BOM TERKAIT ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5" style="border: 1px solid var(--spk-border-color) !important;">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-7">
                                <label class="form-label fw-bold spk-text-main small mb-1">
                                    <i class="bi bi-diagram-2-fill text-primary me-1"></i> Resep BOM Terkait <span class="text-danger">*</span>
                                </label>
                                <select name="bom_id" id="inp_tah_bom_id" class="form-select spk-input rounded-3 shadow-xs">
                                    <option value="">-- Template Umum (Berlaku untuk Semua BOM / Standar) --</option>
                                    @if(isset($boms) && $boms->count() > 0)
                                        <optgroup label="Khusus Resep BOM Tertentu:">
                                            @foreach($boms as $b)
                                                <option value="{{ $b->id }}">{{ $b->nama_bom }} ({{ $b->kode_bom }}) {{ $b->barangJadi ? ' • '.$b->barangJadi->nama_barang : '' }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <small class="spk-text-muted d-block mt-1" style="font-size: 11px;">
                                    Pilih BOM agar alur tahapan ini otomatis dimuat saat membuat SPK untuk produk tersebut.
                                </small>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="p-2.5 rounded-3 d-flex align-items-center gap-2" style="background: rgba(37,99,235,0.08); border: 1px dashed rgba(37,99,235,0.25);">
                                    <i class="bi bi-lightning-charge-fill text-primary fs-5 flex-shrink-0"></i>
                                    <small class="spk-text-muted" style="font-size: 11.5px; line-height: 1.35;">
                                        Input banyak baris tahapan sekaligus. Tiap tahapan dapat dilengkapi foto SOP / instruksi kerja.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 2: DAFTAR BARIS TAHAPAN KERJA ── --}}
                    <div class="spk-card border-0 rounded-4 shadow-sm p-3 p-md-3.5" style="border: 1px solid var(--spk-border-color) !important;">
                        
                        {{-- Action Toolbar --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--spk-border-color) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-circle p-1.5 d-flex align-items-center justify-content-center text-white" style="width: 26px; height: 26px; background: rgba(37,99,235,0.25); border: 1px solid rgba(37,99,235,0.4);">
                                    <i class="bi bi-list-check" style="font-size: 13px;"></i>
                                </span>
                                <span class="fw-bold text-white small text-uppercase" style="letter-spacing: 0.5px;">Daftar Alur Tahapan Proses</span>
                                <span class="badge rounded-pill px-3 py-1 text-white fw-bold shadow-xs" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); font-size: 11px;" id="badgeTotalBarisTahapan">0 / 50</span>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-xs" onclick="muatPresetStandarGarmen()" title="Muat 6 alur garmen standar otomatis">
                                    <i class="bi bi-magic me-1"></i> Muat 6 Alur Standar Garmen
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 shadow-xs" onclick="bersihkanSemuaBarisTahapan()" title="Kosongkan semua baris">
                                    <i class="bi bi-trash me-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="tambahBarisTahapan()" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris Tahapan
                                </button>
                            </div>
                        </div>

                        {{-- Dynamic Table / Rows --}}
                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                            <table class="table table-hover align-middle mb-0 w-100" id="tabelInputBatchTahapan">
                                <thead style="position: sticky; top: 0; z-index: 5; background: var(--spk-table-head-bg, #0f172a);">
                                    <tr class="small text-uppercase fw-bold text-nowrap" style="border-bottom: 2px solid var(--spk-border-color);">
                                        <th style="width: 48px;" class="text-center">No</th>
                                        <th style="width: 90px;" class="text-center">Foto SOP</th>
                                        <th style="min-width: 240px;">Nama Tahapan Proses <span class="text-danger">*</span></th>
                                        <th style="min-width: 280px;">Deskripsi / Instruksi Kerja <span class="spk-text-muted fw-normal">(Opsional)</span></th>
                                        <th style="width: 48px;" class="text-center">Aksi</th>
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
                <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between gap-2 p-3 p-md-3.5 border-0" 
                     style="background: var(--spk-card-bg, #1e293b); border-top: 1px solid var(--spk-border-color) !important; flex-shrink: 0;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill w-100 w-sm-auto px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> Batal
                    </button>
                    <button type="button" id="btnSimpanBatchTahapan" class="btn btn-primary rounded-pill w-100 w-sm-auto px-4 fw-bold shadow-lg" onclick="simpanBatchTahapan()"
                            style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #0ea5e9 100%); border: 1px solid rgba(255,255,255,0.25);">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Semua Tahapan (<span id="lblCountSimpan">0</span>)
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
                    <span class="badge bg-secondary bg-opacity-20 text-white rounded-circle p-2 badge-row-number" style="width: 28px; height: 28px; font-size: 11px;">1</span>
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

        if (!preset) {
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

    function compressImageFile(file, maxWidth = 1200, maxHeight = 1200, quality = 0.75) {
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
        
        const bomId = $('#inp_tah_bom_id').val();
        if (bomId) {
            formData.append('bom_id', bomId);
        }

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
                $('#modalTambahTahapan').modal('hide');
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Semua Tahapan');
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                    .then(() => reloadTabelTahapan());
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Simpan Semua Tahapan');
                const err = xhr.responseJSON?.errors;
                const msg = err ? Object.values(err).flat().join('<br>') : (xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.');
                Swal.fire('Gagal Menyimpan', msg, 'error');
            }
        });
    }

    // Modal open handler: Reset form & siapkan 1 baris awal
    document.getElementById('modalTambahTahapan').addEventListener('show.bs.modal', function() {
        bersihkanSemuaBarisTahapan();
        $('#inp_tah_bom_id').val('');
        // Tambah 3 baris default untuk kenyamanan
        tambahBarisTahapan();
        tambahBarisTahapan();
        tambahBarisTahapan();
    });
</script>
@endpush

