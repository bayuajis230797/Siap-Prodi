<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir UAS - <?php echo htmlspecialchars($jadwal->nama_mk); ?></title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
            .table-daftar-hadir {
                border: 2px solid #000 !important;
                page-break-inside: auto !important;
            }
            .table-daftar-hadir thead {
                display: table-header-group !important;
            }
            .table-daftar-hadir th,
            .table-daftar-hadir td {
                border: 1px solid #000 !important;
                padding: 2px 4px !important;
            }
            .table-daftar-hadir th {
                background-color: #f0f0f0 !important;
            }
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Regular.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Regular.woff'); ?>') format('woff');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo base_url('Poppins/Poppins-Bold.woff2'); ?>') format('woff2'),
                 url('<?php echo base_url('Poppins/Poppins-Bold.woff'); ?>') format('woff');
            font-weight: 700;
            font-style: normal;
        }
        body {
            font-family: "Poppins", Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 3px;
            line-height: 1.1;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 3px;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
        }
        .judul {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 3px 0;
            text-transform: uppercase;
        }
        .info-jadwal {
            margin-bottom: 3px;
            font-size: 9pt;
        }
        .info-jadwal table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-jadwal table td {
            padding: 1px 4px;
            vertical-align: top;
        }
        .info-jadwal table td:first-child {
            width: 150px;
            font-weight: bold;
        }
        .table-daftar-hadir {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            margin-top: 0;
            border: 2px solid #000;
        }
        .table-daftar-hadir th,
        .table-daftar-hadir td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: left;
            font-size: 9pt;
        }
        .table-daftar-hadir th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .table-daftar-hadir td {
            vertical-align: middle;
            line-height: 1.1;
        }
        .table-daftar-hadir .no {
            width: 5%;
            text-align: center;
        }
        .table-daftar-hadir .nama {
            width: 35%;
        }
        .table-daftar-hadir .nim {
            width: 20%;
            text-align: center;
        }
        .table-daftar-hadir .ttd {
            width: 40%;
        }
        .ttd-section {
            margin-top: 5px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            font-size: 9pt;
        }
        .ttd-box {
            width: 45%;
            text-align: center;
        }
        .ttd-box .label {
            margin-bottom: 40px;
            font-weight: bold;
        }
        .ttd-box .nama {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
            min-height: 30px;
        }
        .qrcode-container {
            margin-top: 5px !important;
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
        Daftar Hadir Ujian Akhir Semester (UAS)
    </div>

    <!-- Info Jadwal -->
    <div class="info-jadwal">
        <table>
            <tr>
                <td>Mata Kuliah</td>
                <td>: <?php echo htmlspecialchars($jadwal->kode_mk); ?> - <?php echo htmlspecialchars($jadwal->nama_mk); ?></td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: <?php echo htmlspecialchars($jadwal->kelas); ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: <?php echo date('d F Y', strtotime($jadwal->tanggal)); ?></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?> WIB</td>
            </tr>
            <tr>
                <td>Ruangan</td>
                <td>: <?php echo htmlspecialchars($jadwal->nama_ruang ? $jadwal->nama_ruang : '-'); ?></td>
            </tr>
            <tr>
                <td>Sesi</td>
                <td>: <?php echo $jadwal->sesi; ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel Daftar Hadir -->
    <table class="table-daftar-hadir">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="nama">Nama</th>
                <th class="nim">NIM</th>
                <th class="ttd">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mahasiswa_list)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada mahasiswa</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($mahasiswa_list as $mhs): ?>
                    <?php 
                    $kehadiran = isset($kehadiran_map[$mhs->id]) ? $kehadiran_map[$mhs->id] : null;
                    ?>
                    <tr>
                        <td class="no" style="text-align: center;"><?php echo $no; ?></td>
                        <td class="nama"><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                        <td class="nim" style="text-align: center;"><?php echo htmlspecialchars($mhs->nim); ?></td>
                        <td class="ttd" style="height: 20px; position: relative;">
                            <?php 
                            // Pola zigzag: nomor ganjil di kiri, nomor genap di tengah
                            $nomor_ttd = $no;
                            if ($nomor_ttd % 2 == 1) {
                                // Nomor ganjil: di kiri
                                echo '<span style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); font-weight: bold;">' . $nomor_ttd . '</span>';
                            } else {
                                // Nomor genap: di tengah
                                echo '<span style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-weight: bold;">' . $nomor_ttd . '</span>';
                            }
                            $no++;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd-section">
        <div class="ttd-box">
            <div class="label">Pengawas</div>
            <?php if (!empty($pengawas)): ?>
                <?php foreach ($pengawas as $idx => $p): ?>
                    <div class="nama">
                        <?php echo htmlspecialchars(isset($p->nama_dosen) ? $p->nama_dosen : (isset($p->nama_tendik) ? $p->nama_tendik : '-')); ?>
                    </div>
                    <?php if ($idx < count($pengawas) - 1): ?>
                        <br>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="nama">-</div>
            <?php endif; ?>
            <!-- QR Code di bawah TTD Pengawas -->
            <div class="qrcode-container" style="margin-top: 5px; text-align: center;">
                <?php
                $this->load->helper('qrcode');
                $qr_code_src = qr_code_embed_jadwal_uas($jadwal);
                ?>
                <?php if ($qr_code_src !== ''): ?>
                <img src="<?php echo $qr_code_src; ?>" alt="QR Code Jadwal" style="width: 100px; height: 100px; border: 1px solid #ddd;">
                <?php else: ?>
                <div style="width: 100px; height: 100px; border: 1px dashed #999; margin: 0 auto; line-height: 100px; font-size: 8pt; color: #999;">QR tidak tersedia</div>
                <?php endif; ?>
                <div style="font-size: 7pt; margin-top: 3px; color: #666;">Scan untuk absensi</div>
            </div>
        </div>
        <div class="ttd-box">
            <div class="label">Perwakilan Mahasiswa</div>
            <div class="nama">..............</div>
        </div>
    </div>
</body>
</html>

