<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Absensi PKL</title>
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
        .table-absensi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8pt;
        }
        .table-absensi th,
        .table-absensi td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }
        .table-absensi th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
        }
        .table-absensi td.no {
            text-align: center;
            width: 4%;
        }
        .table-absensi td.tanggal {
            text-align: center;
            width: 10%;
        }
        .table-absensi td.tipe {
            text-align: center;
            width: 12%;
        }
        .table-absensi td.waktu {
            text-align: center;
            width: 12%;
        }
        .table-absensi td.status {
            text-align: center;
            width: 20%;
        }
        .table-absensi td.jarak {
            text-align: center;
            width: 15%;
        }
        .footer {
            margin-top: 15px;
            font-size: 9pt;
        }
        .badge-danger {
            color: #dc3545;
            font-weight: bold;
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
        Absensi Harian Praktik Kerja Lapangan (PKL)
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
            <?php if ($statistik): ?>
            <tr>
                <td class="label">Total Hari</td>
                <td>: <?php echo $statistik->total_hari; ?> hari</td>
                <td class="label">Berangkat/Pulang</td>
                <td>: <?php echo $statistik->total_berangkat; ?> / <?php echo $statistik->total_pulang; ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td colspan="3">: <?php echo date('d F Y H:i:s'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel Absensi -->
    <table class="table-absensi">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="tanggal">Tanggal</th>
                <th class="tipe">Tipe</th>
                <th class="waktu">Waktu</th>
                <th class="status">Status</th>
                <th class="jarak">Jarak</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $tanggal_list = array();
            foreach ($absensi_list as $absen) {
                if (!isset($tanggal_list[$absen->tanggal])) {
                    $tanggal_list[$absen->tanggal] = array('berangkat' => null, 'pulang' => null);
                }
                $tanggal_list[$absen->tanggal][$absen->tipe_absensi] = $absen;
            }
            krsort($tanggal_list);
            
            if (empty($tanggal_list)):
            ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px;">
                        Belum ada data absensi.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($tanggal_list as $tanggal => $absensi): ?>
                    <?php 
                    // Tampilkan baris untuk berangkat jika ada
                    if ($absensi['berangkat']):
                    ?>
                        <tr>
                            <td class="no"><?php echo $no++; ?></td>
                            <td class="tanggal"><?php echo date('d/m/Y', strtotime($tanggal)); ?></td>
                            <td class="tipe">Berangkat</td>
                            <td class="waktu"><?php echo date('H:i', strtotime($absensi['berangkat']->waktu_absen)); ?></td>
                            <td class="status">
                                <?php if ($absensi['berangkat']->status_radius === 'dalam_radius'): ?>
                                    Lengkap
                                <?php else: ?>
                                    <span class="badge-danger">Di Luar Radius</span>
                                <?php endif; ?>
                            </td>
                            <td class="jarak"><?php echo number_format($absensi['berangkat']->jarak_meter, 1); ?> m</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php 
                    // Tampilkan baris untuk pulang jika ada
                    if ($absensi['pulang']):
                    ?>
                        <tr>
                            <td class="no"><?php echo $no++; ?></td>
                            <td class="tanggal"><?php echo date('d/m/Y', strtotime($tanggal)); ?></td>
                            <td class="tipe">Pulang</td>
                            <td class="waktu"><?php echo date('H:i', strtotime($absensi['pulang']->waktu_absen)); ?></td>
                            <td class="status">
                                <?php if ($absensi['pulang']->status_radius === 'dalam_radius'): ?>
                                    Lengkap
                                <?php else: ?>
                                    <span class="badge-danger">Di Luar Radius</span>
                                <?php endif; ?>
                            </td>
                            <td class="jarak"><?php echo number_format($absensi['pulang']->jarak_meter, 1); ?> m</td>
                        </tr>
                    <?php endif; ?>
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

