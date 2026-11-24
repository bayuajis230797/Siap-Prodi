<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Jadwal Kuliah</h3>
            </div>
            <?php echo form_open('Jadwal_kuliah/edit/' . $jadwal->id, array('method' => 'post', 'id' => 'form-jadwal')); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

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
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Rules Validasi:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Hari perkuliahan: <strong>Senin - Jumat</strong> (tidak termasuk Sabtu)</li>
                        <li>1 SKS = 50 menit (jam selesai direkomendasikan, dapat disesuaikan)</li>
                        <li>Tidak boleh bentrok ruang kelas (ruang yang sama, waktu yang sama, kelas berbeda)</li>
                        <li>Tidak boleh bentrok dosen (dosen yang sama, waktu yang sama, kelas berbeda)</li>
                        <li>Tidak boleh bentrok kelas (kelas yang sama, waktu dan hari yang sama)</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kelas">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" id="kelas" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo $k->kelas; ?>" 
                                                <?php echo set_select('kelas', $k->kelas, ($k->kelas == $jadwal->kelas)); ?>>
                                            <?php echo $k->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih kelas terlebih dahulu untuk menampilkan mapping mata kuliah</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_mapping_dosen_mk">Mata Kuliah & Dosen <span class="text-danger">*</span></label>
                            <select name="id_mapping_dosen_mk" id="id_mapping_dosen_mk" class="form-control" required disabled>
                                <option value="">-- Pilih Mapping --</option>
                                <?php if (!empty($mapping_list)): ?>
                                    <?php foreach ($mapping_list as $m): ?>
                                        <option value="<?php echo $m->id; ?>" 
                                                <?php echo set_select('id_mapping_dosen_mk', $m->id, ($m->id == $jadwal->id_mapping_dosen_mk)); ?>
                                                data-sks="<?php echo $m->sks; ?>" selected>
                                            <?php echo $m->kode_mk . ' - ' . $m->nama_mk . ' (' . $m->sks . ' SKS) - ' . $m->nama_dosen . ' (' . $m->nidn . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="id_mapping_dosen_mk" value="<?php echo $jadwal->id_mapping_dosen_mk; ?>">
                            <small class="form-text text-muted">Mapping mata kuliah dan dosen tidak dapat diubah saat edit</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hari">Hari <span class="text-danger">*</span></label>
                            <select name="hari" id="hari" class="form-control" required>
                                <option value="">-- Pilih Hari --</option>
                                <option value="senin" <?php echo set_select('hari', 'senin', ($jadwal->hari == 'senin')); ?>>Senin</option>
                                <option value="selasa" <?php echo set_select('hari', 'selasa', ($jadwal->hari == 'selasa')); ?>>Selasa</option>
                                <option value="rabu" <?php echo set_select('hari', 'rabu', ($jadwal->hari == 'rabu')); ?>>Rabu</option>
                                <option value="kamis" <?php echo set_select('hari', 'kamis', ($jadwal->hari == 'kamis')); ?>>Kamis</option>
                                <option value="jumat" <?php echo set_select('hari', 'jumat', ($jadwal->hari == 'jumat')); ?>>Jumat</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required 
                                   value="<?php echo set_value('jam_mulai', date('H:i', strtotime($jadwal->jam_mulai))); ?>">
                            <small class="form-text text-muted">Jam selesai akan otomatis direkomendasikan berdasarkan SKS</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control"
                                   value="<?php echo set_value('jam_selesai', date('H:i', strtotime($jadwal->jam_selesai))); ?>">
                            <small class="form-text text-muted">Rekomendasi: 1 SKS = 50 menit (dapat disesuaikan)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_ruang_kelas">Ruang Kelas <span class="text-danger">*</span></label>
                            <select name="id_ruang_kelas" id="id_ruang_kelas" class="form-control" required>
                                <option value="">-- Pilih Ruang --</option>
                                <?php if (!empty($ruang_list)): ?>
                                    <?php foreach ($ruang_list as $r): ?>
                                        <option value="<?php echo $r->id; ?>" 
                                                <?php echo set_select('id_ruang_kelas', $r->id, ($r->id == $jadwal->id_ruang_kelas)); ?>>
                                            <?php echo $r->kode_ruang . ' - ' . $r->nama_ruang . ' (Kapasitas: ' . $r->kapasitas . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="2"><?php echo set_value('keterangan', $jadwal->keterangan ?? ''); ?></textarea>
                            <small class="form-text text-muted">Keterangan tambahan (opsional)</small>
                        </div>
                    </div>
                </div>

                <div id="info-sks" class="alert alert-info" style="display: none;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Info:</strong> Mata kuliah ini memiliki <span id="sks-value">0</span> SKS. 
                    Jam selesai direkomendasikan: <span id="durasi-value">0</span> menit (dapat disesuaikan).
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="<?php echo site_url('Jadwal_kuliah'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.getElementById('kelas');
    const mappingSelect = document.getElementById('id_mapping_dosen_mk');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const infoSksEl = document.getElementById('info-sks');
    
    // Update SKS info saat mapping dipilih
    function updateSksInfo() {
        const selectedOption = mappingSelect.options[mappingSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.sks) {
            const sks = parseInt(selectedOption.dataset.sks);
            document.getElementById('sks-value').textContent = sks;
            document.getElementById('durasi-value').textContent = sks * 50;
            infoSksEl.style.display = 'block';
        } else {
            infoSksEl.style.display = 'none';
        }
    }
    
    // Disable perubahan kelas dan mapping saat edit (tidak perlu load mapping lagi)
    // kelasSelect.disabled = true;
    // mappingSelect.disabled = true;
    
    // Load mapping saat kelas berubah (disabled untuk edit)
    // kelasSelect.addEventListener('change', function() {
    //     const kelas = this.value;
    //     if (kelas) {
    //         loadMappingByKelas(kelas);
    //     }
    // });
    
    // Update SKS info dan calculate jam selesai
    mappingSelect.addEventListener('change', function() {
        updateSksInfo();
        calculateJamSelesai();
    });
    
    // Auto calculate jam selesai saat jam mulai berubah
    jamMulaiInput.addEventListener('change', function() {
        calculateJamSelesai();
    });
    
    // Initialize SKS info
    updateSksInfo();
    
    function calculateJamSelesai() {
        const selectedOption = mappingSelect.options[mappingSelect.selectedIndex];
        const jamMulai = jamMulaiInput.value;
        
        if (!selectedOption || !selectedOption.dataset.sks || !jamMulai) {
            return;
        }
        
        const sks = parseInt(selectedOption.dataset.sks);
        const time = new Date('2000-01-01T' + jamMulai + ':00');
        const menitTotal = sks * 50;
        time.setMinutes(time.getMinutes() + menitTotal);
        
        const jamSelesai = time.toTimeString().substring(0, 5);
        jamSelesaiInput.value = jamSelesai;
    }
    
    // Calculate saat halaman dimuat
    calculateJamSelesai();
});
</script>

