<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir Rapat</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
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
        }
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
        }
        .info-rapat {
            margin-bottom: 10px;
        }
        .info-rapat table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-rapat td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .info-rapat td.label {
            width: 120px;
            font-weight: bold;
        }
        .table-daftar-hadir {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-daftar-hadir th,
        .table-daftar-hadir td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .table-daftar-hadir th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .table-daftar-hadir td.no {
            text-align: center;
            width: 5%;
        }
        .table-daftar-hadir td.nama {
            width: 60%;
        }
        .table-daftar-hadir td.ttd {
            width: 35%;
            text-align: center;
            height: 35px;
        }
        .footer {
            margin-top: 15px;
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
        Daftar Hadir Rapat
    </div>

    <!-- Info Rapat -->
    <div class="info-rapat">
        <table>
            <tr>
                <td class="label">Judul Rapat</td>
                <td>: <?php echo htmlspecialchars($rapat->judul_rapat); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td>: <?php echo date('d F Y', strtotime($rapat->tanggal_rapat)); ?></td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td>: <?php echo date('H:i', strtotime($rapat->jam_mulai)); ?>
                    <?php if ($rapat->jam_selesai): ?>
                        - <?php echo date('H:i', strtotime($rapat->jam_selesai)); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($rapat->tempat): ?>
            <tr>
                <td class="label">Tempat</td>
                <td>: <?php echo htmlspecialchars($rapat->tempat); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Tabel Daftar Hadir -->
    <table class="table-daftar-hadir">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="nama">Nama</th>
                <th class="ttd">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($peserta_list)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px;">
                        Belum ada peserta yang terdaftar.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($peserta_list as $peserta): ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td class="nama">
                            <?php echo htmlspecialchars($peserta->nama_peserta); ?>
                            <?php 
                            // Gunakan label_role jika ada, jika tidak gunakan default
                            $label = '';
                            if (!empty($peserta->label_role)) {
                                $label = $peserta->label_role;
                            } elseif ($peserta->role_peserta == 'dosen') {
                                $label = 'Dosen';
                            } elseif ($peserta->role_peserta == 'tendik') {
                                $label = 'Tendik';
                            } elseif ($peserta->role_peserta == 'mahasiswa') {
                                $label = 'Mahasiswa';
                            } elseif ($peserta->role_peserta == 'eksternal') {
                                $label = 'Eksternal';
                            }
                            if ($label): ?>
                                <br><small style="font-style: italic;">(<?php echo htmlspecialchars($label); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td class="ttd"></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
</body>
</html>

