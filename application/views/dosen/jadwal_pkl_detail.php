<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Jadwal Sidang</h3>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Nama Mahasiswa</strong><br><?php echo $jadwal->nama_mahasiswa; ?> (<?php echo $jadwal->nim; ?>)</p>
                <p class="mb-1"><strong>Kelas</strong><br><?php echo $jadwal->kelas ?: '-'; ?></p>
                <p class="mb-1"><strong>Tanggal Sidang</strong><br><?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?></p>
                <p class="mb-1"><strong>Jam</strong><br><?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></p>
                <p class="mb-1"><strong>Ruangan</strong><br><?php echo $jadwal->ruangan; ?></p>
                <p class="mb-0"><strong>Status</strong><br>
                    <?php
                    $status_class = 'secondary';
                    switch ($jadwal->status) {
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
                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($jadwal->status); ?></span>
                </p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Setelah sidang selesai, berikan penilaian untuk setiap item yang ditugaskan untuk Anda. Anda juga dapat memberikan catatan revisi untuk mahasiswa.</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Input Penilaian Sidang PKL</h3>
            </div>
            <?php echo form_open('Jadwal_pkl_dosen/detail/' . $jadwal->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php if (empty($aspek_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tidak ada aspek penilaian yang ditugaskan untuk Penguji.
                    </div>
                <?php else: ?>
                    <?php foreach ($aspek_list as $aspek): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <strong><?php echo $aspek->kode_aspek; ?>:</strong> <?php echo $aspek->nama_aspek; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($aspek->items)): ?>
                                    <p class="text-muted mb-0">Belum ada item penilaian untuk aspek ini.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Item Penilaian</th>
                                                    <th width="10%" class="text-center">Bobot</th>
                                                    <th width="15%" class="text-center">Nilai (0-100)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no_item = 1;
                                                foreach ($aspek->items as $item): 
                                                    $nilai_item = isset($penilaian_details[$item->id]) ? $penilaian_details[$item->id] : '';
                                                ?>
                                                    <tr>
                                                        <td><?php echo $no_item++; ?></td>
                                                        <td>
                                                            <strong><?php echo $item->pertanyaan; ?></strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-secondary"><?php echo $item->bobot; ?>%</span>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   name="nilai_item_<?php echo $item->id; ?>" 
                                                                   class="form-control form-control-sm" 
                                                                   min="0" 
                                                                   max="100" 
                                                                   step="0.01" 
                                                                   required
                                                                   value="<?php echo $nilai_item; ?>"
                                                                   placeholder="0-100">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Form Catatan Revisi -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">
                                <i class="fas fa-comment-dots"></i> Catatan Revisi Pasca Sidang
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="catatan_revisi">Catatan Revisi untuk Mahasiswa</label>
                                <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="5" placeholder="Masukkan catatan revisi yang perlu dilakukan mahasiswa setelah sidang (opsional)..."><?php echo $penilaian ? htmlspecialchars($penilaian->catatan) : ''; ?></textarea>
                                <small class="form-text text-muted">Catatan revisi ini akan dapat dilihat oleh mahasiswa setelah sidang selesai.</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($penilaian): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-sync-alt"></i> 
                            Anda dapat memperbarui penilaian dan catatan revisi yang pernah disimpan sebelumnya.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Jadwal_pkl_dosen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?php echo site_url('Jadwal_pkl_dosen/berkas/' . $jadwal->id); ?>" class="btn btn-info">
                    <i class="fas fa-file-download"></i> Lihat Berkas Pendaftaran
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Penilaian & Catatan Revisi
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

