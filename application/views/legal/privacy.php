<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi | SIHARKAT</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 16px; line-height: 1.6; color: #222; }
        h1 { font-size: 1.6rem; }
        h2 { font-size: 1.15rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <h1>Kebijakan Privasi — SIHARKAT</h1>
    <p>Terakhir diperbarui: <?php echo date('d/m/Y'); ?></p>
    <p>
        SIHARKAT (Sistem Informasi Program Studi) dikelola untuk keperluan akademik internal.
        Kebijakan ini menjelaskan bagaimana data digunakan saat menghubungkan Google Drive.
    </p>

    <h2>1. Data yang diakses</h2>
    <ul>
        <li>Alamat email akun Google yang dihubungkan (oleh admin).</li>
        <li>Akses terbatas ke Google Drive (<code>drive.file</code>): hanya file/folder yang dibuat oleh aplikasi SIHARKAT.</li>
    </ul>

    <h2>2. Tujuan penggunaan</h2>
    <p>
        File akademik (proposal, skripsi, PKL, berkas, dll.) disimpan ke Google Drive institusi
        agar tidak memenuhi penyimpanan server lokal. Data tidak dijual ke pihak ketiga.
    </p>

    <h2>3. Siapa yang mengelola</h2>
    <p>
        Hanya administrator sistem yang menghubungkan akun Google Drive institusi.
        Pengguna lain mengunggah melalui SIHARKAT tanpa login Google pribadi.
    </p>

    <h2>4. Penyimpanan &amp; keamanan</h2>
    <p>
        Token OAuth disimpan terenkripsi di server aplikasi. Admin dapat memutus koneksi
        dan mengganti akun Drive sewaktu-waktu.
    </p>

    <h2>5. Kontak</h2>
    <p>
        Pertanyaan terkait privasi: hubungi pengelola SIHARKAT /
        email developer yang tertera di Google Cloud Console.
    </p>
</body>
</html>
