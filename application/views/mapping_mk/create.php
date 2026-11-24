<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Mapping Mata Kuliah - Dosen</h3>
            </div>
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
                <?php endif; ?>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="mappingTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="by-kelas-tab" data-bs-toggle="tab" href="#by-kelas" role="tab" aria-controls="by-kelas" aria-selected="true">
                            <i class="fas fa-users"></i> Mapping Per Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="false">
                            <i class="fas fa-edit"></i> Mapping Manual
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="mappingTabContent">
                    <!-- Tab: Mapping Per Kelas -->
                    <div class="tab-pane fade show active" id="by-kelas" role="tabpanel">
                        <div class="form-group">
                            <label for="kelas_select">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_select" id="kelas_select" class="form-control">
                                <option value="">-- Pilih Kelas --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo htmlspecialchars($k->kelas); ?>">
                                            <?php echo htmlspecialchars($k->kelas); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">
                                Kelas ditampilkan sesuai semester aktif
                                <?php if (!empty($tahun_aktif)): ?>
                                    (<?php echo htmlspecialchars(ucfirst($tahun_aktif->semester)); ?>:
                                    <?php echo $tahun_aktif->semester === 'ganjil' ? '1,3,5,7' : '2,4,6,8'; ?>)
                                <?php endif; ?>
                            </small>
                        </div>

                        <div id="loading-mk" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat mata kuliah...
                        </div>

                        <div id="mk-error-message" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Perhatian:</strong> <span id="error-text"></span>
                        </div>

                        <div id="mk-list-container" style="display: none;">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Mata Kuliah untuk Kelas:</strong> <span id="kelas-selected"></span>
                                <br><small class="text-muted"><i class="fas fa-info-circle"></i> Mapping dosen yang Anda pilih akan spesifik untuk kelas ini. Kelas lain (misalnya 1A) tidak akan terpengaruh.</small>
                            </div>
                            
                            <?php echo form_open('Mapping_mk/batch_create', array('method' => 'post', 'id' => 'form-batch-mapping')); ?>
                            <input type="hidden" name="kelas" id="batch-kelas" value="">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="table-mk-list">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="12%">Kode MK</th>
                                            <th>Nama Mata Kuliah</th>
                                            <th width="8%" class="text-center">SKS</th>
                                            <th width="10%" class="text-center">Semester</th>
                                            <th width="15%">Kurikulum</th>
                                            <th width="30%">Dosen Pengampu</th>
                                            <th width="5%" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mk-list-body">
                                        <!-- Akan diisi via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary" id="btn-save-batch" disabled>
                                    <i class="fas fa-save"></i> Simpan Semua Mapping
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetKelasForm()">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>

                    <!-- Tab: Mapping Manual -->
                    <div class="tab-pane fade" id="manual" role="tabpanel">
                        <?php echo form_open('Mapping_mk/create', array('method' => 'post')); ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Petunjuk:</strong> Pilih kelas terlebih dahulu, kemudian pilih mata kuliah yang belum di-mapping untuk kelas tersebut.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kelas_manual">Kelas <span class="text-danger">*</span></label>
                                    <select name="kelas" id="kelas_manual" class="form-control" required>
                                        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                                        <?php if (!empty($kelas_list)): ?>
                                            <?php foreach ($kelas_list as $k): ?>
                                                <option value="<?php echo $k->kelas; ?>" <?php echo set_select('kelas', $k->kelas); ?>>
                                                    <?php echo $k->kelas; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Pilih kelas terlebih dahulu untuk menampilkan mata kuliah yang belum di-mapping</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_mata_kuliah_aktif">Mata Kuliah Aktif <span class="text-danger">*</span></label>
                                    <select name="id_mata_kuliah_aktif" id="id_mata_kuliah_aktif_manual" class="form-control" required disabled>
                                        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                                    </select>
                                    <small class="form-text text-muted">Pilih mata kuliah yang belum di-mapping untuk kelas yang dipilih</small>
                                    <div id="loading-mk-manual" class="text-center mt-2" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat mata kuliah...
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_dosen">Dosen Pengampu <span class="text-danger">*</span></label>
                                    <select name="id_dosen" id="id_dosen" class="form-control" required>
                                        <option value="">-- Pilih Dosen --</option>
                                        <?php if (!empty($dosen)): ?>
                                            <?php foreach ($dosen as $d): ?>
                                                <?php if ($d->status == 'aktif'): ?>
                                                    <option value="<?php echo $d->id; ?>" <?php echo set_select('id_dosen', $d->id); ?>>
                                                        <?php echo $d->nidn . ' - ' . $d->nama_dosen; ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Pilih dosen yang akan mengampu mata kuliah</small>
                                </div>
                            </div>
                        </div>

                        <div id="mk-manual-empty" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Perhatian:</strong> <span id="mk-manual-empty-text">Tidak ada mata kuliah yang tersedia untuk kelas yang dipilih.</span>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?php echo site_url('Mapping_mk'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var mkLoadController = null;
var mkManualLoadController = null;

function fillKelasOptions(selectEl, list) {
    if (!selectEl) return;
    var current = selectEl.value;
    selectEl.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    (list || []).forEach(function(item) {
        var kelas = item.kelas || item;
        if (!kelas) return;
        var option = document.createElement('option');
        option.value = kelas;
        option.textContent = kelas;
        if (current && current === kelas) {
            option.selected = true;
        }
        selectEl.appendChild(option);
    });
}

function parseJsonResponse(text, httpStatus) {
    var trimmed = (text || '').trim();
    if (!trimmed) {
        throw new Error('Server mengembalikan respons kosong (HTTP ' + httpStatus + ').');
    }
    try {
        return JSON.parse(trimmed);
    } catch (e) {
        if (trimmed.charAt(0) === '<' || /csrf|forbidden|login/i.test(trimmed)) {
            throw new Error('Sesi atau token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.');
        }
        throw new Error('Respons server tidak valid (HTTP ' + httpStatus + '). Silakan refresh halaman.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadKelas();

    document.getElementById('kelas_select').addEventListener('change', function() {
        const kelas = this.value;
        if (kelas) {
            document.getElementById('batch-kelas').value = kelas;
            loadMataKuliahByKelas(kelas);
        } else {
            document.getElementById('mk-list-container').style.display = 'none';
            document.getElementById('mk-error-message').style.display = 'none';
            document.getElementById('batch-kelas').value = '';
        }
    });

    const kelasManualSelect = document.getElementById('kelas_manual');
    if (kelasManualSelect) {
        kelasManualSelect.addEventListener('change', function() {
            const kelas = this.value;
            if (kelas) {
                loadMataKuliahManualByKelas(kelas);
            } else {
                const mkSelect = document.getElementById('id_mata_kuliah_aktif_manual');
                mkSelect.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
                mkSelect.disabled = true;
                document.getElementById('mk-manual-empty').style.display = 'none';
            }
        });
    }
});

function loadKelas() {
    fetch('<?php echo site_url("Mapping_mk/get_kelas"); ?>?filter_semester=1', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            fillKelasOptions(document.getElementById('kelas_select'), data.data);
            fillKelasOptions(document.getElementById('kelas_manual'), data.data);
        }
    })
    .catch(error => {
        console.error('Error loading kelas:', error);
    });
}

function loadMataKuliahByKelas(kelas) {
    const loadingEl = document.getElementById('loading-mk');
    const containerEl = document.getElementById('mk-list-container');
    const errorEl = document.getElementById('mk-error-message');
    const errorTextEl = document.getElementById('error-text');
    const kelasSelectedEl = document.getElementById('kelas-selected');

    errorEl.style.display = 'none';
    containerEl.style.display = 'none';
    loadingEl.style.display = 'block';

    if (mkLoadController) {
        try { mkLoadController.abort(); } catch (e) {}
    }
    mkLoadController = (typeof AbortController !== 'undefined') ? new AbortController() : null;

    const url = '<?php echo site_url("Mapping_mk/get_mk_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas);
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        signal: mkLoadController ? mkLoadController.signal : undefined
    })
    .then(response => {
        return response.text().then(text => ({ ok: response.ok, status: response.status, text: text }));
    })
    .then(result => {
        const data = parseJsonResponse(result.text, result.status);
        loadingEl.style.display = 'none';

        if (!result.ok || !data.success) {
            errorTextEl.innerHTML = data.message || ('Gagal memuat mata kuliah (HTTP ' + result.status + ').');
            errorEl.style.display = 'block';
            return;
        }

        if (data.data && data.data.length > 0) {
            kelasSelectedEl.textContent = kelas;
            populateMataKuliahTable(data.data, data.mapped_ids || [], data.mapped_dosen || {});
            containerEl.style.display = 'block';
            errorEl.style.display = 'none';
        } else {
            errorTextEl.innerHTML = 'Tidak ada mata kuliah untuk kelas ' + kelas + '. Pastikan mata kuliah sudah di-set aktif di menu Kurikulum > Mapping MK.';
            errorEl.style.display = 'block';
        }
    })
    .catch(error => {
        if (error && error.name === 'AbortError') {
            return;
        }
        loadingEl.style.display = 'none';
        console.error('Error loading mata kuliah:', error);
        errorTextEl.innerHTML = (error && error.message) ? error.message : 'Terjadi kesalahan saat memuat mata kuliah. Silakan refresh halaman lalu coba lagi.';
        errorEl.style.display = 'block';
    });
}

function populateMataKuliahTable(mataKuliah, mappedIds, mappedDosen) {
    const tbody = document.getElementById('mk-list-body');
    tbody.innerHTML = '';

    const dosenList = <?php echo json_encode(array_map(function($d) { return ['id' => $d->id, 'nidn' => $d->nidn, 'nama' => $d->nama_dosen]; }, array_filter($dosen ?? [], function($d) { return $d->status == 'aktif'; }))); ?>;

    let no = 1;
    mataKuliah.forEach(mk => {
        const row = document.createElement('tr');
        const isMapped = mappedIds.includes(mk.id);
        let currentDosenId = mappedDosen && mappedDosen[mk.id] ? mappedDosen[mk.id] : '';

        row.innerHTML = `
            <td>${no++}</td>
            <td><strong>${mk.kode_mk}</strong></td>
            <td>${mk.nama_mk}</td>
            <td class="text-center"><span class="badge badge-info">${mk.sks} SKS</span></td>
            <td class="text-center"><span class="badge badge-primary">Semester ${mk.semester || mk.semester_aktif}</span></td>
            <td><small><strong>${mk.nama_kurikulum}</strong><br><span class="text-muted">Tahun ${mk.tahun_berlaku}</span></small></td>
            <td>
                <select name="mappings[${mk.id}][id_dosen]" class="form-control form-control-sm dosen-select" data-mk-id="${mk.id}" ${isMapped ? 'disabled' : ''}>
                    <option value="">-- Pilih Dosen --</option>
                    ${dosenList.map(d => `<option value="${d.id}" ${currentDosenId == d.id ? 'selected' : ''}>${d.nidn} - ${d.nama}</option>`).join('')}
                </select>
                <input type="hidden" name="mappings[${mk.id}][id_mata_kuliah_aktif]" value="${mk.id}">
            </td>
            <td class="text-center">
                ${isMapped ? '<span class="badge badge-success"><i class="fas fa-check"></i> Sudah di-mapping</span>' : '<span class="badge badge-secondary">Belum di-mapping</span>'}
            </td>
        `;

        tbody.appendChild(row);
    });

    updateSaveButton();

    document.querySelectorAll('.dosen-select').forEach(select => {
        select.addEventListener('change', updateSaveButton);
    });
}

function updateSaveButton() {
    const selects = document.querySelectorAll('.dosen-select:not([disabled])');
    let hasSelection = false;

    selects.forEach(select => {
        if (select.value) {
            hasSelection = true;
        }
    });

    document.getElementById('btn-save-batch').disabled = !hasSelection;
}

function loadMataKuliahManualByKelas(kelas) {
    const mkSelect = document.getElementById('id_mata_kuliah_aktif_manual');
    const loadingEl = document.getElementById('loading-mk-manual');
    const emptyEl = document.getElementById('mk-manual-empty');
    const emptyTextEl = document.getElementById('mk-manual-empty-text');

    mkSelect.disabled = true;
    mkSelect.innerHTML = '<option value="">Memuat...</option>';
    loadingEl.style.display = 'block';
    emptyEl.style.display = 'none';

    if (mkManualLoadController) {
        try { mkManualLoadController.abort(); } catch (e) {}
    }
    mkManualLoadController = (typeof AbortController !== 'undefined') ? new AbortController() : null;

    const url = '<?php echo site_url("Mapping_mk/get_mk_available_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas);
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        signal: mkManualLoadController ? mkManualLoadController.signal : undefined
    })
    .then(response => {
        return response.text().then(text => ({ ok: response.ok, status: response.status, text: text }));
    })
    .then(result => {
        const data = parseJsonResponse(result.text, result.status);
        loadingEl.style.display = 'none';

        if (!result.ok || !data.success) {
            mkSelect.innerHTML = '<option value="">-- Error: ' + (data.message || 'Terjadi kesalahan') + ' --</option>';
            emptyTextEl.textContent = data.message || 'Terjadi kesalahan saat memuat mata kuliah.';
            emptyEl.style.display = 'block';
            return;
        }

        if (data.data && data.data.length > 0) {
            mkSelect.innerHTML = '<option value="">-- Pilih Mata Kuliah --</option>';
            data.data.forEach(mk => {
                const semester = mk.semester || mk.semester_aktif || '-';
                const option = document.createElement('option');
                option.value = mk.id;
                option.textContent = mk.kode_mk + ' - ' + mk.nama_mk + ' (' + mk.sks + ' SKS) - Semester ' + semester + ' - ' + mk.nama_kurikulum;
                mkSelect.appendChild(option);
            });
            mkSelect.disabled = false;
            emptyEl.style.display = 'none';
        } else {
            mkSelect.innerHTML = '<option value="">-- Semua mata kuliah sudah di-mapping --</option>';
            mkSelect.disabled = true;
            emptyTextEl.textContent = 'Semua mata kuliah untuk kelas ' + kelas + ' sudah di-mapping.';
            emptyEl.style.display = 'block';
        }
    })
    .catch(error => {
        if (error && error.name === 'AbortError') {
            return;
        }
        loadingEl.style.display = 'none';
        console.error('Error loading mata kuliah:', error);
        mkSelect.innerHTML = '<option value="">-- Error loading data --</option>';
        emptyTextEl.textContent = (error && error.message) ? error.message : 'Terjadi kesalahan saat memuat mata kuliah.';
        emptyEl.style.display = 'block';
    });
}

function resetKelasForm() {
    document.getElementById('kelas_select').value = '';
    document.getElementById('mk-list-container').style.display = 'none';
    document.getElementById('mk-error-message').style.display = 'none';
    document.getElementById('mk-list-body').innerHTML = '';
}
</script>
