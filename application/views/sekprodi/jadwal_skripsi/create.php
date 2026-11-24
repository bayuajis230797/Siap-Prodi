<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Jadwal Sidang Skripsi</h3>
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

                <?php if (empty($mahasiswa_siap_jadwal)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada mahasiswa yang siap jadwal. Pastikan pendaftaran sidang sudah disetujui.
                    </div>
                    <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                <?php else: ?>
                    <?php echo form_open('Jadwal_skripsi_sekprodi/create'); ?>
                        <div class="form-group">
                            <label for="id_mahasiswa">Mahasiswa <span class="text-danger">*</span></label>
                            <select name="id_mahasiswa" id="id_mahasiswa" class="form-control" required>
                                <option value="">-- Pilih Mahasiswa --</option>
                                <?php foreach ($mahasiswa_siap_jadwal as $mhs): ?>
                                    <option value="<?php echo isset($mhs->id_mahasiswa) ? $mhs->id_mahasiswa : $mhs->id; ?>">
                                        <?php echo (isset($mhs->nim) ? $mhs->nim : '-') . ' - ' . (isset($mhs->nama_mahasiswa) ? $mhs->nama_mahasiswa : '-') . ' (' . (isset($mhs->kelas) ? $mhs->kelas : '-') . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan mahasiswa yang pendaftaran sidangnya sudah disetujui</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_ketua_penguji">Ketua Penguji <span class="text-danger">*</span></label>
                                    <select name="id_ketua_penguji" id="id_ketua_penguji" class="form-control" required>
                                        <option value="">-- Pilih Ketua Penguji --</option>
                                        <?php foreach ($dosen_list as $dosen): ?>
                                            <option value="<?php echo $dosen->id; ?>">
                                                <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_penguji_1">Anggota Penguji 1 <span class="text-danger">*</span></label>
                                    <select name="id_penguji_1" id="id_penguji_1" class="form-control" required>
                                        <option value="">-- Pilih Anggota Penguji 1 --</option>
                                        <?php foreach ($dosen_list as $dosen): ?>
                                            <option value="<?php echo $dosen->id; ?>">
                                                <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_penguji_2">Anggota Penguji 2 <span class="text-danger">*</span></label>
                                    <select name="id_penguji_2" id="id_penguji_2" class="form-control" required>
                                        <option value="">-- Pilih Anggota Penguji 2 --</option>
                                        <?php foreach ($dosen_list as $dosen): ?>
                                            <option value="<?php echo $dosen->id; ?>">
                                                <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_ujian">Tanggal Sidang <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                            <input type="text" name="ruangan" id="ruangan" class="form-control" placeholder="Contoh: A101" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





