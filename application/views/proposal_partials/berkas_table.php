<?php
/**
 * Tabel berkas yang sudah dikirim. Variabel: $berkas_uploaded
 */
$this->load->helper('google_drive');
?>
<?php if (!empty($berkas_uploaded)): ?>
    <div class="table-responsive mb-3">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr><th>Nama Berkas</th><th>Berkas</th><th width="18%">Dikirim</th></tr>
            </thead>
            <tbody>
                <?php foreach ($berkas_uploaded as $b): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($b->nama_berkas); ?>
                            <?php if (!empty($b->wajib)): ?><span class="badge badge-danger ml-1">Wajib</span><?php endif; ?>
                        </td>
                        <td><?php echo google_drive_link_html($b->file_path, 'Buka Berkas'); ?></td>
                        <td><?php echo !empty($b->uploaded_at) ? date('d/m/Y H:i', strtotime($b->uploaded_at)) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
