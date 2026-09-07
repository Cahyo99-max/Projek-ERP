{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditLokasiRak" formId="formEditLokasiRak" title="<i class='bi bi-pencil-square me-2'></i> Edit Lokasi Rak" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" id="edit_lokasi_rak_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Gudang / Warehouse <span class="text-danger">*</span></label>
                <select name="gudang_id" id="edit_gudang_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Gudang --</option>
                    @foreach ($gudangs as $g)
                        <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kode Rak <span class="text-danger">*</span></label>
                <input type="text" name="kode_rak" id="edit_kode_rak"
                    class="form-control form-control-custom"
                    placeholder="Contoh: RAK-0001" autocomplete="off" required maxlength="20" readonly style="background-color: var(--bs-secondary-bg) !important; cursor: not-allowed;">
                <div class="form-text text-muted fs-8">Kode rak tidak dapat diubah setelah disimpan.</div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Rak <span class="text-danger">*</span></label>
                <input type="text" name="nama_rak" id="edit_nama_rak"
                    class="form-control form-control-custom"
                    placeholder="Contoh: RAK BARAT A-1" autocomplete="off" required maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Baris (Row)</label>
                <input type="text" name="baris" id="edit_baris"
                    class="form-control form-control-custom"
                    placeholder="Contoh: A, B, 01" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kolom (Column)</label>
                <input type="text" name="kolom" id="edit_kolom"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2, 02" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Level (Tier)</label>
                <input type="text" name="level" id="edit_level"
                    class="form-control form-control-custom"
                    placeholder="Contoh: L1, L2, 03" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Keterangan tambahan lokasi rak (opsional)" maxlength="100"></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </x-slot>
</x-modal_form>

@push('scripts')
<script>
    $(document).ready(function () {
        // Otomatis uppercase dan restrict character type untuk kode_rak
        $(document).on('input', '#edit_kode_rak', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        });

        // ===== EDIT — SHOW MODAL AND LOAD DATA =====
        $(document).on('click', '.btn-edit', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get("{{ url('/lokasi-rak') }}/" + id + "/edit", function (res) {
                let rak = res.lokasi_rak;
                $('#edit_lokasi_rak_id').val(rak.id);
                $('#edit_gudang_id').val(rak.gudang_id);
                $('#edit_kode_rak').val(rak.kode_rak);
                $('#edit_nama_rak').val(rak.nama_rak);
                $('#edit_baris').val(rak.baris ? rak.baris : '');
                $('#edit_kolom').val(rak.kolom ? rak.kolom : '');
                $('#edit_level').val(rak.level ? rak.level : '');
                $('#edit_deskripsi').val(rak.deskripsi ? rak.deskripsi : '');
                $('#edit_status').val(rak.status);

                $('#modalEditLokasiRak').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data lokasi rak.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditLokasiRak').on('hidden.bs.modal', function () {
            $('#formEditLokasiRak')[0].reset();
        });

        // Form Submit handler
        $('#formEditLokasiRak').on('submit', function (e) {
            e.preventDefault();
            let id = parseInt($('#edit_lokasi_rak_id').val());

            // Double check length constraints to block submission
            const kode = $('#edit_kode_rak').val();
            const nama = $('#edit_nama_rak').val();
            const baris = $('#edit_baris').val();
            const kolom = $('#edit_kolom').val();
            const level = $('#edit_level').val();
            const deskripsi = $('#edit_deskripsi').val();

            if (kode.length > 20) {
                showCharLimitPopup('#edit_kode_rak', 'Kode Rak', 20);
                return;
            }
            if (nama.length > 50) {
                showCharLimitPopup('#edit_nama_rak', 'Nama Rak', 50);
                return;
            }
            if (baris.length > 10) {
                showCharLimitPopup('#edit_baris', 'Baris (Row)', 10);
                return;
            }
            if (kolom.length > 10) {
                showCharLimitPopup('#edit_kolom', 'Kolom (Column)', 10);
                return;
            }
            if (level.length > 10) {
                showCharLimitPopup('#edit_level', 'Level (Tier)', 10);
                return;
            }
            if (deskripsi.length > 100) {
                showCharLimitPopup('#edit_deskripsi', 'Deskripsi', 100);
                return;
            }

            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);

            let data = {
                gudang_id: $('#edit_gudang_id').val(),
                kode_rak:  kode,
                nama_rak:  nama,
                baris:     baris,
                kolom:     kolom,
                level:     level,
                status:    $('#edit_status').val(),
                deskripsi: deskripsi
            };

            $.ajax({
                url: "{{ url('/lokasi-rak') }}/" + id + "/update",
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#modalEditLokasiRak').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableLokasiRak !== 'undefined') {
                            tableLokasiRak.ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                },
                error: function (err) {
                    let errorMsg = 'Terjadi kesalahan saat mengupdate data.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) {
                            errorMsg += '• ' + escapeHtml(val[0]) + '<br>';
                        });
                    } else if (err.status === 429) {
                        errorMsg = 'Terlalu banyak permintaan. Harap tunggu sebentar.';
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = escapeHtml(err.responseJSON.message);
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });
    });
</script>
@endpush
