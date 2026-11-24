<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Sidang PKL</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm 2cm;
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
            padding: 15px;
            line-height: 1.6;
            text-align: justify;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 15px;
        }
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .pembukaan {
            margin: 15px 0;
            text-indent: 30px;
            font-size: 11pt;
        }
        .info-section {
            margin: 15px 0;
            font-size: 11pt;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-section td.label {
            width: 180px;
            font-weight: bold;
        }
        .table-penilaian {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        .table-penilaian th,
        .table-penilaian td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .table-penilaian th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 8px;
        }
        .table-penilaian td {
            text-align: left;
        }
        .table-penilaian td.center {
            text-align: center;
        }
        .table-penilaian td.aspek {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .table-penilaian td.item {
            padding-left: 25px;
        }
        .nilai-akhir {
            margin: 20px 0;
            padding: 12px;
            background-color: #f0f0f0;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .nilai-akhir table {
            width: 100%;
            border-collapse: collapse;
        }
        .nilai-akhir td {
            padding: 5px;
        }
        .nilai-akhir td.label {
            font-weight: bold;
            width: 200px;
        }
        .catatan-revisi {
            margin: 20px 0;
            padding: 12px;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .catatan-revisi h4 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .catatan-revisi-content {
            padding: 10px;
            background-color: #fff9e6;
            border: 1px solid #ddd;
            min-height: 100px;
            text-align: justify;
            line-height: 1.6;
        }
        .ttd-section {
            margin-top: 40px;
            font-size: 11pt;
        }
        .ttd-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .ttd-section td {
            width: 50%;
            vertical-align: top;
            padding: 15px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .ttd-nidn {
            margin-top: 5px;
            font-size: 10pt;
        }
        .penutup {
            margin: 20px 0;
            text-indent: 30px;
            font-size: 11pt;
        }
        .tanggal-lokasi {
            margin: 20px 0;
            text-align: right;
            font-size: 11pt;
        }
        .status-kelulusan {
            margin: 20px 0;
            padding: 12px;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .status-kelulusan h4 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .checkbox-item {
            margin: 8px 0;
            font-size: 11pt;
        }
        .checkbox-box {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            margin-right: 10px;
            vertical-align: middle;
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
        Berita Acara Sidang PKL
    </div>

    <!-- Pembukaan -->
    <div class="pembukaan">
        Pada hari ini <?php 
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        if ($jadwal_sidang) {
            $tanggal_sidang = strtotime($jadwal_sidang->tanggal);
            echo $hari[date('w', $tanggal_sidang)] . ', tanggal ' . date('d', $tanggal_sidang) . ' ' . $bulan[date('n', $tanggal_sidang)] . ' ' . date('Y', $tanggal_sidang);
        } else {
            echo date('l, d F Y');
        }
        ?>, telah dilaksanakan Sidang Praktik Kerja Lapangan (PKL) dengan ketentuan sebagai berikut:
    </div>

    <!-- Info Mahasiswa -->
    <div class="info-section">
        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->nim); ?></td>
            </tr>
            <tr>
                <td class="label">Kelas</td>
                <td>: <?php echo htmlspecialchars($mahasiswa->kelas); ?></td>
            </tr>
            <tr>
                <td class="label">Judul PKL</td>
                <td>: <?php echo htmlspecialchars($pengajuan->judul_pkl); ?></td>
            </tr>
            <?php if ($jadwal_sidang): ?>
            <tr>
                <td class="label">Waktu Pelaksanaan</td>
                <td>: <?php echo date('H:i', strtotime($jadwal_sidang->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal_sidang->jam_selesai)); ?> WIB</td>
            </tr>
            <?php if ($jadwal_sidang->ruangan): ?>
            <tr>
                <td class="label">Tempat/Ruangan</td>
                <td>: <?php echo htmlspecialchars($jadwal_sidang->ruangan); ?></td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
            <tr>
                <td class="label">Tahun Akademik</td>
                <td>: <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' - ' . $tahun_aktif->semester); ?></td>
            </tr>
            <?php if ($dosen_penguji): ?>
            <tr>
                <td class="label">Dosen Penguji</td>
                <td>: <?php echo htmlspecialchars($dosen_penguji->nama_dosen); ?> (NIDN: <?php echo htmlspecialchars($dosen_penguji->nidn); ?>)</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Isi Berita Acara -->
    <div class="pembukaan">
        Setelah melakukan evaluasi dan penilaian terhadap presentasi dan laporan PKL yang disampaikan oleh mahasiswa, maka Dosen Penguji memberikan penilaian sebagai berikut:
    </div>

    <!-- Tabel Penilaian dari Penguji -->
    <?php if ($penilaian_by_tipe['penguji']): ?>
    <div style="margin-top: 15px;">
        <h4 style="font-size: 12pt; font-weight: bold; margin-bottom: 10px; text-align: center;">Rincian Penilaian dari Dosen Penguji</h4>
        <table class="table-penilaian">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Aspek Penilaian</th>
                    <th style="width: 50%;">Item Penilaian</th>
                    <th style="width: 10%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $total_nilai_penguji = 0;
                foreach ($aspek_list as $aspek): 
                    // Ambil nilai aspek dari penguji
                    $field = 'nilai_' . strtolower($aspek->kode_aspek);
                    $nilai_aspek_penguji = isset($penilaian_by_tipe['penguji']->$field) ? floatval($penilaian_by_tipe['penguji']->$field) : 0;
                    $total_nilai_penguji += $nilai_aspek_penguji;
                ?>
                    <tr>
                        <td class="center aspek" rowspan="<?php echo !empty($aspek->items) ? count($aspek->items) + 1 : 1; ?>"><?php echo $no++; ?></td>
                        <td class="aspek" rowspan="<?php echo !empty($aspek->items) ? count($aspek->items) + 1 : 1; ?>">
                            <strong><?php echo htmlspecialchars($aspek->kode_aspek . ' - ' . $aspek->nama_aspek); ?></strong>
                        </td>
                        <td class="center" colspan="2">
                            <strong>Nilai Aspek: <?php echo number_format($nilai_aspek_penguji, 2); ?></strong>
                        </td>
                    </tr>
                    <?php if (!empty($aspek->items)): ?>
                        <?php foreach ($aspek->items as $item): ?>
                            <?php
                            // Ambil nilai item dari detail penilaian penguji
                            $nilai_item_penguji = '-';
                            if (!empty($penilaian_by_tipe['penguji']->details)) {
                                foreach ($penilaian_by_tipe['penguji']->details as $detail) {
                                    if ($detail->id_item_penilaian == $item->id) {
                                        $nilai_item_penguji = number_format($detail->nilai, 2);
                                        break;
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td class="item"><?php echo htmlspecialchars($item->pertanyaan); ?></td>
                                <td class="center"><?php echo $nilai_item_penguji; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Nilai Akhir dari Penguji -->
    <div class="nilai-akhir">
        <h4 style="font-size: 12pt; font-weight: bold; margin: 0 0 10px 0;">Rekapitulasi Nilai dari Dosen Penguji</h4>
        <table>
            <?php
            $p1 = floatval($penilaian_by_tipe['penguji']->nilai_p1);
            $p2 = floatval($penilaian_by_tipe['penguji']->nilai_p2);
            $p3 = floatval($penilaian_by_tipe['penguji']->nilai_p3);
            $p4 = floatval($penilaian_by_tipe['penguji']->nilai_p4);
            $p5 = floatval($penilaian_by_tipe['penguji']->nilai_p5);
            $p6 = floatval($penilaian_by_tipe['penguji']->nilai_p6);
            
            $nilai_akhir_penguji = hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6);
            $nilai_huruf_penguji = konversi_nilai_huruf_pkl($nilai_akhir_penguji);
            ?>
            <tr>
                <td class="label">P1 (Aspek 1)</td>
                <td>: <?php echo number_format($p1, 2); ?></td>
            </tr>
            <tr>
                <td class="label">P2 (Aspek 2)</td>
                <td>: <?php echo number_format($p2, 2); ?></td>
            </tr>
            <tr>
                <td class="label">P3 (Aspek 3)</td>
                <td>: <?php echo number_format($p3, 2); ?></td>
            </tr>
            <tr>
                <td class="label">P4 (Aspek 4)</td>
                <td>: <?php echo number_format($p4, 2); ?></td>
            </tr>
            <tr>
                <td class="label">P5 (Aspek 5)</td>
                <td>: <?php echo number_format($p5, 2); ?></td>
            </tr>
            <tr>
                <td class="label">P6 (Aspek 6)</td>
                <td>: <?php echo number_format($p6, 2); ?></td>
            </tr>
            <tr>
                <td class="label"><strong>Nilai Akhir</strong></td>
                <td>: <strong><?php echo number_format($nilai_akhir_penguji, 2); ?></strong></td>
            </tr>
            <tr>
                <td class="label"><strong>Nilai Huruf (Grade)</strong></td>
                <td>: <strong><?php echo $nilai_huruf_penguji; ?></strong></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- Status Kelulusan -->
    <div class="status-kelulusan">
        <h4>Keputusan Sidang</h4>
        <div class="checkbox-item">
            <span class="checkbox-box"></span> LULUS
        </div>
        <div class="checkbox-item">
            <span class="checkbox-box"></span> LULUS DENGAN PERBAIKAN
        </div>
        <div class="checkbox-item">
            <span class="checkbox-box"></span> TIDAK LULUS
        </div>
    </div>

    <!-- Catatan Revisi dari Penguji -->
    <div class="catatan-revisi">
        <h4>Rincian Revisi yang Harus Dilakukan Mahasiswa</h4>
        <div class="catatan-revisi-content">
            <?php 
            // Cek catatan revisi dengan lebih teliti
            $ada_catatan = false;
            $teks_catatan = '';
            
            if ($catatan_revisi && isset($catatan_revisi->catatan)) {
                $teks_catatan = trim($catatan_revisi->catatan);
                if (!empty($teks_catatan) && $teks_catatan !== '' && $teks_catatan !== null) {
                    $ada_catatan = true;
                }
            }
            
            // Jika tidak ada di catatan_revisi, coba ambil dari penilaian_by_tipe
            if (!$ada_catatan && !empty($penilaian_by_tipe['penguji'])) {
                $penilaian_penguji = $penilaian_by_tipe['penguji'];
                if (isset($penilaian_penguji->catatan)) {
                    $teks_catatan = trim($penilaian_penguji->catatan);
                    if (!empty($teks_catatan) && $teks_catatan !== '' && $teks_catatan !== null) {
                        $ada_catatan = true;
                        $catatan_revisi = $penilaian_penguji;
                    }
                }
            }
            
            if ($ada_catatan): 
            ?>
                <?php echo nl2br(htmlspecialchars($teks_catatan)); ?>
            <?php else: ?>
                <em>Tidak ada catatan revisi dari Dosen Penguji.</em>
            <?php endif; ?>
        </div>
        <?php if ($ada_catatan && $catatan_revisi): ?>
        <div style="margin-top: 10px; font-size: 10pt;">
            <strong>Dari:</strong> <?php echo htmlspecialchars($catatan_revisi->penilai_nama); ?> 
            <?php if (isset($catatan_revisi->penilai_nidn) && $catatan_revisi->penilai_nidn && $catatan_revisi->penilai_nidn !== '-'): ?>
                (NIDN: <?php echo htmlspecialchars($catatan_revisi->penilai_nidn); ?>)
            <?php endif; ?>
            <?php if (isset($catatan_revisi->tanggal_penilaian) && $catatan_revisi->tanggal_penilaian): ?>
            <br>
            <strong>Tanggal Penilaian:</strong> <?php echo date('d F Y', strtotime($catatan_revisi->tanggal_penilaian)); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Penutup -->
    <div class="penutup">
        Demikian berita acara pelaksanaan sidang PKL ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- Tanda Tangan -->
    <div class="ttd-section">
        <table>
            <tr>
                <td>
                    <div style="text-align: center;">
                        <div style="margin-bottom: 5px; font-weight: bold;">Mahasiswa</div>
                        <div style="margin-top: 100px;">
                            <div class="ttd-name"><?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></div>
                            <div class="ttd-nidn">NIM: <?php echo htmlspecialchars($mahasiswa->nim); ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="text-align: center;">
                        <div style="margin-bottom: 5px; font-weight: bold;">Dosen Penguji</div>
                        <div style="margin-top: 100px;">
                            <?php if ($dosen_penguji): ?>
                                <div class="ttd-name"><?php echo htmlspecialchars($dosen_penguji->nama_dosen); ?></div>
                                <div class="ttd-nidn">NIDN: <?php echo htmlspecialchars($dosen_penguji->nidn); ?></div>
                            <?php else: ?>
                                <div class="ttd-name" style="min-height: 60px;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div style="margin-top: 20px; font-size: 9pt; text-align: center; color: #666;">
        <p>Dokumen ini dicetak secara otomatis pada: <?php echo date('d F Y, H:i:s'); ?></p>
    </div>
</body>
</html>
