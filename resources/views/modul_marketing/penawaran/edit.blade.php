<x-layouts title="Edit Penawaran Harga {{ $quotation->nomor_quotation }}">

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_smd/penawaran.css') }}">
    @endpush

    <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2.5">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-bold mb-0 text-body page-header-title">Edit Penawaran <span class="font-monospace text-primary">{{ $quotation->nomor_quotation }}</span></h3>
                    <span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill fw-semibold">REV.{{ $quotation->revisi }}</span>
                </div>
                <p class="text-muted fs-8 mb-0 mt-0.5">Perbarui rincian harga penawaran, item barang, diskon, dan ketentuan pembayaran.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
                <a href="{{ route('penawaran.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 shadow-xs">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <form id="formQuotation" action="{{ route('penawaran.update', $quotation->id) }}" method="POST" autocomplete="off">
            @csrf
            <input type="hidden" name="status" id="inputQuotationStatus" value="{{ $quotation->status ?? 'Sent' }}">

            <!-- 1. Identitas Dokumen & Klien (Full Width) -->
            <div class="sq-form-section mb-3 mb-md-4">
                        <div class="sq-section-title">
                            <i class="bi bi-building me-1"></i> 1. Identitas Klien & Dokumen Penawaran
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Nomor Penawaran</label>
                                <input type="text" class="form-control form-control-sm font-monospace fw-bold" value="{{ $quotation->nomor_quotation }}" readonly>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fs-8">Tanggal Penawaran <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_quotation" class="form-control form-control-sm" value="{{ $quotation->tanggal_quotation ? $quotation->tanggal_quotation->format('Y-m-d') : date('Y-m-d') }}" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fs-8">Masa Berlaku Hingga</label>
                                <input type="date" name="berlaku_hingga" class="form-control form-control-sm" value="{{ $quotation->berlaku_hingga ? $quotation->berlaku_hingga->format('Y-m-d') : '' }}">
                            </div>

                            @php
                                $curCust = $customers->firstWhere('id', $quotation->customer_id);
                                $curCustText = $curCust ? ($curCust->nama_perusahaan ?: $curCust->nama_customer) : '';
                                $curTenderText = $quotation->tender ? ($quotation->tender->nomor_kontrak . ' - ' . $quotation->tender->nama_tender) : '';
                            @endphp
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Pilih Customer <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="customer_id" id="selectCustomer" value="{{ $quotation->customer_id }}" required>
                                    <input type="text" id="displayCustomer" class="form-control form-control-sm rounded-start-3 bg-body" placeholder="Pilih Customer / Instansi..." readonly required value="{{ $curCustText }}">
                                    <button type="button" class="btn btn-primary px-3 rounded-end-3" id="btnOpenLookupCustomer" title="Cari Customer">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Terkait Tender / Proyek (Opsional)</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="tender_id" id="selectTender" value="{{ $quotation->tender_id }}" data-pagu="{{ $quotation->tender ? (float)$quotation->tender->nilai_estimasi : 0 }}">
                                    <input type="text" id="displayTender" class="form-control form-control-sm rounded-start-3 bg-body" placeholder="-- Penawaran Langsung (Non-Tender) --" readonly value="{{ $curTenderText }}">
                                    <button type="button" class="btn btn-outline-danger px-2.5 {{ $quotation->tender_id ? '' : 'd-none' }}" id="btnClearTender" title="Hapus Kaitan Tender">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary px-3 rounded-end-3" id="btnOpenLookupTender" title="Cari Tender">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="tenderPaguInfoBox" class="mt-1.5 p-2 rounded-3 bg-body-tertiary border {{ $quotation->tender_id ? '' : 'd-none' }}">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 fs-8">
                                        <div>
                                            <span class="text-muted"><i class="bi bi-wallet2 text-success me-1"></i>Nilai Proyek:</span>
                                            <strong class="font-monospace text-body ms-1" id="displayTenderPagu">Rp {{ $quotation->tender ? number_format($quotation->tender->nilai_estimasi, 0, ',', '.') : 0 }}</strong>
                                        </div>
                                        <div id="badgeBudgetStatus">
                                            <!-- Dynamically populated via JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fs-8 mb-0">Perihal / Judul Penawaran <span class="text-danger">*</span></label>
                                    <small id="counter_perihal" class="text-muted fs-9 font-monospace">0/40</small>
                                </div>
                                <input type="text" name="perihal" id="input_perihal" maxlength="40" class="form-control form-control-sm" value="{{ $quotation->perihal }}" required autocomplete="off">
                                <div id="warn_perihal" class="text-danger fs-9 mt-1" style="display:none;"><i class="bi bi-exclamation-circle me-1"></i>Judul penawaran maksimal 40 karakter!</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fs-8">Sales / PIC</label>
                                <select name="sales_id" id="selectSales" class="form-select form-select-sm select2" data-placeholder="-- Pilih Sales PIC --">
                                    <option value="">-- Pilih Sales PIC --</option>
                                    @foreach($salesList as $s)
                                        <option value="{{ $s->id }}" {{ $quotation->sales_id == $s->id ? 'selected' : '' }}>{{ $s->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Rincian Item Penawaran (Full Width) -->
                    <div class="sq-form-section mb-3 mb-md-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                            <div class="sq-section-title mb-0">
                                <i class="bi bi-cart-check me-1"></i> 2. Rincian Item Barang & Harga
                            </div>
                            <div class="btn-add-item-group d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddMasterItem">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Master
                                </button>
                            </div>
                        </div>

                        <div class="d-md-none mb-2.5">
                            <span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 fs-9">
                                <i class="bi bi-arrows-left-right text-primary"></i>
                                <span>Geser tabel ke samping untuk kolom lengkap</span>
                            </span>
                        </div>

                        <div class="table-items-wrapper mb-3">
                            <table class="table align-middle table-items w-100 mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th class="ps-3"><i class="bi bi-box-seam me-1 text-muted"></i> Item Barang & Spesifikasi</th>
                                        <th class="text-center" style="min-width: 140px; width: 145px;"><i class="bi bi-layers me-1 text-muted"></i> Qty & Satuan</th>
                                        <th style="min-width: 185px; width: 195px;"><i class="bi bi-tag me-1 text-muted"></i> Harga Satuan (Rp)</th>
                                        <th style="min-width: 165px; width: 175px;"><i class="bi bi-percent me-1 text-muted"></i> Diskon</th>
                                        <th class="text-end" style="min-width: 140px; width: 150px;"><i class="bi bi-cash-stack me-1 text-muted"></i> Subtotal (Rp)</th>
                                        <th style="min-width: 175px; width: 185px;"><i class="bi bi-receipt me-1 text-muted"></i> PPN</th>
                                        <th class="text-center pe-3" style="min-width: 48px; width: 50px;"><i class="bi bi-gear text-muted"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    @foreach($quotation->items as $idx => $it)
                                    @php
                                        $pPath = $it->barang?->primaryFoto ? $it->barang->primaryFoto->foto_path : null;
                                        $pUrl = $pPath ? (str_starts_with($pPath, 'http') ? $pPath : (str_starts_with($pPath, 'storage/') ? asset($pPath) : asset('storage/' . ltrim($pPath, '/')))) : asset('uploads/barang/placeholder_garment.png');
                                        $hRetail = (float)($it->barang?->harga_jual_retail ?: 0);
                                        $hGrosir = (float)($it->barang?->harga_jual_grosir ?: 0);
                                        $hTender = (float)($it->barang?->harga_jual_tender ?: 0);
                                        $currentPrice = (float)$it->harga_satuan;
                                        $tierSelected = 'custom';
                                        if ($currentPrice == $hTender && $hTender > 0) {
                                            $tierSelected = 'tender';
                                        } elseif ($currentPrice == $hGrosir && $hGrosir > 0) {
                                            $tierSelected = 'grosir';
                                        } elseif ($currentPrice == $hRetail && $hRetail > 0) {
                                            $tierSelected = 'satuan';
                                        }
                                        $itDiscPersen = (float)$it->diskon_persen;
                                        $itDiscNominal = (float)$it->diskon_nominal;
                                        $itDiscTipe = ($itDiscNominal > 0 && $itDiscPersen == 0) ? 'nominal' : 'persen';
                                        $itDiscVal = ($itDiscTipe === 'nominal') ? number_format($itDiscNominal, 0, ',', '.') : $itDiscPersen;
                                    @endphp
                                    <tr id="itemRow_{{ $idx + 1 }}" class="item-calc-row">
                                        <td>
                                            <div class="d-flex align-items-center gap-2.5">
                                                @if($it->barang_id)
                                                    <img src="{{ $pUrl }}" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                                    <div class="min-w-0">
                                                        <div class="fw-semibold text-body fs-8 text-truncate mb-0.5" title="{{ $it->barang?->nama_barang }}">{{ $it->barang?->nama_barang }}</div>
                                                        <span class="badge badge-soft-neutral fs-9 px-1.5 py-0.5 font-monospace">{{ $it->barang?->kode_barang }}</span>
                                                    </div>
                                                    <input type="hidden" name="items[{{ $idx + 1 }}][barang_id]" value="{{ $it->barang_id }}">
                                                @else
                                                    <input type="text" name="items[{{ $idx + 1 }}][nama_barang_custom]" class="form-control form-control-sm fw-bold" value="{{ $it->nama_barang_custom }}" placeholder="Nama item / jasa / pekerjaan..." required>
                                                @endif
                                            </div>
                                            <div class="mt-1.5">
                                                <input type="text" name="items[{{ $idx + 1 }}][deskripsi_item]" class="form-control form-control-xs item-desc-input" value="{{ $it->deskripsi_item }}" placeholder="+ Catatan spesifikasi / detail item...">
                                            </div>
                                            <input type="hidden" name="items[{{ $idx + 1 }}][kategori_id]" value="{{ $it->kategori_id }}">
                                            <input type="hidden" name="items[{{ $idx + 1 }}][satuan_id]" value="{{ $it->satuan_id }}">
                                            <input type="hidden" name="items[{{ $idx + 1 }}][hpp_satuan]" class="item-hpp-satuan" value="{{ $canSeeHpp ? $it->hpp_satuan : 0 }}">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" min="0.01" name="items[{{ $idx + 1 }}][qty]" class="form-control form-control-sm text-end fw-bold font-monospace item-qty" value="{{ (float)$it->qty }}" required>
                                                <span class="input-group-text fs-9 px-2 text-muted fw-semibold item-satuan-label bg-body-tertiary" title="{{ $it->nama_satuan ?: ($it->satuan?->nama_satuan ?? 'Pcs') }}">{{ $it->nama_satuan ?: ($it->satuan?->nama_satuan ?? 'Pcs') }}</span>
                                                <input type="hidden" name="items[{{ $idx + 1 }}][nama_satuan]" value="{{ $it->nama_satuan ?: ($it->satuan?->nama_satuan ?? 'Pcs') }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm mb-1">
                                                <span class="input-group-text fs-9 px-1.5 text-muted">Rp</span>
                                                <input type="text" inputmode="numeric" name="items[{{ $idx + 1 }}][harga]" class="form-control form-control-sm text-end item-price fw-bold font-monospace" value="{{ number_format($currentPrice, 0, ',', '.') }}" required>
                                            </div>
                                            @if($it->barang_id)
                                            <select class="form-select form-select-xs select-price-tier"
                                                data-harga-retail="{{ $hRetail }}"
                                                data-harga-grosir="{{ $hGrosir }}"
                                                data-harga-tender="{{ $hTender }}">
                                                <option value="tender" {{ $tierSelected === 'tender' ? 'selected' : '' }}>🏷️ Tender: Rp {{ number_format($hTender, 0, ',', '.') }}</option>
                                                <option value="grosir" {{ $tierSelected === 'grosir' ? 'selected' : '' }}>📦 Grosir: Rp {{ number_format($hGrosir, 0, ',', '.') }}</option>
                                                <option value="satuan" {{ $tierSelected === 'satuan' ? 'selected' : '' }}>🛍️ Satuan: Rp {{ number_format($hRetail, 0, ',', '.') }}</option>
                                            </select>
                                            @else
                                            <span class="badge badge-soft-neutral border fs-9 w-100 py-1">Item Bebas / Custom</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm sq-disc-group flex-nowrap">
                                                <div class="btn-group btn-group-sm flex-shrink-0 sq-disc-toggle border rounded-start-2 overflow-hidden" role="group">
                                                    <button type="button" class="btn btn-xs fw-bold btn-item-disc-mode {{ $itDiscTipe === 'persen' ? 'active' : '' }}" data-mode="persen">%</button>
                                                    <button type="button" class="btn btn-xs fw-bold btn-item-disc-mode {{ $itDiscTipe === 'nominal' ? 'active' : '' }}" data-mode="nominal">Rp</button>
                                                </div>
                                                <input type="hidden" class="item-disc-tipe" value="{{ $itDiscTipe }}">
                                                <input type="text" class="form-control form-control-sm text-end font-monospace item-disc-nilai" value="{{ $itDiscVal }}">
                                                <input type="hidden" name="items[{{ $idx + 1 }}][diskon_persen]" class="item-disc-persen" value="{{ $itDiscPersen }}">
                                                <input type="hidden" name="items[{{ $idx + 1 }}][diskon_nominal]" class="item-disc-nominal" value="{{ $itDiscNominal }}">
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                                <span class="fs-9 text-muted">Potongan:</span>
                                                <span class="text-danger fs-9 font-monospace fw-semibold item-disc-display">
                                                    @if($itDiscTipe === 'nominal' && $itDiscNominal > 0)
                                                        ({{ number_format($itDiscPersen, 1) }}%)
                                                    @elseif($itDiscPersen > 0)
                                                        - Rp {{ number_format($itDiscNominal, 0, ',', '.') }}
                                                    @else
                                                        - Rp 0
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="item-subtotal-display fs-7 fw-bold font-monospace text-body">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx + 1 }}][ppn_pajak_id]" class="form-select form-select-sm select-item-ppn fs-8" data-placeholder="Pilih PPN">
                                                <option value="" data-tarif="0" {{ empty($it->ppn_pajak_id) ? 'selected' : '' }}>Non PPN (0%)</option>
                                                @foreach($pajakPpnList as $p)
                                                    <option value="{{ $p->id }}" data-tarif="{{ (float)$p->tarif }}" {{ $it->ppn_pajak_id == $p->id ? 'selected' : '' }}>
                                                        {{ $p->nama_pajak }} ({{ (float)$p->tarif }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="items[{{ $idx + 1 }}][tarif_ppn]" class="item-tarif-ppn" value="{{ (float)$it->tarif_ppn }}">
                                            <input type="hidden" name="items[{{ $idx + 1 }}][nilai_ppn]" class="item-nilai-ppn" value="{{ (float)$it->nilai_ppn }}">
                                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                                <span class="fs-9 text-muted">Pajak:</span>
                                                <span class="text-primary fs-9 font-monospace fw-semibold item-ppn-display">
                                                    {{ (float)$it->nilai_ppn > 0 ? '+ Rp ' . number_format($it->nilai_ppn, 0, ',', '.') : 'Rp 0' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-item p-1.5" data-id="{{ $idx + 1 }}" title="Hapus Item">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div id="emptyItemNotice" class="text-center py-4 text-muted border border-dashed rounded-3" style="{{ $quotation->items->count() > 0 ? 'display:none;' : '' }}">
                            <i class="bi bi-box-seam fs-3 d-block mb-1 text-secondary opacity-50"></i>
                            <span class="fs-8">Belum ada item ditambahkan. Klik tombol <strong>Tambah dari Master Barang</strong> di atas.</span>
                        </div>
                    </div>

            <!-- Bottom Section Below Table: Ketentuan & Catatan (Kiri) + Ringkasan & Aksi (Kanan) -->
            <div class="row g-3 g-md-4">
                
                <!-- Kolom Kiri: Ketentuan Pembayaran, DP, Catatan & Syarat -->
                <div class="col-12 col-lg-7">
                    
                    <!-- 3. Ketentuan Pembayaran & Uang Muka (DP) -->
                    <div class="sq-form-section mb-3 mb-md-4">
                        <div class="sq-section-title">
                            <i class="bi bi-credit-card-2-front me-1"></i> 3. Ketentuan Pembayaran & Uang Muka (DP)
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8 mb-1">Tenor / Termin Pembayaran:</label>
                                <select name="payment_term_id" id="selectPaymentTerm" class="form-select form-select-sm select2" data-placeholder="-- Pilih Termin --">
                                    <option value="">-- Pilih Termin --</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ $quotation->payment_term_id == $pt->id ? 'selected' : '' }}>{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} Hari)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fs-8 mb-1">Uang Muka (DP):</label>
                                        <select name="tipe_dp" id="selectTipeDp" class="form-select form-select-sm select2" data-placeholder="Pilih Tipe DP">
                                            <option value="None" {{ $quotation->tipe_dp === 'None' ? 'selected' : '' }}>Tanpa DP</option>
                                            <option value="Persen" {{ $quotation->tipe_dp === 'Persen' ? 'selected' : '' }}>Persen (%)</option>
                                            <option value="Nominal" {{ $quotation->tipe_dp === 'Nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-8 mb-1">Nilai DP:</label>
                                        <input type="number" step="0.01" min="0" max="100" name="dp_persen" id="inputDpPersen" class="form-control form-control-sm text-end" value="{{ (float)$quotation->dp_persen }}" placeholder="Contoh: 50%" style="{{ $quotation->tipe_dp === 'Persen' ? '' : 'display:none;' }}">
                                        <input type="text" inputmode="numeric" name="dp_nominal" id="inputDpNominal" class="form-control form-control-sm text-end font-monospace" value="{{ number_format($quotation->dp_nominal ?: 0, 0, ',', '.') }}" placeholder="Nominal DP..." style="{{ $quotation->tipe_dp === 'Nominal' ? '' : 'display:none;' }}">
                                    </div>
                                </div>
                                <div id="displayDpBreakdown" class="col-12 fs-9 text-primary mt-1" style="{{ $quotation->tipe_dp !== 'None' && $quotation->dp_nominal > 0 ? '' : 'display:none;' }}">
                                    <div class="d-flex justify-content-between">
                                        <span>Nominal DP:</span>
                                        <strong id="displayDpCalculatedNominal" class="font-monospace">Rp {{ number_format($quotation->dp_nominal ?: 0, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Syarat, Ketentuan, & Catatan -->
                    <div class="sq-form-section">
                        <div class="sq-section-title">
                            <i class="bi bi-card-text me-1"></i> 4. Syarat, Ketentuan, & Catatan
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fs-8 mb-0">Syarat & Ketentuan (Terms & Conditions)</label>
                                    <small id="counter_syarat_ketentuan" class="text-muted fs-9 font-monospace">0/400</small>
                                </div>
                                <textarea name="syarat_ketentuan" id="input_syarat_ketentuan" maxlength="400" class="form-control form-control-sm" rows="4">{{ $quotation->syarat_ketentuan }}</textarea>
                                <div id="warn_syarat_ketentuan" class="text-danger fs-9 mt-1" style="display:none;"><i class="bi bi-exclamation-circle me-1"></i>Syarat & Ketentuan maksimal 400 karakter!</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fs-8 mb-0">Catatan Internal / Eksternal</label>
                                    <small id="counter_catatan" class="text-muted fs-9 font-monospace">0/60</small>
                                </div>
                                <textarea name="catatan" id="input_catatan" maxlength="60" class="form-control form-control-sm" rows="4">{{ $quotation->catatan }}</textarea>
                                <div id="warn_catatan" class="text-danger fs-9 mt-1" style="display:none;"><i class="bi bi-exclamation-circle me-1"></i>Catatan maksimal 60 karakter!</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan: Ringkasan Nilai Penawaran & Action Buttons -->
                <div class="col-12 col-lg-5">
                    <div class="sq-form-section">
                        <div class="sq-section-title text-success">
                            <i class="bi bi-calculator me-1"></i> Ringkasan Nilai Penawaran
                        </div>

                        <div class="d-flex justify-content-between fs-8 mb-2">
                            <span class="text-muted">Subtotal Item:</span>
                            <span class="fw-bold font-monospace" id="displaySubtotal">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                            <input type="hidden" name="subtotal" id="inputSubtotal" value="{{ $quotation->subtotal }}">
                        </div>

                        <!-- Total PPN (Akumulasi Item) -->
                        <div class="d-flex justify-content-between fs-8 mb-2 pt-2 border-top">
                            <span class="text-muted">Total PPN:</span>
                            <span class="fw-bold font-monospace text-primary" id="displayNilaiPpn">+ Rp {{ number_format($quotation->nilai_ppn ?: 0, 0, ',', '.') }}</span>
                            <input type="hidden" name="nilai_ppn" id="inputNilaiPpn" value="{{ $quotation->nilai_ppn ?: 0 }}">
                            <input type="hidden" name="tarif_ppn" id="inputTarifPpn" value="{{ $quotation->tarif_ppn ?: 0 }}">
                            <input type="hidden" name="tipe_ppn" id="selectTipePpn" value="Exclude">
                            <input type="hidden" name="diskon_tipe" value="Nominal">
                            <input type="hidden" name="diskon_persen" id="inputDiskonPersen" value="0">
                            <input type="hidden" name="diskon_nominal" id="inputDiskonNominal" value="0">
                        </div>

                            <input type="hidden" name="pph_pajak_id" value="">
                            <input type="hidden" name="tipe_pph" value="None">
                            <input type="hidden" name="tarif_pph" value="0">
                            <input type="hidden" name="nilai_pph" value="0">

                        <!-- Biaya Pengiriman / Ekstra -->
                        <div class="mb-3 pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="fs-8 mb-0">Biaya Pengiriman / Ekstra (Rp):</label>
                                <small id="counter_ongkir" class="text-muted fs-9 font-monospace">Maks 10 digit</small>
                            </div>
                            <input type="text" inputmode="numeric" name="biaya_pengiriman" id="inputBiayaPengiriman" class="form-control form-control-sm text-end fw-semibold font-monospace" value="{{ number_format($quotation->biaya_pengiriman ?: 0, 0, ',', '.') }}">
                            <div id="warn_ongkir" class="text-danger fs-9 mt-1" style="display:none;"><i class="bi bi-exclamation-circle me-1"></i>Biaya pengiriman maksimal 10 digit angka!</div>
                        </div>

                        <!-- Grand Total Box -->
                        <div class="sq-grand-total-box mb-3">
                            <div class="sq-grand-total-title mb-1">Grand Total Penawaran</div>
                            <div class="fs-4 sq-grand-total-amount font-monospace" id="displayGrandTotal">Rp {{ number_format($quotation->grand_total ?: 0, 0, ',', '.') }}</div>
                            <input type="hidden" name="grand_total" id="inputGrandTotal" value="{{ $quotation->grand_total ?: 0 }}">
                        </div>

                        <!-- HPP & Profit Analysis (Authorized Only) -->
                        @if($canSeeHpp)
                        <div class="sq-hpp-box mb-3">
                            <div class="fs-8 fw-bold text-secondary text-uppercase mb-1.5"><i class="bi bi-graph-up-arrow me-1"></i> Analisis HPP & Margin</div>
                            <div class="d-flex justify-content-between fs-8 mb-1">
                                <span class="text-muted">Total HPP:</span>
                                <span class="fw-semibold font-monospace" id="displayTotalHpp">Rp {{ number_format($quotation->total_hpp ?: 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="total_hpp" id="inputTotalHpp" value="{{ $canSeeHpp ? ($quotation->total_hpp ?: 0) : 0 }}">
                            </div>
                            <div class="d-flex justify-content-between fs-8 mb-1">
                                <span class="text-muted">Estimasi Laba Kotor:</span>
                                <span class="fw-bold text-success font-monospace" id="displayLabaKotor">Rp {{ number_format($quotation->estimasi_laba_kotor ?: 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="estimasi_laba_kotor" id="inputLabaKotor" value="{{ $quotation->estimasi_laba_kotor ?: 0 }}">
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-8">
                                <span class="text-muted">Margin Keuntungan:</span>
                                <span class="badge badge-soft-emerald fw-bold fs-8" id="displayMarginPersen">{{ number_format($quotation->margin_persen ?: 0, 1) }}%</span>
                                <input type="hidden" name="margin_persen" id="inputMarginPersen" value="{{ $quotation->margin_persen ?: 0 }}">
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 pt-3 border-top">
                            <button type="submit" name="status_btn" value="Sent" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm btn-submit-quotation">
                                <i class="bi bi-send-fill me-1"></i> Simpan & Kirim Penawaran
                            </button>
                            <button type="submit" name="status_btn" value="Draft" class="btn btn-outline-secondary rounded-pill py-2 fs-8 fw-semibold btn-submit-quotation">
                                <i class="bi bi-save me-1"></i> Simpan Sebagai Draft
                            </button>
                            <a href="{{ route('penawaran.index') }}" class="btn btn-link text-decoration-none text-muted py-1 fs-9 text-center">
                                Batal & Kembali
                            </a>
                        </div>

                    </div>
                </div>

            </div>

        </form>

    </div>

    <!-- Modal Picker Barang Master -->
    <div class="modal fade" id="modalPickerBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="max-width: 1200px; width: 95%;">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header px-4 py-3 border-bottom">
                    <h5 class="modal-title fw-bold text-body mb-0">
                        <i class="bi bi-box-seam me-2 text-primary"></i>
                        Pilih Barang dari Master Produk
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <input type="text" id="searchBarangInput" class="form-control rounded-pill px-3.5" placeholder="Ketik nama atau kode barang untuk mencari...">
                    </div>
                    <div class="d-md-none mb-2.5">
                        <span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 fs-9">
                            <i class="bi bi-arrows-left-right text-primary"></i>
                            <span>Geser tabel ke samping untuk melihat opsi harga & pilih</span>
                        </span>
                    </div>

                    <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover align-middle mb-0" id="pickerBarangTable">
                            <thead class="sticky-top">
                                <tr>
                                    <th>Kode & Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Pilihan Harga Jual</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangList as $b)
                                @php
                                    $pUrl = $b->resolved_photo_url ?: ($b->primaryFoto ? (str_starts_with($b->primaryFoto->foto_path, 'uploads/') ? asset($b->primaryFoto->foto_path) : asset('storage/' . ltrim($b->primaryFoto->foto_path, '/'))) : asset('uploads/barang/placeholder_garment.png'));
                                    $hRetail = (float)($b->harga_retail ?? $b->harga_jual_retail ?? 0);
                                    $hGrosir = (float)($b->harga_grosir ?? $b->harga_jual_grosir ?? 0);
                                    $hTender = (float)($b->harga_tender ?? $b->harga_jual_tender ?? 0);
                                    $hDefault = $hTender ?: ($hGrosir ?: ($hRetail ?: 0));
                                @endphp
                                <tr class="barang-item-row" data-search="{{ strtolower($b->kode_barang . ' ' . $b->nama_barang . ' ' . ($b->kategori?->nama_kategori ?? '')) }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <img src="{{ $pUrl }}" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                            <div>
                                                <div class="fw-bold text-body fs-8 mb-0.5">{{ $b->nama_barang }}</div>
                                                <span class="badge badge-soft-neutral font-monospace" style="font-size:0.7rem;">{{ $b->kode_barang }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-soft-sky fs-9">{{ $b->kategori?->nama_kategori ?? '-' }}</span></td>
                                    <td><span class="badge badge-soft-neutral font-monospace fs-9">{{ $b->satuan?->nama_satuan ?? 'Pcs' }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end gap-0.5 font-monospace">
                                            <span class="fs-9 text-muted"><span class="badge badge-soft-neutral me-1">Satuan</span>Rp {{ number_format($hRetail, 0, ',', '.') }}</span>
                                            <span class="fs-9 text-muted"><span class="badge badge-soft-sky me-1">Grosir</span>Rp {{ number_format($hGrosir, 0, ',', '.') }}</span>
                                            <span class="fs-8 fw-bold text-success"><span class="badge badge-soft-emerald me-1">Tender</span>Rp {{ number_format($hTender, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-select-item shadow-xs"
                                            data-id="{{ $b->id }}"
                                            data-kode="{{ $b->kode_barang }}"
                                            data-nama="{{ $b->nama_barang }}"
                                            data-satuan-id="{{ $b->satuan_id }}"
                                            data-satuan-nama="{{ $b->satuan?->nama_satuan ?? 'Pcs' }}"
                                            data-kategori-id="{{ $b->kategori_barang_id }}"
                                            data-harga="{{ $hDefault }}"
                                            data-harga-retail="{{ $hRetail }}"
                                            data-harga-grosir="{{ $hGrosir }}"
                                            data-harga-tender="{{ $hTender }}"
                                            data-hpp="{{ $b->hpp_referensi ?: 0 }}"
                                            data-photo-url="{{ $pUrl }}">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cari Customer Penawaran (Dual Theme & Scrollable) -->
    <!-- Modal Cari Customer Penawaran (Dual Theme & Horizontal Swipeable Table) -->
    <div class="modal fade" id="modalCariCustomerPenawaran" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-2 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0">
                            <i class="bi bi-building-check fs-5"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="modal-title fw-bold text-body mb-0 fs-6">Pilih Customer / Instansi</h6>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size:0.68rem;">{{ $customers->count() }} Klien</span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size:0.75rem;">Pilih customer yang dituju untuk surat penawaran harga.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 p-md-4 pt-2">
                    <!-- Search Input -->
                    <div class="mb-2.5 position-relative">
                        <input type="text" id="search_customer_penawaran_input" class="form-control rounded-pill ps-5 pe-5 py-2 shadow-xs" placeholder="Ketik nama instansi, PIC, telepon, atau kode..." autocomplete="off">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-primary fs-6"></i>
                        <button type="button" id="btn_clear_search_customer_penawaran" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted border-0 d-none" style="background: transparent;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Swipeable Category Chips (Geser ke Samping) -->
                    <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-2 text-nowrap align-items-center" id="filter_chips_customer_penawaran" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-semibold filter-chip-cust active" data-filter="all">Semua ({{ $customers->count() }})</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="pt">🏢 PT</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="cv">🏭 CV</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="distributor">📦 Distributor</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="end user">🎯 End User</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="retail">🏪 Retail</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="agen">🏷️ Agen</button>
                    </div>

                    <!-- Swipe Hint Mobile -->
                    <div class="d-md-none text-muted fs-9 mb-2 d-flex align-items-center gap-1.5 px-1">
                        <i class="bi bi-arrows-expand text-primary"></i>
                        <span>Geser tabel ke samping untuk melihat detail lengkap</span>
                    </div>

                    <!-- Horizontal Scrollable Table -->
                    <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table modal-lookup-table table-hover align-middle mb-0" id="table_customer_penawaran" style="min-width: 560px;">
                            <thead class="bg-body-tertiary sticky-top" style="z-index: 2;">
                                <tr class="fs-9 text-uppercase text-muted">
                                    <th style="width: 42%;" class="ps-3 py-2.5 text-nowrap">Nama Instansi / Klien</th>
                                    <th style="width: 28%;" class="py-2.5 text-nowrap">Kontak / PIC</th>
                                    <th style="width: 16%;" class="py-2.5 text-nowrap">Jenis</th>
                                    <th style="width: 14%;" class="text-center pe-3 py-2.5 text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="customer_penawaran_lookup_container">
                                @foreach($customers as $c)
                                @php
                                    $corpName = $c->nama_perusahaan ?: $c->nama_customer;
                                    $displayCorpName = preg_replace('/^(PT|CV|UD|PD|KOP)\s+\1\s+/i', '$1 ', $corpName);
                                    $initials = mb_strtoupper(mb_substr($displayCorpName, 0, 2));
                                    if (str_starts_with(mb_strtoupper($displayCorpName), 'PT ')) {
                                        $initials = 'PT';
                                    } elseif (str_starts_with(mb_strtoupper($displayCorpName), 'CV ')) {
                                        $initials = 'CV';
                                    } elseif (str_starts_with(mb_strtoupper($displayCorpName), 'UD ')) {
                                        $initials = 'UD';
                                    }
                                @endphp
                                <tr class="customer-penawaran-card cursor-pointer"
                                    data-id="{{ $c->id }}" 
                                    data-name="{{ $displayCorpName }}"
                                    data-pic="{{ $c->nama_customer }}"
                                    data-jenis="{{ strtolower($c->jenis_customer ?? '') }}"
                                    data-search="{{ strtolower($displayCorpName . ' ' . $c->nama_customer . ' ' . $c->kode_customer . ' ' . $c->telepon . ' ' . ($c->jenis_customer ?? '')) }}">
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary font-monospace rounded-3 px-2 py-1 flex-shrink-0" style="font-size: 0.72rem;">{{ $initials }}</span>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-body fs-7 text-truncate" style="max-width: 240px;">{{ $displayCorpName }}</div>
                                                <code class="text-muted fs-9 font-monospace">{{ $c->kode_customer ?: '-' }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5">
                                        <div class="fs-8 fw-semibold text-body text-truncate" style="max-width: 160px;">{{ $c->nama_customer ?: '-' }}</div>
                                        <div class="text-muted fs-9"><i class="bi bi-telephone text-success me-1"></i>{{ $c->telepon ?: ($c->hp ?: '-') }}</div>
                                    </td>
                                    <td class="py-2.5 text-nowrap">
                                        @if($c->jenis_customer)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">{{ $c->jenis_customer }}</span>
                                        @else
                                            <span class="text-muted fs-9">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3 py-2.5 text-nowrap">
                                        <button type="button" class="btn btn-sm btn-primary btn-select-customer-penawaran rounded-pill px-3.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                                                data-id="{{ $c->id }}"
                                                data-name="{{ $displayCorpName }}">
                                            <i class="bi bi-check2"></i>
                                            <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div id="customer_penawaran_lookup_empty" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle bg-body-tertiary text-muted d-inline-flex mb-2">
                                <i class="bi bi-search fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Customer Tidak Ditemukan</h6>
                            <p class="text-muted small mb-0">Tidak ada instansi atau customer yang cocok dengan kata kunci pencarian Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cari Tender Penawaran (Dual Theme & Horizontal Swipeable Table) -->
    <div class="modal fade" id="modalCariTenderPenawaran" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-2 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <span class="p-2 rounded-3 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0">
                            <i class="bi bi-briefcase-fill fs-5"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="modal-title fw-bold text-body mb-0 fs-6">Pilih Proyek / Tender</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size:0.68rem;">{{ $tenders->count() }} Proyek</span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size:0.75rem;">Pilih tender untuk memuat otomatis instansi, PIC, dan rincian item.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 p-md-4 pt-2">
                    <!-- Search Input -->
                    <div class="mb-2.5 position-relative">
                        <input type="text" id="search_tender_penawaran_input" class="form-control rounded-pill ps-5 pe-5 py-2 shadow-xs" placeholder="Ketik nama proyek, no. tender, atau nama instansi..." autocomplete="off">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-success fs-6"></i>
                        <button type="button" id="btn_clear_search_tender_penawaran" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted border-0 d-none" style="background: transparent;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Swipeable Category Chips (Geser ke Samping) -->
                    <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-2 text-nowrap align-items-center" id="filter_chips_tender_penawaran" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-semibold filter-chip-tender active" data-filter="all">Semua ({{ $tenders->count() }})</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="won">🏆 Won</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="negotiation">🤝 Negotiation</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="sent">📨 Sent</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="approved">✅ Approved</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="draft">📝 Draft</button>
                    </div>

                    <!-- Swipe Hint Mobile -->
                    <div class="d-md-none text-muted fs-9 mb-2 d-flex align-items-center gap-1.5 px-1">
                        <i class="bi bi-arrows-expand text-success"></i>
                        <span>Geser tabel ke samping untuk melihat detail lengkap</span>
                    </div>

                    <!-- Horizontal Scrollable Table -->
                    <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table modal-lookup-table table-hover align-middle mb-0" id="table_tender_penawaran" style="min-width: 600px;">
                            <thead class="bg-body-tertiary sticky-top" style="z-index: 2;">
                                <tr class="fs-9 text-uppercase text-muted">
                                    <th style="width: 38%;" class="ps-3 py-2.5 text-nowrap">Nama Proyek / Tender</th>
                                    <th style="width: 27%;" class="py-2.5 text-nowrap">Instansi Klien</th>
                                    <th style="width: 21%;" class="py-2.5 text-nowrap">Nilai Proyek</th>
                                    <th style="width: 14%;" class="text-center pe-3 py-2.5 text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tender_penawaran_lookup_container">
                                @foreach($tenders as $t)
                                @php
                                    $tCustCorp = $t->customer ? ($t->customer->nama_perusahaan ?: $t->customer->nama_customer) : '-';
                                    $displayTCustCorp = preg_replace('/^(PT|CV|UD|PD|KOP)\s+\1\s+/i', '$1 ', $tCustCorp);
                                    $statusBadgeClass = [
                                        'Draft' => 'bg-secondary-subtle text-secondary',
                                        'Pending Approval' => 'bg-warning-subtle text-dark',
                                        'Approved' => 'bg-success-subtle text-success',
                                        'Sent' => 'bg-info-subtle text-info',
                                        'Negotiation' => 'bg-primary-subtle text-primary',
                                        'Won' => 'bg-success text-white',
                                    ][$t->status] ?? 'bg-secondary-subtle text-secondary';
                                @endphp
                                <tr class="tender-penawaran-card cursor-pointer"
                                    data-id="{{ $t->id }}" 
                                    data-nama="{{ $t->nama_tender }}"
                                    data-nomor="{{ $t->nomor_kontrak }}"
                                    data-pagu="{{ (float)$t->nilai_estimasi }}"
                                    data-customer-id="{{ $t->customer_id }}"
                                    data-customer-name="{{ $displayTCustCorp }}"
                                    data-status="{{ strtolower($t->status) }}"
                                    data-search="{{ strtolower($t->nama_tender . ' ' . $t->nomor_kontrak . ' ' . $displayTCustCorp . ' ' . $t->status) }}">
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success-subtle text-success font-monospace rounded-3 px-2 py-1 flex-shrink-0"><i class="bi bi-briefcase"></i></span>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-body fs-7 text-truncate" style="max-width: 240px;">{{ $t->nama_tender }}</div>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <code class="text-muted fs-9 font-monospace">{{ $t->nomor_kontrak }}</code>
                                                    <span class="badge {{ $statusBadgeClass }} font-monospace" style="font-size: 0.62rem;">{{ $t->status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5">
                                        <div class="fs-8 fw-semibold text-body text-truncate" style="max-width: 180px;">{{ $displayTCustCorp }}</div>
                                        <div class="text-muted fs-9"><i class="bi bi-box-seam me-1"></i>{{ $t->items->count() }} Item</div>
                                    </td>
                                    <td class="py-2.5 text-nowrap">
                                        <div class="fw-bold text-success font-monospace fs-8 text-nowrap">Rp {{ number_format($t->nilai_estimasi, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="text-center pe-3 py-2.5 text-nowrap">
                                        <button type="button" class="btn btn-sm btn-success btn-select-tender-penawaran rounded-pill px-3.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                                                data-id="{{ $t->id }}"
                                                data-nama="{{ $t->nama_tender }}"
                                                data-nomor="{{ $t->nomor_kontrak }}"
                                                data-pagu="{{ (float)$t->nilai_estimasi }}"
                                                data-customer-id="{{ $t->customer_id }}"
                                                data-customer-name="{{ $displayTCustCorp }}">
                                            <i class="bi bi-check2"></i>
                                            <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div id="tender_penawaran_lookup_empty" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle bg-body-tertiary text-muted d-inline-flex mb-2">
                                <i class="bi bi-search fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Tender Tidak Ditemukan</h6>
                            <p class="text-muted small mb-0">Tidak ada proyek lelang atau tender yang cocok dengan kata kunci pencarian Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window._tendersMap = @json($tendersMap ?? []);

        // Helper functions for Rupiah formatting with dots
        function parseRupiah(val) {
            if (!val) return 0;
            if (typeof val === 'number') return val;
            let clean = val.toString().replace(/[^0-9]/g, '');
            return parseFloat(clean) || 0;
        }

        function formatRupiah(num) {
            return (parseFloat(num) || 0).toLocaleString('id-ID');
        }

        $(document).ready(function() {
            $('#formQuotation, #formQuotation input, #formQuotation textarea').attr('autocomplete', 'off');

            // Security Character Limit Counters & Live Warning Handlers
            function initFieldSecurityCounters() {
                function checkPerihal() {
                    let val = $('input[name="perihal"]').val() || '';
                    let len = val.length;
                    $('#counter_perihal').text(len + '/40');
                    if (len >= 40) {
                        $('#counter_perihal').removeClass('text-muted').addClass('text-danger fw-bold');
                        $('#warn_perihal').show();
                    } else {
                        $('#counter_perihal').removeClass('text-danger fw-bold').addClass('text-muted');
                        $('#warn_perihal').hide();
                    }
                }
                $('input[name="perihal"]').on('input change keyup paste', function() {
                    let val = $(this).val() || '';
                    if (val.length > 40) {
                        $(this).val(val.substring(0, 40));
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Karakter Tercapai',
                            text: 'Judul Penawaran maksimal 40 karakter!',
                            timer: 2200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                    checkPerihal();
                });
                checkPerihal();

                function checkCatatan() {
                    let val = $('textarea[name="catatan"]').val() || '';
                    let len = val.length;
                    $('#counter_catatan').text(len + '/60');
                    if (len >= 60) {
                        $('#counter_catatan').removeClass('text-muted').addClass('text-danger fw-bold');
                        $('#warn_catatan').show();
                    } else {
                        $('#counter_catatan').removeClass('text-danger fw-bold').addClass('text-muted');
                        $('#warn_catatan').hide();
                    }
                }
                $('textarea[name="catatan"]').on('input change keyup paste', function() {
                    let val = $(this).val() || '';
                    if (val.length > 60) {
                        $(this).val(val.substring(0, 60));
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Karakter Tercapai',
                            text: 'Catatan maksimal 60 karakter!',
                            timer: 2200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                    checkCatatan();
                });
                checkCatatan();

                function checkSyarat() {
                    let val = $('textarea[name="syarat_ketentuan"]').val() || '';
                    let len = val.length;
                    $('#counter_syarat_ketentuan').text(len + '/400');
                    if (len >= 400) {
                        $('#counter_syarat_ketentuan').removeClass('text-muted').addClass('text-danger fw-bold');
                        $('#warn_syarat_ketentuan').show();
                    } else {
                        $('#counter_syarat_ketentuan').removeClass('text-danger fw-bold').addClass('text-muted');
                        $('#warn_syarat_ketentuan').hide();
                    }
                }
                $('textarea[name="syarat_ketentuan"]').on('input change keyup paste', function() {
                    let val = $(this).val() || '';
                    if (val.length > 400) {
                        $(this).val(val.substring(0, 400));
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Karakter Tercapai',
                            text: 'Syarat & Ketentuan maksimal 400 karakter!',
                            timer: 2200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                    checkSyarat();
                });
                checkSyarat();

                $('#inputBiayaPengiriman').on('input change keyup paste', function() {
                    let rawDigits = ($(this).val() || '').replace(/\D/g, '');
                    if (rawDigits.length > 10) {
                        let truncated = rawDigits.substring(0, 10);
                        $(this).val(formatRupiah(truncated));
                        $('#warn_ongkir').show();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Nilai Tercapai',
                            text: 'Biaya Pengiriman maksimal 10 digit angka!',
                            timer: 2200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        $('#warn_ongkir').hide();
                    }
                });
            }
            initFieldSecurityCounters();

            // Initialize Select2 with Searchable Support and Auto Focus
            function initSelect2Dropdowns() {
                if ($.fn.select2) {
                    $('.select2').each(function() {
                        let placeholder = $(this).data('placeholder') || '-- Pilih --';
                        let hasEmpty = $(this).find('option[value=""]').length > 0;
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: placeholder,
                                allowClear: hasEmpty
                            });
                        }
                    });
                }
            }
            initSelect2Dropdowns();

            // Initialize Searchable PPN Select2
            function initPpnSelect2(container) {
                if (!$.fn.select2) return;
                let target = container ? $(container).find('.select-item-ppn') : $('.select-item-ppn');
                target.each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: $(this).data('placeholder') || 'Pilih PPN',
                            minimumResultsForSearch: 0
                        });
                    }
                });
            }
            initPpnSelect2();

            // Auto-focus search input immediately when Select2 dropdown opens
            $(document).on('select2:open', function() {
                setTimeout(function() {
                    let searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 30);
            });

            let itemIndex = {{ $quotation->items->count() + 10 }};

            // Trigger Modal Picker
            $('#btnAddMasterItem').on('click', function() {
                $('#modalPickerBarang').modal('show');
                $('#searchBarangInput').val('').trigger('keyup').focus();
            });

            // Filter in modal picker
            $('#searchBarangInput').on('keyup', function() {
                let term = $(this).val().toLowerCase();
                $('.barang-item-row').each(function() {
                    let searchData = $(this).data('search');
                    $(this).toggle(searchData.indexOf(term) > -1);
                });
            });

            // Select item from picker
            $(document).on('click', '.btn-select-item', function() {
                const b = $(this).data();
                addItemRow({
                    barang_id: b.id,
                    kode: b.kode,
                    nama: b.nama,
                    satuan_id: b.satuanId,
                    satuan_nama: b.satuanNama,
                    kategori_id: b.kategoriId,
                    harga: b.harga,
                    harga_retail: b.hargaRetail || 0,
                    harga_grosir: b.hargaGrosir || 0,
                    harga_tender: b.hargaTender || 0,
                    tier_default: (b.hargaTender > 0) ? 'tender' : ((b.hargaGrosir > 0) ? 'grosir' : 'satuan'),
                    hpp: b.hpp,
                    photo_url: b.photoUrl || '',
                    is_custom: false
                });
                $('#modalPickerBarang').modal('hide');
            });


            // Function to append Row
            function addItemRow(data) {
                itemIndex++;
                $('#emptyItemNotice').hide();

                let safeCustomName = data.nama_barang_custom || data.nama || '';
                let nameInput = data.is_custom 
                    ? `<input type="text" name="items[${itemIndex}][nama_barang_custom]" class="form-control form-control-sm fw-bold" value="${$('<div>').text(safeCustomName).html()}" placeholder="Nama item / jasa / pekerjaan..." required>`
                    : `<div class="d-flex align-items-center gap-2.5">
                            <img src="${data.photo_url || '/uploads/barang/placeholder_garment.png'}" style="width:40px;height:40px;object-fit:cover;border-radius:8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                            <div class="min-w-0">
                                <div class="fw-semibold text-body fs-8 text-truncate mb-0.5" title="${$('<div>').text(data.nama).html()}">${$('<div>').text(data.nama).html()}</div>
                                <span class="badge badge-soft-neutral fs-9 px-1.5 py-0.5 font-monospace">${$('<div>').text(data.kode).html()}</span>
                            </div>
                       </div>
                       <input type="hidden" name="items[${itemIndex}][barang_id]" value="${data.barang_id}">`;

                let hRetail = parseFloat(data.harga_retail) || 0;
                let hGrosir = parseFloat(data.harga_grosir) || 0;
                let hTender = parseFloat(data.harga_tender) || 0;
                let currentPrice = parseFloat(data.harga) || 0;
                let tierDef = data.tier_default || 'tender';

                let priceSelectorHtml = !data.is_custom ? `
                    <select class="form-select form-select-xs select-price-tier"
                        data-harga-retail="${hRetail}"
                        data-harga-grosir="${hGrosir}"
                        data-harga-tender="${hTender}">
                        <option value="tender" ${tierDef === 'tender' ? 'selected' : ''}>🏷️ Tender: Rp ${hTender.toLocaleString('id-ID')}</option>
                        <option value="grosir" ${tierDef === 'grosir' ? 'selected' : ''}>📦 Grosir: Rp ${hGrosir.toLocaleString('id-ID')}</option>
                        <option value="satuan" ${tierDef === 'satuan' ? 'selected' : ''}>🛍️ Satuan: Rp ${hRetail.toLocaleString('id-ID')}</option>
                    </select>
                ` : `<span class="badge badge-soft-neutral border fs-9 w-100 py-1">Item Bebas / Custom</span>`;

                let ppnOptionsHtml = `
                    <option value="" data-tarif="0" selected>Non PPN (0%)</option>
                    @foreach($pajakPpnList as $p)
                        <option value="{{ $p->id }}" data-tarif="{{ (float)$p->tarif }}" ${data.ppn_pajak_id == '{{ $p->id }}' ? 'selected' : ''}>
                            {{ $p->nama_pajak }} ({{ (float)$p->tarif }}%)
                        </option>
                    @endforeach
                `;

                let discPersen = parseFloat(data.diskon_persen) || 0;
                let discNominal = parseFloat(data.diskon_nominal) || 0;
                let discTipe = (discNominal > 0 && discPersen === 0) ? 'nominal' : 'persen';
                let discVal = (discTipe === 'nominal') ? formatRupiah(discNominal) : discPersen;

                let rowHtml = `
                    <tr id="itemRow_${itemIndex}" class="item-calc-row">
                        <td>
                            ${nameInput}
                            <div class="mt-1.5">
                                <input type="text" name="items[${itemIndex}][deskripsi_item]" class="form-control form-control-xs item-desc-input" value="${$('<div>').text(data.deskripsi_item || '').html()}" placeholder="+ Catatan spesifikasi / detail item...">
                            </div>
                            <input type="hidden" name="items[${itemIndex}][kategori_id]" value="${data.kategori_id || ''}">
                            <input type="hidden" name="items[${itemIndex}][satuan_id]" value="${data.satuan_id || ''}">
                            <input type="hidden" name="items[${itemIndex}][hpp_satuan]" class="item-hpp-satuan" value="${data.hpp || 0}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][qty]" class="form-control form-control-sm text-end fw-bold font-monospace item-qty" value="${parseFloat(data.qty) || 1}" required>
                                <span class="input-group-text fs-9 px-2 text-muted fw-semibold item-satuan-label bg-body-tertiary" title="${$('<div>').text(data.satuan_nama || 'Pcs').html()}">${$('<div>').text(data.satuan_nama || 'Pcs').html()}</span>
                                <input type="hidden" name="items[${itemIndex}][nama_satuan]" value="${$('<div>').text(data.satuan_nama || 'Pcs').html()}">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text fs-9 px-1.5 text-muted">Rp</span>
                                <input type="text" inputmode="numeric" name="items[${itemIndex}][harga]" class="form-control form-control-sm text-end item-price fw-bold font-monospace" value="${formatRupiah(currentPrice)}" required>
                            </div>
                            ${priceSelectorHtml}
                        </td>
                        <td>
                            <div class="input-group input-group-sm sq-disc-group flex-nowrap">
                                <div class="btn-group btn-group-sm flex-shrink-0 sq-disc-toggle border rounded-start-2 overflow-hidden" role="group">
                                    <button type="button" class="btn btn-xs fw-bold btn-item-disc-mode ${discTipe === 'persen' ? 'active' : ''}" data-mode="persen">%</button>
                                    <button type="button" class="btn btn-xs fw-bold btn-item-disc-mode ${discTipe === 'nominal' ? 'active' : ''}" data-mode="nominal">Rp</button>
                                </div>
                                <input type="hidden" class="item-disc-tipe" value="${discTipe}">
                                <input type="text" class="form-control form-control-sm text-end font-monospace item-disc-nilai" value="${discVal}">
                                <input type="hidden" name="items[${itemIndex}][diskon_persen]" class="item-disc-persen" value="${discPersen}">
                                <input type="hidden" name="items[${itemIndex}][diskon_nominal]" class="item-disc-nominal" value="${discNominal}">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                <span class="fs-9 text-muted">Potongan:</span>
                                <span class="text-danger fs-9 font-monospace fw-semibold item-disc-display">- Rp 0</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="item-subtotal-display fs-7 fw-bold font-monospace text-body">Rp 0</div>
                        </td>
                        <td>
                            <select name="items[${itemIndex}][ppn_pajak_id]" class="form-select form-select-sm select-item-ppn fs-8" data-placeholder="Pilih PPN">
                                ${ppnOptionsHtml}
                            </select>
                            <input type="hidden" name="items[${itemIndex}][tarif_ppn]" class="item-tarif-ppn" value="${parseFloat(data.tarif_ppn) || 0}">
                            <input type="hidden" name="items[${itemIndex}][nilai_ppn]" class="item-nilai-ppn" value="${parseFloat(data.nilai_ppn) || 0}">
                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                <span class="fs-9 text-muted">Pajak:</span>
                                <span class="text-primary fs-9 font-monospace fw-semibold item-ppn-display">Rp 0</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-item p-1.5" data-id="${itemIndex}" title="Hapus Item">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#itemsTableBody').append(rowHtml);
                initPpnSelect2($(`#itemRow_${itemIndex}`));
                calculateTotal();
            }

            // Remove Row
            $(document).on('click', '.btn-remove-item', function() {
                const id = $(this).data('id');
                const $row = $(`#itemRow_${id}`);
                $row.find('.select-item-ppn').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });
                $row.remove();
                if ($('#itemsTableBody tr').length === 0) {
                    $('#emptyItemNotice').show();
                }
                calculateTotal();
            });

            // Real-time Indonesian dot formatting on input for price and currency inputs
            $(document).on('input', '.item-price, #inputBiayaPengiriman, #inputDpNominal', function() {
                let raw = $(this).val().replace(/[^0-9]/g, '');
                if (raw === '') {
                    $(this).val('0');
                } else {
                    $(this).val(parseInt(raw, 10).toLocaleString('id-ID'));
                }
            });

            // Format nominal discount with dot separators when tipe is nominal
            $(document).on('input', '.item-disc-nilai', function() {
                let row = $(this).closest('tr');
                let tipe = row.find('.item-disc-tipe').val();
                if (tipe === 'nominal') {
                    let raw = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(raw === '' ? '0' : parseInt(raw, 10).toLocaleString('id-ID'));
                }
            });

            // Handle toggle click between % and Rp
            $(document).on('click', '.btn-item-disc-mode', function(e) {
                e.preventDefault();
                let btn = $(this);
                if (btn.hasClass('active')) return;

                let group = btn.closest('.sq-disc-toggle');
                group.find('.btn-item-disc-mode').removeClass('active');
                btn.addClass('active');

                let mode = btn.data('mode');
                let row = btn.closest('tr');
                let tipeInput = row.find('.item-disc-tipe');
                tipeInput.val(mode).trigger('change');
            });

            // Handle switch between % and Rp
            $(document).on('change', '.item-disc-tipe', function() {
                let row = $(this).closest('tr');
                let tipe = $(this).val();
                let inputValEl = row.find('.item-disc-nilai');
                let discPersen = parseFloat(row.find('.item-disc-persen').val()) || 0;
                let discNominal = parseFloat(row.find('.item-disc-nominal').val()) || 0;

                // Sync toggle button active state
                let group = row.find('.sq-disc-toggle');
                group.find('.btn-item-disc-mode').removeClass('active');
                group.find(`.btn-item-disc-mode[data-mode="${tipe}"]`).addClass('active');

                if (tipe === 'nominal') {
                    inputValEl.val(discNominal > 0 ? formatRupiah(discNominal) : '0');
                } else {
                    inputValEl.val(discPersen > 0 ? parseFloat(discPersen.toFixed(2)) : '0');
                }
                calculateTotal();
            });

            // Handle Price Tier Selection
            $(document).on('change', '.select-price-tier', function() {
                let tier = $(this).val();
                let row = $(this).closest('tr');
                let priceInput = row.find('.item-price');

                if (tier === 'tender') {
                    let p = parseFloat($(this).data('harga-tender')) || 0;
                    priceInput.val(formatRupiah(p));
                } else if (tier === 'grosir') {
                    let p = parseFloat($(this).data('harga-grosir')) || 0;
                    priceInput.val(formatRupiah(p));
                } else if (tier === 'satuan') {
                    let p = parseFloat($(this).data('harga-retail')) || 0;
                    priceInput.val(formatRupiah(p));
                }
                calculateTotal();
            });

            // When user types manually in price input, mark tier as custom if different
            $(document).on('input', '.item-price', function() {
                let row = $(this).closest('tr');
                let tierSelect = row.find('.select-price-tier');
                if (tierSelect.length) {
                    let currentVal = parseRupiah($(this).val());
                    let hTender = parseFloat(tierSelect.data('harga-tender')) || 0;
                    let hGrosir = parseFloat(tierSelect.data('harga-grosir')) || 0;
                    let hRetail = parseFloat(tierSelect.data('harga-retail')) || 0;

                    if (currentVal === hTender && hTender > 0) {
                        tierSelect.val('tender');
                    } else if (currentVal === hGrosir && hGrosir > 0) {
                        tierSelect.val('grosir');
                    } else if (currentVal === hRetail && hRetail > 0) {
                        tierSelect.val('satuan');
                    } else {
                        tierSelect.val(null);
                    }
                }
            });

            // Live Calculation Trigger
            $(document).on('input change select2:select select2:clear', '.item-qty, .item-price, .item-disc-tipe, .item-disc-nilai, .select-item-ppn, #inputBiayaPengiriman, #selectTipeDp, #inputDpPersen, #inputDpNominal', function() {
                calculateTotal();
            });

            // ===== MODAL LOOKUP CUSTOMER PENAWARAN =====
            $('#btnOpenLookupCustomer').on('click', function() {
                $('#modalCariCustomerPenawaran').modal('show');
                $('#search_customer_penawaran_input').val('');
                $('#filter_chips_customer_penawaran .filter-chip-cust').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $('#filter_chips_customer_penawaran .filter-chip-cust[data-filter="all"]').removeClass('btn-outline-secondary').addClass('btn-primary active');
                filterCustomerTable();
                setTimeout(() => $('#search_customer_penawaran_input').focus(), 300);
            });

            function filterCustomerTable() {
                let term = $('#search_customer_penawaran_input').val().toLowerCase().trim();
                let activeFilter = $('#filter_chips_customer_penawaran .filter-chip-cust.active').data('filter') || 'all';
                $('#btn_clear_search_customer_penawaran').toggleClass('d-none', !term);
                
                let visibleCount = 0;
                $('#customer_penawaran_lookup_container tr.customer-penawaran-card').each(function() {
                    let searchStr = (String($(this).attr('data-search') || '')).toLowerCase();
                    let jenis = (String($(this).attr('data-jenis') || '')).toLowerCase();
                    let name = (String($(this).attr('data-name') || '')).toLowerCase();

                    let matchSearch = !term || searchStr.indexOf(term) > -1;
                    let matchCategory = true;
                    if (activeFilter === 'pt') {
                        matchCategory = name.startsWith('pt ') || searchStr.indexOf('pt ') > -1;
                    } else if (activeFilter === 'cv') {
                        matchCategory = name.startsWith('cv ') || searchStr.indexOf('cv ') > -1;
                    } else if (activeFilter !== 'all') {
                        matchCategory = jenis.indexOf(activeFilter) > -1;
                    }

                    if (matchSearch && matchCategory) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });
                $('#customer_penawaran_lookup_empty').toggleClass('d-none', visibleCount > 0);
            }

            $(document).on('input keyup search paste', '#search_customer_penawaran_input', function() {
                filterCustomerTable();
            });

            $(document).on('click', '.filter-chip-cust', function() {
                $('#filter_chips_customer_penawaran .filter-chip-cust').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
                filterCustomerTable();
            });

            $('#btn_clear_search_customer_penawaran').on('click', function() {
                $('#search_customer_penawaran_input').val('');
                filterCustomerTable();
                $('#search_customer_penawaran_input').focus();
            });

            $(document).on('click', '.btn-select-customer-penawaran, .customer-penawaran-card', function(e) {
                if ($(e.target).closest('button').length && !$(e.target).closest('.btn-select-customer-penawaran').length) return;
                let card = $(this).closest('.customer-penawaran-card');
                let id = card.data('id');
                let name = card.data('name');

                $('#selectCustomer').val(id).trigger('change');
                $('#displayCustomer').val(name);
                $('#modalCariCustomerPenawaran').modal('hide');
            });

            // ===== MODAL LOOKUP TENDER PENAWARAN =====
            $('#btnOpenLookupTender').on('click', function() {
                $('#modalCariTenderPenawaran').modal('show');
                $('#search_tender_penawaran_input').val('');
                $('#filter_chips_tender_penawaran .filter-chip-tender').removeClass('btn-success active').addClass('btn-outline-secondary');
                $('#filter_chips_tender_penawaran .filter-chip-tender[data-filter="all"]').removeClass('btn-outline-secondary').addClass('btn-success active');
                filterTenderTable();
                setTimeout(() => $('#search_tender_penawaran_input').focus(), 300);
            });

            function filterTenderTable() {
                let term = $('#search_tender_penawaran_input').val().toLowerCase().trim();
                let activeFilter = $('#filter_chips_tender_penawaran .filter-chip-tender.active').data('filter') || 'all';
                $('#btn_clear_search_tender_penawaran').toggleClass('d-none', !term);
                
                let visibleCount = 0;
                $('#tender_penawaran_lookup_container tr.tender-penawaran-card').each(function() {
                    let searchStr = (String($(this).attr('data-search') || '')).toLowerCase();
                    let status = (String($(this).attr('data-status') || '')).toLowerCase();

                    let matchSearch = !term || searchStr.indexOf(term) > -1;
                    let matchCategory = (activeFilter === 'all') || (status.indexOf(activeFilter) > -1);

                    if (matchSearch && matchCategory) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });
                $('#tender_penawaran_lookup_empty').toggleClass('d-none', visibleCount > 0);
            }

            $(document).on('input keyup search paste', '#search_tender_penawaran_input', function() {
                filterTenderTable();
            });

            $(document).on('click', '.filter-chip-tender', function() {
                $('#filter_chips_tender_penawaran .filter-chip-tender').removeClass('btn-success active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-success active');
                filterTenderTable();
            });

            $('#btn_clear_search_tender_penawaran').on('click', function() {
                $('#search_tender_penawaran_input').val('');
                filterTenderTable();
                $('#search_tender_penawaran_input').focus();
            });

            $(document).on('click', '.btn-select-tender-penawaran, .tender-penawaran-card', function(e) {
                if ($(e.target).closest('button').length && !$(e.target).closest('.btn-select-tender-penawaran').length) return;
                let card = $(this).closest('.tender-penawaran-card');
                let id = card.data('id');
                let nama = card.data('nama');
                let nomor = card.data('nomor');
                let pagu = parseFloat(card.data('pagu')) || 0;
                let customerId = card.data('customer-id');
                let customerName = card.data('customer-name');

                $('#selectTender').val(id).data('pagu', pagu).attr('data-pagu', pagu);
                $('#displayTender').val(nomor + ' - ' + nama);
                $('#displayTenderPagu').text('Rp ' + formatRupiah(pagu));
                $('#tenderPaguInfoBox').removeClass('d-none');
                $('#btnClearTender').removeClass('d-none');

                if (customerId) {
                    $('#selectCustomer').val(customerId).trigger('change');
                    $('#displayCustomer').val(customerName);
                }

                // Process Tender Map Data
                let tData = window._tendersMap ? window._tendersMap[id] : null;
                if (tData) {
                    if (tData.sales_id && $('select[name="sales_id"]').length) {
                        $('select[name="sales_id"]').val(tData.sales_id).trigger('change');
                    }
                    let perihalInput = $('input[name="perihal"]');
                    if (perihalInput.length && (!perihalInput.val() || perihalInput.val().startsWith('Penawaran '))) {
                        perihalInput.val('Penawaran ' + tData.nama_tender);
                    }
                    if (Array.isArray(tData.items) && tData.items.length > 0) {
                        $('#itemsTableBody').empty();
                        $('#emptyItemNotice').hide();
                        tData.items.forEach(function(item) {
                            addItemRow(item);
                        });
                        calculateTotal();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: `${tData.items.length} item dari tender berhasil dimuat!`,
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    }
                } else {
                    calculateTotal();
                }

                $('#modalCariTenderPenawaran').modal('hide');
            });

            $('#btnClearTender').on('click', function() {
                $('#selectTender').val('').data('pagu', 0).attr('data-pagu', 0);
                $('#displayTender').val('');
                $('#tenderPaguInfoBox').addClass('d-none');
                $(this).addClass('d-none');
                calculateTotal();
            });

            // DP Type Toggle
            $('#selectTipeDp').on('change', function() {
                let val = $(this).val();
                if (val === 'Persen') {
                    $('#inputDpPersen').show();
                    $('#inputDpNominal').hide();
                } else if (val === 'Nominal') {
                    $('#inputDpPersen').hide();
                    $('#inputDpNominal').show();
                } else {
                    $('#inputDpPersen').hide();
                    $('#inputDpNominal').hide();
                }
            });

            function calculateTotal() {
                let subtotal = 0;
                let totalHpp = 0;
                let totalNilaiPpn = 0;

                $('.item-calc-row').each(function() {
                    let qty = parseFloat($(this).find('.item-qty').val()) || 0;
                    let price = parseRupiah($(this).find('.item-price').val());
                    let hppSatuan = parseFloat($(this).find('.item-hpp-satuan').val()) || 0;
                    let gross = qty * price;

                    // Discount calculation (% or Rp)
                    let discTipe = $(this).find('.item-disc-tipe').val() || 'persen';
                    let discInput = $(this).find('.item-disc-nilai').val();
                    let discPersen = 0;
                    let discNominal = 0;

                    if (discTipe === 'nominal') {
                        discNominal = parseRupiah(discInput);
                        if (discNominal > gross) {
                            discNominal = gross;
                        }
                        discPersen = gross > 0 ? (discNominal / gross) * 100 : 0;
                        if (discNominal > 0) {
                            $(this).find('.item-disc-display').text('(' + discPersen.toFixed(1) + '%)');
                        } else {
                            $(this).find('.item-disc-display').text('- Rp 0');
                        }
                    } else {
                        discPersen = parseFloat(discInput) || 0;
                        if (discPersen > 100) discPersen = 100;
                        discNominal = gross * (discPersen / 100);
                        if (discPersen > 0) {
                            $(this).find('.item-disc-display').text('- Rp ' + Math.round(discNominal).toLocaleString('id-ID'));
                        } else {
                            $(this).find('.item-disc-display').text('- Rp 0');
                        }
                    }

                    $(this).find('.item-disc-persen').val(discPersen);
                    $(this).find('.item-disc-nominal').val(discNominal);

                    let itemSubtotal = Math.max(0, gross - discNominal);

                    // Item PPN
                    let selectedPpnOpt = $(this).find('.select-item-ppn option:selected');
                    let tarifPpn = parseFloat(selectedPpnOpt.data('tarif') !== undefined ? selectedPpnOpt.data('tarif') : selectedPpnOpt.attr('data-tarif')) || 0;
                    $(this).find('.item-tarif-ppn').val(tarifPpn);
                    let itemNilaiPpn = itemSubtotal * (tarifPpn / 100);
                    $(this).find('.item-nilai-ppn').val(itemNilaiPpn);

                    $(this).find('.item-subtotal-display').text('Rp ' + itemSubtotal.toLocaleString('id-ID'));
                    if (tarifPpn > 0) {
                        $(this).find('.item-ppn-display').text('+ Rp ' + Math.round(itemNilaiPpn).toLocaleString('id-ID'));
                    } else {
                        $(this).find('.item-ppn-display').text('Rp 0');
                    }

                    subtotal += itemSubtotal;
                    totalNilaiPpn += itemNilaiPpn;
                    totalHpp += (qty * hppSatuan);
                });

                $('#displaySubtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
                $('#inputSubtotal').val(subtotal);

                // Global Discount removed / 0
                $('#inputDiskonPersen').val(0);
                $('#inputDiskonNominal').val(0);

                // Total PPN
                $('#displayNilaiPpn').text('+ Rp ' + Math.round(totalNilaiPpn).toLocaleString('id-ID'));
                $('#inputNilaiPpn').val(totalNilaiPpn);
                $('#inputTarifPpn').val(subtotal > 0 ? (totalNilaiPpn / subtotal) * 100 : 0);


                let shipping = parseRupiah($('#inputBiayaPengiriman').val());
                let grandTotal = subtotal + totalNilaiPpn + shipping;

                // Grand Total
                $('#displayGrandTotal').text('Rp ' + Math.round(grandTotal).toLocaleString('id-ID'));
                $('#inputGrandTotal').val(grandTotal);

                // Tender Pagu / Nilai Proyek Comparison Indicator
                let tenderPagu = parseFloat($('#selectTender').data('pagu')) || parseFloat($('#selectTender').attr('data-pagu')) || 0;
                if ($('#selectTender').val() && tenderPagu > 0) {
                    $('#tenderPaguInfoBox').removeClass('d-none');
                    $('#displayTenderPagu').text('Rp ' + formatRupiah(tenderPagu));
                    if (grandTotal <= tenderPagu) {
                        let sisa = tenderPagu - grandTotal;
                        $('#badgeBudgetStatus').html(`
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.72rem;" title="Harga penawaran masih dalam batas nilai proyek">
                                <i class="bi bi-shield-check me-1"></i>Sesuai Nilai Proyek (Sisa Rp ${formatRupiah(sisa)})
                            </span>
                        `);
                    } else {
                        let over = grandTotal - tenderPagu;
                        $('#badgeBudgetStatus').html(`
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size: 0.72rem;" title="Harga penawaran melebihi nilai proyek!">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Melebihi Nilai Proyek (+Rp ${formatRupiah(over)})
                            </span>
                        `);
                    }
                } else {
                    $('#tenderPaguInfoBox').addClass('d-none');
                }

                // HPP & Margin Calculation (Gross Profit = Net Product Revenue [DPP] - Total HPP)
                $('#displayTotalHpp').text('Rp ' + Math.round(totalHpp).toLocaleString('id-ID'));
                $('#inputTotalHpp').val(totalHpp);

                let netRevenue = subtotal;
                let labaKotor = netRevenue - totalHpp;
                let labaDisplayEl = $('#displayLabaKotor');
                let marginBadgeEl = $('#displayMarginPersen');

                if (labaKotor < 0) {
                    labaDisplayEl.removeClass('text-success').addClass('text-danger fw-bold').text('- Rp ' + Math.abs(Math.round(labaKotor)).toLocaleString('id-ID') + ' (Rugi)');
                } else {
                    labaDisplayEl.removeClass('text-danger').addClass('text-success fw-bold').text('Rp ' + Math.round(labaKotor).toLocaleString('id-ID'));
                }
                $('#inputLabaKotor').val(labaKotor);

                let margin = netRevenue > 0 ? (labaKotor / netRevenue) * 100 : 0;
                if (margin < 0) {
                    marginBadgeEl.removeClass('badge-soft-emerald').addClass('badge-soft-danger').html('<i class="bi bi-exclamation-triangle me-1"></i>' + margin.toFixed(1) + '% (Minus)');
                } else {
                    marginBadgeEl.removeClass('badge-soft-danger').addClass('badge-soft-emerald').text(margin.toFixed(1) + '%');
                }
                $('#inputMarginPersen').val(margin);

                // Live DP Calculation
                let tipeDp = $('#selectTipeDp').val();
                let dpNominal = 0;
                let dpPersen = 0;
                let roundedGrandTotal = Math.round(grandTotal);

                if (tipeDp === 'Persen') {
                    dpPersen = parseFloat($('#inputDpPersen').val()) || 0;
                    dpNominal = Math.round(roundedGrandTotal * (dpPersen / 100));
                } else if (tipeDp === 'Nominal') {
                    dpNominal = Math.round(parseRupiah($('#inputDpNominal').val())) || 0;
                    dpPersen = roundedGrandTotal > 0 ? ((dpNominal / roundedGrandTotal) * 100).toFixed(1) : 0;
                }

                if (tipeDp !== 'None' && dpNominal > 0) {
                    let sisa = Math.max(0, roundedGrandTotal - dpNominal);
                    $('#displayDpCalculatedNominal').text('Rp ' + dpNominal.toLocaleString('id-ID') + (tipeDp === 'Nominal' && dpPersen > 0 ? ` (${dpPersen}%)` : ''));
                    $('#displayDpSisaPelunasan').text('Rp ' + sisa.toLocaleString('id-ID'));
                    $('#displayDpBreakdown').show();
                } else {
                    $('#displayDpBreakdown').hide();
                }
            }

            // Form Submission Validation Guard & Currency Unmasking
            $('#formQuotation').on('submit', function(e) {
                // Character limit security guards
                let perihalVal = ($('input[name="perihal"]').val() || '').trim();
                if (perihalVal.length > 40) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Validasi',
                        text: 'Perihal / Judul Penawaran maksimal 40 karakter! (Saat ini: ' + perihalVal.length + ' karakter)',
                        confirmButtonColor: '#4f46e5'
                    });
                    $('input[name="perihal"]').focus();
                    return false;
                }

                let catatanVal = ($('textarea[name="catatan"]').val() || '').trim();
                if (catatanVal.length > 60) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Validasi',
                        text: 'Catatan Penawaran maksimal 60 karakter! (Saat ini: ' + catatanVal.length + ' karakter)',
                        confirmButtonColor: '#4f46e5'
                    });
                    $('textarea[name="catatan"]').focus();
                    return false;
                }

                let syaratVal = ($('textarea[name="syarat_ketentuan"]').val() || '').trim();
                if (syaratVal.length > 400) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Validasi',
                        text: 'Syarat & Ketentuan maksimal 400 karakter! (Saat ini: ' + syaratVal.length + ' karakter)',
                        confirmButtonColor: '#4f46e5'
                    });
                    $('textarea[name="syarat_ketentuan"]').focus();
                    return false;
                }

                let rawOngkirDigits = ($('#inputBiayaPengiriman').val() || '').replace(/\D/g, '');
                if (rawOngkirDigits.length > 10) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Validasi',
                        text: 'Biaya Pengiriman maksimal 10 digit angka!',
                        confirmButtonColor: '#4f46e5'
                    });
                    $('#inputBiayaPengiriman').focus();
                    return false;
                }

                if ($('#itemsTableBody tr.item-calc-row').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Item Masih Kosong',
                        text: 'Silakan tambahkan minimal 1 item barang atau jasa sebelum menyimpan penawaran!',
                        confirmButtonColor: '#4f46e5'
                    });
                    return false;
                }

                // Calculate Total HPP & Grand Total
                let totalHpp = parseFloat($('#inputTotalHpp').val()) || 0;
                let grandTotal = parseFloat($('#inputGrandTotal').val()) || 0;
                let labaKotor = parseFloat($('#inputLabaKotor').val()) || 0;
                let margin = parseFloat($('#inputMarginPersen').val()) || 0;

                // Fallback check directly from table rows
                if (totalHpp === 0) {
                    $('.item-calc-row').each(function() {
                        let qty = parseFloat($(this).find('.item-qty').val()) || 0;
                        let hpp = parseFloat($(this).find('.item-hpp-satuan').val()) || 0;
                        totalHpp += (qty * hpp);
                    });
                }

                // Guard: Minus Margin Validation (Block Save & Draft)
                if (totalHpp > 0 && (grandTotal < totalHpp || labaKotor < 0 || margin < 0)) {
                    e.preventDefault();
                    let defisit = Math.abs(labaKotor || (grandTotal - totalHpp));
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="text-danger fw-bold">Peringatan: Margin Negatif!</span>',
                        html: `
                            <div class="text-start fs-8">
                                <div class="alert alert-danger border-0 p-2.5 rounded-3 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0"></i>
                                        <div class="fw-semibold">Penawaran harga ini menghasilkan potensi kerugian operasional dan tidak dapat disimpan (termasuk sebagai Draft).</div>
                                    </div>
                                </div>
                                <div class="bg-body-tertiary p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between mb-1.5">
                                        <span class="text-muted">Total HPP Modal:</span>
                                        <strong class="font-monospace text-body">Rp ${formatRupiah(totalHpp)}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1.5">
                                        <span class="text-muted">Total Penawaran Jual:</span>
                                        <strong class="font-monospace text-body">Rp ${formatRupiah(grandTotal)}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 border-top border-secondary-subtle">
                                        <span class="text-danger fw-bold">Estimasi Defisit / Rugi:</span>
                                        <strong class="text-danger font-monospace fw-bold">- Rp ${formatRupiah(defisit)} (${margin.toFixed(1)}%)</strong>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small"><i class="bi bi-info-circle text-primary me-1"></i>Sesuai kebijakan keuangan perusahaan, harga penawaran tidak boleh berada di bawah total modal pokok (HPP). Silakan sesuaikan kembali <strong>harga jual</strong> atau <strong>potongan diskon</strong>.</p>
                            </div>
                        `,
                        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Tinjau Kembali Harga',
                        confirmButtonColor: '#e11d48',
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                    return false;
                }

                // Strip thousand dots from prices and extra cost before sending
                $('.item-price').each(function() {
                    let rawPrice = parseRupiah($(this).val());
                    $(this).val(rawPrice);
                });

                let rawShipping = parseRupiah($('#inputBiayaPengiriman').val());
                $('#inputBiayaPengiriman').val(rawShipping);

                let rawDp = parseRupiah($('#inputDpNominal').val());
                $('#inputDpNominal').val(rawDp);

                // Mencegah double-submit / spam klik tombol simpan
                $('.btn-submit-quotation').prop('disabled', true).addClass('disabled');
            });

            // Handle submit button click for status assignment (Draft vs Sent)
            $(document).on('click', '.btn-submit-quotation', function() {
                const val = $(this).val();
                if (val) {
                    $('#inputQuotationStatus').val(val);
                }
            });

            // Initial calculation
            calculateTotal();
        });
    </script>
    @endpush

</x-layouts>
