<?php $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', '.'); }; ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Aktivitas Penilaian Proposal TA</h3>
                <div class="card-tools"><a href="<?php echo site_url('Proposal_aktivitas_penilaian/export_csv?' . http_build_query(array_filter(array('kelas' => $kelas_filter, 'tahap' => $tahap_filter)))); ?>" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Ekspor CSV</a></div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>

                <form method="get" action="<?php echo site_url('Proposal_aktivitas_penilaian'); ?>" class="form-inline mb-3">
                    <select name="kelas" class="form-control form-control-sm mr-2"><option value="">Semua Kelas</option>
                        <?php foreach ($kelas_list as $k): ?><option value="<?php echo htmlspecialchars($k); ?>" <?php echo $kelas_filter === $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option><?php endforeach; ?></select>
                    <select name="tahap" class="form-control form-control-sm mr-2"><option value="">Semua Tahap</option>
                        <?php foreach ($tahap_opsi as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $tahap_filter === $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($v); ?></option><?php endforeach; ?></select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Proposal_aktivitas_penilaian'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Kelas</th><th>Pembimbing</th><th>Tahap Saat Ini</th>
                            <th class="text-center">Topik ACC</th><th class="text-center">Bimbingan</th><th class="text-center">Penilai</th><th class="text-center">Layak Klaim</th><th class="text-center">Akhir</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            <?php if (empty($list)): ?><tr><td colspan="12" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: $no = 1; foreach ($list as $r): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td><td><strong><?php echo htmlspecialchars($r->nim ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r->nama_mahasiswa ?? '-'); ?></td><td><?php echo htmlspecialchars($r->kelas ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars((string) $r->pembimbing); ?></td>
                                    <td><span class="badge badge-<?php echo $r->tahap_kode === 'selesai' ? 'success' : ($r->tahap_kode === 'belum_judul' ? 'secondary' : 'info'); ?>"><?php echo htmlspecialchars($r->tahap_label); ?></span></td>
                                    <td class="text-center"><?php echo (int) $r->topik_acc; ?>/<?php echo (int) $r->topik_total; ?></td>
                                    <td class="text-center"><?php echo $f($r->nilai_bimbingan); ?></td>
                                    <td class="text-center"><?php echo (int) $r->jumlah_nilai_penguji; ?>/2</td>
                                    <td class="text-center"><?php echo (int) $r->jumlah_layak_klaim; ?>/2</td>
                                    <td class="text-center"><strong><?php echo $f($r->nilai_proposal_final); ?></strong> <?php echo htmlspecialchars((string) $r->nilai_huruf); ?></td>
                                    <td class="text-center"><a href="<?php echo site_url('Proposal_aktivitas_penilaian/detail/' . $r->id); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
