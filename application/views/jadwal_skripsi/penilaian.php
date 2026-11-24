<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Ujian</h3>
            </div>
            <div class="card-body row">
                <div class="col-md-4">
                    <strong>Mahasiswa</strong><br>
                    <?php echo $jadwal->nama_mahasiswa; ?> (<?php echo $jadwal->nim; ?>)<br>
                    <span class="badge badge-info mt-1"><?php echo $jadwal->kelas; ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Jadwal</strong><br>
                    <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?><br>
                    <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?><br>
                    Ruangan: <?php echo $jadwal->ruangan ?: '-'; ?>
                </div>
                <div class="col-md-4">
                    <strong>Ringkasan Nilai</strong>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Nilai Bimbingan</td>
                            <td class="text-right"><?php echo $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2) : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>Nilai Sidang</td>
                            <td class="text-right"><?php echo $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2) : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>Nilai Akhir (40% + 60%)</td>
                            <td class="text-right">
                                <?php echo $jadwal->nilai_skripsi_final !== null ? '<strong>' . number_format($jadwal->nilai_skripsi_final, 2) . '</strong>' : '-'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Grade</td>
                            <td class="text-right">
                                <?php echo $jadwal->nilai_huruf ? '<span class="badge badge-success">' . $jadwal->nilai_huruf . '</span>' : '-'; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
<?php endif; ?>

<?php
$penguji_cards = array(
    'ketua' => array(
        'title' => 'Ketua Penguji',
        'nama' => $jadwal->ketua_penguji,
        'nidn' => $jadwal->ketua_penguji_nidn
    ),
    'anggota_1' => array(
        'title' => 'Anggota Penguji 1',
        'nama' => $jadwal->anggota_penguji_1,
        'nidn' => $jadwal->anggota_penguji_1_nidn
    ),
    'anggota_2' => array(
        'title' => 'Anggota Penguji 2',
        'nama' => $jadwal->anggota_penguji_2,
        'nidn' => $jadwal->anggota_penguji_2_nidn
    )
);
?>

<div class="row">
    <?php foreach ($penguji_cards as $peran => $info): ?>
        <?php $nilai_peran = isset($nilai[$peran]) ? $nilai[$peran] : null; ?>
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $info['title']; ?></h3>
                    <div class="card-tools">
                        <?php if ($nilai_peran): ?>
                            <span class="badge badge-success">Nilai tersimpan</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Belum dinilai</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php echo form_open('Jadwal_skripsi/penilaian/' . $jadwal->id); ?>
                <input type="hidden" name="peran" value="<?php echo $peran; ?>">
                <div class="card-body" style="max-height: 650px; overflow-y: auto;">
                    <p class="mb-2">
                        <strong><?php echo $info['nama']; ?></strong><br>
                        <small class="text-muted"><?php echo $info['nidn'] ?: '-'; ?></small>
                    </p>
                    <div class="alert alert-light border text-sm">
                        Rentang nilai: 0 - 100. Sistem menghitung otomatis N1 s.d N4 dan Nilai Akhir.
                    </div>
                    <h6 class="text-uppercase text-muted mt-3">1. Isi & Bobot Naskah</h6>
                    <div class="form-group">
                        <label>Ketepatan Sistematika</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_sistematika" value="<?php echo $nilai_peran ? $nilai_peran->nilai_sistematika : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ketepatan Bahasa</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_bahasa" value="<?php echo $nilai_peran ? $nilai_peran->nilai_bahasa : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ketepatan Format</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_format" value="<?php echo $nilai_peran ? $nilai_peran->nilai_format : ''; ?>" required>
                    </div>

                    <h6 class="text-uppercase text-muted mt-4">2. Penguasaan Materi</h6>
                    <div class="form-group">
                        <label>Penguasaan Isi Materi</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penguasaan_isi" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penguasaan_isi : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Penguasaan Isi Tugas Akhir</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penguasaan_tugas" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penguasaan_tugas : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Penguasaan Konsep Perancangan</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penguasaan_konsep" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penguasaan_konsep : ''; ?>" required>
                    </div>

                    <h6 class="text-uppercase text-muted mt-4">3. Presentasi & Penampilan</h6>
                    <div class="form-group">
                        <label>Penampilan</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penampilan" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penampilan : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Penggunaan Alat</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penggunaan_alat" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penggunaan_alat : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kesesuaian Waktu</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_kesesuaian_waktu" value="<?php echo $nilai_peran ? $nilai_peran->nilai_kesesuaian_waktu : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kejelasan Materi</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_kejelasan_materi" value="<?php echo $nilai_peran ? $nilai_peran->nilai_kejelasan_materi : ''; ?>" required>
                    </div>

                    <h6 class="text-uppercase text-muted mt-4">4. Hasil Rancang Bangun</h6>
                    <div class="form-group">
                        <label>Penguasaan Produk</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_penguasaan_produk" value="<?php echo $nilai_peran ? $nilai_peran->nilai_penguasaan_produk : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hasil Perancangan Produk</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm" name="nilai_perancangan_produk" value="<?php echo $nilai_peran ? $nilai_peran->nilai_perancangan_produk : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Catatan (Opsional)</label>
                        <textarea name="catatan" rows="3" class="form-control form-control-sm" placeholder="Tambahkan catatan"><?php echo $nilai_peran ? htmlspecialchars($nilai_peran->catatan) : ''; ?></textarea>
                    </div>

                    <?php if ($nilai_peran): ?>
                        <div class="alert alert-success text-center">
                            <div>Nilai terakhir: <strong><?php echo number_format($nilai_peran->nilai_total, 2); ?></strong></div>
                            <small>N1=<?php echo number_format($nilai_peran->n1, 2); ?> | N2=<?php echo number_format($nilai_peran->n2, 2); ?> | N3=<?php echo number_format($nilai_peran->n3, 2); ?> | N4=<?php echo number_format($nilai_peran->n4, 2); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Simpan Nilai <?php echo $info['title']; ?>
                    </button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

