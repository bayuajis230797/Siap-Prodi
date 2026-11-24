<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Validasi Perubahan Judul Proposal TA</h3>
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
                            <?php if (empty($perubahan_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada permintaan perubahan judul Proposal TA.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($perubahan_list as $perubahan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($perubahan->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($perubahan->nama_mahasiswa ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($perubahan->kelas ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($perubahan->judul_lama ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($perubahan->judul_baru ?? '-'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'warning';
                                            $status_text = 'Pending';
                                            $status_value = $perubahan->status_approve_sekprodi ?? 'pending';
                                            if ($status_value == 'acc' || $status_value == 'disetujui') {
                                                $status_class = 'success';
                                                $status_text = 'Disetujui';
                                            } elseif ($status_value == 'ditolak') {
                                                $status_class = 'danger';
                                                $status_text = 'Ditolak';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Proposal_perubahan_judul_sekprodi/detail/' . $perubahan->id); ?>" 
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

