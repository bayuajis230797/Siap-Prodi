<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logbook PKL</title>
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
        .table-logbook {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8pt;
        }
        .table-logbook th,
        .table-logbook td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }
        .table-logbook th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
        }
        .table-logbook td.no {
            text-align: center;
            width: 4%;
        }
        .table-logbook td.tanggal {
            text-align: center;
            width: 10%;
        }
        .table-logbook td.jam {
            text-align: center;
            width: 12%;
        }
        .table-logbook td.aktivitas {
            width: 60%;
        }
        .table-logbook td.koordinat {
            text-align: center;
            width: 14%;
            font-size: 7pt;
        }
        .footer {
            margin-top: 15px;
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
        Logbook Praktik Kerja Lapangan (PKL)
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
            <?php if ($tempat_pkl): ?>
            <tr>
                <td class="label">Tempat PKL</td>
                <td colspan="3">: <?php echo htmlspecialchars($tempat_pkl->nama_instansi); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($pengajuan && $pengajuan->judul_pkl): ?>
            <tr>
                <td class="label">Judul PKL</td>
                <td colspan="3">: <?php echo htmlspecialchars($pengajuan->judul_pkl); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td colspan="3">: <?php echo date('d F Y H:i:s'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel Logbook -->
    <table class="table-logbook">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="tanggal">Tanggal</th>
                <th class="jam">Jam</th>
                <th class="aktivitas">Aktivitas</th>
                <th class="koordinat">Koordinat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logbook_list)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 15px;">
                        Belum ada data logbook.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($logbook_list as $logbook): ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td class="tanggal"><?php echo date('d/m/Y', strtotime($logbook->tanggal)); ?></td>
                        <td class="jam"><?php echo date('H:i', strtotime($logbook->jam_mulai)); ?> - <?php echo date('H:i', strtotime($logbook->jam_selesai)); ?></td>
                        <td class="aktivitas"><?php echo nl2br(htmlspecialchars($logbook->aktivitas)); ?></td>
                        <td class="koordinat">
                            <?php if ($logbook->latitude_logbook && $logbook->longitude_logbook): ?>
                                <?php echo number_format($logbook->latitude_logbook, 6); ?><br>
                                <?php echo number_format($logbook->longitude_logbook, 6); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p style="margin: 5px 0;">
            <strong>Tanggal Cetak:</strong> <?php echo date('d F Y H:i:s'); ?>
        </p>
        <div style="margin-top: 30px; text-align: left;">
            <p style="margin: 0;">Pembimbing Lapangan</p>
            <p style="margin: 20px 0 5px 0; height: 40px;"></p>
            <p style="margin: 0;">
                <?php if ($tempat_pkl && !empty($tempat_pkl->nama_pembimbing_lapangan)): ?>
                    <strong style="text-decoration: underline;"><?php echo htmlspecialchars($tempat_pkl->nama_pembimbing_lapangan); ?></strong>
                <?php else: ?>
                    ..................................
                <?php endif; ?>
            </p>
            <p style="margin: 20px 0 5px 0; height: 40px;"></p>
        </div>
    </div>
</body>
</html>

