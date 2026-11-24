<?php
/**
 * Info jadwal sidang. Variabel: $jadwal_list (hasil Jadwal_proposal_model::get_by_mahasiswa)
 */
$this->load->helper('app');
?>
<?php if (!empty($jadwal_list)): ?>
    <?php foreach ($jadwal_list as $j): ?>
        <div class="card card-outline card-success mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-calendar-check"></i> Jadwal Sidang</h5></div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th width="25%">Tanggal</th>
                        <td><?php echo hari_dari_tanggal($j->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($j->tanggal_ujian)); ?></td>
                    </tr>
                    <tr>
                        <th>Waktu</th>
                        <td><?php echo $j->jam_mulai ? date('H:i', strtotime($j->jam_mulai)) : '-'; ?> - <?php echo $j->jam_selesai ? date('H:i', strtotime($j->jam_selesai)) : '-'; ?></td>
                    </tr>
                    <tr><th>Ruangan</th><td><?php echo htmlspecialchars($j->ruangan ?: '-'); ?></td></tr>
                    <tr>
                        <th>Penguji 1</th>
                        <td><?php echo !empty($j->penguji_1) ? htmlspecialchars($j->penguji_1) : '<span class="text-muted">Belum ditetapkan</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>Penguji 2</th>
                        <td><?php echo !empty($j->penguji_2) ? htmlspecialchars($j->penguji_2) : '<span class="text-muted">Belum ditetapkan</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php $cls = array('terjadwal' => 'info', 'selesai' => 'success', 'batal' => 'danger'); ?>
                            <span class="badge badge-<?php echo isset($cls[$j->status]) ? $cls[$j->status] : 'secondary'; ?>"><?php echo ucfirst($j->status); ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
