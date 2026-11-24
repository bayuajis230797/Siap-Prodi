<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Verifikasi Berkas Sidang Skripsi</h3>
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
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Skripsi_verifikasi_berkas_sidang/index'); ?>">
                            <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                            <?php if ($kelas_filter): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <?php if (isset($per_page) && $per_page != 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
                            <div class="input-group">
                                <select name="tahap" class="form-control">
                                    <option value="">-- Semua Tahap --</option>
                                    <option value="jadwal" <?php echo (isset($tahap_filter) && $tahap_filter == 'jadwal') ? 'selected' : ''; ?>>Tahap Jadwal</option>
                                    <option value="verifikasi" <?php echo (isset($tahap_filter) && $tahap_filter == 'verifikasi') ? 'selected' : ''; ?>>Tahap Verifikasi</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Skripsi_verifikasi_berkas_sidang/index'); ?>">
                            <?php if ($tahap_filter): ?><input type="hidden" name="tahap" value="<?php echo htmlspecialchars($tahap_filter); ?>"><?php endif; ?>
                            <?php if ($kelas_filter): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <?php if (isset($per_page) && $per_page != 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="menunggu" <?php echo ($status_filter == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="disetujui" <?php echo ($status_filter == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo ($status_filter == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                    <option value="selesai" <?php echo ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($status_filter || $tahap_filter): ?>
                                        <a href="<?php echo site_url('Skripsi_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Skripsi_verifikasi_berkas_sidang/index'); ?>">
                            <?php if ($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <?php if ($tahap_filter): ?>
                                <input type="hidden" name="tahap" value="<?php echo htmlspecialchars($tahap_filter); ?>">
                            <?php endif; ?>
                            <?php if (isset($per_page) && $per_page != 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
                            <div class="input-group">
                                <select name="kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php if (!empty($kelas_list)): ?>
                                        <?php foreach ($kelas_list as $kelas): ?>
                                            <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" <?php echo ($kelas_filter == $kelas->kelas) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kelas->kelas); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Skripsi_verifikasi_berkas_sidang/index'); ?>" id="formPerPageVerifikasiBerkasSkripsi" class="form-inline justify-content-end">
                            <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                            <?php if ($tahap_filter): ?><input type="hidden" name="tahap" value="<?php echo htmlspecialchars($tahap_filter); ?>"><?php endif; ?>
                            <?php if ($kelas_filter): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <label for="per_page_verifikasi_berkas_skripsi" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_verifikasi_berkas_skripsi" class="form-control form-control-sm" onchange="document.getElementById('formPerPageVerifikasiBerkasSkripsi').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="10%" class="text-center">Tahap</th>
                                <th>Judul Skripsi</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Tanggal</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftaran_list)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada pendaftaran sidang Skripsi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($pendaftaran_list as $pendaftaran): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pendaftaran->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pendaftaran->nama_mahasiswa ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($pendaftaran->kelas ?? '-'); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $tahap = isset($pendaftaran->tahap) ? $pendaftaran->tahap : 'verifikasi';
                                            $tahap_badge = ($tahap == 'jadwal') ? 'badge-primary' : 'badge-success';
                                            ?>
                                            <span class="badge <?php echo $tahap_badge; ?>"><?php echo ucfirst($tahap); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($pendaftaran->judul_skripsi ?? '-'); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($pendaftaran->status ?? 'menunggu') {
                                                case 'menunggu':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'info';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($pendaftaran->status ?? 'Menunggu'); ?></span>
                                        </td>
                                        <td class="text-center"><?php echo isset($pendaftaran->created_at) ? date('d/m/Y H:i', strtotime($pendaftaran->created_at)) : '-'; ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id); ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">Menampilkan <?php echo count($pendaftaran_list); ?> dari <?php echo $total_rows ?? 0; ?> data</p>
                        </div>
                        <div class="col-md-6">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

