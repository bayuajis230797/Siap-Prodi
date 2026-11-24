<?php
$items_map = isset($items_map) ? $items_map : array();
$tahun_aktif = isset($tahun_aktif) ? $tahun_aktif : null;
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-1"></i> Pengaturan Besaran Honor
                </h3>
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

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik Aktif:</strong>
                        <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Saat ini belum ada tahun akademik aktif. Pengaturan disimpan sebagai default (berlaku umum).
                    </div>
                <?php endif; ?>

                <?php echo form_open('Honor_setup'); ?>
                <div class="row">
                    <?php foreach ($items_map as $category): ?>
                        <div class="col-md-4">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo $category['title']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($category['items'] as $key => $item): ?>
                                        <?php
                                            $current_value = isset($item['value']) ? $item['value'] : 0;
                                            $input_name = "honor[{$key}]";
                                            $field_value = set_value($input_name, $current_value);
                                            // Pastikan nilai adalah angka dan tampilkan tanpa format
                                            $display_value = is_numeric($field_value) ? number_format($field_value, 0, '.', '') : '0';
                                        ?>
                                        <div class="form-group">
                                            <label for="<?php echo $key; ?>"><?php echo $item['label']; ?></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    pattern="[0-9]+(\.[0-9]{1,2})?"
                                                    class="form-control honor-input"
                                                    id="<?php echo $key; ?>"
                                                    name="<?php echo $input_name; ?>"
                                                    value="<?php echo htmlspecialchars($display_value); ?>"
                                                    required
                                                >
                                            </div>
                                            <small class="form-text text-muted">Masukkan angka tanpa pemisah ribuan (contoh: 32000)</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3 text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>




