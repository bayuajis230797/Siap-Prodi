<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Berkas Ajuan Sidang Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_proposal_dosen'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered">
                    <tr><th width="25%">Mahasiswa</th><td><strong><?php echo htmlspecialchars($jadwal->nama_mahasiswa ?? '-'); ?></strong> (<?php echo htmlspecialchars($jadwal->nim ?? '-'); ?>)</td></tr>
                    <tr><th>Judul</th><td><?php echo htmlspecialchars($judul ?: '-'); ?></td></tr>
                    <tr><th>Jadwal</th><td><?php echo hari_dari_tanggal($jadwal->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?>
                        pukul <?php echo $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-'; ?>-<?php echo $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-'; ?>
                        di <?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></td></tr>
                    <tr><th>Peran Anda</th><td><span class="badge badge-info"><?php echo $peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span></td></tr>
                </table>

                <h5 class="mt-3"><i class="fas fa-file-alt"></i> Berkas Pendaftaran Sidang</h5>
                <?php if (empty($berkas_uploaded)): ?>
                    <p class="text-muted">Belum ada berkas.</p>
                <?php else: ?>
                    <?php $this->load->view('proposal_partials/berkas_table', array('berkas_uploaded' => $berkas_uploaded)); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
