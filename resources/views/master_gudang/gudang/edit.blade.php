{{-- ===== MODAL EDIT ===== --}}
<x-modal_form id="modalEditGudang" formId="formEditGudang" title="<i class='bi bi-pencil-square me-2'></i> Edit Gudang" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" id="edit_gudang_id">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Nama Gudang <span class="text-danger">*</span></label>
                <input type="text" name="nama_gudang" id="edit_nama_gudang"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Gudang Utama, Gudang Raw Material" autocomplete="off" required maxlength="30">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Lokasi / Kepemilikan (PT / Cabang)</label>
                <select name="cabang_id" id="edit_cabang_id" class="form-select form-control-custom">
                    <option value="">{{ $activeCompany?->nama_perusahaan ?? 'PT Pusat' }} (Kantor Pusat)</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Jenis Gudang <span class="text-danger">*</span></label>
                <select name="jenis_gudang_id" id="edit_jenis_gudang_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Jenis Gudang --</option>
                    @foreach ($jenisGudangs as $jg)
                        <option value="{{ $jg->id }}">{{ $jg->nama_jenis_gudang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Penanggung Jawab (PIC)</label>
                <select name="penanggung_jawab_id" id="edit_penanggung_jawab_id" class="form-select form-control-custom">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">No. Telepon</label>
                <input type="text" name="telepon" id="edit_telepon"
                    class="form-control form-control-custom"
                    placeholder="Contoh: 081234567890" autocomplete="off" maxlength="13">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Email Gudang</label>
                <input type="email" name="email" id="edit_email"
                    class="form-control form-control-custom"
                    placeholder="Contoh: gudang@perusahaan.com" autocomplete="off" maxlength="50">
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="gunakan_lokasi_rak" id="edit_gunakan_lokasi_rak" value="1">
                    <label class="form-check-label fw-semibold" for="edit_gunakan_lokasi_rak">Gunakan Sistem Rak Lokasi</label>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="izinkan_stok_negatif" id="edit_izinkan_stok_negatif" value="1">
                    <label class="form-check-label fw-semibold text-warning" for="edit_izinkan_stok_negatif">Izinkan Stok Negatif (Minus)</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat Gudang</label>
                <textarea name="alamat" id="edit_alamat" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Alamat fisik lengkap gudang" maxlength="60"></textarea>
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold">Keterangan / Deskripsi</label>
                <textarea name="keterangan" id="edit_keterangan" rows="2"
                    class="form-control form-control-custom"
                    placeholder="Keterangan tambahan untuk gudang (opsional)" maxlength="30"></textarea>
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
        // ===== EDIT — SHOW MODAL =====
        $(document).on('click', '.btn-edit', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get("{{ url('/gudang') }}/" + id + "/edit", function (res) {
                let g = res.gudang;
                $('#edit_gudang_id').val(g.id);
                $('#edit_nama_gudang').val(g.nama_gudang);
                $('#edit_cabang_id').val(g.cabang_id);
                $('#edit_jenis_gudang_id').val(g.jenis_gudang_id);
                $('#edit_penanggung_jawab_id').val(g.penanggung_jawab_id ? g.penanggung_jawab_id : '');
                $('#edit_telepon').val(g.telepon);
                $('#edit_email').val(g.email);
                $('#edit_alamat').val(g.alamat);
                $('#edit_keterangan').val(g.keterangan);
                $('#edit_status').val(g.status);

                $('#edit_gunakan_lokasi_rak').prop('checked', g.gunakan_lokasi_rak == 1);
                $('#edit_izinkan_stok_negatif').prop('checked', g.izinkan_stok_negatif == 1);

                $('#modalEditGudang').modal('show');
            }).fail(function (err) {
                let msg = err.status === 403
                    ? 'Anda tidak memiliki akses untuk mengedit data ini.'
                    : 'Gagal memuat data gudang.';
                Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
            });
        });

        $('#modalEditGudang').on('hidden.bs.modal', function () {
            $('#formEditGudang')[0].reset();
        });

        // ===== UPDATE =====
        $('#formEditGudang').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('[type=submit]');
            btn.prop('disabled', true);
            let id = parseInt($('#edit_gudang_id').val());

            let data = {
                nama_gudang:          $('#edit_nama_gudang').val(),
                cabang_id:            $('#edit_cabang_id').val(),
                jenis_gudang_id:      $('#edit_jenis_gudang_id').val(),
                penanggung_jawab_id:  $('#edit_penanggung_jawab_id').val(),
                telepon:              $('#edit_telepon').val(),
                email:                $('#edit_email').val(),
                alamat:               $('#edit_alamat').val(),
                keterangan:           $('#edit_keterangan').val(),
                status:               $('#edit_status').val(),
                gunakan_lokasi_rak:   $('#edit_gunakan_lokasi_rak').is(':checked') ? 1 : 0,
                izinkan_stok_negatif: $('#edit_izinkan_stok_negatif').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: "{{ url('/gudang') }}/" + id + "/update",
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#modalEditGudang').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof tableGudang !== 'undefined') {
                            tableGudang.ajax.reload(null, false);
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
