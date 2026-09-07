<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-dark">Master & Mapping Tipe Pengeluaran</h5>
        <p class="text-muted small m-0">Pengelolaan master data tipe pengeluaran barang (Goods Issue) dan pemetaan akun COA-nya.</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTipePengeluaran">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Pengeluaran
        </button>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tableMasterTipePengeluaran">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th>KODE TIPE</th>
                <th>NAMA TIPE PENGELUARAN</th>
                <th>DESKRIPSI</th>
                <th>AKUN COA PENGELUARAN</th>
                <th class="text-center">STATUS</th>
                <th style="width: 100px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($issueTypes as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace text-muted">{{ $item->kode_tipe ?: '-' }}</td>
                <td class="fw-bold text-dark">{{ $item->nama_tipe }}</td>
                <td>{{ $item->deskripsi ?: '-' }}</td>
                <td>
                    @if($item->coa)
                        <span class="badge bg-light text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                            {{ $item->coa->code }} - {{ $item->coa->name }}
                        </span>
                    @else
                        <span class="text-muted italic">- Belum Di-mapping -</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($item->status == 'Active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Aktif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Nonaktif</span>
                    @endif
                </td>
                <td class="text-center">
                    <button class="btn btn-action-circle me-1 btn-edit-tipe-pengeluaran" data-id="{{ $item->id }}" title="Edit">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </button>
                    <button class="btn btn-action-circle text-danger btn-delete-tipe-pengeluaran" data-id="{{ $item->id }}" data-nama="{{ $item->nama_tipe }}" title="Hapus">
                        <i class="bi bi-trash fs-6"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
