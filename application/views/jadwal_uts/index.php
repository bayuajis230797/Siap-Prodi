<?php
$jadwal_list = isset($jadwal_list) && is_array($jadwal_list) ? $jadwal_list : array();
$kelas_options = isset($kelas_options) && is_array($kelas_options) ? $kelas_options : array();
$filter_tanggal = isset($filter_tanggal) ? $filter_tanggal : '';
$filter_kelas = isset($filter_kelas) ? $filter_kelas : '';
$per_page = isset($per_page) ? $per_page : 10;
$total_rows = isset($total_rows) ? $total_rows : 0;
$bentrok_map = isset($bentrok_map) && is_array($bentrok_map) ? $bentrok_map : array();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check me-1"></i> Jadwal Ujian Tengah Semester
                </h3>
                <div class="card-tools">
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Jadwal_uts/generate_otomatis'); ?>" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-magic"></i> Generate Otomatis
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo site_url('Laporan/export_jadwal_uts'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-export"></i> Export CSV
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

                <?php if (!empty($tahun_aktif)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik Aktif:</strong>
                        <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada tahun akademik yang aktif. Aktifkan terlebih dahulu untuk melihat data jadwal.
                    </div>
                <?php endif; ?>

                <form method="get" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="filter_tanggal">Tanggal Ujian</label>
                            <input type="date" name="tanggal" id="filter_tanggal" class="form-control"
                                   value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="filter_kelas">Kelas</label>
                            <select name="kelas" id="filter_kelas" class="form-control">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelas_options as $kelas): ?>
                                    <option value="<?php echo $kelas; ?>" <?php echo ($filter_kelas === $kelas) ? 'selected' : ''; ?>>
                                        <?php echo $kelas; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="filter_per_page">Tampilkan</label>
                            <select name="per_page" id="filter_per_page" class="form-control">
                                <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10 data</option>
                                <option value="25" <?php echo ($per_page == 25) ? 'selected' : ''; ?>>25 data</option>
                                <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50 data</option>
                                <option value="100" <?php echo ($per_page == 100) ? 'selected' : ''; ?>>100 data</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                            <a href="<?php echo site_url('Jadwal_uts'); ?>" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Total Data: <?php echo number_format($total_rows); ?></strong>
                    <small class="text-muted">Klik baris untuk melihat detail singkat.</small>
                </div>

                <div class="table-responsive">
                    <style>
                        #table-jadwal-uts tbody tr.bentrok-pengawas,
                        #table-jadwal-uts tbody tr.bentrok-pengawas:hover {
                            background-color: #f8d7da !important;
                        }
                    </style>
                    <table id="table-jadwal-uts" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Tanggal</th>
                                <th width="8%" class="text-center">Hari</th>
                                <th width="12%">Jam</th>
                                <th>Mata Kuliah</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="8%" class="text-center">Sesi</th>
                                <th width="12%">Dosen Pengampu</th>
                                <th width="14%">Pengawas</th>
                                <th width="10%">Ruang</th>
                                <th width="12%">Keterangan</th>
                                <th width="18%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal UTS yang tercatat.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 1;
                                $page = $page < 1 ? 1 : $page;
                                $no = ($page - 1) * $per_page + 1;
                                ?>
                                <?php foreach ($jadwal_list as $jadwal): ?>
                                    <?php
                                    $bentrok_info = isset($bentrok_map[(int) $jadwal->id]) ? $bentrok_map[(int) $jadwal->id] : array('bentrok' => false, 'keterangan_str' => '');
                                    $is_bentrok = !empty($bentrok_info['bentrok']);
                                    ?>
                                    <tr class="<?php echo $is_bentrok ? 'table-danger bentrok-pengawas' : ''; ?>"
                                        <?php if ($is_bentrok): ?>
                                            title="<?php echo htmlspecialchars($bentrok_info['keterangan_str']); ?>"
                                        <?php endif; ?>>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php echo $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-'; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo hari_dari_tanggal($jadwal->tanggal ?? null); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $mulai = $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-';
                                            $selesai = $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-';
                                            echo $mulai . ' - ' . $selesai;
                                            ?>
                                        </td>
                                        <td>
                                            <strong><?php echo $jadwal->kode_mk ?? '-'; ?></strong><br>
                                            <span class="text-muted"><?php echo $jadwal->nama_mk ?? '-'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary"><?php echo $jadwal->kelas ?? '-'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?php echo ($jadwal->sesi == 2) ? 'warning' : 'info'; ?>">
                                                S<?php echo $jadwal->sesi ?? '-'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($jadwal->nama_dosen)): ?>
                                                <strong><?php echo $jadwal->nama_dosen; ?></strong><br>
                                                <small class="text-muted">NIDN: <?php echo $jadwal->nidn; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (!empty($jadwal->pengawas)) {
                                                foreach ($jadwal->pengawas as $pengawas) {
                                                    $nama_pengawas = jadwal_ujian_pengawas_display_name($pengawas);
                                                    if ($nama_pengawas !== '') {
                                                        echo '<span class="badge badge-secondary d-block mb-1">' . htmlspecialchars($nama_pengawas) . '</span>';
                                                    }
                                                }
                                            } else {
                                                echo '<span class="text-muted">Belum ditetapkan</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (!empty($jadwal->nama_ruang)) {
                                                echo htmlspecialchars($jadwal->nama_ruang);
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($is_bentrok): ?>
                                                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Bentrok</span>
                                                <small class="d-block text-danger mt-1"><?php echo htmlspecialchars($bentrok_info['keterangan_str']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if (get_user_role() !== 'kaprodi'): ?>
                                                <a href="<?php echo site_url('Jadwal_uts/edit/' . $jadwal->id); ?>"
                                                   class="btn btn-outline-primary"
                                                   title="Edit Jadwal">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('Jadwal_uts/berita_acara/' . $jadwal->id); ?>"
                                                   class="btn btn-outline-success"
                                                   title="Input Daftar Hadir">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?php echo site_url('Jadwal_uts/cetak_daftar_hadir/' . $jadwal->id); ?>"
                                                   class="btn btn-info"
                                                   title="Cetak Daftar Hadir"
                                                   target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <?php if (get_user_role() !== 'kaprodi'): ?>
                                                <a href="<?php echo site_url('Jadwal_uts/ganti_pengawas/' . $jadwal->id); ?>"
                                                   class="btn btn-outline-warning"
                                                   title="Ganti Pengawas">
                                                    <i class="fas fa-user-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                <?php if (in_array(get_user_role(), array('sekprodi', 'admin'))): ?>
                                                <a href="<?php echo site_url('Jadwal_uts/delete/' . $jadwal->id); ?>"
                                                   class="btn btn-outline-danger"
                                                   title="Hapus Jadwal"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">Menampilkan <?php echo count($jadwal_list); ?> data pada halaman ini.</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

