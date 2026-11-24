<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Kelas</h3>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchKelas" class="form-control" placeholder="Cari Kelas, Kurikulum...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <div class="form-inline justify-content-end">
                            <label class="mr-2">Tampilkan:</label>
                            <select id="per_page_kelas" class="form-control form-control-sm" disabled>
                                <option>Semua</option>
                            </select>
                            <span class="ml-2">data</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableKelas">
                        <thead>
                            <tr>
                                <th width="10%">Kelas</th>
                                <th width="15%">Jumlah Mahasiswa</th>
                                <th width="15%">Semester</th>
                                <th width="20%">Kurikulum</th>
                                <th width="30%">Mata Kuliah</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kelas_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data kelas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($kelas_list as $k): ?>
                                    <?php
                                    // Ambil semester dari kelas (misalnya 3A = semester 3)
                                    $semester_kelas = intval(substr($k->kelas, 0, 1));
                                    
                                    // Load model jika belum di-load
                                    $CI =& get_instance();
                                    $CI->load->model('Kelas_model');
                                    
                                    // Ambil kurikulum untuk kelas ini
                                    $kurikulum_kelas = $CI->Kelas_model->get_kurikulum_by_kelas($k->kelas);
                                    
                                    // Ambil mata kuliah untuk kelas ini
                                    $id_tahun_akademik = isset($tahun_aktif) && $tahun_aktif ? $tahun_aktif->id : null;
                                    $mata_kuliah_kelas = $CI->Kelas_model->get_mata_kuliah_by_kelas($k->kelas, $id_tahun_akademik);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="badge badge-primary" style="font-size: 14px;">
                                                <?php echo $k->kelas; ?>
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $k->jumlah_mahasiswa; ?> Mahasiswa</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary">Semester <?php echo $semester_kelas; ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($kurikulum_kelas)): ?>
                                                <?php foreach ($kurikulum_kelas as $kur): ?>
                                                    <small>
                                                        <strong><?php echo $kur->nama_kurikulum; ?></strong><br>
                                                        <span class="text-muted">Tahun <?php echo $kur->tahun_berlaku; ?></span>
                                                    </small>
                                                    <?php if (count($kurikulum_kelas) > 1): ?>
                                                        <br>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($mata_kuliah_kelas)): ?>
                                                <span class="badge badge-success"><?php echo count($mata_kuliah_kelas); ?> Mata Kuliah</span>
                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                    $mk_names = array();
                                                    $count = 0;
                                                    foreach ($mata_kuliah_kelas as $mk) {
                                                        if ($count < 3) {
                                                            $mk_names[] = $mk->kode_mk;
                                                            $count++;
                                                        }
                                                    }
                                                    echo implode(', ', $mk_names);
                                                    if (count($mata_kuliah_kelas) > 3) {
                                                        echo '...';
                                                    }
                                                    ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ada mata kuliah</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Kelas/detail/' . urlencode($k->kelas)); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchKelas');
    const table = document.getElementById('tableKelas');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    searchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

