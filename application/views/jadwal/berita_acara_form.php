<?php
$jenis_label = isset($jenis_label) ? $jenis_label : 'UTS';
$action_url = isset($action_url) ? $action_url : '#';
$mahasiswa = isset($mahasiswa) ? $mahasiswa : array();
$kehadiran = isset($kehadiran) ? $kehadiran : array();
$berita_acara = isset($berita_acara) ? $berita_acara : null;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Berita Acara & Kehadiran <?php echo htmlspecialchars($jenis_label); ?></h3>
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
                    <h5><i class="fas fa-info-circle"></i> Informasi Jadwal <?php echo htmlspecialchars($jenis_label); ?></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Mata Kuliah:</strong> <?php echo $jadwal->kode_mk; ?> - <?php echo $jadwal->nama_mk; ?><br>
                            <strong>Kelas:</strong> <?php echo $jadwal->kelas; ?><br>
                            <strong>Sesi:</strong> <?php echo $jadwal->sesi; ?><br>
                            <strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?><br>
                            <strong>Ruang:</strong> <?php echo $jadwal->nama_ruang; ?><br>
                            <strong>Pengawas Saat Ini:</strong>
                            <?php if (!empty($jadwal->pengawas)): ?>
                                <?php foreach ($jadwal->pengawas as $idx => $p): ?>
                                    <?php if ($idx > 0): ?>, <?php endif; ?>
                                    <?php echo isset($p->nama_dosen) ? $p->nama_dosen : (isset($p->nama_tendik) ? $p->nama_tendik : '-'); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?php echo $action_url; ?>">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Kehadiran Mahasiswa</h5>
                            <?php if (!empty($mahasiswa)): ?>
                                <button type="button"
                                        class="btn btn-sm btn-light text-success"
                                        onclick="setHadirSemua()">
                                    <i class="fas fa-user-check"></i> Hadir Semua
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($mahasiswa)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Tidak ada mahasiswa aktif di kelas ini.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">NIM</th>
                                                <th>Nama Mahasiswa</th>
                                                <th width="20%">Status Kehadiran</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($mahasiswa as $m): 
                                                $current = isset($kehadiran[$m->id]) ? $kehadiran[$m->id]->status_kehadiran : 'hadir';
                                                $ket = isset($kehadiran[$m->id]) ? $kehadiran[$m->id]->keterangan : '';
                                            ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><strong><?php echo $m->nim; ?></strong></td>
                                                    <td><?php echo $m->nama_mahasiswa; ?></td>
                                                    <td>
                                                        <select name="kehadiran[<?php echo $m->id; ?>]" class="form-control form-control-sm" required>
                                                            <option value="hadir" <?php echo ($current == 'hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                            <option value="tidak_hadir" <?php echo ($current == 'tidak_hadir') ? 'selected' : ''; ?>>Tidak Hadir</option>
                                                            <option value="izin" <?php echo ($current == 'izin') ? 'selected' : ''; ?>>Izin</option>
                                                            <option value="sakit" <?php echo ($current == 'sakit') ? 'selected' : ''; ?>>Sakit</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="keterangan_kehadiran[<?php echo $m->id; ?>]"
                                                               class="form-control form-control-sm"
                                                               placeholder="Keterangan (opsional)"
                                                               value="<?php echo $ket; ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <?php 
                        // Tentukan URL kembali berdasarkan role
                        $back_url = isset($back_url) ? $back_url : (($jenis_label === 'UAS') ? site_url('Jadwal_uas') : site_url('Jadwal_uts'));
                        ?>
                        <a href="<?php echo $back_url; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function setHadirSemua() {
    const selects = document.querySelectorAll('select[name^="kehadiran["]');
    selects.forEach(function(sel) {
        sel.value = 'hadir';
    });
}
</script>
