<?php $this->load->helper('app'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histori Bimbingan Proposal TA</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 1cm 1.5cm; }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Regular.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Regular.woff'); ?>') format('woff');
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Bold.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Bold.woff'); ?>') format('woff');
            font-weight: 700; font-style: normal; font-display: swap;
        }
        body { font-family: "Poppins", Arial, sans-serif; font-size: 10pt; margin: 0; padding: 10px; line-height: 1.3; }
        .kop-surat { text-align: center; margin-bottom: 8px; }
        .kop-surat img { max-width: 100%; height: auto; max-height: 100px; }
        .judul { text-align: center; font-size: 11pt; font-weight: bold; margin: 8px 0; text-transform: uppercase; }
        .info-mahasiswa { margin-bottom: 8px; font-size: 8pt; }
        .info-mahasiswa table { width: 100%; border-collapse: collapse; }
        .info-mahasiswa td { padding: 2px 5px; vertical-align: top; }
        .info-mahasiswa td.label { width: 120px; font-weight: bold; }
        .statistik { margin: 10px 0; font-size: 9pt; background-color: #f0f0f0; padding: 8px; border: 1px solid #ddd; }
        .statistik table { width: 100%; border-collapse: collapse; }
        .statistik td { padding: 3px 5px; }
        .table-log { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 8pt; }
        .table-log th, .table-log td { border: 1px solid #000; padding: 3px; text-align: left; vertical-align: top; }
        .table-log th { background-color: #f0f0f0; font-weight: bold; text-align: center; padding: 4px 3px; }
        .table-log td.no { text-align: center; width: 4%; }
        .table-log td.tanggal { text-align: center; width: 12%; }
        .table-log td.topik { width: 20%; }
        .table-log td.pengirim { width: 20%; text-align: left; }
        .table-log td.status { text-align: center; width: 10%; }
        .table-log td.keterangan { width: 42%; word-wrap: break-word; word-break: break-word; white-space: normal; line-height: 1.35; }
        .footer { margin-top: 20px; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="kop-surat"><?php echo get_kop_header(); ?></div>

    <div class="judul">Histori Bimbingan Proposal TA</div>

    <div class="info-mahasiswa">
        <table>
            <tr>
                <td class="label">NIM</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nim); ?></td>
                <td class="label" style="width: 100px;">Tahun Akademik</td>
                <td>: <?php echo $tahun_aktif ? htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)) : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
                <td class="label">Kelas</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->kelas ?: '-'); ?></td>
            </tr>
            <?php if (!empty($judul)): ?>
            <tr>
                <td class="label">Judul Proposal</td>
                <td colspan="3">: <?php echo htmlspecialchars($judul); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($pembimbing): ?>
            <tr>
                <td class="label">Pembimbing</td>
                <td>: <?php echo htmlspecialchars($pembimbing->nama_dosen); ?></td>
                <td class="label">NIDN</td>
                <td>: <?php echo htmlspecialchars($pembimbing->nidn ?: '-'); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td colspan="3">: <?php echo date('d F Y H:i:s'); ?></td>
            </tr>
        </table>
    </div>

    <div class="statistik">
        <table>
            <tr>
                <td><strong>Total Topik Bimbingan:</strong> <?php echo (int) $statistik->total_topik; ?></td>
                <td><strong>Total Sesi Bimbingan:</strong> <?php echo (int) $statistik->total_log; ?></td>
                <td><strong>Jumlah ACC:</strong> <?php echo (int) $statistik->total_acc; ?></td>
                <td><strong>Jumlah Revisi:</strong> <?php echo (int) $statistik->total_revisi; ?></td>
            </tr>
        </table>
    </div>

    <?php
    $nama_dosen = $pembimbing ? $pembimbing->nama_dosen : 'Dosen Pembimbing';
    $all_histori = array();
    foreach ($all_logs as $log_group) {
        foreach ($log_group['logs'] as $log) {
            $all_histori[] = array(
                'tanggal' => $log->created_at,
                'topik' => $log_group['bimbingan']->judul_topik,
                'pengirim' => $log->role_pengguna === 'dosen' ? $nama_dosen : $mahasiswa->nama_mahasiswa,
                'status' => $log->status_revisi,
                'keterangan' => $log->keterangan_revisi ?: ($log->pesan ?: '-')
            );
        }
    }
    usort($all_histori, function ($a, $b) { return strtotime($a['tanggal']) - strtotime($b['tanggal']); });
    ?>

    <?php if (empty($all_histori)): ?>
        <p style="text-align: center; padding: 15px; font-style: italic;">Belum ada data bimbingan.</p>
    <?php else: ?>
        <table class="table-log">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="tanggal">Tanggal</th>
                    <th class="topik">Topik</th>
                    <th class="pengirim">Pengirim</th>
                    <th class="status">Status</th>
                    <th class="keterangan">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($all_histori as $hist): ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td class="tanggal"><?php echo date('d/m/Y H:i', strtotime($hist['tanggal'])); ?></td>
                        <td class="topik"><?php echo htmlspecialchars($hist['topik']); ?></td>
                        <td class="pengirim"><?php echo htmlspecialchars($hist['pengirim']); ?></td>
                        <td class="status"><?php echo $hist['status'] === 'acc' ? 'ACC' : ($hist['status'] === 'revisi' ? 'REVISI' : ($hist['status'] === 'pending' ? 'PENDING' : '-')); ?></td>
                        <td class="keterangan"><?php echo nl2br(htmlspecialchars(str_replace(array("\r\n", "\r"), "\n", (string) $hist['keterangan'])), false); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        <p style="margin: 5px 0;"><strong>Tanggal Cetak:</strong> <?php echo date('d F Y H:i:s'); ?></p>
        <div style="margin-top: 30px;">
            <div style="text-align: left; width: 45%;">
                <p style="margin: 0;">Dosen Pembimbing</p>
                <p style="margin: 20px 0 5px 0; height: 40px;"></p>
                <p style="margin: 0;">
                    <?php if ($pembimbing): ?>
                        <strong style="text-decoration: underline;"><?php echo htmlspecialchars($pembimbing->nama_dosen); ?></strong>
                    <?php else: ?>
                        ..................................
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
