<?php $this->load->helper('google_drive'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Berkas Pendaftaran Sidang PKL
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

                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nama:</strong> <?php echo htmlspecialchars($jadwal->nama_mahasiswa); ?></p>
                                <p class="mb-1"><strong>NIM:</strong> <?php echo htmlspecialchars($jadwal->nim); ?></p>
                                <p class="mb-1"><strong>Kelas:</strong> <?php echo htmlspecialchars($jadwal->kelas ?: '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tanggal Sidang:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?></p>
                                <p class="mb-1"><strong>Jam:</strong>
                                    <?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?>
                                </p>
                                <p class="mb-0"><strong>Ruangan:</strong> <?php echo htmlspecialchars($jadwal->ruangan); ?></p>
                                <?php if (!empty($pendaftaran)): ?>
                                    <p class="mb-0 mt-2"><strong>Status Pendaftaran:</strong>
                                        <span class="badge badge-info"><?php echo ucfirst($pendaftaran->status); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($pendaftaran) && !empty($pendaftaran->judul_pkl)): ?>
                            <hr>
                            <p class="mb-0"><strong>Judul PKL:</strong> <?php echo htmlspecialchars($pendaftaran->judul_pkl); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($berkas_list)): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada berkas pendaftaran sidang yang diupload oleh mahasiswa.
                    </div>
                <?php else: ?>
                    <div class="card card-outline card-primary mb-0">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-folder-open"></i> Daftar Berkas Pendaftaran Sidang</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="30%">Nama Berkas</th>
                                            <th width="35%">Nama File / Link</th>
                                            <th width="15%">Tanggal Upload</th>
                                            <th width="10%">Ukuran</th>
                                            <th width="5%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($berkas_list as $berkas):
                                            $file_size = '-';
                                            if (!empty($berkas->file_size)) {
                                                $size = intval($berkas->file_size);
                                                if ($size < 1024) {
                                                    $file_size = $size . ' B';
                                                } elseif ($size < 1048576) {
                                                    $file_size = number_format($size / 1024, 2) . ' KB';
                                                } else {
                                                    $file_size = number_format($size / 1048576, 2) . ' MB';
                                                }
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($berkas->nama_berkas); ?></strong>
                                                    <?php if (!empty($berkas->wajib)): ?>
                                                        <span class="badge badge-danger ml-1">Wajib</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($berkas->deskripsi)): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($berkas->deskripsi); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo google_drive_link_html($berkas->file_path, htmlspecialchars($berkas->file_name)); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo !empty($berkas->uploaded_at)
                                                        ? date('d/m/Y H:i', strtotime($berkas->uploaded_at))
                                                        : '-';
                                                    ?>
                                                </td>
                                                <td><?php echo $file_size; ?></td>
                                                <td class="text-center">
                                                    <?php echo google_drive_link_html($berkas->file_path, 'Buka'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?php echo site_url('Jadwal_pkl_dosen/detail/' . $jadwal->id); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Jadwal
                </a>
            </div>
        </div>
    </div>
</div>
