<?php
/**
 * Kartu hitung mundur satu sidang.
 * Variabel: $cd_id (unik), $cd_timestamp (detik), $cd_judul (teks kecil), $cd_info (tanggal/ruang)
 */
?>
<div class="proposal-countdown" data-timestamp="<?php echo (int) $cd_timestamp; ?>" data-id="<?php echo htmlspecialchars($cd_id); ?>">
    <?php if (!empty($cd_judul)): ?><p class="mb-1"><strong><?php echo htmlspecialchars($cd_judul); ?></strong></p><?php endif; ?>
    <?php if (!empty($cd_info)): ?><p class="mb-2 small text-muted"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($cd_info); ?></p><?php endif; ?>
    <div class="row text-center no-gutters">
        <?php foreach (array('hari' => 'Hari', 'jam' => 'Jam', 'menit' => 'Menit', 'detik' => 'Detik') as $k => $label): ?>
            <div class="col-3 px-1">
                <div class="p-1" style="background:#007bff;color:#fff;border-radius:6px;">
                    <div style="font-weight:bold;font-size:1.1rem" data-unit="<?php echo $k; ?>">--</div>
                    <div style="font-size:.65rem"><?php echo $label; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="text-muted mb-0 mt-2 small text-center" data-status></p>
</div>
