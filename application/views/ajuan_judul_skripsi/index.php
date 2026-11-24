<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Ajuan Judul Skripsi
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
                        Tidak ada ajuan judul skripsi yang perlu direview.
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
                                    <th>Judul Pengajuan</th>
                                    <th width="10%" class="text-center">Status ACC</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($pengajuan_list as $pengajuan): 
                                    // Tentukan status ACC berdasarkan pembimbing
                                    $is_p1 = ($pengajuan->id_pembimbing_1_final == $dosen_id);
                                    $status_acc = $is_p1 ? ($pengajuan->status_acc_judul_p1 ?? 'pending') : ($pengajuan->status_acc_judul_p2 ?? 'pending');
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = 'Pending';
                                            switch ($status_acc) {
                                                case 'acc':
                                                    $status_class = 'success';
                                                    $status_text = 'ACC';
                                                    break;
                                                case 'revisi':
                                                    $status_class = 'warning';
                                                    $status_text = 'Revisi';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Ajuan_judul_skripsi/detail/' . $pengajuan->id); ?>" 
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

