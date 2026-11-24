<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Tambah Rapat Baru</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Rapat/store', array('id' => 'formRapat')); ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="judul_rapat">Judul Rapat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul_rapat" name="judul_rapat" 
                                   value="<?php echo set_value('judul_rapat'); ?>" required>
                            <?php echo form_error('judul_rapat', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_rapat">Tanggal Rapat <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_rapat" name="tanggal_rapat" 
                                   value="<?php echo set_value('tanggal_rapat'); ?>" required>
                            <?php echo form_error('tanggal_rapat', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                   value="<?php echo set_value('jam_mulai'); ?>" required>
                            <?php echo form_error('jam_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                   value="<?php echo set_value('jam_selesai'); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="tempat">Tempat</label>
                            <input type="text" class="form-control" id="tempat" name="tempat" 
                                   value="<?php echo set_value('tempat'); ?>" placeholder="Contoh: Ruang Rapat Prodi">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notulen">Notulen</label>
                            <textarea class="form-control" id="notulen" name="notulen" rows="5" 
                                      placeholder="Catatan notulen rapat..."><?php echo set_value('notulen'); ?></textarea>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Pilih Peserta Rapat</h5>

                <!-- Dosen -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Dosen</label>
                        <div class="form-group" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <?php if (!empty($dosen_list)): ?>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="peserta_dosen[]" 
                                               value="<?php echo $dosen->id; ?>" id="dosen_<?php echo $dosen->id; ?>">
                                        <label class="form-check-label" for="dosen_<?php echo $dosen->id; ?>">
                                            <?php echo htmlspecialchars($dosen->nama_dosen); ?> 
                                            <small class="text-muted">(<?php echo htmlspecialchars($dosen->nidn); ?>)</small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada data dosen.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tendik -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Tenaga Kependidikan</label>
                        <div class="form-group" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <?php if (!empty($tendik_list)): ?>
                                <?php foreach ($tendik_list as $tendik): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="peserta_tendik[]" 
                                               value="<?php echo $tendik->id; ?>" id="tendik_<?php echo $tendik->id; ?>">
                                        <label class="form-check-label" for="tendik_<?php echo $tendik->id; ?>">
                                            <?php echo htmlspecialchars($tendik->nama_tendik); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada data tendik.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Mahasiswa -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Mahasiswa</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-control" id="filter_kelas" onchange="loadMahasiswaByKelas()">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php if (!empty($kelas_list)): ?>
                                        <?php foreach ($kelas_list as $kelas): ?>
                                            <option value="<?php echo htmlspecialchars($kelas->kelas); ?>">
                                                <?php echo htmlspecialchars($kelas->kelas); ?> (<?php echo $kelas->jumlah; ?> mahasiswa)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <div id="mahasiswa_container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    <p class="text-muted">Pilih kelas terlebih dahulu untuk menampilkan daftar mahasiswa.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peserta Eksternal -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Peserta Eksternal (Opsional)</label>
                        <div id="peserta_eksternal_container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="peserta_eksternal[]" 
                                       placeholder="Nama peserta eksternal">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" onclick="tambahPesertaEksternal()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url('Rapat'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
var selectedMahasiswa = []; // Array untuk menyimpan ID mahasiswa yang dipilih

function tambahPesertaEksternal() {
    const container = document.getElementById('peserta_eksternal_container');
    const newInput = document.createElement('div');
    newInput.className = 'input-group mb-2';
    newInput.innerHTML = `
        <input type="text" class="form-control" name="peserta_eksternal[]" placeholder="Nama peserta eksternal">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newInput);
}

function loadMahasiswaByKelas() {
    const kelas = document.getElementById('filter_kelas').value;
    const container = document.getElementById('mahasiswa_container');
    
    if (!kelas) {
        container.innerHTML = '<p class="text-muted">Pilih kelas terlebih dahulu untuk menampilkan daftar mahasiswa.</p>';
        selectedMahasiswa = [];
        return;
    }
    
    // Show loading
    container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data mahasiswa...</p>';
    
    // AJAX request
    fetch('<?php echo site_url("Rapat/get_mahasiswa_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.mahasiswa && data.mahasiswa.length > 0) {
                let html = '';
                data.mahasiswa.forEach(function(mhs) {
                    const isChecked = selectedMahasiswa.includes(mhs.id.toString());
                    html += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="peserta_mahasiswa[]" 
                                   value="${mhs.id}" id="mhs_${mhs.id}" 
                                   ${isChecked ? 'checked' : ''} 
                                   onchange="toggleMahasiswa(${mhs.id}, this.checked)">
                            <label class="form-check-label" for="mhs_${mhs.id}">
                                ${mhs.nama_mahasiswa} 
                                <small class="text-muted">(${mhs.nim})</small>
                            </label>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">Tidak ada mahasiswa aktif di kelas ini.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-danger">Terjadi kesalahan saat memuat data mahasiswa.</p>';
        });
}

function toggleMahasiswa(id, isChecked) {
    const idStr = id.toString();
    if (isChecked) {
        if (!selectedMahasiswa.includes(idStr)) {
            selectedMahasiswa.push(idStr);
        }
    } else {
        selectedMahasiswa = selectedMahasiswa.filter(mhsId => mhsId !== idStr);
    }
}
</script>







