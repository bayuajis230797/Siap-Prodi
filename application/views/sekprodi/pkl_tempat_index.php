<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Tempat PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_tempat_sekprodi/export_excel'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Pkl_tempat_sekprodi/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="disetujui" <?php echo ($filter_status == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo ($filter_status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($filter_status): ?>
                                        <a href="<?php echo site_url('Pkl_tempat_sekprodi'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Pkl_tempat_sekprodi/index'); ?>" id="formPerPage" class="form-inline justify-content-end">
                            <?php if ($filter_status): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                            <?php endif; ?>
                            <label for="per_page_select" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_select" class="form-control form-control-sm" onchange="document.getElementById('formPerPage').submit();">
                                <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo ($per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo ($per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="10%" class="text-center">Kelas</th>
                                <th>Nama Instansi</th>
                                <th width="8%" class="text-center">Koordinat</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tempat_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pengajuan tempat PKL.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                $start_no = ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0) * $per_page + 1;
                                foreach ($tempat_list as $tempat): 
                                ?>
                                    <tr>
                                        <td><?php echo $start_no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($tempat->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($tempat->nama_mahasiswa); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($tempat->kelas ?: '-'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($tempat->nama_instansi ?: '-'); ?></td>
                                        <td class="text-center">
                                            <?php if ($tempat->latitude && $tempat->longitude): ?>
                                                <span class="badge badge-success" title="Koordinat: <?php echo number_format($tempat->latitude, 6); ?>, <?php echo number_format($tempat->longitude, 6); ?>">
                                                    <i class="fas fa-map-marker-alt"></i> Ada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary" title="Koordinat belum diisi">
                                                    <i class="fas fa-times"></i> Tidak Ada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst($tempat->status_acc_tempat ?: 'Pending');
                                            switch ($tempat->status_acc_tempat) {
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'pending':
                                                default:
                                                    $status_class = 'warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_tempat_sekprodi/detail/' . $tempat->id); ?>" 
                                               class="btn btn-info btn-sm" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <?php if ($tempat->latitude && $tempat->longitude): ?>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        data-toggle="modal" 
                                                        data-target="#modalPeta<?php echo $tempat->id; ?>"
                                                        title="Lihat Peta">
                                                    <i class="fas fa-map"></i> Peta
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Total: <?php echo count($tempat_list); ?> pengajuan
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Peta untuk setiap tempat PKL -->
<?php if (!empty($tempat_list)): ?>
    <?php foreach ($tempat_list as $tempat): ?>
        <?php if ($tempat->latitude && $tempat->longitude): ?>
            <div class="modal fade" id="modalPeta<?php echo $tempat->id; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-map"></i> Peta Lokasi - <?php echo htmlspecialchars($tempat->nama_instansi); ?>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($tempat->nama_mahasiswa); ?> (<?php echo htmlspecialchars($tempat->nim); ?>)</p>
                            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($tempat->alamat_instansi); ?></p>
                            <p><strong>Koordinat:</strong> 
                                <span class="badge badge-info">
                                    <?php echo number_format($tempat->latitude, 8); ?>, <?php echo number_format($tempat->longitude, 8); ?>
                                </span>
                            </p>
                            <div id="map-modal-<?php echo $tempat->id; ?>" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <a href="<?php echo site_url('Pkl_tempat_sekprodi/detail/' . $tempat->id); ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Lihat Detail Lengkap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
// Initialize map untuk setiap modal
<?php if (!empty($tempat_list)): ?>
    <?php foreach ($tempat_list as $tempat): ?>
        <?php if ($tempat->latitude && $tempat->longitude): ?>
            $('#modalPeta<?php echo $tempat->id; ?>').on('shown.bs.modal', function() {
                // Cek apakah map sudah diinisialisasi
                if (typeof window.mapModal<?php echo $tempat->id; ?> === 'undefined') {
                    const lat = <?php echo $tempat->latitude; ?>;
                    const lng = <?php echo $tempat->longitude; ?>;
                    
                    // Initialize Leaflet map
                    window.mapModal<?php echo $tempat->id; ?> = L.map('map-modal-<?php echo $tempat->id; ?>').setView([lat, lng], 17);

                    // Add OpenStreetMap tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(window.mapModal<?php echo $tempat->id; ?>);

                    // Add marker untuk tempat PKL
                    const marker = L.marker([lat, lng], {
                        title: '<?php echo htmlspecialchars($tempat->nama_instansi, ENT_QUOTES); ?>'
                    }).addTo(window.mapModal<?php echo $tempat->id; ?>);
                    
                    // Popup dengan informasi tempat PKL
                    const popupContent = `
                        <div style="padding: 5px;">
                            <strong><?php echo htmlspecialchars($tempat->nama_instansi, ENT_QUOTES); ?></strong><br>
                            <small><?php echo htmlspecialchars($tempat->alamat_instansi, ENT_QUOTES); ?></small><br>
                            <hr style="margin: 5px 0;">
                            <strong>Koordinat:</strong><br>
                            Lat: ${lat.toFixed(8)}<br>
                            Lng: ${lng.toFixed(8)}
                        </div>
                    `;
                    marker.bindPopup(popupContent).openPopup();
                } else {
                    // Invalidate size jika map sudah ada
                    window.mapModal<?php echo $tempat->id; ?>.invalidateSize();
                }
            });
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</script>

