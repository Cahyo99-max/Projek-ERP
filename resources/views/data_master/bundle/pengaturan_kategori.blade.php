<x-layouts title="Setting Visibilitas Kategori (Bundle & Sales)">
    <div class="container-fluid px-4 py-3">
        
        {{-- HEADER TITLE & ACTION BUTTON --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold spk-text-contrast mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2-vertical text-warning fs-3"></i>
                    <span>Setting Visibilitas Kategori</span>
                </h4>
                <p class="spk-text-muted mb-0 small">
                    Atur kategori barang mana saja yang diizinkan tampil saat pemilihan komponen <b>Master Bundle</b> dan katalog <b>Sales (Penjualan / POS)</b>.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('master-bundle.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 font-monospace fw-bold shadow-xs d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left fs-6"></i>
                    <span>Daftar Paket Bundle</span>
                </a>
            </div>
        </div>

        {{-- SUMMARY KPI GRADIENT CARDS --}}
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-blue p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">TOTAL KATEGORI</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardTotalKategori">{{ number_format($totalKategori, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-folder2-open fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-tags me-1 opacity-75"></i> Kategori Terdaftar
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-green p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">AKTIF DI BUNDLE</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardBundleKategori">{{ number_format($totalActiveBundleKategori, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-check-circle me-1 opacity-75"></i> Muncul di Pilihan Bundle
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-amber p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">AKTIF DI SALES (POS/SO)</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardSalesKategori">{{ number_format($totalActiveSalesKategori, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-shop me-1 opacity-75"></i> Muncul di Penjualan
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-orange p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">INTERNAL / BAHAN BAKU</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardInternalKategori">{{ number_format($totalInternalKategori, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-lock-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-eye-slash me-1 opacity-75"></i> Disembunyikan (Khusus Produksi)
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN MATRIX TABLE CARD --}}
        <div class="spk-card p-3 p-md-4 rounded-4 shadow-sm border border-secondary border-opacity-10 mb-4">
            
            {{-- TOOLBAR CONTROL --}}
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-warning fs-5"></i>
                    <h5 class="fw-bold spk-text-contrast mb-0">Matriks Izin Kategori (Live Auto-Save)</h5>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- Search Input --}}
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text spk-form-input border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchKategoriTable" class="form-control form-control-sm spk-form-input border-start-0" placeholder="Cari nama kategori...">
                    </div>

                    {{-- Bulk Actions Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 font-monospace fw-bold dropdown-toggle shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-lightning-charge me-1"></i> Aksi Cepat
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-secondary border-opacity-20 font-monospace" style="font-size: 12px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="executeBulkUpdate('enable_all')"><i class="bi bi-check-all text-success me-2"></i> Aktifkan Semua (Bundle &amp; Sales)</a></li>
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="executeBulkUpdate('enable_all_bundle')"><i class="bi bi-boxes text-info me-2"></i> Aktifkan Semua di Bundle</a></li>
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="executeBulkUpdate('enable_all_sales')"><i class="bi bi-cart text-primary me-2"></i> Aktifkan Semua di Sales</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="executeBulkUpdate('disable_all')"><i class="bi bi-x-circle me-2"></i> Nonaktifkan Semua (Mode Internal)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- TABLE MATRIX --}}
            <div class="table-responsive rounded-3 border border-secondary border-opacity-15">
                <table class="table table-hover align-middle mb-0" id="tableKategoriMatrix">
                    <thead class="spk-table-header">
                        <tr>
                            <th class="text-center" style="width: 45px;">NO</th>
                            <th style="min-width: 240px;">KODE &amp; NAMA KATEGORI</th>
                            <th class="text-center" style="min-width: 140px;">JUMLAH BARANG</th>
                            <th class="text-center" style="min-width: 180px;">TAMPIL DI MASTER BUNDLE</th>
                            <th class="text-center" style="min-width: 180px;">TAMPIL DI SALES (POS/SO)</th>
                            <th class="text-center" style="width: 100px;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $idx => $cat)
                        <tr class="kategori-row" data-search="{{ strtolower($cat->kode_kategori . ' ' . $cat->nama_kategori . ' ' . $cat->deskripsi) }}">
                            <td class="text-center font-monospace text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center py-1">
                                    <div class="rounded-3 border bg-secondary bg-opacity-10 me-3 d-inline-flex align-items-center justify-content-center text-info flex-shrink-0" style="width:40px; height:40px;">
                                        <i class="bi bi-folder2 fs-5"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                            <span class="badge spk-badge-bundle-code font-monospace px-2 py-0.5">{{ $cat->kode_kategori }}</span>
                                        </div>
                                        <span class="fw-bold spk-bundle-title" style="font-size: 13.5px;">{{ $cat->nama_kategori }}</span>
                                        @if($cat->deskripsi)
                                        <small class="spk-bundle-meta" style="font-size: 11px;">{{ $cat->deskripsi }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-20 text-body border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                    <i class="bi bi-box-seam me-1 text-info"></i>{{ number_format($cat->total_barang_count, 0, ',', '.') }} Item
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-flex align-items-center justify-content-center gap-2 m-0">
                                    <input class="form-check-input spk-switch-toggle cursor-pointer" type="checkbox" role="switch"
                                        id="switch_bundle_{{ $cat->id }}"
                                        data-id="{{ $cat->id }}"
                                        data-field="tampil_di_bundle"
                                        {{ $cat->tampil_di_bundle ? 'checked' : '' }}
                                        style="width: 44px; height: 22px;">
                                    <label class="form-check-label font-monospace fw-bold small cursor-pointer {{ $cat->tampil_di_bundle ? 'text-success' : 'text-muted' }}" 
                                        id="label_bundle_{{ $cat->id }}" for="switch_bundle_{{ $cat->id }}" style="font-size: 11px;">
                                        {{ $cat->tampil_di_bundle ? 'MUNCUL' : 'SEMBUNYI' }}
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-flex align-items-center justify-content-center gap-2 m-0">
                                    <input class="form-check-input spk-switch-toggle cursor-pointer" type="checkbox" role="switch"
                                        id="switch_sales_{{ $cat->id }}"
                                        data-id="{{ $cat->id }}"
                                        data-field="tampil_di_sales"
                                        {{ $cat->tampil_di_sales ? 'checked' : '' }}
                                        style="width: 44px; height: 22px;">
                                    <label class="form-check-label font-monospace fw-bold small cursor-pointer {{ $cat->tampil_di_sales ? 'text-primary' : 'text-muted' }}" 
                                        id="label_sales_{{ $cat->id }}" for="switch_sales_{{ $cat->id }}" style="font-size: 11px;">
                                        {{ $cat->tampil_di_sales ? 'MUNCUL' : 'SEMBUNYI' }}
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($cat->status === 'Active')
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.35); font-size: 10.5px;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                </span>
                                @else
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="background: rgba(148, 163, 184, 0.18); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.35); font-size: 10.5px;">
                                    <i class="bi bi-x-circle-fill me-1"></i>Nonaktif
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data kategori terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    @push('styles')
    <style>
        /* VIBRANT LUXURY RESPONSIVE KPI GRADIENT CARDS */
        .spk-kpi-card {
            border: none;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            color: #ffffff;
            cursor: default;
            min-height: 130px;
            border-top: 1px solid rgba(255, 255, 255, 0.4) !important;
            padding: 22px 24px !important;
        }
        .spk-kpi-card:hover {
            transform: translateY(-5px) scale(1.01);
        }

        .spk-kpi-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -20px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
            z-index: 1;
        }

        .spk-kpi-card::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 40px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
            z-index: 1;
        }
        
        .spk-kpi-blue {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%);
            box-shadow: 0 12px 28px -5px rgba(37, 99, 235, 0.55), 0 8px 10px -6px rgba(37, 99, 235, 0.3);
        }
        .spk-kpi-green {
            background: linear-gradient(135deg, #047857 0%, #059669 55%, #10b981 100%);
            box-shadow: 0 12px 28px -5px rgba(5, 150, 105, 0.55), 0 8px 10px -6px rgba(5, 150, 105, 0.3);
        }
        .spk-kpi-amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 55%, #f59e0b 100%);
            box-shadow: 0 12px 28px -5px rgba(217, 119, 6, 0.55), 0 8px 10px -6px rgba(217, 119, 6, 0.3);
        }
        .spk-kpi-orange {
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 55%, #f97316 100%);
            box-shadow: 0 12px 28px -5px rgba(234, 88, 12, 0.55), 0 8px 10px -6px rgba(234, 88, 12, 0.3);
        }

        .spk-kpi-label {
            font-size: 11px;
            letter-spacing: 0.8px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 700;
        }
        .spk-kpi-value {
            font-size: 2.3rem;
            line-height: 1.1;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .spk-kpi-subtext {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }
        .spk-kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        /* HIGH CONTRAST & CINEMATIC STYLING FOR DARK & LIGHT MODES */
        .spk-badge-bundle-code {
            background: rgba(14, 165, 233, 0.15) !important;
            color: #0369a1 !important;
            border: 1px solid rgba(14, 165, 233, 0.45) !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            letter-spacing: 0.4px;
        }
        html[data-bs-theme="dark"] .spk-badge-bundle-code {
            background: rgba(14, 165, 233, 0.25) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.6) !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.6) !important;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.2) !important;
        }

        .spk-bundle-title {
            color: #0f172a;
            letter-spacing: -0.2px;
        }
        html[data-bs-theme="dark"] .spk-bundle-title {
            color: #f8fafc !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .spk-bundle-meta {
            color: #475569;
        }
        html[data-bs-theme="dark"] .spk-bundle-meta {
            color: #94a3b8 !important;
        }

        .spk-table-header th {
            font-family: var(--bs-font-monospace, monospace);
            font-size: 11.5px;
            letter-spacing: 0.5px;
            padding: 12px 14px !important;
        }
        html:not([data-bs-theme="dark"]) .spk-table-header th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-table-header th {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #f1f5f9 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
        }

        .spk-switch-toggle:checked {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.5);
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Live Search in Table
        $('#searchKategoriTable').on('keyup', function() {
            const query = $(this).val().toLowerCase().trim();
            $('.kategori-row').each(function() {
                const searchData = $(this).data('search') || '';
                if (searchData.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // AJAX Toggle Listener
        $(document).on('change', '.spk-switch-toggle', function() {
            const el = $(this);
            const kategoriId = el.data('id');
            const field = el.data('field');
            const isChecked = el.is(':checked') ? 1 : 0;
            const labelEl = field === 'tampil_di_bundle' ? $(`#label_bundle_${kategoriId}`) : $(`#label_sales_${kategoriId}`);

            // Visual feedback
            if (field === 'tampil_di_bundle') {
                labelEl.text(isChecked ? 'MUNCUL' : 'SEMBUNYI').toggleClass('text-success text-muted');
            } else {
                labelEl.text(isChecked ? 'MUNCUL' : 'SEMBUNYI').toggleClass('text-primary text-muted');
            }

            $.ajax({
                url: "{{ route('master-bundle.pengaturan-kategori.toggle') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    kategori_id: kategoriId,
                    field: field,
                    value: isChecked
                },
                success: function(res) {
                    if (res.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: res.message
                        });
                    }
                },
                error: function(err) {
                    // Revert checkbox state on error
                    el.prop('checked', !isChecked);
                    Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal mengubah pengaturan visibilitas.', 'error');
                }
            });
        });

        // Bulk Actions
        function executeBulkUpdate(action) {
            Swal.fire({
                title: 'Jalankan Aksi Cepat?',
                text: 'Pengaturan visibilitas seluruh kategori akan disesuaikan secara otomatis.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Terapkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('master-bundle.pengaturan-kategori.bulk') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            action: action
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(err) {
                            Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memproses aksi.', 'error');
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</x-layouts>
