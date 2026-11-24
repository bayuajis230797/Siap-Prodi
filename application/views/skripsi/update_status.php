<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Update Status Skripsi</h3>
            </div>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Informasi Mahasiswa</h5>
                    <strong>NIM:</strong> <?php echo $skripsi->nim; ?><br>
                    <strong>Nama:</strong> <?php echo $skripsi->nama_mahasiswa; ?><br>
                    <strong>Kelas:</strong> <?php echo $skripsi->kelas; ?>
                </div>

                <?php echo form_open('Skripsi/update_status/' . $skripsi->id, array('method' => 'post')); ?>
                
                <div class="form-group">
                    <label for="status">Status Skripsi <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="terdaftar" <?php echo ($skripsi->status == 'terdaftar') ? 'selected' : ''; ?>>Terdaftar</option>
                        <option value="sedang_skripsi" <?php echo ($skripsi->status == 'sedang_skripsi') ? 'selected' : ''; ?>>Sedang Skripsi</option>
                        <option value="selesai_skripsi" <?php echo ($skripsi->status == 'selesai_skripsi') ? 'selected' : ''; ?>>Selesai Skripsi</option>
                        <option value="batal" <?php echo ($skripsi->status == 'batal') ? 'selected' : ''; ?>>Batal</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_pembimbing_1">Pembimbing 1</label>
                            <select name="id_pembimbing_1" id="id_pembimbing_1" class="form-control">
                                <option value="">-- Pilih Pembimbing 1 --</option>
                                <?php if (!empty($pembimbing1_options)): ?>
                                    <?php foreach ($pembimbing1_options as $pb1): ?>
                                        <option value="<?php echo $pb1->id_dosen; ?>" <?php echo ($skripsi->id_pembimbing_1 == $pb1->id_dosen) ? 'selected' : ''; ?>>
                                            <?php echo ($pb1->nidn ? $pb1->nidn . ' - ' : '') . $pb1->nama_dosen; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Hanya dosen yang diaktifkan sebagai Pembimbing 1 yang muncul di sini.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_pembimbing_2">Pembimbing 2</label>
                            <select name="id_pembimbing_2" id="id_pembimbing_2" class="form-control">
                                <option value="">-- Pilih Pembimbing 2 --</option>
                                <?php if (!empty($pembimbing2_options)): ?>
                                    <?php foreach ($pembimbing2_options as $pb2): ?>
                                        <option value="<?php echo $pb2->id_dosen; ?>" <?php echo ($skripsi->id_pembimbing_2 == $pb2->id_dosen) ? 'selected' : ''; ?>>
                                            <?php echo ($pb2->nidn ? $pb2->nidn . ' - ' : '') . $pb2->nama_dosen; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Hanya dosen yang diaktifkan sebagai Pembimbing 2 yang muncul di sini.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="judul_skripsi">Judul Skripsi</label>
                    <textarea name="judul_skripsi" id="judul_skripsi" class="form-control" rows="3" placeholder="Masukkan judul skripsi"><?php echo $skripsi->judul_skripsi; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?php echo $skripsi->tanggal_mulai ? date('Y-m-d', strtotime($skripsi->tanggal_mulai)) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?php echo $skripsi->tanggal_selesai ? date('Y-m-d', strtotime($skripsi->tanggal_selesai)) : ''; ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

