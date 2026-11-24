<div class="row">
    <div class="col-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Tambah Jadwal Pemadatan</h3>
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
                    <strong>Rules Jadwal Pemadatan (TIDAK BOLEH DIUBAH):</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Setiap mata kuliah akan bertemu 2x per minggu</strong> (untuk mempercepat perkuliahan)</li>
                        <li><strong>2x pertemuan tidak boleh di hari yang sama</strong> (harus di hari berbeda)</li>
                        <li>Hari perkuliahan: <strong>Senin - Jumat</strong></li>
                        <li>Jam mulai: 08:00 WIB, maksimal selesai: 17:30 WIB</li>
                        <li>1 SKS = 50 menit</li>
                        <li>Minimal 1 hari bisa 2-3 mata kuliah</li>
                        <li>Contoh: 3 SKS = 08:00-09:30, 10:00-12:30, 13:30-16:00</li>
                        <li>Ruangan random dan acak setiap mata kuliah</li>
                        <li><strong>TIDAK BOLEH BENTROK: kelas, dosen, ruangan (aturan tidak bisa diganggu gugat)</strong></li>
                        <li><strong>Hanya akan generate jadwal untuk mata kuliah yang belum dijadwalkan</strong></li>
                    </ul>
                </div>

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
                    <small class="form-text text-muted">Pilih kelas untuk menampilkan mata kuliah yang perlu dijadwalkan pemadatan</small>
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
                            <span class="badge badge-success">Hijau</span> = Sudah 2x pertemuan (lengkap) | 
                            <span class="badge badge-warning">Kuning</span> = Sudah 1x pertemuan (perlu dilengkapi) | 
                            <span class="badge badge-light">Putih</span> = Belum dijadwalkan (isi jadwal pemadatan - 2x pertemuan di hari berbeda)
                        </small>
                    </div>
                    
                    <?php echo form_open('Jadwal_kuliah/process_create_pemadatan', array('method' => 'post', 'id' => 'form-batch-jadwal-pemadatan')); ?>
                    <input type="hidden" name="kelas" id="batch-kelas-jadwal" value="">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-jadwal-list">
                            <thead>
                                <tr>
                                    <th width="3%">No</th>
                                    <th width="10%">Kode MK</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th width="6%" class="text-center">SKS</th>
                                    <th width="10%">Dosen</th>
                                    <th width="10%">Pertemuan 1 - Hari</th>
                                    <th width="8%">Pertemuan 1 - Jam Mulai</th>
                                    <th width="8%">Pertemuan 1 - Jam Selesai</th>
                                    <th width="10%">Pertemuan 1 - Ruang</th>
                                    <th width="10%">Pertemuan 2 - Hari</th>
                                    <th width="8%">Pertemuan 2 - Jam Mulai</th>
                                    <th width="8%">Pertemuan 2 - Jam Selesai</th>
                                    <th width="10%">Pertemuan 2 - Ruang</th>
                                    <th width="8%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="jadwal-list-body">
                                <!-- Akan diisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning" id="btn-save-batch-jadwal" disabled>
                            <i class="fas fa-save"></i> Simpan Jadwal Pemadatan
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetJadwalForm()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                loadingJadwal.style.display = 'none';

                if (data.success && data.data && data.data.length > 0) {
                    kelasSelectedJadwal.textContent = kelas;
                    batchKelasJadwal.value = kelas;
                    populateJadwalTable(data.data);
                    jadwalListContainer.style.display = 'block';
                    btnSaveBatchJadwal.disabled = false;
                } else {
                    errorTextJadwal.textContent = 'Tidak ada mata kuliah yang perlu dijadwalkan untuk kelas ' + kelas + '. Semua mata kuliah sudah dijadwalkan.';
                    errorMessageJadwal.style.display = 'block';
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                loadingJadwal.style.display = 'none';
                errorTextJadwal.textContent = 'Terjadi kesalahan saat memuat data.';
                errorMessageJadwal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingJadwal.style.display = 'none';
            errorTextJadwal.textContent = 'Terjadi kesalahan saat memuat data.';
            errorMessageJadwal.style.display = 'block';
        });
    }

    function populateJadwalTable(mataKuliahList) {
        jadwalListBody.innerHTML = '';
        mataKuliahList.forEach((mk, index) => {
            const row = document.createElement('tr');
            const isScheduled = mk.is_scheduled || false;
            const jadwalList = mk.jadwal || [];
            const jumlahPertemuan = mk.jumlah_pertemuan || 0;
            
            // Untuk jadwal pemadatan: tampilkan semua mata kuliah
            // Jika sudah 2x pertemuan, tampilkan detail jadwalnya
            // Jika belum 2x pertemuan, tampilkan form untuk melengkapi
            if (jumlahPertemuan >= 2) {
                // Sudah 2x pertemuan - tampilkan detail lengkap
                const pertemuan1 = jadwalList[0] || null;
                const pertemuan2 = jadwalList[1] || null;
                
                row.className = 'table-success';
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${mk.kode_mk}</strong></td>
                    <td>${mk.nama_mk}</td>
                    <td class="text-center"><span class="badge badge-info">${mk.sks} SKS</span></td>
                    <td><small>${mk.nama_dosen}<br><span class="text-muted">${mk.nidn}</span></small></td>
                    <td>
                        ${pertemuan1 && pertemuan1.hari ? '<span class="badge badge-success">' + pertemuan1.hari.charAt(0).toUpperCase() + pertemuan1.hari.slice(1) + '</span>' : '-'}
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.jam_mulai ? pertemuan1.jam_mulai.substring(0, 5) : '-'}
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.jam_selesai ? pertemuan1.jam_selesai.substring(0, 5) : '-'}
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.ruang ? '<small class="text-success"><strong>' + pertemuan1.ruang + '</strong></small>' : '-'}
                    </td>
                    <td>
                        ${pertemuan2 && pertemuan2.hari ? '<span class="badge badge-success">' + pertemuan2.hari.charAt(0).toUpperCase() + pertemuan2.hari.slice(1) + '</span>' : '-'}
                    </td>
                    <td>
                        ${pertemuan2 && pertemuan2.jam_mulai ? pertemuan2.jam_mulai.substring(0, 5) : '-'}
                    </td>
                    <td>
                        ${pertemuan2 && pertemuan2.jam_selesai ? pertemuan2.jam_selesai.substring(0, 5) : '-'}
                    </td>
                    <td>
                        ${pertemuan2 && pertemuan2.ruang ? '<small class="text-success"><strong>' + pertemuan2.ruang + '</strong></small>' : '-'}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success"><i class="fas fa-check"></i> Sudah 2x pertemuan</span>
                    </td>
                `;
            } else if (jumlahPertemuan == 1) {
                // Sudah 1x pertemuan - tampilkan detail pertemuan 1, form untuk pertemuan 2
                const pertemuan1 = jadwalList[0] || null;
                
                row.className = 'table-warning';
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${mk.kode_mk}</strong></td>
                    <td>${mk.nama_mk}</td>
                    <td class="text-center"><span class="badge badge-info">${mk.sks} SKS</span></td>
                    <td><small>${mk.nama_dosen}<br><span class="text-muted">${mk.nidn}</span></small></td>
                    <td>
                        ${pertemuan1 && pertemuan1.hari ? '<span class="badge badge-success">' + pertemuan1.hari.charAt(0).toUpperCase() + pertemuan1.hari.slice(1) + '</span>' : '-'}
                        <input type="hidden" name="jadwal[${mk.id}][pertemuan_1][hari]" value="${pertemuan1 && pertemuan1.hari ? pertemuan1.hari : ''}">
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.jam_mulai ? pertemuan1.jam_mulai.substring(0, 5) : '-'}
                        <input type="hidden" name="jadwal[${mk.id}][pertemuan_1][jam_mulai]" value="${pertemuan1 && pertemuan1.jam_mulai ? pertemuan1.jam_mulai : ''}">
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.jam_selesai ? pertemuan1.jam_selesai.substring(0, 5) : '-'}
                        <input type="hidden" name="jadwal[${mk.id}][pertemuan_1][jam_selesai]" value="${pertemuan1 && pertemuan1.jam_selesai ? pertemuan1.jam_selesai : ''}">
                    </td>
                    <td>
                        ${pertemuan1 && pertemuan1.ruang ? '<small class="text-success"><strong>' + pertemuan1.ruang + '</strong></small>' : '-'}
                        <input type="hidden" name="jadwal[${mk.id}][pertemuan_1][id_ruang_kelas]" value="${pertemuan1 && pertemuan1.id_ruang_kelas ? pertemuan1.id_ruang_kelas : ''}">
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_2][hari]" class="form-control form-control-sm pertemuan-hari" 
                                data-mapping-id="${mk.id}" data-pertemuan="2" required>
                            <option value="">-- Pilih --</option>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_2][jam_mulai]" 
                               class="form-control form-control-sm jam-mulai-input pertemuan-jam-mulai" 
                               data-sks="${mk.sks}" data-mapping-id="${mk.id}" data-pertemuan="2" required>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_2][jam_selesai]" 
                               class="form-control form-control-sm jam-selesai-input pertemuan-jam-selesai" 
                               data-mapping-id="${mk.id}" data-pertemuan="2">
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_2][id_ruang_kelas]" class="form-control form-control-sm" required>
                            <option value="">-- Pilih --</option>
                            <?php if (!empty($ruang_list)): ?>
                                <?php foreach ($ruang_list as $r): ?>
                                    <option value="<?php echo $r->id; ?>"><?php echo $r->kode_ruang; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Perlu 1 pertemuan lagi</span>
                    </td>
                `;
                
                // Auto calculate jam selesai untuk pertemuan 2
                const jamMulaiInput2 = row.querySelector('[data-pertemuan="2"].jam-mulai-input');
                const jamSelesaiInput2 = row.querySelector('[data-pertemuan="2"].jam-selesai-input');
                if (jamMulaiInput2 && jamSelesaiInput2) {
                    jamMulaiInput2.addEventListener('change', function() {
                        const sks = parseInt(this.dataset.sks);
                        const jamMulai = this.value;
                        if (jamMulai && sks) {
                            const time = new Date('2000-01-01T' + jamMulai + ':00');
                            const menitTotal = sks * 50;
                            time.setMinutes(time.getMinutes() + menitTotal);
                            const jamSelesai = time.toTimeString().substring(0, 5);
                            jamSelesaiInput2.value = jamSelesai;
                        }
                    });
                }
                
                // Validasi: pertemuan 2 tidak boleh di hari yang sama dengan pertemuan 1
                const hariSelect2 = row.querySelector('[data-pertemuan="2"].pertemuan-hari');
                if (hariSelect2 && pertemuan1) {
                    hariSelect2.addEventListener('change', function() {
                        if (this.value && this.value === pertemuan1.hari) {
                            this.setCustomValidity('Pertemuan 2 harus di hari berbeda dengan pertemuan 1');
                            this.classList.add('is-invalid');
                        } else {
                            this.setCustomValidity('');
                            this.classList.remove('is-invalid');
                        }
                    });
                }
            } else if (jumlahPertemuan == 0) {
                // Belum dijadwalkan, tampilkan form input untuk 2 pertemuan
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${mk.kode_mk}</strong></td>
                    <td>${mk.nama_mk}</td>
                    <td class="text-center"><span class="badge badge-info">${mk.sks} SKS</span></td>
                    <td><small>${mk.nama_dosen}<br><span class="text-muted">${mk.nidn}</span></small></td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_1][hari]" class="form-control form-control-sm pertemuan-hari" 
                                data-mapping-id="${mk.id}" data-pertemuan="1" required>
                            <option value="">-- Pilih --</option>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_1][jam_mulai]" 
                               class="form-control form-control-sm jam-mulai-input pertemuan-jam-mulai" 
                               data-sks="${mk.sks}" data-mapping-id="${mk.id}" data-pertemuan="1" required>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_1][jam_selesai]" 
                               class="form-control form-control-sm jam-selesai-input pertemuan-jam-selesai" 
                               data-mapping-id="${mk.id}" data-pertemuan="1">
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_1][id_ruang_kelas]" class="form-control form-control-sm" required>
                            <option value="">-- Pilih --</option>
                            <?php if (!empty($ruang_list)): ?>
                                <?php foreach ($ruang_list as $r): ?>
                                    <option value="<?php echo $r->id; ?>"><?php echo $r->kode_ruang; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_2][hari]" class="form-control form-control-sm pertemuan-hari" 
                                data-mapping-id="${mk.id}" data-pertemuan="2" required>
                            <option value="">-- Pilih --</option>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_2][jam_mulai]" 
                               class="form-control form-control-sm jam-mulai-input pertemuan-jam-mulai" 
                               data-sks="${mk.sks}" data-mapping-id="${mk.id}" data-pertemuan="2" required>
                    </td>
                    <td>
                        <input type="time" name="jadwal[${mk.id}][pertemuan_2][jam_selesai]" 
                               class="form-control form-control-sm jam-selesai-input pertemuan-jam-selesai" 
                               data-mapping-id="${mk.id}" data-pertemuan="2">
                    </td>
                    <td>
                        <select name="jadwal[${mk.id}][pertemuan_2][id_ruang_kelas]" class="form-control form-control-sm" required>
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
                
                // Auto calculate jam selesai untuk pertemuan 1
                const jamMulaiInput1 = row.querySelector('[data-pertemuan="1"].jam-mulai-input');
                const jamSelesaiInput1 = row.querySelector('[data-pertemuan="1"].jam-selesai-input');
                if (jamMulaiInput1 && jamSelesaiInput1) {
                    jamMulaiInput1.addEventListener('change', function() {
                        const sks = parseInt(this.dataset.sks);
                        const jamMulai = this.value;
                        if (jamMulai && sks) {
                            const time = new Date('2000-01-01T' + jamMulai + ':00');
                            const menitTotal = sks * 50;
                            time.setMinutes(time.getMinutes() + menitTotal);
                            const jamSelesai = time.toTimeString().substring(0, 5);
                            jamSelesaiInput1.value = jamSelesai;
                        }
                    });
                }
                
                // Auto calculate jam selesai untuk pertemuan 2
                const jamMulaiInput2 = row.querySelector('[data-pertemuan="2"].jam-mulai-input');
                const jamSelesaiInput2 = row.querySelector('[data-pertemuan="2"].jam-selesai-input');
                if (jamMulaiInput2 && jamSelesaiInput2) {
                    jamMulaiInput2.addEventListener('change', function() {
                        const sks = parseInt(this.dataset.sks);
                        const jamMulai = this.value;
                        if (jamMulai && sks) {
                            const time = new Date('2000-01-01T' + jamMulai + ':00');
                            const menitTotal = sks * 50;
                            time.setMinutes(time.getMinutes() + menitTotal);
                            const jamSelesai = time.toTimeString().substring(0, 5);
                            jamSelesaiInput2.value = jamSelesai;
                        }
                    });
                }
                
                // Validasi: 2 pertemuan tidak boleh di hari yang sama
                const hariSelect1 = row.querySelector('[data-pertemuan="1"].pertemuan-hari');
                const hariSelect2 = row.querySelector('[data-pertemuan="2"].pertemuan-hari');
                
                function validateHariBerbeda() {
                    if (hariSelect1.value && hariSelect2.value && hariSelect1.value === hariSelect2.value) {
                        hariSelect2.setCustomValidity('Pertemuan 2 harus di hari berbeda dengan pertemuan 1');
                        hariSelect2.classList.add('is-invalid');
                    } else {
                        hariSelect2.setCustomValidity('');
                        hariSelect2.classList.remove('is-invalid');
                    }
                }
                
                hariSelect1.addEventListener('change', validateHariBerbeda);
                hariSelect2.addEventListener('change', validateHariBerbeda);
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
    
    // Validasi form sebelum submit
    document.getElementById('form-batch-jadwal-pemadatan').addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Cek setiap row
        jadwalListBody.querySelectorAll('tr').forEach((row, index) => {
            // Skip row yang sudah 2x pertemuan (table-success)
            if (row.classList.contains('table-success')) {
                return;
            }
            
            const hari1Input = row.querySelector('[name*="[pertemuan_1][hari]"]');
            const hari2Input = row.querySelector('[name*="[pertemuan_2][hari]"]');
            
            if (!hari1Input || !hari2Input) return; // Skip row yang tidak memiliki input
            
            const hari1 = hari1Input.value;
            const hari2 = hari2Input.value;
            const jamMulai1 = row.querySelector('[name*="[pertemuan_1][jam_mulai]"]')?.value;
            const jamMulai2 = row.querySelector('[name*="[pertemuan_2][jam_mulai]"]')?.value;
            const ruang1 = row.querySelector('[name*="[pertemuan_1][id_ruang_kelas]"]')?.value;
            const ruang2 = row.querySelector('[name*="[pertemuan_2][id_ruang_kelas]"]')?.value;
            
            // Cek apakah row ini adalah row dengan jumlahPertemuan == 1 (table-warning)
            // Jika ya, pertemuan 1 sudah ada di database, jadi skip validasi pertemuan 1
            const isPertemuan1Exists = row.classList.contains('table-warning');
            
            // Validasi: 2 pertemuan harus di hari berbeda (hanya jika kedua hari diisi)
            if (hari1 && hari2 && hari1 === hari2) {
                isValid = false;
                const mkName = row.querySelector('td:nth-child(3)')?.textContent.trim() || 'Mata Kuliah';
                errors.push(`${mkName}: Pertemuan 1 dan 2 tidak boleh di hari yang sama`);
            }
            
            // Validasi pertemuan 1: hanya jika belum ada di database (bukan table-warning)
            if (!isPertemuan1Exists) {
                if (hari1 && (!jamMulai1 || !ruang1)) {
                    isValid = false;
                    const mkName = row.querySelector('td:nth-child(3)')?.textContent.trim() || 'Mata Kuliah';
                    errors.push(`${mkName}: Pertemuan 1 belum lengkap`);
                }
            }
            
            // Validasi pertemuan 2: selalu harus lengkap
            if (hari2 && (!jamMulai2 || !ruang2)) {
                isValid = false;
                const mkName = row.querySelector('td:nth-child(3)')?.textContent.trim() || 'Mata Kuliah';
                errors.push(`${mkName}: Pertemuan 2 belum lengkap`);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Validasi gagal:\n' + errors.join('\n'));
            return false;
        }
    });
});
</script>

