<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Daftar Mahasiswa Bimbingan Proposal TA</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">NIM</th>
                                <th width="16%">Nama</th>
                                <th>Judul</th>
                                <th width="12%" class="text-center">Topik (ACC/Total)</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="9%" class="text-center">Nilai</th>
                                <th width="14%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada mahasiswa bimbingan Anda.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($mahasiswa_list as $p): ?>
                                    <?php
                                    $status_map = array(
                                        'terdaftar' => array('secondary', 'Terdaftar'),
                                        'sedang_proposal' => array('info', 'Bimbingan'),
                                        'layak_sidang' => array('success', 'Layak Sidang'),
                                        'selesai_proposal' => array('dark', 'Selesai'),
                                        'batal' => array('danger', 'Batal'),
                                    );
                                    $st = isset($status_map[$p->status]) ? $status_map[$p->status] : array('secondary', $p->status);
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa ?? '-'); ?></td>
                                        <td><small><?php echo htmlspecialchars($p->judul ?: '-'); ?></small></td>
                                        <td class="text-center">
                                            <span class="badge badge-success"><?php echo (int) $p->ringkas['acc']; ?></span> /
                                            <span class="badge badge-secondary"><?php echo (int) $p->ringkas['total']; ?></span>
                                            <?php if ($p->ringkas['berjalan'] > 0): ?>
                                                <br><small class="text-warning"><?php echo (int) $p->ringkas['berjalan']; ?> berjalan</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><span class="badge badge-<?php echo $st[0]; ?>"><?php echo $st[1]; ?></span></td>
                                        <td class="text-center"><?php echo $p->nilai ? format_nilai_proposal($p->nilai->nilai_rata) : '-'; ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Proposal_mahasiswa_dosen/siap_sidang/' . $p->id); ?>" class="btn btn-sm btn-<?php echo $p->nilai ? 'info' : 'success'; ?>" title="Nilai dan layak sidang">
                                                <i class="fas fa-clipboard-check"></i> <?php echo $p->nilai ? 'Ubah' : 'Nilai'; ?>
                                            </a>
                                            <?php if ($p->nilai): ?>
                                                <a href="<?php echo site_url('Proposal_mahasiswa_dosen/detail_penilaian/' . $p->id); ?>" class="btn btn-sm btn-secondary" title="Rincian nilai"><i class="fas fa-eye"></i></a>
                                            <?php endif; ?>
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
