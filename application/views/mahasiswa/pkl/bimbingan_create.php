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

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i> Buat Topik Bimbingan Baru
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl/bimbingan'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php 
                $this->load->helper(array('google_drive', 'app'));
                $pkl_topik_opts = pkl_topik_bimbingan_pilihan();
                echo form_open_multipart('Pkl/bimbingan_create'); 
                ?>
                    <div class="form-group">
                        <label for="judul_topik">Judul Topik Bimbingan <span class="text-danger">*</span></label>
                        <select name="judul_topik" id="judul_topik" class="form-control" required>
                            <option value="">-- Pilih topik bimbingan --</option>
                            <?php foreach ($pkl_topik_opts as $topik_opt): ?>
                                <option value="<?php echo htmlspecialchars($topik_opt); ?>" <?php echo set_select('judul_topik', $topik_opt); ?>>
                                    <?php echo htmlspecialchars($topik_opt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('judul_topik', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Topik bimbingan laporan PKL diseragamkan agar progres mudah dilacak.</small>
                    </div>

                    <div class="form-group">
                        <label for="file_laporan">Berkas Laporan <span class="text-danger">*</span></label>
                        <input type="file" name="file_laporan" id="file_laporan" class="form-control-file"
                               accept=".pdf,.doc,.docx,.zip,.rar" required>
                        <small class="form-text text-muted">Diunggah ke Google Drive institusi. PDF/DOC/DOCX/ZIP tanpa batas form.</small>
                    </div>

                    <div class="form-group">
                        <label for="pesan">Pesan/Keterangan (Opsional)</label>
                        <textarea name="pesan" id="pesan" class="form-control" rows="3" 
                                  placeholder="Tambahkan pesan atau keterangan untuk pembimbing..."><?php echo set_value('pesan'); ?></textarea>
                        <?php echo form_error('pesan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Informasi:</strong> Setelah membuat topik bimbingan, pembimbing akan melakukan review. 
                        Jika diperlukan revisi, Anda akan mendapat notifikasi dan dapat mengirim revisi melalui halaman detail topik.
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Topik Bimbingan
                        </button>
                        <a href="<?php echo site_url('Pkl/bimbingan'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

