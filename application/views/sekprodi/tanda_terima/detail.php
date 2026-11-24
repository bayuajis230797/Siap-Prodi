<?php
$status_labels = array(
    'draft' => 'secondary', 'aktif' => 'success', 'selesai' => 'info', 'dibatalkan' => 'danger',
);
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo htmlspecialchars($tt->nama_distribusi); ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tanda_terima/cetak_rekap/' . $tt->id); ?>" class="btn btn-danger btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> Cetak PDF</a>
                    <a href="<?php echo site_url('Tanda_terima/export_csv/' . $tt->id); ?>" class="btn btn-success btn-sm"><i class="fas fa-file-csv"></i> Export CSV</a>
                    <a href="<?php echo site_url('Tanda_terima'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <p>
                    <strong>Penerima:</strong>
                    <?php
                    $jenis_label = array('mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'campuran' => 'Mahasiswa + Dosen');
                    echo $jenis_label[$tt->jenis_penerima] ?? ucfirst($tt->jenis_penerima);
                    ?> &nbsp;
                    <strong>Status:</strong>
                    <span class="badge badge-<?php echo $status_labels[$tt->status] ?? 'secondary'; ?>"><?php echo ucfirst($tt->status); ?></span>
                </p>
                <div class="mb-3">
                    <label>Ubah status:</label>
                    <select id="select_status" class="form-control form-control-sm d-inline-block" style="width:160px">
                        <?php foreach (array('draft','aktif','selesai','dibatalkan') as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $tt->status === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-simpan-status" data-id="<?php echo $tt->id; ?>">Simpan</button>
                </div>
                <?php if ($tt->deskripsi): ?><p class="text-muted"><?php echo nl2br(htmlspecialchars($tt->deskripsi)); ?></p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Daftar Barang & QR</h3></div>
            <div class="card-body">
                <form method="post" action="<?php echo site_url('Tanda_terima/tambah_barang/' . $tt->id); ?>" class="mb-3">
                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Barang + QR</button>
                </form>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Barang</th><th>QR</th><th></th></tr></thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="3" class="text-muted text-center">Belum ada barang.</td></tr>
                        <?php else: foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($it->nama_barang); ?></strong>
                                    <?php if ($it->keterangan): ?><br><small class="text-muted"><?php echo htmlspecialchars($it->keterangan); ?></small><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($it->qr_code): ?>
                                        <?php $this->load->helper('qrcode'); ?>
                                        <img src="<?php echo qr_code_serve_url_tanda_terima_item($it->id); ?>" alt="QR" style="max-width:80px">
                                    <?php endif; ?>
                                    <br><a href="<?php echo site_url('Tanda_terima/cetak_qr/' . $it->id); ?>" target="_blank" class="btn btn-xs btn-outline-secondary mt-1">Cetak</a>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('Tanda_terima/hapus_barang/' . $it->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus barang ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Pilih Penerima Barang (<?php echo count($peserta); ?> terdaftar)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Centang hanya mahasiswa/dosen yang berhak mengambil barang pada distribusi ini. Gunakan <strong>Pilih semua</strong> atau <strong>Pilih per kelas</strong> untuk mempercepat.
                </p>
                <form method="post" action="<?php echo site_url('Tanda_terima/simpan_peserta/' . $tt->id); ?>" id="form-peserta-tt">
                    <?php if (!empty($show_mhs)): ?>
                        <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-user-graduate"></i> Mahasiswa</h5>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-mhs-pilih-semua">Pilih semua mahasiswa</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-mhs-batal-semua">Batal semua</button>
                            <span class="ml-2 text-muted small">Filter kelas:</span>
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
                        <div style="max-height:320px;overflow:auto;border:1px solid #dee2e6;padding:8px">
                            <?php foreach ($mahasiswa_by_kelas as $kelas => $mhs_list): ?>
                                <div class="mb-2 kelas-group" data-kelas="<?php echo htmlspecialchars($kelas); ?>">
                                    <strong class="text-primary"><?php echo htmlspecialchars($kelas); ?></strong>
                                    <table class="table table-sm table-borderless mb-0">
                                        <?php foreach ($mhs_list as $m):
                                            $checked = in_array((int) $m->id, $peserta_mhs_ids, true);
                                        ?>
                                            <tr class="row-mhs" data-nim="<?php echo htmlspecialchars(strtolower($m->nim)); ?>" data-nama="<?php echo htmlspecialchars(strtolower($m->nama_mahasiswa)); ?>">
                                                <td width="30">
                                                    <input type="checkbox" class="cb-mhs" name="id_mahasiswa[]" value="<?php echo (int) $m->id; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                                </td>
                                                <td><code><?php echo htmlspecialchars($m->nim); ?></code> — <?php echo htmlspecialchars($m->nama_mahasiswa); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($show_dosen)): ?>
                        <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-chalkboard-teacher"></i> Dosen</h5>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-dosen-pilih-semua">Pilih semua dosen</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-dosen-batal-semua">Batal semua</button>
                        </div>
                        <div class="mb-2">
                            <input type="text" id="cari-dosen" class="form-control form-control-sm" placeholder="Cari NIDN atau nama dosen...">
                        </div>
                        <div style="max-height:240px;overflow:auto;border:1px solid #dee2e6;padding:8px">
                            <table class="table table-sm table-borderless mb-0">
                                <?php foreach ($dosen_list as $d):
                                    $checked = in_array((int) $d->id, $peserta_dosen_ids, true);
                                ?>
                                    <tr class="row-dosen" data-nidn="<?php echo htmlspecialchars(strtolower($d->nidn ?? '')); ?>" data-nama="<?php echo htmlspecialchars(strtolower($d->nama_dosen)); ?>">
                                        <td width="30">
                                            <input type="checkbox" class="cb-dosen" name="id_dosen[]" value="<?php echo (int) $d->id; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($d->nidn ?? '-'); ?></code> — <?php echo htmlspecialchars($d->nama_dosen); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan daftar penerima</button>
                        <span class="text-muted small ml-2" id="hitung-peserta"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap Penerimaan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tanda_terima/cetak_rekap/' . $tt->id); ?>" class="btn btn-danger btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> Cetak PDF</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <p class="text-muted small mb-2">
                    Jika penerima lupa scan barcode, gunakan tombol <strong>Catat</strong> untuk mengisi pengambilan manual.
                    Tombol <strong>Batal</strong> untuk menghapus catatan jika salah.
                </p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <?php if ($tt->jenis_penerima === 'campuran'): ?>
                                <th rowspan="2" class="align-middle">Jenis</th>
                            <?php endif; ?>
                            <th rowspan="2" class="align-middle"><?php echo $tt->jenis_penerima === 'dosen' ? 'NIDN' : ($tt->jenis_penerima === 'campuran' ? 'NIM/NIDN' : 'NIM'); ?></th>
                            <th rowspan="2" class="align-middle">Nama</th>
                            <?php if ($tt->jenis_penerima !== 'dosen'): ?>
                                <th rowspan="2" class="align-middle">Kelas</th>
                            <?php endif; ?>
                            <?php foreach ($rekap['items'] as $it): ?>
                                <th colspan="4" class="text-center bg-light"><?php echo htmlspecialchars($it->nama_barang); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($rekap['items'] as $it): ?>
                                <th class="text-center" style="width:72px">Status</th>
                                <th class="text-center" style="width:95px">Tanggal</th>
                                <th class="text-center" style="width:70px">Jam</th>
                                <th class="text-center" style="width:88px">Aksi</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekap['peserta'])): ?>
                            <tr>
                                <td colspan="20" class="text-center text-muted">Belum ada penerima terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($rekap['peserta'] as $p):
                            $tipe_p = isset($p->tipe_penerima) ? $p->tipe_penerima : ($tt->jenis_penerima === 'dosen' ? 'dosen' : 'mahasiswa');
                        ?>
                            <tr>
                                <?php if ($tt->jenis_penerima === 'campuran'): ?>
                                    <td><span class="badge badge-info"><?php echo ucfirst($tipe_p); ?></span></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($p->kode); ?></td>
                                <td><?php echo htmlspecialchars($p->nama); ?></td>
                                <?php if ($tt->jenis_penerima !== 'dosen'): ?><td><?php echo htmlspecialchars($p->kelas ?? ''); ?></td><?php endif; ?>
                                <?php foreach ($rekap['items'] as $it):
                                    $ref_key = isset($p->ref_key) ? $p->ref_key : ((isset($p->tipe_penerima) && $p->tipe_penerima === 'dosen') ? 'd' : 'm') . (int) $p->ref_id;
                                    $key = (int) $it->id . '_' . $ref_key;
                                    $terima = isset($rekap['terima_map'][$key]) ? $rekap['terima_map'][$key] : null;
                                    $sudah = $terima && !empty($terima->waktu_terima);
                                ?>
                                    <td class="text-center">
                                        <?php if ($sudah): ?>
                                            <span class="badge badge-success">Sudah</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $sudah ? date('d/m/Y', strtotime($terima->waktu_terima)) : '<span class="text-muted">—</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $sudah ? date('H:i', strtotime($terima->waktu_terima)) . ' WIB' : '<span class="text-muted">—</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sudah): ?>
                                            <button type="button"
                                                class="btn btn-xs btn-outline-danger btn-batal-manual"
                                                data-item="<?php echo (int) $it->id; ?>"
                                                data-tipe="<?php echo htmlspecialchars($tipe_p); ?>"
                                                data-ref="<?php echo (int) $p->ref_id; ?>"
                                                data-nama="<?php echo htmlspecialchars($p->nama); ?>"
                                                data-barang="<?php echo htmlspecialchars($it->nama_barang); ?>">
                                                Batal
                                            </button>
                                        <?php else: ?>
                                            <button type="button"
                                                class="btn btn-xs btn-primary btn-catat-manual"
                                                data-item="<?php echo (int) $it->id; ?>"
                                                data-tipe="<?php echo htmlspecialchars($tipe_p); ?>"
                                                data-ref="<?php echo (int) $p->ref_id; ?>"
                                                data-nama="<?php echo htmlspecialchars($p->nama); ?>"
                                                data-barang="<?php echo htmlspecialchars($it->nama_barang); ?>">
                                                Catat
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCatatManual" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Pengambilan Manual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" id="catat-manual-info"></p>
                <div class="form-group mb-0">
                    <label for="input-waktu-manual">Waktu terima</label>
                    <input type="datetime-local" class="form-control" id="input-waktu-manual">
                    <small class="text-muted">Kosongkan untuk memakai waktu sekarang.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-catat-manual">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function updateHitung() {
        var m = document.querySelectorAll('.cb-mhs:checked').length;
        var d = document.querySelectorAll('.cb-dosen:checked').length;
        var el = document.getElementById('hitung-peserta');
        if (el) el.textContent = 'Terpilih: ' + m + ' mahasiswa, ' + d + ' dosen';
    }
    document.querySelectorAll('.cb-mhs, .cb-dosen').forEach(function(cb) {
        cb.addEventListener('change', updateHitung);
    });
    updateHitung();

    var btnMhsAll = document.getElementById('btn-mhs-pilih-semua');
    var btnMhsNone = document.getElementById('btn-mhs-batal-semua');
    if (btnMhsAll) btnMhsAll.onclick = function() {
        document.querySelectorAll('.row-mhs').forEach(function(row) {
            if (row.style.display !== 'none') row.querySelector('.cb-mhs').checked = true;
        });
        updateHitung();
    };
    if (btnMhsNone) btnMhsNone.onclick = function() {
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

    var cariMhs = document.getElementById('cari-mhs');
    if (cariMhs) cariMhs.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.row-mhs').forEach(function(row) {
            var show = !q || row.getAttribute('data-nim').indexOf(q) >= 0 || row.getAttribute('data-nama').indexOf(q) >= 0;
            row.style.display = show ? '' : 'none';
        });
    });

    var btnDosenAll = document.getElementById('btn-dosen-pilih-semua');
    var btnDosenNone = document.getElementById('btn-dosen-batal-semua');
    if (btnDosenAll) btnDosenAll.onclick = function() {
        document.querySelectorAll('.row-dosen').forEach(function(row) {
            if (row.style.display !== 'none') row.querySelector('.cb-dosen').checked = true;
        });
        updateHitung();
    };
    if (btnDosenNone) btnDosenNone.onclick = function() {
        document.querySelectorAll('.cb-dosen').forEach(function(cb) { cb.checked = false; });
        updateHitung();
    };

    var cariDosen = document.getElementById('cari-dosen');
    if (cariDosen) cariDosen.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.row-dosen').forEach(function(row) {
            var show = !q || row.getAttribute('data-nidn').indexOf(q) >= 0 || row.getAttribute('data-nama').indexOf(q) >= 0;
            row.style.display = show ? '' : 'none';
        });
    });
})();

(function() {
    var btn = document.getElementById('btn-simpan-status');
    if (!btn) return;

    function syncCsrfFromCookie() {
        var cookieName = 'csrf_siapprodi_cookie';
        var parts = document.cookie ? document.cookie.split(';') : [];
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].trim();
            if (part.indexOf(cookieName + '=') === 0) {
                var newHash = decodeURIComponent(part.substring(cookieName.length + 1));
                if (newHash) {
                    var meta = document.querySelector('meta[name=csrf-token]');
                    if (meta) meta.setAttribute('content', newHash);
                }
                break;
            }
        }
    }

    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var status = document.getElementById('select_status').value;
        syncCsrfFromCookie();
        var nameEl = document.querySelector('meta[name=csrf-token-name]');
        var hashEl = document.querySelector('meta[name=csrf-token]');
        var body = 'status=' + encodeURIComponent(status);
        if (nameEl && hashEl && nameEl.content && hashEl.content) {
            body += '&' + encodeURIComponent(nameEl.content) + '=' + encodeURIComponent(hashEl.content);
        }
        btn.disabled = true;
        fetch('<?php echo site_url('Tanda_terima/update_status'); ?>/' + id, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function(r) {
            return r.text().then(function(t) {
                syncCsrfFromCookie();
                var d;
                try { d = JSON.parse(t); } catch (e) {
                    throw new Error(r.status === 403 ? 'Sesi keamanan kedaluwarsa. Muat ulang halaman.' : 'Respons server tidak valid.');
                }
                return d;
            });
        }).then(function(d) {
            alert(d.message || (d.success ? 'Berhasil' : 'Gagal'));
            if (d.success) location.reload();
            else btn.disabled = false;
        }).catch(function(err) {
            alert((err && err.message) ? err.message : 'Gagal mengubah status.');
            btn.disabled = false;
        });
    });
})();

(function() {
    var urlCatat = <?php echo json_encode(site_url('Tanda_terima/catat_manual/' . $tt->id)); ?>;
    var urlBatal = <?php echo json_encode(site_url('Tanda_terima/batal_manual/' . $tt->id)); ?>;
    var pending = null;

    function syncCsrfFromCookie() {
        var cookieName = 'csrf_siapprodi_cookie';
        var parts = document.cookie ? document.cookie.split(';') : [];
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].trim();
            if (part.indexOf(cookieName + '=') === 0) {
                var newHash = decodeURIComponent(part.substring(cookieName.length + 1));
                if (newHash) {
                    var meta = document.querySelector('meta[name=csrf-token]');
                    if (meta) meta.setAttribute('content', newHash);
                }
                break;
            }
        }
    }

    function postForm(url, fields) {
        syncCsrfFromCookie();
        var nameEl = document.querySelector('meta[name=csrf-token-name]');
        var hashEl = document.querySelector('meta[name=csrf-token]');
        var body = [];
        Object.keys(fields).forEach(function(k) {
            body.push(encodeURIComponent(k) + '=' + encodeURIComponent(fields[k]));
        });
        if (nameEl && hashEl && nameEl.content && hashEl.content) {
            body.push(encodeURIComponent(nameEl.content) + '=' + encodeURIComponent(hashEl.content));
        }
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.join('&')
        }).then(function(r) {
            return r.text().then(function(t) {
                syncCsrfFromCookie();
                var d;
                try { d = JSON.parse(t); } catch (e) {
                    throw new Error(r.status === 403 ? 'Sesi keamanan kedaluwarsa. Muat ulang halaman.' : 'Respons server tidak valid.');
                }
                return d;
            });
        });
    }

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function nowLocalInput() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    document.querySelectorAll('.btn-catat-manual').forEach(function(btn) {
        btn.addEventListener('click', function() {
            pending = {
                id_item: this.getAttribute('data-item'),
                tipe: this.getAttribute('data-tipe'),
                id_ref: this.getAttribute('data-ref')
            };
            document.getElementById('catat-manual-info').textContent =
                this.getAttribute('data-nama') + ' — ' + this.getAttribute('data-barang');
            document.getElementById('input-waktu-manual').value = nowLocalInput();
            $('#modalCatatManual').modal('show');
        });
    });

    var btnSimpan = document.getElementById('btn-simpan-catat-manual');
    if (btnSimpan) {
        btnSimpan.addEventListener('click', function() {
            if (!pending) return;
            var waktu = document.getElementById('input-waktu-manual').value || '';
            btnSimpan.disabled = true;
            postForm(urlCatat, {
                id_item: pending.id_item,
                tipe: pending.tipe,
                id_ref: pending.id_ref,
                waktu_terima: waktu
            }).then(function(d) {
                alert(d.message || (d.success ? 'Berhasil' : 'Gagal'));
                if (d.success) location.reload();
                else btnSimpan.disabled = false;
            }).catch(function(err) {
                alert((err && err.message) ? err.message : 'Gagal mencatat.');
                btnSimpan.disabled = false;
            });
        });
    }

    document.querySelectorAll('.btn-batal-manual').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var nama = this.getAttribute('data-nama');
            var barang = this.getAttribute('data-barang');
            if (!confirm('Batalkan catatan pengambilan untuk ' + nama + ' — ' + barang + '?')) return;
            var self = this;
            self.disabled = true;
            postForm(urlBatal, {
                id_item: this.getAttribute('data-item'),
                tipe: this.getAttribute('data-tipe'),
                id_ref: this.getAttribute('data-ref')
            }).then(function(d) {
                alert(d.message || (d.success ? 'Berhasil' : 'Gagal'));
                if (d.success) location.reload();
                else self.disabled = false;
            }).catch(function(err) {
                alert((err && err.message) ? err.message : 'Gagal membatalkan.');
                self.disabled = false;
            });
        });
    });
})();
</script>
