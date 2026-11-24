<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Pengawas UTS</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Tanggal dan Kelas -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="get" action="<?php echo site_url('Jadwal_pengawas/uts'); ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="tanggal" class="mr-2">Filter Tanggal:</label>
                                <input type="date" 
                                       name="tanggal" 
                                       id="tanggal" 
                                       class="form-control form-control-sm" 
                                       value="<?php echo $filter_tanggal ? $filter_tanggal : ''; ?>">
                            </div>
                            <div class="form-group mr-2">
                                <label for="kelas" class="mr-2">Filter Kelas:</label>
                                <select name="kelas" 
                                        id="kelas" 
                                        class="form-control form-control-sm">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php if (!empty($kelas_options)): ?>
                                        <?php foreach ($kelas_options as $kelas_key => $kelas_val): ?>
                                            <option value="<?php echo htmlspecialchars($kelas_key); ?>" 
                                                    <?php echo ($filter_kelas == $kelas_key) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kelas_val); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <?php if ($filter_tanggal || $filter_kelas): ?>
                                <a href="<?php echo site_url('Jadwal_pengawas/uts'); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th width="8%" class="text-center">SKS</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="8%">Tanggal</th>
                                <th width="10%">Jam</th>
                                <th width="8%" class="text-center">Sesi</th>
                                <th width="10%">Ruang</th>
                                <th width="15%">Dosen Pengampu</th>
                                <th width="10%" class="text-center">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jadwal_uts)): ?>
                                <?php $no = 1; foreach ($jadwal_uts as $j): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($j->kode_mk ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($j->nama_mk ?? '-'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($j->sks ?? '-'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($j->kelas ?? '-'); ?></td>
                                        <td><?php echo $j->tanggal ? date('d/m/Y', strtotime($j->tanggal)) : '-'; ?></td>
                                        <td>
                                            <?php 
                                            if ($j->jam_mulai && $j->jam_selesai) {
                                                echo date('H:i', strtotime($j->jam_mulai)) . ' - ' . date('H:i', strtotime($j->jam_selesai));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($j->sesi) && $j->sesi == 2): ?>
                                                <span class="badge badge-warning">Sesi 2</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Sesi 1</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($j->nama_ruang ?? ($j->kode_ruang ?? '-')); ?></td>
                                        <td><?php echo htmlspecialchars($j->nama_dosen ?? '-'); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_pengawas/view_absensi_uts/' . $j->id); ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Lihat Status Absensi Mahasiswa">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Belum ada jadwal UTS dimana Anda menjadi pengawas.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

