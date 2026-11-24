<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Daftar Mahasiswa Bimbingan PKL
                </h3>
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
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    Berikut adalah daftar mahasiswa bimbingan PKL Anda. Klik tombol <strong>"Layak Sidang"</strong> untuk memberikan penilaian dan menyatakan mahasiswa layak sidang.
                </div>

                <?php if (empty($mahasiswa_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Belum ada mahasiswa bimbingan PKL yang judulnya sudah disetujui.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th>Judul PKL</th>
                                    <th width="10%" class="text-center">Nilai Akhir</th>
                                    <th width="10%" class="text-center">Grade</th>
                                    <th width="12%" class="text-center">Status Layak Sidang</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($mahasiswa_list as $mhs): 
                                    $is_layak = isset($mhs->is_layak_sidang) && $mhs->is_layak_sidang == 1;
                                    $nilai_akhir = isset($mhs->nilai_akhir) ? $mhs->nilai_akhir : 0;
                                    $grade = isset($mhs->grade) ? $mhs->grade : null;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mhs->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->kelas); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mhs->judul_pkl); ?></strong>
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
                                            <?php if ($is_layak): ?>
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle"></i> LAYAK SIDANG
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning badge-lg">
                                                    <i class="fas fa-clock"></i> BELUM LAYAK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo site_url('Pkl_mahasiswa_dosen/layak_sidang/' . $mhs->id); ?>" 
                                                   class="btn btn-sm <?php echo $is_layak ? 'btn-info' : 'btn-success'; ?>" 
                                                   title="<?php echo $is_layak ? 'Edit Penilaian' : 'Layak Sidang & Input Nilai'; ?>">
                                                    <i class="fas fa-<?php echo $is_layak ? 'edit' : 'check'; ?>"></i> 
                                                    <?php echo $is_layak ? 'Edit Nilai' : 'Layak Sidang'; ?>
                                                </a>
                                                <?php if ($is_layak): ?>
                                                    <a href="<?php echo site_url('Pkl_mahasiswa_dosen/detail_penilaian/' . $mhs->id); ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Detail Penilaian">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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





