<?php
$jenis_label = array(
    'mahasiswa' => 'Mahasiswa',
    'dosen' => 'Dosen',
    'campuran' => 'Mahasiswa + Dosen',
);
$items_count = isset($rekap['items']) ? count($rekap['items']) : 0;
$page_orient = $items_count > 2 ? 'landscape' : 'portrait';
$kode_label = $tt->jenis_penerima === 'dosen'
    ? 'NIDN'
    : ($tt->jenis_penerima === 'campuran' ? 'NIM/NIDN' : 'NIM');
?>
<style>
    @media print {
        @page {
            size: A4 <?php echo $page_orient; ?>;
            margin: 1cm;
        }
        body { margin: 0; padding: 0; }
        .no-print { display: none !important; }
        table.rekap { page-break-inside: auto; }
        table.rekap tr { page-break-inside: avoid; page-break-after: auto; }
        table.rekap thead { display: table-header-group; }
    }
    body {
        font-family: "Poppins", Arial, sans-serif;
        font-size: 9pt;
        margin: 0;
        padding: 8px;
        line-height: 1.25;
        color: #111;
    }
    .kop-surat { text-align: center; margin-bottom: 8px; }
    .kop-surat img { max-width: 100%; height: auto; max-height: 110px; }
    .judul {
        text-align: center;
        font-size: 12pt;
        font-weight: bold;
        margin: 6px 0 4px;
        text-transform: uppercase;
    }
    .subjudul {
        text-align: center;
        font-size: 10pt;
        margin-bottom: 10px;
    }
    .info {
        margin-bottom: 10px;
        font-size: 9pt;
    }
    .info table { border-collapse: collapse; }
    .info td { padding: 1px 6px 1px 0; vertical-align: top; }
    .info td.lbl { width: 120px; font-weight: bold; }
    table.rekap {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    table.rekap th,
    table.rekap td {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: middle;
    }
    table.rekap th {
        background: #f0f0f0;
        text-align: center;
        font-weight: bold;
    }
    .text-center { text-align: center; }
    .footer-note {
        margin-top: 10px;
        font-size: 8pt;
        color: #444;
    }
</style>

<div class="kop-surat">
    <?php echo get_kop_header(); ?>
</div>

<div class="judul">Rekap Penerimaan Tanda Terima</div>
<div class="subjudul"><?php echo htmlspecialchars($tt->nama_distribusi); ?></div>

<div class="info">
    <table>
        <tr>
            <td class="lbl">Jenis Penerima</td>
            <td>: <?php echo isset($jenis_label[$tt->jenis_penerima]) ? $jenis_label[$tt->jenis_penerima] : ucfirst($tt->jenis_penerima); ?></td>
        </tr>
        <tr>
            <td class="lbl">Status Distribusi</td>
            <td>: <?php echo ucfirst($tt->status); ?></td>
        </tr>
        <?php if (!empty($tt->tanggal_mulai) || !empty($tt->tanggal_selesai)): ?>
        <tr>
            <td class="lbl">Periode</td>
            <td>:
                <?php
                echo !empty($tt->tanggal_mulai) ? date('d/m/Y', strtotime($tt->tanggal_mulai)) : '—';
                echo ' s.d. ';
                echo !empty($tt->tanggal_selesai) ? date('d/m/Y', strtotime($tt->tanggal_selesai)) : '—';
                ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="lbl">Dicetak</td>
            <td>: <?php echo date('d/m/Y H:i'); ?> WIB</td>
        </tr>
    </table>
</div>

<table class="rekap">
    <thead>
        <tr>
            <th rowspan="2" class="align-middle" style="width:28px">No</th>
            <?php if ($tt->jenis_penerima === 'campuran'): ?>
                <th rowspan="2">Jenis</th>
            <?php endif; ?>
            <th rowspan="2"><?php echo $kode_label; ?></th>
            <th rowspan="2">Nama</th>
            <?php if ($tt->jenis_penerima !== 'dosen'): ?>
                <th rowspan="2">Kelas</th>
            <?php endif; ?>
            <?php foreach ($rekap['items'] as $it): ?>
                <th colspan="3"><?php echo htmlspecialchars($it->nama_barang); ?></th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <?php foreach ($rekap['items'] as $it): ?>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Jam</th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rekap['peserta'])): ?>
            <tr>
                <td colspan="<?php echo 4 + ($tt->jenis_penerima === 'campuran' ? 1 : 0) + ($tt->jenis_penerima !== 'dosen' ? 1 : 0) + (count($rekap['items']) * 3); ?>" class="text-center">
                    Belum ada peserta penerima.
                </td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach ($rekap['peserta'] as $p): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <?php if ($tt->jenis_penerima === 'campuran'): ?>
                        <td class="text-center"><?php echo isset($p->tipe_penerima) ? ucfirst($p->tipe_penerima) : '-'; ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($p->kode); ?></td>
                    <td><?php echo htmlspecialchars($p->nama); ?></td>
                    <?php if ($tt->jenis_penerima !== 'dosen'): ?>
                        <td class="text-center"><?php echo htmlspecialchars($p->kelas ?? ''); ?></td>
                    <?php endif; ?>
                    <?php foreach ($rekap['items'] as $it):
                        $ref_key = isset($p->ref_key) ? $p->ref_key : ((isset($p->tipe_penerima) && $p->tipe_penerima === 'dosen') ? 'd' : 'm') . (int) $p->ref_id;
                        $key = (int) $it->id . '_' . $ref_key;
                        $terima = isset($rekap['terima_map'][$key]) ? $rekap['terima_map'][$key] : null;
                        $sudah = $terima && !empty($terima->waktu_terima);
                    ?>
                        <td class="text-center"><?php echo $sudah ? 'Sudah' : 'Belum'; ?></td>
                        <td class="text-center"><?php echo $sudah ? date('d/m/Y', strtotime($terima->waktu_terima)) : '—'; ?></td>
                        <td class="text-center"><?php echo $sudah ? date('H:i', strtotime($terima->waktu_terima)) : '—'; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p class="footer-note">
    Dokumen ini dicetak dari Sistem Informasi Program Studi. Total penerima: <?php echo count($rekap['peserta']); ?>,
    total barang: <?php echo count($rekap['items']); ?>.
</p>
