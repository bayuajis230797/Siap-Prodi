<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Mahasiswa Skripsi</h3>
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

                <?php echo form_open('Skripsi/create', array('method' => 'post', 'id' => 'form-skripsi')); ?>
                
                <div class="form-group">
                    <label for="kelas_select">Pilih Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_select" id="kelas_select" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php if (!empty($kelas_list)): ?>
                            <?php foreach ($kelas_list as $k): ?>
                                <option value="<?php echo $k->kelas; ?>">
                                    <?php echo $k->kelas; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Pilih kelas untuk menampilkan mahasiswa yang dapat ditambahkan ke Skripsi</small>
                </div>

                <div id="loading-mhs" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data mahasiswa...
                </div>

                <div id="mhs-error-message" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian:</strong> <span id="error-text"></span>
                </div>

                <div id="mhs-list-container" style="display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Mahasiswa Kelas:</strong> <span id="kelas-selected"></span>
                        <br><small class="text-muted"><i class="fas fa-info-circle"></i> Pilih mahasiswa yang akan ditambahkan ke Skripsi. Mahasiswa yang sudah terdaftar Skripsi tidak akan ditampilkan.</small>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th width="5%">No</th>
                                    <th width="15%">NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th width="10%" class="text-center">Kelas</th>
                                </tr>
                            </thead>
                            <tbody id="mhs-list-body">
                                <!-- Akan diisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="btn-save-skripsi" disabled>
                            <i class="fas fa-save"></i> Simpan Mahasiswa Skripsi
                        </button>
                        <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.getElementById('kelas_select');
    const loadingMhs = document.getElementById('loading-mhs');
    const errorMessage = document.getElementById('mhs-error-message');
    const mhsListContainer = document.getElementById('mhs-list-container');
    const mhsListBody = document.getElementById('mhs-list-body');
    const btnSaveSkripsi = document.getElementById('btn-save-skripsi');
    const selectAll = document.getElementById('select-all');
    const kelasSelected = document.getElementById('kelas-selected');

    kelasSelect.addEventListener('change', function() {
        const kelas = this.value;
        
        if (!kelas) {
            mhsListContainer.style.display = 'none';
            errorMessage.style.display = 'none';
            return;
        }

        // Show loading
        loadingMhs.style.display = 'block';
        errorMessage.style.display = 'none';
        mhsListContainer.style.display = 'none';
        btnSaveSkripsi.disabled = true;

        // AJAX request
        fetch('<?php echo site_url("Skripsi/get_mahasiswa_by_kelas"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'kelas=' + encodeURIComponent(kelas)
        })
        .then(response => response.json())
        .then(data => {
            loadingMhs.style.display = 'none';
            
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    kelasSelected.textContent = kelas;
                    renderMahasiswaList(data.data);
                    mhsListContainer.style.display = 'block';
                } else {
                    errorMessage.querySelector('#error-text').textContent = 'Tidak ada mahasiswa aktif di kelas ' + kelas + ' yang belum terdaftar Skripsi.';
                    errorMessage.style.display = 'block';
                }
            } else {
                errorMessage.querySelector('#error-text').textContent = data.message || 'Terjadi kesalahan saat memuat data mahasiswa.';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            loadingMhs.style.display = 'none';
            errorMessage.querySelector('#error-text').textContent = 'Terjadi kesalahan: ' + error.message;
            errorMessage.style.display = 'block';
        });
    });

    function renderMahasiswaList(mahasiswa) {
        mhsListBody.innerHTML = '';
        
        mahasiswa.forEach((mhs, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" name="mahasiswa_ids[]" value="${mhs.id}" class="mahasiswa-checkbox">
                </td>
                <td>${index + 1}</td>
                <td><strong>${mhs.nim}</strong></td>
                <td>${mhs.nama_mahasiswa}</td>
                <td class="text-center"><span class="badge badge-info">${mhs.kelas}</span></td>
            `;
            mhsListBody.appendChild(row);
        });

        // Add event listeners to checkboxes
        const checkboxes = document.querySelectorAll('.mahasiswa-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSaveButton);
        });
    }

    // Select all checkbox
    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.mahasiswa-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateSaveButton();
    });

    function updateSaveButton() {
        const checked = document.querySelectorAll('.mahasiswa-checkbox:checked');
        btnSaveSkripsi.disabled = checked.length === 0;
    }

    // Form submission
    document.getElementById('form-skripsi').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.mahasiswa-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu mahasiswa untuk ditambahkan ke Skripsi.');
            return false;
        }
    });
});
</script>













