<!-- Detail Tim Penguji -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background-color: #3c8dbc; color: white;">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users"></i> Detail Tim Penguji
                </h3>
            </div>
            <div class="card-body">
                <h4 class="mb-2"><strong><?php echo $jadwal->nama_mahasiswa; ?></strong></h4>
                <p class="text-muted mb-3"><?php echo $jadwal->nim; ?></p>
                
                <hr style="border-top: 2px solid #3c8dbc;">
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Tanggal</strong></p>
                        <p><?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Jam</strong></p>
                        <p><?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Ruangan</strong></p>
                        <p><?php echo $jadwal->ruangan ?: '-'; ?></p>
                    </div>
                </div>
                
                <hr style="border-top: 2px solid #3c8dbc;">
                
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Ketua Penguji</strong></p>
                        <p><?php echo $jadwal->ketua_penguji ? $jadwal->ketua_penguji . ' (' . $jadwal->ketua_penguji_nidn . ')' : '-'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Anggota Penguji 1</strong></p>
                        <p><?php echo $jadwal->anggota_penguji_1 ? $jadwal->anggota_penguji_1 . ' (' . $jadwal->anggota_penguji_1_nidn . ')' : '-'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Anggota Penguji 2</strong></p>
                        <p><?php echo $jadwal->anggota_penguji_2 ? $jadwal->anggota_penguji_2 . ' (' . $jadwal->anggota_penguji_2_nidn . ')' : '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Form Penilaian Sidang Skripsi</h3>
                <div class="card-tools">
                    <?php if ($nilai): ?>
                        <span class="badge badge-success">Nilai tersimpan</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Belum dinilai</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php echo form_open('Jadwal_ujian/penilaian/' . $jadwal->id); ?>
            <div class="card-body" style="max-height: 650px; overflow-y: auto;">
                <div class="alert alert-light border text-sm mb-3">
                    <strong>Petunjuk:</strong> Rentang nilai: 0 - 100. Sistem menghitung otomatis N1, N2, N3, N4 dan Nilai Akhir sesuai formula:
                    <ul class="mb-0 mt-2">
                        <li>N1 = (Sistematika + Bahasa + Format) / 3</li>
                        <li>N2 = (Penguasaan Isi + Penguasaan Tugas + Penguasaan Konsep) / 3</li>
                        <li>N3 = (Penampilan + Penggunaan Alat + Kesesuaian Waktu + Kejelasan Materi) / 4</li>
                        <li>N4 = (Penguasaan Produk + Perancangan Produk) / 2</li>
                        <li><strong>Nilai Akhir = (N1 + N2 + N3 + N4) / 4</strong></li>
                    </ul>
                </div>

                <h6 class="text-uppercase text-muted mt-3 mb-3">1. ISI DAN BOBOT NASKAH TUGAS AKHIR</h6>
                <div class="form-group">
                    <label>a. Ketepatan Sistematika dan Konsistensi <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_sistematika" 
                           value="<?php echo $nilai ? $nilai->nilai_sistematika : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>b. Ketepatan Bahasa yang digunakan <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_bahasa" 
                           value="<?php echo $nilai ? $nilai->nilai_bahasa : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>c. Ketepatan Format Penulisan <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_format" 
                           value="<?php echo $nilai ? $nilai->nilai_format : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4 mb-3">2. PENGUASAAN MATERI TUGAS AKHIR</h6>
                <div class="form-group">
                    <label>a. Penguasaan Isi Materi Tugas Akhir <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penguasaan_isi" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_isi : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>b. Penguasaan Isi Tugas Akhir <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penguasaan_tugas" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_tugas : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>c. Penguasaan Konsep Perancangan <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penguasaan_konsep" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_konsep : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4 mb-3">3. PRESENTASI DAN PENAMPILAN</h6>
                <div class="form-group">
                    <label>a. Penampilan <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penampilan" 
                           value="<?php echo $nilai ? $nilai->nilai_penampilan : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>b. Penguasaan Penggunaan Alat <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penggunaan_alat" 
                           value="<?php echo $nilai ? $nilai->nilai_penggunaan_alat : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>c. Kesesuaian Penggunaan Waktu <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_kesesuaian_waktu" 
                           value="<?php echo $nilai ? $nilai->nilai_kesesuaian_waktu : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>d. Kejelasan Mengungkapkan Materi <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_kejelasan_materi" 
                           value="<?php echo $nilai ? $nilai->nilai_kejelasan_materi : ''; ?>" required>
                </div>

                <h6 class="text-uppercase text-muted mt-4 mb-3">4. HASIL RANCANG BANGUN</h6>
                <div class="form-group">
                    <label>a. Penguasaan Produk Tugas Akhir <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_penguasaan_produk" 
                           value="<?php echo $nilai ? $nilai->nilai_penguasaan_produk : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>b. Hasil Perancangan Produk Tugas Akhir <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="nilai_perancangan_produk" 
                           value="<?php echo $nilai ? $nilai->nilai_perancangan_produk : ''; ?>" required>
                </div>

                <div class="form-group mt-4">
                    <label>Catatan (Opsional)</label>
                    <textarea name="catatan" rows="3" class="form-control" placeholder="Tambahkan catatan atau saran"><?php echo $nilai ? htmlspecialchars($nilai->catatan) : ''; ?></textarea>
                </div>

                <div class="form-group mt-4">
                    <label>Catatan Revisi untuk Mahasiswa <?php echo (!$nilai || empty($nilai->catatan_revisi)) ? '<span class="text-danger">*</span>' : ''; ?></label>
                    <textarea name="catatan_revisi" rows="5" class="form-control" placeholder="Masukkan catatan revisi yang akan dilihat oleh mahasiswa" <?php echo (!$nilai || empty($nilai->catatan_revisi)) ? 'required' : ''; ?>><?php echo $nilai ? htmlspecialchars($nilai->catatan_revisi) : ''; ?></textarea>
                    <small class="form-text text-muted">Catatan ini akan dapat dilihat oleh mahasiswa setelah sidang selesai. <?php echo ($nilai && !empty($nilai->catatan_revisi)) ? 'Anda dapat mengubah catatan ini jika diperlukan.' : ''; ?></small>
                </div>

                <?php if ($nilai): ?>
                    <div class="alert alert-success text-center mt-3">
                        <h5>Nilai Terakhir</h5>
                        <div class="display-4"><strong><?php echo number_format($nilai->nilai_total, 2); ?></strong></div>
                        <div class="mt-2">
                            <small>
                                N1 = <?php echo number_format($nilai->n1, 2); ?> | 
                                N2 = <?php echo number_format($nilai->n2, 2); ?> | 
                                N3 = <?php echo number_format($nilai->n3, 2); ?> | 
                                N4 = <?php echo number_format($nilai->n4, 2); ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Jadwal_ujian'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Nilai
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

