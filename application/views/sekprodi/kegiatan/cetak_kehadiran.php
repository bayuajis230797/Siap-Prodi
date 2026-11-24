<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Kegiatan - <?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm;
            }
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
            .table-kehadiran {
                border: 2px solid #000 !important;
                page-break-inside: auto !important;
            }
            .table-kehadiran thead {
                display: table-header-group !important;
            }
            .table-kehadiran thead tr {
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
            .table-kehadiran tbody {
                display: table-row-group !important;
            }
            .table-kehadiran tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .table-kehadiran th,
            .table-kehadiran td {
                border: 1px solid #000 !important;
                page-break-inside: avoid !important;
                overflow: visible !important;
            }
            .table-kehadiran th {
                background-color: #f0f0f0 !important;
            }
            .kop-surat {
                page-break-after: avoid !important;
                margin-bottom: 10px !important;
            }
            .judul {
                page-break-after: avoid !important;
                margin: 10px 0 !important;
            }
            .info-kegiatan {
                page-break-after: avoid !important;
                margin-bottom: 10px !important;
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
            padding: 20px;
            line-height: 1.4;
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
            font-size: 12pt;
            font-weight: bold;
            margin: 12px 0;
            text-transform: uppercase;
        }
        .info-kegiatan {
            margin-bottom: 15px;
        }
        .info-kegiatan table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-kegiatan td {
            padding: 8px 5px;
            vertical-align: top;
        }
        .info-kegiatan td.label {
            width: 150px;
            font-weight: bold;
        }
        .table-kehadiran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 2px solid #000;
        }
        .table-kehadiran th,
        .table-kehadiran td {
            border: 1px solid #000;
            padding: 6px 5px;
            text-align: left;
            vertical-align: middle;
            font-size: 9pt;
        }
        .table-kehadiran th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        .table-kehadiran td {
            text-align: left;
            border: 1px solid #000;
        }
        .table-kehadiran td.center {
            text-align: center;
        }
        .table-kehadiran tbody tr {
            border: 1px solid #000;
        }
        .table-kehadiran tbody tr:last-child {
            border-bottom: 2px solid #000;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer td {
            padding: 5px;
        }
        .footer td.right {
            text-align: right;
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
        Laporan Kehadiran Kegiatan
    </div>

    <!-- Info Kegiatan -->
    <div class="info-kegiatan">
        <table>
            <tr>
                <td class="label">Nama Kegiatan:</td>
                <td><?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal:</td>
                <td><?php echo date('d F Y', strtotime($kegiatan->tanggal_kegiatan)); ?></td>
            </tr>
            <tr>
                <td class="label">Waktu:</td>
                <td>
                    <?php echo date('H:i', strtotime($kegiatan->jam_mulai)); ?> WIB
                    <?php if ($kegiatan->jam_selesai): ?>
                        - <?php echo date('H:i', strtotime($kegiatan->jam_selesai)); ?> WIB
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($kegiatan->lokasi): ?>
            <tr>
                <td class="label">Lokasi:</td>
                <td><?php echo htmlspecialchars($kegiatan->lokasi); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Jenis Kegiatan:</td>
                <td><?php echo ucfirst(str_replace('_', ' ', $kegiatan->jenis_kegiatan)); ?></td>
            </tr>
        </table>
    </div>


    <!-- Tabel Kehadiran -->
    <table class="table-kehadiran">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIM</th>
                <th style="width: 35%;">Nama Mahasiswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 20%;">Waktu Absen</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kegiatan->peserta)): ?>
                <tr>
                    <td colspan="6" class="center" style="padding: 20px;">Tidak ada peserta.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($kegiatan->peserta as $peserta): ?>
                    <tr>
                        <td class="center" style="font-weight: bold;"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($peserta->nim); ?></td>
                        <td><?php echo htmlspecialchars($peserta->nama_mahasiswa); ?></td>
                        <td class="center"><?php echo htmlspecialchars($peserta->kelas); ?></td>
                        <td class="center">
                            <?php if (isset($peserta->absensi) && $peserta->absensi): ?>
                                <?php echo date('d/m/Y H:i', strtotime($peserta->absensi->waktu_absen)); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php 
                            $status = $peserta->status_absen ?? 'tidak_hadir';
                            if ($status == 'hadir') {
                                echo 'Hadir';
                            } elseif ($status == 'terlambat') {
                                echo 'Terlambat';
                            } else {
                                echo 'Tidak Hadir';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <table>
            <tr>
                <td>
                    <strong>Dicetak pada:</strong> <?php echo date('d F Y H:i:s'); ?>
                </td>
                <td class="right">
                    <strong>Halaman 1</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tombol Print (No Print) -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; font-size: 14pt; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 5px;">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" class="btn btn-secondary" style="padding: 10px 20px; font-size: 14pt; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <script>
        // Auto print saat halaman load (opsional, bisa di-comment jika tidak diinginkan)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

