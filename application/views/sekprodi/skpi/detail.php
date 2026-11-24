<?php
$status_badge = array('draft' => 'secondary', 'aktif' => 'success', 'selesai' => 'info', 'dibatalkan' => 'danger');
?>
<div class="row mb-3">
    <div class="col-12">
        <a href="<?php echo site_url('Skpi'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="<?php echo site_url('Skpi/edit/' . $periode->id); ?>" class="btn btn-warning btn-sm">Edit Periode</a>
        <a href="<?php echo site_url('Skpi_verifikasi?id_periode=' . $periode->id); ?>" class="btn btn-info btn-sm">Verifikasi Pengajuan</a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Periode</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Nama</th><td><strong><?php echo htmlspecialchars($periode->nama_periode); ?></strong></td></tr>
                    <tr><th>Deadline</th><td>
                        <?php echo date('d/m/Y', strtotime($periode->deadline_pengumpulan)); ?>
                        <?php if ($deadline_lewat): ?><span class="badge badge-danger ml-1">Sudah lewat</span><?php endif; ?>
                    </td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge badge-<?php echo $status_badge[$periode->status] ?? 'secondary'; ?>"><?php echo ucfirst($periode->status); ?></span>
                        <select id="sel-status-skpi" class="form-control form-control-sm d-inline-block ml-2" style="width:auto">
                            <option value="draft" <?php echo $periode->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="aktif" <?php echo $periode->status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?php echo $periode->status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="dibatalkan" <?php echo $periode->status === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-simpan-status">Simpan status</button>
                    </td></tr>
                    <tr><th>Peserta</th><td><?php echo count($peserta_ids); ?> mahasiswa</td></tr>
                    <tr><th>Jenis berkas</th><td><?php echo count($jenis_list); ?> item</td></tr>
                </table>
                <?php if ($periode->deskripsi): ?><p class="text-muted small"><?php echo nl2br(htmlspecialchars($periode->deskripsi)); ?></p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header"><h3 class="card-title">Jenis Berkas Wajib Diupload</h3></div>
            <div class="card-body">
                <form method="post" action="<?php echo site_url('Skpi/tambah_jenis/' . $periode->id); ?>" class="mb-3 border-bottom pb-3">
                    <div class="form-row">
                        <div class="col-md-5">
                            <input type="text" name="nama_berkas" class="form-control form-control-sm" placeholder="Nama jenis berkas *" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="petunjuk" class="form-control form-control-sm" placeholder="Petunjuk (opsional)">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="urutan" class="form-control form-control-sm" placeholder="Urut" value="0">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </form>
                <?php if (empty($jenis_list)): ?>
                    <p class="text-muted">Belum ada jenis berkas. Contoh: Sertifikat Kompetensi, Sertifikat Prestasi, Organisasi Kampus.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead><tr><th>Urut</th><th>Nama</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($jenis_list as $j): ?>
                                <tr>
                                    <td><?php echo (int) $j->urutan; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($j->nama_berkas); ?></strong>
                                        <?php if ($j->petunjuk): ?><br><small class="text-muted"><?php echo htmlspecialchars($j->petunjuk); ?></small><?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('Skpi/hapus_jenis/' . $j->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus jenis berkas ini?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Pilih Mahasiswa Peserta SKPI (<?php echo count($peserta_ids); ?> terdaftar)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Centang mahasiswa yang berhak mengakses menu SKPI pada periode ini. Gunakan <strong>Pilih semua</strong> atau <strong>Pilih per kelas</strong>.</p>
                <form method="post" action="<?php echo site_url('Skpi/simpan_peserta/' . $periode->id); ?>">
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-mhs-pilih-semua">Pilih semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-mhs-batal-semua">Batal semua</button>
                        <select id="filter-kelas-mhs" class="form-control form-control-sm d-inline-block" style="width:auto;min-width:120px">
                            <option value="">— Semua kelas —</option>
                            <?php foreach (array_keys($mahasiswa_by_kelas) as $kls): ?>
                                <option value="<?php echo htmlspecialchars($kls); ?>"><?php echo htmlspecialchars($kls); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-info" id="btn-mhs-pilih-kelas">Pilih semua di kelas ini</button>
                    </div>
                    <div class="mb-2">
                        <input type="text" id="cari-mhs" class="form-control form-control-sm" placeholder="Cari NIM atau nama...">
                    </div>
                    <div style="max-height:360px;overflow:auto;border:1px solid #dee2e6;padding:8px">
                        <?php foreach ($mahasiswa_by_kelas as $kelas => $mhs_list): ?>
                            <div class="mb-2 kelas-group" data-kelas="<?php echo htmlspecialchars($kelas); ?>">
                                <strong class="text-primary"><?php echo htmlspecialchars($kelas); ?></strong>
                                <table class="table table-sm table-borderless mb-0">
                                    <?php foreach ($mhs_list as $m):
                                        $checked = in_array((int) $m->id, $peserta_ids, true);
                                    ?>
                                        <tr class="row-mhs" data-nim="<?php echo htmlspecialchars(strtolower($m->nim)); ?>" data-nama="<?php echo htmlspecialchars(strtolower($m->nama_mahasiswa)); ?>">
                                            <td width="30"><input type="checkbox" class="cb-mhs" name="id_mahasiswa[]" value="<?php echo (int) $m->id; ?>" <?php echo $checked ? 'checked' : ''; ?>></td>
                                            <td><code><?php echo htmlspecialchars($m->nim); ?></code> — <?php echo htmlspecialchars($m->nama_mahasiswa); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan daftar peserta</button>
                        <span class="text-muted small ml-2" id="hitung-peserta"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function updateHitung() {
        var m = document.querySelectorAll('.cb-mhs:checked').length;
        var el = document.getElementById('hitung-peserta');
        if (el) el.textContent = 'Terpilih: ' + m + ' mahasiswa';
    }
    document.querySelectorAll('.cb-mhs').forEach(function(cb) { cb.addEventListener('change', updateHitung); });
    updateHitung();

    var btnAll = document.getElementById('btn-mhs-pilih-semua');
    if (btnAll) btnAll.onclick = function() {
        document.querySelectorAll('.row-mhs').forEach(function(row) {
            if (row.style.display !== 'none') row.querySelector('.cb-mhs').checked = true;
        });
        updateHitung();
    };
    var btnNone = document.getElementById('btn-mhs-batal-semua');
    if (btnNone) btnNone.onclick = function() {
        document.querySelectorAll('.cb-mhs').forEach(function(cb) { cb.checked = false; });
        updateHitung();
    };
    var btnKelas = document.getElementById('btn-mhs-pilih-kelas');
    if (btnKelas) btnKelas.onclick = function() {
        var k = document.getElementById('filter-kelas-mhs').value;
        if (!k) { alert('Pilih kelas terlebih dahulu.'); return; }
        document.querySelectorAll('.kelas-group').forEach(function(g) {
            if (g.getAttribute('data-kelas') === k) {
                g.querySelectorAll('.cb-mhs').forEach(function(cb) {
                    if (cb.closest('.row-mhs').style.display !== 'none') cb.checked = true;
                });
            }
        });
        updateHitung();
    };
    var cari = document.getElementById('cari-mhs');
    if (cari) cari.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.row-mhs').forEach(function(row) {
            var show = !q || row.getAttribute('data-nim').indexOf(q) >= 0 || row.getAttribute('data-nama').indexOf(q) >= 0;
            row.style.display = show ? '' : 'none';
        });
    });

    var btnStatus = document.getElementById('btn-simpan-status');
    if (btnStatus) btnStatus.onclick = function() {
        var st = document.getElementById('sel-status-skpi').value;
        var fd = new FormData();
        fd.append('status', st);
        if (typeof window.appendSiapCsrf === 'function') {
            window.appendSiapCsrf(fd);
        } else {
            var csrfName = document.querySelector('meta[name=csrf-token-name]');
            var csrfHash = document.querySelector('meta[name=csrf-token]');
            if (csrfName && csrfHash) {
                fd.append(csrfName.content, csrfHash.content);
            }
        }
        fetch('<?php echo site_url('Skpi/update_status/' . $periode->id); ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                alert(d.message || (d.success ? 'OK' : 'Gagal'));
                if (d.success) location.reload();
            });
    };
})();
</script>
