<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Jadwal Sidang Skripsi</h3>
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

                <?php echo form_open('Jadwal_skripsi_sekprodi/edit/' . $jadwal->id); ?>
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" value="<?php echo (isset($jadwal->nim) ? $jadwal->nim : '-') . ' - ' . (isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-') . ' (' . (isset($jadwal->kelas) ? $jadwal->kelas : '-') . ')'; ?>" readonly>
                        <small class="form-text text-muted">Mahasiswa tidak dapat diubah</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="id_ketua_penguji">Ketua Penguji <span class="text-danger">*</span></label>
                                <select name="id_ketua_penguji" id="id_ketua_penguji" class="form-control" required>
                                    <option value="">-- Pilih Ketua Penguji --</option>
                                    <?php foreach ($dosen_list as $dosen): ?>
                                        <option value="<?php echo $dosen->id; ?>" <?php echo (isset($jadwal->id_ketua_penguji) && $jadwal->id_ketua_penguji == $dosen->id) ? 'selected' : ''; ?>>
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
                                        <option value="<?php echo $dosen->id; ?>" <?php echo (isset($jadwal->id_anggota_penguji_1) && $jadwal->id_anggota_penguji_1 == $dosen->id) ? 'selected' : ''; ?>>
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
                                        <option value="<?php echo $dosen->id; ?>" <?php echo (isset($jadwal->id_anggota_penguji_2) && $jadwal->id_anggota_penguji_2 == $dosen->id) ? 'selected' : ''; ?>>
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
                                <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" value="<?php echo isset($jadwal->tanggal_ujian) ? date('Y-m-d', strtotime($jadwal->tanggal_ujian)) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?php echo isset($jadwal->jam_mulai) ? date('H:i', strtotime($jadwal->jam_mulai)) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?php echo isset($jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_selesai)) : ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="ruangan" id="ruangan" class="form-control" value="<?php echo isset($jadwal->ruangan) ? htmlspecialchars($jadwal->ruangan) : ''; ?>" placeholder="Contoh: A101" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="terjadwal" <?php echo (isset($jadwal->status) && $jadwal->status == 'terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                            <option value="selesai" <?php echo (isset($jadwal->status) && $jadwal->status == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="batal" <?php echo (isset($jadwal->status) && $jadwal->status == 'batal') ? 'selected' : ''; ?>>Batal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>





