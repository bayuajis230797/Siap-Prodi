<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Aspek Penilaian Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_aspek_penilaian/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Aspek Penilaian
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <form method="get" action="<?php echo site_url('Proposal_aspek_penilaian/index'); ?>" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="is_aktif" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1" <?php echo ($is_aktif_filter === '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($is_aktif_filter === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="ditunjukkan_untuk" class="form-control">
                                <option value="">Semua Penilai</option>
                                <option value="pembimbing" <?php echo ($ditunjukkan_filter === 'pembimbing') ? 'selected' : ''; ?>>Pembimbing</option>
                                <option value="penguji" <?php echo ($ditunjukkan_filter === 'penguji') ? 'selected' : ''; ?>>Penguji</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Proposal_aspek_penilaian/index'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode</th>
                                <th>Nama Aspek</th>
                                <th width="12%">Total Bobot Item</th>
                                <th width="18%">Ditunjukkan Untuk</th>
                                <th width="10%">Item</th>
                                <th width="8%">Status</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($aspek_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data aspek penilaian.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($aspek_list as $aspek): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars(isset($aspek->kode_aspek) ? $aspek->kode_aspek : (isset($aspek->kode) ? $aspek->kode : '-')); ?></strong></td>
                                        <td><?php echo htmlspecialchars(isset($aspek->nama_aspek) ? $aspek->nama_aspek : (isset($aspek->nama) ? $aspek->nama : '-')); ?></td>
                                        <td>
                                            <?php 
                                            $total_bobot = isset($aspek->total_bobot) ? $aspek->total_bobot : 0;
                                            echo number_format($total_bobot, 2); ?>%
                                        </td>
                                        <td>
                                            <?php
                                            $label_map = array(
                                                'pembimbing' => array('label' => 'Pembimbing', 'class' => 'info'),
                                                'penguji' => array('label' => 'Penguji', 'class' => 'success')
                                            );
                                            $ditunjukkan_untuk = isset($aspek->ditunjukkan_untuk) ? $aspek->ditunjukkan_untuk : (isset($aspek->tipe_penilai) ? $aspek->tipe_penilai : '');
                                            $mapped = isset($label_map[$ditunjukkan_untuk]) ? $label_map[$ditunjukkan_untuk] : array('label' => ucfirst($ditunjukkan_untuk), 'class' => 'secondary');
                                            ?>
                                            <span class="badge badge-<?php echo $mapped['class']; ?>">
                                                <?php echo $mapped['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo isset($aspek->jumlah_item) ? $aspek->jumlah_item : 0; ?> item</span>
                                        </td>
                                        <td>
                                            <?php 
                                            $is_aktif = isset($aspek->is_aktif) ? $aspek->is_aktif : (isset($aspek->aktif) ? $aspek->aktif : 0);
                                            ?>
                                            <?php if ($is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Proposal_item_penilaian/index?id_aspek_penilaian=' . $aspek->id); ?>" 
                                               class="btn btn-info btn-sm" 
                                               title="Lihat Item">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            <a href="<?php echo site_url('Proposal_aspek_penilaian/edit/' . $aspek->id); ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php echo form_open('Proposal_aspek_penilaian/delete/' . $aspek->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('Hapus data ini?');")); ?><button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button><?php echo form_close(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($total_rows)): ?>
                    <p class="text-muted mt-3">
                        Total: <?php echo $total_rows; ?> aspek penilaian
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

