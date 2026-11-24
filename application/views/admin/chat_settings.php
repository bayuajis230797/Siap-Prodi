<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-cog"></i> Pengaturan Chat</h3>
            </div>
            <?php echo form_open('Chat_settings/update'); ?>
            <div class="card-body">
                <h5>Saluran Chat (aktif / nonaktif)</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="enable_dosen_to_mahasiswa" value="1" id="e1" <?php echo !empty($settings->enable_dosen_to_mahasiswa) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e1">Dosen → Mahasiswa</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="enable_sekprodi_to_mahasiswa" value="1" id="e2" <?php echo !empty($settings->enable_sekprodi_to_mahasiswa) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e2">Sekprodi / Kaprodi → Mahasiswa</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="enable_admin_to_mahasiswa" value="1" id="e3" <?php echo !empty($settings->enable_admin_to_mahasiswa) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e3">Admin → Mahasiswa</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="enable_mahasiswa_to_dosen" value="1" id="e4" <?php echo !empty($settings->enable_mahasiswa_to_dosen) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e4">Mahasiswa → Dosen</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="enable_mahasiswa_to_mahasiswa" value="1" id="e5" <?php echo !empty($settings->enable_mahasiswa_to_mahasiswa) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e5">Mahasiswa ↔ Mahasiswa</label>
                </div>

                <hr>
                <h5>Aturan Kontak</h5>
                <div class="form-group">
                    <label>Mahasiswa menghubungi dosen</label>
                    <select name="mhs_dosen_rule" class="form-control">
                        <option value="pembimbing_only" <?php echo ($settings->mhs_dosen_rule ?? '') === 'pembimbing_only' ? 'selected' : ''; ?>>Hanya dosen pembimbing (PKL/Skripsi/PKM)</option>
                        <option value="any_dosen" <?php echo ($settings->mhs_dosen_rule ?? '') === 'any_dosen' ? 'selected' : ''; ?>>Semua dosen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Chat antar mahasiswa</label>
                    <select name="mhs_mhs_rule" class="form-control">
                        <option value="same_class" <?php echo ($settings->mhs_mhs_rule ?? '') === 'same_class' ? 'selected' : ''; ?>>Hanya sesama kelas</option>
                        <option value="any_active" <?php echo ($settings->mhs_mhs_rule ?? '') === 'any_active' ? 'selected' : ''; ?>>Semua mahasiswa aktif</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
