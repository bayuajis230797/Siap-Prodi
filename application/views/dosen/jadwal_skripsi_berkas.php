<?php
$this->load->helper('google_drive');

if (!function_exists('dosen_skripsi_format_ukuran')) {
    function dosen_skripsi_format_ukuran($size) {
        $size = intval($size);
        if ($size <= 0) {
            return '-';
        }
        if ($size < 1024) {
            return $size . ' B';
        }
        if ($size < 1048576) {
            return number_format($size / 1024, 2) . ' KB';
        }
        return number_format($size / 1048576, 2) . ' MB';
    }
}

if (!function_exists('dosen_skripsi_render_berkas_table')) {
    function dosen_skripsi_render_berkas_table($berkas_list) {
        if (empty($berkas_list)) {
            echo '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle"></i> Belum ada berkas yang diupload untuk tahap ini.</div>';
            return;
        }
        ?>
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
                    <?php $no = 1; foreach ($berkas_list as $berkas): ?>
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
                            <td><?php echo google_drive_link_html($berkas->file_path, htmlspecialchars($berkas->file_name)); ?></td>
                            <td>
                                <?php
                                echo !empty($berkas->uploaded_at)
                                    ? date('d/m/Y H:i', strtotime($berkas->uploaded_at))
                                    : '-';
                                ?>
                            </td>
                            <td><?php echo dosen_skripsi_format_ukuran(isset($berkas->file_size) ? $berkas->file_size : 0); ?></td>
                            <td class="text-center"><?php echo google_drive_link_html($berkas->file_path, 'Buka'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Berkas Ajuan Sidang Skripsi
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
                                <p class="mb-1"><strong>Nama:</strong> <?php echo isset($jadwal->nama_mahasiswa) ? htmlspecialchars($jadwal->nama_mahasiswa) : '-'; ?></p>
                                <p class="mb-1"><strong>NIM:</strong> <?php echo isset($jadwal->nim) ? htmlspecialchars($jadwal->nim) : '-'; ?></p>
                                <p class="mb-1"><strong>Kelas:</strong> <?php echo isset($jadwal->kelas) ? htmlspecialchars($jadwal->kelas) : '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Judul Skripsi:</strong></p>
                                <p class="mb-1">
                                    <?php
                                    if (!empty($judul_skripsi_terbaru) && $judul_skripsi_terbaru != 'Belum ada judul') {
                                        echo htmlspecialchars($judul_skripsi_terbaru);
                                    } else {
                                        echo !empty($jadwal->judul_skripsi) ? htmlspecialchars($jadwal->judul_skripsi) : '-';
                                    }
                                    ?>
                                </p>
                                <p class="mb-0"><strong>Tanggal Sidang:</strong>
                                    <?php
                                    if (!empty($jadwal->tanggal_ujian)) {
                                        echo date('d/m/Y', strtotime($jadwal->tanggal_ujian));
                                    } elseif (!empty($jadwal->tanggal)) {
                                        echo date('d/m/Y', strtotime($jadwal->tanggal));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open"></i> Tahap 1 — Pendaftaran Jadwal Sidang
                            <?php if (!empty($pendaftaran_tahap1)): ?>
                                <span class="badge badge-<?php echo ($pendaftaran_tahap1->status === 'disetujui' || $pendaftaran_tahap1->status === 'selesai') ? 'success' : 'secondary'; ?> ml-2">
                                    <?php echo ucfirst($pendaftaran_tahap1->status); ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php dosen_skripsi_render_berkas_table(isset($berkas_tahap1) ? $berkas_tahap1 : array()); ?>
                    </div>
                </div>

                <div class="card card-outline card-success mb-0">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open"></i> Tahap 2 — Pendaftaran Verifikasi Sidang
                            <?php if (!empty($pendaftaran_tahap2)): ?>
                                <span class="badge badge-<?php echo ($pendaftaran_tahap2->status === 'disetujui' || $pendaftaran_tahap2->status === 'selesai') ? 'success' : 'secondary'; ?> ml-2">
                                    <?php echo ucfirst($pendaftaran_tahap2->status); ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php dosen_skripsi_render_berkas_table(isset($berkas_tahap2) ? $berkas_tahap2 : array()); ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?php echo site_url('Jadwal_skripsi_dosen/detail/' . $jadwal->id); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Jadwal
                </a>
            </div>
        </div>
    </div>
</div>
