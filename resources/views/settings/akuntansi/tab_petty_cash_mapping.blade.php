<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-body-emphasis"><i class="bi bi-cash-coin text-success me-2"></i>Mapping Kategori Kas Kecil (Petty Cash)</h5>
        <p class="text-secondary small m-0">Konfigurasi kategori transaksi pengeluaran/klaim kas kecil beserta Akun Lawan (COA Beban) otomatis.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-success btn-sm px-3 rounded-3 shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalTambahPettyCashMapping">
            <i class="bi bi-plus-circle fs-6"></i>
            <span class="fw-semibold">Tambah Kategori Kas Kecil</span>
        </button>
    </div>
</div>

<!-- Info Banner -->
<div class="filter-bar-kas-kategori d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 p-2.5 rounded-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="kas-pill kas-pill-all active">
            <i class="bi bi-tags me-1"></i>
            <span>Total Kategori</span>
            <span class="badge rounded-pill ms-1">{{ $pettyCashMappings->count() }}</span>
        </span>
        <span class="kas-pill kas-pill-masuk active">
            <i class="bi bi-check-circle me-1"></i>
            <span>Aktif</span>
            <span class="badge rounded-pill ms-1">{{ $pettyCashMappings->where('is_active', true)->count() }}</span>
        </span>
    </div>
    <div class="small kas-filter-hint d-none d-md-flex align-items-center gap-1.5">
        <i class="bi bi-lightning-charge-fill text-warning"></i>
        <span>Kategori aktif otomatis muncul sebagai chip shortcut & autofill COA di formulir Kas Kecil</span>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tablePettyCashMapping">
        <thead>
            <tr>
                <th style="width: 45px;" class="text-center">NO</th>
                <th style="width: 250px;">NAMA KATEGORI</th>
                <th>AKUN BEBAN (COA LAWAN) DEFAULT</th>
                <th>DESKRIPSI / KEGUNAAN</th>
                <th style="width: 110px;" class="text-center">STATUS</th>
                <th style="width: 90px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @forelse($pettyCashMappings as $idx => $item)
            <tr>
                <td class="text-center font-monospace text-secondary">{{ $idx + 1 }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $badgeColor = $item->warna_badge ?: 'primary';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }}-subtle rounded-pill px-2.5 py-1 fw-bold font-monospace">
                            @if($item->icon)
                                <i class="bi {{ $item->icon }} me-1"></i>
                            @else
                                <i class="bi bi-tag me-1"></i>
                            @endif
                            {{ $item->nama_kategori }}
                        </span>
                    </div>
                </td>
                <td>
                    @if($item->coa)
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                {{ $item->coa->code }}
                            </span>
                            <span class="text-body-emphasis fw-semibold">{{ $item->coa->name }}</span>
                            <span class="text-secondary text-xxs font-monospace">({{ ucfirst($item->coa->type) }})</span>
                        </div>
                    @else
                        <span class="text-secondary fst-italic">- Belum Terhubung COA -</span>
                    @endif
                </td>
                <td>
                    <span class="text-secondary">{{ $item->deskripsi ?: '-' }}</span>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-inline-block m-0">
                        <input class="form-check-input btn-toggle-petty-cash-mapping" type="checkbox" role="switch" 
                            data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} 
                            title="Klik untuk ubah status aktif/nonaktif">
                    </div>
                    <span class="d-block text-xxs {{ $item->is_active ? 'text-success fw-semibold' : 'text-danger' }}">
                        {{ $item->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button class="btn btn-action-circle btn-edit-petty-cash-mapping" data-id="{{ $item->id }}" title="Edit Kategori">
                            <i class="bi bi-pencil-square fs-6 text-primary"></i>
                        </button>
                        <button class="btn btn-action-circle text-danger btn-delete-petty-cash-mapping" data-id="{{ $item->id }}" data-nama="{{ e($item->nama_kategori) }}" title="Hapus Kategori">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-secondary">
                    <i class="bi bi-inbox fs-3 d-block mb-2 text-muted"></i>
                    Belum ada mapping kategori kas kecil. Klik tombol "Tambah Kategori Kas Kecil" di atas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
