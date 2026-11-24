<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Assign Mahasiswa ke Sesi</h3>
            </div>
            <?php echo form_open('Jadwal_uts/process_assign_sesi', array('method' => 'post')); ?>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Kelas:</strong> <?php echo $kelas; ?> | 
                    <strong>Tanggal UTS:</strong> <?php echo date('d/m/Y', strtotime($tanggal_uts)); ?> | 
                    <strong>Jumlah Sesi:</strong> <?php echo $jumlah_sesi; ?>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian:</strong> Assign mahasiswa ke Sesi 1 atau Sesi 2. Pastikan semua mahasiswa sudah di-assign ke salah satu sesi.
                </div>

                <input type="hidden" name="kelas" value="<?php echo $kelas; ?>">
                <input type="hidden" name="jumlah_sesi" value="<?php echo $jumlah_sesi; ?>">
                <input type="hidden" name="tanggal_uts" value="<?php echo $tanggal_uts; ?>">
                <input type="hidden" name="overwrite" value="<?php echo $overwrite ? '1' : '0'; ?>">

                <h5>Preview Jadwal yang akan di-generate:</h5>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> Tanggal UTS akan disesuaikan dengan hari jadwal kuliah. 
                    Jika jadwal kuliah hari Senin, maka UTS akan di hari Senin pada minggu yang sama dengan tanggal yang diinput.
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Hari Jadwal Kuliah</th>
                                <th>Jam Kuliah</th>
                                <th>Ruang</th>
                                <th>Dosen Pengampu</th>
                                <th>Sesi 1 (Jika 2 sesi)</th>
                                <th>Sesi 2 (Jika 2 sesi)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwal_kuliah as $jk): ?>
                                <?php
                                // Hitung durasi
                                $jam_mulai_timestamp = strtotime($jk->jam_mulai);
                                $jam_selesai_timestamp = strtotime($jk->jam_selesai);
                                $durasi_menit = ($jam_selesai_timestamp - $jam_mulai_timestamp) / 60;
                                $durasi_sesi_menit = $durasi_menit / 2;
                                
                                $jam_mulai_sesi1 = $jk->jam_mulai;
                                $jam_selesai_sesi1 = date('H:i', strtotime("+{$durasi_sesi_menit} minutes", $jam_mulai_timestamp));
                                $jam_mulai_sesi2 = $jam_selesai_sesi1;
                                $jam_selesai_sesi2 = date('H:i', $jam_selesai_timestamp);
                                
                                // Hitung tanggal UTS berdasarkan hari jadwal kuliah
                                $hari_map = array('minggu' => 0, 'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6);
                                $hari_target_num = isset($hari_map[strtolower($jk->hari)]) ? $hari_map[strtolower($jk->hari)] : 1;
                                $tanggal_ref = strtotime($tanggal_uts);
                                $hari_ref = date('w', $tanggal_ref);
                                $selisih = $hari_target_num - $hari_ref;
                                if ($selisih < 0) $selisih += 7;
                                $tanggal_uts_actual = date('d/m/Y', strtotime("+{$selisih} days", $tanggal_ref));
                                ?>
                                <tr>
                                    <td><strong><?php echo $jk->kode_mk; ?></strong></td>
                                    <td><?php echo $jk->nama_mk; ?></td>
                                    <td><?php echo $jk->sks; ?> SKS</td>
                                    <td>
                                        <strong><?php echo ucfirst($jk->hari); ?></strong><br>
                                        <small class="text-muted">Tanggal UTS: <?php echo $tanggal_uts_actual; ?></small>
                                    </td>
                                    <td><?php echo date('H:i', $jam_mulai_timestamp); ?> - <?php echo date('H:i', $jam_selesai_timestamp); ?></td>
                                    <td><?php echo $jk->kode_ruang; ?></td>
                                    <td><?php echo $jk->nama_dosen; ?></td>
                                    <td>
                                        <?php if ($jumlah_sesi == 2): ?>
                                            <small>
                                                <strong><?php echo $jam_mulai_sesi1; ?> - <?php echo $jam_selesai_sesi1; ?></strong><br>
                                                <span class="text-success">Pengawas: <?php echo $jk->nama_dosen; ?> (Dosen)</span>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($jumlah_sesi == 2): ?>
                                            <small>
                                                <strong><?php echo $jam_mulai_sesi2; ?> - <?php echo $jam_selesai_sesi2; ?></strong><br>
                                                <span class="text-info">Pengawas: Tendik (Random)</span>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h5>Assign Mahasiswa ke Sesi:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="20%">Sesi 1</th>
                                <th width="20%">Sesi 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Tidak ada mahasiswa di kelas ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($mahasiswa as $m): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $m->nim; ?></strong></td>
                                        <td><?php echo $m->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <input type="radio" name="mahasiswa_sesi[<?php echo $m->id; ?>]" value="1" required>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" name="mahasiswa_sesi[<?php echo $m->id; ?>]" value="2" required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Generate Jadwal UTS
                </button>
                <a href="<?php echo site_url('Jadwal_uts/generate_otomatis'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-assign mahasiswa secara merata (50-50)
    const radios = document.querySelectorAll('input[type="radio"][name^="mahasiswa_sesi"]');
    const mahasiswaIds = new Set();
    
    radios.forEach(radio => {
        const match = radio.name.match(/\[(\d+)\]/);
        if (match) {
            mahasiswaIds.add(match[1]);
        }
    });
    
    const mahasiswaArray = Array.from(mahasiswaIds);
    const total = mahasiswaArray.length;
    const sesi1Count = Math.ceil(total / 2);
    
    mahasiswaArray.forEach((id, index) => {
        const sesi = (index < sesi1Count) ? '1' : '2';
        const radio = document.querySelector(`input[type="radio"][name="mahasiswa_sesi[${id}]"][value="${sesi}"]`);
        if (radio) {
            radio.checked = true;
        }
    });
});
</script>

