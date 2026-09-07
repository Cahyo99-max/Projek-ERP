<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-dark">Master & Mapping Jenis Biaya</h5>
        <p class="text-muted small m-0">Pengelolaan master data jenis biaya operasional dan pemetaan akun COA-nya.</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJenisBiaya">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jenis Biaya
        </button>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tableMasterExpense">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th>KODE BIAYA</th>
                <th>NAMA JENIS BIAYA</th>
                <th class="text-center">MASUK HPP</th>
                <th>ALOKASI BIAYA</th>
                <th>AKUN COA AKUNTANSI</th>
                <th class="text-center">STATUS</th>
                <th style="width: 100px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($expenseTypes as $idx => $exp)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace text-muted">{{ $exp->kode_biaya ?: '-' }}</td>
                <td class="fw-bold text-dark">{{ $exp->nama_jenis_biaya }}</td>
                <td class="text-center">
                    @if($exp->masuk_hpp)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Ya</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">Tidak</span>
                    @endif
                </td>
                <td>{{ $exp->alokasi_biaya }}</td>
                <td>
                    @if($exp->coa)
                        <span class="badge bg-light text-primary border font-monospace px-2.5 py-1">
                            {{ $exp->coa->code }} - {{ $exp->coa->name }}
                        </span>
                    @else
                        <span class="text-muted italic">- Belum Di-mapping -</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($exp->status == 'Active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Aktif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Nonaktif</span>
                    @endif
                </td>
                <td class="text-center">
                    <button class="btn btn-action-circle me-1 btn-edit-exp" data-id="{{ $exp->id }}" title="Edit">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </button>
                    <button class="btn btn-action-circle text-danger btn-delete-exp" data-id="{{ $exp->id }}" title="Hapus">
                        <i class="bi bi-trash fs-6"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
