<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope-open-text"></i> Daftar Pengajuan Skripsi Mahasiswa
                </h3>
                <div class="card-tools">
                    <?php if ($tahun_aktif): ?>
                        <span class="badge badge-info">
                            TA Aktif: <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning">Belum ada tahun aktif</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <form method="get" class="mb-3">
                    <div class="form-row">
                        <div class="col-md-3">
                            <label for="status_filter">Status Pengajuan</label>
                            <select name="status" id="status_filter" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="disetujui" <?php echo ($status_filter === 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="ditolak" <?php echo ($status_filter === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="kelas_filter">Kelas</label>
                            <input type="text" name="kelas" id="kelas_filter" class="form-control" 
                                   value="<?php echo htmlspecialchars($kelas_filter); ?>" placeholder="Contoh: 7A">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                            <a href="<?php echo site_url('Skripsi_pengajuan/review'); ?>" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada pengajuan judul skripsi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th>Judul Pengajuan</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($pengajuan_list as $pengajuan): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                                        <td>
                                            <?php 
                                            // Tampilkan judul_disetujui jika sudah ada (judul terbaru), jika belum tampilkan judul_pengajuan
                                            $judul_tampil = !empty($pengajuan->judul_disetujui) ? $pengajuan->judul_disetujui : $pengajuan->judul_pengajuan;
                                            echo htmlspecialchars($judul_tampil); 
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($pengajuan->status) {
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($pengajuan->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Skripsi_pengajuan/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($pengajuan->status == 'disetujui' && !empty($pengajuan->judul_disetujui)): ?>
                                            <button type="button" class="btn btn-sm btn-warning btnEditJudulModal" 
                                                    data-pengajuan-id="<?php echo $pengajuan->id; ?>"
                                                    title="Edit Judul">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <a href="<?php echo site_url('Skripsi_pengajuan/delete/' . $pengajuan->id); ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Hapus"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pengajuan judul ini? Tindakan ini tidak dapat dibatalkan.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($pagination)): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted">Menampilkan <?php echo count($pengajuan_list); ?> data</p>
                            </div>
                            <div class="col-md-6">
                                <?php echo $pagination; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Judul untuk setiap pengajuan -->
<?php if (!empty($pengajuan_list)): ?>
    <?php foreach ($pengajuan_list as $pengajuan): ?>
        <?php if ($pengajuan->status == 'disetujui' && !empty($pengajuan->judul_disetujui)): ?>
        <div class="modal fade" id="modalEditJudul<?php echo $pengajuan->id; ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditJudulLabel<?php echo $pengajuan->id; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="modalEditJudulLabel<?php echo $pengajuan->id; ?>">
                            <i class="fas fa-edit"></i> Edit Judul Skripsi
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php echo form_open('Skripsi_pengajuan/edit_judul/' . $pengajuan->id, array('method' => 'post', 'id' => 'formEditJudul' . $pengajuan->id)); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Mahasiswa:</label>
                            <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?> (<?php echo htmlspecialchars($pengajuan->nim); ?>)</strong></p>
                        </div>
                        <div class="form-group">
                            <label for="judul_disetujui_edit<?php echo $pengajuan->id; ?>">Judul Baru <span class="text-danger">*</span></label>
                            <input type="text" name="judul_disetujui" id="judul_disetujui_edit<?php echo $pengajuan->id; ?>" class="form-control" 
                                   value="<?php echo htmlspecialchars($pengajuan->judul_disetujui); ?>" required minlength="10">
                            <small class="form-text text-muted">Judul ini akan menggantikan judul yang sudah disetujui sebelumnya. Minimal 10 karakter.</small>
                        </div>
                        <div class="form-group">
                            <label for="catatan_edit_judul<?php echo $pengajuan->id; ?>">Catatan Perubahan (Opsional)</label>
                            <textarea name="catatan_edit_judul" id="catatan_edit_judul<?php echo $pengajuan->id; ?>" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perubahan judul jika diperlukan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="$(this).closest('.modal').modal('hide');">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Handle klik tombol edit untuk membuka modal
    $(document).on('click', '.btnEditJudulModal', function(e) {
        e.preventDefault();
        var pengajuanId = $(this).data('pengajuan-id');
        var modalId = '#modalEditJudul' + pengajuanId;
        
        // Buka modal menggunakan Bootstrap
        $(modalId).modal('show');
    });
    
    // Handle form submission dengan konfirmasi untuk semua modal edit judul
    $(document).on('submit', '[id^="formEditJudul"]', function(e) {
        var formId = $(this).attr('id');
        var pengajuanId = formId.replace('formEditJudul', '');
        var judulBaru = $('#judul_disetujui_edit' + pengajuanId).val().trim();
        
        if (judulBaru.length < 10) {
            alert('Judul baru minimal harus 10 karakter!');
            e.preventDefault();
            return false;
        }
        
        if (!confirm('Apakah Anda yakin ingin mengubah judul ini?\n\nJudul baru: ' + judulBaru + '\n\nPerubahan akan mempengaruhi judul di bimbingan skripsi dan data terkait.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto focus saat modal dibuka
    $(document).on('shown.bs.modal', '[id^="modalEditJudul"]', function() {
        var modalId = $(this).attr('id');
        var pengajuanId = modalId.replace('modalEditJudul', '');
        setTimeout(function() {
            $('#judul_disetujui_edit' + pengajuanId).focus();
        }, 300);
    });
});
</script>



