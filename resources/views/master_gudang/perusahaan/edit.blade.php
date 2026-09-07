{{-- ===== MODAL EDIT PERUSAHAAN ===== --}}
<x-modal_form id="modalEditPerusahaan" formId="formEditPerusahaan" title="<i class='bi bi-pencil-square me-2'></i> Edit Perusahaan (PT) Induk" btnSubmitId="btnUpdatePT" btnSubmitText="Update PT" autocomplete="off" enctype="multipart/form-data">
    <x-slot name="slot">
        <input type="hidden" id="edit_company_id">
        <div class="row g-3">

            <!-- Logo Perusahaan -->
            <div class="col-12 text-center mb-2">
                <div class="d-inline-block position-relative">
                    <div id="previewLogoEdit" class="rounded-3 border border-2 border-dashed d-flex align-items-center justify-content-center bg-body-tertiary shadow-sm" style="width:120px; height:120px; overflow:hidden;">
                        <i class="bi bi-building fs-1 text-muted"></i>
                    </div>
                    <label for="edit_logo" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 shadow" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;" title="Ubah Logo">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                    <input type="file" name="logo" id="edit_logo" class="d-none" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewImage(this, '#previewLogoEdit')">
                </div>
                <div class="small text-muted mt-1">Logo PT (Ganti jika ingin diperbarui)</div>
            </div>

            <!-- Kode Perusahaan & Status -->
            <div class="col-md-6">
                <label for="edit_kode_perusahaan" class="form-label fw-semibold">Kode PT <small class="text-muted">(Tidak dapat diubah)</small></label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary text-body border-0"><i class="bi bi-lock-fill"></i></span>
                    <input type="text" name="kode_perusahaan" id="edit_kode_perusahaan" class="form-control form-control-custom text-uppercase bg-body-tertiary text-muted" readonly tabindex="-1" style="cursor: not-allowed;">
                </div>
            </div>
            <div class="col-md-6">
                <label for="edit_status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                    <option value="Active">Aktif</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
            </div>

            <!-- Nama Perusahaan -->
            <div class="col-12">
                <label for="edit_nama_perusahaan" class="form-label fw-semibold">Nama Perusahaan (PT) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary text-body border-0"><i class="bi bi-building"></i></span>
                    <input type="text" name="nama_perusahaan" id="edit_nama_perusahaan" class="form-control form-control-custom" placeholder="Contoh: PT. Contoh Maju Bersama" required maxlength="40" autocomplete="new-password" aria-autocomplete="none">
                </div>
            </div>

            <!-- NPWP & NIB -->
            <div class="col-md-6">
                <label for="edit_npwp" class="form-label fw-semibold">No. NPWP</label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary text-body border-0"><i class="bi bi-card-heading"></i></span>
                    <input type="text" name="npwp" id="edit_npwp" class="form-control form-control-custom" placeholder="01.234.567.8-901.000" maxlength="30" autocomplete="new-password" aria-autocomplete="none">
                </div>
            </div>
            <div class="col-md-6">
                <label for="edit_nib" class="form-label fw-semibold">No. NIB</label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary text-body border-0"><i class="bi bi-file-earmark-check"></i></span>
                    <input type="text" name="nib" id="edit_nib" class="form-control form-control-custom" placeholder="1234567890123" maxlength="30" autocomplete="new-password" aria-autocomplete="none">
                </div>
            </div>

            <!-- Penanggung Jawab / Direktur -->
            <div class="col-md-6">
                <label for="edit_director_name" class="form-label fw-semibold">Direktur / Penanggung Jawab</label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary text-body border-0"><i class="bi bi-person"></i></span>
                    <input type="text" name="director_name" id="edit_director_name" class="form-control form-control-custom" placeholder="Nama Direktur Utama" maxlength="40" autocomplete="new-password" aria-autocomplete="none">
                </div>
            </div>

            <!-- Telepon & Email -->
            <div class="col-md-3">
                <label for="edit_phone_pt" class="form-label fw-semibold">No. Telepon</label>
                <input type="text" name="phone" id="edit_phone_pt" class="form-control form-control-custom" placeholder="0215551234" maxlength="13" autocomplete="new-password" aria-autocomplete="none">
            </div>
            <div class="col-md-3">
                <label for="edit_email_pt" class="form-label fw-semibold">Email PT</label>
                <input type="email" name="email" id="edit_email_pt" class="form-control form-control-custom" placeholder="info@pt.co.id" maxlength="60" autocomplete="new-password" aria-autocomplete="none">
            </div>

            <!-- Alamat Lanjutan -->
            <div class="col-12">
                <label for="edit_address_pt" class="form-label fw-semibold">Alamat Kantor Pusat</label>
                <textarea name="address" id="edit_address_pt" class="form-control form-control-custom" rows="2" placeholder="Alamat jalan, gedung, nomor..." maxlength="60" autocomplete="new-password" aria-autocomplete="none"></textarea>
            </div>
            <div class="col-md-4">
                <label for="edit_city_pt" class="form-label fw-semibold">Kota / Kabupaten</label>
                <input type="text" name="city" id="edit_city_pt" class="form-control form-control-custom" placeholder="Contoh: Jakarta Selatan" maxlength="100" autocomplete="new-password" aria-autocomplete="none">
            </div>
            <div class="col-md-4">
                <label for="edit_province_pt" class="form-label fw-semibold">Provinsi</label>
                <input type="text" name="province" id="edit_province_pt" class="form-control form-control-custom" placeholder="Contoh: DKI Jakarta" maxlength="40" autocomplete="new-password" aria-autocomplete="none">
            </div>
            <div class="col-md-4">
                <label for="edit_postal_code_pt" class="form-label fw-semibold">Kode Pos</label>
                <input type="text" name="postal_code" id="edit_postal_code_pt" class="form-control form-control-custom" placeholder="12190" maxlength="10" autocomplete="new-password" aria-autocomplete="none">
            </div>

        </div>
    </x-slot>
</x-modal_form>
