<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Jadwal UTS</h3>
            </div>
            <?php echo form_open('Jadwal_uts/create', array('method' => 'post', 'id' => 'form-jadwal')); ?>
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
                        <li>1 SKS = 50 menit (jam selesai direkomendasikan, dapat disesuaikan)</li>
                        <li>Tidak boleh bentrok ruang kelas (ruang yang sama, tanggal dan waktu yang sama, kelas berbeda)</li>
                        <li>Tidak boleh bentrok dosen (dosen yang sama, tanggal dan waktu yang sama, kelas berbeda)</li>
                        <li>Tidak boleh bentrok kelas (kelas yang sama, tanggal dan waktu yang sama)</li>
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
                                        <option value="<?php echo $k->kelas; ?>" <?php echo set_select('kelas', $k->kelas); ?>>
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
                                <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                            </select>
                            <small class="form-text text-muted">Pilih mapping mata kuliah dan dosen pengampu</small>
                            <div id="loading-mapping" class="text-center mt-2" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Memuat mapping...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal">Tanggal UTS <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?php echo set_value('tanggal'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required value="<?php echo set_value('jam_mulai'); ?>">
                            <small class="form-text text-muted">Jam selesai akan otomatis direkomendasikan berdasarkan SKS</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control">
                            <small class="form-text text-muted">Rekomendasi: 1 SKS = 50 menit (dapat disesuaikan)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_ruang_kelas">Ruang Kelas <span class="text-danger">*</span></label>
                            <select name="id_ruang_kelas" id="id_ruang_kelas" class="form-control" required>
                                <option value="">-- Pilih Ruang --</option>
                                <?php if (!empty($ruang_list)): ?>
                                    <?php foreach ($ruang_list as $r): ?>
                                        <option value="<?php echo $r->id; ?>" <?php echo set_select('id_ruang_kelas', $r->id); ?>>
                                            <?php echo $r->kode_ruang . ' - ' . $r->nama_ruang . ' (Kapasitas: ' . $r->kapasitas . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="2"><?php echo set_value('keterangan'); ?></textarea>
                            <small class="form-text text-muted">Keterangan tambahan (opsional)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="pengawas">Pengawas</label>
                            <select name="pengawas[]" id="pengawas" class="form-control" multiple size="5">
                                <?php if (!empty($dosen_list)): ?>
                                    <?php foreach ($dosen_list as $d): ?>
                                        <option value="<?php echo $d->id; ?>" <?php echo set_select('pengawas[]', $d->id); ?>>
                                            <?php echo $d->nama_dosen . ' (' . $d->nidn . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih satu atau lebih pengawas (tekan Ctrl/Cmd untuk memilih multiple). Pengawas tidak boleh bentrok dengan jadwal lain di waktu yang sama.</small>
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
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Jadwal_uts'); ?>" class="btn btn-secondary">
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
    const loadingEl = document.getElementById('loading-mapping');
    const infoSksEl = document.getElementById('info-sks');
    
    // Store mapping data untuk akses SKS
    let mappingData = {};
    
    // Event listener untuk kelas
    kelasSelect.addEventListener('change', function() {
        const kelas = this.value;
        if (kelas) {
            loadMappingByKelas(kelas);
        } else {
            mappingSelect.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
            mappingSelect.disabled = true;
            infoSksEl.style.display = 'none';
        }
    });
    
    // Event listener untuk mapping (untuk update SKS info)
    mappingSelect.addEventListener('change', function() {
        const mappingId = this.value;
        if (mappingId && mappingData[mappingId]) {
            const mapping = mappingData[mappingId];
            document.getElementById('sks-value').textContent = mapping.sks;
            document.getElementById('durasi-value').textContent = mapping.sks * 50;
            infoSksEl.style.display = 'block';
            
            // Auto calculate jam selesai jika jam mulai sudah diisi
            if (jamMulaiInput.value) {
                calculateJamSelesai();
            }
        } else {
            infoSksEl.style.display = 'none';
        }
    });
    
    // Event listener untuk jam mulai (auto calculate jam selesai)
    jamMulaiInput.addEventListener('change', function() {
        calculateJamSelesai();
    });
    
    function loadMappingByKelas(kelas) {
        mappingSelect.disabled = true;
        mappingSelect.innerHTML = '<option value="">Memuat...</option>';
        loadingEl.style.display = 'block';
        
        fetch('<?php echo site_url("Jadwal_uts/get_mapping_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                loadingEl.style.display = 'none';
                
                if (data.success && data.data && data.data.length > 0) {
                    mappingSelect.innerHTML = '<option value="">-- Pilih Mapping --</option>';
                    mappingData = {};
                    
                    data.data.forEach(m => {
                        mappingData[m.id] = m;
                        const option = document.createElement('option');
                        option.value = m.id;
                        option.textContent = m.kode_mk + ' - ' + m.nama_mk + ' (' + m.sks + ' SKS) - ' + m.nama_dosen + ' (' + m.nidn + ')';
                        mappingSelect.appendChild(option);
                    });
                    
                    mappingSelect.disabled = false;
                } else {
                    mappingSelect.innerHTML = '<option value="">-- Tidak ada mapping untuk kelas ini --</option>';
                    mappingSelect.disabled = true;
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                loadingEl.style.display = 'none';
                mappingSelect.innerHTML = '<option value="">-- Error loading data --</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingEl.style.display = 'none';
            mappingSelect.innerHTML = '<option value="">-- Error loading data --</option>';
        });
    }
    
    function calculateJamSelesai() {
        const mappingId = mappingSelect.value;
        const jamMulai = jamMulaiInput.value;
        
        if (!mappingId || !jamMulai) {
            jamSelesaiInput.value = '';
            return;
        }
        
        const mapping = mappingData[mappingId];
        if (!mapping || !mapping.sks) {
            jamSelesaiInput.value = '';
            return;
        }
        
        // Hitung jam selesai: jam mulai + (SKS * 50 menit)
        const time = new Date('2000-01-01T' + jamMulai + ':00');
        const menitTotal = mapping.sks * 50;
        time.setMinutes(time.getMinutes() + menitTotal);
        
        const jamSelesai = time.toTimeString().substring(0, 5);
        jamSelesaiInput.value = jamSelesai;
    }
});
</script>

