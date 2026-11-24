<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Pengajuan Tempat PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_tempat_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($tempat->nim); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($tempat->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($tempat->kelas ?: '-'); ?></span></td>
                            </tr>
                            <tr>
                                <th>Tahun Akademik</th>
                                <td><?php echo htmlspecialchars($tempat->tahun_akademik . ' ' . ucfirst($tempat->semester)); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-building"></i> Informasi Tempat PKL</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Nama Instansi</th>
                                <td><?php echo htmlspecialchars($tempat->nama_instansi); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat Instansi</th>
                                <td><?php echo nl2br(htmlspecialchars($tempat->alamat_instansi ?: '-')); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <td><?php echo $tempat->tanggal_mulai ? date('d/m/Y', strtotime($tempat->tanggal_mulai)) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td><?php echo $tempat->tanggal_selesai ? date('d/m/Y', strtotime($tempat->tanggal_selesai)) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Koordinat GPS</th>
                                <td>
                                    <?php if ($tempat->latitude && $tempat->longitude): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo number_format($tempat->latitude, 8); ?>, <?php echo number_format($tempat->longitude, 8); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php
                                    $status_class = 'warning';
                                    $status_text = ucfirst($tempat->status_acc_tempat ?: 'Pending');
                                    switch ($tempat->status_acc_tempat) {
                                        case 'disetujui':
                                            $status_class = 'success';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'danger';
                                            break;
                                        default:
                                            $status_class = 'warning';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Peta Lokasi Tempat PKL -->
                <?php if ($tempat->latitude && $tempat->longitude): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-map"></i> Peta Lokasi Tempat PKL</h5>
                            <div id="map-tempat-pkl" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Peta menampilkan lokasi koordinat tempat PKL yang diajukan oleh mahasiswa.
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Koordinat GPS tempat PKL belum diisi oleh mahasiswa.
                    </div>
                <?php endif; ?>

                <!-- Progress PKL Mahasiswa -->
                <?php if (isset($pkl_progress) && $pkl_progress): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Progress PKL Mahasiswa</h5>
                            <div class="card card-outline card-info">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center mb-3">
                                            <div class="progress-circle-wrapper">
                                                <div class="progress-circle" data-percent="<?php echo $pkl_progress['percentage']; ?>" data-color="#17a2b8">
                                                    <svg class="progress-circle-svg" viewBox="0 0 200 200">
                                                        <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                                        <circle class="progress-circle-progress" cx="100" cy="100" r="90" 
                                                                data-percent="<?php echo $pkl_progress['percentage']; ?>"></circle>
                                                    </svg>
                                                    <div class="progress-circle-content">
                                                        <span class="progress-circle-percent"><?php echo $pkl_progress['percentage']; ?>%</span>
                                                        <span class="progress-circle-text"><?php echo $pkl_progress['current_step']; ?>/<?php echo $pkl_progress['total_steps']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="mb-3">Tahapan PKL</h6>
                                            <div class="progress-steps">
                                                <?php foreach ($pkl_progress['steps'] as $index => $step): ?>
                                                    <div class="progress-step-item <?php echo $step['completed'] ? 'completed' : ''; ?>">
                                                        <div class="step-indicator">
                                                            <?php if ($step['completed']): ?>
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-circle text-muted"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="step-label"><?php echo $step['name']; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                    .progress-circle-wrapper {
                        display: flex;
                        justify-content: center;
                    }
                    
                    .progress-circle {
                        position: relative;
                        width: 150px;
                        height: 150px;
                        margin: 0 auto;
                    }
                    
                    .progress-circle-svg {
                        width: 100%;
                        height: 100%;
                        transform: rotate(-90deg);
                    }
                    
                    .progress-circle-bg {
                        fill: none;
                        stroke: #e0e0e0;
                        stroke-width: 12;
                    }
                    
                    .progress-circle-progress {
                        fill: none;
                        stroke-width: 12;
                        stroke-linecap: round;
                        stroke-dasharray: 565.48;
                        stroke-dashoffset: 565.48;
                        transition: stroke-dashoffset 1s ease-in-out;
                    }
                    
                    .progress-circle-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        text-align: center;
                    }
                    
                    .progress-circle-percent {
                        display: block;
                        font-size: 24px;
                        font-weight: bold;
                        color: #333;
                    }
                    
                    .progress-circle-text {
                        display: block;
                        font-size: 12px;
                        color: #666;
                        margin-top: 3px;
                    }
                    
                    .progress-steps {
                        text-align: left;
                        max-height: 400px;
                        overflow-y: auto;
                        padding: 5px;
                    }
                    
                    .progress-step-item {
                        display: flex;
                        align-items: center;
                        padding: 8px 0;
                        border-bottom: 1px solid #f0f0f0;
                    }
                    
                    .progress-step-item:last-child {
                        border-bottom: none;
                    }
                    
                    .progress-step-item.completed .step-label {
                        color: #28a745;
                        font-weight: 500;
                    }
                    
                    .step-indicator {
                        width: 30px;
                        text-align: center;
                        margin-right: 10px;
                    }
                    
                    .step-indicator i {
                        font-size: 18px;
                    }
                    
                    .step-label {
                        flex: 1;
                        font-size: 14px;
                        color: #666;
                    }
                    </style>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var pklCircle = document.querySelector('.progress-circle-progress');
                        if (pklCircle) {
                            var pklPercent = <?php echo $pkl_progress['percentage']; ?>;
                            var pklColor = '#17a2b8';
                            var circumference = 2 * Math.PI * 90;
                            var offset = circumference - (pklPercent / 100) * circumference;
                            pklCircle.style.strokeDashoffset = offset;
                            pklCircle.style.stroke = pklColor;
                        }
                    });
                    </script>
                <?php endif; ?>

                <!-- Informasi Pembimbing Lapangan -->
                <?php if ($tempat->nama_pembimbing_lapangan): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-user-tie"></i> Informasi Pembimbing Lapangan</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="15%">Nama</th>
                                        <td><?php echo htmlspecialchars($tempat->nama_pembimbing_lapangan); ?></td>
                                        <th width="15%">Jabatan</th>
                                        <td><?php echo htmlspecialchars($tempat->jabatan_pembimbing_lapangan ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. HP</th>
                                        <td><?php echo htmlspecialchars($tempat->no_hp_pembimbing_lapangan ?: '-'); ?></td>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($tempat->email_pembimbing_lapangan ?: '-'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tempat->catatan_penolakan && $tempat->status_acc_tempat == 'ditolak'): ?>
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Catatan Penolakan</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($tempat->catatan_penolakan)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($tempat->status_acc_tempat == 'pending' && get_user_role() !== 'kaprodi'): ?>
                    <!-- Form Approve/Reject -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-check-circle"></i> Proses Pengajuan Tempat PKL</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open('Pkl_tempat_sekprodi/proses/' . $tempat->id); ?>
                                <div class="form-group">
                                    <label for="status_acc_tempat">Status <span class="text-danger">*</span></label>
                                    <select name="status_acc_tempat" id="status_acc_tempat" class="form-control" required onchange="togglePembimbing(this.value)">
                                        <option value="">Pilih Status</option>
                                        <option value="disetujui">Disetujui</option>
                                        <option value="ditolak">Ditolak</option>
                                    </select>
                                    <?php echo form_error('status_acc_tempat', '<small class="text-danger">', '</small>'); ?>
                                </div>

                                <div id="pembimbing_section" style="display: none;">
                                    <div class="form-group">
                                        <label for="id_pembimbing">Dosen Pembimbing <span class="text-danger">*</span></label>
                                        <select name="id_pembimbing" id="id_pembimbing" class="form-control">
                                            <option value="">Pilih Dosen Pembimbing</option>
                                            <?php foreach ($dosen_pembimbing as $dosen): ?>
                                                <option value="<?php echo $dosen->id; ?>" 
                                                        <?php echo ($pengajuan && $pengajuan->id_pembimbing == $dosen->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dosen->nama_dosen . ' - ' . $dosen->nidn); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php echo form_error('id_pembimbing', '<small class="text-danger">', '</small>'); ?>
                                        <small class="form-text text-muted">Pilih dosen pembimbing untuk mahasiswa ini</small>
                                    </div>
                                </div>

                                <div id="catatan_section" style="display: none;">
                                    <div class="form-group">
                                        <label for="catatan_penolakan">Catatan Penolakan</label>
                                        <textarea name="catatan_penolakan" id="catatan_penolakan" class="form-control" rows="3"></textarea>
                                        <small class="form-text text-muted">Berikan alasan penolakan (opsional)</small>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Proses
                                    </button>
                                    <a href="<?php echo site_url('Pkl_tempat_sekprodi'); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php elseif ($tempat->status_acc_tempat == 'pending' && get_user_role() === 'kaprodi'): ?>
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle"></i> Pengajuan ini menunggu keputusan Sekprodi (Kaprodi hanya dapat melihat).
                    </div>
                <?php elseif ($tempat->status_acc_tempat == 'disetujui'): ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Pengajuan Tempat PKL Telah Disetujui</h6>
                        <?php if ($pengajuan && $pengajuan->id_pembimbing): ?>
                            <?php
                            $pembimbing_nama = '';
                            foreach ($dosen_pembimbing as $dosen) {
                                if ($dosen->id == $pengajuan->id_pembimbing) {
                                    $pembimbing_nama = $dosen->nama_dosen;
                                    break;
                                }
                            }
                            ?>
                            <p class="mb-0"><strong>Dosen Pembimbing:</strong> <?php echo htmlspecialchars($pembimbing_nama); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-user-edit"></i> Edit Dosen Pembimbing</h5>
                            </div>
                            <div class="card-body">
                                <?php echo form_open('Pkl_tempat_sekprodi/update_pembimbing/' . $tempat->id); ?>
                                    <div class="form-group">
                                        <label for="id_pembimbing_edit">Dosen Pembimbing <span class="text-danger">*</span></label>
                                        <select name="id_pembimbing" id="id_pembimbing_edit" class="form-control" required>
                                            <option value="">Pilih Dosen Pembimbing</option>
                                            <?php foreach ($dosen_pembimbing as $dosen): ?>
                                                <option value="<?php echo $dosen->id; ?>" <?php echo ($pengajuan && (int)$pengajuan->id_pembimbing === (int)$dosen->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dosen->nama_dosen . ' - ' . $dosen->nidn); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Gunakan fitur ini jika diperlukan pergantian pembimbing setelah pengajuan tempat PKL disetujui.</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Ubah dosen pembimbing untuk mahasiswa ini?');">
                                        <i class="fas fa-save"></i> Simpan Perubahan Pembimbing
                                    </button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
function togglePembimbing(status) {
    const pembimbingSection = document.getElementById('pembimbing_section');
    const catatanSection = document.getElementById('catatan_section');
    const idPembimbing = document.getElementById('id_pembimbing');
    
    if (status === 'disetujui') {
        pembimbingSection.style.display = 'block';
        catatanSection.style.display = 'none';
        idPembimbing.setAttribute('required', 'required');
    } else if (status === 'ditolak') {
        pembimbingSection.style.display = 'none';
        catatanSection.style.display = 'block';
        idPembimbing.removeAttribute('required');
    } else {
        pembimbingSection.style.display = 'none';
        catatanSection.style.display = 'none';
        idPembimbing.removeAttribute('required');
    }
}

<?php if ($tempat->latitude && $tempat->longitude): ?>
// Initialize map untuk tempat PKL
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?php echo $tempat->latitude; ?>;
    const lng = <?php echo $tempat->longitude; ?>;
    
    // Initialize Leaflet map
    const map = L.map('map-tempat-pkl').setView([lat, lng], 17);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Add marker untuk tempat PKL
    const marker = L.marker([lat, lng], {
        title: 'Tempat PKL'
    }).addTo(map);
    
    // Popup dengan informasi tempat PKL
    const popupContent = `
        <div style="padding: 5px;">
            <strong><?php echo htmlspecialchars($tempat->nama_instansi); ?></strong><br>
            <small><?php echo htmlspecialchars($tempat->alamat_instansi); ?></small><br>
            <hr style="margin: 5px 0;">
            <strong>Koordinat:</strong><br>
            Lat: ${lat.toFixed(8)}<br>
            Lng: ${lng.toFixed(8)}
        </div>
    `;
    marker.bindPopup(popupContent).openPopup();
});
<?php endif; ?>
</script>

