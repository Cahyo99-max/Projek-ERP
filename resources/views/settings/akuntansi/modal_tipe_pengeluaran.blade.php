{{-- Modal Tambah Tipe Pengeluaran --}}
<div class="modal fade" id="modalTambahTipePengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-arrow-up text-primary me-2"></i>Tambah Tipe Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahTipePengeluaran" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Tipe Pengeluaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tipe" class="form-control rounded-3" placeholder="Misal: Pemberian Sampel, Barang Rusak, dll" maxlength="41" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Kode Tipe</label>
                        <input type="text" name="kode_tipe" class="form-control rounded-3 bg-light" placeholder="Otomatis diset sistem (contoh: TP-001)" maxlength="11">
                        <small class="text-muted d-block mt-1">Kosongkan jika ingin dibuatkan kode otomatis oleh sistem.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Alokasi Akun COA Pengeluaran / Beban <span class="text-danger">*</span></label>
                        <select name="coa_id" id="tambah_tipe_coa_id" class="form-select rounded-3 select2-tambah-tipe-coa" required>
                            <option value="">-- Pilih Akun COA Beban (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ $coa->type_label }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Akun beban/biaya pengeluaran yang dihubungkan dengan tipe ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="Active" selected>Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="2" placeholder="Catatan singkat (opsional, maks 40 karakter)..." maxlength="41"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Tipe Pengeluaran --}}
<div class="modal fade" id="modalEditTipePengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tipe Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTipePengeluaran" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_tipe_pengeluaran_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Tipe Pengeluaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tipe" id="edit_tipe_nama_tipe" class="form-control rounded-3" maxlength="41" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Kode Tipe</label>
                        <input type="text" name="kode_tipe" id="edit_tipe_kode_tipe" class="form-control rounded-3 bg-light text-muted fw-bold" readonly title="Kode Tipe tidak dapat diubah">
                        <small class="text-muted d-block mt-1"><i class="bi bi-lock-fill me-1 text-danger"></i>Kode Tipe bersifat permanen dan tidak dapat diubah.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Alokasi Akun COA Pengeluaran / Beban <span class="text-danger">*</span></label>
                        <select name="coa_id" id="edit_tipe_coa_id" class="form-select rounded-3 select2-edit-tipe-coa" required>
                            <option value="">-- Pilih Akun COA Beban (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ $coa->type_label }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_tipe_status" class="form-select rounded-3" required>
                            <option value="Active">Aktif</option>
                            <option value="Inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" id="edit_tipe_deskripsi" class="form-control rounded-3" rows="2" placeholder="Catatan singkat (opsional, maks 40 karakter)..." maxlength="41"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('input paste keyup', '#formTambahTipePengeluaran input[name="nama_tipe"], #formEditTipePengeluaran input[name="nama_tipe"]', function() {
        if (this.value.length > 40) {
            this.value = this.value.substring(0, 40);
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai',
                text: 'Nama Tipe Pengeluaran maksimal 40 karakter!',
                confirmButtonColor: '#ef4444'
            });
        }
    });

    $(document).on('input paste keyup', '#formTambahTipePengeluaran input[name="kode_tipe"], #formEditTipePengeluaran input[name="kode_tipe"]', function() {
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai',
                text: 'Kode Tipe maksimal 10 karakter!',
                confirmButtonColor: '#ef4444'
            });
        }
    });

    $(document).on('input paste keyup', '#formTambahTipePengeluaran textarea[name="deskripsi"], #formEditTipePengeluaran textarea[name="deskripsi"]', function() {
        if (this.value.length > 40) {
            this.value = this.value.substring(0, 40);
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Tercapai',
                text: 'Deskripsi / Keterangan maksimal 40 karakter!',
                confirmButtonColor: '#ef4444'
            });
        }
    });
});
</script>
@endpush
