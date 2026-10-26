<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold m-0 text-body">Master Tipe Akun</h5>
        <p class="text-body-secondary small m-0">Kelola kategori tipe akun keuangan khusus <strong>{{ session('active_company_name') ?? 'Perusahaan Aktif' }}</strong> (Aset, Kewajiban, Ekuitas, Pendapatan, HPP, Beban).</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('master-tipe-akuns.export') }}" id="btnExportMasterTipeAkun" class="btn btn-outline-success btn-sm px-3 rounded-3 shadow-2xs fw-semibold no-loader" data-no-loader="true" title="Export Data ke Excel">
            <i class="bi bi-file-earmark-excel me-1.5"></i> Export Excel
        </a>
        <button class="btn btn-outline-primary btn-sm px-3 rounded-3 shadow-2xs fw-semibold" data-bs-toggle="modal" data-bs-target="#modalImportMasterTipeAkun" title="Import Data dari Excel">
            <i class="bi bi-cloud-arrow-up me-1.5"></i> Import Excel
        </button>
        <button class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahMasterTipeAkun">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Akun
        </button>
    </div>
</div>

<div class="table-responsive p-1">
    <table class="table table-akuntansi align-middle mb-0" id="tableMasterType">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th>KODE TIPE</th>
                <th>NAMA TIPE AKUN</th>
                <th>KATEGORI LAPORAN</th>
                <th class="text-center">SALDO NORMAL DEFAULT</th>
                <th class="text-center">STATUS</th>
                <th style="width: 100px;" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($accountTypes as $idx => $t)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace text-primary fw-bold">{{ $t->kode_tipe }}</td>
                <td class="fw-bold text-dark">{{ $t->nama_tipe }}</td>
                <td>
                    <span class="badge rounded-pill {{ $t->kategori_laporan == 'neraca' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle' }}">
                        {{ $t->kategori_laporan_label }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge {{ strtolower($t->saldo_normal_label) == 'debit' ? 'badge-debit' : 'badge-kredit' }}">
                        {{ $t->saldo_normal_label }}
                    </span>
                </td>
                <td class="text-center">
                    @if($t->status == 'Active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Aktif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Nonaktif</span>
                    @endif
                </td>
                <td class="text-center">
                    <button class="btn btn-action-circle me-1 btn-edit-type" data-id="{{ $t->id }}" title="Edit">
                        <i class="bi bi-pencil-square fs-6"></i>
                    </button>
                    <button class="btn btn-action-circle text-danger btn-delete-type" data-id="{{ $t->id }}" title="Hapus">
                        <i class="bi bi-trash fs-6"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
