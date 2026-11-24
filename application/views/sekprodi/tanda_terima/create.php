<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Tambah Distribusi Barang</h3></div>
            <form method="post" action="<?php echo site_url('Tanda_terima/store'); ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Distribusi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_distribusi" class="form-control" required placeholder="Contoh: Pengambilan KTM 2026">
                    </div>
                    <div class="form-group">
                        <label>Jenis Penerima <span class="text-danger">*</span></label>
                        <select name="jenis_penerima" class="form-control" required>
                            <option value="mahasiswa">Mahasiswa saja</option>
                            <option value="dosen">Dosen saja</option>
                            <option value="campuran">Mahasiswa + Dosen (pilih masing-masing)</option>
                        </select>
                        <small class="text-muted">Tidak semua orang wajib menerima semua barang — daftar penerima dipilih di halaman detail.</small>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control">
                        </div>
                    </div>
                    <?php if (!empty($tahun_aktif)): ?>
                        <p class="text-muted small">Tahun akademik aktif: <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?php echo site_url('Tanda_terima'); ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
