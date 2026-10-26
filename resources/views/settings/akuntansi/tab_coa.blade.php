<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-dark">Chart of Account</h5>
        <p class="text-muted small m-0">Daftar akun akuntansi perusahaan.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <form action="{{ route('akuntansi.index') }}" method="GET" class="d-flex gap-2">
            <input type="hidden" name="tab" value="coa">
            <select name="type" class="form-select form-select-sm rounded-3" style="width: 200px;" onchange="this.form.submit()">
                <option value="">Semua Tipe Akun</option>
                @foreach($accountTypes as $t)
                    <option value="{{ $t->id }}" {{ $typeFilter == $t->id ? 'selected' : '' }}>{{ $t->nama_tipe }}</option>
                @endforeach
            </select>
        </form>
        <button class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCoa">
            <i class="bi bi-plus-lg me-1"></i> Tambah Akun
        </button>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tableCoa">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th style="width: 120px;">KODE AKUN</th>
                <th>NAMA AKUN</th>
                <th>TIPE AKUN</th>
                <th style="width: 80px;" class="text-center">LEVEL</th>
                <th style="width: 130px;" class="text-center">SALDO NORMAL</th>
                <th style="width: 90px;" class="text-center">STATUS</th>
                <th style="width: 100px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($accounts as $index => $acc)
            <tr class="{{ $acc->is_header ? 'coa-header-row' : '' }}">
                <td>{{ $index + 1 }}</td>
                <td>
                    <span class="font-monospace {{ $acc->is_header ? 'text-primary fw-bold' : 'text-dark' }}">
                        {{ $acc->code }}
                    </span>
                </td>
                <td>
                    @if($acc->level > 1)
                        <span class="text-muted me-1" style="padding-left: {{ ($acc->level - 1) * 15 }}px;">&rarr;</span>
                    @endif
                    <span class="{{ $acc->is_header ? 'fw-bold text-dark' : '' }}">
                        {{ $acc->name }}
                    </span>
                </td>
                <td>
                    <span class="badge rounded-pill badge-account-type">
                        {{ $acc->type_label }}
                    </span>
                </td>
                <td class="text-center fw-semibold">{{ $acc->level }}</td>
                <td class="text-center">
                    <span class="badge {{ strtolower($acc->normal_balance_label) == 'debit' ? 'badge-debit' : 'badge-kredit' }}">
                        {{ $acc->normal_balance_label }}
                    </span>
                </td>
                <td class="text-center">
                    @if($acc->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Aktif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Nonaktif</span>
                    @endif
                </td>
                <td class="text-center">
                    <button class="btn btn-action-circle me-1 btn-edit-coa" data-id="{{ $acc->id }}" title="Edit">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </button>
                    <button class="btn btn-action-circle text-danger btn-delete-coa" data-id="{{ $acc->id }}" title="Hapus">
                        <i class="bi bi-trash fs-6"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
