<?php $this->load->helper('pkm'); ?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Buat Topik Bimbingan PKM</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <?php echo form_open_multipart('Bimbingan_pkm_mahasiswa/bimbingan_create'); ?>
                    <div class="form-group">
                        <label>Topik Bimbingan <span class="text-danger">*</span></label>
                        <select name="judul_topik" class="form-control" required>
                            <option value="">— Pilih topik —</option>
                            <?php foreach (pkm_topik_bimbingan_pilihan() as $topik): ?>
                                <option value="<?php echo htmlspecialchars($topik); ?>" <?php echo set_select('judul_topik', $topik); ?>><?php echo htmlspecialchars($topik); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('judul_topik', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <div class="form-group">
                        <label>Berkas Bimbingan <span class="text-danger">*</span></label>
                        <input type="file" name="file_laporan" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar" required>
                    </div>
                    <div class="form-group">
                        <label>Pesan (Opsional)</label>
                        <textarea name="pesan" class="form-control" rows="3"><?php echo set_value('pesan'); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Mulai Bimbingan</button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
