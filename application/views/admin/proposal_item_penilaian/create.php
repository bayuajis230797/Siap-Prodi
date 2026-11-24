<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Item Penilaian Proposal TA</h3>
            </div>
            <?php echo form_open('Proposal_item_penilaian/create'); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="id_aspek_penilaian">Aspek Penilaian <span class="text-danger">*</span></label>
                    <select name="id_aspek_penilaian" id="id_aspek_penilaian" class="form-control" required>
                        <option value="">Pilih Aspek Penilaian</option>
                        <?php foreach ($aspek_list as $aspek): ?>
                            <option value="<?php echo $aspek->id; ?>" <?php echo set_select('id_aspek_penilaian', $aspek->id); ?>>
                                <?php echo htmlspecialchars($aspek->kode_aspek . ' - ' . $aspek->nama_aspek); ?>
                                (<?php echo ucfirst(str_replace('_', ' ', $aspek->ditunjukkan_untuk)); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo form_error('id_aspek_penilaian', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Pilih aspek penilaian yang akan ditambahkan item-nya</small>
                </div>

                <div class="form-group">
                    <label for="pertanyaan">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="pertanyaan" id="pertanyaan" class="form-control" rows="4" required><?php echo set_value('pertanyaan'); ?></textarea>
                    <?php echo form_error('pertanyaan', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Pertanyaan atau item yang akan dinilai</small>
                </div>

                <div class="form-group">
                    <label for="bobot">Bobot (%) <span class="text-danger">*</span></label>
                    <input type="number" name="bobot" id="bobot" class="form-control" 
                           value="<?php echo set_value('bobot', 0); ?>" 
                           step="0.01" min="0" max="100" required>
                    <?php echo form_error('bobot', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Bobot item penilaian (0-100). Total bobot semua item dalam satu aspek sebaiknya = 100%</small>
                </div>

                <div class="form-group">
                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                    <input type="number" name="urutan" id="urutan" class="form-control" 
                           value="<?php echo set_value('urutan', 1); ?>" 
                           min="1" required>
                    <?php echo form_error('urutan', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Urutan tampil item dalam aspek (1, 2, 3, ...)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_aktif" id="is_aktif" class="form-check-input" 
                               value="1" <?php echo set_checkbox('is_aktif', '1', true); ?>>
                        <label class="form-check-label" for="is_aktif">
                            Aktif
                        </label>
                    </div>
                    <small class="form-text text-muted">Centang untuk mengaktifkan item penilaian</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Proposal_item_penilaian'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

