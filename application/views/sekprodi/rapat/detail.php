<div class="row">
    <!-- Info Rapat -->
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Rapat</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Rapat/cetak_daftar_hadir/' . $rapat->id); ?>" 
                       class="btn btn-success btn-sm" title="Cetak Daftar Hadir" target="_blank">
                        <i class="fas fa-print"></i> Cetak Daftar Hadir
                    </a>
                    <a href="<?php echo site_url('Rapat/cetak_berita_acara/' . $rapat->id); ?>" 
                       class="btn btn-info btn-sm" 
                       title="Cetak Berita Acara" 
                       target="_blank">
                        <i class="fas fa-file-alt"></i> Cetak Berita Acara
                    </a>
                    <a href="<?php echo site_url('Rapat/edit/' . $rapat->id); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?php echo site_url('Rapat'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($rapat->judul_rapat); ?></h4>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Tanggal</th>
                                <td><?php echo date('d F Y', strtotime($rapat->tanggal_rapat)); ?></td>
                            </tr>
                            <tr>
                                <th>Waktu</th>
                                <td>
                                    <?php echo date('H:i', strtotime($rapat->jam_mulai)); ?>
                                    <?php if ($rapat->jam_selesai): ?>
                                        - <?php echo date('H:i', strtotime($rapat->jam_selesai)); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tempat</th>
                                <td><?php echo $rapat->tempat ? htmlspecialchars($rapat->tempat) : '-'; ?></td>
                            </tr>
                            <?php if ($rapat->notulen): ?>
                            <tr>
                                <th>Notulen</th>
                                <td><?php echo nl2br(htmlspecialchars($rapat->notulen)); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Statistik Kehadiran</h5>
                        <?php if (isset($rapat->statistik)): ?>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hadir</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['hadir']; ?></span>
                                </div>
                            </div>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tidak Hadir</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['tidak_hadir']; ?></span>
                                </div>
                            </div>
                            <?php if ($rapat->statistik['izin'] > 0): ?>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Izin</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['izin']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Peserta</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['total']; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Peserta -->
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Peserta</h3>
            </div>
            <div class="card-body">
                <!-- Form Tambah Peserta -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title">Tambah Peserta</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <select class="form-control" id="select_dosen" onchange="tambahPesertaDosen()">
                                            <option value="">-- Pilih Dosen --</option>
                                            <?php foreach ($dosen_list as $dosen): ?>
                                                <option value="<?php echo $dosen->id; ?>" data-nama="<?php echo htmlspecialchars($dosen->nama_dosen); ?>">
                                                    <?php echo htmlspecialchars($dosen->nama_dosen); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="select_tendik" onchange="tambahPesertaTendik()">
                                            <option value="">-- Pilih Tendik --</option>
                                            <?php foreach ($tendik_list as $tendik): ?>
                                                <option value="<?php echo $tendik->id; ?>" data-nama="<?php echo htmlspecialchars($tendik->nama_tendik); ?>">
                                                    <?php echo htmlspecialchars($tendik->nama_tendik); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="filter_kelas_detail" onchange="loadMahasiswaByKelasDetail()">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php if (!empty($kelas_list)): ?>
                                                <?php foreach ($kelas_list as $kelas): ?>
                                                    <option value="<?php echo htmlspecialchars($kelas->kelas); ?>">
                                                        <?php echo htmlspecialchars($kelas->kelas); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="select_mahasiswa" onchange="tambahPesertaMahasiswa()">
                                            <option value="">-- Pilih Mahasiswa --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="nama_eksternal" placeholder="Nama Peserta Eksternal">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" onclick="tambahPesertaEksternal()">
                                                    <i class="fas fa-plus"></i> Tambah Eksternal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Peserta -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tablePeserta">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="5%">Urutan</th>
                                <th width="22%">Nama Peserta</th>
                                <th width="10%">Role</th>
                                <th width="13%">Label untuk Cetak</th>
                                <th width="13%">Status Kehadiran</th>
                                <th width="17%">Keterangan</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rapat->peserta)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada peserta.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($rapat->peserta as $peserta): ?>
                                    <tr data-peserta-id="<?php echo $peserta->id; ?>">
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm urutan-input" 
                                                   data-peserta-id="<?php echo $peserta->id; ?>"
                                                   value="<?php echo $peserta->urutan ? $peserta->urutan : $no; ?>"
                                                   min="1"
                                                   style="width: 60px; text-align: center;"
                                                   onchange="updateUrutan(<?php echo $peserta->id; ?>, this.value)">
                                        </td>
                                        <td><?php echo htmlspecialchars($peserta->nama_peserta); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php 
                                                $role_labels = array(
                                                    'dosen' => 'Dosen',
                                                    'tendik' => 'Tendik',
                                                    'mahasiswa' => 'Mahasiswa',
                                                    'eksternal' => 'Eksternal',
                                                    'lainnya' => 'Lainnya'
                                                );
                                                echo isset($role_labels[$peserta->role_peserta]) ? $role_labels[$peserta->role_peserta] : $peserta->role_peserta;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (in_array($peserta->role_peserta, array('dosen', 'tendik', 'mahasiswa', 'eksternal'))): ?>
                                                <?php
                                                $default_label = '';
                                                if ($peserta->role_peserta == 'dosen') {
                                                    $default_label = 'Dosen';
                                                } elseif ($peserta->role_peserta == 'tendik') {
                                                    $default_label = 'Tendik';
                                                } elseif ($peserta->role_peserta == 'mahasiswa') {
                                                    $default_label = 'Mahasiswa';
                                                } elseif ($peserta->role_peserta == 'eksternal') {
                                                    $default_label = 'Eksternal';
                                                }
                                                ?>
                                                <input type="text" class="form-control form-control-sm label-role" 
                                                       data-peserta-id="<?php echo $peserta->id; ?>"
                                                       value="<?php echo htmlspecialchars($peserta->label_role ? $peserta->label_role : $default_label); ?>"
                                                       placeholder="Label (misal: Dosen, Ketua, dll)"
                                                       onblur="updateLabelRole(<?php echo $peserta->id; ?>, this.value)">
                                                <small class="text-muted">Untuk cetak</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-sm status-kehadiran" 
                                                    data-peserta-id="<?php echo $peserta->id; ?>"
                                                    onchange="updateKehadiran(<?php echo $peserta->id; ?>, this.value)">
                                                <option value="tidak_hadir" <?php echo $peserta->status_kehadiran == 'tidak_hadir' ? 'selected' : ''; ?>>Tidak Hadir</option>
                                                <option value="hadir" <?php echo $peserta->status_kehadiran == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="izin" <?php echo $peserta->status_kehadiran == 'izin' ? 'selected' : ''; ?>>Izin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm keterangan" 
                                                   data-peserta-id="<?php echo $peserta->id; ?>"
                                                   value="<?php echo htmlspecialchars($peserta->keterangan); ?>"
                                                   placeholder="Keterangan (opsional)">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="hapusPeserta(<?php echo $peserta->id; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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


    <!-- Dokumentasi -->
    <div class="col-md-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Dokumentasi Rapat</h3>
            </div>
            <div class="card-body">
                <!-- Form Upload -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title">Upload Dokumentasi</h5>
                            </div>
                            <div class="card-body">
                                <form id="formUploadDokumentasi" enctype="multipart/form-data">
                                    <input type="hidden" name="id_rapat" value="<?php echo $rapat->id; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Pilih File (JPG, PNG, GIF, PDF - Max 5MB)</label>
                                                <input type="file" class="form-control-file" name="file" id="file_dokumentasi" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Keterangan (Opsional)</label>
                                                <input type="text" class="form-control" name="keterangan" placeholder="Keterangan foto">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-success btn-block">
                                                    <i class="fas fa-upload"></i> Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Galeri Dokumentasi -->
                <div class="row" id="galeri_dokumentasi">
                    <?php if (empty($rapat->dokumentasi)): ?>
                        <div class="col-md-12">
                            <p class="text-muted text-center">Belum ada dokumentasi.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rapat->dokumentasi as $dok): ?>
                            <div class="col-md-3 mb-3" data-dok-id="<?php echo $dok->id; ?>">
                                <div class="card">
                                    <?php 
                                    // Cek apakah file adalah gambar berdasarkan file_type atau extension
                                    $is_image = false;
                                    if (isset($dok->file_type) && strpos(strtolower($dok->file_type), 'image') !== false) {
                                        $is_image = true;
                                    } elseif (isset($dok->file_path) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $dok->file_path)) {
                                        $is_image = true;
                                    } elseif (isset($dok->file_name) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $dok->file_name)) {
                                        $is_image = true;
                                    }
                                    ?>
                                    <?php if ($is_image): ?>
                                        <?php 
                                        // Cari file yang benar-benar ada
                                        $image_path = trim($dok->file_path);
                                        $image_path = ltrim($image_path, '/');
                                        $image_path = str_replace('\\', '/', $image_path);
                                        
                                        $final_path = $image_path;
                                        
                                        // Cek apakah file ada berdasarkan path di database
                                        if (!empty($image_path)) {
                                            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $image_path);
                                            if (!file_exists($full_path)) {
                                                // Jika tidak ditemukan, cari file berdasarkan nama asli
                                                $upload_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'rapat' . DIRECTORY_SEPARATOR;
                                                if (is_dir($upload_dir) && !empty($dok->file_name)) {
                                                    // Cari file yang mungkin sesuai
                                                    $files = glob($upload_dir . '*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                                                    if (!empty($files)) {
                                                        // Sort berdasarkan waktu modifikasi (terbaru dulu)
                                                        usort($files, function($a, $b) {
                                                            return filemtime($b) - filemtime($a);
                                                        });
                                                        
                                                        // Ambil file berdasarkan urutan ID dokumentasi
                                                        $file_index = isset($dok->id) ? (($dok->id - 1) % count($files)) : 0;
                                                        if (isset($files[$file_index])) {
                                                            $final_path = 'uploads/rapat/' . basename($files[$file_index]);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        $image_url = base_url($final_path);
                                        ?>
                                        <img src="<?php echo $image_url; ?>" class="card-img-top" 
                                             style="height: 200px; object-fit: cover; width: 100%; cursor: pointer;" 
                                             alt="<?php echo htmlspecialchars($dok->file_name); ?>"
                                             onclick="window.open('<?php echo $image_url; ?>', '_blank')"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'200\'%3E%3Crect fill=\'%23f0f0f0\' width=\'300\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'Arial\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dominant-baseline=\'middle\'%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E'; console.error('Gagal memuat gambar: <?php echo htmlspecialchars($image_url, ENT_QUOTES); ?>');">
                                    <?php else: ?>
                                        <div class="card-img-top d-flex align-items-center justify-content-center" 
                                             style="height: 200px; background: #f0f0f0; cursor: pointer;"
                                             onclick="window.open('<?php echo base_url($dok->file_path); ?>', '_blank')">
                                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-2">
                                        <p class="card-text small mb-1">
                                            <strong><?php echo htmlspecialchars($dok->file_name); ?></strong>
                                        </p>
                                        <?php if ($dok->keterangan): ?>
                                            <p class="card-text small text-muted mb-1">
                                                <?php echo htmlspecialchars($dok->keterangan); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <?php 
                                            // Gunakan path yang sama dengan gambar
                                            $file_url = base_url($final_path);
                                            ?>
                                            <a href="<?php echo $file_url; ?>" 
                                               target="_blank" 
                                               class="btn btn-info btn-sm"
                                               title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo $file_url; ?>" 
                                               download="<?php echo htmlspecialchars($dok->file_name); ?>"
                                               class="btn btn-success btn-sm"
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    onclick="hapusDokumentasi(<?php echo $dok->id; ?>)"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pastikan DOM sudah ready
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi setelah DOM ready
});

const idRapat = <?php echo $rapat->id; ?>;

// Tambah Peserta Dosen
function tambahPesertaDosen() {
    const select = document.getElementById('select_dosen');
    const option = select.options[select.selectedIndex];
    if (option.value) {
        tambahPeserta(null, option.dataset.nama, 'dosen');
        select.value = '';
    }
}

// Tambah Peserta Tendik
function tambahPesertaTendik() {
    const select = document.getElementById('select_tendik');
    const option = select.options[select.selectedIndex];
    if (option.value) {
        tambahPeserta(null, option.dataset.nama, 'tendik');
        select.value = '';
    }
}

// Tambah Peserta Eksternal
function tambahPesertaEksternal() {
    const input = document.getElementById('nama_eksternal');
    const nama = input.value.trim();
    if (nama) {
        tambahPeserta(null, nama, 'eksternal');
        input.value = '';
    }
}

// Load Mahasiswa by Kelas (untuk detail page)
function loadMahasiswaByKelasDetail() {
    const kelas = document.getElementById('filter_kelas_detail').value;
    const select = document.getElementById('select_mahasiswa');
    
    // Reset select
    select.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
    
    if (!kelas) {
        return;
    }
    
    // Show loading
    select.innerHTML = '<option value="">Memuat...</option>';
    select.disabled = true;
    
    // AJAX request
    fetch('<?php echo site_url("Rapat/get_mahasiswa_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas))
        .then(response => response.json())
        .then(data => {
            select.disabled = false;
            if (data.success && data.mahasiswa && data.mahasiswa.length > 0) {
                select.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
                data.mahasiswa.forEach(function(mhs) {
                    const option = document.createElement('option');
                    option.value = mhs.id;
                    option.textContent = mhs.nama_mahasiswa + ' (' + mhs.nim + ')';
                    option.dataset.nama = mhs.nama_mahasiswa + ' (' + mhs.nim + ')';
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Tidak ada mahasiswa aktif</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            select.disabled = false;
            select.innerHTML = '<option value="">Error memuat data</option>';
        });
}

// Tambah Peserta Mahasiswa
function tambahPesertaMahasiswa() {
    const select = document.getElementById('select_mahasiswa');
    const option = select.options[select.selectedIndex];
    if (option.value && option.dataset.nama) {
        tambahPeserta(null, option.dataset.nama, 'mahasiswa');
        select.value = '';
        // Reset kelas filter juga
        document.getElementById('filter_kelas_detail').value = '';
        select.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
    }
}

// AJAX Tambah Peserta
function tambahPeserta(idUser, namaPeserta, rolePeserta) {
    $.ajax({
        url: '<?php echo site_url("Rapat/tambah_peserta"); ?>',
        method: 'POST',
        data: {
            id_rapat: idRapat,
            nama_peserta: namaPeserta,
            role_peserta: rolePeserta
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Gagal menambah peserta.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat menambah peserta.');
        }
    });
}

// Update Status Kehadiran
function updateKehadiran(idPeserta, status) {
    const row = document.querySelector(`tr[data-peserta-id="${idPeserta}"]`);
    const keterangan = row.querySelector('.keterangan').value;
    
    $.ajax({
        url: '<?php echo site_url("Rapat/update_kehadiran"); ?>',
        method: 'POST',
        data: {
            id_peserta: idPeserta,
            status: status,
            keterangan: keterangan
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update statistik jika perlu
                if (response.statistik) {
                    location.reload(); // Reload untuk update statistik
                }
            } else {
                alert(response.message || 'Gagal update status kehadiran.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat update status kehadiran.');
        }
    });
}

// Update Label Role untuk Cetak
function updateLabelRole(idPeserta, labelRole) {
    $.ajax({
        url: '<?php echo site_url("Rapat/update_label_role"); ?>',
        method: 'POST',
        data: {
            id_peserta: idPeserta,
            label_role: labelRole
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Optional: Show success message
                // console.log('Label role berhasil diupdate');
            } else {
                alert(response.message || 'Gagal update label role.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat update label role.');
        }
    });
}

// Update Urutan Peserta
function updateUrutan(idPeserta, urutan) {
    $.ajax({
        url: '<?php echo site_url("Rapat/update_urutan"); ?>',
        method: 'POST',
        data: {
            id_peserta: idPeserta,
            urutan: urutan
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Reload untuk update urutan tampilan
                location.reload();
            } else {
                alert(response.message || 'Gagal update urutan.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat update urutan.');
        }
    });
}

// Hapus Peserta
function hapusPeserta(idPeserta) {
    if (!confirm('Apakah Anda yakin ingin menghapus peserta ini?')) {
        return;
    }
    
    $.ajax({
        url: '<?php echo site_url("Rapat/hapus_peserta"); ?>',
        method: 'POST',
        data: { id_peserta: idPeserta },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Gagal menghapus peserta.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat menghapus peserta.');
        }
    });
}


// Upload Dokumentasi
const formUploadDokumentasi = document.getElementById('formUploadDokumentasi');
if (formUploadDokumentasi) {
    formUploadDokumentasi.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        if (typeof window.appendSiapCsrf === 'function') {
            window.appendSiapCsrf(formData);
        }
        
        // Gunakan jQuery jika tersedia
        if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
            const $jq = typeof $ !== 'undefined' ? $ : jQuery;
            $jq.ajax({
                url: '<?php echo site_url("Rapat/upload_dokumentasi"); ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Gagal upload dokumentasi.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat upload dokumentasi.');
                }
            });
        } else {
            // Fallback dengan fetch API
            fetch('<?php echo site_url("Rapat/upload_dokumentasi"); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal upload dokumentasi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat upload dokumentasi.');
            });
        }
    });
}

// Hapus Dokumentasi
function hapusDokumentasi(idDok) {
    if (!confirm('Apakah Anda yakin ingin menghapus dokumentasi ini?')) {
        return;
    }
    
    console.log('Menghapus dokumentasi ID:', idDok);
    
    // Gunakan jQuery jika tersedia, jika tidak gunakan fetch API
    if (typeof $ !== 'undefined' || typeof jQuery !== 'undefined') {
        const $jq = typeof $ !== 'undefined' ? $ : jQuery;
        $jq.ajax({
            url: '<?php echo site_url("Rapat/hapus_dokumentasi"); ?>',
            method: 'POST',
            data: { id: idDok },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const element = document.querySelector(`div[data-dok-id="${idDok}"]`);
                    if (element) {
                        element.remove();
                    } else {
                        // Fallback: reload halaman
                        location.reload();
                    }
                } else {
                    alert(response.message || 'Gagal menghapus dokumentasi.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus dokumentasi.');
            }
        });
    } else {
        // Fallback dengan fetch API
        fetch('<?php echo site_url("Rapat/hapus_dokumentasi"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + encodeURIComponent(idDok)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = document.querySelector(`div[data-dok-id="${idDok}"]`);
                if (element) {
                    element.remove();
                } else {
                    location.reload();
                }
            } else {
                alert(data.message || 'Gagal menghapus dokumentasi.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus dokumentasi.');
        });
    }
}
</script>


