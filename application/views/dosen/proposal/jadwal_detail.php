<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Informasi Sidang</h3></div>
            <div class="card-body">
                <p class="mb-1"><strong>Mahasiswa</strong><br><?php echo htmlspecialchars($jadwal->nama_mahasiswa ?? '-'); ?> (<?php echo htmlspecialchars($jadwal->nim ?? '-'); ?>)</p>
                <p class="mb-1"><strong>Judul</strong><br><?php echo htmlspecialchars($judul ?: '-'); ?></p>
                <p class="mb-1"><strong>Jadwal</strong><br><?php echo hari_dari_tanggal($jadwal->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?>
                    <?php echo $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-'; ?>-<?php echo $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-'; ?>
                    di <?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></p>
                <p class="mb-1"><strong>Peran Anda</strong><br><span class="badge badge-info"><?php echo $peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span></p>
                <p class="mb-0"><strong>Penilai yang sudah menilai</strong><br><?php echo (int) $jumlah_penilai; ?> dari 2</p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Beri nilai 0 - 100 untuk tiap butir. Nilai sidang dihitung otomatis dari bobot butir. Nilai akhir sidang mahasiswa adalah rata-rata nilai Penguji 1 dan Penguji 2.</small>
        </div>
        <a href="<?php echo site_url('Jadwal_proposal_dosen/berkas/' . $jadwal->id); ?>" class="btn btn-outline-info btn-block"><i class="fas fa-folder-open"></i> Lihat Berkas Ajuan</a>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header"><h3 class="card-title">Penilaian Sidang Proposal TA</h3></div>
            <?php echo form_open('Jadwal_proposal_dosen/detail/' . $jadwal->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                <?php if ($terkunci !== null): ?>
                    <div class="alert alert-warning"><i class="fas fa-lock"></i> <?php echo htmlspecialchars($terkunci); ?></div>
                <?php endif; ?>

                <?php if (!empty($item_list)): ?>
                    <fieldset <?php echo $terkunci !== null ? 'disabled' : ''; ?>>
                        <div class="card mb-3">
                            <div class="card-header bg-light"><h5 class="mb-0"><strong><?php echo htmlspecialchars($aspek->kode_aspek); ?>:</strong> <?php echo htmlspecialchars($aspek->nama_aspek); ?></h5></div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th width="5%">No</th><th>Butir Penilaian</th><th width="10%" class="text-center">Bobot</th><th width="18%" class="text-center">Nilai (0-100)</th></tr></thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($item_list as $item): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($item->pertanyaan); ?></strong></td>
                                                <td class="text-center"><span class="badge badge-secondary"><?php echo (float) $item->bobot; ?>%</span></td>
                                                <td><input type="number" name="nilai_item_<?php echo $item->id; ?>" class="form-control form-control-sm" min="0" max="100" step="0.01" required
                                                           value="<?php echo isset($penilaian_details[$item->id]) ? htmlspecialchars($penilaian_details[$item->id]) : ''; ?>"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="catatan_revisi">Catatan Revisi untuk Mahasiswa</label>
                            <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="5" placeholder="Bagian yang perlu direvisi setelah sidang..."><?php echo $nilai_sidang ? htmlspecialchars((string) $nilai_sidang->catatan_revisi) : ''; ?></textarea>
                        </div>
                    </fieldset>
                    <?php if ($nilai_sidang): ?>
                        <div class="alert alert-secondary mb-0"><i class="fas fa-sync-alt"></i> Nilai tersimpan: <strong><?php echo number_format((float) $nilai_sidang->nilai_total, 2, ',', '.'); ?></strong>.
                            <?php echo $terkunci === null ? 'Anda dapat memperbaruinya.' : ''; ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">Butir penilaian sidang belum tersedia. Hubungi admin.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Jadwal_proposal_dosen'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <?php if ($terkunci === null && !empty($item_list)): ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Simpan penilaian sidang?');"><i class="fas fa-check"></i> Simpan Penilaian</button>
                <?php endif; ?>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
