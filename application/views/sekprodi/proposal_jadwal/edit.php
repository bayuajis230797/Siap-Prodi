<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Ubah Jadwal Sidang Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_proposal_sekprodi'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <?php echo form_open('Jadwal_proposal_sekprodi/edit/' . $jadwal->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <p class="mb-3"><strong><?php echo htmlspecialchars($jadwal->nama_mahasiswa ?? '-'); ?></strong> (<?php echo htmlspecialchars($jadwal->nim ?? '-'); ?>)</p>

                <?php if ($terkunci): ?>
                    <div class="alert alert-secondary"><i class="fas fa-lock"></i> Jadwal sudah selesai (semua penguji telah menilai) dan tidak dapat diubah.</div>
                <?php endif; ?>
                <?php if ($nilai_ada && !$terkunci): ?>
                    <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Sudah ada penilaian sidang: penguji tidak dapat diganti.</div>
                <?php endif; ?>

                <fieldset <?php echo $terkunci ? 'disabled' : ''; ?>>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="tanggal_ujian">Tanggal Ujian <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" value="<?php echo htmlspecialchars($jadwal->tanggal_ujian); ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?php echo $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : ''; ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?php echo $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="ruangan" id="ruangan" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($jadwal->ruangan); ?>" required>
                    </div>

                    <div class="form-row">
                        <?php foreach (array(1, 2) as $n): ?>
                            <?php $sel = $n === 1 ? $jadwal->id_penguji_1 : $jadwal->id_penguji_2; ?>
                            <div class="form-group col-md-6">
                                <label for="id_penguji_<?php echo $n; ?>">Penguji <?php echo $n; ?></label>
                                <select name="id_penguji_<?php echo $n; ?>" id="id_penguji_<?php echo $n; ?>" class="form-control" <?php echo $nilai_ada ? 'readonly style="pointer-events:none;background:#e9ecef"' : ''; ?>>
                                    <option value="">- Belum ditetapkan -</option>
                                    <?php foreach ($dosen_list as $d): ?>
                                        <?php $is_pemb = ($id_pembimbing && (int) $d->id === (int) $id_pembimbing); ?>
                                        <?php if ($n === 1 && $is_pemb) { continue; } ?>
                                        <option value="<?php echo $d->id; ?>" <?php echo ((int) $sel === (int) $d->id || ($n === 2 && empty($sel) && $is_pemb)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d->nama_dosen . ' - ' . $d->nidn); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-text text-muted mb-3">Penguji 2 secara bawaan adalah pembimbing mahasiswa dan dapat diganti; Penguji 1 harus dosen lain dan berbeda dari Penguji 2. Kosongkan keduanya bila belum ditetapkan.</small>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="terjadwal" <?php echo $jadwal->status === 'terjadwal' ? 'selected' : ''; ?>>Terjadwal</option>
                            <option value="batal" <?php echo $jadwal->status === 'batal' ? 'selected' : ''; ?>>Batal</option>
                            <?php if ($jadwal->status === 'selesai'): ?><option value="selesai" selected>Selesai</option><?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="catatan">Catatan</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="2"><?php echo htmlspecialchars((string) $jadwal->catatan); ?></textarea>
                    </div>
                </fieldset>
            </div>
            <?php if (!$terkunci): ?>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="<?php echo site_url('Jadwal_proposal_sekprodi'); ?>" class="btn btn-secondary">Batal</a>
                </div>
            <?php endif; ?>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
