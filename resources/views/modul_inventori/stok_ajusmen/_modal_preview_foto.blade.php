{{-- MODAL PREVIEW FOTO BARANG --}}
<div class="modal fade" id="modalPreviewFotoBarang" tabindex="-1" aria-hidden="true" style="z-index: 1070; background: rgba(15, 23, 42, 0.7) !important;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3.5 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-25 text-primary p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-image fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white" id="previewFotoModalTitle">Pratinjau Foto Barang</h6>
                        <small class="text-white-50" id="previewFotoModalSub">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="min-height: 280px;">
                <div id="wrapperPreviewImg" class="w-100">
                    <img id="previewFotoImg" src="" alt="Foto Barang" class="img-fluid rounded-3 shadow border d-none" style="max-height: 420px; width: auto; object-fit: contain;">
                    <div id="previewFotoEmpty" class="text-center py-5 text-muted">
                        <i class="bi bi-image fs-1 d-block opacity-25 mb-2"></i>
                        <span class="fw-semibold">Barang ini belum memiliki foto persediaan.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3 justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalPreviewFoto(fotoUrl, titleText = '') {
        $('#previewFotoModalSub').text(titleText || 'Foto Persediaan Barang');

        if (fotoUrl && fotoUrl !== 'null' && fotoUrl !== 'undefined') {
            $('#previewFotoImg').attr('src', fotoUrl).removeClass('d-none');
            $('#previewFotoEmpty').addClass('d-none');
        } else {
            $('#previewFotoImg').attr('src', '').addClass('d-none');
            $('#previewFotoEmpty').removeClass('d-none');
        }

        $('#modalPreviewFotoBarang').modal('show');
    }
</script>
@endpush
