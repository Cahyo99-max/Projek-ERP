<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-dark">Account Mapping System</h5>
        <p class="text-muted small m-0">Pemetaan terpusat akun bawaan untuk persediaan, HPP, utang/piutang, ongkir, pajak, dan kasir.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm px-4 rounded-3 shadow-sm" id="btnSaveSystemMapping">
            <i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan
        </button>
    </div>
</div>

<form id="formSystemMapping" autocomplete="off">
    @csrf
    @php
        $defaultKeys = [
            'default_inventory' => ['label' => 'Default Persediaan Barang', 'desc' => 'Akun persediaan bawaan untuk transaksi Gudang & Purchasing'],
            'default_cogs' => ['label' => 'Default HPP (Cost of Goods Sold)', 'desc' => 'Akun modal pokok bawaan saat penjualan/pengeluaran barang'],
            'default_sales' => ['label' => 'Default Penjualan (Revenue)', 'desc' => 'Akun pendapatan bawaan penjualan produk'],
            'default_ap' => ['label' => 'Default Utang Dagang (Accounts Payable)', 'desc' => 'Akun kewajiban utang ke supplier'],
            'default_ar' => ['label' => 'Default Piutang Usaha (Accounts Receivable)', 'desc' => 'Akun piutang tagihan customer'],
            'default_gr_clearing' => ['label' => 'Default GR/IR Clearing (Goods Receipt)', 'desc' => 'Akun penampung penerimaan barang sebelum invoice terbit'],
            'default_stock_adjustment' => ['label' => 'Default Selisih Stok (Stock Opname)', 'desc' => 'Akun penyesuaian selisih stok opname'],
            'default_opening_balance' => ['label' => 'Default Ekuitas Saldo Awal (Modal)', 'desc' => 'Akun ekuitas/modal kredit saat konfirmasi Saldo Awal Stok'],
        ];

        $freightKeys = [
            'freight_in' => ['label' => 'Biaya Angkut Pembelian (Freight-In)', 'desc' => 'Ongkir beli yang menambah HPP barang'],
            'freight_out' => ['label' => 'Beban Pengiriman Penjualan (Freight-Out)', 'desc' => 'Beban ongkir kirim ke customer'],
        ];
    @endphp

    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sliders me-1"></i> 1. Default System Accounts</h6>
    <div class="row g-3 mb-4">
        @foreach($defaultKeys as $key => $meta)
            @php $sel = $mappings->get("default.{$key}")->coa_id ?? null; @endphp
            <div class="col-md-6">
                <div class="mapping-box">
                    <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                    <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>
                    <input type="hidden" name="mappings[{{ $key }}][group]" value="default">
                    <input type="hidden" name="mappings[{{ $key }}][mapping_key]" value="{{ $key }}">
                    <input type="hidden" name="mappings[{{ $key }}][description]" value="{{ $meta['label'] }}">
                    <select class="form-select form-select-sm rounded-3" name="mappings[{{ $key }}][coa_id]">
                        <option value="">-- Pilih Akun COA --</option>
                        @foreach($detailCoas as $coa)
                            <option value="{{ $coa->id }}" {{ $sel == $coa->id ? 'selected' : '' }}>
                                {{ $coa->code }} - {{ $coa->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>

    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-truck me-1"></i> 2. Ongkos Kirim & Pengiriman</h6>
    <div class="row g-3 mb-4">
        @foreach($freightKeys as $key => $meta)
            @php $sel = $mappings->get("freight.{$key}")->coa_id ?? null; @endphp
            <div class="col-md-6">
                <div class="mapping-box">
                    <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                    <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>
                    <input type="hidden" name="mappings[freight_{{ $key }}][group]" value="freight">
                    <input type="hidden" name="mappings[freight_{{ $key }}][mapping_key]" value="{{ $key }}">
                    <input type="hidden" name="mappings[freight_{{ $key }}][description]" value="{{ $meta['label'] }}">
                    <select class="form-select form-select-sm rounded-3" name="mappings[freight_{{ $key }}][coa_id]">
                        <option value="">-- Pilih Akun COA --</option>
                        @foreach($detailCoas as $coa)
                            <option value="{{ $coa->id }}" {{ $sel == $coa->id ? 'selected' : '' }}>
                                {{ $coa->code }} - {{ $coa->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>

    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-receipt me-1"></i> 3. Pemetaan Akun Pajak (PPN/PPh)</h6>
    <div class="table-responsive border rounded-3 mb-4">
        <table class="table table-akuntansi align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama Pajak</th>
                    <th>Tarif (%)</th>
                    <th style="width: 40%;">PPN Masukan (Pembelian)</th>
                    <th style="width: 40%;">PPN Keluaran (Penjualan)</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($taxes as $tax)
                    @php
                        $keyIn = "tax_{$tax->id}_in";
                        $keyOut = "tax_{$tax->id}_out";
                        $selIn = $mappings->get("tax.{$keyIn}")->coa_id ?? null;
                        $selOut = $mappings->get("tax.{$keyOut}")->coa_id ?? null;
                    @endphp
                    <tr>
                        <td class="fw-bold text-dark">{{ $tax->nama_pajak }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">{{ $tax->tarif }}%</span></td>
                        <td>
                            <input type="hidden" name="mappings[{{ $keyIn }}][group]" value="tax">
                            <input type="hidden" name="mappings[{{ $keyIn }}][mapping_key]" value="{{ $keyIn }}">
                            <select class="form-select form-select-sm rounded-2" name="mappings[{{ $keyIn }}][coa_id]">
                                <option value="">-- Pilih Akun PPN Masukan --</option>
                                @foreach($detailCoas as $coa)
                                    <option value="{{ $coa->id }}" {{ $selIn == $coa->id ? 'selected' : '' }}>{{ $coa->code }} - {{ $coa->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="mappings[{{ $keyOut }}][group]" value="tax">
                            <input type="hidden" name="mappings[{{ $keyOut }}][mapping_key]" value="{{ $keyOut }}">
                            <select class="form-select form-select-sm rounded-2" name="mappings[{{ $keyOut }}][coa_id]">
                                <option value="">-- Pilih Akun PPN Keluaran --</option>
                                @foreach($detailCoas as $coa)
                                    <option value="{{ $coa->id }}" {{ $selOut == $coa->id ? 'selected' : '' }}>{{ $coa->code }} - {{ $coa->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada Master Pajak.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-wallet2 me-1"></i> 4. Metode Pembayaran (Kas & Bank)</h6>
    <div class="table-responsive border rounded-3 mb-4">
        <table class="table table-akuntansi align-middle mb-0">
            <thead>
                <tr>
                    <th>Metode Pembayaran</th>
                    <th>Kode</th>
                    <th style="width: 60%;">Akun Kas / Rekening Bank COA</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($paymentMethods as $pm)
                    @php
                        $keyPm = "pm_{$pm->id}";
                        $selPm = $mappings->get("payment_method.{$keyPm}")->coa_id ?? null;
                    @endphp
                    <tr>
                        <td class="fw-bold text-dark">{{ $pm->nama_metode }}</td>
                        <td><span class="badge bg-light text-dark border font-monospace rounded-pill">{{ $pm->kode_metode }}</span></td>
                        <td>
                            <input type="hidden" name="mappings[{{ $keyPm }}][group]" value="payment_method">
                            <input type="hidden" name="mappings[{{ $keyPm }}][mapping_key]" value="{{ $keyPm }}">
                            <select class="form-select form-select-sm rounded-2" name="mappings[{{ $keyPm }}][coa_id]">
                                <option value="">-- Pilih Akun Kas / Bank --</option>
                                @foreach($detailCoas as $coa)
                                    <option value="{{ $coa->id }}" {{ $selPm == $coa->id ? 'selected' : '' }}>{{ $coa->code }} - {{ $coa->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada Metode Pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-arrow-left-right me-1"></i> 5. Transfer Gudang Inter-PT (Intercompany Transfer)</h6>
    <p class="text-muted small mb-3">Pemetaan akun COA bawaan saat melakukan Transfer Gudang Antar PT. Akun ini akan otomatis terisi pada form pembuatan Transfer Gudang Inter-PT.</p>
    @php
        $wtMappingKeys = [
            'intercompany_debit' => [
                'label' => 'Akun Debit Default Intercompany (Piutang Intercompany / Biaya)',
                'desc'  => 'Akun debit bawaan untuk transfer barang antar PT.',
                'group' => 'warehouse_transfer',
                'placeholder' => '-- Pilih Akun Debit Default --',
            ],
            'intercompany_kredit' => [
                'label' => 'Akun Kredit Default Intercompany (Persediaan / Utang Intercompany)',
                'desc'  => 'Akun kredit bawaan untuk transfer barang antar PT.',
                'group' => 'warehouse_transfer',
                'placeholder' => '-- Pilih Akun Kredit Default --',
            ],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($wtMappingKeys as $key => $meta)
            @php $sel = $mappings->get("{$meta['group']}.{$key}")->coa_id ?? null; @endphp
            <div class="col-md-6">
                <div class="mapping-box">
                    <label class="form-label fw-bold mb-1 text-dark">{{ $meta['label'] }}</label>
                    <p class="text-muted small mb-2">{{ $meta['desc'] }}</p>
                    <input type="hidden" name="mappings[wt_{{ $key }}][group]" value="{{ $meta['group'] }}">
                    <input type="hidden" name="mappings[wt_{{ $key }}][mapping_key]" value="{{ $key }}">
                    <input type="hidden" name="mappings[wt_{{ $key }}][description]" value="{{ $meta['label'] }}">
                    <select class="form-select form-select-sm rounded-3" name="mappings[wt_{{ $key }}][coa_id]">
                        <option value="">{{ $meta['placeholder'] }}</option>
                        @foreach($detailCoas as $coa)
                            <option value="{{ $coa->id }}" {{ $sel == $coa->id ? 'selected' : '' }}>
                                {{ $coa->code }} - {{ $coa->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>

</form>
