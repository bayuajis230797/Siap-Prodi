<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Plot Pembimbing Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_plot_pembimbing'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($pengajuan->nim ?? '-'); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?? '-'); ?></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Judul Pengajuan</h5>
                        <div class="alert alert-info">
                            <p class="mb-0"><strong><?php echo htmlspecialchars($pengajuan->judul_pengajuan ?? '-'); ?></strong></p>
                        </div>
                    </div>
                </div>

                <!-- Form Plot Pembimbing -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Plot Pembimbing</h5>
                    </div>
                    <div class="card-body">
                        <?php echo form_open('Skripsi_plot_pembimbing/proses/' . $pengajuan->id); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_pembimbing_1">Pembimbing 1 <span class="text-danger">*</span></label>
                                        <select name="id_pembimbing_1" id="id_pembimbing_1" class="form-control" required>
                                            <option value="">Pilih Pembimbing 1</option>
                                            <?php foreach ($dosen_list as $dosen): ?>
                                                <option value="<?php echo $dosen->id; ?>" 
                                                        <?php $p1 = $pengajuan->id_pembimbing_1_final ?? $pengajuan->id_pembimbing_1 ?? null; echo ($p1 && $p1 == $dosen->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dosen->nama_dosen . ' - ' . $dosen->nidn); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php echo form_error('id_pembimbing_1', '<small class="text-danger">', '</small>'); ?>
                                        <small class="form-text text-muted">Pembimbing 1 wajib (review proposal). Bertugas sebagai pembimbing utama.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_pembimbing_2">Pembimbing 2 <span class="text-muted">(Opsional)</span></label>
                                        <select name="id_pembimbing_2" id="id_pembimbing_2" class="form-control">
                                            <option value="">Belum ditentukan / Input nanti</option>
                                            <?php foreach ($dosen_list as $dosen): ?>
                                                <option value="<?php echo $dosen->id; ?>" 
                                                        <?php $p2 = $pengajuan->id_pembimbing_2_final ?? $pengajuan->id_pembimbing_2 ?? null; echo ($p2 && $p2 == $dosen->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dosen->nama_dosen . ' - ' . $dosen->nidn); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php echo form_error('id_pembimbing_2', '<small class="text-danger">', '</small>'); ?>
                                        <small class="form-text text-muted">Boleh dikosongkan. Bisa diinput nanti sebelum pendaftaran verifikasi berkas.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Info:</strong> Cukup plot Pembimbing 1 untuk memulai. Pembimbing 2 dapat ditambahkan nanti sebelum pendaftaran verifikasi berkas.
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Plot Pembimbing
                                </button>
                                <a href="<?php echo site_url('Skripsi_plot_pembimbing'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pembimbing1 = document.getElementById('id_pembimbing_1');
    const pembimbing2 = document.getElementById('id_pembimbing_2');
    
    function validatePembimbing() {
        if (pembimbing1.value && pembimbing2.value && pembimbing1.value === pembimbing2.value) {
            alert('Pembimbing 1 dan Pembimbing 2 tidak boleh sama!');
            pembimbing2.value = '';
            return false;
        }
        return true;
    }
    
    pembimbing1.addEventListener('change', function() {
        validatePembimbing();
    });
    
    pembimbing2.addEventListener('change', function() {
        validatePembimbing();
    });
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!validatePembimbing()) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

