<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tanda Terima Barang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tanda_terima/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Distribusi
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <form method="get" class="mb-3">
                    <div class="form-row">
                        <div class="col-md-4">
                            <select name="jenis" class="form-control">
                                <option value="">Semua jenis penerima</option>
                                <option value="mahasiswa" <?php echo ($filter_jenis === 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                                <option value="dosen" <?php echo ($filter_jenis === 'dosen') ? 'selected' : ''; ?>>Dosen</option>
                                <option value="campuran" <?php echo ($filter_jenis === 'campuran') ? 'selected' : ''; ?>>Campuran</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info">Filter</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Distribusi</th>
                                <th>Penerima</th>
                                <th>Periode</th>
                                <th>Barang</th>
                                <th>Penerima terdaftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada data.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($list as $row): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row->nama_distribusi); ?></strong></td>
                                        <td><span class="badge badge-info"><?php echo ucfirst($row->jenis_penerima); ?></span></td>
                                        <td>
                                            <?php
                                            if ($row->tanggal_mulai || $row->tanggal_selesai) {
                                                echo ($row->tanggal_mulai ? date('d/m/Y', strtotime($row->tanggal_mulai)) : '—');
                                                echo ' s/d ';
                                                echo ($row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : '—');
                                            } else {
                                                echo '<span class="text-muted">Tidak dibatasi</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center"><?php echo (int) $row->jumlah_barang; ?></td>
                                        <td class="text-center"><?php echo (int) $row->jumlah_peserta; ?></td>
                                        <td><span class="badge badge-secondary"><?php echo ucfirst($row->status); ?></span></td>
                                        <td>
                                            <a href="<?php echo site_url('Tanda_terima/detail/' . $row->id); ?>" class="btn btn-sm btn-info">Detail</a>
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
</div>
