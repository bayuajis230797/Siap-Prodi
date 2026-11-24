<?php $this->load->helper('pkm'); ?>
<div class="row">
    <div class="col-lg-10">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Judul PKM — <?php echo htmlspecialchars($periode->nama_periode); ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkm_mahasiswa'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <form method="post" action="<?php echo site_url('Pkm_mahasiswa/simpan'); ?>" id="form-pkm-ajuan" enctype="multipart/form-data">
                <input type="hidden" name="id_pkm_periode" value="<?php echo htmlspecialchars(pkm_encoded_id($periode->id)); ?>">
                <div class="card-body">
                    <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                    <div class="form-group">
                        <label>Jenis Lomba PKM <span class="text-danger">*</span></label>
                        <select name="id_pkm_jenis" id="sel-jenis" class="form-control" required>
                            <option value="">— Pilih jenis —</option>
                            <?php foreach ($jenis_list as $j): ?>
                                <option value="<?php echo (int) $j->id; ?>" data-min="<?php echo (int) $j->min_anggota; ?>" data-max="<?php echo (int) $j->max_anggota; ?>">
                                    <?php echo htmlspecialchars($j->nama_jenis); ?> (anggota: <?php echo (int) $j->min_anggota; ?>–<?php echo (int) $j->max_anggota; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Judul PKM <span class="text-danger">*</span></label>
                        <input type="text" name="judul_pkm" class="form-control" required maxlength="500" placeholder="Judul proposal PKM">
                    </div>
                    <div class="form-group">
                        <label>Proposal Awal (opsional)</label>
                        <input type="file" name="file_proposal_awal" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar">
                        <small class="text-muted">Diunggah ke Google Drive institusi. Referensi disimpan terenkripsi di database.</small>
                    </div>
                    <div class="form-group">
                        <label>Dosen Pembimbing (Request) <span class="text-danger">*</span></label>
                        <select name="id_pembimbing_request" class="form-control" required>
                            <option value="">— Pilih dosen —</option>
                            <?php foreach ($dosen_list as $d): ?>
                                <option value="<?php echo (int) $d->id; ?>"><?php echo htmlspecialchars($d->nama_dosen); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Akan diverifikasi oleh sekprodi.</small>
                    </div>

                    <hr>
                    <h5>Anggota Tim <small class="text-muted" id="hint-anggota">(selain Anda sebagai ketua)</small></h5>
                    <div id="container-anggota"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-tambah-anggota"><i class="fas fa-plus"></i> Tambah Anggota</button>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var idx = 0;
    var minA = 1, maxA = 4;
    var searchUrl = '<?php echo site_url('Pkm_mahasiswa/cari_mahasiswa'); ?>';
    var idPeriode = <?php echo (int) $periode->id; ?>;

    document.getElementById('sel-jenis').addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        minA = parseInt(opt.getAttribute('data-min') || '1', 10);
        maxA = parseInt(opt.getAttribute('data-max') || '4', 10);
        document.getElementById('hint-anggota').textContent = '(min ' + minA + ', max ' + maxA + ' anggota selain ketua)';
    });

    function bindRow(row) {
        var selTipe = row.querySelector('.sel-tipe');
        var internal = row.querySelector('.blok-internal');
        var external = row.querySelector('.blok-external');
        var inputSearch = row.querySelector('.input-cari-mhs');
        var hiddenId = row.querySelector('.hidden-id-mhs');
        var results = row.querySelector('.hasil-cari');

        selTipe.addEventListener('change', function() {
            var isExt = this.value === 'external';
            internal.style.display = isExt ? 'none' : 'block';
            external.style.display = isExt ? 'block' : 'none';
            row.querySelectorAll('.fld-nim-manual, .fld-nama-manual, .fld-prodi-manual').forEach(function(el) {
                el.readOnly = !isExt;
                if (!isExt) el.value = '';
            });
            if (inputSearch) {
                inputSearch.readOnly = isExt;
                if (isExt) inputSearch.value = '';
            }
            if (isExt) {
                hiddenId.value = '';
            }
        });

        if (inputSearch) {
            var timer;
            inputSearch.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.innerHTML = ''; return; }
                timer = setTimeout(function() {
                    fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&id_periode=' + idPeriode)
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            results.innerHTML = '';
                            data.forEach(function(item) {
                                var a = document.createElement('a');
                                a.href = '#';
                                a.className = 'list-group-item list-group-item-action py-1';
                                a.textContent = item.label;
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    hiddenId.value = item.token;
                                    inputSearch.value = item.label;
                                    results.innerHTML = '';
                                });
                                results.appendChild(a);
                            });
                        });
                }, 300);
            });
        }

        row.querySelector('.btn-hapus-anggota').addEventListener('click', function() {
            row.remove();
        });
    }

    function addRow() {
        var container = document.getElementById('container-anggota');
        if (container.children.length >= maxA) {
            alert('Maksimal ' + maxA + ' anggota.');
            return;
        }
        var i = idx++;
        var html = '<div class="card card-outline card-secondary mb-2 p-2 row-anggota" data-idx="' + i + '">' +
            '<div class="form-row align-items-center">' +
            '<div class="col-md-3"><select name="anggota_tipe[' + i + ']" class="form-control form-control-sm sel-tipe">' +
            '<option value="internal">Dari Prodi S1 Sistem Informasi</option>' +
            '<option value="external">Dari Prodi Lain</option></select></div>' +
            '<div class="col-md-8">' +
            '<input type="hidden" name="anggota_id_mahasiswa[' + i + ']" class="hidden-id-mhs" value="">' +
            '<div class="blok-internal">' +
            '<input type="text" class="form-control form-control-sm input-cari-mhs" placeholder="Cari NIM / nama mahasiswa S1 SI..." autocomplete="off">' +
            '<div class="list-group hasil-cari" style="max-height:120px;overflow:auto"></div>' +
            '</div>' +
            '<div class="blok-external" style="display:none">' +
            '<div class="form-row">' +
            '<div class="col-4"><input type="text" name="anggota_nim_manual[' + i + ']" class="form-control form-control-sm fld-nim-manual" placeholder="NIM" readonly tabindex="-1"></div>' +
            '<div class="col-4"><input type="text" name="anggota_nama_manual[' + i + ']" class="form-control form-control-sm fld-nama-manual" placeholder="Nama lengkap" readonly tabindex="-1"></div>' +
            '<div class="col-4"><input type="text" name="anggota_prodi_asal[' + i + ']" class="form-control form-control-sm fld-prodi-manual" placeholder="Nama prodi *" readonly tabindex="-1"></div>' +
            '</div></div></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btn-hapus-anggota"><i class="fas fa-times"></i></button></div>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
        bindRow(container.lastElementChild);
    }

    document.getElementById('btn-tambah-anggota').addEventListener('click', addRow);
    document.getElementById('form-pkm-ajuan').addEventListener('submit', function(e) {
        var n = document.querySelectorAll('#container-anggota .row-anggota').length;
        if (n < minA || n > maxA) {
            e.preventDefault();
            alert('Jumlah anggota harus ' + minA + '–' + maxA + '.');
        }
    });

    for (var c = 0; c < minA; c++) addRow();

})();
</script>
