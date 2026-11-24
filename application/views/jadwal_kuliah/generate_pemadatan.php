<div class="row">
    <div class="col-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Generate Jadwal Pemadatan</h3>
            </div>
            <?php echo form_open('Jadwal_kuliah/process_pemadatan', array('method' => 'post', 'id' => 'form-pemadatan')); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Rules Generate Jadwal Pemadatan:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Setiap mata kuliah akan bertemu 2x per minggu</strong> (untuk mempercepat perkuliahan)</li>
                        <li><strong>2x pertemuan tidak boleh di hari yang sama</strong> (harus di hari berbeda)</li>
                        <li>Hari perkuliahan: <strong>Senin - Jumat</strong></li>
                        <li>Jam mulai: 08:00 WIB, maksimal selesai: 17:30 WIB</li>
                        <li>1 SKS = 50 menit</li>
                        <li>Minimal 1 hari bisa 2-3 mata kuliah</li>
                        <li>Contoh: 3 SKS = 08:00-09:30, 10:00-12:30, 13:30-16:00</li>
                        <li>Ruangan random dan acak setiap mata kuliah</li>
                        <li><strong>TIDAK BOLEH BENTROK: kelas, dosen, ruangan (aturan tidak bisa diganggu gugat)</strong></li>
                        <li><strong>Hanya akan generate jadwal untuk mata kuliah yang belum dijadwalkan</strong></li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kelas">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" id="kelas" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo $k->kelas; ?>" <?php echo set_select('kelas', $k->kelas); ?>>
                                            <?php echo $k->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih kelas yang akan di-generate jadwal pemadatannya</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_mk_per_hari">Maksimal Mata Kuliah per Hari</label>
                            <select name="max_mk_per_hari" id="max_mk_per_hari" class="form-control">
                                <option value="2" <?php echo set_select('max_mk_per_hari', '2'); ?>>2 Mata Kuliah</option>
                                <option value="3" <?php echo set_select('max_mk_per_hari', '3', true); ?>>3 Mata Kuliah (Rekomendasi)</option>
                            </select>
                            <small class="form-text text-muted">Jumlah maksimal mata kuliah per hari</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="overwrite" value="1" <?php echo set_checkbox('overwrite', '1'); ?>>
                                Overwrite jadwal yang sudah ada
                            </label>
                            <small class="form-text text-muted">Jika dicentang, akan menghapus jadwal yang sudah ada untuk kelas ini dan generate ulang</small>
                        </div>
                    </div>
                </div>

                <div id="preview-container" style="display: none;">
                    <hr>
                    <h5>Preview Jadwal Pemadatan yang akan di-generate:</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Setiap mata kuliah akan muncul 2x (pertemuan pertama dan kedua) di hari yang berbeda.
                    </div>
                    <div id="preview-content" class="table-responsive">
                        <!-- Akan diisi via JavaScript -->
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-info" id="btn-preview">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="submit" class="btn btn-warning" id="btn-generate">
                    <i class="fas fa-compress"></i> Generate Jadwal Pemadatan
                </button>
                <a href="<?php echo site_url('Jadwal_kuliah'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.getElementById('kelas');
    const btnPreview = document.getElementById('btn-preview');
    const btnGenerate = document.getElementById('btn-generate');
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');

    btnPreview.addEventListener('click', function() {
        const kelas = kelasSelect.value;
        if (!kelas) {
            alert('Pilih kelas terlebih dahulu!');
            return;
        }

        const maxMkPerHari = document.getElementById('max_mk_per_hari').value;
        const url = '<?php echo site_url("Jadwal_kuliah/preview_pemadatan"); ?>'
            + '?kelas=' + encodeURIComponent(kelas)
            + '&max_mk_per_hari=' + encodeURIComponent(maxMkPerHari);

        btnPreview.disabled = true;
        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.text().then(text => ({ ok: response.ok, status: response.status, text: text })))
        .then(result => {
            btnPreview.disabled = false;
            let data;
            try {
                data = JSON.parse(result.text);
            } catch (e) {
                console.error('Error:', e, result.text);
                alert(result.status === 403
                    ? 'Sesi keamanan kedaluwarsa. Silakan refresh halaman lalu coba lagi.'
                    : 'Terjadi kesalahan saat memuat preview (HTTP ' + result.status + ').');
                return;
            }
            if (data.success) {
                displayPreview(data.data || []);
                previewContainer.style.display = 'block';
            } else {
                alert('Error: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            btnPreview.disabled = false;
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat preview');
        });
    });

    function displayPreview(data) {
        if (!data || !data.length) {
            previewContent.innerHTML = '<div class="alert alert-warning mb-0">Tidak ada jadwal pemadatan yang bisa di-preview untuk kelas ini.</div>';
            return;
        }
        let html = '<table class="table table-bordered table-sm">';
        html += '<thead><tr><th>Hari</th><th>Kode MK</th><th>Mata Kuliah</th><th>SKS</th><th>Dosen</th><th>Jam</th><th>Ruang</th><th>Pertemuan</th></tr></thead>';
        html += '<tbody>';
        
        const hariOrder = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        hariOrder.forEach(hari => {
            const jadwalHari = data.filter(j => j.hari === hari);
            if (jadwalHari.length > 0) {
                jadwalHari.forEach((j, idx) => {
                    if (idx === 0) {
                        html += `<tr><td rowspan="${jadwalHari.length}"><strong>${hari.charAt(0).toUpperCase() + hari.slice(1)}</strong></td>`;
                    } else {
                        html += '<tr>';
                    }
                    html += `<td>${j.kode_mk}</td>`;
                    html += `<td>${j.nama_mk}</td>`;
                    html += `<td>${j.sks} SKS</td>`;
                    html += `<td>${j.nama_dosen}</td>`;
                    html += `<td>${j.jam_mulai} - ${j.jam_selesai}</td>`;
                    html += `<td>${j.ruang}</td>`;
                    html += `<td><span class="badge badge-info">${j.pertemuan}</span></td>`;
                    html += '</tr>';
                });
            }
        });
        
        html += '</tbody></table>';
        previewContent.innerHTML = html;
    }
});
</script>













