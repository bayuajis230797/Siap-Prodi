<?php $this->load->helper('app'); $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', '.'); }; ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Laporan Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_laporan/export_rekap_nilai' . ($kelas_filter ? '?kelas=' . urlencode($kelas_filter) : '')); ?>" class="btn btn-warning btn-sm"><i class="fas fa-table"></i> Rekap Butir Nilai (CSV)</a>
                    <a href="<?php echo site_url('Proposal_laporan/export_csv?' . http_build_query(array_filter(array('kelas' => $kelas_filter, 'status' => $status_filter)))); ?>" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Laporan (CSV)</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?>.
                    Nilai <em>Sementara</em> dihitung dari penilaian yang sudah masuk; <em>Final</em> setelah klaim disetujui sekprodi.</div>

                <div class="row mb-3 text-center">
                    <div class="col-md-3"><div class="small-box bg-info mb-0"><div class="inner"><h3><?php echo (int) $ringkas['total']; ?></h3><p>Mahasiswa</p></div></div></div>
                    <div class="col-md-3"><div class="small-box bg-warning mb-0"><div class="inner"><h3><?php echo (int) $ringkas['layak']; ?></h3><p>Layak Sidang</p></div></div></div>
                    <div class="col-md-3"><div class="small-box bg-success mb-0"><div class="inner"><h3><?php echo (int) $ringkas['selesai']; ?></h3><p>Selesai</p></div></div></div>
                    <div class="col-md-3"><div class="small-box bg-primary mb-0"><div class="inner"><h3><?php echo $ringkas['rata'] !== null ? number_format($ringkas['rata'], 2, ',', '.') : '-'; ?></h3><p>Rata-rata Nilai Final</p></div></div></div>
                </div>
                <?php if (!empty($ringkas['grade'])): ?>
                    <p class="mb-3"><strong>Sebaran grade (final):</strong>
                        <?php foreach ($ringkas['grade'] as $g => $jml): ?><span class="badge badge-secondary mr-1"><?php echo htmlspecialchars($g); ?>: <?php echo (int) $jml; ?></span><?php endforeach; ?></p>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Proposal_laporan'); ?>" class="form-inline mb-3">
                    <select name="kelas" class="form-control form-control-sm mr-2">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelas_list as $k): ?><option value="<?php echo htmlspecialchars($k); ?>" <?php echo $kelas_filter === $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option><?php endforeach; ?>
                    </select>
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <?php foreach (array('terdaftar' => 'Terdaftar', 'sedang_proposal' => 'Bimbingan', 'layak_sidang' => 'Layak Sidang', 'selesai_proposal' => 'Selesai', 'batal' => 'Batal') as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $status_filter === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Proposal_laporan'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr><th>No</th><th>NIM</th><th>Nama</th><th>Kelas</th><th>Judul</th><th>Pembimbing</th><th>Status</th><th>Sidang</th><th>Penguji 1</th><th>Penguji 2</th>
                                <th class="text-center">Bimbingan</th><th class="text-center">Sidang</th><th class="text-center">Akhir</th><th class="text-center">Grade</th><th class="text-center">Nilai</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan_list)): ?>
                                <tr><td colspan="15" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: $no = 1; foreach ($laporan_list as $r): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($r->nim ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r->nama_mahasiswa ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($r->kelas ?? '-'); ?></td>
                                    <td><small><?php echo htmlspecialchars((string) $r->judul_proposal); ?></small></td>
                                    <td><?php echo htmlspecialchars((string) $r->pembimbing); ?></td>
                                    <td><?php echo htmlspecialchars(str_replace('_', ' ', $r->status)); ?></td>
                                    <td><?php echo $r->tanggal_ujian ? date('d/m/Y', strtotime($r->tanggal_ujian)) . '<br><small class="text-muted">' . htmlspecialchars((string) $r->jam . ' ' . $r->ruangan) . '</small>' : '-'; ?></td>
                                    <td><?php echo htmlspecialchars((string) $r->penguji_1); ?></td>
                                    <td><?php echo htmlspecialchars((string) $r->penguji_2); ?></td>
                                    <td class="text-center"><?php echo $f($r->nilai_bimbingan); ?></td>
                                    <td class="text-center"><?php echo $f($r->nilai_sidang); ?></td>
                                    <td class="text-center"><strong><?php echo $f($r->nilai_proposal_final); ?></strong></td>
                                    <td class="text-center"><?php echo htmlspecialchars((string) $r->nilai_huruf ?: '-'); ?></td>
                                    <td class="text-center"><span class="badge badge-<?php echo $r->status_nilai === 'Final' ? 'success' : ($r->status_nilai === 'Sementara' ? 'warning' : 'secondary'); ?>"><?php echo $r->status_nilai; ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
