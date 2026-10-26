<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-body-emphasis"><i class="bi bi-wallet2 text-primary me-2"></i>Mapping Kategori Kas & Bank</h5>
        <p class="text-secondary small m-0">Konfigurasi kategori penerimaan (Kas Masuk) & pengeluaran (Kas Keluar) beserta Akun Lawan (COA) otomatis.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalTambahKasKategori">
            <i class="bi bi-plus-circle fs-6"></i>
            <span class="fw-semibold">Tambah Kategori</span>
        </button>
    </div>
</div>

<!-- Filter Pills Tipe Kategori (Adaptive Light / Dark Theme) -->
<div class="filter-bar-kas-kategori d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 p-2.5 rounded-3">
    <div class="d-flex align-items-center gap-2 flex-wrap" id="kasKategoriFilterPills">
        <button type="button" class="kas-pill kas-pill-all active" data-filter="all">
            <span>Semua</span>
            <span class="badge rounded-pill ms-1">{{ $kasKategoriMappings->count() }}</span>
        </button>
        <button type="button" class="kas-pill kas-pill-masuk" data-filter="Masuk">
            <i class="bi bi-arrow-down-left-circle me-1"></i>
            <span>Kas Masuk</span>
            <span class="badge rounded-pill ms-1">{{ $kasKategoriMappings->where('tipe', 'Masuk')->count() }}</span>
        </button>
        <button type="button" class="kas-pill kas-pill-keluar" data-filter="Keluar">
            <i class="bi bi-arrow-up-right-circle me-1"></i>
            <span>Kas Keluar</span>
            <span class="badge rounded-pill ms-1">{{ $kasKategoriMappings->where('tipe', 'Keluar')->count() }}</span>
        </button>
    </div>
    <div class="small kas-filter-hint d-none d-md-flex align-items-center gap-1.5">
        <i class="bi bi-info-circle text-primary"></i>
        <span>Kategori aktif otomatis muncul pada formulir Kas Masuk & Kas Keluar</span>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tableKasKategoriMapping">
        <thead>
            <tr>
                <th style="width: 45px;" class="text-center">NO</th>
                <th style="width: 125px;">TIPE</th>
                <th>NAMA KATEGORI</th>
                <th>AKUN LAWAN (COA) DEFAULT</th>
                <th>DESKRIPSI / KEGUNAAN</th>
                <th style="width: 110px;" class="text-center">STATUS</th>
                <th style="width: 90px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($kasKategoriMappings as $idx => $item)
            <tr data-tipe="{{ $item->tipe }}">
                <td class="text-center font-monospace text-secondary">{{ $idx + 1 }}</td>
                <td>
                    @if($item->tipe == 'Masuk')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                            <i class="bi bi-arrow-down-left me-1"></i>Kas Masuk
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold">
                            <i class="bi bi-arrow-up-right me-1"></i>Kas Keluar
                        </span>
                    @endif
                </td>
                <td>
                    <span class="fw-bold text-body-emphasis">{{ $item->nama_kategori }}</span>
                </td>
                <td>
                    @if($item->coa)
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                {{ $item->coa->code }}
                            </span>
                            <span class="text-body-emphasis fw-semibold">{{ $item->coa->name }}</span>
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
                        <input class="form-check-input btn-toggle-kas-kategori" type="checkbox" role="switch" 
                            data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} 
                            title="Klik untuk ubah status aktif/nonaktif">
                    </div>
                    <span class="d-block text-xxs {{ $item->is_active ? 'text-success fw-semibold' : 'text-danger' }}">
                        {{ $item->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button class="btn btn-action-circle btn-edit-kas-kategori" data-id="{{ $item->id }}" title="Edit Kategori">
                            <i class="bi bi-pencil-square fs-6 text-primary"></i>
                        </button>
                        <button class="btn btn-action-circle text-danger btn-delete-kas-kategori" data-id="{{ $item->id }}" data-nama="{{ e($item->nama_kategori) }}" title="Hapus Kategori">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
/* ─── Filter Bar Container ─── */
.filter-bar-kas-kategori {
    background-color: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: all 0.25s ease;
}

[data-bs-theme="dark"] .filter-bar-kas-kategori,
html[data-theme="dark"] .filter-bar-kas-kategori,
.dark-mode .filter-bar-kas-kategori {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.3);
}

.kas-filter-hint {
    color: #64748b;
}
[data-bs-theme="dark"] .kas-filter-hint,
html[data-theme="dark"] .kas-filter-hint,
.dark-mode .kas-filter-hint {
    color: #94a3b8 !important;
}

/* ─── Filter Pills Base ─── */
.kas-pill {
    font-size: 0.8125rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 5px 14px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    border: 1px solid transparent;
    cursor: pointer;
    user-select: none;
    line-height: 1.4;
}

/* Pill 1: Semua */
.kas-pill-all {
    background-color: #ffffff;
    color: #475569;
    border-color: #cbd5e1;
}
.kas-pill-all:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}
.kas-pill-all.active {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
}
.kas-pill-all .badge {
    background-color: #e2e8f0;
    color: #475569;
}
.kas-pill-all.active .badge {
    background-color: #ffffff !important;
    color: #2563eb !important;
}

/* Pill 2: Kas Masuk */
.kas-pill-masuk {
    background-color: #ffffff;
    color: #16a34a;
    border-color: #bbf7d0;
}
.kas-pill-masuk:hover {
    background-color: #f0fdf4;
    color: #15803d;
}
.kas-pill-masuk.active {
    background-color: #16a34a !important;
    border-color: #16a34a !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
}
.kas-pill-masuk .badge {
    background-color: #dcfce7;
    color: #16a34a;
}
.kas-pill-masuk.active .badge {
    background-color: #ffffff !important;
    color: #16a34a !important;
}

/* Pill 3: Kas Keluar */
.kas-pill-keluar {
    background-color: #ffffff;
    color: #dc2626;
    border-color: #fecaca;
}
.kas-pill-keluar:hover {
    background-color: #fef2f2;
    color: #b91c1c;
}
.kas-pill-keluar.active {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
}
.kas-pill-keluar .badge {
    background-color: #fee2e2;
    color: #dc2626;
}
.kas-pill-keluar.active .badge {
    background-color: #ffffff !important;
    color: #dc2626 !important;
}

/* ─── Dark Mode Specific Overrides for Pills ─── */
[data-bs-theme="dark"] .kas-pill-all,
html[data-theme="dark"] .kas-pill-all,
.dark-mode .kas-pill-all {
    background-color: #1e293b;
    color: #cbd5e1;
    border-color: #334155;
}
[data-bs-theme="dark"] .kas-pill-all:hover,
html[data-theme="dark"] .kas-pill-all:hover,
.dark-mode .kas-pill-all:hover {
    background-color: #334155;
    color: #f8fafc;
}
[data-bs-theme="dark"] .kas-pill-all .badge,
html[data-theme="dark"] .kas-pill-all .badge,
.dark-mode .kas-pill-all .badge {
    background-color: #334155;
    color: #cbd5e1;
}

[data-bs-theme="dark"] .kas-pill-masuk,
html[data-theme="dark"] .kas-pill-masuk,
.dark-mode .kas-pill-masuk {
    background-color: rgba(22, 163, 74, 0.12);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.3);
}
[data-bs-theme="dark"] .kas-pill-masuk:hover,
html[data-theme="dark"] .kas-pill-masuk:hover,
.dark-mode .kas-pill-masuk:hover {
    background-color: rgba(22, 163, 74, 0.22);
    color: #86efac;
    border-color: rgba(34, 197, 94, 0.45);
}
[data-bs-theme="dark"] .kas-pill-masuk .badge,
html[data-theme="dark"] .kas-pill-masuk .badge,
.dark-mode .kas-pill-masuk .badge {
    background-color: rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

[data-bs-theme="dark"] .kas-pill-keluar,
html[data-theme="dark"] .kas-pill-keluar,
.dark-mode .kas-pill-keluar {
    background-color: rgba(220, 38, 38, 0.12);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}
[data-bs-theme="dark"] .kas-pill-keluar:hover,
html[data-theme="dark"] .kas-pill-keluar:hover,
.dark-mode .kas-pill-keluar:hover {
    background-color: rgba(220, 38, 38, 0.22);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.45);
}
[data-bs-theme="dark"] .kas-pill-keluar .badge,
html[data-theme="dark"] .kas-pill-keluar .badge,
.dark-mode .kas-pill-keluar .badge {
    background-color: rgba(239, 68, 68, 0.2);
    color: #f87171;
}
</style>
