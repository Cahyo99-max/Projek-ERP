<x-layouts title="Input Peminjaman Barang Pameran">

    <link rel="stylesheet" href="{{ asset('inventori/pinjam_barang.css') }}?v={{ time() }}">

    <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4 peminjaman-form-container">
        <!-- Breadcrumb & Top Bar -->
        <div class="form-header-wrapper d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="section-badge flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-page-title form-page-title">Form Pengajuan Peminjaman Barang</h3>
                        <p class="text-muted small mb-0 form-page-sub">Isi data pameran dan pilih barang dari katalog persediaan gudang fisik.</p>
                    </div>
                </div>
            </div>
            <div class="form-header-actions">
                <a href="{{ route('peminjaman.index') }}" class="btn btn-cinematic-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <form id="form-peminjaman" autocomplete="off">
            @csrf
            <div class="row g-3 g-md-4">
                <!-- Top Row: 1. Informasi Acara & Jadwal Pameran -->
                <div class="col-12 col-lg-7">
                    <div class="card cinematic-card p-3 p-sm-4 h-100">
                        <div class="section-title">
                            <span class="section-badge"><i class="bi bi-calendar2-event-fill"></i></span>
                            <span>1. Informasi Acara &amp; Jadwal Pameran</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                    <label class="form-label-cinematic mb-0">No. Dokumen Peminjaman <span class="text-danger">*</span></label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="toggle_nomor_auto" checked>
                                        <label class="form-check-label small text-primary fw-semibold" for="toggle_nomor_auto" id="label_nomor_mode">
                                            <i class="bi bi-magic me-1"></i>Otomatis
                                        </label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-cinematic fw-bold text-primary" name="nomor_peminjaman" id="nomor_peminjaman" value="{{ $autoNomor }}" readonly required autocomplete="off" placeholder="Contoh: PB-202610-0001">
                                    <button type="button" class="btn btn-refresh-input" id="btn-refresh-nomor" title="Ambil Nomor Urut Otomatis Terbaru">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="is_nomor_otomatis" id="is_nomor_otomatis" value="1">
                                <small class="text-muted d-block mt-1" id="nomor_help_text">
                                    <i class="bi bi-shield-check text-success me-1"></i>Nomor digenerate otomatis dan dijamin anti-duplikasi.
                                </small>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label-cinematic">Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-cinematic" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ date('Y-m-d') }}" required autocomplete="off">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label-cinematic">Rencana Kembali <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-cinematic" name="tanggal_kembali_rencana" id="tanggal_kembali_rencana" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required autocomplete="off">
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label-cinematic mb-0">Nama Acara / Pameran <span class="text-danger">*</span></label>
                                    <small class="text-muted" style="font-size: 0.75rem;"><span id="nama_acara_count">0</span>/40 Karakter</small>
                                </div>
                                <input type="text" class="form-control form-control-cinematic" name="nama_acara" id="nama_acara" maxlength="40" placeholder="Contoh: Indonesia Industrial Expo 2026" required autocomplete="off">
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label-cinematic mb-0">Lokasi Acara / Stand Booth</label>
                                    <small class="text-muted" style="font-size: 0.75rem;"><span id="lokasi_acara_count">0</span>/50 Karakter</small>
                                </div>
                                <input type="text" class="form-control form-control-cinematic" name="lokasi_acara" id="lokasi_acara" maxlength="50" placeholder="Contoh: Hall B Booth 42, JIExpo Kemayoran" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Row: Alokasi Gudang -->
                <div class="col-12 col-lg-5">
                    <div class="card cinematic-card p-3 p-sm-4 h-100">
                        <div class="section-title">
                            <span class="section-badge"><i class="bi bi-building-gear"></i></span>
                            <span>Alokasi Gudang</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-12">
                                <label class="form-label-cinematic">Gudang Asal Barang <span class="text-danger">*</span></label>
                                <select class="form-select form-control-cinematic" name="gudang_asal_id" id="gudang_asal_id" required>
                                    <option value="">-- Pilih Gudang Asal --</option>
                                    @php
                                        $defaultAsal = $gudangs->firstWhere('id', '!=', $gudangPameran->id) ?? $gudangs->first();
                                    @endphp
                                    @foreach ($gudangs as $g)
                                        <option value="{{ $g->id }}" {{ $defaultAsal && $defaultAsal->id == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_gudang }} ({{ $g->kode_gudang }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Pencarian barang disaring dari stok gudang ini.</small>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-12">
                                <label class="form-label-cinematic">Gudang Transit Pameran (Tujuan) <span class="text-danger">*</span></label>
                                <select class="form-select form-control-cinematic" name="gudang_pameran_id" id="gudang_pameran_id" required>
                                    <option value="">-- Pilih Gudang Transit (Tujuan) --</option>
                                    @foreach ($gudangs as $g)
                                        <option value="{{ $g->id }}" {{ $g->id == $gudangPameran->id ? 'selected' : '' }}>
                                            {{ $g->nama_gudang }} ({{ $g->kode_gudang }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-primary d-block mt-1"><i class="bi bi-shield-check me-1"></i>Stok otomatis berpindah saat barang diserahkan.</small>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label-cinematic mb-0">Keterangan Tambahan</label>
                                    <small class="text-muted" style="font-size: 0.75rem;"><span id="keterangan_count">0</span>/40 Karakter</small>
                                </div>
                                <textarea class="form-control form-control-cinematic" name="keterangan" id="keterangan" rows="2" maxlength="40" placeholder="Tuliskan tujuan display, instruksi handling (maks. 40 karakter)..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Full Width Row: 2. Daftar Barang yang Dipinjam (Lebar ke samping, 100% full width) -->
                <div class="col-12">
                    <div class="card cinematic-card p-3 p-sm-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div class="section-title mb-0">
                                <span class="section-badge"><i class="bi bi-boxes"></i></span>
                                <span>2. Daftar Barang yang Dipinjam</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-counter-adaptive px-2.5 py-1.5" id="row-count-badge">
                                    <i class="bi bi-list-check me-1"></i><span id="row-count-num">1</span> / 80 Baris
                                </span>
                                <button type="button" class="btn btn-cinematic-primary btn-sm d-inline-flex align-items-center gap-2" id="btn-add-item">
                                    <i class="bi bi-plus-lg"></i> Tambah Baris
                                </button>
                            </div>
                        </div>

                        <!-- Mobile Swipe Indicator Banner -->
                        <div class="d-flex d-md-none align-items-center justify-content-between px-2.5 py-1.5 mb-2 rounded-3 text-primary shadow-2xs" style="background: rgba(37, 99, 235, 0.08); font-size: 0.775rem; border: 1px dashed rgba(37, 99, 235, 0.25);">
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-arrow-left-right"></i>
                                <span>Tabel bisa digeser ke samping</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
                        </div>

                        <div class="table-responsive table-cinematic-wrapper">
                            <table class="table-cinematic align-middle mb-0" id="table-items">
                                <thead>
                                    <tr>
                                        <th style="min-width: 320px; width: 44%;">Barang / Produk <span class="text-danger">*</span></th>
                                        <th style="min-width: 110px; width: 14%;" class="text-center">Stok Gudang</th>
                                        <th style="min-width: 120px; width: 15%;" class="text-center">Qty Pinjam <span class="text-danger">*</span></th>
                                        <th style="min-width: 180px; width: 21%;">Kondisi</th>
                                        <th style="min-width: 60px; width: 6%;" class="text-center"><i class="bi bi-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="item-tbody">
                                    <!-- Dynamic rows added here -->
                                </tbody>
                            </table>
                        </div>
                        <div id="item-empty-notice" class="text-center py-4 rounded-3 mt-2 d-none" style="background: var(--input-readonly-bg); border: 1.5px dashed var(--input-border);">
                            <i class="bi bi-box-seam fs-2 text-muted d-block mb-1"></i>
                            <span class="text-muted small">Belum ada barang ditambahkan. Klik tombol <strong>Tambah Baris</strong> di atas.</span>
                        </div>

                        <!-- Tombol Aksi Submit di Footer Tabel Barang -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
                            <div></div>
                            <div class="form-footer-actions d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto justify-content-end">
                                <button type="button" class="btn btn-cinematic-secondary px-3 px-sm-4 py-2.5" id="btn-submit-draft">
                                    <i class="bi bi-floppy me-1"></i> Simpan Sebagai Draft
                                </button>
                                <button type="button" class="btn btn-cinematic-primary px-3 px-sm-4 py-2.5 d-flex align-items-center justify-content-center gap-2" id="btn-submit-ajukan">
                                    <i class="bi bi-send-fill"></i> Simpan &amp; Ajukan Peminjaman
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- MODAL LOOKUP BARANG (PENCARIAN MODAL SINEMATIK & PROFESIONAL) -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalLookupBarang" tabindex="-1" aria-labelledby="modalLookupLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1050px;">
            <div class="modal-content modal-lookup-content">
                <div class="modal-header modal-lookup-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-badge" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-seam-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-adaptive mb-0" id="modalLookupLabel">Katalog Barang di Gudang Asal</h5>
                                <span class="badge badge-counter-adaptive rounded-pill px-2.5 py-0.5 small" id="lookupCountBadge">0 Barang</span>
                            </div>
                            <small class="text-muted">Gudang Sumber: <strong id="lookupGudangName" class="text-primary">-</strong></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="background: var(--modal-bg);">
                    <!-- Search Input Box -->
                    <div class="input-group mb-3">
                        <span class="input-group-text border-end-0 text-primary" style="background: var(--input-bg); border: 1.5px solid var(--input-border); border-right: none;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="lookupSearchInput" class="form-control form-control-cinematic border-start-0 ps-0" placeholder="Ketik kode barang, nama produk, atau kategori untuk menyaring..." autofocus autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary border-start-0 d-none" id="btnResetSearch" title="Hapus Pencarian" style="border: 1.5px solid var(--input-border); border-left: none;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Table of Items -->
                    <div class="table-responsive table-cinematic-wrapper" style="max-height: 440px;">
                        <table class="table-cinematic align-middle mb-0" id="tableLookupBarang">
                            <thead class="sticky-top">
                                <tr>
                                    <th style="width: 5%;" class="text-center">No</th>
                                    <th style="width: 9%;" class="text-center">Foto</th>
                                    <th style="width: 17%;">Kode Barang</th>
                                    <th style="width: 31%;">Nama Barang</th>
                                    <th style="width: 16%;">Kategori</th>
                                    <th style="width: 12%;" class="text-center">Stok Gudang</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="lookupBarangTbody">
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                                        <span>Memuat data persediaan barang...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pilih barang yang ingin dipinjam. Klik foto untuk melihat gambar lebih jelas.</small>
                    <button type="button" class="btn btn-sm btn-cinematic-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Foto Produk (Cinematic Zoom) -->
    <div class="modal fade" id="modalPreviewFoto" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-lookup-content">
                <div class="modal-header modal-lookup-header d-flex justify-content-between align-items-center py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-counter-adaptive px-2.5 py-1.5" id="previewFotoKode">-</span>
                        <h6 class="modal-title fw-bold text-adaptive mb-0" id="previewFotoNama">Foto Produk</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4" style="background: var(--modal-bg);">
                    <div class="position-relative d-inline-block rounded-3 overflow-hidden p-1" style="max-height: 65vh; background: var(--thumb-bg); border: 1.5px solid var(--input-border);">
                        <img src="" id="previewFotoImg" class="img-fluid rounded-3" style="max-height: 62vh; object-fit: contain; width: auto;" alt="Foto Produk">
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Foto resmi barang dari Master Data Persediaan.</small>
                    <button type="button" class="btn btn-sm btn-cinematic-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Section -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function () {
            let rowIndex = 0;
            let currentActiveRowForLookup = null;
            let cachedWarehouseItems = [];

            // 1. Auto / Manual Nomor Dokumen Peminjaman
            let autoNomorBackup = "{{ $autoNomor }}";

            $('#toggle_nomor_auto').on('change', function () {
                const isAuto = $(this).is(':checked');
                $('#is_nomor_otomatis').val(isAuto ? '1' : '0');

                if (isAuto) {
                    $('#label_nomor_mode').html('<i class="bi bi-magic me-1"></i>Otomatis').removeClass('text-warning').addClass('text-primary');
                    $('#nomor_peminjaman').prop('readonly', true).removeClass('text-adaptive text-white').addClass('text-primary').val(autoNomorBackup);
                    $('#btn-refresh-nomor').removeClass('d-none');
                    $('#nomor_help_text').html('<i class="bi bi-shield-check text-success me-1"></i>Nomor digenerate otomatis dan dijamin anti-duplikasi.');
                    refreshAutoNomor();
                } else {
                    $('#label_nomor_mode').html('<i class="bi bi-pencil me-1"></i>Manual').removeClass('text-primary').addClass('text-warning');
                    $('#nomor_peminjaman').prop('readonly', false).removeClass('text-primary').addClass('text-adaptive').focus();
                    $('#btn-refresh-nomor').addClass('d-none');
                    $('#nomor_help_text').html('<i class="bi bi-info-circle text-primary me-1"></i>Mode manual: ketik nomor dokumen Anda sendiri. Sistem akan memverifikasi anti-duplikat.');
                }
            });

            function refreshAutoNomor() {
                $('#btn-refresh-nomor').prop('disabled', true).find('i').addClass('bi-spin');
                $.ajax({
                    url: "{{ route('peminjaman.generateNomor') }}",
                    type: 'GET',
                    success: function (res) {
                        $('#btn-refresh-nomor').prop('disabled', false).find('i').removeClass('bi-spin');
                        if (res.success && res.nomor) {
                            autoNomorBackup = res.nomor;
                            if ($('#toggle_nomor_auto').is(':checked')) {
                                $('#nomor_peminjaman').val(res.nomor);
                            }
                        }
                    },
                    error: function () {
                        $('#btn-refresh-nomor').prop('disabled', false).find('i').removeClass('bi-spin');
                    }
                });
            }

            $('#btn-refresh-nomor').on('click', function () {
                refreshAutoNomor();
            });

            // Debounced check for manual nomor uniqueness
            let nomorCheckTimeout = null;
            $('#nomor_peminjaman').on('input', function () {
                if ($('#toggle_nomor_auto').is(':checked')) return;
                const val = $(this).val().trim();
                if (!val) {
                    $('#nomor_help_text').html('<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Nomor dokumen manual wajib diisi.</span>');
                    return;
                }

                clearTimeout(nomorCheckTimeout);
                nomorCheckTimeout = setTimeout(function () {
                    $.ajax({
                        url: "{{ route('peminjaman.checkNomor') }}",
                        type: 'GET',
                        data: { nomor: val },
                        success: function (res) {
                            if (res.valid) {
                                $('#nomor_help_text').html('<span class="text-success"><i class="bi bi-check-circle me-1"></i>Nomor dokumen tersedia & valid.</span>');
                            } else {
                                $('#nomor_help_text').html('<span class="text-danger"><i class="bi bi-x-circle me-1"></i>' + res.message + '</span>');
                            }
                        }
                    });
                }, 350);
            });

            // Live Character Counters and Numeric Sanitizer
            $('#nama_acara').on('input', function () {
                if (this.value.length > 40) this.value = this.value.slice(0, 40);
                $('#nama_acara_count').text(this.value.length);
            });

            $('#lokasi_acara').on('input', function () {
                if (this.value.length > 50) this.value = this.value.slice(0, 50);
                $('#lokasi_acara_count').text(this.value.length);
            });

            $('#keterangan').on('input', function () {
                if (this.value.length > 40) this.value = this.value.slice(0, 40);
                $('#keterangan_count').text(this.value.length);
            });

            // 3. Table Row Add/Remove Logic with 80 Max Guard
            const MAX_ROWS = 80;

            function updateRowCountBadge() {
                const count = $('#item-tbody tr').length;
                $('#row-count-num').text(count);
                if (count >= MAX_ROWS) {
                    $('#row-count-badge')
                        .removeClass('badge-counter-adaptive')
                        .addClass('bg-danger bg-opacity-25 text-danger border border-danger');
                } else {
                    $('#row-count-badge')
                        .removeClass('bg-danger bg-opacity-25 text-danger border border-danger')
                        .addClass('badge-counter-adaptive');
                }
            }

            function checkEmptyTable() {
                if ($('#item-tbody tr').length === 0) {
                    $('#item-empty-notice').removeClass('d-none');
                } else {
                    $('#item-empty-notice').addClass('d-none');
                }
                updateRowCountBadge();
            }

            function addRow() {
                const currentCount = $('#item-tbody tr').length;
                if (currentCount >= MAX_ROWS) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Maksimal 80 Baris!',
                        html: `Daftar barang yang dipinjam telah mencapai batas maksimum <strong>80 baris</strong> per dokumen.<br><br>Jika kebutuhan barang lebih banyak, mohon buat dokumen peminjaman baru untuk menjaga kestabilan data & persediaan.`,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#e11d48'
                    });
                    return;
                }

                const gudangId = $('#gudang_asal_id').val();
                if (!gudangId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Gudang Asal',
                        text: 'Silakan tentukan Gudang Asal terlebih dahulu sebelum menambah barang.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                rowIndex++;
                const rowHtml = `
                    <tr id="row-${rowIndex}" data-index="${rowIndex}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="item-thumb-container" id="thumb_container_${rowIndex}">
                                    <div class="product-thumb-placeholder-sm" title="Belum ada barang dipilih">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm flex-grow-1">
                                    <input type="text" class="form-control form-control-cinematic text-truncate item-barang-display" id="display_name_${rowIndex}" placeholder="Klik tombol Cari untuk memilih barang..." readonly style="cursor: pointer;" autocomplete="off">
                                    <input type="hidden" name="items[${rowIndex}][barang_id]" id="barang_id_${rowIndex}" required>
                                    <input type="hidden" name="items[${rowIndex}][satuan_id]" id="satuan_id_${rowIndex}">
                                    <button type="button" class="btn btn-search-product btn-open-lookup" data-row="${rowIndex}" title="Cari Barang di Gudang">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-stok-cinematic bg-secondary bg-opacity-25 text-muted border border-secondary border-opacity-25 stok-badge" id="stok_badge_${rowIndex}">-</span>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm form-control-cinematic text-center input-qty" name="items[${rowIndex}][qty_pinjam]" id="qty_${rowIndex}" value="1" min="0.0001" step="any" required autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm form-control-cinematic" name="items[${rowIndex}][kondisi_awal]" value="Bagus / Siap Pakai" autocomplete="off">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-row" data-row="row-${rowIndex}" title="Hapus Baris">
                                <i class="bi bi-trash fs-6"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#item-tbody').append(rowHtml);
                checkEmptyTable();
            }

            $('#btn-add-item').on('click', function () {
                addRow();
            });

            $(document).on('click', '.btn-remove-row', function () {
                const targetRow = $(this).data('row');
                $(`#${targetRow}`).remove();
                checkEmptyTable();
            });

            // 3. Modal Lookup Barang Handlers
            $(document).on('click', '.btn-open-lookup, .item-barang-display', function () {
                const row = $(this).closest('tr').data('index');
                currentActiveRowForLookup = row;
                openLookupModal();
            });

            function openLookupModal() {
                const gudangId = $('#gudang_asal_id').val();
                const gudangName = $('#gudang_asal_id option:selected').text();

                if (!gudangId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Gudang Asal',
                        text: 'Silakan pilih Gudang Asal terlebih dahulu!',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                $('#lookupGudangName').text(gudangName);
                $('#lookupSearchInput').val('');
                $('#btnResetSearch').addClass('d-none');
                $('#modalLookupBarang').modal('show');

                loadLookupData(gudangId);
            }

            function loadLookupData(gudangId) {
                $('#lookupBarangTbody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            <span>Memuat data persediaan barang di ${$('#gudang_asal_id option:selected').text()}...</span>
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: `/peminjaman-barang/barangs/${gudangId}`,
                    type: 'GET',
                    success: function (items) {
                        cachedWarehouseItems = items;
                        renderLookupTable(items);
                    },
                    error: function () {
                        $('#lookupBarangTbody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5 text-danger">
                                    <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                                    Gagal memuat persediaan barang. Pastikan koneksi atau hak akses valid.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function renderLookupTable(items) {
                $('#lookupCountBadge').text(`${items.length} Barang`);

                if (items.length === 0) {
                    $('#lookupBarangTbody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada barang ditemukan pada gudang ini.
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                items.forEach((item, idx) => {
                    const stokVal = parseFloat(item.stok) || 0;
                    const isReady = stokVal > 0;
                    const badgeClass = isReady ? 'badge-stok-ready' : 'badge-stok-empty';
                    const fotoHtml = item.foto_url 
                        ? `<div class="product-thumb-box btn-show-photo-modal" data-url="${item.foto_url}" data-nama="${item.nama_barang}" data-kode="${item.kode_barang}" title="Klik untuk memperbesar foto">
                               <img src="${item.foto_url}" alt="${item.nama_barang}">
                               <div class="product-thumb-overlay"><i class="bi bi-zoom-in"></i></div>
                           </div>`
                        : `<div class="product-thumb-placeholder" title="Tidak ada foto">
                               <i class="bi bi-image"></i>
                           </div>`;

                    html += `
                        <tr class="lookup-item-row" data-id="${item.id}" data-kode="${item.kode_barang}" data-nama="${item.nama_barang}" data-satuan-id="${item.satuan_id || ''}" data-satuan="${item.satuan}" data-stok="${stokVal}" data-foto-url="${item.foto_url || ''}">
                            <td class="text-center text-muted">${idx + 1}</td>
                            <td class="text-center">${fotoHtml}</td>
                            <td><span class="fw-bold text-primary">${item.kode_barang}</span></td>
                            <td><span class="fw-semibold text-adaptive">${item.nama_barang}</span></td>
                            <td><span class="badge badge-counter-adaptive">${item.kategori || '-'}</span></td>
                            <td class="text-center">
                                <span class="badge badge-stok-cinematic ${badgeClass}">${stokVal} ${item.satuan}</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm ${isReady ? 'btn-cinematic-primary' : 'btn-outline-secondary disabled'} btn-select-item" data-id="${item.id}" ${!isReady ? 'disabled' : ''}>
                                    <i class="bi bi-check-lg me-1"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#lookupBarangTbody').html(html);
            }

            // Photo Preview Modal Trigger
            $(document).on('click', '.btn-show-photo-modal', function (e) {
                e.stopPropagation();
                const url = $(this).data('url');
                const nama = $(this).data('nama') || 'Foto Produk';
                const kode = $(this).data('kode') || '-';

                if (!url) return;

                $('#previewFotoImg').attr('src', url);
                $('#previewFotoNama').text(nama);
                $('#previewFotoKode').text(kode);
                $('#modalPreviewFoto').modal('show');
            });

            // Live Search inside Lookup Modal
            $('#lookupSearchInput').on('input', function () {
                const keyword = $(this).val().toLowerCase().trim();
                $('#btnResetSearch').toggleClass('d-none', keyword.length === 0);

                if (!keyword) {
                    renderLookupTable(cachedWarehouseItems);
                    return;
                }

                const filtered = cachedWarehouseItems.filter(item => {
                    return (item.kode_barang && item.kode_barang.toLowerCase().includes(keyword)) ||
                           (item.nama_barang && item.nama_barang.toLowerCase().includes(keyword)) ||
                           (item.kategori && item.kategori.toLowerCase().includes(keyword));
                });

                renderLookupTable(filtered);
            });

            $('#btnResetSearch').on('click', function () {
                $('#lookupSearchInput').val('').trigger('input').focus();
            });

            // Handle Selecting an Item from Lookup
            $(document).on('click', '.btn-select-item, .lookup-item-row', function (e) {
                // If clicked on row or button
                const rowElem = $(this).hasClass('lookup-item-row') ? $(this) : $(this).closest('.lookup-item-row');
                const itemId = rowElem.data('id');
                const kode = rowElem.data('kode');
                const nama = rowElem.data('nama');
                const satuanId = rowElem.data('satuan-id');
                const satuan = rowElem.data('satuan');
                const stok = parseFloat(rowElem.data('stok')) || 0;
                const fotoUrl = rowElem.data('foto-url') || '';

                if (stok <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Habis',
                        text: `Barang "${nama}" memiliki stok 0 di gudang ini, sehingga tidak dapat dipinjam.`,
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                if (currentActiveRowForLookup) {
                    const row = currentActiveRowForLookup;
                    $(`#display_name_${row}`).val(`${kode} - ${nama}`);
                    $(`#barang_id_${row}`).val(itemId);
                    $(`#satuan_id_${row}`).val(satuanId);
                    
                    // Update Thumbnail in row
                    if (fotoUrl) {
                        $(`#thumb_container_${row}`).html(`
                            <div class="product-thumb-sm btn-show-photo-modal" data-url="${fotoUrl}" data-nama="${nama}" data-kode="${kode}" title="Klik untuk memperbesar foto">
                                <img src="${fotoUrl}" alt="${nama}">
                                <div class="product-thumb-overlay"><i class="bi bi-zoom-in"></i></div>
                            </div>
                        `);
                    } else {
                        $(`#thumb_container_${row}`).html(`
                            <div class="product-thumb-placeholder-sm" title="Tidak ada foto">
                                <i class="bi bi-image"></i>
                            </div>
                        `);
                    }

                    const badge = $(`#stok_badge_${row}`);
                    badge.text(`${stok} ${satuan}`)
                         .removeClass('bg-secondary bg-danger bg-opacity-25 text-muted text-danger border-secondary border-danger')
                         .addClass('badge-stok-ready');

                    const $qtyInput = $(`#qty_${row}`);
                    $qtyInput.attr('max', stok);
                    $qtyInput.data('max-stok', stok);
                    $qtyInput.data('satuan', satuan);
                    $qtyInput.data('nama-barang', nama);

                    // Check if current qty exceeds the newly selected stock
                    const currentQty = parseFloat($qtyInput.val()) || 0;
                    if (currentQty > stok) {
                        $qtyInput.val(stok);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Qty Melebihi Stok Gudang!',
                            html: `<div class="text-start">
                                <p class="mb-2">Jumlah pinjam untuk <strong>${nama}</strong> tidak boleh melebihi persediaan gudang asal.</p>
                                <div class="p-2.5 rounded-3 mb-2" style="background: rgba(225, 29, 72, 0.15); border: 1px solid rgba(225, 29, 72, 0.3);">
                                    <div class="d-flex justify-content-between small text-white">
                                        <span>Stok Tersedia di Gudang:</span>
                                        <span class="fw-bold text-success">${stok} ${satuan}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-white mt-1">
                                        <span>Qty Sebelumnya:</span>
                                        <span class="fw-bold text-danger">${currentQty} ${satuan}</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Jumlah pinjam otomatis disesuaikan ke stok maksimal (<strong>${stok}</strong>).</p>
                            </div>`,
                            confirmButtonText: 'Saya Mengerti',
                            confirmButtonColor: '#e11d48'
                        });
                    }
                }

                $('#modalLookupBarang').modal('hide');
            });

            // Real-time QTY check against Warehouse Stock
            $(document).on('input change', '.input-qty', function () {
                const $input = $(this);
                const row = $input.closest('tr');
                const maxStokAttr = $input.data('max-stok');
                const maxStok = maxStokAttr !== undefined ? parseFloat(maxStokAttr) : (parseFloat($input.attr('max')) || null);
                const namaBarang = $input.data('nama-barang') || row.find('.item-barang-display').val() || 'Barang terpilih';
                const satuan = $input.data('satuan') || '';
                const currentVal = parseFloat($input.val());

                if (maxStok !== null && !isNaN(maxStok) && !isNaN(currentVal)) {
                    if (currentVal > maxStok) {
                        $input.val(maxStok);
                        $input.addClass('border-danger is-invalid');
                        setTimeout(() => {
                            $input.removeClass('border-danger is-invalid');
                        }, 1800);

                        Swal.fire({
                            icon: 'warning',
                            title: 'Qty Pinjam Melebihi Stok!',
                            html: `<div class="text-start">
                                <p class="mb-2">Jumlah peminjaman untuk <strong>${namaBarang}</strong> melebihi persediaan gudang asal.</p>
                                <div class="p-2.5 rounded-3 mb-2" style="background: rgba(225, 29, 72, 0.15); border: 1px solid rgba(225, 29, 72, 0.3);">
                                    <div class="d-flex justify-content-between small">
                                        <span>Stok Gudang Tersedia:</span>
                                        <span class="fw-bold text-success">${maxStok} ${satuan}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span>Qty yang Diminta:</span>
                                        <span class="fw-bold text-danger">${currentVal} ${satuan}</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Nilai telah disesuaikan otomatis ke stok maksimal (<strong>${maxStok}</strong>).</p>
                            </div>`,
                            confirmButtonText: 'Saya Mengerti',
                            confirmButtonColor: '#e11d48'
                        });
                    }
                }
            });

            // Safety Guard: Gudang Asal & Gudang Transit (Tujuan) cannot be the same
            function checkWarehouseConflict(sourceField) {
                const asalId = $('#gudang_asal_id').val();
                const tujuanId = $('#gudang_pameran_id').val();

                if (asalId && tujuanId && asalId === tujuanId) {
                    const warehouseName = $(`#${sourceField} option:selected`).text().trim();
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gudang Tidak Boleh Sama!',
                        html: `<div class="text-start">
                            <p class="mb-2"><strong>Gudang Asal Barang</strong> dan <strong>Gudang Transit Pameran (Tujuan)</strong> tidak boleh menggunakan gudang yang sama!</p>
                            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 py-2 px-3 small text-danger mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Gudang yang dipilih: <strong>${warehouseName}</strong>
                            </div>
                            <p class="mb-0 text-muted small">Peminjaman memerlukan alokasi gudang transit yang berbeda dari lokasi persediaan fisik asal.</p>
                        </div>`,
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Pilih Gudang Lain',
                        confirmButtonColor: '#e11d48'
                    });

                    // Reset the conflicting field
                    $(`#${sourceField}`).val('').focus();
                    return false;
                }
                return true;
            }

            $('#gudang_pameran_id').on('change', function () {
                checkWarehouseConflict('gudang_pameran_id');
            });

            // When gudang asal changes, verify conflict and clear items because stock belongs to that warehouse
            $('#gudang_asal_id').on('change', function () {
                if (!checkWarehouseConflict('gudang_asal_id')) {
                    return;
                }
                if ($('#item-tbody tr').length > 0) {
                    Swal.fire({
                        title: 'Ganti Gudang Asal?',
                        text: 'Mengubah gudang asal akan mengosongkan barang yang sudah dipilih karena persediaan fisik berbeda.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ganti',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#4f46e5'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#item-tbody').empty();
                            checkEmptyTable();
                            addRow();
                        }
                    });
                }
            });

            // Add first row by default
            addRow();

            // 4. Form Submit Handling (Draft vs Submit)
            function submitForm(actionType) {
                const form = $('#form-peminjaman')[0];
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const asalId = $('#gudang_asal_id').val();
                const tujuanId = $('#gudang_pameran_id').val();
                if (!asalId) {
                    Swal.fire('Perhatian', 'Silakan pilih Gudang Asal Barang!', 'warning');
                    return;
                }
                if (!tujuanId) {
                    Swal.fire('Perhatian', 'Silakan pilih Gudang Transit Pameran (Tujuan)!', 'warning');
                    return;
                }
                if (asalId === tujuanId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gudang Tidak Boleh Sama!',
                        text: 'Gudang Asal Barang dan Gudang Transit Pameran (Tujuan) tidak boleh sama. Silakan pilih gudang yang berbeda.',
                        confirmButtonColor: '#e11d48'
                    });
                    return;
                }

                const nomorDoc = $('#nomor_peminjaman').val().trim();
                if (!nomorDoc) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor Dokumen Kosong',
                        text: 'Silakan isi No. Dokumen Peminjaman terlebih dahulu.',
                        confirmButtonColor: '#4f46e5'
                    });
                    $('#nomor_peminjaman').focus();
                    return;
                }

                const totalRows = $('#item-tbody tr').length;
                if (totalRows === 0) {
                    Swal.fire('Peringatan', 'Minimal tambahkan 1 barang untuk dipinjam!', 'warning');
                    return;
                }

                if (totalRows > 80) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maksimal 80 Baris!',
                        text: `Jumlah barang saat ini (${totalRows} baris) melebihi batas maksimum 80 baris. Mohon kurangi daftar barang.`,
                        confirmButtonColor: '#e11d48'
                    });
                    return;
                }

                // Verify every row has barang_id selected and qty doesn't exceed warehouse stock
                let missingItem = false;
                let overStockItem = null;
                $('#item-tbody tr').each(function () {
                    const bId = $(this).find('input[name*="[barang_id]"]').val();
                    if (!bId) {
                        missingItem = true;
                        return false;
                    }

                    const qtyInput = $(this).find('.input-qty');
                    const qty = parseFloat(qtyInput.val()) || 0;
                    const maxStokAttr = qtyInput.data('max-stok');
                    const maxStok = maxStokAttr !== undefined ? parseFloat(maxStokAttr) : (parseFloat(qtyInput.attr('max')) || null);
                    const namaBarang = $(this).find('.item-barang-display').val() || 'Barang';

                    if (maxStok !== null && !isNaN(maxStok) && qty > maxStok) {
                        overStockItem = { nama: namaBarang, qty: qty, max: maxStok, input: qtyInput };
                        return false;
                    }
                });

                if (missingItem) {
                    Swal.fire('Perhatian', 'Pastikan semua baris sudah memilih barang melalui tombol Cari!', 'warning');
                    return;
                }

                if (overStockItem) {
                    overStockItem.input.val(overStockItem.max).focus();
                    Swal.fire({
                        icon: 'error',
                        title: 'Qty Melebihi Stok Gudang!',
                        html: `Jumlah pinjam untuk <strong>${overStockItem.nama}</strong> (${overStockItem.qty}) melebihi persediaan gudang (${overStockItem.max}).<br>Silakan sesuaikan jumlah pinjam sebelum menyimpan.`,
                        confirmButtonColor: '#e11d48'
                    });
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', actionType);

                const btn = actionType === 'submit' ? $('#btn-submit-ajukan') : $('#btn-submit-draft');
                const origHtml = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('peminjaman.store') }}",
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
                                confirmButtonText: 'Lihat Dokumen',
                                confirmButtonColor: '#4f46e5'
                            }).then(() => {
                                window.location.href = res.redirect_url;
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                            btn.prop('disabled', false).html(origHtml);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(origHtml);
                        let errMsg = 'Terjadi kesalahan sistem saat menyimpan.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                errMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                        }
                        Swal.fire('Gagal Menyimpan!', errMsg, 'error');
                    }
                });
            }

            $('#btn-submit-ajukan').on('click', function () {
                Swal.fire({
                    title: 'Ajukan Peminjaman?',
                    text: 'Dokumen akan diajukan ke Atasan (Mengetahui) untuk diverifikasi.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan Sekarang!',
                    cancelButtonText: 'Periksa Lagi',
                    confirmButtonColor: '#4f46e5'
                }).then((r) => {
                    if (r.isConfirmed) submitForm('submit');
                });
            });

            $('#btn-submit-draft').on('click', function () {
                submitForm('draft');
            });
        });
    </script>
    @endpush
</x-layouts>
