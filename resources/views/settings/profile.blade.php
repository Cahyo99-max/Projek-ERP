<x-layouts title="{{ __('profile.title') }} - ">

    {{-- ===== STYLES ===== --}}
    @push('styles')
    <style>
        /* Profile Header Cover */
        .profile-cover {
            background: linear-gradient(135deg, var(--sidebar-bg, #1e40af) 0%, var(--primary, #2563eb) 100%);
            position: relative;
            overflow: hidden;
        }
        .profile-cover::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Avatar Container & Effects */
        .avatar-upload-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0.2) 100%);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .avatar-upload-wrapper:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
            background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0.4) 100%);
        }
        .avatar-upload-wrapper img {
            border: 4px solid var(--card-bg, #fff);
            transition: all 0.3s ease;
        }
        .avatar-overlay {
            position: absolute;
            inset: 8px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            transition: opacity 0.3s ease;
            pointer-events: none;
            gap: 4px;
        }
        .avatar-overlay i {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }
        .avatar-upload-wrapper:hover .avatar-overlay {
            opacity: 1;
        }
        .avatar-upload-wrapper:hover .avatar-overlay i {
            transform: translateY(-2px);
        }

        /* Upload progress ring */
        .upload-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: var(--primary, #2563eb);
            animation: spinRing 0.8s linear infinite;
            display: none;
            z-index: 10;
        }
        @keyframes spinRing {
            to { transform: rotate(360deg); }
        }

        /* Employee Info List styling */
        .info-list-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.2rem;
            border-radius: 12px;
            background: var(--bg-color, #f8fafc);
            border: 1px solid var(--border-color, #e2e8f0);
            transition: all 0.2s ease;
        }
        .info-list-item:hover {
            transform: translateX(4px);
            background: var(--border-color, #e2e8f0);
            border-color: rgba(37, 99, 235, 0.2);
        }
        .info-icon-wrapper {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary, #2563eb);
            font-size: 1.15rem;
            margin-right: 0.85rem;
            flex-shrink: 0;
        }
        .info-label {
            font-size: 0.72rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 0.92rem;
            color: var(--text-main, #1e293b);
            font-weight: 600;
        }

        /* Branch Pills styling */
        .branch-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid var(--border-color, #e2e8f0);
            gap: 6px;
        }
        .branch-pill-default {
            background-color: rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.25);
            color: var(--primary, #2563eb);
        }
        .branch-pill-default:hover {
            background-color: var(--primary, #2563eb);
            color: #fff;
        }
        .branch-pill-secondary {
            background-color: var(--bg-color, #f8fafc);
            color: var(--text-muted, #64748b);
        }
        .branch-pill-secondary:hover {
            background-color: var(--text-muted, #64748b);
            color: #fff;
            border-color: var(--text-muted, #64748b);
        }

        /* Form Custom Elements */
        .form-control-custom {
            border-radius: 10px !important;
            padding: 0.75rem 1.2rem !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            background-color: var(--bg-color, #f8fafc) !important;
            color: var(--text-main, #1e293b) !important;
            font-size: 0.9rem !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .form-control-custom::placeholder {
            color: #a0aec0;
            font-size: 0.85rem;
        }
        .form-control-custom:focus {
            border-color: var(--primary, #2563eb) !important;
            background-color: var(--card-bg, #fff) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
        }
        .input-group-text-custom {
            border-radius: 10px 0 0 10px !important;
            background-color: var(--bg-color, #f8fafc) !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            color: var(--text-muted, #64748b) !important;
            border-end-0: 0;
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }
        .input-group-text-append {
            border-radius: 0 10px 10px 0 !important;
            background-color: var(--bg-color, #f8fafc) !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            color: var(--text-muted, #64748b) !important;
            cursor: pointer;
            transition: all 0.2s;
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }
        .input-group-text-append:hover {
            color: var(--primary, #2563eb) !important;
            background-color: var(--border-color, #e2e8f0) !important;
        }

        /* Buttons with subtle shadow */
        .btn-premium-primary {
            background: var(--primary, #2563eb);
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.3s ease;
        }
        .btn-premium-primary:hover:not(:disabled) {
            background: var(--primary-hover, #1d4ed8);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
            color: #fff;
        }
        .btn-premium-primary:active:not(:disabled) {
            transform: translateY(1px);
        }

        .btn-premium-light {
            background: var(--bg-color, #f8fafc);
            color: var(--text-main, #1e293b);
            border: 1px solid var(--border-color, #e2e8f0);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        .btn-premium-light:hover {
            background: var(--border-color, #e2e8f0);
            transform: translateY(-1px);
        }

        /* Explicit theme overrides for cards and texts */
        .card {
            background: linear-gradient(135deg, #f0f5ff 0%, #e6efff 100%) !important;
            color: #1e293b !important;
            border: 1px solid rgba(37, 99, 235, 0.15) !important;
        }
        .card-body, .card-header {
            color: #1e293b !important;
        }
        .text-muted {
            color: #64748b !important;
        }
        .info-list-item {
            background: #ffffff !important;
            border: 1px solid rgba(37, 99, 235, 0.1) !important;
        }
        .form-control-custom {
            background-color: #ffffff !important;
            border: 1px solid rgba(37, 99, 235, 0.15) !important;
            color: #1e293b !important;
        }
        .input-group-text-custom, .input-group-text-append {
            background-color: #f0f4ff !important;
            border: 1px solid rgba(37, 99, 235, 0.15) !important;
            color: #64748b !important;
        }

        /* Dark Mode explicit overrides */
        [data-bs-theme="dark"] .card {
            background: var(--card-bg, #1e293b) !important;
            color: var(--text-main, #f8fafc) !important;
            border: 1px solid var(--border-color, #334155) !important;
        }
        [data-bs-theme="dark"] .card-body, [data-bs-theme="dark"] .card-header {
            color: var(--text-main, #f8fafc) !important;
        }
        [data-bs-theme="dark"] .text-muted {
            color: var(--text-muted, #94a3b8) !important;
        }
        [data-bs-theme="dark"] .info-list-item {
            background: var(--bg-color, #0f172a) !important;
            border: 1px solid var(--border-color, #334155) !important;
        }
        [data-bs-theme="dark"] .form-control-custom {
            background-color: var(--bg-color, #0f172a) !important;
            border: 1px solid var(--border-color, #334155) !important;
            color: var(--text-main, #f8fafc) !important;
        }
        [data-bs-theme="dark"] .input-group-text-custom, [data-bs-theme="dark"] .input-group-text-append {
            background-color: var(--bg-color, #0f172a) !important;
            border: 1px solid var(--border-color, #334155) !important;
            color: var(--text-muted, #94a3b8) !important;
        }
    </style>
    @endpush

    <div class="row g-4">
        <!-- Kolom Kiri: Detail Akun -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden">
                <!-- Background Header Pattern / Gradient -->
                <div class="profile-cover p-5 text-center position-relative">

                    {{-- Hidden file input --}}
                    <input type="file" id="fotoInput" name="foto" accept="image/*" class="d-none">

                    {{-- Avatar wrapper (clickable) --}}
                    <div class="position-relative d-inline-block mb-2" id="avatarContainer">
                        <div class="avatar-upload-wrapper" id="avatarClickTarget" title="{{ __('profile.click_to_change') }}">
                            <img id="avatarPreview"
                                 src="{{ $user->foto ? asset($user->foto) : ($user->jenis_kelamin == 'P' ? asset('gambar/perempuan.jpg') : asset('gambar/laki.jpg')) }}"
                                 alt="Avatar"
                                 class="rounded-circle"
                                 width="120" height="120" style="object-fit: cover;">

                            <div class="avatar-overlay">
                                <i class="bi bi-camera-fill"></i>
                                <span>{{ __('profile.change_photo') }}</span>
                            </div>
                            <div class="upload-ring" id="uploadRing"></div>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-success border border-3 border-white rounded-circle p-2" style="transform: translate(-10%, -10%);" title="{{ __('profile.status_active') }}"></span>
                    </div>

                    {{-- Preview action bar (shown when photo is selected) --}}
                    <div id="photoActionBar" class="d-none mt-3 d-flex justify-content-center gap-2">
                        <button type="button" id="btnUploadFoto" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold shadow-sm">
                            <i class="bi bi-cloud-upload me-1"></i> {{ __('profile.save_photo') }}
                        </button>
                        <button type="button" id="btnCancelFoto" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold">
                            <i class="bi bi-x-lg me-1"></i> {{ __('profile.cancel') }}
                        </button>
                    </div>

                    <h4 class="text-white fw-bold mt-3 mb-1">{{ $user->nama_lengkap }}</h4>
                    <p class="text-white text-opacity-75 mb-0" style="font-size: 0.9rem;">
                        <i class="bi bi-person-badge me-1"></i> {{ $user->getRoleNames()->first() ?? 'No Role' }}
                    </p>
                    <p class="text-white text-opacity-50 mb-0 mt-1" style="font-size: 0.78rem;">
                        <i class="bi bi-camera me-1"></i> {{ __('profile.click_to_change') }}
                    </p>
                </div>

                <div class="card-body p-4">
                    <div class="section-title mb-4" style="font-size: 0.95rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 700; color: var(--text-main);">
                        <i class="bi bi-info-circle text-primary me-2"></i> {{ __('profile.employee_info') }}
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-hash"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.employee_code') }}</div>
                                <div class="info-value">{{ $user->kode_karyawan ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.username') }}</div>
                                <div class="info-value">{{ $user->username }}</div>
                            </div>
                        </div>
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.email') }}</div>
                                <div class="info-value">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.phone_number') }}</div>
                                <div class="info-value">{{ $user->no_hp ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.department') }}</div>
                                <div class="info-value">
                                    @if($user->department)
                                        <span class="badge bg-info-soft px-3 py-1.5 rounded-pill fw-semibold">{{ $user->department->department_name }}</span>
                                    @elseif($user->hasRole('Super Admin'))
                                        <span class="badge bg-info-soft px-3 py-1.5 rounded-pill fw-semibold">{{ __('profile.all_departments') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-list-item">
                            <div class="info-icon-wrapper">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">{{ __('profile.position') }}</div>
                                <div class="info-value">
                                    @if($user->position)
                                        <span class="badge bg-primary-soft px-3 py-1.5 rounded-pill fw-semibold">{{ $user->position->position_name }}</span>
                                    @elseif($user->hasRole('Super Admin'))
                                        <span class="badge bg-primary-soft px-3 py-1.5 rounded-pill fw-semibold">{{ __('profile.super_admin') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4 mb-4" style="font-size: 0.95rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 700; color: var(--text-main);">
                        <i class="bi bi-buildings text-primary me-2"></i> {{ __('profile.branch_access') }}
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $userBranches = $user->hasRole('Super Admin')
                                            ? \App\Models\Branch::where('status', 'Active')->get()
                                            : $user->branches;
                            $activeBranchId = session('active_branch_id');
                        @endphp
                        @forelse($userBranches as $branch)
                            @php
                                $isDefault = $user->hasRole('Super Admin')
                                            ? ($branch->id == $activeBranchId)
                                            : ($branch->pivot && $branch->pivot->is_default);
                            @endphp
                            @if($isDefault)
                                <span class="branch-pill branch-pill-default" title="{{ __('profile.default_branch') }}">
                                    <i class="bi bi-building-fill-check"></i>
                                    {{ $branch->nama_cabang }}
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $user->hasRole('Super Admin') ? 'Aktif' : __('profile.default') }}
                                    </span>
                                </span>
                            @else
                                <span class="branch-pill branch-pill-secondary">
                                    <i class="bi bi-building"></i>
                                    {{ $branch->nama_cabang }}
                                </span>
                            @endif
                        @empty
                            <div class="w-100 text-center py-3 bg-light rounded-3 text-muted small italic">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ __('profile.no_branch_access') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Ganti Password & Tanda Tangan Digital -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-1" style="color: var(--text-main); font-weight: 700;">
                        <i class="bi bi-shield-lock-fill text-primary me-2"></i> {{ __('profile.update_password') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        {{ __('profile.password_hint') }}
                    </p>
                </div>

                <div class="card-body p-4">
                    <form id="formGantiPassword" autocomplete="off">
                        @csrf

                        <div class="mb-4 position-relative">
                            <label class="form-label small text-muted fw-bold text-uppercase tracking-wider">{{ __('profile.current_password') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-custom"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control form-control-custom"
                                       placeholder="{{ __('profile.enter_old_password') }}" autocomplete="new-password" required>
                                <span class="input-group-text input-group-text-append toggle-password" data-target="#current_password">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4 position-relative">
                            <label class="form-label small text-muted fw-bold text-uppercase tracking-wider">{{ __('profile.new_password') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-custom"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control form-control-custom"
                                       placeholder="{{ __('profile.enter_new_password') }}" autocomplete="new-password" required>
                                <span class="input-group-text input-group-text-append toggle-password" data-target="#new_password">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle me-1"></i> {{ __('profile.password_combination_hint') }}
                            </small>
                        </div>

                        <div class="mb-4 position-relative">
                            <label class="form-label small text-muted fw-bold text-uppercase tracking-wider">{{ __('profile.confirm_new_password') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-custom"><i class="bi bi-check-circle-fill"></i></span>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control form-control-custom"
                                       placeholder="{{ __('repeat_new_password') }}" autocomplete="new-password" required>
                                <span class="input-group-text input-group-text-append toggle-password" data-target="#new_password_confirmation">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <button type="reset" class="btn btn-premium-light rounded-pill px-4 fw-semibold">{{ __('profile.cancel') }}</button>
                            <button type="submit" id="btnSubmitPassword" class="btn btn-premium-primary rounded-pill px-4 fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> {{ __('profile.save_password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tanda Tangan Digital Card -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-1" style="color: var(--text-main); font-weight: 700;">
                        <i class="bi bi-pen-fill text-primary me-2"></i> Tanda Tangan Digital Anda
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Gunakan tanda tangan ini untuk menyetujui dokumen digital di dalam aplikasi.
                    </p>
                </div>
                <div class="card-body p-4">
                    <form id="formGantiSignature" autocomplete="off">
                        @csrf
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-5 text-center">
                                <div class="fw-semibold small text-muted mb-2">Tanda Tangan Aktif:</div>
                                <div class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center mx-auto" style="height: 120px; max-width: 250px;">
                                    <img id="profile_signature_preview" 
                                         src="{{ $user->activeSignature ? asset($user->activeSignature->signature_path) : '' }}" 
                                         alt="Belum ada tanda tangan" 
                                         style="max-height: 100%; max-width: 100%; object-fit: contain; {{ $user->activeSignature ? '' : 'display: none;' }}">
                                    <span id="profile_signature_empty" class="text-muted small" style="{{ $user->activeSignature ? 'display: none;' : '' }}">Belum ada tanda tangan</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-7 text-center">
                                <div class="fw-semibold small text-muted mb-2">Gambar Ulang / Daftarkan Baru:</div>
                                <div class="signature-container border rounded-3 p-1 bg-white shadow-sm position-relative mx-auto" style="width: 100%; max-width: 320px;">
                                    <canvas id="profile_signature_pad" height="130" style="touch-action: none; cursor: crosshair; display: block; width: 100%; height: 130px;"></canvas>
                                    <button type="button" class="btn btn-sm btn-danger rounded-pill position-absolute" id="profile_clear_signature" style="bottom: 8px; right: 8px;">
                                        <i class="bi bi-trash"></i> Bersihkan
                                    </button>
                                </div>
                                <input type="hidden" name="signature" id="profile_signature_input">
                                <div class="mt-2 text-center">
                                    <span class="text-muted small">Atau unggah file gambar:</span>
                                    <input type="file" id="profile_signature_file" class="form-control form-control-sm mt-1 mx-auto" accept="image/*" style="max-width: 250px;">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="submit" id="btnSubmitSignature" class="btn btn-premium-primary rounded-pill px-4 fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> Simpan Tanda Tangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // ==================== PURE VANILLA JS SIGNATURE PAD HELPER ====================
        function initSignaturePad(canvasId, clearButtonId, inputId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;
            const ctx = canvas.getContext('2d');
            const clearButton = document.getElementById(clearButtonId);
            const input = document.getElementById(inputId);

            let drawing = false;

            // Set draw styles
            ctx.strokeStyle = "#0f172a"; // Slate-900
            ctx.lineWidth = 3;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                if (canvas.width !== rect.width || canvas.height !== rect.height) {
                    const tempImg = canvas.toDataURL();
                    canvas.width = rect.width;
                    canvas.height = rect.height;

                    // Restore draw styles
                    ctx.strokeStyle = "#0f172a";
                    ctx.lineWidth = 3;
                    ctx.lineCap = "round";
                    ctx.lineJoin = "round";

                    const img = new Image();
                    img.onload = function() {
                        ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    };
                    img.src = tempImg;
                }
            }

            // Initial scale
            setTimeout(resizeCanvas, 200);
            window.addEventListener('resize', resizeCanvas);

            function getMousePos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDrawing(e) {
                drawing = true;
                const pos = getMousePos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.cancelable) e.preventDefault();
            }

            function draw(e) {
                if (!drawing) return;
                const pos = getMousePos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                if (e.cancelable) e.preventDefault();
            }

            function stopDrawing() {
                if (drawing) {
                    drawing = false;
                    input.value = canvas.toDataURL();
                }
            }

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            clearButton.addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                input.value = "";
            });

            return {
                clear: () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    input.value = "";
                },
                resize: resizeCanvas
            };
        }

        $(document).ready(function() {
            // Inisialisasi Signature Pad Profil
            const profileSigPad = initSignaturePad('profile_signature_pad', 'profile_clear_signature', 'profile_signature_input');

            // Listen file upload untuk tanda tangan
            $('#profile_signature_file').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            const canvas = document.getElementById('profile_signature_pad');
                            const ctx = canvas.getContext('2d');
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            // Draw image centered & fitted inside canvas
                            const ratio = Math.min(canvas.width / img.width, canvas.height / img.height) * 0.95;
                            const newWidth = img.width * ratio;
                            const newHeight = img.height * ratio;
                            const x = (canvas.width - newWidth) / 2;
                            const y = (canvas.height - newHeight) / 2;
                            ctx.drawImage(img, x, y, newWidth, newHeight);

                            // Update input hidden
                            $('#profile_signature_input').val(canvas.toDataURL());
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Reset file input saat canvas dibersihkan
            $('#profile_clear_signature').on('click', function() {
                $('#profile_signature_file').val('');
            });

            // AJAX Submit Signature
            $('#formGantiSignature').on('submit', function(e) {
                e.preventDefault();

                let inputVal = $('#profile_signature_input').val();
                if (!inputVal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Silakan gambar tanda tangan Anda terlebih dahulu pada canvas!'
                    });
                    return;
                }

                let btn = $('#btnSubmitSignature');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

                $.ajax({
                    url: '{{ route("profile.signature") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        signature: inputVal
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Update preview signature
                            $('#profile_signature_preview').attr('src', res.signature_url).show();
                            $('#profile_signature_empty').hide();
                            if (profileSigPad) profileSigPad.clear();
                        });
                    },
                    error: function(err) {
                        let errorMsg = 'Gagal menyimpan tanda tangan digital.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: 'Gagal', text: errorMsg });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Simpan Tanda Tangan');
                    }
                });
            });

            // ==================== TOGGLE PASSWORD VISIBILITY ====================
            $(document).on('click', '.toggle-password', function() {
                const targetInput = $($(this).data('target'));
                const icon = $(this).find('i');
                if (targetInput.attr('type') === 'password') {
                    targetInput.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    targetInput.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // ==================== PHOTO UPLOAD LOGIC ====================
            let originalSrc = $('#avatarPreview').attr('src');
            let selectedFile = null;

            // Click avatar → trigger file picker
            $('#avatarClickTarget').on('click', function() {
                $('#fotoInput').trigger('click');
            });

            // File selected → show preview immediately
            $('#fotoInput').on('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Validate client-side size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'warning',
                        title: "{{ __('profile.file_too_large') }}",
                        text: "{{ __('profile.max_size_hint') }}",
                        confirmButtonColor: '#3b82f6'
                    });
                    this.value = '';
                    return;
                }

                selectedFile = file;

                // Live preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatarPreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);

                // Show action bar
                $('#photoActionBar').removeClass('d-none').addClass('d-flex');
            });

            // Cancel → restore original
            $('#btnCancelFoto').on('click', function() {
                $('#avatarPreview').attr('src', originalSrc);
                $('#fotoInput').val('');
                selectedFile = null;
                $('#photoActionBar').removeClass('d-flex').addClass('d-none');
            });

            // Upload button → AJAX POST
            $('#btnUploadFoto').on('click', function() {
                if (!selectedFile) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('foto', selectedFile);

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> ' + "{{ __('profile.uploading') }}");
                $('#uploadRing').show();

                $.ajax({
                    url: '{{ route("profile.photo") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        // Update src to uploaded URL (busts browser cache)
                        $('#avatarPreview').attr('src', res.foto_url + '?t=' + Date.now());
                        originalSrc = res.foto_url;

                        // Also update nav avatar if exists
                        $('img.nav-avatar, img.sidebar-avatar').attr('src', res.foto_url);

                        $('#photoActionBar').removeClass('d-flex').addClass('d-none');
                        $('#fotoInput').val('');
                        selectedFile = null;

                        Swal.fire({
                            icon: 'success',
                            title: "{{ __('profile.photo_updated') }}",
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Soft reload to refresh nav avatar everywhere
                            location.reload();
                        });
                    },
                    error: function(err) {
                        let msg = "{{ __('profile.failed_to_upload') }}";
                        if (err.responseJSON) {
                            if (err.responseJSON.message) msg = err.responseJSON.message;
                            else if (err.responseJSON.errors && err.responseJSON.errors.foto) {
                                msg = err.responseJSON.errors.foto[0];
                            }
                        }
                        Swal.fire({ icon: 'error', title: "{{ __('profile.failed') }}", text: msg });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> ' + "{{ __('profile.save_photo') }}");
                        $('#uploadRing').hide();
                    }
                });
            });

            // ==================== CHANGE PASSWORD VIA AJAX ====================
            $('#formGantiPassword').on('submit', function(e) {
                e.preventDefault();

                let btn = $('#btnSubmitPassword');
                let form = $(this);

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + "{{ __('profile.saving') }}");

                $.ajax({
                    url: '{{ route("profile.password") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: "{{ __('profile.success') }}",
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            form[0].reset();
                        });
                    },
                    error: function(err) {
                        let errorMsg = "{{ __('profile.failed_to_save_password') }}";
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: "{{ __('profile.failed') }}", text: errorMsg });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> ' + "{{ __('profile.save_password') }}");
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
