<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Tagihan Berkas</h3>
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

                <form method="post" action="<?php echo site_url('Tagihan_berkas_sekprodi/edit/' . $tagihan->id); ?>">
                    <div class="form-group">
                        <label for="nama_tagihan">Nama Tagihan <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_tagihan" 
                               id="nama_tagihan" 
                               class="form-control" 
                               value="<?php echo set_value('nama_tagihan', $tagihan->nama_tagihan); ?>"
                               required>
                        <?php echo form_error('nama_tagihan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" 
                                  id="deskripsi" 
                                  class="form-control" 
                                  rows="3"><?php echo set_value('deskripsi', $tagihan->deskripsi); ?></textarea>
                        <?php echo form_error('deskripsi', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="is_hardcopy">Jenis Berkas <span class="text-danger">*</span></label>
                        <select name="is_hardcopy" id="is_hardcopy" class="form-control" required>
                            <option value="0" <?php echo set_value('is_hardcopy', $tagihan->is_hardcopy) == '0' ? 'selected' : ''; ?>>Softfile (Mahasiswa Upload File)</option>
                            <option value="1" <?php echo set_value('is_hardcopy', $tagihan->is_hardcopy) == '1' ? 'selected' : ''; ?>>Hardcopy (Sekprodi Tandai Pengumpulan)</option>
                        </select>
                        <?php echo form_error('is_hardcopy', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="kelas_filter">Filter Kelas</label>
                        <select name="kelas_filter" id="kelas_filter" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($kelas_list as $kelas): ?>
                                <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                        <?php echo set_value('kelas_filter', $tagihan->kelas_filter) == $kelas->kelas ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kelas->kelas); ?> (<?php echo $kelas->jumlah_mahasiswa; ?> mahasiswa)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('kelas_filter', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" 
                               name="deadline" 
                               id="deadline" 
                               class="form-control"
                               value="<?php echo set_value('deadline', $tagihan->deadline ? date('Y-m-d', strtotime($tagihan->deadline)) : ''); ?>">
                        <?php echo form_error('deadline', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="is_aktif">Status <span class="text-danger">*</span></label>
                        <select name="is_aktif" id="is_aktif" class="form-control" required>
                            <option value="1" <?php echo set_value('is_aktif', $tagihan->is_aktif) == '1' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="0" <?php echo set_value('is_aktif', $tagihan->is_aktif) == '0' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                        <?php echo form_error('is_aktif', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?php echo site_url('Tagihan_berkas_sekprodi/detail/' . $tagihan->id); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

