<div class="modal-header border-bottom py-3 px-4 bg-body-secondary">
    <div class="d-flex align-items-center gap-2">
        <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
            <i class="bi bi-receipt-cutoff fs-5"></i>
        </span>
        <div>
            <h5 class="modal-title fw-bold text-body-emphasis mb-0">Detail Faktur Penjualan</h5>
            <p class="text-body-secondary small mb-0 font-monospace">No. Faktur: <strong class="text-primary">{{ $invoice->nomor_faktur ?? '-' }}</strong></p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <div class="printable-invoice">
        @if($invoice->status === 'Rejected' && !empty($invoice->alasan_penolakan))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-octagon-fill fs-4 mt-0.5 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1">Pengajuan Faktur Penjualan Ditolak</h6>
                <p class="mb-1 small"><strong>Alasan Penolakan:</strong> {{ $invoice->alasan_penolakan }}</p>
                @if($invoice->approver)
                <small class="text-body-secondary d-block">Ditolak oleh: <strong>{{ $invoice->approver->nama_lengkap }}</strong> pada {{ $invoice->approved_at ? $invoice->approved_at->format('d/m/Y H:i') : '' }} WIB</small>
                @endif
            </div>
        </div>
        @endif

        @if($invoice->status === 'Menunggu Persetujuan')
        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history fs-4 text-warning-emphasis"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-warning-emphasis">Menunggu Persetujuan (Approval)</h6>
                    <small class="text-secondary">Faktur ini telah diajukan dan sedang menunggu review oleh atasan/finance.</small>
                </div>
            </div>
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('approve-sales-invoice'))
            <span class="badge bg-warning text-dark font-monospace px-3 py-1.5 rounded-pill">Anda Memiliki Akses Approval</span>
            @endif
        </div>
        @endif

        <!-- Invoice Header Info Card -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 h-100">
                    <span class="small fw-semibold text-body-secondary text-uppercase font-monospace d-block mb-1">Informasi Customer</span>
                    @php
                        $detInstansi = trim($invoice->customer?->nama_perusahaan ?? '');
                        $detPerson   = trim($invoice->customer?->nama_customer ?? '');
                    @endphp
                    @if($detInstansi && $detPerson && strcasecmp($detInstansi, $detPerson) !== 0)
                        <h6 class="fw-bold text-body-emphasis mb-0.5">{{ $detInstansi }}</h6>
                        <div class="small text-body-secondary font-monospace mb-1.5"><i class="bi bi-person me-1"></i>{{ $detPerson }}</div>
                    @else
                        <h6 class="fw-bold text-body-emphasis mb-1">{{ $detInstansi ?: ($detPerson ?: '-') }}</h6>
                    @endif
                    <p class="small text-body-secondary mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $invoice->customer?->alamat ?? 'Alamat tidak tersedia' }}</p>
                    <p class="small text-body-secondary mb-0"><i class="bi bi-telephone me-1"></i>{{ $invoice->customer?->telepon ?? '-' }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 h-100">
                    <span class="small fw-semibold text-body-secondary text-uppercase font-monospace d-block mb-1">Detail Transaksi</span>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Tanggal Faktur:</span>
                        <span class="fw-bold font-monospace text-body-emphasis">{{ $invoice->tanggal_faktur ? $invoice->tanggal_faktur->format('d F Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Jatuh Tempo:</span>
                        <span class="fw-bold font-monospace text-danger">{{ $invoice->tanggal_jatuh_tempo ? $invoice->tanggal_jatuh_tempo->format('d F Y') : '-' }}</span>
                    </div>
                    @php
                        $relatedDos = $invoice->getRelatedDeliveryOrders();
                        $approvedDoStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered'];
                    @endphp
                    @if($relatedDos->isNotEmpty())
                    <div class="d-flex justify-content-between small mb-1 align-items-start">
                        <span class="text-body-secondary text-nowrap me-2">Surat Jalan (DO):</span>
                        <span class="fw-bold font-monospace text-primary text-end">
                            <i class="bi bi-truck me-1"></i>
                            @foreach($relatedDos as $dIndex => $rDo)
                                @php
                                    $rDoIsAcc = in_array($rDo->status, $approvedDoStatuses);
                                @endphp
                                <span class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ url('modul-marketing/pengiriman/' . $rDo->id . '/print') }}" target="_blank" class="text-decoration-none text-primary" title="Lihat/Cetak Surat Jalan {{ $rDo->nomor_do }}">
                                        {{ $rDo->nomor_do }}
                                    </a>
                                    @if(!$rDoIsAcc)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace py-0.5 px-1.5" style="font-size:0.65rem;" title="Surat Jalan masih berstatus: {{ $rDo->status }} (Belum di-ACC)">
                                            <i class="bi bi-clock me-0.5"></i>{{ $rDo->status }}
                                        </span>
                                    @endif
                                </span>{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </span>
                    </div>
                    @if($invoice->nomor_resi_list)
                    <div class="d-flex justify-content-between small mb-1 align-items-start">
                        <span class="text-body-secondary text-nowrap me-2">No. Resi Kirim:</span>
                        <span class="fw-bold font-monospace text-secondary text-end">
                            <i class="bi bi-upc-scan me-1"></i>{{ $invoice->nomor_resi_list }}
                        </span>
                    </div>
                    @endif
                    @endif
                    @if($invoice->salesOrder || $invoice->deliveryOrder?->salesOrder)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Sales Order (SO):</span>
                        <span class="fw-bold font-monospace text-info-emphasis"><i class="bi bi-cart me-1"></i>{{ $invoice->salesOrder?->nomor_so ?? $invoice->deliveryOrder?->salesOrder?->nomor_so }}</span>
                    </div>
                    @endif
                    @if($relatedDos->isEmpty() && !$invoice->salesOrder)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Referensi:</span>
                        <span class="text-muted font-monospace">Manual (Tanpa DO/SO)</span>
                    </div>
                    @endif
                    @if($invoice->tender)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Tender / Proyek:</span>
                        <span class="fw-bold font-monospace text-primary">
                            <i class="bi bi-briefcase me-1"></i>{{ $invoice->tender->nama_tender }}{{ !empty($invoice->tender->nomor_kontrak) && $invoice->tender->nomor_kontrak !== '-' ? ' ('.$invoice->tender->nomor_kontrak.')' : '' }}
                        </span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between small">
                        <span class="text-body-secondary">Status Faktur:</span>
                        <span>
                            @if($invoice->status === 'Paid')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($invoice->status === 'Unpaid')
                                <span class="badge bg-primary">Disetujui (Belum Lunas)</span>
                            @elseif($invoice->status === 'Menunggu Persetujuan')
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu Approval</span>
                            @elseif($invoice->status === 'Rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                            @elseif($invoice->status === 'Draft')
                                <span class="badge bg-secondary font-monospace">Draft</span>
                            @else
                                <span class="badge bg-secondary">{{ $invoice->status }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        @php
            // Hitung total qty terkirim per barang_id HANYA jika status DO sudah di-ACC / disetujui
            $deliveredQtyMap = [];
            $pendingDeliveryQtyMap = [];
            $hasSoLink = false;

            if ($invoice->salesOrder) {
                $hasSoLink = true;
                // Gunakan relasi yang sudah di eager load dari controller agar hemat query
                $soDetails = ($invoice->salesOrder->relationLoaded('details') && $invoice->salesOrder->details->first()?->relationLoaded('deliveryOrderDetails'))
                    ? $invoice->salesOrder->details
                    : $invoice->salesOrder->details()->with(['deliveryOrderDetails.deliveryOrder'])->get();
                foreach ($soDetails as $soDet) {
                    $barangId = $soDet->barang_id;
                    if (!$barangId) continue;

                    foreach ($soDet->deliveryOrderDetails as $dod) {
                        $doStatus = $dod->deliveryOrder?->status;
                        if ($doStatus && in_array($doStatus, $approvedDoStatuses)) {
                            // HANYA jika sudah di-ACC / disetujui baru tercatat di total kirim
                            $deliveredQtyMap[$barangId] = ($deliveredQtyMap[$barangId] ?? 0) + (float)$dod->qty_kirim;
                        } elseif ($doStatus && in_array($doStatus, ['Draft', 'Menunggu Persetujuan'])) {
                            // Masih pending / belum di-ACC
                            $pendingDeliveryQtyMap[$barangId] = ($pendingDeliveryQtyMap[$barangId] ?? 0) + (float)$dod->qty_kirim;
                        }
                    }
                }
            } elseif ($invoice->deliveryOrder) {
                $hasSoLink = true;
                $doStatus = $invoice->deliveryOrder->status;
                $doDetails = $invoice->deliveryOrder->details ?? collect();
                foreach ($doDetails as $doDet) {
                    $barangId = $doDet->barang_id;
                    if (!$barangId) continue;

                    if (in_array($doStatus, $approvedDoStatuses)) {
                        $deliveredQtyMap[$barangId] = ($deliveredQtyMap[$barangId] ?? 0) + (float)$doDet->qty_kirim;
                    } elseif (in_array($doStatus, ['Draft', 'Menunggu Persetujuan'])) {
                        $pendingDeliveryQtyMap[$barangId] = ($pendingDeliveryQtyMap[$barangId] ?? 0) + (float)$doDet->qty_kirim;
                    }
                }
            }
        @endphp
        @php
            $fmtQty = function($val) {
                $num = (float)$val;
                return fmod($num, 1) == 0 ? number_format($num, 0, ',', '.') : rtrim(rtrim(number_format($num, 2, ',', '.'), '0'), ',');
            };
        @endphp
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Deskripsi Barang / Jasa</th>
                        <th style="width: 90px;" class="text-center text-nowrap">Qty Faktur</th>
                        @if($hasSoLink)
                        <th style="width: 140px;" class="text-center text-nowrap">Sisa Kirim</th>
                        @endif
                        <th style="min-width: 140px;" class="text-end text-nowrap">Harga Satuan</th>
                        <th style="width: 85px;" class="text-center text-nowrap">Diskon</th>
                        <th style="min-width: 120px;" class="text-center text-nowrap">PPN</th>
                        <th style="min-width: 145px;" class="text-end text-nowrap">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->details as $idx => $det)
                    @php
                        $qtyFaktur    = (float) $det->qty;
                        $barangId     = $det->barang_id;
                        $totalKirim   = isset($deliveredQtyMap[$barangId]) ? (float)$deliveredQtyMap[$barangId] : 0;
                        $pendingKirim = isset($pendingDeliveryQtyMap[$barangId]) ? (float)$pendingDeliveryQtyMap[$barangId] : 0;
                        $sisaKirim    = max(0, $qtyFaktur - $totalKirim);
                        $sudahLunas   = ($totalKirim > 0 && $sisaKirim <= 0);
                        $sebagianKirim= ($totalKirim > 0 && $totalKirim < $qtyFaktur);

                        $diskonVal   = (float) $det->diskon_persen;
                        $diskonNom   = (float) $det->diskon_nominal;
                        if ($diskonVal > 0) {
                            $diskonFmt = (fmod($diskonVal, 1) == 0 ? number_format($diskonVal, 0, ',', '.') : rtrim(rtrim(number_format($diskonVal, 2, ',', '.'), '0'), ',')) . '%';
                        } elseif ($diskonNom > 0) {
                            $diskonFmt = 'Rp ' . number_format($diskonNom, 2, ',', '.');
                        } else {
                            $diskonFmt = '-';
                        }
                    @endphp
                    <tr>
                        <td class="text-center small font-monospace">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-semibold text-body-emphasis">{{ $det->nama_barang }}</div>
                        </td>
                        <td class="text-center font-monospace text-nowrap">{{ $fmtQty($qtyFaktur) }}</td>
                        @if($hasSoLink)
                        <td class="text-center">
                            @if(!$barangId)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace" style="font-size:0.72rem;">Manual</span>
                            @elseif($sudahLunas)
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span class="badge rounded-pill px-2 py-1 font-monospace text-nowrap" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;font-size:0.72rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ $fmtQty($totalKirim) }} Terkirim
                                    </span>
                                    <small class="text-body-secondary font-monospace" style="font-size:0.68rem;">
                                        <i class="bi bi-check2-all me-1 text-success"></i>Lengkap
                                    </small>
                                </div>
                            @elseif($sebagianKirim)
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span class="badge rounded-pill px-2 py-1 font-monospace text-nowrap" style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;font-size:0.72rem;">
                                        <i class="bi bi-box-seam me-1"></i>{{ $fmtQty($totalKirim) }} Terkirim
                                    </span>
                                    <small class="text-body-secondary font-monospace" style="font-size:0.68rem;">
                                        Sisa: {{ $fmtQty($sisaKirim) }} / {{ $fmtQty($qtyFaktur) }}
                                    </small>
                                    @if($pendingKirim > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-nowrap" style="font-size:0.65rem;" title="Pengiriman sedang menunggu approval / ACC">
                                        <i class="bi bi-clock me-0.5"></i>{{ $fmtQty($pendingKirim) }} Menunggu ACC
                                    </span>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span class="badge rounded-pill px-2 py-1 font-monospace text-nowrap" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;font-size:0.72rem;">
                                        <i class="bi bi-dash-circle me-1"></i>0 Terkirim
                                    </span>
                                    @if($pendingKirim > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-nowrap" style="font-size:0.68rem;" title="Surat Jalan masih berstatus Pending dan belum di-ACC">
                                        <i class="bi bi-hourglass-split me-0.5"></i>{{ $fmtQty($pendingKirim) }} Menunggu ACC
                                    </span>
                                    @else
                                    <small class="text-body-secondary font-monospace" style="font-size:0.68rem;">
                                        Belum Dikirim
                                    </small>
                                    @endif
                                </div>
                            @endif
                        </td>
                        @endif
                        <td class="text-end font-monospace text-nowrap">Rp {{ number_format($det->harga_satuan, 2, ',', '.') }}</td>
                        <td class="text-center font-monospace text-nowrap">{{ $diskonFmt }}</td>
                        <td class="text-center font-monospace text-nowrap">
                            @if((float)$det->tarif_ppn > 0)
                                @php
                                    $ppnVal = (float)$det->tarif_ppn;
                                    $ppnFmt = fmod($ppnVal, 1) == 0 ? number_format($ppnVal, 0, ',', '.') : rtrim(rtrim(number_format($ppnVal, 2, ',', '.'), '0'), ',');
                                @endphp
                                <span class="badge bg-info-subtle text-info-emphasis">{{ $ppnFmt }}%</span>
                                <small class="d-block text-body-secondary font-monospace" style="font-size: 0.72rem;">(Rp {{ number_format($det->nilai_ppn, 2, ',', '.') }})</small>
                            @else
                                <span class="text-body-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace fw-bold text-body-emphasis text-nowrap">Rp {{ number_format($det->total_harga, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Biaya Tambahan Breakdown Section -->
        @if($invoice->costs && $invoice->costs->count() > 0)
        <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 p-sm-4 mb-4 shadow-sm">
            <h6 class="fw-bold text-body-emphasis mb-3 d-flex align-items-center gap-2">
                <span class="p-1.5 rounded-2 text-primary d-inline-flex bg-primary-subtle">
                    <i class="bi bi-truck fs-6"></i>
                </span>
                <span>Rincian Biaya Tambahan</span>
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 font-monospace small">
                    <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                        <tr>
                            <th style="min-width: 180px;">Jenis Biaya</th>
                            <th class="text-end text-nowrap" style="width: 180px;">Nominal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->costs as $cItem)
                        <tr>
                            <td class="fw-bold text-body-emphasis text-nowrap">{{ $cItem->jenis_biaya }}</td>
                            <td class="text-end fw-bold text-nowrap font-monospace">Rp {{ number_format($cItem->nominal, 2, ',', '.') }}</td>
                            <td class="text-body-secondary">{{ $cItem->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @php
            $validPayments = $invoice->paymentDetails ? $invoice->paymentDetails->filter(function($d) {
                return $d->payment && $d->payment->status !== 'Cancelled' && empty($d->payment->deleted_at);
            })->sortBy(function($d) {
                return $d->payment->tanggal_bayar ?? $d->created_at;
            })->values() : collect();

            $totalPaidFromPayments = (float)$validPayments->sum(function($d) {
                return (float)$d->jumlah_bayar + (float)$d->potongan;
            });

            $dpNominal = (float)($invoice->uang_muka ?? 0);
            $totalSemuaMasuk = $dpNominal + $totalPaidFromPayments;
            $sisaTagihanBerjalan = max(0, (float)$invoice->grand_total - $totalSemuaMasuk);
            $nextPaymentStep = $validPayments->count() + 1;
            $runningPiutang = (float)$invoice->grand_total;
        @endphp

        <!-- Riwayat Pembayaran & Cicilan (Termin Ke-1, Ke-2, dst.) -->
        <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 p-sm-4 mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom border-body-tertiary">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 text-success d-inline-flex bg-success-subtle">
                        <i class="bi bi-receipt-cutoff fs-6"></i>
                    </span>
                    <span>Riwayat Pembayaran &amp; Cicilan Termin</span>
                </h6>
                <div>
                    @if($sisaTagihanBerjalan <= 0.01 || $invoice->status === 'Paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill font-monospace small fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i> STATUS: LUNAS (PAID)
                        </span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill font-monospace small fw-bold">
                            <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran Ke-{{ $nextPaymentStep }}
                        </span>
                    @endif
                </div>
            </div>

            @if($dpNominal > 0 || $validPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 font-monospace small">
                        <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                            <tr>
                                <th class="text-center text-nowrap" style="width: 45px;">No.</th>
                                <th class="text-nowrap" style="width: 145px;">Termin / Tahapan</th>
                                <th class="text-center text-nowrap" style="width: 115px;">Tanggal Bayar</th>
                                <th class="text-nowrap" style="min-width: 190px;">No. Bukti Transaksi</th>
                                <th class="text-nowrap" style="min-width: 200px;">Kas / Bank &amp; Metode</th>
                                <th class="text-end text-nowrap" style="width: 165px;">Jumlah Diterima</th>
                                <th class="text-end text-nowrap" style="width: 175px;">Sisa Piutang Berjalan</th>
                                <th class="text-center text-nowrap" style="width: 95px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNo = 1; @endphp
                            @if($dpNominal > 0)
                                @php $runningPiutang = max(0, $runningPiutang - $dpNominal); @endphp
                                <tr>
                                    <td class="text-center text-nowrap">{{ $rowNo++ }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                                            <i class="bi bi-cash-stack me-1"></i>Uang Muka (DP)
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">{{ \Carbon\Carbon::parse($invoice->tanggal_faktur)->format('d/m/Y') }}</td>
                                    <td class="text-body-secondary text-nowrap font-monospace">DP Awal Faktur</td>
                                    <td class="text-nowrap">
                                        <div class="fw-semibold text-body-emphasis">Kas / Bank Masuk</div>
                                        <small class="text-muted font-monospace" style="font-size:0.75rem;">Uang Muka Penjualan</small>
                                    </td>
                                    <td class="text-end fw-bold text-success text-nowrap">Rp {{ number_format($dpNominal, 2, ',', '.') }}</td>
                                    <td class="text-end fw-semibold text-body-emphasis text-nowrap">Rp {{ number_format($runningPiutang, 2, ',', '.') }}</td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Selesai</span>
                                    </td>
                                </tr>
                            @endif

                            @foreach($validPayments as $idx => $pDetail)
                                @php
                                    $pNominal = (float)$pDetail->jumlah_bayar + (float)$pDetail->potongan;
                                    $runningPiutang = max(0, $runningPiutang - $pNominal);
                                    $pay = $pDetail->payment;
                                @endphp
                                <tr>
                                    <td class="text-center text-nowrap">{{ $rowNo++ }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                            <i class="bi bi-check2-circle me-1"></i>Pembayaran Ke-{{ $idx + 1 }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">{{ $pay && $pay->tanggal_bayar ? \Carbon\Carbon::parse($pay->tanggal_bayar)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-nowrap">
                                        <span class="fw-bold text-primary font-monospace">{{ $pay?->nomor_pembayaran ?: '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($pay?->kasBank)
                                            <div class="fw-semibold text-body-emphasis">{{ $pay->kasBank->nama_bank }}</div>
                                            <small class="text-muted font-monospace" style="font-size:0.75rem;">{{ $pay->kasBank->nomor_rekening ?: 'Kas' }}</small>
                                        @else
                                            <div class="fw-semibold text-body-emphasis">{{ $pay?->paymentMethod?->name ?? 'Kas / Bank' }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success text-nowrap">
                                        <div>Rp {{ number_format($pNominal, 2, ',', '.') }}</div>
                                        @if((float)$pDetail->potongan > 0)
                                            <small class="text-danger font-monospace fw-normal d-block" style="font-size: 0.72rem;">(Kas: Rp {{ number_format((float)$pDetail->jumlah_bayar, 2, ',', '.') }} | Diskon: Rp {{ number_format((float)$pDetail->potongan, 2, ',', '.') }})</small>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-body-emphasis text-nowrap">Rp {{ number_format($runningPiutang, 2, ',', '.') }}</td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">{{ $pay?->status ?? 'Posted' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($sisaTagihanBerjalan > 0.01)
                    <div class="rounded-3 p-3 mt-3 border border-primary-subtle bg-primary-subtle bg-opacity-10 d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-body-emphasis small">Menunggu Pembayaran Ke-{{ $nextPaymentStep }}</div>
                            <div class="small text-body-secondary">
                                Sisa tagihan berjalan saat ini: <strong class="text-primary font-monospace fs-6">Rp {{ number_format($sisaTagihanBerjalan, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-3 p-3 mt-3 border border-success-subtle bg-success-subtle bg-opacity-10 d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-circle bg-success bg-opacity-20 text-success d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-check2-all fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-success small">Tagihan Faktur LUNAS</div>
                            <div class="small text-body-secondary">
                                Semua tagihan faktur ini telah dibayar penuh. Total penerimaan: <strong class="font-monospace text-body-emphasis">Rp {{ number_format($totalSemuaMasuk, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-body-secondary border border-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-2 bg-secondary bg-opacity-10 text-muted">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block small text-body-emphasis">Belum Ada Pembayaran Tercatat</span>
                            <small class="text-body-secondary">Faktur ini belum menerima pembayaran cicilan maupun pelunasan.</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill font-monospace small">
                        Belum Dibayar
                    </span>
                </div>
            @endif
        </div>

        <!-- Grand Total Summary Row -->
        <div class="row justify-content-end mb-4">
            <div class="col-lg-5 col-md-7">
                <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 p-sm-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-body-tertiary">
                        <span class="small fw-bold text-body-secondary text-uppercase font-monospace d-flex align-items-center gap-2">
                            <i class="bi bi-calculator text-primary fs-6"></i>Ringkasan Finansial
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">{{ $invoice->currency?->kode_currency ?? 'IDR' }}</span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-body-secondary font-monospace">Subtotal Barang / Jasa</span>
                        <span class="fw-bold font-monospace text-body-emphasis text-nowrap">Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if($invoice->diskon_nominal > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-body-secondary font-monospace">Diskon Faktur</span>
                        <span class="fw-bold font-monospace text-danger text-nowrap">- Rp {{ number_format($invoice->diskon_nominal, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($invoice->pajak_nominal > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-body-secondary font-monospace">Total PPN</span>
                        <span class="fw-bold font-monospace text-body-emphasis text-nowrap">+ Rp {{ number_format($invoice->pajak_nominal, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($invoice->biaya_lain > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-body-secondary font-monospace">Total Biaya Tambahan</span>
                        <span class="fw-bold font-monospace text-body-emphasis text-nowrap">+ Rp {{ number_format($invoice->biaya_lain, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($invoice->pph_nominal > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-body-secondary font-monospace">Potongan PPh ({{ $invoice->pphPajak?->nama_pajak ?? 'PPh' }})</span>
                        <span class="fw-bold font-monospace text-warning text-nowrap">- Rp {{ number_format($invoice->pph_nominal, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <hr class="my-2.5 border-body-tertiary">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-body-emphasis font-monospace">Total Tagihan Faktur</span>
                        <span class="fw-bold font-monospace text-body-emphasis fs-6 text-nowrap">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</span>
                    </div>
                    @if($dpNominal > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-warning font-monospace"><i class="bi bi-cash-stack me-1"></i> Uang Muka / DP</span>
                        <span class="fw-bold font-monospace text-danger text-nowrap">- Rp {{ number_format($dpNominal, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($validPayments->count() > 0)
                    @php
                        $totalBayarPure = (float)$validPayments->sum(fn($d) => (float)$d->jumlah_bayar);
                        $totalPotonganPure = (float)$validPayments->sum(fn($d) => (float)$d->potongan);
                    @endphp
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-success font-monospace"><i class="bi bi-check2-circle me-1"></i> Pembayaran Masuk (Kas/Bank)</span>
                        <span class="fw-bold font-monospace text-success text-nowrap">- Rp {{ number_format($totalBayarPure, 2, ',', '.') }}</span>
                    </div>
                    @if($totalPotonganPure > 0)
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-danger font-monospace"><i class="bi bi-tag me-1"></i> Potongan / Diskon Pelunasan</span>
                        <span class="fw-bold font-monospace text-danger text-nowrap">- Rp {{ number_format($totalPotonganPure, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @endif

                    <div class="rounded-3 p-3 mt-3 bg-body-secondary border border-body-tertiary d-flex justify-content-between align-items-center">
                        @if($sisaTagihanBerjalan <= 0.01 || $invoice->status === 'Paid')
                            <span class="fw-bold font-monospace text-success small">Status Pelunasan</span>
                            <span class="badge bg-success text-white font-monospace px-3 py-2 rounded-pill shadow-sm">LUNAS</span>
                        @elseif($validPayments->count() > 0 || $dpNominal > 0)
                            <span class="fw-bold font-monospace text-primary small">Sisa Tagihan (Termin Ke-{{ $nextPaymentStep }})</span>
                            <span class="fw-bold font-monospace text-primary fs-5 text-nowrap">Rp {{ number_format($sisaTagihanBerjalan, 2, ',', '.') }}</span>
                        @else
                            <span class="fw-bold font-monospace text-body-emphasis small">Sisa Tagihan</span>
                            <span class="fw-bold font-monospace text-primary fs-5 text-nowrap">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</span>
                        @endif
                    </div>
                    @if($invoice->catatan)
                    <div class="mt-3 p-2.5 rounded-2 bg-body-secondary small text-body-secondary border border-body-tertiary">
                        <strong class="text-body-emphasis"><i class="bi bi-chat-left-text me-1"></i>Catatan:</strong> {{ $invoice->catatan }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- File Pendukung Attachment Section -->
        <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 mb-4">
            <h6 class="fw-bold text-body-emphasis mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-paperclip text-primary"></i> Dokumen / Foto Pendukung
            </h6>
            @if($invoice->file_pendukung && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->file_pendukung))
                @php
                    $ext = strtolower(pathinfo($invoice->file_pendukung, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                    $sizeBytes = \Illuminate\Support\Facades\Storage::disk('public')->size($invoice->file_pendukung);
                    $sizeKb = round($sizeBytes / 1024, 1);
                @endphp
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-body-secondary border border-body-tertiary">
                    <div class="d-flex align-items-center gap-2">
                        @if($isImage)
                            <i class="bi bi-file-earmark-image-fill text-success fs-3"></i>
                        @else
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                        @endif
                        <div>
                            <span class="fw-bold d-block small text-body-emphasis font-monospace">{{ basename($invoice->file_pendukung) }}</span>
                            <small class="text-body-secondary font-monospace" style="font-size:0.75rem;">
                                Format: .{{ strtoupper($ext) }} &bull; Ukuran: <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $sizeKb }} KB</span>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" 
                                class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold btn-preview-document"
                                data-url="{{ asset('storage/' . $invoice->file_pendukung) }}"
                                data-filename="{{ basename($invoice->file_pendukung) }}"
                                data-ext="{{ $ext }}"
                                data-download="{{ route('modul_marketing.faktur_penjualan.download_document', $invoice->id) }}">
                            <i class="bi bi-eye me-1"></i> Buka / Preview
                        </button>
                        <a href="{{ route('modul_marketing.faktur_penjualan.download_document', $invoice->id) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm btn-download-document no-loader">
                            <i class="bi bi-download me-1"></i> Download Dokumen
                        </a>
                    </div>
                </div>
                @if($isImage)
                <div class="mt-3 text-center">
                    <a href="javascript:void(0)" class="btn-preview-document d-inline-block text-decoration-none"
                       data-url="{{ asset('storage/' . $invoice->file_pendukung) }}"
                       data-filename="{{ basename($invoice->file_pendukung) }}"
                       data-ext="{{ $ext }}"
                       data-download="{{ route('modul_marketing.faktur_penjualan.download_document', $invoice->id) }}"
                       title="Klik untuk memperbesar preview di modal">
                        <img src="{{ asset('storage/' . $invoice->file_pendukung) }}" alt="Foto Pendukung" class="img-fluid rounded-3 border shadow-sm" style="max-height: 280px; object-fit: contain; cursor: pointer;">
                        <span class="d-block text-body-secondary small mt-1 font-monospace"><i class="bi bi-zoom-in me-1"></i>Klik gambar untuk memperbesar</span>
                    </a>
                </div>
                @endif
            @else
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-body-secondary border border-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-file-earmark-x text-muted fs-3"></i>
                        <div>
                            <span class="fw-bold d-block small text-body-emphasis">Tidak Ada Dokumen Dilampirkan</span>
                            <small class="text-body-secondary">Faktur ini belum memiliki file pendukung atau bukti fisik.</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill font-monospace small">
                        Kosong
                    </span>
                </div>
            @endif
        </div>

        <!-- Accounting Journal Tab Section -->
        @if($invoice->jurnal && (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-jurnal-sales-invoice')))
        <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 p-sm-4 mb-3 shadow-sm">
            <h6 class="fw-bold text-body-emphasis mb-3 d-flex align-items-center gap-2">
                <span class="p-1.5 rounded-2 text-primary d-inline-flex bg-primary-subtle">
                    <i class="bi bi-journal-bookmark-fill fs-6"></i>
                </span>
                <span>Jurnal Otomatis Akuntansi</span>
                <span class="badge bg-secondary-subtle text-secondary font-monospace ms-auto" style="font-size:0.72rem;">Ref: {{ $invoice->jurnal->nomor_jurnal }}</span>
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 font-monospace small">
                    <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                        <tr>
                            <th>Kode / Nama Akun</th>
                            <th class="text-end text-nowrap" style="width: 170px;">Debit</th>
                            <th class="text-end text-nowrap" style="width: 170px;">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->jurnal->details as $jdet)
                        <tr>
                            <td class="text-body-emphasis">{{ $jdet->account?->code ?? '-' }} - {{ $jdet->account?->name ?? 'Akun' }}</td>
                            <td class="text-end text-nowrap {{ $jdet->debit > 0 ? 'fw-bold text-success' : '' }}">Rp {{ number_format($jdet->debit, 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap {{ $jdet->kredit > 0 ? 'fw-bold text-primary' : '' }}">Rp {{ number_format($jdet->kredit, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal-footer border-top py-3 px-4 bg-body-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @php
            $user = auth()->user();
            $canApprove = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('approve-sales-invoice');
            $canEdit = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('edit-sales-invoice');
            $canDelete = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('hapus-sales-invoice');
            $isCreator = $invoice->created_by == $user->id;
        @endphp

        {{-- Tombol Ajukan (bila Draft atau Rejected) --}}
        @if(in_array($invoice->status, ['Draft', 'Rejected']) && ($canEdit || $isCreator))
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm btn-submit-invoice" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}">
            <i class="bi bi-send me-1"></i> Ajukan Approval
        </button>
        @endif

        {{-- Tombol Edit (bila Draft, Menunggu Persetujuan, atau Rejected) --}}
        @if(in_array($invoice->status, ['Draft', 'Rejected', 'Menunggu Persetujuan']) && ($canEdit || $isCreator))
        <button type="button" class="btn btn-warning rounded-pill px-3 fw-bold shadow-sm btn-edit-invoice" data-id="{{ $invoice->id }}" data-bs-dismiss="modal">
            <i class="bi bi-pencil me-1"></i> Edit Faktur
        </button>
        @endif

        {{-- Tombol Hapus: Hanya tampil untuk status Draft dan Rejected (tidak boleh tampil jika Disetujui/Unpaid/Partial/Paid/Menunggu Persetujuan) --}}
        @if(in_array($invoice->status, ['Draft', 'Rejected']) && ($canDelete || ($invoice->status === 'Draft' && $isCreator)))
        <button type="button" class="btn btn-outline-danger rounded-pill px-3 fw-bold shadow-sm btn-delete-invoice" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}" data-bs-dismiss="modal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
        @endif

        {{-- Tombol Persetujuan (Hanya tampil jika Menunggu Persetujuan & berhak approve) --}}
        @if($invoice->status === 'Menunggu Persetujuan' && $canApprove)
        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm btn-approve-invoice" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}">
            <i class="bi bi-check2-circle me-1"></i> Setujui (Approve)
        </button>
        <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm btn-reject-invoice" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}">
            <i class="bi bi-x-circle me-1"></i> Tolak (Reject)
        </button>
        @endif

        {{-- Tombol Batal Setujui / Kembalikan ke Draf (Khusus Super Admin, hanya jika status Unpaid dan belum ada pembayaran) --}}
        @if($invoice->status === 'Unpaid' && auth()->user()->hasRole('Super Admin') && $validPayments->count() === 0)
        <button type="button" class="btn btn-outline-warning rounded-pill px-4 fw-bold shadow-sm btn-unapprove-invoice" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Batal Setujui (Ke Draf)
        </button>
        @endif

        @if($invoice->file_pendukung && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->file_pendukung))
        <a href="{{ route('modul_marketing.faktur_penjualan.download_document', $invoice->id) }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm btn-download-document no-loader">
            <i class="bi bi-download me-1"></i> Download Dokumen
        </a>
        @else
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold text-muted" disabled title="Tidak ada dokumen terlampir pada faktur ini">
            <i class="bi bi-download me-1"></i> Dokumen Tidak Ada
        </button>
        @endif
        <a href="{{ route('modul_marketing.faktur_penjualan.print', $invoice->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm no-loader">
            <i class="bi bi-printer me-1"></i> Cetak Faktur
        </a>
        @if($invoice->jurnal && (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-jurnal-sales-invoice')))
        <button type="button" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm btn-view-jurnal" data-id="{{ $invoice->id }}" data-nomor="{{ $invoice->nomor_faktur }}">
            <i class="bi bi-journal-bookmark-fill me-1"></i> Voucher Jurnal
        </button>
        @endif
    </div>
</div>
