<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Buku Bimbingan Skripsi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 15px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 14pt;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .main-table td {
            text-align: left;
        }
        .main-table td.center {
            text-align: center;
        }
        .signature-cell {
            height: 40px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <h2>BUKU BIMBINGAN SKRIPSI</h2>
    
    <table class="info-table" border="0">
        <tr>
            <td width="20%"><strong>NIM</strong></td>
            <td width="30%">: <?php echo htmlspecialchars($mahasiswa->nim); ?></td>
            <td width="20%"><strong>Nama</strong></td>
            <td width="30%">: <?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
        </tr>
        <tr>
            <td><strong>Judul Skripsi</strong></td>
            <td colspan="3">: <?php echo htmlspecialchars($pengajuan ? $pengajuan->judul_pengajuan : '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Tahun Akademik</strong></td>
            <td>: <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: <?php echo date('d/m/Y H:i:s'); ?></td>
        </tr>
    </table>
    
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Dosen Pembimbing</th>
                <th width="12%">Status</th>
                <th width="20%">Keterangan Revisi</th>
                <th width="13%">Tanggal</th>
                <th width="25%">TTD</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($buku_bimbingan)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px;">Belum ada feedback dari dosen pembimbing.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($buku_bimbingan as $row): ?>
                    <tr>
                        <td class="center"><?php echo $row['no']; ?></td>
                        <td><?php echo htmlspecialchars($row['dosen_pembimbing']); ?></td>
                        <td class="center"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['keterangan_revisi'])); ?></td>
                        <td class="center"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                        <td class="signature-cell"></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

