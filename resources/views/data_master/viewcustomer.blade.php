<x-layouts title="Data Customer / Pelanggan">

    @push('styles')
    <style>
        .nav-tabs-custom {
            border-bottom: 2px solid #e2e8f0;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 0.85rem 1.25rem;
            position: relative;
            background: transparent;
            transition: all 0.2s ease;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #2563eb;
        }
        .nav-tabs-custom .nav-link.active {
            color: #2563eb;
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #2563eb;
            border-radius: 2px;
        }
        .customer-detail-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        .customer-detail-val {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }
        .card-detail-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
        }
        [data-bs-theme="dark"] .card-detail-section {
            background-color: #0f172a;
            border-color: rgba(255, 255, 255, 0.08);
        }
        [data-bs-theme="dark"] .customer-detail-val {
            color: #f8fafc;
        }
        .summary-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            transition: all 0.25s ease;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Dashed advanced border styling */
        .card-advanced-options {
            border: 1.5px dashed #cbd5e1 !important;
            border-radius: 12px;
            overflow: hidden;
        }
        [data-bs-theme="dark"] .card-advanced-options {
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        
        .header-advanced-toggle {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s;
        }
        .header-advanced-toggle:hover {
            background-color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .header-advanced-toggle:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
        
        /* Arrow icon rotation on collapse */
        .header-advanced-toggle[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
        .header-advanced-toggle .bi-chevron-down {
            transition: transform 0.2s ease;
        }

        /* Dynamic Card UI enhancements */
        .address-card, .contact-card {
            transition: all 0.2s ease;
            border-radius: 10px !important;
        }
        .address-card:hover, .contact-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
            border-color: #2563eb !important;
        }
        [data-bs-theme="dark"] .address-card:hover, [data-bs-theme="dark"] .contact-card:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.03) !important;
        }
    </style>
    @endpush

    {{-- ===== SUMMARY CARDS ===== --}}
    @php
        $countTotal = \App\Models\MasterCustomer::count();
        $countActive = \App\Models\MasterCustomer::where('status', 'Active')->count();
        $countDistributor = \App\Models\MasterCustomer::where('jenis_customer', 'Distributor')->count();
        $countRetail = \App\Models\MasterCustomer::whereIn('jenis_customer', ['Retail', 'End User'])->count();
    @endphp
    <div class="container-fluid pt-3 px-2 px-md-4">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Customer</div>
                        <div class="fs-3 fw-bold mt-1 text-primary">{{ number_format($countTotal) }}</div>
                    </div>
                    <div class="summary-icon bg-primary-soft">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Aktif</div>
                        <div class="fs-3 fw-bold mt-1 text-success">{{ number_format($countActive) }}</div>
                    </div>
                    <div class="summary-icon bg-success-soft">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Distributor & Agen</div>
                        <div class="fs-3 fw-bold mt-1 text-info">{{ number_format($countDistributor) }}</div>
                    </div>
                    <div class="summary-icon bg-info-soft">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Retail & End User</div>
                        <div class="fs-3 fw-bold mt-1 text-warning">{{ number_format($countRetail) }}</div>
                    </div>
                    <div class="summary-icon bg-warning-soft">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Card -->
    <x-table_card tableId="tableCustomer" title="Data Customer / Pelanggan" icon="bi-people-fill"
        btnTarget="{{ auth()->user()->can('tambah-customer') ? '#modalTambahCustomer' : '' }}"
        btnText="Tambah Customer">

        <x-slot name="thead">
            <th width="5%" class="text-center py-3">No</th>
            <th class="py-3" width="12%">Kode</th>
            <th class="py-3" width="23%">Nama / Perusahaan</th>
            <th class="py-3" width="18%">Jenis & CP</th>
            <th class="py-3" width="18%">Hubungi</th>
            <th class="py-3" width="15%">Lokasi Utama</th>
            <th class="py-3 text-center" width="8%">Status</th>
            <th width="12%" class="text-center py-3">Aksi</th>
        </x-slot>
    </x-table_card>

    {{-- ===== MODAL TAMBAH ===== --}}
    <x-modal_form id="modalTambahCustomer" formId="formTambahCustomer" title="<i class='bi bi-people-fill me-2'></i> Tambah Customer Baru" autocomplete="off">
        <x-slot name="slot">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="tambahTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tambah-umum-tab" data-bs-toggle="tab" data-bs-target="#tambah-umum" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle me-1.5"></i> Info Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tambah-alamat-tab" data-bs-toggle="tab" data-bs-target="#tambah-alamat" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Pengiriman
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tambah-kontak-tab" data-bs-toggle="tab" data-bs-target="#tambah-kontak" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-person-lines-fill me-1.5"></i> Kontak Personil
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="tambahTabsContent">
                <!-- Tab 1: Info Umum -->
                <div class="tab-pane fade show active" id="tambah-umum" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Customer <span class="text-danger">*</span></label>
                            <input type="text" name="nama_customer" id="tambah_nama_customer"
                                class="form-control form-control-custom"
                                placeholder="Nama lengkap customer" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Customer <span class="text-danger">*</span></label>
                            <select name="jenis_customer" id="tambah_jenis_customer" class="form-select form-control-custom" required>
                                <option value="Retail">Retail</option>
                                <option value="End User">End User</option>
                                <option value="Distributor">Distributor</option>
                                <option value="Agen">Agen</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Perusahaan / Toko</label>
                            <input type="text" name="nama_perusahaan" id="tambah_nama_perusahaan"
                                class="form-control form-control-custom"
                                placeholder="Contoh: PT. Maju Bersama">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Handphone / WA</label>
                            <input type="text" name="hp" id="tambah_hp"
                                class="form-control form-control-custom"
                                placeholder="08XXXXXXXXX">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="tambah_email"
                                class="form-control form-control-custom"
                                placeholder="email@domain.com">
                        </div>
                        
                        <!-- Collapsible Advanced Options -->
                        <div class="col-12">
                            <div class="card card-advanced-options shadow-none mt-2">
                                <div class="card-header py-2.5 px-3 bg-light border-0 d-flex justify-content-between align-items-center header-advanced-toggle" 
                                     data-bs-toggle="collapse" data-bs-target="#tambah_opsi_lanjutan" aria-expanded="false">
                                    <span class="fw-semibold text-secondary small"><i class="bi bi-sliders me-1.5 text-primary"></i> Opsi Lanjutan (Kode, Finansial, CP, dll)</span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                                <div id="tambah_opsi_lanjutan" class="collapse">
                                    <div class="card-body p-3 border-top bg-white">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Kode Customer</label>
                                                <input type="text" name="kode_customer" id="tambah_kode_customer"
                                                    class="form-control form-control-custom"
                                                    placeholder="Otomatis jika kosong">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Contact Person Utama</label>
                                                <input type="text" name="contact_person" id="tambah_contact_person"
                                                    class="form-control form-control-custom"
                                                    placeholder="Nama CP Utama">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Jabatan CP</label>
                                                <input type="text" name="jabatan" id="tambah_jabatan"
                                                    class="form-control form-control-custom"
                                                    placeholder="Contoh: Manager Purchasing">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">No. Telepon Kantor</label>
                                                <input type="text" name="telepon" id="tambah_telepon"
                                                    class="form-control form-control-custom"
                                                    placeholder="021-XXXXXXX">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Website</label>
                                                <input type="text" name="website" id="tambah_website"
                                                    class="form-control form-control-custom"
                                                    placeholder="www.domain.com">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Mata Uang (Currency)</label>
                                                <select name="currency" id="tambah_currency" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Currency --</option>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }} ({{ $curr->simbol }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Nomor NPWP</label>
                                                <input type="text" name="npwp" id="tambah_npwp"
                                                    class="form-control form-control-custom"
                                                    placeholder="00.000.000.0-000.000">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status PKP</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input class="form-check-input" type="checkbox" name="pkp" id="tambah_pkp" value="1">
                                                    <label class="form-check-label" for="tambah_pkp">Wajib Pajak</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status Keaktifan</label>
                                                <select name="status" id="tambah_status" class="form-select form-control-custom" required>
                                                    <option value="Active">Aktif</option>
                                                    <option value="Inactive">Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Term Pembayaran (Days)</label>
                                                <select name="payment_term_id" id="tambah_payment_term_id" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Term --</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->nama_term }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Batas Kredit (Credit Limit)</label>
                                                <input type="number" name="credit_limit" id="tambah_credit_limit"
                                                    class="form-control form-control-custom"
                                                    placeholder="Batas limit utang piutang" min="0">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Sales Representative</label>
                                                <select name="sales_id" id="tambah_sales_id" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Sales --</option>
                                                    @foreach($salesList as $sales)
                                                        <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Keterangan Tambahan</label>
                                                <textarea name="keterangan" id="tambah_keterangan" rows="2" class="form-control form-control-custom" placeholder="Catatan internal..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Alamat Pengiriman -->
                <div class="tab-pane fade" id="tambah-alamat" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Input minimal satu alamat pengiriman. Centang "Alamat Utama" untuk menetapkan alamat default.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_tambah_alamat_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_tambah_alamat">
                        <!-- Dinamis via JS (Address Cards) -->
                    </div>
                </div>

                <!-- Tab 3: Kontak Personil -->
                <div class="tab-pane fade" id="tambah-kontak" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar kontak penanggung jawab sekunder dari customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_tambah_kontak_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Personil
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_tambah_kontak">
                        <!-- Dinamis via JS (Contact Cards) -->
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    {{-- ===== MODAL EDIT ===== --}}
    <x-modal_form id="modalEditCustomer" formId="formEditCustomer" title="<i class='bi bi-pencil-square me-2'></i> Edit Data Customer" autocomplete="off">
        <x-slot name="slot">
            <input type="hidden" name="id" id="edit_id">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="editTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="edit-umum-tab" data-bs-toggle="tab" data-bs-target="#edit-umum" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle me-1.5"></i> Info Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="edit-alamat-tab" data-bs-toggle="tab" data-bs-target="#edit-alamat" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Pengiriman
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="edit-kontak-tab" data-bs-toggle="tab" data-bs-target="#edit-kontak" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-person-lines-fill me-1.5"></i> Kontak Personil
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="editTabsContent">
                <!-- Tab 1: Info Umum -->
                <div class="tab-pane fade show active" id="edit-umum" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Customer <span class="text-danger">*</span></label>
                            <input type="text" name="nama_customer" id="edit_nama_customer"
                                class="form-control form-control-custom" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Customer <span class="text-danger">*</span></label>
                            <select name="jenis_customer" id="edit_jenis_customer" class="form-select form-control-custom" required>
                                <option value="Retail">Retail</option>
                                <option value="End User">End User</option>
                                <option value="Distributor">Distributor</option>
                                <option value="Agen">Agen</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Perusahaan / Toko</label>
                            <input type="text" name="nama_perusahaan" id="edit_nama_perusahaan"
                                class="form-control form-control-custom">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Handphone / WA</label>
                            <input type="text" name="hp" id="edit_hp"
                                class="form-control form-control-custom">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email"
                                class="form-control form-control-custom">
                        </div>
                        
                        <!-- Collapsible Advanced Options -->
                        <div class="col-12">
                            <div class="card card-advanced-options shadow-none mt-2">
                                <div class="card-header py-2.5 px-3 bg-light border-0 d-flex justify-content-between align-items-center header-advanced-toggle" 
                                     data-bs-toggle="collapse" data-bs-target="#edit_opsi_lanjutan" aria-expanded="false">
                                    <span class="fw-semibold text-secondary small"><i class="bi bi-sliders me-1.5 text-primary"></i> Opsi Lanjutan (Kode, Finansial, CP, dll)</span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                                <div id="edit_opsi_lanjutan" class="collapse">
                                    <div class="card-body p-3 border-top bg-white">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Kode Customer</label>
                                                <input type="text" name="kode_customer" id="edit_kode_customer"
                                                    class="form-control form-control-custom" required>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Contact Person Utama</label>
                                                <input type="text" name="contact_person" id="edit_contact_person"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Jabatan CP</label>
                                                <input type="text" name="jabatan" id="edit_jabatan"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">No. Telepon Kantor</label>
                                                <input type="text" name="telepon" id="edit_telepon"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Website</label>
                                                <input type="text" name="website" id="edit_website"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Mata Uang (Currency)</label>
                                                <select name="currency" id="edit_currency" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Currency --</option>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Nomor NPWP</label>
                                                <input type="text" name="npwp" id="edit_npwp"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status PKP</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input class="form-check-input" type="checkbox" name="pkp" id="edit_pkp" value="1">
                                                    <label class="form-check-label" for="edit_pkp">Wajib Pajak</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status Keaktifan</label>
                                                <select name="status" id="edit_status" class="form-select form-control-custom" required>
                                                    <option value="Active">Aktif</option>
                                                    <option value="Inactive">Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Term Pembayaran (Days)</label>
                                                <select name="payment_term_id" id="edit_payment_term_id" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Term --</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->nama_term }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Batas Kredit (Credit Limit)</label>
                                                <input type="number" name="credit_limit" id="edit_credit_limit"
                                                    class="form-control form-control-custom" min="0">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Sales Representative</label>
                                                <select name="sales_id" id="edit_sales_id" class="form-select form-control-custom">
                                                    <option value="">-- Pilih Sales --</option>
                                                    @foreach($salesList as $sales)
                                                        <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Keterangan Tambahan</label>
                                                <textarea name="keterangan" id="edit_keterangan" rows="2" class="form-control form-control-custom"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Alamat Pengiriman -->
                <div class="tab-pane fade" id="edit-alamat" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar alamat pengiriman yang terdaftar untuk customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_edit_alamat_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_edit_alamat">
                        <!-- Dinamis via JS (Address Cards) -->
                    </div>
                </div>

                <!-- Tab 3: Kontak Personil -->
                <div class="tab-pane fade" id="edit-kontak" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar kontak penanggung jawab sekunder dari customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_edit_kontak_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Personil
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_edit_kontak">
                        <!-- Dinamis via JS (Contact Cards) -->
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    {{-- ===== MODAL DETAIL SHOW ===== --}}
    <div class="modal fade" id="modalDetailCustomer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i> Profil & Spesifikasi Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div class="row g-3">
                        <!-- Summary Section -->
                        <div class="col-12">
                            <div class="card-detail-section d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fs-4 fw-bold text-dark" id="detail_nama_customer">-</div>
                                    <div class="text-muted" id="detail_nama_perusahaan">-</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill font-monospace" id="detail_kode_customer">-</span>
                                    <div class="mt-1" id="detail_status_badge"></div>
                                </div>
                            </div>
                        </div>

                        <!-- General Info Column 1 -->
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Jenis Customer</div>
                            <div class="customer-detail-val" id="detail_jenis_customer">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Mata Uang</div>
                            <div class="customer-detail-val" id="detail_currency">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Batas Kredit</div>
                            <div class="customer-detail-val text-primary" id="detail_credit_limit">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Term Pembayaran</div>
                            <div class="customer-detail-val" id="detail_payment_term">-</div>
                        </div>

                        <!-- General Info Column 2 -->
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">CP Utama</div>
                            <div class="customer-detail-val" id="detail_contact_person">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Jabatan CP</div>
                            <div class="customer-detail-val" id="detail_jabatan">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Sales Rep</div>
                            <div class="customer-detail-val" id="detail_sales">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Status PKP</div>
                            <div class="customer-detail-val" id="detail_pkp">-</div>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <!-- Contacts Column -->
                        <div class="col-6 col-md-4">
                            <div class="customer-detail-label">Telepon Kantor</div>
                            <div class="customer-detail-val font-monospace" id="detail_telepon">-</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="customer-detail-label">Handphone/WA</div>
                            <div class="customer-detail-val font-monospace" id="detail_hp">-</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="customer-detail-label">Email</div>
                            <div class="customer-detail-val" id="detail_email">-</div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="customer-detail-label">NPWP</div>
                            <div class="customer-detail-val font-monospace" id="detail_npwp">-</div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="customer-detail-label">Website</div>
                            <div class="customer-detail-val" id="detail_website">-</div>
                        </div>

                        <!-- Tabs Detail sub relations -->
                        <div class="col-12">
                            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="detailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="detail-alamat-tab" data-bs-toggle="tab" data-bs-target="#detail-alamat" type="button" role="tab">
                                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Terdaftar
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="detail-kontak-tab" data-bs-toggle="tab" data-bs-target="#detail-kontak" type="button" role="tab">
                                        <i class="bi bi-person-lines-fill me-1.5"></i> Personil Kontak
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="detailTabsContent">
                                <!-- Tab 1: Alamat -->
                                <div class="tab-pane fade show active" id="detail-alamat" role="tabpanel">
                                    <div id="detail_alamat_list" class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                        <!-- render via JS -->
                                    </div>
                                </div>
                                <!-- Tab 2: Kontak -->
                                <div class="tab-pane fade" id="detail-kontak" role="tabpanel">
                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm table-striped mb-0 align-middle small">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jabatan</th>
                                                    <th>No. HP</th>
                                                    <th>Email</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detail_kontak_table_body">
                                                <!-- render via JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Internal Note -->
                        <div class="col-12">
                            <div class="customer-detail-label">Catatan Keterangan</div>
                            <div class="p-3 bg-light rounded-3 mt-1 small" id="detail_keterangan" style="white-space: pre-wrap;">-</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Helper to format currency
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        // Security helper to escape HTML entities (XSS protection)
        function escapeHtml(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $(document).ready(function () {
            // ===== INITIALIZE DATATABLE IN SERVER-SIDE MODE =====
            const table = $('#tableCustomer').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("customer.data") }}',
                    type: 'GET'
                },
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>Belum ada data customer.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columns: [
                    { data: null, orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_customer' },
                    { data: 'nama_customer' },
                    { data: 'jenis_customer' },
                    { data: 'telepon' },
                    { data: 'location' },
                    { data: 'status', className: 'text-center' },
                    { data: 'id', orderable: false, searchable: false, className: 'text-center' }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 1,
                        render: function (data, type, row) {
                            return `<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill font-monospace">${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        targets: 2,
                        render: function (data, type, row) {
                            let comp = row.nama_perusahaan !== '-' ? `<div class="small text-muted" style="font-size:0.75rem;"><i class="bi bi-building me-1"></i>${escapeHtml(row.nama_perusahaan)}</div>` : '';
                            return `<div class="fw-bold text-dark">${escapeHtml(data)}</div>${comp}`;
                        }
                    },
                    {
                        targets: 3,
                        render: function (data, type, row) {
                            let typeBadge = '';
                            if (data === 'Distributor') typeBadge = 'bg-primary-soft text-primary';
                            else if (data === 'Agen') typeBadge = 'bg-info-soft text-info';
                            else if (data === 'Retail') typeBadge = 'bg-warning-soft text-warning';
                            else typeBadge = 'bg-secondary-soft text-secondary';
                            
                            return `
                                <span class="badge ${typeBadge} px-2.5 py-1 rounded small mb-1" style="font-size:0.65rem;">${data}</span>
                                <div class="small text-muted" style="font-size:0.7rem;"><i class="bi bi-person me-1"></i>${escapeHtml(row.contact_person)}</div>
                            `;
                        }
                    },
                    {
                        targets: 4,
                        render: function (data, type, row) {
                            let emailInfo = row.email !== '-' ? `<div class="small text-muted" style="font-size:0.7rem;"><i class="bi bi-envelope me-1"></i>${escapeHtml(row.email)}</div>` : '';
                            return `<div class="fw-medium text-secondary" style="font-size:0.85rem;">${escapeHtml(data)}</div>${emailInfo}`;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            return `<span class="text-secondary small"><i class="bi bi-geo-alt me-1 text-danger"></i>${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        targets: 6,
                        render: function (data, type, row) {
                            if (data === 'Active') {
                                return `<span class="badge bg-success-soft px-3 py-1.5 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>`;
                            }
                            return `<span class="badge bg-secondary-soft px-3 py-1.5 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>`;
                        }
                    },
                    {
                        targets: 7,
                        render: function (data, type, row) {
                            let detailBtn = `<button class="btn btn-sm btn-light text-primary border btn-detail" data-id="${data}" title="Lihat Detail"><i class="bi bi-eye"></i></button>`;
                            let editBtn = '';
                            let deleteBtn = '';
                            
                            @can('edit-customer')
                            editBtn = `<button class="btn btn-sm btn-light text-info border btn-edit" data-id="${data}" title="Edit"><i class="bi bi-pencil"></i></button>`;
                            @endcan
                            
                            @can('hapus-customer')
                            deleteBtn = `<button class="btn btn-sm btn-light text-danger border btn-delete" data-id="${data}" data-name="${escapeHtml(row.nama_customer)}" title="Hapus"><i class="bi bi-trash"></i></button>`;
                            @endcan
                            
                            return `<div class="d-flex justify-content-center gap-1">${detailBtn} ${editBtn} ${deleteBtn}</div>`;
                        }
                    }
                ]
            });

            // ===== DYNAMIC SUB CARDS HELPERS =====
            let addrIndex = 0;
            let contactIndex = 0;

            function addAddressCard(prefix, addressData = null) {
                let uniqueId = addrIndex++;
                let container = $(`#container_${prefix}_alamat`);

                let jenis = addressData ? addressData.jenis_alamat : 'Kantor';
                let penerima = addressData ? addressData.nama_penerima : '';
                let telp = addressData ? addressData.telepon : '';
                let alamat = addressData ? addressData.alamat : '';
                let kota = addressData ? addressData.kota : '';
                let prov = addressData ? addressData.provinsi : '';
                let pos = addressData ? addressData.kode_pos : '';
                let defaultChecked = (addressData && (addressData.is_default == 1 || addressData.is_default == true)) ? 'checked' : '';
                if (!addressData && container.children().length === 0) {
                    defaultChecked = 'checked'; // Default checked for first row
                }

                // Determine badge class
                let badgeClass = 'bg-secondary';
                if (jenis === 'Gudang') badgeClass = 'bg-dark';
                else if (jenis === 'Toko') badgeClass = 'bg-primary';
                else if (jenis === 'Rumah') badgeClass = 'bg-success';
                else if (jenis === 'Lainnya') badgeClass = 'bg-info';

                let html = `
                    <div class="card address-card border shadow-sm mb-3 position-relative">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge ${badgeClass} text-uppercase fw-semibold" style="font-size: 0.65rem;" id="badge_jenis_${prefix}_${uniqueId}">${jenis}</span>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input address-default-radio" type="radio" 
                                        name="${prefix}_address_default" value="${uniqueId}" ${defaultChecked}>
                                    <input type="hidden" name="addresses[${uniqueId}][is_default]" class="address-default-val" value="${defaultChecked ? '1' : '0'}">
                                    <label class="form-check-label small fw-semibold text-primary" style="font-size: 0.75rem;">Alamat Utama</label>
                                </div>
                            </div>
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove-address-card" title="Hapus Alamat" style="text-decoration: none;">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Jenis Alamat</label>
                                    <select name="addresses[${uniqueId}][jenis_alamat]" class="form-select form-select-sm select-jenis-alamat" data-target="badge_jenis_${prefix}_${uniqueId}" required>
                                        <option value="Kantor" ${jenis === 'Kantor' ? 'selected' : ''}>Kantor</option>
                                        <option value="Gudang" ${jenis === 'Gudang' ? 'selected' : ''}>Gudang</option>
                                        <option value="Toko" ${jenis === 'Toko' ? 'selected' : ''}>Toko</option>
                                        <option value="Rumah" ${jenis === 'Rumah' ? 'selected' : ''}>Rumah</option>
                                        <option value="Lainnya" ${jenis === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Nama Penerima <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][nama_penerima]" 
                                        class="form-control form-control-sm" 
                                        placeholder="Nama Penerima" value="${escapeHtml(penerima)}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Telepon Penerima</label>
                                    <input type="text" name="addresses[${uniqueId}][telepon]" 
                                        class="form-control form-control-sm" 
                                        placeholder="No. Telp / HP" value="${escapeHtml(telp)}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea name="addresses[${uniqueId}][alamat]" class="form-control form-control-sm" 
                                        rows="2" placeholder="Nama Jalan, RT/RW, Blok, No. Rumah, Kecamatan..." required>${escapeHtml(alamat)}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Kota / Kabupaten <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][kota]" 
                                        class="form-control form-control-sm" placeholder="Kota" value="${escapeHtml(kota)}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Provinsi <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][provinsi]" 
                                        class="form-control form-control-sm" placeholder="Provinsi" value="${escapeHtml(prov)}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Kode Pos</label>
                                    <input type="text" name="addresses[${uniqueId}][kode_pos]" 
                                        class="form-control form-control-sm" placeholder="Kode Pos" value="${escapeHtml(pos)}">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(html);
            }

            function addContactCard(prefix, contactData = null) {
                let uniqueId = contactIndex++;
                let container = $(`#container_${prefix}_kontak`);

                let nama = contactData ? contactData.nama : '';
                let jabatan = contactData ? contactData.jabatan : '';
                let hp = contactData ? contactData.hp : '';
                let email = contactData ? contactData.email : '';

                let html = `
                    <div class="card contact-card border shadow-sm mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="fw-bold text-dark small"><i class="bi bi-person-fill me-1 text-primary"></i> Personil Kontak</span>
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove-contact-card" title="Hapus Kontak" style="text-decoration: none;">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="contacts[${uniqueId}][nama]" 
                                        class="form-control form-control-sm" 
                                        placeholder="Nama Lengkap" value="${escapeHtml(nama)}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Jabatan</label>
                                    <input type="text" name="contacts[${uniqueId}][jabatan]" 
                                        class="form-control form-control-sm" 
                                        placeholder="Contoh: Manager Finance" value="${escapeHtml(jabatan)}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                    <input type="text" name="contacts[${uniqueId}][hp]" 
                                        class="form-control form-control-sm" 
                                        placeholder="08XXXXXXXXXX" value="${escapeHtml(hp)}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Email</label>
                                    <input type="email" name="contacts[${uniqueId}][email]" 
                                        class="form-control form-control-sm" 
                                        placeholder="email@domain.com" value="${escapeHtml(email)}">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(html);
            }

            // Sync dynamic default radios to hidden values
            $(document).on('change', '.address-default-radio', function() {
                let container = $(this).closest('.pe-1');
                container.find('.address-default-val').val('0');
                $(this).closest('.address-card').find('.address-default-val').val('1');
            });

            // Handle live badge text/color updates when Jenis Alamat select changes
            $(document).on('change', '.select-jenis-alamat', function() {
                let targetId = $(this).data('target');
                let val = $(this).val();
                let badge = $(`#${targetId}`);
                badge.text(val);
                
                badge.removeClass('bg-primary bg-secondary bg-success bg-info bg-warning bg-dark');
                if (val === 'Kantor') badge.addClass('bg-secondary');
                else if (val === 'Gudang') badge.addClass('bg-dark');
                else if (val === 'Toko') badge.addClass('bg-primary');
                else if (val === 'Rumah') badge.addClass('bg-success');
                else badge.addClass('bg-info');
            });

            // Remove card handlers
            $(document).on('click', '.btn-remove-address-card', function() {
                let card = $(this).closest('.address-card');
                let wasDefault = card.find('.address-default-radio').is(':checked');
                let container = card.closest('.pe-1');
                
                card.remove();
                
                // Reset default option to the first remaining card if default was deleted
                if (wasDefault && container.children().length > 0) {
                    container.find('.address-default-radio').first().prop('checked', true).trigger('change');
                }
            });

            $(document).on('click', '.btn-remove-contact-card', function() {
                $(this).closest('.contact-card').remove();
            });

            // Bind click buttons
            $('#btn_tambah_alamat_row').on('click', () => addAddressCard('tambah'));
            $('#btn_edit_alamat_row').on('click', () => addAddressCard('edit'));
            $('#btn_tambah_kontak_row').on('click', () => addContactCard('tambah'));
            $('#btn_edit_kontak_row').on('click', () => addContactCard('edit'));

            // ===== RESET FORMS ON OPEN =====
            $('#modalTambahCustomer').on('show.bs.modal', function () {
                $('#formTambahCustomer')[0].reset();
                $('#container_tambah_alamat').empty();
                $('#container_tambah_kontak').empty();
                $('#tambahTabs button:first').tab('show');
                
                // Collapse advanced options by default
                let collapseEl = $('#tambah_opsi_lanjutan');
                if (collapseEl.hasClass('show')) {
                    collapseEl.collapse('hide');
                }
                
                // Seed 1 blank address and contact automatically as placeholders
                addAddressCard('tambah');
                addContactCard('tambah');
            });

            // ===== SUBMIT CREATION =====
            $('#formTambahCustomer').on('submit', function (e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('customer.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        $('#modalTambahCustomer').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            table.ajax.reload(null, false);
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        } else if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                    }
                });
            });

            // ===== SHOW DETAILS MODAL =====
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                $.get('/customer/' + id + '/edit', function (res) {
                    let c = res.customer;
                    
                    $('#detail_nama_customer').text(c.nama_customer);
                    $('#detail_nama_perusahaan').text(c.nama_perusahaan ? c.nama_perusahaan : '— Tanpa Nama Perusahaan');
                    $('#detail_kode_customer').text(c.kode_customer);
                    
                    let activeHtml = c.status === 'Active' 
                        ? '<span class="badge bg-success-soft px-3 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>'
                        : '<span class="badge bg-secondary-soft px-3 py-1.5 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>';
                    $('#detail_status_badge').html(activeHtml);
                    
                    $('#detail_jenis_customer').text(c.jenis_customer);
                    $('#detail_currency').text(c.currency ? c.currency : '-');
                    $('#detail_credit_limit').text(c.credit_limit ? formatRupiah(c.credit_limit) : 'Rp 0');
                    $('#detail_payment_term').text(c.payment_term ? c.payment_term.nama_term : 'COD (0 Hari)');
                    
                    $('#detail_contact_person').text(c.contact_person ? c.contact_person : '-');
                    $('#detail_jabatan').text(c.jabatan ? c.jabatan : '-');
                    $('#detail_sales').text(c.sales ? c.sales.nama_lengkap : '-');
                    $('#detail_pkp').text(c.pkp ? 'Wajib Pajak (PKP)' : 'Bukan PKP');
                    
                    $('#detail_telepon').text(c.telepon ? c.telepon : '-');
                    $('#detail_hp').text(c.hp ? c.hp : '-');
                    $('#detail_email').text(c.email ? c.email : '-');
                    $('#detail_npwp').text(c.npwp ? c.npwp : '-');
                    $('#detail_website').text(c.website ? c.website : '-');
                    $('#detail_keterangan').text(c.keterangan ? c.keterangan : '-');

                    // Addresses rendering
                    let addrHtml = '';
                    if (c.addresses && c.addresses.length > 0) {
                        c.addresses.forEach(a => {
                            let badgeDefault = a.is_default ? '<span class="badge bg-primary px-2.5 py-1" style="font-size:0.6rem;">UTAMA</span>' : '';
                            addrHtml += `
                                <div class="border rounded p-3 bg-white" style="position:relative;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-soft text-secondary px-2.5 py-1 text-uppercase" style="font-size:0.65rem; font-weight:600;">${a.jenis_alamat}</span>
                                        ${badgeDefault}
                                    </div>
                                    <div class="fw-bold text-dark mb-1" style="font-size:0.85rem;">Penerima: ${escapeHtml(a.nama_penerima)} ${a.telepon ? '('+escapeHtml(a.telepon)+')' : ''}</div>
                                    <div class="small text-muted mb-1">${escapeHtml(a.alamat)}</div>
                                    <div class="small text-dark font-monospace fw-semibold" style="font-size:0.75rem;">${escapeHtml(a.kota)}, ${escapeHtml(a.provinsi)} ${a.kode_pos ? '- '+escapeHtml(a.kode_pos) : ''} (${escapeHtml(a.negara)})</div>
                                </div>
                            `;
                        });
                    } else {
                        addrHtml = '<div class="text-center text-muted p-4 small">Belum ada alamat pengiriman terdaftar.</div>';
                    }
                    $('#detail_alamat_list').html(addrHtml);

                    // Contacts rendering
                    let contactsHtml = '';
                    if (c.contacts && c.contacts.length > 0) {
                        c.contacts.forEach(con => {
                            contactsHtml += `
                                <tr>
                                    <td class="fw-bold text-dark">${escapeHtml(con.nama)}</td>
                                    <td>${escapeHtml(con.jabatan ?? '-')}</td>
                                    <td class="font-monospace">${escapeHtml(con.hp)}</td>
                                    <td>${escapeHtml(con.email ?? '-')}</td>
                                </tr>
                            `;
                        });
                    } else {
                        contactsHtml = '<tr><td colspan="4" class="text-center text-muted py-3 small">Tidak ada personil kontak terdaftar.</td></tr>';
                    }
                    $('#detail_kontak_table_body').html(contactsHtml);

                    $('#detailTabs button:first').tab('show');
                    $('#modalDetailCustomer').modal('show');
                });
            });

            // ===== TRIGGER EDIT MODAL =====
            $(document).on('click', '.btn-edit', function () {
                let id = $(this).data('id');
                $.get('/customer/' + id + '/edit', function (res) {
                    let c = res.customer;
                    
                    $('#edit_id').val(c.id);
                    $('#edit_kode_customer').val(c.kode_customer);
                    $('#edit_nama_customer').val(c.nama_customer);
                    $('#edit_nama_perusahaan').val(c.nama_perusahaan);
                    $('#edit_jenis_customer').val(c.jenis_customer);
                    $('#edit_contact_person').val(c.contact_person);
                    $('#edit_jabatan').val(c.jabatan);
                    $('#edit_telepon').val(c.telepon);
                    $('#edit_hp').val(c.hp);
                    $('#edit_email').val(c.email);
                    $('#edit_website').val(c.website);
                    $('#edit_currency').val(c.currency ? c.currency : '');
                    $('#edit_npwp').val(c.npwp);
                    $('#edit_pkp').prop('checked', c.pkp == 1);
                    $('#edit_status').val(c.status);
                    $('#edit_payment_term_id').val(c.payment_term_id ? c.payment_term_id : '');
                    $('#edit_credit_limit').val(c.credit_limit ? parseFloat(c.credit_limit) : '');
                    $('#edit_sales_id').val(c.sales_id ? c.sales_id : '');
                    $('#edit_keterangan').val(c.keterangan);

                    // Collapse advanced options by default in Edit
                    let collapseEl = $('#edit_opsi_lanjutan');
                    if (collapseEl.hasClass('show')) {
                        collapseEl.collapse('hide');
                    }

                    // Populate Addresses
                    $('#container_edit_alamat').empty();
                    if (c.addresses && c.addresses.length > 0) {
                        c.addresses.forEach(addr => addAddressCard('edit', addr));
                    } else {
                        addAddressCard('edit');
                    }

                    // Populate Contacts
                    $('#container_edit_kontak').empty();
                    if (c.contacts && c.contacts.length > 0) {
                        c.contacts.forEach(contact => addContactCard('edit', contact));
                    } else {
                        addContactCard('edit');
                    }

                    $('#editTabs button:first').tab('show');
                    $('#modalEditCustomer').modal('show');
                });
            });

            // ===== SUBMIT UPDATE =====
            $('#formEditCustomer').on('submit', function (e) {
                e.preventDefault();
                let id = $('#edit_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: "/customer/" + id + "/update",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        $('#modalEditCustomer').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            table.ajax.reload(null, false);
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Terjadi kesalahan saat memperbarui data.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        } else if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                    }
                });
            });

            // ===== TRIGGER DELETION =====
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Menghapus customer " + name + " juga akan menonaktifkan alamat dan kontak terkait!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/customer/" + id + "/delete",
                            type: "DELETE",
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data customer.';
                                if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg = err.responseJSON.message;
                                }
                                Swal.fire({ title: 'Gagal!', text: errorMsg, icon: 'error' });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts>
