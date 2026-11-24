<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Kurikulum: <?php echo $kurikulum->nama_kurikulum; ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kurikulum'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Nama Kurikulum:</strong> <?php echo $kurikulum->nama_kurikulum; ?></p>
                        <p><strong>Tahun Berlaku:</strong> <?php echo $kurikulum->tahun_berlaku; ?></p>
                        <p><strong>Status:</strong> 
                            <?php if ($kurikulum->is_aktif): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Tidak Aktif</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <hr>

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Cara Mengatur Mata Kuliah:</strong> Pilih mata kuliah dari <strong>Master Data Mata Kuliah</strong> untuk setiap semester (1-8). 
                    Jika mata kuliah belum ada di master, tambahkan terlebih dahulu di menu <a href="<?php echo site_url('Mata_kuliah'); ?>" target="_blank"><strong>Mata Kuliah</strong></a>.
                </div>

                <!-- Tab untuk setiap semester -->
                <ul class="nav nav-tabs" id="semesterTabs" role="tablist">
                    <?php for ($sem = 1; $sem <= 8; $sem++): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $sem == 1 ? 'active' : ''; ?>" 
                               id="sem<?php echo $sem; ?>-tab" 
                               data-toggle="tab" 
                               href="#sem<?php echo $sem; ?>" 
                               role="tab">
                                Semester <?php echo $sem; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>

                <div class="tab-content" id="semesterTabContent">
                    <?php for ($sem = 1; $sem <= 8; $sem++): ?>
                        <div class="tab-pane fade <?php echo $sem == 1 ? 'show active' : ''; ?>" 
                             id="sem<?php echo $sem; ?>" 
                             role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">Mata Kuliah Semester <?php echo $sem; ?></h4>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAddMK<?php echo $sem; ?>">
                                        <i class="fas fa-plus"></i> Tambah Mata Kuliah
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode MK</th>
                                                    <th>Nama Mata Kuliah</th>
                                                    <th>SKS</th>
                                                    <th>Jenis</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($mata_kuliah_by_semester[$sem])): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Belum ada mata kuliah di semester ini.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $no = 1; foreach ($mata_kuliah_by_semester[$sem] as $mk): ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><?php echo $mk->kode_mk; ?></td>
                                                            <td><?php echo $mk->nama_mk; ?></td>
                                                            <td><?php echo $mk->sks; ?></td>
                                                            <td><?php echo $mk->jenis ? ucfirst($mk->jenis) : '-'; ?></td>
                                                            <td>
                                                                <a href="<?php echo site_url('Kurikulum/remove_mata_kuliah/' . $mk->id . '/' . $kurikulum->id); ?>" 
                                                                   class="btn btn-danger btn-sm" 
                                                                   onclick="return confirm('Hapus mata kuliah ini dari kurikulum?')">
                                                                    <i class="fas fa-trash"></i> Hapus
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Tambah Mata Kuliah untuk Semester -->
                        <div class="modal fade" id="modalAddMK<?php echo $sem; ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <?php echo form_open('Kurikulum/add_mata_kuliah'); ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tambah Mata Kuliah - Semester <?php echo $sem; ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_kurikulum" value="<?php echo $kurikulum->id; ?>">
                                        <input type="hidden" name="semester" value="<?php echo $sem; ?>">
                                        
                                        <div class="form-group">
                                            <label for="id_mata_kuliah<?php echo $sem; ?>">Mata Kuliah <span class="text-danger">*</span></label>
                                            <select name="id_mata_kuliah" id="id_mata_kuliah<?php echo $sem; ?>" class="form-control" required>
                                                <option value="">-- Pilih Mata Kuliah --</option>
                                                <?php foreach ($mata_kuliah as $mk): ?>
                                                    <option value="<?php echo $mk->id; ?>">
                                                        <?php echo $mk->kode_mk . ' - ' . $mk->nama_mk . ' (' . $mk->sks . ' SKS)'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="jenis<?php echo $sem; ?>">Jenis</label>
                                            <select name="jenis" id="jenis<?php echo $sem; ?>" class="form-control">
                                                <option value="">-- Pilih Jenis --</option>
                                                <option value="wajib">Wajib</option>
                                                <option value="pilihan">Pilihan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap 5 tab functionality (jQuery fallback for AdminLTE)
$(document).ready(function() {
    $('#semesterTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
});
</script>

