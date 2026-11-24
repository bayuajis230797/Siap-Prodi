<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Jadwal Sidang</h3>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Nama Mahasiswa</strong><br><?php echo isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-'; ?> (<?php echo isset($jadwal->nim) ? $jadwal->nim : '-'; ?>)</p>
                <p class="mb-1"><strong>Kelas</strong><br><?php echo isset($jadwal->kelas) ? $jadwal->kelas : '-'; ?></p>
                <p class="mb-1"><strong>Judul Skripsi</strong><br>
                    <small class="text-muted">
                        <?php 
                        if (isset($judul_skripsi_terbaru) && !empty($judul_skripsi_terbaru) && $judul_skripsi_terbaru != 'Belum ada judul') {
                            echo htmlspecialchars($judul_skripsi_terbaru);
                        } else {
                            echo isset($jadwal->judul_skripsi) && !empty($jadwal->judul_skripsi) ? htmlspecialchars($jadwal->judul_skripsi) : '-';
                        }
                        ?>
                    </small>
                </p>
                <p class="mb-1"><strong>Tanggal Sidang</strong><br><?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-'; ?></p>
                <p class="mb-1"><strong>Jam</strong><br>
                    <?php 
                    if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                        echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                    } else {
                        echo '-';
                    }
                    ?>
                </p>
                <p class="mb-1"><strong>Ruangan</strong><br><?php echo isset($jadwal->ruangan) ? $jadwal->ruangan : '-'; ?></p>
                <p class="mb-0"><strong>Peran</strong><br>
                    <?php
                    $peran_label = '';
                    switch ($tipe_penguji ?? '') {
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
                </p>
                <p class="mb-0 mt-2"><strong>Status</strong><br>
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
                </p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Setelah sidang selesai, berikan penilaian P2 (Nilai Sidang) untuk setiap item yang ditugaskan untuk Anda. Anda juga dapat memberikan catatan revisi untuk mahasiswa.</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Input Penilaian Sidang Skripsi</h3>
            </div>
            <?php echo form_open('Jadwal_skripsi_dosen/detail/' . $jadwal->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php if (empty($item_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tidak ada item penilaian P2 (Nilai Sidang). Silakan hubungi admin untuk setting item penilaian.
                    </div>
                <?php else: ?>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <strong>P2:</strong> Nilai Ujian Sidang SKRIPSI
                            </h5>
                        </div>
                        <div class="card-body">
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
                                        foreach ($item_list as $item): 
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
                        </div>
                    </div>

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
                                <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="5" placeholder="Masukkan catatan revisi yang perlu dilakukan mahasiswa setelah sidang (opsional)..."><?php echo isset($nilai_sidang->catatan) ? htmlspecialchars($nilai_sidang->catatan) : ''; ?></textarea>
                                <small class="form-text text-muted">Catatan revisi ini akan dapat dilihat oleh mahasiswa setelah sidang selesai.</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($nilai_sidang): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-sync-alt"></i> 
                            Anda dapat memperbarui penilaian dan catatan revisi yang pernah disimpan sebelumnya.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Jadwal_skripsi_dosen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?php echo site_url('Jadwal_skripsi_dosen/berkas/' . $jadwal->id); ?>" class="btn btn-info">
                    <i class="fas fa-file-download"></i> Lihat Berkas Tahap 1 &amp; 2
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Penilaian & Catatan Revisi
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>





