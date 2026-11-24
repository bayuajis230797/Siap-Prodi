<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Edit Rapat</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Rapat/update/' . $rapat->id, array('id' => 'formRapat')); ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="judul_rapat">Judul Rapat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul_rapat" name="judul_rapat" 
                                   value="<?php echo set_value('judul_rapat', $rapat->judul_rapat); ?>" required>
                            <?php echo form_error('judul_rapat', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_rapat">Tanggal Rapat <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_rapat" name="tanggal_rapat" 
                                   value="<?php echo set_value('tanggal_rapat', $rapat->tanggal_rapat); ?>" required>
                            <?php echo form_error('tanggal_rapat', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                   value="<?php echo set_value('jam_mulai', $rapat->jam_mulai); ?>" required>
                            <?php echo form_error('jam_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                   value="<?php echo set_value('jam_selesai', $rapat->jam_selesai); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="tempat">Tempat</label>
                            <input type="text" class="form-control" id="tempat" name="tempat" 
                                   value="<?php echo set_value('tempat', $rapat->tempat); ?>" placeholder="Contoh: Ruang Rapat Prodi">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notulen">Notulen</label>
                            <textarea class="form-control" id="notulen" name="notulen" rows="5" 
                                      placeholder="Catatan notulen rapat..."><?php echo set_value('notulen', $rapat->notulen); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="<?php echo site_url('Rapat/detail/' . $rapat->id); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>







