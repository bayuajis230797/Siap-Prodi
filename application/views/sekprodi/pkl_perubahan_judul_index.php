<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Perubahan Judul PKL</h3>
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="10%" class="text-center">Kelas</th>
                                <th>Judul Lama</th>
                                <th>Judul Baru</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengajuan_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada permintaan perubahan judul PKL.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($pengajuan_list as $pengajuan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim ?: '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?: '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?: '-'); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $st_rev = isset($pengajuan->status_approve_revisi) ? (string) $pengajuan->status_approve_revisi : '';
                                            // Setelah disetujui, judul_pkl di DB sudah diganti ke judul baru; kolom revisi dikosongkan — jangan tampilkan judul_pkl sebagai "lama".
                                            if ($st_rev === 'disetujui' && trim((string) ($pengajuan->judul_revisi ?? '')) === '') {
                                                echo '<span class="text-muted">—</span>';
                                            } else {
                                                echo '<small class="text-muted">' . htmlspecialchars($pengajuan->judul_pkl ?: '-') . '</small>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $judul_revisi_teks = isset($pengajuan->judul_revisi) ? trim((string) $pengajuan->judul_revisi) : '';
                                            if ($judul_revisi_teks !== '') {
                                                echo '<strong>' . htmlspecialchars($judul_revisi_teks) . '</strong>';
                                            } elseif ($st_rev === 'disetujui' && !empty($pengajuan->judul_pkl)) {
                                                echo '<strong>' . htmlspecialchars($pengajuan->judul_pkl) . '</strong><br><small class="text-success">Judul berlaku (disahkan)</small>';
                                            } elseif (!empty($pengajuan->file_approve_revisi)) {
                                                $nama_berkas = basename(str_replace('\\', '/', (string) $pengajuan->file_approve_revisi));
                                                echo '<span class="text-muted"><i class="fas fa-file-pdf text-danger"></i> Judul tercantum di berkas: <strong>'
                                                    . htmlspecialchars($nama_berkas) . '</strong><br><small>Buka menu Detail untuk membaca / mengunduh PDF.</small></span>';
                                            } else {
                                                echo '<span class="text-muted">—</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'warning';
                                            $status_text = 'Pending';
                                            if ($pengajuan->status_approve_revisi == 'disetujui') {
                                                $status_class = 'success';
                                                $status_text = 'Disetujui';
                                            } elseif ($pengajuan->status_approve_revisi == 'ditolak') {
                                                $status_class = 'danger';
                                                $status_text = 'Ditolak';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_perubahan_judul_sekprodi/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-info btn-sm" 
                                               title="Lihat Detail">
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





