<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Mahasiswa</h3>
            </div>
            <div class="card-body">
                <?php 
                $judul = 'Belum ada judul';
                if (isset($skripsi->pengajuan)) {
                    $judul = $skripsi->pengajuan->judul_skripsi ?? 'Belum ada judul';
                }
                ?>
                <p class="mb-1"><strong>Nama</strong><br><?php echo isset($skripsi->nama_mahasiswa) ? $skripsi->nama_mahasiswa : '-'; ?> (<?php echo isset($skripsi->nim) ? $skripsi->nim : '-'; ?>)</p>
                <p class="mb-1"><strong>Kelas</strong><br><?php echo isset($skripsi->kelas) ? $skripsi->kelas : '-'; ?></p>
                <p class="mb-0 mt-2"><strong>Tipe Pembimbing</strong><br>
                    <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $tipe_pembimbing)); ?></span>
                </p>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <small>Berikan nilai 0 - 100 untuk setiap item penilaian P1 (Nilai Bimbingan). Sistem akan menghitung nilai P1 secara otomatis berdasarkan bobot yang telah ditentukan. Setelah kedua pembimbing memberikan nilai, mahasiswa akan dinyatakan siap sidang.</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Siap Sidang & Input Nilai Bimbingan Skripsi</h3>
            </div>
            <?php echo form_open('Skripsi_mahasiswa_dosen/siap_sidang/' . $skripsi->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php if (empty($item_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tidak ada item penilaian P1 (Nilai Bimbingan). Silakan hubungi admin untuk setting item penilaian.
                    </div>
                <?php else: ?>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <strong>P1:</strong> Nilai Bimbingan Tugas Akhir
                            </h5>
                        </div>
                        <div class="card-body">
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
                                        foreach ($item_list as $item): 
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
                        </div>
                    </div>

                    <?php if ($nilai_bimbingan): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-sync-alt"></i> 
                            Anda dapat memperbarui nilai yang pernah disimpan sebelumnya.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo site_url('Skripsi_mahasiswa_dosen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Simpan Nilai Bimbingan
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>





