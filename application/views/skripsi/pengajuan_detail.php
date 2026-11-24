<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Detail Pengajuan Skripsi</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama Mahasiswa</dt>
                    <dd class="col-sm-8"><?php echo $pengajuan->nama_mahasiswa; ?></dd>

                    <dt class="col-sm-4">NIM</dt>
                    <dd class="col-sm-8"><?php echo $pengajuan->nim; ?></dd>

                    <dt class="col-sm-4">Kelas</dt>
                    <dd class="col-sm-8"><?php echo $pengajuan->kelas ?: '-'; ?></dd>

                    <dt class="col-sm-4">Judul Diajukan</dt>
                    <dd class="col-sm-8"><?php echo $pengajuan->judul_pengajuan; ?></dd>

                    <dt class="col-sm-4">File Proposal</dt>
                    <dd class="col-sm-8">
                        <?php if (!empty($pengajuan->file_proposal)): ?>
                            <a href="<?php echo site_url('Skripsi/download_proposal/' . $pengajuan->id); ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> Download Proposal
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
<?php endif; ?>

<?php if (!empty($skripsi_detail)): ?>
<div class="card card-outline card-success mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Ringkasan Skripsi</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Status</th>
                        <td>
                            <?php
                            $status_class = 'secondary';
                            $status_text = ucfirst(str_replace('_', ' ', $skripsi_detail->status));
                            switch ($skripsi_detail->status) {
                                case 'terdaftar': $status_class = 'info'; break;
                                case 'sedang_skripsi': $status_class = 'primary'; break;
                                case 'layak_sidang': $status_class = 'warning'; break;
                                case 'selesai_skripsi': $status_class = 'success'; break;
                                case 'batal': $status_class = 'danger'; break;
                            }
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Judul Skripsi</th>
                        <td><?php echo $skripsi_detail->judul_skripsi ?: '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td><?php echo $skripsi_detail->tanggal_mulai ? date('d/m/Y', strtotime($skripsi_detail->tanggal_mulai)) : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td><?php echo $skripsi_detail->tanggal_selesai ? date('d/m/Y', strtotime($skripsi_detail->tanggal_selesai)) : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Pembimbing 1</th>
                        <td>
                            <?php if (!empty($skripsi_detail->pembimbing_1)): ?>
                                <strong><?php echo $skripsi_detail->pembimbing_1; ?></strong><br>
                                <small class="text-muted">NIDN: <?php echo $skripsi_detail->pembimbing_1_nidn ?: '-'; ?></small>
                            <?php else: ?>
                                <span class="text-muted">Belum ditetapkan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Pembimbing 2</th>
                        <td>
                            <?php if (!empty($skripsi_detail->pembimbing_2)): ?>
                                <strong><?php echo $skripsi_detail->pembimbing_2; ?></strong><br>
                                <small class="text-muted">NIDN: <?php echo $skripsi_detail->pembimbing_2_nidn ?: '-'; ?></small>
                            <?php else: ?>
                                <span class="text-muted">Belum ditetapkan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th width="25%">Aspek</th>
                        <th width="25%" class="text-center">Nilai</th>
                        <th width="25%" class="text-center">Detail Bimbingan</th>
                        <th width="25%" class="text-center">Detail Sidang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Nilai Bimbingan</strong></td>
                        <td class="text-center"><?php echo $skripsi_detail->nilai_bimbingan_final !== null ? number_format($skripsi_detail->nilai_bimbingan_final, 2) : '<span class="text-muted">-</span>'; ?></td>
                        <td class="text-center">
                            P1: <?php echo $skripsi_detail->nilai_bimbingan_p1 !== null ? number_format($skripsi_detail->nilai_bimbingan_p1, 2) : '<span class="text-muted">-</span>'; ?><br>
                            P2: <?php echo $skripsi_detail->nilai_bimbingan_p2 !== null ? number_format($skripsi_detail->nilai_bimbingan_p2, 2) : '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td class="text-center">-</td>
                    </tr>
                    <tr>
                        <td><strong>Nilai Sidang</strong></td>
                        <td class="text-center"><?php echo $skripsi_detail->nilai_sidang !== null ? number_format($skripsi_detail->nilai_sidang, 2) : '<span class="text-muted">-</span>'; ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">
                            <?php
                            $nilai_penguji_display = array();
                            if (!empty($jadwal_skripsi_detail)) {
                                foreach ($jadwal_skripsi_detail as $jadwal) {
                                    if (isset($nilai_sidang_penguji[$jadwal->id])) {
                                        $nilai = $nilai_sidang_penguji[$jadwal->id];
                                        if (!empty($nilai['ketua']) && $nilai['ketua']->nilai_total !== null) {
                                            $nilai_penguji_display[] = 'Ketua: ' . number_format($nilai['ketua']->nilai_total, 2);
                                        }
                                        if (!empty($nilai['anggota_1']) && $nilai['anggota_1']->nilai_total !== null) {
                                            $nilai_penguji_display[] = 'Anggota 1: ' . number_format($nilai['anggota_1']->nilai_total, 2);
                                        }
                                        if (!empty($nilai['anggota_2']) && $nilai['anggota_2']->nilai_total !== null) {
                                            $nilai_penguji_display[] = 'Anggota 2: ' . number_format($nilai['anggota_2']->nilai_total, 2);
                                        }
                                    }
                                }
                            }
                            echo !empty($nilai_penguji_display) ? implode('<br>', $nilai_penguji_display) : '<span class="text-muted">-</span>';
                            ?>
                        </td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Nilai Akhir Skripsi</strong></td>
                        <td class="text-center"><?php echo $skripsi_detail->nilai_skripsi_final !== null ? '<strong>' . number_format($skripsi_detail->nilai_skripsi_final, 2) . '</strong>' : '<span class="text-muted">-</span>'; ?></td>
                        <td class="text-center"><?php echo $skripsi_detail->nilai_bimbingan_final !== null ? number_format($skripsi_detail->nilai_bimbingan_final * 0.4, 2) . ' (40%)' : '<span class="text-muted">-</span>'; ?></td>
                        <td class="text-center"><?php echo $skripsi_detail->nilai_sidang !== null ? number_format($skripsi_detail->nilai_sidang * 0.6, 2) . ' (60%)' : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Grade</strong></td>
                        <td colspan="3" class="text-center">
                            <?php echo $skripsi_detail->nilai_huruf ? '<span class="badge badge-success" style="font-size:1.1em;padding:6px 12px;">' . $skripsi_detail->nilai_huruf . '</span>' : '<span class="text-muted">-</span>'; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($nilai_bimbingan_detail)): ?>
            <div class="mt-4">
                <h5 class="mb-3"><i class="fas fa-user-check"></i> Rincian Nilai Pembimbing</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th width="20%">Pembimbing</th>
                                <th width="12%" class="text-center">Kedisiplinan</th>
                                <th width="12%" class="text-center">Kreativitas</th>
                                <th width="12%" class="text-center">Penguasaan Materi</th>
                                <th width="15%" class="text-center">Kelengkapan Referensi</th>
                                <th width="12%" class="text-center">Nilai Rata-rata</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nilai_bimbingan_detail as $nilai): ?>
                                <?php 
                                    $label = $nilai->tipe_pembimbing == 1 ? 'Pembimbing 1' : 'Pembimbing 2';
                                    $nama = $nilai->tipe_pembimbing == 1 ? ($skripsi_detail->pembimbing_1 ?? '-') : ($skripsi_detail->pembimbing_2 ?? '-');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $label; ?></strong><br>
                                        <small><?php echo $nama; ?></small>
                                    </td>
                                    <td class="text-center"><?php echo number_format($nilai->kedisiplinan, 2); ?></td>
                                    <td class="text-center"><?php echo number_format($nilai->kreativitas, 2); ?></td>
                                    <td class="text-center"><?php echo number_format($nilai->penguasaan_materi, 2); ?></td>
                                    <td class="text-center"><?php echo number_format($nilai->kelengkapan_referensi, 2); ?></td>
                                    <td class="text-center"><strong><?php echo number_format($nilai->nilai_rata, 2); ?></strong></td>
                                    <td><?php echo !empty($nilai->catatan) ? nl2br(htmlspecialchars($nilai->catatan)) : '<span class="text-muted">-</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($jadwal_skripsi_detail) && !empty($nilai_sidang_penguji)): ?>
            <div class="mt-4">
                <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Rincian Nilai Penguji</h5>
                <?php foreach ($jadwal_skripsi_detail as $jadwal): ?>
                    <?php if (isset($nilai_sidang_penguji[$jadwal->id])): ?>
                        <?php $nilai = $nilai_sidang_penguji[$jadwal->id]; ?>
                        <div class="card card-outline card-info mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-alt"></i>
                                    Jadwal: <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?>
                                    <?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="25%">Penguji</th>
                                                <th width="15%" class="text-center">Nilai Total</th>
                                                <th width="15%" class="text-center">N1</th>
                                                <th width="15%" class="text-center">N2</th>
                                                <th width="15%" class="text-center">N3</th>
                                                <th width="15%" class="text-center">N4</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($nilai['ketua'] && $nilai['ketua']->nilai_total !== null): ?>
                                                <tr>
                                                    <td><strong>Ketua Penguji</strong><br><small><?php echo $jadwal->ketua_penguji; ?> (<?php echo $jadwal->ketua_penguji_nidn; ?>)</small></td>
                                                    <td class="text-center"><strong><?php echo number_format($nilai['ketua']->nilai_total, 2); ?></strong></td>
                                                    <td class="text-center"><?php echo number_format($nilai['ketua']->n1, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['ketua']->n2, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['ketua']->n3, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['ketua']->n4, 2); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if ($nilai['anggota_1'] && $nilai['anggota_1']->nilai_total !== null): ?>
                                                <tr>
                                                    <td><strong>Anggota Penguji 1</strong><br><small><?php echo $jadwal->anggota_penguji_1; ?> (<?php echo $jadwal->anggota_penguji_1_nidn; ?>)</small></td>
                                                    <td class="text-center"><strong><?php echo number_format($nilai['anggota_1']->nilai_total, 2); ?></strong></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_1']->n1, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_1']->n2, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_1']->n3, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_1']->n4, 2); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if ($nilai['anggota_2'] && $nilai['anggota_2']->nilai_total !== null): ?>
                                                <tr>
                                                    <td><strong>Anggota Penguji 2</strong><br><small><?php echo $jadwal->anggota_penguji_2; ?> (<?php echo $jadwal->anggota_penguji_2_nidn; ?>)</small></td>
                                                    <td class="text-center"><strong><?php echo number_format($nilai['anggota_2']->nilai_total, 2); ?></strong></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_2']->n1, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_2']->n2, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_2']->n3, 2); ?></td>
                                                    <td class="text-center"><?php echo number_format($nilai['anggota_2']->n4, 2); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Pembimbing yang Ditentukan</dt>
                    <dd class="col-sm-8">
                        Pembimbing 1: <strong><?php echo $pengajuan->pembimbing_1_final_nama ?? '<span class="text-muted">Belum ditentukan</span>'; ?></strong><br>
                        Pembimbing 2: <strong><?php echo $pengajuan->pembimbing_2_final_nama ?? '<span class="text-muted">Belum ditentukan</span>'; ?></strong>
                    </dd>
                    
                    <?php if ($pengajuan->status === 'disetujui' && ($pengajuan->id_pembimbing_1_final || $pengajuan->id_pembimbing_2_final)): ?>
                        <dt class="col-sm-4">Status ACC Judul</dt>
                        <dd class="col-sm-8">
                            <?php
                            $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
                            $status_p2 = $pengajuan->status_acc_judul_p2 ?? 'pending';
                            $badge_p1 = array('pending' => 'warning', 'acc' => 'success', 'revisi' => 'danger');
                            $badge_p2 = array('pending' => 'warning', 'acc' => 'success', 'revisi' => 'danger');
                            ?>
                            Pembimbing 1: 
                            <span class="badge badge-<?php echo $badge_p1[$status_p1] ?? 'secondary'; ?>">
                                <?php echo strtoupper($status_p1); ?>
                            </span>
                            <?php if ($status_p1 === 'revisi' && !empty($pengajuan->catatan_revisi_p1)): ?>
                                <br><small class="text-danger">Catatan: <?php echo htmlspecialchars($pengajuan->catatan_revisi_p1); ?></small>
                            <?php endif; ?>
                            <br>
                            Pembimbing 2: 
                            <span class="badge badge-<?php echo $badge_p2[$status_p2] ?? 'secondary'; ?>">
                                <?php echo strtoupper($status_p2); ?>
                            </span>
                            <?php if ($status_p2 === 'revisi' && !empty($pengajuan->catatan_revisi_p2)): ?>
                                <br><small class="text-danger">Catatan: <?php echo htmlspecialchars($pengajuan->catatan_revisi_p2); ?></small>
                            <?php endif; ?>
                        </dd>
                        
                        <?php if (!empty($pengajuan->file_persetujuan_judul) || !empty($pengajuan->judul_revisi)): ?>
                            <dt class="col-sm-4">Dokumen Revisi</dt>
                            <dd class="col-sm-8">
                                <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                                    <strong>File Persetujuan:</strong><br>
                                    <a href="<?php echo site_url('Skripsi/download_persetujuan_judul/' . $pengajuan->id); ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download
                                    </a><br>
                                <?php endif; ?>
                                <?php if (!empty($pengajuan->judul_revisi)): ?>
                                    <strong>Judul Revisi:</strong><br>
                                    <?php echo $pengajuan->judul_revisi; ?><br>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Status: 
                                    <span class="badge badge-<?php echo $pengajuan->status_approve_revisi === 'disetujui' ? 'success' : ($pengajuan->status_approve_revisi === 'ditolak' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($pengajuan->status_approve_revisi ?? 'pending'); ?>
                                    </span>
                                </small>
                            </dd>
                        <?php endif; ?>
                    <?php endif; ?>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 31px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
}
.timeline > div {
    position: relative;
    margin-bottom: 20px;
}
.time-label span {
    padding: 5px 15px;
    border-radius: 4px;
    color: #fff;
    font-weight: bold;
}
.timeline-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-left: 60px;
    position: relative;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-right: 15px solid #ddd;
}
.timeline-item::after {
    content: '';
    position: absolute;
    left: -14px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-right: 15px solid #fff;
}
.timeline > div > i {
    position: absolute;
    left: 18px;
    top: 20px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    text-align: center;
    line-height: 30px;
    color: #fff;
    z-index: 1;
}
.timeline-item .time {
    float: right;
    color: #999;
    font-size: 12px;
}
.timeline-item .timeline-header {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: bold;
}
.bg-gray { background-color: #6c757d !important; }
</style>

                    <dt class="col-sm-4">Status Pengajuan</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-<?php echo ($pengajuan->status === 'pending') ? 'warning' : (($pengajuan->status === 'disetujui') ? 'success' : 'danger'); ?>">
                            <?php echo ucfirst($pengajuan->status); ?>
                        </span>
                        <br><small>Diajukan pada <?php echo date('d/m/Y H:i', strtotime($pengajuan->created_at)); ?></small>
                    </dd>

                    <?php if (!empty($pengajuan->catatan_sekprodi)): ?>
                        <dt class="col-sm-4">Catatan Sekprodi</dt>
                        <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success mb-3">
            <div class="card-header">
                <h3 class="card-title">Keputusan Sekprodi</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Skripsi/review/' . $pengajuan->id); ?>
                    <input type="hidden" name="decision" value="approve">
                    <div class="form-group">
                        <label for="judul_disetujui">Judul Disetujui <span class="text-danger">*</span></label>
                        <input type="text" name="judul_disetujui" id="judul_disetujui" class="form-control" value="<?php echo set_value('judul_disetujui', $pengajuan->judul_disetujui ?? $pengajuan->judul_pengajuan); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_pembimbing_1">Pembimbing 1 <span class="text-danger">*</span></label>
                        <select name="id_pembimbing_1" id="id_pembimbing_1" class="form-control" required>
                            <option value="">-- Pilih Pembimbing 1 --</option>
                            <?php if (!empty($pembimbing1_options)): ?>
                                <?php foreach ($pembimbing1_options as $pb1): ?>
                                    <option value="<?php echo isset($pb1->id_dosen) ? $pb1->id_dosen : $pb1->id; ?>" <?php echo set_select('id_pembimbing_1', isset($pb1->id_dosen) ? $pb1->id_dosen : $pb1->id, (isset($pengajuan->id_pembimbing_1_final) && $pengajuan->id_pembimbing_1_final == (isset($pb1->id_dosen) ? $pb1->id_dosen : $pb1->id))); ?>>
                                        <?php echo (isset($pb1->nidn) && $pb1->nidn ? $pb1->nidn . ' - ' : '') . (isset($pb1->nama_dosen) ? $pb1->nama_dosen : '-'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>-- Belum ada dosen pembimbing yang diaktifkan --</option>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">Hanya dosen yang diaktifkan sebagai pembimbing skripsi di menu Dosen (Admin) yang muncul di sini.</small>
                    </div>
                    <div class="form-group">
                        <label for="id_pembimbing_2">Pembimbing 2 <span class="text-danger">*</span></label>
                        <select name="id_pembimbing_2" id="id_pembimbing_2" class="form-control" required>
                            <option value="">-- Pilih Pembimbing 2 --</option>
                            <?php if (!empty($pembimbing2_options)): ?>
                                <?php foreach ($pembimbing2_options as $pb2): ?>
                                    <option value="<?php echo isset($pb2->id_dosen) ? $pb2->id_dosen : $pb2->id; ?>" <?php echo set_select('id_pembimbing_2', isset($pb2->id_dosen) ? $pb2->id_dosen : $pb2->id, (isset($pengajuan->id_pembimbing_2_final) && $pengajuan->id_pembimbing_2_final == (isset($pb2->id_dosen) ? $pb2->id_dosen : $pb2->id))); ?>>
                                        <?php echo (isset($pb2->nidn) && $pb2->nidn ? $pb2->nidn . ' - ' : '') . (isset($pb2->nama_dosen) ? $pb2->nama_dosen : '-'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>-- Belum ada dosen pembimbing yang diaktifkan --</option>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">Hanya dosen yang diaktifkan sebagai pembimbing skripsi di menu Dosen (Admin) yang muncul di sini.</small>
                    </div>
                    <div class="form-group">
                        <label for="catatan_disetujui">Catatan (opsional)</label>
                        <textarea name="catatan_disetujui" id="catatan_disetujui" rows="2" class="form-control"><?php echo set_value('catatan_disetujui'); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block" name="action" value="approve" <?php echo ($pengajuan->status !== 'pending') ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i> Setujui dan Tetapkan Pembimbing
                    </button>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Tolak Pengajuan</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Skripsi/review/' . $pengajuan->id); ?>
                    <input type="hidden" name="decision" value="reject">
                    <div class="form-group">
                        <label for="catatan_ditolak">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_ditolak" id="catatan_ditolak" rows="3" class="form-control" required><?php echo set_value('catatan_ditolak'); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block" <?php echo ($pengajuan->status !== 'pending') ? 'disabled' : ''; ?>>
                        <i class="fas fa-times"></i> Tolak Pengajuan
                    </button>
                <?php echo form_close(); ?>
            </div>
        </div>

        <a href="<?php echo site_url('Skripsi/pengajuan'); ?>" class="btn btn-secondary btn-block">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pengajuan
        </a>
    </div>
</div>

<!-- Timeline Histori Skripsi -->
<div class="card card-outline card-info mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-stream"></i> Timeline Histori Skripsi</h3>
    </div>
    <div class="card-body">
        <?php if (empty($timeline_events)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> Belum ada histori yang tercatat.
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($timeline_events as $event): ?>
                    <?php
                        $icon = 'fa-circle';
                        $color = 'primary';
                        $badge_color = 'secondary';

                        switch ($event['type']) {
                            case 'pengajuan':
                                $icon = 'fa-file-alt';
                                $color = 'info';
                                $badge_color = $event['status'] === 'disetujui' ? 'success' : ($event['status'] === 'ditolak' ? 'danger' : 'warning');
                                break;
                            case 'pembimbing':
                                $icon = 'fa-user-tie';
                                $color = 'success';
                                $badge_color = 'success';
                                break;
                            case 'acc_judul':
                                $icon = 'fa-check-circle';
                                if ($event['status'] === 'acc') {
                                    $color = 'success';
                                    $badge_color = 'success';
                                } else {
                                    $color = 'warning';
                                    $badge_color = 'warning';
                                }
                                break;
                            case 'upload_persetujuan':
                                $icon = 'fa-upload';
                                $color = 'warning';
                                $badge_color = $event['status'] === 'disetujui' ? 'success' : ($event['status'] === 'ditolak' ? 'danger' : 'warning');
                                break;
                            case 'bimbingan':
                                $icon = 'fa-comments';
                                $color = 'primary';
                                if ($event['status'] === 'acc') {
                                    $badge_color = 'success';
                                } elseif ($event['status'] === 'revisi') {
                                    $badge_color = 'warning';
                                } else {
                                    $badge_color = 'info';
                                }
                                break;
                        }
                    ?>
                    <div class="time-label">
                        <span class="bg-<?php echo $color; ?>">
                            <?php echo date('d M Y', strtotime($event['date'])); ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas <?php echo $icon; ?> bg-<?php echo $color; ?>"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> <?php echo date('H:i:s', strtotime($event['date'])); ?></span>
                            <h3 class="timeline-header">
                                <?php echo $event['title']; ?>
                                <span class="badge badge-<?php echo $badge_color; ?> ml-2">
                                    <?php
                                        if ($event['type'] === 'bimbingan') {
                                            echo strtoupper($event['status'] === 'chat' ? 'CHAT' : $event['status']);
                                        } elseif ($event['type'] === 'upload_persetujuan') {
                                            echo strtoupper($event['status'] === 'disetujui' ? 'DISETUJUI' : ($event['status'] === 'ditolak' ? 'DITOLAK' : 'MENUNGGU'));
                                        } else {
                                            echo strtoupper($event['status']);
                                        }
                                    ?>
                                </span>
                            </h3>
                            <div class="timeline-body">
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

                                <?php if ($event['type'] === 'pengajuan' && !empty($event['data']->file_proposal)): ?>
                                    <p>
                                        <a href="<?php echo site_url('Skripsi/download_proposal/' . $event['data']->id); ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Download Proposal
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if ($event['type'] === 'upload_persetujuan' && !empty($event['data']->file_persetujuan_judul)): ?>
                                    <p>
                                        <a href="<?php echo site_url('Skripsi/download_persetujuan_judul/' . $event['data']->id); ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Download Persetujuan Judul
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if ($event['type'] === 'bimbingan' && !empty($event['data']['log']->file_path)): ?>
                                    <p>
                                        <a href="<?php echo site_url('Skripsi/download_bimbingan_file/' . $event['data']['log']->id); ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Download File
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Histori Bimbingan Mahasiswa -->
<div class="card card-outline card-primary mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-history"></i> Histori Bimbingan Mahasiswa</h3>
        <a href="<?php echo site_url('Skripsi/export_histori_bimbingan/' . $pengajuan->id); ?>" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($bimbingan_list)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> Belum ada histori bimbingan untuk mahasiswa ini.
            </div>
        <?php else: ?>
            <?php foreach ($bimbingan_list as $bimbingan): ?>
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-tie"></i> Pembimbing: <?php echo $bimbingan->nama_dosen; ?>
                            <span class="badge badge-<?php echo $bimbingan->tipe_pembimbing == 1 ? 'primary' : 'secondary'; ?> ml-2">
                                Pembimbing <?php echo $bimbingan->tipe_pembimbing; ?>
                            </span>
                            <span class="badge badge-<?php echo $bimbingan->status === 'open' ? 'success' : 'secondary'; ?> ml-2">
                                <?php echo strtoupper($bimbingan->status); ?>
                            </span>
                        </h5>
                        <div class="card-tools">
                            <small class="text-muted">
                                Topik: <?php echo $bimbingan->judul_topik ?: '-'; ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php $logs = $bimbingan_logs[$bimbingan->id] ?? array(); ?>
                        <?php if (empty($logs)): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-info-circle"></i> Belum ada percakapan pada bimbingan ini.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Tanggal & Waktu</th>
                                            <th width="15%">Pengirim</th>
                                            <th width="35%">Pesan</th>
                                            <th width="15%">File</th>
                                            <th width="10%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($log->created_at)); ?><br>
                                                    <small class="text-muted"><?php echo date('H:i:s', strtotime($log->created_at)); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $log->role_pengguna === 'mahasiswa' ? 'info' : 'primary'; ?>">
                                                        <?php echo ucfirst($log->role_pengguna); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo nl2br(htmlspecialchars($log->pesan)); ?>
                                                    <?php if (!empty($log->keterangan_revisi)): ?>
                                                        <br><small class="text-muted"><em>Keterangan: <?php echo htmlspecialchars($log->keterangan_revisi); ?></em></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($log->file_path) && !empty($log->file_name)): ?>
                                                        <a href="<?php echo site_url('Skripsi/download_bimbingan_file/' . $log->id); ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-download"></i> <?php echo htmlspecialchars($log->file_name); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($log->status_revisi)): ?>
                                                        <?php
                                                            $badge = 'secondary';
                                                            if ($log->status_revisi === 'acc') {
                                                                $badge = 'success';
                                                            } elseif ($log->status_revisi === 'revisi') {
                                                                $badge = 'warning';
                                                            } elseif ($log->status_revisi === 'menunggu') {
                                                                $badge = 'secondary';
                                                            }
                                                        ?>
                                                        <span class="badge badge-<?php echo $badge; ?>">
                                                            <?php echo strtoupper($log->status_revisi); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
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
        <?php endif; ?>
    </div>
</div>
