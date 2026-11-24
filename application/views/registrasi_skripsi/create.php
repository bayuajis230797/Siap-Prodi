<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Mahasiswa Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Registrasi_skripsi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?php echo form_open('Registrasi_skripsi/create', array('method' => 'post')); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <!-- Filter Kelas -->
                <div class="form-group">
                    <label for="filter_kelas">Filter Berdasarkan Kelas</label>
                    <select name="filter_kelas" id="filter_kelas" class="form-control">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo htmlspecialchars($kelas); ?>">
                                <?php echo htmlspecialchars($kelas); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Pilih kelas untuk memfilter daftar mahasiswa.</small>
                </div>

                <div class="form-group">
                    <label>Pilih Mahasiswa <span class="text-danger">*</span></label>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th width="10%">NIM</th>
                                    <th width="25%">Nama Mahasiswa</th>
                                    <th width="10%">Kelas</th>
                                    <th width="15%">Angkatan</th>
                                    <th width="15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $registered_ids = array();
                                if (!empty($skripsi_list)) {
                                    foreach ($skripsi_list as $reg) {
                                        if (isset($reg->id_mahasiswa)) {
                                            $registered_ids[] = $reg->id_mahasiswa;
                                        }
                                    }
                                }
                                foreach ($mahasiswa_list as $mhs): 
                                    $is_registered = in_array($mhs->id, $registered_ids);
                                ?>
                                    <tr class="mahasiswa-row <?php echo $is_registered ? 'table-secondary' : ''; ?>" 
                                        data-kelas="<?php echo htmlspecialchars($mhs->kelas); ?>">
                                        <td>
                                            <?php if (!$is_registered): ?>
                                                <input type="checkbox" name="id_mahasiswa[]" value="<?php echo $mhs->id; ?>" class="mahasiswa-checkbox">
                                            <?php else: ?>
                                                <span class="text-muted">✓</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mhs->nim); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->kelas); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->angkatan); ?></td>
                                        <td>
                                            <?php if ($is_registered): ?>
                                                <span class="badge badge-success">Sudah Terdaftar</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum Terdaftar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="form-text text-muted">Mahasiswa yang sudah terdaftar Skripsi tidak dapat dipilih lagi.</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Registrasi_skripsi'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const visibleRows = document.querySelectorAll('.mahasiswa-row[style=""], .mahasiswa-row:not([style])');
    visibleRows.forEach(row => {
        const cb = row.querySelector('.mahasiswa-checkbox');
        if (cb && row.style.display !== 'none') {
            cb.checked = checkbox.checked;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const filterKelas = document.getElementById('filter_kelas');
    const mahasiswaRows = document.querySelectorAll('.mahasiswa-row');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    function updateSelectAllState() {
        if (!selectAllCheckbox) return;
        const visibleCheckboxes = Array.from(mahasiswaRows)
            .filter(row => row.style.display !== 'none')
            .map(row => row.querySelector('.mahasiswa-checkbox'))
            .filter(cb => cb !== null);
        
        const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
        selectAllCheckbox.checked = visibleCheckboxes.length > 0 && checkedCount === visibleCheckboxes.length;
    }
    
    if (filterKelas) {
        filterKelas.addEventListener('change', function() {
            const selectedKelas = this.value;
            
            mahasiswaRows.forEach(function(row) {
                const rowKelas = row.getAttribute('data-kelas');
                if (selectedKelas === '' || rowKelas === selectedKelas) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    // Uncheck checkbox jika row disembunyikan
                    const checkbox = row.querySelector('.mahasiswa-checkbox');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            });
            
            updateSelectAllState();
        });
    }
    
    // Update select all state saat checkbox individual diubah
    mahasiswaRows.forEach(row => {
        const checkbox = row.querySelector('.mahasiswa-checkbox');
        if (checkbox) {
            checkbox.addEventListener('change', updateSelectAllState);
        }
    });
});
</script>

