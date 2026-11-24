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
                <h3 class="card-title">Approve Perubahan Judul Skripsi</h3>
            </div>
            <div class="card-body">
                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada pengajuan perubahan judul yang perlu di-approve.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="10%">Kelas</th>
                                    <th width="20%">Judul Awal</th>
                                    <th width="20%">Judul Revisi / File Persetujuan</th>
                                    <th width="10%">Status ACC Pembimbing</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pengajuan_list as $pengajuan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $pengajuan->nim; ?></td>
                                        <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                                        <td><?php echo $pengajuan->kelas; ?></td>
                                        <td>
                                            <small><?php echo $pengajuan->judul_pengajuan; ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($pengajuan->judul_revisi)): ?>
                                                <strong class="text-primary">Judul Revisi:</strong><br>
                                                <small><?php echo $pengajuan->judul_revisi; ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                                                <?php if (!empty($pengajuan->judul_revisi)): ?><br><?php endif; ?>
                                                <strong class="text-info">File Persetujuan:</strong><br>
                                                <a href="<?php echo site_url('Skripsi/download_persetujuan_judul/' . $pengajuan->id); ?>" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
                                            $status_p2 = $pengajuan->status_acc_judul_p2 ?? 'pending';
                                            ?>
                                            <small>
                                                P1: <span class="badge badge-<?php echo $status_p1 === 'revisi' ? 'danger' : ($status_p1 === 'acc' ? 'success' : 'warning'); ?>">
                                                    <?php echo strtoupper($status_p1); ?>
                                                </span><br>
                                                P2: <span class="badge badge-<?php echo $status_p2 === 'revisi' ? 'danger' : ($status_p2 === 'acc' ? 'success' : 'warning'); ?>">
                                                    <?php echo strtoupper($status_p2); ?>
                                                </span>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Skripsi/review_revisi_judul/' . $pengajuan->id); ?>" class="btn btn-sm btn-primary">
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

