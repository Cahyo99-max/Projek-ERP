<!-- MODAL INPUT TRANSAKSI KAS KECIL (MOBILE-OPTIMIZED) -->
<div class="modal fade" id="modalInputPettyCash" tabindex="-1" aria-labelledby="modalInputPettyCashLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form id="formInputPettyCash" class="modal-content pc-modal-content border-0 shadow-lg rounded-4 overflow-hidden" enctype="multipart/form-data" autocomplete="off">
            @csrf
            
            <!-- Modal Header with Gradient -->
            <div class="modal-header border-0 text-white px-3 px-md-4 py-3 flex-shrink-0" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-white bg-opacity-10 d-flex align-items-center justify-content-center">
                        <i class="bi bi-wallet2 fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalInputPettyCashLabel">Input Kas Kecil</h5>
                        <p class="text-white-50 small mb-0 font-monospace">{{ $activeEntityName }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="modal-body px-3 px-md-4 py-3 pc-modal-body-canvas" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(100vh - 170px);">

                <!-- Alert Panduan Saldo Kas (Dinamis Sesuai Akun Kas yang Dipilih) -->
                @php
                    $firstKasAccount = $coaKasAccounts->first();
                    $initialNamaKas  = $firstKasAccount?->nama_akun ?: ($firstKasAccount?->coa?->name ?? 'Kas Kecil');
                    $initialSaldoKas = $firstKasAccount ? max(0, (float) $firstKasAccount->saldo_berjalan) : 0;
                @endphp
                <div id="bannerAlertSaldoKas" class="alert py-2.5 px-3 small d-flex flex-column flex-sm-row align-items-sm-center justify-content-between rounded-3 mb-3 border-0 {{ $initialSaldoKas <= 0 ? 'bg-danger-subtle text-danger-emphasis alert-danger' : 'bg-info-subtle text-info-emphasis alert-info' }}">
                    <div class="mb-1 mb-sm-0" id="bannerContentSaldoKas">
                        <i class="bi {{ $initialSaldoKas <= 0 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-info-circle-fill text-info' }} me-1" id="bannerIconSaldoKas"></i>
                        <strong id="bannerLabelSaldoKas">{{ $initialSaldoKas <= 0 ? "Saldo {$initialNamaKas} Kosong:" : "Sisa Saldo {$initialNamaKas}:" }}</strong> 
                        <strong id="bannerNominalSaldoKas">{{ $initialSaldoKas <= 0 ? '(Rp 0,00)' : 'Rp ' . number_format($initialSaldoKas, 2, ',', '.') }}</strong> 
                        <span id="bannerKeteranganSaldoKas" class="{{ $initialSaldoKas <= 0 ? '' : 'pc-text-muted-adaptive' }}">
                            {{ $initialSaldoKas <= 0 ? 'Belum dapat mencatat pengeluaran. Harap lakukan Top Up saldo terlebih dahulu.' : '(Khusus pengeluaran operasional).' }}
                        </span>
                    </div>
                    <a href="{{ route('accounting.kas-bank.index') }}" target="_blank" class="btn {{ $initialSaldoKas <= 0 ? 'btn-sm btn-danger text-white fw-bold' : 'btn-xs btn-outline-info' }} rounded-pill px-2.5 py-1 small fw-semibold text-nowrap align-self-start align-self-sm-auto" style="font-size: 11px;" id="bannerLinkTopUpKas">
                        {{ $initialSaldoKas <= 0 ? 'Top Up Saldo' : 'Transfer Kas & Bank' }} <i class="bi {{ $initialSaldoKas <= 0 ? 'bi-arrow-right' : 'bi-box-arrow-up-right' }} ms-1"></i>
                    </a>
                </div>

                <input type="hidden" name="tipe" id="tipe_pengeluaran" value="Pengeluaran">

                <!-- STEP 1: Nominal Transaksi & Quick Nominal Chips -->
                <div class="card pc-bento-card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-uppercase mb-1 pc-text-title-adaptive">1. Nominal Pengeluaran (Rupiah) <span class="text-danger">*</span> <span class="pc-text-muted-adaptive fw-normal ms-1">(Maks. 20 Karakter)</span></label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text fw-bold border-end-0">Rp</span>
                            <input type="text" name="nominal" id="input_nominal" class="form-control form-control-lg fw-bold font-monospace text-primary border-start-0 fs-4" placeholder="0" inputmode="numeric" maxlength="20" required autocomplete="off">
                        </div>
                        
                        <!-- Quick Nominal Chips for fast phone typing -->
                        <div class="d-flex flex-wrap gap-1.5">
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="20000">+20rb</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="50000">+50rb</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="100000">+100rb</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="250000">+250rb</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="500000">+500rb</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-quick-nominal" data-amount="1000000">+1jt</button>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 small btn-clear-nominal" title="Reset Nominal"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Kategori Cepat & Tanggal -->
                <div class="card pc-bento-card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-uppercase mb-1 pc-text-title-adaptive">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="input_tanggal" class="form-control rounded-3 pc-input-adaptive" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-uppercase mb-1 pc-text-title-adaptive d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person me-1 text-info"></i>Nama Pemohon / Pengaju <span class="text-danger">*</span></span>
                                    <span class="pc-text-muted-adaptive fw-normal small" style="font-size: 11px;">Input / Cari Riwayat</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="nama_pengaju" id="input_nama_pengaju" class="form-control rounded-start-3 pc-input-adaptive"
                                           placeholder="Ketik atau cari nama pemohon..." autocomplete="off" required>
                                    <button type="button" class="btn btn-info text-white rounded-end-3 px-3 d-flex align-items-center gap-1.5 shadow-sm"
                                            onclick="bukaModalLookupPemohon('input')" title="Cari Nama dari Riwayat Kas Kecil">
                                        <i class="bi bi-search"></i>
                                        <span class="fw-semibold small">Cari</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase mb-1 pc-text-title-adaptive d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tag-fill me-1 text-warning"></i>Kategori Transaksi <span class="text-danger">*</span></span>
                                    <span class="pc-text-muted-adaptive fw-normal small" style="font-size: 11px;">Cari atau Pilih Kategori</span>
                                </label>
                                <select name="kategori" id="input_kategori" class="form-select rounded-3 select2-kategori-petty" required>
                                    <option value="" selected>-- Cari / Pilih Kategori Kas Kecil --</option>
                                    @if(isset($categoryMapping) && is_array($categoryMapping))
                                        @foreach($categoryMapping as $catKey => $catInfo)
                                            <option value="{{ $catKey }}" 
                                                data-coa-id="{{ $catInfo['coa_id'] ?? '' }}"
                                                data-coa-code="{{ $catInfo['coa_code'] ?? '' }}"
                                                data-coa-name="{{ $catInfo['coa_name'] ?? '' }}"
                                                data-icon="{{ $catInfo['icon'] ?? '' }}"
                                                data-desc="{{ $catInfo['description'] ?? '' }}">
                                                {{ $catInfo['label'] }} @if(!empty($catInfo['coa_code'])) &nbsp;·&nbsp; [{{ $catInfo['coa_code'] }} - {{ $catInfo['coa_name'] }}] @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <small class="pc-text-muted-adaptive" style="font-size: 11px;" id="kategoriDescHint">
                                        <i class="bi bi-info-circle me-1"></i>Otomatis menghubungkan Akun Beban (COA Lawan).
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Koneksi COA & Hubungkan Tender -->
                <div class="card pc-bento-card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-success text-uppercase mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> 4. Integrasi Chart of Accounts (COA) &amp; Tender
                        </label>
                        
                        <div class="row g-3">
                            <!-- Akun Kas Kecil (COA Kas) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold mb-1 pc-text-title-adaptive" id="labelCoaKas">
                                    Akun Kas Kecil <span class="text-danger">*</span>
                                </label>
                                <select name="coa_kas_id" id="input_coa_kas_id" class="form-select rounded-3 pc-input-adaptive" required {{ $coaKasAccounts->isEmpty() ? 'disabled' : '' }}>
                                    @if($coaKasAccounts->isEmpty())
                                        <option value="">-- Tidak Ada Akun Kas (Saldo Kosong) --</option>
                                    @else
                                        @foreach($coaKasAccounts as $kas)
                                            @php
                                                $coaId = $kas->coa_id ?? $kas->coa?->id;
                                                $kodeCoa = $kas->coa?->code ? $kas->coa->code . ' - ' : '';
                                                $namaAkun = $kas->nama_akun ?: ($kas->coa?->name ?? 'Kas');
                                                $saldo = (float) $kas->saldo_berjalan;
                                                $isSelected = ($loop->first);
                                            @endphp
                                            <option value="{{ $coaId }}" data-saldo="{{ $saldo }}" data-nama="{{ $namaAkun }}" data-id="{{ $kas->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $kodeCoa }}{{ $namaAkun }} (Saldo: Rp {{ number_format($saldo, 2, ',', '.') }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if($coaKasAccounts->isEmpty())
                                    <small class="text-warning d-block mt-1" style="font-size: 11px;">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak ada akun Kas Tunai untuk entitas ini. Buat akun kas di menu <a href="{{ route('accounting.kas-bank.index') }}" class="text-warning text-decoration-underline" target="_blank">Kas &amp; Bank</a>.
                                    </small>
                                @else
                                    <small class="pc-text-muted-adaptive d-block mt-1" style="font-size: 11px;">Akun Kas Kecil yang berkurang saat pengeluaran disimpan.</small>
                                @endif
                            </div>

                            <!-- Lawan COA (Biaya/Beban) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold mb-1 pc-text-title-adaptive" id="labelLawanCoa">
                                    Akun Biaya / Beban (Lawan COA) <span class="text-danger">*</span>
                                </label>
                                <select name="lawan_coa_id" id="input_lawan_coa_id" class="form-select rounded-3 pc-input-adaptive" required>
                                    <option value="">-- Pilih Akun Beban / Biaya --</option>
                                    @foreach($coaAccounts as $coa)
                                        <option value="{{ $coa->id }}" data-code="{{ $coa->code }}" {{ str_starts_with((string)$coa->code, '6') || str_starts_with((string)$coa->code, '5') ? 'data-expense="1"' : '' }}>
                                            {{ $coa->code }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="infoCoaAutoMappedInput" class="small text-info mt-1 d-none" style="font-size: 11px;">
                                    <i class="bi bi-magic me-1"></i> Terpilih otomatis via kategori: <strong id="textCoaAutoMappedInput">Beban Perlengkapan &amp; ATK</strong>
                                </div>
                            </div>

                            <!-- Koneksi ke Tender / Proyek (Opsional - Terisolasi Spesifik PT / Cabang Aktif) -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1 d-flex align-items-center justify-content-between pc-text-title-adaptive">
                                    <span><i class="bi bi-briefcase-fill text-warning me-1"></i> Hubungkan ke Tender</span>
                                  
                                </label>
                                <select name="tender_id" id="input_tender_id" class="form-select rounded-3 pc-input-adaptive">
                                    <option value="">-- Tidak Terhubung ke Tender / Umum --</option>
                                    @if(isset($tenders) && $tenders->isNotEmpty())
                                        @foreach($tenders as $t)
                                            @php
                                                $ownerLabel = $t->branch ? ('Cabang ' . $t->branch->nama_cabang) : ($t->company ? $t->company->nama_perusahaan : ($currentCompany->nama_perusahaan ?? 'PT Pusat'));
                                            @endphp
                                            <option value="{{ $t->id }}">
                                                 {{ $t->nomor_kontrak }} - {{ $t->nama_tender }} ({{ $t->status ?? 'Aktif' }}) [{{ $ownerLabel }}]
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>-- Tidak ada tender terdaftar untuk {{ !empty($activeBranchId) ? ('Cabang ' . ($currentBranch->nama_cabang ?? '')) : ($currentCompany->nama_perusahaan ?? 'PT Pusat') }} --</option>
                                    @endif
                                </select>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Keterangan & Kamera / Upload Foto Nota -->
                <div class="card pc-bento-card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase mb-1 pc-text-title-adaptive d-flex justify-content-between align-items-center">
                                <span>Catatan / Keperluan</span>
                                <span class="pc-text-muted-adaptive fw-normal small" style="font-size: 11px;">Maks. 50 Karakter</span>
                            </label>
                            <textarea name="keterangan" id="input_keterangan" rows="2" class="form-control rounded-3 pc-input-adaptive" placeholder="Tuliskan rincian pembelian atau keperluan transaksi..." maxlength="50" autocomplete="off"></textarea>
                        </div>

                        <!-- Upload / Camera Struk (Wajib Diisi) -->
                        <div>
                            <label class="form-label small fw-bold text-uppercase mb-1 d-flex align-items-center gap-1.5 pc-text-title-adaptive">
                                <i class="bi bi-camera-fill text-info"></i> Foto Struk / Bukti Transaksi <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="lampiran_nota" id="input_lampiran_nota" class="form-control rounded-3 pc-input-adaptive" accept="image/*,application/pdf" capture="environment" required>
                            <small class="text-secondary d-block mt-1 fw-semibold" style="font-size: 11px;">
                                <i class="bi bi-asterisk text-danger me-1"></i>Foto / bukti nota pembayaran wajib diunggah (JPG, PNG, atau PDF maks. 10MB).
                                <span class="d-inline-flex align-items-center text-success ms-1"><i class="bi bi-file-earmark-zip me-1"></i>Otomatis dikompres ke ukuran KB</span>
                            </small>
                            
                            <!-- Preview Image Container -->
                            <div id="previewReceiptContainer" class="mt-2 d-none">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-body-tertiary">
                                    <div class="position-relative d-inline-block border rounded-2 overflow-hidden shadow-sm bg-body flex-shrink-0" style="width: 80px; height: 80px;">
                                        <img id="imgReceiptPreview" src="#" alt="Preview Nota" class="w-100 h-100" style="object-fit: cover;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 lh-1" id="btnRemoveReceiptPreview" title="Hapus foto">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="small flex-grow-1 overflow-hidden">
                                        <div class="fw-bold text-truncate pc-text-title-adaptive" id="previewReceiptFileName" style="max-width: 250px;"></div>
                                        <div class="text-muted" id="previewReceiptOriginalSize" style="font-size: 11px;"></div>
                                        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 10px;">
                                            <i class="bi bi-lightning-charge-fill me-1"></i>Otomatis dikompres ke ukuran KB
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer with Touch Friendly Buttons -->
            <div class="modal-footer pc-modal-footer px-3 px-md-4 py-3 flex-shrink-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnSubmitPettyCash" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
                    <i class="bi bi-check2-circle fs-5"></i>
                    <span>Simpan Pengeluaran</span>
                </button>
            </div>
        </form>
    </div>
</div>
