<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Daftar Mahasiswa Bimbingan Skripsi
                </h3>
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    Berikut adalah daftar mahasiswa bimbingan Skripsi Anda. Klik tombol <strong>"Layak Sidang"</strong> untuk menginputkan nilai bimbingan dan menyatakan mahasiswa layak sidang.
                </div>

                <?php if (empty($mahasiswa_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Belum ada mahasiswa bimbingan Skripsi yang judulnya sudah disetujui.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th width="12%">Tipe Pembimbing</th>
                                    <th width="15%" class="text-center">Status Saya</th>
                                    <th width="15%" class="text-center">Status Pembimbing Lain</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($mahasiswa_list as $skripsi): 
                                    // Cek apakah sudah ada nilai bimbingan
                                    $this->load->model('Skripsi_nilai_bimbingan_model');
                                    $nilai_bimbingan = null;
                                    $status_saya = false;
                                    $status_pembimbing_lain = false;
                                    $nama_pembimbing_lain = '-';
                                    
                                    if ($skripsi->id_pembimbing_1 == $dosen_id) {
                                        $nilai_bimbingan = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 1);
                                        $tipe_pembimbing = 'Pembimbing 1';
                                        $status_saya = isset($skripsi->layak_pembimbing_1) ? $skripsi->layak_pembimbing_1 : false;
                                        $status_pembimbing_lain = isset($skripsi->layak_pembimbing_2) ? $skripsi->layak_pembimbing_2 : false;
                                        // Alias dari get_all() adalah 'pembimbing_2' bukan 'nama_pembimbing_2'
                                        $nama_pembimbing_lain = (isset($skripsi->pembimbing_2) && !empty($skripsi->pembimbing_2)) ? $skripsi->pembimbing_2 : 'Pembimbing 2';
                                    } elseif ($skripsi->id_pembimbing_2 == $dosen_id) {
                                        $nilai_bimbingan = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 2);
                                        $tipe_pembimbing = 'Pembimbing 2';
                                        $status_saya = isset($skripsi->layak_pembimbing_2) ? $skripsi->layak_pembimbing_2 : false;
                                        $status_pembimbing_lain = isset($skripsi->layak_pembimbing_1) ? $skripsi->layak_pembimbing_1 : false;
                                        // Alias dari get_all() adalah 'pembimbing_1' bukan 'nama_pembimbing_1'
                                        $nama_pembimbing_lain = (isset($skripsi->pembimbing_1) && !empty($skripsi->pembimbing_1)) ? $skripsi->pembimbing_1 : 'Pembimbing 1';
                                    }
                                    $sudah_nilai = ($nilai_bimbingan !== null);
                                    $status_layak = isset($skripsi->status) && $skripsi->status === 'layak_sidang';
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($skripsi->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($skripsi->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($skripsi->kelas ?? '-'); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo isset($tipe_pembimbing) ? $tipe_pembimbing : '-'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($status_saya): ?>
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle"></i> LAYAK SIDANG
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning badge-lg">
                                                    <i class="fas fa-clock"></i> BELUM LAYAK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($status_pembimbing_lain): ?>
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle"></i> LAYAK SIDANG
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning badge-lg">
                                                    <i class="fas fa-clock"></i> BELUM LAYAK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo site_url('Skripsi_mahasiswa_dosen/siap_sidang/' . $skripsi->id); ?>" 
                                                   class="btn btn-sm btn-success" 
                                                   title="Layak Sidang - Input Nilai Bimbingan">
                                                    <i class="fas fa-check-circle"></i> Layak Sidang
                                                </a>
                                                <?php if ($status_layak): ?>
                                                    <a href="<?php echo site_url('Skripsi_mahasiswa_dosen/detail_penilaian/' . $skripsi->id); ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Detail Penilaian">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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


