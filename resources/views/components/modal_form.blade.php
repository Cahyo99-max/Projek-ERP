@props([
    'id',
    'title',
    'action' => '#',
    'formId' => '',
    'size' => 'modal-lg',
    'btnSubmitId' => 'btnSimpan',
    'btnSubmitText' => 'Simpan Data',
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modal_form.css') }}">
@endpush

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" data-bs-focus="false" data-focus="false">
    <div class="modal-dialog {{ $size }} modal-dialog-centered my-4" @if($size === 'modal-xl') style="max-width: 1120px; width: 95vw;" @endif>
        <div class="modal-content modal-content-custom bg-body">
            <div class="modal-header header-cinematic">
                <h5 class="modal-title fw-semibold text-white">
                    {!! $title !!}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="{{ $formId }}" action="{{ $action }}" {{ $attributes->merge(['method' => 'POST']) }}>
                @csrf
                <div class="modal-body p-3 p-md-4 p-lg-4">
                    <div class="alert alert-danger d-none rounded-3" id="{{ $formId }}ErrorAlert"></div>

                    @if (isset($slot) && $slot->isNotEmpty())
                        {{ $slot }}
                    @elseif(isset($content))
                        {{ $content }}
                    @else
                        <p class="text-danger text-center">Form gagal dimuat. Cek pembungkus slot!</p>
                    @endif
                </div>

                <div class="modal-footer border-top-0 py-3 pe-4 shadow-sm"
                    style="background-color: var(--cine-input-bg); border-top: 1px solid var(--cine-border-line) !important;">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cinematic" id="{{ $btnSubmitId }}">
                        {{ $btnSubmitText }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
