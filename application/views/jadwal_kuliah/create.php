<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Jadwal Kuliah</h3>
            </div>
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

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="jadwalTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="per-kelas-tab" data-bs-toggle="tab" href="#per-kelas" role="tab" aria-controls="per-kelas" aria-selected="true">
                            <i class="fas fa-users"></i> Jadwal Per Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="false">
                            <i class="fas fa-edit"></i> Jadwal Manual
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="jadwalTabContent">
                    <!-- Tab: Jadwal Per Kelas -->
                    <div class="tab-pane fade show active" id="per-kelas" role="tabpanel">
                        <div class="form-group">
                            <label for="kelas_select">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_select" id="kelas_select" class="form-control">
                                <option value="">-- Pilih Kelas --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo $k->kelas; ?>">
                                            <?php echo $k->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih kelas untuk menampilkan mata kuliah yang perlu dijadwalkan</small>
                        </div>

                        <div id="loading-jadwal" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat mata kuliah...
                        </div>

                        <div id="jadwal-error-message" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Perhatian:</strong> <span id="error-text-jadwal"></span>
                        </div>

                        <div id="jadwal-list-container" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Mata Kuliah untuk Kelas:</strong> <span id="kelas-selected-jadwal"></span>
                                <br><small class="text-muted">
                                    <span class="badge badge-success">Hijau</span> = Sudah dijadwalkan | 
                                    <span class="badge badge-light">Putih</span> = Belum dijadwalkan (isi jadwal)
                                </small>
                            </div>
                            
                            <?php echo form_open('Jadwal_kuliah/batch_create', array('method' => 'post', 'id' => 'form-batch-jadwal')); ?>
                            <input type="hidden" name="kelas" id="batch-kelas-jadwal" value="">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="table-jadwal-list">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="12%">Kode MK</th>
                                            <th>Nama Mata Kuliah</th>
                                            <th width="8%" class="text-center">SKS</th>
                                            <th width="10%">Dosen</th>
                                            <th width="10%">Hari</th>
                                            <th width="10%">Jam Mulai</th>
                                            <th width="10%">Jam Selesai</th>
                                            <th width="15%">Ruang</th>
                                            <th width="8%" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jadwal-list-body">
                                        <!-- Akan diisi via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary" id="btn-save-batch-jadwal" disabled>
                                    <i class="fas fa-save"></i> Simpan Semua Jadwal
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetJadwalForm()">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>

                    <!-- Tab: Jadwal Manual -->
                    <div class="tab-pane fade" id="manual" role="tabpanel">
                        <?php echo form_open('Jadwal_kuliah/create', array('method' => 'post', 'id' => 'form-jadwal')); ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Petunjuk:</strong> Pilih kelas terlebih dahulu, kemudian pilih mata kuliah yang belum dijadwalkan untuk kelas tersebut.
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="hari">Hari <span class="text-danger">*</span></label>
                                    <select name="hari" id="hari" class="form-control" required>
                                        <option value="">-- Pilih Hari --</option>
                                        <option value="senin" <?php echo set_select('hari', 'senin'); ?>>Senin</option>
                                        <option value="selasa" <?php echo set_select('hari', 'selasa'); ?>>Selasa</option>
                                        <option value="rabu" <?php echo set_select('hari', 'rabu'); ?>>Rabu</option>
                                        <option value="kamis" <?php echo set_select('hari', 'kamis'); ?>>Kamis</option>
                                        <option value="jumat" <?php echo set_select('hari', 'jumat'); ?>>Jumat</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required value="<?php echo set_value('jam_mulai'); ?>">
                                    <small class="form-text text-muted">Jam selesai akan otomatis direkomendasikan berdasarkan SKS</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="jam_selesai">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control">
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
                                                <option value="<?php echo $r->id; ?>" <?php echo set_select('id_ruang_kelas', $r->id); ?>>
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
                                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2"><?php echo set_value('keterangan'); ?></textarea>
                                    <small class="form-text text-muted">Keterangan tambahan (opsional)</small>
                                </div>
                            </div>
                        </div>

                        <div id="info-sks" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Info:</strong> Mata kuliah ini memiliki <span id="sks-value">0</span> SKS. 
                            Jam selesai direkomendasikan: <span id="durasi-value">0</span> menit (dapat disesuaikan).
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?php echo site_url('Jadwal_kuliah'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== JADWAL PER KELAS ==========
    const kelasSelectJadwal = document.getElementById('kelas_select');
    const loadingJadwal = document.getElementById('loading-jadwal');
    const errorMessageJadwal = document.getElementById('jadwal-error-message');
    const errorTextJadwal = document.getElementById('error-text-jadwal');
    const jadwalListContainer = document.getElementById('jadwal-list-container');
    const kelasSelectedJadwal = document.getElementById('kelas-selected-jadwal');
    const batchKelasJadwal = document.getElementById('batch-kelas-jadwal');
    const btnSaveBatchJadwal = document.getElementById('btn-save-batch-jadwal');
    const jadwalListBody = document.getElementById('jadwal-list-body');

    kelasSelectJadwal.addEventListener('change', function() {
        const kelas = this.value;
        if (kelas) {
            loadJadwalByKelas(kelas);
        } else {
            jadwalListContainer.style.display = 'none';
            btnSaveBatchJadwal.disabled = true;
        }
    });

    function loadJadwalByKelas(kelas) {
        if (!kelas || kelas.trim() === '') {
            return;
        }
        
        loadingJadwal.style.display = 'block';
        errorMessageJadwal.style.display = 'none';
        jadwalListContainer.style.display = 'none';
        btnSaveBatchJadwal.disabled = true;
        jadwalListBody.innerHTML = '';

        fetch('<?php echo site_url("Jadwal_kuliah/get_mapping_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            loadingJadwal.style.display = 'none';
            
            if (!text || text.trim() === '') {
                throw new Error('Response kosong dari server');
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Format response tidak valid: ' + text.substring(0, 100));
            }
            
            if (!data) {
                throw new Error('Data tidak valid');
            }
            
            if (data.success === false) {
                errorTextJadwal.textContent = data.message || 'Terjadi kesalahan saat memuat data.';
                errorMessageJadwal.style.display = 'block';
                return;
            }
            
            if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                kelasSelectedJadwal.textContent = kelas;
                batchKelasJadwal.value = kelas;
                populateJadwalTable(data.data);
                jadwalListContainer.style.display = 'block';
                btnSaveBatchJadwal.disabled = false;
            } else {
                errorTextJadwal.textContent = 'Tidak ada mata kuliah yang perlu dijadwalkan untuk kelas ' + kelas + '. Semua mata kuliah sudah dijadwalkan.';
                errorMessageJadwal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingJadwal.style.display = 'none';
            errorTextJadwal.textContent = 'Terjadi kesalahan saat memuat data: ' + (error.message || 'Unknown error');
            errorMessageJadwal.style.display = 'block';
        });
    }

    function populateJadwalTable(mataKuliahList) {
        jadwalListBody.innerHTML = '';
        mataKuliahList.forEach((mk, index) => {
            const row = document.createElement('tr');
            const isScheduled = mk.is_scheduled || false;
            const jadwalArray = mk.jadwal || [];
            
            // Jika sudah dijadwalkan, tampilkan dengan style berbeda dan disable input
            if (isScheduled && Array.isArray(jadwalArray) && jadwalArray.length > 0) {
                // Ambil jadwal pertama untuk ditampilkan
                const jadwalInfo = jadwalArray[0];
                const hariText = (jadwalInfo.hari && typeof jadwalInfo.hari === 'string') 
                    ? jadwalInfo.hari.charAt(0).toUpperCase() + jadwalInfo.hari.slice(1) 
                    : '';
                const jamMulaiTime = (jadwalInfo.jam_mulai && typeof jadwalInfo.jam_mulai === 'string')
                    ? jadwalInfo.jam_mulai.substring(0, 5)
                    : '';
                const jamSelesaiTime = (jadwalInfo.jam_selesai && typeof jadwalInfo.jam_selesai === 'string')
                    ? jadwalInfo.jam_selesai.substring(0, 5)
                    : '';
                const ruangText = jadwalInfo.ruang || '';
                
                row.className = 'table-success';
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${mk.kode_mk || ''}</strong></td>
                    <td>${mk.nama_mk || ''}</td>
                    <td class="text-center"><span class="badge badge-info">${mk.sks || 0} SKS</span></td>
                    <td><small>${mk.nama_dosen || ''}<br><span class="text-muted">${mk.nidn || ''}</span></small></td>
                    <td>
                        <span class="badge badge-success">${hariText}</span>
                        <input type="hidden" name="jadwal[${mk.id}][hari]" value="${jadwalInfo.hari || ''}">
                    </td>
                    <td>
                        <span class="text-success"><strong>${jamMulaiTime}</strong></span>
                        <input type="hidden" name="jadwal[${mk.id}][jam_mulai]" value="${jadwalInfo.jam_mulai || ''}">
                    </td>
                    <td>
                        <span class="text-success"><strong>${jamSelesaiTime}</strong></span>
                        <input type="hidden" name="jadwal[${mk.id}][jam_selesai]" value="${jadwalInfo.jam_selesai || ''}">
                    </td>
                    <td>
                        <small class="text-success"><strong>${ruangText}</strong></small>
                        <input type="hidden" name="jadwal[${mk.id}][id_ruang_kelas]" value="">
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success"><i class="fas fa-check"></i> Sudah dijadwalkan</span>
                    </td>
                `;
            } else {
                // Belum dijadwalkan, tampilkan form input
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${mk.kode_mk || ''}</strong></td>
                    <td>${mk.nama_mk || ''}</td>
                    <td class="text-center"><span class="badge badge-info">${mk.sks || 0} SKS</span></td>
                    <td><small>${mk.nama_dosen || ''}<br><span class="text-muted">${mk.nidn || ''}</span></small></td>
                    <td>
                        <select name="jadwal[${mk.id}][hari]" class="form-control form-control-sm" required>
                            <option value="">-- Pilih --</option>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][jam_mulai]" class="form-control form-control-sm jam-mulai-input" 
                               data-sks="${mk.sks}" data-mapping-id="${mk.id}" required>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][jam_selesai]" class="form-control form-control-sm jam-selesai-input" 
                               data-mapping-id="${mk.id}">
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][id_ruang_kelas]" class="form-control form-control-sm" required>
                            <option value="">-- Pilih --</option>
                            <?php if (!empty($ruang_list)): ?>
                                <?php foreach ($ruang_list as $r): ?>
                                    <option value="<?php echo $r->id; ?>"><?php echo $r->kode_ruang; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum dijadwalkan</span>
                    </td>
                `;
                
                // Auto calculate jam selesai
                const jamMulaiInput = row.querySelector('.jam-mulai-input');
                const jamSelesaiInput = row.querySelector('.jam-selesai-input');
                if (jamMulaiInput && jamSelesaiInput) {
                    jamMulaiInput.addEventListener('change', function() {
                        const sks = parseInt(this.dataset.sks);
                        const jamMulai = this.value;
                        if (jamMulai && sks) {
                            const time = new Date('2000-01-01T' + jamMulai + ':00');
                            const menitTotal = sks * 50;
                            time.setMinutes(time.getMinutes() + menitTotal);
                            const jamSelesai = time.toTimeString().substring(0, 5);
                            jamSelesaiInput.value = jamSelesai;
                        }
                    });
                }
            }
            
            jadwalListBody.appendChild(row);
        });
    }

    function resetJadwalForm() {
        kelasSelectJadwal.value = '';
        jadwalListContainer.style.display = 'none';
        btnSaveBatchJadwal.disabled = true;
        jadwalListBody.innerHTML = '';
    }

    // ========== JADWAL MANUAL ==========
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
        
        fetch('<?php echo site_url("Jadwal_kuliah/get_mapping_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas), {
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
