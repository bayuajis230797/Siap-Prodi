<?php
$this->load->helper('google_drive');
$boleh_kirim = ($syarat_pesan === null) && (!$pendaftaran || $pendaftaran->status === 'ditolak');
?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> <?php echo htmlspecialchars($page_title); ?></h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($keterangan); ?>
                    <?php if (!empty($judul)): ?><br><strong>Judul:</strong> <?php echo htmlspecialchars($judul); ?><?php endif; ?>
                </div>

                <?php $this->load->view('proposal_partials/jadwal_info', array('jadwal_list' => $jadwal_list)); ?>

                <?php if ($syarat_pesan !== null && !$pendaftaran): ?>
                    <div class="alert alert-warning"><i class="fas fa-lock"></i> <?php echo htmlspecialchars($syarat_pesan); ?></div>
                <?php endif; ?>

                <?php if ($pendaftaran): ?>
                    <div class="alert alert-secondary">
                        <h5><i class="fas fa-info-circle"></i> Status Pendaftaran</h5>
                        <p class="mb-0"><strong>Status:</strong> <?php echo proposal_status_badge($pendaftaran->status); ?></p>
                        <?php if (!empty($pendaftaran->catatan_sekprodi)): ?>
                            <p class="mb-0 mt-2"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($pendaftaran->catatan_sekprodi)); ?></p>
                        <?php endif; ?>
                        <?php if ($pendaftaran->status === 'ditolak'): ?>
                            <p class="mb-0 mt-2 text-danger">Pendaftaran ditolak. Perbaiki berkas lalu kirim kembali di bawah.</p>
                        <?php elseif ($pendaftaran->status === 'menunggu'): ?>
                            <p class="mb-0 mt-2">Menunggu verifikasi sekprodi. Berkas tidak dapat diubah selama proses verifikasi.</p>
                        <?php endif; ?>
                    </div>
                    <h5 class="mb-2"><i class="fas fa-file-alt"></i> Berkas yang Dikirim</h5>
                    <?php $this->load->view('proposal_partials/berkas_table', array('berkas_uploaded' => $berkas_uploaded)); ?>
                <?php endif; ?>

                <?php if ($boleh_kirim): ?>
                    <?php echo form_open_multipart($form_action); ?>
                        <h5 class="mb-3"><i class="fas fa-upload"></i> Unggah Berkas</h5>
                        <p class="text-muted">Berkas disimpan ke Google Drive institusi (dikelola admin).</p>

                        <?php if (empty($berkas_list)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada berkas untuk tahap ini yang ditentukan. Hubungi Sekprodi atau Admin.
                            </div>
                        <?php else: ?>
                            <?php foreach ($berkas_list as $berkas): ?>
                                <?php
                                $sudah = null;
                                foreach ($berkas_uploaded as $bu) {
                                    if ((int) $bu->id_berkas_sidang === (int) $berkas->id) { $sudah = $bu; break; }
                                }
                                ?>
                                <div class="form-group">
                                    <label for="file_berkas_<?php echo $berkas->id; ?>">
                                        <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                        <?php if (!empty($berkas->wajib)): ?><span class="text-danger">*</span><?php endif; ?>
                                    </label>
                                    <?php if (!empty($berkas->deskripsi)): ?>
                                        <small class="form-text text-muted mb-1"><?php echo htmlspecialchars($berkas->deskripsi); ?></small>
                                    <?php endif; ?>
                                    <?php if ($sudah): ?>
                                        <?php $this->load->helper('google_drive'); ?>
                                        <div class="alert alert-info py-1 mb-2 small"><?php echo google_drive_link_html($sudah->file_path, 'Berkas saat ini'); ?></div>
                                    <?php endif; ?>
                                    <input type="file" name="file_berkas_<?php echo $berkas->id; ?>" id="file_berkas_<?php echo $berkas->id; ?>"
                                           class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                           <?php echo (!empty($berkas->wajib) && !$sudah) ? 'required' : ''; ?>>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?php echo $pendaftaran ? 'Kirim Ulang Pendaftaran' : 'Kirim Pendaftaran'; ?></button>
                        <?php endif; ?>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
