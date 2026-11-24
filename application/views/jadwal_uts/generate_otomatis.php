<div class="row">
    <div class="col-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Generate Jadwal UTS Otomatis</h3>
            </div>
            <?php echo form_open('Jadwal_uts/generate_otomatis', array('method' => 'post')); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Rules Generate Jadwal UTS Otomatis:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Jadwal UTS akan <strong>mengikuti jadwal mata kuliah</strong> yang sudah di-setting di <strong>Jadwal Kuliah</strong></li>
                        <li><strong>Hari, waktu, dan ruangan</strong> akan <strong>sama persis</strong> dengan jadwal kuliah</li>
                        <li>Tanggal UTS akan disesuaikan dengan hari jadwal kuliah (contoh: jika jadwal kuliah hari Senin, maka UTS akan di hari Senin pada minggu yang sama dengan tanggal yang diinput)</li>
                        <li>Bisa pilih <strong>1 sesi</strong> atau <strong>2 sesi</strong> (opsional)</li>
                        <li>Jika <strong>2 sesi</strong>:
                            <ul>
                                <li>Total durasi jadwal kuliah akan <strong>dibagi 2</strong></li>
                                <li>Contoh: Jadwal kuliah 13:00-15:30 (150 menit) menjadi:
                                    <ul>
                                        <li><strong>Sesi 1:</strong> 13:00-14:15 (75 menit) - Pengawas: <strong>Dosen pengampu</strong></li>
                                        <li><strong>Sesi 2:</strong> 14:15-15:30 (75 menit) - Pengawas: <strong>Tendik</strong> (random)</li>
                                    </ul>
                                </li>
                                <li>Ruangan sama dengan jadwal kuliah</li>
                                <li>Perlu assign mahasiswa ke sesi 1 atau sesi 2</li>
                            </ul>
                        </li>
                        <li>Jika <strong>1 sesi</strong>:
                            <ul>
                                <li>Waktu dan ruangan sama dengan jadwal kuliah</li>
                                <li>Pengawas: <strong>Dosen pengampu</strong> mata kuliah masing-masing</li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kelas">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" id="kelas" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo $k->kelas; ?>" <?php echo set_select('kelas', $k->kelas); ?>>
                                            <?php echo $k->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih kelas yang akan di-generate jadwal UTS-nya</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_uts">Tanggal UTS <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_uts" id="tanggal_uts" class="form-control" required value="<?php echo set_value('tanggal_uts'); ?>">
                            <small class="form-text text-muted">Tanggal pelaksanaan UTS</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jumlah_sesi">Jumlah Sesi <span class="text-danger">*</span></label>
                            <select name="jumlah_sesi" id="jumlah_sesi" class="form-control" required>
                                <option value="1" <?php echo set_select('jumlah_sesi', '1', true); ?>>1 Sesi</option>
                                <option value="2" <?php echo set_select('jumlah_sesi', '2'); ?>>2 Sesi</option>
                            </select>
                            <small class="form-text text-muted">Pilih jumlah sesi (1 atau 2)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="overwrite" value="1" <?php echo set_checkbox('overwrite', '1'); ?>>
                                Overwrite jadwal yang sudah ada
                            </label>
                            <small class="form-text text-muted">Jika dicentang, akan menghapus jadwal UTS yang sudah ada untuk kelas ini dan generate ulang</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-magic"></i> Generate Jadwal
                </button>
                <a href="<?php echo site_url('Jadwal_uts'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

