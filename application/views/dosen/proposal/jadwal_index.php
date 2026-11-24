<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt"></i> Jadwal Sidang Proposal TA (sebagai Penguji)</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="4%">No</th><th width="15%">Tanggal</th><th width="10%">Waktu</th><th width="8%">Ruang</th>
                                <th width="16%">Mahasiswa</th><th>Judul</th><th width="9%" class="text-center">Peran</th>
                                <th width="9%" class="text-center">Penilaian</th><th width="9%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr><td colspan="9" class="text-center text-muted">Belum ada jadwal sidang yang Anda uji.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($jadwal_list as $j): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo hari_dari_tanggal($j->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($j->tanggal_ujian)); ?></td>
                                        <td><?php echo $j->jam_mulai ? date('H:i', strtotime($j->jam_mulai)) : '-'; ?>-<?php echo $j->jam_selesai ? date('H:i', strtotime($j->jam_selesai)) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($j->ruangan ?: '-'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($j->nama_mahasiswa ?? '-'); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($j->nim ?? ''); ?></small></td>
                                        <td><small><?php echo htmlspecialchars($j->judul ?: '-'); ?></small></td>
                                        <td class="text-center"><span class="badge badge-info"><?php echo $j->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span></td>
                                        <td class="text-center">
                                            <?php if ($j->penilaian): ?>
                                                <span class="badge badge-success"><?php echo number_format((float) $j->penilaian->nilai_total, 2, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_proposal_dosen/detail/' . $j->id); ?>" class="btn btn-sm btn-success" title="Input nilai sidang"><i class="fas fa-clipboard-check"></i> Nilai</a>
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
