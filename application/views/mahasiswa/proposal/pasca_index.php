<?php $this->load->helper('id_encode'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan Pasca Sidang Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Kirim revisi laporan kepada penguji. Setelah <strong>kedua penguji</strong> menyatakan layak klaim, Anda dapat mengajukan klaim nilai.</div>
                <?php if (empty($list)): ?>
                    <div class="alert alert-warning mb-0">Bimbingan pasca sidang tersedia setelah penguji mulai menilai sidang Anda.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead><tr><th>Topik</th><th width="14%">Tanggal Sidang</th><th width="10%" class="text-center">Status</th><th width="15%">Aktivitas Terakhir</th><th width="8%" class="text-center">Aksi</th></tr></thead>
                            <tbody>
                                <?php foreach ($list as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b->judul_topik); ?></td>
                                        <td><?php echo $b->tanggal_ujian ? date('d/m/Y', strtotime($b->tanggal_ujian)) : '-'; ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $b->status === 'open' ? 'success' : 'secondary'; ?>"><?php echo $b->status === 'open' ? 'Terbuka' : 'Ditutup'; ?></span></td>
                                        <td><?php echo $b->last_activity ? date('d/m/Y H:i', strtotime($b->last_activity)) : '-'; ?></td>
                                        <td class="text-center"><a href="<?php echo site_url('Bimbingan_pasca_proposal_mahasiswa/detail/' . encode_id($b->id)); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
