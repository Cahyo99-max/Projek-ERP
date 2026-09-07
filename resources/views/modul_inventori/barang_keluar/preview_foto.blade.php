{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL PREVIEW FOTO BARANG                                  --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalPreviewFoto" tabindex="-1" aria-hidden="true" style="z-index: 1095 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1096 !important;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white rounded-top-3 py-2.5">
                <h6 class="modal-title fw-bold" id="modalPreviewTitle"><i class="bi bi-image me-2 text-danger"></i>Foto Barang</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="previewFotoImg" src="" class="img-fluid rounded-3 shadow-sm mb-2" style="max-height: 420px; object-fit: contain;">
                <h6 id="previewFotoCaption" class="fw-bold text-body-emphasis mb-0"></h6>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.openPreviewFoto = function (url, caption) {
    if (!url) return;
    $('#previewFotoImg').attr('src', url);
    $('#previewFotoCaption').text(caption || '');
    
    const modalEl = document.getElementById('modalPreviewFoto');
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }
};
</script>
@endpush
