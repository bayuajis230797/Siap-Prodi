<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Buat Tagihan Berkas Baru</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url('Tagihan_berkas_sekprodi/create'); ?>">
                    <div class="form-group">
                        <label for="nama_tagihan">Nama Tagihan <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_tagihan" 
                               id="nama_tagihan" 
                               class="form-control" 
                               placeholder="Contoh: Laporan PKL, Berkas Skripsi, dll"
                               value="<?php echo set_value('nama_tagihan'); ?>"
                               required>
                        <?php echo form_error('nama_tagihan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" 
                                  id="deskripsi" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Deskripsi tagihan (opsional)"><?php echo set_value('deskripsi'); ?></textarea>
                        <?php echo form_error('deskripsi', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="is_hardcopy">Jenis Berkas <span class="text-danger">*</span></label>
                        <select name="is_hardcopy" id="is_hardcopy" class="form-control" required>
                            <option value="0" <?php echo set_value('is_hardcopy') == '0' ? 'selected' : ''; ?>>Softfile (Mahasiswa Upload File)</option>
                            <option value="1" <?php echo set_value('is_hardcopy') == '1' ? 'selected' : ''; ?>>Hardcopy (Sekprodi Tandai Pengumpulan)</option>
                        </select>
                        <?php echo form_error('is_hardcopy', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">
                            <strong>Softfile:</strong> Mahasiswa akan upload file melalui sistem.<br>
                            <strong>Hardcopy:</strong> Mahasiswa mengumpulkan fisik, sekprodi menandai yang sudah mengumpulkan.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="kelas_filter">Filter Kelas</label>
                        <select name="kelas_filter" id="kelas_filter" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($kelas_list as $kelas): ?>
                                <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                        <?php echo set_value('kelas_filter') == $kelas->kelas ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kelas->kelas); ?> (<?php echo $kelas->jumlah_mahasiswa; ?> mahasiswa)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('kelas_filter', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Pilih kelas tertentu atau biarkan kosong untuk semua kelas.</small>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" 
                               name="deadline" 
                               id="deadline" 
                               class="form-control"
                               value="<?php echo set_value('deadline'); ?>">
                        <?php echo form_error('deadline', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Tanggal batas akhir pengumpulan (opsional).</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url('Tagihan_berkas_sekprodi'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

