<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Penilaian Sidang Skripsi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada jadwal sidang yang perlu dinilai.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th width="12%">Tanggal</th>
                                    <th width="10%">Jam</th>
                                    <th width="10%">Ruangan</th>
                                    <th width="15%" class="text-center">Sebagai</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($jadwal_list as $jadwal): 
                                    // Tentukan peran dosen
                                    $peran = '';
                                    if ($jadwal->id_ketua_penguji == $dosen_id) {
                                        $peran = 'Ketua Penguji';
                                    } elseif ($jadwal->id_anggota_penguji_1 == $dosen_id) {
                                        $peran = 'Anggota Penguji 1';
                                    } elseif ($jadwal->id_anggota_penguji_2 == $dosen_id) {
                                        $peran = 'Anggota Penguji 2';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($jadwal->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($jadwal->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal->kelas); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></td>
                                        <td><?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $peran; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Penilaian_sidang/nilai/' . $jadwal->id); ?>" 
                                               class="btn btn-sm btn-primary" title="Input Nilai">
                                                <i class="fas fa-edit"></i> Input Nilai
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



