<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Ujian</h3>
            </div>
            <div class="card-body row">
                <div class="col-md-4">
                    <strong>Mahasiswa</strong><br>
                    <?php echo htmlspecialchars($jadwal->nama_mahasiswa); ?> (<?php echo htmlspecialchars($jadwal->nim); ?>)<br>
                    <span class="badge badge-info mt-1"><?php echo htmlspecialchars($jadwal->kelas); ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Jadwal</strong><br>
                    <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?><br>
                    <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?><br>
                    Ruangan: <?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?>
                </div>
                <div class="col-md-4">
                    <strong>Sebagai</strong><br>
                    <span class="badge badge-primary">
                        <?php
                        $peran_label = '';
                        switch ($peran) {
                            case 'ketua':
                                $peran_label = 'Ketua Penguji';
                                break;
                            case 'anggota_1':
                                $peran_label = 'Anggota Penguji 1';
                                break;
                            case 'anggota_2':
                                $peran_label = 'Anggota Penguji 2';
                                break;
                        }
                        echo $peran_label;
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

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

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Form Penilaian Sidang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Penilaian_sidang'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?php echo form_open('Penilaian_sidang/nilai/' . $jadwal->id); ?>
            <div class="card-body" style="max-height: 650px; overflow-y: auto;">
                <div class="alert alert-light border text-sm">
                    <i class="fas fa-info-circle"></i> Rentang nilai: 0 - 100. Sistem menghitung otomatis N1 s.d N4 dan Nilai Akhir.
                </div>

                <h6 class="text-uppercase text-muted mt-3">1. Isi & Bobot Naskah</h6>
                <div class="form-group">
                    <label>Ketepatan Sistematika dan Konsistensi</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_sistematika" 
                           value="<?php echo $nilai ? $nilai->nilai_sistematika : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Ketepatan Bahasa yang digunakan</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_bahasa" 
                           value="<?php echo $nilai ? $nilai->nilai_bahasa : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Ketepatan Format Penulisan</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_format" 
                           value="<?php echo $nilai ? $nilai->nilai_format : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4">2. Penguasaan Materi</h6>
                <div class="form-group">
                    <label>Penguasaan Isi Materi Tugas Akhir</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penguasaan_isi" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_isi : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Penguasaan Isi Tugas Akhir</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penguasaan_tugas" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_tugas : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Penguasaan Konsep Perancangan</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penguasaan_konsep" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_konsep : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4">3. Presentasi & Penampilan</h6>
                <div class="form-group">
                    <label>Penampilan</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penampilan" 
                           value="<?php echo $nilai ? $nilai->nilai_penampilan : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Penguasaan Penggunaan Alat</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penggunaan_alat" 
                           value="<?php echo $nilai ? $nilai->nilai_penggunaan_alat : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Kesesuaian Penggunaan Waktu</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_kesesuaian_waktu" 
                           value="<?php echo $nilai ? $nilai->nilai_kesesuaian_waktu : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Kejelasan Mengungkapkan Materi</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_kejelasan_materi" 
                           value="<?php echo $nilai ? $nilai->nilai_kejelasan_materi : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4">4. Hasil Rancang Bangun</h6>
                <div class="form-group">
                    <label>Penguasaan Produk Tugas Akhir</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_penguasaan_produk" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_produk : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Hasil Perancangan Produk</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" 
                           name="nilai_perancangan_produk" 
                           value="<?php echo $nilai ? $nilai->nilai_perancangan_produk : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea name="catatan" rows="3" class="form-control" placeholder="Tambahkan catatan"><?php echo $nilai ? htmlspecialchars($nilai->catatan) : ''; ?></textarea>
                </div>

                <?php if ($nilai): ?>
                    <div class="alert alert-success text-center">
                        <h5>Nilai Terakhir</h5>
                        <div class="mb-2">
                            <strong>Nilai Total: <?php echo number_format($nilai->nilai_total, 2); ?></strong>
                        </div>
                        <small>
                            N1=<?php echo number_format($nilai->n1, 2); ?> | 
                            N2=<?php echo number_format($nilai->n2, 2); ?> | 
                            N3=<?php echo number_format($nilai->n3, 2); ?> | 
                            N4=<?php echo number_format($nilai->n4, 2); ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Nilai
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>



