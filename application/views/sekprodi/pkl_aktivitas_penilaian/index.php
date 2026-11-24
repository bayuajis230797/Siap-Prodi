<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Penilaian PKL</h3>
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

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    Halaman ini menampilkan rinci semua aktivitas penilaian PKL dari berbagai penilai (Pembimbing Dosen, Pembimbing Lapangan, Sekprodi, dan Penguji).
                </div>

                <div class="mb-3">
                    <a href="<?php echo site_url('Pkl_aktivitas_penilaian/export_excel'); ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th>Judul PKL</th>
                                <th width="12%" class="text-center">Jumlah Penilai</th>
                                <th width="15%" class="text-center">Nilai Akhir</th>
                                <th width="10%" class="text-center">Nilai Huruf</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_penilaian)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada aktivitas penilaian PKL.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                foreach ($mahasiswa_penilaian as $item): 
                                    $pengajuan = $item['pengajuan'];
                                    $penilaian_list = $item['penilaian_list'];
                                    $nilai_akhir = isset($item['nilai_akhir']) ? $item['nilai_akhir'] : 0;
                                    $grade = isset($item['grade']) ? $item['grade'] : null;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $pengajuan->nim; ?></strong></td>
                                        <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $pengajuan->kelas; ?></span>
                                        </td>
                                        <td><?php echo $pengajuan->judul_pkl ?: '-'; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary"><?php echo count($penilaian_list); ?> Penilai</span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nilai_akhir > 0): ?>
                                                <strong><?php echo number_format($nilai_akhir, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($grade): ?>
                                                <?php
                                                $grade_class = 'secondary';
                                                switch (strtoupper($grade)) {
                                                    case 'A':
                                                        $grade_class = 'success';
                                                        break;
                                                    case 'B':
                                                        $grade_class = 'info';
                                                        break;
                                                    case 'C':
                                                        $grade_class = 'warning';
                                                        break;
                                                    case 'D':
                                                    case 'E':
                                                        $grade_class = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $grade_class; ?> badge-lg"><?php echo htmlspecialchars($grade); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_aktivitas_penilaian/detail/' . $pengajuan->id); ?>" class="btn btn-sm btn-info" title="Detail">
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





