{{-- ===== MODAL IMPORT PERUSAHAAN ===== --}}
<x-modal_form id="modalImportPerusahaan" formId="formImportPerusahaan" action="{{ route('companies.import') }}" title="<i class='bi bi-file-earmark-arrow-up-fill me-2'></i> Import Data Perusahaan (PT)" btnSubmitId="btnProsesImportPT" btnSubmitText="Proses Import" autocomplete="off" enctype="multipart/form-data">
    <x-slot name="slot">
        <div class="alert alert-info border-0 rounded-4 p-3 d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-info-circle-fill fs-4 text-info flex-shrink-0"></i>
            <div class="small">
                <strong>Petunjuk Import Excel:</strong>
                <ol class="ps-3 mb-0 mt-1">
                    <li>Unduh template file Excel terlebih dahulu.</li>
                    <li>Isi data Kode PT, Nama Perusahaan, NPWP, NIB, Direktur, Telepon, Email, dll.</li>
                    <li>Unggah kembali file Excel yang telah diisi.</li>
                </ol>
            </div>
        </div>

        <div class="mb-3 text-center">
            <a href="{{ route('companies.import.template') }}" class="btn btn-sm btn-outline-success rounded-pill px-4 no-loader">
                <i class="bi bi-download me-1"></i> Unduh Template Excel (.xlsx)
            </a>
        </div>

        <div class="mb-3">
            <label for="excel_file_pt" class="form-label fw-semibold">Pilih File Excel (.xlsx, .csv)</label>
            <input type="file" name="excel_file" id="excel_file_pt" class="form-control form-control-custom" accept=".xlsx, .csv, .txt" required autocomplete="off">
        </div>
    </x-slot>
</x-modal_form>
