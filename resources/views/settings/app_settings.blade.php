<x-layouts title="Pengaturan Aplikasi - ">

    {{-- ===== STYLES ===== --}}
    @push('styles')
    <style>
        .settings-card {
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: var(--card-bg, #fff);
            border: none;
        }

        .logo-preview-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .logo-preview-container:hover {
            border-color: var(--primary, #2563eb);
            background: rgba(37, 99, 235, 0.02);
        }

        .logo-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.75rem;
            cursor: pointer;
            text-align: center;
            padding: 10px;
        }

        .upload-placeholder i {
            font-size: 1.5rem;
            color: var(--primary, #2563eb);
        }

        .file-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .btn-save-settings {
            background: linear-gradient(135deg, var(--primary, #2563eb) 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .btn-save-settings:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
    </style>
    @endpush

    <div class="container-fluid py-4 px-4">
        <!-- Breadcrumb / Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Pengaturan</li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Pengaturan Aplikasi</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">Pengaturan Aplikasi</h4>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card settings-card p-4">
                    <form id="formAppSettings" enctype="multipart/form-data">
                        @csrf
                        <div class="text-center mb-4">
                            <h6 class="fw-bold text-secondary text-uppercase tracking-wider small">Logo Aplikasi</h6>
                            <p class="small text-muted mb-3">Logo ini akan digunakan pada halaman login dan sidebar menu.</p>
                            
                            <div class="logo-preview-container">
                                <input type="file" name="app_logo" id="app_logo" class="file-input" accept="image/*">
                                <img id="logo_preview_img" src="{{ $appLogoSetting }}" alt="Preview Logo">
                                <div class="upload-placeholder d-none" id="upload_placeholder">
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                    <span>Pilih File<br><small class="text-muted">(JPG, PNG, WEBP)</small></span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 mt-2 d-none" id="btn_remove_new_logo">
                                    Batal Pilih Gambar
                                </button>
                                <div class="form-text text-muted mt-2">
                                    Format: PNG, JPG, JPEG, WEBP. Maks 5MB.<br>
                                    <span class="text-primary font-weight-bold">Gambar akan otomatis dikompres menjadi ±800 KB demi kecepatan performa loading.</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Nama Aplikasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;">
                                    <i class="bi bi-display text-muted"></i>
                                </span>
                                <input type="text" name="app_name" id="app_name" 
                                    class="form-control border-start-0" 
                                    value="{{ $appNameSetting }}" 
                                    style="border-radius: 0 8px 8px 0;" 
                                    placeholder="Masukkan Nama Aplikasi..." required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Bahasa / Language</label>
                            <div class="dropdown mt-2">
                                <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; background: white; border-color: #dee2e6;">
                                    <span class="d-flex align-items-center gap-2 text-dark">
                                        @if(app()->getLocale() == 'id')
                                            <span class="flag flag-xs flag-country-id rounded shadow-sm"></span>
                                            <span>Indonesia</span>
                                        @else
                                            <span class="flag flag-xs flag-country-gb rounded shadow-sm"></span>
                                            <span>English</span>
                                        @endif
                                    </span>
                                    <i class="bi bi-chevron-down text-muted"></i>
                                </button>
                                <ul class="dropdown-menu shadow border-0 mt-1 p-2 w-100" style="border-radius: 12px;">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 {{ app()->getLocale() == 'id' ? 'active bg-light' : '' }}" href="{{ route('lang.switch', 'id') }}">
                                            <span class="flag flag-xs flag-country-id rounded"></span>
                                            <span class="fs-7 fw-medium">Indonesia</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 {{ app()->getLocale() == 'en' ? 'active bg-light' : '' }}" href="{{ route('lang.switch', 'en') }}">
                                            <span class="flag flag-xs flag-country-gb rounded"></span>
                                            <span class="fs-7 fw-medium">English</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-save-settings w-100 py-2.5">
                                <i class="bi bi-save me-1.5"></i> Simpan Perubahan
                            </button>
                        </div>
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
