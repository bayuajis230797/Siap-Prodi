<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Edit Jadwal UAS</h3>
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

                <?php echo form_open('Jadwal_uas/edit/' . $jadwal->id, array('id' => 'formEditJadwal')); ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Mata Kuliah:</strong> <?php echo htmlspecialchars($jadwal->kode_mk ?? '-'); ?> - <?php echo htmlspecialchars($jadwal->nama_mk ?? '-'); ?><br>
                            <strong>Kelas:</strong> <?php echo htmlspecialchars($jadwal->kelas ?? '-'); ?><br>
                            <strong>Sesi:</strong> <?php echo htmlspecialchars($jadwal->sesi ?? '-'); ?><br>
                            <strong>Dosen Pengampu:</strong> <?php echo htmlspecialchars($jadwal->nama_dosen ?? '-'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Ujian <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="<?php echo set_value('tanggal', $jadwal->tanggal ? date('Y-m-d', strtotime($jadwal->tanggal)) : ''); ?>" required>
                            <?php echo form_error('tanggal', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                   value="<?php echo set_value('jam_mulai', $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : ''); ?>" required>
                            <?php echo form_error('jam_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                   value="<?php echo set_value('jam_selesai', $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : ''); ?>" required>
                            <?php echo form_error('jam_selesai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_ruang_kelas">Ruang Kelas <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_ruang_kelas" name="id_ruang_kelas" required>
                                <option value="">-- Pilih Ruang --</option>
                                <?php foreach ($ruang_list as $ruang): ?>
                                    <option value="<?php echo $ruang->id; ?>" 
                                            <?php echo set_select('id_ruang_kelas', $ruang->id, ($jadwal->id_ruang_kelas == $ruang->id)); ?>>
                                        <?php echo htmlspecialchars($ruang->kode_ruang); ?> - <?php echo htmlspecialchars($ruang->nama_ruang); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo form_error('id_ruang_kelas', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" 
                                      placeholder="Keterangan tambahan (opsional)"><?php echo set_value('keterangan', $jadwal->keterangan ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?php echo site_url('Jadwal_uas'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
