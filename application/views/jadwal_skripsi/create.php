<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Jadwal Ujian Skripsi</h3>
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

                <?php if (empty($layak_sidang)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada mahasiswa yang layak sidang. Pastikan kedua pembimbing sudah memberikan ACC Layak Sidang.
                    </div>
                    <a href="<?php echo site_url('Jadwal_skripsi'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                <?php else: ?>
                    <?php echo form_open('Jadwal_skripsi/create'); ?>
                        <div class="form-group">
                            <label for="id_mahasiswa_skripsi">Mahasiswa <span class="text-danger">*</span></label>
                            <select name="id_mahasiswa_skripsi" id="id_mahasiswa_skripsi" class="form-control" required>
                                <option value="">-- Pilih Mahasiswa --</option>
                                <?php foreach ($layak_sidang as $ls): ?>
                                    <option value="<?php echo $ls->id; ?>" 
                                            data-pembimbing1="<?php echo $ls->id_pembimbing_1; ?>"
                                            data-pembimbing2="<?php echo $ls->id_pembimbing_2; ?>">
                                        <?php echo $ls->nim . ' - ' . $ls->nama_mahasiswa . ' (' . $ls->kelas . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan mahasiswa yang sudah layak sidang (kedua pembimbing sudah ACC)</small>
                        </div>

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
                            <small class="form-text text-danger">Pembimbing 1 tidak boleh menjadi Ketua Penguji</small>
                        </div>

                        <div class="form-group">
                            <label for="id_anggota_penguji_1">Anggota Penguji 1 <span class="text-danger">*</span></label>
                            <select name="id_anggota_penguji_1" id="id_anggota_penguji_1" class="form-control" required>
                                <option value="">-- Pilih Anggota Penguji 1 --</option>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <option value="<?php echo $dosen->id; ?>">
                                        <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-danger">Pembimbing 1 tidak boleh menjadi Anggota Penguji 1</small>
                        </div>

                        <div class="form-group">
                            <label>Anggota Penguji 2</label>
                            <input type="text" class="form-control" value="Otomatis: Pembimbing 2" readonly>
                            <small class="form-text text-muted">Anggota Penguji 2 otomatis diisi oleh Pembimbing 2</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_ujian">Tanggal Ujian <span class="text-danger">*</span></label>
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
                            <label for="ruangan">Ruangan</label>
                            <input type="text" name="ruangan" id="ruangan" class="form-control" placeholder="Contoh: A101">
                        </div>

                        <div class="form-group">
                            <label for="catatan">Catatan</label>
                            <textarea name="catatan" id="catatan" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?php echo site_url('Jadwal_skripsi'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    <?php echo form_close(); ?>

                    <script>
                        document.getElementById('id_mahasiswa_skripsi').addEventListener('change', function() {
                            const option = this.options[this.selectedIndex];
                            const pembimbing1 = option.getAttribute('data-pembimbing1');
                            const pembimbing2 = option.getAttribute('data-pembimbing2');
                            
                            const ketuaSelect = document.getElementById('id_ketua_penguji');
                            const anggota1Select = document.getElementById('id_anggota_penguji_1');
                            
                            // Reset dan filter ketua penguji (bukan pembimbing 1)
                            Array.from(ketuaSelect.options).forEach(opt => {
                                if (opt.value && opt.value == pembimbing1) {
                                    opt.style.display = 'none';
                                    opt.disabled = true;
                                } else {
                                    opt.style.display = '';
                                    opt.disabled = false;
                                }
                            });
                            
                            // Reset dan filter anggota penguji 1 (bukan pembimbing 1)
                            Array.from(anggota1Select.options).forEach(opt => {
                                if (opt.value && opt.value == pembimbing1) {
                                    opt.style.display = 'none';
                                    opt.disabled = true;
                                } else {
                                    opt.style.display = '';
                                    opt.disabled = false;
                                }
                            });
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>













