<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histori Bimbingan PKL</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm 1.5cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Regular.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Regular.woff'); ?>') format('woff');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Bold.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Bold.woff'); ?>') format('woff');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        body {
            font-family: "Poppins", Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 10px;
            line-height: 1.3;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 8px;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
            max-height: 100px;
        }
        .judul {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
        }
        .info-mahasiswa {
            margin-bottom: 8px;
            font-size: 8pt;
        }
        .info-mahasiswa table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-mahasiswa td {
            padding: 2px 5px;
            vertical-align: top;
        }
        .info-mahasiswa td.label {
            width: 120px;
            font-weight: bold;
        }
        .statistik {
            margin: 10px 0;
            font-size: 9pt;
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .statistik table {
            width: 100%;
            border-collapse: collapse;
        }
        .statistik td {
            padding: 3px 5px;
        }
        .table-log {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8pt;
        }
        .table-log th,
        .table-log td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }
        .table-log th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
        }
        .table-log td.no {
            text-align: center;
            width: 3%;
        }
        .table-log td.tanggal {
            text-align: center;
            width: 10%;
        }
        .table-log td.topik {
            width: 18%;
            word-wrap: break-word;
            white-space: normal;
        }
        .table-log td.pengirim {
            width: 10%;
            text-align: center;
        }
        .table-log td.status {
            text-align: center;
            width: 7%;
        }
        .table-log td.keterangan {
            width: 52%;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            line-height: 1.35;
        }
        .footer {
            margin-top: 20px;
            font-size: 9pt;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <div class="kop-surat">
        <?php echo get_kop_header(); ?>
    </div>

    <!-- Judul -->
    <div class="judul">
        Histori Bimbingan Praktik Kerja Lapangan (PKL)
    </div>

    <!-- Info Mahasiswa -->
    <div class="info-mahasiswa">
        <table>
            <tr>
                <td class="label">NIM</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nim); ?></td>
                <td class="label" style="width: 100px;">Kelas</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->kelas ?: '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
                <td class="label">Tahun Akademik</td>
                <td>: <?php echo $tahun_aktif ? htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)) : '-'; ?></td>
            </tr>
            <?php if ($pengajuan && $pengajuan->judul_pkl): ?>
            <tr>
                <td class="label">Judul PKL</td>
                <td colspan="3">: <?php echo htmlspecialchars($pengajuan->judul_pkl); ?></td>
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

    <!-- Statistik -->
    <div class="statistik">
        <table>
            <tr>
                <td><strong>Total Topik Bimbingan:</strong> <?php echo $statistik->total_topik; ?></td>
                <td><strong>Total Sesi Bimbingan:</strong> <?php echo $statistik->total_log; ?></td>
                <td><strong>Jumlah ACC:</strong> <?php echo $statistik->total_acc; ?></td>
                <td><strong>Jumlah Revisi:</strong> <?php echo $statistik->total_revisi; ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel gabungan: histori pengajuan judul (kolom Topik = label kegiatan; teks judul di Keterangan) + log bimbingan laporan -->
    <?php
    $all_histori = array();
    if (!empty($histori_pengajuan)) {
        foreach ($histori_pengajuan as $hist) {
            $all_histori[] = array(
                'tanggal' => $hist['tanggal'],
                'topik' => $hist['topik'],
                'pengirim' => $hist['pengirim'],
                'status' => $hist['status'],
                'keterangan' => $hist['keterangan'],
            );
        }
    }
    foreach ($all_logs as $log_group):
        $bimbingan = $log_group['bimbingan'];
        $logs = $log_group['logs'];
        foreach ($logs as $log):
            $all_histori[] = array(
                'tanggal' => $log->created_at,
                'topik' => $bimbingan->judul_topik,
                'pengirim' => isset($log->nama_pengirim) ? $log->nama_pengirim : ($log->role_pengguna === 'mahasiswa' ? $mahasiswa->nama_mahasiswa : ($pembimbing ? $pembimbing->nama_dosen : 'Dosen')),
                'status' => $log->status_revisi,
                'keterangan' => $log->keterangan_revisi ?: $log->pesan ?: '-',
            );
        endforeach;
    endforeach;

    usort($all_histori, function ($a, $b) {
        return strtotime($a['tanggal']) - strtotime($b['tanggal']);
    });

    $ada_isi = !empty($all_histori);
    ?>

    <?php if (!$ada_isi): ?>
        <p style="text-align: center; padding: 15px; font-style: italic;">
            Belum ada data histori bimbingan.
        </p>
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
                <?php
                $no = 1;
                foreach ($all_histori as $hist):
                ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td class="tanggal"><?php echo date('d/m/Y H:i', strtotime($hist['tanggal'])); ?></td>
                        <td class="topik"><?php echo htmlspecialchars($hist['topik']); ?></td>
                        <td class="pengirim"><?php echo htmlspecialchars($hist['pengirim']); ?></td>
                        <td class="status">
                            <?php
                            if ($hist['status'] === 'acc'):
                                echo 'ACC';
                            elseif ($hist['status'] === 'revisi'):
                                echo 'REVISI';
                            elseif ($hist['status'] === 'pending'):
                                echo 'PENDING';
                            else:
                                echo '-';
                            endif;
                            ?>
                        </td>
                        <td class="keterangan">
                            <?php
                            $keterangan = isset($hist['keterangan']) ? (string) $hist['keterangan'] : '-';
                            $keterangan = str_replace(array("\r\n", "\r"), "\n", $keterangan);
                            echo nl2br(htmlspecialchars($keterangan), false);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p style="margin: 5px 0;">
            <strong>Tanggal Cetak:</strong> <?php echo date('d F Y H:i:s'); ?>
        </p>
        <div style="margin-top: 30px; text-align: left;">
            <p style="margin: 0;">Dosen Pembimbing</p>
            <p style="margin: 20px 0 5px 0; height: 40px;"></p>
            <p style="margin: 0;">
                <?php if ($pembimbing): ?>
                    <strong style="text-decoration: underline;"><?php echo htmlspecialchars($pembimbing->nama_dosen); ?></strong>
                <?php else: ?>
                    ..................................
                <?php endif; ?>
            </p>
            <p style="margin: 20px 0 5px 0; height: 40px;"></p>
        </div>
    </div>
</body>
</html>

