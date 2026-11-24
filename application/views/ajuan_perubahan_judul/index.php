<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-edit"></i> Ajuan Perubahan Judul
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada ajuan perubahan judul yang perlu direview.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th>Judul Lama</th>
                                    <th>Judul Revisi</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($pengajuan_list as $pengajuan): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                                        <?php
                                        // Tentukan judul lama dan judul baru
                                        $status_approve = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
                                        
                                        // Jika sudah di-ACC, ambil judul lama dari catatan_sekprodi
                                        $judul_lama = '';
                                        $judul_baru = '';
                                        
                                        if ($status_approve == 'disetujui') {
                                            // Jika sudah di-ACC, judul lama ada di catatan_sekprodi
                                            if (!empty($pengajuan->catatan_sekprodi) && strpos($pengajuan->catatan_sekprodi, 'JUDUL_LAMA_SEBELUM_REVISI:') !== false) {
                                                $lines = explode("\n", $pengajuan->catatan_sekprodi);
                                                foreach ($lines as $line) {
                                                    if (strpos($line, 'JUDUL_LAMA_SEBELUM_REVISI:') !== false) {
                                                        $judul_lama = trim(str_replace('JUDUL_LAMA_SEBELUM_REVISI:', '', $line));
                                                        break;
                                                    }
                                                }
                                            }
                                            // Jika tidak ada di catatan, gunakan judul_pengajuan sebagai fallback
                                            if (empty($judul_lama)) {
                                                $judul_lama = $pengajuan->judul_pengajuan;
                                            }
                                            // Judul baru adalah judul_disetujui yang sudah diupdate
                                            $judul_baru = $pengajuan->judul_disetujui;
                                        } else {
                                            // Jika belum di-ACC, judul lama adalah judul_disetujui, judul baru adalah judul_revisi
                                            $judul_lama = $pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan;
                                            $judul_baru = $pengajuan->judul_revisi ?? '-';
                                        }
                                        ?>
                                        <td><?php echo htmlspecialchars($judul_lama); ?></td>
                                        <td><?php echo htmlspecialchars($judul_baru); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = 'warning';
                                            $status_text = 'Pending';
                                            
                                            switch ($status_approve) {
                                                case 'disetujui':
                                                    $badge_class = 'success';
                                                    $status_text = 'Acc Judul';
                                                    break;
                                                case 'ditolak':
                                                    $badge_class = 'danger';
                                                    $status_text = 'Ditolak';
                                                    break;
                                                default:
                                                    $badge_class = 'warning';
                                                    $status_text = 'Pending';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Ajuan_perubahan_judul/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-sm btn-info" title="Review">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

