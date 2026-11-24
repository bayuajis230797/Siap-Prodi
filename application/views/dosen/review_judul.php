<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Review Judul Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_skripsi/ajuan_judul'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo $pengajuan->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $pengajuan->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Peran Anda</th>
                                <td>
                                    <span class="badge badge-primary">Pembimbing <?php echo $tipe_pembimbing; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Informasi Pengajuan</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Judul Pengajuan</th>
                                <td><?php echo $pengajuan->judul_pengajuan; ?></td>
                            </tr>
                            <tr>
                                <th>Judul Disetujui Sekprodi</th>
                                <td>
                                    <?php echo $pengajuan->judul_disetujui ? $pengajuan->judul_disetujui : '<span class="text-muted">-</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>File Proposal</th>
                                <td>
                                    <?php if (!empty($pengajuan->file_proposal)): ?>
                                        <a href="<?php echo site_url('Bimbingan_skripsi/download_proposal/' . $pengajuan->id); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download Proposal
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status ACC Anda</th>
                                <td>
                                    <?php
                                    $current_status_safe = isset($current_status) && !empty($current_status) ? $current_status : 'pending';
                                    $status_badge = array(
                                        'pending' => 'warning',
                                        'acc' => 'success',
                                        'revisi' => 'danger'
                                    );
                                    $badge_class = isset($status_badge[$current_status_safe]) ? $status_badge[$current_status_safe] : 'secondary';
                                    $status_text = ucfirst($current_status_safe);
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Form ACC/Revisi -->
                <?php 
                $current_status_safe = isset($current_status) && !empty($current_status) ? $current_status : 'pending';
                if ($current_status_safe === 'pending' || $current_status_safe === 'revisi'): ?>
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Review & Tindakan</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?php echo site_url('Bimbingan_skripsi/review_judul/' . $pengajuan->id); ?>">
                                <div class="form-group">
                                    <label>Judul yang Diajukan:</label>
                                    <div class="alert alert-light">
                                        <strong><?php echo $pengajuan->judul_pengajuan; ?></strong>
                                    </div>
                                </div>
                                
                                <?php if (!empty($pengajuan->judul_revisi)): ?>
                                <div class="form-group">
                                    <label>Judul Revisi yang Diajukan Mahasiswa:</label>
                                    <div class="alert alert-info">
                                        <strong><?php echo htmlspecialchars($pengajuan->judul_revisi); ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>


                                <div class="row no-gutters">
                                    <div class="col-6 pr-1">
                                        <button type="submit" name="action" value="acc" class="btn btn-success btn-lg w-100" onclick="return confirm('Apakah Anda yakin ingin meng-ACC judul ini?');">
                                            <i class="fas fa-check-circle"></i> Setujui Judul
                                        </button>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <button type="button" class="btn btn-danger btn-lg w-100" data-toggle="modal" data-target="#modalRevisi">
                                            <i class="fas fa-times-circle"></i> Revisi Judul
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <small class="form-text text-muted text-center d-block">
                                            Klik tombol ini jika judul sudah sesuai dan dapat digunakan.
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="form-text text-muted text-center d-block">
                                            Klik tombol ini jika judul perlu direvisi.
                                        </small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Anda telah meng-ACC judul ini.
                    </div>
                <?php endif; ?>

                <?php if (!empty($is_pembimbing_1)): ?>
                    <div class="card card-outline card-info mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-edit"></i> Ubah Judul Skripsi (Pembimbing 1)</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open('Bimbingan_skripsi/ubah_judul_mahasiswa/' . $pengajuan->id); ?>
                            <div class="form-group">
                                <label for="judul_disetujui">Judul Skripsi <span class="text-danger">*</span></label>
                                <textarea name="judul_disetujui" id="judul_disetujui" class="form-control" rows="3" required minlength="10"><?php echo htmlspecialchars($judul_aktif ?? $pengajuan->judul_pengajuan); ?></textarea>
                                <small class="form-text text-muted">Perubahan judul akan disinkronkan ke data skripsi dan topik bimbingan mahasiswa.</small>
                            </div>
                            <button type="submit" class="btn btn-info" onclick="return confirm('Simpan perubahan judul skripsi mahasiswa?');">
                                <i class="fas fa-save"></i> Simpan Perubahan Judul
                            </button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revisi -->
<div class="modal fade" id="modalRevisi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Revisi Judul Skripsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?php echo site_url('Bimbingan_skripsi/review_judul/' . $pengajuan->id); ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul yang Diajukan:</label>
                        <div class="alert alert-light">
                            <strong><?php echo $pengajuan->judul_pengajuan; ?></strong>
                        </div>
                    </div>
                    <?php if ($current_status_safe === 'revisi' && !empty($current_catatan)): ?>
                    <div class="form-group">
                        <label>Catatan Revisi Sebelumnya:</label>
                        <div class="alert alert-warning">
                            <?php echo nl2br(htmlspecialchars($current_catatan)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="catatan_revisi_modal">Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea name="catatan_revisi" id="catatan_revisi_modal" rows="5" class="form-control" required placeholder="Masukkan catatan revisi untuk mahasiswa (minimal 10 karakter)..."><?php echo set_value('catatan_revisi', $current_catatan); ?></textarea>
                        <small class="form-text text-muted">Berikan catatan yang jelas tentang bagian judul yang perlu direvisi. Minimal 10 karakter.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="action" value="revisi" class="btn btn-danger btn-lg" onclick="return validateRevisi();">
                        <i class="fas fa-times-circle"></i> Revisi Judul Skripsi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validateRevisi() {
    var catatan = document.getElementById('catatan_revisi_modal').value.trim();
    
    // Validasi
    if (catatan === '') {
        alert('Catatan revisi wajib diisi.');
        document.getElementById('catatan_revisi_modal').focus();
        return false;
    }
    
    if (catatan.length < 10) {
        alert('Catatan revisi minimal 10 karakter.');
        document.getElementById('catatan_revisi_modal').focus();
        return false;
    }
    
    // Konfirmasi seperti tombol ACC
    if (!confirm('Apakah Anda yakin ingin merevisi judul skripsi ini?')) {
        return false;
    }
    
    return true;
}

$(document).ready(function() {
    // Reset form saat modal ditutup (Bootstrap 4) - hanya jika tidak ada catatan sebelumnya
    $('#modalRevisi').on('hidden.bs.modal', function() {
        <?php if ($current_status !== 'revisi' || empty($current_catatan)): ?>
        $('#catatan_revisi_modal').val('');
        <?php endif; ?>
    });
    
    // Jika status revisi, tampilkan modal otomatis saat halaman dimuat
    <?php if ($current_status === 'revisi' && !empty($current_catatan)): ?>
    $('#modalRevisi').modal('show');
    <?php endif; ?>
});
</script>

<style>
.row.no-gutters > .col-6:first-child {
    padding-right: 5px;
}
.row.no-gutters > .col-6:last-child {
    padding-left: 5px;
}
</style>

