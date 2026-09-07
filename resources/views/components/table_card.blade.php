@props(['title', 'icon' => 'bi-table', 'btnTarget' => '', 'btnText' => 'Tambah Data', 'tableId' => 'tableDinamis'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tabel_card.css?v=' . time()) }}">
@endpush

<div class="container-fluid py-3 px-2 px-md-4">
    <div class="card tbl-card border-0">

        {{-- Card Header --}}
        <div class="card-header tbl-card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-0">
            <h5 class="mb-0 fw-bold d-flex align-items-center tbl-card-title">
                <div class="tbl-icon-box me-3">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <span>{!! $title !!}</span>
            </h5>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if (isset($extraActions))
                    {{ $extraActions }}
                @endif
                @if ($btnTarget)
                    <button type="button"
                        class="btn tbl-btn-add rounded-pill d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="{{ $btnTarget }}">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ $btnText }}</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Card Body --}}
        <div class="card-body tbl-card-body px-0 pb-0">
            <div class="table-responsive">
                <table class="table table-hover tbl-table mb-0 align-middle" id="{{ $tableId }}">
                    <thead>
                        <tr>
                            {{ $thead }}
                        </tr>
                    </thead>
                    <tbody>
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
