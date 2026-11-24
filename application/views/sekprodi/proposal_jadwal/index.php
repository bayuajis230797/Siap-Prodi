<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Sidang Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_proposal_sekprodi/export_csv' . ($status_filter ? '?status=' . urlencode($status_filter) : '')); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Ekspor CSV
                    </a>
                </div>
            </div>
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
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Jadwal_proposal_sekprodi/index'); ?>" class="form-inline mb-3">
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <?php foreach (array('terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'batal' => 'Batal') as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $status_filter === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="per_page" class="form-control form-control-sm mr-2">
                        <?php foreach (array(10, 25, 50, 100) as $n): ?>
                            <option value="<?php echo $n; ?>" <?php echo (int) $per_page === $n ? 'selected' : ''; ?>><?php echo $n; ?> / halaman</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Jadwal_proposal_sekprodi'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="4%">No</th><th width="9%">NIM</th><th width="15%">Mahasiswa</th>
                                <th width="14%">Tanggal</th><th width="10%">Waktu</th><th width="8%">Ruang</th>
                                <th width="15%">Penguji 1</th><th width="15%">Penguji 2</th>
                                <th width="8%" class="text-center">Status</th><th width="8%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr><td colspan="10" class="text-center text-muted">Belum ada jadwal sidang.</td></tr>
                            <?php else: ?>
                                <?php $no = (max(1, (int) $this->uri->segment(3)) - 1) * $per_page + 1; foreach ($jadwal_list as $j): ?>
                                    <?php $cls = array('terjadwal' => 'info', 'selesai' => 'success', 'batal' => 'danger'); ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($j->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($j->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo hari_dari_tanggal($j->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($j->tanggal_ujian)); ?></td>
                                        <td><?php echo $j->jam_mulai ? date('H:i', strtotime($j->jam_mulai)) : '-'; ?>-<?php echo $j->jam_selesai ? date('H:i', strtotime($j->jam_selesai)) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($j->ruangan ?: '-'); ?></td>
                                        <td><?php echo !empty($j->penguji_1) ? htmlspecialchars($j->penguji_1) : '<span class="text-muted">-</span>'; ?></td>
                                        <td><?php echo !empty($j->penguji_2) ? htmlspecialchars($j->penguji_2) : '<span class="text-muted">-</span>'; ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo isset($cls[$j->status]) ? $cls[$j->status] : 'secondary'; ?>"><?php echo ucfirst($j->status); ?></span></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_proposal_sekprodi/view/' . $j->id); ?>" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                                            <?php if ($bisa_ubah): ?>
                                                <a href="<?php echo site_url('Jadwal_proposal_sekprodi/edit/' . $j->id); ?>" class="btn btn-sm btn-warning" title="Ubah"><i class="fas fa-edit"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6"><p class="text-muted">Menampilkan <?php echo count($jadwal_list); ?> dari <?php echo $total_rows; ?> data</p></div>
                        <div class="col-md-6"><?php echo $pagination; ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
