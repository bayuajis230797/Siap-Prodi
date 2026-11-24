<div class="row">
    <div class="col-12">
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ajuan Judul Skripsi</h3>
            </div>
            <div class="card-body">
                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada pengajuan judul skripsi yang perlu direview.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="15%">Kelas</th>
                                    <th width="25%">Judul Pengajuan</th>
                                    <th width="10%">Peran</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pengajuan_list as $pengajuan): ?>
                                    <?php 
                                    $id_dosen = $this->session->userdata('ref_id');
                                    $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_dosen);
                                    $peran = $is_pembimbing_1 ? 'Pembimbing 1' : 'Pembimbing 2';
                                    $status = $is_pembimbing_1 ? $pengajuan->status_acc_judul_p1 : $pengajuan->status_acc_judul_p2;
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $pengajuan->nim; ?></td>
                                        <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                                        <td><?php echo $pengajuan->kelas; ?></td>
                                        <td>
                                            <strong><?php echo $pengajuan->judul_pengajuan; ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $peran; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <a href="<?php echo site_url('Bimbingan_skripsi/review_judul/' . $pengajuan->id); ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Review
                                                </a>
                                            <?php elseif ($status === 'acc'): ?>
                                                <span class="badge badge-success">Sudah ACC</span>
                                                <?php if ($is_pembimbing_1): ?>
                                                    <br>
                                                    <a href="<?php echo site_url('Bimbingan_skripsi/review_judul/' . $pengajuan->id); ?>" class="btn btn-sm btn-info mt-1">
                                                        <i class="fas fa-edit"></i> Ubah Judul
                                                    </a>
                                                <?php endif; ?>
                                            <?php elseif ($status === 'revisi'): ?>
                                                <span class="badge badge-warning">Revisi</span>
                                                <br>
                                                <a href="<?php echo site_url('Bimbingan_skripsi/review_judul/' . $pengajuan->id); ?>" class="btn btn-sm btn-info mt-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            <?php endif; ?>
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

