<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Berita Acara & Kehadiran UAS</h3>
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

                <!-- Info Jadwal -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Informasi Jadwal UAS</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Mata Kuliah:</strong> <?php echo $jadwal->kode_mk; ?> - <?php echo $jadwal->nama_mk; ?><br>
                            <strong>Kelas:</strong> <?php echo $jadwal->kelas; ?><br>
                            <strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?><br>
                            <strong>Ruang:</strong> <?php echo $jadwal->nama_ruang; ?><br>
                            <strong>Dosen:</strong> <?php echo $jadwal->nama_dosen; ?>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?php echo site_url('Jadwal_uas/berita_acara/' . $jadwal->id); ?>">
                    <!-- Berita Acara -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Berita Acara Pelaksanaan</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="berita_acara">Berita Acara <span class="text-danger">*</span></label>
                                <textarea name="berita_acara" id="berita_acara" class="form-control" rows="5" required><?php echo isset($berita_acara) ? $berita_acara->berita_acara : ''; ?></textarea>
                                <small class="form-text text-muted">Tuliskan berita acara pelaksanaan UAS</small>
                            </div>
                            <div class="form-group">
                                <label for="catatan">Catatan</label>
                                <textarea name="catatan" id="catatan" class="form-control" rows="3"><?php echo isset($berita_acara) ? $berita_acara->catatan : ''; ?></textarea>
                                <small class="form-text text-muted">Catatan tambahan (opsional)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Kehadiran Mahasiswa -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Kehadiran Mahasiswa</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($mahasiswa)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Tidak ada mahasiswa aktif di kelas <?php echo $jadwal->kelas; ?>.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">NIM</th>
                                                <th>Nama Mahasiswa</th>
                                                <th width="15%" class="text-center">Status Kehadiran</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($mahasiswa as $m): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><strong><?php echo $m->nim; ?></strong></td>
                                                    <td><?php echo $m->nama_mahasiswa; ?></td>
                                                    <td>
                                                        <select name="kehadiran[<?php echo $m->id; ?>]" class="form-control form-control-sm" required>
                                                            <option value="hadir" <?php echo (!isset($kehadiran[$m->id]) || (isset($kehadiran[$m->id]) && $kehadiran[$m->id]->status_kehadiran == 'hadir')) ? 'selected' : ''; ?>>Hadir</option>
                                                            <option value="tidak_hadir" <?php echo (isset($kehadiran[$m->id]) && $kehadiran[$m->id]->status_kehadiran == 'tidak_hadir') ? 'selected' : ''; ?>>Tidak Hadir</option>
                                                            <option value="izin" <?php echo (isset($kehadiran[$m->id]) && $kehadiran[$m->id]->status_kehadiran == 'izin') ? 'selected' : ''; ?>>Izin</option>
                                                            <option value="sakit" <?php echo (isset($kehadiran[$m->id]) && $kehadiran[$m->id]->status_kehadiran == 'sakit') ? 'selected' : ''; ?>>Sakit</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               name="keterangan_kehadiran[<?php echo $m->id; ?>]" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="Keterangan (opsional)"
                                                               value="<?php echo isset($kehadiran[$m->id]) ? $kehadiran[$m->id]->keterangan : ''; ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Berita Acara & Kehadiran
                        </button>
                        <a href="<?php echo site_url('Jadwal_uas'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

