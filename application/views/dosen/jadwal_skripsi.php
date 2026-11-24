<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Sidang Skripsi</h3>
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

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    Berikut adalah jadwal sidang Skripsi dimana Anda menjadi penguji. Setelah sidang selesai, silakan input penilaian P2 (Nilai Sidang) dan catatan revisi.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="12%" class="text-center">Tanggal Sidang</th>
                                <th width="10%" class="text-center">Jam</th>
                                <th>Ruangan</th>
                                <th width="12%" class="text-center">Peran</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Status Penilaian</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal sidang Skripsi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                foreach ($jadwal_list as $jadwal): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo isset($jadwal->nim) ? $jadwal->nim : '-'; ?></strong></td>
                                        <td><?php echo isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-'; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo isset($jadwal->kelas) ? $jadwal->kelas : '-'; ?></span>
                                        </td>
                                        <td class="text-center"><?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-'; ?></td>
                                        <td class="text-center">
                                            <?php 
                                            if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                                                echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo isset($jadwal->ruangan) ? $jadwal->ruangan : '-'; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $peran_label = '';
                                            switch ($jadwal->tipe_penguji ?? '') {
                                                case 'ketua_penguji':
                                                    $peran_label = 'Ketua Penguji';
                                                    break;
                                                case 'penguji_1':
                                                    $peran_label = 'Anggota Penguji 1';
                                                    break;
                                                case 'penguji_2':
                                                    $peran_label = 'Anggota Penguji 2';
                                                    break;
                                                default:
                                                    $peran_label = 'Penguji';
                                            }
                                            ?>
                                            <span class="badge badge-primary"><?php echo $peran_label; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status = $jadwal->status ?? 'terjadwal';
                                            switch ($status) {
                                                case 'terjadwal':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                                case 'batal':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($jadwal->penilaian) && $jadwal->penilaian): ?>
                                                <span class="badge badge-success">Sudah Dinilai</span>
                                                <br><small class="text-muted">
                                                    <?php echo isset($jadwal->penilaian->tanggal_input) ? date('d/m/Y', strtotime($jadwal->penilaian->tanggal_input)) : '-'; ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum Dinilai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_skripsi_dosen/detail/' . $jadwal->id); ?>" class="btn btn-sm btn-primary" title="Input Nilai">
                                                <i class="fas fa-edit"></i> Input Nilai
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


