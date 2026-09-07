{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg animate__animated animate__fadeInUp" style="border-radius: 20px; overflow: hidden; background-color: var(--bs-body-bg);">
            <div class="modal-header border-0 bg-primary bg-opacity-10 py-4">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-person-badge me-2"></i> Profil Lengkap Karyawan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <div class="row align-items-center mb-4">
                    <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <img id="detail_foto" src="" alt="Foto Karyawan" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover; border-width: 3px; border-color: var(--bs-border-color);">
                            <div class="position-absolute bottom-0 end-0 mb-1 me-1" id="detail_status">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <h3 id="detail_nama_lengkap" class="fw-bold mb-1 h4" style="color: var(--bs-body-color);"></h3>
                        <p class="text-muted mb-3"><i class="bi bi-building"></i> <span id="detail_departemen"></span> • <span id="detail_jabatan"></span></p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-25">
                                <i class="bi bi-upc-scan me-1"></i> <span id="detail_kode_karyawan"></span>
                            </span>
                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill border border-info border-opacity-25">
                                <i class="bi bi-card-text me-1"></i> <span id="detail_nik"></span>
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning border-opacity-25" id="detail_inisial_wrapper">
                                <i class="bi bi-hash me-1"></i> <span id="detail_inisial"></span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <hr class="text-muted opacity-25 mb-4">

                <div class="row g-4">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-telephone fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-1 small fw-semibold kry-detail-label">No. HP / WhatsApp</p>
                                <h6 id="detail_no_hp" class="mb-0 fw-bold" style="color: var(--bs-body-color);"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-gender-ambiguous fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-1 small fw-semibold kry-detail-label">Jenis Kelamin</p>
                                <h6 id="detail_jenis_kelamin" class="mb-0 fw-bold" style="color: var(--bs-body-color);"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-calendar-event fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-1 small fw-semibold kry-detail-label">Tanggal Lahir</p>
                                <h6 id="detail_tanggal_lahir" class="mb-0 fw-bold" style="color: var(--bs-body-color);"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-calendar-check fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-1 small fw-semibold kry-detail-label">Tanggal Masuk Kerja</p>
                                <h6 id="detail_tanggal_masuk" class="mb-0 fw-bold" style="color: var(--bs-body-color);"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-geo-alt fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-1 small fw-semibold kry-detail-label">Alamat Lengkap</p>
                                <h6 id="detail_alamat" class="mb-0 fw-bold" style="white-space: pre-line; color: var(--bs-body-color);"></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== DETAIL VIEW =====
        $(document).on('click', '.btn-view-karyawan', function () {
            let id = parseInt($(this).data('id'));
            if (!id) return;

            $.get('/karyawan/' + id + '/edit', function (res) {
                let k = res.karyawan;
                let photoUrl = k.foto ? '/' + k.foto : (k.jenis_kelamin === 'P' ? '/gambar/perempuan.jpg' : '/gambar/laki.jpg');
                
                $('#detail_foto').attr('src', photoUrl);
                $('#detail_nama_lengkap').text(k.nama_lengkap);
                $('#detail_nik').text(k.nik || '-');
                $('#detail_kode_karyawan').text(k.kode_karyawan || '-');
                $('#detail_no_hp').text(k.no_hp || '-');
                $('#detail_alamat').text(k.alamat || '-');

                if (k.inisial) {
                    $('#detail_inisial').text(k.inisial);
                    $('#detail_inisial_wrapper').show();
                } else {
                    $('#detail_inisial_wrapper').hide();
                }

                if (k.status === 'Aktif') {
                    $('#detail_status').html('<span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>');
                } else {
                    $('#detail_status').html('<span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>');
                }

                let jk = k.jenis_kelamin === 'L' ? 'Laki-laki' : (k.jenis_kelamin === 'P' ? 'Perempuan' : '-');
                $('#detail_jenis_kelamin').text(jk);
                
                let formatDate = function(dateStr) {
                    if (!dateStr) return '-';
                    let parts = dateStr.split('-');
                    if (parts.length === 3) {
                        return parts[2] + '/' + parts[1] + '/' + parts[0];
                    }
                    return dateStr;
                };

                $('#detail_tanggal_lahir').text(formatDate(k.tanggal_lahir));
                $('#detail_tanggal_masuk').text(formatDate(k.tanggal_masuk));
                $('#detail_departemen').text(k.department ? k.department.department_name : '-');
                $('#detail_jabatan').text(k.position ? k.position.position_name : '-');

                $('#modalDetailKaryawan').modal('show');
            }).fail(function () {
                Swal.fire({ title: 'Gagal!', text: 'Gagal memuat profil karyawan.', icon: 'error' });
            });
        });
    });
</script>
@endpush
