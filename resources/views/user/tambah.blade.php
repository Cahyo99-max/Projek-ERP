{{-- Modal Tambah User --}}
<x-modal_form id="modalTambah" formId="formTambahKaryawan"
    title="<i class='bi bi-person-plus me-2'></i> Tambah Akun User Baru" enctype="multipart/form-data" autocomplete="off">
    <x-slot name="slot">
        <div class="row g-4">
            {{-- Left Column: Profil & Akun --}}
            <div class="col-12 col-lg-6 border-custom-separator pe-lg-4">
                <div class="section-title mb-3">
                    <i class="bi bi-person-badge text-primary me-2"></i> Informasi Profil & Akun
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Hubungkan dengan Karyawan (HRD)</label>
                    <select name="karyawan_id" id="tambah_karyawan_id" class="form-select form-select-custom">
                        <option value="">-- Buat User Tanpa Link Karyawan --</option>
                        @foreach ($availableKaryawan as $emp)
                            <option value="{{ $emp->id }}" data-nama="{{ $emp->nama_lengkap }}" data-kode="{{ $emp->kode_karyawan }}" data-inisial="{{ $emp->inisial }}" data-no-hp="{{ $emp->no_hp }}" data-jk="{{ $emp->jenis_kelamin }}" data-dept="{{ $emp->departemen_id }}" data-jab="{{ $emp->jabatan_id }}">
                                {{ $emp->nama_lengkap }} ({{ $emp->kode_karyawan ?? 'Tanpa Kode' }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Jika dihubungkan, data profil disinkronkan otomatis dari data HRD.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Kode Karyawan <small class="text-muted">(Maks 4)</small></label>
                        <input type="text" name="kode_karyawan" id="tambah_kode_karyawan" class="form-control form-control-custom"
                            placeholder="Contoh: A001" maxlength="4">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Inisial <span class="text-danger" id="tambah_inisial_req">*</span> <small class="text-muted">(Maks 4)</small></label>
                        <input type="text" name="inisial" id="tambah_inisial" class="form-control form-control-custom text-uppercase"
                            placeholder="Contoh: JD" maxlength="4" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span> <small class="text-muted">(Maks 50)</small></label>
                    <input type="text" name="nama_lengkap" id="tambah_nama_lengkap" class="form-control form-control-custom"
                        placeholder="Masukkan nama lengkap" required maxlength="50">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Username <span class="text-danger">*</span> <small class="text-muted">(Maks 20)</small></label>
                        <input type="text" name="username" id="tambah_username" class="form-control form-control-custom"
                            placeholder="Username login" required maxlength="20" autocomplete="new-password">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email <span class="text-danger">*</span> <small class="text-muted">(Maks 50)</small></label>
                        <input type="email" name="email" id="tambah_email" class="form-control form-control-custom"
                            placeholder="email@perusahaan.com" required maxlength="50">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Jenis Kelamin <span class="text-danger" id="tambah_jk_req">*</span></label>
                        <select name="jenis_kelamin" id="tambah_jenis_kelamin" class="form-select form-select-custom" required>
                            <option value="">Pilih...</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Status <span class="text-danger" id="tambah_status_req">*</span></label>
                        <select name="status" id="tambah_status" class="form-select form-select-custom" required>
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
                    <input type="text" name="no_hp" id="tambah_no_hp" class="form-control form-control-custom"
                        placeholder="08xxxxxxxxxx" maxlength="13">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select form-select-custom" required>
                            <option value="">Pilih Role...</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departemen <span class="text-danger">*</span></label>
                        <select name="departemen_id" id="tambah_departemen_id" class="form-select form-select-custom" required>
                            <option value="">Pilih Dept...</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <select name="jabatan_id" id="tambah_jabatan_id" class="form-select form-select-custom" required>
                        <option value="">Pilih Jabatan...</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->position_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Akses Perusahaan (PT) --}}
                <div class="mb-3">
                    <label class="form-label d-block fw-semibold">Akses Perusahaan (PT) Induk <span class="text-danger">*</span></label>
                    <div class="row g-2 border rounded-3 p-2 mx-0 shadow-sm" style="max-height: 120px; overflow-y: auto; background-color: var(--cine-input-bg); border-color: var(--cine-border-line) !important;">
                        @if(isset($companies))
                            @foreach ($companies as $comp)
                                <div class="col-12 form-check">
                                    <input class="form-check-input check-company-tambah" type="checkbox" name="company_ids[]" value="{{ $comp->id }}" id="tambah_company_{{ $comp->id }}">
                                    <label class="form-check-label text-truncate w-100 fw-medium" for="tambah_company_{{ $comp->id }}" style="font-size: 0.85rem; color: var(--tbl-header-color);">
                                        <i class="bi bi-building me-1 text-primary"></i>{{ $comp->nama_perusahaan }} <small class="text-muted">({{ $comp->kode_perusahaan }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Perusahaan (PT) Default <span class="text-danger">*</span></label>
                    <select name="default_company_id" id="tambah_default_company_id" class="form-select form-select-custom" required>
                        <option value="">Pilih PT Default...</option>
                    </select>
                </div>

                {{-- Container Akses Cabang (Hidden if no PT selected) --}}
                <div id="select_pt_first_msg_tambah" class="alert alert-info py-2 px-3 mb-3 small shadow-sm" style="font-size: 0.82rem;">
                    <i class="bi bi-info-circle me-1"></i> Pilih <strong>Perusahaan (PT) Induk</strong> terlebih dahulu untuk memilih cabang.
                </div>

                <div id="container_akses_cabang_tambah" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label d-block fw-semibold">Akses Cabang <small class="text-muted">(Opsional)</small></label>
                        <div class="row g-2 border rounded-3 p-2 mx-0 shadow-sm" style="max-height: 120px; overflow-y: auto; background-color: var(--cine-input-bg); border-color: var(--cine-border-line) !important;">
                            @foreach ($branches as $branch)
                                <div class="col-12 col-md-6 form-check branch-item-tambah" data-company-id="{{ $branch->company_id }}">
                                    <input class="form-check-input check-branch-tambah" type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" id="tambah_branch_{{ $branch->id }}">
                                    <label class="form-check-label text-truncate w-100 fw-medium" for="tambah_branch_{{ $branch->id }}" style="font-size: 0.85rem; color: var(--tbl-header-color);">
                                        {{ $branch->nama_cabang }} @if($branch->company)<small class="text-muted">({{ $branch->company->kode_perusahaan }})</small>@endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div id="no_branch_msg_tambah" class="small text-muted mt-1" style="display: none;"><i class="bi bi-exclamation-circle me-1"></i> PT yang dipilih belum memiliki cabang terdaftar.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cabang Default <small class="text-muted">(Opsional)</small></label>
                        <select name="default_branch_id" id="tambah_default_branch_id" class="form-select form-select-custom">
                            <option value="">Pilih Cabang Default...</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal_form>
