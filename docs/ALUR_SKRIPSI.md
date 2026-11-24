# Alur Skripsi - SI-HARKAT

Dokumen ini menjelaskan alur lengkap proses skripsi dalam Sistem Informasi Administrasi Prodi (SI-HARKAT), dari registrasi hingga klaim nilai akhir.

---

## Diagram Alur (Flowchart)

```
┌─────────────────────┐
│  1. Registrasi      │  ← Sekprodi mendaftarkan mahasiswa
│     Skripsi         │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  2. Pengajuan       │  ← Mahasiswa ajukan judul + proposal
│     Judul           │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  3. Review          │  ← Sekprodi setujui/tolak + plot pembimbing
│     Sekprodi        │  (Pembimbing 1 wajib, Pembimbing 2 opsional)
└──────────┬──────────┘
           │ (disetujui)
           ▼
┌─────────────────────┐
│  4. Review         │  ← Pembimbing 1 & 2 ACC/Revisi judul
│     Pembimbing     │
└──────────┬──────────┘
           │ (keduanya ACC)
           ▼
┌─────────────────────┐
│  5. Bimbingan      │  ← Bimbingan + nilai + ACC layak sidang
│     Skripsi        │
└──────────┬──────────┘
           │
           ├──────────────────────────────────────────────────────────┐
           │                                                          │
           ▼                                                          ▼
┌─────────────────────┐                                    ┌─────────────────────┐
│  6a. Pendaftaran    │  ← Mahasiswa upload berkas tahap   │  6b. Pendaftaran    │
│      Jadwal (T1)    │    jadwal (link GDrive). Syarat:    │      Verifikasi(T2) │
│                     │    Pembimbing 1 sudah diplot       │                     │
└──────────┬──────────┘                                    └──────────┬──────────┘
           │                                                           │
           ▼                                                           │
┌─────────────────────┐                                                │
│  7a. Verifikasi    │  ← Sekprodi verifikasi berkas tahap jadwal    │
│      & Jadwal      │    Input tanggal, jam, ruangan (tanpa penguji)  │
└──────────┬──────────┘                                                │
           │                                                            │
           ▼                                                            │
           │  (layak_sidang: kedua pembimbing ACC + nilai)              │
           └────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────────┐
│  7b. Verifikasi 2  │  ← Sekprodi verifikasi berkas tahap verifikasi
│      & Penguji     │    Tetapkan tim penguji (Ketua, Anggota 1, 2)
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  8. Sidang         │  ← Penguji beri penilaian sidang
│     Skripsi        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  9. Klaim Nilai    │  ← Mahasiswa upload berkas klaim
│                     │  ← Sekprodi verifikasi & finalisasi nilai
└─────────────────────┘
```

---

## Dua Tahap Pendaftaran Sidang

| Tahap | Nama | Syarat | Berkas | Output |
|-------|------|--------|--------|--------|
| **Tahap 1** | Pendaftaran Jadwal | Pembimbing 1 sudah diplot (P2 opsional) | Berkas tahap jadwal (Admin setting) | Tujuan: tentukan tanggal, jam, ruangan sidang |
| **Tahap 2** | Pendaftaran Verifikasi | Kedua pembimbing ACC layak sidang + nilai | Berkas tahap verifikasi (laporan final) | Tujuan: verifikasi berkas + tetapkan tim penguji |

---

## Tahapan Detail

### 1. Registrasi Skripsi

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Sekprodi |
| **Deskripsi** | Sekprodi mendaftarkan mahasiswa ke dalam sistem skripsi untuk tahun akademik aktif. |
| **Menu** | Skripsi → Registrasi Mahasiswa |
| **URL** | `Registrasi_skripsi` |
| **Output** | Mahasiswa tercatat di tabel `mahasiswa_skripsi` dan dapat mengakses modul skripsi. |

---

### 2. Pengajuan Judul Skripsi

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Mahasiswa |
| **Deskripsi** | Mahasiswa mengajukan judul skripsi beserta deskripsi, alasan pemilihan, file proposal, dan usulan pembimbing 1 & 2. |
| **Menu** | Skripsi → Pengajuan Judul Skripsi |
| **URL** | `Skripsi_pengajuan` |
| **Data yang diisi** | Judul, deskripsi, alasan, file proposal, id_pembimbing_1_request, id_pembimbing_2_request |
| **Status** | `pending` → menunggu review Sekprodi |

---

### 3. Review Pengajuan oleh Sekprodi

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Sekprodi |
| **Deskripsi** | Sekprodi mereview pengajuan judul, dapat menyetujui atau menolak. Jika disetujui: menetapkan judul final, plot pembimbing 1 & 2. |
| **Menu** | Skripsi → Plot Pembimbing (untuk plot) / Review pengajuan via menu terkait |
| **URL** | `Skripsi_plot_pembimbing`, `Skripsi_review_judul` |
| **Status** | `disetujui` / `ditolak` |
| **Output** | judul_disetujui, id_pembimbing_1_final, id_pembimbing_2_final |

---

### 4. Review Judul oleh Pembimbing

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Dosen Pembimbing 1 & Pembimbing 2 |
| **Deskripsi** | Masing-masing pembimbing mereview judul dan memberi status: **ACC** atau **Revisi**. Jika revisi, mahasiswa perbaiki dan ajukan ulang. |
| **Menu** | Skripsi → Ajuan Judul Mahasiswa |
| **URL** | `Ajuan_judul_skripsi` |
| **Status per pembimbing** | `pending` / `acc` / `revisi` |
| **Syarat lanjut** | Kedua pembimbing harus **ACC** agar mahasiswa dapat lanjut ke bimbingan. |

---

### 5. Bimbingan Skripsi

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Mahasiswa + Dosen Pembimbing 1 & 2 |
| **Deskripsi** | Mahasiswa melakukan bimbingan dengan kedua pembimbing. Pembimbing menginput nilai bimbingan dan memberi ACC layak sidang. |
| **Menu (Mahasiswa)** | Skripsi → Bimbingan Skripsi |
| **Menu (Dosen)** | Skripsi → Bimbingan Skripsi |
| **URL** | `Bimbingan_skripsi_mahasiswa`, `Bimbingan_skripsi` |
| **Data** | Log bimbingan, catatan, nilai bimbingan (P1 & P2) |
| **Status** | `bimbingan` → `layak_sidang` (jika kedua pembimbing ACC + sudah input nilai) |

---

### 6a. Pendaftaran Jadwal Sidang (Tahap 1)

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Mahasiswa |
| **Deskripsi** | Mahasiswa upload link Google Drive berkas tahap jadwal untuk menentukan tanggal, jam, ruangan sidang. |
| **Menu** | Skripsi → Pendaftaran Jadwal Sidang |
| **URL** | `Pendaftaran_jadwal_skripsi` |
| **Syarat** | Sekprodi sudah plot Pembimbing 1 (Pembimbing 2 opsional) |
| **Output** | Data pendaftaran tahap jadwal + upload berkas ke `skripsi_berkas_sidang_upload` |

---

### 7a. Verifikasi & Jadwal (Tahap 1)

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Sekprodi |
| **Deskripsi** | Sekprodi verifikasi berkas pendaftaran tahap 1 dan input jadwal (tanggal, jam, ruangan) ke `jadwal_skripsi`. Penguji belum ditetapkan. |
| **Menu** | Skripsi → Verifikasi Berkas Sidang (filter Tahap Jadwal) |
| **URL** | `Skripsi_verifikasi_berkas_sidang` |

---

### 6b. Pendaftaran Verifikasi Berkas (Tahap 2)

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Mahasiswa |
| **Deskripsi** | Mahasiswa upload link Google Drive berkas tahap verifikasi (laporan final) setelah layak sidang. |
| **Menu** | Skripsi → Pendaftaran Verifikasi Berkas |
| **URL** | `Pendaftaran_sidang_skripsi` |
| **Syarat** | Kedua pembimbing ACC layak sidang + sudah input nilai |
| **Output** | Data pendaftaran tahap verifikasi + upload berkas |

---

### 7b. Verifikasi & Penguji (Tahap 2)

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Sekprodi |
| **Deskripsi** | Sekprodi verifikasi berkas pendaftaran tahap 2 dan menetapkan tim penguji (Ketua, Anggota 1, Anggota 2) pada jadwal yang sudah ada. |
| **Menu** | Skripsi → Verifikasi Berkas Sidang (filter Tahap Verifikasi) |
| **URL** | `Skripsi_verifikasi_berkas_sidang` |
| **Output** | Update `jadwal_skripsi` dengan `id_ketua_penguji`, `id_anggota_penguji_1`, `id_anggota_penguji_2` |

---

### 8. Sidang Skripsi

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Dosen Penguji (Ketua + Anggota 1 + Anggota 2) |
| **Deskripsi** | Penguji melakukan penilaian sidang berdasarkan aspek dan item penilaian yang telah disetting. |
| **Menu** | Skripsi → Jadwal Sidang Skripsi |
| **URL** | `Jadwal_skripsi_dosen`, `Penilaian_sidang` |
| **Output** | Nilai sidang (P2) tersimpan di `skripsi_nilai_sidang` |

---

### 9. Klaim Nilai

| Aspek | Keterangan |
|-------|------------|
| **Pelaku** | Mahasiswa + Sekprodi |
| **Deskripsi** | Setelah sidang selesai dan semua penguji sudah input nilai, mahasiswa dapat mengajukan klaim nilai dengan mengupload berkas yang disyaratkan. Sekprodi memverifikasi dan sistem menghitung nilai akhir. |
| **Menu (Mahasiswa)** | Skripsi → Klaim Nilai |
| **Menu (Sekprodi)** | Skripsi → Verifikasi Klaim Nilai |
| **URL** | `Skripsi_klaim_nilai`, `Skripsi_verifikasi_klaim` |
| **Perhitungan** | Nilai akhir = (Nilai Bimbingan × Bobot P1) + (Nilai Sidang × Bobot P2) sesuai Setting Bobot Skripsi |
| **Output** | Nilai final tersimpan di `skripsi_nilai_rekap` |

---

## Perubahan Judul (Opsional)

Jika mahasiswa perlu mengubah judul setelah disetujui:

1. **Mahasiswa** mengajukan perubahan judul via menu **Skripsi → Perubahan Judul**
2. **Sekprodi** mereview dan menyetujui/menolak di **Skripsi → Validasi Perubahan Judul**
3. Status: `pending` → `disetujui` / `ditolak`

---

## Peran dan Menu Terkait

| Role | Menu Skripsi |
|------|--------------|
| **Admin** | Aspek Penilaian, Item Penilaian, Setting Berkas Sidang, Setting Berkas Klaim, Setting Grade, Setting Bobot |
| **Sekprodi** | Registrasi Mahasiswa, Plot Pembimbing, Validasi Perubahan Judul, Verifikasi Berkas Sidang, Jadwal Sidang, Monitoring Penguji, Verifikasi Klaim Nilai, Aktivitas Penilaian, Honor Skripsi, Laporan Skripsi |
| **Kaprodi** | - |
| **Dosen** | Ajuan Judul Mahasiswa, Bimbingan Skripsi, Daftar Mahasiswa, Jadwal Sidang Skripsi |
| **Mahasiswa** | Pengajuan Judul Skripsi, Bimbingan Skripsi, Perubahan Judul, Pendaftaran Jadwal Sidang, Pendaftaran Verifikasi Berkas, Klaim Nilai, Jadwal Sidang |

---

## Tabel Database Utama

| Tabel | Fungsi |
|-------|--------|
| `mahasiswa_skripsi` | Data registrasi mahasiswa skripsi |
| `skripsi_pengajuan` | Pengajuan judul beserta status review |
| `skripsi_pembimbing` | Plot pembimbing per mahasiswa |
| `skripsi_bimbingan` | Data bimbingan |
| `skripsi_bimbingan_log` | Log/catatan tiap bimbingan |
| `skripsi_nilai_bimbingan` | Nilai bimbingan dari pembimbing |
| `skripsi_pendaftaran_sidang` | Pendaftaran sidang mahasiswa |
| `skripsi_berkas_sidang` | Setting berkas sidang (per tahap: jadwal/verifikasi) |
| `skripsi_berkas_sidang_upload` | Berkas sidang yang diupload |
| `jadwal_skripsi` | Jadwal sidang |
| `skripsi_nilai_sidang` | Nilai sidang dari penguji |
| `skripsi_klaim_nilai` | Klaim nilai mahasiswa |
| `skripsi_nilai_rekap` | Nilai akhir skripsi |

---

## Status Skripsi (mahasiswa_skripsi.status)

| Status | Arti |
|--------|------|
| `bimbingan` | Sedang dalam proses bimbingan |
| `layak_sidang` | Kedua pembimbing sudah ACC + input nilai, siap daftar sidang |

---

---

## Countdown Sidang

Di halaman utama mahasiswa skripsi, countdown ditampilkan **di atas** Progress Skripsi. Menampilkan hitung mundur (hari, jam, menit, detik) ke tanggal/jam sidang secara real-time.

---

*Dokumen ini mengacu pada implementasi SI-HARKAT. Terakhir diperbarui: Februari 2026.*
