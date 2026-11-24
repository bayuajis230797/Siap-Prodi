<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Rincian Honor: <?php echo htmlspecialchars($dosen->nama_dosen); ?></h3>
                <div class="card-tools"><a href="<?php echo site_url('Honor_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body">
                <h5>Mahasiswa Bimbingan (<?php echo count($bimbingan); ?>)</h5>
                <table class="table table-sm table-bordered mb-4">
                    <thead><tr><th width="5%">No</th><th width="12%">NIM</th><th width="22%">Nama</th><th>Judul</th><th width="14%">Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($bimbingan)): ?><tr><td colspan="5" class="text-center text-muted">Tidak ada.</td></tr>
                        <?php else: $no = 1; foreach ($bimbingan as $b): ?>
                            <tr><td><?php echo $no++; ?></td><td><?php echo htmlspecialchars($b->nim ?? '-'); ?></td><td><?php echo htmlspecialchars($b->nama_mahasiswa ?? '-'); ?></td>
                                <td><small><?php echo htmlspecialchars((string) $b->judul_proposal); ?></small></td><td><?php echo htmlspecialchars(str_replace('_', ' ', $b->status)); ?></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <h5>Sidang sebagai Penguji (<?php echo count($sidang); ?>)</h5>
                <table class="table table-sm table-bordered">
                    <thead><tr><th width="5%">No</th><th width="16%">Tanggal</th><th width="10%">Waktu</th><th width="8%">Ruang</th><th>Mahasiswa</th><th width="10%">Peran</th><th width="10%">Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($sidang)): ?><tr><td colspan="7" class="text-center text-muted">Tidak ada.</td></tr>
                        <?php else: $no = 1; foreach ($sidang as $s): ?>
                            <tr><td><?php echo $no++; ?></td><td><?php echo hari_dari_tanggal($s->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($s->tanggal_ujian)); ?></td>
                                <td><?php echo $s->jam_mulai ? date('H:i', strtotime($s->jam_mulai)) : '-'; ?></td><td><?php echo htmlspecialchars((string) $s->ruangan); ?></td>
                                <td><?php echo htmlspecialchars(($s->nama_mahasiswa ?? '-') . ' (' . ($s->nim ?? '-') . ')'); ?></td><td><?php echo $s->peran; ?></td><td><?php echo ucfirst($s->status); ?></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
