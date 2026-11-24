<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mapping Mata Kuliah: <?php echo $kurikulum->nama_kurikulum; ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kurikulum/download/' . $kurikulum->id); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-download"></i> Download Kurikulum
                    </a>
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Kurikulum/import_excel/' . $kurikulum->id); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo site_url('Kurikulum'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        <?php if (isset($semester_aktif)): ?>
                            <br><strong>Semester Aktif untuk Kurikulum ini:</strong> Semester <?php echo $semester_aktif; ?>
                            (Mata kuliah semester <?php echo $semester_aktif; ?> akan ditandai sebagai aktif)
                            <?php if ($kurikulum->is_aktif == 1 && get_user_role() !== 'kaprodi'): ?>
                                <br>
                                <a href="<?php echo site_url('Kurikulum/set_aktif_mata_kuliah/' . $kurikulum->id); ?>" 
                                   class="btn btn-primary btn-sm mt-2" 
                                   onclick="return confirm('Aktifkan semua mata kuliah untuk kurikulum ini? Mata kuliah ini akan muncul di menu Mata Kuliah Aktif.')">
                                    <i class="fas fa-check-circle"></i> Aktifkan Semua Mata Kuliah
                                </a>
                                <small class="d-block mt-1 text-muted">Klik tombol ini untuk mengaktifkan semua mata kuliah di kurikulum ini agar muncul di menu Mata Kuliah Aktif.</small>
                            <?php else: ?>
                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Aktifkan kurikulum terlebih dahulu untuk bisa mengaktifkan mata kuliah.</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu untuk menentukan mata kuliah aktif.
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Input langsung mata kuliah dengan format: <strong>kode_mk, nama_mk, sks, semester, is_wajib (1/0)</strong>. 
                    Mata kuliah akan otomatis ditambahkan ke master data jika belum ada.
                </div>

                <!-- Form Tambah Mapping MK -->
                <?php if (get_user_role() !== 'kaprodi'): ?>
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fas fa-plus"></i> Tambah Mapping Mata Kuliah</h4>
                    </div>
                    <?php echo form_open('Kurikulum/add_mapping_mk', array('method' => 'post')); ?>
                    <div class="card-body">
                        <input type="hidden" name="id_kurikulum" value="<?php echo $kurikulum->id; ?>">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="kode_mk">Kode MK <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_mk" id="kode_mk" class="form-control" 
                                           placeholder="MK001" required style="text-transform: uppercase;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mk" id="nama_mk" class="form-control" 
                                           placeholder="Pemrograman Dasar" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="sks">SKS <span class="text-danger">*</span></label>
                                    <input type="number" name="sks" id="sks" class="form-control" 
                                           min="1" max="6" value="3" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="semester">Semester <span class="text-danger">*</span></label>
                                    <input type="number" name="semester" id="semester" class="form-control" 
                                           min="1" max="8" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="is_wajib">Jenis <span class="text-danger">*</span></label>
                                    <select name="is_wajib" id="is_wajib" class="form-control" required>
                                        <option value="1">Wajib</option>
                                        <option value="0">Pilihan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php endif; ?>

                <!-- Tabel Mapping MK -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th width="8%" class="text-center">SKS</th>
                                <th width="10%" class="text-center">Semester</th>
                                <th width="10%" class="text-center">Jenis</th>
                                <th width="10%" class="text-center">Status Aktif</th>
                                <?php if (get_user_role() !== 'kaprodi'): ?><th width="15%" class="text-center">Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mapping_mk)): ?>
                                <tr>
                                    <td colspan="<?php echo get_user_role() === 'kaprodi' ? '7' : '8'; ?>" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada mapping mata kuliah.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($mapping_mk as $mk): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $mk->kode_mk; ?></strong></td>
                                        <td><?php echo $mk->nama_mk; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $mk->sks; ?> SKS</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $mk->semester; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($mk->jenis == 'wajib'): ?>
                                                <span class="badge badge-success">Wajib</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pilihan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $is_aktif = false;
                                            if (isset($tahun_aktif) && $tahun_aktif && isset($semester_aktif)) {
                                                $is_aktif = ($mk->semester == $semester_aktif);
                                            }
                                            ?>
                                            <?php if ($is_aktif): ?>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-check"></i> Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (get_user_role() !== 'kaprodi'): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="editMapping(<?php echo $mk->id; ?>, '<?php echo $mk->kode_mk; ?>', '<?php echo addslashes($mk->nama_mk); ?>', <?php echo $mk->sks; ?>, <?php echo $mk->semester; ?>, '<?php echo $mk->jenis; ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="<?php echo site_url('Kurikulum/delete_mapping_mk/' . $mk->id . '/' . $kurikulum->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus mapping ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Menampilkan <?php echo count($mapping_mk); ?> dari <?php echo isset($total_rows) ? $total_rows : count($mapping_mk); ?> data
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Mapping -->
<?php if (get_user_role() !== 'kaprodi'): ?>
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('Kurikulum/update_mapping_mk', array('method' => 'post')); ?>
            <div class="modal-header">
                <h5 class="modal-title">Edit Mapping Mata Kuliah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_kurikulum_detail" id="edit_id">
                <input type="hidden" name="id_kurikulum" value="<?php echo $kurikulum->id; ?>">
                
                <div class="form-group">
                    <label for="edit_kode_mk">Kode MK <span class="text-danger">*</span></label>
                    <input type="text" name="kode_mk" id="edit_kode_mk" class="form-control" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label for="edit_nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="nama_mk" id="edit_nama_mk" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_sks">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" id="edit_sks" class="form-control" min="1" max="6" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_semester">Semester <span class="text-danger">*</span></label>
                            <input type="number" name="semester" id="edit_semester" class="form-control" min="1" max="8" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_is_wajib">Jenis <span class="text-danger">*</span></label>
                            <select name="is_wajib" id="edit_is_wajib" class="form-control" required>
                                <option value="1">Wajib</option>
                                <option value="0">Pilihan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
<?php if (get_user_role() !== 'kaprodi'): ?>
document.getElementById('kode_mk').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});

function editMapping(id, kode_mk, nama_mk, sks, semester, jenis) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode_mk').value = kode_mk;
    document.getElementById('edit_nama_mk').value = nama_mk;
    document.getElementById('edit_sks').value = sks;
    document.getElementById('edit_semester').value = semester;
    document.getElementById('edit_is_wajib').value = (jenis == 'wajib' ? 1 : 0);
    $('#modalEdit').modal('show');
}

document.getElementById('edit_kode_mk').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
<?php endif; ?>
</script>

