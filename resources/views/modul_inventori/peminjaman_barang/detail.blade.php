<x-layouts title="Detail Peminjaman Barang - {{ $peminjaman->nomor_peminjaman }}">

    <link rel="stylesheet" href="{{ asset('inventori/pinjam_barang.css') }}?v={{ time() }}">

    <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4 peminjaman-detail-container">
        <!-- Breadcrumb & Top Bar -->
        <div class="detail-header-wrapper d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 p-2 text-white flex-shrink-0" style="background: var(--accent-gradient); width: 42px; height: 42px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                        <i class="bi bi-file-earmark-check-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h3 class="fw-bold mb-0 text-page-title fs-4 fs-sm-3">{{ $peminjaman->nomor_peminjaman }}</h3>
                            @php
                                $statusBadgeMap = [
                                    'Draft'                 => 'badge-status-draft',
                                    'Menunggu'              => 'badge-status-warning',
                                    'Barang Disiapkan'      => 'badge-status-info',
                                    'Barang Siap'           => 'badge-status-primary',
                                    'Sedang Dipinjam'       => 'badge-status-purple',
                                    'Selesai'               => 'badge-status-success',
                                    'Selesai (Ada Masalah)' => 'badge-status-danger',
                                    'Ditolak'               => 'badge-status-danger',
                                ];
                                $statusClass = $statusBadgeMap[$peminjaman->status] ?? 'badge-status-draft';
                            @endphp
                            <span class="badge-status-pill {{ $statusClass }}">
                                <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                                <span>{{ $peminjaman->status }}</span>
                            </span>
                        </div>
                        <small class="text-muted">Diajukan pada {{ $peminjaman->created_at ? $peminjaman->created_at->format('d M Y, H:i') : '-' }}</small>
                    </div>
                </div>
            </div>

            <!-- Top Action Buttons -->
            <div class="detail-header-actions d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('peminjaman.index') }}" class="btn btn-cinematic-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                @if (in_array($peminjaman->status, ['Draft', 'Menunggu']) && auth()->user()->can('edit-peminjaman'))
                    @php
                        $canEditDraft = ($peminjaman->status !== 'Draft') || ($peminjaman->created_by == auth()->id()) || ($peminjaman->diajukan_oleh == auth()->id()) || auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
                    @endphp
                    @if ($canEditDraft)
                        <a href="{{ route('peminjaman.edit', $peminjaman->id) }}" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-2 rounded-3 px-3">
                            <i class="bi bi-pencil-square"></i> Edit Pengajuan
                        </a>
                    @endif
                @endif
                <a href="{{ route('peminjaman.print-surat-jalan', $peminjaman->id) }}" target="_blank" class="btn btn-cinematic-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-printer-fill"></i> Cetak Surat Jalan
                </a>
            </div>
        </div>

        <!-- Workflow Status Stepper Card -->
        @php
            $statuses = ['Draft', 'Menunggu', 'Barang Disiapkan', 'Barang Siap', 'Sedang Dipinjam', 'Selesai'];
            $currentStatus = $peminjaman->status;
            $currentIndex = array_search($currentStatus, $statuses);
            if ($currentStatus === 'Selesai (Ada Masalah)') $currentIndex = 5;
            $isRejected = ($currentStatus === 'Ditolak');

            $bannerClass = 'banner-info';
            if ($currentStatus === 'Draft') $bannerClass = 'banner-draft';
            elseif ($currentStatus === 'Menunggu') $bannerClass = 'banner-warning';
            elseif ($currentStatus === 'Barang Disiapkan') $bannerClass = 'banner-info';
            elseif ($currentStatus === 'Barang Siap') $bannerClass = 'banner-primary';
            elseif ($currentStatus === 'Sedang Dipinjam') $bannerClass = 'banner-purple';
            elseif (in_array($currentStatus, ['Selesai', 'Selesai (Ada Masalah)'])) $bannerClass = 'banner-success';
            elseif ($currentStatus === 'Ditolak') $bannerClass = 'banner-danger';
        @endphp

        <div class="card card-custom p-3 p-sm-4 mb-4">
            <div class="workflow-timeline-wrapper">
                <div class="workflow-timeline">
                    @foreach (['Draft', 'Menunggu', 'Barang Disiapkan', 'Barang Siap', 'Sedang Dipinjam', 'Selesai'] as $idx => $st)
                        @php
                            $stepClass = '';
                            if ($isRejected) {
                                $stepClass = ($idx <= 1) ? 'completed' : ($idx === 2 ? 'rejected' : '');
                            } else {
                                if ($currentIndex !== false) {
                                    if ($idx < $currentIndex) $stepClass = 'completed';
                                    elseif ($idx === $currentIndex) $stepClass = 'active';
                                }
                            }
                            $icons = ['1', '2', '3', '4', '5', '✓'];
                        @endphp
                        <div class="timeline-step {{ $stepClass }}">
                            <div class="step-icon">
                                @if ($stepClass === 'completed')
                                    <i class="bi bi-check-lg"></i>
                                @elseif ($stepClass === 'rejected')
                                    <i class="bi bi-x-lg"></i>
                                @else
                                    {{ $icons[$idx] }}
                                @endif
                            </div>
                            <div class="step-label">
                                {{ $st }}
                                @if ($idx === 4 && $currentStatus === 'Sedang Dipinjam')
                                    <span class="d-block text-purple" style="font-size: 0.725rem;">(Stok di Booth)</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Workflow Action Callout Banner -->
            <div class="workflow-action-card {{ $bannerClass }}">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 p-2.5 flex-shrink-0" style="width: 44px; height: 44px; background: var(--workflow-banner-icon-bg);">
                        @if ($currentStatus === 'Draft') <i class="bi bi-file-earmark-text text-muted fs-4"></i>
                        @elseif ($currentStatus === 'Menunggu') <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        @elseif ($currentStatus === 'Barang Disiapkan') <i class="bi bi-boxes text-info fs-4"></i>
                        @elseif ($currentStatus === 'Barang Siap') <i class="bi bi-box-seam text-primary fs-4"></i>
                        @elseif ($currentStatus === 'Sedang Dipinjam') <i class="bi bi-geo-alt-fill text-purple fs-4"></i>
                        @elseif (in_array($currentStatus, ['Selesai', 'Selesai (Ada Masalah)'])) <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        @else <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold workflow-banner-title fs-6">
                            @if ($currentStatus === 'Draft') Dokumen Masih Draft
                            @elseif ($currentStatus === 'Menunggu') Menunggu Persetujuan Atasan (Mengetahui)
                            @elseif ($currentStatus === 'Barang Disiapkan') Disetujui! Tim Gudang Sedang Menyiapkan Fisik Barang
                            @elseif ($currentStatus === 'Barang Siap') Fisik Barang Siap &amp; Dipacking oleh Petugas Gudang
                            @elseif ($currentStatus === 'Sedang Dipinjam') Barang Berada di Lokasi Pameran (Sedang Dipinjam)
                            @elseif ($currentStatus === 'Selesai') Peminjaman Selesai - Semua Barang Telah Kembali Utuh
                            @elseif ($currentStatus === 'Selesai (Ada Masalah)') Peminjaman Selesai - Terdapat Barang Rusak / Hilang
                            @elseif ($currentStatus === 'Ditolak') Pengajuan Peminjaman Ditolak
                            @endif
                        </div>
                        <small class="text-body d-block mt-0.5">
                            @if ($currentStatus === 'Draft') Klik tombol "Ajukan Sekarang" untuk mengirimkan pengajuan ke Atasan yang berwenang.
                            @elseif ($currentStatus === 'Menunggu') Atasan/Manager dapat memeriksa rincian pameran dan barang lalu klik Setujui atau Tolak.
                            @elseif ($currentStatus === 'Barang Disiapkan') Petugas gudang menyiapkan dan mem-packing fisik barang, lalu klik "Konfirmasi Barang Siap".
                            @elseif ($currentStatus === 'Barang Siap') Petugas melakukan serah terima fisik (stok barang berpindah otomatis ke gudang transit pameran).
                            @elseif ($currentStatus === 'Sedang Dipinjam') Batas waktu rencana kembali: <strong class="text-info">{{ $peminjaman->tanggal_kembali_rencana ? $peminjaman->tanggal_kembali_rencana->format('d/m/Y') : '-' }}</strong>.
                            @elseif ($currentStatus === 'Selesai') Seluruh barang dalam kondisi bagus telah kembali otomatis ke persediaan Gudang Asal.
                            @elseif ($currentStatus === 'Selesai (Ada Masalah)') Tinjau catatan kondisi akhir untuk penyelesaian administrasi / penggantian.
                            @elseif ($currentStatus === 'Ditolak') Alasan Penolakan: <strong>{{ $peminjaman->catatan_mengetahui }}</strong>
                            @endif
                        </small>
                    </div>
                </div>

                <!-- Tombol Interaksi Workflow -->
                <div class="workflow-btn-group d-flex flex-wrap gap-2">
                    @if ($currentStatus === 'Draft')
                        @php
                            $isDraftCreator = ($peminjaman->created_by == auth()->id()) || ($peminjaman->diajukan_oleh == auth()->id()) || auth()->user()->hasAnyRole(['Super Admin', 'Owner']);
                        @endphp
                        @if ($isDraftCreator)
                            <button type="button" class="btn btn-cinematic-primary px-3 py-2 d-inline-flex align-items-center gap-2" id="btn-submit-action">
                                <i class="bi bi-send-fill"></i> Ajukan Sekarang
                            </button>
                        @endif
                    @endif

                    @if ($currentStatus === 'Menunggu' && auth()->user()->can('approve-peminjaman'))
                        <button type="button" class="btn btn-success px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#modal-approve">
                            <i class="bi bi-check2-circle"></i> Setujui (Mengetahui)
                        </button>
                        <button type="button" class="btn btn-outline-danger px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#modal-reject">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                    @endif

                    @if ($currentStatus === 'Barang Disiapkan' && auth()->user()->can('siapkan-peminjaman'))
                        <button type="button" class="btn btn-info text-dark px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#modal-barang-siap">
                            <i class="bi bi-box-seam"></i> Konfirmasi Barang Siap
                        </button>
                    @endif

                    @if ($currentStatus === 'Barang Siap' && (auth()->user()->can('serah-terima-peminjaman') || auth()->user()->can('siapkan-peminjaman')))
                        <button type="button" class="btn text-white px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold rounded-3" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);" id="btn-serah-terima">
                            <i class="bi bi-arrow-right-circle"></i> Serah Terima &amp; Keluarkan Barang
                        </button>
                    @endif

                    @if ($currentStatus === 'Sedang Dipinjam' && auth()->user()->can('kembalikan-peminjaman'))
                        <button type="button" class="btn btn-success px-3 py-2 d-inline-flex align-items-center gap-2 fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#modal-pengembalian">
                            <i class="bi bi-arrow-return-left"></i> Proses Pengembalian (QC Gudang)
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Kolom Kiri: Informasi Pameran & Daftar Barang -->
            <div class="col-12 col-xl-8">
                <!-- 1. Informasi Pameran & Alokasi -->
                <div class="card card-custom p-3 p-sm-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 text-info" style="background: rgba(56, 189, 248, 0.15);">
                                <i class="bi bi-calendar2-event-fill fs-6"></i>
                            </div>
                            <h5 class="fw-bold text-adaptive mb-0">Informasi Pameran &amp; Alokasi</h5>
                        </div>
                        <span class="badge badge-cinematic-blue small px-2.5 py-1">
                            <i class="bi bi-geo-alt me-1"></i>Detail Acara
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-flag-fill text-info"></i> Nama Acara / Pameran</div>
                                <div class="info-tile-value text-adaptive">{{ $peminjaman->nama_acara }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-geo-alt-fill text-danger"></i> Lokasi Booth / Stand</div>
                                <div class="info-tile-value text-cyan">{{ $peminjaman->lokasi_acara ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-person-badge-fill text-warning"></i> Penanggung Jawab (PJ)</div>
                                <div class="info-tile-value text-adaptive d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <span>{{ $peminjaman->penanggung_jawab }}</span>
                                    @if ($peminjaman->kontak_pj)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $peminjaman->kontak_pj) }}" target="_blank" class="badge badge-cinematic-green small">
                                             <i class="bi bi-whatsapp me-1"></i>{{ $peminjaman->kontak_pj }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-calendar-check text-info"></i> Tanggal Mulai Pinjam</div>
                                <div class="info-tile-value text-adaptive">
                                    {{ $peminjaman->tanggal_pinjam ? $peminjaman->tanggal_pinjam->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-calendar-event text-warning"></i> Rencana Kembali</div>
                                <div class="info-tile-value text-adaptive">
                                    {{ $peminjaman->tanggal_kembali_rencana ? $peminjaman->tanggal_kembali_rencana->format('d/m/Y') : '-' }}
                                    @if ($peminjaman->tanggal_pinjam && $peminjaman->tanggal_kembali_rencana)
                                        @php
                                            $days = $peminjaman->tanggal_pinjam->diffInDays($peminjaman->tanggal_kembali_rencana) + 1;
                                        @endphp
                                        <span class="badge badge-cinematic-warning ms-1 small" style="font-size: 0.7rem;">{{ $days }} Hari</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-calendar2-check-fill text-success"></i> Tanggal Aktual Kembali</div>
                                <div class="info-tile-value {{ $peminjaman->tanggal_kembali_aktual ? 'text-success' : 'text-muted' }}">
                                    @if ($peminjaman->tanggal_kembali_aktual)
                                        <i class="bi bi-check2-circle me-1"></i>{{ $peminjaman->tanggal_kembali_aktual->format('d/m/Y H:i') }}
                                    @else
                                        <span class="badge badge-cinematic-muted small"><i class="bi bi-clock me-1"></i>Belum Kembali</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-building text-info"></i> Gudang Asal (Sumber Fisik)</div>
                                <div class="info-tile-value text-adaptive">
                                    <span class="badge badge-cinematic-blue px-2.5 py-1.5">
                                        <i class="bi bi-building me-1"></i>{{ $peminjaman->gudangAsal ? $peminjaman->gudangAsal->nama_gudang : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-geo-alt text-purple"></i> Gudang Transit Pameran (Tujuan)</div>
                                <div class="info-tile-value text-adaptive">
                                    <span class="badge badge-cinematic-purple px-2.5 py-1.5">
                                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $peminjaman->gudangPameran ? $peminjaman->gudangPameran->nama_gudang : 'Gudang Pameran' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-boxes text-cyan"></i> Total Barang Dipinjam</div>
                                <div class="info-tile-value text-adaptive">
                                    <span class="badge badge-cinematic-cyan px-2.5 py-1.5">
                                        <i class="bi bi-box-seam me-1"></i>{{ $peminjaman->details->count() }} Item ({{ number_format($peminjaman->details->sum('qty_pinjam'), 0, ',', '.') }} Unit)
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($peminjaman->keterangan)
                        <div class="col-12">
                            <div class="info-tile">
                                <div class="info-tile-label"><i class="bi bi-chat-left-text-fill text-muted"></i> Keterangan Tambahan / Catatan Handling</div>
                                <div class="text-body small fst-italic">{{ $peminjaman->keterangan }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Tabel Daftar Barang yang Dipinjam -->
                <div class="card card-custom p-3 p-sm-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 text-info" style="background: rgba(56, 189, 248, 0.15);">
                                <i class="bi bi-boxes fs-6"></i>
                            </div>
                            <h5 class="fw-bold text-adaptive mb-0">Daftar Barang yang Dipinjam</h5>
                        </div>
                        <span class="badge badge-cinematic-blue px-2.5 py-1 small">
                            {{ $peminjaman->details->count() }} Item Barang
                        </span>
                    </div>

                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25">
                        <table class="table-cinematic-detail align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">No</th>
                                    <th style="width: 55px;" class="text-center">Foto</th>
                                    <th>Kode &amp; Nama Barang</th>
                                    <th class="text-center">Qty Pinjam</th>
                                    <th>Kondisi Awal</th>
                                    @if (in_array($peminjaman->status, ['Sedang Dipinjam', 'Selesai', 'Selesai (Ada Masalah)']))
                                        <th class="text-center text-success">Kembali Bagus</th>
                                        <th class="text-center text-warning">Rusak</th>
                                        <th class="text-center text-danger">Hilang</th>
                                        <th>Catatan QC</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peminjaman->details as $idx => $item)
                                @php
                                    $fotoRel = $item->barang?->primaryFoto?->foto_path ?? $item->barang?->fotos?->first()?->foto_path;
                                    $fotoUrl = null;
                                    if ($fotoRel) {
                                        if (str_starts_with($fotoRel, 'http')) {
                                            $fotoUrl = $fotoRel;
                                        } elseif (str_starts_with($fotoRel, 'uploads/')) {
                                            $fotoUrl = asset($fotoRel);
                                        } else {
                                            $fotoUrl = asset('storage/' . ltrim($fotoRel, '/'));
                                        }
                                    }
                                    $namaBarang = $item->barang ? $item->barang->nama_barang : '-';
                                    $kodeBarang = $item->barang ? $item->barang->kode_barang : '-';
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td class="text-center">
                                        @if ($fotoUrl)
                                            <div class="product-thumb-sm btn-show-photo-modal mx-auto" data-url="{{ $fotoUrl }}" data-nama="{{ $namaBarang }}" data-kode="{{ $kodeBarang }}" title="Klik untuk memperbesar foto">
                                                <img src="{{ $fotoUrl }}" alt="{{ $namaBarang }}">
                                                <div class="product-thumb-overlay"><i class="bi bi-zoom-in"></i></div>
                                            </div>
                                        @else
                                            <div class="product-thumb-placeholder-sm mx-auto" title="Tidak ada foto">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-adaptive">{{ $namaBarang }}</div>
                                        <span class="badge badge-cinematic-muted small font-monospace" style="font-size: 0.725rem;">
                                            {{ $kodeBarang }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-cinematic-indigo px-2.5 py-1 fw-bold fs-7">
                                            {{ number_format($item->qty_pinjam, 0, ',', '.') }} {{ $item->satuan ? $item->satuan->nama_satuan : ($item->barang && $item->barang->satuan ? $item->barang->satuan->nama_satuan : 'Unit') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-cinematic-muted small">
                                            {{ $item->kondisi_awal }}
                                        </span>
                                    </td>
                                    @if (in_array($peminjaman->status, ['Sedang Dipinjam', 'Selesai', 'Selesai (Ada Masalah)']))
                                        <td class="text-center fw-bold text-success">{{ number_format($item->qty_kembali_bagus, 0) }}</td>
                                        <td class="text-center fw-bold text-warning">{{ number_format($item->qty_kembali_rusak, 0) }}</td>
                                        <td class="text-center fw-bold text-danger">{{ number_format($item->qty_hilang, 0) }}</td>
                                        <td><small class="text-muted">{{ $item->kondisi_akhir ?: '-' }}</small></td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Summary Footer Bar -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Klik foto produk untuk melihat gambar secara jelas.</small>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small text-muted">Total Barang: <strong class="text-adaptive">{{ $peminjaman->details->count() }} Item</strong></span>
                            <span class="small text-muted">Total Unit: <strong class="text-cyan">{{ number_format($peminjaman->details->sum('qty_pinjam'), 0) }} Unit</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Tracking & Riwayat Dokumen (TANPA TTD) -->
            <div class="col-12 col-xl-4">
                <!-- Card 1: Riwayat Persetujuan & Tracking Status (Clean & Professional, No Signature Images) -->
                <div class="card card-custom p-3 p-sm-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 text-info" style="background: rgba(56, 189, 248, 0.15);">
                                <i class="bi bi-clock-history fs-6"></i>
                            </div>
                            <h5 class="fw-bold text-adaptive mb-0">Riwayat &amp; Tracking</h5>
                        </div>
                        <span class="badge badge-cinematic-muted small">Audit Trail</span>
                    </div>

                    <!-- Vertical Approval Timeline (NO SIGNATURES) -->
                    <div class="approval-trail-container">
                        <!-- 1. Pengajuan Dokumen -->
                        <div class="approval-trail-item">
                            <div class="trail-dot done">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="trail-box">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-adaptive">1. Diajukan Oleh (Pengaju)</span>
                                    <span class="badge badge-cinematic-indigo small px-2 py-0.5" style="font-size: 0.675rem;">
                                        Diajukan
                                    </span>
                                </div>
                                <div class="fw-semibold text-adaptive fs-7">{{ $peminjaman->pengaju ? $peminjaman->pengaju->name : '-' }}</div>
                                <div class="d-flex align-items-center gap-1 text-muted small mt-1" style="font-size: 0.725rem;">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $peminjaman->diajukan_at ? $peminjaman->diajukan_at->format('d M Y, H:i') : ($peminjaman->created_at ? $peminjaman->created_at->format('d M Y, H:i') : '-') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Mengetahui (Atasan) -->
                        @php
                            $isDiketahui = !empty($peminjaman->diketahui_at);
                            $isMenungguAtasan = ($currentStatus === 'Menunggu');
                        @endphp
                        <div class="approval-trail-item">
                            <div class="trail-dot {{ $isDiketahui ? 'done' : ($isMenungguAtasan ? 'current' : 'pending') }}">
                                @if ($isDiketahui) <i class="bi bi-check-lg"></i>
                                @elseif ($isRejected) <i class="bi bi-x-lg"></i>
                                @else <i class="bi bi-hourglass-split"></i>
                                @endif
                            </div>
                            <div class="trail-box">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-adaptive">2. Mengetahui (Atasan)</span>
                                    @if ($isDiketahui)
                                        <span class="badge badge-cinematic-green small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Disetujui
                                        </span>
                                    @elseif ($isRejected)
                                        <span class="badge badge-cinematic-danger small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="badge badge-cinematic-warning small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Menunggu
                                        </span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-adaptive fs-7">{{ $peminjaman->pengetahu ? $peminjaman->pengetahu->name : 'Atasan / Manager Terkait' }}</div>
                                <div class="d-flex align-items-center gap-1 text-muted small mt-1" style="font-size: 0.725rem;">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $peminjaman->diketahui_at ? $peminjaman->diketahui_at->format('d M Y, H:i') : 'Menunggu tindakan' }}</span>
                                </div>
                                @if ($peminjaman->catatan_mengetahui)
                                    <div class="p-2 rounded-2 mt-2 small text-warning" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); font-size: 0.75rem;">
                                        <i class="bi bi-chat-quote me-1"></i><strong>Catatan:</strong> {{ $peminjaman->catatan_mengetahui }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 3. Menyiapkan (Gudang) -->
                        @php
                            $isDisiapkan = !empty($peminjaman->disiapkan_at);
                            $isProcessGudang = ($currentStatus === 'Barang Disiapkan');
                        @endphp
                        <div class="approval-trail-item">
                            <div class="trail-dot {{ $isDisiapkan ? 'done' : ($isProcessGudang ? 'current' : 'pending') }}">
                                @if ($isDisiapkan) <i class="bi bi-check-lg"></i>
                                @else <i class="bi bi-box-seam"></i>
                                @endif
                            </div>
                            <div class="trail-box">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-adaptive">3. Persiapan Fisik (Gudang)</span>
                                    @if ($isDisiapkan)
                                        <span class="badge badge-cinematic-blue small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Barang Siap
                                        </span>
                                    @elseif ($isProcessGudang)
                                        <span class="badge badge-cinematic-cyan small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Sedang Packing
                                        </span>
                                    @else
                                        <span class="badge badge-cinematic-muted small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Menunggu
                                        </span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-adaptive fs-7">{{ $peminjaman->penyiap ? $peminjaman->penyiap->name : 'Petugas Gudang' }}</div>
                                <div class="d-flex align-items-center gap-1 text-muted small mt-1" style="font-size: 0.725rem;">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $peminjaman->disiapkan_at ? $peminjaman->disiapkan_at->format('d M Y, H:i') : 'Menunggu tahap sebelumnya' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Pengembalian & QC Fisik -->
                        @php
                            $isKembali = !empty($peminjaman->diterima_kembali_at);
                            $isDiPameran = ($currentStatus === 'Sedang Dipinjam');
                        @endphp
                        <div class="approval-trail-item">
                            <div class="trail-dot {{ $isKembali ? 'done' : ($isDiPameran ? 'current' : 'pending') }}">
                                @if ($isKembali) <i class="bi bi-check-circle-fill"></i>
                                @else <i class="bi bi-arrow-return-left"></i>
                                @endif
                            </div>
                            <div class="trail-box">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-adaptive">4. Penerimaan Kembali (Gudang QC)</span>
                                    @if ($isKembali)
                                        <span class="badge badge-cinematic-green small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Diterima
                                        </span>
                                    @elseif ($isDiPameran)
                                        <span class="badge badge-cinematic-purple small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Di Pameran
                                        </span>
                                    @else
                                        <span class="badge badge-cinematic-muted small px-2 py-0.5" style="font-size: 0.675rem;">
                                            Belum Selesai
                                        </span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-adaptive fs-7">{{ $peminjaman->penerimaKembali ? $peminjaman->penerimaKembali->name : 'Petugas QC Gudang' }}</div>
                                <div class="d-flex align-items-center gap-1 text-muted small mt-1" style="font-size: 0.725rem;">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $peminjaman->diterima_kembali_at ? $peminjaman->diterima_kembali_at->format('d M Y, H:i') : 'Pengembalian belum diproses' }}</span>
                                </div>
                                @if ($peminjaman->catatan_pengembalian)
                                    <div class="p-2 rounded-2 mt-2 small subtle-strip" style="font-size: 0.75rem;">
                                        <i class="bi bi-clipboard-check me-1 text-success"></i><strong>Catatan QC:</strong> {{ $peminjaman->catatan_pengembalian }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Ringkasan Entitas & Alokasi -->
                <div class="card card-custom p-3 p-sm-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 text-info" style="background: rgba(56, 189, 248, 0.15);">
                            <i class="bi bi-building-check fs-6"></i>
                        </div>
                        <h6 class="fw-bold text-adaptive mb-0">Entitas &amp; Alokasi Cabang</h6>
                    </div>

                    <div class="d-flex flex-column gap-2.5">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 subtle-strip">
                            <span class="small text-muted">Perusahaan:</span>
                            <span class="small fw-bold text-adaptive">{{ $peminjaman->company ? $peminjaman->company->nama_perusahaan : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 subtle-strip">
                            <span class="small text-muted">Cabang Operasional:</span>
                            <span class="small fw-semibold text-adaptive">{{ $peminjaman->branch ? $peminjaman->branch->nama_cabang : 'Kantor Pusat' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 subtle-strip">
                            <span class="small text-muted">Total Varian Barang:</span>
                            <span class="badge badge-cinematic-muted">{{ $peminjaman->details->count() }} Jenis</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 subtle-strip">
                            <span class="small text-muted">Total Kuantitas Fisik:</span>
                            <span class="badge badge-cinematic-indigo">{{ number_format($peminjaman->details->sum('qty_pinjam'), 0) }} Unit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: APPROVE (MENGETAHUI) -->
    <div class="modal fade" id="modal-approve" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-cinematic">
                <div class="modal-header modal-header-cinematic">
                    <h6 class="modal-title fw-bold text-adaptive mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Persetujuan Peminjaman (Mengetahui)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-body mb-3">Sebagai atasan/manager yang mengetahui, Anda menyetujui peminjaman barang ini untuk pameran <strong>{{ $peminjaman->nama_acara }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-adaptive">Catatan / Instruksi Khusus (Opsional)</label>
                        <textarea class="form-control form-control-sm form-control-filter" id="approve-catatan" rows="2" placeholder="Contoh: Pastikan barang dijaga baik-baik..."></textarea>
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4">
                    <button type="button" class="btn btn-cinematic-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm px-3 fw-semibold" id="btn-submit-approve">Setujui &amp; Teruskan ke Gudang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: REJECT (TOLAK) -->
    <div class="modal fade" id="modal-reject" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-cinematic">
                <div class="modal-header modal-header-cinematic">
                    <h6 class="modal-title fw-bold text-danger mb-0"><i class="bi bi-x-circle me-2"></i>Tolak Pengajuan Peminjaman</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-adaptive">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm form-control-filter" id="reject-alasan" rows="3" placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4">
                    <button type="button" class="btn btn-cinematic-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 fw-semibold" id="btn-submit-reject">Konfirmasi Tolak</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: BARANG SIAP (GUDANG PACKING) -->
    <div class="modal fade" id="modal-barang-siap" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-cinematic">
                <div class="modal-header modal-header-cinematic">
                    <h6 class="modal-title fw-bold text-info mb-0"><i class="bi bi-box-seam me-2"></i>Konfirmasi Fisik Barang Siap</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-body mb-3">Pastikan fisik barang di bawah ini sudah lengkap dan selesai disiapkan oleh tim gudang:</p>
                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25 mb-3">
                        <table class="table-cinematic-detail table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-center" style="width: 120px;">Qty Pinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peminjaman->details as $d)
                                <tr>
                                    <td class="text-adaptive">{{ $d->barang ? $d->barang->nama_barang : '-' }}</td>
                                    <td class="text-center fw-bold text-cyan">{{ number_format($d->qty_pinjam, 0) }} {{ $d->satuan ? $d->satuan->nama_satuan : ($d->barang && $d->barang->satuan ? $d->barang->satuan->nama_satuan : 'Unit') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center gap-2 text-muted small p-2 rounded-2 bg-body-tertiary border border-secondary border-opacity-25">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Tanda tangan petugas gudang akan otomatis disematkan dari akun Anda.</span>
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4">
                    <button type="button" class="btn btn-cinematic-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info text-dark btn-sm px-3 fw-semibold" id="btn-submit-barang-siap"><i class="bi bi-check-circle me-1"></i>Konfirmasi Barang Siap</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: PENGEMBALIAN BARANG (QC GUDANG) -->
    <div class="modal fade" id="modal-pengembalian" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content modal-content-cinematic">
                <div class="modal-header modal-header-cinematic">
                    <h6 class="modal-title fw-bold text-success mb-0"><i class="bi bi-arrow-return-left me-2"></i>Pemeriksaan QC &amp; Pengembalian Barang ke Gudang</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 small mb-3 text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Barang yang berstatus <strong>Kembali Bagus</strong> akan otomatis menambah kembali stok di <strong>{{ $peminjaman->gudangAsal ? $peminjaman->gudangAsal->nama_gudang : 'Gudang Asal' }}</strong>.
                    </div>

                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25 mb-3">
                        <table class="table-cinematic-detail table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-center" style="width: 90px;">Qty Pinjam</th>
                                    <th class="text-center text-success" style="width: 110px;">Bagus</th>
                                    <th class="text-center text-warning" style="width: 110px;">Rusak</th>
                                    <th class="text-center text-danger" style="width: 110px;">Hilang</th>
                                    <th style="width: 220px;">Catatan Kerusakan/Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peminjaman->details as $d)
                                <tr class="return-row" data-detail-id="{{ $d->id }}" data-qty-pinjam="{{ floatval($d->qty_pinjam) }}">
                                    <td>
                                        <div class="fw-bold text-adaptive">{{ $d->barang ? $d->barang->nama_barang : '-' }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-cyan">{{ number_format($d->qty_pinjam, 0) }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm form-control-filter text-center return-bagus" value="{{ floatval($d->qty_pinjam) }}" min="0" max="{{ floatval($d->qty_pinjam) }}">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm form-control-filter text-center return-rusak" value="0" min="0" max="{{ floatval($d->qty_pinjam) }}">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm form-control-filter text-center return-hilang" value="0" min="0" max="{{ floatval($d->qty_pinjam) }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm form-control-filter return-catatan" placeholder="Kondisi fisik...">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-adaptive">Catatan Umum Pengembalian</label>
                        <textarea class="form-control form-control-sm form-control-filter" id="return-catatan-umum" rows="2" placeholder="Catatan keseluruhan berita acara pengembalian..."></textarea>
                    </div>

                    <div class="d-flex align-items-center gap-2 text-muted small p-2 rounded-2 bg-body-tertiary border border-secondary border-opacity-25 mb-3">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Tanda tangan pemeriksa QC gudang akan otomatis disematkan dari akun Anda.</span>
                    </div>
                </div>
                <div class="modal-footer modal-footer-cinematic py-2.5 px-4">
                    <button type="button" class="btn btn-cinematic-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm px-3 fw-semibold" id="btn-submit-pengembalian">Selesaikan Pengembalian &amp; Balikkan Stok</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Foto Produk (Cinematic Zoom) -->
    <div class="modal fade" id="modalPreviewFoto" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-cinematic">
                <div class="modal-header modal-header-cinematic d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-cinematic-cyan px-2.5 py-1.5" id="previewFotoKode">-</span>
                        <h6 class="modal-title fw-bold text-adaptive mb-0" id="previewFotoNama">Foto Produk</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="position-relative d-inline-block rounded-3 overflow-hidden border border-secondary border-opacity-25 p-1" style="max-height: 65vh; background: var(--thumb-bg);">
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
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            const peminjamanId = {{ $peminjaman->id }};

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

            // 1. Submit Action (Draft -> Menunggu)
            $('#btn-submit-action').on('click', function () {
                Swal.fire({
                    title: 'Ajukan Peminjaman?',
                    text: 'Dokumen akan diteruskan ke Atasan (Mengetahui) untuk disetujui.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5'
                }).then((r) => {
                    if (r.isConfirmed) {
                        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                        $.post(`/peminjaman-barang/${peminjamanId}/submit`, function (res) {
                            if (res.success) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                            }
                        }).fail((xhr) => Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal submit' }));
                    }
                });
            });

            // 2. Approve Action (Mengetahui - Otomatis Menggunakan TTD Akun User)
            $('#btn-submit-approve').on('click', function () {
                const catatan = $('#approve-catatan').val();
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

                $.post(`/peminjaman-barang/${peminjamanId}/approve`, {
                    use_saved_signature: 1,
                    catatan_mengetahui: catatan
                }, function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Disetujui!', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                        $('#btn-submit-approve').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i>Setujui & Teruskan ke Gudang');
                    }
                }).fail((xhr) => {
                    $('#btn-submit-approve').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i>Setujui & Teruskan ke Gudang');
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal menyetujui' });
                });
            });

            // 2B. Reject Action
            $('#btn-submit-reject').on('click', function () {
                const alasan = $('#reject-alasan').val();
                if (!alasan) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan penolakan wajib diisi!' });
                    return;
                }

                $(this).prop('disabled', true).text('Memproses...');
                $.post(`/peminjaman-barang/${peminjamanId}/reject`, { alasan_penolakan: alasan }, function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'info', title: 'Ditolak!', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                        $('#btn-submit-reject').prop('disabled', false).text('Konfirmasi Tolak');
                    }
                }).fail((xhr) => {
                    $('#btn-submit-reject').prop('disabled', false).text('Konfirmasi Tolak');
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal menolak' });
                });
            });

            // 3. Konfirmasi Barang Siap (Gudang Packing - Cepat & Otomatis TTD)
            $('#btn-submit-barang-siap').on('click', function () {
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

                $.post(`/peminjaman-barang/${peminjamanId}/barang-siap`, {
                    use_saved_signature: 1
                }, function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Siap Diambil!', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                        $('#btn-submit-barang-siap').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Konfirmasi Barang Siap');
                    }
                }).fail((xhr) => {
                    $('#btn-submit-barang-siap').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Konfirmasi Barang Siap');
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal konfirmasi' });
                });
            });

            // 4. Serah Terima & Keluarkan Barang (Trigger Perpindahan Stok Fisik ke Gudang Transit Pameran)
            $('#btn-serah-terima').on('click', function () {
                Swal.fire({
                    title: 'Serah Terima Barang Pameran?',
                    html: `Dengan konfirmasi ini, <strong>stok barang fisik akan otomatis berpindah</strong> dari Gudang Asal ke Gudang Transit Pameran (stand/booth).`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Serahkan Barang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#7c3aed'
                }).then((r) => {
                    if (r.isConfirmed) {
                        Swal.fire({ title: 'Memproses perpindahan stok...', didOpen: () => Swal.showLoading() });
                        $.post(`/peminjaman-barang/${peminjamanId}/serah-terima`, function (res) {
                            if (res.success) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                            }
                        }).fail((xhr) => Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal serah terima' }));
                    }
                });
            });

            // 5. Pengembalian Barang & QC (Trigger Stok Masuk Balik ke Gudang Asal - Cepat & Otomatis TTD)
            $('#btn-submit-pengembalian').on('click', function () {
                const returns = {};
                let valid = true;

                $('.return-row').each(function () {
                    const detailId = $(this).data('detail-id');
                    const qtyPinjam = parseFloat($(this).data('qty-pinjam'));
                    const qBagus = parseFloat($(this).find('.return-bagus').val()) || 0;
                    const qRusak = parseFloat($(this).find('.return-rusak').val()) || 0;
                    const qHilang = parseFloat($(this).find('.return-hilang').val()) || 0;
                    const catatan = $(this).find('.return-catatan').val();

                    const sumReturn = Math.round((qBagus + qRusak + qHilang) * 10000) / 10000;
                    const expectedQty = Math.round(qtyPinjam * 10000) / 10000;
                    if (Math.abs(sumReturn - expectedQty) > 0.00001) {
                        valid = false;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan QC',
                            text: `Total Qty (Bagus + Rusak + Hilang) harus sama dengan Qty Pinjam (${qtyPinjam})! Total diinput: ${sumReturn}`
                        });
                        return false;
                    }

                    returns[detailId] = {
                        qty_kembali_bagus: qBagus,
                        qty_kembali_rusak: qRusak,
                        qty_hilang: qHilang,
                        kondisi_akhir: catatan
                    };
                });

                if (!valid) return;

                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

                $.post(`/peminjaman-barang/${peminjamanId}/kembalikan`, {
                    returns: returns,
                    use_saved_signature: 1,
                    catatan_pengembalian: $('#return-catatan-umum').val()
                }, function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Selesai!', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                        $('#btn-submit-pengembalian').prop('disabled', false).text('Selesaikan Pengembalian & Balikkan Stok');
                    }
                }).fail((xhr) => {
                    $('#btn-submit-pengembalian').prop('disabled', false).text('Selesaikan Pengembalian & Balikkan Stok');
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Gagal pengembalian' });
                });
            });
        });
    </script>
    @endpush
</x-layouts>
