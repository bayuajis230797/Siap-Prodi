<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR - <?php echo htmlspecialchars($item->nama_barang); ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 24px; }
        h2 { margin-bottom: 8px; }
        .qr { margin: 16px auto; }
        .qr img { max-width: 280px; }
        .muted { color: #666; font-size: 14px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Cetak</button>
    <h2><?php echo htmlspecialchars($tt->nama_distribusi); ?></h2>
    <h3><?php echo htmlspecialchars($item->nama_barang); ?></h3>
    <p class="muted">Penerima: <?php echo ucfirst($tt->jenis_penerima); ?></p>
    <div class="qr">
        <?php if ($item->qr_code): ?>
            <?php $this->load->helper('qrcode'); ?>
            <img src="<?php echo qr_code_serve_url_tanda_terima_item($item->id); ?>" alt="QR Code">
        <?php else: ?>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=<?php echo urlencode($scan_url); ?>" alt="QR">
        <?php endif; ?>
    </div>
    <p class="muted">
        Scan dengan akun yang terdaftar
        <?php if ($tt->jenis_penerima === 'campuran'): ?>
            (mahasiswa atau dosen, sesuai login)
        <?php else: ?>
            (<?php echo $tt->jenis_penerima; ?>)
        <?php endif; ?>
    </p>
</body>
</html>
