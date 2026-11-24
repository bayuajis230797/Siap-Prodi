<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-check"></i> Monitoring Penguji Sidang Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php endif; ?>
                <?php if ($jumlah_bentrok > 0): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Ada <strong><?php echo (int) $jumlah_bentrok; ?></strong> penugasan penguji yang bentrok jam. Ubah lewat menu Jadwal Sidang.</div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Monitoring_penguji_sidang_proposal'); ?>" class="form-inline mb-3">
                    <select name="dosen" class="form-control form-control-sm mr-2">
                        <option value="">Semua Dosen</option>
                        <?php foreach ($dosen_list as $d): ?><option value="<?php echo $d->id; ?>" <?php echo ((int) $filter_dosen === (int) $d->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d->nama_dosen); ?></option><?php endforeach; ?>
                    </select>
                    <input type="date" name="tanggal_mulai" class="form-control form-control-sm mr-2" value="<?php echo htmlspecialchars((string) $filter_tanggal_mulai); ?>">
                    <input type="date" name="tanggal_selesai" class="form-control form-control-sm mr-2" value="<?php echo htmlspecialchars((string) $filter_tanggal_selesai); ?>">
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Monitoring_penguji_sidang_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <h5>Ringkasan per Dosen</h5>
                <table class="table table-sm table-bordered mb-4">
                    <thead><tr><th>Dosen</th><th width="14%" class="text-center">Penguji 1</th><th width="14%" class="text-center">Penguji 2</th><th width="12%" class="text-center">Total</th><th width="12%" class="text-center">Bentrok</th></tr></thead>
                    <tbody>
                        <?php if (empty($ringkas)): ?><tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                        <?php else: foreach ($ringkas as $r): ?>
                            <tr><td><strong><?php echo htmlspecialchars($r['nama']); ?></strong> <small class="text-muted"><?php echo htmlspecialchars((string) $r['nidn']); ?></small></td>
                                <td class="text-center"><?php echo (int) $r['penguji_1']; ?></td><td class="text-center"><?php echo (int) $r['penguji_2']; ?></td>
                                <td class="text-center"><strong><?php echo (int) $r['total']; ?></strong></td>
                                <td class="text-center"><?php echo $r['bentrok'] ? '<span class="badge badge-danger">' . (int) $r['bentrok'] . '</span>' : '<span class="badge badge-success">0</span>'; ?></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <h5>Rincian Jadwal</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead><tr><th width="4%">No</th><th>Dosen</th><th width="10%">Peran</th><th width="16%">Tanggal</th><th width="12%">Waktu</th><th width="8%">Ruang</th><th>Mahasiswa</th><th width="9%" class="text-center">Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($baris)): ?><tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: $no = 1; foreach ($baris as $b): ?>
                                <tr class="<?php echo $b['bentrok'] ? 'table-danger' : ''; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($b['dosen']); ?></td><td><?php echo $b['peran']; ?></td>
                                    <td><?php echo htmlspecialchars($b['hari_tanggal']); ?></td>
                                    <td><?php echo $b['jam_mulai'] ? date('H:i', strtotime($b['jam_mulai'])) : '-'; ?>-<?php echo $b['jam_selesai'] ? date('H:i', strtotime($b['jam_selesai'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars((string) $b['ruangan']); ?></td><td><?php echo htmlspecialchars($b['mahasiswa']); ?></td>
                                    <td class="text-center"><?php echo $b['bentrok'] ? '<span class="badge badge-danger">Bentrok</span>' : ucfirst($b['status']); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
