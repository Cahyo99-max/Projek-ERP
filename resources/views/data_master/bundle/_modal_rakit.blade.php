<div class="modal fade" id="modalRakitBundle" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content spk-modal-content rounded-4 border-0 shadow-lg">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 text-warning bg-warning bg-opacity-15">
                        <i class="bi bi-tools fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.05rem;">Input Perakitan Paket Bundle (Assembly / Kitting)</h5>
                        <small class="text-white-50" style="font-size: 0.8rem;">Proses perakitan komponen menjadi Paket Set siap jual & potong stok otomatis.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <form id="formRakitBundle" autocomplete="off">
                @csrf
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    
                    <div class="spk-card-section p-3.5 rounded-3 mb-3">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Pilih Paket Bundle <span class="text-danger">*</span></label>
                                <select name="parent_barang_id" id="rakit_parent_barang_id" class="form-select form-select-sm spk-form-input fw-bold" required onchange="loadRakitComponentPreview()">
                                    <option value="">-- Pilih Paket Bundle --</option>
                                    @foreach($activeBundles as $ab)
                                        <option value="{{ $ab->id }}">[{{ $ab->kode_barang }}] {{ $ab->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="rakit_tanggal" class="form-control form-control-sm spk-form-input font-monospace fw-bold" value="{{ date('Y-m-d') }}" required autocomplete="off">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Gudang Asal Komponen <span class="text-danger">*</span></label>
                                <select name="gudang_komponen_id" id="rakit_gudang_komponen_id" class="form-select form-select-sm spk-form-input" required onchange="loadRakitComponentPreview()">
                                    <option value="">-- Pilih Gudang Asal Komponen --</option>
                                    @foreach($gudangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Gudang Tujuan Hasil Bundle <span class="text-danger">*</span></label>
                                <select name="gudang_bundle_id" id="rakit_gudang_bundle_id" class="form-select form-select-sm spk-form-input" required>
                                    <option value="">-- Pilih Gudang Tujuan --</option>
                                    @foreach($gudangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Jumlah Paket Dirakit (Set) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text spk-form-addon font-monospace fw-bold text-warning"><i class="bi bi-box-seam me-1"></i>+</span>
                                    <input type="number" name="jumlah_paket" id="rakit_jumlah_paket" class="form-control spk-form-input text-center font-monospace fw-bold" value="1" min="1" step="1" required oninput="loadRakitComponentPreview()" autocomplete="off">
                                    <span class="input-group-text spk-form-addon" id="rakit_satuan_text">Set</span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Catatan / Keterangan Perakitan</label>
                                <input type="text" name="keterangan" class="form-control form-control-sm spk-form-input" placeholder="Contoh: Perakitan 10 set seragam TNI untuk SO-001" maxlength="255" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    {{-- LIVE PREVIEW KEBUTUHAN STOK KOMPONEN --}}
                    <div class="spk-card-section p-3 rounded-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2 text-info fw-bold font-monospace" style="font-size: 0.85rem;">
                                <i class="bi bi-shield-check"></i> LIVE VALIDASI STOK KOMPONEN DI GUDANG ASAL
                            </div>
                            <span class="badge bg-secondary rounded-pill font-monospace" id="rakit_status_ketersediaan">Pilih Paket & Gudang</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0" id="tablePreviewRakitStok">
                                <thead>
                                    <tr class="spk-table-header-custom small font-monospace">
                                        <th style="width: 40px;" class="text-center">NO</th>
                                        <th>BARANG JADI / PENYUSUN</th>
                                        <th style="width: 120px;" class="text-center">QTY / PAKET</th>
                                        <th style="width: 140px;" class="text-center">TOTAL DIBUTUHKAN</th>
                                        <th style="width: 140px;" class="text-center">STOK GUDANG ASAL</th>
                                        <th style="width: 110px;" class="text-center">STATUS STOK</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPreviewRakitStok">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Pilih Paket Bundle dan Gudang Asal Komponen untuk melihat simulasi perakitan.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3 justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 font-monospace fw-bold text-dark" id="btnSubmitRakitBundle">
                        <i class="bi bi-gear-wide-connected me-1"></i> Proses Perakitan & Potong Stok
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function openRakitBundleModal(bundleId = '') {
        $('#formRakitBundle')[0].reset();
        $('#rakit_tanggal').val(new Date().toISOString().split('T')[0]);
        $('#rakit_jumlah_paket').val(1);
        $('#tbodyPreviewRakitStok').html(`
            <tr>
                <td colspan="6" class="text-center text-muted py-3">Pilih Paket Bundle dan Gudang Asal Komponen untuk melihat simulasi perakitan.</td>
            </tr>
        `);
        $('#rakit_status_ketersediaan').attr('class', 'badge bg-secondary rounded-pill font-monospace').text('Pilih Paket & Gudang');

        if (bundleId) {
            $('#rakit_parent_barang_id').val(bundleId);
        }

        $('#modalRakitBundle').modal('show');

        if (bundleId) {
            loadRakitComponentPreview();
        }
    }

    function loadRakitComponentPreview() {
        const bundleId = $('#rakit_parent_barang_id').val();
        const gudangId = $('#rakit_gudang_komponen_id').val();
        const jumlahPaket = parseFloat($('#rakit_jumlah_paket').val() || 0);

        if (!bundleId || !gudangId || jumlahPaket <= 0) {
            $('#tbodyPreviewRakitStok').html(`
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">Pilih Paket Bundle dan Gudang Asal Komponen untuk melihat simulasi perakitan.</td>
                </tr>
            `);
            $('#rakit_status_ketersediaan').attr('class', 'badge bg-secondary rounded-pill font-monospace').text('Pilih Paket & Gudang');
            $('#btnSubmitRakitBundle').prop('disabled', false);
            return;
        }

        $('#tbodyPreviewRakitStok').html(`
            <tr>
                <td colspan="6" class="text-center py-3 text-info">
                    <span class="spinner-border spinner-border-sm me-1"></span> Mengkalkulasi ketersediaan stok komponen...
                </td>
            </tr>
        `);

        $.ajax({
            url: `/master-bundle/${bundleId}/edit`,
            method: 'GET',
            data: { gudang_id: gudangId },
            success: function(res) {
                if (res.success && res.components) {
                    let html = '';
                    let allSufficient = true;

                    if (res.components.length === 0) {
                        $('#tbodyPreviewRakitStok').html(`
                            <tr>
                                <td colspan="6" class="text-center text-danger py-3">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Paket Bundle ini belum memiliki komponen penyusun.
                                </td>
                            </tr>
                        `);
                        $('#rakit_status_ketersediaan').attr('class', 'badge bg-danger rounded-pill font-monospace').text('Tidak Ada Komponen');
                        $('#btnSubmitRakitBundle').prop('disabled', true);
                        return;
                    }

                    res.components.forEach((c, idx) => {
                        const totalNeeded = (parseFloat(c.qty_per_bundle) * jumlahPaket);
                        const isStockEnough = c.stok_gudang >= totalNeeded;

                        if (!isStockEnough) {
                            allSufficient = false;
                        }

                        const statusBadge = isStockEnough 
                            ? `<span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 font-monospace"><i class="bi bi-check-circle-fill me-1"></i>Cukup</span>`
                            : `<span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 font-monospace"><i class="bi bi-x-circle-fill me-1"></i>Kurang</span>`;

                        const rowBg = isStockEnough ? '' : 'table-danger';

                        html += `
                            <tr class="${rowBg}">
                                <td class="text-center font-monospace text-muted">${idx + 1}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold font-monospace text-primary" style="font-size:12px;">[${c.kode_barang}] ${c.nama_barang}</span>
                                    </div>
                                </td>
                                <td class="text-center font-monospace fw-semibold">${numberFormatLocal(c.qty_per_bundle)} ${c.satuan_singkatan}</td>
                                <td class="text-center font-monospace fw-bold text-primary">${numberFormatLocal(totalNeeded)} ${c.satuan_singkatan}</td>
                                <td class="text-center font-monospace fw-bold ${isStockEnough ? 'text-success' : 'text-danger'}">${numberFormatLocal(c.stok_gudang)} ${c.satuan_singkatan}</td>
                                <td class="text-center">${statusBadge}</td>
                            </tr>
                        `;
                    });

                    $('#tbodyPreviewRakitStok').html(html);

                    if (allSufficient) {
                        $('#rakit_status_ketersediaan').attr('class', 'badge bg-success rounded-pill font-monospace').html('<i class="bi bi-check-lg me-1"></i>Stok Komponen Cukup');
                        $('#btnSubmitRakitBundle').prop('disabled', false);
                    } else {
                        $('#rakit_status_ketersediaan').attr('class', 'badge bg-danger rounded-pill font-monospace').html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Stok Komponen Kurang');
                        $('#btnSubmitRakitBundle').prop('disabled', true);
                    }
                }
            },
            error: function() {
                $('#tbodyPreviewRakitStok').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger py-3">Gagal memuat data simulasi perakitan.</td>
                    </tr>
                `);
            }
        });
    }

    function numberFormatLocal(val) {
        return parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    $('#formRakitBundle').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSubmitRakitBundle');
        const originalText = $btn.html();
        const formData = $(this).serialize();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses Perakitan...');

        $.ajax({
            url: "{{ route('master-bundle.rakit') }}",
            method: 'POST',
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Perakitan Paket Bundle Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#modalRakitBundle').modal('hide');
                    if (typeof tableBundle !== 'undefined') tableBundle.ajax.reload();
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memproses Perakitan',
                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem saat memotong/menambah stok.'
                });
            }
        });
    });
</script>
@endpush
