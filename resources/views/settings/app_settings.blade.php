<x-layouts title="Pengaturan Aplikasi - ">

    {{-- ===== STYLES ===== --}}
    @push('styles')
    <style>
        .settings-card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: var(--bs-card-bg, #ffffff);
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.08));
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .settings-card,
        html[data-theme="dark"] .settings-card,
        .dark-mode .settings-card {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45) !important;
        }

        .logo-preview-container {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto;
            border-radius: 16px;
            border: 2px dashed rgba(148, 163, 184, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: rgba(248, 250, 252, 0.8);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .logo-preview-container,
        html[data-theme="dark"] .logo-preview-container,
        .dark-mode .logo-preview-container {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        .logo-preview-container:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }

        .logo-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 0.75rem;
            cursor: pointer;
            text-align: center;
            padding: 10px;
        }

        .upload-placeholder i {
            font-size: 1.6rem;
            color: #3b82f6;
        }

        .file-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .btn-save-settings {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 11px 24px;
            border-radius: 50rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .btn-save-settings:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
            color: white;
        }

        .lang-select-btn {
            border-radius: 12px !important;
            background: var(--bs-body-bg, #ffffff) !important;
            border: 1px solid var(--bs-border-color, #cbd5e1) !important;
            color: var(--bs-body-color) !important;
        }
        [data-bs-theme="dark"] .lang-select-btn,
        html[data-theme="dark"] .lang-select-btn,
        .dark-mode .lang-select-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
    </style>
    @endpush

    <div class="container-fluid py-4 px-3 px-md-4">
        <!-- Header Toolbar -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm flex-shrink-0"
                 style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);">
                <i class="bi bi-sliders fs-5"></i>
            </div>
            <div>
                <h4 class="fw-bold text-body-emphasis mb-0">Pengaturan Aplikasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.78rem;">
                        <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item text-muted">Pengaturan</li>
                        <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Pengaturan Aplikasi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="card settings-card p-4 p-md-4.5">
                    <form id="formAppSettings" enctype="multipart/form-data">
                        @csrf
                        <div class="text-center mb-4">
                            <h6 class="fw-bold text-body-emphasis text-uppercase tracking-wider small mb-1">Logo Aplikasi</h6>
                            <p class="small text-muted mb-3">Logo ini akan digunakan pada halaman login dan sidebar menu.</p>
                            
                            <div class="logo-preview-container">
                                @can('edit-pengaturan-aplikasi')
                                <input type="file" name="app_logo" id="app_logo" class="file-input" accept="image/*">
                                @endcan
                                <img id="logo_preview_img" src="{{ $appLogoSetting }}" alt="Preview Logo">
                                <div class="upload-placeholder d-none" id="upload_placeholder">
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                    <span>Pilih File<br><small class="text-muted">(JPG, PNG, WEBP)</small></span>
                                </div>
                            </div>
                            <div class="mt-2">
                                @can('edit-pengaturan-aplikasi')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 mt-2 d-none" id="btn_remove_new_logo">
                                    Batal Pilih Gambar
                                </button>
                                <div class="form-text text-muted mt-2" style="font-size: 0.76rem;">
                                    Format: PNG, JPG, JPEG, WEBP. Maks 5MB.<br>
                                    <span class="text-primary fw-semibold">Gambar otomatis dikompres menjadi ±800 KB demi kecepatan loading.</span>
                                </div>
                                @endcan
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-body-emphasis small mb-1.5">
                                Nama Aplikasi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0" style="border-radius: 10px 0 0 10px;">
                                    <i class="bi bi-display text-muted"></i>
                                </span>
                                <input type="text" name="app_name" id="app_name" 
                                    class="form-control border-start-0" 
                                    value="{{ $appNameSetting }}" 
                                    style="border-radius: 0 10px 10px 0;" 
                                    placeholder="Masukkan Nama Aplikasi..." 
                                    {{ auth()->user()->hasRole('Super Admin') || auth()->user()->can('edit-pengaturan-aplikasi') ? 'required' : 'readonly' }}>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-body-emphasis small mb-1.5">Bahasa / Language</label>
                            <div class="dropdown">
                                <button class="btn lang-select-btn w-100 d-flex align-items-center justify-content-between p-2.5 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="d-flex align-items-center gap-2">
                                        @if(app()->getLocale() == 'id')
                                            <span class="flag flag-xs flag-country-id rounded shadow-sm"></span>
                                            <span class="fw-medium">Indonesia</span>
                                        @else
                                            <span class="flag flag-xs flag-country-gb rounded shadow-sm"></span>
                                            <span class="fw-medium">English</span>
                                        @endif
                                    </span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </button>
                                <ul class="dropdown-menu shadow border mt-1 p-2 w-100" style="border-radius: 14px;">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 {{ app()->getLocale() == 'id' ? 'active' : '' }}" href="{{ route('lang.switch', 'id') }}">
                                            <span class="flag flag-xs flag-country-id rounded"></span>
                                            <span class="fs-7 fw-medium">Indonesia</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">
                                            <span class="flag flag-xs flag-country-gb rounded"></span>
                                            <span class="fs-7 fw-medium">English</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('edit-pengaturan-aplikasi'))
                        <div class="d-flex justify-content-end pt-1">
                            <button type="submit" class="btn btn-save-settings w-100">
                                <i class="bi bi-floppy-fill me-1.5"></i> Simpan Perubahan
                            </button>
                        </div>
                        @else
                        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0 py-2.5 px-3 small rounded-3 border-0 bg-body-tertiary">
                            <i class="bi bi-info-circle-fill text-info flex-shrink-0"></i>
                            <span class="text-muted">Mode Lihat Saja. Anda tidak memiliki hak akses untuk mengubah pengaturan ini.</span>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            const defaultLogo = "{{ $appLogoSetting }}";
            const $fileInput = $('#app_logo');
            const $previewImg = $('#logo_preview_img');
            const $placeholder = $('#upload_placeholder');
            const $btnRemove = $('#btn_remove_new_logo');

            // Handle file input changes for dynamic preview
            $fileInput.on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        $previewImg.attr('src', event.target.result).removeClass('d-none');
                        $placeholder.addClass('d-none');
                        $btnRemove.removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Batal pilih gambar
            $btnRemove.on('click', function () {
                $fileInput.val('');
                $previewImg.attr('src', defaultLogo);
                $btnRemove.addClass('d-none');
            });

            // Submit Form via AJAX
            $('#formAppSettings').on('submit', function (e) {
                e.preventDefault();
                
                let formData = new FormData(this);

                Swal.fire({
                    title: 'Menyimpan Pengaturan...',
                    text: 'Mohon tunggu sebentar selagi sistem menyimpan dan mengompres logo.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/app-settings/update',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Instantly update the sidebar brand logo and name
                            const newName = $('#app_name').val();
                            $('.sidebar-brand .brand-text').text(newName);
                            
                            if (res.logo_url) {
                                $('.sidebar-brand .sidebar-logo').attr('src', res.logo_url);
                                $previewImg.attr('src', res.logo_url);
                                $fileInput.val('');
                                $btnRemove.addClass('d-none');
                            }
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function (err) {
                        let errorMsg = 'Terjadi kesalahan sistem.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
