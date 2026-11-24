<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Tagihan Berkas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tagihan_berkas_sekprodi/edit/' . $tagihan->id); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?php echo site_url('Tagihan_berkas_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Info Tagihan -->
                <div class="card card-info mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Tagihan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Nama Tagihan:</th>
                                        <td><strong><?php echo htmlspecialchars($tagihan->nama_tagihan); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Jenis:</th>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <span class="badge badge-warning">Hardcopy</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Softfile</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Kelas:</th>
                                        <td>
                                            <?php echo $tagihan->kelas_filter ? htmlspecialchars($tagihan->kelas_filter) : '<span class="text-muted">Semua Kelas</span>'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Deadline:</th>
                                        <td>
                                            <?php echo $tagihan->deadline ? date('d/m/Y', strtotime($tagihan->deadline)) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Status:</th>
                                        <td>
                                            <?php if ($tagihan->is_aktif == 1): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Oleh:</th>
                                        <td><?php echo htmlspecialchars($tagihan->created_by_name); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Dibuat:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($tagihan->created_at)); ?></td>
                                    </tr>
                                    <?php if ($tagihan->deskripsi): ?>
                                        <tr>
                                            <th>Deskripsi:</th>
                                            <td><?php echo nl2br(htmlspecialchars($tagihan->deskripsi)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik -->
                <?php if ($statistik): ?>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Mahasiswa</span>
                                    <span class="info-box-number"><?php echo $statistik->total; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sudah Mengumpulkan</span>
                                    <span class="info-box-number"><?php echo $statistik->sudah; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Belum Mengumpulkan</span>
                                    <span class="info-box-number"><?php echo $statistik->belum; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if ($tagihan->is_hardcopy == 1): ?>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-file-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Hardcopy Sudah</span>
                                        <span class="info-box-number"><?php echo $statistik->hardcopy_sudah; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Kelas -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="get" action="<?php echo site_url('Tagihan_berkas_sekprodi/detail/' . $tagihan->id); ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="kelas" class="mr-2">Filter Kelas:</label>
                                <select name="kelas" id="kelas" class="form-control form-control-sm">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                                <?php echo ($kelas_filter == $kelas->kelas) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas->kelas); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <?php if ($kelas_filter): ?>
                                <a href="<?php echo site_url('Tagihan_berkas_sekprodi/detail/' . $tagihan->id); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Tombol Simpan untuk Hardcopy -->
                <?php if ($tagihan->is_hardcopy == 1 && !empty($detail_list)): ?>
                    <div class="mb-3">
                        <button type="button" id="btn-simpan-hardcopy" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan Status
                        </button>
                        <button type="button" id="btn-select-all" class="btn btn-success btn-sm">
                            <i class="fas fa-check-circle"></i> Tandai Semua Sudah
                        </button>
                        <button type="button" id="btn-unselect-all" class="btn btn-danger btn-sm">
                            <i class="fas fa-times-circle"></i> Tandai Semua Belum
                        </button>
                        <span id="selected-count" class="ml-2 text-muted">0 perubahan</span>
                    </div>
                <?php endif; ?>

                <!-- Tabel Detail -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Kelas</th>
                                <?php if ($tagihan->is_hardcopy == 1): ?>
                                    <th>Status Hardcopy</th>
                                    <th width="12%" class="text-center">Status</th>
                                <?php else: ?>
                                    <th>Status Upload</th>
                                    <th>File</th>
                                    <th>Tanggal Upload</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detail_list)): ?>
                                <tr>
                                    <td colspan="<?php echo $tagihan->is_hardcopy == 1 ? '6' : '8'; ?>" class="text-center text-muted">Tidak ada data.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($detail_list as $detail): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($detail->nim); ?></td>
                                        <td><?php echo htmlspecialchars($detail->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($detail->kelas); ?></td>
                                        <?php if ($tagihan->is_hardcopy == 1): ?>
                                            <td>
                                                <span class="badge badge-<?php echo $detail->status_hardcopy == 'sudah' ? 'success' : 'warning'; ?>">
                                                    <?php echo $detail->status_hardcopy == 'sudah' ? 'Sudah' : 'Belum'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="javascript:void(0);" 
                                                   class="status-icon-toggle" 
                                                   data-id="<?php echo $detail->id; ?>"
                                                   data-status="<?php echo $detail->status_hardcopy; ?>"
                                                   title="<?php echo $detail->status_hardcopy == 'sudah' ? 'Klik untuk tandai belum' : 'Klik untuk tandai sudah'; ?>"
                                                   style="cursor: pointer; display: inline-block;">
                                                    <?php if ($detail->status_hardcopy == 'sudah'): ?>
                                                        <i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle text-danger" style="font-size: 24px;"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </td>
                                        <?php else: ?>
                                            <td>
                                                <span class="badge badge-<?php echo $detail->status == 'sudah' ? 'success' : 'warning'; ?>">
                                                    <?php echo $detail->status == 'sudah' ? 'Sudah Upload' : 'Belum Upload'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($detail->file_name): ?>
                                                    <a href="<?php echo base_url($detail->file_path); ?>" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-download"></i> <?php echo htmlspecialchars($detail->file_name); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $detail->uploaded_at ? date('d/m/Y H:i', strtotime($detail->uploaded_at)) : '-'; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($tagihan->is_hardcopy == 1): ?>
<script type="text/javascript">
(function() {
    'use strict';
    
    // Tunggu sampai DOM dan jQuery siap
    function initTagihanBerkas() {
        // Cek jQuery
        if (typeof jQuery === 'undefined') {
            console.error('jQuery tidak ditemukan!');
            setTimeout(initTagihanBerkas, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Tunggu sampai document ready
        $(document).ready(function() {
            console.log('=== Tagihan Berkas Script Loaded ===');
            
            // Simpan status awal untuk tracking perubahan
            var initialStatus = {};
            $('.status-icon-toggle').each(function() {
                var id = $(this).data('id');
                var status = $(this).data('status');
                if (id && status) {
                    initialStatus[id] = status;
                }
            });
            console.log('Initial status:', initialStatus);
            
            // Fungsi untuk update counter perubahan
            function updateSelectedCount() {
                var changed = 0;
                $('.status-icon-toggle').each(function() {
                    var id = $(this).data('id');
                    var currentStatus = $(this).data('status');
                    if (id && initialStatus[id] && initialStatus[id] !== currentStatus) {
                        changed++;
                    }
                });
                var countText = changed > 0 ? changed + ' perubahan' : 'Tidak ada perubahan';
                $('#selected-count').text(countText);
                console.log('Counter:', countText);
            }
            
            // Toggle icon saat diklik
            $(document).on('click', '.status-icon-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $icon = $(this);
                var id = $icon.data('id');
                var currentStatus = $icon.data('status');
                
                if (!id) {
                    console.error('Icon tanpa data-id');
                    return;
                }
                
                var newStatus = currentStatus === 'sudah' ? 'belum' : 'sudah';
                console.log('Icon clicked - ID:', id, 'Status:', currentStatus, '->', newStatus);
                
                // Update icon
                if (newStatus === 'sudah') {
                    $icon.html('<i class="fas fa-check-circle text-success" style="font-size: 24px; cursor: pointer;"></i>');
                    $icon.attr('title', 'Klik untuk tandai belum');
                } else {
                    $icon.html('<i class="fas fa-times-circle text-danger" style="font-size: 24px; cursor: pointer;"></i>');
                    $icon.attr('title', 'Klik untuk tandai sudah');
                }
                
                // Update data attribute
                $icon.data('status', newStatus);
                
                // Update counter
                updateSelectedCount();
            });
            
            // Tombol "Tandai Semua Sudah"
            $('#btn-select-all').on('click', function(e) {
                e.preventDefault();
                console.log('Tombol: Tandai Semua Sudah');
                
                $('.status-icon-toggle').each(function() {
                    var $icon = $(this);
                    $icon.html('<i class="fas fa-check-circle text-success" style="font-size: 24px; cursor: pointer;"></i>');
                    $icon.attr('title', 'Klik untuk tandai belum');
                    $icon.data('status', 'sudah');
                });
                updateSelectedCount();
            });
            
            // Tombol "Tandai Semua Belum"
            $('#btn-unselect-all').on('click', function(e) {
                e.preventDefault();
                console.log('Tombol: Tandai Semua Belum');
                
                $('.status-icon-toggle').each(function() {
                    var $icon = $(this);
                    $icon.html('<i class="fas fa-times-circle text-danger" style="font-size: 24px; cursor: pointer;"></i>');
                    $icon.attr('title', 'Klik untuk tandai sudah');
                    $icon.data('status', 'belum');
                });
                updateSelectedCount();
            });
            
            // Tombol Simpan
            $('#btn-simpan-hardcopy').on('click', function(e) {
                e.preventDefault();
                console.log('Tombol: Simpan Perubahan');
                
                var checkedIds = [];
                var uncheckedIds = [];
                
                $('.status-icon-toggle').each(function() {
                    var id = $(this).data('id');
                    var currentStatus = $(this).data('status');
                    var originalStatus = initialStatus[id];
                    
                    if (!id) return;
                    
                    // Hanya simpan yang berubah
                    if (originalStatus && currentStatus !== originalStatus) {
                        if (currentStatus === 'sudah') {
                            checkedIds.push(id);
                        } else {
                            uncheckedIds.push(id);
                        }
                    }
                });
                
                console.log('Checked IDs:', checkedIds);
                console.log('Unchecked IDs:', uncheckedIds);
                
                if (checkedIds.length === 0 && uncheckedIds.length === 0) {
                    alert('Tidak ada perubahan untuk disimpan.');
                    return;
                }
                
                var totalChanges = checkedIds.length + uncheckedIds.length;
                if (!confirm('Yakin ingin menyimpan perubahan status untuk ' + totalChanges + ' mahasiswa?')) {
                    return;
                }
                
                // Disable button saat proses
                var btn = $(this);
                btn.prop('disabled', true);
                var originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                // Format data untuk CodeIgniter
                var postData = {};
                $.each(checkedIds, function(index, id) {
                    postData['checked_ids[' + index + ']'] = id;
                });
                $.each(uncheckedIds, function(index, id) {
                    postData['unchecked_ids[' + index + ']'] = id;
                });
                
                console.log('Sending POST data:', postData);
                
                $.ajax({
                    url: '<?php echo site_url("Tagihan_berkas_sekprodi/update_status_hardcopy_batch"); ?>',
                    method: 'POST',
                    data: postData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        if (response && response.success) {
                            alert('Status berhasil diperbarui untuk ' + (response.updated || totalChanges) + ' mahasiswa.');
                            location.reload();
                        } else {
                            btn.prop('disabled', false);
                            btn.html(originalHtml);
                            var errorMsg = (response && response.message) ? response.message : 'Gagal update status';
                            alert('Gagal update status: ' + errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error, xhr);
                        btn.prop('disabled', false);
                        btn.html(originalHtml);
                        
                        var errorMsg = 'Terjadi kesalahan saat update status.';
                        if (xhr.status === 404) {
                            errorMsg = 'URL tidak ditemukan.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Terjadi kesalahan di server.';
                        }
                        alert(errorMsg);
                    }
                });
            });
            
            // Inisialisasi
            updateSelectedCount();
            console.log('Icons found:', $('.status-icon-toggle').length);
            console.log('Buttons found:', $('#btn-simpan-hardcopy').length, $('#btn-select-all').length, $('#btn-unselect-all').length);
        });
    }
    
    // Mulai inisialisasi
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTagihanBerkas);
    } else {
        initTagihanBerkas();
    }
})();
</script>
<?php endif; ?>

