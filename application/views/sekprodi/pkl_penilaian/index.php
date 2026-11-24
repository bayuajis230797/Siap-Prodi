<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Penilaian PKL</h3>
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

                <?php if (empty($aspek_sekprodi)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tidak ada aspek penilaian yang ditugaskan untuk Sekprodi. Silakan hubungi Admin untuk setting aspek penilaian.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-3">
                        <h6><i class="fas fa-info-circle"></i> Aspek Penilaian yang Ditugaskan untuk Sekprodi:</h6>
                        <ul class="mb-0">
                            <?php foreach ($aspek_sekprodi as $aspek): ?>
                                <li><strong><?php echo $aspek->kode_aspek; ?>:</strong> <?php echo $aspek->nama_aspek; ?></li>
                            <?php endforeach; ?>
                        </ul>
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
                                    <th width="10%" class="text-center">Nilai Akhir</th>
                                    <th width="10%" class="text-center">Grade</th>
                                    <th width="15%" class="text-center">Status Penilaian</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mahasiswa_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox"></i> Belum ada mahasiswa yang siap dinilai.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = 1;
                                    foreach ($mahasiswa_list as $pengajuan): 
                                        $nilai_akhir = isset($pengajuan->nilai_akhir) ? $pengajuan->nilai_akhir : 0;
                                        $grade = isset($pengajuan->grade) ? $pengajuan->grade : null;
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
                                                <?php if ($pengajuan->penilaian_sekprodi): ?>
                                                    <span class="badge badge-success">Sudah Dinilai</span>
                                                    <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($pengajuan->penilaian_sekprodi->tanggal_penilaian)); ?></small>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Dinilai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?php echo site_url('Pkl_penilaian_sekprodi/form/' . $pengajuan->id); ?>" class="btn btn-sm btn-primary" title="<?php echo $pengajuan->penilaian_sekprodi ? 'Update' : 'Input'; ?> Penilaian">
                                                    <i class="fas fa-<?php echo $pengajuan->penilaian_sekprodi ? 'edit' : 'plus'; ?>"></i> 
                                                    <?php echo $pengajuan->penilaian_sekprodi ? 'Update' : 'Input'; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





