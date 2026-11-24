<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dosen Pembimbing Proposal TA</h3>
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

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Centang dosen yang boleh dipilih sekprodi sebagai <strong>pembimbing Proposal TA</strong>.
                    Setiap mahasiswa hanya memiliki <strong>satu</strong> pembimbing.
                </div>

                <?php echo form_open('Proposal_pembimbing_setting/save'); ?>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="pilih-semua">Pilih semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="kosongkan">Kosongkan</button>
                        <span class="ml-2 text-muted">Terpilih: <strong id="jumlah-terpilih"><?php echo (int) $total_dipilih; ?></strong> dari <?php echo count($dosen_list); ?> dosen aktif</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60" class="text-center">Boleh</th>
                                    <th width="50">No</th>
                                    <th>Nama Dosen</th>
                                    <th width="180">NIDN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dosen_list)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Tidak ada dosen aktif.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($dosen_list as $d): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="cek-pembimbing" name="pembimbing[]"
                                                       value="<?php echo (int) $d->id; ?>"
                                                       <?php echo !empty($d->is_pembimbing_proposal) ? 'checked' : ''; ?>>
                                            </td>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($d->nama_dosen); ?></td>
                                            <td><?php echo htmlspecialchars((string) $d->nidn); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var boxes = document.querySelectorAll('.cek-pembimbing');
    var out = document.getElementById('jumlah-terpilih');
    function hitung() {
        var n = 0;
        boxes.forEach(function (b) { if (b.checked) n++; });
        out.textContent = n;
    }
    boxes.forEach(function (b) { b.addEventListener('change', hitung); });
    document.getElementById('pilih-semua').addEventListener('click', function () {
        boxes.forEach(function (b) { b.checked = true; }); hitung();
    });
    document.getElementById('kosongkan').addEventListener('click', function () {
        boxes.forEach(function (b) { b.checked = false; }); hitung();
    });
})();
</script>
