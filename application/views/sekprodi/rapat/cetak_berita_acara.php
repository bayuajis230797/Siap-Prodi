<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Rapat - <?php echo htmlspecialchars($rapat->judul_rapat); ?></title>
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
            padding: 10px;
            line-height: 1.4;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 10px;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
        }
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .info-rapat {
            margin-bottom: 10px;
        }
        .info-rapat table {
            width: auto;
            border-collapse: collapse;
        }
        .info-rapat td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .info-rapat td.label {
            width: auto;
            min-width: 100px;
            font-weight: bold;
            padding-right: 10px;
        }
        .peserta-section {
            margin: 10px 0;
        }
        .peserta-section h4 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .table-peserta {
            width: auto;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .table-peserta th,
        .table-peserta td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: left;
        }
        .table-peserta th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 5px 8px;
        }
        .table-peserta td.no {
            text-align: center;
            width: auto;
            min-width: 30px;
        }
        .table-peserta td.nama {
            width: auto;
            min-width: 200px;
        }
        .table-peserta td.role {
            width: auto;
            min-width: 100px;
        }
        .table-peserta td.status {
            width: auto;
            min-width: 120px;
            text-align: center;
        }
        .notulen-section {
            margin: 10px 0;
        }
        .notulen-section h4 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .notulen-content {
            text-align: justify;
            padding: 8px;
            border: 1px solid #000;
            min-height: 80px;
        }
        .dokumentasi-section {
            margin: 10px 0;
        }
        .dokumentasi-section h4 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .foto-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
        .foto-item {
            flex: 0 0 calc(50% - 5px);
            text-align: center;
            page-break-inside: avoid;
        }
        .foto-item img {
            max-width: 100%;
            max-height: 120px;
            border: 1px solid #000;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        @media print {
            .foto-item img {
                max-height: 100px;
            }
        }
        .foto-item .caption {
            font-size: 8pt;
            margin-top: 3px;
            font-style: italic;
            word-wrap: break-word;
        }
        .ttd-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .ttd-box {
            width: 45%;
            text-align: center;
        }
        .ttd-box .label {
            font-weight: bold;
            margin-bottom: 50px;
        }
        .ttd-box .nama {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            margin-top: 15px;
            text-align: left;
            font-size: 9pt;
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
        Berita Acara Rapat
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
                    <?php endif; ?> WIB
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

    <!-- Daftar Peserta -->
    <div class="peserta-section">
        <h4>Daftar Peserta:</h4>
        <table class="table-peserta">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="nama">Nama</th>
                    <th class="role">Keterangan</th>
                    <th class="status">Status Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($peserta_list)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">
                            Belum ada peserta yang terdaftar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($peserta_list as $peserta): ?>
                        <tr>
                            <td class="no"><?php echo $no++; ?></td>
                            <td class="nama"><?php echo htmlspecialchars($peserta->nama_peserta); ?></td>
                            <td class="role">
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
                                echo htmlspecialchars($label);
                                ?>
                            </td>
                            <td class="status">
                                <?php
                                $status_labels = array(
                                    'hadir' => 'Hadir',
                                    'tidak_hadir' => 'Tidak Hadir',
                                    'izin' => 'Izin'
                                );
                                $status = isset($status_labels[$peserta->status_kehadiran]) 
                                    ? $status_labels[$peserta->status_kehadiran] 
                                    : ucfirst(str_replace('_', ' ', $peserta->status_kehadiran));
                                echo htmlspecialchars($status);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Notulen -->
    <div class="notulen-section">
        <h4>Notulen Rapat:</h4>
        <div class="notulen-content">
            <?php if ($rapat->notulen): ?>
                <?php echo nl2br(htmlspecialchars($rapat->notulen)); ?>
            <?php else: ?>
                <p style="color: #999; font-style: italic;">Tidak ada notulen yang dicatat.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dokumentasi Foto -->
    <?php if (!empty($foto_dokumentasi)): ?>
    <div class="dokumentasi-section">
        <h4>Dokumentasi Rapat:</h4>
        <div class="foto-container">
            <?php foreach ($foto_dokumentasi as $index => $foto): ?>
                <div class="foto-item">
                    <?php 
                    // Cari file yang benar-benar ada
                    $image_path = trim($foto->file_path);
                    $image_path = ltrim($image_path, '/');
                    $image_path = str_replace('\\', '/', $image_path);
                    
                    $final_image_path = $image_path;
                    
                    // Cek apakah file ada berdasarkan path di database
                    if (!empty($image_path)) {
                        $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $image_path);
                        if (!file_exists($full_path)) {
                            // Jika tidak ditemukan, cari file berdasarkan nama asli
                            $upload_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'rapat' . DIRECTORY_SEPARATOR;
                            if (is_dir($upload_dir)) {
                                // Cari file yang mungkin sesuai
                                $files = glob($upload_dir . '*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                                if (!empty($files)) {
                                    // Sort berdasarkan waktu modifikasi (terbaru dulu)
                                    usort($files, function($a, $b) {
                                        return filemtime($b) - filemtime($a);
                                    });
                                    
                                    // Ambil file berdasarkan urutan ID dokumentasi
                                    $file_index = isset($foto->id) ? (($foto->id - 1) % count($files)) : 0;
                                    if (isset($files[$file_index])) {
                                        $final_image_path = 'uploads/rapat/' . basename($files[$file_index]);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Pastikan file ada
                    $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $final_image_path);
                    if (file_exists($full_path)) {
                    ?>
                        <img src="<?php echo base_url($final_image_path); ?>" 
                             alt="Dokumentasi Rapat <?php echo $index + 1; ?>"
                             onerror="this.style.display='none';">
                    <?php } else { ?>
                        <div style="border: 1px solid #000; height: 120px; display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                            <span style="font-size: 9pt; color: #999;">Gambar tidak ditemukan</span>
                        </div>
                    <?php } ?>
                    <?php if ($foto->keterangan): ?>
                        <div class="caption"><?php echo htmlspecialchars($foto->keterangan); ?></div>
                    <?php else: ?>
                        <div class="caption">Foto <?php echo $index + 1; ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="ttd-section">
        <div class="ttd-box">
            <div class="label">Ka Prodi Sistem Informas</div>
            <div class="nama">
                <?php if (isset($kepala_prodi) && $kepala_prodi): ?>
                    <?php echo htmlspecialchars($kepala_prodi->nama_dosen); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="ttd-box">
            <div class="label">Sekretaris Prodi Sistem Informasi</div>
            <div class="nama">
                <?php if (isset($sekretaris_prodi) && $sekretaris_prodi): ?>
                    <?php echo htmlspecialchars($sekretaris_prodi->nama_dosen); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
    </div>
</body>
</html>

