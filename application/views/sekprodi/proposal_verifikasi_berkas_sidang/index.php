<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Verifikasi Berkas Sidang Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3"><i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik Aktif:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Proposal_verifikasi_berkas_sidang/index'); ?>" class="form-inline mb-3">
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <?php foreach (array('menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai') as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $status_filter === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="kelas" class="form-control form-control-sm mr-2">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?php echo htmlspecialchars($k); ?>" <?php echo $kelas_filter === $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="per_page" class="form-control form-control-sm mr-2">
                        <?php foreach (array(10, 25, 50, 100) as $n): ?>
                            <option value="<?php echo $n; ?>" <?php echo (int) $per_page === $n ? 'selected' : ''; ?>><?php echo $n; ?> / halaman</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Proposal_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="4%">No</th><th width="10%">NIM</th><th width="18%">Nama</th><th width="7%">Kelas</th>
                                <th>Judul</th><th width="11%" class="text-center">Status</th>
                                <th width="12%">Dikirim</th><th width="8%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftaran_list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada pendaftaran.</td></tr>
                            <?php else: ?>
                                <?php $no = (max(1, (int) $this->uri->segment(3)) - 1) * $per_page + 1; foreach ($pendaftaran_list as $p): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($p->kelas ?? '-'); ?></td>
                                        <td><small><?php echo htmlspecialchars($p->judul ?: '-'); ?></small></td>
                                        <td class="text-center"><?php echo proposal_status_badge($p->status); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($p->updated_at ?: $p->created_at)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Proposal_verifikasi_berkas_sidang/detail/' . $p->id); ?>" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6"><p class="text-muted">Menampilkan <?php echo count($pendaftaran_list); ?> dari <?php echo $total_rows; ?> data</p></div>
                        <div class="col-md-6"><?php echo $pagination; ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
