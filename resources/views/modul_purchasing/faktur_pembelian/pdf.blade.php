<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Pembelian - {{ $purchaseInvoice->nomor_faktur }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 20px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #2563eb; letter-spacing: -0.5px; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 18px; color: #0f172a; text-transform: uppercase; }
        .doc-title p { margin: 2px 0 0; color: #64748b; font-size: 12px; }
        .grid { display: flex; gap: 20px; margin-bottom: 20px; }
        .col { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .col h4 { margin: 0 0 8px; font-size: 12px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; }
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { width: 300px; margin-left: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .summary-row.total { font-weight: bold; font-size: 15px; color: #0f172a; border-top: 2px solid #cbd5e1; padding-top: 8px; margin-top: 4px; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; text-align: center; font-size: 11px; color: #64748b; }
        .sig-box { width: 150px; }
        .sig-space { height: 50px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ Cetak Dokumen</button>
    </div>

    <div class="header">
        <div>
            <div class="logo">ENTERPRISE ERP</div>
            <div style="color: #64748b; font-size: 11px;">{{ $purchaseInvoice->branch?->nama_cabang ?? 'Kantor Pusat' }}</div>
        </div>
        <div class="doc-title">
            <h2>Faktur Pembelian</h2>
            <p><strong>{{ $purchaseInvoice->nomor_faktur }}</strong></p>
        </div>
    </div>

    <div class="grid">
        <div class="col">
            <h4>Pemasok / Supplier</h4>
            <div style="font-weight: bold; font-size: 14px; color: #0f172a;">{{ $purchaseInvoice->supplier?->nama_supplier ?? '-' }}</div>
            <div>NPWP: {{ $purchaseInvoice->supplier?->npwp ?? '-' }}</div>
            <div>Alamat: {{ $purchaseInvoice->supplier?->alamat ?? '-' }}</div>
        </div>
        <div class="col">
            <h4>Detail Dokumen</h4>
            <div>No. Faktur Supplier: <strong>{{ $purchaseInvoice->nomor_faktur_supplier ?? '-' }}</strong></div>
            <div>Tgl. Invoice: <strong>{{ $purchaseInvoice->tanggal_faktur ? $purchaseInvoice->tanggal_faktur->format('d/m/Y') : '-' }}</strong></div>
            <div>Tgl. Jatuh Tempo: <strong>{{ $purchaseInvoice->tanggal_jatuh_tempo ? $purchaseInvoice->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}</strong></div>
            <div>Ref. PO / GR: <strong>{{ $purchaseInvoice->po?->nomor_po ?? '-' }} / {{ $purchaseInvoice->goodsReceipt?->nomor_gr ?? '-' }}</strong></div>
            <div>Status: <span style="font-weight: bold; color: #2563eb;">{{ $purchaseInvoice->status }}</span></div>
        </div>
    </div>

    @php
        $currencyCode = $purchaseInvoice->currency?->kode_currency ?? ($purchaseInvoice->po?->currency?->kode_currency ?? 'IDR');
        $currencySymbol = $purchaseInvoice->currency?->simbol ?? ($purchaseInvoice->po?->currency?->simbol ?? ($currencyCode === 'USD' ? '$' : 'Rp'));
        $isUsd = ($currencyCode === 'USD');

        $fmt = function($num) use ($isUsd, $currencySymbol) {
            $val = floatval($num ?? 0);
            if ($isUsd) {
                return $currencySymbol . ' ' . number_format($val, 2, '.', ',');
            } else {
                return $currencySymbol . ' ' . number_format($val, 2, ',', '.');
            }
        };
    @endphp

    <table class="info-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Deskripsi / Barang</th>
                <th class="text-center" width="10%">Qty</th>
                <th class="text-right" width="20%">Harga Satuan ({{ $currencyCode }})</th>
                <th class="text-right" width="25%">Subtotal ({{ $currencyCode }})</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($purchaseInvoice->details as $det)
            <tr>
                <td>{{ $no++ }}</td>
                <td>
                    <strong>{{ $det->keterangan ?? $det->barang?->nama_barang }}</strong>
                    <div style="font-size: 11px; color: #64748b;">Jenis: {{ $det->jenis }} {{ $det->masuk_hpp ? '(Masuk HPP)' : '' }}</div>
                </td>
                <td class="text-center">{{ floatval($det->jumlah) > 0 ? floatval($det->jumlah) : '-' }}</td>
                <td class="text-right">{{ $fmt($det->harga_satuan) }}</td>
                <td class="text-right" style="font-weight: bold;">{{ $fmt($det->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <span>Subtotal Barang:</span>
            <span>{{ $fmt($purchaseInvoice->subtotal) }}</span>
        </div>
        @if(floatval($purchaseInvoice->diskon_nominal) > 0)
        <div class="summary-row" style="color: #dc2626;">
            <span>Total Diskon:</span>
            <span>- {{ $fmt($purchaseInvoice->diskon_nominal) }}</span>
        </div>
        @endif
        <div class="summary-row">
            <span>Biaya Lain-lain:</span>
            <span>{{ $fmt($purchaseInvoice->biaya_lain) }}</span>
        </div>
        <div class="summary-row">
            <span>Pajak (PPN):</span>
            <span>{{ $fmt($purchaseInvoice->pajak_nominal) }}</span>
        </div>
        <div class="summary-row total">
            <span>Grand Total:</span>
            <span>{{ $fmt($purchaseInvoice->grand_total) }}</span>
        </div>
        @if($currencyCode !== 'IDR' && floatval($purchaseInvoice->kurs) > 1)
        <div class="summary-row" style="font-size: 11px; color: #64748b; margin-top: 4px;">
            <span>Ekuivalen IDR (Kurs {{ (floatval($purchaseInvoice->kurs) == intval($purchaseInvoice->kurs)) ? number_format($purchaseInvoice->kurs, 0, ',', '.') : rtrim(rtrim(number_format($purchaseInvoice->kurs, 4, ',', '.'), '0'), ',') }}):</span>
            <span>Rp {{ number_format($purchaseInvoice->grand_total * $purchaseInvoice->kurs, 2, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <div class="sig-box">
            <div>Dibuat Oleh,</div>
            <div class="sig-space"></div>
            <div>( {{ $purchaseInvoice->creator?->name ?? 'Admin' }} )</div>
        </div>
        <div class="sig-box">
            <div>Diperiksa Oleh,</div>
            <div class="sig-space"></div>
            <div>( Finance Manager )</div>
        </div>
        <div class="sig-box">
            <div>Disetujui Oleh,</div>
            <div class="sig-space"></div>
            <div>( Direktur / General Manager )</div>
        </div>
    </div>
</body>
</html>
