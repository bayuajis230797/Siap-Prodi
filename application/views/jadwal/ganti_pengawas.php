<?php
$is_sesi_1 = isset($is_sesi_1) ? $is_sesi_1 : true;
$action_url = isset($action_url) ? $action_url : '#';
$dosen_list = isset($dosen_list) ? $dosen_list : array();
$tendik_list = isset($tendik_list) ? $tendik_list : array();
$selected_id = isset($selected_id) ? $selected_id : null;
$pengawas_sekarang_type = isset($pengawas_sekarang_type) ? $pengawas_sekarang_type : '';
$pengawas_sekarang_id = isset($pengawas_sekarang_id) ? $pengawas_sekarang_id : null;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ganti Pengawas <?php echo $is_sesi_1 ? 'Sesi 1' : 'Sesi 2'; ?></h3>
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

                <div class="alert alert-info">
                    <strong>Mata Kuliah:</strong> <?php echo $jadwal->kode_mk; ?> - <?php echo $jadwal->nama_mk; ?><br>
                    <strong>Kelas:</strong> <?php echo $jadwal->kelas; ?> | <strong>Sesi:</strong> <?php echo $jadwal->sesi; ?><br>
                    <strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?> | 
                    <strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?>
                </div>

                <form method="post" action="<?php echo $action_url; ?>" id="form-pengawas">
                    <div class="form-group">
                        <label for="pengawas_type">Tipe Pengawas <span class="text-danger">*</span></label>
                        <select name="pengawas_type" id="pengawas_type" class="form-control" required>
                            <option value="">-- Pilih Tipe Pengawas --</option>
                            <option value="dosen" <?php echo ($pengawas_sekarang_type == 'dosen') ? 'selected' : ''; ?>>Dosen</option>
                            <option value="tendik" <?php echo ($pengawas_sekarang_type == 'tendik') ? 'selected' : ''; ?>>Tendik</option>
                        </select>
                        <small class="form-text text-muted">Pilih tipe pengawas (Dosen atau Tendik). Dosen dapat menggantikan tendik jika tendik berhalangan mengawasi.</small>
                    </div>

                    <div class="form-group" id="dosen-group" style="display: <?php echo ($pengawas_sekarang_type == 'dosen') ? 'block' : 'none'; ?>;">
                        <label for="pengawas_id_dosen">Pilih Dosen <span class="text-danger">*</span></label>
                        <select id="pengawas_id_dosen" class="form-control" <?php echo ($pengawas_sekarang_type == 'dosen') ? 'name="pengawas_id"' : ''; ?>>
                            <option value="">-- Pilih Dosen --</option>
                            <?php foreach ($dosen_list as $d): ?>
                                <option value="<?php echo $d->id; ?>" <?php echo ($pengawas_sekarang_type == 'dosen' && $pengawas_sekarang_id == $d->id) ? 'selected' : ''; ?>>
                                    <?php echo $d->nama_dosen; ?> (NIDN: <?php echo $d->nidn; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="tendik-group" style="display: <?php echo ($pengawas_sekarang_type == 'tendik') ? 'block' : 'none'; ?>;">
                        <label for="pengawas_id_tendik">Pilih Tendik <span class="text-danger">*</span></label>
                        <select id="pengawas_id_tendik" class="form-control" <?php echo ($pengawas_sekarang_type == 'tendik') ? 'name="pengawas_id"' : ''; ?>>
                            <option value="">-- Pilih Tendik --</option>
                            <?php foreach ($tendik_list as $t): ?>
                                <option value="<?php echo $t->id; ?>" <?php echo ($pengawas_sekarang_type == 'tendik' && $pengawas_sekarang_id == $t->id) ? 'selected' : ''; ?>>
                                    <?php echo $t->nama_tendik; ?> (NIPY: <?php echo $t->nipy; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?php echo ($jenis ?? 'UTS') === 'UAS' ? site_url('Jadwal_uas') : site_url('Jadwal_uts'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var pengawasType = document.getElementById('pengawas_type');
                        var dosenGroup = document.getElementById('dosen-group');
                        var tendikGroup = document.getElementById('tendik-group');
                        var pengawasIdDosen = document.getElementById('pengawas_id_dosen');
                        var pengawasIdTendik = document.getElementById('pengawas_id_tendik');
                        var formPengawas = document.getElementById('form-pengawas');
                        
                        function updatePengawasFields() {
                            var type = pengawasType.value;
                            
                            if (type == 'dosen') {
                                dosenGroup.style.display = 'block';
                                tendikGroup.style.display = 'none';
                                pengawasIdDosen.required = true;
                                pengawasIdTendik.required = false;
                                pengawasIdTendik.value = '';
                                // Hapus name dari tendik agar tidak ikut terkirim
                                pengawasIdTendik.removeAttribute('name');
                                pengawasIdDosen.setAttribute('name', 'pengawas_id');
                            } else if (type == 'tendik') {
                                dosenGroup.style.display = 'none';
                                tendikGroup.style.display = 'block';
                                pengawasIdDosen.required = false;
                                pengawasIdTendik.required = true;
                                pengawasIdDosen.value = '';
                                // Hapus name dari dosen agar tidak ikut terkirim
                                pengawasIdDosen.removeAttribute('name');
                                pengawasIdTendik.setAttribute('name', 'pengawas_id');
                            } else {
                                dosenGroup.style.display = 'none';
                                tendikGroup.style.display = 'none';
                                pengawasIdDosen.required = false;
                                pengawasIdTendik.required = false;
                                pengawasIdDosen.removeAttribute('name');
                                pengawasIdTendik.removeAttribute('name');
                            }
                        }
                        
                        // Inisialisasi saat halaman dimuat
                        updatePengawasFields();
                        
                        // Event listener untuk perubahan tipe pengawas
                        pengawasType.addEventListener('change', updatePengawasFields);
                        
                        // Validasi form sebelum submit
                        formPengawas.addEventListener('submit', function(e) {
                            var type = pengawasType.value;
                            var id = '';
                            
                            if (type == 'dosen') {
                                id = pengawasIdDosen.value;
                            } else if (type == 'tendik') {
                                id = pengawasIdTendik.value;
                            }
                            
                            if (!type || !id) {
                                e.preventDefault();
                                alert('Silakan pilih tipe pengawas dan pengawas yang akan ditugaskan.');
                                return false;
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>


