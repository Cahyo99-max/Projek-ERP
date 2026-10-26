{{-- Modal Edit User --}}
<x-modal_form id="modalEdit" formId="formEditKaryawan" title="Edit Akun User" enctype="multipart/form-data" autocomplete="off">
    <x-slot name="slot">
        <input type="hidden" name="id" id="edit_id">
        <div class="row g-4">
            {{-- Left Column: Profil & Akun --}}
            <div class="col-12 col-lg-6 border-custom-separator pe-lg-4">
                <div class="section-title mb-3">
                    <i class="bi bi-shield-lock text-primary me-2"></i> Informasi Profil & Akun
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-link-45deg text-primary me-1"></i>
                        Hubungkan dengan Karyawan (HRD)
                        <span class="badge bg-secondary ms-1" style="font-size:0.68rem; vertical-align:middle;">
                            <i class="bi bi-lock-fill me-1"></i>Terkunci
                        </span>
                    </label>
                    <select id="edit_karyawan_id" class="form-select form-select-custom" disabled></select>
                    <input type="hidden" name="karyawan_id" id="edit_karyawan_id_hidden">
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Koneksi ke data HRD tidak dapat diubah setelah user dibuat.
                    </small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Kode Karyawan <small class="text-muted">(Maks 4)</small></label>
                        <input type="text" name="kode_karyawan" id="edit_kode_karyawan" class="form-control form-control-custom" placeholder="Contoh: A001" maxlength="4" autocomplete="off">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Inisial <span class="text-danger" id="edit_inisial_req">*</span> <small class="text-muted">(Maks 4)</small></label>
                        <input type="text" name="inisial" id="edit_inisial" class="form-control form-control-custom text-uppercase" placeholder="Contoh: JD" maxlength="4" autocomplete="off" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span> <small class="text-muted">(Maks 50)</small></label>
                    <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control form-control-custom" placeholder="Masukkan nama lengkap" required maxlength="50" autocomplete="off">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Username <span class="text-danger">*</span> <small class="text-muted">(Maks 20)</small></label>
                        <input type="text" name="username" id="edit_username" class="form-control form-control-custom" placeholder="Username login" required maxlength="20" autocomplete="off">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email <span class="text-danger">*</span> <small class="text-muted">(Maks 50)</small></label>
                        <input type="email" name="email" id="edit_email" class="form-control form-control-custom" placeholder="email@perusahaan.com" required maxlength="50" autocomplete="off">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Jenis Kelamin <span class="text-danger" id="edit_jk_req">*</span></label>
                        <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select form-select-custom" required>
                            <option value="">Pilih...</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Status <span class="text-danger" id="edit_status_req">*</span></label>
                        <select name="status" id="edit_status" class="form-select form-select-custom" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Right Column: Pekerjaan & Hak Akses PT/Cabang --}}
            <div class="col-12 col-lg-6 ps-lg-4">
                <div class="section-title mb-3">
                    <i class="bi bi-briefcase text-primary me-2"></i> Pekerjaan & Hak Akses Perusahaan
                </div>

                <div class="mb-3">
                    <label class="form-label">No HP / WhatsApp <small class="text-muted">(Maks 13)</small></label>
                    <input type="text" name="no_hp" id="edit_no_hp" class="form-control form-control-custom" placeholder="08xxxxxxxxxx" autocomplete="off" maxlength="13">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role_id" id="edit_role_id" class="form-select form-select-custom" required></select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departemen <span class="text-danger">*</span></label>
                        <select name="departemen_id" id="edit_departemen_id" class="form-select form-select-custom" required></select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <select name="jabatan_id" id="edit_jabatan_id" class="form-select form-select-custom" required></select>
                </div>

                {{-- Akses Perusahaan (PT) --}}
                <div class="mb-3">
                    <label class="form-label d-block fw-semibold">Akses Perusahaan (PT) Induk <span class="text-danger">*</span></label>
                    <div class="row g-2 border rounded-3 p-2 mx-0 shadow-sm" style="max-height: 120px; overflow-y: auto; background-color: var(--cine-input-bg); border-color: var(--cine-border-line) !important;">
                        @if(isset($companies))
                            @foreach ($companies as $comp)
                                <div class="col-12 form-check">
                                    <input class="form-check-input check-company-edit" type="checkbox" name="company_ids[]" value="{{ $comp->id }}" id="edit_company_{{ $comp->id }}">
                                    <label class="form-check-label text-truncate w-100 fw-medium" for="edit_company_{{ $comp->id }}" style="font-size: 0.85rem; color: var(--tbl-header-color);">
                                        <i class="bi bi-building me-1 text-primary"></i>{{ $comp->nama_perusahaan }} <small class="text-muted">({{ $comp->kode_perusahaan }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Perusahaan (PT) Default <span class="text-danger">*</span></label>
                    <select name="default_company_id" id="edit_default_company_id" class="form-select form-select-custom" required>
                        <option value="">Pilih PT Default...</option>
                    </select>
                </div>

                {{-- Container Akses Cabang (Hidden if no PT selected) --}}
                <div id="select_pt_first_msg_edit" class="alert alert-info py-2 px-3 mb-3 small shadow-sm" style="font-size: 0.82rem;">
                    <i class="bi bi-info-circle me-1"></i> Pilih <strong>Perusahaan (PT) Induk</strong> terlebih dahulu untuk memilih cabang.
                </div>

                <div id="container_akses_cabang_edit" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label d-block fw-semibold">Akses Cabang <small class="text-muted">(Opsional)</small></label>
                        <div class="row g-2 border rounded-3 p-2 mx-0 shadow-sm" style="max-height: 120px; overflow-y: auto; background-color: var(--cine-input-bg); border-color: var(--cine-border-line) !important;">
                            @foreach ($branches as $branch)
                                <div class="col-12 col-md-6 form-check branch-item-edit" data-company-id="{{ $branch->company_id }}">
                                    <input class="form-check-input check-branch-edit" type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" id="edit_branch_{{ $branch->id }}">
                                    <label class="form-check-label text-truncate w-100 fw-medium" for="edit_branch_{{ $branch->id }}" style="font-size: 0.85rem; color: var(--tbl-header-color);">
                                        {{ $branch->nama_cabang }} @if($branch->company)<small class="text-muted">({{ $branch->company->kode_perusahaan }})</small>@endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div id="no_branch_msg_edit" class="small text-muted mt-1" style="display: none;"><i class="bi bi-exclamation-circle me-1"></i> PT yang dipilih belum memiliki cabang terdaftar.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cabang Default <small class="text-muted">(Opsional)</small></label>
                        <select name="default_branch_id" id="edit_default_branch_id" class="form-select form-select-custom">
                            <option value="">-- Tingkat Pusat / PT Induk (Tanpa Cabang) --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal_form>
