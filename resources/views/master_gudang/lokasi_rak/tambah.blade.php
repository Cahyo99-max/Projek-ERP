{{-- ===== MODAL TAMBAH ===== --}}
<x-modal_form id="modalTambahLokasiRak" formId="formTambahLokasiRak" title="<i class='bi bi-plus-circle me-2'></i> Tambah Lokasi Rak" autocomplete="off">
    <x-slot name="slot">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Gudang / Warehouse <span class="text-danger">*</span></label>
                <select name="gudang_id" id="tambah_gudang_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Gudang --</option>
                    @foreach ($gudangs as $g)
                        <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kode Rak</label>
                <input type="text" name="kode_rak" id="tambah_kode_rak"
                    class="form-control form-control-custom"
                    placeholder="Kosongkan untuk auto-generate (RAK-XXXX)" autocomplete="off" maxlength="20">
                <div class="form-text text-muted fs-8">Hanya alphanumeric dan tanda hubung (-). Otomatis kapital.</div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Rak <span class="text-danger">*</span></label>
                <input type="text" name="nama_rak" id="tambah_nama_rak"
                    class="form-control form-control-custom"
                    placeholder="Contoh: RAK BARAT A-1" autocomplete="off" required maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Baris (Row)</label>
                <input type="text" name="baris" id="tambah_baris"
                    class="form-control form-control-custom"
                    placeholder="Contoh: A, B, 01" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Kolom (Column)</label>
                <input type="text" name="kolom" id="tambah_kolom"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 1, 2, 02" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Level (Tier)</label>
                <input type="text" name="level" id="tambah_level"
                    class="form-control form-control-custom"
                    placeholder="Contoh: L1, L2, 03" autocomplete="off" maxlength="10">
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" id="tambah_deskripsi" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Keterangan tambahan lokasi rak (opsional)" maxlength="100"></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
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
        $(document).on('input', '#tambah_kode_rak', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        });

        // Form Submit handler
        $('#formTambahLokasiRak').on('submit', function (e) {
            e.preventDefault();

            // Double check length constraints to block submission
            const kode = $('#tambah_kode_rak').val();
            const nama = $('#tambah_nama_rak').val();
            const baris = $('#tambah_baris').val();
            const kolom = $('#tambah_kolom').val();
            const level = $('#tambah_level').val();
            const deskripsi = $('#tambah_deskripsi').val();

            if (kode.length > 20) {
                showCharLimitPopup('#tambah_kode_rak', 'Kode Rak', 20);
                return;
            }
            if (nama.length > 50) {
                showCharLimitPopup('#tambah_nama_rak', 'Nama Rak', 50);
                return;
            }
            if (baris.length > 10) {
                showCharLimitPopup('#tambah_baris', 'Baris (Row)', 10);
                return;
            }
            if (kolom.length > 10) {
                showCharLimitPopup('#tambah_kolom', 'Kolom (Column)', 10);
                return;
            }
            if (level.length > 10) {
                showCharLimitPopup('#tambah_level', 'Level (Tier)', 10);
                return;
            }
            if (deskripsi.length > 100) {
                showCharLimitPopup('#tambah_deskripsi', 'Deskripsi', 100);
                return;
            }

            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('lokasi-rak.store') }}",
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalTambahLokasiRak').modal('hide');
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
                    let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
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

        $('#modalTambahLokasiRak').on('hidden.bs.modal', function () {
            $('#formTambahLokasiRak')[0].reset();
        });
    });
</script>
@endpush
