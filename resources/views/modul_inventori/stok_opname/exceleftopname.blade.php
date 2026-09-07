<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        .title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }
        .subtitle {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #64748b;
            text-align: left;
        }
        .meta-label {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            font-weight: bold;
            background-color: #f1f5f9;
            color: #475569;
        }
        .meta-value {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            color: #1e293b;
        }
        .th-header {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .td-body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .td-body-center {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }
        .td-body-right {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            text-align: right;
            vertical-align: middle;
        }
        .summary-header {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }
        .summary-value {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            background-color: #f0fdf4;
            color: #1e293b;
            border: 1px solid #dcfce7;
            text-align: center;
        }
        .selisih-plus {
            color: #16a34a;
            font-weight: bold;
        }
        .selisih-minus {
            color: #dc2626;
            font-weight: bold;
        }
        .selisih-zero {
            color: #64748b;
        }
        .catatan {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            color: #64748b;
            font-style: italic;
            border: 1px solid #cbd5e1;
        }
    </style>
@php
    $activeBranchId  = session('active_branch_id');
    $activeCompanyId = session('active_company_id');
    $activeBranch    = $activeBranchId ? \App\Models\Branch::find($activeBranchId) : null;
    $activeCompany   = $activeCompanyId ? \App\Models\Company::find($activeCompanyId) : null;
    $entityName      = $activeBranch 
        ? $activeBranch->nama_cabang 
        : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', session('active_branch_name', 'ERP SYSTEM')));
@endphp
<body>
    <table>
        <tr>
            <td colspan="8" class="title">{{ $entityName }}</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">Laporan Hasil Stock Opname Barang</td>
        </tr>
        <tr>
            <td colspan="8"></td>
        </tr>
        
        <!-- Metadata Info -->
        <tr>
            <td class="meta-label">Nomor Opname:</td>
            <td class="meta-value" colspan="3">{{ $stockOpname->nomor_opname }}</td>
            <td class="meta-label">Lokasi Gudang:</td>
            <td class="meta-value" colspan="3">{{ $stockOpname->gudang->nama_gudang ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Dibuat:</td>
            <td class="meta-value" colspan="3">{{ \Carbon\Carbon::parse($stockOpname->tanggal_opname)->format('d/m/Y') }}</td>
            <td class="meta-label">Status Alur:</td>
            <td class="meta-value" colspan="3">{{ $stockOpname->status }}</td>
        </tr>
        <tr>
            <td class="meta-label">Rencana Periode:</td>
            <td class="meta-value" colspan="3">{{ \Carbon\Carbon::parse($stockOpname->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($stockOpname->tanggal_selesai)->format('d/m/Y') }}</td>
            <td class="meta-label">Pembuat:</td>
            <td class="meta-value" colspan="3">{{ $stockOpname->creator?->nama_lengkap ?? '-' }}</td>
        </tr>
        @if(!empty($stockOpname->keterangan))
        <tr>
            <td class="meta-label">Keterangan:</td>
            <td class="meta-value" colspan="7">{{ $stockOpname->keterangan }}</td>
        </tr>
        @endif
        
        <tr>
            <td colspan="8"></td>
        </tr>

        <!-- Ringkasan Stock Opname -->
        @if($stockOpname->status === 'Menunggu Closing' || $stockOpname->status === 'Closed')
        <tr>
            <td colspan="{{ $canSeeFinancials ? 8 : 7 }}" class="summary-header" style="font-size: 11pt; padding: 5px;">Ringkasan Hasil Stock Opname</td>
        </tr>
        <tr>
            <td class="summary-header">Total Item:</td>
            <td class="summary-value">{{ $countTotal }}</td>
            <td class="summary-header">Dihitung:</td>
            <td class="summary-value" style="color: #16a34a;">{{ $countDihitung }}</td>
            <td class="summary-header">Belum Dihitung:</td>
            <td class="summary-value" style="color: #d97706;">{{ $countBelum }}</td>
            <td colspan="{{ $canSeeFinancials ? 2 : 1 }}"></td>
        </tr>
        @if($canSeeFinancials)
        <tr>
            <td class="summary-header">Selisih Lebih (+):</td>
            <td class="summary-value" style="color: #16a34a;">Rp {{ number_format($selisihLebihVal, 0, ',', '.') }}</td>
            <td class="summary-header">Selisih Kurang (-):</td>
            <td class="summary-value" style="color: #dc2626;">Rp {{ number_format($selisihKurangVal, 0, ',', '.') }}</td>
            <td class="summary-header">Net Selisih:</td>
            <td class="summary-value" style="color: {{ $nilaiSelisihTotal >= 0 ? '#16a34a' : '#dc2626' }};">
                {{ $nilaiSelisihTotal > 0 ? '+' : '' }}Rp {{ number_format($nilaiSelisihTotal, 0, ',', '.') }}
            </td>
            <td colspan="2"></td>
        </tr>
        @endif
        <tr>
            <td colspan="{{ $canSeeFinancials ? 8 : 7 }}"></td>
        </tr>
        @endif

        <!-- Details Table -->
        <thead>
            <tr>
                <th class="th-header" style="width: 50px;">No</th>
                <th class="th-header" style="width: 150px;">Kode Barang</th>
                <th class="th-header" style="width: 250px;">Nama Barang</th>
                <th class="th-header" style="width: 120px;">Stok Sistem</th>
                <th class="th-header" style="width: 120px;">Stok Fisik</th>
                <th class="th-header" style="width: 100px;">Selisih Qty</th>
                @if($canSeeFinancials)
                <th class="th-header" style="width: 150px;">Nilai Selisih</th>
                @endif
                <th class="th-header" style="width: 200px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $d)
                @php
                    $diffClass = 'selisih-zero';
                    $diffSign = '';
                    if ($d['selisih'] > 0) {
                        $diffClass = 'selisih-plus';
                        $diffSign = '+';
                    } elseif ($d['selisih'] < 0) {
                        $diffClass = 'selisih-minus';
                    }

                    $fmtQty = function($val) {
                        if (is_null($val)) return '—';
                        $floatVal = floatval($val);
                        return ($floatVal == floor($floatVal)) 
                            ? number_format($floatVal, 0, ',', '.') 
                            : rtrim(rtrim(number_format($floatVal, 2, ',', '.'), '0'), ',');
                    };
                @endphp
                <tr>
                    <td class="td-body-center">{{ $index + 1 }}</td>
                    <td class="td-body" style="font-weight: bold;">{{ $d['barang_kode'] }}</td>
                    <td class="td-body">{{ $d['barang_nama'] }}</td>
                    <td class="td-body-center">{{ $fmtQty($d['stok_sistem']) }} {{ $d['satuan'] }}</td>
                    <td class="td-body-center" style="font-weight: bold;">
                        {{ $d['stok_fisik'] !== null ? $fmtQty($d['stok_fisik']) . ' ' . $d['satuan'] : '—' }}
                    </td>
                    <td class="td-body-center {{ $diffClass }}">
                        {{ $d['selisih'] !== null ? $diffSign . $fmtQty($d['selisih']) : '—' }}
                    </td>
                    @if($canSeeFinancials)
                    <td class="td-body-right {{ $diffClass }}">
                        {{ $d['selisih'] !== null ? 'Rp ' . number_format($d['nilai_selisih'], 0, ',', '.') : '—' }}
                    </td>
                    @endif
                    <td class="catatan">{{ $d['catatan'] ? $d['catatan'] : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
