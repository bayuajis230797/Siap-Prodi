<?php
$menunggu = ($pendaftaran->status === 'menunggu');
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Verifikasi Berkas Sidang Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="35%">NIM</th><td><strong><?php echo htmlspecialchars($pendaftaran->nim ?? '-'); ?></strong></td></tr>
                            <tr><th>Nama</th><td><?php echo htmlspecialchars($pendaftaran->nama_mahasiswa ?? '-'); ?></td></tr>
                            <tr><th>Kelas</th><td><?php echo htmlspecialchars($pendaftaran->kelas ?? '-'); ?></td></tr>
                            <tr><th>Judul</th><td><?php echo htmlspecialchars($judul ?: '-'); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="35%">Status</th><td><?php echo proposal_status_badge($pendaftaran->status); ?></td></tr>
                            <tr><th>Nilai Bimbingan</th><td><?php echo ($proposal && $proposal->nilai_bimbingan !== null) ? format_nilai_proposal($proposal->nilai_bimbingan) : '-'; ?></td></tr>
                            <?php if (!empty($pendaftaran->catatan_sekprodi)): ?>
                                <tr><th>Catatan</th><td><?php echo nl2br(htmlspecialchars($pendaftaran->catatan_sekprodi)); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <h5 class="mb-2"><i class="fas fa-file-alt"></i> Berkas yang Dikirim</h5>
                <?php if (empty($berkas_uploaded)): ?>
                    <div class="alert alert-warning">Belum ada berkas.</div>
                <?php else: ?>
                    <?php $this->load->view('proposal_partials/berkas_table', array('berkas_uploaded' => $berkas_uploaded)); ?>
                <?php endif; ?>

                <?php if ($jadwal): ?>
                    <?php $this->load->view('proposal_partials/jadwal_info', array('jadwal_list' => array($jadwal))); ?>
                    <p><a href="<?php echo site_url('Jadwal_proposal_sekprodi/edit/' . $jadwal->id); ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i> Ubah Jadwal / Penguji</a></p>
                <?php endif; ?>

                <?php if ($menunggu): ?>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-check"></i> Setujui, Tetapkan Jadwal dan Penguji</h5>
                                </div>
                                <?php echo form_open('Proposal_verifikasi_berkas_sidang/approve/' . $pendaftaran->id); ?>
                                <div class="card-body">
                                    <?php if ($jadwal): ?>
                                        <div class="alert alert-warning mb-0">Jadwal mahasiswa ini sudah ada. Ubah lewat menu Jadwal Sidang.</div>
                                    <?php else: ?>
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="tanggal_ujian">Tanggal Ujian <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                                <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                                            <input type="text" name="ruangan" id="ruangan" class="form-control" maxlength="50" required>
                                        </div>
                                        <div class="form-row">
                                            <?php foreach (array(1, 2) as $n): ?>
                                                <div class="form-group col-md-6">
                                                    <label for="id_penguji_<?php echo $n; ?>">Penguji <?php echo $n; ?> <span class="text-danger">*</span></label>
                                                    <select name="id_penguji_<?php echo $n; ?>" id="id_penguji_<?php echo $n; ?>" class="form-control" required>
                                                        <option value="">Pilih Penguji <?php echo $n; ?></option>
                                                        <?php foreach ($dosen_list as $d): ?>
                                                            <?php $is_pemb = ($proposal && (int) $d->id === (int) $proposal->id_pembimbing); ?>
                                                            <?php if ($n === 1 && $is_pemb) { continue; } ?>
                                                            <option value="<?php echo $d->id; ?>" <?php echo ($n === 2 && $is_pemb) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d->nama_dosen . ' - ' . $d->nidn . ($n === 2 && $is_pemb ? ' (Pembimbing)' : '')); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <small class="form-text text-muted">Penguji 2 otomatis terisi pembimbing mahasiswa dan dapat diganti. Penguji 1 harus dosen lain dan berbeda dari Penguji 2. Bentrok ruangan dan penguji diperiksa otomatis.</small>
                                    <?php endif; ?>
                                    <div class="form-group mt-3 mb-0">
                                        <label for="catatan_sekprodi">Catatan (opsional)</label>
                                        <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Setujui pendaftaran ini?');"
                                        <?php echo $jadwal ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card card-danger">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-times"></i> Tolak Pendaftaran</h5></div>
                                <?php echo form_open('Proposal_verifikasi_berkas_sidang/reject/' . $pendaftaran->id); ?>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label for="alasan_tolak">Alasan <span class="text-danger">*</span></label>
                                        <textarea name="catatan_sekprodi" id="alasan_tolak" class="form-control" rows="4" required></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tolak pendaftaran ini?');"><i class="fas fa-times"></i> Tolak</button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
