<?php
$r = $rencana;
$rs = $r ? $r['ringkasan'] : array();
$arah_label = $r ? array('maju' => 'MAJU', 'mundur' => 'MUNDUR', 'sama' => 'SAMA') [$r['arah']] : '-';
?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Pratinjau: aktifkan <?php echo htmlspecialchars($ta->tahun_akademik . ' ' . ucfirst($ta->semester)); ?></h3>
                <div class="card-tools"><a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body">
                <p class="mb-1">Tahun akademik aktif sekarang:
                    <strong><?php echo $aktif ? htmlspecialchars($aktif->tahun_akademik . ' ' . ucfirst($aktif->semester)) : '(belum ada)'; ?></strong>
                    <?php if ($r): ?>&nbsp;&rarr;&nbsp; arah perpindahan: <span class="badge badge-<?php echo $r['arah'] === 'mundur' ? 'warning' : 'info'; ?>"><?php echo $arah_label; ?></span><?php endif; ?></p>
                <?php if ($hw): ?><p class="mb-1">Tahun akademik tertinggi yang pernah diterapkan kenaikan kelasnya: <strong><?php echo htmlspecialchars($hw->tahun_akademik . ' ' . ucfirst($hw->semester)); ?></strong></p><?php endif; ?>
                <p class="text-muted mb-0">Halaman ini hanya pratinjau. Tidak ada data yang diubah sampai Anda menekan tombol konfirmasi di bawah.</p>
            </div>
        </div>

        <?php if (!$migrasi_ok): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Migrasi database kenaikan kelas (<code>2026_11_15_kenaikan_kelas.sql</code>) belum dijalankan.
                Aktivasi akan memakai cara lama (tanpa pratinjau, riwayat, dan fitur pulihkan).
            </div>
            <?php echo form_open('Tahun_akademik/activate/' . $ta->id); ?>
                <button type="submit" class="btn btn-warning" onclick="return confirm('Aktifkan dengan cara lama?');"><i class="fas fa-check"></i> Aktifkan (cara lama)</button>
            <?php echo form_close(); ?>
        <?php elseif ($mode === 'ganti_saja'): ?>
            <div class="alert alert-info">
                <h5><i class="fas fa-exchange-alt"></i> Hanya mengganti tahun akademik</h5>
                <p class="mb-1"><strong><?php echo htmlspecialchars($ta->tahun_akademik . ' ' . ucfirst($ta->semester)); ?></strong> sama atau lebih lama dari tahun akademik tertinggi yang pernah diterapkan
                    (<?php echo $hw ? htmlspecialchars($hw->tahun_akademik . ' ' . ucfirst($hw->semester)) : '-'; ?>).</p>
                <ul class="mb-0">
                    <li>Data kelas, semester, dan status mahasiswa <strong>tidak diubah atau ditimpa</strong> (tetap tersimpan aman). Tampilan kelas dan semester di seluruh halaman mengikuti tahun akademik ini, dihitung dari angkatan (mis. angkatan 2023 menjadi semester 5 pada 2025/2026 Ganjil).</li>
                    <li>Jadwal, UTS, UAS, dan mata kuliah aktif yang sudah ada <strong>dipertahankan</strong> dan tampil sesuai tahun akademik ini (mata kuliah aktif dibuat hanya bila belum ada).</li>
                    <li>Tidak dibuat riwayat kenaikan kelas.</li>
                </ul>
            </div>
            <div class="alert alert-secondary"><i class="fas fa-eye"></i> Hanya ingin melihat data semester ini? Gunakan pilihan <strong>Tahun Akademik</strong> di bagian atas halaman (mode lihat) agar tahun akademik berjalan tidak ikut berpindah untuk semua pengguna.</div>
            <?php echo form_open('Tahun_akademik/activate/' . $ta->id); ?>
                <button type="submit" class="btn btn-success" onclick="return confirm('Pindahkan tahun akademik resmi ke tahun akademik ini untuk SEMUA pengguna? Kelas mahasiswa tidak berubah.');">
                    <i class="fas fa-check"></i> Konfirmasi: Ganti Tahun Akademik
                </button>
                <a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary">Batal</a>
            <?php echo form_close(); ?>
        <?php else: ?>
            <?php if ($r['blokir'] !== null): ?>
                <div class="alert alert-danger"><i class="fas fa-ban"></i> <strong>Tidak dapat diaktifkan.</strong> <?php echo htmlspecialchars($r['blokir']); ?></div>
            <?php endif; ?>
            <?php if ($r['arah'] === 'mundur' && $r['blokir'] === null): ?>
                <div class="alert alert-warning"><i class="fas fa-undo"></i> Anda memundurkan tahun akademik. Mahasiswa yang sebelumnya ditandai lulus otomatis akan kembali aktif bila semesternya masih dalam batas 14.</div>
            <?php endif; ?>

            <div class="row">
                <?php
                $kartu = array(
                    array('Diproses', $rs['total'], 'secondary', 'Mahasiswa aktif dan lulus-otomatis'),
                    array('Berubah', $rs['berubah'], 'primary', 'Kelas / semester / status berubah'),
                    array('Semester riil naik', $rs['naik'], 'success', ''),
                    array('Semester riil turun', $rs['turun'], 'warning', ''),
                    array('Di atas semester 8', $rs['riil_di_atas_8'], 'info', 'Kelas tetap 8 + rombel'),
                    array('Lulus otomatis', $rs['lulus_otomatis'], 'danger', 'Melewati 14 semester'),
                    array('Ditahan', $rs['ditahan'], 'warning', 'Proses akademik masih berjalan'),
                    array('Kembali aktif', $rs['kembali_aktif'], 'success', 'Sebelumnya lulus otomatis'),
                );
                foreach ($kartu as $k): ?>
                    <div class="col-md-3 col-6">
                        <div class="info-box mb-2">
                            <span class="info-box-icon bg-<?php echo $k[2]; ?>"><strong><?php echo (int) $k[1]; ?></strong></span>
                            <div class="info-box-content"><span class="info-box-text"><?php echo $k[0]; ?></span><small class="text-muted"><?php echo $k[3]; ?></small></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Perubahan per kelompok</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead><tr><th>Angkatan</th><th>Kelas Lama</th><th>Kelas Baru</th><th class="text-center">Semester Riil</th><th>Status</th><th class="text-right">Jumlah</th></tr></thead>
                        <tbody>
                        <?php if (empty($kelompok)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada perubahan kelas/semester.</td></tr>
                        <?php else: foreach ($kelompok as $g): ?>
                            <tr>
                                <td><?php echo (int) $g['angkatan']; ?></td>
                                <td><?php echo htmlspecialchars((string) $g['kelas_lama'] ?: '-'); ?></td>
                                <td><strong><?php echo htmlspecialchars($g['kelas_baru']); ?></strong></td>
                                <td class="text-center"><?php echo (int) $g['semester_riil']; ?></td>
                                <td><?php echo htmlspecialchars($g['status']); ?></td>
                                <td class="text-right"><?php echo (int) $g['jumlah']; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($perhatian)): ?>
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Perlu perhatian (<?php echo count($perhatian); ?>)</h3></div>
                    <div class="card-body table-responsive p-0" style="max-height:360px;overflow:auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Kelas</th><th>Semester Riil</th><th>Keterangan</th></tr></thead>
                            <tbody>
                            <?php foreach ($perhatian as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['nim']); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama']); ?></td>
                                    <td><?php echo (int) $p['angkatan']; ?></td>
                                    <td><?php echo htmlspecialchars((string) $p['lama']['kelas']); ?> &rarr; <?php echo htmlspecialchars($p['baru']['kelas']); ?></td>
                                    <td><?php echo (int) $p['baru']['semester_riil']; ?></td>
                                    <td><?php echo htmlspecialchars($p['keterangan'] !== null ? $p['keterangan'] : 'belum masuk pada tahun akademik ini'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($r['blokir'] === null): ?>
                <?php echo form_open('Tahun_akademik/activate/' . $ta->id); ?>
                    <div class="alert alert-info"><i class="fas fa-shield-alt"></i> Sebelum diterapkan, kondisi lama setiap mahasiswa yang berubah disimpan otomatis dan dapat dipulihkan lewat <strong>Riwayat Kenaikan Kelas</strong>.</div>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Terapkan kenaikan kelas dan aktifkan tahun akademik ini?');">
                        <i class="fas fa-check"></i> Konfirmasi: Terapkan &amp; Aktifkan
                    </button>
                    <a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary">Batal</a>
                <?php echo form_close(); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
