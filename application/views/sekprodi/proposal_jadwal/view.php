<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Detail Jadwal Sidang Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_proposal_sekprodi'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <?php if ($bisa_ubah): ?>
                        <a href="<?php echo site_url('Jadwal_proposal_sekprodi/edit/' . $jadwal->id); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Ubah</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <table class="table table-sm table-bordered">
                    <tr><th width="30%">Mahasiswa</th><td><strong><?php echo htmlspecialchars($jadwal->nama_mahasiswa ?? '-'); ?></strong> (<?php echo htmlspecialchars($jadwal->nim ?? '-'); ?>)</td></tr>
                    <tr><th>Kelas</th><td><?php echo htmlspecialchars($jadwal->kelas ?? '-'); ?></td></tr>
                    <tr><th>Judul</th><td><?php echo htmlspecialchars($judul ?: '-'); ?></td></tr>
                    <tr><th>Tanggal</th><td><?php echo hari_dari_tanggal($jadwal->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></td></tr>
                    <tr><th>Waktu</th><td><?php echo $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-'; ?> - <?php echo $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-'; ?></td></tr>
                    <tr><th>Ruangan</th><td><?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></td></tr>
                    <tr><th>Penguji 1</th><td><?php echo !empty($jadwal->penguji_1) ? htmlspecialchars($jadwal->penguji_1 . ' (' . $jadwal->penguji_1_nidn . ')') : '<span class="text-muted">Belum ditetapkan</span>'; ?></td></tr>
                    <tr><th>Penguji 2</th><td><?php echo !empty($jadwal->penguji_2) ? htmlspecialchars($jadwal->penguji_2 . ' (' . $jadwal->penguji_2_nidn . ')') : '<span class="text-muted">Belum ditetapkan</span>'; ?></td></tr>
                    <tr><th>Status</th><td><?php echo ucfirst($jadwal->status); ?></td></tr>
                    <?php if (!empty($jadwal->catatan)): ?><tr><th>Catatan</th><td><?php echo nl2br(htmlspecialchars($jadwal->catatan)); ?></td></tr><?php endif; ?>
                </table>

                <h5 class="mt-3">Penilaian Sidang</h5>
                <?php if (empty($nilai_list)): ?>
                    <p class="text-muted">Belum ada penilaian.</p>
                <?php else: ?>
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Penguji</th><th width="20%" class="text-center">Peran</th><th width="20%" class="text-center">Nilai</th></tr></thead>
                        <tbody>
                            <?php foreach ($nilai_list as $n): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($n->penguji_nama ?? '-'); ?></td>
                                    <td class="text-center"><?php echo $n->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></td>
                                    <td class="text-center"><?php echo number_format((float) $n->nilai_total, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
