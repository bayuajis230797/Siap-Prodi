<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Detail Nilai Bimbingan Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_mahasiswa_dosen'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo htmlspecialchars($proposal->nama_mahasiswa ?? '-'); ?></strong> (<?php echo htmlspecialchars($proposal->nim ?? '-'); ?>)</p>
                <p class="text-muted">Dinilai pada <?php echo !empty($nilai_bimbingan->updated_at) ? date('d/m/Y H:i', strtotime($nilai_bimbingan->updated_at)) : '-'; ?></p>

                <table class="table table-sm table-bordered">
                    <thead><tr><th>Item Penilaian</th><th width="12%" class="text-center">Bobot</th><th width="15%" class="text-center">Nilai</th></tr></thead>
                    <tbody>
                        <?php foreach ($rincian as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r->pertanyaan); ?></td>
                                <td class="text-center"><?php echo $r->bobot !== null ? (float) $r->bobot . '%' : '-'; ?></td>
                                <td class="text-center"><?php echo format_nilai_proposal($r->nilai); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success"><th colspan="2" class="text-right">Nilai Bimbingan</th><th class="text-center"><?php echo format_nilai_proposal($nilai_bimbingan->nilai_rata); ?></th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
