<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Mahasiswa</h3>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Nama</strong><br><?php echo $pengajuan->nama_mahasiswa; ?> (<?php echo $pengajuan->nim; ?>)</p>
                <p class="mb-1"><strong>Kelas</strong><br><?php echo $pengajuan->kelas ?: '-'; ?></p>
                <p class="mb-0"><strong>Judul PKL</strong><br><?php echo $pengajuan->judul_pkl ?: '-'; ?></p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Berikan nilai 0 - 100 untuk setiap item penilaian. Sistem akan menghitung nilai P secara otomatis berdasarkan bobot yang telah ditentukan.</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Input Penilaian PKL (Sekprodi)</h3>
            </div>
            <?php echo form_open('Pkl_penilaian_sekprodi/form/' . $pengajuan->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php if (empty($aspek_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tidak ada aspek penilaian yang ditugaskan untuk Sekprodi.
                    </div>
                <?php else: ?>
                    <?php foreach ($aspek_list as $aspek): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <strong><?php echo $aspek->kode_aspek; ?>:</strong> <?php echo $aspek->nama_aspek; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($aspek->items)): ?>
                                    <p class="text-muted mb-0">Belum ada item penilaian untuk aspek ini.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Item Penilaian</th>
                                                    <th width="10%" class="text-center">Bobot</th>
                                                    <th width="15%" class="text-center">Nilai (0-100)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no_item = 1;
                                                foreach ($aspek->items as $item): 
                                                    $nilai_item = isset($penilaian_details[$item->id]) ? $penilaian_details[$item->id] : '';
                                                ?>
                                                    <tr>
                                                        <td><?php echo $no_item++; ?></td>
                                                        <td>
                                                            <strong><?php echo $item->pertanyaan; ?></strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-secondary"><?php echo $item->bobot; ?>%</span>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   name="nilai_item_<?php echo $item->id; ?>" 
                                                                   class="form-control form-control-sm" 
                                                                   min="0" 
                                                                   max="100" 
                                                                   step="0.01" 
                                                                   required
                                                                   value="<?php echo $nilai_item; ?>"
                                                                   placeholder="0-100">
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

                    <?php if ($penilaian): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-sync-alt"></i> 
                            Anda dapat memperbarui nilai yang pernah disimpan sebelumnya.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Pkl_penilaian_sekprodi'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Penilaian
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

