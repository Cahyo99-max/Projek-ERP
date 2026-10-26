<!-- MODAL EDIT TRANSAKSI KAS KECIL -->
<div class="modal fade" id="modalEditPettyCash" tabindex="-1" aria-labelledby="modalEditPettyCashLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form id="formEditPettyCash" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" id="edit_pc_id">

            <!-- Modal Header -->
            <div class="modal-header border-0 text-white px-3 px-md-4 py-3 flex-shrink-0" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-white bg-opacity-10 d-flex align-items-center justify-content-center">
                        <i class="bi bi-pencil-square fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalEditPettyCashLabel">Edit Transaksi Kas Kecil</h5>
                        <p class="text-white-50 small mb-0 font-monospace" id="edit_pc_nomor_display">PC-xxxx-xxxx</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="modal-body px-3 px-md-4 py-3 bg-body-tertiary" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(100vh - 170px);">

                <input type="hidden" name="tipe" id="edit_tipe_pengeluaran" value="Pengeluaran">

                <!-- Nominal -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-uppercase mb-1 text-light d-flex justify-content-between align-items-center">
                            <span>Nominal Pengeluaran (Rupiah) <span class="text-danger">*</span></span>
                            <span class="text-white-50 fw-normal small" style="font-size: 11px;">Maks. 20 Karakter</span>
                        </label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text fw-bold border-end-0">Rp</span>
                            <input type="text" name="nominal" id="edit_input_nominal" class="form-control form-control-lg fw-bold font-monospace text-primary border-start-0 fs-4" placeholder="0" inputmode="numeric" maxlength="20" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Rincian Transaksi & Tanggal -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-uppercase mb-1 text-light">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="edit_input_tanggal" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-uppercase mb-1 text-light d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person me-1 text-info"></i>Nama Pemohon / Pengaju <span class="text-danger">*</span></span>
                                    <span class="text-white-50 fw-normal small" style="font-size: 11px;">Input / Cari Riwayat</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="nama_pengaju" id="edit_input_nama_pengaju" class="form-control rounded-start-3 text-white border-secondary"
                                           placeholder="Ketik atau cari nama pemohon..." autocomplete="off" required style="background-color: #111c2e;">
                                    <button type="button" class="btn btn-info text-white rounded-end-3 px-3 d-flex align-items-center gap-1.5 shadow-sm"
                                            onclick="bukaModalLookupPemohon('edit')" title="Cari Nama dari Riwayat Kas Kecil">
                                        <i class="bi bi-search"></i>
                                        <span class="fw-semibold small">Cari</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase mb-1 text-light d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tag-fill me-1 text-warning"></i>Kategori Transaksi <span class="text-danger">*</span></span>
                                    <span class="text-white-50 fw-normal small" style="font-size: 11px;">Cari atau Pilih Kategori</span>
                                </label>
                                <select name="kategori" id="edit_input_kategori" class="form-select rounded-3 select2-kategori-petty-edit" required>
                                    <option value="">-- Cari / Pilih Kategori Kas Kecil --</option>
                                    @if(isset($categoryMapping) && is_array($categoryMapping))
                                        @foreach($categoryMapping as $catKey => $catInfo)
                                            <option value="{{ $catKey }}" 
                                                data-coa-id="{{ $catInfo['coa_id'] ?? '' }}"
                                                data-coa-code="{{ $catInfo['coa_code'] ?? '' }}"
                                                data-coa-name="{{ $catInfo['coa_name'] ?? '' }}"
                                                data-desc="{{ $catInfo['description'] ?? '' }}">
                                                {{ $catInfo['label'] }} @if(!empty($catInfo['coa_code'])) &nbsp;·&nbsp; [{{ $catInfo['coa_code'] }} - {{ $catInfo['coa_name'] }}] @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <small class="text-white-50" style="font-size: 11px;" id="editKategoriDescHint">
                                        <i class="bi bi-info-circle me-1"></i>Otomatis menghubungkan Akun Beban (COA Lawan).
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COA & Tender -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-success text-uppercase mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> Integrasi COA &amp; Tender
                        </label>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold mb-1 text-light">Akun Kas Kecil <span class="text-danger">*</span></label>
                                <select name="coa_kas_id" id="edit_input_coa_kas_id" class="form-select rounded-3" required {{ $coaKasAccounts->isEmpty() ? 'disabled' : '' }}>
                                    @if($coaKasAccounts->isEmpty())
                                        <option value="">-- Tidak Ada Akun Kas (Saldo Kosong) --</option>
                                    @else
                                        <option value="">-- Pilih Akun Kas --</option>
                                        @foreach($coaKasAccounts as $kas)
                                            @php
                                                $coaId = $kas->coa_id ?? $kas->coa?->id;
                                                $kodeCoa = $kas->coa?->code ? $kas->coa->code . ' - ' : '';
                                                $namaAkun = $kas->nama_akun ?: ($kas->coa?->name ?? 'Kas Kecil');
                                                $saldo = (float) $kas->saldo_berjalan;
                                            @endphp
                                            <option value="{{ $coaId }}" data-saldo="{{ $saldo }}">
                                                {{ $kodeCoa }}{{ $namaAkun }} (Saldo: Rp {{ number_format($saldo, 2, ',', '.') }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold mb-1 text-light">Akun Lawan (Biaya / Beban) <span class="text-danger">*</span></label>
                                <select name="lawan_coa_id" id="edit_input_lawan_coa_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Akun Beban / Biaya --</option>
                                    @foreach($coaAccounts as $coa)
                                        <option value="{{ $coa->id }}" data-code="{{ $coa->code }}">{{ $coa->code }} - {{ $coa->name }}</option>
                                    @endforeach
                                </select>
                                <div id="infoCoaAutoMappedEdit" class="small text-info mt-1 d-none" style="font-size: 11px;">
                                    <i class="bi bi-magic me-1"></i> Terpilih otomatis via kategori: <strong id="textCoaAutoMappedEdit"></strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1 text-light d-flex justify-content-between align-items-center">
                                    <span>Hubungkan ke Tender / Proyek (Opsional)</span>
                                    @if(!empty($activeBranchId))
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 10px;">
                                            <i class="bi bi-shop me-1"></i> Cabang {{ $currentBranch->nama_cabang ?? 'Aktif' }}
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-info border border-primary-subtle" style="font-size: 10px;">
                                            <i class="bi bi-building me-1"></i> {{ $currentCompany->nama_perusahaan ?? 'PT Pusat' }}
                                        </span>
                                    @endif
                                </label>
                                <select name="tender_id" id="edit_input_tender_id" class="form-select rounded-3">
                                    <option value="">-- Tidak Terhubung ke Tender --</option>
                                    @if(isset($tenders) && $tenders->isNotEmpty())
                                        @foreach($tenders as $t)
                                            @php
                                                $ownerLabel = $t->branch ? ('Cabang ' . $t->branch->nama_cabang) : ($t->company ? $t->company->nama_perusahaan : ($currentCompany->nama_perusahaan ?? 'PT Pusat'));
                                            @endphp
                                            <option value="{{ $t->id }}">{{ $t->nomor_kontrak }} - {{ $t->nama_tender }} ({{ $t->status ?? 'Aktif' }}) [{{ $ownerLabel }}]</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan & Lampiran Nota -->
                <div class="card border-0 shadow-sm rounded-3 bg-body">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase mb-1 text-light d-flex justify-content-between align-items-center">
                                <span>Catatan / Keperluan</span>
                                <span class="text-white-50 fw-normal small" style="font-size: 11px;">Maks. 50 Karakter</span>
                            </label>
                            <textarea name="keterangan" id="edit_input_keterangan" rows="2" class="form-control rounded-3" maxlength="50" autocomplete="off"></textarea>
                        </div>
                        <div>
                            <label class="form-label small fw-bold text-uppercase mb-1 d-flex align-items-center gap-1.5 text-light">
                                <i class="bi bi-camera-fill text-info"></i> Ganti Foto Struk / Nota (Opsional)
                            </label>
                            <input type="file" name="lampiran_nota" id="edit_input_lampiran_nota" class="form-control rounded-3" accept="image/*,application/pdf" capture="environment">
                            <div id="edit_existingReceiptContainer" class="mt-2 d-none">
                                <p class="small text-white-50 mb-1">Nota Saat Ini:</p>
                                <a id="edit_linkExistingReceipt" href="#" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1">
                                    <i class="bi bi-file-earmark-image"></i> Lihat Nota Tersimpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-3 px-md-4 py-3 bg-body flex-shrink-0">
                <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnUpdatePettyCash">
                    <i class="bi bi-check-lg fs-5"></i>
                    <span>Update Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>
