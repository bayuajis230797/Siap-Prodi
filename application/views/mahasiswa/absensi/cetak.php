<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Kegiatan</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm;
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
            font-size: 11pt;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
        }
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .info-mahasiswa {
            margin-bottom: 20px;
        }
        .info-mahasiswa table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-mahasiswa td {
            padding: 5px;
            vertical-align: top;
        }
        .info-mahasiswa td.label {
            width: 150px;
            font-weight: bold;
        }
        .table-absensi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-absensi th,
        .table-absensi td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table-absensi th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .table-absensi td.no {
            text-align: center;
            width: 5%;
        }
        .table-absensi td.center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: left;
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
        Laporan Absensi Kegiatan
    </div>

    <!-- Info Mahasiswa -->
    <div class="info-mahasiswa">
        <table>
            <tr>
                <td class="label">NIM</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nim); ?></td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
            </tr>
            <tr>
                <td class="label">Kelas</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->kelas); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td>: <?php echo date('d F Y H:i:s'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel Absensi -->
    <table class="table-absensi">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama Kegiatan</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Lokasi</th>
                <th>Waktu Absen</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($absensi_list)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        Belum ada data absensi.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($absensi_list as $absen): ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($absen->nama_kegiatan); ?></td>
                        <td class="center"><?php echo date('d/m/Y', strtotime($absen->tanggal_kegiatan)); ?></td>
                        <td class="center"><?php echo date('H:i', strtotime($absen->jam_mulai)); ?></td>
                        <td><?php echo htmlspecialchars($absen->lokasi ? $absen->lokasi : '-'); ?></td>
                        <td class="center"><?php echo date('d/m/Y H:i', strtotime($absen->waktu_absen)); ?></td>
                        <td class="center">
                            <?php 
                            $status_labels = array(
                                'hadir' => 'Hadir',
                                'terlambat' => 'Terlambat',
                                'tidak_hadir' => 'Tidak Hadir'
                            );
                            echo isset($status_labels[$absen->status]) ? $status_labels[$absen->status] : $absen->status;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Tanggal Cetak:</strong> <?php echo date('d F Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>






