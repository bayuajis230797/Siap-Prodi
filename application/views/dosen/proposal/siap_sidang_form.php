<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Informasi Mahasiswa</h3></div>
            <div class="card-body">
                <p class="mb-1"><strong>Nama</strong><br><?php echo htmlspecialchars($proposal->nama_mahasiswa ?? '-'); ?> (<?php echo htmlspecialchars($proposal->nim ?? '-'); ?>)</p>
                <p class="mb-1"><strong>Kelas</strong><br><?php echo htmlspecialchars($proposal->kelas ?? '-'); ?></p>
                <p class="mb-0"><strong>Judul</strong><br><?php echo htmlspecialchars($judul ?: '-'); ?></p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Beri nilai 0 - 100 untuk tiap item. Nilai bimbingan dihitung otomatis dari bobot item.
            Menyimpan form ini berarti Anda menyatakan mahasiswa <strong>layak sidang</strong>.</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header"><h3 class="card-title">Nilai Bimbingan dan Layak Sidang Proposal TA</h3></div>
            <?php echo form_open('Proposal_mahasiswa_dosen/siap_sidang/' . $proposal->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <div class="card mb-3">
                    <div class="card-header bg-light"><h5 class="mb-0"><strong><?php echo htmlspecialchars($aspek->kode_aspek); ?>:</strong> <?php echo htmlspecialchars($aspek->nama_aspek); ?></h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th><th>Item Penilaian</th>
                                        <th width="10%" class="text-center">Bobot</th>
                                        <th width="18%" class="text-center">Nilai (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($item_list as $item): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($item->pertanyaan); ?></strong></td>
                                            <td class="text-center"><span class="badge badge-secondary"><?php echo (float) $item->bobot; ?>%</span></td>
                                            <td>
                                                <input type="number" name="nilai_item_<?php echo $item->id; ?>" class="form-control form-control-sm"
                                                       min="0" max="100" step="0.01" required placeholder="0-100"
                                                       value="<?php echo isset($penilaian_details[$item->id]) ? htmlspecialchars($penilaian_details[$item->id]) : ''; ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($nilai_bimbingan): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-sync-alt"></i>
                        Nilai tersimpan saat ini: <strong><?php echo format_nilai_proposal($nilai_bimbingan->nilai_rata); ?></strong>. Anda dapat memperbaruinya
                        selama sidang belum dijadwalkan.
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Proposal_mahasiswa_dosen'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
                <button type="submit" class="btn btn-success"
                        onclick="return confirm('Simpan nilai dan nyatakan mahasiswa layak sidang?');">
                    <i class="fas fa-check"></i> Simpan Nilai dan Layak Sidang
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
